<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php"); 
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

	/*$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    INVENTORY_STOCK_TRANSFER_SLUG_VIEW
	]);

	if(!in_array(INVENTORY_STOCK_TRANSFER_SLUG_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }*/

	$form="Work order Agains Stock Transfer";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	if(strpos($_SERVER[REQUEST_URI], "so_stock_transfer_edit")==true){
		$mode="Edit";
		$grn_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_work_order_stock_transfer as mst
		where mst.work_order_transfer_id=$grn_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		//$grn_date=date('d-m-Y',strtotime($rel['grn_date'])); 
		$work_order_transfer_date='';
		if($rel['work_order_transfer_date']!="1970-01-01" && $rel['work_order_transfer_date']!="0000-00-00" && $rel['ref_date']!=""){
			$ref_date=date('d-m-Y',strtotime($rel['work_order_transfer_date']));
		} 
	}
	else{
		$mode="Add";
		$ref_date=date('d-m-Y');
		$back="grn_list";
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
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
					<?php //include_once('../include/equick_link.php');?>
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.'grn_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="stock_transfer_add" action="javascript:;" method="post" name="stock_transfer_add" enctype="multipart/form-data">
										<div class="row"> 
											<div class="col-md-12" style="margin-top:10px;">
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">Transfer No</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<input id="transfer_no" name="transfer_no" type="text" class="form-control" title="Enter Transfer" value="<?=$rel['work_order_transfer_no']?>" placeholder="Transfer No" required>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">Transfer Date</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<input id="transfer_date" name="transfer_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$ref_date?>" placeholder="Transfer Date">
													</div>  
												</div>
											</div>	
											<div class="col-md-12" style="margin-top:10px;"></div>	
											<div class="col-md-12">
												<div class="form-group">
													<div class="col-md-12 col-xs-11">
														<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
															<tr id="field">
																<th width="20%" class="text-center">Product</th>
																<th width="20%" class="text-center">Sales Order</th>
																<th width="10%" class="text-center">Reserve Qty</th>
																<th width="20%" class="text-center">Transfer Sales Order</th>
																<th width="10%" class="text-center">Transfer Qty</th>
																<th width="10%" class="text-center">Action</th>
															</tr>
															<tbody id="field1" >
															<tr id="field">
																<td width="20%" >
																<select class="select2" name="product_id" id="product_id" title="Select Product" onChange="get_order_no(this.value);" >
																	<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
																</select>
																</td>
																<td width="20%" >
																<select class="select2" name="work_order_id" id="work_order_id" title="Select Work Order"
																onchange="reserve_stock_check(this.value)" >
																	
																</select>
																</td>
																<td width="20%" >
																<input id="reserve_qty" name="reserve_qty" type="text" class="form-control" title="Reserve Qty" value="" placeholder="Reserve Qty" readonly >
																</td>
																<td width="20%" >
																<select class="select2" name="transfer_work_order_id" id="transfer_work_order_id" title="Select Work Order" onChange="pending_reserve_stock_check(this.value);" >
																	
																</select>
																</td>
																<td width="20%" >
																<input id="transfer_qty" name="transfer_qty" type="text" class="form-control" title="Transfer Qty" value="" placeholder="Transfer Qty"  >
																</td>
																<td width="10%" class="text-center">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary" value="Add"/>
																</td>
															</tr>
															</tbody>
														</table>
													</div>
												</div>
												<div id="sale_productdata"></div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
														</div>
													</div> 
												</div>
												<div class="clearfix"></div>	
											</div>
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['work_order_transfer_id']?>' />
											<input type='hidden' name='back' id='back' value='<?=$back?>' />
											<input type='hidden' name='pmode' id='pmode' value='<?=$pmode?>' />
											<div class="clearfix"></div>	
											<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
											<a href="<?=ROOT.'stock_transfer_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
										</div>
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once('../include/footer.php');?>
		</section>
		<?php include_once('../include/include_js_file.php');?>   
		<script src="<?=ROOT?>js/app/so_stock_transfer.js?<?=time()?>"></script>
		<script>
			//$('#container').addClass('sidebar-closed');
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
			<?php if($mode=='Add'){?>
			//load_grn_no();
			show_data();
			<?php }?>
			
		</script> 
	</body>
</html>