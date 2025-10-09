<div class="modal colored-header info" id="solid_planing" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>So Planing</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12" id="sodiv">
						<div class="col-md-3">
							Sales Order No
						</div>
						<div class="col-md-3">
							<span id="so_no"> </span>
						</div>
						<div class="col-md-3">Sales Order No</div>
						<div class="col-md-3"><span id="so_date"> </span></div>
					</div>
					
					<div class="col-md-12">
						<div class="col-md-3">
							Product Name
						</div>
						<div class="col-md-3">
							<span id="pro_name"> </span>
						</div>
						<div class="col-md-3">Qty</div>
						<div class="col-md-3">
							<input type="number" class="form-control" onchange="open_so_trn_modal_pro();" value="" id="pqty" name="pqty" />
						</div>
					</div>
					
					<div class="col-md-12" id="solid_planning_div"></div>
					<input type="hidden" id="sotrn" name="sotrn" value="" />
					<input type="hidden" id="product_id" name="product_id" value="" />
					<div class="col-md-12" style="text-align: center;">
						<button class="btn btn-primary" data-original-title="Save" data-toggle="tooltip" data-placement="top" type="button" onclick="save_solid_planning();">Save</button>
					</div>

				</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-content -->
</div><!-- /.modal-content -->