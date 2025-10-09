<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Hrms Appraisal Template";
	$companyID = $_SESSION['company_id'];
	$usertype=$_SESSION['user_type'];
	if(strpos($_SERVER[REQUEST_URI], "hrms_appraisal_template_edit")==false)
	{
		$mode="Add";
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	}
	else
	{
		$mode="Edit";
		$hrmsappraisalid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select hrmsappraisallist.* from hrms_appraisal_template as hrmsappraisallist 
				left join tbl_company as com on com.company_id = hrmsappraisallist.company_id
				left join hrms_appraisal_goals as hrmsappraisalgoals on hrmsappraisalgoals.id = hrmsappraisalgoals.hrms_appraisal_template_id
		 		where `hrmsappraisallist`.`id` = $hrmsappraisalid and `hrmsappraisallist`.`company_id` = $companyID";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<style>
		.check_box_class{ position: absolute !important; overflow: visible !important; }	
		.checkbox_label{ margin-left: 12px; }
		.cke_chrome{ border: 1px solid #d1d1d1 !important; }	
	</style>
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
										<li><a href="<?=ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li ><a href="<?=ROOT . HRMS_ROOT . 'hrms_appraisal_template_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="hrms_appraisal_template_add" action="javascript:;" method="post" name="hrms_appraisal_template_add">
										<div class="">
											<div class="col-md-12 margin_row">
												<?php if($mode == "Edit"){ ?>
								 					<div class="col-md-7">
														  <div class="form-group">
														  		<label class="col-md-4 control-label">Series</label>
														  		<div class="col-md-8 col-xs-11">
														  			<input type="text" class="form-control" id="series_edit_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" readonly />
														  			<input type="hidden" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php if($mode=='Edit'){ echo $rel['series_id'];} ?>" />
																</div>
														  </div>							 
													 </div>
								 				<?php } else { ?>
								 					<div class="col-md-7">
														  <div class="form-group">
														  		<label class="col-md-4 control-label">Series</label>
														  		<?php
														  		$series_id = '';
														  		$query = $dbcon->query("SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 and `invoice_type`='HRMS APPRAISAL TEMPLATE' and company_id = $companyID and `type_id` = '16' order by invoicetype_id ");
																		while ($r = $query->fetch_assoc()) {
																			$series_id = $r['format_value'] . $r['taxinvoice_start'] . $r['end_format_value'];
																		}
																?>
														  		<div class="col-md-8 col-xs-11">
														  			<input type="text" class="form-control" id="series_id" name="series_id" placeholder="series_id" value="<?php echo $series_id; ?>" readonly />
																</div>
														  </div>							 
													 </div>	
												 <?php } ?>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-7">
													<div class="form-group">
														<label for="appraisal_template_title" class="col-md-4 control-label">Appraisal Template Title*</label>
														<div class="col-md-8 col-xs-11">
															<input type="text"  name="appraisal_template_title" title="Enter Appraisal Template Title" placeholder="Appraisal Template Title" id="appraisal_template_title" class="form-control" value="<?php if($mode=='Edit'){ echo $rel['appraisal_template_title'];} ?>"/>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 margin_row">
												<div class="col-md-9">
													<div class="form-group">
														<label for="appraisal_template_desc" class="col-md-3 control-label">Appraisal Description</label>
														<div class="col-md-7 col-xs-11">
															<textarea style="border: 1px solid #ccc;" id="appraisal_template_desc" name="appraisal_template_desc" placeholder="Enter Description" ><?php if($mode=='Edit') { echo $rel['appraisal_template_desc']; } ?></textarea>
														</div>
													</div>
												</div>
											</div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
											<h4>Goals </h4>
											<h6>Appraisal Goals</h6>
											<div class="col-md-12">
												<div class="form-group">
													<table cellspacing="10" style="border-collapse:inherit; " id="hrms_appraisal_goals" class="display table table12 table-striped table-bordered">
														<tr id="field">
															<th width="20%" class="text-center">KRA</th>
															<th width="20%" class="text-center">Weightage (%)</th>
															<th width="8%" class="text-center">Action</th>
														</tr>
														<input type="hidden" value="1" name="fieldcnt" id="fieldcnt"/>
														<tr id="field1">
															<td data-label="KRA" style="vertical-align:top;">
																<input type="text"  name="key_resource_planning_name" title="Enter KRA" placeholder="KRA" id="key_resource_planning_name" class="form-control" />
															</td>
															<td data-label="Weightage (%)" style="vertical-align:top;">
																<input type="text"  name="key_resource_planning_weightage" title="Enter Weightage (%)" placeholder="Weightage (%)" id="key_resource_planning_weightage" class="form-control" />
															</td>
															<td style="vertical-align:top;">
																<input type="button"  name="addappraisalgoalsrow" id="addappraisalgoalsrow" onClick="return add_appraisal_goals_field();"  class="btn btn-primary" value="Add"/>
															</td>
															<input type='hidden' name='edit_id' id='edit_id' value='' />
														</tr>
													</table>			
												</div>
											</div>
											<div id="hrms_appraisal_goals_data"></div>
											<hr style="margin-top: 0px; margin-bottom:0px; border: 1px solid #eee"><br>
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
													<a href="<?=ROOT . HRMS_ROOT . 'hrms_appraisal_template_list'?>" type="button" class="btn btn-danger">Cancel</a>
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
			<script src="<?=ROOT . HRMS_ROOT ?>js/app/hrms_appraisal_template.js?<?= time() ?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			CKEDITOR.replace( 'appraisal_template_desc', {
				enterMode: CKEDITOR.ENTER_BR,
				height: 200,
			});
			</script>
		<?php 
			echo "<script>show_appraisal_goals_data() </script>";
		?>
	</body>
</html>
