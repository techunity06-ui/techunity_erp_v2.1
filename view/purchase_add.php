<?php 
   session_start();
   include_once("../config/config.php");
   include_once("../config/session.php");
   include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
   include_once("../include/function_database_query.php");
   $form="Purchase Bill";
   $countryid='101';$stateid='1';$cityid='1';
   	
   $currency_id=$_SESSION['currency_id'];
   $branch_id=$_SESSION['branch_id'];
    
   $conversion_rate = (($_SESSION['purchase_bill_rate'])?$_SESSION['purchase_bill_rate']:$_SESSION['currency_rate']);
   $checked='';
   $disabled='';
   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
   			PURCHASE_BILL_PENDING_ADD
   ]);
   if(!in_array(PURCHASE_BILL_PENDING_ADD,$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
   }
   if(strpos($_SERVER['REQUEST_URI'], "purchaseedit")==true) {
   	$disabled='disabled';
      $isDisabled='disabled';
   	$mode="Edit";
   	$poid=$dbcon->real_escape_string($_REQUEST['id']);
   	$query="select * from tbl_pono where po_id=$poid";
   	$rel=mysqli_fetch_assoc($dbcon->query($query));	
   	$vender_id=$rel['vender_id'];
	
      $branchId=$rel['branch_id'];
   	$_SESSION['selected_vendor'] = $vender_id;
   	$currency_id = $rel['currency_id'];
   	$conversion_rate = $rel['conversion_rate'];
   	$order_date='';
   	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00"){
   		$order_date=date('d-m-Y',strtotime($rel['order_date']));
   	}
	$purchase_ledger=$rel['purchase_ledger_id'];
   
   }
   else if(strpos($_SERVER['REQUEST_URI'], "purchase_bill_pending")==true){
   	
   	$grn_id=$dbcon->real_escape_string($_REQUEST['id']);
   	$branchId=$dbcon->real_escape_string($_REQUEST['branch_id']);
   	
   	$query_grn="select * from tbl_grn where grn_id=$grn_id";
   	
   	$rel_grn=mysqli_fetch_assoc($dbcon->query($query_grn));
   
   	$mode="Add";
   	
   	$date=date('d-m-Y');
   	
   	$order_date='';
   	
   //$deleteid=delete_record('tbl_potrancation',"potrancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
   	//echo $countryid;
   //die;
   	$vender_id=$rel_grn['vender_id'];
	
   	$_SESSION['selected_vendor'] = $vender_id;
   	$checked='checked';
      $isDisabled='disabled';
	  
	$query="select l_id from  tbl_ledger where l_group=24 and company_id=".$_SESSION['company_id'];
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$purchase_ledger=$rel['l_id'];
   }
   else {
      $isDisabled='';
   	$checked='checked';
   	$mode="Add";
   	$date=date('d-m-Y');
   	$order_date='';
   	$vender_id = $_SESSION['selected_vendor'];
   	//$deleteid=delete_record('tbl_potrancation',"potrancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
	
		$query="select l_id from  tbl_ledger where l_group=24 and company_id=".$_SESSION['company_id'];
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$purchase_ledger=$rel['l_id'];
	
   }
   
   $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
   $set_head=mysqli_fetch_assoc($dbcon->query($set));	
   
   $setconf1="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
$set_conf=brp_mysqli_fetch_assoc($dbcon->query($setconf1));
$type_conf = $set_conf['so_pro_type'];
$purchase_party_show = $set_conf['purchase_party_show'];
   
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <?php include_once('../include/include_css_file.php');?>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once('../include/include_top_menu.php');?>
         <?php include_once('../include/left_menu.php');?>
         <section id="main-content">
            <section class="wrapper">
               <div class="row">
                  <div class="col-lg-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.'purchase_list'?>"><?=$form?> List</a></li>
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
                     <?php if($poid!=''){ ?>
                     <span class="tools pull-right">
                     <a href="<?=ROOT.'purchase_add'?>"><button class="btn btn-success btn-flat">Create Purchase Bill</button></a>
                     </span>
                     <?php } ?>
                  </header>
                  <div class="panel-body">
                     <form class="form-horizontal" role="form" id="po_add" action="javascript:void(0)" method="post" name="po_add">
                        <div class="row" style="margin-bottom: 15px ">
                           <div class="col-md-12 col-md-offset-4">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-6 control-label">
                                       <input type="hidden" id="inquiry_type" name="inquiry_type" value="1">
                                    <?php if($poid!=''){ ?>
                                    <input type="hidden" id="purchase_bill_type" name="purchase_bill_type" value="<?=$rel['purchase_bill_type']?>">
                                    <?php } ?>	
                                    <input type="radio" id="purchase_bill_type" name="purchase_bill_type" style="height: 18px;width: 18px;" value="1" onchange="check_purchase_bill_type()"  <?=($rel['purchase_bill_type']=='1')?'checked':''?>  <?=$checked?> <?=$disabled?>>
                                    <strong>General Purchase</strong></label>
                                    <label class="col-md-6 control-label">
                                    <input type="radio" id="" name="purchase_bill_type" style="height: 18px;width: 18px;" value="2" onchange="check_purchase_bill_type()" <?=($rel['purchase_bill_type']=='2')?'checked':''?> <?=$disabled?>>
                                    <strong>Import Purchase</strong></label>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="row">
							<div class="col-md-12" style="margin-bottom: 15px;">
								<div class="col-md-4 col-xs-12">
									<label class="col-md-4 control-label" style="white-space:nowrap;">Purchase Ledger*</label>
									<div class="col-md-8 col-xs-10 resclear" >
										<select class="select2" name="purchase_ledger_id" id="purchase_ledger_id" required title="Select Purchase Ledger">
											<?=get_ledger($dbcon,$purchase_ledger,'');?>
										</select>
									</div>
								</div>
							</div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Select Vendor </label>
                                    <div class="col-md-6 col-xs-11">
                                       <select class="select2" name="vender_id" id="vender_id" required title="Select Vender" onChange="load_ven_grn(this.value);get_vendor_name(this.value)">
                                       <?=getcust($dbcon,$vender_id,$purchase_party_show,0);?>	
                                       </select>
                                    </div>
									<button type="button" onClick="vendor_price_modal()" title="Vendor Price List" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> </button>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label">Purchase No </label>
                                    <div class="col-md-8 col-xs-11">
                                       <input id="po_no" name="po_no" type="text" class="form-control" title="Date" value="<?=$rel['po_no']?>" placeholder="Purchase No" readonly >
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Bill No.*</label>
                                    <div class="col-md-8">
                                       <input type="text" class="form-control" id="order_no" name="order_no" title="Enter Bill No."  placeholder="Bill No." value="<?=$rel['order_no']?>" required >
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Bill Booking No.*</label>
                                    <div class="col-md-8">
                                       <input type="text" class="form-control" id="bill_booking_no" name="bill_booking_no" title="Enter Bill Booking No."  placeholder="Bill Booking No." value="<?=$rel['bill_booking_no']?>" required >
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Status</label>
                                    <div class="col-md-8">
                                       <input type="text" class="form-control" id="bill_status" name="bill_status" title="Status"  placeholder="Status" value="Prepared" readonly="">
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" >Bill Date </label>
                                    <div class="col-md-8 col-xs-11">
                                       <input id="po_date" name="po_date" type="text" class="form-control default-date-picker" title="Date" value="<?phpif($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['po_date']));}?>" placeholder="Purchase Date">
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" >Bill Booking Date </label>
                                    <div class="col-md-8 col-xs-11">
                                       <input id="po_booking_date" name="po_booking_date" type="text" class="form-control default-date-picker" title="Date" value="<?phpif($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['po_booking_date']));}?>" placeholder="Booking Date">
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" >Bill Received Date </label>
                                    <div class="col-md-8 col-xs-11">
                                       <input id="po_received_date" name="po_received_date" type="text" class="form-control default-date-picker" title="Date" value="<?phpif($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['po_received_date']));}?>" placeholder="Received Date">
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Tax</label>
                                    <div class="col-md-8">
                                       <input type="text" class="form-control" id="bill_tax" name="bill_tax" title="Tax"  placeholder="Tax" value="GST" readonly="">
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Tax Type </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="tax_type" id="tax_type" required title="Select Tax">
                                       <?=tax_type_bill($dbcon, $rel['tax_type']);?>	
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">ITC Type </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="itc_type" id="itc_type" required title="Select Tax">
                                       <?=itc_bill($dbcon, $rel['itc_type']);?>	
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label">Select Currency</label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="currency_id" id="currency_id"  required title="Select Currency">
                                       <?=getcurrency($dbcon,$currency_id);?> 
                                       </select>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label">Conversion</label>
                                    <div class="col-md-8 col-xs-11">
                                       <input id="conversion_rate" name="conversion_rate" type="text" class="form-control" title="Conversion Rate" value="<?=$conversion_rate?>" placeholder="Conversion Rate" onkeyup="set_currency_conversion(this.value);">
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">GST Type </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="gst_type" id="gst_type" required title="Select Tax">
                                       <?=itc_bill($dbcon, $rel['gst_type']);?>	
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Reverse Charge </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="reverse_charge" id="reverse_charge" required title="Select Reverse">
                                       <?=reverse_type_bill($dbcon, $rel['reverse_charge']);?>	
                                       </select>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Purchase Type </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="purchase_type_main" id="purchase_type_main" required title="Select Purchase Type">
                                       <?=purchase_type_main_bill($dbcon, $rel['purchase_type_main']);?>	
                                       </select><br><br>
                                       <select class="select2" name="purchase_type_secondary" id="purchase_type_secondary" required title="Select Purchase Type">
                                       <?=purchase_type_second_bill($dbcon, $rel['purchase_type_secondary']);?>	
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Supply Type </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" name="supply_type_main" id="supply_type_main" required title="Select Supply Type">
                                       <?=supply_type_main_bill($dbcon, $rel['supply_type_main']);?>	
                                       </select><br><br>
                                       <select class="select2" name="supply_type_secondary" id="supply_type_secondary" required title="Select Supply Type">
                                       <?=supply_type_second_bill($dbcon, $rel['supply_type_secondary']);?>	
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label">Consignee Same *</label>
                                    <div class="col-md-6 col-xs-11">
                                       <?
                                          $ck='';
                                          if(empty($rel['consignee_id']))
                                          {
                                          	$ck='checked="checked"';
                                          }
                                          ?>
                                       <input id="same_as" name="same_as" type="checkbox" class="" title="Other Name"  <?=$ck?> value="1" style="width:20%;height:25px;" onChange="consinee_change(this.checked);">
                                    </div>
                                 </div>
                                 <div class="form-group" id="consignee" <?if(empty($rel['consignee_id'])){ echo "style='display:none'"; }?>>
                                    <label class="col-md-3 control-label">Consignee *</label>
                                    <div class="col-md-6 col-xs-11">
                                       <select class="select2" name="consignee_id" id="consignee_id">
                                       <?=get_custmer_consignee($dbcon,$vender_id,$rel['consignee_id'])?>
                                       </select>
                                    </div>
                                    <div class="col-md-2">
                                       <input type="button" class="btn btn-primary" name="addcust" id="addcust" onClick="open_consignee_click();" value="New Consignee"/>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12">
                              <div class="col-md-4">
                                 <?php echo getBranchBox($dbcon, $branch_id, $branchId, $isDisabled, false); ?>
                              </div>
                           </div>
                        </div>
                        <!-- End Code -->
                        <!--<div class="row">
                           <div class="col-md-12">
                           <div class="col-md-6">
                           	<label class="col-md-4 control-label" style="">Purchase Order</label>
                           	<div class="col-md-6 col-xs-11" style="padding-left:6px">
                           		<select class="select2" name="trn_purchaseorder_id_up" id="trn_purchaseorder_id_up" onChange="load_purhcase_order_data(this.value);load_product_tax(this.value,'purchase')" >
                           			<option value="">Choose Purchase Order</option>	
                           		</select>
                           	</div>
                           </div>
                           </div>
                           <div class="clearfix"></div>
                           <div class="col-md-12">
                           <div class="form-group" id="purchase_order_div" style="display:none;">
                           	<label class="col-md-2 control-label">Choose Purchase Order</label>
                           	<div class="col-md-3 col-xs-11">
                           		<select class="select2" name="purchaseorder_id" id="purchaseorder_id" onChange="load_purhcase_order_data(this.value)" >
                           			<option value="">Choose Purchase Order</option>	
                           		</select>
                           	</div>
                           </div>		
                           </div>	
                           </div>-->
                        <hr>
                        <div class="row">
                           <!-- Tab Section Start By Umair -->
                           <section class="panel" style="margin-top: 15px">
                              <header class="panel-heading tab-bg-dark-navy-blue ">
                                 <ul class="nav nav-tabs">
                                    <li class="active">
                                       <a data-toggle="tab" href="#po_items" aria-expanded="true">Items</a>
                                    </li>
                                    <li class="">
                                       <a data-toggle="tab" href="#po_other_expenses" aria-expanded="false">Other Expenses</a>
                                    </li>
                                    <li class="">
                                       <a data-toggle="tab" href="#po_vendor_details" onClick="get_vendor_details('po_vendor_details')" aria-expanded="false">Vendor Details</a>
                                    </li>
                                    <li class="">
                                       <a data-toggle="tab" href="#po_order" onClick="get_vendor_details('po_order')" aria-expanded="false">Purchase Bill</a>
                                    </li>
                                    <li class="">
                                       <a data-toggle="tab" href="#po_deductions" aria-expanded="false">Deductions</a>
                                    </li>
                                    <li class="">
                                       <a data-toggle="tab" href="#po_manufacturer" onClick="get_manufacturer_details('po_manufacturer')"  aria-expanded="false">Manufacturer</a>
                                    </li>
                                    <li class="">
                                       <a data-toggle="tab" href="#po_history" onClick="get_vendor_details('po_history')" aria-expanded="false">Login</a>
                                    </li>
                                    <li class="">
                                       <a data-toggle="tab" href="#po_accounting" onClick="get_vendor_details('po_accounting')"  aria-expanded="false">Accounting</a>
                                    </li>
                                 </ul>
                              </header>
                              <div class="panel-body">
                                 <div class="tab-content">
                                    <div id="po_items" class="tab-pane active">
                                       <div class="row">
                                          <div class="col-md-12 col-md-offset-4">
                                             <div class="col-md-4">
                                                <div class="form-group">
                                                   <label class="col-md-3 control-label">
                                                   <input type="radio" id="purchase_type_grn" name="purchase_type" style="height: 18px;width: 18px;" value="1" onchange="check_grn();" <?=($rel['purchase_type']!='2')?'checked':''?> >
                                                   <strong>G.R.N.</strong></label>
                                                   <?if(empty($grn_id)){?>
                                                   <label class="col-md-3 control-label">
                                                   <input type="radio" id="purchase_type_direct" name="purchase_type" style="height: 18px;width: 18px;" value="2" onchange="check_grn();" <?=($rel['purchase_type']=='2')?'checked':''?> >
                                                   <strong>Direct</strong></label>
                                                   <?php} ?>
                                                </div>
                                             </div>
                                          </div>
											<div class="col-md-12 grn" >
												<!-- <select class="select2" name="grn_id" id="grn_id" onChange="load_grn_data(this.value);">
													<?//=get_grn_for_purchase($dbcon,$rel['vender_id'],"",$mode);?>
												</select>
												 -->
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">GRN No</label>
														<div class="col-md-6 col-xs-11">
														<select id="grn_id" name="grn_id[]" class="select2" title="Select GRN No" placeholder="Select GRN No" multiple="multiple" onChange="insert_product()" >	
															<?=get_grn_for_purchase($dbcon,$vender_id,"",$mode);?>
														</select>	
														</div>
													 </div>
												</div>
											</div>
                                       </div>
                                       <div class="row">
                                          <div class="col-md-12" style="margin-top:10px;">
                                             <table cellspacing="10" style=" border-spacing:10px;table-layout: fixed;" class="display table table-bordered table-striped" id="product_list">
                                                <tr id="field" >
                                                   <!--<th width="4%" class="text-center grn">GRN</th>-->
                                                   <th width="20%" class="text-center">Product</th>
                                                   <th width="6%" class="text-center">Quantity</th>
                                                   <th width="6%" class="text-center">USD Rate</th>
                                                   <th width="6%" class="text-center">INR Rate</th>
                                                   <th width="6%" class="text-center">Per</th>
                                                   <th width="6%" class="text-center">Discount</th>
                                                   <th width="9%" class="generalfield">Taxable Value</th>
                                                   <th width="14%" class="generalfield">Tax</th>
                                                   <th width="9%" class="text-center">USD Amount</th>
                                                   <th width="9%" class="text-center">INR Amount</th>
                                                   <th width="5%" class="text-center"></th>
                                                </tr>
                                                <input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
                                                <tr id="field1">
                                                   <!--<td class="grn" style="vertical-align:top;">
                                                      <select class="select2" name="grn_id" id="grn_id" onChange="load_grn_data(this.value);">
                                                      <?//=get_grn_for_purchase($dbcon,$rel['vender_id'],"",$mode);?>
                                                      </select>
                                                   </td>-->
                                                   <td style="vertical-align:top;">
                                                      <!-- <select class="select2" title="Select product" name="product_id" id="product_id" onChange="load_productdetail(this.value);load_product_tax(this.value,'purchase')">
                                                      <?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
                                                      </select> -->
                                                      <input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onChange="load_productdetail(this.value);load_product_tax(this.value,'purchase')"/>
                                                      <!--<input type="button" name="addproduct" id="addproduct" data-toggle="modal" data-target="#bs-example-modal-addproduct" class="btn btn-primary" value="+"/>-->
                                                      <br/><br/>
                                                      <textarea id="product_des" name="product_des" class="form-control" ></textarea>
                                                   </td>
                                                   <td style="vertical-align:top;">
                                                      <input type="number" min="0" id="product_qty" name="product_qty"  class="form-control" onkeyup="get_amount();"/>
													  <br>
													  <button type="button" onClick="vendor_product_price_modal()" title="Vendor Product Price List" class="btn btn-primary"><i class="fa fa-eye" aria-hidden="true"></i> </button>
                                                   </td>
                                                   <td style="vertical-align:top;">
                                                      <input type="number"  title="Enter USD Rate" min="0" id="product_usd_rate" name="product_usd_rate" onchange="get_amount();" onkeyup="get_amount(); get_currency_amount('2',this.value);" class="form-control"/><br/>
                                                   </td>
                                                   <td style="vertical-align:top;">
                                                      <input type="number"  title="Enter INR Rate" min="0" id="product_rate" name="product_rate"onchange="get_amount();"  onkeyup="get_amount(); get_currency_amount('1', this.value);" class="form-control"/><br/>
                                                      <!--<button type="button" title="Show Previous Rate History" name="rate_history" id="rate_history" onclick="load_rate_hist()" style="display:none;" class="btn btn-info"><i class="fa fa-eye"></i> show</button>-->
                                                   </td>
                                                   <td style="vertical-align:top;">
                                                      <select class="select2"  name="unitid" id="unitid"  title="Select Unit">
                                                      <?=getunit($dbcon,0);?>
                                                      </select>
                                                   </td>
                                                   <td style="vertical-align:top;">
                                                      <input type="number" title="Enter Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" placeholder="in Rs."/><br/>
                                                      <input type="number"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100"/>
                                                   </td>
                                                   <td style="vertical-align:top;" class="generalfield">
                                                      <input type="number" title="Taxable Value" min="0" id="taxable_value" name="taxable_value" class="form-control" readonly />
                                                   </td>
                                                   <td style="vertical-align:top;" class="generalfield">
                                                      <select class="form-control" name="formulaid" id="formulaid" onChange="get_amount();">
                                                      <?php//echo getformula($dbcon,$rel['formulaid']);?>
                                                      <?php echo get_tax_formula($dbcon,$rel['formulaid'],' and tax_type=0'); ?>
                                                      </select>
                                                      <!-- <input type="hidden" name="formulaid" id="formulaid" class="form-control" readonly /> -->
                                                      <input type="hidden" name="formula_tax_id" id="formula_tax_id" class="form-control" readonly />
                                                      <input type="hidden" name="product_amount_tax" id="product_amount_tax" class="form-control" readonly />
                                                      <input type="hidden"  name="sel_tax" id="sel_tax" class="form-control" readonly />
                                                   </td>
                                                   <td style="vertical-align:top;"> 
                                                      <input type="number" min="0" id="product_usd_amount" readonly="readonly" name="product_usd_amount" class="form-control"/>
                                                   </td>
                                                   <td style="vertical-align:top;"> 
                                                      <input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" class="form-control"/>
                                                   </td>
                                                   <td width="5%">
                                                      <input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
                                                   </td>
                                                   <input type='hidden' name='edit_id' id='edit_id' value='' />
                                                </tr>
                                             </table>
                                             <div id="sale_productdata"></div>
                                          </div>
                                       </div>
                                       <hr>
                                       <div class="row">
                                          <div class="col-md-6">
                                             <?phpif($mode=="Add"){ ?>
                                             <div class="form-group">
                                                <label class="col-md-4 control-label">Payment Mode</label>
                                                <div class="col-md-6 col-xs-11">
                                                   <select class="select2" name="paymentmodeid" id="paymentmodeid" onChange="paymentmode(this.value);/*get_cash_opening_bal(this.value,'max_paid_amount','tran_amounterr');*/" title="Select Debit Account">
                                                   <?= get_ledger_bank($dbcon,$purchase_ledger);?>	
                                                   </select>
                                                </div>
                                             </div>
                                             <div style="display:none" id="cheque_data">
                                                <div class="form-group dr" id="cheque_display" style="display:none;">
                                                   <label class="col-md-4 control-label">Select Bank *</label>
                                                   <div class="col-md-6 col-xs-11">
                                                      <select class="form-control"  name="bankid" id="bankid" title="Select Bank">
                                                      <?=getbank($dbcon,0,' and bankid!=0')?>	
                                                      </select>
                                                   </div>
                                                </div>
                                                <div class="form-group">
                                                   <label class="col-md-4 control-label">Reference No *</label>
                                                   <div class="col-md-6 col-xs-11">
                                                      <input id="cheque_dtl" name="cheque_dtl" type="text" class="form-control" title="cheque_dtl" value="<?=$rel['cheque_dtl']?>" placeholder="Cheque No. / NEFT No. / RTGS No." >
                                                   </div>
                                                </div>
                                                <div class="form-group">
                                                   <label class="col-md-4 control-label" >Reference date </label>
                                                   <div class="col-md-6 col-xs-11">
                                                      <input id="ref_date" name="ref_date" type="text" class="form-control default-date-picker" title="Reference Date" value="<?=$date?>" placeholder="Cheque Date/NEFT Date">
                                                   </div>
                                                </div>
                                             </div>
                                             <div class="form-group">
                                                <label class="col-md-4 control-label">Paid Amount*</label>
                                                <div class="col-md-6 col-xs-11">
                                                   <input id="paid_amount" name="paid_amount" type="number" min='0' class="form-control" title="" value="" max="<?phpecho $due; ?>" placeholder="Amount" onkeyup="copy_full_payment();">
                                                </div>
                                                <div class="col-md-2 col-xs-11"  style="font-size:14px;display:none;">
                                                   <select class="select2" name="paid_typeid" onchange="copy_full_payment();" id="paid_typeid" title="Select Type">
                                                   <?=getbalance_type($dbcon,2)?>
                                                   </select>
                                                </div>
                                             </div>
                                             <?php} ?>
                                          </div>
                                          <div class="col-md-6">
                                             <div class="form-group">
                                                <label class="col-md-6 control-label">Total *</label>
                                                <div class="col-md-4 col-xs-11">
                                                   <input id="total" name="total" type="text" readonly="readonly" class="form-control" title="dispatch_no" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total">
                                                </div>
                                             </div>
                                             <div class="form-group">
                                                <label class="col-md-6 control-label">Round Off</label>
                                                <div class="col-md-4 col-xs-11">
                                                   <input id="round_off" name="round_off" type="number" class="form-control" title="Round Off" value="<?phpif($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['round_off'];}?>" onKeyUp="get_amount();" placeholder="Round Off">
                                                </div>
                                             </div>
                                             <div class="form-group importfield" style="display: none">
                                                <label class="col-md-6 control-label">Tax </label>
                                                <div class="col-md-4 col-xs-11">
                                                   <select class="form-control" name="importformulaid" id="importformulaid" onChange="get_amount();">
                                                   <?
                                                      echo getformula($dbcon,$rel['formulaid']);
                                                      ?>
                                                   </select>
                                                </div>
                                             </div>
                                             <div class="form-group importfield" style="display: none">
                                                <label class="col-md-6 control-label">IGST Amount</label>
                                                <div class="col-md-4 col-xs-11">
                                                   <input id="igst_amount" name="igst_amount" type="number" class="form-control" title="IGST" value="<?phpif($mode=="Add"){echo 0;}else if($mode="Edit"){echo $rel['igst_amount'];}?>" onKeyUp="get_amount();"  placeholder="IGST Amount">
                                                </div>
                                             </div>
                                             <!-- Dimple Panchal : start -->
                                             <?php //$tcs_applicable = $dbcon->query("SELECT tcs_applicable FROM tbl_finance_setting as comp WHERE company_id=".$_SESSION['company_id'])
                                                                   // ->fetch_object()->tcs_applicable; 
                                            if($tcs_applicable) {?>
                                             <div class="form-group">
                                                <label class="col-md-6 control-label">Select Formula</label>
                                                <div class="col-md-4 col-xs-11">
                                                   <select class="form-control" name="formula_id" id="formula_id" onChange="get_gtotal();">
                                                   <?php echo get_tax_formula($dbcon,$rel['formulaid'],' and tax_type=1'); ?>
                                                   </select>
                                                </div>
                                             </div>
                                             <div class="form-group">
                                                <label class="col-md-6 control-label">Tax</label>
                                                <div class="col-md-4 col-xs-11">
                                                   <input type='text' class="form-control" name='tcs_total' id='tcs_total' value='0' />
                                                </div>
                                             </div>
                                             <?php } ?>
                                             <!-- Dimple Panchal : end -->
                                             <div class="form-group">
                                                <label class="col-md-6 control-label"> Total  Expense *</label>
                                                <div class="col-md-4 col-xs-11">
                                                   <input id="exp_total" name="exp_total" type="text"  class="form-control" title="total" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['exp_total'];}?>" placeholder="Total"readonly="readonly">
                                                   <!--<input id="total" name="total" type="hidden" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;} ?>" placeholder="total"readonly="readonly">-->
                                                </div>
                                             </div>
                                             <div class="form-group">
                                                <label class="col-md-6 control-label">Grand Total *</label>
                                                <div class="col-md-4 col-xs-11">
                                                   <input id="g_total" name="g_total" type="text"  class="form-control" title="total" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $rel['g_total'];}?>" placeholder="Total"readonly="readonly">
                                                   <!--<input id="total" name="total" type="hidden" value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;} ?>" placeholder="total"readonly="readonly">-->
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div id="po_other_expenses" class="tab-pane">
                                       <section class="panel">
                                          <div class="panel-body bio-graph-info">
                                             <div class="row">
                                                <div class="col-md-12">
                                                   <div class="form-group">
                                                      <h1>Other Expenses</h1>
                                                      <div class="col-md-12">
                                                         <div class="col-md-3 col-xs-11">
                                                            <select class="form-control"  id="ename">
                                                            <?=get_all_expense($dbcon,'');?>
                                                            </select>
                                                         </div>
                                                         <div class="col-md-3 col-xs-11"><input type="text" class="form-control" id="eamount"   placeholder="Amount"></div>
                                                         <div class="col-md-3 col-xs-11"><input type="button" class="add-row btn btn-success" value="+"></div>
                                                      </div>
                                                      <div  class="col-md-8 ">
                                                         <table class="table table-borederd" id="etable">
                                                            <thead>
                                                               <tr>
                                                                  <th>Select</th>
                                                                  <th>Expense</th>
                                                                  <th>Amount</th>
                                                               </tr>
                                                            </thead>
                                                            <tbody>
                                                               <?php if($mode=="Edit") { 
                                                                  $querye="select * from tbl_purchase_exp where exp_in_id='".$rel['po_id']."' and exp_e_amount!='0'";
                                                                  $rele=$dbcon->query($querye);
                                                                  $ecount=mysqli_num_rows($rele);
                                                                  $counte=1;
                                                                  while($rowe=mysqli_fetch_array($rele))
                                                                  {
                                                                  ?>
                                                               <tr>
                                                                  <td><input type='checkbox' name='record'></td>
                                                                  <td>
                                                                     <span id='ncnt<?php echo $counte; ?>'></span>
                                                                     <?php echo get_expense_name_by_id($dbcon,$rowe['exp_e_name']); ?>
                                                                     <input type='hidden' name='ename_a[]' value='<?php echo $rowe['exp_e_name'] ?>' class='ex_name' />
                                                                  </td>
                                                                  <td>
                                                                     <?php echo $rowe['exp_e_amount'] ?>
                                                                     <input type='hidden' name='eamount_a[]' value='<?php echo $rowe['exp_e_amount'] ?>'  class='ex_amount' />
                                                                  </td>
                                                               </tr>
                                                               <?php
                                                                  $counte++;
                                                                  }
                                                                  } ?>
                                                            </tbody>
                                                         </table>
                                                         <button type="button" class="delete-row btn btn-danger">Delete Row</button>
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                             <div>	
                                       </section>
                                       </div>
                                       <div id="po_vendor_details" class="tab-pane">Please Select Vendor</div>
                                       <div id="po_order" class="tab-pane">Bill List Details</div>	
                                       <div id="po_deductions" class="tab-pane">Deductions Details</div>
                                       <div id="po_manufacturer" class="tab-pane">Manufacturer Details</div>
                                       <div id="po_history" class="tab-pane">Login Details</div>
                                       <div id="po_accounting" class="tab-pane">Accounting Details</div>
                                       </div>
                                    </div>
                           </section>
                           <!-- Tab Section -->
                           </div>
                           <div class="row">
                           <div class="col-md-12 text-center">
                           <button type="submit" class="btn btn-success" id="save" name="save">Save</button>
                           <a href="<?=ROOT.'purchase_list'?>" type="button" class="btn btn-danger">Cancel</a>
                           </div>	
                           </div>
                           <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                           <input type='hidden' name='eid' id='eid' value='<?=$rel['po_id']?>' />
                           <input type="hidden"  name="row_cnt" id="row_cnt" value="<?=($mode=='Edit')?$ecount:'0'?>" >
                     </form>
                     </div>
               </section>
               </div>
               </div>		
            </section>
         </section>
         <?php
            include_once('../include/add_consignee.php');
            include_once('../include/add_city.php');
            include_once('../include/add_state.php');
			include_once('../include/vendor_price_list.php');
			include_once('../include/vendor_product_price_list.php');
            ?>
         <?php include_once('../include/include_show_purchase_history.php'); ?>
         <?php include_once('../include/footer.php');?>
      </section>
      <?php include_once('../include/include_js_file.php');?>   
      <script src="<?=ROOT?>js/app/purchase.js?<?=time()?>"></script>
      <script src="<?=ROOT?>js/app/invoice_consignee.js"></script>
      <script src="<?=ROOT?>js/app/city_mst.js"></script>
      <script src="<?=ROOT?>js/app/state_mst.js"></script>
      <script src="<?=ROOT?>js/app/customer.js"></script>
      <script src="<?=ROOT?>js/app/payment_new.js"></script>
      <script>
         //$('#container').addClass('sidebar-closed');
         $(".select2").select2({
         	width: '100%'
         });
         /* $("#product_id").select2({
         	width: '83%'
         }); */
         $('.default-date-picker').datepicker({
         	format: 'dd-mm-yyyy',
         	autoclose: true
         });
         $(".form_datetime").datetimepicker({
         	format: 'dd-mm-yyyy hh:ii',
         	autoclose: true,
         	todayBtn: true,
         	pickerPosition: "bottom-left"
         
         });
         <?if($mode=='Add'){?>
         	load_purchase_srs_no();
         <?}?>
      </script>
      <script type="text/javascript">
         $(".add-row").click(function(){
         	var count =$('#row_cnt').val();
         	var name = $("#ename").val();
         	var amount = $("#eamount").val();
         	var new_cnt=Number(count)+1;
         
         	if(name==''){
         		toastr.warning("Please Select Expense", "WARNING");	
         		return false;
         	}
         	if(amount==''){
         		toastr.warning("Please Enter Amount", "WARNING");
         		return false;	
         	}
         	//alert(new_cnt);
         	$('#row_cnt').val(new_cnt);
         	get_expense_name(new_cnt,name);
         	var markup = "<tr><td><input type='checkbox' name='record'></td><td><span id='ncnt"+new_cnt+"'></span><input type='hidden' name='ename_a[]' value='"+name+"' class='ex_name' /><input type='hidden' name='eamount_a[]' value='"+amount+"'  class='ex_amount' /></td><td>" + amount + "</td></tr>";
         	$("#etable tbody").append(markup);
         	get_final_total();
         	calculate_grate();
         	//alert($('#row_cnt').val(Number(count)+1));
         });
         
         // Find and remove selected table rows
         $(".delete-row").click(function(){
         	$("#etable tbody").find('input[name="record"]').each(function(){
         		if($(this).is(":checked")){
         			$(this).parents("tr").remove();
         		}
         	});
         	get_final_total();
         	calculate_grate();
         });
              
         function get_expense_name(count,expense)
         {
         	$.ajax({
         		type: "POST",
         		url: root_domain+'app/purchase/',
         		data: { mode : "expense_by_id",  eid : expense },
         		success: function(response)
         		{
         			//alert(response);			
         			//return response;
         			$('#ncnt'+count).html(response);
         		}
         	});	
         }
         
         function get_final_total()
         {
         	
         	var g_total=Number($('#total').val());
         	var add = 0;
         	$(".ex_amount").each(function() {
         		add += Number($(this).val());
         		//alert(add);
         	});
         
         	//alert(add);
         	var total=add+g_total;
         
         	var igs=Number($('#igst_amount').val());
         	if(igs!=0)
         	{
         		if(isNaN(igs)==false){
         			total=Number(total)+igs;
         		}
         	}
         	//alert(total);
         	$('#g_total').val(total);
         	$('#exp_total').val(add);
         }
         
         function calculate_grate(){
         	var usd_add = 0;
         	$(".usd_amount").each(function() {
         		usd_add += Number($(this).val());
         	});
         	
         	var total = $('#g_total').val();
         	
         	var dollar_rate = parseFloat(total)/parseFloat(usd_add);
         
         	//.toFixed(2)
         	
         	$(".item_div").each(function(index) {
         		var i = index + 1;
         		var id = $(this).attr('data-qtnid');
         		var usdrate = $('.item_qty_'+i).attr('data-usdrate');
         
         		var grate = parseFloat(dollar_rate)*parseFloat(usdrate);
         		grate = grate.toFixed(2);
         
         		$.ajax({
         			type: "POST",
         			url: root_domain+'app/purchase/',
         			data: { mode : "update_grate",id :id, grate : grate },
         			success: function(response)
         			{
         				$('.item_grate_'+id).html(grate);
         				//console.log(response);
         			}
         		});
         		
         	});
         }
         
         function consinee_change(val){
         	if(val=='1'){
         		$('#consignee_id').select2("val","");
         		$('#consignee').hide();
         	}
         	else{
         		$('#consignee').show();
         	}
         }
         
         <?
            if(!empty($grn_id)){ ?>
         	load_ven_grn(<?=$vender_id?>,<?=$grn_id?>);
         	//load_grn_data(<?=$grn_id?>);
			insert_product();
         	$('#vender_id').select2('readonly',true);
         <?php} ?>
         
      </script>
      <?php 
         echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
         echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
         
         echo "<script>load_state(".$countryid.",'con_stateid',".$stateid.")</script>";
         echo "<script>load_city(".$stateid.",'con_cityid',".$cityid.")</script>";
         ?>
   </body>
</html>

