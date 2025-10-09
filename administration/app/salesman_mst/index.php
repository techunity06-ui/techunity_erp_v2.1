<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    DELETE_SALESMAN_MASTER,
    UPDATE_SALESMAN_MASTER,
    ADMINISTRATOR_LEDGER_APPROVE,
    ADMINISTRATOR_LEDGER_FINAL_APPROVE
]);

//echo '<pre>';print_r($_POST);exit;

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {			
			
			$approve_btn_per = in_array(ADMINISTRATOR_LEDGER_APPROVE,$bulkAccessArray);
            $final_approve_btn_per = in_array(ADMINISTRATOR_LEDGER_FINAL_APPROVE,$bulkAccessArray);
		 
            $branch_id = $POST['branch_id'];

            $where='';
            if($branch_id){
               // $where .= check_branch('l',$branch_id);
            }
                        
			$appData = array();
			$i=1;
			$aColumns = array('sm.*');
			$sIndexColumn = "sm.salesman_id";
			$isWhere = array("sm.isdelete=0");
			$sTable = "tbl_salesman_master as sm";			
			$isJOIN = array();
			//$isJOIN = array("left join tbl_group as g on g.g_id=l.l_group","left join city_mst as cit on cit.cityid=l.cityid");
			$hOrder = "sm.salesman_id";
			include($include.'pagging.php');
			//$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				
				$row_data = array();
				$row_data[] = $row['sr'];
				
				$row_data[] = $row['salesman_name'];
				$row_data[] = $row['salesman_allias'];
				$row_data[] = $row['salesman_print_name'];

				$row_data[] = $row['salesman_address'];
				$row_data[] = $row['salesman_mobile'];
				$row_data[] = $row['salesman_whatsup']; 

				$row_data[] = $row['salesman_email'];
				$row_data[] = $row['salesman_comm_type'];
				$row_data[] = $row['salesman_commision'];
				$edit_btn=''; $delete_btn=''; 
				if(in_array(UPDATE_SALESMAN_MASTER,$bulkAccessArray)){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.ADMINISTRATION_ROOT.'salesman_edit/'.$row['salesman_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				if(in_array(DELETE_SALESMAN_MASTER,$bulkAccessArray) && !$used_ledger){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_sales_data('.$row['salesman_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				} 

				$row_data[] = $edit_btn.' '.$delete_btn ; 

				//Amish Soni 15-09-2020
				$row_data[] = ($approve_btn_per && $final_approve_btn_per) ? $change_status : '';
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			
			$tr = $dbcon -> query("SELECT `salesman_id` FROM `tbl_salesman_master` WHERE `isdelete`=0 and `salesman_name` ='".$POST['salesman_name']."' and `company_id`='".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				$r = brp_mysqli_fetch_assoc($tr);
				if($r['isdelete'] != 0) {
					$info['isdelete']=0;
					$updateid=update_record('tbl_salesman_master', $info,"salesman_id=".$r['salesman_id'] , $dbcon);						
					if($updateid)
					echo "1";
					else
					echo "0";
				}
				else {
					echo '-1';
				}
			}
			else {
				$info['salesman_name']	= $POST['salesman_name'];							
				$info['salesman_allias'] = $POST['salesman_allias'];
				$info['salesman_print_name'] = $POST['salesman_print_name'];
				
				$info['salesman_address']	= $POST['salesman_address'];							
				$info['salesman_mobile'] = $POST['salesman_mobile'];
				$info['salesman_whatsup'] = $POST['salesman_whatsup'];

				$info['salesman_email']	= $POST['salesman_email'];							
				$info['salesman_comm_type'] = $POST['salesman_comm_type'];
				$info['salesman_commision'] = $POST['salesman_commision'];

				$inserid=add_record('tbl_salesman_master', $info, $dbcon);
				if($inserid)
				echo "1";
				else
				echo "0";
			}
			
		}else if(strtolower($POST['mode']) == "edit") {

			$tr = $dbcon -> query("SELECT `salesman_id` FROM `tbl_salesman_master` WHERE `isdelete`=0 and `salesman_name` ='".$POST['salesman_name']."' and `salesman_id` != '".$POST['salesman_id']."' and `salesman_mobile` ='".$POST['salesman_mobile']."' and `company_id`='".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				echo '-1';
			} else {
				$info['salesman_name']	= $POST['salesman_name'];	
				$info['salesman_allias'] = $POST['salesman_allias'];
				$info['salesman_print_name'] = $POST['salesman_print_name'];
				$info['salesman_mobile']	= $POST['salesman_mobile'];
				$info['salesman_whatsup']	= $POST['salesman_whatsup'];
				$info['salesman_address']	= $POST['salesman_address'];
				$info['salesman_comm_type']	= $POST['salesman_comm_type'];	
				$info['salesman_commision'] = $POST['salesman_commision'];					
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['usertype_id']	= $_SESSION['user_type'];
				$updateid=update_record('tbl_salesman_master', $info,"salesman_id=".$POST['salesman_id'] , $dbcon);
				if($updateid)
					echo "3";
				else
					echo "0".$dbcon->error;
			}
			
		}
		else if(strtolower($POST['mode']) == "delete_sales_data") {
			
			$row=array();
			$info['isdelete']=1;	
			
			$updateid=update_record('tbl_salesman_master', $info, "salesman_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
	
			
		}

  
?>