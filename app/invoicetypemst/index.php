<?php

session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
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
			$appData = array();
			$i=1;
			$aColumns = array('invoicetype_id','invoice_type','taxinvoice_start','type_id','exciseinvoice_start','invoice_format','format_value','end_format_value','deletable','cdate', 'status', 'imst.user_id','(select count(invoicetype_id) from tbl_invoice where invoice_status=0 and invoicetype_id=imst.invoicetype_id) as usedformulaid');
			$sIndexColumn = "invoicetype_id";
			$isWhere = array("status = 0".check_user('imst'));
			$sTable = "tbl_invoicetype as imst";			
			$isJOIN = array();
			$hOrder = "imst.invoicetype_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['invoice_type'];
				$row_data[] = $row['taxinvoice_start'];
				if($row['invoice_format']=="1")
				{
					$row_data[] =$row['format_value'].$row['taxinvoice_start'];
					//$row_data[] =($row['invoice_format']==1?$row['format_value'].$row['taxinvoice_start']:$row['taxinvoice_start'].$row['format_value']);
				}
				else if($row['invoice_format']=="2")
				{
					$row_data[] =$row['taxinvoice_start'].$row['format_value'];
				}
				else if($row['invoice_format']=="3")
				{
					$row_data[] =$row['format_value'].$row['taxinvoice_start'].$row['end_format_value'];
				}
				else
				{
					$row_data[] = '';
				}
					if($row['deletable']=='1')
					{
						$delete='';
					}
					else
					{
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_catalog('.$row['invoicetype_id'].')"><i class="fa fa-trash-o"></i></button>';
					 }
					 if($row['type_id']=="1"){
					// $printcheckbox='<input type="checkbox" class="form-control" style="width:26px;" id="allchk'.$id.'" name="chk" value="'.$row["invoicetype_id"].'">';
					 }else{
						$printcheckbox=''; 
					 }
					
				$row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['invoicetype_id'].');"><i class="fa fa-pencil"></i></button> '.$delete . $printcheckbox;
				 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
				$tr = $dbcon -> query("SELECT `invoicetype_id`,`invoice_type`,`status` FROM `tbl_invoicetype` WHERE status=0 and `invoice_type` = '$POST[invoicetype_name]' and company_id=".$_SESSION['company_id']);
				if($tr->num_rows > 0) {
						echo '-1';
				}
				else {
							$info['invoice_type']		= $POST['invoicetype_name'];
							$info['taxinvoice_start']	= $POST['taxinvoicestart'];
							$info['exciseinvoice_start']= $POST['exciseinvoicestart'];
							$info['invoice_format'] 	= $POST['invoice_format'];	
							$info['format_value']		= $POST['format_value'];	
							$info['end_format_value'] 	= $POST['end_format_value'];							
							$info['cdate']				= date("Y-m-d H:i:s");
							$info['user_id']			= $_SESSION['user_id'];
							$info['usertype_id']		= $_SESSION['user_type'];
							$info['company_id']			= $_SESSION['company_id'];
							$inserid=add_record('tbl_invoicetype', $info, $dbcon);
					if($inserid)
						echo "1";
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['mode']) == "preedit") {	
			$q = $dbcon -> query("SELECT * FROM `tbl_invoicetype` WHERE `invoicetype_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		
		else if(strtolower($POST['mode']) == "edit") {
//			if($_POST['token'] == $_SESSION['token'])
			{
							$info['invoice_type']		= $POST['invoicetype_name'];
							$info['taxinvoice_start']	= $POST['taxinvoicestart'];
							$info['exciseinvoice_start']= $POST['exciseinvoicestart'];
							$info['invoice_format']		= $POST['invoice_format'];
							$info['format_value']		= $POST['format_value'];
							$info['end_format_value']	= $POST['end_format_value'];
							$info['cdate']			= date("Y-m-d H:i:s");
							$info['user_id']		= $_SESSION['user_id'];
							$info['usertype_id']	= $_SESSION['user_type'];
							$updateid=update_record('tbl_invoicetype', $info,"invoicetype_id=".$POST['eid'] , $dbcon);
				
				
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['mode']) == "invoice_series_same") {
			$id=implode(",",$POST['typeid']);
		 $upd="update tbl_invoicetype set series_same=0 where company_id=".$_SESSION['company_id'];
				$rose = $dbcon -> query($upd);
	 $query="SELECT MAX(`taxinvoice_start`) as series FROM tbl_invoicetype WHERE invoicetype_id in (".$id.") and company_id=".$_SESSION['company_id'];
			$res = $dbcon -> query($query);
		$ro=mysqli_fetch_assoc($res);
			//echo $ro['series'];
			  $update="update tbl_invoicetype set series_same=1,taxinvoice_start=".$ro['series']." where invoicetype_id in (".$id.") and company_id=".$_SESSION['company_id'];
				$ros = $dbcon -> query($update);
				if($ros){
		   $row['status']=1;
				}else{
					 $row['status']=0;
				}	
			echo json_encode($row);	
		}
		else if(strtolower($POST['mode']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$info['status']='2';
					$updateid=update_record('tbl_invoicetype', $info,"invoicetype_id=".$POST['eid'] , $dbcon);
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