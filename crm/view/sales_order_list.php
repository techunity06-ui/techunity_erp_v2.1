<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$getspecialConfiguration=getspecialConfiguration($dbcon);
// error_reporting(E_ALL);

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	SALES_ORDER_SLUG_EDIT,
	SALES_ORDER_SLUG_DELETE,
	SALES_ORDER_SLUG_PRINT,
	SALES_ORDER_SLUG_CREATE,
	SALES_ORDER_SLUG_READ,
	ORDER_ACCEPTANCE_SLUG_DELETE
]);

if(!in_array(SALES_ORDER_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
$form="Sales Order";
if(empty($_SESSION['start']))
{
	$start = date('1-m-Y');
	$end = date("d-m-Y");
}
else
{
	$start = $_SESSION['start'];
	$end = $_SESSION['end'];
}
$branch_id = $_SESSION['branch_id'];

$companyConfiguration=getCompanyConfiguration($dbcon);

$crm_user_type = $companyConfiguration['crm_user_type'];
$amnts = get_so_taxable_total($dbcon);
$cnyts = explode(",",$amnts);
// print_r($_SESSION);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>SALES ORDER LIST</title>
	<?php include_once('../../include/include_css_file.php');?>
	<style>
		.icons{
			width: 18%;
			float: left;
			margin: 10px 7px 10px;
			text-align: center;
			position:relative;

		}
		.icons12{
			background-color:#fff;
			padding-top:15px;
			border: 8px;
		}
		.icons p{
			text-align:center;
			font-size:15px;
			font-weight:600;
			padding-top:5px;
			font-color:white

		}

		.icon1 fa{

		}
		.icon1.success{background-color: #5cb85c;}
		.icon1.primary{background-color: #0275d8;}
		.icon1.warning{background-color: #f0ad4e;}
		.icon1.info{background-color: #5bc0de;}
		.icon1.danger{background-color: #d9534f;}
		.icon1.terques{background-color: #6ccac9;}
		.icon1.yellow{background-color: #f8d347;}
		.icon1.pink{background-color:#E5649A;}
		.icon1.mustard{background-color:#F0BD23;}
		.icon1.success,.icon1.primary,.icon1.warning,.icon1.danger,.icon1.info,.icon1.terques,.icon1.yellow,.icon1.pink,.icon1.mustard{
			width: 150px;
			height:120px;
			border-radius: 8px;
			text-align:center;
			margin:0 auto
		}
		.icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
			text-align:center;
			color:#fff;
			padding-top: 27%;
			font-size: 37px;
		}
		@media (max-width:767px){
			.icons {
				width:265px;
				float: left;
				margin: 30px 4px 25px;
				position:relative;
			}

		}
		@media (min-width:768px) and (max-width:980px)
		{
			.icons12{
				background-color:#fff;
				padding-top:20px;
				padding-bottom:20px;
				border-radius: 8px;
			}
			.icons {
				width: 17%;
				float: left;
				margin: 30px 4px 25px;
				text-align: center;
				position:relative;
			}

		}
		.icons .badge {
			position: absolute;
			right: 25px;
			top: 0px;
			z-index: 100;
		}
	</style>
</head>
<body>
	<section id="container" >
		<?php include_once('../../include/include_top_menu.php');?>
		<?php include_once('../../include/left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$form?> List</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active"><?=$form?> List</li>
								</ul>
							</div>
							<div class="row">
								<div class="col-lg-12 centeral-align">
									<div class="icons">
										<div class="icon1 success" >
											<p style="color:white;padding-top:10px;">Total Sale Amount</p>
											<h3 style="font-size:20px;color:white;padding-top:5px;" id="soamount"></h3>
										</div>
									</div>
									<div class="icons">	 	
										<div class="icon1 info" >

											<p style="color:white;padding-top:10px;">Total Sale<br> Taxable Value</p>

											<h3 style="font-size:20px;color:white;padding-top:5px;" id="sotaxamount"></h3>
										</div>
									</div>
								</div>	
							</div>
						</section>
					</div>
				</div>
				<div class="col-sm-12">
					<section class="panel">
						<header class="panel-heading respadlr0">
							<div class='col-lg-5'>
								<div class="form-group">
									<label class="control-label col-lg-4 col-md-4 col-xs-4 respad-l0">Choose Date</label>
									<div class=" col-lg-8 col-md-8 col-xs-8 respad-r0">
										<div class="input-group date form_datetime-component">
											<input type="hidden" id="from_date"  value="<?=$start?>">
											<input type="hidden" id="to_date"  value="<?=$end?>">
											<input type="text" id="rep_date"  onChange="reload_data();" class="form-control datepikerdemo" value="">
											<span class="input-group-btn"> 
												<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
										</div>
									</div>
								</div>

							</div>
							<!-- <div class='col-lg-4'>

								<php //echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'reload_data()','4','8'); ?>
							</div> -->
							<?php if($companyConfiguration['branch_wise_manage']==1){?>
								<div class='col-lg-4'>
									<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'reload_data()','4','8'); ?>
								</div>
							<?php }else{ ?>
								<input type="hidden" name="branch_id" id="branch_id" value="<?=$companyConfiguration['default_branch_id']?>" />
							<?php } ?>
							<span class="tools pull-right respadr_15">
								<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat" >Export Excel</button></a>	
								<!-- <a href="javascript:;" onClick="tableToExcel('dynamic-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>	 -->
								<?php if(in_array(SALES_ORDER_SLUG_CREATE,$bulkAccessArray)){ ?>
									<a href="<?=ROOT.CRM_ROOT.'sales_order'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
								<?php } ?>
							</span>
							<div class="col-md-12"	style="height:10px;" ></div>

							<div class="row ">
								<?php if($companyConfiguration['outside_jobwork']){ ?>
									<div class="col-md-8 mtop20">
										<div class="form-group">
											<div class="col-md-3">
												<label>
													<input id="status_all" name="jobwork_type"  checked="checked"  type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_datatable();" class="" title="All" value="">
													<div class='external-event label label-primary ui-draggable' style='position: relative;width:70px;'>All</div>					
													
												</label>
											</div>
											<div class="col-md-3">
												<label>
													<input id="status_pend" name="jobwork_type" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_datatable();" class="" title="Pending" value="0">
													<div class='external-event label label-success ui-draggable' style='position: relative;width:70px;'>Normal</div>					
													
												</label>
											</div>
											<div class="col-md-6">
												<label>
													<input id="status_comp" name="jobwork_type" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_datatable();" class="" title="Pending" value="1">
													<div class='external-event label label-danger ui-draggable' style='position: relative;width:100px;'>Outside Jobwork</div>					
													
												</label>
											</div>
										</div>
									</div>
								<?php } ?>
								<div class="col-md-4 mtop20">
									<div class="form-group">
										<label for="user_id" class="col-md-4 control-label">User</label>
										<div class="col-md-8">
											<select class="select2" name="user_id" id="user_id" onchange="reload_data()">
												<?php 
												if($getspecialConfiguration['apson_special']==1){
													echo get_tree_user($dbcon, $_SESSION['user_id'], '');
												}
												else{
													echo get_users_typewise($dbcon, '', '');
												}
												?>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-4 mtop20">
									<div class="form-group">
										<label for="user_id" class="col-md-4 control-label">Sales Order Status</label>
										<div class="col-md-8">
											<select class="select2" name="so_status" id="so_status" onchange="reload_data()">
												<option value="all">All</option>
												<option value="8">Approve Pending</option>
												<option value="1">Approve Done</option>
												<option value="2">Disapprove</option>
												<option value="3">Order Accept Pending</option>
												<option value="4">Order Accept Done</option>
												<option value="5">Invoice Pending</option>
												<option value="6">Invoice Done</option>
												<option value="7">Short Close</option>
											</select>
										</div>
									</div>
								</div>
							</div>
						</header>	
						<div class="panel-body">
							<div class="adv-table">
								<table  class="display table table-bordered table-striped" id="dynamic-table">
									<thead>
										<tr>
											<th>Sales Order No</th>
											<th>Sales Order Date</th>
											<th>Customer Name</th>
											<th>City </th>
											<th>Basic total</th>
											<th>Grand Total</th>
											<th>Po No.</th>
											<th>Po Date</th>
											<th>Approval Status</th>
											<th></th>
											<?php if($companyConfiguration['outside_jobwork']){ ?>
												<th>Jobwork Type</th>
											<?php } ?>
											<?php if($companyConfiguration['branch_wise_manage']==1){ ?>
												<th>Branch Name</th>
											<?php } ?>
											<th>User Name</th>
											<?php if(in_array(SALES_ORDER_SLUG_EDIT,$bulkAccessArray) || in_array(SALES_ORDER_SLUG_DELETE,$bulkAccessArray) || in_array(SALES_ORDER_SLUG_PRINT,$bulkAccessArray)){ ?>
												<th class="hidden-phone">Action</th>	
											<?php } ?>
										</tr>
									</thead>
									<tbody></tbody>	
									<tfoot>	
										<tr>
											<th colspan="4" style="text-align:right">Total:</th>
											<th></th>
											<th></th>
											<th></th>
											<?php if($companyConfiguration['outside_jobwork']){ ?>
												<th></th>
											<?php } ?>
											<?php if($companyConfiguration['branch_wise_manage']==1){ ?>
												<th></th>
											<?php } ?>
											<th></th>
											<th></th>
										</tr>
									</tfoot>			 
								</table>

							</div>
						</div>
					</section>
				</div>
			</section>
		</section>

		<?php include_once('../../include/footer.php');?>
	</section>
	<?php include_once('../../include/preview_ord_po_aprv_hist.php'); ?>
	<?php include_once('../include/preview_attached_doc.php');?>
	<?php include_once('../include/order_view.php');?>
	<?php include_once('../include/preview_update_user.php');?>
	<?php include_once('../../include/include_js_file.php');?>   
	<script src="<?= ROOT.CRM_ROOT?>js/app/sales_order.js?<?=time()?>"></script>
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
				  // 'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				   //'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				   //'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				   'This Month': [moment().startOf('month'), moment().endOf('month')],
				   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				   'Last 3 Month': [moment().subtract(3, 'months'), moment().endOf('month')],
				   'Last 6 Month': [moment().subtract(6, 'months'), moment().endOf('month')],
				   'Last 1 Year': [moment().subtract(12, 'months'), moment().endOf('month')]
				}
			}, cb);
		$('.date-set').click(function(){
			$('.datepikerdemo').trigger('click')
		});
		var tableToExcel = (function() {
			var uri = 'data:application/vnd.ms-excel;base64,'
			, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
			, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
			, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
			return function(table, name) {
				if (!table.nodeType) table = document.getElementById(table)
					var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
				window.location.href = uri + base64(format(template, ctx))
			}
		})()
	</script>
</body>
</html>
