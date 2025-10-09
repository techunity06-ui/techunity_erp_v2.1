<div class="modal colored-header info" id="allocate_req_product" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
			<h3>Choose Required Products</h3>				
		</div>
		<div class="modal-body form">
			<form id="Formadjuststock" name="Formadjuststock" role="form" method="post" novalidate>				
				<div class="form-group"> 
					<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="70%">Product Detail</th>
							<th class="text-center" width="20%">Qty.</th>
							<th class="text-center" width="10%"></th>
						</tr>
						<tr id="field1">
							<td style="vertical-align:top;">
								<select class="select2" title="Select product" name="req_product_id" id="req_product_id">
									<?=get_product_typewise($dbcon,'','2,3,4,5')?>
								</select>
							</td>
							<td style="vertical-align:top;">
								<input type="number" title="Enter Qty" min="0" id="req_product_qty" name="req_product_qty" class="form-control"/>
							</td>
							<td style="vertical-align:top;"> 
								<input type="button" name="addrow" id="addrow" onClick="return add_req_field();"  class="btn btn-primary" value="Add"/>	
							</td>
							<input type='hidden' name='edit_id1' id='edit_id1' value='' />
						</tr>
						
					</table>				
				</div>
				<div class="col-md-12"></div>
				
				<!--<div id="trn_res"></div>-->
				<div class="panel-body">
					<div class="adv-table">
						<table class="display table table-bordered table-striped" id="req-pro-table">
							<thead>
								<tr>
									<th>Sr. NO.</th>
									<th>Product Name</th>
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
			<div class="modal-footer" style="margin-top:25px;">
				<input type="hidden" name="model_id" id="model_id" value="" /> 
				<!--<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Close</button>--> 
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->