<div class="modal colored-header info " id="ModalSalesman" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Allocate Salesman</h3>
				
			</div>
			<div class="modal-body form">
				<div class="row">
					
					<div class="col-md-12">
						
						<form class="form-horizontal" role="form" id="" action="javascript:;" method="" name="">
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Select Salesman *</label>
								<div class="col-md-12 col-xs-11">
									<select class="select2" name="salesman_id" id="salesman_id" onchange="get_salesman_detail(this.value)"> 
										<option value="">--Select Salesman--</option>
										<?=get_salesman_ledger_selectbox($dbcon);?>
									</select>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Bill Amount *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control numbersOnly valid" name="sales_bill_amt" id="sales_bill_amt" onkeyup="set_salesman_percentage(this.value)" />
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Total Qty *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control numbersOnly valid" name="sales_tot_qty" id="sales_tot_qty" onkeyup="set_salesman_bag(this.value)" />
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Commision Type *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="sales_comm_type" id="sales_comm_type" readonly  />
								</div>
							</div>
							
							<div class="form-group" id="comm_per_div">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Commision Percentage *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control numbersOnly valid" name="sales_comm_percentage" id="sales_comm_percentage" onkeyup="set_salesman_percentage(this.value)" />
								</div>
							</div>
							
							<div class="form-group"  id="comm_bag_div">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Commision Per Bag *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control numbersOnly valid" name="sales_comm_bag" id="sales_comm_bag" onkeyup="set_salesman_bag(this.value)" />
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Commision Amount *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control numbersOnly valid" name="sales_comm_amount" id="sales_comm_amount" />
								</div>
							</div>
							
							<div class="col-md-12">
								<button type="button" class="btn btn-success" onclick="add_salesman_transaction()">Submit</button> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</div></div>
							
							<!--Vendor row end-->	
							<input type='hidden' name='salesman_popup_id' id='salesman_popup_id' value='' />
							
					</form>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
