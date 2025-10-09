<div class="modal colored-header info" id="modal-value-add-followup" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog modal-lg">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
		<h3>Add Followup</h3> 
	</div>
	<div class="modal-body form">
		<div class="row row_margin"> 
			<div class="col-md-12"> 
			<form class="form-horizontal" role="form" id="product_add" action="javascript:;" method="post" name="product_add">

				<div class="col-md-12">
					<div class="form-group">
						<label class="col-md-2 control-label">Task*</label>
						<div class="col-md-6"> 
							<select class="select2" id="task_type_id" name="task_type_id" required onchange="check_assign_user(this.value)">
								<option value="">Choose Task Type</option>
								<!-- <=get_master_category_dtl($dbcon,28,10,'POST',1);//10:Task?> -->
							</select>
						</div>
					</div>	
				</div>


				<div class="clearfix"></div>	
				<div class="col-md-12">
					<div class="form-group">
						<label class="col-md-2 control-label row_margin" style="text-align:left;" for="product_name">Next Followup Date*</label>
						<div class="col-md-6">
							<div data-date="<?=$task_due_date?>" class="input-group date form_datetime-meridian">
								<input type="text" class="form-control" value="<?=$task_due_date?>" name="task_due_date" id="task_due_date" autocomplete="off">
								<div class="input-group-btn">
									<button type="button" class="btn btn-info date-set"><i class="fa fa-calendar"></i></button>
								</div>
							</div>
						</div>			  
					</div>			  
				</div>
				
				<div class="col-md-6" id="task_stage_div" <?= $style ?>>
					<div class="form-group">
						<label class="col-md-4 control-label">Stage</label>
						<div class="col-md-6"> 
							<select class="select2" id="opp_id" name="opp_id"  >
								<?=get_inquiry_stage($dbcon,$opp_id);?>
							</select>
						</div>
					</div>	
				</div>
				<input type="hidden" id="stage_prob" name="stage_prob" class="form-control" value="<?=$stage_prob?>">
				<div class="col-md-6" id="task_sales_stage_div" <?= $style ?>>
					<div class="form-group">
						<label class="col-md-3 control-label" style="white-space:nowrap;">Sales Stage</label>
						<div class="col-md-6"> 
							<select class="select2" id="sales_stage_id" name="sales_stage_id">
								<option value="">Choose Sales Stage</option>
								<!-- <= get_master_category_dtl($dbcon,$sales_stage,7) ?> -->
							</select>
						</div>	
					</div>	
				</div>

				<div class="clearfix"></div>
				<div class="col-md-12">
					<div class="form-group">
						<label class="col-md-2 control-label">Assign To</label>
						<div class="col-md-6"> 
							<select class="select2" id="assign_user_ids" name="assign_user_ids" placeholder="Choose Assign User" >
								<?=get_assign_users($dbcon, $_SESSION['user_id'], " and user_type in(".$crm_user_type.")");?>
							</select>
							<div id="no_of_inquiry" style="font-size: 12px; color: #337ab7;"></div>
						</div>
					</div>	
				</div>
				<div class="clearfix"></div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-4 control-label">Priority</label>
						<div class="col-md-6"> 
							<select class="select2" id="task_priority_id" name="task_priority_id">
								<?=get_task_priority($dbcon,$rel['task_priority_id']);?>
							</select>
						</div>
					</div>	
				</div>

				<div class="clearfix"></div>
				<div class="col-md-12">
					<div class="form-group">
						<label class="col-md-2 control-label">Alert</label>
						<div class="col-md-6"> 
							<select class="select2" id="task_alert_id" name="task_alert_id">
								<?=get_task_alert_types($dbcon,$task_alert_id);?>
							</select>
						</div>
					</div>
				</div>

				<?php // Amish Soni Start 19-01-2021
				    if($showTemplate) { ?>
				    	<div class="clearfix"></div>
				    	<div class="col-md-12">
				    		<div class="form-group">
				    			<label class="col-md-2 control-label">Email Template</label>
				    			<div class="col-md-6">
				    				<select class="select2" id="email_template_id" name="email_template_id">
				    					<?php echo getAllEmailSMSTemplate($dbcon,2, $email_template_id) ?>
				    				</select>
				    			</div>
				    		</div>
				    	</div>
			    <?php } // Amish Soni End 19-01-2021 ?>


				<div  class="col-md-12"> 
					<div class="form-group"> 
					 <label class="col-md-2 control-label" >Remark *</label>
					  <div class="col-md-10">
						<textarea class="form-control" id="followup_remark"></textarea>
					  </div>
					</div> 
				</div>


				<hr/>
					<div class="col-md-12">
						<div class="card">
							<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
								<li role="presentation" id="tab2" class="active"><a href="#attch-section" aria-controls="attch-section" role="tab" data-toggle="tab">Attachments</a></li>
							</ul>
							<div class="tab-content">
								<div role="tabpanel" class="tab-pane active" id="attch-section">
									<div class="form-group" style="margin-top:20px;">
										<?php if($mode!='view'){?>
											<table class="display table table-bordered table-striped">
												<thead>
													<tr>
														<th width="40%" class="text-center">Document Name</th>
														<th width="50%" class="text-center">Upload Document</th>
														<th width="10%" class="text-center">Action</th>
													</tr>
												</thead>
												<tbody>
													<tr>
														<td>
															<input type="text" class="form-control" id="inq_attch_doc_name" name="inq_attch_doc_name" value="" placeholder="Document Name">
														</td>
														<td>
															<input type="file" class="form-control" id="inq_attch_file" name="inq_attch_file">
														</td>
														<td>
															<button type="button" class="btn btn-primary" id="task_attch_btn" onclick="add_task_attch_field()">Add</button>
														</td>
													</tr>
												</tbody>
											</table>
										<?php } ?>
									</div>
									<div class="form-group" style="margin-top:20px;" id="task_attch_trn_div"></div>
								</div>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<hr/>
				
				<div class="col-md-12 text-center">
					<input type="hidden" class="form-control" name="cust_id" id="cust_id" />
					<input type="hidden" class="form-control" name="month_id" id="month_id" />
					<button type="button" class="btn btn-success" tabindex="305" onclick="add_value_followup()">Submit</button> &nbsp;
					<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true" tabindex="306">Close</button>
				</div>
			</form>
			</div> 
		</div>

		<div class="row ">
			<div class="col-md-12 load_product_details">
				
			</div>
		</div>

	</div>	
</div>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog --> 
