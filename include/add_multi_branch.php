<div class="modal colored-header info " id="modal-multi-branch" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Multi Branch</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="30%" class="text-center">Branch</th>
								<th width="30%" class="text-center">D/C</th>
								<th width="30%" class="text-center">OpeningBalace(in Rs.)</th>
								<th width="10%" class="text-center"></th>
							</tr>
							<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
							<tr id="field1">
								
								<td style="vertical-align:top;">
									<?php  $branch_id = $_SESSION['branch_id']; ?>
									<select class="select2" name="multi_branch_id" id="multi_branch_id">
										<?php echo get_branch_name_company($dbcon, $branch_id); ?>
									</select>
									
								</td>	
								
								<td style="vertical-align:top;">

									<select class="select2" name="branch_entry_type" id="branch_entry_type">
										<?php echo getbalance_type_new($dbcon); ?>
									</select>
								</td>
								<td style="vertical-align:top;">
									<input type="number"  title="Opening Balance" placeholder="OpeningBalace In Rs." id="branch_opening_balance" name="branch_opening_balance" class="form-control"/>
								</td>
								
								<td style="vertical-align:top;"> 
									<input type="button"  name="addrow_branch" id="addrow_branch" onClick="return add_multi_branch_field();"  class="btn btn-primary" value="Add"/>	
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
							<table  class="display table table-bordered table-striped" id="multi_branch_table">							
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Branch Name</th>
										<th>Entry Type</th>
										<th>Opening Balance Rs.</th>
										<th class="hidden-phone">Action</th>					  
									</tr>
								</thead>
								<tbody>
								</tbody>
								<tfoot>
									<tr><th colspan="3" style="text-align: right;">Total</th><th><?php echo total_multibranch($dbcon);  ?></th></tr>
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
