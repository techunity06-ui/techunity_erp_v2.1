<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Approval Indent";
	$branch_id = $_SESSION['branch_id'];
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
            INDENT_APPROVE
    ]);
    $start_date=date("01-m-Y");
	$end_date=date("d-m-Y");
    if(!in_array(INDENT_APPROVE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MULTIPLE INDENT APPROVAL</title>
		<?php include_once($include.'/include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'/include_top_menu.php');?>
			<?php include_once($include.'/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
								  <h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.PURCHASE_ROOT.'indent_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
								  New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="multiple_indent_approove" action="javascript:;" method="post" name="multiple_indent_approove">
										<div class="row">
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group"> 
														<label class="col-md-4 control-label" > Start Date </label>
														<div class="col-md-8 col-xs-11">
															<input id="start_date" name="start_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$start_date?>" placeholder="Start Date" autocomplete="off" onchange="load_pending_indent()">
														</div>
													</div>
												</div>
												
												<div class="col-md-4">
													<div class="form-group"> 
														<label class="col-md-4 control-label" > End Date </label>
														<div class="col-md-8 col-xs-11">
															<input id="end_date" name="end_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$end_date?>" placeholder="Start Date" autocomplete="off" onchange="load_pending_indent()">
														</div>
													</div>
												</div>
												<?if($companyConfiguration['branch_wise_manage']==1){?>
												<div class="col-md-4">
													<?php echo getBranchBox($dbcon, $branch_id, '', false, false, 'get_pending_work_order_no(this.value);load_pending_indent();get_pending_indent_no(this.value);get_pending_product(this.value);load_pending_indent()'); ?>	
												</div>
											<?php} ?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label" > Sales Order No </label>
														<div class="col-md-8 col-xs-11">
														<select class="select2" name="sales_order_no" id="sales_order_no" title="Select Sales Order No" onchange="get_work_order_no(this.value);load_pending_indent();">
															
														</select>
														</div>
													</div>
												</div>

												<div class="col-md-4">
													<div class="form-group">  	
													<label class="col-md-4 control-label" > Work Order No </label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="work_order_no" id="work_order_no" title="Select Work Order No" onchange="get_indent_no(this.value);load_pending_indent();">
															
														</select>
													</div>
													</div>	
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
													<label class="col-md-4 control-label"> Indent No </label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="indent_no" id="indent_no" title="Select Indent No" onchange="get_inden_pro(this.value);load_pending_indent()">
															
														</select>
													</div>
													</div>	
												</div>
												
												<div class="col-md-4">
													<div class="form-group">
													<label class="col-md-4 control-label"> Product Name </label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="product_id" id="product_id" title="Select Product" onchange="load_pending_indent()">
															
														</select>
													</div>
													</div>	
												</div>
											</div>	 
											
											<!--<div class="col-md-12" style="margin-top:10px;text-align:center">
												<button type="submit" class="btn btn-success" id="save" name="save">Approve All </button>
												<a href="<?=ROOT.'po_req_list'?>" type="button" class="btn btn-danger">Cancel</a>
											</div>-->
											
											<div class="col-md-12" style="margin-top:10px;">
												<div id="multiple_appr_data">
														
												</div>	
											</div>
											<div class="clearfix"></div>
											<button type="submit" class="btn btn-success" id="save" name="save">Approve All </button>
											<a href="<?=ROOT.PURCHASE_ROOT.'indent_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>					
										</div>
										<input type='hidden' name='mode' id='mode' value='multiple_indent_approove' />
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'/footer.php');?>
		</section>
		<?php include_once($include.'/include_js_file.php');?>   
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/multiple_approove_indent.js?<?=time()?>"></script>
<script>


$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});

$(".form_datetime").datetimepicker({
    format: 'dd-mm-yyyy hh:ii',
    autoclose: true,
    todayBtn: true,
    pickerPosition: "bottom-left"

});

/*$(document).ready(function (){
	var max_limit = 5; // Max Limit
	$("#save").hide();	
    $(".chk_box").each(function (index){
		this.checked = (".chk_box" < max_limit);
    }).change(function (){
        if ($(".chk_box:checked").length > max_limit){
            this.checked = false;
        }
    });

});*/

function add_customer_purchase()
{
	$("#bs-example-modal-lg").modal("show");
	$("#cat_id").val('1');
}
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
