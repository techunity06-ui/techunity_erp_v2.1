<div class="modal colored-header info" id="modal-contact-person-view" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
		<h3>Add Contact Person</h3>
	</div>
	<div class="modal-body form">
		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr>
							<th width="30%" class="text-center">Name</th>
							<th width="10%" class="text-center">Mobile No.</th>
							<th width="10%" class="text-center">Email</th>
							<th width="10%" class="text-center">Action</th>
						</tr>
						<tr>
							<td style="vertical-align:top;">
								<input type="text" class="form-control" name="cust_contact_person_name" id="cust_contact_person_name">
							</td>
							<td style="vertical-align:top;">
								<input type="text" class="form-control" name="cust_contact_person_no" id="cust_contact_person_no">
							</td>
							<td style="vertical-align:top;">
								<input type="email" class="form-control" name="cust_contact_person_email" id="cust_contact_person_email">
							</td>
							<td style="vertical-align:top;"> 
								<input type='hidden' name='cust_id' id='cust_id' value='' />
								<input type='hidden' name='edit_cust_contact_person_id' id='edit_cust_contact_person_id' value='' />
								<input type="button" name="cust_per_addrow" id="cust_per_addrow" onClick="return add_cust_person_field();" class="btn btn-primary" value="Add"/>	
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>	
	</div>
	<hr style="margin: unset;">
	<div class="modal-body form">
		<div class="row">
			<div class="col-md-12">
				<div class="adv-table" id="adv-table">
					<table class="display table table-bordered table-striped" id="table-cust-person">
						<thead>
							<tr>
								<th>Sr. NO.</th>
								<th>Name</th>
								<th>Mobile No.</th>
								<th>Email</th>
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
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
