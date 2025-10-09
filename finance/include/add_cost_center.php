
<!-- Modal Cost Center-->
<div class="modal colored-header info" id="ModalCostCenter" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Cost Center Detail</h3>
			
		</div>
		<div class="modal-body form">
			
			<?php 
				$master_details=get_table_details_option($dbcon,'tbl_cost_center','cost_center_id','cost_center_name',' and isdelete=0');
				$balance_type=getbalance_type_new($dbcon,"");
			?>
			
			<div class="row">
			<div class="col-md-12 margin_row">
			
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Cost Center</label>
						<select class="form-control" name="costcenter_id" id="costcenter_id" tabindex="201">
							<option value="">--Select Cost Center--</option>
							<?=$master_details;?>
						</select>
						<strong id="cost_error_id" class="common_form_error"></strong>
					</div>	
				</div>
				
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Amount</label>
						<input type="text" class="form-control" name="costcenter_amount" id="costcenter_amount" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);"  tabindex="202" />
						<strong id="cost_amount_id" class="common_form_error"></strong>
					</div>	
				</div>

				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Type</label>
						<select class="form-control" name="costcenter_entry_type" id="costcenter_entry_type"  tabindex="203">
							<?=$balance_type;?>
						</select>
						<strong id="cost_entry_id" class="common_form_error"></strong>
					</div>
				</div>
				
			</div>
			
			<div class="col-md-12 margin_row">
				<div class="col-md-3">
					<input type="button" id="add_cost_center_btn" value="Add"  class="btn btn-primary" onclick="add_cost_center()"  tabindex="204" />
				</div>
			</div>
			
			
		</div>
		
		<div class="row">
			<div class="col-md-12 margin_row">
				<div class="adv-table">
					<table class="display table table-bordered table-striped" id="cost-table">
						<thead>
							<tr>
								<th>Sr. NO.</th>
								<th>Cost Center Name</th> 
								<th>Amount</th> 
								<th>Entry Type</th> 
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


