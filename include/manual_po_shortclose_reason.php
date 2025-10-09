<div class="modal colored-header info" id="manual_po_shortclose_reason" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval : # <span id="shortclose_poman_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="po_comp_detail"></div>
					
					<div class="col-md-12">
						<h3 style="text-align:center"><u>Product Wise Po Short Close</u></h3>
					</div>
					<div class="col-md-12" id="mod_po_per_div_sec">
						<div class="form-group">
							<table class="display table table-bordered table-striped">
								<tr>
									<th width="45%">Remark</th>
									<th width="5%">Action</th>
								</tr>
								<tr>
									<td>
										<textarea class="form-control" id="m_close_remark" name="m_close_remark" placeholder="Remark"></textarea>
									</td>
									<td>
										<input type="button" class="btn btn-primary" id="apprv_btn" onclick="add_manualpo_short_close()" value="Add">
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div id="m_po_close_reason">
							
						</div>
						</div>
					</div>
					<div class="col-md-12">
						<div id="po_trn_tbl"></div>
					</div>
				</div>
			</div>	
		</div>
		<input type="hidden" id="ref_po_ref_id" name="ref_po_ref_id" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
