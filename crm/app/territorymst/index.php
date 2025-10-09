<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			//check paermission for territory
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	CUSTOMER_TERRITORY_SLUG_UPDATE,
		        CUSTOMER_TERRITORY_SLUG_DELETE
		    ]);
		 
			$appData = array();
			$i=1;
			$branch_id = $POST['branch_id'];
			$where='';
		    if($branch_id){
		        $where .= check_branch('ter',$branch_id);
		    }
			$aColumns = array('ter.t_id', 'ter.t_name', 'ter.t_status', 'ter.userid', 'tp.t_name as p_name','ter.company_id','ter.is_delete');
			$sIndexColumn = "ter.t_id";
			$isWhere = array("ter.t_status = 0  and ter.company_id in (0,$_SESSION[company_id])","ter.userid in (0,$_SESSION[user_id])".$where);
			$sTable = "territory_mst as ter";			
			$isJOIN = array('left join territory_mst as tp on tp.t_id=ter.t_parent');
			$hOrder = "ter.t_id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['t_name'];
				$row_data[] = $row['p_name'];
				
				$edit_btn=''; $delete_btn=''; 
				
			if($row['t_id']!='0' && $row['is_delete']==0){
				if(in_array(CUSTOMER_TERRITORY_SLUG_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['t_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(CUSTOMER_TERRITORY_SLUG_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_territory('.$row['t_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				} 
			}
				
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

				$tr = $dbcon -> query("SELECT `t_id`,`t_name`,`t_status`,`company_id` FROM `territory_mst` WHERE `t_name` = '$POST[t_name]' and t_status='0' and company_id = '".$_SESSION['company_id']."'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['t_status'] != 0) {
						$info['t_status']=0;
						$updateid=update_record('territory_mst', $info,"t_id=".$r['t_id'] , $dbcon);						
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
					$info['t_name']= $POST['t_name'];							
					$info['t_parent']= $POST['t_parent'];							
					$info['cdate']		= date("Y-m-d H:i:s");
					$info['userid']		= $_SESSION['user_id'];
					$info['company_id']	= $_SESSION['company_id'];
					$info['is_delete']	= 0;
					
					$inserid=add_record('territory_mst', $info, $dbcon, $branch_id);
					if($inserid)
						echo "1";
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['mode']) == "preedit") {		
			$q = $dbcon -> query("SELECT * FROM `territory_mst` WHERE `t_id` = ".$POST['id']);
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "get_ter_list") {		
			echo get_all_territory($dbcon,$_POST['text_id']);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$tr = $dbcon->query("SELECT `t_id`,`t_name`,`t_parent`,`t_status`,`company_id` FROM `territory_mst` WHERE `t_name` = '".$POST['territory_name']."' and `t_parent` = '".$POST['t_parent']."' and `t_id` != '".$POST['eid']."' and t_status='0' and company_id = '".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				echo "-1";
			}else{
				$info['t_name']= $POST['territory_name'];
				$info['t_parent']= $POST['t_parent'];
				$info['company_id']	= $_SESSION['company_id'];				
				$info['cdate']		= date("Y-m-d H:i:s");	
				$info['is_delete']	= 0;
				$info['userid']		= $_SESSION['user_id'];
				$updateid=update_record('territory_mst', $info,"t_id=".$POST['eid'] , $dbcon, $branch_id);
				
				
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
				$info['t_status']='2';
				$updateid=update_record('territory_mst', $info,"t_id=".$POST['eid'] , $dbcon);
				
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