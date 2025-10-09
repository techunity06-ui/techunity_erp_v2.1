<!-- Modal Bill By Bill Adjustment  -->
<div class="modal colored-header info" id="ModalAdvancePymentTds" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>TDS Calculation</h3>
			
		</div>
		<div class="modal-body form">
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
							
							<tr id="field1"  class="it_act_trns">
								<td>
									<label class="form-group">TDS Category</label>
								</td>
								<td style="vertical-align:top;">
									<select class="form-control" name="tds_cat" id="tds_cat" onChange="get_details(this.value);" >
										
									</select>
									
								</td>
							</tr>
							
							
						</table>	
						<input type="hidden" class="form-control" id="paid_amount_tds" />	
						<input type="hidden" class="form-control" id="paid_amount_cust" />
						<input type="hidden" class="form-control" value="" id="entrytype" title="1-for-payment_2-for-journal" />							
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->