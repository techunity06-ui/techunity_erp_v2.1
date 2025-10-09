<div class="modal colored-header info" id="import_stock_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Import Opening Stock</h3>
			</div>
			<div class="modal-body form">
				<form class="form-horizontal" role="form" id="import_stock" action="javascript:;" method="post" name="import_stock">

					<div class="row mtop20">
						<div class="col-md-12 m-bot15">
							<div class="form-group">
							  <label class="col-md-3 control-label">Import Stock File</label>
									<div class="col-md-4 col-xs-11">
									<input type="file" id="excel_file" name="excel_file" class="form-control"  accept=".csv" required title="Select File"/>
									 <div id="msg"></div>
								</div>							
							 </div>
							 <div class="form-group">
							  <label class="col-md-3 control-label">File Formate</label>
									<div class="col-md-6 col-xs-11">
					<a href="<?=ROOT.PRODUCTION_ROOT.'upload/opening_stock_excel/demo_stock_import.csv'?>" target="_blank" class="btn btn-info">Click to View Csv File Formate  </a>
					<div id="msg"></div>
								</div>
								
							 </div>
						</div>

						<div class="col-md-12 text-center">
							<div class="form-group"	>
								<input type="submit" class="btn btn-primary" value="ADD"/>
								<input type="button" class="btn btn-danger" data-dismiss="modal" aria-hidden="true" value="Cancel">
							</div>
						</div>
					</div>
					<input type='hidden' name='mode' id='mode' value='check_data' />
				</form>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->