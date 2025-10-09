<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
	//	print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "fetch") {
			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		 
			$appData = array();
			$i=1;
			$aColumns = array('mode_dis_id', 'mode_dispatch', 'mode_des_status', 'userid');
			$sIndexColumn = "mode_dis_id";
			$isWhere = array("mode_des_status = 0");
			$sTable = "mode_of_dispatch";			
			$isJOIN = array();
			$hOrder = "mode_of_dispatch.mode_dis_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['mode_dispatch'];
				
				
				$edit_btn=''; $delete_btn=''; 
				if($edit_btn_per){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['mode_dis_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if($delete_btn_per){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bank('.$row['mode_dis_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				} 
				$row_data[] = $edit_btn.' '.$delete_btn; 

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add" || strtolower($POST['mode']) == "payment") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
				$tr = $dbcon -> query("SELECT `mode_dis_id`,`mode_dispatch`,`mode_des_status` FROM `mode_of_dispatch` WHERE `mode_dispatch` = '$POST[mode_dispatch]'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['mode_des_status'] != 0) {
						$info['mode_des_status']=0;
						$updateid=update_record('mode_of_dispatch', $info,"mode_dis_id=".$r['mode_dis_id'] , $dbcon);
						$row['res']='';
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
							
							$info['mode_dispatch']= $POST['mode_dispatch'];							
							$info['cdate']		= date("Y-m-d H:i:s");
							$info['userid']		= $_SESSION[user_id];
							$inserid=add_record('mode_of_dispatch', $info, $dbcon);
							$row['res']='';
					if($inserid)
					{
						if(strtolower($POST['model'])=="model")
						{
							$query="select * from mode_of_dispatch where mode_dis_id=".$inserid;
							$rel=mysqli_fetch_assoc($dbcon->query($query));		
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
				echo json_encode($row);	
				
			}
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `mode_of_dispatch` WHERE `mode_dis_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
				$info['mode_dispatch']= $POST['mode_dispatch'];				
				$info['cdate']		= date("Y-m-d H:i:s");				
				$updateid=update_record('mode_of_dispatch', $info,"mode_dis_id=".$POST['eid'] , $dbcon);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$info['mode_des_status']='2';
				$updateid=update_record('mode_of_dispatch', $info,"mode_dis_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0";
			}
		}
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
?>