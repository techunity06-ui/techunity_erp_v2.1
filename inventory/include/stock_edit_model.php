<div class="modal colored-header info" id="stock_edit_modal" role="dialog" data-keyboard="false" data-backdrop="static" style="z-index: 9999;">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Update Opening Stock</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="stock_edit" action="javascript:;" method="post" name="stock_edit">

				<div class="row">
					<div class="col-md-12 m-bot15">
						<div class="col-md-12 m-bot15">
							<div class="form-group">
									<label class="col-md-4 control-label text-right">Product Name :</label>
									<div class="col-md-6 col-xs-11">
										 <input type="text" class="form-control" id="edit_product_name" readonly="true">
									</div>
								</div>
						</div>
						<div class="col-md-6 m-bot15">
							<div class="form-group">
								<label class="col-md-4 control-label text-right">Branch *</label>
									<div class="col-md-8 col-xs-11">
								<select  name="branch_id" class="select2 branch_id" id="edit_branch_id">
								<?=get_branch($dbcon, $branch_id); ?>   
								</select>   
							</div>
							</div>
							</div>
							<div class="col-md-6 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label text-right">Location *</label>
									<div class="col-md-8 col-xs-11">
										 <select  name="location_id" class="location_id" id="edit_location_id">
					                     <option value="">--Select Location--</option>
					                  </select>
									</div>
								</div>
							</div>
							<div class="col-md-6 m-bot15 batch_no">
								<div class="form-group">
									<label class="col-md-4 control-label text-right">Batch No *</label>
									<div class="col-md-8 col-xs-11">
										<input type="text" class="form-control" name="batch_no" id="edit_batch_no" />
					                  </select>
									</div>
								</div>
							</div>
						</div>
							<div class="col-md-12 m-bot15">
								<div class="col-md-12">
								<div class="form-group">
									<label class="col-md-3 control-label text-right">Opening Stock *</label>
									<div class="col-md-9 col-xs-11 getstock">
										<div style="display:flex;" class="col-md-6">
										 <input type="number" class="form-control" name="opening_stock_qty_main" id="opening_stock_edit_main" class="opening_stock_qty" onkeyup="product_convert_qty_main(1,'_edit_main','edit');" />
										 <input type="hidden" name="unitid" id="edit_unitid" class="unitid" value="" />
													
													<input type="hidden" id="opening_stock_qty_hide_edit_main" name="opening_stock_qty_hide_main" class="opening_stock_qty_hide" value="" />
													
													<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs unit_show" id="edit_unit_show">  </span>
													</div>
													
													<div id="edit_convert_unit_block"   class="col-md-6 convert_unit_block" style="display:none;" >
														<div style="display:flex;">
														
														<input type="number"  title="Enter Qty" min="0" id="opening_stock_conv_qty_edit_main" name="opening_stock_conv_qty_main"  class="form-control opening_stock_conv_qty_main" onkeyup="product_convert_qty_main(2,'_edit_main','edit');" />
														
														<input type="hidden" name="conv_unitid" id="edit_conv_unitid" class="conv_unitid" value="" />
														
														<input type="hidden" id="opening_stock_conv_qty_hide_edit_main" name="opening_stock_conv_qty_hide_main" class="opening_stock_conv_qty_hide_main" value="" />
														
														<span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs convert_unit_show" id="edit_convert_unit_show">  </span>
													</div>
													</div>
									</div>
								</div>
								</div>
							</div>
							<div class="col-md-12 m-bot15">
								<div class="col-md-12">
								<div class="form-group">
									<label class="col-md-3 control-label text-right">Base Rate * <a href="#"  data-original-title="Rate for one quantity." data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-2x"></i></a></label>
									<div class="col-md-9 col-xs-11 getstock">
										<div style="display:flex;" class="col-md-6">
										 <input type="number" class="form-control" name="base_rate" id="edit_base_rate"onkeyup="product_convert_rate(1,'edit_');" />
										 			<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs unit_show" id="unit_show_rate" >  </span>
													</div>
													
													<div id="convert_unit_block"  class="col-md-6 convert_unit_block" style="display:none;" >
														<div style="display:flex;">
														
														<input type="number"  title="Enter Qty" min="0" id="edit_conv_rate" name="conv_rate"  class="form-control" onkeyup="product_convert_rate(2,'edit_');" />
														<span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning convert_unit_show btn-xs" id="convert_unit_show_rate">  </span>
													</div>
													</div>
									</div>
								</div>
								</div>
							</div>
							<div class="col-md-12 m-bot15 e_process_list">
							<h3 class="m-bot15 bg-info text-center" style="margin-left: 50px;"> Process Stock</h3>
								<div class="col-md-12">
								<div id="e_process_list">
									
									</div>
								</div>
							</div>
							<input type="hidden" class="selected_product_id"  name="product_id" id="edit_product_id">
							<input type="hidden" name="opening_stock_id" id="opening_stock_id">
							
							<input type="hidden" name="mode" value="update">
							<div class="col-md-12 text-center">
								<div class="form-group">
									<input type="submit" class="btn btn-primary" value="UPDATE"/>
								</div>
								</div>
						</div>
					</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

