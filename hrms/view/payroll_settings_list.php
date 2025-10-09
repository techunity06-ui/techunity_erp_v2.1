<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");

	$form="Payroll Settings";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
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
			
			<?php //include_once('../include/equick_link.php');?>
     		<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?=$mode.' '.$form?> List</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT . HRMS_ROOT . 'payroll_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li ><a href="<?=ROOT . HRMS_ROOT . 'payroll_settings_list'?>"><?=$form?> List</a></li>
							 
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
	                                <div class=" col-lg-8 col-md-8 col-xs-9"></div>
	                    </div>
				    </div>	
					<span class="tools pull-right">
						<a href="<?=ROOT.'hrms/payroll_settings_add'?>" ><button class="btn btn-success btn-flat" >Create <?=$form?></button></a>	
					</span>
					</header>	
					 <div class="panel-body">
					 	<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="dynamic-table">
								<thead>
									<tr>
										<th>Series No.</th>
										<th>Company Name</th>
										<th>Calculate Payroll</th>
										<th>Fraction Of Daily</th>
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
   <script src="<?= ROOT . HRMS_ROOT ?>js/app/payroll_settings.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
</script>
</body>
</html>
