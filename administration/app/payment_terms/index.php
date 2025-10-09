<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
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

			$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	ADMINISTRATOR_PAYMENTTERMS_EDIT,
		        ADMINISTRATOR_PAYMENTTERMS_DELETE
		    ]);


			$appData = array();
			$i=1;
			$aColumns = array('terms_id', 'payment_terms','payment_days', 'terms_status', 'userid');
			$sIndexColumn = "terms_id";
			$isWhere = array("terms_status = 0");
			$sTable = "pay_terms";			
			$isJOIN = array();
			$hOrder = "pay_terms.terms_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['payment_terms'];
				$row_data[] = $row['payment_days'];
				
				$edit_btn=''; $delete_btn=''; 
				if(in_array(ADMINISTRATOR_PAYMENTTERMS_EDIT,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['terms_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_PAYMENTTERMS_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bank('.$row['terms_id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
				$tr = $dbcon -> query("SELECT `terms_id`,`payment_terms`,`terms_status` FROM `pay_terms` WHERE `payment_terms` = '$POST[payment_terms]'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['terms_status'] != 0) {
						$info['terms_status']=0;
						$updateid=update_record('pay_terms', $info,"terms_id=".$r['terms_id'] , $dbcon);
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
					$info['payment_terms']= $POST['payment_terms'];
					$info['payment_days']= $POST['payment_days'];					
					$info['cdate']		= date("Y-m-d H:i:s");
					$info['userid']		= $_SESSION[user_id];
					$inserid=add_record('pay_terms', $info, $dbcon);
							
					$row['res']='';
					if($inserid)
					{
						if(strtolower($POST['model'])=="model")
						{
							$query="select * from pay_terms where terms_id=".$inserid;
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
			$q = $dbcon -> query("SELECT * FROM `pay_terms` WHERE `terms_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
				$info['payment_terms']= $POST['payment_terms'];
				$info['payment_days']= $POST['payment_days'];	
				$info['cdate']		= date("Y-m-d H:i:s");				
				$updateid=update_record('pay_terms', $info,"terms_id=".$POST['eid'] , $dbcon);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['terms_status']='2';
			$updateid=update_record('pay_terms', $info,"terms_id=".$POST['eid'] , $dbcon);
				
			if($updateid)
				echo "1";
			else
				echo "0";
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