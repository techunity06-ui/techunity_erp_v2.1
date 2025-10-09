<div class="modal colored-header info " id="modal-add-product" role="dialog" data-keyboard="false" data-backdrop="static">
   <div class="modal-dialog modal-lg xlg" style="width: 95%;height: 60%;">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="btn_close  close md-close" accesskey="c" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Add Product</h3>
         </div>
         <div class="modal-body form">
            <section class="panel">

               <div class="panel-body">
                  <form role="form" id="product_add" action="javascript:;" method="post" name="product_add">
                     <input type="hidden" name="direct_product_add" value="1">
                     <input type="hidden" name="product_add_type" id="product_add_type" value="">
                     <div class="col-md-12" style="padding-top: 25px;">
                        <div class="col-md-12 margin_row">


                           <div class="col-md-4 typeled" style="display: none;">
                              <!-- add pathik -->
                              <div class="form-group">

                                 <label for="Product Type" class="col-md-4 control-label">Select Ledger*</label>
                                 <div class="col-md-8">
                                    <select class="select2" name="ledger_id" id="ledger_id" title="Select Ledger">
                                       <?= get_ledger($dbcon, $rel['ledger_id'], " and l_group in (16,17,18,19,20)"); ?>
                                    </select>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 margin_row">
                           <!--  Start jayesh C  14-03-2024 dynamic data from database  -->
                           <div class="col-md-4">
                              <div class="form-group">
                                 <label for="Product Type" class="col-md-4 control-label">Product Type*</label>
                                 <div class="col-md-8 col-xs-11">
                                    <select class="select2" id="product_type" name="product_type" onchange="pro_status(this.value);get_product_code(this.value);">
                                       <?php echo get_product_type_company($dbcon, '', ''); ?>
                                    </select>
                                 </div>
                              </div>
                           </div>

                           <div class="col-md-4">
                              <div class="form-group">
                                 <label for="opening stock" class="col-md-4 control-label">Select Category</label>
                                 <div class="col-md-8 col-xs-11">
                                    <select class="select2" name="product_category" id="product_category">
                                       <?= get_all_category($dbcon, ''); ?>
                                    </select>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-4">
                              <div class="form-group">
                                 <label for="Product Type" class="col-md-4 control-label">Item Code</label>
                                 <div class="col-md-8 col-xs-11">
                                    <input type="text" class="form-control" id="product_icode" name="product_icode" placeholder="Item Code" value='' />
                                    <input type="hidden" class="form-control" id="product_icode_code" name="product_icode_code" value="" readonly />
                                 </div>
                              </div>
                           </div>
                        </div>
                        <?phpif ($getspecialConfiguration['power_drive'] == 1) {
                           $query_field = "select * from tbl_item_master_field where item_master_field_status=0 and company_id=" . $_SESSION['company_id'] . " order by priority ASC";
                           $res_field = $dbcon->query($query_field);
                           $ro_cnt = brp_mysqli_num_rows($res_field);
                           $field = 1;
                           $counter = 1;
                           while ($row_field = brp_mysqli_fetch_array($res_field)) {
                              $field_name = $row_field['item_master_field_db_name'];
                              if ($field == 1) { ?>
                                 <div class="col-md-12 margin_row" style="margin-top: 10px;">
                              <?php} ?>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label"><?= $row_field['item_master_field'] ?>*</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2 dynamic_field" name="<?= $row_field['item_master_field_db_name'] ?>" id="field_id<?= $field ?>" title="<?= $row_field['item_master_field'] ?>" onchange="generate_product_name();">
                                                <option value="" data-pcode="">--CHOOSE <?= $row_field['item_master_field'] ?>--</option>
                                                <?= get_field_value($dbcon, $rel_field[$field_name], $row_field['item_master_field_id']) ?>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                              <?phpif ($ro_cnt == $field) { ?>
                                 </div>
                              <?php} else {
                                 if ($counter == 3) {
                                    $counter = 0;
                              ?>
                                 </div>
                                 <div class="col-md-12 margin_row">
                              <?php}
                              } 
                              $field++;
                              $counter++;
                           } ?>
                           <input type="hidden" name="dynamic_field" id="dynamic_field" value="<?=$field-1?>">
                        <?} ?>
                        
            <br><br><br>
            <div class="col-md-12 margin_row">
               <div class="col-md-4">
                  <div class="form-group">
                     <label for="Product Type" class="col-md-4 control-label">Product Name*</label>
                     <div class="col-md-8 col-xs-11">
                        <input type="text" class="form-control" id="product_name" name="product_name" placeholder="Product Name" value="" />
                     </div>
                  </div>
               </div>
               <div class="col-md-4">
                  <?php echo getBranchBox($dbcon, $branch_id, '', false, true, '', '4', '8', '', ''); ?>
               </div>

               <div class="col-md-4">

                  <div class="form-group">
                     <label for="Product Image" class="col-md-4 control-label">Product Image</label>
                     <div class="col-md-8 col-xs-11">
                        <input type="file" name="image_name" id="image_name" accept="image/*" />
                        <span class="text-info"> NOTE : Image size 300 X 200 </span>

                     </div>
                  </div>
               </div>
            </div>
            <br><br><br>
            <div class="col-md-12 margin_row">
               <div class="col-md-4">
                  <div class="form-group">
                     <label for="Product Type" class="col-md-4 control-label">HSN Code</label>
                     <div class="col-md-6 col-xs-11">
                        <select class="select2" name="product_hsn" id="product_hsn" title="Select HSN Code" onchange="getGst(this.value);">
                           <?= get_hsn($dbcon, '',''); ?>
                        </select>
                     </div>
                     <div class="col-md-2 col-xs-1">
                        <a class="btn btn-primary" title="Add HSN Code" data-toggle="tooltip" data-id="2" data-placement="top" href="javascript:void(0)" onclick="add_hsn_invoice()"><i class="fa fa-plus"></i></a>
                     </div>
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="form-group">
                     <label for="Product Type" class="col-md-4 control-label">Sale GST</label>
                     <div class="col-md-8 col-xs-11">
                        <input type="text" class="form-control" id="product_sale_gst" name="product_sale_gst" placeholder="Sale GST" value="" readonly required />

                     </div>
                  </div>
               </div>
               <div class="col-md-4">
                  <div class="form-group">
                     <label for="Product Type" class="col-md-4 control-label">Purchase GST</label>
                     <div class="col-md-8 col-xs-11">
                        <input type="text" class="form-control" id="product_purchase_gst" name="product_purchase_gst" placeholder="Purchase GST" value="" readonly required />

                     </div>
                  </div>
               </div>

            </div>
            <div class="col-md-12 margin_row" style="margin-top:25px !important;">
               <div class="col-md-3">
                  <div class="form-group">
                     <label for="Product Type" class="col-md-4 control-label">Base Unit</label>
                     <div class="col-md-8 col-xs-11">
                        <select class="select2" name="product_base_unit" id="product_base_unit" title="Select Unit" onchange="get_product_unit(this.value)" required <?= $disabled_u ?>>
                           <?php echo getunit($dbcon, 3); ?>
                        </select>

                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="form-group">
                     <label for="Product Type" class="col-md-4 control-label">Qty</label>
                     <div class="col-md-8 col-xs-11">
                        <input type="text" class="form-control" name="product_base_qty" id="product_base_qty" value="1" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" required <?= $readonly ?> />
                     </div>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="form-group">
                     <label for="Product Type" class="col-md-4 control-label">Conv. Unit</label>
                     <div class="col-md-8 col-xs-11">
                        <select class="select2" name="product_conv_unit" id="product_conv_unit" title="Select Unit" required <?= $disabled_u ?>>
                           <?php echo getunit($dbcon, 3); ?>
                        </select>
                     </div>

                  </div>
               </div>
               <div class="col-md-3">
                  <div class="form-group">
                     <label for="Product Type" class="col-md-4 control-label">Qty</label>
                     <div class="col-md-8 col-xs-11">
                        <input type="text" class="form-control" name="product_conv_qty" id="product_conv_qty" value="1" onkeypress="return isNumberKey(event)" onchange="add_decimal_weight(this);" required <?= $readonly ?> />
                     </div>
                  </div>
               </div>
               <input type="hidden" name="mode" id="mode" value="add" />
               <input type="hidden" name="eid_main" id="eid_main" value="" />
            </div>
            <div class="clearfix" style="margin-bottom:10px;">
            </div>
            <div class="col-md-5"></div>
                     </div>
                     <button type="submit" class="btn btn-shadow btn-success">Save</button>
                     <button type="button" class="btn btn-danger" accesskey="c" onclick="modal_remove()">Cancel</button>
                     <div class="col-md-3"></div>
                  </form>
               </div>
            </section>

         </div>
      </div>
   </div>
</div>