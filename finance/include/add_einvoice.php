
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
<div class="modal colored-header info" id="Modaleinv_new" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>E-Invoice</h3>

			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12 margin_row info_line">
						Transaction Details
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_supply_type">Type of Supply</label>
								<select class="form-control" name="einv_supply_type" id="einv_supply_type" >
									<option value="B2B" >Business to Business</option>
									<option value="SEZWP" >SEZ with payment</option>
									<option value="SEZWOP" >SEZ without payment</option>
									<option value="EXPWP" >Export with Payment</option>
									<option value="EXPWOP" >Export without payment</option>
									<option value="DEXP" >Deemed Export</option>
								</select>
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="rev_charg">Reverse Charge</label>
								<select class="form-control" name="rev_charg" id="rev_charg" >
									<option value="Y" >Yes</option>
									<option value="N" >No</option>
								</select>
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row info_line">
						Document Details
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_type">Document Type</label>
								<input type="text" class="form-control" name="einv_doc_type" id="einv_doc_type" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_no">Document No</label>
								<input type="text" class="form-control" name="einv_doc_no" id="einv_doc_no" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_date">Document Date</label>
								<input type="text" class="form-control" name="einv_doc_date" id="einv_doc_date" value="" readonly />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row info_line">
						Seller Details
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_type">GSTN</label>
								<input type="text" class="form-control" name="einv_seller_gstn" id="einv_seller_gstn" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_no">Seller Name</label>
								<input type="text" class="form-control" name="einv_seller_name" id="einv_seller_name" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_date">Address</label>
								<input type="text" class="form-control" name="einv_seller_add" id="einv_seller_add" value="" readonly />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_type">State</label>
								<input type="text" class="form-control" name="einv_seller_state" id="einv_seller_state" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_date">State Code</label>
								<input type="text" class="form-control" name="einv_seller_statecode" id="einv_seller_statecode" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_no">Pincode</label>
								<input type="text" class="form-control" name="einv_seller_pincode" id="einv_seller_pincode" value="" readonly />
							</div>	
						</div>
						
					</div>
					<div class="col-md-12 margin_row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_date">Phone No</label>
								<input type="text" class="form-control" name="einv_seller_phoneno" id="einv_seller_phoneno" value="" readonly />
							</div>	
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="einv_doc_no">Email</label>
								<input type="text" class="form-control" name="einv_seller_email" id="einv_seller_email" value="" readonly />
							</div>	
						</div>
					</div>
					<div class="col-md-12 margin_row info_line">
						Product Details
					</div>
					<div class="col-md-12 margin_row">
						<div id="einv_product_detail"></div>
					</div>

					<div class="col-md-12 margin_row">
						<center>
							<input type="button"  name="addeway" id="addeway" onClick="return add_einv_bill();"  class="btn btn-success" value="Add" />	
						</center>
					</div>
					<input type="hidden" name="einv_invoice_id" id="einv_invoice_id" />

				</div>

			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


