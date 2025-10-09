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
		    	ADMINISTRATOR_QC_PARAMETER_UPDATE,
		        ADMINISTRATOR_QC_PARAMETER_DELETE
		    ]);

		    $branch_id = $POST['branch_id'];
			$where='';
		   
		     if($branch_id != '1000'){
	        $where .= check_branch('qcparam',$branch_id);
	    }
	    if($branch_id == ""){
	    	 $output = array(
		        "sEcho" => 1,
		        "iTotalRecords" => 0,
		        "iTotalDisplayRecords" => 0,
		        "aaData" => array()
		    );
	     	
	     	echo json_encode( $output );
	     }else{
			
			$appData = array();
			$i=1;
			$aColumns = array('qcparam.p_id', 'qcparam.p_name','qcparam.p_status','qcparam.user_id','qcparam.is_deletable');
			$sIndexColumn = "qcparam.p_id";
			$isWhere = array("qcparam.p_status = 0 and qcparam.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "tbl_qc_param as qcparam";			
			$isJOIN = array();
			$hOrder = "qcparam.p_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['p_name'];
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(ADMINISTRATOR_QC_PARAMETER_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_parameter('.$row['p_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_QC_PARAMETER_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_parameter('.$row['p_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		}
		else if(strtolower($POST['mode']) == "add" || strtolower($POST['mode']) == "add_model") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `p_id`,`p_name`,`p_status`,`company_id` FROM `tbl_qc_param` WHERE `p_name` ='".$POST['p_name']."' and company_id = '".$POST['company_id']."' and `p_status`='0'");
			if($tr->num_rows > 0) {
				$resp['msg'] = "-1";
			} else {
				$info['p_name']	= $POST['p_name'];							
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$inserid=add_record('tbl_qc_param', $info, $dbcon, $branch_id);
				if($inserid) {
					if(strtolower($POST['mode']) == "add") {
						$resp['msg'] = "1";
					} else {
						$zone_qry="select * from tbl_qc_param where p_id=".$inserid; 
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
			$q = $dbcon -> query("SELECT * FROM `tbl_qc_param` WHERE `p_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `p_id`,`p_name`,`p_status`,`company_id` FROM `tbl_qc_param` WHERE `p_id` != '".$POST['eid']."' and company_id = '".$POST['company_id']."' and `p_name` ='".$POST['e_p_name']."' and `p_status`='0'");
			if($tr->num_rows > 0) {
				echo "-1";
			} else {
				$info['p_name']	= $POST['e_p_name'];							
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$updateid=update_record('tbl_qc_param', $info,"p_id=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			$sTable = array(TABLE_PRODUCT_PARAMETER=>'ITEM MASTER MODULE');
			$aColumns = array(array('param_id'));
			$sWhere = array(array('param_id = "'.$POST['eid'].'"'));
			$checkLang = getCheckRelation($dbcon, $sTable, $aColumns, $sWhere);
			if(count($checkLang) > 0){
				$resp['msg'] = '-1';
				$resp['table'] = $checkLang;
			}else{	
				$info['p_status']='2';
				$updateid=update_record('tbl_qc_param', $info,"p_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					$resp['msg'] = '1';
				else
					$resp['msg'] = '0';
			}
			echo json_encode($resp);	
		}
		else if(strtolower($POST['mode']) == "get_all_product") {
			echo getproduct($dbcon,$POST['id']);
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