<?php 
   session_start();
   
   include('../include/urlfile.php'); 
   $form="Job Work Rate Card";
   $infopage = pathinfo( __FILE__ );
   $_SESSION['page']=$infopage['filename'];
   $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	JOBWORK_RATE_CARD_ADD,JOBWORK_RATE_CARD_UPDATE
	]);
   if(strpos($_SERVER['REQUEST_URI'], "jobworkratecardedit")==true){
   	if(!in_array(JOBWORK_RATE_CARD_UPDATE,$bulkAccessArray)){
        	header("Location: ".DOMAIN."permission_access");
    	}
   	$pocardid =$dbcon->real_escape_string($_REQUEST['jobcardid']);
		$po_card = "select * from tbl_jobwork_rate_card where jobwork_card_id=".$pocardid;

		$rel=brp_mysqli_fetch_array($dbcon->query($po_card));
		$jobwork_card_date = date('d-m-Y',strtotime($rel['jobwork_card_date']));
		
		$mode="edit";
		$disable="disabled";
   }else{
   	if(!in_array(JOBWORK_RATE_CARD_ADD,$bulkAccessArray)){
        	header("Location: ".DOMAIN."permission_access");
    	}
   	$disable="";
    	$quotation_date='d-m-Y';
	
    	$mode="Add";
    	$jobwork_card_date=date('d-m-Y');
   }
	$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
	$set_conf=mysqli_fetch_assoc($dbcon->query($setconf));
	$type_conf = $set_conf['production_pro_type'];
  
   ?>
<!DOCTYPE html>
<html lang="en">
   <head>
   	<title>JOBWORK RATE CARD</title>
      <?php include_once($include.'include_css_file.php');?>
   </head>
   <body>
      <section id="container" class="sidebar-closed">
         <?php include_once($include.'include_top_menu.php');?>
         <?php include_once($include.'left_menu.php');?>
         <section id="main-content">
            <section class="wrapper">
               <!-- < //include_once('../include/equick_link.php');?> -->
               <div class="row">
                  <div class="col-lg-12">
                     <section class="panel">
                        <header class="panel-heading">
                           <h3><?=$mode.' '.$form?></h3>
                        </header>
                        <div class="">
                           <ul class="breadcrumb">
                              <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                              <li><a href="<?=ROOT.PRODUCTION_ROOT.'jobwork_rate_card_list'?>"><?=$form?> Wise</a></li>
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
						   <div class="row">
								
							</div>
						   </div>
                           <form class="form-horizontal" role="form" id="jobwork_rate_card_add" action="javascript:;" method="post" name="jobwork_rate_card_add">
                            
                              <div class="row">
								<div class="col-md-12" style="font-size:16px">
									<?php if($mode=='edit'){?>
										<input type="hidden" name="card_type" value="<?=$rel['card_type']?>">
									<?php }?>
									<div class="col-md-6" style="text-align:right">
										<div class="form-group">
                                          <div class="radio">
												<label><input type="radio" name="card_type" id="ven_wise" value="0" onchange="ven_prod(this.value);item_detail_data();" checked <?=$disable?>><strong>Vendor Wise</strong></label>
										  </div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
                                          <div class="radio">
												<label><input type="radio" name="card_type" id="prod_wise" value="1" onchange="ven_prod(this.value);item_detail_data();" <?php if($rel['card_type']==1){?>checked<?php }?> <?=$disable?>><strong>Product Wise</strong></label>
										  </div>
										</div>
									</div>
								</div>
                                 <div class="col-md-12">
                                    <div class="col-md-6" id="choose_ven">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label" style="text-align:left">Selected Vendor : </label>
                                          <div class="col-md-6 col-xs-11">
                                             <select class="select2 vendor_class vendor_specified_class" id="vender_id" name="vender_id" title="Select Vender" <?=$disable?>>
                                             <?=getcust($dbcon,$rel['party_id'],"");?> 
                                             </select>
											 <?php if($mode == 'edit'){?>
												<input type="hidden" name="vender_id" value="<?=$rel['party_id']?>">
											 <?php }?>
                                          </div>
                                       </div>
                                    </div>
									
									<div class="col-md-6" id="choose_prod">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label" style="text-align:left">Selected Product : </label>
                                          <div class="col-md-6 col-xs-11">
                                             <select class="select2 product_class vendor_specified_class" id="product_id" name="product_id" title="Select Product" <?=$disable?> onchange="load_product_unit()">
											 <option value="">Choose Product</option>
                                             <?=getproduct_typewise($dbcon,$rel['product_id'],$type_conf);?>
                                             </select>
											 <?php if($mode == 'edit'){?>
												<input type="hidden" name="product_id" value="<?=$rel['product_id']?>">
											 <?php }?>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label" style="text-align:left">Jobwork Card No : </label>
											<div class="col-md-6 col-xs-11">
												<input type="text" name="jobwork_card_no" id="jobwork_card_no" class="form-control" value="<?=$rel['jobwork_card_no']?>" readonly>
											</div>
										</div>
									</div>
                                 </div>
								 <div class="col-md-12">
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label" style="text-align:left">Purchase Card Date : </label>
											<div class="col-md-6 col-xs-11">
												<input type="text" name="jobwork_card_date" id="	" class="form-control default-date-picker" autocomplete="off" value="<?=$jobwork_card_date?>">
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label class="col-md-4 control-label" style="text-align:left">Quotation Ref No. : </label>
											<div class="col-md-6 col-xs-11">
												<input type="text" name="quot_ref" id="quot_ref" class="form-control" placeholder="Quotation Ref." autocomplete="off" value="<?=$rel['quot_ref']?>">
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-12">
									
									
									<!-- <div class="col-md-6" style="display:none">
										<div class="form-group">
											<label class="col-md-4 control-label" style="text-align:left">Purchase Card Effective Date : </label>
											<div class="col-md-6 col-xs-11">
												<input type="text" name="effective_date" id="effective_date" class="form-control default-date-picker" autocomplete="off" value="<=$affected_date?>">
											</div>
										</div>
									</div> -->
								</div>
                              </div>
                              <hr>
                              <!-- Tab Section Start By Umair -->
                              <section class="panel" style="margin-top: 15px">
                                 <header class="panel-heading tab-bg-dark-navy-blue ">
                                    <ul class="nav nav-tabs">
                                       <li class="active">
                                          <a data-toggle="tab" href="#po_listing_info"  aria-expanded="true">Item Details</a>
                                       </li>
                                       
                                       <!-- <li class="">
                                          <a data-toggle="tab" href="#po_vendor_details" onClick="get_vendor_details('po_vendor_details')" aria-expanded="false">Party Details</a>
                                       </li>
                                       <li class="">
                                          <a data-toggle="tab" href="#po_billing_terms" onClick="get_vendor_details('po_billing_terms')" aria-expanded="false">Billing Terms</a>
                                       </li>
                                       <li class="">
                                          <a data-toggle="tab" href="#po_terms_cond" aria-expanded="false">Terms & Condition</a>
                                       </li> -->
                                    </ul>
                                 </header>
                                 <div class="panel-body">
                                    <div class="tab-content">
                                       <div id="po_listing_info" class="tab-pane active" >
                                          <div class="panel-body">
                                            <div class="row" id="existing_item_div" style="margin-top: 30px">
												<section class="panel" >
													<div class="panel-body bio-graph-info">
														<div class="col-md-12" style="height:20px">
														</div>
														<div class="col-md-12" >
															<table class="display table table-bordered table-striped">
																<thead>
																	<tr>
																		<th colspan="7" style="height:20px;text-align:center">Purchase Rate Details</th>
																	</tr>
																	<tr>
																		<th class="pro_vend">Product</th>
																		<th class="vend_pro">Vender</th>
																		<th style="display:none">Currency</th>
																		<th>Rate Tolerance (%) *</th>
																		<th>Disc(%) *</th>
																		<th>Qtn No</th>
																		<th>Quotation Date</th>
																		<th>Action</th>
																	</tr>
																</thead>
																<tbody>
																	<tr>
																		<td class="pro_vend" style="max-width: 250px;"><select class="select2" title="Select product" name="vend_product_id" id="vend_product_id" onchange="load_product_unit()" >
																			<option value="">Choose Product</option>
																			<?=getproduct_typewise($dbcon,'',$type_conf);?>
																		</select></td>
																		<td class="vend_pro" style="max-width: 250px;">
																			<select class="select2" title="Select Vendor" name="prod_id_vend" id="prod_id_vend">
																				<?=getcust($dbcon,$vender_id,"");?>
																			</select>
																		</td>
																		<td style="display:none">
																		<select class="select2" name="currency_id" id="currency_id" title="Select Currency">
																			<?=getcurrency($dbcon,'1');?>
																		</select>
                                                                     
																		<td><input id="rate_tolerance" name="rate_tolerance" type="number" class="form-control" title="Rate Tolerance" placeholder="Rate Tolerance" ></td>
																		<td><input id="discount_percentage" name="discount_percentage" type="number" class="form-control" title="Discount Percentage" maxlength="2" placeholder="Discount Percentage" ></td>
																		<td><input id="quotation_no" name="quotation_no" type="number" class="form-control" title="Date" value="" placeholder="Quotation No"></td>
																		
																		<td><input id="quotation_date" name="quotation_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Date" ></td>
																		<td><input type="button" class="btn btn-success" id="save" name="save" onclick="return add_field();" value="Add"></td>
																	</tr>
																</tbody>
																<thead>
																	<tr>
																		<th>Rate *</th>
																		<th style="display:none">G Rate *</th>
																		<th style="display:none">Lead Time</th>
																		<th>Effective Date</th>
																		<th>Valid Date</th>
																		<th>Process</th>
																		<th>Unit</th>
																		<th></th>
																	</tr>
																</thead>
																<tbody>
																	<tr>
																		<td> <input id="price" name="price" type="number" class="form-control" title="Rate" placeholder="Rate" ></td>
																		<td style="display:none"><input id="grate" name="grate" type="number" class="form-control" title="Grate" maxlength="100" value="" placeholder="GRate" ></td>
																		<td style="display:none"><input id="lead_time" name="lead_time" type="number" class="form-control" title="Lead Time" maxlength="10" value="" placeholder="Lead Time" ></td>
																		<td><input id="affected_date" name="affected_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Effective Date"></td>
																		<td><input id="valid_date" name="valid_date" type="text" class="form-control default-date-picker" title="Date" placeholder="Valid Date"></td>
																		<td>
																			<select class="form-control" name="process_id" id="process_id">	
																				<?=get_all_process($dbcon,$id) ?>
																			</select>
																		 </td>
																		<td>
																			<select class="form-control" name="unit_id" id="unit_id">	
																				<?=getunit($dbcon,$id);?>
																			</select>
																		</td>
																		<td></td>
																		<input type='hidden' name='edit_id' id='edit_id' value='' />
																	</tr>
																</tbody>
															</table>
															
														</div>
														<div class="col-md-12" style="height:20px">
														</div>
														
														<div class="col-md-12">
														<div class="panel-body">
															<div class="adv-table">
															<table class="display table table-bordered table-striped" id="item_data_table">
																<thead>
																	<tr>
																		<!-- <th class="pro_vend">Product</th> -->
																		<th id="first"></th>
																		<!-- <th style="display:none">Currency</th> -->
																		<th>Process Name</th>
																		<th>Rate Tolerance (%) *</th>
																		<th>Disc(%) *</th>
																		<th>Effactive Date</th>
																		<th>Valid Date</th>
																		<th>Quotation Date</th>
																		<!-- <th style="display:none">Created By</th> -->
																		<th>Rate *</th>
																		<th>Unit</th>
																		<!-- <th style="display:none">G Rate *</th>
																		<th style="display:none">Lead Time</th>-->
																		<th>Qtn No</th>
																		<!-- <th style="display:none">Item Make</th>  -->
																		<th>Action</th>
																	</tr>
																</thead>
																<tbody>
																</tbody>
															</table>
															</div>
														</div>
														</div>
													</div>
                                                  </section>      
                                             </div>
                                          </div>
                                       </div>
                                      		<div class="row">
                                          	<div class="col-md-7">
                                             <div class="form-group">
                                                <label class="col-md-4 control-label">Remark</label>
                                                <div class="col-md-8 col-xs-11">
                                                   <textarea class="form-control" placeholder="Remark" name="remark" id="remark" rows="4" ><?=$rel['remark']?></textarea>
                                                </div>
                                             </div>
                                          </div>
                                       
                                          <div class="row">
                                          	<div class="col-md-7">
                                             <div class="form-group">
                                                <label class="col-md-4 control-label">Terms Condition</label>
                                                <div class="col-md-8 col-xs-11">
                                                   <textarea class="form-control" placeholder="Terms Condition" name="terms_condition" id="terms_condition" ><?=$rel['terms_condition']?></textarea>
                                                </div>
                                             </div>
                                          </div>
                                    </div>
									<div class="col-md-12" style="text-align:center;vertical-align:center">
										<input type="submit" class="btn btn-shadow btn-success" name="submit" value="Submit">&nbsp;
										<a href="<?=ROOT.PRODUCTION_ROOT.'jobwork_rate_card_list'?>" type="button" class="btn btn-danger">Cancel</a>
									</div>
                                 </div>
								</section>
                              <!-- Tab Section -->
                              <!--Vendor row end-->
							  <input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
                              <input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
                              <input type='hidden' name='eid' id='eid' value="<?=$rel['jobwork_card_id']?>" />  
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
         <?php include_once($include.'footer.php');?>
         <!--footer end-->
      </section>
      <!-- js placed at the end of the document so the pages load faster -->
      <?php include_once($include.'include_js_file.php');?>   
      <script src="<?=ROOT.PRODUCTION_ROOT?>js/app/jobwork_rate_card.js?<?=time()?>"></script>
      <script>
          
          $(".select2").select2({
            width: '100%',
            minimumInputLength: 2,
          });
          CKEDITOR.replace( 'terms_condition', {
            enterMode: CKEDITOR.ENTER_BR
          });
          
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
      </script>
      <?php 
		if($mode=="Add"){
			echo "<script>get_series_no(40);</script>";
			echo "<script>ven_prod(0);</script>";
		}else{
			echo "<script>ven_prod(".$rel['card_type'].");</script>";
		}
		echo "<script>item_detail_data();</script>";
	  ?>
   </body>
</html>
