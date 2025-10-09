<?php
	$date = date("d-m-Y");
?>

<div class="modal colored-header info" id="stock_add_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add Opening Stock</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="stock_add" action="javascript:;" method="post" name="stock_add">

				<div class="row">
					<div class="col-md-12 m-bot15">
							<div class="form-group">
									<label class="col-md-4 control-label text-right">Product Name :</label>
									<div class="col-md-6 col-xs-11">
										 <input type="text" class="form-control" id="product_name" readonly="true">
									</div>
								</div>
						</div>
					<div class="col-md-12 m-bot15">
						<div class="col-md-6 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label text-right">Location *</label>
									<div class="col-md-8 col-xs-11">
										 <select  name="location_id" id="location" class="select2" onchange="get_opening_stock_hist(this.value)">
					                     <option value="">--Select Location--</option>
					                     <?=get_all_godown($dbcon,'',1);?>
					                  </select>
									</div>
								</div>
							</div>
							<div class="col-md-6 m-bot15">
								
						<?php echo getBranchBox($dbcon, $branch_id); ?>      
							
						</div>
							<div class="col-md-6 m-bot15 batch_no">
								<div class="form-group">
									<label class="col-md-4 control-label text-right">Batch No *</label>
									<div class="col-md-8 col-xs-11">
										<input type="text" class="form-control" name="batch_no" id="batch_no" />
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
										 <input type="number" class="form-control opening_stock_qty" name="opening_stock" id="opening_stock"onkeyup="product_convert_qty_main(1);" />
										 <input type="hidden" name="unitid" id="unitid" class="unitid" value="" />
													
													<input type="hidden" id="opening_stock_qty_hide" name="opening_stock_qty_hide_main" value="" class="opening_stock_qty_hide_main" />
													
													<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs unit_show" id="unit_show" >  </span>
													</div>
													
													<div id="convert_unit_block"  class="col-md-6 convert_unit_block" style="display:none;" >
														<div style="display:flex;">
														<input type="number"  title="Enter Qty" min="0" id="opening_stock_conv_qty" name="opening_stock_conv_qty_main"  class="form-control opening_stock_conv_qty_main" onkeyup="product_convert_qty_main(2);" />
														<input type="hidden" name="conv_unitid"  class="conv_unitid" id="conv_unitid" value="" />	
														<input type="hidden" class="opening_stock_conv_qty_hide_main" id="opening_stock_conv_qty_hide" name="opening_stock_conv_qty_hide_main" value="" />	
														<span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning convert_unit_show btn-xs" id="convert_unit_show">  </span>
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
										 <input type="number" class="form-control" name="base_rate" id="base_rate"onkeyup="product_convert_rate(1);" />
										 			<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs unit_show" id="unit_show_rate" >  </span>
													</div>
													
													<div id="convert_unit_block"  class="col-md-6 convert_unit_block" style="display:none;" >
														<div style="display:flex;">
														
														<input type="number"  title="Enter Qty" min="0" id="conv_rate" name="conv_rate"  class="form-control" onkeyup="product_convert_rate(2);" />
														<span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning convert_unit_show btn-xs" id="convert_unit_show_rate">  </span>
													</div>
													</div>
									</div>
								</div>
								</div>
							</div>
							<div class="col-md-12 m-bot15">
								<div class="col-md-12">
								<div class="form-group">
									<label class="col-md-3 control-label text-right">MFG Date * </label>
									<div class="col-md-9 col-xs-11">
									<div class="col-md-5 col-xs-11">
                                        <input id="mfg_date" name="mfg_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$date?>" placeholder="MFG Date">
                                    </div>
								</div>
								</div>
							</div>
							<div class="col-md-12 m-bot15">
								<h3 class="m-bot15 bg-info text-center" style="margin-left: 50px;"> Process Stock</h3>
								<div class="col-md-12">
								<div id="process_list">
									
									</div>
								</div>
							</div>
							<input type="hidden" name="selected_product_id" id="selected_product_id">
							<input type="hidden" name="selected_product_base_qty" id="selected_product_base_qty">
							<input type="hidden" name="selected_product_conv_qty" id="selected_product_conv_qty">
							<input type="hidden" name="same_unit" id="same_unit">
							<input type="hidden" name="mode" id="mode" value="add">
							<div class="col-md-12 text-center">
								<div class="form-group">
									<input type="submit" class="btn btn-primary" value="ADD"  style="" id="add_stock" />
								</div>
								</div>
						</div>

						<div class="row">
							<div class="col-md-12" id="opening_stock_history"></div>
						</div>
					</form>

			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
