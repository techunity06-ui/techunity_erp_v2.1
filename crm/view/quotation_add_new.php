<?php 
error_reporting(E_ALL);
session_start();
include('../include/urlfile.php');
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
	$c_con_id=$rel['c_con_id'];
	$quotation_valid_date='';
	$inquiry_type = $rel['inquiry_type'];
	$address = $rel['quot_address'];
	if($rel['quotation_valid_date']!="1970-01-01" && $rel['quotation_valid_date']!="0000-00-00"){
		$quotation_valid_date=date('d-m-Y',strtotime($rel['quotation_valid_date']));
	}
}
else {
	if(!in_array(QUOTATION_SLUG_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";
	$viewmode="Add";
	$quotation_date=date('d-m-Y');
	$quotation_valid_date=date('d-m-Y');
	$user_name=$_SESSION['user_name'];
	$task_type_id=21;
	$task_due_date=date('d-m-Y h:i A');
	$assign_user_ids = $_SESSION['user_id'];
	$cust_id='';$inquiry_id='';$quot_subject='';$c_con_id='';$quot_type=0;
	if(strpos($_SERVER['REQUEST_URI'], "inq_to_quot")==true) {

		$inq_to_quot=true;
		$inquiry_id=$dbcon->real_escape_string($_REQUEST['id']);
		$inq_qry="select inq.*,(SELECT group_concat(assign_user_ids) FROM `tbl_task` where task_status!=2 and inquiry_id=inq.inquiry_id) as assign_user_ids from tbl_inquiry as inq
		where inquiry_id=".$inquiry_id;
		$inq_rel=mysqli_fetch_assoc($dbcon->query($inq_qry));

		$inq_qry1="select * from tbl_task as task where inquiry_id=".$inquiry_id." and task_status=0";
		$inq_rel1=mysqli_fetch_assoc($dbcon->query($inq_qry1));

		$cust_id=$inq_rel['cust_id'];
		$c_con_id=$inq_rel['c_con_id'];
		$selected_branch_id = $inq_rel['branch_id'];
		$inquiry_type = $inq_rel['inquiry_type'];

		$addr_query = "select per.*,country_name,state_name,city_name from tbl_cust_address as per
		left join country_mst as country on country.countryid=per.c_add_country
		left join state_mst as state on state.stateid=per.c_add_state
		left join city_mst as city on city.cityid=per.c_add_city
		where  c_addr_defult=1 and cust_id=".$inq_rel['cust_id'];
		$addr_ex = $dbcon->query($addr_query);
		$row=mysqli_fetch_assoc($addr_ex);
		if(mysqli_num_rows($addr_ex)>0){
			$address1=nl2br($row['c_add_location']." \n ".$row['c_add_street']." \n ".$row['city_name']." ".$row['state_name']." ".$row['country_name']." - ".$row['c_add_zip']);
			$add = stripcslashes(str_replace(array("<br />"), '', $address1));
			$address=$add;
		}

			//$assign_user_ids=array_unique(explode(",",$inq_rel['assign_user_ids']));
			//unset( $assign_user_ids[array_search( $_SESSION['user_id'], $assign_user_ids )] );

			//$assign_user_ids=implode(",",$assign_user_ids);
		$assign_user_ids = $_SESSION['user_id'];
	}
		else if($dbcon->real_escape_string($_REQUEST['id'])){//Check Revise Mode
			$prev_quotation_id=$dbcon->real_escape_string($_REQUEST['id']);
			$viewmode="Revise";
			$revise_status=true;
			$query="select quot.*,usr.user_name from tbl_quotation as quot
			left join users as usr on usr.user_id=quot.user_id
			where quot.quotation_id=$prev_quotation_id";
			$rel=mysqli_fetch_assoc($dbcon->query($query));
			$cust_id=$rel['cust_id'];
			$inquiry_id=$rel['inquiry_id'];
			$quot_type=$rel['quot_type'];
			$start_quotation_id=$rel['start_quotation_id'];
			$quot_subject=$rel['quot_subject'];
			$c_con_id=$rel['c_con_id'];
			$inquiry_type = $rel['inquiry_type'];
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
		$general_terms_condition = $companySettings['general_terms_condition'] ? $companySettings['general_terms_condition'] : $general_terms_condition;
		$general_terms_condition = str_ireplace(array("\r","\n",'\r','\n'),'', $general_terms_condition);
		$battery_limits_and_schedule_exclusion = $companySettings['battery_limits_and_schedule_exclusion'] ? $companySettings['battery_limits_and_schedule_exclusion'] : $general_terms_condition;
		$battery_limits_and_schedule_exclusion = str_ireplace(array("\r","\n",'\r','\n'),'', $battery_limits_and_schedule_exclusion);
		$quotation_footer_content = $companySettings['quotation_footer_content'] ? $companySettings['quotation_footer_content'] : "";
		$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
	}
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$crm_pro_type=$companyConfiguration['crm_pro_type'];
	$crm_pro_search=$companyConfiguration['crm_pro_search'];
	$getspecialConfiguration=getspecialConfiguration($dbcon);
// Umair End 05-07-2021
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
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
											<?php if($mode !== 'Add'){ ?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Quotation No*</label>
														<div class="col-md-6">
															<input id="quotation_no" name="quotation_no" type="text" class="form-control" title="Enter Quotation No" value="<?=$rel['quotation_no']?>" placeholder="Quotation No" readonly >		
														</div>
													</div>
												</div>
											<?php } ?>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Quotation Date*</label>
													<div class="col-md-6"> 
														<input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$quotation_date?>" placeholder="Quotation Date">
													</div>
												</div>	
											</div>
											<div class="col-md-6">
												<?php echo getBranchBox($dbcon, $branch_id, $selected_branch_id, false, true,'','3','6'); ?>
											</div>
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
												<?php }?>
												<div class="clearfix"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Customer*</label>
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
														<?php if($mode != 'Edit'){ ?>
															<div class="col-md-1">
																<button type="button" id="addcustper" onclick="open_cust_contact()" class="btn btn-primary"><i class="fa fa-plus"></i></button>
															</div>
														<?php } ?>
													</div>	
												</div>
												<div class="clearfix"></div>
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
												<div class="clearfix"></div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Quotation Type</label>
														<div class="col-md-6"> 
															<label class="col-md-5 col-sm-6" style="font-weight:bold;"><input type="radio" id="quot_type_domestic" name="quot_type" style="width: 15px;height: 15px;" <?php if($mode=='Add'){?>onclick="load_typeswise_terms(this.value,'');"<?php }?> value="0" <?=($quot_type!='1')?'checked':''?>> Domestic</label>
															<label class="col-md-6 col-sm-6 " style="font-weight:bold;"><input type="radio" id="quot_type_export" name="quot_type" style="width: 15px;height: 15px;" <?php if($mode=='Add'){?>onclick="load_typeswise_terms(this.value,'');"<?php }?> value="1" <?=($quot_type=='1')?'checked':''?>> Export</label>
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
												<div class="clearfix"></div>
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
														<label class="col-md-3 control-label">Print With BOM ?</label>
														<div class="col-md-6"> 
															<input type="checkbox" class="form-control" id="with_bom_flag" name="with_bom_flag" value="1" <?=($rel['with_bom_flag']== '1'? 'checked': ''); ?> style="height: 20px;width: 20px;">
														</div>
													</div>	
												</div>

<?php 	//Show Flp field only if add mode
if($mode=='Add'){
	?>
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

	<div class="clearfix"></div>
	<div class="col-md-4">
		<div class="form-group">
			<label class="col-md-4 control-label">Assign To*</label>
			<div class="col-md-8">
				<select class="select2" id="assign_user_ids" name="assign_user_ids[]" title="Choose Assign User" placeholder="Choose Assign User" required>
					<?php //=get_assign_users($dbcon, $rel['assign_user_ids'], " and user_id not in(".$_SESSION['user_id'].")");?>
					<?=get_assign_users($dbcon, $assign_user_ids, "");?>
				</select>
			</div>
		</div>	
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label class="col-md-4 control-label">Priority*</label>
			<div class="col-md-8">
				<select class="select2" id="task_priority_id" name="task_priority_id">
					<?=get_task_priority($dbcon,"");?>
				</select>
			</div>
		</div>	
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label class="col-md-4 control-label">Follow-Up Date*</label>
			<div class="col-md-8">
				<div data-date="<?=$task_due_date?>" class="input-group date quotattion-followup-date">
					<input type="text" class="form-control" value="<?=$task_due_date?>" name="task_due_date" id="task_due_date" autocomplete="off">
					<div class="input-group-btn">
						<button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
					</div>
				</div>
			</div>
		</div>	
	</div>	
	<?php }?>
	<?php if($getspecialConfiguration['elcon_permission'] ==1){?>
		<div class="col-md-6">
			<div class="form-group">
				<label class="col-md-3 control-label">Greeting</label>
				<div class="col-md-8">
					<textarea class="form-control" id="quatation_greeting" name="quatation_greeting" placeholder="Enter Quatation Greeting"><?php if($mode=='Add'){ echo $set_head['quotation_greeting'];}else{ echo $rel['quatation_greeting']; }?></textarea>
				</div>
			</div>
		</div>
		
		<?php }?>
		<div class="clearfix"></div>
		<hr/>
		<!--tab start--> 
		<div class="col-md-12">
			<div class="card">
				<ul class="nav nav-tabs" id="my_tab_id" role="tablist"> 
					<li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Product Details</a></li>
					<li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Description</a></li>
					<li role="presentation" id="tab2"><a href="#product-other-desc" aria-controls="product--other-desc" role="tab" data-toggle="tab">Other</a></li>
				</ul>
				<!-- Tab panes -->
				<div class="tab-content"> 
					<!-- Remaks Tab Start -->
					<div role="tabpanel" class="tab-pane active" id="product-details">
						<div class="col-md-12">
							<div class="form-group" style="margin-top:20px;overflow-x:scroll;">
								<table class="display table table-bordered table-striped">
									<thead>
										<tr>
											<th width="25%" class="text-center">Product Name</th>
											<!--<th width="5%" class="text-center">Level</th>-->
											<th width="5%" class="text-center">Quantity</th>
											<th width="8%" class="text-center">Rate</th>
											<th width="10%" class="text-center">Unit</th>
											<th width="10%" class="text-center">Discount</th>
											<th width="15%" class="text-center">Amount</th>
											<th width="2%" class="text-center">Action</th>					  
										</tr>
									</thead>
									<tbody>
										<input type="hidden" value="<?=$company_config['enable_negative_qty']?>" name="isstockngative" id="isstockngative"/>
										<tr>
											<td width="25%">
												<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls(this.value);get_hsn(this.value);"/>
												<br><strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>
												<strong class="product_stock_label" style="display:none;color:green"> , Current Stock : <span id="product_stock_label"></span></strong><br>
												<button type="button" id="projectItem" onclick="load_project_item()" title="View Project Wise Item List" class="btn btn-primary" style="display: none;">View Item List <i class="fa fa-plus"></i></button>&nbsp;&nbsp;&nbsp;
												<button type="button" id="productHistory" onclick="load_product_history()" title="View Product History" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></button>
											</td>
											<td>
												<input type="number" min="0" class="form-control" id="product_qty" name="product_qty" onkeyup="get_amount();get_discount('per');" value="">
											</td>
											<td>
												<input type="number" min="0" class="form-control" id="product_rate" name="product_rate" onkeyup="get_amount();get_discount('per');" value="">
											</td>
											<td>
												<select class="select2" name="unitid" id="unitid" title="Select Unit">
													<?=getunit($dbcon,0);?>
												</select>
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
											<td style="vertical-align:middle;">
												<input type="hidden" id="edit_id" name="edit_id" value="">
												<input type="hidden" name="cust_stateid" id="cust_stateid">
												<button type="button" class="btn btn-primary" id="quot_trn_btn" onclick="add_field()">Add</button>
											</td>
										</tr>
									</tbody>
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
									<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Specification</label>
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
					<label class="col-md-5 control-label">Total *</label>
					<div class="col-md-5 col-xs-11">
						<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
					</div>
				</div>	
				<div class="invoiceTotalTax">

				</div>
				<div class="sundryadded">

				</div>
				<div class="form-group">
					<label class="col-md-5 control-label">Net Amount *</label>
					<div class="col-md-5 col-xs-11">
						<input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly">
					</div>
				</div>
				<!-- <div>
					<div class="form-group">
						<label class="col-md-5 control-label">Select Bill Sundry</label>
						<div class="col-md-2">
							<?php //$get_bill_sundry = get_bill_sundry_ledger($dbcon,0); ?>
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
				</div> -->
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
						<li role="presentation" id="tab5"><a href="#dfd-section" aria-controls="dfd-section" role="tab" data-toggle="tab">Annex DFD</a></li>
						<li role="presentation" id="tab4"><a href="#address-section" aria-controls="address-section" role="tab" data-toggle="tab">Address</a></li>
						<li role="presentation" id="tab6"><a href="#greeting-section" aria-controls="greeting-section" role="tab" data-toggle="tab">Greetings</a></li>
						<?php if($getspecialConfiguration['maruti_permission']==1){?>
							<li role="presentation" id="tab4"><a href="#general-terms-condition-section" aria-controls="general-terms-condition-section" role="tab" data-toggle="tab">General Terms & Conditions Content</a></li>
							<li role="presentation" id="tab4"><a href="#battery-limits-and-schedule-exclusion-section" aria-controls="battery-limits-and-schedule-exclusion-section" role="tab" data-toggle="tab">Battery Limits And Schedule Of Exclusion Content</a></li>
							<?php }?>
						</ul>
						<!-- Tab panes -->
						<div class="tab-content"> 
							<!-- Terms Tab Start -->
							<div role="tabpanel" class="tab-pane active" id="terms-section">
								<div class="form-group" style="margin-top:20px;" id="quot_terms_cond_div">

								</div>  
							</div>
							<!-- Remaks Tab Start -->
							<div role="tabpanel" class="tab-pane" id="remark-section">
								<div class="col-md-6">
									<div class="form-group">
										<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
										<div class="col-md-12">
											<textarea id="quot_remark" name="quot_remark" class="form-control" rows="3" style="resize:both;"><?=$rel['quot_remark']?></textarea> 
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
											<select class="select2" id="an_id" name="an_id" onchange="load_annex_content(this.value);">
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
											<textarea id="quot_header" name="quot_header" class="form-control"><?=$rel['quot_header']?></textarea>
										</div>
									</div> 
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="col-md-12 control-label text-center" style="text-align:left;font-weight:bold;">Footer Greetings</label>
										<div class="col-md-12">
											<textarea id="quot_footer" name="quot_footer" class="form-control"><?=$rel['quot_footer']?></textarea>
										</div>
									</div> 
								</div> 
							</div>
						</div>
						<?php if($getspecialConfiguration['maruti_permission']==1){?>
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
		<input type='hidden' name='revise_status' id='revise_status' value='<?=$revise_status?>' />
		<input type='hidden' name='start_quotation_id' id='start_quotation_id' value='<?=$start_quotation_id?>' />
		<input type='hidden' name='prev_quotation_id' id='prev_quotation_id' value='<?=$prev_quotation_id?>' />
		<input type='hidden' name='old_product_id' id='old_product_id' value='' />
		<input type='hidden' name='quotation_trn_id' id='quotation_trn_id' value='' />
		<input type='hidden' name='project_inquiry_id' id='project_inquiry_id' value='<?=$inquiry_id?>' />
		<input type='hidden' name='pro_type' id='pro_type' value='<?=$crm_pro_type?>' />
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
<?php include_once('../include/preview_cust_address.php');?>
<?php include_once('../include/preview_cust_dtls.php');?>
<?php include_once('../include/preview_product_history.php');?>
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
	<?php if($mode=='Add'){?>
		$('#task_type_id').select2('readonly',true);
		<?php }?>
		<?php if($mode=='Edit'){?>
			$('#cust_id').select2('readonly',true);
			$('#c_con_id').select2('readonly',true);
			$('#inquiry_id').select2('readonly',true);
	//Disable not selected Radio Button
	$(':radio:not(:checked)').attr('disabled', true);
	load_typeswise_terms(<?=$quot_type?>,<?=$quotation_id?>);
	<?php } else if ($viewmode=='Revise') { ?>
		load_typeswise_terms(<?=$quot_type?>,<?=$prev_quotation_id?>);
	<?php} else {?>
		load_typeswise_terms(<?=$quot_type?>,'');
		<?php }?>
		<?php if($prev_quotation_id){?>
			copy_prev_quot_trn(<?=$prev_quotation_id?>);
			<?php }?>

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
	load_inq_pro(<?=$inquiry_id?>);
	$('#cust_id').select2('readonly',true);
	$('#c_con_id').select2('readonly',true);
	$('#inquiry_id').select2('readonly',true);
	<?php }?>
	<?php if($viewmode=="Add"){?>
		load_def_quotation_no();

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
		<?phpif($getspecialConfiguration['elcon_permission'] ==1){?>
			CKEDITOR.replace( 'quatation_greeting', {
				enterMode: CKEDITOR.ENTER_BR
			});
		<?php} ?>
		<?phpif($getspecialConfiguration['maruti_permission']==1){?>
			CKEDITOR.replace( 'quot_general_terms_condition_content', {
				enterMode: CKEDITOR.ENTER_BR
			});
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
