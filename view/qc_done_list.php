<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="QC Done";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>QC DONE LIST</title>
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
			<div class="col-lg-12">
				<!--breadcrumbs start -->
				<section class="panel">
					<header class="panel-heading">
						<h3><?=$mode.' '.$form?> List</h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li class="active"><?=$form?> list</li>
						</ul>
					</div>
				</section>
				<!--breadcrumbs end -->
			</div>	
		</div>
		<!-- Heading Filter Start-->
		
		<!--state overview start-->
		<div class="row">			
			<div class="col-sm-12">
				<section class="panel">
					
					<div class="panel-body">
						<div class="col-md-4">
							<label class="col-md-4 control-label" style="">Qc Type*</label>
							<div class="col-md-8" style="padding-left: 9px;">
								<select class="select2" name="grn_against" id="grn_against" title="Select GRN Against" required onchange="load_finish_qc_pending_datatable()">
									<option value="">--Select Qc Type--</option>
									<option value="1" <?=($rel['ref_type']=='1')?'selected':''?>> Jobwork </option>
									<option value="2" <?=($rel['ref_type']=='2')?'selected':''?>> Purchase Order </option>
									<option value="3" <?=($rel['ref_type']=='3')?'selected':''?>>Service Purchase Order </option>
									<option value="4" <?=($rel['ref_type']=='4')?'selected':''?>>Direct GRN </option>
									<option value="5" <?=($rel['ref_type']=='5')?'selected':''?>>Outside So GRN </option>
									<option value="6" <?=($rel['ref_type']=='6')?'selected':''?>>Returnable Chalan GRN </option>
									<option value="7" <?=($rel['ref_type']=='7')?'selected':''?>>Stock Transfer GRN </option>
								</select>
							</div>  
						</div>

						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="finish-qc-pending-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>QC No.</th>					  					  	  
										<th>QC Date</th>					  					  	  
										<th>Grn No</th>					  					  	  
										<th>Grn Date</th>	
												  	  
										<th>Product Name</th>				  					  	  <th>Workorder No</th>
										<th>Salesorder No</th>		
										<th>Accept Qty</th>				  					  	  
										<th>Reject Qty</th>				  					  	  
										<th>Reprocess Qty</th>				  					  	  
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
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/preview_attach_document.php');?>   
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/qc_done_list.js?<?=time()?>"></script>

<script>
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
$(".select2").select2({
				width: '100%'
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
</body>
</html>
