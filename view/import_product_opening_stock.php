<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Import BOM";
$mode="Add";

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRODUCT_OPENING_STOCK_CSV_UPLOAD
]);

if(!in_array(PRODUCT_OPENING_STOCK_CSV_UPLOAD,$bulkAccessArray)){
    //header("Location: ".DOMAIN."permission_access");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" >
		<?php include_once('../include/include_top_menu.php');?>
		<?php include_once('../include/left_menu.php');?>
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
									<!--<li><a href="<?//=ROOT.'customer_list'?>">Customer List</a></li>-->
								</ul>
							</div>
						</section>
					</div>	
				</div>
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								New <?=$form?>
							</header>	
							<div class="panel-body ">
								<form class="form-horizontal" role="form" id="import_customer" action="javascript:;" method="post" name="import_customer" enctype="multipart/form-data">
									<div class="row">
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Import Bom.csv File</label>
												<div class="col-md-4 col-xs-11">
													<input type="file" id="excel_file" name="excel_file" class="form-control"   title="Select File"/>
													<div id="msg"></div>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">File Formate</label>
												<div class="col-md-6 col-xs-11">
													<a href="<?=ROOT.CUSTOMER_VWING.'stock.csv'?>" target="_blank" class="btn btn-info">Click to View Csv File Formate  </a>
												</div>
											</div>
											<button type="submit" class="btn btn-success">Submit</button> &nbsp;
											<a href="<?=ROOT.'production/bom_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-3"></div>	
										</div>
									</div>	
									<input type='hidden' name='mode' id='mode' value='check_data' />
									<input type='hidden' name='eid' id='eid' value='<?=$rel['festival_id']?>' />
									<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
								</form>
							</div>	
						</section>
						<section class="panel" id="imported_data_section" style="display:none">
							<header class="panel-heading">
								Error In Import Data Record
							</header>
							<div class="panel-body">
								<div id="temp_custdata"></div>
							</div>						
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once('../include/footer.php');?>
	</section>
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/import_product_opening_stock.js?<?=time()?>"></script>
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
