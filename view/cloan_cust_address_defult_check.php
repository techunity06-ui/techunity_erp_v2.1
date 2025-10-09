<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	//$company_config = getCompanyConfiguration($dbcon);
	

	/* $qry1="select cust_id,cust_name from tbl_customer as grn
	 		where grn.cust_status=0";
	$result1=$dbcon->query($qry1);
	while($rel=brp_mysqli_fetch_assoc($result1)){
		
		$qry="select c_add_id from tbl_cust_address as grn
	 		where c_addr_defult=1 and c_add_status=0 and grn.cust_id=".$rel['cust_id'];
		$result=$dbcon->query($qry);
		$cnt = mysqli_num_rows($result);
		$updatesalesid="";
		//$res=brp_mysqli_fetch_assoc($result);
		if($cnt>0){
			echo "<span style='color:blue'>allready done -".$rel['cust_id']."-".$rel['cust_name']."</span></br>";
		}else{
			$qry2="select c_add_id from tbl_cust_address as grn
	 		where c_add_status=0 and grn.cust_id=".$rel['cust_id']." order by c_add_id limit 1";
			$result2=$dbcon->query($qry2);
			$cnt1 = mysqli_num_rows($result2);
			if($cnt1>0){
				$res=brp_mysqli_fetch_assoc($result2);
				$info1['c_addr_defult'] = 1;
				$updatesalesid=update_record("tbl_cust_address", $info1,"c_add_id=".$res['c_add_id'], $dbcon);
				echo "<span style='color:green'>done -".$rel['cust_id']."-".$rel['cust_name']."--".$updatesalesid."</span></br>";
			}else{
				echo "<span style='color:red'>Address Not Add -".$rel['cust_id']."-".$rel['cust_name']."</span></br>";
			}
		}
	}*/


	

 $qry1="SELECT cust.cust_name,sta.city_name,cadd.c_add_id,sta.stateid,cs.state_name FROM `tbl_cust_address` as cadd
left join tbl_customer as cust on cust.cust_id=cadd.cust_id
left join city_mst as sta on sta.cityid=cadd.c_add_city
left join state_mst as cs on cs.stateid=sta.stateid
 WHERE `c_add_state` = 0 and cadd.c_add_city!=0 and c_addr_defult=1 and c_add_status=0";
	$result1=$dbcon->query($qry1);
	while($rel=brp_mysqli_fetch_assoc($result1)){
		$info1['c_add_state'] = $rel['stateid'];
		$updatesalesid=update_record("tbl_cust_address", $info1,"c_add_id=".$rel['c_add_id'], $dbcon);
		echo $rel['cust_name']." -- ".$rel['state_name'];
	}



?>
