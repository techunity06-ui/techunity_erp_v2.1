<?php 
session_start();
include('../include/urlfile.php');
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$form="Party";
	//check paermission for customer add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CUSTOMER_PARTY_MASTER_SLUG_CREATE,
	CUSTOMER_PARTY_MASTER_SLUG_UPDATE
]);
$branch_id = $_SESSION['branch_id'];

if(strpos($_SERVER['REQUEST_URI'], "crm/customeraddedit")==false)
{
	if(!in_array(CUSTOMER_PARTY_MASTER_SLUG_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";
	$countryid="101";
	$user_name=$_SESSION['user_name'];
	$countryconsigneeid="101";
	$stateconsigneeid="1";
	$cityconsigneeid="1";
}
else
{
	if(!in_array(CUSTOMER_PARTY_MASTER_SLUG_UPDATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$countryid="101";
	$mode="Edit";
	$custid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select cust.*,usr.user_name from tbl_customer as cust 
	left join users as usr on usr.user_id=cust.user_id
	where cust.cust_id=$custid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	$cst_date='';$st_date='';
	if($rel['cst_date']!="1970-01-01" && $rel['cst_date']!="0000-00-00")
	{
		$cst_date=date('d-m-Y',strtotime($rel['cst_date']));
	}
	if($rel['st_date']!="1970-01-01" && $rel['st_date']!="0000-00-00")
	{
		$st_date=date('d-m-Y',strtotime($rel['st_date']));
	}
	
	$ass_array=explode(",",$rel['cust_assign_user']);
	$user_name=$rel['user_name'];
	$countryconsigneeid="101";
	$stateconsigneeid="1";
	$cityconsigneeid="1";
	$post_crm_yes_no =$rel['post_crm_yes_no'];
	
	$q_annual = $dbcon->query("select sum(forecast_amount_pr) as total from tbl_cust_forecast_pr where forecast_cust_id='$custid' and forecast_type='1' and isdelete='0'");
	$r_annual = brp_mysqli_fetch_assoc($q_annual);
}
$com_sel=$dbcon->query("select * from tbl_company where company_id='$_SESSION[company_id]'");
$r_sel=mysqli_fetch_array($com_sel);

$companyConfiguration=getCompanyConfiguration($dbcon);
$enable_assing_user=$companyConfiguration['enable_assing_user'];
$enable_post_crm = $companyConfiguration['enable_post_crm'];

$getpagePermissions=getpagePermission($dbcon);

$financial_year=get_financial_year_new($dbcon); 

$start_date = date("m",strtotime($financial_year['financial_start_date']));
$end_date = date("m",strtotime($financial_year['financial_end_date']));

$start_year= date("Y",strtotime($financial_year['financial_start_date']));
$end_year = date("Y",strtotime($financial_year['financial_end_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>CUSTOMER</title>
	<?php include_once($include.'include_css_file.php');?>
	<style type="text/css">
		
		.row_margin
		{
			margin-bottom: 10px !important;
		}
		.row_margin_top
		{
			margin-top: 10px !important;
		}

	</style>
</head>
<body>
	<section id="container" >
		<?php include_once($include.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php');?>
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
								<div class="text-right"><a onclick="openpagemodal()" class="btn btn-md btn-primary">Page Field Permission</a></div>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.CRM_ROOT.'crm_master'?>">CRM Masters</a></li>
									<li><a href="<?=ROOT.CRM_ROOT.'customer_list'?>"><?=$form?> List</a></li>
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
								<form class="form-horizontal" role="form" id="cust_add" action="javascript:;" method="post" name="cust_add">
									<div class="row">
										<div class="col-md-12">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Product Type" class="col-md-4 control-label">Party Code</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" id="cust_code" name="cust_code" placeholder="Customer Code"  value="<?php if($mode=='Edit'){ echo $rel['cust_code']; } else { echo get_customer_code($dbcon); } ?>" readonly />
														
														<input type="hidden" class="form-control" id="cust_code_series" name="cust_code_series" placeholder="Customer Code Series"  value="<?=get_customer_code_series($dbcon);?>" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
														<label for="Product Type" class="col-md-4 control-label">Branch Name</label>

														<?php //echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'','4','8'); ?>
														<div class="col-md-8 col-xs-11">
														<select class="select2" name="branch_id" id="branch_id" required>
															<option value="">--Select Branch--</option>
															<?=getBranchBox_new($dbcon,$rel['branch_id']);?>
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Product Type" class="col-md-4 control-label">Party Category</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="cust_cat" id="cust_cat" <?=($getpagePermissions['crm_partymst_cust_cat'] == '0') ? '' : 'required';?>>
															<option value="">--Select Party Category--</option>
															<?=get_customer_category($dbcon,$rel['cust_cat']);?>
														</select>
													</div>
												</div>							 
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="Product Type" class="col-md-4 control-label">Company Name*</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" id="cust_name" name="cust_name" placeholder=""  value="<?=$rel['cust_name'];?>" <?=($getpagePermissions['crm_partymst_cust_name'] == '0') ? '' : 'required';?>/>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											
											<div class="col-md-12">
												<div class="form-group">
													<label for="Product Type" class="col-md-2 control-label">Description / Notes </label>
													<div class="col-md-10 col-xs-11">
														<textarea class="form-control" name="cust_desc" id="cust_desc" maxlength="300"><?=$rel['cust_desc']?></textarea>
														<span id="rchars">300</span> Character(s) Remaining
													</div>
												</div>							 
											</div>

										</div>
										<div class="col-md-12">
											<?php if($enable_post_crm == 1) { ?>
											<div class="col-md-12">
												<div class="form-group">
													<label for="Product Type" class="col-md-2 control-label">POST CRM Yes/No</label>
													<div class="col-md-4 col-xs-11">
														<select class="select2" name="post_crm_yes_no" id="post_crm_yes_no" >
															<option value="">--Select Yes or No--</option>
															
															<option value="0" <?php if($post_crm_yes_no == "0"){?> selected="selected" <?php }?> >Yes</option>
															<option value="1" <?php if($post_crm_yes_no == "1"){?> selected="selected" <?php }?>>No</option>
														</select>
													</div>
												</div>							 
											</div>
											<?php } ?>
										</div>


										<?php if($enable_assing_user==1){ ?>
										<div class="col-md-12" style="margin-top:5px;">
											<header class="panel-heading breadcrumb text-center">
												<h3>Owner User</h3>
											</header>
											<div class="col-md-12">
												<div class="col-md-12">
													<div class="form-group">
														<label for="Product Type" class="col-md-2 control-label">Owner User </label>
														<div class="col-md-10 col-xs-11">
															<select class="select2" name="cust_owner" id="cust_owner">
																<option value="">--Owner User--</option>
																<?php 
																// if($mode=='Edit')
																// {
																// 	$qry="select * from users where active=0 and user_id!='$custid' AND company_id = '".$_SESSION['company_id']."'";
																// 	$user_report_arr=explode(",",$rel['user_report']);
																// }
																// else
																// {
																// }
																$qry="select * from users where active=0 AND company_id = '".$_SESSION['company_id']."'";
																$rs_state=$dbcon->query($qry);
																while($row=mysqli_fetch_array($rs_state))
																{ ?>
																	<option value="<?php echo $row['user_id']; ?>" <?php if($row['user_id']==$rel['cust_owner']){ echo "selected"; } ?> ><?php echo $row['user_name']; ?></option>
																<?php } ?>
															</select>
														</div>
													</div>							 
												</div>
											</div>
										</div>
									<?php } ?>
						<!--
						<div class="col-md-12" style="margin-top:5px;"> 
							
							<header class="panel-heading breadcrumb text-center">
							   <h3>Party Existing Details</h3>
							</header>
							
							<table class="table table-bordered">
								<thead>
									<tr>
										<th>Type</th>
										<th>Products</th>
										<th>Remark</th>
										
										<th></th>
									</tr>
									
									<tr>
										<td><input type="text" class="form-control" name="ext_type" id="ext_type" /></td>
										<td>
											<select class="select2" name="ext_product" id="ext_product">
												<?php //=getproduct($dbcon,0,'')?>
											</select>
										</td>
										<td><input type="text" class="form-control" name="ext_remark" id="ext_remark" /></td>
										
										<td><input type="button" class="btn btn-success" value="Add" onclick="add_exist()" id="add_exist_btn" />
											<input type="hidden" class="form-control" name="edit_exist_id" id="edit_exist_id" />
										</td>
									</tr>
								</thead>
								
								<tbody id="cust_exist_details">
									
								</tbody>
							</table>
							
						</div>
					-->
					
				</div><!--Vendor row end-->	

				<div class="row">
					
					<div class="col-md-12">
						
						<ul class="nav nav-tabs">
						  <li class="active"><a data-toggle="tab" href="#overview">Overview</a></li>
						  <?php if($enable_post_crm == 1) { ?>
							  <li><a data-toggle="tab" href="#dispatch">Dispatch Details</a></li>
							  <li><a data-toggle="tab" href="#competitor">Competitor Details</a></li>
							  <li><a data-toggle="tab" href="#price_list">Price List</a></li>
							  <li><a data-toggle="tab" href="#forecast">Forecast</a></li>
							  <li><a data-toggle="tab" href="#account_terms">Account Terms</a></li>
							  <!-- <li><a href="#">Custom</a></li> -->
						  <?php } ?>
						  <li><a data-toggle="tab" href="#domestic">Domestic</a></li>
						  <li><a data-toggle="tab" href="#export">Export</a></li>
						</ul>

						<div class="tab-content">

						  <div id="overview" class="tab-pane fade in active">
						  	
						  		<div class="row">
						  			
						  			<div class="col-md-12">
						  				
						  				<div class="col-md-12" style="margin-top:5px;">
											<header class="panel-heading breadcrumb text-center">
												<h3>Overview</h3>
											</header>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label for="Product Type" class="col-md-4 control-label">Party Industry</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="cust_ind" id="cust_ind" <?=($getpagePermissions['crm_partymst_cust_ind'] == '0') ? '' : 'required';?>>
																<option value="">--Select Party Industry--</option>
																<?=get_customer_industries($dbcon,$rel['cust_ind']);?>
															</select>
														</div>
													</div>							 
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="Product Type" class="col-md-4 control-label">Customer Type</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="cust_type" id="cust_type" <?=($getpagePermissions['crm_partymst_cust_type'] == '0') ? '' : 'required';?>>
																<option value="">--Select Customer Type--</option>
																<?=get_customer_master_type($dbcon,$rel['cust_type']);?>
															</select>
														</div>
													</div>							 
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label for="Product Type" class="col-md-4 control-label">Source / Refer By</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="cust_source" id="cust_source" <?=($getpagePermissions['crm_partymst_cust_source'] == '0') ? '' : 'required';?>>
																<?=get_refer_by($dbcon,$rel['cust_source']);?>
															</select>
														</div>
													</div>							 
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Territory</label> 
														<div class="col-md-8">
															<select class="select2" id="t_id" name="t_id" <?=($getpagePermissions['crm_partymst_t_id'] == '0') ? '' : 'required';?>>
																<?=get_all_territory($dbcon,$rel['t_id']);?>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label for="Product Type" class="col-md-4 control-label">Gst No </label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" name="cust_gst" id="cust_gst" value="<?=$rel['cust_gst']?>" <?=($getpagePermissions['crm_partymst_cust_gst'] == '0') ? '' : 'required';?>/>
														</div>
													</div>							 
												</div>
												<div class="col-md-6">
													<div class="form-group">
														<label for="Product Type" class="col-md-4 control-label">IEC No </label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" name="cust_iec" id="cust_iec" value="<?=$rel['cust_iec']?>" <?=($getpagePermissions['crm_partymst_cust_iec'] == '0') ? '' : 'required';?>/>
														</div>
													</div>							 
												</div>
												
											</div>
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
														<label for="Product Type" class="col-md-4 control-label">Mobile *</label>
														<div class="col-md-8 col-xs-11">
															<input type="number" class="form-control" name="cust_mobile" id="cust_mobile" onkeyup="check_mobile_no(this.value)" onchange="check_mobile_no(this.value)" value="<?=$rel['cust_mobile']?>" <?=($getpagePermissions['crm_partymst_cust_mobile'] == '0') ? '' : 'required';?>  />
														</div>
													</div>							 
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label for="Product Type" class="col-md-4 control-label">E-mail</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" name="cust_email" id="cust_email" value="<?=$rel['cust_email']?>" <?=($getpagePermissions['crm_partymst_cust_email'] == '0') ? '' : 'required';?>/>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="col-md-6">
												<div class="form-group">
													<label for="Product Type" class="col-md-4 control-label">Pan No </label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" name="cust_pan" id="cust_pan" value="<?=$rel['cust_pan']?>" <?=($getpagePermissions['crm_partymst_cust_pan'] == '0') ? '' : 'required';?>  />
													</div>
												</div>							 
											</div>
										</div>


										<div class="col-md-12" style="margin-top:5px;" id="personal_details">
											<header class="panel-heading breadcrumb text-center">
												<h3>Personal Details</h3>
											</header>
											<table class="table table-bordered">
												<thead>
													<tr>
														<th>Relation</th>
														<th>Gender</th>
														<th>Birthday</th>
														<th>Anniversary</th>
														<th></th>
													</tr>
													<tr>
														<td><input type="text" class="form-control" id="relation" name="relation" placeholder="Relation" value="<?=($rel['relation']) ? $rel['relation'] : ''?>" /></td>
														<td>

															<select class="select2" id="gender" name="gender">
																
																<option value="">Please Select</option>
																<option value="0">Male</option>
																<option value="1">Female</option>
																<option value="2">Other</option>
															</select>	
														</td>
														<td><input type="text" class="form-control datepickerPrev" id="birth_date" name="birth_date" placeholder="Birth Date" value="<?=($rel['birth_date'] && $rel['birth_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['birth_date'])) : ''?>" autocomplete="off" /></td>
														<td><input type="text" class="form-control datepickerPrev" id="anniversary_date" name="anniversary_date" placeholder="Anniversary Date" value="<?=($rel['anniversary_date'] && $rel['anniversary_date'] != '0000-00-00') ? date('d-m-Y', strtotime($rel['anniversary_date'])) : ''?>" autocomplete="off" /></td>
														<td>
															<input type="button" class="btn btn-success" value="Add" onclick="add_cust_relation()" id="add_relation_btn" />
															<input type="hidden" class="form-control" name="edit_relation_id" id="edit_relation_id" />
														</td>
													</tr>
												</thead>
												<tbody id="cust_relation_details"></tbody>
											</table>
										</div>

										<div class="col-md-12" style="margin-top:5px;">
											<header class="panel-heading breadcrumb text-center">
												<h3>Address Details</h3>
											</header>
											<table class="table table-bordered">
												<thead>
													<tr>
														<th width="35%">Address</th>
														<th>Pincode</th>
														<th>Country</th>
														<th>State</th>
														<th>City</th>
														<!-- <th>Street</th>
														<th>Zip / Postal Code</th> -->
														<th>Default</th>
														<th></th>
													</tr>
													<tr>
														<td><textarea class="form-control" name="c_add_address" id="c_add_address"></textarea></td>
														<td><input type="number" class="form-control" name="c_pincode" id="c_pincode"></td>
														<td>
															<select class="select2" name="c_add_country" id="c_add_country" onChange="load_state(this.value,'c_add_state','')">
																<?=get_country($dbcon,$countryid)?>				
															</select>
														</td>
														<td>
															<select class="select2" name="c_add_state" id="c_add_state" onChange="load_city(this.value,'c_add_city','')">
																<option value="">Select State</option>	
																<?php //=getstate($dbcon,$rel['stateid'])?>				
															</select>
														</td>
														<td>
															<select class="select2" name="c_add_city" id="c_add_city">
																<option value="">Select City</option>	
															</select>
														</td>
														<!-- <td><input type="text" class="form-control" name="c_add_street" id="c_add_street" /></td>
														<td><input type="text" class="form-control" name="c_add_zip" id="c_add_zip" /></td> -->
														<td>
															<select class="select2" name="c_addr_default" id="c_addr_default">
																<option value="1">YES</option>
																<option value="0">NO</option>
															</select>
														</td>
														<td><input type="button" class="btn btn-success" value="Add" onclick="add_cust_address()" id="add_ad_btn" />
															<input type="hidden" class="form-control" name="edit_add_id" id="edit_add_id" />
														</td>
													</tr>
												</thead>
												<tbody id="cust_address_details"></tbody>
											</table>
										</div>

										<div class="col-md-12" style="margin-top:5px;">
											<header class="panel-heading breadcrumb text-center">
												<h3>Contact Details</h3>
											</header>
											<table class="table table-bordered" style="table-layout: fixed;">
												<thead>
													<tr>
														<th>First Name</th>
														<th>Last Name</th>
														<th>Email</th>
														<th>ISD No</th>
														<th>Mobile</th>
														<th>Phone</th>
														<th>Job Title</th>
														<th></th>
													</tr>
													<tr>
														<td><input type="text" class="form-control" name="con_first" id="con_first" /></td>
														<td><input type="text" class="form-control" name="con_last" id="con_last" /></td>
														<td><input type="email" class="form-control" name="com_email" id="com_email" /></td>
														<td>
															<select class="select2" name="con_isd_id" id="con_isd_id">
																<?=get_isd_no($dbcon,$isd_id)?>				
															</select>
														</td>
														<td><input type="text" class="form-control" name="con_mobile" id="con_mobile" onkeypress="return isNumberKey(event)"  /></td>
														<td><input type="text" class="form-control" name="con_phone" id="con_phone" onkeypress="return isNumberKey(event)"  /></td>
														<td><input type="text" class="form-control" name="con_job" id="con_job" /></td>
														<td><input type="button" class="btn btn-success" value="Add" onclick="add_cust_contact()" id="add_btn_contact" />
															<input type="hidden" class="form-control" name="edit_con_id" id="edit_con_id" />
														</td>
													</tr>
												</thead>
												<tbody id="cust_contact_details"></tbody>
											</table>
										</div>

										<div class="col-md-12" style="margin-top:5px;">
											<header class="panel-heading breadcrumb text-center">
												<h3>Consignee Details</h3>
											</header>
											<table class="table table-bordered">
												<thead>
													<tr>
														<th width="10%">Company Name</th>
														<th width="10%">Person Name</th>
														<th width="8%">Mobile</th>
														<th width="10%">Email</th>
														<th width="10%">Address</th>
														<th width="12%">Country</th>
														<th width="12%">State</th>
														<th width="12%">City</th>
														<th width="8%">GST No</th>
														<td width="8%"></td>
													</tr>
													<tr>
														<td>
															<input type="text" class="form-control" name="consignee_comp_name" id="consignee_comp_name" />
														</td>
														<td>
															<input type="text" class="form-control" name="consignee_name" id="consignee_name" />
														</td>
														<td>
															<input type="text" class="form-control" name="consignee_mobile" id="consignee_mobile" onkeypress="return isNumberKey(event)" />
														</td>
														<td>
															<input type="email" class="form-control" name="consignee_email" id="consignee_email"/>
														</td>
														<td>
															<textarea class="form-control" name="consignee_address" id="consignee_address"></textarea>
														</td>
														<td>
															<select class="select2" name="country_consinee_id" id="country_consinee_id" onChange="load_consinee_state(this.value,'state_consinee_id','')">
																<?=get_country($dbcon,$countryid)?>				
															</select>
														</td>
														<td>
															<select class="select2" name="state_consinee_id" id="state_consinee_id" onChange="load_consinee_city(this.value,'city_consinee_id','')">
																<option value="">Select State</option>
															</select>
														</td>
														<td>
															<select class="select2" name="city_consinee_id" id="city_consinee_id">
																<option value="">Select City</option>
															</select>
														</td>
														<td>
															<input type="text" class="form-control" name="gst_consinee_no" id="gst_consinee_no"/>
														</td>
														<td><input type="button" class="btn btn-success" value="ADD" style="box-shadow: 3px 3px #61a642;" onclick="add_consignee()" id="add_consignee_btn" /></td>
														<input type="hidden" id="edit_id_consignee" value=""  />
													</tr>
												</thead>
												<tbody id="table_consignee_details"></tbody>
											</table>
										</div>

										<div class="col-md-12" style="margin-top:5px;">
											<header class="panel-heading breadcrumb text-center">
												<h3>Upload Document</h3>
											</header>
											<table class="display table table-bordered table-striped">
												<thead>
													<tr>
														<th width="60%">Document Name</th>
														<th width="30%">Document File</th>
														<th width="10%">Action</th>
													</tr>
													<tr>
														<td><input type="text" class="form-control" id="led_doc_name" name="led_doc_name" placeholder="Document Name"></td>
														<td><input type="file" class="form-control" id="led_attch_file" name="led_attch_file"></td>
														<td><button type="button" class="btn btn-primary" id="led_attch_btn" onclick="add_ledger_doc_field()">Add</button></td>
													</tr>
												</thead>
												<tbody id="led_attach_div">
													
												</tbody>
												
											</table>
										</div>

						  			</div>

						  		</div>  	


						  </div>

						  <div id="dispatch" class="tab-pane fade">
						  		
				  			<div class="col-md-12" style="margin-top:5px;" id="personal_details">
									<header class="panel-heading breadcrumb text-center">
										<h3>Dispatch Details</h3>
									</header>
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>Transporter Name</th>
												<th>Address</th>
												<th>Contact No</th>
												<th>Type</th>
												<th></th>
											</tr>
											<tr>
												<td><input type="text" class="form-control" id="transporter_name" name="transporter_name" placeholder="Transporter Name" value="<?=($rel['transporter_name']) ? $rel['transporter_name'] : ''?>" /></td>
												<td>
													<textarea class="form-control" name="transporter_add" id="transporter_add"></textarea>
												</td>
												<td>
													<input type="text" class="form-control" name="transporter_contact" id="transporter_contact" onkeypress="return isNumberKey(event)" maxlength="10" >
												</td>
												
												<td>
													<select class="form-control" name="transporter_type" id="transporter_type">
														<option value="">--Select Type--</option>
														<option value="1">Road</option>
														<option value="2">Rail</option>
														<option value="3">Air</option>
														<option value="4">Bus</option>
													</select>
												</td>
												<td>

													<input type="button" class="btn btn-success" value="Add" onclick="add_cust_dispatch()" id="add_dispatch_btn" />
													<input type="hidden" class="form-control" name="edit_dispatch_id" id="edit_dispatch_id" value="0" />
												</td>
											</tr>
										</thead>
										<tbody id="dispatch_details"></tbody>
									</table>
								</div>

						  </div>
						  
						  <div id="competitor" class="tab-pane fade">
						    	
						  		<section class="panel">
						  			
						  			<div class="row" style="margin-top:5px;">
						  				
						  				<div class="col-md-4">
						  					
						  					<table class="table table-bordered table-hover">
						  						
						  						<tr>
						  							<th colspan="2" style="text-align: center;">
						  								
						  								<header class="panel-heading breadcrumb text-center">
															<h3>Competitor Details</h3>
														</header>

						  							</th>
						  						</tr>

						  						<tr>
						  							<th>Name</th>
						  							<td>
						  								<input type="text" class="form-control" name="comp_name" id="comp_name">
						  							</td>
						  						</tr>

						  						<tr>
						  							<th>Address</th>
						  							<td>
						  								<textarea class="form-control" name="comp_add" id="comp_add"></textarea>
						  							</td>
						  						</tr>

						  						<tr>
						  							<th>E-mail</th>
						  							<td>
						  								<input type="text" class="form-control" name="comp_email" id="comp_email">
						  							</td>
						  						</tr>

						  						<tr>
						  							<th>Mobile</th>
						  							<td>
						  								<input type="text" class="form-control" name="comp_mobile" id="comp_mobile" onkeypress="return isNumberKey(event)" maxlength="10" >
						  							</td>
						  						</tr>

						  						<tr>
						  							
						  							<td colspan="2" align="center">
						  								
						  								<input type="hidden" class="form-control" name="edit_comp_id" id="edit_comp_id" value="0" />

						  								<input type="button" class="btn btn-primary" onclick="add_competitor()" id="add_comp_btn" value="Add">
						  							</td>

						  						</tr>

						  					</table>

						  				</div>

						  				<div class="col-md-8" id="comp_data_details">
						  					
						  					

						  				</div>

						  			</div>



						  		</section>

						  </div>

						 
						   <div id="price_list" class="tab-pane fade">
						   		
						   		<table class="table table-bordered table-hover table-striped"> 
						   			<tr>
						   				<th>#</th>
						   				<th>Month Name</th>
						   				<th>Price List Version</th>
						   			</tr>
						   		<?php 

						   			for($i=0;$i<12;$i++)
						   			{
						   				
						   				
					   					$month = $start_date+$i;
					   					if($month > 12)
					   					{
					   						$month = $month - 12;
					   						$start_year_new = $start_year+1;
					   					}
					   					else
					   					{
					   						$start_year_new = $start_year;
					   					}
					   					$monthName = date("F", mktime(0, 0, 0, $month, 10));

					   					if(isset($custid)&&$custid>0){

					   						$sel_pl = $dbcon->query("select * from tbl_cust_price_list where customer_id='$custid' and cust_price_month='$month' and cust_price_year='$start_year_new'");
					   						$r_pl = brp_mysqli_fetch_assoc($sel_pl);

					   						$selected_pl = $r_pl['cust_price_version_id'];
					   					}
					   					else{
					   						$selected_pl="";
					   					}

					   				?>	
					   				<tr>
					   					<th><?=$i+1;?></th>
					   					<td><?=$monthName."-".$start_year_new;?></td>
					   					<td>
					   						<input type='hidden' class='form-control' name='price_month_name<?=$month ?>' id='price_month_name<?=$i ?>' value='<?=$month?>' />

					   						<input type='hidden' class='form-control' name='price_year_name<?=$start_year_new ?>' id='price_year_name<?=$start_year_new ?>' value='<?=$start_year_new ?>' />

					   						<select class="form-control" name="price_list_version<?=$month?>" id="price_list_version<?=$month?>">
					   							<option value="">--Select Version--</option>
					   							<?php 
					   								echo get_price_list($dbcon,$selected_pl);
					   							?>
					   						</select>
					   					</td>
					   				</tr>
						   		<?php		
						   			}

						   		?>

						   		</table>
						   </div>

						   <div id="forecast" class="tab-pane fade row_margin_top">
						   		
						   		<div class="row">
						   			<div class="col-md-12">


						   				<section class="panel" style="padding:20px">
											<div class="row">		

												<div class="col-xs-2"> <!-- required for floating -->
													<!-- Nav tabs -->
													<ul class="nav nav-tabs tabs-left">
														<li><a href="#month_wise_forecast" data-toggle="tab" id="ltunit">Month Wise</a></li>
														<li  class="active"><a href="#product_wise_forecast" data-toggle="tab" id="ltbopen">Product Wise</a></li>
													</ul>
												</div>

												<div class="col-xs-10">
													<!-- Tab panes -->
													<div class="tab-content">

														<div class="tab-pane" id="month_wise_forecast">
															
															<div class="row">
																<div class="col-md-12">
																	<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Forecast By Month</a></h3>
																</div>
															</div>
															
															<div class="row row_margin_top">

																
																<div class="col-md-12">
														   			<div class="form-group">
														   				<div class="col-md-3" style="text-align:right !important">
																			<strong  style="text-align:right !important">Annual Forecast</strong>
																		</div>
																		<div class="col-md-8 col-xs-11">
																			<input type="text" class="form-control" name="annual_consume" id="annual_consume" value="<?=$r_annual['total'];?>" onkeyup="changeMonthlyBudget()" >
																		</div>
																	</div>
														   		</div>
																	
																<input type="hidden" class="form-control" name="edit_id_fpr_month" id="edit_id_fpr_month" value="" />
																
															</div>

																<div class="row">

																   		<?php

																   			

																   			$str="";
																   			for($i=0;$i<12;$i++)
																   			{

																   				
															   					$month = $start_date+$i;
															   					if($month > 12)
															   					{
															   						$month = $month - 12;
															   						$start_year_new = $start_year+1;
															   					}
															   					else
															   					{
															   						$start_year_new = $start_year;
															   					}
															   					$monthName = date("F", mktime(0, 0, 0, $month, 10));

															   					if($custid!='')
															   					{
															   						$qm = $dbcon->query("select * from tbl_cust_forecast_pr where forecast_month='$month' and forecast_year='$start_year_new' and forecast_cust_id='$custid' and isdelete='0'");
															   						$rm = brp_mysqli_fetch_assoc($qm); 

															   						$month_value = $rm['forecast_amount_pr'];
															   					}

															   					echo "<div class='col-md-3' style='font-weight:bold'>

															   						".$monthName." -".$start_year_new." <input type='text' class='form-control monthlyDivide' name='month_value".$month."' id='month_value".$month."' value='".$month_value."' onkeyup='changeAnnualBudget()' />

															   						<input type='hidden' class='form-control' name='year_name".$start_year_new."' id='year_name".$start_year_new."' value='".$start_year_new."' />

															   						<input type='hidden' class='form-control' name='month_name".$month."' id='month_name".$month."' value='".$month."' />

															   					</div>";
																   				
																   			}

																   		?>
																   		</div>
						   
															
														</div>
														
														<div class="tab-pane active" id="product_wise_forecast" >
															
															<div class="row">
																<div class="col-md-12">
																	<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Forecast By Product</a></h3>
																</div>
															</div>
															
															<div class="row row_margin_top">

																<div class="col-md-4 margin_row">

																	<table class="table table-bordered">
																		
																		<tr>
																			<th>Select Product
																				<select class="select2_product" name="forecast_pr_product_id" id="forecast_pr_product_id" onchange="get_price_form_price_list()" tabindex="1">
																					<option value="">--Select Product--</option>
																					 <?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
																				</select>
																			</th>
																		</tr>
																		<tr>
																			<th>Price List Version
																				<select class="form-control" name="price_list_version_pr_id" id="price_list_version_pr_id" onchange="get_price_form_price_list(this.value)"  tabindex="2">
																					<option value="">--Select Version--</option>
														   							<?php 
														   								echo get_price_list($dbcon);
														   							?>
																				</select>
																			</th>
																		</tr>
																		<tr>
																			<th>Price
																				<input type="text" class="form-control" name="forecast_amount_pr" id="forecast_amount_pr"  tabindex="3" readonly>
																			</th>
																		</tr>

																		<tr>
																			<th>Qty
																				<input type="text" class="form-control" name="forecast_pro_qty" id="forecast_pro_qty"  tabindex="4" onkeyup="get_forecast_pr_amount(this.value)">
																			</th>
																		</tr>
																		
																		<tr>
																			<th>Amount
																				<input type="text" class="form-control" name="forecast_pro_total" id="forecast_pro_total"  tabindex="5" readonly>
																			</th>
																		</tr>

																		<tr>
																			
																			<td>
																				<a class="btn btn-primary" onclick="add_forecast_pr()"  tabindex="4" id="add_forecast_pr_btn">ADD</a>

																				<input type="hidden" class="form-control" name="edit_id_fpr" id="edit_id_fpr" value="" />
																			</td>
																		</tr>
																		
																	</table>

																</div>
																
																<div class="col-md-8" id="table_forecast_details"></div>
																
															</div>
															
														</div>
														
														<div class="clearfix"></div>

													</div>

												</div>

												
											</div>	
										</section>

						
						   			</div>
						   		</div>

						   </div>


						   <div id="account_terms" class="tab-pane fade row_margin_top">
						   	
						   		<div class="row">
						   			<div class="form-group">
						   				<div class="col-md-3" style="text-align:right !important">
											<strong  style="text-align:right !important">Terms & Condition</strong>
										</div>
										<div class="col-md-8 col-xs-11">
											<textarea id="account_terms" name="account_terms" class="form-control"><?=isset($custid)?$rel['account_terms']:''?></textarea>
										</div>
									</div>

									<div class="form-group">
						   				<div class="col-md-3" style="text-align:right !important">
											<strong  style="text-align:right !important">Credit Limit</strong>
										</div>
										<div class="col-md-8 col-xs-11">
											<input type="text" class="form-control" name="account_credit_limit" id="account_credit_limit" value="<?=isset($custid)?$rel['account_credit_limit']:''?>">
										</div>
									</div>

									<div class="form-group">
						   				<div class="col-md-3" style="text-align:right !important">
											<strong  style="text-align:right !important">Credit Days</strong>
										</div>
										<div class="col-md-8 col-xs-11">
											<input type="text" class="form-control" name="account_credit_days" id="account_credit_days" value="<?=isset($custid)?$rel['account_credit_days']:''?>">
										</div>
									</div>
						   		</div>

						   </div>

						   <div id="domestic" class="tab-pane fade row_margin_top">
						   		<div class="form-group" style="margin-top:20px;" id="party_terms_cond_domestic_div">
								</div>
						   </div>

						   <div id="export" class="tab-pane fade row_margin_top">
						   		<div class="form-group" style="margin-top:20px;" id="party_terms_cond_export_div">

								</div>
						   </div>

						</div>

					</div>

				</div>

				<div class="row">

					<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
					<input type='hidden' name='eid' id='eid' value='<?php if($mode=='Edit'){ echo $rel['cust_id']; } else { echo "0"; }?>' />				  
					<div class="col-md-12" style="margin-top:5px;"> 
						<div class="col-md-2"></div>
						<button type="submit" class="btn btn-success">Submit</button> &nbsp;
						<a href="<?=ROOT.CRM_ROOT.'customer_list'?>" type="button" class="btn btn-danger">Cancel</a><div class="col-md-3"></div>					

					</div>

				</div>
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
<?php include_once($include1.'add_comp_product.php');?>
<?php include_once($include1.'add_page_permission.php');?>
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/customer.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/state_mst.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/city_mst.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
	$(".select2_product").select2({
		width: '100%',
		minimumInputLength: 3
	});
	$("#comp_product_id").select2({
		width: '100%',
		minimumInputLength: 3
	})
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});
	$(".datepickerPrev").datepicker({
		format: "dd-mm-yyyy",
		endDate: "-1y",
		autoclose: true,
		todayHighlight: true
	});
	var maxLength = 300;
	<?php if($mode=="Edit"){	?>
		var str_len = "<?= strlen($rel['cust_desc']); ?>";
		var textlen = maxLength - str_len;
		$('#rchars').text(textlen);
	<?php } ?>
	$('#cust_desc').keyup(function() {
		var textlen = maxLength - $(this).val().length;
		$('#rchars').text(textlen);
	});
	$('#cust_gst').on('change', function () {
		var statecode = $(this).val().substring(0, 2);
		var pancarno = $(this).val().substring(2, 12);
		var entityNumber = $(this).val().substring(12, 13);
		var defaultZvalue = $(this).val().substring(13, 14);
		var checksumdigit = $(this).val().substring(14, 15);
		if ($(this).val().length != 15) {
			alert('GST Number is invalid');
			$(this).focus();
			return false;
		}
		if (pancarno.length != 10) {
			alert('GST number is invalid ');
			$(this).focus();
			return false;
		}
		if (defaultZvalue !== 'Z') {
			alert('GST Number is invalid Z not in Entered Gst Number');
			$(this).focus();
		}

		if ($.isNumeric(statecode)) {
			$('#gst_state_code').val(statecode).trigger('change');
		} else {
			alert('Please Enter Valid State Code');
			$(this).focus();
		}

	});
</script>
<?php
if($mode=="Edit"){
	echo "<script>load_state(".$countryid.",'c_add_state',".$stateid.")</script>";
		//echo "<script>load_city(".$stateid.",'c_add_city',".$cityid.")</script>";
	echo "<script>load_consinee_state(".$countryconsigneeid.",'state_consinee_id',".$stateconsigneeid.")</script>";
	echo "<script>load_consinee_city(".$stateconsigneeid.",'city_consinee_id',".$cityconsigneeid.")</script>";
	echo "<script>load_typeswise_terms_dom('0',".$rel['cust_id'].")</script>";
	echo "<script>load_typeswise_terms_exp('1',".$rel['cust_id'].")</script>";
}
else{
	echo "<script>load_state(".$countryid.",'c_add_state',".$stateid.")</script>";
		// echo "<script>load_city(1,'c_add_city',".$cityid.")</script>";
	echo "<script>load_consinee_state(".$countryconsigneeid.",'state_consinee_id',".$stateconsigneeid.")</script>";
	echo "<script>load_consinee_city(".$stateconsigneeid.",'city_consinee_id',".$cityconsigneeid.")</script>";
	echo "<script>load_typeswise_terms_dom('0',".$rel['cust_id'].")</script>";
	echo "<script>load_typeswise_terms_exp('1',".$rel['cust_id'].")</script>";
}
?>


</body>
</html>