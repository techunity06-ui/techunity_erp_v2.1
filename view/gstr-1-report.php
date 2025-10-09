<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="GSTR 1 Report";

	$qry='Select invoice.*,trn.product_discount,trn.discount_per,sum(product_amount) as taxable_amt ,SUM(g_total) as g_total,cust.gst_no
			from tbl_invoice as invoice 
			left join tbl_invoicetrn as trn on trn.invoice_id=invoice.invoice_id 
			inner join tbl_ledger as cust on invoice.cust_id=cust.cust_id and gst_no!="" 
			where invoice_status=0  and invoice_date>="'.date('Y-m-d',strtotime($_SESSION['start'])).'" and invoice_date<="'.date('Y-m-d',strtotime($_SESSION['end'])).'" ';
	//$row=mysqli_fetch_assoc($dbcon->query($qry));

	
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('../include/include_css_file.php');?>
<style>
.icons{
    width: 14.5%;
    float: left;
    margin: 30px 7px 25px;
    text-align: center;
	position:relative;

}
.icons12{
background-color:#fff;
padding-top:15px;
    border: 8px;
}
 .icons p{
 text-align:center;
 font-size:15px;
 font-weight:600;
 padding-top:5px;
 font-color:white
 
 }
 
 .icon1 fa{

 }
 .icon1.success{background-color: #5cb85c;}
 .icon1.primary{background-color: #0275d8;}
 .icon1.warning{background-color: #f0ad4e;}
 .icon1.info{background-color: #5bc0de;}
 .icon1.danger{background-color: #d9534f;}
 .icon1.terques{background-color: #6ccac9;}
 .icon1.yellow{background-color: #f8d347;}
 .icon1.pink{background-color:#E5649A;}
 .icon1.mustard{background-color:#F0BD23;}
 .icon1.success,.icon1.primary,.icon1.warning,.icon1.danger,.icon1.info,.icon1.terques,.icon1.yellow,.icon1.pink,.icon1.mustard{
    width: 120px;
    height:100px;
    border-radius: 8px;
	text-align:center;
	margin:0 auto
 }
 .icon1.success i,.icon1.primary i,.icon1.warning i,.icon1.danger i,.icon1.info i,.icon1.terques i,.icon1.yellow i,.icon1.pink i,.icon1.mustard i{
 text-align:center;
 color:#fff;
     padding-top: 27%;
	font-size: 37px;
 }
 @media (max-width:767px){
.icons {
    width: 47%;
    float: left;
    margin: 30px 4px 25px;
	position:relative;
}

}
@media (min-width:768px) and (max-width:980px)
 {
 .icons12{
background-color:#fff;
padding-top:20px;
padding-bottom:20px;
   border-radius: 8px;
}
 .icons {
    width: 265px;
    float: left;
    margin: 30px 4px 25px;
    text-align: center;
	position:relative;
}

 }
.icons .badge {
    position: absolute;
    right: 25px;
    top: 0px;
    z-index: 100;
}

</style>
</head>
<body>
  <section id="container" class="sidebar-closed">
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
							  
						  </ul>
						 </div>
					</section>
				  <!--breadcrumbs end -->
			  </div>	
             </div>
			 
			
				  <!--breadcrumbs start -->
				  <section class="panel">
				   <div class="row">
			  <div class="col-lg-12 centeral-align">
				  <div class="icons">
			
				<div class="icon1 success">
				<p style="color:white;padding-top:10px;">Total Invoice Value</p>
					<h3 style="font-size:20px;color:white;padding-top:5px;">Rs.<span id="tval" style="font-size:20px;color:white;"></span> </h3>
				</div>
				
			</div>
				<div class="icons"  style="margin-left:0;">	 	
				<div class="icon1 info" style="margin-left:0;">
					
						<p style="color:white;padding-top:10px;">Total Tax<br> Value</p>
					
						<h3 style="font-size:20px;color:white;padding-top:5px;">Rs.<span id="ttax" style="font-size:20px;color:white"></span></h3>
				
				</div>
				</div>
			 </div>	
             </div>
					</section>
				  <!--breadcrumbs end -->
			 
			 
			 
			 
              <!--state overview start-->
		  <div class="row">			
			<div class="col-sm-12">
				<section class="panel">
				  <header class="panel-heading">
				  	 <span class="tools pull-right">
			<!--<a href="javascript:;" onClick="tableToExcel('adv-table', 'Instalment Collection')" ><button class="btn btn-success btn-flat" >Export Excel</button></a>
			<button class="btn btn-info btn-flat" id="export_csv" onclick="return csv_export();">Export CSV</button>-->	
			 </span>
				  	 <span class="tools pull-right">
						<button class="btn btn-warning btn-flat" onClick="PrintMe('gstr');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>	
						<button class="btn btn-info btn-flat" id="export_csv" onclick="return csv_export();">Export CSV</button>										
					</span>	
				 
					  New <?=$form?>
					</header>	
			
				
				<div class="panel-body">
					<div class="row">
								
							<div class="col-md-4"> 
						<div class="form-group">
                                  <label class="control-label col-md-3">Choose Date</label>
                                  <div class="col-md-9">
                                      <div class="input-group date form_datetime-component">
									 <?php 
									  $start=date('01-m-Y');
									  ?>
                                         <input type="hidden" id="from_date"  value="<?=$start?>">
										 <input type="hidden" id="to_date"  value="<?=date('t-m-Y')?>">
         					 		        <input type="text" id="rep_date"  onChange="generate_report();totalvalue();" class="form-control datepikerdemo" value="">
											<span class="input-group-btn">
											<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
											</span>
                                  </div>
                                  </div>
                              </div>
							  </div>
							 <!-- <div class="col-md-4"> 
						<div class="form-group">
                                  <label class="control-label col-md-3">Invoice Type</label>
                                  <div class="col-md-6">
                                       <select  class="form-control" name="type_id" id="type_id" onChange="reload_data();">
									<?=getlistinvoicetype($dbcon);?>	
								</select>
                                  </div>
                              </div>
							  </div>-->
							  
					<br>
					<br>
					
					<header class="panel-heading tab-bg-dark-navy-blue">
						<ul class="nav nav-tabs">
						  <li class="active">
							  <a data-toggle="tab" href="#b2b_data">
								GSTR1 - B2B
							  </a>
						  </li>
						  <li class="">
							  <a data-toggle="tab" href="#b2cs_data">
								 GSTR1 - B2CS
							  </a>
						  </li>
						</ul>
					  
				  </header>
				  <Div id="gstr">
				  <div class="panel-body">
						<div class="tab-content">
						  <div id="b2b_data" class="tab-pane active">
							 
						  </div>
						  <div id="b2cs_data" class="tab-pane">
						  </div>
						</div>
				  </div>
					
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
	<?php include_once('../include/footer.php');?>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/gstr1_report.js?<?=time()?>"></script>
   <!--<script src="<?=ROOT?>js/jquery.tabletoCSV.js?<?=time()?>" type="text/javascript" charset="utf-8"></script>-->
   <script src="<?=ROOT?>js/table2csv.js?<?=time()?>" type="text/javascript" charset="utf-8"></script>
    <!--<script src="js/count.js"></script>-->
		<script>
		$(document).ready(function() {
 Loading(true);	

  totalvalue();
 
Unloading();
});
function totalvalue()
		{
			Loading();
	var date=$("#rep_date").val();

	
	$.ajax({
		type: "POST",
		url: root_domain+'app/gstr1_report/',
		data: { mode : "totalval",date :  date},
		success: function(response)
		{
			console.log(response);
			var resp=jQuery.parseJSON(response);
			if(response != "") {
				
				$('#ttax').html(resp.taxable_amt);
				$('#tval').html(resp.g_total);
				Unloading();
			}
										
		}
	});	
		}
		function csv_export()
		{
			$('.hide_csv').css('display','none');
			//$(".table-bordered").tableToCSV();
			$("#gstr").first().table2csv({
				filename: 'b2b.csv',
				separator: ',',
				newline: '\n',
				quoteFields: false,
				excludeColumns: '',
				excludeRows: ''
			});			
			$('.hide_csv').css('display','');
			return false;
		}
		function b2bcsv_export()
		{
			$('.hide_csv').css('display','none');
			//$(".table-bordered").tableToCSV();
			$("#b2b_table").first().table2csv({
				filename: 'b2b.csv',
				separator: ',',
				newline: '\n',
				quoteFields: false,
				excludeColumns: '',
				excludeRows: ''
			});			
			$('.hide_csv').css('display','');
			return false;
		}
		function b2cscsv_export()
		{
			$('.hide_csv').css('display','none');
			//$(".table-bordered").tableToCSV();			
			$("#b2cs_table").first().table2csv({
				filename: 'b2cs.csv',
				separator: ',',
				newline: '\n',
				quoteFields: false,
				excludeColumns: '',
				excludeRows: ''
			});
			$('.hide_csv').css('display','');
			return false;
		}
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
	$('#hide_csv').css('display','none');
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
