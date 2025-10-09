<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$_SESSION['contents']=''; 
	$form="Invoice";
		$mode="Print";
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select invoice.*,state.state_name,cust.stateid,state.gst_state_code,city.city_name,cust.company_name,cust.cust_address,type.invoice_type,cust_pincode,cust_mobile,gst_no,cst_no,cst_date,pan_no from tbl_invoice as invoice 
		inner join tbl_customer as cust on cust.cust_id=invoice.cust_id
		inner join state_mst as state on state.stateid=cust.stateid
		inner join city_mst as city on city.cityid=cust.cityid
		inner join tbl_invoicetype as type on type.invoicetype_id=invoice.invoicetype_id
		where invoice_id=$invoiceid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$_SESSION['invoice_no']=$rel['invoice_no'];		
		$cons_gst_no=$rel['gst_no'];
		$cons_pan_no=$rel['pan_no'];
		$cons_state_name=$rel['state_name'];
		$cons_gst_state_code=$rel['gst_state_code'];

		if($rel['cust_id']!=$rel['consignee_id'] && !empty($rel['consignee_id']))//consignee
		{	
			$consignee="select * from tbl_customer as cust left join state_mst as state on state.stateid=cust.stateid inner join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
			$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
			$cons_gst_no=$cons_data['gst_no'];
			$cons_pan_no=$cons_data['pan_no'];
			$cons_state_name=$cons_data['state_name'];
			$cons_gst_state_code=$cons_data['gst_state_code'];
		}
		$set="select * from tbl_company as comp  where company_id=".$rel['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));	
		$order_date='';$lr_date='';$dispatch_date='';
		if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
		{
			$order_date=date('d-m-Y',strtotime($rel['order_date']));
		}
		if($rel['dispatch_date']!="1970-01-01 00:00:00" && $rel['dispatch_date']!="0000-00-00 00:00:00")
		{
			$dispatch_date=date('d-m-Y h:i a',strtotime($rel['dispatch_date']));
		}
		//var_dump(date('d-m-Y h:i a',strtotime($rel['dispatch_date'])));
		if($rel['lr_date']!="1970-01-01" && $rel['lr_date']!="0000-00-00")
		{
			$lr_date=date('d-m-Y',strtotime($rel['lr_date']));
		}
		
		if($rel['cst_date']!="1970-01-01" && $rel['cst_date']!="0000-00-00")
		{
			$cst_date=date('d-m-Y',strtotime($rel['cst_date']));
		}
		//query for tax column count
		$query="SELECT SUM(
				CASE
					WHEN `tax_amount1` > 0 THEN 1 ELSE 0
				END +
				CASE
					WHEN `tax_amount2` > 0 THEN 1 ELSE 0
				END +
				CASE
					WHEN `tax_amount3` > 0 THEN 1 ELSE 0
				END)/count(product_id) as cnt
				FROM tbl_invoicetrn where invoice_id=".$rel['invoice_id']." and `trancation_status`!=2";
		$rel_col=mysqli_fetch_assoc($dbcon->query($query));
		
		(intval($rel_col['cnt'])>1?$colspan=4:(intval($rel_col['cnt'])>0?$colspan=2:$colspan=0));//colspan
		(intval($rel_col['cnt'])>0?$rowspan=2:$rowspan=0);//colspan
		
		//RESET COLSPAN AND ROWSPAN HERE 
		$colspan=2;$rowspan=2;
		
		
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
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
    padding: 0px 2px !important;
}
</style>

</head>
<body>
  <section id="container" >
      <?php include_once('../include/include_top_menu.php');?>
      <!--sidebar start-->
      <?php include_once('../include/left_menu.php');?>
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
							  <li ><a href="<?=ROOT.'invoice_list'?>">Invoice List</a></li>
							  
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
              <!--state overview start-->
		  <div class="row">			
			<div class="col-sm-8 col-md-offset-2">
				<section class="panel">
				  	
						<div class="panel-body">
<center>
			<div class="col-md-1"> </div>With Logo
						<br/>
							<label class="col-md-2 control-label"> Print</label>
				<div class="col-md-4 col-xs-11">
				 <form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
					<select class="form-control" name="print_status" id="print_status" <?if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
						<option value="">Select Print</option>
						<option value="1" <?if($_REQUEST['printstatus']=='1'){ echo "selected";}?>>ORIGINAL</option>
						<option value="2" <?if($_REQUEST['printstatus']=='2'){ echo "selected";}?>>DUPLICATE</option>
						<option value="3" <?if($_REQUEST['printstatus']=='3'){ echo "selected";}?>>TRIPLICATE</option>
						<option value="4" <?if($_REQUEST['printstatus']=='4'){ echo "selected";}?>>EXTRA</option>
					</select>
				 </form>
				</div>
				<div class="col-md-1">
					<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
				</div>
				<div class="col-md-4">
					<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
					<a href="<?=ROOT.'invoice_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
					<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
				</div>
</center>		
				
			<div class="col-md-12"></div>
									<label class="col-md-3 control-label"></label>
								<div class="col-lg-4">
										
								</div>
<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
					<?php ob_start(); ?>
							<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
							
							<table width="100%" border="0" style="" id="table_head">
							<tr>
							<td colspan="7"  style="border:none;padding-left:0px;padding-top: 15px;"> 
								<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>
							<!--<h1 align="center">Shakti Pump Sales & Services</h1>
							<h4 align="center">Authorised Service Center - Shakti Pumps (I) Ltd.
								</h4>
							<h4 align="center">58- Gopinath Ind Estate Part - 1, N.H.No - 8, Soni Ni Chali, Odhav- Ahmedabad
								</h4>
							<h4 align="center">Email: shaktipumpsservice@gmail.com (M) +91 9725022853</h4>
							-->
								
							</td>
						</tr>
							</table>
							
						<table style="font-size:10px;border-collapse: separate;border-top:none;" cellpadding="0" cellspacing="0" width="100%" id="invoice_type">
						
							<tr>
								<td style="" colspan="2"> </td>
								<td colspan="3" style="text-align:center;"> 
								<strong class="typetitle" style="font-size:14px;">
								<?=$rel['invoice_type']?>
								</strong>
								</td>
								<td  width="10%" style="text-align:right;"> 
								<strong style="font-size:10px">
									<b class="data_title">ORIGINAL</b>
								</strong>
								</td>
								
							</tr>
						
						<tr>
								<td  width="12%"  style="vertical-align:top;border:1px solid black;border-right:none;"><strong>Invoice No </strong>
								</td>
								<td  width="17%" colspan="" style="vertical-align:top;border-bottom:1px solid black; border-right:1px solid black;border-top:1px solid black">:<strong><?=$rel['invoice_no']?></strong>
								</td>
								<td width="7%" style="vertical-align:top;border-bottom:1px solid black;border-top:1px solid black"><strong>Date </strong>
								</td>
								<td width="15%" style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black;border-top:1px solid black" colspan="">: <strong><?=date('d-m-Y',strtotime($rel['invoice_date']))?></strong>
								</td>
								
								<td  width="25%" style="vertical-align:top;border-bottom:1px solid black;border-top:1px solid black"><strong>Transport Mode </strong>						
								</td>							
								<td  width="25%" style="vertical-align:top;border-bottom:1px solid black;border-top:1px solid black;border-right:1px solid black;">: <?=$rel['dispatch_doc_no']?>						
								</td>							
							</tr>
							<tr>
								<td style="vertical-align:top;border-bottom:1px solid black;border-left:1px solid black;"><strong>Challan No </strong>
								</td>
								<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black" colspan="">: <?=$rel['challan_no']?>
								</td>
								<td style="vertical-align:top;border-bottom:1px solid black "><strong>Date </strong>
								</td>
								<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black" colspan="">: <?=date('d-m-Y',strtotime($rel['challan_date']))?>
								</td>
								
								<td style="vertical-align:top;border-bottom:1px solid black"><strong>Vehicle number </strong>					
								</td>							
								<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black;">: <?=$rel['vehicle_no']?></td>
							</tr>
							<tr>
							<td style="vertical-align:top;border-bottom:1px solid black;border-left:1px solid black;"><strong>Po No </strong></td>
							<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black" colspan="">: <?=$rel['order_no']?>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid black "><strong>Date </strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black" colspan="">: <?=$order_date?>
							</td>
								
							<td style="vertical-align:top;border-bottom:1px solid black"><strong>Date and Time of Supply </strong>					
							</td>							
							<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black;">: <?=$dispatch_date?></td>
							
							</tr>
							
							
							<tr>
							<td style="vertical-align:top;border-bottom:1px solid black;border-left:1px solid black; "><strong>State</strong></td>
							<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black">: Gujarat
							<td style="vertical-align:top;border-bottom:1px solid black "><strong>Code</strong></td>
							<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black">: 24
							
							</td>
							
							<td style="vertical-align:top;border-bottom:1px solid black"><strong>Place of Supply</strong>					
							</td>							
							<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black;">: <?=$rel['destination']?></td>
							
							</tr>
							<tr>
							<td colspan="4" style="vertical-align:top;border-left:1px solid black;border-bottom:1px solid black;border-right:1px solid black;white-space:nowrap;"><strong>Reverse Charges </strong> : <?=(!empty($rel['reverse_charge'])?'Yes':'No')?></td>
							
						
							<td style="vertical-align:top;border-bottom:1px solid black"><strong>Payment Terms</strong>					
							</td>							
							<td style="vertical-align:top;border-bottom:1px solid black;border-right:1px solid black;">: <?=$rel['payment_terms']?></td>
							
							</tr>
							
							<tr>
						<td colspan="4"  width="0%" style="vertical-align:top;border-right:1px solid black;border-left:1px solid black;">
						<b>Bill to Party : </b><br>
							<strong><?=$rel['company_name']?></strong>
							  <br>
							<?=$rel['cust_address']?>
							 <br>
							 <?=$rel['city_name']?>, <?=$rel['state_name']?>
							  <?phpif(!empty($rel['cust_pincode']))
								{	?>
							 -  <?=$rel['cust_pincode']?>
								<?php} ?>
								<!--<br>
								Mobile no : <?=$rel['cust_mobile']?>-->
								
						</td>
						<?phpif(empty($rel['consignee_id'])) { ?>
						<td colspan="2"  style="border-right:1px solid black">
						<b>Shipped to Party : </b><br>
							<strong><?=$rel['company_name']?></strong>
							  <br>
							<?=$rel['cust_address']?>
							 <br>
							 <?=$rel['city_name']?>, <?=$rel['state_name']?>
								<?phpif(!empty($rel['cust_pincode']))
								{	?>
							 -  <?=$rel['cust_pincode']?>
								<?php} ?>
							<!--	<br>
								Mobile no : <?=$rel['cust_mobile']?>-->
								
						</td>
						<?php} else
						{?>
							<td colspan="2"  style="border-right:1px solid black">
							<b>Consignee : </b><br>
							<strong><?=$cons_data['company_name']?></strong>
							  <br>
							<?=$cons_data['cust_address']?>
							 <br>
							 <?=$cons_data['city_name']?>, <?=$cons_data['state_name']?>
								<?phpif(!empty($cons_data['cust_pincode']))
								{	?>
							 -  <?=$cons_data['cust_pincode']?>
								<?php} ?>
								<!--<br>
								Mobile no : <?=$cons_data['cust_mobile']?>-->
						</td>
						<?php}?>
						</tr>
						<tr>
							<td colspan="4" style="border-right:1px solid black;border-left:1px solid black;"><strong>GSTIN: <?=$rel['gst_no']?> 
							<?if($rel['pan_no']){?>, PAN No : <?=$rel['pan_no']?><?}?> </strong></td>
							<td colspan="2" style="border-right:1px solid black;"><strong>GSTIN: <?=$cons_gst_no?> 
							<?if($cons_pan_no){?>, PAN No : <?=$cons_pan_no?><?}?> 
							</strong></td>
						</tr>
						<tr>
							<td colspan="2" width="25%" style="border-left:1px solid black;border-bottom:1px solid black;">State : <?=$rel['state_name']?></td>
							<td colspan="2" width="23.5%" style="border-right:1px solid black;text-align:left;border-bottom:1px solid black;border-right:1px solid black;">Code : <?=$rel['gst_state_code']?></td>
							<td style="text-align:left;border-bottom:1px solid black;">State : <?=$cons_state_name?></td>
							<td style="text-align:left;border-bottom:1px solid black;border-right:1px solid black;">Code : <?=$cons_gst_state_code?></td>
						</tr>
				</table>
						
					<table style="font-size:10px;border-collapse:separate;margin-top:10px;" width="100%">
					<tr>					
						<td rowspan="<?=$rowspan?>"  width="4%" style="text-align:center;border-right: 1px solid #000; border: 1px solid #000;"><strong>SR.<br/> NO.</strong></td>
						<td rowspan="<?=$rowspan?>" width="30%"  style="text-align:center;border-right: 1px solid #000; border-bottom: 1px solid #000;border-top: 1px solid #000;" >
							<strong>Particulars </strong>
						</td>
						<td rowspan="<?=$rowspan?>" width="6%" style="text-align:center;border-right: 1px solid #000; border-bottom: 1px solid #000;border-top: 1px solid #000;">
							<strong>HSN <br/>Code</strong>
						</td>
						
						
						<td rowspan="<?=$rowspan?>" width="7%" style="text-align:center;border-right: 1px solid #000; border-bottom: 1px solid #000;border-top: 1px solid #000;">
							<strong>QTY.
								</strong>
						</td>
						<td rowspan="<?=$rowspan?>" width="7%" style="text-align:center;border-right: 1px solid #000; border-bottom: 1px solid #000;border-top: 1px solid #000;">
							<strong>Rate</strong>
						</td>
						<!--<td rowspan="<?=$rowspan?>" width="8%" style="text-align:center; border-bottom: 1px solid #000;border-top: 1px solid #000;border-right: 1px solid #000;"  >
							<strong>Amount</strong>
						</td>-->
						<td rowspan="<?=$rowspan?>" width="6%" style="text-align:center; border-bottom: 1px solid #000;border-top: 1px solid #000;border-right: 1px solid #000;"  >
							<strong>Less:<br/>Disc.</strong>
						</td>
						<td rowspan="<?=$rowspan?>" width="8%" style="text-align:center; border-bottom: 1px solid #000;border-top: 1px solid #000;border-right: 1px solid #000;"  >
							<strong>Taxable<br/>Value</strong>
						</td>
						<td colspan="2" style="text-align:center;border-right: 1px solid #000; border-bottom: 1px solid #000;border-top: 1px solid #000;"  >
							<strong>GST</strong>
						</td>
						<td rowspan="<?=$rowspan?>" width="7%" style="text-align:center; border-bottom: 1px solid #000;border-top: 1px solid #000;border-right: 1px solid #000;"  >
							<strong>Total</strong>
						</td>
						</tr>
						<tr>
							<td width="4%" style="text-align:center; border-bottom: 1px solid #000;border-right: 1px solid #000;"  >
								<strong>Rate</strong>
							</td>
							<td width="6%" style="text-align:center;border-right: 1px solid #000; border-bottom: 1px solid #000;"  >
								<strong>Amount</strong>
							</td>
						
						</tr>
						
				<?php
				$qry="select trn.*,product.*,unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name FROM `tbl_invoicetrn` as trn left join tbl_product as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id 
				left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
				where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id";
				$result=$dbcon->query($qry);		
				$i=1;$total=0;$discount=0;$totalqty=0;
				$cnt=mysqli_num_rows($result);
				 while($row=mysqli_fetch_assoc($result))
				{
					$tax_arr=explode(",",$row['tax_val']);
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
					}
					//tax summary calculation end
				?>
					<tr style="">
					<td style="text-align:center;vertical-align:top;border-right:1px solid #000;border-left:1px solid #000;">
							<?=$i?>
					</td>
					<td style="border-bottom-color:#FFFFFF; border-right:1px solid #000;" >
					<?
						echo stripcslashes($row['product_name']).' <br/> '.stripcslashes($row['description']);
						
					?>
					</td>
					<td style="border-bottom-color:#FFFFFF; border-right:1px solid #000;vertical-align:top;text-align:center" >
					<?=stripcslashes($row['product_hsn_code'])?>
					</td>
					
					<td style="text-align:center;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid #000;white-space:nowrap;" >
						<?=$row['product_qty'].' '.$row['unit_name']?>
				
					</td>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid #000;" >
						<?=number_format($row['product_rate'],2,".","")?>
					</td>
					<!--<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid #000;">
						<?=number_format($row['product_qty']*$row['product_rate'],2,".","")?>
					</td>-->
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid #000;">
						<?=number_format($row['discount_per'],2,".","").'%'?>
					</td>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid #000;">
						<?=number_format($row['product_amount'],2,".","")?>
					</td>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid #000;">
						<?=$tax_arr[0]+$tax_arr[1]?>%
					</td>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid #000;">
						<?=number_format($row['tax_amount1']+$row['tax_amount2'],2,".","")?>
					</td>
					
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:1px solid #000;">
						<?=number_format($row['total'],2,".","")?>
					</td>
					</tr>
				<?php$i++; 
						$totalqty=$totalqty+$row['product_qty'];
						$total_product_amount+=($row['product_qty']*$row['product_rate']);
						$totaltaxable+=$row['product_amount'];
						$totaltax1+=$row['tax_amount1'];
						$totaltax2+=$row['tax_amount2'];
						$total+=$row['total'];
					}
					$pr=12-$cnt;
					
					for($j=0; $j<$pr; $j++)
					{
					?>
					<tr style="height:24px">
									<td style="border-right:1px solid #000;border-left:1px solid #000;"></td>
									<td style="border-right:1px solid #000;"></td>
									<!--<td style="border-right:1px solid #000;"></td>-->
									<td style="border-right:1px solid #000;"></td>
									<td style="border-right:1px solid #000;"></td>
									<td style="border-right:1px solid #000;"></td>
									<td style="border-right:1px solid #000;"></Td>
									<td style="border-right:1px solid #000;"></td>
									<td style="border-right:1px solid #000;"></td>
									<td style="border-right:1px solid #000;"></td>
									<td style="border-right:1px solid #000;"></td>
									
									
					</tr>
				<?php} 
				?>
					<tr style="height:20px">
									<td style="border-top:1px solid #000;border-right:1px solid #000;border-left:1px solid #000; text-align:right;" colspan="3"><strong>Total</strong></td>
									
									<td style="text-align:center;border-top:1px solid #000;border-right:1px solid #000;"><strong><?=number_format($totalqty,2,".","")?></strong></Td>
									<td style="border-top:1px solid #000;border-right:1px solid #000;"></Td>
									<!--<td style="border-top:1px solid #000;border-right:1px solid #000;text-align:right;"><strong><?=number_format($total_product_amount,2,".","")?></strong></td>-->
									<td style="border-top:1px solid #000;border-right:1px solid #000;"></td>
									<td style="border-top:1px solid #000;border-right:1px solid #000;text-align:right;"><strong><?=number_format($totaltaxable,2,".","")?></strong></td>
									<td style="border-top:1px solid #000;border-right:1px solid #000;text-align:right;"></Td>
									<td style="border-top:1px solid #000;border-right:1px solid #000;text-align:right;"><strong><?=number_format($totaltax1+$totaltax2,2,".","")?></strong></td>
									
									<td style="border-top:1px solid #000;border-right:1px solid #000;text-align:right;"><strong><?=number_format($total,2,".","")?></strong></td>
									
									
					</tr>
					<?php$total-=$rel['discount'];?>
					<tr height="20px">
							<td style="border-right:1px solid #000;border-top:1px solid #000;border-left:1px solid #000; font-size:10px;" colspan="<?=4+$colspan?>">
							<?phpif(!empty($set_head['bank_name'])){?>
									<strong>Bank Name:</strong> <?=$set_head['bank_name']?>, 
									<?php} ?>
								<?phpif(!empty($set_head['ac_no'])){?>
									<strong>A/c No:</strong> <?=$set_head['ac_no']?>	 
									<?php} ?>
							</td>
						<td colspan="3" style="border-top:1px solid #000;border-right:1px solid #000;font-size:10px;text-align:left">
							Taxable Amount
						</td>
						<td colspan="2" style="text-align:right; border-top:1px solid #000;font-size:10px;border-right:1px solid #000; "><?=number_format($totaltaxable,2,".","")?></td>	
					</tr>
					<tr height="20px">
						<td style="border-right:1px solid #000;border-top:1px solid #000;border-left:1px solid #000; font-size:10px;" colspan="<?=4+$colspan?>">
							<?phpif(!empty($set_head['ifcs'])){ ?>
									<strong>IFCS:</strong><?=$set_head['ifcs']?>,
								<?php} ?>	
								<?phpif(!empty($set_head['branch_name'])){ ?>
									<strong>Branch :</strong> <?=$set_head['branch_name']?><?php} ?>
						</td>
						<?phpif(intval($rel_col['cnt'])>0) { ?>
						<td colspan="3" style="border-top:1px solid #000;border-right:1px solid #000;font-size:10px;text-align:left" >
						
						<?=(intval($rel_col['cnt'])>1?'Add :  CGST':'Add :  IGST');?>
						</td>
						<td colspan="2" style="text-align:right; border-top:1px solid #000;font-size:10px;border-right:1px solid #000; "><?=number_format($totaltax1,2,".","")?></td>
						<?php} else {?>
						<td colspan="3" style="border-top:1px solid #000;border-right:1px solid #000;font-size:10px;text-align:center"> - 
						</td>
						<td colspan="2" style="text-align:center; border-top:1px solid #000;font-size:10px;border-right:1px solid #000; "> - </td>
						<?php}?>
					</tr>
					<?phpif(intval($rel_col['cnt'])>1) { ?>
					<tr height="20px">
						<td style="border-right:1px solid #000;border-top:1px solid #000;border-left:1px solid #000; font-size:10px;" colspan="<?=4+$colspan?>">
						
						</td>
						<td colspan="3" style="border-top:1px solid #000;border-right:1px solid #000;font-size:10px;text-align:left">Add : SGST</td>
						<td colspan="2" style="text-align:right; border-top:1px solid #000;font-size:10px;border-right:1px solid #000; "><?=number_format($totaltax2,2,".","")?></td>
					</tr>
					<?php}
						$totaltax=$totaltax1+$totaltax2;
					?>
					<tr height="20px">
						<td style="border-right:1px solid #000;border-top:1px solid #000;border-left:1px solid #000; font-size:10px;" colspan="<?=4+$colspan?>">
										
						</td>
						<td colspan="3" style="border-top:1px solid #000;border-right:1px solid #000;font-size:10px;text-align:left">
						Tax Amount :  GST
						</td>
						<?php
						$totaltax
						?>
						<td colspan="2" style="text-align:right; border-top:1px solid #000;font-size:10px;border-right:1px solid #000; "><?=number_format($totaltax,2,".","")?></td>
					</tr>
					<?php 
					$total=($total)+$rel['packing']; 
				if($rel['packing']!="0.00")
					{ ?>
					<tr height="20px">
						<td style="border-right:1px solid #000;border-top:1px solid #000;border-left:1px solid #000; font-size:10px;" colspan="<?=4+$colspan?>">
							
						</td>
						<td colspan="3" style="border-top:1px solid #000;border-right:1px solid #000;font-size:10px;text-align:left">Packing :</td>
						<td colspan="2" style="text-align:right; border-top:1px solid #000;font-size:10px;border-right:1px solid #000; "><?=number_format($rel['packing'],2,".","")?></td>
					</tr>
					<?php}
					$r=round($total)-$total; 
					?>
					<tr height="20px">
						<td style="border-right:1px solid #000;border-top:1px solid #000;border-left:1px solid #000; font-size:10px;" colspan="<?=4+$colspan?>">
							<strong>GSTIN NO. : <?=$set_head['vatno']?> </strong><br>
						</td>
						<td colspan="3" style="border-top:1px solid #000;border-right:1px solid #000;font-size:10px;text-align:left">Round off :</td>
						<td colspan="2" style="text-align:right; border-top:1px solid #000;font-size:10px;border-right:1px solid #000; "><?=number_format($r,2,".","")?></td>
					</tr>
					<tr height="20px">
						<td style="border-right:1px solid #000;border-top:1px solid #000;border-left:1px solid #000; font-size:10px;" colspan="<?=4+$colspan?>">
							<strong>Rupees:</strong>
									<?=ucwords(convert_number_to_words($total))?>
						</td>
						<td colspan="3" style="border-top:1px solid #000;border-right:1px solid #000;font-size:10px;text-align:left"><strong>Grand Total</strong> :</td>
						<td colspan="2" style="text-align:right; border-top:1px solid #000;font-size:10px;border-right:1px solid #000; "><strong><?=number_format($total,0,".","").'.00'?></strong></td>
					</tr>
					<tr height="35px">
						<td colspan="<?=9+$colspan?>" style="border:1px solid #000;border-bottom:none;"></td>
					</tr>
					<tr ><td style="border-right:1px solid #000;border-top:1px solid #000;border-left:1px solid #000; font-size:10px;padding:0px !important;" 	colspan="<?=9+$colspan?>">
					<?
								
								if($rel['gst_state_code']==24)
								{
									echo '<table border="0" style="font-size:10px;text-align:right;" width="100%"><tr> 
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
										<strong>HSN Code</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
										<strong>Total Amt.</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
											<strong>CGST Rate</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
											<strong>CGST Amt.</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
											<strong>SGST Rate</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
											<strong>SGST Amt.</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;"><strong>Total Tax Amount<strong></td>
									</tr>';
								}
								else if($rel['gst_state_code']!=24)
								{
									echo '<table border="0" style="font-size:10px;text-align:right;" width="100%"><tr> 
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
										<strong>HSN Code</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
										<strong>Taxable Amt.</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
											<strong>IGST Rate</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;border-right:1px solid black;" >
											<strong>IGST Amt.</strong>
										</td>
									<td style="vertical-align:top;text-align:center;border-bottom:1px solid black;"><strong>Total Tax Amount<strong></td>
									</tr>';
								}
							 $query="select sum(total) as amount,sum(tax_amount1) as tax_amt1,trn.product_hsn_code,sum(tax_amount2) as tax_amt2,tax_name1,tax_name2 
							FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trn.formulaid, trn.product_hsn_code";
							$rs_tax=$dbcon->query($query);
							while($rel_tax=mysqli_fetch_assoc($rs_tax))
							{	
								$total1+=$row_total=$rel_tax['tax_amt1']+$rel_tax['tax_amt2'];
								echo '<tr> 
										<td style="vertical-align:top;text-align:right;border-right:1px solid black;border-bottom:1px solid black;" >
										'.$rel_tax['product_hsn_code'].'
										</td>
										<td style="vertical-align:top;text-align:right;border-right:1px solid black;border-bottom:1px solid black;" >
										'.$rel_tax['amount'].'
										</td>';
								if($rel['gst_state_code']==24)
								{
									echo '<td style="vertical-align:top;text-align:right;border-right:1px solid black;border-bottom:1px solid black;" >
											'.str_replace("CGST","",$rel_tax['tax_name1']).'
										</td>
										<td style="vertical-align:top;text-align:right;border-right:1px solid black;border-bottom:1px solid black;" >
											'.$rel_tax['tax_amt1'].'
										</td>
										<td style="vertical-align:top;text-align:right;border-right:1px solid black;border-bottom:1px solid black;" >
											'.str_replace("SGST","",$rel_tax['tax_name2']).'
										</td>
										<td style="vertical-align:top;text-align:right;border-right:1px solid black;border-bottom:1px solid black;" >
											'.$rel_tax['tax_amt2'].'
										</td>';
								}
								else if($rel['gst_state_code']!=24)
								{
									echo '<td style="vertical-align:top;text-align:center;border-right:1px solid black;border-bottom:1px solid black;" >
											'.str_replace("IGST","",$rel_tax['tax_name1']).'
										</td>
										<td style="vertical-align:top;text-align:right;border-right:1px solid black;border-bottom:1px solid black;" >
											'.$rel_tax['tax_amt1'].'
										</td>';
								}
								echo '<td style="vertical-align:top;text-align:right;border-bottom:1px solid black;" >
											'.number_format($row_total,2).'
										</td>';
								
								echo '</tr>';
							$totalamt+=$rel_tax['amount'];
							$totaltaxamt1+=$rel_tax['tax_amt1'];
							$totaltaxamt2+=$rel_tax['tax_amt2'];
							}
							echo '<tr> 
										<td></td>
										<td style="vertical-align:top;text-align:right;border-top:1px solid black;border-right:1px solid black;" >
										'.number_format($totalamt,2).'
										</td>
										<td style="vertical-align:top;text-align:right;border-top:1px solid black;border-right:1px solid black;" >
											
										</td>
										<td style="vertical-align:top;text-align:right;border-top:1px solid black;border-right:1px solid black;" >
											'.number_format($totaltaxamt1,2).'
										</td>';
								if($rel['gst_state_code']==24)
								{
									echo '<td style="vertical-align:top;text-align:right;border-top:1px solid black;border-right:1px solid black;" >
											
										</td>
										<td style="vertical-align:top;text-align:right;border-top:1px solid black;border-right:1px solid black;" >
											'.number_format($totaltaxamt2,2).'
										</td>';
								}
								echo '<td style="vertical-align:top;text-align:right;border-top:1px solid black;">'.number_format($total1,2).'</td></tr></table>';
							?>
							</td></tr>
					<tr height="35px">
						<td colspan="<?=9+$colspan?>" style="border:1px solid #000;border-bottom:none;"></td>
					</tr>		
					<tr>
						<td colspan="<?=4+$colspan?>" style="vertical-align:top;border:1px solid #000;border-top:none;
						border-right:none;font-size:10px;text-align:left" class="con">
							
						<?phpif(!empty($set_head['conditions'])){ ?>
								<strong>Terms and Conditions:</strong><br> <?=$set_head['conditions']?>
							<?php} ?>	<br/><br/>
						<span style="vertical-align:bottom;">E & O.E.</span>
	
						</td>
						<td colspan="5" style=" border:1px solid #000;border-left:none;vertical-align:top;border-top:none;">
						<center>
						For, <strong> <span style="font-size:10px;text-decoration:bold;">
						<?=$set_head['company_name']?></span></strong>
						
						</center>
						 <br><br><br><br>
						 <center style="vertical-align:bottom;">Authorised Signatory</center>

						</td>
						
						</tr>
									
								</table>
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
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/invoice.js"></script>
    <!--<script src="js/count.js"></script>-->
		<script>
$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });


</script>
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
if ($('input[name=logo]:Checked').val() == "1") {
   
	$('#table_head').show();
	$('#table_foot').show();
	
}
else
{
	$('#table_head').hide();
	$('#table_foot').hide();
	$('#invoice_type').css('margin-top','1.75in');	
	
}

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

  docprint.document.write('<style type="text/css">@media print{ @page { size:A4; margin: 0;} } body {margin-top: 20px !important;margin-bottom: 0;margin-right: 20px;margin-left: 10px !important; ');
  docprint.document.write('font-family:Tahoma;color:#000;');
  docprint.document.write('font-family:Tahoma,Verdana; font-size:10px} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
  docprint.document.write('#mainpart table,#mainpart tr,#mainpart td,#mainpart th {border:1px #eee solid;padding:2px 5px 2px 5px;text-align:center;}ul li {list-style: disc !important;}');
  docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } @page {} .con ul {padding-left:0px !important;}.con ul li {margin-left:22px !important;} </style>');
  docprint.document.write('</head><body onLoad="self.print()">');
  docprint.document.write(content_vlue);
  docprint.document.write('</body></html>');
  docprint.document.close();
  docprint.focus();
	$('#table_head').show();
	$('#invoice_type').css('margin-top','0px');

  }
  location.reload();
}
</script>


  </body>
</html>
