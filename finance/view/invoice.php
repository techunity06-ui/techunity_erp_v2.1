<?php 
session_start();
set_time_limit(0);
$path = '../../';
$include = '../../include/';
$include1 = '../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_sub_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$form="Invoice";
$branch_id = $_SESSION['branch_id'];
$countryid='101';$stateid='1';$cityid='1';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_INVOICE_CREATE,
	FINANCE_INVOICE_EDIT,
	FINANCE_CREATE_INVOICE,
	FINANCE_SPARE_TO_INVOICE,
	FINANCE_INVOICE_SO
]);

$company_config = getCompanyConfiguration($dbcon);
$getspecialConfiguration=getspecialConfiguration($dbcon);
$sales_party_show = $company_config['sales_party_show'];
$crm_user_type 	  = $company_config['crm_user_type'];
$invoiceid 		  = '';
$quot_type		  = 0;	 
if(strpos($_SERVER['REQUEST_URI'], "invoiceedit")==true){
	if(!in_array(FINANCE_INVOICE_EDIT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Edit";
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_invoice where invoice_id=$invoiceid";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	if(!$rel){
		header("Location: ".ROOT."invoice_list");
	}

	$order_date='';$dispatch_date='';
	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
	{
		$order_date=date('d-m-Y',strtotime($rel['order_date']));
	}
	if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
	{
		$dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
	}
	if($rel['$lr_date']!="1970-01-01" && $rel['$lr_date']!="0000-00-00")
	{
		$lr_date=date('d-m-Y',strtotime($rel['$lr_date']));
	}
	$currency_enable="";
	if($rel['currency_enable']==1){
		$currency_enable="checked";
	}
	$currency_rate="";
	if($rel['currency_rate']){
		$currency_rate = $rel['currency_rate'];
	}
	$currency_id = $rel['currency_id'];
	$disable = 'disabled';
	$sales_order_id = $rel['sales_order_id'];
	$invoice_no=$rel['invoice_no'];
	$challan_no=$rel['challan_no'];
	$load_inv_type=$rel['invoicetype_id'];
	$cust_id=$rel['cust_id'];
	$edit_branch_id=$rel['branch_id'];
	$order_no=$rel['order_no'];
	$sales_ledger=$rel['sales_ledger_id'];
	$readonly = "readonly='readonly'";
	$enable_transport=0;
	$quot_type = $rel['quot_type'];
	$so_voucher_type = ($rel['enable_transport']==1) ? '' : SO_VOUCHER;
	$invoice_terms_conditions = $rel['invoice_condition'];
}
else if(strpos($_SERVER['REQUEST_URI'], "invoiceso")==true){
	$mode="Add";
	$viewmode="invoiceso";
 	$sotrn=$dbcon->real_escape_string($_REQUEST['id']);
 	
 	$query="select * from tbl_sales_ordertrn as trn 
	left join tbl_sales_order as so on so.sales_order_id=trn.sales_order_id
	where trn.sales_ordertrn_id=$sotrn";
		//echo $sotrn;
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$enable_transport = $rel['enable_transport'];

	if(!$rel){
		header("Location: ".ROOT."invoice_list");
	}

	$order_date='';$dispatch_date='';
	if($rel['po_date']!="1970-01-01" && $rel['po_date']!="0000-00-00")
	{
		$order_date=date('d-m-Y',strtotime($rel['po_date']));
	}
	if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
	{
		$dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
	}
	if($rel['lr_date']!="1970-01-01" && $rel['lr_date']!="0000-00-00")
	{
		$lr_date=date('d-m-Y',strtotime($rel['lr_date']));
	}

	$currency_enable="";
	if($rel['currency_enable']==1){
		$currency_enable="checked";
	}
	$currency_rate="1";
	if($rel['currency_rate']){
		$currency_rate = $rel['currency_rate'];
	}
	$currency_id = $rel['currency_id'];
	$sales_order_id=$rel['sales_order_id'];
	$load_inv_type='8';
	$cust_id=$rel['cust_id'];
	$edit_branch_id=$rel['branch_id'];
	$order_no=$rel['po_no'];
	$sales_ledger=$rel['sales_ledger_id'];
	$readonly = "readonly='readonly'";
	$rel['order_user_id'] = $rel['user_id'];

	if($enable_transport==1)
	{
		$sel_trans = $dbcon->query("select * from tbl_transport_transaction where transport_transaction_table_id='$sales_order_id'");
		$row_trans = brp_mysqli_fetch_array($sel_trans);

		$transport_id_so = $row_trans['transport_transaction_id'];
	}

	$so_voucher_type = SO_VOUCHER;
		//echo $so_voucher_type;
}
else if(strpos($_SERVER['REQUEST_URI'], "spare_to_inv")==true){
	if(!in_array(FINANCE_INVOICE_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";
	$date=date('d-m-Y');
	$complaint_id=$dbcon->real_escape_string($_REQUEST['id']);
	$comp_query="select * from tbl_complaint where complaint_id=$complaint_id";
	$comp_rel=mysqli_fetch_assoc($dbcon->query($comp_query));
	$cust_id=$comp_rel['cust_id'];
	$rel['install_type']='no';
	$load_inv_type='8';
	$rel['branch_id']=$comp_rel['branch_id'];
	$sales_ledger = $dbcon->query("SELECT l_id FROM `tbl_ledger` WHERE `l_group` = ".SALES_ACCOUNTS)
	->fetch_object()->l_id;
	$currency_rate="1";
	$currency_id = $_SESSION['currency_id'];
}
else{
	if(!in_array(FINANCE_INVOICE_CREATE,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Add";
	$date=date('d-m-Y');
	$order_date=date('d-m-Y');
	$load_inv_type='8';
	$lr_date=date('d-m-Y');
	$sales_ledger = $dbcon->query("SELECT l_id FROM `tbl_ledger` WHERE `l_group` = ".SALES_ACCOUNTS)
	->fetch_object()->l_id;
	$enable_transport=0;
	$so_voucher_type = SO_VOUCHER;
	$currency_rate="1";
	$currency_id = $_SESSION['currency_id'];
}
$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));

if($mode == "Add"){
	$invoice_terms_conditions = $set_head['conditions'];
}


$financial_year=get_financial_year_new($dbcon);

$discount_editable="";
if($company_config['invoice_discount_editable']==0){
	$discount_editable = "readonly='readonly'";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>INVOICE</title>
	<?php include_once($include.'include_css_file.php');?>
	<style type="text/css">
		.currency_icon{
			color: green;
			font-size: 12px;
			font-weight: bold;
		}
		.info_line
		{
			background-color:#337AB7 !important;
			color:#FFFFFF !important;
			padding:10px;
			text-align:center !important;
			font-weight:bold;
			font-size:14px;
		}
	</style>
</head>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once($include.'include_top_menu.php');?>
		<?php include_once($include.'left_menu.php');?>
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<section class="panel">
							<header class="panel-heading">
								<h3 style="float:left;"> <?=$mode .' '.$form?></h3>
								<?phpinclude_once($include."head_menu.php") ?>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.FINANCE_ROOT.'invoice_list'?>">Invoice List</a></li>
								</ul>
							</div>
						</section>
					</div>	
				</div>
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								New <?=$form?>
							</header>	
							<div class="panel-body">
								<form class="form-horizontal" role="form" id="invoice_add" action="javascript:;" method="post" name="invoice_add">
									<input type="hidden" name="cust_stateid" id="cust_stateid">
									<div class="row">
										<div class="col-md-12 info_line" style="margin-bottom: 5px;">Customer Details</div>
										<?if($company_config['branch_wise_manage']==1){?>
										<div class="col-md-4">
											<?php echo getBranchBox($dbcon, $branch_id, $edit_branch_id, false, true, '','4','8'); ?>
										</div>
									<?php} ?>
										<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-3 control-label">Company*</label>
												<div class="col-md-6 col-xs-11">
													<!-- load_consignee(this.value);check_due_payment(this.value);load_billdata(this.value);show_tcs_row(this.value); -->
													<select class="select2" name="cust_id" id="cust_id" <?= $disable ?> onChange="get_statecode(this.value);get_grossbalance(this.value);get_invoice_total_tax();get_gtotal();get_ledger_details(this.value);get_so_detail(this.value)" tabindex="3">
														<?=
														getcust($dbcon,$cust_id,$sales_party_show,0);?>	
													</select>
													<?php
													if($disable){
														echo '<input type="hidden" name="cust_id" id="cust_id" value="'.$cust_id.'">';
													}
													?>
													<strong style="display:none;color:green" id="gross">Gross balance : <span class="gross"></span></strong><br><strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong><br><strong id="sez_enable_text" style="display:none;color:red">This Party Is SEZ Enabled</strong>

												</div>	

												<button accesskey="n" class="btn btn-round btn-info btn-xs" title="Short-Cut To Open PopUp, Shift + Alt + n " type="button" data-toggle="modal" value="R1" onclick="showledger();"><i class="fa fa-plus"></i> </button>&nbsp;&nbsp;
											</div>
											<!-- <a href="#"  data-original-title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="tooltip" data-placement="top" ><i class="fa fa-info-circle fa-sm" style="color: black;"></a></i> -->
										</div>
										<div class="col-md-2">
											<div class="form-group">
												<label class="col-md-8 control-label" style="white-space:nowrap">Same Consignee</label>
												<div class="col-md-3 col-xs-11">
													<input type="checkbox" name="enable_consignee" id="enable_consignee" style="height: 15px;" value="1" <?php if($mode=='Edit' && $rel['enable_consignee']==1){ echo "checked"; } else if($mode=='Edit' && $rel['enable_consignee']==0){ echo ""; }else{ echo "checked"; }  ?> onChange="load_consignee_new()">	<input type="hidden" id="edit_consignee_party" value="<?php if($mode=='Edit' && $rel['enable_consignee']==0){ echo $rel['consignee_id']; }elseif($viewmode=="invoiceso" && $rel['enable_consignee']==0){ echo $rel['consignee_id']; } ?>" />
												</div>
											</div>
										</div>

										<div class="col-md-4" id="consignee_id_div" style="display:none">
											<div class="form-group">
												<label class="col-md-4 control-label">Select Consignee</label>
												<div class="col-md-6 col-xs-11">
													<select class="form-control" name="consignee_id" id="consignee_id">

													</select>

												</div>
												<div class="col-md-2 col-xs-11">
													<button type="button" id="viewcompany" onClick="add_consignee_open()" title="Add New Consignee" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i></button>
												</div>
											</div>
										</div>

										<?if($company_config['crm_sales_order_user_selecation']==1){ ?>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Users *</label>

													<div class="col-md-8 col-xs-11">
														<select class="select2" name="user_id" id="user_id" onchange="show_data()">
															<option value="">Select User</option>
															<?=get_assign_users($dbcon, $rel['order_user_id'], " and user_type in(".$crm_user_type.")");?>
														</select>
													</div>
												</div>
											</div>
										<?php}else{ ?>
											<input type="hidden" id="user_id" name="user_id" value="<?=$_SESSION['user_id']?>">
										<?php} ?>

										<div class="col-md-12 info_line" style="margin-bottom: 5px;">Invoice Details</div>
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Sales Ledger*</label>
												<div class="col-md-6 col-xs-11" >
													<?php 
													$sales_grp_array=implode(",",array(SALES_ACCOUNTS));
													$sales_account = isset($rel['sales_ledger_id']) ? $rel['sales_ledger_id'] : SALES_ACCOUNT ;

													?>
													<select <?= $disable ?> class="select2" name="sales_ledger_id" id="sales_ledger_id"  title="Select Sales Ledger" tabindex="1">
														<?=f_get_group_ledger($dbcon,$sales_grp_array,$sales_account);?>
													</select>
													<?php
													if($disable){
														echo '<input type="hidden" name="sales_ledger_id" id="sales_ledger_id" value="'.$sales_account.'">';
													}
													?>
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Series*</label>
												<div class="col-md-6 col-xs-11" >
													<select <?= $disable ?> class="select2" name="invoicetype_id" id="invoicetype_id" tabindex="2" onchange="load_pono(this.value)" required>
														<option value="">--Select Series--</option>
														<?php$chkseri = $dbcon->query("SELECT * FROM tbl_invoicetype WHERE status = 0 AND type_id = 37 AND company_id = ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
														while($getseri = brp_mysqli_fetch_assoc($chkseri)){ ?>
															<option value="<?=$getseri['invoicetype_id']?>" <?=($getseri['invoicetype_id'] == $rel['invoicetype_id']) ? "selected" : "" ?>><?=$getseri['invoice_type']?></option>
														<?php} ?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Invoice No *</label>
												<div class="col-md-6 col-xs-11">
													<input id="invoice_no" name="invoice_no" type="text" class="form-control" title="Enter Invoice No" value="<?=$invoice_no?>" placeholder="Invoice No" tabindex="4" readonly required>		
												</div>
											</div>
										</div>
										
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Invoice Date*</label>
												<div class="col-md-6 col-xs-11">
													<input id="invoice_date" name="invoice_date" type="text" class="form-control default_date required valid" title="Date" value="<?phpif($mode=='Add'){echo $date;}else if($mode=='Edit'){echo date('d-m-Y',strtotime($rel['invoice_date']));}?>" placeholder="Invoice Date" tabindex="5" autocomplete="off">
												</div>
											</div>	
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Due Date*</label>
												<div class="col-md-6 col-xs-11">
													<input id="invoice_due_date" name="invoice_due_date" type="text" class="form-control required valid due_date_class" title="Date" value="<?phpif($mode=='Add'){echo $date;}else if($mode=='Edit' && $rel['invoice_due_date']!="0000-00-00"){ echo date('d-m-Y',strtotime($rel['invoice_due_date']));} else { echo ""; }  ?>" placeholder="Due Date" tabindex="6" autocomplete="off" onchange="check_previos_date(this.value)">
													<strong class="invoice_due_date_error" style="color:red"></strong>
												</div>
											</div>	
										</div>
										<?if($company_config['enable_installation_type']==1){?>
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Installation type *</label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="install_type" id="install_type" tabindex="16">
														<option value="yes" <?=$rel['install_type']=='yes'?'selected':''?> <?php if(!isset($rel['install_type'])){ echo "selected"; } ?>>Yes</option>
														<option value="no" <?=$rel['install_type']=='no'?'selected':''?> >No</option>
													</select>
												</div>
											</div>									
										</div>
									<?php} ?>
										<div class="col-md-4">
											<input id="dc_enable" name="dc_enable" type="checkbox" class="" title="Other Name"  value="1" style="display: none;" checked>
											<div class="form-group">
												<label class="col-md-4 control-label">D.C. No *</label>
												<div class="col-md-6 col-xs-11">
													<input id="challan_no" name="challan_no" type="text" class="form-control" title="Enter Challan No" value="<?=$mode=='Edit'?$rel['challan_no']:''?>" placeholder="Challan No" tabindex="8" required>
												</div>
											</div>
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">D.C. Date*</label>
												<div class="col-md-6 col-xs-11">
													<input id="challan_date" name="challan_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?phpif($mode=='Add'){ echo date('d-m-Y');}else if($mode=='Edit'){ if($rel['challan_date']=='0000-00-00'){ echo ""; } else { echo date('d-m-Y',strtotime($rel['challan_date']));} } ?>" placeholder="Challan Date" tabindex="9">
												</div>
											</div>	
										</div>

										<div class="col-md-4">
											<input id="po_enable" name="po_enable" type="checkbox" class="" title="" value="1" style="display: none;" checked>
											<div class="form-group">
												<label class="col-md-4 control-label">P.O. No *</label>
												<div class="col-md-6 col-xs-11">
													<input id="order_no" name="order_no" type="text" class="form-control" title="Enter P.O. No" value="<?=$mode=='Edit'?$rel['order_no']:$order_no?>" placeholder="P.O. No" tabindex="11">
												</div>
											</div>
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">P.O. Date*</label>
												<div class="col-md-6 col-xs-11">
													<input id="order_date" name="order_date" type="text" class="form-control default-date-picker  valid" title="Date" value="<?phpif($mode=='Add'){ echo $order_date;}else if($mode=='Edit'){ if($rel['order_date']=='0000-00-00'){ echo ""; } else { echo date('d-m-Y',strtotime($rel['order_date']));} } ?>" placeholder="P.O. Date" tabindex="12">
												</div>
											</div>	
										</div>
										<div class="col-md-4">
											<input id="currency_enable" tabindex="13" name="currency_enable" type="checkbox" class="" title="" value="1" style="display: none;" checked>
											<div class="form-group">
												<label class="col-md-4 control-label">Convert Currency *</label>
												<div class="col-md-6 col-xs-11">
													<select class="select2" name="currency_id" id="currency_id" onChange="get_symbol();currency_rate_c();" tabindex="14">
														<?=getcurrency($dbcon,$currency_id);?>
													</select>

												</div>
											</div>
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Rate *</label>
												<div class="col-md-6 col-xs-11">
													<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=$currency_rate?>" placeholder="" tabindex="15">
												</div>
											</div>	
										</div>

										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label">Carton *</label>
												<div class="col-md-6 col-xs-11">
													<input id="num_of_parcel" name="num_of_parcel" type="text" class="form-control" title="Carton" value="<?=$rel['num_of_parcel']?>" placeholder="Carton" tabindex="15">
												</div>
											</div>	
										</div>
										<?if($company_config['enable_material_center']==1){?>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Material Center</label>
													<div class="col-md-6 col-xs-10" >
														<select class="select2" name="sale_material_center" id="sale_material_center" title="Select Godown">
															<?= get_all_godown($dbcon,$rel['sale_material_center'],"");?>
														</select>
													</div>
												</div>
											</div>
										<?php} ?>
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4 control-label" >Payment Terms</label>
												<div class="col-md-6 col-xs-11">                                                    
													<select class="select2" onchange="change_due_date(this.value);" name="payment_terms" id="payment_terms" >
														<?=getpaymentterms($dbcon,$rel['payment_terms']);?>
													</select>
												</div>
												<!-- <input type="button" name="addproduct2" id="addproduct2" data-toggle="modal" data-target="#bs-payterms-modal-lg" class="btn btn-primary btn-xs" value="+" title="Add Payment Terms" /> -->
											</div>
										</div>
										<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Sales Type</label>
													<div class="col-md-6 col-xs-11">
													
														<select class="form-control" name="sales_type" id="sales_type">
															<option value="1" <?php if($rel['sales_type']==1){ echo "selected"; } else{ echo ""; } ?>>Item Wise Tax</option>
															<option value="2" <?php if($rel['sales_type']==2){ echo "selected"; } else{ echo ""; } ?> >Merchant</option>
															<option value="3" <?php if($rel['sales_type']==3){ echo "selected"; } else{ echo ""; } ?> >SEZ</option>
															<option value="4" <?php if($rel['sales_type']==4){ echo "selected"; } else{ echo ""; } ?> >GST 0%</option>
															<option value="5" <?php if($rel['sales_type']==5){ echo "selected"; } else{ echo ""; } ?> >GST 5%</option>
															<option value="6" <?php if($rel['sales_type']==6){ echo "selected"; } else{ echo ""; } ?> >GST 12%</option>
															<option value="7" <?php if($rel['sales_type']==7){ echo "selected"; } else{ echo ""; } ?> >GST 18%</option>
																	<option value="8" <?php if($rel['sales_type']==8){ echo "selected"; } else{ echo ""; } ?> >GST 24%</option>		
														</select>
													</div>
												 </div>
											</div>
										<?php //if(strpos($_SERVER['REQUEST_URI'], "invoiceso")!=true) { ?>
											<div class="col-md-4" id="sales_order_div">
												<div class="form-group">
													<label class="col-md-4 control-label" style="white-space:nowrap">Choose Sales Order</label>
													<div class="col-md-6 col-xs-11">
														<select class="select2" name="is_sales_order" id="is_sales_order" onChange="load_sales_order_popup(this.value)" tabindex="18" <?= $disable ?> >
															<option value="no" <?=($rel['is_sales_order']=='0')?'selected':''?>>No</option>
															<option value="yes" <?=($rel['is_sales_order']=='1')?'selected':''?>>Yes</option>
														</select>
														<a id="sales_order_link" href="#" onclick="load_sales_order_popup('yes')" style="display: none;">Choose Sale Order</a>
														<?php
														if($disable){
															echo '<input type="hidden" name="is_sales_order" id="is_sales_order" value="'.$rel['is_sales_order'].'">';
														}
														?>
													</div>
												</div>									
											</div>
										<?php //} ?>

										<div class="col-md-4">
				                            <div class="form-group">
				                                <label class="col-md-4 control-label">Invoice Type</label>
				                                <div class="col-md-8"> 
				                                    <label class="col-md-6" style="font-weight:bold;"><input type="radio" id="quot_type_domestic" name="quot_type" onclick="load_typeswise_terms(<?=$invoiceid?>);" value="0" <?=($quot_type!='1')?'checked':''?>> Domestic</label>
				                                    <label class="col-md-5 " style="font-weight:bold;"><input type="radio" id="quot_type_export" name="quot_type" onclick="load_typeswise_terms(<?=$invoiceid?>);" value="1" <?=($quot_type=='1')?'checked':''?>> Export</label>
				                                </div>
				                            </div>  
				                        </div>
										<?php if($company_config['enable_hypothication']==1) { ?>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="white-space:nowrap">Enable Hypothication</label>
													<div class="col-md-8 col-xs-11">

														<input type="checkbox" name="check_hypothication" id="check_hypothication" style="width:20%;height:25px;" onChange="enable_hypothication()" value="1" <?php if($mode=='Edit' && $rel['check_hypothication']==1){ echo "checked"; }  ?>>	
													</div>
												</div>
											</div>

											<div class="col-md-4" id="hypo_bank_div" style="display:none">
												<div class="form-group">
													<label class="col-md-4 control-label">Select Bank</label>
													<div class="col-md-6 col-xs-11">
														<select class="form-control" name="hypo_bank" id="hypo_bank">
															<?=get_all_bank($dbcon,$rel['hypo_bank']);?>
														</select>
													</div>
												</div>
											</div>
										<?php } ?>
										<div class="col-md-4" style="display:none" id="salesorder_div">
											<div class="form-group">
												<label class="col-md-4 control-label">Sales Order No</label>
												<div class="col-md-8 col-xs-11">
													<select id="sales_order_id" name="sales_order_id[]" class="select2" title="Select Sales Order No" placeholder="Select Sales Order No" multiple="multiple" onchange="add_sales_order()" <?=$readonly?> tabindex="19" disabled >	
														
													</select>
													<input type="hidden" id="salesorderid" name="salesorderid[]">	
												</div>
											</div>
										</div>

										<div class="col-md-4" >
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Kind Attn.</label>
                                                <div class="col-md-8 col-xs-11">
                                                    <select class="form-control" name="kind_attn" id="kind_attn" title="Select Kind Attn.">
                                                        <option value='' >select Kind Attn.</option>
                                                    </select>
                                                    <input type="hidden" name="kind_attn_hidden" id="kind_attn_hidden" value="<?=$rel['kind_attn']?>" />
                                                </div>
                                            </div>
                                        </div>

										<?php//if($getspecialConfiguration['power_drive']==1){ ?>
													<!-- <div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >Orange</label>
															<div class="col-md-6 col-xs-11">
																<input id="orange" name="orange" type="text" class="form-control"  value="<php echo $rel['orange']; ?>" placeholder="Orange">
															</div>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >MFG.</label>
															<div class="col-md-6 col-xs-11">
																<input id="mfg" name="mfg" type="text" class="form-control" title="mfg" value="<php echo $rel['mfg']; ?>" placeholder="MFG.">
															</div>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >Trading</label>
															<div class="col-md-6 col-xs-11">
																<input id="trading" name="trading" type="text" class="form-control" title="Trading" value="<php echo $rel['trading']; ?>" placeholder="Trading">
															</div>
														</div>
													</div>


													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >Reparing</label>
															<div class="col-md-6 col-xs-11">
																<input id="repairing" name="repairing" type="text" class="form-control" title="Reparing" value="<php echo $rel['repairing']; ?>" placeholder="Reparing">
															</div>
														</div>
													</div>

													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4 control-label" >Other</label>
															<div class="col-md-6 col-xs-11">
																<input id="other" name="other" type="text" class="form-control" title="Other" value="<php echo $rel['other']; ?>" placeholder="Other">
															</div>
														</div>
													</div> -->
												<?//}?>	
									</div>
									<div class="row">
										<div class="col-md-7" id="check_due_div" style="display:none">
											<div class="form-group">
												<label class="col-md-1 control-label"></label>
												<div class="col-md-7">
													<input type="checkbox" name="" id="check_due" onclick="enable_invoice()" tabindex="20" /> <strong>Click Here If U Still Want To Create Invoice </strong>
												</div>

											</div>	 
										</div>										
									</div>

									<div class="row">

										<div class="col-md-12">

											<div class="card">

												<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
													<li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Product Details</a></li>
													<li role="presentation" id="tab2"><a href="#product-desc" aria-controls="product-desc" role="tab" data-toggle="tab">Description</a></li>
												</ul>

												<div class="tab-content">
													<!-- Remaks Tab Start -->
													<div role="tabpanel" class="tab-pane active" id="product-details">

														<div class="form-group">
															<table cellspacing="10" style="border-spacing:10px; table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
																<tr id="field">
																	<?if($getspecialConfiguration['reciclar']==1){?>
																	<th width="12%" class="text-center">Parent Category</th>
																	<th width="12%" class="text-center">Category</th>
																	<?}?>
																	<th width="12%" class="text-center">Type</th>
																	<th width="28%" class="text-center">Product Detail</th>
																	<th width="10%" class="text-center">Per</th>
																	<th width="10%" class="text-center">Quantity</th>
																	<th width="10%" class="text-center">Rate  <span class="currency_icon"></span></th>
																	<th width="10%" class="text-center" style="display:none">Unit</th>
																	<th width="10%">Discount <span class="currency_icon"></span></th>
																	<th width="10%" class="text-center">Amount <span class="currency_icon"></span></th>
																	<th width="10%"></th>
																</tr>
																<input type="hidden" value="<?=$company_config['enable_negative_qty']?>" name="isstockngative" id="isstockngative"/>
																<tr id="field1">
																	<?if($getspecialConfiguration['reciclar']==1){?>
																	<td>
																		<select class="select2" name="parent_cat_id" id="parent_cat_id" title="Parent Category" onchange="load_parent_cat()">
                                                                            <?=get_all_category($dbcon,0);?>
                                                                        </select>
																	</td>
																	<td>
																		<select class="select2" name="cat_id" id="cat_id" title="Select Category" <?if($getspecialConfiguration['reciclar'] ==1){?> onchange="product_load('')"<?}?>>
                                                                            <?=get_all_category($dbcon,0);?>
                                                                        </select>
																	</td>
																	<?}?>
																	<td style="vertical-align:top;" width="12%">
																		<select class="select2" name="product_type_sel" id="product_type_sel" onChange="product_load(this.value);" title="Select Product Type" tabindex="21">
																			<?=get_product_type_company($dbcon,'0');?>
																		</select>
																	</td>
																	<td style="vertical-align:top;" width="28%">
																			<!--<select class="select2_product" tabindex="22" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);get_hsn(this.value);"  style="width:100% !important">
																				<?//=getproduct($dbcon,0,'0,1,2,3,4,5')?>
																			</select> -->
																			<div class="col-md-9">
																				<input id="product_id" name="product_id" style="width:100%;" placeholder="Select Product" onchange="load_productdetail(this.value);" autocomplete="off" />
																				<input type="text" id="product_name_hid" value="" style="display:none" class="form-control" readonly />
																				<strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>
																				<strong class="product_stock_label" style="display:none;color:green"> , Current Stock : <span id="product_stock_label"></span></strong><br/>
																				<strong class="taxtype" style="display:none;color:blue">TAX : <span id="taxtype"></span></strong>
																				<strong id="product_stock_count_check" style="display: none;"></strong>
																				<br/><br/>
																			</div>
																			<?phpif($getspecialConfiguration['oilfield_permission']==1){ ?>
																			<div class="col-md-3">   
																				<button accesskey="p" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="R1" title="Short-Cut To Open PopUp, Shift + Alt + p " onclick="showproduct();"><i class="fa fa-plus"></i> Add Product</button>
																				<!-- <a href="#"  data-original-title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="tooltip" data-placement="top" ><i class="fa fa-info-circle fa-sm" style="color: black;"></a></i> -->
																			</div>
																			<?php} ?>
																			

																		</td>	
																		<td>
				                                                            <select class="form-control"  title="Select Unit" placeholder="Unit" name="rate_unit_id" id="rate_unit_id" onchange="load_product_unit();">
				                                                                <?//=getunit($dbcon,0);?>
				                                                                <option value="0">Select Unit</option>
				                                                            </select><br>
				                                                            <input type="hidden" name="p_qty" id="p_qty">
				                                                        </td>
																		<td style="vertical-align:top;" width="10%">
																			<div id="convert_unit_block" style="display:none;" >
					                                                            <input type="text"  title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(1);"onChange="get_discount('per');" />
					                                                            <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
					                                                            <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
					                                                            <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show">  </span>
					                                                        </div>
					                                                        <div id="base_unit_block">
																				<input type="text" min="0" id="product_qty" name="product_qty"  class="form-control numbersOnly" onKeyUp="product_convert_qty(2);get_amount();is_product_stock_count();" value="" tabindex="24"  />
																				<input type="hidden" name="unitid" id="unitid" value="" />
																				<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show" >  </span>
																				<input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
																			</div>
																			<div id="stock_alert" style="display: none">
																				<strong>Your stock is going negative . Do you still want to continue ?</strong>
																				<button type="button" id="con_without_stock">yes</button>
																				
																			</div>
																			<input type="hidden" name="product_stock" id="product_stock" value=''>
																			<input type="hidden" id="trans_type" name="trans_type" value="">
																			<input type="hidden" id="trans_stock" name="trans_stock" value="">
																			<input type="hidden" id="trans_id" name="trans_id" value="">
																		</td>
																		<td style="vertical-align:top;" width="10%">
																		<input type="text"  title="Enter Rate"  id="product_rate" name="product_rate" onKeyUp="get_amount();get_discount('per');" onchange="get_discount('per');" class="form-control numbersOnly" value="" tabindex="25" /><br/>
																			<strong class="pro_amt" style="display:none;color:green"> Product Rate : <span id="pro_amt"></span></strong>
																			<br/>
																			<input type="hidden" id="taxper">
																			<strong class="taxrate" style="display:none;color:green"> Tax Rate : <span id="taxrate"></span></strong>
																			<br/>
																			<button type="button" title="Show Previous Rate History" name="rate_history" id="rate_history" onclick="load_rate_hist()" class="btn btn-info"><i class="fa fa-eye"></i> show</button>
																		</td>
																		<td style="vertical-align:top;display:none" width="10%">
																			<select class="select2"  title="Select Unit" name="unit_id" id="unit_id" tabindex="26">
																				<?=getunit($dbcon,0);?>
																			</select>
																		</td>
																		<td style="vertical-align:top;" width="10%">
																			<input type="text" title="Enter Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control numbersOnly" placeholder="in Rs." tabindex="27" <?=$discount_editable?> /><br/>
																			<input type="text"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control numbersOnly" placeholder="in %" max="100" tabindex="28" <?=$discount_editable?> />
																		</td>
																		<td style="vertical-align:top;" width="10%"> 
																			<input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control" onmouseover="this.title=this.value" tabindex="29"  />
																		</td>
																		<!-- <td style="vertical-align:top;"> 
																			<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add" tabindex="30" />	
																		</td> -->
																		<td style="vertical-align:top;" rowspan="3"> 
																			<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary product_add_direct" value="Add" tabindex="30" />	

																			<input type="button"  name="addrow" id="addrow" onClick="open_batch_wise_qty()"  class="btn btn-primary product_add_batch_wise" value="Add" />
																		</td>
																		<input type='hidden' name='edit_id' id='edit_id' value='' />
																		<input type='hidden' name='pro_cal_type' id='pro_cal_type' value='' />
																	</tr>

																	<?phpif($getspecialConfiguration['power_drive']==1){ ?>
																		<tr>
																			<th class="text-center">Orange</th>
																			<th class="text-center">MFG.</th>
																			<th class="text-center" colspan="2">Trading</th>
																			<th class="text-center">Reparing</th>
																			<th class="text-center">Other</th>
																		</tr>
																		<tr>
																			<td><input id="orange" name="orange" type="text" class="form-control" placeholder="Orange" onkeyup="calculate_orange()"><br><input id="orange_total" name="orange_total" type="text" class="form-control" readonly placeholder="Orange Total"></td>
																		<td><input id="mfg" name="mfg" type="text" class="form-control" title="mfg" placeholder="MFG." onkeyup="calculate_mfg()"><br><input id="mfg_total" name="mfg_total" type="text" class="form-control" title="mfg Total" placeholder="MFG. Total" readonly></td>
																		<td colspan="2"><input id="trading" name="trading" type="text" class="form-control" title="Trading" placeholder="Trading" onkeyup="calculate_trading()"><br><input id="trading_total" name="trading_total" type="text" class="form-control" title="Trading Total" placeholder="Trading Total" readonly></td>
																		<td colspan="2"><input id="repairing" name="repairing" type="text" class="form-control" title="Reparing" placeholder="Reparing" onkeyup="calculate_repairing()"><br><input id="repairing_total" name="repairing_total" type="text" class="form-control" title="Reparing Total" placeholder="Reparing Total" readonly></td>
																		<td><input id="other" name="other" type="text" class="form-control" title="Other" placeholder="Other" onkeyup="calculate_other()"><br><input id="other_total" name="other_total" type="text" class="form-control" title="Other Total" placeholder="Other Total" readonly></td>
																		</tr>
																	<?}?>
																</table>								
															</div>

														</div>

														<div class="tab-pane" id="product-desc" >
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group">
																		<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;">Description</label>
																		<div class="col-md-12">
																			<textarea class="form-control" id="product_des" name="product_des" placeholder="Enter Product Description"><?=$rel['description']?></textarea>
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
																	<input type="hidden" name="product_stock" id="product_stock" value=''>
																	<input type="hidden" id="trans_type" name="trans_type" value="">
																	<input type="hidden" id="trans_stock" name="trans_stock" value="">
																	<input type="hidden" id="trans_id" name="trans_id" value="">
																	<input type="hidden" id="isbatchwise" name="isbatchwise" value="">
																</td>


																<input type='hidden' name='edit_id' id='edit_id' value='' />
															</tr>
														</table>								
													</div>
												</div>
											</div>

										</div>




									</div>


								</div>

							</div>

							<div class="row">
								<div id="sale_productdata"></div>
							</div>

							<div class="row">

								<div class="col-md-6 tax_details">



								</div>

								<div class="col-md-6">

									<div class="form-group">
										<label class="col-md-5 control-label">Total * <span class="currency_icon"></span></label>
										<div class="col-md-5 col-xs-11">
											<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
										</div>
									</div>	
									<div class="sundryaddedwithtax">

									</div>
									<div class="invoiceTotalTax">

									</div>


												<!-- <div class="form-group">
													<label class="col-md-5 control-label">SGST</label>
													<div class="col-md-5 col-xs-11">
														<input id="packing" name="packing" type="number" class="form-control" title="packing" min="0"  value="" placeholder="Packing" onKeyUp="add_freight();" >
													</div>
												</div>
												
												<div class="form-group">
													<label class="col-md-5 control-label">TCS</label>
													<div class="col-md-5 col-xs-11">
														<input id="packing" name="packing" type="number" class="form-control" title="packing" min="0"  value="" placeholder="Packing" onKeyUp="add_freight();" >
													</div>
												</div>
												                                                       
																									   
												<div class="form-group">
													<label class="col-md-5 control-label">Packing</label>
													<div class="col-md-5 col-xs-11">
														<input id="packing" name="packing" type="number" class="form-control" title="packing" min="0"  value="" placeholder="Packing" onKeyUp="add_freight();" >
													</div>
												</div> -->
												<!-- Dimple Panchal : end -->
												<div class="sundryadded">
													
												</div>
												

												<input id="is_power_drive" name="is_power_drive" type="hidden" class="form-control" title="Round Off" value="<?= $getspecialConfiguration['power_drive'];?>">
												<?phpif($getspecialConfiguration['power_drive']==1){ ?>
													<input id="round_of" name="round_of" type="hidden" class="form-control" title="Round Off" value="0.00">
												<?php} else { ?>
													<div class="row">							
														<div class="form-group">
															<label class="col-md-5 control-label text-right">Round Off * <span class="currency_icon"></span></label>
															<div class="col-md-5 col-xs-11">
																<input id="round_of" name="round_of" type="text" class="form-control" title="Round Off" value="<?=$rel['round_of']?>" placeholder="Round Off"  tabindex="25" onKeyUp="get_gtotal_roundoff();">
															</div>
														</div>										
													</div>
												<?php} ?>



												<div class="form-group">
													<label class="col-md-5 control-label">Net Amount * <span class="currency_icon"></span></label>
													<div class="col-md-5 col-xs-11">
														<input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly" tabindex="31">
													</div>
												</div>
												<div>
													<div class="form-group">
														<label class="col-md-5 control-label">Select Bill Sundry</label>
														<div class="col-md-2">
															<?php $get_bill_sundry = get_bill_sundry_ledger($dbcon,0); ?>
															<select class="form-control" name="bill_sundry" id="bill_sundry" onchange="get_sundry_label(this.value)" tabindex="32">
																<option value="0">Select</option>
																<?php foreach ($get_bill_sundry as $sundry) {
																	
																	?>
																	<option value="<?php echo $sundry['l_id'] ?>"><?php echo $sundry['l_name']; ?></option>

																<?php } ?>
															</select>
														</div>
														<div class="col-md-2">
															<input id="bill_sundry_amount" name="bill_sundry_amount" type="text" class="form-control numbersOnly" placeholder="Amount" title="Amount" value="<?=$rel['amount']?>" placeholder="" tabindex="33"  onchange="validateFloatKeyPress(this);"	 >
														</div>
														<div class="col-md-2">
															<button style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" value="R1" onclick="addBillSundry()"><i class="fa fa-plus"></i></button>
														</div>
													</div>
												</div>

												
												<div class="form-group">
													<label class="col-md-5 control-label">Select Print</label>
													<div class="col-md-5 col-xs-11">
														<select class="form-control" name="print_status" id="print_status">
															<option value="1">ORIGINAL</option>
															<option value="2">DUPLICATE</option>
															<option value="3">TRIPLICATE</option>
															<option value="4">EXTRA</option>
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="col-md-3">
	                                               <div class="form-group">
	                                                    <input type="radio" class="" name="tc_format" id="format1" value="1" onchange="tc_format_view();" <?phpif($rel['tc_format'] == '1'){ echo 'checked="checked"';}else{ if($mode == 'Add'){echo 'checked="checked"';} }?> > Format-1                 
	                                                </div>
	                                            </div>

	                                            <div class="col-md-3">
	                                               <div class="form-group">
	                                                    <input type="radio" class="" name="tc_format" onchange="tc_format_view();" id="format2" value="2" <?phpif($rel['tc_format'] == '2'){ echo 'checked="checked"';}?>> Format-2                 
	                                                </div>
	                                            </div>
											</div>

											<div class="col-md-8" style="margin-top:12px" id="format_1">
                                                  <div class="form-group">
                                                     <label class="col-md-2 control-label">Terms Condition</label>
                                                     <div class="col-md-10 col-xs-11">
                                                        <textarea class="form-control" placeholder="Terms Condition" name="invoice_condition" id="invoice_condition" ><?php if(!empty($rel['invoice_condition'])) { echo $rel['invoice_condition']; } else { echo $invoice_terms_conditions; }?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                           
                                            <div class="col-md-8" style="margin-top:12px" id="format_2">
                                            	<div class="col-md-3">
													<div class="form-group">
														<input type="radio" class="" name="terms_type" id="common_terms" value="0" onchange="get_so_data_invoice();" 

														<?phpif($rel['terms_type'] == '0'){ echo 'checked="checked"';}else{ if($mode == 'Add'){echo 'checked="checked"';} }?> > Common Terms 
													</div>
												</div>

												<div class="col-md-3">
													<div class="form-group">
														<input type="radio" class="" name="terms_type" id="party_terms" value="1" onchange="get_so_data_invoice();"
														<?phpif($rel['terms_type'] == '1'){ echo 'checked="checked"';}?> > Party Wise
													</div>
												</div>

												<div class="col-md-3">
													<div class="form-group">
														<input type="radio" class="" name="terms_type" id="quotation_terms" value="2" onchange="get_so_data_invoice();"
														<?phpif($rel['terms_type'] == '2'){ echo 'checked="checked"';}?> > Sales Order Wise
													</div>
												</div>

													<div class="col-md-3">
														<div class="form-group">
															<input type="radio" class="" name="terms_type" id="multi_condition" value="3" onchange="get_so_data_invoice();"
															<?phpif($rel['terms_type'] == '3'){ echo 'checked="checked"';}?> > Multi Condition
														</div>
													</div>

												<div class="col-md-4" id="salesorder_wise_term" style="display: none;">
													<div class="form-group">
														<select class="select2" name="term_salesorder_id" id="term_salesorder_id" onchange="load_typeswise_terms()">
															<option value=""> Choose Sales Order</option>		
														</select>
													</div>
												</div>
                                            	<div class="form-group" id="po_terms_cond_div">

                                                </div> 
                                            </div>

										</div>

										<!-- <div class="row">											
											
										</div> -->
										
										<div class="row">
											
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Advance Payment Adjustment</label>
													<div class="col-md-8 col-xs-11">

														<select class="form-control" name="bill_adjustment" id="bill_adjustment" onchange="get_bill_adjsutment(this.value,'0')">
															<option value="">--Select Advance Adjustment--</option>
															<option value="1" <?php if($mode=='Edit' && $rel['enable_bill_adjustment']==1){ echo "selected"; } else{ echo ""; } ?> >Yes</option>
															<option value="0" <?php if($mode=='Edit' && $rel['enable_bill_adjustment']==0){ echo "selected"; } else{ echo ""; } ?>>No</option>
														</select>
														<a href="#" class="adjust_advance_link" onclick="get_bill_adjsutment('1','0')" style="display: none;">Adjust Advance Payment</a>
													</div>
												</div>
											</div>

											<div class="col-md-4"  style="display:none" id="divcost_cent_er">
												<div class="form-group">
													<label class="col-md-4 control-label">Cost Center *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_cost_center" id="allocate_cost_center" onchange="get_cost_center(this.value)">
															<option value="no" selected>No</option>
															<option value="yes"  <?php if($mode=='Edit' && $rel['enable_cost_center']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														<?php if($mode=='Edit' && $rel['enable_cost_center']==1){ $style=""; } else { $style='display:none'; } ?>
														<a style="<?=$style;?>" href="#" id="cost_center_link" onclick="get_cost_center('yes')">Show Cost Center Transaction</a>
													</div>
												</div>
											</div>
											
											<!-- <div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable Salesman *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_salesman" id="enable_salesman">
															<option value="no" selected>No</option>
															<option value="yes">Yes</option>
														</select>
													</div>
												</div>
											</div> -->
											
											<div class="col-md-4 tcs_details" style="display:none">
												<div class="form-group">
													<label class="col-md-4 control-label">TCS Detail *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_tcs_details" id="enable_tcs_details" onchange="get_tcs_popup(this.value);">
															<option value="no" selected>No</option>
															<option value="yes" <?php if($mode=='Edit' && $rel['enable_tcs_details']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														<?php if($mode=='Edit' && $rel['enable_tcs_details']==1){ $style=""; } else { $style='display:none'; } ?>
														<a style="<?=$style;?>" href="#" id="tcs_detail_link" onclick="get_tcs_popup('yes')">Show TCS Transaction</a>
													</div>
												</div>
											</div>

											<div class="col-md-4" style="display:none" id="eway_div">
												<div class="form-group">
													<label class="col-md-4 control-label">Auto Eway Bill *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_ewaybill" id="enable_ewaybill"  onchange="get_eway_bill(this.value,'auto_eway')">
															<option value="no" selected>No</option>
															<option value="yes" <?php if($mode=='Edit' && $rel['enable_ewaybill']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														<?php if($mode=='Edit' && $rel['enable_ewaybill']==1){ $style=""; } else { $style='display:none'; } ?>
														<a style="<?=$style;?>" href="#" id="eway_bill_link" onclick="get_eway_bill('yes','auto_eway')">Show Eway Bill Details</a>
													</div>
												</div>
											</div>

											<div class="col-md-4" style="display:none" id="tran_div">
												<div class="form-group">
													<label class="col-md-4 control-label">Transport Detail *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" 
														name="enable_transport" id="enable_transport"  onchange="get_eway_bill(this.value,'transport')">
														<option value="no" <?php if($rel['enable_transport']!=1){ echo "selected"; } else{ echo ""; } ?>>No</option>
														<option value="yes" <?php if(($mode=='Edit' && $rel['enable_transport']==1)||$enable_transport==1 || $rel['enable_transport']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
													</select>
													<?php if(($mode=='Edit' && $rel['enable_transport']==1)||$enable_transport==1 || $rel['enable_transport']==1){ $style=""; } else { $style='display:none'; } ?>
													<a style="<?=$style;?>" id="transport_link" onclick="get_eway_bill('yes','transport',<?=$so_voucher_type;?>)">Show Transport Details</a>
												</div>
											</div>
										</div>


										

									</div>
									<div class="row">

										<div class="col-md-4">

											<div class="form-group">
												<label class="col-md-4 control-label">EWay Bill No </label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" name="eway_bill_no" id="eway_bill_no" value="<?=$mode=='Edit'?$rel['eway_bill_no']:''?>" />
												</div>
											</div>
										</div>

										<div class="col-md-4">

											<div class="form-group">
												<label class="col-md-4 control-label">EWay Bill Date </label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control default-eway-date" name="eway_bill_date" id="eway_bill_date" value="<?=$mode=='Edit' && $rel['eway_bill_date']!='0000-00-00'?date("d/m/Y",strtotime($rel['eway_bill_date'])):''?>" />
												</div>
											</div>
										</div>	

											<!--<div class="col-md-4" style="display:none" id="salesman_div">
												<div class="form-group">
													<label class="col-md-4 control-label">Enable Salesman *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="enable_salesman" id="enable_salesman"  onchange="get_ledger_salesman(this.value,'total')">
															<option value="no" selected>No</option>
															<option value="yes" <php if($mode=='Edit' && $rel['enable_salesman']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
														</select>
														 <php if($mode=='Edit' && $rel['enable_salesman']==1){ $style=""; } else { $style='display:none'; } ?>
														<a style="<=$style;?>" href="#" id="salesman_link" onclick="get_ledger_salesman('yes','total')">Show Salesman Details</a>
													</div>
												</div>
											</div>			-->							
											
										</div>
										<div class="clearfix"></div>
										<div class="row">
											
											<div class="col-md-12">

												<div class="form-group">
													<label class="col-md-1 control-label">Remarks </label>
													<div class="col-md-11 col-xs-11">
														<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
													</div>
												</div>
											</div>	
											
										</div>

										<div class="row">
											<div class="col-md-12">
												<button type="submit" class="btn btn-success" id="save" name="save" tabindex="34">Save</button>
												<button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> &nbsp;
												<a href="<?=ROOT.FINANCE_ROOT.'invoice_list'?>" type="button" class="btn btn-danger">Cancel</a>
												<div class="col-md-3"></div>			
											</div>		
											
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											
											<input type='hidden' name='viewmode' id='viewmode' value='<?=$viewmode?>' />
											
											<!-- Financial Year Setting start -->
											
											<input type='hidden' name='financial_year' id='financial_year' value='<?=$financial_year['financial_year_id'];?>' />
											<input type='hidden' name='financial_start_date' id='financial_start_date' value='<?=$financial_year['financial_start_date'];?>' />
											<input type='hidden' name='financial_end_date' id='financial_end_date' value='<?=$financial_year['financial_end_date'];?>' />
											
											<!-- Financial Year Setting end -->
											
											<input type='hidden' name='o_total' id='o_total' value='<?=$rel['g_total']?>' />
											<input type='hidden' name='save_print' id='save_print' value='' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['invoice_id']?>' />
											
											<!-- Company Settings -->
											<input type="hidden" name="company_cost_center" id="company_cost_center" value="<?=$company_config['enable_cost_center']?>" />

											<input type="hidden" name="company_salesman" id="company_salesman" value="<?=$company_config['enable_salesman']?>" />

											<input type="hidden" name="company_tcs" id="company_tcs" value="<?=$company_config['enable_tcs_reporting']?>" />

											<input type="hidden" name="company_eway" id="company_eway" value="<?=$company_config['enable_eway_bill']?>" />

											<input type="hidden" name="company_trans" id="company_trans" value="<?=$company_config['enable_transport']?>" />

											<input type="hidden" name="company_tax_editable" id="company_tax_editable" value="<?=$company_config['tax_editable']?>" />

											<!-- cost center popup --> 
											
											<input type="hidden" name="cost_center_voucher_type" id="cost_center_voucher_type" value="<?=SALES_VOUCHER?>" />
											<input type="hidden" name="cost_center_ledger_id" id="cost_center_ledger_id" placeholder="Ledger Id" value="<?=$mode=='Edit'?$rel['cust_id']:'' ?>">
											<input type="hidden" name="cost_center_table" id="cost_center_table" value="tbl_invoice" placeholder="table name of sale , purchase , payment..">
											<input type="hidden" name="cost_center_table_id" id="cost_center_table_id" value="<?=$mode=='Edit'?$rel['invoice_id']:'0'?>" placeholder="primary key of that inserted table ">


											<!-- Transport and Eway bill transaction popup -->
											<input type="hidden" name="transport_voucher" id="transport_voucher" value="<?=SALES_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
											<input type="hidden" name="transport_transaction_table" id="transport_transaction_table" placeholder="table name of sale , purchase , payment.." value="tbl_invoice">
											<input type="hidden" name="transport_transaction_table_id" id="transport_transaction_table_id" placeholder="primary key of that inserted table " value="<?=$mode=='Edit'?$rel['invoice_id']:'0'?>">
											<input type="hidden" id="edit_id_transport" value="<?php if($mode=='Edit'){ echo $rel['invoice_id']; } else if(strpos($_SERVER['REQUEST_URI'], "invoiceso")==true && $enable_transport==1){ echo $sales_order_id; } else { echo '0'; }?>" />

											<!-- Transport and Eway bill transaction popup -->
											<input type="hidden" name="eway_bill_voucher_type" id="eway_bill_voucher_type" value="<?=SALES_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
											<input type="hidden" name="eway_bill_voucher_table" id="eway_bill_voucher_table" placeholder="table name of sale , purchase , payment.." value="tbl_invoice">
											<input type="hidden" name="eway_bill_voucher_id" id="eway_bill_voucher_id" placeholder="primary key of that inserted table ">
											<input type="hidden" id="edit_id_ewaybill" value="<?=$mode=='Edit'?$rel['invoice_id']:'0'?>" />

											<!-- Salesman transaction popup -->
											<input type="hidden" name="salesman_voucher_type" id="salesman_voucher_type" value="<?=SALES_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
											<input type="hidden" name="salesman_voucher_table" id="salesman_voucher_table" placeholder="table name of sale , purchase , payment.." value="tbl_invoice">
											
											<input type="hidden" id="edit_id_salesman" value="" />

											<input type="hidden" class="form-control" name="so_enable_transport" id="so_enable_transport" value="<?php if(strpos($_SERVER['REQUEST_URI'], "invoiceso")==false){ echo "0"; } else { echo $enable_transport; }  ?> " >

											<input type="hidden" id="so_voucher_type" value="<?=SO_VOUCHER?>" />
											<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
											<input type='hidden' name='complaint_id' id='complaint_id' value='<?=$complaint_id?>' />

										</div>
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
			
			<?php include_once($include.'footer.php');?>

			<?php include_once($include1.'add_sales_order.php');?>
			<?php 
			include_once($include1.'add_cost_center.php');
			include_once($include1.'add_tcs_details.php'); 
			include_once($include1.'add_eway_bill.php');
			include_once($include1.'add_salesman.php');
			include_once($include1.'add_batch_wise_qty.php');
			include_once($include1.'add_bill_adjustment.php');
			include_once($include1.'add_ledger.php');
			include_once($path.'purchase/include/vendor_product_price_list.php');
			include_once($path.'administration/include/add_multi_currency.php');
			include_once($path.'administration/include/add_multi_branch.php');
			include_once($path.'administration/include/add_billbybill_opening.php');
			include_once($path.'administration/include/add_depreciation.php');
			include_once($path.'administration/include/add_bill_sundry.php');
			include_once($path.'administration/include/add_monthly_budget.php');
			include_once($path.'administration/include/add_bank_cheque.php');

			include_once($path.'administration/include/add_product.php');
			include_once($path.'administration/include/add_hsn_in_popup.php');
			?>
			<!-- <div class="modal colored-header info" id="modal-sales-order" role="dialog" data-keyboard="false" data-backdrop="static" >
			</div> -->
			<div class="modal colored-header info" id="inv_srl_modal" role="dialog" data-keyboard="false" data-backdrop="static">
				<div class="modal-dialog modal-md">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
							<h3> <strong id="head_inv_srl_modal_pro_name"></strong></h3>
						</div>
						<div class="modal-body form">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group" id="add_pro_srl_div">
										<table class="display table table-bordered table-striped">
											<tr>
												<th>Serial No.</th>
												<th>Action</th>
											</tr>
											<tr>
												<td>
													<input type="text" class="form-control" name="pro_srl_no" id="pro_srl_no" placeholder="Serial No." value="" autocomplete="off" />
												</td>
												<td>
													<button type="button" id="add_pro_srl_no_btn" onclick="add_pro_srl_no();" class="btn btn-success">Add</button>
												</td>
											</tr>
										</table>
									</div>
									<div class="form-group">
										<div class="adv-table dt-resp">
											<table class="display table table-bordered table-striped">
												<thead>
													<tr>
														<th>Sr.</th>
														<th>Serail No.</th>
														<th class="">Action</th>	
													</tr>
												</thead>
												<tbody id="inv-srlno-modal-datatable">
												</tbody>				 
											</table>
										</div>
									</div>
									
									<div class="clearfix"></div>
									<div class="col-md-12 text-center" style="margin-top:10px;">
										<input type='hidden' name='ref_trancation_id' id='ref_trancation_id' value='' />	

										<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
									</div>
								</div>
							</div>
						</div>	
					</div>
				</div>
			</div>
			
		</section>
		<?php
		include_once($include.'include_js_file.php');
			include_once('../include/add_consignee.php');
			
		?>   
		<script src="<?=ROOT.FINANCE_ROOT?>js/app/invoice.js?<?php echo time(); ?>"></script>
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script>
		<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
		<script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/add_ledger_js.js?<?=time()?>"></script>
		<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/ledger.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/consignee.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/product_mst.js?<?php echo time(); ?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/hsn_master.js?<?php echo time(); ?>"></script>
		<script>
			$(".select2").select2({
				width: '100%',
				//minimumInputLength: 3
			});

			$('.select2_product').select2({
				width: '100%',
				//minimumInputLength: 3
			});

			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$('.default_date').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true,
				startDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_start_date'])) ?>',
				endDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_end_date'])) ?>',

			});

			$('.due_date_class').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true,
				startDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_start_date'])) ?>',
				endDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_end_date'])) ?>',

			});

			
			$('.default-eway-date').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true,
				startDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_start_date'])) ?>',
				endDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_end_date'])) ?>',
			});
			$(".form_datetime-meridian").datetimepicker({
				format: "dd-mm-yyyy HH:ii P",
				showMeridian: true,
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"
			});

			$('.tcs-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				"setDate": new Date(),
				"autoclose": true
			});

			
			
			function dc_change()
			{
				
				if($('#dc_enable').is(":checked"))
				{
					$('.dc_div').show();
				}
				else
				{
					$('.dc_div').hide();
				}
				
			}
			
			function po_change()
			{
				if($('#po_enable').is(":checked"))
				{
					$('.po_div').show();
				}
				else
				{
					$('.po_div').hide();
				}
				
			}
			
			function currency_change()
			{
				if($('#currency_enable').is(":checked"))
				{
					$('.currency_div').show();
				}
				else
				{
					$('.currency_div').hide();
				}
			}
			<?
			if($mode == "Edit"){ ?>
				load_cust_so(<?=$cust_id?>,<?=$sales_order_id?>);
				get_so_data_invoice();
				/*load_typeswise_terms(<=$invoiceid?>);*/
			<?php}else{ ?>
				load_typeswise_terms('');
			<?}?>
			<?if($viewmode == "invoiceso"){?>
				load_ven_grn(<?=$cust_id?>,<?=$sales_order_id?>);
				//load_grn_data(<=$grn_id?>);
				// insert_product();
				get_sales_bill_sundry(<?=$sales_order_id?>,0);
				$('#cust_id').select2('readonly',true);	
				<?php} ?>
				get_tax_details_table();
				get_invoice_total_tax();
			</script>
			<script>
				CKEDITOR.replace( 'product_des', {
					enterMode: CKEDITOR.ENTER_BR
				});
				CKEDITOR.replace( 'product_spec', {
					enterMode: CKEDITOR.ENTER_BR
				});
				CKEDITOR.replace( 'invoice_condition', {
					enterMode: CKEDITOR.ENTER_BR
				});
			</script>
			<?
			echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
			echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";

			echo "<script>load_state(".$countryid.",'con_stateid',".$stateid.")</script>";
			echo "<script>load_city(".$stateid.",'con_cityid',".$cityid.")</script>";

			
			
			/*if($rel['terms_type']=='2'){
				echo "<script>get_so_data_invoice()</script>";
			}*/

			
			if($complaint_id){
				echo "<script>copy_comp_spare_trn_data(".$complaint_id.");</script>";
			}
			
			?>

		</body>
		</html>
