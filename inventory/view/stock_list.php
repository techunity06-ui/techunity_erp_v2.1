<?php
session_start();
include('../include/urlfile.php');
$form = "Opening Stock";
if (empty($_SESSION['start'])) {
	$start = date('1-m-Y');
	$end = date("d-m-Y");
} else {
	$start = $_SESSION['start'];
	$end = $_SESSION['end'];
}
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	OPENING_STOCK_LIST_SLUG_VIEW, OPENING_STOCK_LIST_SLUG_CREATE
]);

if (!in_array(OPENING_STOCK_LIST_SLUG_VIEW, $bulkAccessArray)) {
	header("Location: " . DOMAIN . "permission_access");
}

$branch_id = $_SESSION['branch_id'];
$companyConfiguration = getCompanyConfiguration($dbcon);
//var_dump($_SESSION);
//echo $_SESSION['branch_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>STOCK LIST</title>
	<?php include_once($include . 'include_css_file.php'); ?>

</head>

<body>
	<section id="container">
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
								<h3><?= $form ?> List</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active"><?= $form ?> List</li>
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

								<div class="col-md-7 m-bot15">
									<div class="form-group">
										<label class="control-label col-md-3 text-right">FILTER</label>
										<div class="col-md-9">
											<div class="col-md-3">
												<label for="approve_status1" class="external-event label label-primary ui-draggable" style="position: relative;cursor:pointer;">All</label>
												<input id="approve_status1" name="approve_status" checked type="radio" onClick="reload_data();" class="" title="All" value="0,1,2">
											</div>
											<div class="col-md-3">
												<label for="approve_status3" class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Pending</label>
												<input id="approve_status3" name="approve_status" onClick="reload_data();" type="radio" class="" title="Pending" value="0" />
											</div>
											<div class="col-md-3">
												<label for="approve_status2" class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Approved</label>
												<input id="approve_status2" name="approve_status" onClick="reload_data();" type="radio" class="" title="Approved" value="1" />
											</div>
											<div class="col-md-3">
												<label for="approve_status2" class="external-event label label-danger ui-draggable" style="position: relative;cursor:pointer;">Rejected</label>
												<input id="approve_status2" name="approve_status" onClick="reload_data();" type="radio" class="" title="Rejected" value="2" />
											</div>

										</div>
									</div>
								</div>
								<div class="col-md-12">
									<div class="col-md-4 m-bot15">
										<div class="form-group">
											<label class="col-md-4 control-label text-right">Branch *</label>
											<div class="col-md-8 col-xs-11">
												<select name="branch_id" class="select2 branch_id" id="branch_id">
													<?= get_branch($dbcon, $branch_id); ?>
												</select>
											</div>
										</div>


									</div>
									<div class="col-md-4 m-bot15">
										<div class="form-group">
											<label class="col-md-4 control-label text-right">Location *</label>
											<div class="col-md-8 col-xs-11">
												<select class="select2 location_id" name="location_id" id="location_id" onchange="reload_data()">
													<?= get_last_node_godown_list($dbcon); ?>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-4 m-bot15">
										<div class="form-group">
											<label class="col-md-4 control-label text-right">Product Name *</label>
											<div class="col-md-8 col-xs-11">
												<!-- <select class="select2 selproduct" title="Select product" name="product_id" id="product_id"  onchange="reload_data()">
										<?= getproduct($dbcon, ''); ?> -->

												<input id="product_id" class="select2 selproduct" name="product_id" style="width:100%;" placeholder="Select product" onchange="reload_data();" value="" />
												</select>
											</div>
										</div>
									</div>

								</div>
								
								<span class="tools pull-right">
									<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat">Export Excel</button></a>
								</span>
								
								<span class="tools pull-right">
									<!-- <a href="javascript:;" onClick="show_import_stock_model()"><button class="btn btn-primary btn-flat" >Import Excel</button></a>
					<a href="javascript:;" onClick="tableToExcel('stock_list', 'Opening Stock')" ><button class="btn btn-info btn-flat" >Export Excel</button></a> -->

									<?php if (in_array(OPENING_STOCK_LIST_SLUG_CREATE, $bulkAccessArray)) {	?>
										<a href="<?= ROOT . INVENTORY_ROOT . 'stock_add' ?>"><button class="btn btn-success btn-flat">Create <?= $form ?></button></a>
									<?php } ?>
								</span>
								
								<div class="col-md-12" style="height:10px;"></div>

							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="stock_list">
										<thead>
											<tr>
												<th>#</th>
												<th>Branch</th>
												<th>Location</th>
												<th>Product</th>
												<?php if ($companyConfiguration['batch_wise_stock'] == '1') { ?>
													<th>Batch No</th>
												<?php } ?>
												<th>Opening Stock Qty</th>
												<th>Closing Stock Qty</th>
												<th>Base Rate</th>
												<th>Convert Rate</th>
												<th>Process Stock</th>
												<th>Status</th>
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
				<!--state overview end-->
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->

		<?php include_once($include . 'footer.php'); ?>
		<!--footer end-->
	</section>
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include1 . 'stock_approve_model.php'); ?>
	<?php include_once($include1 . 'stock_import_model.php'); ?>
	<?php include_once($include . 'include_js_file.php'); ?>
	<script src="<?= ROOT . INVENTORY_ROOT ?>js/app/stock.js?<?= time() ?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});

		var tableToExcel = (function() {
			var uri = 'data:application/vnd.ms-excel;base64,',
				template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head></head><body><table>{table}</table></body></html>',
				base64 = function(s) {
					return window.btoa(unescape(encodeURIComponent(s)))
				},
				format = function(s, c) {
					return s.replace(/{(\w+)}/g, function(m, p) {
						return c[p];
					})
				}
			return function(table, name) {
				if (!table.nodeType) table = document.getElementById(table)
				var ctx = {
					worksheet: name || 'Worksheet',
					table: table.innerHTML
				}
				window.location.href = uri + base64(format(template, ctx))
			}
		})()
	</script>
</body>

</html>
<script type="text/javascript">
	var alloted = "";
</script>