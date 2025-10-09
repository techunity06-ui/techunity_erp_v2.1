<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Company Setting"
?>
<!DOCTYPE html>
<html lang="en">
<head>
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
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3><?=$form?></h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?=$form?></li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
             </div>
              <!--state overview start-->
		  <div class="row">
			
			<div class="col-md-12">
			<section class="panel">
				  <header class="panel-heading">
					  <?=$form?>
						<span class="tools pull-right">
							<a href="javascript:;">
								<button onclick="trancate_tables(3);" class="btn btn-success btn-flat" >Remove All Data </button></a>	
						</span>
					<!--
						<span class="tools pull-right">
							<a href="javascript:;">
								<button onclick="trancate_tables(4);" class="btn btn-success btn-flat" >Remove Invoice Data  </button>
							</a>	
						</span>
					-->
				  </header>
				  <div class="panel-body">
				  
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
<!-- Modal -->
<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>
<script type="text/javascript">
function trancate_tables(val)
{
	var r= confirm(" Are you want to Remove Data ?");
	if(r) 
	{
	Loading(true);	
	
	window.location=root_domain+'backup/'+val;
	}
}
</script>
  </body>
</html>
