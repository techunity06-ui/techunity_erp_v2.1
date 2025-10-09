<?php 
	session_start();
	include('../include/urlfile.php');
	$form = 'Stock General';

	$branch_id = $_SESSION['branch_id'];
	$companyID = $_SESSION['company_id'];
	if(strpos($_SERVER['REQUEST_URI'], "stock_general_edit")==true){
		$mode="Edit";
		$general_stock_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_general_stock where general_stock_id =$general_stock_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$general_stock_no = $rel['general_stock_no'];
		$general_stock_date ="";
		if($rel['general_stock_date']!="1970-01-01" && $rel['general_stock_date']!="0000-00-00")
		{
			$general_stock_date=date('d-m-Y',strtotime($rel['general_stock_date']));
		}
		$back="stock_general_list";
	}else {
		
		$mode="Add";
		$general_stock_no = load_common_no($dbcon,STOCK_GENERAL_SERIES);
		$general_stock_date = date('d-m-Y');
		$back="stock_general_list";
	}
	$max_followup_date = MAX_FOLLOWUP_DATE;
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));


	$so_qry = " SELECT sales_order_id,sales_order_no  FROM tbl_sales_order WHERE  approve_status = 3 and order_accept_status= 1 and sales_order_status = 0 and invoice_status = 0";
	$so_result = $dbcon->query($so_qry);

	$so_details = "<option value=''>Select Sales Order</option>";

	while($so_rw = brp_mysqli_fetch_assoc($so_result)){
		$so_details .= "<option value='".$so_rw['sales_order_id']."'>".$so_rw['sales_order_no']."</option>";
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?=$form?></title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			.deduct_stock_background{
				background-color : #ff9494;
				color: black;
			}
			.additive_stock_background{
				background-color: #3bc73bab;
				color: black;
			}
			th{
				text-align: center;
			}
		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<?php//include_once('../include/equick_link.php');?>
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										
										<li><a href="<?=ROOT.INVENTORY_ROOT.'stock_general_list'?>"><?=$form?> List</a></li>
										
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
									<form class="form-horizontal" role="form" id="stock_general_add" action="javascript:;" method="post" name="stock_general_add" enctype="multipart/form-data">
										<div class="row">
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Stock General No </label>
														<div class="col-md-6 col-xs-11">
															<input type="text" name="stock_general_no" id="stock_general_no" class="form-control" title="Stock General No" value="<?=$general_stock_no?>" placeholder="Stock General No">
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Stock General Date </label>
														<div class="col-md-6 col-xs-11">
															<input type="text" name="stock_general_date" id="stock_general_date" class="form-control default-date-picker" title="Stock General Date" value="<?=$general_stock_date?>" placeholder="Stock General Date"> 
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12" style="margin-top:10px;"></div>	

											<div class="col-md-12">
												<div class="col-md-6">
													<table class="table table-bordered ">
														<thead>
															<tr>
																<th colspan="6" style="text-align: center;" class="deduct_stock_background">Deduct Stock Detail</th>
															</tr>
															
															<tr>
																<th style="width:20%;">Sales Order</th>
																<th style="width:20%;">User</th>
																<th style="width:25%;">Product Name</th>
																<th style="width:12%;">Unit</th>
																<th style="width:15%;">Qty</th>
																<th style="width:8%;">Action</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td style="max-width:150px">
																	<select class="select2" name="sales_order_id_deduct" id="sales_order_id_deduct" >
																	<?= $so_details?>
																</select>
																</td>
																<td style="max-width:150px">
																	<select class="select2" name="user_deduct" id="user_deduct" >
																	<?= get_ledger($dbcon,$rel['for_user_id'],'and l_group IN ('.SALARY_ACCOUNT.')');?>
																</select>
																</td>
																<td style="max-width:150px">
																	<input type="text" name="product_deduct_id" id="product_deduct_id" class="form-control" onchange="load_deduct_productdetail(this.value)">

																	<strong class="product_stock_label" style="display:none;color:green"> Current Stock : <span id="product_stock_label"></span></strong>
																</td>
																<td>
																	<select class="form-control"  title="Select Unit" placeholder="Unit" name="deduct_unit_id" id="deduct_unit_id" onchange="load_deduct_product_unit()">
                                                                	
                                                                	<option value="0">Select Unit</option>
                                                            		</select>
																</td>
																<td>
																	<div id="convert_deduct_unit_block" style="display: none;">
			                                                            <input type="text" title="Enter Qty" min="0" id="product_deduct_conv_qty" name="product_deduct_conv_qty" class="form-control numbersOnly valid" onkeyup="product_convert_deduct_qty(1);" >
			                                                            <input type="hidden" name="conv_deduct_unitid" id="conv_deduct_unitid" value="1">
			                                                            <input type="hidden" id="product_deduct_conv_qtyh" name="product_deduct_conv_qtyh" value="3">
			                                                            <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_deduct_unit_show"></span>
			                                                        </div>
			                                                        
			                                                        <div id="base_deduct_unit_block" style="">
			                                                            <input type="text" title="Enter Qty" min="0" id="product_deduct_qty" name="product_deduct_qty" class="form-control numbersOnly" onkeyup="product_convert_deduct_qty(2);">
			                                                            <input type="hidden" name="deduct_unitid" id="deduct_unitid" value="">
			                                                            <input type="hidden" id="product_deduct_qtyh" name="product_deduct_qtyh" value="">
			                                                            <span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="deduct_unit_show">  </span>
			                                                        </div>
																	<!-- <input type="text" name="product_deduct_qty" id="product_deduct_qty" class="form-control numbersOnly"> -->
																</td>
																<td>
																	<input type="hidden" name="product_deduct_stock" id="product_deduct_stock" >
																	<input type="hidden" name="edit_deduct_id" id="edit_deduct_id">
																	<input type="button" id="deduct" value="Add" class="btn btn-primary" onclick="add_field_deduct()"> 

																	<input type="button" id="deduct_batch_wise" value="Add" class="btn btn-primary" onclick="batch_wise_deduct_stock_open()" style="display: none;">
																</td>
															</tr>
														</tbody>

													</table>
													<div class="col-lg-12" style="margin-top:10px" id="stock_deduct_detail"></div>
												</div>
												
												<div class="col-md-6">
													<table class="table table-bordered ">
														<thead>
															<tr>
																<th colspan="7" style="text-align: center;" class="additive_stock_background"> Stock In Detail</th>
															</tr>
															
															<tr>
																<th style="width:17%;">Sales Order</th>
																<th style="width:17%;">User</th>
																<th style="width:17%;">Product Name</th>
																<th style="width:14%;">Unit</th>
																<th style="width:14%;">Qty</th>
																<th style="width:14%;">Rate</th>
																<th style="width:7%;">Action</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td style="max-width:150px">
																	<select class="select2" name="sales_order_id_in" id="sales_order_id_in" >
																	<?= $so_details?>
																</select>
																</td>
																<td style="max-width:150px">
																	<select class="select2" name="user_in" id="user_in" >
																	<?= get_ledger($dbcon,$rel['for_user_id'],'and l_group IN ('.SALARY_ACCOUNT.')');?>
																</select>
																</td>
																<td style="max-width:150px">
																	<input type="text" name="product_in_id" id="product_in_id" class="form-control" onchange="load_in_productdetail(this.value);load_in_product_stock();">
																	<span id="insto" style="color:green;display:none;"></span>
																</td>

																<td>
																	<select class="form-control"  title="Select Unit" placeholder="Unit" name="in_unit_id" id="in_unit_id" onchange="load_in_product_unit();load_in_product_stock();">
                                                                	
                                                                	<option value="0">Select Unit</option>
                                                            		</select>
                                                            	</td>
																<td>
																	<div id="convert_in_unit_block" style="display: none;">
			                                                            <input type="text" title="Enter Qty" min="0" id="product_in_conv_qty" name="product_in_conv_qty" class="form-control numbersOnly" onkeyup="product_convert_in_qty(1);" >
			                                                            <input type="hidden" name="conv_in_unitid" id="conv_in_unitid" value="">
			                                                            <input type="hidden" id="product_conv_in_qtyh" name="product_conv_in_qtyh" value="">
			                                                            <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_in_unit_show">  </span>
			                                                        </div>

			                                                        <div id="base_in_unit_block" style="">
			                                                            <input type="text" title="Enter Qty" min="0" id="product_in_qty" name="product_in_qty" class="form-control numbersOnly" onkeyup="product_convert_in_qty(2);">
			                                                            <input type="hidden" name="in_unitid" id="in_unitid" value="">
			                                                            <input type="hidden" id="product_in_qtyh" name="product_in_qtyh" value="">
			                                                            <span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="in_unit_show">  </span>
			                                                        </div>
																	
																</td>
																<td>
																	<div id="base_rate_block">
																		<input type="text" name="product_in_rate" id="product_in_rate" class="form-control numbersOnly" onchange="convert_rate()" onkeyup="convert_rate()">	
																	</div>
																	
																	<div id="conv_rate_block" style="display:none;">
																		<input type="text" name="product_in_conv_rate" id="product_in_conv_rate" class="form-control numbersOnly" onchange="convert_rate()" onkeyup="convert_rate()">
																	</div>
																	
																</td>
																<td>
																	<input type="hidden" name="edit_in_id" id="edit_in_id">
																	
																	<input type="button" id="in_st" value="Add" style="display:none;" class="btn btn-primary" onclick="add_field_in()"> 
																	
																	<input type="button" id="batch_wise_in_st" value="Add" style="display: block;" class="btn btn-primary" onclick="batch_wise_in_stock_open()">
																</td>
															</tr>

														</tbody>		
													</table>

													<div class="col-lg-12" style="margin-top:10px" id="stock_in_detail"></div>
													

												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div> 
												</div>
											</div>
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$general_stock_id?>' />
											<input type="hidden" id="isbatchwise" name="isbatchwise" value="">
											<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
											
											<div class="clearfix"></div>	
											<div class="col-md-12" style="margin-top:10px;text-align: center;">
												<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
												
												<a href="<?=ROOT.INVENTORY_ROOT.'stock_general_list'?>" type="button" class="btn btn-danger">Cancel</a>
											</div>
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
			<?php //include_once($include1.'add_cust.php');?>
			<?php //include_once($include1.'add_person.php');?>
			<?php //include_once($include1.'add_return_date.php');?>
			<?php //include_once($include1.'preview_cust_person_dtl.php');?>
			<?php //include_once($include1.'preview_cust_dtls.php');?>
			<?php //include_once($include1.'add_batch_wise_qty.php');?>
		</section>
		 
		<?php include_once($include1.'add_batch_data.php');?>   
		<?php include_once($include1.'add_batch_data_in.php');?>   
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/stock_general.js?<?=time()?>"></script>
		<!-- <script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script> -->
		<script>
			$(".select2").select2({
				width: '100%'
			});

			$("#item_id").select2({
				width: '100%',
				minimumInputLength: 3
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
			var max_followup_date = '<?=$max_followup_date?>';
         	var date = new Date();
         	var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
         	var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //end date should not greater than 15 days
	         $(".form_datetime-meridian").datetimepicker({
	           format: "dd-mm-yyyy HH:ii P",
	           showMeridian: true,
	           autoclose: true,
	           todayBtn: true,
	           pickerPosition: "bottom-left",
	           startDate: today,
	           endDate: endDate
	       }); 
         
		</script> 
	</body>
</html>