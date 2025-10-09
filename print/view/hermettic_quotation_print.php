<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once("../../crm/include/crm_common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_store_wise_function.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
            QUOTATION_SLUG_PRINT
        ]);
        
if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
        
$quotation_id = $_REQUEST['id'];	
$type='pdf';
$type_mail = $_REQUEST['type'];   // create pdf for attach in email ::  Added by Sanat :: 12-08-21
if(strtolower($type) == 'pdf') {
//Quotation Data
$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date from tbl_quotation as quot
left join tbl_cust_contact as per on per.c_con_id=quot.c_con_id
left join tbl_customer as cust on cust.cust_id=quot.cust_id
left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
where quot.quotation_id=".$quotation_id;
$rel=mysqli_fetch_assoc($dbcon->query($query));

if(!$rel){
    header("Location: ".ROOT.CRM_ROOT."quotation_list");
}
//Company Data
/*$comp_qry="select * from tbl_company as comp 
where comp.company_id=".$rel['company_id'];
*/
$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
		
$comp_rel=mysqli_fetch_assoc($dbcon->query($set));
//$header ='<div style="text-align:right;"><img src="'.DOMAIN_F.LOGO.'Hermettic_Equipments.png" style="width:8.27in;padding-top:20px;" /></div>';  
$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel['logo'].'" style="" />';
//$header =$comp_rel["logo"];
//$footer='<img src="'.DOMAIN_F.LOGO.$comp_rel['f_logo'].'" style="padding-left:0px !important;width:8.27in"/>';

$approve_status='';
if($rel['approve_status']=='0'){
	$approve_status=' (DRAFT)';
}

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
    page-break-inside:auto;
}
table tr { 
    border:1px solid #000 !important;
    page-break-inside:avoid; 
    page-break-after:auto;
}
table td{
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
    <div style="text-align:center">'.$header.'</div>
</htmlpageheader>
<!--<htmlpagefooter name="otherpages_footer" style="display:none">
    <div style="text-align:center">'.$footer.'</div>
</htmlpagefooter>-->
<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
<div>
	<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr>
			<td colspan="3" style="text-align:center;font-size:15px;font-weight:bold;"> 
				Sales Quotation
			</td>
		</tr>
		<tr>
			<td rowspan="11" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
				<strong style="font-size:14px">'.$comp_rel['company_name'].'</strong><br/>
				'.$comp_rel['address'].'<br/>
				Phone No : '.$comp_rel['contact_no'].'<br/><br/><br/>
                                
                                To,<br/>
				<strong style="font-size:14px">'.$rel['cust_name'].'</strong><br/>
				'.(nl2br($rel['quot_address'])).'<br/>
                                Kind Atten:&nbsp;<strong>'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'</strong>
			</td>
			<td style="text-align:left;border:1px solid;width:20%;"> 
				<strong>Quotation No</strong>
			</td>
			<td style="text-align:left;border:1px solid;width:30%;font-size:14px"> 
				<strong>'.$rel['quotation_no'].'</strong>
			</td>
                    </tr>
                    <tr>
                        <td style="text-align:left;border:1px solid;"> 
				Quotation Date
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.date("d-M-Y",strtotime($rel['quotation_date'])).'
			</td>
                    </tr>
                    <tr>
                        <td style="text-align:left;border:1px solid;"> 
				Inquiry Ref No
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$rel['inquiry_no'].'
			</td>
                    </tr>
                    <tr>
                        <td style="text-align:left;border:1px solid;"> 
				Inquiry Ref Date
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.date('d-M-Y', strtotime($rel['inquiry_date'])).'
			</td>
                    </tr>
                    ';

                $user_qry = "select user_name,user_mail,user_phone from users where user_id=".$_SESSION['user_id']." and company_id=".$rel['company_id'];
		$user_data = mysqli_fetch_assoc($dbcon->query($user_qry));
		
                $html .= '<tr>
			<td style="text-align:left;border:1px solid;"> 
				Sales Person
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$user_data['user_name'].'
			</td>
		</tr>
		<tr>
			<td style="text-align:left;border:1px solid;"> 
				Sales Person Contact No
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$user_data['user_phone'].'
			</td>
		</tr>
		<tr>
			<td style="text-align:left;border:1px solid;"> 
				Sales Person Email
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.(strtolower($user_data['user_mail'])).'
			</td>
		</tr>
                <tr>
			<td style="text-align:left;border:1px solid;"> 
				Buyers Reference
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$rel['quotation_ref'].'
			</td>
                </tr>
                <tr>
			<td style="text-align:left;border:1px solid;"> 
				Quotation Valid till
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.date("d-M-Y",strtotime($rel['quotation_valid_date'])).'
			</td>
                </tr>
                <tr>
			<td style="text-align:left;border:1px solid;"> 
				Buyers Email
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$rel['c_con_email'].'
			</td>
                </tr>
                <tr>
			<td style="text-align:left;border:1px solid;"> 
				Buyers Mobile No
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$rel['c_con_mobile'].'
			</td>
                </tr>
	</table>';

$trn_qry="select trn.*,pro.product_name,hsn.hsn_code as product_hsn,unit.unit_name from tbl_quotation_trn as trn 
left join product_mst as pro on pro.product_id=trn.product_id
left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
left join unit_mst as unit on unit.unitid=trn.unitid
where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
$trn_qry_rs=$dbcon->query($trn_qry);
$p=1;$ttl_amt=0;$ttl_qty=0;
$pcount=1;
$cnt=mysqli_num_rows($trn_qry_rs);
while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
	$product_desc = ($trn_rel['product_desc']) ? nl2br($trn_rel['product_desc']) : '';

	if($pcount=="1"){
	    $html.='<div style="clear:both;"></div>
	    <div>
	    <table  style="font-size:12px;border-collapse: collapse;width:100% !important;table-layout:fix;" >
	   
	       	<tr>
				<th style="width:5% !important;text-align:center;border:1px solid;">Sr.<br/>No.</th>
				<th style="width:53% !important;text-align:center;border:1px solid;">Item Description</th>
				<th style="width:5% !important;text-align:center;border:1px solid; white-space: nowrap">HSN Code</th>
				<th style="width:5% !important;text-align:center;border:1px solid;">Qty</th>
				<th style="width:5% !important;text-align:center;border:1px solid;">Unit</th>
				<th style="width:10% !important;text-align:center;border:1px solid;">Unit Price</th>
				<th style="width:15% !important;text-align:center;border:1px solid;">Total Price</th>
			</tr>
			
		';
	}
	$html.='<tr style="border:1px solid;border-left:1px solid;border-right:1px solid;border-top:none;border-bottom:none;">
		<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;width:53% !important;border-top:none;">
			<strong>'.$trn_rel['product_name'].'</strong><br/>
			'.$product_desc.'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;">'.$trn_rel['product_hsn'].'</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;">
			'.$trn_rel['product_qty'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;width:10% !important;border-top:none;">'.$trn_rel['unit_name'].'</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;width:15% !important;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=indian_number($trn_rel['product_rate'],2);
		}
			
		$html.='</td>
		<td style="text-align:right;border:1px solid;vertical-align:top;width:5% !important;border-top:none;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=indian_number($trn_rel['product_amount'],2);
		}
			
		$html.='</td>
	
    	</tr>
    	';
    		$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
    	if($trn_rel['act_amt_flag']!='1'){
    		$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
    	}
    	if($cnt==$p){
    	     $html.='
    	     	<tr>
		<td  colspan="2"; style="text-align:left;border:1px solid;color:Black; font-weight: bold;">Total(In Words): '. ucfirst(convert_number_to_words_new($ttl_amt)).'</td>
		<td style="text-align:center;border:1px solid; font-weight: bold;">Total</td>
		<td style="text-align:center;border:1px solid;">
			'.$ttl_qty.'
		</td>
		<td style="text-align:center;border:1px solid; font-weight: bold;"></td>
		<td style="text-align:center;border:1px solid; font-weight: bold;"></td>
		<td style="text-align:right;border:1px solid;font-weight: bold;">
			'.indian_number($ttl_amt,2).'
		</td>
	</tr>';
$html.='
<tr style="height:120px;">
	<td colspan="2" style="height:120px;text-align:left;vertical-align:top;border-left:1px solid;border-bottom:1px solid;font-weight: bold;">Remark:'.($rel['quot_remark'] ? $rel['quot_remark'] : '').'</td>
	<td colspan="5" style="text-align:center;vertical-align:bottom;border-bottom:1px solid;border-right:1px solid;font-weight: bold;">Authorised Signatory</td>
</tr>
    	     </table>
    	       </div>
    	     <div style="clear:both;"></div>';
    	}
    $pcount++;
    		if($pcount==6 && $cnt!=$p){
    		     $pcount=1;
    		   $html.='
    		   <tr style="border-left:1px solid;border-right:1px solid;border-top:none;">
    		   <td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;"></td>
		<td style="text-align:left;border:1px solid;vertical-align:top;width:53% !important;border-top:none;">
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;"></td>
		<td style="text-align:center;border:1px solid;vertical-align:top;width:5% !important;border-top:none;">
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;width:10% !important;border-top:none;"></td>
		<td style="text-align:right;border:1px solid;vertical-align:top;width:15% !important;">
		</td>
		<td style="text-align:right;border:1px solid;vertical-align:top;width:5% !important;border-top:none;">
		</td>
    		    </tr>
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


$html.='</div>
<!--page1 end-->';


/* Get Bom of Product Start */
if($rel['with_bom_flag']=='1'){
$html.='<center class="nextpage"></center>
			<h3 style="text-align:center;">BOM</h3>
				<div>
					<table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3">
				
					<thead>
					<tr height="30px">					
						<th  width="5%" style="text-align:center;border:1px solid;border-top:none;">
							<strong>SR. NO.</strong>
						</th>
						<th width="45%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Description</strong></th>
						<th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Item Type</strong></th>
						<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Qty</strong></th>
						<th width="5%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Unit</strong></th>
						<!--<th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Last Purchase Price</strong></th>-->
						<th width="20%" style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;"><strong>Process</strong></th>
						
					</tr>
				</thead>
					
					<tbody>';

					$trn_qry1="select trn.*,pro.product_name,unit.unit_name,bom.bom_id from tbl_quotation_trn as trn 
					left join product_mst as pro on pro.product_id=trn.product_id
					left join unit_mst as unit on unit.unitid=trn.unitid
					left join tbl_bom as bom on bom.bom_product=trn.product_id
					where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
					$trn_qry_rs1=$dbcon->query($trn_qry1);
					$p=1;$ttl_amt=0;$ttl_qty=0;
					$cnt1=mysqli_num_rows($trn_qry_rs1);
					while($trn_rel1=mysqli_fetch_assoc($trn_qry_rs1)){
						
						/*  $trn_qry22="select * from tbl_bom as trn 
						where trn.bom_status=0 and trn.bom_product=".$trn_rel1['product_id'];
						$trn_qry_rs222=$dbcon->query($trn_qry22);
						while($trn_rel22=mysqli_fetch_assoc($trn_qry_rs222)){
							//$bom_id=$trn_rel22['bom_id'];
						} */
						
						
						$qry="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
						left join product_mst as pro on pro.product_id=bom_trn.product_id
						left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
						left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
						where bom_trn_status=0 and bom_id=".$trn_rel1['bom_id'];	
					//$result1=$dbcon->query($query1);
							//echo $qry;
							$result1=$dbcon->query($qry);		
							$i=1;$total=0;$discount=0;
							$_SESSION['bom_tot']=0;
							$cnt1=mysqli_num_rows($result);
							$cnt=1;
							while($rel1=mysqli_fetch_assoc($result1))
							{
								$pty=get_product_type_by_id($dbcon,$rel1['product_type']);
								//$gla=get_last_purchase($dbcon,$rel1['product_id']);
					
								$html.='<tr>
										<td style="border-right:1px solid; border-bottom:1px solid;" >'.$i.'</td>
										<td style="border-right:1px solid; border-bottom:1px solid;" >'.$rel1["product_name"].'</td>
										<td style="border-right:1px solid; border-bottom:1px solid;" >'.$pty.'</td>
										<td style="border-right:1px solid; border-bottom:1px solid;" >
											'.$rel1["product_base_qty"].' '.$rel1["base_unit_name"].'
										</td>
										<td style="border-right:1px solid; border-bottom:1px solid;" >
											'.$rel1["base_unit_name"].'
										</td>
										<!--<td style="border-right:1px solid; border-bottom:1px solid;">
											'.$gla.'
										</td>-->
										<td style="border-right:1px solid; border-bottom:1px solid;" >';
											$query111="select mst.*,p.process_name from tbl_product_process as mst 
											left join process_mst as p on p.process_id=mst.process_id where mst.status = 0 and mst.product_id=".$rel1['product_id']." order by process_priority";
											$result111=$dbcon->query($query111);
											$cnt11=mysqli_num_rows($result111);
											if($cnt11>0){ 
												$html.='<table style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
												<tr>
													<th style="border:0.5px #444 solid;text-align:center;" >Priority</th>
													<th style="border:0.5px #444 solid;text-align:center;" >Process Type</th>
													<th style="border:0.5px #444 solid;text-align:center;" >Process Name</th>
												</tr>';
											while($rel111=mysqli_fetch_assoc($result111)){ 
												if($rel111['process_type']==1){
													$process_type="Inhouse";
												}else{
													$process_type="Outside";
												}
												$html.='<tr>
													<td style="border:0.5px #444 solid;text-align:center;" >'.$rel111["process_priority"].'</td>
													<td style="border:0.5px #444 solid;text-align:center;" >'.$process_type.'</td>
													<td style="border:0.5px #444 solid;text-align:center;" >'.$rel111["process_name"].'</td>
												</tr>';
											} 
											$html.='</table>';
										}
									$html.='</td>
									</tr>';
	
			$html.=quotation_bom_show_print($dbcon,$rel1['p_bom_id'],$rel1['product_base_qty'],$i,$call,$space);
				
		  $i++;  }
		
						
					}

$html.='</table>
		</div>
		';
}
/* Get Bom of product end */

/* Get Terms And Condition Start */
 $terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
$terms_qry_rs=$dbcon->query($terms_qry);
if(mysqli_num_rows($terms_qry_rs)){
$html.='<center class="nextpage"></center>
<h3 style="text-align:center;">Terms & Conditions for Sales Quotation No : <u>'.$rel['quotation_no'].'</u></h3>
<div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
	$t=1;
	while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
	    $string=(nl2br($term_rel['tc_details']));

		$html.='<tr>
			<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">'.$t.'</td>
			<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px;">'.$term_rel['tc_name'].'</td>
			<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
		</tr>';
		$t++;
	}
$html.='</tbody></table></div>';	
}
/* Get Terms And Condition Start */

/* Check Annexure Attachments Start */
if(trim($rel['quot_annex_content'])){
	$html.='<center class="nextpage"></center>';
	$html.='<div class="quot_annex_content_div">'.$rel['quot_annex_content'];
	$html.='</div>';
}
/* Check Annexure Attachments End */

$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
</body>
</html>';
// echo $html;exit;
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','25','10','1','1');
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
// 		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		
		/*
		 *  Created by Sanat ::  12-08-2021
		 	Comment ::  added type_mail for create pdf and attach in send_quotation_mail
		 	START
		*/
		if($type_mail){
		    $mpdf->Output('../quotation'.$quotation_id.'.pdf','f');
		    echo 'quotation'.$quotation_id.'.pdf';
		}else{
		    $mpdf->Output();
		    return 'quotation'.$quotation_id.'.pdf';
		}

		/*
		 *  Created by Sanat ::  12-08-2021
		 	END
		*/
		ob_clean();
		return 'quotation'.$quotation_id.'.pdf';
	}
	
?>