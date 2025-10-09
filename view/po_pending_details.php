<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");

include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$form="Po Pending Report";
$userid=$_SESSION['user_id'];

$type=$dbcon->real_escape_string($_REQUEST['id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../include/include_css_file.php');?>
</head>
<body>
	<section id="container" >
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
								<h3 style="float:left;"><?=$form?></h3><br>
								<?php //include_once('../include/reporthead_menu.php');?>
								
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li ><a href="<?=ROOT.'purchase_dashboard'?>"><?=$form?></a></li>
									
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
								<!-- <span class="tools pull-right">
									<a href="javascript:;" onClick="tableToExcel('adv-table', 'Instalment Collection')" ><button class="btn btn-success btn-flat" >Export Excel</button></a>	
								</span>
								<span class="tools pull-right">
									<button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>											
								</span>	 -->
								<?=$form?>
							</header>				
							<div class="panel-body">
								<div class="row">
									<div class="adv-table" id="adv-table" >
										<table width="100%" class="maintable" style="border-collapse: collapse;border-top:none !important;" id="invoice_type" cellpadding="0" cellspacing="0" >
											<tr>
												<td>PO NO</td>
												<td>PO Date</td>
												<td>Vender Name</td>
												<td>Product Name</td>
												<td>Pending Qty</td>
												<td>Due Date</td>
											</tr>
											<?php 
											$today_date = date('Y-m-d');
											if($type==1){
												$over_due_inword="select pmst.product_name,po.purchaseorder_no,po.purchaseorder_date,(del.product_qty-del.used_qty) as pending_qty,del.delivery_date,led.l_name from `tbl_purchaseorder_delivery_date` as del 
												left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id = del.purchaseordertrn_id
												left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
												left join product_mst as pmst on pmst.product_id=trn.product_id
												left join tbl_ledger as led on led.l_id=po.vender_id
												where po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status=1 and trn.used_status=0 and del.delivery_date<'".$today_date."' and del.grn_status=0 and del.company_id=".$_SESSION['company_id'];
											}else if($type==2){
												$over_due_inword="select pmst.product_name,po.purchaseorder_no,po.purchaseorder_date,(del.product_qty-del.used_qty) as pending_qty,del.delivery_date,led.l_name from `tbl_purchaseorder_delivery_date` as del 
												left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id = del.purchaseordertrn_id
												left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
												left join product_mst as pmst on pmst.product_id=trn.product_id
												left join tbl_ledger as led on led.l_id=po.vender_id
												where po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status=1 and trn.used_status=0 and del.delivery_date='".$today_date."' and del.grn_status=0 and del.company_id=".$_SESSION['company_id'];

											}else{
												 $over_due_inword="select pmst.product_name,po.purchaseorder_no,po.purchaseorder_date,(del.product_qty-del.used_qty) as pending_qty,del.delivery_date,led.l_name from `tbl_purchaseorder_delivery_date` as del 
												left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id = del.purchaseordertrn_id
												left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
												left join product_mst as pmst on pmst.product_id=trn.product_id
												left join tbl_ledger as led on led.l_id=po.vender_id
												where po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status=1 and trn.used_status=0 and del.grn_status=0 and del.company_id=".$_SESSION['company_id'];

											}
											$result=$dbcon->query($over_due_inword);		
											$i=1;
											$cnt=mysqli_num_rows($result);
											while($row=mysqli_fetch_assoc($result))
												{ ?>
													<tr>
														<td><?=$row['purchaseorder_no']?></td>
														<td><?=$row['purchaseorder_date']?></td>
														<td><?=$row['l_name']?></td>
														<td><?=$row['product_name']?></td>
														<td><?=$row['pending_qty']?></td>
														<td><?=$row['delivery_date']?></td>
													</tr>
												<?php 	} ?>
												</table>
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
				<?php include_once('../include/footer.php');?>
				<!--footer end-->
			</section>

			<!-- js placed at the end of the document so the pages load faster -->
			<?php include_once('../include/include_js_file.php');?>   
			<!--<script src="<?=ROOT?>js/app/employee_expense.js?<?=time()?>"></script>-->


</body>
</html>
