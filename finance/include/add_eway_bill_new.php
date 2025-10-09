
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
<div class="modal colored-header info" id="ModalEwayBill_new" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>E-way Bill </h3>

			</div>
			<div class="modal-body form">
				<div class="row">
					<!-- <div class="col-md-12 margin_row info_line">
						Transport Details
					</div> -->
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="supply_type">Supply Type</label>
								<input type="text" class="form-control" name="supply_type" id="supply_type" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="sub_type">Sub Type</label>
								<select class="form-control" name="sub_type" id="sub_type" >
									<?php //echo get_eway_bill_sub_type($dbcon,$ewayDetail[0]['transport_id']); ?>
								</select>
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="doc_type">Document Type</label>
								<input type="text" class="form-control" name="doc_type" id="doc_type" value="" readonly />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="doc_no">Document No</label>
								<input type="text" class="form-control" name="doc_no" id="doc_no" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="doc_date">Document Date</label>
								<input type="text" class="form-control default-eway-date required valid" name="doc_date" id="doc_date" value="" />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row info_line">
						Supplier Details
					</div>

					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="sup_gst_no">Supplier GSTIN</label>
								<input type="text" class="form-control" name="sup_gst_no" id="sup_gst_no" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="sup_name">Supplier Name</label>
								<input type="text" class="form-control" name="sup_name" id="sup_name" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="sup_add1">Supplier Address</label>
								<!-- <input type="text" class="form-control" name="sup_add1" id="sup_add1" value="" readonly /> -->
								<textarea class="form-control" name="sup_add1" id="sup_add1" readonly></textarea>
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="sup_city">Supplier City</label>
								<input type="text" class="form-control" name="sup_city" id="sup_city" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="sup_state">Supplier State</label>
								<input type="text" class="form-control" name="sup_state" id="sup_state" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="sup_add1">Supplier PinCode</label>
								<input type="text" class="form-control" name="sup_pincode" id="sup_pincode" value="" readonly />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row info_line">
						Customer Details
					</div>

					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="rec_gst_no">Customer GSTIN</label>
								<input type="text" class="form-control" name="rec_gst_no" id="rec_gst_no" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="rec_name">Customer Name</label>
								<input type="text" class="form-control" name="rec_name" id="rec_name" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="rec_add1">Customer Address</label>
								<!-- <input type="text" class="form-control" name="rec_add1" id="rec_add1" value="" readonly /> -->
								<textarea class="form-control" name="rec_add1" id="rec_add1" readonly></textarea>
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="rec_city">Customer City</label>
								<input type="text" class="form-control" name="rec_city" id="rec_city" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="rec_state">Customer State</label>
								<input type="text" class="form-control" name="rec_state" id="rec_state" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="rec_pincode">Customer Pincode</label>
								<input type="text" class="form-control" name="rec_pincode" id="rec_pincode" value="" readonly />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row info_line">
						Transport Details
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="trn_mode">Transport Mode</label>
								<!-- <input type="text" class="form-control" name="trn_mode" id="trn_mode" value="" readonly /> -->
								<select class="form-control" name="trn_mode" id="trn_mode" tabindex="101">
									<?php echo get_eway_transport_mode($dbcon,""); ?>
								</select>
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="trn_name">Transporter Name</label>
								<select class="form-control" name="trn_name" id="trn_name" tabindex="101">
									<?php echo get_trasports($dbcon,$ewayDetail[0]['trn_mode']); ?>
								</select>
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="trn_distance">Distance</label>
								<input type="number" class="form-control" name="trn_distance" id="trn_distance" value=""  />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="trn_mode">Transport Document No(LR No)</label>
								<input type="text" class="form-control" name="trn_doc_no" id="trn_doc_no" value="" />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="trn_name">Transport Document Date(LR Date)</label>
								<input type="text" class="form-control default-eway-date required valid" name="trn_doc_date" id="trn_doc_date" value="" />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="trn_distance">Vehicle No</label>
								<input type="text" class="form-control" name="vehicle_no" id="vehicle_no" value=""  />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row">
					    <div class="col-md-4">
							<div class="form-group">
								<label for="trn_distance">Transporter Id</label>
								<input type="text" class="form-control" name="TransporterId" id="TransporterId" value=""  />
							</div>	
						</div>
					 </div>
					<div class="col-md-12 margin_row info_line">
						Product Details
					</div>
					<div class="col-md-12 margin_row">
						<div id="eway_product_detail"></div>
					</div>

					<div class="col-md-12 margin_row">
						<center>
							<input type="button"  name="addeway" id="addeway" onClick="return add_eway_bill();"  class="btn btn-success" value="Add" />	
						</center>
					</div>
					<input type="hidden" name="invoice_id" id="invoice_id" />

				</div>

			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


