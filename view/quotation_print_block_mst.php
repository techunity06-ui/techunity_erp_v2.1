<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
//$_SESSION['token'] = $token;
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
$form="Quotation Print Block";
	//check permission for source mst
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_PRINT_BLOCK_MST_LIST,
	QUOTATION_PRINT_BLOCK_MST_CREATE
]);

if(!in_array(QUOTATION_PRINT_BLOCK_MST_LIST,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>QUOTATION PRINT BLOCK</title>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" >
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
								<h3>New <?=$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<!-- <li><a href="<?=ROOT.CRM_ROOT.'crm_master'?>">CRM Masters</a></li> -->
									<li class="active"><?=$form?> List</li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--unit overview start-->
				<div class="row">
					<?php if(in_array(QUOTATION_PRINT_BLOCK_MST_CREATE,$bulkAccessArray)){ ?>
						<div class="col-sm-6">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form role="form" id="quotation_print_block_mst_add" action="javascript:;" method="post" name="quotation_print_block_mst_add">
										<div class="col-md-12 form-group">
											<label>Block Name</label>
											<input class="form-control" type='text' name='block_name' id='block_name' placeholder="Block Name" value='' />
										</div>
										<div class="col-md-12 form-group">
											<label>Block Formate Content</label>
											<textarea id="block_formate" name="block_formate" class="form-control"></textarea>
										</div>
										<div class="col-md-12 form-group">
											<label>Block Type</label>
											<select class="select2" name="block_type" id="block_type" required>
												<option value="">Select Block type</option>
												<option value="0">Single</option>
												<option value="1">Product Specification</option>
												<option value="2">Terms</option>
											</select>
										</div>
										<div class="col-md-12 form-group">
											<button type="submit" class="btn btn-success">Submit</button>
										</div>
									</form>
								</div>
							</section>
						</div>
					<?php } ?>
					<?php if(in_array(QUOTATION_PRINT_BLOCK_MST_CREATE,$bulkAccessArray)){ ?>	
						<div class="col-sm-6">
						<?php }else{ ?>	
							<div class="col-sm-12">
							<?php } ?>	
							<section class="panel">
								<header class="panel-heading">
									<?=$form?> List
									<span class="tools pull-right">
										<a href="javascript:;" class="fa fa-chevron-down"></a>
									</span>
								</header>
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="quotation_print_block_mst-datatable">
											<thead>
												<tr>
													<th>Sr. No.</th>
													<th>Block Name</th>
													<!-- <th>Block Formate Content</th> -->
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

					<!--unit overview end-->
				</section>
			</section>
			<!--main content end-->
			<!--footer start-->
			<?php include_once('../include/footer.php');?>
			<!--footer end-->
		</section>
		<!-- Modal -->
		<div class="modal colored-header info" id="ModalEditquotation_print_block_mst" role="dialog" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog custom-width modal-lg">
				<div class="modal-content modal-lg">
					<div class="modal-header">
						<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
						<h3>Edit <?=$form?></h3>
					</div>
					<form id="FormEditquotation_print_block_mst" role="form" method="post" novalidate>
						<div class="modal-body form">
							<div class="col-md-6 form-group">
								<label for="e_block_name">Block Name</label>
								<input class="form-control" type='text' name='e_block_name' id='e_block_name' value='' />
							</div>	
							<div class="col-md-6 form-group">
								<label>Block Type</label>
								<select class="select2" name="e_block_type" id="e_block_type">
									<option value="">Select Block type</option>
									<option value="0">Single</option>
									<option value="1">Product</option>
									<option value="2">Product Specification</option>
									<option value="3">Terms</option>
								</select>
							</div>
							<div class="col-md-12 form-group">
								<label for="e_block_formate">Block Formate Content</label>
								<textarea id="e_block_formate" name="e_block_formate" class="form-control"></textarea>
							</div>
						</div>
						<div class="modal-footer">
							<input type="hidden" name="edit_id" id="edit_id" value="" />
							<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
							<button class="btn btn-info btn-success" type="submit">Update</button>
						</div>
					</form>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<!-- js placed at the end of the document so the pages load faster -->
		<?php include_once('../include/include_js_file.php');?>
		<script src="<?=ROOT?>js/app/quotation_print_block_mst.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			// CKEDITOR.replace( 'block_formate', {
			// 	enterMode: CKEDITOR.ENTER_BR
			// });
			// CKEDITOR.replace( 'e_block_formate', {
			// 	enterMode: CKEDITOR.ENTER_BR
			// });
			loadEditor('2','block_formate');
			loadEditor('2','e_block_formate');
		</script>
	</body>
	</html>