<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Work Order ";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	//$ver = (float)phpversion();
	//echo $ver; 
	$branch_id = $_SESSION['branch_id'];
	if(strpos($_SERVER[REQUEST_URI], "design_department_request_product")==true)
	{
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		//$query="select * from product_mst where product_id='$id'";
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		 $query1="select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 and req.rp_pid=".$rel["product_id"]." group by req.rp_pid";
		$rel1=mysqli_fetch_assoc($dbcon->query($query1));
		
		$total=$min_stock-($opening+$rel1['reqqty']);
		if($total<0){
			$total=0;
		}
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT sp_id,branch_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$select_branchId = $bom_rel_q['branch_id'];
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		
		// pathik start date : 12-12-2020
			//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
	}else if(strpos($_SERVER[REQUEST_URI], "sorequesproduct")==true)
	{
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$so_trn_id=$dbcon->real_escape_string($_REQUEST['so_trn_id']);
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		
		 $query1="select req.*,`so`.`sales_order_no`, `so`.`cust_id`, `so`.`sales_order_date`,`so`.`po_no`, `so`.`po_date`,req.branch_id from tbl_sales_ordertrn as req
		 left join tbl_sales_order as so ON `req`.`sales_order_id` = `so`.`sales_order_id`
 		 where req.sales_ordertrn_status=0 and req.sales_ordertrn_id=".$so_trn_id." group by req.sales_ordertrn_id";
		$rel1=mysqli_fetch_assoc($dbcon->query($query1));
		
		$select_branchId = $rel1['branch_id'];
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		
		$query_p="select IFNULL(sum(product_qty),0) as used_qty from tbl_sales_order_production_trn as req
		 where req.sales_order_production_status=0 and req.sales_ordertrn_id=".$so_trn_id." group by req.sales_ordertrn_id";
		$rel1p=mysqli_fetch_assoc($dbcon->query($query_p));
						
		$total=$rel1['product_qty']-$rel1p['used_qty'];
		
		/* $total=$min_stock-($opening+$rel1['reqqty']);
		if($total<0){
			$total=0;
		} */
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT sp_id,branch_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and sales_order_trn_id=".$so_trn_id." and company_id=".$_SESSION['company_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$branchId = $bom_rel_q['branch_id'];
		// pathik start date : 12-12-2020
			//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
	}
	else if(strpos($_SERVER[REQUEST_URI], "rejectrequestproduct")==true)
	{
		$mode="Add";$direct_add='1';$request=1;$smode="add_rej";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		//$parent_po_ref_id=$dbcon->real_escape_string($_REQUEST['po_ref_id']);
		//$query="select * from product_mst where product_id='$id'";
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		
		/*$query11="select sum(reject_qty-reject_request_qty) as qty from tbl_qc_process_trn 
		where reject_qty!=0 and reject_request_qty<reject_qty and qc_process_status=0 and product_id=".$id." group by product_id";
		$rs11=$dbcon->query($query11);
		
		$row111=mysqli_fetch_array($rs11);
		$total=$row111['qty'];*/
		
		$set11="select rp.*,sum(reject_qty-reject_request_qty) as pending_qty from tbl_qc_process_trn as rp
			where rp.qc_process_status=0 and rp.reject_qty>0 and CAST(reject_qty as DECIMAL(50,2)) > CAST(reject_request_qty as DECIMAL(50,2)) and rp.product_id=".$id." group by rp.product_id";
		$ser=$dbcon->query($set11);
		$set_row=brp_mysqli_fetch_assoc($ser);
		$total=$set_row['pending_qty'];
		
		if($total<0){
			//$total=0;
		} 
		//echo $total;
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$reject_status=1;
		
		// pathik start date : 12-12-2020
			//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
	}else if(strpos($_SERVER[REQUEST_URI], "stock_pending_product")==true)
	{

		$mode="Add";$direct_add='1';$request=1;$smode="add_all";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		//$query="select * from product_mst where product_id='$id'";
		$query="select mst.*, tc.cat_name from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		
		$reserv=reserve_stock($dbcon,$rel['product_id'],$rel['product_base_unit'],$reserve_id);
		$current=get_current_stock_new($dbcon,$rel['product_id'],$rel['product_base_unit']);
		$pendun=get_part_invoice_not_done_send($dbcon,$rel['product_id']);
		$tot=($current-($reserv+$pendun));
		
		$tot=request_all_department_request_qty($dbcon,$rel['product_id']);

		$total=abs($tot);
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		
		$bom_q="SELECT sp_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		
		// pathik start date : 12-12-2020
			//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
	}else
	{
		$mode="Add";$direct_add='0';$request=0;
		$purchaseorder_date=date('d-m-Y');
		$po_type_status='';
	}
	
	//echo $mode;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
		<style >
			.error {
				font-weight: bold;
				color: #ef1717;
				
				font-size: 16px;
			}

		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.'get_stock_detail/min_max'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
								  New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="product_request_add" action="javascript:;" method="post" name="product_request_add">
										<input type="hidden" id="cust_id" name="cust_id" value="<?=$rel1['cust_id']?>">
										<input type="hidden" id="sales_order_date" name="sales_order_date" value="<?=$rel1['sales_order_date']?>">
										<input type="hidden" id="po_no" name="po_no" value="<?=$rel1['po_no']?>">
										<input type="hidden" id="po_date" name="po_date" value="<?=$rel1['po_date']?>">
										<input type="hidden" id="sales_order_no" name="sales_order_no" value="<?=$rel1['sales_order_no']?>">
										<div class="row">
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Work Order No</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="po_req_no" name="po_req_no" type="text" class="form-control" title="Req No" value="" placeholder="" readonly>
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Work Order Date</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="po_req_date" name="po_req_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo date('d-m-Y'); ?>" placeholder="">
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Product Name </strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="po_product_name" name="po_product_name" type="text" class="form-control" title="Date" value="<?=$rel['product_name'].'--( '. get_product_type_by_id($dbcon,$rel['product_type'],'product_type').' )' ?>" placeholder="Product Name" readonly>
														</div>
													</div>	
												</div>
											</div>	
											<!--<div class="col-md-12" style="margin-top:10px;">
													
												
											</div>-->
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<div class="form-group">  	
														<label class="col-md-4 control-label" ><strong> Request Quantity </strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="rp_req_qty" name="rp_req_qty" type="text" class="form-control" title="" value="<?=$total?>" placeholder="Request Qty" onkeypress="return isNumberKey(event)" onkeyup="get_bom_request_qty(this.value);" onchange="cal_po_qty();" >
														</div>
													</div>	
												</div>
												<div class="col-md-4 proc1">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Process Qty</strong></label>
														<div class="col-md-8 col-xs-11">
														<?//=$readonly;?>
															<input id="in_process_qty_main" name="in_process_qty" type="number" class="form-control" title="Date" value="" placeholder="Inhouse Process Qty"  onkeyup="get_inhouse_request_qty(this.value);get_bom_request_qty(this.value);get_po_request_qty(this.value);cal_po_qty();" >
															
														</div>
													</div>	
												</div>	
												<div class="col-md-4">
													<div class="form-group">  	
														<label class="col-md-4 control-label" ><strong> Purchase Qty </strong></label>
														<div class="col-md-5 col-xs-11">
															<input id="rp_po_qty" name="rp_po_qty" type="text" class="form-control" title="Date" value="" placeholder="PO Qty" onkeypress="return isNumberKey(event)" onchange="cal_po_qty();" >
														</div>
														<div class="col-md-2 proc1">
															<a class="btn btn-primary mainRequest" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="main_po_reqdata();" ><i class="fa fa-paper-plane"></i> Request</a>
															
															<a class="btn btn-danger mainRequested" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>
															
															<input type="hidden" name="main_poreq_status" id="main_poreq_status" value="" />
														</div>
													</div>	
												</div>
											</div>
											<div class="col-md-12" style="margin-top:10px;">
											<?php if($branch_id=='0'){ ?>
												<div class="col-md-4">
													<?php echo getBranchBox($dbcon, $branch_id,$select_branchId, $branch_read, true, ''); ?>	
												</div>
											
											<?php }else{ ?>
												<input type="hidden" name="branch_id" id="branch_id" value="<?=$branch_id?>">
											<?php } ?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Category Name</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="category_name" name="category_name" type="text" class="form-control" title="Category Name" value="<?=$category_name?>" readonly placeholder="Category Name" >
														</div>
													</div>	
												</div>
											</div>
											<div class="col-md-12 col-md-offset-5" style="margin-top:10px;">
												<input type="button" id="set_process_btn" name="set_process_btn" class="btn btn-success" value="SET Process Request" onclick="set_main_process_request_qty();" />
												<!--show_btn(1,1);-->
											</div>	
											<div class="col-md-12" style="margin-top:10px;">
												<div id="req_val" >
													<table class="table table-bordered">
														<thead>
															<tr>					
																<th><strong>SR. NO.</strong></th>
																<th><strong>Item Description</strong></th>
																<th><strong>Product Image</strong></th>
																<th><strong>Item Category</strong></th>
																<!--<th><strong>Item Type</strong></th>-->
																<th><strong>Minimum Qty</strong></th>
																<th><strong>Current Stock</strong></th>
																<th><strong>Request Qty</strong></th>
																<th><strong>Reserve Stock</strong></th>
																<th><strong>Process Qty</strong></th>
																<th><strong>PO Qty</strong></th>
																<th><strong>Request</strong></th>
															</tr>
														</thead>
														<tbody id="show_tree_request">
														</tbody>
													</table>
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div> 
												</div>
											</div>
											<div class="col-md-12">
												<center>
												
												<input type="button" name="save" id="save" class="btn btn-success" value="save" onclick="get_main_form_submit();" /> 
												
												<!--<button type="submit" class="btn btn-success" id="save" name="save">Save</button>-->
							
												<a href="<?=ROOT.'get_stock_detail/min_max'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='smode' id='smode' value='<?=$smode?>' />
										<input type='hidden' name='eid' id='eid' value='<?=$id;?>' />	
										<input type='hidden' name='pr_type' id='pr_type' value='<?=$pr_type;?>' />	
										<input type='hidden' name='bom_id' id='bom_id' value='<?=$bom_id;?>' />	
										<input type='hidden' name='process_status' id='process_status' value='<?=$process_status;?>' />	
										
										<input type="hidden" name="work_order_id" id="work_order_id" value="<?=$work_order_id?>" />

										<input type="hidden" name="reject_status" id="reject_status" value="<?=$reject_status?>" />
										
										<input type="hidden" name="bom_check" id="bom_check" value="<?=$bom_check?>" />

										
										
										<input type="hidden" name="sales_order_trn_id" id="sales_order_trn_id" value="<?=$so_trn_id?>" />
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/desing_department_request_product.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$("#product_id").select2({
				width: '86%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

			$(".form_datetime").datetimepicker({
				format: 'dd-mm-yyyy hh:ii',
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"

			});
			function add_customer_purchase()
			{
				$("#bs-example-modal-lg").modal("show");
				$("#cat_id").val('1');
			}
			function consinee_change(val){
				if(val=='1'){
					$('#consignee_id').select2("val","");
					$('#consignee').hide();
				}
				else{
					$('#consignee').show();
				}
			}
		</script>
	</body>
</html>
