<?php
session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/payroll_common_functions.php");

		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "fetch") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$where='';
			$appData = array();
			$i=1;
			$aColumns = array('payrollempclaim.id','empusers.l_name','payrollempclaim.claim_date','payrollempclaim.series_id','payrollempclaim.employee_id','com.company_name','payrollempclaim.status');
			$sIndexColumn = "payrollempclaim.id";
			$isWhere = array("payrollempclaim.status IN (0,1) and payrollempclaim.company_id = $companyID".check_user('payrollempclaim'));
			$sTable = "payroll_emp_benefit_claim as payrollempclaim";			
			$isJOIN = array('left join tbl_company as com on com.company_id=payrollempclaim.company_id',
							'left join tbl_ledger as empusers on empusers.l_id=payrollempclaim.employee_id');
			$hOrder = "payrollempclaim.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['series_id'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['claim_date'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'payroll_emp_benefit_claim_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payroll_emp_benefit_claim('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($row['status'] == '0')
				{  
					$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
				} else {
					$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
				}
					
				$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			
			$test = explode('.', $_FILES["file"]["name"]);
			$ext = end($test);
			$name = rand(100, 999).'.' . $ext;
			$path='../../view/upload/emp_benefit_claim_file/';
			$location = $path . $name;  
			move_uploaded_file($_FILES["file"]["tmp_name"], $location);

			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id'] = $_POST['series_id'];
			$info['employee_id'] = $_POST['employee_id'];
			$info['claim_date'] = ($_POST['claim_date'])?date('Y-m-d',strtotime($_POST['claim_date'])):'';
			$info['claim_benefit_for'] = ($_POST['claim_benefit_for'])?$_POST['claim_benefit_for']:'0.00';
			$info['claim_amount'] = ($_POST['claim_amount'])?$_POST['claim_amount']:'0.00';
			$info['claim_attachment'] = $name;
			$info['status'] = $POST['status'];
			$insertclaimid=add_record('payroll_emp_benefit_claim', $info, $dbcon);

			updateSeries($dbcon, 'id', 'payroll_emp_benefit_claim', 'PAYROLL EMP BENEFIT CLAIM');
					
			if($insertclaimid){	
				$arr['msg']="1";
				$arr['eid']=$insertclaimid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			if(isset($_FILES["file"]["name"]) && !empty($_FILES["file"]["name"])){
				$test = explode('.', $_FILES["file"]["name"]);
				$ext = end($test);
				$name = rand(100, 999).'.' . $ext;
				$path='../../view/upload/emp_benefit_claim_file/';
				$location = $path . $name;  
				move_uploaded_file($_FILES["file"]["tmp_name"], $location);
				$info['claim_attachment'] = $name;
			}

			$info['series_id'] = $_POST['series_id'];
			$info['employee_id'] = $_POST['employee_id'];
			$info['claim_date'] = ($_POST['claim_date'])?date('Y-m-d',strtotime($_POST['claim_date'])):'';
			$info['claim_benefit_for'] = ($_POST['claim_benefit_for'])?$_POST['claim_benefit_for']:'0.00';
			$info['claim_amount'] = ($_POST['claim_amount'])?$_POST['claim_amount']:'0.00';
			$info['status'] = $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");

			$updateid=update_record('payroll_emp_benefit_claim', $info,"id=".$POST['eid'] , $dbcon);
			
			if($updateid){	
				$arr['msg']="update";
				$arr['eid']=$POST['eid'];
			}else{
				if($updateid == 0){
					$arr['msg']="update";
					$arr['eid']=$POST['eid'];
				}else{
					$arr['msg']=0;
				}
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['status']	= 2;
			$updateclaimid=update_record('payroll_emp_benefit_claim', $info,"id=".$POST['eid'] , $dbcon);			
			if($updateclaimid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode'])== "get_earning_component_data")
		{
			$ids = $POST['component_id'];
			$q = $dbcon -> query("select payrollsalcomp.* from payroll_salary_component as payrollsalcomp
		 		where `payrollsalcomp`.`id`= $ids");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('payroll_emp_benefit_claim', $info,"id=".$POST['eid'] , $dbcon);
			
			echo ($updateid) ? "1" : "0";
		}
?>