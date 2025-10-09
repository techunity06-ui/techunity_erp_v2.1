<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Printing Entry";
	$type=$dbcon->real_escape_string($_REQUEST['id']);
	if($type==2){
		$endstock="selected='selected'";
	}else if($type==1){
		$instock="selected='selected'";
	}else{
		$inorder="selected='selected'";
	}
	
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
    // if(!in_array(MRP_GET_SALES_ORDER_SLUG_VIEW,$bulkAccessArray)) {
 	// 	header("Location: ".DOMAIN."permission_access");
    // }
	$branch_id = $_SESSION['branch_id'];

	$companyConfiguration=getCompanyConfiguration($dbcon);

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Extrusion PLANNING</title>
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
									<div class="col-md-12" >
										<div class="col-md-4" style="display:none;">
											<?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'show_data()'); ?>	
										</div>	
										
									</div>
									<div class="col-md-8 ">
											<select class="select2" name="stage" id="stage"  onChange="show_data();">
												<option value="0" <?=$inorder?>>In order</option>
												<option value="1" <?=$instock?>>In Stock</option>
												<option value="2" <?=$endstock?>>End Process</option>
											</select>
										</div>
									<div class="adv-table">
										<table  class="display table table-bordered table-striped" id="dynamic-table1">
											<thead>
												<tr>
												  	<th>Product Name</th>
												  	<th>Balty</th>
												  	<th>Qty</th>
													<th class="hidden-phone">Action</th>	 			  
												</tr>
											</thead>
											<tbody id="dynamic-table1"></tbody>
										</table>
									</div>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
			<?php include_once($include1.'solid_extrusion_allocate.php');?>
			<?php include_once($include1.'solid_printing_entry.php');?>
			
			
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?php echo ROOT.PRODUCTION_ROOT; ?>js/app/solid_printing_entry.js"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
		</script>
	</body>
</html>
