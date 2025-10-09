<?php 
	session_start();
	include('../include/urlfile.php');
	
	$form="Cost Center";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']=$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];
	//check permission for process type add
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	READ_COST_CENTER_MASTER,
        CREATE_COST_CENTER_MASTER
    ]);

    if(!in_array(READ_COST_CENTER_MASTER,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>COST CENTER</title>
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
		<!--Cost Center overview start-->
		<div class="row">
			<?php if(in_array(CREATE_COST_CENTER_MASTER,$bulkAccessArray)){ ?>
				<div class="col-sm-4">
					<section class="panel">
						<header class="panel-heading">
							New Cost Center
						</header>	
						<div class="panel-body">
							<form role="form" id="CostCenter_add" action="javascript:;" method="post" name="CostCenter_add">
								<div class="row">
									<div class="col-md-10">
										<div class="form-group">
											<label>Cost Center Group *</label>
											<?php
												$str='';
												$query="SELECT cost_group_id, cost_group_name FROM tbl_cost_center_group WHERE isactive=1 AND isdelete=0 order by cost_group_id desc";
												$rs_dispatch=$dbcon->query($query);	
											?>
											<select class="select2" name="cost_group_id" id="cost_group_id" required >
		                    					<option value="">Select Cost Center Group</option>
		                    					<!-- <option value="10000">All Cost Center Group</option> -->
		                    					<?php 
		                    						while($rel=brp_mysqli_fetch_assoc($rs_dispatch))
													{	
														$sel=''; 
														// if($rel['cost_group_id']==$branchid){
														// 	$sel ="selected='selected'";
														// }
														$str .= '<option '.$sel.' value="'.$rel['cost_group_id'].'">'.$rel['cost_group_name'].'</option>';
													}
													echo $str;
		                    					?>
		                					</select>
							            </div>
									</div>
									<div class="form-group col-md-2">
										<input type="button" style="margin-top: 23px;" name="addCostCenterGroup" id="addCostCenterGroup" data-toggle="modal" data-target="" onclick="add_cost_center_group();" class="btn btn-primary" value="+"/>
									</div>
								</div>
								
								<div class="form-group">
									<label>Cost Center Name *</label>
									<input class="form-control" type='text' required="" minlength="2" name='CostCenter_name' id='CostCenter_name' placeholder="Cost Center Name" value='' />
								</div>			  
								<button type="submit" class="btn btn-info">Submit</button>
							</form>
							
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(CREATE_COST_CENTER_MASTER,$bulkAccessArray)){ ?>
				<div class="col-sm-8">
			<?php }else{ ?>	
				<div class="col-sm-12">
			<?php } ?>		
				<section class="panel">
					<header class="panel-heading">
						Cost Center List
						<span class="tools pull-right">
							<a href="javascript:;" class="fa fa-chevron-down"></a>
						</span>
					</header>
					<div class="panel-body">
						<div class="adv-table">
							<table  class="display table table-bordered table-striped" id="cost-center-table">
								
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Cost Center Name</th>
										<th>Cost Center Group Name</th>
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
		
		<!--CostCenter overview end-->
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
			<h3>Edit Cost Center</h3>
			
		</div>
		<div class="modal-body form">
		<form id="FormEditCostCenter" role="form" method="post" novalidate>
			<div class="form-group">
				<label>Common *</label>
				<?php
					$str='';
					$query="SELECT cost_group_id, cost_group_name FROM tbl_cost_center_group WHERE isactive=1 AND isdelete=0";
					$rs_dispatch=$dbcon->query($query);	
				?>
				<select class="select2" name="cost_group_id" id="e_cost_group_id" required>
					<option value="">Select Common Category</option>
					<?php 
						while($rel=brp_mysqli_fetch_assoc($rs_dispatch))
						{	
							$str .= '<option '.$sel.' value="'.$rel['cost_group_id'].'">'.$rel['cost_group_name'].'</option>';
						}
						echo $str;
					?>
				</select>
            </div>	        
			<div class="form-group">
				<label for="CostCenterid">Cost Center Name</label>
				<input class="form-control" required="" minlength="2" type='text' name='edit_CostCenter_name' id='edit_CostCenter_name' value='' />
			</div>		
			
			</div>
			<div class="modal-footer">
				<input type="hidden" name="edit_id" id="edit_id" value="" />
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
				<button class="btn btn-info btn-flat" type="submit">Update Cost Center</button>
			</div>
		</form>
	</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<?php include_once($include1.'add_cost_center_group.php');?>
<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/cost_center_mst.js?<?=time()?>"></script>
<script>

$(".select2").select2({
width: '100%'
}).on('change', function() {
$(this).valid();
});
</script>
</body>
</html>
