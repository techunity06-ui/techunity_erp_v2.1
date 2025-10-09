<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';
	
	$customer = $dbcon->real_escape_string($_REQUEST['customer']);
	$st_date = $dbcon->real_escape_string($_REQUEST['st_date']);
	$end_date = $dbcon->real_escape_string($_REQUEST['end_date']);
	
	$st_date_start = date("Y-m-d",strtotime($st_date));
	$st_date_end = date("Y-m-d",strtotime($end_date));

	$st_month = date("m",strtotime($st_date_start));
	$end_month = date("m",strtotime($st_date_end));

	$st_year = date("Y",strtotime($st_date_start));
	$end_year = date("Y",strtotime($st_date_end));

	$ledger_id = get_id_detail($dbcon,'tbl_customer','cust_id',$customer,'ledger_id');
	//echo $st_month;
	//echo $st_date;
	//$start=date('d-m-Y');
	//$end=date("d-m-Y", strtotime('+1 month'));
	
	//$month = date("m");

	$form="Target Report Details For  "."<strong style='color:red'>".get_id_detail($dbcon,'tbl_customer','cust_id',$customer,'cust_name')."</strong> From <strong  style='color:red'>".date("d/m/Y",strtotime($st_date))." TO ".date("d/m/Y",strtotime($end_date)) ."</strong>";

	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<?php include_once($include.'include_css_file.php');?>
		<style type="text/css">
			table tr th ,td{
				text-align: center !important;
			}
		</style>
	</head>
	<body>
		<section id="container">
			<?php include_once($include.'include_top_menu.php');?>
			<!--sidebar start-->
			<?php include_once($include.'left_menu.php');?>
			<!--sidebar end-->
			<!--main content start-->
			<section id="main-content">
				<section class="wrapper">

					<!--state overview start-->
					<section class="panel">
						

						<div class="row">
							<div class="col-lg-12">
								<!--breadcrumbs start -->
								<section class="panel">
										
									<div class="">
										<ul class="breadcrumb">
											<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
											<li><a href="<?=ROOT.CRM_ROOT.'target_report'?>">Target Report</a></li>
											<li class="active"><?=$form?> list</li>
										</ul>
									</div>
								</section>
								<!--breadcrumbs end -->
							</div>	
						</div>

						<div class="col-md-12" style="margin-top:20px;text-align: center !important">
							<h4><?=$form?></h4>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-md-12">
									<div class="col-md-12" style="margin-top:10px;">
										
										<div class="col-md-12">
											<table class="table table-bordered table-hover table-striped">
												<thead>
													<th>#</th>
									      			<th>Month</th>
									      			<th>Target</th>
									      			<th>Achieved</th>
									      			<th>Achived (%)</th>
												</thead>
												<tbody>
													<?php 
														$cnt=1;
														
														$sel = $dbcon->query("select * from tbl_cust_forecast_pr where forecast_cust_id='$customer' and forecast_type='1' and isdelete='0' and forecast_month between '$st_month' and '$end_month' and forecast_year between '$st_year' and '$end_year'");
														while($row = brp_mysqli_fetch_assoc($sel))
														{

															$target=$row['forecast_amount_pr'];

															$achivement = get_invoice_total($dbcon,$row['forecast_month'],$ledger_id,$row['forecast_year']);

															if($target!=0)
															{
																$achieve_per = ($achivement*100)/$target;
															}
															else
															{
																$achieve_per=0;
															}

															echo "<tr>

																<th>".$cnt."</th>
																<td>".$row['forecast_month'].'-'.$row['forecast_year']."</td>
																<td>".$target."</td>
																<td>".$achivement."</td>
																<td>".$achieve_per." % </td>
															</tr>";

															$cnt++;
														}
													?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
					<!--state overview end-->

				</section>
			</section>
<!--main content end-->
<!--footer start-->
<?php include_once($include.'footer.php');?>
<!--footer end-->
</section>
<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'include_js_file.php');?>   
<script src="<?=ROOT.CRM_ROOT?>js/app/dashboard_target.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
	
	$(document).ready(function() {
		Loading(true);	
		Unloading();
	});
</script>
</body>
</html>