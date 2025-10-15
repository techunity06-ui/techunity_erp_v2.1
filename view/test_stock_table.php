<?php
session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");

$sql="select * from opening_stock_mst as so_trn 
where so_trn.status=0 and approve_status=1";
$result=$dbcon->query($sql);
while($row=brp_mysqli_fetch_assoc($result)){

	$stock = 0;
	$stock = $row['opening_stock_qty'];
	$info_stockadd['stock_flage']				= "1";
	$info_stockadd['stock_date']				= date("Y-m-d",strtotime($row['cdate']));
	$info_stockadd['product_id']				= $row['product_id'];
	$info_stockadd['base_stock']				= $stock;
	$info_stockadd['base_unit']					= $row['opening_stock_unit'];

	$info_stockadd['convert_stock']				= $row['opening_stock_conv_qty'];
	$info_stockadd['convert_unit']				= $row['opening_stock_conv_unit'];
	$info_stockadd['stock_flage']				= "1";
	$info_stockadd['godown_id']					= $row['location_id'];
	$info_stockadd['ref_name']					= 'opening_stock';
	$info_stockadd['ref_id']					= $row['opening_stock_id'];
	$info_stockadd['stock_status']				= "0";
	$info_stockadd['cdate']						= date("Y-m-d H:i:s",strtotime($row['cdate']));
	$info_stockadd['user_id']					= $row['user_id'];
	$info_stockadd['company_id']				= $row['company_id'];
	$info_stockadd['batch_no']					= $row['batch_no'];
	$info_stockadd['base_rate']					= $row['base_rate'];
	$info_stockadd['conv_rate']					= $row['conv_rate'];

	$opening_stock_id=add_record('tbl_stock_trn',$info_stockadd, $dbcon,$row['branch_id']);
}


//purchase stock add entry start
$query="select trn.*,grn_trn.ref_type from tbl_store_accept_trn as trn
left join tbl_grn_trn as grn_trn on grn_trn.grn_trn_id=trn.grn_trn_id
where trn.store_accept_trn_status=0 and store_accept_id!=0 and grn_trn.ref_type=2";
	//var_dump($query);
$result1d=$dbcon->query($query);
if(mysqli_num_rows($result1d)>0)
{
	while($rel=brp_mysqli_fetch_assoc($result1d))
	{
		$accept_qty=$rel['qty'];

		$query_grn="select batch.*,trn.*,grn.grn_date,trn.branch_id as sel_branch from tbl_batch_data as batch
		left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
		left join tbl_grn as grn on grn.grn_id=trn.grn_id
		where batch.batch_id =".$rel['batch_id'];

			// $accept_qty=$rel_grn['batch_qty'];

		$result_grn=$dbcon->query($query_grn);
		$rel_grn=brp_mysqli_fetch_assoc($result_grn);

		purchase_stock_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);

	}
}
//purchase stock add entry end
	//reserve stock entry start

$query_red="select rp_id,sales_ordertrn_id from work_order_reserve_temp as batch
where status=0 and batch.company_id =".$_SESSION['company_id'];

$result_red=$dbcon->query($query_red);
while($rel_red=brp_mysqli_fetch_assoc($result_red)){

	if(!empty($rel_red['sales_ordertrn_id'])){
		res_sowise($dbcon,$rel_red['sales_ordertrn_id']);
	}

	if(!empty($rel_red['rp_id']))
	{
		res_rpid_wise($dbcon,$rel_red['rp_id']);

	}
}



	//reserve stock entry stop
//production stock effect start
$query="select trn.*,grn_trn.ref_type from tbl_store_accept_trn as trn
left join tbl_grn_trn as grn_trn on grn_trn.grn_trn_id=trn.grn_trn_id
where trn.store_accept_trn_status=0 and store_accept_id!=0 and grn_trn.ref_type!=2";
	//var_dump($query);
$result1d=$dbcon->query($query);
if(mysqli_num_rows($result1d)>0)
{
	while($rel=brp_mysqli_fetch_assoc($result1d))
	{
		$accept_qty=$rel['qty'];

		$query_grn="select batch.*,trn.*,grn.grn_date,trn.branch_id as sel_branch from tbl_batch_data as batch
		left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
		left join tbl_grn as grn on grn.grn_id=trn.grn_id
		where batch.batch_id =".$rel['batch_id'];

			// $accept_qty=$rel_grn['batch_qty'];

		$result_grn=$dbcon->query($query_grn);
		$rel_grn=brp_mysqli_fetch_assoc($result_grn);

			// var_dump($rel_grn['ref_type']);
		if($rel_grn['reprocess_qc'] == '1' && $rel_grn['ref_type']=="2"){

		}else if($rel_grn['is_scrap'] == '1'){
			add_stock($dbcon,$rel_grn['product_scrap_id'],$rel_grn['scrap_unit'],$rel_grn['grn_date'],"scrap",$rel_grn['grn_trn_id'],$rel['godown_id'],$rel_grn['scrap_qty'],"1",$rel_grn['branch_id'],"","","",$rel_grn['batch_id'],$rel_grn['batch_no']);
		}
		else if($rel_grn['ref_type']=="2"){
			
				//purchase_stock_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
		}else if($rel_grn['ref_type']=="1"){
			jobwork_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$accept_qty,$rel_grn['batch_unit'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['reject_qty']);
		}else if($rel_grn['ref_type']=="3"){
			jobwork_stock_accept($dbcon,$rel['grn_trn_id'],$rel['godown_id'],$rel['product_id'],$rel['qty'],$rel_grn['batch_unit'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['reject_qty']);
		}else if($rel_grn['ref_type']=="4"){
			direct_grn_stock_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['po_ref_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
		}
			else if($rel_grn['ref_type']=="6"){  // returnable chalan stock
				$stock_date=date("Y-m-d");

				$query11 = "select grn_sub_trn.grn_trn_sub_id,customer_id from tbl_grn_sub_trn as grn_sub_trn
				where grn_sub_trn.grn_trn_id=".$rel_grn['grn_trn_id'] ;

				$result1=$dbcon->query($query11);

				$res1 = brp_mysqli_fetch_assoc($result1);

				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['unit_id'],$stock_date,"returnable",$rel_grn['grn_trn_id'],$rel['godown_id'],$rel['qty'],1,$rel_grn['sel_branch'],"","",$res1['customer_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="5"){ 
				$stock_date=date("Y-m-d");

				$query11 = "select grn_sub_trn.grn_trn_sub_id,customer_id from tbl_grn_sub_trn as grn_sub_trn
				where grn_sub_trn.grn_trn_id=".$rel_grn['grn_trn_id'] ;

				$result1=$dbcon->query($query11);

				$res1 = brp_mysqli_fetch_assoc($result1);


				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['unit_id'],$stock_date,"direct_grn",$res1['grn_trn_sub_id'],$rel['godown_id'],$rel['qty'],1,$rel_grn['sel_branch'],"","",$rel_grn['customer_id'],$rel_grn['batch_id'],$rel_grn['batch_no']);
			}else if($rel_grn['ref_type']=="7"){

				stock_transfer_accept($dbcon,$rel['product_id'],$rel_grn['batch_unit'],$rel_grn['grn_date'],$rel_grn['grn_trn_id'],$rel['godown_id'],$accept_qty,$rel_grn['branch_id'],$rel_grn['stock_transfer_trn_id'],$rel_grn['batch_id'],$rel_grn['batch_no'],$rel_grn['to_godown_id']);
			}
		}
	}
//production stock effect end

//non returnable challan stock effet start
	$query_non= "SELECT id FROM `tbl_returnable_channal_item` WHERE `returnable_id` != 0 AND `status` = 0 AND `approve_status` = 1";
	$exe_non= $dbcon->query($query_non);
	while($row_non = brp_mysqli_fetch_array($exe_non)){
		returnable_challan_effect($dbcon,$row_non['id']);
	}
//non returnable challan stock effet end

	function purchase_stock_accept($dbcon,$product_id,$unit_id,$grn_date,$grn_trn_id,$godown_id,$accept_qty,$branch_id,$po_ref_id,$batch_id,$batch_no){

	// $stock_id=add_stock($dbcon,$product_id,$unit_id,$grn_date,"tbl_grn_trn",$grn_trn_id,$godown_id,$accept_qty,"1",$branch_id,"","","",$batch_id,$batch_no);

		$query = "select sum(sgtr.product_qty) as product_qty, sum(sgtr.product_conv_qty) as product_conv_qty, sgtr.product_base_unit, sgtr.product_conv_unit,ptrn.unit_id,ptrn.conv_unit_id,ptrn.rate_unit,ptrn.product_rate, pmst.product_base_qty, pmst.product_conv_qty from tbl_grn_trn as gtrn 
		left join tbl_grn_sub_trn as sgtr on sgtr.grn_trn_id = gtrn.grn_trn_id
		left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=sgtr.purchaseordertrn_id
		left join product_mst as pmst on pmst.product_id = ptrn.product_id
		where gtrn.grn_trn_status = 0 and sgtr.status = 0 and gtrn.grn_trn_id=".$grn_trn_id." group by ptrn.product_rate";
		$exe = $dbcon->query($query);
		while($row = brp_mysqli_fetch_array($exe)){
		// if($unit_id == $row['product_base_unit']){
		// 	$accept_qty = $row['product_qty'];
		// }else{
		// 	$accept_qty = $row['product_conv_qty'];
		// }


			if($row['rate_unit'] == $row['unit_id']){
			$base_rate = $row['product_rate']; //1000
			$conv_rate = ($row['product_base_qty']/$row['product_conv_qty'])*$base_rate;
		}else{
			$conv_rate = $row['product_rate'];
			$base_rate = ($row['product_conv_qty']/$row['product_base_qty'])*$conv_rate;
		}
		$stock_id=add_stock($dbcon,$product_id,$unit_id,$grn_date,"tbl_grn_trn",$grn_trn_id,$godown_id,$accept_qty,"1",$branch_id,"","","",$batch_id,$batch_no,$base_rate,$conv_rate);
		
	}


	$query_res="select * from tbl_request_product as req where rp_id in (".$po_ref_id.")";
	$result_res=$dbcon->query($query_res);

	$resqty1=$accept_qty;
	// var_dump($resqty1);
	// var_dump($unit_id);
	while($row_res=brp_mysqli_fetch_assoc($result_res)){
		
		$query_ind="select sum(approve_qty) as app_qty from approve_indent as req where rp_id=".$row_res['rp_id'];
		$result_ind=$dbcon->query($query_ind);
		$row_ind=brp_mysqli_fetch_assoc($result_ind);
		$reserve_id="";
		$request_id=$row_res['rp_id'];
		$complaint_id="";
		$sales_order_trn_id="";
		$customer_id = $row_res['customer_id'];

		$used_rese=total_reserve_stock($dbcon,$request_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$customer_id,"wo_allocate");
		$res_pending=$row_ind['app_qty']-$used_rese;
		// var_dump($res_pending);

		// echo "resqty1 : " . $resqty1 . " -- res_pending : " . $res_pending .'---';
		if($resqty1>=$res_pending){
			update_workorder_complete_qty_and_Status($dbcon,$request_id,$res_pending);
			// var_dump('1 qty :  ' . $res_pending);
			grn_sub_trn_wise_reserv_stock_add($dbcon,$res_pending,$unit_id,$grn_date,"",$request_id,$stock_id,$customer_id);

			$resqty1=$resqty1-$res_pending;
		}else if($resqty1 > 0){
			update_workorder_complete_qty_and_Status($dbcon,$row_res['rp_pid'],$resqty1);
			// var_dump('el1 :  ' . $resqty1);
			grn_sub_trn_wise_reserv_stock_add($dbcon,$resqty1,$unit_id,$grn_date,"",$request_id,$stock_id,$customer_id);

			$resqty1=$resqty1-$resqty1;
		}
	}
}


function jobwork_stock_accept($dbcon,$grn_trn_id,$godown_id,$product_id,$qty,$unit_id,$batch_id,$batch_no,$reject_qty){

	$query = "select grn_sub_trn.grn_trn_id,grn_sub_trn.grn_trn_sub_id,grn_sub_trn.product_id,grn_sub_trn.purchaseordertrn_id,grn_sub_trn.job_work_sub_trn_id,grn_sub_trn.product_qty,grn_sub_trn.product_base_unit,grn_sub_trn.customer_id from tbl_grn_sub_trn as grn_sub_trn
	where grn_sub_trn.status=0 and cast(grn_sub_trn.product_qty AS DECIMAL(50,5)) >  cast(grn_sub_trn.product_stock_used_qty AS DECIMAL(50,5)) and grn_sub_trn.grn_trn_id=".$grn_trn_id ;

//var_dump($query);
	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	//var_dump("ss");
	//var_dump($cnt);
	if($cnt>0){

		while($row=brp_mysqli_fetch_array($result)){

			if(!empty($row['job_work_sub_trn_id'])){
				$trn_pending_qty=$row['product_qty']-$row['product_stock_used_qty'];
				if($qty>=$trn_pending_qty){
					$product_qty=$trn_pending_qty;

				}else{
					$product_qty=$qty;
				}

				$query1= "select p_id,rp_id,customer_id from tbl_job_work_sub_trn as job_sub_trn
				where job_sub_trn.job_work_sub_trn_id=".$row['job_work_sub_trn_id'] ;
				$result1=$dbcon->query($query1);
				$cnt1=brp_mysqli_num_rows($result1);

				if($cnt1>0){
					$row1=brp_mysqli_fetch_array($result1);

					$query2 = "select grn_godown,branch_id from tbl_grn_trn as grn_trn
					where grn_trn.grn_trn_id=".$row['grn_trn_id'] ;
					$result2=$dbcon->query($query2);
					$row2=brp_mysqli_fetch_array($result2);

					$stock_date=date("Y-m-d");

					$process=p_id_wise_find_previous_and_next_process($dbcon,$row1['p_id']);
					$process_pr=json_decode($process);

					$next_process_id=$process_pr->next_process_id;
					$next_process_type=$process_pr->next_process_type;
					$next_process_priority=$process_pr->next_process_priority;

					$previous_process_pid=$process_pr->previous_process_pid;

					$workorder_process_id = $process_pr->workorder_process_id;
				//  var_dump($previous_process_pid);
				//  var_dump($next_process_id);
					if($previous_process_pid=="0" && $next_process_id=="0"){
						$qry__2 = "select * from tbl_grn_sub_trn where grn_trn_sub_id=".$row['grn_trn_sub_id'];
						$result__3=$dbcon->query($qry__2);
						$row__3=brp_mysqli_fetch_array($result__3);


					$base_rate = $row__3['process_pus_material_rate'] / $row__3['product_qty']; //1000
					$conv_rate = $row__3['process_pus_material_conv_rate'] / $row__3['product_conv_qty'];
					$stock_id=add_stock($dbcon,$product_id,$unit_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$godown_id,$product_qty,1,$row2['branch_id'],"","",$row1['customer_id'],$batch_id,$batch_no,$base_rate,$conv_rate);
								//product stock add end
					update_workorder_complete_qty_and_Status($dbcon,$row1['rp_id'],$product_qty,$reject_qty);


								//product reserve stock start
					grn_sub_trn_wise_reserv_stock_add($dbcon,$product_qty,$unit_id,$stock_date,$row1['p_id'],$row1['rp_id'],$stock_id,$row1['customer_id']);
								//product reserve stock end

						// die;

				}
				else if($previous_process_pid=="0"){

					//process stock add start
					//$process_stock_id=production_add_process_stock($dbcon,$product_qty,$unit_id,$row1['p_id'],$stock_date,$godown_id,"Grn_sub_trn",$row['grn_trn_sub_id']);
						//process stock add end

						//next process entry start
					//$next_pid=next_process_entry($dbcon,$product_qty,$unit_id,$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id);
						//next process entry end

						//reserve process stock start
					//$process_reserve_id=production_reserve_add_process_stock($dbcon,$product_qty,$unit_id,$next_pid,$process_stock_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id']);
						//reserve process stock end
					


				}else if($next_process_id=="0"){
						//var_dump("cd");
						//last process
							//product stock add start 
					$qry__2 = "select * from tbl_grn_sub_trn where grn_trn_sub_id=".$row['grn_trn_sub_id'];
					$result__3=$dbcon->query($qry__2);
					$row__3=brp_mysqli_fetch_array($result__3);


							$base_rate = $row__3['process_pus_material_rate'] / $row__3['product_qty']; //1000
							$conv_rate = $row__3['process_pus_material_conv_rate'] / $row__3['product_conv_qty'];
							$stock_id=add_stock($dbcon,$product_id,$unit_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$godown_id,$product_qty,1,$row2['branch_id'],"","",$row1['customer_id'],$batch_id,$batch_no,$base_rate,$conv_rate);
							//product stock add end
							update_workorder_complete_qty_and_Status($dbcon,$row1['rp_id'],$product_qty,$reject_qty);
							//reserve stock add start
							grn_sub_trn_wise_reserv_stock_add($dbcon,$product_qty,$unit_id,$stock_date,$row1['p_id'],$row1['rp_id'],$stock_id,$row1['customer_id']);
							//reserve stock add end


						}else{
					//middel process
					//process stock add start

					//$process_stock_id=production_add_process_stock($dbcon,$product_qty,$unit_id,$row1['p_id'],$stock_date,$godown_id,"Grn_sub_trn",$row['grn_trn_sub_id']);
						//process stock add end

						//next process entry start
					//$next_pid=next_process_entry($dbcon,$product_qty,$unit_id,$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id);
						//next process entry stop

						//reserve process stock start
					//$process_reserve_id=production_reserve_add_process_stock($dbcon,$product_qty,$unit_id,$next_pid,$process_stock_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id']);
						//reserve process stock end

						}

				/*$s_qry = "select * from tbl_allocate_process where p_id = " .$row1['p_id'];
				$res_2=$dbcon->query($s_qry);
				$row_2=brp_mysqli_fetch_array($res_2);
				update_completed_process_time_and_qty($dbcon, $row_2['process_id'], $row_2['resource_id'], $row_2['p_ref_id'], $product_qty);
				$s_qryq = "select * from tbl_allocate_process where p_id = " .$next_pid;
				$res_2q=$dbcon->query($s_qryq);
				$row_2q=brp_mysqli_fetch_array($res_2q);
				if($row_2q['pr_process_type']==1){
					resource_schedule_assign_at_process_allocate($dbcon, $row_2q['p_ref_id'], $row_2q['pen_qty'], $next_pid);
				}*/
				
			}

			$dbcon->query("update tbl_grn_sub_trn set product_stock_used_qty=product_stock_used_qty+".$product_qty." where grn_trn_sub_id=".$row['grn_trn_sub_id']."");

		}
	}
	$dbcon->query("update tbl_grn_trn set store_accept = 1 where grn_trn_id=".$grn_trn_id);
}
}


function direct_grn_stock_accept($dbcon,$product_id,$unit_id,$grn_date,$grn_trn_id,$godown_id,$accept_qty,$branch_id,$po_ref_id,$batch_id,$batch_no){

	
	$stock_id=add_stock($dbcon,$product_id,$unit_id,$grn_date,"tbl_grn_trn",$grn_trn_id,$godown_id,$accept_qty,"1",$branch_id,"","","",$batch_id,$batch_no);

}

function res_sowise($dbcon,$sales_ordertrn_id){
	$query_rstock="select * from work_order_reserve_temp as i
	where i.status = 0 and i.sales_ordertrn_id =".$sales_ordertrn_id;
	$result_rstock=$dbcon->query($query_rstock);
	while($row_rstock=brp_mysqli_fetch_assoc($result_rstock)){
		$reserve_qty=$row_rstock['reserve_qty'];
		$batch_where="";
		if(!empty($row_rstock['stock_id'])){
			$batch_where=" and i.stock_id=".$row_rstock['stock_id'];
		}
		$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
		where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) ".$batch_where." and i.product_id=".$row_rstock['product_id']." and i.godown_id=".$row_rstock['godown_id'];
		$result_dstock=$dbcon->query($query_dstock);
		while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
			if($row_dstock['convert_unit']==$row_rstock['unit_id']){
				$pending_stock=$row_dstock['pending_conv_stock'];
			}else{
				$pending_stock=$row_dstock['pending_base_stock'];	
			}
			if($reserve_qty>0){
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

				$info_rese['cdate']					= date("Y-m-d H:i:s");
				$info_rese['user_id']				= $_SESSION['user_id'];
				$info_rese['company_id']			= $_SESSION['company_id'];		

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

					/*$info_e['sales_ordertrn_id']	=$row_rstock['sales_ordertrn_id'];
					$info_e['product_id']			=$row_rstock['product_id'];
					$info_e['product_qty']			=$info_rese['base_stock'];
					$info_e['godown_id']			=$info_rese['godown_id'];
					$info_e['unit_id']				=$info_rese['base_unit'];
					$info_e['allocate_qty']			=$info_rese['base_stock'];
					$info_e['remaning_invoice_qty']	=$info_rese['base_stock'];
					
					$info_e['cdate']				=date("Y-m-d");
					$info_e['company_id']			=$_SESSION['company_id'];
					$info_e['user_id']				=$_SESSION['user_id'];
					$inserinvoiceidexp=add_record('tbl_sales_order_production_trn', $info_e, $dbcon,$row_dstock['branch_id']);*/
				}
			}
		}
	}

	function res_rpid_wise($dbcon,$rp_id){
		$query_rstock="select * from work_order_reserve_temp as i
		where i.status=0 and i.rp_id=".$rp_id;
		$result_rstock=$dbcon->query($query_rstock);
		while($row_rstock=brp_mysqli_fetch_assoc($result_rstock)){
			$reserve_qty=$row_rstock['reserve_qty'];
			$batch_where="";
			if(!empty($row_rstock['stock_id'])){
				$batch_where=" and i.stock_id=".$row_rstock['stock_id'];
			}

						//$customer_id = $POST['customer_id'];

			$whr_cust = "";
						/*if($customer_id !="" && $customer_id !="0"){
							$whr_cust = " and customer_id = " . $customer_id;
						}*/
						$query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i

						where stock_status=0 and i.branch_id=".$row['branch_id']." and stock_flage=1 and  cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) ".$batch_where." and product_id = ".$row_rstock['product_id']." and i.godown_id=".$row_rstock['godown_id'] . $whr_cust;
						$result_dstock=$dbcon->query($query_dstock);
						while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
							if($row_dstock['convert_unit']==$row_rstock['unit_id']){
								$pending_stock=$row_dstock['pending_conv_stock'];
							}else{
								$pending_stock=$row_dstock['pending_base_stock'];	
							}
							if($reserve_qty>0){
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

								$que_s="select perent_id from tbl_request_product as ta where rp_id=".$row_rstock['rp_id'];
								$rs_dwi=$dbcon->query($que_s);
								$re_df=brp_mysqli_fetch_assoc($rs_dwi);

								$que_s1="select p_id from tbl_allocate_process as ta where p_status != 2 and previous_process_id=0 and p_ref_id=".$re_df['perent_id'];
								$rs_dwi1=$dbcon->query($que_s1);
								$re_df1=brp_mysqli_fetch_assoc($rs_dwi1);
								

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
								$info_rese['p_id']				= $re_df1['p_id'];
								$info_rese['stock_id']			= $row_dstock['stock_id'];
								
								$info_rese['cdate']				= date("Y-m-d H:i:s");
								$info_rese['user_id']			= $_SESSION['user_id'];
								$info_rese['company_id']		= $_SESSION['company_id'];		
								//$info_rese['customer_id']		= $POST['customer_id'];		
								// var_dump($info_rese);					
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
								
								$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$info_rese['stock_id'] , $dbcon);
							}
							

						}
					}
				}

				function returnable_challan_effect($dbcon,$id){
					$que="select * from tbl_returnable_channal_item as ta where id=".$id;
					$rs_di=$dbcon->query($que);
					while($re=brp_mysqli_fetch_assoc($rs_di)){
						$stock_qty = 0;
						$currentStock = get_godown_stock_check($dbcon, $re['item_id'], $re['item_unit_id']);
			//var_dump($currentStock);
						if(count($currentStock) > 0){
							$approved_stock = $re['rr_approve_qty'];
							foreach($currentStock as $key => $value) {
								if($value >= $approved_stock){
									$product_id = $re['item_id'];
									$unit_id = $re['item_unit_id'];
									$stock_date = date('Y-m-d',strtotime($re['created_at']));
									$ref_name = "returning_receipt";
									$ref_id = $re['id'];
									$godown_id = $key;
									$stock_qty = $approved_stock;
									$stock_flag = '2';
									$branch_id = $re['branch_id'];
									add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id);
									break;
								}else{
									$product_id = $re['item_id'];
									$unit_id = $re['item_unit_id'];
									$stock_date = date('Y-m-d',strtotime($re['created_at']));
									$ref_name = "returning_receipt";
									$ref_id = $re['id'];
									$godown_id = $key;
									$stock_qty = $value;
									$stock_flag = '2';
									$branch_id = $re['branch_id'];
									add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id);
									$approved_stock = $approved_stock - $value;
								}
							}
							/*$info['rr_approve_qty'] = $r['rr_approve_qty'] + $stock_qty;
							$info['rr_disapprove_qty'] = 0;
				// End Stock Approve
							$updateid=update_record('tbl_returnable_channal_item', $info, "id=".$POST['returnable_channal_id'] , $dbcon);*/

						}
					}
				}

				?>
