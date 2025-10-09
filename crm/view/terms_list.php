<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Terms & Condition";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']='crm/'.$infopage['filename'];
$countryid='101';
$stateid='1';
$cityid='1';
$end = date("d-m-Y");
$branch_id = $_SESSION['branch_id'];

	//check permission for terms and condition
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CUSTOMER_TERMS_CONDITION_SLUG_READ,
	CUSTOMER_TERMS_CONDITION_SLUG_CREATE
]);

if(!in_array(CUSTOMER_TERMS_CONDITION_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>TERMS LIST</title>
	<?php include_once($include.'include_css_file.php');?>
	<style>

	@media (min-width: 1200px){
		#custom_sold_modal {
			width: 1150px;
		}
	}
</style>
</head>
<body>
	<section id="container" class="sidebar-closed">
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
							<header class="panel-heading">
								<div class="col-md-12" style="height:20px;" ></div>
								<div class="col-md-12">
									<div class="col-md-6">
										<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_terms_mst_datatable()','4','6'); ?>
									</div>
									<div class="col-md-6">
										<span class="tools pull-right">
											<?php if(in_array(CUSTOMER_TERMS_CONDITION_SLUG_CREATE,$bulkAccessArray)){ ?> 
												<a href="<?=ROOT.CRM_ROOT.'terms_add'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
											<?php } ?>
										</span>
									</div>
								</div> 
								
							</header>	
							<div class="panel-body">
								<div class="adv-table" id="adv-table">
									<table  class="display table table-bordered table-striped" id="dynamic-table">
										<thead>
											<tr>
												<th>Sr. NO.</th>
												<th>Name</th>
												<th>Detail</th>
												<th>For</th>
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
				<input type="hidden" name="custno" id="custno" value="<?=$end?>">	
				<!--state overview end-->
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->
		<?php 

		include_once($include.'footer.php');
		?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.CRM_ROOT?>js/app/terms_mst.js?<?=time()?>"></script>


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
