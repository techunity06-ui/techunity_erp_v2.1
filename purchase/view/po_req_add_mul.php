<?php 
	session_start();
	include('../include/urlfile.php');	
	$form=" Requested Purchase Order";

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			PO_REQ_ADD
	]);

	if(!in_array(PO_REQ_ADD,$bulkAccessArray)){
	        header("Location: ".DOMAIN."permission_access");
    }
	$purchaseorder_date = date('d-m-Y');
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
			                                          <?php echo getBranchBox($dbcon, $branch_id, $branchId, false, true); ?>
			                                    </div>

												<div class="col-md-3">
													<div class="form-group">
													<label class="col-md-4 control-label"> Select Vendor * </label>
													<div class="col-md-8 col-xs-11">
														<?//=getcust_purchase($dbcon,$vender_id,$product_id)?>
														<select class="select2" <?=$venreq?> name="vender_id" id="vender_id" onChange="get_product(this.value)"  title="Select Vender">
															<?=getcust($dbcon,$vender_id,$purchase_party_show);?>	
														</select>
													</div>
													</div>	
												</div>
												
												
												<div class="col-md-5">
													<div class="form-group">  	
													<label class="col-md-3 control-label" >PO Request Date </label>
													<div class="col-md-5 col-xs-11">
														<input id="purchaseorder_date" name="purchaseorder_date" type="text" class="form-control" title="Date" value="<?=$purchaseorder_date?>" placeholder="Purchase Order Date" readonly>
													</div>
													</div>	
												</div>
											</div>
											<div class="col-md-12" style="margin-top:10px;">
												
												<?if($companyConfiguration['po_work_order_wise'] == 1){?>
												<div class="col-md-4">
													<div class="form-group">
													<label class="col-md-4 control-label"> Select Sales Order</label>
													<div class="col-md-8 col-xs-11">
														<?//=getcust_purchase($dbcon,$vender_id,$product_id)?>
														<select class="select2" name="sales_order_id" id="sales_order_id" onChange="get_product();get_work_o_no();"  title="Select Sales Order">
															<?=getsalesorderno($dbcon);?>	
														</select>
													</div>
													</div>	
												</div>

												<div class="col-md-3">
													<div class="form-group">
													<label class="col-md-4 control-label"> Select Work Order</label>
													<div class="col-md-8 col-xs-11">
														<?//=getcust_purchase($dbcon,$vender_id,$product_id)?>
														<select class="select2" name="workorder_id" id="workorder_id" onChange="get_product()"  title="Select Work Order">
															<?=getworkorderpo($dbcon);?>	
														</select>
													</div>
													</div>	
												</div>

												<div class="col-md-5">
													<div class="form-group">
													<label class="col-md-3 control-label"> Select Product Category</label>
													<div class="col-md-5 col-xs-11">
														<?//=getcust_purchase($dbcon,$vender_id,$product_id)?>
														<select class="select2" name="product_cat" id="product_cat" onChange="get_product()"  title="Select Product Category">
															<?=get_all_category($dbcon,"","");?>	
														</select>
													</div>
													</div>	
												</div>
												<?}else{ 
													$venreq="required";
												}?>
											</div>
										
												 
											<div class="col-md-12" style="margin-top:10px;">
											</div>			
											<div class="col-md-12" style="margin-top:10px;">
												<div id="sale_productdata"></div>	
											</div>
											<div class="clearfix"></div>
											<button type="submit" class="btn btn-success" id="save" name="save">Create PO</button>
											<a href="<?=ROOT.PURCHASE_ROOT.'po_req_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>					
										</div>
										<input type='hidden' name='mode' id='mode' value='req_po_to_main_po' />
										<!--<input type='hidden' name='eid' id='eid' value='<?=$product_id; ?>' />-->	
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
			
		</script>
	</body>
</html>

<?php
function getworkorderpo($dbcon){
	$sel = '';
	$str = '';
	 $query = "select req.rp_id,req.indent_no,po.po_req_no,so.sales_order_no from tbl_purchasetrntemp as gt
	left join tbl_request_product as req on req.rp_id = gt.po_ref_id
	left join tbl_set_main_process as po on po.sp_id = req.sp_id
	left join tbl_sales_ordertrn as sotrn on sotrn.sales_ordertrn_id=req.sales_order_trn_id
	left join tbl_sales_order as so on so.sales_order_id=sotrn.sales_order_id
	where gt.purchaseordertrn_status=0 and gt.po_trn_req_status=0 and req.sp_id!=0 group by req.sp_id";
	$rs_type=$dbcon->query($query);
	$str .='<option value="" >--Choose Work Order / Indent--</option>';
	while($row=mysqli_fetch_assoc($rs_type)){
		if(!empty($row['sales_order_no'])){
			$so=" - ".$row['sales_order_no'];
		}
		$str .= '<option '.$sel.' value="'.$row['rp_id'].'">'.$row['po_req_no'].' '.$so.'</option>';
		
	}
	$query1 = "select req.rp_id,req.indent_no,po.po_req_no from tbl_purchasetrntemp as gt
	left join tbl_request_product as req on req.rp_id = gt.po_ref_id
	left join tbl_set_main_process as po on po.sp_id = req.sp_id
	where gt.purchaseordertrn_status=0 and indent_status=1 and gt.po_trn_req_status=0 and req.sp_id=0 group by req.rp_id";
	$rs_type1=$dbcon->query($query1);
	while($row1=mysqli_fetch_assoc($rs_type1)){
		$str .= '<option '.$sel.' value="'.$row1['rp_id'].'">'.$row1['indent_no'].'</option>';
	}
	return $str;
}

function getsalesorderno($dbcon){
	$str = ''; $sel='';
	$query = "select GROUP_CONCAT(req.sp_id) as sp_id,req.indent_status,(select strn.sales_order_id
 from tbl_request_product as re 
 left join tbl_sales_ordertrn as strn on strn.sales_ordertrn_id = re.sales_order_trn_id  
 where main_request=1 and re.sp_id = req.sp_id group by re.sp_id ) as so_trn from tbl_purchasetrntemp as temp
	left join tbl_request_product as req on req.rp_id = temp.po_ref_id
	where temp.purchaseordertrn_status=0 and req.indent_status=3 and temp.po_trn_req_status=0 group by so_trn";

	$rs_type=$dbcon->query($query);
	$str .='<option value="" >--Choose Sales Order--</option>';
	while($row=mysqli_fetch_assoc($rs_type)){
		if($row['so_trn'] != ''){
			$get_so = "select sales_order_no from tbl_sales_order
			where sales_order_id=".$row['so_trn'];
			$exe = $dbcon->query($get_so);
			$rel = brp_mysqli_fetch_array($exe);
			if(!empty($rel['sales_order_no'])){
				$so=" - ".$row['sales_order_no'];
				$str .= '<option '.$sel.' value="'.$row['sp_id'].'">'.$rel['sales_order_no'].' '.$so.'</option>';
			}
		}		
	}
	return $str;
}
?>
