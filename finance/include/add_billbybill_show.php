<!-- Modal Bill By Bill Adjustment  -->
<div class="modal colored-header info" id="ModalBillAdjustment" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Bill By Bill Adjustment <span class="cust_name"></span></h3>
			
		</div>
		<div class="modal-body form">	
		
		<div class="row">
			<div class="col-md-12">
				
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Method</label>
						<select class="form-control" name="bill_method" id="bill_method" onChange="get_bill_by_method(this.value);" >
							<option value="">--Select Method--</option>
							<option value="1">Append</option>
							<option value="2">New Ref.</option>
						</select>
					</div>	
				</div>

				<div class="col-md-3">
					<div class="form-group bill_append" style="display:none" >
						<label for="edit_zone_name">Ref.</label>
						<select class="form-control" name="bill_ref" id="bill_ref" onChange="get_due_amount(this.value);" >
							
						</select>
						<input type="hidden" class="form-control" name="cust_ledger_id" id="cust_ledger_id" />
					</div>

					<div class="form-group bill_ref_no" style="display:none" >
						<label for="edit_zone_name">Ref.</label>
						<input type="text" class="form-control" name="bill_ref_manual" id="bill_ref_manual" value="" readonly>
					</div>

				</div>

				<div class="col-md-3 bill_ref_no_type" style="display:none" >
					<div class="form-group " >
						<label for="edit_zone_name">Type.</label>
						<select class="form-control" id="new_ref_type">
							<option value=''>--Select Reference Type--</option>
							<option value="2">Payment</option>
							<option value="0">Receipt</option>
						</select>
					</div>
				</div>
				
				<div class="col-md-3 due_amt_div">
					<div class="form-group">
						<label for="edit_zone_name">Due Amount</label>
						<input type="number" min="0" class="form-control" name="bill_amt" id="bill_amt" readonly />
						<strong id="billamt_error_id" class="common_form_error"></strong>
					</div>	
				</div>

				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Paid Amount</label>
						<input type="number" min="0" class="form-control numbersOnly" name="paid_amt" id="paid_amt" />
						<strong id="billpaid_error_id" class="common_form_error"></strong>
					</div>	
				</div>
				
				<!-- <div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">D/C</label>
						<select class="form-control" name="bill_entry_type" id="bill_entry_type">
							<?php 
								$balance_type=getbalance_type_new($dbcon,"");
								echo $balance_type;
							?>
						</select>
						<strong id="billentry_error_id" class="common_form_error"></strong>
					</div>	
				</div> -->
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Due Date</label>
						<input type="text" class="form-control default-date-picker" name="bill_due_date" id="bill_due_date" value="<?=date("d-m-Y");?>" required title="Due Date" />
						<strong id="bill_due_date_error" class="common_form_error"></strong>
					</div>	
				</div>
				<div class="col-md-3">
					<div class="form-group">
						<input type="hidden" class="form-control" name="bill_type_hid" id="bill_type_hid" />
						<input type="hidden" class="form-control" name="jv_hid" id="jv_hid" />
						<input type="button" id="add_bill_adjustment_btn" value="Add"  class="btn btn-primary" onclick="add_bill_show()" 
						style="margin-top: 24px;"  tabindex="" />
					</div>
				</div>
			</div>
		</div>
		
		<!-- <div class="row margin_row">
			
			
		</div> -->
		
		<div class="row margin_row">
			<!-- <div class="col-md-12 ">
				
			</div> -->
			
			<div class="extra_data">
								
				<input type="hidden" name="bill_adjust_voucher_type" id="bill_adjust_voucher_type" placeholder="Voucher Type eg. sale , purchase">
				<input type="hidden" name="bill_adjust_ledger_id" id="bill_adjust_ledger_id" placeholder="Ledger Id">
				<input type="hidden" name="bill_adjust_table" id="bill_adjust_table" placeholder="table name of sale , purchase , payment..">
				<input type="hidden" name="bill_adjust_table_id" id="bill_adjust_table_id" placeholder="primary key of that inserted table ">
				<input type="hidden" id="edit_id_bill_popup" value="" />
			</div>
		</div>
		
		<div class="row margin_row">
			<div class="col-md-12 ">
				<div class="adv-table">
					<table class="display table table-bordered table-striped" id="billbybill-table">
						<thead>
							<tr>
								<th>Sr. NO.</th>
								<th>Bill Ref</th> 
								<th>Bill Amount</th> 
								<th>Bill Entry Type</th> 
								<th>Bill Due Date</th> 
								
								<th class="hidden-phone">Action</th>					  
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->