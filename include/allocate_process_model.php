<div class="modal colored-header info" id="alloc_process_modal" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
			<h3>Allocate Process For <span id="alloc_product_id"></span></h3>				
		</div>
		<div class="modal-body form">
			<form id="Formadjuststock" name="Formadjuststock" role="form" method="post" novalidate>				
				<div class="form-group"> 
					<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="20%">Process Type</th>
							<th class="text-center" width="40%">Process Name</th>
							<th class="text-center" width="20%">Making Time( In Minute.)</th>
							<th class="text-center" width="10%"></th>
						</tr>
						<tr id="field1">
							<td style="vertical-align:top;">
								<select class="select2" title="Select Process" name="process_type_id" id="process_type_id" onchange="get_all_process(this.value)">
									<option value="">--Select Process Type--</option>
									<?=get_process_type($dbcon,'0');?>
								</select>
							</td>
							<td style="vertical-align:top;">
								<select class="select2" title="Select Process" name="process_id" id="process_id">
										
								</select>
							</td>
							<td style="vertical-align:top;">
								<input type="number" title="Enter Making Time" min="0" id="pr_make_time" name="pr_make_time" class="form-control"/>
							</td>
							<td style="vertical-align:top;"> 
								<input type="button" name="addrow" id="addrow" onClick="return add_procecss_product();"  class="btn btn-primary" value="Add"/>	
							</td>
							<input type='hidden' name='edit_id1' id='edit_id1' value='' />
							<input type='hidden' name='process_product_id' id='process_product_id' value='' />
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
									<th>Process Type Name</th>
									<th>Process Name</th>
									<th>Making Time ( In Minute.)</th>
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