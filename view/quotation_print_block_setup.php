<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
//$_SESSION['token'] = $token;
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
$form="Quotation Print Block Formate Setup";
	//check permission for source mst
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_PRINT_BLOCK_SETUP_LIST,
	QUOTATION_PRINT_BLOCK_SETUP_CREATE
]);

if(!in_array(QUOTATION_PRINT_BLOCK_SETUP_LIST,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>QUOTATION PRINT BLOCK SETUP</title>
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
					<div class="col-sm-6">
						<?php if(in_array(QUOTATION_PRINT_BLOCK_SETUP_CREATE,$bulkAccessArray)){ ?>
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form role="form" id="quotation_print_block_setup_add" action="javascript:;" method="post" name="quotation_print_block_setup_add">
										<div class="col-md-6 form-group">
											<label>Quotation Print Block Formate</label>
											<select name="quotation_print_block_id" id="quotation_print_block_id" class="select2" required onchange="show_data()">
												<option value="">Select Formate</option>
												<?php $res="";
												$chkQuery="SELECT quotation_print_block_id, block_name FROM tbl_quotation_print_block WHERE status=0 AND company_id =".$_SESSION['company_id'];
												$re_query=$dbcon->query($chkQuery);
												while($rels=mysqli_fetch_assoc($re_query)){
													$res .= '<option value="'.$rels['quotation_print_block_id'].'">'.$rels['block_name'].'</option>';
												} 
												echo $res; ?>
											</select>
										</div>
										<div class="col-md-6 form-group">
											<label>Priority</label>
											<input class="form-control" type='text' name='priority' id='priority' placeholder="Priority" value='' onkeyup="show_data()"/>
										</div>
										<div class="col-md-12 form-group">
											<button type="submit" class="btn btn-success">Submit</button>
										</div>
									</form>
								</div>
							</section>
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
									<table class="display table table-bordered table-striped" id="quotation_print_block_setup-datatable">
										<thead>
											<tr>
												<th>Sr. No.</th>
												<th>Formate Name</th>
												<th>Priority</th>
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
					<div class="col-sm-6">
						<section class="panel">
							<div class="panel-body">
								<div id="show_print_formate"></div>
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
	<div class="modal colored-header info" id="ModalEditquotation_print_block_setup" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
					<h3>Edit <?=$form?></h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditquotation_print_block_setup" role="form" method="post" novalidate> 
						<div class="form-group">
							<label>Quotation Print Block Formate</label>
							<select name="e_quotation_print_block_id" id="e_quotation_print_block_id" class="select2" required>
								<option value="">Select Formate</option>
								<?php $res="";
								$chkQuery="SELECT quotation_print_block_id, block_name FROM tbl_quotation_print_block WHERE status=0 AND company_id =".$_SESSION['company_id'];
								$re_query=$dbcon->query($chkQuery);
								while($rels=mysqli_fetch_assoc($re_query)){
									$res .= '<option value="'.$rels['quotation_print_block_id'].'">'.$rels['block_name'].'</option>';
								} 
								echo $res; ?>
							</select>
						</div>
						<div class="form-group">
							<label for="e_priority">Priority</label>
							<input class="form-control" type='text' name='e_priority' id='e_priority' value='' />
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
	<script src="<?=ROOT?>js/app/quotation_print_block_setup.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
			/*CKEDITOR.replace( 'block_formate', {
				enterMode: CKEDITOR.ENTER_BR
			});
			CKEDITOR.replace( 'e_block_formate', {
				enterMode: CKEDITOR.ENTER_BR
			});*/
		</script>
	</body>
	</html>