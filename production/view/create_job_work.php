<?php 
   session_start();
 	 include('../include/urlfile.php');
   $form="Create Jobwork";
   
   $jobwork_date = date('d-m-Y');
   if(strpos($_SERVER['REQUEST_URI'], "create_job_work")==true)
   {
   	$mode="Add";
   	$process_id=$dbcon->real_escape_string($_REQUEST['process_id']);
   	$product_id=$dbcon->real_escape_string($_REQUEST['product_id']);
      $vendor_id=$dbcon->real_escape_string($_REQUEST['vendor_id']);
      $edit_branch_id=$dbcon->real_escape_string($_REQUEST['branch_id']);
   }
   
   $branch_id = $_SESSION['branch_id'];

   $getspecialConfiguration=getspecialConfiguration($dbcon);
   $company_config = getCompanyConfiguration($dbcon);

   $required = "";

   if($getspecialConfiguration['hermattic_permission']=="1") {
       $required = "required";
   }

   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once($include.'include_css_file.php');?>
   </head>
   <body >
      <section id="container" class="sidebar-closed">
         <?php include_once($include.'include_top_menu.php');?>
         <?php include_once($include.'left_menu.php');?>
         <section id="main-content">
            <section class="wrapper">
               <div class="row">
                  <div class="col-lg-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.PRODUCTION_ROOT.'pending_job_work_list'?>">Pending Jobwork List </a></li>
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
                           <form class="form-horizontal" role="form" id="jobwork_add" action="javascript:;" method="post" name="jobwork_add">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Jobwork No </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="jobwork_no" name="jobwork_no" type="text" class="form-control" title="Jobwork No" value="" placeholder="Jobwork No" readonly >
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label"> Jobwork Date </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="jobwork_date" name="jobwork_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$jobwork_date?>" placeholder="Jobwork Date" readonly >
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label"> Vendor *</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="vender_id" id="vender_id"  title="Select Vendor" onChange="reload_working_data();load_po(this.value);">
                                             <?=getcust($dbcon,$vendor_id);?>	
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
                                             <input id="vehicle_no" name="vehicle_no" type="text" class="form-control" value="" placeholder="Vehicle No"  >
                                          </div>
                                       </div>
                                    </div>
                                    <?php if($company_config['branch_wise_manage']=='1'){ ?>
									<div class="col-md-4">
                                       <div class="form-group">
                                          <?php echo getBranchBox($dbcon, $branch_id,$edit_branch_id, true, true, ''); ?>	
                                       </div>
                                    </div>
                                    <?php }else{ ?>
                                       <input type="hidden" name="branch_id" id="branch_id" value="<?=$company_config['default_branch_id']?>" />
                                    <?php } ?>
                                
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">PO No.* </label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="purchase_id" id="purchase_id"  title="Select Purchase No" onchange="load_po_rate(this.value)">
                                             <option value="" >--Choose PO NO--</option>
                                             </select>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="row">
                                        <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Remark </label>
                                          <div class="col-md-8 col-xs-11">
                                             <textarea class="form-control" name="remark" id="remark" <?=$required?>></textarea>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-12">
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
                                 </div>
                                 <div class="clearfix"></div>
                                 <div class="col-md-12">
                                    <center>
                                       <button type="submit" class="btn btn-success" id="save" name="save">Create Jobwork</button>
                                       <a href="<?=ROOT.PRODUCTION_ROOT.'pending_job_work_list'?>" type="button" class="btn btn-danger">Cancel</a>
                                    </center>
                                 </div>
                                 <input type='hidden' name='mode' id='mode' value='add' />

                                 <input type='hidden' name='process_id' id='process_id' value='<?=$process_id?>' />
                                 <input type='hidden' name='product_id' id ="product_id" value='<?=$product_id?>' />
                                 <!-- <input type='hidden' name='allocate_process_id' id='allocate_process_id' value='<?=$p_id; ?>' />	 -->

                              </div>
                           </form>
                        </div>
                     </section>
                  </div>
               </div>
            </section>
         </section>
         <?php include_once($include.'footer.php');?>
      </section>
      <?php include_once($include.'include_js_file.php');?>   
      <script src="<?=ROOT.PRODUCTION_ROOT?>js/app/pending_jobwork_list.js?<?=time()?>"></script>
      <script>
         $(".select2").select2({
         	width: '100%'
         });
         $('.default-date-picker').datepicker({
         	format: 'dd-mm-yyyy',
         	autoclose: true
         });
      </script>
      <?php
         if($mode=="Add" ){
         	echo "<script>get_series_no_jobwork()</script>";
         } 
      ?>
   </body>
</html>