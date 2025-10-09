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
	$userPhone = $userData['user_phone'] ? 'Mobile no.: '.$userData['user_phone'] : '';
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
		$header ='<div style="text-align:center;padding-top:30px"><img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:8.27in;" /></div>';
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

		$payment_trms = "-";

		if(!empty($rel['payment_tems_jainflex'])){

			$payment_trms  = $rel['payment_tems_jainflex'];
		}
		
		$colspan = 7;
//Amish Soni End 16-03-2021
		$html ='<html>
		<head>					
		<title>Quotation - '.$rel['quotation_no'].'</title>
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

		.terms{ 
		
            border-collapse: collapse;

			width:100%;

		}
            table, tr, td{
            border-collapse: collapse;
        }
			
			.blueHeading {
				color: #365f91;
			}

			</style>
			</head>
			<body>
				<!--Show Logo in other pages-->
			<htmlpageheader name="otherpages" style="display:none">
				<div style="text-align:center">'.$header.'</div>
				</htmlpageheader>
				<!-- <htmlpagefooter name="otherpages_footer" style="display:none">
				<div style="text-align:center">'.$footer.'</div>
				</htmlpagefooter> -->
				<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
            
<div style="vertical-align: top; text-align:center;">
<div style="text-align:center;font-size:20px;font-weight:bold;"> <b>'.$comp_rel['company_name'].'</b><br></div>
               
                '.$comp_rel['address'].' <br>
                GST No. : '.$comp_rel['vatno'].' <span style=
				"color:black;">CIN NO. : '.$comp_rel['cin'].'</span>  Website :
                <a href="'.$comp_rel['company_website'].'" >'.$comp_rel['company_website'].'</a>
             
        </div> 
		  <div style="text-align:center;font-size:20px;font-weight:bold;">Quotation</div>
          <div style="text-align:left;">'.$comp_rel['qout_header'].'</span></div>
		
        
			<table cellpadding="5" cellspacing="5" style="border:1px solid black;font-size: 14px;">
            <tr >
            <td  rowspan="8" style="border: 1px solid black; width:60%;">
                <p><b> 
                    To, 
                    <br>
                    '.$rel['qt_company_name'].'
                    <br>
                    '.$rel['c_con_fname']. ' '. $rel['c_con_lname'].'
                    <br>
                   '.$quot_address.'
               </p>
                 </td>
                
        </tr>
        <tr>
            <td style="border:1px solid black; border-right:0px;width:20%;"> Quotation No. : <br><b><i>'.$rel['quotation_no'].'</i></b></td>
            <td style="border:1px solid black;border-left: 0px;width:20%;">Date :<br> <b>'.$rel['quotation_date'].'</b></td>
        </tr>
        <tr>
            <td style="border:1px solid black;border-right:0px;width:20%;"> Reference No. :<br> <b>'.$rel['inquiry_no'].'</b></td>
            <td style="border:1px solid black;border-left: 0px;width:20%;">Date :<br> <b>'.$rel['inquiry_ref_date'].'</b></td>
        </tr>
        <tr>
            <td style="border:1px solid black;border-right:0px;width:20%;">Quotation Validity :<br> <b>'.$rel['quotation_valid_days'].'</b></td>
            <td style="border:1px solid black;border-left: 0px;width:20%;">Payment Terms :<br> <b>'.$payment_trms.'</b></td>
        </tr>
            </table>';
    // echo $html;die;

           
            
			if($inquiry_type!="2"){
					$trn_qry="SELECT trn.*,pro.product_name,unit.unit_name, pro.product_icode,hsn.hsn_code FROM tbl_quotation_trn as trn 
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
			   $p=1;$ttl_amt=0;$ttl_qty=0;$charges_qty=0;$total_gst=0;$total_i_gst=0;$pcount=1;
              
              
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
			   	$amt = $trn_rel['product_qty'] * $trn_rel['product_rate'];
			   	$ttl_amt = $ttl_amt + 	$amt;
			   	$ttl_qty = $ttl_qty + $trn_rel['product_qty'];
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

			   	if($pcount=="1")
				{
						$html.='<div style="clear:both;"></div>
						<div>
						<table style=" border: 1px solid black;border-collapse: collapse;width:100%;">
						<tr>
							<td style="border: 1px solid black; text-align: center; width:5%;"  ><b>Sr no.</b></td>
							<td style="border: 1px solid black; text-align: center; width:75%;" ><b>Description</b></td>
							<td style="border: 1px solid black; text-align: center; width:10%;"  ><b>Qty<br>(In Mtrs)</b></td>
							<td style="border: 1px solid black; text-align: center; width:10%;"  ><b>Rate (Per metre) <br><span>'.$currency_name.' </span> </b></td>
						</tr>';
				}
$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';
				$html .=' 
                <tr>
                    <td style="border: 1px solid black; border-bottom:none; border-top:none; text-align: center;width:5%;vertical-align:top; "><b>'.$p.'</b></td>
                    <td style="vertical-align:top;border: 1px solid black; border-bottom:none; border-top:none; text-align: left; width:75%; padding-left:10px;">'.$trn_rel['product_name'].'';
if(!empty($product_desc)){
					$html .='<br><b>Desc:</b>'.$product_desc.'';}
					$html .='</td> 
                    <td style="vertical-align:top;border: 1px solid black; border-bottom:none; border-top:none; text-align: right; width:10%; padding-right:10px;">'.number_format($trn_rel['product_qty'],2,".","").'</td>
                    <td style="vertical-align:top;border: 1px solid black;border-bottom:none;  border-top:none;padding-right:10px; width:10%; text-align: right; ">'.$currency_symbol.' '.number_format($trn_rel['product_rate'],2,".","").'</td>
                </tr>';	
			
				
		/*		$pr=10-$cnt;
				for($j=0; $j<$pr; $j++)
				 {
					 $html.=' <tr>
					 <td style="border: 1px solid black; border-top:none; border-bottom:none; width:5% ;">&nbsp;</td>
					 <td style="border: 1px solid black; border-top:none; border-bottom:none; width:65%">&nbsp;</td>
					 <td style="border: 1px solid black; border-top:none; border-bottom:none; width:10%;">&nbsp;</td>
					 <td style="border: 1px solid black; border-top:none; border-bottom:none; width:25% ;">&nbsp;</td>
				 </tr>
			   ';
			 
				 }*/
			
				
				if($cnt==$p)
				{
					$html.=' <tr>
					<td style="border: 1px solid black; border-top:none; border-bottom:none; width:5% ;">&nbsp;</td>
					<td style="border: 1px solid black; border-top:none; border-bottom:none; width:75%">&nbsp;</td>
					<td style="border: 1px solid black; border-top:none; border-bottom:none; width:10%;">&nbsp;</td>
					<td style="border: 1px solid black; border-top:none; border-bottom:none; width:10% ;">&nbsp;</td>
				</tr>
			  ';
					$html.='<tr>
						 <td style="border: 1px solid black; border-bottom:none; text-align: center; "><b>&nbsp;</b></td>
						 <td style="border: 1px solid black; border-bottom:none; text-align: center;  padding-left:10px;"><b>Total</b></td> 
						 <td style="border: 1px solid black; border-bottom:none; text-align: center; "><b>'.number_format($ttl_qty,2,".","").'</b></td>
						 <td style="border: 1px solid black;border-bottom:none;  text-align: right; "><b></b></td>
					 </tr>
					 
					 ';

				}
				

			
				$pcount++;
    		if($pcount==14 && $cnt!=$p)
			{
    		     $pcount=1;
    		   $html.='
    		   
    		   </table>
    		   </div>
    		   <center class="nextpage"></center>
    		     <div style="clear:both;"></div>';
    	      
    		}
			
			

              		$p++;	
				}

				

			$html.='</table>
			<table style=" border: 1px solid black;border-collapse: collapse;font-size:13px;">
			
			<tr >
			<td style="border: 1px solid black;border-right:none;  text-align: left;width:27%; "><b>Bank Name : '.$comp_rel["bank_name"].'</b></td> 
			<td  style="border: 1px solid black;  text-align: left;width:43%;border-left:none;border-right:none;   "><b> Branch & IFSC  :  '.$comp_rel["branch_name"].' & '.$comp_rel["ifcs"].'</b></td> 
			 
			<td   style="border: 1px solid black;  text-align: right;width:30%; border-left:none; "><b>Account No. : '.$comp_rel["ac_no"].'</b></td> 

		</tr></table>
			 ';
			 $terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
			 left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
			 where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
			 $terms_qry_rs=$dbcon->query($terms_qry);
			 if(mysqli_num_rows($terms_qry_rs)){
				 $html .= '<table style=" border: 1px solid black;border-collapse: collapse;font-size:13px;">
				<tr style="border:1px solid;border-bottom:none;">	
				<td   style=" border: 0px; text-align: left;padding-left: 10px; ">
					<b>TERMS AND CONDITIONS.</b>
					
				</td>
				<td   style=" border-left:1px solid black; text-align: left;padding-left: 10px; ">
					
					
				</td>
				
				</tr><tr style="border:1px solid;border-top:none;"><td width="70%"  style="padding-left: 10px;"><table>';
				 $t=1;
				 while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
					 $string=(nl2br($term_rel['tc_details']));
	 
					 $html.='<tr><td style="vertical-align:top;">'.$t.'.&nbsp;&nbsp; '.$term_rel['tc_name'].'</td><td style="vertical-align:top;"> : 
					  '.$string.'</td></tr>
					 ';
					 $t++;
				 }
				 
				 
				 $html .='</table><td width="30%" style="text-align: center; border-right: 1px solid black;border-left: 1px solid black;border-bottom: 1px solid black; padding-left: 10px;" >
						<b>With Best Regards, 
						<br>			
						<br>			
						<br>	
						<br>				
						'.$comp_rel['company_name'].'<br>	</b><p style="text-align:left;">
						'.$userData['user_name'].'	<br>		
'.$userPhone.'	<br>	
Email ID : '. $userData['user_mail'].'	<br></p></div>		
						</td>';
				 $html.='</tr></tbody></table></div>';	
			 }
	$html.='
						
			<p style="text-align: center;">Subject to Ahmedabad jurisdiction <br>
				This is a Computer generated Document										
				</p> ';	

				
				




			$html.='
			</body>
			</html>';
 //echo $trn_qry;
 // echo $html;die;
			ob_end_clean();
			$file_name = $rel['quotation_no'].'.pdf';
			$file_name=str_ireplace("/","_",$file_name);
			if($save_file=="No"){
				include("../../view/export/mpdf/mpdf.php");
			}else{
				include("../../../view/export/mpdf/mpdf.php");
			}
			$mpdf=new mPDF('','A4','0','Calibri','10','10','38','0','1','1');
//		$mdf->SetFont('ProximaNova');
			$mpdf->defaultheaderfontsize = 10; /* in pts */
			$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
			$mpdf->defaultfooterfontsize = 10; /* in pts */
			$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
			$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
			 $mpdf->SetHTMLHeader($header);
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