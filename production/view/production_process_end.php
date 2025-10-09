<?php 
	//error_reporting(E_ALL);
	session_start();
	include('../include/urlfile.php');
	$form="End Process Allocation";
	$countryid='101';$stateid='1';$cityid='1';
	
	$mode="end_process";
	$_REQUEST['id']=urldecode($_REQUEST['id']);
	$p_id=$dbcon->real_escape_string($_REQUEST['id']);
	
	$company_config = getCompanyConfiguration($dbcon);	
	
	$branch_id = $_SESSION['branch_id'];
	if(!empty($edit_branch_id)){
		$branch_whre=" and ap.branch_id=".$edit_branch_id;
	}
	
	$query="select p.product_name,pr.process_name,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version,smain.sp_id as work_order_id,ap.batch_no  from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join tbl_request_product req on req.rp_id=ap.p_ref_id
				left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
			where p_status=1 and ap.p_id in (".$p_id.")";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	$select_branch_id=$rel['branch_id'];

	$work_order_id = $rel['work_order_id'];
		
	$batch_query = "select * from tbl_batch_data where grn_id = '$work_order_id'";
	$batch_result=$dbcon->query($batch_query);
	if(mysqli_num_rows($batch_result)>0)
	{
		$batch_data = array();
		while($batch_row = brp_mysqli_fetch_array($batch_result))
		{
			$batch_data[] = $batch_row['batch_no'];
		}
		
		$batches_data = implode(",",$batch_data);
	}
	
	/* $query="select ap.*,p.product_name,p.product_type,p.product_setting_check,pr.process_name,group_concat(ap.p_id ORDER BY ap.p_id ASC) as allo_id,group_concat(ap.p_ref_id ORDER BY ap.p_id ASC) as p_ref_id1 from tbl_allocate_process as ap 
	left join product_mst as p on p.product_id=ap.p_product_id 
	left join process_mst as pr on pr.process_id=ap.process_id 
	left join tbl_jobwork as j on j.j_alloc_process_id=ap.p_id
	where ap.process_id=".$process_id." and ap.p_product_id=".$product_id." ".$branch_whre." and ap.company_id=".$_SESSION['company_id']." and ap.p_status=1 and pr_process_type=".$process_type." group by ap.p_product_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query)); */
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$qc_paramter_info = check_product_qc_paramter($dbcon,$rel['p_product_id'],$rel['process_id']);
	if($qc_paramter_info=='1')
	{
		$qc_st="yes";
		$sty="display:none;";
	}else{
		$qc_st="no";
		$sty="";
	}
	$pno=load_series_no_using_type_id($dbcon,INHOUSE_GRN,$_SESSION['company_id'],$branch_id1);
	$working_qty=production_end_count_using_p_id($dbcon,$p_id);
	
	


?>

<!DOCTYPE html>
<html lang="en">
	<head>
	<title>End Process</title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
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
									  <li><a href="<?=ROOT.PRODUCTION_ROOT.'process_detail_list/'.$rel['process_id'].'/'.$rel['pr_process_type'];?>">Process  List</a></li>
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
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Product Name </label>
												<div class="col-md-6 col-xs-11" style="color: #0e8400;font-weight: 600;">
													<?=$rel['product_name']?>
													<!--<input type="text" class="form-control" id="pr_product_id" name="pr_product_id" value="<?php //=$rel['product_name']; ?>" readonly />-->
												</div>
											</div>
											<div class="col-md-4">
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Process Name </label>
												<div class="col-md-6 col-xs-11" style="color: #c71313;font-weight: 600;">
													<?=$rel['process_name']; ?>
													<!--<input type="text" class="form-control" id="pr_process_id" name="pr_process_id" value="<?php //=$rel['process_name']; ?>" readonly />-->
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">  	
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Pending Qty</label>
													<div class="col-md-6 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
														<?=$working_qty;?> <?=$rel['unit_name']?>
													</div>
												</div>	
											</div>
											<div class="col-md-12"></div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">End Process Time</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" id="pr_end_time1" name="pr_end_time1" value="<?=date('d-m-Y h:i:sa'); ?>" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Process No</label>
													<div class="col-md-6 col-xs-11">
														<input type="text" class="form-control" id="process_no" name="process_no" value="<?=$pno; ?>" readonly />
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Stop Qty *</label>
													<div class="col-md-4 col-xs-11">
														<input type="text" name="stop_qty" id="stop_qty" class="form-control" onkeyup="show_material_list()" value="<?=$working_qty?>" /> 
													</div>
													<div class="col-md-1" style="font-size: 18px;font-weight: 600;color: #d02424;">
														<?=$rel['unit_name']?>
													</div>
												</div>
											</div>
											<div class="col-md-12"></div>
											<div class="col-md-4" style='<?=$sty?>' >
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Godown *</label>
													<div class="col-md-6 col-xs-11">
														<select class='form-control' name='grn_godown'  id='grn_godown' required >
															<?=get_all_godown($dbcon,'',1);?>
														</select>
													</div>
												</div>
											</div>
											<div class="col-md-4">
												<?php echo getBranchBox($dbcon, $branch_id,$select_branch_id, true, true, 'show_material_list()'); ?>
											</div>
											<?php if($company_config['batch_wise_stock'] == '1'){ 

												$readonly = "readonly";
												$txt_batch_id = "batch_no";
												$batch_no = $rel['batch_no'];

												 if($company_config['batch_process'] == '1'){
													
													if($company_config['batch_stock'] == '0'){
														$readonly = "";	
													 	$txt_batch_id = "batch_man_no";
													 }else{
													 	$batch_no = get_batch_no($dbcon,$rel['p_product_id']);
													 }	 	
												 }



												if($company_config['batch_type']=='0') {
														$lbl_batch ='Batch No';
													}else{
														$lbl_batch ='Serial No';
													}	

												?>	
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"><?=$lbl_batch;?>*</label>
													<div class="col-md-6 col-xs-11">
														<input id="<?=$txt_batch_id?>" name="<?=$txt_batch_id?>" type="text" class="form-control" title="Enter <?=$lbl_batch?>" value="<?= $batch_no?>" placeholder="<?=$lbl_batch?>" required <?=$readonly?>>
													</div>
												</div>
											</div>

										<?php } ?>
											<div class="col-md-4" style="display:none;">
												<div class="col-md-4"> </div>
												<div class="col-md-4" >
													<button type="button" class="btn  btn-success" data-original-title="Alloca" data-toggle="tooltip" data-placement="top" onClick="open_scrap_entry()">Scrap Entry</button>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Scrap Product</label>
													<div class="col-md-6 col-xs-11">
														<select class="form-control select2" name="product_scrap_id" id="product_scrap_id" onChange="get_scrap_unit(this.value)">
						                                  <?=getScrapCode($dbcon,$id)?>
						                                 </select>
													</div>
												</div>
											</div>
											<div class="col-md-12 mtop20 scrap_row" style="display:none;">
					<div class="col-md-4 ">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Scrap Unit</label>
							<div class="col-md-8 col-xs-11">
								<select class="form-control select2" name="scrap_unit" id="scrap_unit">
                                  <option value="">Choose Scrap Unit</option>
                                 </select>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Scrap Qty</label>
							<div class="col-md-8 col-xs-11">
								<input type="number" class="form-control" id="scrap_qty" name="scrap_qty" value="">
							</div>
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
													<input type="submit" id="sp_btn" name="submit" class="btn btn-success" value="End Process" />
												</div>
											</div>		
										</div>
										<!--pathik start -->
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type="hidden" name="max_available_qty" id="max_available_qty" value="<?=$working_qty?>" />
											<input type="hidden" id="redirect_page" name="redirect_page" value="<?=$_SESSION['redirect_page']?>">
											<input type="hidden" id="pending_qty" name="pending_qty" value="<?=$working_qty;?>">
											<input type='hidden' name='p_id' id='p_id' value='<?=$p_id;?>' />
											<input type="hidden" name="product_base_unit" id="product_base_unit" value="<?=$rel['process_unit']?>" />
											<input type="hidden" name="branch_id" id="branch_id" value="<?=$select_branch_id?>" />
											<input type="hidden" name="product_id" id="product_id" value="<?=$rel['p_product_id']?>" />
											<input type="hidden" name="process_id" id="process_id" value="<?=$rel['process_id']?>" />
										
										<!--pathik end -->
										
									</form>
								</div>	
							</section>
						</div>
					</div>
				</section>
			</section>
		</section>
		<?php
			include_once($include1.'get_warehose_deduction_modal.php');
			include_once($include1.'scrap_entry.php');
			include_once($include.'include_js_file.php');
		?>   
			<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/production_process_end.js"></script>
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