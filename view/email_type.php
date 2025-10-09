<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   $token = md5(rand(1000,9999));
   $_SESSION['token'] = $token;
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once('../include/include_css_file.php');?>
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
                           <h3>New Email Type</h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li class="active">Email Type</li>
                           </ul>
                        </div>
                     </section>
                     <!--breadcrumbs end -->
                  </div>
               </div>
               <!--state overview start-->
               <div class="row">
                  <div class="col-sm-3">
                     <section class="panel">
                        <header class="panel-heading">
                           Email Type 
                        </header>
                        <div class="panel-body">
                           <form role="form" id="email_type_add" action="javascript:;" method="post" name="email_type_add">
                              <div class="form-group">
                                 <label for="stateid">Module</label>
                                 <select id="module_id" class="select2" name="module_id" required>
                                    <option selected disabled value="">Select Module</option>
                                    <?php
                                       $query = $dbcon->query("SELECT `email_module_id`,`name` FROM `email_module_list` WHERE `status` = 0 order by name ");
                                       while($r = $query -> fetch_assoc()) {
                                       	echo '<option value="'.$r['email_module_id'].'">'.$r['name'].'</option>';
                                       }
                                       ?>
                                 </select>
                              </div>
                              <div class="form-group">
                                 <label for="catalog_name">Email Type</label>
                                 <input type="text" class="form-control" id="email_template_name" name="email_template_name" placeholder="Email Type" />
                              </div>
                              <input type='hidden' name='mode' id='mode' value='add' />
                              <input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
                              <button type="submit" class="btn btn-info">Submit</button>
                           </form>
                        </div>
                     </section>
                  </div>
                  <div class="col-sm-9">
                     <section class="panel">
                        <header class="panel-heading">
                           Email Type List
                           <span class="pull-right">
                           <a href="<?=ROOT.'module_name'?>" type="button" class="btn btn-success">Module List</a> </span>	
                        </header>
                        <div class="panel-body">
                           <div class="adv-table">
                              <table  class="display table table-bordered table-striped" id="dynamic-table">
                                 <thead>
                                    <tr>
                                       <th>Sr. NO.</th>
                                       <th>Email Type</th>
                                       <th>Module Name</th>
                                       <th class="hidden-phone">Action</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                 </tbody>
                              </table>
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
         <!--footer end-->
      </section>
      <!-- Modal -->
      <div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
         <div class="modal-dialog custom-width">
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
                  <h3>Edit Email Type</h3>
               </div>
               <div class="modal-body form">
                  <form id="FormEditEmail" role="form" method="post" novalidate>
                     <div class="form-group">
                        <label class="control-label">Email Type</label>
                        <input type="text" name="edit_email_template_name"  id="edit_email_template_name" class="form-control" required >
                     </div>
                     <div class="form-group">
                        <label class="control-label">Module</label>
                        <select id="edit_module_id" class="select2" name="edit_module_id" required>
                           <option selected disabled value="">Select Module</option>
                           <?php
                              $query = $dbcon->query("SELECT `email_module_id`,`name` FROM `email_module_list` WHERE `status` = 0 order by name ");
                              while($r = $query -> fetch_assoc()) {
                              	echo '<option value="'.$r['email_module_id'].'">'.$r['name'].'</option>';
                              }
                              ?>
                        </select>
                     </div>
               </div>
               <div class="modal-footer">
               <input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
               <input type="hidden" name="edit_id" id="edit_id" value="" />
               <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
               <button class="btn btn-info btn-flat" type="submit">Update Type</button>
               </div>
               </form>
            </div>
            <!-- /.modal-content -->
         </div>
         <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/email_module_type.js"></script>
      <script>
         $(".select2").select2({
         	width: '100%'
         });
      </script>
   </body>
</html>