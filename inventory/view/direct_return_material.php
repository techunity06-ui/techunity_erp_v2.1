<?php 

	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	
	$form="Direct Return Materials";
	
	$branch_id = $_SESSION['branch_id'];
	
		
	$query="select issue_no from tbl_store_release where release_status = 1 and release_type = 1 order by release_id desc";
	$res = $dbcon->query($query);
	// $arr_issue_no = brp_mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Material Return</title>
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
			  		<form class="form-horizontal" role="form" id="store_return" action="javascript:;" method="post" name="store_return">
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel panel-body">
								<div class="col-md-12" style="margin-top: 30px;margin-bottom: 30px;">
									<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>Isuue No.</strong>
										</div>
										<div class="col-md-8">
											<!-- <input class="form-control" type="text" readonly="true" name="issue_no" id="issue_no" value="<?= $result['issue_no'] ?>"> -->
											<select id="issue_no" class="select2" name="issue_no" class="form-control">
												<option value="" >--Choose Issue No--</option>
												<?php 
													while($row=mysqli_fetch_assoc($res)){
														echo '<option value="'.$row['issue_no'].'">'.$row['issue_no'].'</option>';
													} ?>
											</select>
										</div>
									</div>
									
								<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>User</strong>
										</div>
										
										<div class="col-md-8">
											<select class="select2" name="user_id" id="user_id" required>
									  			<option value="">SELECT USER NAME</option>
												<?php 
													echo getalluser($dbcon,$result['to_user_id']);
												?>
		                					</select>
	                					</div>
									</div>
										<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>Branch</strong>
										</div>
										
										<div class="col-md-8">
											<select class="select2" name="branch_id" id="branch_id" required>
		                    								<?php $branch = isset($branch_id) ? $branch_id : '1000'; ?>
															<?=getBranchBox_new($dbcon, $branch);?>
		                					</select>
	                					</div>
									</div>
								</div>
															
								<div class="row">
									<div class="col-md-12">
								<div class="form-group">
									<table cellspacing="10" style="border-collapse:inherit;table-layout: fixed;" id="product_list" class="display table table-bordered table-striped">
										<tr id="field">
											
											<th  class="text-center">Product Detail</th>
											<th class="text-center">Godown</th>
											<th class="text-center hide_act_add" style="display:none;">Unit</th>

											<th class="text-center hide_act_add">Quantity</th>
											<th class="text-center hide_act_add">Unit</th>
											<th class="text-center hide_act_add">Conv Qty</th>
											<th class="text-center hide_act_add">Conv Unit</th>
											
										</tr>
										<tr id="field1">
											<td style="vertical-align:top;" width="25%">
												<input id="product_id" name="product_id" style="width:100%;" placeholder="Select product" class="" onchange="load_product_detail(this.value)" />
												<br/><br/>
												</td>
												<td>
												<select class="select2" name="godown_id" id="godown_id">
													<?=getgodown($dbcon,"")?>
												</select>
												
											</td>	
												<td style="vertical-align:top;" class="hide_act_add">
												<input type="number"  title="Enter Qty" min="0" id="product_base_qty" name="product_base_qty" onkeyup="product_convert_qty(1);" value="1"  class="form-control" />
								
												<input type="hidden" id="product_base_qty_hide" name="product_base_qty_hide" value="" />

											</td>
											<td style="vertical-align:top;" class="hide_act_add"> 
												<input class="form-control" type="text" name="product_base_unit_name" id="product_base_unit_name" value="" readonly />
												<input type="hidden" name="product_base_unit" id="product_base_unit"value="" />	
												<input type="hidden" id="edit_id" name="edit_id" value="">
											</td>	
											
											
											<td style="vertical-align:top;" class="hide_act_add">
												<input type="number"  title="Enter Qty" min="0" id="product_conv_qty" 

												name="product_conv_qty"  class="form-control" onkeyup="product_convert_qty(2);"  value="1"  />
												<!--onkeyup="product_convert_qty(2);"-->
												<input type="hidden" id="product_conv_qty_hide" name="product_conv_qty_hide" value="" />

												<input type="hidden"  title="" id="product_spec_hid" name="product_spec_hid"  class="form-control" />
												<input type="hidden"  title="" id="product_spec_hid_qty" name="product_spec_hid_qty"  class="form-control" />
												<input type="hidden"  title="" id="product_spec_act_qty" name="product_spec_act_qty"  class="form-control" />
											</td>
											<td style="vertical-align:top;" class="hide_act_add">
												<input class="form-control" type="text" id="product_conv_unit_name" name="product_conv_unit_name" value="" readonly />

												<input type="hidden" name="product_conv_unit" id="product_conv_unit"value="" />
											</td>
										
										</tr>
									</table>			
								</div>
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
									<a href="<?=ROOT.'dashboard'?>" type="button" id="btnCancel" class="btn btn-danger">Cancel</a>
							</div>	
							</section>
						</div>
					</div>
					<input type="hidden" name="inquiry_type" id="inquiry_type" value="1">
					<input type="hidden" name="mode" id="mode" value="add_direct_material_return">
				</form>
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
		</section>
		 
		<?php include_once($include.'include_js_file.php');?>   
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/direct_return_material.js?<?=time()?>"></script>
		<!--<script src="js/count.js"></script>-->
		<script>
			$(".select2").select2({
				width: '100%'
			});
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
