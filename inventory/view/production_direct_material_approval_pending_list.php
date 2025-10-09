<?php 
   session_start();
   
   include('../include/urlfile.php');   
   $token = md5(rand(1000,9999));
   $_SESSION['token'] = $token;
   $form="Direct Material Pending Approval";
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];


$bulkAccessArray = canCheckPermissionAccess($dbcon, [
       PRODUCTION_STORE_LIST_SLUG_VIEW,PRODUCTION_STORE_LIST_SLUG_CREATE,PRODUCTION_STORE_LIST_SLUG_READ,PRODUCTION_STORE_LIST_SLUG_UPDATE,PRODUCTION_STORE_LIST_SLUG_DELETE,PRODUCTION_STORE_LIST_APPROVE,PRODUCTION_STORE_LIST_RETURN
]);

if(!in_array(PRODUCTION_STORE_LIST_APPROVE,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}

   if(empty($_SESSION['start']))
   {
   	$start=date('1-m-Y');
   	$end=date("d-m-Y");
   }
   else
   {
   	$start=$_SESSION['start'];
   	$end=$_SESSION['end'];
   }
   
   $branch_id = $_SESSION['branch_id'];
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Direct Material Approval</title>
      <?php include_once($include.'/include_css_file.php');?>
   </head>
   <body>
      <section id="container" >
         <?php include_once($include.'/include_top_menu.php');?>
         <!--sidebar start-->
         <?php include_once($include.'/left_menu.php');?>
         <!--sidebar end-->
         <!--main content start-->
         <section id="main-content">
            <section class="wrapper">
             
               <div class="row">
                  <div class="col-lg-12">
                     <!--breadcrumbs start -->
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$mode.' '.$form?> List</h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li class="active"><?=$form?> list</li>
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
                        <header class="panel-heading">
                           <div class='col-lg-4 col-md-7 col-xs-9'>
                              <div class="form-group">
                                 <label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
                                 <div class=" col-lg-8 col-md-8 col-xs-9">
                                    <div class="input-group date form_datetime-component">
                                       <?
                                          //$start=(date('m')<'04') ? date('01-04-Y',strtotime('-1 year')) : date('01-04-Y');
                                          ?>
                                       <input type="hidden" id="from_date" value="<?=$start?>">
                                       <input type="hidden" id="to_date" value="<?=$end?>">
                                       <input type="text" id="rep_date" onChange="reload_data();" class="form-control datepikerdemo" value="">
                                       <span class="input-group-btn">
                                       <button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
                                       </span>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-4">
                              <label class="control-label col-lg-4 col-md-4 col-xs-3">Branch</label>
                                 <div class=" col-lg-8 col-md-8 col-xs-9">
                                    <select class="select2" name="branch_id" id="branch_id" required onchange="reload_data()">
                                             <?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
                                          <?=getBranchBox_new($dbcon, $branch);?>
                                 </select>
                                 </div>
                              
                           </div>
                           <div class="col-md-4">
                              <div class="col-md-4">
                              	<label for="release_status1" class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Approval Pending</label>
                              	<input id="release_status1" name="release_status"  type="radio" checked="checked" onClick="reload_data();" class="" title="Created" value="0">
                              </div>
                              <div class="col-md-4">
                              	<label for="release_status3" class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Approved</label>
                              	<input id="release_status3" name="release_status"  onClick="reload_data();" type="radio" class="" title="Cancel" value="1" />
                              </div>
                              </div>
                         
                        </header>
                        <div class="panel-body">
                           <div class="adv-table">
                              <table class="display table table-bordered table-striped" id="dynamic-table">
                                 <thead>
                                    <tr>
                                       <th>Issue No</th>
                                       <th>Issue Date</th>
                                       <th>User Name</th>
                                       <th>Branch Name</th>
                                       <th>Approval Status</th>
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
         <?php include_once($include.'/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once($include1.'/preview_direct_material_aprv_hist.php'); ?>
      <?php include_once($include.'/include_js_file.php');?>    
      <script src="<?=ROOT.INVENTORY_ROOT?>js/app/direct_material_approve_pending_list.js"></script>
      <!--<script src="js/count.js"></script>-->
      <script>
         $(".select2").select2({
         		width: '100%'
         	});
         $('.default-date-picker').datepicker({
                   format: 'dd-mm-yyyy',
                   autoclose: true
               });
         function cb(start, end) {
               $('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
           }
           cb(moment().subtract(29, 'days'), moment());
         
         
           $('.datepikerdemo').daterangepicker({       
         		locale: {
         		format: 'DD-MM-YYYY'
         	},
          "autoApply": true,	
         "startDate": $('#from_date').val(),
         "endDate": $('#to_date').val(),	
            ranges: {
                  'Today': [moment(), moment()],
                  'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                  'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                  'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                  'This Month': [moment().startOf('month'), moment().endOf('month')],
                  'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
               }
           }, cb);
         $('.date-set').click(function(){
              $('.datepikerdemo').trigger('click')
         });
         
      </script>
   </body>
</html>
