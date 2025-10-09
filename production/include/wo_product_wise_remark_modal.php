<div class="modal colored-header info" id="wo_product_wise_remark_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add Remark</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<input type='hidden' id='remark_rp_id'>
					<div class='col-md-12 mtop20'>
						<div class='col-md-12'>
						<strong>Product Remark</strong>
					</div>
					<div class='col-md-12 mtop20' style='padding:0px'>
					<textarea class='form-control' rows='5' id='product_remark'></textarea>
					</div>
					
					<div class='col-md-12' style='margin-top: 15px;'>
						<center>

							<button type='button' id='btn_prod_remark' name='btn_prod_remark' onClick='save_product_remark()' class='btn btn-success btn-space'>Save</button>
						</center>
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

