<?php session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
/*if(strpos($_SERVER[REQUEST_URI], "setting")==false) {
	$mode="Add";
	$valid_till_start_date=date('1-m-Y');
	$valid_till_end_date=date("d-m-Y");
}
else {
	$eid=$dbcon->real_escape_string($_REQUEST['id']);
}*/
$query="select * from tbl_company where company_id=".$_SESSION['company_id'];
$rel=mysqli_fetch_assoc($dbcon->query($query));
$mode="Edit";

// Amish Soni Start 12-01-2021
$setting_id = $crm_auto_mail = $quotation_print_content = $project_wise_manufacturing = $project_wise_item_rate = $company_conf_id = $crm_pro_type = $so_pro_type = $indent_po_pro_type = '';
$companySettings = getCompanySettings($dbcon);

$companyConfiguration=getCompanyConfiguration($dbcon);
if($companySettings) {
	$setting_id = $companySettings['id'] ? $companySettings['id'] : $setting_id;
	$crm_auto_mail = $companySettings['crm_auto_mail'] ? $companySettings['crm_auto_mail'] : $crm_auto_mail;
	$project_wise_manufacturing = $companySettings['project_wise_manufacturing'] ? $companySettings['project_wise_manufacturing'] : $project_wise_manufacturing;
	$project_wise_item_rate = $companySettings['project_wise_item_rate'] ? $companySettings['project_wise_item_rate'] : $project_wise_item_rate;
	$quotation_print_content = $companySettings['quotation_print_content'] ? $companySettings['quotation_print_content'] : $quotation_print_content;
	$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
}
if($companyConfiguration) {
	$company_conf_id = $companyConfiguration['company_conf_id'] ? $companyConfiguration['company_conf_id'] : $company_conf_id;
	$crm_pro_type = $companyConfiguration['crm_pro_type'] ? $companyConfiguration['crm_pro_type'] : $crm_pro_type;
	$so_pro_type = $companyConfiguration['so_pro_type'] ? $companyConfiguration['so_pro_type'] : $so_pro_type;
	$indent_po_pro_type = $companyConfiguration['indent_po_pro_type'] ? $companyConfiguration['indent_po_pro_type'] : $indent_po_pro_type;
	$upload_receipt = $companyConfiguration['upload_reciept'] ? $companyConfiguration['upload_reciept'] : $upload_receipt;
	$qc_upload_receipt = $companyConfiguration['qc_upload_receipt'] ? $companyConfiguration['qc_upload_receipt'] : $qc_upload_receipt;
}
$crm_pro_search=explode(",",$companyConfiguration['crm_pro_search']);
$purchase_pro_search=explode(",",$companyConfiguration['purchase_pro_search']);
$production_pro_search=explode(",",$companyConfiguration['production_pro_search']);
$sales_pro_search=explode(",",$companyConfiguration['sales_pro_search']);
$bom_pro_search=explode(",",$companyConfiguration['bom_pro_search']);
$sales_party_show=explode(",",$companyConfiguration['sales_party_show']);
$purchase_party_show=explode(",",$companyConfiguration['purchase_party_show']);

// print_r($rel['inventory_management']);
// Amish Soni End 12-01-2021
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>COMPANY SETTING</title>
	<?php include_once('../include/include_css_file.php');?>
	<style>
	.btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
		z-index:2;
		background-color: #bbdce6;
	}
	.control-label{
		font-weight: bold;
	}
</style>

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
							<header class="panel-heading" style="padding-bottom: 20px;">
								<h3><?=$mode?> Company Setting
								</h3>
							</header>

							<div class="">
								<ul class="breadcrumb no_padding">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="javascript:;">Setting</a></li>
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
								Edit Company Setting
								<!-- Amish Soni Start 12-01-2021 -->
								<span class="tools pull-right">
									<a href="javascript:;" class="fa fa-chevron-down"></a>
								</span>
								<!-- Amish Soni End 12-01-2021 -->
							</header>	
							<div class="panel-body ">
								<form class="form-horizontal" role="form" id="company_configuration" action="javascript:;" method="post" name="company_configuration">
									<div class="row">
										<div class="col-md-10">
											<h4>Product Searching: </h4>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">CRM</label>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="crm_pro_search[]" value="item" <?=(in_array('item',$crm_pro_search)) ? "checked" : ""; ?>> Item No
												</div>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="crm_pro_search[]" value="drawing" <?=(in_array('item',$crm_pro_search)) ? "checked" : ""; ?>> Drawing No
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Purchase</label>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="purchase_pro_search[]" value="item" <?=(in_array('item',$purchase_pro_search)) ? "checked" : ""; ?>> Item No
												</div>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="purchase_pro_search[]" value="drawing" <?=(in_array('drawing',$purchase_pro_search)) ? "checked" : ""; ?>> Drawing No
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Production</label>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="production_pro_search[]" value="item" <?=(in_array('item',$production_pro_search)) ? "checked" : ""; ?>> Item No
												</div>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="production_pro_search[]" value="drawing" <?=(in_array('drawing',$production_pro_search)) ? "checked" : ""; ?>> Drawing No
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Sales</label>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="sales_pro_search[]" value="item" <?=(in_array('item',$sales_pro_search)) ? "checked" : ""; ?>> Item No
												</div>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="sales_pro_search[]" value="drawing" <?=(in_array('drawing',$sales_pro_search)) ? "checked" : ""; ?>> Drawing No
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">BOM</label>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="bom_pro_search[]" value="item" <?=(in_array('item',$bom_pro_search)) ? "checked" : ""; ?>> Item No
												</div>
												<div class="col-md-3 col-xs-4">
													<input type="checkbox" name="bom_pro_search[]" value="drawing" <?=(in_array('drawing',$bom_pro_search)) ? "checked" : ""; ?>> Drawing No
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<h4>Party Show Permission: </h4>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Sales</label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="sales_party_show[]" id="sales_party_show" multiple>
														<?=get_all_groups($dbcon,"","",$sales_party_show)?>				
													</select>
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Purchase</label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="purchase_party_show[]" id="purchase_party_show" multiple>
														<?=get_all_groups($dbcon,"","",$purchase_party_show)?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<h4>Item Code: </h4>
										</div>
										<div class="col-md-10">
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
										<div class="col-md-10">
											<h4>PO Terms & Conditions: </h4>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">PO Terms & Conditions: </label>
												<div class="col-md-9 col-xs-9">
													<textarea class="form-control" placeholder="PO Terms & Conditions" name="po_terms_conditions" id="po_terms_conditions" ><?=$companyConfiguration['po_terms_conditions']?></textarea>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<h4>how to invoice time so product load : </h4>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Sales Time How to Load: </label>
												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['sales_time_load_pro'] == 0){ echo "active";}?>">
															<input type="radio" name="sales_time_load_pro" id="sales_time_load_pro1" autocomplete="off" value="0" <?php if($companyConfiguration['sales_time_load_pro'] == 0){ echo "checked";}?>  > Product Load Wise
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['sales_time_load_pro'] == 1){ echo "active";}?>" >
															<input type="radio" name="sales_time_load_pro" id="sales_time_load_pro2" autocomplete="off" value="1" <?php if($companyConfiguration['sales_time_load_pro'] == 1){ echo"checked"; }?>> Product Dropdown Wise
														</label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<h4> Trading Stock </h4>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Trading Stock? </label>
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

										<div class="col-md-10">
											<h4>Assign Users to Ledger and Customer : </h4>
										</div>
										<div class="col-md-10">
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
										<div class="col-md-10">
											<h4>Other Permissions : </h4>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Manage Inventory *: </label>
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel['inventory_management'] == 0){ echo "active";}?>">
															<input type="radio" name="inventory_management" id="inventory_management1" autocomplete="off" value="0" <?php if($rel['inventory_management'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($rel['inventory_management'] == 1){ echo "active";}?>" >
															<input type="radio" name="inventory_management" id="inventory_management2" autocomplete="off" value="1" <?php if($rel['inventory_management'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">TCS Applicable*: </label>
												<div class="col-md-6">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($rel['tcs_applicable'] == 0){ echo "active";}?>">
															<input type="radio" name="tcs_applicable" id="tcs_applicable1" autocomplete="off" value="0" <?php if($rel['tcs_applicable'] == 0){ echo "checked";}?>  > No
														</label>
														<label class="btn btn-secondary <?php if($rel['tcs_applicable'] == 1){ echo "active";}?>" >
															<input type="radio" name="tcs_applicable" id="tcs_applicable2" autocomplete="off" value="1" <?php if($rel['tcs_applicable'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Daily Send Email*: </label>
												<div class="col-md-6">
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
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Send Mail Automatically*: </label>
												<div class="col-md-6">
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
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Send Email* : </label>
												<div class="col-md-6">
													<input type="text" class="form-control" placeholder="Send email" name="smtp_email" id="smtp_email"  value="<?=$rel['smtp_email']?>" required title="Enter Email Address" />
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Password*: </label>
												<div class="col-md-6">
													<input type="password" class="form-control" placeholder="Password" name="smtp_password" id="smtp_password"  value="<?=$rel['smtp_password']?>" required title="Enter Password" />
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Are you working with project wise manufacturing?*: </label>
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
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">GRN time upload receipt field Mandetory?*: </label>
												<div class="col-md-6">
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
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">QC time upload receipt field Mandetory?*: </label>
												<div class="col-md-6">
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
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Are you want to display the project wise item rate?*: </label>
												<div class="col-md-6">
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
										</div>
										<!-- Code by Sanat  Start ::  20/09/2021  -->
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Store Approval?* : </label>
												<div class="col-md-6 col-xs-11">
													<div class="btn-group btn-group-toggle" data-toggle="buttons">
														<label class="btn btn-secondary <?php if($companyConfiguration['store_approval'] == 0){ echo "active";}?>">
															<input type="radio" name="store_approval" id="store_approval1" autocomplete="off" value="0" <?php if($companyConfiguration['store_approval'] == 0){ echo "checked";}?>  > NO
														</label>
														<label class="btn btn-secondary <?php if($companyConfiguration['store_approval'] == 1){ echo "active";}?>" >
															<input type="radio" name="store_approval" id="store_approval2" autocomplete="off" value="1" <?php if($companyConfiguration['store_approval'] == 1){ echo"checked"; }?>> Yes
														</label>
													</div>
												</div>
											</div>
										</div>
										<!-- Code by Sanat  END ::  20/09/2021  -->
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Max Followup Date(In Days)* : </label>
												<div class="col-md-6">
													<input type="number" class="form-control" placeholder="Max Followup Date" name="max_followup_date" id="max_followup_date" maxlength="365" value="<?=$companySettings['max_followup_date']?>">
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">CRM Product Type :</label>
												<div class="col-md-6">
													<select class="select2" name="crm_pro_type[]" id="crm_pro_type" multiple data-placeholder="CRM Product Type">
														<?= get_product_type_company($dbcon,$crm_pro_type,''); ?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Sales order Product Type :</label>
												<div class="col-md-6">
													<select class="select2" name="so_pro_type[]" id="so_pro_type" multiple data-placeholder="Sales order Product Type">
														<?= get_product_type_company($dbcon,$so_pro_type,''); ?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Indent and PO Product Type :</label>
												<div class="col-md-6">
													<select class="select2" name="indent_po_pro_type[]" id="indent_po_pro_type" multiple data-placeholder="Indent and PO Product Type">
														<?= get_product_type_company($dbcon,$indent_po_pro_type,''); ?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-10">
											<div class="form-group">
												<label class="col-md-3 control-label">Quotation Print First Page Content* :</label>
												<div class="col-md-9">
													<textarea class="form-control" placeholder="Quotation Print First Page Content" name="quotation_print_content" id="quotation_print_content" ><?= stripslashes($quotation_print_content)?></textarea>
												</div>
											</div>
										</div>
										<div class="col-md-3"></div>
										<div class="col-md-3">
											<button type="submit" class="btn btn-success">Submit</button>
										</div>					 	
									</div><!--Vendor row end-->	
									<input type='hidden' name='mode' id='mode' value='company_configuration' />
									<input type='hidden' name='setting_id' id='setting_id' value='<?php echo $setting_id; ?>' />
									<input type='hidden' name='company_conf_id' id='company_conf_id' value='<?php echo $company_conf_id; ?>' />
									<input type='hidden' name='eid' id='eid' value='<?=$rel['company_id']?>' />
								</form>
							</div>
						</section>
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
		<script src="<?=ROOT?>js/app/setting.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$(".form_datetime-meridian").datetimepicker({
				format: "dd-mm-yyyy HH:ii P",
				showMeridian: true,
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"
			});
			function cb(start, end) {
				$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			}
			cb(moment().subtract(29, 'days'), moment());

			$('.datepikerdemo').daterangepicker({       
				locale: {
					format: 'DD-MM-YYYY'
				},
				"autoApply": true,	
				"startDate": $('#valid_till_start_date').val(),
				"endDate": $('#valid_till_end_date').val(),	
				ranges: {
					'Today': [moment(), moment()],
					'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
					'Last 7 Days': [moment().subtract(6, 'days'), moment()],
					'Last 30 Days': [moment().subtract(29, 'days'), moment()],
					'This Month': [moment().startOf('month'), moment().endOf('month')],
					'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			}, cb);
			$('.date-set').click(function(){
				$('.datepikerdemo').trigger('click')
			});
			function trancate_tables(val)
			{
				var r= confirm(" Are you want to Remove Data ?");
				if(r) {
					Loading(true);	
					window.location=root_domain+'backup/'+val;
				}
			}
			CKEDITOR.replace( 'po_terms_conditions', {
				enterMode: CKEDITOR.ENTER_BR
			});
			CKEDITOR.replace( 'quotation_print_content', {
				enterMode: CKEDITOR.ENTER_BR
			});
			/*CKEDITOR.replace( 'address', {
				enterMode: CKEDITOR.ENTER_BR
			});
			CKEDITOR.replace( 'logo_content', {
				enterMode: CKEDITOR.ENTER_BR
			});
			CKEDITOR.replace( 'condition', {
				enterMode: CKEDITOR.ENTER_BR
			}); */
		</script>
	</body>
	</html>
