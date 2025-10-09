<?php 
session_start();
$path = '../../';
$include1 = '../include/';
$include = '../../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
//echo COMMON_FUNCTION_PATH."common_functions.php";
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$form="Balance Sheet";

//check permission for this page
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    FINANCE_BALANCE_SHEET_REPORT_VIEW
]);
if(!in_array(FINANCE_BALANCE_SHEET_REPORT_VIEW,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}

if(empty($_SESSION['start']))
{
    $start_date = date('1-m-Y');
    $end_date = date("d-m-Y");
}
else
{
    $start = $_SESSION['start'];
    $end = $_SESSION['end'];
}
$date = get_financial_year();
$financial_year=get_financial_year_new($dbcon);
//print_r($date);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>BALANCE SHEET</title>
        <?php include_once($include.'include_css_file.php');?>
    </head>
    <body>
        <section id="container">
        <?php include_once($include.'include_top_menu.php');?>
        <?php include_once($include.'left_menu.php');?>
            <section id="main-content">
                <section class="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="panel">
                                <header class="panel-heading"><h3><?=$mode.' '.$form?></h3></header>	
                                <div class="">
                                    <ul class="breadcrumb">
                                        <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="<?=ROOT.FINANCE_ROOT ?>finance_report_list">Finance Report List</a></li>
                                        <li><a href="#"><?=$form?> Report</a></li>
                                    </ul>
                                </div>
                            </section>
                        </div>	
                    </div>
                    <div class="row">			
                        <div class="col-sm-12">
                            <section class="panel">
                                <header class="panel-heading"><?=$form?> Report
                                     <span class="tools pull-right">
                                     <button type="submit" class="btn btn-success" onClick="PrintMe('receipt_print');"><i class="fa fa-print"></i> Print</button>
                                 </span>
                                </header>	
                                    <div class="panel-body">
                                        <form class="form-horizontal" role="form" id="po_add" action="javascript:;" method="post" name="po_add">
                                            <div class="row">
                                                <div class="col-md-12"  style="margin-top:10px;">

                                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>Start Date</strong></div>
                                                        <div class="col-md-9" style="padding-right: 0px;">
                                                            <input id="start_date" name="start_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$date['start_date'];?>" placeholder="Start Date" onChange="reload_data();" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6" style="margin-bottom: 15px;">
                                                        <div class="col-md-3" style="white-space:nowrap;padding-left: 0px;"><strong>End Date</strong></div>
                                                        <div class="col-md-9" style="padding-right: 0px;">
                                                            <input id="end_date" name="end_date" type="text" class="form-control default-date-picker reuired valid" title="Date" value="<?=$date['end_date'];?>" placeholder="End Date" onChange="reload_data();" autocomplete="off">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                     <div class="col-lg-12 table-responsive" id="receipt_print"> <div class="col-md-12" style=" margin-top:10px;" id="print1">
                                                        <div id="balance_sheet_id" ></div>
                                                    </div>
                                                </div>
                                            </div>
										</form>
                                    </div>
                            </section>
                        </div>
                    </div>		
                </section>
            </section>
        <?php include_once($include.'footer.php');?>
        </section>
        <?php include_once($include.'include_js_file.php');?>  
        <script src="<?=ROOT?>js/mousetrap/mousetrap.min.js"></script>
        <script src="<?=ROOT?>js/mousetrap/mousetrap-bind-dictionary.min.js"></script>
        <script src="<?=ROOT.FINANCE_ROOT ?>js/app/balance_sheet.js"></script>
        <script>
            $(".select2").select2({
                width: '100%'
            });
            
            $('.default-date-picker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                startDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_start_date'])) ?>',
                endDate:'<?php echo date("d-m-Y", strtotime($financial_year['financial_end_date'])) ?>',
            });
            
            $(".form_datetime").datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                autoclose: true,
                todayBtn: true,
                pickerPosition: "bottom-left"

            });
        </script>
        <script>
            $(document).ready(function() {
                Loading(true);	
                //Unloading();
            });
            
            $('.default-date-picker').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true
            });
            
         /* Added By Jayesh 30-07-2021 For tab and enter key */   
		window.onkeyup = function(e){
		var event = e.which || e.keyCode || 0; // .which with fallback
			var current = $(':focus');
			var id = current.attr("tabIndex");
			var current_link = current.find('a:first').attr('href');
			if(event== 13)
			{
				if(typeof current_link == "undefined"){
					return false;
				}
				else{
					window.location=root_domain+finance_root_domain+current_link; // Navigate to URL	
					return false;					
				}
			}			   	
		}
        /* Added By Jayesh 30-07-2021 For tab and enter key */     
            
            
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
                  // 'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   //'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                   //'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                   'This Month': [moment().startOf('month'), moment().endOf('month')],
                   'Last 3 Month': [moment().subtract(3, 'months'), moment().endOf('month')],
                   'Last 6 Month': [moment().subtract(6, 'months'), moment().endOf('month')],
                   'Last 1 Year': [moment().subtract(12, 'months'), moment().endOf('month')],
                   'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);
            
            $('.date-set').click(function(){
                $('.datepikerdemo').trigger('click');
            });
 
        </script>
         <script type="text/javascript"> 

    function PrintMe(DivID) {
$(".printshow").show();
$(".noprint").hide();
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
  docprint.document.write('<head><title><?php echo TITLE;?></title>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/style.css" media="all"/>');
  docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT;?>css/bootstrap.min.css" media="all"/>');
  docprint.document.write('<style type="text/css">');
  
    docprint.document.write(' @media print{ @page { size:landscape; margin: 0.2in 0.2in 0.2in 0.2in; } }  #table_head, #table_foot { display:none }');
        //$('#invoice_type').css('margin-top','1.7in');
     docprint.document.write('@media print{ .noprint{ display:none; }}');
     
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
$(".printshow").hide();
$(".noprint").show();
}
</script>
    </body>
</html>
