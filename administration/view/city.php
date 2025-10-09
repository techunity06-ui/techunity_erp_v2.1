<?php 
	session_start();	
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_CITY_LIST,
        ADMINISTRATOR_CITY_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_CITY_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>CITY</title>
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
						  <h3>New City</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							  <li class="active">City List</li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		<?php include_once($include.'country_state_city.php');?>
		  <div class="row">
		  	<?php if(in_array(ADMINISTRATOR_CITY_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
					  <header class="panel-heading">
						  New City
						</header>	
						<div class="panel-body">
							<form role="form" id="city_add" action="javascript:;" method="post" name="city_add">
								 
								  <div class="form-group">
									  <label for="stateid">State*</label>
									  <select id="state_id" class="select2" name="state_id" required>
										<option selected disabled value="">SELECT STATE</option>
										<?php
											$query = $dbcon->query("SELECT `stateid`,`state_name` FROM `state_mst` WHERE `state_status` = 0 order by state_name ");
											while($r = $query -> fetch_assoc()) {
												echo '<option value="'.$r['stateid'].'">'.$r['state_name'].'</option>';
											}
										?>
									</select>
								  </div>
								  <div class="form-group">
									  <label for="catalog_name">City Name*</label>
									  <input type="text" class="form-control" id="city_name" name="city_name" placeholder="City Name" />
								  </div>							  
									<input type='hidden' name='mode' id='mode' value='add' />
								  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
								  <button type="submit" class="btn btn-info">Submit</button>
							  </form>

						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_CITY_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
			<section class="panel">
				  <header class="panel-heading">
					  City List
				 <span class="pull-right">
							<a href="<?=ROOT.ADMINISTRATION_ROOT.'state'?>" type="button" class="btn btn-success">State List</a> </span>	
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
			  	 
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>City Name</th>					  
					  <th>State Name</th>
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
		  
		  <!--state overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit City</h3>				
			</div>
			<div class="modal-body form">
			<form id="FormEditCity" role="form" method="post" novalidate>
						
				<div class="form-group">
					<label class="control-label">City Name*</label>
					<input type="text" name="city_name"  id="edit_city_name" class="form-control" required >
				</div>				
				<div class="form-group">
					<label class="control-label">State*</label>
					<select id="edit_stateid" class="select2" name="edit_stateid" required>
									<option selected disabled value="">SELECT STATE</option>
									<?php
										$query = $dbcon->query("SELECT `stateid`,`state_name` FROM `state_mst` WHERE `state_status` = 0 order by state_name ");
										while($r = $query -> fetch_assoc()) {
											echo '<option value="'.$r['stateid'].'">'.$r['state_name'].'</option>';
										}
									?>
								</select>
				</div>				
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update City</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/city_mst.js?<?=time()?>"></script>
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
