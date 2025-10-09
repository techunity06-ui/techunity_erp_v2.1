<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	 $qry1="select * from tbl_po_grn_used as grn
	 		where grn.used_qty='' and po_grn_used_status=0";
	$result1=$dbcon->query($qry1);
	while($rel=brp_mysqli_fetch_assoc($result1)){
		$qry="select * from tbl_potrancation as grn
	 		where grn.potrancation_id=".$rel['potrancation_id'];
	$result=$dbcon->query($qry);
	$res=brp_mysqli_fetch_assoc($result);

	$qry2="select * from tbl_grn_sub_trn as grn
	 		where grn.grn_trn_sub_id=".$rel['grn_sub_trn_id'];
	$result2=$dbcon->query($qry2);
	$res2=brp_mysqli_fetch_assoc($result2);
		if($res['product_qty']<=$res2['product_qty']){
			$pqty=$res2['product_qty'];
		}else{
			$pqty=$res['product_qty'];
		}
		$info1['used_qty'] = $pqty;
		$info1['conv_used_qty'] = $pqty;
		$updatesalesid=update_record("tbl_po_grn_used", $info1,"tbl_po_grn_used=".$rel['po_grn_used_id'], $dbcon);
		update_grn_sub_trn_to_purchase_status($dbcon, $rel['grn_sub_trn_id']);
		var_dump($rel['po_grn_used_id']);			
	}



	function update_grn_sub_trn_to_purchase_status($dbcon, $grn_sub_trn_id)
    {
        $query = "select product_qty,grn_trn_id from tbl_grn_sub_trn as res
        where grn_trn_sub_id=" . $grn_sub_trn_id;

        $result = $dbcon->query($query);
        $row = brp_mysqli_fetch_assoc($result);

        $query_used = "select sum(used_qty) as used_qty from tbl_po_grn_used as res
        where po_grn_used_status=0 and grn_sub_trn_id=" . $grn_sub_trn_id;

        $result_used = $dbcon->query($query_used);
        $row_used = brp_mysqli_fetch_assoc($result_used);

        if ($row['product_qty'] <= $row_used['used_qty'])
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn_sub_trn SET purchase_status =1 WHERE grn_trn_sub_id = " . $grn_sub_trn_id);
        }
        else
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn_sub_trn SET purchase_status =0 WHERE grn_trn_sub_id = " . $grn_sub_trn_id);
        }

        $query_gtrn = "select grn_trn_id from tbl_grn_sub_trn as res
        where status=0 and purchase_status=0 and grn_trn_id=" . $row['grn_trn_id'];
        $result_gtrn = $dbcon->query($query_gtrn);
        $row_gtrn = brp_mysqli_fetch_assoc($result_gtrn);

        if (!empty($row_gtrn['grn_trn_id']))
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn_trn SET purchase_status=0 WHERE grn_trn_id = " . $row_gtrn['grn_trn_id']);
        }
        else
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn_trn SET purchase_status=1 WHERE grn_trn_id = " . $row['grn_trn_id']);
        }

        $query_g = "select grn_id from tbl_grn_trn as res
        where grn_trn_id=" . $row['grn_trn_id'];
        $result_g = $dbcon->query($query_g);
        $row_g = brp_mysqli_fetch_assoc($result_g);

        $query_gmst = "select grn_id from tbl_grn_trn as res
        where grn_trn_status=0 and purchase_status=0 and grn_id=" . $row_g['grn_id'];
        $result_gmst = $dbcon->query($query_gmst);
        $row_gmst = brp_mysqli_fetch_assoc($result_gmst);

        if (!empty($row_gmst['grn_id']))
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn SET purchase_status=0 WHERE grn_id = " . $row_gmst['grn_id']);
        }
        else
        {
            $query_invoicetype = $dbcon->query("UPDATE tbl_grn SET purchase_status=1 WHERE grn_id = " . $row_g['grn_id']);
        }
    }
		
				

?>
