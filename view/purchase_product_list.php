<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
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
						  <h3>New Purchase Product</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active">Purchase Product </li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--Customer overview start-->
		  <div class="row">
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New Purchase Product 
					  </header>	
					<div class="panel-body">
						<form role="form" id="product_add" action="javascript:;" method="post" name="product_add">
							  
							  <div class="form-group">
								  <label for="Product Type">Product Name *</label>
								  <input type="text"  class="form-control" id="product_name" name="product_name" placeholder="Product Name" />
							  </div>							  <div class="form-group">
								  <label for="Product Description">Product Description</label>
								  <textarea class="form-control" id="productdes" name="productdes" placeholder="Product Description"></textarea>
							  </div>
							  <div class="form-group">
								  <label for="Product Type">HSN Code</label>
								  <input type="text" class="form-control" id="product_code" name="product_code" placeholder="HSN Code" />
							  </div>
							  <div class="form-group">
								  <label for="Product Type">Product Rate</label>
								  <input type="number" min="0" class="form-control" id="product_mst_rate" name="product_mst_rate" placeholder="Product Rate" />
							  </div>
							  <div class="form-group">
									<label for="Product Type">Product Unit</label>
									<select class="select2" name="product_mst_unitid" id="product_mst_unitid"  title="Select Unit">
										<?=getunit($dbcon,0);?>
									</select>
							  </div>
							  <div class="form-group">
									<label for="Product Type">Intra Tax(CGST+SGST)</label>
									<select class="form-control" name="intra_tax" id="intra_tax">
										<?=getformula($dbcon,'');?>
									</select>
							  </div>
							  <div class="form-group">
									<label for="Product Type">Inter Tax(IGST)</label>
									<select class="form-control" name="inter_tax" id="inter_tax">
										<?=getformula($dbcon,'');?>
									</select>
							  </div>
							  <div class="form-group">
								<div class="checkbox">
								  <label>
									 <input type="checkbox" id="multi_company" name="multi_company" checked value="1">  View in all Company
								  </label>
								</div>
							  </div>	
								<input type='hidden' name='mode' id='mode' value='add' />
							  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							  <button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
			<div class="col-sm-9">
			<section class="panel">
				  <header class="panel-heading">
					 Purchase  Product List
				 <span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					<a href="javascript:;" class="fa fa-times"></a>
				 </span>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Product Name</th>
					  <th>HSN Code</th>
					  <th>Product Rate</th>
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
		  
		  <!--Customer overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit Purchase Product</h3>				
			</div>
			<div class="modal-body form">
			<form id="FormEditproduct" role="form" method="post" novalidate>				
				<div class="form-group">
				  <label for="Product Type">Product Name</label>
				  <input type="text" class="form-control" id="edit_product_name" name="edit_product_name" placeholder="Product Name" />
			  </div>
			   <div class="form-group">
								  <label for="Product Type">Product Description</label>
								  <textarea type="text" class="form-control" id="edit_product_des" name="edit_product_des" placeholder="Product Description"></textarea>
							  </div>
			  <div class="form-group">
								  <label for="Product Type">HSN Code</label>
								  <input type="text" class="form-control" id="edit_product_code" name="edit_product_code" placeholder="HSN Code" />
							  </div>
				
				<div class="form-group">
								  <label for="Product Type">Product Rate</label>
								  <input type="number" min="0" class="form-control" id="edit_product_mst_rate" name="product_mst_rate" placeholder="Product Rate" />
							  </div>
							  <div class="form-group">
									<label for="Product Type">Product Unit</label>
									<select class="select2" name="product_mst_unitid" id="edit_product_mst_unitid"  title="Select Unit">
										<?=getunit($dbcon,0);?>
									</select>
							  </div>
							  <div class="form-group">
									<label for="Product Type">Intra Tax(CGST+SGST)</label>
									<select class="form-control" name="intra_tax" id="edit_intra_tax">
										<?=getformula($dbcon,'');?>
									</select>
							  </div>
							  <div class="form-group">
									<label for="Product Type">Inter Tax(IGST)</label>
									<select class="form-control" name="inter_tax" id="edit_inter_tax">
										<?=getformula($dbcon,'');?>
									</select>
							  </div>

				
				<div class="form-group">
					<div class="checkbox">
					  <label>
						 <input type="checkbox" id="edit_multi_company" name="multi_company" checked value="1">  View in all Company
					  </label>
				  </div>
				</div>											  
											
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/purchaseproduct_mst.js?v1.<?=date('dmy')?>"></script>
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
