<?php 
    session_start();
    include('../include/urlfile.php');	
    $form="Resource Allocation List";
    $infopage = pathinfo( __FILE__ );
    $_SESSION['page']=$infopage['filename'];


    $back="resource";
    $mode="Edit";$direct_add='0';$request=0;
    $purchasecard_date= $date = date('d-m-Y');

    $sql = "SELECT wra.*,`p`.`product_name`,`r`.`resource_name`, `rp`.`sp_id`, (SELECT po_req_no FROM tbl_set_main_process WHERE sp_id=`rp`.`sp_id`) as work_order_no, `proc`.`process_name`  FROM `tbl_work_order_resource_allocate` as wra 
    LEFT JOIN product_mst as p ON `wra`.`product_id` = `p`.`product_id`
    LEFT JOIN tbl_resource as r ON `wra`.`resource_id` = `r`.`resource_id`
    LEFT JOIN tbl_request_product as rp ON `wra`.`request_id` = `rp`.`rp_id`
    LEFT JOIN process_mst as proc ON `wra`.`process_id` = `proc`.`process_id`
    INNER JOIN tbl_resource_schedule as rsc ON `wra`.`request_id` = `rsc`.`rp_id`
    WHERE `wra`.`resourse_allocation_status`=0 AND `wra`.`qty`!=0  AND `wra`.`user_id`='".$_SESSION['user_id']."' AND `wra`.`company_id`='".$_SESSION['company_id']."' ORDER BY `wra`.`resource_allocate_id` DESC";
    $result=$dbcon->query($sql);
   
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>Work order allocate list</title>
      <?php include_once($include.'include_css_file.php');?>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once($include.'include_top_menu.php');?>
         <?php include_once($include.'left_menu.php');?>
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
                                          <div style="height:200px; overflow:auto;overflow-x: hidden;" id="list_table_div">
                                             <table class="display table table-bordered table-striped" id="vendor_table">
                                                <thead>
                                                   <tr>
                                                      <th>Resource Name</th>
                                                      <th>Product Name</th>
                                                      <th>Work Order No.</th>
                                                      <th>Request ID</th>
                                                      <th>Process Name</th>
                                                      <th>Qty</th>
                                                      <th>Time Per Qty</th>
                                                      <th>Total Time</th>
                                                      <th>Date</th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   <?php 
                                                      if(mysqli_num_rows($result)>0)
                                                        {
                                                          $i=0;  
                                                          while($rel=mysqli_fetch_assoc($result))
                                                          { 
                                                             
                                                      ?>
                                                   <tr onclick="get_item_information('<?=$rel['resource_allocate_id']?>')" data-id="<?=$rel['resource_allocate_id']?>" class="item_<?=$i?>">
                                                      <td><?=$rel['resource_name']?></td>
                                                      <td><?=$rel['product_name']?></td>
                                                      <td><?=$rel['work_order_no']?></td>
                                                      <td><?=$rel['request_id']?></td>
                                                      <td><?=$rel['process_name']?></td>
                                                      <td><?=$rel['qty']?></td>
                                                      <td><?=$rel['time_per_qty']?></td>
                                                      <td><?=$rel['total_time']?></td>
                                                      <td><?=date('d-M-Y',strtotime($rel['cdate']))?></td>
                                                      <!-- <td><?=$stage?></td> -->
                                                   </tr>
                                                   <?php $i++;} } ?> 
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </section>
                              <!-- Tab Section Start By Umair -->
                              <section class="panel" style="margin-top: 15px">
                                 <header class="panel-heading tab-bg-dark-navy-blue ">
                                    <ul class="nav nav-tabs">
                                       <li class="active">
                                          <a data-toggle="tab" href="#po_resource" aria-expanded="true">Resource Allocation Details</a>
                                       </li>
                                       <li class="">
                                          <a data-toggle="tab" href="#po_login" onclick="get_vendor_details('po_login')" aria-expanded="false">Login Details</a>
                                       </li>
                                    </ul>
                                 </header>
                                 <div class="panel-body">
                                    <div class="tab-content">
                                       <div id="po_resource" class="tab-pane active" >
                                          <section class="panel">
                                             <div class="panel-body bio-graph-info">
                                                <h1>Resource Details</h1>
                                                <div class="row">
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Product Name *</label>
                                                            <div class="col-md-8">
                                                               <input type="text" class="form-control" id="product_name" name="product_name" title="Product Name"  placeholder="Product Name" value="" readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >Work Order No. *</label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="work_order_no" name="work_order_no" type="text" class="form-control" title="Work Order No." value="" placeholder="Work Order No." readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Resource Name * </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <select class="select2"  name="resource_id" id="resource_id" title="Resource Name">
                                                                <?=getsalaryemployee($dbcon)?>
                                                               </select>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Qty *</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <input id="qty" name="qty" type="number" class="form-control" title="Qty" value="" placeholder="Qty" readonly>
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Time Per Qty*</label>
                                                            <div class="col-md-8">
                                                               <input type="text" class="form-control" id="time_per_qty" name="time_per_qty" title="Time Per Qty"  placeholder="Time Per Qty" value="" readonly >
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >Total Time *</label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <input id="total_time" name="total_time" type="number" class="form-control" title="Total Time" value="" readonly placeholder="Total Time"> 
                                                            </div>
                                                         </div>
                                                      </div>
                                                      
                                                   </div>
                                             </div>
                                          </section>
                                           <div class="row">
                                             <button type="submit" class="btn btn-success " id="save" name="save">Update</button>
                                             <div class="col-md-5"></div>
                                          </div>
                                       </div>
                                       <div id="po_login" class="tab-pane"> Login Details </div>
                                    </div>
                                 </div>
                              </section>
                              <!-- Tab Section -->
                              <!--Vendor row end--> 
                              <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                              <input type='hidden' name='eid' id='eid' value='' />  
                              <input type='hidden' name='table_id' id='table_id' value='' />  
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
         <!-- <?php //include_once($include.'add_cust.php');?> -->
        <?php include_once($include.'footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once($include.'include_js_file.php');?>   
      <script src="<?=ROOT.PRODUCTION_ROOT?>js/app/work_order_resource_allocate.js?<?=time()?>"></script>
      <!--
         <script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
         <script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
         <script src="<?=ROOT?>js/app/customer.js?<?=time()?>"></script>
         -->
      <script>
         $(".select2").select2({
           width: '100%',
           //minimumInputLength: 2,
         });
       
         $('.default-date-picker').datepicker({
           format: 'dd-mm-yyyy',
           autoclose: true
         });
         
         $('tr').not(':first').click(function () {
          $(this).addClass("active"); //add class selected to current clicked row
          $(this).siblings().removeClass( "active" ); //remove class selected from rest of the rows
         });
         
         $(document).ready(function(){
         $('.item_0').click();
         $('#table_id').val($('.item_0').attr('data-id'));
         });
         
         
         $("#vendor_table").dataTable({
           "bPaginate": false,
           "aaSorting": [],
           "bInfo" : false
           /*"oLanguage": {
              "sSearch": "Filter records:"
            }*/
         });
      </script>
   </body>
</html>