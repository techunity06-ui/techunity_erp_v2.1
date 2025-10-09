<div class="modal colored-header info" id="preview_returnable_approval_hist_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="width:90%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval : #<span id="apprv_po_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-3">
							<h4> Type : <span id="challan_type"></span></h4>
						</div>
						<div class="col-md-3">
							<h4> Party Name : <span id="party_name"></span></h4>
						</div>
						<div class="col-md-3">
							<h4>Challa No : <span id="challan_no"></span></h4>
						</div>
						<div class="col-md-3">
							<h4>Challan Date : <span id="challan_date"></span></h4>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="returnable-channal-datatable">
								<thead>
									<tr>
										 <th>Item Name</th>
		                                 <th>Item Desc</th>
		                                 <th>Item Per</th>
		                              	 <th>Item Qty</th>
		                              	 <!-- <th>Approved Item Qty</th>
		                              	 <th>Dis approved Item Qty</th> -->
		                              	 <th>Item Price</th>
		                              	 <th>Remark</th>
		                              	 <th>Item Status</th>
		                              	 <th>Action</th>					  
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
		<input type="hidden" id="ref_ord_id" name="ref_ord_id" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->