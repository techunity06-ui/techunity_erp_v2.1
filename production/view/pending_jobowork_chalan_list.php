<?php 
session_start();   
include('../include/urlfile.php');   
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Pending Jobwork Chalan ";
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
$date=date('d-m-Y');
$branch_id = $_SESSION['branch_id'];
$company_config = getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <title>Jobwork Chalan List</title>
   <?php include_once($include.'/include_css_file.php');?>
</head>
<body>
   <section id="container" >
      <?php include_once($include.'/include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once($include.'/left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
         <section class="wrapper">

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
                  </section>
                  <!--breadcrumbs end -->
               </div>
            </div>
            <!--state overview start-->
            <div class="row">
               <div class="col-sm-12">
                  <section class="panel">
                     <header class="panel-heading">
                        <div class="row ">
                           <div class="col-md-8 mtop20">
                              <div class="form-group">
                                 <div class="col-md-3">
                                    <label>
                                       <input id="status_all" name="jobwork_status" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_datatable();" class="" title="All" value="1">
                                       <div class='external-event label label-primary ui-draggable' style='position: relative;width:70px;'>Done</div>              
                                       
                                    </label>
                                 </div>
                                 <div class="col-md-3">
                                    <label>
                                       <input id="status_pend" name="jobwork_status" checked="checked" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="load_datatable();" class="" title="Pending" value="0">
                                       <div class='external-event label label-success ui-draggable' style='position: relative;width:70px;'>Pending</div>              
                                       
                                    </label>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </header>
                     <div class="panel-body">
                        <div class="adv-table">
                           <table class="display table table-bordered table-striped" id="dynamic-table">
                              <thead>
                                 <tr>
                                 <?php if($company_config['po_work_order_wise']=='1'){ ?>
                                    <th id="workorder_no">Workorder No</th>
                                   <?php } ?>
                                   <th class="jwchalan" style="display:none;">Jobwork Chalan No</th>
                                   <th>Jobwork No</th>
                                   <th>Jobwork Date</th>
                                   <th>Jobcard No</th>
                                   <th>Vender Name</th>
                                   <th>Vehicle No</th>
                                   <?php if($company_config['branch_wise_manage']=='1'){ ?>
                                   <th>Branch</th>
                                   <?php } ?>
                                   <th>Total</th>
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
     <?php include_once($include.'/footer.php');?>
     <!--footer end-->
  </section>
  <!-- js placed at the end of the document so the pages load faster -->
  <?php include_once($include.'/include_js_file.php');?>    
  <?php include_once($include1.'jobwork_rate_modal.php');?>    
  <script src="<?=ROOT.PRODUCTION_ROOT?>js/app/pending_jobwork_chalan_list.js"></script>
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
