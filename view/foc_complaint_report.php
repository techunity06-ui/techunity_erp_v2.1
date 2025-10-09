<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="FOC Complaint Report";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>FOC COMPLAINT</title>
<?php include_once('../include/include_css_file.php');?>
<script>
function generate_service_user_report() 
{
	var date=$("#rep_date").val();
	var cat_id=$("#cat_id").val();
	var status_id=$("#status_id").val();
	
	
	$("#adv-table").dataTable({
		"bStateSave": true,
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"scrollX": true,
		"bDestroy": true,
		"bServerSide" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[-1, 10, 20, 30, 50], ["All", 10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+'app/complaint/',
		"fnServerParams": function ( aoData ) {
			aoData.push( {"name": "mode", "value": "complaint_install_report"},{"name": "comp_type", "value": "2"},{"name": "report_type", "value": "1"}, {"name": "date", "value": date}, {"name": "cat_id", "value": cat_id}, {"name": "status_id", "value": status_id} );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
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
			<li><a href="<?=ROOT.'foc_complaint_report'?>"> <?=$form?></a></li>
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
			<a href="javascript:;" onClick="tableToExcel('adv-table', 'FOC Complaint Report')" ><button class="btn btn-success btn-flat">Export Excel</button></a>
		</span>
		<span class="tools pull-right">
			<button class="btn btn-warning btn-flat" onClick="PrintMe('user-adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>											
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
								<input type="text" id="rep_date" onChange="generate_service_user_report();" class="form-control datepikerdemo" value="">
								<span class="input-group-btn">
									<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
								</span>
							</div>
						</div>
					</div>	
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="control-label col-md-3">Select Machine</label>
						<div class="col-md-9">
							<select class="select2" name="cat_id" id="cat_id" onChange="generate_service_user_report();" >
								<?=get_all_category($dbcon,"","");?>
							</select>
						</div>
					</div>	
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<label class="control-label col-md-3">Select Status</label>
						<div class="col-md-9">
							<select class="select2" name="status_id" id="status_id" onChange="generate_service_user_report();" >
								<option value="">All</option>
								<?=getAllStatus($dbcon,'');?>
							</select>
						</div>
					</div>	
				</div>
			</div>

			<div class="panel-body">
				<div class="adv-table dt-resp">
					<table class="display table table-bordered table-striped" id="user-table">
					</table>
					<div style="overflow-x: auto" id="user-adv-table">
						<table class="display table table-bordered table-striped" id="adv-table">
							<thead>
								<tr>
									<th>Client Name</th>
									<th>Machine Name</th>
									<th>Product Name</th>
									<th>City</th>
									<th>Complaint No</th>
									<th>Employee Name</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
							</tbody>				 
						</table>
					</div>
				</div>
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
