<?php 
	session_start();
	include('../include/urlfile.php');	
	// error_reporting(E_ALL);
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$_SESSION['contents']='';
	$form="Work Order";
	$mode="Print";
	$work_order_id = $dbcon->real_escape_string($_REQUEST['id']);
	$rp_id = $bom_id = $work_order_id;
	 $query="select bom.*,product.product_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name,product.product_icode from tbl_bom as bom
		left join product_mst as product on product.product_id=bom.bom_product
		left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
		where bom.bom_id=$bom_id";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	//exit;
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));
	
	$sel1=$dbcon->query("select sum(product_base_qty)as sqty from tbl_bomtrn where bom_id='$bom_id'");
	$row1=brp_mysqli_fetch_assoc($sel1);
	
	$totalqty=$row1['sqty'];
	//echo $row1['sqty'];


	$where = "";

	$heading_title = "";

	if(strpos($_SERVER['REQUEST_URI'], "jobcard_tracking_report")==true){
		$where = " where bom_trn.status != 2 and bom_trn.rp_id=".$rp_id;
		$form= "Jobcard ";
		$title = "Jobcard Tracking Report";

	}else{
		$title = "Work Order Tracking Report";
		$where = " where bom_trn.status != 2 and bom_trn.sp_id=".$work_order_id." group by bom_trn.sp_id";
	}

$so_qry="select smain.po_req_no as workorder_no,smain.sales_order_no,l.l_name,bom_trn.job_card_no from tbl_request_product as bom_trn 
										left join tbl_set_main_process as smain on smain.sp_id=bom_trn.sp_id
										left join  tbl_sales_ordertrn as sotrn on sotrn.sales_ordertrn_id=smain.sales_order_trn_id
										left join  tbl_sales_order as so on so.sales_order_id=sotrn.sales_order_id
										left join  tbl_ledger as l on l.l_id=so.cust_id" .$where;	
	$so_result=$dbcon->query($so_qry);
	$so_detail = brp_mysqli_fetch_assoc($so_result);
	$getspecialConfiguration=getspecialConfiguration($dbcon);

	$condition = "";
	$merge_rp_id = $rp_id;
	if(strpos($_SERVER['REQUEST_URI'], "jobcard_tracking_report")==true){
		$heading_title =  "Jobcard No : " .$so_detail['job_card_no'] . " (".$so_detail['workorder_no'].")";
		$merge_rp_id = get_child_rp_id($dbcon,$rp_id,$merge_rp_id);

		$condition = " where status != 2 and rp_id in(".$merge_rp_id.") order by bom_trn.rp_id";
		$cancel_url = ROOT.PRODUCTION_ROOT.'job_card_list';
	}else{
		$heading_title = "Workorder No : ". $so_detail['workorder_no'];
		$condition = " where status != 2 and sp_id=".$work_order_id." order by bom_trn.rp_id";
		$cancel_url = ROOT.PRODUCTION_ROOT.'work_order';
	}

	function get_child_rp_id($dbcon,$rp_id,$merge_rp_id){
		
		$q1 = " select rp_id from tbl_request_product where status != 2 and perent_id = " . $rp_id;
		$res1 = $dbcon->query($q1);
		$cnt = brp_mysqli_num_rows($res1);

		if($cnt > 0){
			while ($r1 = brp_mysqli_fetch_assoc($res1)) {
				$merge_rp_id = $merge_rp_id . "," . $r1['rp_id'];
			
				$merge_rp_id = get_child_rp_id($dbcon,$r1['rp_id'],$merge_rp_id);
				$merge_rp_id; 
			}
		}
		return $merge_rp_id;
	}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?=$title?></title>
		<?php include_once($include.'include_css_file.php');?>
		<style>
			body {
				color: #000000;
			}
			.con ul 
			{	
				padding-left:0px;
			}
			.con ul li 
			{
				margin-left:22px;
				list-style: disc !important;
			}
			/*td, th {
				padding: 0px 2px !important;
			}*/

			.td1
			{
				text-align:center;
				vertical-align:top;
				border-right:1px solid;
				border-left:1px solid;
				 border-bottom:1px solid;
			}
			.td2
			{
				text-align:center;
				border-bottom-color:#FFFFFF; 
				border-right:1px solid;
				vertical-align:top;
				 border-bottom:1px solid;
			}
			.td3
			{
				text-align:center;
				vertical-align:top;
				border-bottom-color:#FFFFFF; 
				border-right:1px solid;
				 border-bottom:1px solid;
			}
		</style>
	</head>
	<body>
		<section id="container" >
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
								  <h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=$cancel_url?>"><?=$form?> List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
								  <?=$form?> <?=$mode?>
								</header>	
								<div class="panel-body">
									<center>
										<div class="col-md-1"> </div>With Logo
										<br/>
										<label class="col-md-2 control-label"> Print</label>
										<div class="col-md-4 col-xs-11">
											<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
												<select class="form-control" name="print_status" id="print_status" <?if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
													<option value="">Select Print</option>
													<option value="1">ORIGINAL</option>
													<option value="2">DUPLICATE</option>
													<option value="3">TRIPLICATE</option>
													<option value="4">EXTRA</option>
												</select>
											</form>
										</div>
										<div class="col-md-1">
											<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
										</div>
										<div class="col-md-4">
											<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
											<a href="<?=$cancel_url?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
											<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
										</div>
									</center>	
									<div class="col-md-12"></div>
									<label class="col-md-3 control-label"></label>
									<div class="col-lg-4"></div>
									<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
									<?php ob_start(); ?>
									<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
									<table width="100%" class="maintable" border="0" style="" id="table_head">
										<tr style="border:none;">
											<td width="100%" style="border:none;"> 
												<!--<img src="<=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
												
												<h2 align="center"><?=$set_head['company_name']?></h2>
												<h5 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h5>
												<h5 align="center"><?=$set_head['address']?></h5>
												<h5 align="center"><?if($set_head['website']){?>Email: <?=$set_head['website']?><?}?> 
												<?if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?}?></h5>
												
											</td>
										</tr>
									</table>
									<table width="100%" border="0" style="" id="">
										<tr>
											<td width="90%" style="text-align:center"> 
												<strong style="font-size:16px">
													<?=$form?> 
												</strong>
											</td>
											<td width="10%" style="text-align:center"> 
												<strong style="font-size:12px">
													<b class="data_title">ORIGINAL</b>
												</strong>
											</td>
										</tr>
										
									</table>
									<table width="100%" class="maintable" style="font-size: 12px;" id="invoice_type" >
										<thead>
											<!--<tr>
												<th colspan="7" style="padding:0px !important;">
													<table  border="0" style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
														
															<tr>
																<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>BOM No </strong>
																</td>
																 
																<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong><?=$rel['bom_no']?></strong>
																</td>
																
																<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>Product</strong>
																</td>
																 
																<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong><?=$rel['product_name']?></strong>
																</td>
															</tr>
															<tr>
																<td style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;">
																	<strong>BOM Date</strong>
																</td>
																<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
																	: <?=date('d/m/Y',strtotime($rel['bom_date']))?>							
																</td>
																
																<td style="vertical-align:top;border:1px solid;border-right:none;white-space:nowrap;">
																	<strong>Product Qty</strong>
																</td>
																<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
																	: <?=$rel['product_base_qty']?>							
																</td>	
																
															</tr>
															
														
													</table>
												</th>
											</tr>-->


											<?phpif($getspecialConfiguration['hermattic_permission']=="1") { ?>
											<tr>
												<th colspan="8" style="padding:0px !important;">
													<table  border="0" style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
															<tr>
																<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>Sales Order No  </strong>
																</td>
																 
																<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong style="margin-left: 25px;"><?=$so_detail['sales_order_no']?></strong>
																</td>
																
																<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>Customer Name </strong>
																</td>
																 
																<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong style="margin-left: 25px;"><?=$so_detail['l_name']?></strong>
																</td>
															</tr>
															</table>
												</th>
											</tr>
										<?php} ?>	
										<tr height="30px">
											<td colspan="9" style="text-align:center;border:1px solid;" class="text-center"><strong style="font-size:14px"><?=$heading_title?></strong></td>
											
										</tr>
											<tr height="30px">					
												<th  width="5%" style="text-align:center;border:1px solid;">
													<strong>SR. NO.</strong>
												</th>
												<th width="30%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Item Name</strong></th>
												<th width="10%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Item Type</strong></th>
												<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Total Request Qty</strong></th>
												<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Process Qty</strong></th>
												<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Po Qty</strong></th>
												<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Reserve Qty</strong></th>
												
												<th width="40%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;"><strong>Status</strong></th>
												<th width="5%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;"><strong>Action</strong></th>
												
											</tr>
										</thead>
										<tbody style="border: 1px solid;">

										<?php
									 $qry="select bom_trn.*,pro.product_name,bunit.unit_name as base_unit_name,cunit.unit_name as convunit,pro.product_type, pro.product_icode from tbl_request_product as bom_trn 
										left join product_mst as pro on pro.product_id=bom_trn.rp_pid
										left join unit_mst as bunit on bunit.unitid=bom_trn.process_unit
										left join unit_mst as cunit on cunit.unitid=bom_trn.purchase_unit " . $condition;	

										$result1=$dbcon->query($qry);		
										$i=1;$total=0;$discount=0;
										$cnt1=mysqli_num_rows($result1);
										$cnt=1;
										// var_dump($cnt1);
										while($rel1=mysqli_fetch_assoc($result1))
										{
											$rr="";
											$spa="";
											//start
											
													
										$query="select mst.*,p.process_name from tbl_wororder_product_process as mst 
													left join process_mst as p on p.process_id=mst.process_id where mst.rp_id=".$rel1['rp_id']." order by process_priority";
													$result=$dbcon->query($query);
													$cnt=mysqli_num_rows($result);
													if($cnt>0){ 
													$spa.='<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
														<tr>
															<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
															<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
															<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
															<th style="border:0.5px #444 solid;text-align:center;" >Require Qty</th>
															<th style="border:0.5px #444 solid;text-align:center;" >Pending Qty</th>
															<th style="border:0.5px #444 solid;text-align:center;" >Inprocess Qty</th>
															<th style="border:0.5px #444 solid;text-align:center;" >Finish Qty</th>
														</tr>';
													 $av_qty=0; $tolp=0; $fin_qty =0;
														while($rel=mysqli_fetch_assoc($result)){ 
															if($rel['process_type']==1){
																$process_type="Inhouse";
															}else{
																$process_type="Outside";
															}
															$av_qty=start_qty_avalable($dbcon,$rel['process_id'],$rel['process_type'],$rel['product_id'],"",$rel['branch_id']);
															// var_dump($av_qty);
														// $queryp="select ap.*,IFNULL(sum(ap.p_qty),0) as ap_qty,IFNULL(sum(ap.pen_qty),0) as apen_qty,p.product_type,p.product_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty,pr.process_name,group_concat(ap.p_id ORDER BY ap.p_id ASC) as allo_id from tbl_allocate_process as ap 
														// 	left join product_mst as p on p.product_id=ap.p_product_id 
														// 	left join process_mst as pr on pr.process_id=ap.process_id 
														// 	left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
														// 	left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
														// 	where ap.p_ref_id = ".$rel1['rp_id']." and
														// 	ap.process_id=".$rel['process_id']." and ap.p_product_id=".$rel['product_id']." and ap.p_status IN(0,1) and pr_process_type=".$rel['process_type']." group by ap.p_product_id";

														 $queryp="select ap.pr_process_type, IFNULL(sum(ap.p_qty),0) as ap_qty,IFNULL(sum(ap.pen_qty),0) as apen_qty,p.product_type,p.product_name,pr.process_name,group_concat(ap.p_id ORDER BY ap.p_id ASC) as allo_id,p.product_icode from tbl_allocate_process as ap 
															left join product_mst as p on p.product_id=ap.p_product_id 
															left join process_mst as pr on pr.process_id=ap.process_id 
															where ap.p_ref_id = ".$rel1['rp_id']." and
															ap.process_id=".$rel['process_id']." and ap.p_product_id=".$rel['product_id']." and ap.process_priority=".$rel['process_priority']." and ap.p_status IN(0,1,3) and pr_process_type=".$rel['process_type']." group by ap.p_product_id";
													
														$relp=mysqli_fetch_assoc($dbcon->query($queryp));
														$apen_qty = $relp["apen_qty"];
														$total_req_qty = $relp["ap_qty"];
														
															if(empty($relp["ap_qty"])){
																$total_req_qty = 0;
															}
														$req_qty = $relp['ap_qty']; 	
														if(empty($relp["ap_qty"])){
																$req_qty = 0;
															}
															if(empty($relp["apen_qty"])){
																$apen_qty = 0;
															}
														$fin_qty = $req_qty - $apen_qty;
															$spa.='<tr>
																<td style="border:0.5px #444 solid;text-align:center;" >'.$rel["process_priority"].'</td>
																<td style="border:0.5px #444 solid;text-align:center;" >'.$process_type.'</td>
																<td style="border:0.5px #444 solid;text-align:center;" >'.$rel["process_name"].'</td>
																<td style="border:0.5px #444 solid;text-align:center;" >'.$total_req_qty.'</td>
																<td style="border:0.5px #444 solid;text-align:center;" >'.$apen_qty.'</td>
																<td style="border:0.5px #444 solid;text-align:center;" >'.$apen_qty.'</td>
																<td style="border:0.5px #444 solid;text-align:center;" >'.$fin_qty.'</td>
															</tr>';
															
														$tolp=$tolp+$av_qty;

													 } 
													$spa.='</table>';
													$result_1=$dbcon->query($query);
													$x = 1;
													$xcnt = brp_mysqli_num_rows($result_1);
													while($rel_1=mysqli_fetch_assoc($result_1)){ 
														if($x == 1){
															
															$spa .= '<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
															<tr>
															<th width="60%">stages</th>
															<th width="20%"> Qty</th>
															<th width="20%"> Date</th> 
															
															</tr>';
														}
														$queryp="select GROUP_CONCAT(ap.p_id) as p_id from tbl_allocate_process as ap 
															where ap.p_ref_id = ".$rel1['rp_id']." and
															ap.process_id=".$rel_1['process_id']." and ap.p_product_id=".$rel_1['product_id']." and ap.p_status IN(0,1,3) and ap.process_priority=".$rel_1['process_priority']."  group by ap.p_ref_id ";
															$rrr = $dbcon->query($queryp);
															while($rel_1p=mysqli_fetch_assoc($rrr)){
																
																$spa.= work_order_production_track($dbcon,$rel_1p['p_id'],$rel1['rp_id'],$rel_1['product_id'],$rel_1['process_id'],$rel_1['process_priority']);
															}
														if($x == $xcnt){
															$spa .= "</table>";
														}
														$x++;
													}

												}else{
													$tolp="-";
												}
												if($tolp==0){
													$tolp="-";
												}
											
											//end
											
											
									?>
											<tr>
												<td style="border:1px #444 solid;" ><?=$rel1['sr_no']?></td>
			
												<td style="border:1px #444 solid;" ><?=$rel1['product_name'] . ' -- ('.$rel1['product_icode'].')'?>
													<?
														if($rel1['job_card_no'] != ""){
															echo "</br><strong>Jobcard No : </strong>" . $rel1['job_card_no'];
														}
														if($rel1['product_remark'] != ""){
															echo "</br><strong>Remark : </strong>" . $rel1['product_remark'];
														}
													?>

												</td>
												<td style="border:1px #444 solid;" ><?=get_product_type_by_id($dbcon,$rel1['product_type'])?></td>
												<td style="border:1px #444 solid;" >
													<?=$rel1['rp_req_qty'] ?>
													<?=$rel1['base_unit_name']?>
													<?php if($rel1['process_unit'] != $rel1['purchase_unit']){

														$req_cov_qty = convert_stock($dbcon,$rel1['rp_req_qty'],$rel1['rp_pid'],"conv_unit");
														echo "</br>". $req_cov_qty . ' ' . $rel1['convunit'];
													}?>
												</td>
												<td style="border:1px #444 solid;" >
													<?=$rel1['in_process_qty'] ?>
													<?=$rel1['base_unit_name']?>
													</br>
													<?=$rel1['in_process_conv_qty'] ?>
													<?=$rel1['convunit']?>
												</td>
												<td style="border:1px #444 solid;" >
													<?=$rel1['rp_po_base_qty'] ?>
													<?=$rel1['base_unit_name']?>
													</br>
													<?=$rel1['rp_po_qty'];?>
													<?=$rel1['convunit'] ?>
													
												</td>
												<td style="border:1px #444 solid;" >
													<?=$rel1['reserve_base_stock'] ?>
													<?=$rel1['base_unit_name']?>
													</br>
													<?=$rel1['reserve_stock'];?>
													<?=$rel1['convunit'] ?>
													
												</td>
												
												
												<td style="border:1px #444 solid;" >
													<?=$spa?>
													<?php
														if(!empty($rel1['rp_po_qty'])){
															if($rel1['rp_po_qty']>0){

																echo $rr=work_order_po_track($dbcon,$rel1['rp_id']);
															}
														}
													?>
												</td>
												<td style="border:1px #444 solid;">
													<?php if($rel1['in_process_qty'] !="" && $rel1['in_process_qty'] > 0){ ?>
													<button class="btn btn-info" onclick="get_child_product_tracking(<?=$rel1['rp_id'];?>,<?=$rel1['rp_pid']?>)"><i class="fa fa-eye"></i></button>
												<?php }?>
												</td>
											</tr>
											<?
											
										
										//=work_order_bom_show_print($dbcon,$rel1['p_bom_id'],$rel1['product_base_qty'],$i,$call,$space);?>
										<?php  $i++;  }	?>
										
									</tbody>	
								</table>
							</div>
							<div id="print2"></div>
							<div id="print3"></div>
						</div>
					</div>	
				</section>
			</div>
		</div>
	</section>
</section>
     <?php include_once($include.'footer.php');?>
    </section>
		<?php include_once($include.'include_js_file.php');?>   
		<?php include_once($include1.'production_pending_report_modal.php');?> 
		<?php include_once($include1.'workorder_tracking_date.php');?> 
   		<script>
$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });
function paymentmode(id)
{
	if(id=="2")
	{	
		$('#cheque_dtl').val('');
		$('#cheque_data').show();
	}
	else
		$('#cheque_data').hide();
}

</script>
<script type="text/javascript"> 
function print_receipt()
{
	var originalContents = document.body.innerHTML;
	//var duplicate = $("#invoiceprint").clone().prepend("<hr style='border-color:#000; border-style:dashed; margin:10px 0' />").appendTo("#invoiceprint");
	 var printContents = document.getElementById('receipt_print').innerHTML;     
     document.body.innerHTML = printContents;
     window.print();
     document.body.innerHTML = originalContents;
}

function PrintMe(DivID) {

if($('#print_status').val()=='')
{
alert('Select PrintType');
}
else
{


if($('#print_status').val()<=3)
{	
for(var i=1;i<$('#print_status').val();i++)
{	
	if($("#invoice").val()==2)
	{
		$("#print"+i+" .data_title").html('Performance');
		$("#type").html("Performance Invoice");
	}
	if($("#invoice").val()==1)
	{
		$("#print"+i+" .data_title").html('ORIGINAL');
		$("#type").html($("#typename").val());
	}
	if(i<$('#print_status').val())
	{
		$("#print"+i).after('<div class="page"></div>');
	}
	$("#print"+(i+1)).html($("#print1").clone());
	if((i+1)==2)
	{
		$("#print"+(i+1)+" .data_title").html('DUPLICATE');
	}
	if((i+1)==3)
	{
		$("#print"+(i+1)+" .data_title").html('TRIPLICATE');
	}
	
}
}
else
{
	$("#print1 .data_title").html('EXTRA');
}
  //var duplicate = $("#receipt_data").clone().appendTo("#receipt_duplicate");
  var disp_setting="toolbar=yes,location=no,";
  disp_setting+="directories=yes,menubar=yes,";
  disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25";
  var content_vlue = document.getElementById(DivID).innerHTML;
  var docprint=window.open("","",disp_setting);
  docprint.document.open();
  docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
  docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
  docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
  docprint.document.write('<head><title><?phpecho TITLE;?></title>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
  docprint.document.write('<style type="text/css">');
	if ($('input[name=logo]:Checked').val() == "1") {
	   
		$('#table_head').show();
		$('#table_foot').show();
		docprint.document.write(' @media print{ @page { size:A4; margin: 0.2in <?=$set_head['letter_head_right_margin']?>in 0.2in <?=$set_head['letter_head_left_margin']?>in; } }   ');
		
	}
	else{
		docprint.document.write(' @media print{ @page { size:A4; margin: <?=$set_head['letter_head_top_margin']?>in <?=$set_head['letter_head_right_margin']?>in <?=$set_head['letter_head_bottom_margin']?>in <?=$set_head['letter_head_left_margin']?>in; } }  #table_head, #table_foot { display:none }');
		//$('#invoice_type').css('margin-top','1.7in');	
		
	}
 
  docprint.document.write('body { font-family:Tahoma;color:#000;');
  docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
  docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } tr { page-break-inside: avoid } .maintable tbody tr { border-bottom:0.5px #ccc solid; }');
  docprint.document.write(' .maintable table { page-break-inside:auto } .maintable tr{ page-break-inside:avoid; page-break-after:auto } .maintable thead { display:table-header-group }  .maintable tfoot tr{ /*display:table-footer-group;*/ page-break-inside:avoid; page-break-before:always; } footer-table{ page-break-inside:avoid; page-break-before:always;  } #table_foot{position:fixed;bottom:0}</style>');
  docprint.document.write('</head><body onLoad="self.print()">');
  docprint.document.write(content_vlue);
  docprint.document.write('</body></html>');
  docprint.document.close();
  docprint.focus();
	$('#table_head').show();
	//$('#invoice_type').css('margin-top','0px');

  }
  location.reload();
}

function get_child_product_tracking(rp_id,product_id){
$("#tracking_reports_modal").modal('show');
	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/work_order/',
		data: { mode:"track_product_detail", rp_id : rp_id, product_id:product_id},
		success: function(resp){
			//console.log(resp);
			//alert(resp);
			var arr = jQuery.parseJSON(resp);	
			
				$("#product_name").empty().html(arr.product_name);
				$("#report_details").empty().html(arr.data);

				$("#tracking_reports_modal").modal('show');
				
			Unloading();
		}
	});

}

function show_wo_po_tracking_date(rp_id,mode_type,purchaseordertrn_id="",product_id="",qc=""){

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/work_order/',
		data: {
			mode:"get_wo_po_tracking_date", 
			rp_id : rp_id, 
			product_id : product_id,
			mode_type : mode_type,
			purchaseordertrn_id:purchaseordertrn_id,
			qc : qc
		},
		success: function(resp){
			
			$("#workorder_date_modal").modal('show');
			$("#workorder_date").empty().html(resp);
				
			Unloading();
		}
	});

}

function show_wo_production_tracking_date(rp_id,mode_type,product_id="",process_id="",priority="",p_id="",qc="",grn_trn_id){

	Loading();
	$.ajax({
		type: "POST",
		url: root_domain+production_domain+'app/work_order/',
		data: {
			mode:"get_wo_production_tracking_date", 
			rp_id : rp_id, 
			product_id : product_id,
			process_id : process_id,
			priority : priority,
			mode_type : mode_type,
			p_id : p_id,
			qc : qc,
			grn_trn_id:grn_trn_id
		},
		success: function(resp){
			
			$("#workorder_date_modal").modal('show');
			$("#workorder_date").empty().html(resp);
				
			Unloading();
		}
	});

}
</script>
	

  </body>
</html>
