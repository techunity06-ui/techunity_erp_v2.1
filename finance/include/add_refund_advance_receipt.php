<div class="modal colored-header info " id="ModalAdvanceRefundPayment" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Refund of Advance with GST</h3>
				
			</div>
			<div class="col-md-12" style="background-color: #8293a2; font-size:14px;color: #ffffff; padding:10px; text-align: center;">
				Party Name : <span class="party_name"></span> <br>
				Region : <span class="party_region"></span>; Place of Supply : <span class="party_state"></span>(<span class="party_state_code"></span>)<br>
				
			</div>
			<div class="modal-body form">
				<div class="row">
					
					<div class="col-md-12">
						
						<form class="form-horizontal" role="form" id="" action="javascript:;" method="" name="">
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Ref No *</label>
								<div class="col-md-12 col-xs-11">
									<select class="select2" name="ref_no" id="ref_no" onchange="get_adv_refund_payment_details(this.value,'');">
										<option value="0">--Select Ref. No.--</option>
									</select>
								</div>
							</div>

							<div class="ref_details">
								
							</div>
							
							<div class="col-md-12" style="text-align:center;">
								<input type="button" id="add_adv_refund_payment_btn" value="Add"  class="btn btn-primary" onclick="add_refund_adv_payment()" />
								<!-- <button type="button" class="btn btn-success" onclick="add_salesman_transaction()">Submit</button> --> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div></div>
							
							<!--Vendor row end-->	
							<input type='hidden' name='adv_refund_payment_id' id='adv_refund_payment_id' value='' />
							
					</form>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
