<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

$POST = ($_POST != NULL) ? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
		
if(strtolower($POST['mode']) == "generate_report") 
{
        $str = '';
        $s_date = explode(' - ',$POST['date']);
        $_SESSION['start'] = $s_date[0];
        $_SESSION['end'] = $s_date[1];
        $companyName = $dbcon->query("SELECT company_name FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
            ->fetch_object()->company_name;
        
        $where = $ledger_where = '';
        if($POST['cust_id']){
            $where .= " AND receipt.cust_id =".$POST['cust_id'];
            $ledger_where .= "AND l_id =".$POST['cust_id'];
        }
        
        $str .='<table  class="display table table-bordered table-striped" id="data_list">
                        <!--<tr id="logo" class="logo" >
                                <td colspan="2" style="text-align:center;">
                                        <strong>'.$companyName.'</strong>
                                </td>
                                <td colspan="2" style="text-align:center;">Date
                                    <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
                                </td>
                        </tr>-->
                        ';
                $str .='<tr>
                            <th style="text-align:center">Customer Name</th>
                            <th style="text-align:center">Unadjusted Advance</th>
                            <th style="text-align:center">Adjustable Debits</th>
                            <th style="text-align:center">Unadjustable Amount</th>
                        </tr>
                    <tbody>';
            $ledger_qry = 'SELECT distinct(ledger.l_id),ledger.l_name FROM tbl_excess as ex  
                LEFT JOIN tbl_ledger ledger ON ex.cust_id = ledger.l_id
                WHERE ledger.l_status = 0 and ledger.company_id = '.$_SESSION['company_id'].' and ledger.l_group IN ('.SUNDRY_CREDITORS.') '.$ledger_where;
            $result = brp_mysqli_query($dbcon,$ledger_qry);
            $ledger_data = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);
            //p($purchase_data);
            
            if($ledger_data){
                $total_excess_amount = 0;
                foreach ($ledger_data as $i => $ledger) {
                    $excess_amount = $dbcon->query('select sum(excess_amount) as excess_amount 
                        from tbl_excess as inv 
                        left join tbl_receipt as rep on rep.receipt_id=inv.receipt_id 
                        where inv.status=0 and inv.company_id = '.$_SESSION['company_id'].' and excess_type=2 AND inv.cust_id= '.$ledger['l_id'].' 
                            and inv.excess_amount>(select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and payment_type=1 and inv.excess_id=trn.excess_id)
                        ')->fetch_object()->excess_amount;
                    
                    $total_excess_amount += $excess_amount;
                    
                    if($excess_amount){
                        $qry = 'select sum(g_total) as adjustable_amount 
                            FROM tbl_pono as po 
                            where po.status=0 and po.approve_status=1 AND vender_id= '.$ledger['l_id'].' and po.company_id = '.$_SESSION['company_id'].'
                                and po.g_total>(select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and po.po_id=trn.purchase_id)
                            ';
                        $adjustable_amount = $dbcon->query($qry)->fetch_object()->adjustable_amount;
                        $unadjustable = $excess_amount - $adjustable_amount;
                        $str .='<tr>
                                <td style="text-align:left;">
                                    '.$ledger['l_name'].'
                                </td>
                                <td style="text-align:right;">
                                    <a style="color: inherit;" class="link_dash" href="'.ROOT.'vendor_unadjusted_advance/'.$ledger['l_id'].'" target="_blank">
                                    '. indian_number($excess_amount,2).'
                                    </a>
                                </td>
                                <td style="text-align:right;">'.indian_number($adjustable_amount,2).'</td>
                                <td style="text-align:right;">'.indian_number($unadjustable,2).'</td>
                            </tr>';
                    }
                    
                }
                $str .= '<tr>
                        <td style="text-align:left;"><strong>Total (INR)</strong></td>
                        <td style="text-align:right;"><strong>'.indian_number($total_excess_amount,2).'</strong></td>
                        <td style="text-align:left;"></td>
                        <td style="text-align:left;"></td>
                    </tr>';
                
                $str .= '<tr>
                        <td colspan="4" style="text-align:center;background-color:antiquewhite;">
                            Total Amount of Unadjusted Customer Advance for 
                            <span style="color:red;">'.count($ledger_data).'</span> 
                            Customers is <span style="color:red;"> INR '.indian_number($total_excess_amount,2).'</span>
                        </td>
                    </tr>';
            } else {
                $str .= '<tr>
                        <td colspan="4" style="text-align:center;">No Data Found !! </td>
                    </tr>';
            } 
        
        echo $str;
}
else if(strtolower($POST['mode']) == "generate_chart") 
{
        $where = '';
        if($POST['cust_id']){
            $where .= " AND inv.cust_id =".$POST['cust_id'];
        }
        
    $query = "select sum(excess_amount) as excess_amount ,inv.cust_id, ledger.l_name 
            from tbl_excess as inv 
            LEFT JOIN tbl_ledger ledger ON inv.cust_id = ledger.l_id 
            left join tbl_receipt as rep on rep.receipt_id=inv.receipt_id 
            where inv.status=0 and excess_type=2 and inv.company_id = ".$_SESSION['company_id']." ".$where."
                and inv.excess_amount>(select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and payment_type=1 and inv.excess_id=trn.excess_id)
            group by inv.cust_id";
			
        $invoice_turnover = $dbcon->query($query);
        $row = array();
        $i=0;
        while($invoice_circle = mysqli_fetch_assoc($invoice_turnover))
        {	
                $row[$i]['label'] = $invoice_circle['l_name'];
                $row[$i]['id'] = $invoice_circle['cust_id'];
                $row[$i]['y'] = intval($invoice_circle['excess_amount']);			
                $i++;
        }	
        //$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
        echo json_encode($row);
}
   
?>