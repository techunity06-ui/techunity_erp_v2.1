<?php
session_start();
include('../include/urlfile.php');
$form = "Item";
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = $infopage['filename'];
$countryid = '101';
$stateid = '1';
$cityid = '1';
$end = date("d-m-Y");
$branch_id = $_SESSION['branch_id'];
//check permission for annexure list
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ADMINISTRATOR_PRODUCT_LIST,
	ADMINISTRATOR_PRODUCT_CREATE,
	ADMINISTRATOR_PRODUCT_EXCEL,
	ADMINISTRATOR_PRODUCT_CLONE
]);

if (!in_array(ADMINISTRATOR_PRODUCT_LIST, $bulkAccessArray)) {
	header("Location: " . DOMAIN . "permission_access");
}

$getspecialConfiguration = getspecialConfiguration($dbcon);
$companyConfiguration=getCompanyConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>ITEM LIST</title>
	<?php include_once($include . 'include_css_file.php'); ?>
	<style>
		@media (min-width: 1200px) {
			#custom_sold_modal {
				width: 1150px;
			}
		}
	</style>
</head>

<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include . 'include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once($include . 'left_menu.php'); ?>
		<!--sidebar end-->
		<!--main content start-->

		<section id="main-content">

			<section class="wrapper">

				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3>
									<?= $form ?> List
								</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . 'masters_list' ?>"> Masters List</a></li>
									<li class="active">
										<?= $form ?> list
									</li>
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
								<div class="col-md-12" style="margin-top:10px;">
									<div class="col-md-4">
										<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
										<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true, 'load_pro_tbl()', '4', '6'); ?>
										<?php } ?>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="control-label col-md-4">Product Type</label>
											<div class="col-md-7">
												<select class="select2" id="fil_product_type" name="fil_product_type"
													onchange="load_pro_tbl();">
													<!--START JAYESH 15-07-2021 purpose : dynamic data from database -->

													<?php echo get_product_type_company($dbcon, '', 'ALL'); ?>
													<!--<option value="">--ALL--</option>
											<option value="0">FINISH PRODUCT</option>
											<option value="1">ASSEMBLY PRODUCT</option>
											<option value="2">SUB ASSEMBLY</option>
											<option value="3">RAW MATERIAL</option>
											<option value="4">FINISH PART</option>
											<option value="5">BOI</option>
											<option value="6">CAPITAL GOODS</option>
											<option value="7">CONSUMABLE</option>
											<option value="8">Service</option>-->
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<span class="tools pull-right">
											<?php //if(in_array(ADMINISTRATOR_PRODUCT_EXCEL,$bulkAccessArray)){ 
											?>
											<!-- <a href="javascript:;" onClick="tableToExcel('product-table', 'Instalment Collection')" ><button class="btn btn-info btn-flat" >Export Items</button></a> -->
											<a onclick="open_model()"><button class="btn btn-info btn-flat"
													data-tooltip="Export Items" accesskey="n"><i class="fa fa-download"
														aria-hidden="true"></i> Export Items</button></a>
											<!-- <a href="<?= ROOT . 'generate_export_product_csv' ?>" ><button class="btn btn-info btn-flat" ><i class="fa fa-download" aria-hidden="true"></i> Export Items</button></a> -->
											<?php // }
											?>
											<?php if (in_array(ADMINISTRATOR_PRODUCT_CREATE, $bulkAccessArray)) { ?>
												<?php if ($getspecialConfiguration['interpower_permission'] == 1) { ?>
													<a href="<?= ROOT . ADMINISTRATION_ROOT . 'product_add_ip' ?>"><button
															class="btn btn-success btn-flat">Add
															<?= $form ?>
														</button></a>

												<?php } else { ?>
													<a href="<?= ROOT . ADMINISTRATION_ROOT . 'product_add' ?>"><button
															class="btn btn-success btn-flat">Add
															<?= $form ?>
														</button></a>
												<?php } ?>
											<?php } ?>
										</span>
									</div>
								</div>

							</header>
							<div class="panel-body">
								<div class="adv-table" id="adv-table">
									<table class="display table table-bordered table-striped" id="product-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Product Image</th>
												<th>Product Type</th>
												<?php if ($getspecialConfiguratio['interpower_permission'] == 1) { ?>
													<th>Product Description</th>
													<th>Old Part Code</th>
													<th>Part Code</th>
												<?php } else { ?>
													<th>Product Name</th>
													<th>Alias Name</th>
													<th>Product Code</th>
												<?php } ?>
												<th>Drawing Number</th>
												<th>Status</th>
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
				<input type="hidden" name="custno" id="custno" value="<?= $end ?>">
				<!--state overview end-->
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->
		<?php

		include_once($include . 'footer.php');
		?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include1 . 'product_excel.php'); ?>
	<?php include_once($include . 'include_js_file.php'); ?>
	<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/product_mst.js?<?= time() ?>"></script>

	<script>
		var tableToExcel = (function () {
			var uri = 'data:application/vnd.ms-excel;base64,'
				, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
				, base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
				, format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
			return function (table, name) {
				if (!table.nodeType) table = document.getElementById(table)
				var ctx = { worksheet: name || 'Worksheet', table: table.innerHTML }
				var custno = $('#custno').val();
				var link = document.createElement("a");
				var link = document.createElement("a");;
				link.download = "products-list-# " + custno + ".xls";
				link.href = uri + base64(format(template, ctx));
				link.click();
			}
		})()
		$(".select2").select2({
			width: '100%'
		});
	</script>


	<!--<script src="js/count.js"></script>-->
	<script>

		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
	</script>
</body>

</html>