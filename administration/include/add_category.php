<div class="modal colored-header info " id="modal-add-category" role="dialog" data-keyboard="false" data-backdrop="static">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="btn_close  close md-close" accesskey="c" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Add Category</h3>
         </div>
         <div class="modal-body form">
            <section class="panel">

               <div class="panel-body">
                     <form role="form" id="category_add" action="javascript:;" method="post" name="category_add">
                     <input type="hidden" name="direct_product_add" value="1">
                     <input type="hidden" name="product_add_type" id="product_add_type" value="">
                     <div class="col-md-12" style="padding-top: 25px;">
                       
           
            <div class="col-md-12 margin_row" style="margin-top:25px !important;">
              <div class="form-group">
                                    <label>Branch *</label>

                                    <select class="branch_validate" name="branch_id" id="abranch_id" required>
                                      
                                       <?= getBranchBox_new($dbcon, '', 'all'); ?>
                                    </select>
                                 </div>
               <div class="form-group">
                                 <label>Sub Category Name *</label>
                                 <input class="form-control" type='text' name='cat_name' id='cat_name' value='' />
                              </div>

                              <div class="form-group">
                                 <label>Select Category *</label>
                                 <select class="select2" name="cat_parent" id="cat_parent">
                                    <?= get_all_category($dbcon, $id); ?>
                                 </select>
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