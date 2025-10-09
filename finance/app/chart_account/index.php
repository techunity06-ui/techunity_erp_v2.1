<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");


//Ankit Sompura 09-01-2021
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    FINANCE_CHARTS_OF_ACCOUNT_EDIT,
    FINANCE_CHARTS_OF_ACCOUNT_DELETE
]);
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
			$appData = array();
			$i=1;
			$delete_btn=true;
			$aColumns = array('g_id', 'g_name','g_pid', 'g_status','user_id','is_deletable');
			$sIndexColumn = "g_id";
			$isWhere = array("g_status = 0");
			$sTable = "tbl_group";			
			$isJOIN = array();
			$hOrder = "g_id desc";
			include($path.'include/pagging.php');
			$id=1;
			foreach($sqlReturn as $row) {
                                //p($row);
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['g_name'];
				$row_data[] = get_grp_by_id($dbcon,$row['g_pid']);
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(FINANCE_CHARTS_OF_ACCOUNT_EDIT,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_group('.$row['g_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
                if(in_array(FINANCE_CHARTS_OF_ACCOUNT_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_category('.$row['g_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {

                    //Start Adeed by Dhruv
                    $q = $dbcon -> query("SELECT g_id, g_name,form_id  FROM `tbl_group` WHERE g_id = ".$POST['g_parent']);
                    $r_form_id = $q->fetch_assoc();
                    //end Adeed by Dhruv

                    $group_name = $dbcon->query("SELECT g_name as group_name FROM tbl_group WHERE g_status = 0 and g_name Like '".$POST['g_name']."'")
                                ->fetch_object()->group_name;
                    if($group_name) {
                            $resp['msg'] = "-1";
                    }
                    else {
                        $info['g_name']         = brp_strtolower($POST['g_name']);							
                        $info['g_pid']          = $POST['g_parent'];							
                        $info['g_open_balance']	= $POST['g_opening'];	
                        $info['form_id'] = $r_form_id['form_id'];  // Adeed by Dhruv					
                        $info['cdate']		= date("Y-m-d H:i:s");
                        $info['user_id']	= $_SESSION['user_id'];
                        $info['company_id']	= $_SESSION['company_id'];
                        $insertid = add_record('tbl_group', $info, $dbcon);
                        if($insertid){
                                $resp['html'] = get_chart_of_account_tree($dbcon, $POST['g_parent']);
                                $resp['msg'] = "1";
                        }
                        else {
                                $resp['msg'] = "0";
                        }
                    }
                    echo json_encode($resp);
		}
		else if(strtolower($POST['mode']) == "preedit") {
                        $q = $dbcon -> query("SELECT g_id, g_name, g_open_balance, g_pid, form_id
                            FROM `tbl_group` WHERE g_status = 0 and g_id = ".$POST['id']);
			$r = $q->fetch_assoc();
                        if($r['g_pid']){
                            $group_name = $dbcon->query("SELECT g_name as group_name FROM tbl_group WHERE g_id ='".$r['g_pid']."'")
                                ->fetch_object()->group_name;
                            $r['parent_name'] = $group_name;
                        } else {
                            $r['parent_name'] = 'Primary';
                        }
                        echo brp_json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
                        $group_name = $dbcon->query("SELECT g_name as group_name FROM tbl_group WHERE g_name Like '".$POST['e_g_name']."'")
                                ->fetch_object()->group_name;
			if($group_name) {
				$resp['msg'] = "-1";
			}
                        else {
                            $info['g_name']	= brp_strtolower($POST['e_g_name']);							
                            $info['g_pid']	= $POST['e_g_parent'];							
                            $info['g_open_balance']	= $POST['e_g_opening'];							
                            $info['cdate']		= date("Y-m-d H:i:s");
                            $info['user_id']	= $_SESSION['user_id'];
                            $info['company_id']	= $_SESSION['company_id'];
                            $updateid=update_record('tbl_group', $info,"g_id=".$POST['eid'] , $dbcon);
                            if($updateid)
                                    $resp['msg'] = "1";
                            else
                                    $resp['msg'] = "0";
                        }
                        echo brp_json_encode($resp);
		}
		else if(strtolower($POST['mode']) == "delete") {
			
                    if($POST['group_id']){
                        $deletable = '1';
                        
                        $has_child = $dbcon->query("select g_id FROM `tbl_group` WHERE g_status = 0 and g_pid =".$POST['group_id']." and company_id=".$_SESSION['company_id'])
                                ->fetch_object()->g_id;
                        if($has_child){
                            $deletable = '2';
                        }
                        
                        $has_ledger = $dbcon->query("SELECT l_id FROM tbl_ledger WHERE l_status = 0 and l_group =".$POST['group_id']." and company_id=".$_SESSION['company_id'])
                                ->fetch_object()->l_id;
                        if($has_ledger){
                            $deletable = '2';
                        }
                        
                        if(!$has_child && !$has_ledger){
                            $info['g_status']='2';
                            $updateid=update_record('tbl_group', $info,"g_id=".$POST['group_id'] , $dbcon);
                            if($updateid)
                                $deletable = "1";
                            else
                                $deletable = "0";
                        }
                        
                    }
                    echo $deletable;
			
		}
                }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>