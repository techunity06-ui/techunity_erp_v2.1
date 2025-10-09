<!--PO Modal Start-->
<div class="modal colored-header info" id="po_dtl_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Quotation: <strong id="head_po_qt_no"></strong></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					
					<div class="col-md-12">
						<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Company Name*</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control" id="qt_company_name" name="qt_company_name" title="Company Name" placeholder="Company Name"  />
							</div>
						</div>
						</div>
						<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Contact No.</label>
							<div class="col-md-12">
								<input type="text" class="form-control" id="qt_com_mno" name="qt_com_mno" title="Contact No." placeholder="Contact No." />
							</div>
						</div>
						</div>
						<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">GST No.</label>
							<div class="col-md-12">
								<input type="text" class="form-control" id="qt_com_gstno" name="qt_com_gstno" title="GST No." placeholder="GST No." />
							</div>
						</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-md-12">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Address</label>
								<div class="col-md-12 col-xs-11">
									<textarea class="form-control" id="qt_com_addr" name="qt_com_addr" ></textarea>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Country*</label>
								<div class="col-md-12 col-xs-11">
									<select class="select2" name="qt_add_country" id="qt_add_country" onChange="load_state(this.value,'qt_add_state','')">
										<?=get_country($dbcon,"")?>				
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">State*</label>
								<div class="col-md-12">
									<select class="select2" name="qt_add_state" id="qt_add_state" onChange="load_city(this.value,'qt_add_city','')">
										<option value="">Select State</option>	
										<?php //=getstate($dbcon,$rel['stateid'])?>				
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">City*</label>
								<div class="col-md-12">
									<select class="select2" name="qt_add_city" id="qt_add_city">
										<option value="">Select City</option>	
									</select>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
						
						<hr/>
						<div class="clearfix"></div>
						<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">PO No.*</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control" id="qt_po_no" name="qt_po_no" placeholder="PO No." />
							</div>
						</div>
						</div>
						<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">PO Date*</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control default-date-picker required valid" name="qt_po_date" id="qt_po_date" placeholder="PO Date"/>
							</div>
						</div>
						</div>
						<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Amount*</label>
							<div class="col-md-12 col-xs-11">
								<input type="number" min="0" class="form-control" name="qt_po_amount" id="qt_po_amount" placeholder="PO Amount"/>
							</div>
						</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">PO Attachment*</label>
							<div class="col-md-10">
								<input type="file" class="form-control" name="qt_po_attch" id="qt_po_attch" placeholder="PO Attachment"/>
							</div>
							<div class="col-md-2">
								<a href="" id="qt_po_attch_view" target="_blank" title="View Attachment" class="btn btn-primary"><i class="fa fa-eye"></i></a>
							</div>
						</div>
						</div>
						<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Delivery Date*</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control default-date-picker required valid" name="qt_delivery_date" id="qt_delivery_date" placeholder="Delivery Date"/>
							</div>
						</div>
						</div>
						<div class="clearfix" ></div>
						<div class="col-md-12 text-center" style="margin-top:10px;">
							<button type="button" id="add_attch_po_dtl_btn" onclick="add_attch_po_dtl();" class="btn btn-success">Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
					</div>
					<input type='hidden' name='qt_po_ref_id' id='qt_po_ref_id' value='' />	
					
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<!--PO Modal end-->