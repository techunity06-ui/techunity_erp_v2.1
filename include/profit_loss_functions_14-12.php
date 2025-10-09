<?php
function closing_stock_value_details($closing_stock){
	$str='';
        $str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        $str.='
                <tr>
                    <td>Stock</td>
                    <td>'. indian_number((float)$closing_stock, 2).'</td>
                </tr>
            ';
	$str.="</table>"; 
	return $str;
										
}
function opening_stock_value_details($dbcon,$start_date){
    $inventory_management = $dbcon->query("SELECT inventory_management FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
                ->fetch_object()->inventory_management;

    if($inventory_management){
        //echo 'inventory - yes';
        $opning_balance = $dbcon->query("SELECT closing_balance as op_bal FROM tbl_closing_balance tcb
                LEFT join tbl_ledger as led ON led.l_id= tcb.ledger_id 
                LEFT join tbl_group as gro ON gro.g_id=led.l_group
                WHERE tcb.closing_balance_date <= '".$start_date."' AND tcb.status = ".ACTIVE." 
                    AND led.l_group = ".STOCK_IN_HAND." AND led.l_status = ".ACTIVE." 
                ORDER BY closing_balance_date DESC LIMIT 1")
                    ->fetch_object()->op_bal;

    } else {
        //SELECT `product_name`,`product_purchase_mst_rate`,`product_stock` FROM `tbl_product` WHERE `product_status`=0 and `product_type`!=3
        $qry122="SELECT SUM(product_stock) as op_value, AVG(product_purchase_mst_rate) as rate 
                    FROM tbl_product as cert 
                    WHERE product_status = ".ACTIVE." 
                        AND product_type != ".CHARGES." 
                        AND company_id = ".$_SESSION['company_id'];
        $ro3=$dbcon->query($qry122);
        $re3=mysqli_fetch_assoc($ro3);
        $opbal= round($re3['op_value']*$re3['rate']);
        //$opbal=round($opbal);

        $qry1="SELECT sum(cert.product_amount) as purchase_value 
                FROM tbl_potrancation as cert 
                LEFT JOIN tbl_product as pro ON pro.product_id=cert.product_id
                LEFT JOIN tbl_pono as po ON po.po_id=cert.po_id
                LEFT JOIN tbl_ledger as led ON led.l_id=po.purchase_ledger_id
            LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group
            where potrancation_status = ".ACTIVE."
                and gro.g_id = ".PURCHASE_ACCOUNTS."
                and po.po_date <= ".$start_date." 
                and pro.product_type != ".CHARGES ;
        //echo $qry1;
        $ro=$dbcon->query($qry1);
        $re=mysqli_fetch_assoc($ro);
	
        $qry12="SELECT SUM(cert.product_amount) as sales_value 
                FROM tbl_invoicetrn as cert 
                LEFT JOIN tbl_product as pro ON pro.product_id = cert.product_id
                LEFT JOIN tbl_invoice as po ON po.invoice_id = cert.invoice_id
                LEFT JOIN tbl_ledger as led ON led.l_id = po.sales_ledger_id
                LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                WHERE trancation_status = ".ACTIVE."
                    AND gro.g_id = ".SALES_ACCOUNTS." 
                    AND po.invoice_date <= ".$start_date." 
                    AND pro.product_type != ".CHARGES ;
        //echo '<br/>'.$qry12;
        $ro1=$dbcon->query($qry12);
        $re1=mysqli_fetch_assoc($ro1);
        $opning_balance= round(($opbal+$re['purchase_value'])-$re1['sales_value']);
    }
    $str = '';
    $str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
    $str.='
            <tr>
                    <td>Stock</td>
                    <td>'.number_format((float)$opning_balance, 2).'</td>
            </tr>
            ';
    $str.="</table>"; 
    return $str;
}

function sales_ac_value_details($dbcon,$where_date) {
    /*$qry1="select GROUP_CONCAT(led.l_id) as leger_id1 from tbl_invoicetrn as cert 
        LEFT JOIN tbl_product as pro on pro.product_id=cert.product_id
            LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id
            LEFT JOIN tbl_ledger as led on led.l_id=po.sales_ledger_id
            LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
            where trancation_status=0 and gro.g_id=25 and po.invoice_date ".$where_date." 
                and pro.product_type!=3 
            group by led.l_id 
            order by led.l_id
     */
    
        // updated by Dimple Panchal
        $sales_ledger_ids = $dbcon->query("SELECT GROUP_CONCAT(l_id) as sales_ids FROM `tbl_ledger` 
            WHERE `l_group` = ".SALES_ACCOUNTS." AND l_status = ".ACTIVE." AND company_id=".$_SESSION['company_id'])
                ->fetch_object()->sales_ids;
        
//        $array1= array();
//	if(!empty($re['leger_id1'])){
//		array_push($array1,$re['leger_id1']);
//	}
//	$led_id= implode(",",$array1); 
		
        /*$query="SELECT sales_value,l_name 
                FROM `tbl_ledger` as pro 
                LEFT JOIN (SELECT sum(cgen.amount) as sales_value,led.l_id 
                    FROM tbl_general_book as cgen
                    LEFT JOIN tbl_invoicetrn as cert ON cert.invoice_id = cgen.table_id
                    LEFT JOIN tbl_product as pro ON pro.product_id = cert.product_id
                    LEFT JOIN tbl_invoice as po ON po.invoice_id = cert.invoice_id
                    LEFT JOIN tbl_ledger as led ON led.l_id = po.sales_ledger_id
                    LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                    WHERE trancation_status = ".ACTIVE." 
                        AND gro.g_id = ".SALES_ACCOUNTS." 
                        AND po.invoice_date ".$where_date." 
                        AND pro.product_type != ".CHARGES."
                        AND cgen.entry_type = ".DEBIT."
                        AND po.sales_ledger_id in (".$sales_ledger_ids.")
                        GROUP BY led.l_id 
                        ORDER BY led.l_id) as genbook ON genbook.l_id=pro.l_id
                WHERE pro.l_status != ".DELETED." 
                    AND pro.l_id in (".$sales_ledger_ids.") 
                    AND company_id=".$_SESSION['company_id']." 
                GROUP BY pro.l_id 
                ORDER BY l_name";*/
        $query = "SELECT SUM(cert.product_amount) as sales_value, led.l_name
                FROM tbl_invoicetrn as cert 
                LEFT JOIN tbl_product as pro on pro.product_id=cert.product_id
                LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id
                LEFT JOIN tbl_ledger as led on led.l_id=po.sales_ledger_id
                LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                WHERE trancation_status = ".ACTIVE." 
                    AND led.l_status = ".ACTIVE."
                    AND led.l_group = ".SALES_ACCOUNTS." 
                    AND led.l_id in (".$sales_ledger_ids.")
                    AND po.invoice_date ".$where_date." 
                    AND pro.product_type != ".CHARGES ;
        //echo '<br/>'.$query;
		
        $result=$dbcon->query($query);
        $str="";
        $str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        while($row=mysqli_fetch_assoc($result)){
                //$amount=$row["sales_value"];
                $amount= number_format((float)$row["sales_value"], 2, '.', '');
                $str.='
                        <tr>
                                <td>'.$row["l_name"].'</td>
                                <td>'.number_format((float)$amount, 2).'</td>
                        </tr>
                ';

        }
        $str.="</table>"; 
    return $str;
}
function purchase_ac_value_details($dbcon,$where_date){
//        $qry1="SELECT GROUP_CONCAT(led.l_id) as leger_id1 FROM tbl_potrancation as cert 
//                    LEFT JOIN tbl_product as pro on pro.product_id = cert.product_id
//                    LEFT JOIN tbl_pono as po on po.po_id = cert.po_id
//                    LEFT JOIN tbl_ledger as led on led.l_id = po.purchase_ledger_id
//                    LEFT JOIN tbl_group as gro on gro.g_id = led.l_group
//                WHERE potrancation_status=0 
//                    AND gro.g_id=24 
//                    AND po.po_date ".$where_date." 
//                    AND pro.product_type!=3 
//                GROUP BY led.l_id 
//                ORDER BY led.l_id";
                
        // updated query by Dimple Panchal
        $qry1="SELECT GROUP_CONCAT(ledgerids.l_id) as leger_id1 FROM
                (SELECT l_id from tbl_potrancation as cert 
                    LEFT JOIN tbl_product as pro on pro.product_id = cert.product_id
                    LEFT JOIN tbl_pono as po on po.po_id = cert.po_id
                    LEFT JOIN tbl_ledger as led on led.l_id = po.purchase_ledger_id
                    LEFT JOIN tbl_group as gro on gro.g_id = led.l_group
                WHERE potrancation_status = ".ACTIVE." 
                    AND gro.g_id = ".PURCHASE_ACCOUNTS."
                    AND po.po_date ".$where_date." 
                    AND pro.product_type != ".CHARGES." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) as ledgerids";
        //echo $qry1;
        $ro=$dbcon->query($qry1);
        $re=mysqli_fetch_assoc($ro);
        
        $qry2 = "SELECT GROUP_CONCAT(ledgerids.l_id) as leger_id2 FROM  
            (SELECT led.l_id 
                FROM tbl_journal_trn as cert 
                LEFT JOIN tbl_journal as jou on jou.journal_id = cert.journal_id
                LEFT JOIN tbl_ledger as led on led.l_id = cert.ledger_id
                LEFT JOIN tbl_group as gro on gro.g_id = led.l_group
                WHERE journal_trn_status = ".ACTIVE." 
                    AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                    AND entry_type = ".DEBIT." 
                    AND jou.journal_date ".$where_date." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) as ledgerids";
        $ro1=$dbcon->query($qry2);
        $re1=mysqli_fetch_assoc($ro1);
	
	$array1= array();
	if(!empty($re['leger_id1'])){
		array_push($array1,$re['leger_id1']);
	}
        if(!empty($re1['leger_id2'])){
		array_push($array1,$re1['leger_id2']);
	}
	$led_id= implode(",",$array1); 
		
        //echo '<pre>';
        $query="SELECT purchase_value,l_name,in_jo_value 
                FROM `tbl_ledger` as pro 
                LEFT JOIN (SELECT SUM(cert.product_amount) as purchase_value,led.l_id 
                    FROM tbl_potrancation as cert 
                    LEFT JOIN tbl_product as pro ON pro.product_id = cert.product_id
                    LEFT JOIN tbl_pono as po ON po.po_id = cert.po_id
                    LEFT JOIN tbl_ledger as led ON led.l_id = po.purchase_ledger_id
                    LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                    WHERE potrancation_status = ".ACTIVE."
                        AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                        AND po.po_date ".$where_date." 
                        AND pro.product_type != ".CHARGES." 
                    GROUP BY led.l_id 
                    ORDER BY led.l_id) as genbook ON genbook.l_id = pro.l_id
                    
                LEFT JOIN (SELECT SUM(cert.amount) as in_jo_value,led.l_id 
                        FROM tbl_journal_trn as cert 
                        LEFT JOIN tbl_journal as jou ON jou.journal_id = cert.journal_id
                        LEFT JOIN tbl_ledger as led ON led.l_id = cert.ledger_id
                        LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                        WHERE journal_trn_status = ".ACTIVE." 
                            AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                            AND entry_type = ".DR." 
                            AND jou.journal_date ".$where_date." 
                        GROUP BY led.l_id 
                        ORDER BY led.l_id) as jout on jout.l_id = pro.l_id
                WHERE pro.l_status != ".DELETED." 
                    AND pro.l_id in (".$led_id.") 
                    AND company_id = ".$_SESSION['company_id']." 
                GROUP BY pro.l_id 
                ORDER BY l_name";

        $result=$dbcon->query($query);
        $str="";
        $str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        while($row=mysqli_fetch_assoc($result)){
                $amount=$row["purchase_value"] + $row['in_jo_value'];
                //$amount= number_format((float)$row["purchase_value"], 2, '.', '');
                $str.='
                        <tr>
                                <td>'.$row["l_name"].'</td>
                                <td>'.number_format((float)$amount, 2).'</td>
                        </tr>
                ';

        }
        $str.="</table>"; 
	return $str;
}
function direct_income_value_details($dbcon,$where_date){
        /*$qry1="select GROUP_CONCAT(led.l_id) as leger_id1 from tbl_invoicetrn as cert 
                LEFT JOIN tbl_product as pro on pro.product_id=cert.product_id
                LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id
                LEFT JOIN tbl_ledger as led on led.l_id=pro.ledger_id
                LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                where trancation_status=0 and gro.g_id=17 and po.invoice_date ".$where_date." and pro.product_type=3 group by led.l_id order by led.l_id";
         */
        // Query Updated by Dimple Panchal
        $qry1="SELECT GROUP_CONCAT(led.l_id) as leger_id1 
                FROM tbl_journal_trn as cert 
                LEFT JOIN tbl_journal as jou ON jou.journal_id = cert.journal_id 
                LEFT JOIN tbl_ledger as led ON led.l_id = cert.ledger_id 
                LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group 
                WHERE journal_trn_status = ".ACTIVE." 
                    AND gro.g_id = ".DIRECT_INCOMES." 
                    AND entry_type = ".CREDIT."
                    AND jou.journal_date ".$where_date." 
                GROUP BY led.l_id 
                ORDER BY led.l_id";
        //echo $qry1;
        $leger_id1 = $dbcon->query($qry1)->fetch_object()->leger_id1;
        
        $qry2 = "SELECT group_concat(ledgerids.l_id) as leger_id2 
                FROM (SELECT led.l_id 
                    FROM tbl_general_book as cgen 
                    LEFT JOIN tbl_receipt as cert ON cert.receipt_id = cgen.table_id 
                    LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id 
                    LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group 
                    WHERE status = ".ACTIVE." 
                        AND gro.g_id = ".DIRECT_INCOMES." 
                        AND payment_type = ".CREDIT." 
                        AND cert.receipt_date ".$where_date." 
                    GROUP BY led.l_id 
                    ORDER BY led.l_id) as ledgerids";
        
        //echo $qry2;
        $leger_id2 = $dbcon->query($qry2)->fetch_object()->leger_id2;
	
	$array1= array();
	if(!empty($leger_id1)){
            array_push($array1,$leger_id1);
	}
        if(!empty($leger_id2)){
            array_push($array1,$leger_id2);
	}
	$led_id= implode(",",$array1); 
		
        $query="SELECT in_expance_value,l_name FROM `tbl_ledger` as pro 
                LEFT JOIN (SELECT sum(cert.product_amount) as in_expance_value,led.l_id 
                    FROM tbl_invoicetrn as cert 
                    LEFT JOIN tbl_product as pro on pro.product_id = cert.product_id
                    LEFT JOIN tbl_invoice as po on po.invoice_id = cert.invoice_id
                    LEFT JOIN tbl_ledger as led on led.l_id = pro.ledger_id
                    LEFT JOIN tbl_group as gro on gro.g_id = led.l_group
                    WHERE trancation_status = ".ACTIVE." 
                        AND gro.g_id = ".DIRECT_INCOMES." 
                        AND po.invoice_date ".$where_date." 
                        AND pro.product_type = ".CHARGES." 
                    GROUP BY led.l_id 
                    ORDER BY led.l_id) as genbook on genbook.l_id=pro.l_id
                WHERE pro.l_status != ".DELETED." 
                    AND pro.l_id in (".$led_id.") 
                    AND company_id = ".$_SESSION['company_id']." 
                GROUP BY pro.l_id 
                ORDER BY l_name";
		//echo '<br/>'.$query;
		$result=$dbcon->query($query);
		$str="";
		$str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
		while($row=mysqli_fetch_assoc($result)){
			//$amount=$row["in_expance_value"];
			$amount= number_format((float)$row["in_expance_value"], 2, '.', '');
			$str.='
				<tr>
					<td>'.$row["l_name"].'</td>
					<td>'.number_format((float)$amount, 2).'</td>
				</tr>
			';
			
		}
		$str.="</table>"; 
	return $str;
}
function direct_expance_value_details($dbcon,$where_date){
//        $qry1="select GROUP_CONCAT(led.l_id) as leger_id1 from tbl_potrancation as cert 
//                LEFT JOIN tbl_product as pro on pro.product_id=cert.product_id
//                LEFT JOIN tbl_pono as po on po.po_id=cert.po_id
//                LEFT JOIN tbl_ledger as led on led.l_id=pro.ledger_id
//                LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
//                where potrancation_status=0 and gro.g_id=16 and po.po_date ".$where_date." 
//                    and pro.product_type=3 
//                group by led.l_id 
//                order by led.l_id";
        
        $qry1="SELECT GROUP_CONCAT(ledgerids.l_id) as leger_id1 from 
            (SELECT led.l_id
                FROM tbl_potrancation as cert 
                LEFT join tbl_product as pro ON pro.product_id=cert.product_id
                LEFT join tbl_pono as po ON po.po_id=cert.po_id
                LEFT join tbl_ledger as led ON led.l_id=pro.ledger_id
                LEFT join tbl_group as gro ON gro.g_id=led.l_group
                WHERE potrancation_status = ".ACTIVE." 
                    AND gro.g_id = ".DIRECT_EXPENSES." 
                    AND po.po_date ".$where_date." 
                    AND pro.product_type = ".CHARGES." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) as ledgerids";
        //echo $qry1;
        $ro=$dbcon->query($qry1);
        $re=mysqli_fetch_assoc($ro);
	
	$array1= array();
	if(!empty($re['leger_id1'])){
		array_push($array1,$re['leger_id1']);
	}
	$led_id= implode(",",$array1); 
		
        $query="SELECT di_expance_value,l_name 
            FROM `tbl_ledger` as pro 
            LEFT JOIN (SELECT SUM(cert.product_amount) as di_expance_value,led.l_id 
                FROM tbl_potrancation as cert 
                LEFT JOIN tbl_product as pro ON pro.product_id=cert.product_id
                LEFT JOIN tbl_pono as po ON po.po_id=cert.po_id
                LEFT JOIN tbl_ledger as led ON led.l_id=pro.ledger_id
                LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group
                WHERE potrancation_status = ".ACTIVE."
                    AND gro.g_id = ".DIRECT_EXPENSES." 
                    AND po.po_date ".$where_date." 
                    AND pro.product_type = ".CHARGES." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) as genbook on genbook.l_id=pro.l_id
            WHERE pro.l_status != ".DELETED." 
                    AND pro.l_id in (".$led_id.") 
                    AND company_id = ".$_SESSION['company_id']." 
                GROUP BY pro.l_id 
                ORDER BY l_name";
		
        //echo '<br/>'.$query;
        $result=$dbcon->query($query);
        $str="";
        $str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        while($row=mysqli_fetch_assoc($result)){
                //$amount=$row["di_expance_value"];
                $amount= number_format((float)$row["di_expance_value"], 2, '.', '');
                $str.='
                        <tr>
                                <td>'.$row["l_name"].'</td>
                                <td>'.number_format((float)$amount, 2).'</td>
                        </tr>
                ';

        }
        $str.="</table>"; 
        return $str;
} 
function get_indirect_expenses($dbcon, $start_date, $end_date){
    $ca_entries = array();
    $sub_ledger_qry = "SELECT group_concat(l_id) as sub_ledger FROM `tbl_ledger` WHERE l_status = 0 AND l_group IN (".INDIRECT_EXPENSES.")";
    $sub_ledger = $dbcon->query($sub_ledger_qry)->fetch_object()->sub_ledger;
        
    $ca_qry = "select sum(opn_balance) as opening_balance,balance_typeid,sum(debitamount) as debitamount ,
                sum(creditamount) as creditamount,l_name as ledger_name, l_id as ledger_id
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=0 and table_name!='tbl_ledger' 
                            and entry_type= 2 and invoice.company_id=".$_SESSION['company_id']." 
                            and ref_date < '".$start_date."' 
                        group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                        from tbl_general_book as rec 
                        where genral_book_status= 0 and table_name!='tbl_ledger' 
                            and entry_type= 1 and company_id=".$_SESSION['company_id']."
                            and ref_date < '".$start_date."' 
                        group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = 0 AND company_id = ".$_SESSION['company_id']." 
                    AND cust.l_id IN (".$sub_ledger.")
                    group by cust.l_id
                    Order by l_name ASC ";
        
                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result,MYSQLI_ASSOC);
                
                if($ca_result){
                    foreach ($ca_result as $value) {
                        $balance_type = $value['balance_typeid'];
                        $op_balance = ($balance_type=="2" ? ($value['opening_balance']) : -$value['opening_balance']);
                        $balance = $op_balance + ($value['debitamount']-$value['creditamount']);
                        
                        $payment_qry = 'select sum(amount) as amount, entry_type from tbl_general_book as payment
				where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and ref_date>="'.date('Y-m-d',strtotime($start_date)).'" 
                                    and ref_date<="'.date('Y-m-d',strtotime($end_date)).'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id IN ('.$value['ledger_id'].') 
                                GROUP BY payment.entry_type
                                ORDER BY payment.ref_date
                                ';
                        $result = mysqli_query($dbcon, $payment_qry);
                        $payment_result = mysqli_fetch_all($result,MYSQLI_ASSOC);
                        
                        //echo '<pre>';                        print_r($payment_result);
                        if($payment_result){
                            foreach ($payment_result as $payment){
                                if($payment['entry_type']==2){
                                    $balance += $payment['amount'];

                                }else{
                                    $balance -= $payment['amount'];
                                }
                            }
                        }

                        $ca_value['ledger_id'] = $value['ledger_id'];
                        $ca_value['ledger_name'] = $value['ledger_name'];
                        $ca_value['ca_value'] = abs($balance);
                        array_push($ca_entries, $ca_value);
                    }
                }
                $ie_value = 0;
                
                if($ca_entries && !empty($ca_entries)){
                    $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
                    foreach ($ca_entries as $ca_entry) { 
                            $amount = number_format((float)$ca_entry["ca_value"], 2, '.', '');
                            $style = ($amount < 0) ? 'style="color: red;"' : ''; 
                            if($amount > 0){
                                $str.= '<tr>
                                        <td><a style="color: inherit;" href="ledger_monthly_view.php?ledger_id='.$ca_entry["ledger_id"].'" target="_blank">'.$ca_entry["ledger_name"].'</a></td>
                                        <td style="text-align: right;" '.$style.'>'.number_format($amount,2).'</td>
                                    </tr>';
                            }
                        $ie_value = $ie_value + $amount;
                        }
                        $str .= "</table>";
                 }
    $indirect_expense['entries'] = $str;
    $indirect_expense['value'] = $ie_value;
    return $indirect_expense;

}
function indirect_expense_value_details($dbcon,$where_date,$start_date){
	$qry1="SELECT GROUP_CONCAT(ledgerids.l_id) as leger_id1 FROM  
            (SELECT led.l_id 
                FROM tbl_journal_trn as cert 
                LEFT JOIN tbl_journal as jou on jou.journal_id = cert.journal_id
                LEFT JOIN tbl_ledger as led on led.l_id = cert.ledger_id
                LEFT JOIN tbl_group as gro on gro.g_id = led.l_group
                WHERE journal_trn_status = ".ACTIVE." 
                    AND gro.g_id = ".INDIRECT_EXPENSES." 
                    AND entry_type = ".DEBIT." 
                    AND jou.journal_date ".$where_date." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) as ledgerids" ;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	/*$qry12="select GROUP_CONCAT(led.l_id) as leger_id2 from tbl_general_book as cgen
                    LEFT JOIN tbl_receipt as cert on cert.receipt_id=cgen.table_id
                    LEFT JOIN tbl_ledger as led on led.l_id=cgen.ledger_id
                    LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                where status=0 and gro.g_id=19 and payment_type=1 and cert.receipt_date".$where_date." 
                group by led.l_id 
                order by led.l_id" ;*/
        /*
         * added new query by Dimple Panchal
         */
        $qry12="SELECT GROUP_CONCAT(ledgerids.l_id) as leger_id2 FROM 
                (SELECT led.l_id FROM tbl_general_book as cgen
                    LEFT JOIN tbl_receipt as cert ON cert.receipt_id = cgen.table_id
                    LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id
                    LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                WHERE status = ".ACTIVE." 
                    and gro.g_id = ".INDIRECT_EXPENSES." 
                    and payment_type = ".DR." 
                    and cert.receipt_date ".$where_date." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) as ledgerids" ;
        
	$ro1=$dbcon->query($qry12);
	$re1=mysqli_fetch_assoc($ro1);
	//$indirect_expance=$re1['leger_id2']; //commented by : Dimple Panchal - unsed variable.
	$array1= array();
	if(!empty($re['leger_id1'])){
		array_push($array1,$re['leger_id1']);
	}
	if(!empty($re1['leger_id2'])){
		array_push($array1,$re1['leger_id2']);
	}
        $led_id= implode(",",$array1);
	
		
        $query="SELECT in_pay_expance_value,in_jo_expance_value,l_name 
        FROM `tbl_ledger` as pro 
        LEFT JOIN (SELECT SUM(cgen.amount) as in_pay_expance_value, led.l_id 
            FROM tbl_general_book as cgen
                LEFT JOIN tbl_receipt as cert ON cert.receipt_id = cgen.table_id
                LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id
                LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                WHERE status = ".ACTIVE." 
                    AND gro.g_id = ".INDIRECT_EXPENSES." 
                    AND payment_type = ".DR." 
                    AND cert.receipt_date".$where_date." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) as genbook ON genbook.l_id = pro.l_id

        LEFT JOIN (SELECT SUM(cert.amount) as in_jo_expance_value,led.l_id 
            FROM tbl_journal_trn as cert 
                LEFT JOIN tbl_journal as jou ON jou.journal_id = cert.journal_id
                LEFT JOIN tbl_ledger as led ON led.l_id = cert.ledger_id
                LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                WHERE journal_trn_status = ".ACTIVE." 
                    AND gro.g_id = ".INDIRECT_EXPENSES." 
                    AND entry_type = ".DR." 
                    AND jou.journal_date ".$where_date." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) as jout on jout.l_id = pro.l_id
        WHERE pro.l_status != ".DELETED." 
            AND pro.l_id in (".$led_id.") 
            AND company_id = ".$_SESSION['company_id']." 
        GROUP BY pro.l_id 
        ORDER BY l_name";
		
        //echo '<br/>'.$query;
        $result = $dbcon->query($query);
        $str="";
        $str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        while($row=mysqli_fetch_assoc($result)){
                $amount = $row["in_pay_expance_value"]+$row["in_jo_expance_value"];
                //$amount= number_format((float)$amount, 2, '.', '');
                $str.='
                        <tr>
                                <td>'.$row["l_name"].'</td>
                                <td>'.number_format((float)$amount, 2).'</td>
                        </tr>
                ';

        }
        $str.="</table>";
    return $str;
}
function indirect_income_value_details($dbcon,$where_date){
//	$qry1="select GROUP_CONCAT(led.l_id) as leger_id1 from tbl_journal_trn as cert 
//                    LEFT JOIN tbl_journal as jou on jou.journal_id=cert.journal_id
//                    LEFT JOIN tbl_ledger as led on led.l_id=cert.ledger_id
//                    LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
//                where journal_trn_status=0 and gro.g_id=20 and entry_type=1 and jou.journal_date ".$where_date." group by led.l_id order by led.l_id" ;
	
        //updated by Dimple Panchal
        //fetch ledger_ids associated with indirect incomes.
        $ii_ledgerid_qry="SELECT GROUP_CONCAT(ledgerids.l_id) as leger_id1 FROM
                (SELECT led.l_id  
                FROM tbl_journal_trn as cert 
                LEFT join tbl_journal as jou ON jou.journal_id = cert.journal_id
                LEFT join tbl_ledger as led ON led.l_id = cert.ledger_id
                LEFT join tbl_group as gro ON gro.g_id = led.l_group
                WHERE journal_trn_status = ".ACTIVE."
                    AND gro.g_id = ".INDIRECT_INCOMES." 
                    AND entry_type = ".CREDIT." 
                    AND jou.journal_date ".$where_date." 
                GROUP BY led.l_id 
                ORDER BY led.l_id) AS ledgerids" ;
        //echo $ii_ledgerid_qry;
        $ii_ledgerid_result = $dbcon->query($ii_ledgerid_qry);
	$re = mysqli_fetch_assoc($ii_ledgerid_result);
	
	$ledgerids_qry="SELECT GROUP_CONCAT(ledgerids.l_id) as leger_id2 FROM
                        (SELECT led.l_id
                        FROM tbl_general_book as cgen
			LEFT JOIN tbl_receipt as cert ON cert.receipt_id = cgen.table_id
			LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id
			LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
			WHERE status = ".ACTIVE." 
                            AND gro.g_id = ".INDIRECT_INCOMES." 
                            AND payment_type = ".CR." 
                            AND cert.receipt_date".$where_date." 
                        GROUP by led.l_id 
                        ORDER by led.l_id) AS ledgerids" ;
	//echo '<br/>'.$ledgerids_qry;
        $ro1=$dbcon->query($ledgerids_qry);
	$re1=mysqli_fetch_assoc($ro1);
	//$indirect_expance=$re1['leger_id2']; //variable not used anywhere - commented by Dimple Panchal
	$array1= array();
	if(!empty($re['leger_id1'])){
		array_push($array1,$re['leger_id1']);
	}
	if(!empty($re1['leger_id2'])){
		array_push($array1,$re1['leger_id2']);
	}
        $led_id= implode(",",$array1);
		
        $str = "";
        if($led_id && !empty($led_id)){	
            $query="SELECT in_pay_expance_value, in_jo_expance_value, l_name 
                FROM `tbl_ledger` as pro 
                LEFT JOIN (SELECT SUM(cgen.amount) as in_pay_expance_value, led.l_id FROM tbl_general_book as cgen
                    LEFT JOIN tbl_receipt as cert on cert.receipt_id=cgen.table_id
                    LEFT JOIN tbl_ledger as led on led.l_id=cgen.ledger_id
                    LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                    WHERE status=0 
                        AND gro.g_id=20 
                        AND payment_type=1 
                        AND cert.receipt_date".$where_date." 
                    GROUP by led.l_id 
                    ORDER by led.l_id) as genbook on genbook.l_id=pro.l_id
                LEFT JOIN (SELECT SUM(cert.amount) as in_jo_expance_value,led.l_id FROM tbl_journal_trn as cert 
                    LEFT JOIN tbl_journal as jou on jou.journal_id=cert.journal_id
                    LEFT JOIN tbl_ledger as led on led.l_id=cert.ledger_id
                    LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                    WHERE journal_trn_status=0 
                        AND gro.g_id=20 
                        AND entry_type=1 
                        AND jou.journal_date ".$where_date." 
                    GROUP by led.l_id 
                    ORDER by led.l_id) as jout on jout.l_id=pro.l_id
                WHERE pro.l_status!=2 
                    AND pro.l_id IN (".$led_id.") 
                    AND company_id=".$_SESSION['company_id']." 
                GROUP BY pro.l_id 
                ORDER BY l_name";

            //echo '<br/>'.$query;
            $result = $dbcon->query($query);

            $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
            while($row=mysqli_fetch_assoc($result)){
                    $amount = $row["in_jo_expance_value"];//$row["in_pay_expance_value"]+$row["in_jo_expance_value"];
                    //$amount = number_format((float)$amount, 2, '.', '');
                    $str.='
                        <tr>
                            <td>'.$row["l_name"].'</td>
                            <td>'.number_format((float)$amount, 2).'</td>
                        </tr>
                    ';

            }
            $str .= "</table>";
        }
    return $str;
}
function indirect_income_value($dbcon,$where_date){
	$qry1="SELECT SUM(cert.amount) as in_jo_expance_value 
                FROM tbl_journal_trn as cert 
                LEFT JOIN tbl_journal as jou ON jou.journal_id = cert.journal_id
                LEFT JOIN tbl_ledger as led ON led.l_id = cert.ledger_id
                LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                WHERE journal_trn_status = ".ACTIVE." 
                    AND gro.g_id = ".INDIRECT_INCOMES." 
                    AND entry_type = ".CREDIT." 
                    AND po.journal_date ".$where_date."" ;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry12="SELECT SUM(cgen.amount) as in_pay_expance_value 
                    FROM tbl_general_book as cgen
                    LEFT JOIN tbl_receipt as cert ON cert.receipt_id = cgen.table_id
                    LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id
                    LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                    WHERE status = ".ACTIVE." 
                        AND gro.g_id = ".INDIRECT_INCOMES." 
                        AND payment_type = ".CR." 
                        AND cert.receipt_date ".$where_date ;
	/* $qry12="select sum(cert.total_paid_amount) as in_pay_expance_value from tbl_receipt as cert 
			LEFT JOIN tbl_ledger as led on led.l_id=cert.cust_id
			LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
			where status=0 and gro.g_id=19 and payment_type=1 and po.receipt_date ".$where_date."" ; */
	$ro1 = $dbcon->query($qry12);
	$re1 = mysqli_fetch_assoc($ro1);
	$indirect_expance = $re1['in_pay_expance_value'] + $re['in_jo_expance_value'];
        return number_format((float)$indirect_expance, 2, '.', '');
	//return $qry12;
}
function indirect_expances_value($dbcon,$where_date){
    $qry1 = "SELECT SUM(cert.amount) as in_jo_expance_value 
                FROM tbl_journal_trn as cert 
                LEFT JOIN tbl_journal as jou ON jou.journal_id=cert.journal_id
                LEFT JOIN tbl_ledger as led ON led.l_id=cert.ledger_id
                LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group
                where journal_trn_status = ".ACTIVE." 
                    AND gro.g_id = ".INDIRECT_EXPENSES." 
                    AND entry_type = ".DEBIT."
                    AND jou.journal_date ".$where_date."" ;
    $ro=$dbcon->query($qry1);
    $re=mysqli_fetch_assoc($ro);
	
    $qry12 = "SELECT SUM(cgen.amount) as in_pay_expance_value 
                FROM tbl_general_book as cgen
                LEFT JOIN tbl_receipt as cert ON cert.receipt_id = cgen.table_id
                LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id
                LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                WHERE status = ".ACTIVE." 
                    AND gro.g_id = ".INDIRECT_EXPENSES." 
                    AND payment_type = ".DR." 
                    AND cert.receipt_date ".$where_date ;
    
    /* $qry12="select sum(cert.total_paid_amount) as in_pay_expance_value from tbl_receipt as cert 
                    LEFT JOIN tbl_ledger as led on led.l_id=cert.cust_id
                    LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                    where status=0 and gro.g_id=19 and payment_type=1 and po.receipt_date ".$where_date."" ; */
    $ro1 = $dbcon->query($qry12);
    $re1 = mysqli_fetch_assoc($ro1);
    $indirect_expance = $re1['in_pay_expance_value'] + $re['in_jo_expance_value'];
    return number_format((float)$indirect_expance, 2, '.', '');
    //return $qry12;
}
function direct_income_value($dbcon, $where_date){
    $qry1="SELECT SUM(cert.product_amount) as in_expance_value 
            FROM tbl_invoicetrn as cert 
            LEFT JOIN tbl_product as pro ON pro.product_id = cert.product_id
            LEFT JOIN tbl_invoice as po ON po.invoice_id = cert.invoice_id
            LEFT JOIN tbl_ledger as led ON led.l_id = pro.ledger_id
            LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
            where trancation_status = ".ACTIVE." 
                AND gro.g_id = ".DIRECT_INCOMES." 
                AND po.invoice_date ".$where_date." 
                AND pro.product_type=3" ;
    //echo $qry1;
    $ro = $dbcon->query($qry1);
    $re = mysqli_fetch_assoc($ro);
    return number_format((float)$re['in_expance_value'], 2, '.', '');
    //return $qry1;
}
function direct_expance_value($dbcon, $where_date){
    $qry1="SELECT SUM(cert.product_amount) as di_expance_value 
            FROM tbl_potrancation as cert 
            LEFT JOIN tbl_product as pro on pro.product_id=cert.product_id
            LEFT JOIN tbl_pono as po on po.po_id=cert.po_id
            LEFT JOIN tbl_ledger as led on led.l_id=pro.ledger_id
            LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
            WHERE potrancation_status = ".ACTIVE." 
                AND gro.g_id = ".DIRECT_EXPENSES." 
                AND po.po_date ".$where_date." 
                AND pro.product_type = ".CHARGES ;
    $ro = $dbcon->query($qry1);
    $re = mysqli_fetch_assoc($ro);
    return number_format((float)$re['di_expance_value'], 2, '.', '');
}
function opening_stock_value($dbcon, $start_date){
        $inventory_management = $dbcon->query("SELECT inventory_management FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
                ->fetch_object()->inventory_management;

        if($inventory_management){
            //echo 'inventory - yes';
            $opening_balance_qry = "SELECT closing_balance as op_bal FROM tbl_closing_balance tcb
                LEFT join tbl_ledger as led ON led.l_id= tcb.ledger_id 
                LEFT join tbl_group as gro ON gro.g_id=led.l_group
                WHERE tcb.closing_balance_date <= '".$start_date."' AND tcb.status = ".ACTIVE." 
                    AND led.l_group = ".STOCK_IN_HAND." AND led.l_status = ".ACTIVE." 
                ORDER BY closing_balance_date DESC LIMIT 1";
            $opening_balance = $dbcon->query($opening_balance_qry)->fetch_object()->op_bal;
            //echo $opbal;
            
        } else {
            // query to get opening stock.
            //SELECT `product_name`,`product_purchase_mst_rate`,`product_stock` FROM `tbl_product` WHERE `product_status`=0 and `product_type`!=3
            $opvalue_qry = "SELECT SUM(product_stock) as op_value, AVG(product_purchase_mst_rate) as rate 
                            FROM tbl_product as cert 
                            WHERE product_status = ".ACTIVE." 
                                AND product_type != ".CHARGES." 
                                AND company_id = ".$_SESSION['company_id'];
            //echo $opvalue_qry;
            $ro3 = $dbcon->query($opvalue_qry);
            $opvalue = mysqli_fetch_assoc($ro3);
            $opbal = round($opvalue['op_value'] * $opvalue['rate']);
        
            // query to get purchase value.
            $purchase_qry="SELECT SUM(cert.product_amount) as purchase_value 
                    FROM tbl_potrancation as cert 
                    LEFT JOIN tbl_product as pro ON pro.product_id = cert.product_id
                    LEFT JOIN tbl_pono as po ON po.po_id = cert.po_id
                    LEFT JOIN tbl_ledger as led ON led.l_id = po.purchase_ledger_id
                    LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                    where potrancation_status = ".ACTIVE." 
                        and gro.g_id = ".PURCHASE_ACCOUNTS." 
                        and po.po_date <= ".$start_date." 
                        and pro.product_type != ".CHARGES ;
            $ro = $dbcon->query($purchase_qry);
            //echo '<br/>'.$purchase_qry;
            $purchase = mysqli_fetch_assoc($ro);
	 
            // query to get sales value.
            $sales_qry="SELECT SUM(cert.product_amount) as sales_value 
                        FROM tbl_invoicetrn as cert 
                        LEFT JOIN tbl_product as pro on pro.product_id=cert.product_id
                        LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id
                        LEFT JOIN tbl_ledger as led on led.l_id=po.sales_ledger_id
                        LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                        WHERE trancation_status = ".ACTIVE." 
                            AND gro.g_id= ".SALES_ACCOUNTS." 
                            AND po.invoice_date <= ".$start_date." 
                            AND pro.product_type != ".CHARGES ;
            $ro1 = $dbcon->query($sales_qry);
            //echo '<br/>'.$sales_qry;
            $sales = mysqli_fetch_assoc($ro1);
            $opening_balance=round(($opbal + $purchase['purchase_value'])- $sales['sales_value']);
        }
	//$opning_balance=round($opning_balance);
	return number_format((float)$opening_balance, 2, '.', '');
	
}
/*function closing_stock_value($dbcon,$start_date){
    $inventory_management = $dbcon->query("SELECT inventory_management FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
                ->fetch_object()->inventory_management;

        if($inventory_management){
            //echo 'inventory - yes';
            $closing_stock = $dbcon->query("SELECT sum(closing_balance) as closing_bal
                                    FROM tbl_closing_balance 
                                    WHERE closing_balance_date = ( SELECT MAX(closing_balance_date) 
                                        FROM tbl_closing_balance 
                                        WHERE closing_balance_date < '".$start_date."')")
                                    ->fetch_object()->closing_bal;
            echo $closing_stock;
            
        } else {
            $closing_stock = number_format((float)($purchase_ac_value - $sales_ac_value), 2, '.', '');
        }
}*/
function purchase_ac_value($dbcon,$where_date){
	$qry1="SELECT SUM(cert.product_amount) as purchase_value 
                FROM tbl_potrancation as cert 
                    LEFT JOIN tbl_product as pro ON pro.product_id=cert.product_id
                    LEFT JOIN tbl_pono as po ON po.po_id=cert.po_id
                    LEFT JOIN tbl_ledger as led ON led.l_id=po.purchase_ledger_id
                    LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group
                    WHERE potrancation_status = ".ACTIVE." 
                        AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                        AND po.po_date ".$where_date." 
                        AND pro.product_type != ".CHARGES ;
        //echo $qry1;
	$ro = $dbcon->query($qry1);
	$re = mysqli_fetch_assoc($ro);
        
        $qry2 = "SELECT SUM(cert.amount) as in_jo_value 
                        FROM tbl_journal_trn as cert 
                        LEFT JOIN tbl_journal as jou ON jou.journal_id = cert.journal_id
                        LEFT JOIN tbl_ledger as led ON led.l_id = cert.ledger_id
                        LEFT JOIN tbl_group as gro ON gro.g_id = led.l_group
                        WHERE journal_trn_status = ".ACTIVE." 
                            AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                            AND entry_type = ".DR." 
                            AND jou.journal_date ".$where_date." 
                        GROUP BY led.l_id 
                        ORDER BY led.l_id";
        $ro1 = $dbcon->query($qry2);
	$re1 = mysqli_fetch_assoc($ro1);
        $amount = $re['purchase_value'] + $re1['in_jo_value'];
	return number_format((float)$amount, 2, '.', '');
	//return $qry1;
}
function sales_ac_value($dbcon, $where_date){
	$qry1="SELECT SUM(cert.product_amount) as sales_value 
                FROM tbl_invoicetrn as cert 
                LEFT JOIN tbl_product as pro on pro.product_id=cert.product_id
                LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id
                LEFT JOIN tbl_ledger as led on led.l_id=po.sales_ledger_id
                LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                WHERE trancation_status = ".ACTIVE." 
                    AND gro.g_id = ".SALES_ACCOUNTS." 
                    AND po.invoice_date ".$where_date." 
                    AND pro.product_type != ".CHARGES ;
        // echo $qry1;
	$ro = $dbcon->query($qry1);
	$re = mysqli_fetch_assoc($ro);
	return number_format((float)$re['sales_value'], 2, '.', '');
}
function get_general_book_id($dbcon,$table_name,$table_id,$ledger_id){
	$gb_qry = "SELECT general_book_id 
                    FROM tbl_general_book 
                    WHERE genral_book_status = ".ACTIVE." 
                        AND table_id = ".$table_id." 
                        AND table_name = '".$table_name."' 
                        AND ledger_id = ".$ledger_id ;
	$ro = $dbcon->query($gb_qry);
	$gb_values = mysqli_fetch_assoc($ro);
	
	return $gb_values['general_book_id'];
}

function get_company_name($dbcon){
    $companyName = $dbcon->query("SELECT company_name FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
            ->fetch_object()->company_name;
    return $companyName;
}