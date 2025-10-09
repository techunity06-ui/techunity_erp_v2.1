<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Service Ledger Report";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
<script>
/*function load_service_ledger() 
{
	var date=$("#rep_date").val();
	var cust_id=$("#cust_id").val();
	
	if(!cust_id){
		toastr.warning("Company Not Found!!!", "WARNING");
		$('#cust_id').select2('focus');
		$('#adv-table').html("");
		return false;
	}
	
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+'app/service_ledger_report/',
		data: { mode : "load_service_ledger", date:date, cust_id:cust_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();					
		}
	});	
}*/
function load_service_ledger() 
{
	
	var date=$("#rep_date").val();
	var cust_id=$("#cust_id").val();
	if(!cust_id){
		toastr.warning("Company Not Found!!!", "WARNING");
		$('#cust_id').select2('focus');
		$('#adv-table').html("");
		return false;
	}
	//alert(emp_id);
	Loading();
	
	$.ajax({
		type: "POST",
		url: root_domain+'app/cust_ledger/',
		data: { mode : "generate_report",date:date,cust_id:cust_id },
		success: function(response)
		{
			//alert(response);
			//console.log(response);
			$('#adv-table').html(response);
			Unloading();
								
		}
	});	
	
}
</script>
</head>
<body>
<section id="container" >
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
			<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
			<li><a href="<?=ROOT.'service_ledger_report'?>"> <?=$form?></a></li>
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
			<a href="javascript:;" onClick="tableToExcel('adv-table', 'Instalment Collection')" ><button class="btn btn-success btn-flat" >Export Excel</button></a>	
		</span>
		<span class="tools pull-right">
			<button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>											
		</span>	
		<?=$form?>
	</header>				
	<div class="panel-body">
		<div class="row">
			<div class="col-md-12" style="margin-top:20px;">
				<div class="col-md-6">
					<div class="form-group">
						<label class="control-label col-md-4" >Choose Date</label>
						<div class="col-md-6">
							<div class="input-group date form_datetime-component">
								<?php 
									$start=date('01-m-Y');
								?>
								<input type="hidden" id="from_date" value="<?=$start?>">
								<input type="hidden" id="to_date" value="<?=date('t-m-Y')?>">
								<input type="text" id="rep_date" onChange="load_service_ledger();" class="form-control datepikerdemo" value="">
								<span class="input-group-btn">
									<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
								</span>
							</div>
						</div>
					</div>	
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="control-label col-md-4">Company</label>
						<div class="col-md-6">
							<select class="select2" name="cust_id" id="cust_id" onChange="load_service_ledger();" >
								<?=getcust($dbcon,$cust_id);?>	
							</select>
						</div>
					</div>	
				</div>	
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
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<?php include_once('../include/report_common_scripts.php');?> 
<!-- <script src="<?=ROOT?>js/app/ledger.js?<?=time()?>"></script>-->

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
	//$('.default-date-picker').datepicker('show');
});
</script>
</body>
</html>
