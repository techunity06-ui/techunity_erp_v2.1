<?php 
session_start();
include('../include/urlfile.php');
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Design Department BOM Assign";
	//$type=$dbcon->real_escape_string($_REQUEST['id']);

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
	//check permission for get sales order details
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_VIEW
]);
if(!in_array(DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_VIEW,$bulkAccessArray)) {
	header("Location: ".DOMAIN."permission_access");
}
$branch_id = $_SESSION['branch_id'];

$companyConfiguration=getCompanyConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>DESIGN DEPARTMENT SO</title>
	<?php include_once($include.'include_css_file.php');?>
</head>
<body>
	<section id="container">
		<?php include_once($include.'include_top_menu.php');?>
		<?php include_once($include.'left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<input type="hidden" class="form-control" name="st_type" id="st_type" value="<?=$type;?>" />

								<h3><?=$form?> List</h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active"><?=$form?> List</li>
								</ul>
							</div>
						</section>
					</div>
				</div>
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<div class="panel-body">
								<div class="col-md-12">
									<?php if($companyConfiguration['branch_wise_manage']==1){?>
										<div class="col-md-4">
											<?php echo getBranchBox($dbcon, $branch_id, $_SESSION['branch_id'], false, true, 'show_data()'); ?>	
										</div>	
									<?php }else{ ?>
													<input type="hidden" name="branch_id" id="branch_id" value="<?=$companyConfiguration['default_branch_id']?>" />
												<?php } ?>
									<div class="col-md-4">
										<button class="btn btn-success btn-flat" onclick="assign_standard_bom();">STANDARD BOM ASSIGN</button>
									</div>

								</div>
								<div class="adv-table">
									<table  class="display table table-bordered table-striped" id="dynamic-table1">
										<thead>
											<tr>
												<th class="nosort">  <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>
												<th>Sales Order No</th>
												<th>Sales Order Date</th>
												<th>Version Name</th>

												<th>Product Name</th>
												<th>Request Qty</th>
												<?php if($companyConfiguration['outside_jobwork']){ ?>
													<th>Jobwork Type</th>
												<?php } ?>
												<th>Priority</th>
												<th class="hidden-phone">Action</th>					  
											</tr>
										</thead>
										<tbody id="data_table"></tbody>
									</table>
								</div>
							</div>
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once($include.'footer.php');?>
		<?php include_once($include1.'assign_bom.php');?>
		<?php include_once($include1.'preview_so_trn_pro_description.php');?>

	</section>
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?php echo ROOT.PRODUCTION_ROOT; ?>js/app/design_department_get_sales_order_details.js"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});


	</script>
</body>
</html>
