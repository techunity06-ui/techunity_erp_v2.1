<div class="modal colored-header info " id="ModalPaymentToGov" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="text-center">GST Payment Details</h3>
				
			</div>
			
			<div class="modal-body form">
					
				<div class="row">
					<div class="col-md-12">	

						<form class="form-horizontal" role="form" id="" action="javascript:;" method="" name="">
							<div class="gov_payment">
								
							</div>
						
							
							<div class="col-md-12" style="text-align:center;">
								<input type="button" id="add_gov_payment_btn" value="Add"  class="btn btn-primary" 
								onclick="add_payment_to_gov()" />
								<!-- <button type="button" class="btn btn-success" onclick="add_salesman_transaction()">Submit</button> --> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div>

							<!--Vendor row end-->	
							<input type='hidden' name='gov_payment_id' id='gov_payment_id' value='' />
							
					</form>
				</div>
			</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
