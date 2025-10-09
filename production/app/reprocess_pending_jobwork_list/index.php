<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

$companyConfiguration = getCompanyConfiguration($dbcon);
if(brp_strtolower($POST['mode']) == "fetch") {
	
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$where_db = check_branch('ap', $branch_id);
	
	$where='';

	
			$appData = array();
			$i=1;
			$aColumns = array('p.product_type', 'p.product_name', 'tc.cat_name', 'pro.process_name', 'branc.branch_name', 'rap.p_qty as ap_qty', 'rap.pen_qty as apen_qty', 'IFNULL(apta.end_qty,0) as end_qty','strtt_qty as strtt_qty','rap.p_id as allocate_id','res.job_card_no as job_card_no','l.l_name as vender_name', 'p.product_icode','setm.po_req_no','setm.po_req_date','setm.sales_order_no','job.job_work_no','job.chalan_no','res.job_card_no','rap.*','job.job_work_id');
			$sIndexColumn = "rap.p_id";
			$isWhere = array("rap.pr_process_type='2' and rap.p_status in(0,1) and rap.company_id=".$_SESSION['company_id']." ".$where." ".$where_db."");
			$sTable = "tbl_allocate_re_process as rap";			
			$isJOIN = array('left join product_mst as p on p.product_id=rap.p_product_id 
									left join tbl_allocate_process as ap on ap.p_id=rap.pt_alloc_id 
									left join tbl_category as tc on p.product_category=tc.cat_id 
									left join (select sum(qty) as end_qty,re_pro_p_id from tbl_reprocess_trn_history where process_type=2 group by pt_alloc_id) as apta on apta.re_pro_p_id=ap.p_id 
									left join (select sum(qty) as strtt_qty,re_pro_p_id from tbl_reprocess_trn_history where process_type=1) as apta1 on apta1.re_pro_p_id=ap.p_id 
									left join process_mst as pro on ap.process_id=pro.process_id 
									left join branch_mst as branc on branc.branch_id=ap.branch_id 
									left join tbl_request_product as res on res.rp_id=ap.p_ref_id 
									left join tbl_set_main_process as setm on setm.sp_id=res.sp_id
									left join tbl_batch_data as bt ON bt.batch_id = rap.batch_id
									left join tbl_grn_trn as grn_trn on bt.grn_trn_id = grn_trn.grn_trn_id
									left join tbl_grn_sub_trn as s_trn on s_trn.grn_trn_id = grn_trn.grn_trn_id
									left join tbl_job_work as job on job.job_work_id = s_trn.jobwork_id
									left join tbl_ledger as l on l.l_id = job.vender_id
									');
			$hOrder = " rap.p_id asc";
			$hGroupby = array();
			include($include.'pagging.php');
			
			$appData = array();
			$id=1;
			
			foreach($sqlReturn as $rel) {
				$allocate_id = $rel['allocate_id'];
				$min_working_qty=$rel['ap_qty'];

				$q = "SELECT IFNULL(sum(trn.product_base_qty),0) as used_qty FROM `tbl_job_work_sub_trn` as trn  
					left join tbl_job_work_trn as job_work_trn on job_work_trn.job_work_trn_id =  trn.job_work_trn_id
					where trn.short_close = 0 AND job_work_trn.short_close = 0 AND job_work_sub_trn_status = 0 and p_id IN (".$allocate_id.")  and job_work_trn.job_work_trn_status in(0,1) and trn.is_reprocess = 1";
				$job_trn=$dbcon->query($q);
				$job_trn_result = brp_mysqli_fetch_assoc($job_trn);
				$jobwork_working_qty = $job_trn_result['used_qty'];

				if($min_working_qty - $jobwork_working_qty > 0){
					if($min_working_qty > 0)
					{
						
						$row_data[] = $rel['sr'];
						$row_data[] = $rel['po_req_no'];
						$row_data[] = $rel['job_card_no'];
						$row_data[] = $rel['job_work_no'];
						$row_data[] = $rel['chalan_no'];
						$row_data[] = $rel['product_name'];
						$row_data[] = $rel['process_name'];
						$row_data[] = $rel['vender_name'];
						$row_data[] = $rel['ap_qty'];
						$row_data[] = $rel['apen_qty'];
						$row_data[] = ($rel['end_qty'] == '') ? 0 : $rel['end_qty'];
						if($_SESSION['branch_id']==0){ 
							$row_data[] = $rel['branch_name'];
						}
						
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create Jobwork" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'create_reprocess_jobwork/'.$rel['job_work_id'].'/'.$rel['p_id'].'/'.$rel['p_product_id'].'/'.$rel['process_id'].'"><i class="fa fa-plus"></i></a>';
					
						$row_data[] = $add_po_btn;
						$appData[] = $row_data;
						$id++;
					}
				}

			}
			$output['aaData'] = $appData;
			echo brp_json_encode( $output );
		}else if(brp_strtolower($POST['mode'])== "fetch_jobwork"){

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('job', $branch_id);
			
			$where='';

					$where .= ' and job_work_type = 2 and job.job_work_status = 0';
					$appData = array();
					$i=1;
					$aColumns = array('job.job_work_id','job.job_work_no','job.job_work_date','job.vehicle_no','l.l_name','branc.branch_name','job.g_total','job.release_status','job.request_status','job.vender_id','job.grn_complete_status','job.short_close');
					$sIndexColumn = "job.job_work_id";
					$isWhere = array("job.job_work_status = 0 and job.company_id=".$_SESSION['company_id']." ".$where." ".$where_db."");
					$sTable = "tbl_job_work as job";			
					$isJOIN = array('left join branch_mst as branc on branc.branch_id=job.branch_id','left join tbl_ledger as l on l.l_id = job.vender_id');
					$hOrder = " job.job_work_id asc";
					$hGroupby = array();
					include($include.'pagging.php');
					
					$appData = array();
					$id=1;
					
					foreach($sqlReturn as $rel) {

						 $qry =  " SELECT GROUP_CONCAT(rp.job_card_no) as job_card_no FROM tbl_job_work_sub_trn as strn 
									LEFT JOIN tbl_job_work_trn as trn ON trn.job_work_trn_id = strn.job_work_trn_id
									LEFT JOIN tbl_request_product as rp ON rp.rp_id = strn.rp_id
									WHERE trn.job_work_trn_status != 2 AND strn.job_work_sub_trn_status !=2 AND trn.job_work_id = ". $rel['job_work_id']." GROUP BY trn.job_work_id";	
						$jc_row = brp_mysqli_fetch_assoc($dbcon->query($qry));

						$row_data = array();
						$row_data[] = $id;
						$row_data[] = $rel['job_work_no'];
						$row_data[] = date('d M, Y',strtotime($rel['job_work_date']));
						$row_data[] = $jc_row['job_card_no'];
						$row_data[] = $rel['l_name'];
						$row_data[] = $rel['vehicle_no'];
						$row_data[] = $rel['branch_name'];
						$row_data[] = $rel['g_total'];
						$short_close='';
						if($rel['grn_complete_status'] == '0'){
							$short_close='<a onclick="jobwork_shortclose('.$rel['job_work_id'].')" class="btn btn-xs btn-danger" data-original-title="Short Close Jobwork" data-toggle="tooltip" data-placement="top"><i class="fa fa-close"></i></a>';	
						}

						if($rel['grn_complete_status'] == '1' && $rel['short_close'] == '1'){
							$short_close='<a onclick="revert_jobwork_shortclose('.$rel['job_work_id'].')" class="btn btn-xs btn-danger" data-original-title="Undo Jobwork Short Close" data-toggle="tooltip" data-placement="top"><i class="fa fa-undo"></i></a>';
						}

						if($companyConfiguration['store_approval'] == '1' && $rel['request_status'] == '0' || $companyConfiguration['store_approval'] == '0' && $rel['chalan_status'] == '0'){
							$add_po_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit Jobwork" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'job_work_edit_new/'.$rel['job_work_id'].'/'.$rel['vender_id'].'"><i class="fa fa-pencil"></i></a>';
						}


						$row_data[] = $short_close;
						$appData[] = $row_data;
						$id++;
					}
			$output['aaData'] = $appData;
			echo brp_json_encode( $output );
		}

		else if(brp_strtolower($POST['mode'])== "get_process_list")
		{
			$product_id = $POST['product_id'];
			$branch_id = $POST['branch_id'];
			
			 $qry = "SELECT pro.process_name,ap.process_id as process_id FROM tbl_allocate_re_process as ap left join process_mst as pro on ap.process_id=pro.process_id where ( 1 AND ap.pr_process_type='2' and ap.p_status in(0,1) and ap.company_id=1 and ap.p_status IN (0,1) and ap.p_product_id = " .$product_id ." and ap.branch_id = ".$branch_id.") Group by ap.p_product_id, ap.process_id,ap.branch_id ORDER BY ap.p_id asc";
			
			$result=$dbcon->query($qry);
			
			$str .= '<option value="">Choose Process</option>';
                                               
         while($pro_res=brp_mysqli_fetch_assoc($result)){
            $str .=  '<option value="'.$pro_res['process_id'].'">'.$pro_res['process_name'].'</option>';
         }

                                           
			echo $str;
		}
		else if(brp_strtolower($POST['mode'])== "get_series_no_jobwork")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=38 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(brp_strtolower($POST['mode'])== "load_invoiceno_jobwork")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			
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
			echo brp_json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])== "convert_qty")
		{
			$row=array();
			if($POST["type"]=="1"){
				$type="conv_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
			}else{
				$ret_qty="0";
			}
			//var_dump($ret_qty);
			$ret_qty_new=number_format($ret_qty, 5, ".", "");
			//$ret_qty=$ret_qty;
		//	echo $ret_qty;
			$row['show_qty']=$ret_qty_new;
			$row['hide_qty']=$ret_qty;
			echo json_encode($row);
		}

		else if(brp_strtolower($POST['mode'])== "get_jobwork_detail_data")
		{
			$product_id = $POST['product_id'];
			$process_id = $POST['process_id'];
			$branch_id = $POST['branch_id'];
			
			 $qry = "SELECT p.product_type, p.product_name, tc.cat_name,p.product_base_unit,p.product_conv_unit, pro.process_name, branc.branch_name, u.unit_name, sum(ap.p_qty) as ap_qty, sum(ap.pen_qty) as apen_qty, IFNULL(apta.end_qty,0) as end_qty, IFNULL(strtt_qty,0) as strtt_qty, GROUP_CONCAT(ap.p_id ORDER BY `ap`.`p_id` ASC) as allocate_id, ap.* FROM tbl_allocate_re_process as ap left join product_mst as p on p.product_id=ap.p_product_id left join tbl_category as tc on p.product_category=tc.cat_id left join (select sum(qty) as end_qty,re_pro_p_id from tbl_reprocess_trn_history where process_type=2 group by pt_alloc_id) as apta on apta.re_pro_p_id=ap.p_id left join (select sum(qty) as strtt_qty,re_pro_p_id from tbl_reprocess_trn_history where process_type=1 group by pt_alloc_id) as apta1 on apta1.re_pro_p_id=ap.p_id left join process_mst as pro on ap.process_id=pro.process_id left join branch_mst as branc on branc.branch_id=ap.branch_id left join unit_mst as u on u.unitid=p.product_base_unit where ( 1 AND ap.pr_process_type='2' and ap.p_status in(0,1) and ap.company_id=1 and ap.p_status IN (0,1) and ap.p_product_id = ".$product_id." and ap.process_id = ". $process_id." and ap.branch_id = ".$branch_id.") Group by ap.p_product_id, ap.process_id,ap.branch_id ORDER BY ap.p_id asc";
			
			$result=$dbcon->query($qry);
			$rel=brp_mysqli_fetch_assoc($result);

			$row=array();
			$allocate_id = $rel['allocate_id'];

				$start_qty_data = "SELECT IFNULL(sum(qty),0) as start_qty_valua FROM `tbl_reprocess_trn_history` where process_type = 1 and pt_alloc_id IN (".$allocate_id.") ";
				$start_result=$dbcon->query($start_qty_data);
				$start_qty_result = brp_mysqli_fetch_assoc($start_result);
				$total_start_qty = $start_qty_result['start_qty_valua'];

				$finish_qty_data = "SELECT sum(qty) as start_qty_valua FROM `tbl_reprocess_trn_history` where process_type = 2 and pt_alloc_id IN (".$allocate_id.") ";
				$finish_result=$dbcon->query($finish_qty_data);
				$finish_qty_result = brp_mysqli_fetch_assoc($finish_result);
				$total_finsih_qty = $finish_qty_result['start_qty_valua'];

				$current_start_qty = $total_start_qty - $total_finsih_qty;

				$req_working_qty = $rel['apen_qty']-$current_start_qty;
				
				$min_working_qty=$rel['ap_qty'];

				$where = "";
				if(!empty($POST['edit_id'])){
						$where = " and job_work_trn_id != " . $POST['edit_id'];
				}

				$q = "SELECT IFNULL(sum(product_base_qty),0) as used_qty FROM `tbl_job_work_sub_trn` where job_work_sub_trn_status = 0 and is_reprocess = 1 and p_id IN (".$allocate_id.") " . $where;
				$job_trn=$dbcon->query($q);
				$job_trn_result = brp_mysqli_fetch_assoc($job_trn);
				$job_trn_qty = $job_trn_result['used_qty'];

				$jobwork_working_qty = 0;

				// var_dump($min_working_qty);

				$row['qty'] = $rel['ap_qty'];
				$row['working_qty'] = $min_working_qty - $job_trn_qty;
				// $row['working_qty'] = $min_working_qty;
				$row['start_qty'] =  $total_start_qty;
				$row['unit_name'] = $rel['unit_name'];
				$row['base_unit'] = $rel['product_base_unit'];
				$row['conv_unit'] = $rel['product_conv_unit'];
				$row['p_id'] = $allocate_id;
				$row['description'] = $rel['description'];
                                           
			echo brp_json_encode($row);
		}
		else if(brp_strtolower($POST['mode']) == "fieldadd") {

				$branch_id = isset($POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

				$start_qty=$POST['product_base_qty'];
				
				// $info_jobwork_trn['job_work_id']			= $job_work_id;
				$info_jobwork_trn['process_id']				= $POST['process_id'];
				$info_jobwork_trn['product_id']				= $POST['product_id'];
				$info_jobwork_trn['product_base_qty']		= $start_qty;
				$info_jobwork_trn['product_base_unit']		= $POST['product_base_unit'];
				$info_jobwork_trn['product_con_qty']		= $POST['product_conv_qty'];
				$info_jobwork_trn['product_con_unit']		= $POST['product_conv_unit'];
				
				//pathik start 14-02-2022
				
				$info_jobwork_trn['material_unit']		= $POST['material_unit'];
				$info_jobwork_trn['material_qty']		= $POST['material_qty'];
				$info_jobwork_trn['is_reprocess']		= 1;
				
				//pathik end 14-02-2022

				// $info_jobwork_trn['remark']					= $POST['remark'];
				// $info_jobwork_trn['purchaseordertrn_id'] = $POST['purchase_id'];
				$info_jobwork_trn['pr_rate']				= $POST['rate'];
				$info_jobwork_trn['rate_unit']				= $POST['product_base_unit'];
				$info_jobwork_trn['description']				= $POST['description'];
				
				$info_jobwork_trn['cdate']						= date("Y-m-d H:i:s");
				$info_jobwork_trn['user_id']					= $_SESSION['user_id'];
				$info_jobwork_trn['company_id']					= $_SESSION['company_id'];
				if(empty($POST['edit_id'])){
					$info_jobwork_trn['job_work_trn_status']	= 3;
					if($companyConfiguration['store_approval'] == '1'){
						$info_jobwork['request_status'] = 0;
					}else{
						$info_jobwork['request_status'] = 1;
						$info_jobwork['release_status'] = 1;
					}
					$job_work_trn_id=add_record('tbl_job_work_trn',$info_jobwork_trn, $dbcon,$branch_id);
				}else{
					$updateid=update_record('tbl_job_work_trn',$info_jobwork_trn,"job_work_trn_id=".$POST['edit_id'] , $dbcon);
					$job_work_trn_id=$POST['edit_id'];
				}


				$info_jobwork_sub_trn['job_work_trn_id']	= $job_work_trn_id;
				$info_jobwork_sub_trn['job_work_sub_trn_status']	= '0';
				$updateid=update_record('tbl_job_work_sub_trn',$info_jobwork_sub_trn,"job_work_trn_id=0 and job_work_sub_trn_status = 3 and p_id in(".$POST['p_id'].") and user_id = " . $_SESSION['user_id'], $dbcon);

				$info_jobwork_strn['job_work_sub_trn_status']	= 2;
				$updateid=update_record('tbl_job_work_sub_trn',$info_jobwork_strn,"temp_delete=1 and p_id in(".$POST['p_id'].") and user_id = " . $_SESSION['user_id'], $dbcon);

				// $query="select p_id,p_qty,start_qty,p_ref_id from tbl_allocate_process where p_id in (".$POST['p_id'].")";
				// $result=$dbcon->query($query);
				// $cnt=brp_mysqli_num_rows($result);
				// if($cnt){
				// 	$allocate_process_qty=0;
				// 	while($row=brp_mysqli_fetch_assoc($result)){
				// 		$allocate_process_qty=($row['p_qty']-$row['start_qty']);
				// 		$working_qty=production_start_count_using_p_id($dbcon,$row['p_id']);


				// 		if($start_qty<$working_qty){
				// 			$working_qty=$start_qty;
				// 		}
				// 		if($working_qty!="0" && $allocate_process_qty!="0"){
				// 			if($working_qty>=$allocate_process_qty){
				// 				//use $allocate_process_qty
				// 				$used_qty=$allocate_process_qty;
				// 			}else{
				// 				//use $working_qty 
				// 				$used_qty=$working_qty;
				// 			}
				// 			if($used_qty>0){
				// 				/*$allocate_process_start_qty=$row['start_qty']+$used_qty;
				// 				$info_allocate['start_qty']		= $allocate_process_start_qty;
				// 				$info_allocate['p_status']		= 1;
				// 				$info_allocate['task_status']	= 1;
				// 				$updatetrnid=update_record('tbl_allocate_process',$info_allocate,"p_id=".$row['p_id'] , $dbcon);
								
				// 				//location common_functions 
				// 				add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$info_jobwork_trn['product_id'],$info_jobwork_trn['process_id'],$used_qty,"0");*/
								
				// 				$info_jobwork_sub_trn['job_work_trn_id']	= $job_work_trn_id;
				// 				$info_jobwork_sub_trn['product_id']			= $info_jobwork_trn['product_id'];
				// 				$info_jobwork_sub_trn['product_base_qty']	= $used_qty;
				// 				$info_jobwork_sub_trn['product_base_unit']	= $info_jobwork_trn['product_base_unit'];
				// 				$info_jobwork_sub_trn['product_con_qty']	= $used_qty;
				// 				$info_jobwork_sub_trn['product_con_unit']	= $info_jobwork_trn['product_con_unit'];
				// 				$info_jobwork_sub_trn['p_id']				= $row['p_id'];
				// 				$info_jobwork_sub_trn['rp_id']				= $row['p_ref_id'];
				// 				$info_jobwork_sub_trn['purchaseordertrn_id'] = $POST['purchase_id'];
				// 				$info_jobwork_sub_trn['pr_rate']				= $POST['rate'];;
								
				// 				$info_jobwork_sub_trn['cdate']				= date("Y-m-d H:i:s");
				// 				$info_jobwork_sub_trn['user_id']			= $_SESSION['user_id'];
				// 				$info_jobwork_sub_trn['company_id']			= $_SESSION['company_id'];

				// 				// echo "<pre>";
				// 				// print_r($info_jobwork_sub_trn);

				// 				if(empty($POST['edit_id'])){
				// 					if($companyConfiguration['store_approval'] == '1'){
				// 							$info_jobwork['request_status'] = 0;
				// 						}else{
				// 							$info_jobwork['request_status'] = 1;
				// 							$info_jobwork['release_status'] = 1;
				// 						}
				// 					$job_work_sub_trn_id=add_record('tbl_job_work_sub_trn',$info_jobwork_sub_trn, $dbcon,$branch_id);
				// 				}else{
				// 					$updateid=update_record('tbl_job_work_sub_trn',$info_jobwork_sub_trn,"p_id = ".$row['p_id']." and job_work_trn_id=".$POST['edit_id'] , $dbcon);
				// 					$job_work_trn_id=$POST['edit_id'];
				// 				}
								
				// 				$start_qty=$start_qty-$used_qty;
				// 				// $info_job_up['task_status']	= $row['product_version'];
				// 				// $updatetrn1id=update_record('tbl_job_work_trn',$info_job_up,"job_work_trn_id=".$job_work_trn_id , $dbcon);
								
								
				// 			}
							
				// 		}
				// 	}
				// }
				echo '1';
		}

		else if(brp_strtolower($POST['mode']) == "load_jobwork_product") {
			$qry = "SELECT p.product_name,ap.p_product_id as product_id FROM tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0) as apta1 on apta1.pt_alloc_id=ap.p_id left join process_mst as pro on ap.process_id=pro.process_id left join branch_mst as branc on branc.branch_id=ap.branch_id where ( 1 AND ap.pr_process_type='2' and ap.p_status in(0,1) and ap.company_id=1 and ap.p_status IN (0,1) and ap.branch_id = ".$POST['branch_id'].") Group by ap.p_product_id, ap.process_id,ap.branch_id ORDER BY ap.p_id asc";

				$pro_result = $dbcon->query($qry);
				$str = "<option value=''>Choose Product</option>";
				while ($pro_res = brp_mysqli_fetch_assoc($pro_result)) {
              // if($pro_res['product_id']==$product_id) { $sel="selected='selected'"; }
              $str .= '<option  value="' . $pro_res['product_id'] . '">' . $pro_res['product_name'] . '</option>';
           }

           echo $str;

		}
		else if(brp_strtolower($POST['mode']) == "load_tempoutward") {
		
	$query="select job_trn.*,product.product_name,pro.process_name,u.unit_name,job_sub_trn.p_id,munit.unit_name as munit_name from tbl_job_work_trn as job_trn
		left join tbl_job_work_sub_trn as job_sub_trn on job_sub_trn.job_work_trn_id=job_trn.job_work_trn_id 
		left join product_mst as product on product.product_id=job_trn.product_id 
		left join process_mst as pro on job_trn.process_id=pro.process_id
		left join unit_mst as u on u.unitid=job_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=job_trn.product_con_unit
		left join unit_mst as munit on munit.unitid=job_trn.material_unit
		where job_trn.branch_id = ".$POST['branch_id']." and job_trn.user_id = ".$_SESSION['user_id']." and job_trn.job_work_trn_status = 3 and job_trn.is_reprocess = 1 group by job_trn.job_work_trn_id";
		$result=$dbcon->query($query);

		echo '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="10%">Product Name</th>
		<th class="text-center" width="10%">Process Name</th>
		<th class="text-center" width="20%">Description</th>
		<th class="text-center" width="10%">Quantity</th>
		<th class="text-center" width="5%">Unit</th>
		<th class="text-center" width="5%">Material Unit</th>
		<th class="text-center" width="5%">Material Quantity</th>
		<th class="text-center" width="10%">Rate
		</th>
		<th class="text-center" width="10%">Total Amount
		</th>
		<th class="text-center hide_act_add" width="5%">Action </th>
		</tr>
		<tbody id="fil_product_tbl">';
		$totalqty = 0;
		$total_amount = 0;
		if(brp_mysqli_num_rows($result)>0)
		{


			$i=1;
			
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$amount = 0;
				$totalqty = $totalqty + $rel['product_base_qty'];
				$rate = 0;
				if($rel['pr_rate'] !=""){
					$rate = $rel['pr_rate'];
				}
				echo '<tr id="fieldtr'.$i.'" >

				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				
				<td style="vertical-align:top;">
				'.$rel['product_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['process_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['description'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['product_base_qty'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['munit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['material_qty'].'
				</td>

				<td style="vertical-align:top;">
				'.$rate.'
				</td>';
				if(!empty($rel['material_qty'])){
					$amount  = $rel['material_qty']*$rate;
				}else{
					$amount  = $rel['product_base_qty']*$rate;
				}
				//$amount  = $rel['product_base_qty']*$rate;
				$total_amount = $total_amount + $amount;
				echo '<td style="vertical-align:top;">
				'.$amount.'
				</td>
				
				<td style="vertical-align:top">
				<button type="button" class="btn btn-round btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Edit" onclick="edit_data('.$rel['job_work_trn_id'].','.$rel['p_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

				<button type="button" class="btn btn-round btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Delete" onclick="delete_data('.$rel['job_work_trn_id'].',\'tbl_job_work_trn\',\'job_work_trn_id\');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>
				';

				echo'</td>
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}


		echo '</tbody></table>		
		<input type="hidden" name="jobwork_total_qty" id = "jobwork_total_qty"	value="'.$totalqty.'" />
		</div>';
		if(brp_mysqli_num_rows($result)>0)
		{
			echo '<div class="col-md-3" style="float:right">
				<div class="form-group">
            <label class="col-md-4 control-label">Total *</label>
            <div class="col-md-8 col-xs-11">
                <input id="g_total" name="g_total" type="text" readonly="readonly" class="form-control valid" title="Total" max="0" value="'.$total_amount.'" placeholder="total">
            </div>
			</div>';
		}
		echo '</div>	';

	}

	else if(brp_strtolower($POST['mode'])== "delete_data")
	{
		$row=array();
		
		$info['job_work_trn_status'] = 2;	
		$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);

		$info_sub['job_work_sub_trn_status'] = 2;	
		$updateid=update_record('tbl_job_work_sub_trn', $info_sub,$_POST['whereid']."=".$POST['eid'], $dbcon);
		
		$row['res']="1";

		echo json_encode($row);
		}

	else if(brp_strtolower($POST['mode'])== "preedit")
	{
		 $query="select job_trn.*,product.product_name,pro.process_name,u.unit_name,job_sub_trn.p_id from tbl_job_work_trn as job_trn
		left join tbl_job_work_sub_trn as job_sub_trn on job_sub_trn.job_work_trn_id=job_trn.job_work_trn_id 
		left join product_mst as product on product.product_id=job_trn.product_id 
		left join process_mst as pro on job_trn.process_id=pro.process_id
		left join unit_mst as u on u.unitid=job_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=job_trn.product_con_unit
		where job_trn.job_work_trn_id = " . $POST['id'];

		$result=$dbcon->query($query);
		$rel=brp_mysqli_fetch_assoc($result);
		echo json_encode($rel);
		}
	else	if(brp_strtolower($POST['mode']) == "add") {
		/*echo "<pre>";
		print_r($POST);die;*/
		$add_old_jobwork_no = $POST['add_old_jobwork_no'];
		$branch_id = isset($POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$info_jobwork['job_work_type']		= "2";
			
			$info_jobwork['job_work_date']		= date('Y-m-d',strtotime($_POST['jobwork_date']));
			$info_jobwork['vender_id']			= $POST['vender_id'];
			$info_jobwork['vehicle_no']			= $POST['vehicle_no'];
			$info_jobwork['remark']				= $POST['remark'];
			$info_jobwork['g_total']				= $POST['g_total'];
			$info_jobwork['is_reprocess']				= 1;
			// $info_jobwork['purchaseordertrn_id'] = $POST['purchase_id'];
			
			$info_jobwork['cdate']				= date("Y-m-d H:i:s");
			$info_jobwork['user_id']			= $_SESSION['user_id'];
			$info_jobwork['company_id']			= $_SESSION['company_id'];

			if(!empty($POST['jobwork_edit_id'])){


			$job_work_id=update_record('tbl_job_work', $info_jobwork,"job_work_id = " . $POST['jobwork_edit_id'] , $dbcon);
				$arr['msg'] = 'update';
			}else{
				if($add_old_jobwork_no == '1'){
					$info_jobwork['job_work_no']	= $POST['old_jobwork_no'];
					$info_jobwork['chalan_no']	= $POST['old_jobwork_chalan_no'];
					$info_jobwork['chalan_status']	= 1;
					$info_jobwork['release_status']	= 1;


				}else{
					$info_jobwork['job_work_no']		= load_series_no_using_type_id($dbcon,OUTSIDE_JOB_WORK,$_SESSION['company_id'],$branch_id1);	
				}

				$info_jobwork['request_status'] = 1;
					$info_jobwork['release_status'] = 1;
				
				
				$job_work_id=add_record('tbl_job_work',$info_jobwork, $dbcon,$branch_id);
			if($job_work_id){
					if($add_old_jobwork_no == '0'){
						update_series_no_using_type_id($dbcon,OUTSIDE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
					}
					$info['job_work_trn_status'] = 1;	
					$info['job_work_id'] = $job_work_id;
					
					$info['request_status'] = 1;
					$info['release_status'] = 1;
					$updateid=update_record('tbl_job_work_trn', $info,"job_work_trn_status=3 and user_id = " . $_SESSION['user_id'], $dbcon);
						$arr['msg'] = '1';
					if($add_old_jobwork_no == '1'){
						$q = "select * from tbl_job_work_trn where job_work_trn_status = 1 and release_status = 1 and grn_complete_status = 0 and job_work_id = " . $job_work_id;
					    $result=$dbcon->query($q);
					    $cnt1 = brp_mysqli_num_rows($result);


						while ($j_res = brp_mysqli_fetch_assoc($result)) {
							$start_qty=$j_res['product_base_qty'];
							$job_work_trn_id = $j_res['job_work_trn_id'];

							

							$qry = "select * from tbl_job_work_sub_trn where grn_complete_status = 0 and job_work_sub_trn_status = 0 and job_work_trn_id = " . $job_work_trn_id;
					   		$result1=$dbcon->query($qry);
					   	
					   		while ($trn_res = brp_mysqli_fetch_assoc($result1)) {
					   			 $query="select p_id,p_qty,start_qty,p_ref_id from tbl_allocate_re_process where p_id in (".$trn_res['p_id'].")";
								$result2=$dbcon->query($query);
								$cnt=brp_mysqli_num_rows($result2);

								if($cnt){
									$allocate_process_qty=0;
									while($row=brp_mysqli_fetch_assoc($result2)){
										$allocate_process_qty=($row['p_qty']-$row['start_qty']);
										$working_qty=$row['p_qty'];
										if($start_qty<$working_qty){
											$working_qty=$start_qty;
										}
										
										if($working_qty!="0" && $allocate_process_qty!="0"){
											if($working_qty>=$allocate_process_qty){
												//use $allocate_process_qty
												$used_qty=$allocate_process_qty;
											}else{
												//use $working_qty 
												$used_qty=$working_qty;
											}
											if($used_qty>0){
												$allocate_process_start_qty=$row['start_qty']+$used_qty;
												$info_allocate['start_qty']		= $allocate_process_start_qty;
												$info_allocate['p_status']		= 1;
												
												$updatetrnid=update_record('tbl_allocate_re_process',$info_allocate,"p_id=".$row['p_id'] , $dbcon);
												
												//location common_functions 
												add_reprocess_start_stop_entry($dbcon,$used_qty,$row['p_id'],1);
												
												$info_job_up['product_version']	= $row['product_version'];
												$info_job_up['job_work_trn_status']	= 0;
												// var_dump($job_work_trn_id);
												
												$updatetrn1id=update_record('tbl_job_work_trn',$info_job_up,"job_work_trn_id=".$job_work_trn_id, $dbcon);
												// echo "<pre>"; print_r($info_job_up);

												// var_dump($updatetrn1id);
												$start_qty=$start_qty-$used_qty;
											}
											
										}
									}
								}
					   	}
						}
					}
			}
			
			else{
				$arr['msg'] = '0';
			}
		}
			echo json_encode($arr);

		}
		else if(brp_strtolower($POST['mode'])== "delete_temp_data")
		{
			 $q = "select GROUP_CONCAT(job_work_trn_id) as jobwork_trn_ids from tbl_job_work_trn where job_work_trn_status = 3 and is_reprocess = 1";
	       $job_trn=$dbcon->query($q);
          $job_trn_result = brp_mysqli_fetch_assoc($job_trn);
          $job_trn_ids = $job_trn_result['jobwork_trn_ids'];

	       $info['job_work_trn_status'] = 2;   
	       $updateid=update_record('tbl_job_work_trn', $info,"job_work_trn_id in (".$job_trn_ids.")" , $dbcon);

	       $info_sub['job_work_sub_trn_status'] = 2; 
	       $updateid=update_record('tbl_job_work_sub_trn', $info_sub,"job_work_trn_id in (".$job_trn_ids.")" , $dbcon);

	       $info_tmp['status'] = 2; 
	       $updateid=update_record('tbl_jobwork_qty_tmp', $info_tmp,"job_work_trn_id in (".$job_trn_ids.")" , $dbcon);
 
	       echo "1";
		}
		else if(brp_strtolower($POST['mode'])== "auto_add_temp_data")
		{
			$vendor_id=$POST['vendor_id'];
			$process_id=$_POST['process_id'];
			$product_id=$_POST['product_id'];
			$type=$_POST['type'];
			$p_id=$_POST['p_id'];
			$branch_id = isset($POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			

			$where = " and ap.process_id = '".$process_id."' and  p_product_id = '".$product_id."' ";
		
			if(!empty($branch_id)){
				$branch_where=" and ap.branch_id=".$branch_id;
			}
			$cnt=1;
			$query1="SELECT ap.*, sum(ap.p_qty) as ap_qty, sum(ap.pen_qty) as apen_qty, p.product_type, p.product_name,p.product_base_unit,p.product_conv_unit, IFNULL(apta.end_qty,0) as end_qty, IFNULL(strtt_qty,0) as strtt_qty, pro.process_name, tc.cat_name, GROUP_CONCAT(`ap`.`p_id` ORDER BY `ap`.`p_id` ASC) allocate_id FROM tbl_allocate_re_process as ap
			left join product_mst as p on p.product_id=ap.p_product_id
			left join tbl_category as tc on p.product_category=tc.cat_id 
			left join (select sum(qty) as end_qty,re_pro_p_id from tbl_reprocess_trn_history where process_type=2 group by re_pro_p_id) as apta on apta.re_pro_p_id=ap.p_id 
			left join (select sum(qty) as strtt_qty,re_pro_p_id from tbl_reprocess_trn_history where process_type=1 group by re_pro_p_id) as apta1 on apta1.re_pro_p_id=ap.p_id 
			left join process_mst as pro on ap.process_id=pro.process_id 
			
			WHERE ( 1 AND pr_process_type='2' and ap.p_status IN (0,1) and ap.company_id=".$_SESSION['company_id']." and p_id = ".$p_id." ".$where." ".$branch_where.") 
			Group by ap.p_product_id,ap.process_id
			ORDER BY ap.p_id asc  ";
			$query=$dbcon->query($query1);
			if($query->num_rows > 0){
				while($rel=brp_mysqli_fetch_array($query))
				{
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
					$allocate_id = $rel['allocate_id'];
				
					$start_qty_data = "SELECT sum(qty) as start_qty_valua FROM `tbl_reprocess_trn_history` where process_type = 1 and re_pro_p_id IN (".$allocate_id.") ";
					$start_result=$dbcon->query($start_qty_data);
					$start_qty_result = brp_mysqli_fetch_assoc($start_result);
					$total_start_qty = $start_qty_result['start_qty_valua'];

					$finish_qty_data = "SELECT sum(pt_qty) as start_qty_valua FROM `tbl_reprocess_trn_history` where process_type = 2 and re_pro_p_id IN (".$allocate_id.") ";
					$finish_result=$dbcon->query($finish_qty_data);
					$finish_qty_result = brp_mysqli_fetch_assoc($finish_result);
					$total_finsih_qty = $finish_qty_result['start_qty_valua'];

					$current_start_qty = $total_start_qty - $total_finsih_qty;

					$req_working_qty = $rel['apen_qty']-$current_start_qty;

					$min_working_qty = $rel['ap_qty'];
					

					$job_party_rate = '';
					if($vendor_id!=''){
						$party_rate_sql = "SELECT job_party_rate FROM `tbl_product_job_party_purchase` where job_party_process_id = '".$rel['process_id']."' and job_party_id = '".$vendor_id."' and job_party_product = '".$rel['p_product_id']."' and company_id='".$_SESSION['company_id']."' ";
						$party_rate_result=$dbcon->query($party_rate_sql);
						$party_rate_data = brp_mysqli_fetch_assoc($party_rate_result);
						$job_party_rate = $party_rate_data['job_party_rate'];
					}


				$q = "SELECT IFNULL(sum(trn.product_base_qty),0) as used_qty FROM `tbl_job_work_sub_trn` as trn  
					left join tbl_job_work_trn as job_work_trn on job_work_trn.job_work_trn_id =  trn.job_work_trn_id
					where job_work_sub_trn_status = 0 and p_id IN (".$allocate_id.") and job_work_trn.is_reprocess = 1 and job_work_trn.job_work_trn_status = 1";
				$job_trn=$dbcon->query($q);
				$job_trn_result = brp_mysqli_fetch_assoc($job_trn);
				$jobwork_working_qty = $job_trn_result['used_qty'];
				
				$min_working_qty = $min_working_qty - $jobwork_working_qty;	
					if($min_working_qty>0){

						$branch_id = isset($POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

				$start_qty=$min_working_qty;
				
				// $info_jobwork_trn['job_work_id']			= $job_work_id;
				$info_jobwork_trn['process_id']				= $process_id;
				$info_jobwork_trn['product_id']				= $product_id;
				$info_jobwork_trn['product_base_qty']		= $start_qty;
				$info_jobwork_trn['product_base_unit']		= $rel['product_base_unit'];
				

				$type="conv_unit";
				$conv_qty=convert_stock($dbcon,$start_qty,$product_id,$type);
				$info_jobwork_trn['product_con_qty']		= $conv_qty;;
				$info_jobwork_trn['product_con_unit']		= $rel['product_conv_unit'];
				
				$info_jobwork_trn['pr_rate']				= '0';
				$info_jobwork_trn['job_work_trn_status']	= 3;
				$info_jobwork_trn['description']	= $rel['description'];
				
				$info_jobwork_trn['is_reprocess']		=1;
				$info_jobwork_trn['cdate']						= date("Y-m-d H:i:s");
				$info_jobwork_trn['user_id']					= $_SESSION['user_id'];
				$info_jobwork_trn['company_id']					= $_SESSION['company_id'];
				
				$job_work_trn_id=add_record('tbl_job_work_trn',$info_jobwork_trn, $dbcon,$branch_id);

				$query="select p_id,p_qty,start_qty,p_ref_id from tbl_allocate_re_process where p_id in (".$allocate_id.")";
				$result=$dbcon->query($query);
				$cnt=brp_mysqli_num_rows($result);
				if($cnt){
					$allocate_process_qty=0;
					while($row=brp_mysqli_fetch_assoc($result)){
						$allocate_process_qty=($row['p_qty']-$row['start_qty']);
						$working_qty=$allocate_process_qty;
						
					/*	var_dump('start_qty : '.$start_qty);	
						var_dump('working_qty : '.$working_qty);	
						var_dump('allocate_process_qty : '.$allocate_process_qty);	*/
						if($start_qty<$working_qty){
							$working_qty=$start_qty;
						}
						if($working_qty!="0" && $allocate_process_qty!="0"){
							if($working_qty>=$allocate_process_qty){
								//use $allocate_process_qty
								$used_qty=$allocate_process_qty;
							}else{
								//use $working_qty 
								$used_qty=$working_qty;
							}
							if($used_qty>0){
								
								$info_jobwork_sub_trn['job_work_trn_id']	= $job_work_trn_id;
								$info_jobwork_sub_trn['product_id']			= $info_jobwork_trn['product_id'];
								$info_jobwork_sub_trn['product_base_qty']	= $used_qty;
								$info_jobwork_sub_trn['product_base_unit']	= $info_jobwork_trn['product_base_unit'];

								$type="conv_unit";
								$used_conv_qty=convert_stock($dbcon,$used_qty,$info_jobwork_trn['product_id'],$type);

								$info_jobwork_sub_trn['product_con_qty']	= $used_conv_qty;
								$info_jobwork_sub_trn['product_con_unit']	= $info_jobwork_trn['product_con_unit'];
								$info_jobwork_sub_trn['p_id']				= $row['p_id'];
								$info_jobwork_sub_trn['rp_id']				= $row['p_ref_id'];
								// $info_jobwork_sub_trn['purchaseordertrn_id'] = $POST['purchase_id'];
								$info_jobwork_sub_trn['pr_rate']				= '0';
								$info_jobwork_sub_trn['is_reprocess']		=1;
								$info_jobwork_sub_trn['cdate']				= date("Y-m-d H:i:s");
								$info_jobwork_sub_trn['user_id']			= $_SESSION['user_id'];
								$info_jobwork_sub_trn['company_id']			= $_SESSION['company_id'];

								
								$job_work_sub_trn_id=add_record('tbl_job_work_sub_trn',$info_jobwork_sub_trn, $dbcon,$branch_id);
			
								$start_qty=$start_qty-$used_qty;
							}
							
						}
					}
				}
						$cnt++;
					}
				}
			}

	       echo "1";
		}

	else if(brp_strtolower($POST['mode']) == "load_tempoutward_edit") {
		
	$query="select job_trn.*,product.product_name,pro.process_name,u.unit_name,job_sub_trn.p_id,munit.unit_name as munit_name from tbl_job_work_trn as job_trn
		left join tbl_job_work_sub_trn as job_sub_trn on job_sub_trn.job_work_trn_id=job_trn.job_work_trn_id 
		left join product_mst as product on product.product_id=job_trn.product_id 
		left join process_mst as pro on job_trn.process_id=pro.process_id
		left join unit_mst as u on u.unitid=job_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=job_trn.product_con_unit
		left join unit_mst as munit on munit.unitid=job_trn.material_unit
		where  job_trn.job_work_trn_status = 1 and job_sub_trn.job_work_sub_trn_status = 0  and job_sub_trn.temp_delete = 0 and job_trn.job_work_id = " .$POST['job_work_id']; 
		$result=$dbcon->query($query);

		echo '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="5%">Workorder No</th>
		<th class="text-center" width="10%">Product Name</th>
		<th class="text-center" width="10%">Process Name</th>
		<th class="text-center" width="20%">Description</th>
		<th class="text-center" width="10%">Quantity</th>
		<th class="text-center" width="5%">Unit</th>
		<th class="text-center" width="5%">Material Unit</th>
		<th class="text-center" width="5%">Material Quantity</th>
		<th class="text-center" width="10%">Rate
		</th>
		<th class="text-center" width="10%">Total Amount
		</th>
		<th class="text-center hide_act_add" width="5%">Action </th>
		</tr>
		<tbody id="fil_product_tbl">';
		$totalqty = 0;
		$total_amount = 0;
		if(brp_mysqli_num_rows($result)>0)
		{


			$i=1;
			
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$amount = 0;
				$totalqty = $totalqty + $rel['product_base_qty'];
				$rate = 0;
				if($rel['pr_rate'] !=""){
					$rate = $rel['pr_rate'];
				}
				echo '<tr id="fieldtr'.$i.'" >

				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				<td style="vertical-align:top;">
				'.$rel['workorder_no'].'
				</td>
				
				<td style="vertical-align:top;">
				'.$rel['product_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['process_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['Description'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['product_base_qty'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['munit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['material_qty'].'
				</td>

				<td style="vertical-align:top;">
				'.$rate.'
				</td>';

				if(!empty($rel['material_qty'])){
					$amount  = $rel['material_qty']*$rate;
				}else{
					$amount  = $rel['product_base_qty']*$rate;
				}
				
				$total_amount = $total_amount + $amount;
				echo '<td style="vertical-align:top;">
				'.$amount.'
				</td>
				
				<td style="vertical-align:top">
				<button type="button" class="btn btn-round btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Edit" onclick="edit_data('.$rel['job_work_trn_id'].','.$rel['p_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

				<button type="button" class="btn btn-round btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Delete" onclick="delete_data('.$rel['job_work_trn_id'].',\'tbl_job_work_trn\',\'job_work_trn_id\');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>
				';

				echo'</td>
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}


		echo '</tbody></table>		
		<input type="hidden" name="jobwork_total_qty" id = "jobwork_total_qty"	value="'.$totalqty.'" />
		</div>';
		if(brp_mysqli_num_rows($result)>0)
		{
			echo '<div class="col-md-3" style="float:right">
				<div class="form-group">
            <label class="col-md-4 control-label">Total *</label>
            <div class="col-md-8 col-xs-11">
                <input id="g_total" name="g_total" type="text" readonly="readonly" class="form-control valid" title="Total" max="0" value="'.$total_amount.'" placeholder="total">
            </div>
			</div>';
		}
		echo '</div>	';

	}
else if(brp_strtolower($POST['mode']) == "get_jobwork_rate") {
	$vendor_id = $POST['vendor_id'];
	$product_id = $POST['product_id'];
	$process_id = $POST['process_id'];
	$process_rate_unit = $POST['material_unit'];
	$branch_id = $POST['branch_id'];
	
	if(!empty($process_rate_unit)){
		$where= " and trn.process_rate_unit=".$process_rate_unit;
	}

	$date = date('Y-m-d');
	 $qry = "select trn.price from tbl_jobwork_rate_cardtrn  as trn
	 left join tbl_jobwork_rate_card as mst on mst.jobwork_card_id
	 where mst.is_aproove = 1 AND mst.branch_id = ".$branch_id." and mst.is_active = 0 and trn.jobwork_cardtrn_status = 0 and trn.vendor_id = " . $vendor_id . " and trn.product_id = " . $product_id . " and trn.process_id = " . $process_id . " and trn.affected_date <= '" . $date . "' and trn.valid_date >= '" . $date . "' ".$where." order by trn.jobwork_card_trnid desc limit 1";

	$result=$dbcon->query($qry);
	$rel=brp_mysqli_fetch_assoc($result);


	echo $rel['price'];

}else if(strtolower($POST['mode'])== "wo_jobwork_model_open"){

	$query="select rp.job_card_no, sp.po_req_no,ap.p_id,rp.rp_id,(ap.p_qty - (select IFNULL(sum(product_base_qty),0) from tbl_job_work_sub_trn where job_work_sub_trn_status = 0  and temp_delete = 0 and is_reprocess = 1 and p_id = ap.p_id)) as pending_qty from tbl_allocate_re_process as ap LEFT JOIN tbl_request_product as rp on rp.rp_id = ap.p_ref_id LEFT JOIN tbl_set_main_process as sp on rp.sp_id = sp.sp_id where ap.p_status in (0,1) AND ap.p_id in(".$POST['p_id'].") having pending_qty > 0";
	$rs_batch=$dbcon->query($query);
	$str= '<option value="">Choose Workorder No</option>';
	while($rel=brp_mysqli_fetch_assoc($rs_batch))
	{	
			$str.= '<option value="'.$rel['p_id'].'">'.$rel['job_card_no'].' ('.$rel['po_req_no'].')'.'</option>';
	}

	$html = '<div class="col-md-12">				
	<div class="col-md-5">
	<div class="form-group">
	<label for="edit_zone_name">Jobcard No</label>
	<select class="form-control wo_select2" name="wo_p_id" id="wo_p_id" onChange="get_wo_jobwork_qty(this.value);" >
	"'.$str.'"
	</select>							
	</div>	
	</div>
	<div class="col-md-3">
	<div class="form-group">
	<label for="edit_zone_name">Total Qty</label>
	<input type="number" min="0" class="form-control" name="wo_qty" id="wo_qty" readonly />
	</div>	
	</div>

	<div class="col-md-3">
	<div class="form-group">
	<label for="edit_zone_name">Qty</label>
	<input type="number" min="0" class="form-control numbersOnly" name="qtyforwo" id="qtyforwo" />
	</div>	
	</div>

	<div class="col-md-1">
	<div class="form-group">
	<input type="button" id="add_wo_jobwork_qty" value="+"  class="btn btn-primary" title="Add" onclick="add_wo_jobwork_qty();" 
	style="margin-top: 24px;"  />
	</div>	
	</div>

	</div>';
	$row['html_data'] = $html;
	echo json_encode($row);
}else if(strtolower($POST['mode'])== "validate_qty"){
	if(!empty($POST['edit_id'])){
		$str = " and bst.job_work_trn_id=".$POST['edit_id']." and bst.job_work_sub_trn_status in(0,3) ";
	}else{
		$str = " and bst.job_work_trn_id=0 and bst.job_work_sub_trn_status=3";
	}
	$qry2="SELECT sum(bst.product_base_qty) as qty FROM tbl_job_work_sub_trn as bst where bst.p_id in(".$POST['p_id'].")  and temp_delete = 0 ".$str." ";

	$result2=mysqli_fetch_assoc($dbcon->query($qry2));
	$total_qty = $result2['qty'] + $POST['qtyforwo'];
	// var_dump($total_qty);
	if($total_qty > $POST['product_qty']){
		$row['res']="0";
	}else if($total_qty == $POST['product_qty']){
		$row['res']="1";
	}else{
		$row['res']="2";
	}
	
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "get_wo_jobwork_qty"){
	$p_id = $POST['p_id'];
	

	$qry = "SELECT * FROM tbl_allocate_re_process WHERE p_id = " . $p_id;
	$row = brp_mysqli_fetch_assoc($dbcon->query($qry));
	$min_working_qty=$row['p_qty'];

	$q = "SELECT IFNULL(sum(trn.product_base_qty),0) as used_qty FROM `tbl_job_work_sub_trn` as trn  
				left join tbl_job_work_trn as job_work_trn on job_work_trn.job_work_trn_id =  trn.job_work_trn_id
				where job_work_sub_trn_status = 0 and p_id IN (".$p_id.") and trn.is_reprocess = 1 and job_work_trn.job_work_trn_status = 0 and temp_delete = 0";
	$job_trn=$dbcon->query($q);
	$job_trn_result = brp_mysqli_fetch_assoc($job_trn);
	$jobwork_working_qty = $job_trn_result['used_qty'];

	// echo $min_working_qty - $jobwork_working_qty;
	echo $min_working_qty;
}
else if(strtolower($POST['mode'])== "fetch_wo_jobwork_qty"){
		
	if(!empty($POST['edit_id'])){
		$str = " and bst.job_work_trn_id=".$POST['edit_id']." and bst.job_work_sub_trn_status != 2 ";
	}else{
		$str = " and bst.job_work_trn_id=0 and bst.job_work_sub_trn_status != 2 ";
	}
	$appData = array();
	$i=1;
	$aColumns = array('bst.product_base_qty','rp.job_card_no','sp.po_req_no','bst.job_work_sub_trn_id','bst.p_id');
	$sTable = "tbl_job_work_sub_trn as bst";			
	$isJOIN = array('LEFT JOIN tbl_allocate_process as ap on ap.p_id = bst.p_id','LEFT JOIN tbl_request_product as rp on rp.rp_id = ap.p_ref_id','LEFT JOIN tbl_set_main_process as sp on rp.sp_id = sp.sp_id');
	$sIndexColumn = "bst.job_work_sub_trn_id";
	$where = " bst.user_id = ".$_SESSION['user_id']." and bst.temp_delete = 0 and bst.p_id in(".$POST['p_id'].") ".$str." ";
	$isWhere = array($where);
	$hOrder = "bst.job_work_sub_trn_id desc";
	include($path.'include/pagging.php');
	$id=1;
	$edit = $delete = '';
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['job_card_no'] .' ('.$row['po_req_no'].')';
		$row_data[] = $row['product_base_qty'];
		$delete='';
		
		$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_wo_jobwork_entry('.$row['job_work_sub_trn_id'].')"><i class="fa fa-trash-o"></i></button>';	
		
		$row_data[] = $delete;

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode'])== "delete_wo_jobwork_entry"){
	$row=array();
	$info['temp_delete']=1;	
		
	$job_work_sub_trn_id = $POST['job_work_sub_trn_id'];	

	$updateid=update_record("tbl_job_work_sub_trn", $info, "job_work_sub_trn_id=".$job_work_sub_trn_id, $dbcon);
	
	if($updateid){
		$row['res']="1";
	}
	else{
		$row['res']="0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "jobwork_shortclose"){
	$job_work_id = $POST['job_work_id'];

	$query = "SELECT 	job_work_trn_id FROM tbl_job_work_trn WHERE grn_complete_status = 0 AND job_work_trn_status != 2 AND job_work_id = " . $job_work_id;
	$result = $dbcon->query($query);

	while($row = brp_mysqli_fetch_assoc($result)){

		$query1 = "SELECT job_work_sub_trn_id,job_work_trn_id,product_base_qty,product_id,product_con_qty,p_id FROM  tbl_job_work_sub_trn WHERE grn_complete_status = 0 AND job_work_sub_trn_status != 2 AND job_work_trn_id = " . $row['job_work_trn_id'];
		$result1 = $dbcon->query($query1);

		$trn_short_close_qty = 0;
		$trn_short_close_conv_qty = 0; 
		$info['short_close'] = '1';
		$info['grn_complete_status'] = '1';

		while($row1 = brp_mysqli_fetch_assoc($result1)){
			$short_close_qty = 0;
			$short_close_conv_qty = 0; 
			
			$base_qty = $row1['product_base_qty'];
			$conv_qty = $row1['product_con_qty'];
			
			$qry = "SELECT IFNULL(SUM(product_qty),0) as base_qty,IFNULL(SUM(product_conv_qty),0) as conv_qty from tbl_grn_sub_trn WHERE status = 0 AND jobwork_id = ".$job_work_id." AND job_work_trn_id = ".$row["job_work_trn_id"]." AND job_work_sub_trn_id = " .$row1['job_work_sub_trn_id'];
			$row2 = brp_mysqli_fetch_assoc($dbcon->query($qry));

			$short_close_qty = $base_qty - $row2['base_qty'];
			$short_close_conv_qty=convert_stock($dbcon,$short_close_qty,$row1['product_id'],"conv_unit");

			$info_trn['short_close'] = '1';
			$info_trn['grn_complete_status'] = '1';
			$info_trn['short_close_qty'] = $short_close_qty;
			$info_trn['short_close_conv_qty'] = $short_close_conv_qty;

			$trn_short_close_qty = $trn_short_close_qty + $short_close_qty;
			$trn_short_close_conv_qty = $trn_short_close_conv_qty + $short_close_conv_qty; 


			update_allocate_process_trn_data($dbcon,$row1['p_id'],$short_close_qty,0);

			$updateid=update_record('tbl_job_work_sub_trn',$info_trn,"job_work_sub_trn_id=".$row1['job_work_sub_trn_id'], $dbcon);
		}		
		
		$info['short_close_qty'] = $trn_short_close_qty;
		$info['short_close_conv_qty'] = $trn_short_close_conv_qty;
		$updateid=update_record('tbl_job_work_trn',$info,"job_work_trn_id=".$row['job_work_trn_id'] , $dbcon);

	}
	$job_info['short_close'] = '1';
	$job_info['grn_complete_status'] = 1;
	$job_updateid=update_record('tbl_job_work',$job_info,"job_work_id=".$job_work_id, $dbcon);
	if($job_updateid){
		echo "1";
	}else{
		echo "0";	
	}
	
}
else if(strtolower($POST['mode'])== "revert_jobwork_shortclose"){
	$job_work_id = $POST['job_work_id'];

	$query = "SELECT 	job_work_trn_id FROM tbl_job_work_trn WHERE grn_complete_status = 1 AND short_close = 1 AND job_work_trn_status != 2 AND job_work_id = " . $job_work_id;
	$result = $dbcon->query($query);

	while($row = brp_mysqli_fetch_assoc($result)){

		$query1 = "SELECT job_work_sub_trn_id,job_work_trn_id,product_base_qty,product_id,product_con_qty,short_close_qty,short_close_qty,short_close_conv_qty,p_id FROM  tbl_job_work_sub_trn WHERE grn_complete_status = 1 AND short_close = 1 AND job_work_sub_trn_status != 2 AND job_work_trn_id = " . $row['job_work_trn_id'];
		$result1 = $dbcon->query($query1);

		$info['short_close'] = '0';
		$info['grn_complete_status'] = '0';

		while($row1 = brp_mysqli_fetch_assoc($result1)){
			$info_trn['short_close'] = '0';
			$info_trn['grn_complete_status'] = '0';
			$info_trn['short_close_qty'] = 0;
			$info_trn['short_close_conv_qty'] = 0;

			 update_allocate_process_trn_data($dbcon,$row1['p_id'],$row1['short_close_qty'],1);

			$updateid=update_record('tbl_job_work_sub_trn',$info_trn,"job_work_sub_trn_id=".$row1['job_work_sub_trn_id'], $dbcon);
		}		
		
		$info['short_close_qty'] = 0;
		$info['short_close_conv_qty'] = 0;
		$updateid=update_record('tbl_job_work_trn',$info,"job_work_trn_id=".$row['job_work_trn_id'], $dbcon);

	}
	$job_info['short_close'] = '0';
	$job_info['grn_complete_status'] = 0;
	$job_updateid=update_record('tbl_job_work',$job_info,"job_work_id=".$job_work_id, $dbcon);
	if($job_updateid){
		echo "1";
	}else{
		echo "0";	
	}
	
}
else if(strtolower($POST['mode'])== "add_wo_jobwork_qty"){

	$branch_id = $POST['branch_id'];
	if(!empty($POST['edit_id'])){
		$str = " and job_work_trn_id=".$POST['edit_id']." and status=1 ";
		$info_jobwork_sub_trn['job_work_trn_id']   = $POST['edit_id'];
	}else{
		$str = " and job_work_trn_id=0 and job_work_sub_trn_status=0 ";
	}

	$tr = $dbcon->query("SELECT job_work_sub_trn_id FROM tbl_job_work_sub_trn where temp_delete = 0 and p_id in(".$POST['p_id'].")".$str);
	if($tr->num_rows > 0) {
		$row['res'] = '-1';
	} else {

		$pro_qry = "select * from product_mst where product_id = " . $POST['product_id'];
		$pro_res = brp_mysqli_fetch_assoc($dbcon->query($pro_qry));

		$ap_qry = "select p_ref_id  from tbl_allocate_process where p_id = " . $POST['p_id'];
		$ap_res = brp_mysqli_fetch_assoc($dbcon->query($ap_qry));

		$info_jobwork_sub_trn['product_id']			= $POST['product_id'];
		$info_jobwork_sub_trn['product_base_unit']	= $pro_res['product_base_unit'];
		$info_jobwork_sub_trn['product_con_unit']	= $pro_res['product_conv_unit'];
		$info_jobwork_sub_trn['product_base_qty']	= $POST['qty'];
		$type="conv_unit";
		$used_conv_qty=convert_stock($dbcon,$POST['qty'],$POST['product_id'],$type);

		$info_jobwork_sub_trn['product_con_qty']	= $used_conv_qty;
		$info_jobwork_sub_trn['is_reprocess']	= 1;
		
		$info_jobwork_sub_trn['p_id']				= $POST['p_id'];
		$info_jobwork_sub_trn['rp_id']				= $ap_res['p_ref_id'];
		// $info_jobwork_sub_trn['purchaseordertrn_id'] = $POST['purchase_id'];
		$info_jobwork_sub_trn['pr_rate']				= '0';
			$info_jobwork_sub_trn['job_work_sub_trn_status']				= '3';
		if(!empty($POST['edit_id'])){
			$info_jobwork_sub_trn['job_work_sub_trn_status']				= '0';
		}
		
		$info_jobwork_sub_trn['cdate']				= date("Y-m-d H:i:s");
		$info_jobwork_sub_trn['user_id']			= $_SESSION['user_id'];
		$info_jobwork_sub_trn['company_id']			= $_SESSION['company_id'];

		
		$job_work_sub_trn_id=add_record('tbl_job_work_sub_trn',$info_jobwork_sub_trn, $dbcon,$branch_id);


		if($job_work_sub_trn_id){
			$row['res']="1";
		}
		else{
			$row['res']="0";
		}
	}
	echo json_encode($row);
}



function update_allocate_process_trn_data($dbcon,$p_id,$short_close_qty,$redo = 0){
	$query = "SELECT start_qty FROM tbl_allocate_process WHERE p_id = " .$p_id;
	$row =  brp_mysqli_fetch_assoc($dbcon->query($query));
	if($redo == 0){
		$info['start_qty'] = $row['start_qty'] - $short_close_qty;
	}else{
		$info['start_qty'] = $row['start_qty'] + $short_close_qty;
	}

	$updateid=update_record('tbl_allocate_process',$info,"p_id=".$p_id, $dbcon);


	$query1 = "SELECT pt_id,pt_qty,(pt_qty - pt_used_qty) as pending_qty FROM tbl_allocate_process_trn WHERE p_status = 0 AND  pt_alloc_id = " .$p_id;
	$result = $dbcon->query($query1);
	$i =1;
	while($row1 = brp_mysqli_fetch_assoc($result)){

		if($redo == 0){
			$pending_qty = $row1['pending_qty'];

			if($short_close_qty > 0 && $pending_qty > 0){
				if($short_close_qty >= $pending_qty){
					$sc_qty = $pending_qty;
					$short_close_qty = $short_close_qty - $pending_qty;
				}else{
					$sc_qty = $short_close_qty;
					$short_close_qty = $short_close_qty - $short_close_qty;
				}

				$upd_info['pt_qty'] = $row1['pt_qty'] - $sc_qty;

				$updateid1=update_record('tbl_allocate_process_trn',$upd_info,"pt_id=".$row1['pt_id'], $dbcon);
			}
		}else{
			if($i == 1){
				$upd_info['pt_qty'] = $row1['pt_qty'] + $short_close_qty;
				$updateid1=update_record('tbl_allocate_process_trn',$upd_info,"pt_id=".$row1['pt_id'], $dbcon);
			}
		}
		$i++;
	}

}
?>
