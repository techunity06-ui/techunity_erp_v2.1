<div class="modal colored-header info " id="bs-consignee-modal-lg" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Consignee <br/> Company Name : <span id="lcuname"></span></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<form class="form-horizontal" role="form" id="consignee_add" action="javascript:;" method="post" name="consignee_add">
						<div class="col-md-12">
							<div  class="col-md-4">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Company Name *</label>
									<div class="col-md-12 col-xs-11">
										<input type="text" class="form-control" name="lconsignee_comp_name" id="lconsignee_comp_name" />
									</div>
								</div>
							</div>		
							<div  class="col-md-4">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Person Name</label>
									<div class="col-md-12 col-xs-11">
										<input type="text" class="form-control" name="lconsignee_name" id="lconsignee_name" autocomplete="off" />
									</div>
								</div>
							</div>
							<div  class="col-md-4">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Mobile</label>
									<div class="col-md-12 col-xs-11">
										<input type="number" class="form-control numbersOnly" name="lconsignee_mobile" id="lconsignee_mobile" onkeypress="return isNumberKey(event)" maxlength="10" autocomplete="off" />
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div  class="col-md-4">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Email</label>
									<div class="col-md-12 col-xs-11">
										<input type="text" class="form-control" name="lconsignee_email" id="lconsignee_email" autocomplete="off" title="Not valid Email" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}"  />
									</div>
								</div>
							</div>
							<div  class="col-md-8">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Address</label>
									<div class="col-md-12 col-xs-11">
										<textarea class="form-control" name="lconsignee_address" id="lconsignee_address" autocomplete="off"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div  class="col-md-4">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Country</label>
									<div class="col-md-12 col-xs-11">
										<select class="select2" name="lcountry_consinee_id" id="lcountry_consinee_id" onChange="load_consinee_state(this.value,'lstate_consinee_id','')" autocomplete="off">
											<?=get_country($dbcon,$countryid)?>				
										</select>
									</div>
								</div>
							</div>
							<div  class="col-md-4">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">State</label>
									<div class="col-md-12 col-xs-11">
										<select class="select2" name="lstate_consinee_id" id="lstate_consinee_id" onChange="load_consinee_city(this.value,'lcity_consinee_id','')" autocomplete="off" >
											<option value="">Select State</option>
										</select>
									</div>
								</div>
							</div>
							<div  class="col-md-4">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">City</label>
									<div class="col-md-12 col-xs-11">
										<select class="select2" name="lcity_consinee_id" id="lcity_consinee_id" autocomplete="off">
											<option value="">Select City</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div  class="col-md-4">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">GST No</label>
									<div class="col-md-12 col-xs-11">
										<input type="text" class="form-control" name="lgst_consinee_no" id="lgst_consinee_no" autocomplete="off" />
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<center>
								<input type="button" class="btn btn-primary" value="ADD" style="box-shadow: 3px 3px #61a642;" onclick="led_add_consignee()" id="add_consignee_btn" /> &nbsp;
								<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
							</center>
						</div>
							<input type='hidden' name='lmodel' id='lmodel' value='model' />
							<input type='hidden' name='lledger_id' id='lledger_id' value='' />
					</form>
				</div>
			</div>	
		</div>
	</div>
 </div>
 

