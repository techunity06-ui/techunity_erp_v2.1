<div class="modal fade full-width-modal-left in" id="bs-batch_wise_stock_deduct-modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="width:490px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Batch Wise Deduct Qty </h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<h4><u><span id="produname_deduct" style="color:red"></span></u></h4>
				</div>
				<?php 
					//$master_details=get_product_batch_stock($dbcon,'tbl_cost_center','cost_center_id','cost_center_name',' and isdelete=0');
					//$balance_type=getbalance_type_new($dbcon);
				?>
				<div class="row">
					<div id="batch_data"></div>
				</div>
				<div class="row margin_row">
					<div class="col-md-12" id="datatablededuct">
						
					</div>
				</div>
				<div class="col-md-12" style="margin-top: 20px;">
					<center>						
						<input type="button"  name="m_addrow" id="m_addrow" onClick="return add_field_deduct();"  class="btn btn-primary" value="Add"/>
						
						&nbsp;
						<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</center>
				</div>
	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
