<div class="modal colored-header info " id="bs-payterms-modal-lg" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Payment Terms</h3>
				
			</div>
			<div class="modal-body form">
			<div class="row">

			<div class="col-md-12">
			
			<form class="form-horizontal" role="form" id="payment" action="javascript:;" method="post" name="payment">
				
				<div class="col-md-12">
			      <div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Payment Terms</label>
						<div class="col-md-12 col-xs-11">
							<!--<input type="text" class="form-control" placeholder="Payment Terms" name="payment_terms" id="payment_terms"  title="Payment Terms"/>-->
							 <input type="text" class="form-control" id="payment_terms1" name="payment_terms" placeholder="Payment Tearms">
						</div>
						<div class="col-md-12">
						<div class="form-group">
								  <label for="payment Days" class="col-md-12 control-label" style="text-align:left;line-height:25px">Payment Days</label>
								  <div class="col-md-12 col-xs-11">
								  <input type="number" class="form-control" id="payment_days" name="payment_days" placeholder="Payment Days">
							  </div>
						</div>
					 </div>	
				</div>
				</div>
				<div class="col-md-3"></div>
							<button type="submit" class="btn btn-success">Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
					
					<!--Vendor row end-->	
							<input type='hidden' name='paymode' id='paymode' value='Add' />
							<input type='hidden' name='model' id='model' value='model' />
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							
						  </form>
				</div>
			</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
