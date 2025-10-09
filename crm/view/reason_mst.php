<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';
$infopage = pathinfo( __FILE__ );
$_SESSION['page']= 'crm/'.$infopage['filename']; 
$form="Reason";

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	REASON_LOST_INQUIRY_SLUG_READ,
	REASON_LOST_INQUIRY_SLUG_CREATE
]);

if(!in_array(REASON_LOST_INQUIRY_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>REASON</title>
	<?php include_once($include.'include_css_file.php');?>
</head>
<body>
	<section id="container" >
		<?php include_once($include.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php');?>
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
									<li><a href="<?=ROOT.CRM_ROOT.'crm_master'?>">CRM Masters</a></li>
									<li class="active"><?=$form?> List</li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--unit overview start-->
				<div class="row">
					<div class="col-sm-3">
						<section class="panel">
							<header class="panel-heading">
								New <?=$form?>
							</header>	
							<div class="panel-body">
								<form role="form" id="reason_mst_add" action="javascript:;" method="post" name="reason_mst_add">
									<div class="form-group">
										<label>Reason</label>
										<input class="form-control" type='text' name='reason' id='reason' placeholder="Reason" value='' />
									</div>
									<button type="submit" class="btn btn-success">Submit</button>
								</form>
							</div>
						</section>
					</div>
					<div class="col-sm-9">
						<section class="panel">
							<header class="panel-heading">
								<?=$form?> List
								<span class="tools pull-right">
									<a href="javascript:;" class="fa fa-chevron-down"></a>
								</span>
							</header>
							<div class="panel-body">
								<div class="adv-table">
									<table class="display table table-bordered table-striped" id="source-mst-datatable">
										<thead>
											<tr>
												<th>Sr. No.</th>
												<th>Reason</th>
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
		<?php include_once('../../include/footer.php');?>
		<!--footer end-->
	</section>
	<!-- Modal -->
	<div class="modal colored-header info" id="ModalEditReasonMst" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
					<h3>Edit <?=$form?></h3>
				</div>
				<div class="modal-body form">
					<form id="FormEditReasonMst" role="form" method="post" novalidate>
						
						<div class="form-group">
							<label for="e_reason">Reason</label>
							<input class="form-control" type='text' name='e_reason' id='e_reason' value='' />
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
	<?php include_once('../../include/include_js_file.php');?>   
	<script src="<?=ROOT.CRM_ROOT?>js/app/reason_mst.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
	</script>
</body>
</html>