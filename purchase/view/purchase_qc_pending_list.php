<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="Purchase QC Pending";
	//check permission for get sales order details
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    QC_DONE_PURCHASE_QC_PENDING_LIST
	]);
	if(!in_array(QC_DONE_PURCHASE_QC_PENDING_LIST,$bulkAccessArray)) {
		header("Location: ".DOMAIN."permission_access");
	}
	$branch_id = $_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PURCHASE QC LIST</title>
	<?php include_once($include.'/include_css_file.php');?>
</head>
<body>
<section id="container">
<?php include_once($include.'/include_top_menu.php');?>
<!--sidebar start-->
<?php include_once($include.'/left_menu.php');?>
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
		<!--state overview start-->
		<div class="row">			
			<div class="col-sm-12">
				<section class="panel">
					
					<div class="panel-body">
						<div class="col-md-12" >
							<div class="col-md-4">
								<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'load_purchase_qc_pending_datatable()'); ?>	
							</div>
							<div class="col-md-8 text-right">
												<button class="btn btn-success btn-flat" onclick="qc_all();">QC All</button>
											</div>
						</div>
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="purchase-qc-pending-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>GRN No.</th>					  					  	  
										<th>GRN Date</th>					  					 <th>Workorder No</th>
										<th>Salesorder No</th>		 	  
										<th>Vendor Name</th>				  					  	  
										<th>Product Name</th>	
										<th>Product Category</th>				  					  	  
										<th>Product Qty</th>				  					  	  
										<th>User Name</th>	
											<?php if($_SESSION['branch_id']==0){ ?>
												<th>Branch Name</th>
											  <?php } ?>										
										<th>Add QC</th>	
										<th class="nosort">  <input id="checkAll" type="checkbox" onclick="checkAll();"  name="chk[]"/></th>			  					  	  
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
<?php include_once($include.'/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'/include_js_file.php');?>   
<?php include_once($include1.'qc_all_model.php'); ?>
<script src="<?=ROOT.PURCHASE_ROOT?>js/app/purchase_qc_pending_list.js?<?=time()?>"></script>

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
