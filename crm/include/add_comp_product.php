<div class="modal colored-header info" id="modal-comp-product" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
		<h3>Add Product</h3> 
	</div>
	<div class="modal-body form">
		<div class="row row_margin"> 
			<div class="col-md-12"> 
			<form class="form-horizontal" role="form" id="product_add" action="javascript:;" method="post" name="product_add">
				<div id="typepro" class="col-md-12"> 
					<div class="form-group"> 
					 <label class="col-md-12 control-label row_margin" style="text-align:left;" for="product_name">Product Type *</label>
					  <div class="col-md-12" style="padding-right:0px;">
						 <select class="select2" name="comp_product_type_sel" id="comp_product_type_sel" onChange="load_product_typeiwse(this.value);" title="Select Product Type" tabindex="301">
							<?=getproducttype($dbcon,'0');?>
						</select>
					  </div>
					</div> 
				</div>
				<div class="clearfix"></div>	
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label row_margin" style="text-align:left;" for="product_name">Product Name*</label>
						<div class="col-md-12">
							<select class="select2" tabindex="302" title="Select product" name="comp_product_id" id="comp_product_id" onChange="load_productdetail(this.value);get_hsn(this.value);"  style="width:100% !important"><!--load_qty()-->
								<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
							</select>
						</div>			  
					</div>			  
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label row_margin" style="text-align:left;" for="product_name">Product Price*</label>
						<div class="col-md-12">
							<input class="form-control" type="text" name="comp_product_price" id="comp_product_price" placeholder="Product Price" value="" tabindex="303" onkeypress="return isNumberKey(event)" maxlength="10" />
						</div>			  
					</div>			  
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label row_margin" style="text-align:left;" for="product_desc">Remark</label>
						<div class="col-md-12">
							<textarea class="form-control" name="comp_prudct_remark" id="comp_prudct_remark" placeholder="Product Description" tabindex="304"></textarea>
						</div>			  
					</div>			  
				</div>	
				
				<div class="col-md-12 text-center">
					<input type="hidden" class="form-control" name="comp_id" id="comp_id" />
					<input type="hidden" class="form-control" name="cust_comp_product_id" id="cust_comp_product_id" />
					<button type="button" class="btn btn-success" tabindex="305" onclick="add_comp_modal_produdct()">Submit</button> &nbsp;
					<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true" tabindex="306">Close</button>
				</div>
			</form>
			</div> 
		</div>

		<div class="row ">
			<div class="col-md-12 load_product_details">
				
			</div>
		</div>

	</div>	
</div>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog --> 
