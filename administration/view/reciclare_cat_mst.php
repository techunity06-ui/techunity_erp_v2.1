<?php 
	session_start();
	
	include('../include/urlfile.php');	
	$form="Category";
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename']; 
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_CATEGORY_LIST,
        ADMINISTRATOR_CATEGORY_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_CATEGORY_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>CATEGORY LIST</title>
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
							  <li><a href="<?=ROOT.'masters_list'?>"> Masters List</a></li>
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
		  	<?php if(in_array(ADMINISTRATOR_CATEGORY_CREATE,$bulkAccessArray)){ ?>
			<div class="col-sm-3">
				<section class="panel">
				  <header class="panel-heading">
					  New Category
					</header>	
					<div class="panel-body">
						<form role="form" id="category_add" action="javascript:;" method="post" name="category_add">
								
								<?php if($branch_id=='0'){ ?>
									<div class="form-group">
										<label>Branch *</label>
										
										<select class="branch_validate" name="branch_id" id="abranch_id" required>
					    					<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
											<?=getBranchBox_new($dbcon, $branch,'all');?>
										</select>
						            </div>
						        <?php } ?>

								<div class="form-group">
								  <label> Category Name *</label>
								 <input class="form-control" type='text' name='cat_name' id='cat_name' value='' />
							    </div>
								
							  	<input type='hidden' name='mode' id='mode' value='add' />
							  	<input type='hidden' name='token' id='token' value='<?php echo $token; ?>' />				  
							  <button type="submit" class="btn btn-info">Submit</button>
						  </form>

					</div>
				</section>
			</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_CATEGORY_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
			<section class="panel">
				  <header class="panel-heading">
					  Category List
				 <span class="tools pull-right">
					<a href="javascript:;" class="fa fa-chevron-down"></a>
					
				 </span>
				  </header>
				  <div class="panel-body">
				  <div class="adv-table">
				  <table  class="display table table-bordered table-striped" id="dynamic-table">
				  	<div class="col-md-12">
			                        <div class="col-md-6">
			                        	<div class="form-group">
											<label class="col-md-4" style="text-align: right">Branch *</label>
											<div class="col-md-6">
												<select class="select2" name="branch_id" id="branch_id" onchange="load_category_datatable()" required >
	                    								<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
														<?=getBranchBox_new($dbcon, $branch,'all');?>
	                							</select>
	                						</div>
						            	</div>
			                            
			                        </div>
			                    </div>
				  <thead>
				  <tr>
						<th>Sr. NO.</th>
						<th>Category Name</th>
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
<div class="modal colored-header info" id="ModalEditAccount" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog custom-width">
		<div class="modal-content">
			<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>Edit Category</h3>
				
			</div>
			<div class="modal-body form">
			<form id="FormEditunit" role="form" method="post" novalidate>
				
				<?php if($branch_id=='0'){ ?>
					<div class="form-group">
						<label>Branch *</label>
						
						<select class="branch_validate" name="branch_id" id="e_branch_id" required>
	    					<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
							<?=getBranchBox_new($dbcon, $branch,'all');?>
						</select>
		            </div>
	        	<?php } ?>

				<div class="form-group">
				  <label for="unitid">Category Name *</label>
				   <input class="form-control" type='text' name='e_cat_name' id='e_cat_name' value='' />
				</div>					
								
			</div>
			<div class="modal-footer">
				<input type="hidden" name="token" id="edit_token" value="<?php echo $token; ?>" />
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<input type="hidden" name="edit_pid" id="edit_pid" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update Category</button>
			</div>
			</form>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
	<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/reciclare_cat_mst.js?<?=time()?>"></script>
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
