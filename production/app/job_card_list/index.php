<?php

session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRODUCTION_JOBCARD_LIST_SLUG_VIEW,PRODUCTION_JOBCARD_LIST_SLUG_UPDATE
]);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}else {
	$POST = bulk_filter($dbcon,$_GET);
}


$company_config = getCompanyConfiguration($dbcon);		
$production_pro_search = $company_config['production_pro_search'];
$pro_search=explode(",", $production_pro_search);

if(brp_strtolower($POST['mode']) == "fetch") {
	// error_reporting(E_ALL);
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
		//$branch=$_SESSION['branch_id'];
	$branch_where="";
	if(!empty($POST['branch_id'])){
		$branch_where=" and po.branch_id=".$POST['branch_id'];
	}
	$company_where="";
	if(!empty($_SESSION['company_id'])){
		$company_where=" and po.company_id=".$_SESSION['company_id'];
	}
	$where='';
	$appData = array();
	$i=1;
	$aColumns = array('pmst.product_icode','spro.po_req_no','pmst.product_name', 'dr.drawing_number','po.job_card_no','po.priority_status','po.branch_id','po.job_card_date','so.sales_order_no','tc.cat_name','po.in_process_qty','unit.unit_name','po.rp_pid','po.rp_id','bran.branch_name','po.rp_req_qty','bom.bom_version_id','po.bom_id');
	$sIndexColumn = "po.rp_id";
	$isWhere = array("po.status not in (2,3) and job_card_no != '' and po.job_card_status in (".$POST['po_type_status'].")".$branch_where.$company_where);
	$sTable = "tbl_request_product as po";			
	$isJOIN = array('left join tbl_set_main_process as spro on spro.sp_id=po.sp_id','left join product_mst as pmst on pmst.product_id=po.rp_pid','left join tbl_category as tc on pmst.product_category=tc.cat_id','left join unit_mst as unit on unit.unitid=po.process_unit','left join branch_mst as bran on bran.branch_id=po.branch_id','left join tbl_drawing as dr on dr.drawing_id = pmst.drawing_id','left join tbl_bom as bom on bom.bom_id=po.bom_id','left join tbl_sales_ordertrn as sotrn on sotrn.sales_ordertrn_id=spro.sales_order_trn_id','left join tbl_sales_order as so on so.sales_order_id=sotrn.sales_order_id');
	$hOrder = "po.rp_id desc";
	$hGroupby = array("po.rp_id");
	$having_clause='';
	include($include.'pagging.php');
			//echo $squery;
	$appData = array();
	$id=1;
			//print_r($sqlReturn);
	foreach($sqlReturn as $row) {
		$drawing_number = "";
		$item_code = "";
		if(in_array('drawing',$pro_search)){
			$drawing_number = " -- (".$row['drawing_number'].")";
		}
		if(in_array('item',$pro_search)){
			$item_code = " -- (".$row['product_icode'].")";
		}	
		$row_data = array();
		$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
		$row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'">'.$row['sr'].'</a>';
		$row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'">'.$row["job_card_no"].'</a>';
		$row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'">'.date('d M, Y',strtotime($row["job_card_date"])).'</a>';
		$row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'">'.$row["po_req_no"].'</a>';
		$row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'">'.$row["sales_order_no"].'</a>';
		$row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'">'.$row["product_name"].' '.$item_code.' '.$drawing_number.'</a>';
		$row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'">'.$cat_name.'</a>';
		$row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'">'.$row["in_process_qty"]. ' <span class="label label-info">'.$row['unit_name'].'</span></a>';

				/* 
				*  code hide by Sanat :: 27-02-2021 
				*	 comment :: hide below code for showing all work order process in action
				*/

				// $row_data[] = '<a class="" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'jobsheet_print/'.$row['rp_id'].'">'.$row["unit_name"].'</a>';
				
				/*$vale=rand(0,100);
				if($vale<=25){
				$row_data[] = '<div class="progress progress-striped active progress-md">
                                  <div class="progress-bar progress-bar-danger"  role="progressbar" aria-valuenow="'.$vale.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$vale.'%">
                                      <span class="">'.$vale.'% Complete</span>
                                  </div>
                              </div>';
				}else if($vale<=50 && $vale>25){
					$row_data[] = '<div class="progress progress-striped active progress-md">
                                  <div class="progress-bar progress-bar-info"  role="progressbar" aria-valuenow="'.$vale.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$vale.'%">
                                      <span class="">'.$vale.'% Complete</span>
                                  </div>
                              </div>';
				}else if($vale<=75 && $vale>50){
					$row_data[] = '<div class="progress progress-striped active progress-md">
                                  <div class="progress-bar progress-bar-warning"  role="progressbar" aria-valuenow="'.$vale.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$vale.'%">
                                      <span class="sr">'.$vale.'% Complete</span>
                                  </div>
                              </div>';
				}else if($vale<=100 && $vale>75){
					$row_data[] = '<div class="progress progress-striped active progress-md">
                                  <div class="progress-bar progress-bar-success"  role="progressbar" aria-valuenow="'.$vale.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$vale.'%">
                                      <span class="">'.$vale.'% Complete</span>
                                  </div>
                              </div>';
                            }*/
    $row_data[] = $row['priority_status'];
    if($_SESSION['branch_id']==0){
    	$row_data[] = $row["branch_name"];
    }
    $add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Jobcard Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobsheet_print/'.$row['rp_id'].'"><i class="fa fa-print"></i></a>';

    $edit=''; $process="";
    if(in_array(PRODUCTION_JOBCARD_LIST_SLUG_UPDATE,$bulkAccessArray)){

    	$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobcardedit/'.$row['rp_pid'].'/'.$row['branch_id'].'/'.$row['bom_version_id'].'/'.$row['rp_req_qty'].'/'.$row['rp_id'].'"><i class="fa fa-pencil"></i></a>';
    	$qry = "SELECT work.rp_id, pmst.process_name, work.process_type,work.product_id, work.process_id, work.process_priority from tbl_wororder_product_process as work left join process_mst as pmst on pmst.process_id = work.process_id where work.rp_id = ".$row['rp_id']." and  work.product_id =". $row['rp_pid']." order by work.process_priority asc";

					// echo $qry;die;

	  	$res_process=$dbcon->query($qry);
	  	$cnt=brp_mysqli_num_rows($res_process);
	  	if($cnt > 0){
	      while($product_process=brp_mysqli_fetch_assoc($res_process)){
					$qry3 = "SELECT sum(pen_qty) as pen_qty,min(p_status) as p_status from tbl_allocate_process where p_status !=2 and p_ref_id =  " . $row['rp_id'] . " and p_product_id =". $row['rp_pid']." and process_id = ".$product_process['process_id'];
					$res_3=$dbcon->query($qry3);
					$row_3 = brp_mysqli_fetch_assoc($res_3);
					$done_status = $row_3['p_status'];
					if($product_process['process_type'] == '1'){
							$btn_color = "btn-xs btn-success";
					}else{
							$btn_color = "btn-xs btn-primary";
					}

					if($row_3['p_status'] == '0'){
							$pro_func = 'onClick="change_process_type('.$product_process["rp_id"].','.$product_process["product_id"].','.$product_process['process_id'].','.$product_process['process_type'].','.$done_status .')"';
					}else if ($row_3['p_status'] == '1'){
							$done_status = 1;
							$btn_color = "btn-xs btn-danger";
							$pro_func = 'onClick="change_process_type('.$product_process["rp_id"].','.$product_process["product_id"].','.$product_process['process_id'].','.$product_process['process_type'].','.$done_status .')"';
					}else	if($row_3['pen_qty'] == '0' && $row_3['p_status'] == '3'){
						$done_status = 3;
						$btn_color = "btn-xs btn-danger";
						$pro_func = "";
					}

					if($product_process['process_type'] == '1'){ // 1 for inhouse, 2 for outside
						$process .=  '<button class="btn btn-xs '.$btn_color.'" style="margin-right: 5px;" '.$pro_func.'><i class="fa fa-home"></i>'." ". $product_process['process_name'] . '</button>';
					}else{
						$process .=  '<button class="btn btn-xs '.$btn_color.'" style="margin-right: 5px;" '.$pro_func.'><i class="fa fa-truck"></i>'." ". $product_process['process_name'] . '</button>';
					}						
				}
			}
		}
		$child_is_requested = check_child_is_requested($dbcon,$row['rp_id']);
		$is_process_started = check_jobcard_process($dbcon,$row['rp_id']);
		// $is_process_started = 0;
		if($is_process_started == '0' && $child_is_requested == '0'){
			$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_jobcard('.$row['rp_id'].')"><i class="fa fa-trash-o"></i></button>'; 	
		}
		else{
			$delete='';
		}
		$btn_priority ='<a class="btn btn-xs btn-lock" data-original-title="Change priority" data-toggle="tooltip" onclick="open_priority_alert('.$row['rp_id'].');"><i class="fa fa-retweet"></i> Change Priority</a>';
		
		$tracking='<a class="btn btn-xs btn-default" data-original-title="Jobcard Tracking" data-toggle="tooltip" data-placement="top" style="background-color: purple; color: white; border: 1px solid purple;" href="'.ROOT.PRODUCTION_ROOT.'jobcard_tracking_report/'.$row['rp_id'].'" target="_blank"><i class="fa fa-history"></i> Tracking</a>';

		$btn_document = '<button type="button" id="btn_bom_doc" onclick="view_documents('.$row['bom_id'].','.$row['bom_version_id'].');" class="btn btn-info btn-xs" >View Documents</button>';

			$qry1 = "select IFNULL(start_qty,0) as stqty from tbl_allocate_process where  p_status!=2 and p_ref_id = ".$row['rp_id']." and process_priority in(0,1) and previous_process_id = 0";
		$result1=$dbcon->query($qry1);
		$res1=brp_mysqli_fetch_assoc($result1);

		if($res1['stqty']<=0 || $res1['stqty']==""){
			$btn_edit_process = '<button id="btn_process_main" type="button" onclick="process_edit('.$row['rp_id'].','.$row['rp_pid'].');" class="btn btn-success btn-xs" >Edit Process</button>';
		}else{
			$btn_edit_process = '';
		}
		
		
		$row_data[] = $delete.'  '.$add_po_btn." ".$edit. " ". $process . " ".$tracking . " ".$btn_document . " " . $btn_priority.' '.$btn_edit_process;

		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(brp_strtolower($POST['mode']) == "add") {
	$approve_no=load_common_no($dbcon,18);
	update_common_no($dbcon,18);
	$info['approve_no']					= $approve_no;
	$info['approve_date']				= date("Y-m-d");
	$info['rp_id']						= $POST['work_order_id'];
	$info['approve_qty']				= $POST['approve_qty'];
	$info['approve_unit']				= $POST['unit_id'];
	$info['delivery_date']				= date("Y-m-d H:i:s");
	$info['quotation_requirement']		= $POST['quotation_requirement'];
	$info['cdate']						= date("Y-m-d H:i:s");
	$info['user_id']					= $_SESSION['user_id'];
	$info['company_id']					= $_SESSION['company_id'];

	$inserpoid=add_record('approve_indent', $info, $dbcon);

	if($POST['max_approve_qty']==$POST['approve_qty']){
		$inftrn['indent_status'] = 3;
		$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$POST['work_order_id'] , $dbcon);
	}

	if($inserpoid){
		$arr['msg']="1";
	}else{
		$arr['msg']="0";
	}
	echo json_encode($arr);	

}

		/*
	START :: Code by Sanat :: 27-08-2021
	comment :: change work order process inhouse to outside 
*/

	else if(brp_strtolower($POST['mode']) == "delete_jobcard"){
		$rp_id = $POST['rp_id'];
		
		$qry = "SELECT * FROM tbl_request_product where rp_id = " . $rp_id;
		$row = brp_mysqli_fetch_assoc($dbcon->query($qry));

		$resp = '0';
		if($row['main_request'] == '1'){
			$info['status'] = 2;
			
			$updatetrnid=update_record('tbl_request_product', $info,"rp_id=".$rp_id, $dbcon);
			$updatetrnid1=update_record('tbl_request_product', $info,"perent_id=".$rp_id, $dbcon);

			if($updatetrnid){
					$resp = '1';
			}
		}
		echo $resp;
	}
	else if(strtolower($POST['mode']) == "change_jobcard_priority") {
		$rp_id = $POST['rp_id'];
		$priority = $POST['priority'];

		$info['priority_status'] = $priority;
		$updateid=update_record('tbl_request_product', $info,"rp_id=".$rp_id, $dbcon);	

		change_child_product_priority($dbcon,$rp_id,$priority);


		echo '1';
	}
	else if(brp_strtolower($POST['mode']) == "change_process_type"){
		$rp_id = $POST['rp_id'];
		$product_id = $POST['product_id']; 
		$process_id = $POST['process_id'];
		$process_type = $POST['process_type']; 

	/*	if($process_type == '2'){
			$chk_process_type = '1';
		}else{
			$chk_process_type = '0';
		}*/

		$qry = "SELECT * FROM  tbl_allocate_process WHERE p_ref_id = " . $rp_id . " and p_product_id = " .$product_id . " and process_id = ". $process_id ." AND p_status NOT IN (2,3) AND pr_process_type = " . $process_type;
		$res = $dbcon->query($qry);
		$cnt=brp_mysqli_num_rows($res);
		$result= brp_mysqli_fetch_assoc($res);
			if($cnt == 0){   //  1) process tbl_allocate_process table entry pending
				$qry1 = "SELECT * FROM  tbl_wororder_product_process WHERE 	rp_id = " . $rp_id . " and product_id = " .$product_id . " and process_id = ". $process_id ." AND process_type = " . $process_type;
				$qry_res =	$dbcon->query($qry1);
				$cnt1=brp_mysqli_num_rows($qry_res);
				$res1 = brp_mysqli_fetch_assoc($qry_res);
				if($cnt1){
					$wororder_product_process = [];
					foreach($res1 as $key => $value){
						$wororder_product_process[$key] = $value;
					}

					unset($wororder_product_process['pr_process_id']);


					if($process_type == '1'){
						$wororder_product_process['process_type'] = 2;
					}else{
						$wororder_product_process['process_type'] = 1;
					}

					$del_id =	delete_record('tbl_wororder_product_process' ,'pr_process_id =' .$res1['pr_process_id'] ,$dbcon);

					$insert_id=add_record('tbl_wororder_product_process', $wororder_product_process, $dbcon);

					$arr['msg'] = '1';
				}else{
					$arr['msg'] = '0';
				}

			}else if($result['p_status'] == '0'){ //2) process not start and tbl_allocate_process table entry done.

				$qry1 = "SELECT * FROM  tbl_wororder_product_process WHERE 	rp_id = " . $rp_id . " and product_id = " .$product_id . " and process_id = ". $process_id ." AND process_type = " . $process_type;

			// echo "</br>".$qry1;

				$qry_res =	$dbcon->query($qry1);
				$cnt1=brp_mysqli_num_rows($qry_res);
				$res1 = brp_mysqli_fetch_assoc($qry_res);

			// $res1 = brp_mysqli_fetch_assoc($dbcon->query($qry1));
				if($cnt1){
					$wororder_product_process = [];
					foreach($res1 as $key => $value){
						$wororder_product_process[$key] = $value;
					}

					unset($wororder_product_process['pr_process_id']);


					if($process_type == '1'){
						$wororder_product_process['process_type'] = 2;
					}else{
						$wororder_product_process['process_type'] = 1;
					}

				

					$del_id =	delete_record('tbl_wororder_product_process' ,'pr_process_id =' .$res1['pr_process_id'] ,$dbcon);

					$insert_id=add_record('tbl_wororder_product_process', $wororder_product_process, $dbcon);

					$arr_allocate_process = [];
					foreach($result as $key => $value){
						$arr_allocate_process[$key] = $value;
					}

					$p_id = $result['p_id'];

					$arr_allocate_process['perent_id'] = $p_id;
					unset($arr_allocate_process['p_id']);
					unset($arr_allocate_process['cdate']);
					$arr_allocate_process['cdate'] = date("Y-m-d H:i:s");



					if($arr_allocate_process['pr_process_type'] == '1'){
						$arr_allocate_process['pr_process_type'] = 2;
					}else{
						$arr_allocate_process['pr_process_type'] = 1;
					}
			// $info1[] =
					$update_info = array();
					$start_end_qty = 0;
					if($result['start_qty']=="" || $result['start_qty']=="0"){
							$update_info['p_status'] = 2;
					}else{
						$p_qty = $result['p_qty'];
						$pen_qty = $result['pen_qty'];
						$start_qty = $result['start_qty'];

						$start_end_qty = 1;
						$update_info['p_qty'] = $p_qty - $pen_qty;
						$update_info['start_qty'] = $p_qty - $pen_qty;
						$update_info['pen_qty'] = 0;

						$arr_allocate_process['p_qty'] = $pen_qty;
						$arr_allocate_process['pen_qty'] = $pen_qty;
						$arr_allocate_process['start_qty'] = 0;
						$arr_allocate_process['p_status'] = 0;
						$arr_allocate_process['task_status'] = 0;
						
						$update_info['p_status'] = 3;

					}

					$total_stock = 0;
					if($result['previous_process_id']=="0"){
						$total_stock=check_row_material_availability($dbcon,$p_id,0);
					}else{
							//$process_start_pending_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
						$total_stock=production_process_reseve_stock($dbcon,$result['process_unit'],$result['branch_id'],$p_id,$result['p_product_id'],$result1['process_id'],$process_reserve_id,$process_stock_id,0);
							
					}

					$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id , $dbcon);

					$new_p_id=add_record('tbl_allocate_process', $arr_allocate_process, $dbcon);

					if($start_end_qty == 0){
							if($result['previous_process_id'] == '0'){
							update_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id,$update_info['p_status']);
						}else{
							update_process_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id,$update_info['p_status']);
						}
					}else{
							if($result['previous_process_id'] == '0'){
							update_reserve_stock_partially($dbcon,$rp_id,$p_id,$new_p_id,$total_stock,$inhouse_stock,$outside_stock);
						}else{
							update_process_reserve_stock_partially($p_id,$dbcon,$new_p_id,$total_stock,$inhouse_stock,$outside_stock);
						}
					}
					

					

					$arr['msg'] = '1';
				}else{
					$arr['msg'] = '0';
				}
			}	
			//	3) process start but process end pending

			else if($result['p_status'] == '1' && $result['task_status'] == '1'){

				$qry1 = "SELECT * FROM  tbl_wororder_product_process WHERE 	rp_id = " . $rp_id . " and product_id = " .$product_id . " and process_id = ". $process_id ." AND process_type = " . $process_type;

			// echo "</br>".$qry1;
				$qry_res =	$dbcon->query($qry1);
				$cnt1=brp_mysqli_num_rows($qry_res);
				$res1 = brp_mysqli_fetch_assoc($qry_res);

				if($cnt1){
					$wororder_product_process = [];
					foreach($res1 as $key => $value){
						$wororder_product_process[$key] = $value;
					}

					unset($wororder_product_process['pr_process_id']);


					if($process_type == '1'){
						$wororder_product_process['process_type'] = 2;
					}else{
						$wororder_product_process['process_type'] = 1;
					}

					$del_id =	delete_record('tbl_wororder_product_process' ,'pr_process_id =' .$res1['pr_process_id'] ,$dbcon);

					$insert_id=add_record('tbl_wororder_product_process', $wororder_product_process, $dbcon);
				}
				
				$p_id = $result['p_id'];

				$start_qty = jobwork_short_close($p_id,$dbcon);

				$arr_allocate_process = [];
				foreach($result as $key => $value){
					$arr_allocate_process[$key] = $value;
				}

				unset($arr_allocate_process['p_id']);
				if($arr_allocate_process['pr_process_type'] == '1'){
					$arr_allocate_process['pr_process_type'] = 2;
				}else{
					$arr_allocate_process['pr_process_type'] = 1;
				}
			// $info1[] =

				 if($result['start_qty']=="" || $result['start_qty']=="0"){
							$update_info['p_status'] = 2;
					}else{
						$p_qty = $result['p_qty'];
						$pen_qty = $result['pen_qty'];
						$start_qty = $result['start_qty'];

						$update_info['p_qty'] = $p_qty - $pen_qty;
						$update_info['start_qty'] = $p_qty - $pen_qty;
						$update_info['pen_qty'] = 0;

						$arr_allocate_process['p_qty'] = $pen_qty;
						$arr_allocate_process['pen_qty'] = $pen_qty;
						$arr_allocate_process['start_qty'] = 0;
						$arr_allocate_process['p_status'] = 0;
						$arr_allocate_process['task_status'] = 0;
						
						$update_info['p_status'] = 3;

						if($update_info['p_qty'] == 0){
								$update_info['p_status'] = 2;							
						}

					}


				$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$result['p_id'] , $dbcon);

				$p_id = $result['p_id'];

				$new_p_id=add_record('tbl_allocate_process', $arr_allocate_process, $dbcon);
				if($new_p_id > 0){

					$qry6 = "SELECT * FROM  tbl_allocate_process_trn WHERE pt_alloc_id = " . $p_id;

					// echo $qry;

					$res6 = $dbcon->query($qry6);
					
					$cnt6=brp_mysqli_num_rows($res6);
					
					$result6= brp_mysqli_fetch_assoc($res6);

					if($cnt6 > 0){
						$arr_allocate_process_trn = [];

						foreach($result6 as $key => $value){
							$arr_allocate_process_trn[$key] = $value;
						}

						$update_trn['p_status'] = 2;
						$updateid=update_record('tbl_allocate_process_trn', $update_trn,"pt_id=".$result6['pt_id'] , $dbcon);	

						unset($arr_allocate_process_trn['pt_id']);

						$new_trn_id=add_record('tbl_allocate_process_trn', $arr_allocate_process_trn, $dbcon);
					}

					if($result['previous_process_id'] == '0'){
						update_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id,$update_trn['p_status']);
					}else{
						update_process_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id,$update_trn['p_status']);
					}

								

					$qry3 = "SELECT * FROM  tbl_process_reserve_stock WHERE p_id = " . $p_id;

					// echo $qry;

					$res3 = $dbcon->query($qry3);
					
					$cnt3=brp_mysqli_num_rows($res3);
					
					if($cnt3 > 0){
						while($result3= brp_mysqli_fetch_assoc($res3)){
							$arr_process_reserve_stock = [];

						foreach($result3 as $key => $value){
							$arr_process_reserve_stock[$key] = $value;
						}	

						$update_stock['stock_status'] = 2;
						$updateid=update_record('tbl_process_reserve_stock', $update_stock,"process_reserve_id=".$result3['process_reserve_id'] , $dbcon);	

						unset($arr_process_reserve_stock['process_reserve_id']);

						$arr_process_reserve_stock['p_id'] = $new_p_id;

						$new_prs_id=add_record('tbl_process_reserve_stock', $arr_process_reserve_stock, $dbcon);
						}
					}
				}
				$arr['msg'] = '1';
				

			}else{
				$arr['msg'] = '0';
			}	

			echo json_encode($arr);			
		}

				else if(brp_strtolower($POST['mode']) == "change_process_vendor"){
					$rp_id = $POST['rp_id'];
					$product_id = $POST['product_id']; 
					$process_id = $POST['process_id'];
					$process_type = $POST['process_type']; 


					/*if($process_type == '2'){
						$chk_process_type = '1';
					}else{
						$chk_process_type = '0';
					}*/

					$qry = "SELECT * FROM  tbl_allocate_process WHERE p_ref_id = " . $rp_id . " and p_product_id = " .$product_id . " and process_id = ". $process_id ." AND p_status != 2 AND pr_process_type = " . $process_type;

					$res = $dbcon->query($qry);

					$cnt=brp_mysqli_num_rows($res);

					$result= brp_mysqli_fetch_assoc($res);


					if($cnt > 0){
						$p_id = $result['p_id'];

						$start_qty = jobwork_short_close($p_id,$dbcon);
						
						if($start_qty){
							$allocate_start_qty = $result['start_qty'];
							$allocate_start_qty = $allocate_start_qty - $start_qty;

							$update_info['start_qty'] = $allocate_start_qty;
							$update_info['p_status'] = 0;

							$updateid=update_record('tbl_allocate_process', $update_info, "p_id=".$p_id, $dbcon);	


							$qry1 = "SELECT * FROM  tbl_allocate_process_trn WHERE pt_alloc_id = " . $p_id;;

							$res1 = $dbcon->query($qry1);

							$cnt1=brp_mysqli_num_rows($res1);

							$result1= brp_mysqli_fetch_assoc($res1);

							if($cnt1 > 0){

								$trn_qty = $result1['pt_qty'];
								$trn_qty = $trn_qty - $start_qty;

								$update_trn['start_qty'] = $allocate_start_qty;
								$update_trn['p_status'] = 0;

								$update_id=update_record('tbl_allocate_process_trn', $update_trn, "pt_id=".$result1['pt_id'], $dbcon);	
							}							

						}
						$arr['msg'] = '1';
					}else{
						$arr['msg'] = '0';
					}			

					echo json_encode($arr);
				}else if(brp_strtolower($POST['mode']) == "check_work_order_grn"){

					$rp_id = $POST['rp_id'];
					$product_id = $POST['product_id']; 
					$process_id = $POST['process_id'];
					$process_type = $POST['process_type']; 


					/*if($process_type == '2'){
						$chk_process_type = '1';
					}else{
						$chk_process_type = '0';
					}*/

					$qry = "SELECT p_id FROM  tbl_allocate_process WHERE  p_ref_id = " . $rp_id . " and p_product_id = " .$product_id . " and process_id = ". $process_id ." AND p_status != 2 AND pr_process_type = " . $process_type;
				
					$res = $dbcon->query($qry);

					$cnt=brp_mysqli_num_rows($res);

					$result= brp_mysqli_fetch_assoc($res);

					$arr['job_work'] = '0';

					if($cnt > 0){
								$qry1 = "SELECT job_work_sub_trn_id	FROM tbl_job_work_sub_trn WHERE p_id = " . $result['p_id'];
									
								$res1 = $dbcon->query($qry1);
								$cnt1=brp_mysqli_num_rows($res1);

								$result1= brp_mysqli_fetch_assoc($res1);
						
								if($cnt1 > 0){
									$qry2 = "SELECT grn_trn_sub_id FROM tbl_grn_sub_trn WHERE job_work_sub_trn_id = " . $result1['job_work_sub_trn_id'];
									
								$res2 = $dbcon->query($qry2);
								$cnt2=brp_mysqli_num_rows($res2);

								$result2= brp_mysqli_fetch_assoc($res2);

									if($cnt2 > 0){
										$arr['job_work'] = '0';
									}else{
										$arr['job_work'] = '1';
									}
								}else{
									$arr['job_work'] = '0';
								}
					}else{
							$arr['job_work'] = '0';
					}

					echo json_encode($arr);
	}else if(brp_strtolower($POST['mode']) == "get_pending_qty"){
			$rp_id = $POST['rp_id'];
					$product_id = $POST['product_id']; 
					$process_id = $POST['process_id'];
					$process_type = $POST['process_type']; 


			$qry = "SELECT mst.*,pmst.product_name,promst.process_name FROM  tbl_allocate_process as mst
			left join product_mst as pmst on pmst.product_id=mst.p_product_id
			left join process_mst as promst on promst.process_id=mst.process_id
			 WHERE  p_ref_id = " . $rp_id . " and p_product_id = " .$product_id . " and mst.process_id = ". $process_id ." AND p_status NOT IN (2,3) AND pr_process_type = " . $process_type;
			// echo $qry;
			$res = $dbcon->query($qry);

		$cnt=brp_mysqli_num_rows($res);

		$result= brp_mysqli_fetch_assoc($res);

		if($cnt > 0){
			$arr['msg'] = '1';
			$arr['process'] = $result;
		}else{
			$arr['msg'] = '0';
		}

		echo json_encode($arr);
	}else if(brp_strtolower($POST['mode']) == "get_pending_qty_by_p_id"){
			$p_id = $POST['p_id'];
				

			$qry = "SELECT mst.*,pmst.product_name,promst.process_name FROM  tbl_allocate_process as mst
			left join product_mst as pmst on pmst.product_id=mst.p_product_id
			left join process_mst as promst on promst.process_id=mst.process_id
			 WHERE p_id = " . $p_id;
			// echo $qry;
			$res = $dbcon->query($qry);

		$cnt=brp_mysqli_num_rows($res);

		$result= brp_mysqli_fetch_assoc($res);

		if($cnt > 0){
			$arr['msg'] = '1';
			$arr['process'] = $result;
		}else{
			$arr['msg'] = '0';
		}

		echo json_encode($arr);
	}else if(brp_strtolower($POST['mode']) == "process_transfer_qty"){
		$rp_id = $POST['rp_id'];
		$product_id = $POST['product_id']; 
		$process_id = $POST['process_id'];
		$process_type = $POST['process_type']; 
		$pen_qty = $POST['pen_qty'];
		$inhouse_qty = $POST['inhouse_qty']; 
		$outside_qty = $POST['outside_qty'];
		$total_stock = $POST['total_stock'];
		$inhouse_stock = $POST['inhouse_stock']; 
		$outside_stock = $POST['outside_stock'];
					
			$qry = "SELECT * FROM  tbl_allocate_process WHERE p_ref_id = " . $rp_id . " and p_product_id = " .$product_id . " and process_id = ". $process_id ." AND p_status NOT IN (2,3) AND pr_process_type = " . $process_type;
			// echo $qry;
			$res = $dbcon->query($qry);

		$cnt=brp_mysqli_num_rows($res);

		$result= brp_mysqli_fetch_assoc($res);

		if($cnt > 0){

						if(($result['start_qty'] != '' || $result['start_qty'] != '0') && $result['p_status'] =='1' && $result['task_status'] == '1'){

								$arr_allocate_process = [];
								foreach($result as $key => $value){
									$arr_allocate_process[$key] = $value;
								}

								$p_id = $result['p_id'];
								unset($arr_allocate_process['p_id']);

							 	// 
								if($process_type  == '1'){  // inside to outside qty transfer
									$arr_allocate_process['pr_process_type'] = 2;
									$allocate_start_qty = $result['start_qty'];
									$allocate_p_qty = $result['p_qty'];
									$allocate_pen_qty = $result['pen_qty'];

									if($outside_qty > ($allocate_pen_qty - $allocate_start_qty)){
										$t_start_qty = ($allocate_pen_qty - $outside_qty);
										
									}else{
										$t_start_qty = $allocate_start_qty;							
									}	
									$t_p_qty = ($allocate_p_qty - $outside_qty);
									$t_pen_qty = ($allocate_pen_qty - $outside_qty);

								$arr_allocate_process['p_qty'] = $outside_qty;
								$arr_allocate_process['pen_qty'] = $outside_qty;
								$arr_allocate_process['start_qty'] = 0;
								$arr_allocate_process['p_status'] = 0;


							}else{  // outside to inside qty transfer
								$arr_allocate_process['pr_process_type'] = 1;
									$allocate_start_qty = $result['start_qty'];
									$allocate_p_qty = $result['p_qty'];
									$allocate_pen_qty = $result['pen_qty'];


									if($inhouse_qty > ($allocate_pen_qty - $allocate_start_qty)){
										$t_start_qty = ($allocate_pen_qty - $inhouse_qty);
										}else{
											$t_start_qty = $allocate_start_qty;
										}

										$t_p_qty = ($allocate_p_qty - $inhouse_qty);
										$t_pen_qty = ($allocate_pen_qty - $inhouse_qty);

										$arr_allocate_process['p_qty'] = $inhouse_qty;
										$arr_allocate_process['pen_qty'] = $inhouse_qty;
										$arr_allocate_process['start_qty'] = 0;
										$arr_allocate_process['p_status'] = 0;
								}

								$update_info['p_qty'] = $t_p_qty;
								$update_info['pen_qty'] = $t_pen_qty;
								$update_info['start_qty'] = $t_start_qty;
								// echo "<pre>";
								// print_r($update_info);

								// echo "<pre>";
								// print_r($arr_allocate_process);die;

								jobwork_change_qty($p_id,$t_start_qty,$dbcon);
								$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id, $dbcon);
							$new_p_id=add_record('tbl_allocate_process', $arr_allocate_process, $dbcon);

							if($result['previous_process_id'] == '0'){
								update_reserve_stock_partially($dbcon,$rp_id,$p_id,$new_p_id,$total_stock,$inhouse_stock,$outside_stock);
							}else{
								update_process_reserve_stock_partially($p_id,$dbcon,$new_p_id,$total_stock,$inhouse_stock,$outside_stock);
							}
							
								
						
							if($new_p_id > 0){
								$arr['msg'] = '1';
							}else{
								$arr['msg'] = '0';
							}

					}else{
							$arr_allocate_process = [];
							foreach($result as $key => $value){
								$arr_allocate_process[$key] = $value;
							}

							$p_id = $result['p_id'];

							// $arr_allocate_process['perent_id'] = $p_id;

							unset($arr_allocate_process['p_id']);

							if($arr_allocate_process['pr_process_type'] == '1'){
								$arr_allocate_process['pr_process_type'] = 2;
								$arr_allocate_process['p_qty'] = $outside_qty;
								$arr_allocate_process['pen_qty'] = $outside_qty;
								$arr_allocate_process['start_qty'] = 0;
								$arr_allocate_process['p_status'] = 0;

								$update_info['p_qty'] = $inhouse_qty;
								$update_info['pen_qty'] = $inhouse_qty;
								$update_info['start_qty'] = 0;
							}else{
								$arr_allocate_process['pr_process_type'] = 1;
								$arr_allocate_process['p_qty'] = $inhouse_qty;
								$arr_allocate_process['pen_qty'] = $inhouse_qty;
								$arr_allocate_process['start_qty'] = 0;
								$arr_allocate_process['p_status'] = 0;

								$update_info['p_qty'] = $outside_qty;
								$update_info['pen_qty'] = $outside_qty;
								$update_info['start_qty'] = 0;
							}
					// $info1[] =	

							$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id, $dbcon);
							$new_p_id=add_record('tbl_allocate_process', $arr_allocate_process, $dbcon);

							if($result['previous_process_id'] == '0'){
								update_reserve_stock_partially($dbcon,$rp_id,$p_id,$new_p_id,$total_stock,$inhouse_stock,$outside_stock);
							}else{
								update_process_reserve_stock_partially($p_id,$dbcon,$new_p_id,$total_stock,$inhouse_stock,$outside_stock);
							}
							
							if($new_p_id > 0){
								$arr['msg'] = '1';
							}else{
								$arr['msg'] = '0';
							}
							
						}
							
		}else{
			$arr['msg'] = '0';
		}

		echo json_encode($arr);
	}else if(brp_strtolower($POST['mode']) == "process_transfer_qty_by_p_id"){

		$p_id = $POST['p_id'];
		$pen_qty = $POST['pen_qty'];
		$inhouse_qty = $POST['inhouse_qty']; 
		$outside_qty = $POST['outside_qty'];
			$total_stock = $POST['total_stock'];
		$inhouse_stock = $POST['inhouse_stock']; 
		$outside_stock = $POST['outside_stock'];
					
					
			$qry = "SELECT * FROM  tbl_allocate_process WHERE  p_id = " . $p_id;
			// echo $qry;
			$res = $dbcon->query($qry);

		$cnt=brp_mysqli_num_rows($res);

		$result= brp_mysqli_fetch_assoc($res);

		$rp_id = $result['p_ref_id'];

		if($cnt > 0){

			$process_type = $result['pr_process_type']; 

				if($process_type == '1'){
					$check_process = 2;
				}else{
					$check_process = 1;
				}

			$qry1 = "SELECT * FROM  tbl_allocate_process WHERE p_ref_id = " . $result['p_ref_id'] . " and p_product_id = " .$result['p_product_id'] . " and process_id = ". $result['process_id'] ." AND p_status NOT IN (2,3) AND pr_process_type = " . $check_process;

		$res1 = $dbcon->query($qry1);
		$cnt1=brp_mysqli_num_rows($res1);
		$result1= brp_mysqli_fetch_assoc($res1);


		if($cnt1 > 0 ){
			if(($result['start_qty'] != '' || $result['start_qty'] != '0') && $result['p_status'] =='1' && $result['task_status'] == '1'){

								$arr_allocate_process = [];
								foreach($result as $key => $value){
									$arr_allocate_process[$key] = $value;
								}

								$p_id = $result['p_id'];
								unset($arr_allocate_process['p_id']);

							 	// 
								if($process_type  == '1'){  // inside to outside qty transfer
									
									$allocate_start_qty = $result['start_qty'];
									$allocate_p_qty = $result['p_qty'];
									$allocate_pen_qty = $result['pen_qty'];


									$tr_start_qty = $result['start_qty'];
									$tr_p_qty = $result['p_qty'];
									$tr_pen_qty = $result['pen_qty'];

									if($outside_qty > ($allocate_pen_qty - $allocate_start_qty)){
										$t_start_qty = ($allocate_pen_qty - $outside_qty);
										
									}else{
										$t_start_qty = $allocate_start_qty;							
									}	
									$t_p_qty = ($allocate_p_qty - $outside_qty);
									$t_pen_qty = ($allocate_pen_qty - $outside_qty);

								$update_qty['p_qty'] = $tr_p_qty + $outside_qty;
								$update_qty['pen_qty'] = $tr_pen_qty + $outside_qty;
								

							}else{  // outside to inside qty transfer
								
									$allocate_start_qty = $result['start_qty'];
									$allocate_p_qty = $result['p_qty'];
									$allocate_pen_qty = $result['pen_qty'];

									$tr_start_qty = $result['start_qty'];
									$tr_p_qty = $result['p_qty'];
									$tr_pen_qty = $result['pen_qty'];


									if($inhouse_qty > ($allocate_pen_qty - $allocate_start_qty)){
										$t_start_qty = ($allocate_pen_qty - $inhouse_qty);
										}else{
											$t_start_qty = $allocate_start_qty;
										}

										$t_p_qty = ($allocate_p_qty - $inhouse_qty);
										$t_pen_qty = ($allocate_pen_qty - $inhouse_qty);

										$update_qty['p_qty'] = 	$tr_p_qty + $inhouse_qty;
										$update_qty['pen_qty'] = $tr_pen_qty + $inhouse_qty;
										
								}

								$update_info['p_qty'] = $t_p_qty;
								$update_info['pen_qty'] = $t_pen_qty;
								$update_info['start_qty'] = $t_start_qty;

								jobwork_change_qty($p_id,$t_start_qty,$dbcon);
								
								$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id, $dbcon);
								$updateid=update_record('tbl_allocate_process', $update_qty,"p_id=".$result1['p_id'], $dbcon);
						
						if($result['previous_process_id'] == '0'){
								update_reserve_stock_partially($dbcon,$rp_id,$p_id,$result1['p_id'],$total_stock,$inhouse_stock,$outside_stock);
							}else{
								update_process_reserve_stock_partially($p_id,$dbcon,$result1['p_id'],$total_stock,$inhouse_stock,$outside_stock);
							}
							
							if($updateid > 0){
								$arr['msg'] = '1';
							}else{
								$arr['msg'] = '0';
							}

					}else{
							$p_qty = $result1['p_qty'];
							$pen_qty	= $result1['pen_qty'];					
							$start_qty	= $result1['start_qty'];


							$t_p_qty = $result['p_qty'];
							$t_pen_qty	= $result['pen_qty'];					
							$t_start_qty	= $result['start_qty'];


							if($process_type == '1'){

								$update_qty['p_qty'] = $p_qty + $outside_qty;
								$update_qty['pen_qty'] = $pen_qty + $outside_qty;


								$update_info['p_qty'] = $inhouse_qty;
								$update_info['pen_qty'] = $inhouse_qty;
								$update_info['start_qty'] = 0;
							}else{

								$update_qty['p_qty'] = $p_qty + $inhouse_qty;
								$update_qty['pen_qty'] = $pen_qty + $inhouse_qty;

								$update_info['p_qty'] = $outside_qty;
								$update_info['pen_qty'] = $outside_qty;
								$update_info['start_qty'] = 0;
							}

							$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id, $dbcon);

							$updateid=update_record('tbl_allocate_process', $update_qty,"p_id=".$result1['p_id'], $dbcon);

							if($result['previous_process_id'] == '0'){
								update_reserve_stock_partially($dbcon,$rp_id,$p_id,$result1['p_id'],$total_stock,$inhouse_stock,$outside_stock);
							}else{
								update_process_reserve_stock_partially($p_id,$dbcon,$result1['p_id'],$total_stock,$inhouse_stock,$outside_stock);
							}
							

							if($updateid){
								$arr['msg'] = '1';
							}else{
								$arr['msg'] = '0';
							}
						}

		}else{
			if(($result['start_qty'] != '' || $result['start_qty'] != '0') && $result['p_status'] =='1' && $result['task_status'] == '1'){

								$arr_allocate_process = [];
								foreach($result as $key => $value){
									$arr_allocate_process[$key] = $value;
								}

								$p_id = $result['p_id'];
								unset($arr_allocate_process['p_id']);

							 	// 
								if($process_type  == '1'){  // inside to outside qty transfer
									$arr_allocate_process['pr_process_type'] = 2;
									$allocate_start_qty = $result['start_qty'];
									$allocate_p_qty = $result['p_qty'];
									$allocate_pen_qty = $result['pen_qty'];

									if($outside_qty > ($allocate_pen_qty - $allocate_start_qty)){
										$t_start_qty = ($allocate_pen_qty - $outside_qty);
										
									}else{
										$t_start_qty = $allocate_start_qty;							
									}	
									$t_p_qty = ($allocate_p_qty - $outside_qty);
									$t_pen_qty = ($allocate_pen_qty - $outside_qty);

								$arr_allocate_process['p_qty'] = $outside_qty;
								$arr_allocate_process['pen_qty'] = $outside_qty;
								$arr_allocate_process['start_qty'] = 0;
								$arr_allocate_process['p_status'] = 0;


							}else{  // outside to inside qty transfer
								  $arr_allocate_process['pr_process_type'] = 1;
									$allocate_start_qty = $result['start_qty'];
									$allocate_p_qty = $result['p_qty'];
									$allocate_pen_qty = $result['pen_qty'];


									if($inhouse_qty > ($allocate_pen_qty - $allocate_start_qty)){
										$t_start_qty = ($allocate_pen_qty - $inhouse_qty);
										}else{
											$t_start_qty = $allocate_start_qty;
										}

										$t_p_qty = ($allocate_p_qty - $inhouse_qty);
										$t_pen_qty = ($allocate_pen_qty - $inhouse_qty);

										$arr_allocate_process['p_qty'] = $inhouse_qty;
										$arr_allocate_process['pen_qty'] = $inhouse_qty;
										$arr_allocate_process['start_qty'] = 0;
										$arr_allocate_process['p_status'] = 0;
								}

								$update_info['p_qty'] = $t_p_qty;
								$update_info['pen_qty'] = $t_pen_qty;
								$update_info['start_qty'] = $t_start_qty;
								// echo "<pre>";
								// print_r($update_info);

								// echo "<pre>";
								// print_r($arr_allocate_process);die;

								jobwork_change_qty($p_id,$t_start_qty,$dbcon);

								$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id, $dbcon);
							$new_p_id=add_record('tbl_allocate_process', $arr_allocate_process, $dbcon);

							if($result['previous_process_id'] == '0'){
								update_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id);
							}else{
								update_process_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id);
							}

								
							if($new_p_id > 0){
								$arr['msg'] = '1';
							}else{
								$arr['msg'] = '0';
							}

					}else{
							$arr_allocate_process = [];
							foreach($result as $key => $value){
								$arr_allocate_process[$key] = $value;
							}

							unset($arr_allocate_process['p_id']);

							if($arr_allocate_process['pr_process_type'] == '1'){
								$arr_allocate_process['pr_process_type'] = 2;
								$arr_allocate_process['p_qty'] = $outside_qty;
								$arr_allocate_process['pen_qty'] = $outside_qty;
								$arr_allocate_process['start_qty'] = 0;
								$arr_allocate_process['p_status'] = 0;

								$update_info['p_qty'] = $inhouse_qty;
								$update_info['pen_qty'] = $inhouse_qty;
								$update_info['start_qty'] = 0;
							}else{
								$arr_allocate_process['pr_process_type'] = 1;
								$arr_allocate_process['p_qty'] = $inhouse_qty;
								$arr_allocate_process['pen_qty'] = $inhouse_qty;
								$arr_allocate_process['start_qty'] = 0;
								$arr_allocate_process['p_status'] = 0;

								$update_info['p_qty'] = $outside_qty;
								$update_info['pen_qty'] = $outside_qty;
								$update_info['start_qty'] = 0;
							}
					
							$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id, $dbcon);
							$new_p_id=add_record('tbl_allocate_process', $arr_allocate_process, $dbcon);

								if($result['previous_process_id'] == '0'){
								update_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id);
							}else{
								update_process_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id);
							}

							if($new_p_id > 0){
								$arr['msg'] = '1';	
							}else{
								$arr['msg'] = '0';
							}
						}
						}	
		}else{
			$arr['msg'] = '0';
		}

		echo json_encode($arr);
	}else if(brp_strtolower($POST['mode']) == "check_multiple_allocate_process"){
		$rp_id = $POST['rp_id'];
		$product_id = $POST['product_id']; 
		$process_id = $POST['process_id'];
		$process_type = $POST['process_type']; 

		if($process_type == '1'){
			$check_process = 2;
		}else{
			$check_process = 1;
		}
			
		$qry = "SELECT * FROM  tbl_allocate_process WHERE p_ref_id = " . $rp_id . " and p_product_id = " .$product_id . " and process_id = ". $process_id ." AND p_status NOT IN (2,3) ";
			// echo $qry;
		$res = $dbcon->query($qry);
		$cnt=brp_mysqli_num_rows($res);
		$result= brp_mysqli_fetch_assoc($res);

		if($cnt > 1){
			$arr['msg'] = '1';

				$qry1 = "SELECT mst.*,pmst.product_name,promst.process_name FROM  tbl_allocate_process as mst
			left join product_mst as pmst on pmst.product_id=mst.p_product_id
			left join process_mst as promst on promst.process_id=mst.process_id
			 WHERE  p_ref_id = " . $rp_id . " and p_product_id = " .$product_id . " and mst.process_id = ". $process_id ." AND p_status NOT IN (2,3)";
			// echo $qry1;
			$res1 = $dbcon->query($qry1);

			
			$html = '<div class="cards-list">';
  			while($result1=brp_mysqli_fetch_assoc($res1)){
  						$html .= '<a href="javascript:;" onClick="multi_transfer_process('. $result1['p_id'] .','.$result1['pr_process_type'].')"><div class="card 1">';
  						if($result1['pr_process_type'] == '1'){
  							$html .= '<div class="card_image icon1 success"> </div>';
  							 $html .= '<div class="card_title title-white">
								    <p class="text-capitalize">'. $result1['process_name'] .'</p>
								     <p>Inhouse</p>
								    <p>QTY : '. $result1['pen_qty'] .'</p>
								  </div>
								</div></a>';
  						}else{
  							$html .= '<div class="card_image icon1 primary"> </div>';	
  							 $html .= '<div class="card_title title-white">
								    <p class="text-capitalize">'. $result1['process_name'] .'</p>
								     <p>Outside</p>
								    <p>QTY : '. $result1['pen_qty'] .'</p>
								  </div>
								</div></a>';
  						}
							
							
							}

		$html .='</div>';

		$arr['html'] = $html;
		}else{
			$arr['msg'] = '0';
		}

		echo json_encode($arr);

	}else if(brp_strtolower($POST['mode']) == "change_process_type_by_p_id"){

		$p_id = $POST['p_id'];

		$qry = "SELECT * FROM  tbl_allocate_process WHERE p_id = " . $p_id;

		$res = $dbcon->query($qry);
		$cnt=brp_mysqli_num_rows($res);
		$result= brp_mysqli_fetch_assoc($res);

		$rp_id = $result['p_ref_id'];

		$qry2 = "SELECT * FROM  tbl_wororder_product_process WHERE 	rp_id = " . $result['p_ref_id']  . " and product_id = " .$result['p_product_id'] . " and process_id = ". $result['process_id'] ." AND process_type = " . $result['pr_process_type'];

					// echo "</br>".$qry1;
						$qry_res =	$dbcon->query($qry2);
						$cnt11=brp_mysqli_num_rows($qry_res);
						$res11 = brp_mysqli_fetch_assoc($qry_res);

						if($cnt11){
							$wororder_product_process = [];
							foreach($res11 as $key => $value){
								$wororder_product_process[$key] = $value;
							}

							$del_pro_id = $wororder_product_process['pr_process_id'];

							unset($wororder_product_process['pr_process_id']);


							if($wororder_product_process['process_type'] == '1'){
								$wororder_product_process['process_type'] = 2;
							}else{
								$wororder_product_process['process_type'] = 1;
							}

							$del_id =	delete_record('tbl_wororder_product_process' ,'pr_process_id =' . $del_pro_id ,$dbcon);

							$insert_id=add_record('tbl_wororder_product_process', $wororder_product_process, $dbcon);

							$arr['msg'] = '1';
						}else{
							$arr['msg'] = '0';
						}

		if($cnt > 0){
			$job_work = jobwork_short_close($p_id,$dbcon);
			$process_type = $result['pr_process_type']; 

				if($process_type == '1'){
					$check_process = 2;
				}else{
					$check_process = 1;
				}

				$qry1 = "SELECT * FROM  tbl_allocate_process WHERE  p_ref_id = " . $result['p_ref_id'] . " and p_product_id = " .$result['p_product_id'] . " and process_id = ". $result['process_id'] ." AND p_status NOT IN (2,3) AND pr_process_type = " . $check_process;

				$res1 = $dbcon->query($qry1);
				$cnt1=brp_mysqli_num_rows($res1);
				$result1= brp_mysqli_fetch_assoc($res1);



			if($cnt1 > 0){


				// short close  if process start 
						
							$p_qty = $result1['p_qty'];
							$pen_qty	= $result1['pen_qty'];					
							$start_qty	= $result1['start_qty'];

							$t_p_qty = $result['p_qty'];
							$t_pen_qty	= $result['pen_qty'];					
							$t_start_qty	= $result['start_qty'];

							$update_qty['p_qty'] = $p_qty + $t_p_qty;
							$update_qty['pen_qty'] = $pen_qty + $t_pen_qty;

							$update_info['p_status'] = 2;
							$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id, $dbcon);

							$updateid=update_record('tbl_allocate_process', $update_qty,"p_id=".$result1['p_id'], $dbcon);

							if($result['previous_process_id'] == '0'){
								update_reserve_stock($dbcon,$rp_id,$p_id,$result1['p_id']);
							}else{
								update_process_reserve_stock($dbcon,$rp_id,$p_id,$result1['p_id']);
							}
							

							if($updateid){
								$arr['msg'] = '1';
							}else{
								$arr['msg'] = '0';
							}


			}else{
		 					$arr_allocate_process = [];
							foreach($result as $key => $value){
								$arr_allocate_process[$key] = $value;
							}

							$arr_allocate_process['perent_id'] = $p_id;
							unset($arr_allocate_process['p_id']);

							if($arr_allocate_process['pr_process_type'] == '1'){
								$arr_allocate_process['pr_process_type'] = 2;
							}else{
								$arr_allocate_process['pr_process_type'] = 1;
							}

							$update_info['p_status'] = 2;

							$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id, $dbcon);

							// $p_id = $result['p_id'];

							$new_p_id=add_record('tbl_allocate_process', $arr_allocate_process, $dbcon);

							if($result['previous_process_id'] == '0'){
								update_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id);
							}else{
								update_process_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id);
								
							}

							
							if($new_p_id){
									$arr['msg'] = '1';					
							}else{
									$arr['msg'] = '0';					
							}
					}
			
		}else{
			$arr['msg'] = '0';
		}

		echo json_encode($arr);
	} else if(brp_strtolower($POST['mode']) == "check_reserve_stock"){
		$rp_id = $POST['rp_id'];
		$product_id = $POST['product_id']; 
		$process_id = $POST['process_id'];
		$process_type = $POST['process_type']; 

		$qry = "SELECT * FROM  tbl_allocate_process WHERE  p_ref_id = " . $rp_id . " and p_product_id = " .$product_id . " and process_id = ". $process_id ." AND p_status NOT IN (2,3) AND pr_process_type = " . $process_type;
		$res = $dbcon->query($qry);
		$cnt=brp_mysqli_num_rows($res);
		$result= brp_mysqli_fetch_assoc($res);

		

		if($cnt > 0){
			$p_id = $result['p_id'];
			
			$qry_1 = "SELECT process_id FROM  tbl_allocate_process WHERE  p_id = " . $result['previous_process_id'];
		$res_1 = $dbcon->query($qry_1);
		$result1= brp_mysqli_fetch_assoc($res_1);

			if($result['previous_process_id']=="0"){
						$working_qty=check_row_material_availability($dbcon,$result['p_id'],0);
					}else{
							//$process_start_pending_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
						$working_qty=production_process_reseve_stock($dbcon,$result['process_unit'],$result['branch_id'],$result['p_id'],$result['p_product_id'],$result1['process_id'],$process_reserve_id,$process_stock_id,0);
							
					}

				if($working_qty !=0 && $working_qty > 0){
					$arr['msg'] = '1';
					$arr['total_stock'] = $working_qty;

				if($process_type == '1'){
					$arr['inhouse_stock'] = $working_qty;
					$arr['outside_stock'] = 0;
				}else{
					$arr['inhouse_stock'] = 0;
					$arr['outside_stock'] = $working_qty;
				}
			}else{
				$arr['msg'] = 0;
			}

	}
		echo json_encode($arr);
	}else if(brp_strtolower($POST['mode']) == "check_reserve_stock_by_p_id"){
		$p_id = $POST['p_id'];
		
		$qry = "SELECT * FROM  tbl_allocate_process WHERE p_id = " . $p_id;
		$res = $dbcon->query($qry);
		$cnt=brp_mysqli_num_rows($res);
		$result= brp_mysqli_fetch_assoc($res);



		

		$total_stock = 0;
		$total_convert_stock = 0;
		$inhouse_stock = 0;
		$outside_stock = 0;

		if($cnt > 0){
			$qry_1 = "SELECT process_id FROM  tbl_allocate_process WHERE  p_id = " . $result['previous_process_id'];
		$res_1 = $dbcon->query($qry_1);
		$result1= brp_mysqli_fetch_assoc($res_1);
			if($result['previous_process_id']=='0'){
				$working_qty=check_row_material_availability($dbcon,$result['p_id'],0);
			}else{


					//$process_start_pending_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
				$working_qty=production_process_reseve_stock($dbcon,$result['process_unit'],$result['branch_id'],$result['p_id'],$result['p_product_id'],$result1['process_id'],$process_reserve_id,$process_stock_id,0);
			}

			// var_dump(	$working_qty);
			if($working_qty > 0){
				if($working_qty != 0  && $working_qty > 0){
					
					$arr['msg'] = '1';
					$arr['total_stock'] = $working_qty;

					if($result['pr_process_type']  == '1'){
						$arr['inhouse_stock'] = $working_qty;
						$arr['outside_stock'] = 0;
					}else{
						$arr['inhouse_stock'] = 0;
						$arr['outside_stock'] = $working_qty;
					}
				}else{
					
					$arr['msg'] = '1';
					$arr['total_stock'] = $working_qty;

					if($result['pr_process_type'] == '1'){
						$arr['inhouse_stock'] = $working_qty;
						$arr['outside_stock'] = 0;
					}else{
						$arr['inhouse_stock'] = 0;
						$arr['outside_stock'] = $working_qty;
					}
				}
		}else{
					$arr['msg'] = '0';
					$arr['inhouse_stock'] = 0;
					$arr['outside_stock'] = 0;
					$arr['total_stock'] = 0;
		}
		}else{
				$arr['msg'] = '0';
				$arr['inhouse_stock'] = 0;
				$arr['outside_stock'] = 0;
				$arr['total_stock'] = 0;
		}		

		echo json_encode($arr);
	}
	else if(brp_strtolower($POST['mode']) == "bom_process_add") {
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

					$p_qry = "select process_type,process_time,process_opening from tbl_product_process where status = 0 and  product_id = " . $product_id . " and process_id = " . $process_id;
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

				//if($res2['main_request'] == 1){
					$qry = "select process_id,product_id,process_type from tbl_wororder_product_process where process_priority = 1 and rp_id = ". $rp_id;
					$result=$dbcon->query($qry);
					$res=brp_mysqli_fetch_assoc($result);


					$qry1 = "select p_id,process_id,pr_process_type from tbl_allocate_process where  p_status = 0 and p_ref_id = ".$rp_id." and  p_product_id = " . $product_id . " and process_priority in(0,1) and previous_process_id = 0";
					$result1=$dbcon->query($qry1);
					$res1=brp_mysqli_fetch_assoc($result1);
					
					if($res1['process_id'] != $res['process_id'] || $res1['pr_process_type'] != $res['process_type']){
						$upd_ap['process_id']	= $res['process_id'];
						$upd_ap['pr_process_type'] = $res['process_type'];
						$upd_ap['process_priority'] =1;
						//var_dump($upd_ap);
						$updateid=update_record("tbl_allocate_process", $upd_ap,"p_id=".$res1['p_id'], $dbcon);	
					}
			//	}

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

				// $req = $result2['request_id'];

				echo json_encode($arr);
			}
	
function update_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id,$status='0'){
	$qry5 = "SELECT *,product_base_qty FROM tbl_request_product trp left join product_mst mst on mst.product_id = trp.rp_pid WHERE rp_id = ". $rp_id;
	$res5 = $dbcon->query($qry5);
	$cnt5=brp_mysqli_num_rows($res5);
	$result5= brp_mysqli_fetch_assoc($res5);

	$qry = "SELECT * FROM tbl_request_product WHERE status = 0 AND perent_id = " . $rp_id;
	$result = $dbcon->query($qry);			
	while($row = brp_mysqli_fetch_assoc($result)){
		$qry1 = "SELECT res.*,(select IFNULL(sum(base_stock),0) from tbl_reserve_stock where stock_status != 2 and p_id = " . $p_id." and stock_flage = 2 and perent_id = res.reserve_id) as base_stock_minus,(select IFNULL(sum(convert_stock),0)  from tbl_reserve_stock where stock_status != 2 and p_id = " . $p_id." and stock_flage = 2 and perent_id = res.reserve_id) as convert_stock_minus FROM tbl_reserve_stock as res WHERE res.stock_flage = 1 and res.stock_status != 2 and res.p_id = " . $p_id . " and  product_id = " . $row['rp_pid'];

		$result1 = $dbcon->query($qry1);
		while($row1 = brp_mysqli_fetch_assoc($result1)){

			$pending_base_stock = $row1['base_stock'] - $row1['base_stock_minus'];
			$pending_conv_stock = $row1['convert_stock'] - $row1['convert_stock_minus'];
			$approve_base_stock = 0;
			$approve_convert_stock = 0;

			if($pending_base_stock > 0 && $pending_conv_stock > 0){

				if($row1['approve_base_stock'] != "" && $row1['approve_base_stock'] > 0){
					$approve_base_stock = $row1['approve_base_stock'] -  $row1['base_stock_minus'];
				}

				if($row1['approve_convert_stock'] != "" && $row1['approve_convert_stock'] > 0){
					$approve_convert_stock = $row1['approve_convert_stock'] -  $row1['convert_stock_minus'];
				}

				$arr_reserve_stock = [];
						foreach($row1 as $key => $value){
							$arr_reserve_stock[$key] = $value;
						}

						unset($arr_reserve_stock['reserve_id']);
						unset($arr_reserve_stock['base_stock_plus']);
						unset($arr_reserve_stock['convert_stock_plus']);
						unset($arr_reserve_stock['base_stock_minus']);
						unset($arr_reserve_stock['convert_stock_minus']);
						unset($arr_reserve_stock['cdate']);
						$arr_reserve_stock['cdate'] = date("Y-m-d H:i:s");
						
						$arr_reserve_stock['p_id'] = $new_p_id;
						$arr_reserve_stock['stock_flage'] = 1;
						$arr_reserve_stock['stock_status'] = 0;
						$arr_reserve_stock['base_stock'] = $pending_base_stock;
						$arr_reserve_stock['convert_stock'] = $pending_conv_stock;	
						$arr_reserve_stock['approve_base_stock'] = $approve_base_stock;
						$arr_reserve_stock['approve_convert_stock'] = $approve_convert_stock;
						$arr_reserve_stock['used_base_stock'] = 0;
						$arr_reserve_stock['used_convert_stock'] = 0;
			

						$new_rs_id=add_record('tbl_reserve_stock', $arr_reserve_stock, $dbcon);

						$update_stock['base_stock'] = $row1['base_stock'] - $pending_base_stock;
						$update_stock['convert_stock'] = $row1['convert_stock']  - $pending_conv_stock;
						$update_stock['approve_base_stock'] = $row1['approve_base_stock'] - $approve_base_stock;
						$update_stock['approve_convert_stock'] = $row1['approve_convert_stock'] - $approve_convert_stock;
						
						$update_stock['stock_status'] = $row1['stock_status'];

						if($update_stock['base_stock'] == '0' && $update_stock['convert_stock'] == '0'){
							$update_stock['stock_status'] = 2;
						}

						$updateid=update_record('tbl_reserve_stock', $update_stock,"reserve_id=".$row1['reserve_id'] , $dbcon);	
					}
		}

	}

}

function update_reserve_stock_partially($dbcon,$rp_id,$p_id,$new_p_id,$total_stock,$inhouse_stock,$outside_stock){

	$qry = "SELECT * FROM tbl_allocate_process WHERE p_id = ". $new_p_id;
	$result = $dbcon->query($qry);
	$row_1 = brp_mysqli_fetch_assoc($result);


	if($total_stock > 0){
			$qry5 = "SELECT *,product_base_qty FROM tbl_request_product trp left join product_mst mst on mst.product_id = trp.rp_pid WHERE rp_id = ". $rp_id;
	$res5 = $dbcon->query($qry5);
	$cnt5=brp_mysqli_num_rows($res5);
	$result5= brp_mysqli_fetch_assoc($res5);

	$qry = "SELECT * FROM tbl_request_product WHERE status = 0 AND perent_id = " . $rp_id;
	$result = $dbcon->query($qry);			
	while($row = brp_mysqli_fetch_assoc($result)){
		$pro_inhouse_stock = $inhouse_stock;
		$pro_outside_stock = $outside_stock;

		if($inhouse_stock == "" && $outside_stock  == ""){
			$pro_inhouse_stock = $total_stock;
			$pro_outside_stock = $total_stock;
		}
		

		$req_qty_one = $row['req_qty_one'];


		$qry1 = "SELECT res.*,(select IFNULL(sum(base_stock),0) from tbl_reserve_stock where stock_status != 2 and p_id = " . $p_id." and stock_flage = 2 and perent_id = res.reserve_id) as base_stock_minus,(select IFNULL(sum(convert_stock),0)  from tbl_reserve_stock where stock_status != 2 and p_id = " . $p_id." and stock_flage = 2 and perent_id = res.reserve_id) as convert_stock_minus FROM tbl_reserve_stock as res WHERE res.stock_flage = 1 and res.stock_status != 2 and res.p_id = " . $p_id . " and  product_id = " . $row['rp_pid'];

		$result1 = $dbcon->query($qry1);
		while($row1 = brp_mysqli_fetch_assoc($result1)){

			$pending_base_stock = $row1['base_stock'] - $row1['base_stock_minus'];
			$pending_conv_stock = $row1['convert_stock'] - $row1['convert_stock_minus'];
			$approve_base_stock = 0;
			$approve_convert_stock = 0;

			if($pending_base_stock > 0 && $pending_conv_stock > 0){

				if($row1['approve_base_stock'] != "" && $row1['approve_base_stock'] > 0){
					$approve_base_stock = $row1['approve_base_stock'] -  $row1['base_stock_minus'];
				}

				if($row1['approve_convert_stock'] != "" && $row1['approve_convert_stock'] > 0){
					$approve_convert_stock = $row1['approve_convert_stock'] -  $row1['convert_stock_minus'];
				}

				if($row_1['pr_process_type'] == '1'){
						$qty = $pro_inhouse_stock * $req_qty_one;
					}else{
						$qty = $pro_outside_stock * $req_qty_one;
					}
					
					if ($qty > 0) {
					
						if($qty>=$pending_base_stock){
							$rqty=$pending_base_stock;
						}else{
							$rqty=$qty;
						}

						if($qty >= $approve_base_stock){
								$aprv_rqty=$approve_base_stock;
							}else{
								$aprv_rqty=$qty;
							}
					
						if($row_1['pr_process_type'] == '1'){
							$pro_inhouse_stock = $pro_inhouse_stock - ($rqty/$req_qty_one);
						}else{
							 $pro_outside_stock = $pro_outside_stock - ($rqty/$req_qty_one);
						}

						$base_qty = $rqty;
						$conv_qty = convert_stock($dbcon,$rqty,$row['rp_pid'],"conv_unit");
						$aprv_base_qty = $aprv_rqty;
						$aprv_conv_qty = convert_stock($dbcon,$aprv_rqty,$row['rp_pid'],"conv_unit");
						

						$arr_reserve_stock = [];
						foreach($row1 as $key => $value){
							$arr_reserve_stock[$key] = $value;
						}

						unset($arr_reserve_stock['reserve_id']);
						unset($arr_reserve_stock['base_stock_minus']);
						unset($arr_reserve_stock['convert_stock_minus']);

						
						$arr_reserve_stock['p_id'] = $new_p_id;
						$arr_reserve_stock['stock_flage'] = 1;
						$arr_reserve_stock['stock_status'] = 0;

						$arr_reserve_stock['base_stock'] = $base_qty;
						$arr_reserve_stock['convert_stock'] = $conv_qty;	
						$arr_reserve_stock['approve_base_stock'] = $aprv_base_qty;
						$arr_reserve_stock['approve_convert_stock'] = $aprv_conv_qty;
						$arr_reserve_stock['used_base_stock'] = 0;
						$arr_reserve_stock['used_convert_stock'] = 0;

						$new_rs_id=add_record('tbl_reserve_stock', $arr_reserve_stock, $dbcon);

						$update_stock['base_stock'] = $row1['base_stock'] - $base_qty;
						$update_stock['convert_stock'] = $row1['convert_stock']  - $conv_qty;
						$update_stock['approve_base_stock'] = $row1['approve_base_stock'] - $aprv_base_qty;
						$update_stock['approve_convert_stock'] = $row1['approve_convert_stock'] - $aprv_conv_qty;
						
						$update_stock['stock_status'] = $row1['stock_status'];

						if($update_stock['base_stock'] == '0' && $update_stock['convert_stock'] == '0'){
							$update_stock['stock_status'] = 2;
						}

					$updateid=update_record('tbl_reserve_stock', $update_stock,"reserve_id=".$row1['reserve_id'] , $dbcon);	
					}
				}
		}

	}		
	}
}

function jobwork_short_close($p_id,$dbcon){

					$qry = "SELECT * FROM  tbl_job_work_sub_trn WHERE grn_complete_status = 0 AND p_id = " . $p_id;
						
					$res = $dbcon->query($qry);
					$cnt=brp_mysqli_num_rows($res);

					$result1= brp_mysqli_fetch_assoc($res);
			
					if($cnt > 0){

						$update_info['grn_complete_status'] = 1;

						$update_job_sub_id=update_record('tbl_job_work_sub_trn', $update_info,"job_work_sub_trn_id=".$result1['job_work_sub_trn_id'] , $dbcon);

						$update_job_id=update_record('tbl_job_work_trn', $update_info,"job_work_trn_id=".$result1['job_work_trn_id'] , $dbcon);

						$qry1 = "SELECT * FROM  tbl_job_work_trn WHERE job_work_trn_id = " . $result1['job_work_trn_id'];
						$res1 = $dbcon->query($qry1);
						$cnt1=brp_mysqli_num_rows($res1);
						$result2= brp_mysqli_fetch_assoc($res1);

						if($cnt1 > 0){
							$updateid=update_record('tbl_job_work', $update_info,"job_work_id=".$result2['job_work_id'] , $dbcon);
						}
						return $result1['product_base_qty'];
					}else{
						return '0';
					}

				}



function jobwork_change_qty($p_id,$qty,$dbcon){
	$qry = "SELECT * FROM  tbl_job_work_sub_trn WHERE grn_complete_status = 0 AND p_id = " . $p_id;
		
	$res = $dbcon->query($qry);
	$cnt=brp_mysqli_num_rows($res);

	$result1= brp_mysqli_fetch_assoc($res);

	if($cnt > 0){

		$update_info['product_base_qty'] = $qty;

		$update_job_sub_id=update_record('tbl_job_work_sub_trn', $update_info,"job_work_sub_trn_id=".$result1['job_work_sub_trn_id'] , $dbcon);

		$update_job_id=update_record('tbl_job_work_trn', $update_info,"job_work_trn_id=".$result1['job_work_trn_id'] , $dbcon);
	
	}

}
				
function update_process_reserve_stock($dbcon,$rp_id,$p_id,$new_p_id,$status='0'){

	$qry = "SELECT * FROM tbl_allocate_process WHERE p_id = ". $p_id;
	$result = $dbcon->query($qry);
	$row = brp_mysqli_fetch_assoc($result);

	$qry1 = "SELECT res.*,(select IFNULL(sum(base_stock),0) from tbl_process_reserve_stock where stock_status != 2 and p_id = " . $p_id." and stock_flage = 2 and perent_id = res.process_reserve_id) as base_stock_minus,(select IFNULL(sum(conv_stock),0)  from tbl_process_reserve_stock where stock_status != 2 and p_id = " . $p_id." and stock_flage = 2 and perent_id = res.process_reserve_id) as conv_stock_minus FROM tbl_process_reserve_stock as res WHERE res.stock_flage = 1 and res.stock_status != 2 and res.p_id = " . $p_id . " and  product_id = " . $row['p_product_id'];
	$result1 = $dbcon->query($qry1);
		while($row1 = brp_mysqli_fetch_assoc($result1)){

			$pending_base_stock = $row1['base_stock'] - $row1['base_stock_minus'];
			$pending_conv_stock = $row1['conv_stock'] - $row1['conv_stock_minus'];
			$approve_base_stock = 0;
			$approve_convert_stock = 0;

			if($pending_base_stock > 0 && $pending_conv_stock > 0){

				if($row1['approve_base_stock'] != "" && $row1['approve_base_stock'] > 0){
					$approve_base_stock = $row1['approve_base_stock'] -  $row1['base_stock_minus'];
				}

				if($row1['approve_convert_stock'] != "" && $row1['approve_convert_stock'] > 0){
					$approve_convert_stock = $row1['approve_convert_stock'] -  $row1['conv_stock_minus'];
				}

						$arr_reserve_stock = [];
						foreach($row1 as $key => $value){
							$arr_reserve_stock[$key] = $value;
						}

						unset($arr_reserve_stock['process_reserve_id']);
						unset($arr_reserve_stock['base_stock_plus']);
						unset($arr_reserve_stock['conv_stock_plus']);
						unset($arr_reserve_stock['base_stock_minus']);
						unset($arr_reserve_stock['conv_stock_minus']);

						
						$arr_reserve_stock['p_id'] = $new_p_id;
						$arr_reserve_stock['stock_flage'] = 1;
						$arr_reserve_stock['stock_status'] = 0;
						$arr_reserve_stock['base_stock'] = $pending_base_stock;
						$arr_reserve_stock['conv_stock'] = $pending_conv_stock;	
						$arr_reserve_stock['approve_base_stock'] = $approve_base_stock;
						$arr_reserve_stock['approve_convert_stock'] = $approve_convert_stock;
						$arr_reserve_stock['used_base_stock'] = 0;
						$arr_reserve_stock['used_conv_stock'] = 0;
			

						$new_rs_id=add_record('tbl_process_reserve_stock', $arr_reserve_stock, $dbcon);

						$update_stock['base_stock'] = $row1['base_stock'] - $pending_base_stock;
						$update_stock['conv_stock'] = $row1['conv_stock']  - $pending_conv_stock;
						$update_stock['approve_base_stock'] = $row1['approve_base_stock'] - $approve_base_stock;
						$update_stock['approve_convert_stock'] = $row1['approve_convert_stock'] - $approve_convert_stock;
						
						$update_stock['stock_status'] = $row1['stock_status'];

						if($update_stock['base_stock'] == '0' && $update_stock['conv_stock'] == '0'){
							$update_stock['stock_status'] = 2;
						}

						$updateid=update_record('tbl_process_reserve_stock', $update_stock,"process_reserve_id=".$row1['process_reserve_id'] , $dbcon);	
				}
		}		
}

function update_process_reserve_stock_partially($p_id,$dbcon,$new_p_id,$total_stock,$inhouse_stock,$outside_stock){
	
	if($total_stock > 0){
			
	$qry = "SELECT * FROM tbl_allocate_process WHERE p_id = ". $new_p_id;
	$result = $dbcon->query($qry);
	$row_1 = brp_mysqli_fetch_assoc($result);

	$qry1 = "SELECT res.*,(select IFNULL(sum(base_stock),0) from tbl_process_reserve_stock where stock_status != 2 and p_id = " . $p_id." and stock_flage = 2 and perent_id = res.process_reserve_id) as base_stock_minus,(select IFNULL(sum(conv_stock),0)  from tbl_process_reserve_stock where stock_status != 2 and p_id = " . $p_id." and stock_flage = 2 and perent_id = res.process_reserve_id) as conv_stock_minus FROM tbl_process_reserve_stock as res WHERE res.stock_flage = 1 and res.stock_status != 2 and res.p_id = " . $p_id . " and  product_id = " . $row_1['p_product_id'];
	$result1 = $dbcon->query($qry1);
		while($row1 = brp_mysqli_fetch_assoc($result1)){

			$pending_base_stock = $row1['base_stock'] - $row1['base_stock_minus'];
			$pending_conv_stock = $row1['conv_stock'] - $row1['conv_stock_minus'];
			$approve_base_stock = 0;
			$approve_convert_stock = 0;

			if($pending_base_stock > 0 && $pending_conv_stock > 0){

				if($row1['approve_base_stock'] != "" && $row1['approve_base_stock'] > 0){
					$approve_base_stock = $row1['approve_base_stock'] -  $row1['base_stock_minus'];
				}

				if($row1['approve_convert_stock'] != "" && $row1['approve_convert_stock'] > 0){
					$approve_convert_stock = $row1['approve_convert_stock'] -  $row1['conv_stock_minus'];
				}

				if($row_1['pr_process_type'] == '1'){
						$qty =$inhouse_stock;
					}else{
						$qty =  $outside_stock;
					}



					if ($qty > 0) {

						if($qty >= $pending_base_stock){
							$rqty=$pending_base_stock;
						}else{
							$rqty=$qty;
						}

						if($qty>=$approve_base_stock){
								$aprv_rqty=$approve_base_stock;
							}else{
								$aprv_rqty=$qty;
							}

						if($row_1['pr_process_type'] == '1'){
							$inhouse_stock = $inhouse_stock - $rqty;
						}else{
							 $outside_stock = $outside_stock - $rqty;
						}

						$base_qty = $rqty;
						$conv_qty = convert_stock($dbcon,$rqty,$row_1['p_product_id'],"conv_unit");
						$aprv_base_qty = $aprv_rqty;
						$aprv_conv_qty = convert_stock($dbcon,$aprv_rqty,$row_1['p_product_id'],"conv_unit");
						$arr_reserve_stock = [];
						foreach($row1 as $key => $value){
							$arr_reserve_stock[$key] = $value;
						}

						unset($arr_reserve_stock['process_reserve_id']);
						unset($arr_reserve_stock['base_stock_plus']);
						unset($arr_reserve_stock['conv_stock_plus']);
						unset($arr_reserve_stock['base_stock_minus']);
						unset($arr_reserve_stock['conv_stock_minus']);

						
						$arr_reserve_stock['p_id'] = $new_p_id;
						$arr_reserve_stock['stock_flage'] = 1;
						$arr_reserve_stock['stock_status'] = 0;
						$arr_reserve_stock['used_base_stock'] = 0;
						$arr_reserve_stock['used_conv_stock'] = 0;
						$arr_reserve_stock['base_stock'] = 	$base_qty;
						$arr_reserve_stock['conv_stock'] = $conv_qty;	
						$arr_reserve_stock['approve_base_stock'] = $aprv_base_qty;
						$arr_reserve_stock['approve_convert_stock'] = $aprv_conv_qty;
			

						$new_rs_id=add_record('tbl_process_reserve_stock', $arr_reserve_stock, $dbcon);

						$update_stock['base_stock'] = $row1['base_stock'] - $base_qty;
						$update_stock['conv_stock'] = $row1['conv_stock']  - $conv_qty;
						$update_stock['approve_base_stock'] = $row1['approve_base_stock'] - $aprv_base_qty;
						$update_stock['approve_convert_stock'] = $row1['approve_convert_stock'] - $aprv_conv_qty;
						
						$update_stock['stock_status'] = $row1['stock_status'];

						if($update_stock['base_stock'] == '0' && $update_stock['conv_stock'] == '0'){
							$update_stock['stock_status'] = 2;
						}

						$updateid=update_record('tbl_process_reserve_stock', $update_stock,"process_reserve_id=".$row1['process_reserve_id'] , $dbcon);	
				}
		}
	}
		
	}
}
/*
	END :: Code by Sanat :: 27-08-2021
*/

function check_jobcard_process($dbcon,$rp_id){
	$query = "select start_qty,	p_status from tbl_allocate_process where process_priority =1 and p_status !=2 and p_ref_id = " . intval($rp_id) ." order by p_id asc limit 1";

	$row = brp_mysqli_fetch_assoc($dbcon->query($query)); 

	if($row &&$row['p_status'] == '0' && ($row['start_qty'] == "" || $row['start_qty'] == "0")){
		return 0;
	}else{
		return 1;
	}
}	


function check_child_is_requested($dbcon,$rp_id){
				$used = 0;


				$qry = "select count(rp_id) as request from tbl_request_product where status = 0 and perent_id = " . $rp_id;
				$result=$dbcon->query($qry);
				$res=brp_mysqli_fetch_assoc($result);

				if($res['request'] > 0){
					$used++;
				}

				$qry1 = "select count(rp_id) as request from tbl_request_product where status = 0 and indent_status = 3 and perent_id = " . $rp_id;
				$result1=$dbcon->query($qry1);
				$res1=brp_mysqli_fetch_assoc($result1);

				if($res1['request'] > 0){
					$used++;
				}


				$qry1 = "select count(rp_id) as request from tbl_request_product where status = 0 and indent_status = 3 and rp_id = " . $rp_id;
				$result1=$dbcon->query($qry1);
				$res1=brp_mysqli_fetch_assoc($result1);

				if($res1['request'] > 0){
					$used++;
				}

				$qry2 = "select count(reserve_id) as request from tbl_reserve_stock where stock_flage = 2 and  stock_status = 0 and request_id = " . $rp_id;
					$result2=$dbcon->query($qry2);
					$res2=brp_mysqli_fetch_assoc($result2);

					if($res2['request'] > 0){
						$used++;
					}

				$qry1 = "select perent_id from tbl_request_product where status = 0 and rp_id = " . $rp_id;
				$result1=$dbcon->query($qry1);

				if(brp_mysqli_num_rows($result1) > 0){
					$res1=brp_mysqli_fetch_assoc($result1);
					$chk_rp_id = $res1['perent_id'];

					$qry2 = "select count(reserve_id) as request from tbl_reserve_stock where stock_flage = 2 and  stock_status = 0 and request_id = " . $chk_rp_id;
					$result2=$dbcon->query($qry2);
					$res2=brp_mysqli_fetch_assoc($result2);

					if($res2['request'] > 0){
						$used++;
					}

				}

				$qry2 = "select count(wip_stock_allocate_id) as request from wip_stock_allocate where stock_flag = 2 and  status = 0 and rp_id = " . $rp_id;
					$result2=$dbcon->query($qry2);
					$res2=brp_mysqli_fetch_assoc($result2);

					if($res2['request'] > 0){
						$used++;
					}

				/*$qry3 = "select count(p_id) as request from tbl_allocate_process where p_status in (1,3) and p_ref_id = " . $rp_id;*/
				$qry3 = "select count(p_id) as request from tbl_allocate_process where p_status in (1) and p_ref_id = " . $rp_id;
				$result3=$dbcon->query($qry3);
				$res3=brp_mysqli_fetch_assoc($result3);	

				if($res3['request'] > 0){
						$used++;
					}

				return $used;
			}


			function change_child_product_priority($dbcon,$rp_id,$priority){
					$qry = "SELECT rp_id FROM tbl_request_product WHERE perent_id = " . $rp_id;
					$result = $dbcon->query($qry);	

					if($result > 0){
						while($row = brp_mysqli_fetch_array($result)){
							$info['priority_status'] = $priority;
							$updateid=update_record('tbl_request_product', $info,"rp_id=".$row['rp_id'], $dbcon);		

							change_child_product_priority($dbcon,$row['rp_id'],$priority);
						}
						
					}			
			}

?>
