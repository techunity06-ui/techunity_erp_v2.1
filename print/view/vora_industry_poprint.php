<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';
$_SESSION['contents']=''; 
$form="Purchase Order";
$mode="Print";
$purchaseorder_id=$dbcon->real_escape_string($_REQUEST['id']);
$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile, l.stateid, state.gst_state_code, city.city_name, bmast.branch_address, city1.city_name as bcity, state1.state_name as bstate, country1.country_name as bcountry, comp.company_name, le.l_name as con_ven, bm.branch_name as cons_bran from tbl_purchaseorder as po 
left join tbl_ledger as l on l.l_id=po.vender_id
left join country_mst as country on country.countryid=l.countryid
left join pay_terms as payterms on payterms.terms_id=po.payment_terms
left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
left join state_mst as state on state.stateid=l.stateid
left join city_mst as city on city.cityid=l.cityid
left join branch_mst as bmast on bmast.branch_id=po.branch_id
left join country_mst as country1 on country1.countryid=bmast.countryid
left join state_mst as state1 on state1.stateid=bmast.stateid
left join city_mst as city1 on city1.cityid=bmast.cityid
left join tbl_company as comp on comp.company_id = po.company_id
left join tbl_ledger as le on le.l_id = po.con_vender_id
left join branch_mst as bm on bm.branch_id = po.con_branch
where po.purchaseorder_id=$purchaseorder_id";
//echo $query;
$rel=mysqli_fetch_assoc($dbcon->query($query));
$_SESSION['invoice_no']=$rel['invoice_no'];		

if(!empty($rel['branch_address'])){
	$baddress=$rel['branch_address'];
}
/*$cons_city_name1=$rel['bcity'];
$cons_state_name1=$rel['bstate'];
$cons_country_name1=$rel['bcountry'];*/

$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
$set_head=mysqli_fetch_assoc($dbcon->query($set));	
$order_date='';
if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
{
	$order_date=date('d-m-Y',strtotime($rel['order_date']));
}

if($rel['cons_same_as']==0){
	if($rel['con_type'] ==1){
		$cons_name = $rel['cons_bran'];
	}else if($rel['con_type'] ==2){
		$cons_name = $rel['con_ven'];
	}else{
		$cons_name = $rel['company_name'];
	}

	$consignee_address = $rel['con_address'];
	$party_address_con = '<strong>'.$cons_name.'</strong><br>'.$consignee_address;
}else{
	$cons_name 		   = $rel['company_name'];
	$consignee_address = $set_head['address'];
	$party_address_con = '<strong>'.$cons_name.'</strong><br>'.$consignee_address;
}

$cons_company_name	= $rel['company_name'];
$cons_cust_address	= $rel['cust_address'];
$cons_gst_no		= $rel['gst_no'];
$cons_state_name	= $rel['state_name'];
$cons_gst_state_code= $rel['gst_state_code'];
$cons_city_name		= $rel['city_name'];
$cons_country_name	= $rel['country_name'];

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
		//consignee
/*if(!empty($rel['consignee_id']))
{	
	$consignee="select * from tbl_custmer_consignee as cust 
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid 
	left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
	$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
	$cons_company_name=$cons_data['company_name'];
	$cons_cust_address=$cons_data['cust_address'];
	$cons_gst_no=$cons_data['gst_no'];
	$cons_state_name=$cons_data['state_name'];
	$cons_gst_state_code=$cons_data['gst_state_code'];
	$cons_city_name=$cons_data['city_name'];
	$cons_country_name=$cons_data['country_name'];

}*/

/* Check Discount is On or off Start */
if($set_head['show_disc']=='1'){
	$colspan=5;
	$dynamicwidth=40;
}else{
	$colspan=6;
	$dynamicwidth=46;
}
/* Check Discount is On or off End */

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once($include.'/include_css_file.php');?>
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
		td, th {
			padding: 0px 5px !important;
		}
	</style>
</head>
<body>
	<section id="container" >
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
								<h3><?=$mode.' '.$form?></h3>
							</header>	
							<div class="">
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
				<div class="row">			
					<div class="col-sm-12">
						<section class="panel">

							<div class="panel-body">
								<!--<center>-->
									<?php 
									if($rel['po_approval_status']=='1'){
										?>
										<div id="logo_sec_div">
											<div class="col-md-4"> </div>With Logo
											<br/>
											<label class="col-md-4 control-label"> </label>
											<div class="col-md-4 col-xs-11" style="display:none;">
												<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
													<select class="form-control" name="print_status" id="print_status" <?php if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
														<option value="">Select Print</option>
														<option value="1" <?php if($_REQUEST['printstatus']=='1'){ echo "selected";}?> selected>ORIGINAL</option>
														<option value="2" <?php if($_REQUEST['printstatus']=='2'){ echo "selected";}?>>DUPLICATE</option>
														<option value="3" <?php if($_REQUEST['printstatus']=='3'){ echo "selected";}?>>TRIPLICATE</option>
														<option value="4" <?php if($_REQUEST['printstatus']=='4'){ echo "selected";}?>>EXTRA</option>
													</select>
												</form>
											</div>
											<div class="col-md-1">
												<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
											</div>
											<div class="col-md-4">
												<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>

												<a href="<?=ROOT.PURCHASE_ROOT.'po_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
												<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
											</div>
											<!--</center>	-->	
										</div>	
										<?php 
									}
									else{
										?>	
										<center>
											<button type="submit" class="btn btn-warning"><i class="fa fa-ban"></i> PO Not Approved</button>
										</center>
										<?php 
									}
									?>
									<div class="col-md-12"></div>
									<label class="col-md-3 control-label"></label>
									<div class="col-lg-4">
									</div>
									<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
									<?php ob_start(); ?>
									<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
										<!-- Fixed Logo Table Start -->
										<table width="100%" class="maintable" border="1" id="table_head">
											<tr style="border:none;">
												<td width="100%" style="border:none;"> 
													<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>
				<!--<h2 align="center" style="font-weight:600;"><u><?=$set_head['company_name']?></u></h2>
				<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX;  !important"><?=$set_head['logo_content']?></h4>
				<h4 align="center" style="padding:top:15px;margin-top: 10PX;margin-bottom: 0PX; font-weight:lighter; !important"><?=$set_head['address']?></h4>
				<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX; font-weight:lighter; !important"><?php if($set_head['website']){?><?php }?> 
				<?php if($set_head['contact_no']){?>Contact No. <?=$set_head['contact_no']?><?php }?></h4>
				<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX; font-weight:lighter; !important"><?php if($set_head['website']){?><?php }?> 
				<?php if($set_head['website']){?>E-Mail: <?=$set_head['website']?><?php }?></h4>-->
				
			</td>
		</tr>
	</table>
	<!-- Fixed Logo Table End -->
	<!-- Multipage Table Start -->	
	<table width="100%" class="maintable" style="font-size: 12px;margin-top: 5px;" id="invoice_type" >
		<thead>
			<tr>
				<th colspan="11" style="padding:0px !important;">
					<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >


						<tr>
							<td colspan="4" style="text-align:center;width:100%;"> 
								<strong style="font-size:14px;">
									<?=$form?>
								</strong>
							</td>
						</tr>
						<tr>
							<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;">
								<strong>Purchase Order No </strong>
							</td>
							<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;">
								<strong>: <?=$rel['purchaseorder_no']?></strong>
							</td>
							<td width="25%" style="vertical-align:top;border:1px solid;border-right:none;">
								<strong>Payment Terms</strong>
							</td>
							<td width="25%" style="vertical-align:top;border:1px solid;border-left:none;">
								<strong>: <?=$rel['payment_term']?></strong>
							</td>
						</tr>
						<tr>
							<td style="vertical-align:top;border:1px solid;border-right:none;">
								<strong>Purchase Order Date  </strong>
							</td>
							<td style="vertical-align:top;border:1px solid;border-left:none;">
								<strong>: <?=date('d-m-Y',strtotime($rel['purchaseorder_date']))?></strong>
							</td>
							<td style="vertical-align:top;border:1px solid;border-right:none;">
								<strong>Mode of Dispatch </strong>
							</td>
							<td style="vertical-align:top;border:1px solid;border-left:none;">
								<strong>: <?=$rel['mode_dispatch']?></strong>
							</td>
						</tr>
						<tr>
							<td width="50%" colspan="2" style="vertical-align:top;border:1px solid;">
								<b>To, </b><br/>
								<strong><?=$rel['vender_name']?></strong>
								<span style="font-weight:normal;">   <br/>
									<?=$rel['vender_address']?>
									<br/>
									<?=$rel['city_name']?>, <?=$rel['state_name']?>, <?=$rel['country_name']?></span>
									<br/>Vendor GST No. : <?=$rel['tin_no']?>
								</td>
								<td width="50%" colspan="2" style="vertical-align:top;border:1px solid;">
									<?php // if(!empty($baddress)){ ?>
										<b>Ship To, </b><br/>
										<strong><?=$party_address_con?></strong>
										 <!-- <span style="font-weight:normal;">   <br/> 
											<?=$baddress?>
											<br/>
											<?=$cons_city_name1?>, <?=$cons_state_name1?>, <?=$cons_country_name1?></span>
											<?php//<br/>GST No. : <?=$cons_gst_no?> -->
										<?php // } ?> 
									</td>
								</tr>
							</table>


						</th>
					</tr>
					<tr>
						<th width="3%" style="text-align:center;border:1px solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
						<th width="<?=$dynamicwidth?>%" style="text-align:center;border:1px solid;border-top: none;" colspan="2">
							<strong>Particulars </strong>
						</th>
						<th width="8%" style="text-align:center;border:1px solid;border-top: none;">
							<strong>HSN/SAC <br/>Code</strong>
						</th>
						<th width="7%" style="text-align:center;border:1px solid;border-top: none;">
							<strong>QTY.</strong>
						</th>
			<!--<th width="7%" style="text-align:center;border:1px solid;border-top: none;">
				<strong>Sqr/</br>Ft</strong>
			</th>-->
			<th width="7%" style="text-align:center;border:1px solid;border-top: none;">
				<strong>Rate</strong>
			</th>
			<?phpif($set_head['show_disc']=='1'){ ?>
				<th width="6%" style="text-align:center;border:1px solid;border-top: none;">
					<strong>Less:<br/>Disc.</strong>
				</th>
				<?php }?>
				<th width="9%" style="text-align:center;border:1px solid;border-top: none;">
					<strong>Amount</strong>
				</th>
				<th width="4%" style="text-align:center;border:1px solid;border-top: none;">
					<strong>Rate</strong>
				</th>
				<th width="6%" style="text-align:center;border:1px solid;border-top: none;">
					<strong>Tax<br/>Value</strong>
				</th>
				<th width="10%" style="text-align:center;border:1px solid;border-top: none;">
					<strong>Total</strong>
				</th>
			</tr>
		</thead>
		<tbody style="border: 1px solid;">
			<?php
			$qry="select trn.*,product.*,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name FROM `tbl_purchaseordertrn` as trn 
			left join product_mst as product on product.product_id=trn.product_id 
			left join unit_mst as per on per.unitid=trn.unit_id 
			left join unit_mst as per1 on per1.unitid=product.product_base_unit 
			left join unit_mst as per2 on per2.unitid=product.product_conv_unit 
			left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid 
			left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
			where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";
// 			echo $qry;
			$result=$dbcon->query($qry);		
			$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
			$cnt=mysqli_num_rows($result);
			while($row=mysqli_fetch_assoc($result))
			{
				if($row['product_base_unit']!=$row['product_conv_unit']){
					//base_unit_name,per2.unit_name as conv_unit_name
					if($row['unit_id']==$row['product_base_unit']){
						$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"conv_unit");
						$uname=$row['conv_unit_name'];
					}else{
						$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"base_unit");
						$uname=$row['base_unit_name'];
					}
				}
				$gst_per = $row['cgst_tax_per']+$row['sgst_tax_per']+$row['igst_tax_per'];
				$gst_rate = $row['cgst_tax_rate']+$row['sgst_tax_rate']+$row['igst_tax_rate'];

				if($row['cgst_tax_rate'] != 0 || $row['sgst_tax_rate'] !=0){
					$total_cs_gst += $gst_rate;
					$taxable_amt = $gst_rate;
				}else{
					$total_i_gst += $gst_rate;
					$taxable_amt = $gst_rate;
				}
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
				}
				// $taxable_amt=$row['total']-$row['product_amount'];
				$code="";
				?>
				<tr style="height:40px">
					<td style="text-align:center;vertical-align:top;border-right:1px solid;border-left:1px solid;">
						<?=$i?>
					</td>
					<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;" colspan="2">
						<!--<?phpif(!empty($row['scode'])){
							$code=" ( ".$row['scode'] .")";
						} ?>-->
						<?php if($row['product_alias_name']){?>
							<strong><?=stripcslashes($row['product_alias_name'])?> <?=$code?></strong>
							<br/><?=($row['product_des']) ? nl2br(stripcslashes($row['product_des'])) : '';?>
						<?php}else{ ?>
							<strong><?=stripcslashes($row['product_name'])?> <?=$code?></strong>
							<br/><?=($row['product_des']) ? nl2br(stripcslashes($row['product_des'])) : '';?>
							<?php }?>
						</td>
						<td style="border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;text-align:center" >
							<?=stripcslashes($row['product_hsn_code'])?>
						</td>

						<td style="text-align:center;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;white-space:nowrap;" >
							<?phpif($row['product_type']!='8'){ ?>
								<?php if($row['product_base_unit']!=$row['product_conv_unit']){?>
									<?=$cqty.' '.$uname?><br/>
									<?php }?>
									<?=$row['product_qty'].' '.$row['unit_name']?>
								<?php}else{
									$charges_qty+=$row['product_qty'];
								} ?>	
							</td>
					<!--<td style="text-align:center;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;white-space:nowrap;" >
						<?phpif($row['product_type']!='3'){ ?>
							<?=$row['sqr_ft']?>
						<?php}else{
							$charges_qty1+=$row['sqr_ft'];
						} ?>	
					</td>-->
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
						<?=number_format($row['product_rate'],2,".","")?>
					</td>
					<?phpif($set_head['show_disc']=='1'){?>
						<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
							<?=number_format($row['discount_per'],2,".","").'%'?>
						</td>
						<?php }?>
						<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
							<?=number_format($row['product_amount'],2,".","")?>
						</td>
						<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
							<!--<?=$tax_arr[0]+$tax_arr[1]?>%-->
							<?=$gst_per?>%

						</td>
						<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
							<?=number_format($taxable_amt,2,".","")?>
							<?php //=number_format($row['product_amount_tax'],2,".","")?>
						</td>

						<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid;">
							<?=number_format($row['total'],2,".","")?>
						</td>
					</tr>

					<?php
					$i++; 
					$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
					$total_product_amount+=$row['product_amount'];
					$totaltaxable+=$row['product_amount_tax'];
					$total+=$row['total'];
				}
				$pr=10-$cnt;

				for($j=0; $j<$pr; $j++)
				{
					?>	
					<tr style="height:40px">
						<td style="border-right:1px solid;border-left:1px solid;"></td>
						<td style="border-right:1px solid;" colspan="2"></td>
						<td style="border-right:1px solid;"></td>
						<?phpif($set_head['show_disc']=='1'){?>
							<td style="border-right:1px solid;"></td>
							<?php }?>
							<!--	<td style="border-right:1px solid;"></td>-->
							<td style="border-right:1px solid;"></td>
							<td style="border-right:1px solid;"></Td>
								<td style="border-right:1px solid;"></td>
								<td style="border-right:1px solid;"></td>
								<td style="border-right:1px solid;"></td>
								<td style="border-right:1px solid;"></td>
							</tr>

						<?php } ?>
						<tr style="height:20px">
							<td style="border-top:1px solid;border-right:1px solid;border-left:1px solid; text-align:right;" colspan="4"><strong>Total</strong></td>

							<td style="text-align:center;border-top:1px solid;border-right:1px solid;"><strong><?=number_format($totalqty,2,".","")?></strong></td>
							<!--<td style="text-align:center;border-top:1px solid;border-right:1px solid;"><strong><?=number_format($totalsqr,2,".","")?></strong></td>-->
							<?phpif($set_head['show_disc']=='1'){?>
								<td style="border-top:1px solid;border-right:1px solid;"></td>
								<?php }?>
								<td style="border-top:1px solid;border-right:1px solid;"></td>
								<td style="border-top:1px solid;border-right:1px solid;text-align:right;"><strong><?=number_format($total_product_amount,2,".","")?></strong></td>
								<td style="border-top:1px solid;border-right:1px solid;text-align:right;"></td>
								<td style="border-top:1px solid;border-right:1px solid;text-align:right;"><strong><?=number_format($taxable_amt,2,".","")?></strong></td>

								<td style="border-top:1px solid;border-right:1px solid;text-align:right;"><strong><?=number_format($total,2,".","")?></strong></td>

							</tr>		
							<?php if($rel['stateid']==$set_head['stateid']){ ?>
								<tr height="20px">
									<td  style="border-right:1px solid;border-top:1px solid; font-size:12px;" colspan="6">COMPANY GST No. : <?=$set_head['vatno']?>
								</td>
								<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left  !important" >
									CGST
								</td>
								<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:12px;border-right:1px solid; "><?=number_format(($total_cs_gst/2),2,".","")?></td>
							</tr>
							<tr height="20px">
								<td  style="border-right:1px solid;border-top:1px solid; font-size:12px;" colspan="6">
								</td>
								<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left  !important" >
									SGST
								</td>
								<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:12px;border-right:1px solid; "><?=number_format(($total_cs_gst/2),2,".","")?></td>
							</tr>
						<?php }else{ ?>
							<tr height="20px">
								<td  style="border-right:1px solid;border-top:1px solid; font-size:12px;" colspan="6">COMPANY GST No. : <?=$set_head['vatno']?>
							</td>
							<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left  !important" >
								IGST
							</td>
							<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:12px;border-right:1px solid; "><?=number_format($total_i_gst,2,".","")?></td>
						</tr>

					<?php }
					$qry11="select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_purchaseordertrn as trn 
					left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
					left join tbl_ledger as l on l.l_id=tc.tax_id 
					where tc.tax_additional='1' and trn.purchaseorder_id=".$rel['purchaseorder_id']." and trn.purchaseordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
					$result11=$dbcon->query($qry11);		
					while($row11=mysqli_fetch_assoc($result11)) { ?>
						<!-- Added By Dhruv -->
						<tr height="20px">
							<td  style="border-right:1px solid;border-top:1px solid; font-size:12px;" colspan="6">
							</td>
							<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left  !important" >
								<?=$row11['l_name'];?>
							</td>
							<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:12px;border-right:1px solid; "><?=number_format($row11['add_sum'],2,".","")?></td>
						</tr>

					<?php } 

					$qry12="select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
					from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
					left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
					where b.sundry_voucher_id=".$rel['purchaseorder_id']." and b.sundry_voucher_table='tbl_purchaseorder' and b.isdelete='0' and le.default_sundry='0'";

					$result12=$dbcon->query($qry12);		
					while($row12=mysqli_fetch_assoc($result12)) { ?>
						<!-- Added By Dhruv -->
						<tr height="20px">
							<td  style="border-right:1px solid;border-top:1px solid; font-size:12px;" colspan="6">
							</td>
							<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left  !important" >
								<?=$row12['l_name'];?>
							</td>
							<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:12px;border-right:1px solid; "><?=number_format($row12['sundry_amount'],2,".","")?></td>
						</tr>

					<?php } 
					$colspan = 6;
					if($tax_name[1]){ ?>
						<tr height="20px">
							<td  style="border-right:1px solid;border-top:1px solid; font-size:12px;" colspan="<?=$colspan?>">
							</td>
							<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left  !important">Add : 
								<?php

								$strt=$tax_name[1];
								$position = strpos($strt, "TCS", 0);
								if ($position == true){ 
									echo $tax_name[1];
								} else{
									echo 'SGST';	
								}
								?></td>
								<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:12px;border-right:1px solid; "><?=number_format($totaltax2,2,".","")?></td>
							</tr>
						<?php} $totaltax=$totaltax1+$totaltax2;?>
						<?php 
						$total=($total)+$rel['packing']; 
						$r=round($total)-$total; ?>
						<tr height="20px">
							<td style="border-right:1px solid;border-top:1px solid;font-size:12px;" colspan="<?=$colspan?>">
								<strong>Rupees:</strong>
								<?=ucwords(convert_number_to_words($rel['g_total']))?>
							</td>
							<td colspan="3" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left  !important">
								<strong>Grand Total</strong> :
							</td>
							<td colspan="2" style="text-align:right  !important; border-top:1px solid;font-size:12px;border-right:1px solid; ">
								<strong><?=number_format($rel['g_total'],0,".","").'.00'?></strong>
							</td>
						</tr>
						<tr height="35px">
							<td colspan="<?=5+$colspan?>" style="border:1px solid;border-left:none;">
								Remark:<?=($rel['remark']) ? $rel['remark'] : ''?>
							</td>
						</tr>	
						<tr>
							<td colspan="<?=$colspan?>" style="vertical-align:top;font-size:10px;text-align:left;border-top:1px solid;" class="con">							
								<strong>Terms and Conditions:</strong><br> <?=$rel['po_condition']?>
							</td>
							<td colspan="5" style="vertical-align:top;border-top:1px solid;">
								<center>
									For, <strong> <span style="font-size:11px;text-decoration:bold;">
										<?=$set_head['company_name']?></span></strong>

									</center>
									<br><br>
									<center><img src="<?=DOMAIN_F.'view/upload/signature/'.$userData['authorized_signature'];?>"  style="width:100%;height:60px;margin-top: -20px;"/></center>
									<center style="vertical-align:bottom;">Authorised Signatory</center>

								</td>

							</tr>
						</table>
					</td>
				</tr>		
			</tbody>
			<!--<table width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%"/>
						</td>
					</tr>
				</table>-->

			</table>
			<!-- Multipage Table End -->		
		<!--<table width="100%" border="0" style="" id="table_foot">
	<tr>
	<td colspan="7"  style="border-left:none;border-top:none;border-bottom:none;padding-left:0px;"> 
	<img src="<?=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%"/>
	
	</td>
	</tr>
</table>	-->			
<!--<center><span style="float:left;">E.& O. E.</span>This is a Computer Generated Invoice</center>-->
</div>
<div id="print2" style="margin-top:0in;"></div>
<div id="print3" style="margin-top:0in;"></div>

</div>
<?php  
$contents = ob_get_contents();
$_SESSION['contents']=$contents;
$_SESSION['file_name']='invoice-#';
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
<?php include_once($include.'/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once($include.'/include_js_file.php');?>   

<script type="text/javascript"> 
	function print_receipt()
	{
		var originalContents = document.body.innerHTML;
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
		//$("#print"+i+" .data_title").html('Performance');
		$("#type").html("Performance Invoice");
	}
	if($("#invoice").val()==1)
	{
		//$("#print"+i+" .data_title").html('ORIGINAL FOR RECIPIENT');
		$("#type").html($("#typename").val());
	}
	if(i<$('#print_status').val())
	{
		$("#print"+i).after('<div class="page"></div>');
	}
	$("#print"+(i+1)).html($("#print1").clone());
	if((i+1)==2)
	{
		//$("#print"+(i+1)+" .data_title").html('DUPLICATE FOR SUPPLIER');
	}
	if((i+1)==3)
	{
		//$("#print"+(i+1)+" .data_title").html('TRIPLICATE FOR TRANSPORTER');
	}
	
}
}
else
{
	//$("#print1 .data_title").html('EXTRA');
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
  else
  {
  	docprint.document.write(' @media print{ @page { size:A4; margin: <?=$set_head['letter_head_top_margin']?>in <?=$set_head['letter_head_right_margin']?>in <?=$set_head['letter_head_bottom_margin']?>in <?=$set_head['letter_head_left_margin']?>in; }  }  #table_head, #table_foot { display:none }');
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
