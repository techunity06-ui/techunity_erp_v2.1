<style type="text/css">
	.padd7px{
		padding: 7px;
	}
</style>
<div class="modal colored-header info" id="preview_job_card_process_transfer_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Job card quantity transfer</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="process_transfer" action="javascript:;" method="post" name="process_transfer">
				<div class="row">
					<div class="col-md-12 m-bot15">
						<div class="row m-bot15">
							<div class="col-12 text-center">
								<h4> Product Name : <span id="product_name"></span> </h4>
							</div>
							<div class="col-12 text-center">
								<h4> Process Name : <span id="process_name"></span> </h4>
							</div>
							<div class="col-12 text-center">
								<h4>Current Process Type :  <span class="process_type"></span></h4>
							</div>
						</div>
						
						<div class="row m-bot15">
							<div class="form-group">
								<label class="col-md-4 control-label text-right"><span class="padd7px label label-warning">Pending Qty</span></label>
								<div class="col-md-8 col-xs-11">
									<div class="col-md-10">
									 	<input type="number" readonly="true" class="form-control" name="pending_qty" id="pending_qty"/>
									</div>
								</div>
							</div>
						</div>
						<div class="row m-bot15">
							<div class="form-group">
								<label class="col-md-4 control-label text-right"><span class="padd7px label label-success">Inhouse Qty</span></label>
								<div class="col-md-8 col-xs-11">
									<div class="col-md-10">
									 	<input type="number" onkeyup="update_qty(1)" class="form-control" name="inhouse_qty" id="inhouse_qty" value="0"/>
									</div>
								</div>
							</div>
						</div>
						<div class="row m-bot15">
							<div class="form-group">
								<label class="col-md-4 control-label text-right"><span class="padd7px label label-primary">Outside Qty</span></label>
								<div class="col-md-8 col-xs-11">
									<div class="col-md-10">
									 	<input type="number" onkeyup="update_qty(2)" class="form-control" name="outside_qty" id="outside_qty" value="0" />
									</div>
								</div>
							</div>
						</div>

						<div class="stock_details" style="display:none">
							<div class="text-center m-bot15">
								<h4>Below is reserve stock for this product. would you like to transfer stock ? </h4>
							</div>
							<div class="row m-bot15">
							<div class="form-group">
								<label class="col-md-4 control-label text-right"><span class="padd7px label label-warning">Total Stock</span></label>
								<div class="col-md-8 col-xs-11">
									<div class="col-md-10">
									 	<input type="number" readonly="true" class="form-control" name="total_stock" id="total_stock"/>
									</div>
								</div>
							</div>
						</div>
						<div class="row m-bot15">
							<div class="form-group">
								<label class="col-md-4 control-label text-right"><span class="padd7px label label-success">Inhouse Stock</span></label>
								<div class="col-md-8 col-xs-11">
									<div class="col-md-10">
									 	<input type="number" onkeyup="update_stock(1)" class="form-control" name="inhouse_stock" id="inhouse_stock" value="0"/>
									</div>
								</div>
							</div>
						</div>
						<div class="row m-bot15">
							<div class="form-group">
								<label class="col-md-4 control-label text-right"><span class="padd7px label label-primary">Outside Stock</span></label>
								<div class="col-md-8 col-xs-11">
									<div class="col-md-10">
									 	<input type="number" onkeyup="update_stock(2)" class="form-control" name="outside_stock" id="outside_stock" value="0" />
									</div>
								</div>
							</div>
						</div>
						</div>
						<div class="row m-bot15">
							<div class="col-md-12 text-center" style="margin-top:10px;">
								<button type="submit" class="btn btn-success">Save</button> &nbsp; &nbsp; 
								<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div>
						</div>
					</div>
				</div>
			</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
