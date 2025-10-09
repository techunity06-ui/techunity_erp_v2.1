<?php 
session_start();
include('../include/urlfile.php');
$form="QC All";
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QC_DONE_CREATE,
    QC_DONE_EDIT,
    QC_DONE_PURCHASE_QC_PENDING_ADD,
    QC_DONE_PARTS_QC_PENDING_ADD,
]);

$count_batch = 0;
$batch_id = 0;

if (is_array($_POST) && !empty($_POST)) {
    $count_batch = count($_POST);
    $batch_id = $_POST['qc_all_batch_id'];
}

$back_link = "";

$display = "";


if(strpos($_SERVER[REQUEST_URI], "purchase_qc_all")==true){
	$display = 'display:none;';
	$back_link = PURCHASE_ROOT.'purchase_qc_pending_list/';
	$back_title = "Purchase QC Pending List";
} else if(strpos($_SERVER[REQUEST_URI], "qc_all")==true){
	
	$product_qc = 1;
	$back_link = PRODUCTION_ROOT.'parts_qc_pending_list/';
	$back_title = "Parts QC Pending List";
} 

$company_config = getCompanyConfiguration($dbcon);

$qc_unit_on = $company_config['qc_unit'];

$query="select batch.batch_id,batch.batch_no,batch.process_id,batch.batch_qty,batch.grn_accept_qty,batch.base_qty,gt.product_id,p.product_type,p.product_name,p.revision_id,g.ref_type,g.grn_no,batch.base_qty,batch.conv_qty,g.grn_date,g.qc_status,g.grn_status,g.ref_type,gt.grn_trn_id,gt.grn_id,gt.unit_id,umst.unit_name,cmst.unit_name as conv_unit_name,gt.po_ref_id,gt.branch_id,gt.product_conv_unit as conv_unit_id, branch.branch_name,p.product_icode, dr.drawing_number,batch.batch_unit from tbl_batch_data as batch
	left join tbl_grn_trn as gt on gt.grn_trn_id = batch.grn_trn_id
	left join unit_mst as umst on umst.unitid=batch.base_unit
	left join unit_mst as cmst on cmst.unitid=batch.conv_unit
	left join tbl_grn as g on g.grn_id=gt.grn_id 
	left join product_mst as p on p.product_id=gt.product_id 
	left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
	left join tbl_category as tc on p.product_category=tc.cat_id
	left join branch_mst as branch on branch.branch_id=gt.branch_id
	where batch.batch_id in(".$batch_id.")";

	$result = $dbcon->query($query);
	$count_batch = brp_mysqli_num_rows($result);	

	$qc_work_type = '';
	if(isset($_SESSION['qc_work_type']) && $_SESSION['qc_work_type']!=''){
		$qc_work_type = $_SESSION['qc_work_type'];
	}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Qc All</title>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			td {
				color: black;
			}
		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3> <?=$form .' '.$mode?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li ><a href="<?=$back_link?>"><?=$back_title?></a></li>
									  <li ><?=$form?> List</li>
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
									<form class="form-horizontal" role="form" id="qc_add" action="javascript:;" method="post" name="qc_add">
										<div class="row">
											<div class="col-md-12" style="margin-top: 20px;margin-bottom: 20px;overflow-x: auto; ">
												<!--<div id="qc_productdata"></div>
												table-bordered table_stripped-->
												<table class='table '>
													<tr style="background-color: #c4cac8;color: #0e0e0e;">
														<!--<th style='width:5%;' >#</th>-->
														<th style='width:5%;text-align: center;'>SrNO. </th>
														<th style='width:11%;text-align: center;'>Batch NO </th>
														<th style='width:11%;text-align: center;'>Product Name</th>
														<th style='width:8%;text-align: center;<?=$display?>'>Process Name</th>
														<th style='width:6%;text-align: center;'>Total Qty</th>
														
														<th style='width:7%;text-align: center;'>Accepted Qty</th>
														<th style='width:8%;text-align: center;'>Accepted Godown *</th>
														<th style='width:7%;text-align: center;'>Rejected Qty</th>
														<th style='width:8%;text-align: center;'>Rejected Godown *</th>
														<th style='width:8%;text-align: center;'> New Product </th>
														<th style='width:8%;text-align: center;'> New Unit </th>
														<th class="<?=$dper?>" style='width:7%;text-align: center;<?=$display?>'>Reprocess Qty</th>
														<th class="<?=$dper?>" style='width:8%;text-align: center;<?=$display?>'>Reprocess Godown *</th>
														<th class="<?=$dper?>" style='width:8%;text-align: center;<?=$display?>'> New Process </th>
													
													</tr>
													<?php 
														$cnt=1;
	
													?>

													<?php if($count_batch == 0) {
														echo '<tr>
														<td colspan="12">
															<div class="text-center"> <h3> No QC Data Found. </h3></div>	
														</td>';
													} else { 
														$x = 1;
														while($row = brp_mysqli_fetch_assoc($result)){
														$current_product_id = $row['product_id'];
														$current_process_id	= $row['process_id'];
														$allocate_process_ids	=find_qc_process_ids($dbcon,$row['grn_trn_id']);
														if($qc_unit_on == '1'){
															$batch_qty = $row['base_qty'];
														}else{
															$batch_qty = $row['conv_qty'];
														}
														$process_read_only = "";
														if($row['grn_type'] == '2'){
															$process_read_only = "readonly";
														}
													 ?>


													<tr>
														<td class="qc_detail_head<?=$x?>"><?=$x?></td>
														<td class="qc_detail_head<?=$x?>"><?=$row['batch_no']?></td>
														<td class="qc_detail_head<?=$x?>"><?=$row['product_name']?></td>
														<td class="qc_detail_head<?=$x?>" style="<?=$display?>"><?=get_process_name($dbcon, $row['process_id'])?></td>
														<!--<td  class="qc_detail_head<?=$x?>"><?=$row['unit_name']?></td>-->
														<td class="qc_detail_head<?=$x?>"><?=$row['base_qty']?> <?=$row['unit_name']?><br>
															<?=$row['conv_qty']?><?=$row['conv_unit_name']?>
															<input type="hidden" class="total_pending_qty" name="total_pending_qty" id="total_pending_qty" value="<?=$row['product_qty']?>">
														</td>
														<td class="qc_detail_head<?=$x?>">
															<input type='text' class='form-control' name='qty_accept[]' id='qty_accept<?=$x?>' value='<?=$batch_qty?>' onkeyup='sub_accept_value("<?=$x?>")' />
															<strong id='qty_error' style='color:red'></strong>
															<input type='hidden' class='form-control' name='' id='qty_accept_hid<?=$x?>' value='<?=$batch_qty?>' />
										                </td>
														
														<td class="qc_detail_head<?=$x?>">
															<select class="form-control select2" name="qc_godown[]" id="qc_godown<?=$x?>">
																<?=get_all_godown($dbcon,'');?>
															</select>
														</td>
														<td class="qc_detail_head<?=$x?>">
															<input type='text' class='form-control' name='qty_reject[]' id='qty_reject<?=$x?>' value='0' onkeyup='sub_accept_value("<?=$x?>")' />
															<strong id='qty_error_reject' style='color:red'></strong>
														</td>
														<td class="qc_detail_head<?=$x?>">
															<select class="form-control select2" name="qc_reject_godown[]" id="qc_reject_godown<?=$x?>"  >
																<?=get_all_godown($dbcon,'');?>
															</select>
														</td>
														<td class="qc_detail_head<?=$x?>">
															<input id="new_product_id<?=$x?>" class="new_product_id" name="new_product_id[]" placeholder="Select product" onchange="load_new_product_unit(this.value,<?=$x?>)"/>
														</td>
															
														<td class="qc_detail_head<?=$x?>">
															<select class="form-control select2" name="new_product_unit[]" id="new_product_unit<?=$x?>">
																<option value="">Select Unit</option>
															</select>
														</td>
														<td class="qc_detail_head<?=$x?>" style="<?=$display?>">															<input type='text' class='form-control' name='qty_reprocess[]' id='qty_reprocess<?=$x?>' onkeyup='sub_accept_value("<?=$x?>")'>						<strong id='qty_error_reprocess' style='color:red'></strong>
														</td>
														<td class="qc_detail_head<?=$x?>" style="<?=$display?>">
															<select class="form-control select2" name="qc_reporcess_godown[]" id="qc_reporcess_godown<?=$x?>">
																<?=get_all_godown($dbcon,'');?>
															</select>
														</td>
														
														<td class="qc_detail_head<?=$x?>" style="<?=$display?>">
															<select class="form-control select2" name="new_process[]" id="new_process<?=$x?>">
																<?=get_products_current_and_previous_process($dbcon, $current_product_id, $current_process_id);?>
															</select>
														</td>
													</tr>
													<input type="hidden" class="batch_id<?=$x?>" name="batch_id[]" id="batch_id<?=$x?>" value="<?=$row['batch_id']?>">

											<input type='hidden' class='' name='grn_product[]' id="grn_product<?=$x?>" value='<?=$row['product_id']?>' />
											<input type='hidden' class='form-control' name='grn_type[]' id='grn_type<?=$x?>' value='<?=$row['ref_type']?>' />
											<input type='hidden' class='form-control' name='qc_unit_id[]' id='qc_unit_id<?=$x?>' value='<?=$row['batch_unit']?>' />
											<input type='hidden' class='form-control' name='qc_base_unit_id[]' id='qc_base_unit_id<?=$x?>' value='<?=$row['unit_id']?>' />
											<input type='hidden' class='form-control' name='qc_conv_unit_id[]' id='qc_conv_unit_id<?=$x?>' value='<?=$row['conv_unit_id']?>' />
											<input type='hidden' class='form-control' name='grn_pqty[]' id='grn_pqty<?=$x?>' value='<?=$batch_qty?>' />
											<input type='hidden'  name='grn_no[]' id='grn_no<?=$x?>' value='<?=$row['grn_id']?>' />
											<input type='hidden'  name='po_ref_id[]' id='po_ref_id<?=$x?>' value='<?=$row['po_ref_id']?>' />

											<input type='hidden' name='grn_trn_id[]' id='grn_trn_id<?=$x?>' value='<?=$row['grn_trn_id']?>' />

											<input type='hidden' name='allocate_process_ids[]' id='allocate_process_ids<?=$x?>' value='<?=$allocate_process_ids?>' />

											<input type='hidden' name='grn_id[]' id='grn_id' value='<?=$row['grn_id']?>' />
											
											<input type="hidden" name="current_process_id[]" id="current_process_id<?=$x?>" value="<?=$current_process_id?>" />

											
											<input type="hidden" name="process_show[]" id="process_show<?=$x?>" value="<?=$process_show?>">
											
											<input type="hidden" name="qc_work_type[]" id="qc_work_type<?=$x?>" value="2">
											<input type="hidden" name="branch_id[]" id="branch_id<?=$x?>" value="<?=$row['branch_id'];?>">


												<?php
												$x++; 
												}

											} 

											
												?>
												</table>
											</div>
										</div>
										<input type='hidden' name='mode' id='mode' value='add' />
											<input type="hidden" name="back" id="back" value="<?=$back_link?>" />
					<input type="hidden" name="session_qc_work_type" id="session_qc_work_type" value="<?=$qc_work_type?>">
										<div class="row" id="final_submit_div">	
											<div class="col-md-12">
												<center>
												<button type="button" onclick="check_validation_qc_all()" class="btn btn-success" id="save" name="save">Submit QC All
												</button>
												<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>	
										</div>
										
									</form>
								</div>
								
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
			<?php include_once($include.'include_js_file.php');?>   
			<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/qc_all.js?<?=time()?>"></script>
			
			<script>
				$(".select2").select2({
					width: '100%',
				});
				
				$("#product_id").select2({
					width: '83%'
				});
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
			</script>
	</body>
</html>
