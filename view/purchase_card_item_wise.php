<?php 
	 session_start();
	 include_once("../config/config.php");
	 include_once("../config/session.php");
	 
	 include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	 include_once("../include/function_database_query.php");
	 $form="Purchase Card Item";
	 $infopage = pathinfo( __FILE__ );
	 $_SESSION['page']=$infopage['filename'];
	 
	 $currency_id=$_SESSION['currency_id'];
	 $quotation_date='d-m-Y';
	 
	 $back="purchase_card_list";
	 $mode="Add";$direct_add='0';$request=0;
	 $purchasecard_date=date('d-m-Y');
	 $affected_date = date('d-m-Y');
	 $po_type_status='';
	 $vender_id = $_SESSION['selected_purchase_vendor'];
	 $purchase_type = $_SESSION['purchase_type']; 
	 $product_type = $_SESSION['selected_product_type'];
	 $product_id = $_SESSION['selected_purchase_item'];
	 $product_name = $_SESSION['selected_product_name'];
	 //selected_purchase_item
	 
	 if($purchase_type=='' || $product_id==''){
		 header('location:'.ROOT.'purchase_card_item');
		 exit;
	 }
	 $sql = "SELECT `ppp`.`party_purchase_id`,`ppp`.`party_id`, `ppp`.`party_rate`,`v`.`l_name`,`c`.`city_name`  FROM `tbl_product_party_purchase`       as ppp 
				 left join tbl_ledger as v ON `ppp`.`party_id`=`v`.`l_id` 
				 left join  city_mst as c ON `v`.`cityid` = `c`.`cityid` 
				 WHERE `ppp`.`party_product` = '".$product_id."' ORDER BY `ppp`.`party_purchase_id` DESC ";
	 
	 $result=$dbcon->query($sql);
	 
?>
<!DOCTYPE html>
<html lang="en">
	 <head>
			<?php include_once('../include/include_css_file.php');?>
	 </head>
	 <body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
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
										<li><a href="<?=$_SESSION['purchase_card_main_list']?>"><?=$form?> Wise</a></li>
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
									<?php if($purchasecard_id!=''){ ?>
									<span class="tools pull-right">
										<a href="<?=ROOT.'purchase_card'?>"><button class="btn btn-success btn-flat">Add Purchase Card</button></a>
									</span>
									<?php } ?>
								</header>
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="purchasecard_add" action="javascript:;" method="post" name="purchasecard_add">
										<input id="purchasecard_no" name="purchasecard_no" type="hidden" class="form-control" title="Date" value="<?=$rel['purchasecard_no']?>" placeholder="Purchase Order No" <?=$readonly?>>
										<input type="hidden" name="product_id" id="product_id" value="<?=$product_id?>">
										<input type="hidden" name="purchase_type" id="purchase_type" value="<?=$purchase_type?>">
										<input type="hidden" name="vendor_id" id="vendor_id" value="">
										<div class="row">
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label"  style="text-align:left">Selected Product:</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" value="<?=trim($product_name)?>" readonly>
														</div>
													</div>
												</div>
											</div>
										</div>
										<hr>
										
										<section class="panel" >
											<div class="panel-body bio-graph-info">
												<div class="col-md-12 margin_row">
													<div class="col-md-6 bio-graph-info" >
														<div class="row">
															<div style="height:300px; overflow:auto;overflow-x: hidden;" class="adv-table" id="list_table_div">
															<table class="display table table-bordered table-striped" id="vendor_table">
															<thead>
																<tr>
																	<th>Vendor Name</th>
																	<th>Vendor Code</th>
																	<th>City</th>
																	<th>Make</th>
																</tr>
															</thead>
															<tbody>
															<?php 
																if(mysqli_num_rows($result)>0){
																$i=0;  
																while($rel=mysqli_fetch_assoc($result))
																{ 
																																																/*if($rel['currency_id']!='0'){
																																																	$currency_id = $rel['currency_id'];
																	} */
																	$getItem = getItemPriceByProductId($dbcon,  $product_id ,$rel['party_id']); // parameters (connection, productid, vendorid)
																	if($getItem){
																		$user_name = $getItem['user_name']; 
																	}else{
																		$user_name = ''; 
																	}
																?>
																<tr onclick="get_item_information('<?=$rel['party_id']?>', '<?=$product_id?>', '<?=$purchase_type?>')" class="vendor_<?=$i?>">
																	<td><?=$rel['l_name']?></td>
																	<td></td>
																	<td><?=$rel['city_name']?></td>
																	<td><?=$user_name?></td>
																</tr>
															<?php $i++;} }?> 
														 </tbody>
													</table>
													</div>
												</div>
											</div>
											<?php if(mysqli_num_rows($result)>0){ ?>
												<div class="col-md-6 bio-graph-info">
													<div class="row">
													<h1>Purchase Rate Details</h1>
																					 <div class="col-md-12">
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Currency</label>
																										<div class="col-md-9 col-xs-11">
																											 <select class="select2" name="currency_id" id="currency_id"  required title="Select Currency" disabled="true">
																											 <?=getcurrency($dbcon,$currency_id);?> 
																											 </select>
																											 <input type="hidden" id="currency_id" name="currency_id" value="<?=$currency_id?>">
																										</div>
																								 </div>
																							</div>
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Rate *</label>
																										<div class="col-md-9 col-xs-11">
																											 <input id="price" name="price" type="number" class="form-control" title="Rate" value="<?=$rel['price']?>" placeholder="Rate" >
																										</div>
																								 </div>
																							</div>
																					 </div>
																					 <div class="col-md-12">
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Rate Tolerance (%) *</label>
																										<div class="col-md-9 col-xs-11">
																											 <input id="rate_tolerance" name="rate_tolerance" type="number" class="form-control" title="Rate Tolerance" maxlength="2" value="<?=$rel['rate_tolerance']?>" placeholder="Rate Tolerance" >
																										</div>
																								 </div>
																							</div>
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">G Rate *</label>
																										<div class="col-md-9 col-xs-11">
																											 <input id="grate" name="grate" type="number" class="form-control" title="Grate" maxlength="100" value="" placeholder="GRate" >
																										</div>
																								 </div>
																							</div>
																					 </div>
																					 <div class="col-md-12">
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Disc(%) *</label>
																										<div class="col-md-9 col-xs-11">
																											 <input id="discount_percentage" name="discount_percentage" type="number" class="form-control" title="Discount Percentage" maxlength="2" value="<?=$rel['discount_percentage']?>" placeholder="Discount Percentage" >
																										</div>
																								 </div>
																							</div>
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Lead Time</label>
																										<div class="col-md-9 col-xs-11">
																											 <input id="lead_time" name="lead_time" type="number" class="form-control" title="Lead Time" maxlength="10" value="" placeholder="Lead Time" >
																										</div>
																								 </div>
																							</div>
																					 </div>
																					 <div class="col-md-12">
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Date </label>
																										<div class="col-md-9">
																											 <input id="affected_date" name="affected_date" type="text" class="form-control default-date-picker" title="Date" value="<?php echo $affected_date; ?>" placeholder="Affected Date">
																										</div>
																								 </div>
																							</div>
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Qtn No </label>
																										<div class="col-md-9">
																											 <input id="quotation_no" name="quotation_no" type="number" class="form-control" title="Date" value="" placeholder="Quotation No">
																										</div>
																								 </div>
																							</div>
																					 </div>
																					 <div class="col-md-12">
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Quotation Date </label>
																										<div class="col-md-9">
																											 <input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$rel['quotation_date']?>" placeholder="Date" >
																										</div>
																								 </div>
																							</div>
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Item Make</label>
																										<div class="col-md-9">
																											 <input id="item_make" name="item_make" type="text" class="form-control" title="Item make" value="" placeholder="Item make">
																										</div>
																								 </div>
																							</div>
																					 </div>
																					 <div class="col-md-12">
																							<div class="col-md-6">
																								 <div class="form-group">
																										<label class="col-md-3 control-label" style="text-align: left">Created By </label>
																										<div class="col-md-9">
																											 <input id="user_name" name="user_name" type="text" class="form-control" title="Date" value="<?=$rel['user_name']?>" placeholder="User name" readonly>
																										</div>
																								 </div>
																							</div>
																					 </div>
																					 <div class="row">
																							<?php if($purchasecard_id==''){ ?>
																							<button type="submit" class="btn btn-success" id="save" name="save">Change Rate</button>
																							<?php } ?>
																							<a href="<?=ROOT.'purchase_card_item'?>" type="button" class="btn btn-danger">Cancel</a>
																							<div class="col-md-3"></div>
																					 </div>
																				</div>
																		 </div>
																		 <?php } ?>
																	</div>
															 </div>
														</section>
														<!-- Tab Section Start By Umair -->
														<section class="panel" style="margin-top: 15px">
															 <header class="panel-heading tab-bg-dark-navy-blue ">
																	<ul class="nav nav-tabs">
																		 <li class="active">
																				<a data-toggle="tab" href="#po_listing_info"  aria-expanded="true">Item Details</a>
																		 </li>
																		 <li class="">
																				<a data-toggle="tab" href="#po_vendor_details" onClick="get_vendor_details('po_vendor_details')" aria-expanded="false">Party Details</a>
																		 </li>
																		 <li class="">
																				<a data-toggle="tab" href="#po_billing_terms" onClick="get_vendor_details('po_billing_terms')" aria-expanded="false">Billing Terms</a>
																		 </li>
																		 <li class="">
																				<a data-toggle="tab" href="#po_terms_cond" aria-expanded="false">Terms & Condition</a>
																		 </li>
																	</ul>
															 </header>
															 <div class="panel-body">
																	<div class="tab-content">
																		 <div id="po_listing_info" class="tab-pane active" >
																				<div class="panel-body">
																					 <div class="row" id="existing_item_div">
																							<section class="panel" >
																								 <div class="panel-body bio-graph-info">
																										<div class="col-md-12 margin_row">
																											 <div class="col-md-6 bio-graph-info">
																													<div class="row">
																														 <h1>Item Details</h1>
																														 <div class="form-group">
																																<label class="col-md-3 control-label" style="text-align: left">Item Code</label>
																																<div class="col-md-6">
																																	 <input type="text" value="" name="product_icode" id="product_icode" class="form-control" readonly>
																																</div>
																														 </div>
																														 <div class="form-group">
																																<label class="col-md-3 control-label" style="text-align: left">Item Description</label>
																																<div class="col-md-6">
																																	 <input type="text" value="" name="product_desc" id="product_desc" class="form-control" readonly>
																																</div>
																														 </div>
																														 <div class="form-group">
																																<label class="col-md-3 control-label" style="text-align: left">Add. Desc.</label>
																																<div class="col-md-6">
																																	 <input type="text" value="" name="additional_product_desc" id="additional_product_desc" class="form-control" readonly>
																																</div>
																														 </div>
																														 <div class="form-group">
																																<label class="col-md-3 control-label" style="text-align: left">Drawing Number</label>
																																<div class="col-md-6">
																																	 <input type="text" value="" name="drawing_number" id="drawing_number" class="form-control" readonly>
																																</div>
																														 </div>
																														 <div class="form-group">
																																<label class="col-md-3 control-label" style="text-align: left">UOM</label>
																																<div class="col-md-6">
																																	 <input type="text" value="" name="unit_name" id="unit_name" class="form-control" readonly>
																																</div>
																														 </div>
																														 <div class="form-group">
																																<label class="col-md-3 control-label" style="text-align: left">Rev</label>
																																<div class="col-md-6">
																																	 <input type="text" value="" name="rev" id="rev" class="form-control" readonly>
																																</div>
																														 </div>
																													</div>
																											 </div>
																											 <div class="col-md-6 bio-graph-info">
																											 </div>
																										</div>
																								 </div>
																							</section>
																					 </div>
																				</div>
																		 </div>
																		 <!-- <div id="po_items" class="tab-pane"></div> -->
																		 <div id="po_vendor_details" class="tab-pane"></div>
																		 <div id="po_billing_terms" class="tab-pane"></div>
																		 <div id="po_terms_cond" class="tab-pane">
																				<div class="row">
																					 <div class="form-group">
																							<label class="col-md-3 control-label">Terms Condition</label>
																							<div class="col-md-9 col-xs-11">
																								 <textarea class="form-control" placeholder="Terms Condition" name="terms_condition" id="terms_condition" ></textarea>
																							</div>
																					 </div>
																				</div>
																		 </div>
																	</div>
															 </div>
														</section>
														<!-- Tab Section -->
														<!--Vendor row end--> 
														<input type='hidden' name='po_request' id='po_request' value='<?=$request?>' />
														<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
														<input type='hidden' name='eid' id='eid' value='<?=$purchasecard_id;?>' />  
														<input type='hidden' name='back' id='back' value='<?=$back;?>' /> 
														<?php 
															 if($direct_add=='1'){
															 ?>   
														<input type="hidden" name="po_ref_id" id="po_ref_id" value="<?=$rel['purchasecard_id']?>" />
														<?php } ?>  
												 </form>
											</div>
									 </section>
								</div>
						 </div>
						 <!--state overview end-->
					</section>
				 </section>
				 <!--main content end-->
				 <!--footer start-->
				 <?php include_once('../include/footer.php');?>
				 <!--footer end-->
			</section>
			<!-- js placed at the end of the document so the pages load faster -->
			<?php include_once('../include/include_js_file.php');?>   
			<script src="<?=ROOT?>js/app/purchase_card_item.js?<?=time()?>"></script>
			<script>
				 /*$("#party_product").select2({
					width: '100%',
					minimumInputLength: 2,
				 });  */
				 $(".select2").select2({
					 width: '100%',
					 // minimumInputLength: 2,
				 });
				 CKEDITOR.replace( 'terms_condition', {
					 enterMode: CKEDITOR.ENTER_BR
				 });
				 /*$("#product_id").select2({
					 width: '86%'
				 });*/
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
				 
				 if($('#eid').val()==''){
					 change_supply_type('<?=$purchase_type?>');
				 }
				 function change_supply_type(str){
						if(str=='0'){
							 $('.item_specified_class').attr('disabled', 'disabled');
							 $('.vendor_specified_class').removeAttr("disabled");
						}else if(str=='1'){
							 $('.vendor_specified_class').attr('disabled', 'disabled');
							 $('.item_specified_class').removeAttr("disabled");
						}else{
							 //$('.vendor_class').attr('disabled', 'disabled');
						}
				 }
				 
				 $('tr').not(':first').click(function () {
							$(this).addClass("active"); //add class selected to current clicked row
							$(this).siblings().removeClass( "active" ); //remove class selected from rest of the rows
					 });
				 
				 $(document).ready(function(){
						 $('.vendor_0').click();
				 });
				 
				 $("#vendor_table").dataTable({
						"bPaginate": false,
						"aaSorting": [],
						"bInfo" : false
						/*"oLanguage": {
							 "sSearch": "Filter records:"
						 }*/
				 });
			</script>
			<?php 
				 //echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
				 //echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
				 if($mode=="Add"){
					//echo "<script>show_data();</script>";
					echo "<script>get_series_no(16);</script>";
				 }
				 ?>
	 </body>
</html>