<div class="modal colored-header info " id="modal-bank-cheque" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg xlg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Bank Cheque</h3>				
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
								<tr id="field">
									<th width="10%" class="text-center">Date</th>
									<th width="12%" class="text-center">Voucher No</th>
									<th width="12%" class="text-center">Account</th>
									<th width="12%" class="text-center">Amount</th>
									<th width="12%" class="text-center">Pay Mode</th>
									<th width="12%" class="text-center">Transaction Number</th>
									<th width="12%" class="text-center">Narration</th>
									<th width="12%" class="text-center">Type</th>
									<th width="4%" class="text-center"></th>
								</tr>
								<tr id="field1">
									
									<td style="vertical-align:top;">
										<input type="text"  title="Cheque Date" placeholder="Cheque Date" id="cheque_date" name="cheque_date" class="form-control default-date-picker"/>
										
									</td>	
									<td style="vertical-align:top;">
										<input type="text"  title="Voucher No" placeholder="Voucher No" id="cheque_voucher_no" name="cheque_voucher_no" class="form-control"/>
										
									</td>
									<td style="vertical-align:top;">
										<select class="select2" name="cheque_account" id="cheque_account">
											<?php echo getledger($dbcon); ?>
										</select>										
									</td>
									<td style="vertical-align:top;">
										<input type="text" placeholder="Cheque Amount" id="cheque_amount" name="cheque_amount" onkeypress="return isNumberKey(event)" class="form-control"/>
									</td>
									<td style="vertical-align:top;">
										<select class="select2" name="cheque_pay_mode" id="cheque_pay_mode">
											<?php echo getpaymentmode($dbcon, $branch_id); ?>
										</select>
									</td>
									<td style="vertical-align:top;">
										<input type="text" placeholder="Transaction Number" id="cheque_transaction_number" name="cheque_transaction_number" class="form-control"/>
									</td>
									<td style="vertical-align:top;">
										<textarea placeholder="Cheque Narration" id="cheque_narration" name="cheque_narration" class="form-control" rows="3"></textarea>
										
									</td>
									<td style="vertical-align:top;">
										<select class="select2" name="cheque_entry_type" id="cheque_entry_type">
											<option value="">--Select Entry Type--</option>
											<option value="1" >Deposit</option>
											<option value="2" >Issued</option>
										</select>
										
									</td>
									<!-- <td style="vertical-align:top;">
										<select class="select2" name="cheque_status" id="cheque_status">
											<?php // echo get_branch_name_company($dbcon, $branch_id); ?>
										</select>										
									</td> -->									
									<td style="vertical-align:top;"> 
										<input type="button"  name="addbank_cheque" id="addbank_cheque" onClick="return add_bank_cheque_field();"  class="btn btn-primary" value="Add"/>	
									</td>
									<input type='hidden' name='edit_id' id='edit_id' value='' />
								</tr>
							</table>								
						</div>
					</div>
				</div>	
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<div class="adv-table">
								<table  class="display table table-bordered table-striped" id="bank_cheque_table">							
									<thead>
										<tr>
											<th>Sr. NO.</th>
											<th>Date</th>
											<th>Voucher No</th>
											<th>Account</th>
											<th>Amount</th>
											<th>Pay Mode</th>
											<th>Transaction Number</th>
											<th>Narration</th>
											<th>Type</th>
											<th class="hidden-phone">Action</th>					  
										</tr>
									</thead>
									<tbody>
									</tbody>
									<tfoot>
										<tr><th colspan="8" style="text-align: right;">Deposite Total</th><th><span id="depo_total"></span></th></tr>
										<tr><th colspan="8" style="text-align: right;">Issued Total</th><th><span id="issued_total"></span></th></tr>
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
