<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	include("../include/function_database_query.php");
	
	$cnt=1;
	$sel=$dbcon->query("select * from tbl_purchaseordertrn");
  	
  	$que="select * from tbl_request_product where perent_id!=0 and status!=2";
   	$sel=$dbcon->query($que);

	while($row=brp_mysqli_fetch_array($sel))
	{
		$formula_id = $row['formulaid'];
		
		$sel_tax = $dbcon->query("select * from formula_mst where formulaid='$formula_id'");
		$r_tax = brp_mysqli_fetch_array($sel_tax);
		
		$tax_cat = substr($r_tax['formula_name'],5);
		
		$tax_per = substr($tax_cat, 0, strpos($tax_cat, '%'));
		
		if($r_tax['tax_cat']=="INTRA")
		{
			$new_tax_per = $tax_per*2;
			
			$tax_amount = ($tax_per*$row['product_amount'])/100;
			
			$total = $row['product_amount']+$tax_amount;
			
			$info1['tx_tax_id'] = "9870";
			$info1['tx_tax_value'] = $tax_per;
			$info1['tx_taxable_value'] = $tax_amount;
			$info1['tx_transaction_id'] = $row['purchaseordertrn_id'];
			$info1['tx_transaction_type'] = 'tbl_purchaseordertrn';
			$info1['tx_product_id']	= $row['product_id'];
			$info1['tx_status'] = 0;
			$info1['cdate']	 = date("Y-m-d H:i:s");
			$info1['user_id'] = $row['user_id'];
			$info1['company_id'] = $row['company_id'];
			$info1['branch_id'] = $row['branch_id'];

			$inserid=add_record("tbl_tax_trn",$info1, $dbcon,$branch_id);
			
			$info2['tx_tax_id'] = "9880";
			$info2['tx_tax_value'] = $tax_per;
			$info2['tx_taxable_value'] = $tax_amount;
			$info2['tx_transaction_id'] = $row['purchaseordertrn_id'];
			$info2['tx_transaction_type'] = 'tbl_purchaseordertrn';
			$info2['tx_product_id']	= $row['product_id'];
			$info2['tx_status'] = 0;
			$info2['cdate']	 = date("Y-m-d H:i:s");
			$info2['user_id'] = $row['user_id'];
			$info2['company_id'] = $row['company_id'];
			$info2['branch_id'] = $row['branch_id'];

		  	$que1="select * from tbl_request_product where rp_id=".$row['perent_id'];
			$sel1=$dbcon->query($que1);
			$row1=brp_mysqli_fetch_array($sel1);
			$cal=$row['rp_req_qty']/$row1['rp_req_qty'];

			$inserid=add_record("tbl_tax_trn",$info2, $dbcon,$branch_id);
			
			$dbcon->query("update tbl_purchaseordertrn set  cgst_tax_per='$tax_per',cgst_tax_rate='$tax_amount',sgst_tax_per='$tax_per',sgst_tax_rate='$tax_amount' where purchaseordertrn_id='$row[purchaseordertrn_id]'");

			
		}
		else
		{
			$new_tax_per = $tax_per;
			
			$tax_amount = ($tax_per*$row['product_amount'])/100;
			
			$total = $row['product_amount']+$tax_amount;
			
			$info2['tx_tax_id'] = "9890";
			$info2['tx_tax_value'] = $tax_per;
			$info2['tx_taxable_value'] = $tax_amount;
			$info2['tx_transaction_id'] = $row['purchaseordertrn_id'];
			$info2['tx_transaction_type'] = 'tbl_purchaseordertrn';
			$info2['tx_product_id']	= $row['product_id'];
			$info2['tx_status'] = 0;
			$info2['cdate']	 = date("Y-m-d H:i:s");
			$info2['user_id'] = $row['user_id'];
			$info2['company_id'] = $row['company_id'];
			$info2['branch_id'] = $row['branch_id'];

			$inserid=add_record("tbl_tax_trn",$info2, $dbcon,$branch_id);
			
			$dbcon->query("update tbl_purchaseordertrn set  igst_tax_per='$tax_per',igst_tax_rate='$tax_amount' where purchaseordertrn_id='$row[purchaseordertrn_id]'");
		}
		
		//echo $cnt . "--" . $new_tax_per."<br>";
		
		$sel_tcat  = $dbcon->query("select * from tbl_tax_category where tax_gst='$new_tax_per'");
		$r_tcat = brp_mysqli_fetch_array($sel_tcat);
		
		$tax_cat_id = $r_tcat['tax_cat_id'];
		
		//echo $cnt . "--" . $new_tax_per."--".$tax_cat_id."<br>";

		$q_update = "update tbl_purchaseordertrn set product_tax_cat='$tax_cat_id' where purchaseordertrn_id='$row[purchaseordertrn_id]'"."<br>";
		
		$dbcon->query("update tbl_purchaseordertrn set product_tax_cat='$tax_cat_id' where purchaseordertrn_id='$row[purchaseordertrn_id]'");
		
		echo $q_update;

		$cnt++;
		$info['req_qty_one'] = $cal;
		$updateid=update_record('tbl_request_product', $info,"rp_id=".$row['rp_id'] , $dbcon);

	}
?>