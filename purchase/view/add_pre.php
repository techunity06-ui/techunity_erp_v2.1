<?php 
session_start();
include('../include/urlfile.php');	

// Initialize variables to prevent undefined warnings
$vender_id = $_REQUEST['vender_id'] ?? '';
$disable = $disable ?? '';
$rel = $rel ?? [];

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    PRE_VIEW
]);
if(!in_array(PRE_VIEW,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}

// FIX: Use strict comparison for strpos()
if(strpos($_SERVER['REQUEST_URI'], "pre_edit") !== false) {
   if(!in_array(PRE_VIEW,$bulkAccessArray)){
       header("Location: ".DOMAIN."permission_access");
   }
   
   $mode = "Edit";
   $pre_id = $dbcon->real_escape_string($_REQUEST['id']);

   $query = "select * from tbl_pre where pre_id=".$pre_id;
   $rel = mysqli_fetch_assoc($dbcon->query($query));
   $invoicetype_id = $rel['invoicetype_id'] ?? '';
   $invoicetype_id_dis='disabled';
   $pre_no = $rel['pre_no'] ?? '';
   $remark = $rel['remark'] ?? '';
   $date = date('d-m-Y',strtotime($rel['pre_date'] ?? 'now'));
   $branch_id = $rel['branch_id'] ?? '';
}else{
    $mode = 'Add';
    $pre_no='';
    $invoicetype_id='';
    $invoicetype_id_dis='';
    $date = date("d-m-Y");
    $branch_id = $_SESSION['branch_id'] ?? '';
}	
$form="Indent";
$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];

$set_conf=mysqli_fetch_assoc($dbcon->query($setconf));

$purchase_party_show = $set_conf['purchase_party_show'] ?? '';
$getspecialConfiguration=getspecialConfiguration($dbcon);
$companyConfiguration=getCompanyConfiguration($dbcon);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MANUAL INDENT</title>
		<?php include_once($include.'/include_css_file.php');?>
	</head>
	<body>
		<section id="container" >
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
										<li class="active"><?=$form?> Add</li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
									
								</header>	
								
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="pre_add" action="javascript:;" method="post" name="pre_add">
									<div class="row">
										<div class="col-md-12">	
    										<div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label">Series * </label>
                                                    <div class="col-md-8 col-xs-11">
                                                       <select class="select2" name="invoicetype_id" id="invoicetype_id" onchange="load_invoiceno(this.value)" required <?= $invoicetype_id_dis; ?>>
                                                            <option value="">--Select Series--</option>
                                                            <?=get_invoice_type_list($dbcon,MANUAL_INDENT_SERIES,$invoicetype_id)?>
                                                       </select>
                                                    </div>
                                                </div>
                                            </div>

											<div class="col-md-6">
												<div class="form-group">
													<label class="col-md-3 control-label">Pre No*</label>
													<div class="col-md-6">
													  <input id="pre_no" name="pre_no" type="text" class="form-control" title="Enter Pre No" value="<?=$pre_no?>" placeholder="Pre No" readonly >
													</div>
												</div>
											</div>
											
											<div class="col-md-6">
												<div class="form-group">
												   <label class="col-md-3 control-label">Date*</label>
												   <div class="col-md-6">
													  <input id="pre_date" name="pre_date" type="text" class="form-control default-date-picker required valid" title="Enter Pre Date" value="<?=$date?>" placeholder="Pre Date" >
													</div>
												</div>
											</div>
											<?php if(($companyConfiguration['branch_wise_manage'] ?? '')=='1'){ ?>
												<div class="col-md-6">
													<?php echo getBranchBox($dbcon, $branch_id, $rel['branch_id'] ?? '', false, true,'','3','6'); ?>
												</div>
											<?php } ?>
										</div>
										
										<div class="col-md-12" style="margin-top:30px">
											<div class="form-group">
												<table cellspacing="10" style="border-collapse:inherit; " id="product_list" class="display table table-bordered table-striped">
													<tr>
														<?php if(($getspecialConfiguration['oilfield_permission'] ?? 0)==1){ ?>
														<th width="10%"></th>
													<?php } ?>
														<?php if($set_conf['po_work_order_wise']==1){?>
															<th width="15%">Choose Sales Order</th>
															<th width="15%">Choose Work Order</th>
														<?php }?>
														

														<th width="15%">Choose Item</th>
														<th width="10%">Qty</th>
														<th width="15%">Vender</th>
														<th width="10%">Rate</th>
														<th width="10%">Attached Document</th>
														<th width="10%">Action</th>
													</tr>
													
													<tr>
														<?php if(($getspecialConfiguration['oilfield_permission'] ?? 0)==1){ ?>
														<td>
															<button accesskey="p" style="margin-top: 5px;" class="btn btn-round btn-info btn-xs" type="button" data-toggle="modal" value="R1" onclick="showproduct();" title="Short-Cut To Open PopUp, Shift + Alt + p "><i class="fa fa-plus"></i> Add Product</button>
														</td>
													<?php } ?>
														<?php if(($set_conf['po_work_order_wise'] ?? 0)==1){?>
															<td  style="max-width:0px">
																<select class="select2"  title="Select Sales Order" name="sales_order_id" id="sales_order_id" onChange="so_to_workorder_load(this.value,'');" >
																	<?=get_sales_order_indent($dbcon)?>
																</select>
															</td>
															<td  style="max-width:0px">
																<select class="select2"  title="Select Work Order" name="work_order_id" id="work_order_id" >
																	<?=get_work_order($dbcon)?>
																</select>
															</td>
														<?php }?>
														<td style="max-width:0px">
															<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
															<input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" onchange="product_detail(this.value)" />
															<br><br>
															<textarea class="form-control" name="product_desc" id="product_desc" placeholder="Description"></textarea>
														</td>
														<td style="max-width:0px">
															<input type="text"  title="Enter Qty" min="0" id="product_qty" name="product_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(2);" />
															<input type="hidden" name="unitid" id="unitid" value="" />
                                                            <input type="hidden" id="product_qty_hide" name="product_qty_hide" value="" />
															<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs" id="unit_show"></span>
															<div id="convert_unit_block" style="display:none;" >
                                                                <input type="text"  title="Enter Qty" min="0" id="product_conv_qty" name="product_conv_qty"  class="form-control numbersOnly" onkeyup="product_convert_qty(1);" />
                                                                <input type="hidden" name="conv_unitid" id="conv_unitid" value="" />
                                                                <input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />
                                                                <span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs" id="convert_unit_show">  </span>
                                                            </div>
															
														</td>
														
														<td style="max-width:0px">
															<select class="select2"  title="Select Vender" name="vender_id" id="vender_id" onchange="new_vendor(this.value);load_rate();">
																<?=getcust($dbcon,$vender_id,$purchase_party_show,$flag=0,$indent=1);?>
															</select>
															<br></br>
															<input type="text" name="vendor_name" id="vendor_name" title="Select Vender Name" style="display:none" class="form-control">
														</td>
														
														<td style="max-width:0px">
															<input type="number"  title="Enter Rate" min="0" id="rate" name="rate" data-pcard="0" data-pcardid="0"  class="form-control" />
														</td>
														
														<td>
															<input type="file" title="Attach Document" name="att_doc" id="att_doc" class="form-control">
															<br><br>
															<span id="uploaded_image"></span>
														</td>
														<td>
															<input type="hidden" id="img_name" name="img_name" value="">
															<input type="hidden" id="edit_id" name="edit_id" value="">
															<input type="button"  name="addrow" id="addrow" onClick="add_field()"  class="btn btn-primary" value="Add"/>
														</td>
													</tr>
												</table>
											</div>
										</div>
										
										<div class="col-md-12" id="show_prod_data" style="margin-top:40px;margin-bottom:30px">
											
										</div>

										<div class="col-md-8">
											<div class="form-group">
											   <label class="col-md-2 control-label">Remark </label>
											   <div class="col-md-6">
												  <textarea name="remark" id="remark" placeholder="Remark" title="Remark" class="form-control"><?=$remark?></textarea>
												</div>
											</div>
										</div>
										
										<div class="col-md-12" style="text-align:center;">
											<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
											<input type='hidden' name='eid' id='eid' value='<?=$pre_id?>' />
											<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
											<a href="<?=ROOT.PURCHASE_ROOT.'pre_list'?>" type="button" class="btn btn-danger">Cancel</a>
										</div>
									</div>
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
		<?php include_once($include_finance.'add_ledger.php');?> 
		<?php include_once($path.'administration/include/add_product.php');?>
		<?php include_once($path.'administration/include/add_hsn_in_popup.php');?>
		<script src="<?=ROOT.PURCHASE_ROOT?>js/app/pre.js?<?=time()?>"></script>
		<!-- <script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/product_mst.js?<?=time()?>"></script> -->
		<script src="<?=ROOT.ADMINISTRATION_ROOT?>js/app/hsn_master.js?<?php echo time(); ?>"></script>
		<script>
			$(".select2").select2({
				width: '100%'
			});
			
			$("select.selproduct").select2({
				width: '100%',
				minimumInputLength: 2,

			});	
			
			var today = new Date();
            
            // Get the same date from last month
            var lastMonth = new Date(today);
            lastMonth.setMonth(lastMonth.getMonth() - 1);
        
            // Adjust for months with fewer days (e.g., Feb 30th → Feb 28th)
            if (lastMonth.getDate() !== today.getDate()) {
                lastMonth.setDate(0); // go to last day of previous month
            }
        
            $('.default-date-picker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                endDate: today,
                startDate: lastMonth,
                todayHighlight: true
            });
			function cb(start, end) {
				$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			}
			cb(moment().subtract(29, 'days'), moment());


			$('.datepikerdemo').daterangepicker({       
				locale: {
					format: 'DD-MM-YYYY'
				},
				"autoApply": true,	
				"startDate": $('#from_date').val(),
				"endDate": $('#to_date').val(),	
				ranges: {
				   'Today': [moment(), moment()],
				   'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				   'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				   'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				   'This Month': [moment().startOf('month'), moment().endOf('month')],
				   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
				}
			}, cb);
			$('.date-set').click(function(){
				$('.datepikerdemo').trigger('click')
			});
		</script>
	</body>
</html>

