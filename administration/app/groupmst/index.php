<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    ADMINISTRATOR_GROUP_ADD,
    ADMINISTRATOR_GROUP_DELETE,
    ADMINISTRATOR_GROUP_EDIT,
    ADMINISTRATOR_GROUP_READ
]);

$branch_id = $_SESSION['branch_id'];
if($_POST != NULL) {
        $POST = bulk_filter($dbcon,$_POST);
}
else {
        $POST = bulk_filter($dbcon,$_GET);
}


if(strtolower($POST['mode']) == "fetch") {
//    $edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
//    $delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

    $branch_id = $POST['branch_id'];

    $where='';

    $appData = array();
    $i=1;
    $aColumns = array('g.g_id', 'g.g_name','g.format_value','g.group_format','g.g_pid','g1.g_name as p_name','g.group_start_series','g.end_format_value','g.user_id', 'g.g_status','g.is_deletable');
    $sIndexColumn = "g.g_id";
    $isWhere = array("g.g_status = 0 and g.company_id = ".$_SESSION['company_id']." ");
    $sTable = "tbl_group as g";
    $isJOIN = array("left join tbl_group as g1 on g.g_pid=g1.g_id");			
    //$isJOIN = array();
    $hOrder = "g.g_id desc";
    include($include.'pagging.php');
    $appData = array();
    $id=1;
    foreach($sqlReturn as $row) {
            $row_data = array();
            $row_data[] = $row['sr'];
            $row_data[] = $row['g_name'];
            $row_data[] = ($row['g_pid'] == '0' ? 'PRIMARY' : $row['p_name']);
			
			$row_data[] = $row['group_start_series'];
			if($row['group_format']=="1")
			{
				$row_data[] =$row['format_value'].$row['group_start_series'];
			}
			else if($row['group_format']=="2")
			{
				$row_data[] =$row['group_start_series'].$row['format_value'];
			}
			else if($row['group_format']=="3")
			{
				$row_data[] =$row['format_value'].$row['group_start_series'].$row['end_format_value'];
			}
			else
			{
				$row_data[] = '';
			}


            $edit_btn=''; $delete_btn='';  
            if(in_array(ADMINISTRATOR_GROUP_EDIT,$bulkAccessArray) && $row['is_deletable']=='0'){
                    $edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_group('.$row['g_id'].');"><i class="fa fa-pencil"></i></button>'; 
            }
            if(in_array(ADMINISTRATOR_GROUP_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
                    $delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_category('.$row['g_id'].')"><i class="fa fa-trash-o"></i></button>'; 
            }
            $row_data[] = $edit_btn.' '.$delete_btn; 
            $appData[] = $row_data;
            $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode( $output );

}
else if(strtolower($POST['mode']) == "add" || strtolower($POST['mode']) == "add_model") {
			
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['g_branch']) && $POST['g_branch']) ? $POST['g_branch'] : $_SESSION['branch_id'];
    $tr = $dbcon -> query("SELECT `g_id`,`g_name`,`g_status` FROM `tbl_group` WHERE `g_name` ='".$POST['g_name']."'");
    if($tr->num_rows > 0) {
            $resp['msg'] = "-1";
    } else {
        $info['g_name']	= $POST['g_name'];							
        $info['g_pid']	= $POST['g_parent'];							
        $info['g_open_balance']	= $POST['g_opening'];	

		$info['group_start_series']		= $POST['group_series_start'];
		$info['group_format']			= $POST['series_format'];	
		$info['format_value']			= $POST['format_value'];	
		$info['end_format_value']		= $POST['end_format_value'];
        $info['group_priority']         = $POST['group_priority'];	
		
        $info['form_id']	= $_POST['g_form'];							
        $info['cdate']		= date("Y-m-d H:i:s");
        $info['user_id']	= $_SESSION['user_id'];
        $info['company_id']	= $_SESSION['company_id'];
      //  print_r($info);exit;
        $inserid=add_record('tbl_group', $info, $dbcon, $branch_id);
        if($inserid){
                if(strtolower($POST['mode']) == "add")
                {
                        $resp['msg'] = "1";
                }
                else
                {
                        $zone_qry="select * from tbl_group where g_id=".$inserid; 
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
        $q = $dbcon -> query("SELECT * FROM `tbl_group` WHERE `g_id` = '$POST[id]'");
        $r = $q->fetch_assoc();
        echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['e_g_branch_id']) && $POST['e_g_branch_id']) ? $POST['e_g_branch_id'] : $_SESSION['branch_id'];
        $tr = $dbcon -> query("SELECT `g_id`,`g_name`,`g_status` FROM `tbl_group` WHERE `g_name` ='".$POST['e_g_name']."' and `g_id` != '".$POST['eid']."'");
        if($tr->num_rows > 0) {
                echo "-1";
        } else {
                $info['g_name']	= $POST['e_g_name'];							
                $info['g_pid']	= $POST['e_g_parent'];							
                $info['g_open_balance']	= $POST['e_g_opening'];		

				$info['group_start_series']	= $POST['edit_taxinvoice_start'];
				$info['group_format']		= $POST['edit_invoice_format'];	
				$info['format_value']		= $POST['edit_format_value'];
				$info['end_format_value']		= $POST['edit_end_format_value'];
                $info['group_priority']         = $POST['edit_group_priority'];
					
                $info['cdate']		= date("Y-m-d H:i:s");
                $info['user_id']	= $_SESSION['user_id'];
                $info['company_id']	= $_SESSION['company_id'];
                $updateid=update_record('tbl_group', $info,"g_id=".$POST['eid'] , $dbcon, $branch_id);
                if($updateid)
                        echo "1";
                else
                        echo "0".$dbcon->error;
        }

}
else if(strtolower($POST['mode']) == "delete") {

    $sTable = array(TABLE_LEDGER=>'LEDGER MODULE');
    $aColumns = array(array('l_group'));
    $sWhere = array(array('l_status=0 and l_group = "'.$POST['eid'].'"'));
    $checkLang = getCheckRelation($dbcon, $sTable, $aColumns, $sWhere);
    if(count($checkLang) > 0){
        $resp['msg'] = '-1';
        $resp['table'] = $checkLang;
    }else{
        $info['g_status']='2';
        $updateid=update_record('tbl_group', $info,"g_id=".$POST['eid'] , $dbcon);

        if($updateid)
            $resp['msg'] = '1';
        else
            $resp['msg'] = '0';
    }
    echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "get_group_dropdown_data") {
    echo get_all_group($dbcon,$POST['id']);
}
else if(strtolower($POST['mode']) == "get_form_type") {
	$gid=$POST['gid'];
	$q=$dbcon->query("select form_id from tbl_group where g_id='$gid'");
	$row=mysqli_fetch_array($q);
	echo $row['form_id'];
	//echo $gid;
}
?>