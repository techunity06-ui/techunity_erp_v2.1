<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
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
	include($include.'pagging.php');
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

	$wo_type = $POST['wo_type'];
	$rp_id = $POST['rp_id'];
	
	if(brp_strtolower($wo_type) == "direct_jobcard"){
		$query="select * from tbl_request_product as i
		where i.rp_id=".$rp_id;
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);
	}else{
		//$chk=check_requested($dbcon,$POST['product_id'],$POST['po_req_no']);
		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];

		$bom_q1="SELECT rp_id FROM `tbl_request_product` WHERE sp_id='".$work_order_id."'";
		$bom_rel_q1=brp_mysqli_fetch_assoc($dbcon->query($bom_q1));

		$query="select * from tbl_request_product as i
		where i.rp_id=".$bom_rel_q1['rp_id'];
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);
	}

		
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
								$process=get_product_process($dbcon,$POST['eid'],$product_id);

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

	$set_pro="SELECT product_base_unit,product_conv_unit,product_base_qty,product_conv_qty,product_id FROM `product_mst` WHERE product_status=0 AND product_id='".$POST['eid']."'";
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

		
			if($setpro_rel['product_conv_unit']==$info_su['purchase_unit']){
				$type="base_unit";
				$con_stock=$info_su['rp_po_qty'];
				$base_stock=convert_stock_new($dbcon,$info_su['rp_po_qty'],$setpro_rel['product_id'],$type);
			}else{
				$type="conv_unit";
				$base_stock=$info_su['rp_po_qty'];
				$con_stock=convert_stock_new($dbcon,$info_su['rp_po_qty'],$setpro_rel['product_id'],$type);
			}

			$info_wip_add['rp_id']					= $inserid_sub1;
			$info_wip_add['type_flag']				= 3;
			$info_wip_add['po_trn_id']				= 0;
			$info_wip_add['sales_order_trn_id']		= 0;
			//$info_wip_add['allocate_for_rp_id']		= 0;
			//$info_wip_add['allocate_table_id']		= $POST['sales_order_trn_id'];
			$info_wip_add['allocate_base_qty']		= $base_stock;
			$info_wip_add['allocate_base_unit']		= $setpro_rel['product_base_unit'];
			$info_wip_add['allocate_conv_qty']		= $con_stock;
			$info_wip_add['allocate_conv_unit']		= $setpro_rel['product_conv_unit'];
			$info_wip_add['stock_flag']				= 1;
			$info_wip_add['cdate']					= date("Y-m-d H:i:s");
			$info_wip_add['user_id']				= $_SESSION['user_id'];
			$info_wip_add['company_id']				= $_SESSION['company_id'];

			$inser_wip_add=add_record('wip_stock_allocate', $info_wip_add, $dbcon,$POST['branch_id']);
		if(!empty($POST['sales_order_trn_id'])){
			
			$info_wip_deduct['rp_id']					= $inserid_sub1;
			$info_wip_deduct['type_flag']				= 3;
			$info_wip_deduct['po_trn_id']				= 0;
			$info_wip_deduct['sales_order_trn_id']		= $POST['sales_order_trn_id'];
			$info_wip_deduct['allocate_for_rp_id']		= $inserid_sub1;
			$info_wip_deduct['perent_id']				= $inser_wip_add;
			$info_wip_deduct['allocate_base_qty']		= $base_stock;
			$info_wip_deduct['allocate_base_unit']		= $setpro_rel['product_base_unit'];
			$info_wip_deduct['allocate_conv_qty']		= $con_stock;
			$info_wip_deduct['allocate_conv_unit']		= $setpro_rel['product_conv_unit'];
			$info_wip_deduct['stock_flag']				= 2;
			$info_wip_deduct['cdate']					= date("Y-m-d H:i:s");
			$info_wip_deduct['user_id']					= $_SESSION['user_id'];
			$info_wip_deduct['company_id']				= $_SESSION['company_id'];

			$inser_wip_deduct=add_record('wip_stock_allocate', $info_wip_deduct, $dbcon,$POST['branch_id']);
		
		$set_pro_w="SELECT allocate_base_qty_used,allocate_conv_qty_used FROM `wip_stock_allocate` WHERE wip_stock_allocate_id='".$info_wip_deduct['perent_id']."'";
		$setpro_rel_w=brp_mysqli_fetch_assoc($dbcon->query($set_pro_w));

			$bsto=$setpro_rel_w['allocate_base_qty_used']+$info_wip_deduct['allocate_base_qty'];
			$csto=$setpro_rel_w['allocate_conv_qty_used']+$info_wip_deduct['allocate_conv_qty'];
		
			$query_invoicetype1=$dbcon->query("UPDATE wip_stock_allocate SET allocate_base_qty_used =".$bsto.",allocate_conv_qty_used=".$csto." WHERE wip_stock_allocate_id =".$info_wip_deduct['perent_id']);

		}
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);

		if($inserid_sub1){
			echo 1;
		}else{
			echo 2;
		}
	}
	else if(brp_strtolower($POST['mode']) == "edit") {
		$info_wo['customer_req_material'] = $POST['customer_req_material'];
		$info_wo['customer_req_grade'] = $POST['customer_req_grade'];
		$info_wo['customer_req_size'] = $POST['customer_req_size'];
		$info_wo['customer_req_id'] = $POST['customer_req_id'];
		$info_wo['customer_req_length'] = $POST['customer_req_length'];
		$info_wo['customer_req_heat'] = $POST['customer_req_heat'];
		$info_wo['customer_req_coc'] = $POST['customer_req_coc'];
		$info_wo['customer_ref_no'] = $POST['customer_ref_no'];
		$info_wo['customer_asset_serial'] = $POST['customer_asset_serial'];
		$info_wo['customer_bevel_spec'] = $POST['customer_bevel_spec'];
		$info_wo['remark'] = $POST['remark'];

		$sp_id = $POST['work_order_id'];

		// $info_wo['sp_status']=2;
		$info_wo['bom_costing_id']= $POST['bom_costing_id'];
		// $updateid12=update_record("tbl_set_main_process", $info_wo,"sp_id=".$sp_id , $dbcon);

		//$info_wo['sp_status']=2;
		// $info_wo['po_req_date'] = $_POST['po_date'];
		$work_order_id = $_POST['work_order_id'];
		$update=update_record("tbl_set_main_process", $info_wo,"sp_id=".$work_order_id , $dbcon);
		echo 3;
	}
	else if(brp_strtolower($POST['mode']) == "add") {
		
		$sp_id = $POST['work_order_id'];

		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];

		// $info_wo['sp_status']=2;
		$info_wo['bom_costing_id']= $POST['bom_costing_id'];
		$updateid12=update_record("tbl_set_main_process", $info_wo,"sp_id=".$sp_id , $dbcon);

		// $customer_req['bom_costing_id'] = $POST['bom_costing_id'];

		$customer_req['customer_req_material'] = $POST['customer_req_material'];
		$customer_req['customer_req_grade'] = $POST['customer_req_grade'];
		$customer_req['customer_req_size'] = $POST['customer_req_size'];
		$customer_req['customer_req_id'] = $POST['customer_req_id'];
		$customer_req['customer_req_length'] = $POST['customer_req_length'];
		$customer_req['customer_req_heat'] = $POST['customer_req_heat'];
		$customer_req['customer_req_coc'] = $POST['customer_req_coc'];
		$customer_req['customer_ref_no'] = $POST['customer_ref_no'];
		$customer_req['customer_asset_serial'] = $POST['customer_asset_serial'];
		$customer_req['customer_bevel_spec'] = $POST['customer_bevel_spec'];

		$update_id=update_record("tbl_request_product", $customer_req,"main_request = 1 and sp_id=".$sp_id , $dbcon);
		$customer_req['remark'] = $POST['remark'];		
		$updateid12=update_record("tbl_set_main_process", $customer_req,"sp_id=".$work_order_id , $dbcon);
		
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
				left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and  mst.user_id=".$_SESSION['user_id']." and mst.product_id='0' ";
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
		
		/*else if(brp_strtolower($POST['mode']) == "load_tempoutward") {
			
			
		}*/
		else if(brp_strtolower($POST['mode']) == "get_tree_request_new") {
			
				$wo_type = $_POST['wo_type'];
				$rp_id = $_POST['rp_id'];
				$extra_stock = $_POST['extra_stock'];
				$ext_stock_vendor_id = $_POST['ext_stock_vendor_id'];
				$jobwork_type = $POST['jobwork_type'];

				// $eid = isset($_POST['eid']) ? (int) $_POST['eid'] : 0;
				// $sp_id = isset($_POST['sp_id']) ? (int) $_POST['sp_id'] : 0;
				$sales_order_trn_id = isset($_POST['sales_order_trn_id']) ? (int) $_POST['sales_order_trn_id'] : null;
				
			if(empty($sales_order_trn_id)){
				
				if(!empty($_POST['bom_version_id']))
				{
					$bom="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']."  and company_id=".$_SESSION['company_id']." and sp_id='".$POST['sp_id']."'";
				}
				else
				{
					$bom="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and user_id=".$_SESSION['user_id'];
				}
			}else{
				
				
				$bom="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$POST['eid']." and sales_order_trn_id=".$POST['sales_order_trn_id']." and company_id=".$_SESSION['company_id'];
				
			}
			
			//echo $bom;
			
			$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom));
			

			if(brp_strtolower($wo_type) == "direct_jobcard"){
					$pq = "select * from tbl_request_product where status !=2 and rp_id=".$_POST['rp_id'];
				}else{
					$pq = "select * from tbl_request_product where status !=2 and sp_id=".$_POST['sp_id']." AND main_request = '1'";		
				}
			
			$pq_res = $dbcon->query($pq);
			$pq_row=brp_mysqli_fetch_assoc($pq_res);			
			//$where = '';			
		$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_icode,dr.drawing_number,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.image_name,pro.product_id,pro.product_type,pro.product_base_qty,pro.product_conv_qty, ptm.product_type_name,pro.reorder_qty, bom.bom_version_id, pro.product_base_unit,pro.product_conv_unit FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			left join pro_ms_product_type as ptm on ptm.product_type_id=pro.product_type
			left join tbl_bom as bom on bom.bom_id=rpro.bom_id

			WHERE main_request=0 and rpro.status in (0,3) AND rpro.perent_id=".$pq_row['rp_id']; 

			// echo $bom1;		

			$result=$dbcon->query($bom1); 
			
			while($rel=brp_mysqli_fetch_assoc($result)){

				$btn_document = '<button type="button" id="btn_bom_doc" onclick="view_documents('.$rel['bom_id'].','.$rel['bom_version_id'].');" class="btn btn-info btn-xs" >View Documents</button>';
				$btn_remark = '<button type="button" id="btn_product_remark" onclick="show_product_remark_modal('.$rel['rp_id'].','.$rel['status'].');" class="btn btn-info btn-xs" >Add Remark</button>';

				$customer_id = "";
				if($jobwork_type == '1'){
					$customer_id = $rel['customer_id'];
				}
				
				/* check product lead time and process */

				$lead_n_process = check_product_lead_time_and_process($dbcon,$rel["product_id"]); 
				$bclolr = '';
				if($lead_n_process == 0)
				{
				$bclolr = 'style="background-color:#FFFFA7;"';
				}
				$lead_n_process = 1;
				/* */	
				$unrequest_button = "";
				$req_btn_read_only = "";
				if($extra_stock == '1'){
					$req_btn_read_only = "style='display:none'";
				}

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
								$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');"';

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
									$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');"';										
									$req_btn_text = 'Request';
								}
								else if($rel['approval_status'] == 2){
									$req_btn_text = 'Rejected Request';
									$req_btn_action= '';
								}
								else
								{
									$req_btn_action = 'onclick="pending_approval();"';
									$req_btn_text = 'Pending Request';
								}
							}	
						}

						$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" '.$req_btn_action.'  '.$req_btn_read_only.'><i class="fa fa-paper-plane"></i> '.$req_btn_text.'</a>';
					}
					else {
						
						$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');"  '.$req_btn_read_only.' ><i class="fa fa-paper-plane"></i> Request</a>';
						/* JAYESH */
					}


				}else{
					$request_button='<a class="btn btn-success dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-check-circle"></i>  Requested</a>';

					$is_child_requested = check_child_is_requested($dbcon,$rel['rp_id']);

					if($is_child_requested > 0){
						$unrequest_button = "";
					}else{
						$unrequest_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" id="unreqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" style="margin: 5px;" onclick="unrequest_product('.$rel["rp_id"].');" ><i class="fa fa-close"></i> Unrequest</a>';
					}
				}
		/*	$bom2="SELECT status,main_request,rp_req_qty,in_process_qty,approval_status FROM `tbl_request_product` WHERE status!=2 AND perent_id=".$rel['perent_id']." AND rp_id =".$rel['rp_id'];
		$bom_rel2=brp_mysqli_fetch_assoc($dbcon->query($bom2));*/

		$bom2="SELECT status,main_request,rp_req_qty,in_process_qty FROM `tbl_request_product` WHERE status!=2 AND rp_id=".$rel['perent_id'];
		$bom_rel2=brp_mysqli_fetch_assoc($dbcon->query($bom2));

				/*if($bom_rel2['main_request']!="1"){
					if($bom_rel2['status']=="3"){
						$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].');" ><i class="fa fa-paper-plane"></i> Request</a>';
					}else{
						$request_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>';
					}
				}*/		
				// $actualstock = 0;
			if($extra_stock == '1'){
				$actualstock=get_extra_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$ext_stock_vendor_id);
				$base_actualstock=get_extra_stock($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$ext_stock_vendor_id);

			}else{
				$cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$customer_id);
				$base_cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$customer_id);
				$rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],"","","","",$rel["branch_id"],"","","","",$customer_id);
				$base_rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["process_unit"],"","","","",$rel["branch_id"],"","","","",$customer_id);
				$wipstock=wipstock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$customer_id);
				$base_wipstock=wipstock($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$customer_id);
				$actualstock=$cstock-$rstock;
				$base_actualstock=$base_cstock-$base_rstock;
				$wip_purchase_stock=wip_purchase_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"]);
				$base_wip_purchase_stock=wip_purchase_stock($dbcon,$rel["rp_pid"],$rel["process_unit"]);
				$wip_purchase_stock=0;
				$base_wip_purchase_stock=0;
				$actualstock=$actualstock+$wipstock+$wip_purchase_stock;
				$base_actualstock=$base_actualstock+$base_wipstock+$base_wip_purchase_stock;


			}
				

				/*var_dump($cstock);
				var_dump($rstock);
				var_dump($wipstock);
				var_dump($wip_purchase_stock);*/

				$query_process_ch="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn
				where trn.rp_id=".$rel["rp_id"];
				
				$result_process_ch=$dbcon->query($query_process_ch);
				$cnt_process_ch=mysqli_num_rows($result_process_ch);
				
				if($cnt_process_ch>0){
					$ac_process_sto=process_stock_for_mrp($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["rp_id"],$rel["branch_id"]);
					$base_ac_process_sto=process_stock_for_mrp($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["rp_id"],$rel["branch_id"]);
					// var_dump($ac_process_sto);

					/*var_dump($ac_process_sto);
					var_dump($base_ac_process_sto);*/
					$actualstock=$actualstock+$ac_process_sto;
					$base_actualstock=$base_actualstock+$base_ac_process_sto;
				}
				


				$req_qty = "";
				if($rel["status"]==0){
					$reserv_read_only="readonly";
					$reserv_conv_read_only="readonly";
					$po_read_only="readonly";
					$po_conv_read_only="readonly";
					$process_read_only="readonly";
					$req_read_only="readonly";
					$req_qty=$rel['rp_req_qty'];
				}else{
					$reserv_read_only="readonly";
					$reserv_conv_read_only="";
					$po_read_only="";
					$po_conv_read_only="";
					$process_read_only="";
					$req_read_only="";

					//if($bom_rel2['in_process_qty']!=0){
						//$req_qty=$bom_rel2['in_process_qty']*$rel["req_qty_one"];
					//}else{
						//$req_qty=$bom_rel2['rp_req_qty']*$rel["req_qty_one"];
					//}

					if($bom_rel2['status']=="3"){
						$req_qty=$bom_rel2['rp_req_qty']*$rel["req_qty_one"];
					}else{
						$req_qty=$bom_rel2['in_process_qty']*$rel["req_qty_one"];
					}
					$req_qty=round($req_qty,4);
				}

				$reorder_qty = 0;
				$reorder_conv_qty = 0;

				/*if(!empty($rel['reorder_qty']) && $rel['reorder_qty'] > 0){
					$reorder_qty = $rel['reorder_qty'];		
					$reorder_conv_qty = convert_stock($dbcon,$reorder_qty,$rel['rp_pid'],"conv_unit");		

				   $chk_qty = 	ceil($req_qty  / $reorder_qty);
				   $req_qty = 	$reorder_qty * $chk_qty;
				}*/

				$process_qty = "";
				$po_qty = "";
				$po_base_qty = "";
				$pr_setting_arr=explode(",",$rel['product_setting_check']);
				$having_child = check_having_child_product($dbcon,$rel['rp_id']);
			
				if($rel["status"]!=0){	
					
					// $process_qty = "";
					if($having_child)
					// if(in_array("process_product",$pr_setting_arr))
					{
						$process_read_only="";
							$process_conv_read_only="";
						$process_qty=$req_qty;
						$po_qty="";
						$basedisplay="display:block";
						$convdisplay="display:block";
						$basereadonly="";
						$convreadonly="";
						
						$request_unit_type="base_unit";
						$req_unit_id=$rel['process_unit'];


					}
					else
					{
						$process_read_only="readonly";
						$process_conv_read_only="readonly";
						$process_qty="";
						$po_base_qty=$req_qty;
						$convdisplay="display:block";
						$basedisplay="display:block";
						$basereadonly="";
						$convreadonly="";
						
						$request_unit_type="conv_unit";
						$req_unit_id=$rel['purchase_unit'];
					//pathik production convert unit start
						$po_qty=convert_stock($dbcon,$po_base_qty,$rel['rp_pid'],"conv_unit");
					//pathik production convert unit end
					}
				}else{
					if($having_child)
					// if(in_array("process_product",$pr_setting_arr))
					{
						$process_qty=$rel["in_process_qty"];
						$po_qty="";
						$po_qty="";
					}else
					{
						$process_qty="";
						$po_base_qty=$rel["rp_po_base_qty"];
						$po_qty=$rel["rp_po_qty"];
						// $po_qty=convert_stock($dbcon,$po_base_qty,$rel['rp_pid'],"base_unit");
						// $po_qty=convert_stock($dbcon,$po_base_qty,$rel['rp_pid'],"conv_unit");
					}

					/*$process_qty=$rel["in_process_qty"];
					$po_qty=$rel["rp_po_qty"];
				//pathik production convert unit start
					$po_qty=convert_stock($dbcon,$po_base_qty,$rel['rp_pid'],"base_unit");*/
				//pathik production convert unit end


			
					if(!empty($process_qty)){
						$basedisplay="display:block";
						$convdisplay="display:block";
						$basereadonly="";
						$convreadonly="";
						
						$request_unit_type="base_unit";
						$req_unit_id=$rel['process_unit'];
					}else{
						$convdisplay="display:block";
						$basedisplay="display:block";
						$basereadonly="";
						$convreadonly="";
						
						$request_unit_type="conv_unit";
						$req_unit_id=$rel['purchase_unit'];
					}
				}

				if($extra_stock == '1'){
						$process_read_only="readonly";
						$process_qty=0;
						$process_conv_qty=0;
						$po_qty=0;
						$po_base_qty=0;
						$po_read_only="readonly";
						$po_conv_read_only="readonly";
						
						if($having_child){
							$basedisplay="display:block";
							$convdisplay="display:block";
							$basereadonly="";
							$convreadonly="";
							$reserv_read_only = "";
							// $reserv_conv_read_only = "readonly";
							$request_unit_type="base_unit";
							$req_unit_id=$rel['process_unit'];
						}else{
							$convdisplay="display:block";
							$basedisplay="display:block";
							$basereadonly="";
							$convreadonly="";
							$reserv_read_only = "readonly";
							// $reserv_conv_read_only = "";
							$request_unit_type="conv_unit";
							$req_unit_id=$rel['purchase_unit'];
						}
					}

					if($actualstock<=0){
						
						$reserv_read_only="readonly";
						$reserv_conv_read_only="readonly";
					}else{
						
							$reserv_read_only="";
							$reserv_conv_read_only="";	
						}

				if($rel["status"]==0){	
					$reserv_read_only="readonly";
					$reserv_conv_read_only="readonly";
					$basereadonly="readonly";
					$convreadonly="readonly";
					$po_read_only="readonly";
					$po_conv_read_only="readonly";
					$process_read_only="readonly";
					$process_conv_read_only="readonly";
					$req_read_only="readonly";
					$req_conv_read_only="readonly";
				}else {
					$reserv_conv_read_only="readonly";
					$convreadonly="readonly";
					$po_conv_read_only="readonly";
					$process_conv_read_only="readonly";
					$req_conv_read_only="readonly";
				}

				$req_unit_id=$rel['process_unit'];
				$request_unit_type="base_unit";

				// var_dump($rel["rp_po_qty"]);
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
											$sub_product_button='<a class="btn btn-success btn-xs" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_sub_product('.$rel["rp_id"].','.$rel["product_id"].','.$POST['eid'].','.$rel['rp_req_qty'].','.$rel['product_base_unit'].');" ><i class="fa fa-plus"></i></a>';
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

										$check_process_allocate_query="SELECT * from tbl_allocate_process where p_product_id=".$rel["product_id"]." AND p_status = '1' and p_ref_id = " . $rel["rp_id"];
										$check_process_allocate_result=$dbcon->query($check_process_allocate_query);
										if(brp_mysqli_num_rows($check_process_allocate_result) < 1 && $rel['status']==3)
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
										$action = "";
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
											// if($rel['status']!=3)
											// {
												$action = $edit_button.' '.$del_button;
											// }
										}

										/* start jayesh for checking product wise process required or not */

			//pathik production convert unit start
										$req_qty_conv=convert_stock($dbcon,$req_qty,$rel['rp_pid'],"conv_unit");
										$process_qty_conv=convert_stock($dbcon,$process_qty,$rel['rp_pid'],"conv_unit");
				//$po_base_qty=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"conv_unit");
				//$base_po_qty=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"base_unit");
										if($rel["conv_unit_name"]!=$rel["base_unit_name"]){
											$unitcon=$rel["product_base_qty"].' '.$rel["base_unit_name"].' = '.$rel["product_conv_qty"].' '.$rel["conv_unit_name"];
										}else{
											$unitcon="";
										}

			//pathik production convert unit end
										$drawing_number = "";
										$item_code = "";
										 if(in_array('drawing',$pro_search)){
									            $drawing_number = " </br> -- (".$rel['drawing_number'].")";
									        }
									        if(in_array('item',$pro_search)){
									            $item_code = "</br> -- (".$rel['product_icode'].")";
									        }

									        $req_qty	=  round_up($req_qty,5);
											$req_qty_conv	= round_up($req_qty_conv,5);
											$process_qty	= round_up(intval($process_qty),5);
											$process_qty_conv = round_up(intval($process_qty_conv),5);
											$po_qty = round_up(intval($po_qty),5);

											$po_qty_conv = round_up($po_qty_conv,5);

										$req_read_only="readonly";
										$req_conv_read_only="readonly";

										$po_base_qty = round_up(intval($po_base_qty),5);

										
										echo '<tr id="rp_row_'.$rel['rp_id'].'" data-rp_id="'.$rel["rp_id"].'" data-perent_rp_id="'.$rel["perent_id"].'" class="child_rp_row'.$rel['perent_id'].'">
										<td>'.$rel["sr_no"].'</td>
										<td><span style="color: red;"><strong>Name : </strong>'.$rel["product_name"].$item_code.$drawing_number.'</span></br><span style="color: #5708d5;"><strong>Category :</strong> '.$cat_name.' </span> </br><span style="color: #5708d5;"><strong>Type :</strong> '.$rel['product_type_name'].' </span></br> <span style="color: #3c7ab7;"> <strong>Minimum Qty :</strong> '.$rel["product_min_stock"].' </span></br> '.$image_name1.'</td>
										<!--<td>'.$image_name1.'</td>
										<td>'.$cat_name.'</td>
										<td>'.$rel["product_min_stock"].'</td>-->
										<td>
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control numbersOnly" name="base_current_stock'.$rel["rp_id"].'" id="base_current_stock'.$rel["rp_id"].'" onkeydown="return numericonly(event)"  value="'.$base_actualstock.'" readonly /> 
										</div>
											<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										<div class="col-md-9" style="margin-top:5px">
										<input type="number" min="0" class="form-control numbersOnly" name="current_stock'.$rel["rp_id"].'" id="current_stock'.$rel["rp_id"].'" onkeydown="return numericonly(event)"  value="'.$actualstock.'" readonly /> 
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
											<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										<!--<a class="btn btn-success btn-xs" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="show_current_stock_by_product('.$rel["rp_id"].','.$rel["rp_pid"].','.$rel["purchase_unit"].','.$rel['customer_id'].');" ><i class="fa fa-plus"></i></a>-->
										</td>
										<td>
										<!--<div class="col-md-9" >
										<input type="number" min="0" class="form-control numbersOnly" name="req_qty'.$rel["rp_id"].'" id="req_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$req_qty_sh.')"  value="'.$req_qty.'"  '.$req_read_only.' />

										<input type="hidden" name="req_qty_one'.$rel["rp_id"].'" id="req_qty_one'.$rel["rp_id"].'" value="'.$rel["req_qty_one"].'" />

										<input type="hidden" name="basic_req_qty'.$rel["rp_id"].'" id="basic_req_qty'.$rel["rp_id"].'" value="'.$req_qty.'" />

										<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>-->
										<div id="base'.$rel["rp_id"].'" style="'.$basedisplay.'">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="req_qty'.$rel["rp_id"].'" id="req_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$req_qty_sh.',\'base_unit\');"  value="'.$req_qty.'"  '.$req_read_only.' />

										<input type="hidden" name="req_qty_one'.$rel["rp_id"].'" id="req_qty_one'.$rel["rp_id"].'" value="'.$rel["req_qty_one"].'" />

										<input type="hidden" name="basic_req_qty'.$rel["rp_id"].'" id="basic_req_qty'.$rel["rp_id"].'" value="'.$req_qty.'" />
										<input type="hidden" name="reorder_qty'.$rel["rp_id"].'" id="reorder_qty'.$rel["rp_id"].'" value="'.$reorder_qty.'" />

										<input type="hidden" name="reorder_conv_qty'.$rel["rp_id"].'" id="reorder_conv_qty'.$rel["rp_id"].'" value="'.$reorder_conv_qty.'" />

										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>

										</div>
										<div id="conv'.$rel["rp_id"].'" style="margin-top:40px;'.$convdisplay.'">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="req_qty_conv'.$rel["rp_id"].'" id="req_qty_conv'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$req_qty_sh.',\'conv_unit\');"  value="'.$req_qty_conv.'"  '.$req_conv_read_only.' />
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										</div>
										<input type="hidden" name="req_unitid'.$rel["rp_id"].'" id="req_unitid'.$rel["rp_id"].'" value="'.$req_unit_id.'" />
										<input type="hidden" name="req_unitname'.$rel["rp_id"].'" id="req_unitname'.$rel["rp_id"].'" value="'.getunitname($dbcon,$req_unit_id).'" />
										<input type="hidden" name="req_product_id'.$rel["rp_id"].'" id="req_product_id'.$rel["rp_id"].'" value="'.$rel["rp_pid"].'" />
										<div class="col-md-12">
										<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										<input type="hidden" name="convtype'.$rel["rp_id"].'" id="convtype'.$rel["rp_id"].'" value="'.$request_unit_type.'" />
										<input type="hidden" name="pro_base_qty'.$rel["rp_id"].'" id="pro_base_qty'.$rel["rp_id"].'" value="'.$rel["product_base_qty"].'" />
										<input type="hidden" name="pro_convert_qty'.$rel["rp_id"].'" id="pro_convert_qty'.$rel["rp_id"].'" value="'.$rel["product_conv_qty"].'" />
										<span class="col-md-12" style="white-space: nowrap;color: #1a8d0d;font-weight: 600;">'.$unitcon.'</span>
										</td>
										<td>
										<div>
										<div class="col-md-9">
											<div id="rbase'.$rel["rp_id"].'" style="'.$basedisplay.'">
												<input type="number" min="0" class="form-control" name="res_qty'.$rel["rp_id"].'" id="res_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$res_qty_sh.',\'base_unit\');error_check('.$rel["rp_id"].','.$res_qty_sh.')" value="'.$rel["reserve_base_stock"].'" '.$reserv_read_only.' />
											</div>
											</div>
											<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										<div class="col-md-9"  style="margin-top:5px;>
											<div id="rconv'.$rel["rp_id"].'" style="'.$convdisplay.'">
												<input type="number" min="0" class="form-control" name="res_qty_conv'.$rel["rp_id"].'" id="res_qty_conv'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$res_qty_sh.');convert_unit_fun('.$rel["rp_id"].','.$res_qty_sh.',\'conv_unit\');" value="'.$rel["reserve_stock"].'" '.$reserv_conv_read_only.' />
											</div>
											</div>
											<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										<div class="col-md-12">
												<span style="display:none;" class="error" id="res_qty_err'.$rel["rp_id"].'" ></span>
										</div>		
										</div>		
										</td>
										<td>
										<!--<div class="col-md-9">
										<input type="number" min="0" class="form-control numbersOnly" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="error_check('.$rel["rp_id"].','.$process_qty_sh.')" onkeydown="return numericonly(event)"  value="'.$process_qty.'" '.$process_read_only.' />

										<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>-->

										<div id="baseprocess'.$rel["rp_id"].'" >
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$process_qty_sh.',\'base_unit\');" onkeypress="return isNumberKey(event)"  value="'.$process_qty.'" '.$process_read_only.' />


										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										</div>
										<div id="convprocess'.$rel["rp_id"].'" style="display:block;margin-top:40px;">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="conv_process_qty'.$rel["rp_id"].'" id="conv_process_qty'.$rel["rp_id"].'" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$process_qty_sh.',\'conv_unit\');" onkeypress="return isNumberKey(event)"  value="'.$process_qty_conv.'" '.$process_conv_read_only.' />
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										</div>
										<div class="col-md-12">
										<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										</td>
										<td>
										<!--<div class="col-md-9" >
										<input type="number" min="0" class="form-control numbersOnly" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$po_qty_sh.')"  value="'.$po_qty.'" '.$po_read_only.' />

										<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>-->
										<div id="basepo'.$rel["rp_id"].'" style="display:block;">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="base_po_qty'.$rel["rp_id"].'" id="base_po_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$po_qty_sh.',\'base_unit\');"  value="'.$po_base_qty.'" '.$po_read_only.' />
										<div class="col-md-12">
										<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										</div>
										<div id="convpo'.$rel["rp_id"].'" style="margin-top:40px">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$po_qty_sh.',\'conv_unit\');"  value="'.$po_qty.'" '.$po_conv_read_only.' />
										<div class="col-md-12">
										<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>

										</div>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										</div>
										</td>
										<td class="action'.$rel["rp_id"].'">'.$request_button.'  '. $unrequest_button .'
										<br>'.$action . '  '. $btn_document . '  '. $btn_remark .'</td>
										</tr>';

										

										$rp_id = $rel['rp_id'];
										$child_query = "select * from tbl_request_product where perent_id = '$rp_id'";
										$child_result=$dbcon->query($child_query);

										if(brp_mysqli_num_rows($child_result)>0)
										{
											if($rel['status']==0){
												get_child_tree($dbcon,$rp_id,$jobwork_type,$POST['eid']);
											}

										}


									}	/* child tree */




								}

else if(brp_strtolower($POST['mode']) == "get_tree_request_level") {
			
				$wo_type = $POST['wo_type'];
				$rp_id = $POST['rp_id'];
				$extra_stock = $POST['extra_stock'];
				$ext_stock_vendor_id = $POST['ext_stock_vendor_id'];
				$jobwork_type = $POST['jobwork_type'];
			
			
		$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_icode,dr.drawing_number,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.image_name,pro.product_id,pro.product_type,pro.product_base_qty,pro.product_conv_qty, ptm.product_type_name,pro.reorder_qty, bom.bom_version_id, pro.product_base_unit,pro.product_conv_unit FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			left join pro_ms_product_type as ptm on ptm.product_type_id=pro.product_type
			left join tbl_bom as bom on bom.bom_id=rpro.bom_id

			WHERE main_request=0 and rpro.status in (0,3) AND rpro.rp_id=".$rp_id; 

			// echo $bom1;		

			$result=$dbcon->query($bom1); 
			
			while($rel=brp_mysqli_fetch_assoc($result)){

				$btn_document = '<button type="button" id="btn_bom_doc" onclick="view_documents('.$rel['bom_id'].','.$rel['bom_version_id'].');" class="btn btn-info btn-xs" >View Documents</button>';
				$btn_remark = '<button type="button" id="btn_product_remark" onclick="show_product_remark_modal('.$rel['rp_id'].');" class="btn btn-info btn-xs" >Add Remark</button>';

				$customer_id = "";
				if($jobwork_type == '1'){
					$customer_id = $rel['customer_id'];
				}
				
				/* check product lead time and process */

				$lead_n_process = check_product_lead_time_and_process($dbcon,$rel["product_id"]); 
				$bclolr = '';
				if($lead_n_process == 0)
				{
				$bclolr = 'style="background-color:#FFFFA7;"';
				}
				$lead_n_process = 1;
				/* */	
				$unrequest_button = "";
				$req_btn_read_only = "";
				if($extra_stock == '1'){
					$req_btn_read_only = "style='display:none'";
				}

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
								$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');"';

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
									$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');"';										
									$req_btn_text = 'Request';
								}
								else if($rel['approval_status'] == 2){
									$req_btn_text = 'Rejected Request';
									$req_btn_action= '';
								}
								else
								{
									$req_btn_action = 'onclick="pending_approval();"';
									$req_btn_text = 'Pending Request';
								}
							}	
						}

						$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" '.$req_btn_action.'  '.$req_btn_read_only.'><i class="fa fa-paper-plane"></i> '.$req_btn_text.'</a>';
					}
					else {
						
						$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');"  '.$req_btn_read_only.' ><i class="fa fa-paper-plane"></i> Request</a>';
						/* JAYESH */
					}


				}else{
					$request_button='<a class="btn btn-success dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-check-circle"></i>  Requested</a>';

					$is_child_requested = check_child_is_requested($dbcon,$rel['rp_id']);

					if($is_child_requested > 0){
						$unrequest_button = "";
					}else{
						$unrequest_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" id="unreqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" style="margin: 5px;" onclick="unrequest_product('.$rel["rp_id"].');" ><i class="fa fa-close"></i> Unrequest</a>';
					}
				}
		

		$bom2="SELECT status,main_request,rp_req_qty,in_process_qty FROM `tbl_request_product` WHERE status!=2 AND rp_id=".$rel['perent_id'];
		$bom_rel2=brp_mysqli_fetch_assoc($dbcon->query($bom2));

				// $actualstock = 0;
			if($extra_stock == '1'){
				$actualstock=get_extra_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$ext_stock_vendor_id);
				$base_actualstock=get_extra_stock($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$ext_stock_vendor_id);

			}else{
				$cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$customer_id);
				$base_cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$customer_id);
				$rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],"","","","",$rel["branch_id"],"","","","",$customer_id);
				$base_rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["process_unit"],"","","","",$rel["branch_id"],"","","","",$customer_id);
				$wipstock=wipstock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$customer_id);
				$base_wipstock=wipstock($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$customer_id);
				$actualstock=$cstock-$rstock;
				$base_actualstock=$base_cstock-$base_rstock;
				$wip_purchase_stock=wip_purchase_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"]);
				$base_wip_purchase_stock=wip_purchase_stock($dbcon,$rel["rp_pid"],$rel["process_unit"]);
				$actualstock=$actualstock+$wipstock+$wip_purchase_stock;
				$base_actualstock=$base_actualstock+$base_wipstock+$base_wip_purchase_stock;
			}
				

				// var_dump($cstock);
				// var_dump($rstock);
				// var_dump($wipstock);

				$query_process_ch="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn
				where trn.rp_id=".$rel["rp_id"];
				
				$result_process_ch=$dbcon->query($query_process_ch);
				$cnt_process_ch=mysqli_num_rows($result_process_ch);
				
				if($cnt_process_ch>0){
					$ac_process_sto=process_stock_for_mrp($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["rp_id"],$rel["branch_id"]);
					$base_ac_process_sto=process_stock_for_mrp($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["rp_id"],$rel["branch_id"]);
					// var_dump($ac_process_sto);
					$actualstock=$actualstock+$ac_process_sto;
					$base_actualstock=$base_actualstock+$base_ac_process_sto;
				}
				


				$req_qty = "";
				if($rel["status"]==0){
					$reserv_read_only="readonly";
					$reserv_conv_read_only="readonly";
					$po_read_only="readonly";
					$po_conv_read_only="readonly";
					$process_read_only="readonly";
					$req_read_only="readonly";
					$req_qty=$rel['rp_req_qty'];
				}else{
					$reserv_read_only="readonly";
					$reserv_conv_read_only="";
					$po_read_only="";
					$po_conv_read_only="";
					$process_read_only="";
					$req_read_only="";

					if($bom_rel2['status']=="3"){
						$req_qty=$bom_rel2['rp_req_qty']*$rel["req_qty_one"];
					}else{
						$req_qty=$bom_rel2['in_process_qty']*$rel["req_qty_one"];
					}
					$req_qty=round($req_qty,4);
					
					
				}

				$reorder_qty = 0;
				$reorder_conv_qty = 0;

				/*if(!empty($rel['reorder_qty']) && $rel['reorder_qty'] > 0){
					$reorder_qty = $rel['reorder_qty'];		
					$reorder_conv_qty = convert_stock($dbcon,$reorder_qty,$rel['rp_pid'],"conv_unit");		

				   $chk_qty = 	ceil($req_qty  / $reorder_qty);
				   $req_qty = 	$reorder_qty * $chk_qty;
				}*/

				$process_qty = "";
				$po_qty = "";
				$po_qty_conv = "";
				$pr_setting_arr=explode(",",$rel['product_setting_check']);
				$having_child = check_having_child_product($dbcon,$rel['rp_id']);
			
				if($rel["status"]!=0){	
					
					// $process_qty = "";
					if($having_child)
					// if(in_array("process_product",$pr_setting_arr))
					{
						$process_read_only="";
							$process_conv_read_only="";
						$process_qty=$req_qty;
						$po_qty="";
						$basedisplay="display:block";
						$convdisplay="display:block";
						$basereadonly="";
						$convreadonly="";
						
						$request_unit_type="base_unit";
						$req_unit_id=$rel['process_unit'];


					}
					else
					{
						$process_read_only="readonly";
						$process_conv_read_only="readonly";
						$process_qty="";
						// $po_qty=$req_qty;
						$po_qty=$rel["rp_po_qty"];
						$convdisplay="display:block";
						$basedisplay="display:block";
						$basereadonly="";
						$convreadonly="";
						
						$request_unit_type="conv_unit";
						$req_unit_id=$rel['purchase_unit'];
					//pathik production convert unit start
						$po_base_qty=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"base_unit");
						// $po_qty_conv=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"conv_unit");
					//pathik production convert unit end
					}
				}else{
					if($having_child)
					// if(in_array("process_product",$pr_setting_arr))
					{
						$process_qty=$rel["in_process_qty"];
						$po_qty="";
						$po_qty="";
					}else
					{
						$process_qty="";
						$po_qty=$rel["rp_po_qty"];
						// $po_qty=convert_stock($dbcon,$po_base_qty,$rel['rp_pid'],"conv_unit");
						$po_base_qty=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"base_unit");
					
					}

			
					if(!empty($process_qty)){
						$basedisplay="display:block";
						$convdisplay="display:block";
						$basereadonly="";
						$convreadonly="";
						
						$request_unit_type="base_unit";
						$req_unit_id=$rel['process_unit'];
					}else{
						$convdisplay="display:block";
						$basedisplay="display:block";
						$basereadonly="";
						$convreadonly="";
						
						$request_unit_type="conv_unit";
						$req_unit_id=$rel['purchase_unit'];
					}
				}

				if($extra_stock == '1'){
						$process_read_only="readonly";
						$process_qty=0;
						$process_conv_qty=0;
						$po_qty=0;
						$po_qty_conv=0;
						$po_read_only="readonly";
						$po_conv_read_only="readonly";
						
						if($having_child){
							$basedisplay="display:block";
							$convdisplay="display:block";
							$basereadonly="";
							$convreadonly="";
							$reserv_read_only = "";
							// $reserv_conv_read_only = "readonly";
							$request_unit_type="base_unit";
							$req_unit_id=$rel['process_unit'];
						}else{
							$convdisplay="display:block";
							$basedisplay="display:block";
							$basereadonly="";
							$convreadonly="";
							$reserv_read_only = "readonly";
							// $reserv_conv_read_only = "";
							$request_unit_type="conv_unit";
							$req_unit_id=$rel['purchase_unit'];
						}
					}

					if($actualstock<=0){
						
						$reserv_read_only="readonly";
						$reserv_conv_read_only="readonly";
					}else{
						
							$reserv_read_only="";
							$reserv_conv_read_only="";	
						}

				if($rel["status"]==0){	
					$reserv_read_only="readonly";
					$reserv_conv_read_only="readonly";
					$basereadonly="readonly";
					$convreadonly="readonly";
					$po_read_only="readonly";
					$po_conv_read_only="readonly";
					$process_read_only="readonly";
					$process_conv_read_only="readonly";
					$req_read_only="readonly";
					$req_conv_read_only="readonly";
				}	

			$req_unit_id=$rel['process_unit'];
			$request_unit_type="base_unit";

			$check_process_query="SELECT * from tbl_wororder_product_process where  rp_id = ".$rel["rp_id"]." AND process_id != '0'";
			$check_process_result=$dbcon->query($check_process_query);

			if(brp_mysqli_num_rows($check_process_result)> 0)
			{ 
											$parent_delete_flag = 1;
			
										if($rel['status']==3){
											$sub_product_button='<a class="btn btn-success btn-xs" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_sub_product('.$rel["rp_id"].','.$rel["product_id"].','.$POST['eid'].','.$rel['rp_req_qty'].','.$rel['product_base_unit'].');" ><i class="fa fa-plus"></i></a>';
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
									
									/*	if($rel['status']==3){*/

										$check_process_allocate_query="SELECT * from tbl_allocate_process where p_product_id=".$rel["product_id"]." AND p_status = '1' and p_ref_id = " . $rel["rp_id"];
										$check_process_allocate_result=$dbcon->query($check_process_allocate_query);
										if(brp_mysqli_num_rows($check_process_allocate_result) < 1 && $rel['status']==3)
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
										$action = "";
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
											// if($rel['status']!=3)
											// {
												$action = $edit_button.' '.$del_button;
											// }
										}

										/* start jayesh for checking product wise process required or not */

			//pathik production convert unit start
										$req_qty_conv=convert_stock($dbcon,$req_qty,$rel['rp_pid'],"conv_unit");
										$process_qty_conv=convert_stock($dbcon,$process_qty,$rel['rp_pid'],"conv_unit");
				//$po_qty_conv=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"conv_unit");
				//$base_po_qty=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"base_unit");
										if($rel["conv_unit_name"]!=$rel["base_unit_name"]){
											$unitcon=$rel["product_base_qty"].' '.$rel["base_unit_name"].' = '.$rel["product_conv_qty"].' '.$rel["conv_unit_name"];
										}else{
											$unitcon="";
										}

			//pathik production convert unit end
										$drawing_number = "";
										$item_code = "";
										 if(in_array('drawing',$pro_search)){
									            $drawing_number = " </br> -- (".$rel['drawing_number'].")";
									        }
									        if(in_array('item',$pro_search)){
									            $item_code = "</br> -- (".$rel['product_icode'].")";
									        }

									        $req_qty	=  round_up($req_qty,5);
											$req_qty_conv	= round_up($req_qty_conv,5);
											$process_qty	= round_up($process_qty,5);
											$process_qty_conv = round_up($process_qty_conv,5);
											$po_qty = round_up($po_qty,5);
											$po_base_qty = round_up($po_base_qty,5);
											$po_qty_conv = round_up($po_qty,5);

										$req_read_only="readonly";
										$req_conv_read_only="readonly";

										echo '<tr id="rp_row_'.$rel['rp_id'].'" data-rp_id="'.$rel["rp_id"].'" data-perent_rp_id="'.$rel["perent_id"].'" class="child_rp_row'.$rel['perent_id'].'">
										<td>'.$rel["sr_no"].'</td>
										<td><span style="color: red;"><strong>Name : </strong>'.$rel["product_name"].$item_code.$drawing_number.'</span></br><span style="color: #5708d5;"><strong>Category :</strong> '.$cat_name.' </span> </br><span style="color: #5708d5;"><strong>Type :</strong> '.$rel['product_type_name'].' </span></br> <span style="color: #3c7ab7;"> <strong>Minimum Qty :</strong> '.$rel["product_min_stock"].' </span></br> '.$image_name1.'</td>
										<!--<td>'.$image_name1.'</td>
										<td>'.$cat_name.'</td>
										<td>'.$rel["product_min_stock"].'</td>-->
										<td>
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control numbersOnly" name="base_current_stock'.$rel["rp_id"].'" id="base_current_stock'.$rel["rp_id"].'" onkeydown="return numericonly(event)"  value="'.$base_actualstock.'" readonly /> 
										</div>
											<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										<div class="col-md-9" style="margin-top:5px">
										<input type="number" min="0" class="form-control numbersOnly" name="current_stock'.$rel["rp_id"].'" id="current_stock'.$rel["rp_id"].'" onkeydown="return numericonly(event)"  value="'.$actualstock.'" readonly /> 
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
											<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										<!--<a class="btn btn-success btn-xs" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="show_current_stock_by_product('.$rel["rp_id"].','.$rel["rp_pid"].','.$rel["purchase_unit"].','.$rel['customer_id'].');" ><i class="fa fa-plus"></i></a>-->
										</td>
										<td>
										<!--<div class="col-md-9" >
										<input type="number" min="0" class="form-control numbersOnly" name="req_qty'.$rel["rp_id"].'" id="req_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$req_qty_sh.')"  value="'.$req_qty.'"  '.$req_read_only.' />

										<input type="hidden" name="req_qty_one'.$rel["rp_id"].'" id="req_qty_one'.$rel["rp_id"].'" value="'.$rel["req_qty_one"].'" />

										<input type="hidden" name="basic_req_qty'.$rel["rp_id"].'" id="basic_req_qty'.$rel["rp_id"].'" value="'.$req_qty.'" />

										<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>-->
										<div id="base'.$rel["rp_id"].'" style="'.$basedisplay.'">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="req_qty'.$rel["rp_id"].'" id="req_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$req_qty_sh.',\'base_unit\');"  value="'.$req_qty.'"  '.$req_read_only.' />

										<input type="hidden" name="req_qty_one'.$rel["rp_id"].'" id="req_qty_one'.$rel["rp_id"].'" value="'.$rel["req_qty_one"].'" />

										<input type="hidden" name="basic_req_qty'.$rel["rp_id"].'" id="basic_req_qty'.$rel["rp_id"].'" value="'.$req_qty.'" />
										<input type="hidden" name="reorder_qty'.$rel["rp_id"].'" id="reorder_qty'.$rel["rp_id"].'" value="'.$reorder_qty.'" />

										<input type="hidden" name="reorder_conv_qty'.$rel["rp_id"].'" id="reorder_conv_qty'.$rel["rp_id"].'" value="'.$reorder_conv_qty.'" />

										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>

										</div>
										<div id="conv'.$rel["rp_id"].'" style="margin-top:40px;'.$convdisplay.'">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="req_qty_conv'.$rel["rp_id"].'" id="req_qty_conv'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$req_qty_sh.',\'conv_unit\');"  value="'.$req_qty_conv.'"  '.$req_conv_read_only.' />
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										</div>
										<input type="hidden" name="req_unitid'.$rel["rp_id"].'" id="req_unitid'.$rel["rp_id"].'" value="'.$req_unit_id.'" />
										<input type="hidden" name="req_unitname'.$rel["rp_id"].'" id="req_unitname'.$rel["rp_id"].'" value="'.getunitname($dbcon,$req_unit_id).'" />
										<input type="hidden" name="req_product_id'.$rel["rp_id"].'" id="req_product_id'.$rel["rp_id"].'" value="'.$rel["rp_pid"].'" />
										<div class="col-md-12">
										<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										<input type="hidden" name="convtype'.$rel["rp_id"].'" id="convtype'.$rel["rp_id"].'" value="'.$request_unit_type.'" />
										<input type="hidden" name="pro_base_qty'.$rel["rp_id"].'" id="pro_base_qty'.$rel["rp_id"].'" value="'.$rel["product_base_qty"].'" />
										<input type="hidden" name="pro_convert_qty'.$rel["rp_id"].'" id="pro_convert_qty'.$rel["rp_id"].'" value="'.$rel["product_conv_qty"].'" />
										<span class="col-md-12" style="white-space: nowrap;color: #1a8d0d;font-weight: 600;">'.$unitcon.'</span>
										</td>
										<td>
										<div>
										<div class="col-md-9">
											<div id="rbase'.$rel["rp_id"].'" style="'.$basedisplay.'">
												<input type="number" min="0" class="form-control" name="res_qty'.$rel["rp_id"].'" id="res_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$res_qty_sh.',\'base_unit\');error_check('.$rel["rp_id"].','.$res_qty_sh.')" value="'.$rel["reserve_base_stock"].'" '.$reserv_read_only.' />
											</div>
											</div>
											<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										<div class="col-md-9"  style="margin-top:5px;>
											<div id="rconv'.$rel["rp_id"].'" style="'.$convdisplay.'">
												<input type="number" min="0" class="form-control" name="res_qty_conv'.$rel["rp_id"].'" id="res_qty_conv'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$res_qty_sh.');convert_unit_fun('.$rel["rp_id"].','.$res_qty_sh.',\'conv_unit\');" value="'.$rel["reserve_stock"].'" '.$reserv_conv_read_only.' />
											</div>
											</div>
											<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										<div class="col-md-12">
												<span style="display:none;" class="error" id="res_qty_err'.$rel["rp_id"].'" ></span>
										</div>		
										</div>		
										</td>
										<td>
										<!--<div class="col-md-9">
										<input type="number" min="0" class="form-control numbersOnly" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="error_check('.$rel["rp_id"].','.$process_qty_sh.')" onkeydown="return numericonly(event)"  value="'.$process_qty.'" '.$process_read_only.' />

										<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>-->

										<div id="baseprocess'.$rel["rp_id"].'" >
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$process_qty_sh.',\'base_unit\');" onkeypress="return isNumberKey(event)"  value="'.$process_qty.'" '.$process_read_only.' />


										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										</div>
										<div id="convprocess'.$rel["rp_id"].'" style="display:block;margin-top:40px;">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="conv_process_qty'.$rel["rp_id"].'" id="conv_process_qty'.$rel["rp_id"].'" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$process_qty_sh.',\'conv_unit\');" onkeypress="return isNumberKey(event)"  value="'.$process_qty_conv.'" '.$process_conv_read_only.' />
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										</div>
										<div class="col-md-12">
										<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										</td>
										<td>
										<!--<div class="col-md-9" >
										<input type="number" min="0" class="form-control numbersOnly" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$po_qty_sh.')"  value="'.$po_qty.'" '.$po_read_only.' />

										<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>-->
										<div id="basepo'.$rel["rp_id"].'" style="display:block;">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="base_po_qty'.$rel["rp_id"].'" id="base_po_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$po_qty_sh.',\'base_unit\');"  value="'.$po_base_qty.'" '.$po_read_only.' />
										<div class="col-md-12">
										<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
										</div>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										</div>
										<div id="convpo'.$rel["rp_id"].'" style="margin-top:40px">
										<div class="col-md-9" >
										<input type="number" min="0" class="form-control" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$po_qty_sh.',\'conv_unit\');"  value="'.$po_qty_conv.'" '.$po_conv_read_only.' />
										<div class="col-md-12">
										<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>

										</div>
										</div>
										<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										</div>
										</td>
										<td class="action'.$rel["rp_id"].'">'.$request_button.'  '. $unrequest_button .'
										<br>'.$action . '  '. $btn_document . '  '. $btn_remark .'</td>
										</tr>';

										

										$rp_id = $rel['rp_id'];
										$child_query = "select * from tbl_request_product where perent_id = '$rp_id'";
										$child_result=$dbcon->query($child_query);

										if(brp_mysqli_num_rows($child_result)>0)
										{
											if($rel['status']==0){
												get_child_tree($dbcon,$rp_id,$jobwork_type,$POST['eid']);
											}

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
				get_tree_request($dbcon,$row['product_id'],$row['parent_id'],$bom_level,$cnt,$bom_new_id,$number,$one_q,$row['bom_trn_id'],$row['product_type'],$row['product_setting_check'],$po_req_no,$bom_real_level,'');
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
						$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.',1)"><i class="fa fa-paper-plane"></i> Request</a>';
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
							$btn='<a class="btn btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$counter_tree.',1)"><i class="fa fa-paper-plane"></i> Request</a>';
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

			$jobwork_type = $POST['jobwork_type'];

			$store_order_id = $POST['store_order_id'];
			$sales_order_id = $POST['sales_order_id'];
			$reject_status = $POST['reject_status'];
			$product_id = $POST['eid'];
			$work_order_id = "";
			$priority_status = 'Low';

			//delete_record("tbl_set_main_process","po_req_no='".$POST['po_req_no']."'",$dbcon);
			//in_process_qty_main
			$set_process="SELECT * FROM `tbl_set_main_process` WHERE sp_status=0 and user_id=".$_SESSION['user_id']." AND po_req_no='".$POST['po_req_no']."'";
			$set_process_rel=brp_mysqli_fetch_assoc($dbcon->query($set_process));
			$sales_customer_id = 0;
			
			if(!empty($POST['sales_order_trn_id'])){
				$so_bom="SELECT * FROM `tbl_sales_ordertrn` WHERE sales_ordertrn_id='".$POST['sales_order_trn_id']."'";
				$sorel=brp_mysqli_fetch_assoc($dbcon->query($so_bom));

				$priority_status = $sorel['priority_status'];
				$bom_id=$sorel['bom_id'];
				$sales_order_id= $sorel['sales_order_id'];
				
				$so_query="SELECT * FROM `tbl_sales_order` WHERE sales_order_id='".$sales_order_id."'";
				$so_row=brp_mysqli_fetch_assoc($dbcon->query($so_query));

				if($so_row['jobwork_type'] == '1'){
					$sales_customer_id= $so_row['cust_id'];
				}
				
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
			$info['bom_version_id']			=$POST['bom_version_id'];
			
			$info['customer_req_material'] = $POST['customer_req_material'];
			$info['customer_req_grade'] = $POST['customer_req_grade'];
			$info['customer_req_size'] = $POST['customer_req_size'];
			$info['customer_req_id'] = $POST['customer_req_id'];
			$info['customer_req_length'] = $POST['customer_req_length'];
			$info['customer_req_heat'] = $POST['customer_req_heat'];
			$info['customer_req_coc'] = $POST['customer_req_coc'];
			$info['customer_ref_no'] = $POST['customer_ref_no'];
			$info['customer_asset_serial'] = $POST['customer_asset_serial'];
			$info['customer_bevel_spec'] = $POST['customer_bevel_spec'];
			$info['store_order_id'] = $store_order_id;
			$info['priority_status'] = $priority_status;

			if(!empty($store_order_id)){
				 update_store_oreder_request_workorder_qty_status($dbcon,$store_order_id,$product_id,$POST['rp_req_qty']);	
			}

			if($reject_status == '1'){

				$request_qty  =  $POST['rp_req_qty'];
				$set11="select rp.*,sum(reject_qty-reject_request_qty) as pending_qty from tbl_qc_process_trn as rp
					where rp.qc_process_status=0 and rp.reject_qty>0 and CAST(reject_qty as DECIMAL(50,2)) > CAST(reject_request_qty as DECIMAL(50,2)) and rp.product_id=".$POST['eid'];
				$ser=$dbcon->query($set11);
				while($set_row=brp_mysqli_fetch_assoc($ser)){
					$pending_qty=$set_row['pending_qty'];
					if($request_qty>0){
						if($pending_qty>0){
							if($pending_qty>=$request_qty){
								$rqty=$request_qty;
								$request_qty=$request_qty-$request_qty;
							}else{
								$rqty=$pending_qty;
								$request_qty=$request_qty-$pending_qty;
							}

							$rej_qinfo['reject_request_qty'] = $set_row['reject_request_qty'] + $rqty;

							$updateid=update_record('tbl_qc_process_trn', $rej_qinfo,"qc_process_trn_id=".$set_row['qc_process_trn_id'] ,$dbcon);
						}
					}
				}
			}
			
			if(empty($set_process_rel['sp_id'])){
				$info['cdate']					= date('Y-m-d H:i:s');
				$info['mdate']					= date('Y-m-d H:i:s');
				$info['adata']					= date('Y-m-d H:i:s');
				$info['user_id']				= $_SESSION['user_id'];
				$info['po_req_no']				= load_series_no($dbcon,9);
				
				$inserid=add_record('tbl_set_main_process', $info, $dbcon,$POST['branch_id']);
				$work_order_id = $inserid;

				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);

				

			}else{
				$info['mdate']					=date('Y-m-d H:i:s');
				$info['muser_id']				=$_SESSION['user_id'];
				
				$updateid=update_record('tbl_set_main_process', $info,"sp_id=".$set_process_rel['sp_id'] ,$dbcon,$POST['branch_id']);
				$work_order_id = $set_process_rel['sp_id'];
			}
			
			
			$set_pro="SELECT product_base_unit,product_conv_unit,product_desc FROM `product_mst` WHERE product_status=0 AND product_id='".$POST['eid']."'";
			$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));
			
			$info_su['sp_id']				= $inserid;
			$info_su['sr_no']				= 0;
			$info_su['work_order_no']			= $info['po_req_no'];
			$info_su['work_order_date']		= $info['po_req_date'];
			$info_su['rp_pid']				= $POST['eid'];//product_id
			$info_su['rp_req_qty']			= $POST['in_process_qty_main'];//required qty
			$info_su['sales_order_trn_id']	= $POST['sales_order_trn_id'];//required qty
			$info_su['rp_po_qty']			= "";//po qty
			$info_su['in_process_qty']		= $POST['in_process_qty_main'];//process qty
			$info_su['rp_req_type']			= "sales_order";//type
			$info_su['process_unit']		= $setpro_rel['product_base_unit'];
			$info_su['purchase_unit']		= $setpro_rel['product_conv_unit'];
			$info_su['perent_id']			= 0;
			$info_su['main_request']		= 1;
			$info_su['status']				= 0;
			$info_su['user_id']				= $_SESSION['user_id'];
			$info_su['company_id']			= $_SESSION['company_id'];
			$info_su['cdate']					= date('Y-m-d H:i:s');
			$info_su['bom_id']				=$bom_rel['bom_id'];
			$info_su['product_version']		= $bom_rel['bom_id'];
			$info_su['store_order_id'] = $store_order_id;
			$info_su['jobwork_type']	=  $jobwork_type;
			$info_su['customer_id']	=  $sales_customer_id;
			$info_su['sales_order_id']			= $sales_order_id;
			$info_su['priority_status']			= $priority_status;
			$info_su['product_remark']			= $setpro_rel['product_desc'];
			
			$inserid_sub1=add_record('tbl_request_product', $info_su, $dbcon,$POST['branch_id']);

			if(empty($set_process_rel['sp_id'])){
				/* START JAYESH */
				
				$workorder_query_pro="SELECT * FROM `tbl_bom` as bom
				left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
				left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
				left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id WHERE tbl_product_process.status = 0 and  pro_bom_process.process_status = 0 and prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$POST['eid']."'"; 

				$workorder_query_result = $dbcon->query($workorder_query_pro);

				if(brp_mysqli_num_rows($workorder_query_result)>0)
				{
					while($wproduct_process=brp_mysqli_fetch_assoc($workorder_query_result))
					{
						$wwpp_info['product_id'] = $POST['eid'];		
						$wwpp_info['rp_id'] = 	$inserid_sub1;
						$wwpp_info['process_priority'] = 	$wproduct_process['priority'];
						$wwpp_info['process_time'] = 	$wproduct_process['process_time'];
						$wwpp_info['process_type'] = 	$wproduct_process['process_type'];
						$wwpp_info['process_opening'] = 	$wproduct_process['process_opening'];
						$wwpp_info['process_id'] = 	$wproduct_process['process_id'];	
						$wwpp_info['cdate']				= date("Y-m-d H:i:s");
						$wwpp_info['user_id']			= $_SESSION['user_id'];
						$wwpp_info['company_id']			= $_SESSION['company_id'];
						$wwpp_info['branch_id']			= $POST['branch_id'];	
						$wwpp_info['description']			= $wproduct_process['description'];					

					//echo "<pre>"; print_r($wwpp_info);

						$inserestimateid=add_record('tbl_wororder_product_process', $wwpp_info, $dbcon);
					}

				}
				/* END JAYESH */
			}

			$work_order_id = $inserid;
				/*$info_wo['sp_status']=2;
		$updateid12=update_record("tbl_set_main_process", $info_wo,"sp_id=".$work_order_id , $dbcon);*/
		
		
		$bom_q1="SELECT rp_id,rp_pid FROM `tbl_request_product` WHERE main_request=1 and sp_id=".$work_order_id;
		$bom_rel_q1=brp_mysqli_fetch_assoc($dbcon->query($bom_q1));
		
		$query="select * from tbl_request_product as i
		where i.rp_id=".$bom_rel_q1['rp_id'];
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);

		$info_req['rp_req_date']		=date('Y-m-d');
		$info_req['rp_req_qty']			=$POST['rp_req_qty'];
		$info_req['rp_po_qty']			=$POST['rp_po_qty'];
		$info_req['in_process_qty']		=$POST['in_process_qty_main'];
		$info_req['reject_status']		=$POST['reject_status'];

		$info_req['status']				=0;
		$info_req['cdate']				=date('Y-m-d H:i:s');
		$info_req['user_id']			=$_SESSION['user_id'];
		$info_req['company_id']			=$_SESSION['company_id'];

		if($info_req['rp_po_qty']>"0"){
			$indent_no=load_common_no($dbcon,17);
			update_common_no($dbcon,17);
			$info_req['indent_status']		= 1;
			$info_req['indent_no']			= $indent_no;
			$info_req['indent_date']		= date('Y-m-d');
		}
		if($info_req['in_process_qty']>"0"){
			$indent_no=load_common_no($dbcon,JOBCARD);
			update_common_no($dbcon,JOBCARD);
			$info_req['job_card_status']		= 1;
			$info_req['job_card_no']			= $indent_no;
			$info_req['job_card_date']		= date('Y-m-d');
		}
		if(!empty($POST['sales_order_trn_id'])){
			$info_req['sales_order_trn_id']		= $POST['sales_order_trn_id'];
		}
		$updateid=update_record("tbl_request_product", $info_req,"rp_id=".$bom_rel_q1['rp_id'] , $dbcon);

		$set_pro="SELECT product_base_unit,product_conv_unit,product_base_qty,product_conv_qty,product_id,batch_wise_stock_manage FROM `product_mst` WHERE product_status=0 AND product_id='".$bom_rel_q1['rp_pid']."'";
	$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));

	//indnet wip stock add start
		if($info['rp_po_qty']>0){
		if($setpro_rel['product_conv_unit']==$row['purchase_unit']){
				$type="base_unit";
				$con_stock=$info['rp_po_qty'];
				$base_stock=convert_stock_new($dbcon,$info['rp_po_qty'],$bom_rel_q1['rp_pid'],$type);
			}else{
				$type="conv_unit";
				$base_stock=$info['rp_po_qty'];
				$con_stock=convert_stock_new($dbcon,$info['rp_po_qty'],$bom_rel_q1['rp_pid'],$type);
			}

			$info_wip_add['rp_id']					= $bom_rel_q1['rp_id'];
			$info_wip_add['type_flag']				= 3;
			$info_wip_add['po_trn_id']				= 0;
			$info_wip_add['sales_order_trn_id']		= 0;
			//$info_wip_add['allocate_for_rp_id']		= 0;
			//$info_wip_add['allocate_table_id']		= $POST['sales_order_trn_id'];
			$info_wip_add['allocate_base_qty']		= $base_stock;
			$info_wip_add['allocate_base_unit']		= $setpro_rel['product_base_unit'];
			$info_wip_add['allocate_conv_qty']		= $con_stock;
			$info_wip_add['allocate_conv_unit']		= $setpro_rel['product_conv_unit'];
			$info_wip_add['stock_flag']				= 1;
			$info_wip_add['cdate']					= date("Y-m-d H:i:s");
			$info_wip_add['user_id']				= $_SESSION['user_id'];
			$info_wip_add['company_id']				= $_SESSION['company_id'];

			$inser_wip_add=add_record('wip_stock_allocate', $info_wip_add, $dbcon,$row['branch_id']);

			if($POST['smode']=="add_rej"){
				reject_request_qty_update($dbcon,$info['rp_req_qty'],$bom_rel_q1['rp_pid'],$inser_wip_add,$row['purchase_unit']);
			}

			if(!empty($POST['sales_order_trn_id'])){

				$info_wip_deduct['rp_id']					= $bom_rel_q1['rp_id'];
				$info_wip_deduct['type_flag']				= 3;
				$info_wip_deduct['po_trn_id']				= 0;
				$info_wip_deduct['sales_order_trn_id']		= $POST['sales_order_trn_id'];
				$info_wip_deduct['allocate_for_rp_id']		= $bom_rel_q1['rp_id'];
				$info_wip_deduct['perent_id']				= $inser_wip_add;
				$info_wip_deduct['allocate_base_qty']		= $base_stock;
				$info_wip_deduct['allocate_base_unit']		= $setpro_rel['product_base_unit'];
				$info_wip_deduct['allocate_conv_qty']		= $con_stock;
				$info_wip_deduct['allocate_conv_unit']		= $setpro_rel['product_conv_unit'];
				$info_wip_deduct['stock_flag']				= 2;
				$info_wip_deduct['cdate']					= date("Y-m-d H:i:s");
				$info_wip_deduct['user_id']					= $_SESSION['user_id'];
				$info_wip_deduct['company_id']				= $_SESSION['company_id'];

				$inser_wip_deduct=add_record('wip_stock_allocate', $info_wip_deduct, $dbcon,$row['branch_id']);
			
			$set_pro_w="SELECT allocate_base_qty_used,allocate_conv_qty_used FROM `wip_stock_allocate` WHERE wip_stock_allocate_id='".$info_wip_deduct['perent_id']."'";
		$setpro_rel_w=brp_mysqli_fetch_assoc($dbcon->query($set_pro_w));

			$bsto=$setpro_rel_w['allocate_base_qty_used']+$info_wip_deduct['allocate_base_qty'];
			$csto=$setpro_rel_w['allocate_conv_qty_used']+$info_wip_deduct['allocate_conv_qty'];

			$query_invoicetype1 = $dbcon->query("UPDATE wip_stock_allocate SET allocate_base_qty_used =".$bsto.",allocate_conv_qty_used=".$csto." WHERE wip_stock_allocate_id =".$info_wip_deduct['perent_id']);
			}
		}
		//indnet wip stock add end

		// jobcard wip stock add
		if($info['in_process_qty']>0){
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
			$info_wip_add1['type_flag']				= 3;
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

			if($POST['smode']=="add_rej"){
				reject_request_qty_update($dbcon,$info['rp_req_qty'],$bom_rel_q1['rp_pid'],$inser_wip_add1);
			}

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
		}
		// jobcard wip stock end
		if($POST['in_process_qty_main']!='')
		{
			if($POST['in_process_qty_main']!="0"){
				
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

					// $inserid_a2=add_record('tbl_wororder_product_process', $infow, $dbcon, $POST['branch_id']);

						/*
						Code By Umair : 05/11/2020
						Comment: Insert Work Order Resource Allocation In tbl_work_order_resource_allocate table
						*/
						if($relw['process_type']=='1'){
							$resource_id = $relw['resource_id'];
							$request_id = $row['rp_id']; 
							$process_id = $relw['process_id'];
							$product_id = $relw['product_id'];
							$qty = $POST['in_process_qty_main'];
							$time_per_qty = $relw['process_time'];
							
							$action_type = 'add';
							$edit_id = '';
							work_order_resource_allocate($dbcon, $resource_id, $request_id, $process_id, $product_id, $qty, $time_per_qty, $edit_id, $action_type, '', $POST['branch_id']);
							
							

						}
						
					}
				}
			}
			if($POST['in_process_qty_main']!='')
			{
				if($POST['in_process_qty_main']!="0"){
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
					$info5['p_qty']				= $POST['in_process_qty_main'];		
					$info5['pen_qty']			= $POST['in_process_qty_main'];		
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


					if($companyConfiguration['batch_wise_stock'] == '1' &&  $companyConfiguration['batch_process'] == '0' && $setpro_rel['batch_wise_stock_manage'] == '1'){
						$info5['batch_process_start_time'] = 1;
					}

					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					
					$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon,$POST['branch_id']);

					$query_reserve="select * from tbl_request_product where status=0 and perent_id=".$row['rp_id'];
						$rs_reserve=$dbcon->query($query_reserve);	
						while($rel_reserve=brp_mysqli_fetch_array($rs_reserve)){

							$query_resu1 = $dbcon->query("UPDATE tbl_reserve_stock SET p_id =".$inserid_alloc." WHERE p_id=0 and request_id =".$rel_reserve['rp_id']);

						}
				}
			}
			//var_dump($info['rp_req_qty']);
			//var_dump($bom_rel_q1['rp_id']);
			
				//echo $bom_rel_q1['rp_id'];
				//echo "222";

			if($POST['smode']=="add_all"){
				$all_request_data_use=all_request_data_use($dbcon,$bom_rel_q1['rp_id'],$info['rp_po_qty']);
			}
			/* if(!empty($POST['sales_order_trn_id'])){
				$query_invoicetype = $dbcon->query("UPDATE tbl_sales_ordertrn SET work_order_qty = work_order_qty +".$info['in_process_qty_main']." WHERE sales_ordertrn_id = ".$POST['sales_order_trn_id']);
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
				

			//var_dump($inserid_sub1);
	//for ($x = 0; $x <= 100; $x++) {	

		/*$query22="select bom_id,product_base_qty from tbl_bom as bom_trn 
				where bom_status=0 and bom_product=".$POST['eid'];	
			$result22=$dbcon->query($query22);
		$rel22=brp_mysqli_fetch_assoc($result22);
			*/

		$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.reorder_qty, pro.product_desc from tbl_bomtrn as bom_trn 
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
			$reorder_qty = 0;
						
				/*if(!empty($rel1['reorder_qty']) && $rel1['reorder_qty'] > 0){
					$reorder_qty = $rel1['reorder_qty'];		
				   $chk_qty = 	ceil($base_qty  / $reorder_qty);
				   $base_qty = 	$reorder_qty * $chk_qty;
				}*/
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

			$info_sub['sp_id']				= $inserid;
			$info_sub['sr_no']				= $i;
			$info_sub['rp_req_date']		= date('Y-m-d H:i:s');
			$info_sub['rp_pid']				= $rel1['product_id'];//product_id
			$info_sub['rp_req_qty']			= $base_qty;//required qty
			$info_sub['req_qty_one']		= $base_one_qty;//required qty
			$info_sub['rp_po_qty']			= "";//po qty
			$info_sub['in_process_qty']		= "";//process qty
			$info_sub['rp_req_type']		= "min_max";//type
			$info_sub['process_unit']		= $rel1['product_base_unit'];
			$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
			$info_sub['perent_id']			= $inserid_sub1;
			$info_sub['status']				= 3;
			$info_sub['cdate']				= date('Y-m-d H:i:s');
			$info_sub['user_id']			= $_SESSION['user_id'];
			$info_sub['company_id']			= $_SESSION['company_id'];
			//$info_sub['main_request']		= $POST['g_total'];
			
			$info_sub['product_version']	= $rel1['p_bom_id'];
			$info_sub['bom_id']				= $rel1['p_bom_id'];
			$info_sub['jobwork_type']	=  $jobwork_type;
			$info_sub['customer_id']	=  $sales_customer_id;
			$info_sub['sales_order_id']			= $sales_order_id;
			$info_sub['store_order_id'] = $store_order_id;
			$info_sub['priority_status'] = $priority_status;
			$info_sub['product_remark']			= $rel1['product_desc'];
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
					$inserid_sub12=add_record('tbl_wo_material_trn', $mat_data, $dbcon,$POST['branch_id']);

				}
			}

			/*   Material Formula */

			/*	$query_pro1="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$rel1['product_id']; */

			/*$query_pro1="select* from pro_bom_process where product_id = ".$rel1['product_id']." AND bom_id =".$bom_rel['bom_id'];	*/
			
			$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status = 0 and  pro_bom_process.process_status = 0 and  bom.bom_product='".$rel1['product_id']."' and bom.bom_id=".$info_sub['bom_id']." AND pro_bom_process.pr_process_id != ''"; 

			$rel_pro1 = $dbcon->query($query_pro1);
			
			if(brp_mysqli_num_rows($rel_pro1)>0)
			{
				while($product_process_row=brp_mysqli_fetch_assoc($rel_pro1))
				{
					$wpp_info['product_id'] = $rel1['product_id'];		
					$wpp_info['rp_id'] = 	$inserid_sub;
					$wpp_info['process_priority'] = 	$product_process_row['priority'];
					$wpp_info['process_time'] = 	$product_process_row['process_time'];
					$wpp_info['process_type'] = 	$product_process_row['process_type'];
					$wpp_info['process_opening'] = 	$product_process_row['process_opening'];
					$wpp_info['process_id'] = 	$product_process_row['process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $POST['branch_id'];
					$wpp_info['description']			= $product_process_row['description'];
					

					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
				}
			}
// die;
			

			/* END JAYESH */

			min_max_bom_show($dbcon,$rel1['p_bom_id'],$base_qty,$i,$call,$space,$inserid,$inserid_sub,'',$POST['branch_id'],$jobwork_type,$sales_customer_id,$sales_order_id,$store_order_id,$priority_status);
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
					$arr['msg'] = "1";
					$arr['sp_id'] = $work_order_id;
				}
				else {
					$arr['msg'] = "0";
				}
				echo json_encode($arr);
			}
			else if(brp_strtolower($POST['mode']) == "check_main_process_request") {
				
				$po_req_no=$POST['po_req_no'];
				$eid=$POST['eid'];
				$sales_order_trn_id=$POST['sales_order_trn_id'];
				$bom_version_id = $POST['bom_version_id'];
				$wo_type = $POST['wo_type'];
				$rp_id = $POST['rp_id'];
				$json=array();
				if(brp_strtolower($wo_type) == "direct_jobcard"){
					
					$qry = "select rp_id,rp_req_qty,in_process_qty,rp_po_qty,job_card_no from tbl_request_product where 	status != 2 and rp_id = " . $rp_id ." and rp_pid =" . $eid;
						$result=$dbcon->query($qry);
						$count=brp_mysqli_num_rows($result);
						$res=brp_mysqli_fetch_assoc($result);

						$having_child = check_having_child_product($dbcon,$res['rp_id']);
						
						if($having_child){
							$process_qty=$res['in_process_qty'];
							$po_qty=$res['rp_po_qty'];
						}else{
							$process_qty=0;
							$po_qty=$res['in_process_qty'];
						}		
						$request_qty=$res['rp_req_qty'];
						
						$status=0;
						$po_req_no=$res['job_card_no'];
						$json['req_qty']=$request_qty;
						$json['process_qty']=$process_qty;
						$json['po_qty']=$po_qty;
						$json['sp_status']=$status;
						$json['po_req_no']=$po_req_no;
				}else{
					
					if(empty($sales_order_trn_id)){
						
						if($bom_version_id != '')
						{	

							if(!empty($POST['store_order_id'])){
								$q=$dbcon->query("select spro.* from tbl_set_main_process as spro
								left join tbl_request_product as rsp on rsp.sp_id=spro.sp_id
								where  spro.sp_status IN (0,2) and  spro.company_id=".$_SESSION['company_id']." and spro.product_id=".$eid." and spro.store_order_id='".$POST['store_order_id']."'");
							}else{
								$q=$dbcon->query("select spro.* from tbl_set_main_process as spro
								left join tbl_request_product as rsp on rsp.sp_id=spro.sp_id
								where  spro.sp_status IN (0,2) and  spro.company_id=".$_SESSION['company_id']." and spro.product_id=".$eid." and spro.po_req_no='".$po_req_no."'");
							}
							
							
						}
						else
						{
							
							$q=$dbcon->query("select * from tbl_set_main_process where  sp_status=0 and company_id=".$_SESSION['company_id']." and user_id=".$_SESSION['user_id']." and product_id=".$eid."");
						}
						$count=brp_mysqli_num_rows($q);
					}else{
						if(brp_strtolower($wo_type) == "so_request"){
							$count = 0;
						}else{

							$q=$dbcon->query("select spro.* from tbl_set_main_process as spro
							left join tbl_request_product as rsp on rsp.sp_id=spro.sp_id
							where  spro.sp_status=0 and spro.po_req_no = '".$POST['po_req_no']."' and rsp.sales_order_trn_id=".$sales_order_trn_id." and spro.company_id=".$_SESSION['company_id']." and spro.product_id=".$eid."");
						
						$count=brp_mysqli_num_rows($q);	
						}
					}

					
					if($count>0)
					{
						$row=brp_mysqli_fetch_array($q);	

						$qry = "select rp_id from tbl_request_product where status != 2 and sp_id = " . $row['sp_id'] . " and rp_pid =" . $eid;
						$result=$dbcon->query($qry);
						$res=brp_mysqli_fetch_assoc($result);

						$having_child = check_having_child_product($dbcon,$res['rp_id']);
						
						if($having_child){
							$process_qty=$row['in_process_qty_main'];
							$po_qty=$row['rp_po_qty'];
						}else{
							$process_qty=0;
							$po_qty=$row['in_process_qty_main'];
						}		
						$request_qty=$row['rp_req_qty'];
						
						$status=$row['sp_status'];
						$po_req_no=$row['po_req_no'];
						$json['req_qty']=$request_qty;
						$json['process_qty']=$process_qty;
						$json['po_qty']=$po_qty;
						$json['sp_status']=$status;
						$json['po_req_no']=$po_req_no;
					}
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
			$extra_stock = $POST['extra_stock'];
			$ext_stock_vendor_id = $POST['ext_stock_vendor_id'];

			$query="select * from tbl_request_product as i
			where i.rp_id=".$POST['rp_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);

			if($POST['convtype']=="conv_unit"){
				$rresqty=$POST['res_qty_conv'];
				$info['reserve_stock']		= $rresqty;
				$info['reserve_base_stock']	= $POST['res_qty'];
			}else{
				$rresqty=$POST['res_qty'];
				$info['reserve_base_stock']	= $rresqty;
				$info['reserve_stock']		= $POST['res_qty_conv'];
			}


			// var_dump($info);

			$query_1="select * from tbl_request_product as i
			where i.rp_id=".$row['perent_id'];
			$result_1=$dbcon->query($query_1);
			$row_1=brp_mysqli_fetch_assoc($result_1);

			$parent_req_qty = $row_1['rp_req_qty'];

			$info['rp_req_date']		=date('Y-m-d');
			$info['rp_req_qty']			=$POST['req_qty'];
			$info['rp_po_qty']			=$POST['po_qty'];
			$info['in_process_qty']		=$POST['process_qty'];
			// $info['reserve_stock']		=$rresqty;


			/* SANAT HIDE 17-05-22 FOR REORDER QTY UPDATE */
			// ---------------------------------------------------------------
				/*$req_qty_one_upd = $POST['req_qty'] / $parent_req_qty;
				$info['req_qty_one']		=$req_qty_one_upd;*/
			// ---------------------------------------------------------------
			
			$info['rp_po_base_qty']		=$POST['rp_po_base_qty'];
			$info['in_process_conv_qty']=$POST['in_process_conv_qty'];

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

			// if($POST['process_qty']>"0" || $rresqty>"0"){
			if($POST['process_qty']>"0"){
				$indent_no=load_common_no($dbcon,JOBCARD);
				update_common_no($dbcon,JOBCARD);
				$info['job_card_status']		= 1;
				$info['job_card_no']			= $indent_no;
				$info['job_card_date']		= date('Y-m-d');
			}
			$updateid=update_record("tbl_request_product", $info,"rp_id=".$POST['rp_id'] , $dbcon, $_POST['branch_id']);

			// var_dump($info);
			
			//$inserid=add_record('tbl_request_product', $info, $dbcon);
			//$de=add_request_reserve_stock($dbcon,$pr_id,$POST['at_reserve']);
			//$de=add_request_reserve_stock($dbcon,$inserid,$POST['at_reserve']);
			// var_dump($POST['res_qty']);
			if($POST['res_qty']!="0"){
				if($POST['res_qty']!=""){
					//add_request_reserve_stock($dbcon,$POST['rp_id'],$POST['res_qty'],$row['purchase_unit'], $_POST['branch_id']);

				 	$query_rstock="select * from work_order_reserve_temp as i
							where i.status=0 and i.rp_id=".$POST['rp_id'];
							$result_rstock=$dbcon->query($query_rstock);
					while($row_rstock=brp_mysqli_fetch_assoc($result_rstock)){
						$reserve_qty=$row_rstock['reserve_qty'];
						$batch_where="";
						if(!empty($row_rstock['stock_id'])){
							$batch_where=" and i.stock_id in(".$row_rstock['stock_id'].")";
						}

						$customer_id = $POST['customer_id'];

						$whr_cust = "";
						if($customer_id !="" && $customer_id !="0"){
							$whr_cust = " and customer_id = " . $customer_id;
						}
						$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i

							where stock_status=0 and i.branch_id=".$row['branch_id']." and stock_flage=1 and  cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) ".$batch_where." and product_id = ".$row_rstock['product_id']." and i.godown_id=".$row_rstock['godown_id'] . $whr_cust;
							$result_dstock=$dbcon->query($query_dstock);
						while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
							if($row_dstock['convert_unit']==$row_rstock['unit_id']){
								$pending_stock=$row_dstock['pending_conv_stock'];
							}else{
								$pending_stock=$row_dstock['pending_base_stock'];	
							}
							if($reserve_qty>0){
								if($pending_stock>=$reserve_qty){
									$rqty=$reserve_qty;
									$reserve_qty=$reserve_qty-$reserve_qty;
								}else{
									$rqty=$pending_stock;
									$reserve_qty=$reserve_qty-$pending_stock;
								}

								$que="select * from product_mst as ta where product_id=".$row_rstock['product_id'];
								$rs_di=$dbcon->query($que);
								$re=brp_mysqli_fetch_assoc($rs_di);
								
								if($re['product_conv_unit']==$row_rstock['unit_id']){
									$type="base_unit";
									$con_stock=$rqty;
									$base_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
								}else{
									$type="conv_unit";
									$base_stock=$rqty;
									$con_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
								}

								$que_s="select perent_id from tbl_request_product as ta where rp_id=".$row_rstock['rp_id'];
								$rs_dwi=$dbcon->query($que_s);
								$re_df=brp_mysqli_fetch_assoc($rs_dwi);

								$que_s1="select p_id from tbl_allocate_process as ta where p_status != 2 and previous_process_id=0 and p_ref_id=".$re_df['perent_id'];
								$rs_dwi1=$dbcon->query($que_s1);
								$re_df1=brp_mysqli_fetch_assoc($rs_dwi1);
								

								$info_rese['reserve_date']		= date('Y-m-d');
								$info_rese['product_id']		= $row_rstock['product_id'];
								$info_rese['godown_id']			= $row_dstock['godown_id'];
								$info_rese['base_unit']			= $re['product_base_unit'];
								$info_rese['base_stock']		= $base_stock;
								$info_rese['convert_unit']		= $re['product_conv_unit'];
								$info_rese['convert_stock']		= $con_stock;
								$info_rese['stock_flage']		= "1";
								$info_rese['request_id']		= $row_rstock['rp_id'];
								$info_rese['ref_name']			= "wo_allocate";
								$info_rese['ref_id']			= "0";
								$info_rese['p_id']				= $re_df1['p_id'];
								$info_rese['stock_id']			= $row_dstock['stock_id'];
								
								$info_rese['cdate']				= date("Y-m-d H:i:s");
								$info_rese['user_id']			= $_SESSION['user_id'];
								$info_rese['company_id']		= $_SESSION['company_id'];		
								$info_rese['customer_id']		= $POST['customer_id'];		
								// var_dump($info_rese);					
								$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row_dstock['branch_id']);

								update_workorder_complete_qty_and_Status($dbcon,$row_rstock['rp_id'],$rqty);

								$wo_res_temp_info['status'] = 3;
                                
                                $updatetrnid=update_record('work_order_reserve_temp',$wo_res_temp_info,"work_order_reserve_temp_id=".$row_rstock['work_order_reserve_temp_id'] , $dbcon);

								if($row_dstock['base_unit']==$re['product_base_unit']){
									$used_base_stock=$row_dstock['used_base_stock']+$base_stock;
									$used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
								}else{
									$used_base_stock=$row_dstock['used_convert_stock']+$con_stock;
									$used_convert_stock=$row_dstock['used_base_stock']+$base_stock;
								}
								
								$info_stock['used_base_stock']		= $used_base_stock;
								$info_stock['used_convert_stock']	= $used_convert_stock;
								
								$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$info_rese['stock_id'] , $dbcon);
								}
							

						}
					}
					//purchase start
						$purchase_res_stock=$POST['purchase_res_stock'];
						$sp_purchase_trn_id=$POST['sp_purchase_trn_id'];
					for($ik=0;$ik<count($purchase_res_stock);$ik++)
					{
						$que12="select rp_pid,purchase_unit from tbl_request_product as ta 
								where rp_id=".$POST['rp_id'];
								$rs_di11=$dbcon->query($que12);
								$re12=brp_mysqli_fetch_assoc($rs_di11);

								
							//var_dump($que12); */
						$que="select * from product_mst as ta where product_id=".$re12['rp_pid'];
								$rs_di=$dbcon->query($que);
								$re=brp_mysqli_fetch_assoc($rs_di);
								
								if($re['product_conv_unit']==$re12['purchase_unit']){
									$type="base_unit";
									$con_stock=$purchase_res_stock[$ik];
									$base_stock=convert_stock_new($dbcon,$purchase_res_stock[$ik],$re12['rp_pid'],$type);
								}else{
									$type="conv_unit";
									$base_stock=$purchase_res_stock[$ik];
									$con_stock=convert_stock_new($dbcon,$purchase_res_stock[$ik],$re12['rp_pid'],$type);
								}
					
						$info_wip_purchase['rp_id']						= $POST['rp_id'];
						$info_wip_purchase['allocate_base_qty']			= $base_stock;
						$info_wip_purchase['allocate_base_unit']		= $re['product_base_unit'];
						$info_wip_purchase['allocate_conv_qty']			= $con_stock;
						$info_wip_purchase['allocate_conv_unit']		= $re['product_conv_unit'];
						$info_wip_purchase['purchaseordertrn_id']		= $sp_purchase_trn_id[$ik];
						$info_wip_purchase['cdate']						= date("Y-m-d H:i:s");
						$info_wip_purchase['user_id']					= $_SESSION['user_id'];
						$info_wip_purchase['company_id']				= $_SESSION['company_id'];

						$reserve_wip_id=add_record('wip_purchase_stock_allocate',$info_wip_purchase, $dbcon);

					}
					
					//purchase end
					$bstock=$POST['bstock'];
					$bid=$POST['bid'];
			
					for($i=0;$i<count($bstock);$i++)
					{
						$que12="select ta.*,req.rp_pid from wip_stock_allocate as ta 
								left join tbl_request_product as req on req.rp_id=ta.rp_id
								where wip_stock_allocate_id=".$bid[$i];
								$rs_di11=$dbcon->query($que12);
								$re12=brp_mysqli_fetch_assoc($rs_di11);
							//var_dump($que12);
						$que="select * from product_mst as ta where product_id=".$re12['rp_pid'];
								$rs_di=$dbcon->query($que);
								$re=brp_mysqli_fetch_assoc($rs_di);
								
								if($re['product_conv_unit']==$re12['allocate_base_unit']){
									$type="base_unit";
									$con_stock=$bstock[$i];
									$base_stock=convert_stock_new($dbcon,$bstock[$i],$re12['rp_pid'],$type);
								}else{
									$type="conv_unit";
									$base_stock=$bstock[$i];
									$con_stock=convert_stock_new($dbcon,$bstock[$i],$re12['rp_pid'],$type);
								}
					

								
						$info_wip['rp_id']						= $re12['rp_id'];
						$info_wip['type_flag']					= $re12['type_flag'];
						$info_wip['allocate_for_rp_id']			= $POST['rp_id'];
						$info_wip['allocate_base_qty']			= $base_stock;
						$info_wip['allocate_base_unit']			= $re['product_base_unit'];
						$info_wip['allocate_conv_qty']			= $con_stock;
						//$info_wip['allocate_conv_qty_used']		= $row_rstock['rp_id'];
						$info_wip['allocate_conv_unit']			= $re['product_conv_unit'];
						$info_wip['perent_id']					= $re12['wip_stock_allocate_id'];
						$info_wip['stock_flag']					= 2;
						$info_wip['cdate']						= date("Y-m-d H:i:s");
						$info_wip['user_id']					= $_SESSION['user_id'];
						$info_wip['company_id']					= $_SESSION['company_id'];

						$reserve_wip_id=add_record('wip_stock_allocate',$info_wip, $dbcon,$re12['branch_id']);

						update_workorder_complete_qty_and_Status($dbcon,$re12['rp_id'],$bstock[$i]);

						$upd_wip_stock["allocate_base_qty_used"] = $re12['allocate_base_qty_used'] + $base_stock;
						$upd_wip_stock["allocate_conv_qty_used"] = $re12['allocate_conv_qty_used'] + $con_stock;

						$updateid=update_record("wip_stock_allocate", $upd_wip_stock,"wip_stock_allocate_id=".$re12['wip_stock_allocate_id'], $dbcon);
					}
					//process stock entry start 22-01-2022
					$process_res_stock=$POST['process_res_stock'];
					$process_id=$POST['process_id'];
					$process_godown=$POST['process_godown'];
			
					for($k=0;$k<count($process_res_stock);$k++)
					{
						
						$info_pro['rp_id']					= $POST['rp_id'];
						$info_pro['process_id']				= $process_id[$k];
						$info_pro['godown_id']				= $process_godown[$k];
						$info_pro['qty']					= $process_res_stock[$k];
						//$info_pro['unit_id']				= $re['product_base_unit'];
						$info_pro['cdate']					= date("Y-m-d H:i:s");
						$info_pro['user_id']				= $_SESSION['user_id'];
						$info_pro['company_id']				= $_SESSION['company_id'];

						if($process_res_stock[$k] > 0){
							$processstockadd_id=add_record('mrp_process_reserve_temp',$info_pro, $dbcon,$row['branch_id']);	

							if(!empty($processstockadd_id)){
								$quesx="select job_card_status from tbl_request_product as ta 
									where rp_id=".$POST['rp_id'];
								$rs_disx=$dbcon->query($quesx);
								$resx=brp_mysqli_fetch_assoc($rs_disx);
								if($resx['job_card_status']==0){
									$indent_no=load_common_no($dbcon,JOBCARD);
									update_common_no($dbcon,JOBCARD);
									$infosx['job_card_status']		= 1;
									$infosx['job_card_no']			= $indent_no;
									$infosx['job_card_date']		= date('Y-m-d');
									$updateidss=update_record("tbl_request_product", $infosx,"rp_id=".$POST['rp_id'], $dbcon);
								}
								
							}
						}
						
					}
					allocate_process_and_process_stock_reserve($dbcon,$POST['rp_id'],$extra_stock);
					//process stock entry stop 22-01-2022
				}
			}
			//var_dump($de);
			
			if($POST['process_qty']!='')
			{
				if($POST['process_qty']!="0"){
					$queryw_b="select * from pro_bom_process where process_status=0 and bom_id=".$row['bom_id'];
					$rs_custw_b=$dbcon->query($queryw_b);	
					while($relw_b=brp_mysqli_fetch_array($rs_custw_b)){
						
						//echo "<pre>"; print_r($relw_b);
						
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
						
						// $inserid_a2=add_record('tbl_wororder_product_process', $infow, $dbcon, $_POST['branch_id']);

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
							$inserid_alloc = work_order_resource_allocate($dbcon, $resource_id, $request_id, $process_id, $product_id, $qty, $time_per_qty, $edit_id, $action_type, '', $_POST['branch_id']);
							
							
						}
					}
				}
			}
			if($POST['process_qty']!='')
			{
				if($POST['process_qty']!="0"){
					$process=get_product_process($dbcon,$_POST['rp_id'],$row['rp_pid']);
					$process_pr=json_decode($process);
					//echo "<pre>"; print_r($process_pr);die;
					
					$process_id=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;
					$description=$process_pr->description;

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
					$info5['description'] = $description;

					$que_12="select * from product_mst as ta where product_id=".$row['rp_pid'];
					$rs_di_12=$dbcon->query($que_12);
					$re_12=brp_mysqli_fetch_assoc($rs_di_12);

					if($companyConfiguration['batch_wise_stock'] == '1' &&  $companyConfiguration['batch_process'] == '0' && $re_12['batch_wise_stock_manage'] == '1'){
						$info5['batch_process_start_time'] = 1;
					}

					if($resourceinfo['process_type']=='1'){			
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}
					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					
					$last_insert=add_record('tbl_allocate_process', $info5, $dbcon, $_POST['branch_id']);
					resource_schedule_assign_at_process_allocate($dbcon,$POST['rp_id'],$POST['process_qty'],$last_insert);
					
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
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '10'");	
				}
			} */
			
			if($POST['process_qty'] == '0' || $POST['process_qty'] ==""){  // remove child product 
				/*$upd_status['status'] = 2;
				$updateid=update_record("tbl_request_product", $upd_status,"perent_id=".$POST['rp_id'], $dbcon);*/

				remove_child_products($dbcon,$POST['rp_id']);
				// $deleteid=delete_record('tbl_request_product', "perent_id=".$POST['rp_id'], $dbcon);
			}else{
				/*$upd_status['status'] = 3;
				$updateid=update_record("tbl_request_product", $upd_status,"perent_id=".$POST['rp_id'], $dbcon);*/
			}
			
			
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
		}else if(brp_strtolower($POST['mode']) == "jobcard_submit_per") {
			$rp_id = $POST['rp_id'];
			$pen_req = check_child_product_requested_count($dbcon,$rp_id);
			// var_dump($pen_req);
			if($pen_req>0){
					echo "2";	
				}else{
					echo "1";
					//echo $query1;
				}
		}
		else if(strtolower($POST['mode'])== "convert_qty")
		{
			//var_dump($POST);
			$row=array();
			if($POST["type"]=="1"){
				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="conv_unit";
				$ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
			}else{
				$ret_qty="0";
			}
				//var_dump($ret_qty);
			$ret_qty_new=number_format($ret_qty, 5, ".", "");
					//$ret_qty=$ret_qty;
				//	echo $ret_qty;
			$row['show_qty']=$ret_qty_new;
			$row['hide_qty']=$ret_qty;
			echo json_encode($row);
		}
		/* START JAYESH */ 
		
		else if(brp_strtolower($POST['mode']) == "add_work_order_product") {
			
			$product_id = $_POST['product_id'];
			$qty = $_POST['qty'];
			@$sales_order_date = $_POST['sales_order_date'];
			@$sales_order_no = $_POST['sales_order_no'];
			$unit_id = $POST['unit_id'];
			if($sales_order_no !='' && $sales_order_date !='' )
			{
				$test = '<tr>
				<th colspan="3">Sales Order No : <span style="color: red;">'.@$sales_order_no.' </span></th>
				<th colspan="2">Sales Order Date: '.@$sales_order_date.' </span></th>

				</tr>';
			}
			else
			{
				$test = '';
			}
			
			
			$str.='<table class="table table-bordered">
			<tr>
			<th colspan="3">Product Name : <span style="color: red;">'.$_POST["product_name"].' </span></th>
			<th colspan="2">Qty: '.$qty.' ' . getunitname($dbcon,$unit_id) .' </span></th>

			</tr>'.$test.'						
			<tr>
			<th colspan="3" style="text-align: center;"> <strong>Product Type :</strong></th>

			<th colspan="2"><select class="select33" title="Select product" name="wo_product_type" id="wo_product_type" onchange="load_product(this.value);">'.								
			get_product_type_company($dbcon,'','').'</select>
			</th></tr>
			<tr>
			<th colspan="3" style="text-align: center;"> <strong>Product:</strong></th>

			<th colspan="2">
			<input id="wo_product_id" name="wo_product_id"  style="width:100% !important"  placeholder="Select product" onchange="check_bom_version(this.value,1);check_product_unit(this.value,1);load_product_detail(this.value);" />
			<!-- <select class="select33" title="Select product" name="wo_product_id" id="wo_product_id" onchange="check_bom_version(this.value,1);check_product_unit(this.value,1);load_product_detail(this.value);" >								
			<option value="">Choose Product</option>'.
			getproduct($dbcon,'').'</select> -->
			</th></tr>

			<tr>
			<th colspan="5"><div id="get_spec_div" style="display:none"></div></th>
			</tr>

			<tr>
			<th colspan="3" style="text-align: center;"> <strong>Bom Version:</strong></th>

			<th colspan="2"><select class="select33 selproduct1" title="Select Bom Version" name="add_bom_version_id" id="add_bom_version_id"><option selected="selected" value="10000">R&D</option></select>
			</th></tr>
			
			<tr>
			<th colspan="3" style="text-align: center;"> <strong>Qty:</strong></th>

			<th colspan="2">
			<div class="col-md-9">
			<input type="text" id="product_qty" name="product_qty" class="form-control  numbersOnly" onkeydown="return numbersOnly(event);" onkeyup="product_convert_qty(2);">
			<input type="hidden" id="product_qty_hide" value="">
			<input type="hidden" id="product_conv_qty_hide" value="">
			</div>
			<div class="col-md-3" style="margin-top:5px">
				<span style="color:green" id="pro_base_unit"></span>
			</div>
			<div class="col-md-9" style="margin-top:5px">
			<input type="text"  id="product_conv_qty" name="product_conv_qty" class="form-control  numbersOnly" onkeydown="return numbersOnly(event);" onkeyup="product_convert_qty(1);">
			</div>
			<div class="col-md-3" style="margin-top:10px">
				<span style="color:green" id="pro_conv_unit"></span>
			</div>
			<input type="hidden" id="prod_id" value="'.$product_id.'">
			<input type="hidden" id="qty" value="'.$qty.'">	
			</th>
			
			</tr>


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
			$rs=$dbcon->query("select product_type,product_base_unit,product_conv_unit from  product_mst where product_id=".$rp_pid);
			$row=brp_mysqli_fetch_array($rs);
			$product_type = $row['product_type'];
			$rp_pro_qty = $_POST['rp_pro_qty'];
			$rp_pro_conv_qty = 0;
			if($row['product_base_unit'] == $row['product_conv_unit']){
				$rp_pro_conv_qty = $rp_pro_qty;
			}else{
				$rp_pro_conv_qty=convert_stock($dbcon,$rp_pro_qty,$rp_pid,"conv_unit");
			} 
			
			
			$parent_query="select i.perent_id, i.rp_req_qty,i.rp_id,i.in_process_qty,pro.product_name,i.rp_pid,i.job_card_status from tbl_request_product as i
			left join product_mst as pro on pro.product_id=i.rp_pid where i.rp_id=".$rp_id;
			$parent_result=$dbcon->query($parent_query);
			
			if($parent_row=brp_mysqli_num_rows($parent_result) > 0 )
			{
				$parent_row=brp_mysqli_fetch_assoc($parent_result);

				 $query="select i.rp_req_qty,i.rp_id,i.in_process_qty,pro.product_name,i.rp_pid,i.job_card_status,pro.product_base_unit from tbl_request_product as i
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
			<th colspan="3">Qty: '.$qty.' ' . getunitname($dbcon,$pro_row['product_base_unit']) .' </span></th>

			</tr><tr>
			<th colspan="5" style="text-align: center;">

			<tr>
			<th colspan="2">Product Type :</th>
			<th colspan="3"><select class="select22 selproduct1" title="Select product" name="wo_product_type" id="wo_product_type" onchange="load_product(this.value)">'.								
			get_product_type_company($dbcon,$product_type,'').'</select>
			</th></tr>
			<tr>
			<th colspan="2">Product:</th>
			<th colspan="3"><select class="select22 selproduct1" title="Select product" name="wo_product_id" id="wo_product_id">'.								
			getproduct_typewise($dbcon,$rp_pid,$product_type).'</select>
			</th></tr>
			<tr>
			<th colspan="5"><div id="get_spec_div" style="display:none"></div></th>
			</tr>
			<tr>
			<th colspan="2">Qty :</th>
			<th colspan="3">	
			<div class="col-md-9">
			<input type="text" id="product_qty" name="product_qty" class="form-control numbersOnly" value="'.$rp_pro_qty.'" onkeydown="return numbersOnly(event);" onkeyup="product_convert_qty(2);">
			<input type="hidden" id="product_qty_hide"value="'.$rp_pro_qty.'">
			<input type="hidden" id="product_conv_qty_hide" value="'.$rp_pro_conv_qty.'">
			</div>
			<div class="col-md-3" style="margin-top:5px">
				<span style="color:green" id="pro_base_unit">'.getunitname($dbcon,$row['product_base_unit']) .'</span>
			</div>
			<div class="col-md-9" style="margin-top:5px">
			<input type="text" readonly id="product_conv_qty" name="product_conv_qty" class="form-control  numbersOnly" onkeydown="return numbersOnly(event);" onkeyup="product_convert_qty(1);" value="'.$rp_pro_conv_qty.'">
			</div>
			<div class="col-md-3" style="margin-top:10px">
				<span style="color:green" id="pro_conv_unit">'.getunitname($dbcon,$row['product_conv_unit']) .'</span>
			</div>
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
			// echo "<pre>";print_r($POST);die;
			$main_product_id = $POST['main_product_id'];
			$bom_version_id =$POST['bom_version_id'];
			$sp_id = $_POST['sp_id'];
			$branch_id = $POST['branch_id'];

			$product_type = $POST['wo_product_type'];
			$customer_id = $POST['customer_id'];
			$jobwork_type = $POST['jobwork_type'];
			$extra_stock = $POST['extra_stock'];
			$ext_stock_vendor_id = $POST['ext_stock_vendor_id'];


			$check_pr_type_process = check_process_product_type($dbcon,$product_type);

			$arr['process_required'] = $check_pr_type_process;
			$req_qty_one = $POST['product_qty'] / $POST['qty'];
			if($bom_version_id == "10000")
			{						
				$sp_query = "select * from  tbl_request_product where sp_id = '$sp_id'  AND main_request ='1'"; 

				$sp_rs=$dbcon->query($sp_query);
				$sp_row = brp_mysqli_fetch_array($sp_rs);
				$rp_id = $sp_row['rp_id'];
				$sp_query1 = "select * from  tbl_request_product where status !=2 and  sp_id = '$sp_id'  AND  perent_id='$rp_id'"; 
				$sp_rs1=$dbcon->query($sp_query1);

				$pro_qry = "select * from  product_mst where product_id = " . $_POST['wo_product_id']; 

				$pro_rs=$dbcon->query($pro_qry);
				$pro_row = brp_mysqli_fetch_array($pro_rs);

				$counter = brp_mysqli_num_rows($sp_rs1)+1; 

				$info2['sp_id']					= $sp_id;
				$info2['sr_no']					= $counter;
				$info2['rp_req_no']				= '';
				$info2['rp_req_date']			= date("Y-m-d");
				$info2['rp_pid']				= $_POST['wo_product_id'];
				// $info2['rp_req_qty']			= $POST['qty']* $POST['product_qty'];
				$info2['rp_req_qty']			=  $POST['product_qty'];
				$info2['req_qty_one']			= $req_qty_one;
				$info2['rp_po_qty']				= 0;
				// $info2['in_process_qty']		= $POST['qty']* $POST['product_qty'];
				// $info2['in_process_qty']		=  $POST['product_qty'];
				$info2['out_process_qty']		= '';
				$info2['rp_req_type']			= 'work_order';
				$info2['rp_po_req_no']			= '';
				$info2['rp_process_req_no']		= '';
				$info2['cdate']					= strtotime(date("Y-m-d"));
				$info2['user_id']				= $_SESSION['user_id'];		
				$info2['company_id']			= $_SESSION['company_id'];	
				$info2['status']				= 3;	
				$info2['row_cnt']				= 0;	
				$info2['process_unit']			= $pro_row['product_base_unit'];	
				$info2['purchase_unit']			= $pro_row['product_conv_unit'];	
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
				$info2['branch_id']		= $branch_id;						
				$info2['extra_stock']		= $extra_stock;												
				$info2['ext_stock_vendor_id']		= $ext_stock_vendor_id;												
				$info2['customer_id']		= $customer_id;										
				$info2['jobwork_type']		= $jobwork_type;												

				$table='tbl_request_product';	
						//echo "<pre>"; print_r($info2);die;
				$reqinserid=add_record($table, $info2, $dbcon);

				$arr['msg'] = '1';
				$arr['rp_id'] =$reqinserid;
			}
			else
			{
				
						//$row=brp_mysqli_fetch_array($rs);

				$sp_query = "select * from  tbl_request_product where status !=2 and sp_id = '$sp_id'  AND main_request ='1'"; 
				/*$sp_query = "select * from  tbl_request_product where sp_id = '$sp_id' AND perent_id='0' AND main_request !='1'"; */
				$sp_rs=$dbcon->query($sp_query);
				$counter = brp_mysqli_num_rows($sp_rs)+1;
				$sp_row = brp_mysqli_fetch_array($sp_rs);
				$rp_id = $sp_row['rp_id'];

				$branch_id = $sp_row['branch_id'];
				if($rp_id != '')
				{
					$sp_query1 = "select count(*) as sr_no from  tbl_request_product where status !=2 and perent_id = '$rp_id'";  
					$sp_rs1=$dbcon->query($sp_query1);
					$sp_rs1= brp_mysqli_fetch_array($sp_rs1);
					$counter =$sp_rs1['sr_no']+1;
					/*if(brp_mysqli_num_rows($sp_rs1)> 0)
					{
						$counter = brp_mysqli_num_rows($sp_rs1)+1;
					}
					else
					{
						$counter = brp_mysqli_num_rows($sp_rs)+1;
					}*/
				}
				else
				{
					$counter = 1;
				}
				// var_dump($counter);die;
				
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

			while($rel1=brp_mysqli_fetch_assoc($result1)){  
				$conv_one_qty=convert_stock($dbcon,$req_qty_one,$rel1['product_id'],"conv_unit");

				$base_qty=$req_qty_one*$info_su['rp_req_qty'];
				$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

				$info_sub['sp_id']				= $sp_id;
				$info_sub['sr_no']				= $counter;
				$info_sub['rp_pid']				= $bom_rel['product_id'];
				
				$info_sub['rp_req_qty']			=  $POST['product_qty'];
				$info_sub['req_qty_one']			= $req_qty_one;
				$info_sub['rp_po_qty']				= 0;
				$info_sub['rp_req_date']			= date("Y-m-d");
				
				// $info_sub['in_process_qty']		=  $POST['product_qty'];


				$info_sub['rp_req_type']		= "work_order";//type
				$info_sub['process_unit']		= $bom_rel['product_base_unit'];
				$info_sub['purchase_unit']		= $bom_rel['product_conv_unit'];
				$info_sub['perent_id']			= $rp_id;
				$info_sub['status']				= 3;
				$info_sub['user_id']			= $_SESSION['user_id'];
				$info_sub['company_id']			= $_SESSION['company_id'];
				//$info_sub['main_request']		= $POST['g_total'];
				
				$info_sub['product_version']	= $bom_rel['bom_id'];
				$info_sub['bom_id']				= $bom_rel['bom_id'];
				$info_sub['branch_id']		= $branch_id;		
				$info_sub['customer_id']		= $customer_id;										
				$info_sub['jobwork_type']		= $jobwork_type;
				$info_sub['extra_stock']		= $extra_stock;											
				$info_sub['ext_stock_vendor_id']		= $ext_stock_vendor_id;											
				
				
				$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon);

				$arr['msg'] = '1';
				$arr['rp_id'] =$inserid_sub;
					
				// $query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$bom_rel['product_id']; 

				// $rel_pro = $dbcon->query($query_pro);

				$queryw_b="select * from pro_bom_process where process_status=0 and bom_id=".$bom_rel['bom_id'] . " and product_id = " . $bom_rel['product_id'];
					$rs_custw_b=$dbcon->query($queryw_b);	
					
				if(brp_mysqli_num_rows($rs_custw_b)>0)
				{
					while($product_process=brp_mysqli_fetch_assoc($rs_custw_b))
					{

						$queryw="select * from tbl_product_process where status = 0 and  pr_process_id=".$product_process['pr_process_id'];
						$rs_custw=$dbcon->query($queryw);	
						$relw=brp_mysqli_fetch_array($rs_custw);


						$wpp_info['product_id'] = $bom_rel['product_id'];		
						$wpp_info['rp_id'] = 	$inserid_sub;
						$wpp_info['process_priority'] = 	$product_process['priority'];
						$wpp_info['process_time'] = 	$relw['process_time'];
						$wpp_info['process_type'] = 	$relw['process_type'];
						$wpp_info['process_opening'] = 	$relw['process_opening'];
						$wpp_info['process_id'] = 	$relw['process_id'];	
						$wpp_info['cdate']				= date("Y-m-d H:i:s");
						$wpp_info['user_id']			= $_SESSION['user_id'];
						$wpp_info['company_id']			= $_SESSION['company_id'];
						$wpp_info['branch_id']			= $branch_id;
						$wpp_info['description']			= $product_process['description'];

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
						$mat_data['sp_id'] = $sp_id; 
						$mat_data['rp_id'] = $inserid_sub; 
						$mat_data['product_id'] = $bom_rel['product_id']; 
						$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id']; 
						$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value']; 
						$mat_data['wo_material_trn_status'] = 0; 
						$mat_data['user_id']			= $_SESSION['user_id'];
						$mat_data['company_id']			= $_SESSION['company_id'];
						$mat_data['branch_id']			= $_SESSION['branch_id'];
						$inserid_sub_material=add_record('tbl_wo_material_trn', $mat_data, $dbcon,$branch_id);
						
					}
				}
			}
				/*   Material Formula */


				$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
				left join product_mst as pro on pro.product_id=bom_trn.product_id
				left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
				left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
				where bom_trn_status=0 and bom_id=".$bom_rel['bom_id'];	 
				$result1=$dbcon->query($query1);

				$call=1;$space="";
				$i = 1;

				while($rel1=brp_mysqli_fetch_assoc($result1)){  
					$sr_no = $counter.'.'.$i;
					$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
					$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

					$base_qty=$base_one_qty*$info_su['rp_req_qty'];
					$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

					$info_sub1['sp_id']				= $sp_id;
					$info_sub1['sr_no']				= $sr_no;
					$info_sub1['rp_pid']				= $rel1['product_id'];
					$info_sub1['rp_req_qty']			=  $base_qty;
						//$info_sub1['rp_req_qty']			= $conv_stock;//required qty
						$info_sub1['req_qty_one']		= $base_one_qty;//required qty
						$info_sub1['rp_po_qty']			= "";//po qty
						// $info_sub1['in_process_qty']		= $POST['qty']* $POST['product_qty'];//process qty
						$info_sub1['rp_req_date']			= date("Y-m-d");
						$info_sub1['rp_req_type']		= "work_order";//type
						$info_sub1['process_unit']		= $rel1['product_base_unit'];
						$info_sub1['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub1['perent_id']			= $inserid_sub;
						$info_sub1['status']				= 3;
						$info_sub1['user_id']			= $_SESSION['user_id'];
						$info_sub1['company_id']			= $_SESSION['company_id'];
						//$info_sub1['main_request']		= $POST['g_total'];
						
						$info_sub1['product_version']	= $rel1['p_bom_id'];
						$info_sub1['bom_id']				= $rel1['p_bom_id'];
						$info_sub1['customer_id']		= $customer_id;
						$info_sub1['jobwork_type']		= $jobwork_type;
						$info_sub1['extra_stock']		= $extra_stock;	
						$info_sub1['ext_stock_vendor_id']		= $ext_stock_vendor_id;	
						
						//echo "<pre>"; print_r($info_sub1);die;
						$inserid_sub1=add_record('tbl_request_product', $info_sub1, $dbcon,$branch_id);
						
						// $query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$rel1['product_id']; 
						// $rel_pro = $dbcon->query($query_pro);
						$query_pro="select * from pro_bom_process where process_status=0 and bom_id=".$rel1['p_bom_id'] . " and product_id = " . $rel1['product_id'];
						$rel_pro=$dbcon->query($query_pro);	
						if(brp_mysqli_num_rows($rel_pro)>0)
						{
							while($product_process=brp_mysqli_fetch_assoc($rel_pro))
							{
								$queryw="select * from tbl_product_process where status = 0 and  pr_process_id=".$product_process['pr_process_id'];
								$rs_custw=$dbcon->query($queryw);	
								$relw=brp_mysqli_fetch_array($rs_custw);

								$wpp_info['product_id'] = $rel1['product_id'];		
								$wpp_info['rp_id'] = 	$inserid_sub1;
								$wpp_info['process_priority'] = 	$product_process['priority'];
								$wpp_info['process_time'] = 	$relw['process_time'];
								$wpp_info['process_type'] = 	$relw['process_type'];
								$wpp_info['process_opening'] = 	$relw['process_opening'];
								$wpp_info['process_id'] = 	$relw['process_id'];	
								$wpp_info['cdate']				= date("Y-m-d H:i:s");
								$wpp_info['user_id']			= $_SESSION['user_id'];
								$wpp_info['company_id']			= $_SESSION['company_id'];
								$wpp_info['branch_id']			= $branch_id;
								$wpp_info['description']			= $product_process['description'];

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
								$mat_data['sp_id'] = $sp_id; 
								$mat_data['rp_id'] = $inserid_sub1; 
								$mat_data['product_id'] = $rel1['product_id']; 
								$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id']; 
								$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value']; 
								$mat_data['wo_material_trn_status'] = 0; 
								$mat_data['user_id']			= $_SESSION['user_id'];
								$mat_data['company_id']			= $_SESSION['company_id'];
								$mat_data['branch_id']			= $_SESSION['branch_id'];
								$inserid_sub_material=add_record('tbl_wo_material_trn', $mat_data, $dbcon,$branch_id);
								
							}
						}
						
						/*   Material Formula */

						/*bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub1,$sr_no,$conv_one_qty* $POST['product_qty'],$branch_id);*/

						bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub1,$sr_no,$POST['product_qty'],$branch_id,$rel1['product_base_qty'],$customer_id,$jobwork_type,$extra_stock,$ext_stock_vendor_id);		
						$i++;
					}	
				}

				if($arr['rp_id'] > 0){
					if($sp_row['in_process_qty'] == '0' || $sp_row['in_process_qty'] == ""){
						$upd_qty['in_process_qty'] = $sp_row['rp_po_qty'];
						$upd_qty['rp_po_qty'] = 0;
						
						$upd_qty['indent_status']		= 0;
						$upd_qty['indent_no']			= "";
						$upd_qty['indent_date']		= "";

						$job_card_no=load_common_no($dbcon,JOBCARD);
						update_common_no($dbcon,JOBCARD);
						$upd_qty['job_card_status']		= 1;
						$upd_qty['job_card_no']			= $job_card_no;
						$upd_qty['job_card_date']		= date('Y-m-d');

						$updateid=update_record("tbl_request_product", $upd_qty,"rp_id=".$sp_row['rp_id'], $dbcon);
					}
				}
			//}
				// echo "1";
				echo json_encode($arr);
			}
			else if(brp_strtolower($POST['mode']) == "add_work_order_sub_product") {

				$rp_id = $_POST['rp_id'];
				$sub_product_id = $_POST['sub_product_id'];
				$main_product_id = $_POST['main_product_id'];
				$qty = $_POST['qty'];
				$sales_order_date = $_POST['sales_order_date'];
				$sales_order_no = $_POST['sales_order_no'];
				$extra_stock = $POST['extra_stock'];
				$ext_stock_vendor_id = $POST['ext_stock_vendor_id'];
				$unit_id = $POST['unit_id'];

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

		/*	if($parent_id != 0)
			{
				$query="select i.rp_id,i.in_process_qty,pro.product_name,i.rp_pid,i.job_card_status from tbl_request_product as i left join product_mst as pro on pro.product_id=i.rp_pid
			where i.rp_id=".$parent_id;
			$result=$dbcon->query($query);
			$pro_row=brp_mysqli_fetch_assoc($result);			
			}
			else
			{
				$query="select i.rp_id,i.in_process_qty,pro.product_name,i.rp_pid,i.job_card_status from tbl_request_product as i
			left join product_mst as pro on pro.product_id=i.rp_pid
			where i.rp_id=".$rp_id;
			$result=$dbcon->query($query);
			$pro_row=brp_mysqli_fetch_assoc($result);
		}*/


		if($sales_order_no == '' && $sales_order_date == '' )
		{
			$show_so = 0;
		}
		else
		{
			$show_so = 1;
		}
			/*$q = "select * from tbl_request_product where rp_pid = '$rp_id'";
			$r=$dbcon->query($q);
			$p_row=brp_mysqli_fetch_assoc($r);
			$parent_id $p_row['perent_id'];*/
			
			
			
			
			$str.='<table class="table table-bordered">
			<tr>
			<th colspan="5" style="text-align: center;font-size: 20px;">'.$POST["product_name"].'</th>	
			</tr>
			<tr>
			<th colspan="2">Product Name : <span style="color: red;">'.$pro_row["product_name"].' </span></th>
			<th colspan="3">Qty: '.$qty.' '. getunitname($dbcon,$unit_id) .' </span></th>

			</tr>'.$test.'
			<tr>
			<tr>
			<th colspan="2" style="text-align: center;"> <strong>Product Type :</strong></th>

			<th colspan="3"><select class="select34" title="Select product" name="wo_product_type" id="wo_sub_product_type" onchange="load_product(this.value,1)">'.					
			get_product_type_company($dbcon,'','').'</select>
			</th></tr>
			<tr>
			<th colspan="2" style="text-align: center;"> <strong>Product:</strong></th>
			<th colspan="3">
			<input id="wo_sub_product_id" name="wo_sub_product_id"  style="width:100% !important"  placeholder="Select product" onchange="check_bom_version(this.value,2);check_product_unit(this.value,2);load_product_detail(this.value);" />
			<!-- <select class="select34" title="Select product" name="wo_sub_product_id" id="wo_sub_product_id" onchange="check_bom_version(this.value,2);check_product_unit(this.value,2);load_product_detail(this.value);">'.								
			getproduct($dbcon,'').'</select> -->
			</th>
			</tr>								
			<tr>
			<th colspan="2" style="text-align: center;"> <strong>Bom Version:</strong></th>

			<th colspan="3"><select class="select34" title="Select Bom Version" name="add_sub_bom_version_id" id="add_sub_bom_version_id"">


			</select>
			</th></tr>
			<tr>
			<th colspan="5"><div id="get_sub_spec_div" style="display:none"></div></th>
			</tr>
			<tr>
			<th colspan="2" style="text-align: center;"> <strong>Qty:</strong></th>
			<th colspan="2">
			<div class="col-md-9">
			<input type="text" id="sub_product_qty" name="product_qty" class="form-control  numbersOnly" onkeydown="return numbersOnly(event);" onkeyup="sub_product_convert_qty(2);">
			<input type="hidden" id="sub_product_qty_hide" value="">
			<input type="hidden" id="sub_product_conv_qty_hide" value="">
			</div>
			<div class="col-md-3" style="margin-top:5px">
				<span style="color:green" id="pro_base_unit"></span>
			</div>
			<div class="col-md-9" style="margin-top:5px">
			<input type="text"  id="sub_product_conv_qty" name="product_conv_qty" class="form-control  numbersOnly" onkeydown="return numbersOnly(event);" onkeyup="sub_product_convert_qty(1);">
			</div>
			<div class="col-md-3" style="margin-top:10px">
				<span style="color:green" id="pro_conv_unit"></span>
			</div>
			<input type="hidden" id="sub_product_id" value="'.$sub_product_id.'">
			<input type="hidden" id="main_product_id" value="'.$main_product_id.'">
			<input type="hidden" id="sub_qty" value="'.$qty.'">		
			<input type="hidden" id="rp_id" value="'.$rp_id.'">	
			</th>
			
			</tr>	
			<tr>
			<th colspan="5" style="text-align: center;">
			<button type="button" onclick="save_work_order_sub_product();" class="btn btn-success" id="save" name="save">Save</button>
			</th></tr></table>';							
			echo $str;
		}
		
		else if(brp_strtolower($POST['mode']) == "save_work_order_sub_product") {
			// echo "<pre>";print_r($_POST);die;	
			$sub_product_id = $POST['sub_product_id'];
			$main_product_id = $POST['main_product_id'];
			$qty = $POST['qty'];
			$product_qty = $POST['product_qty']; 
			$rp_id = $POST['rp_id'];
			$bom_version_id = $POST['bom_version_id'];
			$req_qty_one = $product_qty / $qty;
			$branch_id = $POST['branch_id'];
			$extra_stock = $POST['extra_stock'];
			$ext_stock_vendor_id = $POST['ext_stock_vendor_id'];

			$sp_query = "select * from  tbl_request_product where status !=2 and rp_id = '$rp_id'";
			$sp_rs=$dbcon->query($sp_query);
			$sp_row = brp_mysqli_fetch_array($sp_rs);	
						//echo "<pre>"; print_r($sp_row);
			$sp_id = $sp_row['sp_id'];	
			$sp_counter_query = "select count(rp_id) as rp_counter,perent_id from  tbl_request_product where status !=2 and perent_id = '$rp_id' ";
			$sp_counter_rs=$dbcon->query($sp_counter_query);
			$sp_counter_row = brp_mysqli_fetch_array($sp_counter_rs);


			$product_type = $POST['wo_product_type'];
			$check_pr_type_process = check_process_product_type($dbcon,$product_type);

			$arr['process_required'] = $check_pr_type_process;

			if($sp_counter_row['rp_counter'] < 1)
			{
				$sr_no = $sp_row['sr_no'].'.1';
			}
			else
			{
				$paerent_id = $sp_counter_row['perent_id'];							
				$parent_sr_query = "select * from  tbl_request_product where status !=2 and rp_id = '$paerent_id'";
				$parent_sr_res=$dbcon->query($parent_sr_query);
				$parent_sr_row = brp_mysqli_fetch_array($parent_sr_res);						
				$sp_counter_row = $sp_counter_row['rp_counter']+1;
				$sr_no = $parent_sr_row['sr_no'].'.'.$sp_counter_row;
			}
			
			if($bom_version_id == "10000" )
			{

				$pro_qry = "select * from  product_mst where product_id = '$sub_product_id'"; 

				$pro_rs=$dbcon->query($pro_qry);
				$pro_row = brp_mysqli_fetch_array($pro_rs);


				$info2['sp_id']					= $sp_id;
				$info2['sr_no']					= $sr_no;
				$info2['rp_req_no']				= '';
				$info2['rp_req_date']			= date("Y-m-d");
				$info2['rp_pid']				= $sub_product_id;
				// $info2['rp_req_qty']			= $qty*$product_qty;
				$info2['rp_req_qty']			= $product_qty;
				$info2['req_qty_one']			= $req_qty_one;
				$info2['rp_po_qty']				= 0;
				// $info2['in_process_qty']		= $qty*$product_qty;
				$info2['in_process_qty']		= $product_qty;
				$info2['out_process_qty']		= '';
				$info2['rp_req_type']			= 'work_order';
				$info2['rp_po_req_no']			= '';
				$info2['rp_process_req_no']		= '';
				$info2['cdate']					= strtotime(date("Y-m-d"));
				$info2['user_id']				= $_SESSION['user_id'];		
				$info2['company_id']			= $_SESSION['company_id'];	
				$info2['status']				= 3;	
				$info2['row_cnt']				= 0;	
				$info2['process_unit']			= $pro_row['product_base_unit'];	
				$info2['purchase_unit']			= $pro_row['product_conv_unit'];
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
				$info2['branch_id']		= $branch_id;							
				$info2['extra_stock']		= $extra_stock;							
				$info2['ext_stock_vendor_id']		= $ext_stock_vendor_id;							
				$table='tbl_request_product';

				$reqinserid=add_record($table, $info2, $dbcon);

				$arr['msg'] = '1';
				$arr['rp_id'] =$reqinserid;
			}else
			{

				$sp_counter_rs=$dbcon->query($sp_counter_query);
				$sp_counter_row = brp_mysqli_fetch_array($sp_counter_rs);

				$sp_rs=$dbcon->query($sp_query);
				$sp_row = brp_mysqli_fetch_array($sp_rs); 
				$i = 1; 
				$branch_id = $sp_row['branch_id'];

				if($sp_counter_row['rp_counter'] < 1)
				{

					$sr_no = $sp_row['sr_no'].'.1';
				}
				else
				{

					$paerent_id = $sp_counter_row['perent_id'];							
					$parent_sr_query = "select * from  tbl_request_product where status !=2 and perent_id = '$paerent_id'";
					$parent_sr_res=$dbcon->query($parent_sr_query);
					$parent_sr_row = brp_mysqli_fetch_array($parent_sr_res);						
					$sp_counter_row = $sp_counter_row['rp_counter']+$i;
					// $sr_no = $parent_sr_row['sr_no'].'.'.$sp_counter_row;
					$sr_no = $parent_sr_row['sr_no']+ 0.1;

					// // echo "===>" . $sr_no;
					// $sr_no = $parent_sr_row['sr_no'];
				}

				// echo "===>" . $sr_no;die;

				$bom_process="SELECT * FROM `tbl_bom` as bom
				left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
				WHERE  prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$POST['sub_product_id']."'";
				$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));

				$info_sub['sp_id']				= $sp_id;
				$info_sub['sr_no']				= $sr_no;
				$info_sub['rp_pid']				= $bom_rel['product_id'];
				
				$info_sub['rp_req_qty']			=  $POST['product_qty'];
				$info_sub['req_qty_one']			= $req_qty_one;
				$info_sub['rp_po_qty']				= 0;
				$info_sub['rp_req_date']			= date("Y-m-d");
				
				// $info_sub['in_process_qty']		=  $POST['product_qty'];


				$info_sub['rp_req_type']		= "work_order";//type
				$info_sub['process_unit']		= $bom_rel['product_base_unit'];
				$info_sub['purchase_unit']		= $bom_rel['product_conv_unit'];
				$info_sub['perent_id']			= $rp_id;
				$info_sub['extra_stock']			= $extra_stock;
				$info_sub['ext_stock_vendor_id']			= $ext_stock_vendor_id;
				$info_sub['status']				= 3;
				$info_sub['user_id']			= $_SESSION['user_id'];
				$info_sub['company_id']			= $_SESSION['company_id'];
				//$info_sub['main_request']		= $POST['g_total'];
				
				$info_sub['product_version']	= $bom_rel['bom_id'];
				$info_sub['bom_id']				= $bom_rel['bom_id'];
				
				
				$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$branch_id);

				$arr['msg'] = '1';
				$arr['rp_id'] =$inserid_sub;

				$queryw_b="select * from pro_bom_process where process_status=0 and bom_id=".$bom_rel['bom_id'] . " and product_id = " . $bom_rel['product_id'];
					$rs_custw_b=$dbcon->query($queryw_b);	
					
				if(brp_mysqli_num_rows($rs_custw_b)>0)
				{
					while($product_process=brp_mysqli_fetch_assoc($rs_custw_b))
					{

						$queryw="select * from tbl_product_process where status = 0 and  pr_process_id=".$product_process['pr_process_id'];
						$rs_custw=$dbcon->query($queryw);	
						$relw=brp_mysqli_fetch_array($rs_custw);


						$wpp_info['product_id'] = $bom_rel['product_id'];		
						$wpp_info['rp_id'] = 	$inserid_sub;
						$wpp_info['process_priority'] = 	$product_process['priority'];
						$wpp_info['process_time'] = 	$relw['process_time'];
						$wpp_info['process_type'] = 	$relw['process_type'];
						$wpp_info['process_opening'] = 	$relw['process_opening'];
						$wpp_info['process_id'] = 	$relw['process_id'];	
						$wpp_info['cdate']				= date("Y-m-d H:i:s");
						$wpp_info['user_id']			= $_SESSION['user_id'];
						$wpp_info['company_id']			= $_SESSION['company_id'];
						$wpp_info['branch_id']			= $branch_id;
						$wpp_info['description']			= $product_process['description'];

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
						$mat_data['sp_id'] = $sp_id; 
						$mat_data['rp_id'] = $inserid_sub; 
						$mat_data['product_id'] = $bom_rel['product_id']; 
						$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id']; 
						$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value']; 
						$mat_data['wo_material_trn_status'] = 0; 
						$mat_data['user_id']			= $_SESSION['user_id'];
						$mat_data['company_id']			= $_SESSION['company_id'];
						$mat_data['branch_id']			= $_SESSION['branch_id'];
						$inserid_sub_material=add_record('tbl_wo_material_trn', $mat_data, $dbcon,$branch_id);
						
					}
				}
				
				/*   Material Formula */


				$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
				left join product_mst as pro on pro.product_id=bom_trn.product_id
				left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
				left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
				where bom_trn_status=0 and bom_id=".$bom_rel['bom_id'];	 

				$result1=$dbcon->query($query1);
				$call=1;$space="";


				while($rel1=brp_mysqli_fetch_assoc($result1)){  
					// $sr_no = $sr_no.'.'.$i;
					$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
					$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

					/*$base_qty=$base_one_qty*$info_su['rp_req_qty'];
					$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/

					$base_qty=$base_one_qty*$qty;
					$conv_qty=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

						$info_sub['sp_id']				= $sp_id;
						$info_sub['sr_no']				= $sr_no.'.'.$i;
						$info_sub['rp_req_no']			= '';
						$info_sub['rp_req_date']		= date("Y-m-d");
						$info_sub['rp_pid']				= $rel1['product_id'];
						$info_sub['rp_req_qty']			= $base_qty;
						$info_sub['req_qty_one']		=  $base_one_qty;;//required qty
						$info_sub['rp_po_qty']			= "";//po qty
						$info_sub['in_process_qty']		= "";//process qty
						$info_sub['rp_req_type']		= "work_order";//type
						$info_sub['process_unit']		= $rel1['product_base_unit'];
						$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub['perent_id']			= $rp_id;
						$info_sub['extra_stock']			= $extra_stock;
						$info_sub['ext_stock_vendor_id']			= $ext_stock_vendor_id;
						$info_sub['status']				= 3;
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
						//echo "<pre>"; print_r($info_sub);die;
						$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$branch_id );
						
						$bomq = "select * from tbl_bomtrn where bom_id =".$rel1['p_bom_id'];
						$bomres=$dbcon->query($bomq);
						if(brp_mysqli_num_rows($bomres) > 0)
						{
							
							bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$sr_no.'.'.$i,$POST['product_qty'],$branch_id,$rel1['product_base_qty'],'','',$extra_stock,$ext_stock_vendor_id);
							}
						
						
						$i++;
					}	
				}

				// echo "1";

				echo json_encode($arr);

			}
			else if(brp_strtolower($POST['mode']) == "delete_work_order_product") {

				$rp_id = $POST['rp_id'];
				$sp_id = $POST['sp_id'];

				$parent_delete_flag = $POST['parent_delete_flag']; 
				if($parent_delete_flag == "1")
				{ 
					$perenet_ids = implode(",",delete_recurring_products($dbcon,$rp_id));
					/*$parent_sr_res=$dbcon->query("delete from  tbl_request_product where rp_id = '$rp_id' OR rp_id IN($perenet_ids)");	*/			
					$parent_sr_res=$dbcon->query("update tbl_request_product set status = 2 where rp_id = '$rp_id' OR rp_id IN($perenet_ids)");
				}
				else
				{
					// $parent_sr_res=$dbcon->query("delete from  tbl_request_product where rp_id = '$rp_id'");
					$parent_sr_res=$dbcon->query("update tbl_request_product set status = 2 where rp_id = '$rp_id'");

				}

				if(brp_mysqli_affected_rows($dbcon) > 0)
				{	
					$delete_wp_res=$dbcon->query("delete from  tbl_wororder_product_process  where rp_id = '$rp_id'");
					update_srno_recurring_products($dbcon,$sp_id);

					$qry1 = "select perent_id,main_request from tbl_request_product where rp_id = " . $rp_id;
					$result1=$dbcon->query($qry1);

					if(brp_mysqli_num_rows($result1) > 0){
						$res1=brp_mysqli_fetch_assoc($result1);

					$q2 = "select * from tbl_request_product where rp_id = ".$res1['perent_id']; 
						$rs2=$dbcon->query($q2);	
						$res2=brp_mysqli_fetch_assoc($rs2);
						$having_child = check_having_child_product($dbcon,$res2['rp_id']);
						if($res2['main_request'] == '1'){
							if(!$having_child){
								$delete_wp_res=$dbcon->query("delete from tbl_wororder_product_process where rp_id = ".$res2['rp_id']);

								$upd_qty['in_process_qty'] = 0;
								$upd_qty['rp_po_qty'] = $res2['in_process_qty'];
								$upd_qty['job_card_no'] = "";
								$upd_qty['job_card_date'] = "";
								$upd_qty['job_card_status'] = 0;
								$indent_no=load_common_no($dbcon,17);
								update_common_no($dbcon,17);
								$upd_qty['indent_status']		= 1;
								$upd_qty['indent_no']			= $indent_no;
								$upd_qty['indent_date']		= date('Y-m-d');

								$updateid=update_record("tbl_request_product", $upd_qty,"rp_id=".$res2['rp_id'], $dbcon);	

							}
						}else{
							if(!$having_child){
								if($res2['in_process_qty'] > 0){
									unrequest_product($dbcon,$res2['rp_id']);
								}
							}
						}
						
					}

					echo "1";
				}
				else
				{
					echo "0";
				}					
			}
			else if(brp_strtolower($POST['mode']) == "edit_save_work_order_product") {
				// echo "<pre>";print_r($_POST);die;
				$rp_id = $POST['rp_id'];
				$q = "select * from tbl_request_product where rp_id = '$rp_id'"; 
				$rs=$dbcon->query($q);	
				$req_qty_one = $POST['rp_product_qty'] / $POST['main_qty'];
				if(brp_mysqli_num_rows($rs) > 0 )
				{
					$info['rp_pid']	= $_POST['wo_product_id'];
					$info['rp_req_qty']	= $_POST['rp_product_qty'];
					$info['req_qty_one ']	= $req_qty_one ;

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

				$qry="SELECT * from pro_ms_bom_version where  bom_version_status = 0 and  product_id=".$POST['product_id']." ".$company_where;
				$result=$dbcon->query($qry);

				if(brp_mysqli_num_rows($result) > 0)
				{	
					while($row=brp_mysqli_fetch_assoc($result))
					{
						$str .= '<option value="'.$row['bom_version_id'].'">'.$row['version_name'].'</option>';
					}
					$str .= '<option  value="10000">R&D</option>';
					
				}
				else
				{
					$str .= '<option  value="10000">R&D</option>';
				}
				echo $str;
			}

			else if(brp_strtolower($POST['mode']) == "get_product_process_data") {
				$del_id =	delete_record('tbl_temp_process_desc' ,'1' ,$dbcon);
				if($POST['rp_id']!='')
				{
					$q = "select wp.process_id,pmst.process_name,wp.process_type,wp.rp_id,wp.description from tbl_wororder_product_process as wp
					left join process_mst as pmst on pmst.process_id=wp.process_id where  wp.product_id =".$POST['product_id']." AND wp.rp_id =".$POST['rp_id']."  order by wp.process_priority ASC";
				}
				else
				{

					$q = "select wp.process_id,wp.process_type,pmst.process_name,wp.rp_id,wp.description  from tbl_wororder_product_process as wp 
					left join process_mst as pmst on pmst.process_id=wp.process_id where  wp.product_id =".$POST['product_id']." order by wp.process_priority ASC";
				}
	
				$res_pro = $dbcon->query($q);
			
				// print_r($arr_process);

				$arr_process=brp_mysqli_fetch_all($res_pro);

				foreach($arr_process as $temp){
					$info['rp_id'] =  $temp['rp_id'];
					$info['process_id'] = $temp['process_id'];
					$info['description'] = $temp['description'];
					if($temp['description'] !=""){
						$inserestimateid=add_record('tbl_temp_process_desc', $info, $dbcon);
					}
					
				}


				$chk_process_start_qry = "SELECT IFNULL(SUM(start_qty),0) as start_qty FROM tbl_allocate_process WHERE p_status != 2 AND p_ref_id = " . $POST['rp_id'];
				$al_rw = brp_mysqli_fetch_assoc($dbcon->query($chk_process_start_qry));

				$is_process_start = $al_rw['start_qty'];

				$process_ids = "";
				$selected_process_ids = "";
				
				$selected_process_ids = implode(',', array_column($arr_process, 'process_id'));

				$multiple_value = implode(',', array_column($arr_process, 'process_id'));
	
				$query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.status = 0 and prod.product_id = ".$POST['product_id'] . " order by process_priority";

				$rel_pro = $dbcon->query($query_pro);
				$i=1;
				$str='<div class="row"><div class="col-md-6 text-center"> <h4>All Process</h4></div>
				<div class="col-md-6 text-center"><h4>Selected Process as priority</h4></div>	</div>
				<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
				<input type="hidden" name="multiple_value" id="multiple_value" value="'.$multiple_value.'"/>
				<input type="hidden" name="process_sel_product_id" id="process_sel_product_id" value="'.$POST['product_id'].'"/>
				<input type="hidden" name="selected_rp_id" id="selected_rp_id" value="'.$POST['rp_id'].'"/>
				<input type="hidden" name="selected_desc_id" id="selected_desc_id" value=""/>
';

				$str .='<div class="row">
						<div class="col-md-5">';
				if($is_process_start == '0'){
	   				$str.= '<label for="chk_leftside_process">
	        						<input type="checkbox" onClick="select_all_left_side_process()" id="chk_leftside_process" name="chk_leftside_process"/> Select All Process
	        					</label>';
				}
   				
   				 $str .= '<ul id="process_left">';

				while($product_process=brp_mysqli_fetch_assoc($rel_pro)){

					if(!in_array($product_process['process_id'],array_column($arr_process, 'process_id'))){
       					$icon = "";
        		if($product_process['process_type'] == '1'){

        			$icon = ' [inhouse] ';
        		}else{

        			$icon = ' [outside]	';
        		}

				// $str .=  '<option class="process_row '.$selected.'" data-cid="'.$i.'" value="'.$product_process['process_id'].'">' . $product_process['process_name'] . $icon .'</option>';

				  $str .= '<li class="process_row" data-cid="'.$i.'"  id="'.$product_process['process_id'].'">'.$product_process['process_name'] . $icon .'</li>';
				  $process_ids = $process_ids + ',' + $product_process['process_id'];
				  $i++;
   					 }
				
				}
				 $str .='</ul>
  </div>
    <div class="col-md-2">
      <div>
        <button id="moveRight" class="bigBtn bigBtn btn btn-primary"> > </button>  
      </div>
       <div>
      <button id="moveLeft"  class="bigBtn bigBtn btn btn-danger"> < </button>
      </div>
      
    </div>
    <div class="col-md-5">';

    if($is_process_start == '0'){
   				$str.= '<label for="chk_rightside_process">
        						<input type="checkbox" onClick="select_all_right_side_process()" id="chk_rightside_process" name="chk_rightside_process"/> Select All Process
        					</label>';
			}
	$str .='<ul id="process_right">';


foreach($arr_process as $pro){
	if($pro['process_type'] == '1'){

        			$icon = ' [inhouse] ';
        		}else{

        			$icon = ' [outside]	';
        		}
	$str .= '<li   class="process_row" data-cid="'.$i.'" id="'.$pro['process_id'].'"> '.$pro['process_name']. $icon .' </li>';
	$i++;
}
  // <li id="114"> <button style="margin-right:10px">+</button> rolling</li>
  // <li id="115"><button style="margin-right:10px">+</button>wiring </li>
  
$str .='</ul>  
  </div>
  <div class="col-md-12">
    <input type="hidden" id="process_ids" class="form-control" placeholder="All Process" value="'.$product_ids.'">
	<input type="hidden" id="selected_process_ids" class="form-control" placeholder="Selected Process" value="'.$selected_process_ids.'">
  </div>
</div>';
				$product_process = brp_mysqli_fetch_assoc($rel_pro);

				if(brp_mysqli_num_rows($rel_pro) > 0){
					if(isset($POST['direct'])){
						$function = 'direct_bom_process_add('.$POST['product_id'].','.$POST['bom_version_id'].',"direct")'; 					
					}else{
						$function = 'bom_process_add('.$POST['rp_id'].')'; 	
					}

					$str.="
					<input type='hidden' id='selected_process_id'>
					<div class='col-md-12' id='row_process_desc' style='display:none;margin-top:15px;'>
						<div class='col-md-12'>
						Description
					</div>
					<div class='col-md-12' style='padding:0px'>
					<textarea class='form-control' rows='5' id='process_desc'></textarea>
					</div>
					
					<div class='col-md-12' style='margin-top: 15px;'>
						<center>

							<button type='button' id='btProcessDesc' name='btProcessDesc' onClick='save_process_desc(".$POST['rp_id'].")' class='btn btn-success btn-space'>Save</button>
						</center>
					</div>

					</div>
					<div class='col-md-12' style='margin-top: 50px;'>
						<center>

							<button type='button' id='process_save' onClick='".$function ."' name='process_save' class='btn btn-success btn-space' >Submit</button>
						</center>
					</div>

					</form>
					";
				}else{

					$str = '<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
					<input type="hidden" name="multiple_value" id="multiple_value" value=""/>

					<div class="col-md-12" style="margin-top: 15px;">
					<h3>NO PROCESS ADDED</h3>
					<div style="display:none;">
					<textarea class="form-control" rows="5" id="process_desc" ></textarea></div>
					</div>
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
				// echo "<pre>";print_r($POST);die;
				$product_id = $POST['product_id'];
				$rp_id = $POST['rp_id'];

				$q = "select pr_process_id from tbl_wororder_product_process where rp_id = ".$POST['rp_id']." and product_id =".$POST['product_id']." order by process_priority ASC";

				$res_pro = $dbcon->query($q);
				$arr_process = brp_mysqli_fetch_all($res_pro);
				$hidden = $_POST['sel_process']; //get the values from the hidden field
				$hidden_in_array = explode(",", $hidden); //convert the values into array
		
		
				$filter_array = array_filter($hidden_in_array); //remove empty index 
				$arr_sel_process = array_values($filter_array); //reset the array key 

				/*$unsel_process = $POST['unsel_process'];
				$arr_unsel_process = explode(',',$unsel_process);
		*/
				$info['product_id'] = $product_id;		
				$info['rp_id'] = 	$POST['rp_id'];		
				$info['branch_id'] = $POST['branch_id'];		
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				
				$del_id =	delete_record('tbl_wororder_product_process' ,'rp_id = ' . $POST['rp_id'] . ' and product_id =' .$product_id ,$dbcon);
				$x = 1;
				foreach ($arr_sel_process as $process_id) {

					$p_qry = "select process_type,process_time,process_opening from tbl_product_process where status = 0 and  product_id = " . $product_id . " and process_id = " . $process_id;
					$p_pro = $dbcon->query($p_qry);
					$p_pro_row=brp_mysqli_fetch_assoc($p_pro);


					$desc_qry = "select description from tbl_temp_process_desc where rp_id = " . $POST['rp_id'] . " and process_id = " . $process_id;
					$desc_pro = $dbcon->query($desc_qry);
					$desc_row=brp_mysqli_fetch_assoc($desc_pro);


					$info['process_priority']	= $x;
					$info['process_id']	= $process_id;
					$info['process_time']	=  $p_pro_row['process_time'];
					$info['process_type']	= $p_pro_row['process_type'];
					$info['process_opening']	= $p_pro_row['process_opening'];
					$info['description']	= $desc_row['description'];
					// if(empty($POST['edit_id']) && empty($arr_process)){			
						$inserestimateid=add_record('tbl_wororder_product_process', $info, $dbcon);
					/*}else if(array_search($process_id, array_column($arr_process, 'process_id')) === false){
						
						$inserestimateid=add_record('tbl_wororder_product_process', $info, $dbcon);
					}else if(array_search($process_id, array_column($arr_process, 'process_id')) !== false){
						
						$update_info['priority'] = $x;
						$update_info['process_status'] = 0;
						$where = "product_id = " . $product_id ." AND process_id=".$process_id;
						$inserestimateid=update_record('tbl_wororder_product_process', $update_info, $where , $dbcon);	
						if($inserestimateid == 0){
							$inserestimateid = 1;
						}
					}*/
					$x++;
				}

			/*	if(!empty($POST['edit_id'])){

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

				$qry2 = "select main_request from tbl_request_product where rp_id = ". $rp_id;
				$result2=$dbcon->query($qry2);
				$res2=brp_mysqli_fetch_assoc($result2);

				if($res2['main_request'] == 1){
					$qry = "select process_id,product_id,process_type from tbl_wororder_product_process where process_priority = 1 and rp_id = ". $rp_id;
					$result=$dbcon->query($qry);
					$res=brp_mysqli_fetch_assoc($result);


					$qry1 = "select p_id,process_id,pr_process_type from tbl_allocate_process where  p_status = 0 and p_ref_id = ".$rp_id." and  p_product_id = " . $product_id . " and process_priority in(0,1) and previous_process_id = 0";
					$result1=$dbcon->query($qry1);
					$res1=brp_mysqli_fetch_assoc($result1);

					if($res1['process_id'] != $res['process_id'] && $res1['pr_process_type'] != $res['process_type']){
						$upd_ap['process_id']	= $res['process_id'];
						$upd_ap['pr_process_type'] = $res['process_type'];
						$upd_ap['process_priority'] =1;
						$updateid=update_record("tbl_allocate_process", $upd_ap,"p_id=".$res1['p_id'], $dbcon);	
					}
				}

				if($inserestimateid){
					// if(empty($POST['edit_id'])){
						$arr['msg']="1";
					/*}else{
						$arr['msg']="update";
					}*/
					$del_id =	delete_record('tbl_temp_process_desc' ,'1' ,$dbcon);

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
		
		$qry="SELECT * from pro_ms_bom_version where  bom_version_status = 0 and  product_id=".$POST['product_id']." ".$company_where." ".$branch_where;
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
		
		$qry="SELECT * from tbl_request_product where sp_id=".$POST['work_order_id']." AND main_request !='1'";
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
		$wo_type = $POST['wo_type'];
		$rp_id = $POST['rp_id'];

		if(brp_strtolower($wo_type) == "direct_jobcard"){
			$rp_id = $POST['rp_id'];
		}
		
		if(brp_strtolower($wo_type) == "direct_jobcard"){
			 $check_process_query="SELECT * from tbl_wororder_product_process  where rp_id IN (".$rp_id.")";
		}else{
			$sp_id = $_POST['sp_id'];
			$check_process_query="SELECT * from tbl_wororder_product_process  where rp_id IN (SELECT rp_id FROM `tbl_request_product` WHERE  sp_id=".$sp_id." AND main_request = '1')";
		}
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
			include($include.'pagging.php');
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
		else if(brp_strtolower($POST['mode']) == "get_current_stock_by_product") {
			$product_id = $_POST['product_id'];
			$purchase_unit = $_POST['purchase_unit'];
			$rp_id = $_POST['rp_id'];
			$actions = $_POST['actions'];			
			$str = '';
			$godown_str = '';
			$godown_total = 0;
			$workorder_str = '';
			$workorder_total = 0;
			$customer_id = $POST['customer_id'];
			
			if($customer_id != 0)
			{
				$customer_reserves_query="select * from tbl_reserve_stock where product_id=".$product_id." AND customer_id ='$customer_id'";
				$customer_reserves_result=$dbcon->query($customer_reserves_query);
				
				$reserve_counter = 0;
				if(mysqli_num_rows($customer_reserves_result)>0)
				{
					while($customer_reserves_row=brp_mysqli_fetch_assoc($customer_reserves_result))
					{
						$reserve_counter += $customer_reserves_row['base_stock'];
					}
				}
				
				$customer_stock_query="select * from tbl_stock_trn where product_id=".$product_id."  AND customer_id ='$customer_id'";
				$customer_stock_result=$dbcon->query($customer_stock_query);
				
				$stock_counter = 0;
				if(mysqli_num_rows($customer_stock_result)>0)
				{
					while($customer_stock_row=brp_mysqli_fetch_assoc($customer_stock_result))
					{
						$stock_counter += $customer_stock_row['base_stock'];
					}
				}
				
				
				
				
				$workorder_total = $reserve_counter + $stock_counter;
				
				
				
				$godown_str .='<td><input class="digitOnly godown_stock" onkeydown="return digitonly(event);" type="text"  value="'.$workorder_total.'" name="godown_stock[]" ></td>';
				
				$workorder_str .='<tr><td><b>Total</b></td><td><b>'.$workorder_total.'</b></td></table>';

				$str.="<table class='table table-bordered'>
				<tr>
				<th colspan='5' style='text-align: center;font-size: 20px;'> STOCK</th>
				
				</tr>
				<tr>
				<td colspan='5'>".$godown_str."</td>
				
				</tr>
				<tr>
				<td colspan='10' style='text-aling:center;'><input type='button' name='save' id='stock_save' class='btn btn-success' value='save' ".$onclick."  style='display: inline-block;'></td>
				
				</tr>
				</table>";
				echo $str;
			}
			else
			{
				$godown_query="select * from tbl_stock_trn as ps inner join mst_godown as g on ps.godown_id = g.gd_id   where ps.product_id=".$product_id." group by ps.godown_id Order BY product_id DESC"; 
				$godown_result=$dbcon->query($godown_query);
				$godown_str .='<table class="table table-bordered">';

				if(mysqli_num_rows($godown_result)>0)
				{

					while($godown_row=brp_mysqli_fetch_assoc($godown_result))
					{
						if($actions == "1")
						{	
							$prdouct_stock = get_current_stock_new_data($dbcon,$product_id,$purchase_unit,$godown_row['gd_id'],$customer_id);


							$gd_stock= get_current_stock_new_data($dbcon,$product_id,$purchase_unit,$godown_row['gd_id'],$customer_id);
						}
						else
						{

							$actions = "2";
							$prdouct_stock= get_current_stock_new_data($dbcon,$product_id,$purchase_unit,$godown_row['gd_id'],$customer_id);
							$reserves_query="select * from tbl_reserve_stock where product_id=".$product_id." AND request_id = ".$rp_id." AND godown_id=".$godown_row['gd_id'];
							$reserves_result=$dbcon->query($reserves_query);
							if(mysqli_num_rows($reserves_result)>0)
							{

								$reserves_row=brp_mysqli_fetch_assoc($reserves_result);
								$gd_stock = $prdouct_stock-$reserves_row['base_stock'];
								$reserve_id =  $reserves_row['reserve_id'];
							}
							else
							{


								$prdouct_stock= get_current_stock_new_data($dbcon,$pro_id,$unit_id,$godown_row['gd_id'],$customer_id);
								$godown_allocate_stock_query="select *,sum(base_stock) as total_stock from tbl_reserve_stock where product_id=".$product_id." AND godown_id=".$godown_row['gd_id'];
								$godown_allocate_stock_result=$dbcon->query($godown_allocate_stock_query);
								$godown_allocate_stock_row=brp_mysqli_fetch_assoc($godown_allocate_stock_result);
								/*$gd_stock = $godown_row['product_stock']-$godown_allocate_stock_row['total_stock'];*/
								$gd_stock = $cstock_data;
								$reserve_id = '';
							}
							$onclick =  "onclick='current_stock_save(".$rp_id.",".$product_id.",".$actions.")';";
						}
			//echo "<pre>"; print_r($godown_row);
						$godown_str .='<tr><td id='.$godown_row['gd_id'].'>'.$godown_row['gd_name'].'</td>
						<td>'.$prdouct_stock.'</td>
						<td><input class="digitOnly godown_stock" onkeydown="return digitonly(event);" type="text" id="'.$godown_row['gd_id'].'" data-main-stock="'.$prdouct_stock.'" data-stock="'.$gd_stock.'" data-reserveid="'.$reserve_id.'" placeholder="'.$gd_stock.'" value="" name="godown_stock[]" onchange="check_stock(this);"></td>
						</tr>';

						$godown_total+=$prdouct_stock;
						$onclick =  "onclick='current_stock_save(".$rp_id.",".$product_id.",".$actions.")';";
					}

					$godown_str .='<tr><td><b>Total</b></td><td><b>'.$godown_total.'</b></td></table>';
					$godown_flag = "0";
				}
				else{

					$godown_str .='<tr><td>No Record Found</td></table>';
					$godown_flag = "1";
				}	

				$product_process_query="select * from tbl_allocate_process as ap inner join process_mst as pm on ap.process_id = pm.process_id where ap.p_product_id=".$product_id." AND p_status = '1'";
				$product_process_result=$dbcon->query($product_process_query);

				$product_process_str .='<table class="table table-bordered">';
				if(mysqli_num_rows($product_process_result)>0)
				{
					$product_process_str .='<tr><td>Order Number</td><td>Process Name</td><td>Qty</td><td>allocate Qty</td></tr>';
					while($product_process_row=brp_mysqli_fetch_assoc($product_process_result))
					{ 
			/*if($workorder_row['sp_id'] == "0")
			{
				$workorder_no = $workorder_row['job_card_no'];
			}
			else
			{ */
				$main_query="select * from tbl_request_product as rp inner join  tbl_set_main_process as sm ON rp.sp_id = sm.sp_id   where rp.rp_id=".$product_process_row['p_ref_id'];
				$main_result=$dbcon->query($main_query);
				$main_row = brp_mysqli_fetch_assoc($main_result);
				
				if($workorder_row['sp_id'] == "0")
				{
					$workorder_no = $main_row['job_card_no'];
				}
				else
				{
					$workorder_no = $main_row['po_req_no'];
				}


				$product_process_str .='<tr><td id='.$workorder_no.'>'.$workorder_no.'</td>
				<td>'.$product_process_row['process_name'].'</td>
				<td>'.$product_process_row['start_qty'].'</td>
				<td><input class="digitOnly godown_stock" onkeydown="return digitonly(event);" type="text" id="'.$product_process_row['p_id'].'"  placeholder="'.$product_process_row['start_qty'].'" value="" name="godown_stock[]" onchange="check_stock(this);"></td>
				</tr>';
				$workorder_total+=$product_process_row['start_qty'];
			}

			$product_process_str .='<tr><td colspan="2"><b>Total</b></td><td></td><td><b>'.$workorder_total.'</b></td></table>';
			$product_flag = "0";
		}
		else
		{
			$product_process_str .='<tr><td>No Record Found</td></table>';
			$product_flag = "1";
		}
		

		$str.="<table class='table table-bordered'>";
		if($product_flag != 1 && $godown_flag != 1 ){
			$str.="<tr>
			<th colspan='5' style='text-align: center;font-size: 20px;'>CURRENT STOCK</th>
			<th colspan='5' style='text-align: center;font-size: 20px;'>WIP SOTCK</th>	
			</tr>
			<tr>
			<td colspan='5'>".$godown_str."</td>
			<td colspan='5'>".$product_process_str."</td>
			</tr>
			<tr>
			<td colspan='10' style='text-aling:center;'><input type='button' name='save' id='stock_save' class='btn btn-success' value='Allocate' ".$onclick."  style='display: inline-block;'></td>

			</tr>";
		}else{
			$str.="<tr><td colspan='10' style='text-aling:center;'>No Record Found !!!</td></tr>";
		}

		$str.="</table>";
		echo $str;





		
		$cdata_array = array();
		foreach($cstock_data as $cdata)
		{
			$cdata_array[] = $cdata[''];
		}

			//$cstock=get_current_stock_new_data($dbcon,$product_id,$purchase_unit);			
			//echo "<pre>"; print_r($cstock); die;
			//$rstock=reserve_stock_data($dbcon,$rel["rp_pid"],$rel["purchase_unit"]);


		$godown_query="select * from tbl_branch_product_stock as ps left join mst_godown as g ON ps.branch_id = g.gd_id  where ps.product_id=".$product_id ;
		$godown_result=$dbcon->query($godown_query);
		$godown_str .='<table class="table table-bordered">';
		while($godown_row=brp_mysqli_fetch_assoc($godown_result))
		{
			

			
			if($actions == "1")
			{				
				$cstock_data= get_current_stock_new_data($dbcon,$pro_id,$unit_id,$godown_row['gd_id']);
				$godown_allocate_stock_query="select *,sum(base_stock) as total_stock from tbl_reserve_stock where product_id=".$product_id." AND godown_id=".$godown_row['gd_id'];
				$godown_allocate_stock_result=$dbcon->query($godown_allocate_stock_query);
				$godown_allocate_stock_row=brp_mysqli_fetch_assoc($godown_allocate_stock_result);
				$gd_stock = $cstock_data;
				
				$onclick =  "onclick='current_stock_save(".$rp_id.",".$product_id.",".$actions.")';";
				
			}
			else
			{
				$actions = "2";
				$reserves_query="select * from tbl_reserve_stock where product_id=".$product_id." AND request_id = ".$rp_id." AND godown_id=".$godown_row['gd_id'];
				$reserves_result=$dbcon->query($reserves_query);
				if(mysqli_num_rows($reserves_result)>0)
				{
					$reserves_row=brp_mysqli_fetch_assoc($reserves_result);
					$gd_stock = $godown_row['product_stock']-$reserves_row['base_stock'];
					$reserve_id =  $reserves_row['reserve_id'];
				}
				else
				{
					echo $cstock_data= get_current_stock_new_data($dbcon,$pro_id,$unit_id,$godown_row['gd_id']);
					$godown_allocate_stock_query="select *,sum(base_stock) as total_stock from tbl_reserve_stock where product_id=".$product_id." AND godown_id=".$godown_row['gd_id'];
					$godown_allocate_stock_result=$dbcon->query($godown_allocate_stock_query);
					$godown_allocate_stock_row=brp_mysqli_fetch_assoc($godown_allocate_stock_result);
					/*$gd_stock = $godown_row['product_stock']-$godown_allocate_stock_row['total_stock'];*/
					$gd_stock = $cstock_data;
					$reserve_id = '';
				}
				$onclick =  "onclick='current_stock_save(".$rp_id.",".$product_id.",".$actions.")';";
				
			}
			
			$godown_str .='<tr><td id='.$godown_row['gd_id'].'>'.$godown_row['gd_name'].'</td>
			<td>'.$godown_row['product_stock']	.'</td>
			<td><input class="digitOnly godown_stock" onkeydown="return digitonly(event);" type="text" id="'.$godown_row['gd_id'].'" data-main-stock="'.$godown_row['product_stock'].'" data-stock="'.$gd_stock.'" data-reserveid="'.$reserve_id.'" placeholder="'.$gd_stock.'" value="" name="godown_stock[]" onchange="check_stock(this);"></td>
			</tr>';
			$godown_total+=$gd_stock;
		}
		
		$godown_str .='<tr><td><b>Total</b></td><td><b>'.$godown_total.'</b></td></table>';
		
		$workorder_query="select * from tbl_request_product where product_id=".$product_id;
		$workorder_result=$dbcon->query($workorder_query);
		$workorder_str .='<table class="table table-bordered">';
		while($workorder_row=brp_mysqli_fetch_assoc($workorder_result))
		{
			if($workorder_row['sp_id'] == "0")
			{
				$workorder_no = $workorder_row['job_card_no'];
			}
			else
			{
				$main_query="select * from tbl_set_main_process where sp_id=".$workorder_row['sp_id'];
				$main_result=$dbcon->query($main_query);
				$main_row = brp_mysqli_fetch_assoc($main_result);
				$workorder_no = $main_row['po_req_no'];
			}

			$workorder_str .='<tr><td id='.$workorder_no.'>'.$workorder_no.'</td>
			<td>'.$workorder_row['in_process_qty'].'</td>
			</tr>';
			$workorder_total+=$workorder_row['in_process_qty'];
		}
		
		$workorder_str .='<tr><td><b>Total</b></td><td><b>'.$workorder_total.'</b></td></table>';
		

		$str.="<table class='table table-bordered'>
		<tr>
		<th colspan='5' style='text-align: center;font-size: 20px;'>CURRENT STOCK</th>
		<th colspan='5' style='text-align: center;font-size: 20px;'>WIP SOTCK</th>	
		</tr>
		<tr>
		<td colspan='5'>".$godown_str."</td>
		<td colspan='5'>".$workorder_str."</td>
		</tr>
		<tr>
		<td colspan='10' style='text-aling:center;'><input type='button' name='save' id='stock_save' class='btn btn-success' value='save' ".$onclick."  style='display: inline-block;'></td>

		</tr>
		</table>";
		echo $str;

	}




}

else if(brp_strtolower($POST['mode']) == "save_process_desc") {
	// print_r($POST);die;
	$rp_id = $POST['rp_id'];
	$process_id = $POST['process_id'];
	$desc = $POST['desc'];

	$info['rp_id'] =$rp_id ;
	$info['process_id'] = $process_id ;
	$info['description'] = $desc;

	if(empty($POST['eid'])){
		$inserid=add_record('tbl_temp_process_desc', $info, $dbcon);
	}else{
		$inserid=update_record("tbl_temp_process_desc", $info,"id=".$POST['eid'] , $dbcon);	
		$inserid =1;	
	}
	
	
	if($inserid){
		if(!empty($POST['eid'])){
			echo 'update';
		}else{
			echo '1';	
		}
		
	}else{
		echo '0';
	}
}
else if(brp_strtolower($POST['mode']) == "get_process_desc") {
	$rp_id = $POST['rp_id'];
	$process_id = $POST['process_id'];

	$qry = "SELECT IFNULL(SUM(start_qty),0) as start_qty FROM tbl_allocate_process WHERE p_status != 2 AND p_ref_id = " . $rp_id . " and process_id = " . $process_id;
	$al_rw = brp_mysqli_fetch_assoc($dbcon->query($qry));

	$arr['is_process_start'] = $al_rw['start_qty'];
	
	$query1="select id,description from tbl_temp_process_desc where rp_id=".$rp_id." and process_id = " . $process_id;
	$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));

	if($rows){
		$arr['desc'] = $rows;
	}else{
		$arr['desc'] = "";
	}
	
	echo json_encode($arr);
}
else if(brp_strtolower($POST['mode']) == "reserve_stock_add") {
	$rp_id = $_POST['rp_id'];
	$action = $_POST['action'];
	$product_id = $_POST['product_id'];
	$godown_stock = $_POST['godown_stock'];
	$godown_ids = $_POST['godown_ids'];
	$reserveid_arr = $_POST['reserveid_arr'];
	$product_query="select * from product_mst where product_id=".$product_id;
	$product_result=$dbcon->query($product_query);
	$product_row = brp_mysqli_fetch_assoc($product_result);
	$product_base_unit = $product_row['product_base_unit'];
	$product_conv_unit = $product_row['product_conv_unit'];

	if($action == "1")
	{
		for($i=0;$i<count($godown_stock);$i++)
		{

			$info_rs['reserve_date']			= date("Y-m-d");
			$info_rs['product_id']				= $product_id;
			$info_rs['godown_id']				= $godown_ids[$i];
			$info_rs['base_unit']				= $product_base_unit	;
			$info_rs['base_stock']				= $godown_stock[$i];
					$info_rs['approve_base_stock']		= '';//required qty
					$info_rs['convert_unit']			= $product_conv_unit;//po qty
					$info_rs['convert_stock']			= $godown_stock[$i];//process qty
					$info_rs['approve_convert_stock']	= "";//type
					$info_rs['stock_flage']				= "1";
					$info_rs['request_id']				= $rp_id;
					$info_rs['ref_name']				= 'request';
					$info_rs['ref_id']					= 0;
					$info_rs['stock_status']			= 0;
					$info_rs['cdate']					= date("Y-m-d");
					$info_rs['user_id']					= $_SESSION['user_id'];
					$info_rs['company_id']				= $_SESSION['company_id'];
					$info_rs['sales_order_trn_id']		= 0;
					$info_rs['p_id']					= 0;
					$info_rs['stock_id']				= 0;
					$info_rs['used_status']				= 0;
					$info_rs['branch_id']				= $_SESSION['branch_id'];
					$inserid_sub=add_record('tbl_reserve_stock', $info_rs, $dbcon,$POST['branch_id']);
				}
			}
			else
			{	

				$existing_stock_query = "select * from tbl_reserve_stock where request_id = '$rp_id'";
				$existing_stock_result=$dbcon->query($existing_stock_query);
				$data_array = array();
				while($existing_stock_row = brp_mysqli_fetch_assoc($existing_stock_result))
				{
					$data_array[] = $existing_stock_row['reserve_id'];
				}
				
				for($j=0;$j<count($reserveid_arr);$j++)
				{
					if(in_array($reserveid_arr[$j],$data_array))
					{
						$reserved_query="select * from tbl_reserve_stock where reserve_id=".$reserveid_arr[$j];
						$reserved_result=$dbcon->query($reserved_query);
						if(mysqli_num_rows($reserved_result)>0)
						{
							$info_rs['product_id']				= $product_id;
							$info_rs['godown_id']				= $godown_ids[$j];
							$info_rs['base_stock']				= $godown_stock[$j];
							$info_rs['convert_stock']			= $godown_stock[$j];
							$updateid=update_record("tbl_reserve_stock", $info_rs,"reserve_id=".$reserveid_arr[$j] , $dbcon);					}
						}
						else
						{
							$info_rs['reserve_date']			= date("Y-m-d");
							$info_rs['product_id']				= $product_id;
							$info_rs['godown_id']				= $godown_ids[$j];
							$info_rs['base_unit']				= $product_base_unit	;
							$info_rs['base_stock']				= $godown_stock[$j];
								$info_rs['approve_base_stock']		= '';//required qty
								$info_rs['convert_unit']			= $product_conv_unit;//po qty
								$info_rs['convert_stock']			= $godown_stock[$j];//process qty
								$info_rs['approve_convert_stock']	= "";//type
								$info_rs['stock_flage']				= "1";
								$info_rs['request_id']				= $rp_id;
								$info_rs['ref_name']				= 'request';
								$info_rs['ref_id']					= 0;
								$info_rs['stock_status']			= 0;
								$info_rs['cdate']					= date("Y-m-d");
								$info_rs['user_id']					= $_SESSION['user_id'];
								$info_rs['company_id']				= $_SESSION['company_id'];
								$info_rs['sales_order_trn_id']		= 0;
								$info_rs['p_id']					= 0;
								$info_rs['stock_id']				= 0;
								$info_rs['used_status']				= 0;
								$info_rs['branch_id']				= $_SESSION['branch_id'];
								$inserid_sub=add_record('tbl_reserve_stock', $info_rs, $dbcon,$POST['branch_id']);
							}
						}
					}			
					echo "true";
					
				}
				else if(brp_strtolower($POST['mode']) == "show_stock_new") {
					$product_id=$POST['product_id'];
					$unit_id=$POST['unit_id'];
					$rp_id=$POST['rp_id'];
					$customer_id = $POST['customer_id'];
					$extra_stock = $POST['extra_stock'];
					$branch_id = $POST['branch_id'];
					$ext_stock_vendor_id = $POST['ext_stock_vendor_id'];
					$que_po="select product_base_unit,product_conv_unit,batch_wise_stock_manage from product_mst where product_id=".$product_id;
					$resi_grn=$dbcon->query($que_po);
					$re=brp_mysqli_fetch_assoc($resi_grn);
					$company_config = getCompanyConfiguration($dbcon);

					$unit_name = getunitname($dbcon,$unit_id);
					$diff_unit_name = "";

					$txt_stock_order = "";
					$function = 'onkeyup="reserve_stock_convert_qty(1);"';
					$diff_function = 'onkeyup="reserve_stock_convert_qty(2);"';
					$diff_unit_name = getunitname($dbcon,$re['product_conv_unit']);
					/*if($re['product_conv_unit'] == $re['product_base_unit']){
							$diff_unit_name = $unit_name;
					}else if($re['product_conv_unit'] == $unit_id){
						$diff_unit_name = getunitname($dbcon,$re['product_base_unit']);
					}else{
						$diff_unit_name = getunitname($dbcon,$re['product_conv_unit']);
						$function = 'onkeyup="reserve_stock_convert_qty(2);"';
						$diff_function = 'onkeyup="reserve_stock_convert_qty(1);"';
					}*/


					/*if($unit_id == $re['product_conv_unit']){
						$txt_stock_order = '
						<td>
								
									 <div class="col-md-9" >
									  	<input type="number"  title="Stock" min="0" id="diff_st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
									  </div>
									  <div class="col-md-3"  style="margin-top:10px">
									 	<span> '.$diff_unit_name.' </span>
									 </div>
									 <div class="col-md-9" style="margin-top:5px">
									 <input type="number"  title="Stock" min="0" id="st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
									 </div>
									 <div class="col-md-3" style="margin-top:5px">
									 	<span> '.$unit_name.' </span>
									 </div>
								</td>
						<td> 
									 <div class="col-md-9">
									  	<input type="number"  title="Enter Stock" min="0" id="diff_st_stock_reserve" name="st_stock_reserve"  class="form-control numbersOnly"  '.$diff_function.' />
									  </div>
									  <div class="col-md-3"  style="margin-top:10px">
									 	<span> '.$diff_unit_name.' </span>
									 </div>
									 <div class="col-md-9 "  style="margin-top:5px">
									 <input type="number"  title="Enter Stock" min="0" id="st_stock_reserve" name="st_stock_reserve" '.$function.' class="form-control numbersOnly" readonly />
									 </div>
									 <div class="col-md-3" style="margin-top:5px">
									 	<span> '.$unit_name.' </span>
									 </div>
								</td>';
					}else{*/
						$txt_stock_order = '<td>
								<div class="col-md-9">
									 <input type="number"  title="Stock" min="0" id="st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
									 </div>
									 <div class="col-md-3" style="margin-top:5px">
									 	<span> '.$unit_name.' </span>
									 </div>
									 <div class="col-md-9" style="margin-top:5px">
									  	<input type="number"  title="Stock" min="0" id="diff_st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
									  </div>
									  <div class="col-md-3"  style="margin-top:10px">
									 	<span> '.$diff_unit_name.' </span>
									 </div>
								</td>
						<td> <div class="col-md-9">
									 <input type="number"  title="Enter Stock" min="0" id="st_stock_reserve" name="st_stock_reserve" '.$function.' class="form-control numbersOnly"  />
									 </div>
									 <div class="col-md-3" style="margin-top:5px">
									 	<span> '.$unit_name.' </span>
									 </div>
									 <div class="col-md-9" style="margin-top:5px">
									  	<input type="number"  title="Enter Stock" min="0" id="diff_st_stock_reserve" name="st_stock_reserve"  class="form-control numbersOnly" '.$diff_function.' readonly/>
									  </div>
									  <div class="col-md-3"  style="margin-top:10px">
									 	<span> '.$diff_unit_name.' </span>
									 </div>
								</td>';
					/*}*/
					$query_o="select branch_id from tbl_request_product as trn
											where trn.rp_id=".$rp_id;
						
							$result_o=$dbcon->query($query_o);
							$rel_o=brp_mysqli_fetch_assoc($result_o);
							$branch_id = $rel_o['branch_id'];
	
					if($extra_stock == '1'){
						$str=' 
						<div class="col-md-12" style="font-size: 25px;"><center><strong>Warehouse Extra Stock</strong></center></div>
					<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
							<tr>';
								if($re['batch_wise_stock_manage']==1){
									$str .='<td style="font-weight: 600;">Batch No</td>';
								}
								$str .='<td style="font-weight: 600;">Stock</td>
								<td style="font-weight: 600;">Reserve Stock</td>
								<td style="font-weight: 600;">Action</td>
							</tr>
							<tr>';
								
								$str .='<td>
									<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" onchange="load_batch_extra_stock(this.value);">
									'.get_extra_stock_batch_no($dbcon,$product_id,$unit_id,$branch_id,$ext_stock_vendor_id).'
                                    </select>
								</td>';
								
							
								$str .='<td>
									 <input type="number"  title="Stock" min="0" id="st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />

								</td>
								<td>
									 <input type="number"  title="Enter Stock" min="0" id="st_stock_reserve" name="st_stock_reserve"  class="form-control numbersOnly"  />
								</td>
								<td>
									 <input type="button"  name="addrow" id="addrow" onClick="return add_extra_reserve_temp();"  class="btn btn-primary" value="Add"/>
								</td>
							</tr>
						</table>
						<input type="hidden" name="batch_wise_stock_manage" id="batch_wise_stock_manage" value="'.$re['batch_wise_stock_manage'].'" />
						<div id="sale_productdata"></div>';

						$str .='<div class="col-md-12" >
							<center>
								 <input type="button"  name="" id="" onClick="return add_product_request_extra('.$rp_id.');"  class="btn btn-primary" value="Save"/>
							</center>
						</div>
						';
					}else{	
					
					$str=' 
						<div class="col-md-12" style="font-size: 25px;"><center><strong>Warehouse Stock</strong></center></div>
					<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
							<tr>
								<td style="font-weight: 600;">Warehouse</td>';
								if($re['batch_wise_stock_manage']=='1' && $company_config['wo_bw_alloc_stock'] == '1'){
									$str .='<td style="font-weight: 600;">Batch No</td>';
								}
								$str .='<td style="font-weight: 600;">Stock</td>
								<td style="font-weight: 600;">Reserve Stock</td>
								<td style="font-weight: 600;">Action</td>
							</tr>
							<tr>';
								if($re['batch_wise_stock_manage']=='1' && $company_config['wo_bw_alloc_stock'] == '1'){
								$str .='<td>
									<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();load_batch_no();">
                                        '.load_available_stock_godown($dbcon,$product_id,$branch_id).'
                                    </select>
								</td>
								<td>
									<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" onchange="load_godown_wise_stock();">
                                    </select>
								</td>';
								}else{
								$str .='<td>
									<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();">
                                        '.load_available_stock_godown($dbcon,$product_id,$branch_id).'
                                    </select>
								</td>
								';
							}
								
								$str .= $txt_stock_order;
								$str .='<td>
									 <input type="button"  name="addrow" id="addrow" onClick="return add_reserve_temp();"  class="btn btn-primary" value="Add"/>
								</td>
							</tr>
						</table>
						<input type="hidden" name="batch_wise_stock_manage" id="batch_wise_stock_manage" value="'.$re['batch_wise_stock_manage'].'" />
						<div id="sale_productdata"></div>

						<div class="col-md-12" style="font-size: 25px;"><center><strong>WIP Stock</strong></center></div>';
						
						$query="select IFNULL(sum(trn.allocate_base_qty-trn.allocate_base_qty_used),0) as stock_qty,wip.indent_no,wip.indent_date,wip.job_card_no,wip.job_card_date,setp.po_req_date,setp.po_req_no,trn.type_flag,trn.wip_stock_allocate_id from wip_stock_allocate as trn
								left join tbl_request_product as wip on wip.rp_id=trn.rp_id
								left join tbl_set_main_process as setp on setp.sp_id=wip.sp_id
								where trn.status=0 and trn.branch_id=".$rel_o['branch_id']." and cast(allocate_base_qty AS DECIMAL(10,5)) > cast(allocate_base_qty_used AS DECIMAL(50,5)) and setp.sp_status = 0 and trn.stock_flag = 1 and trn.company_id=".$_SESSION['company_id']." and wip.rp_pid=".$product_id."";
				
						$result=$dbcon->query($query);
						if(mysqli_num_rows($result)>0)
						{
							$str .='<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
							<tr>
								<td style="font-weight: 600;">Indent/Job Card/Work Order</td>
								<td style="font-weight: 600;">Date</td>
								<td style="font-weight: 600;">Pending Qty</td>
								<td style="font-weight: 600;">Reserve Stock</td>
							</tr>';

							$i=1;
							while($rel=brp_mysqli_fetch_assoc($result))
							{
								$stockqty=$rel['stock_qty'];
										
								if($stockqty>0){
										
										if($rel['type_flag']==1){
											$refno=$rel['indent_no'];
											$refdate=$rel['indent_date'];
										}else if($rel['type_flag']==2){
											$refno=$rel['job_card_no'];
											$refdate=$rel['job_card_date'];
										}else if($rel['type_flag']==3){
											$refno=$rel['po_req_no'];
											$refdate=$rel['po_req_date'];
										}

										$str .='<tr>
										<td style="font-weight: 600;">'.$refno.'</td>
										<td style="font-weight: 600;">'.$refdate.'</td>
										<td style="font-weight: 600;">'.$stockqty.'</td>
										<td style="font-weight: 600;">
											 <input type="number"  title="Enter Stock" min="0" max="'.$stockqty.'" id="wip_stock_reserve'.$rel['wip_stock_allocate_id'].'" name="wip_stock_reserve[]"  class="form-control numbersOnly wip_res_stock"  />
											 <input type="hidden" class="wip_stock_id" name="wip_stock_allocate_id[]" id="wip_stock_allocate_id'.$rel['wip_stock_allocate_id'].'" value="'.$rel['wip_stock_allocate_id'].'" />
										</td>
									</tr>';
								}
							}
							$str .='</table>';
						}

						//process stock entry start 22-1-2022 pathik 
						// $query_pro="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn  where trn.rp_id=".$rp_id;

						$query_pro="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn 
                                               where trn.process_priority != (select process_priority from  tbl_wororder_product_process where rp_id=".$rp_id." order by process_priority desc limit 1) and trn.rp_id=".$rp_id;
						
							$result_pro=$dbcon->query($query_pro);
							$cnt_process=mysqli_num_rows($result_pro);
							if($cnt_process>0){
								$rel_pro=brp_mysqli_fetch_assoc($result_pro);

								$str .='
									<div class="col-md-12" style="font-size: 25px;"><center><strong>Process Stock</strong></center></div>
								<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
								<tr>
									<td style="font-weight: 600;">Process Name</td>
									<td style="font-weight: 600;">Godown</td>
									<td style="font-weight: 600;">Stock</td>
									<td style="font-weight: 600;">Reserve Stock</td>
								</tr>';
							$query_sto="select IFNULL(sum(base_stock),0) as stockqty,pmst.process_name,msgo.gd_name,trn.godown_id,trn.process_id from tbl_process_stock_trn as trn
											left join process_mst as pmst on pmst.process_id=trn.process_id
											left join mst_godown as msgo on msgo.gd_id=trn.godown_id
									where trn.stock_status=0 and trn.branch_id=".$rel_o['branch_id']." and trn.stock_flage=1 and trn.company_id=".$_SESSION['company_id']." and trn.product_id=".$product_id." and trn.process_id in (".$rel_pro['process_id_rp'].") group by trn.process_id,trn.godown_id order by ref_id";
									
										$j=1;
										$result_sto=$dbcon->query($query_sto);
										while($rel_sto=brp_mysqli_fetch_assoc($result_sto)){

											$query_res="select IFNULL(sum(base_stock),0) as used_stockqty from tbl_process_reserve_stock as trn
												where trn.stock_status=0 and stock_flage=1 and trn.company_id=".$_SESSION['company_id']." and trn.product_id=".$product_id." and process_id=".$rel_sto['process_id']." and godown_id=".$rel_sto['godown_id'];
												
													$result_res=$dbcon->query($query_res);
													$rel_res=brp_mysqli_fetch_assoc($result_res);

													$process_stock=$rel_sto['stockqty']-$rel_res['used_stockqty'];
											if($process_stock>0){
												$str .='<tr>
													<td style="font-weight: 600;">'.$rel_sto["process_name"].'</td>
													<td style="font-weight: 600;">'.$rel_sto["gd_name"].'</td>
													<td style="font-weight: 600;">'.$process_stock.'</td>
													<td style="font-weight: 600;">
														 <input type="number"  title="Enter Stock" min="0" max="'.$process_stock.'" id="sp_process_stock'.$j.'" name="sp_process_stock[]"  class="form-control numbersOnly sp_process_stock"  />

														 <input type="hidden" class="sp_process_id" name="sp_process_id[]" id="sp_process_id'.$j.'" value="'.$rel_sto['process_id'].'" />

														 <input type="hidden" class="sp_godown_id" name="sp_godown_id[]" id="sp_godown_id'.$j.'" value="'.$rel_sto['godown_id'].'" />
													</td>
												</tr>';
												$j++;
											}
											
										}
								$str .="</table>";
								
							}
							

						//process stock entry stop 22-1-2022 pathik

							//purchase stock entry start 24-1-2023 pathik 
						
								$str .='
									<div class="col-md-12" style="font-size: 25px;"><center><strong>Purchase Stock</strong></center></div>
								<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
								<tr>
									<td style="font-weight: 600;">Purchase Order</td>
									<td style="font-weight: 600;">Vendor Name</td>
									<td style="font-weight: 600;">Stock</td>
									<td style="font-weight: 600;">Reserve Stock</td>
								</tr>';
							$query_sto2="select IFNULL(wip_po_stock-wip_po_used_stock,0) as pobase_stock,purchaseordertrn_id,led.l_name,req.purchaseorder_no from tbl_purchaseordertrn as hsn
					            left join tbl_purchaseorder as req on req.purchaseorder_id=hsn.purchaseorder_id
					            left join tbl_ledger as led on led.l_id=req.vender_id
					        where purchaseordertrn_status=0 and hsn.purchaseorder_id!=0 and req.po_approval_status=1 and unit_id=".$unit_id." and wip_po_stock>wip_po_used_stock and product_id=".$product_id;
									
										$jp=1;
										$result_sto2=$dbcon->query($query_sto2);
										while($rel_sto2=brp_mysqli_fetch_assoc($result_sto2)){

											if($rel_sto2['pobase_stock']>0){
												$str .='<tr>
													<td style="font-weight: 600;">'.$rel_sto2["purchaseorder_no"].'</td>
													<td style="font-weight: 600;">'.$rel_sto2["l_name"].'</td>
													<td style="font-weight: 600;">'.$rel_sto2['pobase_stock'].'</td>
													<td style="font-weight: 600;">
														 <input type="number"  title="Enter Stock" min="0" max="'.$rel_sto2['pobase_stock'].'" id="sp_purchase_stock'.$jp.'" name="sp_purchase_stock[]"  class="form-control numbersOnly sp_purchase_stock"  />

														 <input type="hidden" class="sp_purchase_trn_id" name="sp_purchase_trn_id[]" id="sp_purchase_trn_id'.$jp.'" value="'.$rel_sto2['purchaseordertrn_id'].'" />

													</td>
												</tr>';
												$jp++;
											}
											
										}
									$query_sto3="select IFNULL(wip_po_stock_conv-wip_po_used_stock_conv,0) as pobase_stock,purchaseordertrn_id,led.l_name,req.purchaseorder_no from tbl_purchaseordertrn as hsn
					            left join tbl_purchaseorder as req on req.purchaseorder_id=hsn.purchaseorder_id
					            left join tbl_ledger as led on led.l_id=req.vender_id
					        where purchaseordertrn_status=0 and hsn.purchaseorder_id!=0 and req.po_approval_status=1 and conv_unit_id=".$unit_id." and hsn.unit_id!=hsn.conv_unit_id and wip_po_stock>wip_po_used_stock and product_id=".$product_id;
									
										$result_sto3=$dbcon->query($query_sto3);
										while($rel_sto3=brp_mysqli_fetch_assoc($result_sto3)){

											if($rel_sto3['pobase_stock']>0){
												$str .='<tr>
													<td style="font-weight: 600;">'.$rel_sto3["purchaseorder_no"].'</td>
													<td style="font-weight: 600;">'.$rel_sto3["l_name"].'</td>
													<td style="font-weight: 600;">'.$rel_sto3['pobase_stock'].'</td>
													<td style="font-weight: 600;">
														 <input type="number"  title="Enter Stock" min="0" max="'.$rel_sto3['pobase_stock'].'" id="sp_purchase_stock'.$jp.'" name="sp_purchase_stock[]"  class="form-control numbersOnly sp_purchase_stock"  />

														 <input type="hidden" class="sp_purchase_trn_id" name="sp_purchase_trn_id[]" id="sp_purchase_trn_id'.$jp.'" value="'.$rel_sto3['purchaseordertrn_id'].'" />

													</td>
												</tr>';
												$jp++;
											}
											
										}
								$str .="</table>";
								
							
							

						//purchase stock entry stop 25-1-2023 pathik


						$str .='<div class="col-md-12" >
							<center>
								 <input type="button"  name="" id="" onClick="return add_product_request('.$rp_id.');"  class="btn btn-primary" value="Save"/>
							</center>
						</div>
						';
					}

					echo $str;
				}
				else if(brp_strtolower($POST['mode']) == "default_godown_stock_reserve") {

					$product_id=$POST['product_id'];
					$unit_id=$POST['unit_id'];
					$rp_id=$POST['rp_id'];
					$customer_id = $POST['customer_id'];
					$extra_stock = $POST['extra_stock'];
					$branch_id = $POST['branch_id'];
					$ext_stock_vendor_id = $POST['ext_stock_vendor_id'];
					$godown_id = $POST['default_godown_id'];

					$reserve_qty = $POST['res_qty'];


					$gstock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,$godown_id,$branch_id,"",$customer_id);

					$rstock=reserve_stock($dbcon,$product_id,$unit_id,"","","","",$branch_id,0,"",$godown_id,"",$customer_id);

					$pending_stock = $gstock - $rstock;
					
					if($pending_stock > 0){
						$info['status']=2;	
						$updateid=update_record("work_order_reserve_temp", $info, "status = 0 and rp_id=".$rp_id, $dbcon);
						$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i 
							where stock_status=0 and i.branch_id=".$branch_id." and stock_flage=1 and  cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))  and product_id = ".$product_id." and i.godown_id=".$godown_id;
							$result_dstock=$dbcon->query($query_dstock);
						while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
							if($row_dstock['convert_unit']==$unit_id){
								$st_pending_stock=$row_dstock['pending_conv_stock'];
							}else{
								$st_pending_stock=$row_dstock['pending_base_stock'];	
							}
							if($reserve_qty>0){
								if($st_pending_stock>=$reserve_qty){
									$rqty=$reserve_qty;
									$reserve_qty=$reserve_qty-$reserve_qty;
								}else{
									$rqty=$st_pending_stock;
									$reserve_qty=$reserve_qty-$st_pending_stock;
								}

								$info1['rp_id']				= $rp_id;
								$info1['reserve_qty']		= $rqty;
								$info1['unit_id']			= $unit_id;
								$info1['godown_id']			= $godown_id;
								$info1['product_id']		= $product_id;
								$info1['stock_id']			= $row_dstock['stock_id'];
								$info1['cdate']				= date('Y-m-d H:i:s');
								$info1['user_id']			= $_SESSION['user_id'];	
								$info1['company_id']		= $_SESSION['company_id'];	
								$info1['customer_id']		= $customer_id;	
								
								$inserpoid=add_record('work_order_reserve_temp',$info1, $dbcon);

								if($inserpoid){
									$res['msg'] = '1';
								}
							}

						}

					}else{
						$res['msg'] = '-1';
					}
					echo json_encode($res);
				}
				else if(brp_strtolower($POST['mode']) == "godown_stock") {
					$gstock=0;$rstock=0;$stock=0;
					$diff_gstock=0;$diff_rstock=0;$diff_stock=0;
					$batch_id=$POST['batch_id'];
					$customer_id=$POST['customer_id'];
					$branch_id = $POST['branch_id'];


					$query = "SELECT product_base_unit,product_conv_unit FROM product_mst WHERE product_id = " . $POST['product_id'];
					$row = brp_mysqli_fetch_assoc($dbcon->query($query));

					$gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$POST['unit_id'],$POST['st_godown_id'],$branch_id,$batch_id,$customer_id);

					$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id,$customer_id);
					

					$stock=$gstock-$rstock;
					$res['stock'] = $stock;
					$diff_stock = 0;
					if($row['product_conv_unit'] == $row['product_base_unit']){
						$diff_stock = $stock;	
					}else if($POST['unit_id'] == $row['product_conv_unit']){
						$diff_gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$row['product_base_unit'],$POST['st_godown_id'],$branch_id,$batch_id,$customer_id);

						$diff_rstock=reserve_stock($dbcon,$POST['product_id'],$row['product_base_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id,$customer_id);

						$diff_stock=$diff_gstock-$diff_rstock;
					}else{
						$diff_gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$row['product_conv_unit'],$POST['st_godown_id'],$branch_id,$batch_id,$customer_id);

						$diff_rstock=reserve_stock($dbcon,$POST['product_id'],$row['product_conv_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id,$customer_id);

						$diff_stock=$diff_gstock-$diff_rstock;
					}
					$res['diff_stock'] = $diff_stock;	
					
					echo json_encode($res);
				}
				else if(brp_strtolower($POST['mode']) == "get_batch_stock") {
					$gstock=0;
					$extra_stock_id=$POST['stock_id'];
					$branch_id = $POST['branch_id'];
					$product_id = $POST['product_id'];
					$unit_id = $POST['unit_id'];

					$qry = "select base_unit,conv_unit,IFNULL(SUM(base_qty)-SUM(used_base_qty),0) as base_stock,IFNULL(SUM(conv_qty)-SUM(used_conv_qty),0) as conv_stock from smpl_extra_stock where status = 0 and extra_stock_id in(".$extra_stock_id.")";
					$row = brp_mysqli_fetch_assoc($dbcon->query($qry));
					if($unit_id == $row['conv_unit']){
						$stock=$row['conv_stock'];
					}else{
						$stock=$row['base_stock'];
					}

					$qry1 = "select IFNULL(sum(reserve_qty),0) as reserve_qty from work_order_extra_reserve_temp where status = 0 and extra_stock_id in(".$extra_stock_id.")";
					$row1 = brp_mysqli_fetch_assoc($dbcon->query($qry1));
					$res_stock = $row1['reserve_qty'];
					
					echo floatval($stock-$res_stock);
				}
				else if(brp_strtolower($POST['mode']) == "fieldadd") {
					$info1['rp_id']				= $POST['rp_id'];
					$info1['reserve_qty']		= $POST['st_stock_reserve'];
					$info1['unit_id']			= $POST['unit_id'];
					$info1['godown_id']			= $POST['st_godown_id'];
					$info1['product_id']		= $POST['product_id'];
					$info1['stock_id']			= $POST['st_stock_id'];
					
					$info1['cdate']				= date('Y-m-d H:i:s');
					$info1['user_id']			= $_SESSION['user_id'];	
					$info1['company_id']		= $_SESSION['company_id'];	
					$info1['customer_id']		= $POST['customer_id'];	

					$inserpoid=add_record('work_order_reserve_temp',$info1, $dbcon);

					if($inserpoid){
						echo 1;
					}
				}
				else if(brp_strtolower($POST['mode']) == "extra_fieldadd") {
					$info1['rp_id']				= $POST['rp_id'];
					$info1['reserve_qty']		= $POST['st_stock_reserve'];
					$info1['unit_id']			= $POST['unit_id'];
					$info1['product_id']		= $POST['product_id'];
					$info1['extra_stock_id']	= $POST['st_stock_id'];
					$info1['batch_no']			= $POST['batch_no'];
					$info1['cdate']				= date('Y-m-d H:i:s');
					$info1['user_id']			= $_SESSION['user_id'];	
					$info1['company_id']		= $_SESSION['company_id'];	
					// $info1['customer_id']		= $POST['customer_id'];	
					$info1['branch_id']		= $POST['branch_id'];	

					$inserpoid=add_record('work_order_extra_reserve_temp',$info1, $dbcon);

					if($inserpoid){
						echo 1;
					}
				}
				else if(strtolower($POST['mode']) == "load_tempoutward") {
					$company_config = getCompanyConfiguration($dbcon);
				
					$query="select trn.work_order_reserve_temp_id,trn.reserve_qty,cat.gd_name,uns.unit_name,st.batch_no from work_order_reserve_temp as trn
					left join mst_godown as cat on cat.gd_id=trn.godown_id
					left join unit_mst as uns on uns.unitid=trn.unit_id
					left join tbl_stock_trn as st on st.stock_id=trn.stock_id
						where trn.status=0 and trn.rp_id=".$POST['rp_id'];
				
			//echo $query;
			$result=$dbcon->query($query);
			echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
			<th class="text-center" width="10%">Warehouse</th>';
			if($POST['batch_wise_stock_manage']=='1' && $company_config['wo_bw_alloc_stock'] == '1'){
				echo '<th class="text-center"width="15%">Batch No</th>';
			}
			echo '<th class="text-center"width="15%">Reserve Stock</th>
			<th class="text-center"width="10%">Action</th>
			</tr>';

			//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;$total=0;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					
					echo '<tr id="fieldtr'.$i.'">
					<td style="vertical-align:top;" class="text-left">
					'.$rel['gd_name'].'
					</td>';				
					if($POST['batch_wise_stock_manage']==1 && $company_config['wo_bw_alloc_stock'] == '1'){
						echo '<td style="vertical-align:top;" class="text-left">
					'.$rel['batch_no'].'
					</td>';
					}
					echo '<td style="vertical-align:top;" class="text-center">
					'.$rel['reserve_qty'].' '.$rel['unit_name'].'
					</td>					
					
					<td style="vertical-align:top">

					<!--<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['purchaseordertrn_id'].',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>-->

					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_stock('.$rel['work_order_reserve_temp_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
					</td>	
					</tr>';
					$total=$total+$rel['reserve_qty'];
					$i++;
				}
			}

			else{
				echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table> 
					<input type="hidden" name="gstock_total" id="gstock_total" value="'.$total.'" />
				</div>
			</div>';
		}
		else if(strtolower($POST['mode']) == "load_tempoutward_extra") {
		
			$query="select trn.wo_reserve_temp_id,trn.reserve_qty,uns.unit_name, trn.batch_no from work_order_extra_reserve_temp as trn
					left join unit_mst as uns on uns.unitid=trn.unit_id
						where trn.status=0 and trn.rp_id=".$POST['rp_id'];
				
			//echo $query;
			$result=$dbcon->query($query);
			echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">';
			if($POST['batch_wise_stock_manage']==1){
				echo '<th class="text-center"width="15%">Batch No</th>';
			}
			echo '<th class="text-center"width="15%">Reserve Stock</th>
			<th class="text-center"width="10%">Action</th>
			</tr>';

			//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;$total=0;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					
					echo '<tr id="fieldtr'.$i.'">';				
					if($POST['batch_wise_stock_manage']==1){
						echo '<td style="vertical-align:top;" class="text-left">
					'.$rel['batch_no'].'
					</td>';
					}
					echo '<td style="vertical-align:top;" class="text-center">
					'.$rel['reserve_qty'].' '.$rel['unit_name'].'
					</td>					
					
					<td style="vertical-align:top">

					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_extra_stock('.$rel['wo_reserve_temp_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
					</td>	
					</tr>';
					$total=$total+$rel['reserve_qty'];
					$i++;
				}
			}

			else{
				echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table> 
					<input type="hidden" name="gstock_total" id="gstock_total" value="'.$total.'" />
				</div>
			</div>';
		}
		else if(strtolower($POST['mode'])== "delete_data_stock")
		{
			$row=array();
				$info['status']=2;	
			$updateid=update_record("work_order_reserve_temp", $info, "work_order_reserve_temp_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_data_stock_extra")
		{
			$row=array();
				$info['status']=2;	
			$updateid=update_record("work_order_extra_reserve_temp", $info, "wo_reserve_temp_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}

		else if(strtolower($POST['mode'])== "load_batch_no")
		{
			$godwn_id=$POST['godwn_id'];
			$product_id=$POST['product_id'];
			$customer_id=$POST['customer_id'];
			$unit_id = $POST['unit_id'];
			$branch_id = $POST['branch_id'];

			$unitname = getunitname($dbcon,$unit_id);

			/*$query="select batch_no,stock_id from tbl_stock_trn as trn
						where trn.stock_status=0 and stock_flage=1 and branch_id = ".$branch_id." and product_id=".$product_id." and trn.godown_id=".$godwn_id." and cast(base_stock AS DECIMAL(50,5)) > cast(used_base_stock AS DECIMAL(50,5))";*/

			$query="select i.*,(IFNULL(sum(base_stock),0)-IFNULL(sum(used_base_stock),0)) as pending_base_stock,(IFNULL(sum(convert_stock),0)-IFNULL(sum(used_convert_stock),0)) as pending_conv_stock,group_concat(i.stock_id) as b_stock_id from tbl_stock_trn as i
			where stock_status != 2 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and branch_id = ".$branch_id." and  product_id = ".$product_id." and godown_id=".$godwn_id." and batch_no != '' group by batch_no";
			$rs_batch=$dbcon->query($query);			
				
			//echo $query;
			$str="";
			$result=$dbcon->query($query);
			if(brp_mysqli_num_rows($result)>0)
			{	
				$str .= '<option value="">Select Batch Data</option>';
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					if($rel['pending_base_stock'] > 0){
						// $str.= '<option value="'.$rel['b_stock_id'].'" data-stock="'.$rel['base_stock'].'" >'.$rel['batch_no'].'</option>';
						$str .= '<option value="'.$rel['b_stock_id'].'" data-stock="'.$rel['pending_base_stock'].'" >'.$rel['batch_no'].' - (' . $rel['pending_base_stock'] . ' '. $unitname . ')</option>';
					}
					/*$gstock=0;$rstock=0;
					$batch_id=$POST['stock_id'];
					
					$gstock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,$godwn_id,$branch_id,$batch_id,$customer_id);

					$rstock=reserve_stock($dbcon,$product_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);


					$stock=$gstock-$rstock;

					$str .= '<option value="'.$rel['stock_id'].'">'.$rel['batch_no'].' - (' . $stock . ' '. $unitname . ')</option>';*/
				}
			}else{
				$str .= '<option value="">No Batch Data !!</option>';
			}

			echo $str;
		}
		else if(strtolower($POST['mode'])== "product_lead_time")
		{
			$info['product_lead_time']=$POST['product_lead_time'];
			$product_id=$POST['product_id'];
			$updateid=update_record("product_mst", $info,"product_id=".$product_id , $dbcon);
		}
		else if(strtolower($POST['mode'])== "unrequest_product")
		{
			$rp_id = $POST['rp_id'];
			unrequest_product($dbcon,$rp_id);

			$qry = "SELECT perent_id as rp_id FROM tbl_request_product WHERE rp_id = " . $rp_id;
			$row = brp_mysqli_fetch_assoc($dbcon->query($qry));
			
			$is_child_requested = check_child_product_requested_or_not($dbcon,$row['rp_id']);
				// var_dump(expression)
			if($is_child_requested>0){
				echo "1";	
			}else{
				echo "0";
			}
			
		}else if(strtolower($POST['mode'])== "check_main_product_process_allocation")
		{
			$wo_type = $POST['wo_type'];
			$rp_id = $POST['rp_id'];
			$sp_id = $POST['sp_id'];
			
			if(brp_strtolower($wo_type) == "direct_jobcard"){
				$qry = "select process_id,product_id,process_type from tbl_wororder_product_process where process_priority = 1 and rp_id = ". $rp_id;
				$result=$dbcon->query($qry);
				$res=brp_mysqli_fetch_assoc($result);


				$qry1 = "select count(p_id) as process_start from tbl_allocate_process where  p_status in(1,3) and p_ref_id = ".$rp_id." and process_id = ". $res['process_id'] . " and p_product_id = " . $res['product_id'] . " and pr_process_type=" . $res['process_type'];
				$result1=$dbcon->query($qry1);
				$res1=brp_mysqli_fetch_assoc($result1);

				if($res1['process_start'] > 0){
					echo "1";
				}else{
					echo "0";
				}

			}else{
				$qry2 = "select rp_id from tbl_request_product where status !=2 and sp_id='".$sp_id."' AND main_request = '1'";
				$result2=$dbcon->query($qry2);
				$res2=brp_mysqli_fetch_assoc($result2);

				$rp_id = $res2['rp_id'];

				$qry = "select process_id,product_id,process_type from tbl_wororder_product_process where process_priority = 1 and rp_id = ". $rp_id;
				$result=$dbcon->query($qry);
				$res=brp_mysqli_fetch_assoc($result);

				$qry1 = "select count(p_id) as process_start from tbl_allocate_process where  p_status in(1,3) and p_ref_id = '".$rp_id."' and process_id = '". $res['process_id'] . "' and p_product_id = '" . $res['product_id'] . "' and pr_process_type='" . $res['process_type']."'";
				
				$result1=$dbcon->query($qry1);
				$res1=brp_mysqli_fetch_assoc($result1);

				if($res1['process_start'] > 0){
					echo "1";
				}else{
					echo "0";
				}
			}
			
		}else if(brp_strtolower($POST['mode']) == "add_product_request_extra") {
			$extra_stock = $POST['extra_stock'];
			$query="select * from tbl_request_product as i
			where i.rp_id=".$POST['rp_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);

			if($POST['convtype']=="conv_unit"){
				$rresqty=$POST['res_qty_conv'];
			}else{
				$rresqty=$POST['res_qty'];
			}

			$query_1="select * from tbl_request_product as i
			where i.rp_id='".$row['perent_id']."'";
			$result_1=$dbcon->query($query_1);
			$row_1=brp_mysqli_fetch_assoc($result_1);

			$parent_req_qty = $row_1['rp_req_qty'];

			$info['rp_req_date']		=date('Y-m-d');
			$info['rp_req_qty']			=$POST['req_qty'];
			$info['rp_po_qty']			=$POST['po_qty'];
			$info['in_process_qty']		=$POST['process_qty'];
			$info['reserve_stock']		=$rresqty;
			$info['rp_po_base_qty']		=$POST['rp_po_base_qty'];
			$info['in_process_conv_qty']=$POST['in_process_conv_qty'];
			$info['status']				=0;
			$info['cdate']				=date('Y-m-d H:i:s');
			$info['user_id']			=$_SESSION['user_id'];
			$info['company_id']			=$_SESSION['company_id'];
						
			$updateid=update_record("tbl_request_product", $info,"rp_id=".$POST['rp_id'] , $dbcon, $_POST['branch_id']);

			$info_ext['extra_stock_material_reserve'] = 1;
			$updateid1=update_record("tbl_allocate_process", $info_ext,"p_status = 0 and p_ref_id=".$row['perent_id'], $dbcon, $_POST['branch_id']);
			
			if($POST['res_qty']!="0"){
				if($POST['res_qty']!=""){
				 	$query_rstock="select * from work_order_extra_reserve_temp as i
							where i.status=0 and i.rp_id=".$POST['rp_id'];
							$result_rstock=$dbcon->query($query_rstock);
					while($row_rstock=brp_mysqli_fetch_assoc($result_rstock)){
						$reserve_qty=$row_rstock['reserve_qty'];
						$batch_where="";
						if(!empty($row_rstock['extra_stock_id'])){
							$batch_where=" and i.extra_stock_id in(".$row_rstock['extra_stock_id'].")";
						}

						$query_dstock="select i.*,(base_qty-used_base_qty) as pending_base_stock,(conv_qty-used_conv_qty) as pending_conv_stock from smpl_extra_stock as i

							where status=0 and i.branch_id=".$row['branch_id']." and cast(base_qty AS DECIMAL(50,5))>cast(used_base_qty AS DECIMAL(50,5)) ".$batch_where." and product_id = ".$row_rstock['product_id'];
							$result_dstock=$dbcon->query($query_dstock);
						while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
							if($row_dstock['conv_unit']==$row_rstock['unit_id']){
								$pending_stock=$row_dstock['pending_conv_stock'];
							}else{
								$pending_stock=$row_dstock['pending_base_stock'];	
							}
							if($reserve_qty>0){
								if($pending_stock>=$reserve_qty){
									$rqty=$reserve_qty;
									$reserve_qty=$reserve_qty-$reserve_qty;
								}else{
									$rqty=$pending_stock;
									$reserve_qty=$reserve_qty-$pending_stock;
								}

								$que="select * from product_mst as ta where product_id=".$row_rstock['product_id'];
								$rs_di=$dbcon->query($que);
								$re=brp_mysqli_fetch_assoc($rs_di);
								
								if($re['product_conv_unit']==$row_rstock['unit_id']){
									$type="base_unit";
									$con_stock=$rqty;
									$base_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
								}else{
									$type="conv_unit";
									$base_stock=$rqty;
									$con_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
								}

								update_workorder_complete_qty_and_Status($dbcon,$row_rstock['rp_id'],$rqty);
								$wo_res_temp_info['status'] = 3;                                
                                $updatetrnid=update_record('work_order_extra_reserve_temp',$wo_res_temp_info,"wo_reserve_temp_id=".$row_rstock['wo_reserve_temp_id'] , $dbcon);

								$used_base_stock=$row_dstock['used_base_qty']+$base_stock;
								$used_convert_stock=$row_dstock['used_conv_qty']+$con_stock;
								
								$info_stock['used_base_qty'] = $used_base_stock;
								$info_stock['used_conv_qty'] = $used_convert_stock;
								
								$updatetrnid=update_record('smpl_extra_stock',$info_stock,"extra_stock_id=".$row_dstock['extra_stock_id'] , $dbcon);
							}
						}
					}

					/*allocate_process_and_process_stock_reserve($dbcon,$POST['rp_id'],$extra_stock);
					//process stock entry stop 22-01-2022*/
				}
			}
			
			if($POST['process_qty'] == '0' || $POST['process_qty'] ==""){  // remove child product 
				$upd_status['status'] = 2;
				$updateid=update_record("tbl_request_product", $upd_status,"perent_id=".$POST['rp_id'], $dbcon);
			}else{

			}
			$q=$dbcon->query("select IFNULL(group_concat(rp_id),0) as trn_ids from tbl_request_product where status!=2 and perent_id=".$POST['rp_id']."");
			$q_rel=brp_mysqli_fetch_assoc($q);
			$resp['trn_ids']=$q_rel['trn_ids'];
			$resp['insert_id']=$POST['rp_id'];
			echo json_encode($resp);
		}else if(brp_strtolower($POST['mode']) == "view_workorder_image") {
			$work_order_id = $POST['work_order_id'];

			$qry="SELECT * FROM `tbl_workorder_attachments` Where status = 0 and `company_id`='".$_SESSION['company_id']."' AND sp_id = " . $work_order_id;

				$result=$dbcon->query($qry);

				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th class="text-center" width="10%">SR No.</th>
								<th class="text-center" width="25%">Image Name</th>
								<th class="text-center" width="25%">View</th>
								<th class="text-center" width="25%">Action</th>
							</tr>';
							
							//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{

					$exts = array('gif', 'png', 'jpg'); 
					if(in_array(end(explode('.', $rel['file_name'])), $exts)){

						$filetype = '<a href="'.ROOT.'view/upload/workorder_attachmen/'.$rel["file_name"].'" target="_blank"><img src="'.ROOT.'view/upload/workorder_attachmen/'.$rel["file_name"].'" class="img-thumbnail" width="70" height="70"></a>';
					}else{
						$filetype = '<a href="'.ROOT.'view/upload/workorder_attachmen/'.$rel["file_name"].'" target="_blank">Download File</a>';
					}	
					
				 echo '<tr id="fieldtr'.$i.'">
						
						<td style="vertical-align:top;" class="text-center">
							'.$i.'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$rel['image_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>

						<td style="vertical-align:top;" class="text-center">
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_image('.$rel['attach_id'].');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>
						</td>					
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr class="text-center"><td colspan="4" style="text-align:center">NO DATA FOUND</td></tr>';
			}

		}else if (brp_strtolower($POST['mode']) == "view_document_data") {
	$bom_id = $POST['bom_id'];
	$bom_version_id = $POST['bom_version_id'];

	 $qry="SELECT * FROM `tbl_bom_documents` WHERE status =  0 and bom_id = ". $bom_id ." AND bom_version_id = ". $bom_version_id ." and `company_id`= ".$_SESSION['company_id'];

	$result=$dbcon->query($qry);

	echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
				<tr id="field">
					<th class="text-center" width="10%">SR No.</th>
					<th class="text-center" width="25%">Document Name</th>
					<th class="text-center" width="25%">View</th>
					
				</tr>';
							
							//echo $query;
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{

					$exts = array('gif', 'png', 'jpg'); 
					if(in_array(end(explode('.', $rel['file_name'])), $exts)){

						$filetype = '<a href="'.ROOT.'view/upload/bom_documents/'.$rel["file_name"].'" target="_blank"><img src="'.ROOT.'view/upload/bom_documents/'.$rel["file_name"].'" class="img-thumbnail" width="70" height="70"></a>';
					}else{
						$filetype = '<a href="'.ROOT.'view/upload/bom_documents/'.$rel["file_name"].'" target="_blank">Download File</a>';
					}	
					
				 echo '<tr id="fieldtr'.$i.'">
						
						<td style="vertical-align:top;" class="text-center">
							'.$i.'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$rel['image_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>

						</td>					
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr class="text-center"><td colspan="4" style="text-align:center">NO DATA FOUND</td></tr>';
			}

}else if(brp_strtolower($POST['mode']) == "save_workorder_image") {
			
			$work_order_id = $POST['work_order_id'];

			$cnt=count($_FILES['workorder_file']['name']);
	
			if(!empty($_FILES['workorder_file']['tmp_name'])) {
				$rand=rand(0,999999);
				$test = explode('.', $_FILES["workorder_file"]["name"]);
				$ext = brp_strtolower(end($test));
				$name = $work_order_id.'_'.$rand. '.' . $ext;
				$path='../../../view/upload/workorder_attachmen/';

				if (!file_exists($path)) {
						    mkdir($path, 0777, true);
						}
				$location = $path . $name;  

				
				move_uploaded_file($_FILES["workorder_file"]["tmp_name"], $location);

				$img_info['sp_id'] 	= $work_order_id;
				$img_info['image_name'] = $POST['image_name'];
				$img_info['file_name'] 	= $name;
				$img_info['file_path'] 	= $path;
				$img_info['user_id']		= $_SESSION['user_id'];
				$img_info['company_id']	= $_SESSION['company_id'];
				$img_info['cdate']		= date('Y-m-d H:i:s');
				$img_info['status'] = 0;
							

				$inserid = add_record('tbl_workorder_attachments', $img_info, $dbcon);
				if($inserid){
					echo "1";
				}else{
					echo "0";
				}
			}else{
				echo "-1";
			}
	
		}else if(strtolower($POST['mode']) == "delete_image") {
			$id	= $POST['id'];

			$image_de = "select * from tbl_workorder_attachments where attach_id=".$id;
			$result = $dbcon->query($image_de);
			$row = brp_mysqli_fetch_array($result);

			unlink($row['file_path'].$row['file_name']);

			$sql = "UPDATE tbl_workorder_attachments SET status = 2 WHERE attach_id='".$id."' ";	
			$updatetrancationid = $dbcon->query($sql);		
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}else if(brp_strtolower($POST['mode']) == "check_child_product_requested") {
			$rp_id = $POST['rp_id'];
			$sp_id = $POST['sp_id'];

			$query = "select IFNULL(reserve_stock,0) as reserve_qty  FROM tbl_request_product where status = 0 and rp_id = '" . $rp_id."'";
			$result = $dbcon->query($query);
			if(brp_mysqli_num_rows($result) > 0){
				$row = brp_mysqli_fetch_assoc($result);
				if(!empty($row['reserve_qty']) && $row['reserve_qty'] > 0){
						echo "2";
				}else{
					$is_child_requested = check_child_product_requested_or_not($dbcon,$rp_id);
				
					if($is_child_requested>0){
						echo "1";	
					}else{
						echo "0";
					}
				}
			}else{
				echo "0";
			}
		}else if(brp_strtolower($POST['mode']) == "check_unrequest_child") {
			$rp_id = $POST['rp_id'];
			$is_child_requested = check_child_product_requested_or_not($dbcon,$rp_id);
			if($is_child_requested>0){
				echo "1";	
			}else{
				echo "0";
			}
		}
		else if(brp_strtolower($POST['mode']) == "show_process_stock") {
			$sp_id = $POST['sp_id'];
			$rp_id = $POST['rp_id'];

			$query_o="select * from tbl_request_product as trn
											where trn.rp_id=".$rp_id;
						
			$result_o=$dbcon->query($query_o);
			$rel_o=brp_mysqli_fetch_assoc($result_o);
			$product_id = $rel_o['rp_pid'];
			$branch_id = $rel_o['branch_id'];


			$str = '<div class="row  text-center" style="background-color: #eee; padding:5px">
						<h3 style="color:green">Reserve Quantity : <span id="process_show_res_qty">'.$rel_o['rp_req_qty'].'</span> <span id="process_res_unit_name_model" style="margin-left:5px;"> '.getunitname($dbcon,$rel_o['process_unit']).' </span> </h3>
					</div>
					<input type="hidden" name="process_req_qty_one_model" id="process_req_qty_one_model" value="'.$rel_o['req_qty_one'].'">
					<input type="hidden" name="process_res_qty_model" id="process_res_qty_model" value="'.$rel_o['req_qty_one'].'>				
					<input type="hidden" name="process_rp_id_model" id="process_rp_id_model" value="'.$rp_id.'>	
					<input type="hidden" name="process_branch_id_model" id="process_branch_id_model" value="'.$rel_o['branch_id'].'>	
					<input type="hidden" name="process_product_id_model" id="process_product_id_model" value="'.$product_id.'>	
					<input type="hidden" name="process_unit_id_model" id="process_unit_id_model" value="'.$rel_o['process_unit'].'>	
					';
	
			//process stock entry start 22-1-2022 pathik 
			// $query_pro="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn where trn.rp_id=".$rp_id;
			$query_pro="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn where trn.rp_id=".$rp_id." and process_priority != ( select max(process_priority) from tbl_wororder_product_process where trn.rp_id=".$rp_id.") order by process_priority";
					
			$result_pro=$dbcon->query($query_pro);
			$cnt_process=mysqli_num_rows($result_pro);
			if($cnt_process>0){
				$rel_pro=brp_mysqli_fetch_assoc($result_pro);

				$str .='<div class="col-md-12 mtop20" style="font-size: 25px;">
							<center><strong>Process Stock</strong></center>
						</div>
						<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
							<tr>
								<td style="font-weight: 600;">Process Name</td>
								<td style="font-weight: 600;">Godown</td>
								<td style="font-weight: 600;">Stock</td>
								<td style="font-weight: 600;">Reserve Stock</td>
							</tr>';
				$query_sto="select IFNULL(sum(base_stock),0) as stockqty, pmst.process_name, msgo.gd_name, trn.godown_id, trn.process_id from tbl_process_stock_trn as trn
					left join process_mst as pmst on pmst.process_id=trn.process_id
					left join mst_godown as msgo on msgo.gd_id=trn.godown_id
					where trn.stock_status=0 and trn.branch_id=".$rel_o['branch_id']." and trn.stock_flage=1 and trn.company_id=".$_SESSION['company_id']." and trn.product_id=".$product_id." and trn.process_id in (".$rel_pro['process_id_rp'].") group by trn.process_id,trn.godown_id order by ref_id";
									
				$j=1;
				$result_sto=$dbcon->query($query_sto);
				while($rel_sto=brp_mysqli_fetch_assoc($result_sto)){

					$query_res="select IFNULL(sum(base_stock),0) as used_stockqty from tbl_process_reserve_stock as trn
						where trn.stock_status=0 and stock_flage=1 and trn.company_id=".$_SESSION['company_id']." and trn.product_id=".$product_id." and process_id=".$rel_sto['process_id']." and godown_id=".$rel_sto['godown_id'];
												
					$result_res=$dbcon->query($query_res);
					$rel_res=brp_mysqli_fetch_assoc($result_res);

					$process_stock=$rel_sto['stockqty']-$rel_res['used_stockqty'];
					if($process_stock>0){
						$str .='<tr>
									<td style="font-weight: 600;">'.$rel_sto["process_name"].'</td>
									<td style="font-weight: 600;">'.$rel_sto["gd_name"].'</td>
									<td style="font-weight: 600;">'.$process_stock.'</td>
									<td style="font-weight: 600;">
										<input type="number"  title="Enter Stock" min="0" max="'.$process_stock.'" id="res_process_stock'.$j.'" name="res_process_stock[]"  class="form-control numbersOnly res_process_stock" onkeyup="change_process_stock_qty('.$j.')" />

										<input type="hidden" class="res_process_id" name="res_process_id[]" id="res_process_id'.$j.'" value="'.$rel_sto['process_id'].'" />

										<input type="hidden" class="res_godown_id" name="res_godown_id[]" id="res_godown_id'.$j.'" value="'.$rel_sto['godown_id'].'" />
									</td>
								</tr>';
						$j++;
					}
											
				}
				$str .="</table>";
								
			}

		//process stock entry stop 22-1-2022 pathik
				$str .='<div class="col-md-12 mtop20">
							<center>
								 <input type="button"  name="" id="" onClick="return add_process_reserve_stock('.$rp_id.','.$rel_o['rp_req_qty'].');"  class="btn btn-primary" value="Save"/>
							</center>
						</div>
						';
				echo $str;
		}else if(brp_strtolower($POST['mode']) == "save_process_stock") {
			/*echo "<pre>";
			print_r($POST);die;*/

			//process stock entry start 22-01-2022
					$process_res_stock=$POST['process_res_stock'];
					$process_id=$POST['process_id'];
					$process_godown=$POST['process_godown'];
			
					for($k=0;$k<count($process_res_stock);$k++)
					{

						$info_pro['rp_id']					= $POST['rp_id'];
						$info_pro['process_id']				= $process_id[$k];
						$info_pro['godown_id']				= $process_godown[$k];
						$info_pro['qty']					= $process_res_stock[$k];
						//$info_pro['unit_id']				= $re['product_base_unit'];
						$info_pro['cdate']					= date("Y-m-d H:i:s");
						$info_pro['user_id']				= $_SESSION['user_id'];
						$info_pro['company_id']				= $_SESSION['company_id'];


						if($process_res_stock[$k] > 0){

							$allo_upd_info['p_status'] = 2;
							$updateidss = update_record("tbl_allocate_process", $allo_upd_info,"p_ref_id = ".$POST['rp_id'], $dbcon);

							$perenet_ids = implode(",",delete_recurring_products($dbcon,$POST['rp_id']));
							
							$parent_sr_res=$dbcon->query("update tbl_request_product set status = 2 where rp_id = ".$POST['rp_id']." OR rp_id IN(".$perenet_ids.")");

							$rp_qty_info['rp_req_qty']	= $process_res_stock[$k];
							$rp_qty_info['in_process_qty']	= $process_res_stock[$k];
							$rp_qty_info['reserve_stock']	= $process_res_stock[$k];
							$rp_qty_info['status']	= 0;
							$updateidss = update_record("tbl_request_product", $rp_qty_info,"rp_id = ".$POST['rp_id'], $dbcon);

							if($POST['sp_id'] > 0){	
								$sp_qty_info['rp_req_qty']	= $process_res_stock[$k];
								$sp_qty_info['in_process_qty_main']	= $process_res_stock[$k];
								$updateidss = update_record("tbl_set_main_process", $sp_qty_info,"sp_id = ".$POST['sp_id'], $dbcon);	
							}
							

							$processstockadd_id=add_record('mrp_process_reserve_temp',$info_pro, $dbcon,$row['branch_id']);	

							if(!empty($processstockadd_id)){
								$quesx="select job_card_status,sp_id,sales_order_trn_id from tbl_request_product as ta 
									where rp_id=".$POST['rp_id'];
								$rs_disx=$dbcon->query($quesx);
								$resx=brp_mysqli_fetch_assoc($rs_disx);

								if($resx['sales_order_trn_id'] > 0){
									$so_pro_query = "SELECT * FROM tbl_sales_order_production_trn where sales_order_production_status = 0 AND request_id = " . $POST['rp_id'] . " and sales_ordertrn_id = " . $resx['sales_order_trn_id'];
									$so_pro_rw = brp_mysqli_fetch_assoc($dbcon->query($so_pro_query));

									$so_pro_info['product_qty'] =  $so_pro_rw['product_qty'] - $process_res_stock[$k];

									$updateidss = update_record("tbl_sales_order_production_trn", $so_pro_info,"sales_order_production_trn_id = ".$so_pro_rw['sales_order_production_trn_id'], $dbcon);	
								}
								if($resx['job_card_status']==0){
									$indent_no=load_common_no($dbcon,JOBCARD);
									update_common_no($dbcon,JOBCARD);
									$infosx['rp_req_qty']		= $process_res_stock[$k];
									$infosx['job_card_status']		= 1;
									$infosx['job_card_no']			= $indent_no;
									$infosx['job_card_date']		= date('Y-m-d');
									$updateidss=update_record("tbl_request_product", $infosx,"rp_id=".$POST['rp_id'], $dbcon);
								}
								
							}
						}
						
					}
					allocate_process_and_process_stock_reserve($dbcon,$POST['rp_id'],$extra_stock);
					//process stock entry stop 22-01-2022
		}
		else if(brp_strtolower($POST['mode']) == "unreserve_process_stock") {
			$sp_id = $POST['sp_id'];
			$rp_id = $POST['rp_id'];

			$process_id = 0;

			$qry = "SELECT * FROM tbl_allocate_process where p_status !=2 AND p_ref_id = " . $rp_id;
			$result = $dbcon->query($qry);
			while ($row = brp_mysqli_fetch_assoc($result)) {
				$p_id_info['p_status'] = 2;
				$updateid=update_record("tbl_allocate_process", $p_id_info,"p_id=".$row['p_id'] , $dbcon);	

				$pro_st_qry = "SELECT process_reserve_id,process_stock_id,base_stock, conv_stock,process_id FROM tbl_process_reserve_stock WHERE stock_flage = 1 and stock_status != 2 and  p_id =" . $row['p_id'];
				$pro_result = $dbcon->query($pro_st_qry);

				while($row1 = brp_mysqli_fetch_assoc($pro_result)){

					$process_id = $row1['process_id'];
					$res_stock['stock_status'] = 2;

					$updateid=update_record("tbl_process_reserve_stock", $res_stock,"process_reserve_id=".$row1['process_reserve_id'] , $dbcon);

					$qry11 = "SELECT used_base_stock,used_convert_stock FROM tbl_process_stock_trn where process_stock_id = " . $row1['process_stock_id'];

					$row_111 = brp_mysqli_fetch_array($dbcon->query($qry11));

					$stock_trn['used_base_stock'] = $row_111['used_base_stock'] - $row1['base_stock'];
					$stock_trn['used_convert_stock'] = $row_111['used_convert_stock'] - $row1['conv_stock'];

					$updateid=update_record("tbl_process_stock_trn", $stock_trn,"process_stock_id=".$row1['process_stock_id'] , $dbcon);
				}
			}

			$rp_qry = "SELECT reserve_stock,process_unit,rp_req_qty FROM tbl_request_product WHERE rp_id = " . $rp_id;
			$rp_row = brp_mysqli_fetch_assoc($dbcon->query($rp_qry));

			$qty = $rp_row['rp_req_qty'];

			$info_pro['rp_id']					= $rp_id;
			$info_pro['process_id']				= $process_id;
			$info_pro['unit_id']				= $rp_row['process_unit'];
			$info_pro['qty']					= $rp_row['reserve_stock'];
			$info_pro['cdate']					= date("Y-m-d H:i:s");
			$info_pro['user_id']				= $_SESSION['user_id'];
			$info_pro['company_id']				= $_SESSION['company_id'];
			$info_pro['branch_id']				= $rp_row['branch_id'];
			
			$processstockadd_id=add_record('mrp_process_unreserved_log',$info_pro, $dbcon);	

			$dbcon->query("update tbl_request_product set reserve_stock = 0 where rp_id = '$rp_id'");	


			$perenet_ids = implode(",",delete_recurring_products($dbcon,$rp_id));
			$parent_sr_res=$dbcon->query("update tbl_request_product set status = 0 where rp_id = '$rp_id' OR rp_id IN($perenet_ids)");	

			recalculate_request_qty($dbcon,$rp_id,$qty);
		}
		else if(brp_strtolower($POST['mode']) == "get_process_remark") {
			$rp_id = $POST['rp_id'];
				
			$query = "SELECT rp.product_remark,pro.product_desc from tbl_request_product as rp 
			left join product_mst as pro on pro.product_id = rp.rp_pid
			WHERE rp_id =" . $rp_id;
			$rw = brp_mysqli_fetch_assoc($dbcon->query($query));	

			$remark = "";

			if(!empty($rw['product_remark'])){
				$remark = $rw['product_remark'];
			}else if(!empty($rw['product_desc'])){
				$remark = $rw['product_desc'];
			}

			echo $remark;
			
		}else if(brp_strtolower($POST['mode']) == "save_process_remark") {
			$rp_id = $POST['rp_id'];
			$remark = $POST['remark'];

			$info['product_remark'] = $remark;
			$updateid=update_record("tbl_request_product", $info,"rp_id=".$rp_id, $dbcon);
			if($updateid){
				echo "1";
			}else{
				echo "0";
			}
		}
		

		function recalculate_request_qty($dbcon,$rp_id,$qty){
		   $rp_qry = "SELECT rp_id,rp_req_qty,req_qty_one,rp_po_qty,in_process_qty FROM tbl_request_product WHERE perent_id = " . $rp_id;
			$result = $dbcon->query($rp_qry);
			$count = brp_mysqli_num_rows($result);
			if($count > 0){
				while($rp_row = brp_mysqli_fetch_assoc($result)){
					$info = array();

					$rp_qty = $rp_row['req_qty_one'] * $qty;
					
					$info['rp_req_qty'] = $rp_qty;
					/*if(!empty($rp_row['rp_po_qty']) && $rp_row['rp_po_qty'] > 0){
						$info['rp_po_qty']	= $rp_qty;
					}
					if(!empty($rp_row['in_process_qty']) && $rp_row['in_process_qty'] > 0){
						$info['in_process_qty']	= $rp_qty;
					}*/

					$updateid1=update_record("tbl_request_product", $info,"rp_id=".$rp_row['rp_id'], $dbcon);
					recalculate_request_qty($dbcon,$rp_row['rp_id'],$rp_qty);
				}
			}
		}
		

		function unrequest_product($dbcon,$rp_id){
			$info['status'] = 3;
			$info['indent_no'] = 3;
			$info['indent_date'] = "";
			$info['indent_status'] = 0;
			$info['job_card_no'] = '';
			$info['job_card_date'] = '';
			$info['job_card_status'] = 0;
			// $info['rp_po_qty'] = '';
			// $info['in_process_qty'] = '';
			$info['reserve_stock'] = '';
			$info['reserve_base_stock'] = '';

			$updateid=update_record("tbl_request_product", $info,"rp_id=".$rp_id , $dbcon);
			undo_remove_child_products($dbcon,$rp_id);
			$qry = "select * from wip_stock_allocate where stock_flag = 2 and  status = 0 and allocate_for_rp_id = " . $rp_id;
			$result=$dbcon->query($qry);
			while($res=brp_mysqli_fetch_assoc($result)){
				$qry1 = "select * from wip_stock_allocate where status = 0 and wip_stock_allocate_id = " . $res['perent_id'];
				$result1=$dbcon->query($qry1);
				$res1=brp_mysqli_fetch_assoc($result1);

			 	$upd_wip_stock['allocate_base_qty_used'] = $res1['allocate_base_qty_used'] - $res['allocate_base_qty'];
				$upd_wip_stock['allocate_conv_qty_used'] = $res1['allocate_conv_qty_used'] - $res['allocate_conv_qty'];

				$updateid1=update_record("wip_stock_allocate", $upd_wip_stock,"wip_stock_allocate_id=".$res1['wip_stock_allocate_id'], $dbcon);
			}

			$wp_allo_stock['status'] = 2;
			$updateid2=update_record("wip_stock_allocate", $wp_allo_stock,"allocate_for_rp_id=".$rp_id." and stock_flag = 2" , $dbcon);

			$qry1 = "select * from tbl_reserve_stock where stock_flage = 1 and  stock_status = 0 and request_id = " . $rp_id;
			$result1=$dbcon->query($qry1);	
			
			while($res1=brp_mysqli_fetch_assoc($result1)){
				$qry2 = "select * from tbl_stock_trn where stock_status = 0 and stock_id = " . $res1['stock_id'];
				$result2=$dbcon->query($qry2);
				$res2=brp_mysqli_fetch_assoc($result2);

			 	$stock_trn['used_base_stock'] = $res2['used_base_stock'] - $res1['base_stock'];
				$stock_trn['used_convert_stock'] = $res2['used_convert_stock'] - $res1['convert_stock'];

				$updateid1=update_record("tbl_stock_trn", $stock_trn,"stock_id=".$res2['stock_id'], $dbcon);
			}


			$res_stock['stock_status'] = 2;
			$updateid3=update_record("tbl_reserve_stock", $res_stock,"request_id=".$rp_id." and ref_name='wo_allocate' and stock_flage = 1" , $dbcon);
			$updateid5=update_record("tbl_process_reserve_stock", $res_stock,"ref_id=".$rp_id." and ref_name='direct process stock allocate' and stock_flage = 1" , $dbcon);
			$res_stock_mrp['status'] = 2;
			$updateid5=update_record("mrp_process_reserve_temp", $res_stock_mrp,"rp_id=".$rp_id, $dbcon);

			$wp_temp_stock['status'] = 2;
			$updateid4=update_record("work_order_reserve_temp", $wp_temp_stock,"rp_id=".$rp_id, $dbcon);

			$allo_pro['p_status'] = 2;
			$updateid4=update_record("tbl_allocate_process", $allo_pro,"p_ref_id=".$rp_id, $dbcon);

			$qry11 = "select * from work_order_extra_reserve_temp where status = 3 and rp_id = " . $rp_id;
			$result11=$dbcon->query($qry11);	
			
			while($res11=brp_mysqli_fetch_assoc($result11)){
				$ext_res_qty = $res11['reserve_qty'];
				$ext_unit_id = $res11['unit_id'];

				$ex_temp_stock['status'] = 2;
				$updateid4=update_record("work_order_extra_reserve_temp", $ex_temp_stock,"wo_reserve_temp_id=".$res11['wo_reserve_temp_id'], $dbcon);

				$qry22 = "select * from smpl_extra_stock where status = 0 and extra_stock_id in (" . $res11['extra_stock_id'].")";
				$result22=$dbcon->query($qry22);			
				while($res22=brp_mysqli_fetch_assoc($result22)){
					$used_base_qty = $res22['used_base_qty'];
					$used_conv_qty = $res22['used_conv_qty'];	

					if($res22['base_unit'] == $res22['conv_unit']){
						$used_stock = 	$used_conv_qty;
					}else{
						$used_stock = 	$used_base_qty;
					}

					if($ext_res_qty>0){
						if($used_stock>=$ext_res_qty){
							$rqty=$ext_res_qty;
							$ext_res_qty=$ext_res_qty-$ext_res_qty;
						}else{
							$rqty=$used_stock;
							$ext_res_qty=$ext_res_qty-$used_stock;
						}

						if($res22['base_unit'] == $res22['conv_unit']){
							$con_stock=$rqty;
							$base_stock=$rqty;
						}else if($res22['conv_unit']==$ext_unit_id){
							$type="base_unit";
							$con_stock=$rqty;
							$base_stock=convert_stock_new($dbcon,$rqty,$res22['product_id'],$type);
						}else{
							$type="conv_unit";
							$base_stock=$rqty;
							$con_stock=convert_stock_new($dbcon,$rqty,$res22['product_id'],$type);
						}
						$extra_stock_trn['used_base_qty'] = $res22['used_base_qty'] - $base_stock;
						$extra_stock_trn['used_conv_qty'] = $res22['used_conv_qty'] - $con_stock;

						// var_dump($extra_stock_trn);
						$updateid1=update_record("smpl_extra_stock", $extra_stock_trn,"extra_stock_id=".$res22['extra_stock_id'], $dbcon);
					}
				}
			}
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
				function get_child_tree($dbcon,$rp_id,$jobwork_type,$eid)
				{


			$q="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_icode,dr.drawing_number,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.image_name,pro.product_id,pro.product_base_qty,pro.product_conv_qty,rpro.customer_id, ptm.product_type_name,pro.product_type,pro.reorder_qty, bom.bom_version_id,pro.product_base_unit FROM `tbl_request_product` as rpro

					left join product_mst as pro on pro.product_id=rpro.rp_pid
					left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
					left join tbl_category as tc on pro.product_category=tc.cat_id
					left join unit_mst as bunit on bunit.unitid=rpro.process_unit
					left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
					left join pro_ms_product_type as ptm on ptm.product_type_id=pro.product_type
					left join tbl_bom as bom on bom.bom_id=rpro.bom_id
					WHERE  rpro.status != 2 and rpro.perent_id = '$rp_id'"; 

					$res = $dbcon->query($q);
					if(brp_mysqli_num_rows($res) > 0)
					{

						while($rel=brp_mysqli_fetch_assoc($res))
						{ 
							$customer_id = "";
							if($jobwork_type == '1'){
								$customer_id = $rel['customer_id'];
							}
							
							$po_base_qty=0;

							$btn_document = '<button type="button" id="btn_bom_doc" onclick="view_documents('.$rel['bom_id'].','.$rel['bom_version_id'].');" class="btn btn-info btn-xs" >View Documents</button>';

							$btn_remark = '<button type="button" id="btn_product_remark" onclick="show_product_remark_modal('.$rel['rp_id'].','.$rel['status'].');" class="btn btn-info btn-xs" >Add Remark</button>';
							

							/* check product lead time and process */

				$lead_n_process = check_product_lead_time_and_process($dbcon,$rel["product_id"]); 
				$bclolr = '';
				if($lead_n_process == 0)
				{
				$bclolr = 'style="background-color:#FFFFA7;"';
				}
				/* */
					$unrequest_button = "";
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
											$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');"';

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
											$req_btn_action = 'onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');"';										
											$req_btn_text = 'Request';
										}
										else if($rel['approval_status'] == 2){
											$req_btn_text = 'Rejected Request';
											$req_btn_action= '';
										}
										else
										{
											$req_btn_action = 'onclick="pending_approval();"';
											$req_btn_text = 'Pending Request';
										}
									}	

									$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" '.$req_btn_action.' ><i class="fa fa-paper-plane"></i> '.$req_btn_text.'</a>';
								}
								else {

								$request_button='<a class="btn btn-primary dispbtn btn-xs" data-original-title="" id="reqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_product_request('.$rel["rp_id"].',1,'.$lead_n_process.');" ><i class="fa fa-paper-plane"></i> Request</a>';
									/* JAYESH */
								}


							}else{
								
								$request_button='<a class="btn btn-success dispbtn btn-xs" data-original-title="" data-toggle="tooltip" data-placement="top" ><i class="fa fa-check-circle"></i>  Requested</a>';

								$is_child_requested = check_child_is_requested($dbcon,$rel['rp_id']);

								if($is_child_requested > 0){
									$unrequest_button = "";
								}else{
									$unrequest_button='<a class="btn btn-danger dispbtn btn-xs" data-original-title="" id="unreqest_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" style="margin: 5px;" onclick="unrequest_product('.$rel["rp_id"].');" ><i class="fa fa-close"></i> Unrequest</a>';
								}
							}
							$bom2="SELECT status,main_request,rp_req_qty,in_process_qty FROM `tbl_request_product` WHERE status!=2 AND rp_id=".$rel['perent_id']; 

							$bom_rel2=brp_mysqli_fetch_assoc($dbcon->query($bom2));

				//echo "<pre>"; print_r($bom_rel2);
				//$bom_rel2=brp_mysqli_fetch_assoc($dbcon->query($bom2));
							if($bom_rel2['main_request']!="1"){
								if($bom_rel2['status']=="3"){
									$request_button="";
								}else{

								}
							}

							/*$cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$customer_id);
							$rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],"","","","",$rel["branch_id"]);
							$wipstock=wipstock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"]);*/
								if($rel['extra_stock'] == '1'){
									$actualstock=get_extra_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$rel['ext_stock_vendor_id']);

									$base_actualstock=get_extra_stock($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$rel['ext_stock_vendor_id']);
									$g=1;
								}else{
									$cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$customer_id);
									$rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],"","","","",$rel["branch_id"],"","","","",$customer_id);
									$wipstock=wipstock($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["branch_id"],$customer_id);
									$actualstock=$cstock-$rstock;
									$wip_purchase_stock=wip_purchase_stock($dbcon,$rel["rp_pid"],$rel["purchase_unit"]);
									$wip_purchase_stock=0;
									
									$actualstock=$actualstock+$wipstock+$wip_purchase_stock;
									
									$g=$wip_purchase_stock;

									$base_cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$customer_id);
									$base_rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["process_unit"],"","","","",$rel["branch_id"],"","","","",$customer_id);
									$base_wipstock=wipstock($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["branch_id"],$customer_id);
									$base_actualstock=$base_cstock-$base_rstock;
									$base_wip_purchase_stock=wip_purchase_stock($dbcon,$rel["rp_pid"],$rel["process_unit"]);
									$base_wip_purchase_stock=0;
									$base_actualstock=$base_actualstock+$base_wipstock+$base_wip_purchase_stock;
								}

							$query_process_ch="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn
								where trn.rp_id=".$rel["rp_id"];
								
								$result_process_ch=$dbcon->query($query_process_ch);
								$cnt_process_ch=mysqli_num_rows($result_process_ch);
								
								if($cnt_process_ch>0){
									$ac_process_sto=process_stock_for_mrp($dbcon,$rel["rp_pid"],$rel["purchase_unit"],$rel["rp_id"],$rel["branch_id"]);
									$base_ac_process_sto=process_stock_for_mrp($dbcon,$rel["rp_pid"],$rel["process_unit"],$rel["rp_id"],$rel["branch_id"]);
									$actualstock=$actualstock+$ac_process_sto;
									$base_actualstock=$base_actualstock+$base_ac_process_sto;
								}

							if($rel["status"]==0){
								$reserv_read_only="readonly";
								$reserv_conv_read_only="readonly";
								$po_read_only="readonly";
								$po_conv_read_only="readonly";
								$process_read_only="readonly";
								$req_read_only="readonly";
								$req_qty=$rel['rp_req_qty'];
							}else{
								$reserv_read_only="readonly";
								$reserv_conv_read_only="";
								$po_read_only="";
								$process_read_only="";
								$po_conv_read_only="";
								$req_read_only="";

								if($bom_rel2['status']=="3"){
									$req_qty=$bom_rel2['rp_req_qty']*$rel["req_qty_one"];
								}else{
									$req_qty=$bom_rel2['in_process_qty']*$rel["req_qty_one"];
								}
								//if($bom_rel2['in_process_qty']!=0){
									
						//$req_qty=$rel['in_process_qty']*$rel["req_qty_one"];
								//}else{
						//$req_qty=$bom_rel2['rp_req_qty']*$rel["req_qty_one"];

									//$req_qty=$bom_rel2['rp_req_qty']*$rel["req_qty_one"];
								//}
								$req_qty=round($req_qty,4);

								if($actualstock<=0){
									$reserv_read_only="readonly";
									$reserv_conv_read_only="readonly";

								}
							}


							$reorder_qty = 0;
							$reorder_conv_qty = 0;

							/*if(!empty($rel['reorder_qty']) && $rel['reorder_qty'] > 0){
								$reorder_qty = $rel['reorder_qty'];		
								$reorder_conv_qty = convert_stock($dbcon,$reorder_qty,$rel['rp_pid'],"conv_unit");		

							   $chk_qty = 	ceil($req_qty  / $reorder_qty);
							   $req_qty = 	$reorder_qty * $chk_qty;
							}*/

							$process_qty = "";
							$po_qty = "";
							$po_base_qty = "";
							$pr_setting_arr=explode(",",$rel['product_setting_check']);
							
							if($rel["status"]!=0){	
								
								if(in_array("process_product",$pr_setting_arr))
								{
									$process_read_only="";
									$process_conv_read_only="";
									$process_qty=$req_qty;
									$po_qty="";
									$basedisplay="display:block";
									$convdisplay="display:block";
									$basereadonly="";
									$convreadonly="";
									$reserv_read_only = "";
									// $reserv_conv_read_only = "readonly";
									$request_unit_type="base_unit";
									$req_unit_id=$rel['process_unit'];
								}
								else
								{
									$process_read_only="readonly";
									$process_conv_read_only="readonly";
									$process_qty="";
									$po_base_qty=$req_qty;
									$convdisplay="display:block";
									$basedisplay="display:block";
									$basereadonly="";
									$convreadonly="";
									$reserv_read_only = "readonly";
									// $reserv_conv_read_only = "";
									$request_unit_type="conv_unit";
									$req_unit_id=$rel['purchase_unit'];

					//pathik production convert unit start
									$po_qty=convert_stock($dbcon,$po_base_qty,$rel['rp_pid'],"conv_unit");
					//pathik production convert unit end
									//var_dump($po_base_qty);
								}
							}else{

								if(in_array("process_product",$pr_setting_arr))
									{
										$process_qty=$rel["in_process_qty"];
										$po_qty="";
										
									}else
									{
										$process_qty="";
										$po_base_qty=$rel["rp_po_base_qty"];
										$po_qty=$rel["rp_po_qty"];
									
									}
								
								/*$process_qty=$rel["in_process_qty"];
								$po_base_qty=$rel["rp_po_qty"];
									//var_dump($po_base_qty);
				//pathik production convert unit start
								$po_qty=convert_stock($dbcon,$po_base_qty,$rel['rp_pid'],"base_unit");*/
				//pathik production convert unit end
								//var_dump($po_qty);
								if(!empty($process_qty)){
									$basedisplay="display:block";
									$convdisplay="display:block";
									$basereadonly="";
									$convreadonly="";
									
									$request_unit_type="base_unit";
									$req_unit_id=$rel['process_unit'];
								}else{
									$convdisplay="display:block";
									$basedisplay="display:block";
									$basereadonly="";
									$convreadonly="";
									
									$request_unit_type="conv_unit";
									$req_unit_id=$rel['purchase_unit'];
								}
							}


							$req_unit_id=$rel['process_unit'];
							$request_unit_type="base_unit";

							if($actualstock<=0){
								$reserv_read_only="readonly";
								$reserv_conv_read_only="readonly";
							}else{
								$reserv_read_only="";
								$reserv_conv_read_only="";	
							}


							if($rel["status"]==0){	
								$reserv_read_only="readonly";
								$reserv_conv_read_only="readonly";
								$basereadonly="readonly";
								$convreadonly="readonly";
								$po_read_only="readonly";
								$po_conv_read_only="readonly";
								$process_read_only="readonly";
								$process_conv_read_only="readonly";
								$req_read_only="readonly";
								$req_conv_read_only="readonly";
							}else {
								$reserv_conv_read_only="readonly";
								$convreadonly="readonly";
								$po_conv_read_only="readonly";
								$process_conv_read_only="readonly";
								$req_conv_read_only="readonly";
							}
							
							$sub_product_button='';
							$check_process_query="SELECT * from tbl_wororder_product_process where  rp_id = ".$rel["rp_id"]." AND process_id != '0'";
							$check_process_result=$dbcon->query($check_process_query);
							if(brp_mysqli_num_rows($check_process_result)> 0)
							{ 

								$parent_delete_flag = 1;

								if($rel['status']==3){
									$sub_product_button='<a class="btn btn-success btn-xs" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="add_sub_product('.$rel["rp_id"].','.$rel["product_id"].','.$eid.','.$rel['product_base_unit'].');" ><i class="fa fa-plus"></i></a>';
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
								$process_button='';
								$check_process_allocate_query="SELECT * from tbl_allocate_process where p_product_id=".$rel["product_id"]." AND p_status = '1' and p_ref_id = " . $rel["rp_id"];
								$check_process_allocate_result=$dbcon->query($check_process_allocate_query);
								if(brp_mysqli_num_rows($check_process_allocate_result) < 1 && $rel['status']==3)
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
								$action = "";
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


			//pathik production convert unit start
								$req_qty_conv=convert_stock($dbcon,$req_qty,$rel['rp_pid'],"conv_unit");
								$process_qty_conv=convert_stock($dbcon,$process_qty,$rel['rp_pid'],"conv_unit");
				//$po_base_qty=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"conv_unit");
				//$base_po_qty=convert_stock($dbcon,$po_qty,$rel['rp_pid'],"base_unit");
								if($rel["conv_unit_name"]!=$rel["base_unit_name"]){
									$unitcon=$rel["product_base_qty"].' '.$rel["base_unit_name"].' = '.$rel["product_conv_qty"].' '.$rel["conv_unit_name"];
								}else{
									$unitcon="";
								}
							//var_dump($po_base_qty);
			//pathik production convert unit end
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

						        $req_qty	=  round_up($req_qty,5);
								$req_qty_conv	= round_up($req_qty_conv,5);
								$process_qty	= round_up($process_qty,5);
								$process_qty_conv = round_up($process_qty_conv,5);
								$po_qty = round_up($po_qty,5);
								$po_base_qty = round_up($po_base_qty,5);

								$req_read_only="readonly";
								$req_conv_read_only="readonly";

								echo '<tr '.$bclolr.' id=rp_row_'.$rel["rp_id"].' data-rp_id="'.$rel["rp_id"].'" data-perent_rp_id="'.$rel["perent_id"].'" class="child_rp_row'.$rel['perent_id'].' '.$rel['rp_pid'].' " > 
								<td>'.$rel["sr_no"].'</td>
								<td><span style="color: red;"><strong>Name : </strong>'.$rel["product_name"].$item_code.$drawing_number.' </span></br><span style="color: #5708d5;"><strong>Category : </strong>'.$cat_name.' </span></br><span style="color: #5708d5;"><strong>Category : </strong>'.$rel['product_type_name'].' </span></br><span style="color: #3c7ab7;"><strong> Minimum Qty : </strong>'.$rel["product_min_stock"].'</span></br>'.$image_name1.'</td>
								<!--<td>'.$image_name1.'</td>

								<td>'.$cat_name.'</td>
								<td>'.$rel["product_min_stock"].'</td>-->
								<td>
								<div class="col-md-9" >
								<input type="number" min="0" class="form-control numbersOnly" name="base_current_stock'.$rel["rp_id"].'" id="base_current_stock'.$rel["rp_id"].'" onkeydown="return numericonly(event)"  value="'.$base_actualstock.'" readonly />
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["base_unit_name"].'</strong>
								</div>
								<div class="col-md-9" style="margin-top:5px">
								<input type="number" min="0" class="form-control numbersOnly" name="current_stock'.$rel["rp_id"].'" id="current_stock'.$rel["rp_id"].'" onkeydown="return numericonly(event)"  value="'.$actualstock.'" readonly />
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["conv_unit_name"].'</strong>
								</div>
								<!--<a class="btn btn-success btn-xs" data-original-title="" id="sub_prd_btn'.$rel["rp_id"].'" data-toggle="tooltip" data-placement="top" onclick="show_current_stock_by_product('.$rel["rp_id"].','.$rel["rp_pid"].','.$rel["purchase_unit"].','.$rel['customer_id'].');" ><i class="fa fa-plus"></i></a>-->
								</td>
								<td>
								<div id="base'.$rel["rp_id"].'" style="'.$basedisplay.'">
								<div class="col-md-9" >
								<input type="number" min="0" class="form-control numbersOnly" name="req_qty'.$rel["rp_id"].'" id="req_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$req_qty_sh.',\'base_unit\');error_check('.$rel["rp_id"].','.$req_qty_sh.')"  value="'.$req_qty.'"  '.$req_read_only.' />

								<input type="hidden" name="req_qty_one'.$rel["rp_id"].'" id="req_qty_one'.$rel["rp_id"].'" value="'.$rel["req_qty_one"].'" />

								<input type="hidden" name="basic_req_qty'.$rel["rp_id"].'" id="basic_req_qty'.$rel["rp_id"].'" value="'.$req_qty.'" />
								<div class="col-md-12">
								<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
								</div>
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["base_unit_name"].'</strong>
								</div>
								</div>
								<div id="conv'.$rel["rp_id"].'" style="'.$convdisplay.'">
								<div class="col-md-9" style="margin-top:5px">
								<input type="number" min="0" class="form-control" name="req_qty_conv'.$rel["rp_id"].'" id="req_qty_conv'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$req_qty_sh.',\'conv_unit\');"  value="'.$req_qty_conv.'"  '.$req_conv_read_only.' />
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["conv_unit_name"].'</strong>
								</div>
								</div>
								<input type="hidden" name="req_unitid'.$rel["rp_id"].'" id="req_unitid'.$rel["rp_id"].'" value="'.$req_unit_id.'" />
								<input type="hidden" name="req_unitname'.$rel["rp_id"].'" id="req_unitname'.$rel["rp_id"].'" value="'.getunitname($dbcon,$req_unit_id).'" />
								<input type="hidden" name="req_product_id'.$rel["rp_id"].'" id="req_product_id'.$rel["rp_id"].'" value="'.$rel["rp_pid"].'" />


								<input type="hidden" name="reorder_qty'.$rel["rp_id"].'" id="reorder_qty'.$rel["rp_id"].'" value="'.$reorder_qty.'" />

								<input type="hidden" name="reorder_conv_qty'.$rel["rp_id"].'" id="reorder_conv_qty'.$rel["rp_id"].'" value="'.$reorder_conv_qty.'" />

								<div class="col-md-12">
								<span style="display:none;" class="error" id="req_qty_err'.$rel["rp_id"].'" ></span>
								</div>
								<input type="hidden" name="convtype'.$rel["rp_id"].'" id="convtype'.$rel["rp_id"].'" value="'.$request_unit_type.'" />
								<input type="hidden" name="pro_base_qty'.$rel["rp_id"].'" id="pro_base_qty'.$rel["rp_id"].'" value="'.$rel["product_base_qty"].'" />
								<input type="hidden" name="pro_convert_qty'.$rel["rp_id"].'" id="pro_convert_qty'.$rel["rp_id"].'" value="'.$rel["product_conv_qty"].'" />
								<span class="col-md-12" style="white-space: nowrap;color: #1a8d0d;font-weight: 600;">'.$unitcon.'</span>
								</td>
								<td>
								<div class="col-md-9">
									<div id="rbase'.$rel["rp_id"].'" style="'.$basedisplay.'">
										<input type="number" min="0" class="form-control numbersOnly" name="res_qty'.$rel["rp_id"].'" id="res_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$res_qty_sh.',\'base_unit\');error_check('.$rel["rp_id"].','.$res_qty_sh.')" value="'.$rel["reserve_base_stock"].'" '.$reserv_read_only.' />
									</div>
									</div>
											<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["base_unit_name"].'</strong>
										</div>
										<div class="col-md-9"  style="margin-top:5px;>
									<div id="rconv'.$rel["rp_id"].'" style="'.$convdisplay.'">
										<input type="number" min="0" class="form-control" name="res_qty_conv'.$rel["rp_id"].'" id="res_qty_conv'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$res_qty_sh.',\'conv_unit\');error_check('.$rel["rp_id"].','.$res_qty_sh.')" value="'.$rel["reserve_stock"].'" '.$reserv_conv_read_only.' />
									</div>
									</div>
									<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
										<strong>'.$rel["conv_unit_name"].'</strong>
										</div>
										<div class="col-md-12">
								<span style="display:none;" class="error" id="res_qty_err'.$rel["rp_id"].'" ></span>
								</div>
								</td>
								<td>
								<!--<div class="col-md-9">
								<input type="number" min="0" class="form-control numbersOnly" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="error_check('.$rel["rp_id"].','.$process_qty_sh.')" onkeydown="return numericonly(event)"  value="'.$process_qty.'" '.$process_read_only.' />
								<div class="col-md-12">
								<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
								</div>
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["base_unit_name"].'</strong>
								</div>-->
								<div id="baseprocess'.$rel["rp_id"].'" >
								<div class="col-md-9" >
								<input type="number" min="0" class="form-control" name="process_qty'.$rel["rp_id"].'" id="process_qty'.$rel["rp_id"].'" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$process_qty_sh.',\'base_unit\');" onkeypress="return isNumberKey(event)"  value="'.$process_qty.'" '.$process_read_only.' />


								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["base_unit_name"].'</strong>
								</div>
								</div>
								<div id="convprocess'.$rel["rp_id"].'" style="display:block;margin-top:40px;">
								<div class="col-md-9" >
								<input type="number" min="0" class="form-control" name="conv_process_qty'.$rel["rp_id"].'" id="conv_process_qty'.$rel["rp_id"].'" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$process_qty_sh.',\'conv_unit\');" onkeypress="return isNumberKey(event)"  value="'.$process_qty_conv.'" '.$process_conv_read_only.' />
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["conv_unit_name"].'</strong>
								</div>
								</div>
								<div class="col-md-12">
								<span style="display:none;" class="error" id="process_qty_err'.$rel["rp_id"].'" ></span>
								</div>

								</td>
								<td>
								<!--<div class="col-md-9" >
								<input type="number" min="0" class="form-control numbersOnly" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeydown="return numericonly(event)" onkeyup="error_check('.$rel["rp_id"].','.$po_qty_sh.')"  value="'.$po_base_qty.'" '.$po_read_only.' />
								<div class="col-md-12">
								<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
								</div>
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["conv_unit_name"].'</strong>
								</div>-->
								<div id="basepo'.$rel["rp_id"].'" style="display:block;">
								<div class="col-md-9" >
								<input type="number" min="0" class="form-control" name="base_po_qty'.$rel["rp_id"].'" id="base_po_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$po_qty_sh.',\'base_unit\');"  value="'.$po_base_qty.'" '.$po_read_only.' />
								<div class="col-md-12">
								<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
								</div>
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["base_unit_name"].'  </strong>
								</div>
								</div>
								<div id="convpo'.$rel["rp_id"].'" >
								<div class="col-md-9" style="margin-top:5px">
								<input type="number" min="0" class="form-control" name="po_qty'.$rel["rp_id"].'" id="po_qty'.$rel["rp_id"].'" onkeypress="return isNumberKey(event)" onkeyup="convert_unit_fun('.$rel["rp_id"].','.$po_qty_sh.',\'conv_unit\');"  value="'.$po_qty.'" '.$po_conv_read_only.' />
								<div class="col-md-12">
								<span style="display:none;" class="error" id="po_qty_err'.$rel["rp_id"].'" ></span>
								</div>
								</div>
								<div class="col-md-2" style="margin-top: 8px;margin-left: -20px;">
								<strong>'.$rel["conv_unit_name"].' </strong>
								</div>
								</div>
								</td>
								<td class="action'.$rel["rp_id"].'">'.$request_button.' '.$unrequest_button .'
								<br>'.$action . ' '.$btn_document . '  '. $btn_remark  .'</td>
								</tr>';

				//$rp_id = $rel['rp_id'];
								$child_query = "select * from tbl_request_product where perent_id = ".$rel['rp_id'];
								$child_result=$dbcon->query($child_query);

								if(brp_mysqli_num_rows($child_result)>0)
								{
									if($rel['status']==0){
												
										$child_rel=brp_mysqli_fetch_assoc($child_result);

										get_child_tree($dbcon,$child_rel['perent_id'],$jobwork_type,$eid);
									}

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
							$q="select * from tbl_request_product where status !=2 and perent_id= '$rp_ids'"; 	
						}
						else
						{
							$q="select * from tbl_request_product where status !=2 and perent_id= '$rp_id'";	 
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
									$q1="select * from tbl_request_product where status !=2 and rp_id= '$rp_id'";
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


								$child_query = "select * from  tbl_request_product where status !=2 and perent_id = '$child_rp_id'";
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

					function bom_child_tree($dbcon,$bom_id,$sp_id,$rp_parent_id,$num,$qty,$branch_id,$bom_qty,$customer_id="",$jobwork_type="",$extra_stock=0,$ext_stock_vendor_id=0)
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
								$sr_no = $num.'.'.$k;

								$base_one_qty=$rel1['product_base_qty']/$bom_qty;
								$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

								/*$base_qty=$base_one_qty*$info_su['rp_req_qty'];
								$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/

								$info_sub['sp_id']				= $sp_id;
								$info_sub['sr_no']				= $sr_no;
								$info_sub['rp_pid']				= $rel1['product_id'];
								$info_sub['rp_req_qty']			= $conv_one_qty * $qty;
								$info_sub['rp_req_date']		=  date("Y-m-d");

						//$info_sub['rp_req_qty']			= $conv_stock;//required qty
						$info_sub['req_qty_one']		= $conv_one_qty;//required qty
						$info_sub['rp_po_qty']			= "";//po qty
						$info_sub['in_process_qty']		= "";//process qty
						$info_sub['rp_req_type']		= "work_order";//type
						$info_sub['process_unit']		= $rel1['product_base_unit'];
						$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub['perent_id']			= $rp_parent_id;
						$info_sub['status']				= 3;
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						//$info_sub['main_request']		= $POST['g_total'];
						
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
						$info_sub['customer_id']		= $customer_id;
						$info_sub['jobwork_type']		= $jobwork_type;
						$info_sub['extra_stock']		= $extra_stock;
						$info_sub['ext_stock_vendor_id']		= $ext_stock_vendor_id;

						$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$branch_id);

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
								$wpp_info['branch_id']			= $branch_id;
								$wpp_info['description']			= $product_process['description'];

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
								$inserid_sub=add_record('tbl_wo_material_trn', $mat_data, $dbcon,$branch_id);
								
							}
						}
						
						/*   Material Formula */

			//echo $sr_no;
			//echo $rel1['p_bom_id'];

						bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$sr_no,$qty,$branch_id,$rel1['product_base_qty'],$customer_id,$jobwork_type,$extra_stock,$ext_stock_vendor_id);
						$k++;	

					}
				}

			}

			function check_child_is_requested($dbcon,$rp_id){
				$used = 0;


				$qry = "select count(rp_id) as request from tbl_request_product where status = 0 and perent_id = " . $rp_id;
				$result=$dbcon->query($qry);
				$res=brp_mysqli_fetch_assoc($result);

				if($res['request'] > 0){
					$used++;
				}

				$qry1 = "select count(rp_id) as request from tbl_request_product where status = 0 and indent_status = 3 and perent_id = " . $rp_id;
				$result1=$dbcon->query($qry1);
				$res1=brp_mysqli_fetch_assoc($result1);

				if($res1['request'] > 0){
					$used++;
				}


				$qry1 = "select count(rp_id) as request from tbl_request_product where status = 0 and indent_status = 3 and rp_id = " . $rp_id;
				$result1=$dbcon->query($qry1);
				$res1=brp_mysqli_fetch_assoc($result1);

				if($res1['request'] > 0){
					$used++;
				}

				$qry2 = "select count(reserve_id) as request from tbl_reserve_stock where stock_flage = 2 and  stock_status = 0 and request_id = " . $rp_id;
					$result2=$dbcon->query($qry2);
					$res2=brp_mysqli_fetch_assoc($result2);

					if($res2['request'] > 0){
						$used++;
					}

				$qry1 = "select perent_id from tbl_request_product where status = 0 and rp_id = " . $rp_id;
				$result1=$dbcon->query($qry1);

				if(brp_mysqli_num_rows($result1) > 0){
					$res1=brp_mysqli_fetch_assoc($result1);
					$chk_rp_id = $res1['perent_id'];

					$qry2 = "select count(reserve_id) as request from tbl_reserve_stock where stock_flage = 2 and  stock_status = 0 and request_id = " . $chk_rp_id;
					$result2=$dbcon->query($qry2);
					$res2=brp_mysqli_fetch_assoc($result2);

					if($res2['request'] > 0){
						$used++;
					}

					$qry2pp = "select count(store_request_id) as request from tbl_store_request where store_request_status = 0 and rp_id = " . $chk_rp_id;
					$result2pp=$dbcon->query($qry2pp);
					$res2pp=brp_mysqli_fetch_assoc($result2pp);

					if($res2pp['request'] > 0){
						$used++;
					}

				}

				$qry2 = "select count(wip_stock_allocate_id) as request from wip_stock_allocate where stock_flag = 2 and  status = 0 and rp_id = " . $rp_id;
					$result2=$dbcon->query($qry2);
					$res2=brp_mysqli_fetch_assoc($result2);

					if($res2['request'] > 0){
						$used++;
					}

				/*$qry3 = "select count(p_id) as request from tbl_allocate_process where p_status in (1,3) and p_ref_id = " . $rp_id;*/
				$qry3 = "select count(p_id) as request from tbl_allocate_process where p_status in (1) and p_ref_id = " . $rp_id;
				$result3=$dbcon->query($qry3);
				$res3=brp_mysqli_fetch_assoc($result3);	

				if($res3['request'] > 0){
						$used++;
					}

				return $used;
			}

			function check_having_child_product($dbcon,$rp_id){

				$qry = "select count(rp_id) as child from tbl_request_product where status != 2 and perent_id = '" . $rp_id."'";
				$result=$dbcon->query($qry);
				$res=brp_mysqli_fetch_assoc($result);

				if($res['child'] > 0){
					return 1;
				}else{
					return 0;
				}
			}


			function check_child_product_requested_count($dbcon,$rp_id){
				$count = 0;

				$qry="select rp_id,status from tbl_request_product where status != 2 and user_id=".$_SESSION['user_id']." and perent_id=".$rp_id;
			
				// $qry = "select count(rp_id) as child from tbl_request_product where status != 2 and perent_id = " . $rp_id;
				$result=$dbcon->query($qry);
				while($res=brp_mysqli_fetch_assoc($result)){
					if($res['status']>0){
						$count++;
					}
					$rcount = check_child_product_requested_count($dbcon,$res['rp_id']);
					$count = $count + $rcount;
				}

				return $count;
			}

			function update_store_oreder_request_workorder_qty_status($dbcon,$order_id,$product_id,$qty){

			$set_pro="SELECT * FROM `tbl_store_order_min_max` WHERE status=0 AND order_id='".$order_id."'";
					$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));

			$req_qty = $setpro_rel['base_request_qty'];
			$req_conv_qty = $setpro_rel['conv_request_qty'];

			$wo_base_qty = $setpro_rel['wo_base_qty'];
			$wo_conv_qty = $setpro_rel['wo_conv_qty'];

			$conv_qty = convert_stock_new($dbcon,$qty,$product_id,"conv_unit");

			$wo_base_qty = $wo_base_qty  + $qty;
			$wo_conv_qty = $wo_conv_qty  + $conv_qty;

					$upd_info['wo_base_qty'] = $wo_base_qty;
					$upd_info['wo_conv_qty'] =$wo_conv_qty;

					if($wo_base_qty >= $req_qty){
						$upd_info['wo_complete_status'] =1;				
					}

					$updatetrnid=update_record('tbl_store_order_min_max',$upd_info,"order_id=".$order_id, $dbcon);
		}

		function remove_child_products($dbcon,$rp_id){

			$qry = "select rp_id from tbl_request_product where perent_id = " . $rp_id;
			$result = $dbcon->query($qry);
			while($res=brp_mysqli_fetch_assoc($result)){
				$upd_status['status'] = 2;
				$updateid=update_record("tbl_request_product", $upd_status,"rp_id=".$res['rp_id'], $dbcon);	
				
				remove_child_products($dbcon,$res['rp_id']);				
			}
			
		}

		function undo_remove_child_products($dbcon,$rp_id){

			$qry = "select rp_id from tbl_request_product where perent_id = " . $rp_id;
			$result = $dbcon->query($qry);
			while($res=brp_mysqli_fetch_assoc($result)){
				$upd_status['status'] = 3;
				$updateid=update_record("tbl_request_product", $upd_status,"rp_id=".$res['rp_id'], $dbcon);	
				
				undo_remove_child_products($dbcon,$res['rp_id']);				
			}
			
		}

		function check_child_product_requested_or_not($dbcon,$rp_id){
				$count = 0;

				$qry="select rp_id,status from tbl_request_product where status != 2 and user_id=".$_SESSION['user_id']." and perent_id=".$rp_id;
			
				// $qry = "select count(rp_id) as child from tbl_request_product where status != 2 and perent_id = " . $rp_id;
				$result=$dbcon->query($qry);
				if(brp_mysqli_num_rows($result) > 0){
					while($res=brp_mysqli_fetch_assoc($result)){
						if($res['status'] == 0){
							$count++;
						}
						$rcount = check_child_product_requested_or_not($dbcon,$res['rp_id']);
						$count = $count + $rcount;
					}	
				}
				
				return $count;
			}
		?>