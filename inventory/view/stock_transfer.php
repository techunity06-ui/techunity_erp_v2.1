<?php 
	session_start();

	include('../include/urlfile.php');

	/*$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    INVENTORY_STOCK_TRANSFER_SLUG_VIEW
	]);

	if(!in_array(INVENTORY_STOCK_TRANSFER_SLUG_VIEW,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }*/

	$form="Godown Stock Transfer";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	if(strpos($_SERVER['REQUEST_URI'], "stock_transfer_edit")==true){
		$mode="Edit";
		$grn_id=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select * from tbl_stock_transfer as mst
		where mst.stock_transfer_id=$grn_id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		//$grn_date=date('d-m-Y',strtotime($rel['grn_date'])); 
		$godown_transfer_date='';
		if($rel['stock_transfer_doc_date']!="1970-01-01" && $rel['stock_transfer_doc_date']!="0000-00-00" && $rel['stock_transfer_doc_date']!=""){
			$godown_transfer_date=date('d-m-Y',strtotime($rel['stock_transfer_doc_date']));
		}
		$transfer_no = $rel['stock_transfer_doc_no']; 
	}
	else{
		$mode="Add";
		$transfer_no = load_common_no($dbcon,46);
		$godown_transfer_date=date('d-m-Y');
		$back="stock_transfer_list";
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>STOCK TRANSFER</title>
		<?php include_once($include.'include_css_file.php');?>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<?php//include_once('../include/equick_link.php');?>
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.INVENTORY_ROOT.'stock_transfer_list'?>"><?=$form?> List</a></li>
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
											
											<div class="col-md-12 mtop20">
												<div class="col-md-4 col-md-offset-2">
													<label class="col-md-4 control-label" style="">Transfer No</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<input id="transfer_no" name="transfer_no" type="text" class="form-control" title="Enter Transfer" value="<?=$transfer_no?>" placeholder="Transfer No" required>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">Transfer Date</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<input id="transfer_date" name="transfer_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$godown_transfer_date?>" placeholder="Transfer Date">
													</div>  
												</div>											
											</div>	
											<div class="col-md-12 mtop20">
												<div class="col-md-4 col-md-offset-2">
													<label class="col-md-4 control-label" style="">From Godown</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<select class="select2" title="From Godown" id="from_godown_id" name="from_godown_id" onchange="get_child_godown(this.value);get_godown_branch(this.value,'from')">
																<?=get_all_parent_godown($dbcon,$rel['from_godown_id'],"")?>
														</select>
													</div>  
												</div>
												<div class="col-md-4 ">
													<label class="col-md-4 control-label" style="">To Godown</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<select class="select2" title="To Godown" id="to_godown_id" name="to_godown_id" onchange="get_godown_branch(this.value,'to');">
																<?=get_all_parent_godown($dbcon,$rel['to_godown_id'],"")?>
														</select>
													</div>  
												</div>
											</div>	

											<div class="col-md-12 mtop20">
												<div class="form-group">
													<div class="col-md-12 col-xs-11">
														<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
															<tr id="field">
																
																<th width="30%" class="text-center">Product</th>
																<!-- <th width="20%" class="text-center">Godown</th> -->
																<th width="20%" class="text-center">Unit</th>
																<th width="10%" class="text-center">Stock Qty</th>
																<th width="10%" class="text-center">Transfer Qty</th>
																<th width="10%" class="text-center">Action</th>
															</tr>
															<tbody id="field1" >
															<tr id="field">
																
																<td width="30%" >
																<!-- <select class="select2" name="product_id" id="product_id" title="Select Product" onChange="load_product_unit(this.value);" >
																	<?=getproduct($dbcon,0,'0,1,2,3,4,5')?>
																</select> -->
																<input id="product_id" name="product_id"  style="width:100%;" placeholder="Select product" onchange="load_available_stock_godown(this.value);load_product_unit(this.value);load_stock_qty();"/>
																<input type="hidden" id="godown_id" name="godown_id" value="" />
																</td>
																<!-- <td width="20%" >
																	<select class="form-control" name="godown_id" id="godown_id" title="Select Godown" onchange="load_stock_qty()">
																	
																</select> -->
																</td>
																<td width="20%" >
																<select class="form-control" name="unit_id" id="unit_id" title="Select Unit" onchange="load_stock_qty()">
																	
																</select>
																</td>
																<td width="20%" >
																<input id="stock_qty" name="stock_qty" type="text" class="form-control numbersOnly" title="Stock Qty" value="" placeholder="Stock Qty" readonly >
																</td>
																
																<td width="20%" >
																<input id="transfer_qty" name="transfer_qty" type="number" class="form-control numbersOnly" title="Transfer Qty" value="" placeholder="Transfer Qty"  >
																</td>
																<td width="10%" class="text-center">
																	<input type="hidden" name="edit_id" id="edit_id">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary product_add_direct" value="Add"/>

																	<input type="button"  name="addrow1" id="addrow1" onClick="open_batch_wise_qty()"  class="btn btn-primary product_add_batch_wise" value="Add" />
																</td>
															</tr>
															</tbody>
														</table>
													</div>
												</div>
												<div id="sale_productdata" ></div>
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
											<input type='hidden' name='from_branch_id' id='from_branch_id' value='' />
											<input type='hidden' name='to_branch_id' id='to_branch_id' value='' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['stock_transfer_id']?>' />
											<input type="hidden" id="isbatchwise" name="isbatchwise" value="">
											<input type='hidden' name='back' id='back' value='<?=$back?>' />
											<input type='hidden' name='pmode' id='pmode' value='<?=$pmode?>' />
											<div class="clearfix"></div>	
											<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
											<a href="<?=ROOT.INVENTORY_ROOT.'stock_transfer_list'?>" type="button" class="btn btn-danger">Cancel</a>
											<div class="col-md-4"></div>
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
		<?php include_once($include1.'add_batch_stock_transfer.php');?> 
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/stock_transfer.js?<?=time()?>"></script>
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
			<?if($mode=='Add'){?>
				show_data();
			<?}?>
			
			
		</script> 
	</body>
</html>