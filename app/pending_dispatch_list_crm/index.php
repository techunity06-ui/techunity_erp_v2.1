<?php

session_start(); //start session
$AJAX = true;
include("../../config/config.php");
///error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");
error_reporting(E_ALL);
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
		// $_SESSION['end']=$s_date[1];

		$where='';
		//$where.="  and quot.quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND quot.quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('so_trn.sales_ordertrn_id','so.sales_order_no','tes.t_name','so.sales_order_date','led.company_name','pro.product_name','so_trn.product_qty','so_trn.product_id','so_trn.unit_id','so_trn.branch_id','so_trn.with_out_stock_invoice','led.l_name','so.cust_id','so_trn.cgst_tax_per','so_trn.sgst_tax_per','so_trn.sgst_tax_rate','so_trn.igst_tax_per','so_trn.igst_tax_rate','so_trn.cgst_tax_rate','so_trn.product_discount','so_trn.discount_per','so_trn.product_amount');
		$sIndexColumn = "so_trn.sales_ordertrn_id";
		$isWhere = array("so_trn.sales_ordertrn_status = 0 and so_trn.invoice_status=0 and so.approve_status=3 and so.company_id IN (0,$_SESSION[company_id])");
		$sTable = "tbl_sales_ordertrn as so_trn";
		$isJOIN = array("left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id","left join tbl_ledger as led on led.l_id=so.cust_id","left join product_mst as pro on pro.product_id=so_trn.product_id","left join territory_mst as tes on tes.t_id=led.territory_id");
		$hOrder = "so_trn.sales_ordertrn_id desc";
		$having_clause =""; 
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
				
			$row_data = array();

			if($row['cgst_tax_per']!=0)
			{
				$cgst_tax ="<Strong>CGST (".$row['cgst_tax_per'].") : </strong>".$row['cgst_tax_rate']."<br>";
			}
			if($row['sgst_tax_per']!=0)
			{
				$sgst_tax="<Strong>SGST (".$row['sgst_tax_per'].") : </strong>".$row['sgst_tax_rate']."<br>";
			}

			if($row['igst_tax_per']!=0)
			{
				$igst_tax="<Strong>IGST (".$row['igst_tax_per'].") : </strong>".$row['igst_tax_rate']."<br>";
			}
			//$row_data[] = $row['quotation_no'];
			//$row_data[] = date('d M, Y',strtotime($row['quotation_date']));
			//pathik status code start 3-1-2022
			$pending_req_qty=0;$dispach_pending_qty=0;$reserve_pending_qty=0;
				/*$qry='SELECT IFNULL(sum(rp_req_qty),0) as reqqty FROM tbl_request_product as req 
						where req.status = 0 and req.sales_order_trn_id='.$row["sales_ordertrn_id"].' and req.company_id='.$_SESSION["company_id"];*/

				$qry = "SELECT IFNULL(sum(product_qty),0) as reqqty FROM tbl_sales_order_production_trn where sales_ordertrn_id = " .$row["sales_ordertrn_id"]. " and product_id = ".$row['product_id']." and company_id=".$_SESSION["company_id"];
				$qry_rs=$dbcon->query($qry);
				if(brp_mysqli_num_rows($qry_rs)){
					$rel=brp_mysqli_fetch_assoc($qry_rs);
					if($row['product_qty']>$rel['reqqty']){	
						$pending_req_qty=$row['product_qty']-$rel['reqqty'];
					}
				}
				if($pending_req_qty<=0){
					$total_reserve_stock=total_reserve_stock($dbcon,$row['product_id'],$row['unit_id'],"","","",$row['sales_ordertrn_id'],$row['branch_id']);

					if($total_reserve_stock<$row['product_qty']){
						$reserve_pending_qty=$row['product_qty']-$total_reserve_stock;
					}
				}
				if($reserve_pending_qty<=0 && $pending_req_qty<=0){
					$qry1='SELECT IFNULL(sum(product_qty),0) as dispach_qty FROM tbl_invoicetrn as req 
						where req.trancation_status = 0 and req.sales_ordertrn_id='.$row["sales_ordertrn_id"].' and req.company_id='.$_SESSION["company_id"];
						$qry_rs1=$dbcon->query($qry1);
						if(brp_mysqli_num_rows($qry_rs1)){
							$rel1=brp_mysqli_fetch_assoc($qry_rs1);
							$dispach_pending_qty=$row['product_qty']-$rel1['dispach_qty'];
						}
				}

				/*var_dump($pending_req_qty);
				var_dump($reserve_pending_qty);
				var_dump($dispach_pending_qty);*/

				if($pending_req_qty>0){
					$chval=1;
					$status_lab="Planning Pending (".$pending_req_qty.")";
					$status_btn="<span class='btn btn-xs btn-danger'>".$status_lab."</span>";
				}else if($reserve_pending_qty>0){
					$chval=2;
					$status_lab="Production Pending (".$reserve_pending_qty.")";
					$status_btn="<span class='btn btn-xs btn-warning'>".$status_lab."</span>";
				}else if($dispach_pending_qty>0){
					$chval=3;
					$status_lab="Dispach Pending (".$dispach_pending_qty.")";
					$status_btn="<span class='btn btn-xs btn-primary'>".$status_lab."</span>";
				}else{
					$chval=4;
					$status_lab="Dispach Done";
					$status_btn="<span class='btn btn-xs btn-success'>".$status_lab."</span>";
				}
				

			


			//pathik status code end 3-1-2022
			
			//$print_btn='<a class="btn btn-xs btn-info" data-original-title="View Quotation" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.'quotation_print/'.$row['quotation_id'].'"><i class="fa fa-print"></i></a>';
			$so_to_inv_btn='';
			if($row['with_out_stock_invoice']=="0"){
				$reserve_stock=reserve_stock($dbcon,$row['product_id'],$row['unit_id'],"","","",$row['sales_ordertrn_id'],"","","","","");
				if($reserve_stock>0){
					
					$so_to_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.''.FINANCE_ROOT.'invoiceso/'.$row['sales_ordertrn_id'].'"><i class="fa fa-plus-circle"></i>add Invoice</a>';
				}
			}else{
				$so_to_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceso/'.$row['sales_ordertrn_id'].'"><i class="fa fa-plus-circle"></i>add Invoice</a>';
			}
			//$so_to_inv_btn='<a class="btn btn-xs btn-primary" data-original-title="Add Invoice" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceso/'.$row['sales_ordertrn_id'].'"><i class="fa fa-plus-circle"></i>add Invoice</a>';
			
			//$row_data[] = $so_to_inv_btn;
			if(!empty($POST['inv_status'])){
				
				if($POST['inv_status']==$chval){
					$sho_per=1;
				}else{
					$sho_per=0;
				}
			}else{
				$sho_per=1;
			}

			if($igst_tax != ""){
				$tex = $igst_tax;
			}else{
				$tex = $cgst_tax.' '.$sgst_tax;
			}

			if($sho_per==1){
				$row_data[] = $row['sr'];
				$row_data[] = $row['sales_order_no'];
				$row_data[] = date('d M, Y',strtotime($row['sales_order_date']));
				$row_data[] = $row['l_name'];
				$row_data[] = $row['t_name'];
				$row_data[] = $row['product_name'];
				$row_data[] = $row['product_qty'];
				$row_data[] = $row['product_discount']." (".$row['discount_per']."%)";
				$row_data[] = $tex;
				$row_data[] = $row['product_amount'];
				$row_data[] = $status_btn;

				$appData[] = $row_data;
				$id++;
			}
			
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