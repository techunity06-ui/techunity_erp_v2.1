<div class="modal fade full-width-modal-right in" id="jobwork_wise_qty_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg" style="width:490px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Jobwork Wise Qty</h3>
			</div>
			<div class="modal-body form">
				<?php 
					//$master_details=get_product_batch_stock($dbcon,'tbl_cost_center','cost_center_id','cost_center_name',' and isdelete=0');
					//$balance_type=getbalance_type_new($dbcon);
				?>
				<div class="row">
					<div id="workorder_data"></div>
				</div>
				<div class="row margin_row">
					<div class="col-md-12 ">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="wo_jobwork_table">
								<thead>
									<tr>
										<th>Jobwork No</th> 
										<th>Qty</th> 								
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
