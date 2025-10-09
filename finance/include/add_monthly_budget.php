<?php $budget = getAnnualBudget($dbcon);   ?>
<div class="modal colored-header info " id="modal-monthly-budget1" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Add Monthly Budget</h3>				
			</div>
			<div class="modal-body form">
				<div class="row">
				<div class="col-md-12">
					<form name="budgetForm" id="budgetForm" method="post" enctype="multipart/form-data" >
						<input type='hidden' name='mode' id='mode' value='add_budget' />
						<input type='hidden' name='edit_id' id='edit_id' value='<?= isset($budget[0]['budget_id']) ? $budget[0]['budget_id'] : '' ?>' />
					<div class="form-group">
						<div class="col-md-12">
							<div class="col-md-4">							
							</div>
							<div class="col-md-4">	
								<div class="form-group">
									  <label class="control-label" style="padding-left: 63px;">Annual Budget</label>
									  <div class="">
											<input type="text"  class="form-control" id="annual_budget" name="annual_budget" onkeyup="changeMonthlyBudget()" placeholder="" min="0" max="" value="<?php echo $budget[0]['annual_budget']; ?>"  onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
							<div class="col-md-4">							
							</div>
						</div>
						<hr>
						<div class="col-md-12" style="text-align: center; text-decoration: underline; margin: 20px; font-size: 25px;">
							Monthly Budgets
						</div>
						<div class="col-md-12" style="margin: 5px;">
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">April</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="april" onkeyup="changeAnnualBudget()" name="month[04]" placeholder="" min="0" max="" value="<?php echo (isset($budget[0]['budget_month']) && ($budget[0]['budget_month'] == 04)) ? $budget[0]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">May</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="2" onkeyup="changeAnnualBudget()" name="month[05]" placeholder="" min="0" max="" value="<?php echo (isset($budget[1]['budget_month']) && ($budget[1]['budget_month'] == 05)) ? $budget[1]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
						</div>
						<div class="col-md-12" style="margin: 5px;">
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">June</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="3" onkeyup="changeAnnualBudget()" name="month[06]" placeholder="" min="0" max="" value="<?php echo (isset($budget[2]['budget_month']) && ($budget[2]['budget_month'] == 06)) ? $budget[2]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">July</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="4" onkeyup="changeAnnualBudget()" name="month[07]" placeholder="" min="0" max="" value="<?php echo (isset($budget[3]['budget_month']) && ($budget[3]['budget_month'] == 07)) ? $budget[3]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
						</div>
						<div class="col-md-12" style="margin: 5px;">
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">August</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[08]" placeholder="" min="0" max="" value="<?php echo (isset($budget[4]['budget_month']) && ($budget[4]['budget_month'] == '08')) ? $budget[4]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">September</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[09]" placeholder="" min="0" max="" value="<?php echo (isset($budget[5]['budget_month']) && ($budget[5]['budget_month'] == '09')) ? $budget[5]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
						</div>
						<div class="col-md-12" style="margin: 5px;">
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">October</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[10]" placeholder="" min="0" max="" value="<?php echo (isset($budget[6]['budget_month']) && ($budget[6]['budget_month'] == 10)) ? $budget[6]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">November</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[11]" placeholder="" min="0" max="" value="<?php echo (isset($budget[7]['budget_month']) && ($budget[7]['budget_month'] == 11)) ? $budget[7]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
						</div>
						<div class="col-md-12" style="margin: 5px;">
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">December</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[12]" placeholder="" min="0" max="" value="<?php echo (isset($budget[8]['budget_month']) && ($budget[8]['budget_month'] == 12)) ? $budget[8]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">January</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[01]" placeholder="" min="0" max="" value="<?php echo (isset($budget[9]['budget_month']) && ($budget[9]['budget_month'] == 01)) ? $budget[9]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
						</div>
						<div class="col-md-12" style="margin: 5px;">
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">February</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[02]" placeholder="" min="0" max="" value="<?php echo (isset($budget[10]['budget_month']) && ($budget[10]['budget_month'] == 02)) ? $budget[10]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
							<div class="col-md-6">	
								<div class="form-group">
									  <label class="col-md-4 control-label">March</label>
									  <div class="col-md-8 col-xs-11">
											<input type="text"  class="form-control monthlyDivide" id="" onkeyup="changeAnnualBudget()" name="month[03]" placeholder="" min="0" max="" value="<?php echo (isset($budget[11]['budget_month']) && ($budget[11]['budget_month'] == 03)) ? $budget[11]['budget_month_amount'] : '0' ?>" onkeypress="return isNumberKey(event)" onchange="validateFloatKeyPress(this);" />
									  </div>
								</div>						
							</div>
						</div>								
					</div>
					<div class="col-md-5"></div>
					<div class="col-md-6">
						<button type="submit" class="btn btn-success"><?= !empty($budget[0]['budget_id']) ? 'Update' : 'Add' ?></button> &nbsp; &nbsp;
						<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
					</form>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
