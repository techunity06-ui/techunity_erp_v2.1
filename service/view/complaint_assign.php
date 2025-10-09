<?php
session_start();
include('../include/urlfile.php');
$incPath = $path . 'include/';

$form = "Complaint Status";
$id = $_REQUEST['id'];
$rel = getComplainDetail($dbcon, $id);

$comp_sp_approve_request = get_total_spare_count_request($dbcon, $id);
$fstat = $rel['followup_status'];

if ($fstat == '1') {
	$where = " and f_id='2' or f_id='6'";
}

if ($fstat == '2') {
	$where = " and f_id='4' or f_id='5'";
}

if ($fstat == '3') {
	$where = " and f_id='4'  or f_id='5'";
}

if ($fstat == '5' || $fstat == '10') {
	$where = " and f_id='3'  or f_id='4'";
}

if ($fstat == '6') {
	$where = " and f_id='2'  or f_id='4'";
}

$where_product = " and product_type!='1'";
$tot_spare_count = get_total_spare_count($dbcon, $id);
$bom_id = get_fist_bom($dbcon, $id);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<?php include_once($incPath . 'include_css_file.php'); ?>
	<style>
		.mg10 {
			margin-left: 5px;
		}
		#radioBtn .notActive {
			color: #3276b1;
			background-color: #fff;
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
								<form class="form-horizontal" role="form" id="comp_not_done_detail_add" action="javascript:;" method="post" name="complaint_add">
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
														<input id="complaint_date" name="complaint_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?= date("d-m-Y h:i A", strtotime($rel['complaint_date'])); ?>" placeholder="Complaint Date" readonly>
														<input type="hidden" id="complaint_date_reverse" value="<?= date("Y-m-d h:i:s", strtotime($rel['complaint_date'])); ?>" />
													</div>
												</div>
											</div>

											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Customer*</label>
													<div class="col-md-7">
														<input id="cust_id" name="cust_id" type="text" id="cust_id" class="form-control valid" title="Date" value="<?= $rel['l_name']; ?>" placeholder="" readonly>
													</div>
												</div>
											</div>
											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-4 control-label">Complaint Type*</label>
													<div class="col-md-7">
														<input id="comp_type" name="comp_type" type="text" id="comp_type" class="form-control default-date-picker required valid" title="Date" value="<?= $rel['complaint_type_name']; ?>" placeholder="" readonly>
													</div>
												</div>
											</div>

											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Mobile*</label>
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
														<input id="cur_status" name="cur_status" type="text" id="cur_status" class="form-control default-date-picker required valid" title="Date" value="<?= $rel['f_status_name']; ?>" placeholder="" readonly>
													</div>
												</div>
											</div>
											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-4 control-label">Change Status*</label>
													<div class="col-md-7">
														<select class="form-control" name="change_status" id="change_status" onchange="hideAllEmployee(this.value)">
															<option value="">--Change Status--</option>
															<?= getAllStatus_filter($dbcon, "", $rel['followup_status']); ?>
														</select>
													</div>
												</div>
											</div>

											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Remark </label>
													<div class="col-md-7">
														<textarea name="f_remark" id="f_remark" class="form-control"></textarea>
													</div>
												</div>
											</div>

											<div class="col-md-5" id="emp_part_id">
												<div class="form-group">
													<label class="col-md-4 control-label">Select Employee*</label>
													<div class="col-md-7">
														<select class="select2" name="f_emp" id="f_emp">
															<option value="">--Select Employee--</option>
															<?= getAllEmployee($dbcon, $rel['emp_id']); ?>
														</select>
													</div>
												</div>
											</div>

											<div class="clearfix"></div>
											<div class="col-md-6" style="display:none" id="cust_fb_id">
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
											<?php if ($_SESSION['user_type'] == '2') { ?>
												<div class="col-md-6">
													<?php echo getBranchBox($dbcon, $_SESSION['branch_id'], $rel['branch_id'], false, true); ?>
												</div>
												<div class="clearfix"></div>
											<?php } ?>

											<div class="col-md-6">
												<div class="form-group">
													<div class="col-md-7">
														<a class="btn btn-success" align="left" onclick="getComplainProduct()">View All Product</a>
													</div>
												</div>
											</div>

											<div class="clearfix"></div>

											<input type='hidden' name='comp_sp_part_status_count' id='comp_sp_part_status_count' value="<?php if ($rel['sp_part_status'] != '4') {
																																		if ($tot_spare_count == '0' && $fstat != '10') {
																																				echo "";
																																			} else {
																																				echo $tot_spare_count;
																																			}
																																		} else {
																																			echo "0";
																																		} ?>" style="margin-left:10% !important" />
											<input type='hidden' name='comp_sp_approve_request' id='comp_sp_approve_request' value="<?= $comp_sp_approve_request > 0 ? '' : $comp_sp_approve_request; ?>" />

											<?php if ($rel['sp_part_status'] != '4' && $bom_id) { ?>
												<div id="spare_part_form" class="col-md-12">
													<div class="col-md-12">
														<div class="form-group">
															<table cellspacing="10" style="border-collapse:inherit;" class="display table table-bordered table-striped">
																<tr>
																	<th colspan="11" style="text-align:center;color:red">Allocate Spare Part</th>
																</tr>
																<tr>
																	<th>
																		Complaint Product
																	</th>
																	<th width="30%" class="text-center">
																		<button type="button" class="btn btn-round btn-success btn-md" onclick="get_spare_part_complain(<?php echo $rel['complaint_id']; ?>)" id="filerequest1" style="float:left"><i class="fa fa-eye"></i></button>
																		<span></span>Product
																	</th>
																	<th width="10%" class="text-center">Qty</th>
																	<th width="10%" class="text-center">Rate</th>
																	<th width="12%" class="text-center">Amount</th>
																	<th width="15%" class="text-center">Courier Details</th>
																	<th width="15%" class="text-center">Spare Part Sent</th>
																	<th width="20%" class="text-center">Old Spare Part</th>
																	<th width="10%" class="text-center">Action</th>
																</tr>
																<tr>
																	<td>
																		<select class="select2" title="Select Product" name="comp_product_id" id="comp_product_id" onchange="get_bom_product(this.value)">
																			<option value="">--Select Product--</option>
																			<?= load_all_complain_product($dbcon, $id) ?>
																		</select>
																	</td>
																	<td style="vertical-align:top;">
																		<select class="select2" title="Select Product" name="product_id" id="product_id" onchange="getProductRate(this.value)">
																			<option value="">--Select Product--</option>
																		</select>
																	</td>
																	<td style="vertical-align:top;">
																		<input type="text" name="product_qty" id="product_qty" class="form-control" onkeypress="return isNumberKey(event)" autocomplete="off" onkeyup="getProductAmount()" />
																	</td>
																	<td style="vertical-align:top;">
																		<input type="text" name="product_rate" id="product_rate" class="form-control" onkeypress="return isNumberKey(event)" autocomplete="off" onkeyup="getProductAmount()" />
																	</td>
																	<td style="vertical-align:top;">
																		<input type="text" name="product_amt" id="product_amt" class="form-control" readonly />
																		<br />
																		<strong>Payment Status:</strong>
																		<select class="form-control" name="sp_free" id="sp_free">
																			<option value="">Payment Status</option>
																			<option value="free">Free</option>
																			<option value="paid">Paid</option>
																		</select>

																	</td>
																	<td style="vertical-align:top;">
																		Courier Name : <input type="text" name="courier_name" id="courier_name" class="form-control" />
																		Courier No : <input type="text" name="courier_no" id="courier_no" class="form-control" />
																		Courier Date : <input type="text" name="courier_del_date" id="courier_del_date" class="form-control default-date-picker" autocomplete="off" />
																	</td>

																	<td style="vertical-align:top;">
																		<select class="form-control" name="sp_sent" id="sp_sent">
																			<option value="">--Spare Part Sent--</option>
																			<option value='yes'>Yes</option>
																			<option value='no'>No</option>
																		</select>
																	</td>

																	<td style="vertical-align:top;">
																		<select class="form-control" name="old_sp_sent" id="old_sp_sent">
																			<option value="">--Old Spare Part Sent--</option>
																			<option value='yes'>Yes</option>
																			<option value='no'>No</option>
																		</select>

																	</td>

																	<td style="vertical-align:top;text-align:center;">
																		<input type='hidden' name='edit_id' id='edit_id' value="" />
																		<input type="button" name="addrow" id="addrow" onclick="add_field()" class="btn btn-primary" value="Add" />
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
																		<th width="25%" class="text-center">Complain Product</th>
																		<th width="30%" class="text-center">Product</th>
																		<th width="5%" class="text-center">Qty</th>
																		<th width="5%" class="text-center">Rate</th>
																		<th width="5%" class="text-center">Amount</th>
																		<th width="5%" class="text-center">Payment</th>
																		<th width="20%" class="text-center">Courier Detail</th>
																		<th width="20%" class="text-center">Spare Part</th>
																		<th width="20%" class="text-center">Old Spare Part</th>
																		<th width="15%" class="text-center">Action</th>
																	</tr>
																</thead>
																<tbody id="complaint_pro_data">
																</tbody>
															</table>
														</div>
													</div>

												</div>
											<?php } ?>
											<div class="clearfix"></div>

											<input type='hidden' name='cust_id_hid' id='cust_id_hid' value="<?= $rel['cust_id']; ?>" />
											<input type='hidden' name='comp_id_hid' id='comp_id_hid' value="<?= $rel['complaint_id']; ?>" />
											<input type='hidden' name='comp_sp_part_status' id='comp_sp_part_status' value="<?= $rel['sp_part_status']; ?>" />

											<!--<input type='hidden' name='f_emp' id='f_emp' value="<?= $rel['emp_id']; ?>" /> -->

											<button type="submit" id="submit_btn" class="btn btn-success">Submit</button> &nbsp;
											<a href="<?= ROOT . SERVICE_ROOT . 'complaint_list' ?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
										</div>
									</div><!--Vendor row end-->
									<input type='hidden' name='mode' id='mode' value='' />
									<input type='hidden' name='eid' id='eid' value='<?= $rel['complaint_id'] ?>' />

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
		<?php include_once($include1 . 'view_complain_product.php'); ?>
		<?php include_once($include1 . 'request_spare_part.php'); ?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($incPath . 'include_js_file.php'); ?>

	<script src="<?= ROOT ?><?= SERVICE_ROOT ?>js/app/complaint_reassign.js?<?= time() ?>"></script>
	<script>
		var comp_date = $('#complaint_date_reverse').val();
		var nowDate = new Date(comp_date);
		var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);

		//alert(today);
		$(".select2").select2({
			width: '100%'
		});
		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true,
			startDate: today
		});

		$('#radioBtn a').on('click', function() {
			var sel = $(this).data('title');
			var tog = $(this).data('toggle');
			$('#' + tog).prop('value', sel);

			$('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
			$('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
		})
	</script>
</body>

</html>