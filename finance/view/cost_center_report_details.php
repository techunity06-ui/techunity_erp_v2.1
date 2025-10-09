<?php 
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path."config/config.php");
	include_once($path."config/session.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
	
	$cost_center_id = isset($_GET['center_id'])?$_GET['center_id']:0;
	$st_date=date("Y-m-d",strtotime($_SESSION['start']));
	$end_date=date("Y-m-d",strtotime($_SESSION['end']));
	
	$company_row = get_company_data($dbcon,$_SESSION['company_id']);
	$company_state=$company_row['stateid'];
	
	//echo $cost_center_id;
	
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
								<li ><a href="<?=ROOT.FINANCE_ROOT.'report_cost_center.php'?>">Cost Center Report</a></li>
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
					<?php 
						
						if(isset($cost_center_id))
						{
							$selc=$dbcon->query("select cost_center_name from tbl_cost_center where cost_center_id='$cost_center_id'");
							$row_c=brp_mysqli_fetch_array($selc);
							echo "<h3>".$row_c['cost_center_name']."</h3>";
						}
						
					?>
				</header>
				<div class="panel-body">
					
					<div class="row">
								
						<div class="col-md-12">
							
							<?php 
								
								if(isset($cost_center_id))
								{
							?>
							
								<table class="table table-bordered">
									
									<tr>
										<th>#</th>
										<th>Date</th>
										<th>Voucher Type</th>
										<th>Voucher No</th>
										<th>Amount</th>
										<th>Type</th>
									</tr>
									
									<?php 
										
										$cnt=1;
										$sel = $dbcon->query("select * from tbl_cost_center_transaction where isdelete='0' and  costcenter_id='$cost_center_id'" );
										while($row=brp_mysqli_fetch_array($sel))
										{
											if($row['cost_center_table']=='tbl_invoice')
											{
												$sel1=$dbcon->query("select invoice_date,invoice_no from tbl_invoice where invoice_id='$row[cost_center_table_id]'");
												$row1=brp_mysqli_fetch_assoc($sel1);
												
												$voucher_type = "Sale";
												$voucher_no = $row1['invoice_no'];
												$date = date("d/m/Y",strtotime($row1['invoice_date']));
												$href=ROOT.FINANCE_ROOT."invoiceedit/".$row['cost_center_table_id'];
											}
											
											if($row['cost_center_table']=='tbl_pono')
											{
												$sel1=$dbcon->query("select po_date,po_no from tbl_pono where po_id='$row[cost_center_table_id]'");
												$row1=brp_mysqli_fetch_assoc($sel1);
												
												$voucher_type = "Purchase";
												$voucher_no = $row1['po_no'];
												$date = date("d/m/Y",strtotime($row1['po_date']));
												$href=ROOT.FINANCE_ROOT."purchaseedit/".$row['cost_center_table_id'];
											}
											
											if($row['cost_center_table']=='tbl_sale_return')
											{
												$sel1=$dbcon->query("select sale_return_date,sal_return_voucher_no from tbl_sale_return where sale_return_id='$row[cost_center_table_id]'");
												$row1=brp_mysqli_fetch_assoc($sel1);
												
												$voucher_type = "Sale Return";
												$voucher_no = $row1['sal_return_voucher_no'];
												$date = date("d/m/Y",strtotime($row1['sale_return_date']));
												$href=ROOT.FINANCE_ROOT."salereturnedit/".$row['cost_center_table_id'];
											}
											
											if($row['cost_center_table']=='tbl_debitnote')
											{
												$sel1=$dbcon->query("select debitnote_date,debitnote_no from tbl_debitnote where debitnote_id='$row[cost_center_table_id]'");
												$row1=brp_mysqli_fetch_assoc($sel1);
												
												$voucher_type = "Purchase Return";
												$voucher_no = $row1['debitnote_no'];
												$date = date("d/m/Y",strtotime($row1['debitnote_date']));
												$href=ROOT.FINANCE_ROOT."debitnoteedit/".$row['cost_center_table_id'];
											}
											
									?>
										
										<tr>
											<th><?=$cnt;?></th>
											<th><?=$date;?></th>
											<th><?=$voucher_type;?></th>
											<th><a href="<?=$href;?>"><?=$voucher_no;?></a></th>
											<th><?=$row['costcenter_amount'];?></th>
											<th>
												<?php if($row['costcenter_entry_type']=='1'){ echo "<strong style='color:green'>Credit</strong>"; } else { echo "<strong style='color:red'>Debit</strong>"; } ?>
											</th>
										</tr>
										
									<?php $cnt++; } ?>
								</table>
							
							<?php
								}
								
							?>
							
							
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
