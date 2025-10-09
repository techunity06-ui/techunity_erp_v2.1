<div class="modal colored-header info" id="solid_exe_entry" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>So Planing</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-3">
							Product Name
						</div>
						<div class="col-md-3">
							<span id="pro_name"> </span>
						</div>
						<div class="col-md-3">Balty</div>
						<div class="col-md-3">
							<span id="batchsiz"> </span>
						</div>
					</div>
					<div class="col-md-12">
						<div class="col-md-3">
							Qty
						</div>
						<div class="col-md-3">
							<span id="tqty"> </span>
						</div>
						<div class="col-md-3">Finish Qty</div>
						<div class="col-md-3">
							<input type="number" class="form-control" value="" onkeyup="roll_stock_model()" id="finish_qty" name="finish_qty" />
						</div>
					</div>
					<div class="col-md-12"  id="show_roll"></div>
					
					<!-- <div class="col-md-12" id="solid_exe_entry_div"></div>  -->
					 <input type="hidden" id="balty" name="balty" value="" />
					<input type="hidden" id="product_id" name="product_id" value="" /> 
					<input type="hidden" id="row_id" name="row_id" value="0" />
					<div class="col-md-12" style="text-align: center;">
						 <button class="btn btn-primary" data-original-title="Save" data-toggle="tooltip" data-placement="top" type="button" onclick="save_solid_exe_planning();">Save</button> 
					</div>

				</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-content -->
</div><!-- /.modal-content -->