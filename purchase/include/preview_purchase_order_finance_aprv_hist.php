<div class="modal colored-header info" id="preview_po_finance_approval_hist_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Finance Approval : #<span id="fin_apprv_po_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="card">
							<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
								<li role="presentation" id="tab1" class="active"><a href="#fin_po_detail" aria-controls="po_detail" role="tab" data-toggle="tab">PO Details</a></li>
								<li role="presentation" id="tab2"><a href="#fin_produ_detail" aria-controls="produ_detail" role="tab" data-toggle="tab">Product Detail</a></li>
								<li role="presentation" id="tab2"><a href="#fin_produ_document" aria-controls="produ_document" role="tab" data-toggle="tab">Document</a></li>
							</ul>
							<div class="tab-content"> 
								<div role="tabpanel" class="tab-pane active" id="fin_po_detail">
									<div class="col-md-12" id="mod_fin_po_comp_div_sec"></div>
								</div>
								<div role="tabpanel" class="tab-pane" id="fin_produ_detail">
									<div class="col-md-12" id="mod_fin_po_pro_div_sec"></div>
								</div>
								<div role="tabpanel" class="tab-pane" id="fin_produ_document">
									<div class="row">
										<div class="col-md-12">
											<div class="form-group" style="margin-top:20px;">
												<table class="display table table-bordered table-striped">
													<thead>
														<tr>
															<th width="40%" class="text-center">Document Name</th>
															<th width="50%" class="text-center">Upload Image</th>
															<th width="10%" class="text-center">Action</th>	
														</tr>
													</thead>
													<tbody>
														<tr>
															<td><input type="text" class="form-control" id="fin_doc_name" name="doc_name" placeholder="Document Name"></td>
															<td><input type="file" class="form-control" id="fin_doc_attach" name="doc_attach" ></td>
															<td><button type="button" class="btn btn-primary" id="fin_dfd_attch_btn" onclick="add_fin_document_attach()">Add</button></td>
														</tr>
													</tbody>
												</table>
											</div>
											<div class="form-group" style="margin-top:20px;" id="fin_po_doc_list"></div>
										</div>
									</div>
								</div>
							</div>
					</div>
					
					<div class="col-md-12" id="mod_fin_po_per_div_sec">
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
										<select class="select2" id="finance_po_approve_status" name="finance_po_approve_status">
											<option value="4" selected ="selected">Disapprove</option>
											<option value="1">Approve</option>
										</select>
									</td>
									<td>
										<textarea class="form-control" id="finance_po_approve_remark" name="finance_po_approve_remark" placeholder="Remark"></textarea>
									</td>
									<td>
										<input type="button" class="btn btn-primary" id="apprv_btn" onclick="add_finance_po_apprv_hist()" value="Add">
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="order-finance-po-history-datatable">
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
		<input type="hidden" id="fin_ref_ord_id" name="fin_ref_ord_id" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->