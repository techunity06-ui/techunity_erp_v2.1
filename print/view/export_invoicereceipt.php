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
$query="select invoice.*,country.country_name,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type,cust_pincode,cust_mobile,gst_no,dispatch.mode_dispatch 
from tbl_invoice as invoice 
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

/* Check Discount is On or off Start */
if($set_head['show_disc']=='1'){
	$colspan=5;
	$dynamicwidth=40;
}else{
	$colspan=6;
	$dynamicwidth=46;
}
/* Check Discount is On or off End */
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>EXPORT INVOICE - <?=$rel['invoice_no']?></title>
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
															<select class="form-control" name="print_status" id="print_status" <?php if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
																<option value="" >Select Print</option>
																<option value="1" <?php if($_REQUEST['printstatus']=='1'){ echo "selected";}?>>ORIGINAL</option>
																<option value="2" <?php if($_REQUEST['printstatus']=='2'){ echo "selected";}?>>DUPLICATE</option>
																<option value="3" <?php if($_REQUEST['printstatus']=='3'){ echo "selected";}?>>TRIPLICATE</option>
																<option value="4" <?php if($_REQUEST['printstatus']=='4'){ echo "selected";}?>>EXTRA</option>
															</select>
														</form>
													</div>
												</div>
												<div class="col-sm-3 resclear">
													<label class=" control-label col-sm-7 " style="
													padding-top: 10px; padding:10px 0 0;">With Logo</label>
													<div class=" resclear col-sm-5">
														<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
													</div>
												</div>
												<div class="col-sm-4 resclear resspace"  style="text-align:right">
													<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
													<a href="<?=ROOT.FINANCE_ROOT.'invoice_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
												</div>
												<div class="col-sm-4 resclear resspace"  style="text-align:center;padding-top:5px;">
													<a type="button" class="btn btn-success" href="https://web.whatsapp.com/send?phone=+91<?php echo $rel['cust_mobile']?>&text=<?php echo $rel['company_name']?>%2C%0aThank you for your purchase.%0aInvoice No:-<?php echo $rel['invoice_no']?>%0aDate:-<?php echo date('d-m-Y',strtotime($rel['invoice_date']))?>%0aAmount:-<?php echo $rel['g_total']?>%0aBest Regards%0a
														<?php echo $set_head['company_name']?>" target="_blank"> <i class="fa fa-whatsapp"></i> Whatsapp</a>
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
													<!-- Fixed Logo Table End -->
													<!-- Multipage Table Start -->	
													<table width="100%" class="maintable" style=" font-size:11px" id="invoice_type" >
														<thead id="fiac">
															<tr>
																<th colspan="9" style="padding:0px !important;">
																	<table style="font-size:10px;border-collapse: collapse;border-top:none !important;" cellpadding="0" cellspacing="0" width="100%" >
																		<tr style="white-space: nowrap;">
																			<td style="font-size:13px;border-left:none !important;border-right:none !important;" colspan="4">PAN : <?=$set_head['pan_no'];?></td>
																			<td style="font-size:13px;border-left:none !important;border-right:none !important; text-align:center !important;" colspan="4"> 
																				State With Code : <?=$set_head['gst_state_code'];?> - <?=$set_head['state_name'];?>
																			</td>
																		</tr>
																		<tr style="border: 1px solid; white-space: nowrap;">
																			<td style="border: 1px solid;" colspan="5"><h3>Tax Invoice For Export</h3><u>OR SUPPLY MEANT FOR EXPORT UNDER BOND WITHOUT PAYMENT OF IGST</u></td>
																			<td colspan="3" style="border: 1px solid; font-size: 13px; vertical-align: bottom;"><strong>Triplicate For Supplier</strong></td>
																		</tr>
																		<tr style="white-space: nowrap;">
																			<td class="" style="vertical-align:top;border:1px solid; border-right:none !important;"><strong>Invoice No </strong>
																			</td>
																			<td colspan="" style="vertical-align:top;border-bottom:1px solid; border-right:1px solid;border-top:1px solid">: <strong><?=$rel['invoice_no']?></strong>
																			</td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid"><strong>Date </strong>
																			</td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;border-top:1px solid" colspan="">: <strong><?=date('d-m-Y',strtotime($rel['invoice_date']))?></strong>
																			</td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;white-space:nowrap;"><strong>Due Date</strong>	
																			</td>							
																			<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <?=date('d-m-Y',strtotime($rel['invoice_date']))?></td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;white-space:nowrap;"><strong>Payment</strong>	
																			</td>							
																			<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <?=$rel['invoice_no']//$rel['payment_trm']?></td>		
																		</tr>
																		<tr style="white-space: nowrap;">
																			<td class="" style="vertical-align:top;border-bottom:1px solid;border-left:1px solid;white-space:nowrap;"><strong>Challan No </strong></td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid" colspan="">: <?=($rel['challan_no']) ? $rel['challan_no'] : ''?> </td>
																			<td style="vertical-align:top;border-bottom:1px solid "><strong>Date </strong></td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid" colspan="">: <?=$challan_date?></td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;white-space:nowrap;"><strong>Transport</strong>	
																			</td>							
																			<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <?=$rel['trans_name']?></td>
																			<td colspan="2" style="border:1px solid;white-space:nowrap;"></td>
																		</tr>
																		<tr style="white-space: nowrap;">
																			<td class="boderremoveres" style="vertical-align:top;border-bottom:1px solid;border-left:1px solid;"><strong>Order No </strong></td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid" colspan="">: </td>
																			<td style="vertical-align:top;border-bottom:1px solid "><strong>Date </strong></td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid" colspan="">: </td>
																			<td style="vertical-align:top;border-bottom:1px solid;white-space:nowrap;"><strong>Vehicle No</strong></td>							
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">: <?=$rel['vehicle_no']?></td>
																			<td colspan="2" style="border:1px solid;white-space:nowrap;"></td>
																		</tr>
																		<tr style="white-space: nowrap;">
																			<td class="boderremoveres" style="vertical-align:top;border-bottom:1px solid;border-left:1px solid; "><strong>LR No</strong></td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid">: </td>
																			<td class="boderremoveres" style="vertical-align:top;border-bottom:1px solid "><strong>Date</strong></td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid">: </td>
																			<td style="vertical-align:top;border-bottom:1px solid;white-space:nowrap;white-space:nowrap;"><strong>Dispatch To</strong> </td>
																			<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">: <?=$place_of_supply?></td>
																			<td colspan="2" style="border:1px solid;white-space:nowrap;"></td>
																		</tr>
																		<tr style="white-space: nowrap;">
																			<td colspan="5" style="vertical-align:top;border-right:1px solid;border-left:1px solid;">
																				<b>Bill to Party : </b><br>
																				<strong><?=$rel['l_name']?></strong><br>
																				<?=$rel['m_address']?><br><?=$rel['city_name']?>, <?=$rel['state_name']?>, <?=$rel['country_name']?>
																				<?php if(!empty($rel['cust_pincode'])){ ?> - <?=$rel['cust_pincode']?><?php } ?><br>
																				<?php if($company_config['enable_hypothication']==1 && $rel['check_hypothication']!=0){ ?>
																					<strong>
																						Hypothecation with <?=get_id_detail($dbcon,'bank_mst','bankid',$rel['hypo_bank'],'bank_name')?>
																					</strong>
																				<?php } ?>
																				<br>Mobile no : <?=$rel['cust_mobile']
																				?>
																			</td>
																			<td colspan="3"  style="border-right:1px solid">
																			</td>
																		</tr>
																		<tr style="white-space: nowrap;">
																			<td colspan="5" style="border:1px solid; border-top: none;"><strong>GSTIN: <?=$rel['gst_no']?> </strong></td>
																			<td colspan="3" style="border:1px solid; border-top: none;"></td>
																		</tr>
																	</table>
																</th>
															</tr>
															<tr>
																<th width="3%" style="text-align:center;border:1px solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
																<th width="8%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>HSN/SAC <br/>Code</strong>
																</th>
																<th width="<?=$dynamicwidth?>%" style="text-align:center !important; border:1px solid;border-top: none;" >
																	<strong>Particulars </strong>
																</th>
																<th width="6%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>No Of<br/>Pack</strong>
																</th>
																<th width="7%" style="text-align:center !important;border:1px solid;border-top: none;">
																	<strong>QTY.</strong>
																</th>
																<th width="7%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>Rate</strong>
																</th>
																<th width="9%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>Taxable<br/>Value</strong>
																</th>
																<th width="4%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>GST Rate</strong>
																</th>
																<th width="10%" style="text-align:center  !important;border:1px solid;border-top: none;">
																	<strong>Total</strong>
																</th>
															</tr>
														</thead>
														<tbody style="border: none;">
															<?php                                                                                  

															$qry="select trn.*,product.*,unit_name FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product.product_type,trancation_id";

															$result=$dbcon->query($qry);		
															$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
															$cnt=mysqli_num_rows($result);
															while($row=mysqli_fetch_assoc($result))
															{
														//$tax_arr=explode(",",$row['tax_val']);
																$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
																$gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate'];

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
																		<?php if($row['product_type']!='3'){ echo $i;}?>
																	</td>
																	<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;text-align:center  !important" >
																		<?=stripcslashes($row['product_hsn_code'])?>
																	</td>
																	<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;<?php if($row['product_type']=='3'){ echo 'text-align:right !important;padding-top:5px;vertical-align:top;';}?>" >
																		<strong><?=stripcslashes($row['product_name'])?></strong>
																		<br/><?=nl2br(stripcslashes($row['description']));?>
																	</td>
																	<td style="text-align:right  !important;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
																		1
																	</td>
																	<td style="text-align:center  !important; vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;white-space:nowrap;" >
																		<?php if($row['product_type']!='3'){ ?>
																			<?=$row['product_qty'].' '.$row['unit_name']?>
																		<?php }else{
																			$charges_qty+=$row['product_qty'];
																		} ?>	
																	</td>
																	<td style="text-align:right  !important;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
																		<?=number_format($row['product_rate'],2,".","")?>
																	</td>
																	<td style="text-align:right  !important; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																		<?=number_format(($row['product_rate'] * $row['product_qty']),2,".","")?>
																	</td>
																	<td style="text-align:right  !important; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																		<?=$gst_per?>%
																	</td>
																	<td style="text-align:right  !important; vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
																		<?=number_format($row['total'],2,".","")?>
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
															}
															$pr=9-$cnt;

															for($j=0; $j<$pr; $j++) { ?>	
																<tr style="height:35px">
																	<td class="borderleftadd" style="text-align:center !important; vertical-align:top;border-right:1px solid;border-left:1px solid;">
																	</td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;<?php if($row['product_type']=='3'){ echo 'text-align:right;padding-top:5px;vertical-align:top;';}?>" >
																	</td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																	<td style="border-right:1px solid;"></td>
																</tr>
															<?php } ?>
															<tr style="height:20px">
																<td class="borderleftadd" style="border-top:1px solid;border-right:1px solid;border-left:1px solid; text-align:right  !important;" colspan="3">
																	<strong>Total</strong>
																</td>
																<td style="border-top:1px solid;border-right:1px solid;"></td>
																<td style="text-align:center  !important;border-top:1px solid;border-right:1px solid;">
																	<strong><?=number_format($totalqty,2,".","")?></strong>
																</td>
																<td style="border-top:1px solid;border-right:1px solid;"></td>
																<td style="border-top:1px solid;border-right:1px solid;text-align:right  !important;">
																	<strong><?=number_format($totaltaxable,2,".","")?></strong>
																</td>
																<td style="border-top:1px solid;border-right:1px solid;text-align:right  !important;">
																</td>
																<td style="border-top:1px solid;border-right:1px solid;text-align:right  !important;">
																	<strong><?=number_format($total,2,".","")?></strong>
																</td>
															</tr>		
															<tr>
																<td class="borderleftadd" colspan="9" style="padding: 0px !important;border:1px solid">
																	<table class="footer-table" width="100%">
																		<tr height="20px">
																			<td width="50%" style="border-right:1px solid;font-size:10px;">
																			</td>
																			<td width="25%" style="border-right:1px solid;font-size:10px;text-align:left  !important">
																				Gross Total
																			</td>
																			<td style="text-align:right  !important;font-size:10px;" width="25%"><?=number_format($totaltaxable,2,".","")?></td>	
																		</tr>
																		<?php if($rel['stateid']==$set_head['stateid'] && ($custLedgerDetails['enable_sez'] == 0)){ ?>
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;" >
																				</td>
																				<td  style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important" >
																					CGST
																				</td>
																				<td  style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; "><?=number_format(($total_cs_gst/2),2,".","")?></td>
																			</tr>
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;" >
																				</td>
																				<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important" >
																					SGST
																				</td>
																				<td  style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; "><?=number_format(($total_cs_gst/2),2,".","")?></td>
																			</tr>
																		<?php }else{ ?>
																			<tr height="20px">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;" >
																				</td>
																				<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important" >
																					IGST
																				</td>
																				<td style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; "><?=number_format($total_i_gst,2,".","")?></td>
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
																			<tr height="20px" style="white-space: nowrap;">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;" >
																				</td>
																				<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important" >
																					TCS
																				</td>
																				<td style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; "><?=number_format($row121['sundry_amount'],2,".","")?></td>
																			</tr>
																		<?php }
																		$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn 
																		left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
																		left join tbl_ledger as l on l.l_id=tc.tax_id 
																		where tc.tax_additional='1' and trn.invoice_id=".$rel['invoice_id']." and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id";
																		$result11=$dbcon->query($qry11);		
																		while($row11=mysqli_fetch_assoc($result11)){ ?>
																			<!-- Added By Dhruv -->
																			<tr height="20px" style="white-space: nowrap;">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;" >
																				</td>
																				<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important" >
																					<?=$row11['l_name'];?>
																				</td>
																				<td style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; "><?=number_format($row11['add_sum'],2,".","")?></td>
																			</tr>
																		<?php } 
																		$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
																		from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
																		left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
																		where b.sundry_voucher_id=".$rel['invoice_id']." and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ";
																		$result12=$dbcon->query($qry12);		
																		while($row12=mysqli_fetch_assoc($result12)){ ?>
																			<!-- Added By Dhruv -->
																			<tr height="20px" style="white-space: nowrap;">
																				<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;">
																				</td>
																				<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important" >
																					<?=$row12['l_name'];?>
																				</td>
																				<td style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; "><?=number_format($row12['sundry_amount'],2,".","")?></td>
																			</tr>
																		<?php } ?>
																	<?php //if($rel['stateid']==$set_head['stateid']) 
																	if($tax_name[1]) { ?>
																		<tr height="20px" style="white-space: nowrap;">
																			<td  style="border-right:1px solid;border-top:1px solid; font-size:10px;" >
																			</td>
																			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important">Add : 
																				<?php $strt=$tax_name[1];
																				$position = strpos($strt, "TCS", 0);
																				if ($position == true){ 
																					echo $tax_name[1];
																				} else{
																					echo 'SGST';	
																				}
																				?>
																			</td>
																			<td style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; "><?=number_format($totaltax2,2,".","")?></td>
																		</tr>
																	<?php } $totaltax=$totaltax1+$totaltax2;?>
																	<?php 
																	$total=($total)+$rel['packing']; 
																	$r=round($total)-$total; ?>
																	<?php if($rel['formulaid']){
																		$tax_on_total = get_tax_on_total ($dbcon, $total, $rel['formulaid']);  ?>
																		<tr height="20px" style="white-space: nowrap;">
																			<td class="borderleftadd" style="border-right:1px solid;border-top:1px solid;font-size:10px;" ></td>
																			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important">Add : <?= $tax_on_total['tax_name'] ?></td>
																			<td style="text-align:right  !important; border-top:1px solid;font-size:10px;border-right:1px solid; "><?=number_format($tax_on_total['tax_value'],2,".","")?></td>
																		</tr>
																		<?php $total = $total + $tax_on_total['tax_value']; } ?>
																		<tr height="20px" style="white-space: nowrap;">
																			<td style="border-right:1px solid;border-top:1px solid;font-size:10px;" >
																				<strong>Rupees:</strong>
																				<?=ucwords(convert_number_to_words_new($rel['g_total']))?>
																			</td>
																			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important">
																				<strong>Bill Amount</strong> :
																			</td>
																			<td style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; ">
																				<strong><?=number_format($rel['g_total'],0,".","").'.00'?></strong>
																			</td>
																		</tr>
																		<tr style="white-space: nowrap;">
																			<td style="border-right:1px solid;border-top:1px solid;font-size:10px;">
																				<strong>Bank : <?=$set_head['bank_name']?> (Branch : <?=$set_head['branch_name']?>)</strong> 
																			</td>
																			<td style="border-top:1px solid;border-right:1px solid;font-size:10px;text-align:left  !important">
																				<strong>A/C No. : <?=$set_head['ac_no']?></strong>
																			</td>
																			<td style="text-align:right  !important; border-top:1px solid;font-size:10px;border-left:1px solid; ">
																				<strong>IFSC : <?=$set_head['ifcs']?></strong>
																			</td>
																		</tr>
																		<tr>
																			<td colspan="2" style="vertical-align:top;border:1px solid;
																			border-right:none;border-left:none;border-bottom:none;font-size:10px;text-align:left  !important"  class="con">
																			<?php if(!empty($set_head['conditions'])){ ?>
																				<strong>Terms and Conditions:</strong><br> <?=$set_head['conditions']?>
																			<?php } ?>
																		</td>
																		<td style=" border-left:none;border-top:1px solid black; text-align: center;">
																			For, <strong><?=$set_head['company_name']?></strong><br>
																			<img src="<?=ROOT.'view/upload/signature/'.$set_head['authorized_signature']?>" style="height: 100px; width: 100px;"><br>
																			Authorised Signatory
																		</td>
																	</tr>
																</table>
															</td>
														</tr>		
													</tbody>
				<!--<table width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%;height:100px;"/>
						</td>
					</tr>
				</table>-->
				
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
  docprint.document.write('<head><title><?php echo TITLE;?></title>');
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