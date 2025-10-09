<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';

$type='pdf';
if(strtolower($type) == 'pdf') {
	$qctrn_id = $dbcon->real_escape_string($_REQUEST['id']);

	$sql = "select qc.qc_date,qc.qc_no, qc.check_tc_no, grn.grn_no,qtr.qc_product, grn.grn_date, pro.product_name, led.l_name, grn.challan_no, qtr.qc_product_qty,un.unit_name,bat.batch_no,bat.supplier_tc_no from tbl_qc_trn as qtr 
	left join tbl_qc as qc on qc.qc_id = qtr.qc_id
	left join unit_mst as un on un.unitid = qc.qc_unit 
	left join product_mst as pro on pro.product_id = qtr.qc_product
	left join tbl_grn_trn as gt on gt.grn_trn_id = qc.grn_trn_id
	left join tbl_grn as grn on grn.grn_id = gt.grn_id
	left join tbl_batch_data as bat on bat.grn_trn_id = gt.grn_trn_id
	left join tbl_ledger as led on led.l_id = grn.vender_id
	where qtr.qctrn_id=".$qctrn_id;
	$result = $dbcon->query($sql);
	$rel = brp_mysqli_fetch_array($result);

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$qc_date = '';
	if($rel['qc_date']!="1970-01-01" && $rel['qc_date']!="0000-00-00"){
		$qc_date = date('d-m-Y',strtotime($rel['qc_date']));
	}

	$grn_date = '';
	if($rel['grn_date']!="1970-01-01" && $rel['grn_date']!="0000-00-00"){
		$grn_date = date('d-m-Y',strtotime($rel['grn_date']));
	}

	$saperator = $companyConfiguration['heat_no_saperator'];
	
	$heat_no  = explode("$saperator",$rel['batch_no'],2);
	/* echo "<pre>";print_r($heat_no); echo"</pre>";
	exit; */

$header = '<table>
	<tr>
		<td rowspan="3" style="width:33.33%;border:1px solid"></td>
		<td colspan="2" style="width:33.33%;border:1px solid;text-align:center"><strong>FORMS AND FORMATS</strong></td>
		<td style="width:16.66%;border:1px solid;text-align:center"><strong>FORM NO.:</strong></td>
		<td style="width:16.66%;border:1px solid;text-align:center"><strong>F:QCD:01</strong></td>
	</tr>

	<tr>
		<td colspan="2" style="border:1px solid;text-align:center"><strong>INCOMING INSPECTION AND TEST REPORT</strong></td>
		<td colspan="2" style="border:1px solid;text-align:center"><strong>PAGE 1 OF 1</strong></td>
	</tr>

	<tr>
		<td style="border:1px solid;text-align:center"><strong>REV NO.: 01</strong></td>
		<td style="border:1px solid;text-align:center"><strong>ISSUE NO.:01</strong></td>
		<td style="border:1px solid;text-align:center"><strong>DATE</strong></td>
		<td style="border:1px solid;text-align:center"><strong>'.$qc_date.'</strong></td>
	</tr>
</table>';
$html='';

$html.='<html>
<head>					
<title>INSPECTION NO - '.$rel['qc_no'].'</title>

<style type="text/css">

	.nextpage
	{
		page-break-after: always;
	}
	table{
		border-collapse:collapse;
		width:100%;
	}
	
	.blueHeading {
		color: #365f91;
	}
	td {
		height : 30px;
		padding-left : 5px;
		font-size:18px;
	}

	</style>
	</head>
	<body>
	<!--Show Logo in other pages-->
	<!--<htmlpageheader name="otherpages" style="display:none">
	<div style="text-align:center">'.$header.'</div>
	</htmlpageheader>-->
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>-->
	<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
	<div>
		<table style="width:100%">
			<tr>	
				<td style="width:50%;border:1px solid"><strong>Inward No :</strong> '.$rel['grn_no'].'</td>
				<td style="width:50%;border:1px solid"><strong>Date :</strong> '.$grn_date.'</td>
			</tr>

			<tr>	
				<td colspan="2" style="border:1px solid"><strong>Item Description :</strong> '.$rel['product_name'].'</td>
			</tr>

			<tr>	
				<td colspan="2" style="border:1px solid"><strong>Dimension</strong></td>
			</tr>
			<tr>	
				<td colspan="2" style="border:1px solid"><strong>Material Specification / Grade</strong></td>
			</tr>
			<tr>	
				<td colspan="2" style="border:1px solid"><strong>Name Of Vendor / Supplier :</strong> '.$rel['l_name'].'</td>
			</tr>
			<tr>
				<td colspan="2" style="border:1px solid"><strong>Suppliers D.C.No. :</strong> '.$rel['challan_no'].'</td>
			</tr>
		</table>
		<table>
			<tr>
				<td style="width:33.33%;border:1px solid;border-right:none"><strong>Qty.:</strong>  '.$rel['qc_product_qty'].' '.$rel['unit_name'].'</td>
				<td style="width:33.33%;border:1px solid;border-left:none"><strong>Weight:-</strong></td>
				<td style="width:33.33%;border:1px solid"><strong>Heat No:-</strong>'.$heat_no['1'].'</td>
			</tr>
			<tr>
				<td colspan="3" style="border:1px solid;"><strong>Supplier T.C.No : </strong> '.$rel['supplier_tc_no'].'</td>
			</tr>
			<tr>
				<td colspan="2" style="border:1px solid;"><strong>SMPL Check T.C.No : </strong> '.$rel['check_tc_no'].'</td>
				<td colspan="2" style="border:1px solid;"><strong>SMPL Heat No:</strong> '.$rel['batch_no'].'</td>
			</tr>
			<tr>
				<td style="width:33.33%;border:1px solid;"><strong>Properities</strong></td>
				<td style="width:33.33%;border:1px solid;"><strong>T.C.No:</strong></td>
				<td style="width:33.33%;border:1px solid"><strong>Result</strong></td>
			</tr>
			<tr>
				<td style="width:33.33%;border:1px solid;"> <strong>- Chemical</strong></td>
				<td style="width:33.33%;border:1px solid;"> <strong></strong></td>
				<td style="width:33.33%;border:1px solid"></td>
			</tr>
			<tr>
				<td style="width:33.33%;border:1px solid;"><strong>- Physical</strong></td>
				<td style="width:33.33%;border:1px solid;"></td>
				<td style="width:33.33%;border:1px solid"></td>
			</tr>
			<tr>
				<td style="width:33.33%;border:1px solid;"><strong>- Micro structure</strong></td>
				<td style="width:33.33%;border:1px solid;"></td>
				<td style="width:33.33%;border:1px solid"></td>
			</tr>
			<tr>
				<td style="width:33.33%;border:1px solid;"></td>
				<td style="width:33.33%;border:1px solid;"></td>
				<td style="width:33.33%;border:1px solid"></td>
			</tr>
		</table>
		<table>
			<tr>
				<td colspan="3" style="width:50%;border:1px solid"><strong>Qty. Recd. :</strong> </td>
				<td colspan="6" style="width:50%;border:1px solid"><strong>Qty Sampled :</strong> </td>
			</tr>';
			$html .='<tr>
				<td rowspan="2" style="width:6%;border:1px solid;text-align:center"><strong>Sr.<br>No</strong></td>
				<td rowspan="2" style="width:22%;border:1px solid;text-align:center"><strong>Parameters</strong></td>
				<td rowspan="2" style="width:22%;border:1px solid;text-align:center"><strong>Specification With Tolerance</strong></td>
				<td colspan="6" style="width:50%;border:1px solid;text-align:center"><strong>Result Of Observation</strong></td>
			</tr>
			<tr>
				<td style="border:1px solid;text-align:center"><strong>1</strong></td>
				<td style="border:1px solid;text-align:center"><strong>2</strong></td>
				<td style="border:1px solid;text-align:center"><strong>3</strong></td>
				<td style="border:1px solid;text-align:center"><strong>4</strong></td>
				<td style="border:1px solid;text-align:center"><strong>5</strong></td>
				<td style="border:1px solid;text-align:center"><strong>Status</strong></td>
			</tr>';
			
			$pram = "select * from tbl_product_parameter as par 
			left join tbl_qc_param as parm on parm.p_id= par.param_id
			where par.process_id='-1' and par.product_id = ".$rel['qc_product'];
			
			$resu_par = $dbcon->query($pram);
			$cnt = brp_mysqli_num_rows($resu_par);

			$i=1;
			while($para_row = brp_mysqli_fetch_array($resu_par)){
				$html .='<tr>
					<td style="border:1px solid;text-align:center"><strong>'.$i.'</strong></td>
					<td style="border:1px solid;text-align:center">'.$para_row['p_name'].'</td>
					<td style="border:1px solid;text-align:center">'.$para_row['tolerance_plus'].'</td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
				</tr>';
				$i++;
			}

			$cnt = 5-$cnt;
			$j=$cnt;
			for($i=1; $i<=$cnt; $i++){
				$html .='<tr>
					<td style="border:1px solid;text-align:center"><strong>'.$j.'</strong></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
					<td style="border:1px solid;text-align:center"></td>
				</tr>';
				$j++;				
			}

			$html.='<tr>
				<td colspan="9" style="border:1px solid"><strong>Finishing & Making :</strong> </td>
			</tr>
			<tr>
				<td colspan="9" style="border:1px solid"><strong>Marking Proivded By Q.C.:</strong> </td>
			</tr>
			<tr>
				<td colspan="9" style="border:1px solid"><strong>Test Status  -> Accepted / Rejected</strong> </td>
			</tr>
			<tr>
				<td colspan="9" style="border:1px solid"><strong>Remarks -></strong> </td>
			</tr>
			<tr>
				<td colspan="3" style="width:50%;border:1px solid;border-bottom:none"><strong>Inspected By</strong> <br><br><br></td>
				<td colspan="6" style="width:50%;border:1px solid;border-bottom:none"><strong> Reviewed And Approved By</strong> <br><br><br></td>
			</tr>
			<tr>
				<td colspan="3" style="width:50%;border:1px solid;border-top:none"></td>
				<td colspan="6" style="width:50%;border:1px solid;border-top:none;text-align:center"><strong>Works Head / QC In Charge</strong></td>
			</tr>

		</table>
	</div>
	</body>
	</html>';
/*echo $header;
echo $html;exit;*/
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','calibri','10','10','40','10','1','1');
//		$mdf->SetFont('ProximaNova');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);
$mpdf->SetWatermarkText();
$mpdf->showWatermarkText = true;
$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
$mpdf->WriteHTML($html);
$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
ob_clean();
return 'Purchase Order'.$rel['purchaseorder_no'].'.pdf';
}	
?>
