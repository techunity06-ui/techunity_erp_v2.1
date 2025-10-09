<div class="modal colored-header info" id="get_warehose_deduction_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3> Godown Stock For -- <span id="product_name"></span> </h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Required Qty.*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="gd_req_qty" name="gd_req_qty" class="form-control" value="" readonly>
								</div>
							</div>
						</div>	
						
					</div>
					
					<div class="col-md-12">
						<div class="panel-body">
							<div class="adv-table">
								 <table class="table table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th>Godown</th>
											<th>Current Stock</th>
											<th>Deducted Stock</th>
										</tr>
									</thead>
									<tbody id="godown_table">
										
									</tbody>
								</table>
							</div>
						</div>
					</div>
					
				
					<input type="hidden" name="product_id" id="product_id" value="" />
					
					
					<div class="col-md-12" style="margin-top:10px;">
						
						<div class="col-md-6 col-md-offset-4">  	
							<input type="button" id="wb_btn" class="btn btn-success" value="Save" />
						</div>	
						
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

