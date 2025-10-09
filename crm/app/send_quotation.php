<?php 
function send_whatsapp_quotation($dbcon,$quotation_id,$quotation_no){	
	$getNum=brp_mysqli_fetch_assoc($dbcon->query("SELECT quot.*, cust.cust_name, cust.cust_mobile FROM tbl_quotation as quot LEFT JOIN tbl_customer as cust on cust.cust_id=quot.cust_id WHERE quot.quotation_id=".$quotation_id));

	if(strlen($getNum['cust_mobile']) == 10){
		// $quot_pdf_link='http://www.brperp.com/common_brp_devlopment/view/upload/quotation_pdf_file/Quotation_'.$quotation_id.'.pdf';
		// $quot_pdf_link='https://www.brperp.com/whatsapp_demo_file2.PDF';
		$quot_pdf_link=DOMAIN.QUO_A.'Quotation_'.$quotation_id.'.pdf';
		$message=urlencode("Hello,\nYour Quotation No: ".$quotation_no."\nTotal Quotation Amount: ".$getNum['g_total']."\nThank You");
		// $message=urlencode("abc demo");
		$url="http://sm.gocreation.in/api/sendFileWithCaption?token=6113947b6a7189f30f1a6dde&phone=91".$getNum['cust_mobile']."&link=".$quot_pdf_link."&message=".$message;

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
		));
		// var_dump($url);
		$output=curl_exec($ch);
		if($output['status']=='success'){
			unlink($quot_pdf_link);
		}
		// $row=array();
		// $row['status']="success";
		// $row['message']="Number is Valid";
		// $row['link']=$url;
		// return json_encode($row);
		return $output;
		// return $url;
	} else {
		$row=array();
		$row['status']="error";
		$row['message']="Number is Invalid";
		return json_encode($row);
	}
}
function send_whatsapp_po($dbcon,$purchaseorder_id){	
	$getNum=brp_mysqli_fetch_assoc($dbcon->query("SELECT quot.*, cust.cust_mobile FROM tbl_purchaseorder as quot LEFT JOIN tbl_ledger as cust on cust.l_id=quot.vender_id WHERE quot.purchaseorder_id=".$purchaseorder_id));

	if(strlen($getNum['cust_mobile']) == 10){
		// $quot_pdf_link='http://www.brperp.com/common_brp_devlopment/view/upload/quotation_pdf_file/Quotation_'.$quotation_id.'.pdf';
		// $quot_pdf_link='https://www.brperp.com/whatsapp_demo_file2.PDF';
		$quot_pdf_link=DOMAIN.QUO_A.'Purchase_Order_'.$purchaseorder_id.'.pdf';
		$message=urlencode("Hello,\nYour Purchase Order No: ".$getNum['purchaseorder_no']."\nThank You");
		// $message=urlencode("abc demo");
		$url="http://sm.gocreation.in/api/sendFileWithCaption?token=6113947b6a7189f30f1a6dde&phone=91".$getNum['cust_mobile']."&link=".$quot_pdf_link."&message=".$message;

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
		));
		// var_dump($url);
		$output=curl_exec($ch);
		if($output['status']=='success'){
			unlink($quot_pdf_link);
		}
		// $row=array();
		// $row['status']="success";
		// $row['message']="Number is Valid";
		// $row['link']=$url;
		// return json_encode($row);
		return $output;
		// return $url;
	} else {
		$row=array();
		$row['status']="error";
		$row['message']="Number is Invalid";
		return json_encode($row);
	}
}
?>