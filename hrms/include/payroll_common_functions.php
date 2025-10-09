<?php
/**
p() - echo exist function
**/

//ankit function start
function p($val, $isexit = true) {
    echo '<pre>';
    print_r($val);
    echo '</pre>';
    if($isexit) {
        die();
    }
}
function updateSeries($dbcon, $field, $table, $invoice_type)
{
	// Series Number Update Code
	$qry = "SELECT $field FROM $table";
	$query = $dbcon->query($qry);
	$total_records = $query->num_rows;
	$updateInfo['taxinvoice_start'] = $total_records;
	$updateinvoiceid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = '$invoice_type'" , $dbcon);
}

?>