<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "sales_order_due_date_report") {
	$s_date=explode(' - ',$POST['date']);
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));
	$getspecialConfiguration=getspecialConfiguration($dbcon);
	$companyConfiguration=getCompanyConfiguration($dbcon);
   	$sales_pro_search = explode(",", $companyConfiguration['sales_pro_search']);

	
	$str .='<table width="100%" class="table table-bordered table-striped">
	<tbody>
	<tr style="font-weight: bold; border-top: 1px solid; border-bottom: 1px solid;">
	<td>No.</td>
	<td>S.O. No.</td>
	<td>Date</td>
	<td>Customer P.O No.</td>
	<td>Customer P.O Date</td>
	<td>Customer Name</td>
	<td>Item Details</td>
	<td>S.O Qty</td>
	<td>Due Qty</td>
	<td>Delivery Date</td>
	<td>Username</td>
	</tr>';
	$where = '';
	if(!empty($POST['cust_id'])){
		$where.=' AND so.cust_id ='.$POST['cust_id'];
	}
	if(!empty($POST['product_id'])){
		$where.=' AND sotrn.product_id ='.$POST['product_id'];
	}
	if(!empty($POST['user_id'])){
		$where.=' AND so.user_id ='.$POST['user_id'];
	}
	if(!empty($POST['sales_order_id'])){
		$where.=' AND so.sales_order_id ='.$POST['sales_order_id'];
	}

	$query = $dbcon->query("SELECT sotrn.sales_ordertrn_id, sotrn.remaning_invoice_qty, sodate.product_qty as due_qty, sotrn.sales_order_id, sotrn.product_qty,sotrn.description, so.*, pro.product_name, l.l_name, sodate.delivery_date as del_date, user.user_name, pro.product_icode, pro.product_alias_name,dr.drawing_number FROM tbl_sales_ordertrn as sotrn 
	LEFT JOIN tbl_sales_order AS so ON so.sales_order_id = sotrn.sales_order_id 
	LEFT JOIN tbl_salesorder_delivery_date AS sodate ON sodate.sales_ordertrn_id = sotrn.sales_ordertrn_id 
	LEFT JOIN tbl_ledger AS l ON l.l_id = so.cust_id 
	LEFT JOIN product_mst AS pro ON pro.product_id = sotrn.product_id 
	LEFT JOIN tbl_drawing as dr on dr.drawing_id = pro.drawing_id
	LEFT JOIN users AS user ON user.user_id = so.user_id 
	
	WHERE sotrn.sales_ordertrn_status = 0 AND so.sales_order_status = 0 AND so.revise_status=0 AND sotrn.invoice_status=0 AND so.invoice_status=0 AND so.company_id = '".$_SESSION['company_id']."' AND so.delivery_date BETWEEN '".date("Y-m-d",strtotime($s_date[0]))."' AND '".date("Y-m-d",strtotime($s_date[1]))."' ".$where." ORDER BY so.delivery_date DESC");
	$i = 1;
	if(mysqli_num_rows($query) > 0){
		while($res = mysqli_fetch_assoc($query)){

			$drawing_number='';$item_code='';$alias='';
   			if(in_array('drawing',$sales_pro_search)){
	            $drawing_number = " -- (".$res['drawing_number'].")";
	        }
	        if(in_array('item',$sales_pro_search)){
	            $item_code = " -- (".$res['product_icode'].")";
	        }
	        if(in_array('alias',$sales_pro_search)){
	            $alias = " -- (".$res['product_alias_name'].")";
	        }

			$desc = '';
			if($getspecialConfiguration['power_drive']==1){
				$desc = '<br> '.$res['description'];
			}
			$str.='<tr>
			<td>'.$i.'</td>
			<td>'.$res['sales_order_no'].'</td>
			<td>'.date('d-m-Y',strtotime($res['sales_order_date'])).'</td>
			<td>'.$res['po_no'].'</td>
			<td>'.date('d-m-Y',strtotime($res['po_date'])).'</td>
			<td>'.$res['l_name'].'</td>
			<td>'.$res['product_name'].' '.$item_code.' '.$drawing_number.' '.$alias.' '.$desc.'</td>
			<td>'.$res['due_qty'].'</td>
			<td>'.$res['remaning_invoice_qty'].'</td>
			<td>'.date('d-m-Y',strtotime($res['delivery_date'])).'</td>
			<td>'.$res['user_name'].'</td>
			</tr>';
			$i++;
		}
	}else{
		$str.='<tr>
		<td colspan="11" style="text-align: center; font-weight: bold;">No Data Found</td>
		</tr>';
	}

	echo $str;
	// echo "SELECT sotrn.sales_ordertrn_id, sotrn.product_qty, sotrn.sales_order_id, sotrn.product_qty, so.*, pro.product_name, l.l_name, sodate.delivery_date as del_date FROM tbl_sales_ordertrn as sotrn LEFT JOIN tbl_sales_order AS so ON so.sales_order_id = sotrn.sales_order_id LEFT JOIN tbl_salesorder_delivery_date AS sodate ON sodate.sales_ordertrn_id = sotrn.sales_ordertrn_id LEFT JOIN tbl_ledger AS l ON l.l_id = so.cust_id LEFT JOIN product_mst AS pro ON pro.product_id = sotrn.product_id WHERE sotrn.sales_ordertrn_status = 0 AND so.sales_order_status = 0 AND so.company_id = '".$_SESSION['company_id']."' AND so.delivery_date BETWEEN '".date("Y-m-d",strtotime($s_date[0]))."' AND '".date("Y-m-d",strtotime($s_date[1]))."' ORDER BY so.delivery_date DESC";
}
?>