<?php 
	session_start();
	include('../include/urlfile.php');	
	// error_reporting(E_ALL);
	$form="Work Order ";
	$countryid='101';
	$stateid='1';
	$cityid='1';
	//$ver = (float)phpversion();
	//echo $ver; 
	$branch_id = $_SESSION['branch_id'];
	$job_work_type = 0;
	$bom_version_name = "R&D";
	$getspecialConfiguration=getspecialConfiguration($dbcon);
	
	$company_config = getCompanyConfiguration($dbcon);
	$unit_id = 0;
	$wo_type = "";
	$extra_stock = 0;
	$ext_stock_vendor_id = 0;

	$customer_id = "";
	$style = "";
	$remark = "";

	$add_attachment = 0;

	$sales_order_id = 0;

	$wo_no_title = "Workorder No";
	$wo_dt_title = "Workorder Date";
	$store_order_id = "";
	$work_order_id = 0;
	$rp_id = 0;

	$is_reserve_godown = 0;
	$default_godown_id = 0;
	$direct_reserve_stock = 0;

	if($getspecialConfiguration['austar_permission'] == '1'){
		if($company_config['set_reserve_godown'] == '1'){
			$is_reserve_godown = 1;
			if($company_config['default_godown_id'] != '' && $company_config['default_godown_id'] != '0' && $company_config['default_godown_id'] > 0){
				$default_godown_id = $company_config['default_godown_id'];
			}
		}
	}


	if(strpos($_SERVER['REQUEST_URI'], "request_product")==true)
	{
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		//$query="select * from product_mst where product_id='$id'";
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	

		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		 $query1="select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 and req.rp_pid=".$rel["product_id"]." group by req.rp_pid";
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));
		
		$total=$min_stock-($opening+$rel1['reqqty']);
		if($total<0){
			$total=0;
		}

		if($rel['bom_required']==1){
			$totalpro=$total;
			$totalpo=0;
		}else{
			$totalpo=$total;
			$totalpro=0;
		}

		if($company_config['workorder_planning'] == '1'){
			if($total > 0){
				$total = 1;
				if($rel['bom_required']==1){
					$totalpro=1;
					$totalpo=0;
				}else{
					$totalpo=1;
					$totalpro=0;
				}
			}
		}
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=brp_mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT sp_id,branch_id,remark FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id;
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		 $remark = $bom_rel_q['remark'];


		$select_branchId = $bom_rel_q['branch_id'];
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		
		// pathik start date : 12-12-2020
		//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'get_stock_detail/work_order';
	}if(strpos($_SERVER['REQUEST_URI'], "store_order_workorder_request")==true)
	{
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		//$query="select * from product_mst where product_id='$id'";

		$store_order_id=$dbcon->real_escape_string($_REQUEST['order_id']);

/*		$query="select o.*,mst.*, tc.cat_name  from  tbl_store_order_min_max as o left join product_mst as mst on mst.product_id = o.product_id left join tbl_category as tc on mst.product_category=tc.cat_id where o.order_id='$store_order_id'";
*/

	$query="select o.*,mst.*, tc.cat_name  from  tbl_store_order_min_max as o left join product_mst as mst on mst.product_id = o.product_id left join tbl_category as tc on mst.product_category=tc.cat_id where o.order_id='$store_order_id'";

		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$doc_no = $rel['doc_no'];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['base_request_qty'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		
		$select_branchId = $_SESSION['variable']['branch_id'];
		 $query1="select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 and store_order_id = " . $store_order_id." and req.rp_pid=".$rel["product_id"]." group by req.rp_pid";
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));
		

		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}

		if($rel['bom_required']==1){
			$totalpro=$total;
			$totalpo=0;
		}else{
			$totalpo=$total;
			$totalpro=0;
		}


		$total=$min_stock-$rel1['reqqty'];
		if($total<0){
			$total=0;
		}

		if($company_config['workorder_planning'] == '1'){
			if($total > 0){
				$total = 1;
				if($rel['bom_required']==1){
					$totalpro=1;
					$totalpo=0;
				}else{
					$totalpo=1;
					$totalpro=0;
				}
			}
		}
		
		$bom_id=$rel['bom_id'];
		$version_id = $rel['bom_version_id'];
		$bom_version_name = get_bom_version_name($dbcon,$version_id);

		// var_dump($rel);die;
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT * FROM `tbl_set_main_process` WHERE sp_status=0 AND store_order_id =".$store_order_id." and product_id=".$id;
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		 $remark = $bom_rel_q['remark'];

		$rp_id = $bom_rel_q['rp_id'];
		// var_dump($work_order_id);die;
		$select_branchId = $bom_rel_q['branch_id'];
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		
		// $bom_q="SELECT * FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and sales_order_trn_id=".$so_trn_id." and company_id=".$_SESSION['company_id'];
		$bom_q_res = $dbcon->query($bom_q);
		$bom_rel_q=brp_mysqli_fetch_assoc($bom_q_res);
		$work_order_id=$bom_rel_q['sp_id'];
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$rp_id = $bom_rel_q['rp_id'];
		// var_dump($work_order_id);die;
		$select_branchId = $bom_rel_q['branch_id'];


		if(brp_mysqli_num_rows($bom_q_res)>0)
		{
			$customer_req_material = $bom_rel_q["customer_req_material"];
			$customer_req_grade = $bom_rel_q["customer_req_grade"];
			$customer_req_size = $bom_rel_q["customer_req_size"];
			$customer_req_id = $bom_rel_q["customer_req_id"];
			$customer_req_length = $bom_rel_q["customer_req_length"];
			$customer_req_heat = $bom_rel_q["customer_req_heat"];
			$customer_req_coc = $bom_rel_q["customer_req_coc"];
			$customer_ref_no = $bom_rel_q["customer_ref_no"];
			$customer_asset_serial = $bom_rel_q["customer_asset_serial"];
			$customer_bevel_spec = $bom_rel_q["customer_bevel_spec"];

		}
		$branchId = $bom_rel_q['branch_id'];

		if($bom_id == 0){
			$ver_qry = "select bom_version_id from pro_ms_bom_version where product_id = ".$id." and is_default_bom = 1 and bom_version_status = 0 and company_id = " . $_SESSION['company_id']; 

			$ver_rel_q=brp_mysqli_fetch_assoc($dbcon->query($ver_qry));

			$version_id = $ver_rel_q['bom_version_id'];

			$sel1_1=$dbcon->query("select * from tbl_bom where bom_product='$id' and bom_version_id ='$version_id'");
			$row1_1=brp_mysqli_fetch_array($sel1_1);
			$bom_id=$row1_1['bom_id'];
			
		}
		// pathik start date : 12-12-2020
			//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select bom_version_id from tbl_bom as bom
								where bom.bom_id='$bom_id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$version_id = $row123['bom_version_id'];


		$bom_version_name = get_bom_version_name($dbcon,$version_id);
		
		$check_process_query="SELECT rp_id,customer_id FROM `tbl_request_product` WHERE   sp_id=".$sp_id." AND main_request = '1'";
		$check_process_result=$dbcon->query($check_process_query);
		$main_rp_row = brp_mysqli_fetch_array($check_process_result);

		$customer_id = $main_rp_row['customer_id'];
		
	 	$bom_query = "select * from pro_bom_process where bom_version_id IN (select bom_version_id from tbl_bom where bom_id = '$bom_id')";
		$bom_result=$dbcon->query($bom_query);
		
		if(brp_mysqli_num_rows($bom_result)>0)
		{
		
			while($bom_row=brp_mysqli_fetch_array($bom_result))
			{	
				$rp_id = $main_rp_row['rp_id'];
				$process_id= $bom_row['pr_process_id'];
				$check_query = "select * from tbl_wororder_product_process where product_id = '$id' AND rp_id = '$rp_id' AND process_id = '$process_id'";
				$check_result=$dbcon->query($check_query);
				if($rp_id > 0){

					// $is_added = 1;
					/*if(brp_mysqli_num_rows($check_result)<1)
					{	
					
						$check_prodcut_query="SELECT process_time,process_type,process_opening,process_id FROM `tbl_product_process` WHERE pr_process_id = '$process_id'";
						$check_prodcut_result=$dbcon->query($check_prodcut_query);
						$check_prodcut_row = brp_mysqli_fetch_array($check_prodcut_result);
					
								
						$info_wororder_process['product_id']		= $id;
						$info_wororder_process['rp_id']				= $rp_id;
						$info_wororder_process['process_priority']	= $bom_row['priority'];
						$info_wororder_process['process_time']		= $check_prodcut_row['process_time'];
						$info_wororder_process['process_type']		= $check_prodcut_row['process_type'];
						$info_wororder_process['process_opening']	= $check_prodcut_row['process_opening'];
						$info_wororder_process['process_id']		= $check_prodcut_row['process_id'];
						$info_wororder_process['cdate']				= date("Y-m-d H:i:s");
						$info_wororder_process['user_id']			= $_SESSION['user_id'];
						$info_wororder_process['company_id']		= $_SESSION['company_id'];
						$info_wororder_process['branch_id']			= $_SESSION['branch_id'];				
						// $job_work_trn_id=add_record('tbl_wororder_product_process',$info_wororder_process, $dbcon,$branch_id);
					}*/
				}
			}
		}else{
			// if($main_rp_row['rp_id'] > 0){
			// 	$is_added = 1;	
			// }
		}
		// pathik start date : 12-12-2020
		//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'store_order_request/min_max';
	}
else if(strpos($_SERVER['REQUEST_URI'], "edit_workorder")==true)
	{
		$mode="Edit";$direct_add='1';$request=1;$smode="";		
		$sp_id=$dbcon->real_escape_string($_REQUEST['edit_id']);
		
		$work_query="select * from tbl_set_main_process where  sp_id='$sp_id'";
		$work_rel=brp_mysqli_fetch_assoc($dbcon->query($work_query));
		 $remark = $work_rel['remark'];
 

		$select_branchId = $work_rel['branch_id'];
		$store_order_id = $work_rel['store_order_id'];
		$extra_stock = $work_rel['extra_stock'];
		$ext_stock_vendor_id = $work_rel['ext_stock_vendor_id'];

		if(!empty($store_order_id) || $store_order_id != '0'){
			$doc_query="select doc_no  from  tbl_store_order_min_max where order_id=" . $store_order_id;
			$doc_rel=brp_mysqli_fetch_assoc($dbcon->query($doc_query));	
			$doc_no = $doc_rel['doc_no'];
		}

		$bom_costing_id = $work_rel['bom_costing_id'];
		$customer_req_material = $work_rel["customer_req_material"];
		$customer_req_grade = $work_rel["customer_req_grade"];
		$customer_req_size = $work_rel["customer_req_size"];
		$customer_req_id = $work_rel["customer_req_id"];
		$customer_req_length = $work_rel["customer_req_length"];
		$customer_req_heat = $work_rel["customer_req_heat"];
		$customer_req_coc = $work_rel["customer_req_coc"];
		$customer_ref_no = $work_rel["customer_ref_no"];
		$customer_asset_serial = $work_rel["customer_asset_serial"];
		$customer_bevel_spec = $work_rel["customer_bevel_spec"];
		

		
		$id = $work_rel['product_id'];
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		 $query1="select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 and req.rp_pid=".$rel["product_id"]." AND req.sp_id=".$sp_id."  group by req.rp_pid";
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));
		
		$total=$rel1['reqqty'];
		
		$version_id=$work_rel['bom_version_id']; 
		$po_req_no = $work_rel['po_req_no'];
		
		$bom_version_name = get_bom_version_name($dbcon,$version_id);

		if($version_id == '10000'){
			$bom_version_name ="R&D";
		}
		
		
		if($total<0){
			$total=0;
		}
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=brp_mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT sp_id,branch_id,remark FROM `tbl_set_main_process` WHERE  product_id=".$id." AND sp_id=".$sp_id;
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$remark = $bom_rel_q['remark'];
		$work_order_id=$bom_rel_q['sp_id'];
		$select_branchId = $bom_rel_q['branch_id'];
		if(!empty($select_branchId)){
			$branch_read=true; 
		}else{
			$branch_read==false;
		}
	
		// pathik start date : 12-12-2020
		//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=1;
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'work_order';
		$work_order_id= $sp_id;


			
		$check_process_query="SELECT rp_id,customer_id,sales_order_trn_id,jobwork_type,reject_status FROM `tbl_request_product` WHERE   sp_id=".$sp_id." AND main_request = '1'";
		$check_process_result=$dbcon->query($check_process_query);
		$main_rp_row = brp_mysqli_fetch_array($check_process_result);
		$job_work_type  = $main_rp_row['jobwork_type'];
		$reject_status = $main_rp_row['reject_status'];
		$customer_id = $main_rp_row['customer_id'];
		$so_trn_id = $main_rp_row['sales_order_trn_id'];
		$rp_id = $main_rp_row['rp_id']; 
		
	}
else if(strpos($_SERVER['REQUEST_URI'], "workorder_permission")==true)
	{
		$mode="wo_permission";$direct_add='1';$request=1;$smode="";		
		$sp_id=$dbcon->real_escape_string($_REQUEST['edit_id']);
		$work_order_id = $sp_id;
		
		$work_query="select * from tbl_set_main_process where sp_id='$sp_id'";
		$work_rel=brp_mysqli_fetch_assoc($dbcon->query($work_query));	
		$remark = $work_rel['remark'];
		
		$id = $work_rel['product_id'];
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		 $query1="select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 and req.rp_pid=".$rel["product_id"]." AND req.sp_id=".$sp_id."  group by req.rp_pid";
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));		
		$total=$rel1['reqqty'];		
		$version_id=$work_rel['bom_version_id']; 
		$po_req_no = $work_rel['po_req_no'];
		$bom_version_name = get_bom_version_name($dbcon,$version_id);
		
		if($total<0){
			$total=0;
		}
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id' and bom_version_id='$version_id'");
		$row1=brp_mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT sp_id,branch_id,remark FROM `tbl_set_main_process` WHERE  product_id=".$id." AND sp_id=".$sp_id;
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$remark = $bom_rel_q['remark'];
		$select_branchId = $bom_rel_q['branch_id'];
		if(!empty($select_branchId)){
			$branch_read=true; 
		}else{
			$branch_read==false;
		}
		// pathik start date : 12-12-2020
		//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
		left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=1;
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'work_order';
	}
else if(strpos($_SERVER['REQUEST_URI'], "direct_workorder")==true)
	{
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$branch_id=$dbcon->real_escape_string($_REQUEST['branch_id']);
		$version_id=$dbcon->real_escape_string($_REQUEST['version_id']);
		$total=$dbcon->real_escape_string($_REQUEST['qty']);
		$sp_id=$dbcon->real_escape_string($_REQUEST['sp_id']);


		$bom_version_name = get_bom_version_name($dbcon,$version_id);
		if($version_id == '10000'){
			$bom_version_name ="R&D";
		}
		
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		 // $query1="select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid,req.rp_id from tbl_request_product as req where req.status=0 and used_status=0 and req.rp_pid=".$rel["product_id"]." AND req.sp_id=".$sp_id." group by req.rp_pid";

		  $query1="select req.rp_req_qty,req.used_rp_req_qty,req.rp_pid,req.rp_id from tbl_request_product as req where req.status=0 and used_status=0 and main_request = 1 and req.rp_pid=".$rel["product_id"]." AND req.sp_id=".$sp_id;
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));
		
		$rp_id = $rel1['rp_id'];
		
		/*$total=$min_stock-($opening+$rel1['reqqty']);
		if($total<0){
			$total=0;
		}
		$total=$dbcon->real_escape_string($_REQUEST['qty']);*/
		$sel1=$dbcon->query("select * from tbl_bom where bom_status = 0 and bom_product='$id' and bom_version_id = " .$version_id);
		$row1=brp_mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT po_req_no,sp_id,branch_id,extra_stock,ext_stock_vendor_id,remark FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and sp_id=".$sp_id;
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$remark = $bom_rel_q['remark'];
		$select_branchId = $bom_rel_q['branch_id'];
		$po_req_no = $bom_rel_q['po_req_no'];
		$extra_stock = $bom_rel_q['extra_stock'];
		$ext_stock_vendor_id = $bom_rel_q['ext_stock_vendor_id'];
		
		
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		/*echo "select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'";*/
		// pathik start date : 12-12-2020
		//bom check if yes process qty show other wise hidden and purchase qty only show 
		 /*$sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");*/
		$sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where  bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'work_order';
	}
	else if(strpos($_SERVER['REQUEST_URI'], "sorequesproduct")==true)
	{
		$style = "style='display:none;'";
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);

		$wo_type = "so_request";
	
		$so_trn_id=$dbcon->real_escape_string($_REQUEST['so_trn_id']);
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		
		 $query1="select req.*,`so`.`sales_order_no`, `so`.`cust_id`, `req`.`sales_order_id`, `so`.`sales_order_date`,`so`.`po_no`, `so`.`po_date`,req.branch_id,so.jobwork_type from tbl_sales_ordertrn as req
		 left join tbl_sales_order as so ON `req`.`sales_order_id` = `so`.`sales_order_id`
 		 where req.sales_ordertrn_status=0 and req.sales_ordertrn_id=".$so_trn_id." group by req.sales_ordertrn_id";
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));
		$sales_order_id = $rel1['sales_order_id'];
		$bom_id=$rel1['bom_id'];
		$branchId = $rel1['production_branch_id'];
		$job_work_type = $rel1['jobwork_type'];
		//$select_branchId = $rel1['branch_id'];
		$select_branchId = $rel1['production_branch_id'];
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}

		$is_added = 0;
		
		$query_p="select IFNULL(sum(product_qty),0) as used_qty from tbl_sales_order_production_trn as req
		 where req.sales_order_production_status=0 and req.sales_ordertrn_id=".$so_trn_id." group by req.sales_ordertrn_id";
		$rel1p=brp_mysqli_fetch_assoc($dbcon->query($query_p));

		
						
		$total=$rel1['product_qty']-$rel1p['used_qty'];
		
		if($rel['bom_required']==1){
			$totalpro=$total;
			$totalpo=0;
		}else{
			$totalpo=$total;
			$totalpro=0;
		}

		if($company_config['workorder_planning'] == '1'){
			if($total > 0){
				$total = 1;
				if($rel['bom_required']==1){
					$totalpro=1;
					$totalpo=0;
				}else{
					$totalpo=1;
					$totalpro=0;
				}
			}
		}
		

		/* $total=$min_stock-($opening+$rel1['reqqty']);
		if($total<0){
			$total=0;
		} */
		
		
		/*$sel11=$dbcon->query("select * from tbl_set_main_process where  sp_status!=2 and sales_order_trn_id='$so_trn_id'");
		$row11=brp_mysqli_fetch_assoc($sel11);
		$sp_id=$row11['sp_id'];*/
		//	$sp_id=$dbcon->real_escape_string($_REQUEST['id']);
 
		/*$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=mysqli_fetch_array($sel1);*/

		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		/*$bom_q="SELECT * FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id." and sales_order_trn_id=".$so_trn_id." and company_id=".$_SESSION['company_id'];
		$bom_q_res = $dbcon->query($bom_q);
		$bom_rel_q=brp_mysqli_fetch_assoc($bom_q_res);
		$work_order_id=$bom_rel_q['sp_id'];


		if(brp_mysqli_num_rows($bom_q_res)>0)
		{
			$customer_req_material = $bom_rel_q["customer_req_material"];
			$customer_req_grade = $bom_rel_q["customer_req_grade"];
			$customer_req_size = $bom_rel_q["customer_req_size"];
			$customer_req_id = $bom_rel_q["customer_req_id"];
			$customer_req_length = $bom_rel_q["customer_req_length"];
			$customer_req_heat = $bom_rel_q["customer_req_heat"];
			$customer_req_coc = $bom_rel_q["customer_req_coc"];
			$customer_ref_no = $bom_rel_q["customer_ref_no"];
			$customer_asset_serial = $bom_rel_q["customer_asset_serial"];
			$customer_bevel_spec = $bom_rel_q["customer_bevel_spec"];

		}
		$branchId = $bom_rel_q['branch_id'];*/

		if($bom_id == 0 || $bom_id == ""){
			$ver_qry = "select bom_version_id from pro_ms_bom_version where product_id = ".$id." and is_default_bom = 1 and bom_version_status = 0 and company_id = " . $_SESSION['company_id']; 

			$ver_rel_q=brp_mysqli_fetch_assoc($dbcon->query($ver_qry));

			$version_id = $ver_rel_q['bom_version_id'];

			$sel1_1=$dbcon->query("select * from tbl_bom where bom_product='$id' and bom_version_id ='$version_id'");
			$row1_1=brp_mysqli_fetch_array($sel1_1);
			$bom_id=$row1_1['bom_id'];
			
		}
		// pathik start date : 12-12-2020
			//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select bom_version_id from tbl_bom as bom
								where bom.bom_id='$bom_id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$version_id = $row123['bom_version_id'];


		$bom_version_name = get_bom_version_name($dbcon,$version_id);
		
		// $check_process_query="SELECT rp_id,customer_id FROM `tbl_request_product` WHERE   sp_id=".$sp_id." AND main_request = '1'";
		// $check_process_result=$dbcon->query($check_process_query);
		// $main_rp_row = brp_mysqli_fetch_array($check_process_result);

		// $customer_id = $main_rp_row['customer_id'];
		
	 // 	$bom_query = "select * from pro_bom_process where bom_version_id IN (select bom_version_id from tbl_bom where bom_id = '$bom_id')";
		// $bom_result=$dbcon->query($bom_query);
		
		// if(brp_mysqli_num_rows($bom_result)>0)
		// {
		
		// 	while($bom_row=brp_mysqli_fetch_array($bom_result))
		// 	{	
		// 		$rp_id = $main_rp_row['rp_id'];
		// 		$process_id= $bom_row['pr_process_id'];
		// 		$check_query = "select * from tbl_wororder_product_process where product_id = '$id' AND rp_id = '$rp_id' AND process_id = '$process_id'";
		// 		$check_result=$dbcon->query($check_query);
		// 		if($rp_id > 0){

		// 			// $is_added = 1;
		// 			if(brp_mysqli_num_rows($check_result)<1)
		// 			{	
					
		// 				$check_prodcut_query="SELECT process_time,process_type,process_opening,process_id FROM `tbl_product_process` WHERE pr_process_id = '$process_id'";
		// 				$check_prodcut_result=$dbcon->query($check_prodcut_query);
		// 				$check_prodcut_row = brp_mysqli_fetch_array($check_prodcut_result);
					
								
		// 				$info_wororder_process['product_id']		= $id;
		// 				$info_wororder_process['rp_id']				= $rp_id;
		// 				$info_wororder_process['process_priority']	= $bom_row['priority'];
		// 				$info_wororder_process['process_time']		= $check_prodcut_row['process_time'];
		// 				$info_wororder_process['process_type']		= $check_prodcut_row['process_type'];
		// 				$info_wororder_process['process_opening']	= $check_prodcut_row['process_opening'];
		// 				$info_wororder_process['process_id']		= $check_prodcut_row['process_id'];
		// 				$info_wororder_process['cdate']				= date("Y-m-d H:i:s");
		// 				$info_wororder_process['user_id']			= $_SESSION['user_id'];
		// 				$info_wororder_process['company_id']		= $_SESSION['company_id'];
		// 				$info_wororder_process['branch_id']			= $_SESSION['branch_id'];				
		// 				// $job_work_trn_id=add_record('tbl_wororder_product_process',$info_wororder_process, $dbcon,$branch_id);
		// 			}
		// 		}
		// 	}
		// }else{
		// 	// if($main_rp_row['rp_id'] > 0){
		// 	// 	$is_added = 1;	
		// 	// }
		// }
		
		
		//echo "<pre>"; print_r($bom_row);die;
		$bom_check=1;
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'get_sales_order_details';
	}
	else if(strpos($_SERVER['REQUEST_URI'], "rejectrequestproduct")==true)
	{
		$mode="Add";$direct_add='1';$request=1;$smode="add_rej";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		//$parent_po_ref_id=$dbcon->real_escape_string($_REQUEST['po_ref_id']);
		//$query="select * from product_mst where product_id='$id'";
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		
		/*$query11="select sum(reject_qty-reject_request_qty) as qty from tbl_qc_process_trn 
		where reject_qty!=0 and reject_request_qty<reject_qty and qc_process_status=0 and product_id=".$id." group by product_id";
		$rs11=$dbcon->query($query11);
		
		$row111=mysqli_fetch_array($rs11);
		$total=$row111['qty'];*/
		
		$set11="select rp.*,sum(reject_qty-reject_request_qty) as pending_qty from tbl_qc_process_trn as rp
			where rp.qc_process_status=0 and rp.reject_qty>0 and CAST(reject_qty as DECIMAL(50,2)) > CAST(reject_request_qty as DECIMAL(50,2)) and rp.product_id=".$id." group by rp.product_id";
		$ser=$dbcon->query($set11);
		$set_row=brp_mysqli_fetch_assoc($ser);
		$total=$set_row['pending_qty'];
		
		if($total<0){
			//$total=0;
		} 
		//echo $total;
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=brp_mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		$version_id=$row1['bom_version_id'];
		$select_branchId = $set_row['branch_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT sp_id,remark FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id;
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$remark = $bom_rel_q['remark'];
		$remark = $bom_rel_q['remark'];
		$reject_status=1;
		
		// pathik start date : 12-12-2020
			//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
		$cancel_url = ROOT.PRODUCTION_ROOT.'get_sales_order_details';
		
	}else if(strpos($_SERVER['REQUEST_URI'], "stock_pending_product")==true)
	{

		$mode="Add";$direct_add='1';$request=1;$smode="add_all";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		//$query="select * from product_mst where product_id='$id'";
		$query="select mst.*, tc.cat_name from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		
		$reserv=reserve_stock($dbcon,$rel['product_id'],$rel['product_base_unit'],$reserve_id,'','','','','');
		$current=get_current_stock_new($dbcon,$rel['product_id'],$rel['product_base_unit']);
		$pendun=get_part_invoice_not_done_send($dbcon,$rel['product_id']);
		$tot=($current-($reserv+$pendun));
		
		$tot=request_all_department_request_qty($dbcon,$rel['product_id']);

		$total=abs($tot);
		$sel1=$dbcon->query("select * from tbl_bom where bom_product='$id'");
		$row1=brp_mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		
		$bom_q="SELECT sp_id,remark FROM `tbl_set_main_process` WHERE sp_status=0 AND product_id=".$id;
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$remark = $bom_rel_q['remark'];
		// pathik start date : 12-12-2020
			//bom check if yes process qty show other wise hidden and purchase qty only show 
		 $sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'get_stock_detail';
	}else if(strpos($_SERVER['REQUEST_URI'], "direct_jobcard")==true)
	{
		$wo_type = "direct_jobcard";
		$wo_no_title = "Jobcard No";
		$wo_dt_title = "Jobcard Date";
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$branch_id=$dbcon->real_escape_string($_REQUEST['branch_id']);
		$version_id=$dbcon->real_escape_string($_REQUEST['version_id']);
		$total=$dbcon->real_escape_string($_REQUEST['qty']);
		$rp_id=$dbcon->real_escape_string($_REQUEST['jobcard_rp_id']);

		$bom_version_name = get_bom_version_name($dbcon,$version_id);
		
		
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		 $query1="select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 and req.rp_pid=".$rel["product_id"]." AND req.rp_id=".$rp_id." group by req.rp_pid";
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));
		
		// $rp_id = $rel1['rp_id'];
		
		/*$total=$min_stock-($opening+$rel1['reqqty']);
		if($total<0){
			$total=0;
		}
		$total=$dbcon->real_escape_string($_REQUEST['qty']);*/
		$sel1=$dbcon->query("select * from tbl_bom where bom_status = 0 and bom_product='$id' and bom_version_id = " .$version_id);
		$row1=brp_mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT job_card_no,sp_id,branch_id FROM `tbl_request_product` WHERE status=0 AND rp_pid=".$id." and rp_id=".$rp_id." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$select_branchId = $bom_rel_q['branch_id'];
		$po_req_no = $bom_rel_q['job_card_no'];
		
		
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		/*echo "select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'";*/
		// pathik start date : 12-12-2020
		//bom check if yes process qty show other wise hidden and purchase qty only show 
		 /*$sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");*/
		$sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where  bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'job_card_list';
	}else if(strpos($_SERVER['REQUEST_URI'], "jobcardedit")==true)
	{
		
		$wo_type = "direct_jobcard";
		$wo_no_title = "Jobcard No";
		$wo_dt_title = "Jobcard Date";
		$mode="Add";$direct_add='1';$request=1;$smode="";
		$id=$dbcon->real_escape_string($_REQUEST['id']);
		$branch_id=$dbcon->real_escape_string($_REQUEST['branch_id']);
		$version_id=$dbcon->real_escape_string($_REQUEST['version_id']);
		$total=$dbcon->real_escape_string($_REQUEST['qty']);
		$rp_id=$dbcon->real_escape_string($_REQUEST['jobcard_rp_id']);
		$select_branchId = $branch_id;
		$bom_version_name = get_bom_version_name($dbcon,$version_id);
		// echo "cerfer";
		
		$query="select mst.*, tc.cat_name  from product_mst as mst left join tbl_category as tc on mst.product_category=tc.cat_id where mst.product_id='$id'";
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$unit_id = $rel["product_base_unit"];
		$pr_setting=explode(",",$rel['product_setting_check']);
		$pr_type=$rel['product_type'];
		$min_stock=$rel['product_min_stock'];
		$category_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
		$opening=get_current_stock_new($dbcon,$rel["product_id"],$rel["product_base_unit"]);
		 $query1="select sum(req.rp_req_qty-req.used_rp_req_qty) as reqqty,req.rp_pid from tbl_request_product as req where req.status=0 and used_status=0 and req.rp_pid=".$rel["product_id"]." AND req.rp_id=".$rp_id." group by req.rp_pid";
		$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1));
		
		// $rp_id = $rel1['rp_id'];
		
		/*$total=$min_stock-($opening+$rel1['reqqty']);
		if($total<0){
			$total=0;
		}
		$total=$dbcon->real_escape_string($_REQUEST['qty']);*/
		$sel1=$dbcon->query("select * from tbl_bom where bom_status = 0 and bom_product='$id' and bom_version_id = " .$version_id);
		$row1=brp_mysqli_fetch_array($sel1);
		$bom_id=$row1['bom_id'];
		if(in_array("process_product",$pr_setting))
		{
			//$readonly="";
			$process_status="process_product";
		}
		else
		{
			$process_status="";
			//$readonly="readonly";
		}
		$readonly="readonly";
		//echo $readonly;
		$bom_q="SELECT job_card_no,sp_id,branch_id FROM `tbl_request_product` WHERE status=0 AND rp_pid=".$id." and rp_id=".$rp_id." and user_id=".$_SESSION['user_id'];
		$bom_rel_q=brp_mysqli_fetch_assoc($dbcon->query($bom_q));
		$work_order_id=$bom_rel_q['sp_id'];
		$select_branchId = $bom_rel_q['branch_id'];
		$po_req_no = $bom_rel_q['job_card_no'];
		
		
		if(!empty($select_branchId)){
			$branch_read=true;
		}else{
			$branch_read==false;
		}
		/*echo "select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'";*/
		// pathik start date : 12-12-2020
		//bom check if yes process qty show other wise hidden and purchase qty only show 
		 /*$sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where bom_status=0 and bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");*/
		$sel112=$dbcon->query("select count(btrn.bom_trn_id) as bcou from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
		where  bom.bom_actual_add_status=0 and btrn.bom_trn_status=0 and bom.bom_product='$id'");
		$row123=brp_mysqli_fetch_array($sel112);
		$bom_check=$row123['bcou'];
		// pathik end
		
		$cancel_url = ROOT.PRODUCTION_ROOT.'job_card_list';
	}else
	{
	
		$mode="Add";$direct_add='0';$request=0;
		$purchaseorder_date=date('d-m-Y');
		$po_type_status='';
	}
	
	//echo $mode;

	if(isset($work_order_id) && !empty($work_order_id)){
		$add_attachment = 1;	
	}
	
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>REQUEST PRODUCT</title>
		<?php include_once($include.'include_css_file.php');?>
		<link href="<?= ROOT ?>assets/sweetalert2/sweetalert2.min.css" rel="stylesheet">
		<style >

			#maintable thead {
			  position: sticky;
			  top: 0;
			  border-bottom: 2px solid #ccc;
			  z-index: 1030;
			  background-color: #5bc0de;
			  height: 70px;
			  color: white;
			 
			}
			#maintable thead th{
 				vertical-align: middle;
 				font-size: large;
			}
			/*.sticky {
			  position: sticky;
			  top: 0;
			 
			}*/
			.error {
				font-weight: bold;
				color: #ef1717;
				
				font-size: 16px;
			}
			 #process_left,#process_right{
   margin: 5px;
    border: 1px solid #cccccc;
    list-style: none;
    padding-left: 0;
    height: 200px;
    overflow: auto;
    /* width: 250px; */
    border-radius: 5px;
  }
.mb-5{
	margin-bottom: 5px;
}
  ul li{
    cursor: pointer;
    padding: 5px 10px;
  }


  .selected{
    background-color: blue;
    color: white;
     margin: 2px;
  }

  .bigBtn{
    height: 50px;
    width: 55px;
    margin-top: 35px;
    margin-left: -5px;
    font-size: 20px;
    font-weight: 900;
  }

		</style>
	</head>
	<body>
		<section id="container" class="sidebar-closed">
			<?php include_once($include.'include_top_menu.php');?>
			<?php include_once($include.'left_menu.php');?>
			<section id="main-content">
				<section class="wrapper">
					<div class="row">
						<div class="col-lg-12">
							<section class="panel">
								<header class="panel-heading">
									<h3><?=$mode.' '.$form?></h3>
								</header>	
								<div class="">
									<ul class="breadcrumb">
										<li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
										<?php if(strpos($_SERVER['REQUEST_URI'], "direct_jobcard")==true || strpos($_SERVER['REQUEST_URI'], "jobcardedit")==true)	{ ?>
											<li><a href="<?=ROOT.PRODUCTION_ROOT.'job_card_list'?>">Jobcard List</a></li>
										<?php } else{ ?>
											<li><a href="<?=ROOT.PRODUCTION_ROOT.'work_order'?>"><?=$form?> List</a></li>
										<?php } ?>
									</ul>
								</div>
							</section>
						</div>	
					</div>
					<div class="row">			
						<div class="col-sm-12">
							<section class="panel">
								<header class="panel-heading">
								  New <?=$form?>
								</header>	
								<div class="panel-body">
									<form class="form-horizontal" role="form" id="product_request_add" action="javascript:;" method="post" name="product_request_add">
										<input type="hidden" id="cust_id" name="cust_id" value="<?=$rel1['cust_id']?>">
										<input type="hidden" id="sales_order_date" name="sales_order_date" value="<?=$rel1['sales_order_date']?>">
										<input type="hidden" id="po_no" name="po_no" value="<?=$rel1['po_no']?>">
										<input type="hidden" id="po_date" name="po_date" value="<?=$rel1['po_date']?>">
										<input type="hidden" id="sales_order_no" name="sales_order_no" value="<?=$rel1['sales_order_no']?>">
										<input type="hidden" id="bom_version_id" name="bom_version_id" value="<?=@$version_id;?>">
										<input type="hidden" id="po_req_nos" name="po_req_nos" value="<?=@$po_req_no;?>">
										
										<div class="row">
											<div class="col-md-12">
										<?php if($rel1['sales_order_date'] != '' && $rel1['sales_order_no'] != '' ) { ?> 
										
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Sales Order No</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="" name="" type="text" class="form-control" title="Req No" value="<?=$rel1['sales_order_no']?>"  readonly >
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>sales order date</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="" name="" type="text" class="form-control default-date-picker" title="Date" value="<?=date("d-m-Y",strtotime($rel1['sales_order_date']));?>" readonly disabled>
														</div>
													</div>	
												 </div>	
												<?php } ?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>BOM Version Name</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="" name="" type="text" class="form-control" title="BOM Version Name" value="<?=$bom_version_name?>" readonly>
														</div>
													</div>	
												 
											</div>
											</div>
										
										
											<div class="col-md-12">
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong><?=$wo_no_title;?></strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="po_req_no" name="po_req_no" type="text" class="form-control" title="Req No" value="" placeholder="" readonly>
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong><?=$wo_dt_title;?></strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="po_req_date" name="po_req_date" type="text" class="form-control default-date-picker" title="Date" readonly disabled value="<?php echo date('d-m-Y'); ?>" placeholder="">
														</div>
													</div>	
												</div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Product Name </strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="po_product_name" name="po_product_name" type="text" class="form-control" title="Date" value="<?=$rel['product_name'].'--( '. get_product_type_by_id($dbcon,$rel['product_type'],'product_type').' )' ?>" placeholder="Product Name" readonly>
														</div>
													</div>	
												</div>
											</div>	
											<!--<div class="col-md-12" style="margin-top:10px;">
													
												
											</div>-->
											<div class="col-md-12" style="margin-top:10px;">
												<div class="col-md-4">
													<div class="form-group">  	
														<label class="col-md-4 control-label" ><strong> Request Quantity </strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="rp_req_qty" name="rp_req_qty" type="text" class="form-control" title="" value="<?=$total?>" placeholder="Request Qty" onkeypress="return isNumberKey(event)" onkeyup="get_bom_request_qty(this.value);" onchange="cal_po_qty();" >
														</div>
													</div>	
												</div>
									<?php 
										$check_process_query="SELECT rp_id FROM `tbl_request_product` WHERE sp_id='".$sp_id."' AND main_request = 1";
										$check_process_result=$dbcon->query($check_process_query);
										$main_rp_row = brp_mysqli_fetch_array($check_process_result);
										
									?>
												
												
												<div class="col-md-4 proc1">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Process Qty</strong></label>
														<div class="col-md-8 col-xs-11">
														<?php //=$readonly;?>
															<input id="in_process_qty_main" name="in_process_qty" type="number" class="form-control" title="Date" value="<?=$totalpro?>" placeholder="Inhouse Process Qty"  onkeyup="get_inhouse_request_qty(this.value);get_bom_request_qty(this.value);get_po_request_qty(this.value);cal_po_qty();"/>
															
														</div>
													</div>	
												</div>	
												<div class="col-md-4">
													<div class="form-group">  	
														<label class="col-md-4 control-label" ><strong> Purchase Qty </strong></label>
														<div class="col-md-5 col-xs-11">
															<input id="rp_po_qty" name="rp_po_qty" type="text" class="form-control" title="Date" value="<?=$totalpo?>" placeholder="PO Qty" onkeypress="return isNumberKey(event)" onchange="cal_po_qty();" >
														</div>
														<div class="col-md-2 proc1">
															<a class="btn btn-primary mainRequest" data-original-title="" data-toggle="tooltip" data-placement="top" onclick="main_po_reqdata();" ><i class="fa fa-paper-plane"></i> Request</a>
															
															<a class="btn btn-danger mainRequested" data-original-title="" data-toggle="tooltip" data-placement="top" > Requested</a>
															
															<input type="hidden" name="main_poreq_status" id="main_poreq_status" value="" />
														</div>
													</div>	
												</div>
											</div>
											<div class="col-md-12" style="margin-top:10px;">
											<?php if($branch_id=='0'){ ?>
												<div class="col-md-4">
													<?php echo getBranchBox($dbcon, $branch_id,$select_branchId, $branch_read, true, ''); ?>	
												</div>
											
											<?php }else{ ?>
												<input type="hidden" name="branch_id" id="branch_id" value="<?=$branch_id?>">
											<?php } ?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-4 control-label"><strong>Category Name</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="category_name" name="category_name" type="text" class="form-control" title="Category Name" value="<?=$category_name?>" readonly placeholder="Category Name" >
														</div>
													</div>	
												</div>
											
											<div class="col-md-4"  <?php if(strpos($_SERVER['REQUEST_URI'], "edit_workorder")==true){ ?> style="display: block;"<?php }?>>
												<div class="form-group">  	
													<div class="col-md-3 col-xs-11">
														<button <?=$style?> type="button" id="add_wo_prd" onclick="add_work_order_product('<?php echo $id;?>','<?=$total?>','<?=$unit_id?>');" class="btn btn-success" >Add Product</button>
														
													</div>
													<?php if($getspecialConfiguration['smpl_permission'] !=1)
													{?>
													<div class="col-md-3 col-xs-11">

													<button id="btn_process_main" <?=$style?> type="button" onclick="direct_show_product_process()" class="btn btn-success" > <span id="process_mode">Add</span> Process</button>	

																							</div>	<?php } ?>
													<div class="col-md-3 col-xs-11">
															<button type="button" id="btn_bom_doc" onclick="view_documents('<?=$bom_id?>','<?=$version_id?>');" class="btn btn-info" >View Documents</button>
													</div>
												<div class="col-md-3 col-xs-11 " >
													<button type="button" id="btn_process_stock" class="btn btn-warning" onclick="show_process_stock('<?=$work_order_id?>','<?=$rp_id?>')">Add Process Stock</button>
												</div>
												<div class="col-md-3 col-xs-11 btn_unrequest_process_stock" >
													<button type="button" id="btn_unrequest_process_stock" class="btn btn-danger" onclick="unrequest_process_stock('<?=$work_order_id?>','<?=$rp_id?>')">Unrequest Process Stock</button>
												</div>
													<!--auto mrp pathik 21-1-2022 -->
													
													<!-- Jayesh for company setting display auto marp -->
													<?php if($company_config['automrp_display'] == 1) { ?>
														
													
													<div class="col-md-3 col-xs-11" style="margin-top: 10px;">
														<button type="button"  onclick="auto_mrp_question();" class="btn btn-warning automrp" >Run Auto MRP</button>
													</div>
													<?php } ?>

													<?php if($work_order_id != "" && $work_order_id > 0 && $rp_id != "" && $rp_id > 0 ) { ?>
														<div class="col-md-3 col-xs-11" style="margin-top: 10px;">
														<button type="button" id="btn_product_remark1" onclick="show_product_remark_modal(<?=$rp_id?>);" class="btn btn-info " >Add Remark</button>
													</div>
													<?php } ?>

													
													<!-- Jayesh for company setting display auto marp -->
													<!--auto mrp pathik 21-1-2022 -->
													
												</div>	
											</div>
											<?php if(strpos($_SERVER['REQUEST_URI'], "direct_jobcard")!=true || strpos($_SERVER['REQUEST_URI'], "jobcardedit")!=true)	{ ?>	
											<div class="col-md-4">
												<div class="form-group">
														<label class="col-md-4 control-label"><strong>BOM Costing *</strong></label>
														<div class="col-md-8 col-xs-11">
															<select class="select2" name="bom_costing_id" id="bom_costing_id">
																<!-- <option value="">Select Costing Template</option> -->
																<=get_bom_costing($dbcon,@$id,@$bom_id,@$bom_costing_id);?>
															</select>
														</div>
													</div>
											</div>
											<?php } ?>	

											<?php if(!empty($store_order_id) || $store_order_id != '0'){ ?>	
											<div class="col-md-4">
												<div class="form-group">
														<label class="col-md-4 control-label"><strong>Document No</strong></label>
														<div class="col-md-8 col-xs-11">
															<input id="doc_no" name="doc_no" type="text" class="form-control" title="Document No" value="<?=$doc_no?>" readonly placeholder="Document No">
														</div>
													</div>
											</div>
											<?php } ?>	
											
											</div>

											<?php if($getspecialConfiguration['oilfield_permission'] == '1'){ ?>
												<div class="col-md-12">
													<div class="col-md-12 mb-5">
														<label class="col-md-12 control-label label label-info" style="font-size:16px; text-align: center;"><strong>Customer Requirement</strong></label>
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>Material</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_req_material" name="customer_req_material" type="text" class="form-control" placeholder="Enter Material" value="<?php echo @$customer_req_material ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>Grade</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_req_grade" name="customer_req_grade" type="text" class="form-control" placeholder="Enter Grade" value="<?php echo @$customer_req_grade ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>Size</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_req_size" name="customer_req_size" type="text" class="form-control" placeholder="Enter Size" value="<?php echo @$customer_req_size ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>ID</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_req_id" name="customer_req_id" type="text" class="form-control" placeholder="Enter ID" value="<?php echo @$customer_req_id ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>Length</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_req_length" name="customer_req_length" type="text" class="form-control" placeholder="Enter Length" value="<?php echo @$customer_req_length ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>Heat#</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_req_heat" name="customer_req_heat" type="text" class="form-control" placeholder="Enter Heat#" value="<?php echo @$customer_req_heat ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>COC</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_req_coc" name="customer_req_coc" type="text" class="form-control" placeholder="Enter COC" value="<?php echo @$customer_req_coc ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>Customer Ref No.</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_ref_no" name="customer_ref_no" type="text" class="form-control" placeholder="Enter Customer Ref No." value="<?php echo @$customer_ref_no ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>Asset/Serial</strong></label>
															<div class="col-md-8 col-xs-11">
																<input id="customer_asset_serial" name="customer_asset_serial" type="text" class="form-control" placeholder="Enter Asset/Serial" value="<?php echo @$customer_asset_serial ?>">
															</div>
														</div>	
													</div>
													<div class="col-md-4 mb-5">
														<div class="form-group">
															<label class="col-md-4 control-label"><strong>Bevel Spec</strong></label>
															<div class="col-md-8 col-xs-11">
																<textarea id="customer_bevel_spec" name="customer_bevel_spec" rows="4" class="form-control" placeholder="Enter Bevel Spec">
																	<?php echo @$customer_bevel_spec ?>
																</textarea>
															</div>
														</div>	
													</div>

												</div>
											<?php } ?>
											<div class="col-md-12 col-md-offset-5" style="margin-top:10px;">
												<input type="button" id="set_process_btn" name="set_process_btn" class="btn btn-success" value="SET Process Request" onclick="set_main_process_request_qty();" />
												<!--show_btn(1,1);-->
											</div>	
											<div class="col-md-12 sticky" style="margin-top:10px;">
												<div id="req_val" class="sticky">
													<table id="maintable" class="table table-bordered" style="width: 100%;">
														<thead>
															<tr>					
																<th style="width: 5%;"><strong>SR. NO.</strong></th>
																<th style="width: 20%;"><strong>Item Description</strong></th>
																<!--<th><strong>Product Image</strong></th>
																<th><strong>Item Category</strong></th>
																<th><strong>Minimum Qty</strong></th>
																<th><strong>Item Type</strong></th>-->
																<th style="width: 13%;"><strong>Current Stock</strong></th>
																<th style="width: 13%;"><strong>Request Qty</strong></th>
																<th style="width: 13%;"><strong>Reserve Stock</strong></th>
																<th style="width: 13%;"><strong>Process Qty</strong></th>
																<th style="width: 13%;"><strong>PO Qty</strong></th>
																<th style="width: 10%;"><strong>Request</strong></th>
															</tr>
														</thead>
														<tbody id="show_tree_request">
														</tbody>
													</table>
												</div>
											</div>
											<div class="col-md-12">
												<div class="mtop20">
												<?php if($mode != "wo_permission"){?>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-3 control-label">Remarks </label>
														<div class="col-md-9 col-xs-11">
															<textarea id="remark" name="remark" class="form-control" rows="3"><?=$remark?></textarea> 
														</div>
													</div> 
												</div>
												<?php } ?>
													<?php if($add_attachment){?>
														<div class="col-md-4">
                                       <div class="form-group">
                                          <label class="col-md-4 control-label">Image Name </label>
                                          <div class="col-md-8 col-xs-11">
                                             <input id="image_name" name="image_name" type="text" class="form-control" title="Drawing Size" value="" placeholder="Drawing Image Name" >
                                          </div>
                                       </div>
                                    </div>
												<div class="col-md-4">
													<div class="form-group">
														<label class="col-md-3 control-label">Attachments :</label>
														<div class="col-md-6">
															<input type="file" name="workorder_file" id="workorder_file" class="form-control">
														</div>
														<div class="col-md-3">
															<button id="btn_add_attach" type="button" onclick="save_workorder_attachments();" class="btn btn-primary"> Add Attachment </button>
														</div>
													</div> 
												</div>

												<div class="col-md-12 mtop20">
			                                    <div id='wo_image_list'>
			                                       
			                                    </div>
			                                 </div>
												<?php } ?>
											</div>
											</div>
											<div class="col-md-12">
												<center>
												<?php if($mode != "wo_permission"){?>
													
												
												<input type="button" name="save" id="save" class="btn btn-success" value="save" onclick="get_main_form_submit();" /> 
												<a href="<?=$cancel_url?>" type="button" class="btn btn-danger">Cancel</a>
												<?php } ?>
												
												<!--<button type="submit" class="btn btn-success" id="save" name="save">Save</button>-->
							
												
												</center>
											</div>
										</div>
										<input type='hidden' name='redirect_url' id='redirect_url' value='<?=$cancel_url?>' />
										<input type='hidden' name='mode' id='mode' value='<?=$mode?>' />
										<input type='hidden' name='smode' id='smode' value='<?=$smode?>' />
										<input type='hidden' name='eid' id='eid' value='<?=$id;?>' />	
										<input type='hidden' name='pr_type' id='pr_type' value='<?=$pr_type;?>' />	
										<input type='hidden' name='bom_id' id='bom_id' value='<?=$bom_id;?>' />	
										<input type='hidden' name='process_status' id='process_status' value='<?=$process_status;?>' />	
										<input type='hidden' name='job_work_type' id='job_work_type' value='<?=$job_work_type;?>' />
										
										<input type="hidden" name="work_order_id" id="work_order_id" value="<?=$work_order_id?>" />
										<input type="hidden" name="is_reserve_godown" id="is_reserve_godown" value="<?=$is_reserve_godown?>" />
										<input type="hidden" name="default_godown_id" id="default_godown_id" value="<?=$default_godown_id?>" />

										<input type="hidden" name="reject_status" id="reject_status" value="<?=$reject_status?>" />
										
										<input type="hidden" name="bom_check" id="bom_check" value="<?=$bom_check?>" />
										<input type="hidden" id="product_add_type" value="">
										<input type="hidden" id="extra_stock" name="extra_stock" value="<?=$extra_stock?>">
										<input type="hidden" id="ext_stock_vendor_id" name="ext_stock_vendor_id" value="<?=$ext_stock_vendor_id?>">
										<input type="hidden" name="sales_order_trn_id" id="sales_order_trn_id" value="<?=$so_trn_id?>" />
										<input type="hidden" name="customer_id" id="customer_id" value="<?=$customer_id?>" />
										<input type="hidden" name="store_order_id" id="store_order_id" value="<?=@$store_order_id?>" />
										<input type="hidden" name="wo_type" id="wo_type" value="<?=@$wo_type?>" />
										<input type="hidden" name="sales_order_id" id="sales_order_id" value="<?=@$sales_order_id?>" />
										<input type="hidden" name="direct_reserve_stock" id="direct_reserve_stock" value="<?=@$direct_reserve_stock?>" />

										<?php if(strpos($_SERVER['REQUEST_URI'], "direct_jobcard")==true || strpos($_SERVER['REQUEST_URI'], "jobcardedit")==true)	{ ?>
												<input type="hidden" name="job_rp_id" id="job_rp_id" value="<?=@$rp_id?>" />
										<?php }  ?>
										<input type="hidden" name="wo_rp_id" id="wo_rp_id" value="<?=@$rp_id?>" />
									</form>
								</div>
							</section>
						</div>
					</div>		
				</section>
			</section>
			<?php include_once($include.'footer.php');?>
			<?php include_once($include1.'add_workorder_product.php');?>
			<?php include_once($include1.'add_workorder_sub_product.php');?>
			<?php include_once($include1.'current_stock.php');?>
			<?php include_once($include1.'update_product_process.php');?>
			<?php include_once($include1.'wo_process_add_model.php');?>   
			<?php include_once($include1.'wo_qc_model.php');?>  
			<?php include_once($include1.'work_order_permission_details.php');?> 
			<?php include_once($include1.'reserve_stock_entry_wo.php');?>
			<?php include_once($include1.'process_reserve_stock_entry_wo.php');?>
			<?php include_once($include1.'auto_mrp_model.php');?>
			<?php include_once($include1.'product_lead_and_process.php');?>
			<?php include_once($include1.'bom_document_view_model.php');?>
			<?php include_once($include1.'wo_product_wise_remark_modal.php');?>
			
			<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
			<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  

		</section>
		<div id="dialog" title="Permission Pending" style="display: none;">
		<p>Please Contact to  Authorise Person For Approve Requested Product</p>
		</div>
		
		<div id="delete_dialog" title="Do Yo Want to Delete  product ?">
		</div>
		<style>
			.ui-dialog .ui-dialog-content {
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 16px !important;
				}
				.ui-dialog .ui-dialog-titlebar {
				background: none !important;
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 20px !important;
				}
				.ui-widget-content {
				background: none !important;
				background-color: #5495ce !important;
				color: #FFF !important;
				font-size : 20px !important;
				}
		</style>
		<?php include_once($include.'include_js_file.php');?>     
		<script type='text/javascript' src='<?= ROOT ?>assets/sweetalert2/sweetalert2.all.min.js'></script>
		<script src="<?=ROOT.PRODUCTION_ROOT?>js/app/request_product.js?<?=time()?>"></script>
		 <script src="<?=ROOT?>js/advanced-form-components.js"></script>
		<script>
		
		 
			$(".select2").select2({
				width: '100%'
			});
			/*$("#product_id").select2({
				width: '100%'
			});*/
			$('.default-date-picker').datepicker({
				format: 'dd-mm-yyyy',
				autoclose: true
			});
			
			

			$(".form_datetime").datetimepicker({
				format: 'dd-mm-yyyy hh:ii',
				autoclose: true,
				todayBtn: true,
				pickerPosition: "bottom-left"

			});
			function add_customer_purchase()
			{
				$("#bs-example-modal-lg").modal("show");
				$("#cat_id").val('1');
			}
			function consinee_change(val){
				if(val=='1'){
					$('#consignee_id').select2("val","");
					$('#consignee').hide();
				}
				else{
					$('#consignee').show();
				}
			}
		</script>
		<?php /* if(strpos($_SERVER['REQUEST_URI'], "sorequesproduct")==true)
			{
				if($is_added == 0){
					echo "<script>set_main_process_request_qty();</script>";
				}
			} */
		?>

		<?php 
			echo "<script>toggle_process_stock_button('".$work_order_id."','".$rp_id."');</script>";
		?>

	</body>
</html>
