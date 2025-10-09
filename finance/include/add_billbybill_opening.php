<div class="modal colored-header info " id="modal-bill-by-bill" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Bill By Bill Opening Balance</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="18%" class="text-center">Ref/Bill No</th>
								<th width="18%" class="text-center">Date</th>
								<th width="18%" class="text-center">Amount</th>
								<th width="18%" class="text-center">C/D</th>
								<th width="18%" class="text-center">Due Date</th>
								<th width="10%" class="text-center"></th>
							</tr>
							<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
							<tr id="field1">
								
								<td style="vertical-align:top;">
									<input type="text"  title="Ref/Bill No" placeholder="Ref/Bill No" id="bill_ref_no" name="bill_ref_no" class="form-control"/>
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="Date" placeholder="Date" id="bill_opening_date" name="bill_opening_date" class="form-control default-date-picker"/>
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="Amount" placeholder="Amount" id="bill_amount" name="bill_amount" class="form-control" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								</td>
								<td style="vertical-align:top;">
									<select class="select2" name="bill_entry_type" id="bill_entry_type">
										<?php echo getbalance_type_new($dbcon,""); ?>
									</select>
									
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="Bill Due Date" placeholder="Bill Due Date" id="bill_due_date" name="bill_due_date" class="form-control default-date-picker"/>
								</td>	
								<td style="vertical-align:top;"> 
									<input type="button"  name="addrow_billbybill" id="addrow_billbybill" onClick="return add_billbybill_field();"  class="btn btn-primary" value="Add"/>	
								</td>
								<input type='hidden' name='edit_bill_id' id='edit_bill_id' value='' />
							</tr>
						</table>								
					</div>
				</div>
			</div>	
			<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="billbybill_table">							
								<thead>
									<tr>
										<th>Sr No.</th>
										<th>Ref/Bill No</th>
										<th>Date</th>
										<th>Amount</th>
										<th>C/D</th>
										<th>Due Date</th>
										<th class="hidden-phone">Action</th>					  
									</tr>
								</thead>
								<tbody>
								</tbody>
								<tfoot>
									<tr><th colspan="3" style="text-align: right;">Total</th><th><span id="billbybill_total"></span><span id="billbybill_total_type"></span></th></tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
