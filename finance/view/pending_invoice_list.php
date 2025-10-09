<?php 
	session_start();
	$path = '../../';
	$include = '../../include/';
	$include1 = '../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	$form="Pending Invoice";
	$infopage = pathinfo(__FILE__);
	$_SESSION['page']=$infopage['filename'];
        $branch_id = $_SESSION['branch_id'];
	//$start=date('1-m-Y');
	$start= date('d-m-Y', strtotime('-1 year'));
	$end =date("d-m-Y");
	//Ankit Sompura 09-01-2021
	// $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	// 	FINANCE_PENDING_INVOICE_LIST
	// ]);
	// if(!in_array(FINANCE_PENDING_INVOICE_LIST,$bulkAccessArray)){
 //       header("Location: ".DOMAIN."permission_access");
 //    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once($include.'include_css_file.php');?>
</head>
<body>
<section id="container">
<?php include_once($include.'include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($include.'left_menu.php');?>
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
						<li><a href="<?=ROOT.FINANCE_ROOT.'pending_invoice_list'?>"><?=$form?> List</a></li>
					</ul>
				</div>
			</section>
			<!--breadcrumbs end -->
		</div>
	</div>
	
	<div class="row">		
		<!--state overview start-->
		<div class="row">			
			<div class="col-sm-12">
				<section class="panel">
					<header class="panel-heading">
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label col-md-4">Choose Date</label>
								<div class="col-md-7">
									<div class="input-group date form_datetime-component">
										<input type="hidden" id="from_date" value="<?=$start?>">
										<input type="hidden" id="to_date" value="<?=$end?>">
										<input type="text" id="rep_date" onChange="load_pending_invoice_datatable();" class="form-control datepikerdemo" value="">
										<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
										</span>
									</div>
								</div>
							</div>
						</div>	
						<div class="col-md-7">
							<div class="form-group">
								
								<div class="col-md-3">
									<label><input type="radio" style="width: 20px;height: 20px;" id="mode_type_spare" name="mode_type" onChange="load_pending_invoice_datatable();" checked value="fetch_service"> <strong>Paid Spare</strong></label>
								</div>
								<div class="col-md-4">
									<label><input type="radio" style="width: 20px;height: 20px;" id="mode_type_service" name="mode_type" onChange="load_pending_invoice_datatable();" value="fetch_service_inv"> <strong>Service Charge Bill</strong></label>
								</div>
								<div class="col-md-3">
									<label><input type="radio" style="width: 20px;height: 20px;" id="mode_type_bos" name="mode_type" onChange="load_pending_invoice_datatable();" value="fetch_bill_of_supply"> <strong>FOC Spare</strong></label>
								</div>
							</div>
						</div>	
					
						<span class="tools pull-right">
							
						</span>
						<div class="col-md-12" style="height:20px;"></div>
                                                <div class="col-md-5">
                                                    <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_pending_invoice_datatable()','4','7'); ?>
                                                </div>
					</header>	
					<div class="panel-body">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="pending-invoice-datatable">
								<thead>
									<tr>
										<th>Quotation/Complaint No</th>
										<th>Quotation/Complaint Date</th>
										<th>Customer</th>
										<th>Total</th>
										<th>Owner</th>
										<th class="hidden-phone">Action</th>	
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
<?php include_once($include.'footer.php');?>
<!--footer end-->

</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>
<script src="<?=ROOT.FINANCE_ROOT?>js/app/pending_invoice_list.js?<?=time()?>"></script>
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
	$('.datepikerdemo').trigger('click');
});
</script>
</body>
</html>
