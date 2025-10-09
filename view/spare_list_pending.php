<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Spare Part List To Be Sent";
	
	$userid=$_SESSION['user_id'];
	$emp_id=getEmployeeIdUser($dbcon,$userid);
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>SPARE LIST</title>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container">
<?php include_once('../include/include_top_menu.php');?>
<!--sidebar start-->
<?php include_once('../include/left_menu.php');?>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
<section class="wrapper">

<div class="row">
<div class="col-md-12">
<!--breadcrumbs start -->
<section class="panel">
<header class="panel-heading">
	<h3><?=$form?></h3>
</header>	
<div class="">
	<ul class="breadcrumb">
		<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home </a></li>
		<li><?=$form?> List</li>
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
				<div class="col-md-8">
					<div class="form-group">
						<label class="control-label col-md-2"><strong>Spare Status</strong></label>
						<div class="col-md-10">
							<div class="col-md-12">
								<div class="col-md-4">
									<label>
										<div class="external-event label label-primary ui-draggable" style="width:70px;">All</div>					
										<input id="sp_sent_status_all" name="sp_sent_status" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_spare_pending_datatable();" title="All" value="">
									</label>
								</div>
								<div class="col-md-4">
									<label>
										<div class="external-event label label-warning ui-draggable" style="width:70px;">Pending</div>
										<input id="sp_sent_status_pend" name="sp_sent_status" checked="checked" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_spare_pending_datatable();" title="Pending" value="no">
									</label>
								</div>
								<div class="col-md-4">
									<label>
										<div class="external-event label label-success ui-draggable" style="width:70px;">Sent</div>
										<input id="sp_sent_status_done" name="sp_sent_status" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_spare_pending_datatable();" title="Done" value="yes">
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<label class="control-label col-md-4"><strong>Invoice Status</strong></label>
						<div class="col-md-8">
							<select class="select2" id="s_inv_status" name="s_inv_status" onchange="load_spare_pending_datatable()">
								<option value="">ALL</option>
								<option value="0">Pending</option>
								<option value="1">Done</option>
							</select>
						</div>
					</div>
				</div>	
			</header>
			<div class="panel-body">
				<div class="adv-table">
					<table class="display table table-bordered table-striped" id="pending-spare-datatable">
						<thead>
							<tr>
								<th>#</th>
								<th>Customer</th>
								<th>Complain No</th>
								<th>Product</th>
								<th>Qty</th>
								<th>Rate</th>
								<th>Amount</th>
								<th>Requested on</th>
								<th>Courier Details</th>
								<th>Employee Name</th>
								<th>Invoice Status</th>
								<th>Spare Status</th>
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
</div>
</div>
<!--state overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->

<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/spare_list_pending.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
</script>
</body>
</html>
