<div class="modal colored-header info " id="modal-add-hsn" role="dialog" data-keyboard="false" data-backdrop="static" style="overflow-y:auto;">
	<div class="modal-dialog modal-lg xlg" style="width: 400px;height: 2000px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" accesskey="c" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add HSN Code</h3>				
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						
							<input type="hidden" name="direct_hsn_add" id="direct_hsn_add" value="1" >
							<input type="hidden" name="hsn_add_type" id="hsn_add_type" value="" >	
												
							<div class="form-group">
								<label>HSN Code</label>
								<input class="form-control numbersOnly" type='text' name='hsn_code' id='hsn_code' value='' maxlength="10" />
							</div>
							
							<div class="form-group">
								<label>HSN Description</label>
								<input class="form-control" type='text' name='hsn_desc' id='hsn_desc' value='' />
							</div>
							
							<div class="form-group">
								<label>Select Tax Category</label>
								<select class="select2" name='sale_gst' id='sale_gst' value=''title="Select Sale Gst" required>
									<?=get_tax_category_new($dbcon,'');?>
								</select>
							</div>
							
							
							<input type='hidden' name='mode' id='mode' value='add' />
							<button type="button" onclick="submit_hsn_form()"   class="btn btn-info">Submit</button>
					
					</div>
				</div>
			</div>
		</div>
	</div>
</div>