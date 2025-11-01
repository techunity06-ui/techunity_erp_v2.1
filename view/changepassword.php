<?php 
	session_start();
	include_once("../config/config.php");
	//error_reporting(E_ALL);
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	if($_REQUEST['id']!=$_SESSION['user_id'])
	{
		echo '<meta http-equiv=refresh content=0;url=http:'.ROOT.'dashboard>';
	}
	if(strpos($_SERVER['REQUEST_URI'], "changepassword")==false)
	{
		$mode="Add";		
		$event_date=date('d-m-Y');		
	}
	else
	{
		$mode="Edit";
		$userid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from users where user_id=$userid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$event_date=date('d-m-Y',strtotime($rel['event_date']));		
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>CHANGE PASSWORD</title>
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
          <section class="wrapper" style="min-height:600px;">			
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
				  <section class="panel">
					  <header class="panel-heading">
						  <h3>Change Password</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>							  
							  <li class="active">Change Password</li>
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
					  Change Password
					</header>	
					<div class="col-md-12" style="line-height:100px;"> 
						<div class="form-group">
                        	<div class="col-md-12">
							 </div>
							</div>
					</div>
					
					<div class="row">
							
					<div class="panel-body">
						<form class="form-horizontal" role="form" id="changepassword_form" action="javascript:;" method="post" name="changepassword_form">
						<div class="col-md-6">
							<div class="form-group">
							  <label class="col-md-4 control-label"> User Name *</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="User Name" name="user_name" id="user_name"  value="<?=$rel['user_name']?>" title="Event Name" readonly/>
								</div>								
                             </div>
							<!-- <div class="form-group">
							  <label class="col-md-4 control-label"> Email *</label>
								<div class="col-md-6 col-xs-11">
									<input type="email" class="form-control user_mail" name="user_mail" id="event_name"  value="<?=$rel['user_mail']?>" title="User Mail" readonly/>
								</div>								
                             </div>-->
							 <div class="form-group">
							  <label class="col-md-4 control-label"> New Password *</label>
								<div class="col-md-6 col-xs-11">
									<input type="password" class="form-control" name="new_pass" id="new_pass"  placeholder="New Password" />
								</div>								
                             </div>
							 <div class="form-group">
							  <label class="col-md-4 control-label"> Confirm Password*</label>
								<div class="col-md-6 col-xs-11">
									<input type="password" class="form-control" name="confirm_pass" id="confirm_pass"  placeholder="Confirm Password" />
								</div>								
                             </div>
							 
							<div class="col-md-5"></div>
							<input type='hidden' name='mode' id='mode' value='changepassword' />
							<input type='hidden' name='eid' id='eid' value='<?=$rel['user_id']?>' />				  
							<button type="submit" class="btn btn-info">Submit</button>
							<a href="<?=ROOT.'dashboard'?>"><button type="button" class="btn btn-danger">Cancel</button></a>
							</div>
							</div>
							<!--Vendor row end-->						
						  </form>
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
	<script src="<?=ROOT?>js/app/changepassword_mst.js?v1"></script>
<script>
$(".select2").select2({
		width: '100%'
	});
</script>
  </body>
</html>
