<div class="modal colored-header info " id="ModalClearEntry" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Clear Entry</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
					
					<div class="col-md-12">
						
						<form class="form-horizontal" role="form" id="" action="javascript:;" method="" name="">

							<div class="col-md-12">
								<div class="form-group">
									<label for="edit_zone_name">Clear Date</label>
									<input type="text" class="form-control default-date-picker" name="clear_date" id="clear_date" value="<?= date('d-m-Y'); ?>" />
								</div>	
							</div>

							<div class="col-md-12">
								<div class="form-group">
									<label for="edit_zone_name">Clear Full Voucher</label>
									<select class="form-control" id="clear_full_voucher" name="clear_full_voucher">
										<option value="">--Clear Full Voucher--</option>
										<option value="1" selected>Y</option>
										<option value="0">N</option>
									</select>
								</div>
							</div>
							
							<div class="col-md-12" style="text-align:center;">
								<input type="button" id="add_cre_deb_note_btn" value="Clear"  class="btn btn-primary" onclick="clear_entry_with_date()" /> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div></div>
							
							<!--Vendor row end-->	
							<input type='hidden' name='general_book_id' id='general_book_id' value='' />
							
					</form>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
