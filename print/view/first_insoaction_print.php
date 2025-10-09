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

	$sql = "select qc.* from tbl_qc_trn as qtr
    left join tbl_qc as qc on qc.qc_id = qtr.qc_id
    where qctrn_id=".$qctrn_id;
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
		<td colspan="2" style="border:1px solid;text-align:center"><strong>FIRST PIECE INSPPECTION REPORT</strong></td>
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
<title>FIRST INSPECTION NO - '.$rel['qc_no'].'</title>

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
        <table style="width:100%;border:1px solid;font-size:16px">
            <tr>
                <th style="border:1px solid;width:10%;font-size:16px">Date</th>
                <th style="border:1px solid;width:10%;font-size:16px">Machine No.</th>
                <th style="border:1px solid;width:10%;font-size:16px">Description</th>
                <th style="border:1px solid;width:10%;font-size:16px">Workorder No</th>
                <th style="border:1px solid;width:10%;font-size:16px">Code No.</th>
                <th style="border:1px solid;width:10%;font-size:16px">Dimention Required</th>
                <th style="border:1px solid;width:10%;font-size:16px">Dimention Observed</th>
                <th style="border:1px solid;width:10%;font-size:16px">Remarks</th>
                <th style="border:1px solid;width:10%;font-size:16px">Inspected By</th>
            </tr>';
        
            $query_trn ='select pro.product_name, pro.product_icode, pra.p_name, res.resource_name, sm.po_req_no,us.user_name,gr.remark from tbl_qc_trn as qtrn 
            left join tbl_qc as qc on qc.qc_id=qtrn.qc_id
            left join tbl_grn_sub_trn as gstr on gstr.grn_trn_id = qc.grn_trn_id
            left join tbl_grn_trn as gt on gt.grn_trn_id = gstr.grn_trn_id
            left join tbl_grn as gr on gr.grn_id = gt.grn_id
            left join tbl_request_product as rp on rp.rp_id = gstr.rp_id
            left join tbl_set_main_process as sm on sm.sp_id  = rp.sp_id
            left join product_mst as pro on pro.product_id = qtrn.qc_product
            left join tbl_product_parameter as par on par.product_id = qtrn.qc_product
            left join tbl_qc_param as pra on pra.p_id = par.param_id
            left join tbl_allocate_process as ap on ap.p_ref_id = gstr.rp_id and ap.process_id=qc.process_id 
            left join tbl_resource as res on res.resource_id = ap.resource_id
            left join users as us on us.user_id = ap.user_id
            where qtrn.qctrn_id='.$qctrn_id;
            $result_trn = $dbcon->query($query_trn);
            $cnt = brp_mysqli_num_rows($result_trn);
            $j=1;
            while($row = brp_mysqli_fetch_array($result_trn)){
                $html.='<tr>
                    <td style="border:1px solid;font-size:14px">'.$qc_date.'</td>
                    <td style="border:1px solid;font-size:14px">'.$row['resource_name'].'</td>
                    <td style="border:1px solid;font-size:14px">'.$row['product_name'].'</td>
                    <td style="border:1px solid;font-size:14px">'.$row['po_req_no'].'</td>
                    <td style="border:1px solid;font-size:14px">'.$row['product_icode'].'</td>
                    <td style="border:1px solid;font-size:14px">'.$row['p_name'].'</td>
                    <td style="border:1px solid;font-size:14px"></td>
                    <td style="border:1px solid;font-size:14px">'.$row['remark'].'</td>
                    <td style="border:1px solid;font-size:14px">'.$row['user_name'].'</td>
                </tr>';
                $j++;
            }
            $cnt = 25;
            for($i=1;$i<=$cnt;$i++){
                    $html.='<tr>
                        <td style="border:1px solid;"></td>
                        <td style="border:1px solid;"></td>
                        <td style="border:1px solid;"></td>
                        <td style="border:1px solid;"></td>
                        <td style="border:1px solid;"></td>
                        <td style="border:1px solid;"></td>
                        <td style="border:1px solid;"></td>
                        <td style="border:1px solid;"></td>
                        <td style="border:1px solid;"></td>
                    </tr>';
            } 
            
        $html .='</table>
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
