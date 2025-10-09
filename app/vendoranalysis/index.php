<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
}

else if(strtolower($POST['mode']) == "vendorwiseanalysisreport" )
{
	$cust_id=$POST['cust_id'];
	$product_id=$POST['product_id'];

	$pr_row=get_product_detail($dbcon,$product_id);
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table  width="100%"   class="display">
	</table>
	<table  class="" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[Vender Detail Analysis]</strong>
	</td>
	</tr>';
	$s_date=explode(' - ',$POST['rep_po_date']);
	$whr.=" tp.purchaseorder_date between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	$query="SELECT group_concat(tp.purchaseorder_id) as poids,tl.l_name as vendorname,tl.m_address,tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date,tp.vender_id
	FROM tbl_purchaseorder as tp
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id where ".$whr."";

	if($POST['po_date_type']){
		if($POST['po_date_type']=='po'){
			$s_date=explode(' - ',$POST['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" where tp.purchaseorder_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_date<='".date('Y-m-d',strtotime($enddate))."'";
		}else{
			$s_date=explode(' - ',$POST['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" where tp.purchaseorder_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_date<='".date('Y-m-d',strtotime($enddate))."'";
		}

	}

	if(isset($POST['specific_vendor'])){
		if($POST['vendor_id']){
			$query.=' and tp.vender_id='.$POST['vendor_id'];
		}
	}
	$query.=" group by tp.vender_id";

	//var_dump($query);
	$result=$dbcon->query($query);
	$i=1;

	if(mysqli_num_rows($result)>0)
	{
		$total=0;
		while($vendor_list=mysqli_fetch_assoc($result))
		{

			$str.='<tr style="margin-top:15px;">
			<td>
			</td>
			<td colspan="5">
			<strong>'.$vendor_list['vendorname'].'</strong>
			</td>
			</tr>
			<tr >
			<td>
			</td>
			<td colspan="5">
			'.$vendor_list['m_address'].'
			</td>
			</tr>';
			$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="5%" style="text-align:center">NO.</th>
			<th width="10%" style="text-align:center">Po No.</th>
			<th width="10%" style="text-align:center">Po Date.</th>
			<th width="18%" style="text-align:center">Description & Drawing No.</th>
			<th width="10%" style="text-align:center">Challan No.</th>
			<th width="10%" style="text-align:center">Del.Date</th>
			<th width="10%" style="text-align:center">PO Qty.</th>
			<th width="10%" style="text-align:center">Recevied On</th>
			<th width="10%" style="text-align:center">Recevied Qty</th>
			<th width="20%" style="text-align:center">Delay Days</th>';
			$vender_ids=$_POST['vendor_id'];
			if(!empty($_POST['dvendor_id'])){
				$query="SELECT tp.purchaseorder_no,tp.purchaseorder_date,tpt.unit_id,tpt.product_id, tl.l_name as vendorname,tpt.product_qty,p.product_name,p.product_desc,tp.purchaseorder_due_date,tpt.purchaseordertrn_id
				FROM tbl_purchaseorder as tp
				left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
				left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
				left JOIN product_mst as p ON p.product_id=tpt.product_id where ".$whr." and tp.vender_id=".$_POST['dvendor_id']." ";
			}else if(!empty($vender_ids)){
				$query="SELECT tp.purchaseorder_no,tp.purchaseorder_date,tpt.unit_id,tpt.product_id, tl.l_name as vendorname,tpt.product_qty,p.product_name,p.product_desc,tp.purchaseorder_due_date,tpt.purchaseordertrn_id
				FROM tbl_purchaseorder as tp
				left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
				left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
				left JOIN product_mst as p ON p.product_id=tpt.product_id where ".$whr."  and tp.vender_id=".$vender_ids."";
			}else{
				$query="SELECT tp.purchaseorder_no,tp.purchaseorder_date,tpt.unit_id,tpt.product_id, tl.l_name as vendorname,tpt.product_qty,p.product_name,p.product_desc,tp.purchaseorder_due_date,tpt.purchaseordertrn_id
				FROM tbl_purchaseorder as tp
				left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
				left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
				left JOIN product_mst as p ON p.product_id=tpt.product_id where ".$whr." ";
			}
			

			if(isset($POST['specific_item'])){
				if($POST['item_id']){
					$query.=' and tpt.product_id='.$POST['item_id'];
				}
			}
			//print_r($query);
			$result1=$dbcon->query($query);
			$j=1;
			if(mysqli_num_rows($result1)>0)
			{
				while($re=mysqli_fetch_assoc($result1))
				{
					$pen=$re["product_qty"]-$re["used_qty"];
					$purchaseorder_date='';
					if($re['purchaseorder_date']!=''){
						$purchaseorder_date=date('d/m/Y',strtotime($re['purchaseorder_date']));
					}
					$purchase_order_due_date='';
					$receiveddate=getchallanno($dbcon,$re["product_id"],$re["purchaseordertrn_id"],'grn_date');
					$diffdays=dateDiffInDays($receiveddate, $re['purchaseorder_due_date']);
					if($re['purchaseorder_due_date']!=''){
						$purchase_order_due_date=date('d/m/Y',strtotime($re['purchaseorder_due_date']));
					}
					if($receiveddate!='-'){
						$receiveddate=date('d/m/Y',strtotime($receiveddate));
					}
					
					$str.='<tr style="border: 1px dashed #cccccc;">
					<td style="text-align:center">'.$j.'</td>
					<td style="text-align:center">'.$re["purchaseorder_no"].'</td>
					<td style="text-align:center">'.$purchaseorder_date.'</td>
					<td style="text-align:center">'.$re["product_name"].'<br>'.$re["product_desc"].'</td>
					<td>'.getchallanno($dbcon,$re["product_id"],$re["purchaseordertrn_id"],'challan_no').'</td>
					<td style="text-align:center">'.$purchase_order_due_date.'</td>
					<td style="text-align:center">'.$re["product_qty"].'</td>
					<td>'.$receiveddate.'</td>
					<td>'.getreceivedqty($dbcon,$re["product_id"],$re["purchaseordertrn_id"]).'</td>
					<td>'.$diffdays.'</td>';
					$j++;
				}

			}else{
				$str .='<tr>
				<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
			}

		}
	}else
	{
		$str .='<tr>
		<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
		</tr>';

	}
	$str .='				 
	</table>';
	echo $str;
}

else if(strtolower($POST['mode']) == "allanalysisreport" )
{
	$tot_last_po_qty=0;
	$tot_last_req_qty=0;
	$tot_last_pen_qty=0;
	$cust_id=$POST['cust_id'];
	$product_id=$POST['product_id'];
	$pr_row=get_product_detail($dbcon,$product_id);
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table  width="100%"   class="display">
	</table>
	<table  class="" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[Vender Detail Analysis]</strong>
	</td>
	</tr>';

	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="5%" style="text-align:center">NO.</th>
	<th width="5%" style="text-align:center">Indent NO. <br> Indent Date</th>
	<th width="10%" style="text-align:center">PO No. <br> PO Date</th>';
	$str.='<th width="10%" style="text-align:center">Vender Name</th>
	<th width="20%" style="text-align:center">Item Details</th>';
	$str.='
	<th width="12%" style="text-align:center">Challan No. <br> Grn No</th>
	<th width="12%" style="text-align:center">P.O Qty. <br> Recevied Qty.</th>
	<th width="12%" style="text-align:center">Accept Qty. <br> Rej Qty.</th>
	<th width="12%" style="text-align:center">Delivery Date. <br> Recevied Date.</th>
	<th width="12%" style="text-align:center">Delay Days</th>';

	$query="SELECT tpt.purchaseordertrn_id,tp.vender_id,tpt.product_id,tp.purchaseorder_no,tp.purchaseorder_date,tpt.product_id,tl.l_name as vendorname,trp.indent_no,trp.indent_date,tpt.product_qty,p.product_name,p.product_desc,tp.purchaseorder_due_date
	FROM tbl_purchaseorder as tp
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN tbl_request_product as trpc ON tpt.po_ref_id=trpc.rp_id
	left JOIN tbl_set_main_process as tsmp ON tsmp.sp_id=trpc.sp_id
	left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
    left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.status=0 and 	tp.po_approval_status=1";

    $s_date=explode(' - ',$POST['rep_po_date']);
	$startdate=$s_date[0];
	$enddate=$s_date[1];

    $query.=" and tp.purchaseorder_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_date<='".date('Y-m-d',strtotime($enddate))."'";

    if($POST['vendor_id']){
			$query.=' and tp.vender_id='.$POST['vendor_id'];
	}

	if($POST['item_id']){
			$query.=' and tpt.product_id='.$POST['item_id'];
	}

	// if($POST['po_date_type']){
	// 	if($POST['po_date_type']=='po'){
	// 		$s_date=explode(' - ',$POST['rep_po_date']);
	// 		$startdate=$s_date[0];
	// 		$enddate=$s_date[1];
	// 		$query.=" where tp.purchaseorder_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_date<='".date('Y-m-d',strtotime($enddate))."'";
	// 	}else{
	// 		$s_date=explode(' - ',$POST['rep_po_date']);
	// 		$startdate=$s_date[0];
	// 		$enddate=$s_date[1];
	// 		$query.=" where tp.purchaseorder_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_date<='".date('Y-m-d',strtotime($enddate))."'";
	// 	}

	// }
	// if(isset($POST['specific_vendor'])){
	// 	if($POST['vendor_id']){
	// 		$query.=' and tp.vender_id='.$POST['vendor_id'];
	// 	}
	// }

	// if(isset($POST['specific_item'])){
	// 	if($POST['item_id']){
	// 		$query.=' and tpt.product_id='.$POST['item_id'];
	// 	}
	// }
	$result1=$dbcon->query($query);
	$j=1;
	if(mysqli_num_rows($result1)>0)
	{
		while($re=mysqli_fetch_assoc($result1))
		{
			$intent_date='';
			if($re['indent_date']!=''){
				$intent_date=date('d/m/Y',strtotime($re['indent_date']));
			}

			$receiveddate=getchallanno($dbcon,$re["product_id"],$re["purchaseordertrn_id"],'grn_date');
			$diffdays=dateDiffInDays($receiveddate, $re['purchaseorder_due_date']);

			if($receiveddate!='-'){
				$receiveddate=date('d/m/Y',strtotime($receiveddate));
			}

			$purchase_order_due_date='';
			if($re['purchaseorder_due_date']!=''){
				$purchase_order_due_date=date('d/m/Y',strtotime($re['purchaseorder_due_date']));
			}

			
			$purchaseorder_date='';
			if($re['purchaseorder_date']!=''){
				$purchaseorder_date=date('d/m/Y',strtotime($re['workorderdate']));
			}

			
			$str.='<tr style="  border: 1px dashed #cccccc;">
			<td style="text-align:center">'.$j.'</td>';
			$str.='<td style="text-align:center">'.$re["indent_no"].'<br>'.$intent_date.'</td>';
			$str.='<td style="text-align:center">'.$re["purchaseorder_no"].'<br>'.$purchaseorder_date.'</td>
			<td style="text-align:center">'.$re["vendorname"].'</td>';
			$str.='<td style="text-align:center">'.$re["product_name"].'<br>'.$re["product_desc"].'</td>';
			$str.='<td>'.getchallanno($dbcon,$re["product_id"],$re["purchaseordertrn_id"],'challan_no').'<br>'.getgrnno($dbcon,$re["product_id"],$re["purchaseordertrn_id"],'challan_no').'</td>
			<td style="text-align:center">'.$re["product_qty"].'<br>'.getreceivedqty($dbcon,$re["product_id"],$re["purchaseordertrn_id"]).'</td>
			<td style="text-align:center">'.getqcaccpetedqty($dbcon,$re["product_id"],$re["purchaseordertrn_id"]).'<br>'.getqcrejectedqty($dbcon,$re["product_id"],$re["purchaseordertrn_id"]).'</td>
			<td style="text-align:center">'.$purchase_order_due_date.'<br>'.$receiveddate.'</td>

			<td style="text-align:center;">'.$diffdays.'</td>
			</tr>';
			$j++;
		}

	}else{
		$str .='<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}

	$str .='				 
	</table>';
	echo $str;
}
?>