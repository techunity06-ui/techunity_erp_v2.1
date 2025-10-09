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
		    	ADMINISTRATOR_GODOWN_UPDATE,
		        ADMINISTRATOR_GODOWN_DELETE
		    ]);
		    $branch_id = $POST['branch_id'];
			$where='';
			if($branch_id != '1000'){
				$where .= check_branch('branch_id',$branch_id);
			}
			if($branch_id == ""){
			$output = array(
			"sEcho" => 1,
			"iTotalRecords" => 0,
			"iTotalDisplayRecords" => 0,
			"aaData" => array()
			);

			echo json_encode( $output );
			}else{
		 
			$appData = array();
			$i=1;
			$aColumns = array('mst.gd_id', 'mst.gd_name','branch.branch_name','mst.g_status','mst.user_id');
			$sIndexColumn = "mst.gd_id";
			$isWhere = array("mst.g_status = 0 and mst.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "mst_godown as mst";			
			$isJOIN = array('left join branch_mst as branch on branch.branch_id=mst.branch_id');
			$hOrder = "mst.gd_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['gd_name'];
				$row_data[] = $row['branch_name'];
				
				
				$edit_btn=''; $delete_btn=''; 
				if(in_array(ADMINISTRATOR_GODOWN_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_parameter('.$row['gd_id'].','.$row['p_gd_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_GODOWN_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_parameter('.$row['gd_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				} 
				
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
			}
		}
		else if(strtolower($POST['mode']) == "add" || strtolower($POST['mode']) == "add_model") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `gd_id`,`gd_name`,`g_status` FROM `mst_godown` WHERE `gd_name` ='".$POST['gd_name']."' and `g_status`='0' and `company_id`='".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				$resp['msg'] = "-1";
			} else {
				$info['gd_name']	= $POST['gd_name'];				
				$info['p_gd_id']	= $POST['p_gd_id'];	
				$info['gd_address']	= $_POST['gd_address'];
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['show_in_list']	= 1;		
				
				$inserid=add_record('mst_godown', $info, $dbcon, $branch_id);
				if($inserid)
				{
					if(strtolower($POST['mode']) == "add")
					{
						$resp['msg'] = "1";

						if($POST['p_gd_id'] > 0){
							$arr_status['show_in_list'] = 0;
							$update_id=update_record('mst_godown', $arr_status,"gd_id=".$POST['p_gd_id'] , $dbcon);
						}
					}
					else
					{
						$zone_qry="select * from mst_godown where gd_id=".$inserid; 
						$zone_rel=mysqli_fetch_assoc($dbcon->query($zone_qry));
						$resp=$zone_rel;
						$resp['msg'] = "2";
					}
				}
				else
				{
					$resp['msg'] = "0";
				}
			}
			
			echo json_encode($resp);
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `mst_godown` WHERE `gd_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$tr = $dbcon -> query("SELECT `gd_id`,`gd_name`,`p_gd_id`,`g_status` FROM `mst_godown` WHERE `gd_name` ='".$POST['e_gd_name']."' and `g_status`='0' and `gd_id` != '".$POST['eid']."'  and `company_id`='".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				echo "-1";
			} else {
				$info['gd_name']	= $POST['e_gd_name'];
				// $info['p_gd_id']	= $POST['edit_p_gd_id'];
				$info['gd_address']	= $_POST['e_gd_address'];
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$updateid=update_record('mst_godown', $info,"gd_id=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(strtolower($POST['mode']) == "delete") {

			  $gd_id = $POST['eid'];
			  
			  $qry = "SELECT p_gd_id FROM mst_godown WHERE gd_id =". $gd_id;
			  $q = $dbcon->query($qry);
			 	$r = $q->fetch_assoc();
				$p_gd_id = $r['p_gd_id'];
				
			
				$info['g_status']='2';
				$updateid=update_record('mst_godown', $info,"gd_id=".$gd_id, $dbcon);

				$sub_location['g_status']='2';
				$update_id=update_record('mst_godown', $info,"p_gd_id=".$gd_id, $dbcon);

				$qry1 = "SELECT count(gd_id) as gd_id FROM mst_godown WHERE g_status = 0 and p_gd_id =". $p_gd_id;

				$res=$dbcon->query($qry1);
        $cnt=brp_mysqli_num_rows($res);
        $r1 = $res->fetch_assoc();
        
			  if($r1['gd_id'] == 0){
			  	$info_show['show_in_list']='1';
				  $updateid=update_record('mst_godown', $info_show,"gd_id=".$p_gd_id, $dbcon);
			  }
				
				if($updateid)
					echo "1";
				else
					echo "0";
			
		}
		else if(strtolower($POST['mode']) == "get_all_branch") {
			echo get_branch($dbcon,$POST['id']);
		}
		else if(strtolower($POST['mode']) == "get_location_tree") {
			$branch_id = $POST['branch_id'];

			$where='';
		    if($branch_id != '1000'){
		      $where .= ' AND branch_id = ' . $branch_id;
		    }


		    $qry = "SELECT	*	FROM mst_godown where g_status = 0 AND company_id = ". $_SESSION['company_id'] . $where;

		  
		    // echo $qry;
			$result=$dbcon->query($qry);
			
//create a multidimensional array to hold a list of category and parent category
$godown = array(
	'godowns' => array(),
	'parent_godown' => array()
);

if(brp_mysqli_num_rows($result) > 0){
	//build the array lists with data from the category table
while ($row = brp_mysqli_fetch_assoc($result)) {
	//creates entry into godowns array with current category id ie. $godowns['godowns'][1]
	$godown['godowns'][$row['gd_id']] = $row;
	//creates entry into parent_godown array. parent_godown array contains a list of all godowns with children
	$godown['parent_godown'][$row['p_gd_id']][] = $row['gd_id'];
}
	echo buildCategory_g(0, $godown);	
}else{
		

	echo "<div style='height:200px' class='col-12 text-center'><h3 style='padding-top:100px;'>No Location added for selected branch.</h3></div>";
}

			
		}
		
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}


function buildCategory_g($parent, $godown) {
	$html = "";
	if (isset($godown['parent_godown'][$parent])) {
		$html .= '<ol class="dd-list">';
		foreach ($godown['parent_godown'][$parent] as $gd) {

			$add_btn=''; $edit_btn=''; $delete_btn=''; 
			if($godown['godowns'][$gd]['gd_id'] > 0) {
			$add_btn=' <button class="btn btn-xs btn-primary" data-original-title="Add Sub Location" data-toggle="tooltip" data-placement="top" onClick="show_add_location_form('. $godown['godowns'][$gd]['gd_id'] .','. $godown['godowns'][$gd]['branch_id'] .');">Add sub</button>'; 
				// if(in_array(ADMINISTRATOR_GODOWN_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_parameter('. $godown['godowns'][$gd]['gd_id'] .','.$godown['godowns'][$gd]['p_gd_id'].');"><i class="fa fa-pencil"></i></button>'; 
				// }
				// if(in_array(ADMINISTRATOR_GODOWN_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_parameter('.$godown['godowns'][$gd]['gd_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				// } 

				}
			if (!isset($godown['parent_godown'][$gd])) {
				

                                      $html .= '<li class="dd-item dd3-item" data-id="'. $godown['godowns'][$gd]['gd_id'] .'">
                                            <div class="dd-handle dd3-handle"></div>
                                            <div class="dd3-content" style="display: flex;">'. $godown['godowns'][$gd]['gd_name'] .'
                                            		<div style="margin-left:auto;">
                                                    '.$add_btn .' '.$edit_btn .' '  . $delete_btn .'
                                                </div>
                                            </div>
                                        </li>';
			}
			if (isset($godown['parent_godown'][$gd])) {
				$html .= ' <li class="dd-item dd3-item" data-id="'. $godown['godowns'][$gd]['gd_id'] .'">
                                          <div class="dd-handle dd3-handle"></div>
                                          <div class="dd3-content" style="display: flex;">'. $godown['godowns'][$gd]['gd_name'] .'
                                          		 <div style="margin-left:auto;">
                                                    '.$add_btn .' '.$edit_btn .' '  . $delete_btn .'
                                                </div>
                                          </div>
                                          <ol class="dd-list">';
                                             $html .= buildCategory_g($gd, $godown);
                                $html .=  '</ol>
                                      </li>';
				
			}
		}
		$html .= "</ol>";



	}
	return $html;
}
?>
