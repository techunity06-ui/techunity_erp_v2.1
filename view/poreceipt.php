<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/coman_function.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Po Print";
		$mode="Print";
		$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);
		$query="select *,state.state_name,city.city_name,cust.* from tbl_povender as invoice inner join tbl_customer as cust on cust.cust_id=invoice.cust_id
		inner join state_mst as state on state.stateid=cust.stateid
		inner join city_mst as city on city.cityid=cust.cityid
		where povender_id=$invoiceid";
		$rel=mysqli_fetch_assoc($dbcon->query($query));		
		
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
<style>
@media all
   {
	body:{letter-spacing:0.1px !important;line-height:1em !important;}
      p.bodyText {font-size: 10px;}	  
	 td, th {
		padding-left: 5px;
		/*font-size: x-small !important;*/
		} 
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
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
						<div class="panel-body">
								<center>
									<div class="col-md-3"> </div>With Logo
							<br>
						<?php
								if($_REQUEST['printstatus']==''){?>
								
								<label class="col-md-3 control-label">Select Print</label>
								<div class="col-md-4 col-xs-11">
								<form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
										<select class="form-control" name="print_status" id="print_status">
										<option value="">Select Print</option>
										<option value="1">ORIGINAL</option>
										<option value="2">DUPLICATE</option>
										<option value="3">TRIPLICATE</option>
										<option value="4">EXTRA</option>
										</select>
									</form>
								</div>
								<div class="col-md-1">
							
								<?php}
								else 
								{
								?>
								<input type="hidden" name="print_status" id="print_status" value="<?=$_REQUEST['printstatus']?>">
									<div class="col-md-7"></div>
									<div class="col-md-1">
							
								<?php 
								}
								?>
								<input type="checkbox" class="form-control"  name="logo" id="logo" value="1">
								
								</div>	
									<center><button type="submit" class="btn btn-danger" onClick="PrintMe('receipt_print');">Print</button>
			<a href="<?=ROOT.'po_venderlist'?>" type="button" class="btn btn-success">Cancel</a>
			</center>

			<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="form-group col-md-12" style="margin-left:0px;" id="print1">
					<?php 
						$set="select * from tbl_setting";
						$set_head=mysqli_fetch_assoc($dbcon->query($set));		
					?>
					
						<table width="100%" border="0" style="margin-top:20px;">
							<tr id="table_head">
							<td  colspan="6"  style="text-align:right;border-left:none;border-top:none;border-bottom:none;padding-left:0px;"> 
							<img src="<?=ROOT.LOGO.$set_head['logo']?>" height="100%" width="100%"/>
							</td>
							</tr>
							<tr>
							<td colspan="6">
						<table width="100%" border="0" id="invoice_type">
							<tr>
							<td colspan="6" style="text-align:center"><h3><strong style="font-size:15px;">Purchase Order </strong></h3>
							<h6 style="text-align:right"><strong><b class="data_title" >ORIGINAL</b></strong></h6>
							</td>
							</tr>
						</table>	
						<table border="1" style="font-size:12px"  width="100%">
							<tr>
									<td colspan="3" style="border-bottom-color:#FFFFFF"><strong>To:M/s  <?=$rel['company_name']?></strong></td>
									<td colspan="2" style="border-bottom-color:#FFFFFF;border-right-color:#FFFFFF">PO No:.<strong><?=$rel['po_no']?> </strong>
									</td>
							</tr>
							<tr>
									<td colspan="3" style="border-bottom-color:#FFFFFF"><p style=" margin-bottom:0px"><strong><?=$rel['cust_address']?></strong></p></td>
									<td colspan="2" style="border-bottom-color:#FFFFFF;border-right-color:#FFFFFF;vertical-align:top">Date.: <strong><?=date('d/m/Y',strtotime($rel['po_date']))?></strong> </td>
									</tr>
							<tr>
									<td colspan="3" style="border-bottom-color:#FFFFFF" ><p style=";margin-bottom:0px"><strong><?=$rel['city_name']?> <?=$rel['state_name']?></strong></p></td>
									<td colspan="2" style="border-right-color:#FFFFFF;border-bottom-color:#FFFFFF;">
									<strong></strong>
									</td>
							</tr>
							<tr>
									<td colspan="3" style="" ><p style=";margin-bottom:0px">Phn No: <strong><?=$rel['cust_mobile']?></strong></p></td>
									<td colspan="2" style="border-right-color:#FFFFFF;">Quotation No :
									<?php echo '<strong>'.$rel['pq_no'].'</strong>';?></strong>
										</td>
							</tr>
							<tr>

									<td width="5%" style="text-align:center;vertical-align:bottom;">NO.</td>
									<Td width="55%" style="text-align:center;vertical-align:bottom;">Description</Td>
									<td width="10%" style="text-align:center;vertical-align:bottom;">Quantity.</td>
									<td width="15%" style="text-align:center;vertical-align:top">Rate/per</td>
									<td width="" style="text-align:center">Amount</td>
							</tr>
									<?php
								 $qry="select  mst.unit_name,trn.* FROM `tbl_povendertrancation` as trn left join unit_mst as mst on mst.unitid=trn.unitid where trn.status=0 AND povender_id=".$rel['povender_id'];
									$result=$dbcon->query($qry);		
									$i=1;$total=0;
									$cnt=mysqli_num_rows($result);
									while($row=mysqli_fetch_assoc($result))
									{
									?>
									<tr>
										<td style="text-align:center;border-bottom-color:#FFFFFF;vertical-align:top"><?=$i?></td>
										<td style="border-bottom-color:#FFFFFF;vertical-align:top">
											<?php 
										 $qry1="select * from  tbl_product where product_id=".$row['product_id'];
										$row1=mysqli_fetch_assoc($dbcon->query($qry1));		
											echo '<strong>'.stripcslashes($row1['product_name'])."<br>";
											echo stripcslashes($row1['product_des']).'</strong>'
										?>
										</td>
										<td style="text-align:center;padding-right:10px;border-bottom-color:#FFFFFF;vertical-align:top;"><strong><?=$row['product_qty']?>  <?=$row['unit_name']?>.</strong></td>
										
										<td style="text-align:center;padding-right:10px;border-bottom-color:#FFFFFF;vertical-align:top;"><strong><?=$row['product_rate']?></strong></td>
										<td style="text-align:right;padding-right:10px;border-bottom-color:#FFFFFF;vertical-align:top;"><strong><?=$row['product_amount']?></strong></td>
									</tr>
									<?php$i++; $total=$total+$row['product_amount'];
									}
									$pr=15-$cnt;
									for($j=0; $j<$pr; $j++)
									{
										?>
								<tr height="12px">
									<td style="border-bottom-color:#FFFFFF"></td>
									<td style="border-bottom-color:#FFFFFF"></td>
									<td style="border-bottom-color:#FFFFFF"></td>
									<td style="border-bottom-color:#FFFFFF"></td>
									<td style="border-bottom-color:#FFFFFF"></td>
							</tr>
								<?php} ?>
							<tr height="1px">
									<td style="border-top:1px solid blank;"></td>
									<td style=""></td>
									<td style=""></td>
									<td style=""></td>
									<td style=""></td>
							</tr>
							<tr>
									<td colspan="4" style="text-align:right;"><strong>Packing Charges & Forwarding </strong></td>
									<td style="text-align:right;">
									<strong><?=number_format($rel['packing'],2,".","")?></strong></td>
							</tr>
							<?php 
							if(!empty($rel['tax1_name']))
							{
								if(strpos($rel['tax1_name'], "2%")==true)
								{
									$c='Against Form "C"';
								}
								
							?>
							<tr>
									<td colspan="3" style="border-right-color:#FFFFFF">
									<?=$c?></td>
									<td style=""><strong><?=$rel['tax1_name']?> </strong></td>
									<td style="text-align:right;">
									<strong><?=$rel['taxvalue1']?></strong></td>
							</tr>
							<?php} ?>
							<?php 
							if(!empty($rel['tax2_name']))
							{
								if(strpos($rel['tax2_name'], "2%")==true)
								{
									$c='Against Form "C"';
								}
								
							?>
							<tr>
									<td colspan="3" style="border-right-color:#FFFFFF">
									<?=$c?></td>
									<td style=""><strong><?=$rel['tax2_name']?> </strong></td>
									<td style="text-align:right;">
									<strong><?=$rel['taxvalue2']?></strong></td>
							</tr>
							<?php} ?>
							<?php 
							if(!empty($rel['tax3_name']))
							{
								if(strpos($rel['tax3_name'], "2%")==true)
								{
									$c='Against Form "C"';
								}
							?>
							<tr>
									<td colspan="3" style="border-right-color:#FFFFFF">
									<?=$c?></td>
									<td style=""><strong><?=$rel['tax3_name']?> </strong></td>
									<td style="text-align:right;">
									<strong><?=$rel['taxvalue3']?></strong></td>
							</tr>
							<?php} ?>
							<tr>
									<td colspan="3" style="border-right-color:#FFFFFF;border-bottom-color:#FFFFFF;"><p style=";margin-bottom:0px">TIN GST 
									<strong><?=$set_head['gstno']?></strong>
									Dt : <strong><?=date('d/m/Y',strtotime($set_head['gst_date']))?></strong>
									</p></td>
									<td  style="border-bottom-color:#FFFFFF;"><strong>Total</strong></td>
									<td style="text-align:right;border-bottom-color:#FFFFFF;">
									<strong><?=number_format($rel['amount'],2,'.','')?></strong></td>
							</tr>
							<tr>
									<td colspan="3" style="border-right-color:#FFFFFF;"><p style=";margin-bottom:0px">TIN CST 
									<strong><?=$set_head['cstno']?></strong>
									Dt : <strong><?=date('d/m/Y',strtotime($set_head['cst_date']))?></strong>
									</p></td>
									<td  style=""></td>
									<td style="text-align:right;">
									<strong></strong></td>
									
								
							</tr>
							<tr>
									<td colspan="3" style="border-right-color:#FFFFFF;">
									<strong>(Rupees : <?=convert_number_to_words($rel['amount'])?>  Only)</strong>  </td>
									<td  style=""><strong></strong></td>
									<td style="text-align:right;">
									<strong></strong></td>
							
							</tr>
							<tr>
									<td colspan="2" style="border-bottom-color:#FFFFFF;">
									Terms & Conditions :-
									<br>
									<table width="100%">
									<tr>
									<td><strong> Delivery </strong></td><td>:<?=$rel['delivery']?></td>
									</tr>
									<tr>
									<td><strong>Payment </strong></td><td>:<?=$rel['payment']?></td>
									</tr>
									<tr>
									<td><strong> Freight & Transportation </strong></td><td>:<?=$rel['transportation']?></td>
									</tr>
									<tr><td><strong> Sales tax </strong></td><td>:<?=$rel['tax1_name']?> , <?=$rel['tax2_name']?>   ,  <?=$rel['tax3_name']?></td>
									</tr>
									</table>
								<td colspan="3" style="border-right-color:#FFFFFF;text-align:right;vertical-align:top">
									For, <strong><?=$set_head['title']?></strong>
									<br>
									<br><br><br><br>
									<strong>Authorised Signatory</strong>
									</td>
							</tr>
							</table> 
		</td>
							<td width="2%">
							</td>
							</tr>
						</table>
						<div style="margin-top:12px;margin-right:10px;" id="footer">
						<img src="<?=ROOT.LOGO.$set_head['f_logo']?>" height="100%" width="98%"/>
						</div>	
					</div>
					<div id="print2"></div>
					<div id="print3"></div>
					</center>
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
alert('Select print');
}
else
{
if ($('input[name=logo]:Checked').val() == "1") {
   
	$('#table_head').show();
	$('#footer').show();
	$('#invoice_type').css('margin','0px');	
}
else
{
	$('#table_head').hide();
	$('#footer').hide();
	$('#invoice_type').css('margin','100px 0px 0px 0px');	
	$('#footer').css('margin-bottom','150px');
}
	for(var i=1;i<=$('#print_status').val();i++)
{	
	if(i<$('#print_status').val())
	{
		$("#print"+i).after('<div class="page"></div>');
	}
	$("#print"+i).html($("#print1").clone());
	if(i==2)
	{
		$("#print"+i+" .data_title").html('DUPLICATE');
	}
	if(i==3)
	{
		$("#print"+i+" .data_title").html('TRIPLICATE');
	}
	
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

  docprint.document.write('<style type="text/css">body { margin:0px 0px 0px 0px;');
  docprint.document.write('font-family:Tahoma;color:#000;');
  docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
  docprint.document.write('#mainpart table,#mainpart tr,#mainpart td,#mainpart th {border:1px #eee solid;padding:2px 5px 2px 5px;text-align:center;}');
  docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-before: always; page-break-inside: avoid; } </style>');
  docprint.document.write('</head><body onLoad="self.print()"><center>');
  docprint.document.write(content_vlue);
  docprint.document.write('</center></body></html>');
  docprint.document.close();
  docprint.focus();
	$('#table_head').show();
	$('#footer').show();
	$('#invoice_type').css('margin-top','0px');

  }
}
</script>


  </body>
</html>
