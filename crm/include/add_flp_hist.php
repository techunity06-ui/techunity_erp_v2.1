<div class="modal colored-header info " id="add_flp_hist_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>View History: <strong id="flp_modal_inq_name"></strong></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12 addFollowupBlock">
						<div class="form-group">
							<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
								<tr>
									<th class="text-center" width="90%">Remark</th>
									<th class="text-center" width="10%">Action</th>
								</tr>
								<tr>
									<td class="text-center" style="vertical-align:top;">
										<textarea class="form-control" id="task_flp_remark" name="task_flp_remark" placeholder="Enter Follow-Up Remark" style="resize:both;" rows="4"></textarea>
									</td>
									<td style="vertical-align:top;"> 
										<input type="button" name="addhistrow" id="addhistrow" onClick="return add_flp_hist_field();"  class="btn btn-success" value="Add"/>	
										<input type="hidden" id="flp_id" name="flp_id" value="">
										<input type="hidden" id="flp_task_id" name="flp_task_id" value="">
									</td>
								</tr>
							</table>
						</div>
					</div>
				<div class="col-md-12"></div>
				
				<div class="panel-body">
					<div class="adv-table">
					<table class="display table table-bordered table-striped" id="flp-hist-datatable">
						<thead>
						<tr>
							<th>Sr. No.</th>
							<th>Remark</th>
							<th>Remarks Date</th>
							<th>User Name </th>
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
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->