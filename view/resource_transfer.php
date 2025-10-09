<?php 
    session_start();
    include_once("../config/config.php");
    include_once("../config/session.php");

    include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
    include_once("../include/function_database_query.php");

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
            RESOURCE_TRANSFER_VIEW, RESOURCE_TRANSFER_UPDATE
    ]);
    if(!in_array(RESOURCE_TRANSFER_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }

    $form="Resource Transfer";
    $infopage = pathinfo( __FILE__ );
    $_SESSION['page']=$infopage['filename'];

    $back="resource_transfer";
    $mode="resource_transfer";$direct_add='0';$request=0;
    $date = date('d-m-Y');

    $branch_id = $_SESSION['branch_id'];
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>RESOURCE TRANSFER</title>
      <?php include_once('../include/include_css_file.php');?>
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
                                         <div class="col-md-4">
                                             <?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'fetch_resource_based_on_branch();'); ?>
                                          </div>
                                       </div> 
                                       <div class="col-md-12">
                                          <div class="col-md-4">
                                               <div class="form-group">
                                                  <label class="col-md-4 control-label" style="">Resource Name * </label>
                                                  <div class="col-md-8 col-xs-11">
                                                     <select class="select2"  name="resource_id" id="resource_id" title="Resource Name" onChange="resourceselect(this.value)">
                                                      <!-- <=get_all_resource($dbcon)?> -->
                                                     </select>
                                                  </div>
                                               </div>
                                          </div>
                                          <div class="col-md-4">
                                               <div class="form-group">
                                                  <label class="col-md-4 control-label" style="">Work Order No. * </label>
                                                  <div class="col-md-8 col-xs-11">
                                                     <select class="select2"  name="work_order_id" id="work_order_id" title="Work Order No." onChange="workorderselect(this.value)">
                                                     </select>
                                                  </div>
                                               </div>
                                          </div>
                                          <div class="col-md-4">
                                               <div class="form-group">
                                                  <label class="col-md-4 control-label" style="">Process Name * </label>
                                                  <div class="col-md-8 col-xs-11">
                                                     <select class="select2"  name="process_id" id="process_id" title="Process Name" onChange="processselect(this.value)">
                                                     </select>
                                                  </div>
                                               </div>
                                          </div>
                                       </div>
                                       <div class="col-md-12">
                                            <div class="col-md-4">
                                               <div class="form-group">
                                                  <label class="col-md-4 control-label" style="">Qty * </label>
                                                  <div class="col-md-8 col-xs-11">
                                                     <input type="text" name="qty" id="qty" value="" class="form-control" readonly>
                                                  </div> 
                                               </div>
                                            </div>
                                            <div class="col-md-4">
                                               <div class="form-group">
                                                  <label class="col-md-4 control-label" style="">Transfer Qty *</label>
                                                  <div class="col-md-8 col-xs-11">
                                                     <input type="number" name="transfer_qty" id="transfer_qty" value="" class="form-control" >
                                                  </div> 
                                               </div>
                                            </div>
                                            <div class="col-md-4">
                                               <div class="form-group">
                                                  <label class="col-md-4 control-label" style="">New Resource Name * </label>
                                                  <div class="col-md-8 col-xs-11">
                                                     <select class="select2"  name="new_resource_id" id="new_resource_id" title="Resource Name" required>
                                                     </select>
                                                  </div> 
                                               </div>
                                            </div>
                                       </div> 
                                    </div>
                                 </div>
                              </section>
                              <?php if(in_array(RESOURCE_TRANSFER_UPDATE,$bulkAccessArray)){ ?> 
                              <div class="row hide action_div" >
                                 <button type="submit" class="btn btn-success " id="save" name="save">Transfer Qty</button>
                                 <div class="col-md-5"></div>
                              </div>
                             <?php } ?>
                              <!--Vendor row end--> 
                              <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                              <input type='hidden' name='eid' id='eid' value='' />  
                              <input type='hidden' name='request_id' id='request_id' value='' />  
                              <input type='hidden' name='back' id='back' value='<?=$back;?>' /> 
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
         <?php //include_once('../include/add_vender.php');?>
         <?php include_once('../include/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/resource_transfer.js?<?=time()?>"></script>
      <script>
         $(".select2").select2({
           width: '100%',
           //minimumInputLength: 2,
         });
      </script>
   </body>
</html>