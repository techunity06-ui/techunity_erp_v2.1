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
	if(strtolower($POST['mode']) == "holidays_add") {
		$appData = array();
		$fromDate = date('Y-m-d', strtotime($POST['holiday_from_date']));
		$toDate = date('Y-m-d', strtotime($POST['holiday_to_date']));
		
		$query = $dbcon->query("SELECT selected_date, DAYNAME(selected_date) AS weekday
								FROM ( SELECT adddate(DATE('".$fromDate."'), t1*10 + t0) AS selected_date FROM
								(select 0 t0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
								(select 0 t1 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1
								) tab WHERE (selected_date BETWEEN '".$fromDate."' AND '".$toDate."') and DAYNAME(selected_date) = '".$POST['currentWeek']."'
								ORDER BY selected_date");
		while ($r = $query->fetch_assoc()) {
			$row_data = array();
			$row_data['selected_date'] = date('d-m-Y', strtotime($r['selected_date']));
			$row_data['weekday'] = $r['weekday'];
			$appData[] = $row_data;
		}
		$output['aaData'] = $appData;
		echo json_encode($output);

	}else if(strtolower($POST['mode']) == "fetch") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$branch=$_SESSION['branch_id'];
			$where='';
			$where.="status IN (0,1) and hrmsho.company_id = $companyID and holiday_from_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND holiday_to_date<='".date('Y-m-d',strtotime($s_date[1]))."'".check_user('hrmsho');
			$appData = array();
			$i=1;
			$aColumns = array('id','holiday_name','holiday_from_date','holiday_to_date','total_holidays','holiday_color_code','status', 'comp.company_name');
			$sIndexColumn = "id";
			$isWhere = array($where);
			$sTable = "hrms_holiday_list as hrmsho";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmsho.company_id");
			$hOrder = "hrmsho.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['holiday_name'];
				$row_data[] = date('d M, Y',strtotime($row['holiday_from_date']));
				$row_data[] = date('d M, Y',strtotime($row['holiday_to_date']));
				$row_data[] = $row['total_holidays'];
				$row_data[] = $row['holiday_color_code'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'holiday_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_holiday('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
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

			// Insert New Holiday List
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['holiday_name'] = $POST['holiday_name'];
			$info['holiday_from_date']	= date('Y-m-d',strtotime($POST['holiday_from_date']));
			$info['holiday_to_date']	= date('Y-m-d',strtotime($POST['holiday_to_date']));
			$info['total_holidays']	= $POST['total_holidays'];
			$info['holiday_color_code']	= $POST['holiday_color_code'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$inserholidayid = add_record('hrms_holiday_list', $info, $dbcon);

			foreach ($POST['holiday_date'] as $i => $date) 
			{	
				$info_e['holiday_id'] = $inserholidayid;
				$info_e['holiday_date'] = date('Y-m-d',strtotime($POST['holiday_date'][$i]));
				$info_e['holiday_description'] = $POST['holiday_description'][$i];
				$info_e['updated_at'] = date("Y-m-d");
				$inserholiday = add_record('hrms_holiday', $info_e, $dbcon);
			}
			if($inserholidayid){	
				$arr['msg']="1";							
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);	

		}else if(strtolower($POST['mode']) == "edit") {
				$info['holiday_name'] = $POST['holiday_name'];
				$info['holiday_from_date']	= date('Y-m-d',strtotime($POST['holiday_from_date']));
				$info['holiday_to_date']	= date('Y-m-d',strtotime($POST['holiday_to_date']));
				$info['total_holidays']	= $POST['total_holidays'];
				$info['holiday_color_code']	= $POST['holiday_color_code'];
				$info['status']	= $POST['status'];
				$info['updated_at']	= date("Y-m-d H:i:s");
				$updateholidayid = update_record('hrms_holiday_list', $info,"id=".$POST['eid'] , $dbcon);

				//Update Holidays List
				$deleteid=delete_record('hrms_holiday',"holiday_id=".$POST['eid'], $dbcon);
		
				foreach ($POST['holiday_date'] as $i => $name) 
				{
					$info_e['holiday_id'] = $POST['eid'];
					$info_e['holiday_date'] = date('Y-m-d',strtotime($POST['holiday_date'][$i]));
					$info_e['holiday_description'] = $POST['holiday_description'][$i];
					$info_e['updated_at'] = date("Y-m-d");
					$updateholidaylist = add_record('hrms_holiday', $info_e, $dbcon);
					
				}
		
				if($updateholidayid){	
					$arr['msg']="1";
				}else{
					$arr['msg']="0";
				}
				echo json_encode($arr);	
		}
		else if(strtolower($POST['mode'])== "delete") {
			$row=array();
			$info['status'] = 2;
			$updateid=update_record('hrms_holiday_list', $info, "id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['msg']="1";
			else
				$row['msg']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_holiday_list', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}	
		
?>