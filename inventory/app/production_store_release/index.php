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
		
		
		if(brp_strtolower($POST['mode']) == "add_store_release") {
			// echo "<pre>";
			// print_r($POST);
			// die;
			$p_id = $POST['p_id'];

			$row_product_id = $POST['row_product_id'];
			$row_req_qty_one = $POST['row_product_id'];

			$query1 = "select product_base_unit,product_conv_unit
			 			from product_mst where product_id = " . $POST['product_id'];

			$result1=$dbcon->query($query1);
			$pro_res =brp_mysqli_fetch_array($result1);

			$branch_id=$POST['branch_id'];
			$start_qty=$POST['start_qty'];
			$process_id = $POST['process_id'];

			$product_id = $POST['product_id'];
			
			$info['rp_id']		= $POST['product_id'];
			$info['process_id']	= $POST['process_id'];
			$info['remark']		= $POST['remark'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $branch_id;

			$info['release_unit'] = $pro_res['product_base_unit'];
			$info['release_conv_unit'] = $pro_res['product_conv_unit'];

			$previous_process_id = $POST['previous_process_id'];
			
			// $pid=explode(',', $p_id);
			$req_qty = $start_qty;
			// for($i=0;$i<count($pid);$i++)
			// {
				
				$qry = "select * from tbl_store_request where p_id in (". $p_id .")";
				$result =$dbcon->query($qry);
				// echo $qry;die;
				while($row=brp_mysqli_fetch_array($result)){
					$store_request_id = $row['store_request_id']; 
					$remaining_qty = $row['base_qty'] - $row['release_qty'];
					$info['p_id'] = $row['p_id'];
					$info['to_user_id'] = $row['user_id'];
					$info['store_request_id'] = $row['store_request_id'];
					$info['issue_no'] = $POST['issue_no'];
					$info['issue_date']	= date('Y-m-d',strtotime($POST['issue_date']));

					if($remaining_qty > 0){
						$rel_qty = 0;
						if($req_qty > $remaining_qty){
							$rel_qty = $remaining_qty;
							$req_qty = $req_qty - $remaining_qty;
							$info['release_qty']	= $rel_qty;
							$info['release_conv_qty']	= $rel_qty;
							
							$req_id = add_record('tbl_store_release',$info, $dbcon,$branch_id);

							if($remaining_qty - $rel_qty  <= 0){
								$str_info['store_request_status'] = 1;
								update_record('tbl_store_request', $str_info, "store_request_id=".$store_request_id, $dbcon);
							}
							store_release_logs($dbcon,$store_request_id,$req_id,$rel_qty,$pid[$i],$POST['product_id'],$POST['process_id'],$POST['remark'],$row['user_id'],$branch_id);


							release_stock_action($dbcon,$row_product_id,$row_req_qty_one,$row['p_id'],$rel_qty,$previous_process_id,$product_id);
						}else{
							$rel_qty = $req_qty;
							$info['release_qty']	= $rel_qty;
							$info['release_conv_qty']	= $rel_qty;
							
							$req_id = add_record('tbl_store_release',$info, $dbcon,$branch_id);
							store_release_logs($dbcon,$store_request_id,$req_id,$rel_qty,$pid[$i],$POST['product_id'],$POST['process_id'],$POST['remark'],$row['user_id'],$branch_id);
							release_stock_action($dbcon,$row_product_id,$row_req_qty_one,$row['p_id'],$rel_qty,$previous_process_id,$product_id);
							break;
						}
					}
				}
			// } 

			if($req_id > 0){
				update_issue_no($dbcon);
				echo "1";
			}else{
				echo "0";
			}
			
		}
		else if(brp_strtolower($POST['mode']) == "add_store_release_using_model") {
			/*echo "<pre>";	
			print_r($POST);
			die;*/
			$mt_rel_p_id = implode(",",$POST['pid']);
			$mt_rel_qty = implode(",",$POST['pid_wise_start_qty']);
			$info1['release_no']		= $POST['release_no'];
			$info1['release_date']		= date('d-m-Y',strtotime($POST['release_date']));
			$info1['to_godown_id']		= $POST['to_godown_id'];
			$info1['to_user_id']		= $POST['to_user_id'];	
			$info1['product_id']		= $POST['product_id'];
			$info1['release_qty']		= $mt_rel_qty;
			$info1['release_unit']		= $POST['product_base_unit'];
			// $info1['rp_id']			= 3;	
			$info1['process_id']		= $POST['process_id'];	
			$info1['p_id']				= $mt_rel_p_id;	

			$info1['cdate']				= date('Y-m-d H:i:s');
			$info1['user_id']			= $_SESSION['user_id'];	
			$info1['company_id']		= $_SESSION['company_id'];	
			
			$material_id = add_record('tbl_material_release',$info1, $dbcon);

			if($material_id){
				update_common_no($dbcon,RELEASE_MATERIAL);
			}


		/*	echo "<pre>";	
			print_r($POST);
			die;*/

			$previous_process_id = $POST['previous_process_id'];
			$query1 = "select product_base_unit,product_conv_unit
			 			from product_mst where product_id = " . $POST['product_id'];

			$result1 = $dbcon->query($query1);
			$pro_res = brp_mysqli_fetch_array($result1);

			$branch_id  = $POST['branch_id'];
			$start_qty  = $POST['start_qty'];
			$process_id = $POST['process_id'];
			$product_id = $POST['product_id'];
			
			$info['rp_id']		= $POST['product_id'];
			$info['process_id']	= $POST['process_id'];
			$info['remark']		= $POST['remark'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $branch_id;

			$info['release_unit'] = $POST['product_base_unit'];
			$info['release_conv_unit'] = $pro_res['product_conv_unit'];
			
			$pid = $POST['pid'];
			$pid_wise_start_qty = $POST['pid_wise_start_qty'];
			
			$arr_material_pid = $POST['material_pid'];
			$arr_material_qty = $POST['material_qty'];  // material_qty
			$arr_material_product_id = $POST['material_product_id'];
			$arr_material_actual_qty = $POST['material_actual_qty'];
			$arr_material_request_id = $POST['material_request_id'];

			// var_dump('count--> ' . count($pid));
			for($i=0;$i<count($pid);$i++)
			{

				$info['p_id']	= $pid[$i];

				if($material_id){
					$rel_info['material_id'] =  $material_id;;
					$rel_info['product_id'] = $POST['product_id'];
					$rel_info['p_id'] = $pid[$i];
					$rel_info['release_qty'] = $pid_wise_start_qty[$i];
					$rel_info['pending_qty'] = $pid_wise_start_qty[$i];
					$rel_info['start_qty'] = 0;
					$rel_info['release_unit'] = $POST['product_base_unit'];
					$rel_info['user_id']	= $_SESSION['user_id'];
					$rel_info['company_id']	= $_SESSION['company_id'];
			
					
					$start_stop_id = add_record('tbl_start_stop_production',$rel_info, $dbcon);
					
					$trn_info['status'] = 0;
					$trn_info['material_id'] = $material_id;
					$trn_info['to_godown_id'] = $POST['to_godown_id'];
					$trn_info['to_user_id'] = $POST['to_user_id'];
					$trn_info['start_stop_id'] = $start_stop_id;
					update_record('tbl_material_release_trn', $trn_info, "status = 3 and p_id = " .  $pid[$i], $dbcon);

					// var_dump('count materiak--> ' . count($arr_material_pid));
					for($x=0;$x<count($arr_material_pid);$x++)
					{
						//var_dump('x : --> ' . count($x));
						if($arr_material_pid[$x] == $pid[$i]){
							$mqry1 = "select * from tbl_material_release_trn where status = 2 and release_status = 0 and is_temp_delete = 1 and product_id = ".$arr_material_product_id[$x]."  and p_id = " . $pid[$i];

							$mres1 = $dbcon->query($mqry1);
							while($mrow1 = brp_mysqli_fetch_assoc($mres1)){

								$qry_rst = "select stock_id,base_stock,convert_stock from tbl_reserve_stock where reserve_id = " . $mrow1['stock_id'];
								$qry_res_rst = $dbcon->query($qry_rst);
								$qry_rw_rst = brp_mysqli_fetch_assoc($qry_res_rst);

								$qry_rst1 = "select stock_id,used_base_stock,used_convert_stock from tbl_stock_trn where stock_id = "  .  $qry_rw_rst['stock_id'];
								$qry_res_rst1 = $dbcon->query($qry_rst1);
								$qry_rw_rst1 = brp_mysqli_fetch_assoc($qry_res_rst1);

								$upd_stkr['used_base_stock'] = 	$qry_rw_rst1['used_base_stock'] - $qry_rw_rst['base_stock'];
								$upd_stkr['used_convert_stock'] = 	$qry_rw_rst1['used_convert_stock']  - $qry_rw_rst['convert_stock'];

								update_record('tbl_stock_trn', $upd_stkr, "stock_id = " .  $qry_rw_rst1['stock_id'], $dbcon);

								$upd_rs['stock_status'] = 2;
								update_record('tbl_reserve_stock', $upd_rs, "reserve_id = " .  $mrow1['stock_id'], $dbcon);
							}


							 $mqry = "select * from tbl_material_release_trn where status = 0 and release_status = 0 and product_id = ".$arr_material_product_id[$x]." and material_id = ".$material_id." and p_id = " . $pid[$i];
							$mres = $dbcon->query($mqry);
							while($mrow = brp_mysqli_fetch_assoc($mres)){
								$res_stock_godown = $mrow['godown_id'];
								release_stock_action_modal_godown_wise($dbcon,$pid[$i],$arr_material_actual_qty[$x],$arr_material_qty[$x],$previous_process_id,$arr_material_product_id[$x],$product_id,$material_id,$POST['to_godown_id'],$res_stock_godown,$mrow['stock_id']);
							}

						}
					}
				}

				$info['p_id'] = $pid[$i];

				$req_qty = $pid_wise_start_qty[$i];

				/*$qry = "select * from tbl_store_request where p_id = ". $pid[$i];
				$result = $dbcon->query($qry);

				while($row = brp_mysqli_fetch_array($result)){
*/

				$qry = "select sr.*,ap.extra_stock from tbl_store_request as sr left join tbl_allocate_process as ap on ap.p_id = sr.p_id where sr.p_id = ". $pid[$i];
				$result =$dbcon->query($qry);
				$xx = 0;
				while($row=brp_mysqli_fetch_array($result)){
					$extra_stock = $row['extra_stock'];

					$store_request_id = $row['store_request_id'];
					$remaining_qty = $row['base_qty'] - $row['release_qty'];
					$info['store_request_id'] = $row['store_request_id'];
					$info['to_user_id'] = $row['user_id'];
					$info['issue_no'] = $POST['issue_no'];
					$info['issue_date']	= date('Y-m-d',strtotime($POST['issue_date']));

					
					if($req_qty > 0){
					if($remaining_qty > 0){
						$rel_qty = 0;
						$job_work_sub_trn_id = $row['job_work_sub_trn_id'];
						if($job_work_sub_trn_id == ""){
							$job_work_sub_trn_id = 0;
						}

						if($req_qty > $remaining_qty){
							$rel_qty = $remaining_qty;
							$req_qty = $req_qty - $remaining_qty;
							$rel_qty_con = convert_stock($dbcon,$rel_qty,$info['rp_id'],"conv_unit");
							$info['release_qty']	= $rel_qty;
							$info['release_conv_qty']	= $rel_qty_con;
							
							$req_id = add_record('tbl_store_release',$info, $dbcon,$branch_id);

							if($remaining_qty - $rel_qty  <= 0){
								$str_info['store_request_status'] = 1;
								update_record('tbl_store_request', $str_info, "store_request_id=".$store_request_id, $dbcon);
							}
							
							store_release_logs($dbcon,$store_request_id,$req_id,$rel_qty,$pid[$i],$POST['product_id'],$POST['process_id'],$POST['remark'],$row['user_id'],$branch_id);
							/*for($x=0;$x<count($arr_material_pid);$x++)
							{
								
								if($arr_material_pid[$x] == $pid[$i]){
									if($xx == 0){

									$query6 = "select product_base_unit,product_conv_unit
			 									from product_mst where product_id = " . $POST['product_id'];

									$result6=$dbcon->query($query6);
									$res6 =brp_mysqli_fetch_array($result6);
									
									$r_product_id=$arr_material_product_id[$x];
									$raw_rel_qty=$arr_material_qty[$x];
									$raw_rel_qty_con=convert_stock($dbcon,$raw_rel_qty,$r_product_id,"conv_unit");

									$info_material['release_id'] = $req_id;
									$info_material['p_id'] = $arr_material_pid[$x];
									$info_material['product_id'] = $r_product_id;
									$info_material['process_id'] = $process_id;
									$info_material['process_id'] = $process_id;
									$info_material['request_qty'] = $arr_material_actual_qty[$x];

									$info_material['release_qty'] = $raw_rel_qty;
									$info_material['release_unit'] = $res6['product_base_unit'];
									$info_material['release_conv_qty'] =  $raw_rel_qty_con;
									$info_material['release_conv_unit'] = $res6['product_conv_unit'];
									$info_material['cdate']		= date("Y-m-d H:i:s");
									$info_material['user_id']	= $_SESSION['user_id'];
									$info_material['company_id']	= $_SESSION['company_id'];
									$info_material['branch_id']	= $branch_id;

									$m_req_id = add_record('tbl_store_release_material_trn',$info_material, $dbcon);
									if($extra_stock	== 0){
										release_stock_action_modal($dbcon,$pid[$i],$info_material['release_qty'],$info_material['release_conv_qty'],$previous_process_id,$arr_material_product_id[$x],$product_id,$arr_material_request_id[$x]);
									}

									}

									release_stock_action_modal($dbcon,$pid[$i],$info_material['release_qty'],$info_material['release_conv_qty'],$previous_process_id,$arr_material_product_id[$x],$product_id);

								}
							}	
						


							store_release_logs($dbcon,$store_request_id,$req_id,$rel_qty,$pid[$i],$POST['product_id'],$POST['process_id'],$POST['remark'],$row['user_id'],$branch_id);

							store_release_logs($dbcon,$store_request_id,$req_id,$arr_material_qty[$x],$rel_qty,$pid[$i],$POST['product_id'],$POST['process_id'],$POST['remark'],$row['user_id'],$branch_id);*/


							if($job_work_sub_trn_id > 0){
								update_jobwork_status($dbcon,$job_work_sub_trn_id,$rel_qty, $rel_qty_con,$pid[$i]);
							}


							// $req_qty =  $req_qty - $rel_qty;

						}else{
							$rel_qty = $req_qty;
							$info['release_qty']	= $rel_qty;
							$info['release_conv_qty']	= $rel_qty;
							
							$req_id = add_record('tbl_store_release',$info, $dbcon,$branch_id);

							store_release_logs($dbcon,$store_request_id,$req_id,$rel_qty,$pid[$i],$POST['product_id'],$POST['process_id'],$POST['remark'],$row['user_id'],$branch_id);

							/*for($x=0;$x<count($arr_material_pid);$x++)
							{
								
								if($arr_material_pid[$x] == $pid[$i]){

										if($xx == 0){

									$query6 = "select product_base_unit,product_conv_unit
			 									from product_mst where product_id = " . $arr_material_product_id[$x];

									$result6=$dbcon->query($query6);
									$res6 =brp_mysqli_fetch_array($result6);

									$r_product_id=$arr_material_product_id[$x];
									$raw_rel_qty=$arr_material_qty[$x];
									$raw_rel_qty_con=convert_stock($dbcon,$raw_rel_qty,$r_product_id,"conv_unit");

									$info_material['release_id'] = $req_id;
									$info_material['p_id'] = $arr_material_pid[$x];
									$info_material['product_id'] = $arr_material_product_id[$x];
									$info_material['process_id'] = $process_id;
									$info_material['request_qty'] = $arr_material_actual_qty[$x];
									
									$info_material['release_qty'] = $raw_rel_qty;
									$info_material['release_unit'] = $res6['product_base_unit'];
									$info_material['release_conv_qty'] =  $raw_rel_qty_con;
									$info_material['release_conv_unit'] = $res6['product_conv_unit'];
									$info_material['cdate']		= date("Y-m-d H:i:s");
									$info_material['user_id']	= $_SESSION['user_id'];
									$info_material['company_id']	= $_SESSION['company_id'];
									$info_material['branch_id']	= $branch_id;

									$m_req_id = add_record('tbl_store_release_material_trn',$info_material, $dbcon);
									if($extra_stock	== 0){	
										release_stock_action_modal($dbcon,$pid[$i],$info_material['release_qty'],$info_material['release_conv_qty'],$previous_process_id,$arr_material_product_id[$x],$product_id,$arr_material_request_id[$x]);
									}
								}
								}
							}	
							$req_qty =  $req_qty - $rel_qty;

							store_release_logs($dbcon,$store_request_id,$req_id,$rel_qty,$pid[$i],$POST['product_id'],$POST['process_id'],$POST['remark'],$row['user_id'],$branch_id);*/

							if($job_work_sub_trn_id > 0){
								update_jobwork_status($dbcon,$job_work_sub_trn_id,$rel_qty, $rel_qty_con,$pid[$i]);
							}
							
							break;
						}
						$xx++;
					}
					}
					
				}
			}
			if($req_id > 0){
				update_issue_no($dbcon);
				echo "1";
			}else{
				echo "0";	
			}
			
		}
		
		else if(brp_strtolower($POST['mode']) == "show_material_list_new") {


			$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
				WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['p_id'].")";
			$resul=$dbcon->query($bom);
			$rel1=brp_mysqli_fetch_assoc($resul);
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			
			$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			WHERE rpro.status!=2 AND rpro.perent_id in (".$rel1['views'].") group by rpro.rp_pid" ;
			$result=$dbcon->query($bom1);

				$query="select p.product_name,pr.process_name,ap.previous_process_id,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
			where ap.p_id in (".$p_id.")";

			
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result)){
				$o_qty=convert_stock($dbcon,$rel["req_qty_one"],$rel['rp_pid'],"base_unit");
				$rel["req_qty_one"]=round($rel["req_qty_one"],6);
				
				$o_qty=round($o_qty,6);
				//$o_qty=round($rel["req_qty_one"],6);
				$total_req_qty=$POST['pending_qty']*$o_qty;
				$total_req_qty=round($total_req_qty,4);
				$used_qty=$POST['max_start_qty']*$o_qty;
				$used_qty=round($used_qty,4);
				$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,0);
				// echo '-->'.$cur_stock . "</br>";
				$cur_stock=round($cur_stock,4);
				$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				//$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['process_unit'],"",$rel_n1['rp_id']);
				echo '<tr>
						<td>'.$rel["product_name"].'
							<input type="hidden" class="" name="row_product_id[]" id="row_product_id'.$i.'" value="'.$rel['rp_pid'].'" />
						</td>
						<td>'.$cat_name.'</td>
						<td>'.$o_qty.'
							<input type="hidden" class="" name="row_req_qty_one[]" id="row_req_qty_one'.$i.'" value="'.$o_qty.'" />
						</td>
						<td>'.$total_req_qty.'</td>
						<td>'.$cur_stock.'</td>
						<td>'.$used_qty.'</td>
						<td>'.$rel["base_unit_name"].'
							<input type="hidden" class="" name="row_unit_id[]" id="row_unit_id'.$i.'" value="'.$rel['process_unit'].'" />
						</td>
				</tr>
				';
				$i++;
			}
		}
		else if(brp_strtolower($POST['mode'])== "get_series_no"){
			$query="select * from tbl_invoicetype where status=0 and type_id=11 and company_id=".$_SESSION['company_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(brp_strtolower($POST['mode'])== "load_invoiceno"){
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno'] = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}

		

function release_stock_action($dbcon,$row_product_id,$row_req_qty_one,$p_id,$rel_qty,$previous_process_id ){
	

	for($i=0;$i<count($row_product_id);$i++)
	{
		if($previous_process_id == 0){
			$query1 = "select *	from tbl_reserve_stock where stock_status !=2 and stock_flage = 1 and p_id = " . $p_id . " and product_id = " . $row_product_id[$i];

			$result1=$dbcon->query($query1);
			$res =brp_mysqli_fetch_array($result1);

			$approve_base_stock = 0;
			$approve_convert_stock =0;

			if($res['approve_base_stock'] != "" && $res['approve_base_stock'] > 0){
				$approve_base_stock = $res['approve_base_stock'];
			} 

			if($res['approve_convert_stock'] != "" && $res['approve_convert_stock'] > 0){
				$approve_convert_stock = $res['approve_convert_stock'];
			} 

			$approve_base_stock = $approve_base_stock + ($rel_qty * $row_req_qty_one[$i]);
			$approve_convert_stock = $approve_convert_stock + $rel_qty;

			$res_stock['approve_base_stock'] = $approve_base_stock;
			$res_stock['approve_convert_stock'] = $approve_convert_stock;

			$table='tbl_reserve_stock';$tableid='reserve_id';
			update_record($table, $res_stock, $tableid."=".$res['reserve_id'], $dbcon);
		}else{
			$query1 = "select *	from tbl_process_reserve_stock where  stock_status !=2 and stock_flage = 1 and p_id = " . $p_id . " and product_id = " . $main_product;

			$result1=$dbcon->query($query1);
			$res =brp_mysqli_fetch_array($result1);

			$approve_base_stock = 0;
			$approve_convert_stock =0;

			if($res['approve_base_stock'] != "" && $res['approve_base_stock'] > 0){
				$approve_base_stock = $res['approve_base_stock'];
			} 

			if($res['approve_convert_stock'] != "" && $res['approve_convert_stock'] > 0){
				$approve_convert_stock = $res['approve_convert_stock'];
			} 

			$approve_base_stock = $approve_base_stock + ($rel_qty * $row_req_qty_one[$i]);
			$approve_convert_stock = $approve_convert_stock + $rel_qty;

			$res_stock['approve_base_stock'] = $approve_base_stock;
			$res_stock['approve_convert_stock'] = $approve_convert_stock;

			$table='tbl_process_reserve_stock';$tableid='process_reserve_id	';
			update_record($table, $res_stock, $tableid."=".$res['process_reserve_id	'], $dbcon);
		}
	}

}

function update_jobwork_status($dbcon,$job_work_sub_trn_id,$qty, $conv_qty,$p_id){

	$qry="SELECT IFNULL(sum(release_qty),0) as release_qty from tbl_store_request where job_work_sub_trn_id = ".$job_work_sub_trn_id." and p_id = ".$p_id;
			$result=$dbcon->query($qry);
	$rel=brp_mysqli_fetch_assoc($result);
	$release_qty = $rel['release_qty'];


	$qry1="SELECT product_base_qty,job_work_trn_id from tbl_job_work_sub_trn where job_work_sub_trn_id = ".$job_work_sub_trn_id;
			$result1=$dbcon->query($qry1);
	$rel1=brp_mysqli_fetch_assoc($result1);
	$jobwork_qty = $rel1['product_base_qty'];
	$job_work_trn_id = $rel1['job_work_trn_id'];
// echo $release_qty . ' - ' . $jobwork_qty . ' - ' . $p_id;
	if($release_qty == $jobwork_qty){
		$jobwork_sub_trn['release_status'] = 1;

		update_record('tbl_job_work_sub_trn', $jobwork_sub_trn, "job_work_sub_trn_id=".$job_work_sub_trn_id, $dbcon);

		$qry2="SELECT count(job_work_sub_trn_id) as pending_jobwork from tbl_job_work_sub_trn where release_status = 0 and job_work_sub_trn_status = 0 and job_work_trn_id = ".$job_work_trn_id;
			$result2=$dbcon->query($qry2);
		$rel2=brp_mysqli_fetch_assoc($result2);

		$pending_jobwork_trn = $rel2['pending_jobwork'];

		if($pending_jobwork_trn == 0){
			$jobwork_trn['release_status'] = 1;

			update_record('tbl_job_work_trn', $jobwork_trn, "job_work_trn_id=".$job_work_trn_id, $dbcon);

			$qry3="SELECT job_work_id from tbl_job_work_trn where job_work_trn_id = ".$job_work_trn_id;
				$result3=$dbcon->query($qry3);
			$rel3=brp_mysqli_fetch_assoc($result3);

			$job_work_id = $rel3['job_work_id'];

			$qry4="SELECT count(job_work_trn_id) as pending_jobwork from tbl_job_work_trn where release_status = 0 and job_work_trn_status = 0 and job_work_id = ".$job_work_id;
				$result4=$dbcon->query($qry4);
			$rel4=brp_mysqli_fetch_assoc($result4);

			$pending_jobwork = $rel4['pending_jobwork'];

			if($pending_jobwork == 0){
			$jobwork['release_status'] = 1;

			update_record('tbl_job_work', $jobwork, "job_work_id=".$job_work_id, $dbcon);
			}
		}
	}
}


?>