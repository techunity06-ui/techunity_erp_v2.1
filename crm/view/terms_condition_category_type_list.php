<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$infopage = pathinfo( __FILE__ );
$_SESSION['page']='crm/'.$infopage['filename']; 
$form="Terms & Condition Category Type";
$branch_id = $_SESSION['branch_id'];
	//check permission for party industry add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_READ,
	CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_CREATE
]);

if(!in_array(CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_READ,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>TERMS CATEGORY</title>
	<?php include_once($include.'include_css_file.php');?>
</head>
<body>
	<section id="container" >
		<?php include_once($include.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3>New <?=$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.CRM_ROOT.'crm_master'?>">CRM Masters</a></li>
									<li class="active"><?=$form?></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--unit overview start-->
				<?php include_once($include.'country_unit_city.php');?>
				<div class="row">
					<?php if(in_array(CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_CREATE,$bulkAccessArray)){ ?>
						<div class="col-sm-3">
							<section class="panel">
								<header class="panel-heading">
									New <?=$form?>
								</header>	
								<div class="panel-body">
									<form role="form" id="terms_condition_category_type_add" action="javascript:;" method="post" name="terms_condition_category_type_add">
										<?php if($branch_id=='0'){ ?>
											<div class="form-group">
												<label>Branch *</label>
												<?php
												$str='';
												$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
												$rs_dispatch=$dbcon->query($query);	
												?>
												<select class="select2" name="branch_id" id="abranch_id" required >
													<option value="">Select Branch</option>
													<option value="10000">All Branch</option>
													<?php 
													while($rel=mysqli_fetch_assoc($rs_dispatch))
													{	
														$sel=''; 
														if($rel['branch_id']==$branchid){
															$sel ="selected='selected'";
														}
														$str .= '<option '.$sel.' value="'.$rel['branch_id'].'">'.$rel['branch_name'].'</option>';
													}
													echo $str;
													?>
												</select>
											</div>
										<?php } ?>
										<div class="form-group">
											<label>Category Name</label>
											<input type="text" class="form-control" name="terms_condition_category_name" id="terms_condition_category_name" />
										</div>
										<div class="form-group">
											<label class="control-label">Status</label>
											<select id="status" class="select2" name="status" required>
												<option selected disabled value="">SELECT STATUS</option>
												<option value="0">Active</option>
												<option value="1">InActive</option>
											</select>
										</div>
										<input type='hidden' name='mode' id='mode' value='add' />
										<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
										<button type="submit" class="btn btn-info">Submit</button>
									</form>

								</div>
							</section>
						</div>
					<?php } ?>
					<?php if(in_array(CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_CREATE,$bulkAccessArray)){ ?>
						<div class="col-sm-9">
						<?php }else{ ?>	
							<div class="col-sm-12">
							<?php } ?>		
							<section class="panel">
								<header class="panel-heading">
									Terms & Condition Category Type List
									<span class="tools pull-right">
										<a href="javascript:;" class="fa fa-chevron-down"></a>
										<a href="javascript:;" class="fa fa-times"></a>
									</span>
								</header>
								<div class="panel-body">
									<div class="adv-table">
										<table  class="display table table-bordered table-striped" id="dynamic-table">
											<div class="col-md-12">
												<div class="col-md-6">
													<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_table_terms_condition_category()','4','6'); ?>
												</div>
											</div>
											<thead>
												<tr>
													<th>Sr. NO.</th>
													<th>Name</th>
													<th>Status</th>
													<th class="hidden-phone">Action</th>					  
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
								</div>
							</section>
						</div>
					</div>
					
					<!--unit overview end-->
				</section>
			</section>
			<!--main content end-->
			<!--footer start-->
			<?php include_once($include.'footer.php');?>
			<!--footer end-->
		</section>
		<!-- Modal -->
		<div class="modal colored-header info" id="ModalEditTermsConditionCategory" role="dialog" data-keyboard="false" data-backdrop="static">
			<div class="modal-dialog custom-width">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>Edit Category</h3>
						
					</div>
					<div class="modal-body form">
						<form id="FormEditTermsConditionCategory" role="form" method="post" novalidate>
							<?php if($branch_id=='0'){ ?>
								<div class="form-group">
									<label>Branch *</label>
									<?php
									$str='';
									$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
									$rs_dispatch=$dbcon->query($query);	
									?>
									<select class="select2" name="e_branch_id" id="e_branch_id" required>
										<option value="">Select Branch</option>
										<option value="10000">All Branch</option>
										<?php $sel = '';
										while($row=mysqli_fetch_assoc($rs_dispatch))
										{	
											if($rel['branch_id']==$row['branch_id']){
											$sel = "selected";
											}
											$str .= '<option '.$sel.' value="'.$row['branch_id'].'">'.$row['branch_name'].'</option>';
										}
										echo $str;
										?>
									</select>
								</div>
							<?php } ?> 
							<div class="form-group">
								<label for="unitid">Category Name</label>
								<input type="text" class="form-control" name="edit_terms_condition_category_name" id="edit_terms_condition_category_name" />
							</div>
							<div class="form-group">
								<label class="control-label">Status</label>
								<select id="edit_status" class="select2" name="status" required>
									<option selected disabled value="">SELECT STATUS</option>
									<option value="0">Active</option>
									<option value="1">InActive</option>
								</select>
							</div>
						</div>
						<div class="modal-footer">
							<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
							<input type="hidden" name="edit_id" id="edit_id" value="" />
							<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
							<button class="btn btn-info btn-flat" type="submit">Update Category</button>
						</div>
					</form>
				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<!-- js placed at the end of the document so the pages load faster -->
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.CRM_ROOT?>js/app/terms_condition_category_type.js?<?=time()?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
		</script>
	</body>
	</html>
