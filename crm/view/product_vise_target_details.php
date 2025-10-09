<?php 
session_start();
include('../include/urlfile.php');
// $incPath = $path.'include/';
	
	$product_id = $dbcon->real_escape_string($_REQUEST['id']);

	$form="Product Wise Target Details For "."<strong style='color:red'>".get_id_detail($dbcon,'product_mst','product_id',$product_id,'product_name')."</strong>";

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
											<li><a href="<?=ROOT.CRM_ROOT.'product_wise_target'?>">Product Wise Target</a></li>
											<li class="active"><?=$form?></li>
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
									      			<th>Customer</th>
									      			<th>Total</th>
												</thead>
												<tbody>
													<?php 
														$cnt=1;
														$sel = $dbcon->query("select sum(tr.product_amount) as sum,i.cust_id,l.l_name
															from tbl_invoicetrn as tr
															left join tbl_invoice as i on i.invoice_id=tr.invoice_id
															left join tbl_ledger as l on l.l_id = i.cust_id 
															where tr.product_id='$product_id' and tr.user_id='$_SESSION[user_id]'
															group by i.cust_id");
														while($row = brp_mysqli_fetch_assoc($sel))
														{

															echo "<tr>

																<th>".$cnt."</th>
																<td>".$row['l_name']."</td>
																<td>".$row['sum']."</td>	
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