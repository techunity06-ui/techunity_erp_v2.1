<div class="modal colored-header info" id="preview_approval_hist_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Approval : #<span id="apprv_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
                    <form role="form" id="invoice_approve_add" action="javascript:;" method="post" name="invoice_approve_add" enctype="multipart/form-data">
                        <div class="col-md-12" id="mod_per_div_sec">
                            <div class="form-group">
                                <table class="display table table-bordered table-striped">
                                    <tr>
                                        <th width="20%">Status</th>
                                        <th width="45%">Remark</th>
                                        <th width="30%">Attachment</th>
                                        <th width="5%">Action</th>
                                    </tr>
                                    <tr>
                                        <!--<td>
                                            <select class="select2" id="assign_user_ids" name="assign_user_ids" placeholder="Choose Assign User">
                                                <?php //=get_assign_users($dbcon, '', " and user_id not in(".$_SESSION['user_id'].")");?>
                                            </select>
                                        </td>-->
                                        <td>
                                            <select class="select2" id="approve_status" name="approve_status">
                                                <option value="2">Reject</option>
                                                <option value="1">Approve</option>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea class="form-control" id="approve_remark" name="approve_remark" placeholder="Remark" maxlength="300"></textarea>
                                            <span id="rchars">300</span> Character(s) Remaining
                                        </td>
                                        <td>
                                            <input type="file" id="apprv_attachment" name="apprv_attachment[]" multiple>
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-success" id="apprv_btn">Add</button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <input type='hidden' name='mode' id='mode' value='add_apprv_hist' />
                        <input type="hidden" name="invoice_id" id="invoice_id"  value="" />
                    </form>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="invoice-apprv-history-datatable">
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

<script>
var maxLength = 300;
$('#approve_remark').keyup(function() {
        var textlen = maxLength - $(this).val().length;
        $('#rchars').text(textlen);
});
</script>