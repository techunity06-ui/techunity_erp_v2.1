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
	$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
	WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
	$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
	$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
	$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';

// $quotation_id = $_REQUEST['id'];	
	$type='pdf';
	if(strtolower($type) == 'pdf') {
//Quotation Data
		$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date, ref.rb_name, cadd.c_add_state from tbl_quotation as quot
		left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
		left join tbl_customer as cust on cust.cust_id=quot.cust_id
		left join tbl_cust_address as cadd on cadd.cust_id=quot.cust_id
		left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
		left join tbl_refer_by as ref on inq.rb_id=ref.rb_id
		where quot.quotation_id=".$quotation_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
//p($rel);
		if(!$rel){
			header("Location: ".ROOT.CRM_ROOT."quotation_list");
		}

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

			$currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
			$currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
			$currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
			$currency_symbol = $currency_rel['currency_symbol'];
		}
		$quot_address =	$rel['quot_address'] ? (nl2br($rel['quot_address'])) : '';
		$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];

		$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
		$header ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>';
		$footer ='<div style="text-align:center;"><img src="'.DOMAIN_F.LOGO.$comp_rel["f_logo"].'" style="width:8.27in;" /></div><div style="text-align:right;">{PAGENO}{nbpg}</div>';
		$trm_and_cond = "select * from tbl_terms_condition";
		$trandCondition = mysqli_fetch_assoc($dbcon->query($trm_and_cond));
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
		$colspan = 7;
//Amish Soni End 16-03-2021
		$html ='<html  lang="en">
		<head>
		  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		  <title>document</title>
		  <meta name="author" content="OM" />
		  <style >
			* {
			  margin: 0;
			  padding: 0;
			  text-indent: 0;
			}
			table{
				border-collapse:collapse;
				width:100%;
			}

		
            table, tr, td{
            border-collapse: collapse;
        }
			
			.nextpage {
				page-break-after: always; }

			
			
		  </style>
		</head>
		<body>
	
		  <h2 style="text-align: center; text-decoration: underline;">
			Quotation
		  </h2>
		 <hr>
		  <p>
			<b>
				Sub: Your Requirement of ' . $rel["quot_subject"] . '
				<br>
				<br>
				Dear Sir,
				<br>
				<br>
				Thank You Very Much for Your Valuable Inquiry to us.
				<br>
				<br></b>
				Our Organizations foundation was laid down by Mr. T.Y. Khare, founder of
			the company. Who has a vast experience of 35 years in Sheet Metal Cutting
			Field. We had Installed More than 100+ Machine across the India.
			<br>
			<br>
			<b>As Per your Request, We have Submit our best offer of ' . $rel["quot_subject"] . '	.</b>
			<br>
			<br>
			We are happy to provide you best, Reliable Product with best service
			support more than your expectation.
			<br>
			<br>
			<br>
			With Best Regards, 
			<br>
			<br>
			' . $userData['user_name'] . '
			<br>
			'  .$userData['user_phone']  . '
			<br>
			Email: <a href="mailto:sales@ashwinengg.com"  target="_blank">sales@ashwinengg.com</a>
			<br>
			Web: <a href="http://www.ashwinengg.com/" target="_blank" >www.ashwinengg.com</a>
		  </p>
		  <center class="nextpage"></center>

		  <h2  style="text-align: center; color: red;">
		  ' . $rel["quot_subject"] . '
		  </h2>
		 
	  ';
	 
	
          

           
            
			if($inquiry_type!="2"){
					$trn_qry="SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode,hsn.hsn_code FROM tbl_quotation_trn as trn 
				   left join product_mst as pro on pro.product_id=trn.product_id
				   left join unit_mst as unit on unit.unitid=trn.unitid
				   left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
				   where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
			   } else {
					 $trn_qry="SELECT trn.* , pro.product_name,pro.product_icode,pro.product_type FROM `tbl_quotation_project_trn` as trn 
					 left join product_mst as pro on pro.product_id = trn.product_id 
				   where trn.quotation_projecttrn_status=0 and trn.quotation_id =".$rel['quotation_id'];
			   }
			 
			   $trn_qry_rs=$dbcon->query($trn_qry);
			   $p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;
              
              
			   $cnt=mysqli_num_rows($trn_qry_rs);

			   while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
				$item_code = '';
				   if(in_array('item',$sales_pro_search)){
					   $item_code = " -- (".$trn_rel['product_icode'].")";
				   }
				$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
				$product_spec = ($trn_rel['product_spec']) ? nl2br($trn_rel['product_spec']) : '';
				$product_type = ($trn_rel['product_type']) ? nl2br($trn_rel['product_type']) : '';
				$product_name = ($trn_rel['product_name']) ? nl2br($trn_rel['product_name']) : '';
				$product_spec = ($trn_rel['product_spec']) ? nl2br($trn_rel['product_spec']) : '';
				
				
			   	$gst_per = $trn_rel['cgst_tax_per']+$trn_rel['sgst_tax_per']+$trn_rel['igst_tax_per'];
				$gst_rate = $trn_rel['cgst_tax_rate_conv']+$trn_rel['sgst_tax_rate_conv']+$trn_rel['igst_tax_rate_conv'];

				if($trn_rel['cgst_tax_rate_conv'] != 0 || $trn_rel['sgst_tax_rate_conv'] !=0){
					$total_cs_gst += $gst_rate;
				}else{
					$total_i_gst += $gst_rate;
				}
				$trn_qryim="SELECT pro.image_name,pro.product_name FROM tbl_quotation_trn as trn 
				left join product_mst as pro on pro.product_id=trn.product_id
				where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id']." group by product_category";
				
				$trn_qry_rsim=$dbcon->query($trn_qryim);
				while($trn_relim=mysqli_fetch_assoc($trn_qry_rsim)){
					if(!empty($trn_relim['image_name'])){
						$html.='<h2 style="text-align: center; color: rgb(55, 0, 255);"> <i>'.$trn_relim['product_name'].'</i></h2>
			
				<h3 style="text-align: left;" >
				   MODEL: '.$item_code.'
				 </h3>
				 <br>
				 
				   <div style="text-align: center; align-items: center;">
					
						   <img width="548" height="355"
							 src="'.DOMAIN_F.'/upload/product_images/'.$trn_relim["image_name"].'"/>
						 </div>
				   
					 <p style="text-align: center;"><b>(More Information refer here: <a href="http://www.ashwinengg.com">http://www.ashwinengg.com</a>)</b></p>
					 <br>
					 <center class="nextpage"></center>
			   
				 ';
							
					}
					
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
			   	$amt = $trn_rel['product_qty'] * $trn_rel['product_rate'];
			   	$ttl_amt = $ttl_amt + 	$amt;
			   	$ttl_qty = $ttl_qty + $trn_rel['product_qty'];
				   $html.='
				   <p>
					   '. $product_desc.'
				   </p>
				   <center class="nextpage"></center>
			 
				   
				   <h2 style="text-align: center; text-decoration: none;">
					   COMMERCIAL OFFER
					 </h2>
					 <p style="text-align: left;">
					   We Thank you very much for your valued enquiry for our products. We are
					   pleased to submit our best prices as under.
					 </p>
					
				  
				   <table style="  border: 1px solid black;"  >
					   <tr>
						   <td style="border: 1px solid black; text-align: center; width:50px; " ><b>Sr no.</b></td>
						   <td style="border: 1px solid black; text-align: center; width:450px;padding-left: 10px; padding-right: 10px; "><b>Product supply particulars. </b></td>
						   <td style="border: 1px solid black; text-align: center; width:40px; padding-left: 10px; padding-right: 10px; " ><b>Qty.</b></td>
						   <td style="border: 1px solid black; text-align: center; width:85px; padding-left: 10px; padding-right: 10px; " ><b>Unit rate</b></td>
						   <td style="border: 1px solid black; text-align: center; width:85px; padding-left: 10px; padding-right: 10px; " ><b>Amount.</b></td>
					   </tr>
					   ';
					$html.='<tr>
						<td style="border: 1px solid black; text-align: center; width:50px; border-bottom: none;border-top: none;" ><b>'.$p.'</b></td>
						<td style="border: 1px solid black; text-align: center; width:450px;padding-left: 10px; border-top: none; border-bottom: none; padding-right: 10px; "><b>'.$trn_rel['product_name'].'</b></td>
						<td style="border: 1px solid black; text-align: center; width:40px; padding-left: 10px; border-top: none; border-bottom: none; padding-right: 10px; " ><b>'.$trn_rel['product_qty'].'</b></td>
						<td style="border: 1px solid black; text-align: center; width:85px; padding-left: 10px; border-top: none; border-bottom: none; padding-right: 10px; " ><b>'.$currency_symbol.' '.$trn_rel['product_rate'].'</b></td>
						<td style="border: 1px solid black; text-align: center; width:85px; padding-left: 10px; border-top: none; border-bottom: none; padding-right: 10px; " ><b>'.$currency_symbol.' '.	$amt.'</b></td>
					</tr>';					
				}
				
				$pr=10-$cnt;
               for($j=0; $j<$pr; $j++)
				{
					$html.='<tr>
						<td style="border: 1px solid black; text-align: center; width:50px;   border-top: none; border-bottom: none;" ><b>&nbsp;</b></td>
						<td style="border: 1px solid black; text-align: center; width:450px;padding-left: 10px; border-top: none; border-bottom: none; padding-right: 10px; "><b> &nbsp;</b></td>
						<td style="border: 1px solid black; text-align: center; width:40px; padding-left: 10px; border-top: none; border-bottom: none; padding-right: 10px; " ><b>&nbsp;</b></td>
						<td style="border: 1px solid black; text-align: center; width:85px; padding-left: 10px; border-top: none; border-bottom: none; padding-right: 10px; " ><b>&nbsp;</b></td>
						<td style="border: 1px solid black; text-align: center; width:85px; padding-left: 10px; border-top: none; border-bottom: none; padding-right: 10px; " ><b>&nbsp;</b></td>
					</tr>
					';
			
	            }
				$html.='';		
				
				

           /////////////////////////////////////////////////PACKAGING CHARGES///////////////////////////////////////     
           if(!empty($total_cs_gst) || !empty($total_i_gst)){
            if($rel['c_add_state']==$comp_rel['stateid']){
                $html.='<tr>
					<td style="border: 1px solid black; text-align: center; width:50px; border-bottom: none;" >&nbsp;</td>
					<td style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b> <b>CGST</b> </b></td>
					<td colspan="3" style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b>'.$currency_symbol.' '.number_format(($total_cs_gst/2),2,".","").'</b></td>
					
				</tr>
				<tr>
					<td style="border: 1px solid black; text-align: center; width:50px; border-bottom: none;" >&nbsp;</td>
					<td style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b> <b>SGST</b> </b></td>
					<td colspan="3" style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b>'.$currency_symbol.' '.number_format(($total_cs_gst/2),2,".","").'</b></td>
					
				</tr>';
            }else{
                $html.='<tr>
				<td style="border: 1px solid black; text-align: center; width:50px; border-bottom: none;" >&nbsp;</td>
				<td style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b> <b>IGST</b> </b></td>
				<td colspan="3" style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b>  '.$currency_symbol.' '.number_format(($total_i_gst),2,".","").'</b></td>';
            }
        }
           $qry11="select sum((tc.tax_per*trn.product_amount_conv)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_quotation_trn as trn 
           left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
           left join tbl_ledger as l on l.l_id=tc.tax_id 
           where tc.tax_additional='1' and trn.quotation_id=".$rel['quotation_id']." and trn.quot_trn_status!=2 and tc.isdelete='0' group by tc.tax_id";
           $result11=$dbcon->query($qry11);		
           while($row11=mysqli_fetch_assoc($result11))
           {
               $html.='
			   <tr>
			   <td style="border: 1px solid black; text-align: center; width:50px; border-bottom: none;" >'.$row11['l_name'].'</td>
			   <td style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b> <b>SGST</b> </b></td>
			   <td colspan="3" style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b>'.$currency_symbol.' '.number_format($row11['add_sum'],2,".","").'</b></td>
			   
		   </tr>';
           }
      
           $qry12="select b.sundry_amount_conv,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
           from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
           left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
           where b.sundry_voucher_id=".$rel['quotation_id']." and b.sundry_voucher_table='tbl_quotation' and b.isdelete='0' and le.default_sundry='0'";

           $result12=$dbcon->query($qry12);	
            $vartotal = 0;	
           while($row12=mysqli_fetch_assoc($result12))
          
           {
               $html.=' <tr>
			   <td style="border: 1px solid black; text-align: center; width:50px; border-bottom: none;" >&nbsp;</td>
			   <td style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b> <b>'.$row12['l_name'].'</b> </b></td>
			   <td colspan="3" style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b> '.$currency_symbol.' '.number_format($row12['sundry_amount_conv'],2,".","").'</b></td>
			   
		   </tr>';
                $vartotal =   $vartotal + $row12['sundry_amount_conv']; 
           }
          // Format the value to 2 decimal places
           $subtotal = $ttl_amt + $vartotal; // Add the formatted value to the subtotal

          // Format the value to 2 decimal places
          // Add the formatted value to the subtotal
           $html.=' <tr>
		   <td style="border: 1px solid black; text-align: center; width:50px; border-bottom: none;" ><b></b></td>
		   <td style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; color:red;"><b>TOTAL AMOUNT</b></td>
		   <td colspan="3" style="border: 1px solid black; text-align: center; padding-left: 10px;  border-bottom: none; padding-right: 10px; "><b>'.$currency_symbol.' '.indian_number($subtotal).'</b></td>
		   
	   </tr>';
	   
	   $html.=' </table>';
	   
	   $html.='<h4 style="text-align: center; text-decoration: none;">
	   GENERAL TERMS AND CONDITIONS.
	 </h4>
	
	 <p>'.$rel['quot_annex_content'].'</p>
		 <h3>Terms & Conditions</h3>
	 
 
			 <table style="overflow-x:auto;">
				 <tr>
					 <td width="20%">
						 <b>'.$trandCondition['tc_name'].'</b>
					 </td>
					 <td>: '.$trandCondition['tc_details'].'
 </td>
					 
				 </tr>
			 </table>
 ';

	 
	  
          

			
// 	 $terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
// 	 left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
// 	 where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
// 	 $terms_qry_rs=$dbcon->query($terms_qry);
// 	 if(mysqli_num_rows($terms_qry_rs)){
// 		 $html .= "<tr><ol>";

// 		 while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
// 			 $string=(nl2br($term_rel['tc_details']));
// 			 $html.='<tr style="border:0px solid #000;">
// 			  <li>'.$term_rel['tc_name'].' : '.$string.'</li>';
// 		 }

// 		 $html .= "</ol></tr>";
// 	 }else{
// 				$html .= '<table >
// 				<tr>
// 					<td width="20%">
// 						<b>'.$trandCondition['tc_name'].'</b>
// 					</td>
// 					<td>: '.$trandCondition['tc_details'].'
// </td>
					
// 				</tr>
// 			</table>';
// 			}	

			$html .= ' 
			<center class="nextpage"></center>
			<tr>
			<td style="border: 1px solid black; border-collapse: collapse;  padding: 10px;" >
           <p>Other Terms: 
			<br>
			1. Order once placed will not be cancelled without our agreement. We may forfeit the advance paid if
			any in full in the event of cancellation of the order without mutual settlement.
			<br>
			2. Supply will be as per the particulars mentioned in this document only.
			<br>
			3. Any alteration in the particulars and or specifications of the goods/services mentioned in this
			document may cause price Escalation. In such case this document will be amended on terms and
			conditions as of original.
			<br>
			4. The price stated in this document is based on the prices of raw materials and bought components
			being used for the Manufacture of the items. If there is any increase in these prices due to market
			conditions or government policies between the date of this document and the date of delivery then
			price of the items under supply may be increased. Such increase in the price shall cause
			amendment to this document.
			<br>
			5. Any conflicting issues between the particulars/specifications of the items required by you and
			those stated in this document should be brought to our notice within one week from the date of
			issue of this document. If such as communication in not received or your acknowledge to this
			document is not received, within one week, then we shall treat the particulars and/or
			specifications of the items stated in this document as valid.
			<br>
			6. Unless otherwise accepted, price stated in this document does not include any charges towards
			freight, insurance, taxes and duties. These are included in the invoices as other charges extra
			payable by the buyer as ruled the time of delivery
			<br>
			7. Ordered items may be supplied in parts with mutual consent. In such case we may raise invoice
			for each supply on same Terms and conditions as applicable to full supply order, taxes & duties
			applicable at the time of delivery of each supply shall be mentioned in the invoice depending on
			government regulations these charges may change from one part supply to other part supply.
			<br>
			8. If specifically stated installation will be done by us and one time training will be provided by us.
			<br>
			9. Where applicable supplied material shall carry warranty for 12 month on mechanical parts.
			<br>
			10. When material is dispatched through transporter and/or collection is advised. From a warehouse
			the buyer should report The discrepancy in the quantity within two days of receiving goods failing
			which no claim of shortages will be admissible.
			<br>
			11. Packing of the material shall be as per the normal practice followed by Ashwin Engineering Works
			which is good enough to avoid Transit damage. Any special packing desired by the buyer must be
			informed to us sufficiently, in advance, preferably along with the purchase order. Ashwin
			Engineering Works will not admit any claim of transit damage. Buyer is advised to provide transit
			insurance to avoid risk.
			<br>
			12. Buyer must use the items supplied for the specifically stated application only. Damages caused due
			to use of the item for any other application will not be our responsibility and no claim for damages
			in such cases will be admitted by us.
			<br>
			13. Where operational skill is required to operate the items supplied, the buyer shall train personnel.
			No claim of damages caused due to wrong handling, wrong operating or overriding the directions
			given in the operation manual or written on the equipment will be admitted.
			<br>
			14. Where buyer wants to inspect the items before dispatch with his own arrangement. Or with the
			arrangement of a third party the same muse be arranged within one Week on receiving a
			communication from us that the items are ready for delivery failing To make arrangement for
			inspection or failing to communicate to us for the extension for The arrangement within aforesaid
			stipulate period will cause an action from us to dispatch the items on our own.
			<br>
			15. In case your order documents states the third party inspection as sellers responsibility the
			inspection arrangement shall be made by seller. Fees may be paid on behalf of buyer and invoiced
			to him as other charges.
			<br>
			16. We shall try to maintain the delivery schedule as per your the quoted terms any delay caused due
			to unavoidable Situations beyond our control delivery extensions will be asked and buyer shall
			agree for extended delivery without claiming any compensation.
			<br>
			17. All disputes are subject to Ahmedabad, Gujarat Jurisdiction only.
			</p>
	</td>
	</tr>
			
			<tr>
			<td style="border: 1px solid black; border-collapse: collapse; text-align: right;  padding: 10px;" >
               
			THANKING YOU
			<br><br><br>
			<br><br><br>
			 Authorized Signature <br><br></b>
	</td>
	</tr>';

           $html .='</table>';

			$html.='<sethtmlpagefooter name="otherpages_footer" value="on" />
			</body>
			</html>';
 //echo $trn_qry;
//echo $html;exit;
			ob_end_clean();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			if($save_file=="No"){
				include("../../view/export/mpdf/mpdf.php");
			}else{
				include("../../../view/export/mpdf/mpdf.php");
			}
			$mpdf=new mPDF('','A4','0','Calibri','10','10','35','20','1','1');
//		$mdf->SetFont('ProximaNova');
			$mpdf->defaultheaderfontsize = 10; /* in pts */
			$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
			$mpdf->defaultfooterfontsize = 10; /* in pts */
			$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
			// $mpdf->SetHTMLHeader($header);
			// $mpdf->SetHTMLFooter($footer);
//Show page number : Dimple Panchal (05-Apr-2021)
			$mpdf->pagenumPrefix = ' ';
			$mpdf->pagenumSuffix = ' / ';
			$mpdf->nbpgPrefix = ' ';
			$mpdf->nbpgSuffix = ' pages';
		//	$mpdf->SetFooter('{PAGENO}{nbpg}');
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
			ob_clean();
			return $file_name;
		}
	}
?>