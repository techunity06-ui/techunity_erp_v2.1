<?php 
   session_start();
   
   include('../include/urlfile.php');
   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
      PURCHASE_CARD_VIEW,PURCHASE_CARD_ADD
   ]);

   if(!in_array(PURCHASE_CARD_VIEW,$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
   }
   
   $form="Purchase Card";
   
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
      <title>PURCHASE CARD LIST</title>
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
               <?php//include_once('../include/equick_link.php');?>
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
						  <div class="row">
						  <div class="col-md-12">
                           <div class='col-lg-5 col-md-7 col-xs-9'>
                              <div class="form-group">
                                 <label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
                                 <div class=" col-lg-8 col-md-8 col-xs-9">
                                    <div class="input-group date form_datetime-component">
                                       
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
                           
						   </div>
						   <div class="col-md-12" style="margin-top:10px">
							<div class="col-lg-5 col-md-7 col-xs-9">
								<div class="form-group">
								<label class="control-label col-lg-4 col-md-4 col-xs-3">Card Type</label>
									<div class=" col-lg-8 col-md-8 col-xs-9">
										<select class="select2" title="Select product" name="card_type" id="card_type" onChange="reload_data();">
											<option value="0">Vendor Wise</option>
											<option value="1">Product Wise</option>
										</select>
									</div>
								</div>
							</div>
						   </div>
						  </div>
						   <span class="tools pull-right">
                           <?php if(in_array(PURCHASE_CARD_ADD,$bulkAccessArray)){ ?>
                              <a href="<?=ROOT.PURCHASE_ROOT.'purchase_card_mer'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>
                           <?php }?>					
                           </span>
                        </header>
                        <div class="panel-body">
                           <div class="adv-table">
                              <table class="display table table-bordered table-striped" id="dynamic-table">
                                 <thead>
                                    <tr>
                                       <th>Card No</th>
                                       <th>Card Date</th>
                                       <th id="car_ty_l"></th>
                                       <!-- <th>Product Name</th> -->
                                       <!-- <th>Valid Date </th>
                                       <th>Effective Date</th> -->
                                       <th>Status</th>
									   <!--<th>Branch Name</th>-->
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
         <?php include_once($include1.'preview_po_card_aprooval.php');?>
         <?php include_once($include.'/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once($include.'/include_js_file.php');?>    
      <script src="<?=ROOT.PURCHASE_ROOT?>js/app/purchase_card_mer.js"></script>
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
