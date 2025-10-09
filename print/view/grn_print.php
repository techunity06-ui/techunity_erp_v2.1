<?php
require_once '../../vendor/autoload.php';
use Mpdf\Mpdf;
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
// error_reporting(E_ALL);
//include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
$_SESSION['contents']=''; 
$form="Inward Receipt Cum Inspection Report";
$mode="Print";
$grn_id=$dbcon->real_escape_string($_REQUEST['id']);	
$store_release_id=$dbcon->real_escape_string($_REQUEST['store_release_id']);	
$type='pdf';
if(strtolower($type) == 'pdf') {
$query="select po.*,jo.jobwork_no,ppo.purchaseorder_no,state.state_name,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name from tbl_grn as po 
left join tbl_ledger as l on l.l_id=po.vender_id
left join country_mst as country on country.countryid=l.countryid
left join state_mst as state on state.stateid=l.stateid
left join city_mst as city on city.cityid=l.cityid
left join tbl_jobwork as jo on jo.jobwork_id=po.purchaseorder_id
left join tbl_purchaseorder as ppo on ppo.purchaseorder_id=po.purchaseorder_id
where po.grn_id=$grn_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$grn_date = '';
	if($rel['grn_date']!="1970-01-01 00:00:00" && $rel['grn_date']!="0000-00-00 00:00:00")
	{
		$grn_date=date('d-m-Y',strtotime($rel['grn_date']));
	}

	$gir_date = '';
	if($rel['gir_date']!="1970-01-01 00:00:00" && $rel['gir_date']!="0000-00-00 00:00:00")
	{
		$gir_date=date('d-m-Y',strtotime($rel['gir_date']));
	}
	$challan_no = "";

	if(!empty($rel['challan_no'])){
		$challan_no = $rel['challan_no'];
	}else if(!empty($rel['invoice_no'])){
		$challan_no = $rel['invoice_no'];
	}

	/*$work_order_date = '';
	if($rel['work_order_date']!="1970-01-01 00:00:00" && $rel['work_order_date']!="0000-00-00 00:00:00")
	{
		$work_order_date=date('d-m-Y',strtotime($rel['work_order_date']));
	}*/
	
	
	$companyConfiguration=getCompanyConfiguration($dbcon);


	$company_id = $rel['company_id'];
	

	$set="select * from tbl_company where company_id=".$company_id;
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	

	$header = get_header($dbcon,'text-align: left','100%','130px');

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel_['grn_no'].'</title>
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
			/*border:1px solid #000 !important;*/
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}
		br{

		}

		</style>
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
		<!--<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>-->
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		
		<tr>
		<td colspan="3" style="text-align:center; font-size:15px; font-weight:bold;border-right:none;border-top:1px solid;">'.$form.'</td>
		</tr>
		
		<tr>
			<td rowspan="3" style="width:33.33%;border-top:1px solid;vertical-align: top;"> 
				<strong> 
					'.$rel['vender_name'].'	
				</strong>
				
			</td>
			<td style="width:33.33%;border-top:1px solid;"> 
				<strong> 
					<span  style="width:10%">GRN No</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['grn_no'].'</span>
			</td>
			<td style="width:33.33%;border-top:1px solid;"> 
				<strong> 
					<span  style="width:10%">Date</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$grn_date.'</span>
			</td>
		</tr>
		<tr>
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">Challan No</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$challan_no.'</span>
			</td>
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">Date</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$grn_date.'</span>
			</td>
		</tr>
		<tr>
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">GIR No</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['gir_no'].'</span>
			</td>
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">Date</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$gir_date.'</span>
			</td>
		</tr>
		
		</table>
		</div>
		';
		$html.='<div>
				<table style="font-size:12px;border-collapse: collapse;width:100%; border:1px solid" cellpadding="3" cellspacing="3">
				<thead>
				<tr>
				<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
				<th style="width:20%;text-align:center;border:1px solid;">
						Item Code <br>
						P.O.No.<br>
						Indent No.<br>
						Job Card No.</th>
				<th style="width:20%;text-align:center;border:1px solid;">
						Item Details<br>
						Remarks<br>
						Drawing No<br>
						Process</th></br>
				<th style="width:10%;text-align:center;border:1px solid;">UOM</th>
				<th style="width:15%;text-align:center;border:1px solid;">P.O.Qty-UM <br>
						P.O.Qty-UP</th>
				<th style="width:15%;text-align:center;border:1px solid;">GRN.Qty-UM <br>
					GRN.Qty-UP</th>
				<th style="width:15%;text-align:center;border:1px solid;">Received-UM<br>
						Accepted-UP<br>
						Rejected-UP</th>
				</thead>
				<tbody> ';

				if($rel['ref_type'] == '2'){
					$qry="select strn.*,strn.product_conv_qty as trn_conv_qty,perc.unit_name as conv_unit_name,product.*,per.unit_name, hsn.hsn_code, po.purchaseorder_no, ptrn.product_qty as po_qty, ptrn.product_conv_qty as po_conv_qty,ptrn.product_des,rp.product_remark,pr.process_name, dr.drawing_number, prp.job_card_no, rp.indent_no FROM `tbl_grn_sub_trn` as strn
			left join tbl_grn_trn as trn on trn.grn_trn_id=strn.grn_trn_id 
			left join product_mst as product on product.product_id=trn.product_id 
			left join tbl_drawing as dr on dr.drawing_id=product.drawing_id 
			left join process_mst as pr on pr.process_id=trn.process_id 
			left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn 
			left join unit_mst as per on per.unitid=trn.unit_id 
			left join unit_mst as perc on perc.unitid=trn.product_conv_unit 
			left join tbl_purchaseordertrn as ptrn on strn.purchaseordertrn_id = ptrn.purchaseordertrn_id
			left join tbl_purchaseorder as po on po.purchaseorder_id = ptrn.purchaseorder_id
			left join tbl_request_product as rp on rp.rp_id=strn.rp_id 
			left join tbl_request_product as prp on prp.rp_id=rp.perent_id 
			where strn.status = 0 and trn.grn_trn_status=0 and trn.grn_id=".$grn_id." order by grn_trn_sub_id";			
				}else if($rel['ref_type'] == '4'){
					$qry="select trn.*,trn.product_conv_qty as trn_conv_qty,perc.unit_name as conv_unit_name,product.*,per.unit_name, hsn.hsn_code,dr.drawing_number FROM `tbl_grn_trn` as trn
                			
                			left join product_mst as product on product.product_id=trn.product_id 
                			left join tbl_drawing as dr on dr.drawing_id=product.drawing_id 
                			left join process_mst as pr on pr.process_id=trn.process_id 
                			left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn 
                			left join unit_mst as per on per.unitid=trn.unit_id 
                			left join unit_mst as perc on perc.unitid=trn.product_conv_unit 
                			where  trn.grn_trn_status=0 and trn.grn_id=".$grn_id." order by trn.grn_trn_id";			
				}else{
					$qry="select strn.*,strn.product_conv_qty as trn_conv_qty,perc.unit_name as conv_unit_name,product.*,per.unit_name, hsn.hsn_code, po.purchaseorder_no, ptrn.product_qty as po_qty, ptrn.product_conv_qty as po_conv_qty,ptrn.product_des,rp.product_remark,pr.process_name, dr.drawing_number, rp.job_card_no, rp.indent_no FROM `tbl_grn_sub_trn` as strn
						left join tbl_grn_trn as trn on trn.grn_trn_id=strn.grn_trn_id 
						left join product_mst as product on product.product_id=trn.product_id 
						left join tbl_drawing as dr on dr.drawing_id=product.drawing_id 
						left join process_mst as pr on pr.process_id=trn.process_id 
						left join mst_hsn_code as hsn on hsn.hsn_id = product.product_hsn 
						left join unit_mst as per on per.unitid=trn.unit_id 
						left join unit_mst as perc on perc.unitid=trn.product_conv_unit 
						left join tbl_purchaseordertrn as ptrn on strn.purchaseordertrn_id = ptrn.purchaseordertrn_id
						left join tbl_purchaseorder as po on po.purchaseorder_id = ptrn.purchaseorder_id
						left join tbl_request_product as rp on rp.rp_id=strn.rp_id 
						left join tbl_request_product as prp on prp.rp_id=rp.perent_id 
						where strn.status = 0 and trn.grn_trn_status=0 and trn.grn_id=".$grn_id." order by grn_trn_sub_id";
				}
		
		$result=$dbcon->query($qry);		
		$i=1;
		$total_qty=0;
		$total_conv_qty=0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{

			$item_code = "";

			if(!empty($row['product_icode'])){
				$item_code =  $row['product_icode'];				
			}
			if(!empty($row['purchaseorder_no'])){
				$item_code .= "<br>".  $row['purchaseorder_no'];				
			}
			if(!empty($row['indent_no'])){
				$item_code .= "<br>".  $row['indent_no'];				
			}
			if(!empty($row['job_card_no'])){
				$item_code .= "<br>".  $row['job_card_no'];				
			}

			$item_details ="";
			if(!empty($row['product_name'])){
				$item_details =  $row['product_name'];				
			}
			if(!empty($row['product_desc'])){
				$item_details .= "<br>".  $row['product_desc'];				
			}
			if(!empty($row['drawing_number'])){
				$item_details .= "<br>".  $row['drawing_number'];				
			}
			if(!empty($row['process_name'])){
				$item_details .= "<br>".  $row['process_name'];				
			}

			if(!empty($row['product_remark'])){
				$item_details .= "<br>".  $row['product_remark'];				
			}
			if(!empty($row['product_des'])){
				$item_details .= "<br>".  $row['product_des'];				
			}

			$total_qty = $total_qty + $row['product_qty'];
			$total_conv_qty = $total_conv_qty + $row['trn_conv_qty'];
			
			$html.='<tr>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">'.$i.'</td>
			<td style="text-align:left;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.$item_code.'</td>';
			$html .='
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.$item_details.'
			</td>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.$row['unit_name'].' <br> ' . $row['conv_unit_name'] .'
			</td>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.number_format($row['po_qty'],4,".","").' <br> ' . number_format($row['po_conv_qty'],4,".","") .'
			</td>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.number_format($row['product_qty'],4,".","").' <br> ' . number_format($row['trn_conv_qty'],4,".","") .'
			</td>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.number_format($row['product_qty'],4,".","").' <br> ' . number_format($row['trn_conv_qty'],4,".","") .'<br> <span style="color:red">0.00</span></td>';
			
			$html.='</tr>';


			$totalqty=$totalqty+$row['release_qty'];
			$i++; 
		}

		$html .= '</table></div><div>';
		
		$sub_total=0; $bill_sundry=0;
		$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<tbody>
			<tr>
			<td colspan="3" style="text-align:right;border:1px solid; font-weight: bold;">Total Qty</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;width:15%">'.number_format($total_qty,4,".","").'<br>'.number_format($total_conv_qty,4,".","").'</td>
			<td style="width:15%;border:1px solid;"></td>
		</tr>
		</tbody></table>
		';
				
		$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr style="">
			<td  style="height:80px; width:60%;  text-align:left;  border-left:1px solid; border-bottom:1px solid !important;border-top:1px solid; font-weight: bold;">
				<strong>Received By </strong><br>
				<strong><br>Received Date </strong><br>
				<strong><br>Store Incharge </strong><br>
			</td>
			<td  style="height:80px; width:40%;  text-align:left;  border-bottom:1px solid !important;border-top:1px solid;border-right:1px solid; font-weight: bold;">
				<strong> Inspected By</strong><br> 
				<strong><br> Inspected Date</strong><br> 
				<strong><br> QC Manager</strong><br> 
			</td>
			
		</tr>

				<!--page1 end-->';
				$html.='</table>
				</div>
				<div style="clear:both;"></div>
				</div>';
			$terms_qry="select qtrm.*,mst.tc_name from tbl_purchaseorder_terms_trn as qtrm 
        left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
        where qtrm.po_terms_trn_status=0 and qtrm.purchaseorder_id=".$rel['purchaseorder_id']." order by qtrm.tc_priority";
        $terms_qry_rs=$dbcon->query($terms_qry);
       if(brp_mysqli_num_rows($terms_qry_rs)){
			
				$html.=' <center class="nextpage"></center>';

		
		//$html .= '<center class="nextpage"></center>';
		
		
        $html.='
        <h3 style="text-align:center;">Terms & Conditions for Purchase Order No : <u>'.$rel['purchaseorder_no'].'</u></h3>
        <div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
        	$t=1;
        	while($term_rel=mysqli_fetch_array($terms_qry_rs)){
        	    $string=(nl2br($term_rel['tc_details']));
                
        		$html.='<tr>
        			<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">'.$t.'</td>
        			<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px;">'.$term_rel['tc_name'].'</td>
        			<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
        		</tr>';
        		$t++;
        	}
        	  $html .="</table></div>";
       }
		

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		// echo $html;exit;
		ob_end_clean();
		// include("../../view/export/mpdf/mpdf.php");
		// $mpdf=new mPDF('','A4','0','calibri','10','10','35','10','1','1');
		include("../../vendor/mpdf/mpdf/src/Mpdf.php");
		$mpdf = new Mpdf(['format' => 'A4','margin_left' => 10,'margin_right' => 10,'margin_top' => 35,'margin_bottom' => 10,'margin_header' => 1,'margin_footer' => 1,'default_font' => 'calibri']);
		
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = 'B'; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = 'B'; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);

		//Show page number
		$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		$mpdf->SetFooter('{PAGENO}{nbpg}');

		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$purchaseorder_id.'.pdf','f');
		ob_clean();
		return 'purchase_order_'.$purchaseorder_id.'.pdf';
	}

?>