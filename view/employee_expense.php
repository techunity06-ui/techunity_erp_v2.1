<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Employee Expense";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];  
	//Ankit Sompura 09-01-2021
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_EMPLOYEE_EXPENSE_LIST
	]);
	if(!in_array(FINANCE_EMPLOYEE_EXPENSE_LIST,$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>

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
						<h3><?=$form?> List</h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li><a href="<?=ROOT.'employee_expense'?>"><?=$form?> list</a></li>
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
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Select Employee</label>
								<div class="col-md-7">
									<select class="select2" name="emp_name" id="emp_name" onchange="load_expense_datatable();">
										<option value="">--Select Employee--</option>
										<?php getAllEmployeeUser($dbcon); ?>
									</select>
								</div>
							</div>
						</div>	
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Choose Status</label>
								<div class="col-md-7">
									<select class="select2" name="emp_status" id="emp_status" onChange="load_expense_datatable();">
										<option value="">--Select Status--</option>
										<option value="1">Approved</option>	
										<option value="0">Pending</option>	
										<option value="2">Rejected</option>	
									</select>
								</div>
							</div>
						</div>	
						
					</header>	
					<div class="panel-body">
					
						
						<div class="adv-table" id="adv-table">
							<table class="display table table-bordered table-striped" id="expense-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Date</th> 
										<th>Expense</th> 
										<th>Employee Name</th> 
										<th>Complaint</th> 
										<th>Customer</th> 
										<th>Amount</th> 
										<th>Remark</th> 
										<th>Status</th> 
										<th>Action</th> 
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
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
<?php  
	include_once('../include/footer.php');

?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/employee_expense.js?<?=time()?>"></script> 
<!--<script src="js/count.js"></script>-->
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

$('.date-set').click(function(){
	$('.datepikerdemo').trigger('click');
});
</script>
</body>
</html> 