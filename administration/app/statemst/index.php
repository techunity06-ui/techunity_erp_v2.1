<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

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
		    	ADMINISTRATOR_STATE_UPDATE,
		        ADMINISTRATOR_STATE_DELETE
		    ]);
		    $branch_id = $POST['branch_id'];
			$where='';
		    
	  	$countryid_search = $POST['countryid_search'];

	  	$where.=" and state.countryid='$countryid_search'";

			$appData = array();
			$i=1;
			$aColumns = array('state.stateid', 'country.country_name', 'state.state_name','state.gst_state_code', 'state.state_status','state.is_deletable');
			$sIndexColumn = "state.stateid";
			$isWhere = array("state.state_status = 0".$where);
			$sTable = "state_mst as state";			
			$isJOIN = array("left join country_mst as country on country.countryid=state.countryid");
			$hOrder = "state.stateid desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row){
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['country_name'];
				$row_data[] = $row['state_name']; 
				$row_data[] = $row['gst_state_code'];
				
				$edit_btn=''; $delete_btn=''; 
				if($row['is_deletable']==0)
				{
					if(in_array(ADMINISTRATOR_STATE_UPDATE,$bulkAccessArray)){
						$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['stateid'].');"><i class="fa fa-pencil"></i></button>'; 
					}
					if(in_array(ADMINISTRATOR_STATE_DELETE,$bulkAccessArray)){
						$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_state('.$row['stateid'].')"><i class="fa fa-trash-o"></i></button>'; 
					}
				}
				else
				{
						$edit_btn='';
						$delete_btn='';
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
				$row['res']='';

				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
				
				$tr = $dbcon -> query("SELECT `stateid`,`state_name`,`state_status` FROM `state_mst` WHERE `state_status`=0 and `state_name` = '$POST[state_name]' and `countryid` = '$POST[countryid]'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['state_status'] != 0) {
						$info['state_status']=0;
						$updateid=update_record('state_mst', $info,"stateid=".$r['stateid'] , $dbcon);						
						if($updateid)
							{
									$row['res']='1';
							}
							else
							{
									$row['res']='0';
							}
					}
					else {
						$row['res']='-1';
					}
				}
				else {
							$info['countryid']= $POST['countryid'];
							$info['state_name']= $POST['state_name'];
							$info['gst_state_code']= $POST['gst_state_code'];							
							$info['cdate']		= date("Y-m-d H:i:s");
							//$info['user_id']		= $_SESSION[user_id];
							//$info['usertype_id']	= $_SESSION['user_type'];
							$inserid=add_record('state_mst', $info, $dbcon, $branch_id);
					if($inserid)
					{
						if(strtolower($POST['model'])=="model")
						{
							$query="select * from state_mst where stateid=".$inserid;
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
			$q = $dbcon -> query("SELECT * FROM `state_mst` WHERE `stateid` = '$POST[id]' ");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$tr = $dbcon -> query("SELECT `stateid`,`state_name`,`state_status` FROM `state_mst` WHERE `state_status`=0 and `state_name` = '".$POST['state_name']."' and `countryid` = '".$POST['countryid']."' and `stateid` != '".$POST['eid']."'");
			if($tr->num_rows > 0) {
				echo "-1";
			}else{
				$info['countryid']= $POST['countryid'];
				$info['state_name']= $POST['state_name'];
				$info['gst_state_code']= $POST['gst_state_code'];
				$info['cdate']		= date("Y-m-d H:i:s");
				$updateid=update_record('state_mst', $info,"stateid=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
				$info['state_status']='2';
				$updateid=update_record('state_mst', $info,"stateid=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0";
			}
		}
    }
   // else {
     //   die("Error - 2");
    //}
}

//else {
  //  die("Error - 1");
//}
?>