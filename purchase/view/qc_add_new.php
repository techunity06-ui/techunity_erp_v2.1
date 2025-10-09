<?php 
session_start();
include('../include/urlfile.php');	
$form="QC Add";
$countryid='101';
$stateid='1';
$cityid='1';
$reprocess_type = '';
$batch_id = "";
//check permission for qa done details
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	QC_DONE_CREATE,
	QC_DONE_EDIT,
	QC_DONE_PURCHASE_QC_PENDING_ADD,
	QC_DONE_PARTS_QC_PENDING_ADD,
]);
$current_process_id = '';
$company_config = getCompanyConfiguration($dbcon);	
$store_relese_first_process = $company_config['store_relese_first_process'];
if(strpos($_SERVER[REQUEST_URI], "qc_edit")==true){
	if(!in_array(QC_DONE_EDIT,$bulkAccessArray)) {
		header("Location: ".DOMAIN."permission_access");
	}
	$mode="Edit";
	$qc_id=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select * from tbl_qc where qc_id=$qc_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$date=date('d-m-Y',strtotime($rel['qc_date']));
	$back=PURCHASE_ROOT."qc_done_list";
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
		$back=PURCHASE_ROOT."purchase_qc_pending_list";
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
		$id = $dbcon->real_escape_string($_REQUEST['id']); // batch_id
		$process_id = $dbcon->real_escape_string($_REQUEST['process_id']);
		if(!empty($process_id)){
			$back=PRODUCTION_ROOT."parts_qc_pending_list/".$process_id;
		}else{
			$back=PRODUCTION_ROOT."parts_qc_pending_list";
		}
		$process_show=1;
		/* $sel=$dbcon->query("select po.process_name,g.grn_id,po.process_id from tbl_grn_trn as gt 
			left join tbl_grn as g on g.grn_id=gt.grn_id
			left join tbl_jobwork as jo on jo.jobwork_id=g.purchaseorder_id
			left join tbl_allocate_process as aol on aol.p_id=jo.j_alloc_process_id
			left join process_mst as po on po.process_id=aol.process_id
			where gt.grn_trn_id='$id'"); */
			
			/*$sel=$dbcon->query("select po.process_name,g.grn_id,po.process_id from tbl_grn_trn as gt 
				left join tbl_grn as g on g.grn_id=gt.grn_id
				left join process_mst as po on po.process_id=gt.process_id
				where gt.grn_trn_id='$id'");*/

			$sel=$dbcon->query("select po.process_name,g.grn_id,po.process_id,batch.grn_trn_id from tbl_batch_data as batch 
				left join tbl_grn_trn as gt on gt.grn_trn_id = batch.grn_trn_id
				left join tbl_grn as g on g.grn_id=gt.grn_id
				left join process_mst as po on po.process_id=gt.process_id
				where batch.batch_id='$id'");
			
			$row=mysqli_fetch_assoc($sel);
			
			$process_name			=$row['process_name'];
			$grn_id					=$row['grn_id'];

			$current_process_id		=$row['process_id'];
			//echo $revision_id		=$row['revision_id'];
			$allocate_process_ids	=find_qc_process_ids($dbcon,$row['grn_trn_id']);
		}else{
		/*if(!in_array(QC_DONE_CREATE,$bulkAccessArray)) {
			header("Location: ".DOMAIN."permission_access");
		}*/
		$mode="Add";
		$date=date('d-m-Y');
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));
		$id = $dbcon->real_escape_string($_REQUEST['id']);
		$back=PURCHASE_ROOT."qc_done_list";
		$process_show=0;
	}

	if($row['ref_type']!="1") { 
	 	//$dper="display:none";
	}
	
	if($process_show!="1") { 
		$dper="hide";
	}
	
	/*$que="select gt.product_id,p.product_type,p.product_name,p.revision_id,g.ref_type,g.grn_no,gt.product_qty,g.grn_date,g.qc_status,g.grn_status,g.ref_type,gt.grn_trn_id,gt.grn_id,gt.unit_id,umst.unit_name,gt.po_ref_id,gt.branch_id from tbl_grn_trn as gt 
	left join unit_mst as umst on umst.unitid=gt.unit_id
	left join tbl_grn as g on g.grn_id=gt.grn_id 
	left join product_mst as p on p.product_id=gt.product_id 
	left join tbl_category as tc on p.product_category=tc.cat_id
	where gt.grn_trn_id=".$id;*/

	$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);

	$que="select batch.batch_id,batch.batch_qty,batch.grn_accept_qty,batch.base_qty,gt.product_id,p.product_type,p.product_name,p.revision_id,g.ref_type,g.grn_no,gt.product_qty,g.grn_date,g.qc_status,g.grn_status,g.ref_type,gt.grn_trn_id,gt.grn_id,gt.unit_id,umst.unit_name,gt.po_ref_id,gt.branch_id,gt.product_conv_unit as conv_unit_id, branch.branch_name,p.product_icode, dr.drawing_number,batch.batch_unit from tbl_batch_data as batch

	left join tbl_grn_trn as gt on gt.grn_trn_id = batch.grn_trn_id
	left join unit_mst as umst on umst.unitid=batch.batch_unit
	left join tbl_grn as g on g.grn_id=gt.grn_id 
	left join product_mst as p on p.product_id=gt.product_id 
	left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
	left join tbl_category as tc on p.product_category=tc.cat_id
	left join branch_mst as branch on branch.branch_id=gt.branch_id
	where batch.batch_id=".$id;

	$sel=$dbcon->query($que);
	$row=mysqli_fetch_assoc($sel);
	$batch_id = $row['batch_id'];

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

	$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$row['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$row['product_icode'].")";
				        }

	$chk_p_id = explode(",",$allocate_process_ids);	
	$process=p_id_wise_find_previous_and_next_process($dbcon,$chk_p_id[0]);
	$process_pr=json_decode($process);
	$next_process_id=$process_pr->next_process_id;
	
?>

	<!DOCTYPE html>
	<html lang="en">
	<head>
		<title>Qc Add</title>
		<?php include_once($include.'/include_css_file.php');?>
		<style type="text/css">

		.wizard > .content {
			overflow-y: scroll;
		}
	</style>
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
									<li ><a href="<?=ROOT.$back?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
					</div>	
				</div>
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">
							<header class="panel-heading">
								<?php if($mode=="Bom"){ echo "Sales Order Bom";}else{?>New <?=$form?><?php } ?>
							</header>
							<div class="panel-body">
								<!--<form class="form-horizontal" role="form" id="qc_add" action="javascript:;" method="post" name="qc_add">-->
<!-- 
								<div class="col-md-12" style="padding-bottom: 10px;">
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
									<?php if($process_show==1){ ?>
										<div class="col-md-4">
											<div class="form-group">
												<label class="col-md-4  control-label"><strong>Process Name</strong></label>
												<div class="col-md-6 col-xs-11" style="font-size:16px;color:red;">
													<strong><?=$process_name?></strong>
												</div>
											</div>
										</div>
									<?php }else{ ?>
										<div class="col-md-4">
											<?php echo getBranchBox($dbcon, $branch_id,$edit_branch_id, true, true, 'load_purchase_qc_pending_datatable()', '4', '6'); ?>
										</div>
									<?php 	} ?>
								</div> -->
								<form class="form-horizontal" role="form" id="qc_add" action="javascript:;" method="post" name="qc_add" >
									<div>
										<h3>Step 1</h3>
										<section>
											<div class="form-group clearfix ">
												<label class="col-lg-2 control-label " for="userName">QC No * </label>
												<div class="col-lg-3">
													<input id="qc_no" name="qc_no" type="text" class="form-control" title="Enter Planning No" value="<?=$rel['qc_no']; ?>" placeholder="QC No" required readonly >
												</div>
											</div>
											<div class="form-group clearfix ">
												<label class="col-lg-2 control-label " for="userName">QC Date * </label>
												<div class="col-lg-3">
													<input id="qc_date" name="qc_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?php if($mode=='Edit'){ date("d-m-Y",strtotime($rel['qc_date'])); } else { echo  date("d-m-Y"); }  ?>" placeholder="Date" >
												</div>
											</div>

											<div class="form-group clearfix ">
												<label class="col-lg-2 control-label " for="userName">Check T.C.No</label>
												<div class="col-lg-3">
													<input id="check_tc_no" name="check_tc_no" type="text" class="form-control" title="Check T.C.No" value="<?=$rel['check_tc_no']; ?>" placeholder="Check T.C.No" >
												</div>
											</div>

											<?php if($process_show==1){ ?>
												<div class="form-group clearfix ">
													<label class="col-lg-2 control-label " for="userName">Process Name</label>
													<div class="col-lg-3">
														<strong style="color:#d90000"><?=$process_name?></strong>
													</div>
												</div>
											<?php } ?>
											<div class="form-group clearfix ">
												<label class="col-lg-2 control-label " for="userName">Branch Name </label>
												<div class="col-lg-3">
													<strong style="color:#d90000"><?=$row['branch_name']?></strong>
													<!-- <input type="text" class="form-control" value="<?=$row['product_name'].' '.$item_code.' '.$drawing_number?>" readonly> -->
												</div>
													<?php /* echo getBranchBox($dbcon, $branch_id,$edit_branch_id, true, true, 'load_purchase_qc_pending_datatable()', '2', '3'); */?>
											</div>
											<div class="form-group clearfix ">
												<label class="col-lg-2 control-label " for="userName">Product Name </label>
												<div class="col-lg-3">
													<strong style="color:#d90000"><?=$row['product_name'].' '.$item_code.' '.$drawing_number?></strong>
													<!-- <input type="text" class="form-control" value="<?=$row['product_name'].' '.$item_code.' '.$drawing_number?>" readonly> -->
												</div>
											</div>
											<div class="form-group clearfix ">
												<label class="col-lg-2 control-label " for="password"> Product Category</label>
												<div class="col-lg-3">
													<strong style="color:#d90000"><?=$cat_name?></strong>
													<!-- <input type="text" class="form-control" value="<?=$cat_name?>" readonly> -->
												</div>
											</div>
											<div class="form-group clearfix">
												<label class="col-lg-2 control-label " for="confirm">Total Quantity</label>
												<div class="col-lg-3">
													<strong style="color:#d90000"><?=$row['grn_accept_qty']?> <?=$row['unit_name']?></strong>
													<!-- <input type="text" class="form-control" value="<?=$row['grn_accept_qty']?> <?=$row['unit_name']?>" readonly> -->
													<input type="hidden" name="total_pending_qty" id="total_pending_qty" value="<?=$row['grn_accept_qty']?>">
												</div>
											</div>
											<div class="form-group clearfix ">
												<label class="col-lg-2 control-label " for="qc_qty">QC Quantity</label>
												<div class="col-lg-2">
													<input id="qc_qty" name="qc_qty" type="number" class="required form-control numbersOnly" onchange="get_qc_qty('<?=$row['grn_accept_qty']?>',this.value,'<?=$row['product_name'].' '.$item_code.' '.$drawing_number?>');">
													<div id="qc_qtys"></div>
												</div>
												<div class="col-lg-1">
													<input type="text" class="form-control" value="<?=$row['unit_name']?>" readonly>
												</div>
											</div>
										</section>
										<h3>Step 2</h3>
										<section>
											<div class="form-group col-lg-4 clearfix">
												<label class="col-lg-4 control-label " for="userName">Product Name </label>
												<div class="col-lg-8">
													<input type="text" class="form-control" value="<?=$row['product_name'].' '.$item_code.' '.$drawing_number?>" readonly>
												</div>
											</div>
											<div class="form-group col-lg-4 clearfix">
												<label class="col-lg-5 control-label " for="password"> Product Category</label>
												<div class="col-lg-7">
													<input type="text" class="form-control" value="<?=$cat_name?>" readonly>
												</div>
											</div>
											<div class="form-group col-lg-4 clearfix">
												<label class="col-lg-4 control-label " for="confirm">Total Quantity</label>
												<div class="col-lg-8">
													<strong style="color:#d90000"><?=$row['grn_accept_qty']?> <?=$row['unit_name']?></strong>
													<!-- <input type="text" class="form-control" value="<?=$row['grn_accept_qty']?> <?=$row['unit_name']?>" readonly> -->
													<input type="hidden" name="total_pending_qty" id="total_pending_qty" value="<?=$row['grn_accept_qty']?>">
												</div>
											</div>
											<div class="row" style="margin:20px 0">
												<form class="form-horizontal" role="form" id="get_each_qty_qc_param" action="javascript:;" method="post" name="get_each_qty_qc_param">
													<div id="get_each_qty_qc_param1"></div>
													<div id="get_each_qty_qc_param1_new"></div>
												</form>
											</div>
											<div style="display:none;">
											<div class="form-group col-lg-3 clearfix">
												<label class="col-lg-12 control-label " for="Product"><strong>Product Name</strong></label>
											</div>
											<div class="form-group col-lg-5 clearfix">
												<label class="col-lg-12 control-label " for="Param"><strong>QC Param</strong></label>
											</div>
											<div class="form-group col-lg-2 clearfix">
												<label class="col-lg-12 control-label " for="Status"><strong>Status</strong></label>
											</div>
											<div class="form-group col-lg-2 clearfix">
												<label class="col-lg-12 control-label " for="Action"><strong>Action</strong></label>
											</div>
											<div id="productloop"></div>
											<div id="qc_pera_show"></div>
										</div>
											
										</section>
										<h3>Step 3</h3>
										<section>
											<div class="form-group col-lg-4 clearfix">
												<label class="col-lg-4 control-label " for="userName">Product Name </label>
												<div class="col-lg-8">
													<input type="text" class="form-control" value="<?=$row['product_name'].' '.$item_code.' '.$drawing_number?>" readonly>
												</div>
											</div>
											<div class="form-group col-lg-4 clearfix">
												<label class="col-lg-5 control-label " for="password"> Product Category</label>
												<div class="col-lg-7">
													<input type="text" class="form-control" value="<?=$cat_name?>" readonly>
												</div>
											</div>
											<div class="form-group col-lg-4 clearfix">
												<label class="col-lg-4 control-label " for="confirm">Total Quantity</label>
												<div class="col-lg-8">
													<strong style="color:#d90000"><?=$row['grn_accept_qty']?> <?=$row['unit_name']?></strong>
													<!-- <input type="text" class="form-control" value="<?=$row['grn_accept_qty']?> <?=$row['unit_name']?>" readonly> -->
													<input type="hidden" name="total_pending_qty" id="total_pending_qty" value="<?=$row['grn_accept_qty']?>">
												</div>
											</div>
											<div class="col-lg-12">
												<div class="form-group col-lg-4 clearfix">
													<label class="col-lg-4 control-label " for="password">Accept Qty</label>
													<div class="col-lg-8">
														<input type='number' class='form-control numbersOnly' name='qty_accept' id='qty_accept' value='<?=$accept?>' onkeyup='sub_accept_value()'/>
														<strong id='qty_error' style='color:red'></strong>
													</div>
												</div>
												<div class="form-group col-lg-4 clearfix">
													<label class="col-lg-4 control-label " for="confirm">Godown</label>
													<div class="col-lg-8">
														<select class="form-control" name="qc_godown" id="qc_godown" >
															<?=get_all_godown($dbcon,'');?>
														</select>
													</div>
												</div>
											</div>
											<div class="col-lg-12">
												<div class="form-group col-lg-4 clearfix">
													<label class="col-lg-4 control-label " for="userName">Reject Qty</label>
													<div class="col-lg-8">
														<input type='number' class='form-control numbersOnly' name='qty_reject' id='qty_reject' value='<?=$reject?>' onkeyup='sub_accept_value()'/>
														<strong id='qty_error_reject' style='color:red'></strong>
													</div>
												</div>
												<div class="form-group col-lg-4 clearfix">
													<label class="col-lg-4 control-label " for="password">Godown</label>
													<div class="col-lg-8">
														<select class="form-control" name="qc_reject_godown"  id="qc_reject_godown"  >
															<?=get_all_godown($dbcon,'');?>
														</select>
													</div>
												</div>

												<!-- <div class="form-group col-lg-4 clearfix">
													<label class="col-lg-4 control-label " for="new_product">New Product</label>
													<div class="col-lg-8">
														<input id="new_product" class="select2 new_product" name="new_product" placeholder="Select product" onchange="load_product_unit(this.value)"/>
														<select class="select2 form-control" name="new_product_id" id="new_product_id" title="Select Product" style="width:100%"  onChange="load_product_unit(this.value);" >
    																	<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
    																</select>
													</div> -->
												<!-- <div class="form-group col-lg-4 clearfix">
													<label class="col-lg-4 control-label " for="new_product">New Product</label>
													<div class="col-lg-8">
														<select class="form-control" name="new_product" id="new_product" >
															<option value="">Select Product</option>
														</select>
													</div>
												</div> -->
												<!--<div class="form-group col-lg-4 clearfix">
													<label class="col-lg-4 control-label " for="confirm">New Product</label>
													<div class="col-lg-8">
														<select class="select2 " name="new_product" id="new_product"  >
															<?php //=getproduct($dbcon,'');?>
														</select>
													</div>
												</div>-->
											</div>
											
											<div class="col-lg-12">
												<div class="form-group col-lg-4 clearfix <?=$dper?>">
													<label class="col-lg-4 control-label " for="userName">Reprocess Qty</label>
													<div class="col-lg-8">
														<input type='number' class='form-control numbersOnly' name='qty_reprocess' id='qty_reprocess' value='<?=$reprocess?>' onkeyup='sub_accept_value()'/>
														<strong id='qty_error_reprocess' style='color:red'></strong>
													</div>
												</div>
												<div class="form-group col-lg-4 clearfix <?=$dper?>">
													<label class="col-lg-4 control-label " for="password">Godown</label>
													<div class="col-lg-8">
														<select class="form-control" name="qc_reporcess_godown" id="qc_reporcess_godown"  >
															<?=get_all_godown($dbcon,'');?>
														</select>
													</div>
												</div>
												<div class="form-group col-lg-4 clearfix <?=$dper?>">
													<label class="col-lg-4 control-label " for="confirm">New Process</label>
													<div class="col-lg-8">
														<select class="form-control" name="new_process" id="new_process">
															<?=get_products_current_and_previous_process($dbcon, $current_product_id, $current_process_id);?>
														</select>
													</div>
												</div>
											</div>
											<div class="col-lg-12">
												<div class="form-group col-lg-4 clearfix">
													<label class="col-lg-4 control-label">Upload Receipt *</label>
													<div class="col-lg-8">
														<input type="file" class="form-control" id="qc_file" name="qc_file[]" multiple="multiple" <?=$ttrt?> />
													</div>
												</div>

												<div class="form-group col-lg-4 clearfix">
													<div class="col-md-2">
													<?php if($mode=='Edit'){
														 $get_attch_qry="select * from tbl_qc_attch where qc_attch_status=0 and qc_id=".$rel['qc_id'];
														$attch_rs=$dbcon->query($get_attch_qry);
														while($attch_rel=mysqli_fetch_assoc($attch_rs)){
													?>
														<a href="<?=ROOT.QC_FILE_VWING.$attch_rel['qc_file']?>" class="btn btn-xs btn-primary" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-eye"></i>  </a> 
														<button type="button" onClick="delete_attch(<?=$attch_rel['grn_attch_id']?>)" class="btn btn-xs btn-danger" target="_blank" style="margin-bottom: 2px;"><i class="fa fa-trash-o"></i></button>
														<br/>
													<?php } }?>
													</div>
												</div>	
											</div>
											
												<div class="col-md-12 reject_product_add">
													<div class="form-group">
														<table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;margin-left: 3%;" id="product_list" class="display table table-bordered table-striped;">
															<tr id="field">
																<th width="25%" class="text-center">Product Detail</th>
																<th width="10%" class="text-center hide_act_add">Quantity</th>
																<th width="5%" class="text-center hide_act_add">Unit</th>
																<th width="10%" class="text-center"></th>
															</tr>
															<tr id="field1">
																<td style="vertical-align:top;" width="25%">

																	<input id="new_product_id" class="select2 new_product_id" name="new_product_id" placeholder="Select product" onchange="load_product_unit(this.value)"/>
																	<!-- <select class="select2 form-control" name="new_product_id" id="new_product_id" title="Select Product" style="width:100%"  onChange="load_product_unit(this.value);" >
    																	<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
    																</select> -->
																</td>
	
																<td style="vertical-align:top;" class="hide_act_add">
																	<input type="number"  title="Enter Qty" min="0" id="new_qty" name="new_qty" value="1"  class="form-control" />
																</td>
																<td style="vertical-align:top;" class="hide_act_add">
																	<select class="select2 form-control" name="new_unit_id" id="new_unit_id" placeholder="Select Unit" title="Select Unit" style="width:100%">
    																</select>
																</td>
																<td style="vertical-align:top;">
																	<input type="button" id="addproduct" class="btn btn-primary" data-original-title="Add Product" data-toggle="tooltip" data-placement="top" onclick="add_reject_product();" value="Add"/>
																</td>
																<input type='hidden' name='edit_id' id='edit_id' value="" />
																
															</tr>
														</table>			
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<div id="new_productdata" style="margin-left: 3%;"></div>
													</div>
												</div>
					
											<input type='hidden' class='form-control' name='j_reprocess' id='j_reprocess' value='<?=$row['j_reprocess']?>' />
											<input type='hidden' class='form-control' name='' id='qty_accept_hid' value='<?=$accept?>' />
											<input type='hidden' class='form-control' name='' id='qty_reprocess_hid' value='' />
											<input type='hidden' class='form-control' name='' id='qty_reject_hid' value='' />
											<input type='hidden' name='po_id' id='po_id' value='' />
										
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />

											<input type='hidden' class='' name='grn_product' id='grn_product' value='<?=$row['product_id']?>' />
											<input type='hidden' class='form-control' name='grn_type' id='grn_type' value='<?=$row['ref_type']?>' />
											<input type='hidden' class='form-control' name='qc_unit_id' id='qc_unit_id' value='<?=$row['batch_unit']?>' />
											<input type='hidden' class='form-control' name='qc_base_unit_id' id='qc_base_unit_id' value='<?=$row['unit_id']?>' />
											<input type='hidden' class='form-control' name='qc_conv_unit_id' id='qc_conv_unit_id' value='<?=$row['conv_unit_id']?>' />
											<input type='hidden' class='form-control' name='grn_pqty' id='grn_pqty' value='<?=$row['grn_accept_qty']?>' />
											<input type='hidden'  name='grn_no' id='grn_no' value='<?=$row['grn_id']?>' />
											<input type='hidden'  name='po_ref_id' id='po_ref_id' value='<?=$row['po_ref_id']?>' />

											<input type='hidden' name='grn_trn_id' id='grn_trn_id' value='<?=$row['grn_trn_id']?>' />

											<input type='hidden' name='allocate_process_ids' id='allocate_process_ids' value='<?=$allocate_process_ids?>' />

											<input type='hidden' name='grn_id' id='grn_id' value='<?=$grn_id?>' />
											<input type='hidden' name='batch_id' id='batch_id' value='<?=$batch_id?>' />

											<input type="hidden" name="current_process_id" id="current_process_id" value="<?=$current_process_id?>" />

											<input type='hidden' name='save_print' id='save_print' value='' />
											<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
											<input type="hidden" name="back" id="back" value="<?=$back?>" />
											<input type="hidden" name="session_qc_work_type" id="session_qc_work_type" value="<?=$qc_work_type?>">
											<input type="hidden" name="process_show" id="process_show" value="<?=$process_show?>">
											
											<input type="hidden" name="auto_store_relese" id="auto_store_relese" value="">
											<!--temp status-->
												
											<input type="hidden" name="qc_work_type" id="qc_work_type" value="2">
											<input type="hidden" name="branch_id" id="branch_id" value="<?=$edit_branch_id?>">
											
											<!-- temp status-->
										</section>
									</div>
								</form>
								<!--</form>-->
							</div>
						</section>
					</div>
				</div>
			</section>
		</section>
		<?php include_once($include1.'show_product_parameter.php');?>
		<?php include_once($include1.'view_revision_image.php');?>
		<?php include_once($include1.'store_require_confirmation.php'); ?>
		
		<?php include_once($include.'/footer.php');?>
	</section> 
	<?php include_once($include.'/include_js_file.php');?> 
	<script src="<?=ROOT.PURCHASE_ROOT?>js/app/qc_detail.js?<?=time()?>"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			var form = $("#qc_add");
			form.validate({
				errorPlacement: function errorPlacement(error, element) {
					element.after(error);
				}
			});
			form.children("div").steps({
				headerTag: "h3",
				bodyTag: "section",
				transitionEffect: "slideLeft",
				onStepChanging: function (event, currentIndex, newIndex) {
					if(newIndex=="1"){
						//check qc qty and load qc perameter
						// manage_qc_work_type();  // hide sanat
						manage_qc_work_type_new();  // add sanat
						load_qc_perameter();
					}
					if(newIndex=="2"){
						//check qc qty and load qc perameter
						toggle_add_reject_product();
						qty_byphargation();
					}
					
					//form.validate().settings.ignore = ":disabled,:hidden";

					return form.valid();

				},
				onFinishing: function (event, currentIndex) {
					//form.validate().settings.ignore = ":disabled";
					return form.valid();

				},
				onFinished: function (event, currentIndex) {
					//alert("Submitted!");
					//form.validate().settings.ignore = ":disabled";
					<?php if($store_relese_first_process == '1' && $next_process_id > 0){ ?>
						store_confirm_msg();	
					<?php } else { ?>
						qc_submit();
					<?php } ?>						
					return form.valid();
				}
			});
		});
	</script> 

	<script>
		/*$(".select2").select2({
			width: '100%',
			minimumInputLength: 2,
		});*/

/*		$("#product_id").select2({
			width: '83%'
		});
*/		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

	</script>
	<?php 

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
