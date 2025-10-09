<?php 
session_start();
include('../include/urlfile.php');
$form="Appointment";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']= 'crm/'.$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
if(empty($_SESSION['start'])) {
  $start=date('1-m-Y');
  $end=date("t-m-Y");
}
else {
  $start=$_SESSION['start'];
  $end=$_SESSION['end'];
}
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    APPOINTMNET_SLUG_CREATE
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>APPOINTMENT LIST</title>
    <?php include_once('../../include/include_css_file.php');?>
</head>
<body>
    <section id="container">
        <?php include_once('../../include/include_top_menu.php');?>
        <!--side bar start-->
        <?php include_once('../../include/left_menu.php');?>
        <!--side bar end-->
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <!--bread crumbs start -->
                        <section class="panel">
                            <header class="panel-heading">
                                <h3> <?=$form?> List</h3>
                            </header>
                            <div class="">
                                <ul class="breadcrumb">
                                    <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                    <li><a href="<?=ROOT.CRM_ROOT.'appointment_list'?>"><?=$form?> List</a></li>
                                </ul>
                            </div>
                        </section>
                        <!--bread crumbs end -->
                    </div>
                </div>

                <div class="row">		
                    <!--state overview start-->
                    <div class="row">			
                        <div class="col-sm-12">
                            <section class="panel">
                                <header class="panel-heading">
                                    <div class="col-md-12">
                                        <?php if(in_array(APPOINTMNET_SLUG_CREATE,$bulkAccessArray)) { ?>
                                            <span class="tools pull-right">
                                                <a href="<?=ROOT.CRM_ROOT.'appointment_add'?>"><button class="btn btn-success btn-flat">Add <?=$form?></button></a>
                                            </span>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-12" style="height:20px;"></div>
                                    <div class="col-md-12">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label col-md-3">Choose Date</label>
                                                <div class="col-md-9">
                                                    <div class="input-group date form_datetime-component">
                                                        <input type="hidden" id="from_date" value="<?=$start?>">
                                                        <input type="hidden" id="to_date" value="<?=$end?>">
                                                        <input type="text" id="rep_date" onChange="load_appointment_datatable();" class="form-control datepikerdemo" value="">
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_appointment_datatable()','4','8'); ?>
                                        </div>	
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <div class="col-md-4">
                                                    <label>
                                                        <div class='external-event label label-primary ui-draggable' style='position: relative;width:110px;'>Future Appointment</div>					
                                                        <input id="task_status_future" name="task_status" checked="checked" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_appointment_datatable();" class="" title="Future" value="0">
                                                    </label>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>
                                                        <div class='external-event label label-warning ui-draggable' style='position: relative;width:110px;'>Missed Appointment</div>					
                                                        <input id="task_status_pending" name="task_status" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_appointment_datatable();" class="" title="Missed" value="2">
                                                    </label>
                                                </div>
                                                <div class="col-md-4">
                                                    <label>
                                                        <div class='external-event label label-success ui-draggable' style='position: relative;width:70px;'>Completed</div>					
                                                        <input id="task_status_complete" name="task_status" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_appointment_datatable();" class="" title="Completed" value="1">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="height:20px;"></div>
                                </header>	
                                <div class="panel-body">
                                    <div class="adv-table">
                                        <table class="display table table-bordered table-striped" id="appointment-datatable">
                                            <thead>
                                                <tr>
                                                    <th>Sr. No.</th>
                                                    <th>Subject</th>
                                                    <th>Regarding</th>
                                                    <th>Name</th>
                                                    <th>Owner </th>
                                                    <th>Appointment Date</th>
                                                    <th>Status</th>
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
            <?php include_once('../../include/footer.php');?>
            <!--footer end-->
        </section>
        <!-- js placed at the end of the document so the pages load faster -->
        <?php include_once('../../include/include_js_file.php');?>   
        <script src="<?=ROOT.CRM_ROOT?>js/app/appointment.js?<?=time()?>"></script>
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
                format: 'DD-MM-YYYY',
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
               $('.datepikerdemo').trigger('click');
           });
       </script>
   </body>
   </html>
