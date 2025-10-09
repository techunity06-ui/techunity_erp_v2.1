<?php
	session_start();
	include("../../config/config.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php
		$TITLE = "Home";
		include("../common/head.php");
	?>
</head>
<body>
<div id="cl-wrapper" class="sb-collapsed">
	<?php
		include("../common/menu.php");
	?>
	<div class="container-fluid" id="pcont">
		<?php
			include("../common/header.php");
		?>
  
    
	<div class="cl-mcont">		<div class="page-head">
			<h2>Blank Page</h2>
			<ol class="breadcrumb">
			  <li><a href="#">Home</a></li>
			  <li><a href="#">Category</a></li>
			  <li class="active">Sub Category</li>
			</ol>
		</div>		
    <div class="cl-mcont">
      <h3 class="text-center">Content goes here!</h3>
    </div>	</div>
	
	</div> 
	
</div>
  
	<?php include("../common/js.php"); ?>

	<!-- Bootstrap core JavaScript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="js/bootstrap/dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/jquery.flot/jquery.flot.js"></script>
	<script type="text/javascript" src="js/jquery.flot/jquery.flot.pie.js"></script>
	<script type="text/javascript" src="js/jquery.flot/jquery.flot.resize.js"></script>
	<script type="text/javascript" src="js/jquery.flot/jquery.flot.labels.js"></script>
</body>
</html>
