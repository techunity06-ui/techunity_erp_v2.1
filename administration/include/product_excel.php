<style type="text/css">
	.btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
		z-index:2;
		background-color: #bbdce6;
	}
	.control-label{
		font-weight: bold;
	}
</style>
<div class="modal colored-header info " id="modal-excel-product" role="dialog" data-keyboard="false" data-backdrop="static" style="overflow-y:auto;">
	<div class="modal-dialog modal-lg xlg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" accesskey="c" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Export Product Items</h3>				
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form role="form" id="item_export" action="<?=ROOT.'generate_export_product_csv'?>" method="post" name="item_export">
							<div class="row">
							    <div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Id</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_id" id="product_id1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_id" id="product_id2" autocomplete="off" value="Product Id"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Type</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_type" id="product_type1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_type" id="product_type2" autocomplete="off" value="Product Type"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Name</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_name" id="product_name1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_name" id="product_name2" autocomplete="off" value="Product Name"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Category</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_category" id="product_category1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_category" id="product_category2" autocomplete="off" value="Category"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Item Code</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_icode" id="product_icode1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_icode" id="product_icode2" autocomplete="off" value="Item Code"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Branch</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="branch_id" id="branch_id1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="branch_id" id="branch_id2" autocomplete="off" value="Branch"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Image</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="image_name" id="image_name1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="image_name" id="image_name2" autocomplete="off" value="Product Image"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Drawing No</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="drawing_id" id="drawing_id1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="drawing_id" id="drawing_id2" autocomplete="off" value="Drawing No"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Revision No</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="revision_id" id="revision_id1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="revision_id" id="revision_id2" autocomplete="off" value="Revision No"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>HSN Code</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_hsn" id="product_hsn1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_hsn" id="product_hsn2" autocomplete="off" value="HSN Code"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>CAT No</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="cat_no" id="cat_no1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="cat_no" id="cat_no2" autocomplete="off" value="CAT No"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Sale GST</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_sale_gst" id="product_sale_gst1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_sale_gst" id="product_sale_gst2" autocomplete="off" value="Sale GST"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Purchase GST</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_purchase_gst" id="product_purchase_gst1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_purchase_gst" id="product_purchase_gst2" autocomplete="off" value="Purchase GST"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Base Unit</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_base_unit" id="product_base_unit1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_base_unit" id="product_base_unit2" autocomplete="off" value="Base Unit"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Base Qty</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_base_qty" id="product_base_qty1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_base_qty" id="product_base_qty2" autocomplete="off" value="Base Qty"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Conv. Unit</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_conv_unit" id="product_conv_unit1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_conv_unit" id="product_conv_unit2" autocomplete="off" value="Conv. Unit"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Conv. Qty</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_conv_qty" id="product_conv_qty1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_conv_qty" id="product_conv_qty2" autocomplete="off" value="Conv. Qty"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Description</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_desc" id="product_desc1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_desc" id="product_desc2" autocomplete="off" value="Description"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Specification</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_spec" id="product_spec1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_spec" id="product_spec2" autocomplete="off" value="Specification"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Material</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_specification" id="product_specification1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_specification" id="product_specification2" autocomplete="off" value="Product Material"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Valuation</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_opening_valuation" id="product_opening_valuation1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_opening_valuation" id="product_opening_valuation2" autocomplete="off" value="Product Valuation"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Barcode</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_barcode" id="product_barcode1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_barcode" id="product_barcode2" autocomplete="off" value="Product Barcode"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Net Weight</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_net_weight" id="product_net_weight1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_net_weight" id="product_net_weight2" autocomplete="off" value="Net Weight"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Making Time</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_making_time" id="product_making_time1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_making_time" id="product_making_time2" autocomplete="off" value="Making Time"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>PO Lead Time</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_lead_time" id="product_lead_time1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_lead_time" id="product_lead_time2" autocomplete="off" value="PO Lead Time"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product GST</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_gst" id="product_gst1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_gst" id="product_gst2" autocomplete="off" value="Product GST"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Sale Rate</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_sale_rate" id="product_sale_rate1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_sale_rate" id="product_sale_rate2" autocomplete="off" value="Product Sale Rate"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Purchase Rate</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_purchase_rate" id="product_purchase_rate1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_purchase_rate" id="product_purchase_rate2" autocomplete="off" value="Product Purchase Rate"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Weight</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="weight" id="weight1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="weight" id="weight2" autocomplete="off" value="Weight"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Opening Stock</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_opening" id="product_opening1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_opening" id="product_opening2" autocomplete="off" value="Opening Stock"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Minimum Stock</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_min_stock" id="product_min_stock1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_min_stock" id="product_min_stock2" autocomplete="off" value="Minimum Stock"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Maximum Stock</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_max_stock" id="product_max_stock1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_max_stock" id="product_max_stock2" autocomplete="off" value="Maximum Stock"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Minimum Order</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_min_order" id="product_min_order1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_min_order" id="product_min_order2" autocomplete="off" value="Minimum Order"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Maximum Order</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_max_order" id="product_max_order1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_max_order" id="product_max_order2" autocomplete="off" value="Maximum Order"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>GRN Required?</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="is_grn" id="is_grn1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="is_grn" id="is_grn2" autocomplete="off" value="GRN Required"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Reorder Quantity</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="reorder_qty" id="reorder_qty1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="reorder_qty" id="reorder_qty2" autocomplete="off" value="Reorder Quantity"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Self Life Days</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="self_life_days" id="self_life_days1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="self_life_days" id="self_life_days2" autocomplete="off" value="Self Life Days"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Warranty Period</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="warrenty_period" id="warrenty_period1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="warrenty_period" id="warrenty_period2" autocomplete="off" value="Warranty Period"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Model No</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="model_no" id="model_no1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="model_no" id="model_no2" autocomplete="off" value="Model No"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Item Type</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="item_type" id="item_type1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="item_type" id="item_type2" autocomplete="off" value="Item Type"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Material Center</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_mat_center" id="product_mat_center1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_mat_center" id="product_mat_center2" autocomplete="off" value="Material Center"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Stock Count</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="product_stock_count" id="product_stock_count1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="product_stock_count" id="product_stock_count2" autocomplete="off" value="Stock Count"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Bom Required?</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="bom_required" id="bom_required1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="bom_required" id="bom_required2" autocomplete="off" value="Bom Required"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Batch Wise Stock Manage?</strong></label></div>
									<div class="col-md-6">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary">
												<input type="radio" name="batch_wise_stock_manage" id="batch_wise_stock_manage1" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary active">
												<input type="radio" checked name="batch_wise_stock_manage" id="batch_wise_stock_manage2" autocomplete="off" value="Batch Wise Stock Manage"> Yes
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="col-md-6"><label><strong>Product Status</strong></label></div>
									<div class="col-md-6">
										<select class="form-control" name="product_status" id="product_status">
											<option value="both" selected>Both</option>
											<option value="active">Active</option>
											<option value="inactive">Inactive</option>
										</select>
									</div>
								</div>
								<div class="clearfix" style="margin-bottom:10px;"></div>
							</div>
							<input type='hidden' name='mode' id='mode' value='add' />
							<button type="submit" class="btn btn-info">Submit</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>