<div class="modal colored-header info " id="ModalDebitCreditNote" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Adjustment of Debit Note Credit Note</h3>
				
			</div>
			<div class="col-md-12" style="background-color: #8293a2; font-size:14px;color: #ffffff; padding:10px; text-align: center;">
				Party Name : <span class="party_name"></span> <br>
				Region : <span class="party_region"></span>; Place of Supply : <span class="party_state"></span>(<span class="party_state_code"></span>)<br>
				
			</div>
			<div class="modal-body form">
				<div class="row">
					
					<div class="col-md-12">
						
						<form class="form-horizontal" role="form" id="" action="javascript:;" method="" name="">

							<div class="deb_cre_details">
								
							</div>
							
							<div class="col-md-12" style="text-align:center;">
								<input type="button" id="add_cre_deb_note_btn" value="Add"  class="btn btn-primary" onclick="add_cre_deb_note()" /> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div></div>
							
							<!--Vendor row end-->	
							<input type='hidden' name='deb_cre_adjustment_id' id='deb_cre_adjustment_id' value='' />
							<input type="hidden" name="isinterstate" id="isinterstate" value="">
							
					</form>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
