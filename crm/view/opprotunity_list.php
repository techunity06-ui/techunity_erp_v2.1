<?php 
	session_start();
	include('../include/urlfile.php');
    $incPath = $path.'include/';
	$form="Sales Stage";
	$infopage = pathinfo( __FILE__ );
	$_SESSION['page']='crm/'.$infopage['filename'];
	$branch_id = $_SESSION['branch_id'];

	//check permission for sales stage
    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
    	CUSTOMER_SALES_STAGE_SLUG_READ,
        CUSTOMER_SALES_STAGE_SLUG_CREATE
    ]);

    if(!in_array(CUSTOMER_SALES_STAGE_SLUG_READ,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
	$getspecialConfiguration = getspecialConfiguration($dbcon);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>OPPORTUNITY</title>
	<style type="text/css">
		label{
			font-size: 15px;
		}
		.row_margin
		{
			margin-top:10px;
		}
		.btn-group-vertical>.btn.active, .btn-group-vertical>.btn:active, .btn-group-vertical>.btn:focus, .btn-group-vertical>.btn:hover, .btn-group>.btn.active, .btn-group>.btn:active, .btn-group>.btn:focus, .btn-group>.btn:hover{
			z-index:2;
			background-color: #bbdce6;
		}
		.control-label{
			font-weight: bold;
		}
		.fa-info-circle
		{
			color: blue !important;
			font-size: 16px !important;
		}
		.submit_err
		{
			color: red;
		}
	</style>
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
							<li><a href="<?=ROOT.CRM_ROOT.'crm_master'?>">CRM Masters</a></li>
							<li class="active"><?=$form?> List</li>
						</ul>
					</div>
				</section>
				<!--breadcrumbs end -->
			</div>	
		</div>
		<!--unit overview start-->
		<div class="row">
			<?php if(in_array(CUSTOMER_SALES_STAGE_SLUG_CREATE,$bulkAccessArray)){ ?>
				<div class="col-sm-3">
					<section class="panel">
						<header class="panel-heading">
							New <?=$form?>
						</header>	
						<div class="panel-body">
							<form role="form" id="opp_add" action="javascript:;" method="post" name="opp_add" enctype="multipart/form-data">
								<?php if($branch_id=='0'){ ?>
									<div class="form-group">
										<label>Branch *</label>
										<?php
											$str='';
											$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
											$rs_dispatch=$dbcon->query($query);	
										?>
										<select class="select2" name="branch_id" id="abranch_id" required >
	                    					<option value="">Select Branch</option>
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
	                    					?>
	                					</select>
						            </div>
						        <?php } ?>
								<div class="form-group">
									<label>Opportunity Stage*</label>
									<input type="text" name="opp_stage" class="form-control" id="opp_stage" />
								</div>
								<div class="form-group">
									<label>Probability(%) *</label>
									<input class="form-control" type="text" name="opp_probability" id="opp_probability" placeholder="" value=""  onkeypress="return isNumberKey(event)"  />
								</div>
								<div class="form-group">
									<label>Priority*</label>
									<input class="form-control" type="number" name="opp_priority" id="opp_priority" placeholder="" value=""  onkeypress="return isNumberKey(event)"  />
								</div>
								<div class="form-group">
									<label>Color</label>
									<input class="form-control" type="color" name="opp_color" id="opp_color" placeholder="" value="#ff0000" />
								</div>
								<?php if ($getspecialConfiguration["umaboy_permission"] == 1) { ?>
									<div class="form-group">
										<label>Template Name</label>
										<input class="form-control" type="text" name="opp_template" id="opp_template" placeholder="Template Name" />
									</div>
									<div class="form-group">
										<label>Template File</label>
										<input class="form-control" type="file" name="opp_file" id="opp_file" placeholder="" />
									</div>
									<div class="form-group">
										<label>Whatsapp enable</label>
										<div class="btn-group btn-group-toggle" data-toggle="buttons">
											<label class="btn btn-secondary active">
												<input type="radio" name="enable_whatsapp" id="enable_whatsapp" autocomplete="off" value="0"> No
											</label>
											<label class="btn btn-secondary" >
												<input type="radio" name="enable_whatsapp" id="enable_whatsapp1" autocomplete="off" value="1"> Yes
											</label>
										</div>
									</div>
								<?php } else {?>
									<input class="form-control" type="hidden" name="opp_template" id="opp_template" />
									<input class="form-control" type="hidden" name="enable_whatsapp" id="opp_template" value="0"  />
									<input class="form-control" type="hidden" name="file" id="opp_file" />
								<?php } ?>
	                                                        
								<button type="submit" class="btn btn-success">Submit</button>
							</form> 
						</div>
					</section>
				</div>
			<?php } ?>
			<?php if(in_array(CUSTOMER_SALES_STAGE_SLUG_CREATE,$bulkAccessArray)){ ?>
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
							<table class="display table table-bordered table-striped" id="opp-table">
								<div class="col-md-12">
			                        <div class="col-md-6">
			                            <?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'], false, true,'load_opp_datatable()','4','6'); ?>
			                        </div>
			                    </div>
								<thead>
									<tr>
										<th>Sr. NO.</th>
										<th>Opportunity Stage</th> 
										<th>Probability</th> 
										<th>Priority</th> 
										<th>Status</th> 
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
<?php include_once($include.'add_expense_head.php');?>
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>
<!-- Modal -->
<div class="modal colored-header info" id="ModalEditExp" role="dialog" data-keyboard="false" data-backdrop="static">
<div class="modal-dialog custom-width">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">Close &times;</button>
			<h3>Edit <?=$form?></h3> 
		</div>
		<div class="modal-body form">
		<form id="FormEditExp" role="form" method="post" novalidate>
			<?php if($branch_id=='0'){ ?>
				<div class="form-group">
					<label>Branch *</label>
					<?php
						$str='';
						$query="SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND company_id =".$_SESSION['company_id'];
						$rs_dispatch=$dbcon->query($query);	
					?>
					<select class="select2" name="e_branch_id" id="e_branch_id" required>
    					<option value="">Select Branch</option>
    					<option value="10000">All Branch</option>
    					<?php 
    						while($rel=mysqli_fetch_assoc($rs_dispatch))
							{	
								$str .= '<option '.$sel.' value="'.$rel['branch_id'].'">'.$rel['branch_name'].'</option>';
							}
							echo $str;
    					?>
					</select>
	            </div>
	        <?php } ?>
			<div class="form-group">
				<label for="edit_expense_name">Opportunity Stage</label>
				<input type="text" name="edit_opp_stage" class="form-control" id="edit_opp_stage" />
			</div>
			<div class="form-group">
				<label>Probability(%) *</label>
				<input class="form-control" type="text" name="edit_opp_probability" id="edit_opp_probability" placeholder="" value="" onkeypress="return isNumberKey(event)" />
			</div>
			<div class="form-group">
				<label>Priority*</label>
				<input class="form-control" type="number" name="edit_opp_priority" id="edit_opp_priority" placeholder="" value="" onkeypress="return isNumberKey(event)" />
			</div>
			<div class="form-group">
				<label>Color</label>
				<input class="form-control col-md-6" type="color" name="edit_opp_color" id="edit_opp_color" placeholder="" value="#ff0000" />
			</div>
			<?php if ($getspecialConfiguration["umaboy_permission"] == 1) { ?>
				<input class="form-control" type="hidden" name="whatsapp_status" id="whatsapp_status" value="1"/>
				<div class="form-group">
						<label>Template Name</label>
						<input class="form-control" type="text" name="edit_opp_template" id="edit_opp_template" placeholder="Template Name" />
					</div>
					<div class="form-group">
						<label>Template File</label>
						<input class="form-control" type="file" name="edit_opp_file" id="edit_opp_file" placeholder="" />
					</div>
					<div class="form-group">
						<label>Whatsapp enable</label>
						<div class="btn-group btn-group-toggle" data-toggle="buttons">
							<label class="btn btn-secondary active">
								<input type="radio" name="edit_enable_whatsapp" id="edit_enable_whatsapp" autocomplete="off" value="0"> No
							</label>
							<label class="btn btn-secondary" >
								<input type="radio" name="edit_enable_whatsapp1" id="edit_enable_whatsapp1" autocomplete="off" value="1"> Yes
							</label>
						</div>
					</div>
				</div>
			<?php } else { ?>
				<input class="form-control" type="hidden" name="whatsapp_status" id="whatsapp_status" value="0"/>
				<input class="form-control" type="hidden" name="edit_enable_whatsapp1" id="edit_opp_template" value="1"  />
				<input class="form-control" type="hidden" name="edit_enable_whatsapp" id="edit_opp_template" value="0"  />
				<input class="form-control" type="hidden" name="edit_opp_file" id="edit_opp_file" />
			<?php } ?>
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
<script src="<?=ROOT.CRM_ROOT?>js/app/opp_mst.js?<?=time()?>"></script>

<script>
$(".select2").select2({
	width: '100%'
});
</script>
</body>
</html>
