<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
		
if(strtolower($POST['mode']) == "generate_report") {
        $where='';
        $s_date=explode(' - ',$POST['date']);
        $str='';
        $companyName = $dbcon->query("SELECT company_name FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
            ->fetch_object()->company_name;
        
        if($POST['cust_id']){
                $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$POST['cust_id'])
                    ->fetch_object()->l_name;
                $where=' and inv.cust_id='.$POST["cust_id"];
        }	
		 	 
			 
				$str .='<div id="payment_detail">
				<table class="display table table-bordered table-striped" id="data_list">
				  <thead> 
					<tr>
						<td class="noborder" colspan="9" style="border:none;text-align: center;">
							<span id="head_logo"><strong style="">'.$companyName.'</strong></span>
						</td>
					</tr>
					<!--<tr>
						<td class="noborder" colspan="2" style="border:none"><strong>Sales Register</strong></td>
						<td class="noborder" style="border:none"><!--Customer Name: <strong>'.$ledger_name.'</strong>-->
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
                        $qry='select inv.*,cust.l_name as company_name,(select SUM(rtrn.paid_amount) as amuount from tbl_receipt_trn as rtrn where  rtrn.status=0 and  rtrn.purchase_id=inv.po_id group by rtrn.purchase_id ) paidamo 
                                from tbl_pono as inv
                                left join tbl_ledger as cust on cust.l_id = inv.vender_id
                                where inv.status=0  '.$cust.' '.$invo.'  and inv.po_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and inv.po_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and inv.company_id='.$_SESSION['company_id'].' 
                                order by inv.po_date desc';
			
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
						</tr>
						';
					$str.='<tfoot>';
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
		else if(strtolower($POST['mode'])== "load_quotation_sales_order")
		{
				$resp['so_html']= get_quotation_sales_order($dbcon,$POST['estimate_id']);
				
			
			echo json_encode($resp);
		}
		
?>