<?php 
	session_start();
	include('../include/urlfile.php');
	if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_add")==true){
		$form="NON RETURNABLE CHALLAN";
	}else{
		$form="RETURNABLE CHALLAN";
	}

	$getspecialConfiguration = getspecialConfiguration($dbcon);
	$is_power_drive = false;
	if ($getspecialConfiguration['power_drive'] == 1) {
		$is_power_drive = true;
	}	

	$branch_id = $_SESSION['branch_id'];
	$companyID = $_SESSION['company_id'];
	if(strpos($_SERVER['REQUEST_URI'], "returnable_channal_update")==true){
		$mode="Edit";
		$returnable_channal_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select rtn.* from tbl_returnable_channal as rtn
		left join tbl_returnable_channal_item as rtnch on rtnch.returnable_id=rtn.id
		where rtn.id=$returnable_channal_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$customer_id=$rel['cust_id'];
		$return_date = date('d-m-Y',strtotime($rel['return_date']));
		$challan_date = date('d-m-Y',strtotime($rel['challan_date']));
		$issue_date   = date('d-m-Y h:i A',strtotime($rel['issue_date']));
		$challan_type = $rel['challan_type'];
		$challan_return_type = $rel['challan_return_type'];
		$returnable_type = $rel['returnable_type'];
		$vehicle_no   = $rel['vehicle_no'];
		$mode_dispatch = $rel['mode_dispatch'];
		$back="returnable_channal_list";
	}else if(strpos($_SERVER['REQUEST_URI'], "returnable_channal_add")==true){
		$mode="Add";
		$grn_date=date('d-m-Y');
		$return_date = date('d-m-Y');
		$challan_date = date('d-m-Y');
		$back="returnable_channal_list";
	}else if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_add")==true){
		$mode="Add";
		$grn_date=date('d-m-Y');
		$return_date = date('d-m-Y');
		$challan_date = date('d-m-Y');
		$back="non_returnable_channal_list";
	}else if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_update")==true){
		$mode="Edit";
		$returnable_channal_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select rtn.* from tbl_returnable_channal as rtn
		left join tbl_returnable_channal_item as rtnch on rtnch.returnable_id=rtn.id
		where rtn.id=$returnable_channal_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$customer_id=$rel['cust_id'];
		$return_date = date('d-m-Y',strtotime($rel['return_date']));
		$challan_date = date('d-m-Y',strtotime($rel['challan_date']));
		$issue_date   = date('d-m-Y h:i A',strtotime($rel['issue_date']));
		$challan_type = $rel['challan_type'];
		$challan_return_type = $rel['challan_return_type'];
		$returnable_type = $rel['returnable_type'];
		$vehicle_no   = $rel['vehicle_no'];	 
		$mode_dispatch = $rel['mode_dispatch'];
		$back="non_returnable_channal_list";
	}
	$max_followup_date = MAX_FOLLOWUP_DATE;
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	$companyConfiguration=getCompanyConfiguration($dbcon);
	function curPageURL() {
	    $url = $_SERVER['REQUEST_URI'];
	    $url = explode('/', $url);
	    $lastPart = array_pop($url);

	    return $lastPart;
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?=$form?></title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<?php//include_once('../include/equick_link.php');?>
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<?php if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_list")==true){ ?>
											<li><a href="<?=ROOT.INVENTORY_ROOT.'non_returnable_channal_list'?>"><?=$form?> LIST</a></li>
										<?php }else{ ?>
											<li><a href="<?=ROOT.INVENTORY_ROOT.'returnable_channal_list'?>"><?=$form?> LIST</a></li>
										<?php } ?>
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
									<form class="form-horizontal" role="form" id="returnable_channal_add" action="javascript:;" method="post" name="returnable_channal_add" enctype="multipart/form-data">
										<div class="row">
											<div class="col-md-12" style="margin-top:10px;">
												<?php 
													$checked = "";
													if(strpos($_SERVER['REQUEST_URI'], "returnable_channal_add")==true){ 
														$checked = "checked";
													}else{
														if($returnable_type =='returnable'){
															$checked = "checked";	
														}
													}
													$nonchecked = "";
													if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_add")==true){ 
														$nonchecked = "checked";
													}else{
														if($returnable_type =='non-returnable'){
															$nonchecked = "checked";
														}
													}
													$without_stock = "";
													if(strpos($_SERVER['REQUEST_URI'], "without_stock_non_returnable_channal_add")==true){ 
														$without_stock = "checked";
													}else{
														if($returnable_type =='without_stock'){
															$without_stock = "checked";
														}
													}
												?>
												<fieldset class="returnable_type">
												  <div class="col-md-2"></div>
												  <div class="col-md-2 radio">
												    <label><input type="radio" name="returnable_type" id="returnable_type" value="returnable" <?=$checked?>  onChange="get_return_date()"> Returnable</label>
												  </div>
												  <div class="col-md-2 radio">
												    <label><input type="radio" name="returnable_type" id="returnable_type1" value="non-returnable" <?=$nonchecked?> onChange="get_return_date()"> Non Returnable</label>

												  </div>
												  <div class="col-md-2 radio">
												    <label><input type="radio" name="returnable_type" id="returnable_type2" value="without_stock" <?=$without_stock?> onChange="get_return_date()"> Without Stock</label>

												  </div>
												  <div class="col-md-2"></div>
												</fieldset>
											</div>	
											<div class="col-md-12">  <hr> </div> 
											<div class="col-md-12" style="margin-top:10px;">
													<?php if($mode == "Edit"){ ?>
									 					<div class="col-md-4">
															  <div class="form-group">
															  		<label class="col-md-4 control-label">Challan No *</label>
															  		<div class="col-md-6 col-xs-11">
															  			<input type="text" class="form-control" id="challan_no_edit_id" placeholder="channal_id" value="<?php if($mode=='Edit'){ echo $rel['channal_id'];} ?>" readonly />
															  			<input type="hidden" class="form-control" id="channal_id" name="channal_id" placeholder="channal_id" value="<?php if($mode=='Edit'){ echo $rel['channal_id'];} ?>" />
																	</div>
															  </div>							 
														 </div>
											 		<?php } else { ?>
							 					<div class="col-md-4">
													<div class="form-group">
												  		<label class="col-md-4 control-label">Challan No *</label>
												  		<?php
													  		$channal_id = '';
													  		$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='RETURNABLE CHANNAL' and company_id = $companyID and `type_id` = '17' order by invoicetype_id ");
															while ($r = $query->fetch_assoc()) {
																$channal_id = $r['format_value'] . $r['taxinvoice_start'] . $r['end_format_value'];
																				}
																		?>
													  		<div class="col-md-6 col-xs-11">
													  			<input type="text" class="form-control" id="channal_id" name="channal_id" placeholder="channal_id" value="<?php echo $channal_id; ?>" readonly />
															</div>
														</div>							 
													</div>	
													<?php } ?>
												
												<div class="col-md-4">
						                            <div class="form-group">
						                                <label class="col-md-4 control-label">Challan Date * </label>
						                                <div class="col-md-6">
															<input type="text" class="form-control default-date-picker required valid" autocomplete="off" placeholder="Challan Date" title="Challan Date" name="challan_date" id="challan_date" value="<?=$challan_date?>">
														</div>
						                            </div>	
												</div>

												<div class="col-md-4">
			                                          <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, false,"load_returnable_channal_data();"); ?>
			                                    </div>

			                                    <div class="col-md-4">
						                            <div class="form-group">
						                                <label class="col-md-4 control-label">Challan Type * </label>
						                                <div class="col-md-6">
															<select class="select2" title="Select Type" id="chln_type" name="chln_type" onchange="sales_order();" >
																<option value="internal" <?php if($challan_type == 'internal'){ echo "selected";}?>>Internal</option>
																<option value="external" <?php if($challan_type == 'external'){ echo "selected";}?>>External</option>
															</select>
														</div>
						                            </div>	
												</div>

			                                    <div class="col-md-4">
						                            <div class="form-group">
						                                <label class="col-md-4 control-label">Customer*</label>
						                                <?php 
														    $where = "and l_group IN (".$companyConfiguration['inventory_party_show'].")";
														?>
														<div class="col-md-6">
															<select class="select2" title="Select Ledger" id="cust_id" name="cust_id" onchange="get_salesorder_no();" >
																<?= get_ledger($dbcon,$customer_id,$where); ?>
															</select>
														</div>
						                            </div>	
												</div>
												<div class="col-md-4">
			                                        <div class="form-group">
			                                            <label class="col-md-4 control-label">Issue Date*</label>
			                                            <div class="col-md-8">
			                                                <div data-date="<?=$issue_date?>" class="input-group date form_datetime-meridian">
			                                                    <input type="text" class="form-control" value="<?=$issue_date?>" name="issue_date" id="issue_date" autocomplete="off">
			                                                    <div class="input-group-btn">
			                                                        <button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
			                                                    </div>
			                                                </div>
			                                            </div>
			                                        </div>
			                                    </div>
											</div>
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
						                            <div class="form-group">
						                                <label class="col-md-4 control-label">Vehicle No * </label>
						                                <div class="col-md-6">
															<input type="text" class="form-control" autocomplete="off" placeholder="Vehicle No" title="Vehicle No" name="vehicle_no" id="vehicle_no" value="<?=$vehicle_no?>">
														</div>
						                            </div>	
												</div>
												 
												<div class="col-md-4">
						                            <div class="form-group">
						                                <label class="col-md-4 control-label">Mode Dispatch *</label>
						                              
														<div class="col-md-6">
															<select class="select2" title="Mode Dispatch" id="mode_dispatch" name="mode_dispatch" >
																<?=getmodeofdispache($dbcon,$mode_dispatch)?>
															</select>
														</div>
						                            </div>	
												</div>

												<div class="col-md-4" id="sales_order">
						                            <div class="form-group">
						                                <label class="col-md-4 control-label">Sales Order No *</label>
						                                <div class="col-md-8">
															<select class="select2" title="Select Sales Order" id="sales_order_id" name="sales_order_id" onchange="get_sales_order_data_load()" >
																<option value="">Choose Sales Order No</option>
															</select>
														</div>
						                            </div>	
												</div>
											</div>	
											<div class="col-md-12" id="returnable">
												<div class="col-md-4">
													<div class="form-group">
													<label class="col-md-4 control-label">Challan Return Type</label>
													<div class="col-md-6">
														<select class="select2" title="Return Type" id="return_type" name="return_type" onchange="return_challan_type_permission()">
															<option value="challan_wise" <?php if($challan_return_type == 'CHALLAN_WISE'){ echo "selected";}?>>Challan Wise</option>
															<option value="product_wise" <?php if($challan_return_type == 'PRODUCT_WISE'){ echo "selected";}?>>Product Wise</option>
														</select>
													</div>
												</div>
												</div>

												<div class="col-md-4 return_date_challan_wise">
													<div class="form-group">
													<label class="col-md-4 control-label">Return Date</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control  default-date-picker required valid" title="Return Date" placeholder="Return Date" autocomplete="off" name="return_date" id="return_date" value="<?=$return_date?>">
													</div>
												</div>
												</div>
											</div>
											<div class="col-md-12" style="margin-top:10px;"></div>	
											<div class="col-md-12">
												<table class="table table-bordered" style="margin-right: auto;margin-left: auto;width: 90%">
						                           <tr>
						                              <th width="20%">Item Name</th>
						                              <th width="20%">Item Desc</th>
						                              <th width="20%">Item Per</th>
						                              <th width="20%" class="withstock">Available Stock</th>
						                              <th width="10%">Qty</th>
						                              <th width="10%">Price</th>
						                              <td width="10%"></td>
						                           </tr>
						                           <tr>
						                              <td style="max-width:300px">
						                              	<input id="item_id" name="item_id" style="width:100%;" placeholder="Select Product" onchange="load_productdetail(this.value);get_hsn(this.value);"/>
						                                 <!-- <select class="select2" title="Select product" id="item_id" onChange="load_productdetail(this.value);get_hsn(this.value);">
															<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
														 </select> --><br>
															<strong class="hsncode" style="display:none;color:blue">HSN Code : <span id="hsncode"></span></strong>
						                              </td>
						                              <td>
						                              	 <textarea class="form-control" id="item_description" placeholder="Enter Item Description"></textarea> 	
						                              </td>
						                              <td style="vertical-align:top;">
														 <select class="select2"  title="Select Unit" id="unit_id">
																<?=getunit($dbcon,0);?>
														 </select>
													  </td>
						                              <td class="withstock">
						                                 <input type="number" class="form-control" id="item_stock" onkeypress="return isNumberKey(event)" readonly />
						                              </td>
						                              <td>
						                                 <input type="number" class="form-control" id="item_qty" onkeypress="return isNumberKey(event)"  />
						                              </td>
						                              <td>
						                                 <input type="number" class="form-control" id="item_price" onkeypress="return isNumberKey(event)"  />
						                              </td>
						                              <td>
						                              	<input type="button" class="btn btn-primary return_date_challan_wise product_add_direct" value="ADD"  style="" onclick="add_returnable_channal()" id="add_returnable_channal_btn" />

														<input type="button"  name="addrow1" id="addrow1" onClick="open_batch_wise_qty()"  class="btn btn-primary product_add_batch_wise" value="Add" />


						                              	<input type="button"  name="addrow" id="addrow" onClick="open_approv_quo1()"  class="btn btn-primary return_date_product_wise" value="Add" />
						                              </td>
						                              <input type='hidden' name='edit_id' id='edit_id' value='' />
						                           </tr>
						                        </table>
						                        <div id="table_returnable_channal_data"></div>
											</div>
											<div class="col-md-12" style="margin-top:10px;"></div>	
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div> 
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label class="col-md-8 control-label">For Jobwork </label>
														<div class="col-md-2 col-xs-11">
															<input type="checkbox" name="for_jobwork" id="for_jobwork"  <?php echo (isset($rel['for_jobwork']) && ($rel['for_jobwork'])==1) ? "checked" : '' ; ?>  >
														</div>
													</div> 
													<div class="form-group">
														<label class="col-md-8 control-label">For Sample </label>
														<div class="col-md-2 col-xs-11">
															<input type="checkbox" name="for_sample" id="for_sample"  <?php echo (isset($rel['for_sample']) && ($rel['for_sample'])==1) ? "checked" : '' ; ?> >
														</div>
													</div> 
												</div>	
												<div class="col-md-2">
													<div class="form-group">
														<label class="col-md-8 control-label">On Loan </label>
														<div class="col-md-2 col-xs-11">
															<input type="checkbox" name="on_loan" id="on_loan"  <?php echo (isset($rel['on_loan']) && ($rel['on_loan'])==1) ? "checked" : '' ; ?> >
														</div>
													</div> 
													<div class="form-group">
														<label class="col-md-8 control-label">For Replacement </label>
														<div class="col-md-2 col-xs-11">
															<input type="checkbox" name="for_replacement" id="for_replacement"  <?php echo (isset($rel['for_replacement']) && ($rel['for_replacement'])==1) ? "checked" : '' ; ?> >
														</div>
													</div> 
												</div>
												<div class="col-md-2">
													<div class="form-group">
														<label class="col-md-8 control-label">For Repairing </label>
														<div class="col-md-2 col-xs-11">
															<input type="checkbox" name="for_repairing" id="for_repairing"  <?php echo (isset($rel['for_repairing']) && ($rel['for_repairing'])==1) ? "checked" : '' ; ?> >
														</div>
													</div> 
													<div class="form-group">
														<label class="col-md-8 control-label">Rejected </label>
														<div class="col-md-2 col-xs-11">
															<input type="checkbox" name="rejected" id="rejected"  <?php echo (isset($rel['rejected']) && ($rel['rejected'])==1) ? "checked" : '' ; ?> >
														</div>
													</div> 
												</div>	
												<div class="col-md-2">
													<div class="form-group">
														<label class="col-md-8 control-label">Loan Returns </label>
														<div class="col-md-2 col-xs-11">
															<input type="checkbox" name="loan_returns" id="loan_returns"  <?php echo (isset($rel['loan_returns']) && ($rel['loan_returns'])==1) ? "checked" : '' ; ?> >
														</div>
													</div> 
													<div class="form-group">
														<label class="col-md-8 control-label">Non Returnable Matl. </label>
														<div class="col-md-2 col-xs-11">
															<input type="checkbox" name="non_returnable_matl" id="non_returnable_matl"  <?php echo (isset($rel['non_returnable_matl']) && ($rel['non_returnable_matl'])==1) ? "checked" : '' ; ?> >
														</div>
													</div> 
												</div>
											</div>
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='is_power_drive' id='is_power_drive' value='<?=$is_power_drive?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['id']?>' />
											<input type="hidden" id="isbatchwise" name="isbatchwise" value="">
											<input type="hidden" name="sales_id" id="sales_id" value="<?=$rel['sales_order_id']?>">
											<input type='hidden' name='requesturi' id='requesturi' value='<?=curPageURL()?>' />
											<div class="clearfix"></div>	
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<?php if(strpos($_SERVER['REQUEST_URI'], "non_returnable_channal_add")==true){ ?>
												<a href="<?=ROOT.INVENTORY_ROOT.'non_returnable_channal_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<?php }else{ ?>
												<a href="<?=ROOT.INVENTORY_ROOT.'returnable_channal_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<?php } ?>	
											<div class="col-md-4"></div>
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
			<?php include_once($include1.'add_cust.php');?>
			<?php include_once($include1.'add_person.php');?>
			<?php include_once($include1.'add_return_date.php');?>
			<?php include_once($include1.'preview_cust_person_dtl.php');?>
			<?php include_once($include1.'preview_cust_dtls.php');?>
			<?php include_once($include1.'add_batch_wise_qty.php');?>
		</section>
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/returnable_channal.js?<?=time()?>"></script>
		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/customer.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});

			/*$("#item_id").select2({
				width: '100%',
				minimumInputLength: 3
			});*/
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
			var max_followup_date = '<?=$max_followup_date?>';
         	var date = new Date();
         	var today = new Date(date.getFullYear(), date.getMonth(), date.getDate()); //start date is today
         	var endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(max_followup_date)); //end date should not greater than 15 days
		

		<?php if ($is_power_drive) { ?>
			today = "";
		<?php }	?>

         $(".form_datetime-meridian").datetimepicker({
           format: "dd-mm-yyyy HH:ii P",
           showMeridian: true,
           autoclose: true,
           todayBtn: true,
           pickerPosition: "bottom-left",
           startDate: today,
           endDate: endDate
       }); 
         <?php if ($mode =="Edit"){?>
         	get_salesorder_no();
     	<?php }?>
		</script> 
	</body>
</html>