<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   include_once("../include/function_database_query.php");
   $form="Purchase Card Item";
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];

   $sql = "SELECT `p`.`product_id`,`p`.`product_type`, `p`.`product_name`, `tc`.`cat_name`, `p`.`product_icode`,`pg`.`process_type_name` FROM `product_mst` as p 
      left join process_type_mst as pg ON `p`.`product_type` = `pg`.`process_type_id`
      left join tbl_category as tc on `p`.`product_category`=`tc`.`cat_id`
      order by `p`.`product_id` desc";
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
                                    <div style="height:350px; overflow:auto;overflow-x: hidden;" class="adv-table" id="list_table_div">
                                       <table class="display table table-bordered table-striped" id="item-table">
                                       <thead>
                                         <tr>
                                          <th>Product Name</th>
                                          <th>Product Category</th>
                                          <th>Product Code</th>
                                          <th>Process Type</th>
                                         </tr>
                                       </thead>
                                       <tbody>
                                          <?php while($rel=mysqli_fetch_assoc($result))
                                           {
                                             $cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
                                            ?>
                                             <tr>
                                                <td><a href="javascript:void(0)" onClick="get_item_name('<?=$rel['product_id']?>', '<?=$rel['product_type']?>', '<?=$rel['product_name']?>')" data-id="<?=$rel['product_id']?>"><?=$rel['product_name']?></a></td>
                                                <td><?=$cat_name?></td>
                                                <td><?=$rel['product_icode']?></td>
                                                <td><?=$rel['process_type_name']?></td>
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
      
      <script src="<?=ROOT?>js/app/purchase_card_item.js?<?=time()?>"></script>
     
      <script>
         $(".select2").select2({
            width: '100%'
         });
         $("#item-table").dataTable({
            "bPaginate": false,
            "bInfo" : false
            /*"oLanguage": {
               "sSearch": "Filter records:"
             }*/
         });
         $(document).ready(function(){
            $(".dataTables_filter input").attr("placeholder", "Search Item");
         });
         
      </script>
   </body>
</html>
<?php 
$_SESSION['selected_purchase_vendor']='';
$_SESSION['purchase_type']='';
$_SESSION['selected_purchase_item'] = '';
$_SESSION['purchase_type'] = '';
$_SESSION['selected_product_type'] = '';
$_SESSION['selected_product_name'] = '';
?>
