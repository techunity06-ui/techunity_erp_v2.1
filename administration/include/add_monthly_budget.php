<?php //$budget = getAnnualBudget($dbcon);   ?>
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
						
					<div class="monthly_bud">
						
					</div>					
					</form>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>
