<?php 
	session_start();
	include('../include/urlfile.php');
	$form=" Requested Purchase Order";
	
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			PO_REQ_ADD,PO_REQ_UPDATE
	]);
	
	$product_id=$dbcon->real_escape_string($_REQUEST['id']);
	$type=$dbcon->real_escape_string($_REQUEST['type']);
	$branchId = $dbcon->real_escape_string($_REQUEST['branch_id']);
	$rp_id = $dbcon->real_escape_string($_REQUEST['rp_id']);
	$purchaseorder_date = date('d-m-Y');
	//$vender_id=find_leat_vender($dbcon,$product_id);
	
	$query_used="select quo.vender_id, quo.product_rate, rpro.branch_id from tbl_purchasetrntemp as rpro 
		left join po_quotation as quo on quo.po_quotation_id=rpro.po_quotation_id
		where purchaseordertrn_status=0 and po_trn_req_status=0 and rpro.po_quotation_id!=0 and product_id=".$product_id;
	$rel_used=brp_mysqli_fetch_array($dbcon->query($query_used));

	
	if(!empty($rel_used['vender_id'])){
		$vender_id=$rel_used['vender_id'];
	}else{
		$mindent = "select ptrn.vender_id from tbl_request_product as req
		left join tbl_pre_trn as ptrn on ptrn.pre_trn_id=req.pre_trn_id
		where req.rp_id=".$rp_id;
		$mindent_r=brp_mysqli_fetch_array($dbcon->query($mindent));
		if(!empty($mindent_r['vender_id'])){
			$vender_id=$mindent_r['vender_id'];
		}else{
			$vender_id=find_leat_vender($dbcon,$product_id);
		}
	}
	//var_dump($rel_used['vender_id']);
	if($rel['po_req_mode']=='1'){
		$mode="Edit";
		if(!in_array(PO_REQ_UPDATE,$bulkAccessArray)){
	        header("Location: ".DOMAIN."permission_access");
	    }
	}
	else
	{
		$mode="Add";
		if(!in_array(PO_REQ_ADD,$bulkAccessArray)){
	        header("Location: ".DOMAIN."permission_access");
	    }
	}
	$branch_id = $_SESSION['branch_id'];
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$purchase_party_show = $companyConfiguration['purchase_party_show'];
	$getspecialConfiguration=getspecialConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>PO REQUEST</title>
		<?php include_once($include.'/include_css_file.php');?>
	</head>
	<body onload="check_submit_btn();">
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'/include_top_menu.php');?>
			<?php include_once($include.'/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
								  <h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.PURCHASE_ROOT.'po_req_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="purchaseorder_req_add" action="javascript:;" method="post" name="purchaseorder_req_add">
										<div class="row">
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<div class="form-group">
													<label class="col-md-4 control-label"> Select Vendor </label>
													<div class="col-md-8 col-xs-11">
														<?//=getcust_purchase($dbcon,$vender_id,$product_id)?>
														<select class="select2" name="vender_id" id="vender_id" onChange="/*get_po_tax(this.value)*/" required title="Select Vender">
															<?=getcust($dbcon,$vender_id,$purchase_party_show);?>	
														</select>
													</div>
													</div>	
												</div>
												<?if($companyConfiguration['branch_wise_manage']==1){?>
												<div class="col-md-4">
													<?php echo getBranchBox($dbcon, $branch_id, $branchId, true, false,'','4','8'); ?>
												</div>
												<?php} ?>
												<div class="col-md-4">
													<div class="form-group">  	
													<label class="col-md-3 control-label" >PO Request Date </label>
													<div class="col-md-5 col-xs-11">
														<input id="purchaseorder_date" name="purchaseorder_date" type="text" class="form-control" title="Date" value="<?=$purchaseorder_date?>" placeholder="Purchase Order Date" readonly>
													</div>
													</div>	
												</div>
											</div>	 
											<div class="col-md-12" style="margin-top:10px;"></div>			
											<div class="col-md-12" style="margin-top:10px;">
												<div id="sale_productdata">
													<?
														$where ='';$ab='';
														if($companyConfiguration['po_work_order_wise'] == 1){
															$where .= ' and po.po_ref_id='.$rp_id;
															$ab .=',po.po_ref_id';
														}

														$query="select sum(po.product_qty) as pqty,po.unit_id, tc.cat_name, product.product_name,product.product_type,product.product_base_unit,product.product_conv_unit,po.purchaseordertrn_id,group_concat(po.purchaseordertrn_id ORDER BY po.purchaseordertrn_id ASC) as req_id,group_concat(po.po_ref_id ORDER BY po.purchaseordertrn_id ASC) as po_ref_id,po.product_id,pretr.rate,pretr.purchasecardtrn_id
														from tbl_purchasetrntemp  as po 
														left join product_mst as product on product.product_id=po.product_id
														left join tbl_category as tc on product.product_category=tc.cat_id  
														left join tbl_request_product as req on req.rp_id  = po.po_ref_id
														left join tbl_pre_trn as pretr on pretr.pre_trn_id = req.pre_trn_id
														where po.purchaseordertrn_status=0 and po.po_trn_req_status=0 and po.product_id=".$product_id." ".$where." group by po.product_id".$ab."  order by pretr.pre_trn_id";
														//echo $query;exit;
														$result=$dbcon->query($query);
													?>
													<div class="form-group">
														<div class="col-md-12 col-xs-11">
															<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
																<tr id="field">
																	<th width="5%" class="text-center">
																		<input type="checkbox" id="all_chk_box" style="width: 23px;height: 23px;margin-top: 0px;" onclick="check_all();">
																	</th>
																	<th width="10%" class="text-center">Type</th>
																	<th width="25%" class="text-center">Product Name</th>
																	<th width="10%" class="text-center">Product Category</th>
																	<th width="8%" class="text-center">Qty</th>
																	<th width="8%" class="text-center">UOM</th>
																	<th width="8%" class="text-center">Unit Of PO </th>
																	<th width="8%" class="text-center">PO qty</th>
																</tr>
															<?
															$i=1;
															while($rel_trn=mysqli_fetch_assoc($result))
															{
																$cat_name = ($rel_trn['cat_name']!=null) ? $rel_trn['cat_name'] : 'PRIMARY';
																$query_q="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn  as po 
																		where purchaseordertrn_req_status=0 and po.req_id in (".$rel_trn['req_id'].")";
																	$result1=$dbcon->query($query_q);
																	$rel_u=mysqli_fetch_assoc($result1);
																
																$pending_qty=$rel_trn['pqty']-$rel_u['used_qty'];
																$pro_qty_val = '';
																if($companyConfiguration['direct_po_create']==0){
																	$pro_qty_val = "max='".$pending_qty."'";
																}

																if($rel_used['product_rate'] != ''){
																	$rel_trn['rate'] = $rel_used['product_rate'];
																}

																$ret_req_conv = '';
																if($rel_trn['product_base_unit'] != $rel_trn['product_conv_unit']){
																	if($rel_trn['unit_id'] == $rel_trn['product_base_unit']){
																		$type="conv_unit";
																		$unit_name  = getunitname($dbcon,$rel_trn['product_conv_unit']);
																		$ret_qty=convert_stock($dbcon,$rel_trn['pqty'],$rel_trn['product_id'],$type);
																		$ret_req_conv='<span style="color:orange"> Conv Unit : '.number_format($ret_qty,4,'.','').' '.$unit_name.'</span>';
																	}else{
																		$type="base_unit";
																		$unit_name  = getunitname($dbcon,$rel_trn['product_base_unit']);
																		$ret_qty=convert_stock($dbcon,$rel_trn['pqty'],$rel_trn['product_id'],$type);
																		$ret_req_conv='<span style="color:green"> Base Unit : '.number_format($ret_qty,4,'.','').' '.$unit_name.'</span>';
																	}
																}
															?>
														
																<tr>
																	<td style="vertical-align:middle;text-align:center;">
																		<?phpif($rel_trn['po_trn_req_status']!='1'){ ?>
																		<input type="checkbox" name="che_box[]" class="chk_box" id="che_box<?=$i?>" value="<?=$rel_trn['purchaseordertrn_id']?>" onclick="check_box(<?=$i?>);" style="width: 23px;height: 23px;margin-top: 0px;">
																		<?php} ?>
																		
																		<input type="hidden" name="purchaseordertrn_id[]" id="purchaseordertrn_id<?=$i?>" value="<?=$rel_trn['purchaseordertrn_id']?>" />
																		<input type="hidden" class="chk_box_st" name="check_status[]" id="check_status<?=$i?>" value="1" />
																						
																		<input type="hidden" name="potemp_id[]" id="potemp_id<?php echo $i ?>" value="<?=$rel_trn['req_id'];?>" />
																		
																		<input type="hidden" name="po_ref_id[]" id="po_ref_id<?php echo $i ?>" value="<?=$rel_trn['po_ref_id'];?>" />
																		
																	</td>
																	<td style="vertical-align:middle;">
																		<?=get_pro_type_name($rel_trn['product_type'])?>
																	</td>
																	<td style="vertical-align:middle;">
																		<b><?=$rel_trn['product_name']?></b>
																		
																		<input type="hidden" name="product_id[]" id="product_id<?=$i?>" value="<?=$rel_trn['product_id']?>" />
																	</td>
																	<td style="vertical-align:middle;">
																		<?=$cat_name?>
																	</td>
																	
																	<td style="vertical-align:middle;white-space: nowrap;" class="text-center">
																		<input type="text" class="form-control" name="product_qty[]" id="product_qty<?php echo $i ?>" value="<?=number_format($rel_trn['pqty'],4,'.','')?>"  readonly />
																		<?=$ret_req_conv?>
																	</td>	
																
																	<td style="vertical-align:middle;" class="text-center">
																		<select class="form-control" id="product_base_unit<?php echo $i ?>" name="product_base_unit[]" >
																			<?//=getunit($dbcon,$rel_trn['product_base_unit']);?>
																			<?=getunit($dbcon,$rel_trn['unit_id']);?>
																		</select>
																	</td>
																	
																	<td style="vertical-align:middle;" class="text-center">
																		<select class="form-control" id="product_uom<?php echo $i ?>" name="product_uom[]" onchange="get_alt_qty(this.value,'<?=$rel_trn['product_id'];?>','<?=$i?>')" >
																			<?//=getunit($dbcon,$rel_trn['product_conv_unit']);?>
																			<?=getunit($dbcon,$rel_trn['unit_id']);?>
																		</select>
																	</td>
																	
																	<td style="vertical-align:middle;" class="text-center">
																	
																		<input type="hidden" class="form-control" name="unit_alt_qty[]" id="unit_alt_qty<?php echo $i ?>" value="" />
																		
																		<input type="hidden" class="form-control" name="man_rate[]" id="man_rate<?php echo $i ?>" value="<?=$rel_trn['rate']?>" />

																		<input type="hidden" class="form-control" name="unit_base_qty[]" id="unit_base_qty<?php echo $i ?>" value="" />

																		<input type="hidden" class="form-control" name="purchasecardtrn_id[]" id="purchasecardtrn_id<?php echo $i ?>" value="<?=$rel_trn['purchasecardtrn_id']?>" />
																		
																		<input type="text" class="form-control numbersOnly" name="pro_qty[]" id="pro_qty<?php echo $i ?>" 
																		onkeyup="copy_qty(this.value,<?=$i?>)" value="<?=number_format($pending_qty,4,'.','')?>" <?=$pro_qty_val?>>

																		<input type="hidden" class="form-control" name="product_alloc_qty[]" id="product_alloc_qty<?php echo $i ?>" value="<?=$pending_qty?>"  />
																	</td>
																</tr>
															<?
																$i++;
															}
															?>
															</table>
														</div>
													</div>	
												</div>	
											</div>
											<div class="clearfix"></div>
											<button type="submit" class="btn btn-success" id="save" name="save">Create PO</button>
											<a href="<?=ROOT.PURCHASE_ROOT.'po_req_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>					
										</div>
										<input type='hidden' name='mode' id='mode' value='req_po_to_main_po' />
										<input type='hidden' name='eid' id='eid' value='<?=$product_id; ?>' />	
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'/footer.php');?>
		</section>
		<?php include_once($include.'/include_js_file.php');?>   
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/po_req.js?<?=time()?>"></script>
	

<script>
$(".select2").select2({
	width: '100%'
});
$('.default-date-picker').datepicker({
	format: 'dd-mm-yyyy',
	autoclose: true
});

$(".form_datetime").datetimepicker({
    format: 'dd-mm-yyyy hh:ii',
    autoclose: true,
    todayBtn: true,
    pickerPosition: "bottom-left"

});
function add_customer_purchase()
{
	$("#bs-example-modal-lg").modal("show");
	$("#cat_id").val('1');
}
</script>
</body>
</html>
