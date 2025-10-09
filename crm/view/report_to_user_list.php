<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Report To User List";
$countryid='101';
$stateid='1';
$cityid='1';
$infopage = pathinfo( __FILE__ );
$_SESSION['page']='crm/'.$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
	//check paermission for customer add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	REPORT_TO_USER_READ,
	REPORT_TO_USER_APPROVE 
]);

if(!in_array(REPORT_TO_USER_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>REPORT TO USER LIST</title>
	<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
	<section id="container" >
		<?php include_once($incPath.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($incPath.'left_menu.php');?>
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
									<li><a href="<?=ROOT.CRM_ROOT.'crm_master'?>">CRM Masters</a></li>
									<li class="active"><?=$form?> list</li>
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
							<header class="panel-heading"></header>	
							<div class="panel-body">
								<div class="adv-table" id="adv-table">
									<table  class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr.</th>
												<th>User Type</th>
												<th>User Name</th>
												<th>E-mail</th>
												<th>Mobile</th>
												<th>User Status</th>
												<th class="hidden-phone"> Action</th>					  
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
		<?php 
		include_once($incPath.'footer.php');
		?>
		<!--footer end-->
	</section>
	<!-- Modal -->
	<div class="modal colored-header info" id="ModalEditReportuserMst" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
					<h3>Unlock User</h3>
				</div>
				<form id="FormEditReportuserMst" role="form" method="post" novalidate>
					<div class="modal-body form">
						<table class="table table-bordered table-stripped">
							<thead>
							<tr>
								<th>UserType name : <span id="usertype"></span></th>
								<th>Username : <span id="user_name"></span></th>
							</tr>
							<tr>
								<th>User Email : <span id="user_mail"></span></th>
								<th>User Phone : <span id="user_phone"></span></th>
							</tr>
							</thead>
						</table>
						<div class="form-group">
							<label for="user_locked_reason">Reasone for unlock</label>
							<textarea class="form-control" type='text' name='user_locked_reason' id='user_locked_reason'></textarea>
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
	<?php include_once($incPath.'include_js_file.php');?>   
	<script src="<?=ROOT.CRM_ROOT?>js/app/report_to_user.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});	

	</script>
</body>
</html>
