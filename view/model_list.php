<?php
	session_start();
	include_once("../config/config.php");
	//error_reporting(E_ALL);
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Model";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container" >
<?php include_once('../include/include_top_menu.php');?>
<!--sidebar start-->
<?php include_once('../include/left_menu.php');?>
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
							<li class="active"><?=$form?> List</li>
						</ul>
					</div>
				</section>
				<!--breadcrumbs end -->
			</div>	
		</div>
		<!--unit overview start-->
		<div class="row">
			<div class="col-sm-3">
				<section class="panel">
					<header class="panel-heading">
						New <?=$form?>
					</header>
					<div class="panel-body">
						<form role="form" id="model_add" action="javascript:;" method="post" name="model_add">
							<div class="form-group">
								<label>Choose Product*  </label>
									<input type="button" name="addProduct" id="addProduct" style="float:right;    margin-bottom: 5px;" title="Add Product" data-toggle="modal" data-target="#add_product_modal" class="btn btn-primary" value="+"/>
								<select class="select2" name="product_id" id="product_id">
									<?=get_product($dbcon,'','0')?>
								</select>
							</div>
							<div class="form-group">
								<label>Model Name*</label>
								<input class="form-control" type="text" name="model_name" id="model_name" placeholder="Model Name" value="" />
							</div>	
							<div class="form-group">
								<label>Model Desc</label>
								<textarea class="form-control" name="model_desc" id="model_desc" placeholder="Model Description" ></textarea>
							</div>			  
							<button type="submit" class="btn btn-success">Submit</button>
						</form>
						
					</div>
				</section>
			</div>
			<div class="col-sm-9">
				<section class="panel">
					<header class="panel-heading">
						<?=$form?> List
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="model-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Product Name</th> 
										<th>Model Name</th> 
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
		
		<!--unit overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../include/add_product.php');?>
<?php include_once('../include/allocate_req_product.php');?>
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditModel" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog custom-width">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Edit <?=$form?></h3> 
		</div>
		<div class="modal-body form">
		<form id="FormEditModel" role="form" method="post" novalidate>
			<div class="form-group">
				<label>Product*</label>
				<select class="select2" name="edit_product_id" id="edit_product_id">
					<?=get_product($dbcon,'','1')?>
				</select>
			</div>
			<div class="form-group">
				<label for="edit_model_name">Model Name</label>
				<input class="form-control" type="text" name="edit_model_name" id="edit_model_name" value="" />
			</div>	
			<div class="form-group">
				<label>Model Desc</label>
				<textarea class="form-control" name="edit_model_desc" id="edit_model_desc" placeholder="Model Description" ></textarea>
			</div>	 
		</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-success btn-flat" type="submit">Update</button>
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/model_mst.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/product_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$('#typepro').hide();
</script>
</body>
</html>
