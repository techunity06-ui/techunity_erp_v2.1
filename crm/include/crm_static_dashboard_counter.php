<style type="text/css">
	.count,
	.count2 {
		margin: 0px !important;
		padding: 0px !important
	}

	.cc_count {
		margin-left: 5%;
	}

	.panel-heading {
		text-align: center;
		font-weight: bold;
		FONT-SIZE: 16px;
	}

	.border_line {
		border-bottom: dotted blue 2px;
	}

	.link_dash {
		border-bottom: dotted blue thin;
	}

	.panel-body {
		padding: 8px !important;
	}

	table {
		width: 100%;
		border-collapse: collapse;
	}

	th {
		text-align: center;
	}
</style>

<?php
$start_date = date('d-m-Y', strtotime('first day of last month'));
$end_date = date("d-m-Y");
?>
<section class="panel">
	<div class="panel-body ">
		<div class="row">
			<div class="col-md-12">
				<div class="clearfix"></div>
				<?php
				$user_ids = check_user_chein($dbcon, $_SESSION['user_id'], 1);
				?>
				<div class="col-md-6">
					<div class="panel panel-primary">
						<div class="panel-heading">New Inquiry Added</div>
						<div class="panel-body" id="">
							<div class="col-md-6" style="margin-bottom: 15px;">
								<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
								<div class="col-md-9">
									<input id="new_inq_start_date" name="new_inq_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="new_inq_add();">
								</div>
							</div>
							<div class="col-md-6" style="margin-bottom: 15px;">
								<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
								<div class="col-md-9">
									<input id="new_inq_end_date" name="new_inq_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="new_inq_add();">
								</div>
							</div>
							<div id="new_inq_add">
							</div>
						</div>
					</div>
				</div>
				<?php

				//$comp_per=check_permission("#team_pend_tasks_sec",$_SESSION['user_id'],'view',$dbcon);
				$comp_per = 1;
				if ($comp_per) {
					$user_ids = check_user_chein($dbcon, $_SESSION['user_id'], 1);
				?>
					<div class="col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">Sales Stage</div>
							<div class="panel-body" id="">
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="sales_stage_start_date" name="sales_stage_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="sales_stage_repo();">
									</div>
								</div>
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="sales_stage_end_date" name="sales_stage_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="sales_stage_repo();">
									</div>
								</div>
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Sales Stage</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<select class="select2" id="sales_stage_id" name="sales_stage_id" onchange="sales_stage_repo();">
											<?= get_master_category_dtl($dbcon, $rel['sales_stage_id'], 7, 0, 0); //7:Sales Stage
											?>
										</select>
									</div>
								</div>
								<div id="sales_stage_repo"></div>
							</div>
						</div>
					</div>
				<?php  }  ?>
				<?php
				//$comp_per=check_permission("#team_pend_tasks_sec",$_SESSION['user_id'],'view',$dbcon);
				$comp_per = 1;
				if ($comp_per) {
					$user_ids = check_user_chein($dbcon, $_SESSION['user_id'], 1);
				?>
					<div class="col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">Stage</div>
							<div class="panel-body" id="">
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="source_start_date" name="source_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="stage_repo();">
									</div>
								</div>
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="source_end_date" name="source_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="stage_repo();">
									</div>
								</div>
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<select class="select2" id="source_user_id" name="source_user_id" onchange="stage_repo();">
											<?= get_user_report($dbcon, $user_ids); ?>
										</select>
									</div>
								</div>
								<div id="stage_repo">
								</div>
							</div>
						</div>
					</div>
				<?php }  ?>
				<?
				//$comp_per=check_permission("#team_pend_tasks_sec",$_SESSION['user_id'],'view',$dbcon);
				$comp_per = 1;
				if ($comp_per) {
					$user_ids = check_user_chein($dbcon, $_SESSION['user_id'], 1);

				?>
					<div class="col-md-6">
						<div class="panel panel-primary">
							<div class="panel-heading">Source</div>
							<div class="panel-body" id="">
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="source_start_date1" name="source_start_date1" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="source_repo1();">
									</div>
								</div>
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="source_end_date1" name="source_end_date1" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="source_repo1();">
									</div>
								</div>
								<div class="col-md-6" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<select class="select2" id="source_user_id1" name="source_user_id1" onchange="source_repo1();">
											<?= get_user_report($dbcon, $user_ids); ?>
										</select>
									</div>
								</div>
								<div id="source_repo1">
								</div>
							</div>
						</div>
					</div>
				<?php }  ?>
				<?php
				//$comp_per=check_permission("#team_pend_tasks_sec",$_SESSION['user_id'],'view',$dbcon);
				$comp_per = 1;
				if ($comp_per) {
					$user_ids = check_user_chein($dbcon, $_SESSION['user_id'], 1);

				?>
					<div class="col-md-12">
						<div class="panel panel-primary">
							<div class="panel-heading">STAGE SUMMARY</div>
							<div class="panel-body" id="">
								<div class="col-md-4" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="stage_summ_start_date" name="stage_summ_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="stage_summ();">
									</div>
								</div>
								<div class="col-md-4" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="stage_summ_end_date" name="stage_summ_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="stage_summ();">
									</div>
								</div>
								<div id="stage_summ" style="margin-top:50px;overflow-x:scroll;"></div>
							</div>
						</div>
					</div>
				<?php }  ?>
				<?php
				//$comp_per=check_permission("#team_pend_tasks_sec",$_SESSION['user_id'],'view',$dbcon);
				$comp_per = 1;
				if ($comp_per) {
					$user_ids = check_user_chein($dbcon, $_SESSION['user_id'], 1);

				?>
					<div class="col-md-12">
						<div class="panel panel-primary">
							<div class="panel-heading">INQUIRY REPORT</div>
							<div class="panel-body" id="">
								<div class="col-md-4" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="so_start_date" name="so_start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $start_date ?>" placeholder="Start Date" onchange="inquiry_monthly_report();">
									</div>
								</div>
								<div class="col-md-4" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<input id="so_end_date" name="so_end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?= $end_date ?>" placeholder="End Date" onchange="inquiry_monthly_report();">
									</div>
								</div>
								<div class="col-md-4" style="margin-bottom: 15px;">
									<div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Employee</strong></div>
									<div class="col-md-9" style="padding-right: 0px;">
										<select class="select2" id="so_user_id" name="so_user_id" onchange="inquiry_monthly_report();">
											<?= get_user_report($dbcon, $user_ids); ?>
										</select>
									</div>
								</div>
								<div id="inquiry_monthly_report" style="margin-top:50px;overflow-x:scroll;"></div>
							</div>
						</div>
					</div>
				<?php }  ?>
			</div>
		</div>
	</div>
</section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalPendingDispatchSO" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Pending Dispatch Sale Order</h3>
			</div>
			<div class="modal-body form">
				<div id="dispatch_so_report_list" style="margin-top:50px;overflow-x:scroll;"></div>

			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<script type="text/javascript">
		$(document).ready(function() {
			sales_stage_repo();
			stage_repo();
			source_repo1();
			stage_summ();
			new_inq_add();
			inquiry_monthly_report();
		});

		function new_inq_add() {
			var new_inq_start_date = $("#new_inq_start_date").val();
			var new_inq_end_date = $("#new_inq_end_date").val();
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/crm_static_dashboard/',
				data: {
					mode: "new_inq_add_load",
					new_inq_start_date: new_inq_start_date,
					new_inq_end_date: new_inq_end_date
				},
				success: function(response) {
					//console.log(response);
					//var data = JSON.parse(response);
					$('#new_inq_add').html(response);
				}
			});
		}

		function sales_stage_repo() {
			var sales_stage_start_date = $("#sales_stage_start_date").val();
			var sales_stage_end_date = $("#sales_stage_end_date").val();
			var sales_stage_id = $("#sales_stage_id").val();
			//alert(sales_stage_id);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/crm_static_dashboard/',
				data: {
					mode: "sales_stage_repo",
					sales_stage_start_date: sales_stage_start_date,
					sales_stage_end_date: sales_stage_end_date,
					sales_stage_id: sales_stage_id
				},
				success: function(response) {
					//console.log(response);
					//var data = JSON.parse(response);
					$('#sales_stage_repo').html(response);
				}
			});
		}

		function stage_repo() {
			var source_start_date = $("#source_start_date").val();
			var source_end_date = $("#source_end_date").val();
			var source_user_id = $("#source_user_id").val();
			//alert(sales_stage_id);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/crm_static_dashboard/',
				data: {
					mode: "stage_repo",
					source_start_date: source_start_date,
					source_end_date: source_end_date,
					source_user_id: source_user_id
				},
				success: function(response) {
					//console.log(response);
					//var data = JSON.parse(response);
					$('#stage_repo').html(response);
				}
			});
		}

		function source_repo1() {
			var source_start_date = $("#source_start_date1").val();
			var source_end_date = $("#source_end_date1").val();
			var source_user_id = $("#source_user_id1").val();
			//alert(sales_stage_id);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/crm_static_dashboard/',
				data: {
					mode: "source_repo1",
					source_start_date: source_start_date,
					source_end_date: source_end_date,
					source_user_id: source_user_id
				},
				success: function(response) {
					//console.log(response);
					//var data = JSON.parse(response);
					$('#source_repo1').html(response);
				}
			});
		}

		function stage_summ() {
			var stage_summ_start_date = $("#stage_summ_start_date").val();
			var stage_summ_end_date = $("#stage_summ_end_date").val();
			//sessionStorage.start=stage_summ_start_date;

			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/crm_static_dashboard/',
				data: {
					mode: "stage_summ",
					stage_summ_start_date: stage_summ_start_date,
					stage_summ_end_date: stage_summ_end_date
				},
				success: function(response) {
					//console.log(response);
					//var data = JSON.parse(response);
					$('#stage_summ').html(response);
				}
			});
		}

		function inquiry_monthly_report() {
			var start_date = $("#so_start_date").val();
			var end_date = $("#so_end_date").val();
			var user_id = $("#so_user_id").val();
			sessionStorage.start = stage_summ_start_date;

			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/crm_static_dashboard/',
				data: {
					mode: "inquiry_monthly_report",
					start_date: start_date,
					end_date: end_date,
					user_id: user_id
				},
				success: function(response) {
					//console.log(response);
					//var data = JSON.parse(response);
					$('#inquiry_monthly_report').html(response);
				}
			});
		}

		function pending_dispatch_so(category_id, month) {
			Loading(true);
			$.ajax({
				type: "POST",
				url: root_domain + crm_domain + 'app/crm_static_dashboard/',
				data: {
					mode: "pending_dispatch_so",
					category_id: category_id,
					month: month
				},
				success: function(response) {
					$('#dispatch_so_report_list').html(response);
					$("#ModalPendingDispatchSO").modal("show");
					Unloading();
				}
			});
		}
	</script>