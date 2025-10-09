<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	 $qry1="SELECT st.*,p.product_base_qty,p.product_conv_qty FROM tbl_stock_trn as st 
	 left join product_mst as p ON p.product_id = st.product_id
	 WHERE st.stock_status != 2 AND st.base_unit != st.convert_unit";
	$result1=$dbcon->query($qry1);
	$i=1;
    if(brp_mysqli_num_rows($result1) > 0){
        while($rel=brp_mysqli_fetch_assoc($result1)){
           $ret_qty=($rel['base_stock']/$rel['product_base_qty'])*$rel['product_conv_qty'];
			
			$info['convert_stock'] = $ret_qty;

           $updateid=update_record('tbl_stock_trn',$info,'stock_id = ' .$rel['stock_id'], $dbcon);

           echo $i . " </br>";
           $i++;
           }	
        }    
    else{
        echo "no records found ::";
    }
	


?>
