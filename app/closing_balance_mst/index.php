<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    FINANCE_CLOSING_BALANCE_EDIT,
    FINANCE_CLOSING_BALANCE_DELETE
]);

if(strtolower($POST['mode']) == "fetch") {
    $closing_balance_date = date("Y-m-d", strtotime($POST['to_date']));
    if(!$closing_balance_date){
        $closing_balance_date = date("Y-m-d");
    }
    
    $appData = array();
    $i=1;
    $aColumns = array('l_id', 'l_name','opn_balance');
    $sIndexColumn = "l_id";
    $isWhere = array("l_status = 0 AND `l_group` = ".STOCK_IN_HAND);
    $sTable = "tbl_ledger";			
    $isJOIN = array();
    $hOrder = "l_id";
    include('../../include/pagging.php');
    $id=1;
    
    
    foreach($sqlReturn as $row) {
            $qry = "SELECT closing_balance FROM tbl_closing_balance 
                    WHERE closing_balance_date < '".$closing_balance_date."' 
                        AND status = ".ACTIVE." AND ledger_id = ".$row["l_id"]." AND company_id = ".$_SESSION['company_id']."
                    ORDER BY closing_balance_date DESC LIMIT 1";
            $op_bal = $dbcon->query($qry)->fetch_object()->closing_balance;
            
            $opening_balance = ($op_bal > 0) ? $op_bal : $row['opn_balance'];
            
            $closing_bal = $dbcon->query("SELECT closing_balance FROM tbl_closing_balance 
                    WHERE closing_balance_date = '".$closing_balance_date."' 
                        AND status = ".ACTIVE." AND ledger_id = ".$row["l_id"]." AND company_id = ".$_SESSION['company_id']." 
                    ORDER BY closing_balance_date DESC LIMIT 1")
                    ->fetch_object()->closing_balance;
            $closing_balance = ($closing_bal > 0) ? $closing_bal : $opening_balance;
            
            $row_data = array();
            $row_data[] = $row['l_name'];
            $row_data[] = number_format((float)$opening_balance, 2, '.', '');
            $row_data[] = '<input type="text" id="closing_balance" name="closing_balance['.$row["l_id"].']" value="'.number_format((float)$closing_balance, 2, '.', '').'">';
            $row_data[] = '<a class="accordion-toggle" data-attr="expand" id="accordion'.$row["l_id"].'" data-parent="#accordion'.$row["l_id"].'" onClick="show_closing_balance_history(this,'.$row["l_id"].')">
                                <i class="fa fa-chevron-down"></i>
                            </a>';
            $appData[] = $row_data;
            $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode( $output );
}

else if(strtolower($POST['mode']) == "add_closing_balance") {
    $resp['msg'] = "0";
    $closing_balance_date = date("Y-m-d", strtotime($POST['to_date']));
    
    if(isset($POST['closing_balance']) && !empty($POST['closing_balance'])){
        foreach ($POST['closing_balance'] as $ledger_id => $closing_balance) {
            $info['ledger_id']              = $ledger_id;							
            $info['closing_balance']        = $closing_balance;							
            $info['closing_balance_date']   = $closing_balance_date;	
            $info['status']                 = ACTIVE;
            $info['created_at']             = date("Y-m-d H:i:s");
            $info['user_id']                = $_SESSION['user_id'];
            $info['company_id']             = $_SESSION['company_id'];
            
            $qry = "SELECT `cb_id`, `closing_balance`, `ledger_id` 
                    FROM `tbl_closing_balance` 
                    WHERE `closing_balance_date` = '".$closing_balance_date."'  
                        AND company_id = ".$_SESSION['company_id']."
                        AND ledger_id = ".$ledger_id;
            $tr = $dbcon -> query($qry);
                $where = "ledger_id =".$ledger_id." And closing_balance_date = '".$closing_balance_date."'";
                if($tr->num_rows > 0) {
                    $updateid = update_record('tbl_closing_balance', array('closing_balance' => $closing_balance) , $where , $dbcon);
                } else {
                    $inserid = add_record('tbl_closing_balance', $info, $dbcon);
                }
            }
        if($inserid || $updateid)
        {
            $resp['msg'] = "1";
                
        } else {
            $resp['msg'] = "0";
        }
    
        echo json_encode($resp);
    }
    return json_encode($resp);
}
else if(strtolower($POST['mode']) == "edit_closing_balance"){
    $resp['msg'] = "0";
    if($POST['cb_id']){
        $data['closing_balance_date']   = date("Y-m-d", strtotime($POST['closing_bal_date']));
        $data['closing_balance']        = $POST['closing_bal'];
        
        $where = "ledger_id =".$POST['ledger_id']." And cb_id = '".$POST['cb_id']."'";
        $updateid = update_record('tbl_closing_balance', $data , $where , $dbcon);
        
        if($updateid)
        {
            $resp['msg'] = "1";
                
        } else {
            $resp['msg'] = "0";
        }
    
        echo json_encode($resp);
    }
    return json_encode($resp);
}
else if(strtolower($POST['mode']) == "show_history") {
    $ledger_id = $POST['ledger_id'];
    $ledger_res = mysqli_query($dbcon,"SELECT l_name, opn_balance FROM tbl_ledger WHERE l_id=$ledger_id");
    $ledger_data = mysqli_fetch_array($ledger_res,MYSQLI_ASSOC);
    //print_r($ledger_data);
    
    $qry = "SELECT tcb.cb_id, tcb.closing_balance, tcb.closing_balance_date
            FROM `tbl_closing_balance` as tcb 
            WHERE tcb.status = 0 AND tcb.ledger_id = ".$ledger_id." 
                AND company_id = ".$_SESSION['company_id']."
                Order by tcb.closing_balance_date desc";
    
    $result = mysqli_query($dbcon, $qry);
    $cb_result = mysqli_fetch_all($result,MYSQLI_ASSOC);
    
    $html = '';
    $html .= '
        <div class="panel-collapse collapse"><h4>'.$ledger_data['l_name'].'</h4></div>
            <table>
                <tr>
                    <td>As on Date</td>
                    <td>Opening Balance</td>
                    <td>Closing Balance</td>
                    <td></td>
                </tr>
               ';
    foreach ($cb_result as $cb_row) {
            $qry = "SELECT closing_balance FROM tbl_closing_balance 
                    WHERE closing_balance_date < '".$cb_row['closing_balance_date']."' 
                        AND status = ".ACTIVE." AND ledger_id = ".$ledger_id." 
                        AND company_id = ".$_SESSION['company_id']."
                    ORDER BY closing_balance_date DESC LIMIT 1";
            $op_bal = $dbcon->query($qry)->fetch_object()->closing_balance;
            
            $opening_balance = ($op_bal > 0) ? $op_bal : $ledger_data['opn_balance'];
            
            $html .=  '<tr>
                        <td>'.date('d-m-Y', strtotime($cb_row['closing_balance_date'])).'</td>
                        <td>'.number_format((float)$opening_balance, 2, '.', '').'</td>
                        <td>'.number_format((float)$cb_row['closing_balance'], 2, '.', '').'</td><td>';
            if(in_array(FINANCE_CLOSING_BALANCE_EDIT,$bulkAccessArray)){
                $html .=    '<a class="btn btn-xs btn-warning" data-original-title="Edit" onClick="edit_closing_balance_history('.$cb_row["cb_id"].')" data-toggle="tooltip" data-placement="top"><i class="fa fa-pencil"></i></a>&nbsp;';
            }
            if(in_array(FINANCE_CLOSING_BALANCE_DELETE,$bulkAccessArray)){
                $html .=    '<a class="btn btn-xs btn-danger" data-original-title="Edit" onClick="delete_closing_balance_history('.$cb_row["cb_id"].')" data-toggle="tooltip" data-placement="top"><i class="fa fa-trash-o"></i></a>';
            }
            $html .=  '</td></tr>';
    }
    $html .= '</table>
            ';
    $resp['html'] = $html;
    echo json_encode($resp);
    //echo '<pre>'; echo print_r($cb_result); exit;
} 
else if(strtolower($POST['mode']) == "show_edit_history_popup"){
    $cb_res = mysqli_query($dbcon,"SELECT * FROM tbl_closing_balance WHERE cb_id = ".$POST['cb_id']);
    $cb_data = mysqli_fetch_array($cb_res,MYSQLI_ASSOC);
    //print_r($cb_data);
    $cb_date = date('d-m-Y', strtotime($cb_data['closing_balance_date']));
    
    $html = '';
    $html .= '<form role="form" id="edit_closing_balance_form" action="javascript:;" method="post" name="edit_closing_balance_form">
                    <div class="form-group">
                        <label for="closing_bal_date">Date</label>
                        <input class="form-control default-date-picker" type="text" name="closing_bal_date" id="closing_bal_date" value="'.$cb_date.'" />
                    </div>	

                    <div class="form-group">
                        <label for="closing_bal">Closing Balance</label>
                        <input type="text" class="form-control" name="closing_bal" id="closing_bal" value="'.$cb_data['closing_balance'].'"/>
                    </div>	

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="save" name="save">Save</button>
                        <input type="hidden" name="cb_id" id="cb_id" value="'.$cb_data['cb_id'].'" />
                        <input type="hidden" name="ledger_id" id="ledger_id" value="'.$cb_data['ledger_id'].'" />
                        <input type="hidden" name="mode" id="mode" value="edit_closing_balance" />
                    </div>
                </form>';
        $script = '
            <script src="'.ROOT.'js/app/closing_balance_mst.js"></script>
            <script>
                    $(".default-date-picker").datepicker({
                        format: "dd-mm-yyyy",
                        autoclose: true
                    });
                </script>
                ';
    $resp['html'] = $html;
    $resp['script'] = $script;
    echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "delete_history"){
    if($POST['cb_id']){
        $deleteid = update_record('tbl_closing_balance', array('status' => 2,'company_id' => $_SESSION['company_id']),"cb_id =".$POST['cb_id'] , $dbcon);
        if($deleteid){
            echo '1';
        } else {
            echo '0';
        }
    } else {
        echo '0';
    }
}