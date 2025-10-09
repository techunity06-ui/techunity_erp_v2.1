<div class="modal colored-header info " id="bs-add_bom_data" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Update </h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="ind_add" action="javascript:;" method="post" name="ind_add">
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Sheet Product Name</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="product_name" placeholder="product_name" id="product_name" title="Product Name" value="" readonly />
									</div>
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Product Name</label>
									<div class="col-md-12">
										<select class="select2" title="Select product" name="product_id" id="product_id" onchange="check_unit();">
											<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
										</select>
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Sheet Unit Name</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="unit_name" placeholder="" id="unit_name" title="Enter Id" value="" readonly />
									</div>
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Unit Name</label>
									<div class="col-md-12">
										<input type="hidden" name="unit_id" id="unit_id" value="" />
										<input type="text" class="form-control" name="sy_unit_name" placeholder="" id="sy_unit_name" title="Unit Name" value="" readonly />
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Qty</label>
									<div class="col-md-12">
										<input type="number" class="form-control" name="qty" placeholder="" id="qty" title="Qty" value="" />
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-3"></div>
							<button type="submit"  class="btn btn-success">Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							<input type='hidden' name='bom_temp_id' id='bom_temp_id' value='' />
							<input type='hidden' name='mode' id='mode' value='Add' />
						</form>
					</div>
				</div>	
			</div>
		</div>
	</div>
</div>

