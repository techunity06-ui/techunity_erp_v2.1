<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	TASK_SLUG_CREATE,
	TASK_SLUG_EDIT,
]);

$infopage = pathinfo( __FILE__ );
$_SESSION['page']= 'crm/'.$infopage['filename'];
$form="Task";
$inquiry_id = 0;
$countryid='101';
$stateid='1';
$cityid='1';
$task_alert_id = 2;
$task_due_date='';$alert_date_time='';
$email_template_id = '';
$branch_id = $_SESSION['branch_id'];
$task_remark = '';

$task_in = "checked";
$task_out = "";

$getspecialConfiguration=getspecialConfiguration($dbcon);

if(strpos($_SERVER['REQUEST_URI'], "task_edit")==true) {
	if(!in_array(TASK_SLUG_EDIT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	
	$mode="Edit";
	$task_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select task.*,usr.user_name,user.report_to_user_id from tbl_task as task
	left join users as usr on usr.user_id=task.user_id
	left join users as user on user.user_id=task.assign_user_ids
	where task.task_id=$task_id";	
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."task_list");
	}

	if($rel['task_in_out'] == '0'){
		$task_in = "";
		$task_out = "checked";
	}else{
		$task_in = "checked";
		$task_out = "";
	}
	
	$user_name=$rel['user_name'];
	$task_rel_id=$rel['task_rel_id'];
	$inquiry_id=$rel['inquiry_id'];
	$task_alert_id = $rel['task_alert_id'];
	$task_type_id= $rel['task_type_id'];
	$selected_branch_id = $rel['branch_id'];
	$task_remark =str_ireplace(array("\r","\n",'\r','\n','<br>','\N','\R'),'', $rel['task_remark']);
	$report_to_user_id =$rel['report_to_user_id'];
	$assign_user = ($rel['assign_user_ids']) ? $rel['assign_user_ids'] : $_SESSION['user_id'];
	
	if($inquiry_id){
		$query = $dbcon -> query("SELECT inquiry_id, inquiry_name, sales_stage_id, objection_flag, opp_id, stage_prob,closed_reason FROM tbl_inquiry as inq WHERE inquiry_id = ".$inquiry_id);
		$inq_data = $query->fetch_assoc();

		$opp_id 		= $inq_data['opp_id'];
		$sales_stage 	= $inq_data['sales_stage_id'];
		$stage_prob		= $inq_data['stage_prob'];
		$lost_reason	= $inq_data['closed_reason'];
		$objection_flag 	= $inq_data['objection_flag'];
	}
	
	if($opp_id == LOST){
		header("Location: ".ROOT.CRM_ROOT."task_list");
	}

	if($opp_id == WON){
		header("Location: ".ROOT.CRM_ROOT."task_list");
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
	if(!in_array(TASK_SLUG_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	
	$mode="Add";
	if(strpos($_SERVER['REQUEST_URI'], "task_flp")==true) {
		echo 'in task_flp';
		$prev_task_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select task.*,usr.user_name, user.report_to_user_id from tbl_task as task
		left join users as usr on usr.user_id=task.user_id
		left join users as user on user.user_id=task.assign_user_ids
		where task.task_id=$prev_task_id";	
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$task_rel_id=$rel['task_rel_id'];
		$task_type_id= $rel['task_type_id'];
		$task_remark =str_ireplace(array("\r","\n",'\r','\n','<br>','\N','\R'),'', $rel['task_remark']);
		$report_to_user_id =$rel['report_to_user_id'];
		if($rel['inquiry_id']){
			$inquiry_id=$rel['inquiry_id'];
		}
	}
	else{
		$inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
		$quotation_id=$dbcon->real_escape_string($_REQUEST['quotation_id']);
		$task_rel_id='';
		if($inquiry_id){
			$viewmode = 'Add_flp';
			$query = $dbcon -> query("SELECT inquiry_id, inquiry_name, sales_stage_id, objection_flag, opp_id, stage_prob, branch_id, inq_desc FROM tbl_inquiry as inq WHERE inquiry_id = ".$inquiry_id);
			$inq_data = $query->fetch_assoc();

			$queryts="select user.report_to_user_id, task.user_id from tbl_task as task
	left join users as user on user.user_id=task.assign_user_ids
	where task.task_status = 0 and task.inquiry_id=".$inq_data['inquiry_id'];	
	$rel=mysqli_fetch_assoc($dbcon->query($queryts));

	$report_to_user_id =$rel['report_to_user_id'];
			
			$assign_user = ($inq_data['user_id']) ? $inq_data['user_id'] : $_SESSION['user_id'];
			$opp_id = $inq_data['opp_id'];
			$sales_stage = $inq_data['sales_stage_id'];
			$stage_prob = $inq_data['stage_prob'];
			$selected_branch_id = $inq_data['branch_id'];
			$objection_flag = $inq_data['objection_flag'];
			
			$task_type_id = $dbcon->query("select task.task_type_id, user.report_to_user_id from tbl_task as task left join users as user on user.user_id=task.assign_user_ids WHERE task.inquiry_id=".$inquiry_id." ORDER BY task_id DESC LIMIT 1")
			->fetch_object()->task_type_id;
			
				$task_rel_id=5;//Fixed Inquiry ID
			}
			$task_remark =str_ireplace(array("\r","\n",'\r','\n','<br>','\N','\R'),'', $inq_data['inq_desc']);
		}
		//$inquiry_date=date('d-m-Y');
		$user_name=$_SESSION['user_name'];
		$task_due_date=date('d-m-Y h:i A');
	}
	$url = $_SERVER['HTTP_REFERER'];
	$infopage = basename($url);
	if($infopage=='dashboard'){
		$back_link=ROOT.'dashboard';
	}
	else{
		$back_link=ROOT.CRM_ROOT.'task_list';
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));

// Amish Soni Start 19-01-2021
	$crm_auto_mail = '';
	$companySettings = getCompanySettings($dbcon);
	$max_followup_date = MAX_FOLLOWUP_DATE;
	if($companySettings) {
		$crm_auto_mail = $companySettings['crm_auto_mail'];

		if($companySettings['max_followup_date']!=0){
			$max_followup_date=(int)$companySettings['max_followup_date'];
		}
	}
	$showTemplate = ($crm_auto_mail == 'No');
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$crm_user_type=$companyConfiguration['crm_user_type'];

	$back_link = $_SERVER['HTTP_REFERER'];
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>TASK</title>
		<?php include_once('../../include/include_css_file.php');?>
		<style>

@import url('https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&subset=devanagari,latin-ext');


:root {
	--white: #ffffff;
	--light: #f0eff3;
	--black: #000000;
	--dark-blue: #1f2029;
	--dark-light: #353746;
	--red: #da2c4d;
	--yellow: #f8ab37;
	--grey: #ecedf3;
}


::selection {
	color: var(--white);
	background-color: var(--black);
}
::-moz-selection {
	color: var(--white);
	background-color: var(--black);
}
mark{
	color: var(--white);
	background-color: var(--black);
}
.section {
    position: relative;
	width: 100%;
	display: block;
/*	text-align: center;*/
	margin: 0 auto;
}
.over-hide {
    overflow: hidden;
}
.z-bigger {
    z-index: 100 !important;
}


.background-color{
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: var(--dark-blue);
	z-index: 1;
	-webkit-transition: all 300ms linear;
	transition: all 300ms linear; 
}
/* .checkbox:checked ~ .background-color{
	background-color: var(--white);
} */


/* [type="checkbox"]:checked, */
/* [type="checkbox"]:not(:checked), */
[type="radio"]:checked,
[type="radio"]:not(:checked){
	position: absolute;
	left: -9999px;
	width: 0;
	height: 0;
	visibility: hidden;
}
/* .checkbox:checked + label,
.checkbox:not(:checked) + label{
	position: relative;
	width: 70px;
	display: inline-block;
	padding: 0;
	margin: 0 auto;
	text-align: center;
	margin: 17px 0;
	margin-top: 100px;
	height: 6px;
	border-radius: 4px;
	background-image: linear-gradient(298deg, var(--red), var(--yellow));
	z-index: 100 !important;
}
.checkbox:checked + label:before,
.checkbox:not(:checked) + label:before {
	position: absolute;
	font-family: 'unicons';
	cursor: pointer;
	top: -17px;
	z-index: 2;
	font-size: 20px;
	line-height: 40px;
	text-align: center;
	width: 40px;
	height: 40px;
	border-radius: 50%;
	-webkit-transition: all 300ms linear;
	transition: all 300ms linear; 
}
.checkbox:not(:checked) + label:before {
	content: '\eac1';
	left: 0;
	color: var(--grey);
	background-color: var(--dark-light);
	box-shadow: 0 4px 4px rgba(0,0,0,0.15), 0 0 0 1px rgba(26,53,71,0.07);
}
.checkbox:checked + label:before {
	content: '\eb8f';
	left: 30px;
	color: var(--yellow);
	background-color: var(--dark-blue);
	box-shadow: 0 4px 4px rgba(26,53,71,0.25), 0 0 0 1px rgba(26,53,71,0.07);
}

.checkbox:checked ~ .section .container .row .col-12 p{
	color: var(--dark-blue);
} */


.checkbox-tools:checked + label,
.checkbox-tools:not(:checked) + label{
	position: relative;
	display: inline-block;
	padding: 20px;
	width: 80px;
	font-size: 18px;
	line-height: 20px;
	letter-spacing: 3px;
	margin: 0 auto;
	font-weight: 600;
	margin-left: 25px;
	margin-right: 5px;
	margin-bottom: 10px;
	text-align: center;
	border-radius: 4px;
	overflow: hidden;
	cursor: pointer;
	text-transform: uppercase;
	color: var(--white);
	-webkit-transition: all 300ms linear;
	transition: all 300ms linear; 
}
.checkbox-tools:not(:checked) + label{
	color: black;
    /* border: 1px solid #f7a738; */
    background-color: #ffffff;
    box-shadow: 0px 1px 4px 1px rgb(234 110 66);

}
.checkbox-tools:checked + label{
	background-color: transparent;
	box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
}
.checkbox-tools:not(:checked) + label:hover{
	background-image: linear-gradient(298deg, var(--red), var(--yellow));
	box-shadow: 0px 3px 25px 3px rgb(255 0 0 / 20%);
	color: white;

}
.checkbox-tools:checked + label::before,
.checkbox-tools:not(:checked) + label::before{
	position: absolute;
	content: '';
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	border-radius: 4px;
	background-image: linear-gradient(298deg, var(--red), var(--yellow));
	z-index: -1;
}
.checkbox-tools:checked + label .uil,
.checkbox-tools:not(:checked) + label .uil{
	font-size: 24px;
	line-height: 24px;
	display: block;
	padding-bottom: 10px;
}

.checkbox:checked ~ .section .container .row .col-12 .checkbox-tools:not(:checked) + label{
	background-color: var(--light);
	color: var(--dark-blue);
	box-shadow: 0 1x 4px 0 rgba(0, 0, 0, 0.05);
}

.checkbox-budget:checked + label,
.checkbox-budget:not(:checked) + label{
	position: relative;
	display: inline-block;
	padding: 0;
	padding-top: 20px;
	padding-bottom: 20px;
	width: 260px;
	font-size: 52px;
	line-height: 52px;
	font-weight: 700;
	letter-spacing: 1px;
	margin: 0 auto;
	margin-left: 5px;
	margin-right: 5px;
	margin-bottom: 10px;
	text-align: center;
	border-radius: 4px;
	overflow: hidden;
	cursor: pointer;
	text-transform: uppercase;
	-webkit-transition: all 300ms linear;
	transition: all 300ms linear; 
	-webkit-text-stroke: 1px var(--white);
    text-stroke: 1px var(--white);
    -webkit-text-fill-color: transparent;
    text-fill-color: transparent;
    color: transparent;
}
.checkbox-budget:not(:checked) + label{
	background-color: var(--dark-light);
	box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}
.checkbox-budget:checked + label{
	background-color: transparent;
	box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
}
.checkbox-budget:not(:checked) + label:hover{
	box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
}
.checkbox-budget:checked + label::before,
.checkbox-budget:not(:checked) + label::before{
	position: absolute;
	content: '';
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	border-radius: 4px;
	background-image: linear-gradient(138deg, var(--red), var(--yellow));
	z-index: -1;
}
.checkbox-budget:checked + label span,
.checkbox-budget:not(:checked) + label span{
	position: relative;
	display: block;
}
.checkbox-budget:checked + label span::before,
.checkbox-budget:not(:checked) + label span::before{
	position: absolute;
	content: attr(data-hover);
	top: 0;
	left: 0;
	width: 100%;
	overflow: hidden;
	-webkit-text-stroke: transparent;
    text-stroke: transparent;
    -webkit-text-fill-color: var(--white);
    text-fill-color: var(--white);
    color: var(--white);
	-webkit-transition: max-height 0.3s;
	-moz-transition: max-height 0.3s;
	transition: max-height 0.3s;
}
.checkbox-budget:not(:checked) + label span::before{
	max-height: 0;
}
.checkbox-budget:checked + label span::before{
	max-height: 100%;
}

.checkbox:checked ~ .section .container .row .col-xl-10 .checkbox-budget:not(:checked) + label{
	background-color: var(--light);
	-webkit-text-stroke: 1px var(--dark-blue);
    text-stroke: 1px var(--dark-blue);
	box-shadow: 0 1x 4px 0 rgba(0, 0, 0, 0.05);
}

.checkbox-booking:checked + label,
.checkbox-booking:not(:checked) + label{
	position: relative;
	display: -webkit-inline-flex;
	display: -ms-inline-flexbox;
	display: inline-flex;
	-webkit-align-items: center;
	-moz-align-items: center;
	-ms-align-items: center;
	align-items: center;
	-webkit-justify-content: center;
	-moz-justify-content: center;
	-ms-justify-content: center;
	justify-content: center;
	-ms-flex-pack: center;
	text-align: center;
	padding: 0;
	padding: 6px 25px;
	font-size: 14px;
	line-height: 30px;
	letter-spacing: 1px;
	margin: 0 auto;
	margin-left: 6px;
	margin-right: 6px;
	margin-bottom: 16px;
	text-align: center;
	border-radius: 4px;
	cursor: pointer;
    color: var(--white);
	text-transform: uppercase;
	background-color: var(--dark-light);
	-webkit-transition: all 300ms linear;
	transition: all 300ms linear; 
}
.checkbox-booking:not(:checked) + label::before{
	box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
}
.checkbox-booking:checked + label::before{
	box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
}
.checkbox-booking:not(:checked) + label:hover::before{
	box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.2);
}
.checkbox-booking:checked + label::before,
.checkbox-booking:not(:checked) + label::before{
	position: absolute;
	content: '';
	top: -2px;
	left: -2px;
	width: calc(100% + 4px);
	height: calc(100% + 4px);
	border-radius: 4px;
	z-index: -2;
	background-image: linear-gradient(138deg, var(--red), var(--yellow));
	-webkit-transition: all 300ms linear;
	transition: all 300ms linear; 
}
.checkbox-booking:not(:checked) + label::before{
	top: -1px;
	left: -1px;
	width: calc(100% + 2px);
	height: calc(100% + 2px);
}
.checkbox-booking:checked + label::after,
.checkbox-booking:not(:checked) + label::after{
	position: absolute;
	content: '';
	top: -2px;
	left: -2px;
	width: calc(100% + 4px);
	height: calc(100% + 4px);
	border-radius: 4px;
	z-index: -2;
	background-color: var(--dark-light);
	-webkit-transition: all 300ms linear;
	transition: all 300ms linear; 
}
.checkbox-booking:checked + label::after{
	opacity: 0;
}
.checkbox-booking:checked + label .uil,
.checkbox-booking:not(:checked) + label .uil{
	font-size: 20px;
}
.checkbox-booking:checked + label .text,
.checkbox-booking:not(:checked) + label .text{
	position: relative;
	display: inline-block;
	-webkit-transition: opacity 300ms linear;
	transition: opacity 300ms linear;
}
.checkbox-booking:checked + label .text{
	opacity: 0.6;
}
.checkbox-booking:checked + label .text::after,
.checkbox-booking:not(:checked) + label .text::after{
	position: absolute;
	content: '';
	width: 0;
	left: 0;
	top: 50%;
	margin-top: -1px;
	height: 2px;
	background-image: linear-gradient(138deg, var(--red), var(--yellow));
	z-index: 1;
	-webkit-transition: all 300ms linear;
	transition: all 300ms linear; 
}
.checkbox-booking:not(:checked) + label .text::after{
	width: 0;
}
.checkbox-booking:checked + label .text::after{
	width: 100%;
}

.checkbox:checked ~ .section .container .row .col-12 .checkbox-booking:not(:checked) + label,
.checkbox:checked ~ .section .container .row .col-12 .checkbox-booking:checked + label{
	background-color: var(--light);
    color: var(--dark-blue);
}
.checkbox:checked ~ .section .container .row .col-12 .checkbox-booking:checked + label::after,
.checkbox:checked ~ .section .container .row .col-12 .checkbox-booking:not(:checked) + label::after{
	background-color: var(--light);
}




.link-to-page {
	position: fixed;
    top: 30px;
    right: 30px;
    z-index: 20000;
    cursor: pointer;
    width: 50px;
}
.link-to-page img{
	width: 100%;
	height: auto;
	display: block;
}
		</style>
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
										<li><a href="<?=ROOT.CRM_ROOT.'task_list'?>"><?=$form?> List</a></li>
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
														<select class="select2" id="task_type_id" name="task_type_id" required>
															<option value="">Choose Task Type</option>$
															<?=get_master_category_dtl($dbcon,$task_type_id,10,$inquiry_id,1);//10:Task?>
														</select>
													</div>
												</div>	
											</div>
											<div class="col-md-12">
												<?php echo getBranchBox($dbcon, $branch_id, $selected_branch_id, false, true,'','2','6'); ?>
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
																<?=getcust($dbcon,$rel['cust_id'],'',1);?>
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
															<?= get_master_category_dtl($dbcon,$sales_stage,7,'','') ?>
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
																<textarea class="form-control reason_remark" name="lost_reason[]" id="lost_reason" style="resize:both;" placeholder="Lost Reason" rows="1"/><?= $remark?></textarea>
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
													<label class="col-md-2 control-label">Remark*</label>
													<div class="col-md-6"> 
														<textarea class="form-control" id="task_remark" name="task_remark" style="resize:both;" placeholder="Remark" rows="4" required><?=$task_remark?></textarea>
													</div>
												</div>	
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="objection_flag" class="col-md-4 control-label">Objection</label>
													<div class="col-md-8 col-xs-11">
														<input type="checkbox" id="objection_flag" name="objection_flag" value="1" <?php if($objection_flag==1){ echo "checked onclick='return false;'"; } ?>> <span class="check_span">Objection </span> 
													</div>
												</div>							 
											</div>
											<div class="clearfix"></div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label">Assign To</label>
													<div class="col-md-6"> 
														<select class="select2" id="assign_user_ids" name="assign_user_ids" placeholder="Choose Assign User" onchange="no_of_inquiry(this)">
															<?=get_assign_users($dbcon, $assign_user, " and user_type in(".$crm_user_type.")");?>
														</select>
														<div id="no_of_inquiry" style="font-size: 12px; color: #337ab7;"></div>
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
														<!--<input type="text" class="form-control default-date-picker required valid" id="task_due_date" name="task_due_date" value="<?=$rel['task_due_date']?>" placeholder="Due Date">-->
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
															<?=get_task_alert_types($dbcon,$task_alert_id);?>
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
												<?}?>
												<?phpif($getspecialConfiguration['jet_technologies_permission'] == '1') { ?> 
												<div class="clearfix"></div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-2 control-label"></label>
													<div class="col-md-6 radio_html"> 
														
														    <div class="section z-bigger">
														    <div class="section z-bigger">
														      <div class="pb-5">
														        <div class="row justify-content-center pb-5">
														          <div class="col-12 pb-5">
														            <input class="checkbox-tools" type="radio" name="task_in_out" value="1" id="task_in" <?=$task_in?>>
														            <label class="for-checkbox-tools" for="task_in">
														              
														              IN
														            </label><!--
														            --><input class="checkbox-tools" type="radio" name="task_in_out" value="0" <?=$task_out?> id="task_out">
														            <label class="for-checkbox-tools" for="task_out">
														              OUT
														            </label>
														          </div>
														         
														          
														        </div>
														      </div>  
														    </div>
														  </div>

													</div>
												</div>
											</div>
											<?}?>

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
										<input type="text" class="form-control" id="inq_attch_doc_name" name="inq_attch_doc_name" value="" placeholder="Document Name">
									</td>
									<td>
										<input type="file" class="form-control" id="inq_attch_file" name="inq_attch_file">
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
	<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>	
</div>	
</div>
</div><!--Vendor row end-->	
<input type='hidden' name='back_link' id='back_link' value='<?=$back_link?>' />
<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
<input type='hidden' name='eid' id='eid' value='<?=$task_id?>' />
<input type='hidden' name='quotation_id' id='quotation_id' value='<?=$quotation_id?>' />
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

<?php include_once('../include/preview_rel_details.php');?>
<?php include_once('../../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/task.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});

	// var task_type_id = $('#task_type_id').val();
	// if(task_type_id === '15' || task_type_id === '20'){
	// 	$('#assign_user_ids').removeAttr('multiple');
	// 	$('#assign_user_ids').select2({width: '100%'});
	// } else {
	// 	$('#assign_user_ids').attr('multiple','true');
	// 	$('#assign_user_ids').select2({width: '100%'});
	// }

	// var maxLength = 300;
	// <php if($mode=="Edit"){	?>
	// 	var str_len = "<= strlen($rel['task_remark']); ?>";
	// 	var textlen = maxLength - str_len;
	// 	$('#rchars').text(textlen);
	// <php } ?>
	// $('#task_remark').keyup(function() {
	// 	var textlen = maxLength - $(this).val().length;
	// 	$('#rchars').text(textlen);
	// });

	<?if($mode!='Add'){?>
		$('#task_rel_id').select2("readonly",true);
		$('#c_con_id').select2("readonly",true);
		$('#cust_id').select2("readonly",true);
		$('#inquiry_id').select2("readonly",true);
		<?}?>
		
		<?if($viewmode == 'Add_flp'){?>
			$('#task_rel_id').select2("readonly",true);
			$('#c_con_id').select2("readonly",true);
			$('#cust_id').select2("readonly",true);
			$('#inquiry_id').select2("readonly",true);
			<?}?>
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			
			var max_followup_date = '<?=$max_followup_date?>';
			var date = new Date();
var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //end date should not greater than 15 days

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
	if($mode=='Add')
	{?>
	,startDate: 'd'//don't allow today and past dates
	<}?>
	});
});*/
<?if($task_rel_id){?>
	$(document).ready(function() {
		get_rel_task_divs(<?=$task_rel_id?>);
	}); 
	<?}?>
</script>
</body>
</html>
