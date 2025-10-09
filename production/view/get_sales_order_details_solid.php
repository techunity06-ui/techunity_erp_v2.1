<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Sales Order Planning";
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
        MRP_GET_SALES_ORDER_SLUG_VIEW
    ]);
   /* if(!in_array(MRP_GET_SALES_ORDER_SLUG_VIEW,$bulkAccessArray)) {
 		header("Location: ".DOMAIN."permission_access");
    }*/
	$branch_id = $_SESSION['branch_id'];

	$companyConfiguration=getCompanyConfiguration($dbcon);

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>SALES ORDER PLANNING</title>
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
									<div class="col-md-12" style="display:none;">
										<div class="col-md-4">
											<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'show_data()'); ?>	
										</div>	
										<div class="col-md-8 text-right">
										<!-- <button class="btn btn-success btn-flat" onclick="create_workorder();">Create Workorder</button> -->
									</div>
									</div>
									<div class="col-md-12">
										<div class="col-md-6">
											<div class="col-md-4">
												<label class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Single SO</label>
												<input id="sotype1" name="sotype" onClick="show_data();" type="radio" class="" title="Pending" value="1" />
											</div>
											<div class="col-md-4">
												<label class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Multiple SO</label>
												<input id="sotype2" name="sotype" checked onClick="show_data();" type="radio" class="" title="Pending" value="2" />
											</div>
											<!--<div class="col-md-4">
												<label class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Done</label>
												<input id="jobwork_status2" name="jobwork_status" onClick="reload_complete_data();" type="radio" class="" title="Done" value="3" />
											</div>-->
										</div>

									</div>
									<div class="adv-table">
										<div id="div1">
										<table  class="display table table-bordered table-striped" id="dynamic-table1">
											<thead>
												<tr>
												  <!-- <th class="nosort">  <input id="checkAll" type="checkbox" onclick="checkAll();"  name="chk[]"/></th> -->
												  <th>Sales Order No</th>
												  <th>Sales Order Date</th>
												   <?php if($companyConfiguration['customer_show_in_production'] == '1'){ ?>
												  	<th>Cust Name</th>
												 <?php } ?>
												  <th>Product Name</th>
												  <th>Product Category</th>
												  <th>Request Qty</th>
												  <th>Pending Qty</th>
												  <th>Stock</th>

												  <th>Delivery Date</th>
												    <?php if($companyConfiguration['outside_jobwork']){ ?>
											<th>Jobwork Type</th>
										<?php } ?>
												   <?php if($_SESSION['branch_id']==0){ ?>
														<th>Branch Name</th>
													  <?php } ?>
												   <th class="hidden-phone">Action</th>	 			  
												</tr>
											</thead>
											<tbody id="data_table"></tbody>
										</table>
										</div>
										<div id="div2">
											<table  class="display table table-bordered table-striped" id="dynamic-table2">
												<thead>
													<tr>
													<th>Product Name</th>
													<th>Product Category</th>
													<th>So Qty</th>
													<th>So Planning Qty</th>
													<th>Min Qty</th>
													<th>Stock</th>
													<th>Planing Pending Qty</th>
													
													<th class="hidden-phone">Action</th>	 			  
													</tr>
												</thead>
												<tbody id="data_table1"></tbody>
											</table>
										</div>
									</div>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
			<?php include_once($include1.'so_stock_allocate.php');?>
			<?php include_once($include1.'so_product_version.php');?>
			<?php include_once($include1.'reserve_stock_entry_so.php');?>
			<?php include_once($include1.'work_order_indent_modal.php');?>
			<?php include_once($include1.'preview_so_trn_pro_description.php');?>
			<?php include_once($include1.'solid_planing.php');?>
			<?php include_once($include1.'solid_allocate.php');?>
			
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?php echo ROOT.PRODUCTION_ROOT; ?>js/app/get_sales_order_details_solid.js"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
		</script>
	</body>
</html>
