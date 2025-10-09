<?php 

$quotation_id = $_REQUEST['id'];	

if(!empty($quotation_id)){
	session_start();
	include("../../config/config.php");
	include("../../config/session.php");
	include("../../include/function_database_query.php");
	include_once(COMMON_FUNCTION_PATH."common_functions.php");
	$incPath = $path.'include/';

	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_SLUG_PRINT,
	]);

	if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
		header("Location: ".DOMAIN."permission_access");
	}
	quotation_print($dbcon,$quotation_id,$save_file = "No");
}
function quotation_print($dbcon,$quotation_id,$save_file){
    
    $type = 'pdf';
if(strtolower($type) == 'pdf') {

//Quotation Data
	$query="select quot.*,per.c_con_fname,per.c_con_lname,inq.inquiry_id,per.c_con_mobile,qtrn.product_size,qtrn.product_gsm,qtrn.product_printing,qtrn.product_handling,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_quotation_trn as qtrn on qtrn.quotation_id=quot.quotation_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		where quot.quotation_id=".$quotation_id;
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	if(!$rel){
		header("Location: ".ROOT.CRM_ROOT."quotation_list");
	}

	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$rel['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mobile : '.$userData['user_phone'] : '';
    $userEmail = $userData['user_mail'] ? 'Email Id : '.$userData['user_mail'] : '';
$product_amount_field = "product_amount";
$product_rate_field = "'product_rate'";
		if($rel['quot_type']=='0'){
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
			$currency_name = '(INR)';
			$currency_word_start = 'Rupees';
			$currency_word_end = 'Paise';
			$currency_symbol = $currency_rel['currency_symbol'];
		}else{
			$currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
			$currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));

			$currency_name = '('.(strtoupper($currency_rel['currency_code'])).')';
			$currency_word_start = (strtoupper($currency_rel['currency_in_word']));
			$currency_word_end = (strtoupper($currency_rel['currency_in_word_end']));
			$currency_symbol = $currency_rel['currency_symbol'];
			
			if($_SESSION["currency_id"] == $rel['currency_id']){
			    $product_amount_field = "product_amount_conv";
			    $product_rate_field = "'product_rate_conv'";
			}
		}
		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		$header ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;border: 1px white solid" /></div>';
		$footer ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;border: 1px white solid" /></div>';
		$approve_status='';
		if($rel['approve_status']=='0'){
			$approve_status=' (DRAFT)';
		}
		$inquiry_type=$rel['inquiry_type'];
//Amish Soni Start 16-03-2021
		$companySettings = getCompanySettings($dbcon);
		$companyConfiguration=getCompanyConfiguration($dbcon);
		$sales_pro_search=explode(",", $companyConfiguration['sales_pro_search']);

	//	if($companySettings) {
			$quotation_print_content = $rel['quot_header'] ? $rel['quot_header'] : '';
			$quotation_print_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_print_content);
			$quotation_footer_content = $rel['quot_footer'] ? $rel['quot_footer'] : $quotation_footer_content;
			$quotation_footer_content = str_ireplace(array("\r","\n",'\r','\n'),'', $quotation_footer_content);
	//	}
		$disc_qry=$dbcon->query("SELECT SUM(trn.product_discount_conv) as discount from tbl_quotation_trn as trn where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']);
		$disc_qrys = brp_mysqli_fetch_assoc($disc_qry);
		if($companyConfiguration['quot_revise_time_rate_with_discount'] == 0){
			$colspan =($disc_qrys['discount'] > 0) ? 7 : 6;	
		}else{
			$colspan = 6;
		}

		$colspan = 10;
$html ='<html>
<head>					
	<title>Quotation - ' . $rel['quotation_no'] . '</title>
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
		border:1px solid black !important;
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
<htmlpageheader name="otherpages" style="display:none">
<div style="text-align:center">' . $header . '</div>
</htmlpageheader>
<htmlpagefooter name="otherpages_footer" style="display:none">
<div style="text-align:center">' . $footer . '</div>
</htmlpagefooter>
<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
	<div>
	<h3 style="text-align:center";>QUOTATION</h3>
		<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px;  border: 0;">
			<tr style="border: 0;">
				<td style="border: 0;">
				<p style="float: left; width: 100%;"><strong>To,<br/>
				' . $rel['cust_name'] . '<br/>
				Add : ' . $quot_address . '<br/>
				GST : ' . $rel['cust_gst'] . '<br/>
				Mobile : ' . $rel['cust_mobile'] . '<br/></strong>
					
				</p>
				<br />
					<br />
				
				</td>
				<td style="border: 0;text-align:right;"><br>
					<p style="float: right; width: 100%;"><b>Date </b> : '.date("d-M-Y",strtotime($rel['quotation_date'])).'<br/><b>Quo. No  </b>: '.$rel["quotation_no"].'
						
					</p>
				
				</td>
			</tr>
			<tr style="border: 0;">
				<td colspan="2" style="border: 0;">
			
				</td>
			</tr>
		</table>
		<table cellpadding="5" cellspacing="5" border="0" style="font-size: 14px;  border: 0;margin-top: 20px;">
			<tr style="border: 0;background-color:rgb(247, 199, 207);">
				<td style="border: 0;">
				Subject : '.$rel['quot_subject'].'
				</td>
			</tr>
		</table>';
	
	if($inquiry_type!="2"){
					$trn_qry="SELECT trn.*,pro.product_name,pro.product_desc,unit.unit_name, pro.product_icode,hsn.hsn_code FROM tbl_quotation_trn as trn 
				   left join product_mst as pro on pro.product_id=trn.product_id
				   left join unit_mst as unit on unit.unitid=trn.unitid
				   left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
				   where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			   } else {
					 $trn_qry="SELECT trn.* , pro.product_name,pro.product_icode FROM `tbl_quotation_project_trn` as trn 
					 left join product_mst as pro on pro.product_id = trn.product_id 
				   where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id'];
			   }
	$trn_qry_rs=$dbcon->query($trn_qry);
	$p=1;$ttl_amt=0;$ttl_qty=0;$pcount=1;
	$cnt=mysqli_num_rows($trn_qry_rs);
	while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
		$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
				$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];

				if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
					$total_cs_gst += $gst_rate;
				}else{
					$total_i_gst += $gst_rate;
				}
		//tax summary calculation start
				if(!empty($trn_rel['tax_val']))
				{
					$tax_num=explode(",",$trn_rel['tax_val']);
					$tax_name=explode(",",$trn_rel['tax_name']);
					$total_net_rate=($trn_rel['product_qty']*$trn_rel['product_rate_conv'])-$trn_rel['product_discount_conv'];
					for($j=0;$j<count($tax_num);$j++)
					{
						if(!in_array($tax_name[$j],$tax['per']))
						{
							$tax['per'][]=$tax_name[$j];
						}
						$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
					}
				}
				$prod_qty = 0;
				$prod_rate = 0;
					if($_SESSION["currency_id"] == $rel['currency_id']){
					    $prod_qty =  $trn_rel['product_qty'];
					      $prod_rate  = $trn_rel['product_rate'];
        			   
					}else{
					    $prod_qty = $trn_rel['product_conv_qty'];
					      $prod_rate  = $trn_rel['product_rate_conv'];
					   
					}
					
					 $amt = $prod_qty * $prod_rate;
					  $ttl_qty = $ttl_qty + $prod_qty;
			   	$ttl_amt = $ttl_amt + 	$amt;
		if($pcount=="1")
		{
				$html.='<div style="clear:both;"></div>
				<div>
				<table  style="font-size: 12px; font-family: calibri;border-collapse: collapse;width:100% !important;table-layout:fix;background-color:rgb(208, 208, 208);" >
					             <tr >
		                    <td style="border: 1px solid black; width: 40px; text-align: center;width:8%;"><b>SR No. </b></td>
		                    
		                    
		                   
		                       <td style="border: 1px solid black; text-align: center;width:36%;" ><b>PRODUCT DESCRIPTION</b>
		                    </td>
							<td style="border: 1px solid black; text-align:center;width:10%; "><b>SIZE</td>
		                         <td style="border: 1px solid black;text-align: center;width:10%;"><b>GSM</b></td>
		                    <td style="border: 1px solid black;text-align: center;width:8%;"><b>PRINTING</b></td>
		                    <td style="border: 1px solid black;text-align: center;width:8%;"><b>HANDLING</b></td>
		                    <td style="border: 1px solid black;text-align: center;width:8%;"><b>QUANTITY</b></td>
		                    <td style="border: 1px solid black;text-align: center;width:10%;"><b>RATE <br>'.$currency_name.'</b></td>
		                   
		                    <td style="border: 1px solid black;text-align: center;width:10%;"><b>TOTAL <br>'.$currency_name.'</b></td>
				</tr>';
		}
		$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
		$html.='<tr>
                   <td style="border: 1px solid black;  text-align: center;border-top: none; border-bottom: none;vertical-align:top;"><b>'.$p.'</b></td>
                  
                   
                   <td style="border: 1px solid black; text-align:left; padding-left: 5px; border-top: none; border-bottom: none;vertical-align:top;" ><b>'.$trn_rel['product_name'].'';
				   if (!empty($product_desc)){
				   $html .='<br>Desc:</b>'.$product_desc.'';
					}		  
		
				  $html.='
                   </td>
				    
                   <td style="border: 1px solid black;border-top: none; ; border-bottom: none;vertical-align:top;" ><b> '.$rel['product_size'].'</b></td>
                   <td style="border: 1px solid black;border-top: none; padding-left: 5px;  border-bottom: none;vertical-align:top;" ><b>'.$rel['product_gsm'].'</b></td>
                   <td style="border: 1px solid black;border-top: none; padding-left: 5px; border-bottom: none;vertical-align:top;"><b> '.$rel['product_printing'].'</b></td>
                   <td style="border: 1px solid black;border-top: none; padding-left: 5px; border-bottom: none;vertical-align:top;"><b> '.$rel['product_handling'].'</b></td>
                   <td style="border: 1px solid black;border-top: none; padding-left: 5px; border-bottom: none;vertical-align:top;text-align:right"><b>'.number_format((float)$prod_qty, 2, '.', '').'</b></td>
                   <td style="border: 1px solid black;border-top: none; padding-left: 5px; border-bottom: none;text-align:right;vertical-align:top;"><b> '.$currency_symbol.' '.number_format($prod_rate,2,".","").'</b></td>
                   <td style="border: 1px solid black;border-top: none; padding-left: 5px; border-bottom: none;text-align:right;vertical-align:top;"><b>'.$currency_symbol.' '.	number_format($amt,2,".","").'</b></td>
		</tr>';
		/*$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
		if($trn_rel['act_amt_flag']!='1'){
			$ttl_amt=$ttl_amt+$trn_rel['product_total_conv'];
		}*/
		///////////////////////////////////////////////////////////////////////////////////////
		if($cnt==$p)
		{
					$html.='<tr style="">
					<td style="border: 1px solid black; width: 40px; text-align: right;" colspan="6"><b>TOTAL QUANTITY</b></td> 
                   <td style="border: 1px solid black;text-align:right"><b>'.number_format((float)$ttl_qty, 2, '.', '').'</b></td>
                   <td style="border: 1px solid black;text-align:right"><b>OFFER VALUE</b></td>
                   <td style="border: 1px solid black;text-align:right"><b>'.$currency_symbol.' '.number_format($ttl_amt,2,".","").'</b></td>
				</tr>';
				$qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
           left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
           left join tbl_ledger as l on l.l_id=tc.tax_id 
           where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
           $result11=$dbcon->query($qry11);		
           while($row11=mysqli_fetch_assoc($result11))
           {
               $html.='<tr>
               <td colspan="'.($colspan + 1).'" style="text-align:right;border:1px solid ;"><b>'.$row11['l_name'].'</b></td>
               <td style="text-align:right;border:1px solid;"><b>
               '.$currency_symbol.' '.number_format($row11['add_sum'],2,".","").'
               </b></td>';
              /* if($rel['quot_type'] == '1'){
                   	$html.= ' <td style="border: 1px solid black;text-align:right"><b></b></td>
                   	 <td style="border: 1px solid black;text-align:right"><b></b></td>';

                   }*/
               $html.='</tr>';
           }
           $qry12="select b.sundry_amount_conv,b.sundry_gst_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
           from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
           left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
           where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";

           $result12=$dbcon->query($qry12);	
            $vartotal = 0;	
           while($row12=mysqli_fetch_assoc($result12))
          
           {
               $html.='<tr>
               <td style="border: 1px solid black; width: 40px; text-align: right;" colspan="8"><b>'.strtoupper($row12['l_name']).'</b></td>
               <td  style="border: 1px solid black;text-align:right"><b> '.$currency_symbol.' '.number_format($row12['sundry_amount_conv'],2,".","").'</b></td>';
              /* if($rel['quot_type'] == '1'){
                   	$html.= ' <td style="border: 1px solid black;text-align:right"><b></b></td>
                   	 <td style="border: 1px solid black;text-align:right"><b></b></td>';

                   }*/
               $html.='</tr>';
                $vartotal =   $vartotal + $row12['sundry_amount_conv']+ $row12['sundry_gst_amount_conv']; 
                 
                $total_cs_gst = $total_cs_gst + $row12['sundry_gst_amount_conv']; 
           }

             $subtotal = $ttl_amt + $vartotal;
           if(!empty($total_cs_gst) || !empty($total_i_gst)){
            if($rel['c_add_state']==$comp_rel['stateid']){
                $html.='<tr>
                <td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>CGST</b></td>
                <td style="text-align:right;border:1px solid;"><b>
                '.$currency_symbol.' '.number_format(($total_cs_gst/2),2,".","").'
                </b></td>';
              /* if($rel['quot_type'] == '1'){
                   	$html.= ' <td style="border: 1px solid black;text-align:right"><b></b></td>
                   	 <td style="border: 1px solid black;text-align:right"><b></b></td>';

                   }*/
               $html.='
                <tr>
                <td colspan="'.$colspan.'" style="text-align:right;border:1px solid;"><b>SGST</b></td>
                <td style="text-align:right;border:1px solid;"><b>
                '.$currency_symbol.' '.number_format(($total_cs_gst/2),2,".","").'
                </b></td>';
               /*if($rel['quot_type'] == '1'){
                   	$html.= ' <td style="border: 1px solid black;text-align:right"><b></b></td>
                   	 <td style="border: 1px solid black;text-align:right"><b></b></td>';

                   }*/
               $html.='</tr>';
                  $subtotal = $subtotal + $total_cs_gst;
            }else{
                $html.='<tr>
                <td colspan="'.($colspan-2).'" style="text-align:right;border:1px solid;"><b>IGST</b></td>
                <td style="text-align:right;border:1px solid;"><b>
                '.$currency_symbol.' '.number_format(($total_i_gst),2,".","").'
                </b></td>';
              /* if($rel['quot_type'] == '1'){
                   	$html.= ' <td style="border: 1px solid black;text-align:right"><b></b></td>
                   	 <td style="border: 1px solid black;text-align:right"><b></b></td>';

                   }*/
               $html.='</tr>';
                   $subtotal = $subtotal + $total_i_gst;
            }
        }
           

				$html.='<tr>
               <td style="border: 1px solid black; width: 40px; text-align: right;" colspan="8"><b>TOTAL OFFER VALUE</b></td>
               
   
        
               <td style="border: 1px solid black;text-align:right"><b>'.$currency_symbol.' '.number_format($subtotal,2,".","").'</b></td>';
               /*if($rel['quot_type'] == '1'){
                   	$html.= ' <td style="border: 1px solid black;text-align:right"><b></b></td>
                   	 <td style="border: 1px solid black;text-align:right"><b></b></td>';

                   }*/
               $html.='</tr>
           <tr>
               <td colspan="'.($colspan + 1).'"
               style="border: 1px solid black;    text-align: left;"><b>Total Amount In Words:'.(($comp_rel['currency_id']==$rel['currency_id']) ? ucfirst(convert_number_to_words_new($subtotal,$rel['currency_id'],$currency_word_end,$currency_word_start)) : ucfirst(convert_number_to_words_new($subtotal,$rel['currency_id'],$currency_word_end,$currency_word_start))).'</b></td>
           </tr>
           <tr>
               <td colspan="'.$colspan.'"
               style="border: 1px solid black;    text-align: left;"><b>Remarks :</b> '.$rel['quot_remark'].' </td>
           </tr>
				 </table>
    	       </div>
    	     <div style="clear:both;"></div>';
		}
		///////////////////////////////////////////////////////////////////////////////////////
		
		 $pcount++;
    		if($pcount==7 && $cnt!=$p)
			{
    		     $pcount=1;
    		   $html.='
    		   
    		   </table>
    		   </div>
    		   <center class="nextpage"></center>
    		     <div style="clear:both;"></div>';
    	      
    		}
    		
	   // if($p==5){
	   // 	$html.='<center class="nextpage"></center>';
	   // }
    
	
	    $p++;
	}
	

	

	$html.='</tbody>
	</table></div>
	';
	//  $html.='<center class="nextpage"></center>';

	 $html.='
	 <div style="clear:both;"></div>';
	 $terms_qry = "select qtrm.*,mst.print_name from tbl_quotation_terms_trn as qtrm 
	 left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
	 where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=" . $rel['quotation_id'] . " order by qtrm.tc_priority";
			 $terms_qry_rs = $dbcon->query($terms_qry);
			 if (brp_mysqli_num_rows($terms_qry_rs)) {
				 $html .= '<br>
	 <div>
		 <table width="100%" style="font-size:16px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3">
			 <tbody>
				 <tr style="border: 0;">
					 <td style="border: 0;background-color:rgb(247, 199, 207);vertical-align: middle;">
						 <b>TERMS AND CONDITION</b>
					 </td>
				 </tr>
			 </tbody>
		 </table>
		 <table width="100%" style="font-size:13px;border-collapse: collapse;width:100%;overflow:wrap;background-color:rgb(208, 208, 208);" cellpadding="3" cellspacing="3"><tbody>
	 ';
				 $t = 1;
				 while ($term_rel = brp_mysqli_fetch_assoc($terms_qry_rs)) {
					 $string = (nl2br($term_rel['tc_details']));
	 
					 $html .= '<tr style="border: 0;">
						 <td style="border: 0; width:25%;vertical-align: top;"><b>' . $t . ' ' . $term_rel['print_name'] . '</b></td>
						 <td style="border: 0; width:3%;vertical-align: top;"><b>:</b></td>
						 <td style="border: 0;width:75%;text-align:left;padding:5px;vertical-align: top;">' . $string . '</td>
			 
			 </tr>';
	 
					 $t++;
				 }
				 $html .= '</tbody></table></div>';
			 }
		 $html .='	<table style="width:100%;" cellpadding="5" cellspacing="5">
			 <tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;">
						 
						 <td  style=" text-align:left; border:none;vertical-align:top;"><b><br>
						 <span style="font-size:17px;">Bank Details : </span>
						 
					 
						 
	 </td></tr>
		 <tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;">
						 <td  style=" text-align:left; border:none;vertical-align:top;"><b>
					 
						 
						 
						 Account Name :</b> '.$comp_rel['company_name'].'<br>
						 
						 
	 </td>	
	 </tr>
		 <tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;">
						 <td  style=" text-align:left; border:none;vertical-align:top;"><b>
					 
						 <b>Bank Name</b> &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;: '.$comp_rel['bank_name'].'<br>
						 
						 
	 </td>	
	 </tr>
		 <tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;">
					 
						 <b>Account No.</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:  '.$comp_rel['ac_no'].'<br>
					 
						 
	 </td>	
	 </tr>
		 <tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;">
						 <td  style=" text-align:left; border:none;vertical-align:top;"><b>
					 
						 <b>IFSC</b> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;: '.$comp_rel['ifcs'].', '.$comp_rel['branch_name'].'<br>
						 
						 
	 </td>	
	 </tr>
	 <br>
	 </table>
	 <table style="width:100%;" cellpadding="5" cellspacing="5">
			 <tr style="border:1px solid rgb(208, 209, 210);border-left:none;border-right:none;border-bottom:none;">
						 
						 <td  style=" text-align:left; border:none;vertical-align:top;padding-right:60px;"><b>
						 <span style="text-decoration:underline">SUBJECT TO KALOL JURISDICTION <BR> Declaration</span><br>
						 We Declare that this performa invoice shows the actual price of the goods described and that all particulars are true and correct.
						 
					 
						 
	 </td>
						 <td  style=" text-align:right; border:none;vertical-align:top;"><b>
						 <span style="">Thanking you,<br>
						 for universal bags<br>';
						 if($comp_rel['authorized_signature']!=""){
							 $html.='<img src="'.DOMAIN_F.'view/upload/signature/'.$comp_rel['authorized_signature'].'" style="height: 100px; width: 100px;"><br>';
						 }else{
							 $html.='<br><br><br>';
						 }
						 $html.='Abhishek Agrawal<br>
					 (Partner)</span>
						 
					 
						 
	 </td>
	 </tr>
	 </table>';	
	 
	

	 
	 $query="select mst.* from tbl_inq_attach as mst 
	 where mst.inq_attach_status=0 and mst.inquiry_id=".$rel['inquiry_id'];

 $result=$dbcon->query($query);
 while($irel=mysqli_fetch_assoc($result)){
	
	$html.= '<center class="nextpage"></center> <div style="clear:both;"></div>';	
	$html.='<div><img src="'.DOMAIN_F.'view/upload/inq_attach/'.$irel['inq_attch_file'].'" style="height:400px;"></div>';  
 }
	 
			$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
// echo $html;exit;
ob_end_clean();
	$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
	if($save_file=="No"){
			include("../../view/export/mpdf/mpdf.php");
		}else{
			include("../../../view/export/mpdf/mpdf.php");
		}

$mpdf=new mPDF('','A4','0','proximanova','10','10','45','30','1','1');
//		$mdf->SetFont('ProximaNova');
$mpdf->defaultheaderfontsize = 10; /* in pts */
$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
$mpdf->defaultfooterfontsize = 10; /* in pts */
$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);
$mpdf->SetWatermarkText();
$mpdf->showWatermarkText = true;
$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
$mpdf->WriteHTML($html);
if($save_file=="No"){
				$mpdf->Output();
			}else{
				$mpdf->Output('../../../view/upload/mail_attach/'.$file_name,'f');
			}
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
ob_clean();
	return $file_name;
// return 'quotation'.$quotation_id.'.pdf';
}

}

?>