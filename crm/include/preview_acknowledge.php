<div class="modal colored-header info " id="bs-acknowledge-modal-lg" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				
				<h3>Add Acknowledge</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="acknowledgement_add" action="javascript:;" method="post" name="acknowledgement_add">
							 <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <label class="control-label text-left" style="text-align:left;font-weight:bold;">Type of Inquiry</label>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="select2" id="type_of_inquiry" name="type_of_inquiry" >
                                                <option value="1" <?php if($rel['type_of_inquiry']=='1'){ ?> selected <?php } ?> >Budgetary</option>
                                                <option value="2" <?php if($rel['type_of_inquiry']=='2'){ ?> selected <?php } ?> >Reference</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <label class="control-label text-left" style="text-align:left;font-weight:bold;">Project Name</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" value="<?=$rel['inquiry_project_name']?>" name="inquiry_project_name" id="inquiry_project_name" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <label class="control-label text-left" style="text-align:left;font-weight:bold;">End User Details</label>
                                        </div>
                                        <div class="col-md-6">
                                            <textarea class="form-control" id="end_user_details" name="end_user_details" placeholder="Enter User Details"><?=$rel['end_user_details']?></textarea>
                                        </div>
                                    </div> 
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <label class="control-label text-left" style="text-align:left;font-weight:bold;">Scope Of Work</label>
                                        </div>
                                        <div class="col-md-6">
                                            <textarea class="form-control" id="scope_of_work" name="scope_of_work" placeholder="Enter Scope Of Work"><?=$rel['scope_of_work']?></textarea>
                                        </div>
                                    </div> 
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <label class="control-label text-left" style="text-align:left;font-weight:bold;">Payment Terms</label>
                                        </div>
                                        <div class="col-md-6">
                                            <textarea class="form-control" id="payment_terms" name="payment_terms" placeholder="Enter Payment Terms"><?=$rel['payment_terms']?></textarea>
                                        </div>
                                    </div> 
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <label class="control-label text-left" style="text-align:left;font-weight:bold;">Delivery Time</label>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control form_datetime-meridian" value="<?=$rel['delivery_time']?>" name="delivery_time" id="delivery_time" autocomplete="off">
                                        </div>
                                    </div> 
                                </div>
                            </div>

	                        <div class="row">
	                            <div class="col-md-6">
	                                <div class="form-group">
	                                    <div class="col-md-4">
	                                        <label class="control-label text-left" style="text-align:left;font-weight:bold;">Estimated Timeline For Closing Deal</label>
	                                    </div>
	                                    <div class="col-md-6">
	                                        <input type="text" class="form-control" value="<?=$rel['estimated_timeline_for_closing']?>" name="estimated_timeline_for_closing" id="estimated_timeline_for_closing" placeholder="Days" autocomplete="off">
	                                    </div>
	                                </div> 
	                            </div>
	                            <div class="col-md-6">
	                                <div class="form-group">
	                                    <div class="col-md-4">
	                                        <label class="control-label text-left" style="text-align:left;font-weight:bold;">Quotation Required Date</label>
	                                    </div>
	                                    <div class="col-md-6">
	                                        <input type="text" class="form-control form_datetime-meridian" value="<?=$rel['quotation_required_date']?>" name="quotation_required_date" id="quotation_required_date" autocomplete="off">
	                                    </div>
	                                </div>
	                            </div>
	                        </div>

	                        <div class="row">
	                        	<div class="col-md-12">
	                        		<center>
	                        			<input type="hidden" name="ac_inq_id" id="ac_inq_id" value="">
	                        			<input type="hidden" name="mode" id="mode" value="ac_edit">
	                        			<input type="submit" name="submit" id="submit" class="btn btn-success">
	                        			<button type="button" class="btn btn-danger" onclick="close_acknowledge()">Close</button>
	                        		</center>
	                        	</div>	
	                        </div>
					
						</form>
					</div>
				</div>	
			</div>
		</div>
	</div>
</div>