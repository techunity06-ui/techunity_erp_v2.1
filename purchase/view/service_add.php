<?php 
	session_start();
	
	include('../include/urlfile.php');
	
	$form="SERVICE";
	$countryid='101';
	$stateid='1';
	$cityid='1';

	$branch_id = $_SESSION['branch_id'];
	
	 if(strpos($_SERVER[REQUEST_URI], "service_add_po")==true){
		$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
		
		$query1="select mst.*,led.l_name from tbl_purchaseorder as mst
		left join tbl_ledger as led on led.l_id=mst.vender_id
		where mst.purchaseorder_id=".$purchaseorder_id;

		$rel2=mysqli_fetch_assoc($dbcon->query($query1));
	
		$mode="Add";
		$pmode="padd";
		$grn_date=date('d-m-Y');
		$ref_no=$rel2['purchaseorder_no'];
		$back="service_notes_pro_list";
		$vender_name=$rel2['l_name'];
		$vender_id=$rel2['vender_id'];
	}
	else{
		$mode="Add";
		$grn_date=date('d-m-Y');
		$back="service_notes_pro_list";
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$set_conf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
	$set_conr=mysqli_fetch_assoc($dbcon->query($set_conf));
	
	$getspecialConfiguration=getspecialConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once($include.'/include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'/include_top_menu.php');?>
			<?php include_once($include.'/left_menu.php');?>
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
										<li><a href="<?=ROOT.PURCHASE_ROOT.'service_notes_pro_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="service_add" action="javascript:;" method="post" name="service_add" enctype="multipart/form-data">
										<div class="row"> 
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Service No.*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="service_no" name="service_no" class="form-control" title="Service No." value="<?=$rel['service_no']?>" placeholder="Service No" readonly>
														</div>
													</div>
												</div>	
												<div class="col-md-4">  	
													<div class="form-group">  	
														<label class="col-md-4 control-label">Service Date*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="service_date" name="service_date" class="form-control default-date-picker" title="Date" value="<?=$service_date?>" placeholder="Purchase Date">
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Invoice No *</label>
														<div class="col-md-8 col-xs-11">
															<input type="text" id="invoice_no" name="invoice_no" class="form-control" title="Invoice No." value="<?=$rel['invoice_no']?>" placeholder="Invoice No">
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-4">
			                                          <?php echo getBranchBox($dbcon, $branch_id, $rel2['branch_id'], true, false,"load_purhcase_order_data();"); ?>
			                                    </div>
											</div>
											<div class="col-md-12">  <hr> </div>
											<div class="col-md-12" style="margin-top:10px;">
												
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="white-space:nowrap;">Choose Order No *</label>
													<div class="col-md-8">
														<?php if($mode=='Add'){?>
															<?php if($pmode=="padd"){ ?>
																<input type="text" class="form-control" value="<?=$ref_no?>" readonly>
																
																<input type="hidden" name="purchaseorder_id" id="purchaseorder_id" value="<?=$purchaseorder_id?>" />
															<?php}else{ ?>
															
															<select class="select2" name="purchaseorder_id" id="purchaseorder_id" onChange="load_purhcase_order_data(this.value)">
																<option value="">Choose Order No</option>
															</select>
															
															
															<?php} ?>
														<?php }else{?>
															<input type="text" class="form-control" value="<?=$pono?>" readonly>
															<input type="hidden" name="purchaseorder_id" id="purchaseorder_id" value="<?=$rel['purchaseorder_id']?>" />
														<?php }?>
													</div>
												</div>
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">Vendor*</label>
													<div class="col-md-8" style="padding-left: 9px;">
													
														<input type="text" class="form-control"  name="vender_name" id="vender_name" placeholder="Vender Name" value="<?=$vender_name?>" readonly />
														
														<input type="hidden" class="form-control"  name="vender_id" id="vender_id" placeholder="vender Id" value="<?=$vender_id?>" />
														
														<input type="hidden" class="form-control"  name="request_no" id="request_no" placeholder="request_no" />
														
													</div>  
												</div>
											</div>	
											<div class="col-md-12" style="margin-top:10px;"></div>	
											<div class="col-md-12">
												<div class="form-group">
													<div class="col-md-12 col-xs-11">
														<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
															<tr id="field">
																<th width="15%" class="text-center">Product Type</th>
																<th width="30%" class="text-center">Product Name</th>
																<th width="15%" class="text-center">Product Category</th>
																<th width="15%" class="text-center">Total Qty</th>
																<th width="15%" class="text-center">Pending Qty</th>
																<th width="10%" class="text-center">Quantity</th>
																<!--<th width="10%" class="text-center">Unit</th>-->
																
																<th width="15%" class="text-center">Action</th>
															</tr>
															<tbody id="field1" style="text-align:center">
															</tbody>
														</table>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div> 
												</div>
												<?php if($mode=="Add" && $set_conr['upload_reciept'] == "Yes"){ 
													$ttrt="required";
												}else{
													$ttrt="";
												}
												?>
												<div class="col-md-6">
													<div class="form-group">
														<label class="col-md-3 control-label">Upload Receipt *</label>
														<div class="col-md-7">
															<input type="file" class="form-control" id="grn_file" name="grn_file[]" multiple="multiple" <?=$ttrt?> />
														</div>
														<div class="col-md-2">
														<?phpif($mode=='Edit'){
															 $get_attch_qry="select * from tbl_grn_attch where grn_attch_status=0 and grn_id=".$rel['grn_id'];
															$attch_rs=$dbcon->query($get_attch_qry);
															while($attch_rel=mysqli_fetch_assoc($attch_rs)){
														?>
															<a href="<?=ROOT.RECEIPT_FILE_VWING.$attch_rel['grn_file']?>" class="btn btn-xs btn-primary" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-eye"></i>  </a> 
															<button type="button" onClick="delete_attch(<?=$attch_rel['grn_attch_id']?>)" class="btn btn-xs btn-danger" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-trash-o"></i></button>
															<br/>
														<?php } }?>
														</div>
													</div> 
												</div>
												<div class="clearfix"></div>	
											</div>
										<?phpif($getspecialConfiguration['hermattic_permission']=="1") { ?>
											<div class="col-md-12">
												<div class="clearfix"></div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Material Inspected</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="material_inspected" id="material_inspected" title="Select Option" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['material_inspected']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['material_inspected']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['material_inspected']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Test certificate</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="test_certificate" id="test_certificate" title="Select Option" onChange="get_order_no();" required>
															<option value="">--Select Option--</option>
															<option value="Received" <?=($rel['test_certificate']=='Received')?'selected':''?>> Received </option>
															<option value="Not Received" <?=($rel['test_certificate']=='Not Received')?'selected':''?>> Not Received </option>
															<option value="Not Applicable" <?=($rel['test_certificate']=='Not Applicable')?'selected':''?>> Not Applicable </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Test certificate - Reviewed as per Code</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="test_certificate_code" id="test_certificate_code" title="Select Option" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['test_certificate_code']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['test_certificate_code']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['test_certificate_code']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>
												<div class="clearfix"></div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Dimensional Insception Done</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="dimension_inspected" id="dimension_inspected" title="Select Option" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['dimension_inspected']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['dimension_inspected']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['dimension_inspected']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Inspection Report attached</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="inspection_report" id="inspection_report" title="Select Option" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['inspection_report']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['inspection_report']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['inspection_report']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Qty Verified & Ok</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="qty_verified" id="qty_verified" title="Select Option" onChange="get_order_no();" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['qty_verified']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['qty_verified']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['qty_verified']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>	
												<div class="clearfix"></div>
												<div class="col-md-4">
													<label class="col-md-6 control-label" style="">Checked & Release for process</label>
													<div class="col-md-6" style="padding-left: 9px;">
														<select class="select2" name="process_checked" id="process_checked" title="Select Option" onChange="get_order_no();" required>
															<option value="">--Select Option--</option>
															<option value="Yes" <?=($rel['process_checked']=='Yes')?'selected':''?>> Yes </option>
															<option value="No" <?=($rel['process_checked']=='No')?'selected':''?>> No </option>
															<option value="N.A." <?=($rel['process_checked']=='N.A.')?'selected':''?>> N.A. </option>
														</select>
													</div>  
												</div>

											</div>
										<?php} ?>
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['grn_id']?>' />
											<!-- <input type='hidden' name='j_alloc_process_id[]' id='j_alloc_process_id' value='<?=$rel2['j_alloc_process_id']?>' /> -->
											<input type='hidden' name='back' id='back' value='<?=$back?>' />
											<input type='hidden' name='pmode' id='pmode' value='<?=$pmode?>' />
											<div class="clearfix"></div>	
											<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
											<a href="<?=ROOT.PURCHASE_ROOT.'grn_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'/footer.php');?>
		</section>
		  
		<?php include_once($include.'/include_js_file.php');?>   
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/service_notes.js?<?=time()?>"></script>
		<script>
			//$('#container').addClass('sidebar-closed');
			$(".select2").select2({
				width: '100%'
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
			<?php if($mode=='Edit'){?>
			$('#vender_id').select2('readonly',true);
			$('#branch_id').select2('readonly',true);
			$('#purchaseorder_id').select2('readonly',true);
			
			load_purhcase_order_data(<?=$rel['purchaseorder_id']?>);
			<?php }?>
			<?php if($mode=='Add'){?>
			load_service_no();
			load_purhcase_order_data(<?=$rel['purchaseorder_id']?>);
			<?php }?>
			
		</script> 
	</body>
</html>