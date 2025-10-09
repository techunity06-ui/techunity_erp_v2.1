<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
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
		
		if(brp_strtolower($POST['mode']) == "fetch") {
			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		 
			$appData = array();
			$i=1;
			$aColumns = array('email_module_type_id', 'email_template_name', 'name as module_name' , 'etype.status');
			$sIndexColumn = "email_module_type_id";
			$isWhere = array(" `etype`.`status` = 0");
			$sTable = "email_module_type_list as etype";			
			$isJOIN = array("left join email_module_list as ml on etype.module_id=ml.email_module_id");
			$hOrder = "etype.email_template_name ASC";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
		
			
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['email_template_name'];
				$row_data[] = $row['module_name'];
				
				$edit_btn=''; $delete_btn=''; 
				if($edit_btn_per){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['email_module_type_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if($delete_btn_per){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_catalog('.$row['email_module_type_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				} 
				
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo brp_json_encode( $output );
		}
		else if(brp_strtolower($POST['mode']) == "add") {
				$row['res']='';
				$tr = $dbcon -> query("SELECT * FROM `email_module_type_list` WHERE `email_template_name` = '".$POST['email_template_name']."' AND `module_id` = '".$POST['module_id']."' ");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['status'] != 0) {
						$info['status']=0;
						$updateid=update_record('email_module_type_list', $info,"email_module_type_id=".$r['email_module_type_id'] , $dbcon);
							if($updateid)
							{
								$row['res']='1';
							}
							else
							{
								$row['res']='0';
							}
					}
					else 
					{
							$row['res']='-1';
					}	
				}
				else {
							$info['module_id']= $POST['module_id'];
							$info['email_template_name']= $POST['email_template_name'];
							$info['cdate']		= date("Y-m-d H:i:s");
							$info['user_id']		= $_SESSION['user_id'];
							$info['company_id']		= $_SESSION['company_id'];
							
							$inserid=add_record('email_module_type_list', $info, $dbcon);
					if($inserid)
					{
						if(brp_strtolower($POST['model'])=="model")
						{
							$query="select * from email_module_type_list where email_module_type_id=".$inserid;
							$rel=brp_mysqli_fetch_assoc($dbcon->query($query));		
							$row = $rel;
							$row['res']="2"; 
						}
						else
						{
							$row['res'] ="1";
						}
					}
					else
					{
						$row['res'] ="0";
					}
					
				}
				echo brp_json_encode($row);
			
		}
		else if(brp_strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `email_module_type_list` WHERE `email_module_type_id` = '".$POST['id']."' ");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(brp_strtolower($POST['mode']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {

				$info['module_id']= $POST['module_id'];
				$info['email_template_name']= $POST['email_template_name'];
				$info['cdate']		= date("Y-m-d H:i:s");	
					
				$updateid=update_record('email_module_type_list', $info,"email_module_type_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(brp_strtolower($POST['mode']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {

				$info['status']='2';
				$updateid=update_record('email_module_type_list', $info,"email_module_type_id=".$POST['eid'] , $dbcon);
				if($updateid)
					echo "1";
				else
					echo "0";
			}
		}
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
  //  die("Error - 1");
//}
?>