<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="User Permission";
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
						  <h3>New <?=$form?></h3>
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
              <!--state overview start-->
		  <div class="row">
			<div class="col-sm-12">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
					<div class="panel-body">
						<form role="form" id="permission_add" action="javascript:;" method="post" name="permission_add">
							
							<div class="form-group col-md-12">
								<div class="col-md-2"></div>
								<div class="col-md-2" style="text-align:right;"> 
									<label for="vendor_name">User Name</label>
								 </div>
								 <div class="col-lg-3">
								 <select class="select2" id="usertype_id" name="usertype_id" onchange="load_menu(this.value)">
								  <option value=""> Select User Name</option>
								  <?php //echo getusertype($dbcon,0," and (usertype_id!=1 or company_id=".$_SESSION['company_id'].")")
										echo getalluser($dbcon,0);
								  ?>
								  </select>
								 </div> 
							</div>				
							<div id="show_menu"></div>
							 	</div>	<div class="col-md-5"></div>
							 	<input type='hidden' name='mode' id='mode' value='add' />				  
							  <button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
		
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/permission_mst.js?v1.2"></script>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
</body>
</html>
