<?php 
	session_start();
	include_once("../../config/config.php");
	include_once("../../config/session.php");
	include_once("../../include/common_functions.php");
	include_once("../../include/hrms_common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page'] = HRMS_ROOT . $infopage['filename'];
	$title = 'Employee Group'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
  <section id="container" >
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
						  <h3>New </h3>
						</header>	
						<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT . HRMS_ROOT . 'hr_dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li class="active"><?php echo $title; ?></li>
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--unit overview start-->
			<?php include_once('../../include/country_unit_city.php');?>
		  <div class="row">
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New Group
					</header>	
					<div class="panel-body">
						<form role="form" id="group_add" action="javascript:;" method="post" name="group_add">
								<div class="form-group">
								  <label>Sub Group Name</label>
								 <input class="form-control" type='text' name='g_name' id='g_name' value='' />
							    </div>
								
								<div class="form-group">
								  <label>Select Group</label>
								  <select class="select2" name="g_parent" id="g_parent" onchange="get_form_type(this.value,'g_form')">
									<?=get_all_group($dbcon,$id);?>
								  </select>
							    </div>
								
								<div class="form-group">
								  <label>Opening  Balance</label>
								 <input class="form-control" type='text' name='g_opening' id='g_opening' value='' />
							    </div>

							    <div class="form-group">
									<label for="g_status">Status*</label>
									<select class="select2" id="g_status" name="g_status">
										<?php echo getStatusOptions($rel['g_status']); ?>
									</select>	
								</div>
								
								<div class="form-group">
									<input class="form-control" type='hidden' name='g_form' id='g_form' value='' />
							    </div>
								
							  	<input type='hidden' name='mode' id='mode' value='add' />
							  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							  <button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
			<div class="col-sm-9">
			<section class="panel">
				  <header class="panel-heading">
					  group List
				 <span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					<a href="javascript:;" class="fa fa-times"></a>
				 </span>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
						<th>Sr. NO.</th>
						<th>group Name</th>
						<th>Parent group</th>
						<th>Status</th>
						<th class="hidden-phone">Action</th>					  
				  </tr>
				  </thead>
				  <tbody>
				  </tbody>
				 <!-- <tfoot>
				  <tr>
					  <th>Rendering engine</th>
					  <th>Browser</th>
					  <th>Platform(s)</th>
					  <th class="hidden-phone">Engine version</th>
					  <th class="hidden-phone">CSS grade</th>
				  </tr>
				  </tfoot>-->
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
	<?php include_once('../../include/footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit group</h3>
				
			</div>
			<div class="modal-body form">
			<form id="FormEditunit" role="form" method="post" novalidate>
			
				<div class="form-group">
				  <label for="unitid">group Name</label>
				   <input class="form-control" type='text' name='e_g_name' id='e_g_name' value='' />
				</div>	

				<div class="form-group">
				   <label for="unitid">Parent group</label>
				   <select class="form-control" name="e_g_parent" id="e_g_parent" onchange="get_form_type(this.value,'e_g_form')">
					 
				   </select>
				</div>

				<div class="form-group">
				   <label for="unitid">Opening Balance</label>
				   <input type="text" class="form-control" name="e_g_opening" id="e_g_opening" />
				</div>

				<div class="form-group">
					<label for="g_status">Status*</label>
					<select class="select2" id="e_g_status" name="e_g_status">
						<?php echo getStatusOptions($rel['g_status']); ?>
					</select>	
				</div>	

				<div class="form-group">
				   <input type="hidden" class="form-control" name="e_g_form" id="e_g_form" />
				</div>					
								
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<input type="hidden" name="edit_pid" id="edit_pid" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update group</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../../include/include_js_file.php');?>   
	<script src="<?=ROOT . HRMS_ROOT ?>js/app/group_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
		width: '100%'
	});
</script>
  </body>
</html>
