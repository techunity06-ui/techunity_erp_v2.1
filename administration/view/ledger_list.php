<?php
session_start();
include('../include/urlfile.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ADMINISTRATOR_LEDGER_READ,
	ADMINISTRATOR_LEDGER_ADD
]);

if (!in_array(ADMINISTRATOR_LEDGER_READ, $bulkAccessArray)) {
	header("Location: " . DOMAIN . "permission_access");
}

$form = "Ledger";
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = $infopage['filename'];
$branch_id = $_SESSION['branch_id'];
$countryid = '101';
$stateid = '1';
$cityid = '1';
$end = date("d-m-Y");

$getspecialConfiguration = getspecialConfiguration($dbcon);
$companyConfiguration=getCompanyConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>LEDGER LIST</title>
	<?php include_once($include . 'include_css_file.php'); ?>
	<style>
		tbody>tr>:nth-child(2) {
			text-align: center;
		}

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
								<h3><?= $form ?> List</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . 'masters_list' ?>"> Masters List</a></li>
									<li><a href="<?= ROOT . ADMINISTRATION_ROOT . 'ledger_list' ?>"><?= $form ?> list</a></li>
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
								<span class="tools pull-right">
									<?php if (in_array(ADMINISTRATOR_LEDGER_ADD, $bulkAccessArray)) { ?>
										<a href="<?= ROOT . ADMINISTRATION_ROOT . 'ledger_create' ?>"><button class="btn btn-success btn-flat">Add <?= $form ?></button></a>
									<?php } ?>
									<a href="<?= ROOT . ADMINISTRATION_ROOT . 'export_ledger_data' ?>"><button class="btn btn-info btn-flat">Export <?= $form ?></button></a>
								</span>
							</header>
							<div class="col-md-5">
							<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
								<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true, 'load_ledger_datatable()'); ?>
							<?php } ?>
							</div>
							<div class="col-md-5">
								<div class="form-group">
									<label class="col-md-1 control-label">Group</label>
									<div class="col-md-8">
										<select class="select2" name="gr_id" id="gr_id" onChange="load_ledger_datatable();">
											<?= get_all_group($dbcon, $id, $where = '', $primary); ?>
											<?php//=getcust($dbcon,$cust_id);
											?>
										</select>
									</div>
								</div>
							</div>
							<div class="panel-body">
								<div class="adv-table" id="adv-table">
									<table class="display table table-bordered table-striped" id="ledger-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<?php if ($getspecialConfiguration['adk_permission'] == 0) { ?>
													<th>Image</th>
												<?php } ?>
												<th>Ledger Code</th>
												<th>Ledger Name</th>
												<th>Group</th>
												<th>City</th>
												<th>Status</th>
												<!-- <th>Upload Documents</th> -->
												<th>Action</th>
												<th>Change Status</th>
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

		include_once($include . 'allocate_sold_product.php');
		include_once($include . 'footer.php');
		?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include . 'include_js_file.php'); ?>
	<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/ledger.js?<?= time() ?>"></script>

	<!--<script src="js/count.js"></script>-->
	<script>
		$(".select2").select2({
			width: '100%'
		});


		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
	</script>
</body>

</html>