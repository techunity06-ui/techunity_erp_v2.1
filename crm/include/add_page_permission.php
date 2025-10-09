<?php $getpagePermission=getpagePermission($dbcon); ?>
<style type="text/css">
		.btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
			z-index:2;
			background-color: #bbdce6;
		}
		.control-label{
			font-weight: bold;
		}
	</style>
<div class="modal colored-header info " id="pagepermissionmodal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Party Page Permission</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="permission_add" action="javascript:;" method="post" name="permission_add">
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Customer Name - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_name'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_name" id="crm_partymst_cust_name1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_name'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_name'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_name" id="crm_partymst_cust_name2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_name'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Customer mobile No - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_mobile'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_mobile" id="crm_partymst_cust_mobile1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_mobile'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_mobile'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_mobile" id="crm_partymst_cust_mobile2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_mobile'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Customer Email id - Required?</strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_email'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_email" id="crm_partymst_cust_email1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_email'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_email'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_email" id="crm_partymst_cust_email2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_email'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Customer GST No - Required?</strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_gst'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_gst" id="crm_partymst_cust_gst1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_gst'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_gst'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_gst" id="crm_partymst_cust_gst2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_gst'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Party category - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_cat'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_cat" id="crm_partymst_cust_cat1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_cat'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_cat'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_cat" id="crm_partymst_cust_cat2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_cat'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Customer IEC No - Required?</strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_iec'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_iec" id="crm_partymst_cust_iec1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_iec'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_iec'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_iec" id="crm_partymst_cust_iec2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_iec'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Customer Type - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_type'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_type" id="crm_partymst_cust_type1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_type'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_type'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_type" id="crm_partymst_cust_type2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_type'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Customer PAN No - Required?</strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_pan'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_pan" id="crm_partymst_cust_pan1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_pan'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_pan'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_pan" id="crm_partymst_cust_pan2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_pan'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Party Industry - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_ind'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_ind" id="crm_partymst_cust_ind1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_ind'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_ind'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_ind" id="crm_partymst_cust_ind2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_ind'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Source/Refer by - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_source'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_cust_source" id="crm_partymst_cust_source1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_cust_source'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_cust_source'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_cust_source" id="crm_partymst_cust_source2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_cust_source'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Territory - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_t_id'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_t_id" id="crm_partymst_t_id1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_t_id'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_t_id'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_t_id" id="crm_partymst_t_id2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_t_id'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Address - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_c_add_address'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_c_add_address" id="crm_partymst_c_add_address1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_c_add_address'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_c_add_address'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_c_add_address" id="crm_partymst_c_add_address2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_c_add_address'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>Country - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_c_add_country'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_c_add_country" id="crm_partymst_c_add_country1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_c_add_country'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_c_add_country'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_c_add_country" id="crm_partymst_c_add_country2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_c_add_country'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>State - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_c_add_state'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_c_add_state" id="crm_partymst_c_add_state1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_c_add_state'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_c_add_state'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_c_add_state" id="crm_partymst_c_add_state2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_c_add_state'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-6"><label><strong>City - Required? </strong></label></div>
							<div class="col-md-6">
								<div class="form-group">
									<div class="col-md-12">
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_c_add_city'] == '0'){ echo "active";}?>">
												<input type="radio" name="crm_partymst_c_add_city" id="crm_partymst_c_add_city1" autocomplete="off" value="0" <?php if($getpagePermission['crm_partymst_c_add_city'] == '0'){ echo "checked";}?>  > No
											</label>
											<label class="btn btn-secondary <?php if($getpagePermission['crm_partymst_c_add_city'] == '1'){ echo "active";}?>" >
												<input type="radio" name="crm_partymst_c_add_city" id="crm_partymst_c_add_city2" autocomplete="off" value="1" <?php if($getpagePermission['crm_partymst_c_add_city'] == '1'){ echo"checked"; }?>> Yes
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="col-md-3"></div>
							<button type="submit"  class="btn btn-success">Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
						<!--Vendor row end-->	
						<input type='hidden' name='permission_mode' id='permission_mode' value='add' />
						<input type='hidden' name='permission_modal' id='permission_modal' value='model'/>
						<input type='hidden' name='permission_id' id='permission_id' value='<?=$getpagePermission['permission_id'];?>'/>
					</div>
				</form>
			</div>	
		</div>
	</div>
</div>