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
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where.="  and quot.quotation_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND quot.quotation_date<='".date('Y-m-d',strtotime($s_date[1]))."'";

		$whr = '';

		if($POST['cust_id']){
			$whr .= ' and quot.cust_id='.$POST['cust_id'];
		}
		
		if($POST['product_id']){
			$whr .= ' and trn.product_id='.$POST['product_id'];
		}

		if($POST['quotation_id']){
			$whr .= ' and quot.quotation_id='.$POST['quotation_id'];
		}

		$where .= $whr;

		$appData = array();
		$i=1;
		$aColumns = array('inq.inquiry_no', 'inq.inquiry_date', 'quot.quotation_no','quot.quotation_date', 'l.cust_name', 'p.product_name', 'trn.product_qty', 'trn.product_conv_qty', 'trn.product_rate', 'trn.product_amount', 'unit.unit_name', 'us.user_name');
		$sIndexColumn = "quot_trn_id";
		$isWhere = array("trn.quot_trn_status = 0 and quot.revise_status=0".$where);
		$sTable = "tbl_quotation_trn as trn";			
		$isJOIN = array('left join tbl_quotation as quot on quot.quotation_id=trn.quotation_id','left join tbl_inquiry as inq on inq.inquiry_id = quot.inquiry_id','left join unit_mst as unit on unit.unitid = trn.rate_unit','left join tbl_customer as l on l.cust_id=inq.cust_id','left join product_mst as p on p.product_id=trn.product_id','left join users as us on us.user_id=quot.user_id');
		$hOrder = "quot_trn_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();

			if($row['rate_unit']==$row['unitid']){
				$product_qty = $row['product_qty'];
			}else{
				$product_qty = $row['product_conv_qty'];
			}
			$row_data[] = $id;
			$row_data[] = $row['inquiry_no'];
			$row_data[] = date('d-m-Y',strtotime($row['inquiry_date']));
			$row_data[] = $row['quotation_no'];
			$row_data[] = date('d-m-Y',strtotime($row['quotation_date']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['product_name'];
			$row_data[] = number_format($product_qty,4,".","");
			$row_data[] = $row['unit_name'];
			$row_data[] = number_format($row['product_rate'],2,".","");
			$row_data[] = number_format($row['product_amount'],2,".","");
			$row_data[] = $row['user_name'];
			 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}	
?>