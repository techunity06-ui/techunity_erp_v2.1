<?php

session_start(); //start session
$AJAX = true;
include("../../config/config.php");
///error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
	$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];

	$where='';
		//$where.="  and quot.quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND quot.quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";

	$appData = array();
	$i=1;
	$aColumns = array('so_trn.sales_ordertrn_id','so.sales_order_no','so.sales_order_date','so.po_no','so.po_date','led.company_name','pro.product_name','unit.unit_name','cunit.unit_name as conv_unit_name','so_trn.product_qty','so_trn.product_conv_qty','so_trn.product_id','so_trn.unit_id','so_trn.conv_unit_id','so_trn.with_out_stock_invoice','led.l_name','COALESCE(itrns.invoice_qty, 0) as invoice_qty','COALESCE(itrns.invoice_conv_qty, 0) as invoice_conv_qty');
	$sIndexColumn = "so_trn.sales_ordertrn_id";
	$isWhere = array("so_trn.sales_ordertrn_status = 0 and so_trn.invoice_status=0 and so.approve_status=3 and so.company_id IN (0,$_SESSION[company_id]) and so.invoice_status=0");
	$sTable = "tbl_sales_ordertrn as so_trn";
	$isJOIN = array("left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id","left join tbl_ledger as led on led.l_id = so.cust_id","left join product_mst as pro on pro.product_id = so_trn.product_id","left join unit_mst as unit on unit.unitid = so_trn.unit_id", "left join unit_mst as cunit on cunit.unitid = so_trn.conv_unit_id","LEFT JOIN 
    (SELECT 
        sales_ordertrn_id, 
        SUM(product_qty) as invoice_qty, 
        SUM(product_conv_qty) as invoice_conv_qty 
    FROM 
        tbl_invoicetrn 
    WHERE 
        trancation_status = 0 
    GROUP BY 
        sales_ordertrn_id) as itrns ON itrns.sales_ordertrn_id = so_trn.sales_ordertrn_id");
	$hOrder = "so_trn.sales_ordertrn_id desc";
	include('../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {

		if($row['unit_id'] == $row['conv_unit_id']){
			$product_qty = $row['product_qty'].' '.$row['unit_name'];
		}else{
			$product_qty = $row['product_qty'].' '.$row['unit_name'].'<br>'.$row['product_conv_qty'].' '.$row['conv_unit_name'];
		}

		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['sales_order_no'];
		$row_data[] = date('d M, Y',strtotime($row['sales_order_date']));
		$row_data[] = $row['po_no'];
		$row_data[] = date('d M, Y',strtotime($row['po_date']));
		$row_data[] = $row['l_name'];
		$row_data[] = $row['product_name'];
		$row_data[] = $product_qty;
		$pqty = 0;
	
		// $qty = $dbcon->query("SELECT reserve_id,base_stock,convert_stock, FROM tbl_reserve_stock WHERE stock_flage = 1 AND stock_status!=2 AND company_id = '".$_SESSION['company_id']."' AND sales_order_trn_id =".$row['sales_ordertrn_id']);
		$qty = $dbcon->query("SELECT res.reserve_id, IFNULL(SUM(res.base_stock),0)  as base_stock, IFNULL(SUM(res.convert_stock),0) as convert_stock, IFNULL(ub.used_base, 0) as used_base_stock, IFNULL(ub.used_conv, 0) as used_convert_stock FROM tbl_reserve_stock AS res LEFT JOIN (SELECT perent_id, IFNULL(SUM(base_stock), 0) AS used_base,IFNULL(SUM(convert_stock), 0) AS used_conv FROM tbl_reserve_stock WHERE stock_flage = 2 AND stock_status != 2 AND company_id = ".$_SESSION['company_id']." GROUP BY perent_id) AS ub ON  res.reserve_id = ub.perent_id WHERE res.stock_flage = 1 AND res.stock_status != 2 AND res.company_id = ".$_SESSION['company_id']." AND res.sales_order_trn_id = ".$row['sales_ordertrn_id']." Group By res.sales_order_trn_id");
		$res = brp_mysqli_fetch_assoc($qty);
		
		$pqty=$pqty+($res['base_stock']-$res['used_base']);
		$pcqty = $pcqty + ($res['convert_stock']-$res['used_conv']);

		$invoice_pending = $row['product_qty'] - $row['invoice_qty'];
		$invoice_conv_pending = $row['product_conv_qty'] - $row['invoice_conv_qty'];
		$reserve_stock = 0;
		if($row['unit_id'] == $row['conv_unit_id']){
			$invoice_pending1 = $invoice_pending.' '.$row['unit_name'];
			$pqty1 = $pqty.' '.$row['unit_name'];
			$reserve_stock = $pqty - $row['invoice_qty'];
		}else{
			$invoice_pending1 = $invoice_pending.' '.$row['unit_name'].'<br>'.$invoice_conv_pending.' '.$row['conv_unit_name'];
			$pqty1 = $pqty.' '.$row['unit_name'].'<br>'.$pcqty.' '.$row['conv_unit_name'];
			$reserve_stock = $pqty - $row['invoice_conv_qty'];
		}


		$row_data[] = $invoice_pending1;
		$row_data[] = $pqty1;
		$so_to_inv_btn='';
		
		
		if($row['with_out_stock_invoice']=="0"){
			/////////////////////////////////////////////////////////////////////////Harshil- 8-2-2023///////////////////////////////////////////////////////////////////
			 //$qry_type = "SELECT pmst.product_type,ptype.is_service FROM product_mst as pmst left join pro_ms_product_type as ptype on pmst.product_type=ptype.product_type_id where pmst.product_id=".$row['product_id'];
			
			//$get_sales_order_type = brp_mysqli_fetch_assoc($dbcon->query($qry_type));
			//if($get_sales_order_type['is_service']==0)
			//{
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// $reserve_stock=reserve_stock($dbcon,$row['product_id'],$row['unit_id'],"","","",$row['sales_ordertrn_id']);
				//var_dump($reserve_stock);
				if($reserve_stock>0){
				

					$so_to_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.''.FINANCE_ROOT.'invoiceso/'.$row['sales_ordertrn_id'].'"><i class="fa fa-plus-circle"></i>Add Invoice</a>';
				}
			//}
			//else
			//{
				//$so_to_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.''.FINANCE_ROOT.'invoiceso/'.$row['sales_ordertrn_id'].'"><i class="fa fa-plus-circle"></i>Add Invoice</a>';
			//}
			
		}else{

			$so_to_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceso/'.$row['sales_ordertrn_id'].'"><i class="fa fa-plus-circle"></i>Add Invoice</a>';
		}
		

		$row_data[] = $so_to_inv_btn;

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "load_pend_disp") {
	$str='';$whr='';
	if($POST['log_user_id']){
		$whr.=' and quot.quot_won_user_id='.$POST['log_user_id'];
	}

	$qry='SELECT quot.quotation_id, cust.cust_name, pro.product_name, trn.product_qty, quot.qt_delivery_date, quot.quotation_no, quot.quotation_date FROM tbl_quotation as quot 
	left join tbl_customer as cust on cust.cust_id=quot.cust_id 
	left join tbl_quotation_trn as trn on trn.quotation_id=quot.quotation_id 
	left join product_mst as pro on pro.product_id=trn.product_id 
	where ( 1 AND quot.quotation_status = 0 and revise_status=0 and quot.approve_status=1 and trn.quot_trn_status=0 and trn.inv_done_status=0 '.$whr.' ) ORDER BY quot.qt_delivery_date';
		$qry_rs=$dbcon->query($qry);
		if(mysqli_num_rows($qry_rs)){
			$k=1;
			while($rel=mysqli_fetch_assoc($qry_rs)){		
				$qt_delivery_date='';
				if($rel['qt_delivery_date']!="1970-01-01" && $rel['qt_delivery_date']!="0000-00-00"){
					$qt_delivery_date=date('d M, Y',strtotime($rel['qt_delivery_date']));
				}
				$str.='<tr>
				<td class="text-left">'.$k.'</td>
				<td class="text-left">'.$rel['cust_name'].'</td>
				<td class="text-left"><strong>'.$rel['product_name'].'</strong></td>
				<td class="text-center">'.$rel['product_qty'].'</td>
				<td class="text-left">'.$qt_delivery_date.'</td>
				</tr>';
				$k++;
			}
		}
		else{
			$str.='<tr>
			<td colspan="7" class="text-center">No Data Found !!!</td>
			</tr>';
		}
		$resp['resp_html']=$str;
		echo json_encode($resp);
	}
?>