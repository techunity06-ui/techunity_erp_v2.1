<div class="modal colored-header info" id="show_date_models" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Assign Date</h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					<div class="col-md-12">
						<form class="form-horizontal" role="form" id="add_date_notes" action="javascript:;" method="post" name="add_date_notes">
							<div class="col-md-12">
								<div class="form-group">
									<label class="col-md-3 control-label">Choose Date</label>
									<div class="col-md-9"> 
										<input id="notes_date" name="notes_date" type="date" class="form-control" title="Date" value="<?=date('Y-m-d');?>">
										<input id="notes_id" name="notes_id" type="hidden">
										<input id="notes_status" name="notes_status" type="hidden">
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<button class="btn third" onclick="assign_date('0')" id="assign">Assign Date</button>
								<button class="btn third" onclick="assign_date('1')" id="done">Done Note</button>
								<button class="btn third" onclick="assign_date('3')" id="cancel">Cancel Note</button>
								<button class="btn third" onclick="assign_date('2')" id="delete">Delete Note</button>
							</div>
						</form>
					</div>
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

