<?php
session_start();
include('../include/urlfile.php');


$service_status_where = '';
$form = "Complaint";
if (strpos($_SERVER['REQUEST_URI'], "complaint_edit") == false) {
	$mode = "Add";
	$countryid = "101";
	$stateid = "1";
	$cityid = "1";
	$complaint_date = date('d-m-Y h:i A');
	$service_status_where = " AND f_id in(1,2)";
} else {
	$mode = "Edit";
	$complaint_id = $dbcon->real_escape_string($_REQUEST['id']);
	$query = "select * from tbl_complaint where complaint_id=$complaint_id";
	$rel = mysqli_fetch_assoc($dbcon->query($query));
	$complaint_date = date('d-m-Y h:i A', strtotime($rel['complaint_date']));
}
$branch_id = $_SESSION['branch_id'];

// $where = " and f_id='2' or f_id='6'";
$where = "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>COMPLAINT</title>
	<?php include_once($include . 'include_css_file.php'); ?>
	<style>
		#radioBtn .notActive {
			color: #3276b1;
			background-color: #fff;
		}

		li {
			Z-Index: 101;
		}
	</style>

	<link rel="stylesheet" href="<?= ROOT ?>css/treejs.css" />
</head>

<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include . 'include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once($include . 'left_menu.php'); ?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3><?= $mode . ' ' . $form ?></h3>
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
								New <?= $form ?>
							</header>
							<div class="panel-body ">
								<form class="form-horizontal" role="form" id="complaint_add" action="javascript:;" method="post" name="complaint_add">
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
														<!-- <input id="complaint_date" name="complaint_date" type="text" class="form-control  required valid" title="Date" value="<?= $complaint_date ?>" placeholder="Inquiry Date"> -->
														<div data-date="<?=$complaint_date?>" class="input-group date form_datetime-meridian">
															<input type="text" class="form-control required valid" value="<?=$complaint_date?>" name="complaint_date" id="complaint_date" autocomplete="off" placeholder="Complaint Date">
															<div class="input-group-btn">
																<button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
															</div>
														</div>
													</div>
												</div>
											</div>

											<div class="clearfix"></div>


											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Customer*</label>
													<div class="col-md-7">
														<select class="select2" name="cust_id" id="cust_id" onChange="load_cust_sold_pro(this.value);checkCustomerStatus(this.value);load_ledger_detail(this.value);" style="width:100%">
															<?= getcust($dbcon, $rel['cust_id'], 38); ?>
														</select>
														<strong style="color:red;display:none;font-size:16px;" id="cust_status_show">This Customer Is Blocked..</strong>
														<strong style="color:red;display:none;font-size:16px;" id="cust_status_due_show">Payment For Complaint Is Due..</strong>
													</div>

												</div>
											</div>

											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-4 control-label">Complaint Type*</label>
													<div class="col-md-7">
														<select class="select2" name="complaint_type_id" id="complaint_type_id">
															<?= get_complaint_type($dbcon, $rel['complaint_type_id']); ?>
														</select>
													</div>
												</div>
											</div>
											<div class="clearfix"></div>

											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Mobile No*</label>
													<div class="col-md-7">
														<input type="text" class="form-control" id="ledger_mobile" name="ledger_mobile" value="<?= $rel['cust_mobile_no'] ?>" />
													</div>
												</div>
											</div>

											<div class="col-md-5">
												<div class="form-group">
													<label class="col-md-4 control-label">Product Serial No</label>
													<div class="col-md-7">
														<input type="text" class="form-control" id="prod_serial_no" name="prod_serial_no" value="<?= $rel['prod_serial_no']; ?>" />
													</div>
												</div>
											</div>
											<div class="clearfix"></div>

											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Product Model No</label>
													<div class="col-md-7">
														<input type="text" class="form-control" id="pro_model_no" name="pro_model_no" value="<?= $rel['pro_model_no']; ?>" />
													</div>
												</div>
											</div>

											<div class="col-md-5">
												<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true); ?>
											</div>

											<div class="clearfix"></div>

											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Complaint Media</label>
													<div class="col-md-7">
														<select class="select2" name="media_id" id="media_id">
															<?= get_media($dbcon, $rel['media_id']); ?>
														</select>
													</div>
												</div>
											</div>

											<div class="col-md-5">
												<?php if ($mode == "add") { ?>
													<div class="form-group">
														<label class="col-md-4 control-label">File</label>
														<div class="col-md-7" style="display: flex;">
															<input class="form-control" type="file" name="file_attachment" id="file_attachment" placeholder="" />
															<div class="btn btn-xs btn-danger hidden" id="btn-file-delete" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_file()" style="margin: 5px;"><i class="fa fa-trash-o"></i></div>

														</div>
													</div>
												<?php } else { ?>
													<div class="form-group" style="margin-bottom: 0;">
														<label class="col-md-4 control-label">File</label>
														<div class="col-md-7" style="display: flex;">
															<input class="form-control" type="file" name="file_attachment" id="file_attachment" placeholder="" />
															<input type="hidden" class="file_attachment_name" id="file_attachment_name">
															<?php if(!empty($rel['file'])) {?>
																<div class="btn btn-xs btn-danger" id="btn-file-delete" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_editatble_file()" style="margin: 5px;"><i class="fa fa-trash-o"></i></div>
															<?php } ?>
														</div>
													</div>
												<?php } ?>
												<label id="add_file_name" style="float: right;"><?= $rel['file']; ?></label>
											</div>
											<div class="clearfix"></div>

											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Address *</label>
													<div class="col-md-7">
														<textarea name="ledger_address" id="ledger_address" class="form-control"><?= $rel['cust_address'] ?></textarea>
													</div>
												</div>
											</div>

											<div class="col-md-5">
												<div class="form-group">

												</div>
											</div>
											<div class="clearfix"></div>

											<div class="col-md-7" id="check_due_div" style="display:none">
												<div class="form-group">
													<label class="col-md-3 control-label"></label>
													<div class="col-md-7">
														<input type="checkbox" name="" id="check_due" onclick="enable_complain()" /> <strong>Click Here If U Still Want To Enter Complain </strong>
													</div>

												</div>
											</div>

											<div class="clearfix"></div>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="2" style="border-collapse:inherit; table-layout: fixed;" class="display table table-bordered table-striped">
														<tr>
															<th width="40%" class="text-center">Product</th>
															<th width="15%" class="text-center">Version</th>
															<th width="15%" class="text-center">Service Status</th>
															<th width="15%" class="text-center">Amount</th>
															<th width="15%" class="text-center">Action</th>
														</tr>
														<tr>
															<td style="vertical-align:top;">
																<!-- <select class="select2" title="Select Product" name="product_id" id="product_id" onChange="load_cust_prowise_model(this.value);load_model_service_status(this.value)">
												<?php//=load_cust_sold_pro($dbcon,'',$rel['cust_id'])
												?>
												<?php//=getproduct_typewise($dbcon,'','','')
												?>
											</select> -->
																<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onChange="load_cust_prowise_model(this.value);load_model_service_status(this.value);load_bom_version(this.value);" />
															</td>
															<td style="vertical-align:top;">
																<select class="select2" title="Select version" name="bom_version_id" id="bom_version_id">
																	<option value="">--Select Version--</option>
																</select>
															</td>
															<td style="vertical-align:top;">
																<select class="select2" title="Select Service Status" name="comp_pro_sts" id="comp_pro_sts" onChange="change_amt_text(this.value)">
																	<option value="">--Select Service Status--</option>
																	<option value="1">Free</option>
																	<option value="2">Paid</option>
																</select>
															</td>

															<td>
																<input type="text" class="form-control" name="comp_amount" id="comp_amount" />
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
																<th width="40%" class="text-center">Product</th>
																<th width="10%" class="text-center">Version</th>
																<th width="10%" class="text-center">Service Status</th>
																<th width="10%" class="text-center">Amount</th>
																<th width="10%" class="text-center">Action</th>
															</tr>
														</thead>
														<tbody id="complaint_pro_data">
														</tbody>
													</table>
												</div>
											</div>
											<div class="clearfix"></div>

											<?php if ($mode == "Add") { ?>

												<div class="col-md-12">

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Assign To Employee*</label>
															<div class="col-md-7">

																<select class="form-control" name="ass_emp" id="ass_emp" title="Choose Assign Status" onchange="hide_ass_emp(this.value)" required>

																	<option value="">--Assign To Employee--</option>
																	<option value="yes">Yes</option>
																	<option value="no">No</option>

																</select>

															</div>
														</div>
													</div>

												</div>


												<div class="col-md-12" id="show_assign_div" style="display:none">

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Change Status</label>
															<div class="col-md-7">

																<select class="form-control" name="change_status" id="change_status" onchange="/*hideAllEmployee(this.value)*/">

																	<option value="">--Change Status--</option>
																	<?= getAllStatus_filter($dbcon, $service_status_where); ?>
																</select>

															</div>
														</div>
													</div>

													<div class="col-md-5" id="emp_part_id">
														<div class="form-group">
															<label class="col-md-4 control-label">Select Employee*</label>
															<div class="col-md-7">
																<select class="select2" name="f_emp" id="f_emp" title="Choose Employee" required>
																	<option value="">--Select Employee--</option>
																	<?= getAllEmployee($dbcon,""); ?>
																</select>
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Remark </label>
															<div class="col-md-7">

																<textarea name="f_remark" id="f_remark" class="form-control"></textarea>

															</div>
														</div>
													</div>
												</div>
											<?php } ?>

											<input type="hidden" value="" id="bom_first_id" name="bom_first_id" value="" />
											<input type="hidden" value="" id="product_first_id" name="product_first_id" value="" />

											<div class="col-md-6 bom-product-spare-parts">
												<div class="form-group">
													<label class="col-md-4 control-label">Need Spare Part*</label>
													<div class="col-md-7">
														<div class="input-group">

															<div id="radioBtn" class="btn-group">
																<a id="radioBtn_yes" class="btn btn-primary btn-sm <?php if ($mode == 'Edit') {
																														if ($rel['sp_part_status'] == '1') { ?>active<?php } else { ?>notActive<?php }
																																														} else { ?>notActive<?php } ?>" data-toggle="sp_part_status" data-title="1" onclick="get_product_tree('yes')">YES</a>

																<a id="radioBtn_no" class="btn btn-primary btn-sm <?php if ($mode == 'Edit') {
																														if ($rel['sp_part_status'] == '4' || $rel['sp_part_status'] == '3') { ?>active<?php  } else { ?>notActive<?php }
																																																							} else { ?>active<?php } ?> " data-toggle="sp_part_status" data-title="4" onclick="get_product_tree('no')">NO</a>
															</div>
															<input type="hidden" name="sp_part_status" id="sp_part_status">
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12">
												<table id="basic" class="table table-bordered">
													<!-- Dynamic Content -->
												</table>
											</div>

											<div class="clearfix"></div>
											<button type="submit" class="btn btn-success" id="submit">Submit</button> &nbsp;
											<a href="<?= ROOT . SERVICE_ROOT . 'complaint_list' ?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
										</div>
									</div><!--Vendor row end-->
									<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
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

		<?php include_once($include . 'footer.php'); ?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include . 'include_js_file.php'); ?>

	<script src="<?= ROOT ?><?= SERVICE_ROOT ?>js/app/complaint.js?<?= time() ?>"></script>
	<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/customer.js?<?= time() ?>"></script>

	<script>
		$(".select2").select2({
			width: '100%',
			//minimumInputLength: 3,
		});
		/*$('#cust_id').select2({

			minimumInputLength: 2,
		});*/
		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true,
		});
		<?phpif ($mode == 'Add') { ?>
			load_complaint_no();
		<?php} ?>
		<?phpif ($mode == 'Edit') { ?>
			// load_ledger_detail(<?= $rel['cust_id'] ?>);
			// $('#cust_id').select2('readonly',true);

			$('#addrow').prop('disabled', true);
		<?php} ?>

		$('#radioBtn a').on('click', function() {
			var sel = $(this).data('title');
			var tog = $(this).data('toggle');
			$('#' + tog).prop('value', sel);

			$('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
			$('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
		})

		$('.myclass li span').click(function() {
			var $cb = $(this).parent().find(":checkbox");
			if (!$cb.prop("checked")) {
				$cb.prop("checked", true);
			} else {
				$cb.prop("checked", false);
			}
		});

		var date = new Date();
        var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
        // var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //end date should not greater than 15 days
        $(".form_datetime-meridian").datetimepicker({
			format: "dd-mm-yyyy HH:ii P",
			showMeridian: true,
			autoclose: true,
			todayBtn: true,
			pickerPosition: "bottom-left",
            startDate: today,
			// endDate: endDate
       });
	</script>
	<script type="text/javascript" charset="utf8" src="<?= ROOT ?>js/treejs.js"></script>
</body>

</html>
<?php if ($mode == 'Edit') { ?>
	<script>
		get_product_tree();
	</script>
<?php } ?>