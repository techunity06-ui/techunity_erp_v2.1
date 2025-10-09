<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Employee Advance";

	if(strpos($_SERVER[REQUEST_URI], "hrms_employee_advance_edit")==false) {
		$mode="Add";
	}
	else {
		$mode="Edit";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="SELECT * FROM hrms_employee_advance WHERE status IN('0','1') AND id=$id";
		$tr = $dbcon->query($query);
		if($tr->num_rows <= 0) {
			header("Location: ". DOMAIN . HRMS_ROOT ."hrms_employee_advance");
		}
		$rel=mysqli_fetch_assoc($tr);
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php');?>
	
	<style type="text/css">
		.margin_row {
			margin-top:10px !important;
		}
		.datepicker td.disabled {
			color: #ccc;
		}
	</style>
	<script type="text/javascript" src="<?php echo ROOT . HRMS_ROOT ?>js/jquery.form.min.js"></script>
</head>
<body>
<section id="container" class="sidebar-closed">
    <?php include_once('../../include/include_top_menu.php');?>
    <!--sidebar start-->
    <?php include_once('../../include/left_menu.php');?>
    <!--sidebar end-->
    
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
			<div class="row">
			  	<div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
						  <h3>New <?=$form?>
						  
						  </h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><a href="<?= ROOT . HRMS_ROOT . 'hrms_employee_advance'?>"><?=$form?> List </a></li>
						  </ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
			  	</div>	
             </div>
              <!--Customer overview start-->
			<form role="form" id="employee_advance_add" action="javascript:;" method="post" name="employee_advance_add">
			  	<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
							  New <?=$form?> 
								<span class="tools pull-right">
									<a href="javascript:;" class="fa fa-chevron-down"></a>
								</span>
							</header>	
							<div class="panel-body">
								<div class="col-md-12" style="padding-top: 25px;">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
										  		<label for="series_id" class="col-md-4 control-label">Series*</label>
											  	<div class="col-md-8 col-xs-11">
											  		<?php $series_id = ($mode == "Edit" && $rel['series_id']) ? $rel['series_id'] : get_series_by_type($dbcon, 'EMPLOYEE ADVANCE', '16'); ?>
											  		<input type="text" class="form-control" id="series_id" name="series_id" value="<?php echo $series_id; ?>" readonly />
											  	</div>
											</div>						 
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="posting_date" class="col-md-4 control-label">Posting Date*</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="posting_date" name="posting_date" placeholder="Enter Attendance Date" value="<?=($rel['posting_date'] && $rel['posting_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['posting_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>				 
										</div>
									</div>
								</div>

								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6 ">
											<div class="form-group">
											  	<label for="employee_id" class="col-md-4 control-label">Employee*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="employee_id" name="employee_id">
														<option value="">Select Employee</option>
														<?php echo getAllEmployee($dbcon, $rel['employee_id']); ?>
													</select>
											  	</div>
											</div>
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
												<label for="reply_unclaim_amount_flag" class="col-md-4 control-label"></label>
												<div class="col-md-8 col-xs-11">
								  					<input type="checkbox" name="reply_unclaim_amount_flag" id="reply_unclaim_amount_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['reply_unclaim_amount_flag'] : 'No' ?>" <?php if($rel['reply_unclaim_amount_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<label class="checkbox_label" for="reply_unclaim_amount_flag">Repay unclaimed amount from salary</label>
								  				</div>	
								  			</div>						 
										</div>
									</div>
								</div>

								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6 ">
											<div class="form-group">
											  	<label for="advance_amount" class="col-md-4 control-label">Advance Amount*</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="advance_amount" name="advance_amount" placeholder="Enter Attendance Amount" value="<?=($rel['advance_amount'])?>" />
											  	</div>
											</div>
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
												<label for="pending_amount" class="col-md-4 control-label">Pending Amount*</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="pending_amount" name="pending_amount" placeholder="Enter Pending Amount" value="<?=($rel['pending_amount'])?>" />
											  	</div>	
											</div>							 
										</div>
									</div>
								</div>

								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
											  	<label for="advance_account_id" class="col-md-4 control-label">Advance Account*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="advance_account_id" name="advance_account_id">
														<option value="">Select Account</option>
														<option value="1">SBI</option>
													</select>
											  	</div>
											</div>
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
												<label for="mode_payment_id" class="col-md-4 control-label">Mode of Payment*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="mode_payment_id" name="mode_payment_id">
														<option value="">Select Payment Mode</option>
														<option value="1">Card</option>
													</select>
											  	</div>	
											</div>							 
										</div>
									</div>
								</div>

								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6">
											<div class="form-group">
											  	<label for="purpose" class="col-md-4 control-label">purpose*</label>
											  	<div class="col-md-8 col-xs-11">
													<textarea id="purpose" name="purpose" class="form-control"><?=($rel['purpose'])?></textarea>
											  	</div>
											</div>
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
												<label for="status" class="col-md-4 control-label">Status*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="status" name="status">
														<?php echo getStatusOptions($rel['status']); ?>
													</select>	
											  	</div>  	
											</div>							 
										</div>
									</div>
								</div>
							</div>
						</section>
					</div>
			  	</div>

				<div class="row">
					<div class="col-sm-12">	
					<div style="background-color: #fff; padding: 10px 0; text-align: center;">	
						<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  			  
						<input type='hidden' name='eid' id='eid' value='<?php if($mode=='Edit'){ echo $rel['id']; } else { echo "0"; } ?>' />
						<input type="hidden" name="mode" id="mode" value="<?php if($mode=='Add'){ echo "add"; } else { echo "edit"; } ?>" />
						<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
						<a href="<?= ROOT . HRMS_ROOT . 'hrms_employee_advance' ?>" type="button" class="btn btn-danger">Cancel</a>
					</div>
					</div>
				</div>
			</form>
		</section>
    </section>
    <!--main content end-->
    <!--footer start-->
	<?php include_once('../../include/footer.php');?>
    <!--footer end-->
</section>
<!-- Modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_employee_advance.js?<?php echo time(); ?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$(".datepicker").datepicker({
	format: "dd-mm-yyyy",
    startDate: "1d",
    autoclose: true,
    todayHighlight: true
});
$(document).ready(function(){
	$(document).on("click", "#reply_unclaim_amount_flag", function(){
		var newVal = ($(this).is(":checked")) ? 'Yes' : 'No';
		$(this).val(newVal);
	});
});
</script>

</body>
</html>