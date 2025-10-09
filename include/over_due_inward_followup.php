<div class="modal colored-header info" id="over_due_followup" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>PO : #<span id="po_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="mod_po_comp_div_sec"></div>
					<div class="col-md-12" id="mod_po_per_div_sec">
						<div class="form-group">
							<table class="display table table-bordered table-striped">
								<tr>
									<th width="25%">Follow-Up Date</th>
									<th width="50%">Remark</th>
									<th width="5%">Action</th>
								</tr>
								<tr>
									<td>
										<div data-date class="input-group date form_datetime-meridian">
											<input type="text" class="form-control" id="folloup_date" name="folloup_date" value="" required autocomplete="off">
											<div class="input-group-btn">
												<button type="button" class="btn btn-info date-set">
													<i class="fa fa-calendar"></i>
												</button>
											</div>
										</div>
									</td>
									<td>
										<textarea class="form-control" id="po_follow_remark" name="po_follow_remark" placeholder="Remark" required></textarea>
									</td>
									<td>
										<input type="button" class="btn btn-primary" id="apprv_btn" onclick="po_follow_up_add()" value="Add">
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="po-followup-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>Follow-Up Date</th>
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
		<input type="hidden" id="po_id" name="po_id" value="">
		<input type="hidden" id="delever_id" name="delever_id" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->