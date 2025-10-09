<div class="modal colored-header info" id="full_po_shortclose_reason" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval : # <span id="shortclose_pofull_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="po_company_detail"></div>
					<div class="col-md-12"><h2 style="text-align:center"><u>Full Po Short Close</u></h2></div>
					<div class="col-md-12" id="mod_po_per_div_sec">
						<div class="form-group">
							<table class="display table table-bordered table-striped">
								<tr>
									<th width="45%">Remark</th>
									<th width="5%">Action</th>
								</tr>
								<tr>
									<td>
										<textarea class="form-control" id="po_close_reson" name="po_close_reson" placeholder="Remark"></textarea>
									</td>
									<td>
										<input type="button" class="btn btn-primary" id="apprv_btn" onclick="add_full_poshort_close()" value="Add">
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div id="f_po_close_reason">
							
						</div>
						</div>
					</div>
				</div>
			</div>	
		</div>
		<input type="hidden" id="ref_pord_id" name="ref_pord_id" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
