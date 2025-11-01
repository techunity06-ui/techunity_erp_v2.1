<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	
	
	$id = $dbcon->real_escape_string($_REQUEST['id']);
	$complaint_no = $date = $customer_name = '';

	if(strpos($_SERVER['REQUEST_URI'], "edit_internal_chalan") == true){
		$form = "Edit Internal Chalan";
		$is_edit = true;
		$mode = 'edit_internal_chalan';
		$query = "SELECT *, c.complaint_no, c.complaint_date, l.l_name FROM tbl_internal_chalan tic 
			JOIN tbl_complaint c ON c.complaint_id = tic.complaint_id
			JOIN tbl_ledger l ON l.l_id = c.cust_id
			WHERE tic.complaint_id = '$id'";
			
		$rs_type = $dbcon->query($query);
		$totalRecords = brp_mysqli_num_rows($rs_type);

		if($totalRecords > 0) {
			$rel = brp_mysqli_fetch_assoc($dbcon->query($query));
			$complaint_no = $rel['complaint_no'];
			$date = date('d-m-Y',strtotime($rel['complaint_date']));
			$customer_name = $rel['l_name'];
			$int_chalan_no = $rel['int_chalan_no'];
		}
	} else {
		$form = "Create Internal Chalan";
		$is_edit = false;
		$mode = 'create_internal_chalan';
		$query = "SELECT c.complaint_no, pr.s_id, pr.s_date,pr.s_product, pr.s_qty, l.l_name, pm.product_name 
			FROM tbl_complaint c 
			JOIN tbl_complain_spare_part pr ON c.complaint_id = pr.s_comp_id
			JOIN product_mst pm ON pr.s_product = pm.product_id
			JOIN tbl_ledger l ON l.l_id = pr.s_cust_id 
			WHERE c.complaint_id = '$id' AND pr.sp_sent_status = 'no'";

		// die($query);
		$rs_type = $dbcon->query($query);
		$totalRecords = brp_mysqli_num_rows($rs_type);

		if($totalRecords > 0) {
			$rel = brp_mysqli_fetch_assoc($dbcon->query($query));
			$complaint_no = $rel['complaint_no'];
			$date = date('d-m-Y',strtotime($rel['s_date']));
			$customer_name = $rel['l_name'];
		}
		
		$int_chalan_no = get_series_by_type($dbcon, 'INTERNAL CHALAN', 17);
	}
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
<section id="container">
	<?php include_once('../include/include_top_menu.php');?>
	<!--sidebar start-->
	<?php include_once('../include/left_menu.php');?>
	<!--sidebar end-->
	<!--main content start-->
    <section id="main-content">
        <section class="wrapper">
			<div class="row">
				<div class="col-lg-12">
					<!--breadcrumbs start -->
					<section class="panel">
						<header class="panel-heading">
							<h3> <span class="english"><?php echo $form; ?></span></h3>
						</header>
						<div class="">
							<ul class="breadcrumb">
								<li>
									<a href="<?php echo ROOT.'dashboard'; ?>">
										<i class="fa fa-home"></i> Home
									</a>
								</li>
								<li><?php echo $form; ?></li>
							</ul>
						</div>
					</section>
					<!--breadcrumbs end -->
				</div>	
            </div>
            <!--state overview start-->
		  	<div class="row">			
				<div class="col-sm-12">
					<section class="panel">
						<header class="panel-heading">
							<h3><?php echo $form; ?></h3>
						</header>
						<div class="panel-body">
							<table class="table table-bordered">
								<tr>
									<th>Internal Chalan No:</th>
									<td><label class="form-control"><?php echo $int_chalan_no; ?></label>
									<th>Requested Date:</th>
									<td><?php echo $date; ?></td>
								</tr>							
								<tr>
									<th>Complain No:</th>
									<td><?php echo $complaint_no; ?></td>
									<th>Customer Name:</th>
									<td><?php echo $customer_name; ?></td>								
								</tr>
							</table>	
							<form class="form-horizontal" role="form" id="create_internal_chalan" action="javascript:;" method="post" name="create_internal_chalan">						
								<table class="table table-bordered">
									<tr>
										<th>Name</th>
										<th>Required Qty</th>
										<th>Qty</th>
										<?php if($is_edit) { ?>
											<th>Received Qty</th>
											<th>Returned Qty</th>
										<?php } ?>
										
									</tr>
									<?php if($totalRecords > 0) {
										$isStock = false;
										$readOnly = false;
										while($row = brp_mysqli_fetch_assoc($rs_type)) {
											$qty = $is_edit ? $row['req_qty'] : $row['s_qty'];
											$sp_id = $is_edit ? $row['sp_id'] : $row['s_id'];
											$total_qty = ($is_edit && $row['total_qty']) ? $row['total_qty'] : 0;
											$received_qty = ($is_edit && $row['received_qty']) ? $row['received_qty'] : 0;
											$return_qty = ($is_edit && $row['return_qty']) ? $row['return_qty'] : 0;
											$product_name = $is_edit ? $row['sp_name'] : $row['product_name']; 
											
											if(!$is_edit) {
												$chQuery = "SELECT * FROM tbl_internal_chalan
												WHERE complaint_id = '".$id."' AND sp_id = '$sp_id'";
												$chType = $dbcon->query($chQuery);
												$checkRecord = brp_mysqli_num_rows($dbcon->query($chQuery));
												$chalan = brp_mysqli_fetch_assoc($chType);
												if($checkRecord > 0) {
													// $is_edit = true;
													$readOnly = true;
													$total_qty = $chalan['total_qty'] ? $chalan['total_qty'] : $total_qty;
													$received_qty = $chalan['received_qty'] ? $chalan['received_qty'] : $received_qty;
													$return_qty = $chalan['return_qty'] ? $chalan['return_qty'] : $return_qty;
												}
											}
											?>
											<tr>
												<td><input type="text" readonly name="sp_name[]" value="<?php echo $product_name; ?>" class="form-control" /></td>
												<td class="text-center"><input type="text" readonly name="req_qty[]" value="<?php echo $qty; ?>" class="form-control" /></td>
												<?php 
													$current_stock = 0;
													$spQuery = "SELECT p.product_id, p.product_base_unit FROM tbl_complain_spare_part sp 
													JOIN product_mst p ON sp.s_product = p.product_id
													WHERE sp.s_id = ".$sp_id;
													
													$prdrel = brp_mysqli_fetch_assoc($dbcon->query($spQuery));
													if($prdrel) {
														$current_stock = get_current_stock_new($dbcon,$prdrel['product_id'],$prdrel['product_base_unit']);
													}

													if($current_stock) {
														$isStock = true;
														$maxQty = $qty ? (($qty > $current_stock) ? $current_stock : $qty) : 100; ?>
														<td class="text-center"><input type="number" <?php echo ($is_edit || $readOnly) ? 'readonly' : ''; ?> name="total_qty[]" value="<?php echo $total_qty; ?>" class="form-control totalQty" min="0" max="<?php echo $maxQty; ?>" /></td>
														<?php if($is_edit) {
															$maxValue = $total_qty ? (($total_qty > $current_stock) ? $current_stock : $total_qty) : 100; ?>
															<td class="text-center"><input type="number" name="received_qty[]" value="<?php echo $received_qty; ?>" class="form-control recQty" min="0" max="<?php echo $maxValue; ?>" /></td>
															<td class="text-center"><input type="number" name="return_qty[]" value="<?php echo $return_qty; ?>" class="form-control retQty" min="0" max="<?php echo $maxValue; ?>" /></td>
														<?php } ?>
													<?php } else { ?>
														<input type="hidden" name="total_qty[]" value="0" class="form-control totalQty" />
														<input type="hidden" name="received_qty[]" value="0" class="form-control recQty" />
														<input type="hidden" name="return_qty[]" value="0" class="form-control retQty" />
														<td colspan="3" class="text-center" style="vertical-align: middle;"><span style="color: red;">Current Stock is <?php echo $current_stock; ?></span></td>
													<?php } ?>
											</tr>
											<input type="hidden" name="sp_id[]" value="<?php echo $sp_id; ?>" />
										<?php } ?>
										<tr>
											<td colspan="8">
												<div class="col-md-12 col-md-offset-2">
													<?php if($isStock) { ?>
														<button type="submit" class="btn btn-success" id="save" name="save"><span class="english">Submit</span></button>
													<?php } ?>
													
													<a href="<?php echo ROOT.'spare_list_pending'?>" type="button" class="btn btn-danger"><span class="english">Cancel</span></a><div class="col-md-3"></div>			
												</div>
											</td>
										</tr>
									<?php } else { ?>
										<tr>
											<td colspan="8" align="center">No records found</td>
										</tr>
									<?php } ?>
								</table>							
								<input type="hidden" id="mode" name="mode" value="<?php echo $mode; ?>" />
								<input type="hidden" id="int_chalan_no" name="int_chalan_no" value="<?php echo $int_chalan_no; ?>" />
								<input type="hidden" id="complaint_id" name="complaint_id" value="<?php echo $id; ?>" />								
							</form>
						</div>
					</section>
				</div>
			</div>
			<!--state overview end-->
    	</section>
	</section>
	<!--main content end-->
	<!--footer start-->
	
	<?php include_once('../include/footer.php');?>
    <!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../include/include_js_file.php');?>   

<script src="<?php echo ROOT; ?>js/app/interal_chalan.js?<?php echo time(); ?>"></script>

<script type="text/javascript">
	$(".select2").select2({
		width: '100%'
	});
</script>
</body>
</html>