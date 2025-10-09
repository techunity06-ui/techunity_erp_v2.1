<div class="modal colored-header info " id="modal-multi-currency" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Multi Currency</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
							<tr id="field">
								<th width="20%" class="text-center">Currency</th>
								<th width="25%" class="text-center">Opening Balace(currency symbol)</th>
								<th width="20%" class="text-center">D/C</th>
								<th width="25%" class="text-center">OpeningBalace(in Rs.)</th>
								<th width="10%" class="text-center"></th>
							</tr>
							<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
							<tr id="field1">
								
								<td style="vertical-align:top;">
									<?php
										$str='';
										$query="SELECT * FROM `currency_mst`";
										$rs_dispatch=$dbcon->query($query);	
									?>
									<select class="select2" name="currencyid" id="currencyid" required >
                    					<option value="">Select Currency</option>
                    					<?php 
                    						while($rel=brp_mysqli_fetch_assoc($rs_dispatch))
											{													
												$str .= '<option '.$sel.' value="'.$rel['currencyid'].'">'.$rel['currency_name'].'</option>';
											}
											echo $str;
                    					?>
                					</select>			
								</td>	
								<td style="vertical-align:top;">
									<input type="number"  title="Currency Opening Balance" placeholder="Currency Opening Balance" id="currency_opening_balance" name="currency_opening_balance" class="form-control" />
								</td>
								<td style="vertical-align:top;">

									<select class="select2" name="currency_entry_type" id="currency_entry_type">
										<?php echo getbalance_type_new($dbcon,""); ?>
									</select>
								</td>
								<td style="vertical-align:top;">
									<input type="number"  title="Opening Balance" placeholder="OpeningBalace In Rs." id="curreency_opening_balance_rs" name="curreency_opening_balance_rs" class="form-control"/>
								</td>
								
								<td style="vertical-align:top;"> 
									<input type="button"  name="addrow_currency" id="addrow_currency" onClick="return add_multi_currency_field();"  class="btn btn-primary" value="Add"/>	
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
							<table  class="display table table-bordered table-striped" id="multi_currency_table">							
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Currency Name</th>
										<th>Opening Balance</th>
										<th>Entry Type</th>
										<th>Opening Balance Rs.</th>
										<th class="hidden-phone">Action</th>					  
									</tr>
								</thead>
								<tbody>
								</tbody>
								<tfoot>
									<tr><th colspan="4" style="text-align: right;">Total</th><th><?php echo total_multicurrency($dbcon,"");  ?></th></tr>
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
