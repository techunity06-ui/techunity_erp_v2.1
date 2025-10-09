<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "generate_inquiry_lost_report") {
	$s_date=explode(' - ',$POST['date']);
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	$str .='<table width="100%" class="table table-bordered">
	<thead>
	<tr>
	<th>No.</th>
	<th>Inquiry No</th>
	<th>Inquiry Date</th>
	<th>Customer Name/Email/Mobile No</th>
	<th>Reason</th>
	<th>Reason Remark</th>
	<th>Lost Date</th>
	<th>Grand Total</th>
	<th>Username</th>
	<th>Action</th>
	</tr>
	</thead>
	<tbody>';
	$where = '';
	if(!empty($POST['cust_id'])){
		$where.=' AND inq.cust_id ='.$POST['cust_id'];
	}
	if(!empty($POST['user_id'])){
	// if(!empty($POST['reason_id'])){
	// 	$where.=' AND inq.reason_id ='.$POST['reason_id'];
	// }
		$where.=' AND inq.user_id ='.$POST['user_id'];
	}

	$query = $dbcon->query("SELECT inq.*, cust.cust_name, user.user_name, cust.cust_email, cust.cust_mobile FROM tbl_inquiry as inq LEFT JOIN tbl_customer AS cust ON inq.cust_id = cust.cust_id LEFT JOIN users AS user ON user.user_id = inq.user_id WHERE inq.inquiry_status = 0 AND inq.company_id = '".$_SESSION['company_id']."' AND inq.opp_id = '".LOST."' AND inq.inquiry_date BETWEEN '".date("Y-m-d",strtotime($s_date[0]))."' AND '".date("Y-m-d",strtotime($s_date[1]))."' ".$where." ORDER BY inq.inquiry_id DESC");
	$i = 1;
	if(mysqli_num_rows($query) > 0){
		$total = 0;
		while($res = mysqli_fetch_assoc($query)){
			$view_inq_btn = $reason_name = $reason_remark = '';
			$reason_array = json_decode($res['closed_reason']);
			foreach($reason_array as $res_array){
				// $rese = get_reason_by_id($dbcon,$res_array[1]);
				$reason_remark.= $res_array;
			}
			foreach($reason_array as $x => $val){
				$rese = get_reason_by_id($dbcon,$x);
				$reason_name.= $rese['reason'];
			}
// print_r($reason_name);
			$view_inq_btn = '<a href="' . ROOT . CRM_ROOT . 'inquiry_view/' . $res['inquiry_id'] . '" class="btn btn-xs btn-success" data-original-title="View Inquiry" data-toggle="tooltip" data-placement="top" target="_blank"><i class="fa fa-eye"></i></a>';
			$str.='<tr>
			<td>'.$i.'</td>
			<td>'.$res['inquiry_no'].'</td>
			<td>'.$res['inquiry_date'].'</td>
			<td><strong>'.$res['cust_name'].'</strong><br>Mo. : '.$res['cust_mobile'].'<br>Email : '.$res['cust_email'].'</td>
			<td>'.$reason_name.'</td>
			<td>'.$reason_remark.'</td>
			<td>'.$res['closing_date'].'</td>
			<td>'.$res['g_total'].'</td>
			<td>'.$res['user_name'].'</td>
			<td>'.$view_inq_btn.'</td>
			</tr>';
			$i++;
			$total+=$res['g_total'];
		}
		$str.='<tr>
		<td colspan="7" style="text-align: right; font-weight: bold;">Total : </td>
		<td style="font-weight: bold;">'.number_format($total,2).'</td>
		<td colspan="2" style="font-weight: bold;"></td>
		</tr>';
	}else{
		$str.='<tr>
		<td colspan="10" style="text-align: center; font-weight: bold;">No Data Found</td>
		</tr>';
	}
	$str.='</tbody>
	</table>';
	echo $str;
	// echo "SELECT sotrn.sales_ordertrn_id, sotrn.product_qty, sotrn.sales_order_id, sotrn.product_qty, so.*, pro.product_name, l.l_name, sodate.delivery_date as del_date FROM tbl_sales_ordertrn as sotrn LEFT JOIN tbl_sales_order AS so ON so.sales_order_id = sotrn.sales_order_id LEFT JOIN tbl_salesorder_delivery_date AS sodate ON sodate.sales_ordertrn_id = sotrn.sales_ordertrn_id LEFT JOIN tbl_ledger AS l ON l.l_id = so.cust_id LEFT JOIN product_mst AS pro ON pro.product_id = sotrn.product_id WHERE sotrn.sales_ordertrn_status = 0 AND so.sales_order_status = 0 AND so.company_id = '".$_SESSION['company_id']."' AND so.delivery_date BETWEEN '".date("Y-m-d",strtotime($s_date[0]))."' AND '".date("Y-m-d",strtotime($s_date[1]))."' ORDER BY so.delivery_date DESC";
}
?>