<?php 
session_start();
include_once("../../config/config.php");
include_once("../../config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$include = '../../include/';
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']=''; 
$form="Invoice";
$mode="Print";
$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
$query="select invoice.*,country.country_name,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type, cust_pincode, cust_mobile, gst_no, dispatch.mode_dispatch, cust.m_pan,cust.enable_sez from tbl_invoice as invoice 
left join tbl_ledger as cust on cust.l_id=invoice.cust_id
left join country_mst as country on country.countryid=cust.countryid
left join state_mst as state on state.stateid=cust.stateid
left join city_mst as city on city.cityid=cust.cityid
left join mode_of_dispatch as dispatch on dispatch.mode_dis_id=invoice.dispatch_doc_no
left join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id
where invoice.invoice_id=$invoiceid";
$rel=mysqli_fetch_assoc($dbcon->query($query));
$cons_gst_no=$rel['gst_no'];
$cons_pan_no=$rel['pan_no'];
$cons_state_name=$rel['state_name'];
$cons_gst_state_code=$rel['gst_state_code'];
$place_of_supply=$rel['city_name'];
if(!empty($rel['consignee_id']))
{	
	$consignee="select * from tbl_custmer_consignee as cust 
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid 
	left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
	$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
	$cons_gst_no=$cons_data['gst_no'];
	$cons_pan_no=$cons_data['pan_no'];
	$cons_state_name=$cons_data['state_name'];
	$cons_gst_state_code=$cons_data['gst_state_code'];
	$place_of_supply=$cons_data['city_name'];
}

$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	
$challan_date='';$lr_date='';$dispatch_date='';
if($rel['challan_date']!="1970-01-01" && $rel['challan_date']!="0000-00-00")
{
	$challan_date=date('d-m-Y',strtotime($rel['challan_date']));
}
if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
{
	$dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
}

$po_no = '';
$po_date = '';
if($rel['is_sales_order']!=0){
	$qry = $dbcon->query("SELECT po_no, po_date FROM tbl_sales_order WHERE sales_order_id = '".$rel['sales_order_id']."'");
	$rels = brp_mysqli_fetch_assoc($qry);
	$po_no = $rels['po_no'];
	$po_date = ($rels['po_date']!='1970-01-01' && $rels['po_date']!='0000-00-00') ? $rels['po_date'] : '';
}
else
{
	$po_no = $rel['order_no'];
	if($rel['order_date']!="1970-01-01")
	{
		$po_date = date("d/m/Y",strtotime($rel['order_date']));
	}
	else
	{
		$po_date='';
	}
}

$custLedgerDetails = get_cust_data_arr($dbcon,$rel['cust_id']);
$company_config = getCompanyConfiguration($dbcon);

//check po number 

if($rel['sales_order_id']!='')
{

	$so_number = explode(",",$rel['sales_order_id']);
	//print_r($so_number);
	for($i=0;$i<count($so_number);$i++)
	{
		$po_number.= get_id_detail($dbcon,'tbl_sales_order','sales_order_id',$so_number[$i],'po_no').',';
		$po_number_date.= date("d/m/Y",strtotime(get_id_detail($dbcon,'tbl_sales_order','sales_order_id',$so_number[$i],'po_date'))).',';

	}
}
else
{
	$po_number = $rel['order_no'];
	$po_number_date = $rel['order_date'];
}

$sundrytax=$dbcon->query("select b.* from tbl_bill_sundry_transaction as b where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' ");
//$sundrytax_rels = mysqli_fetch_assoc($sundrytax);
$total_sundrytax=0;
while($sumsundrytax=mysqli_fetch_assoc($sundrytax)){
	$total_sundrytax = $total_sundrytax + $sumsundrytax['sundry_gst_amount'];
}
$sel_t = $dbcon->query("select trn.*,t.id,t.transportation_name from tbl_transport_transaction as trn 
	left join transportation_details as t on t.id=trn.transport_id
	where transport_transaction_table_id='$invoiceid'");
$r_t=brp_mysqli_fetch_assoc($sel_t);

$qry_disc="SELECT SUM(trn.product_discount) as discount FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id'];
$rel_disc = brp_mysqli_fetch_assoc($qry_disc);

/* Check Discount is On or off Start */
if($rel_disc['discount'] > 0){
	$colspan=5;
	$cols = 3;
	$dynamicwidth=40;
}else{
	$colspan=6;
	$cols = 2;
	$dynamicwidth=46;
}
/* Check Discount is On or off End */
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once($include.'include_css_file.php');?>
	<style>
		body {color: #000000;}
		.con ul {padding-left:0px;}
		.con ul li { margin-left:22px;list-style: disc !important;}
		/*td, th {padding: 0px 5px !important;}*/
		#print_status, #print_status option { text-overflow: ellipsis;}
			@media(max-width:768px){/*.boderremoveres{border-left:none !important;}.borderleftadd{border-left:1px solid !important
			}*/}
		</style>
	</head>
	<body>
		<section id="container" >
			<?php include_once($include.'include_top_menu.php');?>
			<!--sidebar start-->
			<?php include_once($include.'left_menu.php');?>
			<!--sidebar end-->
			<!--main content start-->
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<!--breadcrumbs start -->
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<li><a href="<?=ROOT.FINANCE_ROOT.'invoice_list'?>">Invoice List</a></li>
									</ul>
								</div>
							</section>
							<!--breadcrumbs end -->
						</div>	
					</div>
					<!--state overview start-->
					<div class="row">			
						<div class="col-sm-12 col-md-12 col-lg-8 col-lg-offset-2">
							<!--<div class="col-sm-12">-->
								<section class="panel">
									<div class="panel-body">
										<div class="col-md-12">
											<center>
												<div class="col-sm-5"  style="padding-left:0;">
													<label class="col-md-2 control-label" style="padding-top: 10px;"> Print</label>
													<div class="col-md-10 col-xs-12">
														<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
															<select class="form-control" name="print_status" id="print_status" <?if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
																<option value="" >Select Print</option>
																<option value="1" <?if($_REQUEST['printstatus']=='1'){ echo "selected";}else{ echo "selected";}?>>ORIGINAL</option>
																<option value="2" <?if($_REQUEST['printstatus']=='2'){ echo "selected";}?>>DUPLICATE</option>
																<option value="3" <?if($_REQUEST['printstatus']=='3'){ echo "selected";}?>>TRIPLICATE</option>
																<option value="4" <?if($_REQUEST['printstatus']=='4'){ echo "selected";}?>>EXTRA</option>
															</select>
														</form>
													</div>
												</div>
												<div class="col-sm-3 resclear">
													<label class=" control-label col-sm-7 " style="
													padding-top: 10px; padding:10px 0 0;">With Logo</label>
													<div class=" resclear col-sm-5">
														<input type="checkbox" class="form-control"  name="logo" id="logo" value="1" checked>
													</div>
												</div>
												<div class="col-sm-4 resclear resspace"  style="text-align:right">
													<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
													<a href="<?=ROOT.FINANCE_ROOT.'invoice_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
												</div>
												<div class="col-sm-4 resclear resspace"  style="text-align:center;padding-top:5px;">
													<a type="button" class="btn btn-success" href="https://web.whatsapp.com/send?phone=+91<?echo $rel['cust_mobile']?>&text=<?echo $rel['company_name']?>%2C%0aThank you for your purchase.%0aInvoice No:-<?phpecho $rel['invoice_no']?>%0aDate:-<?phpecho date('d-m-Y',strtotime($rel['invoice_date']))?>%0aAmount:-<?phpecho $rel['g_total']?>%0aBest Regards%0a
														<?phpecho $set_head['company_name']?>" target="_blank"> <i class="fa fa-whatsapp"></i> Whatsapp</a>
													</div>
												</center>
											</div>
											<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
											<?php ob_start(); ?>
											<div class="col-lg-12 " id="receipt_print">	
												<div class="col-md-12 breakout" style=" margin-top:10px;" id="print1">
													<table  class="maintable headermain " id="table_head" width="100%">
														<tr style="border:none;">
															<td width="100%" style="border:none;">
																<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>
															</td>
														</tr>
													</table>	
													<table width="100%" class="maintable" style=" font-size:11px" id="invoice_type" >
														<thead id="fiac">
															<tr>
																<th colspan="2" style="border-top:1px solid; border-left:1px solid;">GSTIN</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" style="border-top:1px solid; border-right:1px solid;">: <?=$set_head['vatno'];?></th>
																<th colspan="2" style="border-top:1px solid; border-left:1px solid; white-space: nowrap;">Invoice No</th>
																<th colspan="3" style="border-top:1px solid; border-right:1px solid; white-space: nowrap;"><?=$rel['invoice_no']?></th>
															</tr>
															<tr>
																<th colspan="2" style="border-bottom:1px solid; border-left:1px solid;">DL. No</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" style="border-bottom:1px solid; border-right:1px solid;">: </th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Date</th>
																<th colspan="3" style="border-right:1px solid;"><?=date("d/m/Y",strtotime($rel['invoice_date']));?></th>
															</tr>
															<tr>
																<th colspan="2" style="border-top:1px solid; border-left:1px solid;">Name</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" style="border-top:1px solid; border-right:1px solid;">: <?=$rel['l_name']?></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Delivery Challan No</th>
																<th colspan="3" style="border-right:1px solid;"><?=($rel['challan_no']) ? $rel['challan_no'] : ''?></th>
															</tr>
															<tr>
																<th colspan="2" style="border-left:1px solid;">Address</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" rowspan="2" style="border-right:1px solid;">: <?=$rel['m_address']?><br/><?=$rel['city_name']?>, <?=$rel['state_name']?>, <?=$rel['country_name']?><?phpif(!empty($rel['cust_pincode'])){	?> - <?=$rel['cust_pincode']?><?php} ?></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Delivery Challan Dt.</th>
																<th colspan="3" style="border-right:1px solid;"><?=$challan_date?></th>
															</tr>
															<tr>
																<th colspan="2" style="border-left:1px solid;"></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Party Order No.</th>
																<th colspan="3" style="border-right:1px solid;"></th>
															</tr>
															<tr>
																<th colspan="2" style="border-left:1px solid;">GST No.</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" style="border-right:1px solid;">: <?=$rel['gst_no']?></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Party Order Dt.</th>
																<th colspan="3" style="border-right:1px solid;"></th>
															</tr>
															<tr>
																<th colspan="2" style="border-left:1px solid;">State</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" style="border-right:1px solid;">: <?=$rel['state_name']?>&nbsp;&nbsp;&nbsp;&nbsp;State Code: <?=$rel['gst_state_code']?></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Date of Supply</th>
																<th colspan="3" style="border-right:1px solid;"></th>
															</tr>
															<tr>
																<th colspan="2" style="border-top:1px solid; border-left:1px solid;">Cons Name</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" style="border-top:1px solid; border-right:1px solid;">: <?=$rel['l_name']?></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Place of Supply</th>
																<th colspan="3" style="border-right:1px solid;"><?=$place_of_supply?></th>
															</tr>
															<tr>
																<th colspan="2" style="border-left:1px solid;">Address</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" rowspan="2" style="border-right:1px solid;">: <?=$rel['m_address']?><br/><?=$rel['city_name']?>, <?=$rel['state_name']?>, <?=$rel['country_name']?><?phpif(!empty($rel['cust_pincode'])){?> - <?=$rel['cust_pincode']?><?php} ?></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Transporter</th>
																<th colspan="3" style="border-right:1px solid;"><?=$r_t['transportation_name']?></th>
															</tr>
															<tr>
																<th colspan="2" style="border-left:1px solid;"></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">Vehicle No.</th>
																<th colspan="3" style="border-right:1px solid;"><?=$r_t['transport_vehicle_no']?></th>
															</tr>
															<tr>
																<th colspan="2" style="border-left:1px solid;">GST No.</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" style="border-right:1px solid;">: <?=$rel['gst_no']?></th>
																<th colspan="2" style="border-left:1px solid; white-space: nowrap;">L.R No</th>
																<th colspan="3" style="border-right:1px solid;"></th>
															</tr>
															<tr>
																<th colspan="2" style="border-left:1px solid; border-bottom: 1px solid;">State</th>
																<th colspan="<?=(($rel_disc['discount'] > 0) ? '3' : '2')+(($rel['stateid']==$set_head['stateid']) ? '1' : '0');?>" style="border-right:1px solid; border-bottom: 1px solid;">: <?=$rel['state_name']?>&nbsp;&nbsp;&nbsp;&nbsp;State Code: <?=$rel['gst_state_code']?></th>
																<th colspan="2" style="border-left:1px solid; border-bottom: 1px solid;">L.R Date</th>
																<th colspan="3" style="border-right:1px solid; border-bottom: 1px solid;"></th>
															</tr>
															<tr>
																<th width="3%" style="text-align:center;border:1px solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
																<th width="6%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>Code No</strong>
																</th>
																<th width="10%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>Batch No</strong>
																</th>
																<th width="<?=$dynamicwidth?>%" style="text-align:center !important; border:1px solid;border-top: none;" >
																	<strong>Description of Goods</strong>
																</th>
																<th width="8%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>HSN</strong>
																</th>
																<?php if($rel['stateid']==$set_head['stateid']){ ?>
																	<th width="4%" style="text-align:center  !important;border:1px solid;border-top: none;">
																		<strong>SGST %</strong>
																	</th>
																	<th width="4%" style="text-align:center  !important;border:1px solid;border-top: none;">
																		<strong>CGST %</strong>
																	</th>
																<?php} else { ?>
																	<th width="4%" style="text-align:center  !important;border:1px solid;border-top: none;">
																		<strong>IGST %</strong>
																	</th>
																<?php} ?>
																<th width="7%" style="text-align:center !important;border:1px solid;border-top: none;">
																	<strong>QTY.</strong>
																</th>
																<th width="7%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>Rate</strong>
																</th>
																<?phpif($rel_disc['discount'] > 0){ ?>
																	<th width="6%" style="text-align:center  !important;border:1px solid;border-top: none;">
																		<strong>Disc.</strong>
																	</th>
																<?php} ?>
																<th width="9%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>Amount</strong>
																</th>
															</tr>
														</thead>
														<tbody style="border: none;">
															<?php $qry="select trn.*,product.*,unit_name FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product.product_type,trancation_id";

															$result=$dbcon->query($qry);		
															$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
															$cnt=mysqli_num_rows($result);
															while($row=mysqli_fetch_assoc($result))
															{
														//$tax_arr=explode(",",$row['tax_val']);
																$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
																$gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate']+$total_sundrytax;

																if($row['cgst_tax_rate'] != 0 || $row['sgst_tax_rate'] !=0){
																	$total_cs_gst += $gst_rate;
																}else{
																	$total_i_gst += $gst_rate;
																}

														//tax summary calculation start
																if(!empty($row['tax_val']))
																{
																	$tax_num=explode(",",$row['tax_val']);
																	$tax_name=explode(",",$row['tax_name']);

																	$total_net_rate=($row['product_qty']*$row['product_rate'])-$row['discount'];
																	for($j=0;$j<count($tax_num);$j++)
																	{
																		if(!in_array($tax_name[$j],$tax['per']))
																		{
																			$tax['per'][]=$tax_name[$j];
																		}
																		$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
																	}
																}?>
																<tr style="height:25px">
																	<td class="borderleftadd" style="text-align:center !important; vertical-align:top;border-right:1px solid;border-left:1px solid;">
																		<?phpif($row['product_type']!='3'){ echo $i;}?>
																	</td>
																	<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																	</td>
																	<td style="text-align:right  !important; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																	</td>
																	<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;<?phpif($row['product_type']=='3'){ echo 'text-align:right !important;padding-top:5px;vertical-align:top;';}?>" >
																		<strong><?=stripcslashes($row['product_name'])?></strong>
																		<?$batch_detail = "select bst.*,st.batch_no from tbl_batch_stock_tmp as bst
																		left join `tbl_stock_trn` as st on st.stock_id=bst.stock_id where invoice_trn_id = ".$row['trancation_id']." and status =1";
																		$brtch_q = $dbcon->query($batch_detail);
																		while($r = brp_mysqli_fetch_array($brtch_q)){?>
																			<span><strong>Batch : </strong><?=$r['batch_no']?></span>	
																			<span><strong>Qty : </strong><?=$r['qty']?></span><br>
																		<?php} ?>
																	</td>
																	<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;text-align:center  !important" >
																		<?=stripcslashes($row['product_hsn_code'])?>
																	</td>
																	<?php if($rel['stateid']==$set_head['stateid']){ ?>
																		<td style="white-space: nowrap; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																			<?=$row['sgst_tax_per']?> %
																		</td>
																		<td style="white-space: nowrap; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																			<?=$row['cgst_tax_per']?> %
																		</td>
																	<?php} else { ?>
																		<td style="text-align:right  !important; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																			<?=$row['igst_tax_per']?> %
																		</td>
																	<?php} ?>
																	<td style="text-align:center  !important; vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;white-space:nowrap;" >
																		<?phpif($row['product_type']!='3'){ ?>
																			<?=$row['product_qty'].' '.$row['unit_name']?>
																		<?php}else{
																			$charges_qty+=$row['product_qty'];
																			echo $charges_qty;
																		} ?>	
																	</td>
																	<td style="text-align:right  !important;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
																		<?=number_format($row['product_rate'],2,".","")?>
																	</td>
																	<?phpif($rel_disc['discount'] > 0){?>
																		<td style="text-align:right  !important; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																			<?=number_format($row['discount_per'],2,".","").'%'?>
																		</td>
																	<?php} ?>
																	<td style="text-align:right  !important; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																		<?=number_format(($row['product_rate'] * $row['product_qty']),2,".","")?>
																	</td>
																</tr>

																<?php
																$i++; 
																$totalqty=$totalqty+$row['product_qty']-$charges_qty;
																$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
																$total_product_amount+=($row['product_qty']*$row['product_rate']);
																$totaltaxable+=$row['product_amount'];
																$totaltax1+=$row['tax_amount1'];
																$totaltax2+=$row['tax_amount2'];
																$total+=$row['total'];
																$total_gst_rate +=$gst_rate;
															}
															$pr=9-$cnt;

															for($j=0; $j<$pr; $j++) { ?>	
																<tr style="height:35px">
																	<td class="borderleftadd" style="text-align:center !important; vertical-align:top;border-right:1px solid;border-left:1px solid;">
																	</td>
																	<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;<?phpif($row['product_type']=='3'){ echo 'text-align:right;padding-top:5px;vertical-align:top;';}?>" >
																	</td>
																	<?phpif($rel_disc['discount'] > 0){?>
																		<td style="border-right:1px solid;"></td>
																	<?php} ?>
																	<?php if($rel['stateid']==$set_head['stateid']){ ?>
																		<td style="border-right:1px solid;"></td>
																		<td style="border-right:1px solid;"></td>
																	<?php} else { ?>
																		<td style="border-right:1px solid;"></td>
																	<?php} ?>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																</tr>
															<?php} ?>
															<tr style="height:20px">
																<td class="borderleftadd" style="border-top:1px solid;border-right:1px solid;border-left:1px solid; text-align:right  !important;" colspan="5">
																	<strong>Total</strong>
																</td>
																<?php if($rel['stateid']==$set_head['stateid']){ ?>
																	<td style="border-top:1px solid;border-right:1px solid;"></td>
																	<td style="border-top:1px solid;border-right:1px solid;"></td>
																<?php} else { ?>
																	<td style="border-top:1px solid;border-right:1px solid;"></td>
																<?php} ?>
																<td style="text-align:center  !important;border-top:1px solid;border-right:1px solid;">
																	<strong><?=number_format($totalqty,2,".","")?></strong>
																</td>
																<?phpif($rel_disc['discount'] > 0){?>
																	<td style="border-top:1px solid;border-right:1px solid;"></td>
																<?php} ?>
																<td style="border-top:1px solid;border-right:1px solid;"></td>
																<td style="border-top:1px solid;border-right:1px solid;text-align:right  !important;">
																	<strong><?=number_format($totaltaxable,2,".","")?></strong>
																</td>
															</tr>		
															<tr>
																<td class="borderleftadd" colspan="11" style="padding: 0px !important;border:1px solid">
																	<table class="footer-table" width="100%">
																		<tr height="20px">
																			<td width="61.6%" style="border-right:1px solid; border-bottom: none; font-size:10px;" colspan="<?=$colspan?>">
																				<?phpif(!empty($set_head['bank_name'])){?>
																					<strong>Bank Name:</strong> <?=$set_head['bank_name']?>, 
																				<?php} ?>
																				<?phpif(!empty($set_head['ac_no'])){?>
																					<strong>A/c No:</strong> <?=$set_head['ac_no']?>	 
																				<?php} ?>
																			</td>
																			<td colspan="3" width="28.7%" style="border-right:1px solid;font-size:10px;text-align:left  !important; border-bottom: none;">
																				Taxable Amount
																			</td>
																			<td colspan="2" style="text-align:right  !important;font-size:10px; border-bottom: none;" width="10%"><?=number_format($totaltaxable,2,".","")?></td>	
																		</tr>
																		<?php if($rel['stateid']==$set_head['stateid'] && ($custLedgerDetails['enable_sez'] == 0)){ ?>
																			<?php if($company_config['tax_editable'] == 0){ 
																				$c_gst = $total_cs_gst/2;
																				$s_gst = $total_cs_gst/2;
																			}else{
																				$c_gst = $rel['cgst'];
																				$s_gst = $rel['sgst'];
																			}
																			$merchantTax = ($totaltaxable*(0.1))/100;
																			if($rel['sales_type'] == 2){ 
																				$c_gst = $merchantTax/2;
																				$s_gst = $merchantTax/2;
																			}
																			?>
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px; border-bottom: none;" colspan="<?=$colspan?>">
																					<?phpif(!empty($set_head['ifcs'])){ ?>
																					<strong>IFSC:</strong><?=$set_head['ifcs']?>,
																				<?php} ?>	
																				<?phpif(!empty($set_head['branch_name'])){ ?>
																					<strong>Branch :</strong> <?=$set_head['branch_name']?>
																				<?php} ?>
																				</td>
																				<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important; border-bottom: none;" >
																					Tax Amount : CGST
																				</td>
																				<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; border-bottom: none;"><?=number_format($c_gst,2,".","")?></td>
																			</tr>
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px; border-bottom: none;" colspan="<?=$colspan?>">
																				</td>
																				<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important; border-bottom: none;" >
																					Tax Amount : SGST
																				</td>
																				<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; border-bottom: none;"><?=number_format($s_gst,2,".","")?></td>
																			</tr>
																		<?php }else{ ?>
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px; border-bottom: none;" colspan="<?=$colspan?>">
																					<?phpif(!empty($set_head['ifcs'])){ ?>
																					<strong>IFSC:</strong><?=$set_head['ifcs']?>,
																				<?php} ?>	
																				<?phpif(!empty($set_head['branch_name'])){ ?>
																					<strong>Branch :</strong> <?=$set_head['branch_name']?>
																				<?php} ?>
																				</td>
																				<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important; border-bottom: none;" >
																					Tax Amount : IGST
																				</td>
																				<?php if($company_config['tax_editable'] == 0){ 
																					$i_gst = $total_i_gst;
																				}else{
																					$i_gst = $rel['igst'];
																				}
																				$merchantTax = ($totaltaxable*(0.1))/100;
																				if($rel['sales_type'] == 2){ 
																					$i_gst = $merchantTax;
																				}
																				?>
																				<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; border-bottom: none;"><?=number_format($i_gst,2,".","")?></td>
																			</tr>
																		<?php } ?>
																		<?php 
																		$qry121="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
																		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
																		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
																		where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and b.sundry_ledger_id=".TCS." ";
																		$result121=$dbcon->query($qry121);
																		if(brp_mysqli_num_rows($result121)>0)
																		{
																			$row121 = brp_mysqli_fetch_assoc($result121);
																			?>
																			<?php if($company_config['tax_editable'] == 0){ 
																				$tcs_gst = $row121['sundry_amount'];
																			}else{
																				$tcs_gst = $rel['tcs'];
																			}
																			?>
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px; border-bottom: none;" colspan="<?=$colspan?>">
																				</td>
																				<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important; border-bottom: none;" >
																					TCS
																				</td>
																				<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; border-bottom: none;"><?=number_format($tcs_gst,2,".","")?></td>
																			</tr>
																		<?php }
																		$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn 
																		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
																		left join tbl_ledger as l on l.l_id=tc.tax_id 
																		where tc.tax_additional='1' and trn.invoice_id=".$rel['invoice_id']." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
																		$result11=$dbcon->query($qry11);		
																		while($row11=mysqli_fetch_assoc($result11)){ ?>
																			<!-- Added By Dhruv -->
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;border-bottom: none;" colspan="<?=$colspan?>">
																				</td>
																				<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important;border-bottom: none;" >
																					<?=$row11['l_name'];?>
																				</td>
																				<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; border-bottom: none;"><?=number_format($row11['add_sum'],2,".","")?></td>
																			</tr>
																		<?php } 
																		$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
																		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
																		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
																		where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ";
																		$result12=$dbcon->query($qry12);		
																		while($row12=mysqli_fetch_assoc($result12)){ ?>
																			<!-- Added By Dhruv -->
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;border-bottom: none;" colspan="<?=$colspan?>">
																				</td>
																				<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important;border-bottom: none;" >
																					<?=$row12['l_name'];?>
																				</td>
																				<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; border-bottom: none;"><?=number_format($row12['sundry_amount'],2,".","")?></td>
																			</tr>
																		<?php } ?>
																	<?php //if($rel['stateid']==$set_head['stateid']) 
																	if($tax_name[1]) { ?>
																		<tr height="20px">
																			<td  style="border-right:1px solid;border-top:1px solid; font-size:10px; border-bottom: none;" colspan="<?=$colspan?>">
																			</td>
																			<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important; border-bottom: none;">Add : 
																				<?php$strt=$tax_name[1];
																				$position = strpos($strt, "TCS", 0);
																				if ($position == true){ 
																					echo $tax_name[1];
																				} else{
																					echo 'SGST';	
																				}
																				?>
																			</td>
																			<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid;border-bottom: none; "><?=number_format($totaltax2,2,".","")?></td>
																		</tr>
																	<?php} $totaltax=$totaltax1+$totaltax2;?>
																	<?php 
																	$total=($total)+$rel['packing']; 
																	$r=round($total)-$total; ?>
																	<?php if($rel['formulaid']){
																		$tax_on_total = get_tax_on_total ($dbcon, $total, $rel['formulaid']);  ?>
																		<tr height="20px">
																			<td class="borderleftadd" style="border-right:1px solid;border-top:1px solid;font-size:10px; border-bottom: none;" colspan="<?=$colspan?>"></td>
																			<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important; border-bottom: none;">Add : <?= $tax_on_total['tax_name'] ?></td>
																			<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-right:1px solid;border-bottom: none; "><?=number_format($tax_on_total['tax_value'],2,".","")?></td>
																		</tr>
																		<?php $total = $total + $tax_on_total['tax_value']; } 
																		$round = round($rel['g_total']) - $rel['g_total']; ?>
																		<tr height="20px">
																			<td style="border-right:1px solid;border-top:1px solid;font-size:12px; border-bottom: none; font-size:10px;" colspan="<?=$colspan?>">
																				Beneficiary Name : 
																			</td>
																			<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important; border-bottom: none;">Round Off</td>
																			<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; border-bottom: none;"><?= $rel['round_off']; ?></td>
																		</tr>
																		<tr height="20px">
																			<td style="border-right:1px solid;border-top:1px solid;" colspan="<?=$colspan?>">
																				<strong>Amount in words: <?=ucwords(convert_number_to_words_new(round($rel['g_total'])))?></strong>
																			</td>
																			<td colspan="3" style="border-top:1px solid;border-left:1px solid;text-align:left  !important">
																				<strong>Invoice value</strong> :
																			</td>
																			<td colspan="2" style="text-align:right  !important; border-top:1px solid;border-right:1px solid; ">
																				<strong><?=number_format($rel['g_total'],0,".","").'.00'?></strong>
																			</td>
																		</tr>
																		<tr>
																			<td colspan="<?=5+$colspan?>" style="border:1px solid;border-left:none;border-right:none;">
																				<?phpif(!empty($set_head['conditions'])){ ?>
																					<strong>Terms and Conditions:</strong><br> <?=$set_head['conditions']?>
																				<?php} ?>
																			</td>
																		</tr>
																		<tr>
																			<td colspan="5" style="border:1px solid; text-align: center;">
																				<strong>ELECTRONIC REFERNCE NUMBER</strong>
																			</td>
																			<td colspan="<?=$colspan?>" style="border-left:1px solid;border-right:1px solid; text-align: right;">
																				For, <strong><?=$set_head['company_name']?></strong>
																			</td>
																		</tr>
																		<tr>
																			<td colspan="5" style="border-right:1px solid; text-align: center;">
																			</td>
																			<td colspan="<?=$colspan?>" style=" border-left:none;vertical-align:top; text-align: right;">
																				<?if($set_head['authorized_signature']!=""){?>
																					<img src="<?=DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'];?>" style="height: 100px; width: 100px;"><br>
																				<?php}else{ ?>
																					<br><br><br>
																					<?}?>
																					<span style="vertical-align:bottom;">Authorised Signatory</span>

																				</td>

																			</tr>
																		</table>
																	</td>
																</tr>		
															</tbody>
			</table>
				<table width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%;"/>
						</td>
					</tr>
				</table>
				
			<!-- Multipage Table End -->		

			<center><span style="float:left;"></span>This is a Computer Generated Invoice</center>
		</div>
		<div id="print2" style="margin-top:0in;"></div>
		<div id="print3" style="margin-top:0in;"></div>

	</div>
	<?php  
	$contents = ob_get_contents();
	$_SESSION['contents']=$contents;
	$_SESSION['file_name']='invoice-#';
	$_SESSION['invoice_no']=$rel['invoice_no'];
	$_SESSION['page_size']='A4';
	echo "<script> function make_pdf()
	{ window.open('".ROOT."export/print','_blank');
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
<?php include_once('../../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
<script src="<?=ROOT.FINANCE_ROOT?>js/app/invoice.js"></script>
<!--<script src="js/count.js"></script>-->

<script type="text/javascript"> 
	function print_receipt()
	{
		var originalContents = document.body.innerHTML;
	//var duplicate = $("#invoiceprint").clone().prepend("<hr style='border-color:#000; border-style:solid; margin:10px 0' />").appendTo("#invoiceprint");
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
					$("#print"+i+" .data_title").html('ORIGINAL FOR RECIPIENT');
					$("#type").html($("#typename").val());
				}
				if(i<$('#print_status').val())
				{
					$("#print"+i).after('<div class="page"></div>');
				}
				$("#print"+(i+1)).html($("#print1").clone());
				if((i+1)==2)
				{
					$("#print"+(i+1)+" .data_title").html('DUPLICATE FOR SUPPLIER');
				}
				if((i+1)==3)
				{
					$("#print"+(i+1)+" .data_title").html('TRIPLICATE FOR TRANSPORTER');
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
 // docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
 docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
 docprint.document.write('<style type="text/css">');
 if ($('input[name=logo]:Checked').val() == "1") {
 	$('#table_head').show();
 	$('#table_foot').show();
 	docprint.document.write('@media print{ @page { size:A4; margin: 0.2in <?=$set_head['letter_head_right_margin']?>in 0.2in <?=$set_head['letter_head_left_margin']?>in; } } ');
 }
 else
 {
 	docprint.document.write('@media print{ @page { size:A4; margin: <?=$set_head['letter_head_top_margin']?>in <?=$set_head['letter_head_right_margin']?>in <?=$set_head['letter_head_bottom_margin']?>in <?=$set_head['letter_head_left_margin']?>in; } }  #table_head, #table_foot ,#texttype { display:none }');
		//$('#invoice_type').css('margin-top','1.7in');	
	}

	docprint.document.write('body { font-family:Tahoma;color:#000;font-size:10px;}.breakout table td,.breakout table th {padding: 2px !important;text-align: inherit !important;}');
	docprint.document.write('.breakout table td,.breakout table th {padding: 2px !important;text-align: inherit !important;}a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } tr { page-break-inside: avoid } .maintable tbody tr { border-bottom:0.5px #ccc solid; }');
	docprint.document.write('.breakout table td,.breakout table th {padding: 2px !important;text-align: inherit !important;} .maintable table { page-break-inside:auto } .maintable tr{ page-break-inside:avoid; page-break-after:auto } .maintable thead { display:table-header-group }  .maintable tfoot tr{ /*display:table-footer-group;*/ page-break-inside:avoid; page-break-before:always; } footer-table{ page-break-inside:avoid; page-break-before:always;  } #table_foot{position:fixed;bottom:0} #rawnone{border:none;}</style>');
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