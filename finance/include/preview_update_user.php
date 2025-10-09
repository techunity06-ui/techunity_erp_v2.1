<div class="modal colored-header info" id="preview_user_update" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Update User : #<span id="ref_mod_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="mod_po_per_div_sec">
						<div class="form-group">
							<table class="display table table-bordered table-striped">
								<tr>
									<!--<th width="30%">Assign User</th>-->
									<th width="20%">Users</th>
									<th width="45%">Remarks</th>
									<th width="5%">Action</th>
								</tr>
								<tr>
									<td>
										<select class="select2" name="updated_user_id" id="updated_user_id" onchange="show_data()">
											<option value="">Select User</option>
											<?=get_assign_users($dbcon,'', " and user_type in(".$crm_user_type.")");?>
										</select>
									</td>
									<td>
										<textarea class="form-control" id="user_update_remark" name="user_update_remark" placeholder="Remark"></textarea>
									</td>
									<td>
										<input type="button" class="btn btn-primary" id="user_upd" onclick="user_update()" value="Add">
										<!-- <input type="button" class="btn btn-primary add_oa_apprv_hist" id="apprv_btn" onclick="add_po_apprv_hists()" value="Add"> -->
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="update_user_log_history">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>User</th>
										<th>Remark</th>
										<th>Date</th>					  
										<th>Login User</th>					  
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
		<input type="hidden" id="ref_mod_id" name="ref_mod_id" value="">
		<input type="hidden" id="preview_user" name="preview_user" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->