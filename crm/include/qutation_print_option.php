<div class="modal colored-header info" id="open_qutation_print_option" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Qutation Print Setting : #<span id="apprv_ref_no"></span></h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="card">
						<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
							<li role="presentation" id="tab1" class="active"><a href="#quot_detail" aria-controls="quot_detail" role="tab" data-toggle="tab">Quotation Details</a></li>
							<li role="presentation" id="tab2"><a href="#produ_detail" aria-controls="produ_detail" role="tab" data-toggle="tab">Product Detail</a></li>
						</ul>
						<div class="tab-content"> 
							<div role="tabpanel" class="tab-pane active" id="quot_detail">
								<div class="col-md-12" id="mod_quot_comp_div_sec"></div>
								<input type="text" id="ref_quotation_id" name="ref_quotation_id" value="">
							</div>
							<div role="tabpanel" class="tab-pane" id="produ_detail">
								<div class="col-md-12" id="mod_quot_pro_div_sec"></div>
							</div>
						</div>
					</div>
					<div class="col-md-12" id="mod_per_div_sec">
						<div class="form-group">
							<table class="display table table-bordered table-striped">
								<tr>
									<!--<th width="30%">Assign User</th>-->
									<th width="20%">Status</th>
									<th width="45%">Remark</th>
									<th width="5%">Action</th>
								</tr>
								<tr>
									<!--<td>
										<select class="select2" id="assign_user_ids" name="assign_user_ids" placeholder="Choose Assign User">
											<?php //=get_assign_users($dbcon, '', " and user_id not in(".$_SESSION['user_id'].")");?>
										</select>
									</td>-->
									<td>
										<select class="select2" id="approve_status" name="approve_status">
											<option value="1">Approve</option>
                                                                                        <option value="2">Reject</option>
										</select>
									</td>
									<td>
										<textarea class="form-control" id="approve_remark" name="approve_remark" placeholder="Remark"></textarea>
									</td>
									<td>
										<input type="button" class="btn btn-primary" id="apprv_btn" onclick="add_apprv_hist()" value="Add">
									</td>
								</tr>
							</table>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="sales-order-history-datatable">
								<thead>
									<tr>
										<th>Sr. No.</th>
										<th>User</th>
										<th>Status</th>
										<th>Remark</th>
										<th>Date</th>					  
									</tr>
								</thead>
								<tbody>
								</tbody>				 
							</table>
						</div>
						</div>
					</div>
					
					
				</div>
			</div>	
		</div>
		<input type="text" id="ref_sales_order_id" name="ref_sales_order_id" value="">
		<input type="text" id="ref_quotation_id" name="ref_quotation_id" value="">
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->