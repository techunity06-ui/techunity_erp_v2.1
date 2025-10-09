<div class="modal fade full-width-modal-right in" id="bs-batch_wise_stock-modal" role="dialog" data-keyboard="false" data-backdrop="static" style="z-index: 1050;">
	<div class="modal-dialog modal-lg" style="width:75%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Deduct Batch Wise Qty </h3>
			</div>
			<div class="modal-body form">
				<?php 
					//$master_details=get_product_batch_stock($dbcon,'tbl_cost_center','cost_center_id','cost_center_name',' and isdelete=0');
					//$balance_type=getbalance_type_new($dbcon);
				?>
				<div class="row margin_row mtop20 text-center">
					<div id="col-md-12">
								<h3>
									<span>Deduct Base QTY : </span>
									<span id="modal_base_qty"> </span>
									<span style="margin-left:30px">Deduct Conv QTY : </span>
									<span id="modal_conv_qty"> </span>
								</h3>
					</div>
				</div>
				<div class="row margin_row mtop20">
					<div class="col-md-12 ">
						<div class="adv-table" id="batch_data">
						</div>
					</div>
					<input type="hidden" id="modal_enter_base_qty" name="modal_enter_base_qty">
					<input type="hidden" id="modal_enter_conv_qty" name="modal_enter_conv_qty">
					<input type="hidden" id="modal_product_id" name="modal_product_id">
					<input type="hidden" id="modal_p_id" name="modal_p_id">
					<input type="hidden" id="modal_rp_id" name="modal_rp_id">
					<input type="hidden" id="modal_process_stock" name="modal_process_stock">
					<input type="hidden" id="modal_unit_id" name="modal_unit_id">
					<input type="hidden" id="modal_conv_unit_id" name="modal_conv_unit_id">
					<input type="hidden" id="modal_edit_id" name="modal_edit_id">
					<input type="hidden" id="modal_type" name="modal_type">
					
				</div>
				<div class="col-md-12" style="margin-top: 20px;">
					<center>						
						<input type="button"  name="m_addrow" id="m_addrow" onClick="return add_batch_wise_deduct_qty();"  class="btn btn-primary addbutton" value="Add"/>
						
						&nbsp;
						<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</center>
				</div>
	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
