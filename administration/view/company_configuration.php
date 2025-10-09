<?php 
session_start();	
include('../include/urlfile.php');	

$form="CompanyConfiguration";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']=$infopage['filename'];
$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ADMINISTRATOR_COMPANY_CONFIGURATION_READ,
	ADMINISTRATOR_COMPANY_CONFIGURATION_ADD
]);

if(!in_array(ADMINISTRATOR_COMPANY_CONFIGURATION_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
 $query="SELECT * FROM `tbl_company_configuration` where isdelete=0 and company_id=$_SESSION[company_id]";
$rel=brp_mysqli_fetch_assoc($dbcon->query($query));


$query1="select * from tbl_financial_year where current_status='1' and company_id=$_SESSION[company_id]";
$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));

$query2="select * from tbl_company where company_id=$_SESSION[company_id]";
$rel2=mysqli_fetch_assoc($dbcon->query($query2));
	//echo "<pre>"; print_r($rel);die;
$mode="Edit";
$companySettings = getCompanySettings($dbcon);
if($companySettings) {
	$setting_id = $companySettings['id'] ? $companySettings['id'] : $setting_id;
	$crm_auto_mail = $companySettings['crm_auto_mail'] ? $companySettings['crm_auto_mail'] : $crm_auto_mail;
	$project_wise_manufacturing = $companySettings['project_wise_manufacturing'] ? $companySettings['project_wise_manufacturing'] : $project_wise_manufacturing;
	$project_wise_item_rate = $companySettings['project_wise_item_rate'] ? $companySettings['project_wise_item_rate'] : $project_wise_item_rate;
	$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : $quotation_print_content;
	$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
	$quotation_footer_content = $companySettings['quotation_footer_content'] ? $companySettings['quotation_footer_content'] : $quotation_footer_content;
	$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
}
$companyConfiguration=getCompanyConfiguration($dbcon);
$getspecialConfiguration=getspecialConfiguration($dbcon);
if($companyConfiguration) {
	$company_conf_id = $companyConfiguration['company_conf_id'] ? $companyConfiguration['company_conf_id'] : $company_conf_id;
	$crm_pro_type = $companyConfiguration['crm_pro_type'] ? $companyConfiguration['crm_pro_type'] : $crm_pro_type;
	$crm_user_type = $companyConfiguration['crm_user_type'] ? $companyConfiguration['crm_user_type'] : $crm_user_type;
	$so_pro_type = $companyConfiguration['so_pro_type'] ? $companyConfiguration['so_pro_type'] : $so_pro_type;
	$indent_po_pro_type = $companyConfiguration['indent_po_pro_type'] ? $companyConfiguration['indent_po_pro_type'] : $indent_po_pro_type;
	$production_pro_type = $companyConfiguration['production_pro_type'] ? $companyConfiguration['production_pro_type'] : $production_pro_type;
	$inventory_pro_type = $companyConfiguration['inventory_pro_type'] ? $companyConfiguration['inventory_pro_type'] : $inventory_pro_type;
	$rejection_pro_type = $companyConfiguration['rejection_pro_type'] ? $companyConfiguration['rejection_pro_type'] : $rejection_pro_type;
	$service_pro_type = $companyConfiguration['service_pro_type'] ? $companyConfiguration['service_pro_type'] : $service_pro_type;
	$upload_receipt = $companyConfiguration['upload_reciept'] ? $companyConfiguration['upload_reciept'] : $upload_receipt;
	$qc_upload_receipt = $companyConfiguration['qc_upload_receipt'] ? $companyConfiguration['qc_upload_receipt'] : $qc_upload_receipt;
}
$crm_pro_search=explode(",",$companyConfiguration['crm_pro_search']);
$purchase_pro_search=explode(",",$companyConfiguration['purchase_pro_search']);
$production_pro_search=explode(",",$companyConfiguration['production_pro_search']);
$sales_pro_search=explode(",",$companyConfiguration['sales_pro_search']);
$bom_pro_search=explode(",",$companyConfiguration['bom_pro_search']);
$service_pro_search=explode(",",$companyConfiguration['service_pro_search']);

$crm_pro_print=explode(",",$companyConfiguration['crm_pro_print']);
$purchase_pro_print=explode(",",$companyConfiguration['purchase_pro_print']);
$production_pro_print=explode(",",$companyConfiguration['production_pro_print']);
$sales_pro_print=explode(",",$companyConfiguration['sales_pro_print']);
$bom_pro_print=explode(",",$companyConfiguration['bom_pro_print']);

$sales_party_show=explode(",",$companyConfiguration['sales_party_show']);
$purchase_party_show=explode(",",$companyConfiguration['purchase_party_show']);
$shift_days = $companyConfiguration['shift_days'] ? $companyConfiguration['shift_days'] : $shift_days;

$quotation_header_content = str_ireplace(array("\r","\n",'\r','\n'),'', $companyConfiguration['quotation_header_content']);
$so_header_content = str_ireplace(array("\r","\n",'\r','\n'),'', $companyConfiguration['so_header_content']);
$po_header_content = str_ireplace(array("\r","\n",'\r','\n'),'', $companyConfiguration['po_header_content']);
$invoice_header_content = str_ireplace(array("\r","\n",'\r','\n'),'', $companyConfiguration['invoice_header_content']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>COMPANY CONFIGURATION</title>
	<style type="text/css">
		label{
			font-size: 15px;
		}
		.row_margin
		{
			margin-top:10px;
		}
		.btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
			z-index:2;
			background-color: #bbdce6;
		}
		.control-label{
			font-weight: bold;
		}
		.fa-info-circle
		{
			color: blue !important;
			font-size: 16px !important;
		}
		.submit_err
		{
			color: red;
		}
	</style>
	<?php include_once($include.'include_css_file.php');?>
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
								<h3>New <?=$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
									<li class="active"><?=$form?> List</li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--Company Configuration overview start-->
				<div class="row">
					<?php if(in_array(ADMINISTRATOR_COMPANY_CONFIGURATION_ADD,$bulkAccessArray)){ ?>
						<div class="col-sm-3">
							<section class="panel">
								<header class="panel-heading">
									Company Configuration
								</header>	
								<div class="panel-body">
									<!-- <form role="form" id="" action="" method="" name=""> -->


										<button type="" class="form-control btn btn-info company_setting">Company Setting</button><br><br>
										<button type="" class="form-control btn btn-info account">Account Setting</button><br><br>
										<button type="" class="form-control btn btn-info inventory">Inventory Setting</button><br><br>
										<button type="" class="form-control btn btn-info enterprise">Enterprise Feature</button><br><br>
										<button type="" class="form-control btn btn-info api">API Setting</button><br><br>

										<button type="" class="form-control btn btn-info finance_year_btn">Financial Year Setting</button><br><br>
										<!--Start Jayesh for design department-->
										<button type="" class="form-control btn btn-info print_setup">Print Setup</button><br><br>
										<button type="" class="form-control btn btn-info design">Production Setting</button><br><br>
										<button type="" class="form-control btn btn-info crm_set">CRM Setting</button><br><br>
										<button type="" class="form-control btn btn-info purchase_set">Purchase Setting</button><br><br>
										<button type="" class="form-control btn btn-info resource_set">Resource Setting</button><br><br>
										<button type="" class="form-control btn btn-info approval_set">Approval Setting</button><br><br>
										<button type="" class="form-control btn btn-info forecast_set">Forecast Setting</button><br><br>
										<button type="" class="form-control btn btn-info qc_set">QC Setting</button><br><br>
										<button type="" class="form-control btn btn-info service_set">Service Setting</button><br><br>
										<!-- </form> -->

									</div>
								</section>
							</div>
						<?php } ?>
						<?php if(in_array(ADMINISTRATOR_COMPANY_CONFIGURATION_ADD,$bulkAccessArray)){ ?>
							<div class="col-sm-9">
							<?php }else{ ?>	
								<div class="col-sm-12">
								<?php } ?>		
								<section class="panel">
					<!-- <header class="panel-heading">
						Company Configuration List
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header> -->
					<div class="panel-body">
						<div class="comp_setting">
							<form class="form-horizontal" role="form" id="a_add" action="javascript:;" method="post" name="a_add">
								<div class="row">
									<div class="col-md-10">

										<!-- Amish Soni Start 04-02-2021 -->
										<div class="form-group">
											<label class="col-md-3 control-label">Company ID *</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="Company ID" name="cmp_unique_id" id="cmp_unique_id"  value="<?=$rel2['cmp_unique_id']?>" required title="Enter Company ID" readonly />
											</div>
										</div>
										<!-- Amish Soni End 04-02-2021 -->
										<div class="form-group">
											<label class="col-md-3 control-label">Company Name *</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="Company Name" name="company_name" id="company_name"  value="<?=$rel2['company_name']?>" required title="Enter Company Name" /> 
											</div>
										</div>						 
										<div class="form-group">
											<label class="col-md-3 control-label">Address *</label>
											<div class="col-md-9 col-xs-11">
												<textarea id="address" name="address" class="form-control" rows="10"><?=stripslashes($rel2['address'])?></textarea> 
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Logo Content</label>
											<div class="col-md-9 col-xs-11">
												<textarea id="logo_content" name="logo_content" class="form-control" rows="10"><?=stripslashes($rel2['logo_content'])?></textarea> 
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">State</label>
											<div class="col-md-9 col-xs-11">
												<select class="select2" name="stateid" id="stateid">
													<?=getstate($dbcon,$rel2['stateid'])?>				
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">City</label>
											<div class="col-md-9 col-xs-11">
												<select class="select2" name="city_id" id="city_id">
													<?=getcity($dbcon,$rel2['stateid'],$rel2['city_id'])?>				
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Pincode</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" id="pincode" name="pincode" placeholder="Pincode" class="form-control numbersOnly digitOnly" value="<?=$rel2['pincode']?>" maxlength="6" minlength="6" onkeypress="return isNumberKey(event)"/>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Mobile No.</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" id="contact_no" name="contact_no" placeholder="Mobile No." class="form-control" value="<?=$rel2['contact_no']?>" />
											</div>
										</div>		
										<div class="form-group">
											<label class="col-md-3 control-label">Email</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" id="website" name="website" placeholder="Email" class="form-control" value="<?=$rel2['website']?>" />
											</div>
										</div>	
										<div class="form-group">
											<label class="col-md-3 control-label">Website</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" id="company_website" name="company_website" placeholder="Website" class="form-control" value="<?=$rel2['company_website']?>" />
											</div>
										</div>	
										<div class="form-group">
											<label class="col-md-3 control-label">Head Logo</label>
											<div class="col-md-6 col-xs-11">
												<input type="file" class="form-control" placeholder="Logo" name="logo" id="logo" accept="image/*" <?phpif($mode=="Add") { echo 'required';}?> title="logo" />

											</div>
											<div class="col-md-3 col-xs-11">
												<?php 
												if($mode=="Edit")
												{
													echo '<img src="'.ROOT.LOGO.$rel2['logo'].'" style="width:120px"/>';
												}
												?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Footer Logo</label>
											<div class="col-md-6 col-xs-11">
												<input type="file" class="form-control" placeholder="Logo" name="f_logo" id="f_logo" accept="image/*" <?phpif($mode=="Add") { echo 'required';}?> title="Footer Logo" />
											</div>
											<div class="col-md-3 col-xs-11">
												<?php if($mode=="Edit")
												{
													echo '<img src="'.ROOT.LOGO.$rel2['f_logo'].'" style="width:120px"/>';
												}
												?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Admin Signature</label>
											<div class="col-md-6 col-xs-11">
												<input type="file" class="form-control" placeholder="Admin Signature" name="authorized_signature" id="authorized_signature" accept="image/*" <?phpif($mode=="Add") { echo 'required';}?> title="Admin Signature" />
											</div>
											<div class="col-md-3 col-xs-11">
												<?php if($mode=="Edit")
												{
													echo '<img src="'.ROOT.SIGNATURE_V.$rel2['authorized_signature'].'" style="width:100px"/>';
												}
												?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Currency*</label>
											<div class="col-md-9 col-xs-11">
												<select class="select2" name="currency_id" id="currency_id">
													<?=getcurrency($dbcon,$rel2['currency_id']);?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Quotation Validity*</label>
											<div class="col-md-9 col-xs-11">
												<select class="select2" name="quot_validity" id="quot_validity" required>
													<option value="">Select Option</option>
													<?php for($i=1;$i <= 100; $i++){ ?>
														<option value="<?= $i ?>" <?=(($rel2['quot_validity'] == $i)? "selected='selected'" : '')?>><?= $i ?> Days</option>
													<?php } ?>
												</select>
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Bank Name</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="Bank Name" name="bank_name" id="bank_name"  value="<?=$rel2['bank_name']?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">A/c No</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="A/c No" name="ac_no" id="ac_no"  value="<?=$rel2['ac_no']?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">IFCS </label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="IFCS" name="ifcs" id="ifcs"  value="<?=$rel2['ifcs']?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">Branch Name</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="Branch Name" name="branch_name" id="branch_name"  value="<?=$rel2['branch_name']?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">GSTIN</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="GSTIN" name="gstno" id="gstno"  value="<?=$rel2['vatno']?>" />
											</div>
										</div>
										<div class="form-group">
											<label class="col-md-3 control-label">IEC No</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="IEC No" name="iec_no" id="iec_no"  value="<?=$rel2['iec_no']?>" />
											</div>
										</div>	

										<div class="form-group">
											<label class="col-md-3 control-label">Lut No.</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" id="lut_no" name="lut_no" class="form-control" title="Lut No." placeholder="Lut No." value="<?=$rel2['lut_no']?>" />
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-3 control-label">CIN No.</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" id="cin" name="cin" class="form-control" title="CIN No." placeholder="CIN No." value="<?=$rel2['cin']?>" />
											</div>
										</div>

										<div class="form-group">
											<label class="col-md-3 control-label">PAN No</label>
											<div class="col-md-9 col-xs-11">
												<input type="text" class="form-control" placeholder="PAN Card No." name="pan_no" id="pan_no"  value="<?=$rel2['pan_no']?>" />
											</div>
										</div> 

										<div class="form-group">
											<label class="col-md-3 control-label">Invoice Condition Content</label>
											<div class="col-md-9 col-xs-11">
												<textarea class="form-control" placeholder="Invoice Condition" name="condition" id="condition" ><?=$rel2['conditions']?></textarea>
											</div>
										</div>
										<div class="col-md-3"></div>
										<button type="submit" class="btn btn-success">Submit</button> &nbsp;
										<div class="col-md-3"></div>					 						 	
									</div>
								</div><!--Vendor row end-->	
								<input type='hidden' name='mode' id='mode' value='edit' />
								<input type='hidden' name='eid' id='eid' value='<?=$rel2['company_id']?>' />				  
							</form>
						</div>
						<form id="company_confg" role="form" name="company_confg" method="post" >
							<input type='hidden' name='mode' id='mode' value='add' />
							<input type='hidden' name='company_conf_id' id='company_conf_id' value='<?php echo $rel['company_conf_id'] ?>'; />
							<div class="acct">
								<div class="row">
									<div class="col-md-4"><label>Enable cost center  &nbsp&nbsp</label></div>
									<div class="col-md-4" ><input type="checkbox" class="" name="enable_cost_center" <?php echo (isset($rel['enable_cost_center']) && ($rel['enable_cost_center'])==1) ? "checked" : '' ; ?>  ></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable party dashboard &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" <?php echo (isset($rel['enable_party_dashboard']) && ($rel['enable_party_dashboard'])==1) ? "checked" : ''; ?> class="" name="enable_party_dashboard"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable Bank Reconciliation &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_bank_reconcilation']) && ($rel['enable_bank_reconcilation'])==1) ? "checked" : ''; ?> name="enable_bank_reconcilation"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable PDC in payment / Receipt &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" <?php echo (isset($rel['enablae_pdc']) && ($rel['enablae_pdc'])==1) ? "checked" : ''; ?> class="" name="enablae_pdc"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable Monthly Budgets &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_month_budget']) && ($rel['enable_month_budget'])==1) ? "checked" : ''; ?> name="enable_month_budget"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable company’s Act Depreciation &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_depreciation']) && ($rel['enable_depreciation'])==1) ? "checked" : ''; ?> name="enable_depreciation"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable multi-currency &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_multi_currency']) && ($rel['enable_multi_currency'])==1) ? "checked" : ''; ?> name="enable_multi_currency"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable Salesman / Broker &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_salesman']) && ($rel['enable_salesman'])==1) ? "checked" : ''; ?> name="enable_salesman"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable Transport Details &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_transport']) && ($rel['enable_transport'])==1) ? "checked" : ''; ?> name="enable_transport"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable Bill by bill Balance &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_billby_bill_blnc']) && ($rel['enable_billby_bill_blnc'])==1) ? "checked" : ''; ?> name="enable_billby_bill_blnc"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable Hypothication &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_hypothication']) && ($rel['enable_hypothication'])==1) ? "checked" : ''; ?> name="enable_hypothication"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable Tax Editable &nbsp&nbsp</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['tax_editable']) && ($rel['tax_editable'])==1) ? "checked" : ''; ?> name="tax_editable"></div>
								</div>
								<div class="row">
									<div class="col-md-4"><label>Enable Installation Type</label></div>
									<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_installation_type']) && ($rel['enable_installation_type'])==1) ? "checked" : ''; ?> name="enable_installation_type"></div>
								</div>
								<div class="row">
									<div class="col-md-12 row_margin">
										<h4><strong>Party Show Permission: </strong></h4>
									</div>
									<div class="col-md-12">
										<div class="form-group">
											<label class="col-md-3 control-label">Sales</label>
											<div class="col-md-6 col-xs-11">
												<select class="select2" name="sales_party_show[]" id="sales_party_show" multiple>
													<?=get_all_groups($dbcon,$rel['sales_party_show'])?>				
												</select>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-3 control-label">Purchase</label>
											<div class="col-md-6 col-xs-11">
												<select class="select2" name="purchase_party_show[]" id="purchase_party_show" multiple>
													<?=get_all_groups($dbcon,$rel['purchase_party_show'])?>
												</select>
											</div>
										</div>
									</div>

									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-3 control-label">Inventory </label>
											<div class="col-md-6 col-xs-11">
												<select class="select2" name="inventory_party_show[]" id="inventory_party_show" multiple>
													<?=get_all_groups($dbcon,$rel['inventory_party_show'])?>
												</select>
											</div>
										</div>
									</div>
									<div class="col-md-12 row_margin">
										<div class="form-group">
											<label class="col-md-3 control-label">Transaction dashboard user type</label>
											<div class="col-md-6 col-xs-11">
												<select class="select2" name="trans_dash_user_type[]" id="trans_dash_user_type" multiple>
													<?=getusertype($dbcon,$rel['trans_dash_user_type']); ?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<h4><strong>Assign Users to Ledger and Customer : </strong></h4>
									</div>
									<div class="col-md-12">
										<div class="form-group">
											<label class="col-md-3 control-label">Assign Users: </label>
											<div class="col-md-6 col-xs-11">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($companyConfiguration['enable_assing_user'] == 0){ echo "active";}?>">
														<input type="radio" name="enable_assing_user" id="enable_assing_user1" autocomplete="off" value="0" <?php if($companyConfiguration['enable_assing_user'] == 0){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($companyConfiguration['enable_assing_user'] == 1){ echo "active";}?>" >
														<input type="radio" name="enable_assing_user" id="enable_assing_user2" autocomplete="off" value="1" <?php if($companyConfiguration['enable_assing_user'] == 1){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>
								</div><br>

								<div class="row">
									<div class="col-md-12">
										<h4><strong>TCS/TDS Configuration : </strong></h4>
									</div>
									<div class="col-md-12">
										<div class="col-md-6">
											<div class="row">
												<div class="col-md-6"><label>Enable TDS Reporting</label></div>
												<div class="col-md-4" ><input type="checkbox" class="" <?php echo (isset($rel['enable_tds_reporting']) && ($rel['enable_tds_reporting'])==1) ? "checked" : ''; ?> name="enable_tds_reporting"></div>
											</div>
											<div class="row">
												<div class="col-md-6"><label>Enable TCS Reporting</label></div>
												<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_tcs_reporting']) && ($rel['enable_tcs_reporting'])==1) ? "checked" : ''; ?> name="enable_tcs_reporting"></div>
											</div>	
										</div>


											<!-- <div class="col-md-6 gross_bal_tds">
												<label class="col-md-6 control-label">TDS Gross Balance limit</label>
												<div class="col-md-5 col-xs-11">
													<input type="text" class="form-control" placeholder="TDS Gross Balance limit" title="TDS Gross Balance limit" 
													name="gross_balance_tds_limit" id="gross_balance_tds_limit" value="<?=isset($rel['gross_balance_tds_limit'])&&$rel['gross_balance_tds_limit']!=''&&$rel['gross_balance_tds_limit']!='0'  ?$rel['gross_balance_tds_limit']:'5000000'?>" />
												</div>
												<div class="col-md-1">
													<a href="#"  data-original-title="If Not set Any balance Auto Gross Balance (500000) will be applicable" data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i>
												</div>								
											</div><br><br> -->
											<div class="col-md-6 gross_bal">
												<label class="col-md-6 control-label">TCS Gross Balance limit</label>
												<div class="col-md-5 col-xs-11">
													<input type="text" class="form-control" placeholder="Gross Balance limit" title="Gross Balance limit" 
													name="gross_balance_limit" id="gross_balance_limit" value="<?=isset($rel['gross_balance_limit'])&&$rel['gross_balance_limit']!=''&&$rel['gross_balance_limit']!='0'  ?$rel['gross_balance_limit']:'5000000'?>" />
												</div>
												<div class="col-md-1">
													<a href="#"  data-original-title="If Not set Any balance Auto Gross Balance (500000) will be applicable" data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i>
												</div>								
											</div>
										</div><br><br>
										<div class="col-md-12">
											<div class="form-group">
												<label class="col-md-3 control-label">Show Ledger Code *: </label>
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['ledger_code'] == '0'){ echo "active";}?>">
															<input type="radio" name="ledger_code" id="ledger_code1" autocomplete="off" value="0" <?php if($companyConfiguration['ledger_code'] == '0'){ echo "checked";}?>  > Manual
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['ledger_code'] == '1'){ echo "active";}?>" >
															<input type="radio" name="ledger_code" id="ledger_code2" autocomplete="off" value="1" <?php if($companyConfiguration['ledger_code'] == '1'){ echo"checked"; }?>> Automatic 
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="row row_margin">
										<div class="col-md-12">
											<div class="form-group">
												<label class="col-md-3 control-label">Enable Material Center: </label>
												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['enable_material_center'] == 0){ echo "active";}?>">
															<input type="radio" name="enable_material_center" id="enable_material_center1" autocomplete="off" value="0" <?php if($companyConfiguration['enable_material_center'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['enable_material_center'] == 1){ echo "active";}?>" >
															<input type="radio" name="enable_material_center" id="enable_material_center2" autocomplete="off" value="1" <?php if($companyConfiguration['enable_material_center'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12 row_margin">
											<div class="form-group">
												<label class="col-md-3 control-label">SO to Invoice Description Transfer: </label>
												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['so_invo_descri_transfer'] == 0){ echo "active";}?>">
															<input type="radio" name="so_invo_descri_transfer" id="so_invo_descri_transfer1" autocomplete="off" value="0" <?php if($companyConfiguration['so_invo_descri_transfer'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['so_invo_descri_transfer'] == 1){ echo "active";}?>" >
															<input type="radio" name="so_invo_descri_transfer" id="so_invo_descri_transfer2" autocomplete="off" value="1" <?php if($companyConfiguration['so_invo_descri_transfer'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-12 row_margin">
											<div class="form-group">
												<label class="col-md-3 control-label">SO Discount Editable..? </label>
												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['so_discount_editable'] == 0){ echo "active";}?>">
															<input type="radio" name="so_discount_editable" id="so_discount_editable1" autocomplete="off" value="0" <?php if($companyConfiguration['so_discount_editable'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['so_discount_editable'] == 1){ echo "active";}?>" >
															<input type="radio" name="so_discount_editable" id="so_discount_editable2" autocomplete="off" value="1" <?php if($companyConfiguration['so_discount_editable'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-12 row_margin">
											<div class="form-group">
												<label class="col-md-3 control-label">SO Discount Calculation Show..? </label>
												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['so_calculation_discount_show'] == 0){ echo "active";}?>">
															<input type="radio" name="so_calculation_discount_show" id="so_calculation_discount_show1" autocomplete="off" value="0" <?php if($companyConfiguration['so_calculation_discount_show'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['so_calculation_discount_show'] == 1){ echo "active";}?>" >
															<input type="radio" name="so_calculation_discount_show" id="so_calculation_discount_show2" autocomplete="off" value="1" <?php if($companyConfiguration['so_calculation_discount_show'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-12 row_margin">
											<div class="form-group">
												<label class="col-md-3 control-label">Invoice Discount Editable..? </label>
												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['invoice_discount_editable'] == 0){ echo "active";}?>">
															<input type="radio" name="invoice_discount_editable" id="invoice_discount_editable1" autocomplete="off" value="0" <?php if($companyConfiguration['invoice_discount_editable'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['invoice_discount_editable'] == 1){ echo "active";}?>" >
															<input type="radio" name="invoice_discount_editable" id="invoice_discount_editable2" autocomplete="off" value="1" <?php if($companyConfiguration['invoice_discount_editable'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-12 row_margin">
											<div class="form-group">
												<label class="col-md-3 control-label">Invoice Discount Calculation Show..? </label>
												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['invoice_calculation_discount_show'] == 0){ echo "active";}?>">
															<input type="radio" name="invoice_calculation_discount_show" id="invoice_calculation_discount_show1" autocomplete="off" value="0" <?php if($companyConfiguration['invoice_calculation_discount_show'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['invoice_calculation_discount_show'] == 1){ echo "active";}?>" >
															<input type="radio" name="invoice_calculation_discount_show" id="invoice_calculation_discount_show2" autocomplete="off" value="1" <?php if($companyConfiguration['invoice_calculation_discount_show'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>

									<div class="row">
										<div class="col-md-12">
											<h4><strong>Ageing Analysis Time Slab</strong></h4>
										</div>

										<div class="col-md-12">
											<table class="table table-bordered table-hover table-striped">
												<tr>
													<th>#</th>
													<th>Start Days</th>
													<th></th>
													<th>End Days</th>
												</tr>
												<?php 

												for($i=1;$i<=5;$i++)
												{
													$aq = $dbcon->query("select * from tbl_aging_slab where slab_name='$i' and company_id='$_SESSION[company_id]'");
													$rowaq = brp_mysqli_fetch_assoc($aq);
													$value_aging_start = $rowaq['slab_start_day'];
													$value_aging_end = $rowaq['slab_end_day'];

													echo "<tr>

													<th>".$i."</th>
													<th>
													<input type='text' class='form-control' name='aging_start_days".$i."' id='aging_start_days".$i."' onchange='check_start_slab(".$i.")' value='".$value_aging_start."' />
													</th>
													<th>TO</th>
													<th>
													<input type='text' class='form-control' name='aging_end_days".$i."' id='aging_end_days".$i."'  onchange='check_end_slab(".$i.")' value='".$value_aging_end."' />
													</th>
													</tr>";
												}
												?>
											</table>
										</div>
									</div>
								</div>
								<div class="inv">
									<div class="row">
										<div class="col-md-4"><label>Enable Scheme  &nbsp&nbsp</label></div>
										<div class="col-md-4" ><input type="checkbox" class="" <?php echo (isset($rel['enable_scheme']) && ($rel['enable_scheme'])==1) ? "checked" : ''; ?> name="enable_scheme"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable Parameterized Details &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_paramter_stock']) && ($rel['nable_paramter_stock'])==1) ? "checked" : ''; ?> name="enable_paramter_stock"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable batch wise details &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_batch_stock']) && ($rel['enable_batch_stock'])==1) ? "checked" : ''; ?> name="enable_batch_stock"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable serial no wise details &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_serial_stock']) && ($rel['enable_serial_stock'])==1) ? "checked" : ''; ?> name="enable_serial_stock"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable MRP wise details &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_mrp_stock']) && ($rel['enable_mrp_stock'])==1) ? "checked" : ''; ?> name="enable_mrp_stock"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable Free Quantity Vouchers &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_free_qty']) && ($rel['enable_free_qty'])==1) ? "checked" : ''; ?> name="enable_free_qty"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable Negative Stock &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_negative_qty']) && ($rel['enable_negative_qty'])==1) ? "checked" : ''; ?> name="enable_negative_qty"></div>
									</div>

									<div class="row">
										<div class="col-md-4"><label>Enable Consolidate Item &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_consolidate_item']) && ($rel['enable_consolidate_item'])==1) ? "checked" : ''; ?> name="enable_consolidate_item"></div>
									</div>

									<div class="row">
										<div class="col-md-4"><label>Item Code Generation &nbsp;&nbsp;</label></div>
										<div class="col-md-4" ><input type="checkbox" class="item_code_generate" <?php echo (isset($rel['item_code_generate']) && ($rel['item_code_generate'])==1) ? "checked" : ''; ?> name="item_code_generate"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Common ItemMaster For Different Companies?</label></div>
										<div class="col-md-4"><input type="checkbox" class="common_item_diff_company" <?php echo (isset($rel['common_item_diff_company']) && ($rel['common_item_diff_company'])==1) ? "checked" : ''; ?> name="common_item_diff_company"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Party code generation &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="party_code_generate" <?php echo (isset($rel['party_code_generate']) && ($rel['party_code_generate'])==1) ? "checked" : ''; ?> name="party_code_generate"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable Multiple make in item master ? &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="multiple_make_item_master" <?php echo (isset($rel['multiple_make_item_master']) && ($rel['multiple_make_item_master'])==1) ? "checked" : ''; ?> name="multiple_make_item_master"></div>
									</div>

									<div class="row">
										<div class="col-md-4"><label>Enable GRN Sticker Print ? &nbsp&nbsp</label></div>
										<div class="col-md-4"><input type="checkbox" class="grn_sticker_print" <?php echo (isset($rel['grn_sticker_print']) && ($rel['grn_sticker_print'])==1) ? "checked" : ''; ?> name="grn_sticker_print"></div>
									</div>

									<div class="row">
										<div class="col-md-12"><h4><strong>How To Invoice Time So Product Load : </strong></h4></div>
										<div class="col-md-4"><label>Sales Time How to Load:</label></div>
										<div class="col-md-8">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($companyConfiguration['sales_time_load_pro'] == 0){ echo "active";}?>">
													<input type="radio" name="sales_time_load_pro" id="sales_time_load_pro1" autocomplete="off" value="0" <?php if($companyConfiguration['sales_time_load_pro'] == 0){ echo "checked";}?>  > Product Load Wise
												</label>
												<label class="btn btn-secondary <?php if($companyConfiguration['sales_time_load_pro'] == 1){ echo "active";}?>" >
													<input type="radio" name="sales_time_load_pro" id="sales_time_load_pro2" autocomplete="off" value="1" <?php if($companyConfiguration['sales_time_load_pro'] == 1){ echo"checked"; }?>> Product Dropdown Wise
												</label>
											</div>
										</div>
									</div><br>

									<div class="row">
										<div class="col-md-4"><label>Trading Stock? </label></div>
										<div class="col-md-8">
											<div class="form-group">

												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['trading_stock'] == 0){ echo "active";}?>">
															<input type="radio" name="trading_stock" id="trading_stock1" autocomplete="off" value="0" <?php if($companyConfiguration['trading_stock'] == 0){ echo "checked";}?>  > NO
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['trading_stock'] == 1){ echo "active";}?>" >
															<input type="radio" name="trading_stock" id="trading_stock2" autocomplete="off" value="1" <?php if($companyConfiguration['trading_stock'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>

									<?php
										$store_disable = "";

										if($companyConfiguration['wo_bw_alloc_stock'] == '1'){
											$store_disable = "disabled";
										}
									?>
									<!-- <div class="row">
										<div class="col-md-4"><label>Store Approval?* :</label></div>
										<div class="col-md-4">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary store_approval store_approval1 <?php if($companyConfiguration['store_approval'] == 0){ echo "active";}?>" <?=$store_disable?>>
													<input type="radio" name="store_approval" id="store_approval1" autocomplete="off" value="0" <?php if($companyConfiguration['store_approval'] == 0){ echo "checked";}?> > NO
												</label>
												<label class="btn btn-secondary store_approval store_approval2 <?php if($companyConfiguration['store_approval'] == 1){ echo "active";}?>" <?=$store_disable?>>
													<input type="radio" name="store_approval" id="store_approval2" autocomplete="off" value="1" <?php if($companyConfiguration['store_approval'] == 1){ echo"checked"; }?> > Yes
												</label>
											</div>
										</div>
									</div></br> -->
									<input type="hidden" name="store_approval" id="store_approval2" autocomplete="off" value="1"> 
									<div class="row">
										<div class="col-md-4"><label>Batch wise Stock? <a href="#"  data-original-title="This setting is for generate stock batch wise or not." data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i> </label></div>
										<div class="col-md-8">
											<div class="form-group">

												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%;">
														<label class="btn btn-secondary <?php if($companyConfiguration['batch_wise_stock'] == 0){ echo "active";}?>" data-original-title="If you don't want to add stock batch wise" data-toggle="tooltip" data-placement="top" >
															<input type="radio" name="batch_wise_stock" id="batch_wise_stock1" onchange="toggle_banch_no_wise()" autocomplete="off" value="0" <?php if($companyConfiguration['batch_wise_stock'] == 0){ echo "checked";}?>  > NO
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['batch_wise_stock'] == 1){ echo "active";}?>" data-original-title="If you want to add stock batch wise" data-toggle="tooltip" data-placement="top">
															<input type="radio" onchange="toggle_banch_no_wise()" name="batch_wise_stock" id="batch_wise_stock2" autocomplete="off" value="1" <?php if($companyConfiguration['batch_wise_stock'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>

									<div class="row batch_stock_permission" style="display: <?php if($companyConfiguration['batch_wise_stock'] == 0){ echo "none";}else{ echo "block";}?>;" >
										<div class="col-md-4"><label>Batch Stock? <a href="#"  data-original-title="This setting is for generate bach stock manually or automatic." data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle"></a></i> </label></div>
										<div class="col-md-8">
											<div class="form-group">

												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%;">
														<label class="btn btn-secondary <?php if($companyConfiguration['batch_stock'] == 0){ echo "active";}?>" data-original-title="If you want to add stock batch no manually" data-toggle="tooltip" data-placement="top" >
															<input type="radio" name="batch_stock" id="batch_stock1" onchange="toggle_batch_stock()" autocomplete="off" value="0" <?php if($companyConfiguration['batch_stock'] == 0){ echo "checked";}?>  > Manually
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['batch_stock'] == 1){ echo "active";}?>" data-original-title="If you want to add stock batch no automatic" data-toggle="tooltip" data-placement="top">
															<input type="radio" onchange="toggle_batch_stock()" name="batch_stock" id="batch_stock2" autocomplete="off" value="1" <?php if($companyConfiguration['batch_stock'] == 1){ echo"checked"; }?>> Automatic
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>

									<div class="row batch_no_permission" style="display: <?php if($companyConfiguration['batch_stock'] == 0){ echo "none";}else{ echo "block";}?>;" >
										<div class="col-md-4"><label>Batch No? <a href="#"  data-original-title="This setting is for generate stock product wise or general batch wise." data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle"></a></i> </label></div>
										<div class="col-md-8">
											<div class="form-group">

												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%;">
														<label class="btn btn-secondary <?php if($companyConfiguration['batch_no_stock'] == 0){ echo "active";}?>"data-original-title="If you want to generat number general wise" data-toggle="tooltip" data-placement="top" >
															<input type="radio" name="batch_no_stock" id="batch_no_stock1" onchange="toggle_banch_no_wise()" autocomplete="off" value="0" <?php if($companyConfiguration['batch_no_stock'] == 0){ echo "checked";}?>  > General Wise
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['batch_no_stock'] == 1){ echo "active";}?>" data-original-title="If you want to generate number product wise" data-toggle="tooltip" data-placement="top">
															<input type="radio" onchange="toggle_banch_no_wise()" name="batch_no_stock" id="batch_no_stock2" autocomplete="off" value="1" <?php if($companyConfiguration['batch_no_stock'] == 1){ echo"checked"; }?>> Product Wise
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>

									<div class="row">
										<div class="col-md-4">
											<label>Manage Inventory *: </label>
										</div>

										<div class="col-md-8">
											<div class="form-group">
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel2['inventory_management'] == 0){ echo "active";}?>">
															<input type="radio" name="inventory_management" id="inventory_management1" autocomplete="off" value="0" <?php if($rel2['inventory_management'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($rel2['inventory_management'] == 1){ echo "active";}?>" >
															<input type="radio" name="inventory_management" id="inventory_management2" autocomplete="off" value="1" <?php if($rel2['inventory_management'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>

									<br>
									<div class="row">
										<div class="col-md-4">
											<label>Batch No as GRN No: </label>
										</div>

										<div class="col-md-8">
											<div class="form-group">
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel['batchno_as_grnno'] == 0){ echo "active";}?>">
															<input type="radio" name="batchno_as_grnno" id="batchno_as_grnno1" autocomplete="off" value="0" <?php if($rel['batchno_as_grnno'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($rel['batchno_as_grnno'] == 1){ echo "active";}?>" >
															<input type="radio" name="batchno_as_grnno" id="batchno_as_grnno2" autocomplete="off" value="1" <?php if($rel['batchno_as_grnno'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<br>
									<div class="row">
										<div class="col-md-4">
											<label>Batch Type *: </label>
										</div>

										<div class="col-md-8">
											<div class="form-group">
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel['batch_type'] == 0){ echo "active";}?>">
															<input type="radio" name="batch_type" id="batch_type1" autocomplete="off" value="0" <?php if($rel['batch_type'] == 0){ echo "checked";}?>  > Batch
														</label>
														<label class="btn btn-secondary <?php if($rel['batch_type'] == 1){ echo "active";}?>" >
															<input type="radio" name="batch_type" id="batch_type2" autocomplete="off" value="1" <?php if($rel['batch_type'] == 1){ echo"checked"; }?>> Serial
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>

									<br>	
									<div class="row">
										<div class="col-md-4">
											<label>Batch Prcoess From :  </label>
										</div>

										<div class="col-md-8">
											<div class="form-group">
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel['batch_process'] == 0){ echo "active";}?>">
															<input type="radio" name="batch_process" id="batch_process1" autocomplete="off" value="0" <?php if($rel['batch_process'] == 0){ echo "checked";}?>  > Start  
														</label>
														<label class="btn btn-secondary <?php if($rel['batch_process'] == 1){ echo "active";}?>" >
															<input type="radio" name="batch_process" id="batch_process2" autocomplete="off" value="1" <?php if($rel['batch_process'] == 1){ echo"checked"; }?>> End
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>

									<div class="row">
										<div class="col-md-4"><label>GRN Diff. for PO? <a href="#"  data-original-title="This setting is for create GRN same as PO OR Different from PO." data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i> </label></div>
										<div class="col-md-8">
											<div class="form-group">

												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%;">
														<label class="btn btn-secondary <?php if($companyConfiguration['grn_diff_from_po'] == 0){ echo "active";}?>" data-original-title="If you want to create GRN same as PO." data-toggle="tooltip" data-placement="top" >
															<input type="radio" name="grn_diff_from_po" id="grn_diff_from_po1" onchange="toggle_banch_no_wise()" autocomplete="off" value="0" <?php if($companyConfiguration['grn_diff_from_po'] == 0){ echo "checked";}?>  > NO
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['grn_diff_from_po'] == 1){ echo "active";}?>" data-original-title="If you want to create GRN Different from PO and add other product in GRN." data-toggle="tooltip" data-placement="top">
															<input type="radio" onchange="toggle_banch_no_wise()" name="grn_diff_from_po" id="grn_diff_from_po2" autocomplete="off" value="1" <?php if($companyConfiguration['grn_diff_from_po'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>
									<div class="row">
										<div class="col-md-4">
											<label>Jobwork GRN :  </label>
										</div>

										<div class="col-md-8">
											<div class="form-group">
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel['jobwork_grn'] == 0){ echo "active";}?>">
															<input type="radio" name="jobwork_grn" id="jobwork_grn1" autocomplete="off" value="0" <?php if($rel['jobwork_grn'] == 0){ echo "checked";}?>  > FIFO WISE  
														</label>
														<label class="btn btn-secondary <?php if($rel['jobwork_grn'] == 1){ echo "active";}?>" >
															<input type="radio" name="jobwork_grn" id="jobwork_grn2" autocomplete="off" value="1" <?php if($rel['jobwork_grn'] == 1){ echo"checked"; }?>> SEPARATE
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>


									<div class="row">
										<div class="col-md-12">
											<h4><strong>Product Searching: </strong></h4>
										</div>
										<div class="col-md-12">
											<div class="col-md-4">
												<label class="col-md-4 control-label">CRM</label>
											</div>
											<div class="col-md-8">
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="crm_pro_search[]" value="item" <?=(in_array('item',$crm_pro_search)) ? "checked" : ""; ?>> Item No
												</div>
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="crm_pro_search[]" value="drawing" <?=(in_array('item',$crm_pro_search)) ? "checked" : ""; ?>> Drawing No
												</div>
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="crm_pro_search[]" value="alias" <?=(in_array('alias',$crm_pro_search)) ? "checked" : ""; ?>> Alias
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<div class="col-md-4">
												<label class="col-md-4 control-label">Purchase</label>
											</div>
											<div class="col-md-8">
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="purchase_pro_search[]" value="item" <?=(in_array('item',$purchase_pro_search)) ? "checked" : ""; ?>> Item No
												</div>
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="purchase_pro_search[]" value="drawing" <?=(in_array('drawing',$purchase_pro_search)) ? "checked" : ""; ?>> Drawing No
												</div>
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="purchase_pro_search[]" value="alias" <?=(in_array('alias',$purchase_pro_search)) ? "checked" : ""; ?>> Alias
												</div>
											</div>
										</div>	
										
										<div class="col-md-12">
											<div class="col-md-4">
												<label class="col-md-4 control-label">Sales</label>
											</div>
											<div class="col-md-8">
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="sales_pro_search[]" value="item" <?=(in_array('item',$sales_pro_search)) ? "checked" : ""; ?>> Item No
												</div>
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="sales_pro_search[]" value="drawing" <?=(in_array('drawing',$sales_pro_search)) ? "checked" : ""; ?>> Drawing No
												</div>
												<div class="col-md-4 col-xs-4">
													<input type="checkbox" name="sales_pro_search[]" value="alias" <?=(in_array('alias',$sales_pro_search)) ? "checked" : ""; ?>> Alias
												</div>
											</div>
										</div>
										
										<div class="col-md-12">
											<h4><strong>Item Code: </strong></h4>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-3 control-label">Generate Item Code: </label>
													<div class="col-md-6 col-xs-11">
														<select class="select2" name="generate_item_code" id="generate_item_code" required>
															<option value="">Select Option</option>
															<option value="0" <?=(($companyConfiguration['generate_item_code'] == 0)? "selected='selected'" : '')?>>Auto-generate</option>
															<option value="1" <?=(($companyConfiguration['generate_item_code'] == 1)? "selected='selected'" : '')?>>Manually</option>
														</select>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12">
											<h4><strong>ABC Analysis: </strong></h4>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-3 control-label">A Type Stock: </label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" name="stock_type_a" id="stock_type_a" onkeyup="check_abc_stock_validation();" value="<?=$companyConfiguration['stock_type_a']?>" />
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-3 control-label">B Type Stock: </label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" name="stock_type_b" id="stock_type_b" onkeyup="check_abc_stock_validation();" value="<?=$companyConfiguration['stock_type_b']?>" />
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="col-md-3 control-label">C Type Stock: </label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" name="stock_type_c" id="stock_type_c" onkeyup="check_abc_stock_validation();" value="<?=$companyConfiguration['stock_type_c']?>" />
													</div>
												</div>
											</div>
											<div class="col-md-12" style="margin-top:20px">
												<div class="col-md-3"><label>Inventory Product Type :</label></div>
												<div class="col-md-6">
													<div class="form-group">
														<select class="select2" name="inventory_pro_type[]" id="inventory_pro_type" multiple data-placeholder="Inventory Product Type">
															<?= get_product_type_company($dbcon,$inventory_pro_type,''); ?>
														</select>
													</div>
												</div>
											</div>
										</div>
										<?php if($getspecialConfiguration['smpl_permission']==1){?>
											<div class="col-md-12" style="margin-top: 50px;">
												<div class="form-group">
													<label class="col-md-3 control-label"> QC heat no saperator</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" name="heat_no_saperator" id="heat_no_saperator"  value="<?=$companyConfiguration['heat_no_saperator']?>"  placeholder="qc heat no saperator" />
													</div>
												</div>	
											</div>

									<div class="col-md-12" style="margin-top: 10px;">												
										<div class="form-group">
											<label class="col-md-3 control-label">MFG Licence No</label>
											<div class="col-md-6 col-xs-11">
												<input type="text" class="form-control" placeholder="MFG Licence No" name="smpl_mfg_licence" id="smpl_mfg_licence"  value="<?=$rel['smpl_mfg_licence']?>" />
											</div>
										</div>
										</div>
										<?php }?>
									</div>

								</div>
								<div class="entr">
									<div class="row">
										<div class="col-md-4"><label>Show Old Dashbord  &nbsp;&nbsp;</label></div>
										<div class="col-md-4" ><input type="checkbox" class="" <?php echo (isset($rel['enable_old_dashbord']) && ($rel['enable_old_dashbord'])==1) ? "checked" : ''; ?> name="enable_old_dashbord"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable voucher approval  &nbsp;&nbsp;</label></div>
										<div class="col-md-4" ><input type="checkbox" class="" <?php echo (isset($rel['enable_voucher_approval']) && ($rel['enable_voucher_approval'])==1) ? "checked" : ''; ?> name="enable_voucher_approval"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable SMS &nbsp;&nbsp;</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_sms']) && ($rel['enable_sms'])==1) ? "checked" : ''; ?> name="enable_sms"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Enable Emails &nbsp;&nbsp;</label></div>
										<div class="col-md-4"><input type="checkbox" class="" <?php echo (isset($rel['enable_email']) && ($rel['enable_email'])==1) ? "checked" : ''; ?> name="enable_email"></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Daily Send Email*: &nbsp;&nbsp;</label></div>
										<div class="col-md-4">
											<div class="form-group">
												<div class="col-md-8">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel['send_email'] == 0){ echo "active";}?>">
															<input type="radio" name="send_email" id="send_email1" autocomplete="off" value="0" <?php if($rel['send_email'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($rel['send_email'] == 1){ echo "active";}?>" >
															<input type="radio" name="send_email" id="send_email2" autocomplete="off" value="1" <?php if($rel['send_email'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>
									<div class="row">
										<div class="col-md-4"><label>IP Address Wise Login ?*: &nbsp;&nbsp;</label></div>
										<div class="col-md-4">
											<div class="form-group">
												<div class="col-md-8">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel['ip_add_login'] == 0){ echo "active";}?>">
															<input type="radio" name="ip_add_login" id="ip_add_login1" autocomplete="off" value="0" <?php if($rel['ip_add_login'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($rel['ip_add_login'] == 1){ echo "active";}?>" >
															<input type="radio" name="ip_add_login" id="ip_add_login2" autocomplete="off" value="1" <?php if($rel['ip_add_login'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>
									<div class="row">
										<div class="col-md-4"><label>Send Mail Automatically*: &nbsp;&nbsp;</label></div>
										<div class="col-md-4">
											<div class="form-group">
												<div class="col-md-8">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($crm_auto_mail == 'No'){ echo "active";}?>">
															<input type="radio" name="crm_auto_mail" id="crm_auto_mail1" autocomplete="off" value="No" <?php if($crm_auto_mail == 'No'){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($crm_auto_mail == 'Yes'){ echo "active";}?>" >
															<input type="radio" name="crm_auto_mail" id="crm_auto_mail2" autocomplete="off" value="Yes" <?php if($crm_auto_mail == 'Yes'){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>

									<div class="row">
										<div class="col-md-4"><label>Send Email* : &nbsp;&nbsp;</label></div>
										<div class="col-md-6">
											<div class="form-group">
												<div class="col-md-12">
													<input type="text" class="form-control" placeholder="Send email" name="smtp_email" id="smtp_email"  value="<?=$rel2['smtp_email']?>"  title="Enter Email Address" />
												</div>
											</div>
										</div>
									</div><br>

									<div class="row">
										<div class="col-md-4"><label>Password*:&nbsp;&nbsp;</label></div>
										<div class="col-md-6">
											<div class="form-group">
												<div class="col-md-12">
													<input type="password" class="form-control" placeholder="Password" name="smtp_password" id="smtp_password"  value="<?=$rel2['smtp_password']?>" title="Enter Password" />
												</div>
											</div>
										</div>
									</div><br>

									<div class="row">
										<div class="col-md-4"><label>Admin Email Id* : &nbsp;&nbsp;</label></div>
										<div class="col-md-6">
											<div class="form-group">
												<div class="col-md-12">
													<input type="email" class="form-control" placeholder="Enter Admin Email" name="common_email_id" id="common_email_id"  value="<?=$rel2['common_email_id']?>"  title="Enter Admin Email" />
												</div>
											</div>
										</div>
									</div><br>
									
									
									<div class="row">
										<div class="col-md-12"><h4><strong>Sendinblue Setting: </strong></h4></div>
									</div>
									<div class="row">
										<div class="col-md-4"><label>Send Mail Id:&nbsp;&nbsp;</label></div>
										<div class="col-md-6">
											<div class="form-group">
												<div class="col-md-12">
													<input type="email" class="form-control" placeholder="Sendinblue mail id" name="sendinblue_mail_id" id="sendinblue_mail_id"  value="<?=$rel['sendinblue_mail_id']?>" title="Enter Sendinblue mail id" />
												</div>
												
											</div>
										</div>
									</div> </br>
									<div class="row">
										<div class="col-md-4"><label>Sendinblue API KEY:&nbsp;&nbsp;</label></div>
										<div class="col-md-6">
											<div class="form-group">
												<div class="col-md-12">
													<input type="text" class="form-control" placeholder="Sending blue API KEY" name="sending_blue_api_key" id="sending_blue_api_key"  value="<?=$rel['sending_blue_api_key']?>" title="Enter Sending blue API KEY" />
												</div>
												<div class="col-md-12">
													<a href="https://www.sendinblue.com/" target="_blank">Sendinblue Registration / Login </a>

												</div>
											</div>
										</div>
									</div>
									<div class="row row_margin">
										<label class="col-md-4 control-label">Branch Wise Manage: </label>
										<div class="col-md-6">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($companyConfiguration['branch_wise_manage'] == 0){ echo "active";}?>">
													<input type="radio" name="branch_wise_manage" id="branch_wise_manage1" autocomplete="off" value="0" <?php if($companyConfiguration['branch_wise_manage'] == 0){ echo "checked";}?> onchange="branch_wise_manages(this.value)"> No
												</label>
												<label class="btn btn-secondary <?php if($companyConfiguration['branch_wise_manage'] == 1){ echo "active";}?>" >
													<input type="radio" name="branch_wise_manage" id="branch_wise_manage2" autocomplete="off" value="1" <?php if($companyConfiguration['branch_wise_manage'] == 1){ echo"checked"; }?> onchange="branch_wise_manages(this.value)"> Yes
												</label>
											</div>
										</div>
									</div>
									<div class="row row_margin">
										<label class="col-md-4 control-label">Default Branch </label>
										<div class="col-md-6" id="branch_1" <?php if($companyConfiguration['branch_wise_manage'] == 0){ echo 'style="display:none"';}?>>
											
											<?php echo getBranchBox($dbcon, $branch_id, $companyConfiguration['default_branch_id'], false, false,'','4','8'); ?>
										
										</div>
										<div class="col-md-6" id="branch_0" <?php if($companyConfiguration['branch_wise_manage'] == 1){ echo 'style="display:none"';}?>>
											
											<?php echo getBranchBox($dbcon, $branch_id, $companyConfiguration['default_branch_id'], true, false,'','4','8'); ?>
										
										</div>
									</div>
								</div>

								<div class="apiset">
									<div class="col-md-12">
										<div class="card">
											<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
												<li role="presentation" id="tab1" class="active"><a href="#e_way_bill" aria-controls="e_way_bill" role="tab" data-toggle="tab">Eway Bill</a></li>
												<li role="presentation" id="tab4"><a href="#e_invoice_bill" aria-controls="e_invoice_bill" role="tab" data-toggle="tab" style="display: none;">E-Invoice</a></li>
												<li role="presentation" id="tab2"><a href="#trade_india" aria-controls="trade_india" role="tab" data-toggle="tab">Trade India</a></li>
												<li role="presentation" id="tab3"><a href="#india_mart" aria-controls="india_mart" role="tab" data-toggle="tab">India Mart</a></li>
												<li role="presentation" id="tab5"><a href="#whatsapp_config" aria-controls="whatsapp_config" role="tab" data-toggle="tab">Whatsapp Configration</a></li>
											</ul>
											<div class="tab-content"> 
												<div role="tabpanel" class="tab-pane active" id="e_way_bill">
													<div class="col-md-6">
														<div class="row">
															<div class="col-md-4"><label>Eway Bill  &nbsp&nbsp</label></div>
															<div class="col-md-4" ><input type="checkbox" class="enable_eway_bill" <?php echo (isset($rel['enable_eway_bill']) && ($rel['enable_eway_bill'])==1) ? "checked" : ''; ?> name="enable_eway_bill"></div>
														</div>
														<div class="row">
															<div class="col-md-4"><label>E-invoice &nbsp&nbsp</label></div>
															<div class="col-md-4"><input type="checkbox" class="enable_einvoice" <?php echo (isset($rel['enable_einvoice']) && ($rel['enable_einvoice'])==1) ? "checked" : ''; ?> name="enable_einvoice"></div>
														</div>
														<div class="row" style="display: none;">
															<div class="col-md-4"><label>GST Filling &nbsp&nbsp</label></div>
															<div class="col-md-4"><input type="checkbox" class="enable_gst_filling" <?php echo (isset($rel['enable_gst_filling']) && ($rel['enable_gst_filling'])==1) ? "checked" : ''; ?> name="enable_gst_filling"></div>
														</div>
													</div>
													<div class="col-md-6 gsp_set" >
														<div class="row">
															<label class="col-md-4 control-label">GSP UserName</label>
															<div class="col-md-8 col-xs-11">
																<input type="text" class="form-control" placeholder="GSP UserName" title="GSP UserName" name="gsp_username" id="gsp_username" value="<?=$rel['gsp_username']?>"  />
															</div>
														</div>
														<div class="row">
															<label class="col-md-4 control-label">GSP Password</label>
															<div class="col-md-8 col-xs-11">
																<input type="text" class="form-control" placeholder="GSP Password" title="GSP Password" name="gsp_password" id="gsp_password" value="<?=$rel['gsp_password']?>"  />
															</div>
														</div>
													</div>
													<div class="col-md-12" style="margin-top: 10px;display:none;">
														<div class="row">
															<label class="col-md-3 control-label">EWay Bill UserName</label>
															<div class="col-md-6 col-xs-11">
																<input type="text" class="form-control" placeholder="EWay Bill UserName" title="EWay Bill UserName" name="ewb_username" id="ewb_username" value="<?=$rel['ewb_username']?>"  />
															</div>
														</div>
													</div>
													<div class="col-md-12" style="margin-top: 10px;display:none;margin-bottom: 10px;">
														<div class="row">
															<label class="col-md-3 control-label">EWay Bill Password</label>
															<div class="col-md-6 col-xs-11">
																<input type="text" class="form-control" placeholder="EWay Bill Password" title="EWay Bill Password" name="ewb_password" id="ewb_password" value="<?=$rel['ewb_password']?>"  />
															</div>
														</div>
													</div>
												</div>
												<div role="tabpanel" class="tab-pane" id="e_invoice_bill" style="display:none;">
													<div class="col-md-12" style="margin-top: 10px;">
														<div class="row">
															<label class="col-md-3 control-label">E-Invoice UserName</label>
															<div class="col-md-6 col-xs-11">
																<input type="text" class="form-control" placeholder="E-Invoice UserName" title="E-Invoice UserName" name="einv_username" id="einv_username" value="<?=$rel['einv_username']?>"  />
															</div>
														</div>
													</div>
													<div class="col-md-12" style="margin-top: 10px;margin-bottom: 10px;">
														<div class="row">
															<label class="col-md-3 control-label">E-Invoice Password</label>
															<div class="col-md-6 col-xs-11">
																<input type="text" class="form-control" placeholder="E-Invoice Password" title="E-Invoice Password" name="einv_password" id="einv_password" value="<?=$rel['einv_password']?>"  />
															</div>
														</div>
													</div>
												</div>
												<div role="tabpanel" class="tab-pane" id="trade_india">
													<div class="row">
														<div class="col-sm-4">
															<section class="panel">
																<div class="panel-body">
																	<div class="form-group">
																		<label><strong>User Id</strong></label>
																		<input class="form-control" type='text' name='trade_india_user_id' id='trade_india_user_id' placeholder="User Id" value='' />
																	</div>
																	<div class="form-group">
																		<label><strong>Profile Id</strong></label>
																		<input class="form-control" type='text' name='trade_india_profile_id' id='trade_india_profile_id' placeholder="Profile Id" value='' />
																	</div>
																	<div class="form-group">
																		<label><strong>API Key</strong></label>
																		<input class="form-control" type='text' name='trad_india_api_key' id='trad_india_api_key' placeholder="API Key" value='' />
																	</div>
																	<div class="form-group">
																		<label><strong>Source</strong></label>
																		<select class="select2" id="source_tradeindia" name="source_tradeindia" >
																			<?=get_refer_by($dbcon,$rel['rb_id']);?>
																		</select>
																	</div>
																	<button type="button" onclick="add_trade_india()" class="btn btn-success">Submit</button>
																</div>
															</section>
														</div>

														<div class="col-sm-8">
															<section class="panel">
																<div class="panel-body">
																	<div class="adv-table">
																		<table class="display table table-bordered table-striped" id="trade_india_ta">
																			<thead>
																				<tr>
																					<th>Sr. NO.</th>
																					<th>User Id</th>
																					<th>Profile Id</th>
																					<th>API Key</th>
																					<th class="hidden-phone">Action</th>
																				</tr>
																			</thead>
																			<tbody>
																			</tbody>
																		</table>
																	</div>
																</div>
															</section>
														</div>
													</div>
												</div>

												<div role="tabpanel" class="tab-pane" id="india_mart">
													<div class="row">
														<div class="col-sm-4">
															<section class="panel">
																<div class="panel-body">
																	<div class="form-group">
																		<label><strong>Mobile No</strong></label>
																		<input class="form-control" type='text' name='mobile_no' id='mobile_no' placeholder="Mobile No" value='' />
																	</div>
																	<div class="form-group">
																		<label><strong>API Key</strong></label>
																		<input class="form-control" type='text' name='api_key' id='api_key' placeholder="API Key" value='' />
																	</div>
																	<div class="form-group">
																		<label><strong>Source</strong></label>
																		<select class="select2" id="source_indiamart" name="source_indiamart" >
																			<?=get_refer_by($dbcon,$rel['rb_id']);?>
																		</select>
																	</div>
																	<button type="button" class="btn btn-success" onclick="add_india_mart()">Submit</button>
																</div>
															</section>
														</div>

														<div class="col-sm-8">
															<div class="col-sm-12">
																<section class="panel">
																	<div class="panel-body">
																		<div class="adv-table">
																			<table class="display table table-bordered table-striped" id="india_mart_ta">
																				<thead>
																					<tr>
																						<th>Sr. NO.</th>
																						<!-- <th>Source</th> -->
																						<th>Mobile No</th>
																						<th>API Key</th>
																						<th class="hidden-phone">Action</th>
																					</tr>
																				</thead>
																				<tbody></tbody>
																			</table>
																		</div>
																	</div>
																</section>
															</div>
														</div>
													</div>
												</div>

												<div role="tabpanel" class="tab-pane" id="whatsapp_config">
													<div class="col-md-12 gsp_set" >
														<div class="row form-group">
															<label class="col-md-2 control-label">Enable Whatsapp: </label>
															<div class="col-md-8 col-xs-11">
																<div class="btn-group btn-group-toggle" data-toggle="buttons">
																	<label class="btn btn-secondary <?php if($companyConfiguration['enable_whatsapp'] == 0){ echo "active";}?>">
																		<input type="radio" name="enable_whatsapp" id="enable_whatsapp" autocomplete="off" value="0" <?php if($companyConfiguration['enable_whatsapp'] == 0){ echo "checked";}?>  > No
																	</label>
																	<label class="btn btn-secondary <?php if($companyConfiguration['enable_whatsapp'] == 1){ echo "active";}?>" >
																		<input type="radio" name="enable_whatsapp" id="enable_whatsapp1" autocomplete="off" value="1" <?php if($companyConfiguration['enable_whatsapp'] == 1){ echo"checked"; }?>> Yes
																	</label>
																</div>
															</div>
														</div>
														<div class="row form-group">
															<label class="col-md-2 control-label">URL</label>
															<div class="col-md-8 col-xs-11">
																<input type="text" class="form-control" placeholder="API URL" title="Whatsapp API Url" name="whatsapp_url" id="whatsapp_url" value="<?=$rel['whatsapp_api_url']?>"  />
															</div>
														</div>
														<div class="row form-group">
															<label class="col-md-2 control-label">Key</label>
															<div class="col-md-8 col-xs-11">
																<input type="text" class="form-control" placeholder="Whatsapp Key" title="Whatsapp Key" name="whatsapp_key" id="whatsapp_key" value="<?=$rel['whatsapp_api_key']?>"  />
															</div>
														</div>
														<div class="row form-group">
															<label class="col-md-2 control-label">Default Template</label>
															<div class="col-md-8 col-xs-11">
																<input type="text" class="form-control" placeholder="Whatsapp Default Template" title="Whatsapp Default Template" name="whatsapp_template" id="whatsapp_template" value="<?=$rel['whatsapp_template']?>"  />
															</div>
														</div>
														<button type="button" onclick="add_whatsapp_confgure()" class="btn btn-success">Submit</button>
													</div>
												</div>

											</div>
										</div>
									</div>
								</div>
								<div class="finance_year">
									<div class="pull-right">
										<button accesskey="f" style="" class="btn btn-round btn-info" title="Short-Cut To Open PopUp, Shift + Alt + f " type="button" data-toggle="modal" value="R1" onclick="change_year();">Year Change</button>
									</div>
									<div class="row row_margin">
										<div class="col-md-12 adv-table">
											<table class="display table table-bordered table-striped" id="financial_year_tble">
												<thead>
													<tr>
														<th>Sr. NO.</th>
														<th>Type</th>
														<th>Year</th>
														<th>Start-End Date</th>
														<th>Status</th>
														<th class="hidden-phone">Action</th>
													</tr>
												</thead>
												<tbody></tbody>
											</table>
										</div>
									</div>
									<div style="display: none;" >
									<h3>Financial Year</h3>
									<div class="row row_margin">
										<div class="col-md-4"><label>Select Financial Year</label></div>
										<div class="col-md-4">
											<select class="form-control" name="finance_year_type" id="finance_year_type" onchange="get_finance_year(this.value)">
												<option value="1" <?=$rel1['finance_year_type']=='1'?'selected':''?>>March To April</option>
												<option value="2" <?=$rel1['finance_year_type']=='2'?'selected':''?>>January To December</option>
											</select>
										</div>
									</div>
									<div class="row row_margin">
										<div class="col-md-4"><label>Start-End Date</label></div>
										<div class="col-md-4">
											<input type="text" class="form-control default_date" name="financial_start_date" id="financial_start_date" value="<?=date("d-m-Y",strtotime($rel1['financial_start_date']));?>" />
										</div>
										<!-- <div class="col-md-4"><label>End Date</label></div> -->
										<div class="col-md-4">
											<input type="text" class="form-control default_date" name="financial_end_date" id="financial_end_date" value="<?=date("d-m-Y",strtotime($rel1['financial_end_date']));?>" />
										</div>
									</div>
									<div class="row row_margin">
										<div class="col-md-4"><label>Financial Year</label></div>
										<div class="col-md-4">
											<input type="text" class="form-control" name="fiancial_year" id="fiancial_year" value="<?=$rel1['fiancial_year'];?>" readonly/>
										</div>
									</div>
									<div class="row row_margin">
										<div class="col-md-4"></div>
										<div class="col-md-4">
											<button type="button" onclick="add_new_fyear()" class="btn btn-success" id="fyearbtn">Add New Financial Year</button>
										</div>
									</div>
								</div>
								</div>
								<!--START JAYESH DESIG DEPARTMENT-->
								<div class="designdepartment">
									<div class="row">
										<div class="col-md-4"><label>Design Department &nbsp&nbsp</label></div>
										<div class="col-md-4" ><input type="checkbox" class="design_department" <?php echo (isset($rel['design_department']) && ($rel['design_department'])==1) ? "checked" : ''; ?> name="design_department"></div>
									</div>

									<div class="row">
										<div class="col-md-4"><label>SO Customization?</label></div>
										<div class="col-md-4"><input type="checkbox" class="design_so_customization" <?php echo (isset($rel['design_so_customization']) && ($rel['design_so_customization'])==1) ? "checked" : ''; ?> name="design_so_customization"></div>
									</div>

									<div class="row">
										<div class="col-md-4"><label>Are you working with project wise manufacturing?*: </label></div>
										<div class="col-md-4">
											<div class="form-group">
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($project_wise_manufacturing == 'No'){ echo "active";}?>">
															<input type="radio" name="project_wise_manufacturing" id="project_wise_manufacturing1" autocomplete="off" value="No" <?php if($project_wise_manufacturing == 'No'){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($project_wise_manufacturing == 'Yes'){ echo "active";}?>" >
															<input type="radio" name="project_wise_manufacturing" id="project_wise_manufacturing2" autocomplete="off" value="Yes" <?php if($project_wise_manufacturing == 'Yes'){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-4"><label>GRN time upload receipt field Mandetory?*: </label></div>
										<div class="col-md-4">
											<div class="form-group">
												<div class="btn-group btn-group-toggle" data-toggle="buttons">
													<label class="btn btn-secondary <?php if($upload_receipt == 'No'){ echo "active";}?>">
														<input type="radio" name="upload_receipt" id="upload_receipt1" autocomplete="off" value="No" <?php if($upload_receipt == 'No'){ echo "checked";}?>  > No
													</label>
													<label class="btn btn-secondary <?php if($upload_receipt == 'Yes'){ echo "active";}?>" >
														<input type="radio" name="upload_receipt" id="upload_receipt2" autocomplete="off" value="Yes" <?php if($upload_receipt == 'Yes'){ echo"checked"; }?>> Yes
													</label>
												</div>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-4"><label>QC time upload receipt field Mandetory?*: </label></div>
										<div class="col-md-4">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($qc_upload_receipt == 'No'){ echo "active";}?>">
													<input type="radio" name="qc_upload_receipt" id="qc_upload_receipt1" autocomplete="off" value="No" <?php if($qc_upload_receipt == 'No'){ echo "checked";}?>  > No
												</label>
												<label class="btn btn-secondary <?php if($qc_upload_receipt == 'Yes'){ echo "active";}?>" >
													<input type="radio" name="qc_upload_receipt" id="qc_upload_receipt2" autocomplete="off" value="Yes" <?php if($qc_upload_receipt == 'Yes'){ echo"checked"; }?>> Yes
												</label>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-4"><label>Are you want to display the project wise item rate?*: </label></div>
										<div class="col-md-4">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($project_wise_item_rate == 'No'){ echo "active";}?>">
													<input type="radio" name="project_wise_item_rate" id="project_wise_item_rate1" autocomplete="off" value="No" <?php if($project_wise_item_rate == 'No'){ echo "checked";}?>  > No
												</label>
												<label class="btn btn-secondary <?php if($project_wise_item_rate == 'Yes'){ echo "active";}?>" >
													<input type="radio" name="project_wise_item_rate" id="project_wise_item_rate2" autocomplete="off" value="Yes" <?php if($project_wise_item_rate == 'Yes'){ echo"checked"; }?>> Yes
												</label>
											</div>
										</div>
									</div>

									
									<div class="row">
										<div class="col-md-4"><label>Outside Jobwork?* :</label></div>
										<div class="col-md-4">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($companyConfiguration['outside_jobwork'] == 0){ echo "active";}?>">
													<input type="radio" name="outside_jobwork" id="outside_jobwork1" autocomplete="off" value="0" <?php if($companyConfiguration['outside_jobwork'] == 0){ echo "checked";}?>  > NO
												</label>
												<label class="btn btn-secondary <?php if($companyConfiguration['outside_jobwork'] == 1){ echo "active";}?>" >
													<input type="radio" name="outside_jobwork" id="outside_jobwork2" autocomplete="off" value="1" <?php if($companyConfiguration['outside_jobwork'] == 1){ echo"checked"; }?>> Yes
												</label>
											</div>
										</div>

									</div><br>
									
									
									<div class="row">
										<div class="col-md-4"><label>AutoMrp Display?* :</label></div>
										<div class="col-md-4">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($companyConfiguration['automrp_display'] == 0){ echo "active";}?>">
													<input type="radio" name="automrp_display" id="automrp_display1" autocomplete="off" value="0" <?php if($companyConfiguration['automrp_display'] == 0){ echo "checked";}?>  > NO
												</label>
												<label class="btn btn-secondary <?php if($companyConfiguration['automrp_display'] == 1){ echo "active";}?>" >
													<input type="radio" name="automrp_display" id="automrp_display2" autocomplete="off" value="1" <?php if($companyConfiguration['automrp_display'] == 1){ echo"checked"; }?>> Yes
												</label>
											</div>
										</div>

									</div><br>
									
									
									<div class="row">
										<div class="col-md-4"><label>Production Start Type?* :</label></div>
										<div class="col-md-4">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($companyConfiguration['production_start_type'] == 0){ echo "active";}?>">
													<input type="radio" name="production_start_type" id="production_start_type1" autocomplete="off" value="0" <?php if($companyConfiguration['production_start_type'] == 0){ echo "checked";}?>  > Manually
												</label>
												<label class="btn btn-secondary <?php if($companyConfiguration['production_start_type'] == 1){ echo "active";}?>" >
													<input type="radio" name="production_start_type" id="production_start_type2" autocomplete="off" value="1" <?php if($companyConfiguration['production_start_type'] == 1){ echo"checked"; }?>> FIFO Wise
												</label>
											</div>
										</div>
									</div>
								</br>
								<div class="row">
										<div class="col-md-4"><label>Store Recive Only first Process* :</label></div>
										<div class="col-md-4">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($companyConfiguration['store_relese_first_process'] == 0){ echo "active";}?>">
													<input type="radio" name="store_relese_first_process" id="automrp_display1" autocomplete="off" value="0" <?php if($companyConfiguration['store_relese_first_process'] == 0){ echo "checked";}?>  > NO
												</label>
												<label class="btn btn-secondary <?php if($companyConfiguration['store_relese_first_process'] == 1){ echo "active";}?>" >
													<input type="radio" name="store_relese_first_process" id="automrp_display2" autocomplete="off" value="1" <?php if($companyConfiguration['store_relese_first_process'] == 1){ echo"checked"; }?>> Yes
												</label>
											</div>
										</div>

									</div><br>

								<div class="row">
									<div class="col-md-4">
										<label>Workorder Planning?* :  <a href="#"  data-original-title="This setting is for product workorder planning." data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></i></a></label>
									</div>
									<div class="col-md-8">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($companyConfiguration['workorder_planning'] == 0){ echo "active";}?>" data-original-title="Set All for create one workorder for all Quantity" data-toggle="tooltip" data-placement="top">
												<input type="radio" name="workorder_planning" id="workorder_planning1" autocomplete="off" value="0" <?php if($companyConfiguration['workorder_planning'] == 0){ echo "checked";}?>  > All
											</label>
											<label class="btn btn-secondary <?php if($companyConfiguration['workorder_planning'] == 1){ echo "active";}?>" data-original-title="Set Single for create single single workorder for all Quantity" data-toggle="tooltip" data-placement="top">
												<input type="radio" name="workorder_planning" id="workorder_planning2" autocomplete="off" value="1" <?php if($companyConfiguration['workorder_planning'] == 1){ echo"checked"; }?>> Single
											</label>
										</div>
									</div>
								</div>
							</br>
							<div class="row">
								<div class="col-md-4">
									<label>Auto MRP Run ?* :  <a href="#"  data-original-title="This setting is for Automatic MRP Run." data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></i></a></label>
								</div>
								<div class="col-md-8">
									<div class="btn-group btn-group-toggle" data-toggle="buttons">
										<label class="btn btn-secondary <?php if($companyConfiguration['workorder_planning'] == 0){ echo "active";}?>" data-original-title="Set All for create one workorder for all Quantity" data-toggle="tooltip" data-placement="top">
											<input type="radio" name="workorder_planning" id="workorder_planning1" autocomplete="off" value="0" <?php if($companyConfiguration['workorder_planning'] == 0){ echo "checked";}?>  > All
										</label>
										<label class="btn btn-secondary <?php if($companyConfiguration['workorder_planning'] == 1){ echo "active";}?>" data-original-title="Set Single for create single single workorder for all Quantity" data-toggle="tooltip" data-placement="top">
											<input type="radio" name="workorder_planning" id="workorder_planning2" autocomplete="off" value="1" <?php if($companyConfiguration['workorder_planning'] == 1){ echo"checked"; }?>> Single
										</label>
									</div>
								</div>
							</div>
						</br>
						<div class="row">
							<div class="col-md-4"><label>Production Start & Stop Timing?* :</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['production_start_stop_time'] == 0){ echo "active";}?>">
										<input type="radio" name="production_start_stop_time" id="production_start_stop_time1" autocomplete="off" value="0" <?php if($companyConfiguration['production_start_stop_time'] == 0){ echo "checked";}?>  > Automatic
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['production_start_stop_time'] == 1){ echo "active";}?>" >
										<input type="radio" name="production_start_stop_time" id="production_start_stop_time2" autocomplete="off" value="1" <?php if($companyConfiguration['production_start_stop_time'] == 1){ echo"checked"; }?>> Manually
									</label>
								</div>
							</div>
						</div>
					</br>
					<div class="row">
										<div class="col-md-4"><label>Resource Wise Production?* :</label></div>
										<div class="col-md-4">
											<div class="btn-group btn-group-toggle" data-toggle="buttons">
												<label class="btn btn-secondary <?php if($companyConfiguration['resource_wise_production'] == 0){ echo "active";}?>">
													<input type="radio" name="resource_wise_production" id="resource_wise_production1" autocomplete="off" value="0" <?php if($companyConfiguration['resource_wise_production'] == 0){ echo "checked";}?>  > NO
												</label>
												<label class="btn btn-secondary <?php if($companyConfiguration['resource_wise_production'] == 1){ echo "active";}?>" >
													<input type="radio" name="resource_wise_production" id="resource_wise_production2" autocomplete="off" value="1" <?php if($companyConfiguration['resource_wise_production'] == 1){ echo"checked"; }?>> Yes
												</label>
											</div>
										</div>

									</div><br>
					<div class="row">
						<div class="col-md-4"><label>Roundup Quantity?* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['round_up_qty'] == 0){ echo "active";}?>">
									<input type="radio" name="round_up_qty" id="round_up_qty1" autocomplete="off" value="0" <?php if($companyConfiguration['round_up_qty'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['round_up_qty'] == 1){ echo "active";}?>" >
									<input type="radio" name="round_up_qty" id="round_up_qty2" autocomplete="off" value="1" <?php if($companyConfiguration['round_up_qty'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>		
					<div class="row">
						<div class="col-md-4"><label>Workorder wise production merge?* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['workorder_wise_production_merge'] == 1){ echo "active";}?>">
									<input type="radio" name="workorder_wise_production_merge" id="workorder_wise_production_merge1" autocomplete="off" value="1" <?php if($companyConfiguration['workorder_wise_production_merge'] == 1){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['workorder_wise_production_merge'] == 0){ echo "active";}?>" >
									<input type="radio" name="workorder_wise_production_merge" id="workorder_wise_production_merge2" autocomplete="off" value="0" <?php if($companyConfiguration['workorder_wise_production_merge'] == 0){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<div class="row">
						<div class="col-md-4"><label>Process END Time QC?* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['process_end_time_qc'] == 0){ echo "active";}?>">
									<input type="radio" name="process_end_time_qc" id="process_end_time_qc1" autocomplete="off" value="0" <?php if($companyConfiguration['process_end_time_qc'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['process_end_time_qc'] == 1){ echo "active";}?>" >
									<input type="radio" name="process_end_time_qc" id="process_end_time_qc2" autocomplete="off" value="1" <?php if($companyConfiguration['process_end_time_qc'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>	
					<div class="row">
						<div class="col-md-4"><label>Extra Stock?* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['extra_stock'] == 0){ echo "active";}?>">
									<input type="radio" name="extra_stock" id="extra_stock1" autocomplete="off" value="0" <?php if($companyConfiguration['extra_stock'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['extra_stock'] == 1){ echo "active";}?>" >
									<input type="radio" name="extra_stock" id="extra_stock2" autocomplete="off" value="1" <?php if($companyConfiguration['extra_stock'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>	
					<div class="row">
						<div class="col-md-4"><label>BOM Extra NO.?* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['bom_extra_no'] == 0){ echo "active";}?>">
									<input type="radio" name="bom_extra_no" id="bom_extra_no1" autocomplete="off" value="0" <?php if($companyConfiguration['bom_extra_no'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['bom_extra_no'] == 1){ echo "active";}?>" >
									<input type="radio" name="bom_extra_no" id="bom_extra_no2" autocomplete="off" value="1" <?php if($companyConfiguration['bom_extra_no'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>	
					<div class="row">
						<div class="col-md-4"><label>GRN Time Supplier TC.?* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['supplier_tc_no'] == 0){ echo "active";}?>">
									<input type="radio" name="supplier_tc_no" id="supplier_tc_no1" autocomplete="off" value="0" <?php if($companyConfiguration['supplier_tc_no'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['supplier_tc_no'] == 1){ echo "active";}?>" >
									<input type="radio" name="supplier_tc_no" id="supplier_tc_no2" autocomplete="off" value="1" <?php if($companyConfiguration['supplier_tc_no'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<div class="row">
						<div class="col-md-4"><label>Workorder Batch Wise Stock Allocation* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['wo_bw_alloc_stock'] == 0){ echo "active";}?>">
									<input type="radio" name="wo_bw_alloc_stock" id="wo_bw_alloc_stock1" autocomplete="off" onchange="toggle_store_approval()" value="0" <?php if($companyConfiguration['wo_bw_alloc_stock'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['wo_bw_alloc_stock'] == 1){ echo "active";}?>" >
									<input type="radio" onchange="toggle_store_approval()" name="wo_bw_alloc_stock" id="wo_bw_alloc_stock2" autocomplete="off" value="1" <?php if($companyConfiguration['wo_bw_alloc_stock'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<div class="row">
						<div class="col-md-4"><label>Show Customer IN Production* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['customer_show_in_production'] == 0){ echo "active";}?>">
									<input type="radio" name="customer_show_in_production" id="customer_show_in_production1" autocomplete="off" value="0" <?php if($companyConfiguration['customer_show_in_production'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['customer_show_in_production'] == 1){ echo "active";}?>" >
									<input type="radio" name="customer_show_in_production" id="customer_show_in_production2" autocomplete="off" value="1" <?php if($companyConfiguration['customer_show_in_production'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<div class="row">
						<div class="col-md-4"><label>Production On Dashboard* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['production_on_dashboard'] == 0){ echo "active";}?>">
									<input type="radio" name="production_on_dashboard" id="production_on_dashboard1" autocomplete="off" value="0" <?php if($companyConfiguration['production_on_dashboard'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['production_on_dashboard'] == 1){ echo "active";}?>" >
									<input type="radio" name="production_on_dashboard" id="production_on_dashboard2" autocomplete="off" value="1" <?php if($companyConfiguration['production_on_dashboard'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<?php if($getspecialConfiguration['austar_permission']==1){?>
					<div class="row">
						<div class="col-md-4"><label>Fix Reserve Godown* :</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['set_reserve_godown'] == 0){ echo "active";}?>">
									<input type="radio" name="set_reserve_godown" id="set_reserve_godown1" autocomplete="off" onchange="toggle_default_godown()" value="0" <?php if($companyConfiguration['set_reserve_godown'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['set_reserve_godown'] == 1){ echo "active";}?>" >
									<input type="radio" name="set_reserve_godown" id="set_reserve_godown2" autocomplete="off" onchange="toggle_default_godown()" value="1" <?php if($companyConfiguration['set_reserve_godown'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<div class="row def_gd_row" style="display: <?php if($companyConfiguration['default_godown_id'] == 0){ echo "none";}else{ echo "block";}?>;">
						<div class="col-md-4"><label>Default Godown* :</label></div>
						<div class="col-md-4">
							<select class="select2" name="default_godown_id" id="default_godown_id"  data-placeholder="Default Godown">
									<?= get_last_node_godown_list($dbcon,$companyConfiguration['default_godown_id'] ); ?>
							</select>
						</div>
					</div><br>

				<?php}?>
					<div class="row">
						<div class="col-md-4"><label>Production Product Type :</label></div>
						<div class="col-md-8">
							<div class="form-group">
								<select class="select2" name="production_pro_type[]" id="production_pro_type" multiple data-placeholder="Production Product Type">
									<?= get_product_type_company($dbcon,$production_pro_type,''); ?>
								</select>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<h4><strong>Product Searching: </strong></h4>
						</div>

						<div class="col-md-12">
							<div class="col-md-4">
								<label class="col-md-4 control-label">Production</label>
							</div>
							<div class="col-md-8">
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="production_pro_search[]" value="item" <?=(in_array('item',$production_pro_search)) ? "checked" : ""; ?>> Item No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="production_pro_search[]" value="drawing" <?=(in_array('drawing',$production_pro_search)) ? "checked" : ""; ?>> Drawing No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="production_pro_search[]" value="alias" <?=(in_array('alias',$production_pro_search)) ? "checked" : ""; ?>> Alias
								</div>
							</div>
						</div>

						<div class="col-md-12">
							<div class="col-md-4">
								<label class="col-md-12 control-label">BOM / Stock Report</label>
							</div>
							<div class="col-md-8">	
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="bom_pro_search[]" value="item" <?=(in_array('item',$bom_pro_search)) ? "checked" : ""; ?>> Item No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="bom_pro_search[]" value="drawing" <?=(in_array('drawing',$bom_pro_search)) ? "checked" : ""; ?>> Drawing No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="bom_pro_search[]" value="alias" <?=(in_array('alias',$production_pro_search)) ? "checked" : ""; ?>> Alias
								</div>
							</div>
						</div>

					</div>					

				</div>
				<div class="print_set">
					<div class="row">
						<div class="col-md-12">
							<h4><strong>Print time product setting: </strong></h4>
						</div>
						<div class="col-md-12">
							<div class="col-md-4">
								<label class="col-md-4 control-label">CRM</label>
							</div>
							<div class="col-md-8">
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="crm_pro_print[]" value="item" <?=(in_array('item',$crm_pro_print)) ? "checked" : ""; ?>> Item No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="crm_pro_print[]" value="drawing" <?=(in_array('drawing',$crm_pro_print)) ? "checked" : ""; ?>> Drawing No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="crm_pro_print[]" value="alias" <?=(in_array('alias',$crm_pro_print)) ? "checked" : ""; ?>> Alias
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div class="col-md-4">
								<label class="col-md-12 control-label">Sales</label>
							</div>
							<div class="col-md-8">	
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="sales_pro_print[]" value="item" <?=(in_array('item',$sales_pro_print)) ? "checked" : ""; ?>> Item No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="sales_pro_print[]" value="drawing" <?=(in_array('drawing',$sales_pro_print)) ? "checked" : ""; ?>> Drawing No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="sales_pro_print[]" value="alias" <?=(in_array('alias',$sales_pro_print)) ? "checked" : ""; ?>> Alias
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div class="col-md-4">
								<label class="col-md-4 control-label">Purchase</label>
							</div>
							<div class="col-md-8">
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="purchase_pro_print[]" value="item" <?=(in_array('item',$purchase_pro_print)) ? "checked" : ""; ?>> Item No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="purchase_pro_print[]" value="drawing" <?=(in_array('drawing',$purchase_pro_print)) ? "checked" : ""; ?>> Drawing No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="purchase_pro_print[]" value="alias" <?=(in_array('alias',$purchase_pro_print)) ? "checked" : ""; ?>> Alias
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div class="col-md-4">
								<label class="col-md-4 control-label">Production</label>
							</div>
							<div class="col-md-8">
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="production_pro_print[]" value="item" <?=(in_array('item',$production_pro_print)) ? "checked" : ""; ?>> Item No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="production_pro_print[]" value="drawing" <?=(in_array('drawing',$production_pro_print)) ? "checked" : ""; ?>> Drawing No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="production_pro_print[]" value="alias" <?=(in_array('alias',$production_pro_print)) ? "checked" : ""; ?>> Alias
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div class="col-md-4">
								<label class="col-md-12 control-label">BOM / Stock Report</label>
							</div>
							<div class="col-md-8">	
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="bom_pro_print[]" value="item" <?=(in_array('item',$bom_pro_print)) ? "checked" : ""; ?>> Item No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="bom_pro_print[]" value="drawing" <?=(in_array('drawing',$bom_pro_print)) ? "checked" : ""; ?>> Drawing No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="bom_pro_print[]" value="alias" <?=(in_array('alias',$bom_pro_print)) ? "checked" : ""; ?>> Alias
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 row_margin">
							<div class="col-md-8"><label><strong>Item Description ?</strong></label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['enable_item_description'] == 0){ echo "active";}?>">
										<input type="radio" name="enable_item_description" id="enable_item_description" autocomplete="off" value="0" <?php if($companyConfiguration['enable_item_description'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['enable_item_description'] == 1){ echo "active";}?>" >
										<input type="radio" name="enable_item_description" id="enable_item_description" autocomplete="off" value="1" <?php if($companyConfiguration['enable_item_description'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-6 row_margin">
							<div class="col-md-8"><label><strong>BOM Item Image Show ?</strong></label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['enable_item_image'] == 0){ echo "active";}?>">
										<input type="radio" name="enable_item_image" id="enable_item_image" autocomplete="off" value="0" <?php if($companyConfiguration['enable_item_image'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['enable_item_image'] == 1){ echo "active";}?>" >
										<input type="radio" name="enable_item_image" id="enable_item_image" autocomplete="off" value="1" <?php if($companyConfiguration['enable_item_image'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-6 row_margin">
							<div class="col-md-8"><label><strong>CRM Print - with letterpad ?</strong></label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['crm_print_letterhead_per'] == 0){ echo "active";}?>">
										<input type="radio" name="crm_print_letterhead_per" id="crm_print_letterhead_per" autocomplete="off" value="0" <?php if($companyConfiguration['crm_print_letterhead_per'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['crm_print_letterhead_per'] == 1){ echo "active";}?>" >
										<input type="radio" name="crm_print_letterhead_per" id="crm_print_letterhead_per" autocomplete="off" value="1" <?php if($companyConfiguration['crm_print_letterhead_per'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-6 row_margin">
							<div class="col-md-8"><label><strong>Sales Print - with letterpad ?</strong></label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['sales_print_letterhead_per'] == 0){ echo "active";}?>">
										<input type="radio" name="sales_print_letterhead_per" id="sales_print_letterhead_per" autocomplete="off" value="0" <?php if($companyConfiguration['sales_print_letterhead_per'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['sales_print_letterhead_per'] == 1){ echo "active";}?>" >
										<input type="radio" name="sales_print_letterhead_per" id="sales_print_letterhead_per" autocomplete="off" value="1" <?php if($companyConfiguration['sales_print_letterhead_per'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-6 row_margin">
							<div class="col-md-8"><label><strong>Purchase Print - with letterpad ?</strong></label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['purchase_print_letterhead_per'] == 0){ echo "active";}?>">
										<input type="radio" name="purchase_print_letterhead_per" id="purchase_print_letterhead_per" autocomplete="off" value="0" <?php if($companyConfiguration['purchase_print_letterhead_per'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['purchase_print_letterhead_per'] == 1){ echo "active";}?>" >
										<input type="radio" name="purchase_print_letterhead_per" id="purchase_print_letterhead_per" autocomplete="off" value="1" <?php if($companyConfiguration['purchase_print_letterhead_per'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-6 row_margin">
							<div class="col-md-8"><label><strong>Finance Print - with letterpad ?</strong></label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['finance_print_letterhead_per'] == 0){ echo "active";}?>">
										<input type="radio" name="finance_print_letterhead_per" id="finance_print_letterhead_per" autocomplete="off" value="0" <?php if($companyConfiguration['finance_print_letterhead_per'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['finance_print_letterhead_per'] == 1){ echo "active";}?>" >
										<input type="radio" name="finance_print_letterhead_per" id="finance_print_letterhead_per" autocomplete="off" value="1" <?php if($companyConfiguration['finance_print_letterhead_per'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-6 row_margin">
							<div class="col-md-8"><label><strong>Production Print - with letterpad ?</strong></label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['production_print_letterhead_per'] == 0){ echo "active";}?>">
										<input type="radio" name="production_print_letterhead_per" id="production_print_letterhead_per" autocomplete="off" value="0" <?php if($companyConfiguration['production_print_letterhead_per'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['production_print_letterhead_per'] == 1){ echo "active";}?>" >
										<input type="radio" name="production_print_letterhead_per" id="production_print_letterhead_per" autocomplete="off" value="1" <?php if($companyConfiguration['production_print_letterhead_per'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Letter Head Top Margin: </strong></label></div>
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" class="form-control" placeholder="PO Terms & Conditions" name="letter_head_top_margin" id="letter_head_top_margin" value="<?=$rel2['letter_head_top_margin']?>"/>
								</div>
							</div>
						</div>
						<div class="col-md-3"><label style="white-space: nowrap;"><strong>Letter Head Bottom Margin: </strong></label></div>
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" class="form-control" placeholder="PO Terms & Conditions" name="letter_head_bottom_margin" id="letter_head_bottom_margin" value="<?=$rel2['letter_head_bottom_margin']?>"/>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Letter Head Left Margin: </strong></label></div>
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" class="form-control" placeholder="PO Terms & Conditions" name="letter_head_left_margin" id="letter_head_left_margin" value="<?=$rel2['letter_head_left_margin']?>"/>
								</div>
							</div>
						</div>
						<div class="col-md-3"><label><strong>Letter Head Right Margin: </strong></label></div>
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" class="form-control" placeholder="PO Terms & Conditions" name="letter_head_right_margin" id="letter_head_right_margin" value="<?=$rel2['letter_head_right_margin']?>"/>
								</div>
							</div>
						</div>
					</div>

					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Header logo Height: </strong></label></div>
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" class="form-control" placeholder="Height" name="header_logo_height" id="header_logo_height" value="<?=$rel2['header_logo_height']?>"/>
								</div>
							</div>
						</div>
						<div class="col-md-3"><label><strong>Header logo Width: </strong></label></div>
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" class="form-control" placeholder="Width" name="header_logo_width" id="header_logo_width" value="<?=$rel2['header_logo_width']?>"/>
								</div>
							</div>
						</div>
					</div>

					<div class="row row_margin">
						<div class="form-group">
							<label class="col-md-3 control-label">Label Print Process*</label>
							<div class="col-md-9 col-xs-11">
								<select class="select2" name="label_print_process_id" id="label_print_process_id" >
									<?=get_all_process($dbcon, $rel['label_print_process_id'])?>
								</select>
							</div>
						</div>
					</div>
					<!--////////////////////////////////////////////SMPL Specail Changes Start - Harashil////////////////////-->
					<?php if($getspecialConfiguration['smpl_permission'] ==1)
				{?>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>SMPL Batch PreFix: </strong></label></div>
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" class="form-control" placeholder="SMPL Batch PreFix" name="smpl_batch_prefix" id="smpl_batch_prefix" value="<?=$rel['smpl_batch_prefix']?>"/>
								</div>
							</div>
						</div>
					</div>

					<div class="row row_margin">
						<div class="col-md-3"><label><strong>D.L.No : </strong></label></div>
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" class="form-control" placeholder="D.L.No" name="smpl_dl_no" id="smpl_dl_no" value="<?=$rel['smpl_dl_no']?>"/>
								</div>
							</div>
						</div>
					</div>
				<?php}?>
					<!--////////////////////////////////////////////SMPL Specail Changes End - Harashil////////////////////-->
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>PO Terms & Conditions: </strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<textarea class="form-control" placeholder="PO Terms & Conditions" name="po_terms_conditions" id="po_terms_conditions" ><?=$companyConfiguration['po_terms_conditions']?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Quotation Header Greeting* :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<textarea class="form-control" placeholder="Quotation Header Greeting" name="quotation_print_content" id="quotation_print_content" ><?= stripslashes($quotation_print_content)?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Quotation Footer Greeting* :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<textarea class="form-control" placeholder="Quotation Footer Greeting" name="quotation_footer_content" id="quotation_footer_content" ><?= stripslashes($quotation_footer_content)?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Header Logo:</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<label class="radio-inline"><input type="radio" name="header_logo" id="header_logo0" <?=($companyConfiguration['header_logo']==0) ? "checked" : ""?> value="0" onchange="getheaderlayout(this.value,'1')">None</label>
									<label class="radio-inline"><input type="radio" name="header_logo" id="header_logo1" <?=($companyConfiguration['header_logo']==1) ? "checked" : ""?> value="1" onchange="getheaderlayout(this.value,'1')">Left</label>
									<label class="radio-inline"><input type="radio" name="header_logo" id="header_logo2" <?=($companyConfiguration['header_logo']==2) ? "checked" : ""?> value="2" onchange="getheaderlayout(this.value,'1')">Right</label>
									<label class="radio-inline"><input type="radio" name="header_logo" id="header_logo3" <?=($companyConfiguration['header_logo']==3) ? "checked" : ""?> value="3" onchange="getheaderlayout(this.value,'1')">All</label>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Header Text:</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<label class="radio-inline disabled"><input type="radio" name="header_text" id="header_text3" <?=($companyConfiguration['header_text']==3) ? "checked" : ""?> value="3" onchange="getheaderlayout(this.value,'2')" disabled>All</label>
									<label class="radio-inline disabled"><input type="radio" name="header_text" id="header_text1" <?=($companyConfiguration['header_text']==1) ? "checked" : ""?> value="1" onchange="getheaderlayout(this.value,'2')" disabled>Left</label>
									<label class="radio-inline disabled"><input type="radio" name="header_text" id="header_text2" <?=($companyConfiguration['header_text']==2) ? "checked" : ""?> value="2" onchange="getheaderlayout(this.value,'2')" disabled>Right</label>
									<label class="radio-inline disabled"><input type="radio" name="header_text" id="header_text0" <?=($companyConfiguration['header_text']==0) ? "checked" : ""?> value="0" onchange="getheaderlayout(this.value,'2')" disabled>None</label>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-12" id="printpreview"></div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Quotation Header Content* :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<textarea class="form-control" placeholder="Quotation Header Content" name="quotation_header_content" id="quotation_header_content" ><?= stripslashes($quotation_header_content)?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Sales Order Header Content* :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<textarea class="form-control" placeholder="Sales Order Header Content" name="so_header_content" id="so_header_content" ><?= stripslashes($so_header_content)?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>PO Header Content* :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<textarea class="form-control" placeholder="PO Header Content" name="po_header_content" id="po_header_content" ><?= stripslashes($po_header_content)?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Invoice Header Content* :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<textarea class="form-control" placeholder="Invoice Header Content" name="invoice_header_content" id="invoice_header_content" ><?= stripslashes($invoice_header_content)?></textarea>
								</div>
							</div>
						</div>
					</div>
				</div><br>
				<div class="crm_s">
					<div class="row">
						<div class="col-md-3"><label><strong>Max Followup Date(In Days)* : </strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="number" class="form-control" placeholder="Max Followup Date" name="max_followup_date" id="max_followup_date" maxlength="365" value="<?=$companySettings['max_followup_date']?>">
								</div>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-3"><label><strong>closing date diff(In Days)* : </strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="number" class="form-control" placeholder="closing date diff" name="closing_date_diff" id="closing_date_diff" maxlength="365" value="<?=$companyConfiguration['closing_date_diff']?>">
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>CRM Product Type :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="select2" name="crm_pro_type[]" id="crm_pro_type" multiple data-placeholder="CRM Product Type">
										<?=get_product_type_company($dbcon,$companyConfiguration['crm_pro_type'],''); ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>CRM User Type :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="select2" name="crm_user_type[]" id="crm_user_type" multiple data-placeholder="CRM User Type">
										<?=getusertype($dbcon,$rel['crm_user_type']); ?>

									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Sales order Product Type :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-9">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="select2" name="so_pro_type[]" id="so_pro_type" multiple data-placeholder="Sales order Product Type">
										<?=get_product_type_company($dbcon,$companyConfiguration['so_pro_type'],''); ?>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="row row_margin">
						<div class="col-md-4"><label><strong>Enable Post CRM </strong>&nbsp;&nbsp;</label></div>
						<div class="col-md-4" ><input type="checkbox" class="" name="enable_post_crm" <?php echo (isset($rel['enable_post_crm']) && ($rel['enable_post_crm'])==1) ? "checked" : '' ; ?>  ></div>
					</div>

					<div class="row row_margin">
						<div class="col-md-4"><label><strong>Count Outstanding Target</strong>&nbsp;&nbsp;</label></div>
						<div class="col-md-4" ><input type="checkbox" class="" name="enable_count_outstanding_target" <?php echo (isset($rel['enable_count_outstanding_target']) && ($rel['enable_count_outstanding_target'])==1) ? "checked" : '' ; ?>  ></div>
					</div>

					<div class="row row_margin">
						<div class="col-md-3"><label><strong>CRM Task Order</strong>&nbsp;&nbsp;</label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="crm_task_order" id="crm_task_order">
										<option value="DESC" <?=($rel['crm_task_order']=='DESC') ? 'selected' : ''?>>DESC</option>
										<option value="ASC" <?=($rel['crm_task_order']=='ASC') ? 'selected' : ''?>>ASC</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Quotation Discount Limit</strong>&nbsp;&nbsp;</label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="enable_quotation_limit" id="enable_quotation_limit" onchange="disclimit('enable_quotation_limit','disc_limit','',this.value);">
										<option value="0" <?=($rel['enable_quotation_limit']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['enable_quotation_limit']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-4" style="display: <?=($rel['enable_quotation_limit']=='0') ? 'none' : 'block'?>;" id="disc_limit">
							<div class="form-group">
								<div class="col-md-11 col-xs-11">
									<input type="text" name="quotation_disc_limit" id="quotation_disc_limit" class="form-control" placeholder="Quotation Discount Limit" value="<?=$rel['quotation_disc_limit'];?>"> %
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Inquiry Lock System</strong>&nbsp;&nbsp;</label></div>
						<div class="col-md-3" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="enable_inquiry_autoclose" id="enable_inquiry_autoclose" onchange="disclimit('enable_inquiry_autoclose','auto_close','lock_user',this.value);">
										<option value="0" <?=($rel['enable_inquiry_autoclose']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['enable_inquiry_autoclose']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-3"><label><strong>Inquiry Name using Company</strong>&nbsp;&nbsp;</label></div>
						<div class="col-md-3" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="inq_name_using_comapany" id="inq_name_using_comapany" >
										<option value="0" <?=($rel['inq_name_using_comapany']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['inq_name_using_comapany']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-3" style="display: <?=($rel['enable_inquiry_autoclose']=='0') ? 'none' : 'block'?>;" id="auto_close">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<input type="text" name="inquiry_autoclose_limit" id="inquiry_autoclose_limit" class="form-control" placeholder="Quotation Discount Limit" value="<?=$rel['inquiry_autoclose_limit'];?>"> Days
								</div>
							</div>
						</div>
						<div class="col-md-3" style="display: <?=($rel['enable_inquiry_autoclose']=='0') ? 'none' : 'block'?>;" id="lock_user">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="inquiry_user_lock" id="inquiry_user_lock">
										<option value="0" <?=($rel['inquiry_user_lock']=='0') ? 'selected' : ''?>>Inquiry Lock</option>
										<option value="1" <?=($rel['inquiry_user_lock']=='1') ? 'selected' : ''?>>User Lock</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Sales Order PO Document- Required?</strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="po_document_required" id="po_document_required">
										<option value="0" <?=($rel['po_document_required']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['po_document_required']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Hierarchy wise Inquiry assign ?</strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="hierarchy_inq_assign" id="hierarchy_inq_assign">
										<option value="0" <?=($rel['hierarchy_inq_assign']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['hierarchy_inq_assign']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Inquiry time product required ?</strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="inq_product_required" id="inq_product_required">
										<option value="0" <?=($rel['inq_product_required']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['inq_product_required']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Sales Order time Show Description ?</strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="so_description_required" id="so_description_required">
										<option value="0" <?=($rel['so_description_required']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['so_description_required']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Quotation time rate fixed ?</strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control" name="quotation_rate_fixed" id="quotation_rate_fixed">
										<option value="0" <?=($rel['quotation_rate_fixed']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['quotation_rate_fixed']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Sales Order Wise Branch Planning? </strong><a href="#"  data-original-title="Disabled if branch wise manage is no in enterprice feature." data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i> </label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<?php  ?>
									<select class="form-control"  name="sales_wise_branch_planning" id="sales_wise_branch_planning">
										<option value="0" <?=($rel['sales_wise_branch_planning']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['sales_wise_branch_planning']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Sales Order Wise Branch Planning Before BOM Assing? </strong><a href="#"  data-original-title="after bom assing / before bom assing" data-toggle="tooltip" data-placement="top"><i class="fa fa-info-circle fa-sm"></a></i> </label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<?php  ?>
									<select class="form-control"  name="sales_wise_branch_planning_before_bom" id="sales_wise_branch_planning_before_bom">
										<option value="0" <?=($rel['sales_wise_branch_planning_before_bom']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['sales_wise_branch_planning_before_bom']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Sales Order User Selection Yes or No ? </strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<?php  ?>
									<select class="form-control"  name="crm_sales_order_user_selecation" id="crm_sales_order_user_selecation" onchange="get_so_usertype()">
										<option value="0" <?=($rel['crm_sales_order_user_selecation']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['crm_sales_order_user_selecation']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin crm_so_user_type">
						<div class="col-md-3"><label><strong>Sales Order User Type Selection ? </strong></label></div>
						<div class="col-md-9" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<?php  ?>
									<select class="select2"  name="crm_sales_order_user_type_selecation[]" id="crm_sales_order_user_type_selecation" multiple>
										<?=getusertype($dbcon,$rel['crm_sales_order_user_type_selecation']); ?>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Show Revise Qutation Time Rate With Discount ? </strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<?php  ?>
									<select class="form-control"  name="quot_revise_time_rate_with_discount" id="quot_revise_time_rate_with_discount" >
										<option value="0" <?=($rel['quot_revise_time_rate_with_discount']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['quot_revise_time_rate_with_discount']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<!---------------------------Harshil 15-10-2022----------------------------------------------------------->
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Sales Order Print After Approval...? </strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<?php  ?>
									<select class="form-control"  name="sales_order_print_after_approval" id="sales_order_print_after_approval" >
										<option value="0" <?=($rel['sales_order_print_after_approval']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['sales_order_print_after_approval']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<!---------------------------Harshil 15-10-2022----------------------------------------------------------->
					<!----------------------------------Maulik Start--------------------------->
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Follow-Up Time Inquiry Name Show...? </strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control"  name="followup_inquiry_show" id="followup_inquiry_show" >
										<option value="0" <?=($rel['followup_inquiry_show']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['followup_inquiry_show']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<!----------------------------------Maulik End----------------------------->
					<!-- pathik so wise allocation start -->
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>So Temp Stock Auto Allocate ? </strong></label></div>
						<div class="col-md-4" >
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="form-control"  name="so_temp_auto_allocate" id="so_temp_auto_allocate" >
										<option value="0" <?=($rel['so_temp_auto_allocate']=='0') ? 'selected' : ''?>>No</option>
										<option value="1" <?=($rel['so_temp_auto_allocate']=='1') ? 'selected' : ''?>>Yes</option>
									</select>
									</div>
								</div>
							</div>	
						</div>	
					<!----------------------------------Maulik start-------------------->
					<div class="row row_margin">
						<div class="col-md-12">
							<div class="col-md-3"><label><strong>Packing Module </strong></label></div>
							<div class="col-md-4" >
								<div class="form-group">
									<div class="col-md-12 col-xs-12">
										<select class="form-control"  name="packing_module" id="packing_module" onchange="packing_event();" >
											<option value="0" <?=($rel['packing_module']=='0') ? 'selected' : ''?>>No</option>
											<option value="1" <?=($rel['packing_module']=='1') ? 'selected' : ''?>>Yes</option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-12 row_margin direct_salesorder_allocate">
							<div class="col-md-3"><label><strong>Direct Sales Order To Stock Allocate..?? </strong></label></div>
							<div class="col-md-4" >
								<div class="form-group">
									<div class="col-md-12 col-xs-12">
										<select class="form-control"  name="direct_sales_allocate" id="direct_sales_allocate" >
											<option value="0" <?=($rel['direct_sales_allocate']=='0') ? 'selected' : ''?>>No</option>
											<option value="1" <?=($rel['direct_sales_allocate']=='1') ? 'selected' : ''?>>Yes</option>
										</select>
									</div>

								</div>
							</div>
						</div>

						<div class="col-md-12 row_margin ">
							<div class="col-md-3"><label><strong>Crm Transaction Time Category Selection Active...?? </strong></label></div>
							<div class="col-md-4" >
								<div class="form-group">
									<div class="col-md-12 col-xs-12">
										<select class="form-control"  name="category_selection_active" id="category_selection_active" >
											<option value="0" <?=($rel['category_selection_active']=='0') ? 'selected' : ''?>>No</option>
											<option value="1" <?=($rel['category_selection_active']=='1') ? 'selected' : ''?>>Yes</option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-12 row_margin">
							<div class="col-md-3"><label><strong>Crm Transaction Time Category Wise Product Load..?? </strong></label></div>
							<div class="col-md-4" >
								<div class="form-group">
									<div class="col-md-12 col-xs-12">
										<select class="form-control"  name="cat_wise_product_load" id="cat_wise_product_load" >
											<option value="0" <?=($rel['cat_wise_product_load']=='0') ? 'selected' : ''?>>No</option>
											<option value="1" <?=($rel['cat_wise_product_load']=='1') ? 'selected' : ''?>>Yes</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- pathik so wise allocation end -->
					<!----------------------------------End----------------------------->
				</div>

				
				<!--END JAYESH DESIG DEPARTMENT-->

				<!--pathik start-->
				<div class="purchase_s">
					<div class="row">
						<div class="col-md-4"><label>Purchase Planning Work Order wise ?</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['po_work_order_wise'] == 0){ echo "active";}?>">
									<input type="radio" name="po_work_order_wise" id="po_work_order_wise1" autocomplete="off" value="0" <?php if($companyConfiguration['po_work_order_wise'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['po_work_order_wise'] == 1){ echo "active";}?>" >
									<input type="radio" name="po_work_order_wise" id="po_work_order_wise2" autocomplete="off" value="1" <?php if($companyConfiguration['po_work_order_wise'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<div class="row">
						<div class="col-md-4"><label>Direct PO Create ?</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['direct_po_create'] == 0){ echo "active";}?>">
									<input type="radio" name="direct_po_create" id="direct_po_create1" autocomplete="off" value="0" <?php if($companyConfiguration['direct_po_create'] == 0){ echo "checked";}?>  > NO
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['direct_po_create'] == 1){ echo "active";}?>" >
									<input type="radio" name="direct_po_create" id="direct_po_create2" autocomplete="off" value="1" <?php if($companyConfiguration['direct_po_create'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="col-md-3 control-label">Purchase Product Type :</label>
								<div class="col-md-6">
									<select class="select2" name="indent_po_pro_type[]" id="indent_po_pro_type" multiple data-placeholder="Indent and PO Product Type">
										<?= get_product_type_company($dbcon,$indent_po_pro_type,''); ?>
									</select>
								</div>
							</div>
						</div>
					</div><br>	
				</div><br>


				<div class="resource_s">

					<div class="row">
						<div class="col-md-4"><label>Resource Display ?</label></div>
						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['resource_display'] == 0){ echo "active";}?>">
									<input type="radio" name="resource_display" id="resource_display" autocomplete="off" value="0" <?php if($companyConfiguration['resource_display'] == 0){ echo "checked";}?>  > No
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['resource_display'] == 1){ echo "active";}?>" >
									<input type="radio" name="resource_display" id="resource_display" autocomplete="off" value="1" <?php if($companyConfiguration['resource_display'] == 1){ echo"checked"; }?>> Yes
								</label>
							</div>
						</div>
					</div><br>
					<div class="row">
						<div class="col-md-4"><label class="control-label">Resource Po Time Calculate In ?</label></div>

						<div class="col-md-4">
							<div class="btn-group btn-group-toggle" data-toggle="buttons">
								<label class="btn btn-secondary <?php if($companyConfiguration['resource_time'] == 0){ echo "active";}?>">
									<input type="radio" name="resource_time" id="resource_time" autocomplete="off" value="0" <?php if($companyConfiguration['resource_time'] == 0){ echo "checked";}?>  > Minute
								</label>
								<label class="btn btn-secondary <?php if($companyConfiguration['resource_time'] == 1){ echo "active";}?>" >
									<input type="radio" name="resource_time" id="resource_time" autocomplete="off" value="1" <?php if($companyConfiguration['resource_time'] == 1){ echo"checked"; }?>> Days
								</label>
							</div>
						</div>
					</div><br>

					<div class="row">

						<div class="col-md-4"><label class=" control-label">How many Shift in Day?</label></div>

						<div class="col-md-4">
							<select class="form-control" name="shift_count" id="shift_count" >
								<option value="">--Select Shifts --</option>
								<option value="1" <?=$companyConfiguration['shift_count']=='1'?'selected':''?>>1</option>
								<option value="2" <?=$companyConfiguration['shift_count']=='2'?'selected':''?>>2</option>
								<option value="3" <?=$rel1['shift_count']=='3'?'selected':''?>>3</option>

							</select>
						</div>
					</div><br>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="col-md-3 control-label">Shift Days :</label>
								<div class="col-md-6">
									<select class="select2" name="shift_days[]" id="shift_days" multiple data-placeholder="Shift Days">
										<?= get_shift_days_company($dbcon,$shift_days,''); ?>
									</select>
								</div>
							</div>
						</div>
					</div><br>	
				</div><br>
				<!--pathik end -->

				<!--Maulik Start -->
				<div class="approval_s">
					<div class="row">
						<div class="col-md-12" style="margin-top:15px"> 
							<div class="col-md-4"><label>Auto Indent Approval ?</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_indent'] == 0){ echo "active";}?>">
										<input type="radio" name="automatic_approval_indent" id="automatic_approval_indent" autocomplete="off" value="0" <?php if($companyConfiguration['automatic_approval_indent'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_indent'] == 1){ echo "active";}?>" >
										<input type="radio" name="automatic_approval_indent" id="automatic_approval_indent" autocomplete="off" value="1" <?php if($companyConfiguration['automatic_approval_indent'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div><br>
						</div><br>

						<div class="col-md-12" style="margin-top:15px"> 
							<div class="col-md-4"><label>Auto PO Approval ?</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_po'] == 0){ echo "active";}?>">
										<input type="radio" name="automatic_approval_po" id="automatic_approval_po" autocomplete="off" onchange="user_wise_approval()" value="0" <?php if($companyConfiguration['automatic_approval_po'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_po'] == 1){ echo "active";}?>" >
										<input type="radio" name="automatic_approval_po" id="automatic_approval_po" onchange="user_wise_approval()" autocomplete="off" value="1" <?php if($companyConfiguration['automatic_approval_po'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div><br>

							<div class="col-md-12" id="userwise_po_approval">
								<?=user_wiseapproval_permission($dbcon,'4')?>
							</div>
						</div><br>

						<div class="col-md-12" style="margin-top:15px">
							<div class="col-md-4"><label>Auto PO Finance Approval ?</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_finance_approval_po'] == 0){ echo "active";}?>">
										<input type="radio" name="automatic_finance_approval_po" id="automatic_finance_approval_po" onchange="user_wise_approval()" autocomplete="off" value="0" <?php if($companyConfiguration['automatic_finance_approval_po'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_finance_approval_po'] == 1){ echo "active";}?>" >
										<input type="radio" name="automatic_finance_approval_po" id="automatic_finance_approval_po" onchange="user_wise_approval()" autocomplete="off" value="1" <?php if($companyConfiguration['automatic_finance_approval_po'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div><br>

							<div class="col-md-12" id="userwise_pofinance_approval">
								<?=user_wiseapproval_permission($dbcon,'5')?>
							</div>
						</div><br>

						<div class="col-md-12" style="margin-top:15px">
							<div class="col-md-4"><label>Auto PO Shortclose Approval ?</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_shortclose_approval_po'] == 0){ echo "active";}?>">
										<input type="radio" name="automatic_shortclose_approval_po" id="automatic_shortclose_approval_po" autocomplete="off" value="0" <?php if($companyConfiguration['automatic_shortclose_approval_po'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_shortclose_approval_po'] == 1){ echo "active";}?>" >
										<input type="radio" name="automatic_shortclose_approval_po" id="automatic_shortclose_approval_po" autocomplete="off" value="1" <?php if($companyConfiguration['automatic_shortclose_approval_po'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div><br>
						</div><br>

						<div class="col-md-12" style="margin-top:15px">
							<div class="col-md-4"><label>Auto Quotation Approval ?</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_quotation'] == 0){ echo "active";}?>">
										<input type="radio" name="automatic_approval_quotation" id="automatic_approval_quotation" onchange="user_wise_approval()" autocomplete="off" value="0" <?php if($companyConfiguration['automatic_approval_quotation'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_quotation'] == 1){ echo "active";}?>" >
										<input type="radio" name="automatic_approval_quotation" onchange="user_wise_approval()" id="automatic_approval_quotation" autocomplete="off" value="1" <?php if($companyConfiguration['automatic_approval_quotation'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div><br>
							<div class="col-md-12" id="userwise_quotation_approval">
								<?=user_wiseapproval_permission($dbcon,'1')?>
							</div>
						</div><br>

						<div class="col-md-12" style="margin-top:15px">
							<div class="col-md-4"><label>Auto Proforma Approval ?</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_proforma'] == 0){ echo "active";}?>">
										<input type="radio" name="automatic_approval_proforma" id="automatic_approval_proforma" onchange="user_wise_approval()" autocomplete="off" value="0" <?php if($companyConfiguration['automatic_approval_proforma'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_proforma'] == 1){ echo "active";}?>" >
										<input type="radio" name="automatic_approval_proforma" id="automatic_approval_proforma" onchange="user_wise_approval()" autocomplete="off" value="1" <?php if($companyConfiguration['automatic_approval_proforma'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div><br>
							<div class="col-md-12" id="userwise_proforma_approval">
								<?=user_wiseapproval_permission($dbcon,'6')?>
							</div>
						</div><br>

						<div class="col-md-12" style="margin-top:15px">
							<div class="col-md-4"><label>Auto Sales Order Approval ?</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_so'] == 0){ echo "active";}?>">
										<input type="radio" name="automatic_approval_so" id="automatic_approval_so" onchange="user_wise_approval()" autocomplete="off" value="0" <?php if($companyConfiguration['automatic_approval_so'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_so'] == 1){ echo "active";}?>" >
										<input type="radio" onchange="user_wise_approval()" name="automatic_approval_so" id="automatic_approval_so" autocomplete="off" value="1" <?php if($companyConfiguration['automatic_approval_so'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div><br>

							<div class="col-md-12" id="userwise_salesorder_approval">
								<?=user_wiseapproval_permission($dbcon,'2')?>
							</div>

						</div><br>

						<div class="col-md-12" style="margin-top:15px">
							<div class="col-md-4"><label>Auto Order Acceptance Approval ?</label></div>
							<div class="col-md-4">
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_order_acceptance'] == 0){ echo "active";}?>">
										<input type="radio" onchange="user_wise_approval()" name="automatic_approval_order_acceptance" id="automatic_approval_order_acceptance" autocomplete="off" value="0" <?php if($companyConfiguration['automatic_approval_order_acceptance'] == 0){ echo "checked";}?>  > No
									</label>
									<label class="btn btn-secondary <?php if($companyConfiguration['automatic_approval_order_acceptance'] == 1){ echo "active";}?>" >
										<input type="radio" onchange="user_wise_approval()" name="automatic_approval_order_acceptance" id="automatic_approval_order_acceptance" autocomplete="off" value="1" <?php if($companyConfiguration['automatic_approval_order_acceptance'] == 1){ echo"checked"; }?>> Yes
									</label>
								</div>
							</div><br>

							<div class="col-md-12" id="userwise_orederacceptance_approval">
								<?=user_wiseapproval_permission($dbcon,'3')?>
							</div>
						</div><br>
					</div>
				</div><br>
				<!-- Maulik End -->
				<div class="forecast_s">

					<div class="row">
						<div class="col-md-3"><label><strong>Forecast Base :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="select2" name="forecast_base" id="forecast_base" data-placeholder="Forecast Base">
										<option value="1" <?=(($rel['forecast_base']==1) ? 'selected' : '')?>>Userwise</option>
										<option value="2" <?=(($rel['forecast_base']==2) ? 'selected' : '')?>>Product Category wise</option>
										<option value="3" <?=(($rel['forecast_base']==3) ? 'selected' : '')?>>Product wise</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="row row_margin">
						<div class="col-md-3"><label><strong>Forecast Calculation :</strong> &nbsp;&nbsp;</label></div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-12 col-xs-12">
									<select class="select2" name="forecast_calculation" id="forecast_calculation" data-placeholder="Forecast Base">
										<option value="1" <?=(($rel['forecast_calculation']==1) ? 'selected' : 'selected')?>>Quotation wise</option>
										<option value="2" <?=(($rel['forecast_calculation']==2) ? 'selected' : '')?>>SO wise</option>
										<option value="3" <?=(($rel['forecast_calculation']==3) ? 'selected' : '')?>>Invoice wise</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div><br>

				<div class="qc_s">
					<div class="row">
										<div class="col-md-4"><label>QC UNIT ON *: &nbsp;&nbsp;</label></div>
										<div class="col-md-4">
											<div class="form-group">
												<div class="col-md-8">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if(	$rel['qc_unit'] == 1){ echo "active";}?>">
															<input type="radio" name="qc_unit" id="qc_unit1" autocomplete="off" value="1" <?php if($rel['qc_unit'] == 1){ echo "checked";}?>  > Base Unit
														</label>
														<label class="btn btn-secondary <?php if($rel['qc_unit'] == 2){ echo "active";}?>" >
															<input type="radio" name="qc_unit" id="qc_unit2" autocomplete="off" value="2" <?php if($rel['qc_unit'] == 2){ echo"checked"; }?>> Conv Unit
														</label>
													</div>
												</div>
											</div>
										</div>
									</div><br>
					<div class="row">
						<div class="col-md-4"><label>Rejection Product Type :</label></div>
						<div class="col-md-8">
							<div class="form-group">
								<select class="select2" name="rejection_pro_type[]" id="rejection_pro_type" multiple data-placeholder="Rejection Product Type">
									<?= get_product_type_company($dbcon,$rejection_pro_type,''); ?>
								</select>
							</div>
						</div>
					</div>
				</div><br>


				<div class="service_setting">

					<div class="row">
						<div class="col-md-4"><label>Service Product Type :</label></div>
						<div class="col-md-8">
							<div class="form-group">
								<select class="select2" name="service_pro_type[]" id="service_pro_type" multiple data-placeholder="Service Product Type">
									<?= get_product_type_company($dbcon,$service_pro_type,''); ?>
								</select>
							</div>
						</div>

						<div class="col-md-12">
							<h4><strong>Product Searching: </strong></h4>
						</div>
						<div class="col-md-12">
							<div class="col-md-4">
								<label class="col-md-4 control-label">Service</label>
							</div>
							<div class="col-md-8">
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="service_pro_search[]" value="item" <?=(in_array('item',$service_pro_search)) ? "checked" : ""; ?>> Item No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="service_pro_search[]" value="drawing" <?=(in_array('drawing',$service_pro_search)) ? "checked" : ""; ?>> Drawing No
								</div>
								<div class="col-md-4 col-xs-4">
									<input type="checkbox" name="service_pro_search[]" value="alias" <?=(in_array('alias',$service_pro_search)) ? "checked" : ""; ?>> Alias
								</div>
							</div>
						</div>
					</div>
				</div><br>

				<input type="hidden" name="com_set_id" id="com_set_id" value="<?=$setting_id?>">
				<div class="form-group col-md-2">
					<button type="submit" class="btn btn-success form-control" id="comp_confg" style="margin-left:200px;">Save</button>
				</div>
				<div class="col-md-12">
					<strong class="submit_err"></strong>
				</div>

			</form>
		</div>

	</section>
</div>
</div>

<!--CostCenterGroup overview end-->
</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>
<!-- Modal -->

<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit Company Configuration</h3>

			</div>
			<div class="modal-body form">
				<form id="FormEditCostCenterGroup" role="form" method="post" novalidate>	        
					<div class="form-group">
						<label for="CostCenterGroupid">Company Configuration Name</label>
						<input class="form-control" required="" minlength="2" type='text' name='edit_CostCenterGroup_name' id='edit_CostCenterGroup_name' value='' />
					</div>		

				</div>
				<div class="modal-footer">
					<input type="hidden" name="edit_id" id="edit_id" value="" />
					<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
					<button class="btn btn-info btn-flat" type="submit">Update Company Configuration</button>
				</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal colored-header info" id="ModalEditIndiamart" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit <?=$form?></h3>
				
			</div>
			<form id="india_mart_edit" role="form" method="post" novalidate>
				<div class="modal-body form">
					<div class="form-group">
						<label for="e_ci_name"><strong>Mobile No</strong></label>
						<input class="form-control" type='text' name='edit_mobile_no' id='edit_mobile_no' value='' />
					</div>
					<div class="form-group">
						<label for="e_ci_name"><strong>API Key</strong></label>
						<input class="form-control" type='text' name='edit_api_key' id='edit_api_key' value='' />
					</div>
					<div class="form-group">
						<label><strong>Source</strong></label>
						<select class="select2" id="edit_source_indiamart" name="edit_source_indiamart" >
							<?=get_refer_by($dbcon,$rel['rb_id']);?>
						</select>
					</div>							
				</div>
				<div class="modal-footer">
					<input type="hidden" name="edit_indiamart" id="edit_indiamart" value="" />
					<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
					<button class="btn btn-info btn-success" type="button" onclick="india_mart_update()" type="submit">Update</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="modal colored-header info" id="ModalEditTradeindia" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Change <?=$form?></h3>
				
			</div>
			<form id="FormEditcust_ind" role="form" method="post" novalidate>
				<div class="modal-body form">
					<div class="form-group">
						<label for="e_ci_name"><strong>User Id</strong></label>
						<input class="form-control" type='text' name='edit_trade_india_user_id' id='edit_trade_india_user_id' value='' />
					</div>
					<div class="form-group">
						<label for="e_ci_name"><strong>Profile Id</strong></label>
						<input class="form-control" type='text' name='edit_trade_india_profile_id' id='edit_trade_india_profile_id' value='' />
					</div>
					<div class="form-group">
						<label for="e_ci_name"><strong>API Key</strong></label>
						<input class="form-control" type='text' name='edit_trad_india_api_key' id='edit_trad_india_api_key' value='' />
					</div>
					<div class="form-group">
						<label><strong>Source</strong></label>
						<select class="select2" id="edit_source_id" name="edit_source_id" >
							<?=get_refer_by($dbcon,$rel['rb_id']);?>
						</select>
					</div>							
				</div>
				<div class="modal-footer">
					<input type="hidden" name="edit_tradeindia" id="edit_tradeindia" value="" />
					<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
					<button class="btn btn-info btn-success" type="button" onclick="update_trade_india()">Update</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal colored-header info" id="ModalEditUserWiseApproval" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit User Approval</h3>				
			</div>
			<div class="modal-body form">
			
				<div class="form-group">
					<label class="control-label"><strong>Choose User *</strong></label>
					<select class="select2" id="edit_permission_user_id" name="permission_user_id" >
						<option value="">Choose User</option>
							<?=getalluser($dbcon, '')?>
					</select>
				</div>				
				<div class="form-group">
					<label class="control-label"><strong class="amount-perc-lbl">Amount *</strong></label>
					<input class="form-control numbersOnly amount-edit-input" type="text" name="amount" id="edit_amount" placeholder="Amount" value="" />
					<input class="form-control numbersOnly percentage-edit-input hidden" type="text" name="percentage" id="edit_percentage" placeholder="Percentage" value="" />
					<input class="form-control numbersOnly amount_type-edit-input hidden" type="text" name="amount_type" id="edit_amount_type" value="" />
				</div>
				<div class="form-group">
					<label><strong>Auto Approval</strong></label>
					<select class="select2" id="edit_auto_approval" name="auto_approval" >
						<option value="0">No</option>
						<option value="1">Yes</option>
					</select>
				</div>			
			</div>
			<div class="modal-footer">
				
				<input type="hidden" name="edit_aprv_setting_id" id="edit_aprv_setting_id" value="" />
				<input type="hidden" name="module_type" id="edit_module_type" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="button" onclick="update_userwise_approval()">Update User</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- fince year chage poup pathik 31-03-2023 -->
	<!-- Modal -->
<div class="modal colored-header info" id="Modalyearchange" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Year Change</h3>				
			</div>
			<div class="modal-body form">
			
				<div class="form-group">
					<label class="control-label"><strong>Year Start Date</strong></label>
					<input class="form-control" type="text" readonly name="start_year_new" id="start_year_new" placeholder="Year Start Date" value="" />
				</div>				
				<div class="form-group">
					<label class="control-label"><strong>Year End Date</strong></label>
					<input class="form-control" type="text" readonly name="end_year_new" id="end_year_new" placeholder="Year End Date" value="" />
				</div>	
				<div class="form-group">
					<label class="control-label"><strong>Year</strong></label>
					<input class="form-control" type="text" readonly name="year_new" id="year_new" placeholder="Year" value="" />
				</div>	
				<div class="form-group">
					<label><strong>Series Start from 1 ?</strong></label>
					<select class="form-control" id="start_series_update_new" name="start_series_update_new" >
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				</div>	
				<div class="form-group">
					<div class="col-md-7">
						<label><strong>Series End Formate Set  </strong></label>
					</div>
					<div class="col-md-5">
						<input class="form-control" type="text"  name="series_year_new" id="series_year_new" placeholder="Year" value="" />
					</div>

					<select class="form-control" id="end_formate_series" name="end_formate_series" >
						<option value="1">Yes</option>
						<option value="0">No</option>
					</select>
				</div>		
			</div>
			<div class="modal-footer">
				
				 <input type="hidden" name="year_perent_id" id="year_perent_id" value="" />
				<!--<input type="hidden" name="module_type" id="edit_module_type" value="" /> -->
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="button" onclick="update_year_change()">Year Change</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- fince year chage poup pathik 31-03-2023 -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/indiamart_api_key.js?<?=time()?>"></script>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/tradeindia_api_key.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});
	$('.default_date').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true,
		startDate:'<?php echo date("d-m-Y", strtotime($rel1['financial_start_date'])) ?>',
		endDate:'<?php echo date("d-m-Y", strtotime($rel1['financial_end_date'])) ?>',

	});
</script>
<script type="text/javascript">
	$(document).ready(function() {
		$('[data-toggle="tooltip"]').tooltip();
		<?php 
		$header_logo='3';
		if(!empty($companyConfiguration['header_logo'])){
			$header_logo = $companyConfiguration['header_logo'];			
		}
		?>
		getheaderlayout(<?php echo $header_logo;?>,'1'); 
		packing_event();
		user_wise_approval();
		user_wise_approval_datatable(1);
		user_wise_approval_datatable(2);
		user_wise_approval_datatable(3);
		user_wise_approval_datatable(4);
		user_wise_approval_datatable(5);
		user_wise_approval_datatable(6);
		get_so_usertype();
		if($('input[name="enable_tcs_reporting"]').prop("checked") == true){
			$(".gross_bal").show();	
		}
		else if($('input[name="enable_tcs_reporting"]').prop("checked") == false){
			$(".gross_bal").hide();
		}

		$('input[name="enable_tcs_reporting"]').click(function(){
			if($(this).prop("checked") == true){
				$(".gross_bal").show();
			}
			else if($(this).prop("checked") == false){
				$(".gross_bal").hide();
			}
		});

		//TDS Gross balance input Show Hide
		if($('input[name="enable_tds_reporting"]').prop("checked") == true){
			$(".gross_bal_tds").show();	
		}
		else if($('input[name="enable_tds_reporting"]').prop("checked") == false){
			$(".gross_bal_tds").hide();
		}

		$('input[name="enable_tds_reporting"]').click(function(){
			if($(this).prop("checked") == true){
				$(".gross_bal_tds").show();
			}
			else if($(this).prop("checked") == false){
				$(".gross_bal_tds").hide();
			}
		});
		
		$("#a_add").validate({
			rules: {
				cmp_unique_id: {
					required: true
				},
				company_name: {
					required: true
				},
				address: {
					required: true,
					minlength: 15
				}
			},
			messages: {
				cmp_unique_id: {
					required: "Enter Company ID"
				},
				company_name: {
					required: "Enter Company Name"
				},
				address: {
					required: "Enter Address",
					minlength: "Your Description must consist of at least 15 characters"
				}

			}

		});

		$("#a_add").on('submit',function(e) {

			for (instance in CKEDITOR.instances) 
			{
				CKEDITOR.instances[instance].updateElement();
			}	

			var form = this;
			e.preventDefault();
			e.stopPropagation();	
			if (!$("#a_add").valid()) {
				return false;
			}
			form.submitted = true;	
			Loading(true);	
			$(this).attr("disabled","disabled");		
			var form_data=new FormData(this);
			$.ajax({
				cache:false,
				url: root_domain+administration_domain+'app/company_confg/',
				type: "POST",
				data: form_data,
				contentType: false,
				processData:false,	
				success: function(response)
				{
			//console.log(response);			
			if(response.trim() == 'update') {
				Unloading();
				toastr.success("UPDATE SUCCESSFULLY", "SUCCESS");		
				location.reload();
			}
			else if(response == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR");
				Unloading();
			}
			else if(response == '-1')
			{
				toastr.info("ALREADY EXISTS", "INFO");
				Unloading();				
			}
			else if(response == '-2')
			{
				toastr.warning("COMPANY ID ALREADY EXISTS", "ERROR");
				Unloading();
			}
			$('#a_add').trigger('reset');
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.log(textStatus, errorThrown);
		}
	});

		});

		$("#company_confg").on('submit',function(e) {
			var form = this;
			e.preventDefault();
			e.stopPropagation();	

			for (instance in CKEDITOR.instances) 
			{
				CKEDITOR.instances[instance].updateElement();
			}

			form.submitted = true;	
			Loading(true);	
			$(this).attr("disabled","disabled");		

			var form_data=new FormData(this);	
			form_data.append('header_text', $('input[name=header_text]:Checked').val());
			$.ajax({
				cache:false,
				url: root_domain+administration_domain+'app/company_confg/',
				type: "POST",
				data: form_data,
				contentType: false,
				processData:false,
				success: function(response)
				{
				//alert(response);
				//console.log(response);	
				//var arr = jQuery.parseJSON(response);			
				if(response.trim() == '1') {
					Unloading();
					toastr.success("COMPANY CONFIGURATION SAVED SUCCESSFULLY", "SUCCESS");	
					//show_data();			
				}
				else if(response.trim() == '0') {
					toastr.warning("SOMETHING WRONG", "ERROR")
					Unloading();
				}
				
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus, errorThrown);
			}
		});


		});

		if($('.enable_eway_bill').is(':checked') || $('.enable_einvoice').is(':checked') || $('.enable_gst_filling').is(':checked')){
			$('.gsp_set').show();
		}else{
			$('.gsp_set').hide();
		}

		$(".enable_eway_bill,.enable_einvoice,.enable_gst_filling").change(function() {
		//alert("hiiii");
		if($('.enable_eway_bill').is(":checked") || $('.enable_einvoice').is(":checked") || $('.enable_gst_filling').is(":checked")) {
			$('.gsp_set').show();  
		}else{
			$('.gsp_set').hide();
		}

	});

		$(".comp_setting").show();
		$(".approval_s").hide();
		$(".acct").hide();
		$(".inv").hide();
		$(".purchase_s").hide();
		$(".entr").hide();
		$(".apiset").hide();
		$(".designdepartment").hide();
		$(".finance_year").hide();
		$(".print_set").hide();
		$(".crm_s").hide();
		$("#comp_confg").hide();
		$(".company_setting").addClass('btn-warning');
		$(".resource_s").hide();
		$(".forecast_s").hide();
		$(".qc_s").hide();
		$(".service_setting").hide();

		$('.account').click(function(){
			$(".acct").show();
			$(".inv").hide();
			$(".approval_s").hide();
			$(".purchase_s").hide();
			$(".entr").hide();
			$(".apiset").hide();
			$(".designdepartment").hide();
			$(".finance_year").hide();
			$(".comp_setting").hide();
			$(".crm_s").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".account").addClass('btn-warning');
			$(".print_set").hide();
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});

		$('.inventory').click(function(){
			$(".inv").show();
			$(".acct").hide();
			$(".approval_s").hide();
			$(".purchase_s").hide();
			$(".entr").hide();
			$(".apiset").hide();
			$(".designdepartment").hide();
			$(".finance_year").hide();
			$(".comp_setting").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".inventory").addClass('btn-warning');
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});

		$('.enterprise').click(function(){
			$(".entr").show();
			$(".acct").hide();
			$(".approval_s").hide();
			$(".purchase_s").hide();
			$(".inv").hide();
			$(".apiset").hide();
			$(".designdepartment").hide();
			$(".finance_year").hide();
			$(".comp_setting").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".enterprise").addClass('btn-warning');
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});

		$('.api').click(function(){
			$(".apiset").show();
			$(".acct").hide();
			$(".purchase_s").hide();
			$(".approval_s").hide();
			$(".inv").hide();
			$(".entr").hide();
			$(".designdepartment").hide();
			$(".finance_year").hide();
			$(".comp_setting").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".api").addClass('btn-warning');
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});

		$('.design').click(function(){
			$(".designdepartment").show();		
			$(".apiset").hide();
			$(".approval_s").hide();
			$(".purchase_s").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$(".finance_year").hide();
			$(".comp_setting").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".design").addClass('btn-warning');
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});	
		$('.finance_year_btn').click(function(){
			$(".finance_year").show();
			$(".purchase_s").hide();
			$(".approval_s").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$(".comp_setting").hide();
			$("#comp_confg").hide();
			$("button").removeClass('btn-warning');
			$(".finance_year_btn").addClass('btn-warning');
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
			load_financial_year();
		});	
		$('.company_setting').click(function(){
			$(".comp_setting").show();
			$(".purchase_s").hide();
			$(".approval_s").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").hide();
			$("button").removeClass('btn-warning');
			$(".company_setting").addClass('btn-warning');
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});	
		$('.print_setup').click(function(){
			$(".print_set").show();
			$(".purchase_s").hide();
			$(".approval_s").hide();
			$(".comp_setting").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".print_setup").addClass('btn-warning');
			$(".crm_s").hide();
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});	
		$(".crm_set").click(function(){


			$(".crm_s").show();
			$(".approval_s").hide();
			$(".purchase_s").hide();
			$(".print_set").hide();
			$(".comp_setting").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".crm_set").addClass('btn-warning');
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});

		$(".purchase_set").click(function(){
			$(".purchase_s").show();
			$(".approval_s").hide();
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".comp_setting").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".purchase_set").addClass('btn-warning');
			$(".resource_s").hide();
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});
		
		$(".resource_set").click(function(){
			$(".resource_s").show();
			$(".purchase_s").hide();
			$(".approval_s").hide();
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".comp_setting").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".resource_set").addClass('btn-warning');
			$(".forecast_s").hide();
			$(".qc_s").hide();
			$(".service_setting").hide();
		});
		$(".approval_set").click(function(){
			$(".resource_s").hide();
			$(".approval_s").show();
			$(".purchase_s").hide();
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".comp_setting").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").show();
			$(".forecast_s").hide();
			$("button").removeClass('btn-warning');
			$(".approval_set").addClass('btn-warning');
			$(".qc_s").hide();
			$(".service_setting").hide();
		});
		$(".forecast_set").click(function(){
			$(".forecast_s").show();
			$(".resource_s").hide();
			$(".approval_s").hide();
			$(".purchase_s").hide();
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".comp_setting").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".forecast_set").addClass('btn-warning');
			$(".qc_s").hide();
			$(".service_setting").hide();
		});
		$(".qc_set").click(function(){
			$(".qc_s").show();
			$(".forecast_s").hide();
			$(".resource_s").hide();
			$(".approval_s").hide();
			$(".purchase_s").hide();
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".comp_setting").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".qc_set").addClass('btn-warning');
			$(".service_setting").hide();
		});

		$(".service_set").click(function(){
			$(".qc_s").hide();
			$(".forecast_s").hide();
			$(".resource_s").hide();
			$(".approval_s").hide();
			$(".purchase_s").hide();
			$(".print_set").hide();
			$(".crm_s").hide();
			$(".comp_setting").hide();
			$(".finance_year").hide();
			$(".designdepartment").hide();		
			$(".apiset").hide();
			$(".acct").hide();
			$(".inv").hide();
			$(".entr").hide();
			$("#comp_confg").show();
			$("button").removeClass('btn-warning');
			$(".service_set").addClass('btn-warning');
			$(".service_setting").show();
		});
		$("#tab1").click(function(){
			$("#comp_confg").show();
		});
		$("#tab2").click(function(){
			$("#comp_confg").hide();
			load_trade_india_api();
		});
		$("#tab3").click(function(){
			$("#comp_confg").hide();
			load_india_mart_api();
		});
		$("#tab5").click(function(){
			$("#comp_confg").hide();
		});


		// JS : Change amount type for Quotation Approval
		$("#amount_type1").change(function(){
			if ($(this).val() == 1) {
				$(".percentage-approval").addClass("hidden");
				$(".amount-approval").removeClass("hidden");
				$("#percentage1").val("");
			} else {
				$(".amount-approval").addClass("hidden");
				$(".percentage-approval").removeClass("hidden");
				$("#amount1").val("");
			}
		});

	});

function disclimit(id,sid,tid,val){
	if(val==1){
		$('#'+sid).css('display','block');
		$('#'+tid).css('display','block');
	}else{
		$('#'+sid).css('display','none');
		$('#'+tid).css('display','none');
	}
}
function get_finance_year(year_type)
{
	//alert(year_type);
	var cur_year = new Date().getFullYear();
	var cur_year1 = cur_year.toString().slice(2);
	var next_year = (Number(cur_year)+1);
	//alert(next_year);
	var next_year1=next_year.toString().slice(2);
	//alert(next_year1);
	if(year_type==1)
	{
		$('#fiancial_year').val(cur_year1+"-"+next_year1);
		$('#financial_start_date').val("01-04-"+cur_year);
		$('#financial_end_date').val("31-03-"+next_year);
	}
	else if(year_type==2)
	{
		$('#fiancial_year').val(cur_year1+"-"+next_year1);
		$('#financial_start_date').val("01-01-"+cur_year);
		$('#financial_end_date').val("31-12-"+cur_year);
	}
	else
	{
		$('#fiancial_year').val("");
		$('#financial_start_date').val("");
		$('#financial_end_date').val("");
	}
}
function get_so_usertype(){
	var user_type = $('#crm_sales_order_user_selecation').val();
	if(user_type==1){
		$('.crm_so_user_type').show();
	}else{
		$('.crm_so_user_type').hide();
	}
}

function packing_event(){
	var packing_module = $("#packing_module").val();

	if(packing_module=='1'){
		$('.direct_salesorder_allocate').show();
	}else{
		$('.direct_salesorder_allocate').hide();
		$('#direct_sales_allocate').val(0);
	}
}

CKEDITOR.replace( 'address', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'logo_content', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'condition', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'po_terms_conditions', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'quotation_print_content', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'quotation_footer_content', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'quotation_header_content', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'so_header_content', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'po_header_content', {
	enterMode: CKEDITOR.ENTER_BR
});
CKEDITOR.replace( 'invoice_header_content', {
	enterMode: CKEDITOR.ENTER_BR
});

function toggle_banch_no_wise(){
	var permission = $('input[name="batch_wise_stock"]:checked').val();
	
	if(permission == '1'){
		
		$(".batch_stock_permission").slideDown();
	}else{
		
		$(".batch_stock_permission").slideUp();
	}
}


function toggle_default_godown(){
	var permission = $('input[name="set_reserve_godown"]:checked').val();
	
	if(permission == '1'){
		
		$(".def_gd_row").slideDown();
	}else{
		
		$(".def_gd_row").slideUp();
	}
}

function toggle_store_approval(){
	var permission = $('input[name="wo_bw_alloc_stock"]:checked').val();
	console.log(permission)
	if(permission == '1'){
		$('.store_approval1').removeClass('active');
		$('.store_approval2').addClass('active');
		$('#store_approval1').prop('checked',true);
		$('#store_approval2').prop('checked',true);
		$(".store_approval").attr("disabled", "disabled");

	}else{
		$(".store_approval").removeAttr("disabled");
	}
}

function toggle_batch_stock(){
	var permission = $('input[name="batch_stock"]:checked').val();
	
	if(permission == '1'){
		$('#header_text1').prop('checked',true);
		$(".batch_no_permission").slideDown();
	}else{
		
		$(".batch_no_permission").slideUp();
	}
}
function check_abc_stock_validation(){
	var type_a=parseFloat($("#stock_type_a").val());
	var type_b=parseFloat($("#stock_type_b").val());
	var type_c=parseFloat($("#stock_type_c").val());
	if(type_a<=type_b){
		//alert(type_a+"<="+type_b);
		$("#stock_type_b").val(0);
	}
	if(type_b<=type_c){
		//alert(type_b+"<="+type_c);
		$("#stock_type_c").val(0);
	}
}
function check_start_slab(count)
{
	var aging_start_days = Number($('#aging_start_days'+count).val());
	var aging_end_days = Number($('#aging_end_days'+count).val());

	//checkpoint - End days should not be blank or zero and must be greate than start date -- dhaval

	if(count!=1)
	{
		var new_cnt = count-1;
		var last_end = Number($('#aging_end_days'+new_cnt).val());
		var count_one_start = last_end+1;
		if(aging_start_days <= last_end)
		{
			alert('Start days must be greater than end days');
			$('#aging_start_days'+count).focus();
			$('#comp_confg').prop('disabled',true);
			$('.submit_err').html('Please Correct the aging slabs');
		}
		else if(aging_start_days!=count_one_start)
		{
			alert('Start days should be '+count_one_start);
			$('#aging_start_days'+count).focus();
			$('#comp_confg').prop('disabled',true);
			$('.submit_err').html('Please Correct the aging slabs');	
		}
		else
		{		
			$('#comp_confg').prop('disabled',false);
			$('.submit_err').html('');		
		}
	}

}
function check_end_slab(count)
{
	
	var aging_start_days = Number($('#aging_start_days'+count).val());
	var aging_end_days = Number($('#aging_end_days'+count).val());	

	//checkpoint - End days should not be blank or zero and must be greate than start date -- dhaval

	if(aging_end_days <= aging_start_days)
	{
		if($('#aging_end_days'+count).val()=='' || $('#aging_end_days'+count).val()==0)
		{
			alert('Please Enter End Days');
		}
		else
		{
			alert('End days must be greater than start days');	
		}
		
		$('#aging_end_days'+count).focus();
		$('#comp_confg').prop('disabled',true);
		$('.submit_err').html('Please Correct the aging slabs');
	}	
	else
	{
		$('#comp_confg').prop('disabled',false);
		$('.submit_err').html('');
	}

}
function getheaderlayout(val,type){
	var logo = '<td><img src="<?php echo ROOT.LOGO.$rel2['logo']?>" style="width: 250px; height: 100px;"/></td>';
	var text = '<td><h3><?php echo $rel2['company_name']?></h3></td>';
	// console.log(val+','+type);
	if(type=='1'){
		if(val=='1'){
			$('#header_text2').prop('checked',true);
			$('#printpreview').html('<table style="border: 1px solid;"><tr>'+logo+text+'</tr></table>');
		} else if(val=='0'){
			$('#header_text3').prop('checked',true);
			$('#printpreview').html('<table style="border: 1px solid;"><tr>'+text+'</tr></table>');
		} else if(val=='2'){
			$('#header_text1').prop('checked',true);
			$('#printpreview').html('<table style="border: 1px solid;"><tr>'+text+logo+'</tr></table>');
		} else if(val=='3'){
			$('#header_text0').prop('checked',true);
			$('#printpreview').html('<table><tr>'+logo+'</tr></table>');
		} else{
			toastr.warning("Please Select Any Header logo option!!", "ERROR")
		}
	} else{
		if(val=='1'){
			$('#header_logo2').prop('checked',true);
			$('#printpreview').html('<table style="border: 1px solid;"><tr>'+logo+text+'</tr></table>');
		} else if(val=='0'){
			$('#header_logo3').prop('checked',true);
			$('#printpreview').html('<table style="border: 1px solid;"><tr>'+text+'</tr></table>');
		} else if(val=='2'){
			$('#header_logo1').prop('checked',true);
			$('#printpreview').html('<table style="border: 1px solid;"><tr>'+text+logo+'</tr></table>');
		} else if(val=='3'){
			$('#header_logo0').prop('checked',true);
			$('#printpreview').html('<table><tr>'+logo+'</tr></table>');
		} else{
			toastr.warning("Please Select Any Header text option!!", "ERROR")
		}
	}
}

function load_financial_year(){
	$("#financial_year_tble").dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/company_confg/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_financial_year" } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}


function user_wise_approval_datatable(module_type){
	$("#user_wise_approval_"+module_type).dataTable({
		"bAutoWidth" : false,
		"bFilter" : true,
		"bSort" : true,
		"bProcessing": true,
		"bServerSide" : true,
		"bDestroy" : true,
		"oLanguage": {
			"sLengthMenu": "_MENU_",
			"sProcessing": "<img src='"+root_domain+"img/loading.gif'/> Loading ...",
			"sEmptyTable": "NO DATA ADDED YET !",
		},
		"aLengthMenu": [[10, 20, 30, 50], [10, 20, 30, 50]],
		"iDisplayLength": 10,
		"sAjaxSource": root_domain+administration_domain+'app/company_confg/',
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "mode", "value": "load_userwise_approval" },
				{ "name": "module_type", "value": module_type } );
		},
		"fnDrawCallback": function( oSettings ) {
			$('.ttip, [data-toggle="tooltip"]').tooltip();
		}
	}).fnSetFilteringDelay();

	//Search input style
	$('.dataTables_filter input').addClass('form-control').attr('placeholder','Search');
	$('.dataTables_length select').addClass('form-control');
}

function add_userwise_approval(module_type){

	var amount_type = $('#amount_type'+module_type).val();

	if($("#permission_user_id"+module_type).val()==="")
	{		
		toastr.warning("Select User", "ERROR")
		$("#permission_user_id"+module_type).select2('focus')
		return false;
	}
	else if( amount_type==1 && $("#amount"+module_type).val()==="")
	{		
		toastr.warning("Enter Amount", "ERROR")
		$("#amount"+module_type).focus();
		return false;
	}
	else if(amount_type==2 && $("#percentage"+module_type).val()==="")
	{		
		toastr.warning("Enter Percentage", "ERROR")
		$("#percentage"+module_type).focus();
		return false;
	}
	else if($("#auto_approval"+module_type).val()==="")
	{		
		toastr.warning("Select Approval", "ERROR")
		$("#auto_approval"+module_type).focus();
		return false;
	}

	var percentage = (module_type == 1) ? $('#percentage'+module_type).val() : '0';

	Loading();
	var form_data = {
		permission_user_id :$('#permission_user_id'+module_type).select2("val"),
		amount: $('#amount'+module_type).val(),
		percentage: percentage,
		amount_percentage_type: amount_type,
		auto_approval: $('#auto_approval'+module_type).val(),
		module_type : $('#module_type'+module_type).val(),
		mode:'add_user_wise_approval',
		is_ajax: 1
	};
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/company_confg/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var arr = jQuery.parseJSON(response);
			if(arr.msg =='1'){
				Unloading();
				$("#permission_user_id"+module_type).select2("val","");
				$("#amount"+module_type).val("");
				$("#auto_approval"+module_type).select2("val","0");
				toastr.success("USER APPROVAL SAVED SUCCESSFULLY", "SUCCESS");
				user_wise_approval_datatable(module_type);
			}else if(arr.msg =='-1'){
				Unloading();
				toastr.warning("ALREADY EXISTS", "INFO")
			}else if(arr.msg =='0'){
				Unloading();
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
		}
	});
}

function active_financial_year(id, status){
	var form_data = {
		eid :id,
		status: status,
		mode:'active_financial_year',
		is_ajax: 1
	};
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/company_confg/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			load_financial_year();
		}
	});
}
function add_new_fyear() {
	var r= confirm("Do you want to use this year as Current Financial year?");
	if(r) {
		var res= confirm("Do you want to start your series at 0?");
		if(res){
			var re = window.prompt("Series Text","/"+$('#fiancial_year').val());
			if(re){
				var series_end_text = re;
				var series_start_text = '0';
				var status = '1';
			}else{
				var series_end_text = "/"+$('#fiancial_year').val();
				var series_start_text = '0';
				var status = '1';
			}
		}else{
			var re = window.prompt("Series Text","/"+$('#fiancial_year').val());
			if(re){
				var series_end_text = re;
				var series_start_text = '1';
				var status = '1';
			}else{
				var series_end_text = "/"+$('#fiancial_year').val();
				var series_start_text = '1';
				var status = '1';
			}
		}
	}else{
		var series_end_text = "/"+$('#fiancial_year').val();
		var series_start_text = '1';
		var status = '0';
	}
	var form_data = {
		finance_year_type :$('#finance_year_type').val(),
		financial_start_date :$('#financial_start_date').val(),
		financial_end_date :$('#financial_end_date').val(),
		fiancial_year :$('#fiancial_year').val(),
		mode:'add_new_fyear',
		series_end_text:series_end_text,
		series_start_text:series_start_text,
		status:status,
		is_ajax: 1
	};
	// console.log(form_data);
	// return false;
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/company_confg/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			console.log(response);
			if(response.trim() == '1') {
				Unloading();
				toastr.success("NEW FINANCIAL YEAR SAVED SUCCESSFULLY", "SUCCESS");	
				//show_data();			
			}
			else if(response.trim() == '0') {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}else if(response.trim() == '2') {
				toastr.info("ALREADY EXISTS THIS FINANCIAL YEAR", "INFO")
				Unloading();
			}
			load_financial_year();
			$("#comp_confg").hide();
		}
	});
}
function branch_wise_manages(val){
	if(val=='0'){
		var r= confirm("Don't you want to manage branch wise?");
	} else{
		var r= confirm("Do you want to manage branch wise?");
	}
	// console.log(val);
	Loading();
	if(r) {
		$.ajax({
			cache:false,
			url: root_domain+administration_domain+'app/company_confg/',
			type: "POST",
			data: {"mode": "branch_wise_manages", "val": val},
			success: function(response)
			{
				console.log(response);
				if(val == '0'){
					$("#sales_wise_branch_planning").prop("disabled", true);
					$("#branch_0").show();
					$("#branch_1").hide();
					//$("#branch_box").text(<?php //echo getBranchBox($dbcon, 0, $companyConfiguration['default_branch_id'], true, false,'','4','8'); ?>);
					
				}else{
					$("#sales_wise_branch_planning").prop("disabled", false);
					//$("#branch_box").text(<?php //echo getBranchBox($dbcon, $branch_id, $companyConfiguration['default_branch_id'], false, false,'','4','8'); ?>);
					$("#branch_1").show();
					$("#branch_0").hide();
				}
				Unloading();
			}
		});
	}
}

function user_wise_approval(){

	if($('input[name=automatic_approval_po]:checked').val()=='0'){
		$("#userwise_po_approval").show();
	}else{
		$("#userwise_po_approval").hide();
	}


	if($('input[name=automatic_finance_approval_po]:checked').val()=='0'){
		$("#userwise_pofinance_approval").show();
	}else{
		$("#userwise_pofinance_approval").hide();
	}

	if($('input[name=automatic_approval_quotation]:checked').val()=='0'){
		$("#userwise_quotation_approval").show();
	}else{
		$("#userwise_quotation_approval").hide();
	}

	if($('input[name=automatic_approval_proforma]:checked').val()=='0'){
		$("#userwise_proforma_approval").show();
	}else{
		$("#userwise_proforma_approval").hide();
	}


	if($('input[name=automatic_approval_so]:checked').val()=='0'){
		$("#userwise_salesorder_approval").show();
	}else{
		$("#userwise_salesorder_approval").hide();
	}

	if($('input[name=automatic_approval_order_acceptance]:checked').val()=='0'){
		$("#userwise_orederacceptance_approval").show();
	}else{
		$("#userwise_orederacceptance_approval").hide();
	}
}

function edit_userwise_approval(id){
	Loading(true);
	$.ajax({
		type: "POST",
		url: root_domain+administration_domain+'app/company_confg/',
		data: { mode : "preedit", id : id },
		success: function(response)
		{
			var obj = jQuery.parseJSON(response);
			if (obj.amount_type == 2) {
				$(".amount-perc-lbl").text("Percentage");
				$(".amount-edit-input").addClass("hidden");
				$(".percentage-edit-input").removeClass("hidden");

			} else {
				$(".amount-perc-lbl").text("Amount");
				$(".amount-edit-input").removeClass("hidden");
				$(".percentage-edit-input").addClass("hidden");
			}

			$("#edit_percentage").val(obj.percentage);
			$("#ModalEditUserWiseApproval").modal("show");
			$("#edit_aprv_setting_id").val(id);								
			$("#edit_permission_user_id").select2("val",obj.permission_user_id);
			$("#edit_amount").val(obj.amount);
			$("#edit_auto_approval").select2("val", obj.auto_approval);	
			$("#edit_module_type").val(obj.module_type);			
			Unloading();
		}
	});
}


function update_userwise_approval(){

	var edit_amount_type = $('#edit_amount_type').val();

	if($("#edit_permission_user_id").val()==="")
	{		
		toastr.warning("Select User", "ERROR")
		$("#edit_permission_user_id").select2('focus')
		return false;
	}
	else if(edit_amount_type == 1 && $("#edit_amount").val()==="")
	{		
		toastr.warning("Enter Amount", "ERROR")
		$("#edit_amount").focus();
		return false;
	}
	else if(edit_amount_type == 2 && $("#edit_percentage").val()==="")
	{		
		toastr.warning("Enter Percentage", "ERROR")
		$("#edit_percentage").focus();
		return false;
	}
	else if($("#edit_auto_approval").val()==="")
	{		
		toastr.warning("Select Approval", "ERROR")
		$("#edit_auto_approval").focus();
		return false;
	}
	Loading();
	var form_data = {
		permission_user_id :$('#edit_permission_user_id').select2("val"),
		amount: $('#edit_amount').val(),
		percentage: $('#edit_percentage').val(),
		auto_approval: $('#edit_auto_approval').val(),
		module_type : $('#edit_module_type').val(),
		aprv_setting_id : $('#edit_aprv_setting_id').val(),
		mode:'update_user_wise_approval',
		is_ajax: 1
	};
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/company_confg/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			//console.log(response);
			var arr = jQuery.parseJSON(response);
			if(arr.msg =='1'){
				Unloading();
				$("#permission_user_id").select2("val","");
				$("#amount").val("");
				$("#auto_approval").select2("val","0");
				toastr.success("USER APPROVAL UPDATED SUCCESSFULLY", "SUCCESS");
				$("#ModalEditUserWiseApproval").modal("hide");
				user_wise_approval_datatable(arr.module_type);
			}else if(arr.msg =='-1'){
				Unloading();
				toastr.info("ALREADY EXISTS", "INFO")
			}else if(arr.msg =='0'){
				Unloading();
				toastr.warning("SOMETHING WRONG", "ERROR")
			}
		}
	});
}

function delete_userwise_approval(id,module_type){
	var r= confirm(" Are you sure want to delete ?");

	if(r) {
		Loading(true);
		$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/company_confg/',
			data: { mode : "delete_userwise_approval", eid : id },
			success: function(response)
			{
				var data=jQuery.parseJSON(response)
				var response=data.res;
				if(response.trim() == "1") {
					console.log(response);
					toastr.success("USER APPROVAL DELETE SUCCESSFULLY", "SUCCESS");
					Unloading();
					user_wise_approval_datatable(module_type);
				}
				else if(response.trim() == "0") {
					toastr.warning("SOMETHING WRONG", "WARNING");
					Unloading();
				}							
			}
		});	
	}
}

if($('input[name=branch_wise_manage]:checked').val() == '0'){
	$("#sales_wise_branch_planning").prop("disabled", true);
}else{
	$("#sales_wise_branch_planning").prop("disabled", false);
}

function change_year(){
	$("#Modalyearchange").modal("show");
	$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/company_confg/',
			data: { mode : "fetch_new_year" },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				//alert(arr.start_date);
				//alert(arr.end_date);
				//alert(arr.year);
				$("#start_year_new").val(arr.start_date);
				$("#end_year_new").val(arr.end_date);
				$("#year_new").val(arr.year);
				$("#series_year_new").val(arr.series_year_new);
				$("#year_perent_id").val(arr.year_perent_id);

									
			}
		});	
}

function update_year_change(){
	var start_year_new=$("#start_year_new").val();
	var end_year_new=$("#end_year_new").val();
	var year_new=$("#year_new").val();
	var series_year_new=$("#series_year_new").val();
	var year_perent_id=$("#year_perent_id").val();
	var start_series_update_new=$("#start_series_update_new").val();
	var end_formate_series=$("#end_formate_series").val();
	var year_perent_id=$("#year_perent_id").val();

	
	$.ajax({
			type: "POST",
			url: root_domain+administration_domain+'app/company_confg/',
			data: { mode : "update_year_change",start_year_new:start_year_new,end_year_new:end_year_new,year_new:year_new,series_year_new:series_year_new,year_perent_id:year_perent_id,start_series_update_new:start_series_update_new,end_formate_series:end_formate_series,year_perent_id:year_perent_id },
			success: function(response)
			{
				var arr = jQuery.parseJSON(response);
				if(arr.res===1){
					$("#Modalyearchange").modal("hide");
					toastr.success("Year Change SUCCESSFULLY", "SUCCESS");
				}else{
					toastr.warning("Year Not Change", "WARNING");
				}
				
									
			}
		});	
}

function add_whatsapp_confgure() {		
	
	var company_conf_id = $("#company_conf_id").val();
	var whatsapp_key=$("#whatsapp_key").val();
	var whatsapp_url=$("#whatsapp_url").val();
	var whatsapp_template=$("#whatsapp_template").val();
	
	var enable_whatsapp = 0;
	if ($("#enable_whatsapp1").is(":checked")) {
		enable_whatsapp = 1;
		if(!whatsapp_url){		
			toastr.warning("Enter Whatsapp Url", "ERROR");
			$("#whatsapp_url").focus();
			return false;
		}

		if(!whatsapp_key){		
			toastr.warning("Enter Whatsapp Key", "ERROR");
			$("#whatsapp_key").focus();
			return false;
		}
		
		if(!whatsapp_template){		
			toastr.warning("Enter Whatsapp Template", "ERROR");
			$("#whatsapp_template").focus();
			return false;
		}
	}
	
	var form_data = {
		company_conf_id: company_conf_id,
		enable_whatsapp: enable_whatsapp,
		whatsapp_key: whatsapp_key,
		whatsapp_url: whatsapp_url,
		whatsapp_template: whatsapp_template,
		mode:"add_whatsapp_config",
		is_ajax: 1
	};	
	
	$.ajax({
		cache:false,
		url: root_domain+administration_domain+'app/company_confg/',
		type: "POST",
		data: form_data,
		success: function(response)
		{
			var resp=JSON.parse(response);			
			var response = resp.res;
			if(response == '1') {				
				toastr.success("Whatsapp API Configuration SUCCESSFULLY", "SUCCESS");
				Unloading();
			} else  {
				toastr.warning("SOMETHING WRONG", "ERROR")
				Unloading();
			}
		},
	});	
}

</script>
</body>
</html>
