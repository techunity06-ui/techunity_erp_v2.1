<?php 
 $res_id = $_REQUEST['id'];
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';
$res_id = $_REQUEST['id'];
$type='pdf';
if(strtolower($type) == 'pdf') {


 $s_ql = "SELECT qc.material_trn_id,qc.product_id,tsr.*,p.product_name,user.user_name,p.product_id as ppid,rp.product_name as rpname,ap.batch_no as fbatch,qc.batch_no as rbatch,p.product_icode,rel.issue_no,rel.issue_date,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,users.user_name,req.rp_id as req_id, pr.process_name, st.doc_no 
				FROM tbl_store_request_aprv_log as tsr 
				left join tbl_allocate_process as ap on ap.p_id=tsr.p_id 
				left join tbl_reserve_stock as trsh on trsh.p_id=ap.p_id 
				left join tbl_stock_trn as tsth on tsth.stock_id=trsh.stock_id 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join tbl_store_release as rel on rel.release_id=tsr.store_release_id 
				left join process_mst as pr on pr.process_id=ap.process_id 
				left join tbl_request_product req on req.rp_id=ap.p_ref_id 
				left join tbl_set_main_process as smain on smain.sp_id=req.sp_id 
				left join tbl_store_order_min_max as st on smain.store_order_id=st.order_id 
				left join users as users on users.user_id=tsr.request_user_id 
				left join (select q.batch_no,q.p_id,q.product_id,q.material_trn_id  from tbl_material_release_trn as q where q.status !=2 ) as qc on qc.p_id=tsr.p_id
				left join product_mst as rp on rp.product_id=qc.product_id 
				left join users as user on users.user_id=tsr.user_id
				left join unit_mst as umst on umst.unitid=ap.process_unit where tsr.company_id=".$_SESSION['company_id']." and tsr.store_release_id=".$res_id."  order by tsr.store_aprv_log_id,qc.material_trn_id desc limit 1";
				
$q=$dbcon->query($s_ql);
$rel=brp_mysqli_fetch_array($q)	;			
	
	
	
//$header =get_header($dbcon,'text-align: center','100%','70px');
$html.='<html>
<head>					
<title>MS JOB CARD </title>

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
	.blueHeading {
		color: #365f91;
	}

	</style>
	</head>
	<body>
	<!--Show Logo in other pages-->
	<!--<htmlpageheader name="otherpages" style="display:none">
	<div style="text-align:center">'.$header.'</div>
	</htmlpageheader>-->
	<!--<htmlpagefooter name="otherpages_footer" style="display:none">
	<div style="text-align:center">'.$footer.'</div>
	</htmlpagefooter>-->
	<!--<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>-->
	<div>
	<table>

	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; ">
	<td colspan="15" style="text-align:center;font-size:18px;font-weight:bold;padding-bottom:0px;">JOB CARD</td>
	</tr>
	
	<tr>
	<td colspan="4" style="text-align:left; width:1.5in; "><b>WORK ORDER NO:</b> '.$rel['work_order_no'].' </td>
	<td colspan="6" style="text-align:center; "><b>SMPL</b></td>
	<td colspan="5" style="text-align:left; width:2.5in;"><b>JOB CARD NO.:</b> '.$rel['job_card_no'].'</td>
	</tr>
	<tr>
	<td colspan="4" style=" text-align:left;"><b>CODE NO:</b> '.$rel['product_icode'].' </td>
	<td colspan="6" style=" text-align:center;"><b>SMIT MEDIMED PVT. LTD.</b></td>
	<td colspan="5" style=" text-align:left;"><b>HEAT NO:</b> '.$rel['rbatch'].'</td>
	</tr>
	<tr>
	<td colspan="4" style=" text-align:left;"><b>QTY.:</b> '.$rel['release_qty'].'</td>
	<td colspan="2" style=" text-align:left;"><b>Rev No: 02</b></td>
	<td colspan="2" style=" text-align:left;"><b>21.05.2022</b></td>
	<td colspan="2" style=" text-align:left;"><b>F:PRD:04</b></td>
	<td colspan="5" style=" text-align:left;"><b>BATCH/LOT NO.:</b><br>'.$rel['fbatch'].' </td>
	</tr>
	
	<tr>
	<td colspan="4" style=" text-align:left;"><b>PREPARED BY.:</b> '.find_user_name($dbcon,$rel['user_id']).' </td>
	<td colspan="6" style=" text-align:left;"><b>RM DESCRIPTION :</b> <br>'.$rel['rpname'].'</td>
	<td colspan="5" style=" text-align:left;"><b>DWG NO :</b></td>
	</tr>
	<tr>
	<td colspan="4" style=" text-align:left;"><b>ISSUE DATE:</b>'.$rel['issue_date'].' </td>
	<td colspan="6" style=" text-align:left;"><b>PRODUCT NAME:</b><br>'.$rel['product_name'].'</td>
	<td colspan="5" style=" text-align:left;"><b>SIZE:</b><p><b>DOCUMENT NO : </b>'.$rel['doc_no'].'</p></td>
	</tr> 
	
	<tr style="border-top:1px solid; border-left:1px solid; border-right:1px solid; background-color:#b3b3b3; ">
	<td colspan="15" style="text-align:Left;font-size:18px;font-weight:bold;padding-bottom:0px;">Customer Name & Specification :</td>
	</tr></table>';
	//////////////////////////////////////////Page - 1 start//////////////////////////////////////////////
	
	$html.='<table>
	<tr>
	<td colspan="2" style=" text-align:left;"><b>1</b> </td>
	<td colspan="11" style=" text-align:Center;"><b>STAGE-1</b></td>
	</tr>';
	$blnk = 7;
	$html.='<tr>
	<td colspan="2" rowspan="'.$blnk.'" style=" text-align:left;"> </td>
	<td colspan="" style=" text-align:Center;"><b>Date</b></td>
	<td colspan="" style=" text-align:Center;"><b>Operation</b></td>
	<td colspan="2" style=" text-align:Center;"><b>M/C Code</b></td>
	<td colspan="" style=" text-align:Center;"><b>Start Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>OK Qty.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Rej Qty.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Operator</b></td>
	<td colspan="2" style=" text-align:Center;"><b>Shop Incharge</b></td>
	
	</tr>';
	$blnk = 6;
	$height_td=22;
	
	for($i=1; $i<=$blnk; $i++)
	{
		if($i !=6)
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
		else
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Remarks</td>
		<td colspan="11" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
	}
	
	/////////////////////////Stage -2//////////////////////
	$html.='<tr>
	<td colspan="2" style=" text-align:left;"><b>2</b> </td>
	<td colspan="11" style=" text-align:Center;"><b>STAGE-2  (Buffing / Polishing)</b></td>
	</tr>';
	$blnk = 7;
	$html.='<tr>
	<td colspan="2" rowspan="'.$blnk.'" style=" text-align:left;"> </td>
	<td colspan="" style=" text-align:Center;"><b>Date</b></td>
	<td colspan="" style=" text-align:Center;"><b>Operation</b></td>
	<td colspan="2" style=" text-align:Center;"><b>M/C Code</b></td>
	<td colspan="" style=" text-align:Center;"><b>Start Qty.</b></td>
	<td colspan="" style=" text-align:Center;"><b>OK Qty.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Rej Qty.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Operator</b></td>
	<td colspan="2" style=" text-align:Center;"><b>Shop Incharge</b></td>
	
	</tr>';
	$blnk = 6;
	
	for($i=1; $i<=$blnk; $i++)
	{
		if($i !=6)
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
		else
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Remarks</td>
		<td colspan="11" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
	}

	///////////////////////////////////////////////////////
	
	/////////////////////////Stage -3//////////////////////
	$html.='<tr>
	<td colspan="2" style=" text-align:left;"><b>3</b> </td>
	<td colspan="11" style=" text-align:Center;"><b>STAGE-3  (Q.C Before Polishing)</b></td>
	</tr>';
	$blnk = 7;
	$html.='<tr>
	<td colspan="2" rowspan="'.$blnk.'" style=" text-align:left;"> </td>
	<td colspan="" style=" text-align:Center;"><b>Date</b></td>
	<td colspan="" style=" text-align:Center;"><b>Parameters</b></td>
	<td colspan="" style=" text-align:Center;"><b>Inst code</b></td>
	<td colspan="" style=" text-align:Center;"><b>Req. Dim</b></td>
	<td colspan="" style=" text-align:Center;"><b>Tol.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Dim Min.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Dim Max.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Rej Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Qty</b></td>
	<td colspan="2" style=" text-align:Center;"><b>Q.C Incharge</b></td>
	
	</tr>';
	$blnk = 6;
	
	for($i=1; $i<=$blnk; $i++)
	{
		if($i !=6)
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
		else
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Remarks</td>
		<td colspan="11" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr></table>';
		}
	}
	//////////////////////////////////Page - 1 End////////////////////////////
	//////////////////////////////////Page - 2////////////////////////////////
	
	$html.='<table>
	<tr>
	<td colspan="2" style=" text-align:left;"><b>4</b> </td>
	<td colspan="11" style=" text-align:Center;"><b>STAGE-4 (Electro Polishing/Anodizing)</b></td>
	</tr>';
	$blnk = 4;
	$html.='<tr>
	<td colspan="2" rowspan='.$blnk.' style=" text-align:left;"> </td>
	<td colspan="" style=" text-align:Center;"><b>Date</b></td>
	<td colspan="" style=" text-align:Center;"><b>Process</b></td>
	<td colspan="2" style=" text-align:Center;"><b>M/C Code</b></td>
	<td colspan="" style=" text-align:Center;"><b>Start Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Rej Qty.</b></td>
	<td colspan="" style=" text-align:Center;"><b>R/W Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Operator</b></td>
	<td colspan="2" style=" text-align:Center;"><b>Shop Incharge</b></td>
	
	</tr>';
	$blnk = 2;
	$stage4 = array("Ultra-Cleaning 2","EP/Anodizing");
	for($i=0; $i<=$blnk; $i++)
	{
		
		if($i !=2)
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">'.$stage4[$i].'</td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
		else
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Remarks</td>
		<td colspan="11" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
	}
	
	///////////////////////////Stage - 5 /////////////////////////
	
	$html.='<tr>
	<td colspan="2" style=" text-align:left;"><b>5</b> </td>
	<td colspan="11" style=" text-align:Center;"><b>STAGE-5  (Q.C After Polishing)</b></td>
	</tr>';
	$blnk = 6;
	$html.='<tr>
	<td colspan="2" rowspan="'.$blnk.'" style=" text-align:left;"> </td>
	<td colspan="" style=" text-align:Center;"><b>Date</b></td>
	<td colspan="" style=" text-align:Center;"><b>Process</b></td>
	<td colspan="" style=" text-align:Center;"><b>Inst code</b></td>
	<td colspan="" style=" text-align:Center;"><b>Req. Dim</b></td>
	<td colspan="" style=" text-align:Center;"><b>Tol.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Dim Min.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Dim Max.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Rej Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Qty</b></td>
	<td colspan="2" style=" text-align:Center;"><b>Q.C Incharge</b></td>
	
	</tr>';
	$blnk = 5;
	
	for($i=1; $i<=$blnk; $i++)
	{
		if($i !=5)
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
		else
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Remarks</td>
		<td colspan="11" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
	}

	//////////////////////////////////////////////////////////////
	
	/////////////////////////Stage -6//////////////////////
	$html.='<tr>
	<td colspan="2" style=" text-align:left;"><b>6</b> </td>
	<td colspan="11" style=" text-align:Center;"><b>STAGE-6  (Lazer Marking & Final Q.A)</b></td>
	</tr>';
	
	$blnk = 5;
	
	$html.='<tr>
	<td colspan="2" rowspan="'.$blnk.'" style=" text-align:left;"> </td>
	<td colspan="" style=" text-align:Center;"><b>Date</b></td>
	<td colspan="" style=" text-align:Center;"><b>Process</b></td>
	<td colspan="2" style=" text-align:Center;"><b>M/C Code</b></td>
	<td colspan="" style=" text-align:Center;"><b>Time</b></td>
	<td colspan="" style=" text-align:Center;"><b>Start Qty.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Rej Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>R/W Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Operator</b></td>
	<td colspan="" style=" text-align:Center;"><b>Shop Incharge</b></td>
	
	</tr>';
	$blnk = 3;
	$stage6 = array("Lazer Marking","Ultra-Cleaning 3","Final Q.A");
	for($i=0; $i<=$blnk; $i++)
	{
		if($i !=3)
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">'.$stage6[$i].'</td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
		else
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Remarks</td>
		<td colspan="11" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
	}

	///////////////////////////////////////////////////////
	
	/////////////////////////Stage -7//////////////////////
	$html.='<tr>
	<td colspan="2" style=" text-align:left;"><b>7</b> </td>
	<td colspan="11" style=" text-align:Center;"><b>STAGE-7  (Packing & Labelling)</b></td>
	</tr>';
	$blnk = 5;
	$html.='<tr>
	<td colspan="2" rowspan="'.$blnk.'" style=" text-align:left;"> </td>
	<td colspan="" style=" text-align:Center;"><b>Date</b></td>
	<td colspan="" style=" text-align:Center;"><b>Process</b></td>
	<td colspan="2" style=" text-align:Center;"><b>M/C Code</b></td>
	<td colspan="" style=" text-align:Center;"><b>Time</b></td>
	<td colspan="" style=" text-align:Center;"><b>Start Qty.</b></td>
	<td colspan="" style=" text-align:Center;"><b>Rej Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>R/W Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Actual Qty</b></td>
	<td colspan="" style=" text-align:Center;"><b>Operator</b></td>
	<td colspan="" style=" text-align:Center;"><b>Shop Incharge</b></td>
	
	</tr>';
	$blnk = 3;
	$stage7 = array("Packing","Labelling","Q.A Verified");
	for($i=0; $i<=$blnk; $i++)
	{
		if($i !=3)
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">'.$stage7[$i].'</td>
		<td colspan="2" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
		else
		{
		$html.='<tr>
		<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Remarks</td>
		<td colspan="11" style=" height: '.$height_td.'px; text-align:Center;"></td>
		</tr>';
		}
	}
	
	
	$html.='<tr>
	<td colspan="15" style=" height: '.$height_td.'px; text-align:Center;"></td>
	</td>
	</tr>
	<tr>
	<td colspan="6" style=" height: 80px; text-align:left;vertical-align: top;"><b>Remarks:</b></td>
	</td>
	<td colspan="7" style=" height: 80px; text-align:left;vertical-align: top;"><b>Reviewed & Approved By Manager</b></td>
	</td>
	</tr>
	
	</table>';
	
	/////////////////////////////////////////////////////// QC Parameter //////////////////////////////////////////////////////////////
	 $html.='<div style ="page-break-after: always;"></div>';
	
	$html.='<div><table>
	<tr>
	<td colspan="5" style="text-align:Center;">QC Parameter</td>
	</tr>
	
	<tr>
	<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Process</td>
	<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Parameter</td>
	<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Base Value</td>
	<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Tolerance (+)</td>
	<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">Tolerance (-)</td>
	
	
	</tr>';
	
			 $qc_query = "select tpp.*,tqp.p_name,pm.process_name from tbl_product_parameter as tpp 
			left join tbl_qc_param as tqp on tqp.p_id=tpp.param_id
			left join process_mst as pm on pm.process_id=tpp.process_id
			
			where product_id=".$rel['ppid']."";
			$qc_query_arr=$dbcon->query($qc_query);
			while($rel=brp_mysqli_fetch_assoc($qc_query_arr))
				{	
	
					$html.='<tr>
					<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">'.$rel['process_name'].'</td>
					<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">'.$rel['p_name'].'</td>
					<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">'.$rel['param_value'].'</td>
					<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">'.$rel['tolerance_plus'].'</td>
					<td colspan="" style=" height: '.$height_td.'px; text-align:Center;">'.$rel['tolerance_minus'].'</td>
					</tr>';
				}
	$html.='</table></div>';
				 
	
	/////////////////////////////////////////////////////////////QC Parameter END////////////////////////////////////////////////////////////
	
	/* Get Terms And Condition Start */

	$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
	</body>
	</html>';
/*echo $header;*/
//echo $html;exit;
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4','0','calibri','10','10','10','10','1','1');
//		$mdf->SetFont('ProximaNova');
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
return 'Purchase Order'.$rel['purchaseorder_no'].'.pdf';
}	
?>