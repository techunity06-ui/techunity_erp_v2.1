<div class="modal colored-header info" id="add_product_modal" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
		<h3>Add Product</h3> 
	</div>
	<div class="modal-body form">
		<div class="row"> 
			<div class="col-md-12"> 
			<form class="form-horizontal" role="form" id="product_add" action="javascript:;" method="post" name="product_add">
				<div id="typepro" class="col-md-12"> 
					<div class="form-group"> 
					  <div class="col-md-12" style="padding-right:0px;">
					  
						  <input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="product_type_finish" name="product_type" value="1" checked> <label for="product_type_finish" style="font-weight: bold;" title="Use As FINISH PRODUCT">FINISH PRODUCT</label>
						  
						  <input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="product_type_assembly" name="product_type" value="2"/> <label for="product_type_assembly" style="font-weight: bold;" title="Use As ASSEMBLY PRODUCT">ASSEMBLY PRODUCT</label>
						  
						  <input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="product_type_semi" name="product_type" value="3"/> <label for="product_type_semi" style="font-weight: bold;" title="Use As SEMI-FINISH">SEMI-FINISH</label>
						  
						  <input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="product_type_row" name="product_type" value="4"/> <label for="product_type_row" style="font-weight: bold;" title="Use As RAW MATERIAL">RAW MATERIAL</label>
						  
						  <input type="radio" class="" style="margin-left: 10px;width: 17px;height: 15px;" id="product_type_component" name="product_type" value="5"/> <label for="product_type_component" style="font-weight: bold;" title="Use As FINISH COMPONENT">FINISH COMPONENT</label>
					  </div>
					</div> 
				</div>
				<div class="clearfix"></div>	
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label" style="text-align:left;" for="product_name">Product Name*</label>
						<div class="col-md-12">
							<input class="form-control" type="text" name="product_name" id="product_name" placeholder="Product Name" value="" />
						</div>			  
					</div>			  
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label" style="text-align:left;" for="product_desc">Product Description</label>
						<div class="col-md-12">
							<textarea class="form-control" name="product_desc" id="product_desc" placeholder="Product Description"></textarea>
						</div>			  
					</div>			  
				</div>	
				<div class="clearfix"></div>	
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label" style="text-align:left;" for="product_hsn_code">HSN Code</label>
						<div class="col-md-12">
							<input class="form-control" type="text" name="product_hsn_code" id="product_hsn_code" placeholder="HSN Code" value="" />
						</div>			  
					</div>			  
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label" style="text-align:left;" for="unitid">Unit</label>
						<div class="col-md-12">
							<select class="select2" name="unitid" id="unitid">
								<?=getunit($dbcon,$id);?>
							</select>
						</div>			  
					</div>			  
				</div>
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-success">Submit</button> &nbsp;
					<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
				<input type='hidden' name='product_model' id='product_model' value='product_model' />	 				
				<input type='hidden' name='mode' id='mode' value='add' />	 
			</form>
			</div> 
		</div>
	</div>	
</div>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog --> 
