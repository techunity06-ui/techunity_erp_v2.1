 <div class="modal colored-header info" id="preview_bom_document_upload" role="dialog" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
               <div class="modal-content">
                  <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                     <h3 style="margin-top:-6px;" important!>View Images</h3>
                  </div>
                  <div class="modal-body form ">
                     <form class="form-horizontal"  id="frm_bom_doc" action="javascript:;" method="post" name="frm_bom_doc" enctype="multipart/form-data">
                     <div class="col-md-12 mtop20">
                                    <div class="col-md-6">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Document Name </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="doc_image_name" name="doc_image_name" type="text" class="form-control" title="Document Name" value="" placeholder="Document Name" >
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-6">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Upload Document</label>
                                          <div class="col-md-8 col-xs-11">
                                             <input type="file" name="dr_file" id="dr_file" class="form-control">
                                       </div>
                                    </div>
                                 </div>
                                    <input type="hidden" name="mode" id="mode" value="save_bom_document" />
                                    <input type="hidden" name="doc_bom_id" id="doc_bom_id" value="" />
                                    <input type="hidden" name="doc_bom_version_id" id="doc_bom_version_id" value="" />
                              </div>
                           </form>
                               <div class="col-md-12 mtop20 text-center">
                                    <button type="button" onClick="save_bom_documents()" class="btn btn-primary" id="save_document" name="save_document">Add Document</button>

                                     <button type="button" class="btn btn-danger btn-flat md-close" data-dismiss="modal">Close</button>
                                 </div>  
                      <div class="col-md-12 mtop20">
                       <!-- <div id="drawing_image_list"></div>-->
                        <div id="documents_data_list"></div>
                     </div>   
                  </div>
                  <div class="modal-footer">
                    
                     <!-- <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button> -->
                     
                  </div>
               </div>
            </div>
         </div>