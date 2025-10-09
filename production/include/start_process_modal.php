<div class="modal colored-header info" id="table_start_allocate_process" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3> Allocate Process For <span id="process_id_name1"></span> </h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Start Time.*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="pr_st_time1" name="pr_st_time1" class="form-control" value="<?=date('d-m-Y h:i:sa') ?>" readonly>
								</div>
							</div>
						</div>	
						<div class="col-md-6">  	
							<div class="form-group">  	
								<label class="col-md-4 control-label">Pending Qty*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" id="pr_p_qty1" name="pr_p_qty1" class="form-control"  value="" placeholder="" readonly>
								</div>
							</div>	
						</div>	
					</div>
					
					<div class="col-md-12">
						<div class="panel-body">
							<div class="adv-table">
								 <table class="display table table-bordered table-striped" id="material_details">
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
					
					<input type="hidden" name="p_id1" id="p_id1" value="" />
					<input type="hidden" name="pro_id1" id="pro_id1" value="" />
					
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">Available Qty Of Machine.*</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" name="machine_no" id="machine_no" class="form-control" value="" readonly />
								</div>
							</div>
						</div>	
						
					</div>
					
					
					<div class="col-md-12" style="margin-top:20px;">
						
						<div class="col-md-6 col-md-offset-4">  	
							<strong style='color:red;display:none' id="error_start_msg">You can Not Start The Process</strong>
							<input type="button" id="sp_btn" class="btn btn-success" value="Start The Process" onclick="add_start_process()" />
						</div>	
						
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

