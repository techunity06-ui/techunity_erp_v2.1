<?php 
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
	
	$type = isset($_GET['type'])?$_GET['type']:0;
	$state = isset($_GET['state'])?$_GET['state']:0;
	$st_date=date("Y-m-d",strtotime($_SESSION['start']));
	$end_date=date("Y-m-d",strtotime($_SESSION['end']));
	
	$company_row = get_company_data($dbcon,$_SESSION['company_id']);
	$company_state=$company_row['stateid'];
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once($include.'include_css_file.php');?>
<style>
	.gst_details
	{
		color:blue;
		font-size:15px !important;
	}
	.style_underline a
	{
		border-bottom: dotted blue 2px !important;
	}
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
		                              <?php 
//				include_once('../include/quick_link.php');
				?>

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
								<li><a href="<?=ROOT.FINANCE_ROOT ?>finance_report_list">Finance Report</a></li>
								<li ><a href="<?=ROOT.FINANCE_ROOT.'gst-3b-report.php'?>">GSTR 3B Report</a></li>
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
				 
				<input type="hidden" id="type_id" value="<?=$type;?>" />
				<input type="hidden" id="start_date" value="<?=date("Y-m-d",strtotime($_SESSION['start']));?>" />
				<input type="hidden" id="end_date" value="<?=date("Y-m-d",strtotime($_SESSION['end']));?>" />
				
				<header class="panel-heading">
					<?php if($type=='3.1a') { ?>
						<h3>Outward txbl. supplies (other than zero rated , nil rated and exempted)</h3>
					<?php } else if($type=='3.1b') { ?>
						<h3>Outward Taxable supplies Zero Rated</h3>
					<?php }  else if($type=='3.1c') { ?>
						<h3>Other Outward supp.(Nill rated , exempted)</h3>
					<?php } else if($type=='3.1e') { ?>
						<h3>Non GST Outward Supplies</h3>
					<?php } else if($type=='unreg_supply') { ?>
						<h3>Supplies Made to Unreg. Persons</h3>
					<?php }  else if($type=='composition_supply') { ?>
						<h3>Supplies Made to Composition Dealers</h3>
					<?php } else if($type=='uin_supply') { ?>
						<h3>Supplies Made to UIN Holders</h3>
					<?php } ?>
				</header>
				<div class="panel-body">
					
					<div class="row">
								
						<div class="col-md-12">
							
							<!-- GST B2B Invoices --->
							
							<?php 
								if($type=='3.1a')
								{
			
									$q = "select IFNULL(sum(trn.product_amount*trn.currency_rate),0) as total , IFNULL(sum(trn.cgst_tax_rate*trn.currency_rate),0) as cgst_rate , IFNULL(sum(trn.sgst_tax_rate*trn.currency_rate),0) as sgst_rate, IFNULL(sum(trn.igst_tax_rate*trn.currency_rate),0) as igst_rate , i.invoice_no,i.invoice_date,i.invoice_id,l.l_name
									 from tbl_invoice as i 
										left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id
										left join tbl_ledger as l on l.l_id=i.cust_id
									  where trn.trancation_status='0' and trn.product_tax_cat NOT IN (".GST_NILL_RATED.",".GST_EXEMPTED.",".GST_ZERO_RATED.",".NON_GST.") and i.invoice_date between '$st_date' and '$end_date' group by i.invoice_id";
									
									$query = $dbcon->query($q);							
								
							?>
						
							<table class="table table-bordered table-hover" id="">
								<thead>
									<tr>
										<th>#</th>
										<th>Customer</th>
										<th>Invoice No</th>
										<th>Invoice Date</th>
										<th>Taxable Amount</th>
										<th>CGST</th>
										<th>SGST</th>
										<th>IGST</th>
									</tr>
								</thead>
								<?php 
									
									$cnt=1;$total=0;$cgst=0;$sgst=0;$igst=0;
									while($row = brp_mysqli_fetch_assoc($query))
									{
										$amount = $row['total'];
										$total+=$amount;
										$cgst+=$row['cgst_rate'];
										$sgst+=$row['sgst_rate'];
										$igst+=$row['igst_rate'];
										
								?>
									<tbody>
										<tr>
											<td><?=$cnt;?></td>
											<td><a href="<?=ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'];?>" target="_blank"><?=$row['l_name']?></a></td>
											<td><?=$row['invoice_no']?></td>
											<td><?=date("d/m/Y",strtotime($row['invoice_date']))?></td>
											<td><?=round($amount,2);?></td>
											<td><?=round($row['cgst_rate'],2);?></td>
											<td><?=round($row['sgst_rate'],2);?></td>
											<td><?=round($row['igst_rate'],2);?></td>
										</tr>
									</tbody>
								<?php $cnt++; } ?>
								
								<tr>
									<th colspan="4" class="text-right">Total:</th>
									<th><?=round($total,2);?></th>
									<th><?=round($cgst,2);?></th>
									<th><?=round($sgst,2);?></th>
									<th><?=round($igst,2);?></th>
								</tr>
							</table>
							
							<?php } ?>
							
							<!-- GST B2C Large Invoices --->
							
							
							<?php 
								if($type=='3.1b')
								{
			
									$q = "select IFNULL(sum(trn.product_amount*trn.currency_rate),0) as total , IFNULL(sum(trn.cgst_tax_rate*trn.currency_rate),0) as cgst_rate , IFNULL(sum(trn.sgst_tax_rate*trn.currency_rate),0) as sgst_rate, IFNULL(sum(trn.igst_tax_rate*trn.currency_rate),0) as igst_rate , i.invoice_no,i.invoice_date,i.invoice_id,l.l_name
									 from tbl_invoice as i 
										left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id
										left join tbl_ledger as l on l.l_id=i.cust_id
									  where trn.trancation_status='0' and trn.product_tax_cat='".GST_ZERO_RATED."' group by i.invoice_id";
									
									$query = $dbcon->query($q);							
								
							?>
							
							<table class="table table-bordered table-hover" id="">
								<thead>
									<tr>
										<th>#</th>
										<th>Customer</th>
										<th>Invoice No</th>
										<th>Invoice Date</th>
										<th>Taxable Amount</th>
										<th>CGST</th>
										<th>SGST</th>
										<th>IGST</th>
									</tr>
								</thead>
								<?php 
									
									$cnt=1;$total=0;$cgst=0;$sgst=0;$igst=0;
									while($row = brp_mysqli_fetch_assoc($query))
									{
										$amount = $row['total'];
										$total+=$amount;
										$cgst+=$row['cgst_rate'];
										$sgst+=$row['sgst_rate'];
										$igst+=$row['igst_rate'];
										
								?>
									<tbody>
										<tr>
											<td><?=$cnt;?></td>
											<td><a href="<?=ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'];?>" target="_blank"><?=$row['l_name']?></a></td>
											<td><?=$row['invoice_no']?></td>
											<td><?=date("d/m/Y",strtotime($row['invoice_date']))?></td>
											<td><?=round($amount,2);?></td>
											<td><?=round($row['cgst_rate'],2);?></td>
											<td><?=round($row['sgst_rate'],2);?></td>
											<td><?=round($row['igst_rate'],2);?></td>
										</tr>
									</tbody>
								<?php $cnt++; } ?>
								
								<tr>
									<th colspan="4" class="text-right">Total:</th>
									<th><?=round($total,2);?></th>
									<th><?=round($cgst,2);?></th>
									<th><?=round($sgst,2);?></th>
									<th><?=round($igst,2);?></th>
								</tr>
							</table>
							
							<?php } ?>
							
							
							<!-- GST B2C Small Invoices --->
							
							
							<?php 
								if($type=='3.1c')
								{
			
									$q = "select IFNULL(sum(trn.product_amount*trn.currency_rate),0) as total , IFNULL(sum(trn.cgst_tax_rate*trn.currency_rate),0) as cgst_rate , IFNULL(sum(trn.sgst_tax_rate*trn.currency_rate),0) as sgst_rate, IFNULL(sum(trn.igst_tax_rate*trn.currency_rate),0) as igst_rate , i.invoice_no,i.invoice_date,i.invoice_id,l.l_name
									 from tbl_invoice as i 
										left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id
										left join tbl_ledger as l on l.l_id=i.cust_id
									  where trn.trancation_status='0' and trn.product_tax_cat IN (".GST_NILL_RATED.",".GST_EXEMPTED.") group by i.invoice_id";
									
									$query = $dbcon->query($q);							
								
							?>

							<table class="table table-bordered table-hover" id="">
								<thead>
									<tr>
										<th>#</th>
										<th>Customer</th>
										<th>Invoice No</th>
										<th>Invoice Date</th>
										<th>Taxable Amount</th>
										<th>CGST</th>
										<th>SGST</th>
										<th>IGST</th>
									</tr>
								</thead>
								<?php 
									
									$cnt=1;$total=0;$cgst=0;$sgst=0;$igst=0;
									while($row = brp_mysqli_fetch_assoc($query))
									{
										$amount = $row['total'];
										$total+=$amount;
										$cgst+=$row['cgst_rate'];
										$sgst+=$row['sgst_rate'];
										$igst+=$row['igst_rate'];
										
								?>
									<tbody>
										<tr>
											<td><?=$cnt;?></td>
											<td><a href="<?=ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'];?>" target="_blank"><?=$row['l_name']?></a></td>
											<td><?=$row['invoice_no']?></td>
											<td><?=date("d/m/Y",strtotime($row['invoice_date']))?></td>
											<td><?=round($amount,2);?></td>
											<td><?=round($row['cgst_rate'],2);?></td>
											<td><?=round($row['sgst_rate'],2);?></td>
											<td><?=round($row['igst_rate'],2);?></td>
										</tr>
									</tbody>
								<?php $cnt++; } ?>
								
								<tr>
									<th colspan="4" class="text-right">Total:</th>
									<th><?=round($total,2);?></th>
									<th><?=round($cgst,2);?></th>
									<th><?=round($sgst,2);?></th>
									<th><?=round($igst,2);?></th>
								</tr>
							</table>
							
							<?php } ?>
							
							
							<!-- Credit - Debit note unregistered --->
							
							<?php 
								if($type=='3.1e')
								{
			
									$q = "select IFNULL(sum(trn.product_amount*trn.currency_rate),0) as total , IFNULL(sum(trn.cgst_tax_rate*trn.currency_rate),0) as cgst_rate , IFNULL(sum(trn.sgst_tax_rate*trn.currency_rate),0) as sgst_rate, IFNULL(sum(trn.igst_tax_rate*trn.currency_rate),0) as igst_rate , i.invoice_no,i.invoice_date,i.invoice_id,l.l_name
									 from tbl_invoice as i 
										left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id
										left join tbl_ledger as l on l.l_id=i.cust_id
									  where trn.trancation_status='0' and trn.product_tax_cat='".NON_GST."' group by i.invoice_id";
									
									$query = $dbcon->query($q);							
								
							?>
							
							<table class="table table-bordered table-hover" id="">
								<thead>
									<tr>
										<th>#</th>
										<th>Customer</th>
										<th>Invoice No</th>
										<th>Invoice Date</th>
										<th>Taxable Amount</th>
										<th>CGST</th>
										<th>SGST</th>
										<th>IGST</th>
									</tr>
								</thead>
								<?php 
									
									$cnt=1;$total=0;$cgst=0;$sgst=0;$igst=0;
									while($row = brp_mysqli_fetch_assoc($query))
									{
										$amount = $row['total'];
										$total+=$amount;
										$cgst+=$row['cgst_rate'];
										$sgst+=$row['sgst_rate'];
										$igst+=$row['igst_rate'];
										
								?>
									<tbody>
										<tr>
											<td><?=$cnt;?></td>
											<td><a href="<?=ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'];?>" target="_blank"><?=$row['l_name']?></a></td>
											<td><?=$row['invoice_no']?></td>
											<td><?=date("d/m/Y",strtotime($row['invoice_date']))?></td>
											<td><?=round($amount,2);?></td>
											<td><?=round($row['cgst_rate'],2);?></td>
											<td><?=round($row['sgst_rate'],2);?></td>
											<td><?=round($row['igst_rate'],2);?></td>
										</tr>
									</tbody>
								<?php $cnt++; } ?>
								
								<tr>
									<th colspan="4" class="text-right">Total:</th>
									<th><?=round($total,2);?></th>
									<th><?=round($cgst,2);?></th>
									<th><?=round($sgst,2);?></th>
									<th><?=round($igst,2);?></th>
								</tr>
							</table>
							
							<?php } ?>
							
							
							
							<?php 
								if($type=='unreg_supply')
								{
			
									$q = "select i.invoice_id,i.cust_id,l.stateid,s.state_name,l.l_name,i.invoice_date,i.invoice_no,i.basic_total,(select sum(trn.igst_tax_rate*trn.currency_rate) from tbl_invoicetrn as trn where trn.invoice_id=i.invoice_id ) as igst_total from tbl_invoice as i left join tbl_ledger as l on l.l_id=i.cust_id left join state_mst as s on s.stateid=l.stateid where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg='1' and l.stateid='$state'";
										//echo $q;
									$query = $dbcon->query($q);							
								
							?>
						
							<table class="table table-bordered table-hover" id="">
								<thead>
									<tr>
										<th>#</th>
										<th>Customer</th>
										<th>Invoice No</th>
										<th>Invoice Date</th>
										<th>Taxable Amount</th>
										<th>IGST</th>
									</tr>
								</thead>
								<?php 
									
									$cnt=1;$total=0;$igst=0;
									while($row = brp_mysqli_fetch_assoc($query))
									{
										$amount = $row['basic_total'];
										$total+=$amount;
										$igst+=$row['igst_total'];
										
								?>
									<tbody>
										<tr>
											<td><?=$cnt;?></td>
											<td><?=$row['l_name']?></td>
											<td><a href="<?=ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'];?>" target="_blank"><?=$row['invoice_no']?></a></td>
											<td><?=date("d/m/Y",strtotime($row['invoice_date']))?></td>
											<td><?=round($amount,2);?></td>
											<td><?=round($row['igst_total'],2);?></td>
										</tr>
									</tbody>
								<?php $cnt++; } ?>
								
								<tr>
									<th colspan="4" class="text-right">Total:</th>
									<th><?=$total;?></th>
									<th><?=$igst;?></th>
								</tr>
							</table>
							
							<?php } ?>

							<!--- Composition supply -->

							<?php 
								if($type=='composition_supply')
								{
			
									$q = "select i.invoice_id,i.cust_id,l.stateid,s.state_name,l.l_name,i.invoice_date,i.invoice_no,i.basic_total,(select sum(trn.igst_tax_rate*trn.currency_rate) from tbl_invoicetrn as trn where trn.invoice_id=i.invoice_id ) as igst_total from tbl_invoice as i left join tbl_ledger as l on l.l_id=i.cust_id left join state_mst as s on s.stateid=l.stateid where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg='2' and l.stateid='$state'";
										//echo $q;
									$query = $dbcon->query($q);							
								
							?>
						
							<table class="table table-bordered table-hover" id="">
								<thead>
									<tr>
										<th>#</th>
										<th>Customer</th>
										<th>Invoice No</th>
										<th>Invoice Date</th>
										<th>Taxable Amount</th>
										<th>IGST</th>
									</tr>
								</thead>
								<?php 
									
									$cnt=1;$total=0;$igst=0;
									while($row = brp_mysqli_fetch_assoc($query))
									{
										$amount = $row['basic_total'];
										$total+=$amount;
										$igst+=$row['igst_total'];
										
								?>
									<tbody>
										<tr>
											<td><?=$cnt;?></td>
											<td><?=$row['l_name']?></td>
											<td><a href="<?=ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'];?>" target="_blank"><?=$row['invoice_no']?></a></td>
											<td><?=date("d/m/Y",strtotime($row['invoice_date']))?></td>
											<td><?=round($amount,2);?></td>
											<td><?=round($row['igst_total'],2);?></td>
										</tr>
									</tbody>
								<?php $cnt++; } ?>
								
								<tr>
									<th colspan="4" class="text-right">Total:</th>
									<th><?=$total;?></th>
									<th><?=$igst;?></th>
								</tr>
							</table>
							
							<?php } ?>

							<!--- UIN Holder supply -->

							<?php 
								if($type=='uin_supply')
								{
			
									$q = "select i.invoice_id,i.cust_id,l.stateid,s.state_name,l.l_name,i.invoice_date,i.invoice_no,i.basic_total,(select sum(trn.igst_tax_rate*trn.currency_rate) from tbl_invoicetrn as trn where trn.invoice_id=i.invoice_id ) as igst_total from tbl_invoice as i left join tbl_ledger as l on l.l_id=i.cust_id left join state_mst as s on s.stateid=l.stateid where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg='4' and l.stateid='$state'";
										//echo $q;
									$query = $dbcon->query($q);							
								
							?>
						
							<table class="table table-bordered table-hover" id="">
								<thead>
									<tr>
										<th>#</th>
										<th>Customer</th>
										<th>Invoice No</th>
										<th>Invoice Date</th>
										<th>Taxable Amount</th>
										<th>IGST</th>
									</tr>
								</thead>
								<?php 
									
									$cnt=1;$total=0;$igst=0;
									while($row = brp_mysqli_fetch_assoc($query))
									{
										$amount = $row['basic_total'];
										$total+=$amount;
										$igst+=$row['igst_total'];
										
								?>
									<tbody>
										<tr>
											<td><?=$cnt;?></td>
											<td><?=$row['l_name']?></td>
											<td><a href="<?=ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'];?>" target="_blank"><?=$row['invoice_no']?></a></td>
											<td><?=date("d/m/Y",strtotime($row['invoice_date']))?></td>
											<td><?=round($amount,2);?></td>
											<td><?=round($row['igst_total'],2);?></td>
										</tr>
									</tbody>
								<?php $cnt++; } ?>
								
								<tr>
									<th colspan="4" class="text-right">Total:</th>
									<th><?=$total;?></th>
									<th><?=$igst;?></th>
								</tr>
							</table>
							
							<?php } ?>



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
	<?php include_once($include.'footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include.'include_js_file.php');?>   
    <!--<script src="js/count.js"></script>-->
		<script>
$(document).ready(function() {		
	$('#example').DataTable();
});
		
$(".select2").select2({
		width: '100%'
	});
	$('.default-date-picker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true
        });
		function cb(start, end) {
        $('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    cb(moment().subtract(29, 'days'), moment());
	
  
    $('.datepikerdemo').daterangepicker({       
 			locale: {
				format: 'DD-MM-YYYY'
			},
		 "autoApply": true,	
		"startDate": $('#from_date').val(),
		"endDate": $('#to_date').val(),	
	    ranges: {
           //'Today': [moment(), moment()],
           //'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           //'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
		   'Last 3 Month': [moment().subtract(3, 'months'), moment().endOf('month')],
		   'Last 6 Month': [moment().subtract(6, 'months'), moment().endOf('month')],
		   'Last 1 Year': [moment().subtract(12, 'months'), moment().endOf('month')]
        }
    }, cb);
	$('.date-set').click(function(){
       $('.datepikerdemo').trigger('click')
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
<script>
var tableToExcel = (function() {
 var uri = 'data:application/vnd.ms-excel;base64,'
   , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
   , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
   , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
 return function(table, name) {
   if (!table.nodeType) table = document.getElementById(table)
   var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
   window.location.href = uri + base64(format(template, ctx))
 }
})()

function PrintMe(DivID) {
$('#logo').css('display','');
var disp_setting="toolbar=yes,location=no,";
var content_vlue=$('#report_head').show();
disp_setting+="directories=yes,menubar=yes,";
disp_setting+="scrollbars=yes,width=800, height=600, left=100, top=25";
	
  content_vlue= document.getElementById(DivID).innerHTML;
  var docprint=window.open("","",disp_setting);
  docprint.document.open();
  docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
  docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
  docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
  docprint.document.write('<head><title><?=TITLE?></title>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');

  docprint.document.write('<style type="text/css">body { margin:20px 10px 10px 35px;');
  docprint.document.write('font-family:Tahoma;color:#000;');
  docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
  docprint.document.write('#mainpart table,#mainpart tr,#mainpart td,#mainpart th {border:1px #eee solid;padding:2px 5px 2px 5px;text-align:center;}');
  docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } </style>');
  docprint.document.write('</head><body onLoad="self.print()"><center>');
  docprint.document.write(content_vlue);
  docprint.document.write('</center></body></html>');
  docprint.document.close();
  $('#report_head').hide()
  docprint.focus();
  
$('#logo').css('display','none');
}
</script>
  </body>
</html>
