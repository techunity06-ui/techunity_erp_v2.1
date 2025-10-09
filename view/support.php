<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
<style>

</style>
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
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3>Support Section</h3>
						</header>
						
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
								
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
					<h4> <strong>MetR Technology</strong></h4>
					</header>	
					<div class="panel-body ">
						<!--<iframe src="<?=ROOT.'support-content'?>" height="290px" width="800px"></iframe>-->
						<iframe src="http://metr.co.in/billing360_files/support-content.php" height="290px" width="800px" name="iframe_a"></iframe>
						</div>
					
				</section>
			</div>	
			<div>
	
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
	
<script>

</script>
  </body>
</html>