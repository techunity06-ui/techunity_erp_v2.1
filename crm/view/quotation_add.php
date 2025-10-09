<?php 

//error_reporting(E_ALL);
session_start();
include('../include/urlfile.php');
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$incPath = $path.'include/';
	//var_dump($_SESSION);
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QUOTATION_SLUG_CREATE,
	QUOTATION_SLUG_EDIT
]);

$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$form="Quotation";
$countryid='101';
$stateid='1';
$cityid='1';
$quot_type=0;
$branch_id = $_SESSION['branch_id'];
$inquiry_type = '';
$address='';
$quot_header='';
$quot_footer='';
$quot_remark = '';
$project_name = '';
$production_up_to ='';
$quotation_id="''";
$task_id='';
if(strpos($_SERVER['REQUEST_URI'], "quotation_edit")==true) {
	if(!in_array(QUOTATION_SLUG_EDIT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	 $mode="Edit";
	$viewmode="Edit";
	$quotation_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select quot.*,usr.user_name from tbl_quotation as quot
	left join users as usr on usr.user_id=quot.user_id
	where quot.quotation_id=$quotation_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."quotation_list");
	}
	$selected_branch_id = $rel['branch_id'];
	$quotation_date=date('d-m-Y',strtotime($rel['quotation_date']));
	$user_name=$rel['user_name'];
	$cust_id=$rel['cust_id'];
	$inquiry_id=$rel['inquiry_id'];
	$quot_type=$rel['quot_type'];
	$quot_subject=$rel['quot_subject'];
	$production_up_to = $rel['production_up_to'];
	$start_quotation_id=$rel['start_quotation_id'];
	$prev_quotation_id=$rel['prev_quotation_id'];
	$quot_revise_type = $rel['quot_revise_type'];
	$c_con_id=$rel['c_con_id'];
	$currency_id=$rel['currency_id'];
	$quotation_valid_date='';
	$inquiry_type = $rel['inquiry_type'];
	$quot_revise_type = $rel['quot_revise_type'];
	$address = $rel['quot_address'];
	$quot_header = $rel['quot_header'];
	$quot_footer = $rel['quot_footer'];
	$quot_remark = $rel['quot_remark'];
	$project_name = $rel['project_name'];
	$currency_rate = $rel['currency_rate'];
	$gst_type = $rel['gst_type'];
	$quotation_valid_date= '';
	if($rel['quotation_valid_date']!="1970-01-01" && $rel['quotation_valid_date']!="0000-00-00"){
		$quotation_valid_date=date('d-m-Y',strtotime($rel['quotation_valid_date']));
	}

	$inquiry_ref_date='';
	if($rel['inquiry_ref_date']!="1970-01-01" && $rel['inquiry_ref_date']!="0000-00-00"){
		$inquiry_ref_date=date('d-m-Y',strtotime($rel['inquiry_ref_date']));
	}
	
	$product_wise="";
    $powise="";
    if(strtolower($rel['delivery_type'])=="product_wise"){
        $product_wise='selected="selected"';
    }else{
        $powise='selected="selected"';
    }
}
else 
{
	if(!in_array(QUOTATION_SLUG_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	 $mode="Add";
	$viewmode="Add";
	$quotation_date=date('d-m-Y');
	//$quotation_valid_date=date('d-m-Y');



					$currentDate = date("d-m-Y");
					$quotation_valid_date = date('d-m-Y', strtotime($currentDate . ' +15 days'));  

	$inquiry_ref_date = date('d-m-Y');
	$user_name=$_SESSION['user_name'];
	$task_type_id=21;
	$task_due_date=date('d-m-Y h:i A');
	$assign_user_ids = $_SESSION['user_id'];
	$cust_id='';$inquiry_id='';$quot_subject='';$c_con_id='';$quot_type=0;
	if(strpos($_SERVER['REQUEST_URI'], "inq_to_quot")==true) {

		$inq_to_quot=true;
		 $inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
		
		$task_query = "select max(task_id) as task_id from tbl_task where task_status=0 and task_type_id=15 and inquiry_id=".$inquiry_id;
		$result_task = $dbcon->query($task_query);
		$task_row = brp_mysqli_fetch_array($result_task);
		$task_id  = $task_row['task_id'];

		$inq_qry="select inq.*,(SELECT group_concat(assign_user_ids) FROM `tbl_task` where task_status!=2 and inquiry_id=inq.inquiry_id) as assign_user_ids from tbl_inquiry as inq
		where inquiry_id=".$inquiry_id;
		$inq_rel=mysqli_fetch_assoc($dbcon->query($inq_qry));

		$inq_qry1="select * from tbl_task as task where inquiry_id=".$inquiry_id." and task_status=0";
		$inq_rel1=mysqli_fetch_assoc($dbcon->query($inq_qry1));

		$cust_id=$inq_rel['cust_id'];
		$inquiry_name=$inq_rel['inquiry_name'];
		$c_con_id=$inq_rel['c_con_id'];
		$selected_branch_id = $inq_rel['branch_id'];
		$inquiry_type = $inq_rel['inquiry_type'];
		$quot_remark = $inq_rel1['task_remark'];
		$project_name = $inq_rel['project_name'];
		$currency_id=$inq_rel['currency_id'];
		$currency_rate = $inq_rel['currency_rate'];
		$gst_type = $inq_rel['gst_type'];

		if($currency_id == $_SESSION['currency_id']){
			$rel['currency_enable']	= 0;
		}else{
			$rel['currency_enable']	= 1;
		}

		$addr_query = "select per.*,country_name,state_name,city_name from tbl_cust_address as per
		left join country_mst as country on country.countryid=per.c_add_country
		left join state_mst as state on state.stateid=per.c_add_state
		left join city_mst as city on city.cityid=per.c_add_city
		where  c_addr_defult=1 and cust_id=".$inq_rel['cust_id'];
		$addr_ex = $dbcon->query($addr_query);
		$row=mysqli_fetch_assoc($addr_ex);
		if(mysqli_num_rows($addr_ex)>0){
			$address1=nl2br($row['c_add_address']." \n ".$row['city_name']." - ".$row['c_add_zip']."  ".$row['state_name']."  ".$row['country_name']);
			$add = stripcslashes(str_replace(array("<br />"), '', $address1));
			$address=$add;
		}

			//$assign_user_ids=array_unique(explode(",",$inq_rel['assign_user_ids']));
			//unset( $assign_user_ids[array_search( $_SESSION['user_id'], $assign_user_ids )] );

			//$assign_user_ids=implode(",",$assign_user_ids);
		$assign_user_ids = $_SESSION['user_id'];
	}else if($dbcon->real_escape_string($_REQUEST['id'])){//Check Revise Mode
			$prev_quotation_id=$dbcon->real_escape_string($_REQUEST['id']);
			$viewmode="Revise";
			$revise_status=true;
			$query="select quot.*,usr.user_name from tbl_quotation as quot
			left join users as usr on usr.user_id=quot.user_id
			where quot.quotation_id=$prev_quotation_id";
			$rel=mysqli_fetch_assoc($dbcon->query($query));
			$cust_id=$rel['cust_id'];
			$inquiry_id=$rel['inquiry_id'];

			$inquiry_ref_date='';
			if($rel['inquiry_ref_date']!="1970-01-01" && $rel['inquiry_ref_date']!="0000-00-00"){
				$inquiry_ref_date=date('d-m-Y',strtotime($rel['inquiry_ref_date']));
			}
	
			$task_query = "select max(task_id) as task_id from tbl_task where task_status=0 and task_type_id=20 and inquiry_id=".$inquiry_id;
			$result_task = $dbcon->query($task_query);
			$task_row = brp_mysqli_fetch_array($result_task);
			$task_id  = $task_row['task_id'];
			$quot_type=$rel['quot_type'];
			$start_quotation_id=$rel['start_quotation_id'];
			$quot_subject=$rel['quot_subject'];
			$production_up_to = $rel['production_up_to'];
			$c_con_id=$rel['c_con_id'];
			$inquiry_type = $rel['inquiry_type'];
			$gst_type = $rel['gst_type'];
			$address = $rel['quot_address'];
			$quot_header = $rel['quot_header'];
			$quot_footer = $rel['quot_footer'];
			$quot_remark = $rel['quot_remark'];
			$project_name = $rel['project_name'];
			$currency_id=$rel['currency_id'];
			$currency_rate = $rel['currency_rate'];
			$quot_revise_type = $rel['quot_revise_type'];
			$rel['revise_status'] ='1';
			//Get Prev Quotation user for assign process
			$inq_qry="select inq.*,(SELECT group_concat(assign_user_ids) FROM `tbl_task` where task_status!=2 and inquiry_id=inq.inquiry_id) as assign_user_ids from tbl_inquiry as inq
			where inquiry_id=".$inquiry_id;
			$inq_rel=mysqli_fetch_assoc($dbcon->query($inq_qry));
			
			$inq_qry1="select * from tbl_task as task where inquiry_id=".$inquiry_id." and task_status=0";
			$inq_rel1=mysqli_fetch_assoc($dbcon->query($inq_qry1));
			
			$cust_id=$inq_rel['cust_id'];
			$c_con_id=$inq_rel['c_con_id'];
			$selected_branch_id = $inq_rel['branch_id'];
			//$assign_user_ids=array_unique(explode(",",$inq_rel['assign_user_ids']));
			//unset( $assign_user_ids[array_search( $_SESSION['user_id'], $assign_user_ids )] );
			//$assign_user_ids=implode(",",$assign_user_ids);
			//$assign_user_ids=$inq_rel1['assign_user_ids'];
			$assign_user_ids = $_SESSION['user_id'];
		}
		else{
			$cust_id=$_SESSION['def_quot_cust_id'];
			$inquiry_id=$_SESSION['def_quot_inquiry_id'];
			$quot_subject=$_SESSION['def_quot_subject'];
			$c_con_id=$_SESSION['def_c_con_id'];
		}
	}
	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));

	$currency_id = (empty($currency_id)) ? $set_head['currency_id'] : $currency_id;
// Umair Start 05-07-2021
	$companySettings = getCompanySettings($dbcon);
	$max_followup_date = MAX_FOLLOWUP_DATE;
	$project_wise_manufacturing = '';
	$project_wise_item_rate = '';
	if($companySettings) {
		$project_wise_manufacturing = $companySettings['project_wise_manufacturing'];
		$project_wise_item_rate = $companySettings['project_wise_item_rate'];
		if($companySettings['max_followup_date']!=0){
			$max_followup_date=(int)$companySettings['max_followup_date'];
		}
		$setting_id = $companySettings['id'] ? $companySettings['id'] : $setting_id;
		$crm_auto_mail = $companySettings['crm_auto_mail'] ? $companySettings['crm_auto_mail'] : $crm_auto_mail;
		$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : "";
		$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);

		 $set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=1";

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		$getspecialConfiguration=getspecialConfiguration($dbcon);
		
		

		if($getspecialConfiguration['apson_special']==1)
		{
				$general_terms_condition = 	$comp_rel['conditions'];
				$general_terms_condition = str_ireplace(array("\r","\n",'\r','\n'),'', $general_terms_condition);
						
		}
		else
		{
		$general_terms_condition = $companySettings['general_terms_condition'] ? $companySettings['general_terms_condition'] : $general_terms_condition;
		$general_terms_condition = str_ireplace(array("\r","\n",'\r','\n'),'', $general_terms_condition);
		}
		$battery_limits_and_schedule_exclusion = $companySettings['battery_limits_and_schedule_exclusion'] ? $companySettings['battery_limits_and_schedule_exclusion'] : $general_terms_condition;
		$battery_limits_and_schedule_exclusion = str_ireplace(array("\r","\n",'\r','\n'),'', $battery_limits_and_schedule_exclusion);
		$quotation_footer_content = $companySettings['quotation_footer_content'] ? $companySettings['quotation_footer_content'] : "";
		$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
	}
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$crm_pro_type=$companyConfiguration['crm_pro_type'];
	$crm_pro_search=$companyConfiguration['crm_pro_search'];
	$crm_user_type=$companyConfiguration['crm_user_type'];
	$getspecialConfiguration=getspecialConfiguration($dbcon);

	$quot_header = ($quot_header) ? $quot_header : $quotation_print_content;
	$quot_footer = ($quot_footer) ? $quot_footer : $quotation_footer_content;

	function get_tax_category_new($dbcon,$eid='')
	{
		$qry = "select * from tbl_tax_category where isdelete='0'";
		$select = $dbcon->query($qry);
		$str='';
		$str.='<option value="">--Select Tax Category--</option>';
		while($row=brp_mysqli_fetch_assoc($select))
		{
			$sel='';

			if($row['tax_cat_id']==$eid)
			{
				$sel='selected=selected';
			}

			$str.='<option value="'.$row['tax_cat_id'].'" '.$sel.'>'.$row['tax_cat_name'].'</option>';

		}
		return $str;
	}
// Umair End 05-07-2021
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>QUOTATION</title>
		<?php include_once('../../include/include_css_file.php');?>
		<style type="text/css">
			.currency_icon{
				color:green;
				font-size:12px;
				font-weight: bold;
			}
		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
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
									<h3><?=$viewmode .' '.$form?></h3>
									<!--<div class="text-center">Owner : <strong><?php //=$user_name?></strong></div>-->
								</header>	
								<div class="">
									<?php 	
									$url = $_SERVER['HTTP_REFERER'];
									$infopage = basename($url);
									if($infopage=='dashboard'){
										$back_link=ROOT.'dashboard';
									}
									else{
										$back_link=ROOT.CRM_ROOT.'quotation_list';
									}
									?>
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.CRM_ROOT.'quotation_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="quotation_add" action="javascript:;" method="post" name="quotation_add">
										<div class="row">
											<div class="clearfix"></div>
											<?php if($viewmode!="Add" && $rel['revise_status']=='1'){?>

												<div class="col-md-7">
													<div class="form-group">
														<label class="col-md-3 control-label">Revise No Type</label>
														<div class="col-md-6"> 
															<label class="col-md-6" style="font-weight:bold;">
															<input type="radio" id="quot_revise_new_type" name="quot_revise_type"  value="0" <?=($quot_revise_type=='0')?'checked':''?> onChange="load_def_quotation_no(<?=$start_quotation_id?>)"> As New Quotation</label>
															<label class="col-md-6 " style="font-weight:bold;"><input type="radio" id="quot_revise_old_type" name="quot_revise_type" value="1" <?=($quot_revise_type=='1')?'checked':''?> onChange="load_def_quotation_no(<?=$start_quotation_id?>)"> As Old Quotation</label>
														</div>
													</div>	
												</div>
											<?php }else{?>
												<input type="hidden" name="quot_revise_type" id="quot_revise_type" value="<?=$quot_revise_type?>">
 											<?php }?>
											<?php if($project_wise_manufacturing=='Yes'){ ?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Inquiry Type*</label>
														<div class="col-md-6"> 
															<?php if( $inquiry_type!='0' && $inquiry_type!=''){ ?>
																<select class="select2" onchange="dm();" disabled="">
																	<?= getInquiryType($dbcon,$inquiry_type) ?>
																</select>
																<input type="hidden" id="inquiry_type" name="inquiry_type" value="<?=$inquiry_type?>">
															<?php }else { ?>
																<select class="select2" id="inquiry_type" name="inquiry_type" onchange="dm();">
																	<?= getInquiryType($dbcon,$inquiry_type) ?>
																</select>
															<?php } ?>
														</div>
													</div>
												</div>
											<?php } else { ?>
												<input type="hidden" id="inquiry_type" name="inquiry_type" value="1">
											<?php } ?>
											<?php //if($mode !== 'Add'){ ?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Quotation No*</label>
														<div class="col-md-6">
															<input id="quotation_no" name="quotation_no" type="text" class="form-control" title="Enter Quotation No" value="<?=$rel['quotation_no']?>" placeholder="Quotation No" <?=($getspecialConfiguration['jr_fiber_glass_permission'] ==0) ? 'readonly' : '';?> >		
														</div>
													</div>
												</div>
												<?php //} ?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Quotation Date*</label>
														<div class="col-md-6"> 
															<input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$quotation_date?>" placeholder="Quotation Date">
														</div>
													</div>	
												</div>
												<?php if($companyConfiguration['branch_wise_manage']==1){?>
													<div class="col-md-6">
														<?php echo getBranchBox($dbcon, $branch_id, $selected_branch_id, false, true,'','3','6'); ?>
													</div>
												<?php} ?>
												<?php if($getspecialConfiguration['rb_auto_permission']==1){?>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Installation type *</label>
															<div class="col-md-6">
																<select class="select2" name="install_type" id="install_type">
																	<option value="no" <?= strtolower($rel['install_type'])=='no'?'selected':''?> >No</option>
																	<option value="yes" <?= strtolower($rel['install_type'])=='yes'?'selected':''?> <?php if(!isset($rel['install_type'])){ echo "selected"; } ?>>Yes</option>
																</select>
															</div>
														</div>									
													</div>
												<?php} ?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Company*</label>
														<div class="col-md-6"> 
															<select class="select2" id="cust_id" name="cust_id" onchange="load_cust_person(this.value);load_cust_inq(this.value);get_statecode(this.value);get_invoice_total_tax();get_gtotal();">
																<?=getcustomer($dbcon,$cust_id)?>
															</select>
															<strong style="display:none;color:green" id="gross">Gross balance : <span class="gross"></span></strong> <br><strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong>
														</div>
														<div class="col-md-1">
															<button type="button" id="viewcompany" onclick="preview_cust_dtls()" title="View Company" class="btn btn-primary"><i class="fa fa-eye"></i></button>
														</div>
													</div>	
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Contact Person*</label>
														<div class="col-md-6"> 
															<select class="select2" id="c_con_id" name="c_con_id">
																<?=get_cust_contactperson($dbcon,$c_con_id,$cust_id);?>
															</select>
														</div>
														<div class="col-md-1">
															<button type="button" id="addcustper" onclick="open_cust_contact()" class="btn btn-primary"><i class="fa fa-plus"></i></button>
														</div>
														<?php if($mode != 'Edit'){ ?>
														<?php } ?>
													</div>	
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Inquiry*</label>
														<div class="col-md-6"> 
															<select class="select2" id="inquiry_id" name="inquiry_id" onchange="load_inq_pro(this.value);">
																<?=get_cust_inq($dbcon,$inquiry_id,$cust_id);?>
															</select>
														</div>
													</div>	
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Subject*</label>
														<div class="col-md-6"> 
															<input type="text" class="form-control" id="quot_subject" name="quot_subject" placeholder="Subject" value="<?=$quot_subject?>">
														</div>
													</div>	
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Production Up To </label>
														<div class="col-md-6"> 
															<input type="text" class="form-control" id="production_up_to" name="production_up_to" placeholder="Production Up To" value="<?=$production_up_to?>">
														</div>
													</div>	
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Valid Till</label>
														<div class="col-md-6"> 
															<input id="quotation_valid_date" name="quotation_valid_date" type="text" class="form-control default-date-picker  valid" title="Date" value="<?=$quotation_valid_date?>" placeholder="Quotation Date">
														</div>
													</div>	
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Reference</label>
														<div class="col-md-6"> 
															<input type="text" id="quotation_ref" name="quotation_ref" class="form-control" title="Reference" value="<?=$rel['quotation_ref']?>" placeholder="Reference">
														</div>
													</div>	
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Payment terms</label>
														<div class="col-md-6"> 
															<input type="text" id="payment_terms" name="payment_terms" class="form-control" title="Payment terms" value="<?=$rel['payment_terms']?>" placeholder="Payment terms">
														</div>
													</div>	
												</div>
													<div class="col-md-6">
													<div class="form-group">	
														<label class="col-md-3 control-label">Client Id</label>
														<div class="col-md-6"> 
															<input type="text" id="client_id" name="client_id" class="form-control" title="Client Id" value="<?=$rel['client_id']?>" placeholder="Client Id">
														</div>
													</div>	
												</div>
												<?php if($getspecialConfiguration['meru_permission']==1){?>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Inquiry Ref Date</label>
															<div class="col-md-6"> 
																<input id="inquiry_ref_date" name="inquiry_ref_date" type="text" class="form-control default-date-picker  valid" title="Date" value="<?=$inquiry_ref_date?>" placeholder="Inquiry Ref Date">
															</div>
														</div>	
													</div>
												<?php }?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Quotation Type</label>
														<div class="col-md-6"> 
															<label class="col-md-6" style="font-weight:bold;"><input type="radio" id="quot_type_domestic" name="quot_type" <?php //if($mode=='Add'){?>onclick="load_typeswise_terms('');"<?php //}?> value="0" <?=($quot_type!='1')?'checked':''?> onChange="get_invoice_total_tax();get_gtotal();"> Domestic</label>
															<label class="col-md-6 " style="font-weight:bold;"><input type="radio" id="quot_type_export" name="quot_type" <?php //if($mode=='Add'){?>onclick="load_typeswise_terms('');"<?php //}?> value="1" <?=($quot_type=='1')?'checked':''?> onChange="get_invoice_total_tax();get_gtotal();"> Export</label>
														</div>
													</div>	
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Print With BOM ?</label>
														<div class="col-md-6"> 
															<input type="checkbox" class="form-control" id="with_bom_flag" name="with_bom_flag" value="1" <?=($rel['with_bom_flag']== '1'? 'checked': ''); ?> style="width: 20px;">
														</div>
													</div>	
												</div>

												<?phpif($mode=='Add'){ ?>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Task*</label>
															<div class="col-md-6">
																<select class="select2" id="task_type_id" name="task_type_id" title="Choose Task Type" required>
																	<option value="">Choose Task Type</option>
																	<?=get_master_category_dtl($dbcon,$task_type_id,10);//10:Task?>
																</select>
															</div>
														</div>	
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Assign To*</label>
															<div class="col-md-6">
																<select class="select2" id="assign_user_ids" name="assign_user_ids" title="Choose Assign User" placeholder="Choose Assign User" required onchange="no_of_inquiry(this)">
																	<?=get_assign_users($dbcon, $assign_user_ids, " and user_type in(".$crm_user_type.")");?>
																</select>
																<div id="no_of_inquiry" style="font-size: 12px; color: #337ab7;"></div>
															</div>
														</div>	
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Priority*</label>
															<div class="col-md-6">
																<select class="select2" id="task_priority_id" name="task_priority_id">
																	<?=get_task_priority($dbcon,"");?>
																</select>
															</div>
														</div>	
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Follow-Up Date*</label>
															<div class="col-md-6">
																<div data-date="<?=$task_due_date?>" class="input-group date quotattion-followup-date">
																	<input type="text" class="form-control" value="<?=$task_due_date?>" name="task_due_date" id="task_due_date" autocomplete="off">
																	<div class="input-group-btn">
																		<button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
																	</div>
																</div>
															</div>
														</div>	
													</div>	
												<?php} ?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Currency Converter *</label>
														<div class="col-md-6 col-xs-11">
															<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" onchange="currency_change();" <?php if($rel['currency_enable']==1){ echo "checked";  }  ?>>
														</div>
													</div>
												</div>

												<div class="col-md-6 currency_div">
													<div class="form-group">
														<label class="col-md-3 control-label">Currency Converter *</label>
														<div class="col-md-6 col-xs-11">
															<select class="select2" name="currency_id" id="currency_id" onChange="get_symbol();">
																<?=getcurrency($dbcon,$currency_id);?>
															</select>
														</div>
													</div>
												</div>

												<div class="col-md-6 currency_div">
													<div class="form-group">
														<label class="col-md-3 control-label">Rate*</label>
														<div class="col-md-6 col-xs-11">
															<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=$currency_rate?>" placeholder="">
														</div>
													</div>
												</div>

												<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">GST Type</label>
													<div class="col-md-6 col-xs-11">
													
														<select class="form-control" name="gst_type" id="gst_type" onchange="calculate_gst_to_all_product(this.value)">
															<option value="1" <?php if($gst_type==1){ echo "selected"; } else{ echo ""; } ?>>Item Wise Tax</option>
															<option value="2" <?php if($gst_type==2){ echo "selected"; } else{ echo ""; } ?> >Merchant</option>
															<option value="3" <?php if($gst_type==3){ echo "selected"; } else{ echo ""; } ?> >SEZ</option>
															<option value="4" <?php if($gst_type==4){ echo "selected"; } else{ echo ""; } ?> >GST 0%</option>
															<option value="5" <?php if($gst_type==5){ echo "selected"; } else{ echo ""; } ?> >GST 5%</option>
															<option value="6" <?php if($gst_type==6){ echo "selected"; } else{ echo ""; } ?> >GST 12%</option>
															<option value="7" <?php if($gst_type==7){ echo "selected"; } else{ echo ""; } ?> >GST 18%</option>
															<option value="8" <?php if($gst_type==8){ echo "selected"; } else{ echo ""; } ?> >GST 24%</option>		
														</select>
													</div>
												 </div>
											</div>

											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Delivery Type</label>
													<div class="col-md-6 col-xs-11">
														<select class="form-control" name="delivery_type" id="delivery_type" onChange="delivery_type_permission();" required title="Select Delivery Type">
	                                                        <option value="po_wise" <?=$powise?> >Quotation Wise</option>
	                                                        <option value="product_wise" <?=$product_wise?> >Product Wise</option>
	                                                    </select>
													</div>
												</div>
											</div>

											<div class="col-md-6 delivary_po_wise">
												<div class="form-group">
													<label class="col-md-3 control-label"> Delivery Date</label>
													<div class="col-md-6 col-xs-11">
														<input id="quo_delivery_date" name="quo_delivery_date" type="text" class="form-control default-date-picker" title="Date" value="<?php 
														$currentDate = date("d-m-Y");
														$newDate = date('d-m-Y', strtotime($currentDate . ' +0 days')); echo $newDate; ?>" placeholder="Quotation Delivery Date">
													</div>
												</div>
											</div>
										<?php 	if($getspecialConfiguration['jainflex_permission']!=1 ){?>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label"> Payment Terms</label>
													<div class="col-md-6 col-xs-11">
														<!--<input id="payment_tems" name="payment_tems" type="number" class="form-control" title="Payment Terms" value="<?=$rel['payment_tems']?>" placeholder="Payment Terms">-->
														<select class="form-control" name="payment_terms_id" id="payment_terms_id" >
															<?=getpaymentterms($dbcon,$rel['payment_terms_id']);?>
														</select>
													</div>
												</div>
											</div>
										<?php} else { ?>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label"> Payment Terms</label>
													<div class="col-md-6 col-xs-11">
														<input id="payment_tems_jainflex" name="payment_tems_jainflex" type="text" class="form-control" title="Payment Terms" value="<?=$rel['payment_tems']?>" placeholder="Payment Terms">
													</div>
												</div>
											</div>
									<?php 	}

											if($getspecialConfiguration['apson_special']!=1 && $getspecialConfiguration['jainflex_permission']!=1 ){?>
										<!--	<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label"> Dispatch Mode </label>
													<div class="col-md-6 col-xs-11">
														<select style="padding-right: 0px;" class="form-control" name="mode_of_dispatch" id="mode_of_dispatch" >
                                                        <?=get_trasports($dbcon,$rel['mode_of_dispatch']);?>
                                                    </select>
													</div>
												</div>
											</div>

											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label"> Destination</label>
													<div class="col-md-6 col-xs-11">
														<input id="destination" name="destination" type="text" class="form-control" title="Date" value="<?=$rel['destination']?>" placeholder="Destination">
													</div>
												</div>
											</div>-->
											<?php} ?> 
											
        
                <div class="col-md-6">
                        <div class="form-group">
                                <label class="col-md-3 control-label" >Transport </label>
                                <div class="col-md-6 col-xs-11">
                                        <select class="form-control" name="transid" id="transid" onchange="load_trans_add();">
                                                <?=gettransp($dbcon,$rel['transid']);?>
                                        </select>
                                </div>
                        </div>
                </div>
                <div class="col-md-6">
                        <div class="form-group">
                                <label class="col-md-3 control-label" >Transport Address</label>
                                <div class="col-md-6 col-xs-11">
                                        <select class="form-control" name="trans_add" id="trans_add" >
                                                <?php //=getpaymentterms($dbcon,$rel['payment_terms']);?>
                                        </select>
                                        <input type="hidden" name="trans_add_ed" id="trans_add_ed" value="<?=$rel['trans_add']?>" />
                                </div>
                        </div>
                </div>

				<!--<div class="col-md-6">
                    <div class="form-group">
                    	<label class="col-md-3 control-label" >Payment Terms</label>
                    		<div class="col-md-6 col-xs-11">
							<input id="payment_tems_apson" name="payment_tems_apson" type="text" class="form-control" title="Payment Terms" value="<?=$rel['payment_tems_apson']?>" placeholder="Payment Terms">     
                        	</div>
                        </div>
                	</div>-->
					<?php if($getspecialConfiguration['apson_special']==1 || $getspecialConfiguration['jainflex_permission']==1){ ?>
				<div class="col-md-6">
                    <div class="form-group">
                    	<label class="col-md-3 control-label" >DELIVERY TIME</label>
                    		<div class="col-md-6 col-xs-11">
							<input id="delivary_time_apson" name="delivary_time_apson" type="text" class="form-control" title="DELIEVERY TIME" value="<?=$rel['delivary_time_apson']?>" placeholder="DELIEVERY TIME">     
                        	</div>
                        </div>
                	</div>

					<?php} ?>
				
      

												
												<?php//if($getspecialConfiguration['power_drive']==1){ ?>
													<!-- <div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Orange</label>
															<div class="col-md-6 col-xs-11">
																<input id="orange" name="orange" type="text" class="form-control"  value="<?php echo $rel['orange']; ?>" placeholder="Orange">
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >MFG.</label>
															<div class="col-md-6 col-xs-11">
																<input id="mfg" name="mfg" type="text" class="form-control" title="mfg" value="<?php echo $rel['mfg']; ?>" placeholder="MFG.">
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Trading</label>
															<div class="col-md-6 col-xs-11">
																<input id="trading" name="trading" type="text" class="form-control" title="Trading" value="<?php echo $rel['trading']; ?>" placeholder="Trading">
															</div>
														</div>
													</div>


													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Reparing</label>
															<div class="col-md-6 col-xs-11">
																<input id="repairing" name="repairing" type="text" class="form-control" title="Reparing" value="<?php echo $rel['repairing']; ?>" placeholder="Reparing">
															</div>
														</div>
													</div>

													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Other</label>
															<div class="col-md-6 col-xs-11">
																<input id="other" name="other" type="text" class="form-control" title="Other" value="<?php echo $rel['other']; ?>" placeholder="Other">
															</div>
														</div>
													</div> -->
												<?php //}?>											

												<?phpif($getspecialConfiguration['oilfield_permission']==1){ ?>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Terms</label>
															<div class="col-md-6 col-xs-11">
																<input id="terms" name="terms" type="text" class="form-control" title="Date" value="<?php echo $rel['terms']; ?>" placeholder="Terms">
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Shipped via</label>
															<div class="col-md-6 col-xs-11">
																<input id="shipped_via" name="shipped_via" type="text" class="form-control" title="Date" value="<?php echo $rel['shipped_via']; ?>" placeholder="Shipped via">
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Delivery No</label>
															<div class="col-md-6 col-xs-11">
																<input id="delivery_no" name="delivery_no" type="text" class="form-control" title="Date" value="<?php echo $rel['delivery_no']; ?>" placeholder="Delivery No">
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Order No</label>
															<div class="col-md-6 col-xs-11">
																<input id="order_no" name="order_no" type="text" class="form-control" title="Date" value="<?php echo $rel['order_no']; ?>" placeholder="Order No">
															</div>
														</div>
													</div>
												<?php} ?>
												<?php if($getspecialConfiguration['jr_fiber_glass_permission'] ==1){?>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >Delivery From</label>
															<div class="col-md-6 col-xs-11">
																<input id="delivery_from" name="delivery_from" type="text" class="form-control" title="Date" value="<?php echo $rel['delivery_from']; ?>" placeholder="Delivery From">
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label" >P.O Address To</label>
															<div class="col-md-6 col-xs-11">
																<input id="po_address_to" name="po_address_to" type="text" class="form-control" title="Date" value="<?php echo $rel['po_address_to']; ?>" placeholder="P.O Address To">
															</div>
														</div>
													</div>
												<?php} ?>
												<?phpif($getspecialConfiguration['elcon_permission'] ==1 || $getspecialConfiguration['filter_concept_permission'] ==1){?>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Project Name</label>
															<div class="col-md-6">
																<input type="text" id="project_name" name="project_name" class="form-control" value="<?=$project_name?>" >
															</div>
														</div>
													</div>
												<?php} ?> 
												<?php if($getspecialConfiguration['elcon_permission'] ==1){?>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-3 control-label">Greeting</label>
															<div class="col-md-6">
																<textarea class="form-control" id="quatation_greeting" name="quatation_greeting" placeholder="Enter Quatation Greeting"><?php if($mode=='Add'){ echo $set_head['quotation_greeting'];}else{ echo $rel['quatation_greeting']; }?></textarea>
															</div>
														</div>
													</div>

												<?php} ?>
												<div class="clearfix"></div>
												<hr/>
												<!--tab start--> 
												<div class="col-md-12">
													<div class="card">
														<ul class="nav nav-tabs" id="my_tab_id" role="tablist"> 
															<li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Product Details</a></li>
															<li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Description</a></li>
															<li role="presentation" id="tab2"><a href="#product-other-desc" aria-controls="product--other-desc" role="tab" data-toggle="tab">Other</a></li>
															<li role="presentation" id="tab2"><a href="#product-specification" aria-controls="product-specification" role="tab" data-toggle="tab">Specification</a></li>
														</ul>
														<!-- Tab panes -->
														<div class="tab-content"> 
															<!-- Remaks Tab Start -->
															<div role="tabpanel" class="tab-pane active" id="product-details">
																<div class="col-md-12">
																	<div class="form-group" style="margin-top:20px;">
																		<table class="display table table-bordered table-striped" style="table-layout: fixed;">
																			<thead>
																				<tr>
																					<?phpif($companyConfiguration['category_selection_active'] ==1){ ?>
																						<th width="15%" class="text-center">Product Category</th>
																					<?php} ?>
																					<th width="20%" class="text-center">Product Name</th>
																					
																					<?phpif($getspecialConfiguration['reciclar'] ==1){ ?>
																						<th width="15%" class="text-center">Reciclar Category</th>
																					<?php }?>
																					<?php if ($getspecialConfiguration['global_eng_permission'] == 1) {?>
																							<th width="8%" class="text-center">Item No</th>
																							<th width="8%" class="text-center">Size</th>
																					<?php }?>
																					<?php 
																					if ($getspecialConfiguration['dintech_valve_permission'] == 1) {?>
																							<th width="8%" class="text-center">Size</th>
																							<th width="8%" class="text-center">item_class</th>
																					<?php }?>
																					<!--<th width="5%" class="text-center">Level</th>-->
																					<th width="10%" class="text-center">Unit</th>
																					<th width="5%" class="text-center">Quantity</th>
																					<th width="10%" class="text-center">Rate <span class="currency_icon"> </span></th>
																					<th width="10%" class="text-center">Discount <span class="currency_icon"> </span></th>
																					<th width="10%" class="text-center">Amount <span class="currency_icon"> </span></th>
																					<th width="5%" class="text-center">Action</th>					  
																				</tr>
																			</thead>
																			<tbody>
																				<input type="hidden" value="<?=$company_config['enable_negative_qty']?>" name="isstockngative" id="isstockngative"/>
																				<tr>
																				<?phpif($companyConfiguration['category_selection_active'] ==1){ ?>
																		 		<td>
			                                                                        <select class="select2" name="cat_id" id="cat_id" title="Select Category" <?php if($companyConfiguration['cat_wise_product_load'] ==1){?> onchange="product_load()"<?php }?>>
			                                                                            <?=get_all_category($dbcon,0);?>
			                                                                        </select>
		                                                                    	</td>
																				<?php} ?>
																<td style="max-width:300px">
																	<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls(this.value);get_hsn(this.value);"/>
																	<br><strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>
																	<label id="current_stock" style="display: none;"></label>
																	<strong class="product_stock_label" style="display:none;color:green"> , Current Stock : <span id="product_stock_label"></span></strong><br>
																	<button type="button" id="projectItem" onclick="load_project_item()" title="View Project Wise Item List" class="btn btn-primary" style="display: none;">View Item List <i class="fa fa-plus"></i></button>&nbsp;&nbsp;&nbsp;
																	<button type="button" id="productHistory" onclick="load_product_history()" title="View Product History" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></button>&nbsp;&nbsp;&nbsp;
																	<?phpif($getspecialConfiguration['oilfield_permission']==1){ ?>
																	<button accesskey="n" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" onclick="showproduct()"><i class="fa fa-plus"></i> Add Product</button>
																<?php} ?>
																</td>
																				<?phpif($getspecialConfiguration['reciclar'] ==1 ){ ?>
																					<td>
				                                                                        <select class="select2" name="parent_cat_id" id="parent_cat_id" title="Select Category">
				                                                                            <?=get_all_reciclare_category($dbcon,0);?>
				                                                                        </select>
		                                                                    		</td>
																				<?php }?>
																				<?php if($getspecialConfiguration['global_eng_permission']==1){?>
																					<td>
																						<input type="text" class="form-control" id="item_no" name="item_no" value="">
																					</td>
																					<td>
																					<input type="text" class="form-control" id="item_size" name="item_size" value="">
																					</td>
																				<?php }?>
																				<?php 
																					if ($getspecialConfiguration['dintech_valve_permission'] == 1) {?>
																					<td>
																						<input type="text" class="form-control" id="item_size" name="item_size" value="">
																					</td>
																					<td>
																						<input type="text" class="form-control" id="item_class" name="item_class" value="">
																					</td>
																					<?php }?>
																					<td>
																						<select class="form-control"  title="Select Unit" placeholder="Unit" name="rate_unit_id" id="rate_unit_id" onchange="load_product_unit();">
							                                                                <?php //=getunit($dbcon,0);?>
							                                                                <option value="0">Select Unit</option>
							                                                            </select>
																						<!-- <select class="select2" name="unitid" id="unitid" title="Select Unit" onchange="getrate();">
																							<?=getunit($dbcon,0);?>
																						</select> -->
																					</td>

																					<td>
																						<div id="convert_unit_block" style="display:none;" >
								                                                            <input type="text"  title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(1);"onChange="get_discount('per');" />
								                                                            <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
								                                                            <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
								                                                            <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show">  </span>
								                                                        </div>
								                                                        
								                                                        <div id="base_unit_block">
								                                                            <input type="text"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(2);" onchange="get_discount('per');calculate_special_total();" />
								                                                            <input type="hidden" name="unitid" id="unitid" value="" />
								                                                            <input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
								                                                            <span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show" >  </span>
								                                                        </div>
																					</td>
																					<td>
																						<input type="number" min="0" class="form-control" id="product_rate" name="product_rate" onkeyup="get_amount();get_discount('per');" value="">
																					</td>
																					<td>
																						<input type="number" title="Enter Discount (In value)" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" placeholder="in value"/><br>
																						<input type="number"  title="Enter Discount (In %)" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100"/>
																					</td>
																					<td>
																						<input type="number" min="0" class="form-control" id="product_amount" name="product_amount" value="" readonly>
																						<br>
																						<strong>Extra At Actual :</strong>
																						<input type="checkbox" class="form-control" id="act_amt_flag" name="act_amt_flag" value="1" style="height: 20px;width: 20px;">
																					</td>
																					<td style="vertical-align:middle;" >
																						<input type="hidden" id="edit_id" name="edit_id" value="">
																						<input type='hidden' name='pro_cal_type' id='pro_cal_type' value='' />
																						<input type="hidden" name="cust_stateid" id="cust_stateid">
																						<input type="hidden" name="enable_quotation_limit" id="enable_quotation_limit" value="<?=$companyConfiguration['enable_quotation_limit']?>">
																						<input type="hidden" name="quotation_disc_limit" id="quotation_disc_limit" value="<?=$companyConfiguration['quotation_disc_limit']?>">

																						<?php if ($getspecialConfiguration['durva_permission']==1)
																		{?>
																			<input type="button"  name="addrow1" id="addrow1" onClick="open_batch_wise_qty()"  class="btn btn-primary product_add_batch_wise" value="Add" />
																			<button type="button" class="btn btn-primary " id="quot_trn_btn" style=" display:none;" onclick="add_field()">Add</button>
																		<?php}else {?>
                                                                       			<button type="button" class="btn btn-primary delivary_po_wise" id="quot_trn_btn" onclick="add_field()">Add</button>
                                                                       			<input type="button"  name="addrow" id="addrow" onClick="open_approv_quo1();load_unit_product();delivery_schedule()"  class="btn btn-primary delivary_product_wise" value="Add" />
																		<?php} ?>
	

																						
																					</td>
																				</tr>
																			</tbody>
																			<?phpif($getspecialConfiguration['power_drive']==1){ ?>
																			<thead>
																				<tr>
																					<th class="text-center">Orange</th>	
																					<th class="text-center">MFG.</th>	
																					<th class="text-center" colspan="2">Trading</th>	
																					<th class="text-center">Reparing</th>	
																					<th class="text-center">Other</th>	
																				</tr>
																			</thead>
																			<tbody>
																				<tr>
																					<td><input id="orange" name="orange" type="text" class="form-control" placeholder="Orange" onkeyup="calculate_orange()"><br><input id="orange_total" name="orange_total" type="text" class="form-control" readonly placeholder="Orange Total"></td>
																		<td><input id="mfg" name="mfg" type="text" class="form-control" title="mfg" placeholder="MFG." onkeyup="calculate_mfg()"><br><input id="mfg_total" name="mfg_total" type="text" class="form-control" title="mfg Total" placeholder="MFG. Total" readonly></td>
																		<td colspan="2"><input id="trading" name="trading" type="text" class="form-control" title="Trading" placeholder="Trading" onkeyup="calculate_trading()"><br><input id="trading_total" name="trading_total" type="text" class="form-control" title="Trading Total" placeholder="Trading Total" readonly></td>
																		<td colspan="2"><input id="repairing" name="repairing" type="text" class="form-control" title="Reparing" placeholder="Reparing" onkeyup="calculate_repairing()"><br><input id="repairing_total" name="repairing_total" type="text" class="form-control" title="Reparing Total" placeholder="Reparing Total" readonly></td>
																		<td><input id="other" name="other" type="text" class="form-control" title="Other" placeholder="Other" onkeyup="calculate_other()"><br><input id="other_total" name="other_total" type="text" class="form-control" title="Other Total" placeholder="Other Total" readonly></td>
																				</tr>
																			</tbody>
																			<?php }?>
																		</table>
																	</div> 
																</div>
															</div>
															<div class="tab-pane" id="product-desc" >
																<div class="row">
																	<div class="col-md-6">
																		<div class="form-group">
																			<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
																			<div class="col-md-12">
																				<textarea class="form-control" id="product_desc" name="product_desc" placeholder="Enter Product Description"><?=$rel['product_desc']?></textarea>
																			</div>
																		</div> 
																	</div>
																	<div class="col-md-6">
																		<div class="form-group">
																			<label class="col-md-2 control-label text-left" style="text-align:left;font-weight:bold;">Specification</label>
																			<div class="form-group">
																				<label class="col-md-4 control-label text-left">Choose Specification</label>
																				<div class="col-md-4">
																					<select class="select2 categojj" id="specification_id" name="specification[]" onchange="load_specification_content();" multiple data-placeholder="Choose Annexure">
																						<?=get_specification_types($dbcon,$rel['product_spec_id']);?>
																					</select>
																				</div>
																			</div>
																			<div class="col-md-12">
																				<textarea class="form-control" id="product_spec" name="product_spec" placeholder="Enter Product Specification"><?=$rel['product_spec']?></textarea> 
																			</div>
																		</div> 
																	</div>
																</div>
															</div>
															<div class="tab-pane" id="product-other-desc" >
																<div class="row">
																	<div class="col-md-6">
																		<div class="form-group">
																			<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Other Description</label>
																			<div class="col-md-12">
																				<textarea class="form-control" id="product_other_desc" name="product_other_desc" placeholder="Enter Other Description"><?=$rel['product_other_desc']?></textarea>
																			</div>
																		</div> 
																	</div>
																</div>
															</div>

															<!-- JS : Add Specifications -->
															<div role="tab-pane" class="tab-pane " id="product-specification">
																<div class="col-md-12">
																	<div class="form-group">
																		<?php
																			// if ($getspecialConfiguration['main_master'] == 1) {
																			$query_field = "select * from tbl_master_field where master_field_status=0 and company_id=" . $_SESSION['company_id'] . " order by priority ASC";
																			$res_field = $dbcon->query($query_field);
																			$ro_cnt = brp_mysqli_num_rows($res_field);
																			$fieldcnt = 1;
																			$counter = 1;
																			while ($row_field = brp_mysqli_fetch_array($res_field)) {
																				$field_name = $row_field['master_field_db_name'];	
																				$field = $row_field['master_field_id'];	
																				if ($fieldcnt == 1) { ?>
																					<div class="col-md-12 margin_row">
																					<div class="row">
																					<?php} ?>
																					<div class="col-md-4">
																					<input type="hidden" name="fid" data-id="<?=$field_name;?>" class="dy_fields[<?=$field?>]" id="fid" value="<?=$field?>">
																						<div class="form-group">
																							<label class="col-md-4 control-label"><?= $row_field['master_field'] ?>*</label>
																							<div class="col-md-8 col-xs-11">
																								<select class="select2 dynamic_field" name="<?= $row_field['master_field_db_name'] ?>" id="field_id_<?= $field ?>" title="<?= $row_field['master_field'] ?>" >
																								
																									<option value="" data-pcode="">--CHOOSE <?= $row_field['master_field'] ?>--</option>
																									<?= get_master_field_value($dbcon, $rel_field[$field_name], $row_field['master_field_id']) ?>
																								</select>
																							</div>
																						</div>
																					</div>
																					<?phpif ($ro_cnt == $fieldcnt) { ?>
																					</div>
																					<?php} else {
																						if ($counter == 3) {
																							$counter = 0;
																					?>
																							</div>
																							<div class="col-md-12 margin_row">
																						<?php}
																					} ?>

																				<?php$fieldcnt++;
																				$counter++;
																			}
																		?>
																		<input type="hidden" name="dynamic_field" id="dynamic_field" value="<?= $field - 1 ?>">
																	</div>
																</div>
															</div>

														</div>

													</div>
												</div>
												<div class="col-md-12">
													<div class="form-group" id="quot_trn_div" style="margin-top:20px;overflow-x:scroll;"></div>
												</div>
												<div class="clearfix"></div>
												<div class="row">
													<div class="col-md-6 tax_details"></div>											
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-5 control-label">Total * <span class="currency_icon"> </span></label>
															<div class="col-md-5 col-xs-11">
																<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
															</div>
														</div>	
														<div class="invoiceTotalTax">

														</div>
														<div class="sundryadded">

														</div>
														<div class="form-group">
															<label class="col-md-5 control-label">Net Amount * <span class="currency_icon"> </span></label>
															<div class="col-md-5 col-xs-11">
																<input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly">
															</div>
														</div>
														<div>
															<div class="form-group">
																<label class="col-md-5 control-label">Select Bill Sundry</label>
																<div class="col-md-2">
																	<?php $get_bill_sundry = get_bill_sundry_ledger($dbcon,0); ?>
																	<select class="form-control" name="bill_sundry" id="bill_sundry" onchange="get_sundry_label(this.value)">
																		<option value="0">Select</option>
																		<?php foreach ($get_bill_sundry as $sundry) { ?>
																			<option value="<?php echo $sundry['l_id'] ?>"><?php echo $sundry['l_name']; ?></option>
																		<?php } ?>
																	</select>
																</div>
																<div class="col-md-2">
																	<input id="bill_sundry_amount" name="bill_sundry_amount" type="text" class="form-control numbersOnly" placeholder="Amount" title="Amount" value="<?=$rel['amount']?>" placeholder="" >
																</div>
																<div class="col-md-2">
																	<button style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" value="R1" onclick="addBillSundry()"><i class="fa fa-plus"></i></button>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-6"></div>
												<?php if($getspecialConfiguration['maruti_permission']==1){?>
													<div class="col-md-6">
														<div class="form-group">
															<label class="col-md-4 control-label">Inclusive all</label>
															<div class="col-md-8">  
																<input type="checkbox"  id="term_inc" name="term_inc" class="terms_checkbox" value="1" <?=($rel['term_inc']==1)? 'checked':'';?> >
															</div>
														</div>	
													</div>
													<?php }?>
													
													<hr/>
													<div class="clearfix"></div>
													<!--tab start--> 
													<div class="col-md-12">
														<div class="card">
															<ul class="nav nav-tabs" id="my_tab_id" role="tablist"> 
																<li role="presentation" id="tab2" class="active"><a href="#terms-section" aria-controls="terms-section" role="tab" data-toggle="tab">Terms And Condition</a></li>
																<li role="presentation" id="tab1"><a href="#remark-section" aria-controls="remark-section" role="tab" data-toggle="tab">Remark</a></li>
																<li role="presentation" id="tab3"><a href="#annexure-section" aria-controls="annexure-section" role="tab" data-toggle="tab">Annexure</a></li>
																<li role="presentation" id="tab5"><a href="#dfd-section" aria-controls="dfd-section" role="tab" data-toggle="tab">Annexure DFD</a></li>
																<li role="presentation" id="tab4"><a href="#address-section" aria-controls="address-section" role="tab" data-toggle="tab">Address</a></li>
																<li role="presentation" id="tab6"><a href="#greeting-section" aria-controls="greeting-section" role="tab" data-toggle="tab">Greetings</a></li>
																<li role="presentation" id="tab6"><a href="#document-section" aria-controls="document-section" role="tab" data-toggle="tab">Documents</a></li>
																<?php if($getspecialConfiguration['maruti_permission']==1 || $getspecialConfiguration['apson_special']==1){?>
																	<li role="presentation" id="tab4"><a href="#general-terms-condition-section" aria-controls="general-terms-condition-section" role="tab" data-toggle="tab">General Terms & Conditions Content</a></li>
																<?php}
																if($getspecialConfiguration['maruti_permission']==1){?>
																	<li role="presentation" id="tab4"><a href="#battery-limits-and-schedule-exclusion-section" aria-controls="battery-limits-and-schedule-exclusion-section" role="tab" data-toggle="tab">Battery Limits And Schedule Of Exclusion Content</a></li>
																	<?php }?>
																</ul>
																<!-- Tab panes -->
																<div class="tab-content"> 
																	<!-- Terms Tab Start -->
																	<div role="tabpanel" class="tab-pane active" id="terms-section">
																		<div class="col-md-2">
																			<div class="form-group">
																				<input type="radio" class="" name="terms_type" id="common_terms" value="0" onchange="load_typeswise_terms();" 

																				<?phpif($rel['terms_type'] == '0'){ echo 'checked="checked"';}else{ if($mode == 'Add'){echo 'checked="checked"';} }?> > Common Terms 
																			</div>
																		</div>

																		<div class="col-md-2">
																			<div class="form-group">
																				<input type="radio" class="" name="terms_type" id="party_terms" value="1" onchange="load_typeswise_terms();"
																				<?phpif($rel['terms_type'] == '1'){ echo 'checked="checked"';}?>
																				> Party Wise		
																			</div>
																		</div>

																		<div class="col-md-2">
																			<div class="form-group">
																				<input type="radio" class="" name="terms_type" id="multi_condition" value="2" onchange="load_typeswise_terms();"
																				<?phpif($rel['terms_type'] == '2'){ echo 'checked="checked"';}?>
																				> Multi Condition		
																			</div>
																		</div>

																		<div class="form-group" style="margin-top:20px;" id="quot_terms_cond_div">

																		</div>  
																	</div>
																	<!-- Remaks Tab Start -->
																	<div role="tabpanel" class="tab-pane" id="remark-section">
																		<div class="col-md-6">
																			<div class="form-group">
																				<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
																				<div class="col-md-12">
																					<textarea id="quot_remark" name="quot_remark" class="form-control" rows="3" style="resize:both;"><?=$quot_remark?></textarea> 
																				</div>
																			</div> 
																		</div>
																	</div>

																	<!-- Annexure Tab Start -->
																	<div role="tabpanel" class="tab-pane" id="annexure-section">
																		<div class="col-md-12">
																			<div class="form-group">
																				<label class="col-md-4 control-label text-left">Choose Annexure</label>
																				<div class="col-md-4">
																					<select class="select2 anexureeee" id="an_id" name="an_id[]" onchange="load_annex_content();" multiple data-placeholder="Choose Annexure">
																						<?=get_annexure_types($dbcon,$rel['an_id']);?>
																					</select>
																				</div>
																			</div> 
																		</div> 
																		<div class="col-md-12">
																			<div class="form-group">
																				<label class="col-md-12 control-label text-center" style="text-align:left;font-weight:bold;">Annexure Content</label>
																				<div class="col-md-12">
																					<textarea id="quot_annex_content" name="quot_annex_content" class="form-control"><?=$rel['quot_annex_content']?></textarea>
																				</div>
																			</div> 
																		</div> 
																	</div>
																	<!-- DFD Tab Start -->
																	<div role="tabpanel" class="tab-pane" id="dfd-section">
																		<div class="col-md-8 col-md-offset-2">
																			<div class="form-group" style="margin-top:20px;">
																				<table class="display table table-bordered table-striped">
																					<thead>
																						<tr>
																							<th width="50%" class="text-center">Upload Image</th>
																							<th width="10%" class="text-center">Action</th>					  
																						</tr>
																					</thead>
																					<tbody>
																						<tr>
																							<td>
																								<input type="file" class="form-control" id="dfd_attch_file" name="dfd_attch_file">
																							</td>
																							<td>
																								<button type="button" class="btn btn-primary" id="dfd_attch_btn" onclick="add_dfd_attch_field()">Add</button>
																							</td>
																						</tr>
																					</tbody>
																				</table>
																			</div> 
																			<div class="form-group" style="margin-top:20px;" id="dfd_attch_trn_div"></div> 
																		</div> 
																	</div>
																	<!-- Address Tab Start -->
																	<div role="tabpanel" class="tab-pane" id="address-section">
																		<div class="col-md-12 text-center">
																			<div class="form-group">
																				<div class="col-md-12">
																					<button type="button" class="btn btn-primary" onclick="view_cust_address()">View Address</button>
																				</div>
																			</div> 
																		</div> 

																		<div class="col-md-12">
																			<div class="form-group">
																				<div class="col-md-12">
																					<textarea id="quot_address" name="quot_address" class="form-control" placeholder="Enter Address" style="resize:both;" rows="4"><?=$address?></textarea>
																				</div>
																			</div> 
																		</div> 
																	</div>
																	<!-- Greetings Tab Start -->
																	<div role="tabpanel" class="tab-pane" id="greeting-section">
																		<div class="col-md-6">
																			<div class="form-group">
																				<label class="col-md-12 control-label text-center" style="text-align:left;font-weight:bold;">Header Greetings</label>
																				<div class="col-md-12">
																					<textarea id="quot_header" name="quot_header" class="form-control"><?=$quot_header?></textarea>
																				</div>
																			</div> 
																		</div>
																		<div class="col-md-6">
																			<div class="form-group">
																				<label class="col-md-12 control-label text-center" style="text-align:left;font-weight:bold;">Footer Greetings</label>
																				<div class="col-md-12">
																					<textarea id="quot_footer" name="quot_footer" class="form-control"><?=$quot_footer?></textarea>
																				</div>
																			</div> 
																		</div> 
																	</div>
																	<div role="tabpanel" class="tab-pane" id="document-section">
																		<div class="col-md-8 col-md-offset-2">
																			<div class="form-group" style="margin-top:20px;">
																				<table class="display table table-bordered table-striped">
																					<thead>
																						<tr>
																							<th width="50%" class="text-center">Document Name</th>
																							<th width="40%" class="text-center">Document File</th>
																							<th width="10%" class="text-center">Action</th>
																						</tr>
																					</thead>
																					<tbody>
																						<tr>
																							<td>
																								<input type="text" class="form-control" id="inq_attch_doc_name" name="inq_attch_doc_name" placeholder="Document Name">
																							</td>
																							<td>
																								<input type="file" class="form-control" id="inq_attch_file" name="inq_attch_file">
																							</td>
																							<td>
																								<button type="button" class="btn btn-primary" id="inq_attch_btn" onclick="add_inq_attch_field()">Add</button>
																							</td>
																						</tr>
																					</tbody>
																				</table>
																			</div> 
																			<div class="form-group" style="margin-top:20px;" id="inq_attch_trn_div"></div> 
																		</div> 
																	</div>
																	<?php if($getspecialConfiguration['maruti_permission']==1 || $getspecialConfiguration['apson_special']==1){?>
																	<div role="tabpanel" class="tab-pane" id="general-terms-condition-section">
																		<div class="col-md-12">
																			<div class="form-group">
																				<label class="col-md-12 control-label text-center" style="text-align:left;font-weight:bold;">General Terms & Conditions Content</label>
																				<div class="col-md-12">
			<textarea id="quot_general_terms_condition_content" name="quot_general_terms_condition_content" class="form-control"><?=($rel['quot_general_terms_condition_content'])?$rel['quot_general_terms_condition_content']:$general_terms_condition?></textarea>
																				</div>
																			</div> 
																		</div> 
																	</div>
																	<?php} if($getspecialConfiguration['maruti_permission']==1){?>

																	<div role="tabpanel" class="tab-pane" id="battery-limits-and-schedule-exclusion-section">
																		<div class="col-md-12">
																			<div class="form-group">
																				<label class="col-md-12 control-label text-center" style="text-align:left;font-weight:bold;">Battery Limits And Schedule Of Exclusion Content</label>
																				<div class="col-md-12">
																					<textarea id="quot_battery_limits_and_schedule_exclusion_content" name="quot_battery_limits_and_schedule_exclusion_content" class="form-control"><?=($rel['quot_battery_limits_and_schedule_exclusion_content'])?$rel['quot_battery_limits_and_schedule_exclusion_content']:$battery_limits_and_schedule_exclusion?></textarea>
																				</div>
																			</div> 
																		</div> 
																	</div>
																	<?php }?>
																</div>
																
																</div>      		
															</div>      		
															<!--tabs end-->	
															<div class="clearfix"></div>
															<hr/>	
														</div>
														<div class="clearfix"></div>
														<div class="col-md-12 text-center">
															<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
															<a href="javascript:;" type="button" class="btn btn-danger" onclick="check_product_validation(<?=$quotation_id?>,'<?=$back_link?>')">Cancel</a>	
														</div>	
													</div>
												</div><!--Vendor row end-->	
												<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
												<input type='hidden' name='eid' id='eid' value='<?=$quotation_id?>' />
												<input type='hidden' name='task_id' id='task_id' value='<?=$task_id?>' />
												<!-- <input type='hidden' name='currency_id' id='currency_id' value='<?=$currency_id?>' /> -->
												<input type='hidden' name='revise_status' id='revise_status' value='<?=$revise_status?>' />
												<input type='hidden' name='start_quotation_id' id='start_quotation_id' value='<?=$start_quotation_id?>' />
												<input type='hidden' name='prev_quotation_id' id='prev_quotation_id' value='<?=$prev_quotation_id?>' />
												<input type='hidden' name='old_product_id' id='old_product_id' value='' />
												<input type='hidden' name='quotation_trn_id' id='quotation_trn_id' value='' />
												<input type='hidden' name='project_inquiry_id' id='project_inquiry_id' value='<?=$inquiry_id?>' />
												<input type='hidden' name='pro_type' id='pro_type' value='<?=$crm_pro_type?>' />
												<input type='hidden' name='quotation_rate_fixed' id='quotation_rate_fixed' value='<?=$companyConfiguration['quotation_rate_fixed']?>' />
												<input type="hidden" name="print_path" id="print_path" value="<?=get_print_path($dbcon,'1');?>" />
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

					<?php include_once('../include/add_person.php');?>
					<?php include_once('../include/add_quotation_dispatch_date.php');?>
					<?php include_once('../include/preview_cust_address.php');?>
					<?php include_once('../include/preview_cust_dtls.php');?>
					<?php include_once('../../administration/include/add_product.php'); ?>
					<?php include_once('../../administration/include/add_hsn_in_popup.php'); ?>
					<?php include_once('../include/preview_product_history.php');?>
					<?php include_once('../include/add_accessories_product.php');?>
					<?php include_once('../include/add_accessories_product_list.php');?>
					<?php include_once('../include/add_project_wise_item.php');?>
					<?php include_once('../../include/footer.php');?>
					<!--footer end-->
				</section>

				<!-- js placed at the end of the document so the pages load faster -->
				<?php include_once('../../include/include_js_file.php');?>   
				<script>
					var formSubmitting = false;
					var setFormSubmitting = function() { formSubmitting = true; };
					window.onload = function() {
						window.addEventListener("beforeunload", function (e) {
							if (formSubmitting) {
								return undefined;
							}

							var confirmationMessage = 'You sure you want to leave? ';

        (e || window.event).returnValue = confirmationMessage; //Gecko + IE
        return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
    });
					};
				</script>
				<script src="<?=ROOT.CRM_ROOT?>js/app/quotation.js?<?=time()?>"></script>
				
				<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
				<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/product_mst.js?<?php echo time(); ?>"></script>
				<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/hsn_master.js?<?php echo time(); ?>"></script>
				<script>
					$(".select2").select2({
						width: '100%'
					});
					$('#cust_id').select2({
						minimumInputLength: 2,
						width: '100%'
					});
					$('.default-date-picker').datepicker({
						format: 'dd-mm-yyyy',
						autoclose: true
					});
					<?php if($inquiry_type=='2'){?>
						$('#projectItem').css('display','block');
					<?php} ?>
					<?php if($mode=='Add'){?>
						$('#task_type_id').select2('readonly',true);
					<?php} ?>
					<?php if($mode=='Edit'){?>
						$('#cust_id').select2('readonly',true);
			// $('#c_con_id').select2('readonly',true);
			$('#inquiry_id').select2('readonly',true);
			//Disable not selected Radio Button
			//$(':radio:not(:checked)').attr('disabled', true);
			load_typeswise_terms(<?=$quotation_id?>);
			get_all_bill_sundry(<?=$quotation_id?>);
		<?php} else if ($viewmode=='Revise') { ?>
			load_def_quotation_no(<?=$start_quotation_id?>);
			load_typeswise_terms(<?=$prev_quotation_id?>);
			show_dfd_attach_data();
			//load_annex_content(<?=$rel['an_id']?>);
			get_all_bill_sundry(<?=$prev_quotation_id?>);
		<?php} else {?>
			load_typeswise_terms('');
		<?php} ?>
		<?php if($prev_quotation_id){?>
			copy_prev_quot_trn(<?=$prev_quotation_id?>);
		<?php} ?>

		var max_followup_date = '<?=$max_followup_date?>';
		var date = new Date();
var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //end date should not greater than 15 days
$(".quotattion-followup-date").datetimepicker({
	format: "dd-mm-yyyy HH:ii P",
	showMeridian: true,
	autoclose: true,
	todayBtn: true,
	pickerPosition: "bottom-left",
	startDate: today,
	endDate: endDate
});
<?php if($inq_to_quot){//check inq to quot for copy inq pro?>
	var cust_id = $('#cust_id').val();
	get_statecode(cust_id);
	load_inq_pro(<?=$inquiry_id?>,'<?=$inquiry_name?>');
	$('#cust_id').select2('readonly',true);
	//$('#c_con_id').select2('readonly',true);
	$('#inquiry_id').select2('readonly',true);
	<?php }?>
	<?php if($viewmode=="Add"){?>
		load_def_quotation_no(<?=$start_quotation_id?>);

		<?php }?>
		CKEDITOR.replace( 'quot_annex_content', {
			enterMode: CKEDITOR.ENTER_BR
		});
		CKEDITOR.replace( 'product_desc', {
			enterMode: CKEDITOR.ENTER_BR
		});
		CKEDITOR.replace( 'product_spec', {
			enterMode: CKEDITOR.ENTER_BR
		});
		CKEDITOR.replace( 'product_other_desc', {
			enterMode: CKEDITOR.ENTER_BR
		});
		CKEDITOR.replace( 'quot_footer', {
			enterMode: CKEDITOR.ENTER_BR
		});
		CKEDITOR.replace( 'quot_header', {
			enterMode: CKEDITOR.ENTER_BR
		});
		CKEDITOR.replace( 'quot_remark', {
			enterMode: CKEDITOR.ENTER_BR
		});
		<?phpif($getspecialConfiguration['elcon_permission'] ==1){?>
			CKEDITOR.replace( 'quatation_greeting', {
				enterMode: CKEDITOR.ENTER_BR
			});
		<?php} ?>
		<?phpif($getspecialConfiguration['maruti_permission']==1 || $getspecialConfiguration['apson_special']==1){?>
			CKEDITOR.replace( 'quot_general_terms_condition_content', {
				enterMode: CKEDITOR.ENTER_BR
			});
			<?php} if($getspecialConfiguration['maruti_permission']==1){?>
			CKEDITOR.replace( 'quot_battery_limits_and_schedule_exclusion_content', {
				enterMode: CKEDITOR.ENTER_BR
			});
		<?php} ?>
		$(function(){
			setTimeout(function(){ $('#sidebar > ul').hide(); }, 1000);
		});
	</script>
</body>
</html>
