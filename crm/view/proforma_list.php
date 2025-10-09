<?php 
session_start();
include('../include/urlfile.php');       

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_PROFORMA_INVOICE_CREATE,
	FINANCE_PROFORMA_INVOICE_LIST
]);

if(!in_array(FINANCE_PROFORMA_INVOICE_LIST,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Proforma Invoice List";

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PROFORMA LIST</title>
	<?php include_once('../../include/include_css_file.php');?>
	<style>
		.icons{
			width: 14.5%;
			float: left;
			margin: 30px 7px 25px;
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
			color:white

		}

		/* .icon1 fa{

		} */
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
			width: 120px;
			height:100px;
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
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3 style="float:left;"> <?=$mode .' '.$form?></h3>
								<?php//include_once("../include/head_menu.php") ?>
								<br/>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li ><a href="<?=ROOT.CRM_ROOT.'proforma_list'?>">Proforma Invoice List</a></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<div class="row">		
					<!--state overview start-->
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<div class='col-md-5'>
										<div class="form-group">
											<label class="control-label col-md-4">Choose Date</label>
											<div class="col-md-7">
												<div class="input-group date form_datetime-component">
													<?
									  //$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
													?>
													<input type="hidden" id="from_date"  value="<?=$start?>">
													<input type="hidden" id="to_date"  value="<?=$end?>">
													<input type="text" id="rep_date"  onChange="load_datatable();" class="form-control datepikerdemo" value="">
													<span class="input-group-btn">
														<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
													</span>
												</div>
											</div>
										</div>
									</div>	
									<?php if(in_array(FINANCE_PROFORMA_INVOICE_CREATE,$bulkAccessArray)){	?>
										
										<span class="tools pull-right respadr_15">
											<a href="javascript:;">
												<button onClick="exportCsv()" class="btn btn-success btn-flat" >Export Excel</button>
											</a>
											<a href="<?=ROOT.CRM_ROOT.'proforma'?>" ><button class="btn btn-success btn-flat" >Create Proforma</button></a>					
										</span>
									<?php } ?>
									<div class="col-md-12"	style="height:20px;" ></div>
						<!--<div class="col-md-5">
								<div class="col-md-4" style="text-align:left;">Select Type </div>
								<div class="col-md-7" >
								<select class="form-control" name="type_id" id="type_id" onChange="reload_data();">
									<=getlistinvoicetype($dbcon,'');?>	
								</select>
								</div>
							</div>-->
						</header>	
						<div class="panel-body">
							<div class="adv-table">
								<table  class="display table table-bordered table-striped" id="dynamic-table">
									<thead>
										<tr>
											<th>Invoice Type</th>
											<th>Invoice No</th>
											<th>Invoice Date</th>
											<th>Company Name</th>
											<th>City </th>
											<th>Grand Total</th>
											<th>Approval Status</th>
											<th>User Name</th>
											<th class="hidden-phone">Action</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
									<tfoot>	
										<tr>
											<th colspan="5" style="text-align:right">Total:</th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
									</tfoot>			 
								</table>
								<style>
									@media screen and (max-width:992px){
										#dynamic-table td:before{
											color:red
										}

										#dynamic-table td:nth-of-type(1):before { content: "Invoice Type :"; }
										#dynamic-table td:nth-of-type(2):before { content: "Invoice No :"; }
										#dynamic-table td:nth-of-type(3):before { content: "Invoice Date :"; }
										#dynamic-table td:nth-of-type(4):before { content: "Customer Name :"; }
										#dynamic-table td:nth-of-type(5):before { content: "City :"; }
										#dynamic-table td:nth-of-type(6):before { content: "Grand Total :"; }
										#dynamic-table td:nth-of-type(7):before { content: "Action :"; }
									}
								</style>
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
	<?php include_once('../../include/footer.php');?>
	<!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/preview_ord_po_aprv_hist.php'); ?>
<?php include_once('../../include/include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/proforma.js"></script>
<!--<script src="js/count.js"></script>-->
<script>
	$(document).ready(function() {
		Loading(true);

		Unloading();
	});
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