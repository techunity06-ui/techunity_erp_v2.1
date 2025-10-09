<div class="modal colored-header info" id="preview_po_approval_hist_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval1 : #<span id="apprv_po_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="card">
						<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
							<li role="presentation" id="tab1" class="active"><a href="#so_detail" aria-controls="so_detail" role="tab" data-toggle="tab">SO Details</a></li>
							<li role="presentation" id="tab2"><a href="#produ_detail" aria-controls="produ_detail" role="tab" data-toggle="tab">Product Detail</a></li>
							<!-- <li role="presentation" id="tab2"><a href="#produ_document" aria-controls="produ_document" role="tab" data-toggle="tab">Document</a></li> -->
						</ul>
						<div class="tab-content"> 
							<div role="tabpanel" class="tab-pane active" id="so_detail">
								<div class="col-md-12" id="mod_so_comp_div_sec"></div>
							</div>
							<div role="tabpanel" class="tab-pane" id="produ_detail">
								<div class="col-md-12" id="mod_so_pro_div_sec"></div>
							</div>
							<!-- <div role="tabpanel" class="tab-pane" id="produ_document">
								
							</div> -->
						</div>
					</div>
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
										<select class="select2" id="po_approve_status" name="po_approve_status">
											<option value="0">Reject</option>
											<option value="1">Approve</option>
										</select>
									</td>
									<td>
										<textarea class="form-control" id="po_approve_remark" name="po_approve_remark" placeholder="Remark"></textarea>
									</td>
									<td>
										<input type="button" class="btn btn-primary add_so_apprv_hist" id="apprv_btn" onclick="add_po_apprv_hist()" value="Add">
										<input type="button" class="btn btn-primary add_oa_apprv_hist" id="apprv_btn" onclick="add_po_apprv_hists()" value="Add">
									</td>
								</tr>
							</table>
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
		<!-- <input type="hidden" id="eid" name="eid" value=""> -->
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->