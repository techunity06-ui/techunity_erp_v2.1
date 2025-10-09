<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");

	$form="Employee Leave Balance Report";
	$user_id=$_SESSION['user_id'];
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	$infopage = pathinfo(__FILE__);
	$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
<section id="container" >
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
		<h3 style="float:left;"><?=$form?></h3><br>
	</header>	
	<div class="">
		<ul class="breadcrumb">
			<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
			<li><a href="<?= ROOT . HRMS_ROOT . 'emp_atten_report'?>"><?=$form?></a></li>
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
		<span class="tools pull-right">
			<a href="javascript:;" onClick="tableToExcel('adv-table', 'Employee Leave Balance')" ><button class="btn btn-success btn-flat">Export Excel</button></a>	
		</span>
		<span class="tools pull-right">
			<button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>											
		</span>	
		<?=$form?>
	</header>				
	<div class="panel-body">
		<div class="row">
			<div class="col-md-12">
				
				<div class="form-group" style="margin-top:20px;">
					<label class="control-label col-md-2" >Choose Date</label>
					<div class="col-md-3">
						<div class="input-group date form_datetime-component">
							<?php 
								$start=date('01-m-Y');
							?>
							<input type="hidden" id="from_date"  value="<?=$start?>">
							<input type="hidden" id="to_date"  value="<?=date('t-m-Y')?>">
							<input type="text" id="rep_date"  onChange="generate_report_emp_leave_balance();" class="form-control datepikerdemo" value="">
							<span class="input-group-btn">
								<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
							</span>
						</div>
					</div>
					
				</div>	
				<?php if($usertype!='3') { ?>
					<div class="col-md-2" style="text-align:right;">Select Employee </div>
					<div class="col-md-4" >
						<select class="select2" name="user_id" id="user_id" onChange="generate_report_emp_leave_balance();">
							<option value="">--Select Employee--</option>
							<?php
								$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `company_id` = $companyID and `l_status` = 0 and `l_group` = '58' order by l_name ");
								while ($r = $query->fetch_assoc()) {
									echo '<option value="' . $r['l_id'] . '">' . $r['l_name'] . '</option>';
								}
							?>
						</select> 
					</div>
					<?php } else { ?>
					<input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id ?>" />
				<?php } ?>
			</div>
			
			<div class="adv-table" id="adv-table" style="margin-top:120px;">
				
			</div>
		</div>
		
	</div>	
	
</section>
</div>
</div>
<!--state overview end-->
</section>
	
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<?php include_once('../../include/report_common_scripts.php');?>   

<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
function cb(start, end) {
	$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}
cb(moment().subtract(29, 'days'), moment());

$('.datepikerdemo').daterangepicker({
	locale: {
		format: 'DD-MM-YYYY'
	},
	"autoApply": true,	
	"startDate": $('#from_date').val(),
	"endDate": $('#to_date').val(),	
	ranges: {
		'Today': [moment(), moment()],
		'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
		'Last 7 Days': [moment().subtract(6, 'days'), moment()],
		'Last 30 Days': [moment().subtract(29, 'days'), moment()],
		'This Month': [moment().startOf('month'), moment().endOf('month')],
		'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	}
}, cb);
$('.date-set').click(function(){
	$('.datepikerdemo').trigger('click')
});
</script>
<script>
function generate_report_emp_leave_balance() 
{
	var date=$("#rep_date").val();
	var user_id=$("#user_id").val();
	
	if(!user_id){
		toastr.warning("Employee Not Found!!!", "WARNING");
		$('#user_id').select2('focus');
		$('#adv-table').html("");
		return false;
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain + hrms_domain + 'app/hrms_employee_leave_balance/',
		data: { mode : "generate_report_emp_leave_balance", date:date, user_id:user_id },
		success: function(response)
		{
			$('#adv-table').html(response);
			Unloading();					
		}
	});	
}
$(document).ready(function() {
	generate_report_emp_leave_balance();
});
</script>
</body>
</html>