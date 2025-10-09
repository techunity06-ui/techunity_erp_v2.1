<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	$companyConfiguration = getCompanyConfiguration($dbcon);
$production_pro_search = $companyConfiguration['production_pro_search'];
$pro_search=explode(",", $production_pro_search);

	if(brp_strtolower($POST['mode']) == "fetch") {
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
	else if(brp_strtolower($POST['mode']) == "check_poreq_status") {
		
		//$chk=check_requested($dbcon,$POST['product_id'],$POST['po_req_no']);
		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		
		$bom_q1="SELECT rp_id FROM `tbl_request_product` WHERE sp_id=".$work_order_id;
		$bom_rel_q1=brp_mysqli_fetch_assoc($dbcon->query($bom_q1));
		
		$query="select * from tbl_request_product as i
				where i.rp_id=".$bom_rel_q1['rp_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
		
		if($row['rp_po_qty']!=0){
			echo "1";
		}else{
			echo "0";
		}
	}
	else if(brp_strtolower($POST['mode']) == "main_po_reqdata") {
		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		
		$bom_q1="SELECT rp_id FROM `tbl_request_product` WHERE sp_id=".$work_order_id;
		$bom_rel_q1=brp_mysqli_fetch_assoc($dbcon->query($bom_q1));
		
		$query="select * from tbl_request_product as i
				where i.rp_id=".$bom_rel_q1['rp_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			
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
	else if(brp_strtolower($POST['mode']) == "main_po_reqdata_old") {
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
	else if(brp_strtolower($POST['mode']) == "add_old") {
		
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
					while($row111=brp_mysqli_fetch_array($rs11)){
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
	else if(brp_strtolower($POST['mode']) == "purchase_mode") {
			
		$set_pro="SELECT product_base_unit,product_conv_unit FROM `product_mst` WHERE product_status=0 AND product_id='".$POST['eid']."'";
			$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));
			
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
	else if(brp_strtolower($POST['mode']) == "edit") {
		//$info_wo['sp_status']=2;
		$info_wo['po_req_date'] = $_POST['po_date'];
		$work_order_id = $_POST['work_order_id'];
		$update=update_record("tbl_set_main_process", $info_wo,"sp_id=".$work_order_id , $dbcon);
		echo 3;
	}
	else if(brp_strtolower($POST['mode']) == "add") {
		
		/*$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		
		$info_wo['sp_status']=2;
		$updateid12=update_record("tbl_set_main_process", $info_wo,"sp_id=".$work_order_id , $dbcon);
		*/
		$bom_q1="SELECT rp_id,rp_pid FROM `tbl_request_product` WHERE main_request=1 and job_card_no='".$POST['po_req_no']."'"; 
		$bom_rel_q1=brp_mysqli_fetch_assoc($dbcon->query($bom_q1));
		
		$query="select * from tbl_request_product as i
				where i.rp_id=".$bom_rel_q1['rp_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			
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
				$info['job_card_no']			= $POST['po_req_no'];
				$info['job_card_date']		= date('Y-m-d');
			}
			if(!empty($POST['sales_order_trn_id'])){
				$info['sales_order_trn_id']		= $POST['sales_order_trn_id'];
			}
			$updateid=update_record("tbl_request_product", $info,"rp_id=".$bom_rel_q1['rp_id'] , $dbcon);

			// jobcard wip stock add

			$set_pro="SELECT product_base_unit,product_conv_unit,product_base_qty,product_conv_qty,product_id FROM `product_mst` WHERE product_status=0 AND product_id='".$bom_rel_q1['rp_pid']."'";
			$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));

			if($setpro_rel['product_conv_unit']==$row['process_unit']){
				$type="base_unit";
				$con_stock1=$info['in_process_qty'];
				$base_stock1=convert_stock_new($dbcon,$info['in_process_qty'],$bom_rel_q1['rp_pid'],$type);
			}else{
				$type="conv_unit";
				$base_stock1=$info['in_process_qty'];
				$con_stock1=convert_stock_new($dbcon,$info['in_process_qty'],$bom_rel_q1['rp_pid'],$type);
			}

			$info_wip_add1['rp_id']					= $bom_rel_q1['rp_id'];
			$info_wip_add1['type_flag']				= 2;
			$info_wip_add1['po_trn_id']				= 0;
			$info_wip_add1['sales_order_trn_id']		= 0;
			//$info_wip_add1['allocate_for_rp_id']		= 0;
			//$info_wip_add1['allocate_table_id']		= $POST['sales_order_trn_id'];
			$info_wip_add1['allocate_base_qty']		= $base_stock1;
			$info_wip_add1['allocate_base_unit']		= $setpro_rel['product_base_unit'];
			$info_wip_add1['allocate_conv_qty']		= $con_stock1;
			$info_wip_add1['allocate_conv_unit']		= $setpro_rel['product_conv_unit'];
			$info_wip_add1['stock_flag']				= 1;
			$info_wip_add1['cdate']					= date("Y-m-d H:i:s");
			$info_wip_add1['user_id']				= $_SESSION['user_id'];
			$info_wip_add1['company_id']				= $_SESSION['company_id'];

			$inser_wip_add1=add_record('wip_stock_allocate', $info_wip_add1, $dbcon,$row['branch_id']);

			if(!empty($POST['sales_order_trn_id'])){

				$info_wip_deduct1['rp_id']					= $bom_rel_q1['rp_id'];
				$info_wip_deduct1['type_flag']				= 3;
				$info_wip_deduct1['po_trn_id']				= 0;
				$info_wip_deduct1['sales_order_trn_id']		= $POST['sales_order_trn_id'];
				$info_wip_deduct1['allocate_for_rp_id']		= $bom_rel_q1['rp_id'];
				$info_wip_deduct1['perent_id']				= $inser_wip_add1;
				$info_wip_deduct1['allocate_base_qty']		= $base_stock1;
				$info_wip_deduct1['allocate_base_unit']		= $setpro_rel['product_base_unit'];
				$info_wip_deduct1['allocate_conv_qty']		= $con_stock1;
				$info_wip_deduct1['allocate_conv_unit']		= $setpro_rel['product_conv_unit'];
				$info_wip_deduct1['stock_flag']				= 2;
				$info_wip_deduct1['cdate']					= date("Y-m-d H:i:s");
				$info_wip_deduct1['user_id']				= $_SESSION['user_id'];
				$info_wip_deduct1['company_id']				= $_SESSION['company_id'];

				$inser_wip_deduct1=add_record('wip_stock_allocate', $info_wip_deduct1, $dbcon,$row['branch_id']);
			

				$set_pro_w="SELECT allocate_base_qty_used,allocate_conv_qty_used FROM `wip_stock_allocate` WHERE wip_stock_allocate_id='".$info_wip_deduct1['perent_id']."'";
			$setpro_rel_w=brp_mysqli_fetch_assoc($dbcon->query($set_pro_w));

			$bsto=$setpro_rel_w['allocate_base_qty_used']+$info_wip_deduct1['allocate_base_qty'];
			$csto=$setpro_rel_w['allocate_conv_qty_used']+$info_wip_deduct1['allocate_conv_qty'];


			$query_invoicetype11 = $dbcon->query("UPDATE wip_stock_allocate SET allocate_base_qty_used =".$bsto.",allocate_conv_qty_used=".$csto." WHERE wip_stock_allocate_id =".$info_wip_deduct1['perent_id']);
			}
		// jobcard wip stock end
			
			if($POST['in_process_qty']!='')
			{
				if($POST['in_process_qty']!="0"){
				
					$queryw_b="select * from pro_bom_process where process_status=0 and bom_id=".$row['bom_id'];
					$rs_custw_b=$dbcon->query($queryw_b);	
					while($relw_b=brp_mysqli_fetch_array($rs_custw_b)){
						
					$queryw="select * from tbl_product_process where status = 0 and  pr_process_id=".$relw_b['pr_process_id'];
					$rs_custw=$dbcon->query($queryw);	
					$relw=brp_mysqli_fetch_array($rs_custw);
						$infow['product_id']		= $relw['product_id'];
						$infow['rp_id']				= $row['rp_id'];
						$infow['process_priority']	= $relw_b['priority'];;
						$infow['process_time']		= $relw['process_time'];
						$infow['process_type']		= $relw['process_type'];
						$infow['process_opening']	= $relw['process_opening'];
						$infow['process_id']		= $relw['process_id'];
						$infow['cdate']				= date('Y-m-d');
						$infow['user_id']			= $_SESSION['user_id'];
						$infow['company_id']		= $_SESSION['company_id'];
						
						$inserid_a2=add_record('tbl_wororder_product_process', $infow, $dbcon, $POST['branch_id']);

					
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
					$info5['product_version']	= $row['product_version'];

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
	else if(brp_strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `product_mst` WHERE `product_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(brp_strtolower($POST['mode']) == "edit") {
		
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
	else if(brp_strtolower($POST['mode']) == "delete") {
		
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
	else if(brp_strtolower($POST['mode']) == "add_unit_converter") {
			
			
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
		
		else if(brp_strtolower($POST['mode']) == "load_unit_converter") {
			
			if(brp_strtolower($POST['form_mode']) == "edit"){
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
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
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
		else if(brp_strtolower($POST['mode'])== "preedit_unit")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_unit WHERE unit_id	= '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(brp_strtolower($POST['mode'])== "delete_data_unit")
		{
			
			$deleteid=delete_record('tbl_product_unit', "unit_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
	
		else if(brp_strtolower($POST['mode']) == "add_branch_stock") {
			
			$bstock=$POST['bstock'];
			$bid=$POST['bid'];
			$form_mode=$POST['form_mode'];
			$pid=$POST['pid'];
			
			for($i=0;$i<count($bstock);$i++)
			{
				$q=$dbcon->query("select branch_id,product_id from tbl_branch_product_stock where branch_id='$bid[$i]' and product_id='$pid'");
				$count=brp_mysqli_num_rows($q);
				
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
		else if(brp_strtolower($POST['mode']) == "add_product_image_temp") {
			
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
		
		else if(brp_strtolower($POST['mode']) == "load_product_images") {
			
			if(brp_strtolower($POST['form_mode']) == "edit"){
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='$POST[product_id]' order by img_id Desc";
			}
			else{
				
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='0' order by img_id Desc";
			}	
				$rel=$dbcon->query($q);
				$path='view/upload/product_images/';
				$str="";
				$str.="<table></tr>";
				while($row  = brp_mysqli_fetch_assoc($rel))
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
		else if(brp_strtolower($POST['mode'])== "delete_data_image")
		{
			
			$deleteid=delete_record('tbl_product_images', "img_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		// Party Purchase
		
		else if(brp_strtolower($POST['mode']) == "add_party_purchase") {
			
			
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
		
		else if(brp_strtolower($POST['mode']) == "load_party_purchase") {
			
			if(brp_strtolower($POST['form_mode']) == "edit"){
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
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
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
		
		else if(brp_strtolower($POST['mode'])== "preedit_party")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_party_purchase WHERE party_purchase_id	= '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(brp_strtolower($POST['mode'])== "delete_data_party")
		{
			
			$deleteid=delete_record('tbl_product_party_purchase', "party_purchase_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		// Product Parameter
		
		else if(brp_strtolower($POST['mode']) == "add_param_value") {
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
				update_product_setting($dbcon,$POST['pid'],'product_qc');
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(brp_strtolower($POST['mode']) == "load_product_param") {
			
			if(brp_strtolower($POST['form_mode']) == "edit"){
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
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
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
		
		else if(brp_strtolower($POST['mode'])== "preedit_param")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_parameter WHERE pr_param_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(brp_strtolower($POST['mode'])== "delete_data_param")
		{
			
			$deleteid=delete_record('tbl_product_parameter', "pr_param_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])== "get_product_code")
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
		
		/*else if(brp_strtolower($POST['mode']) == "add_process_value") {
			
			
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
		}*/
		
		else if(brp_strtolower($POST['mode']) == "load_product_process") {
			
			if(brp_strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and  mst.user_id=".$_SESSION['user_id']." and mst.product_id='$POST[product_id]'";
			}
			else{
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and mst.user_id=".$_SESSION['user_id']." and mst.product_id='0' ";
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
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
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
		
		else if(brp_strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where type_id='9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
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
		
		else if(brp_strtolower($POST['mode']) == "load_tempoutward") {
			
			
		}
		else if(brp_strtolower($POST['mode']) == "get_tree_request_new") {
			
			
			$pq=$dbcon->query("select * from  tbl_request_product where  status IN (0,3) and  company_id=".$_SESSION['company_id']." and rp_pid=".$POST['eid']." and job_card_no='".$POST['po_req_no']."'");
			$pq_row=brp_mysqli_fetch_array($pq);
			//$where = '';			
			$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,pro.product_icode,dr.drawing_number,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.image_name,pro.product_id,pro.product_type FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			WHERE main_request=0 and rpro.status in (0,3) AND rpro.perent_id=".$pq_row['rp_id']; 
			
			$result=$dbcon->query($bom1); 
			
			
			while($rel=brp_mysqli_fetch_assoc($result)){
				//echo "<pre>"; print_r($rel);die;
									
					/*if($rel['status']==3){
						/*if($rel['approval_status'] == 1){
							
							if($_POST['main_mode'] == "wo_permission"){
							$req_btn_action = '';

							$req_btn_text = 'Granted';
											
							} else if($rel['approval_status'] == 2){
								$req_btn_text = 'Rejected';
							}
							else{
								$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].');"';
							
								$req_btn_text = 'Request';
							}	
							
							
								$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" '.$req_btn_action.' ><i class="fa fa-paper-plane"></i> '.$req_btn_text.'</a>';
						}
						else
						{
							if($_POST['main_mode'] == "wo_permission"){
								$req_btn_action = 'onclick="workorder_permission('.$rel["rp_id"].');"';
									if($rel['approval_status'] == 1){
												$req_btn_text = 'Granted permission';
											}
											else if($rel['approval_status'] == 2){
												$req_btn_text = 'Rejected permission';
											}
											else
											{
												$req_btn_text = 'Pending permission';
											}
							}else{
								$req_btn_action = 'onclick="pending_approval();"';
								//$req_btn_text = 'Pending';
								if($rel['approval_status'] == 1){
												$req_btn_text = 'Granted Request';
											}
											else if($rel['approval_status'] == 2){
												$req_btn_text = 'Rejected Request';
											}
											else
											{
												$req_btn_text = 'Pending Request';
											}
							}	
													
								$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].');" ><i class="fa fa-paper-plane"></i>  Request </a>';
						
					
					}else{
						$request_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					}*/
					
					if($rel['status']==3){
						if($rel['bom_id'] == 0)
						{
						
							/* JAYESH */
							if($rel['approval_status'] == 1){
							
							if($_POST['main_mode'] == "wo_permission"){
							$req_btn_action = '';

							$req_btn_text = 'Granted';
											
							}
							else if($rel['approval_status'] == 2){
								$req_btn_text = 'Rejected';
							}
							else{
								$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].');"';
							
								$req_btn_text = 'Request';
							}	
						}else
						{
							if($_POST['main_mode'] == "wo_permission"){
								$req_btn_action = 'onclick="workorder_permission('.$rel["rp_id"].');"';
									if($rel['approval_status'] == 1){
												$req_btn_text = 'Granted permission';
											}
											else if($rel['approval_status'] == 2){
												$req_btn_text = 'Rejected permission';
											}
											else
											{
												$req_btn_text = 'Pending permission';
											}
							}else{
										if($rel['approval_status'] == 1){
											$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].');"';					
											$req_btn_text = 'Request';
										}
										else if($rel['approval_status'] == 2){
											$req_btn_text = 'Rejected Request';
											$req_btn_action= '';
										}
										else
										{
											
											$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].');"';					
											$req_btn_text = 'Request';
											/*$req_btn_action = 'onclick="pending_approval();"';
											$req_btn_text = 'Pending Request';*/
										}
							}	
						}
							
								$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" '.$req_btn_action.' ><i class="fa fa-paper-plane"></i> '.$req_btn_text.'</a>';
					}
					else {
						
						$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].');" ><i class="fa fa-paper-plane"></i> Request</a>';
							/* JAYESH */
						}
						
						
					}else{
						$request_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					}
			$bom2="SELECT status,main_request,rp_req_qty,in_process_qty,approval_status FROM `tbl_request_product` WHERE status!=2 AND perent_id=".$rel['perent_id']." AND rp_id =".$rel['rp_id'];
				$bom_rel2=brp_mysqli_fetch_assoc($dbcon->query($bom2));
				
				/*if($bom_rel2['main_request']!="1"){
					if($bom_rel2['status']=="3"){
						$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].');" ><i class="fa fa-paper-plane"></i> Request</a>';
					}else{
						$request_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					}
				}*/
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
			
			/*START JAYESH */
			/*
				$process_button='<a class="btn btn-success" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="view_process('.$rel["rp_id"].','.$parent_delete_flag.');" ><!--<i class="fa fa-paper-plane"></i>--> Process</a>';*/
				
				
								$check_process_query="SELECT * from tbl_wororder_product_process where  rp_id = ".$rel["rp_id"]." AND process_id != '0'";
									$check_process_result=$dbcon->query($check_process_query);
									
									if(brp_mysqli_num_rows($check_process_result)> 0)
									{ 
										/*$rp_id = $rel["rp_id"];										
										$parent_sr_res=$dbcon->query("select * from  tbl_request_product where perent_id = '$rp_id'");
										if(brp_mysqli_num_rows($parent_sr_res)>0)
										{*/
											$parent_delete_flag = 1;
										/*}
										else{
											$parent_delete_flag = 0;
										}	*/								
									
									if($rel['status']==3){
										$sub_product_button='<a class="btn btn-success btn-xs" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_sub_product('.$rel["rp_id"].','.$rel["product_id"].','.$POST['eid'].','.$rel['rp_req_qty'].');" ><i class="fa fa-plus"></i></a>';
											}
											else{
												$sub_product_button='';
											}
										
									}else{
										
										//$process_button='';
										$sub_product_button='';
										$parent_delete_flag = 0;
									}
									
										$rp_id = $rel["rp_id"];								
										$parent_sr_res=$dbcon->query("select * from  tbl_request_product where perent_id = '$rp_id'");
										if(brp_mysqli_num_rows($parent_sr_res)>0)
										{
											$child_flag = 1;
										}
										else{
											$child_flag = 0;
										}	
									
									/*	$sub_product_button='<a class="btn btn-success" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_sub_product('.$rel["rp_id"].','.$rel["rp_pid"].','.$POST['eid'].');" ><i class="fa fa-plus"></i></a>';*/
									
								/*	if($rel['status']==3){*/
								
									$check_process_allocate_query="SELECT * from tbl_allocate_process where product_id=".$rel["product_id"]." AND p_status = '1'";
									$check_process_allocate_result=$dbcon->query($check_process_allocate_query);
									if(brp_mysqli_num_rows($check_process_allocate_result) < 1)
									{ 
										
										$process_button='<a class="btn btn-success btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="direct_show_product_process('.$rel['rp_pid'].','.$rel['rp_id'].');" ><!--<i class="fa fa-paper-plane"></i>--> Process</a>';
			
									
									$edit_button='<a class="btn btn-primary btn-xs" data-original-title="" id="del_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="edit_work_order_product('.$POST['eid'].','.$rel["rp_id"].','.$rel["rp_pid"].','.$rel["rp_req_qty"].');" ><i class="fa fa-pencil-square-o"></i></a>';
									
									$del_button='<a class="btn btn-danger btn-xs" data-original-title="" id="del_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="delete_work_order_product('.$POST['eid'].','.$rel["rp_id"].','.$child_flag.','.$rel["sp_id"].');" ><i class="fa fa-remove"></i></a>';
									}
									else
									{
										$process_button='';
										$del_button='';
										$edit_button='';
									}
									
			/* END JAYESH */
			
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
				/* start jayesh for checking product wise process required or not */
			$action = '';
			$check_pr_type_process = check_process_product_type($dbcon,$rel['product_type']);
			if($check_pr_type_process == 1)
			{
				if($rel['status']!=3)
				{
					$action = $process_button;
				}
				else
				{
					$action = $process_button.'	'.$sub_product_button.' '.$edit_button.' '.$del_button;
				}
				
			}
			else
			{
				if($rel['status']!=3)
				{
					$action = $edit_button.' '.$del_button;
				}
			}
			
			/* start jayesh for checking product wise process required or not */
					$drawing_number = "";
										$item_code = "";
										 if(in_array('drawing',$pro_search)){
									            $drawing_number = " </br> -- (".$rel['drawing_number'].")";
									        }
									        if(in_array('item',$pro_search)){
									            $item_code = "</br> -- (".$rel['product_icode'].")";
									        }

			
				echo '<tr>
						<td>'.$rel["sr_no"].'</td>
						<td>'.$rel["product_name"].$rel["product_type"].$item_code.$drawing_number.'</td>
						<td>'.$image_name1.'</td>
						<td>'.$cat_name.'</td>
						<td>'.$rel["product_min_stock"].'</td>
						<td>
							<input type="number" min="0" class="form-control numbersOnly" name="current_stock'.$rel["rp_id"].'" id="current_stock'.$rel["rp_id"].'" onkeydown="return numericonly(event)"  value="'.$actualstock.'" readonly />
							
						</td>
						<td>
							<div class="col-md-9" >
								<input type="number" min="0" class="form-control numbersOnly" name="req_qty'.$rel["rp_id"].'" id="req_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$req_qty_sh.')"  value="'.$req_qty.'"  '.$req_read_only.' />
								
								<input type="hidden" name="req_qty_one'.$rel["rp_id"].'" id="req_qty_one'.$rel["rp_id"].'" value="'.$rel["req_qty_one"].'" />
								
								<input type="hidden" name="basic_req_qty'.$rel["rp_id"].'" id="basic_req_qty'.$rel["rp_id"].'" value="'.$req_qty.'" />
								
								<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["conv_unit_name"].'</strong>
							</div>
						</td>
						<td>
							<input type="number" min="0" class="form-control numbersOnly" name="res_qty'.$rel["rp_id"].'" id="res_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$res_qty_sh.')" value="'.$rel["reserve_stock"].'" '.$reserv_read_only.' />
							<span style="display:none;" class="error" id="res_qty_err'.$rel["rp_id"].'" ></span>
						</td>
						<td>
							<div class="col-md-9">
								<input type="number" min="0" class="form-control numbersOnly" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="error_check('.$rel["rp_id"].','.$process_qty_sh.')" onkeydown="return numericonly(event)"  value="'.$process_qty.'" '.$process_read_only.' />
								
								<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["base_unit_name"].'</strong>
							</div>
						</td>
						<td>
							<div class="col-md-9" >
								<input type="number" min="0" class="form-control numbersOnly" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$po_qty_sh.')"  value="'.$po_qty.'" '.$po_read_only.' />
								
								<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["conv_unit_name"].'</strong>
							</div>
						</td>
						<td class="action'.$rel["rp_id"].'">'.$request_button.'
						<br>'.$action.'</td>
					</tr>';
					
				$rp_id = $rel['rp_id'];
				$child_query = "select * from tbl_request_product where perent_id = '$rp_id'";
				$child_result=$dbcon->query($child_query);
				
				if(brp_mysqli_num_rows($child_result)>0)
				{
					get_child_tree($dbcon,$rp_id);
					
				}
				
					
	}	/* child tree */
					
		
		
			
		}
		else if(brp_strtolower($POST['mode']) == "get_tree_request") {
			
			$id=$POST['eid'];
			$pr_type=$POST['pr_type'];
			$bom_id=$POST['bom_id'];
			$po_req_no=$POST['po_req_no'];
			
			
			$json=array();
			
			$cnt=1;$counter_tree = 0;
				//$bom_exist_qry="SELECT COUNT(bom_id) as bom_cnt FROM `tbl_bom` WHERE bom_status=0 AND bom_product=".$info['product_id'];
				//$bom_exist_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_exist_qry));
				
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
				$row1=brp_mysqli_fetch_assoc($result1);
				//$cnt1=brp_mysqli_num_rows($result);
				
				$qry="select trn.*,product.product_setting_check FROM `tbl_bomtrn` as trn 
				left join product_mst as product on product.product_id=trn.product_id 
				left join unit_mst as per on per.unitid=product.product_base_unit
				where bom_trn_status!=1 and trn.product_id='$id' and bom_trn_id=".$row1['bom_trn_id']." order by bom_trn_id";
				
			}
			//echo $qry;
				$result=$dbcon->query($qry);		
				$i=1;$total=0;$discount=0;
				$cnt1=brp_mysqli_num_rows($result);
				//echo $qry;
				while($row=brp_mysqli_fetch_assoc($result))
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
		
		else if(brp_strtolower($POST['mode']) == "get_tree_request_second") {
			
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
			while($row=brp_mysqli_fetch_array($q))
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
		
		else if(brp_strtolower($POST['mode']) == "get_all_requested_qty") {
			
			$po_req_no=$POST['po_req_no'];
			$json=array();
			
			$q=$dbcon->query("select * from tbl_request_product where main_request=0 and  rp_req_no='$po_req_no'");
			$count=brp_mysqli_num_rows($q);
			if($count>0)
			{
				$data=array();
				while($row=brp_mysqli_fetch_assoc($q))
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
			$count=brp_mysqli_num_rows($q);
			if($count>0)
			{
				$row=brp_mysqli_fetch_array($q);
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
		
		else if(brp_strtolower($POST['mode']) == "add_main_process_request_qty") {



			//delete_record("tbl_set_main_process","po_req_no='".$POST['po_req_no']."'",$dbcon);
			//in_process_qty_main
			$set_process="SELECT * FROM `tbl_set_main_process` WHERE sp_status=0 and user_id=".$_SESSION['user_id']." AND po_req_no='".$POST['po_req_no']."'";
			$set_process_rel=brp_mysqli_fetch_assoc($dbcon->query($set_process));
			
			if(!empty($POST['sales_order_trn_id'])){
				$so_bom="SELECT * FROM `tbl_sales_ordertrn` WHERE sales_ordertrn_id='".$POST['sales_order_trn_id']."'";
				$sorel=brp_mysqli_fetch_assoc($dbcon->query($so_bom));
				$bom_id=$sorel['bom_id'];
			}
			
			if(!empty($bom_id)){
				$bom_process="SELECT * FROM `tbl_bom` WHERE bom_status=0 AND bom_id='".$bom_id."'";
				$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));
			}else{
				
				if($POST['bom_version_id']!='')
				{
					$bom_process="SELECT * FROM `tbl_bom` as bom
					left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
					WHERE  prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$POST['eid']."'";
				$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));
				}
				else
				{
					$bom_process="SELECT * FROM `tbl_bom` as bom
					left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
					WHERE bom.bom_status=0 and prover.is_default_bom='1' AND bom.bom_product='".$POST['eid']."'";
				$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));
				}
				
			}
			
			
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
				
				
				/* START JAYESH */
				
				$workorder_query_pro="SELECT * FROM `tbl_bom` as bom
					left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
					left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
					left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id WHERE tbl_product_process.status = 0 and  prover.bom_version_id='".$POST['bom_version_id']."' AND pro_bom_process = '0' AND  bom.bom_product='".$POST['eid']."'"; 

					$workorder_query_result = $dbcon->query($workorder_query_pro);
			
				if(brp_mysqli_num_rows($workorder_query_result)>0)
				{
				while($wproduct_process=brp_mysqli_fetch_assoc($workorder_query_result))
				{
					$wwpp_info['product_id'] = $POST['eid'];		
					$wwpp_info['rp_id'] = 	$inserid;
					$wwpp_info['process_priority'] = 	$wproduct_process['priority'];
					$wwpp_info['process_time'] = 	$wproduct_process['process_time'];
					$wwpp_info['process_type'] = 	$wproduct_process['process_type'];
					$wwpp_info['process_opening'] = 	$wproduct_process['process_opening'];
					$wwpp_info['process_id'] = 	$wproduct_process['process_id'];	
					$wwpp_info['cdate']				= date("Y-m-d H:i:s");
					$wwpp_info['user_id']			= $_SESSION['user_id'];
					$wwpp_info['company_id']			= $_SESSION['company_id'];
					$wwpp_info['branch_id']			= $POST['branch_id'];
					
					$inserestimateid=add_record('tbl_wororder_product_process', $wwpp_info, $dbcon);
				}
				
				}
				/* END JAYESH */
			
			}else{
				$info['mdate']					=date('Y-m-d H:i:s');
				$info['muser_id']				=$_SESSION['user_id'];
				
				$updateid=update_record('tbl_set_main_process', $info,"sp_id=".$set_process_rel['sp_id'] ,$dbcon,$POST['branch_id']);
			}
			
			
			$set_pro="SELECT product_base_unit,product_conv_unit FROM `product_mst` WHERE product_status=0 AND product_id='".$POST['eid']."'";
			$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));
			
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
			
			$info_su['bom_id']				=$bom_rel['bom_id'];
			$info_su['product_version']		= $bom_rel['bom_id'];
			
			$inserid_sub1=add_record('tbl_request_product', $info_su, $dbcon,$POST['branch_id']);
			//var_dump($inserid_sub1);
	//for ($x = 0; $x <= 100; $x++) {	
		
		/*$query22="select bom_id,product_base_qty from tbl_bom as bom_trn 
				where bom_status=0 and bom_product=".$POST['eid'];	
			$result22=$dbcon->query($query22);
		$rel22=brp_mysqli_fetch_assoc($result22);
			*/
			
		$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=".$bom_rel['bom_id'];	
	$result1=$dbcon->query($query1);
	$i=1;$call=1;$space="";
	
	 while($rel1=brp_mysqli_fetch_assoc($result1)){  
	
			/* if($rel1['product_base_unit']!=$rel1['product_conv_unit']){ 
				$rel1['product_base_qty']  $rel1['base_unit_name']
				$rel1['product_conv_qty']  $rel1['conv_unit_name']
			}else{
				$rel1['product_base_qty']  $rel1['base_unit_name']
			} */
			
			$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
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
			
			$info_sub['product_version']	= $rel1['p_bom_id'];
			$info_sub['bom_id']				= $rel1['p_bom_id'];

			$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
			 //var_dump($inserid_sub);
			 
			 
			 /* START JAYESH */
			 
			 		/*   Material Formula */
						$material_query="select * from tbl_bom_material_trn where bom_trn_id=".$rel1['bom_trn_id']." AND bom_id =".$rel1['bom_id']; 	
						$material_result=$dbcon->query($material_query);
						if(brp_mysqli_num_rows($material_result) > 0)
						{
							while($mat_rel=brp_mysqli_fetch_assoc($material_result))
							{ 
								$mat_data['sp_id'] = $inserid; 
								$mat_data['rp_id'] = $inserid_sub; 
								$mat_data['product_id'] = $rel1['product_id']; 
								$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id']; 
								$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value']; 
								$mat_data['wo_material_trn_status'] = 0; 
								$mat_data['user_id']			= $_SESSION['user_id'];
								$mat_data['company_id']			= $_SESSION['company_id'];
								$mat_data['branch_id']			= $_SESSION['branch_id'];
								$inserid_sub=add_record('tbl_jobcard_material_trn', $mat_data, $dbcon,$POST['branch_id']);
								
							}
						}
						
						/*   Material Formula */
						
				/*	$query_pro1="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$rel1['product_id']; */
				
			/*$query_pro1="select* from pro_bom_process where product_id = ".$rel1['product_id']." AND bom_id =".$bom_rel['bom_id'];	*/
			
			$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status = 0 and  bom.bom_product='".$rel1['product_id']."' AND pro_bom_process = '0' AND  pro_bom_process.pr_process_id != ''"; 
		
			$rel_pro1 = $dbcon->query($query_pro1);
			
			if(brp_mysqli_num_rows($rel_pro1)>0)
			{
				while($product_process_row=brp_mysqli_fetch_assoc($rel_pro1))
				{
					$wpp_info['product_id'] = $rel1['product_id'];		
					$wpp_info['rp_id'] = 	$inserid_sub;
					$wpp_info['process_priority'] = 	$product_process_row['process_priority'];
					$wpp_info['process_time'] = 	$product_process_row['process_time'];
					$wpp_info['process_type'] = 	$product_process_row['process_type'];
					$wpp_info['process_opening'] = 	$product_process_row['process_opening'];
					$wpp_info['process_id'] = 	$product_process_row['process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $POST['branch_id'];
				
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
				}
			}
			 
			 
			 
			 /* END JAYESH */

			min_max_bom_show($dbcon,$rel1['p_bom_id'],$base_qty,$i,$call,$space,$inserid,$inserid_sub,'',$POST['branch_id']);
			$i++;
	} 
			
	//}
			
			/*$bom_exist=true;
			//Check BOM Exist or not of Product
			//if($POST['pr_type']=='0'){
				$bom_exist_qry="SELECT COUNT(bom_id) as bom_cnt FROM `tbl_bom` WHERE bom_status=0 AND bom_product=".$info['product_id'];
				$bom_exist_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_exist_qry));
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
		else if(brp_strtolower($POST['mode']) == "check_main_process_request") {
			
			$po_req_no=$POST['po_req_no'];
			$eid=$POST['eid'];
			$sales_order_trn_id=$POST['sales_order_trn_id'];
			$bom_version_id = $_POST['bom_version_id'];
			$json=array();
				
			$q=$dbcon->query("select * from  tbl_request_product where  status IN (0,3) and  company_id=".$_SESSION['company_id']." and rp_pid=".$eid." and job_card_no='".$po_req_no."'");
					
			$count=brp_mysqli_num_rows($q);
			if($count>0)
			{
				$row=brp_mysqli_fetch_array($q);				
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
		
		
		
		else if(brp_strtolower($POST['mode']) == "lock_main_request") {
			
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
		else if(brp_strtolower($POST['mode']) == "add_product_request") {
			
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
			$row=brp_mysqli_fetch_assoc($result);
				
				
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
						
			if($POST['res_qty']!="0"){
				if($POST['res_qty']!=""){
					add_request_reserve_stock($dbcon,$POST['rp_id'],$POST['res_qty'],$row['purchase_unit'], $_POST['branch_id']);
				}
			}
			//var_dump($de);
			
			if($POST['process_qty']!='')
			{
				if($POST['process_qty']!="0"){
					$queryw_b="select * from pro_bom_process where process_status=0 and bom_id=".$row['bom_id'];
					$rs_custw_b=$dbcon->query($queryw_b);	
					while($relw_b=brp_mysqli_fetch_array($rs_custw_b)){
						
						$queryw="select * from tbl_product_process where status = 0 and  pr_process_id=".$relw_b['pr_process_id'];
						$rs_custw=$dbcon->query($queryw);	
						$relw=brp_mysqli_fetch_array($rs_custw);
						
						$infow['product_id']		= $relw['product_id'];
						$infow['rp_id']				= $row['rp_id'];
						$infow['process_priority']	= $relw_b['priority'];;
						$infow['process_time']		= $relw['process_time'];
						$infow['process_type']		= $relw['process_type'];
						$infow['process_opening']	= $relw['process_opening'];
						$infow['process_id']		= $relw['process_id'];
						$infow['cdate']				= date('Y-m-d');
						$infow['user_id']			= $_SESSION['user_id'];
						$infow['company_id']		= $_SESSION['company_id'];
						
						//$inserid_a2=add_record('tbl_wororder_product_process', $infow, $dbcon, $_POST['branch_id']);

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
					$info5['product_version']	= $row['product_version'];

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
			
			$q_rel=brp_mysqli_fetch_assoc($q);
			$resp['trn_ids']=$q_rel['trn_ids'];
			$resp['insert_id']=$POST['rp_id'];
			
			echo json_encode($resp);

		}
		
	else if(brp_strtolower($POST['mode']) == "get_under_tree") {
			
			$trn_id=$POST['trn_id'];
			$req_no=$POST['po_req_no'];
			
			$q=$dbcon->query("select IFNULL(group_concat(bom_trn_id),0) as trn_ids from tbl_bomtrn where bom_trn_status=0 and parent_id='$trn_id'");
				
			$q_rel=brp_mysqli_fetch_assoc($q);
			$trn_id_new=$q_rel['trn_ids'];
			$trn_id1=explode(",",$trn_id_new);
			$data="";
			foreach($trn_id1 as $t)
			{
				$q1=$dbcon->query("select row_cnt from tbl_request_product where row_cnt='$t'");
				$c1=brp_mysqli_num_rows($q1);
				if($c1==0)
				{
					$data.=",".$t;
				}
				$resp['trn_ids']=$data;
			}
			
			
			//$resp['count']=$count1;
			echo json_encode($resp);

		}
		else if(brp_strtolower($POST['mode']) == "work_order_submit_per") {
			$query11="select count(rp_id) as pending_req from tbl_request_product where main_request!=1 and status in (0,3) and user_id=".$_SESSION['user_id']." and sp_id=".$POST['work_order_id'];
				$rows1=brp_mysqli_fetch_assoc($dbcon->query($query11));
			if($rows1['pending_req']==0){
				echo "1";
				
			}else{
			
				$query1="select count(rp_id) as pending_req from tbl_request_product where main_request!=1 and user_id=".$_SESSION['user_id']." and status=3 and sp_id=".$POST['work_order_id'];
				$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
				if($rows['pending_req']>0){
					echo "2";	
				}else{
					echo "1";
					//echo $query1;
				}
			}
		}
		
		/* START JAYESH */ 
		
		else if(brp_strtolower($POST['mode']) == "add_work_order_product") {
			
			$product_id = $_POST['product_id'];
			$qty = $_POST['qty'];
			@$sales_order_date = $_POST['sales_order_date'];
			@$sales_order_no = $_POST['sales_order_no'];
			
			if($sales_order_no !='' && $sales_order_date !='' )
			{
				$test = '<tr>
								<th colspan="2">Sales Order No : <span style="color: red;">'.@$sales_order_no.' </span></th>
								<th colspan="3">Sales Order Date: '.@$sales_order_date.' </span></th>
								
							</tr>';
			}
			else
			{
				$test = '';
			}
			
			
			$str.='<table class="table table-bordered">
							<tr>
								<th colspan="2">Product Name : <span style="color: red;">'.$_POST["product_name"].' </span></th>
								<th colspan="3">Qty: '.$qty.' </span></th>
								
							</tr>'.$test.'						
							<tr>
								<th colspan="2" style="text-align: center;"> <strong>Product Type :</strong></th>
				
								<th colspan="3"><select class="select3" title="Select product" name="wo_product_type" id="wo_product_type" onchange="load_product(this.value);">'.								
								get_product_type_company($dbcon,'','').'</select>
								</th></tr>
					<tr>
								<th colspan="2" style="text-align: center;"> <strong>Product:</strong></th>
				
								<th colspan="3">
								<input id="wo_product_id" name="wo_product_id"  style="width:100% !important"  placeholder="Select product" onchange="check_bom_version(this.value,1);check_product_unit(this.value,1);load_product_detail(this.value);" />

							<!--	<select class="select3 selproduct1" title="Select product" name="wo_product_id" id="wo_product_id" onchange="check_bom_version(this.value,1);check_product_unit(this.value,1);load_product_detail(this.value);"  required>								
								<option value="">Choose Product</option>'.
							getproduct($dbcon,'').'</select> -->
								</th></tr>
						
						<tr>
						<th colspan="5"><div id="get_spec_div" style="display:none"></div></th>
						</tr>
						
						<tr>
								<th colspan="2" style="text-align: center;"> <strong>Bom Version:</strong></th>
				
								<th colspan="3"><select class="select3 selproduct1" title="Select Bom Version" name="add_bom_version_id" id="add_bom_version_id"><option selected="selected" value="10000">R&D</option></select>
								</th></tr>
						
						<tr>
								<th colspan="2" style="text-align: center;"> <strong>Qty:</strong></th>
				
								<th colspan="2">
									<input type="text" id="product_qty" name="product_qty" class="form-control  digitOnly" onkeydown="return digitonly(event);" required>
									<input type="hidden" id="prod_id" value="'.$product_id.'">
									<input type="hidden" id="qty" value="'.$qty.'">	
								</th>
								<th><span id="product_unit"></span></th></tr>
								
										
					<tr>
							
								<th colspan="5"  style="text-align: center;"><button type="button" onclick="save_work_order_product();" class="btn btn-success" id="save" name="save">Save</button>
						</th></tr></table>';							
			echo $str;
		}
		else if(brp_strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and p.product_type='.$type_id.'');
		}
		else if(brp_strtolower($POST['mode']) == "edit_work_order_product") {
				
			$product_id = $_POST['product_id'];
			$rp_pid = $_POST['rp_pid'];
			$rp_id = trim($_POST['rp_id']);
			$rs=$dbcon->query("select product_type from  product_mst where product_id=".$rp_pid);
			$row=brp_mysqli_fetch_array($rs);
			$product_type = $row['product_type'];
			$rp_pro_qty = $_POST['rp_pro_qty'];
			
			
			$parent_query="select i.perent_id, i.rp_req_qty,i.rp_id,i.in_process_qty,pro.product_name,i.rp_pid,i.job_card_status from tbl_request_product as i
			left join product_mst as pro on pro.product_id=i.rp_pid where i.rp_id=".$rp_id;
			$parent_result=$dbcon->query($parent_query);
			
			if($parent_row=brp_mysqli_num_rows($parent_result) > 0 )
			{
				$parent_row=brp_mysqli_fetch_assoc($parent_result);
			
			$query="select i.rp_req_qty,i.rp_id,i.in_process_qty,pro.product_name,i.rp_pid,i.job_card_status from tbl_request_product as i
			left join product_mst as pro on pro.product_id=i.rp_pid
			where i.rp_id=".$parent_row['perent_id'];
			$result=$dbcon->query($query);
			$pro_row=brp_mysqli_fetch_assoc($result);
			$prodcuct_name = $pro_row["product_name"];
			$qty= $pro_row['rp_req_qty'];
			
			}
			else
			{
				$prodcuct_name = $parent_row["product_name"];
				$qty= $parent_row['rp_req_qty'];
			
			}

			
			
			
			
			//$qty = $_POST['qty'];
			$str.='<table class="table table-bordered">
							<tr>
								<th colspan="5" style="text-align: center;font-size: 20px;">'.$POST["product_name"].'</th>
								
							</tr>
							<tr>
								<th colspan="2">Product Name : <span style="color: red;">'.$prodcuct_name.' </span></th>
								<th colspan="3">Qty: '.$rp_pro_qty.' </span></th>
								
							</tr><tr>
								<th colspan="5" style="text-align: center;">
								
				<tr>
								<th colspan="2">Product Type :</th>
									<th colspan="3"><select class="select2 selproduct1" title="Select product" name="wo_product_type" id="wo_product_type" onchange="load_product(this.value)">'.								
								get_product_type_company($dbcon,$product_type,'').'</select>
								</th></tr>
				<tr>
								<th colspan="2">Product:</th>
								<th colspan="3"><select class="select2 selproduct1" title="Select product" name="wo_product_id" id="wo_product_id">'.								
								getproduct_typewise($dbcon,$rp_pid,$product_type).'</select>
								</th></tr>
						<tr>
						<th colspan="5"><div id="get_spec_div" style="display:none"></div></th>
						</tr>
					<tr>
								<th colspan="2">Qty :</th>
								<th colspan="3">	<input class="form-control" type="text" id="product_qty" name="product_qty" value="'.$rp_pro_qty.'" onkeydown="return digitonly(event);">
									<input type="hidden" id="prod_id" value="'.$product_id.'">
									<input type="hidden" id="qty" value="'.$qty.'">	
								</th>
					</tr>
										
						<tr>
							
							<th colspan="5" style="text-align:center;"> 					
							<button  type="button" onclick="edit_save_work_order_product('.$rp_id.');" class="btn btn-success" id="save" name="save">Save</button>
						</th></tr></table>';							
			echo $str;
		}
		else if(brp_strtolower($POST['mode']) == "save_work_order_product") {
			
					$main_product_id = $POST['main_product_id'];
					$bom_version_id =$POST['bom_version_id'];
					$jobcard_no = $_POST['sp_id'];
					$req_qty_one = $POST['product_qty'] / $POST['qty'];
					if($bom_version_id == "10000")
					{						
						$sp_query = "select * from  tbl_request_product where job_card_no = '$jobcard_no'"; 
					
						$sp_rs=$dbcon->query($sp_query);
						$sp_row = brp_mysqli_fetch_array($sp_rs);
						$rp_id = $sp_row['rp_id'];
						$sp_query1 = "select * from  tbl_request_product where sp_id = '$sp_id'  AND  perent_id='$rp_id'"; 
						$sp_rs1=$dbcon->query($sp_query1);
						
						$counter = brp_mysqli_num_rows($sp_rs1)+1; 
							
						$info2['sp_id']					= 0;
						$info2['sr_no']					= $counter;
						$info2['rp_req_no']				= '';
						$info2['rp_req_date']			= date("Y-m-d");
						$info2['rp_pid']				= $_POST['wo_product_id'];
						// $info2['rp_req_qty']			= $POST['qty']* $POST['product_qty'];
						$info2['rp_req_qty']			= $POST['product_qty'];
						$info2['req_qty_one']			= $req_qty_one;
						$info2['rp_po_qty']				= 0;
						// $info2['in_process_qty']		= $POST['qty']* $POST['product_qty'];
						$info2['in_process_qty']		= $POST['product_qty'];
						$info2['out_process_qty']		= '';
						$info2['rp_req_type']			= 'job_card';
						$info2['rp_po_req_no']			= '';
						$info2['rp_process_req_no']		= '';
						$info2['cdate']					= strtotime(date("Y-m-d"));
						$info2['user_id']				= $_SESSION['user_id'];		
						$info2['company_id']			= $_SESSION['company_id'];	
						$info2['status']				= 3;	
						$info2['row_cnt']				= 0;	
						$info2['process_unit']			= 3;	
						$info2['purchase_unit']			= 3;
						$info2['reserve_status']		= 0;
						$info2['used_rp_req_qty']		= '';
						$info2['used_status']			= 0;
						$info2['perent_id']				= $rp_id;	
						$info2['reserve_stock']			= '';	
						$info2['main_request']			= 0;
						$info2['indent_no']				= '';	
						$info2['indent_date']			= '';
						$info2['indent_status']			= '';		
						$info2['job_card_no']			= '';
						$info2['job_card_date']			= '';		
						$info2['job_card_status']		= '';
						$info2['reject_status']			= 0;
						$info2['sales_order_trn_id']	= 0;
						$info2['branch_id']				= '';
						$info2['finish_used_qty']		= '';
						$info2['finish_status']			= 0;
						$info2['product_version']		= '';	
						$info2['pre_trn_id']			= 0;	
						$info2['shortclose_qty']		= 0;
						$info2['shortclose_remark']		= '';												
										
						$table='tbl_request_product';	
						//echo "<pre>"; print_r($info2);die;
						$reqinserid=add_record($table, $info2, $dbcon);
					
			}
			else
			{
				
						//$row=brp_mysqli_fetch_array($rs);
					
						$sp_query = "select * from  tbl_request_product where job_card_no = '$jobcard_no'"; 
						/*$sp_query = "select * from  tbl_request_product where sp_id = '$sp_id' AND perent_id='0' AND main_request !='1'"; */
						$sp_rs=$dbcon->query($sp_query);
					 	$counter = brp_mysqli_num_rows($sp_rs)+1;
						$sp_row = brp_mysqli_fetch_array($sp_rs);
						$rp_id = $sp_row['rp_id'];
						if($rp_id != '')
						{
							$sp_query1 = "select * from  tbl_request_product where perent_id = '$rp_id'";  
							$sp_rs1=$dbcon->query($sp_query1);
							if(brp_mysqli_num_rows($sp_rs1)> 0)
							{
							$counter = brp_mysqli_num_rows($sp_rs1)+1;
							}
							else
							{
							$counter = brp_mysqli_num_rows($sp_rs)+1;
							}
						}
						else
						{
						$counter = brp_mysqli_num_rows($sp_rs)+1;
						}
				
				
				$bom_process="SELECT * FROM `tbl_bom` as bom
				left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
				WHERE  prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$POST['wo_product_id']."'";
				
				$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));
				$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
				left join product_mst as pro on pro.product_id=bom_trn.product_id
				left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
				left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
				where bom_trn_status=0 and bom_id=".$bom_rel['bom_id'];	 
				$result1=$dbcon->query($query1);
			
					$call=1;$space="";
					$i = $counter;

					while($rel1=brp_mysqli_fetch_assoc($result1)){  
						
						$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
						$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

						$base_qty=$base_one_qty*$info_su['rp_req_qty'];
						$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

						$info_sub['sp_id']				= 0;
						$info_sub['sr_no']				= $i;
						$info_sub['rp_pid']				= $rel1['product_id'];
						$info_sub['rp_req_qty']			=  $POST['qty']* $POST['product_qty'];
						//$info_sub['rp_req_qty']			= $conv_stock;//required qty
						$info_sub['req_qty_one']		= $conv_one_qty;//required qty
						$info_sub['rp_po_qty']			= "";//po qty
						$info_sub['in_process_qty']		= $POST['qty']* $POST['product_qty'];//process qty
						$info_sub['rp_req_type']		= "job_card";//type
						$info_sub['process_unit']		= $rel1['product_base_unit'];
						$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub['perent_id']			= $rp_id;
						$info_sub['status']				= 3;
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						//$info_sub['main_request']		= $POST['g_total'];
						
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
						
						//echo "<pre>"; print_r($info_sub);die;
						$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
						
					$query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.status = 0 and  prod.product_id = ".$rel1['product_id']; 
		
			$rel_pro = $dbcon->query($query_pro);
			
			if(brp_mysqli_num_rows($rel_pro)>0)
			{
				while($product_process=brp_mysqli_fetch_assoc($rel_pro))
				{
					$wpp_info['product_id'] = $rel1['product_id'];		
					$wpp_info['rp_id'] = 	$inserid_sub;
					$wpp_info['process_priority'] = 	$product_process['process_priority'];
					$wpp_info['process_time'] = 	$product_process['process_time'];
					$wpp_info['process_type'] = 	$product_process['process_type'];
					$wpp_info['process_opening'] = 	$product_process['process_opening'];
					$wpp_info['process_id'] = 	$product_process['process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $POST['branch_id'];
					
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
				}
			}
			
			
			/*   Material Formula */
						$material_query="select * from tbl_bom_material_trn where bom_trn_id=".$rel1['bom_trn_id']." AND bom_id =".$rel1['bom_id']; 	
						$material_result=$dbcon->query($material_query);
						if(brp_mysqli_num_rows($material_result) > 0)
						{
							while($mat_rel=brp_mysqli_fetch_assoc($material_result))
							{ 
								$mat_data['sp_id'] = $inserid; 
								$mat_data['rp_id'] = $inserid_sub; 
								$mat_data['product_id'] = $rel1['product_id']; 
								$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id']; 
								$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value']; 
								$mat_data['wo_material_trn_status'] = 0; 
								$mat_data['user_id']			= $_SESSION['user_id'];
								$mat_data['company_id']			= $_SESSION['company_id'];
								$mat_data['branch_id']			= $_SESSION['branch_id'];
								$inserid_sub=add_record('tbl_jobcard_material_trn', $mat_data, $dbcon,$POST['branch_id']);
								
							}
						}
						
						/*   Material Formula */
			
			
					
				
				bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$i,$POST['qty']* $POST['product_qty']);		
					$i++;
					}	
					}
						
					
			//}
			echo "1";
		}
		else if(brp_strtolower($POST['mode']) == "add_work_order_sub_product") {
			
			$rp_id = $_POST['rp_id'];
			$sub_product_id = $_POST['sub_product_id'];
			$main_product_id = $_POST['main_product_id'];
			$qty = $_POST['qty'];
			$sales_order_date = $_POST['sales_order_date'];
			$sales_order_no = $_POST['sales_order_no'];
			
			if($sales_order_no !='' && $sales_order_date !='' )
			{
				$test = '<tr>
								<th colspan="2">Sales Order No : <span style="color: red;">'.@$sales_order_no.' </span></th>
								<th colspan="3">Sales Order Date: '.@$sales_order_date.' </span></th>
								
							</tr>';
			}
			else
			{
				$test = '';
			}
			
			$q = "select i.rp_id,i.in_process_qty,pro.product_name,i.rp_pid,i.job_card_status from tbl_request_product as i left join product_mst as pro on pro.product_id=i.rp_pid	where i.rp_id=".$rp_id;
			$r=$dbcon->query($q);
			$pro_row=brp_mysqli_fetch_assoc($r);
			//$parent_id = $p_row['perent_id']; 
			
	
			
			
			if($sales_order_no == '' && $sales_order_date == '' )
			{
				$show_so = 0;
			}
			else
			{
				$show_so = 1;
			}
		
			
			
			
			$str.='<table class="table table-bordered">
							<tr>
								<th colspan="5" style="text-align: center;font-size: 20px;">'.$POST["product_name"].'</th>	
							</tr>
								<tr>
								<th colspan="2">Product Name : <span style="color: red;">'.$pro_row["product_name"].' </span></th>
								<th colspan="3">Qty: '.$qty.' </span></th>
								
							</tr>'.$test.'
							<tr>
							<tr>
								<th colspan="2" style="text-align: center;"> <strong>Product Type :</strong></th>
				
								<th colspan="3"><select class="select3 modal_select" title="Select product" name="wo_product_type" id="wo_product_type" onchange="load_product(this.value,1)">'.						
								get_product_type_company($dbcon,'','').'</select>
							</th></tr>
							<tr>
								<th colspan="2" style="text-align: center;"> <strong>Product:</strong></th>
								<th colspan="3">
								<input id="wo_sub_product_id" name="wo_sub_product_id"  style="width:100% !important"  placeholder="Select product" onchange="check_bom_version(this.value,2);check_product_unit(this.value,2);load_product_detail(this.value);" />
								<!--
								<select class="select3" title="Select product" name="wo_sub_product_id" id="wo_sub_product_id" onchange="check_bom_version(this.value,2);check_product_unit(this.value,2);load_product_detail(this.value);">'.								
								getproduct($dbcon,'').'</select> -->
								</th>
								</tr>								
								<tr>
								<th colspan="2" style="text-align: center;"> <strong>Bom Version:</strong></th>
				
								<th colspan="3"><select class="select3 modal_select" title="Select Bom Version" name="add_bom_version_id" id="add_bom_sub_version_id"">
														
														
								</select>
								</th></tr>
								<tr>
						<th colspan="5"><div id="get_sub_spec_div" style="display:none"></div></th>
						</tr>
							<tr>
								<th colspan="2" style="text-align: center;"> <strong>Qty:</strong></th>
								<th colspan="2">
								<input type="number" id="sub_product_qty" name="product_qty" value="" class="form-control  digitOnly" onkeydown="return digitonly(event);"> 
								<input type="hidden" id="sub_product_id" value="'.$sub_product_id.'">
								<input type="hidden" id="main_product_id" value="'.$main_product_id.'">
								<input type="hidden" id="qty" value="'.$qty.'">		
								<input type="hidden" id="rp_id" value="'.$rp_id.'">	
								</th>
								<th><span id="sub_product_unit"></span></th>
							</tr>	
						<tr>
								<th colspan="5" style="text-align: center;">
							<button type="button" onclick="save_work_order_sub_product();" class="btn btn-success" id="save" name="save">Save</button>
						</th></tr></table>';							
			echo $str;
		}
		
		else if(brp_strtolower($POST['mode']) == "save_work_order_sub_product") {
			// print_r($POST);die();
			$sub_product_id = $POST['sub_product_id'];
			$main_product_id = $POST['main_product_id'];
			$qty = $POST['qty'];
			$product_qty = $POST['product_qty']; 
			$rp_id = $POST['rp_id'];
			$req_qty_one = $POST['product_qty'] / $POST['qty'];
			$bom_version_id = $POST['bom_version_id'];
						
						$sp_query = "select * from  tbl_request_product where rp_id = '$rp_id'";
						$sp_rs=$dbcon->query($sp_query);
						$sp_row = brp_mysqli_fetch_array($sp_rs);	
						//echo "<pre>"; print_r($sp_row);
						$sp_id = $sp_row['sp_id'];	
						$sp_counter_query = "select count(rp_id) as rp_counter,perent_id from  tbl_request_product where perent_id = '$rp_id' ";
						$sp_counter_rs=$dbcon->query($sp_counter_query);
						$sp_counter_row = brp_mysqli_fetch_array($sp_counter_rs);
						
						if($sp_counter_row['rp_counter'] < 1)
						{
							$sr_no = $sp_row['sr_no'].'.1';
						}
						else
						{
							$paerent_id = $sp_counter_row['perent_id'];							
							$parent_sr_query = "select * from  tbl_request_product where rp_id = '$paerent_id'";
							$parent_sr_res=$dbcon->query($parent_sr_query);
							$parent_sr_row = brp_mysqli_fetch_array($parent_sr_res);						
							$sp_counter_row = $sp_counter_row['rp_counter']+1;
							$sr_no = $parent_sr_row['sr_no'].'.'.$sp_counter_row;
						}
						
					if($bom_version_id == "10000" )
					{
						$info2['sp_id']					= $sp_id;
						$info2['sr_no']					= $sr_no;
						$info2['rp_req_no']				= '';
						$info2['rp_req_date']			= date("Y-m-d");
						$info2['rp_pid']				= $sub_product_id;
						$info2['rp_req_qty']			= $product_qty;
						// $info2['rp_req_qty']			= $qty*$product_qty;
						$info2['req_qty_one']			= $req_qty_one;
						$info2['rp_po_qty']				= 0;
						// $info2['in_process_qty']		= $qty*$product_qty;
						$info2['in_process_qty']		= $product_qty;
						$info2['out_process_qty']		= '';
						$info2['rp_req_type']			= 'job_card';
						$info2['rp_po_req_no']			= '';
						$info2['rp_process_req_no']		= '';
						$info2['cdate']					= strtotime(date("Y-m-d"));
						$info2['user_id']				= $_SESSION['user_id'];		
						$info2['company_id']			= $_SESSION['company_id'];	
						$info2['status']				= 3;	
						$info2['row_cnt']				= 0;	
						$info2['process_unit']			= 3;	
						$info2['purchase_unit']			= 3;
						$info2['reserve_status']		= 0;
						$info2['used_rp_req_qty']		= '';
						$info2['used_status']			= 0;
						$info2['perent_id']				= $rp_id;	
						$info2['reserve_stock']			= '';	
						$info2['main_request']			= 0;
						$info2['indent_no']				= '';	
						$info2['indent_date']			= '';
						$info2['indent_status']			= '';		
						$info2['job_card_no']			= '';
						$info2['job_card_date']			= '';		
						$info2['job_card_status']		= '';
						$info2['reject_status']			= 0;
						$info2['sales_order_trn_id']	= 0;
						$info2['branch_id']				= '';
						$info2['finish_used_qty']		= '';
						$info2['finish_status']			= 0;
						$info2['product_version']		= '';	
						$info2['pre_trn_id']			= 0;	
						$info2['shortclose_qty']		= 0;
						$info2['shortclose_remark']		= '';							
						$table='tbl_request_product';
						
						$reqinserid=add_record($table, $info2, $dbcon);
				}else
				{
					
					$sp_counter_rs=$dbcon->query($sp_counter_query);
					$sp_counter_row = brp_mysqli_fetch_array($sp_counter_rs);
						
					$sp_rs=$dbcon->query($sp_query);
					$sp_row = brp_mysqli_fetch_array($sp_rs); 
					$i = 1; 
					
					
					if($sp_counter_row['rp_counter'] < 1)
						{
							
							$sr_no = $sp_row['sr_no'];
						}
						else
						{
							
							$paerent_id = $sp_counter_row['perent_id'];							
							$parent_sr_query = "select * from  tbl_request_product where perent_id = '$paerent_id'";
							$parent_sr_res=$dbcon->query($parent_sr_query);
							$parent_sr_row = brp_mysqli_fetch_array($parent_sr_res);						
							$sp_counter_row = $sp_counter_row['rp_counter']+$i;
							$sr_no = $parent_sr_row['sr_no'].'.'.$sp_counter_row;
							$sr_no = $parent_sr_row['sr_no'];
						}
						
			
				$bom_process="SELECT * FROM `tbl_bom` as bom
				left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
				WHERE  prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$POST['sub_product_id']."'";
				$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));
				$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
				left join product_mst as pro on pro.product_id=bom_trn.product_id
				left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
				left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
				where bom_trn_status=0 and bom_id=".$bom_rel['bom_id'];	 

				$result1=$dbcon->query($query1);
				$call=1;$space="";
				
			

					while($rel1=brp_mysqli_fetch_assoc($result1)){  

						$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
						$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

						$base_qty=$base_one_qty*$info_su['rp_req_qty'];
						$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

						$info_sub['sp_id']				= $sp_id;
						$info_sub['sr_no']				= $sr_no.'.'.$i;
						$info_sub['rp_req_no']			= '';
						$info_sub['rp_req_date']		= date("Y-m-d");
						$info_sub['rp_pid']				= $rel1['product_id'];
						$info_sub['rp_req_qty']			= $qty*$product_qty;
						$info_sub['req_qty_one']		= 1;//required qty
						$info_sub['rp_po_qty']			= "";//po qty
						$info_sub['in_process_qty']		= $qty*$product_qty;//process qty
						$info_sub['rp_req_type']		= "job_card";//type
						$info_sub['process_unit']		= $rel1['product_base_unit'];
						$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub['perent_id']			= $rp_id;
						$info_sub['status']				= 3;
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
						//echo "<pre>"; print_r($info_sub);die;
						$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
						
						$bomq = "select * from tbl_bomtrn where bom_id =".$rel1['p_bom_id'];
						$bomres=$dbcon->query($bomq);
						if(brp_mysqli_num_rows($bomres) > 0)
						{
							bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$sr_no.'.'.$i,$qty*$product_qty);	
						}
						
						
				$i++;
					}	
					}
						
					
			
			
			echo "1";
		
		}
		else if(brp_strtolower($POST['mode']) == "delete_work_order_product") {
			
			$rp_id = $POST['rp_id'];
			$sp_id = $POST['sp_id'];
			
			$parent_delete_flag = $POST['parent_delete_flag']; 
			if($parent_delete_flag == "1")
			{ 
				$perenet_ids = implode(",",delete_recurring_products($dbcon,$rp_id));
				$parent_sr_res=$dbcon->query("delete from  tbl_request_product where rp_id = '$rp_id' OR rp_id IN($perenet_ids)");				
			}
			else
			{
				$parent_sr_res=$dbcon->query("delete from  tbl_request_product where rp_id = '$rp_id'");
			}
			 
			if(brp_mysqli_affected_rows($dbcon) > 0)
			{	
				$delete_wp_res=$dbcon->query("delete from  tbl_wororder_product_process  where rp_id = '$rp_id'");
				update_srno_recurring_products($dbcon,$sp_id);
				echo "1";
			}
			else
			{
				echo "0";
			}					
		}
		else if(brp_strtolower($POST['mode']) == "edit_save_work_order_product") {
			$rp_id = $POST['rp_id'];
			$q = "select * from tbl_request_product where rp_id = '$rp_id'"; 
			$rs=$dbcon->query($q);	
			
			if(brp_mysqli_num_rows($rs) > 0 )
			{
				$info['rp_pid']	= $_POST['wo_product_id'];
				$info['rp_req_qty']	= $_POST['rp_product_qty'];
				
				$deleteid=delete_record('tbl_wororder_product_process', "rp_id=$rp_id", $dbcon);
				$row = brp_mysqli_fetch_array($rs);
				
				if($_POST['wo_product_id'] !=  $row['rp_pid'])
				{
					$deleteid=delete_record('tbl_request_product', "perent_id=$rp_id", $dbcon);
				}
				
				$updateid=update_record("tbl_request_product", $info,"rp_id=".$rp_id, $dbcon);	
				echo "1";
			}
			else
			{		
				echo "0";	
			}
		}
		else if(brp_strtolower($POST['mode'])== "delete_data_process")
		{
			
			$deleteid=delete_record('tbl_wororder_product_process', "pr_process_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
else if(brp_strtolower($POST['mode']) == "check_bom_version_by_product") {
		$branch_where="";
		$str='';
		if(!empty($POST['branch_id'])){
			$branch_where=" and branch_id=".$POST['branch_id'];
		}
		
		$company_where="";
		if(!empty($_SESSION['company_id'])){
			$company_where=" and company_id=".$_SESSION['company_id'];
		}
		
		$qry="SELECT * from pro_ms_bom_version where bom_version_status = 0 and product_id=".$POST['product_id']." ".$company_where;
		$result=$dbcon->query($qry);
		
		if(brp_mysqli_num_rows($result) > 0)
		{	
			while($row=brp_mysqli_fetch_assoc($result))
			{
				$str .= '<option value="'.$row['bom_version_id'].'">'.$row['version_name'].'</option>';
			}
				$str .= '<option  value="10000">R&D</option>';
				echo $str;
		}
		else
		{
			echo "0";
		}
	}
		
	else if(brp_strtolower($POST['mode']) == "get_product_process_data") {

		if($POST['rp_id']!='')
		{
			 $q = "select process_id from tbl_wororder_product_process where  product_id =".$POST['product_id']." AND rp_id =".$POST['rp_id']."  order by process_priority ASC";
		}
		else
		{
			
			$q = "select process_id from tbl_wororder_product_process where  product_id =".$POST['product_id']." order by process_priority ASC";
		}
		//echo $q;
		
		//bom_version_id
		
		
			$res_pro = $dbcon->query($q);
			$arr_process=brp_mysqli_fetch_all($res_pro);
			
				
			$multiple_value =implode(',', array_column($arr_process, 'process_id'));
			
			
			//echo "<pre>"; print_r($multiple_value); die;

			$query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.status = 0 and prod.product_id = ".$POST['product_id'];
		
			$rel_pro = $dbcon->query($query_pro);
			$i=1;
			$str='<div class="row m-bot20"><div class="col-md-6 text-center"> <h4>All Process</h4></div>
			<div class="col-md-6 text-center"><h4>Selected Process as priority</h4></div>	</div>
			<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
			    <input type="hidden" name="multiple_value" id="multiple_value" value="'.$multiple_value.'"/>
 				<input type="hidden" name="process_sel_product_id" id="process_sel_product_id" value="'.$POST['product_id'].'"/>
 				
			<select class="multi-select" multiple=""  name="process_item[]" id="process_item" >';
		while($product_process=brp_mysqli_fetch_assoc($rel_pro)){
			
			
			$selected="";
			if(empty($POST['edit_id'])){
				//echo "helllo";
				// $selected = "ms-selected";	
			}
			// else if(in_array($product_process['pr_process_id'], $arr_process)){
			else if(array_search($product_process['process_id'], array_column($arr_process, 'process_id')) !== false){
					$selected="ms-selected";
			}

			$str .=  '<option class="process_row '.$selected.'" data-cid="'.$i.'" value="'.$product_process['process_id'].'">' . $product_process['process_name'] . '</option>';
			
		
		$i++;
	
		}
		$product_process = brp_mysqli_fetch_assoc($rel_pro);
		
		if(brp_mysqli_num_rows($rel_pro) > 0){
			if(isset($POST['direct'])){
				$function = 'direct_bom_process_add('.$POST['product_id'].','.$POST['bom_version_id'].',"direct")'; 					
			}else{
				$function = 'bom_process_add('.$POST['rp_id'].')'; 	
			}
			
			$str.="</select>
					<div class='col-md-12' >
						
					</div>
					<div class='col-md-12' style='margin-top: 15px;'>
						<div class='col-md-4' >
							<center>
							
								<button type='button' id='process_save' onClick='".$function ."' name='process_save' class='btn btn-success' >Submit</button>
							</center>
						</div>
						
					</div>
					</form>
			";
		}else{
			
			$str = '<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
			    <input type="hidden" name="multiple_value" id="multiple_value" value=""/>
			   
					<div class="col-md-12" style="margin-top: 15px;">
						<h3>NO PROCESS ADDED</h3>
					</div>
					</form>
					
			';
		}
			
			
			echo $str;
		}
		// Process Parameter
		else if(brp_strtolower($POST['mode']) == "add_process_value") {

			$product_id=$POST['product_id'];
			$process_id = $POST['process_id'];
			$query="select pr_process_id from tbl_product_process where status = 0 and  product_id=".$product_id." and process_id=".$process_id;
			$result=$dbcon->query($query);
			$count=brp_mysqli_num_rows($result);
			if($count > 0){
				$arr['msg']="exist";
			}else{
				$info1['process_id']= $process_id;
				$info1['process_rate']= $POST['process_rate'];
				$info1['process_priority']= $POST['process_priority'];
				$info1['process_type']= $POST['process_type'];
				$info1['product_id']= $product_id;
				$info1['process_time']= $POST['process_time'];
				$info1['process_opening']= $POST['process_opening'];
				$info1['resource_id']= $POST['resource_id'];
				$info1['process_loss']= $POST['process_loss'];
				$info1['process_scrap_tolerance_plus']= $POST['process_scrap_tolerance_plus'];
				$info1['process_scrap_tolerance_minus']= $POST['process_scrap_tolerance_minus'];
			
				$info1['cdate'] = date("Y-m-d");
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']			= $_SESSION['company_id'];
				
				$table='tbl_product_process';$tableid='pr_process_id';
				
				
				$inserid=add_record($table, $info1, $dbcon);

				if($inserid){
					update_product_setting($dbcon,$product_id,'process_product');
					$log_entry=common_log_entry($dbcon,"product_process_add",1,"tbl_product_process",$inserid);
					$arr['msg']="1";
					$arr['process_id']=$inserid;
				}else{
					$arr['msg']="0";
				}
			}

			echo json_encode($arr);
				
		}
		else if(brp_strtolower($POST['mode'])== "check_duplicate_process")
	{
			$product_id=$POST['product_id'];
			$process_id = $POST['process_id'];
			$query="select pr_process_id from tbl_product_process where status = 0 and  product_id=".$product_id." and process_id=".$process_id;
			// echo $query;
			$result=$dbcon->query($query);
			$count=brp_mysqli_num_rows($result);
		echo $count;
	}
	else if(brp_strtolower($POST['mode'])== "check_product_process")
	{
		$product_id=$POST['product_id'];
		$bom_version_id = $POST['bom_version_id'];
			$query="select pro_bom_process_id from pro_bom_process where process_status = 0 and product_id=".$product_id." and bom_version_id = " . $bom_version_id;
			$result=$dbcon->query($query);
			$count=brp_mysqli_num_rows($result);
		echo $count;
	}
		
	 /*  END :: Code By : Jayesh ::   29-07-2021 */

	else if(brp_strtolower($POST['mode'])== "check_duplicate")
	{
		/*jayesh :: Added Bom version condition - 04-08-2021 */

		$pro_id=$POST['pro_id'];
		$bom_version_id = $POST['bom_version_id'];
		$query="select bom_id from tbl_bom where bom_status=0 and bom_product=".$pro_id." AND bom_version_id =". $bom_version_id;
		// echo $query;die;
		$result=$dbcon->query($query);
		$count=brp_mysqli_num_rows($result);
		echo $count;
	}
		else if(brp_strtolower($POST['mode'])== "check_version_name")
	{
		/*Sanat :: Added Bom version condition - 04-08-2021 */

		$product_id=$POST['product_id'];
		$version_name = $POST['version_name'];

		$bom_version_id = $POST['bom_version_id'];
		$whr = "";
		if($bom_version_id !=""){
				$whr.= " AND bom_version_id !=" . $bom_version_id;
		}
		$query="select bom_version_id from  pro_ms_bom_version where bom_version_status=0 and version_name='".$version_name	."' AND product_id =". $product_id ." " .$whr;
		// echo $query;die;
		$result=$dbcon->query($query);
		$count=brp_mysqli_num_rows($result);
		echo $count;
	}
	else if(brp_strtolower($POST['mode']) == "bom_process_add") {

		$product_id = $POST['product_id'];
		$q = "select pr_process_id from tbl_wororder_product_process where product_id =".$POST['product_id']." order by process_priority ASC";

		$res_pro = $dbcon->query($q);
		$arr_process = brp_mysqli_fetch_all($res_pro);
		$hidden = $_POST['multiple_value']; //get the values from the hidden field
		$hidden_in_array = explode(",", $hidden); //convert the values into array
		
		
		$filter_array = array_filter($hidden_in_array); //remove empty index 
		$arr_sel_process = array_values($filter_array); //reset the array key 

		$unsel_process = $POST['unsel_process'];
		$arr_unsel_process = explode(',',$unsel_process);

		$info['product_id'] = $product_id;		
		$info['rp_id'] = 	$POST['rp_id'];		
		$info['cdate']				= date("Y-m-d H:i:s");
		$info['user_id']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];

		$del_id =	delete_record('tbl_wororder_product_process' ,'rp_id = ' . $POST['rp_id'] . ' and product_id =' .$product_id ,$dbcon);
		
		
		$x = 1;
		foreach ($arr_sel_process as $process_id) {
			$p_qry = "select process_type,process_time,process_opening from tbl_product_process where status = 0 and  product_id = " . $product_id . " and process_id = " . $process_id;
			$p_pro = $dbcon->query($p_qry);
			$p_pro_row=brp_mysqli_fetch_assoc($p_pro);

			$info['process_priority']	= $x;
			$info['process_id']	= $process_id;
			$info['process_time']	=  $p_pro_row['process_time'];
			$info['process_type']	= $p_pro_row['process_type'];
			$info['process_opening']	= $p_pro_row['process_opening'];

			// if(empty($POST['edit_id']) && empty($arr_process)){			
				$inserestimateid=add_record('tbl_wororder_product_process', $info, $dbcon);
			// }else if(array_search($process_id, array_column($arr_process, 'process_id')) === false){
				
			// 	$inserestimateid=add_record('tbl_wororder_product_process', $info, $dbcon);
			// }else if(array_search($process_id, array_column($arr_process, 'process_id')) !== false){
				
			// 	$update_info['priority'] = $x;
			// 	$update_info['process_status'] = 0;
			// 	$where = "product_id = " . $product_id ." AND process_id=".$process_id;
			// 	$inserestimateid=update_record('tbl_wororder_product_process', $update_info, $where , $dbcon);	
			// 	if($inserestimateid == 0){
			// 		$inserestimateid = 1;
			// 	}
			// }
			$x++;
		}
	
		/*if(!empty($POST['edit_id'])){
	
			foreach ($arr_unsel_process as $process_id) {
				 if(array_search($process_id, array_column($arr_process, 'process_id')) !== false){
					$update_info['process_status'] = 2;
					$where = "product_id = " . $product_id ." AND  process_id=".$process_id;
					$inserestimateid=update_record('tbl_wororder_product_process', $update_info, $where, $dbcon);
					if($inserestimateid == 0){
					$inserestimateid = 1;
				}	
				}

			}
		}*/

		if($inserestimateid){
			if(empty($POST['edit_id'])){
				$arr['msg']="1";
			}else{
				$arr['msg']="update";
			}
		}else{
			$arr['msg']="0";
		}

		echo json_encode($arr);
	}
/*	else if(brp_strtolower($POST['mode']) == "get_product_process_data") {

		
			$q = "select pr_process_id from tbl_wororder_product_process where process_status = 0 AND product_id =" . $POST['product_id'] . " AND bom_version_id = " . $POST['bom_version_id'] . " order by priority ASC";
			
			$res_pro = $dbcon->query($q);
			$arr_process=brp_mysqli_fetch_all($res_pro);
				// echo "<pre>";
				// print_r($arr_process);

			$multiple_value =implode(',', array_column($arr_process, 'pr_process_id'));

			$query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$POST['product_id'];
			// $rel_pro=brp_mysqli_fetch_assoc($dbcon->query($query_pro));

			$rel_pro = $dbcon->query($query_pro);
			$i=1;
			$str='<div class="row m-bot20"><div class="col-md-6 text-center"> <h4>All Process</h4></div>
			<div class="col-md-6 text-center"><h4>Selected Process as priority</h4></div>	</div>
			<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
			    <input type="hidden" name="multiple_value" id="multiple_value" value="'.$multiple_value.'"/>
 				<input type="hidden" name="process_sel_product_id" id="process_sel_product_id" value="'. $POST['product_id'] .'"/>
			<select class="multi-select" multiple=""  name="process_item[]" id="process_item" >';
		while($product_process=brp_mysqli_fetch_assoc($rel_pro)){
			$selected="";
			if(empty($POST['edit_id'])){
				// $selected = "ms-selected";	
			}
			// else if(in_array($product_process['pr_process_id'], $arr_process)){
			else if(array_search($product_process['pr_process_id'], array_column($arr_process, 'pr_process_id')) !== false){
					$selected="ms-selected";
			}

			$str .=  '
                                      <option class="process_row '.$selected.'" data-cid="'.$i.'" value="'.$product_process['pr_process_id'].'">' . $product_process['process_name'] . '</option>';
			
			
		$i++;
	
		}
		$product_process = brp_mysqli_fetch_assoc($rel_pro);
		
		if(brp_mysqli_num_rows($rel_pro) > 0){
			if(isset($POST['direct'])){
				$function = 'direct_bom_process_add('.$POST['product_id'].','.$POST['bom_version_id'].',"direct")'; 					
			}else{
				$function = 'bom_process_add()'; 	
			}
			
			$str.="</select>
					<div class='col-md-12' >
						
					</div>
					<div class='col-md-12' style='margin-top: 15px;'>
						<div class='col-md-4' >
							<center>
								<button type='button' id='process_save' onClick='".$function ."' name='process_save' class='btn btn-success' >Submit</button>
							</center>
						</div>
						
					</div>
					</form>
			";
		}else{
			
			$str = '<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
			    <input type="hidden" name="multiple_value" id="multiple_value" value=""/>
					<div class="col-md-12" style="margin-top: 15px;">
						<h3>NO PROCESS ADDED</h3>
					</div>
					</form>
					
			';
		}
			
			
			echo $str;
		}*/
		else if(brp_strtolower($POST['mode']) == "check_bom_version_by_product") {
		$branch_where="";
		$str='';
		/*if(!empty($POST['branch_id'])){
			$branch_where=" and branch_id=".$POST['branch_id'];
		}*/
		
		$company_where="";
		/*if(!empty($_SESSION['company_id'])){
			$company_where=" and company_id=".$_SESSION['company_id'];
		}*/
		
		$qry="SELECT * from pro_ms_bom_version where bom_version_status = 0 and  product_id=".$POST['product_id']." ".$company_where." ".$branch_where;
		$result=$dbcon->query($qry);
		
		if(brp_mysqli_num_rows($result) > 0)
		{	
			while($row=brp_mysqli_fetch_assoc($result))
			{
				$str .= '<option value="'.$row['bom_version_id'].'">'.$row['version_name'].'</option>';
			}
				$str .= '<option  value="10000">R&D</option>';
				echo $str;
		}
		else
		{
			echo "0";
		}
	}
	else if(brp_strtolower($POST['mode']) == "check_child_product") {
		
		$q="SELECT * from tbl_request_product where job_card_no='".$POST['work_order_id']."'";
		$res=$dbcon->query($q);
		$row = brp_mysqli_fetch_array($res);		
		$qry="SELECT * from tbl_request_product where perent_id=".$row['rp_id'];
		$result=$dbcon->query($qry);
		
		if(brp_mysqli_num_rows($result) > 0)
		{	
			echo "1";
		}
		else
		{
			echo "0";
		}
	}
	else if(brp_strtolower($POST['mode']) == "check_work_order_process") {
		
			$po_req_no = $_POST['po_req_no'];
		    $check_process_query="SELECT * from tbl_wororder_product_process  where rp_id IN (SELECT rp_id FROM `tbl_request_product` WHERE  job_card_no='".$po_req_no."')";
			$check_process_result=$dbcon->query($check_process_query);
			if(brp_mysqli_num_rows($check_process_result)> 0)
			{ 
				echo  "1";
			}
			else 
			{ 
				echo  "0";
			} 																	

		}
		else if(brp_strtolower($POST['mode']) == "check_product_unit") {
		
			$product_id = $_POST['product_id'];			
			$check_process_query="SELECT * from product_mst INNER JOIN unit_mst On unit_mst.unitid = product_mst.product_base_unit WHERE product_mst.product_id = '$product_id'";
			$check_process_result=$dbcon->query($check_process_query);
			if(brp_mysqli_num_rows($check_process_result)> 0)
			{ 
				$row = brp_mysqli_fetch_array($check_process_result);
				echo $row['unit_name'];
			}
			else 
			{ 
				echo  "0";
			} 																	

		}
		else if(brp_strtolower($POST['mode']) == "workorder_permission") {
			
			$q = "select * from tbl_request_product where rp_id = ".$POST['rp_id']; 
			$rs=$dbcon->query($q);	
		
			if(brp_mysqli_num_rows($rs) > 0 )
			{
				$info['rp_id']	= $POST['rp_id'];
				$info['approval_status'] = '1';
				$updateid=update_record("tbl_request_product", $info,"rp_id=".$POST['rp_id'], $dbcon);	
				echo "1";
			}
			else
			{		
				echo "0";	
			}
		}
		 else if(brp_strtolower($POST['mode']) == "add_wo_apprv_hist") {
                $info1['approve_remark']	= $POST['approve_remark'];
                $info1['approve_status']	= $POST['approve_status'];
                $info1['rp_id']             = $POST['rp_id'];
                $info1['user_id']		= $_SESSION['user_id'];
                $info1['company_id']	= $_SESSION['company_id'];
                 

                $insert_id=add_record("tbl_workorder_aprv_log", $info1, $dbcon);

                if($insert_id){
                    $infoso['approval_status'] = $POST['approve_status'];
                    $updateid=update_record('tbl_request_product', $infoso,"rp_id=".$POST['rp_id'] , $dbcon, $branch_id);
                }
                echo TRUE;
            }
            else if(brp_strtolower($POST['mode']) == "load_wo_hist_datatable") {
		$where='';
        if($POST['rp_id']){
            $where.=" and log.rp_id=".$POST['rp_id'];
        }

		$appData = array();
        $i=1;
        $aColumns = array('log.wo_aprv_log_id','log.rp_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.created_at', 'log.user_id');
        $sIndexColumn = "log.wo_aprv_log_id";
        $isWhere = array("log.approve_status IN (0,1,2) ".$where." ");
        $sTable = "tbl_workorder_aprv_log as log";
        $isJOIN = array('left join users as usr on usr.user_id=log.user_id');
        $hOrder = "log.wo_aprv_log_id desc";
        include('../../include/pagging.php');
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['user_name'];
			
			if($row['approve_status']=='1'){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
			}
			else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
			}
			
			$row_data[] = nl2br($row['approve_remark']);
			$row_data[] = date("d-M-Y h:i A",strtotime($row['created_at']));
			
			$appData[] = $row_data;
			$id++;
			//print_r($row_data);
		}
		$output['aaData'] = $appData;
		//print_r($output);
		echo json_encode( $output );
	}
	 else if(brp_strtolower($POST['mode']) == "get_requested_proudct_details") {
	 		$rp_id = $_POST['rp_id'];
	 		$parent_query="select i.rp_req_qty,pro.product_name,i.rp_pid from tbl_request_product as i
			left join product_mst as pro on pro.product_id=i.rp_pid where i.rp_id=".$rp_id;
			$parent_result=$dbcon->query($parent_query);
			$row = brp_mysqli_fetch_array($parent_result);
			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode']) == "load_productdata") {
		
		$pid=$POST['eid'];
		
		$sel=$dbcon->query("select m.*,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from product_mst as m 
		left join unit_mst as bunit on bunit.unitid=m.product_base_unit
		left join unit_mst as cunit on cunit.unitid=m.product_conv_unit
		left join mst_material_spec as s on m.product_specification=s.ms_id where product_id='$pid'"); // s.m_type_density,
		$row=brp_mysqli_fetch_assoc($sel);
		
		$sel1=$dbcon->query("select bom_id from tbl_bom as m where bom_product='$pid'");
		$row1=brp_mysqli_fetch_assoc($sel1);
		$row['bom_id']=$row1['bom_id'];

		/*
			Code By Umair: 31-05-2021
			Comment : Below Code is use for product specification dynamically
			START
		*/
		$html = '';	
		if($row['product_specification']!='' && $row['product_specification']!='0'){
			$param_sql = "select * from tbl_material_parameter where material_parameter_status = 0 and company_id='".$_SESSION['company_id']."' ";
			$rs_parameter=$dbcon->query($param_sql);	
			while($rel_param=brp_mysqli_fetch_assoc($rs_parameter)){
				$parameter_name = ucfirst(brp_strtolower($rel_param['material_parameter_name']));	
                $parameter_id = 'product_'.$rel_param['material_parameter_id'];	

				$material_parameter_id = $rel_param['material_parameter_id'];

				$param_trn_sql = "select * from mst_material_spec_trn where material_parameter_id = '".$material_parameter_id."' and ms_id='".$row['product_specification']."' ";
				$rs_exec=$dbcon->query($param_trn_sql);	
				$rel_data=brp_mysqli_fetch_assoc($rs_exec);
				if($rel_data['material_parameter_value']){
					$html .= $parameter_name. ' : <input type="text" class="form-control get_ms_kg" name="'.$parameter_id.'" id="'.$parameter_id.'" value="'.$rel_data['material_parameter_value'].'" data-parameter="'.$material_parameter_id.'" data-msid="'.$row['product_specification'].'"  onkeyup="get_ms_kg();" />';
				}
			}
			if($html!=''){
				$html .= '<input type="hidden" name="msid" id="msid" value="'.$row['product_specification'].'">';
				$html .= '<input type="text" class="form-control" name="product_kg" id="product_kg" value="" readonly /> 
							<input type="checkbox" name="set_kg" id="set_kg" value="0" onclick="set_kg_to_qty(this.value)" />SET'; 
			}
		}
		$row['product_specification_code']=$html;
		/* END */

		echo json_encode($row);
	}
	else if(brp_strtolower($POST['mode']) == "get_product_specification_cal") {
			$query="select * from mst_material_spec as trn where trn.ms_id=".$POST['msid'];
			$result1=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result1);

			$formula = $row['formula']; 
			$parameter_value = $POST['values'];

			$material_calculation = 0;
			$material_parameter_value = 0;
			foreach ($parameter_value as $key => $val){
				$material_parameter_id = str_replace('PRODUCT_', '', $val['name']);
				$material_parameter_value = floatval($val['value']);

				$p_query="select * from tbl_material_parameter as mp where mp.material_parameter_id=".$material_parameter_id;
				$p_result1=$dbcon->query($p_query);
				$p_row=brp_mysqli_fetch_assoc($p_result1);

				$material_parameter_code = $p_row['material_parameter_code'];

				$formula = str_replace($material_parameter_code, $material_parameter_value, $formula);
			}
			
			echo $material_calculation = do_maths($formula);

			
		}
		
		
		
		
		
		/* END JAYESH */
		function do_maths($expression) {
   eval('$o = ' . preg_replace('/[^0-9\+\-\*\/\(\)\.]/', '', $expression) . ';');
   return $o;
}  

function load_po_no($dbcon,$typeid){
	$row=array();
	$query1="select * from tbl_invoicetype where type_id=".$typeid." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
	$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
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
function get_child_tree($dbcon,$rp_id)
{
	
	$q="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_icode,dr.drawing_number,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.image_name,pro.product_id,pro.product_type FROM `tbl_request_product` as rpro
					left join product_mst as pro on pro.product_id=rpro.rp_pid
					left join tbl_category as tc on pro.product_category=tc.cat_id
					left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
					left join unit_mst as bunit on bunit.unitid=rpro.process_unit
					left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
					WHERE rpro.perent_id = '$rp_id' GROUP BY rpro.rp_id"; 
	
	$res = $dbcon->query($q);
	if(brp_mysqli_num_rows($res) > 0)
	{
	
	while($rel=brp_mysqli_fetch_assoc($res))
	{ 
	
	
//	echo "<pre>"; print_r($rel); die;
		if($rel['status']==3){
						if($rel['bom_id'] == 0)
						{
							/* JAYESH */
							if($rel['approval_status'] == 1){
							
							if($_POST['main_mode'] == "wo_permission"){
							$req_btn_action = '';

							$req_btn_text = 'Granted';
											
							}
							else if($rel['approval_status'] == 2){
								$req_btn_text = 'Rejected';
							}
							else{
								$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].');"';
							
								$req_btn_text = 'Request';
							}	
						}
						if($_POST['main_mode'] == "wo_permission"){
								$req_btn_action = 'onclick="workorder_permission('.$rel["rp_id"].');"';
									if($rel['approval_status'] == 1){
												$req_btn_text = 'Granted permission';
											}
											else if($rel['approval_status'] == 2){
												$req_btn_text = 'Rejected permission';
											}
											else
											{
												$req_btn_text = 'Pending permission';
											}
							}else{
										
										//$req_btn_text = 'Pending';
										if($rel['approval_status'] == 1){
											$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].');"';					
											$req_btn_text = 'Request';
										}
										else if($rel['approval_status'] == 2){
											$req_btn_text = 'Rejected Request';
											$req_btn_action= '';
										}
										else
										{
											/*$req_btn_action = 'onclick="pending_approval();"';
											$req_btn_text = 'Pending Request';*/
											$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].');"';					
											$req_btn_text = 'Request';
										}
							}	
							
								$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" '.$req_btn_action.' ><i class="fa fa-paper-plane"></i> '.$req_btn_text.'</a>';
					}
					else {
						
						$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].');" ><i class="fa fa-paper-plane"></i> Request</a>';
							/* JAYESH */
						}
						
						
					}else{
						$request_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					}
				$bom2="SELECT status,main_request,rp_req_qty,in_process_qty FROM `tbl_request_product` WHERE status!=2 AND rp_id=".$rp_id;
				
				
				/*if($rel['status']==3){
						/*if($rel['approval_status'] == 1){
							
							if($_POST['main_mode'] == "wo_permission"){
							$req_btn_action = '';

							$req_btn_text = 'Granted';
											
							} else if($rel['approval_status'] == 2){
								$req_btn_text = 'Rejected';
							}
							else{
								$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].');"';
							
								$req_btn_text = 'Request';
							}	
							
							
								$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" '.$req_btn_action.' ><i class="fa fa-paper-plane"></i> '.$req_btn_text.'</a>';
						}
						else
						{
							if($_POST['main_mode'] == "wo_permission"){
								$req_btn_action = 'onclick="workorder_permission('.$rel["rp_id"].');"';
									if($rel['approval_status'] == 1){
												$req_btn_text = 'Granted';
											}
											else if($rel['approval_status'] == 2){
												$req_btn_text = 'Rejected';
											}
											else
											{
												$req_btn_text = 'Pending';
											}
							}else{
								$req_btn_action = 'onclick="pending_approval();"';
								$req_btn_text = 'Pending';
							}	
													
								$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].');" ><i class="fa fa-paper-plane"></i> Request  </a>';
						//}
					
					}else{
						$request_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					}*/
			//$bom2="SELECT status,main_request,rp_req_qty,in_process_qty,approval_status FROM `tbl_request_product` WHERE status!=2 AND perent_id=".$rel['perent_id']." AND rp_id =".$rel['rp_id'];
				$bom_rel2=brp_mysqli_fetch_assoc($dbcon->query($bom2));
				//$bom_rel2=brp_mysqli_fetch_assoc($dbcon->query($bom2));
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
						$req_qty=$rel['in_process_qty']*$rel["req_qty_one"];
						//$req_qty=$bom_rel2['in_process_qty']*$rel["req_qty_one"];
					}else{
						//$req_qty=$rel['rp_req_qty']*$rel["req_qty_one"];
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

				
									$check_process_query="SELECT * from tbl_wororder_product_process where  rp_id = ".$rel["rp_id"]." AND process_id != '0'";
									$check_process_result=$dbcon->query($check_process_query);
									if(brp_mysqli_num_rows($check_process_result)> 0)
									{ 
										
									$parent_delete_flag = 1;
									
									if($rel['status']==3){
										$sub_product_button='<a class="btn btn-success btn-xs" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_sub_product('.$rel["rp_id"].','.$rel["product_id"].','.$POST['eid'].');" ><i class="fa fa-plus"></i></a>';
											}
											else{
												$sub_product_button='';
											}
										
									}else{
										
										//$process_button='';
										$sub_product_button='';
										$parent_delete_flag = 0;
									} 
									
									$rp_id = $rel["rp_id"];
									$parent_sr_res=$dbcon->query("select * from  tbl_request_product where perent_id = '$rp_id'");
										if(brp_mysqli_num_rows($parent_sr_res)>0)
										{
											$child_flag = 1;
										}
										else{
											$child_flag = 0;
										}	
									
									
									/*	$sub_product_button='<a class="btn btn-success" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_sub_product('.$rel["rp_id"].','.$rel["rp_pid"].','.$POST['eid'].');" ><i class="fa fa-plus"></i></a>';*/
									
								/*	if($rel['status']==3){*/
								
									$check_process_allocate_query="SELECT * from tbl_allocate_process where product_id=".$rel["product_id"]." AND p_status = '1'";
									$check_process_allocate_result=$dbcon->query($check_process_allocate_query);
									if(brp_mysqli_num_rows($check_process_allocate_result) < 1)
									{ 
										
										$process_button='<a class="btn btn-success btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="direct_show_product_process('.$rel['rp_pid'].','.$rel['rp_id'].');" ><!--<i class="fa fa-paper-plane"></i>--> Process</a>';
			
									
									$edit_button='<a class="btn btn-primary btn-xs" data-original-title="" id="del_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="edit_work_order_product('.$rel["rp_pid"].','.$rel["rp_id"].','.$rel["rp_pid"].','.$rel["rp_req_qty"].');" ><i class="fa fa-pencil-square-o"></i></a>';
									
									$del_button='<a class="btn btn-danger btn-xs" data-original-title="" id="del_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="delete_work_order_product('.$rel["rp_pid"].','.$rel["rp_id"].','.$child_flag.','.$rel["sp_id"].');" ><i class="fa fa-remove"></i></a>';
									}
									else
									{
										$process_button='';
										$del_button='';
										$edit_button='';
									}
									
			/* END JAYESH */
			
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
			
				
			
			/* start jayesh for checking product wise process required or not */
			$action = '';
			$check_pr_type_process = check_process_product_type($dbcon,$rel['product_type']);
			if($check_pr_type_process == 1)
			{
				$action = $process_button.'	'.$sub_product_button.' '.$edit_button.' '.$del_button;
			}
			else
			{
				if($rel['status']!= '3')
				{
					$action = '';
				}
				else
				{
					$action = $edit_button.' '.$del_button;
				}
				
			}
			
			/* start jayesh for checking product wise process required or not */
			
$companyConfiguration = getCompanyConfiguration($dbcon);
$production_pro_search = $companyConfiguration['production_pro_search'];
$pro_search=explode(",", $production_pro_search);
			$drawing_number = "";
								$item_code = "";
								 if(in_array('drawing',$pro_search)){
							            $drawing_number = " </br> -- (".$rel['drawing_number'].")";
							        }
							        if(in_array('item',$pro_search)){
							            $item_code = "</br> -- (".$rel['product_icode'].")";
							        }
			
				echo '<tr>
						<td>'.$rel["sr_no"].'</td>
						<td>'.$rel["product_name"].$item_code.$drawing_number.'</td>
						<td>'.$image_name1.'</td>
						<td>'.$cat_name.'</td>
						<td>'.$rel["product_min_stock"].'</td>
						<td>
							<input type="number" min="0" class="form-control numbersOnly" name="current_stock'.$rel["rp_id"].'" id="current_stock'.$rel["rp_id"].'" onkeydown="return numericonly(event)"  value="'.$actualstock.'" readonly />
						</td>
						<td>
							<div class="col-md-9" >
								<input type="number" min="0" class="form-control numbersOnly" name="req_qty'.$rel["rp_id"].'" id="req_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$req_qty_sh.')"  value="'.$req_qty.'"  '.$req_read_only.' />
								
								<input type="hidden" name="req_qty_one'.$rel["rp_id"].'" id="req_qty_one'.$rel["rp_id"].'" value="'.$rel["req_qty_one"].'" />
								
								<input type="hidden" name="basic_req_qty'.$rel["rp_id"].'" id="basic_req_qty'.$rel["rp_id"].'" value="'.$req_qty.'" />
								
								<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["conv_unit_name"].'</strong>
							</div>
						</td>
						<td>
							<input type="number" min="0" class="form-control numbersOnly" name="res_qty'.$rel["rp_id"].'" id="res_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$res_qty_sh.')" value="'.$rel["reserve_stock"].'" '.$reserv_read_only.' />
							<span style="display:none;" class="error" id="res_qty_err'.$rel["rp_id"].'" ></span>
						</td>
						<td>
							<div class="col-md-9">
								<input type="number" min="0" class="form-control numbersOnly" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="error_check('.$rel["rp_id"].','.$process_qty_sh.')" onkeydown="return numericonly(event)"  value="'.$process_qty.'" '.$process_read_only.' />
								
								<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["base_unit_name"].'</strong>
							</div>
						</td>
						<td>
							<div class="col-md-9" >
								<input type="number" min="0" class="form-control numbersOnly" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$po_qty_sh.')"  value="'.$po_qty.'" '.$po_read_only.' />
								
								<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
							</div>
							<div class="col-md-2">
								<strong>'.$rel["conv_unit_name"].'</strong>
							</div>
						</td>
						<td class="action'.$rel["rp_id"].'">'.$request_button.'
						<br>'.$action.'</td>
					</tr>';
					
				//$rp_id = $rel['rp_id'];
				$child_query = "select * from tbl_request_product where perent_id = ".$rel['rp_id'];
				$child_result=$dbcon->query($child_query);
				
				if(brp_mysqli_num_rows($child_result)>0)
				{
						$child_rel=brp_mysqli_fetch_assoc($child_result);
						get_child_tree($dbcon,$child_rel['perent_id']);
					
				}
		}
	
	}
}
function delete_recurring_products($dbcon,$rp_id)
{
	
	$q="select * from tbl_request_product where perent_id = '$rp_id'";	
	$res = $dbcon->query($q);
	if(brp_mysqli_num_rows($res) > 0)
	{
		$data = array();
		while($rel=brp_mysqli_fetch_assoc($res))
		{ 
			$data[]= $rel['rp_id'];
			$child_rp_id = $rel['rp_id'];
			$child_query = "select * from tbl_request_product where perent_id = '$child_rp_id'";
			$child_result=$dbcon->query($child_query);					
			if(brp_mysqli_num_rows($child_result)>0)
			{
				while($child_rel=brp_mysqli_fetch_assoc($child_result)){

					$data = array_merge($data,delete_recurring_products($dbcon,$child_rel['perent_id']));

				}
			}	
		}
		
		return $data;	
	}
}
function update_srno_recurring_products($dbcon,$sp_id,$rp_id='')
{
	if($rp_id == '')
	{
		$q1="select * from tbl_request_product where sp_id= '$sp_id' AND main_request = '1'";	
		$res1 = $dbcon->query($q1);
		$rel1=brp_mysqli_fetch_assoc($res1);
		$rp_ids = $rel1['rp_id'];
		$q="select * from tbl_request_product where perent_id= '$rp_ids'"; 	
	}
	else
	{
		$q="select * from tbl_request_product where perent_id= '$rp_id'";	 
	}
	
	$res = $dbcon->query($q);
	if(brp_mysqli_num_rows($res) > 0)
	{
		$data = array();	
		$i = 1;
		
		while($rel=brp_mysqli_fetch_assoc($res))
		{ 
			$child_rp_id = $rel['rp_id'];
			if($rp_id != '')
			{
				$q1="select * from tbl_request_product where rp_id= '$rp_id'";
				$res1 = $dbcon->query($q1);
				$rel1=brp_mysqli_fetch_assoc($res1);
				$counter = $rel1['sr_no'].'.'.$i;
				$child_update_query = "update tbl_request_product set sr_no='$counter' where rp_id = '$child_rp_id'";
				$child_update_result=$dbcon->query($child_update_query);
			}
			else
			{
				$child_update_query = "update tbl_request_product set sr_no= '$i' where rp_id = '$child_rp_id'";
				$child_update_result=$dbcon->query($child_update_query);
			}
			
			
			$child_query = "select * from  tbl_request_product where perent_id = '$child_rp_id'";
			$child_result=$dbcon->query($child_query);							
			if(brp_mysqli_num_rows($child_result)>0)
			{
					update_srno_recurring_products($dbcon,$sp_id,$child_rp_id);
				
			}	
			$i++;
		}
	
		//return $data;	
	}
}

function bom_child_tree($dbcon,$bom_id,$sp_id,$rp_parent_id,$num,$qty)
		{   
						$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
							left join product_mst as pro on pro.product_id=bom_trn.product_id
							left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
							left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
							where bom_trn_status=0 and bom_id=".$bom_id;	
							$result1=$dbcon->query($query1);
			
							$k=1;
							$call=1;$space="";
					if(brp_mysqli_num_rows($result1) > 0)
					{
						
						
					while($rel1=brp_mysqli_fetch_assoc($result1)){ 
					
					//echo "<pre>"; print_r($rel1); die;
						$sr_no = $num.'.'.$k;
					
						$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
						$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

						$base_qty=$base_one_qty*$info_su['rp_req_qty'];
						$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

						$info_sub['sp_id']				= $sp_id;
						$info_sub['sr_no']				= $sr_no;
						$info_sub['rp_pid']				= $rel1['product_id'];
						$info_sub['rp_req_qty']			=  $qty;
						$info_sub['rp_req_date']		=  date("Y-m-d");
						
						//$info_sub['rp_req_qty']			= $conv_stock;//required qty
						$info_sub['req_qty_one']		= $conv_one_qty;//required qty
						$info_sub['rp_po_qty']			= "";//po qty
						$info_sub['in_process_qty']		= "";//process qty
						$info_sub['rp_req_type']		= "job_card";//type
						$info_sub['process_unit']		= $rel1['product_base_unit'];
						$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub['perent_id']			= $rp_parent_id;
						$info_sub['status']				= 3;
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						//$info_sub['main_request']		= $POST['g_total'];
						
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
					
					$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
					
					$query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.status = 0 and  prod.product_id = ".$rel1['product_id']; 
		
			$rel_pro = $dbcon->query($query_pro);
			
			if(brp_mysqli_num_rows($rel_pro)>0)
			{
				while($product_process=brp_mysqli_fetch_assoc($rel_pro))
				{
					$wpp_info['product_id'] = $rel1['product_id'];		
					$wpp_info['rp_id'] = 	$inserid_sub;
					$wpp_info['process_priority'] = 	$product_process['process_priority'];
					$wpp_info['process_time'] = 	$product_process['process_time'];
					$wpp_info['process_type'] = 	$product_process['process_type'];
					$wpp_info['process_opening'] = 	$product_process['process_opening'];
					$wpp_info['process_id'] = 	$product_process['process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $POST['branch_id'];
					
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
				}
			}
			
			
					
					/*   Material Formula */
						$material_query="select * from tbl_bom_material_trn where bom_trn_id=".$rel1['bom_trn_id']." AND bom_id =".$rel1['bom_id']; 	
						$material_result=$dbcon->query($material_query);
						if(brp_mysqli_num_rows($material_result) > 0)
						{
							while($mat_rel=brp_mysqli_fetch_assoc($material_result))
							{ 
								$mat_data['sp_id'] = $inserid; 
								$mat_data['rp_id'] = $inserid_sub; 
								$mat_data['product_id'] = $rel1['product_id']; 
								$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id']; 
								$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value']; 
								$mat_data['jobcard_material_trn_status'] = 0; 
								$mat_data['user_id']			= $_SESSION['user_id'];
								$mat_data['company_id']			= $_SESSION['company_id'];
								$mat_data['branch_id']			= $_SESSION['branch_id'];
								$inserid_sub=add_record('tbl_jobcard_material_trn', $mat_data, $dbcon,$POST['branch_id']);
								
							}
						}
						
						/*   Material Formula */			
			
			//echo $sr_no;
			//echo $rel1['p_bom_id'];
			bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$sr_no,$qty);
			$k++;	
				
		}
	}
		
}
?>