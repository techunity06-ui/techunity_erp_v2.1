<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [QUOTATION_SLUG_PRINT]);

if (!in_array(QUOTATION_SLUG_PRINT, $bulkAccessArray))
{
	header("Location: " . DOMAIN . "permission_access");
}

$quotation_id = $_REQUEST['id'];
$type = 'pdf';
if (strtolower($type) == 'pdf')
{
    //Quotation Data
	$query = "select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date,currency.currency_symbol,inq.project_name from tbl_quotation as quot
	left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
	left join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
	left join tbl_currency as currency on currency.currency_id=quot.currency_id
	where quot.quotation_id=" . $quotation_id;
	$rel = mysqli_fetch_assoc($dbcon->query($query));

	if (!$rel)
	{
		header("Location: " . ROOT . CRM_ROOT . "quotation_list");
	}
    //Company Data
    /*$comp_qry="select * from tbl_company as comp
    where comp.company_id=".$rel['company_id'];
    */
    $set = "select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=" . $rel['company_id'];

    $comp_rel = mysqli_fetch_assoc($dbcon->query($set));
    //$header=$set;
    $header = '<img src="' . DOMAIN_F . LOGO . $comp_rel["logo"] . '" style="width:8.27in" />';
    //$header ='<img src="'.DOMAIN_F.LOGO.'elcon.png" style="width:3.27in;padding-top:25px;" />';
    //$header =$comp_rel["logo"];
    //$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';
    $approve_status = '';
    if ($rel['approve_status'] == '0')
    {
    	$approve_status = ' (DRAFT)';
    }
    if($rel['quot_type']=='0'){
    	$currency_name = '(INR)';
    	$currency_word_start = 'Rupees';
    	$currency_word_end = 'Paise';
    }else{
    	$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
    	$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

    	$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
    	$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
    	$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
    }

    $html = '<html>
    <head>					
    <title>Quotation - ' . $rel['quotation_no'] . '</title>
    <style type="text/css">
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

    </style>
    </head>
    <body>

    <htmlpageheader name="otherpages" style="display:none">
    <div style="text-align:center">' . $header . '</div>
    </htmlpageheader>
    <!--<htmlpagefooter name="otherpages_footer" style="display:none">
    <div style="text-align:center">' . $footer . '</div>
    </htmlpagefooter>-->
    <sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
    <div>
    <table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
    <tr>
    <td colspan="8" style="text-align:center;font-size:15px;font-weight:bold;"> 
    Quotation' . $approve_status . '
    </td>
    </tr>
    <tr>
    <td colspan="3"  rowspan="" style="text-align:left;vertical-align:top;border:1px solid;width:40%;"> 
    To,<br/>
    <strong>' . $rel['cust_name'] . '</strong><br/>
    ' . (($rel['quot_address']) ? nl2br($rel['quot_address']) : '') . ' <br><br> <br>
    Subject :' . $rel["quot_subject"] . ' <br>
    Project :' . (($rel["project_name"] != '' && $rel["project_name"] != '0') ? $rel["project_name"] : "") . '
    </td>
    <td colspan="2" style="text-align:left;border:0px solid;width:20%;"> 
    Quotation No :<br>
    Quotation Date :<br>
    Ref No :<br>
    Validity :<br>
    Enquiry No :
    </td>
    <td colspan="3" style="text-align:left;border:0px solid;width:30%;"> 
    <strong>' . $rel['quotation_no'] . '</strong><br>
    <strong> ' . date("d-M-Y", strtotime($rel['quotation_date'])) . ' </strong> <br>
    <strong> ' . $rel['quotation_ref'] . ' </strong><br>
    <strong> '.date("d-M-Y",strtotime($rel['quotation_valid_date'])).'</strong><br>
    <strong> ' . $rel['inquiry_no'] . '</strong>
    </td>	
    </tr>
    <tr>
    <td colspan="8">   <u>Kind Attn.</u>' . $rel['c_con_fname'] . ' ' . $rel['c_con_lname'] . ' (' . $rel['c_con_mobile'] . ')<br>
    ' . (($rel['quatation_greeting']) ? nl2br($rel['quatation_greeting']) : '') . '
    </td>
    </tr>
    <tr>
    <th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
    <th style="width:6%;text-align:center;border:1px solid;">Item Code</th>
    <th style="width:30%;text-align:center;border:1px solid;">Item Description</th>
    <th style="width:5%;text-align:center;border:1px solid;">HSN Code</th>
    <th style="width:8%;text-align:center;border:1px solid;">Qty</th>
    <th style="width:5%;text-align:center;border:1px solid;">Unit</th>
    <th style="width:5%;text-align:center;border:1px solid;">RATE (' . $currency_name . ')</th>
    <!--<th style="width:10%;text-align:center;border:1px solid;">Disc.%</th>-->
    <th style="width:15%;text-align:center;border:1px solid;">Total (' . $currency_name . ')</th>
    </tr>';
    $trn_qry = "select trn.*,pro.product_name,hsn.hsn_code as product_hsn_code ,pro.product_icode ,unit.unit_name from tbl_quotation_trn as trn  left join product_mst as pro on pro.product_id=trn.product_id left join unit_mst as unit on unit.unitid=trn.unitid left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn where trn.quot_trn_status=0 and trn.quotation_id=" . $rel['quotation_id'];
    $trn_qry_rs = $dbcon->query($trn_qry);
    $p = 1;
    $ttl_amt = 0;
    $ttl_qty = 0;
    $disc_total = 0;
    $total_gst=0;$total_i_gst=0;
    $cnt = mysqli_num_rows($trn_qry_rs);
    while ($trn_rel = mysqli_fetch_assoc($trn_qry_rs))
    {
    	$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
    	$gst_rate = $trn_rel['cgst_tax_rate']+$trn_rel['sgst_tax_rate']+$trn_rel['igst_tax_rate'];

    	if($trn_rel['cgst_tax_rate'] != 0 || $trn_rel['sgst_tax_rate'] !=0){
    		$total_cs_gst += $gst_rate;
    	}else{
    		$total_i_gst += $gst_rate;
    	}
		//tax summary calculation start
    	if(!empty($trn_rel['tax_val']))
    	{
    		$tax_num=explode(",",$trn_rel['tax_val']);
    		$tax_name=explode(",",$trn_rel['tax_name']);
    		$total_net_rate=($trn_rel['product_qty']*$trn_rel['product_rate'])-$trn_rel['discount'];
    		for($j=0;$j<count($tax_num);$j++)
    		{
    			if(!in_array($tax_name[$j],$tax['per']))
    			{
    				$tax['per'][]=$tax_name[$j];
    			}
    			$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
    		}
    	}

    	$disc_total = $disc_total + $trn_rel['product_discount'];
    	$product_desc = '';
    	if ($trn_rel['product_desc'] != '' && $trn_rel['product_desc'] != '0')
    	{
    		$product_desc = nl2br($trn_rel['product_desc']);
    	}
    	$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
    	<td style="text-align:center;border:1px solid;vertical-align:top;">' . $p . '</td>
    	<td style="text-align:center;border:1px solid;vertical-align:top;">
    	<b>' . nl2br($trn_rel['product_icode']) . '</b>
    	</td>
    	<td style="text-align:left;border:1px solid;vertical-align:top;">
    	' . $trn_rel['product_name'] . '<br/>
    	' . $product_desc . '
    	</td>
    	<td style="text-align:center;border:1px solid;vertical-align:top;">
    	' . $trn_rel['product_hsn_code'] . '
    	</td>
    	<td style="text-align:center;border:1px solid;vertical-align:top;">' . indian_number($trn_rel['product_qty'], 2) . '</td>
    	<td style="text-align:center;border:1px solid;vertical-align:top;">' . $trn_rel['unit_name'] . '</td>
    	<td style="text-align:center;border:1px solid;vertical-align:top;">';
    	if ($trn_rel['act_amt_flag'] == '1')
    	{
    		$html .= "Extra At Actual";
    	}
    	else
    	{
    		$html .= indian_number($trn_rel['product_rate'], 2);;
    	}

    	$product_total = $trn_rel['product_qty'] * $trn_rel['product_rate'];
    	$html .= '</td>
    	<!--<td style="text-align:center;border:1px solid;vertical-align:top;">' . ($trn_rel['product_discount'] ? $trn_rel['product_discount'] : '0.00') . '</td>-->
    	<td style="text-align:center;border:1px solid;vertical-align:top;">';
    	if ($trn_rel['act_amt_flag'] == '1')
    	{
    		$html .= "Extra At Actual";
    	}
    	else
    	{
    		$html .= indian_number($product_total, 2);
    	}

    	$html .= '</td>
    	</tr>';
    	$ttl_qty = $ttl_qty + $trn_rel['product_qty'];
    	if ($trn_rel['act_amt_flag'] != '1')
    	{
    		$ttl_amt = $ttl_amt + $product_total;
    	}

    	$p++;
    }
    $pr = 5 - $cnt;
    for ($j = 0;$j < $pr;$j++)
    {
    	$html .= '<tr style="border:none;border-left:1px solid;border-right:1px solid;">
    	<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
    	<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
    	<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
    	<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
    	<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
    	<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
    	<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
    	<!--<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>-->
    	<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
    	</tr>';
    }

    $html .= '<tr>

    <td colspan="4" style="text-decoration:underline;">REMARKS:
    ' . (($rel['quot_remark']) ? nl2br($rel['quot_remark']) : '') . '


    </td>
    <td  colspan="3" style="text-align:left;border:1px solid;">RUNNING TOTAL :</td>
    <td style="text-align:right;">' . indian_number($ttl_amt, 2) . '</td>
    </tr>';

    $html .= '<tr>

    <td rowspan="3" colspan="4" height="90px">
    PAN NO : ' . $comp_rel['pan_no'] . ' <br>
    GST NO : ' . $comp_rel['vatno'] . '<br>
    AN ISO 9001:2015  - 27341 COMPANY<br>
    UIN NO. : U31101PN2012PTC142385<br>
    MSME NO : 270251203944<br>
    UDYAM NO : 26 - 0011381

    </td>';

    if ($disc_total > 0)
    {
    	$html .= '
    	<td  colspan="3">DISCOUNT :</td>
    	<td style="text-align:right;">' . indian_number($disc_total, 2) . '</td>
    	';
    }
    $html .= '<tr><td  colspan="3" height="90px"> TAXABLE VALUE <br>';
    if($rel['qt_add_state']==$comp_rel['stateid']){
    	$html .= 'CGST ('.($gst_per/2).' %)<br>SGST ('.($gst_per/2).' %)';
    }else{
    	$html .= 'IGST ('.($gst_per).' %)';
    }
    $html .= '</td>
    <td style="text-align:right;" height="90px"> <br>';
    if($rel['qt_add_state']==$comp_rel['stateid']){
    	$html .= number_format(($total_cs_gst/2),2,".","").'<br>'.number_format(($total_cs_gst/2),2,".","");
    }else{
    	$html .= number_format(($total_i_gst),2,".","");
    }
    
    $html .= '</td></tr>';

    $html .= '<tr>
    <td  colspan="3">FINAL AMOUNT (' . $currency_name . ') :</td>
    <td style="text-align:right;">' . indian_number($rel['g_total'], 2) . '</td>
    </tr>';

    $html .= '<tr>
    <td colspan="4"  style="padding:0px"><table style="width:100%"><tr style="border:none">
    <td colspan="2" style="height:100px;text-align:top;" width="75%">
    <strong>Bank Details</strong><br>
    Bank Name : ' . $comp_rel['bank_name'] . ' <br>
    A/C No :' . $comp_rel['ac_no'] . '<br>
    Branch Name : ' . $comp_rel['branch_name'] . '<br>
    IFSC : ' . $comp_rel['ifcs'] . '<br></td>
    <td colspan="2" style="height:100px;text-align:top;" width="50%">
    <strong>Bank Details</strong><br>
    Bank Name : THE SARASWAT CO-OP BANK <br>
    A/C No :283500100000119<br>
    Branch Name : SME-283, Sangamwadi<br>
    IFSC : SRCB0000283<br></td> </tr></table>
    </td>                      
    <td colspan="4" style="text-align:center"> For ELCON CABLE TRAYS PVT. LTD.<br>
    <img src="' . DOMAIN_F . LOGO . 'sign.png" height="80" width="80"/>
    </td>
    </tr>';
    $user_qry = "select * from users where user_id=" . $rel['user_id'];
    $user = mysqli_fetch_assoc($dbcon->query($user_qry));
    $html .= '<tr>
    <td  colspan="4"> For any information contact our Sales Executive ' . $user['user_name'] . ' - ' . $user['user_phone'] . '</td>
    <td colspan="4"> Authorised Signatory <br> Approved By :</td>
    </tr>';
    $html .= '</table>
    <!--page1 end-->';
    $terms_qry = "select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
    left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
    where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=" . $rel['quotation_id'] . " order by qtrm.tc_priority";
    $terms_qry_rs = $dbcon->query($terms_qry);
    if (mysqli_num_rows($terms_qry_rs))
    {
    	$html .= '<center class="nextpage"></center>
    	<h3 style="text-align:center;">Terms and Conditions</h3>
    	<div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
    	$t = 1;
    	while ($term_rel = mysqli_fetch_assoc($terms_qry_rs))
    	{
    		$string = (nl2br($term_rel['tc_details']));

    		$html .= '<tr>
    		<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">' . $t . '</td>
    		<td width="25%" style="width:25%;text-align:center;border:1px solid;padding:5px;">' . $term_rel['tc_name'] . '</td>
    		<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">' . $string . '</td>
    		</tr>';
    		$t++;
    	}
    	$html .= '</tbody></table></div>';
    }
    /* Get Terms And Condition Start */

    /* Check Annexure Attachments Start */
    if (trim($rel['quot_annex_content']))
    {
    	$html .= '<center class="nextpage"></center>';
    	$html .= '<div class="quot_annex_content_div">' . $rel['quot_annex_content'];
    	$html .= '</div>';
    }
    /* Check Annexure Attachments End */

    $html .= '<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
    </body>
    </html>';
    //echo $html;exit;
    ob_end_clean();
    include ("../../view/export/mpdf/mpdf.php");
    $mpdf = new mPDF('', 'A4', '0', 'calibri', '10', '10', '40', '3.7', '1', '');
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
    $mpdf->allow_charset_conversion = true;
    $mpdf->charset_in = 'UTF-8';
    $mpdf->WriteHTML($html);
    $mpdf->Output();
    //$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
    ob_clean();
    return 'quotation' . $quotation_id . '.pdf';
}

?>
