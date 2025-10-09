<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   include_once("../include/function_database_query.php");

   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
         PURCHASE_BILL_ADD
   ]);
   if(!in_array(PURCHASE_BILL_ADD,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    
   $form="Purchase Bill";
    $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];

   $setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
    $set_conf=mysqli_fetch_assoc($dbcon->query($setconf));
   $type_conf = $set_conf['indent_po_pro_type'];
   $pro_search = $set_conf['purchase_pro_search'];
   $purchase_party_show = $set_conf['purchase_party_show'];

   $sql = "SELECT l.*, `g`.`g_name`,`c`.`city_name` FROM `tbl_ledger` as l left join tbl_group as g ON `l`.`l_group`=`g`.`g_id` left join city_mst as c ON `l`.`cityid`=`c`.`cityid` WHERE `l`.`l_group` in (".$purchase_party_show.") order by `l`.`l_id` desc";
   $result=$dbcon->query($sql);
   
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
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
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.'purchase_list'?>"><?=$form?> List</a></li>
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
                                    <div style="height:350px; overflow:auto;overflow-x: hidden;" id="list_table_div">
                                       <table class="display table table-bordered table-striped" id="vendor-table">
                                       <thead>
                                         <tr>
                                          <th>Vendor Name</th>
                                          <th>Vendor Code</th>
                                          <th>Email</th>
                                          <th>Phone</th>
                                          <th>City</th>
                                         </tr>
                                       </thead>
                                       <tbody>
                                          <?php while($rel=mysqli_fetch_assoc($result))
                                           { ?>
                                             <tr>
                                                <td><a href="javascript:void(0)" onClick="get_vendor_name('<?=$rel['l_id']?>')" data-id="<?=$rel['l_id']?>"><?=$rel['l_name']?></a></td>
                                                <td>NA</td>
                                                <td><?=$rel['cust_mobile']?></td>
                                                <td><?=$rel['cust_email']?></td>
                                                <td><?=$rel['city_name']?></td>
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
         <?php include_once('../include/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      
      <script src="<?=ROOT?>js/app/purchase.js?<?=time()?>"></script>
     
      <script>
         $(".select2").select2({
            width: '100%'
         });
         $("#vendor-table").dataTable({
            "bPaginate": false,
            "bInfo" : false
         });
      </script>
   </body>
</html>