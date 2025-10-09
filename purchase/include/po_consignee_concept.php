<div class="modal colored-header info" id="modal-add-consignee-concept" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Shipping Address</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<center>
						<label class="radio-inline">
					      <input type="radio" name="con_type" value="1" <?php if($rel['con_type']=='1'){ echo "checked";}?> onchange="cons_type()">Unit
					    </label>
					    <label class="radio-inline">
					      <input type="radio" name="con_type" value="2" <?php if($rel['con_type']=='2'){ echo "checked";}?> onchange="cons_type()">Vendor
					    </label>
					    <label class="radio-inline">
					      <input type="radio" name="con_type" value="3" <?php if($rel['con_type']=='3'){ echo "checked";}if($mode=='Add' && $viewmode=='Add'){echo "checked";}?> onchange="cons_type()">Manual
					    </label>
						</center>
					</div>
					<div class="col-md-12" style="margin-top:30px" >
						<div class="col-md-6" id="con_ve">
							<div class="form-group">
								<label class="col-md-4 control-label">Choose Vendor</label>
                               	<div class="col-md-8">
                                    <select class="select2" name="con_vender_id" id="con_vender_id" required title="Select Vender" onchange="get_vendor_address(this.value)">
                                        <?=getcust($dbcon,$rel['con_vender_id'],$purchase_party_show);?> 
                                    </select>
                                </div>
							</div>
						</div>
						<div class="col-md-6" id="con_uni">
							<div class="form-group">
								<label class="col-md-4 control-label"> Unit </label>
                               	<div class="col-md-8">
                               		<select class="select2" name="con_branch" id="con_branch" onchange="get_branch_address(this.value)">
                                        <?=get_branch($dbcon,$rel['con_branch'])?>
                                    </select>
                               	</div>
                            </div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="col-md-12">
							<div class="form-group">
								<label class="col-md-2 control-label">Address</label>
								<div class="col-md-8">
									<textarea class="form-control" placeholder="Address" name="con_address" id="con_address" ><?=$rel['con_address']?>
									</textarea>
								</div>
						</div>
					</div>

					<div class="col-md-12">
						<center>
							<button type="button" class="btn btn-success" onclick ="close_consignee_concept()">Done</button>
						</center>
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->