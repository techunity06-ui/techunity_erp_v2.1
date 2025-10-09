<?php 
	session_start();
	include('../include/urlfile.php');
	
	$form="Transportation";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_TRANSPORATATION_LIST,
        ADMINISTRATOR_TRANSPORATATION_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_TRANSPORATATION_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
    $companyConfiguration=getCompanyConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>TRANSPORTATION DETAIL</title>
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
			<?php if(in_array(ADMINISTRATOR_TRANSPORATATION_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
						<header class="panel-heading">
							New <?=$form?>
						</header>	
						<div class="panel-body">
							<form role="form" id="transportation_add" action="javascript:;" method="post" name="transportation_add">
								<?php if($branch_id=='0'){ ?>
									<div class="form-group">
										<label>Branch *</label>
										<select class="branch_validate" name="branch_id" id="abranch_id" required >
	                    					<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
																<?=getBranchBox_new($dbcon, $branch,'all');?>
	                					</select>
						            </div>
						        <?php}else{ ?>
													<input type="hidden" name="branch_id" id="branch_id" value="<?=$companyConfiguration['default_branch_id']?>" />
												<?php} ?>

								<div class="form-group">
									<label>Transportation Name *</label>
									<input class="form-control" type='text' name='transportation_name' id='transportation_name' placeholder="Transportation Name" value='' />
								</div>
								
								<div class="form-group">
									<label>Transportation Email Id </label>
									<input class="form-control" type='text' name='transportation_email_id' id='transportation_email_id' placeholder="Transportation Email Id" value='' />
								</div>
								<div class="form-group">
									<label>Transportation Branch </label>
									<input class="form-control" type='text' name='transportation_branch' id='transportation_branch' placeholder="Transportation Branch" value='' />
								</div>
								<div class="form-group">
									<label>Transportation Phone Num </label>
									<input class="form-control" type='text' name='transportation_phone_num' id='transportation_phone_num' placeholder="Transportation Phone Num" value='' />
								</div>

								<div class="form-group">
									<label>Transportation GST No</label>
									<input class="form-control" type='text' name='transportation_gst_number' minlength="15" maxlength="15" id='transportation_gst_number' placeholder="Transportation GST No" value='' />
								</div>			  
								<button type="submit" class="btn btn-success">Submit</button>
							</form>
							
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_TRANSPORATATION_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
				<section class="panel">
					<header class="panel-heading">
						<?=$form?> List
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table class="display table table-bordered table-striped" id="transportation-table">
								<!-- <div class="col-md-12">
			                        <div class="col-md-6">
			                        <select class="select2" name="branch_id" id="branch_id" onchange="load_transportation_datatable()" required >
	                    								<?php $branch = isset($rel['branch_id']) ? $rel['branch_id'] : '1000'; ?>
														<?=getBranchBox_new($dbcon, $branch,'all');?>
	                							</select>
			                           
			                        </div>
			                    </div> -->
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Transportation Name</th> 
										<th>Transportation Email Id</th> 
										<th>Transportation Branch</th> 
										<th>Transportation Phone Num</th> 
										<th>Transportation GST No</th> 
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
<?php include_once($include.'add_transport_address.php');?>
<!--footer end-->
</section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEdittransportation" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog custom-width">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Edit <?=$form?></h3>
			
		</div>
		<div class="modal-body form">
		<form id="FormEdittransportation" role="form" method="post" novalidate>
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
				<label for="edit_transportation_name">Transportation Name</label>
				<input class="form-control" type='text' name='edit_transportation_name' id='edit_transportation_name' value='' />
			</div>
			
			<div class="form-group">
				<label for="edit_transportation_name">Transportation Email Id</label>
				<input class="form-control" type='text' name='edit_transportation_email_id' id='edit_transportation_email_id' value='' />
			</div>
			<div class="form-group">
				<label for="edit_transportation_name">Transportation Branch</label>
				<input class="form-control" type='text' name='edit_transportation_branch' id='edit_transportation_branch' value='' />
			</div>
			<div class="form-group">
				<label for="edit_transportation_name">Transportation Phone Num</label>
				<input class="form-control" type='text' name='edit_transportation_phone_num' id='edit_transportation_phone_num' value='' />
			</div>
			
			<div class="form-group">
				<label>Transportation GST No</label>
				<input class="form-control" type='text' name='edit_transportation_gst_number' id='edit_transportation_gst_number' placeholder="Transportation GST No" value='' />
			</div>	

			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-success btn-flat" type="submit">Update</button>
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/transportation.js?<?=time()?>"></script>
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
