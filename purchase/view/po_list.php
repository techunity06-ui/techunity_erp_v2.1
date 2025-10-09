<?php 
   session_start();
   
   //var_dump($_SESSION);
   $include1 = "../../include";
   include('../include/urlfile.php');	
   // error_reporting(E_ALL);
   $token = md5(rand(1000,9999));
   $_SESSION['token'] = $token;
   $form="Purchase Order";
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
   
   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
   		PO_LIST_VIEW,PO_LIST_ADD
   ]);
   if(!in_array(PO_LIST_VIEW,$bulkAccessArray)){
          header("Location: ".DOMAIN."permission_access");
      }
   
   $branch_id = $_SESSION['branch_id'];
   $companyConfiguration=getCompanyConfiguration($dbcon);

$amnts = get_po_taxable_total($dbcon);
// $cnyts = explode(",",$amnts);
$vender_id = "";

$purchase_party_show = $companyConfiguration['purchase_party_show'];

   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>PO LIST</title>
      <?php include_once('../../include/include_css_file.php');?>
      <style>
      .icons{
         width: 18%;
         float: left;
         margin: 10px 7px 10px;
         text-align: center;
         position:relative;

      }
      .icons12{
         background-color:#fff;
         padding-top:15px;
         border: 8px;
      }
      .icons p{
         text-align:center;
         font-size:15px;
         font-weight:600;
         padding-top:5px;
         color:white

      }

      /* .icon1 fa{

      } */
      .icon1.success{background-color: #5cb85c;}
      .icon1.primary{background-color: #0275d8;}
      .icon1.warning{background-color: #f0ad4e;}
      .icon1.info{background-color: #5bc0de;}
      .icon1.danger{background-color: #d9534f;}
      .icon1.terques{background-color: #6ccac9;}
      .icon1.yellow{background-color: #f8d347;}
      .icon1.pink{background-color:#E5649A;}
      .icon1.mustard{background-color:#F0BD23;}
      .icon1.success,.icon1.primary,.icon1.warning,.icon1.danger,.icon1.info,.icon1.terques,.icon1.yellow,.icon1.pink,.icon1.mustard{
         width: 150px;
         height:120px;
         border-radius: 8px;
         text-align:center;
         margin:0 auto
      }
      .icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
         text-align:center;
         color:#fff;
         padding-top: 27%;
         font-size: 37px;
      }
      @media (max-width:767px){
         .icons {
            width:265px;
            float: left;
            margin: 30px 4px 25px;
            position:relative;
         }

      }
      @media (min-width:768px) and (max-width:980px)
      {
         .icons12{
            background-color:#fff;
            padding-top:20px;
            padding-bottom:20px;
            border-radius: 8px;
         }
         .icons {
            width: 17%;
            float: left;
            margin: 30px 4px 25px;
            text-align: center;
            position:relative;
         }

      }
      .icons .badge {
         position: absolute;
         right: 25px;
         top: 0px;
         z-index: 100;
      }
   </style>
   </head>
   <body>
      <section id="container" >
         <?php include_once('../../include/include_top_menu.php');?>
         <!--sidebar start-->
         <?php include_once('../../include/left_menu.php');?>
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
                        <div class="row">
                           <div class="col-lg-12 centeral-align">
                              <div class="icons">
                                 <div class="icon1 success" >
                                    <p style="color:white;padding-top:10px;">Total Purchase Amount</p>
                                    <h3 style="font-size:20px;color:white;padding-top:5px;" id="total_purchase"></h3>
                                 </div>
                              </div>
                              <div class="icons">     
                                 <div class="icon1 info" >

                                    <p style="color:white;padding-top:10px;">Total Purchase<br> Taxable Value</p>

                                    <h3 style="font-size:20px;color:white;padding-top:5px;" id="taxable_amt"></h3>
                                 </div>
                              </div>
                           </div>   
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
                           <div class='col-lg-3 col-md-3 col-xs-12'>
                              <div class="form-group">
                                 <label class="control-label col-lg-3 col-md-3 col-xs-3">Choose Date</label>
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
                           <?if($companyConfiguration['branch_wise_manage']==1){?>
                           <div class="col-md-3">
                              <?php echo getBranchBox($dbcon, $branch_id, '', false, false, 'reload_data()'); ?>	
                           </div>
                        <?php} ?>
                        <div class="col-md-3">
                  <label class="col-md-4 control-label">Vendor*</label>
                  <div class="col-md-8" style="padding-left: 9px;">
                     <select class="select2" name="vender_id" id="vender_id" required title="Select Vender" onChange="reload_data();" >
                        <option value="">--Selct Vender--</option>
                           <?=getcust($dbcon,$vender_id,$purchase_party_show);?> 
                     </select>

                  </div>  
               </div>
               <div class="col-md-3">
                  <label class="col-md-4 control-label">Status*</label>
                  <div class="col-md-8" style="padding-left: 9px;">
                     <select class="select2" name="filt_status" id="filt_status" title="Select Status" onChange="reload_data();" >
                        <option value="">--Selct Status--</option>
                        <option value="1">Approved Pending</option>
                        <option value="2">Approved</option>
                        <option value="3">Pending Finance Approve</option>
                        <option value="4">Revised PO</option>
                        <option value="5">Short closed pending</option>
                        <option value="6">Short closed done</option>

                     </select>

                  </div>  
               </div>     
                 <!-- <div class="col-md-3">
                  <label class="col-md-4 control-label" style="">Short close status*</label>
                  <div class="col-md-8" style="padding-left: 9px;">
                     <select class="select2" name="short_status" id="short_status" title="Select Status" onChange="reload_data();" >
                        <option value="">--Selct Status--</option>
                        <option value="1">Short closed done</option>
                        <option value="2">Cancelled</option>
                     </select>

                  </div>  
               </div> -->
                     
                           <!--<div class="col-md-6">
                              <div class="col-md-3">
                              	<label for="po_type_status1" class="external-event label label-success ui-draggable" style="position: relative;cursor:pointer;">Created</label>
                              	<input id="po_type_status1" name="po_type_status"  type="radio" checked="checked" onClick="reload_data();" class="" title="Created" value="1">
                              </div>
                              <div class="col-md-3">
                              	<label for="po_type_status2" class="external-event label label-primary ui-draggable" style="position: relative;cursor:pointer;">Requested</label>
                              	<input id="po_type_status2" name="po_type_status" onClick="reload_data();" type="radio" class="" title="Requested" value="2" />
                              </div>
                              <div class="col-md-3">
                              	<label for="po_type_status3" class="external-event label label-warning ui-draggable" style="position: relative;cursor:pointer;">Cancel</label>
                              	<input id="po_type_status3" name="po_type_status"  onClick="reload_data();" type="radio" class="" title="Cancel" value="3" />
                              </div>
                              </div>-->
                           <span class="tools pull-right mtop20">
										<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat">Export Excel</button></a>
									
                           <?php if(in_array(PO_LIST_ADD,$bulkAccessArray)){ ?>	
                              <a href="<?=ROOT.PURCHASE_ROOT.'po'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
                              </span>
                           <?php } ?>
                        </header>
                        <div class="panel-body">
                           <div class="adv-table">
                              <table class="display table table-bordered table-striped" id="dynamic-table">
                                 <thead>
                                    <tr>
                                       <th>PO No</th>
                                       <th>PO Date</th>
                                       <th>Vendor Name</th>
                                       <th>Branch Name</th>
                                       <th>City </th>
                                       <th>Grand Total</th>
                                       <th>Basic Total</th>
                                       <th>Total Purchase</th>
                                       <th>User Name</th>
                                       <th>Approval Status</th>
                                       <th class="hidden-phone">Action</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                 </tbody>
                                 <tfoot>
                           <tr>
                              <th colspan="5" style="text-align:right">Total:</th>
                              <th></th>
                              <th></th>
                              <th></th>
                              <th></th>
                           </tr>
                        </tfoot>
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
         <?php include_once('../../include/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../../include/preview_purchase_order_aprv_hist.php'); ?>
      <?php include_once('../../include/preview_attached_doc.php'); ?>
      <?php include_once('../../include/preview_purchase_order_finance_aprv_hist.php'); ?>
	  <?php include_once('../../include/full_po_shortclose_reason.php'); ?>
	  <?php include_once('../../include/manual_po_shortclose_reason.php');?>
     <?php include_once('../../include/view_delivery_detail.php');?>
	  <?php include_once('../../include/include_js_file.php');?>    
   
     <?php include_once('../include/send_email_via_po.php');?>
     <script src="<?=ROOT.PURCHASE_ROOT?>js/app/po.js"></script>
      
      
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
<?php $_SESSION['selected_vendor']=''; ?>