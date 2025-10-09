<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
}
else if(strtolower($POST['mode']) == "purchase_card_report")
{
	//var_dump($POST);
	$s_date=explode(' - ',$POST['date']);
	$set = "select * from tbl_company where company_id=".$_SESSION
	['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	$where = '';
	if($POST['product_id']){
		$where .= " and trn.product_id=".$POST['product_id'];
	}
	if($POST['vender_id']){
		$where .= " and trn.vendor_id=".$POST['vender_id'];
	}
	$str ='';
	$str .='<table width="100%" class="display table table-bordered table-striped">
		<tr>
			<th>Sr.No</th>
			<th>Purchase Card No.</th>
			<th>Purchase Card Date</th>
			<th>Vendor Name</th>
			<th>Product Name</th>
			<th>Effective Date</th>
			<th>Valid Date</th>
			<th>Rate</th>
			<th>Disc.</th>
			<th>Tolerance</th>
		</tr>';

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
	$pro_search=explode(",", $purchase_pro_search);
	$query = 'select trn.*, card.pur_card_no, card.pur_card_date, led.l_name, pro.product_name,pro.product_icode,dr.drawing_number from tbl_purchasecardtrn as trn
	left join tbl_product_party_purchase as card on card.party_purchase_id=trn.party_purchase_id
	left join tbl_ledger as led on led.l_id=trn.vendor_id
	left join product_mst as pro on pro.product_id = trn.product_id
	left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
	where trn.purchasecardtrn_status=0 and trn.company_id='.$_SESSION['company_id'].' '.$where;

 	$result=$dbcon->query($query);
	$i=1;
	if(brp_mysqli_num_rows($result)>0){
		while($row = brp_mysqli_fetch_array($result)){
		 	if(in_array('drawing',$pro_search)){
	        	$drawing_number = " -- (".$row['drawing_number'].")";
	        }
	        if(in_array('item',$pro_search)){
	            $item_code = " -- (".$row['product_icode'].")";
	        }
	        $disc='';$tol='';
	        if($row['discount_percentage']){
	        	$disc = $row['discount_percentage']." %";
	        }
	        if($row['rate_tolerance']){
	        	$tol = $row['rate_tolerance']." %";
	        }
	        $str .= '<tr>
	        	<td>'.$i.'</td>
	        	<td>'.$row['pur_card_no'].'</td>
	        	<td>'.$row['pur_card_date'].'</td>
	        	<td>'.$row['l_name'].'</td>
	        	<td>'.$row['product_name'].' -- '.$row['product_icode'].'</td>
	        	<td>'.$row['affected_date'].'</td>
	        	<td>'.$row['valid_date'].'</td>
	        	<td>'.$row['price'].'</td>
	        	<td>'.$disc.'</td>
	        	<td>'.$tol.'</td>
	        </tr>';
	        $i++;
		}
	}else{
		$str .='<tr>
			<td colspan="10" style="text-align:center">No Data Yet...</td>
		</tr>';
	}
 	
	$str.='</table>';
	echo $str;
}
?>