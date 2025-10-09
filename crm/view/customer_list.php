<?php 
session_start();
include('../include/urlfile.php'); 
$form="Party";
$countryid='101';
$stateid='1';
$cityid='1';
$infopage = pathinfo( __FILE__ );
$_SESSION['page']='crm/'.$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
	//check paermission for customer add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CUSTOMER_PARTY_MASTER_SLUG_READ,
	CUSTOMER_PARTY_MASTER_SLUG_CREATE,
	CUSTOMER_PARTY_MASTER_SLUG_EXPORT
]);

if(!in_array(CUSTOMER_PARTY_MASTER_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
$companyConfiguration=getCompanyConfiguration($dbcon);
$enable_post_crm = $companyConfiguration['enable_post_crm'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>CUSTOMER LIST</title>
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
								
								<div class="col-md-12">
									<div class="col-md-6">
										<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_cust_datatable()','4','6'); ?>
									</div>
									
									
									<div class="col-md-6">
										<span class="tools pull-right">
											<?php if(in_array(CUSTOMER_PARTY_MASTER_SLUG_CREATE,$bulkAccessArray)){ ?>
												<a href="<?=ROOT.CRM_ROOT.'customer'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>	
											<?php } ?>
											<a href="<?=ROOT.CRM_ROOT.'	export_customer_data'?>" ><button class="btn btn-info btn-flat" >Export / View <?=$form?></button></a>
											<?php if(in_array(CUSTOMER_PARTY_MASTER_SLUG_EXPORT,$bulkAccessArray)){ ?>
												<a href="javascript:;" onClick="tableToExcel('dynamic-table', 'Party Data')" ><button class="btn btn-info btn-flat" >Export Excel</button></a>
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
												<th>Sr.</th>
												<th>Party Category</th>
												<th>Type</th>
												<th>Company Name</th>
												<th>E-mail</th>
												<th>Mobile</th>
												<th>GST No.</th>
												<th>Username</th>
												<th>Owner User</th>
												<?php if($enable_post_crm == 1) { ?>
												<th>Post Crm Status</th>
												<?php } ?>
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
		include_once($include.'footer.php');
		?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.CRM_ROOT?>js/app/customer.js?<?=time()?>"></script>
	<script>
		$(".select2").select2({
			width: '100%'
		});	

		var tableToExcel = (function() {
			var uri = 'data:application/vnd.ms-excel;base64,'
			, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head></head><body><table>{table}</table></body></html>'
			, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
			, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
			return function(table, name) {
				if (!table.nodeType) table = document.getElementById(table)
					var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
				window.location.href = uri + base64(format(template, ctx))
			}
		})()
	</script>
</body>
</html>
