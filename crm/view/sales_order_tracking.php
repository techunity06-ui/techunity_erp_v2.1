<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Sales Order";
$sales_order_id=$dbcon->real_escape_string($_REQUEST['id']);
$query="select qt.*,country_name,state_name,city_name,led.company_name,led.cust_mobile,led.m_address,led.gst_no,led.l_id,led.credit_limit,led.credit_days from tbl_sales_order as qt
left join tbl_ledger as led on led.l_id=qt.cust_id
left join country_mst as country on country.countryid=led.countryid
left join state_mst as state on state.stateid=led.stateid
left join city_mst as city on city.cityid=led.cityid
where qt.sales_order_id=$sales_order_id";
$rel=mysqli_fetch_assoc($dbcon->query($query));
$sales_order_date=date('d-m-Y',strtotime($rel['sales_order_date']));
$delivery_date='';
if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00"){
	$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
}

$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));
$back_link = $_SERVER['HTTP_REFERER'];

$class = $status = $class1 = $status1 = '';
if($rel['approve_status']==0){
	$class = 'warning';
	$status = 'Sales Order Approve Pending';
}else if($rel['approve_status']==3){
	$class = 'success';
	$status = 'Sales Order Approved';
}else{
	$class = 'danger';
	$status = 'Sales Order Disapproved';
}
if($rel['order_accept_status']==0){
	$class1 = 'warning';
	$status1 = 'Order Accepte Pending';
}else if($rel['order_accept_status']==1){
	$class1 = 'success';
	$status1 = 'Order Accepted';
}else{
	$class1 = 'danger';
	$status1 = 'Disapproved Order Accepte';
}

$companyConfiguration=getCompanyConfiguration($dbcon);
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
									<li><a href="<?=ROOT.CRM_ROOT.'sales_order_list'?>"><?=$form?> List</a></li>
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
											<h3>Sales Order Details</h3>
										</header>
										<table class="display table table-bordered">
											<tr>
												<td width="35%"><strong>Sales Order No: </strong><?=$rel['sales_order_no']?></td>
												<td width="35%"><strong>Sales Order Date: </strong><?=date("d-M-Y",strtotime($rel['sales_order_date']))?></td>
												<td width="30%"><strong>Sales Order Amount: </strong><?=$rel['g_total']?></td>
											</tr>
											<tr>
												<td><strong>Company: </strong><?=$rel['company_name']?><input type="hidden" id="cust_id" name="cust_id" value="<?=$rel['cust_id']?>"></td>
												<td><strong>Contact no: </strong><?=$rel['cust_mobile'].' '.$rel['c_con_lname']?></td>
												<td><strong>Entry Date & Time: </strong><?=date("d-M-Y h:i:s", strtotime($rel['cdate']))?></td>
											</tr>
											<tr>
												<td colspan="2"><strong>Address</strong><?=$rel['m_address']?></td>
												<td><strong>GST No.:</strong><?=$rel['gst_no']?></td>
											</tr>
											<tr>
												<td><strong>City: </strong><?=$rel['city_name']?></td>
												<td><strong>State: </strong><?=$rel['state_name']?></td>
												<td><strong>Country: </strong><?=$rel['country_name']?></td>
											</tr>
											<tr>
												<td colspan="3"><button type="button" class="btn btn-<?=$class?> btn-lg"><?=$status?></button>&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-<?=$class1?> btn-lg"><?=$status1?></button>&nbsp;&nbsp;&nbsp;<button type="button" class="btn btn-<?=($rel['invoice_status']==1) ? 'success' : 'warning'?> btn-lg"><?=($rel['invoice_status']==1) ? 'Invoice Done' : 'Invoice Pending'?></button></td>
											</tr>
											<?=get_so_approved_log($dbcon,$rel['sales_order_id']);?>
											<?=get_oa_approved_log($dbcon,$rel['sales_order_id']);?>
										</table>
									</div>
									<div class="col-md-12" style="padding-top:10px;">
										<header class="panel-heading breadcrumb text-center">
											<h3>Product Details</h3>
										</header>
										<div class="tab-content">
											<div class="tab-pane active" id="tab_1">
												<div class="panel-group" id="accordion1">
													<?php $qry="SELECT mst.*,sales_ordertrn_id,if(mst.project_wise=0,(SELECT product_name FROM product_mst as pro WHERE pro.product_id=mst.product_id) ,(SELECT project_name FROM tbl_project_assign as proj WHERE proj.project_assign_id=mst.product_id)) as product_name,cat.unit_name,mst.description,product_qty,product_rate,product_amount,product.product_icode FROM tbl_sales_ordertrn as mst 
													left join unit_mst as cat on cat.unitid=mst.unit_id 
													left join product_mst as product on product.product_id=mst.product_id  
													WHERE sales_ordertrn_status=0 and sales_order_id=".$sales_order_id;
													$results=$dbcon->query($qry);
													$i=1;
													while($rels=mysqli_fetch_assoc($results)){ 
														$cls='';
														if($rels['invoice_status']==1){
															$cls = 'panel-success';
														}else{
															$cls = 'panel-primary';
														}

														?>
														<div class="panel <?=$cls?>">
															<div class="panel-heading">
																<h4 class="panel-title">
																	<a href="#accordion1_<?=$i?>" data-parent="#accordion1" data-toggle="collapse" class="accordion-toggle">
																		<strong><?=$i?>] <?=$rels['product_name']?> -- <?=$rels['product_qty']?> <?=$rels['unit_name']?></strong>
																	</a>
																</h4>
															</div>
															<div class="panel-collapse collapse  in" id="accordion1_<?=$i?>">
																<?php if($rel['order_accept_status']==1){ ?>
																	<div class="panel-body">
																		<table class="table table-bordered" style="font-weight: bold;">
																			<tbody>
																				<?php if($companyConfiguration['design_department']==1 && $rels['bom_id']!=0){ ?>
																					<tr>
																						<td colspan="2" style="background: lavender;">Design Department</td>
																					</tr>
																					<?php if($rels['bom_status']==1){ ?>
																						<tr>
																							<td width="50%">BOM Assign</td>
																							<td width="50%">Yes</td>
																						</tr>
																						<tr>
																							<td width="50%">BOM No.</td>
																							<td width="50%"><?=get_bom_no($dbcon,$rels['bom_id'])?></td>
																						</tr>
																					<?php } ?>
																				<?php } ?>
																				<?php if($rels['production_branch_id']!=0){ ?>
																					<tr>
																						<td colspan="2" style="background: lavender;">Sales order wise Branch Planning</td>
																					</tr>
																					<tr>
																						<td width="50%">Allocate Branch</td>
																						<td width="50%"><?=get_branch_name_by_id($dbcon,$rels['production_branch_id'])?></td>
																					</tr>
																				<?php } ?>
																				<?=get_reserve_stock_qty($dbcon,$rels['sales_ordertrn_id']);?>
																				<?=get_reserve_stock_deallocate_qty($dbcon,$rels['sales_ordertrn_id']);?>
																				<?=get_wo_detail_by_so($dbcon,$rels['sales_ordertrn_id'],$rels['unit_id']);?>

																				<?=get_invoice_no_by_so($dbcon,$rels['sales_ordertrn_id']);?>
																				<?php if($rels['short_close_status']==1){ ?>
																					<tr>
																						<td colspan="2" style="background: lavender;">Shortclose Status</td>
																					</tr>
																					<tr>
																						<td>Shortclose SO</td>
																						<td>Yes</td>
																					</tr>
																					<tr>
																						<td>Shortclose Qty</td>
																						<td><?=$rels['short_close_product_qty']?> <?=getunitname($dbcon,$rels['short_close_unit_id']);?></td>
																					</tr>
																				<?php } ?>
																			</tbody>
																		</table>
																	</div>
																<?php } ?>
															</div>
														</div>
														<?php $i++;
													} ?>
												</div>
											</div>
										</div>
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

<script src="<?=ROOT.CRM_ROOT?>js/app/sales_order.js?<?=time()?>"></script>
<script>
	$(".select2").select2({
		width: '100%'
	});
</script>
</body>
</html>