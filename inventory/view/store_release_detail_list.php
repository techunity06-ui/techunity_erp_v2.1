<?php 

	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;

		
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
       PRODUCTION_STORE_LIST_SLUG_VIEW,PRODUCTION_STORE_LIST_SLUG_CREATE,PRODUCTION_STORE_LIST_SLUG_READ,PRODUCTION_STORE_LIST_SLUG_UPDATE,PRODUCTION_STORE_LIST_SLUG_DELETE,PRODUCTION_STORE_LIST_APPROVE,PRODUCTION_STORE_LIST_RETURN
]);

if(!in_array(PRODUCTION_STORE_LIST_APPROVE,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
	
	$id=$dbcon->real_escape_string($_REQUEST['id']);
	$type=$dbcon->real_escape_string($_REQUEST['type']);
	
	$form="Direct Material Release";
		
	if(empty($_SESSION['start']))
	{
		$start = date('1-m-Y');
		$end = date("d-m-Y");
	}
	else
	{
		$start = $_SESSION['start'];
		$end = $_SESSION['end'];
	}
	
	
	
	$_SESSION['redirect_page'] = 'store_request_detail_list';
	//echo $type;
	//var_dump(round(8.8, 0, PHP_ROUND_HALF_ODD));
	$branch_id = $_SESSION['branch_id'];
	$date=date('d-m-Y');
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>DIRECT MATERIAL RELEASE</title>
		<?php include_once($include.'include_css_file.php');?>
		<link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
	</head>
	<body>
		<section id="container" >
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$form?></h3>
								</header>
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									</ul>
								</div>
							</section>
						</div>
			  		</div>
			  		<form class="form-horizontal" role="form" id="store_release" action="javascript:;" method="post" name="store_release">
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel panel-body">
								<div class="col-md-12" style="margin-top: 30px;margin-bottom: 30px;">
									<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>Isuue No.</strong>
										</div>
										<div class="col-md-6">
											<input class="form-control" type="text" readonly="true" name="issue_no" id="issue_no" value="<?= get_issue_no($dbcon); ?>">
										</div>
									</div>
									<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>Isuue Date</strong>
										</div>

										<div class="col-md-6">
											<input id="issue_date" name="issue_date" type="text" class="form-control default-date-picker required valid" title="Issue Date" placeholder="Issue Date" value="<?=$date?>">
										</div>
									</div>
									<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>Branch</strong>
										</div>
										
										<div class="col-md-6">
											<select class="select2" name="branch_id" id="branch_id" required>
		                    								<?php $branch = isset($branch_id) ? $branch_id : '1000'; ?>
															<?=getBranchBox_new($dbcon, $branch);?>
		                					</select>
	                					</div>
									</div>
								
								</div>
								<div class="col-md-12" style="margin-bottom: 30px;">
										<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>User</strong>
										</div>
										
										<div class="col-md-8">
											<select class="select2" name="user_id" id="user_id" required>
									  			<option value="">SELECT USER NAME</option>
												<?php 
													echo getalluser($dbcon,0);
												?>
		                					</select>
	                					</div>
									</div>
								</div>
								<div class="col-md-12">
								<div class="form-group">
									<table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
										<tr id="field">
											
											<th  class="text-center">Product Detail</th>
											<th class="text-center">Godown</th>
											<th class="text-center hide_act_add" style="display:none;">Unit</th>

											<th class="text-center hide_act_add">Quantity</th>
											<th class="text-center hide_act_add" style="display:none;">UOM</th>
											<th class="text-center hide_act_add" style="display:none;">ACtual Qty</th>
											
											<th class="text-center">Returnable</th>
											<th class="text-center"></th>
										</tr>
										<tr id="field1">
											<td style="vertical-align:top;" width="25%">
												<input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" class="" onchange="load_product_detail(this.value);check_stock(this.value)"/>
												<br/><br/>
												</td>
												<td>
												<select class="select2" name="godown_id" id="godown_id" onchange="check_stock()">
													<?=getgodown($dbcon,$godown_id)?>
												</select>
												
											</td>	
												
											<td style="vertical-align:top;display:none;" class="hide_act_add"> 
												<input class="form-control" type="text" name="product_base_unit_name" id="product_base_unit_name" value="" readonly />
												<input type="hidden" name="product_base_unit" id="product_base_unit"value="" />	
												<input type="hidden" id="edit_id" name="edit_id" value="">
											</td>	
											<td style="vertical-align:top;" class="hide_act_add">
												<input type="number"  title="Enter Qty" min="0" id="product_base_qty" name="product_base_qty" onkeyup="product_convert_qty(1);" value="1"  class="form-control" />
												
												<label class="label label-info current_stock fa" style="display:none; margin-top: 10px;"> Current Stock : <span id="current_stock">0</span> </label>
												<input type="hidden" id="product_base_qty_hide" name="product_base_qty_hide" value="" />

											</td>
											<td style="vertical-align:top;display:none;" class="hide_act_add">
												<input class="form-control" type="text" id="product_conv_unit_name" name="product_conv_unit_name" value="" readonly />

												<input type="hidden" name="product_conv_unit" id="product_conv_unit"value="" />
											</td>
											
											<td style="vertical-align:top;display:none;" class="hide_act_add">
												<input type="number"  title="Enter Qty" min="0" id="product_conv_qty" 

												name="product_conv_qty"  class="form-control" onkeyup="product_convert_qty(2);"  value="1"  />
												<!--onkeyup="product_convert_qty(2);"-->
												<input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />

												<input type="hidden"  title="" id="product_spec_hid" name="product_spec_hid"  class="form-control" />
												<input type="hidden"  title="" id="product_spec_hid_qty" name="product_spec_hid_qty"  class="form-control" />
												<input type="hidden"  title="" id="product_spec_act_qty" name="product_spec_act_qty"  class="form-control" />
											</td>
											
											<td>
												<select class="select2" name="returnable" id="returnable">
													<option value="0">No</option>
													<option value="1">Yes</option>
												</select>
											</td>		
											<td style="vertical-align:top;">
												<input type="button" id="add_row" class="btn btn-primary" data-original-title="Add Process" data-toggle="tooltip" data-placement="top" onclick="add_field();" value="Add"/>
											</td>
											
										</tr>
									</table>			
								</div>
							</div>
								<div class="row">
									<div class="adv-table">
										<table  class="display table table-bordered table-striped" id="dynamic_table_working">
										</table>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<label class="col-md-2 control-label">Remarks</label>
										<div class="col-md-6 col-xs-11">
											<textarea id="remark" name="remark" class="form-control" rows="3"><?=$rel['remark']?></textarea> 
										</div>
									</div>
								</div>	
								<div class="col-md-12 text-center">
									<button type="submit" class="btn btn-success" id="save" name="save">Save</button>
									<a href="<?=ROOT.'dashboard'?>" type="button" class="btn btn-danger">Cancel</a>
									
							</div>	
							</section>
						</div>
					</div>
					<input type="hidden" id="stock_release_count" name="stock_release_count" value="" />
					<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
					<input type="hidden" name="mode" id="mode" value="release_stock_material">
				</form>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		 
		<?php include_once($include.'include_js_file.php');?>   
		<?php include_once($include1.'store_release_detail_modal.php'); ?>
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/store_release_detail_list.js?<?=time()?>"></script>
		<!--<script src="js/count.js"></script>-->
		<script>
			$(".select2").select2({
				width: '100%'
			});
			$('#returnable').select2({
				width: '100%'
			})
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
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
			var tableToExcel = (function() {
			 var uri = 'data:application/vnd.ms-excel;base64,'
			   , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
			   , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
			   , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
			 return function(table, name) {
			   if (!table.nodeType) table = document.getElementById(table)
			   var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
			   window.location.href = uri + base64(format(template, ctx))
			 }
			})()
		</script>
	</body>
</html>
