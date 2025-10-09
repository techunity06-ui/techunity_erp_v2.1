<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
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
		
	
		if(strtolower($POST['mode']) == "end_process") {
			
			$process_id=$POST['process_id_hid'];
			$process_type_hid=$POST['process_type_hid'];
			$eid=$POST['eid'];
			$date=date("Y-m-d h:i:sa");
			
			if($process_type_hid==1)
			{
				//$grn_status='1';
				$grn_status='0';
			}
			else
			{
				$grn_status='0';
			}
			
			if($POST['machine_no1']!=0)
			{
				if($POST['process_type_hid']=="1"){
					$info['grn_no']				= $POST['grn_no'];
					$info['grn_date']			= date('Y-m-d');
					//$info['vender_id']		= $POST['vender_id'];
					$info['invoice_no']			= $POST['process_no'];
					$info['challan_no']			= $POST['process_no'];
					$info['ref_type']			= '1';
					$info['purchaseorder_id']	= $POST['jobwork_id'];
					$info['remark']				= 'inhouse process';
					$info['ref_no']				= $_POST['request_no'];
					$info['grn_status']			= $grn_status;
					//$info['product_qc']			= $POST['product_qc'];
					
					$info['cdate']				= date("Y-m-d H:i:s"); 
					$info['user_id']			= $_SESSION['user_id'];
					$info['company_id']			= $_SESSION['company_id'];
					
					$inserpoid=add_record('tbl_grn', $info, $dbcon);
					
					$godown_id="";
					$product_qc=0;
					
					
					$info2['purchaseorder_id']	=$POST['jobwork_id'];
					$info2['product_id']		=$POST['product_id_hid'];
					$info2['grn_id']			=$inserpoid;
					$info2['product_qty']		=$POST['machine_no1'];
					$info2['unit_id']			=$POST['process_unit'];
					$info2['grn_godown']		=$godown_id;
					$info2['product_qc']		=$product_qc;
					
					$info2['cdate']				= date("Y-m-d H:i:s");
					$info2['user_id']			= $_SESSION['user_id'];
					$info2['company_id']		= $_SESSION['company_id'];
					
					$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2, $dbcon);
					
					
				$infogtrn['product_id']			= $POST['product_id_hid'];
				$infogtrn['grn_trn_id']			= $tbl_grn_trn_id;
				$infogtrn['jobwork_id']			= $POST['jobwork_id'];
				$infogtrn['product_qty']		= $POST['machine_no1'];
				$infogtrn['cdate']				= date("Y-m-d H:i:s");
				$infogtrn['user_id']			= $_SESSION['user_id'];
				$infogtrn['company_id']			= $_SESSION['company_id'];
				$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon);
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=14 and company_id=".$_SESSION['company_id']);
				
					$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$POST['machine_no1']." where jobwork_id=".$POST['jobwork_id']."");
				}
			}
			
			
			$dbcon->query("update tbl_allocate_re_process set pen_qty=pen_qty-".$POST['machine_no1']." where p_id='$eid'");
			
			
			if($POST['pr_p_qty1']==$POST['machine_no1']){
				$date=date("Y-m-d h:i:sa");
				$dbcon->query("update tbl_allocate_re_process set p_end_time='".$date."',p_status=3 where p_id='$eid'");
			}
			
			
			$info6['process_id']=$POST['process_id_hid'];
			$info6['p_start_time']='';
			$info6['p_end_time']=$date;
			$info6['p_qty']=$POST['machine_no1'];
			$info6['pen_qty']='';
			$info6['p_status']='2';
			$info6['p_ref_id']=$POST['request_no'];
			//$info3['p_ref_type']=$POST['pr_chalan_no'];
			$info6['p_product_id']=$POST['product_id_hid'];
			//$info3['pr_process_type']=$POST['process_type_hid'];
			$info6['j_alloc_process_id']=$eid;
			
			$info6['cdate']			= date("Y-m-d H:i:s");
			$info6['user_id']		= $_SESSION['user_id'];
			$info6['company_id']		= $_SESSION['company_id'];
			
			$inserusrid1=add_record('tbl_jobwork_history', $info6, $dbcon);
			
			
			
			$j_pr_job_id=$POST['j_pr_job_id'];
				
				for($k=0;$k<count($j_pr_job_id);$k++)
				{
					$loop_id=$j_pr_job_id[$k];
					
					$info4['product_type']=$POST['j_ptype'][$k];
					$info4['raw_product_id']=$loop_id;
					$info4['jobwork_id']=$inserusrid1;
					$info4['outward_product_qty']=$POST['j_usable'][$k];
					$info4['outward_product_rate']=$POST['j_prate'][$k];
					$info4['outward_product_amt']=$POST['j_pamount'][$k];
				
					$info4['cdate']			= date("Y-m-d H:i:s");
					$info4['user_id']		= $_SESSION['user_id'];
					$info4['company_id']		= $_SESSION['company_id'];
					
					add_record('tbl_jobworktrn', $info4, $dbcon);
				}
				
				
			echo "1";
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