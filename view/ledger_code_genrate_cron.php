<?php 
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../include/function_database_query.php");

$query = "Select * from tbl_group where g_status=0";
$rs_type = $dbcon->query($query);
while($row = brp_mysqli_fetch_array($rs_type)){
	$que = "select * from tbl_ledger where l_status !=2 and l_group=".$row['g_id'];
	$rs = $dbcon->query($que);
	$i=1;
	while($r1 = brp_mysqli_fetch_array($rs)){
		if($r1['l_group']=='16'){

		}else if($r1['l_group']=='19'){

		}else if($r1['l_group']=='24'){

		}else if($r1['l_group']=='25'){

		}else if($r1['l_group']=='27'){

		}else if($r1['l_group']=='28'){

		}else if($r1['l_group']=='31'){

		}else if($r1['l_group']=='35'){

		}else if($r1['l_group']=='37'){

		}else if($r1['l_group']=='38'){

		}else if($r1['l_group']=='39'){

		}else if($r1['l_group']=='58'){
		
		}else if($r1['l_group']=='119'){
		
		}else if($r1['l_group']=='120'){

		}else if($r1['l_group']=='121'){
		
		}else if($r1['l_group']=='1000'){

		}
		$info['']

		$i++;
	}
}
?>