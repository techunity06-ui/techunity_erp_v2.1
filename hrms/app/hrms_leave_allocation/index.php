<?php

session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");
	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	} else {
		$POST = bulk_filter($dbcon,$_GET);
	}
	if(strtolower($POST['mode']) == "fetch") {
		
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$where='';
			$where.="hrmsleavealloc.status IN (0,1) and hrmsleavealloc.company_id = $companyID and leave_from_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND leave_to_date<='".date('Y-m-d',strtotime($s_date[1]))."'".check_user('hrmsleavealloc');
			$appData = array();
			$i=1;
			$aColumns = array('hrmsleavealloc.id','hrmsleavealloc.series_id','invoicetype.invoice_format','invoicetype.format_value','invoicetype.end_format_value','leave_from_date','leave_to_date','new_leave_allocation','add_unused_leave_flag','total_leave_allocated', 'leavetype.leave_type_name', 'comp.company_name','empusers.l_name','hrmsleavealloc.status');
			$sIndexColumn = "hrmsleavealloc.id";
			$isWhere = array($where);
			$sTable = "hrms_leave_allocation as hrmsleavealloc";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmsleavealloc.company_id","left join tbl_ledger as empusers on empusers.l_id=hrmsleavealloc.employee_id","left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=hrmsleavealloc.series_id","left join hrms_leave_type as leavetype on leavetype.id=hrmsleavealloc.leave_type_id");
			$hOrder = "hrmsleavealloc.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['id'];
				$row_data[] = $row['series_id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['leave_type_name'];
				$row_data[] = date('d M, Y',strtotime($row['leave_from_date']));
				$row_data[] = date('d M, Y',strtotime($row['leave_to_date']));
				$row_data[] = $row['add_unused_leave_flag'];
				$row_data[] = $row['total_leave_allocated'];

				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'hrms_leave_allocation_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_leave_application('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($other_btn_per) {
					if($row['status'] == '0'){  
						$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
					} else {
						$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
					}
				}
				$row_data[] = $edit_btn.' '.$delete_btn.' '.$change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {

			// Insert New Leave Application List
			$info['user_id']= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['employee_id']	= $POST['employee_id'];
			$info['series_id'] = $POST['series_id'];
			$info['leave_type_id'] = $POST['leave_type_id'];
			$info['leave_from_date'] = date('Y-m-d',strtotime($POST['leave_from_date']));
			$info['leave_to_date'] = date('Y-m-d',strtotime($POST['leave_to_date']));
			$info['new_leave_allocation']	= $POST['new_leave_allocation'];
			$info['add_unused_leave_flag']	= ($POST['add_unused_leave_flag'])?$POST['add_unused_leave_flag']:"No";
			$info['unused_leave_total']	= $POST['unused_leave_total'];
			$info['total_leave_allocated']	= $POST['total_leave_allocated'];
			$info['allocation_description']	= $POST['allocation_description'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$info['status']	= $POST['status'];
			$insertleaveid = add_record('hrms_leave_allocation', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `hrms_leave_allocation`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updateinvoiceid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'LEAVE ALLOCATION'" , $dbcon);

			if($insertleaveid){	
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
				$info['employee_id']	= $POST['employee_id'];
				$info['series_id'] = $POST['series_id'];
				$info['leave_type_id'] = $POST['leave_type_id'];
				$info['leave_from_date'] = date('Y-m-d',strtotime($POST['leave_from_date']));
				$info['leave_to_date'] = date('Y-m-d',strtotime($POST['leave_to_date']));
				$info['new_leave_allocation']	= $POST['new_leave_allocation'];
				$info['add_unused_leave_flag']	= ($POST['add_unused_leave_flag'])?$POST['add_unused_leave_flag']:"No";
				$info['unused_leave_total']	= $POST['unused_leave_total'];
				$info['total_leave_allocated']	= $POST['total_leave_allocated'];
				$info['allocation_description']	= $POST['allocation_description'];
				$info['updated_at']	= date("Y-m-d H:i:s");
				$info['status']	= $POST['status'];
				$updateleaveid = update_record('hrms_leave_allocation', $info,"id=".$POST['eid'] , $dbcon);
		
				if($updateleaveid){	
					$arr['msg']="1";
				}else{
					$arr['msg']="0";
				}
				echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('hrms_leave_allocation', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_leave_allocation', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}	
		
?>