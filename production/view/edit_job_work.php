<?php
session_start();
include('../include/urlfile.php');
$form = "Edit Jobwork";

$mode = "edit";
$job_work_id = $dbcon->real_escape_string($_REQUEST['job_work_id']);

$branch_id = $_SESSION['branch_id'];

$qry = "select * from tbl_job_work where job_work_id = " . $job_work_id;
$result = $dbcon->query($qry);
$rel = brp_mysqli_fetch_assoc($result);
$edit_branch_id = $rel['branch_id'];
$company_config = getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">

<head>
   <title>Edit Jobwork</title>
   <?php include_once($include . 'include_css_file.php'); ?>
</head>
<style type="text/css">
   .is_conversation_toggle {
      background-color: #f1f1f1;
      border: 1px solid #ddd;
      width: 100px;
      border-radius: 2em;
      padding: 5px;
      margin: 0 auto;
      position: relative;
      margin-top: 0px;
   }

   .is_conversation_toggle span {
      text-transform: uppercase;
      font-weight: bold;
      position: absolute;
      top: 5px;
      font-size: 20px;
   }

   .is_conversation_toggle span.yes_span {
      left: -45px;
   }

   .is_conversation_toggle span.no_span {
      left: 115px;
   }

   .is_conversation_toggle .toggle_icon {
      position: relative;
      z-index: 2;
      cursor: pointer;
      -webkit-transition: color 0.5s ease;
      -moz-transition: color 0.5s ease;
      -o-transition: color 0.5s ease;
      transition: color 0.5s ease;
   }

   .is_conversation_toggle .toggle_icon.yes {
      margin-left: 2px;
      float: left;
      width: 50%;
   }

   .is_conversation_toggle .toggle_icon.yes.selected {
      color: #39bf3f;
   }

   .is_conversation_toggle .toggle_icon.no {
      margin-right: 2px;
      float: right;
      width: 45%;
   }

   .is_conversation_toggle .toggle_icon.no.selected {
      color: #bf002d;
   }

   .is_conversation_toggle .toggle {
      width: 42px;
      height: 40px;
      border-radius: 42px;
      background-color: #ddd;
      position: absolute;
      z-index: 1;
      left: 0px;
      top: 0px;
      -webkit-transition: background-color 0.5s ease;
      -moz-transition: background-color 0.5s ease;
      -o-transition: background-color 0.5s ease;
      transition: background-color 0.5s ease;
   }

   .is_conversation_toggle .toggle.yes {
      background-color: rgba(57, 191, 63, 0.5);
   }

   .is_conversation_toggle .toggle.no {
      background-color: rgba(191, 0, 45, 0.3);
   }

   .is_conversation_toggle .clearfix {
      clear: both;
      float: none;
   }

   .is_conversation_wrap {
      display: none;
   }

   .fa-times-rectangle:before,
   .fa-window-close:before {
      margin-left: 12px;
   }

   .fa-check-square:before {
      content: "\f14a";
      margin-left: 0px;
   }

   <style type="text/css">label {
      font-size: 15px;
   }

   .row_margin {
      margin-top: 10px;
   }

   .btn-group-vertical>.btn.active,
   .btn-group-vertical>.btn:active,
   .btn-group-vertical>.btn:focus,
   .btn-group-vertical>.btn:hover,
   .btn-group>.btn.active,
   .btn-group>.btn:active,
   .btn-group>.btn:focus,
   .btn-group>.btn:hover {
      z-index: 2;
      background-color: #bbdce6;
   }

   .control-label {
      font-weight: bold;
   }
</style>

</style>

<body>
   <section id="container" class="sidebar-closed">
      <?php include_once($include . 'include_top_menu.php'); ?>
      <?php include_once($include . 'left_menu.php'); ?>
      <link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
      <section id="main-content">
         <section class="wrapper">
            <div class="row">
               <div class="col-lg-12">
                  <section class="panel">
                     <header class="panel-heading">
                        <h3><?= $form ?></h3>
                     </header>
                     <div class="">
                        <ul class="breadcrumb">
                           <li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
                           <li><a href="<?= ROOT . PRODUCTION_ROOT . 'pending_job_work_list_new' ?>">Pending Jobwork List </a></li>
                        </ul>
                     </div>
                  </section>
               </div>
            </div>
            <div class="row">
               <div class="col-sm-12">
                  <section class="panel">
                     <header class="panel-heading">
                        New <?= $form ?>
                     </header>
                     <div class="panel-body">
                        <form class="form-horizontal" role="form" id="jobwork_add" action="javascript:;" method="post" name="jobwork_add">
                           <input type="hidden" name="job_work_id" id="job_work_id" value="<?=$job_work_id?>">
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Jobwork No </label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="jobwork_no" name="jobwork_no" type="text" class="form-control" title="Jobwork No" value="<?=$rel['job_work_no']?>" placeholder="Jobwork No" readonly>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label"> Jobwork Date </label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="jobwork_date" name="jobwork_date" type="text" class="form-control default-date-picker" title="Date" value="<?= date('d-m-Y',strtotime($rel['job_work_date'])) ?>" placeholder="Jobwork Date" readonly>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label"> Vendor *</label>
                                       <div class="col-md-8 col-xs-11">
                                          <select class="select2" name="vender_id" id="vender_id" title="Select Vendor">
                                             <?= getcust($dbcon, $rel['vender_id']); ?>
                                          </select>
                                       </div>
                                    </div>
                                 </div>
                              </div>

                              <div class="col-md-12">
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Vehicle No *</label>
                                       <div class="col-md-8 col-xs-11">
                                          <input id="vehicle_no" name="vehicle_no" type="text" class="form-control" value="<?= $rel['vehicle_no'] ?>" placeholder="Vehicle No">
                                       </div>
                                    </div>
                                 </div>
                                 <?php if ($company_config['branch_wise_manage'] == '1') { ?>
                                    <div class="col-md-4">
                                       <div class="form-group">

                                          <label class="col-md-4 control-label">Branch *</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" onchange="load_jobwork_product(this.value)" name="branch_id" id="branch_id" required>
                                                <?php $branch = isset($edit_branch_id) ? $edit_branch_id : (isset($branch_id) ? $branch_id : '1000'); ?>
                                                <?= getBranchBox_new($dbcon, $branch, 'all'); ?>
                                             </select>

                                          </div>
                                       </div>
                                    </div>
                                 <?php}else{ ?>
                                       <input type="hidden" name="branch_id" id="branch_id" value="<?=$company_config['default_branch_id']?>" />
                                    <?php} ?>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Jobwork Type</label>
                                       <div class="col-md-8">
                                          <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                             <label class="btn btn-secondary active">
                                                <input type="radio" name="jobcard_type" id="jobcard_type1" autocomplete="off" value="0" checked> Jobcard Wise
                                             </label>
                                             <label class="btn btn-secondary">
                                                <input type="radio" name="jobcard_type" id="jobcard_type2" autocomplete="off" value="1"> Direct
                                             </label>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-12" style="margin:30px;">
                                <!-- Tab Section Start By Umair -->
<section class="panel" style="margin-top: 15px">
    <header class="panel-heading tab-bg-dark-navy-blue ">
        <ul class="nav nav-tabs">
            <li class="active">
                <a data-toggle="tab" href="#jobwork_items" aria-expanded="true">Items</a>
            </li>
            <li class="">
                <a data-toggle="tab" href="#des" aria-expanded="false">Product Description</a>
            </li>
        </ul>
    </header>
    <div class="panel-body">
        <div class="tab-content">
            <div id="jobwork_items" class="tab-pane active">
                <!-- Display The Total Amount -->
                <div class="form-group">
                    <table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
                       <tr id="field">
                          <th width="22%" class="text-center">Product Name</th>
                          <th width="20%" class="text-center ">Process Name</th>
                          <!-- <th width="20%" class="text-center ">Description</th> -->
                          <th width="10%" class="text-center">Quantity</th>
                          <th width="8%" class="text-center">Unit</th>
                          <th width="8%" class="text-center">Material Unit</th>
                          <th width="10%" class="text-center">Material Quantity</th>
                          <th width="10%" class="text-center">Rate</th>
                          <th width="10%" class="text-center">Total</th>
                          <th width="10%" class="text-center"></th>
                       </tr>
                       <tr id="field1">
                          <td style="vertical-align:top;" width="25%">

                             <select class="select2" title="Select Product" name="product_id" onchange="get_process_list(this.value);get_jobwork_rate();" id="product_id" onchange="">
                                <option value="">Choose Product</option>
                                <?php
                                while ($pro_res = brp_mysqli_fetch_assoc($pro_result)) {
                                   $sel = '';
                                   // if($pro_res['product_id']==$product_id) { $sel="selected='selected'"; }
                                   echo '<option ' . $sel . ' value="' . $pro_res['product_id'] . '">' . $pro_res['product_name'] . '</option>';
                                }

                                ?>
                             </select>

                          </td>
                          <td class="hide_product_version">
                             <select class="select2" title="Select Process" name="process_id" id="process_id" onchange="show_jobwork_detail_data(this.value); get_jobwork_rate()">
                                <option value="">Choose Process</option>
                             </select>
                          </td>
                          <!-- <td class="hide_product_version">
                             <textarea class="form-control" placeholder="Enter Description" name="description" id="description">

                       </textarea>
                          </td> -->

                          <td style="vertical-align:top; text-align: center;" class="hide_act_add">
                             <input type="number" title="Enter Qty" min="0" id="product_base_qty" name="product_base_qty" onkeyup="product_convert_qty(1);calculate_total_amount(this.value);" value="" class="form-control" />
                             <label style="color:red; font-weight: bold;font-size: 14px;"> Working Qty : <span id="base_qty">0</span></label>
                             <input type="hidden" title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty" onkeyup="product_convert_qty(2);" value="" class="form-control" />
                             <input type="hidden" id="product_working_qty_hide" name="product_working_qty_hide" value="" />
                             <input type="hidden" id="product_base_qty_hide" name="product_base_qty_hide" value="" />
                             <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
                          </td>
                          <td style="vertical-align:top; text-align: center;" class="hide_act_add">
                             <span id="product_base_unit_name" style="color:red; font-weight: bold;font-size: 20px;"> NOS </span>
                             <input type="hidden" name="product_base_unit" id="product_base_unit" value="" />
                             <input type="hidden" name="product_conv_unit" id="product_conv_unit" value="" />
                          </td>
                          <!--pathik start 14-02-2022 -->

                          <td style="vertical-align:top; text-align: center;" class="">
                             <select class="form-control" name="material_unit" id="material_unit" onchange="get_jobwork_rate();">
                                <?= getunit($dbcon, $id); ?>
                             </select>
                          </td>
                          <td style="vertical-align:top;" class="">
                             <input type="number" title="Enter Qty" min="0" id="material_qty" name="material_qty" onkeyup="calculate_total();" class="form-control" value="" />
                          </td>

                          <!-- pathik end 14-02-2022 -->

                          <td style="vertical-align:top;" class="hide_act_add">
                             <input type="number" title="Enter Qty" min="0" id="rate" name="rate" onkeyup="calculate_total();" class="form-control" value="" />
                          </td>
                          <td style="vertical-align:top;" class="hide_act_add">
                             <input type="number" title="Enter Qty" min="0" id="total_amount" name="total_amount" class="form-control" value="" readonly />
                          </td>
                          <td style="vertical-align:top;">
                             <input type="hidden" name="edit_id" id="edit_id">
                             <input type="hidden" name="p_id" id="p_id">
                             <input type="button" id="addrow" class="btn btn-primary" data-original-title="Add Process" data-toggle="tooltip" data-placement="top" onclick="open_workorder_wise_jobwork_qty();" value="Add" />
                          </td>
                       </tr>
                    </table>
                 </div>
                
            </div>
            <div id="des" class="tab-pane">
                <div class="col-md-12" style="margin-top:12px;padding:10px">
                    <label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;"> Description </label>
                    <div class="col-md-12 mtop20">
                        <div class="form-group">
                        <textarea class="form-control" placeholder="Enter Description" name="description" id="description">

                        </textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
                              </div>

                              <!-- <div class="col-md-12">
                                    <div class="panel-body">
                                       <div class="adv-table">
                                          <table class="display table table-bordered table-striped" id="material_details">
                                             <thead>
                                                <tr>
                                                   <th>#</th>
                                                   <th>Product Name</th>
                                                   <th>Product Category</th>
                                                   <th>Process Name</th>
                                                   <th>Total Qty</th>
                                                   <th>Working Qty</th>
                                                   <th>Rate</th>
                                                   <th>Available Qty</th>
                                                </tr>
                                             </thead>
                                             <tbody id="sub_row_mat"></tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div> -->
                              <div id="tbl_jobwork_data" style="margin: 35px;"></div>
                              <div class="row">
                                 <div class="col-md-4">
                                    <div class="form-group">
                                       <label class="col-md-4 control-label">Remark </label>
                                       <div class="col-md-8 col-xs-11">
                                          <textarea class="form-control" name="remark" id="remark"></textarea>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="clearfix"></div>
                              <div class="col-md-12">
                                 <center>
                                    <button type="submit" class="btn btn-success" id="save" name="save">Update Jobwork</button>
                                    <a href="<?= ROOT . PRODUCTION_ROOT . 'pending_job_work_list_new' ?>" type="button" class="btn btn-danger">Cancel</a>
                                 </center>
                              </div>
                              <input type='hidden' name='mode' id='mode' value='add' />
                              <input type='hidden' name='jobwork_edit_id' id='jobwork_edit_id' value='<?= $job_work_id ?>' />


                              <!-- <input type='hidden' name='allocate_process_id' id='allocate_process_id' value='<?= $p_id; ?>' />	 -->

                           </div>
                        </form>
                     </div>
                  </section>
               </div>
            </div>
         </section>
      </section>
      <?php include_once($include . 'footer.php'); ?>
   </section>
   <?php include_once($include . 'include_js_file.php'); ?>
   <?php include_once($include1.'workorder_wise_jobwork_qty.php');?>  
   <script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
   <script src="<?= ROOT . PRODUCTION_ROOT ?>js/app/pending_jobwork_list_new.js?<?= time() ?>"></script>
   <script>
      $(".select2").select2({
         width: '100%'
      });
      $('.default-date-picker').datepicker({
         format: 'dd-mm-yyyy',
         autoclose: true
      });
      CKEDITOR.replace( 'description', {
          enterMode: CKEDITOR.ENTER_BR
      });
      
   </script>

</body>

</html>