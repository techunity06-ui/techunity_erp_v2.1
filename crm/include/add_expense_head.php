<div class="modal colored-header info " id="add_expense_head_modal" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog custom-width">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="btn_close close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
		<h3>Add Group Head</h3> 
	</div>
	<div class="modal-body form">
		<div class="row"> 
			<div class="col-md-12"> 
			<form class="form-horizontal" role="form" id="group_add" action="javascript:;" method="post" name="expense_head_add"> 
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Group Head Name *</label>
					<div class="col-md-12 col-xs-11">
						<input class="form-control" type="text" name="g_name" id="g_name" placeholder="Group Head Name" value="" />
					</div>
				</div>
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Group Parent *</label>
					<div class="col-md-12 col-xs-11">
						<select class="form-control" name="g_parent" id="g_parent">
							<?=get_all_group($dbcon,'');?>
					    </select>
					</div>
				</div>
				<div class="col-md-12">
					<button type="submit" class="btn btn-success">Submit</button> &nbsp;
					<button type="button" class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
				<input type='hidden' name='mode' id='mode' value='add_model' />	 
			</form>
			</div> 
		</div>
	</div>	
</div>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog --> 
