<div class="modal colored-header info" id="preview_workorder_indent" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Create Indent</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="create_wo_indent" action="javascript:;" method="post" name="create_wo_indent">
					<div class="row mtop20">
						<div class="col-md-6 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Product Name *</label>
									<div class="col-md-8 col-xs-11">
										<input type="text" readonly class="form-control" name="product_name" id="product_name"  value=""/>
									</div>
								</div>
							</div>
					</div>
					<div class="row">
						<div class="col-md-6 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Indent No *</label>
									<div class="col-md-8 col-xs-11">
										<input type="text" readonly class="form-control" name="indent_no" id="indent_no"  value="<?=load_common_no($dbcon,17);?>"/>
									</div>
								</div>
							</div>
							<div class="col-md-6 m-bot15">
								<div class="form-group">
								   <label class="col-md-4 control-label">Indent Date*</label>
								   <div class="col-md-8">
									  <input id="indent_date" name="indent_date" type="text" class="form-control default-date-picker required valid" title="Enter Indent Date" value="<?=date("d-m-Y");?>" placeholder="Indent Date" readonly>
									</div>
								</div>
							</div>
							<div class="col-md-6 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Pending Qty</label>
									<div class="col-md-8 col-xs-11">
										 <input type="number" class="form-control numbersOnly" readonly name="pending_qty" id="pending_qty"  onkeydown="return numericonly(event)"/>

									</div>
								</div>
							</div>
							<div class="col-md-6 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Indent Qty</label>
									<div class="col-md-8 col-xs-11">
										 <input type="number" class="form-control numbersOnly" name="indent_qty" id="indent_qty"  onkeydown="return numericonly(event)"/>

									</div>
								</div>
							</div>
							<div class="row m-bot15">
								<div class="col-md-12 text-center" style="margin-top:10px;">
									<button type="submit" class="btn btn-success">Save</button> &nbsp; &nbsp; 
									<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
								</div>
							</div>
						
							<input type="hidden" name="so_product_id" id="so_product_id">
							<input type="hidden" name="sales_ordertrn_id" id="sales_ordertrn_id">
							<input type="hidden" name="mode" id="mode" value="create_workorder">
							<input type="hidden" name="cust_id" id="cust_id" value="">
							<input type="hidden" name="production_branch_id" id="production_branch_id" value="">
					</div>
					
				</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

