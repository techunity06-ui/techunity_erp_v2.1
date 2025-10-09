<?php 
   session_start();
    include('../include/urlfile.php');
   $form="Create Jobwork";
   
   $chalan_date = date('d-m-Y');
   
   $job_work_id = $dbcon->real_escape_string($_REQUEST['job_work_id']);
   $vendor_id=$dbcon->real_escape_string($_REQUEST['vendor_id']);

   $q = "select * from tbl_job_work  where job_work_id = " . $job_work_id;
   $result=$dbcon->query($q);
   $j_res = brp_mysqli_fetch_assoc($result);

    $job_work_no = $j_res['job_work_no'];

   $vehicle_no = $j_res['vehicle_no'];
   $edit_branch_id = $j_res['branch_id'];

   $branch_id = $_SESSION['branch_id'];

   $company_config = getCompanyConfiguration($dbcon);
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once($include.'include_css_file.php');?>
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
 .fa-times-rectangle:before, .fa-window-close:before {
    margin-left: 12px;
}
.fa-check-square:before {
    content: "\f14a";
    margin-left: 0px;
}
<style type="text/css">
   label{
      font-size: 15px;
   }
   .row_margin
   {
      margin-top:10px;
   }
   .btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
      z-index:2;
      background-color: #bbdce6;
   }
   .control-label{
      font-weight: bold;
   }
</style>
 
      </style>
   <body >
      <section id="container" class="sidebar-closed">
         <?php include_once($include.'include_top_menu.php');?>
         <?php include_once($include.'left_menu.php');?>
         <link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
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
                              <li><a href="<?=ROOT.PRODUCTION_ROOT.'pending_reprocess_jobowork_chalan_list'?>">Pending Jobwork Chalan List </a></li>
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
                           <form class="form-horizontal" role="form" id="create_chalan" action="javascript:;" method="post" name="create_chalan">
                              <div class="row">
                                 <div class="col-md-12">
                                     <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Chalan No </label>
                                             <div class="col-md-8 col-xs-11">
                                             <input id="chalan_no" name="chalan_no" type="text" class="form-control" title="Chalan No" value="<?=load_series_no_using_type_id($dbcon,OUTSIDE_JOB_WORK_CHALAN,$_SESSION['company_id'])?>" placeholder="Chalan No" readonly >
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Jobwork No </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="jobwork_no" name="jobwork_no" type="text" class="form-control" title="Jobwork No" value="<?=$job_work_no?>" placeholder="Jobwork No" readonly >
                                          </div>
                                       </div>
                                    </div>

                                    <?php if($company_config['branch_wise_manage']=='1'){ ?>
                                    <div class="col-md-4">
                                       <div class="form-group">

                                          <label class="col-md-4 control-label">Branch *</label>
                                          <div class="col-md-8 col-xs-11">
                                             <select class="select2" name="branch_id" id="branch_id" required >
                                                <?php $branch = isset($edit_branch_id) ? $edit_branch_id : (isset($branch_id) ? $branch_id : '1000'); ?>
                                                <?=getBranchBox_new($dbcon, $branch,'all');?>
                                             </select>

                                          </div>
                                       </div>
                                    </div>
                                   <?php}else{ ?>
                                       <input type="hidden" name="branch_id" id="branch_id" value="<?=$company_config['default_branch_id']?>" />
                                    <?php} ?>

                                    <div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label"> Chalan Date </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="chalan_date" name="chalan_date" type="text" class="form-control default-date-picker" title="Date" value="<?=$chalan_date?>" placeholder="Chalan Date" readonly >
                                          </div>
                                       </div>
                                    </div>
                                   
                                 </div>

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
                                       <button type="submit" class="btn btn-success" id="save" name="save">Create Chalan</button>
                                       <a href="<?=ROOT.PRODUCTION_ROOT.'pending_reprocess_jobowork_chalan_list'?>" type="button" class="btn btn-danger">Cancel</a>
                                    </center>
                                 </div>
                                 <input type='hidden' name='mode' id='mode' value='save_chalan' />
                                 <input type='hidden' name='job_work_id' id='job_work_id' value='<?=$job_work_id?>' />
                                 
                       
                                 <!-- <input type='hidden' name='allocate_process_id' id='allocate_process_id' value='<?=$p_id; ?>' />  -->

                              </div>
                           </form>
                        </div>
                     </section>
                  </div>
               </div>
            </section>
         </section>
         <?php include_once($include.'footer.php');?>
         <?php include_once($include1.'job_work_challan_edit.php');?>
      </section>
      <?php include_once($include.'include_js_file.php');?>   
      
      <script src="<?=ROOT.PRODUCTION_ROOT?>js/app/create_reprocess_jobowork_chalan.js?<?=time()?>"></script>
      <script>
         $(".select2").select2({
            width: '100%'
         });
         $('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
         });
         
         
      </script>

   </body>
</html>