<div class="modal colored-header info" id="work_order_details" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Work Order details</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					 <div id="po_work_order" class="tab-pane active" >
                                          <section class="panel">
                                             <div class="panel-body bio-graph-info">
                                                <h1>Work Order Details</h1>
                                                <div class="row">
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">W.O. No.</label>
                                                            <div class="col-md-8">
                                                               <input type="text" class="form-control" id="po_req_no" name="po_req_no" title="Enter WO. No."  placeholder="Wo. No." value="" readonly >
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >W.O. Date </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="po_req_date" name="po_req_date" type="text" class="form-control default-date-picker" title="Date" value="" readonly placeholder="WO. Date">
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">W.O. Type </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <select class="select2" name="work_order_type" id="work_order_type" required title="Select Tax">
                                                                  <option value="">None</option>
                                                               </select>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">S.O. No.</label>
                                                            <div class="col-md-8">
                                                               <input type="text" class="form-control" id="so_no" name="so_no" title="Enter WO. No."  placeholder="S.O. No." value="" readonly >
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >S.O. Date </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="so_date" name="so_date" type="text" class="form-control default-date-picker" title="Date" value="" placeholder="S.O. Date" readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Status </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="status" name="status" type="text" class="form-control default-date-picker" value="" readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Select Vendor </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input type="text" name="vender_id" id="vender_id" class="form-control" value="" readonly> 
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Cust. P.O. No.</label>
                                                            <div class="col-md-8">
                                                               <input type="text" class="form-control" id="vendor_po_number" name="vendor_po_number" title="Enter Vendor P.O. No."  placeholder="Vendor P.O. No." value="" readonly >
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >P.O. Date </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="vender_po_date" name="vender_po_date" type="text" class="form-control default-date-picker" title="Date" value="" placeholder="Vendor P.O. Date" readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >W.O. Start. Date </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="order_start_date" name="order_start_date" type="text" class="form-control default-date-picker" title="Date" value="" placeholder="Order Start Date" readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >Delivery Date </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="order_delivery_date" name="order_delivery_date" type="text" class="form-control default-date-picker" title="Date" value="" placeholder="Order Delivery Date" readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >D.S. No </label>
                                                            <div class="col-md-8">
                                                               <input type="text" class="form-control" id="ds_number" name="ds_number" title="Enter D.S. No."  placeholder="D.S. No." value="" readonly >
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label">Product Type</label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input type="text" name="product_type" id="product_type" class="form-control" value="" readonly>   
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label">Select Product </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input type="text" name="product_id" id="product_id" class="form-control" value="" readonly>   
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label">Description</label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <textarea class="form-control" name="item_description" id="item_description" readonly>ASSEMBLY ITEM</textarea>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label">BOM No.</label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="bom_no" name="bom_no" type="text" class="form-control" title="BOM No." value="<?=($rel['bom_no']?$rel['bom_no']:'22233')?>" placeholder="Bom No." readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">W.O. Qty </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="order_qty" name="order_qty" type="number" class="form-control" title="W.O. Qty" value="<?=($rel['order_qty']?$rel['order_qty']:'33')?>" placeholder="W.O. Qty" readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Reverse Charge </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <select class="select2" name="reverse_charge" id="reverse_charge" required title="Select Reverse" readonly>
                                                               <?=reverse_type_bill($dbcon, $rel['reverse_charge']);?> 
                                                               </select>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">UOM </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <select class="select2" name="product_base_unit" id="product_base_unit"  title="Select Unit" >
                                                               <?php if($mode=='Edit') { echo getunit($dbcon,$rel['product_base_unit']); } else { echo getunit($dbcon,3); } ?>
                                                               </select>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label">Remark</label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <textarea class="form-control" name="remark" id="remark" readonly></textarea>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                          </section>
                                       </div>
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

