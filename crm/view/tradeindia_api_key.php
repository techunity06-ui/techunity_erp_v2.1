<?php 
	session_start();
	include('../include/urlfile.php');
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename']; 
	$form="Trade India API Configuration";
	$incPath = $path.'include/';
?>
<!DOCTYPE html>
<html lang="en">
	<head>
	</head>
		<title>TRADEINDIA API</title>
		<?php include_once($incPath.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
			<?php include_once($incPath.'include_top_menu.php');?>
			<?php include_once($incPath.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3>New <?=$form?></h3>
								</header>	
								<div class="">
								  <ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li class="active"><?=$form?> List</li>
								  </ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">
						<div class="col-sm-3">
							<section class="panel">
								<header class="panel-heading">
								  New <?=$form?>
								</header>	
								<div class="panel-body">
									<form role="form" id="cust_ind_mst_add" action="javascript:;" method="post" name="cust_ind_mst_add">
										<div class="form-group">
											<label><strong>User Id</strong></label>
											<input class="form-control" type='text' name='trade_india_user_id' id='trade_india_user_id' placeholder="User Id" value='' />
										</div>
										<div class="form-group">
											<label><strong>Profile Id</strong></label>
											<input class="form-control" type='text' name='trade_india_profile_id' id='trade_india_profile_id' placeholder="Profile Id" value='' />
										</div>
										<div class="form-group">
											<label><strong>API Key</strong></label>
											<input class="form-control" type='text' name='trad_india_api_key' id='trad_india_api_key' placeholder="API Key" value='' />
										</div>
										<div class="form-group">
											<label><strong>Source</strong></label>
											<select class="select2" id="source_id" name="source_id" >
												<?=get_refer_by($dbcon,$rel['rb_id']);?>
											</select>
										</div>
										<button type="submit" class="btn btn-success">Submit</button>
									</form>
								</div>
							</section>
						</div>
						<div class="col-sm-9">
							<section class="panel">
								<header class="panel-heading">
									<?=$form?> List
									 <span class="tools pull-right">
										<a href="javascript:;" class="fa fa-chevron-down"></a>
									 </span>
								</header>
								<div class="panel-body">
									<div class="adv-table">
										<table class="display table table-bordered table-striped" id="cust-ind-datatable">
											<thead>
												<tr>
													<th>Sr. NO.</th>
													<th>User Id</th>
													<th>Profile Id</th>
													<th>API Key</th>
													<th>Source</th>
													<th class="hidden-phone">Action</th>
												</tr>
											</thead>
											<tbody></tbody>
										</table>
									</div>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog custom-width">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Change <?=$form?></h3>
						
					</div>
					<form id="FormEditcust_ind" role="form" method="post" novalidate>
						<div class="modal-body form">
							<div class="form-group">
								<label for="e_ci_name"><strong>User Id</strong></label>
								<input class="form-control" type='text' name='edit_trade_india_user_id' id='edit_trade_india_user_id' value='' />
							</div>
							<div class="form-group">
								<label for="e_ci_name"><strong>Profile Id</strong></label>
								<input class="form-control" type='text' name='edit_trade_india_profile_id' id='edit_trade_india_profile_id' value='' />
							</div>
							<div class="form-group">
								<label for="e_ci_name"><strong>API Key</strong></label>
								<input class="form-control" type='text' name='edit_trad_india_api_key' id='edit_trad_india_api_key' value='' />
							</div>
							<div class="form-group">
								<label><strong>Source</strong></label>
								<select class="select2" id="edit_source_id" name="edit_source_id" >
									<?=get_refer_by($dbcon,$rel['rb_id']);?>
								</select>
							</div>							
						</div>
						<div class="modal-footer">
							<input type="hidden" name="edit_id" id="edit_id" value="" />
							<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
							<button class="btn btn-info btn-success" type="submit">Update</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php include_once($incPath.'include_js_file.php');?>   
		<script src="<?=ROOT.CRM_ROOT?>js/app/tradeindia_api_key.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
		</script>
	</body>
</html>