<?php $dep = getAddedDepreciation($dbcon);  ?>
<div class="modal colored-header info " id="modal-depreciation" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add/Update Depreciation</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
							
							<tr>
								<th colspan="2">
									<input type="checkbox" name="" id="it_act_check" value="0" onclick="get_depreciation_it_act(this.value)" /> Click to Enter Depreciation Per Income Tax Act 
								</th>
							</tr>
							
							<tr class="it_act">
								<th colspan="2" style="background-color:#337AB7;color:#FFFFFF;text-align:center">According To Income Tax Act</th>
							</tr>
							
							<tr id="field1"  class="it_act">
								<td>
									<label class="form-group">Annual Rate of Depreciation</label>
								</td>
								<td style="vertical-align:top;">
									<input type="text"  placeholder="Annual Rate of Depreciation" id="depreciate_annual_rate" value="<?=$dep['depreciate_annual_rate']?>" name="depreciate_annual_rate" class="form-control" required onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
								</td>
							</tr>
							<tr  class="it_act">
								<td>
									<label class="form-group">Half year rate of depreciation</label>
								</td>
								<td style="vertical-align:top;"> 
									<input type="text" placeholder="Half year rate of depreciation" value="<?=$dep['depreciate_half_rate']?>" name="depreciate_half_rate" id="depreciate_half_rate"  class="form-control" required  onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />	
								</td>
							</tr>
							<tr  class="it_act">
								<td>
									<label class="form-group">Opening Balance</label>
								</td>
								<td style="vertical-align:top;"> 
									<input type="text" placeholder="Opening Balance" value="<?=$dep['depreciate_opening']?>" name="depreciate_opening" id="depreciate_opening"  class="form-control" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" required />	
								</td>
							</tr>
							
							<tr class="company_act">
								<th colspan="2"  style="background-color:#337AB7;color:#FFFFFF;text-align:center">According To Company's Act</th>
							</tr>
							
							<tr  class="company_act">
								<td>
									<label class="form-group">Rate Of Depreciation(w.d.v)</label>
								</td>
								<td style="vertical-align:top;"> 
									<input type="text" placeholder="Rate Of Depreciation(w.d.v)" value="<?=$dep['depreciate_rate_wdv']?>" name="depreciate_rate_wdv" id="depreciate_rate_wdv"  class="form-control" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" required />	
								</td>
							</tr>
							
							<tr>	
								<td style="vertical-align:top; text-align: center;" colspan="2"> 
									<input type="button"  name="add_depreciation" id="add_depreciation" onClick="return add_depreciation_field();"  class="btn btn-primary" value="<?= !empty($dep['depreciate_id']) ? 'Update' : 'Add' ?>"/>	
								</td>
								<input type='hidden' name='edit_dep_id' id='edit_dep_id' value='<?=$dep['depreciate_id']?>' />
								<input type='hidden' name='edit_ledger_id' id='edit_ledger_id' value='<?=$dep['depreciate_ledger_id']?>' />
							</tr>
						</table>								
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
