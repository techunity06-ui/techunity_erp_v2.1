<?
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");
$godown_id = 23;
$query = "SELECT rp_id FROM tbl_request_product WHERE status = 2";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){
	$rp_id = $row['rp_id'];
	 $q1 = "SELECT p_id,p_status FROM tbl_allocate_process WHERE p_status != 2 AND p_ref_id = " . $rp_id;
	$rs =  $dbcon->query($q1);
	if(brp_mysqli_num_rows($rs) > 0){
		while($r1 = brp_mysqli_fetch_array($rs)){
			echo " rp_id :  " . $rp_id .  " p_id :  " . $r1['p_id'] . " Pre Status : " . $r1['p_status'] . "</br>";
			$dbcon->query("update tbl_allocate_process set p_status = 2 where p_id = " . $r1['p_id']);
		}	
	}
	
} 


?>
