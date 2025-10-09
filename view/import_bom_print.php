<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	$token = md5(rand(1000,9999));
	$_SESSION['token'] = $token;
	$_SESSION['contents']='';
	$form="BOM";
	$mode="Print";
	$bom_id = $dbcon->real_escape_string($_REQUEST['id']);
	$query="select bom.*,product.product_name,product.product_type from bom_temp as bom 
		left join product_mst as product on product.product_id=bom.bom_product
		where sr_no='main'";
	$rel=mysqli_fetch_assoc($dbcon->query($query));	
	
	 $query="select bom.*,product.product_name,product.image_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name,product.product_type,dwg.drawing_number from bom_temp as bom
	left join product_mst as product on product.product_id=bom.product_id
			left join unit_mst as bunit on bunit.unitid=bom.unit_id
			left join unit_mst as cunit on cunit.unitid=bom.unit_id
			left join tbl_drawing as dwg on dwg.drawing_id=product.drawing_id
		where bom.sr_no='main' and bom.bom_temp_id=".$bom_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	//exit;
	 $set="select * from tbl_company where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	
	
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
			<?php include_once('../include/include_top_menu.php');?>
			<?php include_once('../include/left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
								  <h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
									  <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
									  <li><a href="<?=ROOT.'bom_upload_list'?>">Import BOM List</a></li>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
								  <?=$form?> <?=$mode?>
								</header>	
								<div class="panel-body">
									<div class="col-md-12"></div>
										<label class="col-md-3 control-label"></label>
										<div class="col-lg-4"></div>
										<input type="hidden" name="typename" id="typename" value="<?=$rel['invoice_type']?>">
										<?php ob_start(); ?>
											<div class="col-lg-12 table-responsive" id="receipt_print">	<div class="col-md-12" style=" margin-top:10px;" id="print1">
												<div class="col-md-12" > 
													<button type="submit" class="btn btn-success" onClick="add_bom1(<?=$bom_id?>);"> Add Bom</button>
												</div>
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
												<table width="100%" border="0" style="" id="">
													<tr>
														<td width="90%" style="text-align:center"> 
															<strong style="font-size:16px">
																Excel Bom Upload 
															</strong>
														</td>
														<td width="10%" style="text-align:center"> 
															<strong style="font-size:12px">
																<b class="data_title">ORIGINAL</b>
															</strong>
														</td>
													</tr>
												</table>
												<table width="100%" class="maintable" style="font-size: 12px;" id="invoice_type" >
													<thead>
														<tr height="30px">					
															<th  width="5%" style="text-align:center;border:1px solid;">
																<strong>SR. NO.</strong>
															</th>
															</strong></th>
															<th width="45%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Item Description</strong></th>
															<th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Item Type</strong></th>
															<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;" ><strong>Qty</strong></th>
															<th width="20%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;"><strong>Process</strong></th>
															<th width="5%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top:1px solid;"><strong>Action</strong></th>
															
														</tr>
													</thead>
													<tbody style="border: 1px solid;">
														<tr>
															<td style="border:1px #444 solid;" >0</td>
															<td style="border:1px #444 solid;" ><?=$rel['product_name']?></td>
															<td style="border:1px #444 solid;" ><?=get_product_type_by_id($dbcon,$rel['product_type'])?></td>
															<td style="border:1px #444 solid;" >
																	<?php 
																	echo $rel['qty'];  ?> <?=$rel['base_unit_name']?>
																
															</td>
															<td style="border:1px #444 solid;" >
																<?php $query3="select mst.*,p.process_name as pname from bom_process_temp as mst 
																left join process_mst as p on p.process_name=mst.process_name where mst.bom_temp_id=".$rel['bom_temp_id']." order by bom_process_temp_id";
																$result3=$dbcon->query($query3);
																$cnt3=mysqli_num_rows($result3);
																if($cnt3>0){ ?>
																	<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
																		<tr>
																			<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
																			<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
																			<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
																		</tr>
																		<?php $p=1;
																		while($rel3=mysqli_fetch_assoc($result3)){ 
																			if(strtolower($rel3['process_type_name'])=="inhouse"){
																				$process_type="Inhouse";
																			}else if(strtolower($rel3['process_type_name'])=="outside"){
																				$process_type="Outside";
																			}else{
																				$process_type="Wrong Type";
																			}
																			
																			if(!empty($rel3['pname'])){
																				$procss_name=$rel3['pname'];
																			}else{
																				$procss_name=$rel3['process_name']." - Not Match";
																			}
																		?>
																			<tr>
																				<td style="border:0.5px #444 solid;text-align:center;" ><?=$p?></td>
																				<td style="border:0.5px #444 solid;text-align:center;" ><?=$process_type?></td>
																				<td style="border:0.5px #444 solid;text-align:center;" ><?=$procss_name?></td>
																			</tr>
																		<?php $p++;
																		} ?>
																	</table>
																	<?php } ?>
			
															</td>
														</tr>
		
														<?php
															
														 $qry="select bom.*,product.product_name as proname,product.image_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name,product.product_type,dwg.drawing_number from bom_temp as bom
														left join product_mst as product on product.product_id=bom.product_id
																left join unit_mst as bunit on bunit.unitid=bom.unit_id
																left join unit_mst as cunit on cunit.unitid=bom.unit_id
																left join tbl_drawing as dwg on dwg.drawing_id=product.drawing_id
															where bom.perent_id=".$rel['bom_temp_id']."";
														$result1=$dbcon->query($qry);		
														$i=1;$total=0;$discount=0;
														$_SESSION['bom_tot']=0;
														$cnt1=mysqli_num_rows($result);
														$cnt=1;
														while($rel1=mysqli_fetch_assoc($result1))
														{
															if(!empty($rel1['product_id'])){
																$proname=$rel1['proname']." - (".$rel1['drawing_number'].")";
															}else{
																$proname=$rel1['product_name'];
																$erro_pro="</br><span style='color:red'>Product Name Not Metch In ERP<span>";
															}
															
															if(!empty($rel1['unit_id'])){
																$unit_name=$rel1['base_unit_name'];
															}else{
																$unit_name=$rel1['unit_name'];
																$erro_unit="</br><span style='color:red'>Unit Name Not Metch In ERP<span>";
															}
													?>
															<tr>
																<td style="border:1px #444 solid;" ><?=$i?></td>
																<td style="border:1px #444 solid;" ><?=$proname?> <?=$erro_pro?></td>
																<td style="border:1px #444 solid;" ><?=get_product_type_by_id($dbcon,$rel1['product_type'])?></td>
																<td style="border:1px #444 solid;" >
																	<?php echo $rel1['qty'];  echo $unit_name; ?> <?=$erro_unit?>
																</td>
																<td style="border:1px #444 solid;" >
																<?php $query3="select mst.*,p.process_name as pname from bom_process_temp as mst 
																left join process_mst as p on p.process_name=mst.process_name where mst.bom_temp_id=".$rel1['bom_temp_id']." order by bom_process_temp_id";
																$result3=$dbcon->query($query3);
																$cnt3=mysqli_num_rows($result3);
																if($cnt3>0){ ?>
																	<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
																		<tr>
																			<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
																			<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
																			<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
																		</tr>
																		<?php $p=1;
																		while($rel3=mysqli_fetch_assoc($result3)){ 
																			if(strtolower($rel3['process_type_name'])=="inhouse"){
																				$process_type="Inhouse";
																			}else if(strtolower($rel3['process_type_name'])=="outside"){
																				$process_type="Outside";
																			}else{
																				$process_type="Wrong Type";
																			}
																			
																			if(!empty($rel3['pname'])){
																				$procss_name=$rel3['pname'];
																			}else{
																				$procss_name=$rel3['process_name']." - Not Match";
																			}
																		?>
																			<tr>
																				<td style="border:0.5px #444 solid;text-align:center;" ><?=$p?></td>
																				<td style="border:0.5px #444 solid;text-align:center;" ><?=$process_type?></td>
																				<td style="border:0.5px #444 solid;text-align:center;" ><?=$procss_name?></td>
																			</tr>
																		<?php $p++;
																		} ?>
																	</table>
																	<?php } ?>
																</td>
																<td style="border:1px #444 solid;">
																	<button class="btn btn-xs btn-warning" data-original-title="Edit " data-toggle="tooltip" data-placement="top" onClick="open_update(<?=$rel1['bom_temp_id']?>)"><i class="fa fa-pencil"></i></button>
																</td>
															</tr>
															<?=bom_show_excel($dbcon,$rel1['bom_temp_id'],$rel1['qty'],$i,$call,$space);?>
				
														<?php  $i++;  }	?>
													</tbody>	
												</table>
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
	 <?php include_once('../include/add_bom_data.php');?>
	<?php include_once('../include/footer.php');?>
	
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
	<?php include_once('../include/include_js_file.php');?>   
   <script src="<?=ROOT?>js/app/import_product_opening_stock.js?<?=time()?>"></script>
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
  docprint.document.write('<head><title><?php echo TITLE;?></title>');
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

<?php 
function bom_show_excel($dbcon,$bom_temp_id,$qty,$num,$call,$space)
{
	$query1="select bom.*,product.product_name as proname,product.image_name,bunit.unit_name as base_unit_name,bunit.unit_name as conv_unit_name,product.product_type,dwg.drawing_number from bom_temp as bom
	left join product_mst as product on product.product_id=bom.product_id
			left join unit_mst as bunit on bunit.unitid=bom.unit_id
			left join unit_mst as cunit on cunit.unitid=bom.unit_id
			left join tbl_drawing as dwg on dwg.drawing_id=product.drawing_id
		where bom.perent_id=".$bom_temp_id."";
	$result1=$dbcon->query($query1);
	$k=1;$new_call=$call+1;
				while($rel1=mysqli_fetch_assoc($result1))
				{ 
				
					if(!empty($rel1['product_id'])){
						$proname=$rel1['proname']." - (".$rel1['drawing_number'].")";
					}else{
						$proname=$rel1['product_name'];
						$erro_pro="</br><span style='color:red'>Product Name Not Metch In ERP<span>";
					}
					
					if(!empty($rel1['unit_id'])){
						$unit_name=$rel1['base_unit_name'];
					}else{
						$unit_name=$rel1['unit_name'];
						$erro_unit="</br><span style='color:red'>Unit Name Not Metch In ERP<span>";
					}
					
					$new_num=$num.".".$k; 

					$html .= '<tr>
					<td style="border:0.5px #444 solid;">'.$new_num.'</td>
					<td style="border:0.5px #444 solid;">'.$proname.' '.$erro_pro.'</td>
					<td style="border:1px #444 solid;" >'.get_product_type_by_id($dbcon,$rel1['product_type']).'</td>';
					
					$html .='<td style="border:1px #444 solid;" >'.$rel1["qty"].' '.$unit_name.'  '.$erro_unit.'</td>';
					$html .= '
					<td style="border:1px #444 solid;" >';
					$query3="select mst.*,p.process_name as pname from bom_process_temp as mst 
				left join process_mst as p on p.process_name=mst.process_name where mst.bom_temp_id=".$rel1['bom_temp_id']." order by bom_process_temp_id";
				$result3=$dbcon->query($query3);
				$cnt3=mysqli_num_rows($result3);
				if($cnt3>0){ 
					$html .= '<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
						</tr>';
						 $p=1;
						while($rel3=mysqli_fetch_assoc($result3)){ 
							if(strtolower($rel3['process_type_name'])=="inhouse"){
								$process_type="Inhouse";
							}else if(strtolower($rel3['process_type_name'])=="outside"){
								$process_type="Outside";
							}else{
								$process_type="Wrong Type";
							}
							
							if(!empty($rel3['pname'])){
								$procss_name=$rel3['pname'];
							}else{
								$procss_name=$rel3['process_name']." - Not Match";
							}
						
							$html .='<tr>
								<td style="border:0.5px #444 solid;text-align:center;" >'.$p.'</td>
								<td style="border:0.5px #444 solid;text-align:center;" >'.$process_type.'</td>
								<td style="border:0.5px #444 solid;text-align:center;" >'.$procss_name.'</td>
							</tr>';
						 $p++;
						} 
					$html .='</table>';
					 } 
					$html .= '</td>
					<td style="border:1px #444 solid;">
							<button class="btn btn-xs btn-warning" data-original-title="Edit " data-toggle="tooltip" data-placement="top" onClick="open_update('.$rel1['bom_temp_id'].')"><i class="fa fa-pencil"></i></button>
						</td>
					</tr>';
					$html .=  bom_show_excel($dbcon,$rel1['bom_temp_id'],$rel1['qty'],$new_num,$new_call,$space);
					$k++;
				}
				return $html;
			}



?>