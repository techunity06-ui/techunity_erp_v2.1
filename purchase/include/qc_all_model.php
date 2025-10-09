<div class="modal colored-header info" id="qc_all_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>QC ALL</h3>
			</div>	
			<div class="modal-body form" style="height:350px !important;">
				<form class="form-horizontal" role="form" id="qc_all_add" action="<?=ROOT.PRODUCTION_ROOT.'purchase_qc_all'?>" method="post" name="qc_all_add">
					<input type="hidden" name="qc_all_batch_id" id="qc_all_batch_id">
				</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

