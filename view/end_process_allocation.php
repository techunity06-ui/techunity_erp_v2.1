<?php 

	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$form="End Process Allocation";
	$countryid='101';$stateid='1';$cityid='1';
	
	$mode="end_process";
	//$id=$dbcon->real_escape_string($_REQUEST['id']);
	
	$product_id=$dbcon->real_escape_string($_REQUEST['id']);
	$process_type=$dbcon->real_escape_string($_REQUEST['type']);
	$process_id=$dbcon->real_escape_string($_REQUEST['process']);
	$edit_branch_id=$dbcon->real_escape_string($_REQUEST['branch_id']);
	 $branch_id = $_SESSION['branch_id'];
	/* $query="select ap.*,p.product_name,p.product_type,p.product_setting_check,pr.process_name,sum(j_qty) as inqty,sum(used_qty) as uqty,j.jobwork_id,j.j_pr_process_no,(select sum(pt_qty) end_qty from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=ap.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=ap.p_id and p_status=0) as start_qty from tbl_allocate_process as ap 
	left join product_mst as p on p.product_id=ap.p_product_id 
	left join process_mst as pr on pr.process_id=ap.process_id 
	left join tbl_jobwork as j on j.j_alloc_process_id=ap.p_id
	where p_id='$id'"; */
	
	if(!empty($edit_branch_id)){
		$branch_whre=" and ap.branch_id=".$edit_branch_id;
	}
	
	$query="select ap.*,p.product_name,p.product_type,p.product_setting_check,pr.process_name,group_concat(ap.p_id ORDER BY ap.p_id ASC) as allo_id,group_concat(ap.p_ref_id ORDER BY ap.p_id ASC) as p_ref_id1 from tbl_allocate_process as ap 
	left join product_mst as p on p.product_id=ap.p_product_id 
	left join process_mst as pr on pr.process_id=ap.process_id 
	left join tbl_jobwork as j on j.j_alloc_process_id=ap.p_id
	where ap.process_id=".$process_id." and ap.p_product_id=".$product_id." ".$branch_whre." and ap.company_id=".$_SESSION['company_id']." and ap.p_status=1 and pr_process_type=".$process_type." group by ap.p_product_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	
	$query11="select sum(pt_qty) as start_qty from tbl_allocate_process_trn as trn where p_status=0 and pt_alloc_id in (".$rel['allo_id'].")";
	$rel1=mysqli_fetch_assoc($dbcon->query($query11));
	
	$query12="select sum(pt_qty) as end_qty from tbl_allocate_process_trn as trn where p_status=1 and pt_alloc_id in (".$rel['allo_id'].")";
	$rel2=mysqli_fetch_assoc($dbcon->query($query12));
	
	$pending_qty=$rel1['start_qty']-$rel2['end_qty'];
	
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	if($rel['pr_process_type']==1)
	{
		$pr_type='inhouse';
	}
	else
	{
		$pr_type='outward';
	}
	
	/*
		Code By Umair
		Comment: Below code is commented and updating new code to check qc parameter added or not according to pathik
		Date: 27/03/2021
	*/
	/*$pr_setting=explode(",",$rel['product_setting_check']);
	if(in_array("product_qc",$pr_setting))
	{
		$qc_st="yes";
		$sty="display:none;";
	}else{
		$qc_st="no";
		$sty="";
	}*/

	$qc_paramter_info = check_product_qc_paramter($dbcon,$product_id,$process_id);
	if($qc_paramter_info=='1')
	{
		$qc_st="yes";
		$sty="display:none;";
	}else{
		$qc_st="no";
		$sty="";
	}
	//echo $process_id;
		
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<?php include_once('../include/include_css_file.php');?>
	</head>
	<body>
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
									  <li><a href="<?=ROOT.'process_detail_list/'.$rel['process_id'].'/'.$rel['pr_process_type'];?>">Process  List</a></li>
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
									<form class="form-horizontal" role="form" id="end_allocate_add" action="javascript:;" method="post" name="end_allocate_add">
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
														<input type="text" class="form-control" id="pr_st_time1" name="pr_st_time1" value="<?=date('d-m-Y h:i:sa',strtotime($rel['p_start_time'])); ?>" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">End Process Time*</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" id="pr_end_time1" name="pr_end_time1" value="<?=date('d-m-Y h:i:sa'); ?>" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label">Process No</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" id="process_no" name="process_no" value="1<?=$rel['j_pr_process_no']; ?>" readonly />
													</div>
												</div>
											</div>
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
													<label class="col-md-4 control-label">Qty.*</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" name="machine_no1" id="machine_no1" class="form-control" value="" onkeyup="get_machine_no_qty(this.value);show_material_list();" required />
														<!--<input type="hidden" name="request_no" id="request_no" class="form-control" value="<?=$rel['p_ref_id'];?>" readonly />-->
														<strong id="error_qty" style='color:red'></strong>
													</div>
												</div>
											</div>	
											<div class="col-md-4" style='<?=$sty?>' >
												<div class="form-group">
													<label class="col-md-4 control-label">Godown *</label>
													<div class="col-md-6 col-xs-11">
														<select class='form-control' name='grn_godown'  id='grn_godown$cnt' required >
															<?=get_all_godown($dbcon,'',1);?>
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<?php echo getBranchBox($dbcon, $branch_id,$edit_branch_id, true, true, '', 4,6); ?>
												</div>
											</div>	
											<div class="col-md-4" >
												<div class="col-md-4"> </div>
												<div class="col-md-4" >
													<button type="button" class="btn  btn-success" data-original-title="Alloca" data-toggle="tooltip" data-placement="top" onClick="open_scrap_entry()">Scrap Entry</button>
												</div>
											</div>
											<div class="col-md-12">
												<div class="panel-body">
													<div class="adv-table" id="sub_row_mat" >
														<!--<table class="display table table-bordered table-striped" id="material_details">
															<thead>
															  <tr>
																<th>Product Name</th>
																<th>Qty Needed For Single Piece</th>
																<th>Total Required Qty</th>
																<th>Total Available Qty </th>
																<th>Estimate Usable Qty</th>
																<th>Actual Usable Qty</th>
																<th>Unit</th>
															  </tr>
															</thead>
															<tbody id="sub_row_mat">
															</tbody>				 
														</table>-->
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="col-md-6 col-md-offset-4">  	
													<input type="submit" id="sp_btn1" name="submit" class="btn btn-success" value="End Process" />
												</div>
											</div>		
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='save_print' id='save_print' value='' />
										<input type='hidden' name='eid' id='eid' value='<?=$rel['allo_id'];?>' />
										<input type='hidden' name='product_id_hid' id='product_id_hid' value='<?=$product_id;?>' />
										<input type='hidden' name='product_type_hid' id='product_type_hid' value='<?=$rel['product_type'];?>' />
										<input type='hidden' name='product_qty_hid' id='product_qty_hid' value='<?=$pending_qty;?>' />
										<input type='hidden' name='process_id_hid' id='process_id_hid' value='<?=$process_id;?>' />
										<input type='hidden' name='process_type_hid' id='process_type_hid' value='<?=$process_type;?>' />
										<input type='hidden' name='product_qc' id='product_qc' value='<?=$qc_st;?>' />
										<input type='hidden' name='process_unit' id='process_unit' value='<?=$rel["process_unit"]?>' />
										<input type='hidden' name='request_no' id='request_no' value='<?=$rel['p_ref_id'];?>' />
										
										
										<input type='hidden' name='alloc_id' id='alloc_id' value='<?=$rel['p_id'];?>' />
										<input type='hidden' name='pre_alloc_id' id='pre_alloc_id' value='<?=$rel['previous_process_id'];?>' />
										<input type='hidden' name='jobwork_id' id='jobwork_id' value='<?=$rel['jobwork_id'];?>' />
										<input type='hidden' name='resource_id' id='resource_id' value='<?=$rel['resource_id'];?>' />
										
										<input type='hidden' name='grn_no' id='grn_no' value='' />
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
			 include_once('../include/scrap_entry.php');
			include_once('../include/include_js_file.php');
		?>   
			<script src="<?=ROOT?>js/app/allocate_process_end.js"></script>
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

	</body>
</html>