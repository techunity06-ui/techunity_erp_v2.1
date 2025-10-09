<?php 
	session_start();
	include('../include/urlfile.php');
	
	$form="TDS Tax Category";
	// error_reporting(E_ALL);
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	READ_TDS_TAX_CATEGORY_MASTER,
        CREATE_TDS_TAX_CATEGORY_MASTER
    ]);

    if(!in_array(READ_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>TDS TAX CATEGORY LIST</title>
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
						<h3>New <?=$form?></h3>
					</header>	
					<div class="">
						<ul class="breadcrumb">
							<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							<li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							<li class="active"><?=$form?> List</li>
						</ul>
					</div>
				</section>
				<!--breadcrumbs end -->
			</div>	
		</div>
		<!--Cost Center overview start-->
		<div class="row">
			
			<?php if(in_array(CREATE_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray)){ ?>
				<div class="col-sm-12">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
				<section class="panel">
					<header class="panel-heading">
                        <?php if(in_array(READ_TDS_TAX_CATEGORY_MASTER,$bulkAccessArray)){ ?>
						<span class="tools pull-right"> 
							<a href="<?=ROOT.ADMINISTRATION_ROOT.'tds_tax_category_create'?>" ><button class="btn btn-success btn-flat" >Add <?=$form?></button></a>
						</span>
                        <?php } ?>
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="tds-tax-category-table">
								
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Tds Category</th>
										<th>Section code</th>
										<th>Date</th>
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
		
		<!--CostCenter overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog custom-width">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Edit Cost Center</h3>
			
		</div>
		
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/tds_tax_category.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
</body>
</html>
