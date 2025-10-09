<div class="modal colored-header info" id="view_attach_document_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval1 : #<span id="ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group" style="margin-top:20px;">
							<table class="display table table-bordered table-striped">
								<thead>
									<tr>
										<th width="20%" class="text-center">Design Dept</th>
										<th width="30%" class="text-center">Document Name</th>
										<th width="40%" class="text-center">Upload Image</th>
										<th width="10%" class="text-center">Action</th>	
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<select class="select2" name="design_dept" id="design_dept">
												<option value="0">No</option>
												<option value="1">Yes</option>
											</select>
										</td>
										<td><input type="text" class="form-control" id="doc_name" name="doc_name" placeholder="Document Name"></td>
										<td><input type="file" class="form-control" id="doc_attach" name="doc_attach" ></td>
										<td><button type="button" class="btn btn-primary" id="dfd_attch_btn" onclick="add_document_attach()">Add</button></td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="form-group" style="margin-top:20px;" id="po_doc_list"></div>
					</div>
				</div>
			</div>	
		</div>
		<input type="hidden" id="eid" name="eid" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->