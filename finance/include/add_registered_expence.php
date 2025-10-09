<div class="modal colored-header info " id="ModalRegisteredExpence" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="text-center">Registered Expense</h3>
				
			</div>
			<div class="col-md-12" style="background-color: #8293a2; font-size:14px;color: #ffffff; padding:10px; text-align: center;">
				Expense Account Name : <span class="party_name"></span> <br>
				Expense Amount to be Adjusted : <span class="adj_amount"></span><br>
				
			</div>
			<div class="modal-body form">
					
				<div class="row">
					<div class="col-md-12">	

						<form class="form-horizontal" role="form" id="" action="javascript:;" method="" name="">
						<div class="party_det">
							
						</div>
						<div class="other_details" style="padding: 10px;margin: 15px;">
							
						</div>
							
							<div class="col-md-12" style="text-align:center;">
								<input type="button" id="add_registered_expense_btn" value="Add"  class="btn btn-primary" 
								onclick="add_registered_expense()" />
								<!-- <button type="button" class="btn btn-success" onclick="add_salesman_transaction()">Submit</button> --> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div>

							<!--Vendor row end-->	
							<input type='hidden' name='registered_expense_id' id='registered_expense_id' value='' />
							
					</form>
				</div>
			</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
