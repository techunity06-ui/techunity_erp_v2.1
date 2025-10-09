
<!--Order Confirmation Modal Start-->
<div class="modal colored-header info" id="order_conf_dtl_modal" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Quotation: <strong id="head_ord_qt_no"></strong></h3>
			</div>
			<div class="modal-body form">
				<div class="row">
					
					<div class="col-md-12">
						
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Confirmation Attachment</label>
							<div class="col-md-9">
								<input type="file" class="form-control" name="qt_order_conf_attch" id="qt_order_conf_attch" title="Confirmation Attachment" />
							</div>
							<div class="col-md-2">
								<a href="" id="qt_order_conf_attch_view" target="_blank" class="btn btn-primary"><i class="fa fa-eye"></i> View</a>
							</div>
						</div>
						
						<div class="clearfix"></div>
						<div class="col-md-12 text-center" style="margin-top:10px;">
							<button type="button" id="add_attch_order_conf_dtl_btn" onclick="add_order_conf_dtl();" class="btn btn-success">Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
					</div>
					<input type='hidden' name='qt_ord_ref_id' id='qt_ord_ref_id' value='' />	
					
				</div>
			</div>	
		</div>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<!--Order Confirmation Modal end-->