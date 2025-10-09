<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';

$month = $dbcon->real_escape_string($_REQUEST['id']);
$cust_id = $dbcon->real_escape_string($_REQUEST['id1']);
$ledger_id = get_id_detail($dbcon,'tbl_customer','cust_id',$cust_id,'ledger_id');

	//$start=date('d-m-Y');
	//$end=date("d-m-Y", strtotime('+1 month'));

	//$month = date("m");

$form="Value Wise Target Details For "."<strong style='color:red'>".get_id_detail($dbcon,'tbl_customer','cust_id',$cust_id,'cust_name')."</strong>";

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
										<li><a href="<?=ROOT.CRM_ROOT.'value_wise_target'?>">Value Wise Target</a></li>
										<li class="active"><?=$form?> list</li>
									</ul>
								</div>
							</section>
							<!--breadcrumbs end -->
						</div>	
					</div>

					<div class="col-md-12" style="margin-top:20px;"></div>
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-12" style="margin-top:10px;">

									<input type="hidden" name="month" id="month" value="<?=$month?>" />
									<input type="hidden" name="month" id="month" value="<?=$cust_id?>" />

									<div class="col-md-12">
										<table class="table table-bordered table-hover table-striped">
											<thead>
												<th>#</th>
												<th>Month</th>
												<th>Target</th>
												<th>Outstanding</th>
												<th>Achieved</th>
											</thead>
											<tbody>
												<?php 
												$companyConfiguration=getCompanyConfiguration($dbcon);
												$outstanding = $companyConfiguration['enable_count_outstanding_target'];
												$cnt=1;
												$sel = $dbcon->query("select sum(forecast_amount_pr) as sum,(case when forecast_month =1 then 'Jan' when forecast_month =2 then 'Feb' when forecast_month =3 then 'Mar' when forecast_month =4 then 'Apr' when forecast_month =5 then 'May' when forecast_month =6 then 'Jun' when forecast_month =7 then 'Jul' when forecast_month =8 then 'Aug' when forecast_month =9 then 'Sep' when forecast_month =10 then 'Oct' when forecast_month =11 then 'Nov' when forecast_month =12 then 'Dec' else 0 end) as forecast_month from tbl_cust_forecast_pr where forecast_cust_id='$cust_id' AND isdelete='0' group by forecast_month");
												while($row = brp_mysqli_fetch_assoc($sel))
												{
													// $month = $row['forecast_month'];
													if($outstanding==1)
													{
														$last_month = $month-1;
														$last_month_name = date('F', mktime(0, 0, 0, $last_month, 10));
														// $check_total_forecast=0;
														$check_total_forecast=check_current_month_forecast($dbcon,$cust_id,1,$row['forecast_month']);

														if($check_total_forecast!=0)
														{
															$invoice_total = get_invoice_current_forecast($dbcon,$ledger_id,$row['forecast_month']);
															$total_outstanding = $check_total_forecast-$invoice_total;
														}
														else
														{
															$invoice_total="0";
															$total_outstanding="0";
														}
													}
													else
													{
														$last_month="";
														$last_month_name="";
														$invoice_total = "";
														$check_total_forecast="";
														$total_outstanding="NA";
													}
													// echo $check_total_forecast;die;
													echo "<tr>

													<th>".$cnt."</th>
													<td>".$row['forecast_month']."</td>
													<td>".$row['sum']."</td>
													<td>".(($total_outstanding > 0) ? $total_outstanding : '0.00')."</td>
													<td>".get_invoice_total($dbcon,$row['forecast_month'],$ledger_id)."</td>
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