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
		    	ADMINISTRATOR_TAX_UPDATE,
		        ADMINISTRATOR_TAX_DELETE
		    ]);
		    $branch_id = $POST['branch_id'];
			$where='';
		    if($branch_id){
		        $where .= check_branch('tax',$branch_id);
		    }
		 
			$appData = array();
			$i=1;
			$aColumns = array('tax.tax_id', 'tax.tax_name','tax.tax_value','ld.l_name', 'tax.tax_status', 'tax.user_id','tax.usertype_id');
			$sIndexColumn = "tax.tax_id";
			$isWhere = array("tax.tax_status = 0 and tax.company_id in (0,$_SESSION[company_id])".check_user('tax').$where);
			$sTable = "tbl_tax as tax";			
			$isJOIN = array('left join tbl_ledger as ld on ld.l_id=tax.ledger_id');
			$hOrder = "tax.tax_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['tax_name'];
				$row_data[] = $row['tax_value'];
				
				$edit_btn=''; $delete_btn=''; 
				if(in_array(ADMINISTRATOR_TAX_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['tax_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_TAX_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_tax('.$row['tax_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				} 
				
				$row_data[] = $edit_btn.' '.$delete_btn;  
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
		//	if($_POST['token'] == $_SESSION['token'])
			{
				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

				$row['res']='';
				$tax_name=$POST['tax_name'].' '.$POST['tax_value'].'%';			
				$tr = $dbcon -> query("SELECT * FROM `tbl_tax` WHERE `tax_status`=0 and `tax_name` = '$tax_name' and company_id=".$_SESSION['company_id']);
				
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['tax_status'] != 0) {
						$info['tax_status']=0;
						$updateid=update_record('tbl_tax', $info,"tax_id=".$r['tax_id'] , $dbcon); 
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
					echo json_encode($row);
			
				}
				else {
							$info['tax_name']		= $POST['tax_name'].' '.$POST['tax_value'].'%';
							$info['tax_value']		= $POST['tax_value'];
							$info['tax_group']		= $POST['tax_group'];
							$info['ledger_id']		= $POST['ledger_id'];
							$info['cdate']			= date("Y-m-d H:i:s");
							$info['user_id']		= $_SESSION['user_id'];
							$info['usertype_id']	= $_SESSION['user_type'];
							$info['company_id']		= $_SESSION['company_id'];
							$inserid=add_record('tbl_tax', $info, $dbcon, $branch_id);
							if($inserid)
							{
									$row['res'] ="1";
							}
							else
							{
								$row['res'] ="0";
							}
						echo json_encode($row);	
					}
			}
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_tax` WHERE `tax_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

				$tr = $dbcon -> query("SELECT * FROM `tbl_tax` WHERE `tax_status`=0 and `tax_name` = '".$POST['tax_name']."' and tax_id != '".$POST['eid']."' and company_id=".$_SESSION['company_id']);
				
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['tax_status'] != 0) {
						$info['tax_status']=0;
						$updateid=update_record('tbl_tax', $info,"tax_id=".$r['tax_id'] , $dbcon); 
						$row['res']='';
						if($updateid){
							$row['res']='1';
						}else{
							$row['res']='0';
						}
					}
					else 
					{
						$row['res']='-1';
					}		
					echo json_encode($row);
				}else {
					$per=substr($POST['tax_name'], -1);
					if($per=="%"){
						$info['tax_name']=$POST['tax_name'];	
					}else{
						$info['tax_name']=$POST['tax_name'].' '.$POST['tax_value'].'%';	
					}				
	                $info['tax_value']= $POST['tax_value'];							
	                $info['tax_group']= $POST['tax_group'];							
	                $info['ledger_id']= $POST['ledger_id'];							
					$info['cdate']		= date("Y-m-d H:i:s");				
					$info['user_id']		= $_SESSION['user_id'];
					$info['usertype_id']	= $_SESSION['user_type'];
					$info['company_id']		= $_SESSION['company_id'];
					$updateid=update_record('tbl_tax', $info,"tax_id=".$POST['eid'] , $dbcon, $branch_id);
					$row['res']='';
					if($updateid){
						$row['res']='update';
					}else{
						$row['res']='0';
					}
					echo json_encode($row);
				}
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$info['tax_status']='2';
				$updateid=update_record('tbl_tax', $info,"tax_id=".$POST['eid'] , $dbcon);
				$row['res']='';
				if($updateid)
				{
					$row['res']='1';
				}
				else
				{
					$row['res']='0';
				}
				echo json_encode($row);
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