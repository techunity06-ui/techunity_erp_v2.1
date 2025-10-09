<?php
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../include/function_database_query.php");
	
		$qry3='SELECT SQL_CALC_FOUND_ROWS task.task_id, task.task_rel_id, task.task_name, cust.cust_name, per.c_con_fname, row.task_rel_name, stage.opp_stage, state.state_name, city.city_name, task.task_due_date, task.task_remark, usr.user_name, task.task_rel_id, task.inquiry_id, task.task_type_id, task.task_status, task.entry_type, task.alert_date_time, type.mcd_name as type_name, task.user_id, task.task_priority_id, task_sub.mcd_name as task_sub_name, stage.opp_probability, qt_aprv.approve_status, qt_aprv.quotation_id, type.mcd_id FROM tbl_task as task left join users as usr on usr.user_id=task.user_id left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id left join task_rel_mst as row on row.task_rel_id=task.task_rel_id left join (SELECT cust_id as c_id,inquiry_id from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id left join (SELECT inquiry_id,approve_status,quotation_id FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0) as qt_aprv on qt_aprv.inquiry_id=task.inquiry_id left join tbl_customer as cust on cust.cust_id=inq.c_id left join state_mst as state on state.stateid=(SELECT c_add_state FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1) left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1) left join tbl_opportunity_mst as stage on stage.opp_id=(SELECT opp_id from tbl_inquiry where inquiry_id=task.inquiry_id) left join tbl_cust_contact as per on per.c_con_id=task.c_con_id where ( 1 AND task.task_status=0 and task.entry_type=1 and task.alert_date_time!="0000-00-00 00:00:00" and task.alert_date_time!="1970-01-01 05:30:00" and DATE_FORMAT(alert_date_time,"%Y-%m-%d")<="2021-06-23" and DATE_FORMAT(task.task_due_date,"%Y-%m-%d")<="2021-06-23" and task.task_type_id=16 and FIND_IN_SET (104,task.show_user_ids)) Group by task.inquiry_id ORDER BY task.task_priority_id,task.alert_date_time';
		$result3=$dbcon->query($qry3);
		while($rel3=mysqli_fetch_assoc($result3))
		{
				$qry_up11="UPDATE `tbl_inquiry` SET inquiry_status=2 WHERE inquiry_id=".$rel3['inquiry_id'];
				$result_up11=$dbcon->query($qry_up11);
				
				$qry_up2="UPDATE `tbl_inquiry_trn` SET inquiry_trn_status=2 WHERE inquiry_id=".$rel3['inquiry_id'];
				$result_up2=$dbcon->query($qry_up2);
				
				$qry_up3="UPDATE `tbl_task` SET task_status=2 WHERE inquiry_id=".$rel3['inquiry_id'];
				$result_up3=$dbcon->query($qry_up3);
				
				$query = "select * from tbl_quotation as allocate_process
					where allocate_process.inquiry_id=".$rel3['inquiry_id'];
				$result=$dbcon->query($query);
				while($rel=mysqli_fetch_assoc($result)){
					
					$qry_up4="UPDATE `tbl_quotation` SET quotation_status=2 WHERE quotation_id=".$rel['quotation_id'];
					$result_up4=$dbcon->query($qry_up4);
					
					$qry_up5="UPDATE `tbl_quotation_trn` SET quot_trn_status=2 WHERE quotation_id=".$rel['quotation_id'];
					$result_up5=$dbcon->query($qry_up5);
				}
		}
	
	
?>
