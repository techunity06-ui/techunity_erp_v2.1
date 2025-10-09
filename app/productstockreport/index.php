<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
include("../../include/function_database_query.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
            if(strtolower($POST['mode']) == "generate_report") {
                $s_date=date('Y-m-d',strtotime($POST['date']));
		$where='';
		if(!empty($POST['material_id']))
		{
			$where =' and pro.product_id='.$POST['material_id'];
		}
		$query="SELECT pro.product_id,product_name,product_min_stock,purchaseqty,todaypurchaseqty,invoiceqty
		,todayinvoiceqty FROM `tbl_product` as pro 
		left join (select sum(purtrn.product_qty) as purchaseqty,purtrn.product_id from  tbl_potrancation as purtrn inner join tbl_pono as po on po.po_id=purtrn.po_id where purtrn.potrancation_status=0 and po.po_date < '".$s_date."' and po.company_id=".$_SESSION['company_id']." 
		group by purtrn.product_id) as purchase on purchase.product_id=pro.product_id
		
		left join (select sum(purtrn.product_qty) as todaypurchaseqty,purtrn.product_id from  tbl_potrancation as purtrn inner join tbl_pono as po on po.po_id=purtrn.po_id where purtrn.potrancation_status=0 and po.po_date = '".$s_date."' and po.company_id=".$_SESSION['company_id']." 
		group by purtrn.product_id) as purchase1 on purchase1.product_id=pro.product_id
		
		left join (select sum(invtrn.product_qty) as invoiceqty,invtrn.product_id from  tbl_invoicetrn as invtrn inner join tbl_invoice as invmst on invmst.invoice_id=invtrn.invoice_id where invtrn.trancation_status=0 and invmst.company_id=".$_SESSION['company_id']." and invoice_date<'".$s_date."'  
		group by invtrn.product_id) as invoice on invoice.product_id=pro.product_id
		
		left join (select sum(invtrn.product_qty) as todayinvoiceqty,invtrn.product_id from  tbl_invoicetrn as invtrn inner join tbl_invoice as invmst on invmst.invoice_id=invtrn.invoice_id where invtrn.trancation_status=0 and invmst.company_id=".$_SESSION['company_id']." and invoice_date='".$s_date."'  
		group by invtrn.product_id) as invoice1 on invoice1.product_id=pro.product_id
		
		where pro.product_status!=2 and pro.product_type=0 ".$where." group by pro.product_id order by product_name";
		$rs=$dbcon->query($query);
			$str='';
			$i=1;$total_mat_val=0;
			while($rel=mysqli_fetch_assoc($rs))
			{
				$op_stock=$rel['product_stock']+$rel['purchaseqty']-($rel['invoiceqty']);
				$total=$op_stock+$rel['todaypurchaseqty'];
				$cl_stock=$total-($rel['todayinvoiceqty']);
				
				$str.='<tr><td style="text-align:center;">'.$i.'</td>
					  <td>'.$rel['product_name'].'</td>
					  <td style="text-align:right;">'.$op_stock.'</td>
					  <td style="text-align:right;">'.$rel['product_min_stock'].'</td>
					  <td style="text-align:right;">'.$cl_stock.'</td>
					  </tr>';
				$i++;					  
			}
			
			echo $str;
			
		}
	
	}
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/

?>