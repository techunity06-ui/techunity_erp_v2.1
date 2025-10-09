<?php 
   session_start();   
   include('../include/urlfile.php');   
   $token = md5(rand(1000,9999));
   $_SESSION['token'] = $token;
   $form="Store Receive Approval";
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
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
                          
                         
                        </header>
                        <div class="panel-body">
                           <div class="adv-table">
                              <table class="display table table-bordered table-striped" id="dynamic-table">
                                 <thead>
                                    <tr>
                                    	<th>Stock Receive Id</th>
                                       <th>Stock Date</th>
                                       <th>Product Name</th>
                                       <th>Stock</th>
                                       <th>Refrence Name</th>       
                                       <th>Status</th>                                           
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
      <?php include_once($include1.'/store_aprv_hist_modal.php'); ?>
      <?php include_once($include.'/include_js_file.php');?>    
      <script src="<?=ROOT.INVENTORY_ROOT?>js/app/store_receive_pending_list.js"></script>
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
