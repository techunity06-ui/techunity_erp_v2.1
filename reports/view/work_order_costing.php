<?php 
	session_start();
	include('../include/urlfile.php');	
	// error_reporting(E_ALL);
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$_SESSION['contents']='';
	$form="Work Order View";
	$mode="Print";
	$work_order_id = $dbcon->real_escape_string($_REQUEST['id']);
	 $query="select bom.*,product.product_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name from tbl_bom as bom
		left join product_mst as product on product.product_id=bom.bom_product
		left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
		where bom.bom_id=$work_order_id";
	$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
	//exit;
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=brp_mysqli_fetch_assoc($dbcon->query($set));
	
	$sel1=$dbcon->query("select sum(product_base_qty)as sqty from tbl_bomtrn where bom_id='$work_order_id'");
	$row1=brp_mysqli_fetch_assoc($sel1);
	
	$totalqty=$row1['sqty'];
	//echo $row1['sqty'];

 $so_qry="select smain.sales_order_no,smain.po_req_no,smain.po_req_date,l.l_name,smain.bom_costing_id,smain.product_id,smain.bom_id,bv.version_name,smain.bom_costing_id from tbl_request_product as bom_trn 
										left join tbl_set_main_process as smain on smain.sp_id=bom_trn.sp_id
										left join  tbl_sales_ordertrn as sotrn on sotrn.sales_ordertrn_id=smain.sales_order_trn_id
										left join  tbl_sales_order as so on so.sales_order_id=sotrn.sales_order_id
										left join  tbl_ledger as l on l.l_id=so.cust_id
										left join  pro_ms_bom_version as bv on bv.bom_version_id=smain.bom_version_id
										where bom_trn.status=0 and bom_trn.sp_id=".$work_order_id." group by bom_trn.sp_id";	
	$so_result=$dbcon->query($so_qry);

	$so_detail = brp_mysqli_fetch_assoc($so_result);
$getspecialConfiguration=getspecialConfiguration($dbcon);

$total_qty = 0;
$total_net_costing = 0;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>WorkOrder Costing Report</title>
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
									  <li><?=$form?> List</li>
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
										<input type="hidden" name="sp_id" id="sp_id" value="<?=$work_order_id?>">
										<div class="col-md-1">
											<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
										</div>
										<div class="col-md-4">
											<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
											<a href="<?=ROOT.PRODUCTION_ROOT.'bom_costing_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
											<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
										</div>
									</center>	
									<div class="col-md-12"></div>
									<label class="col-md-3 control-label"></label>
									<div class="col-lg-4"></div>
									<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
									<?php ob_start(); ?>
									<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
									<table width="100%" class="maintable" border="0" id="table_head">
										<tr style="border:none;">
											<td width="100%" style="border:none;"> 
												<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
												
												<h2 align="center"><?=$set_head['company_name']?></h2>
												<h5 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h5>
												<h5 align="center"><?=$set_head['address']?></h5>
												<h5 align="center"><?if($set_head['website']){?>Email: <?=$set_head['website']?><?}?> 
												<?if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?}?></h5>
												
											</td>
										</tr>
									</table>
									<table width="100%" border="0" id="">
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

									<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;" id="invoice_type" >
										<thead>
											
											<tr>
												<th colspan="5" style="padding:0px !important;">
													<table class="maintable table  table-responsive"  border="0" style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
															<tr>
																<td width="10%"><strong>Workorder No  </strong>
																</td>
																 
																<td width="14%" style="border-right:0px solid;">: <strong style="margin-left: 10px;"><?=$so_detail['po_req_no']?></strong>
																</td>
																
																<td width="10%" style="border-left: 1px solid #ddd;border-bottom: 1px solid #ddd;"><strong>Workorder Date </strong>
																</td>
																 
																<td width="14%" style="border-right:0px solid;border-bottom: 1px solid #ddd;">: <strong style="margin-left: 10px;"><?=date('d/m/Y',strtotime($so_detail['po_req_date']))?></strong>
																</td>
																<td width="10%" style="border-left: 1px solid #ddd;border-bottom: 1px solid #ddd;"><strong>Salesorder No  </strong>
																</td>
																 
																<td width="14%" style="border-right:0px solid;border-bottom: 1px solid #ddd;">: <strong style="margin-left: 10px;"><?=$so_detail['sales_order_no']?></strong>
																</td>
																
																<td width="10%" style="border-left: 1px solid #ddd;border-bottom: 1px solid #ddd;"><strong>Customer Name </strong>
																</td>
																 
																<td width="14%" style="border-right:0px solid;border-bottom: 1px solid #ddd;">: <strong style="margin-left: 10px;"><?=$so_detail['l_name'];?></strong>
																</td>
															</tr>
															<tr>
																<td width="10%" style="border-left: 1px solid #ddd;"><strong>BOM Version
																</td>
																 
																<td width="14%" style="border-right:0px solid;">: <strong style="margin-left: 10px;"><?=$so_detail['version_name'];?></strong>
															</tr>
															</table>
												</th>
											</tr>
										
											<tr>
												<th class="text-center" style="width:3%;"><strong>SR.NO.</strong></th>
												<th class="text-center" style="width:20%;"><strong>PRODUCT NAME</strong></th>
												<th class="text-center" style="width:10%;"><strong>QTY</strong></th>
												<th class="text-center" style="width:57%;"><strong>PROCESS</strong></th>
												<th class="text-center" style="width:10%;"><strong>RAW MATERIAL RATE</strong></th>
											</tr>
										</thead>
										<tbody style="border: 1px solid;">

										<?php
									 $qry="select bom_trn.*,pro.product_name,bunit.unit_name as base_unit_name,cunit.unit_name as convunit,pro.product_type from tbl_request_product as bom_trn 
										left join product_mst as pro on pro.product_id=bom_trn.rp_pid
										left join unit_mst as bunit on bunit.unitid=bom_trn.process_unit
										left join unit_mst as cunit on cunit.unitid=bom_trn.purchase_unit
										where bom_trn.status=0 and sp_id=".$work_order_id.' order by sr_no';	

										$result1=$dbcon->query($qry);		
										$i=1;$total=0;$discount=0;
										$cnt1=mysqli_num_rows($result1);
										$cnt=1;
										$total_product_costing = 0;
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
													$spa.='<table class="maintable table table-bordered table-responsive"  cellpadding="0" cellspacing="0" width="100%" >
														<tr>
															<th class="text-center" style="width:3%;"><strong>PRIORITY</strong></th>
															<th class="text-center" style="width:20%;"><strong>PROCESS NAME</strong></th>
															<th class="text-center" style="width:20%;"><strong>PROCESS TYPE</strong></th>
															<th class="text-center" style="width:12%;"><strong>RATE</strong></th>
															<th class="text-center" style="width:15%;"><strong>TOTAL RATE</strong></th>
															<th class="text-center" style="width:30%;"><strong>Extra Material</strong></th>
														</tr>	';
													 $av_qty=0; $tolp=0; $fin_qty =0;

														while($rel=mysqli_fetch_assoc($result)){ 
															if($rel['process_type']==1){
																$process_type="Inhouse";
															}else{
																$process_type="Outside";
															}
															$av_qty=start_qty_avalable($dbcon,$rel['process_id'],$rel['process_type'],$rel['product_id'],"",$rel['branch_id']);

														/* $queryp="select ap.p_id from tbl_allocate_process as ap 
															where ap.p_ref_id = ".$rel1['rp_id']." and
															ap.process_id=".$rel['process_id']." and ap.p_product_id=".$rel['product_id']." and ap.p_status IN(0,1,3) and pr_process_type=".$rel['process_type'];
													
															$relp=mysqli_fetch_assoc($dbcon->query($queryp));

															$queryp_1="select group_concat(stock_id) as stock_id,group_concat(ref_id) as grn_sub_trn_id from tbl_reserve_stock as res
															where res.p_id = ".$relp['p_id']." and res.stock_status = 0 and res.stock_flage = 1 group by product_id,request_id";
															$relp_1=mysqli_fetch_assoc($dbcon->query($queryp_1));

															 $queryp_2="select IFNULL(AVG(stock.base_rate),0) as base_rate,IFNULL(AVG(stock.conv_rate),0) as conv_rate,base_unit,convert_unit from tbl_stock_trn as stock
															where stock.stock_id in(".$relp_1['stock_id'].") and stock.stock_status = 0 and stock.stock_flage = 1";
													$relp_2=mysqli_fetch_assoc($dbcon->query($queryp_2));
														*/

													/* Extra Material Rate show */

														/*$q_111 = "select * from tbl_workorder_direct_material_issue where status = 1 and rp_id = " . $rel1['rp_id'] . " and process_id = " . $rep['process_id'];*/

														$q_111 = "select trn.*,pro.product_name from tbl_workorder_direct_material_issue_trn as trn
																	left join tbl_workorder_direct_material_issue as mst on mst.material_issue_id = trn.material_issue_id
																left join product_mst as pro on pro.product_id = trn.product_id
																where mst.status = 1 and trn.status = 0 and mst.rp_id = " . $rel1['rp_id'] . " and mst.process_id = " . $rel['process_id'];
														$rel_111=$dbcon->query($q_111);
														
														$extra_material = "";
														if(brp_mysqli_num_rows($rel_111) > 0){

															$cnt_ext = 1;
															$extra_material .= '<table class="maintable table table-bordered table-responsive"  cellpadding="0" cellspacing="0" width="100%" >
																<tr>
																	<th class="text-center" style="width:3%;"><strong>SRNo.</strong></th>
																	<th class="text-center" style="width:20%;"><strong>PRODUCT Name</strong></th>
																	<th class="text-center" style="width:20%;"><strong> Qty </strong></th>
																	<th class="text-center" style="width:15%;"><strong>RATE</strong></th>
																	<th class="text-center" style="width:25%;"><strong>TOTAL RATE</strong></th>
																</tr>';
															while($row_111 = brp_mysqli_fetch_assoc($rel_111)){

															$queryp_2="select IFNULL(AVG(stock.base_rate),0) as base_rate,IFNULL(AVG(stock.conv_rate),0) as conv_rate,base_unit,convert_unit from tbl_stock_trn as stock
															where ref_name ='workorder_direct_material_issue' and stock.ref_id in(".$row_111['material_issue_trn_id'].") and stock.stock_status = 0 and stock.stock_flage = 2 and product_id = " . $row_111['product_id'];
															$relp_2=mysqli_fetch_assoc($dbcon->query($queryp_2));
															
															$extra_material .= "<tr>
																<td> ".$cnt_ext." </td>
																<td> ".$row_111['product_name']." </td>
																<td> ".$row_111['base_qty']." </td>
																<td> ".$relp_2['base_rate']." </td>
																<td> ".$relp_2['base_rate'] * $row_111['base_qty']." </td>
															</tr>";
															$cnt_ext++;
															$total_product_costing = $total_product_costing + ($relp_2['base_rate'] * $row_111['base_qty']);
															}

															$extra_material.= "</table>";
														}

													$queryp = "select IFNULL(AVG(grn.total_process_rate),0) as base_rate,IFNULL(AVG(grn.total_process_conv_rate),0) as conv_rate,grn.product_base_unit,grn.product_conv_unit,grn.product_process_rate,grn.product_process_unit from tbl_grn_sub_trn as grn left join tbl_grn_trn as trn on trn.grn_trn_id = grn.grn_trn_id where grn.rp_id in(".$rel1['rp_id'].") and trn.process_id = ".$rel['process_id']." group by grn.product_id";
													$relp=mysqli_fetch_assoc($dbcon->query($queryp));
															$spa.='<tr>
																<td style="text-align:center;" >'.$rel["process_priority"].'</td>
																<td style="text-align:center;" >'.$rel["process_name"].'</td>
																<td style="text-align:center;" >'.$process_type.'</td>
																<td style="text-align:center;" >'.$relp['product_process_rate'].'</td>
																<td style="text-align:center;" >'.($relp['base_rate']).'</td>
																<td style="text-align:center;">'.$extra_material.'</td>
															</tr>';
															
														$tolp=$tolp+$av_qty;
														$total_product_costing = $total_product_costing + $relp['base_rate'];
													 }
													 
													/* REPROCESS  */ 

													 $q_11 = "select * from tbl_allocate_re_process where p_ref_id = " . $rel1['rp_id'];
													$rel_56=$dbcon->query($q_11);
													$re_cnt = brp_mysqli_num_rows($rel_56);

													if($re_cnt > 0){
														$x = 1;
														$spa.="<tr class='text-center bg-danger'>
														<td colspan='5'>REPROCESS</td>
														</tr>";
														while($row_56 = brp_mysqli_fetch_assoc($rel_56)){
															
															if($row_56['pr_process_type']==1){
																$process_type="Inhouse";
															}else{
																$process_type="Outside";
															}
															$spa.='<tr>
																<td style="text-align:center;" >'.$x.'</td>
																<td style="text-align:center;" >'.get_process_name($dbcon,$row_56['process_id']).'</td>
																<td style="text-align:center;" >'.$process_type.'</td>
																<td style="text-align:center;" >'.$row_56['product_process_rate'].'</td>
																<td style="text-align:center;" >'.$row_56['total_process_rate'].'</td>
															</tr>';
															$total_product_costing = $total_product_costing + $row_56['total_process_rate'];
															$x++;
														}  
													}
													  

													$spa.='</table>';
													$result_1=$dbcon->query($query);
													$x = 1;
													$xcnt = brp_mysqli_num_rows($result_1);
													

												}else{
													$tolp="-";
												}
												if($tolp==0){
													$tolp="-";
												}
									?>
											<tr>
												<td ><?php echo (empty($rel1['sr_no'])) ? '0' : $rel1['sr_no']; ?></td>
												<td ><?=$rel1['product_name'];?></br><?=get_product_type_by_id($dbcon,$rel1['product_type']);?></td>
												<td >
													<?=$rel1['rp_req_qty'] ?>
													<?=$rel1['base_unit_name']?>

													<?php $total_qty =  $total_qty + $rel1['rp_req_qty']; ?>
												</td>
												<td >
													<?=$spa?>
												</td>
												<?php
												$total_rate = 0;
													if($cnt>0){ 
														 $queryp_1="select (base_stock) as base_stock,(stock_id) as stock_id,(ref_id) as grn_sub_trn_id from tbl_reserve_stock as res
															where res.request_id = ".$rel1['rp_id']." and res.stock_status in (0,1) and ref_name = 'wo_allocate' and res.stock_flage = 1 ";
													// echo "</br></br>";
													$res_queryp_1 = $dbcon->query($queryp_1);
													
													 while($relp_1=mysqli_fetch_assoc($res_queryp_1)){

													 $queryp_2="select IFNULL(AVG(stock.base_rate),0) as base_rate,IFNULL(AVG(stock.conv_rate),0) as conv_rate,base_unit,convert_unit from tbl_stock_trn as stock
															where stock.stock_id in(".$relp_1['stock_id'].") and stock.stock_status in (0,1) and stock.stock_flage = 1";
														$relp_2=mysqli_fetch_assoc($dbcon->query($queryp_2));
														
														$total_rate = $total_rate + ($relp_1['base_stock'] * 	$relp_2['base_rate']);
													}

														// echo "</br></br>";
													}else{
													  $queryp_1="select IFNULL(base_stock,0) as base_stock,(stock_id) as stock_id,(ref_id) as grn_sub_trn_id from tbl_reserve_stock as res
															where res.request_id = ".$rel1['rp_id']." and res.stock_status in (0,1) and res.stock_flage = 1";
													// echo "</br></br>";
													$res_queryp_1 = $dbcon->query($queryp_1);
													
													 while($relp_1=mysqli_fetch_assoc($res_queryp_1)){
													 	$queryp_2="select IFNULL(stock.base_rate,0) as base_rate,IFNULL(stock.conv_rate,0) as conv_rate,base_unit,convert_unit from tbl_stock_trn as stock
															where stock.stock_id in(".$relp_1['stock_id'].") and stock.stock_status in (0,1) and stock.stock_flage = 1";
														$relp_2=mysqli_fetch_assoc($dbcon->query($queryp_2));
														 // echo "</br></br>";
														
														$total_rate = $total_rate + ($relp_1['base_stock'] * 	$relp_2['base_rate']);

													 }
													}
												?>
												<td>
													<?php 	
															$total_product_costing = $total_product_costing + $total_rate;
																echo $total_rate;
													?>
												</td>
												</tr>
											<?
											//=work_order_bom_show_print($dbcon,$rel1['p_bom_id'],$rel1['product_base_qty'],$i,$call,$space);?>
										<?php  $i++;  }	?>

										<tr style="font-weight:bold; font-size: 16px;" class="bg-info">
											<td colspan="4" class="text-right"><strong>Total Product Costing</strong></td>
											<td id="total_product_costing"><strong><?=$total_product_costing ?></strong></td>
										</tr>
										<?php
										// var_dump($total_qty);
										 $total_net_costing = number_format(($total_product_costing/$total_qty),2,".",""); ?>
										
										<tr style="font-weight:bold; font-size: 16px;" class="bg-info">
											<td colspan="4" class="text-right"><strong>Total Net Costing</strong></td>
											<td id="total_product_costing"><strong><?= $total_net_costing ?></strong></td>
										</tr>
										<tr>
											<td colspan="5" class="text-right">
												<div class="col-md-6 col-md-offset-6">
												<div class="form-group">
													<label class="col-md-6 text-right control-label">Costing Template *</label>
													<div class="col-md-6 col-xs-11 text-left">
														<select class="select2" name="dyn_bom_costing_id" id="dyn_bom_costing_id" onchange="change_template(this.value,'<?=$total_product_costing?>')">
															<?=get_bom_costing($dbcon,$so_detail['product_id'],$so_detail['bom_id'],$so_detail['bom_costing_id']);?>
														</select>
													</div>
												</div>
											</div>
											</td>
										</tr>

										<?php $total_costing_value = $total_product_costing;?>
										<tr>
											<td colspan="5">
												<div id="bom_costing_valuation">
													<?php if($so_detail['bom_costing_id'] > 0){ 

													$qry1 = "select * from  tbl_workorder_costing_extra_rate where status =0 and sp_id = ".$work_order_id; 
														$rs1=$dbcon->query($qry1);	
														if(brp_mysqli_num_rows($rs1) == 0){
													$qry1 = "select * from  tbl_bom_costing_extra_rate where status =0 and bom_costing_id = ".$so_detail['bom_costing_id']; 
														$rs1=$dbcon->query($qry1);
													}
														if(brp_mysqli_num_rows($rs1) >= 0){
															
	echo  '<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;">';
	$i=1;
		while($row2 = brp_mysqli_fetch_array($rs1)){
			echo "<tr id=''>";
			
	  	echo "<td width='80%' class='text-right tmp_typename'>".$row2['type_name']."</td>";
			if($row2['type'] == '0') { // 0 - plus | 1 - minus
				$plus = 0;

				if(!empty($row2['per']) && $row2['per'] > 0){
					echo "<td width='10%' class='text-right'><input type='text' id='input_rate_".$i."' class='form-control input_rate' data-cal-type='1' value='".$row2['per']."' onkeyup='calculate_rate(".$i.",".$total_product_costing.",1)'>%</td>";
					$plus =  ($total_product_costing * $row2['per']) / 100;
				}else if(!empty($row2['amount']) && $row2['amount'] > 0){
					echo "<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$total_product_costing.",2)' value='".$row2['amount']."'></td>";
					$plus = $row2['amount'];
				}else{
					echo "<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$total_product_costing.",2)' value='0'></td>";
					$plus = 0;
				}
				echo "<td width='10%' data-operation='0' class='input_temp_rate' id='txt_tmp_total_".$i."' style='color:green'>".$plus."</td>";

				$total_costing_value = $total_costing_value + $plus;
				
			}else{
				$minus = 0;

				if(!empty($row2['per']) && $row2['per'] > 0){
					echo "<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='1' onkeyup='calculate_rate(".$i.",".$total_product_costing.",1)' value='".$row2['per']."'>%</td>";
					$minus =  ($total_product_costing * $row2['per']) / 100;
				}else if(!empty($row2['amount']) && $row2['amount'] > 0){
					echo "<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$total_product_costing.",2)' value='".$row2['amount']."'></td>";
					$minus = $row2['amount'];
				}else{
					echo "<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$total_product_costing.",2)' value='0'></td>";
					$minus = 0;
				}
				echo "<td width='10%' class='input_temp_rate' data-operation='1' id='txt_tmp_total_".$i."' style='color:red'>".$minus."</td>";

				$total_costing_value = $total_costing_value - $minus;

			}
			
			
			echo "</tr>";
			$i++;
		}
		echo "</table>";
														}
													}
													?>
													<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;">
													<tr style="font-weight:bold; font-size: 16px;" class="bg-info">
											<td width="90%" class="text-right"><strong>Total Workorder Costing Rate</strong></td>
											<td id="total_product_costing"><strong><?=number_format(($total_costing_value + $total_net_costing) ,2,".","");?></strong></td>
										</tr>
									</table>
										
													
										</div>
											</td>
										</tr>
										
									</tbody>	
								</table>
							</div>
							<div class="row">	
									<div class="col-md-12 mtop20" style="margin-bottom: 20px;">
										<div class="text-center">
											<button type="button" class="btn btn-success" id="save_costing" name="save_costing" onclick="save_costing_template_value()">Save</button>
										</div>
									</div>
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
		<script src="<?=ROOT.REPORT_ROOT?>js/app/workorder_costing.js?<?php echo time(); ?>"></script>
   
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
</script>


  </body>
</html>
