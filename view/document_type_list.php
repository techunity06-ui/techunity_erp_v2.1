<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Docuement Type";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for document type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	ADMINISTRATOR_DOCUMENT_TYPE_MST_LIST,
        ADMINISTRATOR_DOCUMENT_TYPE_MST_CREATE
    ]);

    if(!in_array(ADMINISTRATOR_DOCUMENT_TYPE_MST_LIST,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
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
			<?php if(in_array(ADMINISTRATOR_DOCUMENT_TYPE_MST_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
						<header class="panel-heading">
							New <?=$form?>
						</header>	
						<div class="panel-body">
							<form role="form" id="document_type_add" action="javascript:;" method="post" name="document_type_add">
								<?php if($branch_id=='0'){ ?>
									<div class="form-group">
										<label>Branch *</label>
										<?php
											$str='';
											$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
											$rs_dispatch=$dbcon->query($query);	
										?>
										<select class="select2" name="branch_id" id="abranch_id" required >
	                    					<?php echo get_branch_name_company($dbcon);  ?>
	                    					<!--<option value="">Select Branch</option>
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
	                    					?>-->
	                					</select>
						            </div>
						        <?php } ?>
								<div class="form-group">
									<label>Document Type Name *</label>
									<input class="form-control" type='text' name='document_type_name' id='document_type_name' placeholder="Document Type Name" value='' />
								</div>			  
								<button type="submit" class="btn btn-success">Submit</button>
							</form>
							
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(ADMINISTRATOR_DOCUMENT_TYPE_MST_CREATE,$bulkAccessArray)){ ?>	
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
							<table class="display table table-bordered table-striped" id="document_type-table">
								<div class="col-md-12">
			                        <div class="col-md-6">
			                            <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_document_type_datatable()','4','6'); ?>
			                        </div>
			                    </div>
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Document Type Name</th> 
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
<?php include_once('../include/footer.php');?>
<!--footer end-->
</section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditdocument_type" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog custom-width">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Edit <?=$form?></h3>
			
		</div>
		<div class="modal-body form">
		<form id="FormEditdocument_type" role="form" method="post" novalidate>
			<?php if($branch_id=='0'){ ?>
				<div class="form-group">
					<label>Branch *</label>
					<?php
						$str='';
						$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
						$rs_dispatch=$dbcon->query($query);	
					?>
					<select class="select2" name="branch_id" id="e_branch_id" required>
    					
    					<?php echo get_branch_name_company($dbcon);  ?>
    					
    					<!--<option value="">Select Branch</option>
    					<option value="10000" selected="">All Branch</option>
    					<?php 
    						while($rel=mysqli_fetch_assoc($rs_dispatch))
							{	
								$str .= '<option '.$sel.' value="'.$rel['branch_id'].'">'.$rel['branch_name'].'</option>';
							}
							echo $str;
    					?>-->
					</select>
	            </div>
	        <?php } ?> 

			<div class="form-group">
				<label for="edit_document_type_name">Document Type Name</label>
				<input class="form-control" type='text' name='edit_document_type_name' id='edit_document_type_name' value='' />
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
<?php include_once('../include/include_js_file.php');?>  
<script src="<?=ROOT?>js/app/document_type_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
	width: '100%'
});
</script>
</body>
</html>
