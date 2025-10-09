<?php 

	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="Start Process";
	$countryid='101';$stateid='1';$cityid='1';
	
	$mode="add_start_process";
	 $product_id=$dbcon->real_escape_string($_REQUEST['id']);
	 $process_type=$dbcon->real_escape_string($_REQUEST['type']);
	 $process_id=$dbcon->real_escape_string($_REQUEST['process']);
	 $page_name=$dbcon->real_escape_string($_REQUEST['page_name']);
	 $branch_id1=$dbcon->real_escape_string($_REQUEST['branch_id']);
	 //$branch_id=0;
	 $branch_id = $_SESSION['branch_id'];
	 $select_branch_id=$branch_id1;
	 $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));
		if($process_type==1)
			{
				$pr_type='inhouse';
			}
			else
			{
				$pr_type='outward';
			}
	//echo $id;
	/* $query="select ap.*,p.product_name,p.product_type,pr.process_name,dqty,r.rp_req_no,j.jobwork_no from tbl_allocate_process as ap 
	left join product_mst as p on p.product_id=ap.p_product_id 
	left join process_mst as pr on pr.process_id=ap.process_id 
	left join tbl_request_product as r on r.rp_id=ap.p_ref_id 
	left join tbl_jobwork as j on j.j_alloc_process_id=ap.p_id
	left join (select sum(pt_qty) as dqty,pt_alloc_id from tbl_allocate_process_trn group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id where p_id='$id'";
	$rel=mysqli_fetch_assoc($dbcon->query($query)); */

	/*$user_type = $_SESSION['user_type'];
	$where_user_wise = '';
	if($user_type!='2'){
		$where_user_wise = 'and ap.resource_id="'.$_SESSION['resource_id'].'"';
	}*/
	if(!empty($branch_id)){
		$branch_whre=" and ap.branch_id=".$branch_id;
	}
	$query="select ap.*,sum(ap.p_qty) as ap_qty,sum(ap.pen_qty) as apen_qty,p.product_type,p.product_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty,pr.process_name,group_concat(ap.p_id ORDER BY ap.p_id ASC) as allo_id from tbl_allocate_process as ap 
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join process_mst as pr on pr.process_id=ap.process_id 
			left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
			left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
			where ap.process_id=".$process_id." and ap.p_product_id=".$product_id." and ap.p_status IN(0,1) and pr_process_type=".$process_type." ".$branch_whre." and ap.company_id=".$_SESSION['company_id']." group by ap.p_product_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	//get Jobcard NO 
	
	$sel1=$dbcon->query("select jobwork_id from tbl_jobwork where j_alloc_process_id='$id'");
	$count_job=mysqli_num_rows($sel1);
	
	$pno=load_series_no($dbcon,7);
	
	
	
	$query_c="select * from tbl_allocate_process where p_id in (".$rel['allo_id'].")";
	//var_dump($query);
	$result_c=$dbcon->query($query_c);
	$nnq=array();
	while($row_c=mysqli_fetch_assoc($result_c)){
		$aaac_qty=start_qty_avalable($dbcon,$process_id,$process_type,$product_id,$row_c['p_id'],$branch_id);
		if($aaac_qty>0){
			array_push($nnq,$row_c['p_id']);
		}
	}
	$eeid=implode(",",$nnq);

	if($page_name=='resource'){
		$av_qty=get_resource_daily_qty($dbcon, $process_type, $process_id, $product_id, date('Y-m-d')); //date('Y-m-d')
		$pending_qty = $av_qty;
	}else{
		$av_qty=start_qty_avalable($dbcon,$process_id,$process_type,$product_id,"",$branch_id);
		$pending_qty = $rel['apen_qty'];
	}
	
	
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body onload="show_material_list();">
		<section id="container" class="sidebar-closed">
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
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
									  <li><a href="<?=ROOT.'process_detail_list/'.$rel['process_id'].'/'.$rel['pr_process_type'];?>">Start Process </a></li>
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
									<form class="form-horizontal" role="form" id="start_allocate_add" action="javascript:;" method="post" name="start_allocate_add">
										
										<div class="row">
											<div class="col-md-4">
												<label class="col-md-4 control-label"> Product Name </label>
												<div class="col-md-6 col-xs-11">
													<input type="text" class="form-control" id="pr_product_id" name="pr_product_id" value="<?=$rel['product_name']; ?>" readonly />
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-md-4 control-label"> Process Name </label>
												<div class="col-md-6 col-xs-11">
													<input type="text" class="form-control" id="pr_process_id" name="pr_process_id" value="<?=$rel['process_name']; ?>" readonly />
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Process Type  *</label>
													<div class="col-md-6 col-xs-11">
														<input id="pr_process_type" name="pr_process_type" type="text" class="form-control" title="Process Type" value="<?=$pr_type;?>" placeholder="Process Type" required readonly>		
													</div>
												</div>
											</div>
											<div class="col-md-12"></div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Start Time *</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" id="pr_st_time1" name="pr_st_time1" value="<?=date('d-m-Y h:i:sa') ?>" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Process No*</label>
													<div class="col-md-6 col-xs-11">
													
														<!--<input id="pr_process_no" name="pr_process_no" type="text" class="form-control" title="Enter Challan No" value="<?=$rel['rp_req_no'];?>" placeholder="Process No" required >-->
														<input id="pr_process_no" name="pr_process_no" type="text" class="form-control" title="Enter Challan No" value="<?=$pno;?>" placeholder="Process No" required >
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<?php echo getBranchBox($dbcon, $branch_id,$select_branch_id, true, true, 'show_material_list()'); ?>
											</div>
											<!--<div class="col-md-4">
												<div class="form-group">
												  <label class="col-md-4 control-label">Jobcard No*</label>
													<div class="col-md-6 col-xs-11">
														<input id="pr_job_no" name="pr_job_no" type="text" class="form-control required valid" title="Date" value="<?php if($count_job>0){  echo $rel['jobwork_no']; } ?>" placeholder="Jobwork No">
													</div>
												</div>	
											</div>-->
											<div class="col-md-12"></div>
											<div class="col-md-4">
												<div class="form-group">  	
													<label class="col-md-4 control-label">Pending Qty*</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" id="pr_p_qty1" name="pr_p_qty1" class="form-control"  value="<?=$pending_qty;?>" placeholder="" readonly>
													</div>
												</div>	
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Available Qty *</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" name="machine_no" id="machine_no" class="form-control" onkeyup="show_material_list()" value="<?=$av_qty?>" />
														
														<!--<input type="hidden" name="request_no" id="request_no" class="form-control" value="<?=$rel['p_ref_id'];?>" readonly />-->
													</div>
												</div>
											</div>	
											<?php if($rel['pr_process_type']=='2') { ?>
												<div class="col-md-4">
													<div class="form-group">
													  <label class="col-md-4 control-label">Jobwork No*</label>
														<div class="col-md-6 col-xs-11">
															<input id="pr_jobwork_no" name="pr_jobwork_no" type="text" class="form-control required valid" title="Date" value="<?php if($count_job>0){  echo $rel['jobwork_no']; } ?>" readonly placeholder="Jobwork No">
														</div>
													</div>	
												</div>
											<?php } ?>
											
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
											<?php if($rel['pr_process_type']=='2') { ?>
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Select Vendor *</label>
														<div class="col-md-6 col-xs-11">
															<select class="select2" id="pr_vendor_id" name="pr_vender_id" required >
																<?=getcust($dbcon,'');?>
															</select>
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Process Rate *</label>
														<div class="col-md-6 col-xs-11">
															<input type="number" name="pr_rate" id="pr_rate" class="form-control" value="" required />
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label">Chalan No *</label>
														<div class="col-md-6 col-xs-11">
															<input type="text" name="pr_chalan_no" id="pr_chalan_no" class="form-control" value=""  required />
														</div>
													</div>
												</div>
											</div>
											<?php } ?>
											<div class="col-md-12">
												<div class="col-md-6 col-md-offset-4">  	<strong style='color:red;display:none' id="error_start_msg">You can Not Start The Process As the Machine Qty is not Available</strong><br>
												<input type="submit" id="sp_btn" name="submit" class="btn btn-success" value="Start The Process" />
												</div>
											</div>		
										</div>
										<input type='hidden' name='previous_process_id' id='previous_process_id' value='<?=$rel['previous_process_id']?>' />
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='save_print' id='save_print' value='' />
										<input type='text' name='eid' id='eid' value='<?=$eeid;?>' />
										<input type='hidden' name='product_id_hid' id='product_id_hid' value='<?=$rel['p_product_id'];?>' />
										<input type='hidden' name='product_type_hid' id='product_type_hid' value='<?=$rel['product_type'];?>' />
										<input type='hidden' name='product_qty_hid' id='product_qty_hid' value='<?=$rel['apen_qty'];?>' />
										<input type='hidden' name='process_id_hid' id='process_id_hid' value='<?=$rel['process_id'];?>' />
										<input type='hidden' name='process_type_hid' id='process_type_hid' value='<?=$rel['pr_process_type'];?>' />
										<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
										<input type="hidden" name="invoicetype_id_jobwork" id="invoicetype_id_jobwork" value="" />
										<input type="hidden" name="process_unit" id="process_unit" value="<?=$rel['process_unit']?>" />
										
										<input type="hidden" name="max_available_qty" id="max_available_qty" value="<?=$av_qty?>" />
										<input type="hidden" id="redirect_page" name="redirect_page" value="<?=$_SESSION['redirect_page']?>">
										
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
		</section>
		<?php
			include_once('../include/get_warehose_deduction_modal.php');
			include_once('../include/include_js_file.php');

			//include_once('../include/serial_number_add.php');
		?>   
		<script src="<?=ROOT?>js/app/allocate_process.js"></script>
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
		if($mode=="add_start_process" && $count_job==0){
			echo "<script>get_series_no()</script>";
			echo "<script>get_series_no_jobwork()</script>";
		} 
		?>
	</body>
</html>