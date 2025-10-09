<div class="modal colored-header info" id="add_person_modal" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
		<h3>Add Person</h3>
	</div>
	<div class="modal-body form">
		<div class="row">
			<div class="col-md-12">
				<form class="form-horizontal" role="form" id="add_person_form" action="javascript:;" method="post" name="add_person_form">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">First Name*</label>
							<div class="col-md-12">
                                <input type="text" class="form-control" name="con_first" id="con_first" placeholder="First Name" required/>
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Last Name</label>
							<div class="col-md-12">
								<input type="text" class="form-control" name="con_last" id="con_last" placeholder="Last Name" />
							</div>
						</div>	
					</div>
					<div class="clearfix"></div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">ISD No</label>
							<div class="col-md-12">
								<select class="select2" name="con_isd_id" id="con_isd_id">
									<?=get_isd_no($dbcon,$isd_id)?>				
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Mobile</label>
							<div class="col-md-12">
                                <input type="text" class="form-control" name="con_mobile" id="con_mobile" placeholder="Mobile"/>
							</div>
						</div>	
					</div>
					<div class="clearfix"></div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Email</label>
							<div class="col-md-12">
                                <input type="email" class="form-control" name="com_email" id="com_email" placeholder="Email"/>
							</div>
						</div>	
					</div>
					
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Phone</label>
							<div class="col-md-12">
								<input type="text" class="form-control" name="con_phone" id="con_phone" placeholder="Phone" pattern="\d*" maxlength="10"/>
							</div>
						</div>	
					</div>
					<div class="clearfix"></div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Job Title</label>
							<div class="col-md-12">
								<input type="text" class="form-control" name="con_job" id="con_job" placeholder="Job Title" />
							</div>
						</div>	
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="col-md-12 text-center">
					<button type="submit" onclick="add_cust_contact()" class="btn btn-success">Submit</button> &nbsp;
					<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
			<!--Vendor row end-->	
			<input type='hidden' name='cust_person_model' id='cust_person_model' value='model' />
		</form>
	</div>
</div>	
</div>
</div><!-- /.modal-content -->