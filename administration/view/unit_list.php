<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Unit";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_UNIT_LIST,
        ADMINISTRATOR_UNIT_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_UNIT_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>UNIT LIST</title>
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
							<li class="active"><?=$form?> List</li>
						</ul>
					</div>
				</section>
				<!--breadcrumbs end -->
			</div>	
		</div>
		<!--unit overview start-->
		<div class="row">
			<?php if(in_array(ADMINISTRATOR_UNIT_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
						<header class="panel-heading">
							New Unit
						</header>	
						<div class="panel-body">
							<form role="form" id="unit_add" action="javascript:;" method="post" name="unit_add">
								
								<div class="form-group">
									<label>Unit Name *</label>
									<input class="form-control" type='text' name='unit_name' id='unit_name' placeholder="Unit Name" value='' />
								</div>
								<div class="form-group">
									<label>GST Code *</label>
									<input class="form-control" type='text' name='unit_code' id='unit_code' placeholder="Unit Code" value='' />
								</div>			  
								<button type="submit" class="btn btn-info">Submit</button>
							</form>
							
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_UNIT_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
				<section class="panel">
					<header class="panel-heading">
						Unit List
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="unit-table">
								
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Unit Name</th>
										<th>GST Code</th>
										<th>Date</th>
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
			<h3>Edit unit</h3>
			
		</div>
		<div class="modal-body form">
		<form id="FormEditunit" role="form" method="post" novalidate>
			
			<div class="form-group">
				<label for="unitid">Unit Name *</label>
				<input class="form-control" type='text' name='edit_unit_name' id='edit_unit_name' value='' />
			</div>	
			<div class="form-group">
				<label>GST Code *</label>
				<input class="form-control" type='text' name='edit_unit_code' id='edit_unit_code' placeholder="Unit Code" value='' />
			</div>	
			
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update unit</button>
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT ?>js/app/unit_mst.js?<?=time()?>"></script>
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
