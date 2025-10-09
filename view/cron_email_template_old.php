<?php

session_start(); //start session
$AJAX = true;
//error_reporting(E_ALL); ini_set('display_errors', '1');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
        WD_TEAM_PENDING_TASK_SLUG_READ,
        WD_PENDING_TASK_SLUG_READ,
        WD_COMPALINT_SLUG_READ,
        WD_EMPLOYEE_SLUG_READ,
        WD_MRP_SLUG_READ,
        WD_SPARE_PARTS_SLUG_READ,
        WD_PENDING_JOB_CARD_SLUG_READ,
        WD_PURCHASE_SLUG_READ,
        WD_QC_PENDING_SLUG_READ,
        WD_USER_INQUIRY_SLUG_READ,
        WD_INHOUSE_PENDING_PROCESS_SLUG_READ,
        WD_OUTSIDE_PENDING_PROCESS_SLUG_READ,
        WD_VENDOR_UNADJUSTED_AMOUNT,
        WD_CUSTOMER_UNADJUSTED_AMOUNT,
        CRM_SLUG_VIEW,
        SCHEDULING_SLUG_VIEW,
        MRP_SLUG_VIEW,
        PURCHASE_SLUG_VIEW,
        PRODUCTION_SLUG_VIEW,
        RESOURCE_SLUG_VIEW,
        INVENTORY_SLUG_VIEW,
        QC_SLUG_VIEW,
        SERVICE_SLUG_VIEW,
        FINANCE_SLUG_VIEW,
        HRMS_SLUG_VIEW,
        MAINTENANCE_SLUG_VIEW,
        DISTRIBUTOR_PORTAL_SLUG_VIEW,
        VENDOR_PORTAL_SLUG_VIEW,
        SUPPORT_TICKET_SLUG_VIEW,
        DASHBOARD_PENDING_TASK_LIST_INQUIRY_ADD,
        DASHBOARD_PENDING_TASK_LIST,
        DASHBOARD_PENDING_TASK_LIST_GENERAL,
        DASHBOARD_PENDING_TASK_LIST_QUOTATION,
        DASHBOARD_PENDING_TASK_LIST_REVISE_QUOTATION,
        DASHBOARD_PENDING_TASK_LIST_QUOTATION_LIST,
        DASHBOARD_PENDING_TASK_LIST_QUOTATION_FOLLOWUP,
        DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST,
        DASHBOARD_PENDING_TASK_LIST_DISPATCH_LIST,
        DASHBOARD_PENDING_TASK_LIST_APPOINTMENT_LIST,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_INQUIRY_ADD,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_GENERAL,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_REVISE,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_LIST,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_FOLLOWUP,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_SALES_ORDER_LIST,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_DISPATCH_LIST,
        DASHBOARD_PERSONAL_PENDING_TASK_LIST_APPOINTMENT_LIST,
        DASHBOARD_GET_SALES_ORDER_DETAILS,
        DASHBOARD_GET_STOCK_DETAILS,
        DASHBOARD_GET_STOCK_PENDING_REQUEST,
        DASHBOARD_GET_REJECT_QC_REQUEST_LIST,
        DASHBOARD_GET_FORECAST_LIST,
        DASHBOARD_INDENT_LIST,
        DASHBOARD_PO_QUOTATION_LIST,
        DASHBOARD_PO_REQUEST_LIST,
        DASHBOARD_OVERDUE_PO_PRO_LIST,
        DASHBOARD_PURCHASE_BILL_PENDING_LIST,
        DASHBOARD_DEBIT_NOTE_PENDING_LIST,
        DASHBOARD_JOB_CARD_LIST,
        DASHBOARD_PENDING_JOB_WORK_LIST,
        DASHBOARD_PENDING_JOB_CARD,
        DASHBOARD_PURCHASE_QC_PENDING_LIST,
        DASHBOARD_PARTS_QC_PENDING_LIST,
        DASHBOARD_COMPLAIN_TYPE,
        DASHBOARD_COMPLAIN_TYPE_COMPLIANT_ASSIGNED,
        DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_STARTED,
        DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_NOT_STARTED,
        DASHBOARD_COMPLAIN_TYPE_CLOSED,
        DASHBOARD_COMPLAIN_TYPE_NOT_DONE,
        DASHBOARD_COMPLAIN_LIST,
        DASHBOARD_EMPLOYEE_PRESENT_LIST,
        DASHBOARD_EMPLOYEE_ABSENT_LIST,
        DASHBOARD_EMPLOYEE_EXPENSE_PENDING_LIST,
        DASHBOARD_SPARE_LIST_PENDING,
        DASHBOARD_RETURN_OLD_SPARE,
        DASHBOARD_CUSTOMER_UNADJUSTED_AMOUNT,
        DASHBOARD_PENDING_ORDER_INVOICE,
        DASHBOARD_PENDING_SPARE_INVOICE,
        DASHBOARD_PENDING_SERVICE_CHARGE_INVOICE,
        DASHBOARD_PENDING_FOC_SPARE_INVOICE,
        DASHBOARD_PENDING_INVOICE_APPROVAL,
        DASHBOARD_VENDOR_UNADJUSTED_AMOUNT,
        DASHBOARD_PO_REQUEST_LIST_APPROVE
    ],$company_id,$user_id);

$set="select comp.logo,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id='".$_SESSION['company_id']."' ";
      
$comp_rel=brp_mysqli_fetch_assoc($dbcon->query($set));

$template= '<html>
   <head>
      <title></title>
      <meta charset="utf-8">
   </head>
   <body>
      <table bgcolor="#f6f6f6" border="0" cellpadding="0" cellspacing="0" style="padding: 10px;" width="100%">
         <tbody>
            <tr>
               <td align="center" valign="top">
                  <table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" style="width: 100%;font-family: Arial, sans-serif;" width="100%">
                     <thead>
                        <tr>
                           <td align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">Please check the following details.</p>
                           </td>
                        </tr>
                     </thead>

                     </table>
                     <table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" style="width: 100%;font-family: Arial, sans-serif;margin-top:20px" width="100%">
                    
                     <tbody>
                        <tr>
                           <td style="margin-top:20px" align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">Today\'s Info List</p>
                           </td>
                        </tr>
                        <tr>
                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:40%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;"> Today\'s Info List</p>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Inquiry Add :</span>
                                  <span style="width:20%;display:inline-block">'.today_inquiry_add_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of quotation created :</span>
                                  <span style="width:20%;display:inline-block">'.today_quotation_created_count($dbcon).'</span> </div>';    

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Pending Followup :</span>
                                  <span style="width:20%;display:inline-block">'.today_pending_folloup_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Sales Order Planning Pending :</span>
                                  <span style="width:20%;display:inline-block">'.count_so_procuct_req($dbcon).'</span> </div>';
                                  
                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Work Order Pending:</span>
                                  <span style="width:20%;display:inline-block">'.today_work_order_pending_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Job card Pending:</span>
                                  <span style="width:20%;display:inline-block">'.today_job_card_pending_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Indent Approved Pending :</span>
                                  <span style="width:20%;display:inline-block">'.today_indent_approve_pending_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of PO Created :</span>
                                  <span style="width:20%;display:inline-block">'.today_purchse_order_created_count($dbcon).'</span> </div>';      

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Po Pending :</span>
                                  <span style="width:20%;display:inline-block">'.today_purchse_order_pending_count($dbcon).'</span> </div>'; 
                              
                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Pending GRN :</span>
                                  <span style="width:20%;display:inline-block">'.today_pending_grn_count($dbcon).'</span> </div>'; 
                              
                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of  Purchase Entry(Total Amount) :</span>
                                  <span style="width:20%;display:inline-block">'.today_purchase_total_amount($dbcon).'</span> </div>';   

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Sales Entry (Total Amount) :</span>
                                  <span style="width:20%;display:inline-block">'.today_sales_total_amount($dbcon).'</span> </div>'; 

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Pending Purchase Bill :</span>
                                  <span style="width:20%;display:inline-block">'.today_purchase_bill_pending_count($dbcon).'</span> </div>'; 
                                          
                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Inquiry Won :</span>
                                  <span style="width:20%;display:inline-block">'.today_won_inquiry_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Today No. of Total Amount of Won Inquiry :</span>
                                  <span style="width:20%;display:inline-block">'.today_total_amount_of_won_inquiry($dbcon).'</span> </div>';    
                                      
                              $template .= '    
                          </td></tr>  
                        <tr>
                           <td style="margin-top:20px" align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">CRM</p>
                           </td>
                        </tr>

                        <tr>
                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:40%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">
                                CRM - Team Pending Tasks</p>';
                            $in_array_check = array(DASHBOARD_PENDING_TASK_LIST_GENERAL,DASHBOARD_PENDING_TASK_LIST,DASHBOARD_PENDING_TASK_LIST_QUOTATION,DASHBOARD_PENDING_TASK_LIST_REVISE_QUOTATION,
                  DASHBOARD_PENDING_TASK_LIST_QUOTATION_FOLLOWUP);    
                            $query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
                             $query_rs=$dbcon->query($query);
                             $i = 0;
                              while($row_p=brp_mysqli_fetch_assoc($query_rs)){
                                  
                                 if($row_p['mcd_id'] == GENERAL_TASK_TYPE) {
                                    $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">'.$row_p['mcd_name'].' :</span><span style="width:20%;display:inline-block">'.count_general_pen_tsk($dbcon, $_SESSION['user_id']).'</span> </div>';
                                 } 
                                 else {
                                        if($row_p['mcd_id'] == '21'){
                                             $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">PENDING QUOTATION APPOVAL :</span><span style="width:20%;display:inline-block">'.count_team_pending_quot_approval($dbcon,$_SESSION['user_id']).' </span></div>';
                                        }
                                        if(in_array($in_array_check[$i],$bulkAccessArray)) { 
                                             $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">'.$row_p['mcd_name'].' :</span><span style="width:20%;display:inline-block">'.count_usr_pen_tsk($dbcon,$row_p['mcd_id'],$_SESSION['user_id']).' </span></div>';
                                        }
                                 } 
                                 $i++; 
                              }
                              
                              $template .= '
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">Pending P.O. Upload : </span><span style="width:20%;display:inline-block">'.count_pend_po_upload($dbcon,$_SESSION['user_id']).'</span></div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">Pending SO Approve : </span><span style="width:20%;display:inline-block">'.count_pend_so_approve_count($dbcon,$_SESSION['user_id']).'</span></div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">Pending Dispatch : </span><span style="width:20%;display:inline-block">'.count_pend_disp($dbcon).'</span></div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">Upcoming Appointments :</span><span style="width:20%;display:inline-block"> '.count_pend_appoint($dbcon,$_SESSION['user_id']).'</span></div>
                           </td>

                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:40%;border-radius: 4px;border: 1px solid #165FAC;margin: 20px;display: inline-block;padding: 20px;height:auto">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">CRM - Pending Tasks</p>';

                             $personal_in_array_check = array(DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_GENERAL,DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE,DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION,DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_REVISE,
                  DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_FOLLOWUP); 
                             $query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
                             $query_rs=$dbcon->query($query); 
                             $k = 0;
                              while($row_p=brp_mysqli_fetch_assoc($query_rs)){
                                  
                                 if($row_p['mcd_id'] == GENERAL_TASK_TYPE) {
                                    $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">'.$row_p['mcd_name'].' :</span><span style="width:20%;display:inline-block">'.count_general_pen_tsk($dbcon, $_SESSION['user_id'], false).'</span> </div>';
                                 } 
                                 else {
                                        if($row_p['mcd_id'] == '21'){
                                             $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">PENDING QUOTATION APPOVAL :</span><span style="width:20%;display:inline-block">'.count_user_pending_quot_approval($dbcon,$_SESSION['user_id']).'</span> </div>';
                                        }
                                        if(in_array($personal_in_array_check[$k],$bulkAccessArray)) {
                                             $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">'.$row_p['mcd_name'].' :</span><span style="width:20%;display:inline-block">'.count_usr_pen_tsk1($dbcon,$row_p['mcd_id'],$_SESSION['user_id']).' </span></div>';
                                        }
                                 } 
                                 $k++; 
                              }
                              
                              $template .= '
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">Pending P.O. Upload : </span><span style="width:20%;display:inline-block">'.count_pend_po_upload($dbcon,$_SESSION['user_id']).'</span></div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">Pending SO Approve : </span><span style="width:20%;display:inline-block">'.count_pend_so_approve_count($dbcon,$_SESSION['user_id']).'</span></div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">Pending Dispatch : </span><span style="width:20%;display:inline-block">'.count_pend_disp($dbcon).'</span></div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;"><span style="width:55%;display:inline-block">Upcoming Appointments :</span><span style="width:20%;display:inline-block"> '.count_pend_appoint($dbcon,$_SESSION['user_id']).'</span></div>
                           </td>
                        </tr>

                        <tr>
                           <td style="margin-top:20px" align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">MRP</p>
                           </td>
                        </tr>

                        <tr>
                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:40%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;"> MRP</p>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Sales Order Wise Planning :</span>
                                  <span style="width:20%;display:inline-block">'.count_so_procuct_req($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Min-Max Planning :</span>
                                  <span style="width:20%;display:inline-block">'.count_min_max($dbcon,'min_max').'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Requisition By All Department :</span>
                                  <span style="width:20%;display:inline-block">'.count_stock_procuct_req($dbcon).'</span> </div>';
                                  
                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Reject Product Planning :</span>
                                  <span style="width:20%;display:inline-block">'.count_reject_procuct_req($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Forecast :</span>
                                  <span style="width:20%;display:inline-block">'.count_so_procuct_req($dbcon).'</span> </div>';            
                         
                         $template .= '</td></tr> 
                         <tr>
                           <td style="margin-top:20px" align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">PURCHASE</p>
                           </td>
                        </tr>
                         <tr>
                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:40%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;"> PURCHASE </p>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Pending Indent :</span>
                                  <span style="width:20%;display:inline-block">'.pending_indent_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Purchase Quotation List :</span>
                                  <span style="width:20%;display:inline-block" >'.purchase_quotation_list_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Purchase Order Pending :</span>
                                  <span style="width:20%;display:inline-block">'.purchse_order_pending_count($dbcon).'</span> </div>';
                                  
                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Purchase Order Pending Approval :</span>
                                  <span style="width:20%;display:inline-block">'.purchse_order_pending_approval_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Overdue Purchase Inward :</span>
                                  <span style="width:20%;display:inline-block">'.purchse_overdue_pending_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Pending Purchase Bill :</span>
                                  <span style="width:20%;display:inline-block">'.purchase_bill_pending_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Pending Debit Note :</span>
                                  <span style="width:20%;display:inline-block">'.debit_note_pending_count($dbcon).'</span> </div>';                    
                         
                         $template .= '</td></tr><tr>
                           <td style="margin-top:20px" align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">PRODUCTION</p>
                           </td>
                        </tr>

                        <tr>
                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:40%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">
                                PENDING JOB CARD</p>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Job Card :</span>
                                  <span style="width:20%;display:inline-block">'.pending_job_card_new_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Pending Job Work :</span>
                                  <span style="width:20%;display:inline-block">'.pending_job_work_count($dbcon).'</span> </div>';   
                              
                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Pending Job Work GRN :</span>
                                  <span style="width:20%;display:inline-block">'.pending_job_card_count($dbcon).'</span> </div>
                           </td>

                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:70%;border-radius: 4px;border: 1px solid #165FAC;margin: 20px;display: inline-block;padding: 20px;height:auto">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">Inhouse Pending Process</p>';
                             
                              $template .= '
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c">
                                <span style="width:10%;display:inline-block"># </span>
                                <span style="width:20%;display:inline-block">Process Name</span>
                                <span style="width:20%;display:inline-block">Total Pending</span>
                                <span style="width:20%;display:inline-block">Working Qty</span>
                                <span style="width:20%;display:inline-block">Reprocess Qty</span>
                              </div>';

                              $process_array = $bulkcheck =  [];
                              $tr = 0; 
                              $cnt=1;
                              $sel_p1=$dbcon->query("select * from process_mst where process_status='0' 
                               order by process_name ");
                              while($row_p1=brp_mysqli_fetch_assoc($sel_p1))
                              {
                                $process_array[] = 'dashboard-inhouse-'.str_replace(' ', '-', brp_strtolower($row_p1['process_name'])); 
                              }
                              $bulkcheck = canCheckPermissionAccess($dbcon, $process_array);
                              $sel_p=$dbcon->query("select * from process_mst where process_status='0' 
                               order by process_name ");
                              while($row_p=brp_mysqli_fetch_assoc($sel_p))
                              {
                                if(in_array($process_array[$tr],$bulkcheck)) {
                                  $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                    <span style="width:10%;display:inline-block">'.$cnt.'</span>
                                    <span style="width:20%;display:inline-block">'.$row_p['process_name'].'</span>
                                    <span style="width:20%;display:inline-block">'.count_process_qty($dbcon,$row_p['process_id'],'1').'</span>
                                    <span style="width:20%;display:inline-block">'.count_working_process_qty($dbcon,$row_p['process_id'],'1').'</span>
                                    <span style="width:20%;display:inline-block">'.count_re_process_qty($dbcon,$row_p['process_id'],'1').'</span>
                                  </div>';
                                }

                                $tr++;
                                
                                $cnt++;
                              }  
                    $template .= '</td></tr>
                        <tr>
                           <td style="margin-top:20px" align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">QC PENDING</p>
                           </td>
                        </tr>
                        <tr>
                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:40%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;"> QC PENDING </p>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;margin:10px;">
                                  <span style="width:55%;display:inline-block">Purchase QC Pending :</span>
                                  <span style="width:20%;display:inline-block">'.purchase_qc_pending_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;margin:10px;">
                                  <span style="width:55%;display:inline-block">Parts QC Pending :</span>
                                  <span style="width:20%;display:inline-block">'.parts_qc_pending_count($dbcon).'</span> </div>';

                              $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;margin:10px;">
                                  <span style="width:55%;display:inline-block">Process QC Pending:</span>
                                  <span style="width:20%;display:inline-block"></div>'; 

                               
                              $part_qc_cou=0;
                              $partsqcpending="SELECT trn.process_id,trn.process_name FROM `process_mst` as trn
                                    WHERE trn.process_status=0 and trn.company_id=".$_SESSION['company_id']." ".$where_part_qc_db."";
                                    
                              $result_part_qc=$dbcon->query($partsqcpending);
                              while($parts_qc_row=brp_mysqli_fetch_assoc($result_part_qc)){
                              
                                $part_qc_cou=parts_qc_count_process_wise($dbcon,$parts_qc_row['process_id']);   
                                $template .= '<div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;margin:10px;">
                                    <span style="width:55%;display:inline-block">'.$parts_qc_row['process_name'].'</span>
                                    <span style="width:20%;display:inline-block">'.$part_qc_cou.'</div>';  
                              }              
                     $template .= '</td></tr>
                        <tr>
                           <td style="margin-top:20px" align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">SERVICE</p>
                           </td>
                        </tr>
                        <tr>
                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:25%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">
                                COMPLAINT</p>';
                              
                              $template .= '
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">New Complaint Registered : </span>
                                <span style="width:20%;display:inline-block">'.bussiness_registered_count($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Complaint Assigned : </span>
                                <span style="width:20%;display:inline-block">'.bussiness_assign_count($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Employess Started :</span>
                                <span style="width:20%;display:inline-block"> '.bussiness_e_start_count($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Employess Not Started :</span>
                                <span style="width:20%;display:inline-block"> '.bussiness_e_notstart_count($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Closed :</span>
                                <span style="width:20%;display:inline-block"> '.bussiness_count($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Not Done :</span>
                                <span style="width:20%;display:inline-block"> '.turnover_count($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Total Complaint :</span>
                                <span style="width:20%;display:inline-block"> '.all_comp_cnt_count($dbcon).'</span>
                              </div>
                           </td>

                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:25%;border-radius: 4px;border: 1px solid #165FAC;margin: 20px;display: inline-block;padding: 20px;height:auto">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">EMPLOYEE</p>';
                              
                              $template .= '
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Employee Present : </span>
                                <span style="width:20%;display:inline-block">'.e_present_count($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Employee Absent : </span>
                                <span style="width:20%;display:inline-block">'.e_absent_count($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Expense Pending  :</span>
                                <span style="width:20%;display:inline-block"> '.exp_approval_count($dbcon).'</span>
                              </div>
                           </td>

                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:25%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">
                                SPARE PARTS</p>';
                            
                             $usertype=$_SESSION['user_type'];
                              if($usertype!='3'){ 
                               $template .= '
                                <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Spare Part To send : </span>
                                  <span style="width:20%;display:inline-block">'.new_spare_count($dbcon).'</span>
                                </div>
                                <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Spare Part To Receive : </span>
                                  <span style="width:20%;display:inline-block">'.old_spare_count($dbcon).'</span>
                                </div>';
                               }else{
                                 $template .= '
                                <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Spare Part To Receive : </span>
                                  <span style="width:20%;display:inline-block">'.new_spare_count($dbcon).'</span>
                                </div>
                                <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Spare Part To send : </span>
                                  <span style="width:20%;display:inline-block">'.old_spare_count($dbcon).'</span>
                                </div>';
                               }
                           $template .= '</td>
                        </tr>
                        <tr>
                           <td style="margin-top:20px" align="center" bgcolor="#165FAC" colspan="4" height="50" valign="middle">
                              <p style="margin: 0;font-size: 18px;color:#fff;">FINANCE</p>
                           </td>
                        </tr>
                        <tr>
                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:32%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">
                                Invoice</p>';
                              
                              $template .= '
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;display:none">
                                <span style="width:55%;display:inline-block">Invoice Unadjusted amount : </span>
                                <span style="width:20%;display:inline-block">'.count_invoice_unadjusted($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Pending Order Invoice : </span>
                                <span style="width:20%;display:inline-block">'.count_pending_order_invoice($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Pending Spare Invoice : </span>
                                <span style="width:20%;display:inline-block">'.count_pending_spare_invoice($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Pending Service Charge Invoice :</span>
                                <span style="width:20%;display:inline-block"> '.count_pending_service_charge_invoice($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Pending FOC Spare Invoice :</span>
                                <span style="width:20%;display:inline-block"> '.count_pending_foc_spare_invoice($dbcon).'</span>
                              </div>
                              <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                <span style="width:55%;display:inline-block">Pending Invoice Approval :</span>
                                <span style="width:20%;display:inline-block"> '.count_pending_invoice_approval($dbcon).'</span>
                              </div>
                           </td>

                           <td align="left" colspan="2" height="70" valign="top" style="/*! padding-left: 20px; */width:32%;border-radius: 4px;border: 1px solid #165FAC;display: inline-table;/*! margin: 20px; */margin: 20px;padding: 20px;">
                              <p style="margin: 0;font-size: 15px;line-height: 18px;font-weight: bold;color: #fff;margin-bottom: 10px;background: #165FAC;padding: 10px;border-radius: 4px;">
                                Purchase</p>';
                            
                             
                               $template .= '
                                <div style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;">
                                  <span style="width:55%;display:inline-block">Purchase Unadjusted amount : </span>
                                  <span style="width:20%;display:inline-block">'.count_purchase_unadjusted($dbcon).'</span>
                                </div>
                               ';
                               
                           $template .= '</td>
                        </tr>';         
                     $template .= '</tbody>
                     <tfoot>
                        <tr>
                           <td align="center" height="50" valign="middle">
                              <table border="0" cellpadding="0" cellspacing="0" width="95%">
                                 <tbody>
                                    <tr>
                                       <td align="left" valign="middle">
                                          <p style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;margin:10px;">Thanks</p>
                                          <p style="margin: 0;font-size: 12px;line-height: 18px;font-weight: normal;color: #4c4c4c;margin:10px;margin:10px;"></p>
                                       </td>
                                    </tr>
                                 </tbody>
                              </table>
                           </td>
                        </tr>
                     </tfoot>
                  </table>
               </td>
            </tr>
         </tbody>
      </table>
   </body>
</html>';

// return $template;
echo $template;die;

?>
