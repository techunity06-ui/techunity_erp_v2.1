<?php $companyConfiguration=getCompanyConfiguration($dbcon);
$getpagePermission=getpagePermission($dbcon);
$enable_assing_user=$companyConfiguration['enable_assing_user'];
if($enable_assing_user==1){
	$cols = "col-md-4";
} else{
	$cols = "col-md-6";
}
// print_r($companyConfiguration);
?>
<div class="modal colored-header info " id="bs-example-modal-lg" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add Party</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="cust_add" action="javascript:;" method="post" name="cust_add">
					<!--<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Party Type*</label>
							<div class="col-md-12">
								<div class="col-md-3" id="party_type_both_div">
									<label>
										<input type="radio" class="form-control" id="party_type_both" name="party_type" value="0" checked> Both
									</label>
								</div>
								<div class="col-md-3" id="party_type_cust_div">
									<label>
										<input type="radio" class="form-control" id="party_type_cust" name="party_type" value="1"> Customer
									</label>
								</div>
								<div class="col-md-3" id="party_type_ven_div">
									<label>
										<input type="radio" class="form-control" id="party_type_ven" name="party_type" value="2"> Vendor
									</label>
								</div>
							</div>
						</div>	
					</div>-->
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Company Name*</label>
							<div class="col-md-12">
								<input type="text" class="form-control" id="cust_name" name="cust_name" placeholder="Company Name"  value="" <?=($getpagePermission['crm_partymst_cust_name'] == '0') ? '' : 'required';?>/>
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Mobile*</label>
							<div class="col-md-12">
								<input type="text" class="form-control" name="cust_mobile" placeholder="Mobile" id="cust_mobile" title="Enter Mobile" value="" <?=($getpagePermission['crm_partymst_cust_mobile'] == '0') ? '' : 'required';?> />
							</div>
						</div>	
					</div>
					<div class="clearfix"></div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">E-mail</label>
							<div class="col-md-12">
								<input type="text" class="form-control" name="cust_email" id="cust_email" placeholder="E-mail" value="" <?=($getpagePermission['crm_partymst_cust_email'] == '0') ? '' : 'required';?>/>
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Gst No</label>
							<div class="col-md-12">
								<input type="text" class="form-control" name="cust_gst" id="cust_gst" value="" <?=($getpagePermission['crm_partymst_cust_gst'] == '0') ? '' : 'required';?>/>
							</div>
						</div>	
					</div>
					<div class="clearfix"></div>
					<div class="<?=$cols;?>">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Party Category</label>
							<div class="col-md-12">
								<select class="select2" name="cust_cat" id="cust_cat" <?=($getpagePermission['crm_partymst_cust_cat'] == '0') ? '' : 'required';?>>
									<option value="">--Select Party Category--</option>
									<?=get_customer_category($dbcon,"");?>
								</select>
							</div>
						</div>	
					</div>
					<div class="<?=$cols;?>">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Customer Type</label>
							<div class="col-md-12">
								<select class="select2" name="cust_type" id="cust_type" <?=($getpagePermission['crm_partymst_cust_type'] == '0') ? '' : 'required';?>>
									<option value="">--Select Party Type--</option>
									<?=get_customer_master_type($dbcon,$rel['cust_type']);?>
								</select>
							</div>
						</div>	
					</div>
					<?php if($enable_assing_user==1){ ?>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Assign To</label>
								<div class="col-md-12">
									<select class="select2" id="cust_owner" name="cust_owner" data-placeholder="Assign User" onchange="no_of_inquiry(this)">
										<?=get_assign_users($dbcon, '', " and user_type in(".$companyConfiguration['crm_user_type'].")");?>
									</select>
									<div id="no_of_inquiry" style="font-size: 12px; color: #337ab7;"></div>
								</div>
							</div>
						</div>
					<?php } ?>
					<div class="clearfix"></div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Party Industry</label>
							<div class="col-md-12">
								<select class="select2" name="cust_ind" id="cust_ind" <?=($getpagePermission['crm_partymst_cust_ind'] == '0') ? '' : 'required';?>>
									<option value="">--Select Party Industry--</option>
									<?=get_customer_industries($dbcon,$rel['cust_ind']);?>
								</select>
							</div>
						</div>	
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Source / Refer By</label>
							<div class="col-md-12">
								<select class="select2" name="cust_source" id="cust_source" <?=($getpagePermission['crm_partymst_cust_source'] == '0') ? '' : 'required';?>>
									<?=get_refer_by($dbcon,$rel['cust_source']);?>
								</select>
							</div>
						</div>	
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Territory</label> 
							<div class="col-md-12">
								<select class="select2" id="t_id" name="t_id" <?php echo ($mode=="view")?"disabled":""?> <?=($getpagePermission['crm_partymst_t_id'] == '0') ? '' : 'required';?>>
									<?=get_all_territory($dbcon,$rel['t_id']);?>
								</select>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					
					<div class="clearfix"></div>
					<div class="col-md-12">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Address</label>
							<div class="col-md-12">
								<textarea class="form-control" name="c_add_address" id="c_add_address" placeholder="Address" title="Enter Address" <?=($getpagePermission['crm_partymst_c_add_address'] == '0') ? '' : 'required';?>></textarea>
							</div>
						</div>	
					</div>
					<div class="clearfix"></div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Country</label>
							<div class="col-md-12">
								<select class="select2" name="c_add_country" id="c_add_country" onChange="load_state(this.value,'c_add_state',<?=$stateid?>)" <?=($getpagePermission['crm_partymst_c_add_country'] == '0') ? '' : 'required';?>>
									<?=get_country($dbcon,$countryid)?>				
								</select>
							</div>
						</div>	
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">State</label>
							<div class="col-md-12">
								<select class="select2" name="c_add_state" id="c_add_state" onChange="load_city(this.value,'c_add_city',<?=$cityid?>)" <?=($getpagePermission['crm_partymst_c_add_state'] == '0') ? '' : 'required';?>>
									<option value="">Select State</option>	
									<?php //=getstate($dbcon,$rel['stateid'])?>				
								</select>
							</div>
						</div>	
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">City</label>
							<div class="col-md-12">
								<select class="select2" name="c_add_city" id="c_add_city" <?=($getpagePermission['crm_partymst_c_add_city'] == '0') ? '' : 'required';?>>
									<option value="">Select City</option>	
								</select>
							</div>
						</div>	
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Pincode *</label>
							<div class="col-md-12">
								<input type="text" class="form-control" name="c_pincode" id="c_pincode" placeholder="Pincode" value="" />
							</div>
						</div>	
					</div>
					
					<div class="clearfix"></div>
				</div>
				<div class="col-md-3"></div>
				<button type="submit"  class="btn btn-success">Submit</button> &nbsp;
				<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
			<!--Vendor row end-->	
			<input type='hidden' name='cust_mode' id='cust_mode' value='Add' />
			<input type='hidden' name='cust_model' id='cust_model' value='model' />	
			<input type='hidden' id="bran" name='branch_id' value='' />	
			<input type="hidden" id="cust_code" name="cust_code" value="<?=get_customer_code($dbcon); ?>" />
			<input type="hidden" id="cust_code_series" name="cust_code_series" value="<?=get_customer_code_series($dbcon);?>" />
		</form>
	</div>
</div>	
</div>
</div><!-- /.modal-content -->
<script>
	$(document).ready(function() {
		var country = $("#c_add_country").val();
		var state = <?=$stateid?>;
		load_state(country,'c_add_state',<?=$stateid?>);
		load_city(state,'c_add_city',<?=$cityid?>);
	});
</script>