<?php 
session_start();
include('../include/urlfile.php');
$form="Annexure";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']='crm/'.$infopage['filename'];
$branch_id = $_SESSION['branch_id'];

if(empty($_SESSION['start'])) {
	$start=date('1-m-Y');
	$end=date("d-m-Y");
} else {
	$start=$_SESSION['start'];
	$end=$_SESSION['end'];
}
	//check permission for annexure list
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CUSTOMER_ANNEXURE_SLUG_READ,
	CUSTOMER_ANNEXURE_SLUG_CREATE
]);

if(!in_array(CUSTOMER_ANNEXURE_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>ANNEXURE LIST</title>
	<?php include_once($include.'include_css_file.php');?>
</head>
<body>
	<section id="container">
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
								<h3> <?=$form?> List</h3>
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
				
				<div class="row">		
					<!--state overview start-->
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<div class="col-md-12" style="height:20px;" ></div>
									<div class="col-md-12">
										<div class="col-md-6">
											<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_annexure_datatable()','4','6'); ?>
										</div>
										<div class="col-md-6">
											<span class="tools pull-right">
												<?php if(in_array(CUSTOMER_ANNEXURE_SLUG_CREATE,$bulkAccessArray)){ ?>
													<a href="<?=ROOT.CRM_ROOT.'annexure_detail'?>"><button class="btn btn-success btn-flat">Add <?=$form?></button></a>
												<?php } ?>
											</span>
										</div>
									</div>
								</header>	
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="annexure-datatable">
											<thead>
												<tr>
													<th>Sr. No.</th>
													<th>Annexure Name</th>
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
		</script>
	</body>
	</html>
