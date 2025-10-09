<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="User";
	if(strpos($_SERVER[REQUEST_URI], "useredit")==false) {
		$mode="Add";
	}
	else {
		$mode="Edit";
		$user_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from users where user_id=$user_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));		
	}
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
					<h3><?=$mode.' '.$form?></h3>
				</header>	
				<div class="">
					<ul class="breadcrumb">
						<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
						<li><a href="<?=ROOT.'user_list'?>">User List</a></li>
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
					<form class="form-horizontal" role="form" id="user_add" action="javascript:;" method="post" name="user_add">
						<div class="row">
							<div class="col-md-10">
								<div class="form-group">
									<label class="col-md-3 control-label">User Name *</label>
									<div class="col-md-6 col-xs-11">
										<input type="text" class="form-control" placeholder="User Name" name="user_name" id="user_name"  value="<?=$rel['user_name']?>"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-3 control-label">User Email *</label>
									<div class="col-md-6 col-xs-11">
										<input type="text" class="form-control" placeholder="User Email" name="user_email" id="user_email" value="<?=$rel['user_mail']?>"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-3 control-label">Password *</label>
									<div class="col-md-6 col-xs-11">
										<input type="password" class="form-control" placeholder="Password" name="password" id="password" value="" <?phpif($mode=="Add"){ echo 'required';}?>/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-md-3 control-label">Address *</label>
									<div class="col-md-6 col-xs-11">
										<textarea id="user_address" name="user_address" class="form-control" rows="5" ><?=$rel['user_address']?></textarea> 
									</div>
								</div>
								
								<div class="form-group">
									<label class="col-md-3 control-label">Select State *</label>
									<div class="col-md-6 col-xs-11">
										<select class="select2" name="stateid" id="stateid" onChange="load_city(this.value,'cityid','0')">
											
										</select>
									</div>
								</div>	
								<div class="form-group">
									<label class="col-md-3 control-label">Select City *</label>
									<div class="col-md-6 col-xs-11">
										<select class="select2" name="cityid" id="cityid"  onChange="">
											
										</select>
									</div>
								</div>	
								<div class="form-group">
									<label class="col-md-3 control-label">Mobile no *</label>
									<div class="col-md-6 col-xs-11">
										<input type="text" class="form-control" placeholder="Customer Mobile" name="user_mobile" id="user_mobile" value="<?=$rel['user_phone']?>"/>
									</div>	
								</div>
								<div class="form-group">
									<label class="col-md-3 control-label">User Type *</label>
									<div class="col-md-6 col-xs-11">
										<select class="form-control" id="usertype_id" name="usertype_id">
											<?=getusertype($dbcon,$rel['user_type']," and (usertype_id=2 or company_id=".$_SESSION['company_id'].")")?>
										</select>
									</div>	
								</div>
								
								<button type="submit" class="btn btn-success">Submit</button> &nbsp;
							<a href="<?=ROOT.'user_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>					</div>
						</div><!--Vendor row end-->	
						<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
						<input type='hidden' name='eid' id='eid' value='<?=$rel['user_id']?>' />
						<input type='hidden' name='company_id' id='company_id' value='<?=$rel['company_id']?>' />
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
<script src="<?=ROOT?>js/app/user.js?<?=time()?>"></script>
<script>

$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});</script>

<?php
	if($mode=="Edit")
	{
		echo "<script>load_state('stateid',".$rel['user_stat'].")</script>";
		echo "<script>load_city(".$rel['user_stat'].",'cityid',".$rel['user_city'].")</script>";
	}
	if($mode=="Add")
	{
		echo "<script>load_state('stateid',1);</script>";
		echo "<script>load_city(1,'cityid',1)</script>";
	}
?>

</body>
</html>
