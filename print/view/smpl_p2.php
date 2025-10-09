<?php 
 $res_id = $_REQUEST['id'];
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';
$res_id = $_REQUEST['id'];
$type='pdf';
if(strtolower($type) == 'pdf') {


$p_id = $dbcon->real_escape_string($_REQUEST['id']);

$bmr_no = "";

$query = "select bmr_no from tbl_smpl_bmr_no where p_id = " . $p_id;
$res = $dbcon->query($query);

$cnt = brp_mysqli_num_rows($res);
if($cnt > 0){
	$row = brp_mysqli_fetch_array($res);			
	$bmr_no = $row['bmr_no'];
}else{
	$bmr_no = load_common_no($dbcon,BMR_NO);
	update_common_no($dbcon,BMR_NO);

	$info['bmr_no'] = $bmr_no;
	$info['p_id'] = $p_id;
	$info['user_id'] =  $_SESSION['user_id'];
	$info['company_id'] = $_SESSION['company_id'];

	$bmr_id = add_record('tbl_smpl_bmr_no', $info, $dbcon);
}


$company_config = getCompanyConfiguration($dbcon);

$s_ql = "SELECT ap.batch_no,ap.process_id,pro.product_id,rp.rp_id,rp.rp_req_qty as wo_qty,ap.p_qty,pro.product_name from tbl_allocate_process as ap
		 LEFT JOIN product_mst as pro ON pro.product_id = ap.p_product_id
		 LEFT JOIN tbl_request_product as rp ON rp.rp_id = ap.p_ref_id
		 WHERE p_id = " . $p_id;
$q=$dbcon->query($s_ql);
$rel=brp_mysqli_fetch_array($q);


$workorder_qty = $rel['wo_qty'];			
	
$q1 = "SELECT release_id,cdate FROM tbl_store_release	WHERE p_id = " . $p_id . " ORDER BY release_id ASC LIMIT 1";
$rel1 = brp_mysqli_fetch_assoc($dbcon->query($q1));


$q2 = "SELECT strn.cdate FROM tbl_store_accept as st 
		LEFT JOIN tbl_batch_data as bt ON bt.batch_id = st.batch_id
		LEFT JOIN tbl_grn_sub_trn as strn ON strn.grn_trn_id = bt.grn_trn_id
		 where st.batch_no LIKE '%". $rel['batch_no'] ."%' ORDER BY store_accept_id DESC LIMIT 1";
$rel2 = brp_mysqli_fetch_assoc($dbcon->query($q2));


$mfg_start_on = date('d/m/Y',strtotime($rel1['cdate']));
$mfg_complete_on = date('d/m/Y',strtotime($rel2['cdate']));


$q3 = "SELECT pro.product_name FROM tbl_request_product as rp 
LEFT JOIN product_mst as pro ON pro.product_id = rp.rp_pid
 WHERE rp.status != 2 and rp.perent_id = " . $rel['rp_id'];
$rel3 = brp_mysqli_fetch_assoc($dbcon->query($q3));



$supplier_tc_no = "";

$q4 = "SELECT supplier_tc_no,cdate FROM tbl_batch_data	WHERE product_id = " . $rel['product_id'] . " and process_id = " . $rel['process_id'] . " and batch_no LIKE '%". $rel['batch_no'] ."%' ORDER BY batch_id ASC LIMIT 1";
$rel4 = brp_mysqli_fetch_assoc($dbcon->query($q4));

$supplier_tc_no = $rel4['supplier_tc_no'];
$supplier_tc_date = date('d/m/Y',strtotime($rel4['cdate']));

$q5 = "SELECT SUM(accept_qty) as store_accept_qty,SUM(qc_sample_qty) as qc_sample_qty FROM tbl_batch_data WHERE process_id = ".$rel['process_id']." and product_id = ".$rel['product_id']." and batch_no LIKE '%". $rel['batch_no'] ."%'";
$rel5 = brp_mysqli_fetch_assoc($dbcon->query($q5));	
	
$store_accept_qty = $rel5['store_accept_qty'];	
$qc_sample_qty = $rel5['qc_sample_qty'];	
//$header =get_header($dbcon,'text-align: center','100%','70px');

$yield = ($store_accept_qty /$workorder_qty) * 100;
$html.='<html>
<head>					
<title>MS JOB CARD </title>

<style type="text/css">
/*
.page{
	width:8.27in;
	height:10.69in;
	}*/
	.nextpage
	{
		page-break-after: always;
	}
	table{
		border-collapse:collapse;
		width:100%;
	}

	table tr,td{
		border:1px solid #000 !important;
		/*page-break-inside:avoid;*/
	}
	.quot_annex_content_div table tr,td{
		padding:5px;
	}
	.blueHeading {
		color: #365f91;
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
	<table>

	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style="text-align:center;font-size:18px;font-weight:bold;padding-bottom:0px;">SMIT MEDIMED PVT.LTD</td>
	</tr>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; ">
	<td colspan="4" style="text-align:center;font-size:18px;font-weight:bold;padding-bottom:0px;">Plot No.10,Phase-1,B/H Prashant Engineering, G.I.D.C Vatava, Ahemdabad - 382445, Gujarata, India</td>
	</tr>
	
	<tr>
	<td colspan="2" style="text-align:left;  "><b>Product Name :-</b> '.$rel['product_name'].' </td>
	<td colspan="2" style="text-align:left; "><b>BMR No : </b> '.$bmr_no.'</td>
	
	</tr>
	<tr>
	<td colspan="2" style="text-align:left; "><b>Lot No :-</b> '.$rel['batch_no'].' </td>
	<td colspan="2" style="text-align:left; "><b>Mfg Lice : </b> '.$company_config['smpl_mfg_licence'].'</td>
	</tr>
	<tr>
	<td colspan="2" style="text-align:left;  "><b>MFG. Started on :-</b> '.$mfg_start_on.' </td>
	<td colspan="2" style="text-align:left; "><b>Mfg Completed On : </b> '.$mfg_complete_on.'</td>
	</tr>
	
	<tr>
	<td colspan="2" style="text-align:left;  "><b>Authorized by :-</b>  </td>
	<td colspan="2" style="text-align:left; "><b>Lot Size : </b>'.$rel['wo_qty'].'</td>
	</tr></table>
	<table>
	
	<tr  style="border-top:1px solid; border-left:1px solid; border-right:1px solid;    ">
	<td colspan="4" style="text-align:Left;font-size:16px;padding-bottom:0px; height:50px;"> A) DELIVERED TO FINISHED GOODS STORIES</td>
	</tr></table>';
	//////////////////////////////////////////Page - 1 start//////////////////////////////////////////////
	
	$html.='<table>
	<tr>
	<td colspan="" style=" text-align:Center; width:20%">SR.NO</td>
	<td  colspan="" style=" text-align:center; width:40%  ">Size </td>
	
	<td colspan="" style=" text-align:Center; width:40% ">Units </td>
	
	</tr>
	<tr>
	<td colspan="" style=" text-align:left; height:25px; ">1</td>
	<td colspan="" style=" text-align:left;"> '.$rel['product_name'].'</td>
	<td colspan="" style=" text-align:left;">'.$store_accept_qty.'</td>
	</tr>
	<tr>
	<td colspan="" style=" text-align:left; height:25px;"></td>
	<td colspan="" style=" text-align:center; "></td>
	<td colspan="" style=" text-align:left;"></td>
	</tr>
	<tr>
	<td colspan="" style=" text-align:left; height:25px;"></td>
	<td colspan="" style=" text-align:center;"></td>
	<td colspan="" style=" text-align:left;"></td>
	</tr>
	<tr>
	<td colspan="" style=" text-align:left; height:25px;"></td>
	<td colspan="" style=" text-align:center;"></td>
	<td colspan="" style=" text-align:left;"></td>
	</tr>
	<tr>
	<td colspan="" style=" text-align:left; height:25px;"></td>
	<td colspan="" style=" text-align:center;"></td>
	<td colspan="" style=" text-align:left;"></td>
	</tr>
	<tr>
	<td colspan="" style=" text-align:left; height:25px;"></td>
	<td colspan="" style=" text-align:center;"></td>
	<td colspan="" style=" text-align:left;"></td>
	</tr>
	<tr>
	<td colspan="" style=" text-align:left; height:25px;"></td>
	<td colspan="" style=" text-align:center;"></td>
	<td colspan="" style=" text-align:left;"></td>
	</tr>
	<tr>
	<td colspan="" style=" text-align:left; height:25px;"></td>
	<td colspan="" style=" text-align:center;"></td>
	<td colspan="" style=" text-align:left;"></td>
	</tr>
	</table>
	
	<table>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; border-bottom:none;   ">
	<td colspan="" style=" text-align:left; width:50%; height:30px;border-right:none;">UNIT PACKED : '.$store_accept_qty.'</td>
	<td colspan="" style=" text-align:left; width:50%; height:30px; border-left:none;">STERILITY DOSE RECEIVED(KGy): </td>
	</tr>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; border-top:none; border-bottom:none;    ">
	<td colspan="" style=" text-align:left; width:50%; height:30px;border-right:none;">Q.C SAMPLES : '.$qc_sample_qty.'</td>
	<td colspan="" style=" text-align:left; width:50%; height:30px; border-left:none;">STERILITY DOSE RECEIVED(KGy): </td>
	</tr>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; border-top:none; border-bottom:none;    ">
	<td colspan="" style=" text-align:left; width:50%; height:30px;border-right:none;">CONTROL SAMPLE :</td>
	<td colspan="" style=" text-align:left; width:50%; height:30px; border-left:none;">STERILITY TEST REPORT NO: </td>
	</tr>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; border-top:none; border-bottom:none;    ">
	<td colspan="2" style=" text-align:left; width:50%; height:30px;border-right:none;">YIELD: '.$yield.'%</td>
	</tr>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; border-top:none; border-bottom:none;    ">
	<td colspan="2" style=" text-align:left; width:50%; height:30px;border-right:none;">SPECIMEN LABLE :</td>
	</tr>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; border-top:none; border-bottom:none;    ">
	<td colspan="2" style=" text-align:left; width:50%; height:175px;border-right:none;"></td>
	</tr>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; border-top:none; border-bottom:none;    ">
	<td colspan="" style=" text-align:left; width:50%; height:30px;border-right:none;">DEPT. HEAD :</td>
	<td colspan="" style=" text-align:left; width:50%; height:30px; border-left:none;">Q.A VERIFICATION & RELEASE : </td>
	</tr>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; border-top:none; border-bottom:none;    ">
	<td colspan="2" style=" text-align:left; width:50%; height:30px;border-right:none;">DATE :</td>
	</tr>
	
	</table>
	
	<table>
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid;">
	<td colspan="" style=" text-align:left;   ">REV.0 </td>
	<td colspan="" style=" text-align:left;   ">1/5/2022 </td>
	<td colspan="" style=" text-align:left;   ">F/QAD/04</td>
	</tr>
	</table>
	
	</div>';
				 
	
	/////////////////////////////////////////////////////////////QC Parameter END////////////////////////////////////////////////////////////
	
	/* Get Terms And Condition Start */

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
/*echo $header;*/
//echo $html;exit;
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','calibri','10','10','10','10','1','1');
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