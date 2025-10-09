<?php 
session_start();
include('../include/urlfile.php');	
$form="QC Add";
$countryid='101';
$stateid='1';
$cityid='1';
$reprocess_type = '';
//check permission for qa done details
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QC_DONE_CREATE,
    QC_DONE_EDIT,
    QC_DONE_PURCHASE_QC_PENDING_ADD,
    QC_DONE_PARTS_QC_PENDING_ADD,
]);
	$current_process_id = '';
	if(strpos($_SERVER[REQUEST_URI], "qc_edit")==true){
		if(!in_array(QC_DONE_EDIT,$bulkAccessArray)) {
			header("Location: ".DOMAIN."permission_access");
		}
		$mode="Edit";
		$qc_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_qc where qc_id=$qc_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$date=date('d-m-Y',strtotime($rel['qc_date']));
		$back="qc_done_list";
		$process_show=0;
	}else if(strpos($_SERVER[REQUEST_URI], "poqc_add")==true){
		/* if(!in_array(QC_DONE_PURCHASE_QC_PENDING_ADD,$bulkAccessArray)) {
			header("Location: ".DOMAIN."permission_access");
		} */
		$mode="Add";
		$date=date('d-m-Y');
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));
		$id = $dbcon->real_escape_string($_REQUEST['id']);
		$back="purchase_qc_pending_list";
		$process_show=0;
		$current_process_id		="-1";
		
	}else if(strpos($_SERVER[REQUEST_URI], "joqc_add")==true){
		/* if(!in_array(QC_DONE_PARTS_QC_PENDING_ADD,$bulkAccessArray)) {
			header("Location: ".DOMAIN."permission_access");
		} */
		$mode="Add";
		$date=date('d-m-Y');
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));
		$id = $dbcon->real_escape_string($_REQUEST['id']);
		$process_id = $dbcon->real_escape_string($_REQUEST['process_id']);
		if(!empty($process_id)){
			$back="parts_qc_pending_list/".$process_id;
		}else{
			$back="parts_qc_pending_list";
		}
		$process_show=1;
		/* $sel=$dbcon->query("select po.process_name,g.grn_id,po.process_id from tbl_grn_trn as gt 
			left join tbl_grn as g on g.grn_id=gt.grn_id
			left join tbl_jobwork as jo on jo.jobwork_id=g.purchaseorder_id
			left join tbl_allocate_process as aol on aol.p_id=jo.j_alloc_process_id
			left join process_mst as po on po.process_id=aol.process_id
			where gt.grn_trn_id='$id'"); */
			
			$sel=$dbcon->query("select po.process_name,g.grn_id,po.process_id from tbl_grn_trn as gt 
			left join tbl_grn as g on g.grn_id=gt.grn_id
			left join process_mst as po on po.process_id=gt.process_id
			where gt.grn_trn_id='$id'");
			
			$row=mysqli_fetch_assoc($sel);
			
			$process_name			=$row['process_name'];
			$grn_id					=$row['grn_id'];
			$current_process_id		=$row['process_id'];
			//echo $revision_id		=$row['revision_id'];
			$allocate_process_ids	=find_qc_process_ids($dbcon,$id);
	}else{
		/*if(!in_array(QC_DONE_CREATE,$bulkAccessArray)) {
			header("Location: ".DOMAIN."permission_access");
		}*/
		$mode="Add";
		$date=date('d-m-Y');
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));
		$id = $dbcon->real_escape_string($_REQUEST['id']);
		$back="qc_done_list";
		$process_show=0;
	}

	if($row['ref_type']!="1") { 
	 	//$dper="display:none";
	}
	
	if($process_show!="1") { 
	 	$dper="hide";
	}
	
	$que="select gt.product_id,p.product_type,p.product_name,p.revision_id,g.ref_type,g.grn_no,gt.product_qty,g.grn_date,g.qc_status,g.grn_status,g.ref_type,gt.grn_trn_id,gt.grn_id,gt.unit_id,umst.unit_name,gt.po_ref_id,gt.branch_id from tbl_grn_trn as gt 
	left join unit_mst as umst on umst.unitid=gt.unit_id
	left join tbl_grn as g on g.grn_id=gt.grn_id 
	left join product_mst as p on p.product_id=gt.product_id 
	left join tbl_category as tc on p.product_category=tc.cat_id
	where gt.grn_trn_id=".$id;
	$sel=$dbcon->query($que);
	$row=mysqli_fetch_assoc($sel);

	$branch_id = $_SESSION['branch_id'];
	$edit_branch_id = $row['branch_id'];
	$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
	
	$qc_work_type = '';
	if(isset($_SESSION['qc_work_type']) && $_SESSION['qc_work_type']!=''){
		$qc_work_type = $_SESSION['qc_work_type'];
	}
	$revision_id		=$row['revision_id'];
	$current_product_id = $row['product_id'];
	
	$set_conf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
	$set_conr=mysqli_fetch_assoc($dbcon->query($set_conf));
?>

<!DOCTYPE html>
<html lang="en">
	<head>
	<title>Qc Add</title>
		<?php include_once($include.'/include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'/include_top_menu.php');?>
			<?php include_once($include.'/left_menu.php');?>
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
									  <li ><a href="<?=ROOT.PRODUCTION_ROOT.$back?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									<?if($mode=="Bom"){ echo "Sales Order Bom";}else{?>New <?=$form?><?} ?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="qc_add" action="javascript:;" method="post" name="qc_add">
										<div class="row">
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>QC No *</strong></label>
														<div class="col-md-6 col-xs-11">
															<input id="qc_no" name="qc_no" type="text" class="form-control" title="Enter Planning No" value="<?=$rel['qc_no']; ?>" placeholder="QC No" required readonly >
														</div>
													</div>
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label"><strong>QC Date*</strong></label>
														<div class="col-md-6 col-xs-11">
															<input id="qc_date" name="qc_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?php if($mode=='Edit'){ date("d-m-Y",strtotime($rel['qc_date'])); } else { echo  date("d-m-Y"); }  ?>" placeholder="Date" >
														</div>
													</div>
												</div>
												<?phpif($process_show==1){ ?>
													<div class="col-md-4">
														<div class="form-group">
															<label class="col-md-4  control-label"><strong>Process Name</strong></label>
															<div class="col-md-6 col-xs-11" style="font-size:16px;color:red;">
																<strong><?=$process_name?></strong>
															</div>
														</div>
													</div>
												<?php}else{ ?>
													<div class="col-md-4">
														<div class="form-group">
															<?php echo getBranchBox($dbcon, $branch_id,$edit_branch_id, true, true, 'load_purchase_qc_pending_datatable()', '4', '6'); ?>
														</div>
													</div>
											<?	} ?>
											</div>
											<div class="col-md-12">
											 <?phpif($process_show==1){ ?>
												<div class="col-md-4">
													<div class="form-group">
														<?php echo getBranchBox($dbcon, $branch_id,$edit_branch_id, true, true, 'load_purchase_qc_pending_datatable()',  '4', '6'); ?>
													</div>
												</div>
											 <?php} ?>
											</div>
											<!--<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4  control-label"><strong>Select Godown*</strong></label>
														<div class="col-md-6 col-xs-11">
															<select class="form-control" name="qc_godown" id="qc_godown" required>
																<?=get_all_godown($dbcon,'');?>
															</select>
														</div>
													</div>
												</div>
											</div>-->
											<div class="col-md-12" style="margin-top: 20px;margin-bottom: 20px;overflow-x: auto; ">
												<!--<div id="qc_productdata"></div>
												table-bordered table_stripped-->
												<table class='table '>
													<tr style="background-color: #c4cac8;color: #0e0e0e;">
														<!--<th style='width:5%;' >#</th>-->
														<th style='width:11%;text-align: center;'>Product Name</th>
														<th style='width:8%;text-align: center;'>Product Category</th>
														<!--<th style='width:8%;text-align: center;'>Unit</th>-->
														<th style='width:6%;text-align: center;'>Total Qty</th>
														<th style='width:8%;text-align: center;'>QC</th>
														<th style='width:8%;text-align: center;'>Add QC Param.</th>
														<th style='width:7%;text-align: center;'>Accepted Qty</th>
														<th style='width:8%;text-align: center;'>Godown *</th>
														<th style='width:7%;text-align: center;'>Rejected Qty</th>
														<th style='width:8%;text-align: center;'>Godown *</th>
														<th style='width:8%;text-align: center;'> New Product </th>
														<?php//if($row['ref_type']=="1"){ ?>
														<th class="<?=$dper?>" style='width:7%;text-align: center;'>Reprocess Qty</th>
														<?php//} ?>
														<th class="<?=$dper?>" style='width:8%;text-align: center;'>Godown *</th>
														<th class="<?=$dper?>" style='width:8%;text-align: center;'> New Process </th>
													</tr>
													<?
														$cnt=1;
	
													?>
													<tr>
														<td class="qc_detail_head"><?=$row['product_name']?>
															<?phpif(!empty($revision_id)) { ?>
															<a class="btn btn-xs btn-info" title="View Image" data-toggle="tooltip" data-id="<?=$revision_id?>" data-placement="top" href="javascript:void(0)" onClick="view_revision_image(<?=$revision_id?>)"><i class="fa fa-eye"></i></a>
															<?php} ?>
														</td>
														<td class="qc_detail_head"><?=$cat_name?></td>
														<!--<td  class="qc_detail_head"><?=$row['unit_name']?></td>-->
														<td class="qc_detail_head"><?=$row['product_qty']?> <?=$row['unit_name']?>
															<input type="hidden" name="total_pending_qty" id="total_pending_qty" value="<?=$row['product_qty']?>">
														</td>
														<td class="qc_detail_head">
															<select class="form-control" name="qc_work_type" id="qc_work_type" onChange="">
															  <option value="">--Select Type--</option>	
										                     <option value="1" <?php if($qc_work_type=='1'){ ?> selected <?php } ?>>All</option>
										                     <option value="2" <?php if($qc_work_type=='2'){ ?> selected <?php } ?>>Any One</option>
										                  </select>
										                </td>
														<td class="qc_detail_head">
															<a class="btn btn-primary" title="Add QC Param" data-toggle="tooltip" data-placement="top" href="javascript:void(0)" onclick="manage_qc_work_type();"><i class="fa fa-plus"></i></a>
														</td>
														<td class="qc_detail_head">
															<input type='text' class='form-control' name='qty_accept' id='qty_accept' value='<?=$accept?>' onkeyup='sub_accept_value()' readonly />
															<strong id='qty_error' style='color:red'></strong>
														</td>
														<td class="qc_detail_head">
															<select class="form-control" name="qc_godown" id="qc_godown" required >
																<?=get_all_godown($dbcon,'');?>
															</select>
														</td>
														<td class="qc_detail_head">
															<input type='text' class='form-control' name='qty_reject' id='qty_reject' value='<?=$reject?>' onkeyup='sub_accept_value()' readonly />
															<strong id='qty_error_reject' style='color:red'></strong>
														</td>
														<td class="qc_detail_head">
															<select class="form-control" name="qc_reject_godown" id="qc_reject_godown"  >
																<?=get_all_godown($dbcon,'');?>
															</select>
														</td>
														<td class="qc_detail_head">
															<select class="select2 " name="new_product" id="new_product"  >
																<?=getproduct($dbcon,'');?>
															</select>
														</td>
														<td class="qc_detail_head <?=$dper?>" >
															<input type='text' class='form-control' name='qty_reprocess' id='qty_reprocess' value='<?=$reprocess?>' onkeyup='sub_accept_value()' readonly />
															<strong id='qty_error_reprocess' style='color:red'></strong>
														</td>
														<td class="qc_detail_head <?=$dper?>" >
															<select class="form-control" name="qc_reporcess_godown" id="qc_reporcess_godown"  >
																<?=get_all_godown($dbcon,'');?>
															</select>
														</td>
														<td class="qc_detail_head <?=$dper?>">
															<select class="form-control" name="new_process" id="new_process"  >
																<?=get_products_current_and_next_process($dbcon, $current_product_id, $current_process_id);?>
															</select>
														</td>
													</tr>
												</table>
												<!--<input type='hidden' class='form-control' name='grn_ref' id='grn_ref' value='<?=$row['ref_no']?>' />-->
												<input type='hidden' class='form-control' name='j_reprocess' id='j_reprocess' value='<?=$row['j_reprocess']?>' />
												<input type='hidden' class='form-control' name='' id='qty_accept_hid' value='<?=$accept?>' />
												<input type='hidden' class='form-control' name='' id='qty_reprocess_hid' value='' />
												<input type='hidden' class='form-control' name='' id='qty_reject_hid' value='' />
												<input type='hidden' name='po_id' id='po_id' value='' />
											</div>
											<!--<div class="col-md-12" style="background-color:blue;text-align:center;padding:5px;color:#FFFFFF;font-weight:bold;font-size:18px;">
												QC PARAMETER
											</div>
											<div class="col-md-12">
												<div id="qc_productdata_parameter"></div>
											</div>-->
										</div>
										
										<div class="row" id="final_submit_div">	
											<div class="col-md-12">
												<div class="col-md-6">
													<div class="form-group">
													  <label class="col-md-4 control-label">Remarks </label>
															<div class="col-md-6 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['qc_remark']?></textarea> 
														</div>
													</div>
												</div>
												<?if($mode=="Add" && $set_conr['qc_upload_receipt'] == "Yes"){ 
													$ttrt="required";
												}else{
													$ttrt="";
												}
												?>
												<div class="col-md-6">
													<label class="col-md-3 control-label">Upload Receipt *</label>
													<div class="col-md-7">
														<input type="file" class="form-control" id="qc_file" name="qc_file[]" multiple="multiple" <?=$ttrt?> />
													</div>
													<div class="col-md-2">
													<?phpif($mode=='Edit'){
														 $get_attch_qry="select * from tbl_qc_attch where qc_attch_status=0 and qc_id=".$rel['qc_id'];
														$attch_rs=$dbcon->query($get_attch_qry);
														while($attch_rel=mysqli_fetch_assoc($attch_rs)){
													?>
														<a href="<?=ROOT.QC_FILE_VWING.$attch_rel['qc_file']?>" class="btn btn-xs btn-primary" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-eye"></i>  </a> 
														<button type="button" onClick="delete_attch(<?=$attch_rel['grn_attch_id']?>)" class="btn btn-xs btn-danger" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-trash-o"></i></button>
														<br/>
													<?} }?>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<center>
												<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
												<a href="<?=ROOT.$back?>" type="button" class="btn btn-danger">Cancel</a>
												</center>
											</div>	
										</div>
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										
										<input type='hidden' class='' name='grn_product' id='grn_product' value='<?=$row['product_id']?>' />
										<input type='hidden' class='form-control' name='grn_type' id='grn_type' value='<?=$row['ref_type']?>' />
										<input type='hidden' class='form-control' name='qc_unit_id' id='qc_unit_id' value='<?=$row['unit_id']?>' />
										<input type='hidden' class='form-control' name='grn_pqty' id='grn_pqty' value='<?=$row['product_qty']?>' />
										<input type='hidden'  name='grn_no' id='grn_no' value='<?=$row['grn_id']?>' />
										<input type='hidden'  name='po_ref_id' id='po_ref_id' value='<?=$row['po_ref_id']?>' />
										
										<input type='hidden' name='grn_trn_id' id='grn_trn_id' value='<?=$id?>' />
										
										<input type='hidden' name='allocate_process_ids' id='allocate_process_ids' value='<?=$allocate_process_ids?>' />
										
										<input type='hidden' name='grn_id' id='grn_id' value='<?=$grn_id?>' />
										
										<input type="hidden" name="current_process_id" id="current_process_id" value="<?=$current_process_id?>" />
										
										<input type='hidden' name='save_print' id='save_print' value='' />
										<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
										<input type="hidden" name="back" id="back" value="<?=$back?>" />
										<input type="hidden" name="session_qc_work_type" id="session_qc_work_type" value="<?=$qc_work_type?>">
										<input type="hidden" name="process_show" id="process_show" value="<?=$process_show?>">
									</form>
								</div>
								<div class="row" style="margin:20px 0">
									<form class="form-horizontal" role="form" id="get_each_qty_qc_param" action="javascript:;" method="post" name="get_each_qty_qc_param">

										
									</form>
								</div>
							</section>
						</div>
					</div>
				</section>
			</section>
			<?php include_once($include1.'show_product_parameter.php');?>
			<?php include_once($include1.'view_revision_image.php');?>

			<?php include_once($include.'/footer.php');?>
		</section>
			<?php include_once($include.'/include_js_file.php');?>   
			<script src="<?=ROOT.PURCHASE_ROOT?>js/app/qc_detail.js?<?=time()?>"></script>
			
				<script>
				$(".select2").select2({
					width: '100%',
					minimumInputLength: 2,
				});
				
				$("#product_id").select2({
					width: '83%'
				});
				$('.default-date-picker').datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
			</script>
			<?
		
			if($mode=="Add")
			{
				echo "<script>get_series_no() </script>";
				//echo "<script>get_grn_product() </script>";
				//echo "<script>show_qc_param_details() </script>";
			}
			
			if($mode=='Edit')
			{
				//echo "<script>get_grn_product($('#grn_no').val()) </script>";
			}
			?>
	</body>
</html>
