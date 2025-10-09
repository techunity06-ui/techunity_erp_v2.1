<?php 
session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../config/session.php");
$form="G.I.R.";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
if(empty($_SESSION['start'])){
	$start = date('1-m-Y');
	$end = date("d-m-Y");
}
else{
	$start = $_SESSION['start'];
	$end = $_SESSION['end'];
}

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_GIR_LIST_SLUG_VIEW,PRODUCTION_GIR_LIST_SLUG_CREATE
]);
if(!in_array(PRODUCTION_GIR_LIST_SLUG_VIEW,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
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
	
	<?php//include_once('../include/equick_link.php');?>
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
						<li><a href="<?=ROOT.'gir_list'?>"><?=$form?> List</a></li>
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
					<div class='col-lg-4 col-md-4 col-xs-8'>
						<div class="form-group">
							<label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
							<div class=" col-lg-8 col-md-8 col-xs-9">
								<div class="input-group date form_datetime-component">
									<?php 
										//$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
									?>
									<input type="hidden" id="from_date"  value="<?=$start?>">
									<input type="hidden" id="to_date"  value="<?=$end?>">
									<input type="text" id="rep_date" onChange="load_gir_datatable();" class="form-control datepikerdemo" value="" autocomplete="off">
									<span class="input-group-btn">
										<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
									</span>
								</div>
							</div>
						</div>
					</div>	
					<div class="col-md-4">
						<label class="col-md-4 control-label" style="">GIR Type</label>
						<div class="col-md-8" style="padding-left: 9px;">
							<select class="select2" name="gir_type" id="gir_type" title="Select GIR TYPE" onchange="get_bill_type(this.value);" >
							
								<option value="">--Select GIR Type--</option>
								<option value="I" <?=($rel['gir_type']=='I')?'selected':''?>> Inward </option>
								<option value="O" <?=($rel['gir_type']=='O')?'selected':''?>> Outward </option>
							</select>
						</div>  
					</div>
					<div class="col-md-4">
						<label class="col-md-4 control-label" style="">GIR Bill Type</label>
						<div class="col-md-8" style="padding-left: 9px;">
							<select class="select2" name="gir_bill_type" id="gir_bill_type" title="Select GIR BILL TYPE" onChange="load_gir_datatable();">
								
							</select>
						</div>  
					</div>
					<div class="clearfix"></div>
					<?php if(in_array(PRODUCTION_GIR_LIST_SLUG_CREATE,$bulkAccessArray)){ ?>
					<span class="tools pull-right">
						<a href="<?=ROOT.'gir_add'?>"><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>
					</span>	
					<?php } ?>
				</header>	
				<div class="panel-body"> 
					<div class="adv-table">
						<table class="display table table-bordered table-striped" id="gir-table">
							<thead>
								<tr> 
									<th>gir Type</th> 
									<th>Gir Bill Type</th>
									<th>Gir No</th> 
									<th>Vender Name</th> 
									<th>Gir Chalan No</th> 
									<th>Gir Status</th>
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
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="js/app/gir.js?<?=time()?>"></script>

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
