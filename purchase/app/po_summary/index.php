<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}


if(strtolower($POST['mode']) == "fetch") {
}

else if(strtolower($POST['mode']) == "po_product_report")
{
	//var_dump($POST);
	$s_date=explode(' - ',$POST['date']);
	$set = "select * from tbl_company where company_id=".$_SESSION
	['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	$where = '';

	
	
	$where ='';
	$where.="  and po.purchaseorder_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND po.purchaseorder_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";

	$whr = '';
	
	if($POST['product_id']){
		$whr.=' and ptrn.product_id='.$POST['product_id'];
	}

	if($POST['vender_id']){
		$whr.=' and po.vender_id ='.$POST['vender_id'];
	}

	if($POST['purchaseorder_id']){
		$whr.=' and po.purchaseorder_id ='.$POST['purchaseorder_id'];
	}

	$where .= $whr;
	/*var_dump($report_qty['opening_unit']);
	var_dump($report_qty2['opening_unit']);*/
	$query = "select req.indent_no, req.indent_date, po.purchaseorder_no, po.purchaseorder_date,led.l_name,pro.product_name,ptrn.product_qty,ptrn.product_conv_qty,ptrn.product_rate,ptrn.purchaseordertrn_id,bun.unit_name as base_unit, cun.unit_name as conv_unit, us.user_name, ptrn.rate_unit, ptrn.unit_id, ptrn.conv_unit_id 

	from tbl_purchaseorder as po
	left join tbl_purchaseordertrn as ptrn on ptrn.purchaseorder_id = po.purchaseorder_id
	left join tbl_purchaseorder_req_trn as preq on preq.purchaseordertrn_id = ptrn.purchaseordertrn_id
	left join tbl_request_product as req on req.rp_id = preq.rp_id
	left join tbl_ledger as led on led.l_id = po.vender_id
	left join product_mst as pro on pro.product_id = ptrn.product_id
	left join unit_mst as bun on bun.unitid = ptrn.unit_id
	left join unit_mst as cun on cun.unitid = ptrn.conv_unit_id
	left join users as us on us.user_id = po.userid

	where po.status=0 and ptrn.purchaseordertrn_status=0 and po.po_approval_status=1 and po.revise_status=0 ".$where."";

	
	$result=$dbcon->query($query);

	$str ='';
	$str .='<table width="100%" class="display table table-bordered table-striped">
		<tr>
			<th colspan="17" style="text-align:center">
				<h4>'.$set_head['company_name'].'</h4>
				<strong>'.$set_head['address'].'</strong><br>
			</th>
		</tr>
		<tr>
			<th>Sr.No</th>
			<th>Indent No</th>
			<th>Indent Date</th>
			<th>Po No.</th>
			<th>Po Date</th>
			<th>Vendor Name</th>
			<th>Product Name</th>
			<th>PO Qty</th>
			<th>Rate</th>
			<th>Delivery Date</th>
			<th>Create By</th>
		</tr>';
	$i=1;
	if(brp_mysqli_num_rows($result)>0){
		while($row = brp_mysqli_fetch_array($result)){
			

			$indent_date='';
			if($row['indent_date']!="1970-01-01" && $row['indent_date']!="0000-00-00" && $row['indent_date']!="")
			{
				$indent_date=date('d-m-Y',strtotime($row['indent_date']));
			}

			$purchaseorder_date='';
			if($row['purchaseorder_date']!="1970-01-01" && $row['purchaseorder_date']!="0000-00-00" && $row['indent_date']!="")
			{
				$purchaseorder_date=date('d-m-Y',strtotime($row['purchaseorder_date']));
			}

			if($row['unit_id'] == $row['conv_unit_id']){
				$product_qty = '<strong style="color:green">'.number_format($row['product_qty'],4,'.','').' '.$row['base_unit'].'</strong>';
			}else{
				$product_qty = '<strong style="color:green">'.number_format($row['product_qty'],4,'.','').' '.$row['base_unit'].'</strong><br><strong style="color:orange">'.$row['product_conv_qty'].' '.$row['conv_unit'].'</strong>';
				$qty_conv_total = $qty_conv_total + $row['product_conv_qty'];
			}

			$qty_total = $qty_total + $row['product_qty'];
			
			/*$pqty_total= $pqty_total + $pending_qty;*/
			$rate_total = $rate_total + $row['product_rate'];
			$po_del = "select del.delivery_date,del.product_qty,unit.unit_name from tbl_purchaseorder_delivery_date as del 
			left join unit_mst as unit on unit.unitid = del.unit_id
			where purchaseordertrn_id=".$row['purchaseordertrn_id']." and po_delivery_date_status=0";
			$result_del = $dbcon->query($po_del);

			$str.='<tr>
				<td>'.$i.'</td>
				<td>'.$row['indent_no'].'</td>
				<td>'.$indent_date.'</td>
				<td>'.$row['purchaseorder_no'].'</td>
				<td>'.$purchaseorder_date.'</td>
				<td>'.$row['l_name'].'</td>
				<td>'.$row['product_name'].'</td>
				<td>'.$product_qty.'</td>
				<td>'.$row['product_rate'].'</td>
				<td>';
			$str.='<table style="width:100%" class="table table-bordered">
				<tr>
					<th>Sr.No.</th>
					<th>Delivery Date</th>
					<th>Delivery Qty</th>
				</tr>';
				$j=1;
			while($drow = brp_mysqli_fetch_array($result_del)){
				$delivery_date='';
				if($drow['delivery_date']!="1970-01-01" && $drow['delivery_date']!="0000-00-00" && $drow['delivery_date']!="")
				{
					$delivery_date=date('d-m-Y',strtotime($drow['delivery_date']));
				}
				$str.='<tr>
					<td>'.$j.'</td>
					<td>'.$delivery_date.'</td>
					<td>'.$drow['product_qty'].' '.$drow['unit_name'].'</td>
				</tr>';
			}
			$str.='</table>';
			$str.='</td>
				<td>'.$row['user_name'].'</td>		
			</tr>';
			$i++;
		}
		$str.='<tr>
			<td colspan="7" style="text-align:right">Total</td>
			<td style="text-align:right"><strong style="color:green">'.number_format($qty_total,4,'.','').'</strong><br><strong style="color:orange">'.number_format($qty_conv_total,4,'.','').'</strong></td>
			<td style="text-align:right">'.indian_number($rate_total,2).'</td>
			<td colspan="2" style="text-align:right"></td>
		</tr>';
	}else{
		$str .= '<tr>
			<td colspan="12" style="text-align:center">No Data Yet...</td>
		</tr>';
	}
	$str.='</table>';
	echo $str;
}
?>