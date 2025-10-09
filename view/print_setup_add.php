<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include("../include/function_database_query.php");
$include = '../include/';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRINT_SETUP_SLUG_CREATE,
	PRINT_SETUP_SLUG_UPDATE
]);

$form="Print Setup Add";
$branch_id = $_SESSION['branch_id'];

$company_multicurrency = getCompanyConfiguration($dbcon);

if(strpos($_SERVER['REQUEST_URI'], "print_setup_edit")==false) {
	if(!in_array(PRINT_SETUP_SLUG_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";
	$status=0;
}
else {
	if(!in_array(PRINT_SETUP_SLUG_UPDATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}

	$mode="Edit";
	$print_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="SELECT * from print_setup_mst where id=$print_id";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

	$status=$rel['status'];
	$withoutlogostatus=$rel['with_out_logo'];
	$eid=$print_id;

	if(!$rel){
		header("Location: ".ROOT."print_setup_list");
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>PRINT SETUP</title>
	<?php include_once($include.'include_css_file.php');?>
	<style>
	.head_margin
	{
		padding:10px;
	}
	.form_class
	{

	}
	.back_head_color
	{
		background-color:#337AB7 !important;
		color:#ffffff !important;
	}
	.row_margin
	{
		margin-top:20px;
	}
	.margin_row
	{
		margin-top:20px;
	}

	.ledger_forms
	{
		display:none !important;
	}
	.xlg
	{
		width:1350px !important;
	}
</style>
</head>
<body>
	<section id="container" >
		<?php include_once($include.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<form class="form-horizontal" role="form" id="print_setup_add" action="javascript:;" method="post" name="print_setup_add" enctype="multipart/form-data">	
			<section id="main-content">
				<section class="wrapper">			
					<div class="row">
						<div class="col-lg-12">
							<!--breadcrumbs start -->
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.'print_setup_list'?>"><?=$form?> List</a></li>
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
									New <?=$form?>
								</header>
								<div class="panel-body ">
									<div class="row">
										<div class="col-md-12 margin_row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Print Type*</label>
													<div class="col-md-8">
														<select class="select2" name="print_type" id="print_type" required>
															<?=get_print_type($dbcon,$rel['print_type']);?>
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Print Name*</label>
													<div class="col-md-8">
														<input type="text" class="form-control" placeholder="Print Name" title="Print Name" name="print_name" id="print_name" value="<?=$rel['print_name']?>" required/>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Fa-Icon*</label>
													<div class="col-md-8">
														<input type="text" class="form-control" placeholder="Fa-Icon" title="Fa-Icon" name="fa_icon" id="fa_icon" value="<?=$rel['fa_icon']?>" required/>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Page Path*</label>
													<div class="col-md-8">
														<input type="text" class="form-control" placeholder="Page Path" title="Page Path" name="page_path" id="page_path" value="<?=$rel['page_path']?>" required/>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Icon Color*</label>
													<div class="col-md-8">
														<input type="color" class="form-control" name="icon_color" id="icon_color" value="<?=$rel['icon_color']?>" required/>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Priority*</label>
													<div class="col-md-8">
														<input type="text" class="form-control" placeholder="Priority" title="Priority" name="priority" id="priority" value="<?=$rel['priority']?>" required/>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Status*</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="status" id="status">
															<option value="0" <?=($status==0) ? "selected" : ""?>>Active</option>		
															<option value="1" <?=($status==1) ? "selected" : ""?>>Not Active</option>		
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">With Out Logo Runtime</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="with_out_logo" id="with_out_logo">
															<option value="0" <?=($withoutlogostatus==0) ? "selected" : ""?>>Not Active</option>		
															<option value="1" <?=($withoutlogostatus==1) ? "selected" : ""?>>Active</option>		
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12 col-md-offset-5 row_margin" >
											<input type='hidden' name='mode' id='mode' value='<?php if($mode=='Edit') { echo "edit"; } else { echo "add"; } ?>' />
											<input type='hidden' name='eid' id='eid' value='<?php if($mode=='Edit') { echo $eid; } else { echo "0"; } ?>' />
											<button type="submit" name="" id="btn_submit" class="btn btn-success">Submit</button>
											<a class="btn btn-danger" href="<?=ROOT.'print_setup_list'?>">Cancel</a>
										</div>
									</div>
								</div>
							</section>
						</div>
						<!--state overview end-->
					</section>
				</section>

			</form>
			<!--main content end-->
			<!--footer start-->
			<?php include_once($include.'footer.php');?>
			<!--footer end-->
		</section>

		<!-- js placed at the end of the document so the pages load faster -->
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/print_setup_list.js?<?=time()?>"></script>

		<script>
			$(".select2").select2({
				width: '100%',

			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});

		</script>
	</body>
	</html>