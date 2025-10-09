<?php 

	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
       PRODUCTION_STORE_LIST_SLUG_VIEW,PRODUCTION_STORE_LIST_SLUG_CREATE,PRODUCTION_STORE_LIST_SLUG_READ,PRODUCTION_STORE_LIST_SLUG_UPDATE,PRODUCTION_STORE_LIST_SLUG_DELETE,PRODUCTION_STORE_LIST_APPROVE,PRODUCTION_STORE_LIST_RETURN
]);

if(!in_array(PRODUCTION_STORE_LIST_RETURN,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
	
	$form="Production Return Materials";
	
	$branch_id = $_SESSION['branch_id'];
	
		$release_id=$dbcon->real_escape_string($_REQUEST['release_id']);
		$release_type=$dbcon->real_escape_string($_REQUEST['release_type']); 
		
		$qry="select *  from tbl_store_release where release_id = " . $release_id;	
		
		$result=brp_mysqli_fetch_assoc($dbcon->query($qry));

		if($release_type == '0'){
			$query="select ret.*,rel.issue_date,rel.issue_no,user.user_name,p.product_name,gd.gd_name from tbl_store_release_material_trn as ret left join tbl_store_release as rel on rel.release_id = ret.release_id left join users as user on user.user_id = rel.to_user_id left join product_mst as p on p.product_id = ret.product_id left join tbl_reserve_stock as stock on stock.p_id = ret.p_id and stock.product_id = ret.product_id left join mst_godown as gd on gd.gd_id = stock.godown_id where ret.release_id = " . $release_id;	
		}else{
			$query="select trn.*,p.product_name,gd.gd_name from tbl_store_release_trn as trn 
			left join tbl_store_release as rel on rel.release_id = trn.release_id 
			left join users as user on user.user_id = rel.to_user_id
			left join product_mst as p on p.product_id =.product_id
			left join mst_godown as gd on gd.gd_id = stock.godown_id
			where trn.release_id = " . $release_id;
			
		}
		// echo $query;die;	
		$res = $dbcon->query($query);
		
		$cnt=brp_mysqli_num_rows($res);

	// echo "<pre>";print_r($rel);die;
		$is_returnable = 0;
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
										<div class="col-md-6">
											<input class="form-control" type="text" readonly="true" name="issue_no" id="issue_no" value="<?= $result['issue_no'] ?>">
										</div>
									</div>
									<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>Isuue Date</strong>
										</div>

										<div class="col-md-6">
											<input id="issue_date" name="issue_date" type="text" class="form-control default-date-picker required valid" readonly="true" title="Issue Date" placeholder="Issue Date" value="<?= $result['issue_date']?>">
										</div>
									</div>
								<div class="col-md-4">
										<div class="col-md-4 text-right">
											<strong>User</strong>
										</div>
										
										<div class="col-md-8">
											<select class="select2" disabled name="user_id" id="user_id" required>
									  			<option value="">SELECT USER NAME</option>
												<?php 
													echo getalluser($dbcon,$result['to_user_id']);
												?>
		                					</select>
	                					</div>
									</div>
								</div>
								<input type="hidden" id ="release_id" name="release_id" value="<?= $release_id?>">
								<input type="hidden" id="release_type" name="release_type" value="<?= $release_type?>">
								
								<div class="row">
									<div class="adv-table">
										<table  class="display table table-bordered table-striped" id="dynamic_table_working">
												<th>#</th>
												<th>Product Name</th>
												<th>Godown</th>
												<th>Request Qty</th>
												<th>Release Qty</th>
												<th>Base Unit</th>
												<th>Return Qty </th>
											<?php if($cnt) { ?>

												<?php
												$x = 1;
												 while($rel=brp_mysqli_fetch_assoc($res)) { ?>
													<tr>
														<td> <?= $x?> </td>
														<td> <?= $rel['product_name'];?> </td>
														<td> <?= $rel['gd_name'];?> </td>
														<td><?= $rel['request_qty'];?> </td>
														<td><?= $rel['release_qty'];?> </td>
														<td><?=getunitname($dbcon,$rel['release_unit'])?></td>
														<td>
															<?php
															 $return_qty = $rel['release_qty'] - $rel['request_qty'];

															 if($return_qty > 0){ ?>

															 	<input type="text" class="form-control start_qty" name="start_qty1[]" data-product_name="<?=$rel['product_name']?>" data-pid="<?= $result['p_id']?>" data-product_id="<?= $rel['product_id']?>" data-release_trn_id="<?= $rel['id'];	?>" data-start_qty="<?=$return_qty?>" id="start_qty11" value="<?=$return_qty?>" onkeyup="check_start_validation();">
															<?php 
															$is_returnable = 1;
														} else {
																echo $return_qty;
															 } ?>
															
														</td>
													</tr>
												<?php 
												$x++; 
												} ?>	
											<?php } else { ?>
												<tr><td colspan="7"> <center>No Product Release.!</center></td></tr>
											 <?php }?>	
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
								<div class="col-md-12" style="display:flex;">
									<button type="button" class="btn btn-success" id="save" style="margin-left: 45%;display: <?php echo ($is_returnable)? 'block':'none';?>" onClick="store_return_material()" name="save">Save</button>
									<a href="<?=ROOT.'dashboard'?>" type="button" id="btnCancel" class="btn btn-danger" style="margin-left:  <?php echo ($is_returnable)? '20px;':'45%';?>">Cancel</a>
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
		<script src="<?=ROOT.INVENTORY_ROOT?>js/app/production_return_material.js?<?=time()?>"></script>
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
