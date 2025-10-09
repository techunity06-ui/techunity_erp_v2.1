<div class="modal colored-header info" id="final_dispatch_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Invoice No : <span id="apprv_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
                    <form role="form" id="final_dispatch_add" action="javascript:;" method="post" name="final_dispatch_add" enctype="multipart/form-data">
                        <div class="col-md-12" id="mod_per_div_sec">
                            <div class="form-group">
                                <table class="display table table-bordered table-striped">
                                    <tr>
                                        <th width="20%">Final Dispatch</th>
                                        <th width="45%">Remark</th>
                                        <th width="30%">Attachment</th>
                                        <th width="5%">Action</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select class="select2" id="final_dispatch_status" name="final_dispatch_status">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea class="form-control" id="final_dispatch_remark" name="final_dispatch_remark" placeholder="Remark"></textarea>
                                        </td>
                                        <td>
                                            <input type="file" id="final_dispatch_attachment" name="final_dispatch_attachment">
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-success" id="apprv_btn">Add</button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <input type='hidden' name='mode' id='mode' value='final_dispatch' />
                        <input type="hidden" name="invoi_id" id="invoi_id"  value="" />
                    </form>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="dispatch-history-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>User</th>
										<th>Status</th>
										<th>Remark</th>
										<th>Date</th>
                                        <th>Attachments</th>
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
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
