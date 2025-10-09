<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/coman_function.php");

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report") {
			$where='';
			$s_date=explode(' - ',$POST['date']);
			$str='';
			$set="SELECT * FROM `tbl_company` where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));	
			if($POST['cust_id']){
				$query1="select company_name from tbl_customer  where  cust_id=".$POST['cust_id'];
				$rel1=mysqli_fetch_assoc($dbcon->query($query1));
				$where=' and inv.cust_id='.$POST["cust_id"];
			}	
		 	 
			 
				$str .='<div id="payment_detail">
				<table class="display table table-bordered table-striped" id="data_list">
				  <thead> 
					<tr>
						<td class="noborder" colspan="11" style="border:none;text-align: center;">
							<span id="head_logo"><strong style="">'.$set_head['company_name'].'</strong></span>
						</td>
					</tr>
					<!--<tr>
						<td class="noborder" colspan="2" style="border:none"><strong>Sales Register</strong></td>
						<td class="noborder" style="border:none"><!--Customer Name: <strong>'.$rel1['company_name'].'</strong>-->
						<!--</td>
						<td class="noborder" colspan="2" style="text-align:right;border-top:none; border-left:none;border-bottom:none;"> 
						Date
						<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
						</td>
				
					</tr>-->
					
					<tr id="field">
							<th class="text-center" width="10%">Purchase No</th>
							<th class="text-center"width="10%">Date</th>
							<th class="text-center"width="20%">Company Name</th>
							<th class="text-center"width="10%">Purchase Amount</th>
							<th class="text-center"width="10%">Purchase Pay Amount</th>
							<th class="text-center"width="10%">Amount Due</th>
							<th class="text-center"width="10%">Due On</th>
							<th class="text-center"width="10%">OVERDUE BY DAYS</th>
							
						</tr>
				 
				 </thead>
				 <tbody>';
				
			if($POST['invoice_id']!=""){
				$invo=" and inv.po_id=".$POST['invoice_id'];
			}
			if($POST['cust_id']!=""){
				$cust=" and inv.vender_id=".$POST['cust_id'];
			}
			if($POST['pro_id']!=""){
				$pro=" and sotrn.product_id=".$POST['pro_id'];
			}
			$qry='select inv.*,cust.company_name,todo.date,todo.ref_table,(select IFNULL(SUM(rtrn.paid_amount),0) as amuount from tbl_receipt_trn as rtrn where  rtrn.status=0 and  rtrn.purchase_id=inv.po_id group by rtrn.purchase_id ) paidamo from tbl_pono as inv
			left join tbl_customer as cust on cust.cust_id=inv.vender_id
			inner join todo_mst as todo on inv.po_id=todo.ref_id and  todo.ref_table="tbl_pono"
			where inv.status=0  '.$cust.' '.$invo.'  and inv.g_total > (select IFNULL(SUM(rtrn.paid_amount),0) as amuount from tbl_receipt_trn as rtrn where  rtrn.status=0 and  rtrn.purchase_id=inv.po_id) and inv.po_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and inv.po_date<="'.date('Y-m-d',strtotime($s_date[1])).'"  and inv.company_id='.$_SESSION['company_id'].' order by inv.po_date desc';
			
				/*$qry='select inv.*,cust.company_name, SUM(res_trn.paid_amount) as amount from tbl_pono as inv
			left join tbl_customer as cust on cust.cust_id=inv.vender_id
			left join tbl_receipt_trn as res_trn on res_trn.purchase_id=inv.po_id
			where inv.status=0  '.$cust.' '.$invo.' and res_trn.status=0 and inv.po_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and inv.po_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and inv.company_id='.$_SESSION['company_id'].' group by res_trn.purchase_id';*/
			
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					$total=0;$arr=array();
					$estimate_date="";$sales_order_date="";$invoice_date="";
					$invamounttotal=0;
					$invdueamounttotal=0;
					$invpendindtotal=0;
					while($re=mysqli_fetch_assoc($result1))
					{
						$tamount=$re['g_total'];
						$due=$tamount-$re['paidamo'];
						$then = $re["date"];
 
//Convert it into a timestamp.
$then = strtotime($then);
 
//Get the current timestamp.
$now = time();
 
//Calculate the difference.
$difference = $now - $then;
 
//Convert seconds into days.
$days = floor($difference / (60*60*24) );
						$str.='<tr id="fieldtr'.$id.'" >
								<td style="vertical-align:top;">
									'.$re['po_no'].'
									<input type="hidden" name="o_invoice_id[]" id="o_invoice_id" value="'.$rel['invoice_id'].'" />
								</td>
								
								<td style="vertical-align:top;" class="text-center">
									'.date('d-m-Y',strtotime($re['po_date'])).'
								</td>
								<td style="vertical-align:top;" class="text-center">
									'.$re['company_name'].'
								</td>
								<td style="vertical-align:top;" class="text-center">
									'.$re['g_total'].'
								</td>
								<td style="vertical-align:top;" class="text-center">
									'.$re['paidamo'].'
								</td>
								<td style="vertical-align:top;" class="text-center">
									'.$due.'
								</td>
								<td style="vertical-align:top;" class="text-center">
									'.date('d/m/Y',strtotime($re["date"])).'
								</td>
								<td style="vertical-align:top;" class="text-center">
									'.$days.'
								</td>
								
						</tr>';
						$invamounttotal=$invamounttotal+$re['g_total'];
						$invdueamounttotal=$invdueamounttotal+$re['paidamo'];
						$invpendindtotal=$invpendindtotal+$due;
						
						
						$i++;
					}
						$str.='
						<tr>
							<td colspan="3" class="text-right"><strong>Total</strong></td>
							<td class="text-center"><strong>'.$invamounttotal.'</strong></td>
							<td class="text-center"><strong>'.$invdueamounttotal.'</strong></td>
							<td class="text-center"><strong>'.$invpendindtotal.'</strong></td>
							<td colspan="2" class="text-right"></td>
						</tr>
						';
					$str.='<tfoot>';
				}
				else
				{
					$str .='<tr>
								<td colspan="11" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='</tbody>				 
				  </table>
				  </div>';
				  
			echo $str;
		}
		else if(strtolower($POST['mode'])== "load_quotation_sales_order")
		{
				$resp['so_html']= get_quotation_sales_order($dbcon,$POST['estimate_id']);
				
			
			echo json_encode($resp);
		}
		
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
?>