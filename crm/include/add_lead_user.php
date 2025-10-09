<div class="modal colored-header info " id="bs-user_allocate" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>User Allocate</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="ind_add" action="javascript:;" method="post" name="ind_add">
							<div class="clearfix"></div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Assign To*</label>
									<div class="col-md-12">
										<select class="select2" id="assign_user_ids" name="assign_user_ids[]" title="Choose Assign User" placeholder="Choose Assign User" multiple="multiple" required >
											<?=get_assign_users($dbcon, $assign_user_ids, " and user_type in(2,8,9,21,22)");?>
										</select>
									</div>
								</div>	
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<center>
										<button class="btn btn-primary" data-original-title="Add To Inquiry" data-toggle="tooltip" data-placement="top" onClick="print_cust_label();"><i class="fa fa-upload"></i> Add To Inquiry</button>
									</center>
								</div>	
							</div>
						</form>
					</div>
				</div>	
			</div>
		</div>
	</div>
</div>

