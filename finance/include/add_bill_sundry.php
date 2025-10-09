<?php $sundryDetails = getAddedBillSundry($dbcon);  ?>
<div class="modal colored-header info " id="modal-bill-sundry" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add/Update Bill Sundry</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<div class="form-group">


						<div class="col-md-12">								
							<form class="form-horizontal" role="form" id="common_category_add" action="javascript:;" method="post" name="common_category_add">
							
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Bill Sundry Type *</label>
										<div class="col-md-12 col-xs-11">
											<select class="form-control" name="sundry_type" id="sundry_type" onchange="" required >
												<option value="">--Select--</option>
												<option value="1" <?php if($sundryDetails['sundry_type']=='1'){ echo "selected"; } ?> >Additive</option>
												<option value="2" <?php if($sundryDetails['sundry_type']=='0'){ echo "selected"; } ?> >Substractive</option>		
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Bill Sundry Nature</label>
										<div class="col-md-12 col-xs-11">
											<select class="form-control" name="sundry_nature" id="sundry_nature" onchange="" >
												<?=get_ledger($dbcon,isset($sundryDetails['sundry_nature']) ? $sundryDetails['sundry_nature'] : '','and l_group = 31');?>		
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Default Value</label>
										<div class="col-md-12 col-xs-11">
											<input type="text" class="form-control" required="" minlength="2" placeholder="Default Value" name="sundry_default_value" id="sundry_default_value" value='<?= $sundryDetails['sundry_default_value'] ?>'  />
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Amount of bill sundry</label>
										<div class="col-md-12 col-xs-11">
											<select class="form-control" name="sundry_amount_of" id="sundry_amount_of" onchange="" >
												<option value="">--Select--</option>
												<option value="1" <?php if($sundryDetails['sundry_amount_of']=='1'){ echo "selected"; } ?> >Ansolute</option>
												<option value="2" <?php if($sundryDetails['sundry_amount_of']=='2'){ echo "selected"; } ?> >Percentage</option>		
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Calculate Bill Sundry On</label>
										<div class="col-md-12 col-xs-11">
											<select class="form-control" name="sundry_calculate_on" id="sundry_calculate_on" onchange="" >
												<option value="">--Select--</option>
												<option value="1" <?php if($sundryDetails['sundry_calculate_on']=='1'){ echo "selected"; } ?> >Net bill amount</option>
												<option value="2" <?php if($sundryDetails['sundry_calculate_on']=='2'){ echo "selected"; } ?> >Basic amount</option>
												<option value="3" <?php if($sundryDetails['sundry_calculate_on']=='3'){ echo "selected"; } ?> >Taxable amount</option>		
											</select>
										</div>
									</div>
									<div class="col-md-12">
										<input type="button"  name="add_sundry" id="add_sundry" onClick="return add_bill_sundry_field();"  class="btn btn-primary" value="<?= !empty($sundryDetails['bill_sundry_id']) ? 'Update' : 'Add' ?>"/> &nbsp;
										<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
									</div></div>
									
									<!--Vendor row end-->	
									<input type='hidden' name='edit_sundry_id' id='edit_sundry_id' value='<?= !empty($sundryDetails['bill_sundry_id']) ? $sundryDetails['bill_sundry_id'] : '' ?>' />
									
							</form>					
						</div>


														
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
