	<?php
session_start();
include('../include/urlfile.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ADMINISTRATOR_LEDGER_READ,
	ADMINISTRATOR_LEDGER_ADD,
	ADMINISTRATOR_LEDGER_EDIT,
	ADMINISTRATOR_LEDGER_DELETE
]);
$companyConfiguration=getCompanyConfiguration($dbcon);
$form="Ledger";
$branch_id = $_SESSION['branch_id'];

$company_multicurrency = getCompanyConfiguration($dbcon);

if(strpos($_SERVER['REQUEST_URI'], "ledger_edit")==true) {

	if(!in_array(ADMINISTRATOR_LEDGER_EDIT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}

	$mode="Edit";
	$ledger_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select led.*,usr.template_access_perm_id from tbl_ledger as led 
	LEFT JOIN users as usr ON usr.employee_id = led.l_id
	where l_id=$ledger_id";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

	if(!$rel){
		header("Location: ".ROOT."ledger_list");
	}

    //echo '<pre>';print_r($rel['l_group']);exit;
	$disable = 'disabled';
	$form_type = $rel['l_form'];
	$form_id = $rel['l_form_id'];

	$countryid = $rel['countryid'];
	$stateid = $rel['stateid'];
	$cityid = $rel['cityid'];
	$lGroup = $rel['l_group'];
	$tdstax_cat = $rel['tdstax_cat'];
	$cust_gst_reg = $rel['cust_gst_reg'];
	$ledger_opening_balance_type = $rel['ledger_opening_balance_type'];
		//$hidden ="hidden";
}
else {

	if(!in_array(ADMINISTRATOR_LEDGER_ADD,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";
	$countryid="101";
	$stateid="1";
	$cityid="1";
	$hidden ="";
}
$companyConfiguration=getCompanyConfiguration($dbcon);
$enable_assing_user=$companyConfiguration['enable_assing_user'];
$leger_per = $companyConfiguration['ledger_code'];
$readonly="";
if($companyConfiguration['ledger_code'] ==1){
	$readonly_code = "readonly";
}else{
	$readonly_code = 'onkeyup="check_manual_ledger_code(this.value)"';
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>LEDGER</title>
	<?php include_once($include.'include_css_file.php');?>
	<style>
		.head_margin
		{
			padding:10px;
		}
		.form_class
		{

		}
		.back_head_color
		{
			background-color:#337AB7 !important;
			color:#ffffff !important;
		}
		.row_margin
		{
			margin-top:20px;
		}
		.margin_row
		{
			margin-top:20px;
		}

		.ledger_forms
		{
			display:none !important;
		}
		.xlg
		{
			width:1350px !important;
		}
		.ledger_duplicate
		{
			color: red;
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
		<form class="form-horizontal" role="form" id="ledger_add" action="javascript:;" method="post" name="ledger_add" enctype="multipart/form-data">	
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
										<li><a href="<?=ROOT.'masters_list'?>"> Master List</a></li>
										<li><a href="<?=ROOT.ADMINISTRATION_ROOT.'ledger_list'?>"><?=$form?> List</a></li>
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
									<div class="row">

										<div class="col-md-12 margin_row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Ledger Name *</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" placeholder="Ledger Name" title="Ledger Name" name="ledger_name" maxlength="100" id="ledger_name" value="<?=$rel['l_name']?>" required onblur="check_duplicate_ledger(this.value)"  />
														<strong class='ledger_duplicate'></strong>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Profile Photo </label>
													<div class="col-md-8 col-xs-12">
														<div class="col-md-7">
															<input type="file" id="emp_profile_img" name="emp_profile_img"  title="Select Profile Photo" accept="image/*" />
														</div>
														<div class="col-md-1">
															<?php if($mode=='Edit') { ?>
																<img src="<?php if(isset($rel['emp_profile_img']) && !empty($rel['emp_profile_img'])){ echo ROOT . ADMINISTRATION_ROOT . 'upload/emp_profile_image/'.$rel['emp_profile_img']; } else { echo ROOT . ADMINISTRATION_ROOT. 'upload/emp_profile_image/no_profile.png'; } ?>" width="50" height="50" />
															<?php } ?>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12 margin_row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Alias Name</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" placeholder="Alias Name" maxlength="100" title="Alias Name" name="alias_name" id="alias_name" value="<?=$rel['ledger_alias']?>"  />
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Select Group*</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" <?//= $disable ?> name="ledger_grp" id="ledger_grp" required onchange="show_div_ledger(this.value);load_ledger_code(this.value,'<?=$leger_per?>');" >
															<?=get_all_group($dbcon,$rel['l_group'],'','0');?>
														</select>
														<?php
									                   /* if($disable){
									                        echo '<input type="hidden" name="ledger_grp" id="ledger_grp" value="'.$rel['l_group'].'">';
									                    }*/
									                    ?>
									                    
									                    <input type="hidden" class="form-control" name="group_id" id="group_id" / >


									                    <input type="hidden" class="form-control" name="parent_group_id" id="parent_group_id" / >									                    

									                </div>
									            </div>
									        </div>

									    </div>

									    <div class="col-md-12 margin_row">
									    	<div class="col-md-6 <?=$hidden?>" id="lcode_div">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Ledger Code *</label>
									    			<div class="col-md-8 col-xs-11">
									    				<input type="text" class="form-control" placeholder="Ledger Code" title="Ledger Code" name="ledger_code" id="ledger_code" value="<?=$rel['ledger_code']?>" required  <?=$readonly_code ?> />
									    				<input type="hidden" name="code_id" id="code_id" value="" >
									    			</div>
									    		</div>
									    	</div>
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Email Id</label>
									    			<div class="col-md-8 col-xs-11">
									    				<input type="email" class="form-control" placeholder="Email" title="Please insert valid Email" name="common_email_id" id="common_email_id" value="<?=$rel['common_email_id'];?>" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}"  />
									    			</div>
									    		</div>
									    	</div>
									    </div>

									    <div class="col-md-12 margin_row">
										<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Choose Branch *</label>
									    			<div class="col-md-8 col-xs-11">
									    				<select class="select2" name="branch_id" id="branch_id">
									    					<?=getBranchBox_new($dbcon,$rel['branch_id']);?>
									    				</select>
									    			</div>
									    		</div>	
									    	</div>
										<?php } ?>
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Set Opening Balance</label>
									    			<div class="col-md-8 col-xs-11">
									    				<select class="form-control" name="set_op_balance" id="set_op_balance" onchange="get_opening_balance(this.value)" >
									    					<option value="0" <?php if($rel['ledger_opening_balance_type']=='0'){ echo "selected"; } ?>>Normal</option>
									    					<option value="1" <?php if($rel['ledger_opening_balance_type']=='1'){ echo "selected"; } ?>>Currency wise</option>
									    					<option value="2" <?php if($rel['ledger_opening_balance_type']=='2'){ echo "selected"; } ?>>Branch Wise</option>		
									    				</select>
									    			</div>
									    		</div>
									    	</div>
									    </div>

									    <div class="col-md-12 margin_row">
									    	<?php if($company_multicurrency['enable_multi_currency'] == 1){ ?>
									    		<div class="col-md-6 multiCurrency">
									    			<div class="form-group">
									    				<label class="col-md-4 control-label">Multi Currency *</label>
									    				<div class="col-md-8 col-xs-11">
									    					<select class="form-control" name="multi_currency" title="Please Select Multi Currency" id="multi_currency" onchange="getMultiCurrencyPopup(this.value)" >
									    						<option value="">--Select Multi Currency--</option>
									    						<option value="yes" <?php if($rel['enable_multi_currency_opening']=='1'){ echo "selected"; } ?> >Yes</option>
									    						<option value="no" <?php if($rel['enable_multi_currency_opening']=='0'){ echo "selected"; } ?> >No</option>
									    					</select>
									    					<a href="#" onclick="return getMultiCurrencyPopup('yes')" id="checkMultiCurrLink" >Check Multi Currency</a>
									    				</div>
									    			</div>
									    		</div>
									    	<?php } ?>
									    	<div class="col-md-6 multiBranch">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Multi Branch Opening *</label>
									    			<div class="col-md-8 col-xs-11">
									    				<select class="form-control" title="Please Select Multi Branch" name="multi_branch" id="multi_branch" onchange="getMultiBranchPopup(this.value)" >
									    					<option value="">--Select Multi Branch--</option>
									    					<option value="yes" <?php if($rel['enable_branch_opening']=='1'){ echo "selected"; } ?> >Yes</option>
									    					<option value="no" <?php if($rel['enable_branch_opening']=='0'){ echo "selected"; } ?> >No</option>							
									    				</select>
									    				<a href="#" onclick="return getMultiBranchPopup('yes')" id="checkBranchLink" >Check Multi Branch</a>
									    			</div>
									    		</div>
									    	</div>
									    </div>

									    <div class="col-md-12 margin_row">
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Select Country *</label>
									    			<div class="col-md-8 col-xs-11">
									    				<select class="select2" name="countryid" id="countryid" onChange="load_state(this.value,'stateid','<?=$countryid?>')">
									    					<?=get_country($dbcon,$countryid)?>				
									    				</select>
									    			</div>
									    		</div>
									    	</div>
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Select State *</label>
									    			<div class="col-md-6 col-xs-11">
									    				<select class="select2" name="stateid" id="stateid" onChange="load_city(this.value,'cityid','<?=$stateid;?>')">
									    					<option value="">Select State</option>	
									    					<?//=getstate($dbcon,$rel['stateid'])?>
									    				</select>
									    			</div>
									    			<div class="col-md-2">
									    				<input type="button"  name="addState" id="addState" data-toggle="modal" data-target="" onclick="add_state();" class="btn btn-primary" value="+ Add State"/>
									    			</div>
									    		</div>
									    	</div>
									    </div>

									    <div class="col-md-12 margin_row">
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Select City *</label>
									    			<div class="col-md-6 col-xs-11">
									    				<select class="select2" name="cityid" id="cityid">
									    					<option value="">Select City</option>	
									    				</select>
									    			</div>
									    			<div class="col-md-2">
									    				<input type="button" name="addCity" id="addCity" data-toggle="modal" data-target="" onclick="add_city();" class="btn btn-primary" value="+ Add city"/>
									    			</div>
									    		</div>
									    	</div>
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Pin Code</label>
									    			<div class="col-md-8 col-xs-11">
									    				<input type="text" class="form-control numbersOnly digitOnly"  placeholder="Customer Pincode" name="cust_pincode" id="cust_pincode" value="<?=$rel['cust_pincode']==0?'':$rel['cust_pincode']?>" maxlength="6" minlength="6" onkeypress="return isNumberKey(event)"  />
									    			</div>
									    		</div>
									    	</div>
									    </div>
									    <div class="col-md-12 row_margin">
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Opening Balance</label>
									    			<div class="col-md-8 col-xs-11">
									    				<input type="text"  class="form-control" id="opn_balance" maxlength="20" name="opn_balance" placeholder=""  value="<?php if($mode=='Edit') { echo $rel['opn_balance']; } else { echo ""; } ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);"  />
									    			</div>
									    		</div>
									    	</div>
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Balance Type *</label>
									    			<div class="col-md-8 col-xs-11">
									    				<select class="form-control" name="balance_typeid" id="balance_typeid" title="Select Type">
									    					<?=getbalance_type($dbcon,$rel['balance_typeid'])?>				
									    				</select>
									    			</div>
									    		</div>
									    	</div>
									    </div>

										<div class="col-md-12 row_margin">
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<label class="col-md-4 control-label">Ledger Type</label>
									    			<div class="col-md-8 col-xs-11">
														<select class="form-control" name="ledger_type" id="ledger_type" title="Select Type">
															<option value="0" <?php if($rel['ledger_type']=='0'){ echo "selected"; } ?> >New Ledger</option>
									    					<option value="1" <?php if($rel['ledger_type']=='1'){ echo "selected"; } ?> >Exist Ledger</option>		
									    				</select>
									    			</div>
									    		</div>
									    	</div>
									    	<div class="col-md-6">
									    		<div class="form-group">
									    			<!-- <label class="col-md-4 control-label">Balance Type *</label>
									    			<div class="col-md-8 col-xs-11">
									    				
									    			</div> -->
									    		</div>
									    	</div>
									    </div>

									</div>
								</div>
							</section>

						</div>

						<!--- Customer Form Start -->

						<div class="col-md-12 ledger_forms" id="customer_form" <?php if($mode=='Edit') { if($form_type=='customer_form') { ?> style="display:block !important" <?php } } ?>>

							<div class="row">

								<div class="col-sm-12">

									<header class="panel-heading breadcrumb text-center back_head_color">
										<h3>Customer Information</h3>
									</header>	

									<section class="">

										<div class="row">

											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Company Name *</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" placeholder="Company Name" title="Company Name" name="company_name" id="company_name" value="<?=$rel['company_name']?>"/>
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Contact Person Name*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" placeholder="Contact Person Name" title="Contact Person Name" name="cust_cont_name" id="cust_cont_name" value="<?=$rel['cust_cont_name']?>" required />
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12 margin_row">

												<div class="col-md-12">
													<div class="form-group">
														<label class="col-md-2 control-label">Company Address*</label>
														<div class="col-md-10 col-xs-11">

															<textarea class="form-control" placeholder="Company Address" maxlength="350" title="Company Address" name="m_address" id="m_address" required><?=$rel['m_address']?></textarea>

														</div>
													</div>
												</div>

											</div>

											<div class="col-md-12 margin_row">

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Type of Dealer *</label>
														<div class="col-md-6 col-xs-11">
															<select class="select2" name="cust_gst_reg" id="cust_gst_reg" onchange="changeGstText(this.value)" title="Please select type of dealer" required >
																<option value="">Select Type of Dealer</option>
																<option value="0" <?php if($rel['cust_gst_reg']=='0'){ echo "selected"; } ?> >Registered</option>
																<option value="1" <?php if($rel['cust_gst_reg']=='1'){ echo "selected"; } ?>>Unregistered</option>
																<option value="2" <?php if($rel['cust_gst_reg']=='2'){ echo "selected"; } ?>>Composition</option>
																<option value="3" <?php if($rel['cust_gst_reg']=='3'){ echo "selected"; } ?>>Govt.body</option>
																<option value="4" <?php if($rel['cust_gst_reg']=='4'){ echo "selected"; } ?>>UIN Holder</option>
															</select>
														</div>
													</div>
												</div>

												<div class="col-md-6" id="gst_div" style="display:none">
													<div class="form-group">
														<label class="col-md-4 control-label">GSTIN *</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" name="gst_no" class="form-control blockSpecialChar" onblur="getPanNo(this.value)" minlength="15" maxlength="15" placeholder="GSTIN" id="gst_no" value="<?=$rel['gst_no']?>" title="Please enter Valid 15 digit GST No." >
														</div>
													</div>
												</div>

												
											</div>		
											<div class="col-md-12 margin_row">
												<div class="col-md-6" >
													<div class="form-group">
														<label class="col-md-4 control-label">IEC No </label>
														<div class="col-md-8 col-xs-11">
															<input type="text" name="iec_no" class="form-control blockSpecialChar"  placeholder="IEC No" id="iec_no" value="<?=$rel['iec_no']?>"  >
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Email</label>
														<div class="col-md-8 col-xs-11">
															<input type="email" class="form-control" placeholder="Email" title="Please insert valid Email" name="cust_email" id="cust_email" value="<?= (isset($rel['cust_email']) && $rel['cust_email'] !='0') ? $rel['cust_email'] : '' ?>" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}"  />
														</div>	
													</div>
												</div>
											</div>						

											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">ISD No.</label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="isd_id" id="isd_id">
																	<?=get_isd_no($dbcon,$isd_id)?>				
															</select>
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Mobile No.</label>
														<div class="col-md-8 col-xs-11">
															<input type="number" class="form-control" placeholder="Mobile No." name="cust_mobile" id="cust_mobile" value="<?= (isset($rel['cust_mobile']) && $rel['cust_mobile'] !=0) ? $rel['cust_mobile'] : '' ?>"  />
														</div>
													</div>
												</div>	
											</div>

											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Website </label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control copyPastNotAllowed" placeholder="Website" title="Website" name="cust_website" id="cust_website" value="<?=$rel['cust_website']?>"  />
														</div>	
													</div>
												</div>
                                                <!-- change event for zone : removed by Dimple Panchal
                                                	onchange="get_branch_by_zone(this.value,'branch_id_customer','')"-->
                                                	<div class="col-md-6">
                                                		<div class="form-group">
                                                			<label class="col-md-4 control-label">Zone</label>
                                                			<div class="col-md-6 col-xs-11">
                                                				<select class="select2" name="zone_id" id="zone_id">
                                                					<?=get_zone($dbcon,$rel['zone_id'],$rel['zone_id']);?>				
                                                				</select>
                                                			</div>	
                                                			<div class="col-md-2 col-xs-11">
                                                				<input type="button" name="addZone" id="addZone" data-toggle="modal" data-target="#add_zone_modal" class="btn btn-primary" value="+ Add Zone"/>
                                                			</div>
                                                		</div>
                                                	</div>

                                                </div>

                                                <div class="col-md-12 margin_row">
                                                	<div class="col-md-6 hide">
                                                		<div class="form-group">
                                                			<label class="col-md-4 control-label">Party Type</label>
                                                			<div class="col-md-6">
                                                				<select class="select2" name="party_sez" id="party_sez">
                                                					<option value="0" <?=($rel['party_sez']!='1')?'selected':''?>>Non SEZ</option>
                                                					<option value="1" <?=($rel['party_sez']=='1')?'selected':''?>>SEZ</option>
                                                				</select>
                                                			</div>	
                                                		</div>
                                                	</div>
                                                	
                                                	<div class="col-md-6">
                                                		<div class="form-group">
                                                			<label class="col-md-4 control-label">Is Party Belong To SEZ</label>
                                                			<div class="col-md-8 col-xs-11">
                                                				<select class="form-control" name="enable_sez" id="enable_sez" onchange="" >
                                                					<option value="">--Select--</option>
                                                					<option value="yes" <?php if($rel['enable_sez']=='1'){ echo "selected"; } ?> >Yes</option>
                                                					<option value="no" <?php if($rel['enable_sez']=='0'){ echo "selected"; } ?> >No</option>										
                                                				</select>
                                                			</div>
                                                		</div>
                                                	</div>
                                                	<?phpif($enable_assing_user==1){?>
                                                		<div class="col-md-6">
                                                			<div class="form-group">
                                                				<label class="col-md-4 control-label">Assign User</label>
                                                				<div class="col-md-8 col-xs-11">
                                                					<select class="select2" name="cust_assign_user" id="cust_assign_user">
                                                						<option value="">--Assign User--</option>
                                                						<?php $qry="select * from users where active=0 AND company_id = '".$_SESSION['company_id']."'";
                                                						$rs_state=$dbcon->query($qry);
                                                						while($row=mysqli_fetch_array($rs_state)) { ?>
                                                							<option value="<?php echo $row['user_id']; ?>" <?php if($row['user_id']==$rel['cust_owner']){ echo "selected"; } ?> ><?php echo $row['user_name']; ?></option>
                                                						<?php } ?>
                                                					</select>
                                                				</div>
                                                			</div>
                                                		</div>
                                                	<?php} ?>
                                                </div>
                                                
                                                
                                                <div class="col-md-12 margin_row">
                                                	<div class="col-md-6">
                                                		<div class="form-group">
                                                			<label class="col-md-4 control-label">Payment Terms</label>
                                                			<div class="col-md-8 col-xs-11">
                                                				<select class="select2" name="pay_terms" id="pay_terms">
                                                					<?=getpaymentterms($dbcon,$rel['pay_terms']);?>
													<!-- <option value="">--Payment Terms--</option>
													<option value="15"  <?php if($rel['pay_terms']=='15'){ echo "selected"; } ?>>15 Days</option>
													<option value="30" <?php if($rel['pay_terms']=='30'){ echo "selected"; } ?>>30 Days</option>
													<option value="45" <?php if($rel['pay_terms']=='45'){ echo "selected"; } ?>>45 Days</option>
													<option value="45" <?php if($rel['pay_terms']=='45'){ echo "selected"; } ?>>60 Days</option>
													<option value="45" <?php if($rel['pay_terms']=='45'){ echo "selected"; } ?>>90 Days</option>
													<option value="45" <?php if($rel['pay_terms']=='45'){ echo "selected"; } ?>>120 Days</option> -->
												</select>
											</div>	
											
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Territory</label>
											<div class="col-md-8 col-xs-11">
												<select class="select2" id="territory_id" name="territory_id">
													<?=get_all_territory($dbcon,$rel['territory_id']);?>
												</select>
											</div>
										</div>
									</div>
									
									<!-- <div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Bill Type</label>
											<div class="col-md-8 col-xs-11">
												<select class="select2" name="bill_type" id="bill_type">
													<option value="">--Select Bill Method--</option>
													<option value="0" <?php if($rel['bill_type']=='0'){ echo "selected"; } ?> >Bill To Bill</option>
													<option value="1"  <?php if($rel['bill_type']=='1'){ echo "selected"; } ?>  >Overall</option>
												</select>
											</div>
										</div>
									</div> -->
								</div>

								<div class="col-md-12 margin_row">
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Credit Limit </label>
											<div class="col-md-8 col-xs-11">
												<input type="text" class="form-control digitOnly numbersOnly" placeholder="Credit Limit" title="Credit Limit" maxlength="20" name="credit_limit" id="credit_limit" value="<?=($rel['credit_limit'])?$rel['credit_limit']:''?>" onkeypress="return isNumberKey(event)"  />
											</div>	
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Credit Days </label>
											<div class="col-md-8 col-xs-11">
												<input type="text" class="form-control digitOnly numbersOnly" maxlength="20" placeholder="Credit Days" title="Credit Days" name="credit_days" id="credit_days" value="<?=($rel['credit_days'])?$rel['credit_days']:''?>" onkeypress="return isNumberKey(event)"  />
											</div>	
										</div>
									</div>
								</div>

								<div class="col-md-6 margin_row">
									<div class="form-group">
										<label class="col-md-4 control-label">Remark</label>
										<div class="col-md-8 col-xs-11">
											<textarea class="form-control" name="cust_remark" id="cust_remark"><?=$rel['cust_remark']?></textarea>
										</div>	
									</div>
								</div>
								<div class="col-md-6 margin_row">
									<div class="form-group">
										<label class="col-md-4 control-label">Bill By Bill Opening Balance</label>
										<div class="col-md-8 col-xs-11">
											<select class="form-control" name="enable_billbybill_opening" id="enable_billbybill_opening" onchange="getBillByBillPopup(this.value)" >
												<option value="">--Select Bill By Bill Opening--</option>
												<option value="yes" <?php if($rel['enable_billbybill_opening']=='1'){ echo "selected"; } ?> >Yes</option>
												<option value="no" <?php if($rel['enable_billbybill_opening']=='0'){ echo "selected"; } ?> >No</option>							
											</select>
											<a href="#" onclick="return getBillByBillPopup('yes')" id="checkBillbybillLink" style="display:none;" >Check Bill By Bill Details</a>
										</div>
									</div>
								</div>
							</div><!--Vendor row end-->	
						</section>		

						<section class="panel" style="padding:20px">
							<div class="row">		

								<div class="col-xs-2"> <!-- required for floating -->
									<!-- Nav tabs -->
									<ul class="nav nav-tabs tabs-left">
										<li class="active"><a href="#tbank" data-toggle="tab" id="ltunit">Bank Details</a></li>
										<li><a href="#tcontact" data-toggle="tab" id="ltbopen">Contact Person</a></li>
										<li><a href="#transportation" data-toggle="tab" id="ltbopen">Transportation</a></li>
										<li><a href="#tconsignee" data-toggle="tab" id="ltbopen">Consignee</a></li>
										<li><a href="#tdomestic" data-toggle="tab" id="ltbopen">Domestic </a></li>
										<li><a href="#texport" data-toggle="tab" id="ltbopen">Export </a></li>
									</ul>
								</div>

								<div class="col-xs-10">
									<!-- Tab panes -->
									<div class="tab-content">
										<div class="tab-pane active" id="tbank">
											
											<div class="row">
												<div class="col-md-12">
													<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Bank Details</a></h3>
												</div>
											</div>
											
											<div class="row">

												<div class="col-md-12 margin_row">

													<table class="table table-bordered">
														
														<tr>
															
															<th width="25%">A/c No</th>
															<th width="25%">Bank Name</th>
															<th width="25%">A/C Name</th>
															<th width="15%">IFSC</th>
															<td width="15%">Opening</td>
															<td></td>
														</tr>
														
														<tr>
															
															<td>
																<input type="text" class="form-control copyPastNotAllowed" name="bank_ac" id="bank_ac" maxlength="30" onkeypress="return isNumberKey(event)" />
															</td>
															<td  width="15%">
																<select class="select2" name="bank_name" id="bank_name" >

																	<?=get_all_bank($dbcon,0);?>
																</select>
															</td>
															<td><input type="text" class="form-control copyPastNotAllowed" name="ac_name" maxlength="50" value="" id="ac_name" /></td>
															<td>
																<input type="text" class="form-control copyPastNotAllowed blockSpecialChar" name="bank_ifsc" maxlength="11" value="" id="bank_ifsc" />
															</td>
															<td>
																<input type="text" class="form-control copyPastNotAllowed" name="bank_open" value="" maxlength="20" id="bank_open" onkeypress="return isNumberKey(event)" />
															</td>
															
															<td><input type="button" class="btn btn-primary" value="ADD" maxlength="20"  style="box-shadow: 3px 3px #61a642;" onclick="add_bank()" id="add_bank_bt" /></td>
															
															<input type="hidden" id="edit_id" value=""  />
															<input type="hidden" id="eid" value="<?php echo $rel['cust_id']; ?>"  />
														</tr>
														
													</table>
													
												</div>
												
												<div class="col-md-12"  id="table_bank_details"></div>
												
											</div>
											
										</div>
										
										<div class="tab-pane" id="tcontact">
											
											<div class="row">
												<div class="col-md-12">
													<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Contact Person Details</a></h3>
												</div>
											</div>
											
											<div class="row">

												<div class="col-md-12 margin_row">

													<table class="table table-bordered">
														
														<tr>
															
															<th>Name</th>
															<th>ISD No.</th>
															<th>Mobile</th>
															<th>Email</th>
															<th>Job Title</th>
															<td></td>
														</tr>
														
														<tr>
															
															<td>
																<input type="text" class="form-control" name="con_name" id="con_name" />
															</td>

															<td>
																<select class="select2" name="con_isd_id" id="con_isd_id">
																	<?=get_isd_no($dbcon,$isd_id)?>				
																</select>
															</td>
															<td>
																<input type="text" class="form-control digitOnly numbersOnly" name="con_mobile" id="con_mobile" onkeypress="return isNumberKey(event)" maxlength="10" minlength="10" />
															</td>
															<td>
																<input type="text" class="form-control" name="con_email" id="con_email" />
															</td>
															<td>
																<input type="text" class="form-control" name="job_title" id="job_title" />
															</td>
															<td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_contact_person()" id="add_contact_bt" /></td>
															
															<input type="hidden" id="edit_id_contact" value=""  />
														</tr>
														
													</table>

												</div>
												
												<div class="col-md-12" id="table_contact_details"></div>
												
											</div>
											
										</div>
										<div class="tab-pane" id="transportation">
											
											<div class="row">
												<div class="col-md-12">
													<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Transportation Details</a></h3>
												</div>
											</div>
											
											<div class="row">

												<div class="col-md-12 margin_row">

													<table class="table table-bordered">
														
														<tr>
															
															<th>Name</th>
															<td></td>
														</tr>
														
														<tr>
															
															<td>
																<select class="select2" name="transport_id" id="transport_id" >
																	<?=get_trasports($dbcon,0);?>
																</select>
															</td>
															<td>
																<input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_tran_del()" id="add_tran_bt" />
															</td>
															<input type="hidden" id="edit_id_transport" value=""  />
														</tr>
														
													</table>

												</div>
												
												<div class="col-md-12" id="table_trans_details"></div>
												
											</div>
											
										</div>
										<div class="tab-pane" id="tconsignee">
											<div class="row">
												<div class="col-md-12">
													<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Consignee Details</a></h3>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12 margin_row">
													<table class="table table-bordered">
														<tr>
															<th width="10%">Company Name</th>
															<th width="10%">Person Name</th>
															<th width="8%">Mobile</th>
															<th width="10%">Email</th>
															<th width="10%">Address</th>
														</tr>
														<tr>
															<td>
																<input type="text" class="form-control" name="consignee_comp_name" id="consignee_comp_name" />
															</td>
															<td>
																<input type="text" class="form-control" name="consignee_name" id="consignee_name" autocomplete="off" />
															</td>
															<td>
																<input type="text" class="form-control copyPastNotAllowed" name="consignee_mobile" id="consignee_mobile" onkeypress="return isNumberKey(event)" maxlength="10" minlength="10" autocomplete="off" />
															</td>
															<td>
																<input type="email" class="form-control copyPastNotAllowed" name="consignee_email" id="consignee_email" autocomplete="off" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}" />
															</td>
															<td>
																<textarea class="form-control" name="consignee_address" id="consignee_address" autocomplete="off"></textarea>
															</td>
														</tr>
														<tr>
															<th width="12%">Country</th>
															<th width="12%">State</th>
															<th width="12%">City</th>
															<th width="8%">GST No</th>
															<th width="8%">Pincode</th>
														</tr>
														<tr>
															<td>
																<select class="select2" name="country_consinee_id" id="country_consinee_id" onChange="load_consinee_state(this.value,'state_consinee_id','')" autocomplete="off">
																	<?=get_country($dbcon,$countryid)?>				
																</select>
															</td>
															<td>
																<select class="select2" name="state_consinee_id" id="state_consinee_id" onChange="load_consinee_city(this.value,'city_consinee_id','')" autocomplete="off" >
																	<option value="">Select State</option>
																</select>
															</td>
															<td>
																<select class="select2" name="city_consinee_id" id="city_consinee_id" autocomplete="off">
																	<option value="">Select City</option>
																</select>
															</td>
															<td>
																<input type="text" class="form-control" name="gst_consinee_no" id="gst_consinee_no" autocomplete="off" />
															</td>
															<td>
																<input type="text" class="form-control" name="pin_consinee_no" id="pin_consinee_no" onkeypress="return isNumberKey(event)" maxlength="6" minlength="6" autocomplete="off" />
															</td>
															<input type="hidden" id="edit_id_consignee" value=""  />
														</tr>
														<tr>
															<td colspan="5">
																<center>
																	<input type="button" class="btn btn-primary" value="ADD" style="box-shadow: 3px 3px #61a642;" onclick="add_consignee()" id="add_consignee_btn" />
																</center>
															</td>
														</tr>
													</table>
												</div>
												<div class="col-md-12" id="table_consignee_details"></div>
											</div>
										</div>

										<div class="clearfix"></div>

										<div class="tab-pane" id="tdomestic">
											<div class="row">
												<div class="col-md-12">
													<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Domestic Terms Details</a></h3>
												</div>
											</div>
											<div class="form-group" style="margin-top:20px;" id="party_terms_cond_domestic_div">
											</div>
										</div>
										<div class="clearfix"></div>
										<div class="tab-pane" id="texport">
											<div class="row">
												<div class="col-md-12">
													<h3 style="text-align:center;" class="head_margin"><a style="border-bottom:dotted blue thin">Export Terms Details</a></h3>
												</div>
											</div>

											<div class="form-group" style="margin-top:20px;" id="party_terms_cond_export_div">

											</div>
										</div>
										<div class="clearfix"></div>
									</div>

								</div>

								<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
								<input type='hidden' name='eid' id='eid' value='<?=$rel['cust_id']?>' />
								
							</div>	
						</section>

						

					</div>

				</div>


			</div>

			<!--- Customer Form End -->

			<!--- Bank Form Start -->
			<div class="col-md-12 ledger_forms" id="bank_form" <?php if($mode=='Edit') { if($form_type=='bank_form') { ?> style="display:block !important" <?php } } ?>>

				<div class="row">

					<div class="col-sm-12">

						<header class="panel-heading breadcrumb text-center back_head_color">
							<h3>Bank Details</h3>
						</header>	

						<section class="panel">

							<div class="row">

								<div class="col-md-12">

									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Select Bank *</label>
											<div class="col-md-8 col-xs-11">
												<select class="select2" id="bankid" name="bankid" title="Select Bank" required >
													<?=getbank($dbcon,$rel['bankid'])?>
												</select>
											</div>
										</div>
										
									</div>
									
								</div>
								

								<div class="col-md-12 row_margin">
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Branch *</label>
											<div class="col-md-8 col-xs-11">
												<input type="text"  class="form-control" id="branch_name" name="branch_name" maxlength="100" placeholder="" value="<?= $rel['branch_name'] ?>" required/>
											</div>
										</div>
										
									</div>
									

								</div>

								<div class="col-md-12 row_margin">
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Account Name *</label>
											<div class="col-md-8 col-xs-11">
												<input type="text" maxlength="100"  class="form-control" id="acc_name" name="acc_name" value="<?= $rel['acc_name'] ?>" required title="Enter Account Name" />
											</div>
										</div>
										
									</div>
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Account Number *</label>
											<div class="col-md-8 col-xs-11">
												<input type="text"  class="form-control numbersOnly" id="acc_number" name="acc_number" value="<?= $rel['acc_number'] ?>" placeholder="" required title="Enter Account Number" />
											</div>
										</div>
										
									</div>
									
								</div>

								<div class="col-md-12 row_margin">
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Cheque Series Starting Number </label>
											<div class="col-md-8 col-xs-11">
												<input type="text"  class="form-control" id="acc_chequeno" name="acc_chequeno" value="<?= $rel['acc_chequeno'] ?>" placeholder=""  min="0" onkeypress="return isNumberKey(event)" />
											</div>
										</div>
										
									</div>
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Number of Cheques </label>
											<div class="col-md-8 col-xs-11">
												<input type="text"  class="form-control numbersOnly" id="acc_chequeleft" name="acc_chequeleft" value="<?= $rel['acc_chequeleft'] ?>" placeholder="" min="0" max="100"  onkeypress="return isNumberKey(event)" />
											</div>
										</div>
										
									</div>
									
								</div>

								
							</div>
							
						</section>

					</div>

				</div>

			</div>
			<!--- Bank Form End -->


			<!--- Employee Form Start -->

			<div class="col-md-12 ledger_forms" id="emp_form" <?php if($mode=='Edit') { if($form_type=='emp_form') { ?> style="display:block !important" <?php } } ?>>

				<div class="row">

					<div class="col-sm-12">

						<header class="panel-heading breadcrumb text-center back_head_color">
							<h3>Employee Details</h3>
						</header>	

						
						<section class="panel">

							<div class="row">

								<div class="col-md-12 margin_row">
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-3 control-label">Email(User name)*</label>
											<div class="col-md-6 col-xs-11">
												<input <?= isset($rel['emp_email']) ? 'readonly' :  '' ?> type="text" class="form-control" placeholder="Email" title="Email" name="emp_email" id="emp_email" value="<?=$rel['emp_email']?>"  onkeyup="checkUsername(this.value);" required pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}" />
												
												<input  type="hidden" class="form-control" placeholder="Email" title="Email" name="" id="emp_email_hid" value="<?=$rel['emp_email']?>"  />
												
												<div id="user_error"></div>
											</div>	
										</div> 
										
									</div>
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-3 control-label">Password*</label>
											<div class="col-md-6 col-xs-11">
												<input type="password" class="form-control" placeholder="Password" title="Password" maxlength="15" name="emp_password" id="emp_password" <?=($mode=='Add')?'required':''?>  />
												<input type="checkbox" onclick="showPswdFunction()"> Show Password

											</div>	
										</div> 
										
									</div>
									
								</div>
								
								<div class="col-md-12 margin_row">
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-3 control-label">Mobile No.*</label>
											<div class="col-md-6 col-xs-11">
												<input type="text" class="form-control digitOnly numbersOnly" placeholder="Mobile No." name="emp_mobile" id="emp_mobile" value="<?=$rel['emp_mobile']?>" onkeypress="return isNumberKey(event)" maxlength="10" minlength="10" required  />
											</div>
										</div>
										
									</div>
									
                                   	<!-- change event for zone : removed by Dimple Panchal
                                   		onchange="get_branch_by_zone(this.value,'branch_id_emp')"--> 
                                   		<div class="col-md-6">

                                   			<div class="form-group">
                                   				<label class="col-md-3 control-label">Zone*</label>
                                   				<div class="col-md-6 col-xs-11">
                                   					<select class="select2" name="emp_zone_id" id="emp_zone_id" required>
                                   						<?=get_zone($dbcon,$rel['emp_zone_id'])?>				
                                   					</select>
                                   				</div>	

                                   			</div>

                                   		</div>

                                   	</div>

<!--								<div class="col-md-12 margin_row">
									
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Branch*</label>
											<div class="col-md-6 col-xs-11">
												<select class="select2" name="branch_id_emp" id="branch_id_emp" required>
																	
												</select>
											</div>	
											
										</div>
									</div>
								</div>-->
								<div class="col-md-12 margin_row">
									
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Allocated State</label>
											<div class="col-md-6">
												<select class="select2" name="alloc_stateid[]" id="alloc_stateid" onChange="load_city_all();" placeholder="Allocated State" multiple>
													<?=get_state_all($dbcon,$rel['alloc_stateid'],"101")?>				
												</select>
											</div>	
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Allocated City</label>
											<div class="col-md-6">
												<select class="select2" name="alloc_cityid[]" id="alloc_cityid" placeholder="Allocated City" multiple>
													<?=get_city_all($dbcon,$rel['alloc_cityid'],$rel['alloc_stateid'])?>	
												</select>
											</div>	
										</div>
									</div>
								</div>
								<div class="col-md-12 margin_row">
									<?php $user_type = ($rel['report_to_user_type'] ? $rel['report_to_user_type'] : $_SESSION['user_type']); ?>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Report To User-Type</label>
											<div class="col-md-6">
												<select class="select2" name="report_to_user_type" id="report_to_user_type" title="Select Type" onchange="load_report_to_users(this.value)">
													<option value="">--Select User Type--</option>
													<?=getusertype($dbcon,$user_type," and (usertype_id!=1 or company_id=".$_SESSION['company_id'].")")?>			
												</select>
											</div>	
										</div>
									</div>
									<?php $user_id = ($rel['report_to_user_id'] ? $rel['report_to_user_id'] : $_SESSION['user_id']); ?>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Report To User</label>
											<div class="col-md-6">
												<select class="select2" name="report_to_user_id" id="report_to_user_id" >
													<?=get_users_typewise($dbcon,$user_id," and user_type=".$user_type)?>			
												</select>
											</div>	
										</div>
									</div>
								</div>

								<div class="col-md-12 margin_row">
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Type*</label>
											<div class="col-md-6 col-xs-11">
												<select class="select2" name="emp_user_type" id="emp_user_type" title="Select Type" required>
													<option value="">--Select User Type--</option>
													<?=getusertype($dbcon,$rel['emp_user_type']," and (usertype_id!=1 or company_id=".$_SESSION['company_id'].")")?>			
												</select>
											</div>	
											
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="template_name" class="col-md-3 control-label">Template Name</label>
											<div class="col-md-6">
												<select class="select2" id="template_id" name="template_id">
													
													<?php
													echo getTemplateName($dbcon, $rel['template_access_perm_id']);
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-12 margin_row">
									
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Authorized Signature</label>
											<div class="col-md-6 col-xs-11">
												<div class="col-md-7">
													<input type="file" id="emp_signature_img" name="emp_signature_img"  title="Select Authorized Signature" accept="image/*" />
												</div>
												<div class="col-md-1">
													<?php if($mode=='Edit') { ?>
														<img src="<?php if(isset($rel['emp_signature_img']) && !empty($rel['emp_signature_img'])){ echo ROOT . 'upload/signature/'.$rel['emp_signature_img']; } else { echo ROOT .'finance/upload/emp_profile_image/no_profile.png'; } ?>" width="50" height="50" />
													<?php } ?>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Shift Time</label>
											<div class="col-md-6 col-xs-11">

												<select class="select2" name="shift_time" id="shift_time">
													<?=get_shift_type($dbcon,$rel['shift_time']);?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<?phpif($companyConfiguration['ip_add_login']==1){ ?>
								<div class="col-md-12 margin_row">
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-3 control-label">IP Address</label>
											<div class="col-md-6 col-xs-11">
												<input type="text" class="form-control" placeholder="IP Address" title="IP Address" maxlength="15" name="ip_add" id="ip_add" value="<?=$rel['ip_add']?>"  />
												
											</div>	
										</div> 
										
									</div>
								</div>
							<?php} ?>
								
							</div>
							
						</section>



						<div class="col-md-12 col-md-offset-4 row_margin">



						</div>

					</div>

				</div>

			</div>
			<!--- Employee Form End -->

			<!--- Tax Form Start -->

			<div class="col-md-12 ledger_forms" id="tax_form" <?php if($mode=='Edit') { if($form_type=='tax_form') { ?> style="display:block !important" <?php } } ?>>

				<div class="row">

					<div class="col-sm-12">

						<header class="panel-heading breadcrumb text-center back_head_color">
							<h3>Tax Details</h3>
						</header>	

						<section class="panel">

							<div class="row">

								<div class="col-md-12">

									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-3 control-label">Tax Value (in %)</label>
											<div class="col-md-6 col-xs-11">
												<input type="text"  name="tax_value"  id="tax_value" class="form-control numbersOnly" value="<?=$rel['tax_value']?>" maxlength="10"  placeholder="Tax Value(in %)" onkeypress="return isNumberKey(event)" maxlength="5" />
											</div>	
										</div> 
										
									</div>
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-3 control-label">Print Priority</label>
											<div class="col-md-6 col-xs-11">
												<input type="text" maxlength="10"  name="print_priority"  id="print_priority" value="<?=$rel['print_priority']?>" class="form-control numbersOnly"  placeholder="Print Priority" />
											</div>	
										</div> 
										
									</div>
									

								</div>
								
							</div>
							
						</section>

					</div>

				</div>

			</div>

			<!--- Tax Form End -->

			<section  class="panel">
				<div class="panel-body">
					<div class="row">

						<div class="col-md-12 row_margin hide" >

							<?php if($company_multicurrency['enable_salesman'] == 1){ ?>
								<div class="col-md-6">
									<div class="form-group">
										<label class="col-md-4 control-label ">Enable Salesman</label>
										<div class="col-md-8 col-xs-11">
											<select class="form-control" name="enable_salesman" id="enable_salesman" onchange="get_salesman_popup(this.value)" >
												<option value="">--Select--</option>
												<option value="yes" <?php if($rel['enable_salesman']=='1'){ echo "selected"; } ?> >Yes</option>
												<option value="no" <?php if($rel['enable_salesman']=='0'){ echo "selected"; } ?> >No</option>
											</select>
											<a href="#" onclick="return get_salesman_popup('yes')" id="checkSalesmanLink" >Check Salesman Details</a>

										</div>
									</div>
								</div>
							<?php } ?>

						</div>

						<div class="col-md-12 row_margin">
							<?php if($company_multicurrency['enable_cost_center'] == 1){ ?>
								<div class="col-md-6">
									<div class="form-group">
										<label class="col-md-4 control-label">Enable Cost Center</label>
										<div class="col-md-8 col-xs-11">
											<select class="form-control" name="enable_cost_center" id="enable_cost_center" onchange="" >
												<option value="">--Select--</option>
												<option value="yes" <?php if($rel['enable_cost_center']=='1'){ echo "selected"; } ?> >Yes</option>
												<option value="no" <?php if($rel['enable_cost_center']=='0'){ echo "selected"; } ?> >No</option>								
											</select>
										</div>
									</div>
								</div>
							<?php } if($company_multicurrency['enable_tds_reporting'] == 1){ ?>
								<div class="col-md-6 tds_tcs">
									<div class="form-group">
										<label class="col-md-4 control-label">Enable TDS</label>
										<div class="col-md-8 col-xs-11">
											<select class="form-control" name="enable_tds" id="enable_tds" onchange="ledger_grp_change();get_party_by_ledger()" >
												<option value="">--Select--</option>
												<option value="yes" <?php if($rel['enable_tds']=='1'){ echo "selected"; } ?> >Yes</option>
												<option value="no" <?php if($rel['enable_tds']=='0'){ echo "selected"; } ?> >No</option>										
											</select>
										</div>
									</div>
								</div>
							<?php } ?>

							<?php 	if($company_multicurrency['enable_tds_reporting'] == 1){ ?>
								<div class="col-md-6 tds_tcs">
									<div class="form-group">
										<label class="col-md-4 control-label">Enable TCS</label>
										<div class="col-md-8 col-xs-11">
											<select class="form-control" name="enable_tcs" id="enable_tcs" onchange="ledger_grp_change()" >
												<option value="">--Select--</option>
												<option value="yes" <?php if($rel['enable_tcs']=='1'){ echo "selected"; } ?> >Yes</option>
												<option value="no" <?php if($rel['enable_tcs']=='0'){ echo "selected"; } ?> >No</option>										
											</select>
										</div>
									</div>
								</div>
							<?php } ?>
							
							<div class="col-md-6 party_pay_cat_div">
								<div class="form-group">
									<label class="col-md-4 control-label">TDS Tax Category *</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="tdstax_cat" id="tdstax_cat" onchange="get_party_by_ledger(this.value);" title="Please Select TDS Tax Category" >
											<?=get_all_tds_cat($dbcon,$rel['tdstax_cat']);?>									
										</select>
										
									</div>
								</div>
							</div>
							
							<div class="col-md-6">
							</div>
							<div class="col-md-6 party_pay_cat_div party_pay_cat_div_sub">
								<div class="form-group">
									<label class="col-md-4 control-label">Party Payee Category *</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="party_pay_cat" id="party_pay_cat" onchange="" title="Please Select Party Payee Category" >
											
										</select>
										<input type="hidden" value="<?=$mode=='Edit'?$rel['party_pay_cat']:'';?>" id="party_pay_cat_text" name="party_pay_cat_text">
									</div>
								</div>
							</div>
							<?php 	if($company_multicurrency['enable_depreciation'] == 1){ ?>
								<div class="col-md-6 depreciation">
									<div class="form-group">
										<label class="col-md-4 control-label">Enable Depreciation</label>
										<div class="col-md-8 col-xs-11">
											<select class="form-control" name="enable_depreciation" id="enable_depreciation" onchange="getDepreciationPopup(this.value)" >
												<option value="">--Select--</option>
												<option value="yes" <?php if($rel['enable_depreciation']=='1'){ echo "selected"; } ?> >Yes</option>
												<option value="no" <?php if($rel['enable_depreciation']=='0'){ echo "selected"; } ?> >No</option>								
											</select>
											<a href="#" onclick="return getDepreciationPopup('yes')" id="checkDepreciationLink" >Check Depreciation</a>
										</div>
									</div>
								</div>
							<?php } ?>
							<div class="col-md-6 ledgerTaxtype">
								<div class="form-group">
									<label class="col-md-4 control-label">Tax Type</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="ledger_Tax_type" id="ledger_Tax_type" onchange="" >
											<?=get_ledger($dbcon,$rel['ledger_Tax_type'],'and l_group = 31');?>									
										</select>
									</div>
								</div>
							</div>
							<?php 	if($company_multicurrency['enable_month_budget'] == 1){ ?>
								<div class="col-md-6 monthly_budget">						
									<div class="form-group">
										<label class="col-md-4 control-label">Set Monthly Budget</label>
										<div class="col-md-8 col-xs-11">
											<select class="form-control" name="enable_monthly_budget" id="enable_monthly_budget" onchange="getMonthlyBudgetPopup(this.value)" >
												<option value="">--Select--</option>
												<option value="yes" <?php if($rel['enable_monthly_budget']=='1'){ echo "selected"; } ?> >Yes</option>
												<option value="no" <?php if($rel['enable_monthly_budget']=='0'){ echo "selected"; } ?> >No</option>										
											</select>
											<a href="#" onclick="return getMonthlyBudgetPopup('yes')" id="checkMonthlyLink" >Check Monthly Budget</a>
										</div>
									</div>						
								</div>
							<?php } ?>
							<div class="col-md-6 chequebank">						
								<div class="form-group">
									<label class="col-md-4 control-label">Enable Deposite / Issue Cheque Details</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="enable_cheque_deposit" id="enable_cheque_deposit" onchange="getBankChequePopup(this.value)" >
											<option value="">--Select--</option>
											<option value="yes" <?php if($rel['enable_cheque_deposit']=='1'){ echo "selected"; } ?> >Yes</option>
											<option value="no" <?php if($rel['enable_cheque_deposit']=='0'){ echo "selected"; } ?> >No</option>								
										</select>
										<a href="#" onclick="return getBankChequePopup('yes')" id="checkChequeDepositLink" >Check Cheque Details</a>
									</div>
								</div>						
							</div>
							<div class="col-md-6 monthly_budget">
								<div class="form-group">
									<label class="col-md-4 control-label">Gst Applicable</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="ledger_gst_applicable" id="ledger_gst_applicable" onchange="changeGstField()" >
											<option value="">--Select Gst Applicable--</option>
											<option value="yes" <?php if($rel['ledger_gst_applicable']=='1'){ echo "selected"; } ?> >Yes</option>
											<option value="no" <?php if($rel['ledger_gst_applicable']=='0'){ echo "selected"; } ?> >No</option>										
										</select>
									</div>
								</div>
							</div>
							
						</div>
						<div class="col-md-12 row_margin gstApplicable" style="display:none">

							<div class="col-md-6">						
								<div class="form-group">
									<label class="col-md-4 control-label">Tax Category</label>
									<div class="col-md-8 col-xs-11">

										<select class="form-control" name="ledger_tax_category" id="ledger_tax_category" onchange="" >
											<?php
											echo get_tax_cetegory_ledger($dbcon,$rel['ledger_tax_category']);
											?>										
										</select>
									</div>
								</div>						
							</div>
							<div class="col-md-6">						
								<div class="form-group">
									<label class="col-md-4 control-label">HSN Code</label>
									<div class="col-md-8 col-xs-11">									
										<input type="text" class="form-control numbersOnly" placeholder="HSN Code" name="ledger_hsn" id="ledger_hsn" maxlength="6" value="<?=$rel['ledger_hsn']?>" required  />
									</div>
								</div>						
							</div>
							<div class="col-md-6">						
								<div class="form-group">
									<label class="col-md-4 control-label">ITC Eligibility</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="ledger_itc" id="ledger_itc" onchange="" >
											<?php
											echo get_common_category($dbcon, 15,'ITC Eligibility',$rel['ledger_itc']);
											?>										
										</select>
									</div>
								</div>						
							</div>
							<div class="col-md-6">						
								<div class="form-group">
									<label class="col-md-4 control-label">RCM Nature</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="ledger_rcm" id="ledger_rcm" onchange="" >
											<?php
											echo get_common_category($dbcon, 13,'RCM Nature',$rel['ledger_rcm']);
											?>										
										</select>
									</div>
								</div>						
							</div>
						</div>

						<div class="col-md-12 row_margin">

							<div class="col-md-6 billSundry">						
								<div class="form-group">
									<label class="col-md-4 control-label">Set To Bill Sundry</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="enable_bill_sunfry" id="enable_bill_sunfry" onchange="get_sundry_popup(this.value)" >
											<option value="">--Select--</option>
											<option value="yes" <?php if($rel['enable_bill_sunfry']=='1'){ echo "selected"; } ?> >Yes</option>
											<option value="no" <?php if($rel['enable_bill_sunfry']=='0'){ echo "selected"; } ?> >No</option>										
										</select>
										<a href="#" onclick="return get_sundry_popup('yes')" id="checkBillSundryLink" >Check Bill Sundry</a>
									</div>
								</div>						
							</div>
						</div>

						<div class="col-md-12 margin_row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-4 control-label">PAN / IT No.</label>
									<div class="col-md-8 col-xs-11">
										<input type="text" class="form-control" placeholder="Customer PAN" name="m_pan" id="m_pan"   value="<?php if($mode=='Edit' && $rel['m_pan'] !='') { echo $rel['m_pan']; } else { echo ""; } ?>" style="text-transform:uppercase" maxlength="10" minlength="10"  />
									</div>
								</div>
							</div>

						</div>
						
						<div class="col-md-12 margin_row" style="text-align:center">
							<h2>Upload Document</h2>
						</div>

						<div class="col-md-12 margin_row">
							<table class="display table table-bordered table-striped">
								<thead>
									<tr>
										<th width="60%">Document Name</th>
										<th width="30%">Document File</th>
										<th width="10%">Action</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><input type="text" class="form-control" id="led_doc_name" name="led_doc_name" placeholder="Document Name"></td>
										<td><input type="file" class="form-control" id="led_attch_file" name="led_attch_file"></td>
										<td><button type="button" class="btn btn-primary" id="led_attch_btn" onclick="add_ledger_doc_field()">Add</button></td>
									</tr>
								</tbody>
								
							</table>
						</div>

						<div class="col-md-12 margin_row" style="margin-top:20px;" id="led_attach_div">
							
						</div>


						<div class="col-md-12 col-md-offset-5 row_margin" >

							<input type="hidden" id="form_type" name="form_type" value='<?php if($mode=='Edit') { echo $form_type; } else { echo ""; } ?>'  />
							<input type='hidden' name='mode' id='mode' value='<?php if($mode=='Edit') { echo "edit"; } else { echo "add"; } ?>' />
							<input type='hidden' name='ledger_id' id='ledger_id' value='<?php if($mode=='Edit') { echo $ledger_id; } else { echo "0"; } ?>' />				  
							<button type="submit" name="" id="btn_submit" class="btn btn-success">Submit</button>
							<a class="btn btn-danger" href="<?=ROOT.ADMINISTRATION_ROOT.'ledger_list'?>">Cancel</a>

						</div>
					</div>

				</div>

			</section>


		</div>
		<!--state overview end-->
	</section>
</section>

</form>
<!--main content end-->
<!--footer start-->

<?php include_once($include1.'add_zone.php');?>
<?php include_once($include1.'add_city.php');?>
<?php include_once($include1.'add_state.php');?>
<?php include_once($include1.'add_multi_currency.php');?>
<?php include_once($include1.'add_billbybill_opening.php');?>
<?php include_once($include1.'add_multi_branch.php');?>
<?php include_once($include1.'add_depreciation.php');?>
<?php include_once($include1.'add_bill_sundry.php');?>
<?php include_once($include1.'add_monthly_budget.php');?>
<?php include_once($include1.'add_bank_cheque.php');?>
<?php include_once($include1.'add_salesman.php');?>
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/customer.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/ledger.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/consignee.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/state_mst.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/city_mst.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/zone_mst.js?<?=time()?>"></script>

<script>

	jQuery('#emp_password').bind('cut copy paste', function(e) {
		e.preventDefault();
		toastr.warning("Cut / Copy / Paste Disabled", "WARNING");
	});

	$(".select2").select2({
		width: '100%',

	});
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});

	$('#ledger_name').keyup(function(e) {
		var txtVal = $(this).val();
		var group_form = $("#ledger_grp").find('option:selected').attr('data-formgroup');
		$('#alias_name').val(txtVal);
		if(group_form=='customer_form'){
			$('#company_name').val(txtVal);
		}

	});
	function showPswdFunction() {
		var x = document.getElementById("emp_password");
		if (x.type === "password") {
			x.type = "text";
		} else {
			x.type = "password";
		}
	}

	function show_div_ledger(gid)
	{
		Loading();
		$.ajax({

			type:'post',
			url: root_domain+administration_domain+'app/ledger/',
			type: "POST",
			data: { mode : "get_open_form", gid : gid },
			success: function(response)
			{
				//alert(response);
				//console.log(response);
				var obj = JSON.parse(response);

				if(obj.form_id=='customer_form'){
					$('#company_name').val($('#ledger_name').val());
				}
				
				$("#customer_form").hide();
				$("#bank_form").hide();
				$("#expense_form").hide();
				$("#income_form").hide();
				$("#emp_form").hide();
				$("#tax_form").hide();
				$('#'+obj.form_id).show();
				
				$('#'+obj.form_id).removeClass("ledger_forms");
				$('#form_type').val(obj.form_id);

			//enter group id 

			$('#group_id').val(obj.group_id);
			$('#parent_group_id').val(obj.group_parent_id);

			//call another onchange functions 

			ledger_grp_change();
			ledger_grp_change_fix_assets();
			ledger_grp_change_Tax_type();
			ledger_monthly_budget_change();
			ledger_chequebank_change();
			ledger_tcs_tds_change();
			//get_party_by_ledger();

		}
	});

		Unloading();
	}
	<?if($cust_gst_reg =='0'){?>
		changeGstText(0);
		<?}?>


/*
window.onbeforeunload = function() {
  return "Data will be lost if you leave the page, are you sure?";
};
*/

</script>
<?php
if($mode=="Edit"){
	echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
	echo "<script>load_consinee_state(".$countryid.",'state_consinee_id',".$stateid.")</script>";
	echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
	echo "<script>load_consinee_city(".$stateid.",'city_consinee_id',".$cityid.")</script>";
	echo "<script>show_div_ledger(".$lGroup.")</script>";
	echo "<script>ledger_grp_change_Tax_type()</script>";

	echo "<script>load_typeswise_terms_dom('0',".$ledger_id.")</script>";
	echo "<script>load_typeswise_terms_exp('1',".$ledger_id.")</script>";
	
	if($ledger_opening_balance_type==1){
		echo "<script> $('.multiCurrency').show();
		$('.multiBranch').hide();
		$('#multi_currency').prop('required',true);
		$('#opn_balance').prop('readOnly',true);</script>";
	}else if($ledger_opening_balance_type==2){
		echo "<script> $('.multiBranch').show();
		$('.multiCurrency').hide();
		$('#multi_branch').prop('required',true);
		$('#opn_balance').prop('readOnly',true);</script>";
	}else{
		echo "<script> $('.multiCurrency').hide();
		$('.multiBranch').hide();
		$('#multi_currency').prop('required',false);
		$('#multi_branch').prop('required',false);
		$('#opn_balance').prop('readOnly',false);</script>";
	}
	//echo "<script>get_opening_balance(".$ledger_opening_balance_type.")</script>";

		//echo "<script>get_branch_by_zone(".$rel['zone_id'].",'branch_id_customer',".$rel['branch_id_customer'].")</script>";
		//echo "<script>get_branch_by_zone(".$rel['emp_zone_id'].",'branch_id_emp',".$rel['branch_id_employee'].")</script>";
	echo "<script>get_party_by_ledger(".$tdstax_cat.")</script>";
}
else{
	echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
	echo "<script>load_consinee_state(".$countryid.",'state_consinee_id',".$stateid.")</script>";
	echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
	echo "<script>load_consinee_city(".$stateid.",'city_consinee_id',".$cityid.")</script>";
	echo "<script>get_opening_balance('0')</script>";
	echo "<script>$('.billSundry').hide();</script>";

	echo "<script>load_typeswise_terms_dom('0',".$ledger_id.")</script>";
	echo "<script>load_typeswise_terms_exp('1',".$ledger_id.")</script>";
}
?>
</body>
</html>