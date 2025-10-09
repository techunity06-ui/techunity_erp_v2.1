<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			//check permission for party industry add
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	ADMINISTRATOR_HSN_MASTER_EDIT,
		        ADMINISTRATOR_HSN_MASTER_DELETE
		    ]);

		  
			$where='';
		   
			$appData = array();
			$i=1;
			$aColumns = array('qcparam.hsn_id', 'qcparam.hsn_code','t.tax_cat_name', 'qcparam.hsn_desc','qcparam.hsn_status','qcparam.user_id','qcparam.is_deletable','qcparam.sale_gst');
			$sIndexColumn = "qcparam.hsn_id";
			$isWhere = array("qcparam.hsn_status = 0 and qcparam.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "mst_hsn_code as qcparam";			
			$isJOIN = array("left join tbl_tax_category as t on t.tax_cat_id=qcparam.sale_gst");
			$hOrder = "qcparam.hsn_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['hsn_code'];
				$row_data[] = $row['hsn_desc'];
				$row_data[] = $row['tax_cat_name'];
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(ADMINISTRATOR_HSN_MASTER_EDIT,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_parameter('.$row['hsn_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_HSN_MASTER_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_parameter('.$row['hsn_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
				
				$row_data[] = $edit_btn.' '.$delete_btn;
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add" || strtolower($POST['mode']) == "add_model") {
			//echo '<pre>';print_r($POST);exit;
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `hsn_id`,`hsn_code`,`hsn_status`,`company_id` FROM `mst_hsn_code` WHERE `hsn_code` ='".$POST['hsn_code']."' and company_id = '".$_SESSION['company_id']."' and `hsn_status`='0'");
			
			$cnt=mysqli_num_rows($tr);
			
			if($cnt>0) {
				$resp['msg'] = "-1";
			} else {
				$info['hsn_code']			= $POST['hsn_code'];							
				$info['hsn_desc']			= $POST['hsn_desc'];							
				$info['sale_gst']			= $POST['sale_gst'];							
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				$inserid=add_record('mst_hsn_code', $info, $dbcon, $branch_id);
				if($inserid) {
					if(strtolower($POST['mode']) == "add") {
						$resp['msg'] = "1";
						$resp['hsn_add_type'] = $POST['hsn_add_type']; 
						$resp['direct_hsn_add'] = $POST['direct_hsn_add'];
						$resp['hsn_code'] = $POST['hsn_code']; 
						$resp['sale_gst'] = $POST['sale_gst']; 
						$resp['inserid']=$inserid;
					} else {
						$zone_qry="select * from mst_hsn_code where hsn_id=".$inserid; 
						$zone_rel=mysqli_fetch_assoc($dbcon->query($zone_qry));
						$resp=$zone_rel;
						$resp['msg'] = "2";
					}
				} else {
					$resp['msg'] = "0";
				}
			}
			echo json_encode($resp);
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `mst_hsn_code` WHERE `hsn_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `hsn_id`,`hsn_code`,`hsn_status`,`company_id` FROM `mst_hsn_code` WHERE `hsn_id` != '".$POST['eid']."' and company_id = '".$_SESSION['company_id']."' and `hsn_code` ='".$POST['hsn_code']."' and `hsn_status`='0'");
			if($tr->num_rows > 0) {
				echo "-1";
			} else {
				$info['hsn_code']			= $POST['hsn_code'];							
				$info['hsn_desc']			= $POST['hsn_desc'];							
				$info['sale_gst']			= $POST['sale_gst'];							
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				$updateid=update_record('mst_hsn_code', $info,"hsn_id=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			
				$info['hsn_status']='2';
				$updateid=update_record('mst_hsn_code', $info,"hsn_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0";
			
		}
		
		
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>
