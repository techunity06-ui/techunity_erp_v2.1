
<style>
	
	.info_line
	{
		background-color:#337AB7 !important;
		color:#FFFFFF !important;
		padding:10px;
		text-align:center !important;
		font-weight:bold;
		font-size:14px;
	}
	
</style>
<!-- Modal Cost Center-->
<div class="modal colored-header info" id="ModalEwayBill" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>E-way Bill </h3>
			
		</div>
		<div class="modal-body form">
			
		
			<div class="row">
			
			<div class="col-md-12 margin_row info_line">
				Transport Details
			</div>
			
			<div class="col-md-12 margin_row">
			
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">Transport Name</label>
						<select class="form-control" name="transport_id" id="transport_id" tabindex="101">
							<?php echo get_trasports($dbcon,$ewayDetail[0]['transport_id']); ?>
						</select>
						<strong id="cost_error_id" class="common_form_error"></strong>
					</div>	
				</div>
				
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">GR/RR No</label>
						<input type="text" class="form-control" name="transport_gr_no" id="transport_gr_no" value="<?php echo $ewayDetail[0]['transport_gr_no']; ?>" tabindex="102" />
					</div>	
				</div>
				
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">GR/RR Date</label>
						<input type="text" class="form-control default_date" name="transport_gr_date" id="transport_gr_date" value="<?php echo $ewayDetail[0]['transport_gr_date']; ?>" tabindex="103" />
					</div>	
				</div>
				
			</div>
			
			<div class="col-md-12 margin_row">
			
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">Mode Of Transport</label>
						<select class="form-control" name="transport_mode" id="transport_mode" tabindex="104" >
							<?php echo get_common_category($dbcon, 23,'Sub Type',$ewayDetail[0]['transport_mode']); ?>
						</select>
					</div>	
				</div>
				
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">Distance in KM</label>
						<input type="text" class="form-control" name="distance_km" id="distance_km" value="<?php echo $ewayDetail[0]['distance_km']; ?>" tabindex="105" />
					</div>	
				</div>
			
			</div>
			
			<div class="col-md-12 margin_row">
			
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">Vehicle No</label>
						<input type="text" class="form-control" name="transport_vehicle_no" id="transport_vehicle_no" value="<?php echo $ewayDetail[0]['transport_vehicle_no']; ?>" tabindex="106" />
					</div>	
				</div>
				
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">Station/TO Place</label>
						<input type="text" class="form-control" name="transport_station" id="transport_station" value="<?php echo $ewayDetail[0]['transport_station']; ?>" tabindex="107" />
					</div>	
				</div>
				
				<div class="col-md-3">
					<div class="form-group">
						<label for="edit_zone_name">Pincode</label>
						<input type="text" class="form-control numbersOnly valid" onkeypress="return isNumberKey(event)" name="transport_pincode" id="transport_pincode" maxlength="6" value="<?php echo $ewayDetail[0]['transport_pincode']; ?>" tabindex="108" />
					</div>	
				</div>
				
			</div>

			<div class="col-md-12 margin_row">
			
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">Transport Doc No</label>
						<input type="text" class="form-control" name="transport_doc_no" id="transport_doc_no" value="<?php echo $ewayDetail[0]['transport_doc_no']; ?>" tabindex="109" />						
					</div>	
				</div>
				
				<div class="col-md-4">
					<div class="form-group">
						<label for="edit_zone_name">Transport Doc Date</label>
						<input type="text" class="form-control default_date" name="transport_doc_date" id="transport_doc_date" value="<?php echo $ewayDetail[0]['transport_doc_date']; ?>" tabindex="110" />
					</div>	
				</div>
			
			</div>
			
			<div class="eway_bill_class">
				
				<div class="col-md-12 margin_row info_line">
					Eway Bill / E-Invoice Details
				</div>			
			
				<div class="col-md-12 margin_row">
				
					<div class="col-md-3">
						<div class="form-group">
							<label for="edit_zone_name">E-invoice Required</label>
							<select class="form-control" id="iseinvoice_bill" name="iseinvoice_bill" tabindex="111"> 
								<option value="0">No</option>
								<option value="1">Yes</option>
							</select>
						</div>	
					</div>
					
					<div class="col-md-3">
						<div class="form-group">
							<label for="edit_zone_name">E-Way Bill Required</label>
							<select class="form-control" id="iseway_bill" name="iseway_bill" onchange="show_eway_field(this.value)" tabindex="112" > 
								<option value="0">No</option>
								<option value="1">Yes</option>
							</select>
						</div>	
					</div>
					
				</div>
				
				<div class="col-md-12 margin_row eway_other_field">
				
					<div class="col-md-3">
						<div class="form-group">
							<label for="edit_zone_name">Sub Type</label>
							<select class="form-control" id="eway_sub_type" name="eway_sub_type" tabindex="113"> 
								<?php echo get_common_category($dbcon, 25,'Sub Type',$ewayDetail[0]['eway_sub_type']); ?>
							</select>
						</div>	
					</div>
					
					<div class="col-md-3">
						<div class="form-group">
							<label for="edit_zone_name">Transaction Type</label>
							<select class="form-control" id="eway_transaction_type" name="eway_transaction_type" tabindex="114" > 
								<?php echo get_common_category($dbcon, 22,'Transaction Type',$ewayDetail[0]['eway_transaction_type']); ?>
							</select>
						</div>	
					</div>
					
				</div>
			</div>
			
			<div class="col-md-12 margin_row">
				<div class="col-md-3">
					<input type="button" id="add_transport_btn" value="Add"  class="btn btn-primary" onclick="add_transport()"  tabindex="115" />
					<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true" tabindex="116">Close</button>
					<input type='hidden' name='edit_eway_id' id='edit_eway_id' value="<?php echo $ewayDetail[0]['transport_transaction_id']; ?>" />
				</div>
			</div>
			
			
		</div>
		
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


