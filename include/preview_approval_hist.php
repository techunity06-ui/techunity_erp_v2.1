<div class="modal colored-header info" id="preview_approval_hist_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval : #<span id="apprv_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="mod_per_div_sec">
						<div class="form-group">
							<table class="display table table-bordered table-striped">
								<tr>
									<!--<th width="30%">Assign User</th>-->
									<th width="20%">Status</th>
									<th width="45%">Remark</th>
									<th width="5%">Action</th>
								</tr>
								<tr>
									<!--<td>
										<select class="select2" id="assign_user_ids" name="assign_user_ids" placeholder="Choose Assign User">
											<?//=get_assign_users($dbcon, '', " and user_id not in(".$_SESSION['user_id'].")");?>
										</select>
									</td>-->
									<td>
										<select class="select2" id="approve_status" name="approve_status">
											<option value="0">Reject</option>
											<option value="1">Approve</option>
										</select>
									</td>
									<td>
                                                                            <textarea class="form-control" id="approve_remark" name="approve_remark" placeholder="Remark" maxlength="300"></textarea>
                                                                            <span id="rchars">300</span> Character(s) Remaining
									</td>
									<td>
										<input type="button" class="btn btn-primary" id="apprv_btn" onclick="add_apprv_hist()" value="Add">
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="sales-order-history-datatable">
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
		<input type="hidden" id="ref_sales_order_id" name="ref_sales_order_id" value="">
		<input type="hidden" id="ref_quotation_id" name="ref_quotation_id" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
var maxLength = 300;
$('#approve_remark').keyup(function() {
        var textlen = maxLength - $(this).val().length;
        $('#rchars').text(textlen);
});
</script>