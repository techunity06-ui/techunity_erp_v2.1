<?php 
session_start();
include('../include/urlfile.php');
$incPath = $path.'include/';
$form="Indent";
$rp_id=$dbcon->real_escape_string($_REQUEST['id']);
$query="select req.*,wo.po_req_no from tbl_request_product as req 
left join tbl_set_main_process as wo on wo.sp_id = req.sp_id
where req.rp_id=".$rp_id;
$rel=brp_mysqli_fetch_array($dbcon->query($query));
$inquiry_date=date('d-m-Y',strtotime($rel['indent_date']));
$closing_date='';
if($rel['indent_date']!="1970-01-01" && $rel['indent_date']!="0000-00-00"){
	$closing_date=date('d-m-Y',strtotime($rel['indent_date']));
}

if($rel['rp_req_type']=='direct'){
	$indent_type = "Direct";
}else{
	$indent_type = "Work Order";
}

if($rel['indent_status']==1){
	$indent_status = 'Pending';
}else{
	$indent_status = 'Done';
}

$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));

$back_link = $_SERVER['HTTP_REFERER'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>INDENT TRACKING</title>
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
						<li><a href="<?=ROOT.PURCHASE_ROOT.'pre_list'?>"><?=$form?> List</a></li>
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
								<h3>Indent Details</h3>
							</header>
							<table class="display table table-bordered table-striped">
								<tr>
									<td><strong>Indent No: </strong><?=$rel['indent_no']?></td>
									<td><strong>Indent Date: </strong><?=date("d-M-Y",strtotime($rel['indent_date']))?></td>
									<td>
										<strong>Work Order No : </strong> <?=$rel['po_req_no']?>
									</td>
								</tr>
								
								<tr>
									<td>
										<strong>Indent Status : </strong> <?=$indent_status?>
									</td>
									<td><strong>Indent Type : </strong> <?=$indent_type?></td>
									<td></td>
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
								$trn_quer1y="select req.*,unit.unit_name,pmst.product_name from tbl_request_product as req 
								left join product_mst as pmst on pmst.product_id = req.rp_pid
								left join unit_mst as unit on unit.unitid = req.purchase_unit 
								where rp_id=".$rel['rp_id'];
								$trn_query_s=$dbcon->query($trn_quer1y);
								$cnt=brp_mysqli_num_rows($trn_query_s);
								$row = brp_mysqli_fetch_array($trn_query_s);
							?>

							<div class="tab-content">
				              <div class="tab-pane active" id="tab_1">
				                  <div class="panel-group" id="accordion1">
				                      <div class="panel panel-primary">
				                          <div class="panel-heading">
				                              <h4 class="panel-title">
				                                  <a href="#accordion1_1" data-parent="#accordion1" data-toggle="collapse" class="accordion-toggle">
				                                      1. <?=$row['product_name']?> -- (<?=$row['rp_po_qty']." ".$row['unit_name']?>)
				                                  </a>
				                              </h4>
				                          </div>
				                          <div class="panel-collapse collapse  in" id="accordion1_1">
				                              <div class="panel-body">
													<table class="table table-bordered">
														<thead>
															<tr>
																<th width="50%">Indent Approved Qty</th>
																<th width="50%"><?=get_indent_approve_qty($dbcon,$rp_id)?></th>
															</tr>

															<tr>
																<th colspan="2" style="text-align: center;background-color: lavender;">Indent Approval Detail</th>
															</tr>
															<tr>
																<th colspan="2" style="height: 40px;">
																	<?=get_approved_detail($dbcon,$rp_id)?>
																</th>
															</tr>

															
														</thead>
														<tbody>
															
														</tbody>
													</table>
											  </div>
										  </div>
									   </div>
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