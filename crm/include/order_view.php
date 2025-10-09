<div class="modal colored-header info" id="oreder_review_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Sales Order No : #<span id="ref_sale_ord_no"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" >
						<div class="col-md-6">
							<select class="select2" name="sales_product" id="sales_product" >
								<option value="">Choose Product</option>	
												
							</select>
						</div>
						<div class="col-md-6">
							
						</div>
					</div>
					<div class="col-md-12 mtop20">
						<center>
							<button class="btn btn-success" onclick="order_review_add()"> Add</button>
							<button class="btn btn-warning" onclick="order_review_print()"> Print</button>
						</center>
					</div>
				</div>
			</div>	
		</div>
		<!-- <input type="hidden" id="ref_mod_id" name="ref_mod_id" value=""> -->
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->