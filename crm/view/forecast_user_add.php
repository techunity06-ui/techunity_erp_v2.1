<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';

$form="Forecast User";
	//check permission for forcast by user pro list
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FORECAST_USER_SLUG_ADD,
	FORECAST_USER_SLUG_EDIT
]);

if(strpos($_SERVER['REQUEST_URI'], "forecast_user_edit")==true) {
	$mode="Edit";
	$cmode = '';
	if(!in_array(FORECAST_USER_SLUG_EDIT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	
	$forecast_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_forecast_user where forecast_user_id=$forecast_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	$forecast_date = date("d-m-Y", strtotime($rel['forecast_date']));
	$forecast_no = $rel['forecast_no'];
	
}else if(strpos($_SERVER['REQUEST_URI'], "forecast_user_copy")==true){
	$mode="Add";
	$cmode = 'Add';
	if(!in_array(FORECAST_USER_SLUG_ADD,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$forecast_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_forecast_user where forecast_user_id=$forecast_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$forecast_date = date("d-m-Y");
	$forecast_no = load_common_no($dbcon,49);
}
else {
	$mode="Add";
	$cmode = '';
	if(!in_array(FORECAST_USER_SLUG_ADD,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	$forecast_no = load_common_no($dbcon,49);
	$forecast_date = date("d-m-Y");
}
$company_data = get_company_data($dbcon,$_SESSION['company_id']);
$companyConfiguration=getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>FORECAST USER</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
	<section id="container">
		<?php include_once($incPath.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($incPath.'left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3><?=$mode.' '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li class="active"><a href="<?=ROOT.CRM_ROOT.'forecast_user_list'?>"><?=$form?> List </a></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--Customer overview start-->

				<div class="row">
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								New <?=$form?> 
								<span class="tools pull-right">
									<a href="javascript:;" class="fa fa-chevron-down"></a>
								</span>
							</header>	
							<div class="panel-body">
								<form class="form-horizontal" role="form" id="forecast_user_add" action="javascript:;" method="post" name="forecast_add">

									<div class="col-md-12">
										<div class="col-md-6">
											<div class="form-group">
												<label for="f_by_id" class="col-md-4 control-label">Forecast No</label>
												<div class="col-md-8">
													<input type="text" class="form-control" id="forecast_no" name="forecast_no" value="<?=$forecast_no;?>" readonly>
												</div>
											</div>							 
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="f_year" class="col-md-4 control-label">Forecast Date*</label>
												<div class="col-md-8">
													<input id="forecast_date" name="forecast_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$forecast_date?>" placeholder="Forecast Date" <?php echo ($mode=="Edit")?"disabled":""?>>
												</div>
											</div>							 
										</div>
										<div class="clearfix"></div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="f_year" class="col-md-4 control-label">Branch*</label>
												<div class="col-md-8">
													<select class="select2" id="branch_id" name="branch_id" onchange="get_branchwise_user(this.value)" required>
														<?=get_branch_name_company($dbcon, $rel['branch_id'], ''); ?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="financial_year_id" class="col-md-4 control-label">Financial Year *</label>
												<div class="col-md-8">
													<select class="select2" id="financial_year_id" name="financial_year_id" onchange="load_f_period();" <?=(($mode=='Edit' || $cmode=='Add') ? 'disabled' : '')?>>
														<?=get_financial_year_list($dbcon,$rel['financial_year_id'])?>
													</select>
												</div>
											</div>
										</div>
										<div class="clearfix"></div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="f_target_period" class="col-md-4 control-label">Forecast Period</label>
												<div class="col-md-8">
													<select class="select2" id="forecast_type" name="forecast_type" onchange="load_f_period();" <?=(($mode=='Edit' || $cmode=='Add') ? 'disabled' : '')?>>
														<?=get_for_target_p($dbcon,$rel['forecast_type']);?>
													</select>
												</div>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="f_target_period" class="col-md-4 control-label">User*</label>
												<div class="col-md-8">
													<select class="select2" name="f_user_id" id="f_user_id" required>
														<option value="">Select User</option>
														<?php //=get_users_typewise($dbcon, '', '')?>
													</select>
													<input type="hidden" name="fore_user_id" id="fore_user_id" value="<?=$rel['f_user_id']?>">
												</div>
											</div>
										</div>
										<div class="clearfix"></div>
										<hr/>
										<div class="col-md-12">
											<div class="card">
												<ul class="nav nav-tabs" id="my_tab_id" role="tablist">
													<li role="presentation" id="tab1" class="active"><a href="#product-details" aria-controls="product-details" role="tab" data-toggle="tab">Target Details</a></li>
												</ul>
												<!-- Tab panes -->
												<div class="tab-content">
													<!-- Remaks Tab Start -->
													<div role="tabpanel" class="tab-pane active" id="product-details">
														<div class="col-md-12">
															<div class="form-group" style="margin-top:20px;overflow-x:scroll;">
																<table class="display table table-bordered table-striped">
																	<thead>
																		<tr>
																			<th>Month</th>
																			<?php if($companyConfiguration['forecast_base']==3){ ?>
																				<th>Product Name</th>
																			<?php }
																			if($companyConfiguration['forecast_base']==2){ ?>
																				<th>Product Category</th>
																			<?php } ?>
																			<th>Amount</th>
																			<th>Quantity</th>
																			<th>Action</th>
																		</tr>
																	</thead>
																	<tbody>
																		<tr>
																			<td>
																				<select class="select2 fmonth" name="forecast_month" id="forecast_month">
																					<?=get_for_period($dbcon,'1','1','');?>
																				</select>
																			</td>
																			<?php if($companyConfiguration['forecast_base']==3){ ?>
																				<td>
																					<select class="select2" name="f_product" id="f_product">
																						<?=getproduct_typewise($dbcon, '', '')?>
																					</select>
																				</td>
																			<?php } ?>
																			<?php if($companyConfiguration['forecast_base']==2){ ?>
																				<td>
																					<select class="select2" name="f_product" id="f_product">
																						<?=get_all_category($dbcon, '', '')?>
																					</select>
																				</td>
																			<?php } ?>
																			<td>
																				<input type="number" min="0" class="form-control" id="target_amount" name="target_amount" value="">
																			</td>
																			<td>
																				<input type="number" min="0" class="form-control" id="target_qty" name="target_qty" value="">
																			</td>
																			<td>
																				<input type="hidden" id="edit_id" name="edit_id" value="">
																				<button type="button" class="btn btn-primary" id="forecast_trn_btn" onclick="add_field()">Add</button>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</div>
														</div>
														<hr/>
														<div class="col-md-12 show_forecast_trndata"></div>
													</div>
												</div>
											</div>
										</div>
										<hr/>	
										<div class="col-md-6">
											<div class="form-group">
												<label for="f_target_period" class="col-md-4 control-label">Remark</label>
												<div class="col-md-8">
													<textarea class="form-control" name="remark" id="remark"><?=$rel['remark']?></textarea>
												</div>
											</div>							 
										</div>
										<div class="clearfix"></div>
										<div class="col-md-12 text-center">					  
											<input type='hidden' name='forecast_base' id='forecast_base' value='<?=$companyConfiguration['forecast_base']?>' />				  
											<input type='hidden' name='eid' id='eid' value='<?=(($mode=='Edit') ? $rel['forecast_user_id'] : '')?>' />				  
											<input type='hidden' name='mode' id='mode' value='<?php echo $mode; ?>' />				  
											<input type='hidden' name='cmode' id='cmode' value='<?php echo $cmode; ?>' />				  
											<button type="submit" id="submit_btn" class="btn btn-shadow btn-success">Submit</button>
											<a class="btn btn-shadow btn-danger" href="<?=ROOT.CRM_ROOT.'forecast_user_list'?>">Cancel</a>
										</div>
									</div>
								</form>

							</div>
						</section>
					</div>
				</div>

				<!--Customer overview end-->
			</section>
		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once($incPath.'footer.php');?>
		<!--footer end-->
	</section>
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($incPath.'include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/forecast_user.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose: true
	});
	<?php if($mode=='Edit' || $cmode=='Add'){ ?>
		get_branchwise_user(<?=$rel['branch_id']?>);
		//load_f_period(<?=$rel['forecast_type']?>);
	<?php }
	if($cmode=='Add'){ ?>
		copy_forecast(<?=$rel['forecast_user_id']?>);
	<?php } ?>
	load_f_period();
	show_data();
</script>
</body>
</html>