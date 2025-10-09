<div class="modal fade full-width-modal-right in" id="bs-batch_wise_stock_in-modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="width:490px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Batch Wise In Qty 1</h3>
			</div>
			<div class="modal-body form">
				<h4><u><span id="produname_in" style="color:red"></span></u></h4>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label">Batch No </label>
							<div class="col-md-6 col-xs-11">
								<input type="text" name="stock_general_date" id="stock_general_date" class="form-control default-date-picker" title="Stock General Date" value="<?=$general_stock_date?>" placeholder="Stock General Date"> 
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Batch Qty </label>
							<div class="col-md-6 col-xs-11">
								<select class="select2" name="cust_id" id="cust_id" onChange="get_sales_order(this.value);">
									<?=get_party_for_paking($dbcon,$cust_id);?>	
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-2">
						<div class="form-group">
							<input type="button" id="add_batch" value="Add" class="btn btn-primary" onclick="add_batch_entry()">
						</div>
					</div>
				</div>
				
				<div class="col-md-12" style="margin-top: 20px;">
					<center>	
						<input type="button"  name="m_addrow1" id="m_addrow1" onClick="return add_field_in();"  class="btn btn-primary addbutton" value="Add"/>
						
						&nbsp;
						<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</center>
				</div>
	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>