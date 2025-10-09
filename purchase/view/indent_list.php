<?php 
session_start();
include('../include/urlfile.php');	

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	INDENT_VIEW
]);
if(!in_array(INDENT_VIEW,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
$form="Approval Pending Indent";
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

$branch_id = $_SESSION['branch_id'];
$companyConfiguration=getCompanyConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>INDENT</title>
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
									<div class="col-md-2" style="display:none">
										<label for="po_type_status1" class="external-event label label-primary ui-draggable" style="position: relative;cursor:pointer;">All</label>
										<input id="po_type_status1" name="po_type_status" type="radio" onClick="reload_data();" class="" title="All" value="1,3">
									</div>
									<div class="col-md-3">
										<label for="po_type_status3" class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Pending</label>
										<input id="po_type_status3" name="po_type_status" checked onClick="reload_data();" type="radio" class="" title="Pending" value="1" />
									</div>
									<div class="col-md-3">
										<label for="po_type_status2" class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Done</label>
										<input id="po_type_status2" name="po_type_status" onClick="reload_data();" type="radio" class="" title="Done" value="3" />
									</div>
								</div>
								<div class="col-md-24"></div>
								<?if($companyConfiguration['branch_wise_manage']==1){?>
									<div class="col-md-6">
										
										<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'reload_data(this.value)','4','8'); ?>	
									
									</div>
								<?php} ?>
								<div class="col-md-6">	
									<span class="tools pull-right">
										<a href="<?=ROOT.PURCHASE_ROOT.'multiple_approove_indent'?>" ><button class="btn btn-success btn-flat" >Multiple <?=$form?></button></a>
									</span>
								</div>	 
							</header>	
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="po-req-table">
										<thead>
											<tr>
												<th>#</th>
												<th>Indent No</th>
												<th>Indent Date</th>
												<?if($companyConfiguration['po_work_order_wise'] ==1){?>
													<th>Sales Order No</th>
													<?}?>
													<th>WorkOrder No</th>
													<th>Product Name</th>
													<th>Product Category</th>
													<th>Branch Name</th>
													<th>Total Qty</th>
													<th>Pending Qty</th>
													<th>Short Close Qty</th>
													<!-- <th>Unit</th> -->
													<th>User Name</th>
													<th class="hidden-phone">Action</th>
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
		<div class="modal colored-header info" id="ModalSortClose" role="dialog" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog custom-width">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Sort Close Indent</h3>
						
					</div>
					<form role="form" id="FormSortClose" action="javascript:;" method="post" name="FormSortClose">
						<div class="modal-body form">
							<div class="form-group">
								<label for="remark">Remark</label>
								<textarea class="form-control" name='remark' id='remark' required=""></textarea>
							</div>		
						</div>
						<div class="modal-footer">
							<input type="hidden" name="pending_qty" id="pending_qty" value="" />
							<input type="hidden" name="rp_id" id="rp_id" value="" />
							<input type="hidden" name="mode" value="sortclose_indent" />
							<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
							<button class="btn btn-info btn-flat" id="sc_complain" type="submit">Submit</button>
						</div>
					</form>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div>
		
		<div class="modal colored-header info" id="ModalSortCloseReason" role="dialog" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog custom-width">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Sort Close Indent</h3>
						
					</div>
					
					<div class="modal-body form">
						<div id="reason_remark">

						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
					</div>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div>
		
		<?php include_once($include.'/include_js_file.php');?> 
		<?php include_once($include1.'view_indent_detail.php');?> 
		<?php include_once($include1.'indent_remark_show.php');?> 
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/indent_list.js?<?=time()?>"></script>
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
