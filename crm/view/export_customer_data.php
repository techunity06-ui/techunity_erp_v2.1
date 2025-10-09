<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
$include = '../../include/';
$form = "Export Customer Data";

$companyConfiguration = getCompanyConfiguration($dbcon);
$enable_assing_user = $companyConfiguration['enable_assing_user'];

$where='';
if($_SESSION['user_type']!=2){ 
  if ($enable_assing_user == 1)
  {
  	$where=" and FIND_IN_SET($_SESSION[user_id],cust.cust_assign_user)";
  }
}
$query = "select cust.*,cc.cc_name,ci.ci_name,ctype.mcd_name as cust_type,ref.rb_name, ter.t_name from tbl_customer as cust
left join  tbl_customer_category as cc on cc.cc_id=cust.cust_cat 
left join tbl_customer_industry as ci on ci.ci_id = cust.cust_ind
left join tbl_master_category_detail as ctype on ctype.mcd_id = cust.cust_type
left join tbl_refer_by as ref on ref.rb_id = cust.cust_source
left join territory_mst as ter on ter.t_id = cust.t_id
where cust.cust_status=0 and cust.company_id in (0, ".$_SESSION[company_id].")".$where;
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
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
									<li ><a href="<?=ROOT.CRM_ROOT.'customer_list'?>">Customer List</a></li>
									
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
								<div class="row">
									<div class="col-md-4"></div>
									<div class="col-md-4"></div>
									<div class="col-md-4" style="text-align:right">
										<a href="javascript:;" onClick="tableToExcel('customer_tbl', '<?=$form?>')" ><button class="btn btn-success btn-flat" >Export Excel</button></a>
									</div>
								</div>
								<div style="width:100%;overflow-x: auto;margin-top: 40px;">
									<table class="display table table-bordered" id="customer_tbl">
										<thead>
										<tr>
												<th style="white-space: nowrap;">Sr. No.</th>
												<th style="white-space: nowrap;">Party Code</th>
												<th style="white-space: nowrap;">Party Category</th>
												<th style="white-space: nowrap;">Owner User</th>
												<th style="white-space: nowrap;">Company Name</th>
												<th style="white-space: nowrap;">Party Industry</th>
												<th style="white-space: nowrap;">Customer Type</th>
												<th style="white-space: nowrap;">Source / Refer By</th>
												<th style="white-space: nowrap;">Territory</th>
												<th style="white-space: nowrap;">Gst No</th>
												<th style="white-space: nowrap;">Iec No</th>
												<th style="white-space: nowrap;">Mobile</th>
												<th style="white-space: nowrap;">E-mail</th>
												<th style="white-space: nowrap;">Pan No</th>
										</tr>
									</thead>
									<tbody>
										<?if($cnt>0){
											$i = 1;
											while($row = brp_mysqli_fetch_array($result)){
												$owners = getUserDetailById($dbcon,$row['cust_owner']);
										?>
										<tr>
												<td><?=$i?></td>
												<td><?=$row['cust_code']?></td>
												<td><?=$row['cc_name']?></td>
												<td><?=$owners['user_name']?></td>
												<td><?=$row['cust_name']?></td>
												<td><?=$row['ci_name']?></td>
												<td><?=$row['cust_type']?></td>
												<td><?=$row['rb_name']?></td>
												<td><?=$row['t_name']?></td>
												<td><?=$row['cust_gst']?></td>
												<td><?=$row['cust_iec']?></td>
												<td><?=$row['cust_mobile']?></td>
												<td><?=$row['cust_email']?></td>
												<td><?=$row['cust_pan']?></td>
										</tr>
										<?$personal_detail = "select from tbl_cust_relation status=0 and cust_id=".$row['cust_id'];
										$result_pdetail = $dbcon->query($personal_detail);
										$per_cnt = brp_mysqli_num_rows($result_pdetail); 
										if($per_cnt>0){
										?>
										<tr>
											<th colspan="14" style="text-align:center">Personal Details</th>
										</tr>
										<tr>	
											<th colspan="4">Relation</th>
											<th colspan="3">Gender</th>
											<th colspan="4">Birthday</th>
											<th colspan="3">Anniversary</th>
										</tr>
										<?
											while($per_row = brp_mysqli_fetch_array($result_pdetail)){?>
												<tr>
														<td colspan="4"><?=$per_row['relation']?></td>
														<td colspan="3"><?=$per_row['gender']?></td>
														<td colspan="4"><?=date("d-M-Y", strtotime($per_row['birth_date']))?></td>
														<td colspan="3"><?=date("d-M-Y", strtotime($per_row['anniversary_date']))?></td>
												</tr>
										<?}?>
										<tr>
												<td colspan="14" style="height:30px"></td>
										</tr>
										<?}
											$cust_add = $dbcon->query("select per.*,country_name,state_name,city_name from tbl_cust_address as per
											left join country_mst as country on country.countryid=per.c_add_country
											left join state_mst as state on state.stateid=per.c_add_state
											left join city_mst as city on city.cityid=per.c_add_city
											where per.c_add_status=0 and per.cust_id=".$row['cust_id']);

											$addr_cnt = brp_mysqli_num_rows($cust_add);
											if($addr_cnt>0){
										?>
											<tr>
													<th colspan="14" style="text-align:center">Address Details</th>
											</tr>
											<tr>
													<th colspan="3">Address</th>
													<th colspan="3">Country</th>
													<th colspan="3">State</th>
													<th colspan="3">City</th>
													<th colspan="2">Default</th>
											</tr>
										<?while($addr_row = brp_mysqli_fetch_array($cust_add)){
											if($row['c_addr_defult'] ==1){
												$default = '<i style="color:green">Default</i>';
											}else{
												$default = '<i style="color:red">Primary</i>';
											}
											?>
											<tr>
												<td colspan="3"><?=$addr_row['c_add_address']?></td>
												<td colspan="3"><?=$addr_row['country_name']?></td>
												<td colspan="3"><?=$addr_row['state_name']?></td>
												<td colspan="3"><?=$addr_row['city_name']?></td>
												<td colspan="2"><?=$default?></td>
											</tr>
										<?}?>
										<tr>
												<td colspan="14" style="height:30px"></td>
										</tr>
										<?}
										$cont_det = $dbcon->query("select * from tbl_cust_contact where c_con_status=0 and cust_id=".$row['cust_id']);
										$cont_cnt = brp_mysqli_num_rows($cont_det);
										if($cont_cnt>0){
										?>
											<tr>
												<th colspan="14" style="text-align:center">Contact Details</th>
											</tr>
											<tr>
												<th colspan="3">First Name</th>
												<th colspan="3">Last Name</th>
												<th colspan="2">Email</th>
												<th colspan="2">Mobile</th>
												<th colspan="2">Phone</th>
												<th colspan="2">Job Title</th>
											</tr>
											<?while($cont_row = brp_mysqli_fetch_array($cont_det)){?>
												<tr>
														<td colspan="3"><?=$cont_row['c_con_fname']?></td>
														<td colspan="3"><?=$cont_row['c_con_lname']?></td>
														<td colspan="2"><?=$cont_row['c_con_email']?></td>
														<td colspan="2"><?=$cont_row['c_con_mobile']?></td>
														<td colspan="2"><?=$cont_row['c_con_phone']?></td>
														<td colspan="2"><?=$cont_row['c_con_job']?></td>
												</tr>
										<?}?>
										<tr>
												<td colspan="14" style="height:30px"></td>
										</tr>
										<?}
											$consignee_det = $dbcon->query("select per.*,country.country_name,state.state_name,city.city_name from tbl_party_consignee as per
											left join country_mst as country on country.countryid=per.countryid
											left join state_mst as state on state.stateid=per.stateid
											left join city_mst as city on city.cityid=per.cityid
											where per.cust_ref_id=".$row['cust_id']);

											
											$cnt_consi = brp_mysqli_num_rows($consignee_det);
											if($cnt_consi>0){
										?>
											<tr>
												<th colspan="14" style="text-align:center">Consignee Details</th>
											</tr>
											<tr>
													<th colspan="2">Company Name</th>
													<th colspan="2">Person Name</th>
													<th colspan="2">Mobile</th>
													<th colspan="2">Email</th>
													<th colspan="2">Address</th>
													<th>Country</th>
													<th>State</th>
													<th>City</th>
													<th>Gst No</th>
											</tr>
											<?while($row_consi = brp_mysqli_fetch_array($consignee_det)){?>
												<tr>
													<td><?=$row_consi['company_name']?></td>
													<td><?=$row_consi['cust_name']?></td>
													<td><?=$row_consi['cust_mobile']?></td>
													<td><?=$row_consi['cust_email']?></td>
													<td><?=$row_consi['cust_address']?></td>
													<td><?=$row_consi['country_name']?></td>
													<td><?=$row_consi['state_name']?></td>
													<td><?=$row_consi['city_name']?></td>
													<td><?=$row_consi['gst_no']?></td>
												</tr>
										<?}?>
										<tr>
												<td colspan="14" style="height:30px"></td>
										</tr>
										<?}?>
										<?$i++;}}else{?>
											<tr>	
												<td colspan="14" style="text-align:center">No Data Found.....!!!!</td>
											</tr>
										<?}?>
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