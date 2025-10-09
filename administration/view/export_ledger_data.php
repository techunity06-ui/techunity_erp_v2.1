<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$include = '../../include/';
$token = md5(rand(1000,9999));
$_SESSION['token'] = $token;
$_SESSION['contents']='';
$form="Export Ledger";
$mode="Print";

$sql = "SELECT group_concat(g_id) as grp_id FROM `tbl_group` WHERE `g_pid` IN (37,38)";
$sresult = $dbcon->query($sql);
$srow = brp_mysqli_fetch_array($sresult);

$grp='';
if($srow['grp_id']){
    $grp = ','.$srow['grp_id'];    
}

$query = "select led.*,grp.g_name,coun.country_name, state.state_name, city.city_name, btype.balance_type_name, zone.zone_name, assign.user_name as assign_user, term.payment_terms, ter.t_name, tdsta.tds_cat_name, cm.common_mst_name from tbl_ledger as led
left join tbl_group as grp on grp.g_id = led.l_group 
left join zone_mst as zone on zone.zone_id = led.zone_id
left join mst_balance_type as btype on btype.balance_typeid = led.balance_typeid
left join country_mst as coun on coun.countryid = led.countryid
left join state_mst as state on state.stateid  = led.stateid
left join city_mst as city on city.cityid = led.cityid
left join users as assign on assign.user_id = led.cust_owner
left join pay_terms as term on term.terms_id=led.pay_terms
left join territory_mst as ter on ter.t_id = led.territory_id
left join tbl_tds_tax_category as tdsta on tdsta.tds_cat_id = led.tdstax_cat
left join tbl_tds_tax_category_detail as ttc on ttc.tds_cat_detail_id = led.party_pay_cat
left join tbl_common_mst as cm on cm.common_mst_id = ttc.tds_payee
where led.l_status!=2 and led.company_id in (0,".$_SESSION['company_id'].") and led.l_group in (37,38 ".$grp.")";
$result = $dbcon->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<?php include_once('../../include/include_css_file.php');?>
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
<script src="//unpkg.com/xlsx/dist/xlsx.full.min.js" type="text/javascript"></script>
<script>
function exportFile(){
  var wb = XLSX.utils.table_to_book(document.getElementById('invoice_type'));
  XLSX.writeFile(wb, 'sample.xlsx');
  return false;
}
</script>
</head>
<body>
	<section id="container" >
		<?php include_once('../../include/include_top_menu.php');?>
		<!--sidebar start-->
		<?php include_once('../../include/left_menu.php');?>
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
									<li ><a href="<?=ROOT.ADMINISTRATION_ROOT.'ledger_list'?>"><?=$form?> List</a></li>
									
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
								<span class="tools pull-right">
									<a href="javascript:;" onClick="tableToExcel('ledger_tbl', '<?=$form?>')" ><button class="btn btn-success btn-flat" >Export Excel</button></a>
								</span>
							</header>	
							<div class="panel-body">
								<div style="width:100%;overflow-x: auto;margin-top: 40px;">
										<table class="display table table-bordered" id="ledger_tbl">
										<thead>
												<tr>
													<th style="white-space: nowrap;">Sr.No.</th>
													<th style="white-space: nowrap;">Ledger Name</th>
													<th style="white-space: nowrap;">Alias Name</th>
													<th style="white-space: nowrap;">Group Name</th>
													<th style="white-space: nowrap;">Ledger Code</th>
													<th style="white-space: nowrap;">Email Id</th>
													<th style="white-space: nowrap;">Country</th>
													<th style="white-space: nowrap;">State</th>
													<th style="white-space: nowrap;">City</th>
													<th style="white-space: nowrap;">Pin Code</th>
													<th style="white-space: nowrap;">Opening Balace</th>
													<th style="white-space: nowrap;">Balance type</th>
													<th style="white-space: nowrap;">Company Name</th>
													<th style="white-space: nowrap;">Contact Person</th>
													<th style="white-space: nowrap;">Company Address</th>
													<th style="white-space: nowrap;">GSTIN</th>
													<th style="white-space: nowrap;">IEC No</th>
													<th style="white-space: nowrap;">Mobile No.</th>
													<th style="white-space: nowrap;">Email</th>
													<th style="white-space: nowrap;">Website</th>
													<th style="white-space: nowrap;">Zone</th>
													<th style="white-space: nowrap;">Is Party Belong To SEZ</th>
													<th style="white-space: nowrap;">Assign User</th>
													<th style="white-space: nowrap;">Payment Terms</th>
													<th style="white-space: nowrap;">Territory</th>
													<th style="white-space: nowrap;">Credit Limit</th>
													<th style="white-space: nowrap;">Credit Days</th>
													<th style="white-space: nowrap;">Remark</th>
													<th style="white-space: nowrap;">Enable Cost Center</th>
													<th style="white-space: nowrap;">Enable TDS</th>
													<th style="white-space: nowrap;">Enable TCS</th>
													<th style="white-space: nowrap;">TDS Tax Category</th>
													<th style="white-space: nowrap;">Party Payee Category</th>
													<th style="white-space: nowrap;">PAN / IT No.</th>
												</tr>
										</thead>
										<tbody>
												<?
												$i=1;
												while($row=brp_mysqli_fetch_array($result)){
													$enable_sez = 'No';$enable_cost_center ='No';$enable_tds='No';$enable_tcs='No';
													if($row['enable_sez']==1){
														$enable_sez = 'Yes';
													}
													
													if($row['enable_cost_center']==1){
														$enable_cost_center='Yes';
													}

													if($row['enable_tds']==1){
														$enable_tds='Yes';
													}

													if($row['enable_tcs']==1){
														$enable_tcs='Yes';
													}
												?>
												<tr>
													<td><?=$i?></td>
													<td><?=$row['l_name']?></td>
													<td><?=$row['ledger_alias']?></td>
													<td><?=$row['g_name']?></td>
													<td><?=$row['ledger_code']?></td>
													<td><?=$row['common_email_id']?></td>
													<td><?=$row['country_name']?></td>
													<td><?=$row['state_name']?></td>
													<td><?=$row['city_name']?></td>
													<td><?=$row['cust_pincode']?></td>
													<td><?=$row['opn_balance']?></td>
													<td><?=$row['balance_type_name']?></td>
													<td><?=$row['company_name']?></td>
													<td><?=$row['cust_cont_name']?></td>
													<td><?=$row['m_address']?></td>
													<td><?=$row['gst_no']?></td>
													<td><?=$row['iec_no']?></td>
													<td><?=$row['cust_mobile']?></td>
													<td><?=$row['cust_email']?></td>
													<td><?=$row['cust_website']?></td>
													<td><?=$row['zone_name']?></td>
													<td><?=$enable_sez?></td>
													<td><?=$row['assign_user']?></td>
													<td><?=$row['payment_terms']?></td>
													<td><?=$row['t_name']?></td>
													<td><?=$row['credit_limit']?></td>
													<td><?=$row['credit_days']?></td>
													<td><?=$row['cust_remark']?></td>
													<td><?=$enable_cost_center?></td>
													<td><?=$enable_tds?></td>
													<td><?=$enable_tcs?></td>
													<td><?=$row['tds_cat_name']?></td>
													<td><?=$row['common_mst_name']?></td>
													<td><?=$row['m_pan']?></td>
												</tr>

												<?
													$query_bank = "select mst.*,b.bank_name from tbl_customer_bank as mst 
														left join bank_mst as b on b.bankid=mst.b_name
														where mst.b_cust=".$row['l_id']." order by b_id Desc";

													$result_bank = $dbcon->query($query_bank);
													$cnt_bank = brp_mysqli_num_rows($result_bank);
													if($cnt_bank>0){
												?>
													<tr>
															<th colspan="34" style="height:30px"></th>
													</tr>
													<tr>
															<th colspan="10" style="text-align: center;">Bank Detail</th>
															<th colspan="24" style="text-align: center;"></th>
													</tr>

													<tr>
															<th colspan="2">A/c No</th>
															<th colspan="2">Bank Name</th>
															<th colspan="2">A/C Name</th>
															<th colspan="2">IFSC</th>
															<th colspan="2">Opening</th>
															<th colspan="24"></th>
													</tr>
													<?while($row_bank = brp_mysqli_fetch_array($result_bank)){?>
														<tr>
															<td colspan="2"><?=$row_bank['bank_ac']?></td>
															<td colspan="2"><?=$row_bank['bank_name']?></td>
															<td colspan="2"><?=$row_bank['ac_name']?></td>
															<td colspan="2"><?=$row_bank['bank_ifsc']?></td>
															<td colspan="2"><?=$row_bank['bank_open']?></td>
															<td colspan="24"></td>
														</tr>
												<?}?>
												<tr>
														<td colspan="34" style="height:30px"></td>
												</tr>
												<?}
													$query_cont="select * from tbl_cust_contact_person where cust_id=".$row['l_id']." order by cust_contact_person_id Desc";
													$result_cont = $dbcon->query($query_cont);
													$cnt_cont = brp_mysqli_num_rows($result_cont);
													if($cnt_cont>0){
												?>
													<tr>
														<th colspan="10" style="text-align: center;">Contact Person Details</th>
														<th colspan="24"></th>
													</tr>
													<tr>
														<th colspan="2">Name</th>
														<th colspan="2">Mobile</th>
														<th colspan="3">Email</th>
														<th colspan="3">Job Title</th>
													</tr>
													<?while($row_cont = brp_mysqli_fetch_array($result_cont)){?>
													<tr>
														<td colspan="2"><?=$row_cont['cust_contact_person_name']?></td>
														<td colspan="2"><?=$row_cont['cust_contact_person_no']?></td>
														<td colspan="3"><?=$row_cont['cust_contact_person_email']?></td>
														<td colspan="3"><?=$row_cont['cust_contact_person_designation']?></td>
													</tr>
												<?}?>
													<tr>
														<td colspan="34" style="height:30px"></td>
													</tr>
												<?}
												$query_consi="select tbcon.*, cit.city_name, sta.state_name, coun.country_name from tbl_custmer_consignee as tbcon
													left join country_mst as coun on coun.countryid=tbcon.countryid
													left join state_mst as sta on sta.stateid=tbcon.stateid 
													left join city_mst as cit on cit.cityid=tbcon.cityid
													where tbcon.cust_ref_id=".$row['l_id']." and tbcon.cust_status=0  order by tbcon.cust_id Desc"; 
												$result_consi = $dbcon->query($query_consi);
												$cnt_consi = brp_mysqli_num_rows($result_consi);
												if($cnt_consi>0){
												?>
													<tr>
														<th colspan="10" style="text-align: center;">Consignee Details</th>
														<th colspan="24" style="text-align: center;"></th>
													</tr>
													<tr>
														<th colspan="2">Company Name</th>
														<th colspan="2">Person Name</th>
														<th>Mobile</th>
														<th>Email</th>
														<th colspan="2">Address</th>
														<th>Country</th>
														<th>State</th>
														<th>City</th>
														<th colspan="2">GST No</th>
														<th>Pincode</th>
														<th colspan="20"></th>
													</tr>
													<?while($row_consi = brp_mysqli_fetch_array($result_consi)){?>
														<tr>
															<td colspan="2"><?=$row_consi['company_name']?></td>
															<td colspan="2"><?=$row_consi['cust_name']?></td>
															<td><?=$row_consi['cust_mobile']?></td>
															<td><?=$row_consi['cust_email']?></td>
															<td colspan="2"><?=$row_consi['cust_address']?></td>
															<td><?=$row_consi['country_name']?></td>
															<td><?=$row_consi['state_name']?></td>
															<td><?=$row_consi['city_name']?></td>
															<td colspan="2"><?=$row_consi['gst_no']?></td>
															<td><?=$row_consi['cust_pincode']?></td>
															<td colspan="20"></td>
														</tr>
												<?}?>
												<tr>
													<td colspan="34" style="height:30px"></td>
												</tr>
												<?}?>
												<?$i++;}?>
										</tbody>
										</table>
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
<?php include_once('../../include/footer.php');?>
<!--footer end-->
</section>

<!-- js placed at the end of the document so the pages load faster -->
<?php include_once('../../include/include_js_file.php');?>   
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
</script>
<script type="text/javascript"> 	
/* var lineArray = [];
result_table.forEach(function(infoArray, index) {
  var line = infoArray.join(" \t");
  lineArray.push(index == 0 ? line : line);
});
var csvContent = lineArray.join("\r\n");
var excel_file = document.createElement('a');
excel_file.setAttribute('href', 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(csvContent));
excel_file.setAttribute('download', 'Visitor_History.xls');
document.body.appendChild(excel_file);
excel_file.click();
document.body.removeChild(excel_file); */

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