<?php 
session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../config/session.php");
$form="";

	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>UPDATING MODULE</title>
		<?php include_once('../include/include_css_file.php');?>
			
		<style>
		body {
		  background-color: white;
		  
		}

		.glow {
		  font-size: 45px;
		  color: #000;
		  text-align: center;
		  -webkit-animation: glow 1s ease-in-out infinite alternate;
		  -moz-animation: glow 1s ease-in-out infinite alternate;
		  animation: glow 1s ease-in-out infinite alternate;
		}

		@-webkit-keyframes glow {
		  from {
			text-shadow: 0 0 10px #fff, 0 0 20px #fff, 0 0 30px #afadad, 0 0 40px #fff, 0 0 50px #fff, 0 0 60px #fff, 0 0 70px #fff;
		  }
		  
		  to {
			text-shadow: 0 0 20px #fff, 0 0 30px #fff, 0 0 40px #afadad, 0 0 50px #fff, 0 0 60px #fff, 0 0 70px #fff, 0 0 80px #fff;
		  }
		}
		.d {
			<!--font-family: cursive;
			font-family: Monospace;
			font-variant: small-caps;-->
			font-family: Roboto;
		}
		</style>

	</head>
	<body>
		<section id="container">
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3></h3>
								</header>	
								<div class="">
									<!--<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.'grn_list'?>"><?=$form?> List</a></li>
									</ul>-->
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<!--<section class="panel">-->
								<header class="panel-heading"></header>	
								<!--<div class="panel-body"> -->
									<h1 class="glow d">
									<center>
										Sorry,We Are Updating This Module </br>Please Try Atten Some Time <br/>
										Or</br>
										Contact Technical Team for Quick Access.
										</center>
									</h1>
								<!--</div>-->
							<!--</section>-->
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<!--<script src="js/app/grn.js?<?=time()?>"></script>-->
	</body>
</html>
