<?php 
	session_start();
	include('../include/urlfile.php');	
	$form="Purchase Order Request";
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
			PO_REQ_VIEW,PO_REQ_ADD
	]);
	if(!in_array(PO_REQ_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $branch_id = $_SESSION['branch_id'];

   $companyConfiguration=getCompanyConfiguration($dbcon);
   $getspecialConfiguration=getspecialConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PO REQUEST LIST</title>
		<?php include_once($include.'/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once($include.'/include_top_menu.php');?>
			<?php include_once($include.'/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
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
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<div class='col-md-6'>
										<div class="form-group">
											<label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
											<div class=" col-lg-8 col-md-8 col-xs-9">
												<div class="input-group date form_datetime-component">
													<input type="hidden" id="from_date" value="<?=$start?>">
													<input type="hidden" id="to_date" value="<?=$end?>">
													<input type="text" id="rep_date" onChange="reload_data();;" class="form-control datepikerdemo" value="">
													<span class="input-group-btn">
													<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>	
									<div class="col-md-6">
										<!--<div class="col-md-3">
											<label for="po_type_status1" class="external-event label label-primary ui-draggable" style="position: relative;cursor:pointer;">All</label>
											<input id="po_type_status1" name="po_type_status" type="radio" <?php if($_SESSION['po_type_status_filter']==''){ echo "checked"; } ?>  onClick="reload_data();" class="" title="All" value="">
										</div>-->
										<div class="col-md-3">
											<label for="po_type_status3" class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Pending</label>
											<input id="po_type_status3" name="po_type_status" checked onClick="reload_data();" type="radio" class="" title="Pending" value="0" />
										</div>
										<div class="col-md-3">
											<label for="po_type_status2" class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Done</label>
											<input id="po_type_status2" name="po_type_status" onClick="reload_data();" type="radio" class="" title="Done" value="1" />
										</div>
									</div>
									<?php if($companyConfiguration['branch_wise_manage']==1){?>
										<div class="col-md-6">
											<?php echo getBranchBox($dbcon, $branch_id, '', false, false, 'reload_data();','4','6'); ?>
										</div>
									<?php } ?>
									<?php if(in_array(PO_REQ_ADD,$bulkAccessArray)){ ?>
									<span class="tools pull-right">
										<a href="<?=ROOT.PURCHASE_ROOT.'po_req_add_mul'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>
									</span>	
									<?php } ?>			 
								</header>	
								<div class="panel-body">
									<div class="adv-table" id="pending_table">
										<table class="display table table-bordered table-striped" id="po-req-table">
											<thead>
												<tr>
													<th>#</th>
													<?php if($companyConfiguration['po_work_order_wise'] == 1){ ?>
														<th>Work Order Details</th>
													<?php } ?>
													<th>PO Request Date</th>
													<th>Product</th>
													<th>Product Category</th>
													<th>Vendor Name</th>
													<th>Branch Name</th>
													<th>Total Qty</th>
													<th>Pending Qty</th>
													<th>Requested Qty</th>
													<th>User Name</th>
													<!--<th>Status</th>-->
													<th class="hidden-phone">Action</th>					  					  	  
												</tr>
											</thead>
											<tbody> </tbody>
					 
										</table>
									</div>


									<div class="adv-table-done" id="done_table">
										<table class="display table table-bordered table-striped" id="po-req-table-done">
											<thead>
												<tr>
													<th>#</th>
													<th>Indent No</th>
													<th>Indent Date</th>
													<th>Product Name</th>
													<th>Vendor Name</th>
													<th>Po No.</th>
													<th>Po Date</th>
													<th>Po Qty (Indent Wise)</th>
													<th>Po Qty (Total Po Qty)</th>
													<th>Branch (PO)</th>
													<th>User Name (PO)</th>
													<!--<th>Status</th>-->					  					  	  
												</tr>
											</thead>
											<tbody> </tbody>
					 
										</table>
									</div>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'/footer.php');?>
		</section>
		<?php include_once($include1.'indent_remark_show.php');?>
		<?php include_once($include.'/include_js_file.php');?>   
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/po_req.js?<?=time()?>"></script>
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
