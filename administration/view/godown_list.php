<?php
session_start();
include('../include/urlfile.php');
$token = md5(rand(1000, 9999));
$_SESSION['token'] = $token;
$infopage = pathinfo(__FILE__);
$_SESSION['page'] = $infopage['filename'];
$form = 'Location';
$branch_id = $_SESSION['branch_id'];




if (isset($_REQUEST['id'])) {
	$sel_branch_id = $dbcon->real_escape_string($_REQUEST['id']);
}


//check permission for process type add
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ADMINISTRATOR_GODOWN_LIST,
	ADMINISTRATOR_GODOWN_CREATE
]);

if (!in_array(ADMINISTRATOR_GODOWN_LIST, $bulkAccessArray)) {
	header("Location: " . DOMAIN . "permission_access");
}
//echo $_SESSION['user_type'];
$companyConfiguration=getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>GODOWN LIST</title>
	<?php include_once($include . 'include_css_file.php'); ?>
</head>

<body>
	<section id="container">
		<?php include_once($include . 'include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once($include . 'left_menu.php'); ?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3>New <?= $form ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . 'masters_list' ?>"> Masters List</a></li>
									<li class="active"><?= $form ?> List</li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<!--unit overview start-->
				<div class="row">
					<!-- Code Hide by Sanat :: 10-08-2021  -->
					<?php /* if(in_array(ADMINISTRATOR_GODOWN_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
					  <header class="panel-heading">
						  New <?=$form ?> List
						</header>	
						<div class="panel-body">
							<form role="form" id="mspec_add" action="javascript:;" method="post" name="mspec_add">
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
									  <label>Godown Name*</label>
									  <input class="form-control" type='text' name='gd_name' id='gd_name' placeholder="Godown Name" value="" />
								    </div>
																
								  	<input type='hidden' name='mode' id='mode' value='add' />
									
								  <button type="submit" class="btn btn-info" style="margin-top: 10px;" >Submit</button>
							  </form>

						</div>
					</section>
				</div>
			<?php } */ ?>
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								Location List
								<span class="tools pull-right">
									<a href="javascript:;" class="fa fa-chevron-down"></a>

								</span>
							</header>
							<div class="col-md-12">
								<div class="col-md-6">

									<div class="form-group">
										<label class="col-md-4 control-label text-right">Branch * </label>
										<div class="col-md-8 col-xs-11">
											<select name="branch_id" class="select2 branch_id" onchange="reload_godown_datatable()" id="branch_id" <?php if($companyConfiguration['branch_wise_manage']=='0'){  echo "disabled"; } ?>>
												<?php
												if (isset($sel_branch_id)) {
													$branch = $sel_branch_id;
												} else if (isset($branch_id)) {
													$branch = $branch_id;
												} else {
													$branch =  '1000';
												}
												?>
												<?= getBranchBox_new($dbcon, $branch, 'all'); ?>
											</select>
										</div>
									</div>
									<?php //echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_godown_datatable()','4','6'); 
									?>
								</div>
							</div>
							<div class="col-md-12 text-right">
								<button type="button" class="btn btn-primary" onclick="show_add_location_form(0,'')"> Add Location</button>
								<span class="tools pull-right">
									<a href="javascript:;"><button onClick="exportCsv()" class="btn btn-success btn-flat" >Export Excel</button></a>	
								</span>
							</div>
							<div class="panel-body">

								<?php
								/*
							Code hide by sanat :: 19-08-2021
				 */

								/* <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <div class="col-md-12">
                        <div class="col-md-6">
                          <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_godown_datatable()','4','6'); ?>
                        </div>
                   </div>
				  <thead>
				  <tr>
						<th>Sr. NO.</th>
						<th>Godown Name</th>
						<th>Branch Name</th>
						<th class="hidden-phone">Action</th>					  
				  </tr>
				  </thead>
				  <tbody>
				  </tbody>
				  </table>
				  </div> */ ?>

								<div class="dd" id="nestable_list_3" class="nestable_list_3" style="margin-top: 75px;">

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
		<?php include_once($include . 'footer.php'); ?>
		<!--footer end-->
	</section>
	<!-- Modal -->
	<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Edit Location</h3>

				</div>
				<div class="modal-body form">
					<form id="FormEditunit" role="form" method="post" novalidate>

						<?php //if ($branch_id == '0') { ?>
						<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>

							<div class="form-group branch_row">
								<label>Branch *</label>
								<select class="branch_validate" name="branch_id" id="e_branch_id" required>
									<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
									<?= getBranchBox_new($dbcon, $branch, 'all'); ?>
								</select>
							</div>
						<?php } ?>

						<div class="form-group">
							<label for="unitid">Location Name*</label>
							<input type="text" class="form-control" name="e_gd_name" id="e_gd_name" placeholder="Location Name" />
						</div>

						<div class="form-group">
							<label>Address *</label>
							<textarea class="form-control" name='e_gd_address' id='e_gd_address' placeholder="Godown Address"></textarea>
						</div>

				</div>
				<div class="modal-footer">
					<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
					<input type="hidden" name="edit_id" id="edit_id" value="" />
					<input type='hidden' name='edit_p_gd_id' id='edit_p_gd_id' value='' />
					<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
					<button class="btn btn-info btn-flat" type="submit">Update</button>
				</div>
				</form>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->


	<!-- Modal -->
	<div class="modal colored-header info" id="ModalAddLocation" role="dialog" data-keyboard="false" data-backdrop="static">
		<div class="modal-dialog custom-width">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>Add Location</h3>

				</div>
				<div class="modal-body form">
					<form role="form" id="mspec_add" action="javascript:;" method="post" name="mspec_add">
						<?php //if ($branch_id == '0') { ?>
						<?php if($companyConfiguration['branch_wise_manage']=='1'){ ?>
							<div class="form-group" id="row_branch">
								<label>Branch *</label>
								<select class="branch_validate" name="branch_id" id="abranch_id" required>
									<option value="">Select Branch</option>
									<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
									<?= getBranchBox_new($dbcon, $branch); ?>
								</select>
							</div>
						<?php } ?>

						<div class="form-group">
							<label>Location Name*</label>
							<input class="form-control" type='text' name='gd_name' id='gd_name' placeholder="Location Name" value="" />
						</div>

						<div class="form-group">
							<label>Address *</label>
							<textarea class="form-control" name='gd_address' id='gd_address' placeholder="Godown Address"></textarea>
						</div>

						<input type='hidden' name='mode' id='mode' value='add' />
						<input type='hidden' name='p_gd_id' id='p_gd_id' value='0' />
						<button type="submit" class="btn btn-info" style="margin-top: 10px;">Submit</button>

					</form>


				</div><!-- /.modal-content -->
			</div><!-- /.modal-dialog -->
		</div><!-- /.modal -->


		<!-- js placed at the end of the document so the pages load faster -->
		<?php include_once($include . 'include_js_file.php'); ?>
		<script src="<?= ROOT . ADMINISTRATION_ROOT ?>js/app/godown_mst.js?<?= time() ?>"></script>
		<script src="<?= ROOT ?>assets/nestable/jquery.nestable.js"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$(".branch_validate").select2({
				width: '100%'
			}).on('change', function() {
				$(this).valid();
			});
		</script>
</body>

</html>