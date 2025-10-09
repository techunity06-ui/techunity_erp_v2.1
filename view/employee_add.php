<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Employee";
	if(strpos($_SERVER[REQUEST_URI], "employee_edit")==false) {
		$mode="Add";
		$countryid="101";
		$stateid="1";
		$cityid="1";
	}
	else {
		$mode="Edit";
		$employee_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from employee_mst where employee_id=$employee_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$countryid=$rel['countryid'];
		$stateid=$rel['stateid'];
		$cityid=$rel['cityid'];
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>EMPLOYEE</title>
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
					<li><a href="<?=ROOT.'employee_list'?>"><?=$form?> List</a></li>
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
				<form class="form-horizontal" role="form" id="employee_add" action="javascript:;" method="post" name="employee_add">
					<div class="row">
						<div class="col-md-10">
							
							<div class="form-group">
								<label class="col-md-3 control-label">Employee Name *</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Employee Name" title="Employee Name" name="employee_name" id="employee_name" value="<?=$rel['employee_name']?>"/>
								</div>
							</div> 
							<div class="form-group">
								<label class="col-md-3 control-label">Address</label>
								<div class="col-md-6 col-xs-11">
									<textarea class="form-control" placeholder="Employee Address" name="employee_address" id="employee_address"><?=$rel['employee_address']?></textarea>
								</div>
							</div>  
							<div class="form-group">
								<label class="col-md-3 control-label">Select Country *</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="countryid" id="countryid" onChange="load_state(this.value,'stateid','')">
										<?=get_country($dbcon,$countryid)?>				
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Select State *</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="stateid" id="stateid" onChange="load_city(this.value,'cityid','')">
										<option value="">Select State</option>	
										<?//=getstate($dbcon,$rel['stateid'])?>				
									</select>
								</div>
								<input type="button"  name="addState" id="addState" data-toggle="modal" data-target="" onclick="add_state();" class="btn btn-primary" value="+ Add State"/>
							</div>	
							<div class="form-group">
								<label class="col-md-3 control-label">Select City *</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="cityid" id="cityid">
										<option value="">Select City</option>	
									</select>
								</div>
								<input type="button" name="addCity" id="addCity" data-toggle="modal" data-target="" onclick="add_city();" class="btn btn-primary" value="+ Add city"/>
							</div>	
							
							<div class="form-group">
								<label class="col-md-3 control-label">Mobile No. </label>
								<div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Mobile No." name="emp_mobile" id="emp_mobile" value="<?=$rel['emp_mobile']?>"  />
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-3 control-label">Email(User name)</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Email" title="Email" name="emp_email" id="emp_email" value="<?=$rel['emp_email']?>" <?php if($mode=='Edit'){ ?> readonly <?php } ?>   />
								</div>	
							</div> 
							<div class="form-group">
								<label class="col-md-3 control-label">Password</label>
								<div class="col-md-6 col-xs-11">
									<input type="text" class="form-control" placeholder="Password" title="Password" name="emp_password" id="emp_password" value="<?=$rel['emp_password']?>" />
								</div>	
							</div> 
							
							<div class="form-group">
								<label class="col-md-3 control-label">Zone</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="zone_id" id="zone_id">
										<?=get_zone($dbcon,$rel['zone_id'])?>				
									</select>
								</div>	
								<input type="button" name="addZone" id="addZone" data-toggle="modal" data-target="#add_zone_modal" class="btn btn-primary" value="+ Add Zone"/>
							</div>  
							<div class="form-group">
								<label class="col-md-3 control-label">Opening Balance</label>
								<div class="col-md-3 col-xs-11">
									<input type="number" class="form-control" placeholder="Amount" name="opening_balance" id="opening_balance" value="<?=$rel['opening_balance']?>" min="0" title="Enter Opening Balance"/>
								</div>
								<div class="col-md-3 col-xs-11">
									<select class="select2" name="balance_typeid" id="balance_typeid" title="Select Type">
										<?=getbalance_type($dbcon,$rel['balance_typeid'])?>				
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-md-3 control-label">Employee Type</label>
								<div class="col-md-3 col-xs-11">
									<select class="form-control" name="e_type" id="e_type">
										<option value="">--Select Employee Type--</option>
										<?php echo get_all_emp_type($dbcon,$rel['e_type']); ?>
									</select>
								</div>
								
							</div>
							<div class="form-group">
								<div class="checkbox">
									<label class="col-md-offset-3">
										<input type="checkbox" id="multi_company" name="multi_company" <?=($mode=="Add"?'checked':($rel['multi_company']=="1"?'checked':''))?> value="1">  View in all Company
									</label>
								</div>
							</div>
							<button type="submit" class="btn btn-success">Submit</button> &nbsp;
						<a href="<?=ROOT.'employee_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>					</div>
					</div><!--Vendor row end-->	
					<input type="hidden" name="mode" id="mode" value="<?=$mode?>" />
					<input type="hidden" name="eid" id="eid" value="<?=$rel['employee_id']?>" />
					
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

<?php include_once('../include/add_zone.php');?>
<?php include_once('../include/add_city.php');?>
<?php include_once('../include/add_state.php');?>
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   
<script src="<?=ROOT?>js/app/employee_mst.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
<script src="<?=ROOT?>js/app/zone_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
load_state(<?=$countryid?>,'stateid',<?=$stateid?>);
load_city(<?=$stateid?>,'cityid',<?=$cityid?>);
</script> 
</body>
</html>
