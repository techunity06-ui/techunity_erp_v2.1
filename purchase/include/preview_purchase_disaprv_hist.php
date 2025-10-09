<div class="modal colored-header info" id="preview_po_disapproval_hist_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval : #<span id="apprv_po_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="card">
							<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
								<li role="presentation" id="tab1" class="active"><a href="#po_detail" aria-controls="po_detail" role="tab" data-toggle="tab">PO Details</a></li>
								<li role="presentation" id="tab2"><a href="#produ_detail" aria-controls="produ_detail" role="tab" data-toggle="tab">Product Detail</a></li>
							</ul>
							<div class="tab-content"> 
								<div role="tabpanel" class="tab-pane active" id="po_detail">
									<div class="col-md-12" id="mod_po_comp_div_sec"></div>
								</div>
								<div role="tabpanel" class="tab-pane" id="produ_detail">
									<div class="col-md-12" id="mod_po_pro_div_sec"></div>
								</div>
							</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="order-po-history-datatable">
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
		<input type="hidden" id="ref_ord_id" name="ref_ord_id" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->