<div class="modal colored-header info" id="send_email_via_po_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add Email Content</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="send_email_add_po" method="post" name="send_email_add_po" enctype="multipart/form-data">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">To Email*</label>
								<div class="col-md-12 col-xs-11">
									<input type="email" class="form-control" placeholder="Add To Email-Id" name="to_email_po" id="to_po_email" title="Enter valid To Email-Id" required />
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Add CC Email</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" placeholder="Add More Email Id" name="ccemail_po" id="ccemail_po" title="Enter valid CC Email Id" />
								</div>
							</div>
							<span style="font-size:14px;color:red">Note : Add Multiple Email id with Commas Like ";"</span>
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Add BCC Email</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" placeholder="Add BCC Email Id" name="bccemail_po" id="bccemail_po" title="Enter valid BCC Email Id" />
								</div>
							</div>
						</div>
						<div class="col-md-12 col-xs-11" style="text-align:center">

							<input type="hidden" name="email_page_path" value="" id="email_page_path" />
							<input type='hidden' name='email_po_id' id='email_po_id' value="">
							<input type='hidden' name='mode' id='mode' value='send_mail_po' />

							<button type="submit" id="send_mail_btn_po" class="btn btn-success">Submit</button> &nbsp;
							<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div><!-- /.modal-content -->