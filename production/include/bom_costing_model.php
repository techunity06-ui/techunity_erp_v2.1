<div class="modal colored-header info" id="bom_costing_model" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Allocate BOM Costing</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="bom_costing" action="javascript:;" method="post" name="bom_costing">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
									<label class="col-md-4 control-label">BOM Costing *</label>
									<div class="col-md-8 col-xs-11">
										<select class="select2" name="bom_costing_id" id="bom_costing_id" title="Select BOM Costing">
										</select>
									</div>
								</div>
						</div>
						<div class="col-md-12">
						<center>
							<input type="submit" name="save_costing" id="save_costing" class="btn btn-success" value="Assign BOM Costing" /> 
						</center>
					</div>
					</div>
					<input type="hidden" name="sp_id" id="sp_id" value="">
					<input type="hidden" name="mode" id="mode" value="bom_costing_assign">
					
				</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

