<div class="modal colored-header info " id="bs-example-modal-state" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add State</h3>
				
			</div>
			<div class="modal-body form">
			<div class="row">

			<div class="col-md-12">
			
			<form class="form-horizontal" role="form" id="state_add" action="javascript:;" method="post" name="state_add">
					<div class="form-group">
					  <label class="col-md-12 control-label" style="text-align:left;line-height:25px">State Initial *</label>
						<div class="col-md-12 col-xs-11">
							<input type="text" class="form-control" id="state_initial" name="state_initial" placeholder="State Initial">
						</div>
                     </div>
					<div class="form-group">
					  <label class="col-md-12 control-label" style="text-align:left;line-height:25px">State Name *</label>
						<div class="col-md-12 col-xs-11">
							<input type="text" class="form-control" placeholder="State Name" name="state_name" id="state_name"  />
						</div>
                     </div>
					 <div class="form-group">
					  <label class="col-md-12 control-label" style="text-align:left;line-height:25px">GST Code</label>
					  <div class="col-md-12 col-xs-11">
							<input type="text" class="form-control" placeholder="GST Code" name="gst_state_code" id="gst_state_code"  />
						</div>
                     </div>
				<div class="col-md-12">
							<button type="submit" class="btn btn-success">Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</div></div>
					
					<!--Vendor row end-->	
							<input type='hidden' name='model' id='model' value='model' />				  
							
						  </form>
				</div>
			</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
