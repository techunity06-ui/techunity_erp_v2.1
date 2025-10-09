
<div class="modal colored-header info " id="modal-salesman" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Salesman Details</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<div class="form-group">


						<div class="col-md-12">								
									
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Salesman Parent</label>
										<div class="col-md-12 col-xs-11">
											<select class="select2" id="salesman_parent">
												<?=get_group_ledger_admin($dbcon,58," and  enable_salesman='1'");?>
											</select>
											
										</div>
										<strong style='color:red'>* Leave Blank If Salesman is Primary or have no parent</strong>
									</div>
									
									<div class="form-group row_margin">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Commision Mode *</label>
										<div class="col-md-12 col-xs-11">
											<select class="form-control" id="salesman_commision_mode">
												<option value="">--Select Commision Mode--</option>
												<option value="0">Percentage</option>
												<option value="1">Lumpsum Amount</option>
												<option value="2">Per Qty</option>
											</select>
										</div>
									</div>
									
									<div class="form-group row_margin">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Commision Amount/Percentage </label>
										<div class="col-md-12 col-xs-11">
											<input type="text" class="form-control" name="" id="salesman_commision_percentage" />
										</div>
									</div>
									
						</div>

						<div class="col-md-12 row_margin">
						
							<div class="form-group">
								<button type="submit" class="btn btn-success" onclick="add_salesman()">Submit</button> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div>
									
						</div>
									
									<!--Vendor row end-->	
									<input type='hidden' name='edit_salesman_id' id='edit_salesman_id' value='' />
									
						</div>


														
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
