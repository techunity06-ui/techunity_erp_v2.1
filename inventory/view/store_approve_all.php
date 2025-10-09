<?php 
session_start();
include('../include/urlfile.php');
$form="Store Approve All";
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QC_DONE_CREATE,
    QC_DONE_EDIT,
    QC_DONE_PURCHASE_QC_PENDING_ADD,
    QC_DONE_PARTS_QC_PENDING_ADD,
]);

$count_batch = 0;
$batch_id = 0;
// print_r($_POST);
if (is_array($_POST) && !empty($_POST)) {
    $count_batch = count($_POST);
    $batch_id = $_POST['all_batch_id'];
}

$back_link = "";

$display = "";

$back_link = ROOT.INVENTORY_ROOT.'store_receive_pending_list_new';
$back_title = "Store Approval Pending List";

 $query="SELECT sr.grn_trn_id, p.product_name,p.product_mat_center as product_godown, p.product_icode,batch.grn_godown, batch.grn_date, batch.batch_no, grn.grn_no, gda.gd_name, batch.product_id,batch.process_id, umst.unit_name, batch.batch_id, batch.batch_qty, batch.accept_qty, batch.reprocess_qc, batch.batch_unit, batch.to_godown_id, qc.qc_no, qc.qc_date, batch.grn_trn_id, batch.grn_id, batch.qc_id, batch.order_no, batch.base_unit,batch.conv_unit, sr.product_qty,sr.product_conv_qty FROM tbl_batch_data as batch left join tbl_grn_trn as sr on sr.grn_trn_id=batch.grn_trn_id left join product_mst as p on p.product_id=batch.product_id left join tbl_grn as grn on grn.grn_id=sr.grn_id left join unit_mst as umst on umst.unitid=batch.batch_unit left join mst_godown as gda on gda.gd_id=sr.grn_godown left join tbl_qc_reject_new_product as rej_qc on rej_qc.batch_id = batch.batch_id and rej_qc.qc_id = batch.qc_id and rej_qc.product_id = batch.product_id left join tbl_qc as qc on qc.qc_id = batch.qc_id where batch.batch_id in(".$batch_id.") ORDER BY batch.batch_id desc";

	$result = $dbcon->query($query);
	$count_batch = brp_mysqli_num_rows($result);	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Store Approve All</title>
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
									<form class="form-horizontal" role="form" id="store_add" action="javascript:;" method="post" name="store_add">
										<div class="row">
											<div class="col-md-12" style="margin-top: 20px;margin-bottom: 20px;overflow-x: auto; ">
												<!--<div id="qc_productdata"></div>
												table-bordered table_stripped-->
												<table class='table '>
													<tr  style="background-color: #c4cac8;color: #0e0e0e;">
														<!--<th style='width:5%;' >#</th>-->
														<th style='width:5%;text-align: center;'>SrNO. </th>
														<th style='width:12%;text-align: center;'>Ref. No.</th>
														<th style='width:7%;text-align: center;'>Ref. Date</th>
														<th style='width:10%;text-align: center;'>Batch NO </th>
														<th style='width:12%;text-align: center;'>Product Name</th>
														<th style='width:10%;text-align: center;'>Process Name</th>
														<th style='width:8%;text-align: center;'>Batch Qty</th>
														<th style='width:8%;text-align: center;'> Qty</th>
														<th style='width:14%;text-align: center;'> Godown *</th>
														<th style='width:14%;text-align: center;'> Remark </th>
													</tr>
													<?php 
														$cnt=1;
	
													?>

													<?phpif($count_batch == 0) {
														echo '<tr>
														<td colspan="12">
															<div class="text-center"> <h3> No QC Data Found. </h3></div>	
														</td>';
													} else { 
														$x = 1;
														while($row = brp_mysqli_fetch_assoc($result)){
														$godown_id = 0;	
														if($row['qc_id'] > 0){
															$trn_qry = "SELECT qc_reject_godown as bt_godown_id FROM tbl_qc_trn where qc_status = 0 AND qc_id = " . $row['qc_id'];
														
														}else if($row['qc_id'] == '0'){
															$trn_qry = "SELECT qc_godown as bt_godown_id FROM tbl_qc where qc_status = 0 AND batch_id = " . $row['batch_id'];
														}

														$qc_result = $dbcon->query($trn_qry);
														$qc_cnt = brp_mysqli_num_rows($qc_result);

														if($qc_cnt > 0){
															$qc_row = brp_mysqli_fetch_assoc($qc_result);
															$godown_id = $qc_row['bt_godown_id'];	
														}else{
															$godown_id = $row['grn_godown'];
														}

														
														if(!empty($row['product_godown'])){
															$godown_id = $row['product_godown'];
														}
														$diff_qty = 0;	
														if($row['base_unit'] == $row['conv_unit']){
															$diff_qty = $row['accept_qty'] . ' ' . $row['unit_name'];	
														}else if($row['batch_unit'] == $row['conv_unit']){
															// $base_stock=convert_stock($dbcon,$row['accept_qty'],$row['product_id'],"base_unit");
															$base_stock=($row['accept_qty']/$row['product_conv_qty']) * $row['product_qty'];
															$diff_qty = $base_stock . ' ' . getunitname($dbcon,$row['base_unit']);	
														}else{
															// $conv_stock=convert_stock($dbcon,$row['accept_qty'],$row['product_id'],"conv_unit");
															 $conv_stock=($row['accept_qty']/$row['product_qty']) * $row['product_conv_qty'];
															$diff_qty = $conv_stock . ' ' . getunitname($dbcon,$row['conv_unit']);	
														}
													 ?>


													<tr class="text-center">
														<td><?=$x?></td>
														<td><?=$row['grn_no']?></td>
														<td><?=date("d-M-Y",strtotime($row['grn_date']));?></td>
														<td><?=$row['batch_no']?></td>
														<td><?=$row['product_name']?></td>
														<td><?=get_process_name($dbcon, $row['process_id'])?></td>
														<!--<td ><?=$row['unit_name']?></td>-->
														<td><?=$row['accept_qty'].' '.$row['unit_name']?>
															<input type="hidden" class="accept_qty" name="accept_qty[]" id="accept_qty" value="<?=$row['accept_qty']?>">
														</td>
															
														<td><?=$diff_qty;?>
														</td>
														<td>
															<select class="form-control select2" name="godown_id[]" id="godown_id<?=$x?>">
																<?=get_all_godown($dbcon,$godown_id);?>
															</select>
														</td>
														<td>
															<textarea class="form-control" rows="3" id="remark<?=$x?>" name="remark[]"> </textarea>
														</td>
													</tr>
													<input type="hidden" class="batch_id<?=$x?>" name="batch_id[]" id="batch_id<?=$x?>" value="<?=$row['batch_id']?>">
													<input type="hidden" class="batch_no<?=$x?>" name="batch_no[]" id="batch_no<?=$x?>" value="<?=$row['batch_no']?>">
													<input type="hidden" class="reprocess_qc<?=$x?>" name="reprocess_qc[]" id="reprocess_qc<?=$x?>" value="<?=$row['reprocess_qc']?>">
													<input type="hidden" class="grn_trn_id<?=$x?>" name="grn_trn_id[]" id="grn_trn_id<?=$x?>" value="<?=$row['grn_trn_id']?>">
													<input type="hidden" class="product_id<?=$x?>" name="product_id[]" id="product_id<?=$x?>" value="<?=$row['product_id']?>">
													<input type="hidden" class="batch_unit<?=$x?>" name="batch_unit[]" id="batch_unit<?=$x?>" value="<?=$row['batch_unit']?>">

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
										<div class="row" id="final_submit_div">	
											<div class="col-md-12">
												<center>
												<button type="button" onclick="check_validation_all()" class="btn btn-success" id="save" name="save">Store Approved All
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
			<script src="<?=ROOT.INVENTORY_ROOT?>js/app/store_approve_all.js?<?=time()?>"></script>
			
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
