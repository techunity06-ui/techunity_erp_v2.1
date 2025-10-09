<?php 
session_start();
include('../include/urlfile.php');	
// error_reporting(E_ALL);
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="Jobcard";
$mode="Print";
$bom_id = $dbcon->real_escape_string($_REQUEST['id']);
$bom_id= intval($bom_id);
$query="SELECT  po.job_card_no,u.user_name,po.job_card_date,pmst.product_desc,tb.bom_no,po.in_process_qty, unit.unit_name, spro.po_req_no,spro.sales_order_no, pmst.product_name, po.rp_id,ver.version_name,ver.bom_no,po.company_id,po.bom_id FROM tbl_request_product as po 
left join tbl_set_main_process as spro on spro.sp_id=po.sp_id
left join product_mst as pmst on pmst.product_id=po.rp_pid
left join tbl_bom as tb on tb.bom_product=po.rp_pid
left join pro_ms_bom_version as ver on ver.bom_version_id = tb.bom_version_id
left join users as u on po.user_id=u.user_id
left join unit_mst as unit on unit.unitid=po.process_unit
where ( 1 AND po.job_card_status !=2) and po.rp_id=$bom_id";
$rel_1=mysqli_fetch_assoc($dbcon->query($query));	
//	print_r($rel_1);

$query="select bom.*,product.product_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name from tbl_bom as bom
left join product_mst as product on product.product_id=bom.bom_product
left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
where bom.bom_id=".$rel_1['bom_id'];
$rel=mysqli_fetch_assoc($dbcon->query($query));
	//exit;
$set="select * from tbl_company where company_id=".$rel_1['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));


?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Jobsheet Print</title>
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
										<li><a href="<?=ROOT.PRODUCTION_ROOT.'job_card_list'?>"><?=$form?> List</a></li>
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
												<select class="form-control" name="print_status" id="print_status" <?php if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
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
											<a href="<?=ROOT.PRODUCTION_ROOT.'job_card_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
											<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
										</div>
									</center>	
									<div class="col-md-12"></div>
									<label class="col-md-3 control-label"></label>
									<div class="col-lg-4"></div>
									<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
									<?php ob_start(); ?>
									<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
									<table width="100%" class="maintable" border="0" style="border: 1px solid; border-bottom: none;" id="table_head">
										<tr style="border:none;">
											<td width="100%" style="border:none;"> 
												<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->

												<h2 align="center"><?=$set_head['company_name']?></h2>
												<h5 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h5>
												<h5 align="center"><?=$set_head['address']?></h5>
												<h5 align="center"><?php if($set_head['website']){?>Email: <?=$set_head['website']?><?php }?> 
												<?php if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?php }?></h5>

											</td>
										</tr>
									</table>
									<table width="100%" border="1" style="" id="">
										<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
											<td width="90%" style="text-align:center;border-right: none;"> 
												<strong style="font-size:16px">
													<?=$form?> 
												</strong>
											</td>
											<td width="10%" style="text-align:center;border-left:none"> 
												<strong style="font-size:12px">
													<b class="data_title">ORIGINAL</b>
												</strong>
											</td>
										</tr>
									</table>
									<table width="100%" class="maintable" style="font-size: 12px;margin-top:5px;border:1px solid;" id="invoice_type" >
										<thead>
											<tr style="border-bottom:0.5px #000 solid;">
												<th colspan="7" style="padding:0px !important;">
													<table  style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
														<tr>
															<td width="13%" style=""><strong>Job Card No</strong></td>
															<td style="border-right: 1px solid;" width="41%">: <strong><?= $rel_1['job_card_no'] ?></strong></td>
															<td width="16%" ><strong>Prepared By</strong</td>
															<td style="" width="34%">: <strong>Admin</strong></td>
														</tr>
														<tr>
															<td style="">
																Job Date
															</td>
															<td style="border-right: 1px solid;">
																:<?=date('d/m/Y',strtotime($rel_1['job_card_date'])) ?>							
															</td>

															<td style="">
																<strong>WO NO</strong>
															</td>
															<td style="margin-left: 370px;">
																: 	<?= $rel_1['po_req_no'] ?>					
															</td>	
														</tr>

														<tr>
															<td style="" >
																<strong>Item Name </strong>
															</td>
															<td style="border-right: 1px solid;">
																: <?= $rel_1['product_name'] ?>
															</td>	
															<td style="">
																<strong>Sales Order No</strong>
															</td>
															<td style="" width="46%">
																: <?=$rel_1['sales_order_no']?><!-- (1 STANDARD BILL)		 -->			
															</td>	
														</tr>
															<td style="">
																<strong>Job Quantity</strong>
															</td>
															<td style="border-right: 1px solid;">
																: <?= $rel_1['in_process_qty'] ?>					
															</td>	
															<td style="">
																<strong>BOM Version</strong>
															</td>
															<td style="margin-left: 370px;">
																	: <?= $rel_1['version_name'] ?>				
															</td>	
														</tr>
													</table>
												</th>
											</tr>
										</thead>
									</table>
									</br>
									<strong>Raw Material Issue Details...</strong></br>
									<table width="100%" class="" style="font-size: 12px;margin-top: 5px;border:1px solid">
										<thead>
											<tr height="30px" style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">					
												<th  width="5%" style="border-right: 1px solid;">
													<strong>SR. NO.</strong>
												</th>
												<th width="25%"  style="border-right: 1px solid;" ><strong>Item Details</strong></th>
												<!-- <th width="15%"  style="border-right: 1px solid;" ><strong>UOM</strong></th> -->
												<th width="10%" colspan="2"  style="border-right: 1px solid;" ><strong>Qty</strong></th>
												<th width="10%"  style="border-right: 1px solid;" ><strong>Actual Qty</strong></th>
											</tr>
										</thead>
										<tbody style="border: 1px none;">
										<?php
										$query="SELECT po.rp_id,pmst.product_name,pmst.product_sale_rate,unit.unit_name,po.rp_req_qty
										FROM tbl_request_product as po 
										left join product_mst as pmst on pmst.product_id=po.rp_pid
										left join unit_mst as unit on unit.unitid=po.process_unit
										where po.status=0 and po.perent_id=".$bom_id." order by rp_id,sr_no";
										$result1=$dbcon->query($query);
										
										$pct = mysqli_num_rows($result1);
										// var_dump($pct);
										$i=1;$total=0;
										if($pct>0)
										{
											
											while($re=mysqli_fetch_assoc($result1)) 
											{
												$total=$re['rp_req_qty']+$total;
												$product_sale_rate=$re['product_sale_rate']*$re['used_qty'];
												
										?>
												<tr>
													<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;border-bottom: 1px solid #ddd4d4;" >
														<?php echo $i; ?>
													</td>
													<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;border-bottom: 1px solid #ddd4d4;" ><?php echo $re['product_name'].'<br>'; ?>
														<?php $chkMaterial = $dbcon->query("SELECT bmt.*, mp.material_parameter_name,bom.product_kg FROM tbl_bom_material_trn as bmt LEFT JOIN tbl_material_parameter as mp ON mp.material_parameter_id = bmt.material_parameter_id LEFT JOIN tbl_bomtrn as bom ON bom.bom_trn_id = bmt.bom_trn_id WHERE bmt.bom_material_trn_status = 0 AND bmt.bom_id='".$bom_id."'");
														$Calculation = 0;
														while($getMaterial=brp_mysqli_fetch_assoc($chkMaterial)){
															echo $getMaterial['material_parameter_name'].' - '.$getMaterial['material_parameter_value'].'<br>';
															$Calculation = $getMaterial['product_kg'];
														}
														echo "Calculation: ".$Calculation;
														?>
													</td>
													<!-- <td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;" ><php echo $re['unit_name']; ?></td> -->
													<td colspan="2" style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;border-bottom: 1px solid #ddd4d4;" ><?php echo $re['rp_req_qty'] .' '. $re['unit_name']; ?></td>

													<?php
														$ap_qry = "select p_id from tbl_allocate_process where p_ref_id = " . $bom_id . " and process_priority = 1";
														$ap_res = $dbcon->query($ap_qry);
														$ap_row = brp_mysqli_fetch_assoc($ap_res);

														$p_id = $ap_row['p_id'];
													
															$mt_qry = "select sum(used_qty) as used_qty,unit.unit_name from tbl_allocate_process_material as mt 
															left join unit_mst as unit on unit.unitid=mt.unit_id
															where mt.status = 0 and mt.allocate_process_id = " . $p_id;
														$mt_res = $dbcon->query($mt_qry);
														$mt_row = brp_mysqli_fetch_assoc($mt_res);

														$used_qty = $mt_row['used_qty'];
													
													?>
													<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;border-bottom: 1px solid #ddd4d4;" > <?=$used_qty .' '. $mt_row['unit_name']?>
													</td>
												</tr>
											<?php $i++;
											} 
										}else { ?>
											<tr>
												<td style="border:1px #444 solid;" colspan="9"><center>No data Found</center></td>
											</tr>
										<?php } ?>

										<?php 

										$q_111 = "select trn.*,pro.product_name,unit.unit_name from tbl_workorder_direct_material_issue_trn as trn
																	left join tbl_workorder_direct_material_issue as mst on mst.material_issue_id = trn.material_issue_id
																left join product_mst as pro on pro.product_id = trn.product_id
																left join unit_mst as unit on unit.unitid=trn.base_unit
																where mst.status = 1 and trn.status = 0 and mst.rp_id = " . $bom_id;
										$rel_111=$dbcon->query($q_111);

										while($re_111=mysqli_fetch_assoc($rel_111)) 
										{
											$total=$re_111['base_qty']+$total;
											
										?>
											<tr style="">
												<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;border-bottom: 1px solid #ddd4d4;" >
													<?php echo $i; ?>
												</td>
												<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;border-bottom: 1px solid #ddd4d4;" >
													<?php echo $re_111['product_name'].'<br>'; 
													?>
												</td>
												<!-- <td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;" ><php echo $re_111['unit_name']; ?></td> -->
												<td colspan="2" style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;border-bottom: 1px solid #ddd4d4;" >
													<?php echo $re_111['base_qty'] .' '. $re_111['unit_name']; ?>

												</td>

												<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;border-bottom: 1px solid #ddd4d4;" > <?=$re_111['base_qty'] .' '. $re_111['unit_name']?>
												</td>
											</tr>
										<?php $i++;
										}



										$blnk = 10 - $pct;
										for($i=1; $i<=$blnk; $i++){
											if($blnk==$i){
												$style = "border-bottom: 1px solid black;";
											}else{
												$style = "border-bottom: 1px solid #ddd4d4;";
											}
										?>
											<tr style="height: 40px;">
													<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;<?=$style?>" ></td>
													<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;<?=$style?>" ></td>
													<!-- <td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;" ></td> -->
													<td colspan="2" style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;<?=$style?>" ></td>
													<td style="padding-top: .2em;padding-bottom: .2em;border-right: 1px solid;<?=$style?>" ></td>
												</tr>
										<?php }?>
											<tr style="border-top:0.5px #000 solid;border-bottom:0.5px #000 solid;">
												<td></td>
												<!-- <td></td>
												<td  style="float: right;"></td> -->
												<td style="float: right;border-right: 1px solid;"><strong>TOTAL</strong></td>
												<td  style=""><?php echo $total; ?></td>
												<td  style=""></td>
												<td  style=""></td>
											</tr> 
										</tbody>	
									</table>
									<table width="100%" class="" style="font-size: 12px; border: 1px solid;margin-top: 15px;">
										<thead>
											<tr height="30px">					
												<th  width="5%" style="text-align:center;border:1px solid;border-top:none;">
													<strong>SR. NO.</strong>
												</th>

												<th width="25%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Operator Name</strong></th>
												<th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Process Name</strong></th>
												<th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Start Time</strong></th>
												<th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>End Time</strong></th>

												<th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>OK Qty</strong></th>
												<th width="8%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>R/W Qty</strong></th>
												<th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Reject Qty</strong></th>
											</tr>
										</thead>
										<tbody style="border: 1px solid;">
										<?php

										 $query="SELECT po.rp_pid,wpp.process_id,spro.p_ref_type,spro.pr_process_type as process_type,tqpt.total_qty as issueqty,tqpt.accept_qty as okqty,spro.p_start_time,pm.process_name, tqpt.reject_qty as rejqty,tqpt.reprocess_qty as rwjqty FROM tbl_request_product as po 
											left join tbl_wororder_product_process as wpp on wpp.rp_id = po.rp_id 
											left join tbl_allocate_process as spro on spro.p_ref_id=po.rp_id 
											left join process_mst as pm on pm.process_id=wpp.process_id 
											left join tbl_qc_process_trn as tqpt on tqpt.p_id=spro.p_id where ( 1 AND po.job_card_status !=2 ) and spro.p_status != 2 and po.rp_id =".$bom_id." group by wpp.process_id order by wpp.process_priority";

										$result1=$dbcon->query($query);
										$prct = brp_mysqli_num_rows($result1);
										if($prct>0)
										{
											$i=1;
											while($re=mysqli_fetch_assoc($result1)) {
												$date=date('Y-m-d',strtotime($re['p_start_time']));
												if($date=='1970-01-01'){
													$date='-';
												}

												$process_name = "";
												if($re['process_type'] == '1'){
													$process_name = $re['process_name'] ." - Inhouse";
												}else{
													$process_name = $re['process_name'] . " - Outside";	
												}
												$p_qry = "select p_id,pr_process_type from tbl_allocate_process where p_ref_id =" . $bom_id . " and process_id = " . intval($re['process_id']) . " and p_status !=2 group by pr_process_type";
												$p_res = $dbcon->query($p_qry);

												$p_id_count = brp_mysqli_num_rows($p_res);

												if($p_id_count > 1){
													$x = 1;
													while($p_row = brp_mysqli_fetch_assoc($p_res)){
														if($p_row['pr_process_type'] == '1'){
															$process_name = $re['process_name'] ." - Inhouse";
														}else{
															$process_name = $re['process_name'] . " - Outside";	
														} 

														$p_id = $p_row['p_id'];

												$p_time_qry = "select process_time as start_time, (select process_time from tbl_allocate_process_trn where p_status = 1 and pt_ref_id = ".$bom_id." and pt_alloc_id = ".$p_id." order by pt_id DESC) as  end_time,(select l.user_name from tbl_allocate_process_trn as t left join users as l on l.user_id =t.start_stop_user_id where t.p_status = 1 and t.pt_ref_id = ".$bom_id." and t.pt_alloc_id = ".$p_id." order by pt_id DESC) as  l_name from tbl_allocate_process_trn as ap where  ap.pt_ref_id = ".$bom_id." and pt_alloc_id = ".$p_id." and ap.p_status = 0 and ap.parent_pt_id = 0";
												$p_time_res = $dbcon->query($p_time_qry);
												$p_time_row = brp_mysqli_fetch_assoc($p_time_res);
												$start_time =''; 
												$end_time =''; 
												if(!empty($p_time_row['start_time'])){
													$start_time = date('d/m/Y h:i:sa',strtotime($p_time_row['start_time']));
												}
												if(!empty($p_time_row['end_time'])){
													$end_time = date('d/m/Y h:i:sa',strtotime($p_time_row['end_time']));
												}

											$qty_detail =	get_jobsheet_qty_details($dbcon,$re['rp_pid'],$bom_id,$re['process_id'],$p_row['pr_process_type']);

														?>

														<tr>
													<td style="border:1px #444 solid;" ><?php echo $i; ?></td>
													<td style="border:1px #444 solid;" ><?php echo $p_time_row['l_name']; ?></td><td style="border:1px #444 solid;" ><?php echo $process_name; ?></td>
										
													<td style="border:1px #444 solid;" ><?php echo $start_time; ?></td>
													<td style="border:1px #444 solid;" ><?php echo $end_time; ?></td>
													<td style="border:1px #444 solid;" >				<?=$qty_detail['accept_qty'];?>								
													</td>
													<td style="border:1px #444 solid;" ><?=$qty_detail['reprocess_qty'];?></td>
													<td style="border:1px #444 solid;" ><?=$qty_detail['reject_qty'];?></td>

												</tr>
												<?php if($x < $p_id_count) {$i++;} $x++; 	}	
												}else{ 
													$p_row = brp_mysqli_fetch_assoc($p_res);
												$p_id = intval($p_row['p_id']);
												// $qty_detail =	get_jobsheet_qty_details($dbcon,$re['rp_pid'],$bom_id,$re['process_id'],$re['process_type']);
												if (!empty($re['process_id']) && !empty($re['process_type'])) {
													$qty_detail = get_jobsheet_qty_details($dbcon, $re['rp_pid'], $bom_id, $re['process_id'], $re['process_type']);
												} else {
													$qty_detail = 0; // or some default value
												}
												$p_time_qry = "select process_time as start_time, (select process_time from tbl_allocate_process_trn where p_status = 1 and pt_ref_id = ".$bom_id." and pt_alloc_id = ".$p_id." order by pt_id DESC) as  end_time,(select user_name from tbl_allocate_process_trn as t left join users as l on l.user_id =t.start_stop_user_id where t.p_status = 1 and t.pt_ref_id = ".$bom_id." and t.pt_alloc_id = ".$p_id." order by pt_id DESC) as  l_name from tbl_allocate_process_trn as ap where  ap.pt_ref_id = ".$bom_id." and pt_alloc_id = ".$p_id." and ap.p_status = 0 and ap.parent_pt_id = 0";
												$p_time_res = $dbcon->query($p_time_qry);
												$p_time_row = brp_mysqli_fetch_assoc($p_time_res);
												$start_time =''; 
												$end_time =''; 
												if(!empty($p_time_row['start_time'])){
													$start_time = date('d/m/Y h:i:sa',strtotime($p_time_row['start_time']));
												}
												if(!empty($p_time_row['end_time'])){
													$end_time = date('d/m/Y h:i:sa',strtotime($p_time_row['end_time']));
												}

													?>


													
													<tr>
													<td style="border:1px #444 solid;" ><?php echo $i; ?></td>
													<td style="border:1px #444 solid;" ><?php echo $p_time_row['l_name']; ?></td><td style="border:1px #444 solid;" ><?php echo $process_name; ?></td>
										
													<td style="border:1px #444 solid;" ><?php echo $start_time; ?></td>
													<td style="border:1px #444 solid;" ><?php echo $end_time; ?></td>
													<td style="border:1px #444 solid;" >				<?=$qty_detail['accept_qty'];?>								
													</td>
													<td style="border:1px #444 solid;" ><?=$qty_detail['reprocess_qty'];?></td>
													<td style="border:1px #444 solid;" ><?=$qty_detail['reject_qty'];?></td>

												</tr>
											<?php 	}
												
										?>
											
										<?php $i++;

										$p_qry = "select pt_alloc_id,pr_process_type from tbl_allocate_re_process where p_ref_id =" .$bom_id . " and process_id = " . intval($re['process_id']) . " and p_status !=2 group by pr_process_type";
												$p_res = $dbcon->query($p_qry);

												$p_id_count = brp_mysqli_num_rows($p_res);
													$x = 1;
													while($p_row = brp_mysqli_fetch_assoc($p_res)){
														if($p_row['pr_process_type'] == '1'){
															$process_name = $re['process_name'] ." - Inhouse - Reprocess";
														}else{
															$process_name = $re['process_name'] . " - Outside - Reprocess";	
														} 

														$p_id = $p_row['pt_alloc_id'];

												 $p_time_qry = "select l.user_name from tbl_allocate_re_process_trn as t left join tbl_ledger as l on l.user_id =t.user_id where  t.pt_ref_id = ".$bom_id." and t.pt_alloc_id = ".$p_id." and t.p_status = 0 "; 
												$p_time_res = $dbcon->query($p_time_qry);
												$p_time_row = brp_mysqli_fetch_assoc($p_time_res);
												$start_time =''; 
												$end_time =''; 
												if(!empty($p_time_row['start_time'])){
													$start_time = date('d/m/Y h:i:sa',strtotime($p_time_row['start_time']));
												}
												if(!empty($p_time_row['end_time'])){
													$end_time = date('d/m/Y h:i:sa',strtotime($p_time_row['end_time']));
												}

											$qty_detail = get_jobsheet_qty_details_reprocess($dbcon,$re['rp_pid'],$bom_id,$re['process_id'],$p_row['pr_process_type']);

														?>

														<tr>
													<td style="border:1px #444 solid;" ><?php echo $i; ?></td>
													<td style="border:1px #444 solid;" ><?php echo $p_time_row['l_name']; ?></td><td style="border:1px #444 solid;" ><?php echo $process_name; ?></td>
										
													<td style="border:1px #444 solid;" ><?php echo $start_time; ?></td>
													<td style="border:1px #444 solid;" ><?php echo $end_time; ?></td>
													<td style="border:1px #444 solid;" >				<?=$qty_detail['accept_qty'];?>								
													</td>
													<td style="border:1px #444 solid;" ><?=$qty_detail['reprocess_qty'];?></td>
													<td style="border:1px #444 solid;" ><?=$qty_detail['reject_qty'];?></td>

												</tr>
												<?php	$i++;}
										} 
									}else { ?>
										<tr>
											<td style="border:1px #444 solid;" colspan="9"><center>No data Found</center></td>
										</tr>
									<?php } ?>
									<?php 
										$bln = 10-$prct;
										for($i=0; $i<$bln; $i++){
									?>
										<tr style="height: 20px;">
											<td style="border:1px #444 solid;" ></td>
											<td style="border:1px #444 solid;" ></td>
											<td style="border:1px #444 solid;" ></td>
											<td style="border:1px #444 solid;" ></td>
											<td style="border:1px #444 solid;" ></td>
											<td style="border:1px #444 solid;" ></td>
											<td style="border:1px #444 solid;" ></td>
											<td style="border:1px #444 solid;" ></td>
										</tr>
									<?php }?>
								</tbody>
							</table>
							<!-- <table style="font-size: 12px;margin-top: 15px; width: 100%;">
								<tr style="border-top: 1px solid;">
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
								</tr>
							</table>
							<!-- <table style="font-size: 12px;margin-top: 15px;">
								<tr>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%">Material Cost :</td>
									<td width="10%">0.00</td>
									<td width="10%"></td>
								</tr>
								<tr>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%">Process Cost :</td>
									<td width="10%">0.00</td>
									<td width="10%"></td>
								</tr>
								<tr>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%">Total Cost :</td>
									<td width="10%">0.00</td>
									<td width="10%"></td>
								</tr>
								<tr>
									<td colspan="8"></td>
								</tr>
								<tr>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="10%"></td>
									<td width="30%"></td>
									<td width="20%" style="border-top: 1px solid;">Assb Rate (Avg):</td>
									<td width="15%" style="border-top: 1px solid;">0.00</td>
									<td width="10%"></td>
								</tr>
							</table> -->
						</div>
		<div id="print2"></div>
		<div id="print3"></div>

	</div>
	<?php  
	$contents = ob_get_contents();
	$_SESSION['contents']=$contents;
	$_SESSION['file_name']='Challan-#';
	$_SESSION['page_size']='A5';
	echo "<script> function make_pdf()
	{ window.open('".ROOT.PRODUCTION_ROOT."export/print','_blank');
}</script>";  
?>
</div>	
</section>
</div>
</div>
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

<!--<script src="js/count.js"></script>-->
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
  docprint.document.write('<head><title><?php echo TITLE;?></title>');
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
