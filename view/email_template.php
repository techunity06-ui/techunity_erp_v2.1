<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   include_once("../include/function_database_query.php");
   $form="Email & SMS Template";
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
   
   if(strpos($_SERVER[REQUEST_URI], "emailtemplateedit")==true){
      
      $back = "email_template_list";
      $mode = "Edit";$direct_add='0';$request=0;
      $email_sms_id = $dbcon->real_escape_string($_REQUEST['id']);
      
      $sql = 'select * from email_sms_template where email_sms_id = "'.$email_sms_id.'" and company_id =  "'.$_SESSION['company_id'].'" '; 
      $exec = $dbcon->query($sql);
      $rel=brp_mysqli_fetch_assoc($exec);

      $email_cc=$rel['email_cc'];
      $email_bcc=$rel['email_bcc'];

   }
   else{
      $back="email_template_list";
      $mode="Add";
      $direct_add='0';$request=0;
   }

    // Amish Soni Start 19-01-2021
    $crm_auto_mail = '';
    $companySettings = getCompanySettings($dbcon);
    if($companySettings) {
        $crm_auto_mail = $companySettings['crm_auto_mail'];
    }
    // Amish Soni End 19-01-2021

?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>EMAIL TEMPLATE</title>
      <?php include_once('../include/include_css_file.php');?>
      <style>
         .brp_select a span.cke_combo_text {
            width: 80% !important;
         }
      </style>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once('../include/include_top_menu.php');?>
         <?php include_once('../include/left_menu.php');?>
         <section id="main-content">
            <section class="wrapper">
               <?php//include_once('../include/equick_link.php');?>
               <div class="row">
                  <div class="col-lg-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.'email_template_list'?>"><?=$form?></a></li>
                           </ul>
                        </div>
                     </section>
                  </div>
               </div>
               <div class="row">
               <div class="col-sm-12">
                  <section class="panel">
                     <header class="panel-heading">
                        New <?=$form?>
                     </header>
                     <div class="panel-body">
                        <form class="form-horizontal" role="form" id="email_template_add" action="javascript:;" method="post" name="email_template_add">
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <label for="Product Type" class="col-md-3 control-label">Template Title *</label>
                                    <div class="col-md-8 col-xs-11">
                                       <input type="text" class="form-control" id="template_title" name="template_title" placeholder="Template Title" value="<?=$rel['template_title']?>" >
                                    </div>
                                 </div>
                              </div>
                              
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <label for="Product Type" class="col-md-3 control-label">Module Name *</label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="email_module_id" id="email_module_id">
                                       <?=get_email_module_list($dbcon, $rel['email_module_id'], false);?> 
                                       </select>
                                    </div>
                                 </div>
                              </div>

                              <div class="show_crm_dd" style="display: none;">
                                 <div class="col-md-12">
                                    <div class="form-group">
                                       <label for="task_id" class="col-md-3 control-label">Task Name *</label>
                                       <div class="col-md-8 col-xs-11">
                                          <select class="select2" name="task_id" id="task_id" >
                                             <option value="">Please select</option>$
                                             <?=get_master_category_dtl($dbcon,$rel['task_id'],10);//10:Task?> 
                                          </select>
                                       </div>
                                    </div>
                                 </div>

                                 <div class="col-md-12">
                                    <div class="form-group">
                                       <label for="stage_id" class="col-md-3 control-label">Stage Name *</label>
                                       <div class="col-md-8 col-xs-11">
                                          <select class="select2" name="stage_id" id="stage_id">
                                             <option value="">Please select</option>$
                                             <?=get_inquiry_stage($dbcon, $rel['stage_id']);?>
                                          </select>
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-md-12">
                                 <div class="form-group">
                                    <label for="email_subject" class="col-md-3 control-label">Email Subject *</label>
                                    <div class="col-md-8 col-xs-11">
                                       <input type="text" class="form-control" id="email_subject" name="email_subject" placeholder="Email Subject" value="<?=$rel['email_subject']?>" >
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="form-group" style="color: red;">
                                    <label class="col-md-3 control-label">Note</label>
                                    <label class="col-md-8 control-label" style="text-align: left;">You can copy variable from <strong>Insert Merge Fields</strong> from Email Content Editor to <u>Email Subject</u> & <u>SMS Content</u> using <?php echo EMAIL_INSERT_TAG_PREFIX.'VARIABLE_NAME'.EMAIL_INSERT_TAG_POSTFIX; ?></label>
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <label for="Product Type" class="col-md-3 control-label">Email Content *</label>
                                    <div class="col-md-8 col-xs-11">
                                       <textarea class="form-control" name="email_content" id="email_content" ><?=$rel['email_content']?></textarea>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label for="Product Type" class="col-md-3 control-label">Email CC </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="email_cc" id="email_cc" >
                                          <?=get_users_typewise($dbcon,$email_cc)?>         
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label for="Product Type" class="col-md-3 control-label">Emial Bcc </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="email_bcc" id="email_bcc" >
                                          <?=get_users_typewise($dbcon,$email_bcc)?>         
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <!-- <div class="col-md-12">
                                 <div class="form-group">
                                    <label for="Product Type" class="col-md-3 control-label">Attachment File name</label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="print_page_id" id="print_page_id" >
                                          <?=getprintpermission($dbcon,$rel['print_page_id'])?>         
                                       </select>
                                    </div>
                                 </div>
                              </div> -->
                              <div class="col-md-12">
                                 <div class="form-group">
                                    <label for="Product Type" class="col-md-3 control-label">SMS Content </label>
                                    <div class="col-md-8 col-xs-11">
                                       <textarea class="form-control" name="sms_content" id="sms_content" maxlength="160"><?=$rel['sms_content']?></textarea>
                                       <span id="remaing_char"></span>
                                    </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
                                 <a href="<?=ROOT.'email_template_list'?>" type="button" class="btn btn-danger">Cancel</a>
                                 <div class="col-md-3"></div>
                              </div>
                              <!--Vendor row end-->   
                              <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                              <input type='hidden' name='eid' id='eid' value='<?=$email_sms_id;?>' />   
                              <input type='hidden' name='back' id='back' value='<?=$back;?>' /> 
                              <input type='hidden' name='crm_auto_mail' id='crm_auto_mail' value='<?=$crm_auto_mail;?>' />
                              <!-- <input type='hidden' id='emailmoduletypeid' value='<?=$rel['email_module_type_id']?>' />  -->
                        </form>
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
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/email_template.js?<?=time()?>"></script>
      <script>
         $(".select2").select2({
            width: '100%'
         });

         loadEditor($('#email_module_id').val());
         
          $('#sms_content').keyup(function() {
            var mychars = $('#sms_content').val().length; 
            
            var mysms = Math.ceil(mychars / 160);

            var remaing_char = 160 - mychars;
            $('#remaing_char').html("Remaining Character : "+remaing_char);
         });

         $('#email_module_id').on('change', function() {
            $('.show_crm_dd').hide();
            $('#task_id').select2('val', '');
            $('#stage_id').select2('val', '');
            var crm_auto_mail = $('#crm_auto_mail').val();
            var curVal = $(this).val();
            loadEditor(curVal);
            if(curVal == 2 && crm_auto_mail != 'No') {
               $('.show_crm_dd').show();
            }
         });

         <?php if($mode == 'Edit') { ?>
            <?php if(isset($rel) && $rel && isset($rel['email_module_id']) && $rel['email_module_id'] == 2 && $crm_auto_mail != 'No') { ?>
               $('.show_crm_dd').show();
            <?php } ?>
         <?php } ?>
      </script>
   </body>
</html>