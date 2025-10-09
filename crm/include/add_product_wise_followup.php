<div class="modal colored-header info" id="modal-product-add-followup" role="dialog" data-keyboard="false" data-backdrop="static">
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
								<option value="">Choose Task Type</option>$
								<?=get_master_category_dtl($dbcon,16,10,$inquiry_id,1);//10:Task?>
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
				

				<div  class="col-md-12"> 
					<div class="form-group"> 
					 <label class="col-md-2 control-label" >Remark *</label>
					  <div class="col-md-10">
						<textarea class="form-control" id="followup_remark"></textarea>
					  </div>
					</div> 
				</div>

				
				<div class="col-md-12 text-center">
					<input type="hidden" class="form-control" name="cust_id" id="cust_id" />
					<input type="hidden" class="form-control" name="f_product_id" id="f_product_id" />
					<button type="button" class="btn btn-success" tabindex="305" onclick="add_product_followup()">Submit</button> &nbsp;
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
