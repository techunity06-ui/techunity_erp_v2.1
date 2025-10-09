	<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	
	$form="Salesorder Dispatch Report";
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

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    WORKORDER_SO_DETAIL_REPORT_SLUG_VIEW,WORKORDER_SO_DISPATCH_REPORT_SLUG_VIEW,SALESORDER_INVOICE_REPORT_SLUG_VIEW
	]);
	
	if(!in_array(WORKORDER_SO_DISPATCH_REPORT_SLUG_VIEW,$bulkAccessArray)){
	    header("Location: ".DOMAIN."permission_access");
	}

	
	$qry = "SELECT sales_order_id,sales_order_no FROM tbl_sales_order WHERE sales_order_status = 0 AND company_id = " . $_SESSION['company_id'];

	$result = $dbcon->query($qry);

	$so_no = "<option value=''> Select Salesorder No </option>";
	while($row = brp_mysqli_fetch_assoc($result)){
		$so_no .= "<option value='".$row['sales_order_id']."'> ". $row['sales_order_no'] ."</option>";
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?=$form?></title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?> List</h3><br>
									<?php include_once('../include/reporthead_menu.php');?>
								</header>
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.PRODUCTION_ROOT.'production_report_list'?>"><i class="fa fa-home"></i> Production Report</a></li>
									  <li class="active"><?=$form?></li>
									</ul>
								</div>
							</section>
						</div>
			  		</div>
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading respadlr0">
								<div class='col-lg-4 col-md-6 col-xs-12'>
									<div class="form-group">
										<label class="control-label col-lg-4 col-md-4 col-xs-4 respad-l0">Choose Date</label>
										<div class=" col-lg-8 col-md-8 col-xs-8 respad-r0">
											<div class="input-group date form_datetime-component">
												<input type="hidden" id="from_date"  value="<?=$start?>">
												<input type="hidden" id="to_date"  value="<?=$end?>">
												<input type="text" id="rep_date"  onChange="generate_report();" class="form-control datepikerdemo" value="">
												<span class="input-group-btn">
													<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
												</span>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-4">
							<div class="form-group">
                  <label class="control-label col-md-4 text-right" >Salesorder No : </label>
                  <div class="col-md-8">
                  	 <select class="select2" id="so_id" onChange="generate_report();"> <?=$so_no?></select>
									</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
                  <label class="control-label col-md-4 text-right" >Customer Name : </label>
                  <div class="col-md-8">
                  	<select class="select2" id="cust_id" onChange="generate_report();"><?=getcust($dbcon);?> </select>
									</div>
							</div>
						</div>
								<span class="tools pull-right respadr_15 mtop20">
									<a href="javascript:;" onClick="tableToExcel('dynamic-table', 'Workorder')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>	
								</span>
								<div class="col-md-12"	style="height:10px;" ></div>
							</header>	
							<div class="panel-body">
								<div class="adv-table">
									<table  class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
										<tr>
											<th>Sr No.</th> 
													<th>Salesorder No</th>
													<th>Salesorder Date</th>
													<th>Customer Name</th>
													<th>PO NO.</th> 
													<th>PO Date</th>
													<th>Product Name</th>
													<th>Salesorder QTY</th>
													<th>Invoice No</th>
													<th>Invoice Date</th>
													<th>Invoice QTY</th>
										</tr>
										</thead>
										<tbody></tbody>				 
									</table>
								</div>
							</div>
						</section>
					</div>
				</section>
			</section>

			
		<?php include_once($include.'footer.php');?>
		</section>
<?php include_once($include.'include_js_file.php');?>  
	<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/workorder_so_dispatch_report.js?<?=time()?>"></script>
	<script src="<?=ROOT?>js/advanced-form-components.js"></script>
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
