<div class="modal colored-header info " id="bs-example-modal-preivew_statement" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>View Statement</h3>
				
			</div>
			<div class="modal-body form">
			<div class="row">
				<div class='col-md-12'>
					<div class="form-group col-md-6">
                                  <label class="control-label col-md-4">Choose Date</label>
                                  <div class="col-md-7">
                                       <div class="input-group date form_datetime-component">
										<?
									  $start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
									 ?>
                                        <input type="hidden" id="from_date"  value="<?=$start?>">
										 <input type="hidden" id="to_date"  value="<?=date('d-m-Y')?>">
         					 		        <input type="text" id="rep_date"  onChange="generate_report();" class="form-control datepikerdemo" value="">
											<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
                                      </div>
                                  </div>
                              </div>
						</div>	
			<div class="col-md-12" id="adv-table1">
			
			</div>
				<div class="col-md-3"></div>
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
					
					<!--Vendor row end-->	
					<input type='hidden' name='mode' id='mode' value='Add' />
					<input type='hidden' name='model' id='model' value='model' />				  
				</div>
			</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
