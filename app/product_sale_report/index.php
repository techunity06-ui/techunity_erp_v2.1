<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
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
                        
                        $where = '';
                        if($POST['cust_id']!=""){
                           $where = " and inv.cust_id=".$POST['cust_id']; 
                        }
                        
			$str='';
			$set="SELECT * FROM `tbl_company` where company_id=".$_SESSION['company_id'];
			$set_head= brp_mysqli_fetch_assoc($dbcon->query($set));		
		 	$query1="select  l_name  from tbl_ledger where  l_id=".$POST['cust_id'];
                        $ledger = brp_mysqli_fetch_assoc($dbcon->query($query1));
                        $str .='<div id="report" class="col-md-12" name="report" width="100%">
                            <table  class="col-md-12 display table table12 table-bordered table-striped" id="data_list">
                            <thead class="resdisplay"> 
                                <tr>
                                    <td class="noborder" colspan="9" style="border:none;text-align: center;">
                                        <span id="head_logo"><strong style="">'.$set_head['company_name'].'</strong></span></td>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="noborder" colspan="3" style="border:none"><strong>Party wise Sale</strong></td>
                                    <td class="noborder" colspan="2" style="border:none">Customer Name: <strong>'.($ledger['l_name'] ? $ledger['l_name'] : 'All' ).'</strong></td>
                                    <td class="noborder" colspan="4" style="text-align:right;border:none"> 
                                        Date <label> : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> 
                                        To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
                                    </td>
                                </tr>

                            <tr>
                                <th width="8%" style="text-align:center">Bill NO.</th>
                                <th width="5%" style="text-align:left">Date</th>
                                <th width="50%" colspan="2" style="text-align:left">Item Description</th>
                                <th width="4%" style="text-align:center">Quantity </th>
                                <th width="8%" style="text-align:right;">Rate </th>
                                 <th width="5%" style="text-align:center">Dis.% </th>
                                <th width="7%" style="text-align:right">Amount</th>
                                <th width="12%" style="text-align:right">Bill Amount</th>
                            </tr>
                            </thead>
                            <tbody>';
				
			$qry='SELECT invoice_no,invoice_date,product_name,product_qty,trn.product_rate,trn.product_discount,trn.product_amount,trn.total 
                                FROM tbl_invoice as inv 
			left join tbl_invoicetrn as trn on trn.invoice_id=inv.invoice_id 
			left join product_mst as pdt on pdt.product_id=trn.product_id
			where inv.invoice_status!=2 AND trancation_status=0 '.$where.'
                        AND inv.invoice_date between "'.date('Y-m-d',strtotime($s_date[0])).'" and "'.date('Y-m-d',strtotime($s_date[1])).'" 
                        and inv.company_id='.$_SESSION['company_id'].'
                        order by invoice_date';
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					$total=0;$arr=array();
					while($re=mysqli_fetch_assoc($result1))
					{	
						$arr[]=$re["invoice_no"];
						$str.='<tr>
                                                    <td data-label="Bill NO." style="text-align:left" class="">'.$re["invoice_no"].'</td>
                                                    <td data-label="Date" style="text-align:left" class="">'.date("d/m/y",strtotime($re["invoice_date"])).'</td>
                                                    <td data-label="Item Description" style="text-align:" colspan="2" class="">'.$re["product_name"].'</td>
                                                    <td data-label="Quantity" style="text-align:right" class="">'.$re["product_qty"].'</td>
                                                    <td data-label="Rate" style="text-align:right" class="">'.$re["product_rate"].'</td>
                                                    <td data-label="Dis.% " style="text-align:right" class="">'.$re["product_discount"].'</td>
                                                    <td data-label="Amount" style="text-align:right" class="">'.$re["product_amount"].'</td>';
						   if(in_array($re["invoice_no"],$arr))
						  {
                                                        $subtotal = $re["product_qty"] * $re["product_rate"];
                                                        if($re["product_discount"]){
                                                            $subtotal = $subtotal - $re["product_disc"];
                                                        }
							$str.=' <td data-label="Bill Amount" style="text-align:right" class="">'.indian_number($subtotal,2).'</td>';
							$total = $total + $subtotal; 
							
					 	  }
						else {
                                                        $subtotal = $re["product_qty"] * $re["product_rate"];
                                                        if($re["product_discount"]){
                                                            $subtotal = $subtotal - $re["product_discount"];
                                                        }
                                                        $str.=' <td  data-label="Bill Amount" style="text-align:right" class="">'.indian_number($subtotal,2).'</td>';
						}
						
						$i++;
					}
					$str.='<tfoot><tr>
						  <td class="tfhide" style="text-align:right" colspan="8"><b>Total</b></td>
						  <td data-label="Total" style="text-align:right">'.indian_number($total,2).'</td></tr></tfoot>';
				}
				else
				{
					$str .='<tr>
							<td colspan="9" style="text-align:center">NO DATA FOUND  </td>
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