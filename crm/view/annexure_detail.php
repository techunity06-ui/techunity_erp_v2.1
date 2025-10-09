<?php 
session_start();
include('../include/urlfile.php');
$infopage = pathinfo( __FILE__ );
$_SESSION['page']='crm/'.$infopage['filename']; 
$form="Annexure";
	// check permission for annexure
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CUSTOMER_ANNEXURE_SLUG_CREATE,
	CUSTOMER_ANNEXURE_SLUG_UPDATE
]);
$branch_id = $_SESSION['branch_id'];

if(strpos($_SERVER[REQUEST_URI], "crm/annexure_detail_edit")==true) {
	$mode="Edit";
	if(!in_array(CUSTOMER_ANNEXURE_SLUG_UPDATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$an_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select anx.* from tbl_annexure as anx
	where anx.an_id=$an_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
} else {
	$mode="Add";
	if(!in_array(CUSTOMER_ANNEXURE_SLUG_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>ANNEXURE</title>
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
					<div class="col-md-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3>New <?=$form; ?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.CRM_ROOT.'crm_master'?>">CRM Masters</a></li>
									<li class="active"><a href="<?=ROOT.CRM_ROOT.'annexure_detail_list'?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--unit overview start-->
				<div class="row">
					<div class="col-md-12">
						<section class="panel">
							<header class="panel-heading">
								New <?=$form?>
							</header>	
							<div class="panel-body">
								<form role="form" id="annexure_add" action="javascript:;" method="post" name="annexure_add">
									<div class="col-md-12">
										<div class="col-md-6">
											<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8','',''); ?>
										</div>
									</div>
									<div class="col-md-12" style="padding-top: 12px;">
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label">Annexure Name*</label>
												<div class="col-md-8">
													<input class="form-control" type="text" name="a_name" id="a_name" placeholder="Annexure Name" value="<?=$rel['an_name']?>" />
												</div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label>Detail</label>
										<textarea class="form-control" name="a_detail" id="a_detail"><?=$rel['an_detail']?></textarea>
									</div>
									
									
									<input type='hidden' name='eid' id='eid' value='<?=$rel['an_id']?>' />	
									<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />	
									
									<div class="clearfix"></div>
									<div class="col-md-12 text-center">
										<button type="submit" id="submit_btn" class="btn btn-success">Submit</button>
										<a href="<?=ROOT.CRM_ROOT.'annexure_detail_list'?>" type="button" class="btn btn-danger">Cancel</a>	
									</div>
								</form>
								
							</div>
						</section>
					</div>
					
				</div>

				<!--unit overview end-->
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once($include.'footer.php');?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.CRM_ROOT?>js/app/annexure.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});
		CKEDITOR.replace( 'a_detail', {
			enterMode: CKEDITOR.ENTER_BR
		});
	</script>
</body>
</html>
