<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Attendance Tools";

	if(strpos($_SERVER[REQUEST_URI], "hrms_attendance_tools_edit")==false) {
		$mode="Add";
	}
	else {
		$mode="Edit";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="SELECT * FROM hrms_attendance_tools WHERE status IN('0','1') AND id=$id";
		$tr = $dbcon->query($query);
		if($tr->num_rows <= 0) {
			header("Location: " . DOMAIN . HRMS_ROOT . "hrms_attendance_tools");
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
	<script type="text/javascript" src="<?php echo ROOT . HRMS_ROOT; ?>js/jquery.form.min.js"></script>
</head>
<body>
<section id="container" class="sidebar-closed">
    <?php include_once('../include/include_top_menu.php');?>
    <!--sidebar start-->
    <?php include_once('../include/left_menu.php');?>
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
							  <li class="active"><a href="<?= ROOT . HRMS_ROOT . 'hrms_attendance_tools'?>"><?=$form?> List </a></li>
						  </ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
			  	</div>	
             </div>
              <!--Customer overview start-->
			<form role="form" id="attendance_tools_add" action="javascript:;" method="post" name="attendance_tools_add">
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
										  		<label for="attendance_date" class="col-md-4 control-label">Date*</label>
											  	<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control datepicker" id="attendance_date" name="attendance_date" placeholder="Enter Attendance Date" value="<?=($rel['attendance_date'] && $rel['attendance_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['attendance_date'])) : ''?>" autocomplete="off" />
											  	</div>
											</div>							 
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="zone_id" class="col-md-4 control-label">Zone*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="zone_id" name="zone_id" onChange="changeZone(this.value);">
														<?php echo get_zone($dbcon,$rel['zone_id']); ?>
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
											  	<label for="branch_id" class="col-md-4 control-label">Branch*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="branch_id" name="branch_id" onChange="changeBranch(this.value);">
														<?php if($mode == 'Edit') { ?>
															<?php $where = ' AND zoneid = '.$rel['zone_id'];
															echo get_branch($dbcon, $rel['branch_id'], $where); ?>
														<?php } else { ?>
															<option value="">Choose Branch</option>
														<?php } ?>
													</select>
											  	</div>
											</div>
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
										  		<label for="department_id" class="col-md-4 control-label">Department*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="department_id" name="department_id">
														<?php echo get_departments($dbcon,$rel['department_id']); ?>
													</select>
											  	</div>
												  
											</div>							 
										</div>
									</div>
								</div>

								<div class="col-md-12">
									<div class="col-md-12 margin_row">
										<div class="col-md-6 ">
											<div class="form-group">
											  	<label for="employee_ids" class="col-md-4 control-label">Employee*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" multiple="" id="employee_ids" name="employee_ids[]">
														<?php if($mode == 'Edit') { ?>
															<?php $where = ' AND branch_id_employee = '.$rel['branch_id'];		
															echo getAllEmployee($dbcon, $rel['employee_ids'], $where); ?>
													<?php } ?>
													</select>
											  	</div>
											</div>
										</div>
										<div class="col-md-6 typeled">
											<div class="form-group">
												<label for="approval_status_id" class="col-md-4 control-label">Approval Status*</label>
											  	<div class="col-md-8 col-xs-11">
											  		<select class="select2" id="approval_status_id" name="approval_status_id">
														<?php echo get_approval_status($dbcon,$rel['approval_status_id']); ?>
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
												<label for="status" class="col-md-4 control-label">Status*</label>
											  	<div class="col-md-8 col-xs-11">
													<select class="select2" id="status" name="status">
														<?php echo getStatusOptions($rel['status']); ?>
													</select>	
											  	</div>  	
											</div>							 
										</div>
									</div>
									<div class="col-md-12">
										<div class="col-md-12 margin_row text-center">
											<br>
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?= ROOT . HRMS_ROOT . 'hrms_attendance_tools' ?>" type="button" class="btn btn-danger">Cancel</a>
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
<script src="<?= ROOT. HRMS_ROOT ?>js/app/hrms_attendance_tools.js?<?php echo time(); ?>"></script>
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
</script>

</body>
</html>