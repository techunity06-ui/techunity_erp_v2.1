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
				
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			
			
				$str .='<table  class="display table table-bordered table-striped" id="">
					<thead>
					<tr id="logo" ">
						<td class="noborder" colspan="6" style="text-align:center;">
						<strong>'.$set_head['company_name'].'</strong></td>
					</tr>
					<tr>
						<td colspan="4" class="noborder"><strong>Party wise Outstanding Statement</strong></td>
						<td class="noborder">Date :'.date('d/m/Y').'</td>
					</tr>
					<tr>
					  <th  rowspan="2" width="2%" style="text-align:center;vertical-align:top;">Sr. NO.</th>
					  <th rowspan="2" width="40%" style="text-align:center;vertical-align:top;">Party Name</th>
					  <th rowspan="2" width="10%" style="text-align:center;vertical-align:top;">Phone No.</th>
					  <th colspan="2" width="10%" style="text-align:center">Closing Balance</th>
					</tr>
					<tr>
					 <th width="10%" style="text-align:center">Debit</th>
					  <th width="10%" style="text-align:center">Credit</th>
					</tr>
				</thead>
				 <tbody>';
			$where ="";
			if(!empty($POST['vender_id']))
			{
				$companyid = implode(',',$POST['vender_id']);
				$where .= "and cust.vender_id in (".$companyid.")";
			}
		 $qry='select vender_name,vender_mobile,opening_balance,balance_typeid,debitamount,creditamount from  tbl_vender as cust 
		left join 
		(select sum(g_total) as debitamount,invoice.vender_id from tbl_pono as invoice where invoice.status=0 and invoice.company_id='.$_SESSION['company_id'].' group by invoice.vender_id) as debitinvoice on debitinvoice.vender_id=cust.vender_id 
		left join 
		(select sum(paid_amount) as creditamount,rec.vender_id from tbl_purchasereceipt as rec where rec.status=0 and rec.company_id='.$_SESSION['company_id'].' group by rec.vender_id) as creditcust on creditcust.vender_id=cust.vender_id 
		where vender_status=0 and vendor_cat=1 and cust.company_id in (0,'.$_SESSION["company_id"].') '.$where;
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					$total=0;
					while($rel=mysqli_fetch_assoc($result1))
					{	
						 $op_balance=($rel['balance_typeid']=="1"?($rel['opening_balance']):-$rel['opening_balance']);
						$balance=($op_balance+$rel['debitamount'])-$rel['creditamount'];
						 $str.='<tr>
								<td style="text-align:center">'.$i.'</td>
								<td style="text-align:left">'.$rel["vender_name"].'</td>
								<td style="text-align:left">'.$rel["vender_mobile"].'</td>
								';
								//$str.=' <td style="text-align:center">'.abs($rel['opening_balance']).($rel['balance_typeid']=="1"?' CR':($rel['balance_typeid']=="2"?' DR':'')).'</td>';
								 //$str.='<td style="text-align:center">'.$rel['debitamount'].'</td><td style="text-align:center">'.$rel['creditamount'].'</td>';
									if($balance>0)
									{
										$balancetype='CR';
										$str.='<td></td><td style="text-align:right">'.number_format(abs($balance),2,".","").'</td>';
										$credittotal+=abs($balance);
									}
									else if($balance<0)
									{
											$balancetype='DR';
											$str.='<td style="text-align:right">'.number_format(abs($balance),2,".","").'</td><td></td>';
											$debittotal+=abs($balance);
									}
									else
									{
										$str.='<td></td><td></td>';
									}
						$str .='</tr>';
						$i++;
					}
					$str.='<tr>
								<td style="text-align:right" colspan="3"><b>Total</b></td>
								<td style="text-align:right">'.indian_number($debittotal,2).'</td>
								<td style="text-align:right">'.indian_number($credittotal,2).'</td></tr>';
				}
				else
				{
					$str .='<tr>
							<td colspan="6" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='</tbody>				 
				  </table>';
				  
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