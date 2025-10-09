<?php 
session_start();
include('../include/urlfile.php');
// include(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
// error_reporting(E_ALL);
$infopage = pathinfo( __FILE__ );
$_SESSION['page']= 'crm/'.$infopage['filename'];
$form="Appointment";
$countryid='101';
$stateid='1';
$cityid='1';
$branch_id = $_SESSION['branch_id'];    
// $bulkAccessArray = canCheckPermissionAccess($dbcon, [
// 	APPOINTMNET_SLUG_CREATE,
// 	APPOINTMNET_SLUG_EDIT
// ]);

if(strpos($_SERVER['REQUEST_URI'], "appointment_edit")==true) {
	// if(!in_array(APPOINTMNET_SLUG_EDIT,$bulkAccessArray)){
	// 	header("Location: ".DOMAIN."permission_access");
	// }
	$mode="Edit";
	$task_id=$dbcon->real_escape_string($_REQUEST['id']);
	// echo $task_id;
	$query="select task.*,usr.user_name from tbl_task as task
	left join users as usr on usr.user_id=task.user_id
	where task.task_id=$task_id";	
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	$user_name=$rel['user_name'];
	$task_rel_id=$rel['task_rel_id'];
	$inquiry_id=$rel['inquiry_id'];
	$assign_user_ids=$rel['assign_user_ids'];
	$appointment_start_time='';$appointment_end_time='';$alert_date_time='';
	if($rel['appointment_start_time']!="1970-01-01 00:00:00" && $rel['appointment_start_time']!="0000-00-00 00:00:00"){
		$appointment_start_time=date('d-m-Y h:i A',strtotime($rel['appointment_start_time']));
	}
	if($rel['appointment_end_time']!="1970-01-01 00:00:00" && $rel['appointment_end_time']!="0000-00-00 00:00:00"){
		$appointment_end_time=date('d-m-Y h:i A',strtotime($rel['appointment_end_time']));
	}
	if($rel['alert_date_time']!="1970-01-01 00:00:00" && $rel['alert_date_time']!="0000-00-00 00:00:00"){
		$alert_date_time=date('d-m-Y h:i A',strtotime($rel['alert_date_time']));
	}
}
else {
	// if(!in_array(APPOINTMNET_SLUG_CREATE,$bulkAccessArray)){
	// 	header("Location: ".DOMAIN."permission_access");
	// }
	$mode="Add";
	$inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
	$task_rel_id=5;
		/*if($inquiry_id)
		{
			$task_rel_id=5;//Fixed Inquiry ID
		}*/
		$inquiry_date=date('d-m-Y');
		$user_name=$_SESSION['user_name'];
		$appointment_start_time=date('d-m-Y h:i A');
		$appointment_end_time=date('d-m-Y h:i A');
		$assign_user_ids=$_SESSION['user_id'];
	}
	
	$back_link = $_SERVER['HTTP_REFERER'];	

	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>APPOINTMENT</title>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container">
			<?php include_once('../../include/include_top_menu.php');?>
			<!--sidebar start-->
			<?php include_once('../../include/left_menu.php');?>
			<!--sidebar end-->
			<!--main content start-->
			<section id="main-content">
				<section class="wrapper">

					<div class="row">
						<div class="col-lg-12">
							<!--breadcrumbs start -->
							<section class="panel">
								<header class="panel-heading">
									<a href="<?=$back_link?>" type="button" class="btn btn-info" style="float:right;"><i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back</a>
									<h3><?=$mode .' '.$form?></h3>
									<!--<div class="text-center">Owner : <strong><?=$user_name?></strong></div>-->
									<?php 
		/*//This is where you put the date, but I use the current date for this example
		$date = date("Y-m-d H:i:s", strtotime("14-11-2019 01:02 PM"));

		//Convert the variable date using strtotime and 30 minutes then format it again on the desired date format
		$add_min = date("Y-m-d H:i:s", strtotime($date . "-1440 minutes"));
		echo  $date . "<br />"; //current date or whatever date you want to put in here
		echo  $add_min; //add 30 minutes*/
		?>
	</header>	
	<div class="">
		<ul class="breadcrumb">
			<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
			<li><a href="<?=ROOT.CRM_ROOT.'appointment_list'?>"><?=$form?> List</a></li>
		</ul>
	</div>
</section>
<!--breadcrumbs end -->
</div>	
</div>
<!--state overview start-->
<div class="row">			
	<div class="col-md-12">
		<section class="panel">
			<header class="panel-heading">
				New <?=$form?>
			</header>	
			<div class="panel-body">
				<form class="form-horizontal" role="form" id="appointment_add" action="javascript:;" method="post" name="appointment_add">
					<div class="row">
						<?php if($mode != 'Add' && $task_rel_id=='5'){?>
							<div class="col-md-12">
								<div class="col-md-6 col-md-offset-1"> 
									<div class="form-group text-center">
										<button type="button" class="btn  btn-info" data-original-title="View History" data-toggle="tooltip" data-placement="top" onClick="view_followup_hist(<?=$inquiry_id?>)"><i class="fa fa-history"></i> View History</button>
										<a href="<?=ROOT.CRM_ROOT.'inquiry_view/'.$inquiry_id?>" class="btn btn-primary"><i class="fa fa-eye"></i> View Inquiry</a>
									</div>
								</div>
							</div>
							<?php }?>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8'); ?>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-4 control-label">Location*</label>
									<div class="col-md-8"> 
										<input type="text" class="form-control" id="task_location" name="task_location" placeholder="Location" value="<?=$rel['task_location']?>">
									</div>
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-4 control-label">Full Day Event</label>
									<div class="col-md-8"> 
										<label class="col-md-3 col-sm-6 " style="font-weight:bold;"><input type="radio" id="full_day_event_yes" name="full_day_event" style="width: 15px;height: 15px;" value="1" <?=($rel['full_day_event']=='1')?'checked':''?>> YES</label>
										<label class="col-md-3 col-sm-6" style="font-weight:bold;"><input type="radio" id="full_day_event_no" name="full_day_event" style="width: 15px;height: 15px;" value="0" <?=($rel['full_day_event']!='1')?'checked':''?>> No</label>
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-4 control-label">Start Time*</label>
									<div class="col-md-8"> 
										<div data-date="<?=$appointment_start_time?>" class="input-group date appointment-start-form_datetime-meridian">
											<input type="text" class="form-control" value="<?=$appointment_start_time?>" name="appointment_start_time" id="appointment_start_time">
											<div class="input-group-btn">
												<button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
											</div>
										</div>
									</div>
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-4 control-label">End Time*</label>
									<div class="col-md-8"> 
										<div data-date="<?=$appointment_end_time?>" class="input-group date appointment-end-form_datetime-meridian">
											<input type="text" class="form-control" value="<?=$appointment_end_time?>" name="appointment_end_time" id="appointment_end_time">
											<div class="input-group-btn">
												<button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
											</div>
										</div>
									</div>
								</div>	
							</div>
							
							<div class="clearfix"></div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-md-2 control-label">Subject*</label>
									<div class="col-md-6"> 
										<input type="text" class="form-control" id="appointment_subject" name="appointment_subject" placeholder="Subject" value="<?=$rel['appointment_subject']?>">
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-md-2 control-label">Remark</label>
									<div class="col-md-6"> 
										<textarea class="form-control" id="task_remark" name="task_remark" style="resize:both;" placeholder="Remark" rows="5"><?=$rel['task_remark']?></textarea>
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-md-2 control-label">Invites To</label>
									<div class="col-md-6"> 
										<select class="select2" id="assign_user_ids" name="assign_user_ids[]" title="Choose Assign User" placeholder="Choose Assign User" multiple="multiple" required>
											<?=get_assign_users($dbcon, $assign_user_ids, " and user_type in(2,8,9,21) ");?>
										</select>
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-4 control-label">Related To</label>
									<div class="col-md-6"> 
										<select class="select2" id="task_rel_id" name="task_rel_id" onchange="get_rel_task_divs(this.value)">
											<?=get_rel_task($dbcon,$task_rel_id);?>
										</select>
									</div>
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<div id="gen_rel_div">
										<label class="col-md-2 control-label">Name</label>
										<div class="col-md-8"> 
											<input type="text" class="form-control" id="task_name" name="task_name" value="<?=$rel['task_name']?>" placeholder="Task Name">
										</div>
									</div>
									<div id="person_rel_div" style="display:none;">
										<div class="col-md-10"> 
											<select class="select2" id="c_con_id" name="c_con_id">
												<?=get_contactperson_all($dbcon,$rel['c_con_id']);?>
											</select>
										</div>
										<div class="col-md-2"> 
											<button type="button" class="btn btn-primary" title="View Details" onclick="preview_rel_types()"><i class="fa fa-eye"></i></button>
										</div>
									</div>
									<div id="company_rel_div" style="display:none;">
										<div class="col-md-10"> 
											<select class="select2" id="cust_id" name="cust_id">
												<=getcust_crm($dbcon,$rel['cust_id']);?>
											</select>
										</div>
										<div class="col-md-2"> 
											<button type="button" class="btn btn-primary" title="View Details" onclick="preview_rel_types()"><i class="fa fa-eye"></i></button>
										</div>
									</div>
									<div id="inq_rel_div" style="display:none;">
										<div class="col-md-10"> 
											<select class="select2" id="inquiry_id" name="inquiry_id">
												<?=get_inquiry($dbcon,$inquiry_id);?>
											</select>
										</div>
										<div class="col-md-2"> 
											<button type="button" class="btn btn-primary" title="View Details" onclick="preview_rel_types()"><i class="fa fa-eye"></i></button>
										</div>
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-12">
								<div class="form-group">
									<?php if($mode=='Add'){?>
										<label class="col-md-2 control-label">Alert</label>
										<div class="col-md-6"> 
											<select class="select2" id="task_alert_id" name="task_alert_id">
												<?=get_task_alert_types($dbcon,$rel['task_alert_id']);?>
											</select>
										</div>
										<?php }else if($alert_date_time){?>
											<label class="col-md-2 control-label">Alert Date</label>
											<div class="col-md-6">
												<div data-date="<?=$alert_date_time?>" class="input-group date form_datetime-meridian">
													<input type="text" class="form-control" value="<?=$alert_date_time?>" name="alert_date_time" id="alert_date_time">
													<div class="input-group-btn">
														<button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
													</div>
												</div>
											</div>
											<?php }?>
										</div>	
									</div>
									<div class="clearfix"></div>
									<?phpif($mode != 'Add') { ?>
										<div class="col-md-12">
											<div class="form-group">
												<label class="col-md-2 control-label">Status</label>
												<div class="col-md-6"> 
													<select class="select2" id="task_status" name="task_status" onchange="validate_close_remark(this.value);">
														<option value="0" <?= ($rel['task_status']=='0' ? 'selected="selected"':'') ?>>Pending</option>
														<option value="1" <?= ($rel['task_status']=='1' ? 'selected="selected"':'') ?>>Close</option>
													</select>
												</div>
											</div>
										</div>
										<div class="clearfix"></div>
										<div class="col-md-12" id="closed_remark_div" style="display: none;">
											<div class="form-group">
												<label class="col-md-2 control-label">Close Remark*</label>
												<div class="col-md-6"> 
													<textarea class="form-control" id="closed_remark" name="closed_remark" height="50px" width="300px" required="true" ><?=$rel['lost_reason']?></textarea>
												</div>	
											</div>
										</div>
									<?php} ?>
									<hr/>
									<div class="clearfix"></div>
									
								</div>
								<div class="clearfix"></div>
								<div class="col-md-12 text-center">
									<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
									<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>	
								</div>	
							</div>
						</div><!--Vendor row end-->	
						<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
						<input type='hidden' name='eid' id='eid' value='<?=$rel['task_id']?>' />
						<input type='hidden' name='back_link' id='back_link' value='<?=$back_link?>' />

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

<?php include_once('../include/preview_rel_details.php');?>
<?php include_once('../../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/appointment.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
	<?php if($mode!='Add'){?>
		$('#task_rel_id').select2("readonly",true);
		$('#c_con_id').select2("readonly",true);
		$('#cust_id').select2("readonly",true);
		$('#inquiry_id').select2("readonly",true);
		<?php }?>
		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});
		var date = new Date();
    var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
    var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate()+15); //end date should not greater than 15 days
    $(".form_datetime-meridian").datetimepicker({
    	format: "dd-mm-yyyy HH:ii P",
    	showMeridian: true,
    	autoclose: true,
    	todayBtn: true,
    	pickerPosition: "bottom-left",
    	startDate: today
    });
    var startDate;
    $(".appointment-start-form_datetime-meridian").datetimepicker({
    	format: "dd-mm-yyyy HH:ii P",
    	showMeridian: true,
    	autoclose: true,
    	todayBtn: true,
    	pickerPosition: "bottom-left",
    	startDate: today
    }).on('change.dp', function (e) {
    	startDate = e.target.value;
    });
    $(".appointment-end-form_datetime-meridian").datetimepicker({
    	format: "dd-mm-yyyy HH:ii P",
    	showMeridian: true,
    	autoclose: true,
    	todayBtn: true,
    	pickerPosition: "bottom-left",
    	startDate: today
    }).on('change.dp', function (e) {
    	var endDate = e.target.value;
    	if(startDate>endDate){
    		alert('End datetime should be greater than start datetime.');
    		$('.appointment-end-form_datetime-meridian').datetimepicker('update',startDate);
    	}
    });
/*$(function() { 
	$('#inquiry_date').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
	<if($mode=='Add')
	{?>
	,startDate: 'd'//don't allow today and past dates
	<}?>
	});
});*/
<?php if($task_rel_id){?>
	$(document).ready(function() {
		get_rel_task_divs(<?=$task_rel_id?>);
	}); 
	<?php }?>
//$('#task_rel_id').select2('readonly',true);
//$('#inquiry_id').select2("readonly",true);
</script>
</body>
</html>