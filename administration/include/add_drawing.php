<div class="modal colored-header info" id="ModalDrawing" role="dialog" data-keyboard="false" data-backdrop="static">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3 style="margin-top:-6px; important!">Add Drawing</h3>
         </div>
         <form class="form-horizontal" role="form" id="drawing_add" action="javascript:;" method="post" name="drawing_add">
         <div class="modal-body form">
               <div class="row">
                  <div class="col-md-12">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label class="col-md-4 control-label">Drawing No. *</label>
                           <div class="col-md-8 col-xs-11">
                              <input id="drawing_number" name="drawing_number" type="text" class="form-control" title="Drawing Number" value="" placeholder="Drawing Number" >
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label class="col-md-4 control-label">Drawing Title *</label>
                           <div class="col-md-8 col-xs-11">
                              <input id="drawing_title" name="drawing_title" type="text" class="form-control" title="Drawing Title" value="" placeholder="Drawing Title" >
                           </div>
                        </div>
                     </div>
                     
                  </div>
                  <div class="col-md-12">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label class="col-md-4 control-label">Select Vendor</label>
                           <div class="col-md-8 col-xs-11">
                              <select class="select2" name="vender_id" id="vender_id" title="Select Vender">
                              <?=getcust($dbcon,$vender_id);?> 
                              </select>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="form-group">
                           <label class="col-md-4 control-label">Drawing Size *</label>
                           <div class="col-md-8 col-xs-11">
                              <input id="drawing_size" name="drawing_size" type="text" class="form-control" title="Drawing Size" value="" placeholder="Drawing Size" >
                           </div>
                        </div>
                     </div>
                     
                  </div>
                  <div class="col-md-12">
                     <div class="col-md-6">
                        <div class="form-group">
                           <label class="col-md-4 control-label">Drawing Scale *</label>
                           <div class="col-md-8 col-xs-11">
                              <input id="drawing_scale" name="drawing_scale" type="text" class="form-control" title="Drawing Scale" value="" placeholder="Drawing Scale" >
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
         </div>
         <div class="modal-footer">
            <input type="hidden" name="mode" value="add_drawing_save">
            <button type="submit" class="btn btn-success" id="drawing_save" name="save">Submit</button>
            <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
         </div>
         </form>
      </div>
   </div>
</div>