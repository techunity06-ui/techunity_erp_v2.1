<?php 
session_start();
ini_set('max_execution_time', 3000000);
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
	include("../include/function_database_query.php");
	
	
		$q="select * from cust_temp as gd 
			where address_id=0";
		$rel=$dbcon->query($q);
		while($row=mysqli_fetch_array($rel)){
			
				if(!empty($row['cid'])){
					$info_dil1['cust_id']			= $row['cid'];
					$info_dil1['c_add_address']		= $row['address'];
					$info_dil1['c_add_country']		= $row['conid'];
					$info_dil1['c_add_state']		= $row['staid'];
					$info_dil1['c_add_city']		= $row['city_id'];
					$info_dil1['c_add_zip']			= $row['pincode'];
					$info_dil1['c_addr_defult']		= 1;
					
					$inserid_ks=add_record("tbl_cust_address",$info_dil1,$dbcon);

					$info['address_id']			= $inserid_ks;
					$inserid=update_record('cust_temp',$info,"stateid=".$row['stateid'],$dbcon);
					echo $row['cid'];
					echo "</br>";
				}
				

					
		
		}
	
?>