<?php
$getspecialConfiguration=getspecialConfiguration($dbcon);
	$remark_req ='data-required = "no"';
		if($getspecialConfiguration['hermattic_permission']=="1") {
			$remark_req = 'data-required = "yes"';
	}

?>
<div class="modal colored-header info" id="store_accept_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">

				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Store Accept</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div id="mod_per_div_sec">
						<form class="form-horizontal" role="form" id="gir_add" action="javascript:;" method="post" name="gir_add" enctype="multipart/form-data">
							<div class="col-md-12" style="margin-top:10px;">
								<div class="col-md-6">
									<label class="col-md-5 control-label" style="white-space:nowrap;font-weight: 600;">Store Accept No *</label>
									<div class="col-md-6" style="padding-left: 9px;">
										<input type="text" id="store_accept_no" name="store_accept_no" class="form-control" title="Store Accept No" readonly value="" placeholder="Store Accept No">
									</div>  
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="col-md-5 control-label" style="white-space:nowrap;font-weight: 600;" >Store Accept Date *</label>
										<div class="col-md-6 col-xs-11">
											<input id="store_accept_date" name="store_accept_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$date?>" placeholder="Store Accept Date">
										</div>
									</div>
								</div>	
							</div>
							<div class="col-md-12" style="margin-top:10px;">
								<div class="col-md-6">
									<label class="col-md-5 control-label" style="white-space:nowrap;font-weight: 600;">Product Name</label>
									<div class="col-md-6" style="padding-left: 9px;">
										<span id="proname" style="color: #609708;font-size: 17px;font-weight: 600;"></span>  
									</div>  
								</div>
								<div class="col-md-6">
									<label class="col-md-5 control-label" style="white-space:nowrap;font-weight: 600;">Qty</label>
									<div class="col-md-6" style="padding-left: 9px;">
										<span id="tqty" style="color: #609708;font-size: 17px;font-weight: 600;"></span>  <span class="unitname" style="color: #609708;font-size: 17px;font-weight: 600;"></span></br>
										<span id="tqty2" style="color: #609708;font-size: 17px;font-weight: 600;"></span>  <span class="unitname2" style="color: #609708;font-size: 17px;font-weight: 600;"></span>
									</div>  
								</div>
							</div>
							<div class="col-md-12" style="margin-top:10px;">
								<div class="col-md-6">
									<label class="col-md-5 control-label" style="white-space:nowrap;font-weight: 600;">Reference No</label>
									<div class="col-md-6" style="padding-left: 9px;">
										<span id="grnno" style="color: #609708;font-size: 17px;font-weight: 600;"></span>  
									</div>  
								</div>
								<div class="col-md-6">
									<label class="col-md-5 control-label" style="white-space:nowrap;font-weight: 600;">Reference Date</label>
									<div class="col-md-6" style="padding-left: 9px;">
										<span id="grndate" style="color: #609708;font-size: 17px;font-weight: 600;"></span>
									</div>  
								</div>
							</div>
							<div class="col-md-12" style="margin-top:10px;">
								<div class="col-md-6">
									<label class="col-md-5 control-label" style="white-space:nowrap;font-weight: 600;">Godown</label>
									<div class="col-md-6" style="padding-left: 9px;">
										<span id="godwn" style="color: #609708;font-size: 17px;font-weight: 600;"></span>
									</div>  
								</div>
							</div>
							
							<div class="col-md-12" style="margin-top:10px;" id="so_details">
							</div>
							<div class="col-md-12" style="margin-top:10px;">
								<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
									<tr>
										<th width="40%" class="text-center">Godown</th>
										<th width="40%" class="text-center">Quantity</th>
										<th width="10%" class="text-center">Unit</th>
									</tr>
									<tr>
										<td>
											<select class="form-control" name="godown_id" id="godown_id"   title="Select Godown">
												<?=get_all_godown($dbcon,'')?>    
											</select>
										</td>
										<td>
											<input type="number" id="aqty" name="aqty" class="form-control" title="Quantity" value="" placeholder="Quantity" >
										</td>
										<td><span class="unitname" style="color: #609708;font-size: 17px;font-weight: 600;"></span></td>
										<td>
											<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
										</td>
									</tr>

								</table>
							</div>
							<div class="col-md-12" style="margin-top:10px;">
								<div id="sale_productdata"></div>
							</div>
							<div class="col-md-12" style="margin-top:10px;">
								<div class="form-group">
									<label class="col-md-2 control-label" style="white-space:nowrap;font-weight: 600;" >Remarks </label>
									<div class="col-md-6 col-xs-11">
										<textarea id="remark" name="remark"  <?=$remark_req?> class="form-control" rows="3"></textarea> 
									</div>
								</div>
							</div>
							<div class="col-md-12" style="margin-top:10px;">
								<center>
									<button type="button" class="btn btn-success" id="save" name="save" onclick="save_store_accept();" >Submit</button>
								</center>
								<input type="hidden" name="unit_id" id="unit_id" value="" />
								<input type="hidden" name="total_qty" id="total_qty" value="" />
								<input type="hidden" name="grn_trn_id" id="grn_trn_id" value="" />
								<input type="hidden" name="store_accept_id" id="store_accept_id" value="" />
								<input type="hidden" name="edit_id" id="edit_id" value="" />
								<input type="hidden" name="product_id" id="product_id" value="" />
								<input type="hidden" name="batch_id" id="batch_id" value="" />
								<input type="hidden" name="batch_no" id="batch_no" value="" />
								<input type="hidden" name="reprocess_qc" id="reprocess_qc" value="" />
							</div>
						</form>
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->