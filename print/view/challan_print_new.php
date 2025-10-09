<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$incPath = '../../include/';

// $bulkAccessArray = canCheckPermissionAccess($dbcon, [
// 	INVENTORY_RETURNABLE_CHANNAL_SLUG_PRINT,
// 	INVENTORY_RETURNABLE_CHANNAL_SLUG_READ
// ]);

// if(!in_array(INVENTORY_RETURNABLE_CHANNAL_SLUG_PRINT,$bulkAccessArray)){
// 	header("Location: ".DOMAIN."permission_access");
// }

$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="Challan Print";
$mode="Print";
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
$query="select invoice.*,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust_pincode,cust_mobile,gst_no,dis.mode_dispatch as modedis from tbl_returnable_channal as invoice 
left join tbl_ledger as cust on cust.l_id=invoice.cust_id
left join country_mst as country on country.countryid=cust.countryid
left join state_mst as state on state.stateid=cust.stateid
left join city_mst as city on city.cityid=cust.cityid
left join mode_of_dispatch as dis on dis.mode_dis_id = invoice.mode_dispatch
where id=$invoiceid";
$rel=mysqli_fetch_assoc($dbcon->query($query));	
$company_name=$rel['company_name'];
$cust_address=$rel['cust_address'];
$city_name=$rel['city_name'];
$state_name=$rel['state_name'];
$country_name=$rel['country_name'];
$cust_pincode=$rel['cust_pincode'];
$gst_no=$rel['gst_no'];
$contac_no =$rel['cust_mobile'];
$returnable_type = $rel['returnable_type'];
$challan_return_type = $rel['challan_return_type'];
$set="select * from tbl_company where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	
$order_date='';$dispatch_date='';

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>CHALLAN PRINT</title>
	<?php include_once($incPath.'include_css_file.php');?>
</head>
<body>
	<section id="container">
		<?php include_once($incPath.'include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($incPath.'left_menu.php');?>
		<section id="main-content">

			<section class="wrapper">

				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3 style=""><?=$form?> </h3>
							</header>	
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									<li><?=$form?></li>
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
								<span class="tools pull-left"> 
									<h1><?=$form?> - <?=$rel['channal_id']?></h1>
								</span> 
								<span class="tools pull-right"> 
									<a href="javascript:;" onClick="tableToExcel('adv-table', '<?=$form?>')" ><button class="btn btn-primary btn-flat" >Export Excel</button></a>	
								</span> 
								<div class="clearfix"></div>
							</header>	
							<div class="clearfix"></div>
							<div class="panel-body">
								<div class="adv-table" id="adv-table">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th class="text-center">Sr. No</th>
												<th class="text-center">Code No</th>
												<th class="text-center">Product Name</th>
												<th class="text-center">Qty</th>
												<th class="text-center">Lot No</th>
												<th class="text-center">Challan No.</th>
												<th class="text-center">Challan Date</th>
											</tr>
										</thead>
										<tbody>
											<?php$qry="select trn.*, product.product_name, product.product_icode, batch.stock_id, stock.batch_no,batch.qty FROM `tbl_returnable_channal_item` as trn left join tbl_returnable_batch_stock_tmp as batch on batch.returnable_trn_id = trn.id left join tbl_stock_trn as stock ON stock.stock_id = batch.stock_id left join product_mst as product on product.product_id=trn.item_id where trn.status=0 and trn.returnable_id=".$rel['id'];
											$result=$dbcon->query($qry);		
											$i=1;
											$cnt=mysqli_num_rows($result);
											while($row=mysqli_fetch_assoc($result))
											{
												?>
												<tr>
													<td class="text-center"><?=$i?></td>
													<td><?=$row['product_icode']?></td>
													<td><?=stripcslashes($row['product_name'])?></td>
													<td class="text-center"><?=$row['qty']?></td>
													<td class="text-center"><?=$row['batch_no']?></td>
													<td class="text-center"><?=$rel['channal_id']?></td>
													<td class="text-center"><?=date('d/m/Y',strtotime($rel['challan_date']))?></td>
												</tr>
											<?php$i++;
										} ?>
										</tbody>
									</table>
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
		<?php include_once($incPath.'footer.php'); ?>
		<!--footer end-->
	</section> 
	<script type="text/javascript">
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
