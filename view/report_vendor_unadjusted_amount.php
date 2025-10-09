<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$form = "Vendor Unadjusted Amount Report";
        $date = get_current_financial_year();
        extract($date);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
            <?php include_once('../include/include_css_file.php');?>
    </head>
    <body>
        <style type="text/css">
        .link_dash
	{
		border-bottom:dotted blue thin;
	}
        </style>
        <section id="container" >
            <?php include_once('../include/include_top_menu.php');?>
            <?php include_once('../include/left_menu.php');?>
            <section id="main-content">
                <section class="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="panel">
                                <header class="panel-heading">
                                    <h3 style="float:left;"><?=$form?></h3></br>
                                </header>	
                                <div class="">
                                    <ul class="breadcrumb">
                                        <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="<?=ROOT.'report_vendor_unadjusted_amount'?>"><?=$form?></a></li>
                                    </ul>
                                </div>
                            </section>
                        </div>	
                    </div>
                    <div class="row">			
                        <div class="col-sm-12">
                            <section class="panel">
                                <header class="panel-heading">
                                        <span class="tools pull-right">
                                                <a href="javascript:;" onClick="tableToExcel('adv-table', 'Instalment Collection')" ><button class="btn btn-success btn-flat" >Export Excel</button></a>	
                                        </span>
                                        <span class="tools pull-right">
                                                <button class="btn btn-warning btn-flat" onClick="PrintMe('adv-table');" style="margin-right:20px;"><i class="fa fa-print"></i> Print Report</button>											
                                        </span>	
                                        <?=$form?>
                                </header>				
                                <div class="panel-body">
                                    <div class="row">
                                        <!--<div class="col-md-12">
                                            <div class="form-group" style="margin-top:20px;">
                                                    <label class="control-label col-md-2" >Choose Date</label>
                                                    <div class="col-md-3">
                                                            <div class="input-group date form_datetime-component">
                                                                    <input type="hidden" id="from_date"  value="<?//= $start_date ?>">
                                                                    <input type="hidden" id="to_date"  value="<?//= $end_date ?>">
                                                                    <input type="text" id="rep_date"  onChange="generate_report();" class="form-control datepikerdemo" value="">
                                                                    <span class="input-group-btn">
                                                                            <button type="button" class="btn btn-danger date-set"><i class="fa fa-calendar"></i></button>
                                                                    </span>
                                                            </div>
                                                    </div>
                                            </div>	
					</div>-->
                                        <div class="clearfix"></div>
                                        <div class="col-md-12" style="margin: 10px 0;">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <div class="col-md-3">
                                                        <label>
                                                            <input id="cust_type_all" name="cust_type" type="radio" checked="checked" style="width:20px;height:20px;vertical-align:middle" onClick="$('#cust_div').hide();generate_report();generate_chart();" class="" title="All" value="0">
                                                            <div class='external-event label label-primary ui-draggable' style='position: relative;width:100px;'>All</div>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>
                                                            <input id="cust_type_sc" name="cust_type" type="radio" style="width:20px;height:20px;vertical-align:middle" onClick="$('#cust_div').show();$('#cust_id').select2('val','0');" class="" title="Choose Party" value="1">
                                                            <div class='external-event label label-warning ui-draggable' style='position: relative;width:100px;'>Choose Customer</div>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-4" id="cust_div" style="display: none;">
                                                        <select  class="select2" name="cust_id" id="cust_id" onChange="generate_report();generate_chart();">
                                                            <?=get_ledger($dbcon,$ledger_id,' and l_group IN ('.SUNDRY_CREDITORS.') ')?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12" style="margin: 10px 0;">
                                            <div id="chart_container" style="height: 300px; width: 100%;"></div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="adv-table" id="adv-table" style="overflow-x: auto;width:100%;min-height: 70px;">
                                        </div>
                                    </div>
				</div>	
                            </section>
			</div>
                    </div>
		</section>
            </section>
            <?php include_once('../include/footer.php');?>
        </section>
        <?php include_once('../include/include_js_file.php');?>   
        <script src="<?=ROOT?>js/app/vendor_unadjusted_amount_report.js?<?=time()?>"></script>
        <script>
                $(".select2").select2({
                        width: '100%'
                });
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
