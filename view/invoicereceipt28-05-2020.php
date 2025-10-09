<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$_SESSION['contents']=''; 
	$form="Invoice";
	$mode="Print";
	$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
	$query="select invoice.*,country.country_name,state.state_name,cust.stateid,state.gst_state_code, city.city_name, cust.l_name, cust.m_address, type.invoice_type,cust_pincode,cust_mobile,gst_no,dispatch.mode_dispatch from tbl_invoice as invoice 
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

		if(!empty($rel['consignee_id']))//consignee
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
		}
		
		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
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
/*td, th {
    padding: 0px 5px !important;
}*/
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
							  <li><a href="<?=ROOT.'invoice_list'?>">Invoice List</a></li>
							</ul>
						</div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
            </div>
              <!--state overview start-->
		  <div class="row">			
			<div class="col-md-12">
				<section class="panel">
				  	
		<div class="panel-body">
	<center>
			<div class="col-md-1"></div>With Logo
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
<input type="hidden" name="typename" id="typename" value="<?=$rel['module_name']?>">
	<?php ob_start(); ?>
<div class="col-lg-12 table-responsive" id="receipt_print">	
	<div class="col-md-12" style=" margin-top:10px;" id="print1">
	<!-- Fixed Logo Table Start -->
	<table width="100%" class="maintable" border="0" style="" id="table_head">
		<tr style="border:none;">
			<td width="100%" style="border:none;"> 
				<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>"  style="width:100%"/>-->
				
				<h1 align="center"><?=$set_head['company_name']?></h1>
				<h4 align="center" style="padding:top:8px;"><?=$set_head['logo_content']?></h4>
				<h4 align="center"><?=$set_head['address']?></h4>
				<h4 align="center"><?if($set_head['website']){?>Email: <?=$set_head['website']?><?}?> 
				<?if($set_head['contact_no']){?>(M) <?=$set_head['contact_no']?><?}?></h4>
				
			</td>
		</tr>
	</table>
	<!-- Fixed Logo Table End -->
	<!-- Multipage Table Start -->	
	<table width="100%" class="maintable" style="font-size: 11px;" id="invoice_type" >
	<thead>
		<tr>
			<th colspan="10" style="padding:0px !important;">
				<table style="font-size:10px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
					<thead>	
							<tr>
								<td style="" colspan="2"> </td>
								<td colspan="3" style="text-align:center;"> 
									<strong class="typetitle" style="font-size:14px;">
									<?=$rel['module_name']?>
									</strong>
								</td>
								<td width="10%" style="text-align:right;"> 
									<strong style="font-size:9px">
										<b class="data_title">ORIGINAL FOR RECIPIENT</b>
									</strong>
								</td>
							</tr>
						
							<tr>
								<td style="vertical-align:top;border:0.5px #ccc solid;border-right:none;"><strong>Invoice No </strong>
								</td>
								<td width="28%" colspan="" style="vertical-align:top;border-bottom:0.5px #ccc solid; border-right:0.5px #ccc solid;border-top:0.5px #ccc solid">: <strong><?=$rel['invoice_no']?></strong>
								</td>
								<td width="4%" style="vertical-align:top;border-bottom:0.5px #ccc solid;border-top:0.5px #ccc solid"><strong>Date </strong>
								</td>
								<td width="15%" style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;border-top:0.5px #ccc solid" colspan="">: <strong><?=date('d-m-Y',strtotime($rel['invoice_date']))?></strong>
								</td>
								
								<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-top:0.5px #ccc solid;"><strong>Mode of Dispatch</strong>						
								</td>							
								<td width="38%" style="vertical-align:top;border-bottom:0.5px #ccc solid;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;">: <?=$rel['mode_dispatch']?>						
								</td>							
							</tr>
							<tr>
								<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-left:0.5px #ccc solid;"><strong>Challan No </strong>
								</td>
								<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid" colspan="">: <?=$rel['challan_no']?>
								</td>
								<td style="vertical-align:top;border-bottom:0.5px #ccc solid "><strong>Date </strong>
								</td>
								<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid" colspan="">: <?=date('d-m-Y',strtotime($rel['challan_date']))?>
								</td>
								
								<td style="vertical-align:top;border-bottom:0.5px #ccc solid"><strong>Vehicle No.</strong>					
								</td>							
								<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;">: <?=$rel['vehicle_no']?></td>
							</tr>
							<tr>
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-left:0.5px #ccc solid;"><strong>Po No </strong></td>
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid" colspan="">: <?=$rel['order_no']?>
							</td>
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid "><strong>Date </strong>
							</td>
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid" colspan="">: <?=$order_date?>
							</td>
								
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid"><strong>Place of Supply</strong>					
							</td>							
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;">: <?=$rel['state_name']?></td>
							
							</tr>
							
							
						<tr>
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-left:0.5px #ccc solid; "><strong>State</strong></td>
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid">: <?=$set_head['state_name']?>
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid "><strong>Code</strong></td>
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid">: <?=$set_head['gst_state_code']?>
							
							</td>
							
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;white-space:nowrap;"><strong>Date And Time of Supply</strong>					
							</td>							
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;">: <?=$dispatch_date?></td>
							
						</tr>
						<tr>
							<td style="vertical-align:top;border-left:0.5px #ccc solid;border-bottom:0.5px #ccc solid;white-space:nowrap;"><strong>E-way Bill No.</strong> </td>
							<td colspan="3" style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid">: <?=$rel['docket_no']?></td>	
							
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid"><strong>Reverse Charge</strong></td>							
							<td colspan="3" style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;">: <?=(!empty($rel['reverse_charge'])?'Yes':'No')?></td>
						</tr>
						<!--<tr>
							<td style="vertical-align:top;border-left:0.5px #ccc solid;border-bottom:0.5px #ccc solid;white-space:nowrap;"><strong>Payment Terms</strong></td>
							<td colspan="3" style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid"> : <?=$rel['payment_terms']?>
							
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid"><strong></strong></td>							
							<td style="vertical-align:top;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;">: </td>
						</tr>-->
						
						<tr>
						<td colspan="4" width="0%" style="vertical-align:top;border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;">
						<b>Bill to Party : </b><br/>
							<strong><?=$rel['l_name']?></strong>
							<span style="font-weight:normal;">  <br/>
							<?=$rel['m_address']?>
							 <br>
							 <?=$rel['city_name']?>, <?=$rel['state_name']?>, <?=$rel['country_name']?>
							  <?phpif(!empty($rel['cust_pincode']))
								{	?>
							 -  <?=$rel['cust_pincode']?>
								<?php} ?></span>
								<!--<br>
								Mobile no : <?=$rel['cust_mobile']?>-->
								
						</td>
						<?phpif(empty($rel['consignee_id'])) { ?>
						<td colspan="2"  style="border-right:0.5px #ccc solid">
						<b>Shipped to Party : </b><br>
							<strong><?=$rel['l_name']?></strong>
							<span style="font-weight:normal;">   <br>
							<?=$rel['m_address']?>
							 <br>
							 <?=$rel['city_name']?>, <?=$rel['state_name']?>, <?=$rel['country_name']?>
								<?phpif(!empty($rel['cust_pincode']))
								{	?>
							 -  <?=$rel['cust_pincode']?>
								<?php} ?></span>
							<!--	<br>
								Mobile no : <?=$rel['cust_mobile']?>-->
								
						</td>
						<?php} else
						{?>
							<td colspan="2"  style="border-right:0.5px #ccc solid">
							<b>Consignee : </b><br>
							<strong><?=$cons_data['l_name']?></strong>
							<span style="font-weight:normal;">   <br>
							<?=$cons_data['m_address']?>
							 <br>
							 <?=$cons_data['city_name']?>, <?=$cons_data['state_name']?>, <?=$cons_data['country_name']?>
								<?phpif(!empty($cons_data['cust_pincode']))
								{	?>
							 -  <?=$cons_data['cust_pincode']?>
								<?php} ?></span>
								<!--<br>
								Mobile no : <?=$cons_data['cust_mobile']?>-->
						</td>
						<?php}?>
						</tr>
						<tr>
							<td colspan="4" style="border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;"><strong>GSTIN: <?=$rel['gst_no']?> </strong></td>
							<td colspan="2" style="border-right:0.5px #ccc solid;"><strong>GSTIN: <?=$cons_gst_no?> 
							</strong></td>
						</tr>
						<tr>
							<td colspan="2" width="25%" style="border-left:0.5px #ccc solid;border-bottom:0.5px #ccc solid;font-weight:normal;">State : <?=$rel['state_name']?></td>
							<td colspan="2" width="23.5%" style="border-right:0.5px #ccc solid;text-align:left;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;font-weight:normal;">Code : <?=$rel['gst_state_code']?></td>
							<td style="text-align:left;border-bottom:0.5px #ccc solid;font-weight:normal;">State : <?=$cons_state_name?></td>
							<td style="text-align:left;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;font-weight:normal;">Code : <?=$cons_gst_state_code?></td>
						</tr>
						</thead>
				</table>
			
			
			</th>
		</tr>
		<tr>
			<th width="3%" style="text-align:center;border:0.5px #ccc solid;border-top: none;"><strong>SR.<br/> NO.</strong></th>
			<th width="<?=$dynamicwidth?>%" style="text-align:center;border:0.5px #ccc solid;border-top: none;" >
				<strong>Particulars </strong>
			</th>
			<th width="8%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
				<strong>HSN/SAC <br/>Code</strong>
			</th>
			<th width="7%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
				<strong>QTY.</strong>
			</th>
			<th width="7%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
				<strong>Rate</strong>
			</th>
			<?phpif($set_head['show_disc']=='1'){ ?>
			<th width="6%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
				<strong>Less:<br/>Disc.</strong>
			</th>
			<?}?>
			<th width="9%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
				<strong>Taxable<br/>Value</strong>
			</th>
			<th width="4%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
				<strong>GST Rate</strong>
			</th>
			<th width="6%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
				<strong>GST Amount</strong>
			</th>
			<th width="10%" style="text-align:center;border:0.5px #ccc solid;border-top: none;">
				<strong>Total</strong>
			</th>
		</tr>
	</thead>
	<tbody style="border: 1px solid;">
		<?php
			$qry="select trn.*,product.*,unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id  left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id) where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trancation_id order by product.product_type,trancation_id";
			/*$qry="select * FROM `tbl_invoicetrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id where trancation_status=0 and product.product_type not in(3) and invoice_id=".$rel['invoice_id'];*/
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
		<tr style="height:30px">
					<td style="text-align:center;vertical-align:top;border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;">
							<?phpif($row['product_type']!='3'){
								echo $i;
							}?>
					</td>
					<td style="vertical-align:top;border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;<?phpif($row['product_type']=='3'){ echo 'text-align:right;padding-top:5px;';}?>" >
						<strong><?=stripcslashes($row['product_name'])?></strong>
						<br/><?=nl2br(stripcslashes($row['description']));?>
					</td>
					<td style="border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;vertical-align:top;text-align:center" >
					<?=stripcslashes($row['product_hsn_code'])?>
					</td>
					<td style="text-align:center;vertical-align:top;border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;white-space:nowrap;" >
						<?phpif($row['product_type']!='3'){ ?>
							<?=$row['product_qty'].' '.$row['unit_name']?>
						<?php}else{
							$charges_qty+=$row['product_qty'];
						} ?>	
					</td>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF; border-right:0.5px #ccc solid;" >
						<?=number_format($row['product_rate'],2,".","")?>
					</td>
					<?phpif($set_head['show_disc']=='1'){?>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:0.5px #ccc solid;">
						<?=number_format($row['discount_per'],2,".","").'%'?>
					</td>
					<?}?>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:0.5px #ccc solid;">
						<?=number_format($row['taxable_value'],2,".","")?>
					</td>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:0.5px #ccc solid;">
						<?=$tax_arr[0]+$tax_arr[1]?>%
					</td>
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:0.5px #ccc solid;">
						<?=number_format($row['tax_amount1']+$row['tax_amount2'],2,".","")?>
					</td>
					
					<td style="text-align:right;vertical-align:top;border-bottom-color:#FFFFFF;border-right:0.5px #ccc solid;">
						<?=number_format($row['total'],2,".","")?>
					</td>
				</tr>
		
		<?php
			$i++; 
				$totalqty=$totalqty+$row['product_qty']-$charges_qty;
				$total_product_amount+=($row['product_qty']*$row['product_rate']);
				$totaltaxable+=$row['taxable_value'];
				$tax_amount+=$row['tax_amount'];
				$total+=$row['total'];
		}
			$pr=13-$cnt;
			
			for($j=0; $j<$pr; $j++)
			{
		?>	
			<tr style="height:30px">
				<td style="border-right:0.5px #ccc solid;border-left:0.5px #ccc solid;"></td>
				<td style="border-right:0.5px #ccc solid;"></td>
				<?phpif($set_head['show_disc']=='1'){?>
				<td style="border-right:0.5px #ccc solid;"></td>
				<?}?>
				<td style="border-right:0.5px #ccc solid;"></td>
				<td style="border-right:0.5px #ccc solid;"></td>
				<td style="border-right:0.5px #ccc solid;"></Td>
				<td style="border-right:0.5px #ccc solid;"></td>
				<td style="border-right:0.5px #ccc solid;"></td>
				<td style="border-right:0.5px #ccc solid;"></td>
				<td style="border-right:0.5px #ccc solid;"></td>
			</tr>
			
		<?php } ?>
			<tr style="height:20px">
				<td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;border-left:0.5px #ccc solid; text-align:right;" colspan="3"><strong>Total</strong></td>
				
				<td style="text-align:center;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;"><strong><?=number_format($totalqty,2,".","")?></strong></td>
				<?phpif($set_head['show_disc']=='1'){?>
				<td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;"></td>
				<?}?>
				<td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;"></td>
				<td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:right;"><strong><?=number_format($totaltaxable,2,".","")?></strong></td>
				<td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:right;"></td>
				<td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:right;"><strong><?=number_format($tax_amount,2,".","")?></strong></td>
				
				<td style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:right;"><strong><?=number_format($total,2,".","")?></strong></td>
							
			</tr>		
			<tr>
				<td colspan="10" style="padding: 0px !important;border:0.5px #ccc solid">
				<table class="footer-table" width="100%">
				<?
				$exp_total=get_expense_by_invoice($dbcon,$invoiceid);
				$query="SELECT tax.tax_typeid,sum(tax_amount) as tax_amount FROM `tbl_tax_trn` as trn 
						left join tbl_tax as tax on tax.tax_id=trn.tax_id 
						left join tbl_tax_type as taxtype on taxtype.tax_typeid=tax.tax_typeid
						where mst_id=".$rel['invoice_id']." and tax_for='invoice' group by tax.tax_typeid";
				$rs_total_tax=$dbcon->query($query);		
				$tax_rows=mysqli_num_rows($rs_total_tax);
				$rowspan=$tax_rows+3;	
				($exp_total!="0.00"?$rowspan++:'');
				?>
					<tr height="20px">
						<td width="61.6%" style="border-right:0.5px #ccc solid;border-top:0.5px #ccc solid;vertical-align:top;" colspan="<?=$colspan?>" rowspan="<?=$rowspan?>">
						<?phpif(!empty($set_head['bank_name'])){?>
								<strong>Bank Name:</strong> <?=$set_head['bank_name']?> <br/>
								<?php} ?>
								<?phpif(!empty($set_head['ac_no'])){?>
								<strong>A/c No:</strong> <?=$set_head['ac_no']?><br/>	 
								<?php} ?>
								<?phpif(!empty($set_head['ifcs'])){ ?>
									<strong>IFSC:</strong><?=$set_head['ifcs']?><br/>
								<?php} ?>	
								<?phpif(!empty($set_head['branch_name'])){ ?>
									<strong>Branch :</strong> <?=$set_head['branch_name']?><br/><?php} ?>
								<strong>COMPANY GST No. : <?=$set_head['vatno']?> </strong><br>	
								<strong>Rupees:</strong>
									<?=ucwords(convert_number_to_words($total))?>
						</td>
						<td colspan="3" width="28.7%" style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:left">
							Taxable Amount
						</td>
						<td colspan="2" style="text-align:right; border-top:0.5px #ccc solid;" width="10%"><?=number_format($totaltaxable,2,".","")?></td>	
					</tr>
					<?
					while($rel_tax=mysqli_fetch_assoc($rs_total_tax))
					{
						$tax_type['type_id'][]=$rel_tax['tax_typeid'];
						$tax_type['type_name'][]=$rel_tax['tax_type_name'];
						$tax_type['type_total'][]=$rel_tax['tax_amount'];
					?>
					<tr height="20px">
										
						<td colspan="3" style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:left" >						
							Add : <?=$rel_tax['tax_type_name']?>
						</td>
						<td colspan="2" style="text-align:right; border-top:0.5px #ccc solid;border-right:0.5px #ccc solid; "><?=number_format($rel_tax['tax_amount'],2,".","")?></td>
						
					</tr>
					<?php}?>
					
					
				<?php 
					
					
					$total=($total)+$exp_total; 
					if($exp_total!="0"){ ?>
					<tr height="20px">
						
						<td colspan="3" style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:left">Expense :</td>
						<td colspan="2" style="text-align:right; border-top:0.5px #ccc solid;border-right:0.5px #ccc solid; "><?=number_format($exp_total,2,".","")?></td>
					</tr>
					<?php}
					$r=round($total)-$total; 
					?>
					<tr height="20px">
						
						<td colspan="3" style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:left">Round off :</td>
						<td colspan="2" style="text-align:right; border-top:0.5px #ccc solid;border-right:0.5px #ccc solid; "><?=number_format($r,2,".","")?></td>
					</tr>
					
					<tr height="20px">
						
						<td colspan="3" style="border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;text-align:left"><strong>Grand Total</strong> :</td>
						<td colspan="2" style="text-align:right; border-top:0.5px #ccc solid;border-right:0.5px #ccc solid; "><strong><?=number_format($total,0,".","").'.00'?></strong></td>
					</tr>
					<tr height="35px">
						<td colspan="<?=5+$colspan?>" style="border:0.5px #ccc solid;border-left:none;"></td>
					</tr>
					<tr>
					<td style="border-right:0.5px #ccc solid;border-top:0.5px #ccc solid;font-size:10px;padding:0px !important;" colspan="<?=5+$colspan?>">
					<?
						
						if($rel['stateid']==$set_head['stateid'])
						{
							echo '<table border="0" style="font-size:10px;text-align:right;" width="100%"><tr> 
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
								<strong>HSN Code</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
								<strong>Total Amt.</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									<strong>CGST Rate</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									<strong>CGST Amt.</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									<strong>SGST Rate</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									<strong>SGST Amt.</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;"><strong>Total Tax Amount<strong></td>
							</tr>';
						}
						else if($rel['stateid']!=$set_head['stateid'])
						{
							echo '<table border="0" style="font-size:10px;text-align:right;" width="100%"><tr> 
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
								<strong>HSN Code</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
								<strong>Taxable Amt.</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									<strong>IGST Rate</strong>
								</td>
								<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									<strong>IGST Amt.</strong>
								</td>
							<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;"><strong>Total Tax Amount<strong></td>
							</tr>';
						}
			$query="select sum(total) as amount,sum(tax_amount1) as tax_amt1,trn.product_hsn_code,sum(tax_amount2) as tax_amt2,tax_name1,tax_name2 
			FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trn.formulaid, trn.product_hsn_code";
			$rs_tax=$dbcon->query($query);
					while($rel_tax=mysqli_fetch_assoc($rs_tax))
					{	
						$total1+=$row_total=$rel_tax['tax_amt1']+$rel_tax['tax_amt2'];
						echo '<tr> 
								<td style="vertical-align:top;text-align:center;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
								'.$rel_tax['product_hsn_code'].'
								</td>
								<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
								'.$rel_tax['amount'].'
								</td>';
						if($rel['stateid']==$set_head['stateid'])
						{
							echo '<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
									'.str_replace("CGST","",$rel_tax['tax_name1']).'
								</td>
								<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
									'.$rel_tax['tax_amt1'].'
								</td>
								<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
									'.str_replace("SGST","",$rel_tax['tax_name2']).'
								</td>
								<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
									'.$rel_tax['tax_amt2'].'
								</td>';
						}
						else if($rel['stateid']!=$set_head['stateid'])
						{
							echo '<td style="vertical-align:top;text-align:center;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
									'.str_replace("IGST","",$rel_tax['tax_name1']).'
								</td>
								<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
									'.$rel_tax['tax_amt1'].'
								</td>';
						}
						echo '<td style="vertical-align:top;text-align:right;border-bottom:0.5px #ccc solid;" >
									'.number_format($row_total,2).'
								</td>';
						
						echo '</tr>';
					$totalamt+=$rel_tax['amount'];
					$totaltaxamt1+=$rel_tax['tax_amt1'];
					$totaltaxamt2+=$rel_tax['tax_amt2'];
					}
					echo '<tr> 
								<td></td>
								<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
								'.number_format($totalamt,2).'
								</td>
								<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									
								</td>
								<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									'.number_format($totaltaxamt1,2).'
								</td>';
						if($rel['stateid']==$set_head['stateid'])
						{
							echo '<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									
								</td>
								<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
									'.number_format($totaltaxamt2,2).'
								</td>';
						}
						echo '<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;">'.number_format($total1,2).'</td></tr></table>';
					?>
					<?
							/*
									echo '<table border="0" style="font-size:10px;text-align:right;" width="100%"><tr> 
										<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
										<strong>HSN Code</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
										<strong>Total Amt.</strong>
										</td>';
									for($i=0;$i<count($tax_type['type_name']);$i++)
									{
										echo '<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
											<strong>'.$tax_type['type_name'][$i].' Rate</strong>
										</td>
										<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
											<strong>'.$tax_type['type_name'][$i].' Amt.</strong>
										</td>';
									}
										echo '
										<td style="vertical-align:top;text-align:center;border-bottom:0.5px #ccc solid;"><strong>Total Tax Amount<strong></td>
									</tr>';
							
								
					//$query="select sum(total) as amount,sum(tax_amount1) as tax_amt1,trn.product_hsn_code,sum(tax_amount2) as tax_amt2,tax_name1,tax_name2 FROM `tbl_invoicetrn` as trn where trancation_status=0 and invoice_id=".$rel['invoice_id']." group by trn.formulaid, trn.product_hsn_code";
					$query="select sum(total) as amount,trn.product_hsn_code,group_concat(trn.trancation_id) as trn_id  FROM `tbl_invoicetrn` as trn  where trancation_status=0 and invoice_id=".$rel['invoice_id']."  group by trn.formulaid,trn.product_hsn_code";
					$rs_tax=$dbcon->query($query);
						while($rel_tax=mysqli_fetch_assoc($rs_tax))
							{	
								
								echo '<tr> 
										<td style="vertical-align:top;text-align:center;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
										'.$rel_tax['product_hsn_code'].'
										</td>
										<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
										'.$rel_tax['amount'].'
										</td>';
								$row_total=0;		
								for($i=0;$i<count($tax_type['type_name']);$i++)
								{
									$arr=get_invoice_trn_tax_rate_total($rel_tax['trn_id'],$tax_type['type_id'][$i],$dbcon);
									$row_total+=$arr['tax_amount'];
									echo '<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
											'.$arr['tax_rate'].'
										</td>
										<td style="vertical-align:top;text-align:right;border-right:0.5px #ccc solid;border-bottom:0.5px #ccc solid;" >
											'.$arr['tax_amount'].'
										</td>';
								}
								
								echo '<td style="vertical-align:top;text-align:right;border-bottom:0.5px #ccc solid;" >
											'.number_format($row_total,2).'
										</td>';
								
								echo '</tr>';
							$totalamt+=$rel_tax['amount'];
							$total1+=$row_total;
							}
							echo '<tr> 
										<td></td>
										<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
										'.number_format($totalamt,2).'
										</td>
										';
								for($i=0;$i<count($tax_type['type_name']);$i++)
								{
									echo '<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
											
										</td>
										<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;border-right:0.5px #ccc solid;" >
											'.number_format($tax_type['type_total'][$i],2).'
										</td>';
								}
								echo '<td style="vertical-align:top;text-align:right;border-top:0.5px #ccc solid;">'.number_format($total1,2).'</td></tr></table>';
								*/
							?>
							</td>
					</tr>
					<!--<tr height="35px">
						<td colspan="<?=5+$colspan?>" style="border:0.5px #ccc solid;border-bottom:none;"></td>
					</tr>-->		
					<tr>
						<td colspan="<?=$colspan?>" style="vertical-align:top;border:0.5px #ccc solid;
						border-right:none;border-left:none;font-size:10px;text-align:left" class="con">
							
						<?phpif(!empty($set_head['conditions'])){ ?>
								<strong>Terms and Conditions:</strong><br> <?=$set_head['conditions']?>
							<?php} ?>	<br/><br/>
						<!--<span style="vertical-align:bottom;">E & O.E.</span>-->
	
						</td>
						<td colspan="5" style=" border:0.5px #ccc solid;border-left:none;vertical-align:top;">
						<center>
						For, <strong> <span style="font-size:10px;text-decoration:bold;">
						<?=$set_head['company_name']?></span></strong>
						
						</center>
						 <br><br><br><br>
						 <center style="vertical-align:bottom;">Authorised Signatory</center>

						</td>
						
					</tr>
				</table>
				<!--<table width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%"/>
						</td>
					</tr>
				</table>-->
				</td>
			</tr>		
	</tbody>
	</table>
	<!-- Multipage Table End -->		
				
		<!--<center><span style="float:left;">E.& O. E.</span>This is a Computer Generated Invoice</center>-->
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
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
</section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
    <script src="<?=ROOT?>js/app/invoice.js"></script>
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
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
  docprint.document.write('<style type="text/css">');
	if ($('input[name=logo]:Checked').val() == "1") {
	    $('#table_head').show();
		$('#table_foot').show();
		docprint.document.write('@media print{ @page { size:A4; margin: 0.2in <?=$set_head['letter_head_right_margin']?>in 0.2in <?=$set_head['letter_head_left_margin']?>in; } }   ');
	}
	else
	{
		docprint.document.write('@media print{ @page { size:A4; margin: <?=$set_head['letter_head_top_margin']?>in <?=$set_head['letter_head_right_margin']?>in <?=$set_head['letter_head_bottom_margin']?>in <?=$set_head['letter_head_left_margin']?>in; } }  #table_head, #table_foot { display:none }');
		//$('#invoice_type').css('margin-top','1.7in');	
	}
 
  docprint.document.write('body { font-family:Tahoma;color:#000;font-size:10px;}');
  docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } tr { page-break-inside: avoid } .maintable tbody tr { border-bottom:0.5px #ccc solid; }');
  docprint.document.write(' .maintable table { page-break-inside:auto } .maintable tr{ page-break-inside:avoid; page-break-after:auto } .maintable thead { display:table-header-group }  .maintable tfoot tr{ /*display:table-footer-group;*/ page-break-inside:avoid; page-break-before:always; } footer-table{ page-break-inside:avoid; page-break-before:always;  }</style>');
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
<?

?>