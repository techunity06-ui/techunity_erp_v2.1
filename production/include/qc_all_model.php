<div class="modal colored-header info" id="qc_all_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>QC ALL</h3>
			</div>	
			<div class="modal-body form" style="height:350px !important;">
				<form class="form-horizontal" role="form" id="qc_all_add" action="<?=ROOT.PRODUCTION_ROOT.'qc_all'?>" method="post" name="qc_all_add">
					<input type="hidden" name="qc_all_batch_id" id="qc_all_batch_id">
				</form>
					<!-- <div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">QC TYPE *</label>
								<div class="col-md-8 col-xs-11">
									 <select class="select2" name="qc_type" id="qc_type">
	                                 	<option value="accept"> ACCEPT </option>
	                                 	<option value="reject"> REJECT </option>
	                                 </select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-4 control-label">QC TYPE *</label>
								<div class="col-md-8 col-xs-11">
									 <select class="form-control" name="qc_all_godown_id" id="qc_all_godown_id">
											<?=get_all_godown($dbcon,'','');?>
										</select>
								</div>
							</div>
						</div>

						<input type="hidden" name="batch_id" id="batch_id" value="" />
						<div class="col-md-12 text-center">
							<div class="form-group">
								<input type="button" class="btn btn-primary" value="ADD"  style="" onclick="submit_qc()" id="add_param" />
							</div>
						</div>
					</div> -->
				<!-- </form> -->
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

