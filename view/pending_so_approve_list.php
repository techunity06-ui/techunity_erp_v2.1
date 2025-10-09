<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$form="Pending Sales Order";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
if(empty($_SESSION['start'])) {
	$start=date('1-m-Y');
	$end=date("d-m-Y");
}
else {
	$start=$_SESSION['start'];
	$end=$_SESSION['end'];
}
$fil_type=$dbcon->real_escape_string($_REQUEST['id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once('../include/include_top_menu.php');?>
		<?php include_once('../include/left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$form?> List</h3>
								<?
											//Hide approve btn if not allowed
											//$aprvfinal_btn_per=check_permission("#mod_po_per_div_sec",$_SESSION['user_id'],'final_aprv',$dbcon);
											//var_dump($aprvfinal_btn_per);
								?>	

							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.'pending_sales_order_list'?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
					</div>
				</div>
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
												<input type="text" id="rep_date" onChange="load_pending_sales_order_list_datatable();" class="form-control datepikerdemo" value="">
												<span class="input-group-btn">
													<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>	
								<div class="col-md-5">
									<div class="form-group">
										<label class="control-label col-md-4">Choose Customer</label>
										<div class="col-md-7">
											<select class="form-control" name="type_id" id="type_id" onChange="reload_data();">
												<option value="1">Pending</option>
												<option value="3">Approved</option>
												<option value="-1">All</option>
											</select>
										</div>
									</div>
								</div>
								<span class="tools pull-right"></span>
								<div class="col-md-12" style="height:20px;"></div>
							</header>	
							<div class="panel-body">
								<div class="adv-table dt-resp">
									<table class="display table table-bordered table-striped" id="order-confirm-datatable">
										<thead>
											<tr>
												<th>Sales Order No</th>
												<th>Sales Order Date</th>
												<th>Customer Name</th>
												<th>City </th>
												<th>Grand Total</th>
												<th>User Name</th>
												<th>Approval Status</th>
												<th class="hidden-phone">Action</th>	
											</tr>
										</thead>
										<tbody>
										</tbody>				 
									</table>
									<input type="hidden" id="fil_type" value="<?=$fil_type?>">
								</div>
							</div>
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once('../include/footer.php');?>
		<?php include_once('../crm/include/preview_ord_po_aprv_hist.php'); ?>
		<div class="modal colored-header info" id="pay_dtl_modal" role="dialog" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
						<h3>Quotation: <strong id="head_pay_qt_no"></strong></h3>
					</div>
					<div class="modal-body form">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group" id="entry_pay_dtl_modal_div">
									<table class="display table table-bordered table-striped">
										<tr>
											<th>Payment Mode</th>
											<th>Due Amount</th>
											<th>Ref No.</th>
											<th>Paid Amount</th>
											<th>Action</th>
										</tr>
										<tr>
											<td>
												<select class="form-control" name="payment_mode_id" id="payment_mode_id" onChange="" title="Select Payment Mode">
													<?=getpaymentmode($dbcon,'');?>	
												</select>
											</td>
											<td>
												<input type="number" min="0" name="due_amt" id="due_amt" class="form-control" value=""  readonly />
											</td>
											<td>
												<input type="text" class="form-control" name="referenceno" id="referenceno" placeholder="Ref No." value="" autocomplete="off" />
											</td>
											<td>
												<input type="number" min="0" name="paid_amt" id="paid_amt" class="form-control" placeholder="Paid Amount" value="" />
											</td>
											<td>
												<button type="button" id="add_pay_dtl_btn" onclick="add_pay_dtl();" class="btn btn-success">Add</button>
											</td>
										</tr>
									</table>
								</div>
								<div class="form-group">
									<div class="adv-table dt-resp">
										<table class="display table table-bordered table-striped" id="pay-dtl-modal-datatable">
											<thead>
												<tr>
													<th>Sr.</th>
													<th>Payment Mode</th>
													<th>Payment Date</th>
													<th>Ref No.</th>
													<th>Paid Amount</th>
													<th>Confirmation Status</th>
													<th class="">Action</th>	
												</tr>
											</thead>
											<tbody>
											</tbody>				 
										</table>
									</div>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-12 text-center" style="margin-top:10px;">
									<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
								</div>
							</div>
							<input type='hidden' name='qt_pay_ref_id' id='qt_pay_ref_id' value='' />	
						</div>
					</div>	
				</div>
			</div>
		</div>
	</section>
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/pending_so_approve_list.js?<?=time()?>"></script>
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
		$(function(){
			setTimeout(function(){ $('#sidebar > ul').hide(); }, 1000);
		});
			<?//Hide approve btn if not allowed - Payment Modal
			$mod_btn_per=check_permission("#mod_per_div_sec",$_SESSION['user_id'],'aprv',$dbcon);
			if(!$mod_btn_per){
				?>	
				$('#mod_per_div_sec').hide();
				<?
			}
			?>
			<?//Hide approve btn if not allowed - Po Modal
			//$mod_btn_per=check_permission("#mod_po_per_div_sec",$_SESSION['user_id'],'aprv',$dbcon);
			//if(!$mod_btn_per){
			?>	
				//$('#mod_po_per_div_sec').hide();
				<?
			//	}
				?>
			</script>
		</body>
		</html>
