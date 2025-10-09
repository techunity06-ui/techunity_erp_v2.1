<div class="modal colored-header info" id="qc_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Copy Bom</h3>
			</div>
			<div class="modal-body form" style="height:350px !important;">
				<!-- <form class="form-horizontal" role="form" id="qc_add" action="javascript:;" method="post" name="qc_add"> -->
					<div class="col-md-6">
						<div class="col-md-12 m-bot15">
							<input type="hidden" name="qc_process_id" id="qc_process_id" value="" />
							<input type="hidden" name="qc_product_id" id="qc_product_id" value="" />
								<div class="form-group">
									<label class="col-md-4 control-label">Parameter Name *</label>
									<div class="col-md-8 col-xs-11">
										 <select class="select2" name="param_id" id="param_id">
		                                 <?php  echo get_all_parameter($dbcon,'') ?>
		                                 </select>
									</div>
								</div>
							</div>
							<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Base Value *</label>
									<div class="col-md-8 col-xs-11">
										<input type="text" class="form-control" name="param_value" id="param_value" onkeyup="check_base_value(this.value)" />

									</div>
								</div>
							</div>
							<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Tolerance (+)</label>
									<div class="col-md-8 col-xs-11">
										   <input type="text" class="form-control numbersOnly" name="tolerance_plus" id="tolerance_plus" onkeyup="check_param_tolerance(this.value)" />

									</div>
								</div>
								</div>
							<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Tolerance (-)</label>
									<div class="col-md-8 col-xs-11">
										<input type="text" class="form-control numbersOnly" name="tolerance_minus" id="tolerance_minus" onkeyup="check_param_tolerance(this.value)" />

									</div>
								</div>
								</div>
							<div class="col-md-12 text-center">
								<div class="form-group">
									<input type="button" class="btn btn-primary" value="ADD"  style="" onclick="add_param_value()" id="add_param" />
								</div>
								</div>
						</div>
				<!-- </form> -->
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

