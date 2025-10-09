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

		$where.="  and inq.inquiry_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND inq.inquiry_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$whr='';
		if($POST['cust_id']){
			$whr .= ' and inq.cust_id='.$POST['cust_id'];
		}

		if($POST['product_id']){
			$whr .= ' and trn.product_id='.$POST['product_id'];
		}

		if($POST['inquiry_id']){
			$whr .= ' and inq.inquiry_id='.$POST['inquiry_id'];
		}

		$where .=$whr;
		$appData = array();
		$i=1;
		$aColumns = array('inq.inquiry_no', 'inq.inquiry_date','l.cust_name','p.product_name', 'trn.product_qty', 'unit.unit_name','us.user_name');
		$sIndexColumn = "trn.inquiry_trn_id";
		$isWhere = array("inquiry_trn_status = 0".$where);
		$sTable = "tbl_inquiry_trn as trn";			
		$isJOIN = array('left join tbl_inquiry as inq on inq.inquiry_id = trn.inquiry_id','left join unit_mst as unit on unit.unitid = trn.unitid','left join tbl_customer as l on l.cust_id=inq.cust_id','left join product_mst as p on p.product_id=trn.product_id','left join users as us on us.user_id=inq.user_id');
		$hOrder = "inquiry_trn_id desc";
		include('../../../include/pagging.php');
		//echo $sQuery;
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $id;
			$row_data[] = $row['inquiry_no'];
			$row_data[] = date('d-m-Y',strtotime($row['inquiry_date']));
			$row_data[] = $row['cust_name'];
			$row_data[] = $row['product_name'];
			$row_data[] = number_format($row['product_qty'],4,".","");
			$row_data[] = $row['user_name'];
			 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
    
	
?>