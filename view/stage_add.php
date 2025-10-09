<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Item";
$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
$comty=mysqli_fetch_assoc($dbcon->query($com));	
	//echo $_SESSION['branch_id'];
	//echo $_SERVER[REQUEST_URI];
if(strpos($_SERVER[REQUEST_URI], "stage_edit")==false) {
	$mode="Add";
	
}
else {
	$mode="Edit";
	$pro_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from stage_mst where stage_id=$pro_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
	
	
	<script type="text/javascript" src="js/jquery.form.min.js"></script>
</head>
<body>
	<section id="container" class="sidebar-closed">
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
								<h3>New <?=$form?>
						  <!--<a href="<?=ROOT.'import_product'?>" >
						  	<button class="btn btn-primary btn-flat pull-right">Import <?=$form?></button></a>-->
						  </h3>
						</header>	
						<div class="">
							<ul class="breadcrumb">
								<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
								<li class="active"><a href="<?=ROOT.'product_list'?>"><?=$form?> List </a></li>
							</ul>
						</div>
					</section>
					<!--breadcrumbs end -->
				</div>	
			</div>
			<!--Customer overview start-->
			
			<div class="row">
				<div class="col-sm-12">
					<section class="panel">
						<header class="panel-heading">
							New <?=$form?> 
							<span class="tools pull-right">
								<a href="javascript:;" class="fa fa-chevron-down"></a>
							</span>
						</header>	
						<div class="panel-body">
							
							<form role="form" id="product_add" action="javascript:;" method="post" name="product_add">
								
								<div class="col-md-12" style="padding-top: 25px;">
									<div class="col-md-12 margin_row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="Product Type" class="col-md-4 control-label">Stage Name*</label>
												<div class="col-md-8 col-xs-11">
													<input type="text"  class="form-control" id="stage_name" name="stage_name" placeholder="Stage Name"  value="<?=htmlspecialchars(stripcslashes($rel['stage_name']))?>" />
												</div>
											</div>							 
										</div>
										<input type="hidden" name="mode" id="mode" value="<?php if($mode=='Add'){ echo "add"; } else { echo "edit"; } ?>" />
										
										<input type="hidden" name="eid_main" id="eid_main" value="<?php if($mode=='Edit'){ echo $rel['stage_id']; } ?>" />
										
									</div> 
									
								</div>
							</section>
						</div>
					</div>
				</div>
				<section>
					
					<div class="row" style="background-color:white !important;padding:10px;">
						<div class="col-md-4 col-md-offset-5">	
							<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							<input type='hidden' name='form_mode' id='form_mode' value='<?php echo $mode; ?>' />				  
							<input type='hidden' name='pid' id='pid' value='<?php if($mode=='Edit'){ echo $rel['product_id']; } else { echo "0"; } ?>' />				  
							<input type='hidden' name='product_model' id='product_model' value='' />				  
							<button type="submit" class="btn btn-shadow btn-success" style="box-shadow: 3px 3px #61a642;">Submit</button>
						</div>
					</div>
					
				</form>
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once('../include/footer.php');?>
		<!--footer end-->
	</section>

	<?php include_once('../include/add_productinpro.php');?>  
	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/stage_mst.js?<?php echo time(); ?>"></script>
	
	
	<script>

		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
		
	</script>

</body>
</html>