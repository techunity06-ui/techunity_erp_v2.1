<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
</head>
<body>
  <section id="container" >
      <?php include_once('../include/include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once('../include/left_menu.php');?>
      <!--sidebar end-->
      <!--main content start-->
      <section id="main-content">
          <section class="wrapper">
			<div class="row">
			  <div class="col-lg-12">
				  <!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
						  <h3>New Menu</h3>
						</header>	
						<div class="">
							<ul class="breadcrumb">
								<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							</ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		  <div class="row">
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New Menu
					</header>	
					<div class="panel-body">
						<form role="form" id="menu_add" action="javascript:;" method="post" name="menu_add">
							  <div class="form-group">
								  <label for="vendor_name">Menu Name</label>
								  <input type="text" class="form-control" id="menu_name" name="menu_name" placeholder="Menu Name">
							  </div>							  
								<div class="form-group">
								  <label for="vendor_name">Page Name</label>
								  <input type="text" class="form-control" id="page_name" name="page_name" placeholder="Menu Name">
							  </div>							  
								<div class="form-group">
								  <label for="vendor_name">Fa Icon</label>
								  <input type="text" class="form-control" id="fa_icon" name="fa_icon" placeholder="Fa Icon">
							  </div>							  
								<div class="form-group">
								  <label for="vendor_name">Menu Order </label>
								  <input type="number" min='0' class="form-control" id="order" name="order" placeholder="Menu Order">
							  </div>
								<div class="form-group">
									<label>
										<input type="checkbox" id="incl_per" name="incl_per" value="1" style="width: 16px;height: 16px;"> <strong>Include Permission</strong>
									</label>
								</div>
								<div class="form-group">
									<label>
										<input type="checkbox" id="report_status" name="report_status" value="1" style="width: 16px;height: 16px;"> <strong>Report Status</strong>
									</label>
								</div>							  
								<input type='hidden' name='mode' id='mode' value='add' />
								<input type='hidden' name='pid' id='pid' value='0' />	
								<input type='hidden' name='ppname' id='ppname' value='' />
												  
							  <button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
			<div class="col-sm-9">
			<section class="panel">
				  <header class="panel-heading">
				 
				 <label id="pname"></label> Menu List 
 				 <span class="tools pull-right" >
					
					
					<button type="submit" class="btn btn-primary" onClick="pid_home(0);">Home</button>
					<button type="submit" id="return" name="return" class="btn btn-success" value="" onClick="pid_return(this.value);"> Return</button>
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					
					
				 </span>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  <thead>
				  <tr>
					  <th>Sr. NO.</th>
					  <th>Menu Name</th>	
						<th>Sort Order</th>					  
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
		  
		  <!--state overview end-->
          </section>
      </section>
      <!--main content end-->
      <!--footer start-->
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit Menu</h3>
				
			</div>
			<div class="modal-body form">
			<form id="FormEditMenu" role="form" method="post" novalidate>				
				<div class="form-group">
					<label class="control-label">Menu Name</label>
					<input type="text" name="menu_name"  id="edit_menu_name" class="form-control" required>
				</div>				
				<div class="form-group">
					<label class="control-label">Page Name</label>
					<input type="text" name="page_name"  id="edit_page_name" class="form-control">
				</div>
				<div class="form-group">
					 <label for="vendor_name">Fa Icon</label>
						<input type="text" class="form-control" id="edit_fa_icon" name="fa_icon" placeholder="Fa Icon">
				 </div>
				<div class="form-group">
					<label for="vendor_name">Menu Order </label>
					<input type="number" min='0'  class="form-control" id="edit_order" name="order" placeholder="Menu Order">
				</div>							  
				<div class="form-group">
					<label>
						<input type="checkbox" id="edit_incl_per" name="edit_incl_per" value="1" style="width: 16px;height: 16px;"> <strong>Include Permission</strong>
					</label>
				</div>
				<div class="form-group">
					<label>
						<input type="checkbox" id="edit_report_status" name="edit_report_status" value="1" style="width: 16px;height: 16px;"> <strong>Report Status</strong>
					</label>
				</div>				
							
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update Menu</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
	<script src="<?=ROOT?>js/app/menu_mst.js?<?=date('dmy')?>"></script>

  </body>
</html>
