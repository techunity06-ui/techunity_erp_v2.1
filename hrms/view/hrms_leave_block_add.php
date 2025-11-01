<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");

	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Leave Block";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER['REQUEST_URI'], "hrms_leave_block_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$block_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select blocklist.* from hrms_leave_block_list as blocklist 
				left join tbl_company as comp on comp.company_id = blocklist.company_id
				left join hrms_leave_block_day as blockday on blockday.leave_block_id = blocklist.id
				left join hrms_leave_block_allow_users as blockallowusers on blockallowusers.leave_block_id = blocklist.id
		 		where `blocklist`.`id` = $block_id and `blocklist`.`company_id` = $companyID";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3> <?=$mode .' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li ><a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_block_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="hrms_leave_block_add" action="javascript:;" method="post" name="hrms_leave_block_add">
										<div class="">
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Leave Block List Name </label>
														<div class="col-md-8 col-xs-12">
															<input id="leave_block_list_name" name="leave_block_list_name" type="text" class="form-control" title="Enter Leave Block Name" placeholder="Enter  Leave Block Name" value="<?php if($mode=='Edit'){ echo $rel['leave_block_list_name'];} ?>" >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label"></label>
														<div class="col-md-8 col-xs-12">
															<input type="checkbox" name="applied_to_company_flag" id="applied_to_company_flag" data-id="applied_to_company_flag" class="check_box_class" value="<?= ($mode == 'Edit') ? $rel['applied_to_company_flag'] : 'No' ?>" <?php if($rel['applied_to_company_flag'] == 'Yes') { echo 'checked'; } ?>>&nbsp;<span class="checkbox_label">Applies to Company <h6>(If not checked, the list will have to be added to each Department where it has to be applied.)</h6></span>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>BLOCK DAYS </h4>(Stop users from making Leave Applications on following days.)
											<h6>Leave Block List Dates</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="leave_block_days" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="10%" class="text-center">Block Date</th>
															<th width="25%" class="text-center">Block Reason</th>
															<th width="3%" class="text-center"></th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Block Date" style="vertical-align:top;">
																<input type="text"  name="block_date" title="Enter Block Date" placeholder="Block Date" id="block_date" class="form-control default-date-picker" />
															</td>
															<td data-label="Block Reason" style="vertical-align:top;">
																<textarea id="block_reason" name="block_reason" placeholder="Block Reason" title="Enter Block Reason" class="form-control" ></textarea>
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_leaveblockdata"></div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>ALLOW USERS </h4>(Allow the following users to approve Leave Applications for block days.)
											<h6>Leave Block List Allowed</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="leave_block_allow_users" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="35%" class="text-center">Allow User</th>
															<th width="3%" class="text-center"></th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="Approver Name" style="vertical-align:top;">
																<select id="employee_id" class="select2" name="employee_id">
																	<option selected disabled value="">SELECT EMPLOYEE</option>
																	<?php
																		$query = $dbcon->query("SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE `l_status` = 0 and company_id = $companyID and `l_group` = '58' order by l_name");
																		while ($r = $query->fetch_assoc()) {
																			if($rel['employee_id'] == $r['l_id']){
																				$employeeIDS = 'selected';
																			}else{
																				$employeeIDS = '';
																			}
																			echo '<option value="' . $r['l_id'] . '" '.$employeeIDS.'>' . $r['l_name'] . '</option>';
																		}
																	?>
																</select>
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addblockrow" id="addblockrow" onClick="return add_block_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_leaveblockallowuserdata"></div>
											<div class="col-md-12 margin_row">
												<div class="col-md-6">
										  			<div class="form-group">
														<label class="col-md-3 control-label">Status*</label>
														<div class="col-md-8 col-xs-11">
															<select id="status" class="select2" name="status">
																<option selected disabled value="">SELECT STATUS</option>
																<option value="0" <?php if($rel['status'] == '0') { echo 'selected'; } ?>>Active</option>
																<option value="1" <?php if($rel['status'] == '1') { echo 'selected'; } ?>>InActive</option>
															</select>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<center>
													<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
													<a href="<?= ROOT . HRMS_ROOT . 'hrms_leave_block_list'?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>		
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='eid' id='eid' value='<?=$rel['id']?>' />
										<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../../include/footer.php');?>
		</section>
		<?php include_once('../../include/include_js_file.php');?>   
			<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_leave_block.js"></script>
		<script>
			//CKEDITOR.replace('quotation_condition');
			$(".select2").select2({
				width: '100%'
			});
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$(document).ready(function(){
				$(document).on("click","#applied_to_company_flag", function(){
					if($(this).is(":checked")){
						$("#applied_to_company_flag").val('Yes');
					}else{
						$("#applied_to_company_flag").val('No');
					}
				});
			});
		</script>
		<?php 
			echo "<script>show_data() </script>";
			echo "<script>show_block_data() </script>";
		?>
	</body>
</html>
