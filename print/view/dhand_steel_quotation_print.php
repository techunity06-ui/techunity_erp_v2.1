<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
            QUOTATION_SLUG_PRINT
        ]);
        
if(!in_array(QUOTATION_SLUG_PRINT,$bulkAccessArray)){
    header("Location: ".DOMAIN."permission_access");
}
        
$quotation_id = $_REQUEST['id'];	
$type='pdf';
if(strtolower($type) == 'pdf') {
//Quotation Data
$query="select quot.*,per.c_con_fname,per.c_con_lname,per.c_con_mobile,per.c_con_email,cust.cust_name,cust.cust_gst, cust.cust_email, cust.cust_mobile, inq.inquiry_no,inq.inquiry_date from tbl_quotation as quot
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
//$header ='<img src="'.DOMAIN_F.LOGO.$comp_rel["logo"].'" style="width:3.27in;padding-top:25px;" />'; 
$header = '<table  class="maintable headermain " style="border-radius: 10px;border-collapse: separate;border-color: black;margin-top: 21px; padding: 14px 0 0;border: 1px solid;" id="table_head" width="100%">
    <tr style="border:none;">
        <td style="border:none;">
            <div width="30%" style="float:left;width: 250px;">
                <img src="'.ROOT.LOGO.'Final.jpg"  style="width:210px;height:100px;"/>
            </div>
        </td>
        <td style="border:none;">
            <div width="50%" style="float:right; width: 300px;">
                <h2 style="margin-bottom:20px;" align="left">'.$comp_rel['company_name'].'</h2>
                <h5 align="left" style="padding-top:8px;">'.$comp_rel['logo_content'].'</h5>
                <h5 style="font-size:19px; margin-bottom:0px;" align="left">'.$comp_rel['address'].'</h5>
            </div>
        </td>
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
    <div style="text-align:center">'.$header.'</div>
</htmlpageheader>
<!--<htmlpagefooter name="otherpages_footer" style="display:none">
    <div style="text-align:center">'.$footer.'</div>
</htmlpagefooter>-->
<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
<div>
	<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr>
			<td colspan="3" style="text-align:center;font-size:15px;font-weight:bold; border-top: none !important;"> 
				Quotation'.$approve_status.'
			</td>
		</tr>
		<tr>
			<td rowspan="5" style="text-align:left;vertical-align:top;border:1px solid;width:50%;"> 
				<strong>'.$comp_rel['company_name'].'</strong><br/>
				'.$comp_rel['address'].'<br/>
				<!--'.$comp_rel['city_name'].', '.$comp_rel['state_name'].', '.$comp_rel['country_name'].'<br/>-->
				Phone No : '.$comp_rel['contact_no'].'<br/>
				GST No: '.$comp_rel['vatno'].'
			</td>
			<td style="text-align:left;border:1px solid;width:20%;"> 
				Quotation No
			</td>
			<td style="text-align:left;border:1px solid;width:30%;"> 
				<strong>'.$rel['quotation_no'].'</strong>
			</td>
		</tr>
		<tr>
			<td style="text-align:left;border:1px solid;"> 
				Quotation Date
			</td>w
			<td style="text-align:left;border:1px solid;"> 
				'.date("d-M-Y",strtotime($rel['quotation_date'])).'
			</td>
		</tr>
		<tr>
			<td style="text-align:left;border:1px solid;"> 
				Sales Person
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'
			</td>
		</tr>
		<tr>
			<td style="text-align:left;border:1px solid;"> 
				Person Contact No
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$rel['c_con_mobile'].'
			</td>
		</tr>
		<tr>
			<td style="text-align:left;border:1px solid;"> 
				Person Email
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.(strtolower($rel['c_con_email'])).'
			</td>
		</tr>
		<tr>
			<td rowspan="4" style="text-align:left;vertical-align:top;border:1px solid;"> 
				To,<br/>
				<strong>'.$rel['cust_name'].'</strong><br/>
				'.(nl2br($rel['quot_address'])).'<br/>
				GST No:-'.$rel['cust_gst'].'
			</td>
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
				'.date("d-M-Y",strtotime($rel['inquiry_date'])).'
			</td>
		</tr>
		<tr>
			<td style="text-align:left;border:1px solid;"> 
				Email
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.(strtolower($rel['cust_email'])).'
			</td>
		</tr>
		<tr>
			<td style="text-align:left;border:1px solid;"> 
				Mobile No
			</td>
			<td style="text-align:left;border:1px solid;"> 
				'.$rel['cust_mobile'].'
			</td>
		</tr>
	</table>
	<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
			<tr>
				<th style="width:2%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
				<th style="width:50%;text-align:center;border:1px solid;">Item Description</th>
				<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
				<th style="width:5%;text-align:center;border:1px solid;">Unit</th>
				<th style="width:10%;text-align:center;border:1px solid;">Unit Price</th>
				<th style="width:15%;text-align:center;border:1px solid;">Total Price</th>
			</tr>
		</thead>
		<tbody>';
$trn_qry="select trn.*,pro.product_name,unit.unit_name from tbl_quotation_trn as trn 
left join product_mst as pro on pro.product_id=trn.product_id
left join unit_mst as unit on unit.unitid=trn.unitid
where trn.quot_trn_status=0 and trn.quotation_id=".$rel['quotation_id'];
$trn_qry_rs=$dbcon->query($trn_qry);
$p=1;$ttl_amt=0;$ttl_qty=0;
$cnt=mysqli_num_rows($trn_qry_rs);
while($trn_rel=mysqli_fetch_assoc($trn_qry_rs)){
	
	$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$p.'</td>
		<td style="text-align:left;border:1px solid;vertical-align:top;">
			<strong>'.$trn_rel['product_name'].'</strong><br/>
			'.nl2br($trn_rel['product_desc']).'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$trn_rel['product_qty'].'
		</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">'.$trn_rel['unit_name'].'</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=$trn_rel['product_rate'];
		}
			
		$html.='</td>
		<td style="text-align:center;border:1px solid;vertical-align:top;">';
		if($trn_rel['act_amt_flag']=='1'){
			$html.="Extra At Actual";
		}
		else{
			$html.=$trn_rel['product_amount'];
		}
			
		$html.='</td>
	</tr>';
	$ttl_qty=$ttl_qty+$trn_rel['product_qty'];
	if($trn_rel['act_amt_flag']!='1'){
		$ttl_amt=$ttl_amt+$trn_rel['product_amount'];
	}
	
	$p++;
}
$pr=10-$cnt;
for($j=0; $j<$pr; $j++)
{
    $html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
		<td style="border:none;border-left:1px solid;border-right:1px solid;height:40px;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
		<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
	</tr>';
}

	$html.='<tr>
		<td colspan="2" style="text-align:center;border:1px solid;">Total</td>
		<td style="text-align:center;border:1px solid;">
			'.$ttl_qty.'
		</td>
		<td style="text-align:center;border:1px solid;"></td>
		<td style="text-align:center;border:1px solid;"></td>
		<td style="text-align:center;border:1px solid;">
			'.$ttl_amt.'
		</td>
	</tr>';
$html.='</tbody></table>
<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
<tr style="height:80px;">
	<td style="height:80px;text-align:center;vertical-align:bottom;border-left:1px solid;border-bottom:1px solid;">Prepared By</td>
	<td style="text-align:center;vertical-align:bottom;border-bottom:1px solid;">Checked By</td>
	<td style="text-align:center;vertical-align:bottom;border-bottom:1px solid;border-right:1px solid;">Authorised Signatory</td>
</tr>
</table>

	<div style="clear:both;"></div>
</div>
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
<h3 style="text-align:center;">Commercial Terms</h3>
<div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
	$t=1;
	while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
	    $string=(nl2br($term_rel['tc_details']));

		$html.='<tr>
			<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">'.$t.'</td>
			<td width="25%" style="width:25%;text-align:center;border:1px solid;padding:5px;">'.$term_rel['tc_name'].'</td>
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
		//$mpdf->SetHTMLFooter($footer);
		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
                $mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$mpdf->Output('../../view/upload/quotation_pfd/quotation'.$quotation_id.'.pdf','f');
		ob_clean();
		return 'quotation'.$quotation_id.'.pdf';
	}
	
?>
		