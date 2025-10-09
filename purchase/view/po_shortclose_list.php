<?php 
	session_start();
	include('../include/urlfile.php');
	
	$form="PO Short Close";
	if(empty($_SESSION['start']))
	{
   		$start=date('1-m-Y');
   		$end=date("d-m-Y");
   	}
   	else
   	{
   		$start=$_SESSION['start'];
   		$end=$_SESSION['end'];
   	}
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			PO_SHORTCLOSE_LIST
	]);
	if(!in_array(PO_SHORTCLOSE_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $branch_id = $_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PO SHORTCLOSE</title>
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
					<header class="panel-heading">
						<div class='col-lg-5 col-md-7 col-xs-9'>
                          <div class="form-group">
                             <label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
                             <div class=" col-lg-8 col-md-8 col-xs-9">
                                <div class="input-group date form_datetime-component">
                                   <?
                                      //$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
                                      ?>
                                   <input type="hidden" id="from_date" value="<?=$start?>">
                                   <input type="hidden" id="to_date" value="<?=$end?>">
                                   <input type="text" id="rep_date" onChange="load_po_req_datatable();" class="form-control datepikerdemo" value="">
                                   <span class="input-group-btn">
                                   <button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
                                   </span>
                                </div>
                             </div>
                          </div>
                       </div>
                       <div class="col-md-2">
                          	<label for="po_type_status1" class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Pending</label>
                          	<input id="status1" name="status"  type="radio" checked="checked" onClick="load_po_req_datatable();" class="" title="Pending" value="0">
    	              	</div>
                      	
                      	<div class="col-md-2">
                      		<label for="po_type_status2" class="external-event label label-primary ui-draggable" style="position: relative;cursor:pointer;">Approved</label>
                      		<input id="status2" name="status" onClick="load_po_req_datatable();" type="radio" class="" title="Approved" value="1" />
                      	</div>	 
					</header>
					<div class="panel-body">
						
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="overdue-po-req-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>PO No.</th>					  					  	  
										<th>Product Name</th>
										<th>Product Category</th>
										<th>Short Close Date</th>
										<th>Short Close Qty.</th>					  					  	  
										<th>Short Close Remark</th>					  					  	  
										<th>User Name</th>	
										<th>Approval Status</th>
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
<?php include_once($include.'/footer.php');?>
<?php include_once($include1.'preview_po_shortclose_aprv_hist.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'/include_js_file.php');?>   
<script src="<?=ROOT.PURCHASE_ROOT?>js/app/po_shortclose_list.js?<?=time()?>"></script>

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
