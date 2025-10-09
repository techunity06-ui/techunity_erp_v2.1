<div class="modal fade full-width-modal-right in" id="bs-po_dispatch_date-modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Delivery Date </h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<!--<div class="col-md-12">-->
						<form class="form-horizontal" role="form" id="dispatch" action="javascript:;" method="post" name="dispatch">
							<div class="col-md-12" id="model_product_name" style="font-size: 20px;font-weight: 600;color: red;text-decoration: underline;" ></div>

							<div class="col-md-12" style="margin-top:10px;margin-bottom: 10px;">
								<select class="form-control"  title="Select Unit" placeholder="Unit" name="unit_wise" id="unit_wise" onchange="delivery_schedule();">
                                    <?//=getunit($dbcon,0);?>
                                    <option value="0">Select Unit</option>
                                </select>
							</div>
							<div id="date_des" > </div>
							
							<input type="hidden" name="m_trn_id" id="m_trn_id" value="" />
							<input type="hidden" name="m_qty" id="m_qty" value="" />
							
							<div class="col-md-12" style="margin-top: 20px;">
								<center>
									<!--<button type="button" class="btn btn-success">Submit</button> -->
									
									<input type="button"  name="m_addrow" id="m_addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
									
									&nbsp;
									<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
								</center>
							</div>
							<input type='hidden' name='mode' id='mode' value='Add' />
							<input type='hidden' name='model' id='model' value='model' />
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
						</form>
					<!--</div>-->
				</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
