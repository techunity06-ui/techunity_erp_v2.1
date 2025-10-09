<?php 
	session_start();
	include('../include/urlfile.php');	
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$_SESSION['contents']='';
	$form="Warehouse";
	$mode="Print";
	$bom_id = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select bom.*,product.product_name from tbl_bom as bom 
		left join product_mst as product on product.product_id=bom.bom_product
		where bom_id=$bom_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	
	$set="select * from tbl_company where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$sel1=$dbcon->query("select sum(product_qty)as sqty from tbl_bomtrn where bom_id='$bom_id'");
	$row1=mysqli_fetch_assoc($sel1);
	
	$totalqty=$row1['sqty'];
	//echo $row1['sqty'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Print Warehouse</title>
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
							  <li><a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>"><?=$form?> List</a></li>
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
					<a href="<?=ROOT.PRODUCTION_ROOT.'bom_list'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
					<!--<input type="button" name="printpdf" id="printpdf" class="btn btn-default" value="Export to Pdf" onClick="make_pdf()" />-->
				</div>
		</center>	
			<div class="col-md-12"></div>
				<label class="col-md-3 control-label"></label>
					<div class="col-lg-4"></div>
<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
<?php ob_start(); ?>
<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
<table width="100%" class="maintable" border="1" id="table_head" style="border-radius:6px;border-collapse: separate; border-width: 2px;border-color: black;" >
	<thead>
		<tr>
			<th style="border: none;padding:5px !important;" width="50%"> 
				<img src="<?=ROOT.LOGO.'fixed_logo.png'?>" style="width:100%;padding: 2px;"/>
				<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>" style="width:100%"/>-->
			</th>
			<th style="text-align:left;border: none;"> 
				<?=$set_head['address']?> 
				<?phpif($set_head['contact_no']){?><br/>Contact No. <?=$set_head['contact_no']?><?}?>
				<?phpif($set_head['website']){?><br/>E-Mail: <?=$set_head['website']?><?}?>
			</th>
		</tr>
	</thead>
</table>	
<!--	
	<table width="100%" class="maintable" border="1" id="table_head" style="border-radius:6px;border-collapse: separate; border-width: 2px;    border-color: black;" >
		<tr style="border:none;">
			<td width="100%" style="border:none;"> 
				
				<h2 align="center" style="font-weight:600;"><u><?=$set_head['company_name']?></u></h2>
				<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX;  !important"><?=$set_head['logo_content']?></h4>
				<h4 align="center" style="padding:top:15px;margin-top: 10PX;margin-bottom: 0PX; font-weight:lighter; !important"><?=$set_head['address']?></h4>
				<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX; font-weight:lighter; !important"><?if($set_head['website']){?><?}?> 
				<?if($set_head['contact_no']){?>Contact No. <?=$set_head['contact_no']?><?}?></h4>
				<h4 align="center" style="padding:top:0px;margin-top: 0PX;margin-bottom: 0PX; font-weight:lighter; !important"><?if($set_head['website']){?><?}?> 
				<?if($set_head['website']){?>E-Mail: <?=$set_head['website']?><?}?></h4>
				
			</td>
		</tr>
	</table>
	-->
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
<!-- Multi Page Challan Start -->				
<table width="100%" class="maintable" style="font-size: 12px;" id="invoice_type" >
	<thead>
		<tr>
			<th colspan="7" style="padding:0px !important;">
				<table  border="0" style="font-size:12px;border-collapse:collapse;" cellpadding="0"  cellspacing="0" width="100%" id="">
					<!--<thead>-->
						<tr>
							<td width="10%" style="vertical-align:top;border:1px solid;border-right:none;"><strong>Process Name </strong>
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
								: <?=$rel['bom_qty']?>							
							</td>	
							
						</tr>
						
					<!--</thead>-->	
				</table>
			</th>
		</tr>
		<?php
			
			$q1="select "; 
		
		?>
		<tr height="30px">	
		
			<th  width="5%" style="text-align:center;border:1px solid;border-top:none;">
				<strong>SR. NO.</strong>
			</th>
			
			<th width="45%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Godown Name</strong></th>
			
			<th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Qty</strong></th>
		
			<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Unit</strong></th>
			
			<th width="20%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Process</strong></th>
			
		</tr>
	</thead>
	<tbody style="border: 1px solid;">
		<?php
			$qry="select *,per.unit_name FROM `tbl_bomtrn` as trn 
			left join product_mst as product on product.product_id=trn.product_id 
			left join unit_mst as per on per.unitid=trn.product_uom
			where bom_trn_status!=1 and bom_id='$rel[bom_id]' and parent_id='0'";
			//echo $qry;
			$result=$dbcon->query($qry);		
			$i=1;$total=0;$discount=0;
			$cnt1=mysqli_num_rows($result);
			$cnt=1;
			while($row=mysqli_fetch_assoc($result))
			{
				$number="1.".$cnt;
			echo '
			<tr>';
					
				get_tree_bom($dbcon,$row['product_id'],$row['parent_id'],0,$cnt,$bom_id,$number,$row['product_qty'],$row['bom_trn_id'],$row['unit_name']);
					
			echo '</tr>';
		
			$cnt++;$i++; 
			$total=$total+=$row['product_amount'];
			
			$totalsqr=$totalsqr+$row['sqr_ft'];
			}
			$pr=15-$cnt1;
			for($j=0; $j<$pr; $j++)
			{
		?>
				<tr style="height:40px">
					<td style="border-right:1px solid;border-left:1px solid;"></td>
					<td style="border-right:1px solid;"></td>
					<td style="border-right:1px solid;"></td>
					<td style="border-right:1px solid;"></td>
					<td style="border-right:1px solid;"></td>
					<td style="border-right:1px solid;"></td>
					<td style="border-right:1px solid;"></td>
				<!--	<td style="border-right:1px solid;"></Td>-->
					<!--<td style="border-right:1px solid;"></td>-->
					<!--<td style="border-right:1px solid;"></td>
					<td style="border-right:1px solid;"></td>-->
				</tr>
		<?php} ?>
		
		<tr height="24px">
			<td colspan="3" style="border-top:1px solid;border-bottom:1px solid;border-right:1px solid;border-left:1px solid;font-size:14px;text-align:right;">TOTAL</td>
			<td style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; "><?=number_format($totalqty,5,".","")?></td>
			<td colspan="3" style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; "></td>
		
			<!--<td style="text-align:center;border-bottom:1px solid; border-top:1px solid;font-size:14px;border-right:1px solid; "><?=number_format($totalsqr,5,".","")?></td>-->
			<!--<td style="border-right:1px solid;border-top:1px solid; ">
			</td>-->
		</tr>	
		
		
	</tbody>	
</table>
				<!--<td colspan="4" style="padding: 0px !important;border:1px solid">
			<table class="footer-table" width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%"/>
						</td>
					</tr>
				</table>-->
				
				<!--<table class="footer-table" width="100%">
					<tr style="border-bottom:none;">
						<td colspan="2" style="border-top:1px solid;">
						<?if(!empty($set_head['vatno'])){ ?>
							<strong>COMPANY GST No. : <?=$set_head['vatno']?> 
						<?php} ?>
						</td>
						<td style="border-top:1px solid;">
							<span style="font-size:12px;float:right;">For, <strong><?=$set_head['company_name']?></strong></span>
						</td>
					</tr>
					
					<tr height="50px" style="border-bottom:none;">
					<td colspan="2"  style="">
							<?phpif(!empty($set_head['challan_condition'])){ ?>
								<strong>Terms and Conditions:</strong><br> <?=$set_head['challan_condition']?>
							<?php} ?><br/>
					</td>
					<td style="vertical-align:top;text-align:left;border-right:1px solid;">
					
					</td>
					</tr>
					<tr height="20px">
						<td colspan="2" style="vertical-align:bottom;border-bottom:1px solid;"> 
								<br/>Receiver's Signature	
						</td>
						 
						<td style="text-align:right;vertical-align:bottom;border:1px solid;border-left:none;border-top:none;border-left:none;">
							Authorised Signature
						</td>
					</tr>-->
				
<!-- Multi Page Challan End -->				
				 
						
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
