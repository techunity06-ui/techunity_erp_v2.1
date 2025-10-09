<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Calibration Tracking";
$maintenance_id=$dbcon->real_escape_string($_REQUEST['id']);
$query="SELECT qt.*, led.l_name, pro.product_name, led.cust_mobile from tbl_maintenance as qt
left join tbl_ledger as led on led.l_id=qt.cust_id
left join product_mst as pro on pro.product_id = qt.product_id
where qt.maintenance_id=$maintenance_id";
$rel=mysqli_fetch_assoc($dbcon->query($query));
$maintenance_date=date('d-m-Y',strtotime($rel['maintenance_date']));
$companyConfiguration=getCompanyConfiguration($dbcon);

$back_link = $_SERVER['HTTP_REFERER'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php');?>
</head>
<body>
	<section id="container"> <!--class="sidebar-closed"-->
		<?php include_once('../../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">

				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<a href="<?=$back_link?>" type="button" class="btn btn-info" style="float:right;"><i class="fa fa-arrow-left" aria-hidden="true"></i> Go Back</a>
								<h3><?='View '.$form?></h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?=ROOT.MAINTENANCE_ROOT.'maintenance_list'?>"><?=$form?> List</a></li>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>	
				</div>
				<!--state overview start-->
				<section class="panel">
					<div class="row">			
						<div class="col-md-12">
							<div class="panel-body">
								<div class="row">
									<div class="col-md-12">
										<header class="panel-heading breadcrumb text-center">
											<h3>CALIBRATION Details</h3>
										</header>
										<table class="display table table-bordered">
											<tr>
												<td width="35%"><strong>Maintenance No: </strong><?=$rel['maintenance_no']?></td>
												<td width="35%"><strong>Maintenance Date: </strong><?=date("d-M-Y",strtotime($rel['maintenance_date']))?></td>
												<td width="30%"><strong>Maintenance Amount: </strong><?=$rel['price']?></td>
											</tr>
											<tr>
												<td><strong>Company: </strong><?=$rel['l_name']?><input type="hidden" id="cust_id" name="cust_id" value="<?=$rel['cust_id']?>"><br><strong>Contact no: </strong><?=$rel['cust_mobile']?></td>
												<td><strong>Bill No: </strong><?=$rel['bill_no']?><br>
													<strong>Bill Date :</strong><?=date("d-M-Y h:i:s", strtotime($rel['bill_date']))?></td>
												<td><strong>Entry Date & Time: </strong><?=date("d-M-Y h:i:s", strtotime($rel['cdate']))?></td>
											</tr>
										</table>
									</div>
									<div class="col-md-12">
										<table class="table table-bordered table-stripped">
											<thead>
												<tr>
													<th>Sr No.</th>
													<th>Calculate Date</th>
													<th>Calibration No.</th>
													<th>Calibration Date</th>
													<th>Company Name</th>
													<th>Amount</th>
													<th>Due Date</th>
													<th>Remind Date</th>
													<th>LCI Used</th>
													<th>Acceptance</th>
													<th>TC Date</th>
												</tr>
											</thead>
											<tbody>
												<?php$qry = $dbcon->query("SELECT le.l_name, cali.*, cali_trn.calculate_date FROM tbl_calibration_date_trn AS cali_trn LEFT JOIN tbl_calibration AS cali ON cali.calibration_id = cali_trn.calibration_id LEFT JOIN tbl_ledger AS le ON le.l_id = cali.cust_id WHERE cali_trn.calibration_date_trn_status = 1 AND cali_trn.maintenance_id = ".$maintenance_id." ORDER BY cali_trn.calibration_date_trn_id ASC");
												$i = 1;
												while($res = brp_mysqli_fetch_assoc($qry)){ ?>
												<tr>
													<td><?=$i?></td>
													<td><?=date("d-M-Y", strtotime($res['calculate_date']))?></td>
													<td><?=$res['calibration_req_no']?></td>
													<td><?=date("d-M-Y", strtotime($res['calibration_req_date']))?></td>
													<td><?=$res['l_name']?></td>
													<td><?=$res['amount']?></td>
													<td><?=date("d-M-Y", strtotime($res['due_date']))?></td>
													<td><?=date("d-M-Y", strtotime($res['remind_date']))?></td>
													<td><?=$res['lci_used']?></td>
													<td><?=$res['acceptance']?></td>
													<td><?=date("d-M-Y", strtotime($res['tc_date']))?></td>
												</tr>
											<?php$i++; 
										} 
										$qrys = $dbcon->query("SELECT cali_trn.calculate_date FROM tbl_calibration_date_trn AS cali_trn WHERE cali_trn.calibration_date_trn_status = 0 AND cali_trn.maintenance_id = ".$maintenance_id." ORDER BY cali_trn.calibration_date_trn_id ASC");
												while($rese = brp_mysqli_fetch_assoc($qrys)){ ?>
												<tr>
													<td><?=$i?></td>
													<td><?=date("d-M-Y", strtotime($rese['calculate_date']))?></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
													<td></td>
												</tr>
											<?php$i++; } ?>
											</tbody>
										</table>
									</div>
								</div>
								<div class="clearfix"></div>
								<div class="col-md-12 text-center">
									<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>	
								</div>	
							</div>
						</div>
					</div>	
				</section>
			</div>
		</div>
		<!--state overview end-->
	</section>
</section>
<!--main content end-->
<!--footer start-->
<?php include_once('../include/preview_cust_dtls.php');?>
<?php include_once('../include/preview_cust_person_dtl.php');?>
<?php include_once('../../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   

<script src="<?=ROOT.MAINTENANCE_ROOT?>js/app/maintenance.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
</script>
</body>
</html>