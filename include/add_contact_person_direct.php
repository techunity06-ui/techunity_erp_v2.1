<div class="modal colored-header info" id="direct-contact-person-add" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-md">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
		<h3>Add Contact Person</h3>
	</div>
	<div class="modal-body form">
		<div class="row">
			<div class="col-md-12">
			<div class="form-horizontal">
				<div class="form-group">
					<label class="col-md-4 control-label">Name *</label>
					<div class="col-md-8 col-xs-11">
						<input type="text" class="form-control" name="cust_contact_person_name" id="cust_contact_person_name" placeholder="Person Name" >
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-4 control-label">Mobile No.</label>
					<div class="col-md-8 col-xs-11">
						<input type="text" class="form-control" name="cust_contact_person_no" id="cust_contact_person_no" placeholder="Mobile No.">
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-4 control-label">Email</label>
					<div class="col-md-8 col-xs-11">
						<input type="email" class="form-control" name="cust_contact_person_email" id="cust_contact_person_email" placeholder="Email">
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-12 text-center">
						<input type="button" name="cust_per_addrow" id="cust_per_addrow" onClick="return direct_add_cust_person_field();" class="btn btn-success" value="Add"/>	
					</div>
				</div>
				
			</div>
			</div>
		</div>	
	</div>

</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
