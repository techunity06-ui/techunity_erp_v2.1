<div class="modal colored-header info" id="preview_bom_add_process_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add Process</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-6">
						<form class="form-horizontal" role="form" id="bom_add_process" action="javascript:;" method="post" name="bom_add_process">
							<div class="row">
								<div id="mask1" class="hidden" style="height: 681px;">
									<div style="position:fixed;left: 10%;top: 20%;margin-left: -25%px;">
										<img src="<?=ROOT?>img/loading_lg.gif">
										<h1> Loading ... </h1>
									</div>
							    </div>
								<div class="col-md-12" id="mod_per_div_add_process" ></div>
							</div>
						</form>
					</div>
					<div class="col-md-6">
						<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Process Name *</label>
									<div class="col-md-8 col-xs-11">
										 <select class="select2 selectoption" name="prod_process_id" id="prod_process_id" onchange="check_duplicate_process(this.value)">
					                  
										<?php  echo get_all_process($dbcon,$id) ?> 
										
					                  </select>

									</div>
								</div>
							</div>
							<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Priority *</label>
									<div class="col-md-8 col-xs-11">
										 <label for="process_priority" class="form-control process_priority_label">1</label>
										 <input type="hidden" class="form-control process_priority" name="process_priority" id="process_priority" />

									</div>
								</div>
							</div>
							<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Type *</label>
									<div class="col-md-8 col-xs-11">
										  <select class="form-control" name="process_type_m" id="process_type_m" onChange="manage_resource(this.value);">
					                     <option value="">--Select Process Type--</option>
					                     <option value="1">Inhouse</option>
					                     <option value="2">Outside</option>
					                  </select>

									</div>
								</div>
								</div>
							<div class="col-md-12 m-bot15 processRate_label_manage">
								<div class="form-group">
									<label class="col-md-4 control-label">Rate</label>
									<div class="col-md-8 col-xs-11">
										 <input type="number" class="form-control numbersOnly" name="process_rate" id="process_rate" onkeydown="return numericonly(event)"/>

									</div>
								</div>
								</div>
							<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Time  (In Min.) </label>
									<div class="col-md-8 col-xs-11">
										<input type="number" class="form-control numbersOnly" name="process_time" id="process_time" onkeydown="return numericonly(event)" />
									</div>
								</div>
								</div>
							<div class="col-md-12 m-bot15 resource_label_manage">
								<div class="form-group">
									<label class="col-md-4 control-label">Resource Name </label>
									<div class="col-md-8 col-xs-11">
										<select class="select2 selectoption" name="resource_id" id="resource_id">
					                  <?php  echo get_all_resource($dbcon,'') ?>
					                  </select>

									</div>
								</div>
								</div>
							<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Loss (In %) </label>
									<div class="col-md-8 col-xs-11">
										  <input type="number" class="form-control numbersOnly" name="process_loss" id="process_loss" onkeyup="check_process_loss(this)" value="" />

									</div>
								</div>
								</div>
							<div class="col-md-12 m-bot15">
								<div class="form-group">
									<label class="col-md-4 control-label">Scrap Tol. (+)</label>
									<div class="col-md-8 col-xs-11">
										 <input type="number" class="form-control numbersOnly" name="process_scrap_tolerance_plus" id="process_scrap_tolerance_plus" onkeyup="check_scrap_tolerance(this)" />

									</div>
								</div>
								</div>
							<div class="col-md-12 mbot30">
								<div class="form-group">
									<label class="col-md-4 control-label">Scrap Tol. (-) *</label>
									<div class="col-md-8 col-xs-11">
										  <input type="number" class="form-control numbersOnly" name="process_scrap_tolerance_minus" id="process_scrap_tolerance_minus" onkeyup="check_scrap_tolerance(this)" />

									</div>
								</div>
							</div>
							<input type="hidden" name="direct_product_id" id="direct_product_id" value="">
							<input type="hidden" name="rp_id" id="rp_id" value="">
							<div class="col-md-12 text-center">
								<div class="form-group">
									<input type="button" class="btn btn-primary" value="ADD PROCESS"  style="" onclick="add_process_value()" id="add_process" />
								</div>
								</div>
						</div>
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

