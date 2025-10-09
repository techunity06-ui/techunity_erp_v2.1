<?php 
	session_start();
	include('../include/urlfile.php');
	$form="Narration";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	READ_NARRATION_MASTER,
        CREATE_NARRATION_MASTER
    ]);

    if(!in_array(READ_NARRATION_MASTER,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>NARRATION</title>
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
		<!--Narration overview start-->
		<div class="row">
			<?php if(in_array(CREATE_NARRATION_MASTER,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
						<header class="panel-heading">
							New Narration
						</header>	
						<div class="panel-body">
							<form role="form" id="Narration_add" action="javascript:;" method="post" name="Narration_add">
								<div class="form-group">
									<label>Select Narration Voucher *</label>
									<?php
										$str='';
										$query="SELECT cm.common_mst_id,cm.`common_mst_name` FROM `tbl_common_mst` as cm join tbl_common_category_mst as ccm on ccm.`common_category_id`=cm.`common_category_id` and ccm.isdelete=0 and ccm.common_category_name='VOUCHER' WHERE cm.isdelete=0
";
										$rs_dispatch=$dbcon->query($query);	
									?>
									<select class="select2" name="common_mst_id" id="common_mst_id" required >
                    					<option value="">Select Category</option>
                    					<?php 
                    						while($rel= brp_mysqli_fetch_assoc($rs_dispatch))
											{	
												$sel=''; 
												if($rel['common_mst_id']==$branchid){
													$sel ="selected='selected'";
												}
												$str .= '<option '.$sel.' value="'.$rel['common_mst_id'].'">'.$rel['common_mst_name'].'</option>';
											}
											echo $str;
                    					?>
                					</select>
					            </div><br>
								<div class="form-group">
									<label>Narration Name *</label>
									<textarea class="form-control" rows="4" required="" name='Narration_name' id='Narration_name'></textarea>
									<!-- <input class="form-control" type='text' required="" minlength="2" name='Narration_name' id='Narration_name' placeholder="Narration Name" value='' /> -->
								</div>			  
								<button type="submit" class="btn btn-info">Submit</button>
							</form>
							
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(CREATE_NARRATION_MASTER,$bulkAccessArray)){ ?>
				<div class="col-sm-9">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
				<section class="panel">
					<header class="panel-heading">
						Narration List
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="narration-table">
								
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Narration Name</th>
										<th>Narration Voucher</th>
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
		
		<!--Narration overview end-->
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
			<h3>Edit Narration</h3>
			
		</div>
		<div class="modal-body form">
		<form id="FormEditNarration" role="form" method="post" novalidate>
			<div class="form-group">
				<label>Narration Voucher *</label>
				<?php
					$str='';
					$query="SELECT cm.common_mst_id,cm.`common_mst_name` FROM `tbl_common_mst` as cm join tbl_common_category_mst as ccm on ccm.`common_category_id`=cm.`common_category_id` and ccm.isdelete=0 and ccm.common_category_name='VOUCHER' WHERE cm.isdelete=0";
					$rs_dispatch=$dbcon->query($query);	
				?>
				<select class="select2" name="common_mst_id" id="e_common_mst_id" required>
					<option value="">Select Common Category</option>
					<?php 
						while($rel=brp_mysqli_fetch_assoc($rs_dispatch))
						{	
							$str .= '<option '.$sel.' value="'.$rel['common_mst_id'].'">'.$rel['common_mst_name'].'</option>';
						}
						echo $str;
					?>
				</select>
            </div>	        
			<div class="form-group">
				<label for="Narrationid">Narration Name</label>
				<textarea class="form-control" rows="4" required="" name='edit_Narration_name' id='edit_Narration_name'></textarea>
			</div>		
			
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update Narration</button>
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/narration_mst.js?<?=time()?>"></script>
<script>
$(".select2").select2({
width: '100%'
}).on('change', function() {
$(this).valid();
});
</script>
</body>
</html>
