<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	TASK_SLUG_CREATE,
	TASK_SLUG_EDIT,
]);
$companyConfiguration=getCompanyConfiguration($dbcon);
$crm_user_type = $companyConfiguration['crm_user_type'] ? $companyConfiguration['crm_user_type'] : $crm_user_type;
$mode="Add";
$infopage = pathinfo( __FILE__ );
$_SESSION['page']= 'crm/'.$infopage['filename'];
$form="Task Transfer";
$back_link = $_SERVER['HTTP_REFERER'];
	?>
	<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Task Transfer</title>
		<?php include_once('../../include/include_css_file.php');?>
	</head>
	<body>
		<section id="container">
			<?php include_once('../../include/include_top_menu.php');?>
			<?php include_once('../../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<!--breadcrumbs start -->
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode .' '.$form?></h3>
									<!--<div class="text-center">Owner : <strong><?=$user_name?></strong></div>-->
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.CRM_ROOT.'task_transfer_list'?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
							<!--breadcrumbs end -->
						</div>	
					</div>
					<!--state overview start-->
					<div class="row">			
						<div class="col-md-12">
							<section class="panel">
								<header class="panel-heading">
									<?=$mode.' '.$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="task_add" action="javascript:;" method="post" name="task_add">
										<div class="row">
											<div class="clearfix"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-2 control-label"><strong>Old User *</strong></label>
													<div class="col-md-6"> 
														 <select class="select2" id="old_user_id" name="old_user_id" required >
                                                            <?=get_assign_users($dbcon, $assign_user_inq_ids, " and user_type in(".$crm_user_type.")");?>
                                                        </select>
													</div>
												</div>	
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-4 control-label"><strong>New user *</strong></label>
													<div class="col-md-6"> 
														 <select class="select2" id="new_user_id" name="new_user_id" required >
                                                            <?=get_assign_users($dbcon, $assign_user_inq_ids, " and user_type in(".$crm_user_type.")");?>
                                                        </select>
													</div>
												</div>	
											</div>
											<div class="col-md-12 text-center">
												<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
												<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>	
											</div>	
										</div>
											<input type='hidden' name='back_link' id='back_link' value='<?=$back_link?>' />
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
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
		<script src="<?=ROOT.CRM_ROOT?>js/app/task_transfer.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
		</script>
	</body>
</html>
