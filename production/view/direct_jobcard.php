<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Jobcard ";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	//$ver = (float)phpversion();
	//echo $ver; 
	$branch_id = $_SESSION['branch_id'];
	
	if(strpos($_SERVER['REQUEST_URI'], "jobcardedit")==true)
	{
		$mode="Edit";$direct_add='1';$request=1;$smode="";	
		$id=$dbcon->real_escape_string($_REQUEST['id']);	
		$jobcard_rp_id=$dbcon->real_escape_string($_REQUEST['rp_id']); 
		$jobcard_q="SELECT * from tbl_request_product  WHERE  rp_pid=".$id." and rp_id=".$jobcard_rp_id." and user_id=".$_SESSION['user_id'];
		$jobcard_res=mysqli_fetch_assoc($dbcon->query($jobcard_q));
		
		$total=$dbcon->real_escape_string($jobcard_res['in_process_qty']);
		
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		
		$rp_id = $jobcard_rp_id;
	
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT po_req_no,sp_id,branch_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and rp_id=".$rp_id." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$jobcard_res['job_card_no'];
		$select_branchId = $bom_rel_q['branch_id'];
		$po_req_no = $bom_rel_q['jobcard_no'];
		
		
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		
		$sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where  bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];

		
		//$rp_id=$dbcon->real_escape_string($_REQUEST['edit_id']);
	}
	else
	{
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$branch_id=$dbcon->real_escape_string($_REQUEST['branch_id']);
		$version_id=$dbcon->real_escape_string($_REQUEST['version_id']);
		$total=$dbcon->real_escape_string($_REQUEST['qty']);
		$jobcard_rp_id=$dbcon->real_escape_string($_REQUEST['jobcard_rp_id']);
		
		
		
		 $jobcard_q="SELECT * from tbl_request_product  WHERE status=3 AND rp_pid=".$id." and rp_id=".$jobcard_rp_id." and user_id=".$_SESSION['user_id'];
		$jobcard_res=mysqli_fetch_assoc($dbcon->query($jobcard_q));
		
		//echo "<pre>"; print_r($jobcard_res); die;
		
		
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		 
		$rp_id = $jobcard_rp_id;
	
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT po_req_no,sp_id,branch_id FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and rp_id=".$rp_id." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$jobcard_res['job_card_no'];
		$select_branchId = $bom_rel_q['branch_id'];
		$po_req_no = $bom_rel_q['jobcard_no'];
		
		
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		
		$sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where  bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
		
		
	}
	
	$cancel_url = ROOT.PRODUCTION_ROOT.'job_card_list';

		
	
	//echo $mode;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
	<title>Jobcard</title>
		<?php include_once($include.'include_css_file.php');?>
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
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
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
										<li><a href="<?=ROOT.PRODUCTION_ROOT.'job_card_list'?>"><?=$form?> List</a></li>
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
										<input type="hidden" id="bom_version_id" name="bom_version_id" value="<?=@$version_id;?>">
										<input type="hidden" id="po_req_nos" name="po_req_nos" value="<?=@$po_req_no;?>">
										
										<div class="row">
										<?php if($rel1['sales_order_date'] != '' && $rel1['sales_order_no'] != '' ) { ?> 
										<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Sales Order No</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="" name="" type="text" class="form-control" title="Req No" value="<?=$rel1['sales_order_no']?>"  readonly>
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>sales_order_date</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="" name="" type="text" class="form-control default-date-picker dateOnly" title="Date" value="<?=date("d-m-Y",strtotime($rel1['sales_order_date']));?>" readonly>
														</div>
													</div>	
												</div>
												<?php } ?>
											
											</div>	
										
										
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Job Card No </strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="po_req_no" name="po_req_no" type="text" class="form-control" title="Req No" value="<?php echo $jobcard_res['job_card_no']; ?>" placeholder="" readonly>
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Job Card Date</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="po_req_date" name="po_req_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo date("d-m-Y",strtotime($jobcard_res['job_card_date'])); ?>" placeholder="" readonly>
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
															<input id="rp_req_qty" name="rp_req_qty" type="text" class="form-control" title="" value="<?=$total?>" placeholder="Request Qty" onkeypress="return isNumberKey(event)" onkeyup="get_bom_request_qty(this.value);" onchange="cal_po_qty();" readonly >
														</div>
													</div>	
												</div>
									<?php 
										$check_process_query="SELECT rp_id FROM `tbl_request_product` WHERE   rp_id=".$rp_id;
										$check_process_result=$dbcon->query($check_process_query);
										$main_rp_row = brp_mysqli_fetch_array($check_process_result);
									?>
												
												
												<div class="col-md-4 proc1">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Process Qty</strong></label>
														<div class="col-md-8 col-xs-11">
														<?php //=$readonly;?>
															<input id="in_process_qty_main" name="in_process_qty" type="number" class="form-control" title="Date" value="<?=$total?>" placeholder="Inhouse Process Qty"  onkeyup="get_inhouse_request_qty(this.value);get_bom_request_qty(this.value);get_po_request_qty(this.value);cal_po_qty();" readonly/>
															
														</div>
													</div>	
												</div>	
												<div class="col-md-4">
													<div class="form-group">  	
														<label class="col-md-4 control-label" ><strong> Purchase Qty </strong></label>
														<div class="col-md-5 col-xs-11">
															<input id="rp_po_qty" name="rp_po_qty" type="text" class="form-control" title="Date" value="" placeholder="PO Qty" onkeypress="return isNumberKey(event)" onchange="cal_po_qty();" readonly >
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
											
											<div class="col-md-4"  <?php if(strpos($_SERVER['REQUEST_URI'], "edit_workorder")==true){ ?> style="display: block;"<?php }?>>
												<div class="form-group">  	
													<div class="col-md-4 col-xs-11">
														<button type="button" id="add_wo_prd" onclick="add_work_order_product('<?php echo $id;?>','<?=$total?>');" class="btn btn-success" >Add Product</button>
														
													</div>
													<div class="col-md-4 col-xs-11">
													<button type="button" onclick="direct_show_product_process('<?php echo $id;?>','<?php echo $main_rp_row['rp_id'];?>');" class="btn btn-success" > <span id="process_mode">Add</span> Process</button>											</div>	
												</div>	
											</div>
																						
											<div class="col-md-4">
												<div class="form-group">  	
													<div class="col-md-8 col-xs-11">
														
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
												<?php if($mode != "wo_permission"){?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div> 
												</div>
												<?php } ?>
											</div>
											<div class="col-md-12">
												<center>
												<?php if($mode != "wo_permission"){?>
													
												
												<input type="button" name="save" id="save" class="btn btn-success" value="save" onclick="get_main_form_submit();" /> 
												<a href="<?=$cancel_url?>" type="button" class="btn btn-danger">Cancel</a>
												<?php } ?>
												
												<!--<button type="submit" class="btn btn-success" id="save" name="save">Save</button>-->
							
												
												</center>
											</div>
										</div>
										<input type='hidden' name='redirect_url' id='redirect_url' value='<?=$cancel_url?>' />
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='smode' id='smode' value='<?=$smode?>' />
										<input type='hidden' name='eid' id='eid' value='<?=$id;?>' />	
										<input type='hidden' name='pr_type' id='pr_type' value='<?=$pr_type;?>' />	
										<input type='hidden' name='bom_id' id='bom_id' value='<?=$bom_id;?>' />	
										<input type='hidden' name='process_status' id='process_status' value='<?=$process_status;?>' />	
										
										<input type="hidden" name="work_order_id" id="work_order_id" value="<?=$work_order_id?>" />

										<input type="hidden" name="reject_status" id="reject_status" value="<?=$reject_status?>" />
										
										<input type="hidden" name="bom_check" id="bom_check" value="<?=$bom_check?>" />
										<input type="hidden" id="product_add_type" value="">
										
										
										<input type="hidden" name="sales_order_trn_id" id="sales_order_trn_id" value="<?=$so_trn_id?>" />
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
			<?php include_once($include1.'add_workorder_product.php');?>
			<?php include_once($include1.'add_workorder_sub_product.php');?>
			<?php include_once($include1.'update_product_process.php');?>
			<?php include_once($include1.'bom_process_add_model.php');?>   
			<?php include_once($include1.'qc_model.php');?>  
			<?php include_once($include1.'work_order_permission_details.php');?> 
			
			<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
			<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
			<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  

		</section>
		<div id="dialog" title="Permission Pending">
		<p>Please Contact to  Authorise Person For Approve Requested Product</p>
		</div>
		
		<div id="delete_dialog" title="Do Yo Want to Delete  product ?">
	
		</div>
		
		<style>
			.ui-dialog .ui-dialog-content {
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 16px !important;
				}
				.ui-dialog .ui-dialog-titlebar {
				background: none !important;
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 20px !important;
				}
				.ui-widget-content {
				background: none !important;
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 20px !important;
				}
		</style>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/direct_jobcard.js?<?=time()?>"></script>
		 <script src="<?=ROOT?>js/advanced-form-components.js"></script>
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
