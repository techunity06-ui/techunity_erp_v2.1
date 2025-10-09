<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");
	$form="Branch";
	$countryid="101";
	$stateid="1";
	$cityid="1";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
<section id="container">
  <?php include_once('../../include/include_top_menu.php');?>
    <!--sidebar start-->
      <?php include_once('../../include/left_menu.php');?>
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
							  <li><a href="<?=ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?=$form?> List</li>
						  </ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
		  <div class="row">
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
					<div class="panel-body">
						<form role="form" id="branch_add" action="javascript:;" method="post" name="branch_add">
							<div class="form-group">
								<label>Branch Name *</label>
								<input class="form-control" type="text" name="branch_name" id="branch_name" placeholder="Branch Name" value="" />
							</div>	
							<div class="form-group">
								<label>Address</label>
								<textarea id="branch_address" name="branch_address" class="form-control" placeholder="Enter Address" style="resize:both;"></textarea> 
							</div>	
							<div class="form-group">
								<label>Select Country *</label>
								<select class="select2" name="countryid" id="countryid" onChange="load_state(this.value,'stateid','')">
									<?=get_country($dbcon,$countryid)?>				
								</select>
							</div>
							<div class="form-group">
								<label>Select Zone *</label>
								<select class="select2" name="zoneid" id="zoneid">
									<option value="">Select Zone</option>	
									<?=get_zone($dbcon,$rel['zoneid'])?>				
								</select>
							</div>
							<div class="form-group">
								<label>Select State *</label>
								<select class="select2" name="stateid" id="stateid" onChange="load_city(this.value,'cityid','')">
									<option value="">Select State</option>	
									<?//=getstate($dbcon,$rel['stateid'])?>				
								</select>
							</div>
							<div class="form-group">
								<label>Select City *</label>
								<select class="select2" name="cityid" id="cityid">
									<option value="">Select City</option>	
								</select>
							</div>	
							<div class="form-group">
								<label>Pin Code</label>
								<input type="text" class="form-control" placeholder="Branch Pincode" name="branch_pincode" id="branch_pincode" value=""  />
							</div>
							<div class="form-group">
								<label for="branch_status">Status*</label>
								<select class="select2" id="branch_status" name="branch_status">
									<?php echo getStatusOptions($rel['branch_status']); ?>
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
					<table  class="display table table-bordered table-striped" id="branch-table">
						<thead>
							<tr>
								<th>Sr. NO.</th>
								<th>Branch Name</th>
								<th>Address</th>
								<th>City</th>
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
	<?php include_once('../../include/add_city.php');?>
	<?php include_once('../../include/add_state.php');?>
	<?php include_once('../../include/footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditBranch" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
				<h3>Edit <?=$form?></h3>
			</div>
			<div class="modal-body form">
			<form id="FormEditBranch" role="form" method="post" novalidate>
				<div class="form-group">
					<label for="edit_branch_name">Branch Name *</label>
					<input class="form-control" type="text" name="edit_branch_name" id="edit_branch_name" value="" />
				</div>  
				<div class="form-group">
					<label>Address</label>
					<textarea id="edit_branch_address" name="edit_branch_address" class="form-control" placeholder="Enter Address"></textarea> 
				</div>	
				<div class="form-group">
					<label>Select Country *</label>
					<select class="select2" name="edit_countryid" id="edit_countryid" onChange="load_state(this.value,'edit_stateid','')">
						<?=get_country($dbcon,$countryid)?>				
					</select>
				</div>
				<div class="form-group">
					<label>Select Zone *</label>
					<select class="select2" name="edit_zoneid" id="edit_zoneid">
								
					</select>
				</div>
				<div class="form-group">
					<label>Select State *</label>
					<select class="select2" name="edit_stateid" id="edit_stateid" onChange="load_city(this.value,'edit_cityid','')">
						<option value="">Select State</option>	
						<?//=getstate($dbcon,$rel['stateid'])?>				
					</select>
				</div>
				<div class="form-group">
					<label>Select City *</label>
					<select class="select2" name="edit_cityid" id="edit_cityid">
						<option value="">Select City</option>	
					</select>
				</div>	
				<div class="form-group">
					<label>Pin Code</label>
					<input type="text" class="form-control" placeholder="Branch Pincode" name="edit_branch_pincode" id="edit_branch_pincode" value=""/>
				</div>
				<div class="form-group">
					<label for="branch_status">Status*</label>
					<select class="select2" id="edit_branch_status" name="edit_branch_status">
						<?php echo getStatusOptions($rel['branch_status']); ?>
					</select>	
				</div>							
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php');?>
	<script src="<?=ROOT . HRMS_ROOT ?>js/app/branch_mst.js?<?=time()?>"></script>
	<script src="<?=ROOT?>js/app/state_mst.js?<?=time()?>"></script>
	<script src="<?=ROOT?>js/app/city_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
<?php
echo "<script>load_state(".$countryid.",'stateid',".$stateid.")</script>";
echo "<script>load_city(".$stateid.",'cityid',".$cityid.")</script>"; 
?>
</body>
</html>
