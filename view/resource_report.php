<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   include_once("../include/function_database_query.php");
   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
            RESOURCE_REPORT_VIEW
   ]);
   if(!in_array(RESOURCE_REPORT_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
   }

   $form="Resource Report";
   $infopage = pathinfo( __FILE__ );

   $_SESSION['page']=$infopage['filename'];
   
   $back="resource_report";
   $mode="generate_report";$direct_add='0';$request=0;
   $date = date('d-m-Y');

   $resource_id=$dbcon->real_escape_string($_REQUEST['id']);
   $branch_id = $_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>RESOURCE</title>
      <?php include_once('../include/include_css_file.php');?>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once('../include/include_top_menu.php');?>
         <?php include_once('../include/left_menu.php');?>
         <section id="main-content">
            <section class="wrapper">
               <?php //include_once('../include/equick_link.php');?>
               <div class="row">
                  <div class="col-lg-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><?=$form?></li>
                           </ul>
                        </div>
                     </section>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <?=$form?> 
                        </header>
                        <div class="panel-body">
                           <form class="form-horizontal" role="form" id="resource_add" action="javascript:;" method="post" name="resource_add">
                              <section class="panel" >
                                 <div class="panel-body bio-graph-info">
                                    <div class="row">
                                       <div class="col-md-12">
                                          <?php if($resource_id==''){ ?>
                                          <div class="col-md-4">
                                             <?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'fetch_resource_based_on_branch();'); ?>
                                          </div>
                                          <?php } ?>
                                            <div class="col-md-4">
                                             <div class="form-group">
                                                <label class="col-md-4 control-label" style="">Resource Name * </label>
                                                <div class="col-md-8 col-xs-11">
                                                   <select class="select2"  name="resource_id" id="resource_id" title="Resource Name" >
                                                    <?php if($resource_id!=''){ ?>  
                                                      <?=get_all_resource($dbcon,$resource_id, '', $branch_id)?>
                                                    <?php } ?>
                                                   </select>
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col-md-2"></div>
                                          <?php if(in_array(RESOURCE_REPORT_VIEW,$bulkAccessArray)){ ?> 
                                          <div class="col-md-2">
                                             <div class="form-group">
                                                <button type="submit" class="btn btn-success" id="save" name="save">Generate Report</button>
                                                <a href="<?=ROOT.'resource_report'?>" type="button" class="btn btn-danger">Cancel</a>
                                             </div>
                                          </div>
                                          <?php } ?>
                                       </div>
                                    </div>
                                 </div>
                              </section>
                              <!--Vendor row end--> 
                              <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                              <input type='hidden' name='back' id='back' value='<?=$back;?>' /> 
                           </form>
                           <section class="panel" >
                              <div class="panel-body bio-graph-info">
                                 <div class="row" id="resource_report_div"></div>
                              </div>
                           </section>
                        </div>
                     </section>
                  </div>
               </div>
               <!--state overview end-->
            </section>
         </section>
         <!--main content end-->
         <!--footer start-->
         <?php //include_once('../include/add_vender.php');?>
         <?php include_once('../include/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/resource_report.js?<?=time()?>"></script>
      <script>
         $(".select2").select2({
           width: '100%',
           //minimumInputLength: 2,
         });
      </script>
   </body>
</html>