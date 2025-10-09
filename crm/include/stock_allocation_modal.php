<div class="modal fade full-width-modal-right in" id="bs-stock_allocation-modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Reserve Stock Entry</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="current_stock_data" action="javascript:;" method="post" name="add_wo_product">
							<div class="clearfix"></div>
							<div class="row  text-center" style="background-color: #eee; padding:5px">
								<h3 style="color:green">Reserve Quantity : <span id="show_res_qty"></span></h3>
							</div>
							<div  id="sstock" class="mtop20">
									
							</div>
							<input type="hidden" name="sales_ordertrn_id_model" id="sales_ordertrn_id_model" />
							<input type="hidden" name="validate_qty" id="validate_qty" />
						</form>
					</div>
				</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>