<?php
session_start();
include('../include/urlfile.php');
$form = "HSN Masters";
$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = $infopage['filename'];
$branch_id = $_SESSION['branch_id'];
//check permission for process type add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ADMINISTRATOR_HSN_MASTER_READ,
	ADMINISTRATOR_HSN_MASTER_ADD
]);

if (!in_array(ADMINISTRATOR_HSN_MASTER_READ, $bulkAccessArray)) {
	header("Location: " . DOMAIN . "permission_access");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>HSN MASTER</title>
	<?php include_once($include . 'include_css_file.php'); ?>
</head>

<body>
	<section id="container">
		<?php include_once($include . 'include_top_menu.php'); ?>
		<?php include_once($include . 'left_menu.php'); ?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3>New </h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . 'masters_list' ?>"> Masters List</a></li>
									<li class="active"><?= $form ?> List</li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<?php include_once($include . 'country_unit_city.php'); ?>
				<div class="row">
					<?php if (in_array(ADMINISTRATOR_HSN_MASTER_ADD, $bulkAccessArray)) { ?>
						<div class="col-sm-3">
							<section class="panel">
								<header class="panel-heading">
									<?= $form ?>
								</header>
								<div class="panel-body">
									<form role="form" id="hsn_add" action="javascript:;" method="post" name="hsn_add">

										<div class="form-group">
											<label>HSN Code</label>
											<input class="form-control numbersOnly" type='text' name='hsn_code' id='hsn_code' value='' maxlength="10" />
										</div>

										<div class="form-group">
											<label>HSN Description</label>
											<input class="form-control" type='text' name='hsn_desc' id='hsn_desc' value='' />
										</div>

										<div class="form-group">
											<label>Select Tax Category</label>
											<select class="select2" name='sale_gst' id='sale_gst' value='' title="Select Sale Gst" required>
												<?= get_tax_category_new($dbcon, $rel['product_sale_gst']); ?>
											</select>
										</div>
										<!--
												<div class="form-group">
													<label>Sale GST</label>
													<select class="select2" name='sale_gst' id='sale_gst' value=''title="Select Sale Gst" required>
														<?= get_tax_percentage($dbcon, $rel['product_sale_gst']); ?>
													</select>
												</div>
												-->

										<input type='hidden' name='mode' id='mode' value='add' />
										<button type="submit" class="btn btn-info">Submit</button>
									</form>
								</div>
							</section>
						</div>
					<?php	} ?>
					<?php if (in_array(ADMINISTRATOR_HSN_MASTER_ADD, $bulkAccessArray)) { ?>
						<div class="col-sm-9">
						<?php } else { ?>
							<div class="col-sm-12">
							<?php } ?>
							<section class="panel">
								<header class="panel-heading">
									<?= $form ?>
									<span class="tools pull-right">
										<a href="javascript:;" class="fa fa-chevron-down"></a>

									</span>
								</header>
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="dynamic-table">
											<div class="col-md-12">
												<span class="tools pull-right">
													<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat">Export Excel</button></a>
												</span>
											</div>
											<thead>
												<tr>
													<th>Sr. NO.</th>
													<th>HSN Code</th>
													<th>HSN Description</th>
													<th>Tax Category</th>
													<th class="hidden-phone">Action</th>
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
		<?php include_once($include . 'footer.php'); ?>
	</section>
	<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit </h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditunit" role="form" method="post" novalidate>

						<div class="form-group">
							<label for="unitid">HSN Code</label>
							<input type="text" class="form-control" name="edit_hsn_code" id="edit_hsn_code" maxlength="10" />
						</div>

						<div class="form-group">
							<label for="unitid">HSN Description</label>
							<input type="text" class="form-control" name="edit_hsn_desc" id="edit_hsn_desc" />
						</div>

						<div class="form-group">
							<label for="unitid">Sale GST</label>
							<select class="select2" name='edit_sale_gst' id='edit_sale_gst' value='' title="Select Sale Gst" required>
								<?= get_tax_category_new($dbcon, $rel['product_sale_gst']); ?>
							</select>
						</div>

						<div class="modal-footer">
							<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
							<input type="hidden" name="edit_id" id="edit_id" value="" />
							<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
							<button class="btn btn-info btn-flat" type="submit">Update </button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php include_once($include . 'include_js_file.php'); ?>
	<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/hsn_master.js?<?= time() ?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
	</script>
</body>

</html>