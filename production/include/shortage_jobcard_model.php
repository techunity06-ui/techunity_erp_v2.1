<div class="modal colored-header info" id="shortage_jobcard_model" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="width:65%">
		<div class="modal-content">
			<div class="modal-header">
				<!-- <button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button> -->
				<h3>Create Jobcard</h3>
			</div>
			<div class="modal-body form">
				<div class="row" id="jobcard_row">
					<div class="col-md-12 mtop20">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label"><strong>Product Name : </strong></label>
								<label class="col-md-8 control-label" id="lbl_product_name"></label>
							</div>
						</div>	
						
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label"><strong>BOM Version :</strong> </label>
								<label class="col-md-8 control-label" id="lbl_bom_ver_name"></label>
							</div>
						</div>	
					</div>

					<div class="col-md-12 mtop20">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label"><strong>Jobcard No :</strong> </label>
								<label class="col-md-8 control-label" id="lbl_product_name"><strong><?= load_common_no($dbcon,54)?></strong></label>
							</div>
						</div>	
						
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label"><strong>Jobcard Date :</strong> </label>
								<label class="col-md-8 control-label"><strong><?=date('d-m-Y') ?></strong></label>
							</div>	
						</div>	
					</div>
					
					<div class="col-md-12 mtop20">
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label"><strong>Total Qty:</strong></label>
								<label class="col-md-8 control-label" id="lbl_total_qty"></label>
							</div>	
						</div>	
						
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label"><strong>Jobcard Qty:</strong></label>
								<div class="col-md-4 col-xs-11">
										 <input type="number" class="form-control numbersOnly" name="jobcard_qty" id="jobcard_qty"  onkeydown="return numericonly(event)"/>

									</div>
							</div>	
						</div>
					
					</div>
					
					<input type="hidden" name="product_id" id="product_id" value="" />
					<input type="hidden" name="rp_id" id="rp_id" value="" />
					<input type="hidden" name="new_rp_id" id="new_rp_id" value="" />
					<input type="hidden" name="total_qty" id="total_qty" value="" />
					<!-- <input type="hidden" name="jobcard_qty" id="jobcard_qty" value="" /> -->
					<input type="hidden" name="unit_id" id="unit_id" value="" />
					<input type="hidden" name="reorder_qty" id="reorder_qty" value="" />
					<input type="hidden" name="bom_version_id" id="bom_version_id" value="" />
					<input type="hidden" name="branch_id_modal" id="branch_id_modal" value="" />
					
					<div class="row mtop20 m-bot15">
						<div class="col-md-12 text-center" style="margin-top:10px;">
							<button type="button" onclick="create_jobcard()" class="btn btn-success">Create Jobcard & Next</button> &nbsp; &nbsp; 
							<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
					</div>
				</div>
				<div class="row" id="process_row">
					<div class="col-md-6 col-md-offset-3" id="mod_per_div_add_process" ></div>
				</div>
				<div class="row" id="material_row">
					<div class="col-md-12" id="mod_per_div_show_material" ></div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

