<?php
session_start();
$AJAX = true;
include("../../../config/config.php");;
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");
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
			$aColumns = array('hrmsappraisaltemplatelist.id','hrmsappraisaltemplatelist.appraisal_template_title','hrmsappraisaltemplatelist.appraisal_template_desc','com.company_name','hrmsappraisaltemplatelist.status');
			$sIndexColumn = "hrmsappraisaltemplatelist.id";
			$isWhere = array("hrmsappraisaltemplatelist.status IN (0,1) and hrmsappraisaltemplatelist.company_id = $companyID".check_user('hrmsappraisaltemplatelist'));
			$sTable = "hrms_appraisal_template as hrmsappraisaltemplatelist";			
			$isJOIN = array('left join tbl_company as com on com.company_id=hrmsappraisaltemplatelist.company_id');
			$hOrder = "hrmsappraisaltemplatelist.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['company_name'];
				$row_data[] = $row['appraisal_template_title'];
				$row_data[] = $row['appraisal_template_desc'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT . 'hrms_appraisal_template_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_hrms_appraisal_template('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($other_btn_per) {
					if($row['status'] == '0')
					{  
						$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
					} else {
						$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
					}
				}
					
				$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id']	= $POST['series_id'];
			$info['appraisal_template_title']= $_POST['appraisal_template_title'];
			$info['appraisal_template_desc']= $_POST['appraisal_template_desc'];
			$info['status']	= $POST['status'];

			$goals = $dbcon -> query("select SUM(hrmsappraisalgoals.key_resource_planning_weightage) as weightage_count from hrms_appraisal_goals as hrmsappraisalgoals
				left join tbl_company as comp on comp.company_id = hrmsappraisalgoals.company_id
		 		where hrmsappraisalgoals.hrms_appraisal_template_id IS NULL and hrmsappraisalgoals.company_id = '".$_SESSION['company_id']."' and hrmsappraisalgoals.user_id = '".$_SESSION['user_id']."'");
			$regoals = $goals->fetch_assoc();
			if($regoals['weightage_count'] != '100'){
				$arr['msg']="3";
				$arr['count']=$regoals['weightage_count'];
			}else{
				$insertappraisaltempid=add_record('hrms_appraisal_template', $info, $dbcon);

				updateSeries($dbcon, 'id', 'hrms_appraisal_template', 'HRMS APPRAISAL TEMPLATE');
						
				$info_update['status']	= 0;
				$info_update['hrms_appraisal_template_id']	= $insertappraisaltempid;
				$updateappraisalgoalsid=update_record('hrms_appraisal_goals', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
						
				if($updateappraisalgoalsid){	
					$arr['msg']="1";
					$arr['eid']=$updateappraisalgoalsid;
				}else{
					$arr['msg']="0";
				}
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			$info['series_id']	= $POST['series_id'];
			$info['appraisal_template_title']= $_POST['appraisal_template_title'];
			$info['appraisal_template_desc']= $_POST['appraisal_template_desc'];
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");

			$goals = $dbcon -> query("select SUM(hrmsappraisalgoals.key_resource_planning_weightage) as weightage_count from hrms_appraisal_goals as hrmsappraisalgoals
				left join tbl_company as comp on comp.company_id = hrmsappraisalgoals.company_id
		 		where hrmsappraisalgoals.hrms_appraisal_template_id = '".$POST['eid']."' and hrmsappraisalgoals.company_id = '".$_SESSION['company_id']."' and hrmsappraisalgoals.user_id = '".$_SESSION['user_id']."'");
			$regoals = $goals->fetch_assoc();

			if($regoals['weightage_count'] != '100'){
				$arr['msg'] = "3";
				$arr['count'] = $regoals['weightage_count'];
			}else{
				$updateid=update_record('hrms_appraisal_template', $info,"id=".$POST['eid'] , $dbcon);
				
				if($updateid){	
					$arr['msg'] = "update";
					$arr['eid'] = $POST['eid'];
				}else{
					if($updateid == 0){
						$arr['msg'] = "update";
						$arr['eid'] = $POST['eid'];
					}else{
						$arr['msg'] = 0;
					}
				}
			}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['status']	= 2;
			$updateappraisaltempid=update_record('hrms_appraisal_template', $info,"id=".$POST['eid'] , $dbcon);
			$updateappraisalgoalsid=update_record('hrms_appraisal_goals', $info,"hrms_appraisal_template_id=".$POST['eid'] , $dbcon);
							
			if($updateappraisalgoalsid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldappraisaltemplateadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['key_resource_planning_name']	= $POST['key_resource_planning_name'];
				$info1['key_resource_planning_weightage']	= $POST['key_resource_planning_weightage'];
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['hrms_appraisal_template_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_appraisal_goals';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				}
			
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['est_id'])){
				$query="select hrmsappraisalgoals.* from hrms_appraisal_goals as hrmsappraisalgoals 
		 				where `hrmsappraisalgoals`.`status` = 3 and `hrmsappraisalgoals`.`user_id` = $userID and `hrmsappraisalgoals`.`company_id` = $companyID";
			}else{
				 $query="select hrmsappraisalgoals.* from hrms_appraisal_goals as hrmsappraisalgoals
		 			 where `hrmsappraisalgoals`.`status` = 0 and `hrmsappraisalgoals`.`hrms_appraisal_template_id`=".$POST['est_id']." and `hrmsappraisalgoals`.`user_id` = $userID and `hrmsappraisalgoals`.`company_id` = ".$companyID;
			}
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
						<div class="col-md-12 col-xs-12">
							<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
							<tr id="field">
								<th width="20%" class="text-center">KRA</th>
								<th width="20%" class="text-center">Weightage (%)</th>
								<th width="8%" class="text-center">Action</th>
							</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result)){
				 	echo '<tr id="fieldtr'.$rel['id'].'" >
						  <td data-label="KRA" style="vertical-align:top;" class="text-center">';
								if(empty($rel['key_resource_planning_name'])){
									echo '-';
								}else{
									echo $rel['key_resource_planning_name'];
								}
						echo'</td>
						<td data-label="Weightage (%)" style="vertical-align:top;" class="text-center">
							'.$rel['key_resource_planning_weightage'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_appraisal_goals_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_appraisal_goals_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
						</td>	
					</tr>';
					$i++;
				}
			}else{
					echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table>			 
				</div>
            </div>';
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select * from hrms_appraisal_goals as hrmsappraisalgoals
				left join tbl_company as comp on comp.company_id = hrmsappraisalgoals.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_appraisal_goals", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_appraisal_template', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
?>