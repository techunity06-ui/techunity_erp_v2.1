<div class="modal colored-header info" id="solid_allocate" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Allocate For Printing</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="sodiv_allocate">
						<div class="col-md-3">
							Sales Order No
						</div>
						<div class="col-md-3">
							<span id="so_no_allocate"> </span>
						</div>
						<div class="col-md-3">Sales Order No</div>
						<div class="col-md-3"><span id="so_date_allocate"> </span></div>
					</div>
					
					<div class="col-md-12">
						<div class="col-md-3">
							Product Name
						</div>
						<div class="col-md-3">
							<span id="pro_name_allocate"> </span>
						</div>
						<div class="col-md-3">Qty</div>
						<div class="col-md-3">
							<input type="number" class="form-control" onchange="open_so_trn_modal_pro();" value="" id="pqty_allocate" name="pqty_allocate" />
						</div>
					</div>
					
					<div class="col-md-12" id="solid_allocate_div"></div>
					<div class="col-md-12" id="solid_allocateshow_div"></div>

					<input type="hidden" id="sotrn_allo" name="sotrn_allo" value="" />
					<input type="hidden" id="product_id_allo" name="product_id_allo" value="" />
					<input type="hidden" id="sub_product_id_allo" name="sub_product_id_allo" value="" />
					<div class="col-md-12" style="text-align: center;">
						<button class="btn btn-primary" data-original-title="Save" data-toggle="tooltip" data-placement="top" type="button" onclick="save_solid_allocate();">Save</button>
					</div>

				</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-content -->
</div><!-- /.modal-content -->