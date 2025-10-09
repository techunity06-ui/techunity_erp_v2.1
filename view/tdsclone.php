<?php
session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");

echo $sql="select tbst.*,tpo.* from tbl_bill_sundry_transaction tbst left join tbl_pono as tpo on tbst.sundry_voucher_id = tpo.po_id where tbst.sundry_ledger_id=24453 and tpo.status=0";
$result=$dbcon->query($sql);
while($rel=brp_mysqli_fetch_assoc($result)){


  $instds = $dbcon->query("INSERT INTO `tbl_general_book` (`ledger_id`,`ledger_id_ref`,`table_name`,`ref_date`,`table_id`,`entry_type`,`amount`,`user_id`,`company_id`,`cdate`) VALUES ('".$rel['vender_id']."',24453,'tbl_pono','".$rel['po_date']."','".$rel['sundry_voucher_id']."',2,'".abs($rel['sundry_amount'])."','".$_SESSION['user_id']."','".$_SESSION['company_id']."','".date("Y-m-d h:i:s")."')");	
  

	
	
}

if($instds)
{
	echo "Add Complete";
}