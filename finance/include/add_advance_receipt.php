<div class="modal colored-header info " id="ModalAdvancePayment" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Allocation of Advance with GST</h3>
				
			</div>
			<div class="col-md-12" style="background-color: #8293a2; font-size:14px;color: #ffffff; padding:10px; text-align: center;">
				Party Name : <span class="party_name"></span> <br>
				Region : <span class="party_region"></span>; Place of Supply : <span class="party_state"></span>(<span class="party_state_code"></span>)<br>
				Advance Amount to be allocated : Rs. <span class="adv_pay"></span> 
			</div>
			<div class="modal-body form">
				<div class="row">
					
					<div class="col-md-12">
						
						<form class="form-horizontal" role="form" id="" action="javascript:;" method="" name="">
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Ref No *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="trn_ref" id="trn_ref" readonly />
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Advance Amount *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="trn_amount" id="trn_amount" readonly />
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate (%) *</label>
								<div class="col-md-12 col-xs-11">
									<input type="number" class="form-control numbersOnly" name="trn_gst" id="trn_gst" onkeyup="calculate_tax(this.value);" max="100" />
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Taxable Amount *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="taxable_amt" id="taxable_amt" readonly  />
								</div>
							</div>

							<div class="gst">
								
							</div>
							
							<!-- <div class="col-md-6">
								<div class="form-group" id="comm_per_div">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate CGST *</label>
									<div class="col-md-12 col-xs-11">
										<input type="text" class="form-control" name="sales_comm_percentage" id="sales_comm_percentage" onkeyup="set_salesman_percentage(this.value)" readonly />
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate SGST *</label>
									<div class="col-md-12 col-xs-11">
										<input type="text" class="form-control" name="sales_comm_percentage" id="sales_comm_percentage" onkeyup="set_salesman_percentage(this.value)" readonly />
									</div>
								</div>
							</div> -->
							
							<div class="col-md-12" style="text-align:center;">
								<input type="button" id="add_adv_payment_btn" value="Add"  class="btn btn-primary" onclick="add_adv_payment()" />
								<!-- <button type="button" class="btn btn-success" onclick="add_salesman_transaction()">Submit</button> --> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div></div>
							
							<!--Vendor row end-->	
							<input type='hidden' name='adv_payment_id' id='adv_payment_id' value='' />
							
					</form>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
