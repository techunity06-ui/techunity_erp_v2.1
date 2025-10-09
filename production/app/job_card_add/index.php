<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// include('../../include/common_function.php');
// error_reporting(E_ALL);
$company_config = getCompanyConfiguration($dbcon);
//print_r($_POST);
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
		}else if(brp_strtolower($POST['mode']) == "check_bom_version_by_product") {
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
		
	}
		
		else if(brp_strtolower($POST['mode']) == "save_work_order_product") {


			$product_id = $POST['product_id'];
			$priority_status = $POST['priority_status'];
			$bom_qry1="SELECT * FROM `tbl_bom` WHERE  bom_version_id='".$POST['bom_version_id']."' AND bom_product='".$product_id."'";
			$bom_res1=brp_mysqli_fetch_assoc($dbcon->query($bom_qry1));

			$bom_id = $bom_res1['bom_id'];
			// print_r($POST);die;

			$jobcard_no=load_common_no($dbcon,JOBCARD);
			// var_dump($jobcard_no);
			// update_common_no($dbcon,JOBCARD);

			$pro_qry = "select * from  product_mst where product_id = " . $product_id; 
			
			$pro_rs=$dbcon->query($pro_qry);
			$pro_row = brp_mysqli_fetch_array($pro_rs);

			$info_su['job_card_status']		= 1;
			$info_su['job_card_no']			= $jobcard_no;
			$info_su['job_card_date']		= date("Y-m-d");
			
			$info_su['sr_no']				= 0;
			$info_su['rp_pid']				= $POST['product_id'];//product_id
			$info_su['rp_req_qty']			= $POST['qty'];//required qty
			$info_su['req_qty_one']			= 1;
			$info_su['rp_po_qty']			= 0;//po qty
			$info_su['in_process_qty']		= $POST['qty'];//process qty
			$info_su['rp_req_type']			= "job_card";//type
			$info_su['process_unit']		= $pro_row['product_base_unit'];
			$info_su['purchase_unit']		= $pro_row['product_conv_unit'];
			$info_su['perent_id']			= 0;
			$info_su['main_request']		= 1;
			$info_su['status']				= 0;
			$info_su['user_id']				= $_SESSION['user_id'];
			$info_su['company_id']			= $_SESSION['company_id'];
			$info_su['branch_id']			= $POST['branch_id'];
			$info_su['bom_id']			= $bom_id;
			$info_su['rp_req_date']		=date('Y-m-d');
			$info_su['reject_status']		=0;
			$info_su['cdate']				=date('Y-m-d H:i:s');
			$info_su['user_id']			=$_SESSION['user_id'];
			$info_su['company_id']			=$_SESSION['company_id'];
			$info_su['product_remark'] = $pro_row['product_desc'];
			$info_su['priority_status']			= $priority_status;

			$inserid_sub1=add_record('tbl_request_product', $info_su, $dbcon);
			$pqty = $POST['qty'];

			if($inserid_sub1){
				$query="select * from tbl_request_product as i
				where i.rp_id=".$inserid_sub1;
				$result=$dbcon->query($query);
				$row=brp_mysqli_fetch_assoc($result);

				/*$query_pro2="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE pro_bom_process.process_status = 0 and bom.bom_version_id = ".$POST['bom_version_id']." and bom.bom_product='".$product_id."' AND process_status = '0' AND  pro_bom_process.pr_process_id != '' group by bom.bom_id"; */
			$query_pro2="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status = 0 and  pro_bom_process.process_status = 0 and prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$product_id."' and bom.bom_id =" .$bom_id; 
		
			$rel_pro2  = $dbcon->query($query_pro2);
			
			if(brp_mysqli_num_rows($rel_pro2)>0)
			{
				while($product_process_row1=brp_mysqli_fetch_assoc($rel_pro2))
				{
					
					
					$wpp_info['product_id'] = $POST['product_id'];		
					$wpp_info['rp_id'] = 	$inserid_sub1;
					$wpp_info['process_priority'] = 	$product_process_row1['priority'];
					$wpp_info['process_time'] = 	$product_process_row1['process_time'];
					$wpp_info['process_type'] = 	$product_process_row1['process_type'];
					$wpp_info['process_opening'] = 	$product_process_row1['process_opening'];
					$wpp_info['process_id'] = 	$product_process_row1['process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $POST['branch_id'];
					$wpp_info['description']			= $wproduct_process['description'];
				
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
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
			}
			
			
				
						
						if($POST['bom_version_id'] != "10000")
						{
				
							$bom_process="SELECT * FROM `tbl_bom` as bom
							left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
							WHERE  prover.bom_version_id='".$POST['bom_version_id']."' AND bom.bom_product='".$product_id."'";
							$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));

							$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name, cunit.unit_name as conv_unit_name, pro.reorder_qty, pro.product_desc from tbl_bomtrn as bom_trn 
							left join product_mst as pro on pro.product_id=bom_trn.product_id
							left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
							left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
							where bom_trn_status=0 and bom_id=".$bom_rel['bom_id'];	
							$result1=$dbcon->query($query1);

							$call=1;$space="";
							$i = 1;

					while($rel1=brp_mysqli_fetch_assoc($result1)){  
						
						/*$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
						$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

						$base_qty=$base_one_qty*$info_su['rp_req_qty'];
						$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/

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

						$info_sub['sp_id']				= 0;
						$info_sub['sr_no']				= $i;
						$info_sub['rp_pid']				= $rel1['product_id'];
						$info_sub['rp_req_date']		= date("Y-m-d");
						$info_sub['rp_req_qty']			= $base_qty;//required qty
						$info_sub['req_qty_one']		= $base_one_qty;//required qty
						// $info_sub['rp_req_qty']			= $POST['qty'];
						// $info_sub['req_qty_one']		= $conv_one_qty;//required qty
						$info_sub['rp_po_qty']			= "";//po qty
						$info_sub['in_process_qty']		= 0;//process qty
						$info_sub['rp_req_type']		= "job_card";//type
						$info_sub['process_unit']		= $rel1['product_base_unit'];
						$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
						$info_sub['perent_id']			= $inserid_sub1;
						$info_sub['status']				= 3;
						$info_sub['cdate']				=date('Y-m-d H:i:s');
						$info_sub['user_id']			= $_SESSION['user_id'];
						$info_sub['company_id']			= $_SESSION['company_id'];
						//$info_sub['main_request']		= $POST['g_total'];
						
						$info_sub['product_version']	= $rel1['p_bom_id'];
						$info_sub['bom_id']				= $rel1['p_bom_id'];
						$info_sub['product_remark'] =  $rel1['product_desc'];
						$info_sub['priority_status']	= $priority_status;
						
						//echo "<pre>"; print_r($info_sub);die;
						$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
						
						/*   Material Formula */
						$material_query="select * from tbl_bom_material_trn where bom_trn_id=".$rel1['bom_trn_id']." AND bom_id =".$rel1['bom_id']; 	
						$material_result=$dbcon->query($material_query);
						if(brp_mysqli_num_rows($material_result) > 0)
						{
							while($mat_rel=brp_mysqli_fetch_assoc($material_result))
							{ 
								$mat_data['sp_id'] = 0; 
								$mat_data['rp_id'] = $inserid_sub; 
								$mat_data['product_id'] = $rel1['product_id']; 
								$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id']; 
								$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value']; 
								$mat_data['jobcard_material_trn_status'] = 0; 
								$mat_data['user_id']			= $_SESSION['user_id'];
								$mat_data['company_id']			= $_SESSION['company_id'];
								$mat_data['branch_id']			= $_SESSION['branch_id'];
								$inserid_sub_material=add_record('tbl_jobcard_material_trn', $mat_data, $dbcon,$POST['branch_id']);
								
							}
						}
						
						/*   Material Formula */
						
				/*	$query_pro1="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$rel1['product_id']; */
				
			/*$query_pro1="select* from pro_bom_process where product_id = ".$rel1['product_id']." AND bom_id =".$bom_rel['bom_id'];	*/
			
	/*		$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND pro_bom_process.pr_process_id != ''"; 
*/
			$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status = 0 and  bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id =" . $rel1['p_bom_id']; 
		
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
			
					bom_child_tree($dbcon,$rel1['p_bom_id'],0,$inserid_sub,$i,$base_qty,$POST['bom_version_id'],$POST['branch_id'],$priority_status);
					
						
					$i++;
					}	
					}	
				
			$row['res']="1";
			$row['jobcard_rp_id']=$inserid_sub1;							
			echo json_encode($row);
			
		}
		
		else if(brp_strtolower($POST['mode']) == " ") {
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
		
	}
				
		/*function bom_child_tree($dbcon,$bom_id,$sp_id,$rp_parent_id,$num,$qty,$bom_version_id)
		{
			
			$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
			left join product_mst as pro on pro.product_id=bom_trn.product_id
			left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
			left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
			where bom_trn_status=0 and bom_id=".$bom_id;	
			$result1=$dbcon->query($query1);
			
			$k=1;
			$call=1;$space="";
		while($rel1=brp_mysqli_fetch_assoc($result1)){ 
			$sr_no = $num.'.'.$k; 
			
			$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

			//$base_qty=$base_one_qty*$info_su['rp_req_qty'];
			$base_qty=$qty*$rel1['product_base_qty'];
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

			$info_sub['sp_id']				= $sp_id;
			$info_sub['sr_no']				= $sr_no;
			$info_sub['rp_pid']				= $rel1['product_id'];
			$info_sub['rp_req_qty']			=  $base_qty;
			$info_sub['rp_req_date']		= date("Y-m-d");
			//$info_sub['rp_req_qty']			= $conv_stock;//required qty
			$info_sub['req_qty_one']		= 1;//required qty
			$info_sub['rp_po_qty']			= "";//po qty
			$info_sub['in_process_qty']		= $base_qty;//process qty
			$info_sub['rp_req_type']		= "job_card";//type
			$info_sub['process_unit']		= $rel1['product_base_unit'];
			$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
			$info_sub['perent_id']			= $rp_parent_id;
			$info_sub['status']				= 3;
			$info_sub['user_id']			= $_SESSION['user_id'];
			$info_sub['company_id']			= $_SESSION['company_id'];
			$info_sub['product_version']	= $rel1['p_bom_id'];
			$info_sub['bom_id']				= $rel1['p_bom_id'];			
			$info_sub['approval_status'] = '1';
			
			$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$POST['branch_id']);
		
			$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE  bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.pr_process_id != ''"; 
			
			$rel_pro1 = $dbcon->query($query_pro1);
			
			if(brp_mysqli_num_rows($rel_pro1)>0)
			{
				while($product_process1=brp_mysqli_fetch_assoc($rel_pro1))
				{
					$wpp_info['product_id'] = $rel1['product_id']; 	
					$wpp_info['rp_id'] = 	$inserid_sub;
					$wpp_info['process_priority'] = 	$product_process1['process_priority'];
					$wpp_info['process_time'] = 	$product_process1['process_time'];
					$wpp_info['process_type'] = 	$product_process1['process_type'];
					$wpp_info['process_opening'] = 	$product_process1['pr_process_id'];
					$wpp_info['process_id'] = 	    $product_process1['process_id'];	
					$wpp_info['cdate']				= date("Y-m-d H:i:s");
					$wpp_info['user_id']			= $_SESSION['user_id'];
					$wpp_info['company_id']			= $_SESSION['company_id'];
					$wpp_info['branch_id']			= $POST['branch_id'];
					
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
				}
			}
			bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$sr_no,$qty,$bom_version_id);
			$k++;	
		}
		
}*/


function bom_child_tree($dbcon,$bom_id,$sp_id,$rp_parent_id,$num,$qty,$bom_version_id,$branch_id,$priority_status)
		{
			$query_m="select * from tbl_bom as bom where bom_status=0 and bom_id=".$bom_id;
			$result_m=$dbcon->query($query_m);
			$rel_m=mysqli_fetch_assoc($result_m);	

			$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name, cunit.unit_name as conv_unit_name, pro.product_desc from tbl_bomtrn as bom_trn 
			left join product_mst as pro on pro.product_id=bom_trn.product_id
			left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
			left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
			where bom_trn_status=0 and bom_id=".$bom_id;	
			$result1=$dbcon->query($query1);
			
			$k=1;
			$call=1;$space="";
		while($rel1=brp_mysqli_fetch_assoc($result1)){ 
			$sr_no = $num.'.'.$k; 
			
			/*$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

			$base_qty=$base_one_qty*$info_su['rp_req_qty'];
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/
			
			/*$base_one_qty=$rel1['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");
			$base_qty=$qty*$rel1['product_base_qty'];				
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/
			
			/*$base_one_qty=$rel1['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");
			$base_qty=$qty*$rel1['product_base_qty'];				
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/
			

			$base_one_qty=$rel1['product_base_qty']/$rel_m['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");
			$base_qty=$base_one_qty*$qty;

			$reorder_qty = 0;
						
				if(!empty($rel1['reorder_qty']) && $rel1['reorder_qty'] > 0){
					$reorder_qty = $rel1['reorder_qty'];		
				   $chk_qty = 	ceil($base_qty  / $reorder_qty);
				   $base_qty = 	$reorder_qty * $chk_qty;
				}	
			$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

			$info_sub['sp_id']				= $sp_id;
			$info_sub['sr_no']				= $sr_no;
			$info_sub['rp_pid']				= $rel1['product_id'];
			$info_sub['rp_req_qty']			= $base_qty;
			$info_sub['req_qty_one']		= $base_one_qty;//required qty
			$info_sub['rp_req_date']		= date("Y-m-d");
			//$info_sub['rp_req_qty']			= $conv_stock;//required qty
			// $info_sub['req_qty_one']		= $conv_one_qty;//required qty
			$info_sub['rp_po_qty']			= "";//po qty
			$info_sub['in_process_qty']		= 0;//process qty
			$info_sub['rp_req_type']		= "job_card";//type
			$info_sub['process_unit']		= $rel1['product_base_unit'];
			$info_sub['purchase_unit']		= $rel1['product_conv_unit'];
			$info_sub['perent_id']			= $rp_parent_id;
			$info_sub['status']				= 3;
			$info_sub['cdate']				=date('Y-m-d H:i:s');
			$info_sub['user_id']			= $_SESSION['user_id'];
			$info_sub['company_id']			= $_SESSION['company_id'];
			$info_sub['product_version']	= $rel1['p_bom_id'];
			$info_sub['bom_id']				= $rel1['p_bom_id'];			
			$info_sub['approval_status'] = '1';
			$info_sub['product_remark'] =  $rel1['product_desc'];
			$info_sub['priority_status']			= $priority_status;
			
			$inserid_sub=add_record('tbl_request_product', $info_sub, $dbcon,$branch_id);
		
			/*$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE  bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != ''"; */

			$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status = 0 and  bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id = " . $rel1['p_bom_id']; 
			
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
					$wpp_info['branch_id']			= $_POST['branch_id'];
					
					$inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
				}
			}
			bom_child_tree($dbcon,$rel1['p_bom_id'],$sp_id,$inserid_sub,$sr_no,$base_qty,$bom_version_id,$branch_id,$priority_status);
			$k++;	
		}
		
}
?>