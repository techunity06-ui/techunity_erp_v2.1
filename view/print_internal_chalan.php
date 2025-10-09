<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$_SESSION['contents']='';
	$form = "Internal Chalan";
	$mode = "Print";
	$id = $dbcon->real_escape_string($_REQUEST['id']);
	
	$query = "SELECT *, c.complaint_no, c.complaint_date, l.l_name, l.m_address, l.cust_mobile,l.gst_no,
		l.cust_pincode, country.country_name, st.state_name, city.city_name
		FROM tbl_internal_chalan tic 
		JOIN tbl_complaint c ON c.complaint_id = tic.complaint_id
		JOIN tbl_ledger l ON l.l_id = c.cust_id
		LEFT JOIN country_mst AS country ON country.countryid = l.countryid
		LEFT JOIN state_mst AS st ON st.stateid = l.stateid
		LEFT JOIN city_mst AS city ON city.cityid = l.cityid
		WHERE tic.complaint_id = '$id'";
		
	$exe_qry = $dbcon->query($query);
	$rel = brp_mysqli_fetch_assoc($dbcon->query($query));	
	$company_name = $rel['l_name'];
	$cust_address = $rel['m_address'];
	$city_name = $rel['city_name'];
	$state_name = $rel['state_name'];
	$country_name = $rel['country_name'];
	$cust_pincode = $rel['cust_pincode'];
	$gst_no = $rel['gst_no'];

	$set = "SELECT * FROM tbl_company WHERE company_id = ".$rel['company_id'];
	$set_head = brp_mysqli_fetch_assoc($dbcon->query($set));	
	$order_date = ''; $dispatch_date = '';
	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
	{
		$order_date=date('d/m/Y',strtotime($rel['order_date']));
	}
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
    padding: 0px 2px !important;
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
							  <li ><a href="<?=ROOT.'spare_list_pending'?>">Spare Part List To Be Sent</a></li>
							  
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
				  <header class="panel-heading">
					  New <?=$form?>
					</header>	
						<div class="panel-body">
		<center>
			<div style="display: none;">
				<div class="col-md-1"> </div>With Logo
						<br/>
							<label class="col-md-2 control-label"> Print</label>
				<div class="col-md-4 col-xs-11">
				 <form class="form-horizontal" role="form" id="print_form" action="javascript:;" method="post" name="print_form">
					<select class="form-control" name="print_status" id="print_status" <?php if($_REQUEST['printstatus']!=''){ echo "readonly";}?>>
						<option value="">Select Print</option>
						<option value="1" selected>ORIGINAL</option>
						<option value="2">DUPLICATE</option>
						<option value="3">TRIPLICATE</option>
						<option value="4">EXTRA</option>
					</select>
				 </form>
				</div>
				<div class="col-md-1">
					<input type="checkbox" class="form-control" checked name="logo" id="logo" value="1">
				</div>
			</div>
				<div class="col-md-12" style="text-center">
					<button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
					<a href="<?=ROOT.'spare_list_pending'?>" type="button" class="btn btn-danger"><i class="fa fa-ban"></i> Cancel</a>
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
<!--			
<table width="100%" class="maintable" border="1" id="table_head" style="border-radius:6px;border-collapse: separate; border-width: 2px;border-color: black;" >
	<thead>
		<tr>
			<th style="border: none;padding:5px !important;" width="50%"> 
				<img src="<?=ROOT.LOGO.'fixed_logo.png'?>" style="width:100%;padding: 2px;"/>
				<!--<img src="<?=ROOT.LOGO.$set_head['logo']?>" style="width:100%"/>--
			</th>
			<th style="text-align:left;border: none;"> 
				<?=$set_head['address']?> 
				<?phpif($set_head['contact_no']){?><br/>Contact No. <?=$set_head['contact_no']?><?php }?>
				<?phpif($set_head['website']){?><br/>E-Mail: <?=$set_head['website']?><?php }?>
			</th>
		</tr>
	</thead>
</table>
-->
							
	<table width="100%" class="maintable" border="0" style="" id="table_head">
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
<!-- Multi Page Challan Start -->				
<table width="100%" class="maintable" style="font-size: 12px;" id="invoice_type" >
	<thead>
		<tr>
			<th colspan="8" style="text-align: center;">
				<strong style="font-size:16px; text-transform: uppercase;"><?=$form?></strong>
			</th>
		</tr>
		</tr>
			<th colspan="6" style="padding:0px !important;">
				<table border="0" style="font-size:12px;border-collapse:separate;" cellpadding="0"  cellspacing="0" width="100%" id="">
					<!--<thead>-->
						<tr>
							<td rowspan="4" width="55%" style="vertical-align:top;border:1px solid;padding-bottom:0px !important;">
							M/s,<br>
								<strong><?=$company_name?></strong>
								<span style="font-weight:normal;"> <br>
								<?=$cust_address?>,<br/>
								  <?=$city_name?>,
								  <?=$state_name?>,
								  <?=$country_name?>
								  <?phpif(!empty($cust_pincode))
									{	?>
								 -  <?=$cust_pincode?>
									<?php} ?></span>
									<br> <strong> GSTIN : <?=$gst_no?></strong>
								 
							</td>
						  
							<td width="15%" style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;"><strong>Internal Chalan No</strong>
							</td>
							 
							<td style="vertical-align:top;border-bottom:1px solid;border-top:1px solid;border-right:1px solid;">: <strong><?=$rel['int_chalan_no']?></strong>
							</td>							
						</tr>
						<tr>
							<td style="vertical-align:top;border-bottom:1px solid;">
								<strong>Complain No</strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
								: <?=$rel['complaint_no']?>							
							</td>							
						</tr>
					
						<tr>
							<td style="vertical-align:top;border-bottom:1px solid;">
								<strong>Requested Date</strong>
							</td>
							<td style="vertical-align:top;border-bottom:1px solid;border-right:1px solid;">
								: <?=date('d-m-Y',strtotime($rel['complaint_date']))?>					
							</td>							
						</tr>
					<!--</thead>	-->
				</table>
			</th>
		</tr>
		<tr height="30px">					
			<th  width="5%" style="text-align:center;border:1px solid;border-top:none;">
				<strong>SR. NO.</strong>
			</th>
			<th width="45%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Description</strong></th>
			<th width="12%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Quantity</strong></th>
		</tr>
	</thead>
	<tbody style="border: 1px solid;">
		<?php
			
			$i=1;$total=0;$discount=0;
			$cnt=brp_mysqli_num_rows($exe_qry);
			 while($row=brp_mysqli_fetch_assoc($exe_qry))
			{
		?>
		<tr style="height:40px">
				<td style="text-align:center;vertical-align:top;border-right:1px solid;border-left:1px solid;">
					<?=$i?>
				</td>
				<td style="padding-left:5px;border-bottom-color:#FFFFFF; border-right:1px solid;vertical-align:top;" >
					<strong><?=stripcslashes($row['sp_name'])?></strong>
				</td>
				<td style="text-align:center;padding-right:10px;vertical-align:top;border-bottom-color:#FFFFFF; border-right:1px solid;" >
					<?=$row['total_qty']?>
				</td>
		</tr>
		<?php	
			$i++; 
			}
			$pr=16-$cnt;
			for($j=0; $j<$pr; $j++)
			{
		?>
				<tr style="height:40px">
					<td style="border-right:1px solid;border-left:1px solid;"></td>
					<td style="border-right:1px solid;"></td>
					<td style="border-right:1px solid;"></td>
				</tr>
		<?php} ?>
		<tr>
			<td colspan="6" style="padding: 0px !important;border:1px solid">
				<table class="footer-table" width="100%">
					<tr style="border-bottom:none;">
						<td colspan="2" style="">
						<?php if(!empty($set_head['vatno'])){ ?>
							<strong>COMPANY GST No. : <?=$set_head['vatno']?> 
						<?php} ?>
						</td>
						<td colspan="2" style="">
							<span style="font-size:12px;float:right;">For, <strong><?=$set_head['company_name']?></strong></span>
						</td>
					</tr>
					
					<tr height="50px" style="border-bottom:none;">
						<td colspan="2"  style="">
						</td>
						<td colspan="2" style="vertical-align:top;text-align:left;border-right:1px solid;">
						</td>
					</tr>
					<tr height="20px">
						<td colspan="2" style="vertical-align:bottom;"> 
								<br/>Receiver's Signature	
						</td>
						 
						<td  colspan="2" style="text-align:right;vertical-align:bottom;border-left:none;border-top:none;border-left:none;">
							Authorised Signature
						</td>
					</tr>
				</table>
				</td>
		</tr>
	</tbody>
			<!--<table width="100%" border="0" style="margin-top: 5px;" id="table_foot">
					<tr>
						<td style="border:none;padding:0px 0px !important;width:100%;"> 
							<img src="<?php //=ROOT.LOGO.$set_head['f_logo']?>"  style="width:100%"/>
						</td>
					</tr>
				</table>-->
			
</table>
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
