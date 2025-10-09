<div class="modal colored-header info " id="modal-complain-add" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button"  class="btn_close  close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Add Complain Status</h3>
				
			</div>
			<div class="modal-body form">
			<div class="row">
			 <?php echo $_GET['fstat'];?>
			<div class="col-md-12">
			
			<form class="form-horizontal" role="form" id="comp_status_add" action="javascript:;" method="post" name="state_add">
					<div class="form-group">
					  <label class="col-md-12 control-label" style="text-align:left;line-height:25px">State Action *</label>
						<div class="col-md-12 col-xs-11" id="fstat_action">
							
							
						</div>
                    </div>
					
					<?php 
						$userid_m=$_SESSION['user_id']; 
						$emp_id=getEmployeeIdUser($dbcon,$userid_m);
						
						if($emp_id>0)
						{
					?>
					<div class="form-group"  id="emp_detail" style="display:none">
					  <label class="col-md-12 control-label" style="text-align:left;line-height:25px">Select Employee *</label>
						<div class="col-md-12 col-xs-11">
							<input type="hidden" name="f_emp" id="f_emp" value="<?=$emp_id ?>" />
						</div>
                    </div>
					<?php } else { ?>
						
						<div class="form-group" style="display:none" id="emp_detail">
						  <label class="col-md-12 control-label" style="text-align:left;line-height:25px">Select Employee *</label>
							<div class="col-md-12 col-xs-11">
								<select class="form-control" name="f_emp" id="f_emp">
									<option value="">--Select Employee--</option>
									<?=getAllEmployee($dbcon,"");?>
								</select>
							</div>
						</div>
						
					<?php } ?>
					
					
					<div class="form-group">
					  <label class="col-md-12 control-label" style="text-align:left;line-height:25px">Remark *</label>
						<div class="col-md-12 col-xs-11">
							<textarea class="form-control" name="f_remark" id="f_remark"></textarea>
						</div>
                    </div>
					
					
					<div class="col-md-12">
							<button type="submit" class="btn btn-success">Submit</button> &nbsp;
							<button type="button"  class="btn_close btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
					</div></div>
					
					<!--Vendor row end-->	
							<input type='hidden' name='complain_id' id='comp_id_hid' value='complain_id' />				  
							<input type='hidden' name='followup_id' id='followup_id' value='<?php echo $row['followup_status']; ?>' />				  
							
						  </form>
				</div>
			</div>	
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>
