<?php 
	session_start();
	include_once("../config/config.php");
	//error_reporting(E_ALL);
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../include/dashboard_common_functions.php");
	$form="Cron Email";
	
	$mode="Add";
	$user_name=$_SESSION['user_name'];

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	CRON_EMAIL_SLUG_CREATE,
        CRON_EMAIL_SLUG_LIST
    ]);

    if(!in_array(CRON_EMAIL_SLUG_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>CRON EMAIL</title>
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
		<?php 
			//include_once('../include/quick_link.php');
		?>
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
							<h3><?=$mode.' '.$form?></h3>
							<div class="text-center">Owner : <strong><?=$user_name?></strong></div>
						</header>	
						<div class="">
							<ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'cron_email_add'?>"><?=$form?></a></li>
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
					<div class="panel-body ">
					<form class="form-horizontal" role="form" id="cron_email_add" action="javascript:;" method="post" name="cron_email_add">
						<div class="row">
						
						<div class="col-md-12">
							<div class="col-md-6">
							  <div class="form-group">
								  <label for="email_user_id" class="col-md-4 control-label">User Name</label>
								  <div class="col-md-8 col-xs-11">
									<select class="select2" id="email_user_id" name="email_user_id">
									  			<option value="">SELECT USER NAME</option>
												<?= getalluser($dbcon,$_SESSION['user_id']) ?>
									  		</select>
								  </div>
							  </div>
							</div>					
							<div class="col-md-6" style="display:none;">
							  <div class="form-group">
								  <label for="director_name" class="col-md-4 control-label">Director Name</label>
								  <div class="col-md-8 col-xs-11">
									<input type="text" class="form-control" id="director_name" name="director_name" placeholder=""  value="" />
								  </div>
							  </div>
							</div>
							<div class="col-md-6" style="display:none;">
								  <div class="form-group">
									  <label for="Product Type" class="col-md-4 control-label">E-mail *</label>
									  <div class="col-md-8 col-xs-11">
										<input type="text" class="form-control" name="director_email" id="director_email" value="" />
									  </div>
								  </div>							 
								</div>
							
						</div> 
						</div><!--Vendor row end-->	
							<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
							<input type='hidden' name='eid' id='eid' value='' />				  
								<div class="col-md-12" style="margin-top:5px;"> 
									<div class="col-md-2"></div>
									<button id="btn_submit" type="submit" class="btn btn-success">Submit</button> &nbsp;
									<a href="<?=ROOT.'dashboard'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>					
								
								</div>
						  </form>
				<div class="panel-body">
				  <div class="adv-table" style="margin-top:40px">
				  <table class="display table table-bordered table-striped" id="dynamic-table">
					  <thead>
						  <tr>
							  <th>#</th>
							  <th>Director Name</th>
							  <th>Director Email</th>
							  <th class="hidden-phone">Action</th>					  
						  </tr>
					  </thead>
					  <tbody>
					  </tbody>				 
				  </table>
				  </div>
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
	<?php //include_once('../include/add_city.php');?>
	<?php //include_once('../include/add_state.php');?>
	
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/cron_email.js?<?=time()?>"></script>
   <script type="text/javascript">
   	$("#email_user_id").select2({
   		width: "100%"
   	});
   </script>
  </body>
</html>
