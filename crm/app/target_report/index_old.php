<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/coman_function.php");
include("../../config/image.php");
$image = new SimpleImage();
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
			$s_date=explode(' - ',$POST['date']);
			$str='';
			$set="SELECT * FROM `tbl_company` where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
		 	$query1="select  company_name  from tbl_customer  where  cust_id=".$POST['cust_id'];
			 $rel1=mysqli_fetch_assoc($dbcon->query($query1));
				$str .='<div id="payment_detail">
				<table  class="display table table-bordered table-striped" id="data_list">
				  <thead> 
					<tr><td class="noborder" colspan="20" style="border:none;text-align: center;">
						<span id="head_logo"><strong style="">'.$set_head['company_name'].'</strong></span></td>
						<td colspan="20" style="text-align: center;">
						<strong style="">'.$set_head['company_name'].'</strong></span>
						</td>
					</td>
					</tr>
					<tr>
						<td class="" colspan="4" style="border-left:none;border-right:none"><strong>Target Report</strong></td>
						<td class="" colspan="10" style="border-left:none;border-right:none"></td>
						<td class="" colspan="6" style="text-align:right;border-left:none;border-right:none"> 
						Date
					<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
					</td>
					<td class="" colspan="4" style="border-right:none"><strong>Target Report</strong></td>
						<td class="" colspan="10" style="border-left:none;border-right:none"></td>
						<td class="" colspan="6" style="border-left:none;border-right:none"> 
						Date
					<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
					</td>
					<!--<td colspan="20" >
					</td>-->
				
					</tr>
					
				  <tr>
					  <th  style="text-align:left">Name</th>
					  <th  style="text-align:left">APR TARGET</th>
					  <th  style="text-align:left">APR</th>
					  <th  style="text-align:left">APR OUT </th>
					  <th  style="text-align:left;">MAY TARGET </th>
					  <th  style="text-align:left">MAY</th>
					  <th  style="text-align:left">MAY OUT</th>
					  <th  style="text-align:left">JUN TARGET</th>
					  <th  style="text-align:left">JUN</th>
					  <th  style="text-align:left">JUN OUT</th>
					  <th  style="text-align:left">JUL TARGET</th>
					  <th  style="text-align:left">JUL</th>
					  <th  style="text-align:left">JUL OUT</th>
					  <th  style="text-align:left">AUG TARGET</th>
					  <th  style="text-align:left">AUG</th>
					  <th  style="text-align:left">AUG OUT</th>
					  <th  style="text-align:left">SEP TARGET</th>
					  <th  style="text-align:left">SEP</th>
					  <th  style="text-align:left">SEP OUT</th>
					  <th  style="text-align:left">OCT TARGET</th>
					  <th  style="text-align:left">OCT</th>
					  <th  style="text-align:left">OCT OUT</th>
					  <th  style="text-align:left">NOV TARGET</th>
					  <th  style="text-align:left">NOV</th>
					  <th  style="text-align:left">NOV OUT</th>
					  <th  style="text-align:left">DECC TARGET</th>
					  <th  style="text-align:left">DECC</th>
					  <th  style="text-align:left">DECC OUT</th>
					  <th  style="text-align:left">JAN TARGET</th>
					  <th  style="text-align:left">JAN</th>
					  <th  style="text-align:left">JAN OUT</th>
					  <th  style="text-align:left">FEB TARGET</th>
					  <th  style="text-align:left">FEB</th>
					  <th  style="text-align:left">FEB OUT</th>
					  <th  style="text-align:left">MAR TARGET</th>
					  <th  style="text-align:left">MAR</th>
					  <th  style="text-align:left">MAR OUT</th>
					  <th  style="text-align:left">Target</th>
					  <th  style="text-align:left">Achived Target</th>
					  <th  style="text-align:left">Pening Traget</th>
				</tr>
				 
				 </thead>
				 <tbody>';
				
			  //$qry='Select cust.company_name,sum(product_qty) as productqty from tbl_tranction as trn inner join tbl_invoice as invoice on invoice.invoice_id=trn.invoice_id left join tbl_customer as cust on cust.cust_id=invoice.cust_id where trn.product_id='.$POST['product_id'].' and trancation_status=0 and  invoice_status=0  and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'" group by cust.cust_id';
			  /* $qry='SELECT po_no,po_date,product_name,product_qty,product_rate,product_disc,product_amount,g_total
				FROM `tbl_pono` as po
				left join tbl_potrancation as trn on trn.po_id=po.po_id
				left join tbl_product as pdt on pdt.product_id=trn.product_id
				where po_date between "'.date('Y-m-d',strtotime($s_date[0])).'" and "'.date('Y-m-d',strtotime($s_date[1])).'" and po.status!=2 and po.company_id='.$_SESSION['company_id'].' and po.vender_id='.$POST["cust_id"].' order by po_date'; */
			echo	$qry="SELECT name,APR_TARGET,APR,APR_OUT,MAY_TARGET,MAY,MAY_OUT,JUN_TARGET,JUN,JUN_OUT,JUL_TARGET,JUL,JUL_OUT,AUG_TARGET,AUG,AUG_OUT,SEP_TARGET,
SEP,SEP_OUT,OCT_TARGET,OCT,OCT_OUT,NOV_TARGET,NOV,NOV_OUT,DECC_TARGET,DECC,DECC_OUT,JAN_TARGET,JAN,JAN_OUT,FEB_TARGET,FEB,FEB_OUT,MAR_TARGET,MAR,MAR_OUT,
(APR_TARGET+MAY_TARGET+JUN_TARGET+JUL_TARGET+AUG_TARGET+SEP_TARGET+OCT_TARGET+NOV_TARGET+DECC_TARGET+JAN_TARGET+FEB_TARGET+MAR_TARGET) as Target ,Achived_Target,(APR_TARGET+MAY_TARGET+JUN_TARGET+JUL_TARGET+AUG_TARGET+SEP_TARGET+OCT_TARGET+NOV_TARGET+DECC_TARGET+JAN_TARGET+FEB_TARGET+MAR_TARGET)-Achived_Target as Pening_Traget from(
select cst.company_name as name,cst.customer_target as APR_TARGET, 
sum(case when MONTH(invs.invoice_date)= 4 then invs.g_total else 0 end) 'APR',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 4 then invs.g_total else 0 end) 'APR_OUT',
cst.customer_target as MAY_TARGET,
sum(case when MONTH(invs.invoice_date)= 5 then invs.g_total else 0 end) 'MAY',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 5 then invs.g_total else 0 end) 'MAY_OUT',
cst.customer_target as JUN_TARGET,
sum(case when MONTH(invs.invoice_date)= 6 then invs.g_total else 0 end) 'JUN',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 6 then invs.g_total else 0 end) 'JUN_OUT',
cst.customer_target as JUL_TARGET,
sum(case when MONTH(invs.invoice_date)= 7 then invs.g_total else 0 end) 'JUL',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 7 then invs.g_total else 0 end) 'JUL_OUT',
cst.customer_target as AUG_TARGET,
sum(case when MONTH(invs.invoice_date)= 8 then invs.g_total else 0 end) 'AUG',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 8 then invs.g_total else 0 end) 'AUG_OUT', 
cst.customer_target as SEP_TARGET,
sum(case when MONTH(invs.invoice_date)= 9 then invs.g_total else 0 end) 'SEP',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 9 then invs.g_total else 0 end) 'SEP_OUT', 
cst.customer_target as OCT_TARGET,
sum(case when MONTH(invs.invoice_date)= 10 then invs.g_total else 0 end) 'OCT',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 10 then invs.g_total else 0 end) 'OCT_OUT', 
cst.customer_target as NOV_TARGET,
sum(case when MONTH(invs.invoice_date)= 11 then invs.g_total else 0 end) 'NOV',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 11 then invs.g_total else 0 end) 'NOV_OUT', 
cst.customer_target as DECC_TARGET,
sum(case when MONTH(invs.invoice_date)= 12 then invs.g_total else 0 end) 'DECC',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 12 then invs.g_total else 0 end) 'DECC_OUT', 
cst.customer_target as JAN_TARGET,
sum(case when MONTH(invs.invoice_date)= 1 then invs.g_total else 0 end) 'JAN',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 1 then invs.g_total else 0 end) 'JAN_OUT', 
cst.customer_target as FEB_TARGET,
sum(case when MONTH(invs.invoice_date)= 2 then invs.g_total else 0 end) 'FEB',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 2 then invs.g_total else 0 end) 'FEB_OUT', 
cst.customer_target as MAR_TARGET,
sum(case when MONTH(invs.invoice_date)= 3 then invs.g_total else 0 end) 'MAR',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 3 then invs.g_total else 0 end) 'MAR_OUT',     
sum(invs.g_total) 'Achived_Target'
from tbl_customer cst
LEFT join ( select invs.cust_id,invs.invoice_date,invs.g_total from tbl_invoice as invs where invs.invoice_date>='2020-04-1' and invs.invoice_date <='2021-03-31' ) as invs on invs.cust_id = cst.cust_id
where cst.customer_target!=0 and cst.party_type = 1
GROUP   BY invs.cust_id) as a
Union ALL
select  'Total', sum(APR_TARGET),sum(APR),sum(APR_OUT),sum(MAY_TARGET),sum(MAY),sum(MAY_OUT),sum(JUN_TARGET),sum(JUN),sum(JUN_OUT),sum(JUL_TARGET),sum(JUL),sum(JUL_OUT),sum(AUG_TARGET),sum(AUG),sum(AUG_OUT),sum(SEP_TARGET),
sum(SEP),sum(SEP_OUT),sum(OCT_TARGET),sum(OCT),sum(OCT_OUT),sum(NOV_TARGET),sum(NOV),sum(NOV_OUT),sum(DECC_TARGET),sum(DECC),sum(DECC_OUT),sum(JAN_TARGET),sum(JAN),sum(JAN_OUT),sum(FEB_TARGET),sum(FEB),sum(FEB_OUT),sum(MAR_TARGET),sum(MAR),sum(MAR_OUT),
sum(Target) ,sum(Achived_Target),sum(Pening_Traget) from (
    SELECT name,APR_TARGET,APR,APR_OUT,MAY_TARGET,MAY,MAY_OUT,JUN_TARGET,JUN,JUN_OUT,JUL_TARGET,JUL,JUL_OUT,AUG_TARGET,AUG,AUG_OUT,SEP_TARGET,
SEP,SEP_OUT,OCT_TARGET,OCT,OCT_OUT,NOV_TARGET,NOV,NOV_OUT,DECC_TARGET,DECC,DECC_OUT,JAN_TARGET,JAN,JAN_OUT,FEB_TARGET,FEB,FEB_OUT,MAR_TARGET,MAR,MAR_OUT,
(APR_TARGET+MAY_TARGET+JUN_TARGET+JUL_TARGET+AUG_TARGET+SEP_TARGET+OCT_TARGET+NOV_TARGET+DECC_TARGET+JAN_TARGET+FEB_TARGET+MAR_TARGET) as Target ,Achived_Target,(APR_TARGET+MAY_TARGET+JUN_TARGET+JUL_TARGET+AUG_TARGET+SEP_TARGET+OCT_TARGET+NOV_TARGET+DECC_TARGET+JAN_TARGET+FEB_TARGET+MAR_TARGET)-Achived_Target as Pening_Traget from(
select cst.company_name as name,cst.customer_target as APR_TARGET, 
sum(case when MONTH(invs.invoice_date)= 4 then invs.g_total else 0 end) 'APR',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 4 then invs.g_total else 0 end) 'APR_OUT',
cst.customer_target as MAY_TARGET,
sum(case when MONTH(invs.invoice_date)= 5 then invs.g_total else 0 end) 'MAY',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 5 then invs.g_total else 0 end) 'MAY_OUT',
cst.customer_target as JUN_TARGET,
sum(case when MONTH(invs.invoice_date)= 6 then invs.g_total else 0 end) 'JUN',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 6 then invs.g_total else 0 end) 'JUN_OUT',
cst.customer_target as JUL_TARGET,
sum(case when MONTH(invs.invoice_date)= 7 then invs.g_total else 0 end) 'JUL',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 7 then invs.g_total else 0 end) 'JUL_OUT',
cst.customer_target as AUG_TARGET,
sum(case when MONTH(invs.invoice_date)= 8 then invs.g_total else 0 end) 'AUG',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 8 then invs.g_total else 0 end) 'AUG_OUT', 
cst.customer_target as SEP_TARGET,
sum(case when MONTH(invs.invoice_date)= 9 then invs.g_total else 0 end) 'SEP',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 9 then invs.g_total else 0 end) 'SEP_OUT', 
cst.customer_target as OCT_TARGET,
sum(case when MONTH(invs.invoice_date)= 10 then invs.g_total else 0 end) 'OCT',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 10 then invs.g_total else 0 end) 'OCT_OUT', 
cst.customer_target as NOV_TARGET,
sum(case when MONTH(invs.invoice_date)= 11 then invs.g_total else 0 end) 'NOV',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 11 then invs.g_total else 0 end) 'NOV_OUT', 
cst.customer_target as DECC_TARGET,
sum(case when MONTH(invs.invoice_date)= 12 then invs.g_total else 0 end) 'DECC',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 12 then invs.g_total else 0 end) 'DECC_OUT', 
cst.customer_target as JAN_TARGET,
sum(case when MONTH(invs.invoice_date)= 1 then invs.g_total else 0 end) 'JAN',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 1 then invs.g_total else 0 end) 'JAN_OUT', 
cst.customer_target as FEB_TARGET,
sum(case when MONTH(invs.invoice_date)= 2 then invs.g_total else 0 end) 'FEB',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 2 then invs.g_total else 0 end) 'FEB_OUT', 
cst.customer_target as MAR_TARGET,
sum(case when MONTH(invs.invoice_date)= 3 then invs.g_total else 0 end) 'MAR',
cst.customer_target-sum(case when MONTH(invs.invoice_date)= 3 then invs.g_total else 0 end) 'MAR_OUT',     
sum(invs.g_total) 'Achived_Target'
from tbl_customer cst
LEFT join ( select invs.cust_id,invs.invoice_date,invs.g_total from tbl_invoice as invs where invs.invoice_date>='".date('Y-m-d',strtotime($s_date[0]))."' and invs.invoice_date <='".date('Y-m-d',strtotime($s_date[1]))."' ) as invs on invs.cust_id = cst.cust_id
where cst.customer_target!=0 and cst.party_type = 1
GROUP   BY invs.cust_id) as a ) as b group by 1";
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					$total=0;$arr=array();
					while($re=mysqli_fetch_assoc($result1))
					{	
						$str.='
						<tr>
							<td style="text-align:center" class="">'.$re["name"].'</td>
							<td style="text-align:center" class="">'.$re["APR_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["APR"].'</td>
							<td style="text-align:center" class="">'.$re["APR_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["MAY_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["MAY"].'</td>
							<td style="text-align:center" class="">'.$re["MAY_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["JUN_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["JUN"].'</td>
							<td style="text-align:center" class="">'.$re["JUN_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["JUL_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["JUL"].'</td>
							<td style="text-align:center" class="">'.$re["JUL_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["AUG_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["AUG"].'</td>
							<td style="text-align:center" class="">'.$re["AUG_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["SEP_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["SEP"].'</td>
							<td style="text-align:center" class="">'.$re["SEP_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["OCT_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["OCT"].'</td>
							<td style="text-align:center" class="">'.$re["OCT_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["NOV_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["NOV"].'</td>
							<td style="text-align:center" class="">'.$re["NOV_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["DECC_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["DECC"].'</td>
							<td style="text-align:center" class="">'.$re["DECC_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["JAN_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["JAN"].'</td>
							<td style="text-align:center" class="">'.$re["JAN_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["FEB_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["FEB"].'</td>
							<td style="text-align:center" class="">'.$re["FEB_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["MAR_TARGET"].'</td>
							<td style="text-align:center" class="">'.$re["MAR"].'</td>
							<td style="text-align:center" class="">'.$re["MAR_OUT"].'</td>
							<td style="text-align:center" class="">'.$re["Target"].'</td>
							<td style="text-align:center" class="">'.$re["Achived_Target"].'</td>
							<td style="text-align:center" class="">'.$re["Pening_Traget"].'</td>
						  
						</tr>';
						  
						
						$i++;
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="40" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='</tbody>				 
				  </table>
				  </div>';
				  
			echo $str;
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