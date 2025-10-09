<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Party";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$countryid='101';
	$stateid='1';
	$cityid='1';
	$end = date("d-m-Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>

</head>
<body>
<section id="container" class="sidebar-closed">
<?php include_once('../include/include_top_menu.php');?>
<!--sidebar start-->
<?php include_once('../include/left_menu.php');?>
<!--sidebar end-->
<!--main content start-->

<section id="main-content">
	
	<section class="wrapper">
		
		<div class="row">
			<div class="col-lg-12">
				<!--breadcrumbs start -->
				<section class="panel">
					<header class="panel-heading">
						<h3><?=$form?> List</h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li><a href="<?=ROOT.'consignee_list'?>"><?=$form?> list</a></li>
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
						<!--<span class="tools pull-right">
							<a href="<?=ROOT.'import_customerdetail'?>"><button class="btn btn-primary btn-flat" >Import Customer</button></a>
						</span>-->
						<?php if($_SESSION['user_type'] == 2){?>	
						<span class="tools pull-right">		
							<a href="javascript:;" onClick="tableToExcel('consignee-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>
							<!--<a href="<?=ROOT.'customer'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>-->
						</span>
						<?php }?>
						<span class="tools pull-right"></span>
						<div class="col-md-12" style="height:20px;"></div>
						<div class="col-md-6">
							<div class="col-md-4" style="text-align:left;">Choose Customer</div>
							<div class="col-md-7">
								<select class="select2" name="filter_cust_id" id="filter_cust_id" onChange="all_load_consignee_datatable();">
									<?=getcust($dbcon,'');?>	
								</select>
							</div>
						</div>
					</header>	
					<div class="panel-body">
						<div class="adv-table" id="adv-table">
							<table class="display table table-bordered table-striped" id="consignee-table">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>Company Name</th>
										<th>Branch Name</th>
										<th>City</th>
										<th>Country</th>
										<!--<th>State</th>-->
										<th>Mobile</th>
										<th>Email</th>
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
		<input type="hidden" name="custno" id="custno" value="<?=$end?>">
		<!--state overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php 
	include_once('../include/add_contact_person.php');
	include_once('../include/view_consignee.php');
	include_once('../include/add_consignee.php');
	include_once('../include/add_city.php');
	include_once('../include/add_state.php');
	include_once('../include/footer.php');
?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="js/app/customer.js?v1.1"></script>
<script src="<?=ROOT?>js/app/state_mst.js"></script>
<script src="<?=ROOT?>js/app/city_mst.js"></script>
<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});	
load_state(<?=$countryid?>,'stateid',<?=$stateid?>);
load_city(<?=$stateid?>,'cityid',<?=$cityid?>);	
all_load_consignee_datatable();

var tableToExcel = (function() {
	var uri = 'data:application/vnd.ms-excel;base64,'
	, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
	, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
	, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
	return function(table, name) {
		if (!table.nodeType) table = document.getElementById(table)
		var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
		
		//window.location.href = uri + base64(format(template, ctx))
		var custid= $('#custno').val();
	var link = document.createElement("a");
    link.download = "Consignee-list-#"+custid + ".xls";
    link.href = uri + base64(format(template, ctx));
    link.click();
	}
})()
</script>
</body>
</html>