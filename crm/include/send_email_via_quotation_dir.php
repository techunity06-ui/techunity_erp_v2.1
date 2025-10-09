<div class="modal colored-header info" id="send_email_via_quotation_dir_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add Email Content</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="send_email_add_d"  method="post" name="send_email_add_d" enctype="multipart/form-data">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">To Email*</label>
								<div class="col-md-12 col-xs-11">
									<input type="email" class="form-control" placeholder="Add To Email-Id" name="to_email_id_d" id="to_email_id_d" title="Enter valid To Email-Id" required />
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Add CC Email</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" placeholder="Add More Email Id" name="ccemail_id_d" id="ccemail_id_d" title="Enter valid CC Email Id" />
								</div>
							</div>
							<span style="font-size:14px;color:red">Note : Add Multiple Email id with Commas Like ";"</span>
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Add BCC Email</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" placeholder="Add BCC Email Id" name="bccemail_id_d" id="bccemail_id_d" title="Enter valid BCC Email Id" />
								</div>
							</div>  			 			 
							<!-- <div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Email Subject</label>
								<div class="col-md-10 col-xs-11">
									<input class="form-control" type="text" name="email_subject_d" value="" id="email_subject_d" placeholder="Email Subject" title="Enter Valid Subject" required>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Email Content</label>
								<div class="col-md-12 col-xs-11">
									<textarea class="form-control" placeholder="Email Content" name="email_content_d" id="email_content_d" ></textarea>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Upload Attachment</label>
								<div class="col-md-6 col-xs-11">
									<input type="file" class="form-control" title="Upload Attachment" name="email_attach[]" id="email_attach" multiple />
								</div>
							</div> -->
						</div>
						<div class="col-md-12 col-xs-11" style="text-align:center">

							<input type="hidden" name="email_page_path" value="" id="email_page_path" />
							<input type='hidden' name='quotation_id_d' id='quotation_id_d' value="">
							<input type='hidden' name='mode' id='mode' value='send_mail_quotation_dir' />
						
							<button type="submit" id="send_mail_btn_d" class="btn btn-success" >Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true" >Close</button>
						</div>
					</div>
				</form>
			</div>
		</div>	
	</div>
</div><!-- /.modal-content -->
