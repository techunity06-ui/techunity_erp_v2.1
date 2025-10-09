<div class="modal colored-header info" id="preview_so_allocate_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Sales order No : #<span id="apprv_ref_no"></span> ( Product Name : <span id="pname"></span> -- Qty : <span id="pqty"></span>)</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="so_allocation_add" action="javascript:;" method="post" name="so_allocation_add">
					<div class="row">
						<div class="col-md-12" id="mod_per_div_sec1" ></div>
					</div>
					<input type="hidden" id="ref_sales_order_trn_id" name="ref_sales_order_trn_id" value="">
					<input type="hidden" id="ref_product_id" name="ref_product_id" value="">
					<input type="hidden" id="ref_pending_qty" name="ref_pending_qty" value="">
					<input type="hidden" id="mode" name="mode" value="add">
				</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

