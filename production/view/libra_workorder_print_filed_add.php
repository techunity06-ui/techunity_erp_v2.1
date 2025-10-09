<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="Workorder Print Fields";
	
	$workorder_id = $dbcon->real_escape_string($_REQUEST['id']);
	$del_dua_date=date('d-m-Y'); 
	$query =  "SELECT * FROM tbl_libra_workorder_fields where workorder_id = " . $workorder_id;
	$result = $dbcon->query($query);
	$rel = brp_mysqli_fetch_assoc($result);
	if(brp_mysqli_num_rows($result) > 0){
		$del_dua_date=date('d-m-Y',strtotime($rel['del_dua_date'])); 
	}

	$mode = "add";
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>GRN Add</title>
		<?php include_once($include.'include_css_file.php');?>
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
										<li><a href="<?=ROOT.PRODUCTION_ROOT.'work_order'?>"> Workorder List</a></li>
										<li><?=$form?></a></li>
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
									<form class="form-horizontal" role="form" id="workorder_data_add" action="javascript:;" method="post" name="workorder_data_add" enctype="multipart/form-data">
										<div class="row"> 
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Customer Po Item</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="po_item" name="po_item" class="form-control" title="Customer Po Item" value="<?=$rel['po_item']?>" placeholder="Customer Po Item" >
														</div>
													</div>
												</div>	

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Customer Po Item Sr.#</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="po_item_sr" name="po_item_sr" class="form-control" title="Customer Po Item Sr.#" value="<?=$rel['po_item_sr']?>" placeholder="Customer Po Item Sr.#" >
														</div>
													</div>
												</div>	
												
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Ref. Approved Datasheet#</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="datasheet" name="datasheet" class="form-control" title="Ref. Approved Datasheet#" value="<?=$rel['datasheet']?>" placeholder="Ref. Approved Datasheet#">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Ref. Approved GAD#</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="gad" name="gad" class="form-control" title="Ref. Approved GAD#" value="<?=$rel['gad']?>" placeholder="Ref. Approved GAD#">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Ref. Approved QAP#</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="qap" name="qap" class="form-control" title="Ref. Approved QAP#" value="<?=$rel['qap']?>" placeholder="Ref. Approved QAP#">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Valve Type</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="valve_type" name="valve_type" class="form-control" title="Valve Type" value="<?=$rel['valve_type']?>" placeholder="Valve Type">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Size & Class</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="size_class" name="size_class" class="form-control" title="Size & Class" value="<?=$rel['size_class']?>" placeholder="Size & Class">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">QSL#</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="qsl" name="qsl" class="form-control" title="QSL#" value="<?=$rel['qsl']?>" placeholder="QSL#">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Qty.</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="qty" name="qty" class="form-control" title="Qty." value="<?=$rel['qty']?>" placeholder="Qty.">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Valve Sr.#</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="valve_sr" name="valve_sr" class="form-control" title="Valve Sr.#" value="<?=$rel['valve_sr']?>" placeholder="Valve Sr.#">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">MOC</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="moc" name="moc" class="form-control" title="MOC" value="<?=$rel['moc']?>" placeholder="MOC">
														</div>
													</div>
												</div>

												

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Service</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="service" name="service" class="form-control" title="Service" value="<?=$rel['service']?>" placeholder="Service">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Design Standard</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="design_standard" name="design_standard" class="form-control" title="Design Standard" value="<?=$rel['design_standard']?>" placeholder="Design Standard">
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Testing Standard</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="testing_standard" name="testing_standard" class="form-control" title="Testing Standard" value="<?=$rel['testing_standard']?>" placeholder="Testing Standard">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Specific Mfg. Req.</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="mfg_req" name="mfg_req" class="form-control" title="Specific Mfg. Req." value="<?=$rel['mfg_req']?>" placeholder="Specific Mfg. Req.">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Specific Test. Req.</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="test_req" name="test_req" class="form-control" title="Specific Test. Req." value="<?=$rel['test_req']?>" placeholder="Specific Test. Req.">
														</div>
													</div>
												</div>
												
												

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">TPISCOPE</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="tpi_scope" name="tpi_scope" class="form-control" title="TPISCOPE" value="<?=$rel['tpi_scope']?>" placeholder="TPISCOPE">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">After Sales Service Req</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="sales_service_req" name="sales_service_req" class="form-control" title="After Sales Service Req" value="<?=$rel['sales_service_req']?>" placeholder="After Sales Service Req">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Coating / Painting Req.</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="coating_painting_req" name="coating_painting_req" class="form-control" title="Coating / Painting Req." value="<?=$rel['coating_painting_req']?>" placeholder="Coating / Painting Req.">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Packing Req.</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="packing_req" name="packing_req" class="form-control" title="Packing Req." value="<?=$rel['packing_req']?>" placeholder="Packing Req.">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Marking On Product</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="marking_on_product" name="marking_on_product" class="form-control" title="Marking On Product" value="<?=$rel['marking_on_product']?>" placeholder="Marking On Product">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Marking On Packing</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="marking_on_packing" name="marking_on_packing" class="form-control" title="Marking On Packing" value="<?=$rel['marking_on_packing']?>" placeholder="Marking On Packing">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">API Monogram Marking</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="api_monogram_marking" name="api_monogram_marking" class="form-control" title="API Monogram Marking" value="<?=$rel['api_monogram_marking']?>" placeholder="API Monogram Marking">
														</div>
													</div>
												</div>

												

												<div class="col-md-4">  	
													<div class="form-group">  	
														<label class="col-md-4 control-label">Delivery Due Date</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="del_dua_date" name="del_dua_date" class="form-control default-date-picker" title="Date" value="<?=$del_dua_date?>" placeholder="Delivery Due  Date">
														</div>
													</div>	
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Customer Contact Details</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="customer_cont_details" name="customer_cont_details" class="form-control" title="Customer Contact Details" value="<?=$rel['customer_cont_details']?>" placeholder="Customer Contact Details">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Delivery Location</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="del_location" name="del_location" class="form-control" title="Delivery Location" value="<?=$rel['del_location']?>" placeholder="Delivery Location">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Documents to be Submit</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="documents" name="documents" class="form-control" title="Documents to be Submit" value="<?=$rel['documents']?>" placeholder="Documents to be Submit">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Payment Temrs</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="payment_terms" name="payment_terms" class="form-control" title="Payment Temrs" value="<?=$rel['payment_terms']?>" placeholder="Payment Temrs">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Insurance</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="insurance" name="insurance" class="form-control" title="Insurance" value="<?=$rel['insurance']?>" placeholder="Insurance">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Freight</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="freight" name="freight" class="form-control" title="Freight" value="<?=$rel['freight']?>" placeholder="Freight">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Additional Req.</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="additional_req" name="additional_req" class="form-control" title="Additional Req." value="<?=$rel['additional_req']?>" placeholder="Additional Req.">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Prepared By</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="prepared_by" name="prepared_by" class="form-control" title="Prepared By" value="<?=$rel['prepared_by']?>" placeholder="Prepared By">
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Approved By</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="approved_by" name="approved_by" class="form-control" title="Approved By" value="<?=$rel['approved_by']?>" placeholder="Approved By">
														</div>
													</div>
												</div>
											</div>
									
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='edit_id' id='edit_id' value='<?=$rel['id']?>' />
											<input type='hidden' name='workorder_id' id='workorder_id' value='<?=$workorder_id?>' />
											
											<div class="clearfix"></div>	
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?=ROOT.PRODUCTION_ROOT.'work_order'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/libra_workorder_print_filed_add.js?<?=time()?>"></script>
		<script>
			//$('#container').addClass('sidebar-closed');
			$(".select2").select2({
				width: '100%'
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
			
		</script> 
	</body>
</html>