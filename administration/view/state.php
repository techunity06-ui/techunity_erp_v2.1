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
    	ADMINISTRATOR_STATE_LIST,
        ADMINISTRATOR_STATE_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_STATE_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>STATE</title>
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
						  <h3>New State</h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
							  <li class="active">State List</li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
			<?php include_once($include.'country_state_city.php');?>
		  <div class="row">
		  	<?php if(in_array(ADMINISTRATOR_STATE_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
					  <header class="panel-heading">
						  New State
						</header>	
						<div class="panel-body">
							<form role="form" id="state_add" action="javascript:;" method="post" name="state_add">
								
									<div class="form-group">
									  <label for="stateid">Country*</label>
									  <select id="countryid" class="select2" name="countryid" required>
										<?=get_country($dbcon,'101')?>
									</select>
									</div>	
								  <div class="form-group">
									  <label for="vendor_name">State Name*</label>
									  <input type="text" class="form-control" id="state_name" name="state_name" placeholder=" State Name">
								  </div>
									<div class="form-group">
									  <label for="vendor_name">GST Code</label>
									  <input type="text" class="form-control digitOnly" id="gst_state_code" name="gst_state_code" placeholder="GST Code"  minlength="2" maxlength="2">
								  </div>							  
									<input type='hidden' name='mode' id='mode' value='add' />
								  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
								  <button type="submit" class="btn btn-info">Submit</button>
							  </form>

						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_STATE_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>
			<section class="panel">
				  <header class="panel-heading">
					  State List
				 <span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					
				 </span>
				  </header>
				  <div class="panel-body">

				  	<div class="row">
				  		<div class="col-md-6">
				  		<div class="form-group">
									  <label for="stateid">Country*</label>
									  <select id="countryid_search" class="select2" name="countryid_search" required onchange="load_state_mst_datatable()">
										<?=get_country($dbcon,'101')?>
									</select>
									</div>	
							</div>
				  	</div>

				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
			  	
				  <thead>
				  <tr>
						<th>Sr. NO.</th>
						<th>Country Name</th>
						<th>State Name</th>
						<th>GST Code</th>			  
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
				<h3>Edit State</h3>
				
			</div>
			<div class="modal-body form">
			<form id="FormEditState" role="form" method="post" novalidate>
				
				<div class="form-group">
					<label for="stateid">Country*</label>
					<select id="edit_countryid" class="select2" name="countryid" required>
						<?=get_country($dbcon,'');?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">State Name*</label>
					<input type="text" name="state_name"  id="edit_state_name" class="form-control" required>
				</div>
				<div class="form-group">
					  <label for="vendor_name">GST Code</label>
					  <input type="text" class="form-control digitOnly" id="edit_gst_state_code" name="gst_state_code" placeholder="GST Code" minlength="2" maxlength="2">
				  </div>					
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update State</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/state_mst.js?<?=time();?>"></script>
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
