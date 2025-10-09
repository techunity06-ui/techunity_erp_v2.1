<?php 
   session_start();
   
   $path = "../../";
   $include = "../../include";
	
   include_once($path."config/config.php");
   include_once($path."config/session.php");
   
   include_once(COMMON_FUNCTION_PATH."common_functions.php");
   include_once($include."function_database_query.php");
   $form="Purchase Quotation";
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
   $approve_indent_id=$dbcon->real_escape_string($_REQUEST['id']);

   $sql = "SELECT pq.*, `l`.`l_name` FROM `po_quotation` as pq left join tbl_ledger as l ON `pq`.`vender_id`=`l`.`l_id` WHERE `pq`.`approve_indent_id` = '".$approve_indent_id."' AND `pq`.`po_quotation_status` in (0,1) AND `pq`.`user_id` = '".$_SESSION['user_id']."' AND `pq`.`company_id` = '".$_SESSION['company_id']."'";
   $result=$dbcon->query($sql);


   $approve_sql = "SELECT pq.*, `l`.`l_name` FROM `po_quotation` as pq left join tbl_ledger as l ON `pq`.`vender_id`=`l`.`l_id` WHERE `pq`.`approve_indent_id` = '".$approve_indent_id."' AND `pq`.`po_quotation_status`= 1 AND `pq`.`user_id` = '".$_SESSION['user_id']."' AND `pq`.`company_id` = '".$_SESSION['company_id']."'";
   $approve_result=$dbcon->query($approve_sql);
   $approve_count = mysqli_num_rows($approve_result);


   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once($include.'/include_css_file.php');?>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once($include.'/include_top_menu.php');?>
         <?php include_once($include.'/left_menu.php');?>
         <section id="main-content">
            <section class="wrapper">
               <?php //include_once('../include/equick_link.php');?>
               <div class="row">
                  <div class="col-lg-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                               <li><a href="<?=ROOT.PURCHASE_ROOT.'po_quotation_list'?>"><?=$form?> List</a></li>
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
                              <div class="row">
                                 <div class="col-md-12">
                                    <div style="height:350px; overflow:auto;overflow-x: hidden;">
                                       <table class="display table table-bordered table-striped" id="vendor-table">
                                       <thead>
                                         <tr>
                                          <th>Quotation No</th>
                                          <th>Vendor Name</th>
                                          <th>Delivery Date</th>
                                          <th>Payment Days</th>
                                          <th>Product Rate</th>
                                          <th>Action</th>
                                         </tr>
                                       </thead>
                                       <tbody>
                                          <?php while($rel=mysqli_fetch_assoc($result))
                                           { ?>
                                             <tr>
                                                <td><?=$rel['quotation_no']?></td>
                                                <td><?=$rel['l_name']?></td>
                                                <td><?=date('d-M-Y', strtotime($rel['delivery_date']))?></td>
                                                <td><?=$rel['payment_days']?></td>
                                                <td><?=$rel['product_rate']?></td>
                                                <td>
                                                   <!-- <a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="<?=ROOT.'quotation_vendorprint/'?><?=$approve_indent_id?>/<?=$rel['po_quotation_id']?>"><i class="fa fa-print"></i></a> -->

                                                   <?php if($approve_count<=0){ ?>
                                                      <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="<?=ROOT.'po_quotation_edit/'?><?=$approve_indent_id?>/<?=$rel['po_quotation_id']?>"><i class="fa fa-edit"></i></a>
                                                     <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onclick="delete_qo('<?=$rel['po_quotation_id']?>', '<?=$rel['approve_indent_id']?>')"><i class="fa fa-trash-o"></i></button>
                                                  <?php } ?>
                                                </td>   
                                             </tr>
                                           <?php } ?>  
                                       </tbody>           
                                   </table>
                                </div>
                                 </div>
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
         <?php //include_once('../include/add_vender.php');?>
         <?php include_once($include.'/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once($include.'/include_js_file.php');?>   
      
      <script src="<?=ROOT.PURCHASE_ROOT?>js/app/po_quotation_list.js?<?=time()?>"></script>
      <!--<script src="<?=ROOT?>js/app/payment_terms.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/mode_disptch.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/product_mst.js?<?=time()?>"></script>
      
         <script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
         <script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
         <script src="<?=ROOT?>js/app/customer.js?<?=time()?>"></script>
         -->
      <script>
         $(".select2").select2({
            width: '100%'
         });
         $("#vendor-table").dataTable({
            "bPaginate": false,
            "bInfo" : false
            /*"oLanguage": {
               "sSearch": "Filter records:"
             }*/
         });
         $(document).ready(function(){
            $(".dataTables_filter input").attr("placeholder", "Search Vendor");
         });
      </script>
   </body>
</html>
<style type="text/css">
   .dataTables_filter, .pull-right{
   float: left !important;
}
.dataTables_filter label input{
   width: 250px !important;
}
</style>

