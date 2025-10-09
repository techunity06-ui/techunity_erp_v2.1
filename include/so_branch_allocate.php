<div class="modal colored-header info" id="preview_so_branch_allocate_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Branch Allocation</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="so_allocation_add" action="javascript:;" method="post" name="so_allocation_add">
					<div class="row">
							<div class="col-md-12">
								<?php echo getBranchBox($dbcon, $branch_id, '', false, true, ''); ?>	
							</div>	
							<div class="col-md-12">
								<center>
									<button type="button" class="btn btn-success" data-original-title="Allocate Branch" data-toggle="tooltip" data-placement="top" onClick="add_branch()">Allocate Branch</button>	
								</center>
							</div>
					</div>
					<input type="hidden" id="ref_sales_order_trn_id" name="ref_sales_order_trn_id" value="">
				</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

