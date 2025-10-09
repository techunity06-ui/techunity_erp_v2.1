<?php 
	// session_start();
	include_once("../config/config.php");
	// include_once("../config/session.php");
	// include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
  <section id="container" >
      
      <section id="main-content">
          <section class="wrapper">
			
		
		  <div class="row">
		
				<div class="col-12">
					<img src="<?=DOMAIN?>img/maintanance.png">
				</div>
			
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>


    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	
  </body>
</html>
