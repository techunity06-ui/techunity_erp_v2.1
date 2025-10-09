<?php

session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
		
		}
		else if(strtolower($POST['mode']) == "add") {
		   	
			$rateTolerance = $POST['rate_tolerance'];
			$discountPercentage = $POST['discount_percentage'];

			$rateToleranceValue = ($POST['price']*$rateTolerance) / 100;
			$rateToleranceFinalValue =  $POST['price'] + $rateToleranceValue;

			$discountPercentageValue = ($POST['price']*$discountPercentage) / 100;
			$discountPercentageFinalValue =  $POST['price'] - $discountPercentageValue;

			$purchasesave['affected_date']	= date('Y-m-d',strtotime($POST['affected_date']));
			$purchasesave['currency_id'] = $POST['currency_id'];
			$purchasesave['purchase_type'] = $POST['purchase_type'];
			$purchasesave['vendor_id'] = $POST['vender_id'];
			$purchasesave['product_id'] = $POST['product_id'];
			$purchasesave['price'] = sprintf('%.2f', $POST['price']);
			$purchasesave['customer_wise_producttrn_status'] = 0;
			$purchasesave['rate_tolerance'] = $rateTolerance;
			$purchasesave['rate_tolerance_value'] = sprintf('%.2f', $rateToleranceFinalValue);
			$purchasesave['discount_percentage'] = $discountPercentage;
			$purchasesave['discount_percentage_value'] = sprintf('%.2f', $discountPercentageFinalValue);
			$purchasesave['grate'] = sprintf('%.2f', $POST['grate']);
			$purchasesave['quotation_number'] = $POST['quotation_no'];
			$purchasesave['quotation_date'] = date('Y-m-d',strtotime($POST['quotation_date']));
			$purchasesave['lead_time'] = $POST['lead_time'];
			$purchasesave['item_make'] = $POST['item_make'];
			$purchasesave['user_id'] = $_SESSION['user_id'];
			$purchasesave['company_id'] = $_SESSION['company_id'];
			$purchasesave['cdate'] = date('Y-m-d');
			$purchasesave['terms_condition'] = $POST['terms_condition'];

			$inserpoid = add_record('tbl_customer_wise_producttrn', $purchasesave, $dbcon);

			if($inserpoid)
			{	
				$updateRate['party_rate'] = $POST['price'];
				update_record('tbl_customer_wise_product', $updateRate, "party_id='".$POST['vender_id']."' and party_product = '".$POST['product_id']."' " , $dbcon);

				$arr['msg']="1";							
			}
			else{
				$arr['msg']="0";
			}
			$arr['back']=$POST['back'];
			echo json_encode($arr);					
		}

		else if(strtolower($POST['mode']) == "edit") {
			$update['party_category_id'] = $POST['party_category_id'];
			$update['party_product'] = $POST['party_product'];

			$check_sql = "SELECT * FROM tbl_customer_wise_product WHERE party_sales_id!='".$POST['id']."' AND party_id='".$POST['vender_id']."' AND party_product= '".$POST['party_product']." ' AND party_category_id= '".$POST['party_category_id']."' ";
			$check_result=$dbcon->query($check_sql);
			if(mysqli_num_rows($check_result)>0)
			{
				$arr['res']="0";
				$arr['msg']="This item is already assigned with this vendor.";
			}else{
				update_record('tbl_customer_wise_product', $update, "party_sales_id='".$POST['id']."'  " , $dbcon);
				$arr['res']="1";	
			}
			echo json_encode($arr);		
		}		

		else if(strtolower($POST['mode'])== "get_po_vendor_details")
		{
			$vendor_id = $POST['vendor_id'];
			$sql = "SELECT `v`.`l_id`,`v`.`l_name`,`v`.`l_form`, `v`.`cust_pincode`, `v`.`m_address`, `v`.`cust_mobile`, `v`.`cust_email`, `v`.`cust_website`, `v`.`gst_no`, `conm1`.`country_name`, `cm1`.`city_name`, `sm`.`state_name`  FROM `tbl_ledger` as v left join `country_mst` as conm1  ON `v`.`countryid`= `conm1`.`countryid` left join `city_mst` as cm1 ON `v`.`cityid`= `cm1`.`cityid` left join `state_mst` as sm ON `v`.`stateid`= `sm`.`stateid`  WHERE `v`.`l_id` = '".$vendor_id."' AND `v`.`company_id`='".$_SESSION['company_id']."'";
			$vrow=$dbcon->query($sql);
			$rel=mysqli_fetch_assoc($vrow);
			
			
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>Vendor Details</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Address </span>: '.$rel["m_address"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>City </span>: '.$rel["city_name"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>State </span>: '.$rel["state_name"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Country</span>: '.$rel["country_name"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Fax No. </span>: NA</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Email ID </span>: '.$rel["cust_email"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Mobile </span>: '.$rel["cust_mobile"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Website </span>: '.$rel["cust_website"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Pin Code </span>: '.$rel["cust_pincode"].'</p>
                             </div>
                             
                         </div>
                     </div>
                 </section>';
		}

		else if(strtolower($POST['mode'])== "set_vendor_sesion"){
			$vendor_id = $POST['vendor_id'];
			$_SESSION['selected_purchase_vendor'] = $vendor_id;
			$_SESSION['purchase_type'] = '0';
			$_SESSION['purchase_card_main_list'] = 'purchase_card_vendor';
		}
		
		/* Assign new item or vendor in tbl_customer_wise_product table */
		else if(strtolower($POST['mode'])== "set_new_item")
		{
			$type = $POST['purchase_type'];
			$v_or_iid = $POST['v_or_iid'];
			$product_id = $POST['new_product'];
			$price = $POST['price'];

			if($type=='0'){
				$check_sql = "SELECT * FROM tbl_customer_wise_product WHERE party_id='".$v_or_iid."' AND party_product= '".$product_id."' AND party_category_id= '".$POST['party_category_id']."' ";
				
				$check_result=$dbcon->query($check_sql);
				if(mysqli_num_rows($check_result)>0)
				{
					$row['res']="0";
					$row['msg']="This item is already assigned with this vendor.";
				}else{

					$infosave['party_id'] = $v_or_iid;
					$infosave['party_rate'] = $price;
					$infosave['party_product'] = $product_id;
					$infosave['party_category_id'] = $POST['party_category_id'];
					$infosave['cdate'] = date('Y-m-d');
					$infosave['user_id'] = $_SESSION['user_id'];
					$infosave['company_id']	= $_SESSION['company_id'];
					$infosave['branch_id']	= 0;

					$inserpoid=add_record('tbl_customer_wise_product', $infosave, $dbcon);
					$row['res']="1";
					$row['msg']="Item has been assigned to this vendor successfully.";
				}

				echo json_encode($row);
			}else{
				$party_id = $POST['new_product']; // fields name is based on the purchase tyupe
				$check_sql = "SELECT * FROM tbl_customer_wise_product WHERE party_id='".$party_id."' AND party_product= '".$v_or_iid."' ";
				$check_result=$dbcon->query($check_sql);
				if(mysqli_num_rows($check_result)>0)
				{
					$row['res']="0";
					$row['msg']="This vendor is already assigned with this item.";
				}else{

					$infosave['party_id'] = $party_id;
					$infosave['party_rate'] = $price;
					$infosave['party_product'] = $v_or_iid;
					$infosave['cdate'] = date('Y-m-d');
					$infosave['user_id'] = $_SESSION['user_id'];
					$infosave['company_id']	= $_SESSION['company_id'];
					$infosave['branch_id']	= 0;

					$inserpoid=add_record('tbl_customer_wise_product', $infosave, $dbcon);
					$row['res']="1";
					$row['msg']="Vendor has been assigned to this item successfully.";
				}

				echo json_encode($row);
			}
		}

		else if(strtolower($POST['mode'])== "get_item_selected_information")
		{
			$purchase_rate_info = [];
			$vendor_id = $POST['vender_id'];
			$product_id = $POST['product_id'];
			$type = $POST['type'];
			$purchase_rate_info = getItemPriceByCustomerId($dbcon, $vendor_id, $product_id);
			if(!empty($purchase_rate_info)){
				$affected_date = date('d-m-Y', strtotime($purchase_rate_info['affected_date']));
				$quotation_date = date('d-m-Y', strtotime($purchase_rate_info['quotation_date']));
			}else{
				$affected_date = date('d-m-Y');
				$quotation_date = date('d-m-Y');
			}
			

			$item_info = "SELECT pm.*, `um`.`unit_name`,`td`.`drawing_number` FROM product_mst as pm left join unit_mst as um ON `pm`.`product_conv_unit`=`um`.`unitid` left join tbl_drawing as td ON `pm`.`drawing_id`=`td`.`drawing_id` WHERE `pm`.`product_id`='".$product_id."' AND `pm`.`product_status`='0' AND `pm`.`company_id`='".$_SESSION['company_id']."'";
			$result=$dbcon->query($item_info);
	 		$response=mysqli_fetch_assoc($result);
			
	 		if(!empty($purchase_rate_info) || !empty($response)){

	 			$row['status']='1';
		 		$row['purchase_info'] = $purchase_rate_info;
		 		$row['purchase_info']['affected_date'] = $affected_date;
		 		$row['purchase_info']['quotation_date'] = $quotation_date;
		 		$row['item_info'] = $response;
		 		$row['today_date']=date("d-m-Y");
		 	}else{
		 		$row['status']='0';
		 		$row['today_date']=date("d-m-Y");
		 	}
		 	$row['party_category_id'] = $POST['party_category_id'];	
		 	$row['product_party'] = getproductsbycategory($dbcon,$POST['party_category_id'],$product_id);

	 		echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_product_by_category"){
			$data = getproductsbycategory($dbcon,$POST['category_id']);
			echo $data;
		}
    }
}




?>