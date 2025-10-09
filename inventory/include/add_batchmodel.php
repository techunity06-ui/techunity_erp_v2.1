<div class="modal fade full-width-modal-right in" id="bs-po_dispatch_date-modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Delivery Date </h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<!--<div class="col-md-12">-->

					<form class="form-horizontal" role="form" id="dispatch" action="javascript:;" method="post" name="dispatch">
						<div class="col-md-12" id="model_product_name" style="font-size: 20px;font-weight: 600;color: red;text-decoration: underline;" ></div>
						<div id="date_des" > </div>
						
						<input type="hidden" name="m_trn_id" id="m_trn_id" value="" />
						<input type="hidden" name="m_qty" id="m_qty" value="" />
						
						<div class="col-md-12" style="margin-top: 20px;">
							<center>
								<!--<button type="button" class="btn btn-success">Submit</button> -->
								
								<input type="button"  name="m_addrow" id="m_addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
								
								&nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</center>
						</div>
					<!--	<input type='hidden' name='main_product_qty' id='main_product_qty' value='' />-->
						<input type='hidden' name='mode' id='mode' value='Add' />
						<input type='hidden' name='model' id='model' value='model' />
						<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
					</form>
					<!--</div>-->
				</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>


<div class="modal colored-header info" id="bs-batch-modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Batch Wise Data</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-6">
							<label class="control-label" style=""> <strong>Product : <span id="batch_product_name"> </span></strong></label>
						</div>
						<div class="col-md-6 m-bot15" style="display:flex;">
							<label class="control-label"><strong> Quantity : <span id="batch_qty_show"> </span><span style="color: green; margin-left:10px;" id="batch_unit_name"></span></strong></label>
							<span style="margin-left:10px" class="diff_unit"><strong>|</strong></span>
							<label class="control-label diff_unit" style="margin-left: 10px;"><strong><span id="diff_batch_qty_show"> </span><span style="color: green; margin-left:10px;" id="diff_batch_unit_name"></span></strong></label>
						</div>
					</div>

					<div class="col-md-12">
						<input type='hidden' name='main_product_qty' id='main_product_qty' value='' />
						<input type='hidden' name='product_id' id='product_id' value=''/>
						<input type='hidden' name='selected_row_cnt' id='selected_row_cnt' value=''/>
						<input type='hidden' name='grn_no' id='grn_no' value=''/>
						<input type='hidden' name='batch_unit_id' id='batch_unit_id' value='' />
						<input type='hidden' name='diff_batch_unit_id' id='diff_batch_unit_id' value='' />
						<input type='hidden' name='diff_unit_type' id='diff_unit_type' value='' />
						<input type='hidden' name='is_diff_unit' id='is_diff_unit' value='' />
						<input type='hidden' name='purchaseordertrn_id' id='purchaseordertrn_id' value='' />
						<form class="form-horizontal" role="form" id="batch_data" action="javascript:;" method="post" name="batch">		
					
							<!--Vendor row end-->	
							<input type='hidden' name='mode' id='mode' value='Add' />
							<input type='hidden' name='model' id='model' value='model' />
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />			
						</form>
						<div class="col-md-3"></div>
						<button id="batch_save_btn" type="submit" class="btn btn-success" onclick="save_batch_data();">Submit</button> &nbsp;
						<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

