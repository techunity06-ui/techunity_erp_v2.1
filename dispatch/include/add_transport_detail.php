<div class="modal colored-header info" id="transport_detail_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Dispatch Transport Details : <span id="apprv_po_ref_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<form class="form-horizontal" role="form" id="transport_detail_add" action="javascript:;" method="post" name="transport_detail_add" novalidate="novalidate">
						<div class="col-md-12 margin_row">
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">Transport Name</label>
									<select class="form-control" name="transport_id" id="transport_id" >
										<?php echo get_trasports($dbcon,''); ?>
									</select>
									<strong id="cost_error_id" class="common_form_error"></strong>
								</div>	
							</div>
							
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">GR/RR No</label>
									<input type="text" class="form-control" name="transport_gr_no" id="transport_gr_no" />
								</div>	
							</div>
							
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">GR/RR Date</label>
									<input type="text" class="form-control default_date" name="transport_gr_date" id="transport_gr_date" />
								</div>	
							</div>
						</div>
					
						<div class="col-md-12 margin_row">
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">Mode Of Transport</label>
									<select class="form-control" name="transport_mode" id="transport_mode">
										<?php echo get_common_category($dbcon, 23,'Sub Type',''); ?>
									</select>
								</div>	
							</div>
							
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">Distance in KM</label>
									<input type="text" class="form-control" name="distance_km" id="distance_km" />
								</div>	
							</div>
						
						</div>
					
						<div class="col-md-12 margin_row">
						
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">Vehicle No</label>
									<input type="text" class="form-control" name="transport_vehicle_no" id="transport_vehicle_no" />
								</div>	
							</div>
							
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">Station/TO Place</label>
									<input type="text" class="form-control" name="transport_station" id="transport_station" />
								</div>	
							</div>
							
							<div class="col-md-3">
								<div class="form-group">
									<label for="edit_zone_name">Pincode</label>
									<input type="text" class="form-control numbersOnly valid" onkeypress="return isNumberKey(event)" name="transport_pincode" id="transport_pincode" />
								</div>	
							</div>
							
						</div>

						<div class="col-md-12 margin_row">
						
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">Transport Doc No</label>
									<input type="text" class="form-control" name="transport_doc_no" id="transport_doc_no" />						
								</div>	
							</div>
							
							<div class="col-md-4">
								<div class="form-group">
									<label for="edit_zone_name">Transport Doc Date</label>
									<input type="text" class="form-control default_date" name="transport_doc_date" id="transport_doc_date" />
								</div>	
							</div>

							<div class="col-md-4">
								<div class="form-group"> 
									<label for="Dispatch">Dispatch</label>
									<select class="form-control" name="dispatch_status" id="dispatch_status">
										<option value="0">No</option>
										<option value="2">Yes</option>
									</select>
								</div>
							</div>
						
						</div>
						<div class="col-md-12 margin_row">
							<div class="col-md-4">
								<input type="hidden" id="invoice_id" name="invoice_id" value="">
								<input type="hidden" name="mode" id="mode" value="transport_add">
								<input type="hidden" name="transport_transaction_id" id="transport_transaction_id" value="">
								<input type="hidden" name="transport_voucher" id="transport_voucher" value="<?=SALES_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
							</div>
							<div class="col-md-4">
								<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
							</div>
							<div class="col-md-4">
							</div>
						</div> 
					</form>
				</div>
			</div>	
		</div>
		
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->