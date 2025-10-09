<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");
include_once("../../include/function_database_query.php");
$form = "Holiday List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "holiday_edit")==true) {
	$mode="Edit";
	$holidayid = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_holiday_list where id=$holidayid and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){	
		$holiday_from_date='';
		if($rel['holiday_from_date']!="1970-01-01" && $rel['holiday_from_date']!="0000-00-00"){
			$holiday_from_date=date('d-m-Y',strtotime($rel['holiday_from_date']));
		}
	}else{
		header("Location: " . DOMAIN . HRMS_ROOT . "hrms_holiday_list");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once('../../include/include_top_menu.php'); ?>
		<?php include_once('../../include/left_menu.php'); ?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?= $mode . ' ' . $form ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_holiday_list' ?>"><?= $form ?></a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<div class="panel-body">
								<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
								<form class="form-horizontal" role="form" id="holiday_add" action="javascript:;" method="post" name="holiday_add">
									<div class="col-md-12" style="padding-top: 25px;">
							 			<div class="col-md-12 margin_row">
							 				<div class="col-md-6">
											  	<div class="form-group">
										  			<label class="col-md-3 control-label" style="">Holiday Name*</label>
										  			<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" id="holiday_name" name="holiday_name" title="Enter Holiday Name" placeholder="Holiday Name" value="<?php if($mode=='Edit'){ echo $rel['holiday_name'];} ?>" required>
													</div>
											  	</div>
											</div>
											<div class="col-md-6">
											  	<div class="form-group">
										  			<label class="col-md-3 control-label">Holiday From Date*</label>
										  			<div class="col-md-8 col-xs-11">
														<input id="holiday_from_date" name="holiday_from_date" type="text" class="form-control default-date-picker required valid" title="Date" placeholder="Holiday From Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['holiday_from_date']));} ?>">
													</div>
											  	</div>
											</div>
							 			</div>
							 			<div class="col-md-12 margin_row">
							 				<div class="col-md-6">
											  	<div class="form-group">
										  			<label class="col-md-3 control-label">Total Holiday</label>
										  			<div class="col-md-8 col-xs-11">
														<input id="total_holidays" name="total_holidays" type="text" class="form-control required valid"  placeholder="Total Holidays" value="<?php if($mode=='Add'){ echo '0';} else if($mode=='Edit') { echo $rel['total_holidays']; } ?>">
													</div>
											  	</div>
											</div>
							 				<div class="col-md-6">
											  	<div class="form-group">
											  		<label class="col-md-3 control-label">Holiday To Date*</label>
											  		<div class="col-md-8 col-xs-11">
														<input id="holiday_to_date" name="holiday_to_date" type="text" class="form-control default-date-picker required valid" title="Date" placeholder="Holiday To Date" value="<?php if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['holiday_to_date']));} ?>">
													</div>
											  	</div>
											</div>
							 			</div>
							 			<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
							 			<h4>Add Weekly Holidays</h4>
							 			<div class="col-md-12 margin_row">
							 				<div class="col-md-6">
											  	<div class="form-group">
											  		<label class="col-md-3 control-label">Weekly Off</label>
											  		<div class="col-md-6 col-xs-11">
											  			<select type="text" autocomplete="off" class="input-with-feedback form-control" id="input-with-feedback" maxlength="140" data-fieldtype="Select" data-fieldname="weekly_off" placeholder="" data-doctype="Holiday List">
															<option value="">Select Week Day</option>
															<option value="Sunday">Sunday</option>
															<option value="Monday">Monday</option>
															<option value="Tuesday">Tuesday</option>
															<option value="Wednesday">Wednesday</option>
															<option value="Thursday">Thursday</option>
															<option value="Friday">Friday</option>
															<option value="Saturday">Saturday</option>
														</select>
											  		</div>
											  		<div class="col-md-3">
											  			<input type="button" class="add-holidays-row btn btn-success" value="Add To Holidays">
											  		</div>
											  	</div>
											</div>
							 			</div>
							 			<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
							 			<h4>Holidays</h4>
							 			<div class="col-md-12 margin_row">
							 				<div class="col-md-6">
											  	<div class="form-group">
											  		<label class="col-md-3 control-label"></label>
											  		<div class="col-md-3 col-xs-11"><input type="text" name="add_holiday_date" class="form-control default-date-picker" id="add_holiday_date" placeholder="Date"></div>
													<div class="col-md-3 col-xs-11"><input type="text" name="add_holiday_description" class="form-control" id="add_holiday_description" placeholder="Description"></div>
													<div class="col-md-3 col-xs-11"><input type="button" class="add-row btn btn-success" value="+"></div>
											  	</div>
											 </div>
										</div>
										<div class="col-md-12 margin_row">
											<div class="col-md-6">
											  	<div class="form-group">
											  		<label class="col-md-2 control-label"></label>
											  		<div class="col-md-9 col-xs-11">
												  		<table class="table table-borederd" id="etable">
															<thead>
																<tr>
																	<th><input type="checkbox" id="selectAll" /></th>
																	<th>Date</th>
																	<th>Description</th>
																</tr>
															</thead>
															<tbody>
																<?php if ($mode == "Edit") {
																	$querye = "select * from hrms_holiday where holiday_id='" . $rel['id'] . "' and status = '0'";
																	$rele = $dbcon->query($querye);
																	$ecount = mysqli_num_rows($rele);
																	$counte = 1;
																	while ($rowe = mysqli_fetch_array($rele)) {
																?>
																		<tr>
																			<td><input type='checkbox' name='record'></td>
																			<td>
																				<span id='ncnt<?php echo $counte; ?>'></span>
																				<?php echo $rowe['holiday_date']; ?>
																				<input type='hidden' name='holiday_date[]' value='<?php echo $rowe['holiday_date'] ?>' id="holiday_date" class='holiday_date' />
																			</td>
																			<td>
																				<?php echo $rowe['holiday_description'] ?>
																				<input type='hidden' name='holiday_description[]' value='<?php echo $rowe['holiday_description'] ?>' id="holiday_description" class='holiday_description' />
																			</td>
																		</tr>
																<?php
																		$counte++;
																	}
																} ?>
															</tbody>
														</table>
												    </div>
											  	</div>
											</div>
										</div>
										<div class="col-md-12 margin_row">
											<div class="col-md-6">
											  	<div class="form-group">
											  		<label class="col-md-2 control-label"></label>
											  		<div class="col-md-8 col-xs-11">
											  			<button type="button" class="delete-row btn btn-danger">Delete Row</button>
											  		</div>
											  	</div>
											</div>
										</div>
										<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
										<h4>Color</h4>
										<div class="col-md-12 margin_row">
											<div class="col-md-6">
											  	<div class="form-group">
											  		<label class="col-md-3 control-label">Color Code</label>
											  		<div class="col-md-8 col-xs-11">
											  			<input type="color" id="holiday_color_code" name="holiday_color_code" class="form-control required valid" title="Date" placeholder="Color Code" value="<?php if($mode=='Edit'){ echo $rel['holiday_color_code'];} ?>" >
											  		</div>
											  	</div>
											</div>
										</div>
										<div class="col-md-12 margin_row">
											<div class="col-md-6">
											  	<div class="form-group">
											  		<label class="col-md-3 control-label">Status</label>
											  		<div class="col-md-8 col-xs-11">
											  			<select id="status" class="select2" name="status" required>
															<option selected disabled value="">SELECT STATUS</option>
															<option value="0" <?php if($rel['status'] == '0') { echo 'selected'; } ?>>Active</option>
															<option value="1" <?php if($rel['status'] == '1') { echo 'selected'; } ?>>InActive</option>
														</select>
											  		</div>
											  	</div>
											</div>
										</div>
										<div class="col-md-12 text-center">
											<br>
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?= ROOT . HRMS_ROOT . 'hrms_holiday_list' ?>" type="button" class="btn btn-danger">Cancel</a>
										</div>
							 		</div>
							</div>
							<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
							<input type='hidden' name='eid' id='eid' value='<?= $rel['id'] ?>' />
							<input type="hidden" name="row_cnt" id="row_cnt" value="<?= ($mode == 'Edit') ? $ecount : '0' ?>">
							</form>
					</div>
			</section>
			</div>
			</div>
		</section>
	</section>
	<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_holiday.js?<?= time() ?>"></script>
	<script>
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		$('#holiday_from_date, #holiday_to_date, .default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

		$("#holiday_to_date").change(function () {
		    var startDate = document.getElementById("holiday_from_date").value;
		    var endDate = document.getElementById("holiday_to_date").value;
		    if ((Date.parse(parseDate(endDate)) <= Date.parse(parseDate(startDate)))) {
		        alert("Holiday to date should be greater than holiday from date");
		        document.getElementById("holiday_to_date").value = "";
		    }
		});
		function parseDate(str) {
          var mdy = str.split('-');
          return new Date(mdy[2], mdy[1] - 1, mdy[0]);
      	}
	</script>
	<script type="text/javascript">
		$(".add-row").click(function() {
			var count = $('#row_cnt').val();
			var holidayDate = $("#add_holiday_date").val();
			var holidayDescription = $("#add_holiday_description").val();
			var new_cnt = Number(count) + 1;
			$('#row_cnt').val(new_cnt);
			var markup = "<tr><td><input type='checkbox' name='record'></td><span id='ncnt" + new_cnt + "'></span><input type='hidden' name='holiday_date[]' value='" + holidayDate + "' class='holiday_date' /><td>" + holidayDate + "</td><input type='hidden' name='holiday_description[]' value='" + holidayDescription + "'  class='holiday_description' /></td><td>" + holidayDescription + "</td></tr>";
			$("#etable tbody").append(markup);
			$("#add_holiday_date").val(" ");
			$("#add_holiday_description").val(" ");
		});

		// Find and remove selected table rows
		$(".delete-row").click(function() {
			$("#etable tbody").find('input[name="record"]').each(function() {
				if ($(this).is(":checked")) {
					$(this).parents("tr").remove();
				}
			});
		});

		$(".add-holidays-row").click(function() {
			var holidayFromDate = $("#holiday_from_date").val();
			var holidayToDate = $("#holiday_to_date").val();
			if (holidayFromDate != "" && holidayToDate != "") {
				var currentWeek = $("select#input-with-feedback option:selected").val();
				if (currentWeek != "") {
					$.ajax({
						type: "POST",
						url: root_domain + 'hrms/app/hrms_holiday/',
						data: {
							mode: "holidays_add",
							currentWeek: currentWeek,
							holiday_from_date: holidayFromDate,
							holiday_to_date: holidayToDate,
						},
						success: function(response) {
							var new_cnt = 0;
							var markup = "";
							var appendHTML = [];
							$.each(jQuery.parseJSON(response).aaData, function(index, data) {
								appendHTML.push("<tr><td><input type='checkbox' name='record'></td><span id='ncnt" + new_cnt + "'></span><input type='hidden' name='holiday_date[]' value='" + data.selected_date + "' class='holiday_date' /><td>" + data.selected_date + "</td><input type='hidden' name='holiday_description[]' value='" + data.weekday + "'  class='holiday_description' /></td><td>" + data.weekday + "</td></tr>");
								new_cnt = new_cnt + 1;
							});
							$("#etable tbody").append(appendHTML);
						}
					});
				} else {
					$("select#input-with-feedback").css('border-color', 'red');
					$("#holiday_from_date").css('border-color', '#cccccc');
					$("#holiday_to_date").css('border-color', '#cccccc');
				}
			} else {
				$("#holiday_from_date").css('border-color', 'red');
				$("#holiday_to_date").css('border-color', 'red');
			}
		});
	</script>
</body>
</html>