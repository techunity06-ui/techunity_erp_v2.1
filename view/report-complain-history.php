<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form="Copmlain History Report";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>COMPLAINT HISTORY</title>
<?php include_once('../include/include_css_file.php');?>
<style>
	
/******************* Accordion Demo - 4 *****************/
#accordion4 .panel{
    border: none;
    border-radius: 0;
    box-shadow: none;
    margin: 0 0 10px;
    overflow: hidden;
    position: relative;
}
#accordion4 .panel-heading{
    padding: 0;
    border: none;
    border-radius: 0;
    margin-bottom: 10px;
    z-index: 1;
    position: relative;
}
#accordion4 .panel-heading:before,
#accordion4 .panel-heading:after{
    content: "";
    width: 50%;
    height: 20%;
    box-shadow: 0 15px 5px rgba(0, 0, 0, 0.4);
    position: absolute;
    bottom: 15px;
    left: 10px;
    transform: rotate(-3deg);
    z-index: -1;
}
#accordion4 .panel-heading:after{
    left: auto;
    right: 10px;
    transform: rotate(3deg);
}
h4.panel-title{margin:10px 0px !important;}
#accordion4 .panel-title a{
    display: block;
    padding: 15px 70px 15px 70px;
    margin: 0;
    background: #fff;
    font-size: 18px;
    font-weight: 500;
    letter-spacing: 1px;
    color: #d11149;
    border-radius: 0;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1), 0 0 40px rgba(0, 0, 0, 0.1) inset;
    position: relative;
}
#accordion4 .panel-title a:before,
#accordion4 .panel-title a.collapsed:before{
    content: "\f055";
    font-family: "FontAwesome";
    font-weight: 900;
    width: 55px;
    height: 100%;
    text-align: center;
    line-height: 50px;
    border-left: 2px solid #D11149;
    position: absolute;
    top: 0;
    right: 0;
}
#accordion4 .panel-title a.collapsed:before{ content: "\f107"; }
#accordion4 .panel-title a .icon{
    display: inline-block;
    width: 55px;
    height: 100%;
    border-right: 2px solid #d11149;
    font-size: 20px;
    color: rgba(0,0,0,0.7);
    line-height: 50px;
    text-align: center;
    position: absolute;
    top: 0;
    left: 0;
}
#accordion4 .panel-body{
    padding: 10px 15px;
    margin: 0 0 20px;
    border-bottom: 3px solid #d11149;
    border-top: none;
    background: #fff;
    font-size: 15px;
    color: #333;
    line-height: 27px;
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
						 <h3 style="float:left;"><?=$form?></h3><br>
						 <?php include_once('../include/reporthead_menu.php');?>
     
						</header>	
							<div class="">
						  <ul class="breadcrumb">
							  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
							  <li ><a href="<?=ROOT.'vendor_ledger'?>"><?=$form?></a></li>
							  
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
				  <header class="panel-heading">
				  	 
				  	 
				  <?=$form?>
					</header>				
				<div class="panel-body">
					<div class="row">
							<div class="col-md-12">
							
							<div class="form-group" style="margin-top:20px;">
                                  <label class="control-label col-md-2" >Choose Date</label>
                                  <div class="col-md-3">
										<div class="input-group date form_datetime-component">
										 <?php 
										  $start=date('01-m-Y');
										  ?>
											 <input type="hidden" id="from_date"  value="<?=$start?>">
											 <input type="hidden" id="to_date"  value="<?=date('t-m-Y')?>">
												<input type="text" id="rep_date"  onChange="generate_report_comp_history();" class="form-control datepikerdemo" value="">
												<span class="input-group-btn">
												<button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
												</span>
										 </div>
									</div>
							
							</div>	

						</div>
					
					<div class="adv-table" id="adv-table" style="margin-top:120px;">
				  	
			
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
   <script src="<?=ROOT?>js/app/employee_expense.js?<?=time()?>"></script>
    <!--<script src="js/count.js"></script>-->
		<script>
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
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
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
