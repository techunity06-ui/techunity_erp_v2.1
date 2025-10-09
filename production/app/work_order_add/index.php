<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$company_config = getCompanyConfiguration($dbcon);	
//print_r($_POST);
// error_reporting(E_ALL);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(brp_strtolower($POST['mode'])=="load_product")
{
	$type_id=$POST['type_id'];
	echo getrequiredproduct($dbcon,'',' and p.product_type='.$type_id.'');
}

else if(brp_strtolower($POST['mode']) == "save_work_order_product") {
	$getspecialConfiguration=getspecialConfiguration($dbcon);

	$jet_techno_perm = $getspecialConfiguration['jet_technologies_permission'];

	$product_id = $POST['product_id'];
	$branch_id = $POST['branch_id'];
	$extra_stock = $POST['extra_stock'];
	$ext_stock_vendor_id = $POST['ext_stock_vendor_id'];
	$priority_status = $POST['priority_status'];

	$bom_qry1="SELECT * FROM `tbl_bom` WHERE  bom_version_id='".$POST['bom_version_id']."' AND bom_product='".$product_id."'";
	$bom_res1=brp_mysqli_fetch_assoc($dbcon->query($bom_qry1));
	// var_dump($bom_qry1);
	$bom_id = $bom_res1['bom_id'];
	$bom_no = $bom_res1['bom_no'];
	$query1="select * from  tbl_invoicetype where type_id='9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
	$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
	
	$new_query1="update tbl_invoicetype set taxinvoice_start = ".$id." where type_id='9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
	$dbcon->query($new_query1);				
	
	if($rows['invoice_format']=='2')
	{
		$info1['po_req_no']	 = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
	}
	else if($rows['invoice_format']=='1')
	{
		$info1['po_req_no']	 = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
	}
	else if($rows['invoice_format']=='3'){
		$info1['po_req_no']	 = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
	}
	else{
		$info1['po_req_no']	 = str_pad($id,3,"0",STR_PAD_LEFT);
	}
	
	$info1['po_req_date']	= date("Y-m-d");
	$info1['rp_req_qty']	= $POST['qty'];
	$info1['in_process_qty_main']	= $POST['qty'];
	$info1['rp_po_qty']	= '0';
	$info1['product_id']		= $product_id;
	$info1['cdate'] 			= date("Y-m-d");
	$info1['mdate'] 			= date("Y-m-d");
	$info1['adata'] 			= date("Y-m-d");
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['muser_id']			= $_SESSION['user_id'];
	$info1['auser_is']			= $_SESSION['user_id'];
	$info1['vendor_id']			= '';
	$info1['bom_id']			= $bom_id;
	$info1['bom_no']			= $bom_no;
	$info1['sales_order_no']			= '';
	$info1['sales_order_date']			= strtotime(date("Y-m-d"));
	$info1['po_no']						= '';
	$info1['po_date']					= '';	
	$info1['sp_status']					= '';	
	$info1['branch_id']					= $POST['branch_id'];		
	$info1['company_id']				= $_SESSION['company_id'];			
	$info1['sales_order_trn_id']		='';
	$info1['bom_version_id']		= $POST['bom_version_id'];
	$info1['extra_stock']		= $extra_stock;
	$info1['ext_stock_vendor_id']		= $ext_stock_vendor_id;
	$info1['priority_status']		= $priority_status;
	$table='tbl_set_main_process';	
	
	$inserid=add_record($table, $info1, $dbcon);
	
	if( $POST['bom_version_id'] != '10000')
	{
				
	}	
	if($inserid)
	{
		$work_order_no = $info1['po_req_no'];
		$work_order_date = $info1['po_req_date'];
		$pro_qry = "select * from  product_mst where product_id = " . $_POST['product_id']; 
		$pro_rs=$dbcon->query($pro_qry);
		$pro_row = brp_mysqli_fetch_array($pro_rs);
		
		$info2['sp_id']					= $inserid;
		$info2['work_order_no']			= $work_order_no;
		$info2['work_order_date']		= $work_order_date;
		$info2['sr_no']					= 0;
		$info2['rp_req_no']				= '';
		$info2['rp_req_date']			= date("Y-m-d");
		$info2['rp_pid']				= $_POST['product_id'];
		$info2['rp_req_qty']			= $POST['qty'];
		$info2['req_qty_one']			= 1;
		$info2['rp_po_qty']				= 0;
		$info2['in_process_qty']		= 0;
		$info2['out_process_qty']		= '';
		$info2['rp_req_type']			= 'direct';
		$info2['rp_po_req_no']			= '';
		$info2['rp_process_req_no']		= '';
		$info2['cdate']					= date("Y-m-d H:i:s");
		$info2['user_id']				= $_SESSION['user_id'];		
		$info2['company_id']			= $_SESSION['company_id'];	
		$info2['status']				= 0;	
		$info2['row_cnt']				= 0;	
		$info2['process_unit']			= $pro_row['product_base_unit'];	
		$info2['purchase_unit']			= $pro_row['product_conv_unit'];	
		$info2['reserve_status']		= 0;
		$info2['used_rp_req_qty']		= '';
		$info2['used_status']			= 0;
		$info2['perent_id']				= 0;	
		$info2['reserve_stock']			= '';	
		$info2['main_request']			= 1;
		$info2['indent_no']				= '';	
		$info2['indent_date']			= '';
		$info2['indent_status']			= '';		
		$info2['job_card_no']			= '';
		$info2['job_card_date']			= '';		
		$info2['job_card_status']		= '';
		$info2['reject_status']			= 0;
		$info2['sales_order_trn_id']	= 0;
		$info2['branch_id']				= $branch_id;
		$info2['finish_used_qty']		= '';
		$info2['finish_status']			= 0;
		$info2['product_version']		= '';	
		$info2['pre_trn_id']			= 0;	
		$info2['shortclose_qty']		= 0;
		$info2['shortclose_remark']		= '';
		$info2['approval_status'] = '1';
		$info2['extra_stock']		= $extra_stock;
		$info2['ext_stock_vendor_id']		= $ext_stock_vendor_id;
		$info2['bom_id']			= $bom_id;
		$info2['product_remark']	= $pro_row['product_desc'];
		$info2['priority_status']	= $priority_status;
		
		$table='tbl_request_product';	
		$reqinserid=add_record($table, $info2, $dbcon);
		$pqty = $POST['qty'];
		if($POST['bom_version_id'] != "10000")
		{
		
			$workorder_query_pro="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE bom.bom_status =  0 and tbl_product_process.status=0 and pro_bom_process.process_status = 0 and prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$product_id."' and bom.bom_id =" .$bom_id; 

			$workorder_query_result = $dbcon->query($workorder_query_pro);
			
			if(brp_mysqli_num_rows($workorder_query_result)>0){
				while($wproduct_process=brp_mysqli_fetch_assoc($workorder_query_result))
				{
					$wwpp_info['product_id'] = $product_id;		
					$wwpp_info['rp_id'] = 	$reqinserid;
					$wwpp_info['process_priority'] = 	$wproduct_process['priority'];
					$wwpp_info['process_time'] = 	$wproduct_process['process_time'];
					$wwpp_info['process_type'] = 	$wproduct_process['process_type'];
					$wwpp_info['process_opening'] = 	$wproduct_process['process_opening'];
					$wwpp_info['process_id'] = 	$wproduct_process['process_id'];	
					$wwpp_info['cdate']				= date("Y-m-d H:i:s");
					$wwpp_info['user_id']			= $_SESSION['user_id'];
					$wwpp_info['company_id']			= $_SESSION['company_id'];
					$wwpp_info['branch_id']			= $POST['branch_id'];
					$wpp_info['description']			= $wproduct_process['description'];
					//echo "<pre>"; print_r($wwpp_info);
					$inserestimateid=add_record('tbl_wororder_product_process', $wwpp_info, $dbcon);
				}
			}		
			
			$bom_process="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			WHERE bom.bom_status =  0 and  prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$product_id."'";
			$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));
			
			$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.reorder_qty,pro.product_desc from tbl_bomtrn as bom_trn 
			left join product_mst as pro on pro.product_id=bom_trn.product_id
			left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
			left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
			where bom_trn.bom_trn_status=0 and bom_trn.bom_id=".$bom_rel['bom_id'];	
			$result1=$dbcon->query($query1);
			$call=1;$space="";
			$i = 1;
							//$rel1=brp_mysqli_fetch_assoc($result1);
							//echo "<pre>"; print_r($rel1);

			while($rel1=brp_mysqli_fetch_assoc($result1)){  
				
				$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
				$conv_one_qty=$rel1['product_conv_qty']/$bom_rel['product_conv_qty'];
				// $conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");
				
				if($jet_techno_perm == '1' && $rel1['conversation_factor'] == '0'){
					$base_qty=$rel1['product_base_qty'];

				}else{
					$base_qty=$base_one_qty*$info2['rp_req_qty'];
				}

				
				$reorder_qty = 0;
						
				/*if(!empty($rel1['reorder_qty']) && $rel1['reorder_qty'] > 0){
					$reorder_qty = $rel1['reorder_qty'];		
				   $chk_qty = 	ceil($base_qty  / $reorder_qty);
				   $base_qty = 	$reorder_qty * $chk_qty;
				}	*/
				$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");
				
						/*$base_one_qty=$rel1['product_base_qty'];
						$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");


						$base_qty=$base_one_qty*$rel1['product_base_qty'];
						$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/
						

						$info_sub['sp_id']				= $inserid;
						$info_sub['sr_no']				= $i;
						$info_sub['rp_pid']				= $rel1['product_id'];
						$info_sub['rp_req_date']		= date("Y-m-d");
						

						//$info_sub['rp_req_qty']			= $POST['qty']*$conv_one_qty;
						//$info_sub['req_qty_one']		= $conv_one_qty;//required qty
						

						
						$info_sub['rp_req_qty']			= $base_qty;//required qty
						$info_sub['req_qty_one']		= $base_one_qty;//required qty

						/*$info_sub['rp_req_qty']		= $POST['qty']*$base_one_qty;
						$info_sub['req_qty_one']		= $base_one_qty;//required qty*/
						$info_sub['rp_po_qty']			= "";//po qty
						$info_sub['in_process_qty']		= 0;//process qty
						$info_sub['rp_req_type']		= "work_order";//type
						$info_sub['process_unit']		= $rel1['product_base_unit'];
						$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub['perent_id']			= $reqinserid;
						$info_sub['status']				= 3;
						$info_sub['cdate']					= date("Y-m-d H:i:s");
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						//$info_sub['main_request']		= $POST['g_total'];
						
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
						$info_sub['approval_status'] = '1';
						$info_sub['extra_stock']		= $extra_stock;
						$info_sub['ext_stock_vendor_id']		= $ext_stock_vendor_id;
						$info_sub['product_remark']	= $rel1['product_desc'];
						$info_sub['priority_status']		= $priority_status;
						//echo "<pre>"; print_r($info_sub);die;
						$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
						//echo "jayesh".$inserid_sub."test";
						/*   Material Formula */
						$material_query="select * from tbl_bom_material_trn where bom_trn_id=".$rel1['bom_trn_id']." AND bom_id =".$rel1['bom_id']; 	
						$material_result=$dbcon->query($material_query);
						if(brp_mysqli_num_rows($material_result) > 0)
						{
							while($mat_rel=brp_mysqli_fetch_assoc($material_result))
							{ 
								$mat_data['sp_id'] = $inserid; 
								$mat_data['rp_id'] = $inserid_sub; 
								$mat_data['product_id'] = $rel1['product_id']; 
								$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id']; 
								$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value']; 
								$mat_data['wo_material_trn_status'] = 0; 
								$mat_data['user_id']			= $_SESSION['user_id'];
								$mat_data['company_id']			= $_SESSION['company_id'];
								$mat_data['branch_id']			= $_SESSION['branch_id'];
								$inserid_wo_sub=add_record('tbl_wo_material_trn', $mat_data, $dbcon,$POST['branch_id']);
								
							}
						}
						// var_dump($rel1['p_bom_id']);
						
					$query_pro1="SELECT * FROM `tbl_bom` as bom
						left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
						left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
						left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
						WHERE bom.bom_status = 0 and tbl_product_process.status=0 and bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id =" . $rel1['p_bom_id']; 
						
						$rel_pro1 = $dbcon->query($query_pro1);
						
						if(brp_mysqli_num_rows($rel_pro1)>0)
						{
							while($product_process_row=brp_mysqli_fetch_assoc($rel_pro1))
							{
								$wpp_info['product_id'] = $rel1['product_id'];		
								$wpp_info['rp_id'] = 	$inserid_sub;
								$wpp_info['process_priority'] = 	$product_process_row['priority'];
								$wpp_info['process_time'] = 	$product_process_row['process_time'];
								$wpp_info['process_type'] = 	$product_process_row['process_type'];
								$wpp_info['process_opening'] = 	$product_process_row['process_opening'];
								$wpp_info['process_id'] = 	$product_process_row['process_id'];	
								$wpp_info['cdate']				= date("Y-m-d H:i:s");
								$wpp_info['user_id']			= $_SESSION['user_id'];
								$wpp_info['company_id']			= $_SESSION['company_id'];
								$wpp_info['branch_id']			= $POST['branch_id'];
								$wpp_info['description']			= $product_process_row['description'];
								
								$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
							}
						}	
						
						bom_child_tree($dbcon,$rel1['p_bom_id'],$inserid,$inserid_sub,$i,$base_qty,$POST['bom_version_id'],$POST['branch_id'],$extra_stock,$ext_stock_vendor_id,$priority_status,$jet_techno_perm,$info2['rp_req_qty'],$bom_rel['product_base_qty']);
						
						$i++;
					}	
				}



				$work_order_id = $inserid;
				/*$info_wo['sp_status']=2;
		$updateid12=update_record("tbl_set_main_process", $info_wo,"sp_id=".$work_order_id , $dbcon);
		*/
		
		$bom_q1="SELECT rp_id,rp_pid FROM `tbl_request_product` WHERE main_request=1 and sp_id=".$work_order_id;
		$bom_rel_q1=brp_mysqli_fetch_assoc($dbcon->query($bom_q1));
		
		$query="select * from tbl_request_product as i
		where i.rp_id=".$bom_rel_q1['rp_id'];
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);

		$info['rp_req_date']		=date('Y-m-d');
		$info['rp_req_qty']			=$POST['qty'];
		$info['rp_po_qty']			=0;
		$info['in_process_qty']		=$POST['qty'];
		
		$info['in_process_conv_qty']	=  convert_stock_new($dbcon,$POST['qty'],$row['rp_pid'],"conv_unit");

		$info['reject_status']		=0;

		$info['status']				=0;
		$info['cdate']				=date('Y-m-d H:i:s');
		$info['user_id']			=$_SESSION['user_id'];
		$info['company_id']			=$_SESSION['company_id'];

		if($info['rp_po_qty']>"0"){
			$indent_no=load_common_no($dbcon,17);
			update_common_no($dbcon,17);
			$info['indent_status']		= 1;
			$info['indent_no']			= $indent_no;
			$info['indent_date']		= date('Y-m-d');
		}
		if($info['in_process_qty']>"0"){
			$indent_no=load_common_no($dbcon,40);
			update_common_no($dbcon,40);
			$info['job_card_status']		= 1;
			$info['job_card_no']			= $indent_no;
			$info['job_card_date']		= date('Y-m-d');
		}
		/*if(!empty($POST['sales_order_trn_id'])){
			$info['sales_order_trn_id']		= $POST['sales_order_trn_id'];
		}*/
		$updateid=update_record("tbl_request_product", $info,"rp_id=".$bom_rel_q1['rp_id'] , $dbcon);

		$set_pro="SELECT product_base_unit,product_conv_unit,product_base_qty,product_conv_qty,product_id,batch_wise_stock_manage FROM `product_mst` WHERE product_status=0 AND product_id='".$bom_rel_q1['rp_pid']."'";
	$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));

	//indnet wip stock add start
		if($info['rp_po_qty']>0){
		if($setpro_rel['product_conv_unit']==$row['purchase_unit']){
				$type="base_unit";
				$con_stock=$info['rp_po_qty'];
				$base_stock=convert_stock_new($dbcon,$info['rp_po_qty'],$bom_rel_q1['rp_pid'],$type);
			}else{
				$type="conv_unit";
				$base_stock=$info['rp_po_qty'];
				$con_stock=convert_stock_new($dbcon,$info['rp_po_qty'],$bom_rel_q1['rp_pid'],$type);
			}

			$info_wip_add['rp_id']					= $bom_rel_q1['rp_id'];
			$info_wip_add['type_flag']				= 3;
			$info_wip_add['po_trn_id']				= 0;
			$info_wip_add['sales_order_trn_id']		= 0;
			//$info_wip_add['allocate_for_rp_id']		= 0;
			//$info_wip_add['allocate_table_id']		= $POST['sales_order_trn_id'];
			$info_wip_add['allocate_base_qty']		= $base_stock;
			$info_wip_add['allocate_base_unit']		= $setpro_rel['product_base_unit'];
			$info_wip_add['allocate_conv_qty']		= $con_stock;
			$info_wip_add['allocate_conv_unit']		= $setpro_rel['product_conv_unit'];
			$info_wip_add['stock_flag']				= 1;
			$info_wip_add['cdate']					= date("Y-m-d H:i:s");
			$info_wip_add['user_id']				= $_SESSION['user_id'];
			$info_wip_add['company_id']				= $_SESSION['company_id'];

			$inser_wip_add=add_record('wip_stock_allocate', $info_wip_add, $dbcon,$row['branch_id']);

		}
		//indnet wip stock add end

		// jobcard wip stock add
		if($info['in_process_qty']>0){
			if($setpro_rel['product_conv_unit']==$row['process_unit']){
				$type="base_unit";
				$con_stock1=$info['in_process_qty'];
				$base_stock1=convert_stock_new($dbcon,$info['in_process_qty'],$bom_rel_q1['rp_pid'],$type);
			}else{
				$type="conv_unit";
				$base_stock1=$info['in_process_qty'];
				$con_stock1=convert_stock_new($dbcon,$info['in_process_qty'],$bom_rel_q1['rp_pid'],$type);
			}

			$info_wip_add1['rp_id']					= $bom_rel_q1['rp_id'];
			$info_wip_add1['type_flag']				= 3;
			$info_wip_add1['po_trn_id']				= 0;
			$info_wip_add1['sales_order_trn_id']		= 0;
			//$info_wip_add1['allocate_for_rp_id']		= 0;
			//$info_wip_add1['allocate_table_id']		= $POST['sales_order_trn_id'];
			$info_wip_add1['allocate_base_qty']		= $base_stock1;
			$info_wip_add1['allocate_base_unit']		= $setpro_rel['product_base_unit'];
			$info_wip_add1['allocate_conv_qty']		= $con_stock1;
			$info_wip_add1['allocate_conv_unit']		= $setpro_rel['product_conv_unit'];
			$info_wip_add1['stock_flag']				= 1;
			$info_wip_add1['cdate']					= date("Y-m-d H:i:s");
			$info_wip_add1['user_id']				= $_SESSION['user_id'];
			$info_wip_add1['company_id']				= $_SESSION['company_id'];

			$inser_wip_add1=add_record('wip_stock_allocate', $info_wip_add1, $dbcon,$row['branch_id']);

		}
		// jobcard wip stock end
		if($POST['qty']!='')
		{
			if($POST['qty']!="0"){
				
				$queryw_b="select * from pro_bom_process where process_status=0 and bom_id=".$row['bom_id'];
				$rs_custw_b=$dbcon->query($queryw_b);	
				while($relw_b=brp_mysqli_fetch_array($rs_custw_b)){

					$queryw="select * from tbl_product_process where  status=0 and pr_process_id=".$relw_b['pr_process_id'];
					$rs_custw=$dbcon->query($queryw);	
					$relw=brp_mysqli_fetch_array($rs_custw);
					$infow['product_id']		= $relw['product_id'];
					$infow['rp_id']				= $row['rp_id'];
					$infow['process_priority']	= $relw_b['priority'];;
					$infow['process_time']		= $relw['process_time'];
					$infow['process_type']		= $relw['process_type'];
					$infow['process_opening']	= $relw['process_opening'];
					$infow['process_id']		= $relw['process_id'];
					$infow['cdate']				= date('Y-m-d');
					$infow['user_id']			= $_SESSION['user_id'];
					$infow['company_id']		= $_SESSION['company_id'];

					// $inserid_a2=add_record('tbl_wororder_product_process', $infow, $dbcon, $POST['branch_id']);

						/*
						Code By Umair : 05/11/2020
						Comment: Insert Work Order Resource Allocation In tbl_work_order_resource_allocate table
						*/
						if($relw['process_type']=='1'){
							$resource_id = $relw['resource_id'];
							$request_id = $row['rp_id']; 
							$process_id = $relw['process_id'];
							$product_id = $relw['product_id'];
							$qty = $POST['qty'];
							$time_per_qty = $relw['process_time'];
							
							$action_type = 'add';
							$edit_id = '';
							work_order_resource_allocate($dbcon, $resource_id, $request_id, $process_id, $product_id, $qty, $time_per_qty, $edit_id, $action_type, '', $POST['branch_id']);
							
							

						}
						
					}
				}
			}
			if($POST['qty']!='')
			{
				if($POST['qty']!="0"){
					$process=get_product_process($dbcon,$row['rp_id'],$row['rp_pid']);
					$process_pr=json_decode($process);
					
					$process_id=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;

					/*Get Resource ID*/
					$resourceinfo=get_resource_from_product_process($dbcon,$row['rp_pid'],$process_id, $where=null);

					$info5['process_id']		= $process_id;			
					$info5['p_start_time']		= '';		
					$info5['p_end_time']		= '';		
					$info5['p_qty']				= $POST['qty'];		
					$info5['pen_qty']			= $POST['qty'];		
					$info5['process_unit']		= $row['process_unit'];		
					$info5['p_ref_id']			= $row['rp_id'];		
					$info5['p_ref_type']		= 'process request';		
					$info5['p_product_id']		= $row['rp_pid'];		
					$info5['pr_process_type']	= $process_type;		
					$info5['process_priority']	= $process_priority;		
					$info5['previous_process_id']= 0;
					$info5['product_version']	= $row['product_version'];
					$info5['extra_stock'] = $extra_stock;
					$info5['ext_stock_vendor_id'] = $ext_stock_vendor_id;

					if($resourceinfo['process_type']=='1'){		
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}	

					
					if($company_config['batch_wise_stock'] == '1' &&  $company_config['batch_process'] == '0' && $setpro_rel['batch_wise_stock_manage'] == '1'){
						$info5['batch_process_start_time'] = 1;
					}

					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					
					$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon,$POST['branch_id']);

					$query_reserve="select * from tbl_request_product where status=0 and perent_id=".$row['rp_id'];
						$rs_reserve=$dbcon->query($query_reserve);	
						while($rel_reserve=brp_mysqli_fetch_array($rs_reserve)){

							$query_resu1 = $dbcon->query("UPDATE tbl_reserve_stock SET p_id =".$inserid_alloc." WHERE p_id=0 and request_id =".$rel_reserve['rp_id']);

						}
				}
			}
			//var_dump($info['rp_req_qty']);
			//var_dump($bom_rel_q1['rp_id']);
			
				//echo $bom_rel_q1['rp_id'];
				//echo "222";

			/*if($POST['smode']=="add_all"){
				$all_request_data_use=all_request_data_use($dbcon,$bom_rel_q1['rp_id'],$info['rp_po_qty']);
			}*/
			/* if(!empty($POST['sales_order_trn_id'])){
				$query_invoicetype = $dbcon->query("UPDATE tbl_sales_ordertrn SET work_order_qty = work_order_qty +".$info['in_process_qty_main']." WHERE sales_ordertrn_id = ".$POST['sales_order_trn_id']);
			} */
							
			}
			
			$row['res']="1";
			$row['sp_id']=$inserid;
			
			
			echo json_encode($row);
			
		}
		
		else if(brp_strtolower($POST['mode']) == "check_bom_version_by_product") {
			$branch_where="";
			$str='';
		/*if(!empty($POST['branch_id'])){
			$branch_where=" and branch_id=".$POST['branch_id'];
		}*/
		
		$company_where="";
		/*if(!empty($_SESSION['company_id'])){
			$company_where=" and company_id=".$_SESSION['company_id'];
		}*/
		
		$qry="SELECT * from pro_ms_bom_version where  bom_version_status = 0 and  product_id=".$POST['product_id']." ".$company_where." ".$branch_where;
		$result=$dbcon->query($qry);
		
		if(brp_mysqli_num_rows($result) > 0)
		{	
			while($row=brp_mysqli_fetch_assoc($result))
			{
				$str .= '<option value="'.$row['bom_version_id'].'">'.$row['version_name'].'</option>';
			}
			$str .= '<option  value="10000">R&D</option>';
			echo $str;
		}
		else
		{
			echo "0";
		}
		
	}else if(brp_strtolower($POST['mode']) == "get_reorder_qty") {
		$product_id = $POST['product_id'];
		$query_m="select reorder_qty from product_mst  where product_id=".$product_id;
		$result_m=$dbcon->query($query_m);
		$rel_m=mysqli_fetch_assoc($result_m);	

		echo $rel_m['reorder_qty'];
	}

	function bom_child_tree($dbcon,$bom_id,$sp_id,$rp_parent_id,$num,$qty,$bom_version_id,$branch_id,$extra_stock=0,$ext_stock_vendor_id=0,$priority_status,$jet_techno_perm,$req_qty,$bom_qty)
	{
		
		$query_m="select * from tbl_bom as bom where bom_status=0 and bom_id=".$bom_id;
		$result_m=$dbcon->query($query_m);
		$rel_m=mysqli_fetch_assoc($result_m);	
		
		
		$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name, cunit.unit_name as conv_unit_name, bom_trn.product_id, pro.reorder_qty,pro.product_desc from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=".$bom_id;	
		$result1=$dbcon->query($query1);
		
		
		$k=1;
		$call=1;$space="";
		while($rel1=brp_mysqli_fetch_assoc($result1)){ 
			
			$sr_no = $num.'.'.$k; 
			// echo $sr_no . " -->> " . $qty . "</br></br>";

			$base_one_qty=$rel1['product_base_qty']/$rel_m['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

			if($jet_techno_perm == "1" && $rel1['conversation_factor'] == '1'){
				$base_qty=($req_qty * $rel1['product_base_qty'])/$bom_qty;
			}else{
				$base_qty=$base_one_qty*$qty;
			}

			$reorder_qty = 0;
						
				/*if(!empty($rel1['reorder_qty']) && $rel1['reorder_qty'] > 0){
					$reorder_qty = $rel1['reorder_qty'];		
				   $chk_qty = 	ceil($base_qty  / $reorder_qty);
				   $base_qty = 	$reorder_qty * $chk_qty;
				}*/	
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");
			
			$info_sub['sp_id']				= $sp_id;
			$info_sub['sr_no']				= $sr_no;
			$info_sub['rp_pid']				= $rel1['product_id'];
			$info_sub['rp_req_qty']			= $base_qty;//required qty
			$info_sub['req_qty_one']		= $base_one_qty;//required qty
			$info_sub['rp_req_date']		= date("Y-m-d");
			$info_sub['rp_po_qty']			= "";//po qty
			$info_sub['in_process_qty']		= 0;//process qty
			$info_sub['rp_req_type']		= "work_order";//type
			$info_sub['process_unit']		= $rel1['product_base_unit'];
			$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
			$info_sub['perent_id']			= $rp_parent_id;
			$info_sub['status']				= 3;
			$info_sub['cdate']				= date("Y-m-d H:i:s");
			$info_sub['user_id']			= $_SESSION['user_id'];
			$info_sub['company_id']			= $_SESSION['company_id'];
			$info_sub['product_version']	= $rel1['p_bom_id'];
			$info_sub['bom_id']				= $rel1['p_bom_id'];			
			$info_sub['approval_status'] = '1';
			$info_sub['extra_stock'] = $extra_stock;
			$info_sub['ext_stock_vendor_id'] = $ext_stock_vendor_id;
			$info_sub['product_remark'] = $rel1['product_desc'];
			$info_sub['priority_status'] = $priority_status;
			
			
			//echo "<pre>"; print_r($info_sub); die;
			
			$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$branch_id);
			
		$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status=0 and  bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id = " . $rel1['p_bom_id']; 
			
			$rel_pro1 = $dbcon->query($query_pro1);
			
			if(brp_mysqli_num_rows($rel_pro1)>0)
			{
				while($product_process1=brp_mysqli_fetch_assoc($rel_pro1))
				{
					$wpp_info['product_id'] = $rel1['product_id']; 	
					$wpp_info['rp_id'] = 	$inserid_sub;
					$wpp_info['process_priority'] = 	$product_process1['priority'];
					$wpp_info['process_time'] = 	$product_process1['process_time'];
					$wpp_info['process_type'] = 	$product_process1['process_type'];
					$wpp_info['process_opening'] = 	$product_process1['pr_process_id'];
					$wpp_info['process_id'] = 	    $product_process1['process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $branch_id;
					$wpp_info['description']			= $product_process1['description'];
					
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
				}
			}
			bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$sr_no,$base_qty,$bom_version_id,$branch_id,$extra_stock,$ext_stock_vendor_id,$priority_status,$jet_techno_perm,$req_qty,$bom_qty);
			$k++;	
		}
		
	}
?>