<?php

session_start();
$AJAX = true;

$path = '../../../';
$include = '../../../include/';

include($path."config/config.php");
//error_reporting(E_ALL);
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
							
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
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';
		
		//echo $_SESSION['page'];
			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);

		
			$where.="  and purchasecard_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND purchasecard_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('pc.*','l.l_name', 'pm.product_name');
			$sIndexColumn = "purchasecard_id";
			$isWhere = array("purchasecard_status = 0".$where);
			$sTable = "tbl_purchasecard as pc";
			$isJOIN = array('left join tbl_ledger as l on pc.vender_id=l.l_id', 'left join product_mst pm on pc.product_id=pm.product_id');
			$hOrder = "pc.purchasecard_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;

			
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['purchasecard_no'];
				$row_data[] = date('d M, Y',strtotime($row['purchasecard_date']));
				$row_data[] = $row['l_name'];
				$row_data[] = $row['product_name'];
				
				if($row['pc_approval_status']=='1'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}
				else{
					$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
				}
				
				$delete='';$edit='';$cancel_po_btn='';$po_app_btn='';
				//PO Approval Button To admin
				if($_SESSION['user_type']=='2'){

					if($appr_btn_per){
						if($row['pc_approval_status']=='1'){
							$po_app_btn='<button class="btn btn-xs btn-success" data-original-title="PC Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status('.$row['purchasecard_id'].',0)"><i class="fa fa-check"></i></button>';
						}
						else{
							$po_app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve PC" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status('.$row['purchasecard_id'].',1)"><i class="fa fa-check"></i></button>';
						}
					}
				}
				if($row['purchasecard_status']==0){
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_po('.$row['purchasecard_id'].')"><i class="fa fa-trash-o"></i></button>';
					$edit='<a class="btn btn-xs btn-warning" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'pcedit/'.$row['purchasecard_id'].'"><i class="fa fa-eye"></i></a>';
				}
				$add_po_btn='';
				if($row['po_type_status']==2){
					if($row['po_req_status']=='1'){
						$add_po_btn='<button class="btn btn-xs btn-success" data-original-title="PC Created" data-toggle="tooltip" data-placement="top" >PC Created</button>';
					}
					else{
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PC" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'direct_po_add/'.$row['purchasecard_id'].'"><i class="fa fa-plus"></i></a>';			
						$cancel_po_btn='<button class="btn btn-xs btn-danger" data-original-title="Cancel PC" data-toggle="tooltip" data-placement="top" onclick="cancel_po_status('.$row['purchasecard_id'].',3)"><i class="fa fa-ban"></i></button>';
					}

				}
				if($row['po_type_status']==3){
					$cancel_po_btn='<button class="btn btn-xs btn-info" data-original-title="Request PC" data-toggle="tooltip" data-placement="top" onclick="cancel_po_status('.$row['purchasecard_id'].',2)"><i class="fa fa-check"></i></button>';
				}
				
				$row_data[] = $edit.' '.$delete.' '.$po_app_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {

		   $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
			
			$info['po_type_status']	= 1;
			$info['purchase_type']	= $POST['purchase_type'];
			$info['purchasecard_no']	= $POST['purchasecard_no'];
			$info['vender_id']	= $POST['vender_id'];
			$info['purchasecard_date']	= date('Y-m-d',strtotime($POST['purchasecard_date']));
			$info['affected_date']	= date('Y-m-d',strtotime($POST['affected_date']));
			$info['quotation_date']	= date('Y-m-d',strtotime($POST['quotation_date']));
			$info['product_type']	= $POST['product_type'];
			$info['product_id']		= $POST['product_id'];
			$info['currency_id']	= $POST['currency_id'];
			//$info['currency_rate']	= $POST['currency_rate'];
			//$info['rate_tolerance']		= $POST['rate_tolerance'];
			//$info['g_rate']	= $POST['g_rate'];
			//$info['discount_percentage']	= $POST['discount_percentage'];
			$info['quotation_no']	= $_POST['quotation_no'];
			$info['terms_condition']	= $_POST['terms_condition'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$info['purchasecard_status']		= 0;

			$inserpoid=add_record('tbl_purchasecard', $info, $dbcon);

			$appr_btn_per=check_permission("purchase_card_list",$_SESSION['user_id'],'aprv',$dbcon);
			//var_dump($appr_btn_per);
			if($appr_btn_per){
				$infopo['auserid']			   = $_SESSION['user_id'];
				$infopo['adate']			   = date("Y-m-d H:i:s");					
				$infopo['pc_approval_status']  = 1;//Change Status to Done
				$updateid12=update_record('tbl_purchasecard', $infopo,"purchasecard_id=".$inserpoid , $dbcon);
			}else{
				$infopo['pc_approval_status']			= 0;//Change Status to Done
				$updateid12=update_record('tbl_purchasecard', $infopo,"purchasecard_id=".$inserpoid , $dbcon);
			}
			
			if($inserpoid)
			{	

				/* Insert the product and vendor data with price in tbl_purchasecardtrn table */
				if(!empty($POST['product_rate_list'])){
					foreach($POST['product_rate_list'] as $pkey => $val){
						if($POST['purchase_type']=='0'){
							$vendorId = $POST['vender_id'];
							$productId = $POST['product_id_list'][$pkey];
						}else{
							$vendorId = $POST['vendor_id_list'][$pkey];
							$productId = $POST['product_id'];
						}

						$rateTolerance = $POST['rate_tolerance'][$pkey];
						$discountPercentage = $POST['discount_percentage'][$pkey];

						$rateToleranceValue = ($POST['product_rate_list'][$pkey]*$rateTolerance) / 100;
						$rateToleranceFinalValue =  $POST['product_rate_list'][$pkey] + $rateToleranceValue;

						$discountPercentageValue = ($POST['product_rate_list'][$pkey]*$discountPercentage) / 100;
						$discountPercentageFinalValue =  $POST['product_rate_list'][$pkey] - $discountPercentageValue;

						$purchasesave['affected_date']	= date('Y-m-d',strtotime($POST['affected_date']));
						$purchasesave['purchasecard_id'] = $inserpoid;
						$purchasesave['purchase_type'] = $POST['purchase_type'];
						$purchasesave['vendor_id'] = $vendorId;
						$purchasesave['product_id'] = $productId;
						$purchasesave['price'] = sprintf('%.2f', $POST['product_rate_list'][$pkey]);
						$purchasesave['purchasecardtrn_status'] = 0;
						$purchasesave['rate_tolerance'] = $rateTolerance;
						$purchasesave['rate_tolerance_value'] = sprintf('%.2f', $rateToleranceFinalValue);
						$purchasesave['discount_percentage'] = $discountPercentage;
						$purchasesave['discount_percentage_value'] = sprintf('%.2f', $discountPercentageFinalValue);

						add_record('tbl_purchasecardtrn', $purchasesave, $dbcon);
					}
				}

				$arr['msg']="1";							
			}
			else{
				$arr['msg']="0";
			}
			$arr['back']=$POST['back'];
			echo json_encode($arr);					
		 
		}		
		else if(strtolower($POST['mode']) == "edit") {

			 
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['purchasecard_status']		= 2;
			
			$updateinvoiceid=update_record('tbl_purchasecard', $info,"purchasecard_id=".$POST['eid'] , $dbcon);	
			update_record('tbl_purchasecardtrn', array('purchasecardtrn_status' => 2), "purchasecard_id=".$POST['eid'] , $dbcon);	

			if($updateinvoiceid)
				echo "1";	
			else
				echo "0";			
		}
		
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		
		
		
		else if(strtolower($POST['mode'])== "load_product_tax")
		{
			$cust_arr=get_cust_data_arr($dbcon,$POST['vendor']);
			$cust_state=$cust_arr['stateid'];
			$r=get_product_tax_formula($dbcon,$POST['pid'],$_POST['tran_type'],$cust_state);
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo $r;
			//echo $cust_state;
		}
		
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			if(!empty($POST['purchaseorder_id']))
			{
				$info['purchaseordertrn_status']=2;	
				//$row['producthtml']=getpurchaseproduct($dbcon,0,'Edit',$POST['purchaseorder_id']);
				delete_po_req_status($dbcon,$POST['eid']);
			}
			else
			{
				$info['purchaseordertrn_status']=2;	
				//$row['producthtml']=getpurchaseproduct($dbcon,0,'Add');
			}
			$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and product_type='.$type_id.'');
		}
		
		else if(strtolower($POST['mode'])== "cancel_po_status")
		{
			$row=array();
			$info['po_type_status'] = $POST['po_status'];	
			
			$updateid=update_record("tbl_purchasecard", $info,"purchasecard_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "change_po_approval_status")
		{
			$row=array();
			$info['pc_approval_status'] = $POST['pc_approval_status'];	
			
			$updateid=update_record("tbl_purchasecard", $info, "purchasecard_id=".$POST['eid'], $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		else if(strtolower($POST['mode'])== "get_po_billing_terms")
		{
			/*echo "<pre>";
			print_r($_POST);*/
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
		else if(strtolower($POST['mode'])== "set_vendor_sesion"){
			$vendor_id = $POST['vendor_id'];
			$_SESSION['selected_purchase_vendor'] = $vendor_id;
			$_SESSION['purchase_type'] = '0';
			$_SESSION['purchase_card_main_list'] = 'purchase_card_vendor';

			
		}
		else if(strtolower($POST['mode'])== "set_item_sesion"){
			$item_id = $POST['item_id'];
			$_SESSION['selected_purchase_item'] = $item_id;
			$_SESSION['purchase_type'] = '1';
			$_SESSION['selected_product_type'] = $POST['product_type'];
			$_SESSION['selected_product_name'] = $POST['product_name'];
			$_SESSION['purchase_card_main_list'] = 'purchase_card_item';
		}
		
		else if(strtolower($POST['mode'])== "get_po_listing_info")
		{
			$eid = $POST['eid'];
			$type = $POST['type'];
			$id = $POST['v_or_iid'];

			// Add New record
			if($eid==''){
				// vendor wise selection
				if($type=='0'){
					
					$sql = "SELECT `ppp`.`party_purchase_id`,`ppp`.`party_rate`, `ppp`.`party_rate`,`pm`.`product_name`  ,`ppp`.`party_product` FROM `tbl_product_party_purchase` as ppp left join product_mst as pm ON `ppp`.`party_product`=`pm`.`product_id` WHERE `ppp`.`party_id` = '".$id."' ORDER BY `ppp`.`party_purchase_id` DESC";

					$result=$dbcon->query($sql);
					
					if(mysqli_num_rows($result)>0)
					{
						$i=0;
						echo '<section class="panel" style="height:350px; overflow:auto;overflow-x: hidden;">
                     			<div class="panel-body bio-graph-info">
                     			<h1>Item List</h1>';
                     	echo   '<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
								   			 <label for="Product Type" style="text-align:left" class="col-md-6 control-label">Item Name</label>
								   			 <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Item Rate</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Rate Tolerance (%)</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Discount Percentage (%)</label>
										      </div>
								   		</div>
								   	</div>
								</div>';		
                     	echo 	'<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
									      <label for="Product Type" style="text-align:left" class="col-md-6 control-label">
									         <select class="select2 selproduct" title="Select product" name="party_product" id="party_product""  >
									            <option value="">Choose Product</option>
									            '.getproduct($dbcon,'').'
									         </select>
									      </label>
									      <div class="col-md-2">
									         <input type="number" class="form-control" id="party_rate" name="party_rate" placeholder="Party Rate" value="0.00" >
									      </div>
									      <div class="col-md-1">
									      <input type="button" name="addprice" id="addprice" onclick="return add_price();" class="btn btn-primary" value="Add" required>
									  </div>
								   </div>
								   <div>
								   </div>
								</div>
							</div>';

									while($rel=mysqli_fetch_assoc($result))
									{
										$getItem = getItemPriceByVendorId($dbcon, $id, $rel['party_product']); // parameters (connection, vendorid, productid)
										if($getItem){
											$lastPrice = $getItem['price']; 
											$rate_tolerance = $getItem['rate_tolerance']; 
											$discount_percentage = $getItem['discount_percentage']; 
										}else{
											$lastPrice = '0.00';
											$rate_tolerance = '0';
											$discount_percentage = '0';
										}
										echo '<div class="col-md-12 margin_row">
											  <div class="col-md-8">
												  <div class="form-group">
													  <label for="Product Type" style="text-align:left" class="col-md-6 control-label">'.$rel['product_name'].'</label>
													  <input type="hidden" name="product_id_list[]" value="'.$rel['party_product'].'">
													  <div class="col-md-2">
													  <input type="number" class="form-control" id="product_rate_list" name="product_rate_list[]" placeholder="Price" value="'.$lastPrice.'" required>
													  </div>
													  <div class="col-md-2">
													  <input type="number" class="form-control" id="rate_tolerance" name="rate_tolerance[]" placeholder="Rate Tolerance" value="'.$rate_tolerance.'" required>
													  </div>
													  <div class="col-md-2">
													  <input type="number" class="form-control" id="discount_percentage" name="discount_percentage[]" placeholder="Discount Percentage" value="'.$discount_percentage.'" required>
													  </div>
												  </div>							 
											  </div>
										</div>';
									}
								echo '</div></section>';

					}else{
						
						echo '<section class="panel" style="height:350px; overflow:auto;overflow-x: hidden;">
                     			<div class="panel-body bio-graph-info">
                     			<h1>Assign Item</h1>';
                     	echo   '<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
								   			 <label for="Product Type" style="text-align:left" class="col-md-6 control-label">Item Name</label>
								   			 <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Item Rate</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Rate Tolerance (%)</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Discount Percentage (%)</label>
										      </div>
								   		</div>
								   	</div>
								</div>';			
                     	echo 	'<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
									      <label for="Product Type" style="text-align:left" class="col-md-6 control-label">
									         <select class="select2 selproduct" title="Select product" name="party_product" id="party_product""  >
									            <option value="">Choose Product</option>
									            '.getproduct($dbcon,'').'
									         </select>
									      </label>
									      <div class="col-md-2">
									         <input type="number" class="form-control" id="party_rate" name="party_rate" placeholder="Party Rate" value="0.00">
									      </div>
									      
									      <div class="col-md-1">
									      <input type="button" name="addprice" id="addprice" onclick="return add_price();" class="btn btn-primary" value="Add" required>
									  </div>
								   </div>
								   <div>
								   </div>
								</div>
							</div>';		
						echo '</div></section>';
					}	
				}
				// item wise selection
				elseif($type=='1'){
					$sql = "SELECT `ppp`.`party_purchase_id`,`ppp`.`party_id`, `ppp`.`party_rate`,`v`.`l_name`  FROM `tbl_product_party_purchase` as ppp left join tbl_ledger as v ON `ppp`.`party_id`=`v`.`l_id` WHERE `ppp`.`party_product` = '".$id."' ORDER BY `ppp`.`party_purchase_id` DESC ";
					$result=$dbcon->query($sql);

					if(mysqli_num_rows($result)>0)
					{
						$i=0;
						echo '<section class="panel" style="height:350px; overflow:auto;overflow-x: hidden;">
                     			<div class="panel-body bio-graph-info">
                     			<h1>Vendor List</h1>';
                     	echo   '<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
								   			 <label for="Product Type" style="text-align:left" class="col-md-6 control-label">Vendor Name</label>
								   			 <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Vendor Rate</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Rate Tolerance (%)</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Discount Percentage (%)</label>
										      </div>
								   		</div>
								   	</div>
								</div>';		
                     	echo 	'<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
									      <label for="Product Type" style="text-align:left" class="col-md-6 control-label">
									         <select class="select2 selproduct" title="Select product" name="party_product" id="party_product"" >
									            <option value="">Choose Product</option>
									            '.getcust($dbcon,'').'
									         </select>
									      </label>
									      <div class="col-md-5 col-xs-11">
									         <input type="number" class="form-control" id="party_rate" name="party_rate" placeholder="Party Rate" value="0.00">
									      </div>
									      <div class="col-md-1">
									      <input type="button" name="addprice" id="addprice" onclick="return add_price();" class="btn btn-primary" value="Add">
									  </div>
								   </div>
								   <div>
								   </div>
								</div>
							</div>';
									
									while($rel=mysqli_fetch_assoc($result))
									{
										$getItem = getItemPriceByProductId($dbcon, $id, $rel['party_id']); // parameters (connection, productid, vendorid)
										if($getItem){
											$lastPrice = $getItem['price']; 
											$rate_tolerance = $getItem['rate_tolerance']; 
											$discount_percentage = $getItem['discount_percentage']; 
										}else{
											$lastPrice = '0.00';
											$rate_tolerance = '0';
											$discount_percentage = '0';
										}
										echo '<div class="col-md-12 margin_row">
											  <div class="col-md-8">
												  <div class="form-group">
													  <label for="Product Type" style="text-align:left" class="col-md-6 control-label">'.$rel['l_name'].'</label>
													  <input type="hidden" name="vendor_id_list[]" value="'.$rel['party_id'].'">
													  <div class="col-md-2">
													  <input type="number" class="form-control" id="product_rate_list" name="product_rate_list[]" placeholder="Price" value="'.$lastPrice.'">
													  </div>
													  <div class="col-md-2">
													  <input type="number" class="form-control" id="rate_tolerance" name="rate_tolerance[]" placeholder="Rate Tolerance" value="'.$rate_tolerance.'" required>
													  </div>
													  <div class="col-md-2">
													  <input type="number" class="form-control" id="discount_percentage" name="discount_percentage[]" placeholder="Discount Percentage" value="'.$discount_percentage.'" required>
													  </div>
												  </div>							 
											  </div>
										</div>';
									}
								echo '</div></section>';

					}else{
						echo '<section class="panel" style="height:350px; overflow:auto;overflow-x: hidden;">
                     			<div class="panel-body bio-graph-info">
                     			<h1>Assign Vendor</h1>';
                     	echo   '<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
								   			 <label for="Product Type" style="text-align:left" class="col-md-6 control-label">Vendor Name</label>
								   			 <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Vendor Rate</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Rate Tolerance (%)</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Discount Percentage (%)</label>
										      </div>
								   		</div>
								   	</div>
								</div>';		
                     	echo 	'<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
									      <label for="Product Type" style="text-align:left" class="col-md-6 control-label">
									         <select class="select2 selproduct" title="Select product" name="party_product" id="party_product"" >
									            <option value="">Choose Product</option>
									            '.getcust($dbcon,'').'
									         </select>
									      </label>
									      <div class="col-md-5 col-xs-11">
									         <input type="number" class="form-control" id="party_rate" name="party_rate" placeholder="Party Rate" value="0.00">
									      </div>
									      <div class="col-md-1">
									      <input type="button" name="addprice" id="addprice" onclick="return add_price();" class="btn btn-primary" value="Add">
									  </div>
								   </div>
								   <div>
								   </div>
								</div>
							</div>';		
						echo '</div></section>';

					}
				}
			}
			// Edit record
			else{
				// vendor wise selection
				if($type=='0'){
					$sql = "SELECT pct.*,`pm`.`product_name` FROM `tbl_purchasecardtrn` as pct left join  product_mst as pm ON `pct`.`product_id` = `pm`.`product_id` where `pct`.`purchasecard_id` =  '".$eid."'";
					$result=$dbcon->query($sql);
					
					if(mysqli_num_rows($result)>0)
					{
						$i=0;
						echo '<section class="panel" style="height:350px; overflow:auto;overflow-x: hidden;">
                     			<div class="panel-body bio-graph-info">
                     			<h1>Item List</h1>';
                     	echo   '<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
								   			 <label for="Product Type" style="text-align:left" class="col-md-6 control-label">Item Name</label>
								   			 <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Item Rate</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Rate Tolerance (%)</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Discount Percentage (%)</label>
										      </div>
								   		</div>
								   	</div>
								</div>';		
									while($rel=mysqli_fetch_assoc($result))
									{
										echo '<div class="col-md-12 margin_row">
											  <div class="col-md-8">
												  <div class="form-group">
													  <label for="Product Type" style="text-align:left" class="col-md-6 control-label">'.$rel['product_name'].'</label>
													  <input type="hidden" name="product_id_list[]" value="'.$rel['product_id'].'">
													  <div class="col-md-2">
													  <input type="text" class="form-control" id="product_rate_list" name="product_rate_list[]" placeholder="Price" value="'.$rel['price'].'" readonly>
													  </div>
													  <div class="col-md-2">
													  <input type="text" class="form-control" id="rate_tolerance" name="rate_tolerance[]" placeholder="Rate Tolerance" value="'.$rel['rate_tolerance'].'" readonly>
													  </div>
													  <div class="col-md-2">
													  <input type="text" class="form-control" id="discount_percentage" name="discount_percentage[]" placeholder="Discount Percentage" value="'.$rel['discount_percentage'].'" readonly>
													  </div>
												  </div>							 
											  </div>
										</div>';
									}
								echo '</div></section>';

					}else{
						echo "Data not found. Please choose other vendor.";

					}

				}
				// item wise selection
				elseif($type=='1'){
					$sql = "SELECT pct.* , `l`.`l_name` FROM `tbl_purchasecardtrn` as pct left join  tbl_ledger as l ON `pct`.`vendor_id` = `l`.`l_id` where `pct`.`purchasecard_id` = '".$eid."'";
					$result=$dbcon->query($sql);

					if(mysqli_num_rows($result)>0)
					{
						$i=0;
						echo '<section class="panel" style="height:350px; overflow:auto;overflow-x: hidden;">
                     			<div class="panel-body bio-graph-info">
                     			<h1>Vendor List</h1>';
                     	echo   '<div class="col-md-12 margin_row">
									<div class="col-md-8">
								   		<div class="form-group">
								   			 <label for="Product Type" style="text-align:left" class="col-md-6 control-label">Vendor Name</label>
								   			 <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Vendor Rate</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Rate Tolerance (%)</label>
										      </div>
										      <div class="col-md-2">
										         <label for="Product Type" style="text-align:left" class="control-label">Discount Percentage (%)</label>
										      </div>
								   		</div>
								   	</div>
								</div>';		
									while($rel=mysqli_fetch_assoc($result))
									{
										
										echo '<div class="col-md-12 margin_row">
											  <div class="col-md-8">
												  <div class="form-group">
													  <label for="Product Type" style="text-align:left" class="col-md-6 control-label">'.$rel['l_name'].'</label>
													  <input type="hidden" name="vendor_id_list[]" value="'.$rel['vendor_id'].'">
													  <div class="col-md-2">
													  <input type="text" class="form-control" id="product_rate_list" name="product_rate_list[]" placeholder="Price" value="'.$rel['price'].'" readonly>
													  </div>
													  <div class="col-md-2">
													  <input type="text" class="form-control" id="rate_tolerance" name="rate_tolerance[]" placeholder="Rate Tolerance" value="'.$rel['rate_tolerance'].'" readonly>
													  </div>
													  <div class="col-md-2">
													  <input type="text" class="form-control" id="discount_percentage" name="discount_percentage[]" placeholder="Discount Percentage" value="'.$rel['discount_percentage'].'" readonly>
													  </div>
												  </div>							 
											  </div>
										</div>';
									}
								echo '</div></section>';

					}else{
						echo "Data not found. Please choose other product.";	
					}
				}
			}
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

		else if(brp_strtolower($POST['mode'])== "get_insert_tags_data"){
			$array = array(
						["[contact_name]", "Name"], 
						["[contact_email]", "Email"],
						["[contact_user_name]", "User Name"],
						["[contact_first_name]", "First Name"],
						["[contact_last_name]", "Last Name"]
					);

			echo json_encode($array);
		}	
    }
}




?>