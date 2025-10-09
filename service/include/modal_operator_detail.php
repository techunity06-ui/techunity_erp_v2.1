<div class="modal colored-header info" id="modal-operator-detail" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg" id="custom_sold_modal">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
			<h3>Operator Detail</h3>				
		</div>
		<div class="modal-body form">
			<form id="allocate_sold_product_form" name="allocate_sold_product_form" role="form" method="post" novalidate>	
				<div class="form-group"> 
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							
							<th class="text-center" width="20%">Name</th> 
							<th class="text-center" width="10%">Mobile</th> 
							<th class="text-center" width="5%"></th>
						</tr>
						<tr>
							
							<td style="vertical-align:top;">
								<input type="text" class="form-control" name="op_name" id="op_name" value="" />
							</td> 
							
							<td style="vertical-align:top;">
								<input type="text" class="form-control" name="op_mobile" id="op_mobile" value="" onkeypress="return isNumberKey(event)" maxlength="10" />
							</td> 
							<td style="vertical-align:top;"> 
								<input type="button" name="add_operator_btn" id="add_operator_btn" onClick="add_operator();"  class="btn btn-primary" value="Add"/>	
							</td>
							<input type='hidden' name='edit_id2' id='edit_id2' value='0' />
						</tr> 
					</table>				
				</div>
				<div class="col-md-12"></div>
				
				<!--<div id="trn_res"></div>-->
				<div class="panel-body">
					<div id="operator_table">
						
					</div>
				</div>
				
			</div>
			<div class="modal-footer" style="margin-top:25px;">
				<input type="hidden" name="op_comp_id" id="op_comp_id" value="" /> 
				<input type="hidden" name="op_cust_id" id="op_cust_id" value="" /> 
				<!--<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Close</button>--> 
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->