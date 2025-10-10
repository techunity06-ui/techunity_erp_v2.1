<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
define('COMMON_FUNCTION_OUTER_PATH','../include/common_functions/');

if (!defined('COMMON_FUNCTION_PATH')) {
    define('COMMON_FUNCTION_PATH', '/some/path/');
}
define('COMMON_FUNCTION_INNER_PATH','../../../include/common_functions/');

define("PRINT_ROOT","print/");

/*
 *  ENTRY TYPES
 */
define('CREDIT', 1);
define('DEBIT', 2);

/*
 *  Payment Type
 */
define('CR', 1);
define('DR', 2);

/*
 *  Product Type
 */

define('BOTH', 0);
define('PURCHASE', 1);
define('SALES', 2);
define('CHARGES', 3);

/*
 * STATUS
 */

define('ACTIVE', 0);
define('DELETED', 2);
define('TEMP', 3);

/*
 *   ACCOUNT GROUPS
 */
define('BRANCH_DIVISIONS', 12);
define('CAPITAL_ACCOUNT', 13);
define('CURRENT_ASSETS', 14);
define('CURRENT_LIABILITIES', 15);
define('DIRECT_EXPENSES', 16);
define('DIRECT_INCOMES', 17);
define('FIXED_ASSETS', 18);
define('INDIRECT_EXPENSES', 19);
define('INDIRECT_INCOMES', 20);
define('INVESTMENTS', 21);
define('LOANS_LIABILITY', 22);
define('MISC_EXPENSES_ASSET', 23);
define('PURCHASE_ACCOUNTS', 24);
define('SALES_ACCOUNTS', 25);
define('SUSPENSE_ACCOUNTS', 26);
define('BANK_ACCOUNTS', 27);
define('BANK_OD_ACCCOUNTS', 28);
define('CASH_IN_HAND', 29);
define('DEPOSITS_ASSET', 30);
define('DUTIES_AND_TAXES', 31);
define('LOANS_ADVANCES_ASSET', 32);
define('PROVISIONS', 33);
define('RESERVES_AND_SURPLUS', 34);
define('SECURED_LOANS', 35);
define('STOCK_ASSETS', 36);
define('SUNDRY_CREDITORS', 37);
define('SUNDRY_DEBTORS', 38);
define('UNSECURED_LOANS', 39);

define('SALARY_ACCOUNT', 58);
define('TEMPORARY_ACCOUNT', 99);
define('TAX_ASSETS', 100);
define('STOCK_EXPENSES', 102);

// Ledgers 
define('CASH', 12);
define('STOCK_IN_HAND', 17);
define('SALES_ACCOUNT',24240);
define('PURCHASE_ACCOUNT',24430);
define('CGST_LEDGER',9870);
define('SGST_LEDGER',9880);
define('IGST_LEDGER',9890);
define('TCS',9892);
/*
 *   OPPORTUNITY STAGES
 */
define('PROSPECTING', 5);
define('FIRST_CALL', 7);
define('MEETING', 9);
define('DEMO', 10);
define('FINAL_CALL_NEGOTIATION', 11);
define('WON', 12);
define('LOST', 13);

/*
 *   TASK TYPES
 */
define('NEW_QUOTATION_CREATION', 15);
define('INQUIRY_FOLLOW_UP', 16);
define('REVISE_QUOTATION', 20);
define('QUOTATION_FOLLOW_UP', 21);

/*
 *   INVOICE TYPES
 */
define('COMPLAINT', 1);
define('INQUIRY', 2);
define('QUOTATION', 3);
define('TAX_INVOICE', 8);
define('PAYMENT', 9);
define('BOM', 11);
define('PO', 12);
define('PLN', 13);
define('GRN', 14);
define('MIN', 15);
define('PO-REQ', 16);
define('BILL_OF_SUPPLY', 17);
define('PURCHASE_BILL', 18);
define('DEBIT_NOTE', 19);
define('QC', 20);
define('STCOK_ADJUSTMENT', 21);
//PATHIK START
define('INHOUSE_JOB_WORK', 25);
define('OUTSIDE_JOB_WORK', 38);
define('OUTSIDE_JOB_WORK_CHALAN', 39);

define('INHOUSE_GRN', 26);
//PATHIK END

define('BATCH', 30); // ADDED SANAT
define('ISSUE', 32); // ADDED SANAT

//Dhaval Start
define('INVOICE_TYPE_SALE_RETURN', 52);
//Dhaval End
/*
 *   SALES STAGES
 */
define('HOT', 4);
define('COLD', 5);
define('WARM', 6);
define('NOT APPLICABLE', 7);

/**
 * Amish Soni 05-01-2021
 * COMPLAINT SLUGS
 */
define('COMPLAINT_SLUG_VIEW', 'complaint-history');
define('COMPLAINT_SLUG_CREATE', 'complaint-add');
define('COMPLAINT_SLUG_READ', 'complaint-list');
define('COMPLAINT_SLUG_EDIT', 'complaint-edit');
define('COMPLAINT_SLUG_DELETE', 'complaint-delete');


/**
    RESOURCE WQORKING HOURS
*/

define('RESOURCE_START_SHIFT_TIME', '10:00:00');
define('RESOURCE_END_SHIFT_TIME', '19:00:00');

/**
    EMAIL TAG PREFIX
*/
define('EMAIL_INSERT_TAG_PREFIX', '{{');
define('EMAIL_INSERT_TAG_POSTFIX', '}}');  

/**
    MAX FOLLOWUP DATE
*/
define('MAX_FOLLOWUP_DATE', 50);  

/**
 * Umair 06-01-2021
 * START
 */
/**
    PURCHASE MODULE PERMISSION SECTION 
*/
// PURCHASE DASHBORD 
define('PURCHASE_DASHBOARD_VIEW', 'purchase-dashboard-view');

// INDENT   
define('INDENT_VIEW', 'indent-view');
define('INDENT_APPROVE', 'indent-approve');

//PRE
define('PRE_VIEW', 'pre_view');
// PURCHASE QUOTATION
define('PO_QUOTATION_VIEW', 'po-quotation-view');
define('PO_QUOTATION_ADD', 'po-quotation-add');
define('PO_QUOTATION_READ', 'po-quotation-read');
define('PO_QUOTATION_UPDATE', 'po-quotation-update');
define('PO_QUOTATION_DELETE', 'po-quotation-delete');
define('PO_QUOTATION_APPROVE', 'po-quotation-approve');
define('PO_QUOTATION_FINAL_APPROVE', 'po-quotation-final-approve');

// PURCHASE ORDER LIST
define('PO_REQ_VIEW', 'po-req-view');
define('PO_REQ_ADD', 'po-req-add');
define('PO_REQ_READ', 'po-req-read');
define('PO_REQ_UPDATE', 'po-req-update');
define('PO_REQ_DELETE', 'po-req-delete');
define('PO_REQ_APPROVE', 'po-req-approve');

// PURCHASE ORDER PENDING
define('PO_LIST_VIEW', 'po-list-view');
define('PO_LIST_ADD', 'po-list-add');
define('PO_LIST_READ', 'po-list-read');
define('PO_LIST_UPDATE', 'po-list-update');
define('PO_LIST_DELETE', 'po-list-delete');
define('PO_LIST_APPROVE', 'po-list-approve');

// OVERDUE PURCHASE INWARD
define('OVERDUE_PO_PRO_VIEW', 'overdue-po-pro-view');
define('OVERDUE_PO_PRO_ADD', 'overdue-po-pro-add');
define('OVERDUE_PO_PRO_READ', 'overdue-po-pro-read');
define('OVERDUE_PO_PRO_UPDATE', 'overdue-po-pro-update');
define('OVERDUE_PO_PRO_DELETE', 'overdue-po-pro-delete');
define('OVERDUE_PO_PRO_APPROVE', 'overdue-po-pro-approve');

// PENDING PURCHASE BILL

define('PURCHASE_BILL_PENDING_VIEW', 'purchase-bill-pending-view');
define('PURCHASE_BILL_PENDING_ADD', 'purchase-bill-pending-add');

// PENDING DEBIT NOTE
define('DEBIT_NOTE_PENDING_VIEW', 'debit-note-pending-view');
define('DEBIT_NOTE_PENDING_ADD', 'debit-note-pending-add');
define('DEBIT_NOTE_PENDING_READ', 'debit-note-pending-read');
define('DEBIT_NOTE_PENDING_UPDATE', 'debit-note-pending-update');
define('DEBIT_NOTE_PENDING_DELETE', 'debit-note-pending-delete');

// PURCHASE BILL LIST
define('PURCHASE_BILL_VIEW', 'purchase-list-view');
define('PURCHASE_BILL_ADD', 'purchase-list-add');
define('PURCHASE_BILL_READ', 'purchase-list-read');
define('PURCHASE_BILL_UPDATE', 'purchase-list-update');
define('PURCHASE_BILL_DELETE', 'purchase-list-delete');
define('PURCHASE_BILL_APPROVE', 'purchase-list-approve');
define('PURCHASE_BILL_FINAL_APPROVE', 'purchase-list-final-approve');

// DEBIT NOTE LIST
define('DEBIT_PENDING_NOTE_VIEW', 'debit-pending-note-view');
define('DEBIT_PENDING_NOTE_ADD', 'debit-pending-note-add');
define('DEBIT_PENDING_NOTE_READ', 'debit-pending-note-read');
define('DEBIT_PENDING_NOTE_UPDATE', 'debit-pending-note-update');
define('DEBIT_PENDING_NOTE_DELETE', 'debit-pending-note-delete');


// PURCHASE CARD LIST
define('PURCHASE_CARD_VIEW', 'purchase-card-list-view');
define('PURCHASE_CARD_ADD', 'purchase-card-add');
define('PURCHASE_CARD_READ', 'purchase-card-read');
define('PURCHASE_CARD_UPDATE', 'purchase-card-update');
define('PURCHASE_CARD_DELETE', 'purchase-card-delete');
define('PURCHASE_CARD_APPROVE', 'purchase-card-approve');
define('PURCHASE_CARD_ACTIVE', 'purchase-card-active');


/**
 * Umair 06-01-2021
 * END
 */

/**
 * Dimple Panchal 06-01-2021
 * Module View SLUGS
 */
define('CRM_SLUG_VIEW','crm-module-view');
define('SCHEDULING_SLUG_VIEW','scheduling-module-view');
define('MRP_SLUG_VIEW','mrp-module-view');
/*START JAEYSH*/
define('DESIGN_DEPARTMENT_SLUG_VIEW','design-department-module-view');
/*END JAEYSH*/
define('PURCHASE_SLUG_VIEW','purchase-module-view');
define('PRODUCTION_SLUG_VIEW','production-module-view');
define('RESOURCE_SLUG_VIEW','resource-module-view');
define('INVENTORY_SLUG_VIEW','inventory-module-view');
define('QC_SLUG_VIEW','qc-module-view');
define('SERVICE_SLUG_VIEW','service-module-view');
define('FINANCE_SLUG_VIEW','finance-module-view');
define('HRMS_SLUG_VIEW', 'hrms-module-view');
define('MAINTENANCE_SLUG_VIEW','maintenance-module-view');
define('DISTRIBUTOR_PORTAL_SLUG_VIEW','distributor-module-view');
define('VENDOR_PORTAL_SLUG_VIEW','vendor-module-view');
define('SUPPORT_TICKET_SLUG_VIEW','support-module-view');

// INQUIRY SLUGS
define('INQUIRY_SLUG_READ', 'inquiry-list');
define('INQUIRY_SLUG_CREATE', 'inquiry-add');
define('INQUIRY_SLUG_EDIT', 'inquiry-edit');
define('INQUIRY_SLUG_DELETE', 'inquiry-delete');
define('INQUIRY_SLUG_VIEW', 'inquiry-history');

// TASK SLUGS
define('TASK_SLUG_READ', 'task-list');
define('TASK_SLUG_CREATE', 'task-add');
define('TASK_SLUG_EDIT', 'task-edit');
define('TASK_SLUG_DELETE', 'task-delete');

// APPOINTMNET SLUGS
define('APPOINTMNET_SLUG_READ', 'appointment-list');
define('APPOINTMNET_SLUG_CREATE', 'appointment-add');
define('APPOINTMNET_SLUG_EDIT', 'appointment-edit');
define('APPOINTMNET_SLUG_DELETE', 'appointment-delete');

//QUOTATION SLUGS
define('QUOTATION_SLUG_READ', 'quotation-list');
define('QUOTATION_SLUG_CREATE', 'quotation-add');
define('QUOTATION_SLUG_EDIT', 'quotation-edit');
define('QUOTATION_SLUG_DELETE', 'quotation-delete');
define('QUOTATION_SLUG_APPROVE', 'quotation-approve');
define('QUOTATION_SLUG_FINAL_APPROVE', 'quotation-final-approve');
define('QUOTATION_SLUG_PRINT', 'quotation-print');

//SALES ORDER SLUGS
define('SALES_ORDER_SLUG_READ', 'sales-order-list');
define('SALES_ORDER_SLUG_CREATE', 'sales-order-create');
define('SALES_ORDER_SLUG_EDIT', 'sales-order-edit');
define('SALES_ORDER_SLUG_DELETE', 'sales-order-delete');
define('SALES_ORDER_SLUG_APPROVE', 'sales-order-approve');
define('SALES_ORDER_SLUG_PRINT', 'sales-order-delete');
define('SALES_ORDER_SLUG_FINAL_APPROVE', 'sales-order-final-approve');

//ORDER CONFIRMATION SLUGS
define('ORDER_CONFIRMATION_SLUG_READ', 'order-confirm-list');
define('ORDER_CONFIRMATION_SLUG_PO_APPROVE', 'order-confirm-po-approve');
define('ORDER_CONFIRMATION_SLUG_PO_FINAL_APPROVE', 'order-confirm-po-final-approve');
define('ORDER_CONFIRMATION_SLUG_PAYMENT', 'order-confirm-payment');

// FORECAST SLUGS
define('FORECAST_SLUG_READ', 'forecast-byuser-pro-list');
define('FORECAST_SLUG_CREATE', 'forecast-byuser-pro-add');
define('FORECAST_SLUG_EDIT', 'forecast-byuser-pro-edit');
define('FORECAST_SLUG_DELETE', 'forecast-byuser-pro-delete');


//JOBWORK CARD SLUGS
define('JOBWORK_RATE_CARD_VIEW', 'jobwork-rate-card-list-view');
define('JOBWORK_RATE_CARD_ADD', 'jobwork-rate-card-add');
define('JOBWORK_RATE_CARD_READ', 'jobwork-rate-card-read');
define('JOBWORK_RATE_CARD_UPDATE', 'jobwork-rate-card-update');
define('JOBWORK_RATE_CARD_DELETE', 'jobwork-rate-card-delete');
define('JOBWORK_RATE_CARD_APPROVE', 'jobwork-rate-card-approve');
define('JOBWORK_RATE_CARD_ACTIVE', 'jobwork-rate-card-active');


// Working Dashboard
define('WD_TEAM_PENDING_TASK_SLUG_READ', 'wd-team-pending-task');
define('WD_PENDING_TASK_SLUG_READ', 'wd-pending-task');
define('WD_COMPALINT_SLUG_READ', 'wd-complaint');
define('WD_EMPLOYEE_SLUG_READ', 'wd-employee');
define('WD_MRP_SLUG_READ', 'wd-mrp');
/*START JAYESH*/
define('WD_DESIGN_DEPARTMENT_SLUG_READ', 'wd-design-department');
/*END JAYESH*/
define('WD_SPARE_PARTS_SLUG_READ', 'wd-spare-parts');
define('WD_PENDING_JOB_CARD_SLUG_READ', 'wd-pending-job-card');
define('WD_PURCHASE_SLUG_READ', 'wd-purchase');
define('WD_QC_PENDING_SLUG_READ', 'wd-qc-pending');
define('WD_USER_INQUIRY_SLUG_READ', 'wd-user-inquiry');
define('WD_INHOUSE_PENDING_PROCESS_SLUG_READ', 'wd-inhouse-pending-process');
define('WD_OUTSIDE_PENDING_PROCESS_SLUG_READ', 'wd-outside-pending-process');
define('WD_VENDOR_UNADJUSTED_AMOUNT','wd-vendor-unadjusted-amount');
define('WD_CUSTOMER_UNADJUSTED_AMOUNT','wd-customer-unadjusted-amount');

/**
 * Dimple Panchal End 06-01-2021
 */

/**
 * Umair start : 08-01-2021
 */
// VENDOR ANALISIS
define('VENDOR_ANALYSIS_VIEW', 'vender-analysis-view');

// PURCHASE BILL ITEM GROUP WISE
define('VENDOR_ANALYSIS_REPORT_VIEW', 'vender-analysis-report-view');

// PURCHASE BILL SUMMART REPORT
define('PURCHASE_BILL_SUMMARY_REPORT_VIEW', 'purchase-bill-summary-report-view');

// PURCHASE ORDER SUMMARY REPORT
define('PENDING_PURCHASE_ORDER_BRIEF_REPORT_VIEW', 'pending-purchase-order-brief-eport-view');

// PURCHASE ORDER SUMMARY WORK ORDER WISE
define('PENDING_PURCHASE_ORDER_SUMMARY_REPORT_VIEW', 'pending-purchase-order-sumary-report-view');

// PENDING PURCHASE ORDER FOLLOW UP REPORT
define('PENDING_PURCHASE_ORDER_FOLLOWUP_REPORT_VIEW', 'pending-purchase-order-follow-up-report-view');

// PENDING PURCHASE ORDER SUMARY WITH RATE REPORT
define('PENDING_PURCHASE_ORDER_SUMMARY_WITHRATE_REPORT_VIEW', 'pending-purchase-order-sumary-withrate-report-view');

// PENDING PURCHASE ORDER REPORT
define('PENDING_PURCHASE_ORDER_REPORT_VIEW', 'pending-purchase-order-report-view');

// PENDING PURCHASE ORDER GROUP WISE
define('PENDING_PURCHASE_ORDER_ITEM_WISE_REPORT_VIEW', 'pending-purchase-order-item-wise-report-view');

// PURCHASE ORDER STATUS REPORT
define('PENDING_PURCHASE_ORDER_STATUS_REPORT_VIEW', 'pending-purchase-order-status-report-view');

// PRICE LIST
define('PRICE_LIST_REPORT_VIEW', 'price-list-report-view');

/**
    RESOURCE MODULE PERMISSION SECTION 
*/

// RESOURCE DASHBOARD
define('RESOURCE_DASHBOARD_VIEW', 'resource-dashboard-view');
    
// RESOURCE MASTER DATA
define('RESOURCE_VIEW', 'resource-view');
define('RESOURCE_ADD', 'resource-add');
define('RESOURCE_UPDATE', 'resource-update1');

// RESOURCE TRANSFER
define('RESOURCE_TRANSFER_VIEW', 'resource-transfer-view');
define('RESOURCE_TRANSFER_ADD', 'resource-transfer-add');
define('RESOURCE_TRANSFER_READ', 'resource-transfer-read');
define('RESOURCE_TRANSFER_UPDATE', 'resource-transfer-update');
define('RESOURCE_TRANSFER_DELETE', 'resource-transfer-delete');

// RESOURCE REPORT
define('RESOURCE_REPORT_VIEW', 'resource-report-view1');
define('RESOURCE_REPORT_ADD', 'resource-report-add1');
define('RESOURCE_REPORT_READ', 'resource-report-read1');
define('RESOURCE_REPORT_UPDATE', 'resource-report-update1');
define('RESOURCE_REPORT_DELETE', 'resource-report-delete1');

/**
 * FINANCE REPORT SLUGS
 */
// Finance - Profit Loss Report Slugs
define('FINANCE_PROFIT_LOSS_REPORT_LIST', 'finance-profit-loss-report-list');
define('FINANCE_BALANCE_SHEET_REPORT_VIEW', 'balance-sheet-report-view');
define('FINANCE_GSTR_1_REPORT_VIEW', 'gstr-1-report-view');
define('FINANCE_CUSTOMER_SALES_SUMMARY_VIEW', 'customer-sales-summary-view');

// Finance - Make Payment List Slugs
define('FINANCE_MAKE_PAYMENT_LIST', 'finance-payment-list');
define('FINANCE_MAKE_PAYMENT_CREATE', 'finance-payment-create');
define('FINANCE_MAKE_PAYMENT_EDIT', 'finance-payment-edit');
define('FINANCE_MAKE_PAYMENT_DELETE', 'finance-payment-delete');
define('FINANCE_MAKE_PAYMENT_PRINT', 'finance-payment-print');

// Finance - Closing Balance List Slugs
define('FINANCE_CLOSING_BALANCE_LIST', 'finance-closing-balance-list');
define('FINANCE_CLOSING_BALANCE_CREATE', 'finance-closing-balance-create');
define('FINANCE_CLOSING_BALANCE_EDIT', 'finance-closing-balance-edit');
define('FINANCE_CLOSING_BALANCE_DELETE', 'finance-closing-balance-delete');

// Finance - Charts Of Account Slugs
define('FINANCE_CHARTS_OF_ACCOUNT_LIST', 'finance-charts-of-account-list');
define('FINANCE_CHARTS_OF_ACCOUNT_CREATE', 'finance-charts-of-account-create');
define('FINANCE_CHARTS_OF_ACCOUNT_EDIT', 'finance-charts-of-account-edit');
define('FINANCE_CHARTS_OF_ACCOUNT_DELETE', 'finance-charts-of-account-delete');

// Finance - Employee Expense List Slugs
define('FINANCE_EMPLOYEE_EXPENSE_LIST', 'finance-employee-expense-list');
define('FINANCE_EMPLOYEE_EXPENSE_STATUS', 'finance-employee-expense-status');
define('FINANCE_EMPLOYEE_EXPENSE_DELETE', 'finance-employee-expense-delete');

// Finance - Expense Detail List Slugs
define('FINANCE_EXPENSE_DETAIL_LIST', 'finance-expense-detail-list');
define('FINANCE_EXPENSE_DETAIL_CREATE', 'finance-expense-detail-create');
define('FINANCE_EXPENSE_DETAIL_EDIT', 'finance-expense-detail-edit');
define('FINANCE_EXPENSE_DETAIL_DELETE', 'finance-expense-detail-delete');
define('FINANCE_EXPENSE_DETAIL_REQUEST', 'finance-expense-detail-request');

// Finance - Bill Of Supply List Slugs
define('FINANCE_BILL_OF_SUPPLY_LIST', 'finance-bill-of-supply-list');
define('FINANCE_BILL_OF_SUPPLY_CREATE', 'finance-bill-of-supply-create');
define('FINANCE_BILL_OF_SUPPLY_EDIT', 'finance-bill-of-supply-edit');
define('FINANCE_BILL_OF_SUPPLY_PRINT', 'finance-bill-of-supply-print');
define('FINANCE_BILL_OF_SUPPLY_DELETE', 'finance-bill-of-supply-delete');

// Finance - Invoice List Slugs
define('FINANCE_SPARE_TO_BOS', 'finance-spare-to-bos');
define('FINANCE_INVOICE_LIST', 'finance-invoice-list');
define('FINANCE_INVOICE_CREATE', 'finance-invoice-create');
define('FINANCE_INVOICE_EDIT', 'finance-invoice-edit');
define('FINANCE_INVOICE_DELETE', 'finance-invoice-delete');
define('FINANCE_INVOICE_RECEIPT', 'finance-invoice-receipt');
define('FINANCE_INVOICE_CHALAN', 'finance-invoice-chalan');
define('FINANCE_INVOICE_SO', 'finance-invoice-so');

// Finance - Price List Slugs

define('PRICE_LIST_CREATE', 'price-list-create');
define('PRICE_LIST_VIEW', 'price-list-view');
define('PRICE_LIST_EDIT', 'price-list-edit');
define('PRICE_LIST_DELETE', 'price-list-delete');


// Proforma Invoice Slugs
define('FINANCE_PROFORMA_INVOICE_LIST', 'finance-proforma-invoice-list');
define('FINANCE_PROFORMA_INVOICE_CREATE', 'finance-proforma-invoice-create');
define('FINANCE_PROFORMA_INVOICE_EDIT', 'finance-proforma-invoice-edit');
define('FINANCE_PROFORMA_INVOICE_DELETE', 'finance-proforma-invoice-delete');
define('FINANCE_PROFORMA_INVOICE_PRINT', 'finance-proforma-invoice-print');
define('FINANCE_PROFORMA_INVOICE_APPROVE', 'finance-proforma-invoice-approve');

// Finance - Finance Dashboard Slugs
define('FINANCE_DASHBOARD', 'finance-dashboard');

// Finance - Pending Invoice Slugs
define('FINANCE_PENDING_INVOICE_LIST', 'finance-pending-invoice-list');
define('FINANCE_CREATE_INVOICE', 'finance-quot-to-inv');
define('FINANCE_QUOTATION_PRINT', 'finance-quotation-print');
define('FINANCE_SPARE_TO_INVOICE', 'finance-spare-to-inv');
define('FINANCE_SPARE_TO_BILL_OF_SUPPLY', 'finance-spare-to-bos');
define('FINANCE_DISPATCH_LIST', 'finance-dispatch-list');

// Finance - Journal Entry Slugs
define('FINANCE_JOURNAL_LIST', 'finance-journal-list');
define('FINANCE_JOURNAL_CREATE', 'finance-journal-create');
define('FINANCE_JOURNAL_EDIT', 'finance-journal-edit');
define('FINANCE_JOURNAL_DELETE', 'finance-journal-delete');

// Finance - Debit Note Without Item Slugs
define('FINANCE_DEBIT_NOTE_WITHOUT_ITEM_LIST', 'finance-debit-note-without-item-list');
define('FINANCE_DEBIT_NOTE_WITHOUT_ITEM_CREATE', 'finance-debit-note-without-item-create');
define('FINANCE_DEBIT_NOTE_WITHOUT_ITEM_EDIT', 'finance-debit-note-without-item-edit');
define('FINANCE_DEBIT_NOTE_WITHOUT_ITEM_DELETE', 'finance-debit-note-without-item-delete');

// Finance - Credit Note Without Item Slugs
define('FINANCE_CREDIT_NOTE_WITHOUT_ITEM_LIST', 'finance-credit-note-without-item-list');
define('FINANCE_CREDIT_NOTE_WITHOUT_ITEM_CREATE', 'finance-credit-note-without-item-create');
define('FINANCE_CREDIT_NOTE_WITHOUT_ITEM_EDIT', 'finance-credit-note-without-item-edit');
define('FINANCE_CREDIT_NOTE_WITHOUT_ITEM_DELETE', 'finance-credit-note-without-item-delete');

// Finance - Contra Entry Slugs
define('FINANCE_CONTRA_LIST', 'finance-contra-list');
define('FINANCE_CONTRA_CREATE', 'finance-contra-create');
define('FINANCE_CONTRA_EDIT', 'finance-contra-edit');
define('FINANCE_CONTRA_DELETE', 'finance-contra-delete');

// Finance - Received Payment (Receipt) Slugs
define('FINANCE_RECEIPT_LIST', 'finance-receipt-list');
define('FINANCE_RECEIPT_CREATE', 'finance-receipt-create');
define('FINANCE_RECEIPT_EDIT', 'finance-receipt-edit');
define('FINANCE_RECEIPT_DELETE', 'finance-receipt-delete');
define('FINANCE_RECEIPT_PRINT', 'finance-receipt-print');

// Finance - Make Payment Slugs
define('FINANCE_PAYMENT_LIST', 'finance-payment-list');
define('FINANCE_PAYMENT_CREATE', 'finance-payment-create');
define('FINANCE_PAYMENT_EDIT', 'finance-payment-edit');
define('FINANCE_PAYMENT_DELETE', 'finance-payment-delete');
define('FINANCE_PAYMENT_PRINT', 'finance-payment-print');


/**
 * QC SLUGS
 */
define('QC_DONE_LIST', 'qc-done-list-read');
define('QC_DONE_CREATE', 'qc-done-list-create');
define('QC_DONE_EDIT', 'qc-done-list-update');
define('QC_DONE_PURCHASE_QC_PENDING_LIST', 'purchase-qc-pending-list-read');
define('QC_DONE_PURCHASE_QC_PENDING_CREATE', 'purchase-qc-pending-list-create');
define('QC_DONE_PARTS_QC_PENDING_ADD', 'qc-done-parts-qc-pending-add');
define('QC_DONE_PARTS_QC_PENDING_LIST', 'qc-done-parts-qc-pending-list-read');

/**
 * Amish Soni 06-01-2021
 * EMAIL SETTINGS SLUGS
 */
define('MERGE_FIELD_SLUG_CREATE', 'merge-field-add');
define('MERGE_FIELD_SLUG_EDIT', 'merge-field-edit');
define('MERGE_FIELD_SLUG_DELETE', 'merge-field-delete');

define('EMAIL_MODULE_SLUG_CREATE', 'email-module-add');
define('EMAIL_MODULE_SLUG_EDIT', 'email-module-edit');
define('EMAIL_MODULE_SLUG_DELETE', 'email-module-delete');

define('EMAIL_TEMPLATE_SLUG_CREATE', 'email-template-add');
define('EMAIL_TEMPLATE_SLUG_EDIT', 'email-template-edit');
define('EMAIL_TEMPLATE_SLUG_DELETE', 'email-template-delete');

/**
 * Amish Soni 06-01-2021
 * SUPPORT SLUGS
 */
define('SUPPORT_SLUG_READ', 'support-list');
define('SUPPORT_SLUG_CREATE', 'support-add');


/**
 * Umair 13-01-2021
 * MRP SLUGS
 */

// MIN MAX PLANNING
define('STOCK_DETAIL_MINMAX_SLUG_VIEW', 'get-stock-detail-min-max-view');
define('STOCK_DETAIL_MINMAX_SLUG_CREATE', 'get-stock-detail-min-max-create');
define('STOCK_DETAIL_MINMAX_SLUG_READ', 'get-stock-detail-min-max');

// SALES ORDER LIST 
/*define('MRP_SALES_ORDER_SLUG_VIEW', 'mrp-sales-order-list-view');
define('MRP_SALES_ORDER_SLUG_CREATE', 'mrp-sales-order-list-create');
define('MRP_SALES_ORDER_SLUG_READ', 'mrp-sales-order-list');
define('MRP_SALES_ORDER_SLUG_UPDATE', 'mrp-sales-order-list-update');
define('MRP_SALES_ORDER_SLUG_DELETE', 'mrp-sales-order-list-delete');
define('MRP_SALES_ORDER_SLUG_APPROVE', 'mrp-sales-order-list-approve');
define('MRP_SALES_ORDER_SLUG_FINAL_APPROVE', 'mrp-sales-order-list-final-approve');*/

// REJECT PRODUCT PLANNING
define('MRP_REJECT_QC_REQUEST_LIST_SLUG_VIEW', 'mrp-reject-qc-request-list-view');
define('MRP_REJECT_QC_REQUEST_LIST_SLUG_CREATE', 'mrp-reject-qc-request-list-create');
define('MRP_REJECT_QC_REQUEST_LIST_SLUG_READ', 'mrp-reject-qc-request-list');

// REQUISITION BY ALL DEPARTMENT
define('MRP_STOCK_PENDING_REQUEST_SLUG_VIEW', 'mrp-stock-pending-request-view');
define('MRP_STOCK_PENDING_REQUEST_SLUG_CREATE', 'mrp-stock-pending-request-create');
define('MRP_STOCK_PENDING_REQUEST_SLUG_READ', 'mrp-stock-pending-request');

// SALES ORDER PLANNING
define('MRP_GET_SALES_ORDER_SLUG_VIEW', 'mrp-get-sales-order-views');
define('MRP_GET_SALES_ORDER_SLUG_CREATE', 'mrp-get-sales-order-details-create');
define('MRP_GET_SALES_ORDER_SLUG_READ', 'mrp-get-sales-order-details');



/**
 * Umair 13-01-2021
 * PRODUCTION SLUGS
 */

// PRODUCTION DASHBOARD
define('PRODUCTION_DASHBOARD_SLUG_VIEW', 'production-dashboard-view');

// WORK ORDER
define('PRODUCTION_WORK_ORDER_SLUG_VIEW', 'production-work-order-view');

// G.R.N. LIST
define('PRODUCTION_GRN_LIST_SLUG_VIEW', 'production-grn-list-view');
define('PRODUCTION_GRN_LIST_SLUG_CREATE', 'production-grn-list-create');
define('PRODUCTION_GRN_LIST_SLUG_READ', 'production-grn-list-read');
define('PRODUCTION_GRN_LIST_SLUG_UPDATE', 'production-grn-list-update');
define('PRODUCTION_GRN_LIST_SLUG_DELETE', 'production-grn-list-delete');

// JOB CARD
define('PRODUCTION_JOBCARD_LIST_SLUG_VIEW', 'production-job-card-list-view');
define('PRODUCTION_JOBCARD_LIST_SLUG_CREATE', 'production-job-card-list-create');
define('PRODUCTION_JOBCARD_LIST_SLUG_READ', 'production-job-card-list-read');
define('PRODUCTION_JOBCARD_LIST_SLUG_UPDATE', 'production-job-card-list-update');

// JOB WORK ORDER
define('PRODUCTION_PENDING_JOBCARD_SLUG_VIEW', 'production-pending-job-card-view');
define('PRODUCTION_PENDING_JOBCARD_SLUG_CREATE', 'production-pending-job-card-create');
define('PRODUCTION_PENDING_JOBCARD_SLUG_READ', 'production-pending-job-card-update');

// CREATE BOM
define('PRODUCTION_BOM_LIST_SLUG_VIEW', 'production-bom-list-view');
define('PRODUCTION_BOM_LIST_SLUG_CREATE', 'production-bom-list-create');
define('PRODUCTION_BOM_LIST_SLUG_READ', 'production-bom-list-read');
define('PRODUCTION_BOM_LIST_SLUG_UPDATE', 'production-bom-list-update');
define('PRODUCTION_BOM_LIST_SLUG_DELETE', 'production-bom-list-delete');

/**
 * Umair 13-01-2021
 * INVENTORY SLUGS
 */

// STCOK ADJUSTMENT LIST
define('INVENTORY_STOCK_ADJUSTMENT_SLUG_VIEW', 'inventory-stock-adjustment-view');
define('INVENTORY_STOCK_ADJUSTMENT_SLUG_CREATE', 'inventory-stock-adjustment-create');
define('INVENTORY_STOCK_ADJUSTMENT_SLUG_READ', 'inventory-stock-adjustment-read');
define('INVENTORY_STOCK_ADJUSTMENT_SLUG_UPDATE', 'inventory-stock-adjustment-update');
define('INVENTORY_STOCK_ADJUSTMENT_SLUG_DELETE', 'inventory-stock-adjustment-delete');

// MATERIAL ISSUE
define('INVENTORY_MATERIALISSUE_LIST_SLUG_VIEW', 'inventory-materialissue-list-view');
define('INVENTORY_MATERIALISSUE_LIST_SLUG_CREATE', 'inventory-materialissue-list-create');
define('INVENTORY_MATERIALISSUE_LIST_SLUG_READ', 'inventory-materialissue-list-read');
define('INVENTORY_MATERIALISSUE_LIST_SLUG_UPDATE', 'inventory-materialissue-list-update');
define('INVENTORY_MATERIALISSUE_LIST_SLUG_DELETE', 'inventory-materialissue-list-delete');

// STOCK TRANSFER REQUEST
define('INVENTORY_STOCK_TRANSFER_LIST_SLUG_VIEW', 'inventory-stock-transfer-list-view');
define('INVENTORY_STOCK_TRANSFER_LIST_SLUG_CREATE', 'inventory-stock-transfer-list-create');
define('INVENTORY_STOCK_TRANSFER_LIST_SLUG_READ', 'inventory-stock-transfer-list-read');
define('INVENTORY_STOCK_TRANSFER_LIST_SLUG_UPDATE', 'inventory-stock-transfer-list-update');
define('INVENTORY_STOCK_TRANSFER_LIST_SLUG_DELETE', 'inventory-stock-transfer-list-delete');

// STOCK TRANSFER
define('INVENTORY_STOCK_TRANSFER_SLUG_VIEW', 'inventory-stock-transfer-view');
define('INVENTORY_STOCK_TRANSFER_SLUG_CREATE', 'inventory-stock-transfer-create');
define('INVENTORY_STOCK_TRANSFER_SLUG_READ', 'inventory-stock-transfer-read');
define('INVENTORY_STOCK_TRANSFER_SLUG_UPDATE', 'inventory-stock-transfer-update');
define('INVENTORY_STOCK_TRANSFER_SLUG_DELETE', 'inventory-stock-transfer-delete');


// CUSTOMER PARTY MASTER 
define('CUSTOMER_PARTY_MASTER_SLUG_READ', 'crm-master-customer-list');
define('CUSTOMER_PARTY_MASTER_SLUG_CREATE', 'crm-master-customer-create');
define('CUSTOMER_PARTY_MASTER_SLUG_EXPORT', 'crm-master-customer-export');
define('CUSTOMER_PARTY_MASTER_SLUG_UPDATE', 'crm-master-customer-update');
define('CUSTOMER_PARTY_MASTER_SLUG_DELETE', 'crm-master-customer-delete');
define('CUSTOMER_PARTY_MASTER_SLUG_VIEW', 'crm-master-customer-view');

// PARTY INDUSTRY MASTER
define('CUSTOMER_PARTY_INDUSTRY_SLUG_READ', 'crm-master-cust-ind-mst-read');
define('CUSTOMER_PARTY_INDUSTRY_SLUG_CREATE', 'crm-master-cust-ind-mst-create');
define('CUSTOMER_PARTY_INDUSTRY_SLUG_UPDATE', 'crm-master-cust-ind-mst-update');
define('CUSTOMER_PARTY_INDUSTRY_SLUG_DELETE', 'crm-master-cust-ind-mst-delete'); 

// LOST INQUIRY REASON MASTER
define('REASON_LOST_INQUIRY_SLUG_READ', 'crm-master-reason-read');
define('REASON_LOST_INQUIRY_SLUG_CREATE', 'crm-master-reason-create');
define('REASON_LOST_INQUIRY_SLUG_UPDATE', 'crm-master-reason-update');
define('REASON_LOST_INQUIRY_SLUG_DELETE', 'crm-master-reason-delete');

// PARTY CATEGORY MASTER
define('PARTY_CATEGORY_SLUG_READ', 'crm-master-party-category-list');
define('PARTY_CATEGORY_SLUG_CREATE', 'crm-master-party-category-create');
define('PARTY_CATEGORY_SLUG_UPDATE', 'crm-master-party-category-update');
define('PARTY_CATEGORY_SLUG_DELETE', 'crm-master-party-category-delete');

// MASTER CATEGORY MASTER
define('CUSTOMER_MASTER_CATEGORY_SLUG_READ', 'crm-master-category-list');
define('CUSTOMER_MASTER_CATEGORY_SLUG_CREATE', 'crm-master-category-create');
define('CUSTOMER_MASTER_CATEGORY_SLUG_UPDATE', 'crm-master-category-update');
define('CUSTOMER_MASTER_CATEGORY_SLUG_DELETE', 'crm-master-category-delete');

// TERRITORY MASTER
define('CUSTOMER_TERRITORY_SLUG_READ', 'crm-master-territory-list');
define('CUSTOMER_TERRITORY_SLUG_CREATE', 'crm-master-territory-create');
define('CUSTOMER_TERRITORY_SLUG_UPDATE', 'crm-master-territory-update');
define('CUSTOMER_TERRITORY_SLUG_DELETE', 'crm-master-territory-delete'); 

// TERMS & CONDITION MASTER
define('CUSTOMER_TERMS_CONDITION_SLUG_READ', 'crm-master-terms-list');
define('CUSTOMER_TERMS_CONDITION_SLUG_CREATE', 'crm-master-terms-create');
define('CUSTOMER_TERMS_CONDITION_SLUG_UPDATE', 'crm-master-terms-update');
define('CUSTOMER_TERMS_CONDITION_SLUG_DELETE', 'crm-master-terms-delete');

// ANNEXURE MASTER
define('CUSTOMER_ANNEXURE_SLUG_READ', 'crm-master-annexure-list');
define('CUSTOMER_ANNEXURE_SLUG_CREATE', 'crm-master-annexure-create');
define('CUSTOMER_ANNEXURE_SLUG_UPDATE', 'crm-master-annexure-update');
define('CUSTOMER_ANNEXURE_SLUG_DELETE', 'crm-master-annexure-delete');

// SALES STAGE MASTER 
define('CUSTOMER_SALES_STAGE_SLUG_READ', 'crm-master-opprotunity-list');
define('CUSTOMER_SALES_STAGE_SLUG_CREATE', 'crm-master-opprotunity-create');
define('CUSTOMER_SALES_STAGE_SLUG_UPDATE', 'crm-master-opprotunity-update');
define('CUSTOMER_SALES_STAGE_SLUG_DELETE', 'crm-master-opprotunity-delete');
define('CUSTOMER_SALES_STAGE_SLUG_STATUS', 'crm-master-opprotunity-status');

// SOURCE MASTER 
define('CUSTOMER_SOURCE_SLUG_READ', 'crm-master-source-list');
define('CUSTOMER_SOURCE_SLUG_CREATE', 'crm-master-source-create');
define('CUSTOMER_SOURCE_SLUG_UPDATE', 'crm-master-source-update');
define('CUSTOMER_SOURCE_SLUG_DELETE', 'crm-master-source-delete');

// TERMS & CONDITION CATEGORY TYPE MASTER 
define('CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_READ', 'crm-master-terms-condition-category-type-list');
define('CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_CREATE', 'crm-master-terms-condition-category-type-create');
define('CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_UPDATE', 'crm-master-terms-condition-category-type-update');
define('CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_DELETE', 'crm-master-terms-condition-category-type-delete');

define('CUSTOMER_SALES_ORDER_SUMMARY_REPORT_VIEW', 'customer-sales-order-summary-report-view');
define('SALES_ORDER_DUE_DATE_REPORT_VIEW', 'sales-order-due-date-report-view');

// INDIA MART DATA MASTER 
define('INDIA_MART_DATA_SLUG_READ', 'indiamart-data-list');
define('INDIA_MART_DATA_SLUG_LOAD_INQUIRY', 'indiamart-data-load-inquiry');
define('INDIA_MART_DATA_SLUG_ADD_INQUIRY', 'indiamart-data-add-inquiry');
define('INDIA_MART_DATA_SLUG_EDIT_INQUIRY', 'indiamart-data-edit-inquiry');
define('INDIA_MART_DATA_SLUG_DELETE_INQUIRY', 'indiamart-data-delete-inquiry');

// INDIA MART API KEY MASTER
define('INDIA_MART_API_SLUG_READ', 'indiamart-api-list');
define('INDIA_MART_API_SLUG_ADD', 'indiamart-api-add');
define('INDIA_MART_API_SLUG_EDIT', 'indiamart-api-edit');
define('INDIA_MART_API_SLUG_DELETE', 'indiamart-api-delete');

// USER TYPE
define('USER_TYPE_VIEW', 'usertypeview');
define('USER_TYPE_CREATE', 'usertypecreate1');
define('USER_TYPE_READ', 'usertyperead');
define('USER_TYPE_UPDATE', 'usertypeupdate');
define('USER_TYPE_DELETE', 'usertypedelete');

// FORECAST BY USER PRO MASTER
define('FORECAST_BY_USER_PRO_SLUG_READ', 'forecast-byuser-pro-list');
define('FORECAST_BY_USER_PRO_SLUG_ADD', 'forecast-byuser-pro-add');
define('FORECAST_BY_USER_PRO_SLUG_EDIT', 'forecast-byuser-pro-edit');
define('FORECAST_BY_USER_PRO_SLUG_DELETE', 'forecast-byuser-pro-delete');


//Payment Terms Maulik Kapatel
define('ADMINISTRATOR_PAYMENTTERMS_READ','administrator-payment-terms-list');
define('ADMINISTRATOR_PAYMENTTERMS_ADD','administrator-payment-terms-create');
define('ADMINISTRATOR_PAYMENTTERMS_EDIT','administrator-payment-terms-update');
define('ADMINISTRATOR_PAYMENTTERMS_DELETE','administrator-payment-terms-delete');
define('ADMINISTRATOR_PAYMENTTERM_VIEW','administrator-payment-terms-list');

// LEDGER LIST
define('ADMINISTRATOR_LEDGER_READ', 'administrator-ledger-list');
define('ADMINISTRATOR_LEDGER_ADD', 'administrator-ledger-add');
define('ADMINISTRATOR_LEDGER_EDIT', 'administrator-ledger-edit');
define('ADMINISTRATOR_LEDGER_DELETE', 'administrator-ledger-delete');
define('ADMINISTRATOR_LEDGER_VIEW', 'administrator-ledger-view');
define('ADMINISTRATOR_LEDGER_APPROVE', 'administrator-ledger-approve');
define('ADMINISTRATOR_LEDGER_FINAL_APPROVE', 'administrator-ledger-final-approve');

// GROUP LIST
define('ADMINISTRATOR_GROUP_READ', 'administrator-group-list');
define('ADMINISTRATOR_GROUP_ADD', 'administrator-group-add');
define('ADMINISTRATOR_GROUP_EDIT', 'administrator-group-edit');
define('ADMINISTRATOR_GROUP_DELETE', 'administrator-group-delete');
define('ADMINISTRATOR_GROUP_VIEW', 'administrator-group-view');

// Pending Invoice Approval
define('FINANCE_PENDING_INVOICE_APPROVAL_LIST', 'pending-invoice-approval-list');
define('FINANCE_PENDING_INVOICE_APPROVE', 'pending-invoice-approve');


// Working Dashboard Inquiry Team Pending Task 
define('DASHBOARD_PENDING_TASK_LIST_INQUIRY_ADD', 'dashboard-inquiry-add');
define('DASHBOARD_PENDING_TASK_LIST', 'dashboard-pending-task-list');
define('DASHBOARD_PENDING_TASK_LIST_GENERAL', 'dashboard-pending-task-list-general');
define('DASHBOARD_PENDING_TASK_LIST_QUOTATION', 'dashboard-pending-task-list-quotation');
define('DASHBOARD_PENDING_TASK_LIST_REVISE_QUOTATION', 'dashboard-pending-task-list-revise-quotation');
define('DASHBOARD_PENDING_TASK_LIST_QUOTATION_LIST', 'dashboard-quotation-list');
define('DASHBOARD_PENDING_TASK_LIST_QUOTATION_FOLLOWUP', 'dashboard-pending-task-list-quotation-followup');
define('DASHBOARD_PENDING_TASK_LIST_SALES_ORDER_LIST', 'dashboard-pending-sales-order-list');
define('DASHBOARD_PENDING_TASK_LIST_DISPATCH_LIST', 'dashboard-pending-dispatch-list');
define('DASHBOARD_PENDING_TASK_LIST_APPOINTMENT_LIST', 'dashboard-pending-appointment-list');

// Working Dashboard Inquiry Personal Pending Task 
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_INQUIRY_ADD', 'dashboard-personal-inquiry-add');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE', 'dashboard-personal-pending-task-list-one');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_GENERAL', 'dashboard-personal-pending-task-list-one-general');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION', 'dashboard-personal-pending-task-list-one-quotation');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_REVISE', 'dashboard-personal-pending-task-list-one-revise');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_LIST', 'dashboard-personal-quotation-list');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_ONE_QUOTATION_FOLLOWUP', 'dashboard-personal-pending-task-list-one-quotation-up');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_SALES_ORDER_LIST', 'dashboard-personal-pending-sales-order-list');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_DISPATCH_LIST', 'dashboard-personal-pending-dispatch-list');
define('DASHBOARD_PERSONAL_PENDING_TASK_LIST_APPOINTMENT_LIST', 'dashboard-personal-pending-appointment-list');

// Working Dashboard MRP 
define('DASHBOARD_GET_SALES_ORDER_DETAILS', 'dashboard-get-sales-order-details');
define('DASHBOARD_GET_STOCK_DETAILS', 'dashboard-get-stock-detail');
define('DASHBOARD_GET_STOCK_PENDING_REQUEST', 'dashboard-stock-pending-request');
define('DASHBOARD_GET_REJECT_QC_REQUEST_LIST', 'dashboard-reject-qc-request-list');
define('DASHBOARD_GET_FORECAST_LIST', 'dashboard-forecast');


// Working Dashboard Purchase
define('DASHBOARD_INDENT_LIST', 'dashboard-indent-list');
define('DASHBOARD_PO_QUOTATION_LIST', 'dashboard-po-quotation-list');
define('DASHBOARD_PO_REQUEST_LIST', 'dashboard-po-req-list');
define('DASHBOARD_PO_REQUEST_LIST_APPROVE', 'dashboard-po-req-list-approv');
/* START JAYESH ADD GIR */
define('DASHBOARD_PO_GIR_LIST', 'dashboard-gir-list-view');
/* END JAYESH ADD GIR */

define('DASHBOARD_SERVICE_NOTES_LIST', 'dashboard-service-notes-list');
define('DASHBOARD_OVERDUE_PO_PRO_LIST', 'dashboard-overdue-po-pro-list');
define('DASHBOARD_PURCHASE_BILL_PENDING_LIST', 'dashboard-purchase-bill-pending-list');
define('DASHBOARD_DEBIT_NOTE_PENDING_LIST', 'dashboard-debit-note-pending-list');

// Working Dashboard Production Jobwork
define('DASHBOARD_JOB_CARD_LIST', 'dashboard-job-card-list');
define('DASHBOARD_PENDING_JOB_WORK_LIST', 'dashboard-pending-job-work-list');
define('DASHBOARD_PENDING_JOB_CARD', 'dashboard-pending-job-card');

// Working Dashboard QC Process
define('DASHBOARD_PURCHASE_QC_PENDING_LIST', 'dashboard-purchase-qc-pending-list');
define('DASHBOARD_PARTS_QC_PENDING_LIST', 'dashboard-parts-qc-pending-list');

// Working Dashboard Service Complain Module
define('DASHBOARD_COMPLAIN_TYPE', 'dashboard-comp-type');
define('DASHBOARD_COMPLAIN_TYPE_COMPLIANT_ASSIGNED', 'dashboard-comp-type-compliant-assigned');
define('DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_STARTED', 'dashboard-comp-type-employees-started');
define('DASHBOARD_COMPLAIN_TYPE_EMPLOYEE_NOT_STARTED', 'dashboard-comp-type-employees-not-started');
define('DASHBOARD_COMPLAIN_TYPE_CLOSED', 'dashboard-comp-type-closed');
define('DASHBOARD_COMPLAIN_TYPE_NOT_DONE', 'dashboard-comp-type-not-done');
define('DASHBOARD_COMPLAIN_LIST', 'dashboard-complaint-list');

// Working Dashboard Service Employee Module 
define('DASHBOARD_EMPLOYEE_PRESENT_LIST', 'dashboard-employee-present-list');
define('DASHBOARD_EMPLOYEE_ABSENT_LIST', 'dashboard-employee-absent-list');
define('DASHBOARD_EMPLOYEE_EXPENSE_PENDING_LIST', 'dashboard-employee-expense-list');

// Working Dashboard Service Spare Parts Module
define('DASHBOARD_SPARE_LIST_PENDING', 'dashboard-spare-list-pending');
define('DASHBOARD_RETURN_OLD_SPARE', 'dashboard-return-old-spare');

// Working Dashboard Finance Invoice Module
define('DASHBOARD_CUSTOMER_UNADJUSTED_AMOUNT', 'dashboard-customer-unadjusted-amount');
define('DASHBOARD_PENDING_ORDER_INVOICE', 'dashboard-pending-order-invoice');
define('DASHBOARD_PENDING_SPARE_INVOICE', 'dashboard-pending-spare-invoice');
define('DASHBOARD_PENDING_SERVICE_CHARGE_INVOICE', 'dashboard-pending-service-charge-invoice');
define('DASHBOARD_PENDING_FOC_SPARE_INVOICE', 'dashboard-pending-foc-spare-invoice');
define('DASHBOARD_PENDING_INVOICE_APPROVAL', 'dashboard-pending-invoice-approval');

// Working Dashboard Finance Purchase Module
define('DASHBOARD_VENDOR_UNADJUSTED_AMOUNT', 'dashboard-vendor-unadjusted-amount');

//Administrator Master Permission Slug
define('ADMINISTRATOR_PROCESS_TYPE_LIST', 'administrator-process-type-list');
define('ADMINISTRATOR_PROCESS_TYPE_CREATE', 'administrator-process-type-create');
define('ADMINISTRATOR_PROCESS_TYPE_UPDATE', 'administrator-process-type-update');
define('ADMINISTRATOR_PROCESS_TYPE_DELETE', 'administrator-process-type-delete');


define('ADMINISTRATOR_PRODUCT_TYPE_LIST', 'administrator-product-type-list');
define('ADMINISTRATOR_PRODUCT_TYPE_CREATE', 'administrator-product-type-create');
define('ADMINISTRATOR_PRODUCT_TYPE_UPDATE', 'administrator-product-type-update');
define('ADMINISTRATOR_PRODUCT_TYPE_DELETE', 'administrator-product-type-delete');

define('ADMINISTRATOR_QC_PARAMETER_LIST', 'administrator-qc-parame-list');
define('ADMINISTRATOR_QC_PARAMETER_CREATE', 'administrator-qc-parame-create');
define('ADMINISTRATOR_QC_PARAMETER_UPDATE', 'administrator-qc-parame-update');
define('ADMINISTRATOR_QC_PARAMETER_DELETE', 'administrator-qc-parame-delete');

define('ADMINISTRATOR_PROCESS_LIST', 'administrator-process-list');
define('ADMINISTRATOR_PROCESS_CREATE', 'administrator-process-create');
define('ADMINISTRATOR_PROCESS_UPDATE', 'administrator-process-update');
define('ADMINISTRATOR_PROCESS_DELETE', 'administrator-process-delete');

define('ADMINISTRATOR_CATEGORY_LIST', 'administrator-category-list');
define('ADMINISTRATOR_CATEGORY_CREATE', 'administrator-category-create');
define('ADMINISTRATOR_CATEGORY_UPDATE', 'administrator-category-update');
define('ADMINISTRATOR_CATEGORY_DELETE', 'administrator-category-delete');

define('ADMINISTRATOR_ZONE_LIST', 'administrator-zone-list');
define('ADMINISTRATOR_ZONE_CREATE', 'administrator-zone-create');
define('ADMINISTRATOR_ZONE_UPDATE', 'administrator-zone-update');
define('ADMINISTRATOR_ZONE_DELETE', 'administrator-zone-delete');

define('ADMINISTRATOR_PRODUCT_LIST', 'administrator-product-list');
define('ADMINISTRATOR_PRODUCT_CREATE', 'administrator-product-create');
define('ADMINISTRATOR_PRODUCT_UPDATE', 'administrator-product-update');
define('ADMINISTRATOR_PRODUCT_DELETE', 'administrator-product-delete');
define('ADMINISTRATOR_PRODUCT_EXCEL', 'administrator-product-excel');
define('ADMINISTRATOR_PRODUCT_COPY', 'administrator-product-copy');
/* START JAYESH */
define('ADMINISTRATOR_PRODUCT_CLONE', 'administrator-product-clone');
/* END JAYESH */

define('ADMINISTRATOR_BRANCH_MST_LIST', 'administrator-branch-mst-list');
define('ADMINISTRATOR_BRANCH_MST_CREATE', 'administrator-branch-mst-create');
define('ADMINISTRATOR_BRANCH_MST_UPDATE', 'administrator-branch-mst-update');
define('ADMINISTRATOR_BRANCH_MST_DELETE', 'administrator-branch-mst-delete');

define('ADMINISTRATOR_BANK_MST_LIST', 'administrator-bank-mst-list');
define('ADMINISTRATOR_BANK_MST_CREATE', 'administrator-bank-mst-create');
define('ADMINISTRATOR_BANK_MST_UPDATE', 'administrator-bank-mst-update');
define('ADMINISTRATOR_BANK_MST_DELETE', 'administrator-bank-mst-delete');

define('ADMINISTRATOR_MSPEC_LIST', 'administrator-mspec-list');
define('ADMINISTRATOR_MSPEC_CREATE', 'administrator-mspec-create');
define('ADMINISTRATOR_MSPEC_UPDATE', 'administrator-mspec-update');
define('ADMINISTRATOR_MSPEC_DELETE', 'administrator-mspec-delete');

define('ADMINISTRATOR_COMPLAINT_TYPE_LIST', 'administrator-complaint-type-list');
define('ADMINISTRATOR_COMPLAINT_TYPE_CREATE', 'administrator-complaint-type-create');
define('ADMINISTRATOR_COMPLAINT_TYPE_UPDATE', 'administrator-complaint-type-update');
define('ADMINISTRATOR_COMPLAINT_TYPE_DELETE', 'administrator-complaint-type-delete');

define('ADMINISTRATOR_FORMULA_LIST', 'administrator-formula-list');
define('ADMINISTRATOR_FORMULA_CREATE', 'administrator-formula-create');
define('ADMINISTRATOR_FORMULA_UPDATE', 'administrator-formula-update');
define('ADMINISTRATOR_FORMULA_DELETE', 'administrator-formula-delete');

define('ADMINISTRATOR_TAX_LIST', 'administrator-tax-list');
define('ADMINISTRATOR_TAX_CREATE', 'administrator-tax-create');
define('ADMINISTRATOR_TAX_UPDATE', 'administrator-tax-update');
define('ADMINISTRATOR_TAX_DELETE', 'administrator-tax-delete');

define('ADMINISTRATOR_UNIT_LIST', 'administrator-unit-list');
define('ADMINISTRATOR_UNIT_CREATE', 'administrator-unit-create');
define('ADMINISTRATOR_UNIT_UPDATE', 'administrator-unit-update');
define('ADMINISTRATOR_UNIT_DELETE', 'administrator-unit-delete');

define('ADMINISTRATOR_COUNTRY_LIST', 'administrator-country-list');
define('ADMINISTRATOR_COUNTRY_CREATE', 'administrator-country-create');
define('ADMINISTRATOR_COUNTRY_UPDATE', 'administrator-country-update');
define('ADMINISTRATOR_COUNTRY_DELETE', 'administrator-country-delete');

define('ADMINISTRATOR_STATE_LIST', 'administrator-state-list');
define('ADMINISTRATOR_STATE_CREATE', 'administrator-state-create');
define('ADMINISTRATOR_STATE_UPDATE', 'administrator-state-update');
define('ADMINISTRATOR_STATE_DELETE', 'administrator-state-delete');

define('ADMINISTRATOR_CITY_LIST', 'administrator-city-list');
define('ADMINISTRATOR_CITY_CREATE', 'administrator-city-create');
define('ADMINISTRATOR_CITY_UPDATE', 'administrator-city-update');
define('ADMINISTRATOR_CITY_DELETE', 'administrator-city-delete');

define('ADMINISTRATOR_GODOWN_LIST', 'administrator-godown-list');
define('ADMINISTRATOR_GODOWN_CREATE', 'administrator-godown-create');
define('ADMINISTRATOR_GODOWN_UPDATE', 'administrator-godown-update');
define('ADMINISTRATOR_GODOWN_DELETE', 'administrator-godown-delete');

define('ADMINISTRATOR_CURRENCY_LIST', 'administrator-currency-list');
define('ADMINISTRATOR_CURRENCY_CREATE', 'administrator-currency-create');
define('ADMINISTRATOR_CURRENCY_UPDATE', 'administrator-currency-update');
define('ADMINISTRATOR_CURRENCY_DELETE', 'administrator-currency-delete');

define('ADMINISTRATOR_SERIES_TYPE_LIST', 'administrator-series-type-list');
define('ADMINISTRATOR_SERIES_TYPE_CREATE', 'administrator-series-type-create');
define('ADMINISTRATOR_SERIES_TYPE_UPDATE', 'administrator-series-type-update');
define('ADMINISTRATOR_SERIES_TYPE_DELETE', 'administrator-series-type-delete');

define('ADMINISTRATOR_DRAWING_LIST', 'administrator-drawing-list');
define('ADMINISTRATOR_DRAWING_CREATE', 'administrator-drawing-create');
define('ADMINISTRATOR_DRAWING_UPDATE', 'administrator-drawing-update');
define('ADMINISTRATOR_DRAWING_DELETE', 'administrator-drawing-delete');

define('ADMINISTRATOR_MAKE_LIST', 'administrator-make-list');
define('ADMINISTRATOR_MAKE_CREATE', 'administrator-make-create');
define('ADMINISTRATOR_MAKE_UPDATE', 'administrator-make-update');
define('ADMINISTRATOR_MAKE_DELETE', 'administrator-make-delete');
define('ADMINISTRATOR_MAKE_EXCEL', 'administrator-make-excel');

define('ADMINISTRATOR_MAKE_NUMBER_LIST', 'administrator-make-number-list');
define('ADMINISTRATOR_MAKE_NUMBER_CREATE', 'administrator-make-number-create');
define('ADMINISTRATOR_MAKE_NUMBER_UPDATE', 'administrator-make-number-update');
define('ADMINISTRATOR_MAKE_NUMBER_DELETE', 'administrator-make-number-delete');
define('ADMINISTRATOR_MAKE_NUMBER_EXCEL', 'administrator-make-number-excel');

define('ADMINISTRATOR_TRANSPORATATION_LIST', 'administrator-transportation-list');
define('ADMINISTRATOR_TRANSPORATATION_CREATE', 'administrator-transportation-create');
define('ADMINISTRATOR_TRANSPORATATION_UPDATE', 'administrator-transportation-update');
define('ADMINISTRATOR_TRANSPORATATION_DELETE', 'administrator-transportation-delete');

//Jayesh Start 26-07-2021
define('ADMINISTRATOR_DOCUMENT_TYPE_MST_LIST', 'administrator-document-type-mst-list');
define('ADMINISTRATOR_DOCUMENT_TYPE_MST_CREATE', 'administrator-document-type-mst-create');
define('ADMINISTRATOR_DOCUMENT_TYPE_MST_UPDATE', 'administrator-document-type-mst-update');
define('ADMINISTRATOR_DOCUMENT_TYPE_MST_DELETE', 'administrator-document-type-mst-delete');

//Amish Soni Start 01-02-2021
define('GENERAL_TASK_TYPE', 14);

// GENERAL TASK SLUGS
define('GENERAL_TASK_SLUG_READ', 'general-task-list');
define('GENERAL_TASK_SLUG_CREATE', 'general-task-add');
define('GENERAL_TASK_SLUG_EDIT', 'general-task-edit');
//Amish Soni End 01-02-2021
define('CUSTOMER_GENERAL_TASK_SLUG_READ', 'customer-general-task-list');
define('CUSTOMER_GENERAL_TASK_SLUG_CREATE', 'customer-general-task-create');
define('CUSTOMER_GENERAL_TASK_SLUG_UPDATE', 'customer-general-task-update');
define('CUSTOMER_GENERAL_TASK_SLUG_DELETE', 'customer-general-task-delete');

// DATABASE EXPIRE CHECK TABLE
define('TABLE_PRODUCT_PARAMETER', 'tbl_product_parameter');
define('TABLE_PRODUCT_MASTER', 'process_mst');
define('TABLE_PURCHASE_ORDER_NUMBER', 'tbl_pono');
define('TABLE_INVOICE', 'tbl_invoice');
define('TABLE_GENERAL_BOOK', 'tbl_general_book');
define('TABLE_JOURNAL_TRN', 'tbl_journal_trn');
define('TABLE_RECEIPT', 'tbl_receipt');
define('TABLE_CONTRA_TRN', 'tbl_contra_trn');
define('TABLE_EXCESS', 'tbl_excess');
define('TABLE_COMPLAINT_TRN', 'tbl_complaint_trn');
define('TABLE_BOM_TRN', 'tbl_bomtrn');
define('TABLE_INQUIRY_TRN', 'tbl_inquiry_trn');
define('TABLE_LEDGER', 'tbl_ledger');

//Umair Start 30-03-2021
// PURCHASE ORDER PENDING APPROVAL
define('PURCHASE_ORDER_PENDING_APPROVAL_VIEW', 'po-req-list-appro-view');
define('PURCHASE_ORDER_PENDING_APPROVAL_READ', 'po-req-list-appro-read');
define('PURCHASE_ORDER_PENDING_APPROVAL_APPROVE', 'po-req-list-appro-approve');

define('PURCHASE_ORDER_FINANCE_APPROVAL', 'dashboard-po-aprooval-finace');
define('PURCHASE_ORDER_APPROVAL', 'dashboard-po-aprooval');

// MATERIAL PARAMETER
define('ADMINISTRATOR_MATERIAL_PARAMETER_LIST', 'administrator-material-parameter-create');
define('ADMINISTRATOR_MATERIAL_PARAMETER_CREATE', 'administrator-material-parameter-list');
define('ADMINISTRATOR_MATERIAL_PARAMETER_UPDATE', 'administrator-material-parameter-update');
define('ADMINISTRATOR_MATERIAL_PARAMETER_DELETE', 'administrator-material-parameter-delete');
define('ADMINISTRATOR_MATERIAL_PARAMETER_EXCEL', 'administrator-material-parameter-excel');


//csv upload
define('PRODUCT_OPENING_STOCK_CSV_UPLOAD', 'product-opening-stock-csv-upload');

//Common Category mster
define('CREATE_COMMON_CATEGORY_MASTER', 'create-common-category-master');
define('READ_COMMON_CATEGORY_MASTER', 'read-common-category-master');
define('UPDATE_COMMON_CATEGORY_MASTER', 'update-common-category-master');
define('DELETE_COMMON_CATEGORY_MASTER', 'delete-common-category-master');

//Common mster
define('CREATE_COMMON_MASTER', 'create-common-master');
define('READ_COMMON_MASTER', 'read-common-master');
define('UPDATE_COMMON_MASTER', 'update-common-master');
define('DELETE_COMMON_MASTER', 'delete-common-master');

//Cost Center Group mster
define('CREATE_COST_CENTER_GROUP_MASTER', 'create-cost-center-group-master');
define('READ_COST_CENTER_GROUP_MASTER', 'read-cost-center-group-master');
define('UPDATE_COST_CENTER_GROUP_MASTER', 'update-cost-center-group-master');
define('DELETE_COST_CENTER_GROUP_MASTER', 'delete-cost-center-group-master');

//Cost Center mster
define('CREATE_COST_CENTER_MASTER', 'create-cost-center-master');
define('READ_COST_CENTER_MASTER', 'read-cost-center-master');
define('UPDATE_COST_CENTER_MASTER', 'update-cost-center-master');
define('DELETE_COST_CENTER_MASTER', 'delete-cost-center-master');

//Narration mster
define('CREATE_NARRATION_MASTER', 'create-narration-master');
define('READ_NARRATION_MASTER', 'read-narration-master');
define('UPDATE_NARRATION_MASTER', 'update-narration-master');
define('DELETE_NARRATION_MASTER', 'delete-narration-master');

//Salesman mster
define('CREATE_SALESMAN_MASTER', 'create-salesman-master');
define('READ_SALESMAN_MASTER', 'read-salesman-master');
define('UPDATE_SALESMAN_MASTER', 'update-salesman-master');
define('DELETE_SALESMAN_MASTER', 'delete-salesman-master');

//Tds Tax Category mster
define('CREATE_TDS_TAX_CATEGORY_MASTER', 'create-tds-tax-category-master');
define('READ_TDS_TAX_CATEGORY_MASTER', 'read-tds-tax-category-master');
define('UPDATE_TDS_TAX_CATEGORY_MASTER', 'update-tds-tax-category-master');
define('DELETE_TDS_TAX_CATEGORY_MASTER', 'delete-tds-tax-category-master');

//Company Configuration
define('ADMINISTRATOR_COMPANY_CONFIGURATION_ADD', 'administrator-company-configuration-add');
define('ADMINISTRATOR_COMPANY_CONFIGURATION_READ', 'administrator-company-configuration-list');
define('ADMINISTRATOR_COMPANY_CONFIGURATION_EDIT', 'administrator-company-configuration-edit');
define('ADMINISTRATOR_COMPANY_CONFIGURATION_DELETE', 'administrator-company-configuration-delete');
//ORDER ACCEPTANCE SLUG
define('ORDER_ACCEPTANCE_SLUG_LIST', 'order-acceptance-list');
define('ORDER_ACCEPTANCE_SLUG_READ', 'order-acceptance-read');
define('ORDER_ACCEPTANCE_SLUG_CREATE', 'order-acceptance-create');
define('ORDER_ACCEPTANCE_SLUG_EDIT', 'order-acceptance-edit');
define('ORDER_ACCEPTANCE_SLUG_DELETE', 'order-acceptance-delete');
define('ORDER_ACCEPTANCE_SLUG_APPROVE', 'order-acceptance-approve');
define('ORDER_ACCEPTANCE_SLUG_PRINT', 'order-acceptance-print');
define('ORDER_ACCEPTANCE_SLUG_FINAL_APPROVE', 'order-acceptance-final-approve');


define('INVENTORY_RETURNABLE_CHANNAL_SLUG_READ', 'inventory-returnable-channal-list');
define('INVENTORY_RETURNABLE_CHANNAL_SLUG_CREATE', 'inventory-returnable-channal-add');
define('INVENTORY_RETURNABLE_CHANNAL_SLUG_UPDATE', 'inventory-returnable-channal-update');
define('INVENTORY_RETURNABLE_CHANNAL_SLUG_DELETE', 'inventory-returnable-channal-delete');
define('INVENTORY_RETURNABLE_CHANNAL_SLUG_APPROVE', 'inventory-returnable-channal-approve');
define('INVENTORY_RETURNABLE_CHANNAL_SLUG_PRINT', 'inventory-returnable-channal-print');

// New Currency
define('ADMINISTRATOR_NEW_CURRENCY_VIEW', 'administrator-new-currency-view');
define('ADMINISTRATOR_NEW_CURRENCY_CREATE', 'administrator-new-currency-create');
define('ADMINISTRATOR_NEW_CURRENCY_READ', 'administrator-new-currency-read');
define('ADMINISTRATOR_NEW_CURRENCY_UPDATE', 'administrator-new-currency-update');
define('ADMINISTRATOR_NEW_CURRENCY_DELETE', 'administrator-new-currency-delete');

//CRM PROJECT ASSIGN
define('CRM_PROJECT_ASSIGN_SLUG_VIEW', 'crm-project-assign-view');
define('CRM_PROJECT_ASSIGN_SLUG_CREATE', 'crm-project-assign-create');
define('CRM_PROJECT_ASSIGN_SLUG_LIST', 'crm-project-assign-list');
define('CRM_PROJECT_ASSIGN_SLUG_UPDATE', 'crm-project-assign-update');
define('CRM_PROJECT_ASSIGN_SLUG_DELETE', 'crm-project-assign-delete');

/*START JAYESH DESOGN DEPARTMENT*/
// DESIGN DEPARTMENT SALES ORDER PLANNING
define('DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_VIEW', 'design-department-get-sales-order-views');
define('DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_CREATE', 'design-department-get-sales-order-details-create');
define('DESIGN_DEPARTMENT_GET_SALES_ORDER_SLUG_READ', 'design-department-get-sales-order-details');
/*END JAYESH DESOGN DEPARTMENT*/

// START JAEYSH Working Dashboard DESIGN DEPARTMENT 
define('DASHBOARD_DESIGN_DEPARTMENT_GET_SALES_ORDER_DETAILS', 'dashboard-design-department-get-sales-order-details');


/*START SANAT*/
define('CRON_EMAIL_SLUG_CREATE', 'cron-email-add');
define('CRON_EMAIL_SLUG_EDIT', 'cron-email-edit');
define('CRON_EMAIL_SLUG_DELETE', 'cron-email-delete');
define('CRON_EMAIL_SLUG_LIST', 'cron-email-list');
/*END SANAT*/
//CRM QUOTATION PRINT BLOCK FORMATE
define('QUOTATION_PRINT_BLOCK_MST_VIEW', 'quotation-print-block-mst-view');
define('QUOTATION_PRINT_BLOCK_MST_CREATE', 'quotation-print-block-mst-create');
define('QUOTATION_PRINT_BLOCK_MST_LIST', 'quotation-print-block-mst-list');
define('QUOTATION_PRINT_BLOCK_MST_UPDATE', 'quotation-print-block-mst-update');
define('QUOTATION_PRINT_BLOCK_MST_DELETE', 'quotation-print-block-mst-delete');

//CRM QUOTATION PRINT BLOCK FORMATE SETUP
define('QUOTATION_PRINT_BLOCK_SETUP_VIEW', 'quotation-print-block-setup-view');
define('QUOTATION_PRINT_BLOCK_SETUP_CREATE', 'quotation-print-block-setup-create');
define('QUOTATION_PRINT_BLOCK_SETUP_LIST', 'quotation-print-block-setup-list');
define('QUOTATION_PRINT_BLOCK_SETUP_UPDATE', 'quotation-print-block-setup-update');
define('QUOTATION_PRINT_BLOCK_SETUP_DELETE', 'quotation-print-block-setup-delete');


//hsn master
define('ADMINISTRATOR_HSN_MASTER_ADD', 'administrator-hsn-master-add');
define('ADMINISTRATOR_HSN_MASTER_READ', 'administrator-hsn-master-list');
define('ADMINISTRATOR_HSN_MASTER_EDIT', 'administrator-hsn-master-edit');
define('ADMINISTRATOR_HSN_MASTER_DELETE', 'administrator-hsn-master-delete');



// Finance - Sale Return * Dhaval Upadhyay * 

define('FINANCE_SALE_RETURN', 'sale-return-list');
define('FINANCE_SALE_RETURN_CREATE', 'sale-return-create');
define('FINANCE_SALE_RETURN_UPDATE', 'sale-return-update');

// Finance - Purchase Return * Dhaval Upadhyay * 

define('FINANCE_PURCHASE_RETURN', 'purchase-return-list');
define('FINANCE_PURCHASE_RETURN_CREATE', 'purchase-return-create');
define('FINANCE_PURCHASE_RETURN_UPDATE', 'purchase-return-update');

 /*  Voucher Type - Dhaval Upadhyay
 */
define('SALES_VOUCHER', 1);
define('PURCHASE_VOUCHER', 2);
define('PO_VOUCHER', 124);
define('SO_VOUCHER', 125);
define('PURCHASE_RETURN_VOUCHER', 3);
define('SALES_RETURN_VOUCHER', 29);
define('PAYMENT_VOUCHER', 82);
define('RECEIPT_VOUCHER', 83);
define('JV_VOUCHER', 84);
define('QUOTATION_VOUCHER', 126);

 /*  Tax category type - Dhaval Upadhyay
 */
 
 define('GST_NILL_RATED', 22);
 define('GST_EXEMPTED', 24);
 define('GST_ZERO_RATED',25);
 define('NON_GST',23);

// G.I.R. LIST  START JAYESH
define('PRODUCTION_GIR_LIST_SLUG_VIEW', 'production-gir-list-create');
define('PRODUCTION_GIR_LIST_SLUG_CREATE', 'production-gir-list-create');
define('PRODUCTION_GIR_LIST_SLUG_READ', 'production-gir-list-read');
define('PRODUCTION_GIR_LIST_SLUG_UPDATE', 'production-gir-list-update');
define('PRODUCTION_GIR_LIST_SLUG_DELETE', 'production-gir-list-delete');


/*START SANAT*/
// OPENING STOCK SLUG
define('OPENING_STOCK_LIST_SLUG_VIEW', 'opening-stock-list-view');
define('OPENING_STOCK_LIST_SLUG_CREATE', 'opening-stock-list-create');
define('OPENING_STOCK_LIST_SLUG_READ', 'opening-stock-list-read');
define('OPENING_STOCK_LIST_SLUG_UPDATE', 'opening-stock-list-update');
define('OPENING_STOCK_LIST_SLUG_DELETE', 'opening-stock-list-delete');
define('OPENING_STOCK_LIST_APPROVE', 'opening-stock-list-approve');
/*END SANAT*/

// PRINT SETUP SLUG
define('PRINT_SETUP_SLUG_VIEW', 'print-setup-list-view');
define('PRINT_SETUP_SLUG_CREATE', 'print-setup-list-create');
define('PRINT_SETUP_SLUG_READ', 'print-setup-list-read');
define('PRINT_SETUP_SLUG_UPDATE', 'print-setup-list-update');
define('PRINT_SETUP_SLUG_DELETE', 'print-setup-list-delete');
define('PRINT_SETUP_SLUG_APPROVE', 'print-setup-list-approve');

// TRADEINDIA API KEY MASTER
define('TRADEINDIA_API_SLUG_READ', 'tradeindia-api-list');
define('TRADEINDIA_API_SLUG_ADD', 'tradeindia-api-add');
define('TRADEINDIA_API_SLUG_EDIT', 'tradeindia-api-edit');
define('TRADEINDIA_API_SLUG_DELETE', 'tradeindia-api-delete');


// GRN INVENTORY
define('INVENTORY_GRN_LIST_SLUG_VIEW', 'inventory-grn-list-view');
define('INVENTORY_GRN_LIST_SLUG_CREATE', 'inventory-grn-list-create');
define('INVENTORY_GRN_LIST_SLUG_READ', 'inventory-grn-list-read');
define('INVENTORY_GRN_LIST_SLUG_UPDATE', 'inventory-grn-list-update');
define('INVENTORY_GRN_LIST_SLUG_DELETE', 'inventory-grn-list-delete');

/*START SANAT*/
// OPENING STOCK SLUG
define('PRODUCTION_STORE_LIST_SLUG_VIEW', 'production-store-list-view');
define('PRODUCTION_STORE_LIST_SLUG_CREATE', 'production-store-list-create');
define('PRODUCTION_STORE_LIST_SLUG_READ', 'production-store-list-read');
define('PRODUCTION_STORE_LIST_SLUG_UPDATE', 'production-store-list-update');
define('PRODUCTION_STORE_LIST_SLUG_DELETE', 'production-store-list-delete');
define('PRODUCTION_STORE_LIST_APPROVE', 'production-store-list-approve');
define('PRODUCTION_STORE_LIST_RETURN', 'production-store-list-return');
/*END SANAT*/


/*payment mode - dhaval */


define('PAY_MODE_CASH', 10);
define('PAY_MODE_CHEQUE', 11);
define('PAY_MODE_RTGS', 12);
define('PAY_MODE_UPI', 13);
define('PAY_MODE_IMPS', 14);

// Delete time Used mst - Maulik

define('MST_COMPLAINT_TYPE_LIST',1);
define('MST_CATEGORY_LIST',2);
define('MST_DRAWING',3);
define('MST_GODOWN_LIST',4);
define('MST_ITEM_LIST',5);
define('MST_MAKE',6);
define('MST_MAKE_NUMBER',7);
define('MST_MATERIAL_PARAMETER',8);
define('MST_MATERIAL_SPECIFICATION',9);
define('MST_PROCECSS_LIST',10);
define('MST_PROCESS_TYPE_LIST',11);
define('MST_PRODUCT_TYPE_LIST',12);
define('MST_QC_PARAMETER_LIST',13);
define('MST_BANK_LIST',14);
define('MST_COMMON_CATEGORY',15);
define('MST_COMMON_MASTER',16);
define('MST_COST_CENTER',17);
define('MST_COST_CENTER_GROUP',18);
define('MST_CURRENCY_LIST',19);
define('MST_GROUP_LIST',20);
define('MST_HSN_MASTER',21);
define('MST_LEDGER_LIST',22);
define('MST_TAX_CATEGORY',23);
define('MST_TDS_TAX_CATEGORY',24);
define('MST_TRANSPORTATION_DETAIL',25);
define('MST_BRANCH_LIST',27);
define('MST_CITY_LIST',28);
define('MST_COMPANY_CONFIGURATION',29);
define('MST_COUNTRY_LIST',30);
define('MST_NARRATION_MASTER',31);
define('MST_SERIES_TYPE',32);
define('MST_STATE_LIST',33);
define('MST_UNIT_LIST',34);
define('MST_ZONE_LIST',35);
define('MST_PRO_MS_BOM_VERSION',36);
define('MST_TBL_SALES_ORDERTRN',37);
define('MST_TBL_BOM',38);

// Product Type Master 

define('SERVICE',8);

?>