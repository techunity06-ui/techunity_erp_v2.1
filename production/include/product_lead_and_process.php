<div class="modal colored-header info " id="product_lead_and_process" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3><span id="mtype"> Product Lead  Time  </span> <span id="current_pname"></span></h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="current_stock_data" action="javascript:;" method="post" name="add_wo_product">
							<div class="clearfix"></div>
							<div class="col-md-12" >
								  <div class="col-md-8">
                                       <div class="form-group">
                                          <label for="Product Type" class="col-md-4 control-label">Lead Time</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="text" class="form-control" name="product_lead_time" id="product_lead_time" value="" /> ( In <?php $company_config = getCompanyConfiguration($dbcon, $id = false); if($company_config['resource_time'] == '0'){ echo  $resource_time = "Mintue";} else { echo $resource_time = "Days"; } ?> ..)
                                              <input type="hidden" class="form-control" name="product_id" id="product_id" value="" /> 
                                              <input type="hidden" class="form-control" name="stock_check_flag_modal" id="stock_check_flag_modal" value="" /> 
                                              <input type="hidden" class="form-control" name="lead_time_process_modal" id="lead_time_process_modal" value="" />  
                                                <input type="hidden" class="form-control" name="rp_id_modal" id="rp_id_modal" value="" /> 
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4" >
                                   
                                       <div class="form-group">
                                          
                                          <div class="col-md-8 col-xs-11">
                                            <input type="button" class="btn btn-primary" value="ADD"   onclick="add_lead_time();"  />
                                          </div>
                                       </div>
                                    </div>
							</div>
						</form>
					</div>
				</div>	
			</div>
		</div>
	</div>
</div>


