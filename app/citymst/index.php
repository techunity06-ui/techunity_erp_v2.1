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
		
		if(strtolower($POST['mode']) == "fetch") {
			//check permission for party industry add
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	ADMINISTRATOR_CITY_UPDATE,
		        ADMINISTRATOR_CITY_DELETE
		    ]);
		    $branch_id = $POST['branch_id'];
			$where='';
		    if($branch_id){
		        $where .= check_branch('cmst',$branch_id);
		    }
		 
			$appData = array();
			$i=1;
			$aColumns = array('cmst.cityid', 'cmst.city_name', 'smst.state_name', 'cmst.city_status', 'cmst.userid');
			$sIndexColumn = "cmst.cityid";
			$isWhere = array("cmst.city_status = 0".$where);
			$sTable = "city_mst as cmst";			
			$isJOIN = array("left join state_mst as smst on cmst.stateid=smst.stateid");
			$hOrder = "cmst.city_name ASC";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['city_name'];
				$row_data[] = $row['state_name'];
				
				$edit_btn=''; $delete_btn=''; 
				if(in_array(ADMINISTRATOR_CITY_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['cityid'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_CITY_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_catalog('.$row['cityid'].')"><i class="fa fa-trash-o"></i></button>'; 
				} 
				
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

				$row['res']='';
				$tr = $dbcon -> query("SELECT `cityid`,`city_name`,`city_status` FROM `city_mst` WHERE `city_status`=0 and `city_name` = '".$POST['city_name']."'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['city_status'] != 0) {
						$info['city_status']=0;
						$updateid=update_record('city_mst', $info,"cityid=".$r['cityid'] , $dbcon);
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
							$info['city_name']= $POST['city_name'];
							$info['stateid']= $POST['stateid'];
							$info['cdate']		= date("Y-m-d H:i:s");
							$info['userid']		= $_SESSION[user_id];
							$inserid=add_record('city_mst', $info, $dbcon, $branch_id);
					if($inserid)
					{
						if(strtolower($POST['model'])=="model")
						{
							$query="select * from city_mst where cityid=".$inserid;
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
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `city_mst` WHERE `cityid` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$tr = $dbcon -> query("SELECT `cityid`,`city_name`,`city_status` FROM `city_mst` WHERE `city_status`=0 and `city_name` = '".$POST['city_name']."' and `cityid` != '".$POST['eid']."'");
			if($tr->num_rows > 0) {
				echo "-1";
			}else{
				$info['stateid']= $POST['stateid'];
				$info['city_name']= $POST['city_name'];
				$info['cdate']		= date("Y-m-d H:i:s");				
				$updateid=update_record('city_mst', $info,"cityid=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$info['city_status']='2';
				$updateid=update_record('city_mst', $info,"cityid=".$POST['eid'] , $dbcon);
				
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