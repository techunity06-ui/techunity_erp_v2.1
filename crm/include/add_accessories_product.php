
<!--<div class="modal colored-header info " id="bs-batch_wise_stock-modal1" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">-->

<div class="modal fade full-width-modal-right in" id="bs-batch_wise_stock-modal1" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="width:1200px; height:1500px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Accessories Product </h3>
			</div>
			<div class="modal-body form">
				<?php 
					//$master_details=get_product_batch_stock($dbcon,'tbl_cost_center','cost_center_id','cost_center_name',' and isdelete=0');
					//$balance_type=getbalance_type_new($dbcon);
				?>
				<div class="row">
					<div id="batch_data"></div>
				</div>
				<div class="row margin_row">
					<div class="col-md-12 ">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="batch_stock_table">
								<thead>
									<tr>
										<th>Product Name</th> 
										<th>Qty</th> 
										<th>Rate</th> 
										<th>Amount</th> 
										<th>Description</th> 										
										<th class="hidden-phone">Action</th>					  
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="col-md-12" style="margin-top: 20px;">
					<center>						
						<input type="button"  name="m_addrow" id="m_addrow" onClick="return add_field();"  class="btn btn-primary addbutton" value="Add"/>
						
						&nbsp;
						<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</center>
				</div>
	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
