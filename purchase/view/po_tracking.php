<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="PO";
$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
$query="select po.*,l.l_name,l.gst_no,l.m_address,l.cust_mobile from tbl_purchaseorder as po 
left join tbl_ledger as l on l.l_id = po.vender_id
where po.purchaseorder_id=".$purchaseorder_id;
$rel=brp_mysqli_fetch_array($dbcon->query($query));


$purchaseorder_date='';
if($rel['purchaseorder_date']!="1970-01-01" && $rel['purchaseorder_date']!="0000-00-00"){
	$purchaseorder_date=date('d-m-Y',strtotime($rel['purchaseorder_date']));
}

if($rel['po_type']==0){
	$type = "Goods";
}else if($rel['po_type']==1){
	$type = "Services";
}else{
	$type = "Jobwork";
}

if($rel['po_approval_status']=='3'){
	$status = '<button class="btn btn-xs btn-warning">Finance Pending</button>';
}else if($rel['po_approval_status']=='1'){
	$status = '<button class="btn btn-xs btn-success" >Approved</button>';
}else if($rel['po_approval_status']=='0'){
	$status = '<button class="btn btn-xs btn-warning">Approved Pending</button>';	
}else if($rel['po_approval_status']=='2'){
	$status = '<button class="btn btn-xs btn-danger">Disapproved</button>';
}else{
	$status = '<button class="btn btn-xs btn-danger">Finance Disapproved</button>';
}


$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));

$back_link = $_SERVER['HTTP_REFERER'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>PO TRACKING</title>
	<?php include_once($include.'/include_css_file.php');?>
</head>
<body>
	<section id="container"> <!--class="sidebar-closed"-->
		<?php include_once($include.'/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once($include.'/left_menu.php');?>
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
								<?	
								
					/*$url = $_SERVER['HTTP_REFERER'];
					$infopage = basename($url);
					if($infopage=='crm_dashboard'){
						$back_link=ROOT.'crm_dashboard';
					}
					else{
						$back_link=ROOT.'inquiry_list';
					}*/
					?>
					
					<ul class="breadcrumb">
						<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
						<li><a href="<?=ROOT.PURCHASE_ROOT.'po_list'?>"><?=$form?> List</a></li>
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
				<header class="panel-heading">View <?=$form?></header>	
				<div class="panel-body">
					<div class="row">
						<div class="col-md-12">
							<header class="panel-heading breadcrumb text-center">
								<h3>PO Details</h3>
							</header>
							<table class="display table table-bordered table-striped">
								<tr>
									<td><strong>Purchase Order No : </strong><?=$rel['purchaseorder_no']?></td>
									<td><strong>Purchase Order Date : </strong><?=$purchaseorder_date?></td>
									<td>
										<strong>Purchase Order Type : </strong> <?=$type?>
									</td>
								</tr>
								
								<tr>
									<td><strong>Vendor Name : </strong> <?=$rel['l_name']?></td>
									<td><strong>Contact No : </strong> <?=$rel['cust_mobile']?></td>
									<td>
										<strong>Purchase Order Status : </strong> <?=$status?>
									</td>
								</tr>
								<tr>
									<td colspan="2"><strong>Address : </strong> <?=$rel['m_address']?></td>
									<td><strong>GST No : </strong> <?=$rel['gst_no']?></td>
								</tr>
							</table>
						</div>
						<div class="col-md-12" style="padding-top:10px;">
							<hr/>
							<header class="panel-heading breadcrumb text-center">
								<h3>Product Details</h3>
							</header>
							<div style="overflow:auto;">
							<?php
								$trn_quer1y="select ptr.*,unit.unit_name as base_unit,cunit.unit_name as conv_unit,pmst.product_name from tbl_purchaseordertrn as ptr 
								left join product_mst as pmst on pmst.product_id = ptr.product_id
								left join unit_mst as unit on unit.unitid = ptr.unit_id
								left join unit_mst as cunit on cunit.unitid = ptr.conv_unit_id
								where purchaseorder_id=".$rel['purchaseorder_id'];
								$trn_query_s=$dbcon->query($trn_quer1y);
								$cnt=brp_mysqli_num_rows($trn_query_s);
							?>

							<div class="tab-content">
				              <div class="tab-pane active" id="tab_1">
				                  <div class="panel-group" id="accordion1">
				                  	<?
			                      		$i=1;
			                      		while($row = brp_mysqli_fetch_array($trn_query_s)){
			                      			if($row['rate_unit']==$row['unit_id']){
			                      				$qty = $row['product_qty']." ".$row['base_unit'];
			                      			}else{
			                      				$qty = $row['product_conv_qty']." ".$row['conv_unit'];
			                      			}
			                      	?>
				                      <div class="panel panel-primary">
				                          <div class="panel-heading">
				                              <h4 class="panel-title">
				                                  <a href="#accordion1_<?=$i?>" data-parent="#accordion1" data-toggle="collapse" class="accordion-toggle">
				                                      <?=$i?>. <?=$row['product_name']?> -- (<?=$qty?>)
				                                  </a>
				                              </h4>
				                          </div>
				                          <div class="panel-collapse collapse  <?if($i==1){?>in<?}?>" id="accordion1_<?=$i?>">
				                              <div class="panel-body">
													<table class="table table-bordered">
														<thead>
															<tr>
																<th colspan="2" style="text-align: center;background-color: lavender;">PO Approve Detail</th>
															</tr>
															<tr>
																<th colspan="2" style="height: 40px;">
																	<?=get_po_approved_detail($dbcon,$rel['purchaseorder_id'])?>
																</th>
															</tr>

															<tr>
																<th colspan="2" style="text-align: center;background-color: lavender;">PO Finance Approve Detail</th>
															</tr>

															<tr>
																<th colspan="2" style="height: 40px;">
																	<?=get_pofinance_approved_detail($dbcon,$rel['purchaseorder_id'])?>
																</th>
															</tr>

															<tr>
																<th colspan="2" style="text-align: center;background-color: lavender;">PO Shortclose Detail</th>
															</tr>

															<tr>
																<th colspan="2" style="height: 40px;">
																<?=get_po_shorclose_detail($dbcon,$row['purchaseordertrn_id'])?>
																</th>
															</tr>

															<tr>
																<th colspan="2" style="height:40px"></th>
															</tr>

															<tr>
																<th width="50%">GRN QTY</th>
																<th width="50%"><?=get_poagainst_grn($dbcon,$row['purchaseordertrn_id'],'')?></th>
															</tr>
															<tr>
																<th colspan="2" style="text-align: center;background-color: lavender;">GRN Detail</th>
															</tr>
															<tr>
																<th colspan="2" style="height: 40px;">
																	<?=get_poagainst_grn_detail($dbcon,$row['purchaseordertrn_id'],'')?>
																</th>
															</tr>
														</thead>
														<tbody>
															
														</tbody>
													</table>
											  </div>
										  </div>
									   </div>
								  	<?$i++;}?>
								  </div>
							  </div>
							</div>	
						</div>
					</div>


					<div class="col-md-12 text-center">
						<a href="<?=$back_link?>" type="button" class="btn btn-danger">Cancel</a>	
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

<?php include_once($include.'/include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'/include_js_file.php');?>   

<script src="<?=ROOT.PURCHASE_ROOT?>js/app/pre.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
</script>
</body>
</html>