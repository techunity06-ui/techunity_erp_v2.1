<div class="modal colored-header info" id="table_show_allocate_process" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3> Today Process Quantity For <span id="process_id_name"></span> </h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Started Time.*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="pr_st_time" name="pr_st_time" class="form-control" value="<?=date('d-m-Y h:i:sa') ?>" readonly>
								</div>
							</div>
						</div>	
						
						
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label">End Time*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="pr_end_time" name="pr_end_time" class="form-control" value="<?=date('d-m-Y h:i:sa') ?>" readonly>
								</div>
							</div>	
						</div>
					</div>
					
					<div class="col-md-12"  style="margin-top:10px;">
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label">Pending Qty*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="pr_p_qty" name="pr_p_qty" class="form-control"  value="" placeholder="" readonly>
								</div>
							</div>	
						</div>	
						
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label">Available Qty*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="pr_available_qty" name="pr_available_qty" class="form-control"  value="" placeholder="" >
								</div>
							</div>	
						</div>
					
					</div>
					
					<div class="col-md-12">
						<div class="panel-body">
							<div class="adv-table">
								 <table class="display table table-bordered table-striped" id="material_details1">
									<thead>
									  <tr>
										<th>Product Name</th>
										<th>Qty Needed For Single Piece</th>
										<th>Total Required Qty</th>
										<th>Total Available Qty </th>
										<th>Total Usable Qty</th>
										<th>Unit</th>
									  </tr>
									</thead>
									<tbody>
									</tbody>				 
								</table>
							</div>
						</div>
					</div>
					
					<div class="col-md-12"  style="margin-top:10px;">
					
						
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label">Process Qty*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="pr_pr_qty" name="pr_pr_qty" class="form-control"  value="" placeholder="" onkeyup="get_final_qty()" >
								</div>
							</div>	
						</div>	
					
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label">Remain Qty*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="pr_remain_qty" name="pr_remain_qty" class="form-control"  value="" placeholder="" readonly>
								</div>
							</div>	
						</div>	
					
					</div>
					
					<input type="hidden" name="product_id" id="product_id" value="" />
					<input type="hidden" name="qc_id" id="qc_id" value="" />
					<input type="hidden" name="p_id" id="p_id" value="" />
					<input type="hidden" name="ref_type" id="ref_type" value="" />
					<input type="hidden" name="pr_process_type" id="pr_process_type" value="" />
					<input type="hidden" name="aid" id="aid" value="" />
					
					<div class="col-md-12" style="margin-top:10px;">
						
						<div class="col-md-6 col-md-offset-4">  	
							<strong style='color:red;display:none' id="error_start_msg_allocated">You can Not Start The Process</strong>
							<input type="button" id="pr_button" class="btn btn-success" value="End Process" onclick="start_process_allocation()" />
						</div>	
						
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

