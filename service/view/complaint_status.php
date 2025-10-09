<?php
session_start();
include('../include/urlfile.php');
$incPath = $path . 'include/';

$form = "Complaint Status";
$id = $_REQUEST['id'];
$rel = getComplainDetail($dbcon, $id);

$fstat = $rel['followup_status'];

$where_status = " and f_id='4' or f_id='5' or f_id='8'";

$where_product = " and product_type!='1'";

$osp_count = get_total_spare_count_old($dbcon, $id);

if ($rel['old_sp_part_status'] == 'yes') {
	if ($osp_count == '0') {
		$osp_count = "";
	} else {
		$osp_count_dis = $osp_count;
	}
} else {
	$osp_count_dis = "0";
}

$req_count = get_total_spare_count_request($dbcon, $id);

$user_id = $_SESSION['user_id'];
$empl_id = getEmployeeIdUser($dbcon, $user_id);

$service_charge = get_service_charge($dbcon, $id);
$spare_charge = get_spare_part_rate($dbcon, $id);


?>

<!DOCTYPE html>
<html lang="en">

<head>
	<?php include_once($incPath . 'include_css_file.php'); ?>
	<style>
		.mg10 {
			margin-left: 5px;
		}

		.sp_back {
			background-color: #337AB7 !important;
			color: #ffffff;
			font-weight: bold;
			text-align: center;
			font-size: 22px !important;
			text-transform: uppercase;
		}
	</style>
</head>

<body>
	<section id="container">

		<?php include_once($incPath . 'include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once($incPath . 'left_menu.php'); ?>
		<!--sidebar end-->
		<!--main content start-->

		<section id="main-content">
			<section class="wrapper">
				<div class="row">

					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3><?= 'Change ' . ' ' . $form ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . SERVICE_ROOT . 'complaint_list' ?>"><?= $form ?> List</a></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<!--state overview start-->
				<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								Change <?= $form ?>
							</header>
							<div class="panel-body ">
								<form class="form-horizontal" role="form" id="comp_status_add" action="javascript:;" method="post" name="complaint_add">
									<div class="row">

										<div class="col-md-12">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Complaint No*</label>
													<div class="col-md-7">
														<input type="text" class="form-control" placeholder="Complaint No" title="Complaint No" name="complaint_no" id="complaint_no" value="<?= $rel['complaint_no'] ?>" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-4 control-label">Complaint Date*</label>
													<div class="col-md-7">
														<input id="complaint_date" name="complaint_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= date("d/m/Y", strtotime($rel['complaint_date'])); ?>" placeholder="Inquiry Date" readonly>
													</div>
												</div>
											</div>

											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Customer*</label>
													<div class="col-md-7">
														<input name="cust_id" type="text" id="cust_id" class="form-control " title="Date" value="<?= $rel['l_name']; ?>" placeholder="" readonly>
													</div>
												</div>
											</div>
											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-4 control-label">Complaint Type*</label>
													<div class="col-md-7">
														<input name="comp_type" type="text" id="comp_type" class="form-control default-date-picker required valid" title="Date" value="<?= $rel['complaint_type_name']; ?>" placeholder="" readonly />
													</div>
												</div>
											</div>

											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Mobiile*</label>
													<div class="col-md-7">
														<input type="text" class="form-control " title="Date" value="<?= $rel['cust_mobile']; ?>" placeholder="" readonly>
													</div>
												</div>
											</div>

											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-4 control-label">Address*</label>
													<div class="col-md-7">
														<textarea class="form-control" readonly><?= $rel['m_address']; ?></textarea>
													</div>
												</div>
											</div>

											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Current Status*</label>
													<div class="col-md-7">
														<input name="cur_status" type="text" id="cur_status" class="form-control default-date-picker required valid" title="Date" value="<?= $rel['f_status_name']; ?>" placeholder="" readonly>
													</div>
												</div>
											</div>
											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-4 control-label">Change Status*</label>
													<div class="col-md-7">
														<select class="form-control" name="change_status" id="change_status" onchange="openStatusForm(this.value);">
															<option value="">--Change Status--</option>
															<?= getAllStatus_filter($dbcon, $where_status); ?>
														</select>
													</div>
												</div>
											</div>


											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Remark</label>
													<div class="col-md-7">
														<textarea name="f_remark" id="f_remark" class="form-control"></textarea>
													</div>
												</div>
											</div>

											<!-- Hide divs for only remakr start -->
											<div id="remrk_hdn_divs">
												<div class="col-md-6" style="display:none" id="ns_part_id">
													<div class="form-group">
														<label class="col-md-4 control-label">Need Spare Part*</label>
														<div class="col-md-7">
															<input type="radio" name="n_spart" value="2" onclick="showSparePartForm(this.value)" /><span class="mg10">Yes</span>
															<input type="radio" name="n_spart" value="4" onclick="showSparePartForm(this.value)" /><span class="mg10">No</span>
															<input type="hidden" name="sp_part_close_status" id="sp_part_close_status" value="<?php echo $rel['sp_part_status']; ?>" />
														</div>
													</div>
												</div>

												<div class="col-md-5" style="display:none" id="cust_fb_id_div">
													<div class="form-group">
														<label class="col-md-4 control-label">Customer Satisfaction Level</label>
														<div class="col-md-7">
															<select class="form-control" title="Select Customer Satisfaction Level" name="cust_fb_id" id="cust_fb_id">
																<option value="">--Select Customer Satisfaction Level--</option>
																<option value="1">Not Happy</option>
																<option value="2">Happy</option>
																<option value="3">Satisfied</option>
																<option value="4">Delight</option>
															</select>
														</div>
													</div>
												</div>

												<div class="clearfix"></div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Payment Status*</label>
														<div class="col-md-7">
															<select class="form-control" id="pay_status" name="pay_status" title="Choose Payment Status" onchange="getTotalPayment(this.value);show_hide_payment_field(this.value);" required>
																<option value="">--Select Pay Status--</option>
																<option value="1">Paid</option>
																<option value="0">Un paid</option>
															</select>
														</div>
													</div>
												</div>

												<div class="col-md-5">
													<div class="form-group">
														<label class="col-md-4 control-label">Service Charge</label>
														<div class="col-md-7">
															<input type="text" class="form-control" name="sum_service_charge" id="sum_service_charge" value="<?php echo $service_charge; ?>" readonly />
														</div>
													</div>
												</div>

												<div class="clearfix"></div>
												<div class="" id="close_form1">
													<div class="col-md-6">
														<div class="form-group" id="s_pmode">
															<label class="col-md-4 control-label">Payment Mode *</label>
															<div class="col-md-7 col-xs-11">
																<select class="form-control" name="service_charge" id="service_charge" onChange="" required title="Select Payment Mode">
																	<?phpecho getpaymentmode($dbcon, $rel['payment_mode']); ?>
																</select>
															</div>
														</div>
													</div>

													<div class="col-md-5">
														<div class="form-group">
															<label class="col-md-4 control-label">Spare Part Rate</label>
															<div class="col-md-7">
																<input type="text" class="form-control" name="spare_charge" id="spare_charge" value="<?php echo $spare_charge; ?>" readonly />
															</div>
														</div>
													</div>
													<input type="hidden" class="form-control" name="" id="" value="<?= get_all_acc_type_emp($dbcon, $empl_id); ?>" />
												</div>

												<div class="" id="close_form1">
													<div class="col-md-6">
														<div class="form-group" id="s_damt">
															<label class="col-md-4 control-label">Due Amount*</label>
															<div class="col-md-7">
																<input type="text" name="c_amount_old" id="c_amount_old" class="form-control" value="" onkeypress="return isNumberKey(event)" readonly />
															</div>
														</div>
													</div>

													<div class="col-md-5">
														<div class="form-group">
															<label class="col-md-4 control-label">Total</label>
															<div class="col-md-7">
																<input type="text" class="form-control" name="total_charge" id="total_charge" value="<?php echo $spare_charge + $service_charge; ?>" readonly />
															</div>
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group" id="s_hamount">
														<label class="col-md-4 control-label">Amount*</label>
														<div class="col-md-7">

															<input type="text" name="c_amount" id="c_amount" class="form-control" value="" onkeypress="return isNumberKey(event);" onkeyup="get_final_close_pay()" readonly />
															<span id="err_amt" style="color:red;font-weight:bold;display:none">Enter Amount Less Then Due Amount</span>
														</div>
													</div>
												</div>

												<div class="col-md-5">
													<div class="form-group" id="s_hamount">
														<label class="col-md-4 control-label attach_file">Attach File</label>
														<div class="col-md-7">
															<input type="file" name="file" id="file" />
														</div>
													</div>
												</div>


												<div class="clearfix"></div>

												<?php if ($_SESSION['user_type'] == '2') { ?>
													<div class="col-md-6">
														<?php echo getBranchBox($dbcon, $_SESSION['branch_id'], $rel['branch_id'], false, true); ?>
													</div>
													<div class="clearfix"></div>
												<?php } ?>

												<div class="col-md-12">
													<?php if ($rel['sp_part_status'] != '4') { ?>
														<div class="col-md-3">
															<div class="form-group" style="float:right">
																<label class="col-md-4 control-label"></label>
																<div class="col-md-7">
																	<input type="button" class="btn btn-success" value="View Spare Parts" onclick="get_spare_part_complain(<?php echo $rel['complaint_id']; ?>)" />
																</div>
															</div>
														</div>
													<?php } ?>

													<?php if ($rel['sp_part_status'] != '4') { ?>
														<div class="col-md-3">
															<div class="form-group" style="float:left">
																<label class="col-md-4 control-label"></label>
																<div class="col-md-7">
																	<input type="button" id="get_operator_detail_btn" class="btn btn-primary" value="Set Operator" onclick="get_operator_detail(<?php echo $rel['complaint_id']; ?>,<?= $rel['cust_id']; ?>)" />
																</div>
															</div>
														</div>
													<?php } ?>
												</div>


												<div class="clearfix"></div>
												<input type="hidden" name="req_sp_count" id="req_sp_count" value="<?php echo $req_count; ?>" />
												<div id="spare_part_form" class="col-md-12" style="display:none">
													<div class="col-md-12 sp_back">
														Request New Spare Part
													</div>

													<div class="col-md-12">
														<div class="form-group">
															<table cellspacing="10" style="border-collapse:inherit;" class="display table table-bordered table-striped">
																<tr>
																	<th width="40%" class="text-center">Complaint Product</th>
																	<th width="40%" class="text-center">Product</th>
																	<th width="40%" class="text-center">Qty</th>
																	<th width="10%" class="text-center">Action</th>
																</tr>
																<tr>
																	<td style="vertical-align:top;">
																		<select class="select2" title="Select Product" name="comp_product_id" id="comp_product_id" onchange="get_bom_product(this.value)">
																			<option value="">--Select Product--</option>
																			<?= load_all_complain_product($dbcon, $id) ?>
																		</select>
																	</td>
																	<td style="vertical-align:top;">
																		<select class="select2" title="Select Product" name="product_id" id="product_id">
																			<option value="">--Select Product--</option>
																		</select>
																	</td>
																	<td style="vertical-align:top;">

																		<input type="text" name="product_qty" id="product_qty" class="form-control" />

																	</td>

																	<td style="vertical-align:top;text-align:center;">
																		<input type='hidden' name='edit_id' id='edit_id' value="" />
																		<input type="button" name="addrow" id="addrow" onclick="add_field_sp()" class="btn btn-primary" value="Add" />
																	</td>
																</tr>
															</table>
														</div>
													</div>
													<div class="col-md-12">
														<div class="form-group">
															<table cellspacing="10" style="border-collapse:inherit;" class="display table table-bordered table-striped">
																<thead>
																	<tr>
																		<th width="40%" class="text-center">Complaint Product</th>
																		<th width="40%" class="text-center">Product</th>
																		<th width="40%" class="text-center">Quantity</th>
																		<th width="10%" class="text-center">Action</th>
																	</tr>
																</thead>
																<tbody id="complaint_pro_data_c">
																</tbody>
															</table>
														</div>
													</div>

												</div>
											</div>
											<!-- Hide divs for only remakr End -->

											<div class="clearfix"></div>
											<input type='hidden' name='cust_id_hid' id='cust_id_hid' value="<?= $rel['cust_id']; ?>" />
											<input type='hidden' name='comp_id_hid' id='comp_id_hid' value="<?= $rel['complaint_id']; ?>" />
											<input type='hidden' name='f_emp' id='f_emp' value="<?= $rel['emp_id']; ?>" />
											<input type='hidden' name='old_sp_part_status' id='old_sp_part_status' value="<?= $rel['old_sp_part_status']; ?>" />

											<button type="submit" class="btn btn-success" id="submit">Submit</button> &nbsp;
											<a href="<?= ROOT . SERVICE_ROOT . 'complaint_list' ?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
										</div>
									</div><!--Vendor row end-->
									<input type='hidden' name='mode' id='mode' value='add_complain_status' />
									<input type='hidden' name='eid' id='eid' value='<?= $rel['complaint_id'] ?>' />
									<input type="hidden" value="" id="bom_first_id" name="bom_first_id" value="" />
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

		<?php include_once($incPath . 'footer.php'); ?>
		<?php include_once($include1 . 'view_complain_history_spare_part.php'); ?>
		<?php include_once($include1 . 'modal_operator_detail.php'); ?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($incPath . 'include_js_file.php'); ?>
	<script src="<?= ROOT ?><?= SERVICE_ROOT ?>js/app/complaint_status.js?<?= time() ?>"></script>
	<script src="<?= ROOT ?><?= SERVICE_ROOT ?>js/app/complaint.js?<?= time() ?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});


		$('#radioBtn a').on('click', function() {
			var sel = $(this).data('title');
			var tog = $(this).data('toggle');
			$('#' + tog).prop('value', sel);

			$('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
			$('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');

			$('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('btn-success').addClass('btn-danger');
			$('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('btn-danger').addClass('btn-success');
		})
	</script>
</body>

</html>