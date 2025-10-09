<?php 
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$include = '../../include/';

$type='pdf';
if(strtolower($type) == 'pdf') {
	$store_request_id=$dbcon->real_escape_string($_REQUEST['id']);
	
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$check_branch = check_branch('ap', $branch_id);
	
	$sql = "SELECT tsr.*,p.product_name,rel.issue_no,rel.issue_date,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,users.user_name,req.rp_id as req_id, pr.process_name, p.product_icode, tsth.batch_no as rbatch,ap.extra_stock,  req.rp_id 
		FROM tbl_store_request_aprv_log as tsr 
		left join tbl_allocate_process as ap on ap.p_id=tsr.p_id 
		left join tbl_reserve_stock as trsh on trsh.p_id=ap.p_id 
		left join tbl_stock_trn as tsth on tsth.stock_id=trsh.stock_id
		left join product_mst as p on p.product_id=ap.p_product_id 
		left join tbl_material_release_trn as rel on rel.p_id=tsr.p_id 
		left join process_mst as pr on pr.process_id=ap.process_id 
		left join tbl_request_product req on req.rp_id=ap.p_ref_id 
		left join tbl_set_main_process as smain on smain.sp_id=req.sp_id 
		left join users as users on users.user_id=tsr.request_user_id 
		left join unit_mst as umst on umst.unitid=ap.process_unit where tsr.company_id=".$_SESSION['company_id']." and tsr.store_aprv_log_id=".$store_request_id." ".$check_branch . " order by tsr.store_aprv_log_id desc";
	$result  = $dbcon->query($sql);
	$rel = brp_mysqli_fetch_array($result);


	$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,req.rp_id as req_id, pr.process_name, ,ap.extra_stock from tbl_allocate_process as ap
		left join product_mst as p on p.product_id=ap.p_product_id 
		left join process_mst as pr on pr.process_id=ap.process_id
		left join tbl_request_product req on req.rp_id=ap.p_ref_id
		left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
		left join unit_mst as umst on umst.unitid=ap.process_unit
		where ap.p_id in (".$rel['p_id'].")" ;

	$result1  = $dbcon->query($query1);
	$rel1 = brp_mysqli_fetch_array($result1);

	$query2 = "select mtr.*,p.product_name,umst.unit_name,users.user_name from tbl_store_release_material_trn as mtr 
			left join product_mst as p on p.product_id=mtr.product_id 
			left join unit_mst as umst on umst.unitid=mtr.release_unit
			left join users as users on users.user_id=mtr.user_id 
			where mtr.release_id = " . $rel['store_release_id'];			
	$result2=$dbcon->query($query2);
	$rel2 = brp_mysqli_fetch_array($result2);


	$extra_stock = $rel['extra_stock'];
	$batch_no = $rel['rbatch'];
	if($extra_stock == '1'){
		$rp_id = $rel['rp_id'];

		$RM_qry = "SELECT rp_id from tbl_request_product where  status = 0 AND perent_id = ".$rp_id;
		$rw_RM = brp_mysqli_fetch_assoc($dbcon->query($RM_qry));

		$rm_rp_id = $rw_RM['rp_id'];

		$batch_no = $rw_bt['batch_no'];

		$btc_qry = "SELECT group_concat(batch_no) as batch_no from work_order_extra_reserve_temp where  status = 3 AND rp_id in(".$rm_rp_id.")";
		$rw_bt = brp_mysqli_fetch_assoc($dbcon->query($btc_qry));

		$batch_no = $rw_bt['batch_no'];
	}

$html='';

$html.='<html>
<head>					
<title>Work Order No - '.$rel['work_order_no'].'</title>

<style type="text/css">

	.nextpage
	{
		page-break-after: always;
	}
	table{
		border-collapse:collapse;
		width:100%;
	}
	
	.blueHeading {
		color: #365f91;
	}
	td {
		height : 40px;
		padding-left : 5px;
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
			<tr>
				<td style="text-align:center;border:1px solid black;" colspan="3"><h2>RM REQUISITION CUM ISSUE SLIP</h2></td>
			</tr>

			<tr>
				<td style="text-align:left;border:1px solid black;font-size:18px">Date : '.date('d-m-Y',strtotime($rel['cdate'])).'</td>
				<td style="text-align:left;border:1px solid black;font-size:18px">W.O. No. : '.$rel['work_order_no'].'</td>
				<td style="text-align:left;border:1px solid black;font-size:18px" rowspan="2">Issue Slip No. : '.$rel['issue_no'].'</td>
			</tr>
			<tr>
				<td style="text-align:left;border:1px solid black;font-size:18px" >Grade : </td>
				<td style="text-align:left;border:1px solid black;font-size:18px" >Code No : '.$rel['product_icode'].'</td>
			</tr>

			<tr>
				<td style="text-align:left;border:1px solid black;font-size:18px" colspan="2" >Product Name : '.$rel['product_name'].'</td>
				<td style="text-align:left;border:1px solid black;font-size:18px" >Size : </td>
			</tr>

			<tr>
				<td style="text-align:left;border:1px solid black;font-size:18px" colspan="2">RM Required : '.$rel2['product_name'].'</td>
				<td style="text-align:left;border:1px solid black;font-size:18px" >Quantity : '.$rel['release_qty'].' '.$rel['unit_name'].'</td>
			</tr>
			</table>
			<table>
				<tr>
					<td style="text-align:left;border:1px solid black;font-size:18px;width:50%">Requested By : '.$rel['user_name'].'</td>
					<td style="text-align:left;border:1px solid black;font-size:18px;width:50%">Supervisors Sign : </td>
				</tr>
			</table>
			<table>
			<tr>
				<td style="width:33.33%;text-align:left;border:1px solid black;font-size:18px" ></td>
				<td style="width:33.33%;text-align:left;border:1px solid black;font-size:20px;background:black;color:white" ><strong>RM Store Entry Only</strong></td>
				<td style="width:33.33%;text-align:left;border:1px solid black;font-size:18px" ></td>
			</tr>

			<tr>
				<td style="text-align:left;border:1px solid black;font-size:18px" >SMPL Heat No. : '.$batch_no.'</td>
				<td style="text-align:left;border:1px solid black;font-size:18px;" ></td>
				<td style="text-align:left;border:1px solid black;font-size:18px" rowspan="2">Job Card No. : '.$rel['job_card_no'].'</td>
			</tr>

			<tr>
				<td style="text-align:left;border:1px solid black;font-size:18px" >Issued Qty.: '.$rel2['release_qty'].' '.$rel2['unit_name'].'</td>
				<td style="text-align:left;border:1px solid black;font-size:18px;" >Date : '.date('d-m-Y',strtotime($rel['issue_date'])).'</td>
			</tr>
		</table>
		<table>
			<tr>
				<td style="text-align:left;border:1px solid black;font-size:18px;width:50%">Issued By Sign.: '.$rel2['user_name'].'</td>
				<td style="text-align:left;border:1px solid black;font-size:18px;width:50%">Recieved By Sign</td>
			</tr>
		</table>

		<table>
			<tr>
				<td style="width:33.33%;text-align:left;border:1px solid black;font-size:18px" ></td>
				<td style="width:33.33%;text-align:left;border:1px solid black;font-size:20px;background:black;color:white" ><strong>Return Materials Details</strong></td>
				<td style="width:33.33%;text-align:left;border:1px solid black;font-size:18px" ></td>
			</tr>
		</table>
		<table>
			<tr>
				<td style="text-align:left;border:1px solid black;font-size:18px;width:50%">Returned Qty.: </td>
				<td style="text-align:left;border:1px solid black;font-size:18px;width:50%">Recieved By : </td>
			</tr>
		</table>

		<table>
			<tr>
				<td style="text-align:left;border:none;font-size:18px;width:33.33%;vertical-align:top">Rev. No : 01</td>
				<td style="text-align:center;font-size:18px; width:33.33%;vertical-align:top">Date : 01.04.2021</td>
				<td style="text-align:right;font-size:18px;width:33.33%;vertical-align:top">F: STR: 02</td>
			</tr>
		</table>
	</div>
	</body>
	</html>';
/*echo $header;
echo $html;exit;*/
ob_end_clean();
include("../../view/export/mpdf/mpdf.php");
$mpdf=new mPDF('','A4-L','0','calibri','10','10','10','10','1','1');
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
