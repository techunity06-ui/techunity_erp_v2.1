<div class="modal colored-header info" id="store_approve_all_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>QC ALL</h3>
			</div>	
			<div class="modal-body form" style="height:350px !important;">
				<form class="form-horizontal" role="form" id="store_approve_all_add" action="<?=ROOT.INVENTORY_ROOT.'store_approve_all'?>" method="post" name="store_approve_all_add">
					<input type="hidden" name="all_batch_id" id="all_batch_id">
				</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

