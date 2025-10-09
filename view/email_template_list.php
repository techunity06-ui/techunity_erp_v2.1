<?php 
   session_start();
   include_once("../config/config.php");
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   include_once("../config/session.php");
   $token = md5(rand(1000,9999));
   $_SESSION['token'] = $token;
   $form="Email & SMS Template";
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
   if(empty($_SESSION['start'])){
   	$start=date('1-m-Y');
   	$end=date("d-m-Y");
   }else{
   	$start=$_SESSION['start'];
   	$end=$_SESSION['end'];
   }
   
   //Amish Soni 06-01-2021
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		EMAIL_TEMPLATE_SLUG_CREATE
	]);

  //  $mydir = '../print/view/';   
  // $myfiles = array_diff(scandir($mydir), array('.', '..','.htaccess'));   
  // print_r($myfiles); 
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>EMAIL & SMS TEMPLATE LIST</title>
      <?php include_once('../include/include_css_file.php');?>
   </head>
   <body>
      <section id="container" >
         <?php include_once('../include/include_top_menu.php');?>
         <!--sidebar start-->
         <?php include_once('../include/left_menu.php');?>
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
                           <div class='col-lg-5 col-md-7 col-xs-9'>
                              <div class="form-group">
								<label class="control-label col-md-4">Choose Module</label>
								<div class="col-md-7">
									<select class="select2" name="module_id" id="module_id" onChange="load_email_datatable();">
										<?=get_email_module_list($dbcon);?>	
									</select>
								</div>
							</div>
                           </div>
                           <?php //Amish Soni 06-01-2021
							      if(in_array(EMAIL_TEMPLATE_SLUG_CREATE, $bulkAccessArray)) { ?>
                              <span class="tools pull-right">
                                 <a href="<?=ROOT.'email_template'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>					
                              </span>
                           <?php } ?>
                        </header>
                        <div class="panel-body">
                           <div class="adv-table">
                              <table class="display table table-bordered table-striped" id="dynamic-table">
                                 <thead>
                                    <tr>
                                       <th>#</th>
                                       <th>Template Title</th>
                                       <th>Module Name</th>
                                       <th>Created Date</th>
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
         <?php include_once('../include/footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once('../include/include_js_file.php');?>   
      <script src="js/app/email_template.js"></script>
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