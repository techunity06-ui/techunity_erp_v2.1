<?php 
	session_start();
	include('../include/urlfile.php');
	$incPath = $path.'include/';
	
	$form="Return Old Spare Part";
	
	$userid=$_SESSION['user_id'];
	$emp_id=getEmployeeIdUser($dbcon,$userid);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>RETURN OLD SPARE</title>
<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
<section id="container">
<?php include_once($incPath.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($incPath.'left_menu.php');?>
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
										<input id="s_return_status_all" name="s_return_status" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_spare_pending_datatable();" title="All" value="">
									</label>
								</div>
								<div class="col-md-4">
									<label>
										<div class="external-event label label-warning ui-draggable" style="width:70px;">Pending</div>
										<input id="s_return_status_pend" name="s_return_status" checked="checked" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_spare_pending_datatable();" title="Pending" value="0">
									</label>
								</div>
								<div class="col-md-4">
									<label>
										<div class="external-event label label-success ui-draggable" style="width:70px;">Received</div>
										<input id="s_return_status_done" name="s_return_status" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_spare_pending_datatable();" title="Done" value="1">
									</label>
								</div>
							</div>
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
								<th>Courier Details</th>
								<th>Employee Name</th>
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

<?php include_once($incPath.'footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($incPath.'include_js_file.php');?>   
<script src="<?=ROOT?><?=SERVICE_ROOT?>js/app/return_old_spare.js?<?=time()?>"></script>
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
