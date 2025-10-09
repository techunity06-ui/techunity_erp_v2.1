<div class="modal fade full-width-modal-right in" id="bs-batch_wise_stock_in-modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="width:490px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Batch Wise In Qty </h3>
			</div>
			<div class="modal-body form">
				<h4><u><span id="produname_in" style="color:red"></span></u></h4>
				<div class="row">
					<div id="batch_in_data"></div>
				</div>
				
				<div class="col-md-12" style="margin-top: 20px;">
					<center>	
						<input type="hidden" name="m_trn_id" id="m_trn_id" value="" />
						<input type="hidden" name="m_qty" id="m_qty" value="" />					
						<input type="button"  name="m_addrow1" id="m_addrow1" onClick="return add_field_in();"  class="btn btn-primary addbutton" value="Add"/>
						
						&nbsp;
						<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</center>
				</div>
	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>