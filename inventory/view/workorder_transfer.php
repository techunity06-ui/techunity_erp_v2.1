<?php 
	session_start();

	include('../include/urlfile.php');

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    INVENTORY_WORKORDER_TRANSFER_SLUG_VIEW,INVENTORY_WORKORDER_TRANSFER_SLUG_CREATE,INVENTORY_WORKORDER_TRANSFER_SLUG_UPDATE
	]);

	if(!in_array(INVENTORY_WORKORDER_TRANSFER_SLUG_CREATE,$bulkAccessArray)){
        header("Location: ".DOMAIN."permission_access");
    }
   
   

	$form="Workorder Stock Transfer";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	$branch_id = $_SESSION['branch_id'];
	if(strpos($_SERVER[REQUEST_URI], "workorder_transfer_edit")==true){
		$mode="Edit";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		 $query="select * from tbl_workorder_transfer as mst
		where mst.wo_stk_transfer_id=$id";
		$rel=mysqli_fetch_assoc($dbcon->query($query));	
		$branch_id = $rel['branch_id'];
		//$grn_date=date('d-m-Y',strtotime($rel['grn_date'])); 
		$wo_transfer_date='';
		if($rel['wo_stk_transfer_date']!="1970-01-01" && $rel['wo_stk_transfer_date']!="0000-00-00" && $rel['wo_stk_transfer_date']!=""){
			$wo_transfer_date=date('d-m-Y',strtotime($rel['wo_stk_transfer_date']));
		}
		$transfer_no = $rel['wo_stk_transfer_no']; 
		 if(!in_array(INVENTORY_WORKORDER_TRANSFER_SLUG_UPDATE,$bulkAccessArray)){
	        header("Location: ".DOMAIN."permission_access");
	    }
	}
	else{
		$mode="Add";
		$transfer_no = load_common_no($dbcon,WO_STOCK_TRANSFER);
		$wo_transfer_date=date('d-m-Y');
		$back="workorder_transfer_list";
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));

	$company_config = getCompanyConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>WORKORDER TRANSFER LIST</title>
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
										<li><a href="<?=ROOT.INVENTORY_ROOT.'workorder_transfer_list'?>"><?=$form?> List</a></li>
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
									<form class="form-horizontal" role="form" id="workorder_transfer_add" action="javascript:;" method="post" name="workorder_transfer_add" enctype="multipart/form-data">
										<div class="row"> 
											
											<div class="col-md-12 mtop20">
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">Transfer No</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<input id="transfer_no" name="transfer_no" type="text" class="form-control" title="Enter Transfer" value="<?=$transfer_no?>" readonly placeholder="Transfer No" required>
													</div>  
												</div>
												<div class="col-md-4">
													<label class="col-md-4 control-label" style="">Transfer Date</label>
													<div class="col-md-8" style="padding-left: 9px;">
														<input id="transfer_date" name="transfer_date" type="text" class="form-control default-date-picker required valid" title="Date" value="<?=$wo_transfer_date?>" placeholder="Transfer Date" readonly>
													</div>  
												</div>		
												<?php if ($company_config['branch_wise_manage'] == '1') { ?>
			                                    <div class="col-md-4">
			                                       <div class="form-group">

			                                          <label class="col-md-4 control-label">Branch *</label>
			                                          <div class="col-md-8 col-xs-11">
			                                             <select class="select2" name="branch_id" id="branch_id" required>
			                                                <?php $branch = isset($edit_branch_id) ? $edit_branch_id : (isset($branch_id) ? $branch_id : '1000'); ?>
			                                                <?= getBranchBox_new($dbcon, $branch, 'all'); ?>
			                                             </select>

			                                          </div>
			                                       </div>
			                                    </div>
			                                <?php}else{ ?>
			                                       <input type="hidden" name="branch_id" id="branch_id" value="<?=$company_config['default_branch_id']?>" />
			                                    <?php} ?>									
											</div>	
											
											<div class="col-md-12 mtop20">
												<div class="form-group">
													<div class="col-md-12 col-xs-11">
														<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
															<tr id="field">
																<th width="15%" class="text-center">From Workorder</th>
																<th width="15%" class="text-center">From Product</th>
																<th width="15%" class="text-center">To Workorder</th>
																<th width="15%" class="text-center">To Product</th>
																<th width="10%" class="text-center">Stock Qty</th>
																<th width="10%" class="text-center">Unit</th>
																<th width="10%" class="text-center">Transfer Qty</th>
																<th width="10%" class="text-center">Action</th>
															</tr>
															<tbody id="field1" >
															<tr id="field">
																<td width="15%" >
																	<select class="select2 form-control" name="from_workorder_id" id="from_workorder_id" title="Select Workorder" onchange="get_from_product_list()">
																	
																</select>
																</td>
																<td width="15%" >
																<select class="form-control" name="from_product_id" id="from_product_id" title="Select Product" onchange="load_product_unit(this.value)">
																	
																</select>
																</td>
																<td width="15%" >
																<select  class="select2 form-control" name="to_workorder_id" id="to_workorder_id" title="Select Workorder"  onchange="valid_workorder()">
																
																</select>
																</td>
																<td width="15%" >
																<select class="form-control" name="to_product_id" id="to_product_id" title="Select Product" onchange="valid_workorder_product()">
																	
																</select>
																</td>

																<td width="10%" >
																<input id="stock_qty" name="stock_qty" type="text" class="form-control numbersOnly" title="Stock Qty" value="" placeholder="Stock Qty" readonly >
																</td>
																<td width="10%" >
																<select class="form-control" name="unit_id" id="unit_id" title="Select Unit">
																	
																</select>
																</td>
																<td width="10%" >
																<input id="transfer_qty" name="transfer_qty" type="number" class="form-control numbersOnly" title="Transfer Qty" value="" placeholder="Transfer Qty"  >
																</td>
																<td width="10%" class="text-center">
																	<input type="hidden" name="edit_id" id="edit_id">
																<input type="button"  name="addrow" id="addrow" onClick="return add_field();"  class="btn btn-primary product_add_direct" value="Add"/>

																	<!-- <input type="button"  name="addrow1" id="addrow1" onClick="open_batch_wise_qty()"  class="btn btn-primary product_add_batch_wise" value="Add" /> -->
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
											<input type='hidden' name='from_branch_id' id='from_branch_id' value='' />
											<input type='hidden' name='to_branch_id' id='to_branch_id' value='' />
											<input type='hidden' name='eid' id='eid' value='<?=$rel['wo_stk_transfer_id']?>' />
											<input type="hidden" id="isbatchwise" name="isbatchwise" value="">
											<input type='hidden' name='back' id='back' value='<?=$back?>' />
											<input type='hidden' name='pmode' id='pmode' value='<?=$pmode?>' />
											<div class="clearfix"></div>	
											<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
											<a href="<?=ROOT.INVENTORY_ROOT.'workorder_transfer_list'?>" type="button" class="btn btn-danger">Cancel</a>
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
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/workorder_transfer.js?<?=time()?>"></script>
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