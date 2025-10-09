<div class="modal colored-header info" id="work_order_approve" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Work Order Approve</h3>
			</div>
			<div class="modal-body form">
	<div class="row">

	<div class="col-md-12" id="mod_po_comp_div_sec"><div class="form-group">
	<table class="display table table-bordered table-striped">
	<tbody><tr>
	<td colspan="2"><strong>Product Name:</strong> <span id="wo_product_name"></span></td>
	<td><strong> Qty:</strong> <span id="wo_qty"></span></td>
	</tr>
	</tbody></table></div>
	<hr>
	</div>

	</div>
					<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="mod_po_comp_div_sec"></div>
					<div class="col-md-12" id="mod_po_per_div_sec">
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
										<select class="select2" id="wo_approve_status" name="wo_approve_status">
											<option value="2">Reject</option>
											<option value="1">Approve</option>
										</select>
									</td>
									<td>
										<textarea class="form-control" id="wo_approve_remark" name="wo_approve_remark" placeholder="Remark"></textarea>
									</td>
									<td>
									<input type="hidden" id="wrp_id" value="">
										<input type="button" class="btn btn-primary" id="apprv_btn" onclick="add_wo_apprv_hist()" value="Add">
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="order-wo-history-datatable1"">
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
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

