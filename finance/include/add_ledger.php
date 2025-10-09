<?php 
$company_multicurrency = getCompanyConfiguration($dbcon);

if($company_multicurrency['ledger_code'] ==1){
	$readonly_code = "readonly";
}else{
	$readonly_code = 'onkeyup="check_manual_ledger_code(this.value)"';
}
$leger_per = $company_multicurrency['ledger_code'];
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

 ?>
<div class="modal colored-header info " id="modal-add-ledger" role="dialog" data-keyboard="false" data-backdrop="static" style="overflow-y:auto;">
	<div class="modal-dialog modal-lg xlg" style="width: 1300px;height: 2000px;">
		<div class="modal-content">
			<div class="modal-header">
				<!-- <button type="button"  class="btn_close  close md-close" accesskey="c" data-dismiss="modal" aria-hidden="true">&times;</button> -->
				<h3>Add Ledger</h3>				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<form class="form-horizontal" role="form" id="ledger_add" action="javascript:;" method="post" name="ledger_add" enctype="multipart/form-data">
					<input type="hidden" name="direct_ledger_add" value="1" >
					<input type="hidden" name="ledger_add_type" id="ledger_add_type" value="" >	
			<section id="main-content">
				<section class="wrapper" style="margin-top: 0px;">			
					
					<!--state overview start-->
					<div class="row">			
						<div class="col-sm-12">

							<section class="panel">
								

								<div class="panel-body ">
									<div class="row">

										<div class="col-md-12 margin_row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Ledger Name *</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" placeholder="Ledger Name" title="Ledger Name" name="ledger_name" maxlength="100" id="ledger_name" value="" required onblur="check_duplicate_ledger(this.value)"  />
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
														<input type="text" class="form-control" placeholder="Alias Name" maxlength="100" title="Alias Name" name="alias_name" id="alias_name" value=""  />
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Select Group*</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="ledger_grp" id="ledger_grp" required onchange="show_div_ledger(this.value);load_ledger_code(this.value,'<?=$leger_per?>');" >
															<?=get_all_group($dbcon,'','','0');?>
														</select>
														<?php
									                    if($disable){
									                        echo '<input type="hidden" name="ledger_grp" id="ledger_grp" value="">';
									                    	}
									                    ?>
									                    
									                    <input type="hidden" class="form-control" name="group_id" id="group_id" / >


														<input type="hidden" class="form-control" name="parent_group_id" id="parent_group_id" / >									                    

													</div>
												</div>
											</div>

										</div>

										<div class="col-md-12 margin_row">
											<div class="col-md-6" id="lcode_div">
												<div class="form-group">
													<label class="col-md-4 control-label">Ledger Code *</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control" placeholder="Ledger Code" title="Ledger Code" name="ledger_code" id="ledger_code" value="" required <?=$readonly_code?> />
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Email Id</label>
													<div class="col-md-8 col-xs-11">
														<input type="email" class="form-control" placeholder="Email" title="Please insert valid Email" name="common_email_id" id="common_email_id" value="" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}"  />
													</div>
												</div>
											</div>
										</div>

										<div class="col-md-12 margin_row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Choose Branch *</label>
													<div class="col-md-8 col-xs-11">
														<select class="select2" name="branch_id" id="branch_id">
															<?=getBranchBox($dbcon,'');?>
														</select>
													</div>
												</div>	
											</div>
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
														<select class="select2" name="countryid" id="countryid" onChange="load_state(this.value,'stateid','')">
															<?=get_country($dbcon,'101')?>				
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Select State *</label>
													<div class="col-md-6 col-xs-11">
														<select class="select2" name="stateid" id="stateid" onChange="load_city(this.value,'cityid','')">
															<option value="">Select State</option>	
															<?php //=getstate($dbcon,$rel['stateid'])?>
														</select>
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
													
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Pin Code</label>
													<div class="col-md-8 col-xs-11">
														<input type="text" class="form-control numbersOnly digitOnly"  placeholder="Customer Pincode" name="cust_pincode" id="cust_pincode" value="" maxlength="6" minlength="6" onkeypress="return isNumberKey(event)"  />
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12 row_margin">
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Opening Balance</label>
													<div class="col-md-8 col-xs-11">
														<input type="text"  class="form-control" id="opn_balance" maxlength="20" name="opn_balance" placeholder=""  value="" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);"  />
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label">Balance Type *</label>
													<div class="col-md-8 col-xs-11">
														<select class="form-control" name="balance_typeid" id="balance_typeid" title="Select Type">
															<?=getbalance_type($dbcon,'')?>				
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
															<option value="0">New Ledger</option>
									    					<option value="1">Exist Ledger</option>		
									    				</select>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													
												</div>
											</div>
										</div>

									</div>
								</div>
							</section>

						</div>

						<!--- Customer Form Start -->

						<div class="col-md-12 ledger_forms" id="customer_form" style="display:block !important" >

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
															<input type="text" class="form-control" placeholder="Company Name" title="Company Name" name="company_name" id="company_name" value=""/>
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Contact Person Name*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control" placeholder="Contact Person Name" title="Contact Person Name" name="cust_cont_name" id="cust_cont_name" value="" required />
														</div>
													</div>
												</div>
											</div>

											<div class="col-md-12 margin_row">

												<div class="col-md-12">
													<div class="form-group">
														<label class="col-md-2 control-label">Company Address*</label>
														<div class="col-md-10 col-xs-11">

															<textarea class="form-control" placeholder="Company Address" maxlength="350" title="Company Address" name="m_address" id="m_address" required></textarea>

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
																<option value="0" >Registered</option>
																<option value="1">Unregistered</option>
																<option value="2">Composition</option>
																<option value="3">Govt.body</option>
																<option value="4">UIN Holder</option>
															</select>
														</div>
													</div>
												</div>

												<div class="col-md-6" id="gst_div" style="display:none">
													<div class="form-group">
														<label class="col-md-4 control-label">GSTIN *</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" name="gst_no" class="form-control blockSpecialChar" onblur="getPanNo(this.value)" minlength="15" maxlength="15" placeholder="GSTIN" id="gst_no" value="" title="Please enter Valid 15 digit GST No." >
														</div>
													</div>
												</div>
											</div>								

											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Mobile No.</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control digitOnly numbersOnly" placeholder="Mobile No." name="cust_mobile" id="cust_mobile" value="" onkeypress="return isNumberKey(event)" maxlength="10" minlength="10"  />
														</div>
													</div>
												</div>

												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Email</label>
														<div class="col-md-8 col-xs-11">
															<input type="email" class="form-control" placeholder="Email" title="Please insert valid Email" name="cust_email" id="cust_email" value="" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}"  />
														</div>	
													</div>
												</div>
											</div>

											<div class="col-md-12 margin_row">
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-4 control-label">Website </label>
														<div class="col-md-8 col-xs-11">
															<input type="text" class="form-control copyPastNotAllowed" placeholder="Website" title="Website" name="cust_website" id="cust_website" value=""  />
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
                                                					<?=get_zone($dbcon,'','');?>				
                                                				</select>
                                                			</div>	
                                                			
                                                		</div>
                                                	</div>

                                                </div>

                                                <div class="col-md-12 margin_row">

<!--									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Branch</label>
											<div class="col-md-6 col-xs-11">
												<select class="select2" name="branch_id_customer" id="branch_id_customer">
																	
												</select>
											</div>	
										</div>
									</div>-->
									<div class="col-md-6 hide">
										<div class="form-group">
											<label class="col-md-4 control-label">Party Type</label>
											<div class="col-md-6">
												<select class="select2" name="party_sez" id="party_sez">
													<option value="0">Non SEZ</option>
													<option value="1">SEZ</option>
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
													<option value="yes" >Yes</option>
													<option value="no" >No</option>										
												</select>
											</div>
										</div>
									</div>
								</div>
								
								
								<div class="col-md-12 margin_row">
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Payment Terms</label>
											<div class="col-md-8 col-xs-11">
												<select class="select2" name="pay_terms" id="pay_terms">
													<?=getpaymentterms($dbcon,'');?>
													
												</select>
											</div>	
											
										</div>
									</div>
									
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Bill Type</label>
											<div class="col-md-8 col-xs-11">
												<select class="select2" name="bill_type" id="bill_type">
													<option value="">--Select Bill Method--</option>
													<option value="0" >Bill To Bill</option>
													<option value="1" >Overall</option>
												</select>
											</div>
										</div>
									</div>
								</div>

								<div class="col-md-12 margin_row">
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Credit Limit </label>
											<div class="col-md-8 col-xs-11">
												<input type="text" class="form-control digitOnly numbersOnly" placeholder="Credit Limit" title="Credit Limit" maxlength="20" name="credit_limit" id="credit_limit" value="" onkeypress="return isNumberKey(event)"  />
											</div>	
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label">Credit Days </label>
											<div class="col-md-8 col-xs-11">
												<input type="text" class="form-control digitOnly numbersOnly" maxlength="20" placeholder="Credit Days" title="Credit Days" name="credit_days" id="credit_days" value="" onkeypress="return isNumberKey(event)"  />
											</div>	
										</div>
									</div>
								</div>

								<div class="col-md-6 margin_row">
									<div class="form-group">
										<label class="col-md-4 control-label">Remark</label>
										<div class="col-md-8 col-xs-11">
											<textarea class="form-control" name="cust_remark" id="cust_remark"></textarea>
										</div>	
									</div>
								</div>
								<div class="col-md-6 margin_row">
									<div class="form-group">
										<label class="col-md-4 control-label">Bill By Bill Opening Balance</label>
										<div class="col-md-8 col-xs-11">
											<select class="form-control" name="enable_billbybill_opening" id="enable_billbybill_opening" onchange="getBillByBillPopup(this.value)" >
												<option value="">--Select Bill By Bill Opening--</option>
												<option value="yes" >Yes</option>
												<option value="no" >No</option>							
											</select>
											
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
										<!-- <li><a href="#transportation" data-toggle="tab" id="ltbopen">Transportation</a></li> -->
										<li><a href="#tconsignee" data-toggle="tab" id="ltbopen">Consignee</a></li>
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
															<td><input type="text" class="form-control copyPastNotAllowed blockSpecialChar" name="ac_name" maxlength="50" value="" id="ac_name" /></td>
															<td>
																<input type="text" class="form-control copyPastNotAllowed blockSpecialChar" name="bank_ifsc" maxlength="11" value="" id="bank_ifsc" />
															</td>
															<td>
																<input type="text" class="form-control copyPastNotAllowed" name="bank_open" value="" maxlength="20" id="bank_open" onkeypress="return isNumberKey(event)" />
															</td>
															
															<td><input type="button" class="btn btn-primary" value="ADD" maxlength="20"  style="box-shadow: 3px 3px #61a642;" onclick="add_bank()" id="add_bank_bt" /></td>
															
															<input type="hidden" id="edit_id" value=""  />
															<input type="hidden" id="eid" value=""  />
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
															<td><input type="button" class="btn btn-primary" value="ADD"  style="box-shadow: 3px 3px #61a642;" onclick="add_contact_person()" id="add_contact_bt" /></td>
															
															<input type="hidden" id="edit_id_contact" value=""  />
														</tr>
														
													</table>

												</div>
												
												<div class="col-md-12" id="table_contact_details"></div>
												
											</div>
											
										</div>
										<!-- <div class="tab-pane" id="transportation">
											
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
											
										</div> -->
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
															<td width="8%"></td>
														</tr>
														<tr>
															<td>
																<select class="select2" name="country_consinee_id" id="country_consinee_id" onChange="load_consinee_state(this.value,'state_consinee_id','')" autocomplete="off">
																	<?=get_country($dbcon,'')?>				
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
																<input type="button" class="btn btn-primary" value="ADD" style="box-shadow: 3px 3px #61a642;" onclick="add_consignee()" id="add_consignee_btn" />
															</td>
															<input type="hidden" id="edit_id_consignee" value=""  />
														</tr>
													</table>
												</div>
												<div class="col-md-12" id="table_consignee_details"></div>
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

			<!--- Customer Form End -->

			<!--- Bank Form Start -->
			<div class="col-md-12 ledger_forms" id="bank_form" style="display:block !important" >

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
													<?=getbank($dbcon,'')?>
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
												<input type="text"  class="form-control" id="branch_name" name="branch_name" maxlength="100" placeholder="" value="" required/>
											</div>
										</div>
										
									</div>
									

								</div>

								<div class="col-md-12 row_margin">
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Account Name *</label>
											<div class="col-md-8 col-xs-11">
												<input type="text" maxlength="100"  class="form-control" id="acc_name" name="acc_name" value="" required title="Enter Account Name" />
											</div>
										</div>
										
									</div>
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Account Number *</label>
											<div class="col-md-8 col-xs-11">
												<input type="text"  class="form-control numbersOnly" id="acc_number" name="acc_number" value="" placeholder="" required title="Enter Account Number" />
											</div>
										</div>
										
									</div>
									
								</div>

								<div class="col-md-12 row_margin">
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Cheque Series Starting Number </label>
											<div class="col-md-8 col-xs-11">
												<input type="text"  class="form-control" id="acc_chequeno" name="acc_chequeno" value="" placeholder=""  min="0" onkeypress="return isNumberKey(event)" />
											</div>
										</div>
										
									</div>
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-4 control-label">Number of Cheques </label>
											<div class="col-md-8 col-xs-11">
												<input type="text"  class="form-control numbersOnly" id="acc_chequeleft" name="acc_chequeleft" value="" placeholder="" min="0" max="100"  onkeypress="return isNumberKey(event)" />
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

			<div class="col-md-12 ledger_forms" id="emp_form" style="display:block !important" >

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
												<input type="text" class="form-control" placeholder="Email" title="Email" name="emp_email" id="emp_email" value=""  onkeyup="checkUsername(this.value);" required pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}" />
												
												<input  type="hidden" class="form-control" placeholder="Email" title="Email" name="" id="emp_email_hid" value=""  />
												
												<div id="user_error"></div>
											</div>	
										</div> 
										
									</div>
									
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-3 control-label">Password*</label>
											<div class="col-md-6 col-xs-11">
												<input type="password" class="form-control" placeholder="Password" title="Password" maxlength="15" name="emp_password" id="emp_password" />
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
												<input type="text" class="form-control digitOnly numbersOnly" placeholder="Mobile No." name="emp_mobile" id="emp_mobile" value="" onkeypress="return isNumberKey(event)" maxlength="10" minlength="10" required  />
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
                                   						<?=get_zone($dbcon,'')?>				
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
													<?=get_state_all($dbcon,'',"101")?>				
												</select>
											</div>	
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Allocated City</label>
											<div class="col-md-6">
												<select class="select2" name="alloc_cityid[]" id="alloc_cityid" placeholder="Allocated City" multiple>
													<?=get_city_all($dbcon,'','')?>	
												</select>
											</div>	
										</div>
									</div>
								</div>
								<div class="col-md-12 margin_row">
									<?php //$user_type = $_SESSION['user_type']; ?>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Report To User-Type </label>
											<div class="col-md-6">
												<select class="select2" name="report_to_user_type" id="report_to_user_type" title="Select Type" onchange="load_report_to_users(this.value)">
													<option value="">--Select User Type--</option>
													<?=getusertype($dbcon,$user_type," and (usertype_id!=1 or company_id=".$_SESSION['company_id'].")")?>			
												</select>
											</div>	
										</div>
									</div>
									<?php //$user_id = $_SESSION['user_id']; ?>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Report To User</label>
											<div class="col-md-6">
												<select class="select2" name="report_to_user_id" id="report_to_user_id" >
													<?=get_users_typewise($dbcon,$user_id," and user_type='".$user_type."'")?>			
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
													<?=getusertype($dbcon,''," and (usertype_id!=1 or company_id=".$_SESSION['company_id'].")")?>			
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
													echo getTemplateName($dbcon,'');
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
													
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-3 control-label">Shift Time</label>
											<div class="col-md-6 col-xs-11">

												<select class="select2" name="shift_time" id="shift_time">
													<?=get_shift_type($dbcon,'');?>
												</select>
											</div>
										</div>
									</div>
								</div>
								
							</div>
							
						</section>



						<div class="col-md-12 col-md-offset-4 row_margin">



						</div>

					</div>

				</div>

			</div>
			<!--- Employee Form End -->

			<!--- Tax Form Start -->

			<div class="col-md-12 ledger_forms" id="tax_form" style="display:block !important" >

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
												<input type="text"  name="tax_value"  id="tax_value" class="form-control numbersOnly" value="" maxlength="10"  placeholder="Tax Value(in %)" onkeypress="return isNumberKey(event)" maxlength="5" />
											</div>	
										</div> 
										
									</div>
									<div class="col-md-6">
										
										<div class="form-group">
											<label class="col-md-3 control-label">Print Priority</label>
											<div class="col-md-6 col-xs-11">
												<input type="text" maxlength="10"  name="print_priority"  id="print_priority" value="" class="form-control numbersOnly"  placeholder="Print Priority" />
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
												<option value="yes" >Yes</option>
												<option value="no" >No</option>
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
											<select class="form-control" name="enable_tds" id="enable_tds" onchange="ledger_grp_change();get_party_by_ledger();" >
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
											<select class="form-control" name="enable_tcs" id="enable_tcs" onchange="ledger_grp_change();" >
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
							<div class="col-md-6 party_pay_cat_div">
								<div class="form-group">
									<label class="col-md-4 control-label">Party Payee Category *</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="party_pay_cat" id="party_pay_cat" onchange="" title="Please Select Party Payee Category" >
																			
										</select>
										<input type="hidden" value="<?=$mode=='Edit'?$rel['party_pay_cat']:'';?>" id="party_pay_cat_text" name="party_pay_cat_text">
									</div>
								</div>
							</div>
							<!-- <div class="col-md-6 party_pay_cat_div">
								<div class="form-group">
									<label class="col-md-4 control-label">Party Payee Category *</label>
									<div class="col-md-8 col-xs-11">
										<select class="form-control" name="party_pay_cat" id="party_pay_cat" onchange="" title="Please Select Party Payee Category" >
											<?php
											//echo get_common_category($dbcon, 6,'Payee Category',$rel['party_pay_cat']);
											?>									
										</select>
									</div>
								</div>
							</div> -->
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

						<div class="col-md-12 col-md-offset-5 row_margin" >

							<input type="hidden" id="form_type" name="form_type" value='<?php if($mode=='Edit') { echo $form_type; } else { echo ""; } ?>'  />
							<input type='hidden' name='mode' id='mode' value='<?php if($mode=='Edit') { echo "edit"; } else { echo "add"; } ?>' />
							<input type='hidden' name='ledger_id' id='ledger_id' value='<?php if($mode=='Edit') { echo $ledger_id; } else { echo "0"; } ?>' />				  
							<button type="submit" name="" id="btn_submit" class="btn btn-success">Submit</button>
							<button type="button" class="btn btn-danger" onclick="remove_vendor_pop()">Cancel</button>

						</div>
					</div>

				</div>

			</section>


		</div>
		<!--state overview end-->
	</section>
</section>

</form>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>

