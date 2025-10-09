<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");

	$form="Leave Application";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
	if(empty($_SESSION['start'])) {
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else {
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../../include/include_css_file.php');?>

</head>
<body>
  <section id="container" >
      <?php include_once('../../include/include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once('../../include/left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
           <section id="main-content">
			<section class="wrapper">
			
			<?php//include_once('../include/equick_link.php');?>
     		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?=$mode.' '.$form?> List</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li ><a href="<?= ROOT . HRMS_ROOT . 'hrms_holiday_list'?>"><?=$form?> List</a></li>
							 
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
				  <div class='col-lg-5 col-md-7 col-xs-9'>
					<div class="form-group">
                                  <label class="control-label col-lg-4 col-md-4 col-xs-3">Choose Date</label>
                                  <div class=" col-lg-8 col-md-8 col-xs-9">
                                       <div class="input-group date form_datetime-component">
                                    		<input type="hidden" id="from_date"  value="<?=$start?>">
											<input type="hidden" id="to_date"  value="<?=$end?>">
         					 		        <input type="text" id="rep_date"  onChange="reload_data();;" class="form-control datepikerdemo" value="">
											<span class="input-group-btn">
												<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
                                      </div>
                                  </div>
                              </div>
						</div>	
					<span class="tools pull-right">
						<a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_application_add'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
					</span>
					</header>	
					 <div class="panel-body">
					 	<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="dynamic-table">
								<thead>
									<tr>
										<th>Series No.</th>
										<th>Series Leave No</th>
										<th>Company Name</th>
										<th>Employee Name</th>
										<th>Leave From Date</th>
										<th>Leave To Date </th>
										<th>Half Day Leave</th>
										<th>Leave Approver Name</th>
										<th>Leave Application Status</th>
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
		<?php include_once('../../include/preview_approval_hist.php');?>
		<?php include_once('../../include/footer.php');?>
      <!--footer end-->
  </section>
  <!-- js placed at the end of the document so the pages load faster -->
   <?php include_once('../../include/include_js_file.php');?>   
   <script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_leave_application.js?<?=time()?>"></script>
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
<?php //Hide approve btn if not allowed
	$mod_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);
	if(!$mod_btn_per){
?>	
	$('#mod_per_div_sec').hide();
<?php } ?>
</script>
</body>
</html>
