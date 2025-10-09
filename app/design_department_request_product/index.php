<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			
		$appData = array();
		$i=1;
		$aColumns = array('product_id', 'product_type', 'product_name', 'product_hsn','zmst.cdate', 'product_status', 'zmst.user_id');
		$sIndexColumn = "product_id";
		$isWhere = array("product_status = 0");
		$sTable = "product_mst as zmst";			
		$isJOIN = array();
		$hOrder = "zmst.product_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = get_product_type_by_id($dbcon,$row['product_type']);
			$row_data[] = $row['product_name']; 
			$row_data[] = $row['product_hsn_code']; 
			
			$edit_btn='';$delete_btn='';
			if($edit_btn_per){
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'product_edit/'.$row['product_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_product('.$row['product_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "check_poreq_status") {
		
		//$chk=check_requested($dbcon,$POST['product_id'],$POST['po_req_no']);
		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		
		$bom_q1="SELECT rp_id FROM `tbl_request_product` WHERE sp_id=".$work_order_id;
		$bom_rel_q1=mysqli_fetch_assoc($dbcon->query($bom_q1));
		
		$query="select * from tbl_request_product as i
				where i.rp_id=".$bom_rel_q1['rp_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
		
		if($row['rp_po_qty']!=0){
			echo "1";
		}else{
			echo "0";
		}
	}
	else if(strtolower($POST['mode']) == "main_po_reqdata") {
		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		
		$bom_q1="SELECT rp_id FROM `tbl_request_product` WHERE sp_id=".$work_order_id;
		$bom_rel_q1=mysqli_fetch_assoc($dbcon->query($bom_q1));
		
		$query="select * from tbl_request_product as i
				where i.rp_id=".$bom_rel_q1['rp_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			
			$info['rp_req_date']		=date('Y-m-d');
			//$info['rp_req_qty']			=$POST['req_qty'];
			$info['rp_po_qty']			=$POST['rp_po_qty'];
			//$info['in_process_qty']		=$POST['in_process_qty_main'];
			
			$info['status']				=0;
			$info['reject_status']		=$POST['reject_status'];
			$info['cdate']				=date('Y-m-d H:i:s');
			$info['user_id']			=$_SESSION['user_id'];
			$info['company_id']			=$_SESSION['company_id'];
			
			if($POST['po_qty']>"0"){
				$indent_no=load_common_no($dbcon,17);
				update_common_no($dbcon,17);
				$info['indent_status']		= 1;
				$info['indent_no']			= $indent_no;
				$info['indent_date']		= date('Y-m-d');
			}
			
			if(!empty($POST['sales_order_trn_id'])){
				$info['sales_order_trn_id']		= $POST['sales_order_trn_id'];
			}
			
			$updateid=update_record("tbl_request_product", $info,"rp_id=".$bom_rel_q1['rp_id'] , $dbcon);
			
			$all_request_data_use=all_request_data_use($dbcon,$bom_rel_q1['rp_id'],$info['rp_po_qty']);
			
			if(!empty($POST['sales_order_trn_id'])){
				$query_invoicetype = $dbcon->query("UPDATE tbl_sales_ordertrn SET work_order_qty = work_order_qty +".$POST['rp_po_qty']." WHERE sales_ordertrn_id = ".$POST['sales_order_trn_id']);
			}
			
		//var_dump($all_request_data_use);
		echo 1;
	}
	else if(strtolower($POST['mode']) == "main_po_reqdata_old") {
		//rp_req_qty
			$info['rp_req_no']			=$POST['po_req_no'];
			$info['rp_pid']				=$POST['eid'];
			$info['rp_req_date']		=date('Y-m-d',strtotime($POST['po_req_date']));
			$info['rp_req_qty']			=$POST['rp_po_qty']; // total
			$info['rp_po_qty']			=$POST['rp_po_qty'];
			$info['in_process_qty']		=0; //process qty
			$info['process_unit']		=get_pro_field($dbcon,$POST['eid'],'product_base_unit');
			$info['purchase_unit']		=get_pro_field($dbcon,$POST['eid'],'product_conv_qty');
			//$info['out_process_qty']	=$POST['out_process_qty'];
			$info['main_request']	="1";
			
			$info['rp_req_type']='min_max';
			$info['rp_po_req_no']='101';
			$info['rp_process_req_no']='201';
			$info['cdate']=date('Y-m-d H:i:s');
			$info['user_id']=$_SESSION['user_id'];
			$info['company_id']=$_SESSION['company_id'];
			
			$inserid=add_record('tbl_request_product', $info, $dbcon);
			
				$rate=get_pro_field($dbcon,$pr_id,'product_purchase_rate');
					$total=$po_qty*$rate;
					
					$infpotrn['purchaseorder_id']	= '0';
					$infpotrn['product_type']		= '';
					$infpotrn['product_id']			= $POST['eid'];
					$infpotrn['product_qty']		= $POST['rp_po_qty'];
					$infpotrn['product_rate']		= $rate;
					$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$POST['eid'],'product_hsn');
					$infpotrn['unit_id']			= get_pro_field($dbcon,$POST['eid'],'product_conv_unit');
					$infpotrn['product_amount']		= $total;
					$infpotrn['total']				= $total;
					$infpotrn['parent_pro']			= 0;
					$infpotrn['main_pro_status']	= 1;//Requested products
					$infpotrn['user_id']			= $_SESSION['user_id'];
					$infpotrn['po_ref_id']			= $inserid;
					//$infpotrn['po_ref_id']			= $inserid_alloc;
					$infpotrn['po_ref_type']			= '0';
					$infpotrn['po_bom_id']				= '';
					$infpotrn['po_bom_trn_id']			= '';
					$infpotrn['mdate']	= date('Y-m-d');
			
					$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon);
					
					$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '10' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
	}
	else if(strtolower($POST['mode']) == "add_old") {
		
			$info['rp_req_no']			=$POST['po_req_no'];
			$info['rp_pid']				=$POST['eid'];
			$info['rp_req_date']		=date('Y-m-d',strtotime($POST['po_req_date']));
			$info['rp_req_qty']			=$POST['rp_req_qty'];
			$info['rp_po_qty']			=$POST['rp_po_qty'];
			$info['in_process_qty']		=$POST['in_process_qty'];
			$info['process_unit']		=get_pro_field($dbcon,$POST['eid'],'product_base_unit');
			$info['purchase_unit']		=get_pro_field($dbcon,$POST['eid'],'product_conv_qty');
			$info['out_process_qty']	=$POST['out_process_qty'];
			$info['main_request']		= "1";
			
			$info['rp_req_type']='min_max';
			$info['rp_po_req_no']='101';
			$info['rp_process_req_no']='201';
			$info['cdate']=date('Y-m-d H:i:s');
			$info['user_id']=$_SESSION['user_id'];
			$info['company_id']=$_SESSION['company_id'];
			//var_dump("1212");
			if($POST['main_poreq_status']!='1'){
				$inserid=add_record('tbl_request_product', $info, $dbcon);
				
				
			}else{
				$rpid=check_mainrequested($dbcon,$POST['eid'],$POST['po_req_no']);
				if(!empty($rpid)){
					$updateid11=update_record('tbl_request_product', $info,"rp_id=".$rpid , $dbcon);
					$inserid=$rpid;
				}else{
					$inserid=add_record('tbl_request_product', $info, $dbcon);
				}
			}
			$q=$POST['po_req_no'];
			$query_invoicetype1 = $dbcon->query("UPDATE tbl_request_product SET perent_id = ".$inserid." WHERE rp_req_no = '".$q."' and perent_id='0' and rp_id!=".$inserid." ");
			//var_dump($inserid);
			
			//$de=add_request_reserve_stock($dbcon,$info['rp_pid']);
			
			if($inserid){
				
				echo "1";
				
				if($POST['in_process_qty']!='')
				{
					if($POST['in_process_qty']!="0"){
						$process=get_product_process($dbcon,$POST['eid']);
						
						$process_pr=json_decode($process);
					
						$process_id=$process_pr->process_id;
						$process_type=$process_pr->process_type;
						$process_priority=$process_pr->process_priority;
						
						$info5['process_id']			= $process_id;			
						$info5['p_qty']					= $POST['in_process_qty'];		
						$info5['pen_qty']				= $POST['in_process_qty'];		
						$info5['p_ref_id']				=$inserid;		
						$info5['p_ref_type']			='process_request';		
						$info5['p_product_id']			= $POST['eid'];	
						$info5['pr_process_type']		= $process_type;
						$info5['process_priority']		= $process_priority;
						$info5['previous_process_id']	= 0;
						$info5['process_unit']	= get_pro_field($dbcon,$POST['eid'],'product_base_unit');
						
						$info5['cdate']					= date("Y-m-d H:i:s");
						$info5['user_id']				= $_SESSION['user_id'];
						$info5['company_id']			= $_SESSION['company_id'];	
						
						$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon);
					}
				}
				
			if($POST['main_poreq_status']!='1'){
				if($POST['rp_po_qty']!='0')
				{
					if($POST['rp_po_qty']!='')
				{
					
					$rate=get_pro_field($dbcon,$pr_id,'product_purchase_rate');
					$total=$po_qty*$rate;
					
					$infpotrn['purchaseorder_id']	= '0';
					$infpotrn['product_type']		= '';
					$infpotrn['product_id']			= $POST['eid'];
					$infpotrn['product_qty']		= $POST['rp_po_qty'];
					$infpotrn['product_rate']		= $rate;
					$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$POST['eid'],'product_hsn');
					$infpotrn['unit_id']			= get_pro_field($dbcon,$POST['eid'],'product_conv_unit');
					$infpotrn['product_amount']		= $total;
					$infpotrn['total']				= $total;
					$infpotrn['parent_pro']			= 0;
					$infpotrn['main_pro_status']	= 1;//Requested products
					$infpotrn['user_id']			= $_SESSION['user_id'];
					$infpotrn['po_ref_id']			= $inserid;
					//$infpotrn['po_ref_id']			= $inserid_alloc;
					$infpotrn['po_ref_type']			= '0';
					$infpotrn['po_bom_id']				= '';
					$infpotrn['po_bom_trn_id']			= '';
					$infpotrn['mdate']	= date('Y-m-d');
			
					$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon);
					
					$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '10' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);

				}
			}
			}
				if(!empty($POST['smode'])){
					$query11="select IFNULL(sum(qc_rejected-qc_rejected_used),0) as qty,qctrn_id,qc_rejected_used from tbl_qc_trn where qc_rejected!=0 and qc_rejected_used<qc_rejected and qc_status=0 and qc_product=".$POST['eid'];
					$rs11=$dbcon->query($query11);
					while($row111=mysqli_fetch_array($rs11)){
						$totalqty=$POST['rp_req_qty'];
						if($row111['qty']>0){
							if($totalqty>=$row111['qty']){
								$query_invoicetype = $dbcon->query("UPDATE tbl_qc_trn SET qc_rejected_used = qc_rejected_used +".$row111['qty']." WHERE qctrn_id = ".$row111['qctrn_id']."");
								$totalqty=$totalqty-$row111['qty'];
							}else{
								$used_qt=$row111['qty']-$totalqty;
								$query_invoicetype = $dbcon->query("UPDATE tbl_qc_trn SET qc_rejected_used = qc_rejected_used +".$used_qt." WHERE qctrn_id = ".$row111['qctrn_id']."");
								$totalqty=$totalqty-$used_qt;
							}
						}
					}
				}
			}
			else{
				
				echo "0";
			}
		
		//echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "purchase_mode") {
			
		$set_pro="SELECT product_base_unit,product_conv_unit FROM `product_mst` WHERE product_status=0 AND product_id='".$POST['eid']."'";
			$setpro_rel=mysqli_fetch_assoc($dbcon->query($set_pro));
			
			$info['po_req_date']			=date('Y-m-d',strtotime($POST['po_req_date']));
			$info['rp_req_qty']				=$POST['rp_req_qty'];
			$info['in_process_qty_main']	=$POST['in_process_qty_main'];
			$info['rp_po_qty']				=$POST['rp_po_qty'];
			$info['product_id']				=$POST['eid'];
			
			$info['vendor_id']				=$POST['cust_id'];
			$info['sales_order_date']		=date('Y-m-d',strtotime($POST['sales_order_date']));
			$info['po_no']					=$POST['po_no'];
			$info['po_date']				=date('Y-m-d',strtotime($POST['po_date']));
			$info['sales_order_no']			=$POST['sales_order_no'];
			
			$info['cdate']					= date('Y-m-d H:i:s');
			$info['user_id']				= $_SESSION['user_id'];
			$info['company_id']				= $_SESSION['company_id'];
			
			$info['po_req_no']				= load_series_no($dbcon,9);
			$info['sp_status']				= 2;
			
			$inserid=add_record('tbl_set_main_process', $info, $dbcon,$POST['branch_id']);
			
			$info_su['sp_id']				= $inserid;
			$info_su['sr_no']				= 0;
			$info_su['rp_pid']				= $POST['eid'];//product_id
			$info_su['rp_req_qty']			= $POST['rp_req_qty'];//required qty
			$info_su['rp_po_qty']			= $POST['rp_po_qty'];//po qty
			$info_su['in_process_qty']		= "";//process qty
			$info_su['rp_req_type']			= "min_max";//type
			$info_su['process_unit']		= $setpro_rel['product_base_unit'];
			$info_su['purchase_unit']		= $setpro_rel['product_conv_unit'];
			$info_su['perent_id']			= 0;
			$info_su['main_request']		= 1;
			$info_su['status']				= 0;
			
			$info_su['rp_req_date']			= date('Y-m-d');
			
			$info_su['cdate']				= date('Y-m-d H:i:s');
			$info_su['user_id']				= $_SESSION['user_id'];
			$info_su['company_id']			= $_SESSION['company_id'];
			
			$info_su['reject_status']		= $POST['reject_status'];
			
			if($info_su['rp_po_qty']>"0"){
				$indent_no=load_common_no($dbcon,17);
				update_common_no($dbcon,17);
				$info_su['indent_status']		= 1;
				$info_su['indent_no']			= $indent_no;
				$info_su['indent_date']			= date('Y-m-d');
			}
			
			$inserid_sub1=add_record('tbl_request_product', $info_su, $dbcon,$POST['branch_id']);
			
			if($POST['smode']=="add_rej"){
				reject_request_qty_update($dbcon,$info_su['rp_po_qty'],$inserid_sub1);
			}
				
			if($POST['smode']=="add_all"){
				$all_request_data_use=all_request_data_use($dbcon,$inserid_sub1,$info_su['rp_po_qty']);
			}
			
		if(!empty($POST['sales_order_trn_id'])){
			$info_soallo['sales_ordertrn_id']		= $POST['sales_order_trn_id'];	
			$info_soallo['product_id']				= $POST['eid'];//product_id	
			$info_soallo['product_qty']				= $info_su['rp_req_qty'];	
			$info_soallo['request_id']				= $inserid_sub1;	
			$info_soallo['unit_id']					= $setpro_rel['product_conv_unit'];	
			$info_soallo['user_id']					= $_SESSION['user_id'];	
			$info_soallo['cdate']					= date("Y-m-d H:i:s");	
			$info_soallo['company_id']				= $_SESSION['company_id'];	
					
			$inser_so_allo=add_record('tbl_sales_order_production_trn', $info_soallo, $dbcon,$POST['branch_id']);
		}

			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
		if($inserid_sub1){
			echo 1;
		}else{
			echo 2;
		}
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		
		$info_wo['sp_status']=2;
		$updateid12=update_record("tbl_set_main_process", $info_wo,"sp_id=".$work_order_id , $dbcon);
		
		$bom_q1="SELECT rp_id,rp_pid FROM `tbl_request_product` WHERE main_request=1 and sp_id=".$work_order_id;
		$bom_rel_q1=mysqli_fetch_assoc($dbcon->query($bom_q1));
		
		$query="select * from tbl_request_product as i
				where i.rp_id=".$bom_rel_q1['rp_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			
			$info['rp_req_date']		=date('Y-m-d');
			$info['rp_req_qty']			=$POST['rp_req_qty'];
			$info['rp_po_qty']			=$POST['rp_po_qty'];
			$info['in_process_qty']		=$POST['in_process_qty'];
			$info['reject_status']		=$POST['reject_status'];
			
			$info['status']				=0;
			$info['cdate']				=date('Y-m-d H:i:s');
			$info['user_id']			=$_SESSION['user_id'];
			$info['company_id']			=$_SESSION['company_id'];
			
			if($info['rp_po_qty']>"0"){
				$indent_no=load_common_no($dbcon,17);
				update_common_no($dbcon,17);
				$info['indent_status']		= 1;
				$info['indent_no']			= $indent_no;
				$info['indent_date']		= date('Y-m-d');
			}
			if($info['in_process_qty']>"0"){
				$indent_no=load_common_no($dbcon,JOBCARD);
				update_common_no($dbcon,JOBCARD);
				$info['job_card_status']		= 1;
				$info['job_card_no']			= $indent_no;
				$info['job_card_date']		= date('Y-m-d');
			}
			if(!empty($POST['sales_order_trn_id'])){
				$info['sales_order_trn_id']		= $POST['sales_order_trn_id'];
			}
			$updateid=update_record("tbl_request_product", $info,"rp_id=".$bom_rel_q1['rp_id'] , $dbcon);
			
			if($POST['in_process_qty']!='')
			{
				if($POST['in_process_qty']!="0"){
					$queryw="select * from tbl_product_process where status=0 AND product_id=".$row['rp_pid'];
					$rs_custw=$dbcon->query($queryw);	
					while($relw=mysqli_fetch_array($rs_custw)){
						$infow['product_id']		= $relw['product_id'];
						$infow['rp_id']				= $row['rp_id'];
						$infow['process_priority']	= $relw['process_priority'];;
						$infow['process_time']		= $relw['process_time'];
						$infow['process_type']		= $relw['process_type'];
						$infow['process_opening']	= $relw['process_opening'];
						$infow['process_id']		= $relw['process_id'];
						$infow['cdate']				= date('Y-m-d');
						$infow['user_id']			= $_SESSION['user_id'];
						$infow['company_id']		= $_SESSION['company_id'];
						
						$inserid_a2=add_record('tbl_wororder_product_process', $infow, $dbcon, $POST['branch_id']);

						/*
						Code By Umair : 05/11/2020
						Comment: Insert Work Order Resource Allocation In tbl_work_order_resource_allocate table
						*/
						if($relw['process_type']=='1'){
							$resource_id = $relw['resource_id'];
							$request_id = $row['rp_id']; 
							$process_id = $relw['process_id'];
							$product_id = $relw['product_id'];
							$qty = $POST['in_process_qty'];
							$time_per_qty = $relw['process_time'];
							
							$action_type = 'add';
							$edit_id = '';
							work_order_resource_allocate($dbcon, $resource_id, $request_id, $process_id, $product_id, $qty, $time_per_qty, $edit_id, $action_type, '', $POST['branch_id']);

						}
						
					}
				}
			}
			 if($POST['in_process_qty']!='')
			{
				if($POST['in_process_qty']!="0"){
					$process=get_product_process($dbcon,$row['rp_id'],$row['rp_pid']);
					$process_pr=json_decode($process);
					
					$process_id=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;

					/*Get Resource ID*/
					$resourceinfo=get_resource_from_product_process($dbcon,$row['rp_pid'],$process_id, $where=null);
				
					$info5['process_id']		= $process_id;			
					$info5['p_start_time']		= '';		
					$info5['p_end_time']		= '';		
					$info5['p_qty']				= $POST['in_process_qty'];		
					$info5['pen_qty']			= $POST['in_process_qty'];		
					$info5['process_unit']		= $row['process_unit'];		
					$info5['p_ref_id']			= $row['rp_id'];		
					$info5['p_ref_type']		= 'process request';		
					$info5['p_product_id']		= $row['rp_pid'];		
					$info5['pr_process_type']	= $process_type;		
					$info5['process_priority']	= $process_priority;		
					$info5['previous_process_id']= 0;

					if($resourceinfo['process_type']=='1'){		
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}	

					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					
					$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon,$POST['branch_id']);
				}
			}
			//var_dump($info['rp_req_qty']);
			//var_dump($bom_rel_q1['rp_id']);
			if($POST['smode']=="add_rej"){
				reject_request_qty_update($dbcon,$info['rp_req_qty'],$bom_rel_q1['rp_pid']);
			}
				//echo $bom_rel_q1['rp_id'];
				//echo "222";
				
			if($POST['smode']=="add_all"){
				$all_request_data_use=all_request_data_use($dbcon,$bom_rel_q1['rp_id'],$info['rp_po_qty']);
			}
			/* if(!empty($POST['sales_order_trn_id'])){
				$query_invoicetype = $dbcon->query("UPDATE tbl_sales_ordertrn SET work_order_qty = work_order_qty +".$info['in_process_qty']." WHERE sales_ordertrn_id = ".$POST['sales_order_trn_id']);
			} */
			
		if(!empty($POST['sales_order_trn_id'])){
			$info_soallo['sales_ordertrn_id']		= $POST['sales_order_trn_id'];	
			$info_soallo['product_id']				= $row['rp_pid'];	
			$info_soallo['product_qty']				= $info['rp_req_qty'];	
			$info_soallo['request_id']				= $bom_rel_q1['rp_id'];	
			$info_soallo['unit_id']					= $bom_rel_q1['process_unit'];	
			$info_soallo['user_id']					= $_SESSION['user_id'];	
			$info_soallo['cdate']					= date("Y-m-d H:i:s");	
			$info_soallo['company_id']				= $_SESSION['company_id'];	
					
			$inser_so_allo=add_record('tbl_sales_order_production_trn', $info_soallo, $dbcon);
		}
			
		//var_dump($all_request_data_use);
		echo 1;
		
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `product_mst` WHERE `product_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
			$info['product_type']	= $POST['product_type'];							
			$info['product_name']	= $POST['product_name'];							
			$info['product_desc']	= $_POST['product_desc'];							
			$info['product_icode']	= $_POST['product_icode'];							
			$info['product_hsn']= $POST['product_hsn'];							
			$info['product_purchase_rate']= $POST['product_purchase_rate'];							
			$info['product_sale_rate']= $POST['product_sale_rate'];							
			$info['product_base_unit']= $POST['product_base_unit'];	

			$info['product_base_qty']= $POST['product_base_qty'];
			$info['product_conv_unit']= $POST['product_conv_unit'];
			$info['product_conv_qty']= $POST['product_conv_qty'];
			
			$info['product_gst']= $POST['product_gst'];							
			$info['product_sale_gst']= $POST['product_sale_gst'];							
			$info['product_purchase_gst']= $POST['product_purchase_gst'];							
			$info['product_opening']= $POST['product_opening'];							
			$info['product_opening_valuation']= $POST['product_opening_valuation'];							
			$info['product_min_stock']= $POST['product_min_stock'];
			$info['product_max_stock']= $POST['product_max_stock'];				
			$info['product_category']= $POST['product_category'];							
			$info['product_barcode']= $POST['product_barcode'];							
			$info['multi_branch']= $POST['multi_branch'];							
			$info['product_status']= '0';							
			$info['count_stock']= $POST['count_stock'];							
			$info['product_making_time']= $POST['product_making_time'];							
			$info['product_check']= implode(",",$POST['product_check']);
			$info['product_setting_check']= implode(",",$POST['product_setting_check']);		

			$info['product_width']= $POST['product_width'];				
			$info['product_height']= $POST['product_height'];				
			$info['product_thickness']= $POST['product_thickness'];				
			$info['product_density']= $POST['product_density'];				
			$info['product_kg']= $POST['product_kg'];
			$info['product_specification']= $POST['product_specification'];					

							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $POST['branchid'];
			
			$updateid=update_record('product_mst', $info,"product_id=".$POST['eid_main'] , $dbcon);
			
			//$dbcon->query("update tbl_product_code_series set pr_code_series='$POST[product_icode_code]' WHERE pr_type='$POST[product_type]'");
			
			$resp['msg'] = "2";
			
			echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$chk_arr[]=array("complaint_trn_id","tbl_complaint_trn","complaint_trn_status=0 and product_id=".$POST['eid']); 
		$chk_arr[]=array("bom_trn_id","tbl_bomtrn","bom_trn_status=0 and product_id=".$POST['eid']); 
		
		$chk_resp=check_delete_trn($dbcon,$chk_arr);
		if($chk_resp)
		{
			echo "-1";
		}
		else
		{
			$info['product_status']='2';
			$updateid=update_record('product_mst', $info,"product_id=".$POST['eid'] , $dbcon);

			if($updateid)
				echo "1";
			else
				echo "0"; 
		}
	}
	else if(strtolower($POST['mode']) == "add_unit_converter") {
			
			
			$info1['unit_alt_qty']= $POST['utab_alt_qty'];
			$info1['unit_alt_unit']= $POST['utab_alt_unit'];
			$info1['unit_basic_qty']= $POST['utab_basic_qty'];
			$info1['unit_basic_unit']= $POST['utab_basic_unit'];
			$info1['unit_product']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_unit';$tableid='unit_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_unit_converter") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,unit.unit_name as uname,unit1.unit_name as uname1 from tbl_product_unit as mst 
				left join unit_mst as unit on unit.unitid=mst.unit_alt_unit  left join unit_mst as unit1 on unit1.unitid=mst.unit_basic_unit
				where mst.user_id=".$_SESSION['user_id']." and mst.unit_product='$POST[product_id]' order by unit_id Desc";
			}
			else{
				$query="select mst.*,unit.unit_name as uname,unit1.unit_name as uname1 from tbl_product_unit as mst 
				left join unit_mst as unit on unit.unitid=mst.unit_alt_unit  left join unit_mst as unit1 on unit1.unitid=mst.unit_basic_unit
				where mst.user_id=".$_SESSION['user_id']." and mst.unit_product='0' order by unit_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Alt Qty.</th>
							<th width="10%" class="text-center">Alt Unit</th>
							<th width="15%" class="text-center">Base Qty.</th>
							<th width="15%" class="text-center">Base Unit</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['unit_alt_qty'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['uname'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['unit_basic_qty'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['uname1'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_unit('.$rel['unit_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_unit('.$rel['unit_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		else if(strtolower($POST['mode'])== "preedit_unit")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_unit WHERE unit_id	= '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_unit")
		{
			
			$deleteid=delete_record('tbl_product_unit', "unit_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
	
		else if(strtolower($POST['mode']) == "add_branch_stock") {
			
			$bstock=$POST['bstock'];
			$bid=$POST['bid'];
			$form_mode=$POST['form_mode'];
			$pid=$POST['pid'];
			
			for($i=0;$i<count($bstock);$i++)
			{
				$q=$dbcon->query("select branch_id,product_id from tbl_branch_product_stock where branch_id='$bid[$i]' and product_id='$pid'");
				$count=mysqli_num_rows($q);
				
				$info['product_stock']=$bstock[$i];
				$info['branch_id']=$bid[$i];
				$info['user_id']=$_SESSION['user_id'];
				$info['cdate']=date("Y-m-d h:i:s");
				$info['company_id']=$_SESSION['company_id'];
				
				$table='tbl_branch_product_stock';$tableid='branch_product_stock_id';
				
				if($count>0)
				{
					$updateid=update_record($table, $info,"branch_id='$bid[$i]' and product_id='$pid'", $dbcon);	
				}else{
					
					$inserid=add_record($table, $info, $dbcon);
				}
			}
			print_r($bid);
			
		}
		else if(strtolower($POST['mode']) == "add_product_image_temp") {
			
			 $test = explode('.', $_FILES["file"]["name"]);
			 $ext = end($test);
			 $name = rand(100, 999) . '.' . $ext;
			 $path='../../view/upload/product_images/';
			 $location = $path . $name;  
			 move_uploaded_file($_FILES["file"]["tmp_name"], $location);
			 
			 $info1['im_name']=$name;
			 $info1['cdate']=date("Y-m-d");
			 $info1['user_id']			= $_SESSION['user_id'];
			 $info1['branch_id']			= $POST['branchid'];
			 $info1['im_product']			= $POST['pid'];
			
			 $table='tbl_product_images';$tableid='img_id';
			
			 $inserid=add_record($table, $info1, $dbcon);
			
			 echo get_images_product($dbcon,'0');
			 
			
		}
		
		else if(strtolower($POST['mode']) == "load_product_images") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='$POST[product_id]' order by img_id Desc";
			}
			else{
				
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='0' order by img_id Desc";
			}	
				$rel=$dbcon->query($q);
				$path='view/upload/product_images/';
				$str="";
				$str.="<table></tr>";
				while($row  = mysqli_fetch_assoc($rel))
				{
					$str.='<td>
						<a onclick="delete_data_image('.$row['img_id'].');" href="#">
							<div class="img-wrap">
								<span class="close">&times;</span>
								<img src="'.ROOT.'view/img/close_img.jpg" width="30" height="30" class="img-thumbnail">
							</div>
							<img src="'.ROOT.$path.$row['im_name'].'" height="150" width="225" class="img-thumbnail" />
						</a>
					</td>';
				}
				$str.="</tr></table>";
				echo $str;
			
		    
		}
		else if(strtolower($POST['mode'])== "delete_data_image")
		{
			
			$deleteid=delete_record('tbl_product_images', "img_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		// Party Purchase
		
		else if(strtolower($POST['mode']) == "add_party_purchase") {
			
			
			$info1['party_id']= $POST['party_id'];
			$info1['party_rate']= $POST['party_rate'];
			$info1['party_product']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_party_purchase';$tableid='party_purchase_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_party_purchase") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.l_name from tbl_product_party_purchase as mst 
				left join tbl_ledger as p on p.l_id=mst.party_id where mst.user_id=".$_SESSION['user_id']." and mst.party_product='$POST[product_id]' order by mst.party_purchase_id Desc";
			}
			else{
				$query="select mst.*,p.l_name from tbl_product_party_purchase as mst 
				left join tbl_ledger as p on p.l_id=mst.party_id where mst.user_id=".$_SESSION['user_id']." and mst.party_product='0' order by mst.party_purchase_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
				
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Party</th>
							<th width="10%" class="text-center">Rate</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['l_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['party_rate'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_party_purchase('.$rel['party_purchase_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_party_purchase('.$rel['party_purchase_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "preedit_party")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_party_purchase WHERE party_purchase_id	= '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_party")
		{
			
			$deleteid=delete_record('tbl_product_party_purchase', "party_purchase_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		
		
		// Product Parameter
		
		else if(strtolower($POST['mode']) == "add_param_value") {
			
			
			$info1['param_id']= $POST['param_id'];
			$info1['param_value']= $POST['param_value'];
			$info1['product_id']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			
			$table='tbl_product_parameter';$tableid='pr_param_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_product_param") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.p_name from tbl_product_parameter as mst 
				left join tbl_qc_param as p on p.p_id=mst.param_id where mst.user_id=".$_SESSION['user_id']." and mst.product_id='$POST[product_id]'";
			}
			else{
				$query="select mst.*,p.p_name from tbl_product_parameter as mst 
				left join tbl_qc_param as p on p.p_id=mst.param_id where mst.user_id=".$_SESSION['user_id']." and mst.product_id='0' ";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
				
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Parameter</th>
							<th width="10%" class="text-center">Value</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['p_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['param_value'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_product_param('.$rel['pr_param_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_param('.$rel['pr_param_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "preedit_param")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_parameter WHERE pr_param_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_param")
		{
			
			$deleteid=delete_record('tbl_product_parameter', "pr_param_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_product_code")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_code_series WHERE pr_type = '$POST[pcode]'");
			$r = $q->fetch_assoc();
			
			$pr_series=$r['pr_code_series']+1;
			$short_code=$r['pr_code_short'];
			
			$res['series']=$short_code."".sprintf('%05d',$pr_series);
			$res['code']=$pr_series;
			
			echo json_encode($res);
		}
		
		
		
		// Process Parameter
		
		else if(strtolower($POST['mode']) == "add_process_value") {
			
			
			$info1['process_id']= $POST['process_id'];
			$info1['process_priority']= $POST['process_priority'];
			$info1['product_id']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			
			$table='tbl_product_process';$tableid='pr_process_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_product_process") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.user_id=".$_SESSION['user_id']." and mst.status=0 AND  mst.product_id='$POST[product_id]'";
			}
			else{
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.user_id=".$_SESSION['user_id']." and  mst.status=0 AND  mst.product_id='0' ";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
				
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Process</th>
							<th width="10%" class="text-center">Priority</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['process_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['process_priority'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_product_process('.$rel['pr_process_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_process('.$rel['pr_process_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where type_id='9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno'] = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			
			
		}
		else if(strtolower($POST['mode']) == "get_tree_request_new") {
			
			if(empty($POST['sales_order_trn_id'])){
				$bom="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
			}else{
			 $bom="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and sales_order_trn_id=".$POST['sales_order_trn_id']." and company_id=".$_SESSION['company_id'];
			}
			
			$bom_rel=mysqli_fetch_assoc($dbcon->query($bom));
			
			$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.image_name FROM `tbl_request_product` as rpro
					left join product_mst as pro on pro.product_id=rpro.rp_pid
					left join tbl_category as tc on pro.product_category=tc.cat_id
					left join unit_mst as bunit on bunit.unitid=rpro.process_unit
					left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
					WHERE main_request=0 and rpro.status in (0,3) AND rpro.sp_id=".$bom_rel['sp_id']; die;
			$result=$dbcon->query($bom1);
			while($rel=mysqli_fetch_assoc($result)){
					if($rel['status']==3){
						$request_button='<a class="btn btn-primary dispbtn" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].');" ><i class="fa fa-paper-plane"></i> Request</a>';
					}else{
						$request_button='<a class="btn btn-danger dispbtn" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					}
				$bom2="SELECT status,main_request,rp_req_qty,in_process_qty FROM `tbl_request_product` WHERE status!=2 AND rp_id=".$rel['perent_id'];
				$bom_rel2=mysqli_fetch_assoc($dbcon->query($bom2));
				if($bom_rel2['main_request']!="1"){
					if($bom_rel2['status']=="3"){
						$request_button="";
					}else{
						
					}
				}
				$cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["purchase_unit"]);
				$rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"]);
				$actualstock=$cstock-$rstock;
				if($rel["status"]==0){
					$reserv_read_only="readonly";
					$po_read_only="readonly";
					$process_read_only="readonly";
					$req_read_only="readonly";
					$req_qty=$rel['rp_req_qty'];
				}else{
					$reserv_read_only="";
					$po_read_only="";
					$process_read_only="";
					$req_read_only="";
					
					if($bom_rel2['in_process_qty']!=0){
						$req_qty=$bom_rel2['in_process_qty']*$rel["req_qty_one"];
					}else{
						$req_qty=$bom_rel2['rp_req_qty']*$rel["req_qty_one"];
					}
					$req_qty=round($req_qty,4);
					
					if($actualstock<=0){
						$reserv_read_only="readonly";
					}
				}
			if($rel["status"]!=0){	
				$pr_setting_arr=explode(",",$rel['product_setting_check']);
				
				if(in_array("process_product",$pr_setting_arr))
				{
					$process_read_only="";
					$process_qty=$req_qty;
					$po_qty="";
				}
				else
				{
					$process_read_only="readonly";
					$process_qty="";
					$po_qty=$req_qty;
				}
			}else{
				$process_qty=$rel["in_process_qty"];
				$po_qty=$rel["rp_po_qty"];
			}
			
			$po_qty_sh="'po_qty'";
			$req_qty_sh="'req_qty'";
			$res_qty_sh="'res_qty'";
			$process_qty_sh="'process_qty'";
			$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
			
			if($rel['image_name']!=null){
				//$image_name1 = '<a href="'.ROOT.'view/upload/product_images/'.$rel1["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$rel1['image_name'].'" style="width: 60px;height: 50px;"></a>';
				$image_name1 = '<img src="'.ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;">';
			}else{
				$image_name1 = '';
			}
				echo '<tr>
						<td>'.$rel["sr_no"].'</td>
						<td>'.$rel["product_name"].'</td>
						<td>'.$image_name1.'</td>
						<td>'.$cat_name.'</td>
						<td>'.$rel["product_min_stock"].'</td>
						<td>
							<input type="number" min="0" class="form-control" name="current_stock'.$rel["rp_id"].'" id="current_stock'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)"  value="'.$actualstock.'" readonly />
						</td>
						<td>
							<div class="col-md-9" >
								<input type="number" min="0" class="form-control" name="req_qty'.$rel["rp_id"].'" id="req_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="error_check('.$rel["rp_id"].','.$req_qty_sh.')"  value="'.$req_qty.'"  '.$req_read_only.' />
								
								<input type="hidden" name="req_qty_one'.$rel["rp_id"].'" id="req_qty_one'.$rel["rp_id"].'" value="'.$rel["req_qty_one"].'" />
								
								<input type="hidden" name="basic_req_qty'.$rel["rp_id"].'" id="basic_req_qty'.$rel["rp_id"].'" value="'.$req_qty.'" />
								
								<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["conv_unit_name"].'</strong>
							</div>
						</td>
						<td>
							<input type="number" min="0" class="form-control" name="res_qty'.$rel["rp_id"].'" id="res_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="error_check('.$rel["rp_id"].','.$res_qty_sh.')" value="'.$rel["reserve_stock"].'" '.$reserv_read_only.' />
							<span style="display:none;" class="error" id="res_qty_err'.$rel["rp_id"].'" ></span>
						</td>
						<td>
							<div class="col-md-9">
								<input type="number" min="0" class="form-control" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="error_check('.$rel["rp_id"].','.$process_qty_sh.')" onkeypress="return isNumberKey(event)"  value="'.$process_qty.'" '.$process_read_only.' />
								
								<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["base_unit_name"].'</strong>
							</div>
						</td>
						<td>
							<div class="col-md-9" >
								<input type="number" min="0" class="form-control" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="error_check('.$rel["rp_id"].','.$po_qty_sh.')"  value="'.$po_qty.'" '.$po_read_only.' />
								
								<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["conv_unit_name"].'</strong>
							</div>
						</td>
						<td class="action'.$rel["rp_id"].'">'.$request_button.'</td>
					</tr>';
			}
			
		}
		else if(strtolower($POST['mode']) == "get_tree_request") {
			
			$id=$POST['eid'];
			$pr_type=$POST['pr_type'];
			$bom_id=$POST['bom_id'];
			$po_req_no=$POST['po_req_no'];
			
			
			$json=array();
			
			$cnt=1;$counter_tree = 0;
				//$bom_exist_qry="SELECT COUNT(bom_id) as bom_cnt FROM `tbl_bom` WHERE bom_status=0 AND bom_product=".$info['product_id'];
				//$bom_exist_rel=mysqli_fetch_assoc($dbcon->query($bom_exist_qry));
				
			//if($pr_type==0)
			if(!empty($bom_id))
			{
				$qry="select trn.*,product.product_setting_check FROM `tbl_bomtrn` as trn 
				left join product_mst as product on product.product_id=trn.product_id 
				left join unit_mst as per on per.unitid=product.product_base_unit
				where bom_trn_status!=1 and bom_id='$bom_id' and parent_id='0'";
				
				/*$qry="select * FROM `tbl_bomtrn` as trn 
				left join product_mst as product on product.product_id=trn.product_id 
				left join unit_mst as per on per.unitid=product.product_base_unit
				where bom_trn_status!=1 and bom_id='$rel[bom_id]' and parent_id='0*/
			}
			else
			{
				$qry1="select bom_trn_id FROM `tbl_bomtrn` as trn 
				where bom_trn_status!=1 and trn.product_id='$id' order by bom_trn_id";
				$result1=$dbcon->query($qry1);
				$row1=mysqli_fetch_assoc($result1);
				//$cnt1=mysqli_num_rows($result);
				
				$qry="select trn.*,product.product_setting_check FROM `tbl_bomtrn` as trn 
				left join product_mst as product on product.product_id=trn.product_id 
				left join unit_mst as per on per.unitid=product.product_base_unit
				where bom_trn_status!=1 and trn.product_id='$id' and bom_trn_id=".$row1['bom_trn_id']." order by bom_trn_id";
				
			}
			//echo $qry;
				$result=$dbcon->query($qry);		
				$i=1;$total=0;$discount=0;
				$cnt1=mysqli_num_rows($result);
				//echo $qry;
				while($row=mysqli_fetch_assoc($result))
				{
					if($pr_type==0)
					{
						$bom_level=0;
						$bom_new_id=$bom_id;
						
						//get_tree_bom($dbcon,$row['product_id'],$row['parent_id'],0,$cnt,$bom_id,$number,$row['product_qty'],$row['bom_trn_id']);
					}
					else
					{
						$bom_level=$row['bom_level'];
						$bom_new_id=$row['bom_id'];
						
						//$bom_level=0;
						//$bom_new_id=$bom_id;
					}
						
					$bom_real_level=$row['bom_level'];
					
					$number="1.".$cnt;
				echo '<tr>';
						$one_q=$row['product_qty']/$row['product_base_qty'];
					//get_tree_request($dbcon,$row['product_id'],$row['parent_id'],$bom_level,$cnt,$bom_new_id,$number,$row['product_qty'],$row['bom_trn_id'],$row['product_type'],$row['product_setting_check'],$po_req_no,$bom_real_level);
					get_tree_request($dbcon,$row['product_id'],$row['parent_id'],$bom_level,$cnt,$bom_new_id,$number,$one_q,$row['bom_trn_id'],$row['product_type'],$row['product_setting_check'],$po_req_no,$bom_real_level);
					//get_tree_request($dbcon,$id,$row['parent_id'],$bom_level,$cnt,$row['bom_id'],$number,$row['product_qty'],$row['bom_trn_id'],$row['product_type'],$row['product_setting_check']);
						
				echo '</tr>';
			
				$cnt++;$counter_tree++;
				
				//$json['str_tree']=$str_tree;
				
				}
				
				echo "<tr>;
					<td>
						<input type='hidden' value='".$counter_tree."' name='counter_tree' id='counter_tree' />
					</td>
				</tr>";
				
				//$json['counter_tree']=$counter_tree;
				
				//echo json_encode($json);
				
		}
		
		else if(strtolower($POST['mode']) == "get_tree_request_second") {
			
			$id=$POST['eid'];
			$pr_type=$POST['pr_type'];
			$bom_id=$POST['bom_id'];
			$po_req_no=$POST['po_req_no'];
			
			$qry="select trn.*,product.product_name,product.product_type,product.product_min_stock,product.product_setting_check,product.product_id FROM `tbl_bomtrn` as trn 
				left join product_mst as product on product.product_id=trn.product_id 
				left join unit_mst as per on per.unitid=product.product_base_unit
				where bom_trn_status!=1 and bom_id='$bom_id' order by bom_level";
				
			$q=$dbcon->query($qry);
			$cnt=0;$cntt=1;
			while($row=mysqli_fetch_array($q))
			{
				$pr_setting_arr=explode(",",$row['product_setting_check']);
		
				if(in_array("process_product",$pr_setting_arr))
				{
					$readonly="";
					$in_check_qty="";
				}
				else
				{
					$readonly="readonly";
					$in_check_qty="1";
				}
				
				if($row['bom_level']==1)
				{
					if(check_requested($dbcon,$row['product_id'],$po_req_no)==0)
					{
						$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
					}
					else
					{
						//$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
						$btn='<a class="btn btn-danger" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-paper-plane"></i> Requested</a>';
					}
				
				}
				else
				{
					if(check_level_open($dbcon,$row['parent_id'],$po_req_no)>0)
					{
						if(check_requested($dbcon,$row['product_id'],$po_req_no)==0)
						{
							$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
						}
						else
						{
							//$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.')"><i class="fa fa-paper-plane"></i> Request</a>';
							$btn='<a class="btn btn-danger" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-paper-plane"></i> Requested</a>';
						}
					}
					else
					{
						$btn="";
					}
				}
				
				echo "<tr>";
					
				echo "<td> 1.".$cntt."</td>";
				echo "<td>".$row['product_name']."</td>";
				echo "<td>".get_product_type_by_id($dbcon,$row['product_type'])."</td>";
				echo "<td>".$row['product_min_stock']."</td>";
				echo "<td>".get_product_stock($dbcon,$row['product_id'])."</td>";
				echo "<td class='td5'>
					<input type='text' class='form-control' name='req_qty".$cnt."' id='req_qty".$cnt."' onkeypress='return isNumberKey(event)'  />
					
					<input type='hidden' class='form-control' name='total_qty".$cnt."' id='total_qty".$cnt."' value='".$row['product_qty']."'  />
					
					
				</td>";
				
				echo "<td class='td5'>
					<input type='text' class='form-control' name='in_process_qty".$cnt."' id='in_process_qty".$cnt."' onkeypress='return isNumberKey(event)' ".$readonly."  />
					
					<input type='hidden' class='form-control' name='in_process_qty_check".$cnt."' id='in_process_qty_check".$cnt."' value='".$in_check_qty."'  />
					
					
				</td>";
				
				echo "<td class='td5'>
					<input type='text' class='form-control' name='po_qty".$cnt."' id='po_qty".$cnt."' onkeypress='return isNumberKey(event)'  />
					
					<input type='hidden' class='form-control' name='pr_id".$cnt."' id='pr_id".$cnt."' value='".$row['product_id']."'  />
					
					
					<input type='hidden' class='form-control' name='pr_type".$cnt."' id='pr_type".$cnt."' value='".$row['product_type']."'  />
					
					
				</td>";
				echo "<td class='td5'>".$btn."</td>";
				
				echo "</tr>";
				
				$cnt++;$cntt++;
			}
			
			echo "<tr>;
					<td>
						<input type='hidden' value='".$cnt."' name='counter_tree' id='counter_tree' />
					</td>
				</tr>";
		}
		
		else if(strtolower($POST['mode']) == "get_all_requested_qty") {
			
			$po_req_no=$POST['po_req_no'];
			$json=array();
			
			$q=$dbcon->query("select * from tbl_request_product where main_request=0 and  rp_req_no='$po_req_no'");
			$count=mysqli_num_rows($q);
			if($count>0)
			{
				$data=array();
				while($row=mysqli_fetch_assoc($q))
				{
					$sqlReturn[] = $row;
					
				}
				
				$json['data']=$sqlReturn;
				$json['count']=$count;
				
			}else{
				$json['data']='';
				$json['count']='';
			}
			echo json_encode($json);
			/*$pr_id=$POST['pr_id'];
			$po_req_no=$POST['po_req_no'];
			$count_var=$POST['count_var'];
			$json=array();
			
			$q=$dbcon->query("select * from tbl_request_product where rp_req_no='$po_req_no' and rp_pid='$pr_id'");
			$count=mysqli_num_rows($q);
			if($count>0)
			{
				$row=mysqli_fetch_array($q);
				$json['rp_req_qty']=$row['rp_req_qty'];
				$json['rp_po_qty']=$row['rp_po_qty'];
				$json['in_process_qty']=$row['in_process_qty'];
				$json['rp_pid']=$row['rp_pid'];
				$json['row_cnt']=$row['row_cnt'];
				
				
			}
			$json['count']=$count;
			$json['count_var']=$count;
			echo json_encode($json);
			*/
		}
		
		else if(strtolower($POST['mode']) == "add_main_process_request_qty") {

			//delete_record("tbl_set_main_process","po_req_no='".$POST['po_req_no']."'",$dbcon);
			//in_process_qty_main
			$set_process="SELECT * FROM `tbl_set_main_process` WHERE sp_status=0 and user_id=".$_SESSION['user_id']." AND po_req_no='".$POST['po_req_no']."'";
			$set_process_rel=mysqli_fetch_assoc($dbcon->query($set_process));

			$bom_process="SELECT * FROM `tbl_bom` WHERE bom_status=0 AND bom_product='".$POST['eid']."'";
			$bom_rel=mysqli_fetch_assoc($dbcon->query($bom_process));
			
			
			$info['po_req_date']			=date('Y-m-d',strtotime($POST['po_req_date']));
			$info['rp_req_qty']				=$POST['rp_req_qty'];
			$info['in_process_qty_main']	=$POST['in_process_qty_main'];
			$info['rp_po_qty']				=$POST['rp_po_qty'];
			$info['product_id']				=$POST['eid'];
			$info['sales_order_trn_id']		=$POST['sales_order_trn_id'];
			$info['company_id']				=$_SESSION['company_id'];
			$info['vendor_id']				=$POST['cust_id'];
			$info['sales_order_date']		=date('Y-m-d',strtotime($POST['sales_order_date']));
			$info['po_no']					=$POST['po_no'];
			$info['po_date']				=date('Y-m-d',strtotime($POST['po_date']));
			$info['sales_order_no']			=$POST['sales_order_no'];
			$info['bom_id']					=$bom_rel['bom_id'];
			$info['bom_no']					=$bom_rel['bom_no'];
			
			
			if(empty($set_process_rel['sp_id'])){
				$info['cdate']					= date('Y-m-d H:i:s');
				$info['user_id']				= $_SESSION['user_id'];
				$info['po_req_no']				= load_series_no($dbcon,9);
				
				$inserid=add_record('tbl_set_main_process', $info, $dbcon,$POST['branch_id']);

				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			}else{
				$info['mdate']					=date('Y-m-d H:i:s');
				$info['muser_id']				=$_SESSION['user_id'];
				
				$updateid=update_record('tbl_set_main_process', $info,"sp_id=".$set_process_rel['sp_id'] ,$dbcon,$POST['branch_id']);
			}
			
			$set_pro="SELECT product_base_unit,product_conv_unit FROM `product_mst` WHERE product_status=0 AND product_id='".$POST['eid']."'";
			$setpro_rel=mysqli_fetch_assoc($dbcon->query($set_pro));
			
			$info_su['sp_id']				= $inserid;
			$info_su['sr_no']				= 0;
			$info_su['rp_pid']				= $POST['eid'];//product_id
			$info_su['rp_req_qty']			= $POST['in_process_qty_main'];//required qty
			$info_su['sales_order_trn_id']	= $POST['sales_order_trn_id'];//required qty
			$info_su['rp_po_qty']			= "";//po qty
			$info_su['in_process_qty']		= "";//process qty
			$info_su['rp_req_type']			= "min_max";//type
			$info_su['process_unit']		= $setpro_rel['product_base_unit'];
			$info_su['purchase_unit']		= $setpro_rel['product_conv_unit'];
			$info_su['perent_id']			= 0;
			$info_su['main_request']		= 1;
			$info_su['status']				= 3;
			$info_su['user_id']				= $_SESSION['user_id'];
			$info_su['company_id']			= $_SESSION['company_id'];
			
			$inserid_sub1=add_record('tbl_request_product', $info_su, $dbcon,$POST['branch_id']);
			//var_dump($inserid_sub1);
	//for ($x = 0; $x <= 100; $x++) {	
		$query22="select bom_id,product_base_qty from tbl_bom as bom_trn 
				where bom_status=0 and bom_product=".$POST['eid'];	
			$result22=$dbcon->query($query22);
		$rel22=mysqli_fetch_assoc($result22);
			
			
		$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=".$rel22['bom_id'];	
	$result1=$dbcon->query($query1);
	$i=1;$call=1;$space="";
	 while($rel1=mysqli_fetch_assoc($result1)){  
	
			/* if($rel1['product_base_unit']!=$rel1['product_conv_unit']){ 
				$rel1['product_base_qty']  $rel1['base_unit_name']
				$rel1['product_conv_qty']  $rel1['conv_unit_name']
			}else{
				$rel1['product_base_qty']  $rel1['base_unit_name']
			} */
			
			$base_one_qty=$rel1['product_base_qty']/$rel22['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

			$base_qty=$base_one_qty*$info_su['rp_req_qty'];
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

			$info_sub['sp_id']				= $inserid;
			$info_sub['sr_no']				= $i;
			$info_sub['rp_pid']				= $rel1['product_id'];//product_id
			$info_sub['rp_req_qty']			= $conv_stock;//required qty
			$info_sub['req_qty_one']		= $conv_one_qty;//required qty
			$info_sub['rp_po_qty']			= "";//po qty
			$info_sub['in_process_qty']		= "";//process qty
			$info_sub['rp_req_type']		= "min_max";//type
			$info_sub['process_unit']		= $rel1['product_base_unit'];
			$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
			$info_sub['perent_id']			= $inserid_sub1;
			$info_sub['status']				= 3;
			$info_sub['user_id']			= $_SESSION['user_id'];
			$info_sub['company_id']			= $_SESSION['company_id'];
			//$info_sub['main_request']		= $POST['g_total'];

			$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
			// var_dump($inserid_sub);  

			min_max_bom_show($dbcon,$rel1['p_bom_id'],$base_qty,$i,$call,$space,$inserid,$inserid_sub,'',$POST['branch_id']);
			$i++;
	} 
			
	//}
			
			/*$bom_exist=true;
			//Check BOM Exist or not of Product
			//if($POST['pr_type']=='0'){
				$bom_exist_qry="SELECT COUNT(bom_id) as bom_cnt FROM `tbl_bom` WHERE bom_status=0 AND bom_product=".$info['product_id'];
				$bom_exist_rel=mysqli_fetch_assoc($dbcon->query($bom_exist_qry));
				if(!$bom_exist_rel['bom_cnt']){
					$bom_exist=false;
				}
			//}
			*/
			
			/*if(!$bom_exist){
				echo "2";
			}
			else*/
			if($inserid){
				echo "1";
			}
			else {
				echo "0";
			}
			
		}
		else if(strtolower($POST['mode']) == "check_main_process_request") {
			
			$po_req_no=$POST['po_req_no'];
			$eid=$POST['eid'];
			$sales_order_trn_id=$POST['sales_order_trn_id'];
			$json=array();
			
			if(empty($sales_order_trn_id)){
				$q=$dbcon->query("select * from tbl_set_main_process where  sp_status=0 and company_id=".$_SESSION['company_id']." and user_id=".$_SESSION['user_id']." and product_id=".$eid."");
			}else{
				$q=$dbcon->query("select spro.* from tbl_set_main_process as spro
						left join tbl_request_product as rsp on rsp.sp_id=spro.sp_id
						where  spro.sp_status=0 and rsp.sales_order_trn_id=".$sales_order_trn_id." and spro.company_id=".$_SESSION['company_id']." and spro.product_id=".$eid."");
			}
			
			$count=mysqli_num_rows($q);
			if($count>0)
			{
				$row=mysqli_fetch_array($q);
				
				$request_qty=$row['rp_req_qty'];
				$process_qty=$row['in_process_qty_main'];
				$po_qty=$row['rp_po_qty'];
				$status=$row['sp_status'];
				$po_req_no=$row['po_req_no'];
				
				$json['req_qty']=$request_qty;
				$json['process_qty']=$process_qty;
				$json['po_qty']=$po_qty;
				$json['sp_status']=$status;
				$json['po_req_no']=$po_req_no;
				
			}
			
			$json['count']=$count;
			
			echo json_encode($json);
		}
		
		
		
		else if(strtolower($POST['mode']) == "lock_main_request") {
			
			$eid=$POST['eid'];
			$po_req_no=$POST['po_req_no'];
			
			$q=$dbcon->query("update tbl_set_main_process set sp_status='1' where po_req_no='$po_req_no' and product_id='$eid'");
			
			if($q)
			{
				echo "1";
			}
			else{
				
				echo "0";
			}
			
		}
		else if(strtolower($POST['mode']) == "add_product_request") {
			
			/* $req_qty			=$POST['req_qty'];
			$in_process_qty		=$POST['in_process_qty'];
			$po_qty				=$POST['po_qty'];
			$pr_id				=$POST['pr_id'];
			$po_req_no			=$POST['po_req_no'];
			$cnt				=$POST['cnt'];
			$trn_id				=$POST['trn_id'];
			 */
			 
			$query="select * from tbl_request_product as i
				where i.rp_id=".$POST['rp_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
				
				
			$info['rp_req_date']		=date('Y-m-d');
			$info['rp_req_qty']			=$POST['req_qty'];
			$info['rp_po_qty']			=$POST['po_qty'];
			$info['in_process_qty']		=$POST['process_qty'];
			$info['reserve_stock']		=$POST['res_qty'];
			
			$info['status']				=0;
			$info['cdate']				=date('Y-m-d H:i:s');
			$info['user_id']			=$_SESSION['user_id'];
			$info['company_id']			=$_SESSION['company_id'];
			
			if($POST['po_qty']>"0"){
				$indent_no=load_common_no($dbcon,17);
				update_common_no($dbcon,17);
				$info['indent_status']		= 1;
				$info['indent_no']			= $indent_no;
				$info['indent_date']		= date('Y-m-d');
			}
			if($POST['process_qty']>"0"){
				$indent_no=load_common_no($dbcon,JOBCARD);
				update_common_no($dbcon,JOBCARD);
				$info['job_card_status']		= 1;
				$info['job_card_no']			= $indent_no;
				$info['job_card_date']		= date('Y-m-d');
			}
			$updateid=update_record("tbl_request_product", $info,"rp_id=".$POST['rp_id'] , $dbcon, $_POST['branch_id']);
			
			//$inserid=add_record('tbl_request_product', $info, $dbcon);
			//$de=add_request_reserve_stock($dbcon,$pr_id,$POST['at_reserve']);
			//$de=add_request_reserve_stock($dbcon,$inserid,$POST['at_reserve']);
			
			if($POST['res_qty']!="0"){
				if($POST['res_qty']!=""){
					add_request_reserve_stock($dbcon,$POST['rp_id'],$POST['res_qty'],$row['purchase_unit'], $_POST['branch_id']);
				}
			}
			//var_dump($de);
			
			if($POST['process_qty']!='')
			{
				if($POST['process_qty']!="0"){
					$queryw="select * from tbl_product_process where status=0 and product_id=".$row['rp_pid'];
					$rs_custw=$dbcon->query($queryw);	
					while($relw=mysqli_fetch_array($rs_custw)){
						$infow['product_id']		= $relw['product_id'];
						$infow['rp_id']				= $row['rp_id'];
						$infow['process_priority']	= $relw['process_priority'];;
						$infow['process_time']		= $relw['process_time'];
						$infow['process_type']		= $relw['process_type'];
						$infow['process_opening']	= $relw['process_opening'];
						$infow['process_id']		= $relw['process_id'];
						$infow['cdate']				= date('Y-m-d');
						$infow['user_id']			= $_SESSION['user_id'];
						$infow['company_id']		= $_SESSION['company_id'];
						
						$inserid_a2=add_record('tbl_wororder_product_process', $infow, $dbcon, $_POST['branch_id']);

						/*
						Code By Umair : 04/11/2020
						Comment: Insert Work Order Resource Allocation In tbl_work_order_resource_allocate table
						*/
						if($relw['process_type']=='1'){
							$resource_id = $relw['resource_id'];
							$request_id = $POST['rp_id']; 
							$process_id = $relw['process_id'];
							$product_id = $relw['product_id'];
							$qty = $POST['process_qty'];
							$time_per_qty = $relw['process_time'];
							
							$action_type = 'add';
							$edit_id = '';
							work_order_resource_allocate($dbcon, $resource_id, $request_id, $process_id, $product_id, $qty, $time_per_qty, $edit_id, $action_type, '', $_POST['branch_id']);
						}
					}
				}
			}
			 if($POST['process_qty']!='')
			{
				if($POST['process_qty']!="0"){
					$process=get_product_process($dbcon,$row['rp_id'],$row['rp_pid']);
					$process_pr=json_decode($process);
					
					$process_id=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;

					/*Get Resource ID*/
					$resourceinfo=get_resource_from_product_process($dbcon,$row['rp_pid'],$process_id, $where=null);
				
					$info5['process_id']		= $process_id;			
					$info5['p_start_time']		= '';		
					$info5['p_end_time']		= '';		
					$info5['p_qty']				= $POST['process_qty'];		
					$info5['pen_qty']			= $POST['process_qty'];		
					$info5['process_unit']		= $row['process_unit'];		
					$info5['p_ref_id']			= $row['rp_id'];		
					$info5['p_ref_type']		= 'process request';		
					$info5['p_product_id']		= $row['rp_pid'];		
					$info5['pr_process_type']	= $process_type;		
					$info5['process_priority']	= $process_priority;		
					$info5['previous_process_id']= 0;

					if($resourceinfo['process_type']=='1'){			
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}
					
					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					
					$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon, $_POST['branch_id']);
				}
			}

			
			/*
			if($po_qty!='0')
			{
				if($po_qty!='')
				{
				$rate=get_pro_field($dbcon,$pr_id,'product_purchase_rate');
				$total=$po_qty*$rate;
				
				$infpotrn['purchaseorder_id']	= '0';
				$infpotrn['product_type']		= '';
				$infpotrn['product_id']			= $pr_id;
				$infpotrn['product_qty']		= $po_qty;
				$infpotrn['product_rate']		= $rate;
				$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$pr_id,'product_hsn');
				//$infpotrn['unit_id']			= get_pro_field($dbcon,$pr_id,'product_base_unit');
				$infpotrn['unit_id']			= $POST['purchase_unit'];
				$infpotrn['product_amount']		= $total;
				$infpotrn['total']				= $total;
				$infpotrn['parent_pro']			= 0;
				$infpotrn['main_pro_status']	= 1;//Requested products
				$infpotrn['user_id']			= $_SESSION['user_id'];
				$infpotrn['po_ref_id']			= $inserid;
				$infpotrn['po_ref_type']			= '0';
				$infpotrn['po_bom_id']			= '';
				$infpotrn['po_bom_trn_id']			= '';
				$infpotrn['mdate']	= date('Y-m-d');
		
				$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon);
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '10' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);	
				}
			} */
			
			
			
			
			$q=$dbcon->query("select IFNULL(group_concat(rp_id),0) as trn_ids from tbl_request_product where status!=2 and perent_id=".$POST['rp_id']."");
			
			$q_rel=mysqli_fetch_assoc($q);
			$resp['trn_ids']=$q_rel['trn_ids'];
			$resp['insert_id']=$POST['rp_id'];
			
			echo json_encode($resp);

		}
		
	else if(strtolower($POST['mode']) == "get_under_tree") {
			
			$trn_id=$POST['trn_id'];
			$req_no=$POST['po_req_no'];
			
			$q=$dbcon->query("select IFNULL(group_concat(bom_trn_id),0) as trn_ids from tbl_bomtrn where bom_trn_status=0 and parent_id='$trn_id'");
				
			$q_rel=mysqli_fetch_assoc($q);
			$trn_id_new=$q_rel['trn_ids'];
			$trn_id1=explode(",",$trn_id_new);
			$data="";
			foreach($trn_id1 as $t)
			{
				$q1=$dbcon->query("select row_cnt from tbl_request_product where row_cnt='$t'");
				$c1=mysqli_num_rows($q1);
				if($c1==0)
				{
					$data.=",".$t;
				}
				$resp['trn_ids']=$data;
			}
			
			
			//$resp['count']=$count1;
			echo json_encode($resp);

		}
		else if(strtolower($POST['mode']) == "work_order_submit_per") {
			$query11="select count(rp_id) as pending_req from tbl_request_product where main_request!=1 and status in (0,3) and user_id=".$_SESSION['user_id']." and sp_id=".$POST['work_order_id'];
				$rows1=mysqli_fetch_assoc($dbcon->query($query11));
			if($rows1['pending_req']==0){
				echo "1";
				
			}else{
			
				$query1="select count(rp_id) as pending_req from tbl_request_product where main_request!=1 and user_id=".$_SESSION['user_id']." and status=3 and sp_id=".$POST['work_order_id'];
				$rows=mysqli_fetch_assoc($dbcon->query($query1));
				if($rows['pending_req']>0){
					echo "2";	
				}else{
					echo "1";
					//echo $query1;
				}
			}
		}

function load_po_no($dbcon,$typeid){
	$row=array();
	$query1="select * from tbl_invoicetype where type_id=".$typeid." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
	$rows=mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
	//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
	//$end = $start+1;
	if($rows['invoice_format']=='2')
	{
		$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
	}
	else if($rows['invoice_format']=='1')
	{
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
	}
	else if($rows['invoice_format']=='3'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
	}
	else{
		$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
	}
	$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
	
	$inv_no=$row['invoiceno'];
	return ($inv_no);
}
?>