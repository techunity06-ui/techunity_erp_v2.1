<div class="modal colored-header info" id="preview_stock_transfer_approve_model" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval : #<span id="apprv_stock_transfer_id"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="mod_stock_div_sec"></div>
					<div class="col-md-12" id="mod_stock_per_div_sec">
						<div class="form-group">
							<table class="display table table-bordered table-striped">
								<tr>
									<!--<th width="30%">Assign User</th>-->
									<th width="20%">Status</th>
									<th width="45%">Remark</th>
									<th width="5%">Action</th>
								</tr>
								<tr>
									<td>
										<select class="select2" id="stock_transfer_approve_status" name="stock_transfer_approve_status">
											<option value="0">Reject</option>
											<option value="1">Approve</option>
										</select>
									</td>
									<td>
										<textarea class="form-control" id="stock_transfer_approve_remark" name="stock_transfer_approve_remark" placeholder="Remark"></textarea>
									</td>
									<td>
										<input type="button" class="btn btn-primary" id="apprv_btn" onclick="add_stock_transfer_apprv_hist()" value="Add">
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="stock-history-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>User</th>
										<th>Status</th>
										<th>Remark</th>
										<th>Date</th>					  
									</tr>
								</thead>
								<tbody>
								</tbody>				 
							</table>
						</div>
						</div>
					</div>
					
					
				</div>
			</div>	
		</div>
		<input type="hidden" id="stock_transfer_id" name="stock_transfer_id" value="">
		
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->