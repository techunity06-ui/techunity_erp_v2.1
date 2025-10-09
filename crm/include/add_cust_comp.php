<div class="modal colored-header info " id="modal-cust-company" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Customer To Company</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<div class="col-md-12">
								<div class="cust_company">
									<select class="select2" name="crm_cust_id" id="crm_cust_id" >
										<?=getcustomer($dbcon,$edit_customer_id='',1);?>
									</select>
								</div>

								<div class="form-group col-md-5" style="margin-top:50px">
									<button type="button" class="btn btn-info form-control" onClick="add_customer_to_company();" style="margin-left:100px;">Add</button>
								</div>					
							</div>									
						</div>
					</div>
				</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>