<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

$set_head = get_company_data($dbcon,$_SESSION['company_id']);
$currency_id = $set_head['currency_id'];

$dbcon->query("UPDATE tbl_inquiry SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_quotation SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_sales_order SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_sales_ordertrn SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_purchaseorder SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_proforma_invoice SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_purchaseordertrn SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_potrancation SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_pono SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_purchasecard SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_invoice SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_invoicetrn SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_ledger_currency_opening SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_receipt_payment_trn SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_receipt SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_journal SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_journal_trn SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_sale_return_transaction SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");
$dbcon->query("UPDATE tbl_sale_return SET currency_id = '$currency_id' WHERE (currency_id = 0 OR currency_id = 1)");

$dbcon->query("UPDATE tbl_inquiry SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_quotation SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_sales_order SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_sales_ordertrn SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_purchaseorder SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_proforma_invoice SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_purchaseordertrn SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_potrancation SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_pono SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_purchasecard SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_invoice SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_invoicetrn SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_ledger_currency_opening SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_receipt_payment_trn SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_receipt SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_journal SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_journal_trn SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_sale_return_transaction SET currency_id = '144' WHERE currency_id = 2");
$dbcon->query("UPDATE tbl_sale_return SET currency_id = '144' WHERE currency_id = 2");

$dbcon->query("UPDATE tbl_inquiry SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_quotation SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_sales_order SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_sales_ordertrn SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_purchaseorder SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_proforma_invoice SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_purchaseordertrn SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_potrancation SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_pono SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_purchasecard SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_invoice SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_invoicetrn SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_ledger_currency_opening SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_receipt_payment_trn SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_receipt SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_journal SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_journal_trn SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_sale_return_transaction SET currency_id = '47' WHERE currency_id = 3");
$dbcon->query("UPDATE tbl_sale_return SET currency_id = '47' WHERE currency_id = 3");

$dbcon->query("UPDATE tbl_inquiry SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_quotation SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_sales_order SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_sales_ordertrn SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_purchaseorder SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_proforma_invoice SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_purchaseordertrn SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_potrancation SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_pono SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_purchasecard SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_invoice SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_invoicetrn SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_ledger_currency_opening SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_receipt_payment_trn SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_receipt SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_journal SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_journal_trn SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_sale_return_transaction SET currency_id = '52' WHERE currency_id = 4");
$dbcon->query("UPDATE tbl_sale_return SET currency_id = '52' WHERE currency_id = 4");


echo "<h3>Currency Updated</h3>";
?>