<div class="modal" id="model_addaccount" role="dialog"  aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Account</h3>
			</div>
			
			<div class="modal-body form">
			
				<div class="row">
				<form class="form-horizontal" role="form" id="bank_account_add" action="javascript:;" method="post" name="bank_account_add">
				<div  class="col-md-10 col-md-offset-1">
				<div class="form-group">
				  <label for="Product Type">Select Bank *</label>
				  <select class="select2" id="edit_bankid" name="bankid" title="Select Bank" required >
					  <?=getbank($dbcon,0)?>
				  </select>
			  </div>
				<div class="form-group">
								  <label for="Product Type">Branch *</label>
								  <input type="text"  class="form-control" id="branch_name" name="branch_name" placeholder="" required/>
							  </div>
				<div class="form-group">
								  <label for="Product Type">City *</label>
								  
								<select class="select2" id="cityid" name="cityid" title="Select Bank" required >
									  <?=getcity($dbcon,'1',$cid)?>
								  </select>
							  </div>
				<div class="form-group">
					  <label for="Product Type">Account Name *</label>
					  <input type="text"  class="form-control" id="acc_name" name="acc_name"  required/>
				  </div>
				  <div class="form-group">
					  <label for="Product Type">Account Number *</label>
					  <input type="text"  class="form-control" id="acc_number" name="acc_number" placeholder="" required />
				  </div>
				<div class="form-group">
					  <label for="Product Type">Cheque Series Starting Number </label>
					  <input type="number"  class="form-control" id="acc_chequeno" name="acc_chequeno" placeholder=""  min="0" max="100000"/>
				  </div>
				<div class="form-group">
					  <label >Number of Cheques </label>
					  <input type="number"  class="form-control" id="acc_chequeleft" name="acc_chequeleft" placeholder="" min="0" max="1000"/>
				  </div>
				<div class="form-group">
					  <label>Opening Balance</label>
					  <input type="number"  class="form-control" id="opn_balance" name="opn_balance" placeholder="" min="0" max="1000000"/>
				  </div>
		</div>
		<button type="submit" class="btn btn-success">Submit</button> &nbsp;
		<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button><div class="col-md-3"></div>					</div>
						</div>	
				<input type='hidden' name='mode' id='mode' value="Add" />
				<input type='hidden' name='model' id='model' value='model' />
				<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
			</form>
			</div>
			</div>
			</div>
		</div>
	</div>
</div>
<!--<div class="modal fade" id="add_customer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                  <div class="modal-dialog modal-lg">
                                      <div class="modal-content">
                                          <div class="modal-header">
                                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                              <h4 class="modal-title">Modal Tittle</h4>
                                          </div>
                                          <div class="modal-body">

                                              Body goes here...

                                          </div>
                                          <div class="modal-footer">
                                              <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
                                              <button class="btn btn-warning" type="button"> Confirm</button>
                                          </div>
                                      </div>
                                  </div>
                              </div>-->