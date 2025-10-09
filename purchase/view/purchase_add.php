 <?php 
   session_start();
	//set_time_limit(0);
	
	// $path = '../../';
	// $include = '../../include/';
	// $include1 = '../include/';
	// include_once($path."config/config.php");
	// include_once($path."config/session.php");
	// include_once(COMMON_FUNCTION_PATH."common_functions.php");
	// include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

	include('../include/urlfile.php');	
	// error_reporting(E_ALL);
   $form="Purchase Bill";
   $countryid='101';$stateid='1';$cityid='1';
  	$style = "display:none"; 	
   $currency_id=$_SESSION['currency_id'];
   $branch_id=$_SESSION['branch_id'];
   $conversion_rate = (($_SESSION['purchase_bill_rate'])?$_SESSION['purchase_bill_rate']:$_SESSION['currency_rate']);
   $checked='';
   $disable='';
   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
   			PURCHASE_BILL_PENDING_ADD
   ]);
   if(!in_array(PURCHASE_BILL_PENDING_ADD,$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
   }
   if(strpos($_SERVER['REQUEST_URI'], "purchaseedit")==true) {
   	
   	$disable = 'disabled';
      $isDisabled='disabled';
   	$mode="Edit";
   	$poid=$dbcon->real_escape_string($_REQUEST['id']);
   	$query="select * from tbl_pono where po_id=$poid";
   	$rel=mysqli_fetch_assoc($dbcon->query($query));	
   	if(!empty($rel['service_id'])){
   		$style = "display:block";
		}
		$service_id = $rel['service_id'];
   	$vender_id=$rel['vender_id'];
	$grn_id = $rel['grn_id'];
	$branchId=$rel['branch_id'];
   	$_SESSION['selected_vendor'] = $vender_id;
   	$currency_id = $rel['currency_id'];
   	$conversion_rate = $rel['conversion_rate'];
	$material_center = $rel['purchase_material_center'];
		$invoice_no = $rel['order_no'];
   	$order_date='';
   	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00"){
   		$order_date=date('d-m-Y',strtotime($rel['order_date']));
   	}
	$purchase_ledger=$rel['purchase_ledger_id'];
   
   }
   else if(strpos($_SERVER['REQUEST_URI'], "purchase_bill_pending")==true){
   	$disable = 'disabled';
   	$grn_id=$dbcon->real_escape_string($_REQUEST['id']);
   	$branchId=$dbcon->real_escape_string($_REQUEST['branch_id']);
   	
   	 $query_grn="select grn.*,po.godown_id,po.currency_id,po.currency_enable,po.currency_rate from tbl_grn as grn 
	left join tbl_purchaseorder as po on po.purchaseorder_id = grn.purchaseorder_id
	where grn.grn_id=$grn_id";
   	$rel_grn=mysqli_fetch_assoc($dbcon->query($query_grn));
   
	$branchId=$rel_grn['branch_id'];
	$vender_id=$rel_grn['vender_id'];
	$material_center=$rel_grn['godown_id'];
	$invoice_no = $rel_grn['order_no'];

	if($rel_grn['currency_id'])
	{
		$currency_id = $rel_grn['currency_id'];
	}
	else
	{
		$currency_id = 68;
	}

	

	$currency_rate = $rel_grn['currency_rate'];
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
			$rel['currency_rate'] = $currency_rate;
   }else if(strpos($_SERVER['REQUEST_URI'], "purchase_bill_service_pending")==true){
   	$disable = 'disabled';
		$service_id = $dbcon->real_escape_string($_REQUEST['id']);
		$branchId=$dbcon->real_escape_string($_REQUEST['branch_id']);

		$query = "select * from tbl_service_notes where service_id=".$service_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));

		$branchId=$rel['branch_id'];
		$vender_id=$rel['vender_id'];
		$material_center="";
		$invoice_no = $rel['order_no'];
   	$mode="Add";
   	$bill_type ="service";
   	$date=date('d-m-Y');
   	
   	$order_date='';

   }
   else {

	$isDisabled='';
   	$checked='checked';
   	$mode="Add";
   	$date=date('d-m-Y');
   	$order_date='';
   //	$vender_id = $_SESSION['selected_vendor'];
   	//$deleteid=delete_record('tbl_potrancation',"potrancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
	
		$query="select l_id from  tbl_ledger where l_group=24 and company_id=".$_SESSION['company_id'];
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$purchase_ledger=$rel['l_id'];

	
   }
   
   $financial_year=get_financial_year_new($dbcon);
   	
   $company_config = getCompanyConfiguration($dbcon);
	$purchase_party_show = $company_config['purchase_party_show'];
	$getspecialConfiguration=getspecialConfiguration($dbcon);
   //echo $purchase_party_show;
   //print_r($purchase_party_show);
   ?>
  
<!DOCTYPE html>
<html lang="en">
   <head>
      <title>PURCHASE BILL</title>
      <?php include_once($include.'/include_css_file.php');?>
	  <style>
		.row_margin
		{
			margin-top:10px !important;
		}
		.currency_icon{
				color: green;
				font-size: 12px;
		    	font-weight: bold;
		}
	  </style>
	  
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once($include.'/include_top_menu.php');?>
         <?php include_once($include.'/left_menu.php');?>
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
                              <li><a href="<?=ROOT.PURCHASE_ROOT.'purchase_list'?>"><?=$form?> List</a></li>
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
				  <form class="form-horizontal" role="form" id="po_add" action="javascript:void(0)" method="post" name="po_add">
                  <div class="panel-body">
                     
                        <input type="hidden" name="cust_stateid" id="cust_stateid">
                        <div class="row">
							
							<div class="col-md-4 col-xs-12">
								<label class="col-md-4 control-label" style="white-space:nowrap;">Purchase Ledger*</label>
								<div class="col-md-8 col-xs-10 resclear">
									<?php $purchase_grp_array=implode(",",array(PURCHASE_ACCOUNTS)); 
										$purchase_account = isset($rel['purchase_ledger_id']) ? $rel['purchase_ledger_id'] : PURCHASE_ACCOUNT ;
									?>
									<select class="select2" <?= $disable ?> name="purchase_ledger_id" id="purchase_ledger_id" required title="Select Purchase Ledger" tabindex="1">
										<?= f_get_group_ledger($dbcon,$purchase_grp_array,$purchase_account);?>
									</select>
									<?php
			                    if($disable){
			                        echo '<input type="hidden" name="purchase_ledger_id" id="purchase_ledger_id" value="'.$purchase_account.'">';
			                    	}
			                  ?>
								</div>
							</div>

								<div class="col-md-4">
								<label class="col-md-4 control-label" style="white-space:nowrap;">Select Branch *</label>
								<div class="col-md-8 col-xs-10 resclear">
									<select class="select2" name="branch_id" id="branch_id" tabindex="2">
										<option value="">--Please Select Branch--</option>
										<?php $branch = isset($branchId) ? $branchId : '1000'; ?>
										<?=getBranchBox_new($dbcon,$branch);?>
									</select>
								</div>
                            </div>

                            <div class="col-md-3">                            
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" style="">Select Vendor </label>
                                    <div class="col-md-8 col-xs-11">
                                       <select class="select2" <?= $disable ?> name="vender_id" id="vender_id" required title="Select Vender" onChange="get_statecode(this.value);get_grossbalance(this.value);get_invoice_total_tax();get_gtotal();get_ledger_details(this.value);get_grn(this.value,<?=$grn_id?>);" tabindex="3">
                                       <?=getcust($dbcon,$vender_id,$purchase_party_show,0);?>	
                                       </select>
                                       <?php
							                    if($disable){
							                        echo '<input type="hidden" name="vender_id" id="vender_id" value="'.$vender_id.'">';
							                    	}
							                  ?>
									   <strong style="display:none;color:green" id="gross">Gross balance : <span class="gross"></span></strong> <br><strong id="statecode" style="display:none;color:blue">GST StateCode : <span class="statecode"></span></strong>
                                    </div>
                                 </div>
                            </div>
                          <div class="col-md-1">   
                            <button accesskey="n" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="modal" value="R1" onclick="showledger();"><i class="fa fa-plus"></i> Add Vendor</button>
                            <!-- <a href="#"  data-original-title="Short-Cut To Open PopUp, Shift + Alt + n " data-toggle="tooltip" data-placement="top" ><i class="fa fa-info-circle fa-sm" style="color: black;"></a></i> -->
                          </div>
                          
							
						</div>
						<div class="row">
										<div class="col-md-4">
                                   <div class="form-group">
                                       <label class="col-md-4 control-label">Series * </label>
                                       <div class="col-md-8 col-xs-11">
                                          <select class="select2" name="invoicetype_id" id="invoicetype_id" onchange="load_purchase_srs_no(this.value)" required>
                                               <option value="">--Select Series--</option>
                                               <?=get_invoice_type_list($dbcon,12,$rel['invoicetype_id'])?>
                                          </select>
                                       </div>
                                   </div>
                               </div>

                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label">Voucher No </label>
                                    <div class="col-md-8 col-xs-11">
                                       <input id="po_no" name="po_no" type="text" class="form-control" title="Voucher Number" value="<?=$rel['po_no']?>" placeholder="Purchase No" readonly  tabindex="4">
                                    </div>
                                 </div>
                              </div>
                             
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" >Bill Date </label>
                                    <div class="col-md-8 col-xs-11">
                                       <input id="po_date" name="po_date" type="text" class="form-control default_date" title="Date" value="<?phpif($mode=='Add'){ echo $date;}else if($mode=='Edit'){ echo date('d-m-Y',strtotime($rel['po_date']));}?>" placeholder="Purchase Date" tabindex="5">
                                    </div>
                                 </div>
                              </div>
							  
							  <div class="col-md-3">
                                 <div class="form-group">
                                    <label class="col-md-4 control-label" >Material Center </label>
                                    <div class="col-md-8 col-xs-11">
                                      	<select class="select2" name="purchase_material_center" id="purchase_material_center" title="Select Godown" tabindex="6">
											<?= get_all_godown($dbcon,$material_center,"");?>
										</select>
                                    </div>
                                 </div>
                              </div>
							  
							  
                        </div>
						
						<div class="row">
							
							<div class="col-md-4" style="display:none" id="grn_div">
								<div class="form-group">
									<label class="col-md-4 control-label">GRN No</label>
									<div class="col-md-8 col-xs-11">
									<select id="grn_id" name="grn_id[]" class="select2" title="Select GRN No" placeholder="Select GRN No" multiple="multiple" onChange="insert_product()" tabindex="7" >	

										<option value="" selected>Choose Grn</option>
									</select>	
									</div>
								 </div>
							</div>
							
							<div class="col-md-4">
	                     <div class="form-group">
	                        <label class="col-md-4 control-label">Invoice No </label>
	                        <div class="col-md-8 col-xs-11">
	                           <input id="invoice_no" name="invoice_no" type="text" class="form-control" title="Invoice No" value="<?=$invoice_no?>" placeholder="Invoice No" tabindex="8" required>
	                        </div>
	                     </div>
	                  </div>

                     <div class="col-md-4">
                        <div class="form-group">
                           <label class="col-md-4 control-label">Invoice Date </label>
                           <div class="col-md-8 col-xs-11">
                              <input id="invoice_date" name="invoice_date" type="text" class="form-control default_date" title="Invoice Date" value="<?=$order_date?>" placeholder="Invoice Date" autocomplete="off" tabindex="9" required>
                           </div>
                        </div>
                     </div>

						</div>
						<div class="row">
							<div class="col-md-4" style="<?=$style?>" id="service_div">
								<div class="form-group">
									<label class="col-md-4 control-label">Service No</label>
									<div class="col-md-8 col-xs-11">
									<select id="service_id" name="service_id[]" class="select2" title="Select Service No" placeholder="Select Service No" multiple="multiple" tabindex="10"  >	
										<!-- <=get_service_for_purchase($dbcon,$vender_id,$service_id,"")?> -->
									</select>	
									</div>
								 </div>
							</div>
						</div>
						<div class="row">
							
							<div class="col-md-4" style="display:none">
								<div class="form-group">
								  <label class="col-md-4 control-label">Currency Converter *</label>
									<div class="col-md-8 col-xs-11">
									
										<input id="currency_enable" name="currency_enable" type="checkbox" class="" title="" value="1" style="width:20%;height:25px;" tabindex="11" onChange="currency_change();" <?php if($mode=='Edit' && $rel['currency_enable']==1){ echo "checked";  }  ?>>
									
									</div>
								 </div>
							</div>
							
							<div class="col-md-4 currency_div"  style="display:block">
								<div class="form-group">
									<label class="col-md-4 control-label">Convert Currency *</label>
									<div class="col-md-6 col-xs-11">
										<select class="select2" name="currency_id" id="currency_id" onChange="get_symbol();currency_rate_c();" tabindex="12">
											<?=getcurrency($dbcon,$currency_id);?>
										</select>
										
									</div>
								</div>
							</div>
							
							<div class="col-md-4 currency_div" style="display:block">
								<div class="form-group">
								  <label class="col-md-4 control-label">Rate *</label>
									<div class="col-md-6 col-xs-11">
										<input id="currency_rate" name="currency_rate" type="text" class="form-control valid numbersOnly" title="" value="<?=$rel['currency_rate']?>" placeholder="" tabindex="13">
									</div>
								</div>	
							</div>
							
						</div>

						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
								  <label class="col-md-4 control-label">Sales Type</label>
										<div class="col-md-8 col-xs-11">
										
											<select class="form-control" name="sales_type" id="sales_type">
												<option value="1" <?php if($rel['sales_type']==1){ echo "selected"; } else{ echo ""; } ?>>Item Wise Tax</option>
												<option value="2" <?php if($rel['sales_type']==2){ echo "selected"; } else{ echo ""; } ?> >Merchant</option>
												<option value="5" <?php if($rel['sales_type']==5){ echo "selected"; } else{ echo ""; } ?> >Import</option>
												<option value="3" <?php if($rel['sales_type']==3){ echo "selected"; } else{ echo ""; } ?> >GST 0%</option>
												<option value="4" <?php if($rel['sales_type']==4){ echo "selected"; } else{ echo ""; } ?> >GST 5%</option>
												<option value="6" <?php if($rel['sales_type']==6){ echo "selected"; } else{ echo ""; } ?> >GST 12%</option>
												<option value="7" <?php if($rel['sales_type']==7){ echo "selected"; } else{ echo ""; } ?> >GST 18%</option>
												<option value="8" <?php if($rel['sales_type']==8){ echo "selected"; } else{ echo ""; } ?> >GST 24%</option>
											</select>
										</div>
								 </div>
							</div>
						</div>
						
						
                    </div>
                     
					 <div class="card">
							<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
								<li role="presentation" id="tab1" class="active"><a href="#pro_detail" aria-controls="pro_detail" role="tab" data-toggle="tab">Product Details</a></li>
								<li role="presentation" id="tab2"><a href="#produ_des" aria-controls="produ_des" role="tab" data-toggle="tab">Product Descrition</a></li>
							</ul>
							<div class="tab-content"> 
								<div role="tabpanel" class="tab-pane active" id="pro_detail">
									<div class="row">
										<div class="col-md-12" style="margin-top:10px;">
											<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
												<tr id="field" >
												   <!--<th width="4%" class="text-center grn">GRN</th>-->
												   <th width="20%" class="text-center">Product</th>
												    <th width="6%" class="text-center">Per</th>
												   <th width="6%" class="text-center">Quantity</th>
												   <th width="6%" class="text-center">Rate <span class="currency_icon"></span></th>
												   <th width="6%" class="text-center">Discount <span class="currency_icon"></span></th>
												   <th width="9%" class="text-center">Amount <span class="currency_icon"></span></th>
												   <th width="5%" class="text-center"></th>
												</tr>
												<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
												<tr id="field1">
												   <!--<td class="grn" style="vertical-align:top;">
													  <select class="select2" name="grn_id" id="grn_id" onChange="load_grn_data(this.value);">
													  <?//=get_grn_for_purchase($dbcon,$rel['vender_id'],"",$mode);?>
													  </select>
												   </td>-->
												   <td style="vertical-align:top;max-width:300px">
												     <div class="col-md-8">
													 	<input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onChange=" get_hsn(this.value);load_productdetail(this.value);job_work_process(this.value);" />
														  <strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>
														  <br/><br/>
														  <textarea id="product_des" name="product_des" tabindex="15" class="form-control" ></textarea>
												     </div>
												      <?phpif($getspecialConfiguration['invoite_permission'] !=1){ ?>
													  <div class="col-md-4">   
					                            	<button accesskey="p" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" title="Short-Cut To Open PopUp, Shift + Alt + p " value="R1" onclick="showproduct();"><i class="fa fa-plus"></i> Add Product</button>
					                            	
					                          </div>
					                       <?php} ?>
												   </td>
												   <td style="vertical-align:top;">
											   		<select class="form-control"  title="Select Unit" placeholder="Unit" name="rate_unit_id" tabindex="16" id="rate_unit_id" onchange="load_product_unit();get_product_price();">
                                        		<?//=getunit($dbcon,0);?>
                                             <option value="0">Select Unit</option>
                                          </select>
												   </td>
												   <td style="vertical-align:top;">
												   	 <div id="convert_unit_block" style="display:none;" >
                                              <input type="text"  title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(1);" onchange="get_discount('per');" tabindex="17" />
                                        	    <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
                                              <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
                                              <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show">  </span>
                                          </div>
                                          <div id="base_unit_block" style="" >
													  		<input type="text" min="0" id="product_qty" name="product_qty" onchange="get_discount('per');" tabindex="18"  class="form-control numbersOnly" onkeyup="product_convert_qty(2);"/>
													 		<input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
													 		<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show"></span>
													  		<input type="hidden" class="form-control" name="unitid" id="unitid" />
													  	</div>
												   </td>
												   <td style="vertical-align:top;">
													  <input type="text"  title="Enter INR Rate" min="0" id="product_rate" name="product_rate"onchange="get_amount();" data-pcard="0" data-pcardid="0" tabindex="19"  onkeyup="get_amount(); get_currency_amount('1', this.value);" class="form-control numbersOnly"/><br/>
													  <button type="button" title="Show Previous Rate History" name="rate_history" id="rate_history" onclick="load_rate_hist()" class="btn btn-info"><i class="fa fa-eye"></i></button>
												   </td>
												   
												   <td style="vertical-align:top;">
													  <input type="number" title="Enter Discount" min="0" id="product_discount" name="product_discount" onkeyup="get_discount('amt');" class="form-control" tabindex="20" placeholder="in Rs."/><br/>
													  <input type="number"  title="Enter Discount Percentage" min="0" id="discount_per" name="discount_per" onkeyup="get_discount('per');" class="form-control" placeholder="in %" max="100" tabindex="21" />
												   </td>
												   <td style="vertical-align:top;"> 
													  <input type="number" min="0" id="product_amount" readonly="readonly" name="product_amount" tabindex="22" class="form-control"/>
												   </td>
												   <td width="5%">
													  <input type="button"  name="addrow" id="addrow" onClick="return add_field();" tabindex="23"  class="btn btn-primary" value="Add"/>
												   </td>
												   <input type='hidden' name='edit_id' id='edit_id' value='' />
												   <input type='hidden' name='pro_cal_type' id='pro_cal_type' value='' />
												</tr>
											 </table>
											 <div id="sale_productdata"></div>
										</div>
									</div>
								</div>
								<div role="tabpanel" class="tab-pane" id="produ_des">
									<div class="col-md-6" style="margin-top:12px;padding:10px" >
										<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;"> Description </label>
										<div class="col-md-12">
											<div class="form-group">
												<textarea class="form-control" placeholder="Product Description" name="pro_des" id="pro_des" ></textarea>
											</div>
										</div>
									</div>
									
									<div class="col-md-6" style="margin-top:12px;padding:10px" >
										<label class="col-md-12 control-label text-left" style="text-align:left;font-weight:bold;"> Specification </label>
										<div class="col-md-12">
											<div class="form-group">
												<textarea class="form-control" placeholder="Product Specification" name="pro_spe" id="pro_spe" ></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
					<div class="row">
						
						<div class="col-md-6 tax_details">
							
							
							
						</div>
						
						
						 <div class="col-md-6">
							
							<div class="row row_margin">
							
								<div class="form-group">
									<label class="col-md-5 control-label text-right">Total * <span class="currency_icon"></span></label>
									<div class="col-md-5 col-xs-11">
										<input id="total" name="total" type="text" readonly="readonly" class="form-control" title="Grand Total" max="0"  value="<?phpif($mode=="Add"){echo '0';}else if($mode=='Edit'){ echo $e_total;}?>" placeholder="total" tabindex="24">
									</div>
								</div>	
								
							</div>
							
							<div class="row invoiceTotalTax row_margin">
								
							</div>
							
							<div class="row sundryadded row_margin">
								
							</div>
							
							<div class="row row_margin">
							
								<div class="form-group">
									<label class="col-md-5 control-label text-right">Round Off * <span class="currency_icon"></span></label>
									<div class="col-md-5 col-xs-11">
										<input id="round_of" name="round_of" type="text" class="form-control" title="Round Off" value="<?=$rel['round_of']?>" placeholder="Round Off" onKeyUp="get_gtotal_roundoff();"  tabindex="25">
									</div>
								</div>	
							
							</div>

							<div class="row row_margin">
							
								<div class="form-group">
									<label class="col-md-5 control-label text-right">Net Amount * <span class="currency_icon"></span></label>
									<div class="col-md-5 col-xs-11">
										<input id="g_total" name="g_total" type="text" class="form-control" title="Net Amount" value="<?=$rel['g_total']?>" placeholder="Grand Total" readonly="readonly" tabindex="25">
									</div>
								</div>	
							
							</div>
							
							<div class="row row_margin">
								<div class="form-group">
									<label class="col-md-5 control-label text-right">Select Bill Sundry</label>
									<div class="col-md-2">
										<?php $get_bill_sundry = get_bill_sundry_ledger($dbcon,0); ?>
										<select class="form-control" name="bill_sundry" id="bill_sundry" onchange="get_sundry_label(this.value)">
											<option value="0">Select</option>
											<?php foreach ($get_bill_sundry as $sundry) {
												
											 ?>
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
							
							<div class="row row_margin"> 
							
								<div class="form-group">
									<label class="col-md-5 control-label text-right">Select Print</label>
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
							
						</div>
					
					
					</div>
										       
					
					<div class="row">
						
						<div class="col-md-4">
								<div class="form-group">
									<label class="col-md-4 control-label">Advance Payment Adjustment</label>
									<div class="col-md-8 col-xs-11">
									
										<select class="form-control" tabindex="26" name="bill_adjustment" id="bill_adjustment" onchange="get_bill_adjsutment(this.value,'2')">
											<option value="">--Select Advance Adjustment--</option>
											<option value="1" <?php if($mode=='Edit' && $rel['enable_bill_adjustment']==1){ echo "selected"; } else{ echo ""; } ?> >Yes</option>
											<option value="0" <?php if($mode=='Edit' && $rel['enable_bill_adjustment']==0){ echo "selected"; } else{ echo ""; } ?>>No</option>
										</select>
										<a href="#" class="adjust_advance_link" onclick="get_bill_adjsutment('1','2')" style="display: none;">Adjust Advance Payment</a>
									</div>
								 </div>
							</div>

						<div class="col-md-4"  style="display:none" id="div_cost_center">
							<div class="form-group">
								<label class="col-md-4 control-label">Cost Center *</label>
								<div class="col-md-8 col-xs-11">
									<select class="form-control" name="enable_cost_center" id="allocate_cost_center" onchange="get_cost_center(this.value)" tabindex="27">
										<option value="no" selected>No</option>
										<option value="yes" <?php if($mode=='Edit' && $rel['enable_cost_center']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
									</select>
									
									<?php if($mode=='Edit' && $rel['enable_cost_center']==1){ $style=""; } else { $style='display:none'; } ?>
									
									<a style="<?=$style;?>" href="#" id="cost_center_link" onclick="get_cost_center('yes')">Show Cost Center Transaction</a>
								</div>
							</div>
						</div>
						
						<div class="col-md-4 row_margin" style="display:none" id="eway_div">
							<div class="form-group">
								<label class="col-md-4 control-label">Auto Eway Bill *</label>
								<div class="col-md-8 col-xs-11">
									<select class="form-control" name="enable_ewaybill" id="enable_ewaybill"  onchange="get_eway_bill(this.value,'auto_eway')" tabindex="28">
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
									<select class="form-control" tabindex="29"
									name="enable_transport" id="enable_transport"  onchange="get_eway_bill(this.value,'transport')">
										<option value="no" selected>No</option>
										<option value="yes" <?php if($mode=='Edit' && $rel['enable_transport']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
									</select>
									 <?php if($mode=='Edit' && $rel['enable_transport']==1){ $style=""; } else { $style='display:none'; } ?>
									<a style="<?=$style;?>" href="#" id="transport_link" onclick="get_eway_bill('yes','transport')">Show Transport Details</a>
								</div>
							</div>
						</div>
						
							
						
					</div>
					<div class="row">

					<?php /**<div class="col-md-4" style="display:none" id="salesman_div">
							<div class="form-group">
								<label class="col-md-4 control-label">Enable Salesman *</label>
								<div class="col-md-8 col-xs-11">
									<select class="form-control" name="enable_salesman" id="enable_salesman"  onchange="get_ledger_salesman(this.value,'total')">
										<option value="no" selected>No</option>
										<option value="yes" <?php if($mode=='Edit' && $rel['enable_salesman']==1){ echo "selected"; } else{ echo ""; } ?>>Yes</option>
									</select>
									
									<?php if($mode=='Edit' && $rel['enable_salesman']==1){ $style=""; } else { $style='display:none'; } ?>
									
									<a style="<?=$style;?>" href="#" id="salesman_link" onclick="get_ledger_salesman('yes','total')">Show Salesman Details</a>
								</div>
							</div>
						</div> **/ ?>
						
						<div class="col-md-4">
													
							 <div class="form-group">
									<label class="col-md-4 control-label">EWay Bill No </label>
									<div class="col-md-8 col-xs-11">
											<input type="text" class="form-control" name="eway_bill_no" id="eway_bill_no" tabindex="30" />
									</div>
							</div>
						</div>

						<div class="col-md-4">
													
							 <div class="form-group">
									<label class="col-md-4 control-label">EWay Bill Date </label>
									<div class="col-md-8 col-xs-11">
											<input type="text" class="form-control default-date-picker" name="eway_bill_date" id="eway_bill_date" tabindex="31" />
									</div>
							</div>
						</div>	

					</div>
					<div class="clearfix"></div>
					<div class="row" style="margin-top:10px;">
							<div class="col-md-12">
								<button type="submit" class="btn btn-success" id="save" name="save" tabindex="32">Save</button>
								<button type="button" onClick="invoice_submit();" class="btn btn-success" id="saveprint" name="saveprint" tabindex="33">Save and Print</button> &nbsp;
								<a href="<?=ROOT.PURCHASE_ROOT.'purchase_list'?>" type="button" class="btn btn-danger" tabindex="34">Cancel</a>
								<div class="col-md-3"></div>			
							</div>		
						
						<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
						<input type='hidden' name='o_total' id='o_total' value='<?=$rel['g_total']?>' />
						<input type='hidden' name='save_print' id='save_print' value='' />
						<input type='hidden' name='eid' id='eid' value='<?=$mode=='Edit'?$rel['po_id']:'0'?>' />
						<input type='hidden' name='so_trn_id' id='so_trn_id' value='<?=$so_trn_id?>' />
						<input type='hidden' name='sales_order_id' id='sales_order_id' value='<?=$sales_order_id?>' />
						<input type='hidden' name='quotation_id' id='quotation_id' value='<?=$quotation_id?>' />
						<input type='hidden' name='complaint_id' id='complaint_id' value='<?=$complaint_id?>' />
						
						<!-- Financial Year Setting start -->
											
						<input type='hidden' name='financial_year' id='financial_year' value='<?=$financial_year['financial_year_id'];?>' />
						<input type='hidden' name='financial_start_date' id='financial_start_date' value='<?=$financial_year['financial_start_date'];?>' />
						<input type='hidden' name='financial_end_date' id='financial_end_date' value='<?=$financial_year['financial_end_date'];?>' />
						
						<!-- Financial Year Setting end -->
											
						
						<!-- Company Settings -->
						
						<input type="hidden" name="company_cost_center" id="company_cost_center" value="<?=$company_config['enable_cost_center']?>" />
						
						<input type="hidden" name="company_salesman" id="company_salesman" value="<?=$company_config['enable_salesman']?>" />
						
						<input type="hidden" name="company_tcs" id="company_tcs" value="<?=$company_config['enable_tcs_reporting']?>" />
						
						<input type="hidden" name="company_eway" id="company_eway" value="<?=$company_config['enable_eway_bill']?>" />
						<input type="hidden" name="company_trans" id="company_trans" value="<?=$company_config['enable_transport']?>" />
						
						<input type="hidden" name="enable_multi_currency" id="enable_multi_currency" value="<?=$company_config['enable_multi_currency']?>" />
						
						<input type="hidden" name="company_tax_editable" id="company_tax_editable" value="<?=$company_config['tax_editable']?>" />
						
						<!-- cost center popup --> 
						
						<input type="hidden" name="cost_center_voucher_type" id="cost_center_voucher_type" value="<?=PURCHASE_VOUCHER?>" />
						<input type="hidden" name="cost_center_ledger_id" id="cost_center_ledger_id" placeholder="Ledger Id" value="<?=$mode=='Edit'?$rel['vender_id']:'' ?>">
						<input type="hidden" name="cost_center_table" id="cost_center_table" value="tbl_pono" placeholder="table name of sale , purchase , payment..">
						<input type="hidden" name="cost_center_table_id" id="cost_center_table_id" value="<?=$mode=='Edit'?$rel['po_id']:'0'?>" placeholder="primary key of that inserted table ">
						<input type="hidden" id="edit_id" value="" />
						
						<!-- Transport and Eway bill transaction popup -->
						<input type="hidden" name="transport_voucher" id="transport_voucher" value="<?=PURCHASE_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
						<input type="hidden" name="transport_transaction_table" id="transport_transaction_table" placeholder="table name of sale , purchase , payment.." value="tbl_pono">
						<input type="hidden" name="transport_transaction_table_id" id="transport_transaction_table_id" placeholder="primary key of that inserted table " value="<?=$mode=='Edit'?$rel['po_id']:'0'?>">
						<input type="hidden" id="edit_id_transport" value="<?=$mode=='Edit'?$rel['po_id']:'0'?>" />

						<!-- Transport and Eway bill transaction popup -->
						<input type="hidden" name="eway_bill_voucher_type" id="eway_bill_voucher_type" value="<?=PURCHASE_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
						<input type="hidden" name="eway_bill_voucher_table" id="eway_bill_voucher_table" placeholder="table name of sale , purchase , payment.." value="tbl_pono">
						<input type="hidden" name="eway_bill_voucher_id" id="eway_bill_voucher_id" placeholder="primary key of that inserted table ">
						<input type="hidden" id="edit_id_ewaybill" value="<?=$mode=='Edit'?$rel['po_id']:'0'?>" />
						
						<!-- Salesman transaction popup -->
						<input type="hidden" name="salesman_voucher_type" id="salesman_voucher_type" value="<?=PURCHASE_VOUCHER?>" placeholder="Voucher Type eg. sale , purchase">
						<input type="hidden" name="salesman_voucher_table" id="salesman_voucher_table" placeholder="table name of sale , purchase , payment.." value="tbl_pono">
						<input type="hidden" id="bill_type" name="bill_type" value="<?=$bill_type?>">
						<input type="hidden" id="edit_id_salesman" value="" />
						
					</div>
					</form>
				</section>
               </div>
               </div>		
            </section>
         </section>
       
			<?php 
				  include_once($include_finance.'add_cost_center.php');
				  include_once($include_finance.'add_tcs_details.php'); 
				  include_once($include_finance.'add_eway_bill.php');
				  include_once($include_finance.'add_salesman.php');
				  include_once($include_finance.'add_bill_adjustment.php');

				  include_once($include_finance.'add_ledger.php');
				 	include_once($include1.'vendor_product_price_list.php');
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
        <?php include_once($include.'footer.php');?>
      </section>
      <?php include_once($include.'/include_js_file.php');?>   
      <script src="<?=ROOT.PURCHASE_ROOT?>js/app/purchase.js?<?=time()?>"></script>
	  <script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/common_form_finance.js?<?=time()?>"></script>
	  <script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
	  <script src="<?=ROOT?><?=FINANCE_ROOT?>js/app/add_ledger_js.js?<?=time()?>"></script>
		<script src="<?=ROOT?><?=ADMINISTRATION_ROOT?>js/app/ledger.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/consignee.js?<?=time()?>"></script>
 		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/product_mst.js?<?php echo time(); ?>"></script>
 		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/hsn_master.js?<?php echo time(); ?>"></script>
 		
      <script>
         //$('#container').addClass('sidebar-closed');
         $(".select2").select2({
         	width: '100%'
         });
         // $("#product_id").select2({
         // 	width: '100%',
         // 	minimumInputLength: 2
         // });
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
         $(".form_datetime").datetimepicker({
         	format: 'dd-mm-yyyy hh:ii',
         	autoclose: true,
         	todayBtn: true,
         	pickerPosition: "bottom-left"
         
         });
		 CKEDITOR.replace( 'pro_des', {
			enterMode: CKEDITOR.ENTER_BR
		});

		CKEDITOR.replace( 'pro_spe', {
			enterMode: CKEDITOR.ENTER_BR
		});
         <?if($mode=='Add'){?>
         	//load_purchase_srs_no();
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
				get_statecode(<?=$vender_id?>);
         	$('#vender_id').select2('readonly',true);
         <?php} ?>
         <?	if(strpos($_SERVER['REQUEST_URI'], "purchase_bill_service_pending")==true){ ?>
         		load_service_bill(<?=$vender_id?>,<?=$service_id?>);
         		insert_service_data(<?=$service_id?>);
         		$('#vender_id').select2('readonly',true);
      	<?	} ?>
      </script>
      <?php 
         echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
         echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>";
         
         echo "<script>load_state(".$countryid.",'con_stateid',".$stateid.")</script>";
         echo "<script>load_city(".$stateid.",'con_cityid',".$cityid.")</script>";
         ?>
   </body>
</html>

