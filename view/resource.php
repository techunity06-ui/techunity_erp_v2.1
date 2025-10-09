<?php 
    session_start();
    include_once("../config/config.php");
    include_once("../config/session.php");

    include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
    include_once("../include/function_database_query.php");

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
            RESOURCE_VIEW
    ]);
    if(!in_array(RESOURCE_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $form="Resource";
    $infopage = pathinfo( __FILE__ );
    $_SESSION['page']=$infopage['filename'];


    $back="resource";
    $mode="Edit";$direct_add='0';$request=0;
    $purchasecard_date= $date = date('d-m-Y');

    $where = '';
    $branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
    $where_db = check_branch('res', $branch_id);
    $where.=" $where_db and res.company_id=".$_SESSION['company_id'];

    $sql = "SELECT res.*, bms.branch_name FROM `tbl_resource` as res 
    LEFT JOIN branch_mst as bms ON bms.branch_id = res.branch_id
    WHERE `res`.`resource_status`=0  $where ORDER BY `res`.`resource_id` DESC";
    $result=$dbcon->query($sql);
    

   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
      RESOURCE_ADD, RESOURCE_UPDATE
    ]);

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
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="javascript:void(0)"><?=$form?> Details</a></li>
                           </ul>
                        </div>
                     </section>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <?=$form?> Details
                           <span class="tools pull-right">
                            <?php if(in_array(RESOURCE_ADD,$bulkAccessArray)){ ?>
                              <button class="btn btn-success add_btn btn-flat" onClick="add_new_record()">Add Resource</button>
                            <?php } ?>  
                              <a href="<?=ROOT.'resource'?>" class="cancel_btn hide"> <button class="btn btn-default btn-flat">Cancel</button></a>
                           </span>
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
                                                      <th>Branch Name</th>
                                                      <th>Working Hours</th>
                                                      <th>Hours Cost</th>
                                                      <th>Resource Value</th>
                                                      <th>Maintance Period</th>
                                                      <th>Date</th>
                                                     <!--  <th>Status</th> -->
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   <?php 
                                                      if(mysqli_num_rows($result)>0)
                                                        {
                                                          $i=0;  
                                                          while($rel=mysqli_fetch_assoc($result))
                                                          { 
                                                             if($rel['resource_status']=='0'){
                                                               $stage = 'Approved';
                                                              }else{
                                                               $stage = 'Pending';
                                                              }
                                                      ?>
                                                   <tr onclick="get_item_information('<?=$rel['resource_id']?>', '<?=$rel['ledger_id']?>', '<?=$rel['branch_id']?>')" data-id="<?=$rel['resource_id']?>" class="item_<?=$i?>">
                                                      <td><?=$rel['resource_name']?></td>
                                                      <td><?=$rel['branch_name']?></td>
                                                      <td><?=$rel['working_hours']?></td>
                                                      <td><?=$rel['hours_cost']?></td>
                                                      <td><?=$rel['resource_value']?></td>
                                                      <td><?=$rel['maintance_period']?></td>
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
                                          <a data-toggle="tab" href="#po_resource" aria-expanded="true">Resource Details</a>
                                       </li>
                                       <li class="">
                                          <a data-toggle="tab" href="#po_notes" onClick="" aria-expanded="false">Notes</a>
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
                                                            <label class="col-md-4 control-label" style="">Resource Name *</label>
                                                            <div class="col-md-8">
                                                               <input type="text" class="form-control" id="resource_name" name="resource_name" title="Enter Resource Name"  placeholder="Resource Name" value="" required >
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >Working Hours *</label>
                                                            <div class="col-md-7 col-xs-11">
                                                               <input id="working_hours" name="working_hours" type="number" class="form-control" title="Working Hours" value="" placeholder="Working Hours" required>
                                                            </div>
                                                            <label lass="col-md-1 control-label">Hours</label>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Hours Cost *</label>
                                                            <div class="col-md-8 col-xs-11">
                                                                <input id="hours_cost" name="hours_cost" type="number" class="form-control" title="Hours Cost" value="" placeholder="Hours Cost" required>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Resource Value *</label>
                                                            <div class="col-md-8">
                                                               <input type="text" class="form-control" id="resource_value" name="resource_value" title="Enter Resource Value"  placeholder="Resource Value" value="" required >
                                                            </div>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                         <div class="form-group">
                                                            <label class="col-md-4 control-label" >Maintance Period *</label>
                                                            <div class="col-md-7 col-xs-11">
                                                               <input id="maintance_period" name="maintance_period" type="number" class="form-control" title="Maintance Period" value="" required placeholder="Maintance Period"> 
                                                            </div>
                                                            <label lass="col-md-1 control-label">Days</label>
                                                         </div>
                                                      </div>
                                                      <div class="col-md-4">
                                                        <?php echo getBranchBox($dbcon, $branch_id, '', false, true, 'fetch_employee_based_on_branch();'); ?>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-12">
                                                      <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Employee  </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <select class="select2"  name="vender_id" id="vender_id" title="Select Vender">
                                                                <!-- <=getsalaryemployee($dbcon)?> -->
                                                               </select>
                                                            </div>
                                                         </div>
                                                      </div>  
                                                      <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label" style="">Shift Type  </label>
                                                            <div class="col-md-8 col-xs-11">
                                                               <select class="select2"  name="shift_type" id="shift_type" title="Select shift">
                                                                 <?=get_shift_type($dbcon,"")?>
                                                               </select>
                                                            </div>
                                                         </div>
                                                      </div>  
                                                   </div>
                                             </div>
                                          </section>
                                          <?php if(in_array(RESOURCE_UPDATE,$bulkAccessArray)){  ?>
                                           <div class="row">
                                             <button type="submit" class="btn btn-success " id="save" name="save">Update</button>
                                             <div class="col-md-5"></div>
                                          </div>
                                         <?php } ?>
                                       </div>
                                       <div id="po_notes" class="tab-pane">
                                         <textarea id="remark" name="remark" class="form-control" rows="3"></textarea>
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
         <?php include_once('../include/add_cust.php');?>
         <?php //include_once('../include/add_vender.php');?>
         <?php include_once('../include/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/resource.js?<?=time()?>"></script>
      <script src="<?=ROOT.CRM_ROOT ?>js/app/customer.js?<?=time()?>"></script>
      <!--
         <script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
         <script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
         
         -->
      <script>
         /*$("#party_product").select2({
          width: '100%',
          minimumInputLength: 2,
         });  */
         $(".select2").select2({
           width: '100%',
           //minimumInputLength: 2,
         });
         
         /*$("#product_id").select2({
           width: '86%'
         });*/
         $('.default-date-picker').datepicker({
           format: 'dd-mm-yyyy',
           autoclose: true
         });
         
         $(".form_datetime").datetimepicker({
             format: 'dd-mm-yyyy hh:ii',
             autoclose: true,
             todayBtn: true,
             pickerPosition: "bottom-left"
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
      <?php 
         //echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
         //echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
         if($mode=="Add"){
          //echo "<script>show_data();</script>";
          echo "<script>get_series_no(16);</script>";
         }
         if($direct_add=='1'){
          /*echo "<script>entry_po_req_data(".$rel['purchaseorder_id'].");</script>";
          echo "<script>
              $('#po_type_status').attr('style','pointer-events: none;').attr('readonly','readonly');
          </script>";*/
         }
         ?>
   </body>
</html>