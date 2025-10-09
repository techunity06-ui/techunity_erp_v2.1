<?php //$sundryDetails = getAddedBillSundry($dbcon);  ?>
<div class="modal colored-header info " id="modal-sales-order" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add/Update Sales order selection</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-6"><label><strong>Transaction Type</strong></label></div>
						<div class="col-md-6" >
							<select class="form-control" name="transaction_type" id="transaction_type" onchange="get_sales_order_details(this.value)">
								<option value="0">--Select Type--</option>
								<option value="1">Sales order wise</option>
								<option value="2">Allocation wise</option>
								<?php if($company_config['packing_module']==1){ ?>
									<option value="3">Packing wise</option>
								<?php } ?>
								
							</select>
						</div>
					</div><br><br><br>
					<div class="col-md-12">
						<div class="form-group">
							<div class="col-md-12">
								<div class="so_orders">
									
								</div>								
								<div class="form-group col-md-5">
									<button type="button" class="btn btn-info form-control" onClick="add_sales();" id="add" style="margin-left:100px;">Add</button>
								</div>					
							</div>									
						</div>
					</div>
				</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
