<?php 

	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Invoice";
	$countryid='101';$stateid='1';$cityid='1';
	
	if(strpos($_SERVER['REQUEST_URI'], "invoiceedit")==true){
		$mode="Edit";
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_invoice where invoice_id=$invoiceid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$order_date='';$dispatch_date='';
		if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
		{
			$order_date=date('d-m-Y',strtotime($rel['order_date']));
		}
		if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
		{
			$dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
		}
		$invoice_no=$rel['invoice_no'];
		$challan_no=$rel['challan_no'];
		$load_inv_type=$rel['invoicetype_id'];
		$cust_id=$rel['cust_id'];
	}
	else if(strpos($_SERVER['REQUEST_URI'], "quot_to_inv")==true){
		$mode="Add";
		$date=date('d-m-Y');
		$quotation_id=$dbcon->real_escape_string($_REQUEST['id']);
		$qt_query="select * from tbl_quotation where quotation_id=$quotation_id";
		$qt_rel=mysqli_fetch_assoc($dbcon->query($qt_query));
		$cust_id=$qt_rel['l_id'];
		$load_inv_type='8';
	}
	else if(strpos($_SERVER['REQUEST_URI'], "spare_to_inv")==true){
		$mode="Add";
		$date=date('d-m-Y');
		$complaint_id=$dbcon->real_escape_string($_REQUEST['id']);
		$comp_query="select * from tbl_complaint where complaint_id=$complaint_id";
		$comp_rel=mysqli_fetch_assoc($dbcon->query($comp_query));
		$cust_id=$comp_rel['cust_id'];
		$rel['install_type']='no';
		$load_inv_type='8';
	}
	else{
		$mode="Add";
		$date=date('d-m-Y');
		$order_date=date('d-m-Y');
		$load_inv_type='8';
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	//$com="select * from tbl_company where company_id=".$_SESSION['company_id'];
	//$comty=mysqli_fetch_assoc($dbcon->query($com));	
	//echo $_SESSION['company_id'];
	//echo load_complaint_no($dbcon);
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container" class="sidebar-closed">
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
						<header class="panel-heading">
							<h3 style="float:left;"> <?=$mode .' '.$form?></h3>
							<?php include_once("../include/head_menu.php") ?>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'invoice_list'?>">Invoice List</a></li>
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
				<div class="panel-body">
	<form class="form-horizontal" role="form" id="invoice_add" action="javascript:;" method="post" name="invoice_add">
			<div class="row">
					<!--<div class="col-md-4">
							<label class="col-md-4 control-label"> Invoice type </label>
							<div class="col-md-6 col-xs-11">
								<select style="padding-right: 0px;" class="form-control" name="invoicetype_id" id="invoicetype_id" onChange="load_invoiceno(this.value)" <?php if($mode=='Edit'){?> readonly="readonly"<?php }?> >
									<?php //=getinvoicetype($dbcon,$load_inv_type);?>
								</select>
							</div>
	    			</div>-->
					<input type="hidden" id="invoicetype_id" name="invoicetype_id" value="<?=$load_inv_type;?>">
					<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-4 control-label">Invoice No *</label>
								<div class="col-md-6 col-xs-11">
									<input id="invoice_no" name="invoice_no" type="text" class="form-control" title="Enter Invoice No" value="<?=$invoice_no?>" placeholder="Invoice No" required>		
								</div>
					         </div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Invoice Date*</label>
							<div class="col-md-6 col-xs-11">
								<input id="invoice_date" name="invoice_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?php if($mode=='Add'){echo $date;}else if($mode=='Edit'){echo date('d-m-Y',strtotime($rel['invoice_date']));}?>" placeholder="Invoice Date">
							</div>
						</div>	
					</div>
					<div class="col-md-12"></div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">D.C. No *</label>
							<div class="col-md-6 col-xs-11">
								<input id="challan_no" name="challan_no" type="text" class="form-control" title="Enter Challan No" value="<?=$challan_no?>" placeholder="Challan No" required>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
						  <label class="col-md-4 control-label">D.C. Date*</label>
							<div class="col-md-6 col-xs-11">
								<input id="challan_date" name="challan_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?php if($mode=='Add'){echo $date;}else if($mode=='Edit'){echo date('d-m-Y',strtotime($rel['challan_date']));}?>" placeholder="Challan Date">
							</div>
						</div>	
					</div>
					<div class="col-md-12"></div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">P.O. No *</label>
							<div class="col-md-6 col-xs-11">
								<input id="order_no" name="order_no" type="text" class="form-control" title="Enter P.O. No" value="<?=$rel['order_no']?>" placeholder="P.O. No">
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
						  <label class="col-md-4 control-label">P.O. Date*</label>
							<div class="col-md-6 col-xs-11">
								<input id="order_date" name="order_date" type="text" class="form-control default-date-picker  valid" title="Date" value="<?=$order_date?>" placeholder="P.O. Date">
							</div>
						</div>	
					</div>
					
					<div class="col-md-4">				   
						<div class="form-group">
							<label class="col-md-4 control-label">Vehicle No.</label>
							<div class="col-md-6">
								<input type="text" id="vehicle_no" name="vehicle_no" class="form-control" title="Vehicle No."  placeholder="Vehicle No."  value="<?=$rel['vehicle_no']?>">
							</div>
						</div>	
					</div>
					<!--<div class="col-md-4">				   
						<div class="form-group">
							<label class="col-md-4 control-label"> Mode/Payment Terms</label>
							<div class="col-md-6 col-xs-11">
							<!--<input id="payment_terms"  name="payment_terms" type="text" class="form-control" title="Mode/Payment Terms"  value="<?=$rel['payment_terms']?>" placeholder=" Mode/Payment Terms">--
								<select style="padding-right: 0px;" class="form-control" name="payment_terms" id="payment_terms" onChange="demo();" placeholder="Days">
									<?php //=getpaymentterms($dbcon,$rel['payment_days']);?>
								</select>
							</div>
							<div class="col-md-2">
								<input type="button" name="addproduct2" id="addproduct2" data-toggle="modal" data-target="#bs-payterms-modal-lg" class="btn btn-primary" value="+"/>
							</div>
						</div>	
					</div>-->	
				    <div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">Date and Time Of Supply</label>
							<div class="col-md-7 col-xs-11">
								<!--<input type="text" id="dispatch_date" name="dispatch_date" type="text" class="form-control default-date-picker" title="Date Of Supply"   placeholder="Date Of Supply" value="<?=$dispatch_date?>">-->
								
								<div data-date="<?php if($mode=="Add"){ echo date('d-m-Y h:i A');}else { echo $dispatch_date;}?>" class="input-group date form_datetime-meridian">
									  <input type="text" class="form-control" value="<?php if($mode=="Add"){ echo date('d-m-Y h:i A');}else { echo $dispatch_date;}?>" name="dispatch_date" id="dispatch_date">
									  <div class="input-group-btn">
										  <button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
									  </div>
                                </div>
							</div>
						</div>
					</div>

					<!--<div class="col-md-4">
							<div class="form-group">
							  <label class="col-md-4 control-label">Place of Supply</label>
							  <div class="col-md-6 col-xs-11">
								<!--<input id="destination"  name="destination" type="text" class="form-control" title="Place of Supply"  value="<?=$rel['destination']?>" placeholder="Place of Supply">-->
								<!--<select style="padding-right: 0px;" class="form-control" name="destination" id="destination" >
									<?=getplaceofsupply($dbcon,$rel['destination']);?>
								</select>
								</div>
								
								<div class="col-md-2">
								<input type="button" name="addproduct3" id="addproduct3" data-toggle="modal" data-target="#bs-place-modal" class="btn btn-primary" value="+"/>
								</div>
                            </div>	
					</div>-->
					<div class="col-md-4">
							<div class="form-group">
							  <label class="col-md-4 control-label">Mode of Dispatch</label>
							  <div class="col-md-6 col-xs-11">
								<!--<input type="text" id="dispatch_doc_no" name="dispatch_doc_no" type="text" class="form-control" title="Transportation Mode"   placeholder="Mode of Dispatch" value="<?=$rel['dispatch_doc_no']?>" />-->
								<select style="padding-right: 0px;" class="form-control" name="dispatch_doc_no" id="dispatch_doc_no" >
									<?=getmodeofdispache($dbcon,$rel['dispatch_doc_no']);?>
								</select>
								</div>
								
								<div class="col-md-2">
								<input type="button" name="addproduct4" id="addproduct4" data-toggle="modal" data-target="#bs-dispatch-modal" class="btn btn-primary" value="+"/>
								</div>
                             </div>	
				    </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label">E-way Bill No.</label>
							<div class="col-md-6 col-xs-11">
								<input type="text" id="docket_no" name="docket_no" type="text" class="form-control" title="E-way Bill No." placeholder="E-way Bill No." value="<?=$rel['docket_no']?>" />
							</div>
						</div>	
				    </div>
					
				<div class="clearfix"></div>
			   <?php if($mode=="Add") {?>
				   <div class="col-md-4"  style="display:none;">
						<div class="form-group">
						  <label class="col-md-4 control-label">Payment Reminder</label>
						  <div class="col-md-6 col-xs-11">
							<input type="number" id="payment_reminder"  name="payment_reminder" class="form-control" title="Payment Notification"  value="<?=$rel['payment_reminder']?>" placeholder=" in Days">
						  </div>
						</div>	
				   </div>
			   <?php } ?>
					<div class="col-md-4">
						<div class="form-group">
						  <label class="col-md-4 control-label">Consignee Same *</label>
							<div class="col-md-6 col-xs-11">
							<?php 
								$ck='';
								if(empty($rel['consignee_id']))
								{
									$ck='checked="checked"';
								}
							?>
							<input id="same_as" name="same_as" type="checkbox" class="" title="Other Name"  <?=$ck?> value="1" style="width:20%;height:25px;" onChange="consinee_change(this.checked);">
							
							</div>
						 </div>
				   </div>
				<!--<div class="col-md-6">
					 <div class="form-group">
						<label class="col-md-3 control-label;" style="text-align:right">Machine Name</label>
						<div class="col-md-6 col-xs-11" style="">
							<input type="text" id="machine_name"  name="machine_name" class="form-control" title="Machine Name"  value="<?=$rel['machine_name']?>"  placeholder="Machine Name" >
						</div>									
					</div>
					</div> 	-->								
					<div class="col-md-12"></div>
					
					<div class="col-md-4">
					 <div class="form-group">
						<label class="col-md-3 control-label">Company *</label>
						<div class="col-md-6 col-xs-11">
							<select class="select2" name="cust_id" id="cust_id" onChange="load_consignee(this.value);check_due_payment(this.value)" >
								<?=getcust($dbcon,$cust_id);?>	
							</select>
							<strong style="color:red;display:none;font-size:16px;" id="cust_status_due_show">Customer Payment Is Due..</strong>
						</div>
						
					 </div>									
					</div>
					
					
					<div class="col-md-4" id="">
					 <div class="form-group">
						<label class="col-md-3 control-label">Installation type *</label>
						<div class="col-md-6 col-xs-11">
							<select class="select2" name="install_type" id="install_type">
								<option value="yes" <?=$rel['install_type']=='yes'?'selected':''?> <?php if(!isset($rel['install_type'])){ echo "selected"; } ?>>Yes</option>
								<option value="no" <?=$rel['install_type']=='no'?'selected':''?> >No</option>
							</select>
						</div>
						
					 </div>									
					</div>
					
					<div class="col-md-3" id="consignee" <?php if(empty($rel['consignee_id'])){ echo "style='display:none'"; }?>>
					 <div class="form-group">
						<label class="col-md-3 control-label">Consignee *</label>
						<div class="col-md-6 col-xs-11">
							<select class="select2" name="consignee_id" id="consignee_id">
								<?=get_custmer_consignee($dbcon,$rel['cust_id'],$rel['consignee_id'])?>
							</select>
						</div>
						<div class="col-md-2">
							<input type="button" class="btn btn-primary" name="addcust" id="addcust" onClick="open_consignee_click();" value="New Consignee"/>
						</div>
					 </div>									
					</div>
					
					<div class="clearfix"></div>
					<div class="col-md-7" id="check_due_div" style="display:none">
						<div class="form-group">
							<label class="col-md-1 control-label"></label>
							<div class="col-md-7">
								<input type="checkbox" name="" id="check_due" onclick="enable_invoice()" /> <strong>Click Here If U Still Want To Create Invoice </strong>
							</div>
							
						</div>	 
					</div>
											
			</div>
	<div class="col-md-12">
		<div class="form-group">
			
				<table cellspacing="10" style="border-spacing:10px;" id="product_list" class="display table table-bordered table-striped">
					<tr id="field">
						<th width="5%" class="text-center">Type</th>
						<th width="20%" class="text-center">Product Detail</th>
						<th width="8%" class="text-center">HSN Code</th>
						<th width="6%" class="text-center">Quantity</th>
						<th width="7%" class="text-center">Rate</th>
						<th width="7%" class="text-center">Per</th>
						<th width="6%">Discount</th>
						<th width="10%">Taxable Value</th>
						<th width="13%">Tax</th>
						<th width="10%" class="text-center">Amount</th>
						<th width="5%" class="text-center"></th>
					</tr>
					<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
					<tr id="field1">
						
						<td style="vertical-align:top;">
							<select class="select2" name="product_type_sel" id="product_type_sel" onChange="load_product_typeiwse(this.value);" title="Select Product Type">
								<?=getproducttype($dbcon,'0');?>
							</select>
							<!-- <input type="hidden" name="product_type" id="product_type" value="0" /> -->
						</td>
						
						<td style="vertical-align:top;">
							<select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);"><!--load_qty()-->
								<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
							</select>
							<!--<input type="button"  name="addproduct" id="addproduct" data-toggle="modal" data-target="#bs-example-modal-addproduct"  class="btn btn-primary" value="+"/>-->
							<br/><br/>
							<textarea id="product_des" name="product_des" class="form-control" placeholder="Product Description"></textarea>
						</td>	
						<td style="vertical-align:top;">
							<input type="text"  title="Enter HSN Code" id="product_hsn_code" name="product_hsn_code" class="form-control"/>
						</td>
						<td style="vertical-align:top;">
							<input type="number" min="0" id="product_qty" name="product_qty"  class="form-control" onKeyUp="get_amount();"/><br/>
							
						</td>
					
						<td style="vertical-align:top;">
							<input type="number"  title="Enter Rate" min="0" id="product_rate" name="product_rate" onKeyUp="get_amount();" class="form-control"/><br/>
							<button type="button" title="Show Previous Rate History" name="rate_history" id="rate_history" onclick="load_rate_hist()" style="display:none;" class="btn btn-info"><i class="fa fa-eye"></i> show</button>
						</td>
						<td style="vertical-align:top;">
							<select class="select2"  title="Select Unit" name="unit_id" id="unit_id">
								<?=getunit($dbcon,0);?>
							</select>
						</td>
						
						<td style="vertical-align:top;">
							<input type="number" title="Enter Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" placeholder="in Rs."/><br/>
							<input type="number"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100"/>
						</td>
						
						<td style="vertical-align:top;">
							<input type="number" title="Taxable Value" min="0" id="taxable_value" name="taxable_value" class="form-control" readonly />
						</td>
						<td style="vertical-align:top;">
							<select class="form-control" name="formulaid" id="formulaid" onChange="get_amount();">
								<?php 
									echo getformula($dbcon,$rel['formulaid']);
								?>
							</select>
						</td>
						
						<td style="vertical-align:top;"> 
							<input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control" onmouseover="this.title=this.value"/>
						</td>
						<td style="vertical-align:top;"> 
							<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>	
						</td>
							 <input type='hidden' name='edit_id' id='edit_id' value='' />
					
						</tr>
			</table>								
  
		
		</div>
	</div>
		<div id="sale_productdata"></div>
	<div class="clearfix"></div>
	 
					<div class="col-md-6">
					 <div class="form-group">
					  <label class="col-md-4 control-label">Remarks </label>
							<div class="col-md-6 col-xs-11">
							<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
						</div>
					 </div>
					 <div class="form-group">
						<label class="col-md-4 control-label">Reverse Charge  </label>
						<div class="col-md-1 col-xs-11">
							<input id="reverse_charge_check"  name="reverse_charge_check" type="checkbox" class="" title="Reverse Charge" <?=(empty($rel['reverse_charge'])?'':'checked="checked"')?>  value="1">
							
						</div>								
					 </div>
					</div>	
						
						<div class="col-md-6">
							<?php 
								if($set_head['show_charges']=='1'){
									$ttl_display="display:block";
								}else{
									$ttl_display="display:none";
								}
							?>	
								
							<div class="form-group" style="<?=$ttl_display?>">
								<label class="col-md-5 control-label">Total *</label>
								<div class="col-md-5 col-xs-11">
									<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?php if($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
					
								</div>
							</div>	
							
						
						<?php if($set_head['show_charges']=='1'){ ?>		
							<div class="form-group">
								<label class="col-md-5 control-label">Packing </label>
								<div class="col-md-5 col-xs-11">
								<input id="packing" name="packing" type="number" class="form-control" title="packing" min="0"  value="<?php if($mode=='Edit'){ echo $rel['packing'];}?>" placeholder="Packing" onKeyUp="add_freight();" >
					
								</div>
							</div>
						<?php 	} ?>	
							<!--<div class="form-group">
								<label class="col-md-5 control-label">Discount </label>
								<div class="col-md-2 col-xs-11">
									<input id="discount_per" name="discount_per" type="number" class="form-control col-md-6" title="in % Max 100" min="0"  value="<?php if($mode=='Edit'){ echo $rel['discount_per'];}?>" placeholder="in %" onKeyUp="add_discount('per');" max="100" style="width: 80px;" >
									
								</div>
								<div class="col-md-3 col-xs-11">
									<input id="discount_amt" name="discount_amt" type="number" class="form-control col-md-6" title="in Rs." min="0"  value="<?php if($mode=='Edit'){ echo $rel['discount'];}?>" placeholder="in Rs." onKeyUp="add_discount('amt');" >
								</div>
							</div>-->
							<!--
							<div class="form-group">
								<label class="col-md-5 control-label">Freight </label>
								<div class="col-md-5 col-xs-11">
								<input id="freight" name="freight" type="number" class="form-control" title="Transport" min="0"  value="<?php if($mode=='Edit'){ echo $rel['freight'];}?>" placeholder="Freight" onKeyUp="add_freight();" >
					
								</div>
							</div>-->
							 
							<!--<div class="form-group">
								<label class="col-md-5 control-label">Tax </label>
								<div class="col-md-5 col-xs-11">
								<select class="form-control" name="formulaid" id="formulaid" onChange="get_gtotal(this.value);"  title="Select Formula">
									<?php //=getformula($dbcon,$rel['formulaid']);?>
								</select>
								</div>
							</div>-->
							<div id="showformulatextbox">
							<?php 
							if($mode=='Edit')
							{
							if(!empty($rel['tax1_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-5 control-label"><?=$rel['tax1_name']?></label>
								<div class="col-md-5 col-xs-11">
								<input id="taxvalue0" name="taxvalue0" value= "<?=$rel['taxvalue1']?>"type="text" class="form-control" readonly="readonly">
								</div>
							</div>
					<input id="taxname0" name="taxname0" value= "<?=$rel['tax1_name']?>" type="hidden" class="form-control">
							<?php 
							}
							if(!empty($rel['tax2_name']))
							{
							?>
							<div class="form-group">
								<label class="col-md-5 control-label"><?=$rel['tax2_name']?></label>
								<div class="col-md-5 col-xs-11">
								<input id="taxvalue1" name="taxvalue1" value= "<?=$rel['taxvalue2']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname1" name="taxname1" value= "<?=$rel['tax2_name']?>" type="hidden" class="form-control">
							<?php 
							}if(!empty($rel['tax3_name']))
							{
							?>
					<div class="form-group">
								<label class="col-md-5 control-label"><?=$rel['tax3_name']?></label>
								<div class="col-md-5 col-xs-11">
								<input id="taxvalue2" name="taxvalue2" value= "<?=$rel['taxvalue3']?>"type="text" class="form-control" readonly="readonly">
						</div>
					</div>
					<input id="taxname2" name="taxname2" value= "<?=$rel['tax3_name']?>" type="hidden" class="form-control">
							<?php 
							}} 
							?>
							</div>
							<!-- <div class="form-group">
								<label class="col-md-5 control-label">Round Off</label>
								<div class="col-md-5 col-xs-11">
								<input id="round_off" name="round_off" type="number" class="form-control" title="Round Off"  value="<?php if($mode=='Edit'){ echo $rel['round_off'];}else{ echo "0";}?>" placeholder="Round Off" onKeyUp="add_freight();" >
					
								</div>
							</div>	-->
							<div class="form-group">
								<label class="col-md-5 control-label">Net Amount *</label>
								<div class="col-md-5 col-xs-11">
								<input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly">
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
				<div class="col-md-12">
					<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
					<button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint">Save and Print</button> &nbsp;
					<a href="<?=ROOT.'invoice_list'?>" type="button" class="btn btn-danger">Cancel</a>
					<div class="col-md-3"></div>			
				</div>		</div>
				</div><!--Vendor row end-->	
					<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
					<input type='hidden' name='o_total' id='o_total' value='<?=$rel['g_total']?>' />
					<input type='hidden' name='save_print' id='save_print' value='' />
					<input type='hidden' name='eid' id='eid' value='<?=$rel['invoice_id']?>' />
					<input type='hidden' name='quotation_id' id='quotation_id' value='<?=$quotation_id?>' />
					<input type='hidden' name='complaint_id' id='complaint_id' value='<?=$complaint_id?>' />
					  
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
	  
	<?php include_once('../include/add_cust.php');?>
	<?php include_once('../include/add_product.php');?>
	<?php include_once('../include/add_city.php');?>
	<?php include_once('../include/add_state.php');?>
	<?php include_once('../include/add_payterms.php');?>
	<?php include_once('../include/footer.php');?>
	<?php include_once('../include/add_placesupally.php');?>
	<?php include_once('../include/add_modedispatch.php');?>
	<?php include_once('../include/add_worktype.php');?>
	<?php include_once('../include/add_invdescription.php');?>
    <!--footer end-->

<!--Serial No. Modal Start-->
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
<!--Serial No. Modal end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php
	include_once('../include/include_js_file.php');
	include_once('../include/add_consignee.php');
	//include_once('../include/serial_number_add.php');
	include_once('../include/include_show_history.php');
?>   
<script src="<?=ROOT?>js/app/invoice.js?<?php echo time(); ?>"></script>
<script src="<?=ROOT?>js/app/customer.js"></script>
<script src="<?=ROOT?>js/app/product_mst.js"></script>
<script src="<?=ROOT?>js/app/city_mst.js"></script>
<script src="<?=ROOT?>js/app/payment_terms.js"></script>
<script src="<?=ROOT?>js/app/invoice_consignee.js"></script>
<script src="<?=ROOT?>js/app/state_mst.js"></script>
<script src="<?=ROOT?>js/app/place_supply.js"></script>
<script src="<?=ROOT?>js/app/mode_disptch.js"></script>
<script src="<?=ROOT?>js/app/work_type.js"></script>
<script src="<?=ROOT?>js/app/description_mst.js"></script>

	
<!--<script src="js/count.js"></script>-->
<script>
$(".select2").select2({
	width: '100%'
});
$("#product_id").select2({
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

function consinee_change(val){
	if(val=='1'){
		$('#consignee_id').select2("val","");
		$('#consignee').hide();
	}
	else{
		$('#consignee').show();
	}
}

</script>
<?php 
echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";

echo "<script>load_state(".$countryid.",'con_stateid',".$stateid.")</script>";
echo "<script>load_city(".$stateid.",'con_cityid',".$cityid.")</script>";

if($mode=="Add"){
	echo "<script>load_invoiceno(".$load_inv_type.");</script>";
}
if($quotation_id){
	echo "<script>copy_quot_trn_data(".$quotation_id.");</script>";
}
if($complaint_id){
	echo "<script>copy_comp_spare_trn_data(".$complaint_id.");</script>";
}


?>
</body>
</html>