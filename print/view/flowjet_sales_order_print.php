<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	SALES_ORDER_SLUG_PRINT
]);

if(!in_array(SALES_ORDER_SLUG_PRINT,$bulkAccessArray)){
	header("Location: ".DOMAIN."permission_access");
}

 $sales_order_id = $_REQUEST['id'];	

$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
	 $query="select invoice.*,country.country_name,cuser.user_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust_pincode,cust_mobile,gst_no from tbl_sales_order as invoice 
	left join tbl_ledger as cust on cust.l_id=invoice.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join users as cuser on cuser.user_id=invoice.user_id
	where sales_order_id=".$sales_order_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	$company_name=$rel['company_name'];
	$cust_address=$rel['cust_address'];
	$city_name=$rel['city_name'];
	$state_name=$rel['state_name'];
	$country_name=$rel['country_name'];
	$cust_pincode=$rel['cust_pincode'];
	$gst_no=$rel['gst_no'];
	$delivery_type = $rel['delivery_type'];

	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."sales_order_list");
	}
	$po_date = '';
	if($rel['po_date']!="1970-01-01 00:00:00" && $rel['po_date']!="0000-00-00 00:00:00")
	{
		$po_date=date('d-m-Y',strtotime($rel['po_date']));
	}

	$so_date = '';
	if($rel['sales_order_date']!="1970-01-01 00:00:00" && $rel['sales_order_date']!="0000-00-00 00:00:00")
	{
		$so_date=date('d-m-Y',strtotime($rel['sales_order_date']));
	}
	$delivery_date = '';
	if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00")
	{
		$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
	}

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$comp_rel=mysqli_fetch_assoc($dbcon->query($set));

	$crm_set = "select * from tbl_company_settings where company_id=".$rel['company_id']." and user_id=".$_SESSION['user_id'];
	$crm_rel = mysqli_fetch_assoc($dbcon->query($crm_set));

	$user = "select * from users where user_id=".$_SESSION['user_id'];
	$user_rel = mysqli_fetch_assoc($dbcon->query($user));

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$sales_pro_print=explode(",", $companyConfiguration['sales_pro_print']);

	$user_app = "SELECT approve.*, user.user_name from tbl_quot_po_aprv_log as approve LEFT JOIN users as user ON user.user_id = approve.user_id where approve.approve_status = 1 AND approve.sales_order_id=".$sales_order_id." ORDER BY approve.quot_aprv_log_id DESC LIMIT 1";
	$user_rels = mysqli_fetch_assoc($dbcon->query($user_app));

	$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
	$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

	$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
	$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
	$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));

//	$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;padding-top:25px;" />';
	$header ='<table style="font-size:12px;width:100%;" cellpadding="5" cellspacing="5">
	<tr>
	<td colspan="4" style="text-align:center;font-size:15px;font-weight:bold;border-bottom:1px solid"> 
	SALES ORDER INTIMATION '.$approve_status.'
	</td>
	</tr>
	<!--<tr>
	<td style="text-align:left; font-weight: bold;">Customer</td>
	<td style="text-align:left; font-weight: bold;">: '.$company_name.'</td>
	<td style="text-align:left; font-weight: bold;">City</td>
	<td style="text-align:left; font-weight: bold;">: '.$city_name.'</td>
	</tr>-->
	<tr>
	<td style="text-align:left;">S.O Number</td>
	<td style="text-align:left;">: '.$rel['sales_order_no'].'</td>
	<td style="text-align:left;">S.O Date</td>
	<td style="text-align:left;">: '.$so_date.'</td>
	</tr>
	<tr>
	<td style="text-align:left;">P.O Number</td>
	<td style="text-align:left;">: '.$rel['po_no'].'</td>
	<td style="text-align:left;">P.O Date</td>
	<td style="text-align:left;">: '.$po_date.'</td>
	</tr>
	<tr>
	<td style="text-align:left;">Project</td>
	<td style="text-align:left;" colspan="3">: '.$rel['order_by'].'</td>
	</tr>
	</table>';  
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}

	$html ='<html>
	<head>					
	<title>SALES ORDER - '.$rel['sales_order_no'].'</title>
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
			font-size:14px;
			/*border:1px solid #000 !important;*/
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}

		</style>
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
		<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table style="font-size:14px;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<th style="width:2%;text-align:center;border-top:1px solid;border-bottom:1px solid">No.</th>
		<th style="width:10%;text-align:left;border-top:1px solid;border-bottom:1px solid">Item Code</th>
		<th style="width:50%;text-align:left;border-top:1px solid;border-bottom:1px solid">Item Details</th>
		<th style="width:10%;text-align:center;border-top:1px solid;border-bottom:1px solid">UOM</th>
		<th style="width:10%;text-align:center;border-top:1px solid;border-bottom:1px solid">Qty</th>
		</tr>
		</thead>
		<tbody>';
		  $trn_qry="select trn.*,product.product_name, product.product_icode, product.product_alias_name ,per.unit_name,drg.drawing_number FROM `tbl_sales_ordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id
		left join tbl_drawing as drg on drg.drawing_id = product.drawing_id
		left join unit_mst as per on per.unitid=trn.unit_id 
		where trn.sales_ordertrn_status=0 and trn.sales_order_id=".$sales_order_id;
		$trn_qry_rs=$dbcon->query($trn_qry);
		$p=1;$ttl_amt=0;$ttl_qty=0;
		$cnt=mysqli_num_rows($trn_qry_rs);

		while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
			$alias_name = '';
			if(in_array('alias',$sales_pro_print)){
				$alias_name = " -- (".$trn_rel['product_alias_name'].")";
			}
			$html.='<tr style="border:none;">
			<td style="width:2%;text-align:center;vertical-align:top;">'.$p.'</td>
			<td style="width:10%;text-align:left;vertical-align:top;">
			'.$trn_rel['product_icode'].'<br>Cust. Desc
			</td>
			<td style="width:50%;text-align:left; vertical-align: top;"><strong>'.$trn_rel['product_name'].'</strong>'.$alias_name.'<br>'.nl2br($trn_rel['description']).'<br><br>';
			
						if($delivery_type == 'product_wise'){
				$retu_date = "select sdate.*,unit.unit_name from tbl_salesorder_delivery_date as sdate left join unit_mst as unit on unit.unitid=sdate.unit_id where invoice_status=0 and sdate.po_delivery_date_status=0 and sales_ordertrn_id=".$trn_rel['sales_ordertrn_id'];
				$resadate=$dbcon->query($retu_date);
			
				while($rowdate=brp_mysqli_fetch_array($resadate)){	
				    
				    
					$html .='
						<br><br>Drawing No. :'.$trn_rel['drawing_number'].'
				       <br>Delivery Schedule :'.date('d-m-Y',strtotime($rowdate['delivery_date'])).'</td>';		
				
				}
				
			}
			else
			{
				$html .='<br><br>Drawing No. :'.$trn_rel['drawing_number'].'
				<br>Delivery Schedule :'.date("d/m/Y", strtotime($rel['delivery_date'])).'</td>';
			}
			
			
			
			$html .='<td style="width:10%;text-align:center;vertical-align:top;">'.$trn_rel['unit_name'].'</td>
			<td style="width:10%;text-align:center;vertical-align:top;">
			'.$trn_rel['product_qty'].'
			</td>
			</tr>';

			$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
			if($trn_rel['act_amt_flag']!='1'){
				$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
			}

			$p++;
		}
		if($delivery_type == 'product_wise'){
			$html.='<tr style="border: none; border-top: 1px solid black; border-bottom: 1px solid black;">
			<td colspan="4" style="text-align:right; font-weight: bold;">Total: </td>
			<td style="text-align:center; font-weight: bold;">
			'.$ttl_qty.'
			</td>
			</tr>
			<tr style="border: none; border-bottom: 1px solid black;">
			<td colspan="5" style="text-align:left; font-weight: bold;"><strong>Note :</strong> '.$rel['remark'].'</td>
			</tr>
			</tbody></table>';
		} else{
			$html.='<tr style="border: none; border-top: 1px solid black;">
			<td colspan="4" style="text-align:right; font-weight: bold;">Total: </td>
			<td style="text-align:center; font-weight: bold;">
			'.$ttl_qty.'
			</td>
			</tr>
			<tr style="border: none; border-bottom: 1px solid black;">
			<td colspan="5" style="text-align:left; font-weight: bold;"><strong>Note :</strong> '.$rel['remark'].'</td>
			</tr>
			</tbody></table>';
		}
		$terms_qry="select qtrm.*,mst.tc_name from tbl_salesorder_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.sales_order_id=".$rel['sales_order_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)){
			$html.='<h4 style="text-align:left;">Terms & Conditions: </h4>
			<div><table width="100%"><tbody>';
			$t=1;
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));

				$html.='<tr>
				<td width="5%" style="text-align:center; vertical-align: top;">'.$t.'</td>
				<td width="20%" style="text-align:left; vertical-align: top;"><strong>'.$term_rel['tc_name'].'</strong></td>
				<td width="75%" style="text-align:left;"> : '.$string.'</td>
				</tr>';
				$t++;
			}
			$html.='</tbody></table></div>';	
		}
		$html .='<div>
		<table width="100%" style="border-collapse: collapse;width:100%;overflow:wrap;">
		<tbody>
		<tr>
		<td style="width: 33%; text-align:left;padding:5px;">Issued By : '.$rel['user_name'].'</td>
		<td style="width: 34%; text-align:left;padding:5px;">Authorised By : '.(($user_rels['user_name']) ? $user_rels['user_name'] : '').'</td>
		<td style="width: 33%; text-align:left;padding:5px;">Received By : _________________</td>
		</tr>
		<tr>
		<td style="text-align:left;padding:5px;">Date : '.date("d-m-Y",strtotime($rel['cdate'])).'</td>
		<td style="text-align:left;padding:5px;">Date : '.(($user_rels['cdate']) ? date("d-m-Y",strtotime($user_rels['cdate'])) : '').'</td>
		<td style="text-align:left;padding:5px;">Date : _________________</td>
		</tr>
		</tbody>
		</table>
		</div>';
		/* Get Terms And Condition Start */
		/* Check Annexure Attachments End */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
	//echo $html;exit;
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
		$mpdf->SetHTMLFooter($footer);
		$mpdf->pagenumPrefix = 'Page ';
		$mpdf->pagenumSuffix = ' of ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = '';
		$mpdf->SetFooter('{PAGENO}{nbpg}');
		if($rel['approve_status'] == '0'){
		$mpdf->SetWatermarkText('NOT APPROVED');	
	}else{
		$mpdf->SetWatermarkText();	
	}
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'Sales_Order_'.$quotation_id.'.pdf';
	}

?>