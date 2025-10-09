<div class="modal colored-header info " id="bs-add_ind_data" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Update Data Bank</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="ind_add" action="javascript:;" method="post" name="ind_add">
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">ID</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="QUERY_ID" placeholder="ID" id="QUERY_ID" title="Enter Id" value="" readonly />
									</div>
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">SENDER NAME</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="SENDERNAME" id="SENDERNAME" placeholder="SENDERNAME" value="" />
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">SENDEREMAIL</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="SENDEREMAIL" placeholder="SENDER E-MAIL" id="SENDEREMAIL" title="Enter SENDER E-MAIL" value=""  />
									</div>
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">COMPANY NAME</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="GLUSR_USR_COMPANYNAME" id="GLUSR_USR_COMPANYNAME" placeholder="COMPANY NAME" value="" />
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">MOBILE NO</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="MOB" placeholder="Mobile" id="MOB" title="Enter Mobile" value=""  />
									</div>
								</div>	
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">ADDRESS</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="ENQ_ADDRESS" id="ENQ_ADDRESS" placeholder="ADDRESS" value="" />
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">INDIAMART  STATE</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="ENQ_STATE" id="ENQ_STATE" placeholder="STATE" value="" />
									</div>
								</div>	
							</div>
							<div class="col-md-6" >
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">State</label>
									<div class="col-md-12">
										<select class="select2" name="stateid" id="stateid" onChange="load_city(this.value,'cityid','')">
											<option value="">Select State</option>	
											<?=getstate($dbcon)?>
										</select>
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">INDIAMART  CITY</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="ENQ_CITY" placeholder="CITY" id="ENQ_CITY" title="Enter CITY" value="" />
									</div>
								</div>	
							</div>
							<div class="col-md-6" >
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">CITY</label>
									<div class="col-md-12">
										<select class="select2" name="cityid" id="cityid">
											<option value="">Select City</option>	
										</select>
									</div>
								</div>	
							</div>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">INDIAMART PRODUCT NAME</label>
									<div class="col-md-12">
										<input type="text" class="form-control" name="PRODUCT_NAME" placeholder="PRODUCT NAME" id="PRODUCT_NAME" title="Enter PRODUCT NAME" value=""  />
										<!--<br/><span id="ero" style="color:red;">PRODUCT NOT ADDED..</span>-->
									</div>
								</div>	
							</div>
							<?if($getspecialConfiguration['reciclar']==1){?>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">PRODUCT CATEGORY</label>
									<div class="col-md-12">
										<select class="select2" id="cat_id" name="cat_id" data-placeholder="Choose Category" onchange="get_product_cat_wise()">
											<?=get_all_category($dbcon,"","");?>
										</select>
									</div>
								</div>	
							</div>
							<?}?>
							
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">PRODUCT NAME</label>
									<div class="col-md-12">
										<select class="select2" id="product_id" name="product_id" data-placeholder="Choose Product">
											<?=getproduct_typewise($dbcon,"",$is_umaboy ? "0" : "");?>
										</select>
									</div>
								</div>	
							</div>

							<?if($getspecialConfiguration['reciclar']==1){?>
								<div class="col-md-6">
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Reciclare Category</label>
										<div class="col-md-12">
											<select class="select2" id="parent_cat_id" name="parent_cat_id" data-placeholder="Reciclare Category">
												<?=get_all_reciclare_category($dbcon,0);?>
											</select>
										</div>
									</div>	
								</div>
							<?}?>
							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Branch</label>
									<div class="col-md-12">
										<select class="select2" id="branch_id" name="branch_id" title="Choose Branch" placeholder="Choose Branch" required>
											<?=get_branch_name_company($dbcon, ""); ?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Assign To</label>
									<div class="col-md-12">
										<select class="select2" id="assign_user_ids" name="assign_user_ids" title="Choose Assign User" placeholder="Choose Assign User" required>
											<?//=get_assign_users($dbcon, $rel['assign_user_ids'], " and user_id not in(".$_SESSION['user_id'].")");?>
											<?//=get_assign_users($dbcon, $rel['assign_user_ids'], " and user_type in(2,8,9,21,22)");?>
											<?=get_users_typewise($dbcon, $rel['assign_user_ids'], " and user_type in(".$crm_user_type.")");?>
										</select>
									</div>
								</div>	
							</div>

							<?php if($enable_assing_user==1){ ?>
								<div class="col-md-6">
									<div class="form-group">
										<label for="Product Type" style="text-align:left;line-height:25px" class="col-md-12 control-label">Owner User </label>
										<div class="col-md-12 col-xs-11">
											<select class="select2" name="cust_owner" id="cust_owner">
												<option value="">--Owner User--</option>
												<?php 
												// if($mode=='Edit')
												// {
												// 	$qry="select * from users where active=0 and user_id!='$custid' AND company_id = '".$_SESSION['company_id']."'";
												// 	$user_report_arr=explode(",",$rel['user_report']);
												// }
												// else
												// {
												// }
												$qry="select * from users where active=0 AND company_id = '".$_SESSION['company_id']."'";
												$rs_state=$dbcon->query($qry);
												while($row=mysqli_fetch_array($rs_state))
												{ ?>
													<option value="<?php echo $row['user_id']; ?>" >
														<?php echo $row['user_name']; ?>
													</option>
												<?php } ?>
											</select>
										</div>
									</div>							 
								</div>
							<?php } ?>

							<div class="clearfix"></div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Remark</label>
									<div class="col-md-12">
										<textarea id="ENQ_MESSAGE" name="ENQ_MESSAGE" class="form-control" rows="3" style="resize:both;"></textarea> 
									</div>
								</div>	
							</div>
                            <?php // Amish Soni Start 19-01-2021
                            if($showTemplate) { ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-md-12 control-label" style="text-align:left;line-height:25px">Email Template</label>
                                        <div class="col-md-12">
                                            <select class="select2" id="email_template_id" name="email_template_id">
                                                <?php echo getAllEmailSMSTemplate($dbcon,2) ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            // Amish Soni End 19-01-2021 ?>
							<div class="clearfix"></div>
							<div class="col-md-3"></div>
							<button type="submit"  class="btn btn-success">Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							<input type='hidden' name='i_id' id='i_id' value='' />
							<input type='hidden' name='mode' id='mode' value='Add' />
						</form>
					</div>
				</div>	
			</div>
		</div>
	</div>
</div>

