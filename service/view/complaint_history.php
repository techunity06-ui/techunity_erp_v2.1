<?php
	session_start();
	include('../include/urlfile.php');
	$incPath = $path . 'include/';

	$form = "Complaint History";
	$id = $_REQUEST['id'];
	$rel = getComplainDetail($dbcon, $id);

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
	if ($fstat == '5') {
		$where = " and f_id='3'  or f_id='4'";
	}
	if ($fstat == '6') {
		$where = " and f_id='2'  or f_id='4'";
	}

	$where_product = " and product_type!='1'";

	$service_charge = get_service_charge($dbcon, $id);
	$spare_charge = get_spare_part_rate($dbcon, $id);

	$back_link = $_SERVER['HTTP_REFERER'];
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

		.redc {
			color: #EB6A5D !important;
			text-align: center !important;
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
								<a href="<?= $back_link ?>" type="button" class="btn btn-info" style="float:right;"><i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back</a>
								<h3><?= 'View ' . ' ' . $form ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . SERVICE_ROOT . 'complaint_list' ?>">Complaint List</a></li>
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
							<div class="panel-body ">
								<table class="table table-bordered table-hover table-striped ">
									<tr>
										<th colspan="4" class="redc">Complain Detail</th>
									</tr>
									<tr>
										<th>Complaint No</th>
										<td><?= $rel['complaint_no'] ?></td>
										<th>Complaint Date</th>
										<td><?= date("d/m/Y", strtotime($rel['complaint_date'])); ?></td>
									</tr>
									<tr>
										<th>Customer</th>
										<td><?= $rel['l_name']; ?></td>

										<th>Complaint Type</th>
										<td><?= $rel['complaint_type_name']; ?></td>
									</tr>
									<tr>
										<th>Current Status</th>
										<td><?= $rel['f_status_name']; ?></td>
										<th></th>
										<td></td>
									</tr>
								</table>

								<table class="table table-bordered table-hover table-striped ">
									<tr>
										<th colspan="4" class="redc">Product Detail</th>
									</tr>
									<tr>
										<th>#</th>
										<th>Product</th>
										<th>Service Status</th>
									</tr>

									<?php
									$cnt = 1;
									$query = "select comp.*,p.product_name from tbl_complaint_trn as comp inner join product_mst as p on p.product_id=comp.product_id  where comp.complaint_id=$id and complaint_trn_status!=2";
									$rs_comp = $dbcon->query($query);
									while ($row = mysqli_fetch_array($rs_comp)) {
									?>
										<tr>
											<th><?php echo $cnt; ?></th>
											<th><?php echo $row['product_name']; ?></th>
											<th><?php if ($row['comp_pro_sts'] == '2') {
													echo "Paid";
												} else {
													echo "Free";
												} ?></th>
										</tr>
									<?php $cnt++;
									} ?>

								</table>

								<table class="table table-bordered table-hover table-striped ">
									<tr>
										<th colspan="5" class="redc">Followup History</th>
									</tr>
									<tr>
										<th>#</th>
										<th>Date</th>
										<th>Status</th>
										<th>Assigned Employee</th>
										<th>Remark</th>

									</tr>

									<?php
										$q = "select f.fl_id,f.fl_cid,f.fl_f_status,f.fl_date,f.f_remark,f.fl_e_id,l.l_name,fl.f_status_name from tbl_follow as f left join tbl_ledger as l on f.fl_e_id=l.l_id left join tbl_followup_status as fl on f.fl_f_status=fl.f_id where f.fl_cid='$id' order by f.fl_id";
										$result = $dbcon->query($q);

										if (mysqli_num_rows($result) > 0) {
											$cnt = 1;
											while ($relf = mysqli_fetch_assoc($result)) {
										?>
											<tr>
												<td><?php echo $cnt; ?></td>
												<td><?php echo date('d M, Y h:i A', strtotime($relf['fl_date'])); ?></td>
												<td><?php echo $relf['f_status_name'] ?></td>
												<td><?php echo $relf['l_name'] ?></td>
												<td><?php echo $relf['f_remark'] ?></td>
											</tr>
										<?php $cnt++;
										}
									} else { ?>
										<tr>
											<th colspan="5" class="redc">No Data Found</th>
										</tr>
									<?php } ?>
								</table>

								<table class="table table-bordered table-hover table-striped">
									<tr>
										<th colspan="8" class="redc">Spare Part History</th>
									</tr>
									<tr>
										<th>#</th>
										<th width="30%" class="text-center">Product</th>
										<th width="5%" class="text-center">Qty</th>
										<th width="5%" class="text-center">Rate</th>
										<th width="5%" class="text-center">Amount</th>
										<th width="20%" class="text-center">Courier Name</th>
										<th width="15%" class="text-center">Courier No</th>
										<th width="20%" class="text-center">Expected Delivery Date</th>
									</tr>

									<?php
										$qs = "select pr.s_id,pr.s_comp_id,pr.s_cust_id,pr.s_user_id,pr.s_date,pr.s_product,pr.s_qty,pr.s_rate,pr.s_amount,pr.s_courier_name,pr.s_courier_no,pr.s_courier_del_date,pr.s_status,pm.product_name from tbl_complain_spare_part as pr inner join product_mst as pm on pr.s_product=pm.product_id where pr.s_comp_id=$id";
										$result1 = $dbcon->query($qs);

										if (mysqli_num_rows($result1) > 0) {
											$cnt = 1;
											while ($relf1 = mysqli_fetch_assoc($result1)) {
												if ($relf1['s_courier_del_date'] != '0000-00-00' && $relf1['s_courier_del_date'] != '1970-01-01') {
													$date = date("d/m/Y", strtotime($relf1['s_courier_del_date']));
												} else {
													$date = "";
												}

												if ($relf1['s_status'] == '2') {
													$btn_request = '  <button type="button" class="btn btn-round btn-success btn-xs" onclick="request_data_complain(' . $relf1['s_id'] . ');" id="filerequest' . $cnt . '"><i class="fa fa-check-circle"></i></button>';
												} else {
													$btn_request = '';
												}
										?>

											<tr>
												<td><?php echo $cnt; ?></td>
												<td><?php echo $relf1['product_name'] ?></td>
												<td><?php echo $relf1['s_qty'] ?></td>
												<td><?php echo $relf1['s_rate'] ?></td>
												<td><?php echo $relf1['s_amount'] ?></td>
												<td><?php echo $relf1['s_courier_name'] ?></td>
												<td><?php echo $relf1['s_courier_no'] ?></td>
												<td><?php echo $date ?></td>
											</tr>
										<?php $cnt++;
										}
									} else { ?>
										<tr>
											<th colspan="8" class="redc">No Data Found</th>
										</tr>
									<?php } ?>
								</table>

								<table class="table table-bordered table-hover table-striped">
									<tr>
										<th colspan="7" class="redc">Old Spare Part History</th>
									</tr>
									<tr>
										<th>#</th>
										<th width="10%" class="text-center">Product</th>
										<th width="10%" class="text-center">Quantity</th>
										<th width="10%" class="text-center">Rate</th>
										<th width="10%" class="text-center">Amount</th>
										<th width="30%" class="text-center">Courier Details</th>
										<th width="30%" class="text-center">Remark</th>
									</tr>

									<?php
									$qo = "select pr.s_id,pr.sc_comp_id,pr.sc_cust_id,pr.courier_name,pr.courier_no,pr.courier_del_date,pr.sc_user_id,pr.sc_date,pr.sc_product,pr.sc_qty,pr.sc_rate,pr.sc_amount,pr.sc_remark,pm.product_name from tbl_complain_close_spare_part as pr inner join product_mst as pm on pr.sc_product=pm.product_id where pr.sc_comp_id=$id";
									$result2 = $dbcon->query($qo);

									if (mysqli_num_rows($result2) > 0) {
										$cnt = 1;
										while ($relf2 = mysqli_fetch_assoc($result2)) {
											if ($relf2['courier_del_date'] == '0000-00-00') {
												$date = "";
											} else {
												$date = date("d/m/Y", strtotime($relf2['courier_del_date']));
											}
									?>
											<tr>
												<td><?php echo $cnt; ?></td>
												<td><?php echo $relf2['product_name'] ?></td>
												<td><?php echo $relf2['sc_qty'] ?></td>
												<td><?php echo $relf2['sc_rate'] ?></td>
												<td><?php echo $relf2['sc_amount'] ?></td>
												<td>
													Courier Name : <?php echo $relf2['courier_name'] ?><br>
													Courier No: <?php echo $relf2['courier_no'] ?><br>
													Courier Date : <?php echo $date; ?><br>
												</td>
												<td><?php echo $relf2['sc_remark'] ?></td>
											</tr>
										<?php $cnt++;
										}
									} else { ?>
										<tr>
											<th colspan="7" class="redc">No Data Found</th>
										</tr>
									<?php } ?>
								</table>
								<table class="table table-bordered table-hover table-striped">
									<tr>
										<th colspan="4" class="redc">Payment History</th>
									</tr>
									<tr>
										<th width="2%">#</th>
										<th width="10%" class="text-center">Date</th>
										<th width="10%" class="text-center">Amount</th>
										<th width="10%" class="text-center">Payment Mode</th>
									</tr>

									<?php
									$qoh = "select * from complain_payment_trn where bill_id=$id";
									$result3 = $dbcon->query($qoh);

									if (mysqli_num_rows($result3) > 0) {
										$cnt = 1;
										$ph_pay = 0;
										while ($relf2 = mysqli_fetch_assoc($result3)) {
											$ph_pay += $relf2['paid_amount'];
										?>
											<tr>
												<td><?php echo $cnt; ?></td>
												<td><?php echo date("d/m/Y", strtotime($relf2['pay_date'])); ?></td>
												<td><?php echo $relf2['paid_amount']; ?></td>
												<td><?php echo $relf2['pay_mode']; ?></td>

											</tr>
											<tr>
												<th colspan="3" style="text-align:right">Total:</th>
												<th><?php echo $ph_pay; ?></th>
											</tr>
										<?php $cnt++;
										}
									} else { ?>
										<tr>
											<th colspan="4" class="redc">No Data Found</th>
										</tr>
									<?php } ?>
									<tr>
										<th>Amount Payable</th>
										<td><?php echo $service_charge + $spare_charge;  ?></td>
										<th>Due Payment</th>
										<td><?php echo ($service_charge + $spare_charge) - $ph_pay; ?></td>
									</tr>
								</table>

								<?php
								$qi = $dbcon->query("select * from tbl_complaint_image where ci_comp_id='$id'");
								while ($irow = mysqli_fetch_array($qi)) {
									if ($irow && $irow['ci_image']) {
								?>
									<div class="col-md-3">
										<a href="<?= ROOT . 'view/upload/complaint_img/' . $irow['ci_image']; ?>" target="_blank">
											<img src="<?= ROOT . 'view/upload/complaint_img/' . $irow['ci_image']; ?>" width="150" height="150" />
										</a>
									</div>
								<?php }} ?>
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
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($incPath . 'include_js_file.php'); ?>

	<script src="<?= ROOT ?><?= SERVICE_ROOT ?>js/app/complaint_reassign.js?<?= time() ?>"></script>
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
		})
	</script>
</body>

</html>