<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   $form="Upload Documents ";
   $id=$_REQUEST['id'];
   
   $query="select l_id,l_name from tbl_ledger where l_id='$id'";
   $rel=$dbcon->query($query);
   $row=mysqli_fetch_array($rel);
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once('../include/include_css_file.php');?>
      <style>
         .mg10
         {
         margin-left:5px;
         }
         #radioBtn .notActive{
         color: #3276b1;
         background-color: #fff;
         }
         .redc
         {
         color:#EB6A5D !important;
         text-align:center !important;
         }
      </style>
   </head>
   <body>
      <section id="container" >
         <?php include_once('../include/include_top_menu.php');?>
         <!--sidebar start-->
         <?php include_once('../include/left_menu.php');?>
         <!--sidebar end-->
         <!--main content start-->
         <section id="main-content">
            <section class="wrapper">
               <div class="row">
                  <div class="col-lg-12">
                     <!--breadcrumbs start -->
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?='View '.' '.$form.' For ' . $row['l_name'] ?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.ADMINISTRATION_ROOT.'ledger_list'?>">Ledger List</a></li>
                           </ul>
                        </div>
                     </section>
                     <!--breadcrumbs end -->
                  </div>
               </div>
               <!--state overview start-->
               <div class="row">
                  <div class="col-sm-12">
                     <section class="panel">
                        <div class="panel-body ">
                           <form role="form" id="upload_add" action="javascript:;" method="post" name="upload_add">
                              <div class="col-md-12">
                                 <div class="col-md-6">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Select Document *</label>
                                       <div class="col-md-8 col-xs-11">
                                          <select class="select2" id="docs_id" name="docs_id" title="Select Document Type" required >
                                             <?=getdocumentlist($dbcon,'');?>
                                          </select>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <input type="file" name="doc_file" id="doc_file" />
                                 </div>
                                 <div class="col-md-2">
                                    <input type="button" name="button" id="upload_button" class="btn btn-primary" value="Upload" onclick="upload_docs();" />
                                    <input type="hidden" name="l_id" id="l_id" value="<?=$id?>"  />
                                    <input type="hidden" name="img_mode" id="img_mode" value="upload_docs"  />
                                 </div>
                              </div>
                           </form>
                           <div id="uploaded_image"></div>
                        </div>
                        <div class="panel-body ">
                           <div class="col-md-12" id="show_document">
                           </div>
                        </div>
                     </section>
                  </div>
               </div>
               <!--state overview end-->
            </section>
         </section>
         <!--main content end-->
         <!--footer start-->
         <?php include_once('../include/footer.php');?>
         <?php include_once('../include/view_complain_history_spare_part.php'); ?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/upload_document.js?<?=time()?>"></script>
      <script>
         $(".select2").select2({
         	width: '100%'
         });
         $('.default-date-picker').datepicker({
         	format: 'dd-mm-yyyy',
         	autoclose: true
         });
         
         $('#radioBtn a').on('click', function(){
             var sel = $(this).data('title');
             var tog = $(this).data('toggle');
             $('#'+tog).prop('value', sel);
             
             $('a[data-toggle="'+tog+'"]').not('[data-title="'+sel+'"]').removeClass('active').addClass('notActive');
             $('a[data-toggle="'+tog+'"][data-title="'+sel+'"]').removeClass('notActive').addClass('active');
         })
      </script> 
   </body>
</html>