<?php

session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once("../../include/common_functions.php");
include_once("../../include/hrms_common_functions.php");
include_once("../../include/function_database_query.php");

$form = "SMS Template List";
$mode="Add";
$companyID = $_SESSION['company_id'];
$userID =  $_SESSION['user_id'];
if(strpos($_SERVER[REQUEST_URI], "hrms_sms_template_edit")==true) {
	$mode="Edit";
	$smsId = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from hrms_sms_template where id=$smsId and company_id = $companyID and user_id = $userID";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	if($rel){
	}else{
		header("Location: ".DOMAIN . HRMS_ROOT ."hrms_sms_template_list");
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php'); ?>
</head>
<style>
.cke_chrome{
	border: 1px solid #d1d1d1 !important;
}	
</style>
<body>
	<section id="container" class="sidebar-closed">
		<?php include_once('../../include/include_top_menu.php'); ?>
		<?php include_once('../../include/left_menu.php'); ?>
			<section id="main-content">
					<section class="wrapper">
							<div class="row">
								<div class="col-lg-12">
									<section class="panel">
										<header class="panel-heading">
											<h3><?= $mode . ' ' . $form ?></h3>
										</header>
										<div class="">
											<ul class="breadcrumb">
												<li><a href="<?= ROOT . HRMS_ROOT . 'hr_dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
												<li><a href="<?= ROOT . HRMS_ROOT . 'hrms_email_template_list' ?>"><?= $form ?></a></li>
											</ul>
										</div>
									</section>
								</div>
							</div>
							<form class="form-horizontal" role="form" id="hrms_sms_template_add" action="javascript:;" method="post" name="hrms_sms_template_add">
								<div class="row">
									<div class="col-sm-12">
										<section class="panel">
											<div class="panel-body">
												<h4><?php if($mode == 'Add') { echo "New"; }else{ echo "Edit"; } ?> <?= $form ?></h4>
													<div class="col-md-12" style="padding-top: 25px;">
														<div class="col-md-12 margin_row">
															<div class="col-md-6">
													  			<div class="form-group">
													  				<label class="col-md-3 control-label" style="">SMS Name*</label>
													  				<div class="col-md-8 col-xs-11">
																		<input type="text" class="form-control" id="sms_template_name" name="sms_template_name" title="Enter SMS Name" placeholder="SMS Name" value="<?php if($mode=='Edit'){ echo $rel['sms_template_name'];} ?>" required>
																	</div>
													  			</div>
													  		</div>
													  		<div class="col-md-6"></div>
													  	</div>
														<div class="col-md-12 margin_row">
															<div class="row col-md-12">
													  			<div class="form-group">
																	<label class="col-md-2 control-label" style="text-align: center;margin-left: 8px;">SMS Body*</label>
																	<div class="col-md-9 col-xs-11" style="margin-left: -53px;">
																		<textarea style="border: 1px solid #ccc;" id="sms_template_body" name="sms_template_body" placeholder="Enter SMS Body" rows="15" cols="75" required><?php if($mode=='Edit') { echo $rel['sms_template_body']; } ?></textarea>
																	</div>
																</div>
													  		</div>
														</div>
														<div class="col-md-12 margin_row">
															<div class="col-md-6">
					                                            <div class="form-group">
					                                                <label for="sms_document_attachment" class="col-md-3 control-label">Upload Document File*</label>
					                                                <div class="col-md-8 col-xs-11">
					                                                    <input type="file" id="sms_document_attachment" name="sms_document_attachment" title="Select Document" />
					                                                    <div id="msg"></div>
					                                                </div>
					                                            </div>                     
					                                        </div>
					                                    </div>
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
														<div class="col-md-12 margin_row text-center">
															<br>
															<button type="submit" class="btn btn-success" id="save" name="save">Submit</button>
															<a href="<?= ROOT . 'hrms/hrms_sms_template_list' ?>" type="button" class="btn btn-danger">Cancel</a>
														</div>
													</div>
												</div>
											</section>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<section class="panel">
												<div class="panel-body">
									                
												</div>
											</section>
										</div>
									</div>
									<input type='hidden' name='mode' id='mode' value='<?= $mode ?>' />
									<input type='hidden' name='eid' id='eid' value='<?= $rel['id'] ?>' />
									<input type="hidden" name="row_cnt" id="row_cnt" value="<?= ($mode == 'Edit') ? $ecount : '0' ?>">
							</form>
					</div>
			
		</section>
	</section>
	<?php include_once('../../include/footer.php'); ?>
	</section>
	<?php include_once('../../include/include_js_file.php'); ?>
	<script src="<?= ROOT . HRMS_ROOT ?>js/app/hrms_sms_template.js?<?= time() ?>"></script>
	<script>
		$('#selectAll').click(function(e){
		    var table= $(e.target).closest('table');
		    $('td input:checkbox',table).prop('checked',this.checked);
		});
		$(".select2").select2({
			width: '100%'
		});
		CKEDITOR.replace( 'sms_template_body', {
			enterMode: CKEDITOR.ENTER_BR,
			height: 400
		});
	</script>
</body>
</html>