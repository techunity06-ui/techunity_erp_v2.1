<?php 
session_start();
include("../../config/config.php");
	ob_start();
//$pdf_upload=quotation_pdf(8,"pdf",$dbcon);
//error_reporting(E_ALL);
/*function quotation_pdf($id,$type,$dbcon)
{*/

	//$id = $dbcon->real_escape_string($id);


	$query="select quot.*,cper.cust_contact_person_name,cper.cust_contact_person_email,cper.cust_contact_person_no,acmst.acc_name,acmst.acc_number,acmst.branch_name,acmst.acc_ifsc_code,acmst.acc_swift_code,bankmst.bank_name,country.country_name,state.state_name,state.gst_state_code, city.city_name,cust.stateid,cust.countryid,cust.company_name, cust.cust_address, cust_pincode,cust.cust_name,cust.cust_mobile,cust_email,gst_no,inq.inquiry_no,inq.inquiry_date ,cur.currency_short_code 
	from tbl_quotation as quot
	inner join tbl_customer as cust on cust.cust_id=quot.cust_id
	left join country_mst as country on country.countryid=cust.countryid
	left join state_mst as state on state.stateid=cust.stateid
	left join city_mst as city on city.cityid=cust.cityid
	left join account_mst as acmst on acmst.acc_id=quot.acc_id
	left join bank_mst as bankmst on bankmst.bankid=acmst.bankid
	left join currency_mst cur on cur.currency_id	=quot.currency_id
	left join tbl_cust_contact_person as cper on cper.cust_contact_person_id=quot.cust_contact_person_id
	left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id 
	where quotation_id=".$_SESSION['quotation_id'];
	$rel=mysqli_fetch_assoc($dbcon->query($query));
	
	if(!empty($rel['cust_consignee_id']))//consignee
	{	
		$consignee="select * from tbl_cust_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid 
		where cust_consignee_id=".$rel['cust_consignee_id'];
		$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
	}
	
	
	
	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	
	
					$procover="select GROUP_CONCAT(product.product_name SEPARATOR ';')as productname  FROM `tbl_quotationtrn` as trn 
					left join tbl_product as product on product.product_id=trn.product_id 
					where quotationtrn_status=0 and quotation_id=".$rel['quotation_id'];
					$coverlatorproduct=mysqli_fetch_assoc($dbcon->query($procover));
					$procover=$coverlatorproduct['productname'];
					// Input string 
					$coverlator_content = $set_head['coverlator_content'];
					
					// Array containing search string  
					$searchVal = array("[product_name]"); 
					
					// Array containing replace string from  search string 
					$replaceVal = array($procover); 
					
					// Function to replace string 
					$covercontent = str_replace($searchVal, $replaceVal, $coverlator_content);
					
	
	$type="pdf";
	$hed="header.png";
$header ='
 <table  cellpadding="0" cellspacing="0" width="100%"   id="table_head" class="maintable"  style="border-collapse:collapse;margin-left:0px !important;margin-top:150px !important;">
					<thead>	
					<tr>
					<td width="100%" style="border:none;padding-left:0;padding-right:0;"> 
							<img src="'.ROOT.LOGO.$hed.'" style="width:100%;height:120px;"/>
							</td>
					</tr>
					
					
				</thead>
					</table>
					';
    if(isset($type)== "pdf") {
		$type = $dbcon -> real_escape_string($type);
	}
	else {
		die('<h1> ERROR </h1>');
	}
      if(strtolower($type) == 'pdf') {
  

$html ='<html>
				<head>					
					<title>Quotation - '.$rel['company_name'].'</title>
<style type="text/css">

.page{
	width:8.27in;
	height:10.69in;
}
/*
table {
width:100%;	
border-width: 0 0 1px 1px;
border-spacing: 0;
border-collapse: collapse;

}
th, td {
margin: 0;
padding: 4px;
border-width: 1px 1px 0 0;

text-align: center;
}*/
table {
width:100%;
}
th {
font-weight:bold;
}

td ul
{
  margin: 0px 0px;
}

p {margin: 10px 0px;}

</style>
</head>
<body>
<table class="headertbl">
	<!--<thead id="thd">
		<tr>
			<th colspan="8" style="padding:0px !important;">
				<table style="font-size:10px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
					<tr>
						<td colspan="5" style="text-align:center;padding-left:0px !important;"> 
							<img src="'.ROOT.LOGO."header.png".'"   style="width:100%"/>
						</td>
					</tr>
				</table>
			</th>
		</tr>
	</thead>-->
	<tbody>';
	if($rel['quot_coverletter'] == '1'){
	$html.='<tr>
			<td colspan="5" style="text-align:left;padding-top:50px !important;padding-left:0px !important;"> 
				TO,<br/>
				<strong>'.$rel['company_name'].'</strong>
				<br/>
				<span style="font-weight:normal;">'.$rel['cust_address'].'
					<br>
					'.$rel['city_name'].', '.$rel['state_name'].' , '.$rel['country_name'].'
				</span>
				
			</td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:left;padding-left:0px !important;padding-top:15px !important;"> 
				<strong>Kind Attn : </strong><span style="font-weight:normal">MR. '.$rel['cust_contact_person_name'].'</span>
			</td>
		</tr>	
		<tr>
			<td colspan="5" style="text-align:left;padding-left:0px !important;padding-top:15px !important;"> 
				<strong>E-mail : </strong><span style="font-weight:normal">
				'.$rel['cust_contact_person_email'].'</span>
			</td>
		</tr>	
		
		<tr>
			<td colspan="5" style="text-align:center;padding-left:0px !important;padding-top:30px !important;"> 
				<strong>Subject : '.$rel['ref_no'].' </strong><span style="font-weight:normal"></span>
			</td>
		</tr>	
		
		<tr>
			<td colspan="5" style="text-align:left;padding-left:0px !important;padding-top:40px !important;"> 
				<strong>Dear Sirs.</strong>
			</td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:left;padding-left:0px !important;padding-top:20px !important;font-weight:normal">
				'.$covercontent.'
		</td>
		</tr>
		<tr>
			<td colspan="5" style="text-align:left;padding-left:0px !important;padding-top:70px !important;"> 
				<strong>For,</strong> '. $set_head['company_name'] .' (an ISO 9001:2015 Certified Company)
			</td>
		</tr>
		<tr>
			<td colspan="" style="text-align:left;padding-left:0px !important;padding-top:20px !important;"> 
				<img src="'.ROOT.LOGO."sign.png".'" />
				  
			</td>
			<td colspan="4" style="text-align:left;padding-left:0px !important;padding-top:20px !important;"> 
				
			</td>
		</tr>
	<tr>
			<td colspan="5" style="text-align:left;padding-left:0px !important;padding-top:20px !important;">'; 
			
									
				if($set_head["contact_no"]){
					$html.='<br>Hardik Gohel <strong>('.$set_head["contact_no"].')</strong>';
						}
				$html .='<h5>';
					
					
	$html.='</strong>
		</td>
		</tr>';
	}
	if($rel['print_template'] == '0'){
		$html.='<tr id="rmvborder" >
										<td colspan="8" style="text-align:center"> 
										
											<strong class="typetitle" style="font-size:20px;">
												Quotation
											</strong>
										</td>
									</tr>
									<tr id="rmvborder">
										<td width="70%"  rowspan="5" colspan="2" style="vertical-align:top;text-align:left">
											
											<span align="center" style="font-size:18px;">
											<strong>'.$set_head['company_name'].'</strong></span><br/>
											<span align="center" style="font-size:15px;">
											'.$set_head['address'].'</span><br/>
											
											<span align="center" style="font-size:12px;">';
											if($set_head['website']){ 
											$html.='Email: '.$set_head["website"].'';
											}
											$html.='<br/>';
											if($set_head['contact_no']){
												$html.='(M) '.$set_head['contact_no'].'';
												}
											$html.='</span><br/>
											<span align="center" style="margin-top:0px;font-size:15px;">';
											if($set_head['company_website']){
												$html.='Website:'. $set_head['company_website'].'';
												}
												$html.='</span>
											
											
										</td>
										
										<td colspan="2" width="30%"  style="vertical-align:top;white-space:nowrap;font-size:12px;text-align:right;" id="rmvborder">	
											Quotation No  :
											<strong>'.$rel['quotation_no'].'</strong>
										</td>
										<tr id="rmvborder">
										
										<td colspan="2"    style="vertical-align:top;white-space:nowrap;font-size:12px;text-align:right;" id="rmvborder">
											Quotation Date :		
											<strong>'. date('d/m/Y',strtotime($rel['quotation_date'])).'</strong>	
										</td>
										
									</tr>
									<tr id="rmvborder">
										
										<td colspan="2"    style="vertical-align:top;white-space:nowrap;font-size:12px;text-align:right;" id="rmvborder">
											Inquiry No :		
											'.$rel['inquiry_no'].'
										</td>
										
									</tr>
									<tr id="rmvborder">
										
										<td width=""  colspan="2" style="vertical-align:top;white-space:nowrap;font-size:12px;text-align:right;">
											
											Inquiry Date:
											'.date('d/m/Y',strtotime($rel['inquiry_date'])).'
										</td>
										
									</tr>
									<tr id="rmvborder">
										
										<td width="" colspan="2"  style="vertical-align:top;white-space:nowrap;font-size:12px;text-align:right;">
											
											Contact person:
											'.$rel['cust_contact_person_name'].'
										</td>
										
									</tr>
									</tr>
										<td colspan="3" style="padding-top:10px !important" ></td>
									</tr>
									<tr id="rmvborder">
										<td colspan="5" style="padding-top:25px !important" ></td>
									</tr>
									<tr id="rmvborder">
										<td colspan="2" style=""><strong>Party Name : </strong></td>
										<td ></td>
										<td colspan="2"  style="text-align:right" ><strong>Consignee Name : </strong></td>
										
									</tr>
									<tr id="rmvborder">
										<td width="42%" height="50%" colspan="2" rowspan="" style="vertical-align:top;">
											
											<strong>'.$rel['company_name'].'</strong>
											<br/>
											<span style="font-weight:normal;">
											'.$rel['cust_address'].'
												<br>
												'.$rel['city_name'].', .'$rel['state_name'].',  <br>'.$rel['country_name'].'';
											if(!empty($rel['cust_pincode']))
													{	
													$html.='-  '.$rel['cust_pincode'].'';
												 }
												$html.=' </span>';
												
													if($rel['countryid']=='101'){
														$typ_lbl='GSTIN';
													}
													/*else{
														$typ_lbl='URP';
													}*/
												
												$html.='<br/><strong>';
													if(!empty($typ_lbl)){
														$html.=''.$typ_lbl.': 
												}
													
											'.$rel['gst_no'].'</strong>
												<br>	Contact Person :
												'.$rel['cust_contact_person_name'].'(.'$rel['cust_contact_person_no'].')
												
										</td>
										
										<td ></td>	
										<!--<td colspan="2" width="53.7%"   rowspan="" style="vertical-align:top;text-align:right">';
											
											if($rel['cust_consignee_id']){ 
												
												$html.='<strong>
												'.$cons_data['cons_company_name'].'</strong>
												<span style="font-weight:normal;">   <br>
												'.$cons_data['cons_consignee_address'].'
													<br>
													'.$cons_data['city_name'].', '.$cons_data['state_name'].', <br>'.$cons_data['country_name'].'';
													if(!empty($cons_data['cons_cust_pincode']))
														{	
														$html.='- '.$cons_data['cons_cust_pincode'].'
													 } </span>
													<br>';
													$html.=' Mobile no : '.$cons_data['cons_cust_mobile'].'<br/>
													}
													else{
													<strong>'.$rel['company_name'].'</strong>
													<br/>
													<span style="font-weight:normal;">
													
													'.$rel['cust_address'].'
													
														<br>
														'.$rel['city_name'].', '.$rel['state_name'].',  <br>'.$rel['country_name'].'';
														 if(!empty($rel['cust_pincode']))
															{	
															-  =$rel['cust_pincode']
														 } $html.='</span>';
														
															if($rel['countryid']=='101'){
																$typ_lbl='GSTIN';
															}
															/*else{
																$typ_lbl='URP';
															}*/
														
														$html.='<br/><strong>';
														if(!empty($typ_lbl)){
															$html.=''.$typ_lbl.': 
														} 
														'.$rel['gst_no'].'';
														$html.='</strong>
														<br>
														Contact Person :
														'.$rel['cust_contact_person_name'].'('.$rel['cust_contact_person_no'].')
															}
										</td>-->
										
									</tr>
									<tr id="rmvborder">
										<td colspan="5" style="padding-top:10px !important"></td>
									</tr>';
	}
	$html.='</tbody>
</table>
</body>
</html>';


		echo $html;
	exit;
	$file_name = $rel['cust_id'].'_'.$rel['quotation_no'].'_'.date("d/m/Y",strtotime($rel['quotation_date'])).'.pdf';
	$file_name=str_ireplace("/","-",$file_name);	
		
		ob_end_clean();
		include("../../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','55','35');

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
		$mpdf->WriteHTML($html);
		//$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pdf_file/'.$file_name,'f');
		$mpdf->Output($_SESSION['file_name'].$_SESSION['invoice_no'].'.pdf', 'D');
		//ob_clean();
		//return $file_name;
		
}
//}

?>
