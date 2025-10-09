<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
$company_config = getCompanyConfiguration($dbcon);	
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(brp_strtolower($POST['mode']) == "fetch") {
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$branch_id = $POST['branch_id'];
			$wher = '';
			if($_SESSION['user_type']!=2){
			$wher=" and `rp`.`user_id`='".$_SESSION['user_id']."'";
			}
			if(!empty($branch_id)){
				$wher .= " and rp.branch_id = " . $branch_id;
			}
		
		  $appData = array();
		  $i=1;
	
		  $aColumns = array('p.product_name','bv.version_name','unit.unit_name','br.branch_name','rp.status','rp.company_id','rp.rp_pid','p.product_base_unit','bom.bom_version_id','sp.branch_id','group_concat(rp.rp_id) as rp_id','sum(rp.rp_req_qty) as shortage_qty','sum(rp.shortage_complete_qty) as shortage_complete_qty','group_concat(sp.sales_order_trn_id) as sales_ordertrn_id');
		  $sIndexColumn = "rp.rp_id";
		  $isWhere = array("rp.status = 3 and rp.workorder_type = 1 and rp.company_id='".$_SESSION['company_id']."'".$wher." and rp.finish_status = 0");
		  $sTable = "tbl_request_product as rp";			
		  $isJOIN = array('left join tbl_bom as bom ON bom.bom_id = rp.bom_id',
		  	'left join pro_ms_bom_version as bv ON bv.bom_version_id = bom.bom_version_id',
		  	'left join tbl_set_main_process as sp ON sp.sp_id = rp.sp_id',
		  	'left join product_mst as p ON rp.rp_pid = p.product_id',
		  	'left join unit_mst as unit ON unit.unitid = p.product_base_unit',
		  	'left join branch_mst as br ON br.branch_id = rp.branch_id'
		  );
		  $hOrder = "rp.rp_id";
		$hGroupby = array("rp.rp_pid","rp.bom_id","rp.branch_id");
		  /*END*/
		include($include."pagging.php");
		 
		  $id=1;
		  foreach($sqlReturn as $row) {
		  	$row_data = array();
		  	$shortage_qty = $row['shortage_qty'] - $row['shortage_complete_qty'];
				$cstock=get_current_stock_new($dbcon,$row["rp_pid"],$row["product_base_unit"]);
				$rstock=reserve_stock($dbcon,$row["rp_pid"],$row["product_base_unit"]);
				$wipstock=wipstock($dbcon,$row["rp_pid"],$row["product_base_unit"]);
				$actualstock=$cstock-$rstock; 
				$actualstock=$actualstock+$wipstock;

				if($shortage_qty>=$actualstock){
					$validateqty=$actualstock;
				}else{
					$validateqty=$shortage_qty;
				}
		  		$row_data[] = $id;
		  		$row_data[] = $row['product_name'];
		  		$row_data[] = $row['version_name'];  
		  		$row_data[] = $shortage_qty . ' ' . $row['unit_name'];  
		  		$row_data[] = $actualstock . ' ' . $row['unit_name'];
		  		$row_data[] = $row['branch_name'];

		  		$stock_allocate='';

		  		$has_process = 0;


		  		$bom_version_id = $row['bom_version_id'];
		  		$product_id = $row['rp_pid'];

	  			if(!empty($bom_version_id) && $bom_version_id > 0){
	  				$p_qry = "select * from pro_bom_process where process_status = 0 and bom_version_id = " . $bom_version_id . " and product_id = " . $product_id;
	  				$p_res = $dbcon->query($p_qry);

	  				$has_process = brp_mysqli_num_rows($p_res);
	  			}

		  		/*$btn_indent='<button type="button" class="btn btn-xs btn-primary" data-original-title="Create Workorder" data-toggle="tooltip" data-placement="top" onClick="open_create_workorder_modal('.$row['rp_id'].','.$row["sales_ordertrn_id"].',\''.$shortage_qty.'\','.$row['production_branch_id'].','.$row['cust_id'].')"><i class="fa fa-dot-circle-o"></i> Create Indent</button>';*/
		  		$btn_indent='<button type="button" class="btn btn-xs btn-primary" data-original-title="Create Workorder" data-toggle="tooltip" data-placement="top" onClick="open_create_workorder_modal('. "'". $row['rp_pid']."'".','. "'". $row['rp_id']."'".','. "'". $shortage_qty."'".','. "'". $row['branch_id']."'".','. "'". $row['cust_id']."'".')"><i class="fa fa-dot-circle-o"></i> Create Indent</button>';
		  		$btn_workorder= "";
		  		if($has_process > 0){
			  		$btn_workorder='<button type="button" class="btn btn-xs btn-info" data-original-title="Create Jobcard" data-toggle="tooltip" data-placement="top" onClick="open_create_jobcard_model('. "'". $row['rp_id']."'".','.$row['rp_pid'].','.$row['product_base_unit'].','.$shortage_qty.','.$shortage_qty.','.$row['bom_version_id'].','.$row['branch_id'].')"><i class="fa fa-plus"></i> Create Jobcard</button>';
			  	}
			  	$stock_allocate= "";

		  		if($actualstock>0){
					// $stock_allocate='<button type="button" class="btn btn-xs btn-success" data-original-title="Allocate Stock" data-toggle="tooltip" data-placement="top" onClick="open_stock_allocation_so('. "'". $row['rp_id']."'".','.$validateqty.')">Allocate Stock</button>';
				}

		  		$row_data[] = $btn_indent.' '.$btn_workorder.' '. $stock_allocate;

		  	$appData[] = $row_data; 
		  	$id++;
		  }
		  $output['aaData'] = $appData;
		  echo json_encode( $output );
		}
		else if(brp_strtolower($POST['mode']) == "add") {
		
		}else if(brp_strtolower($POST['mode']) == "create_jobcard") {
				$product_id = $POST['product_id'];
				$bom_version_id = $POST['bom_version_id'];
				$qty = $POST['jobcard_qty'];
				$branch_id = $POST['branch_id'];
			
				$bom_qry1="SELECT * FROM `tbl_bom` WHERE bom_status =0 and  bom_version_id=".$bom_version_id . " and bom_product = " . $product_id;

				$bom_res1=brp_mysqli_fetch_assoc($dbcon->query($bom_qry1));
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
				
				$info1['po_req_date']			= date("Y-m-d");
				$info1['rp_req_qty']			= $qty;
				$info1['in_process_qty_main']	= $qty;
				$info1['rp_po_qty']				= '0';
				$info1['product_id']			= $product_id;
				$info1['cdate'] 				= date("Y-m-d");
				$info1['mdate'] 				= date("Y-m-d");
				$info1['user_id']				= $_SESSION['user_id'];
				$info1['muser_id']				= $_SESSION['user_id'];
				$info1['auser_is']				= $_SESSION['user_id'];
				$info1['adata']					= '';
				$info1['vendor_id']				= '';
				$info1['bom_id']				= $bom_id;
				$info1['bom_no']				= $bom_no;
				$info1['po_no']					= '';
				$info1['po_date']				= '';	
				$info1['sp_status']				= 0;	
				$info1['workorder_type']		= '1';	
				$info1['branch_id']				= $branch_id;		
				$info1['company_id']			= $_SESSION['company_id'];			
				$info1['sales_order_trn_id']	= $so_array[$x];
				$info1['bom_version_id']		= $bom_version_id;
				$table='tbl_set_main_process';	


				
				$inserid=add_record($table, $info1, $dbcon);

				if($inserid)
				{
					$pqty =  $info1['rp_req_qty'];
					$pro_qry = "select * from  product_mst where product_id = " . $_POST['product_id']; 

					$pro_rs=$dbcon->query($pro_qry);
					$pro_row = brp_mysqli_fetch_array($pro_rs);
		
					$info2['sp_id']					= $inserid;
					$info2['sr_no']					= 0;
					$info2['rp_req_no']				= '';
					$info2['rp_req_date']			= date("Y-m-d");
					$info2['rp_pid']				= $info1['product_id'];
					$info2['rp_req_qty']			= $info1['rp_req_qty'];
					$info2['req_qty_one']			= 1;
					$info2['rp_po_qty']				= 0;
					$info2['in_process_qty']		= 0;
					$info2['out_process_qty']		= '';
					$info2['rp_req_type']			= 'work_order';
					$info2['rp_po_req_no']			= '';
					$info2['rp_process_req_no']		= '';
					$info2['cdate']					= strtotime(date("Y-m-d"));
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
					$info2['branch_id']				= $branch_id;
					$info2['finish_used_qty']		= '';
					$info2['finish_status']			= 0;
					$info2['product_version']		= '';	
					$info2['pre_trn_id']			= 0;	
					$info2['shortclose_qty']		= 0;
					$info2['shortclose_remark']		= '';
					$info2['approval_status'] 		= '1';
					$info2['workorder_type']		= '1';	
					$info2['bom_id']				= $bom_id;
					
					
					$table='tbl_request_product';	
					$reqinserid=add_record($table, $info2, $dbcon);
					$arr['rp_id'] = $reqinserid;

					update_workorder_complete_qty_and_Status($dbcon,$POST['rp_id'],$pqty);
					// var_dump($info2);
				
					$workorder_query_pro="SELECT * FROM `tbl_bom` as bom
							left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
							left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
							left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
							WHERE pro_bom_process.process_status = 0 and prover.bom_version_id='".$bom_version_id."' AND bom.bom_product='".$product_id."' and bom.bom_id =" .$bom_id; 

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
									$wwpp_info['branch_id']			= $branch_id;
									$wpp_info['description']			= $wproduct_process['description'];
									//echo "<pre>"; print_r($wwpp_info);
									$inserestimateid=add_record('tbl_wororder_product_process', $wwpp_info, $dbcon);
								}
							}		
			
			$bom_process="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			WHERE  prover.bom_version_id='".$bom_version_id."' AND bom.bom_product='".$product_id."'";
			$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));
			
			$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.reorder_qty from tbl_bomtrn as bom_trn 
			left join product_mst as pro on pro.product_id=bom_trn.product_id
			left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
			left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
			where bom_trn_status=0 and bom_id=".$bom_rel['bom_id'];	
			$result1=$dbcon->query($query1);
			$call=1;$space="";
			$i = 1;
							//$rel1=brp_mysqli_fetch_assoc($result1);
							//echo "<pre>"; print_r($rel1);

			while($rel1=brp_mysqli_fetch_assoc($result1)){  
				
				$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
				$conv_one_qty=$rel1['product_conv_qty']/$bom_rel['product_conv_qty'];
				// $conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

				$base_qty=$base_one_qty*$info2['rp_req_qty'];
				$reorder_qty = 0;
						
				if(!empty($rel1['reorder_qty']) && $rel1['reorder_qty'] > 0){
					$reorder_qty = $rel1['reorder_qty'];		
				   $chk_qty = 	ceil($base_qty  / $reorder_qty);
				   $base_qty = 	$reorder_qty * $chk_qty;
				}	
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
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						//$info_sub['main_request']		= $POST['g_total'];
						
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
						$info_sub['approval_status'] = '1';
						$info_sub['workorder_type']		= '1';	
						
						//echo "<pre>"; print_r($info_sub);die;
						$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$branch_id);
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
								$mat_data['branch_id']			= $branch_id;
								// $inserid_wo_sub=add_record('tbl_wo_material_trn', $mat_data, $dbcon,$branch_id);
								
							}
						}
						// var_dump($rel1['p_bom_id']);
						
					$query_pro1="SELECT * FROM `tbl_bom` as bom
						left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
						left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
						left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
						WHERE bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id =" . $rel1['p_bom_id']; 
						
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
								$wpp_info['branch_id']			= $branch_id;
								$wpp_info['description']			= $product_process_row['description'];
								
								// $inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
							}
						}	
						
						// bom_child_tree($dbcon,$rel1['p_bom_id'],$inserid,$inserid_sub,$i,$base_qty,$bom_version_id,$branch_id);
						
						$i++;
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
		$info['rp_req_qty']			=$info1['rp_req_qty'];
		$info['rp_po_qty']			=0;
		$info['in_process_qty']		=$info1['rp_req_qty'];
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
			$indent_no=load_common_no($dbcon,19);
			update_common_no($dbcon,19);
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

			$inser_wip_add=add_record('wip_stock_allocate', $info_wip_add, $dbcon,$branch_id);

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
			$info_wip_add1['sales_order_trn_id']		= $so_array[$x];
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

			$inser_wip_add1=add_record('wip_stock_allocate', $info_wip_add1, $dbcon,$branch_id);

		}
		// jobcard wip stock end
		if($pqty!='')
		{
			if($pqty!="0"){
				
				$queryw_b="select * from pro_bom_process where process_status=0 and bom_id=".$row['bom_id'];
				$rs_custw_b=$dbcon->query($queryw_b);	
				while($relw_b=brp_mysqli_fetch_array($rs_custw_b)){

					$queryw="select * from tbl_product_process where  pr_process_id=".$relw_b['pr_process_id'];
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
							$qty = $pqty;
							$time_per_qty = $relw['process_time'];
							
							$action_type = 'add';
							$edit_id = '';
							work_order_resource_allocate($dbcon, $resource_id, $request_id, $process_id, $product_id, $qty, $time_per_qty, $edit_id, $action_type, '',$branch_id);
							
							

						}
						
					}
				}
			}
			if($pqty!='')
			{
				if($pqty!="0"){
					
					$process=get_product_process($dbcon,$row['rp_id'],$row['rp_pid']);
					$process_pr=json_decode($process);
					
					$process_id=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;

					/*Get Resource ID*/
					$resourceinfo=get_resource_from_product_process($dbcon,$row['rp_pid'],$process_id, $where=null);

					// var_dump($resourceinfo);

					$info5['process_id']		= $process_id;			
					$info5['p_start_time']		= '';		
					$info5['p_end_time']		= '';		
					$info5['p_qty']				= $pqty;		
					$info5['pen_qty']			= $pqty;		
					$info5['process_unit']		= $bom_rel_q1['process_unit'];		
					$info5['p_ref_id']			= $bom_rel_q1['rp_id'];		
					$info5['p_ref_type']		= 'process request';		
					$info5['p_product_id']		= $bom_rel_q1['rp_pid'];		
					$info5['pr_process_type']	= $process_type;		
					$info5['process_priority']	= $process_priority;		
					$info5['previous_process_id']= 0;
					$info5['product_version']	= $bom_rel_q1['product_version'];

					if($resourceinfo['process_type']=='1'){		
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}	

					
					if($company_config['batch_wise_stock'] == '1' &&  $company_config['batch_process'] == '0' && $setpro_rel['batch_wise_stock_manage'] == '1'){
						$info5['batch_process_start_time'] = 1;
					}

					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					
					$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon,$branch_id);

					$query_reserve="select * from tbl_request_product where status=0 and perent_id=".$row['rp_id'];
						$rs_reserve=$dbcon->query($query_reserve);	
						while($rel_reserve=brp_mysqli_fetch_array($rs_reserve)){

							$query_resu1 = $dbcon->query("UPDATE tbl_reserve_stock SET p_id =".$inserid_alloc." WHERE p_id=0 and request_id =".$rel_reserve['rp_id']);

						}
				}
			}
							
			}

			if($inserid ){
				$arr['msg']="1";
			}else{
				$arr['msg']="0";
			}
			
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "get_product_name"){
			echo get_product_name($dbcon,$POST['product_id']);
		}
		else if(brp_strtolower($POST['mode']) == "get_product_details") {

			$qry = "select reorder_qty from product_mst where product_id = " . $POST['product_id'];
			$res = $dbcon->query($qry);
			$row = brp_mysqli_fetch_assoc($res);

			$arr['product_name'] = get_product_name($dbcon,$POST['product_id']);
			$arr['unit_name'] = getunitname($dbcon,$POST['unit_id']);
			$arr['version_name'] = get_bom_version_name($dbcon,$POST['bom_version_id']);
			$arr['reorder_qty'] = $row['reorder_qty'];
			echo json_encode($arr);
		}
		else if(brp_strtolower($POST['mode']) == "show_stock_new") {

		$que_so="select * from tbl_request_product where status !=2 and rp_id in(".$POST['rp_id'].")";
		$resi_so=$dbcon->query($que_so);
		$re_so = brp_mysqli_fetch_assoc($resi_so);
		$str = "";
		
			$product_id=$re_so['rp_pid'];
			$unit_id=$re_so['process_unit'];
						//$rp_id=$POST['rp_id'];

			$que_po="select batch_wise_stock_manage from product_mst where product_id=".$product_id;
			$resi_grn=$dbcon->query($que_po);
			$re=brp_mysqli_fetch_assoc($resi_grn);
		

		//$god_stock=req_stock_entry();
		//$wipstock=req_wipstock_entry();
		$str=' 
		<div class="col-md-12" style="font-size: 25px;"><center><strong>Warehouse Stock</strong></center></div>
		<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
		<tr>
		<td style="font-weight: 600;">Warehouse</td>';
		if($re['batch_wise_stock_manage']==1){
			$str .='<td style="font-weight: 600;">Batch No</td>';
		}
		$str .='<td style="font-weight: 600;">Stock</td>
		<td style="font-weight: 600;">Reserve Stock</td>
		<td style="font-weight: 600;">Action</td>
		</tr>
		<tr>';
		if($re['batch_wise_stock_manage']==1){
			$str .='<td>
			<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();load_batch_no();">
			'.get_all_godown($dbcon,'').'
			</select>
			</td>
			<td>
			<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" onchange="load_godown_wise_stock();">
			</select>
			</td>';
		}else{
			$str .='<td>
			<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();">
			'.get_all_godown($dbcon,'').'
			</select>
			</td>
			<!--<td>
			<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" >
			</select>
			</td>-->';
		}
		$str .='<td>
		<input type="number"  title="Stock" min="0" id="st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
		</td>
		<td>
		<input type="number"  title="Enter Stock" min="0" id="st_stock_reserve" name="st_stock_reserve"  class="form-control numbersOnly"  />
		</td>
		<td>
		<input type="button"  name="addrow" id="addrow" onClick="return add_reserve_temp();"  class="btn btn-primary" value="Add"/>
		</td>
		</tr>
		</table>
		<input type="hidden" name="batch_wise_stock_manage" id="batch_wise_stock_manage" value="'.$re['batch_wise_stock_manage'].'" />
		<div id="sale_productdata"></div>

		<div class="col-md-12" style="font-size: 25px;"><center><strong>WIP Stock</strong></center></div>';

		$query="select IFNULL(sum(trn.allocate_base_qty-trn.allocate_base_qty_used),0) as stock_qty,wip.indent_no,wip.indent_date,wip.job_card_no,wip.job_card_date,setp.po_req_date,setp.po_req_no,trn.type_flag,trn.wip_stock_allocate_id from wip_stock_allocate as trn
		left join tbl_request_product as wip on wip.rp_id=trn.rp_id
		left join tbl_set_main_process as setp on setp.sp_id=wip.sp_id
		where trn.status=0 and allocate_base_qty>allocate_base_qty_used and trn.company_id=".$_SESSION['company_id']." and wip.rp_pid=".$product_id."";

		$result=$dbcon->query($query);
		if(mysqli_num_rows($result)>0)
		{
			$str .='<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
			<tr>
			<td style="font-weight: 600;">Indent/Job Card/Work Order</td>
			<td style="font-weight: 600;">Date</td>
			<td style="font-weight: 600;">Pending Qty</td>
			<td style="font-weight: 600;">Reserve Stock</td>
			</tr>';

			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$stockqty=$rel['stock_qty'];

				if($rel['type_flag']==1){
					$refno=$rel['indent_no'];
					$refdate=$rel['indent_date'];
				}else if($rel['type_flag']==2){
					$refno=$rel['job_card_no'];
					$refdate=$rel['job_card_date'];
				}else if($rel['type_flag']==3){
					$refno=$rel['po_req_no'];
					$refdate=$rel['po_req_date'];
				}

				$str .='<tr>
				<td style="font-weight: 600;">'.$refno.'</td>
				<td style="font-weight: 600;">'.$refdate.'</td>
				<td style="font-weight: 600;">'.$stockqty.'</td>
				<td style="font-weight: 600;">
				<input type="number"  title="Enter Stock" min="0" max="'.$stockqty.'" id="wip_stock_reserve'.$rel['wip_stock_allocate_id'].'" name="wip_stock_reserve[]"  class="form-control numbersOnly wip_res_stock"  />
				<input type="hidden" class="wip_stock_id" name="wip_stock_allocate_id[]" id="wip_stock_allocate_id'.$rel['wip_stock_allocate_id'].'" value="'.$rel['wip_stock_allocate_id'].'" />
				</td>
				</tr>';
			}
			$str .='</table>';
		}

		$str .='<div class="col-md-12" >
		<center>
		<input type="button"  name="" id="" onClick="return save_reserve_stock();"  class="btn btn-primary" value="Save"/>

		<input type="hidden" name="product_id_model" id="product_id_model" value="'.$product_id.'" />
		<input type="hidden" name="unit_id_model" id="unit_id_model" value="'.$unit_id.'" />
		
		</center>
		</div>
		';


		echo $str;
	}else if(strtolower($POST['mode'])== "load_batch_no")
	{
		
		$godwn_id=$POST['godwn_id'];
		$product_id=$POST['product_id'];
		$customer_id=$POST['customer_id'];
		$unit_id = $POST['unit_id'];

		$unitname = getunitname($dbcon,$unit_id);

		$query="select batch_no,stock_id from tbl_stock_trn as trn
		where trn.stock_status=0 and stock_flage=1 and product_id=".$product_id." and trn.godown_id=".$godwn_id." and cast(base_stock AS DECIMAL(10,5))>cast(used_base_stock AS DECIMAL(10,5))";

			//echo $query;
		$str="";
		$result=$dbcon->query($query);
		if(mysqli_num_rows($result)>0)
		{	
			$str .= '<option value="">Select Batch Data</option>';
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$gstock=0;$rstock=0;
					$batch_id=$POST['stock_id'];
					
					$gstock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,$godwn_id,$branch_id,$batch_id,$customer_id);

					$rstock=reserve_stock($dbcon,$product_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);


					$stock=$gstock-$rstock;

				$str .= '<option value="'.$rel['stock_id'].'">'.$rel['batch_no'].' - (' . $stock . ' '. $unitname . ')</option>';
			}
		}else{
			$str .= '<option value="">No Batch Data !!</option>';
		}

		echo $str;
	}else if(brp_strtolower($POST['mode']) == "godown_stock") {
		$gstock=0;$rstock=0;
		$batch_id=$POST['batch_id'];
		$gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$POST['unit_id'],$POST['st_godown_id'],$branch_id,$batch_id);

		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);


		$stock=$gstock-$rstock;
			//var_dump($gstock);
			//var_dump($stock);
			//var_dump($gstock-$rstock);
		echo $stock;
	}else if(strtolower($POST['mode'])== "save_reserve_stock")
	{
		//start godown stock
		$query_rstock="select * from work_order_reserve_temp as i
		where i.status = 0 and i.rp_id =".$POST['rp_id'];
		$result_rstock=$dbcon->query($query_rstock);
		while($row_rstock=brp_mysqli_fetch_assoc($result_rstock)){
			$reserve_qty=$row_rstock['reserve_qty'];
			$batch_where="";
			if(!empty($row_rstock['stock_id'])){
				$batch_where=" and i.stock_id=".$row_rstock['stock_id'];
			}
			 $query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
			where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(10,5))>cast(used_base_stock AS DECIMAL(10,5)) ".$batch_where." and i.product_id=".$row_rstock['product_id']." and i.godown_id=".$row_rstock['godown_id'];
			$result_dstock=$dbcon->query($query_dstock);
			while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
				if($row_dstock['convert_unit']==$row_rstock['unit_id']){
					$pending_stock=$row_dstock['pending_conv_stock'];
				}else{
					$pending_stock=$row_dstock['pending_base_stock'];	
				}
				if($reserve_qty>0){
					if($pending_stock>0){
						if($pending_stock>=$reserve_qty){
							$rqty=$reserve_qty;
							$reserve_qty=$reserve_qty-$reserve_qty;
						}else{
							$rqty=$pending_stock;
							$reserve_qty=$reserve_qty-$pending_stock;
						}

						$que="select * from product_mst as ta where product_id=".$row_rstock['product_id'];
						$rs_di=$dbcon->query($que);
						$re=brp_mysqli_fetch_assoc($rs_di);


						if($re['product_conv_unit']==$row_rstock['unit_id']){
							$type="base_unit";
							$con_stock=$rqty;
							$base_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
						}else{
							$type="conv_unit";
							$base_stock=$rqty;
							$con_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
						}

						
						$info_rese['reserve_date']		= date('Y-m-d');
						$info_rese['product_id']		= $row_rstock['product_id'];
						$info_rese['godown_id']			= $row_dstock['godown_id'];
						$info_rese['base_unit']			= $re['product_base_unit'];
						$info_rese['base_stock']		= $base_stock;
						$info_rese['convert_unit']		= $re['product_conv_unit'];
						$info_rese['convert_stock']		= $con_stock;
						$info_rese['stock_flage']		= "1";
						$info_rese['request_id']		= $row_rstock['rp_id'];
						$info_rese['ref_name']			= "wo_allocate";
			 			$info_rese['ref_id']			= "0";
						$info_rese['sales_order_trn_id']= $row_rstock['sales_ordertrn_id'];
						$info_rese['stock_id']			= $row_dstock['stock_id'];
						

						$info_rese['cdate']				= date("Y-m-d H:i:s");
						$info_rese['user_id']			= $_SESSION['user_id'];
						$info_rese['company_id']		= $_SESSION['company_id'];		
											
						$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row_dstock['branch_id']);
					

						update_workorder_complete_qty_and_Status($dbcon,$row_rstock['rp_id'],$rqty);
						$wo_res_temp_info['status'] = 3;
	                                
	                                $updatetrnid=update_record('work_order_reserve_temp',$wo_res_temp_info,"work_order_reserve_temp_id=".$row_rstock['work_order_reserve_temp_id'] , $dbcon);
						
						if($row_dstock['base_unit']==$re['product_base_unit']){
							$used_base_stock=$row_dstock['used_base_stock']+$base_stock;
							$used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
						}else{
							$used_base_stock=$row_dstock['used_convert_stock']+$con_stock;
							$used_convert_stock=$row_dstock['used_base_stock']+$base_stock;
						}

						$info_stock['used_base_stock']		= $used_base_stock;
						$info_stock['used_convert_stock']	= $used_convert_stock;
						
						$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);

						$info_e['sales_ordertrn_id']	=$row_rstock['sales_ordertrn_id'];
						$info_e['product_id']			=$row_rstock['product_id'];
						$info_e['product_qty']			=$info_rese['base_stock'];
						$info_e['godown_id']			=$info_rese['godown_id'];
						$info_e['unit_id']				=$info_rese['base_unit'];
						$info_e['allocate_qty']			=$info_rese['base_stock'];
						$info_e['remaning_invoice_qty']	=$info_rese['base_stock'];
						
						$info_e['cdate']				=date("Y-m-d");
						$info_e['company_id']			=$_SESSION['company_id'];
						$info_e['user_id']				=$_SESSION['user_id'];
						$inserinvoiceidexp=add_record('tbl_sales_order_production_trn', $info_e, $dbcon,$row_dstock['branch_id']);
					}
				}
			}
		}
		//End godown stock
		//start wip stock
		$bstock=$POST['bstock'];
		$bid=$POST['bid'];

		for($i=0;$i<count($bstock);$i++)
		{
			$que12="select ta.*,req.rp_pid from wip_stock_allocate as ta 
			left join tbl_request_product as req on req.rp_id=ta.rp_id
			where wip_stock_allocate_id=".$bid[$i];
			$rs_di11=$dbcon->query($que12);
			$re12=brp_mysqli_fetch_assoc($rs_di11);
							//var_dump($que12);
			$que="select * from product_mst as ta where product_id=".$re12['rp_pid'];
			$rs_di=$dbcon->query($que);
			$re=brp_mysqli_fetch_assoc($rs_di);

			if($re['product_conv_unit']==$re12['allocate_base_unit']){
				$type="base_unit";
				$con_stock=$bstock[$i];
				$base_stock=convert_stock_new($dbcon,$bstock[$i],$re12['rp_pid'],$type);
				
			}else{
				$type="conv_unit";
				$base_stock=$bstock[$i];
				$con_stock=convert_stock_new($dbcon,$bstock[$i],$re12['rp_pid'],$type);
				
			}

			update_workorder_complete_qty_and_Status($dbcon,$re12['rp_id'],$bstock[$i]);


			$info_wip['rp_id']						= $re12['rp_id'];
			$info_wip['type_flag']					= $re12['type_flag'];
			
			$info_wip['sales_order_trn_id']			= $POST['sales_ordertrn_id'];
			//$info_wip['allocate_for_rp_id']		= $POST['rp_id'];
			$info_wip['allocate_for_rp_id']			= $re12['rp_id'];
			$info_wip['allocate_base_qty']			= $base_stock;
			$info_wip['allocate_base_unit']			= $re['product_base_unit'];
			$info_wip['allocate_conv_qty']			= $con_stock;
						//$info_wip['allocate_conv_qty_used']		= $row_rstock['rp_id'];
			$info_wip['allocate_conv_unit']			= $re['product_conv_unit'];
			$info_wip['perent_id']					= $re12['wip_stock_allocate_id'];
			$info_wip['stock_flag']					= 2;
			$info_wip['cdate']						= date("Y-m-d H:i:s");
			$info_wip['user_id']					= $_SESSION['user_id'];
			$info_wip['company_id']					= $_SESSION['company_id'];

			$reserve_wip_id=add_record('wip_stock_allocate',$info_wip, $dbcon,$re12['branch_id']);

			$info_w['sales_ordertrn_id']	=$info_wip['sales_order_trn_id'];
			$info_w['product_id']			=$re['product_id'];
			$info_w['product_qty']			=$info_wip['allocate_base_qty'];
			$info_w['request_id']			=$info_wip['allocate_for_rp_id'];
			$info_w['unit_id']				=$info_wip['allocate_base_unit'];
			
			$info_w['cdate']				=date("Y-m-d");
			$info_w['company_id']			=$_SESSION['company_id'];
			$info_w['user_id']				=$_SESSION['user_id'];
			$inserinvoiceidexp1=add_record('tbl_sales_order_production_trn', $info_w, $dbcon,$re12['branch_id']);
		}

		//end wip stock
	}		
		
		else if(brp_strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		
		else if(brp_strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and product_type='.$type_id.'');
		}

		else if(brp_strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
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
		
		else if(brp_strtolower($POST['mode'])== "get_po_login")
		{
			$id = $POST['id']; // as table id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `po`.`sp_status` as stage FROM `tbl_set_main_process` as po left join `users` as u ON  `po`.`user_id` = `u`.`user_id` left join `users` as mu ON  `po`.`muser_id` = `mu`.`user_id`  Where `po`.`sp_id`='".$id."' and `po`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=brp_mysqli_fetch_assoc($vrow);
				
			if($rel['stage']=='1'){
			 	$stage = 'Approved';
			 }else{
			 	$stage = 'Pending';
			 }
					
			echo '<section class="panel">
                     <div class="panel-body bio-graph-info">
                         <h1>Login History</h1>
                         <div class="row">
                             <div class="bio-row">
                                 <p><span>Prepared By </span>: '.$rel["prepared_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Prepared Date </span>: '.(($rel["cdate"]!='' && $rel['cdate']!="1970-01-01" && $rel['cdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["cdate"])):'').'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified By </span>: '.$rel["last_modify_by"].'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Modified Date</span>: '.(($rel["mdate"]!='' && $rel['mdate']!="1970-01-01" && $rel['mdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["mdate"])):'').'</p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved By </span>: </p>
                             </div>
                             <div class="bio-row">
                                 <p><span>Approved Date </span>: </p>
                             </div>
                             <div class="bio-row">
                                 <p><span> Stage </span>: '.$stage.'</p>
                             </div>
                             
                         </div>
                     </div>
                 </section>';
		}

		else if(brp_strtolower($POST['mode'])== "get_item_selected_information")
		{
			$id = $POST['id'];
			$product_id = $POST['product_id'];
			$vendor_id = $POST['vendor_id'];


			$sql = "SELECT sep.*,`l`.`l_name`, `pm`.`product_type`, `pm`.`product_name`,`pm`.`product_desc` FROM `tbl_set_main_process` as sep 
				    left join tbl_ledger as l ON `sep`.`vendor_id` = `l`.`l_id` 
				    left join product_mst as pm ON `sep`.`product_id` = `pm`.`product_id` 
				    WHERE  `sep`.`sp_id`='".$id."' AND `sep`.`company_id`='".$_SESSION['company_id']."'";
			$rel=$dbcon->query($sql);
			$result=brp_mysqli_fetch_assoc($rel);

			if($result['sp_status']=='0'){
				$status='Pending';
			}else{
				$status='Approved';
			}

			$arr['po_req_no'] = $result['po_req_no'];
			$arr['po_req_date'] = date('d-m-Y', strtotime($result['po_req_date']));
			$arr['so_no'] = 'NA';
			$arr['so_date'] = date('d-m-Y');
			$arr['status'] = $status;
			$arr['vender_id'] =  $result['l_name'];
			$arr['vendor_po_number'] = $result['po_no'];
			$arr['vender_po_date'] = date('d-m-Y', strtotime($result['po_date']));
			$arr['product_type'] = get_pro_type_name($result['product_type']);
			$arr['product_id'] = $result['product_name'];
			$arr['item_description'] = $result['product_desc'];
			$arr['order_start_date'] = date('d-m-Y');
			$arr['order_delivery_date'] = date('d-m-Y');
			$arr['ds_number'] = 'NA';
			$arr['bom_no'] = $result['bom_no'];
			$arr['bom_id'] = $result['bom_id'];
			$arr['order_qty'] = $result['rp_req_qty'];
			$arr['remark'] = '0';
			$arr['report']= '<a class="btn btn-primary" data-original-title="view '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'work_order_new_print/'.$result['sp_id'].'">Live Production Report</a>';
			
			$arr['vendorId'] = $vendor_id;
			echo json_encode($arr);
		}
		else if(brp_strtolower($POST['mode']) == "get_bom_costing") {
			$sp_id = $POST['sp_id'];

			$sql = "SELECT * from tbl_set_main_process WHERE  sp_id =" . $sp_id;
			$rel=$dbcon->query($sql);
			$result=brp_mysqli_fetch_assoc($rel);

			echo get_bom_costing($dbcon,$result['product_id'],$result['bom_id']);
		}

		else if(brp_strtolower($POST['mode']) == "bom_costing_assign") {
			
			$info['bom_costing_id'] = $POST['bom_costing_id'];	
			$updateid=update_record('tbl_set_main_process', $info,"sp_id=".$POST['sp_id'] , $dbcon);

			if($updateid){
				$arr['msg'] = "1";
			}else{
				$arr['msg'] = "0";
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "delete") {
		   	$info['sp_status']	= 2;
		   	$info1['status']	= 2;
		   	$sp_id = $POST['eid'];

		   	$updateestimateid=update_record('tbl_set_main_process', $info,"sp_id=".$POST['eid'] , $dbcon);	
		   	$updatetrancationid=update_record('tbl_request_product', $info1,"sp_id=".$POST['eid'] , $dbcon);

		   	$qry = "SELECT sales_order_trn_id,(SELECT GROUP_CONCAT(rp_id) FROM tbl_request_product where sp_id = ".$sp_id.") as rp_id FROM tbl_set_main_process WHERE sp_id = " . $sp_id;
		   	$res = $dbcon->query($qry);
		   	$row = brp_mysqli_fetch_assoc($res);

		   	if(!empty($row['sales_order_trn_id'])){
		   		$info2['sales_order_production_status']	= 2;
				$updateestimateid=update_record('tbl_sales_order_production_trn', $info2,"request_id in (".$row['rp_id'].") OR sales_ordertrn_id = " . $row['sales_order_trn_id'], $dbcon);		
		   	}
						
			$info3['status']	= 2;		   	
			$updateestimateid=update_record('wip_stock_allocate', $info3,"rp_id in (".$row['rp_id'].")", $dbcon);	
		   	
		   	if($updateestimateid)
		   		echo "1";	
		   	else
		   		echo "0";			
	   }
	   else if(brp_strtolower($POST['mode']) == "fieldadd") {
		$info1['rp_id']				= $POST['rp_id'];
		$info1['reserve_qty']		= $POST['st_stock_reserve'];
		$info1['unit_id']			= $POST['unit_id'];
		$info1['godown_id']			= $POST['st_godown_id'];
		$info1['product_id']		= $POST['product_id'];
		$info1['stock_id']			= $POST['st_stock_id'];

		$info1['cdate']				= date('Y-m-d H:i:s');
		$info1['user_id']			= $_SESSION['user_id'];	
		$info1['company_id']		= $_SESSION['company_id'];	

		$inserpoid=add_record('work_order_reserve_temp',$info1, $dbcon, $branch_id);

		if($inserpoid){
			echo 1;
		}
	}
	else if(strtolower($POST['mode'])== "delete_data_stock")
	{
		$row=array();
		$info['status']=2;	
		$updateid=update_record("work_order_reserve_temp", $info, "work_order_reserve_temp_id=".$POST['eid'] , $dbcon);

		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	   else if(strtolower($POST['mode']) == "load_tempoutward") {


		 $query="select trn.work_order_reserve_temp_id,trn.reserve_qty,cat.gd_name,uns.unit_name,st.batch_no from work_order_reserve_temp as trn
		left join mst_godown as cat on cat.gd_id=trn.godown_id
		left join unit_mst as uns on uns.unitid=trn.unit_id
		left join tbl_stock_trn as st on st.stock_id=trn.stock_id
		where trn.status=0 and trn.rp_id='".$POST['rp_id']."'";

			//echo $query;
		$result=$dbcon->query($query);
		echo '<div class="form-group">
		<div class="col-md-12 col-xs-11">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="10%">Warehouse</th>';
		if($POST['batch_wise_stock_manage']==1){
			echo '<th class="text-center"width="15%">Batch No</th>';
		}
		echo '<th class="text-center"width="15%">Reserve Stock</th>
		<th class="text-center"width="10%">Action</th>
		</tr>';

			//echo $query;
		if(mysqli_num_rows($result)>0)
		{
			$i=1;$total=0;
			while($rel=brp_mysqli_fetch_assoc($result))
			{

				echo '<tr id="fieldtr'.$i.'">
				<td style="vertical-align:top;" class="text-left">
				'.$rel['gd_name'].'
				</td>';				
				if($POST['batch_wise_stock_manage']==1){
					echo '<td style="vertical-align:top;" class="text-left">
					'.$rel['batch_no'].'
					</td>';
				}
				echo '<td style="vertical-align:top;" class="text-center">
				'.$rel['reserve_qty'].' '.$rel['unit_name'].'
				</td>					

				<td style="vertical-align:top">

				<!--<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['purchaseordertrn_id'].',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>-->

				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_stock('.$rel['work_order_reserve_temp_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
				</td>	
				</tr>';
				$total=$total+$rel['reserve_qty'];
				$i++;
			}
		}

		else{
			echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</table> 
		<input type="hidden" name="gstock_total" id="gstock_total" value="'.$total.'" />
		</div>
		</div>';
	}
	else if(strtolower($POST['mode'])== "create_indent") {

		$product_id = $POST['so_product_id'];
		$branch_id = $POST['production_branch_id'];
		$rp_id= $POST['so_rp_id'];
		
		$info['po_req_date']			=date('Y-m-d',strtotime($POST['indent_date']));
		$info['rp_req_qty']				=$POST['indent_qty'];
		$info['in_process_qty_main']	= '';
		$info['rp_po_qty']				=$POST['indent_qty'];;
		$info['product_id']				=$product_id;
		$info['sales_order_trn_id']		='';
		$info['company_id']				=$_SESSION['company_id'];
		$info['vendor_id']				=$POST['cust_id'];
		$info['sales_order_date']		=date('Y-m-d');
		$info['po_no']					='';
		$info['po_date']				=date('Y-m-d');
		$info['sales_order_no']			='';
		$info['bom_id']					="";
		$info['bom_no']					="";
		$info['workorder_type']			= 1;

		$info['cdate']					= date('Y-m-d H:i:s');
		$info['user_id']				= $_SESSION['user_id'];
		$info['po_req_no']				= load_series_no($dbcon,9);
			
		$inserid=add_record('tbl_set_main_process', $info, $dbcon,$branch_id);
		if($inserid){
			update_common_no($dbcon,9);
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '9' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);

			$set_pro="SELECT product_base_unit,product_conv_unit FROM `product_mst` WHERE product_status=0 AND product_id='".$product_id."'";
			$setpro_rel=brp_mysqli_fetch_assoc($dbcon->query($set_pro));

			$info_su['sp_id']				= $inserid;
			$info_su['sr_no']				= 0;
			$info_su['rp_pid']				= $product_id;//product_id
			$info_su['rp_req_date']			= date("Y-m-d");
			$info_su['rp_req_qty']			= $POST['indent_qty'];//required qty
			$info_su['sales_order_trn_id']	= $sales_ordertrn_id;//required qty
			$info_su['rp_po_qty']			= $POST['indent_qty'];//po qty
			$info_su['in_process_qty']		= '';//process qty
			$info_su['rp_req_type']			= "min_max";//type
			$info_su['process_unit']		= $setpro_rel['product_base_unit'];
			$info_su['purchase_unit']		= $setpro_rel['product_conv_unit'];
			$info_su['perent_id']			= 0;
			$info_su['main_request']		= 1;
			$info_su['status']				= 0;
			$info_su['user_id']				= $_SESSION['user_id'];
			$info_su['company_id']			= $_SESSION['company_id'];
			
			$info_su['bom_id']				='';
			$info_su['product_version']		= '';
			$info_su['jobwork_type']		= $info['jobwork_type']	;
			$info_su['workorder_type']	= 1;
			$info_su['customer_id']			=  $POST['cust_id'];

			$indent_no=load_common_no($dbcon,17);
			update_common_no($dbcon,17);
			$info_su['indent_status']		= 1;
			$info_su['indent_no']			= $indent_no;
			$info_su['indent_date']			= date('Y-m-d');
			$info_su['cdate']			= date('Y-m-d H:i:s');
			$info_su['branch_id']			=	$branch_id;

			
			$inserid_sub1=add_record('tbl_request_product', $info_su, $dbcon,	$branch_id);

			update_workorder_complete_qty_and_Status($dbcon,$rp_id,$POST['indent_qty']);

			$info_soallo['sales_ordertrn_id']		= '';	
			$info_soallo['product_id']				= $product_id;	
			$info_soallo['product_qty']				= $info['rp_req_qty'];	
			$info_soallo['request_id']				= $inserid_sub1;	
			$info_soallo['unit_id']					= $info_su['process_unit']	;	
			$info_soallo['user_id']					= $_SESSION['user_id'];	
			$info_soallo['cdate']					= date("Y-m-d H:i:s");	
			$info_soallo['company_id']				= $_SESSION['company_id'];	

			$inser_so_allo=add_record('tbl_sales_order_production_trn', $info_soallo, $dbcon);
			$arr['msg']  = '1';
		}else{
			$arr['msg']  = '0';
		}
		
		echo json_encode($arr);
	}
	else if(brp_strtolower($POST['mode']) == "update_request_qty") {
		$jobcard_qty = $POST['jobcard_qty'];
		$arr_rp_id = $POST['rp_id'];
		$arr_qty = $POST['qty'];
		$update_id = 0;
		for($i=0;$i<count($arr_rp_id);$i++){
			$info['rp_req_qty'] = $arr_qty[$i];
			$info['req_qty_one'] = $arr_qty[$i] / $jobcard_qty;

			$update_id=update_record('tbl_request_product', $info, "rp_id =".$arr_rp_id[$i] , $dbcon);	
		}
		echo "1";
	}
	else if(brp_strtolower($POST['mode']) == "show_material_list") {
		$rp_id = $POST['rp_id'];
		$jobcard_qty = $POST['jobcard_qty'];
		$query  = "SELECT req.*,p.product_name,p.reorder_qty,unit.unit_name FROM tbl_request_product as req
				left join product_mst as p ON p.product_id = req.rp_pid
				left join unit_mst as unit ON unit.unitid = req.process_unit
		 where req.perent_id = " . $rp_id;
		$result = $dbcon->query($query);
		$str = "";
		$str .= "<div class='row mtop20 m-bot15 text-center'>
				<h2> Sub Product & Raw Material List</h2>
			</div>";
		if(brp_mysqli_num_rows($result) > 0){
			$i = 1;
			$str .= '<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped mtop20 m-bot15">';
			$str .= "<th>Sr No.</th>
					<th>Product Name</th>
					<th>Shortage Qty</th>
					<th>Qty</th>";
			$str .= "<tbody>";		
			while($row = brp_mysqli_fetch_assoc($result)){
				$str .= "<td>".$i."</td>";
				$str .= "<td>".$row['product_name']."</td>";
				$str .= "<td>".$row['rp_req_qty']. "  ". $row['unit_name'] ."</td>";
				$str .= '<td> <input type="number"  title="Enter Qty" min="0" id="shortage_qty_'.$row['rp_id'].'" data-rp_id="'.$row['rp_id'].'" name="st_stock_reserve" data-shortage_qty="'.$row['rp_req_qty'].'" data-parent_qty="'.$jobcard_qty.'" class="form-control numbersOnly shortage_qty" data-reorder_qty="'.$row['reorder_qty'].'" value="'.$row['rp_req_qty'].'" />'.$row['unit_name'].'</td>';
			}
			$str .= "</tbody></table>";
			$str .= "<div class='row'>
				<div class='col-md-12 text-center'>
					<button type='button' class='btn btn-success' onClick='update_rowmaterial_qty()'>Submit</button>
					<button type='button' style='margin-left:20px;' class='btn btn-danger' onClick='close_jobcard_modal(2)'>Close</button> 
				</div>
			</div>";
		}else{
			$str .= "<div class='row'>
				<div class='col-md-12 text-center'>
					<h4> No Product Material. </h4>
				</div>
			</div>";
		}	

		echo $str;
		
	}else if(brp_strtolower($POST['mode']) == "bom_process_add") {
				// echo "<pre>";print_r($POST);die;
				$product_id = $POST['product_id'];
				$rp_id = $POST['rp_id'];

				$q = "select pr_process_id from tbl_wororder_product_process where rp_id = ".$POST['rp_id']." and product_id =".$POST['product_id']." order by process_priority ASC";

				$res_pro = $dbcon->query($q);
				$arr_process = brp_mysqli_fetch_all($res_pro);
				$hidden = $_POST['sel_process']; //get the values from the hidden field
				$hidden_in_array = explode(",", $hidden); //convert the values into array
		
		
				$filter_array = array_filter($hidden_in_array); //remove empty index 
				$arr_sel_process = array_values($filter_array); //reset the array key 

				/*$unsel_process = $POST['unsel_process'];
				$arr_unsel_process = explode(',',$unsel_process);
		*/
				$info['product_id'] = $product_id;		
				$info['rp_id'] = 	$POST['rp_id'];		
				$info['branch_id'] = $POST['branch_id'];		
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				
				$del_id =	delete_record('tbl_wororder_product_process' ,'rp_id = ' . $POST['rp_id'] . ' and product_id =' .$product_id ,$dbcon);
				$x = 1;
				foreach ($arr_sel_process as $process_id) {

					$p_qry = "select process_type,process_time,process_opening from tbl_product_process where product_id = " . $product_id . " and process_id = " . $process_id;
					$p_pro = $dbcon->query($p_qry);
					$p_pro_row=brp_mysqli_fetch_assoc($p_pro);


					$desc_qry = "select description from tbl_temp_process_desc where rp_id = " . $POST['rp_id'] . " and process_id = " . $process_id;
					$desc_pro = $dbcon->query($desc_qry);
					$desc_row=brp_mysqli_fetch_assoc($desc_pro);


					$info['process_priority']	= $x;
					$info['process_id']	= $process_id;
					$info['process_time']	=  $p_pro_row['process_time'];
					$info['process_type']	= $p_pro_row['process_type'];
					$info['process_opening']	= $p_pro_row['process_opening'];
					$info['description']	= $desc_row['description'];
					// if(empty($POST['edit_id']) && empty($arr_process)){			
						$inserestimateid=add_record('tbl_wororder_product_process', $info, $dbcon);
					/*}else if(array_search($process_id, array_column($arr_process, 'process_id')) === false){
						
						$inserestimateid=add_record('tbl_wororder_product_process', $info, $dbcon);
					}else if(array_search($process_id, array_column($arr_process, 'process_id')) !== false){
						
						$update_info['priority'] = $x;
						$update_info['process_status'] = 0;
						$where = "product_id = " . $product_id ." AND process_id=".$process_id;
						$inserestimateid=update_record('tbl_wororder_product_process', $update_info, $where , $dbcon);	
						if($inserestimateid == 0){
							$inserestimateid = 1;
						}
					}*/
					$x++;
				}

			/*	if(!empty($POST['edit_id'])){

					foreach ($arr_unsel_process as $process_id) {
						if(array_search($process_id, array_column($arr_process, 'process_id')) !== false){
							$update_info['process_status'] = 2;
							$where = "product_id = " . $product_id ." AND  process_id=".$process_id;
							$inserestimateid=update_record('tbl_wororder_product_process', $update_info, $where, $dbcon);
							if($inserestimateid == 0){
								$inserestimateid = 1;
							}	
						}

					}
				}*/

				$qry2 = "select main_request from tbl_request_product where rp_id = ". $rp_id;
				$result2=$dbcon->query($qry2);
				$res2=brp_mysqli_fetch_assoc($result2);

				if($res2['main_request'] == 1){
					$qry = "select process_id,product_id,process_type from tbl_wororder_product_process where process_priority = 1 and rp_id = ". $rp_id;
					$result=$dbcon->query($qry);
					$res=brp_mysqli_fetch_assoc($result);


					$qry1 = "select p_id,process_id,pr_process_type from tbl_allocate_process where  p_status = 0 and p_ref_id = ".$rp_id." and  p_product_id = " . $product_id . " and process_priority= 1 and previous_process_id = 0";
					$result1=$dbcon->query($qry1);
					$res1=brp_mysqli_fetch_assoc($result1);

					if($res1['process_id'] != $res['process_id'] && $res1['pr_process_type'] != $res['process_type']){
						$upd_ap['process_id']	= $res['process_id'];
						$upd_ap['pr_process_type'] = $res['process_type'];
						$updateid=update_record("tbl_allocate_process", $upd_ap,"p_id=".$res1['p_id'], $dbcon);	
					}
				}

				if($inserestimateid){
					// if(empty($POST['edit_id'])){
						$arr['msg']="1";
					/*}else{
						$arr['msg']="update";
					}*/
					$del_id =	delete_record('tbl_temp_process_desc' ,'1' ,$dbcon);

				}else{
					$arr['msg']="0";
				}

				echo json_encode($arr);
			}
	else if(brp_strtolower($POST['mode']) == "get_product_process_data") {
		$del_id =	delete_record('tbl_temp_process_desc' ,'1' ,$dbcon);
		
		$q = "select wp.process_id,pmst.process_name,wp.process_type,wp.rp_id,wp.description from tbl_wororder_product_process as wp
		left join process_mst as pmst on pmst.process_id=wp.process_id where  wp.product_id =".$POST['product_id']." AND wp.rp_id =".$POST['rp_id']."  order by wp.process_priority ASC";
		$res_pro = $dbcon->query($q);
		$arr_process=brp_mysqli_fetch_all($res_pro);
		foreach($arr_process as $temp){
			$info['rp_id'] =  $temp['rp_id'];
			$info['process_id'] = $temp['process_id'];
			$info['description'] = $temp['description'];
			if($temp['description'] !=""){
				$inserestimateid=add_record('tbl_temp_process_desc', $info, $dbcon);
			}
		}

		$process_ids = "";
		$selected_process_ids = "";
		
		$selected_process_ids = implode(',', array_column($arr_process, 'process_id'));

		$multiple_value = implode(',', array_column($arr_process, 'process_id'));
	
		$query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$POST['product_id'];

		$rel_pro = $dbcon->query($query_pro);
		$i=1;
		$str='<div class="row"><div class="col-md-6 text-center"> <h4>All Process</h4></div>
		<div class="col-md-6 text-center"><h4>Selected Process as priority</h4></div>	</div>
		<form class="form-horizontal" role="form" id="frm_bom_process_add" action="javascript:;" method="post" name="frm_bom_process_add">
		<input type="hidden" name="multiple_value" id="multiple_value" value="'.$multiple_value.'"/>
		<input type="hidden" name="process_sel_product_id" id="process_sel_product_id" value="'.$POST['product_id'].'"/>
		<input type="hidden" name="selected_rp_id" id="selected_rp_id" value="'.$POST['rp_id'].'"/>
		<input type="hidden" name="selected_desc_id" id="selected_desc_id" value=""/>';

		$str .='<div class="row">
				  <div class="col-md-5">
				  	 <label for="chk_leftside_process">
				     <input type="checkbox" onClick="select_all_left_side_process()" id="chk_leftside_process" name="chk_leftside_process"/> Select All Process	</label>
				    <ul id="process_left">';

		while($product_process=brp_mysqli_fetch_assoc($rel_pro)){

			if(!in_array($product_process['process_id'],array_column($arr_process, 'process_id'))){
					$icon = "";
		if($product_process['process_type'] == '1'){

			$icon = ' [inhouse] ';
		}else{

			$icon = ' [outside]	';
		}

		// $str .=  '<option class="process_row '.$selected.'" data-cid="'.$i.'" value="'.$product_process['process_id'].'">' . $product_process['process_name'] . $icon .'</option>';

		  $str .= '<li class="process_row" data-cid="'.$i.'"  id="'.$product_process['process_id'].'">'.$product_process['process_name'] . $icon .'</li>';
		  $process_ids = $process_ids + ',' + $product_process['process_id'];
		  $i++;
				 }
		
		}
		$str .='</ul>
				  </div>
				    <div class="col-md-2">
				      <div>
				        <button id="moveRight" class="bigBtn bigBtn btn btn-primary"> > </button>  
				      </div>
				       <div>
				      <button id="moveLeft"  class="bigBtn bigBtn btn btn-danger"> < </button>
				      </div>
				      
				    </div>
				    <div class="col-md-5">
				     <label for="chk_rightside_process">
				     <input type="checkbox" onClick="select_all_right_side_process()" id="chk_rightside_process" name="chk_rightside_process"/> Select All Process </label>
				<ul id="process_right">';

		foreach($arr_process as $pro){
			if($pro['process_type'] == '1'){

		        			$icon = ' [inhouse] ';
		        		}else{

		        			$icon = ' [outside]	';
		        		}
			$str .= '<li   class="process_row" data-cid="'.$i.'" id="'.$pro['process_id'].'"> '.$pro['process_name']. $icon .' </li>';
			$i++;
		}
  
		$str .='</ul>  
		  </div>
		  <div class="col-md-12">
		    <input type="hidden" id="process_ids" class="form-control" placeholder="All Process" value="'.$product_ids.'">
			<input type="hidden" id="selected_process_ids" class="form-control" placeholder="Selected Process" value="'.$selected_process_ids.'">
		  </div>
		</div>';
		$product_process = brp_mysqli_fetch_assoc($rel_pro);

		if(brp_mysqli_num_rows($rel_pro) > 0){
			
			$str.="
			<input type='hidden' id='selected_process_id'>
			<div class='col-md-12' id='row_process_desc' style='display:none;margin-top:15px;'>
				<div class='col-md-12'>
				Description
			</div>
			<div class='col-md-12' style='padding:0px'>
			<textarea class='form-control' rows='5' id='process_desc'></textarea>
			</div>
			
			<div class='col-md-12' style='margin-top: 15px;'>
				<center>

					<button type='button' id='btProcessDesc' name='btProcessDesc' onClick='save_process_desc(".$POST['rp_id'].")' class='btn btn-success btn-space'>Save</button>
				</center>
			</div>

			</div>
			<div class='col-md-12' style='margin-top: 50px;'>
				<center>

					<button type='button' id='process_save' onClick='bom_process_add(".$POST['rp_id'].")' name='process_save' class='btn btn-success btn-space' >Save & Next</button>
					<button type='button' style='margin-left:20px;' class='btn btn-danger' onClick='close_jobcard_modal(1)'>Close</button> 
				</center>
			</div>

			</form>
			";
		}else{

			$str = '<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
			<input type="hidden" name="multiple_value" id="multiple_value" value=""/>

			<div class="col-md-12" style="margin-top: 15px;">
			<h3>NO PROCESS ADDED</h3>
			<div style="display:none;">
			<textarea class="form-control" rows="5" id="process_desc" ></textarea></div>
			</div>
			</div>
			</form>
			
			';
		}
		echo $str;
	}
  }
}
?>