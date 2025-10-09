<div class="modal colored-header info" id="workorder_package_print_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Workorder Package Print</h3>
			</div>	
			<div class="modal-body form" style="height:350px !important;">
				<form class="form-horizontal" role="form" id="wo_print_add" action="<?=ROOT.PRODUCTION_ROOT.'workorder_package_print'?>" method="post" name="wo_print_add">
					<input type="hidden" name="workorder_packing_trn_id" id="workorder_packing_trn_id">
				</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

