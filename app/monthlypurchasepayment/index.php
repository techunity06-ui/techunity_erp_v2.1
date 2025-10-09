<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
include("../../config/image.php");
$image = new SimpleImage();
$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
		
if(strtolower($POST['mode']) == "generate_report") {
        $s_date = explode(' - ',$POST['date']);
        $set = "select * from tbl_company where company_id=".$_SESSION['company_id'];
        $set_head = brp_mysqli_fetch_assoc($dbcon->query($set));		
        $str .='
                <table  class="display table table-striped table-bordered" id="data_list">
                    <tr id="logo" class="logo" style="display:none">
                                <td colspan="8" style="text-align:center;">
                                        <strong>'.$set_head['company_name'].'</strong>
                                </td>
                        </tr>
                        <tr>
                                <td colspan="4"><strong>Monthly Purchase Payment Report</strong></td>
                                <td colspan="4" style="text-align:right">Date
                                <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
                    </tr>
                    <tr>
                            <th width="2%" style="text-align:center">Sr. NO.</th>
                            <th width="10%" style="text-align:center">Purchase No</th>
                             <th width="10%" style="text-align:center">Payment Date</th>
                             <th width="15%" style="text-align:center">Party Name</th>
                             <th width="15%" style="text-align:center">Payment Type</th>
                             <th width="20%" style="text-align:center">Bank Name/<br>Acc Name</th>
                             <th width="15%" style="text-align:center">Cheque No</th>
                            <th width="15%" style="text-align:center">Amount</th>
                    </tr>
                <tbody>';
				 
                if($POST['cust_id']!=""){
                        $wher="and receipt.cust_id=".$POST['cust_id']; 
                }else{
                        $wher="";
                }
                if($POST['paymode_id']!=""){
                        $where="and receipt.payment_mode_id=".$POST['paymode_id'];
                }else{
                        $where="";
                }
                $qry='Select cust.l_name as company_name,receipt_date,paymentmode.l_name as bank_name,paymentmode.acc_name,res_trn.paid_amount as receive,cheque_dtl,inv.po_no 
                    from tbl_receipt as receipt 
                    left join tbl_receipt_trn as res_trn on res_trn.receipt_id=receipt.receipt_id 
                    left join tbl_pono as inv on inv.po_id=res_trn.purchase_id 
                    left join tbl_ledger as paymentmode on paymentmode.l_id=receipt.payment_mode_id 
                    inner join tbl_ledger as cust on receipt.cust_id=cust.l_id 
                    where receipt.status=0 and res_trn.status=0 '.$wher.' 
                        and receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" 
                        and res_trn.purchase_id !=0 and receipt.company_id='.$_SESSION['company_id'].'
                    order by receipt_date';
                $result1 = $dbcon->query($qry);
                $i=1;
                if(mysqli_num_rows($result1)>0)
                {
                        $total=0;
                        while($re=mysqli_fetch_assoc($result1))
                        {                            
                                $str.='<tr>
                                    <td data-label="Sr. NO." style="text-align:center">'.$i.'</td>
                                    <td data-label="Sr. NO." style="text-align:center">'.$re["po_no"].'</td>
                                    <td data-label="Payment Date" style="text-align:center">'.date('d/m/Y',strtotime($re["receipt_date"])).'</td>';
                                $str .='<td data-label="Party Name" style="text-align:center">'.$re["company_name"].'</td>
                                    <td data-label="Payment Type" style="text-align:center">'.$re["bank_name"].'</td>';
							
                                if($re["acc_name"])
                                {
                                    $str.='<td data-label="Bank Name" style="text-align:center">'.$re['bank_name'].'<br/>'.$re["acc_name"].'</td>';
                                }  
                                else{
                                    $str.='<td data-label="Bank Name" style="text-align:center">'.$re['bank_name'].'</td>';
                                }
						  
                                if(!empty($re['cheque_dtl']))
                                {
                                    $str.='<td data-label="Cheque No" style="text-align:center">'.$re["cheque_dtl"].'</td>';
                                } else{
                                    $str.='<td data-label="Cheque No" style="text-align:center"> - </td>';
                                }
                                $str.='
                                  <td data-label="Amount" style="text-align:center">'.indian_number($re['receive'],2).'</td>	 
                                </tr>';	
                                $i++;
                                $total=$total+$re['receive'];
                        }
                        $str .="<tr>
                                    <td></td><td></td><td></td><td></td><td></td><td></td> 
                                    <td class='tfhide' style='text-align:right;'>Total:</td>
                                    <td data-label='Total' style='text-align:center'>".indian_number($total,2)."</td></tr>";
				}
				else
				{
					$str .='<tr>
							<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='</tbody>				 
				  </table>';
				  
			echo $str;
		}
		
?>