<?php session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
//include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
$_SESSION['contents']=''; 
$form="MATERIAL ISSUE SLIP";
$mode="Print";
$p_id=$dbcon->real_escape_string($_REQUEST['p_id']);	
$store_release_id=$dbcon->real_escape_string($_REQUEST['store_release_id']);	
$type='pdf';
if(strtolower($type) == 'pdf') {
$query="select ap.p_id, ap.process_id,ap.p_qty, ap.pen_qty,p.product_icode, p.product_name,p.product_desc,GROUP_CONCAT(req.job_card_no) as job_card_no, GROUP_CONCAT(req.job_card_date) as job_card_date, GROUP_CONCAT(smain.po_req_no) as work_order_no, GROUP_CONCAT(smain.po_req_date) as work_order_date, umst.unit_name, req.rp_id as req_id, pr.process_name, d.drawing_number 
 	 	from tbl_allocate_process as ap
		left join product_mst as p on p.product_id=ap.p_product_id 
		left join tbl_drawing as d on d.drawing_id=p.drawing_id 
		left join process_mst as pr on pr.process_id=ap.process_id
		left join tbl_request_product req on req.rp_id=ap.p_ref_id
		left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
		left join unit_mst as umst on umst.unitid=ap.process_unit
		where ap.p_id in (".$p_id.")";
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$delivery_type = $rel['delivery_type'];
	$transportation_name = ($rel['transportation_name']!='0')?$rel['transportation_name']:'';
	$job_card_date = '';
	if($rel['job_card_date']!="1970-01-01 00:00:00" && $rel['job_card_date']!="0000-00-00 00:00:00")
	{
		$job_card_date=date('d-m-Y',strtotime($rel['job_card_date']));
	}

	$work_order_date = '';
	if($rel['work_order_date']!="1970-01-01 00:00:00" && $rel['work_order_date']!="0000-00-00 00:00:00")
	{
		$work_order_date=date('d-m-Y',strtotime($rel['work_order_date']));
	}
	
	
	$companyConfiguration=getCompanyConfiguration($dbcon);


	$query2 = "select issue_no,issue_date,company_id,release_qty from tbl_store_release where release_id = " . $store_release_id;
	$issue_rw = brp_mysqli_fetch_assoc($dbcon->query($query2));

	$issue_date = '';
	if($issue_rw['issue_date']!="1970-01-01" && $issue_rw['issue_date']!="0000-00-00")
	{
		$issue_date=date('d-m-Y',strtotime($issue_rw['issue_date']));
	}

	$company_id = $issue_rw['company_id'];
	

	$set="select * from tbl_company where company_id=".$company_id;
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	

	$header = get_header($dbcon,'text-align: left','100%','130px');

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$issue_rw['issue_no'].'</title>
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
			<td style="width:33.33%;border-top:1px solid;"> 
				<strong> 
					<span style="width:10%">Issue No.</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$issue_rw['issue_no'].'</span>
			</td>
			<td style="width:33.33%;border-top:1px solid;"> 
				<strong> 
					<span  style="width:10%">Workorder No.</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['work_order_no'].'</span>
			</td>
			<td style="width:33.33%;border-top:1px solid;"> 
				<strong> 
					<span  style="width:10%">Jobcard No.</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['job_card_no'].'</span>
			</td>
		</tr>
		<tr>
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">Issue Date.</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$issue_date.'</span>
			</td>
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">Workorder Date.</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$work_order_date.'</span>
			</td>
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">Jobcard Date.</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$job_card_date.'</span>
			</td>
		</tr>
		<tr>
			<td style="width:33.33%;border-top:1px solid;"> 
				<strong> 
					<span style="width:10%">Item Code</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['product_icode'].'</span>
			</td>
			<td style="width:33.33%;border-top:1px solid;"></td>
			<td style="width:33.33%;border-top:1px solid;"> 
				<strong> 
					<span  style="width:10%">Department</span>
					<span style="margin-left:39px">:</span>
				</strong>
				<span style="margin-left:15px">	</span>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="width:66.66%"> 
				<strong> 
					<span style="width:10%">Item Description</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['product_name'].'</span>
			</td>
			
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">Process</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['process_name'].'</span>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="width:66.66%"> 
				<strong> 
					<span style="width:10%">Drawing No.</span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$rel['drawing_number'].'</span>
			</td>
			<td style="width:33.33%"> 
				<strong> 
					<span style="width:10%">QTY </span>
					<span style="margin-left:30px">:</span>
				</strong>
				<span style="margin-left:15px">	'.$issue_rw['release_qty'].'</span>
			</td>
		</tr>
		</table>
		</div>
		';
		$html.='<div>
				<table style="font-size:12px;border-collapse: collapse;width:100%; border:1px solid" cellpadding="3" cellspacing="3">
				<thead>
				<tr>
				<th style="width:10%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
				<th style="width:60%;text-align:center;border:1px solid;">Item Description</th>
				<th style="width:15%;text-align:center;border:1px solid;">Godown</th>
				<th style="width:15%;text-align:center;border:1px solid;">Unit Price</th>
				<th style="width:15%;text-align:center;border:1px solid;">Qty</th>
				</thead>
				<tbody> ';
		/*$qry="select  mtr.*,sum(mtr.base_qty) as base_qty, ap.previous_process_id, p.product_name, p.product_icode, u.unit_name from tbl_material_release_trn as mtr 
				left join product_mst as p on p.product_id=mtr.product_id 
				left join unit_mst as u on u.unitid=mtr.base_unit 
				left join tbl_allocate_process as ap on ap.p_id=mtr.p_id 
				where status = 0 and mtr.p_id = " . $p_id . " group by mtr.Product_id";*/
		$qry="select  mtr.*,sum(mtr.base_qty) as base_qty, ap.previous_process_id, p.product_name, p.product_icode, u.unit_name from tbl_material_release_trn as mtr 
	left join tbl_material_release as m on m.material_id=mtr.material_id 
	left join product_mst as p on p.product_id=mtr.product_id 
	left join unit_mst as u on u.unitid=mtr.base_unit 
	left join tbl_allocate_process as ap on ap.p_id=mtr.p_id 
	where status = 0 and  m.release_qty = ".$issue_rw['release_qty']." and mtr.p_id = " . $p_id . " group by mtr.product_id";
		$result=$dbcon->query($qry);
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;$pcount=1;$total_cs_gst=0;$total_i_gst=0;$gst_per=0;$gst_rate=0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			$godown = "";

			if($row['previous_process_id'] == '0'){
				$rs_qry = "SELECT group_concat(godown_id) as godown_id FROM tbl_reserve_stock where stock_flage = 1 and stock_status != 2 and product_id = " . $row['product_id'] . " and p_id = " . $row['p_id'];
			}else{
				$rs_qry = "SELECT group_concat(godown_id) as godown_id FROM tbl_process_reserve_stock where stock_flage = 1 and stock_status != 2 and product_id = " . $row['product_id'] . " and p_id = " . $row['p_id'];	
			}

			$rs_res = $dbcon->query($rs_qry);
			if(brp_mysqli_num_rows($rs_res) > 0){
				$rs_rw = brp_mysqli_fetch_assoc($rs_res);

				$gd_qry = "SELECT group_concat(gd_name) as gd_name FROM mst_godown WHERE gd_id in(".$rs_rw['godown_id'].")";
				$gd_res = $dbcon->query($gd_qry);
				
				$gd_row = brp_mysqli_fetch_assoc($gd_res);
				if(!empty($gd_row['gd_name'])){
					$godown = $gd_row['gd_name'];
				}
			}
			
			
			$html.='<tr>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">'.$i.'</td>
			<td style="text-align:left;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			<strong>'.$row['product_name'].'</strong><br>
			<strong>Product Code : </strong>'.$row['product_icode'];
			$html .='
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.$godown.'
			</td>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">
			'.$row['unit_name'].'
			</td>
			<td style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid">'.number_format($row['base_qty'],4,".","").'</td>';
			
			$html.='</tr>';


			$totalqty=$totalqty+$row['base_qty'];
			$i++; 
		}

		$html .= '</table></div><div>';
		////////////////////////////////////////////////////////////////////Tax Calculation Start - Harshil//////////////////////////////////////////////////
		
		$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
			<tbody>
			<tr>
			<td colspan="3" style="text-align:right;border:1px solid; font-weight: bold;">Total Qty</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;width:13%">'.number_format($totalqty,4,".","").'</td>
		</tr>
		</tbody></table>
		';
				
		$html.='<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<tr style="">
			<td  style="height:80px; width:33.33%;  text-align:center; vertical-align:bottom; border-left:1px solid; border-bottom:1px solid !important;border-top:1px solid; font-weight: bold;"><strong>Issued By </strong><br> </td>
			<td  style="height:80px; width:33.33%;  text-align:center; vertical-align:bottom; border-bottom:1px solid !important;border-top:1px solid; font-weight: bold;"><strong> Received By</strong><br> </td>
			<td style="text-align:center;vertical-align:bottom;font-weight: bold;height:80px; width:33.33%;border-top:1px solid;border-bottom:1px solid;border-right:1px solid">Authorised By
			</td>
		</tr>
		


				<!--page1 end-->';
				$html.='</table>
				</div>
				<div style="clear:both;"></div>
				</div>';

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		// echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','35','10','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
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
		return 'material_release_print'.$store_release_id.'.pdf';
	}

?>