<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
							
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

			$purchasesave['affected_date']		= date('Y-m-d',strtotime($POST['affected_date']));
			$purchasesave['currency_id'] 		= $POST['currency_id'];
			$purchasesave['purchase_type'] 		= $POST['purchase_type'];
			$purchasesave['vendor_id'] 			= $POST['vendor_id'];
			$purchasesave['product_id'] 		= $POST['product_id'];
			$purchasesave['price'] 				= sprintf('%.2f', $POST['price']);
			$purchasesave['purchasecardtrn_status'] = 0;
			$purchasesave['rate_tolerance'] 	= $rateTolerance;
			$purchasesave['rate_tolerance_value'] = sprintf('%.2f', $rateToleranceFinalValue);
			$purchasesave['discount_percentage']= $discountPercentage;
			$purchasesave['discount_percentage_value'] = sprintf('%.2f', $discountPercentageFinalValue);
			$purchasesave['grate'] 				= sprintf('%.2f', $POST['grate']);
			$purchasesave['quotation_number'] 	= $POST['quotation_no'];
			$purchasesave['quotation_date'] 	= date('Y-m-d',strtotime($POST['quotation_date']));
			$purchasesave['lead_time'] 			= $POST['lead_time'];
			$purchasesave['item_make'] 			= $POST['item_make'];
			$purchasesave['user_id'] 			= $_SESSION['user_id'];
			$purchasesave['company_id'] 		= $_SESSION['company_id'];
			$purchasesave['cdate']	 			= date('Y-m-d');
			$purchasesave['terms_condition'] 	= $POST['terms_condition'];

			$inserpoid = add_record('tbl_purchasecardtrn', $purchasesave, $dbcon);

			if($inserpoid)
			{	
				$updateRate['party_rate'] = $POST['price'];
				update_record('tbl_product_party_purchase', $updateRate, "party_id='".$POST['vendor_id']."' and party_product = '".$POST['product_id']."' " , $dbcon);
				$arr['msg']="1";							
			}
			else{
				$arr['msg']="0";
			}
			$arr['back']=$POST['back'];
			echo json_encode($arr);					
		 
		}		
		
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
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
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_po_items")
		{
			$product_id = $POST['product_id']; // as purchase id
			
			$sql = "SELECT pm.*, `u`.`user_name` FROM product_mst as pm left join users as u ON `pm`.`user_id` = `u`.`user_id` Where product_id='".$product_id."' AND `pm`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=mysqli_fetch_assoc($vrow);

			if($rel['product_status']=='1'){
			 	$status = 'Approved';
			 }else{ 
			 	$status = 'No';
			 }			
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>Item Details</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Item Name </span>: '.$rel["product_name"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Item Description </span>: '.$rel["product_desc"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Item Code </span>: '.$rel["product_icode"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Item HSN </span>: '.$rel["product_hsn"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Minimum Stock</span>: '.$rel["product_min_stock"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Make </span>: '.$rel["user_name"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span> Status </span>: '.$status.'</p>
                             </div>
                         </div>
                     </div>
                 </section>';
		}
		else if(strtolower($POST['mode'])== "set_item_sesion"){
			$item_id = $POST['item_id'];
			$_SESSION['selected_purchase_item'] = $item_id;
			$_SESSION['purchase_type'] = '1';
			$_SESSION['selected_product_type'] = $POST['product_type'];
			$_SESSION['selected_product_name'] = $POST['product_name'];
			$_SESSION['purchase_card_main_list'] = 'purchase_card_item';
		}
		
		else if(strtolower($POST['mode'])== "get_po_report")
		{
			$id = $POST['v_or_iid'];
			$type = $POST['type'];
			if($type=='0'){
				$sql = "SELECT `p`.*, `l`.`l_name` FROM tbl_purchasecard as p left join tbl_ledger as l ON `p`.`vender_id` = `l`.`l_id` WHERE `p`.`vender_id` = '".$id."' AND `p`.`purchasecard_status`=0 ORDER BY `p`.`purchasecard_id` DESC";

					$result=$dbcon->query($sql);
					
					if(mysqli_num_rows($result)>0)
					{
						$i=0;
						echo '<div class="bio-graph-info" style="height:350px; overflow:auto;overflow-x: hidden;">
							<h1>Purchase Card List</h1>
                               <table class="display table table-bordered table-striped" id="vendor-table">
                               <thead>
                                 <tr>
                                  <th>PC No.</th>
                                  <th>Vendor Name</th>
                                  <th>Affacted Date</th>
                                  <th>Rate</th>
                                 </tr>
                               </thead>
                               <tbody>';
						while($rel=mysqli_fetch_assoc($result))
						{
							$affected_date = date("d M, Y",strtotime($rel["affected_date"]));
							echo '<tr>
									<td><a  data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'pcedit/'.$rel['purchasecard_id'].'">'.$rel['purchasecard_no'].'</a></td>
									<td>'.$rel['l_name'].'</td>
									<td>'.$affected_date.'</td>
									<td>'.$rel['currency_rate'].'</td>
								  </tr>';
						}
						echo '</tbody>           
                               </table>
                                </div>';
					}else{
						echo "Purchase card record does not exists for this vendor.";
					}		
			}
			elseif($type=='1'){
				$sql = "SELECT `p`.*, `pm`.`product_name` FROM tbl_purchasecard as p left join product_mst as pm ON `p`.`product_id` = `pm`.`product_id` WHERE `p`.`product_id` = '".$id."' AND `p`.`purchasecard_status`=0 ORDER BY `p`.`purchasecard_id` DESC";
				$result=$dbcon->query($sql);
					
					if(mysqli_num_rows($result)>0)
					{
						$i=0;
						echo '<div class="bio-graph-info" style="height:350px; overflow:auto;overflow-x: hidden;">
							<h1>Purchase Card List</h1>
                               <table class="display table table-bordered table-striped" id="vendor-table">
                               <thead>
                                 <tr>
                                  <th>PC No.</th>
                                  <th>Item Name</th>
                                  <th>Affacted Date</th>
                                  <th>Rate</th>
                                 </tr>
                               </thead>
                               <tbody>';
						while($rel=mysqli_fetch_assoc($result))
						{
							$affected_date = date("d M, Y",strtotime($rel["affected_date"]));
							echo '<tr>
									<td><a  data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'pcedit/'.$rel['purchasecard_id'].'">'.$rel['purchasecard_no'].'</a></td>
									<td>'.$rel['product_name'].'</td>
									<td>'.$affected_date.'</td>
									<td>'.$rel['currency_rate'].'</td>
								  </tr>';
						}
						echo '</tbody>           
                               </table>
                                </div>';
					}else{
						echo "Purchase card record does not exists for this product.";
					}	
			}

		}

		/* Assign new item or vendor in tbl_product_party_purchase table */
		else if(strtolower($POST['mode'])== "set_new_item")
		{
			$type = $POST['purchase_type'];
			$v_or_iid = $POST['v_or_iid'];
			$product_id = $POST['new_product'];
			$price = $POST['price'];

			if($type=='0'){
				$check_sql = "SELECT * FROM tbl_product_party_purchase WHERE party_id='".$v_or_iid."' AND party_product= '".$product_id."' ";
				$check_result=$dbcon->query($check_sql);
				if(mysqli_num_rows($check_result)>0)
				{
					$row['res']="0";
					$row['msg']="This item is already assigned with this vendor.";
				}else{

					$infosave['party_id'] = $v_or_iid;
					$infosave['party_rate'] = $price;
					$infosave['party_product'] = $product_id;
					$infosave['cdate'] = date('Y-m-d');
					$infosave['user_id'] = $_SESSION['user_id'];
					$infosave['company_id']	= $_SESSION['company_id'];
					$infosave['branch_id']	= 0;

					$inserpoid=add_record('tbl_product_party_purchase', $infosave, $dbcon);
					$row['res']="1";
					$row['msg']="Item has been assigned to this vendor successfully.";
				}

				echo json_encode($row);
			}else{
				$party_id = $POST['new_product']; // fields name is based on the purchase tyupe
				$check_sql = "SELECT * FROM tbl_product_party_purchase WHERE party_id='".$party_id."' AND party_product= '".$v_or_iid."' ";
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

					$inserpoid=add_record('tbl_product_party_purchase', $infosave, $dbcon);
					$row['res']="1";
					$row['msg']="Vendor has been assigned to this item successfully.";
				}

				echo json_encode($row);
			}
		}

		else if(strtolower($POST['mode'])== "get_item_selected_information")
		{
			$purchase_rate_info = array();
			$vendor_id = $POST['vendor_id'];
			$product_id = $POST['product_id'];
			$type = $POST['type'];
			$purchase_rate_info = getItemPriceByProductId($dbcon, $product_id, $vendor_id);
			$affected_date = date('d-m-Y', strtotime($purchase_rate_info['affected_date']));
			$quotation_date = date('d-m-Y', strtotime($purchase_rate_info['quotation_date']));

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
		 		$row['vendor_id'] = $vendor_id;
	 		echo json_encode($row);
		}
    }
}




?>