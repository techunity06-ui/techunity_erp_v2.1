
<div class="modal colored-header info " id="modal-tcs-details" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add TCS Details</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="20%" class="text-center">Lower Rate Applicable</th>
								<th width="25%" class="text-center">Reason For lower/non-deduction</th>
								<th width="20%" class="text-center">TCS Section</th>
								<th width="25%" class="text-center">Collection Code</th>
							</tr>
							<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
							<tr id="field1">
								
								<td style="vertical-align:top;">
									
									<select class="select2" name="tcs_lower_rate" id="tcs_lower_rate" required >
                    					<option value="1">No</option>
										<option value="2">Yes</option>
                					</select>			
								</td>	
								<td style="vertical-align:top;">
									<input type="text"  title="Reason" placeholder="Reason" id="tcs_lower_rate_reason" name="tcs_lower_rate_reason" class="form-control" value="<?php echo $tcsDetail[0]['tcs_lower_rate_reason']; ?>" />
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="TCS Section" placeholder="TCS Section" id="tcs_section" name="tcs_section" class="form-control" value="<?php echo $tcsDetail[0]['tcs_section']; ?>" />
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="Collection Code" placeholder="Collection Code" id="tcs_collection_code" name="tcs_collection_code" class="form-control" value="<?php echo $tcsDetail[0]['tcs_section']; ?>" />
								</td>
								
								<!-- <td style="vertical-align:top;"> 
									<input type="button"  name="addrow_currency" id="addrow_currency" onClick="return add_multi_currency_field();"  class="btn btn-primary" value="Add"/>	
								</td> -->
							</tr>
						</table>								
					</div>
				</div>
			</div>	
			<div class="row">
				<div class="col-md-12"><h3><strong>Tcs Table</strong></h3></div>
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="20%" class="text-center">Ref no</th>
								<th width="25%" class="text-center">Amt</th>
								<th width="20%" class="text-center">TCS.Col On</th>
								<th width="25%" class="text-center">Invoice Date</th>
							</tr>
							<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
							<tr id="field1">
								
								<td style="vertical-align:top;">
									<input type="text"  title="Ref no" placeholder="Ref no" id="tcs_ref_no" name="tcs_ref_no" class="form-control" value="<?php echo $tcsDetail[0]['tcs_ref_no']; ?>" readonly />
								</td>	
								<td style="vertical-align:top;">
									<input type="text"  title="Amount" placeholder="Amount" id="tcs_amt" name="tcs_amt" class="form-control" value="<?php echo $tcsDetail[0]['tcs_amt']; ?>" readonly />
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="TCS.Col On" placeholder="TCS.Col On" id="tcs_collected_on" name="tcs_collected_on" class="form-control tcs-date-picker" value="<?php echo $tcsDetail[0]['tcs_collected_on']; ?>" />
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="Invoice Date" placeholder="Invoice Date" id="tcs_invoice_date" name="tcs_invoice_date" class="form-control tcs-date-picker" value="<?php echo $tcsDetail[0]['tcs_invoice_date']; ?>" />
								</td>
								
								<!-- <td style="vertical-align:top;"> 
									<input type="button"  name="addrow_currency" id="addrow_currency" onClick="return add_multi_currency_field();"  class="btn btn-primary" value="Add"/>	
								</td> -->
							</tr>
						</table>								
					</div>
				</div>
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="20%" class="text-center">TCS %</th>
								<th width="20%" class="text-center">TCS Amt</th>
								<th width="20%" class="text-center">Sur %</th>
								<th width="20%" class="text-center">Sur Amt</th>
								<th width="20%" class="text-center">Total Tax</th>
							</tr>
							<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
							<tr id="field1">
								
								<td style="vertical-align:top;">
									<input type="text"  title="TCS %" placeholder="TCS %" id="tcs_percentage" name="tcs_percentage" class="form-control" value="<?php echo $tcsDetail[0]['tcs_percentage']; ?>" readonly />
								</td>	
								<td style="vertical-align:top;">
									<input type="text"  title="TCS Amt" placeholder="TCS Amt" id="tcs_amount" name="tcs_amount" class="form-control" value="<?php echo $tcsDetail[0]['tcs_amount']; ?>" readonly />
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="Sur %" placeholder="Sur %" id="tcs_sur_percentage" name="tcs_sur_percentage" class="form-control" value="<?php echo $tcsDetail[0]['tcs_sur_percentage']; ?>" />
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="Sur Amt" placeholder="Sur Amt" id="tcs_sur_percentage_amount" name="tcs_sur_percentage_amount" class="form-control" value="<?php echo $tcsDetail[0]['tcs_sur_percentage_amount']; ?>" />
								</td>
								<td style="vertical-align:top;">
									<input type="text"  title="Total Tax" placeholder="Total Tax" id="tcs_total_tax" name="tcs_total_tax" class="form-control" value="<?php echo $tcsDetail[0]['tcs_total_tax']; ?>" readonly />
								</td>
								
								<!-- <td style="vertical-align:top;"> 
									<input type="button"  name="addrow_currency" id="addrow_currency" onClick="return add_multi_currency_field();"  class="btn btn-primary" value="Add"/>	
								</td> -->
								
							</tr>
						</table>								
					</div>
				</div>

				<div class="col-md-12">
					<div class="col-md-5">
						<input type='hidden' name='edit_tcs_id' id='edit_tcs_id' value="<?php echo $tcsDetail[0]['tcs_deduct_id']; ?>" />
					</div>
					<div class="col-md-5">
						<input type="button"  name="add_tcs" id="add_tcs" onClick="return add_tcs_field();"  class="btn btn-primary" value="<?= !empty($tcsDetail[0]['tcs_deduct_id']) ? 'Update' : 'Add' ?>"/> &nbsp;
						<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</div>
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
