<?
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");

$query = "select sum(cgst_tax_rate) as cgst, sum(sgst_tax_rate) as sgst,sum(igst_tax_rate) as igst, inv.invoice_id, inv.invoice_no from tbl_invoice as inv 
left join tbl_invoicetrn as trn on trn.invoice_id = inv.invoice_id
where trn.trancation_status=0 and cgst='0.00' and sgst='0.00' and igst='0.00' group by invoice_id";

$result = $dbcon->query($query);

while($row = brp_mysqli_fetch_array($result)){
	$info['cgst']		= $row['cgst'];	
	$info['sgst']		= $row['sgst'];
	$info['igst']		= $row['igst'];
	$info['cgst_conv']	= $row['cgst'];
	$info['sgst_conv']	= $row['sgst'];
	$info['igst_conv']	= $row['igst'];

	$updateid11=update_record('tbl_invoice', $info, "invoice_id=".$row['invoice_id'] , $dbcon);
}

if($updateid11){
	echo "Clone Updated Successfully";
}
?>