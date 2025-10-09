<?php 

	session_start();
	include('../include/urlfile.php');	
	$form="Store Request";
	$countryid='101';$stateid='1';$cityid='1';

	
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
       PRODUCTION_STORE_LIST_SLUG_VIEW,PRODUCTION_STORE_LIST_SLUG_CREATE,PRODUCTION_STORE_LIST_SLUG_READ,PRODUCTION_STORE_LIST_SLUG_UPDATE,PRODUCTION_STORE_LIST_SLUG_DELETE,PRODUCTION_STORE_LIST_APPROVE,PRODUCTION_STORE_LIST_RETURN
]);

if(!in_array(PRODUCTION_STORE_LIST_APPROVE,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
	
	
	$mode="add_store_release";
	$_REQUEST['id']=urldecode($_REQUEST['id']);
	$p_id=$dbcon->real_escape_string($_REQUEST['id']);
	
	 $branch_id = $_SESSION['branch_id'];
	
	 $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));
		
	if(!empty($branch_id)){
		$branch_whre=" and ap.branch_id=".$branch_id;
	}
	$query="select p.product_name,pr.process_name,ap.previous_process_id,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
			where ap.p_id in (".$p_id.")";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	$select_branch_id=$rel['branch_id'];
	//get Jobcard NO 
	//$pno=load_series_no($dbcon,7);
	$pno=load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
	
	$pending_qty=total_production_pending_qty($dbcon,$p_id);
	$working_qty=production_start_count_using_p_id($dbcon,$p_id,0);

	$req_qty = store_request_approval_pending_count($dbcon,$rel['process_id'],1,1,1);
	$release_qty = store_release_count($dbcon,$rel['process_id'],1,1,1);
	$pending_qty =  $req_qty - $release_qty;
	
	$working_qty = $req_qty - $release_qty;
	$date=date('d-m-Y');
?>

<!DOCTYPE html>
<html lang="en">
	<head>
	<title>Production Request Material</title>
		<?php include_once($include.'include_css_file.php');?>
		<style>
			.abc {
				color: #2a3542;
			}
		</style>
		
	</head>
	<body onload="show_material_list();">
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel" >
								<header class="panel-heading">
									<h3 style="float:left;"> <?=$form?></h3>
								</header>	
								<div class="" style="padding:20px !important;">
								  <ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.INVENTORY_ROOT.'production_request_pending_material_list'?>">Store Approval Pendng List </a></li>
								  </ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
								  New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="store_release_add" action="javascript:;" method="post" name="store_release_add">
										
										<div class="row">
											<div class="col-md-4">
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Product Name </label>
												<div class="col-md-6 col-xs-11" style="color: #0e8400;font-weight: 600;">
													<?=$rel['product_name']?>
													<!--<input type="text" class="form-control" id="pr_product_id" name="pr_product_id" value="<?//=$rel['product_name']; ?>" readonly />-->
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Process Name </label>
												<div class="col-md-6 col-xs-11" style="color: #c71313;font-weight: 600;">
													<?=$rel['process_name']; ?>
													<!--<input type="text" class="form-control" id="pr_process_id" name="pr_process_id" value="<?//=$rel['process_name']; ?>" readonly />-->
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">  	
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Pending Qty</label>
													<div class="col-md-6 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
														<?=$pending_qty;?> <?=$rel['unit_name']?>
													</div>
												</div>	
											</div>
											
											<div class="col-md-12"></div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Start Time </label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" id="pr_st_time1" name="pr_st_time1" value="<?=date('d-m-Y h:i:sa') ?>" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Process No*</label>
													<div class="col-md-6 col-xs-11">
														<input id="pr_process_no" name="pr_process_no" type="text" class="form-control" title="Enter Challan No" value="<?=$pno;?>" placeholder="Process No" required readonly >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Request Qty *</label>
													<div class="col-md-4 col-xs-11">
														<input type="text" name="start_qty" id="start_qty" class="form-control" onkeyup="show_material_list()" value="<?=$working_qty?>" /> 
													</div>
													<div class="col-md-1" style="font-size: 18px;font-weight: 600;color: #d02424;">
														<?=$rel['unit_name']?>
													</div>
												</div>
											</div>	
											
											<div class="col-md-12">
											<div class="col-md-4">
												<?php echo getBranchBox($dbcon, $branch_id,$select_branch_id, true, true, 'show_material_list()'); ?>
											</div>
											<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>Isuue No.</strong>
										</div>
										<div class="col-md-6">
											<input class="form-control" type="text" readonly="true" name="issue_no" id="issue_no" value="<?= get_issue_no($dbcon); ?>">
										</div>
									</div>
									<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>Isuue Date</strong>
										</div>

										<div class="col-md-6">
											<input id="issue_date" name="issue_date" type="text" class="form-control default-date-picker required valid" title="Issue Date" placeholder="Issue Date" value="<?=$date?>">
										</div>
									</div>
										</div>
											
											<div class="col-md-12">
												<div class="panel-body">
													<div class="adv-table">
														<table class="display table table-bordered table-striped" id="material_details">
															<thead>
															  <tr>
																<th>Product Name</th>
																<th>Product Category</th>
																<th>Qty Needed For Single Piece</th>
																<th>Total Required Qty</th>
																<th>Total Available Qty </th>
																<th>Total Usable Qty</th>
																<th>Unit</th>
															  </tr>
															</thead>
															<tbody id="sub_row_mat"></tbody>				 
														</table>
													</div>
												</div>
											</div>
											<div class="col-md-12" >
												<label class="col-md-1 control-label" style="color: #404040;font-weight: 600;">Remarks </label>
												<div class="col-md-6 col-xs-11">
														<textarea id="remark" name="remark" class="form-control" rows="3"></textarea> 
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6 col-md-offset-4">  	
													<input type="submit" id="sp_btn" name="submit" class="btn btn-success" value="Release" />
												</div>
											</div>		
										</div>
										
										
										<!--start -->
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
											<input type="hidden" name="max_available_qty" id="max_available_qty" value="<?=$working_qty?>" />
											<input type="hidden" id="redirect_page" name="redirect_page" value="<?=$_SESSION['redirect_page']?>">
											<input type="hidden" id="pending_qty" name="pending_qty" value="<?=$pending_qty;?>">
											<input type='hidden' name='p_id' id='p_id' value='<?=$p_id;?>' />
											<input type="hidden" name="product_base_unit" id="product_base_unit" value="<?=$rel['process_unit']?>" />
											<input type="hidden" name="branch_id" id="branch_id" value="<?=$select_branch_id?>" />
											<input type="hidden" name="product_id" id="product_id" value="<?=$rel['p_product_id']?>" />
											<input type="hidden" name="process_id" id="process_id" value="<?=$rel['process_id']?>" />
											<input type="hidden" name="previous_process_id" id="previous_process_id" value="<?=$rel['previous_process_id']?>" />
											<input type="hidden" name="product_version" id="product_version" value="<?=$rel['product_version']?>" />
										<!--end-->
										
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
		</section>
		<?php
			
			include_once($include.'include_js_file.php');

			//include_once($include.'serial_number_add.php');
		?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/production_store_release.js"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			/* $("#product_id").select2({
				width: '83%'
			}); */

			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			$(".form_datetime-meridian").datetimepicker({
				format: "dd-mm-yyyy HH:ii P",
				showMeridian: true,
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"
			});

			function consinee_change(val){
				if(val=='1'){
					$('#consignee_id').select2("val","");
					$('#consignee').hide();
				}
				else{
					$('#consignee').show();
				}
			}

		</script>

		<?php
		if($mode=="add_store_request" && $count_job==0){
			echo "<script>get_series_no()</script>";
			echo "<script>get_series_no_jobwork()</script>";
		} 
		?>
	</body>
</html>