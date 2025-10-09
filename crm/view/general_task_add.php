<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	GENERAL_TASK_SLUG_CREATE,
	GENERAL_TASK_SLUG_EDIT,
]);

$infopage = pathinfo( __FILE__ );
$_SESSION['page']= 'crm/'.$infopage['filename'];
$form="General Task";
$countryid='101';
$stateid='1';
$cityid='1';
$email_template_id = '';
$task_alert_id = 2;
$task_due_date='';
$alert_date_time='';


$task_list_link = ROOT.CRM_ROOT."general_task_list";
$companySettings = getCompanySettings($dbcon);
if(strpos($_SERVER['REQUEST_URI'], "task_edit")==true) {
	if(!in_array(GENERAL_TASK_SLUG_EDIT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	
	$mode="Edit";
	$task_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select task.*,usr.user_name from tbl_task as task
	left join users as usr on usr.user_id=task.user_id
	where task.task_id=$task_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	if(!$rel){
		header("Location: ".$task_list_link);
	}
	

	$user_name=$rel['user_name'];
	$task_rel_id=$rel['task_rel_id'];
	$inquiry_id=$rel['inquiry_id'];
	$task_alert_id = $rel['task_alert_id'];
	$task_type_id= $rel['task_type_id'];
	$assign_user = ($rel['assign_user_ids']) ? $rel['assign_user_ids'] : '';
	if($inquiry_id){
		$query = $dbcon -> query("SELECT inquiry_id, inquiry_name, sales_stage_id, opp_id, stage_prob,closed_reason FROM tbl_inquiry as inq WHERE inquiry_id = ".$inquiry_id);
		$inq_data = $query->fetch_assoc();

		$opp_id = $inq_data['opp_id'];
		$sales_stage = $inq_data['sales_stage_id'];
		$stage_prob = $inq_data['stage_prob'];
		$lost_reason = $inq_data['closed_reason'];
	}
	if($opp_id == LOST){
		header("Location: ".$task_list_link);
	}

	if($opp_id == WON){
		header("Location: ".$task_list_link);
	}
	if($rel['task_due_date']!="1970-01-01 00:00:00" && $rel['task_due_date']!="0000-00-00 00:00:00"){
		$task_due_date=date('d-m-Y h:i a',strtotime($rel['task_due_date']));
	}
	if($rel['alert_date_time']!="1970-01-01 00:00:00" && $rel['alert_date_time']!="0000-00-00 00:00:00"){
		$alert_date_time=date('d-m-Y h:i A',strtotime($rel['alert_date_time']));
	}

        // Amish Soni Start 19-01-2021
	$email_template_id = $rel['email_template_id'];
        // Amish Soni End 19-01-2021
}
else {
	if(!in_array(GENERAL_TASK_SLUG_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";
	if(strpos($_SERVER['REQUEST_URI'], "task_flp")==true) {
		$prev_task_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select task.*,usr.user_name from tbl_task as task
		left join users as usr on usr.user_id=task.user_id
		where task.task_id=$prev_task_id";	
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$task_rel_id=$rel['task_rel_id'];
		$task_type_id= $rel['task_type_id'];
		if($rel['inquiry_id']){
			$inquiry_id=$rel['inquiry_id'];
		}
	}
	else{
		$inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
		$task_rel_id='';
		if($inquiry_id){
			$viewmode = 'Add_flp';
			$query = $dbcon -> query("SELECT inquiry_id, inquiry_name, sales_stage_id, opp_id, stage_prob FROM tbl_inquiry as inq WHERE inquiry_id = ".$inquiry_id);
			$inq_data = $query->fetch_assoc();

			$assign_user = ($inq_data['user_id']) ? $inq_data['user_id'] : '';
			$opp_id = $inq_data['opp_id'];
			$sales_stage = $inq_data['sales_stage_id'];
			$stage_prob = $inq_data['stage_prob'];

			$task_type_id = $dbcon->query("select task.task_type_id from tbl_task as task WHERE task.inquiry_id=".$inquiry_id." ORDER BY task_id DESC LIMIT 1")
			->fetch_object()->task_type_id;

                $task_rel_id=5;//Fixed Inquiry ID
            }
        }
		//$inquiry_date=date('d-m-Y');
        $user_name=$_SESSION['user_name'];
        $task_due_date=date('d-m-Y h:i A');
    }
    
    $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
    $set_head=mysqli_fetch_assoc($dbcon->query($set));

    // Amish Soni Start 19-01-2021
    $crm_auto_mail = '';
    $companySettings = getCompanySettings($dbcon);
    if($companySettings) {
    	$crm_auto_mail = $companySettings['crm_auto_mail'];
    }
    $showTemplate = ($crm_auto_mail == 'No');


    if($companySettings['max_followup_date']!=0){
		$max_followup_date=(int)$companySettings['max_followup_date'];
	}else{
		$max_followup_date = 365;
	}
// Amish Soni End 19-01-2021
	//echo $assign_user;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    	<title>GENERAL TASK</title>
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
    								<h3><?=$mode .' '.$form?></h3>
    								<!--<div class="text-center">Owner : <strong><?=$user_name?></strong></div>-->
    							</header>	
    							<div class="">
    								<ul class="breadcrumb">
    									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
    									<li><a href="<?=$task_list_link?>"><?=$form?> List</a></li>
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
    								<?=$mode.' '.$form?>
    							</header>
    							<div class="panel-body">
    								<form class="form-horizontal" role="form" id="task_add" action="javascript:;" method="post" name="task_add">
    									<div class="row">
    										
    										<div class="clearfix"></div>
    										<div class="col-md-12">
    											<div class="form-group">
    												<label class="col-md-2 control-label">Task*</label>
    												<div class="col-md-6">
    													<select class="select2" id="task_type_id" name="task_type_id" required onchange="check_assign_user(this.value)">
    														<option value="">Choose Task Type</option>
    														<?=get_master_category_dtl_general($dbcon,$task_type_id,10);//10:Task?>
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
    														<?=get_general_rel_task($dbcon,$task_rel_id);?>
    													</select>
    												</div>
    											</div>	
    										</div>
    										<div class="col-md-6">
    											<div class="form-group">
    												<div id="gen_rel_div">
    													<label class="col-md-4 control-label">General Task Name *</label>
    													<div class="col-md-6"> 
    														<!-- <input type="text" class="form-control" id="task_name" name="task_name" value="<=$rel['task_name']?>" placeholder="Task Name"> -->
    														<select class="select2" id="gt_id" name="gt_id" required>
    															<?=get_generaltask_all($dbcon,$rel['gt_id']);?>
    														</select>
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
    															<?=getcust($dbcon,$rel['cust_id'],"");?>
    														</select>
    													</div>
    													<div class="col-md-2"> 
    														<button type="button" class="btn btn-primary" title="View Details" onclick="preview_rel_types()"><i class="fa fa-eye"></i></button>
    													</div>
    												</div>
    												<div id="inq_rel_div" style="display:none;">
    													<div class="col-md-10"> 
    														<select class="select2" id="inquiry_id" name="inquiry_id" onchange="load_inquiry_stage(this.value)" >
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
    										<?php $style = '';
    										if(!$inquiry_id){
    											$style = 'style="display:none;"';
    										} ?>
    										<div class="col-md-6" id="task_stage_div" <?= $style ?>>
    											<div class="form-group">
    												<label class="col-md-4 control-label">Stage</label>
    												<div class="col-md-6"> 
    													<select class="select2" id="opp_id" name="opp_id" onchange="show_lost_reason();change_inquiry_stage(this.value);" >
    														<?=get_inquiry_stage($dbcon,$opp_id);?>
    													</select>
    												</div>
    											</div>	
    										</div>
    										<input type="hidden" id="stage_prob" name="stage_prob" class="form-control" value="<?=$stage_prob?>">
    										<div class="col-md-6" id="task_sales_stage_div" <?= $style ?>>
    											<div class="form-group">
    												<label class="col-md-2 control-label" style="white-space:nowrap;">Sales Stage</label>
    												<div class="col-md-6"> 
    													<select class="select2" id="sales_stage_id" name="sales_stage_id">
    														<option value="">Choose Sales Stage</option>
    														<?= get_master_category_dtl($dbcon,$sales_stage,7,"","") ?>
    													</select>
    												</div>	
    											</div>	
    										</div>
    										<div class="clearfix"></div>
    										<div class="col-md-12 lost_reasons" id="lost_reason_div" style="display:none;">
    											<?php if($opp_id == LOST) {
    												$reason_array = json_decode($lost_reason,true);
    												foreach ($reason_array as $reason_id => $remark) { ?>
    													<div class="form-group">
    														<label class="col-md-2 control-label" style="text-align: right;">Reason*</label>
    														<div class="col-md-3"> 
    															<select class="select2 reasonid" id="reason_id" name="reason_id[]">
    																<?= get_lost_reasons($dbcon,$reason_id) ?>
    															</select>
    														</div>
    														<label class="col-md-2 control-label">Reason Remark*</label>
    														<div class="col-md-3"> 
    															<textarea class="form-control reason_remark" name="lost_reason[]" id="lost_reason" style="resize:both;" placeholder="Lost Reason" rows="1"><?= $remark?></textarea>
    														</div>
    													</div>
    												<?php } 
    											} else { ?>
    												<div class="form-group">
    													<label class="col-md-2 control-label" style="text-align: right;">Reason*</label>
    													<div class="col-md-3"> 
    														<select class="select2 reasonid" id="reason_id" name="reason_id[]">
    															<?= get_lost_reasons($dbcon,$id) ?>
    														</select>
    													</div>
    													<label class="col-md-2 control-label">Reason Remark*</label>
    													<div class="col-md-3"> 
    														<textarea class="form-control reason_remark" name="lost_reason[]" id="lost_reason" style="resize:both;" placeholder="Lost Reason" rows="1"/></textarea>
    													</div>	
    													<div class="col-md-2"> 
    														<button type="button" id="reason_btn" class="btn btn-primary" title="View Details" onclick="add_reason_div()"><i class="add_remove_reason fa fa-plus"></i></button>
    													</div>
    												</div>
    											<?php } ?>
    											<input type="hidden" id="counter" name="counter" value="1">
    										</div>
    										<div class="clearfix"></div>
    										<div class="col-md-12">
    											<div class="form-group">
    												<label class="col-md-2 control-label">Task Name</label>
    												<div class="col-md-6"> 
    													<input type="text" class="form-control" value="<?=$rel['task_name']?>" name="task_name" id="task_name" autocomplete="off">
    													
    													
    												</div>
    											</div>	
    										</div>
    										<div class="clearfix"></div>
    										<div class="col-md-12">
    											<div class="form-group">
    												<label class="col-md-2 control-label">Remark</label>
    												<div class="col-md-6"> 
    													<textarea class="form-control" id="task_remark" name="task_remark" style="resize:both;" placeholder="Remark" rows="4"><?=$rel['task_remark']?></textarea>
    												</div>
    											</div>	
    										</div>
    										<div class="clearfix"></div>
    										<div class="col-md-12">
    											<div class="form-group">
    												<label class="col-md-2 control-label">Assign To</label>
    												<div class="col-md-6"> 
    													<select class="select2" id="assign_user_ids" name="assign_user_ids" placeholder="Choose Assign User" required>
    														<!-- <=get_assign_users($dbcon, $rel['assign_user_ids'], " and user_id not in(".$_SESSION['user_id'].")");?> -->
    														<?=getalluser($dbcon, $assign_user);?>
    													</select>
    												</div>
    											</div>	
    										</div>
    										<div class="clearfix"></div>
    										<div class="col-md-6">
    											<div class="form-group"> 
    												<label class="col-md-4 control-label">Priority</label>
    												<div class="col-md-6"> 
    													<select class="select2" id="task_priority_id" name="task_priority_id">
    														<?=get_task_priority($dbcon,$rel['task_priority_id']);?>
    													</select>
    												</div>
    											</div>	
    										</div>
    										<div class="clearfix"></div>
    										<div class="col-md-12">
    											<div class="form-group">
    												<label class="col-md-2 control-label">Next Followup Date</label>
    												<div class="col-md-6"> 
    													<!--<input type="text" class="form-control default-date-picker required valid" id="task_due_date" name="task_due_date" value="<=$rel['task_due_date']?>" placeholder="Due Date">-->
    													<div data-date="<?=$task_due_date?>" class="input-group date form_datetime-meridian">
    														<input type="text" class="form-control" value="<?=$task_due_date?>" name="task_due_date" id="task_due_date" autocomplete="off">
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
    												<label class="col-md-2 control-label">Alert</label>
    												<div class="col-md-6"> 
    													<select class="select2" id="task_alert_id" name="task_alert_id">
    														<=get_task_alert_types($dbcon,$task_alert_id);?>
    													</select>
    												</div>
    											</div>
    										</div>
    										<?phpif($rel['task_alert_id']!='1' && $alert_date_time){?>
    											<div class="col-md-12">
    												<div class="form-group">
    													<label class="col-md-2 control-label">Alert Date</label>
    													<div class="col-md-6">
    														<div data-date="<?=$alert_date_time?>" class="input-group date form_datetime-meridian">
    															<input type="text" class="form-control" value="<?=$alert_date_time?>" name="alert_date_time" id="alert_date_time">
    															<div class="input-group-btn">
    																<button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
    															</div>
    														</div>
    													</div>
    												</div>
    											</div>
    											<?php }?>

    <?php // Amish Soni Start 19-01-2021
    if($showTemplate) { ?>
    	<div class="clearfix"></div>
    	<div class="col-md-12">
    		<div class="form-group">
    			<label class="col-md-2 control-label">Email Template</label>
    			<div class="col-md-6">
    				<select class="select2" id="email_template_id" name="email_template_id">
    					<?php echo getAllEmailSMSTemplate($dbcon,2, $email_template_id) ?>
    				</select>
    			</div>
    		</div>
    	</div>
    <?php }
    // Amish Soni End 19-01-2021 ?>
    <hr/>
    <div class="clearfix"></div>
    
</div>
<div class="clearfix"></div>
<hr/>
<div class="col-md-12">
	<div class="card">
		<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
			<li role="presentation" id="tab2" class="active"><a href="#attch-section" aria-controls="attch-section" role="tab" data-toggle="tab">Attachments</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="attch-section">
				<div class="form-group" style="margin-top:20px;">
					<?phpif($mode!='view'){?>
						<table class="display table table-bordered table-striped">
							<thead>
								<tr>
									<th width="40%" class="text-center">Document Name</th>
									<th width="50%" class="text-center">Upload Document</th>
									<th width="10%" class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<input type="text" class="form-control" id="attachment_name" name="attachment_name" value="" placeholder="Document Name">
									</td>
									<td>
										<input type="file" class="form-control" id="attachment_file" name="attachment_file">
									</td>
									<td>
										<button type="button" class="btn btn-primary" id="task_attch_btn" onclick="add_task_attch_field()">Add</button>
									</td>
								</tr>
							</tbody>
						</table>
					<?php} ?>
				</div>
				<div class="form-group" style="margin-top:20px;" id="task_attch_trn_div"></div>
			</div>
		</div>
	</div>
</div>
<div class="clearfix"></div>
<hr/>
<div class="col-md-12 text-center">
	<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
	<a href="<?=$task_list_link?>" type="button" class="btn btn-danger">Cancel</a>
</div>	
</div>
</div><!--Vendor row end-->	
<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
<input type='hidden' name='eid' id='eid' value='<?=$task_id?>' />
<input type='hidden' name='prev_task_id' id='prev_task_id' value='<?=$prev_task_id?>' />

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

<?php include_once('../../include/preview_rel_details.php');?>
<?php include_once('../../include/footer.php'); ?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>
<script src="<?=ROOT.CRM_ROOT?>js/app/general_task.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});

	var task_type_id = $('#task_type_id').val();
// if(task_type_id === '15' || task_type_id === '20'){
//     $('#assign_user_ids').removeAttr('multiple');
//     $('#assign_user_ids').select2({width: '100%'});
// } else {
//     $('#assign_user_ids').attr('multiple','true');
//     $('#assign_user_ids').select2({width: '100%'});
// }

// var maxLength = 300;
// <?php if($mode=="Edit"){	?>
// 	var str_len = "<= strlen($rel['task_remark']); ?>";
// 	var textlen = maxLength - str_len;
// 	$('#rchars').text(textlen);
// <?php } ?>
// $('#task_remark').keyup(function() {
// 	var textlen = maxLength - $(this).val().length;
// 	$('#rchars').text(textlen);
// });

<?php if($mode!='Add'){?>
	$('#task_rel_id').select2("readonly",true);
	$('#c_con_id').select2("readonly",true);
	$('#cust_id').select2("readonly",true);
	$('#inquiry_id').select2("readonly",true);
<?php} ?>

<?php if($viewmode == 'Add_flp'){?>
	$('#task_rel_id').select2("readonly",true);
	$('#c_con_id').select2("readonly",true);
	$('#cust_id').select2("readonly",true);
	$('#inquiry_id').select2("readonly",true);
<?php} ?>
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});
var max_followup_date = '<?=$max_followup_date?>';
var date = new Date();
var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate()+ parseInt(max_followup_date)); //end date should not greater than 15 days
$(".form_datetime-meridian").datetimepicker({
	format: "dd-mm-yyyy HH:ii P",
	showMeridian: true,
	autoclose: true,
	todayBtn: true,
	pickerPosition: "top-left",
	startDate: today,
	endDate: endDate
});
/*$(".form_datetime-meridian").datetimepicker({
    format: "dd-mm-yyyy HH:ii P",
    showMeridian: true,
    autoclose: true,
    todayBtn: true,
    pickerPosition: "top-left"
});*/
/*$(function() { 
	$('#inquiry_date').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
	<?php if($mode=='Add')
	{?>
	,startDate: 'd'//don't allow today and past dates
	<?php }?>
	});
});*/
<?php if($task_rel_id){?>
	$(document).ready(function() {
		get_rel_task_divs(<?=$task_rel_id?>);
	}); 
<?php} ?>
</script>
</body>
</html>