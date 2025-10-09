
<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/finance_common_functions.php");
//include("../../config/image.php");

$approve_btn_per = check_permission('purchase_list',$_SESSION['user_id'],'aprv',$dbcon);
$final_approve_btn_per = check_permission('purchase_list',$_SESSION['user_id'],'final_aprv',$dbcon);

//will show status column only if user has permission to approve
if($approve_btn_per && $final_approve_btn_per){
    // setting colspan for table
    $total_column = 6;
    $company_colspan = 4;
} else {
    $total_column = 5;
    $company_colspan = 3;
}

$POST = ($_POST != NULL) ? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
		
if(strtolower($POST['mode']) == "generate_report") 
{
        $str = '';
        $s_date = explode(' - ',$POST['date']);
        $_SESSION['start'] = $s_date[0];
        $_SESSION['end'] = $s_date[1];
        $companyName = $dbcon->query("SELECT company_name FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
            ->fetch_object()->company_name;
        
        $where = '';
        if($POST['cust_id']){
            $where .= " AND po.vender_id =".$POST['cust_id'];
        }
        
        if($POST['pay_terms']){
            $where .=" AND DATEDIFF(NOW(),DATE_ADD(po.po_date, INTERVAL ledger.pay_terms DAY)) >= ".$POST['pay_terms'];
        }
        $cust_qry = "select l_id,l_name,pay_terms from tbl_ledger where l_status = 0 and l_group IN (".SUNDRY_DEBTORS.")".$where;
        $cust_data = brp_mysqli_fetch_all($dbcon->query($cust_qry));	
        
        $str .='<table  class="display table table-bordered table-striped" id="data_list">
                        <tr id="logo" class="logo" >
                                <td colspan="'.$company_colspan.'" style="text-align:center;">
                                        <strong>'.$companyName.'</strong>
                                </td>
                                <td colspan="2" style="text-align:center;">Date
                                    <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
                                </td>
                        </tr>
                        ';
                $str .='<tr>
                        <th style="text-align:center">Purchase No</th>
                        <th style="text-align:center">GRN No</th>
                        <th style="text-align:center">Party Name</th>
                        <th style="text-align:center">Amount</th>
                        <th style="text-align:center">Due Days</th>';
                
                if($approve_btn_per && $final_approve_btn_per){
                    $str .='<th style="text-align:center">Status</th>';
                }
                $str .='</tr>
                    <tbody>';
            $invoice_qry = 'SELECT po.po_id, po.po_no,po.po_date, group_concat(grn.grn_id,":",grn.grn_no) as grn, ledger.l_name,ledger.pay_terms, po.g_total ,po.approve_status,
                        DATE_ADD(po.po_date, INTERVAL ledger.pay_terms DAY) AS due_date,
                        DATEDIFF(NOW(),DATE_ADD(po.po_date, INTERVAL ledger.pay_terms DAY)) as due_days
                        FROM `tbl_pono` po
                        LEFT JOIN tbl_potrancation as potrn ON potrn.po_id = po.po_id
                        LEFT JOIN tbl_grn as grn ON grn.grn_id = potrn.grn_id
                        LEFT JOIN tbl_ledger as ledger ON ledger.l_id = po.vender_id
                        WHERE po.status = 0 AND grn_status = 0 '.$where.'
                            AND po.po_date >="'.date('Y-m-d',strtotime($s_date[0])).'" 
                            AND po.po_date <="'.date('Y-m-d',strtotime($s_date[1])).'"  
                        GROUP BY po_id
                        ORDER BY `po_id` DESC';
            $result = brp_mysqli_query($dbcon,$invoice_qry);
            $purchase_data = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);
            //p($purchase_data);
            
            if($purchase_data){
                foreach ($purchase_data as $i => $purchase) {
                    // calculate overdue days
                    $payment_terms = $customer['pay_terms'];
                    $purchase_date = date('d-m-Y', strtotime($purchase['po_date']));
                    $today = date('d-m-Y');
                    if($payment_terms){
                        $due_date = date('d-m-Y', strtotime($purchase_date. ' + '.$payment_terms.' days'));
                        $due_days = date_diff(date_create($due_date), date_create($today))->format("%R%a");
                    } else {
                        $due_date = $purchase_date;
                        $due_days = date_diff(date_create($purchase_date), date_create($today))->format("%R%a");
                    }
                    
                    // make string for GRN list
                    if($purchase['grn']){
                        $grns = explode(',', $purchase['grn']);
                        $grn_str = '';
                        foreach($grns as $grn){
                            $grn_data = explode(':', $grn);
                            $grn_str .= '<a style="color: inherit;" class="link_dash" href="grn_view/'.$grn_data[0].'" target="_blank">
                            '.$grn_data[1].'</a><br/>';
                        }
                    }
                    
                    // user can change status only if user has permission to approve 
                    if($approve_btn_per && $final_approve_btn_per){
                        $approve_status = 'Unpass';
                        $po_no = $purchase['po_no'];
                        if($purchase['approve_status']){
                            $status = '<td style="text-align:center">
                                <a class="btn btn-success" onClick="open_approv_model('.$purchase['po_id'].',\''.$po_no.'\')">
                                Pass</a></td>';
                        } else {
                            $status = '<td style="text-align:center">
                                <a class="btn btn-warning" onClick="open_approv_model('.$purchase['po_id'].',\''.$po_no.'\')">
                                Unpass</a></td>';
                        }
                        
                    } else {
                        $status = '';
                    }
                    
                    
                    $str .='<tr>
                            <td style="text-align:center">
                                <a style="color: inherit;" class="link_dash" href="purchase_view/'.$purchase["po_id"].'" target="_blank">
                                '.$purchase['po_no'].'
                                </a>
                            </td>
                            <td style="text-align:center">'.$grn_str.'</td>
                            <td style="text-align:center">'.$purchase['l_name'].'</td>
                            <td style="text-align:center">'.indian_number($purchase['g_total'],2).'</td>
                            <td style="text-align:center">'.(($purchase['due_days'] > 0) ? abs($purchase['due_days']) : '0').'</td>
                            '.$status.'
                        </tr>';
                }
            } else {
                $str .= '<tr>
                        <td colspan="'.$total_column.'" style="text-align:center">No Purchase Bill Found !! </td>
                    </tr>';
            } 
        
        echo $str;
}
   
?>