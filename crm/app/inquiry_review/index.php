<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';
// include_once(COMMON_FUNCTION_INNER_PATH."crm_common_functions.php");
include_once($incPath."common_send_email.php");
// Amish Soni End 30-12-2020
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INQUIRY_SLUG_EDIT,
    INQUIRY_SLUG_DELETE
]);


$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "add") {
    $info['customer_address']               = $POST['customer_address'];
    $info['inquiry_no_date']                = $POST['inquiry_no_date'];
    $info['technical_spacification']        = $POST['technical_spacification'];
    $info['pro_speci_req']                  = $POST['pro_speci_req'];
    $info['cust_draw_enclose']              = $POST['cust_draw_enclose'];
    $info['scope_inspection']               = $POST['scope_inspection'];
    $info['delivery']                       = $POST['delivery'];
    $info['pricing_available']              = $POST['pricing_available'];
    $info['com_term_clear']                 = $POST['com_term_clear'];
    $info['earn_money_deposit']             = $POST['earn_money_deposit'];
    $info['bank_guarantee_dd_tdr']          = $POST['bank_guarantee_dd_tdr'];
    $info['sep_cov_price_techbid']          = $POST['sep_cov_price_techbid'];
    $info['del_due_date']                   = $POST['del_due_date'];
    $info['any_other_comment']              = $POST['any_other_comment'];
    $info['ref_wo_no']                      = $POST['ref_wo_no'];
    $info['reviewed_by']                    = $POST['reviewed_by'];
    $info['wo_date']                        = date('Y-m-d',strtotime($POST['wo_date']));
    $info['approved_by']                    = $POST['approved_by'];
    $info['cdate']                          = date("Y-m-d H:i:s");
    $info['user_id']                        = $_SESSION['user_id'];
    $info['company_id']                     = $_SESSION['company_id'];
    $info['inquiry_id']                     = $POST['inquiry_id'];
    
    if(!empty($POST['inquiry_review_id'])){
         
        $inserid = update_record('tbl_inquiry_review', $info,"inquiry_review_id=".$POST['inquiry_review_id'] , $dbcon);
    }else{
        $inserid = add_record('tbl_inquiry_review', $info, $dbcon);
    }

    echo ($inserid) ? 'update' : '0'.$dbcon->error;
}
?>
