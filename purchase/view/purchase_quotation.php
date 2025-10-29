<?php
session_start();
include('../include/urlfile.php');
$form = "Request For Quotation";
$company_config = getCompanyConfiguration($dbcon);
 error_reporting(E_ALL);
if (strpos($_SERVER['REQUEST_URI'], "purchase_quotation") !== false) {
	$quotation_ref_id = $dbcon->real_escape_string(string: $_REQUEST['id']);
    $query = "SELECT * FROM po_quotation_ref WHERE quotation_ref_id = " . $quotation_ref_id;
	$result = $dbcon->query($query);

    if ($result && $row = brp_mysqli_fetch_array($result)) {
		 $ref_quotation_no = $row['ref_quotation_no']; 
        if (!empty($row['vender_id'])) {
            $suppliers_detail = "
                SELECT GROUP_CONCAT(l_name) AS suppliers 
                FROM tbl_ledger 
                WHERE l_id IN (" . $row['vender_id'] . ")";
			$supplier_result = $dbcon->query($suppliers_detail);
			$supplier_data = brp_mysqli_fetch_array($supplier_result);
        } else {
            // No vendor IDs
            $supplier_data['suppliers'] = '';
        }
    }
}


$companyConfiguration = getCompanyConfiguration($dbcon);
$purchase_party_show = $companyConfiguration['purchase_party_show'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Purchase Quotation</title>
	<?php include_once($include . '/include_css_file.php'); ?>
	<style type="text/css">
		.wizard>.content {
			overflow-y: scroll;
		}

		fieldset {
			background-color: #eeeeee;
		}

		legend {
			background-color: gray;
			color: white;
			padding: 5px 10px;
		}

		input {
			margin: 5px;
		}

		input[type="radio"] {
			appearance: none;
			border: 1px solid #d3d3d3;
			width: 30px;
			height: 30px;
			content: none;
			outline: none;
			margin: 0;
			box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
		}

		input[type="radio"]:checked {
			appearance: none;
			outline: none;
			padding: 0;
			content: none;
			border: none;
		}

		input[type="radio"]:checked::before {
			position: absolute;
			color: green !important;
			content: "\00A0\2713\00A0" !important;
			border: 1px solid #d3d3d3;
			font-weight: bolder;
			font-size: 17px;
		}
	</style>
</head>

<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include . '/include_top_menu.php'); ?>
		<?php include_once($include . '/left_menu.php'); ?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3> <?php echo $form . ' ' . $mode ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?php echo ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?php echo ROOT . PURCHASE_ROOT . 'po_quotation_list_new' ?>"><?php echo $form ?> List</a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								PO Quotation
							</header>
							<div class="panel-body">
								<form class="form-horizontal" role="form" id="purchase_quotation" action="javascript:;" method="post" name="purchase_quotation">
									<div class="container">
										<ul class="nav nav-tabs">
											<li class="active"><a data-toggle="tab" href="#req_quot">Request For Quotation</a></li>
											<li><a data-toggle="tab" href="#supplier_quot">Supplier Quotation</a></li>
											<li><a data-toggle="tab" href="#quot_comparision">Quotation Comparision</a></li>
										</ul>

										<div class="tab-content">
											<div id="req_quot" class="tab-pane fade in active">
												<div class="row" style="margin-top: 30px;">
													<div class="col-md-12">
														<ul class="nav navbar-nav navbar-right">
														<li>
    <button type="button"
            class="btn btn-success"
            onclick="window.open('<?php echo ROOT; ?>print/request_quo_print/<?php echo $_REQUEST['id']; ?>', '_blank')">
        <i class="fa fa-print"></i>&nbsp;Print
    </button>&nbsp;
</li>

															<li><button class="btn btn-primary addmode" onclick="mode_change_req_quot()">Edit</button>&nbsp;</li>
															<li><button class="btn btn-success editmode1" onclick="request_quotation_data()">Submit</button>&nbsp;</li>
														</ul>
													</div>
												</div>
												<div class="row">
													<fieldset>
														<legend>Main Details :</legend>
														<div class="col-md-12">
															<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-4 control-label">Ref. Quotation No</label>
																	<div class="col-md-8 col-xs-11">
																		<strong><?php echo $ref_quotation_no ?></strong>
																	</div>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-4 control-label">Ref. Quotation Date</label>
																	<div class="col-md-8 col-xs-11">
																		<strong><?php echo date('d-m-Y', strtotime($row['ref_quotation_date'])) ?></strong>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12">
															<div class="col-md-6 clearfix">
																<div class="form-group">
																	<label class="col-md-4 control-label">Suppliers</label>
																	<div class="col-md-8 col-xs-11">
																		<div class="addmode">
																			<strong id="suppliers"><?php echo $supplier_data['suppliers'] ?></strong>
																		</div>
																		<div class="editmode1">
																			<select class="select2" name="supplier_id" id="supplier_id" multiple>
																				<?php echo getcust($dbcon, $vender_id, $purchase_party_show); ?>
																			</select>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</fieldset>

													<fieldset>
														<legend>Item Details :</legend>
														<div class="row">
															<div class="col-md-12 editmode1">
																<ul class="nav navbar-nav navbar-right" style="padding-right: 15px;">
																	<li><button class="btn btn-warning" onclick="quotation_item_edit('po_quotationtrn_ref','chk_box_item_req','request_for_quotation')"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>&nbsp;</li>
																	<li><button class="btn btn-danger" onclick="quotation_item_delete('po_quotationtrn_ref','chk_box_item_req','request_for_quotation')"><i class="fa fa-trash-o" aria-hidden="true"></i></button>&nbsp;</li>
																</ul>
															</div>
														</div>
														<div id="request_for_quotation">

														</div>
													</fieldset>
													<input type="hidden" name="rq_mode" id="rq_mode" value="add">
												</div>
											</div>


											<div id="supplier_quot" class="tab-pane">
												<div class="row" style="margin-top: 30px;">
													<div class="col-md-12">
														<ul class="nav navbar-nav navbar-right">
															<li><button onclick="supplier_quotation_data()" class="btn btn-success">Submit</button>&nbsp;</li>
														</ul>
													</div>
												</div>
												<div class="row">
													<fieldset>
														<legend>Main Details :</legend>
														<div class="col-md-12">
															<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-4 control-label">Ref. Quotation No</label>
																	<div class="col-md-8 col-xs-11">
																		<strong><?php echo $ref_quotation_no ?></strong>
																	</div>
																</div>
															</div>
															<div class="col-md-6">
																<div class="form-group">
																	<label class="col-md-4 control-label">Ref. Quotation Date</label>
																	<div class="col-md-8 col-xs-11">
																		<strong><?php echo date('d-m-Y', strtotime($row['ref_quotation_date'])) ?></strong>
																	</div>
																</div>
															</div>
														</div>
														<div class="col-md-12">
															<div class="col-md-6 clearfix">
																<div class="form-group">
																	<label class="col-md-4 control-label">Choose Party</label>
																	<div class="col-md-8 col-xs-11">
																		<select class="select2" name="vender_id" id="vender_id" onchange="load_party_quotation_product();load_supplier_detail()">
																			<option value="">Choose Vendor</option>
																		</select>
																	</div>
																</div>
															</div>
															<div class="col-md-6 clearfix">
																<div class="form-group">
																	<label class="col-md-4 control-label">Quotation No</label>
																	<div class="col-md-8 col-xs-11">
																		<input type="text" name="quotation_no" id="quotation_no" class="form-control" placeholder="Quotation No" title="Quotation No">
																	</div>
																</div>
															</div>
														</div>

														<div class="col-md-12">
															<div class="col-md-6 clearfix">
																<div class="form-group">
																	<label class="col-md-4 control-label">Quotation Date *</label>
																	<div class="col-md-8 col-xs-11">
																		<input type="text" class="form-control default-date-picker" name="quotation_date" id="quotation_date" placeholder="Quotation Date" title="Quotation Date" value="">
																	</div>
																</div>
															</div>
															<div class="col-md-6 clearfix">
																<div class="form-group">
																	<label class="col-md-4 control-label">Delivery Priode (in Days)</label>
																	<div class="col-md-8 col-xs-11">
																		<input type="number" class="form-control" name="delivery_priode" id="delivery_priode" placeholder="Delivery Priode" title="Delivery Priode" value="">
																	</div>
																</div>
															</div>
														</div>

														<div class="col-md-12">
															<div class="col-md-6 clearfix">
																<div class="form-group">
																	<label class="col-md-4 control-label">Ex-Delivery</label>
																	<div class="col-md-8 col-xs-11">
																		<input type="text" class="form-control" name="ex_delivery" id="ex_delivery" placeholder="Ex-Delivery" title="Ex-Delivery" value="">
																	</div>
																</div>
															</div>
														</div> 
														
													</fieldset>

													<fieldset style="margin-top: 10px;">
														<legend>Item Details :</legend>
														<div class="row">
															<!-- <div class="col-md-12 ">
																<ul class="nav navbar-nav navbar-right" style="padding-right: 15px;">
																	<li><button class="btn btn-warning" onclick="quotation_item_edit('po_quotationtrn_ref','chk_box_item_req12','supplier_quotation')"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>&nbsp;</li>
																	<li><button class="btn btn-danger" onclick="quotation_item_delete('po_quotationtrn_ref','chk_box_item_req12','supplier_quotation')"><i class="fa fa-trash-o" aria-hidden="true"></i></button>&nbsp;</li>
																</ul>
															</div> -->
														</div>
														<div id="supplier_quotation">

														</div>
													</fieldset>
													
													<fieldset style="margin-top: 10px;">
														<legend>Other Details :</legend>
														<div class="col-md-6 clearfix">
															<div class="form-group">
																<label class="col-md-4 control-label">Discount (in %)</label>
																<div class="col-md-8 col-xs-11">
																	<input type="number" name="discount" id="discount" class="form-control" placeholder="Discount" title="Discount" value="0" onChange="update_gtotal();">
																</div>
															</div>
														</div>
														<div class="col-md-6 clearfix">
															<div class="form-group">
																<label class="col-md-4 control-label">Payment Terms</label>
																<div class="col-md-8 col-xs-11">
																	<textarea name="payment_terms" id="payment_terms" class="form-control" placeholder="Payment Terms" title="Payment Terms"></textarea>
																</div>
															</div>
														</div>
														<div class="col-md-12" style="align-items: center;display: flex;">
														    <div class="col-md-2 col-xs-11" style="text-align: right;">
        													    <label class="control-label">Select Bill Sundry</label>
        													</div>
        													<div class="col-md-4 col-xs-11">
        														<?php $get_bill_sundry = get_bill_sundry_ledger($dbcon, 0); ?>
        														<select class="form-control" name="bill_sundry" id="bill_sundry" onchange="get_sundry_label(this.value)">
        															<option value="0">Select</option>
        															<?php foreach ($get_bill_sundry as $sundry) {
        
        															?>
        																<option value="<?php echo $sundry['l_id'] ?>"><?php echo $sundry['l_name']; ?></option>
        
        															<?php } ?>
        														</select>
        													</div>
        													<div class="col-md-2 col-xs-11">
        														<input id="bill_sundry_amount" name="bill_sundry_amount" type="text" class="form-control numbersOnly" placeholder="Amount" title="Amount" value="<?php echo $rel['amount'] ?>" placeholder="">
        													</div>
        													<div class="col-md-2 col-xs-11">
        														<button style="" class="btn btn-round btn-info" type="button" value="R1" onclick="addBillSundry()"><i class="fa fa-plus"></i></button>
        													</div>
        												</div>
        												
        												<div class="all_charges">
                                                            <table class="display table table-bordered table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center" style="width:50%">Title</th>
                                                                        <th class="text-center" style="width:25%">Amount</th>
                                                                        <th class="text-center" style="width:25%">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="charges_table_body">
                                                                    <!-- Fixed Sub Total row -->
                                                                    <tr class="sub_total_row">
                                                                        <td class="text-center"><b>Sub Total</b></td>
                                                                        <td class="text-center sub_total">0.00</td>
                                                                        <td class="text-center"></td>
                                                                    </tr>
                                                                    
                                                                    <!-- Fixed Discount row -->
                                                                    <tr class="discount_row">
                                                                        <td class="text-center"><b>Discount</b></td>
                                                                        <td class="text-center discount">0.00</td>
                                                                        <td class="text-center"></td>
                                                                    </tr>
                                                                    
                                                                    <!-- Dynamic charge rows will be inserted here -->
                                                                    
                                                                    <!-- Fixed Grand Total row -->
                                                                    
                                                                    <tr class="grand_total_row">
                                                                        <td class="text-center"><b>Grand Total</b></td>
                                                                        <td class="text-center grand_total">0.00</td>
                                                                        <td class="text-center"></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>

													</fieldset>
													<input type="hidden" name="supplier_detail_id" id="supplier_detail_id">
													<input type="hidden" name="sup_mode" id="sup_mode" value="add">
												</div>
											</div>

											<div id="quot_comparision" class="tab-pane">
												<section style="margin-left:30px">
													<div class="col-md-12">
														<div class="col-md-6 clearfix">
															<div class="form-group">
																<label class="col-md-4 control-label">Compare</label>
																<div class="col-md-8 col-xs-11">
																	<select class="select2" name="comparision" id="comparision" onchange="quotation_compare()">
																		<option <?php if($row['comparision']==1){ echo "selected='selected'";}?> value="1">Quotation Wise</option>
																		<option <?php if($row['comparision']==2){ echo "selected='selected'";}?> value="2">Item Wise</option>
																	</select>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-12">
														<center>
															<h2 style="color:white;background-color:gray;">Quotation Comparision</h2>
														</center>
														<div id="quotation_comparision">

														</div>
														<center>
															<button class="btn btn-success" onclick="quotation_comparision_add()">Submit</button>
															<a href="<?php echo ROOT.PURCHASE_ROOT.'po_quotation_list_new'?>" class="btn btn-danger">Cancel</a>
														</center>
													</div>
												</section>
											</div>
										</div>
									</div>
									<input type="hidden" name="quotation_ref_id" id="quotation_ref_id" value="<?php echo $quotation_ref_id ?>">
									<input type="hidden" name="supplier_detail_id" id="supplier_detail_id">
								</form>
							</div>
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once($include1 . 'edit_quotation_trn_modal.php'); ?>
		<?php include_once($include . '/footer.php'); ?>
	</section>
	<?php include_once($include . '/include_js_file.php'); ?>
	<script src="<?php echo ROOT . PURCHASE_ROOT ?>js/app/po_quotation_list_new.js?<?php echo time() ?>"></script>


	<script>
		$(".select2").select2({
			width: '100%'
		});
		
		$("#bill_sundry").select2({
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
		load_req_quotation();
	</script>

</body>

</html>