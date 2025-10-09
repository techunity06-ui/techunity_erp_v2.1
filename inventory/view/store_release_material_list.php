<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	$type=$dbcon->real_escape_string($_REQUEST['type']);
	
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
       PRODUCTION_STORE_LIST_SLUG_VIEW,PRODUCTION_STORE_LIST_SLUG_CREATE,PRODUCTION_STORE_LIST_SLUG_READ,PRODUCTION_STORE_LIST_SLUG_UPDATE,PRODUCTION_STORE_LIST_SLUG_DELETE,PRODUCTION_STORE_LIST_APPROVE,PRODUCTION_STORE_LIST_RETURN
]);

if(!in_array(PRODUCTION_STORE_LIST_APPROVE,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}

	$form="Product Store Release Material List";
		
	if(empty($_SESSION['start']))
	{
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else
	{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	
	$branch_id = $_SESSION['branch_id'];
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>STORE RELEASE MATERIAL LIST</title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?></h3>
								</header>
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									</ul>
								</div>
							</section>
						</div>
			  		</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<div class="panel-body">
						<div class="adv-table" id="adv-table">
							<table  class="display table table-bordered table-striped" id="dynamic_table_working">
								<thead>
									<tr>
										<th>#</th>
										<th>Product Name </th>
										<th>Issue NO</th>
										<th>Issue Date</th>
										<th>Work Order No</th>
										<th>Job Card No</th>
										<th>Batch No</th>
										<th>Release Qty</th>
										<th>User Name</th>
										<?php if($_SESSION['branch_id']==0){ ?>
											<th>Branch Name</th>
										<?php} ?>
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
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>

	 <?php include_once($include1.'store_release_material_modal.php'); ?>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/store_release_material_list.js?<?=time()?>"></script>
		
	</body>
</html>
