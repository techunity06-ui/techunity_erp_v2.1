
<div class="modal" id="model_add_transport_address" role="dialog"  aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Address</h3>
			</div>
			
			<div class="modal-body form">
			<form name="trans_add_f" id="trans_add_f">
				<div class="row">
					<div class="col-md-8">
						<div class="form-group">
						
							<label>Transportation Address </label>
							<textarea class="form-control" name="transportation_address" id="transportation_address" ></textarea>
							
						</div>
					</div>
					<div class="col-md-4">
						<input type="button" class="btn btn-success" value="ADD" style="box-shadow: 3px 3px #61a642;" onclick="add_address_db()" id="add_tranc_btn" />
						
						<input type="hidden" id="trans_id" />
						<input type="hidden" id="trans_add_id" />
					</div>
					<div class="col-md-12">
					<div class="adv-table">
							<table class="display table table-bordered table-striped" id="transportation_add-table">
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Transportation Address</th> 
										<th class="hidden-phone">Action</th>					  
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</form></div>
			</div>
		</div>
	</div>
</div>
