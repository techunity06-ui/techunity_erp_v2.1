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
				$grn_status='1';
			}
			else
			{
				$grn_status='0';
			}
			
			if($POST['machine_no1']!=0)
			{
				$info['grn_no']			= $POST['grn_no'];
				$info['grn_date']		= date('Y-m-d',strtotime($POST['grn_date']));
				//$info['vender_id']		= $POST['vender_id'];
				$info['ref_type']		= '1';
				$info['purchaseorder_id']= $POST['jobwork_id'];
				$info['remark']			= 'inhouse process';
				$info['ref_no']			= $_POST['request_no'];
				$info['grn_status']			= $grn_status;
				$info['product_qc']			= $POST['product_qc'];
				
				$info['cdate']			= date("Y-m-d H:i:s"); 
				$info['user_id']		= $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];
				
				$inserpoid=add_record('tbl_grn', $info, $dbcon);
				
				$info2['purchaseorder_id']=$POST['jobwork_id'];
				$info2['product_id']=$POST['product_id_hid'];
				$info2['grn_id']=$inserpoid;
				$info2['product_qty']=$POST['machine_no1'];
				
				$info2['cdate']			= date("Y-m-d H:i:s");
				$info2['user_id']		= $_SESSION['user_id'];
				$info2['company_id']		= $_SESSION['company_id'];
				
				add_record('tbl_grn_trn', $info2, $dbcon);
				
			}

			$dbcon->query("update tbl_allocate_process set task_status='2' where p_id='$eid'");
			
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
				
				$deducted_stock=$POST['deducted_stock'];
				$deducted_gd_id=$POST['deducted_gd_id'];
				
				foreach($deducted_stock as $key=>$value)
				{
					if($POST['deducted_stock'][$key]>0)
					{
						$info3['gst_dd_id']=$POST['deducted_gd_id'][$key];
						$info3['gst_pid']=$POST['product_id'][$key];
						$info3['gst_qty']=$POST['deducted_stock'][$key];
						$info3['gst_type']='1';
						$info3['gst_ref_id']=$POST['gst_eid'][$key];
						$info3['gst_date']=date('Y-m-d');
						
						$info3['cdate']			= date("Y-m-d H:i:s");
						$info3['user_id']		= $_SESSION['user_id'];
						$info3['company_id']		= $_SESSION['company_id'];
						
						add_record('tbl_godown_stock_trn', $info3, $dbcon);
					}
				}
			
			/*$info6['pt_alloc_id']	= $eid;			
			$info6['pt_ref_id']	= $POST['request_no'];			
			$info6['pt_product_id']	= $POST['product_id_hid'];			
			$info6['pt_process_id']	= $POST['process_id_hid'];			
			$info6['pt_qty']	= $POST['machine_no1'];			
				
		
			$info6['cdate']		= date("Y-m-d H:i:s");
			$info6['user_id']	= $_SESSION['user_id'];
			$info6['company_id']	= $_SESSION['company_id'];	
			
			add_record('tbl_allocate_process_trn', $info6, $dbcon);
			 */
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