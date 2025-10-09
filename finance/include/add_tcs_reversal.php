<!-- Modal Cost Center-->
<div class="modal colored-header info" id="ModalTcsReversal" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>TCS Reversal Details</h3>
			
		</div>
		<div class="modal-body form">
			
		<div class="row">
		
			<div class="col-md-12 margin_row">
			
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">TCS Section</label>
						<input type="text" class="form-control" name="tcs_section" id="tcs_section" value="206C" />
					</div>	
				</div>
				
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Sub Category Code</label>
						<input type="text" class="form-control" name="tcs_sub_cat_code" id="tcs_sub_cat_code" value="" />
					</div>	
				</div>
				
			</div>
			
		</div>
		
		<div class="row">
			<div class="col-md-12 margin_row">
				<div class="adv-table">
					<table class="table table-bordered">
						<tr>
							<th>Ref No</th> 
							<th>Amt Reversed</th> 
							<th>TCS Col. On</th> 
							<th>TCS Amt</th> 
							<th>Sur Amt</th> 
							<th>Total Tax</th> 
							<th class="hidden-phone">Action</th>					  
						</tr>
						<tr>
							<th>
								<select class="select2" name="ref_id" id="ref_id">
									<option>--Select Reference No--</option>
									
								</select>
							</th>
							<th>
								<input type="text" class="form-control" name="amt_reversed" id="amt_reversed" onkeypress="return isNumberKey(event)" />
							</th>
							<th>
								<input type="text" class="form-control default-date-picker" name="tcs_collected_on" id="tcs_collected_on"  />
							</th>
							<th>
								<input type="text" class="form-control" name="tcs_amt" id="tcs_amt" onkeypress="return isNumberKey(event)" />
							</th>
							<th>
								<input type="text" class="form-control" name="sur_amt" id="sur_amt" onkeypress="return isNumberKey(event)" />
							</th>
							<th>
								<input type="text" class="form-control" name="total_tax" id="total_tax" onkeypress="return isNumberKey(event)" />
							</th>
							<th>
								<input type="button" class="btn btn-primary" id="add_reversal_btn" value="ADD" onclick="add_tcs_reversal_trn()" />
							</th>
						</tr>
					</table>
					<table class="display table table-bordered table-striped" id="tcs-trn-table">
						<thead>
							<tr>
								<th>Sr. NO.</th>
								<th>Ref No</th> 
								<th>Amt Reversed</th> 
								<th>TCS Col. On</th> 
								<th>TCS Amt</th> 
								<th>Sur Amt</th> 
								<th>Total Tax</th> 
								<th class="hidden-phone">Action</th>					  
							</tr>
							
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<div class="row">
			
			<div class="col-md-12 margin_row">
				<div class="col-md-3">
					<input type="hidden" class="form-control" id="edit_id_tcs_reversal" name="" value="" />
					<input type="hidden" class="form-control" id="edit_id_tcs_reversal_main" name="" value="" />
					<input type="button" id="add_tcs_reversal_btn" value="Submit"  class="btn btn-primary" onclick="add_tcs_reversal()" />
				</div>
			</div>
			
		</div>
		
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->