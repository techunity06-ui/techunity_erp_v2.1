<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRODUCTION_GRN_LIST_SLUG_VIEW,PRODUCTION_GRN_LIST_SLUG_CREATE,PRODUCTION_GRN_LIST_SLUG_UPDATE,PRODUCTION_GRN_LIST_SLUG_DELETE
]);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=''; 
		$where.=" and grn.grn_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND grn.grn_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		$where.=" and grn.ref_type=".$POST['grn_against'];
		
		if($POST['grn_against']=="1"){
			$isJOIN_new = array();
			$aColumns_new=array('"" as pono');
		}else{
			$isJOIN_new = array('left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id','left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=gtrn.purchaseordertrn_id','left join tbl_purchaseorder as po on po.purchaseorder_id=ptrn.purchaseorder_id');
			$aColumns_new=array('group_concat(DISTINCT po.purchaseorder_no) as pono');
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('grn.grn_id','grn.grn_no', 'grn.grn_date','grn.ref_type', 'cust.l_name', 'grn.grn_status','grn.cdate','grn.user_id','grn.purchaseorder_id');
		$aColumns=array_merge($aColumns,$aColumns_new);
		$sIndexColumn = "grn.grn_id";
		$isWhere = array("grn.grn_status = 0".$where);
		$sTable = "tbl_grn as grn";
		//$isJOIN = array('left join tbl_ledger as cust on cust.l_id=grn.vender_id','left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id','left join tbl_grn_sub_trn as gstrn on gstrn.grn_trn_id=gtrn.grn_trn_id','left join tbl_purchaseordertrn as ptrn on ptrn.purchaseordertrn_id=gtrn.purchaseordertrn_id','left join tbl_purchaseorder as po on po.purchaseorder_id=ptrn.purchaseorder_id');
		$isJOIN = array('left join tbl_ledger as cust on cust.l_id=grn.vender_id');
		$isJOIN=array_merge($isJOIN,$isJOIN_new);
		$hOrder = "grn.grn_id desc";
		$hGroupby = array("grn.grn_id");
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			if($row['ref_type']=='1'){ $ref_type="JOBWORK"; } else {  $ref_type="PO"; } 
			
			$row_data = array();
			$que_po12="select grn_id from tbl_grn_trn where product_qc=0 and grn_id=".$row['grn_id'];
			$resi_grn12=$dbcon->query($que_po12);
			$re12=mysqli_fetch_assoc($resi_grn12);
			
			if(in_array(PRODUCTION_GRN_LIST_SLUG_UPDATE,$bulkAccessArray)){
				if(!empty($re12['grn_id'])){
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.$row["grn_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.date('d M, Y',strtotime($row["grn_date"])).'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.$row["pono"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.$ref_type.'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["grn_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'">'.$row["l_name"].'</a>';
				}else{
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["grn_no"].'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.date('d M, Y',strtotime($row["grn_date"])).'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["pono"].'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$ref_type.'</a> ';
					$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["l_name"].'</a> ';
				}
			}else{
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["grn_no"].'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.date('d M, Y',strtotime($row["grn_date"])).'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["pono"].'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$ref_type.'</a> ';
				$row_data[] = '<a class="" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'">'.$row["l_name"].'</a> ';
			}
			

			$edit_btn=''; $delete_btn=''; $view='';

			if(in_array(PRODUCTION_GRN_LIST_SLUG_UPDATE,$bulkAccessArray)){
				if(!empty($re12['grn_id'])){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_edit/'.$row['grn_id'].'"><i class="fa fa-pencil"></i></a>'; 
				}
			}
			if(in_array(PRODUCTION_GRN_LIST_SLUG_DELETE,$bulkAccessArray)){
				if(!empty($re12['grn_id'])){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_grn('.$row['grn_id'].','.$row['purchaseorder_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
			}
			if(in_array(PRODUCTION_GRN_LIST_SLUG_VIEW,$bulkAccessArray)){
				$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'grn_view/'.$row['grn_id'].'"><i class="fa fa-eye"></i></a> ';
			}  
			
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$view;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") 
	{
		$grn_against = $POST['grn_against'];
		//old purchase code start
		if($grn_against=='2')
		{
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id='8' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['vender_id']			= $POST['vender_id'];
			$info['gir_no']				= $POST['gir_no'];
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['ref_type']			= $POST['grn_against'];
			$info['purchaseorder_id']	= $POST['purchaseorder_id'];
			//$info['branch_id']		= $POST['branch_id'];
			$info['remark']				= $_POST['remark'];
			//$info['ref_no']				= $_POST['request_no'];
			
			$info['material_inspected']		= $_POST['material_inspected'];
			$info['test_certificate']		= $_POST['test_certificate'];
			$info['test_certificate_code']	= $_POST['test_certificate_code'];
			$info['dimension_inspected']	= $_POST['dimension_inspected'];
			$info['inspection_report']		= $_POST['inspection_report'];
			$info['qty_verified']			= $_POST['qty_verified'];
			$info['process_checked']		= $_POST['process_checked'];

			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			$grn_id=add_record('tbl_grn', $info, $dbcon, $branch_id);
			
			if($grn_id){
				$grn_qty=$POST['grn_qty'];
				for($k=0;$k<count($grn_qty);$k++)
				{
					$loop_id=$grn_qty[$k];
					if($POST['grn_qty'][$k]!=0){
						if($POST['grn_qty'][$k]!=""){

							if($info['ref_type']==2){
								if(strtolower($POST['qc_type'][$k])=="no"){
									$godown_id=$POST['grn_godown'][$k];
									$product_qc=1;
								}else{
									$godown_id="";
									$product_qc=0;
								}
								$wwe=get_pro_field($dbcon,$POST['grn_pid'][$k],'minimum_tolerance');
						//$info2['purchaseorder_id']	=$POST['purchaseorder_id'];
								$info2s['purchaseordertrn_id']	=$POST['purchaseordertrn_id'][$k];
								$info2s['product_id']			=$POST['grn_pid'][$k];
								$info2s['grn_id']				=$grn_id;
								$info2s['product_qty']			=$POST['grn_qty_hide'][$k];
								$info2s['unit_id']				=$POST['unit_id'][$k];
								$info2s['product_conv_qty']		=$POST['conv_grn_qty_hide'][$k];
								$info2s['product_conv_unit']	=$POST['conv_unit_id'][$k];
								$info2s['grn_godown']			=$godown_id;
								$info2s['product_qc']			=$product_qc;
								$info2s['tolerance']			=$wwe;
								$info2s['po_ref_id']			=$POST['po_ref_id'][$k];
					//	var_dump($info2['po_ref_id']);
								$info2s['cdate']				= date("Y-m-d H:i:s");
								$info2s['user_id']			= $_SESSION['user_id'];
								$info2s['company_id']		= $_SESSION['company_id'];
						//var_dump($info2);
								$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2s, $dbcon, $branch_id);

								$ptrn=$info2s['purchaseordertrn_id'];
								$hhhh=grn_po_sub_trn($dbcon,$tbl_grn_trn_id,$ptrn);
						//var_dump($hhhh);
								if($godown_id!=""){
									$stock_id=add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id);

									$query_res="select * from tbl_request_product as req where rp_id in (".$info2s['po_ref_id'].")";
									$result_res=$dbcon->query($query_res);
									$resqty1=$POST['grn_qty'][$k];
									while($row_res=mysqli_fetch_assoc($result_res)){

										$query_ind="select sum(approve_qty) as app_qty from approve_indent as req where rp_id=".$row_res['rp_id'];
										$result_ind=$dbcon->query($query_ind);
										$row_ind=mysqli_fetch_assoc($result_ind);
								//echo $query_ind;
										$reserve_id="";
										$request_id=$row_res['rp_id'];
										$complaint_id="";
										$sales_order_trn_id="";

										$used_rese=total_reserve_stock($dbcon,$row_res['rp_pid'],$info2s['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id);
								//var_dump($used_rese);
										$res_pending=$row_ind['app_qty']-$used_rese;

								//var_dump($res_pending);
								//var_dump($resqty1);
									//add_request_reserve_stock($dbcon,$info2s['po_ref_id'],$info2s['product_qty'],$info2s['unit_id']);
										if($resqty1>=$res_pending){
									//add_request_reserve_stock($dbcon,$request_id,$res_pending,$info2s['unit_id'],$branch_id);

											grn_sub_trn_wise_reserv_stock_add($dbcon,$res_pending,$info2s['unit_id'],$POST['grn_date'],"",$request_id,$stock_id);

											$resqty1=$resqty1-$res_pending;
										}else{
									//add_request_reserve_stock($dbcon,$request_id,$resqty,$info2s['unit_id'],$branch_id);

											grn_sub_trn_wise_reserv_stock_add($dbcon,$resqty1,$info2s['unit_id'],$POST['grn_date'],"",$request_id,$stock_id);

											$resqty1=$resqty1-$resqty1;
										}
								//var_dump($resqty1);
									}
								}
							}
						}
					}
				}		
			//$updatetrnid=update_record('tbl_grn_trn', $infotrn,"grn_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
			/*Update Data in Trn Table End*/
			$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 1);
		}
		//old purchase code end
		//job work to grn new code start 
		if($grn_against=='1'){
			$branch_id = $POST['branch_id'];

			$info_grn['grn_no']				= load_series_no_using_type_id($dbcon,INHOUSE_GRN,$_SESSION['company_id'],$branch_id1);
			$info_grn['grn_date']			= date("Y-m-d");
			$info_grn['gir_no']				= $POST['gir_no'];
			$info_grn['invoice_no']			= $POST['invoice_no'];
			$info_grn['challan_no']			= $POST['challan_no'];
			$info_grn['ref_type']			= "1";
			$info_grn['vender_id']			= $POST['vender_id'];
			$info_grn['remark']				= $POST['remark'];

			$info_grn['cdate']				= date("Y-m-d H:i:s");
			$info_grn['user_id']			= $_SESSION['user_id'];
			$info_grn['company_id']			= $_SESSION['company_id'];

			$grn_id=add_record('tbl_grn',$info_grn, $dbcon,$branch_id);

			if($grn_id){
				update_series_no_using_type_id($dbcon,INHOUSE_GRN,$_SESSION['company_id'],$branch_id1);

				$grn_qty_st=$POST['grn_qty'];
				for($m=0;$m<count($grn_qty_st);$m++)
				{
					if($POST['grn_qty'][$m]!=0 && $POST['grn_qty'][$m]!="")
					{
						$product_id			= $POST['product_id'][$m];
						$stop_qty			= $POST['grn_qty'][$m];
						$product_base_unit	= $POST['product_base_unit'][$m];
						$process_id			= $POST['process_id'][$m];
						$grn_godown			= $POST['grn_godown'][$m];
						$p_id				= $POST['p_id'][$m];

						grn_trn_and_sub_trn_entry($dbcon,$product_id,$grn_id,$stop_qty,$product_base_unit,$process_id,$grn_godown,$p_id,$branch_id);

					}
				}
			}	
		}
		//job work to grn new code end
		if(!empty($_FILES['grn_file']['tmp_name'][0])) {
			$imgresp = upload_grn_receipt($_FILES,$dbcon,$grn_id);
		}
		if($grn_id){	
			$arr['msg']="1";	
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"grn_add",1,"tbl_grn",$inserpoid);						
		}
		else{
			$arr['msg']="0";
		}
		$arr['back']=$POST['back'];
		
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "add_old"){
		if($grn_against=='1')
		{
			$grn_qty_st=$POST['grn_qty'];
			for($m=0;$m<count($grn_qty_st);$m++)
			{
				if($POST['grn_qty'][$m]!=0)
				{
					if($POST['grn_qty'][$m]!="")
					{
						$qty = $POST['grn_qty'][$m];
						$j_alloc_process_id = $POST['j_alloc_process_id'][$m];
						if(strtolower($POST['qc_type'][$m])=="no"){
							$godown_id=$POST['grn_godown'][$m];
							$product_qc=1;
						}else{
							$godown_id="";
							$product_qc=0;
						}
						
						$wwe=get_pro_field($dbcon,$POST['grn_pid'][$m],'minimum_tolerance');
						//$info2['purchaseorder_id']	=$POST['purchaseorder_id'];
						$info2s['purchaseordertrn_id']=$POST['purchaseordertrn_id'][$m];
						$info2s['product_id']		=$POST['grn_pid'][$m];
						$info2s['grn_id']			=$inserpoid;
						$info2s['product_qty']		=$POST['grn_qty'][$m];
						$info2s['unit_id']			=$POST['unit_id'][$m];
						$info2s['grn_godown']		=$godown_id;
						$info2s['product_qc']		=$product_qc;
						$info2s['tolerance']		=$wwe;
						$info2s['po_ref_id']		=$POST['po_ref_id'][$m];
					//	var_dump($info2['po_ref_id']);
						$info2s['cdate']			= date("Y-m-d H:i:s");
						$info2s['user_id']			= $_SESSION['user_id'];
						$info2s['company_id']		= $_SESSION['company_id'];

					//pathik start 26-02-2021 3:46	
						$info2s['process_id']		= $POST['j_pr_process_id'][$m];
					//pathik end 26-02-2021 3:46	
						//var_dump($info2);
						$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2s, $dbcon,$branch_id);
						
						
						
						// main processs
						$sqty=0;$jpqty=0;$pending_qty=0;
						$a_query="select * from tbl_allocate_process where p_id in (".$j_alloc_process_id.")";
						//var_dump($query);
						$a_result=$dbcon->query($a_query);
						while($a_row=mysqli_fetch_assoc($a_result))
						{

							$query11="select sum(pt_qty) as start_qty from tbl_allocate_process_trn as trn where p_status=0 and pt_alloc_id=".$a_row['p_id'];
							$rel1=mysqli_fetch_assoc($dbcon->query($query11));
							
							$query12="select sum(pt_qty) as end_qty from tbl_allocate_process_trn as trn where p_status=1 and pt_alloc_id=".$a_row['p_id'];
							$rel2=mysqli_fetch_assoc($dbcon->query($query12));

							$pending_qty=$rel1['start_qty']-$rel2['end_qty'];
							/* if($qty!=0){
								if($qty!=0){ */
									if($pending_qty>=$qty){
										$sqty=$qty;
										$jpqty=$sqty;

										$info6['process_id']		= $a_row['process_id'];
										$info6['p_start_time']		='';
										$info6['p_end_time']		= date("Y-m-d H:i:s");
										$info6['p_qty']				= $sqty;
										$info6['pen_qty']			='';
										$info6['p_status']			='2';
										$info6['p_ref_id']			= $a_row['p_ref_id'];
										$info6['p_product_id']		= $a_row['p_product_id'];
										$info6['j_alloc_process_id']= $a_row['p_id'];

										$info6['cdate']				= date("Y-m-d H:i:s");
										$info6['user_id']			= $_SESSION['user_id'];
										$info6['company_id']		= $_SESSION['company_id'];


										$inserusrid1=add_record('tbl_jobwork_history', $info6, $dbcon, $branch_id);

										add_process_stock_new($dbcon,$a_row['p_id'],$sqty,$sqty);


								//pathik start
										$job_qty=$sqty;
										$jid=explode(",",$POST['j_job_work_id'][$m]);
										for($p=0;$p<count($jid);$p++)
										{
											$jobquery11="select sum(j_qty) as start_qty from tbl_jobwork as trn where status=0 and jobwork_id=".$jid[$p];
											$jobrel1=mysqli_fetch_assoc($dbcon->query($jobquery11));

											$jobquery12="select sum(product_qty) as end_qty from tbl_grn_sub_trn as trn where status=0 and jobwork_id=".$jid[$p];
											$jobrel2=mysqli_fetch_assoc($dbcon->query($jobquery12));
											$job_pending_qty=$jobrel1['start_qty']-$jobrel2['end_qty'];

											if($job_qty!=0){
												if($job_qty!=""){
													if($job_qty>=$job_pending_qty){
														$infogtrn['product_id']			= $info2s['product_id'];
														$infogtrn['grn_trn_id']			= $tbl_grn_trn_id;

														$infogtrn['jobwork_id']			= $jid[$p];
														$infogtrn['product_qty']		= $job_pending_qty;

														$infogtrn['cdate']				= date("Y-m-d H:i:s");
														$infogtrn['user_id']			= $_SESSION['user_id'];
														$infogtrn['company_id']			= $_SESSION['company_id'];

														$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon, $branch_id);

										//close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$infogtrn['product_qty']);

														$process_id=$re1['p_id'];
											/* $que_po="select * from tbl_allocate_process where p_id=".$a_row['p_id'];
											$resi_grn=$dbcon->query($que_po);
											$re=mysqli_fetch_assoc($resi_grn); */
											
											add_process_trn($dbcon,$a_row['p_id'],$a_row['p_ref_id'],$a_row['p_product_id'],$a_row['process_id'],$infogtrn['product_qty'],"1",$inserpoid);
											$j_process_qty=$infogtrn['product_qty'];
										//var_dump("1");
										//var_dump($j_process_qty);
											$j_que_po="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jid[$p]." and p_id=".$a_row['p_id']." having qty>used_qty order by jobwork_process_id";
										//var_dump($j_que_po);
											$j_resi_grn=$dbcon->query($j_que_po);
											while($j_re=mysqli_fetch_assoc($j_resi_grn)){
											//var_dump("2");
											//var_dump($j_re['pen_qty']);
											//var_dump("3");
											//var_dump($j_process_qty);
												if($j_process_qty!=0){
													if($j_process_qty!=""){
														if($j_process_qty>=$j_re['pen_qty']){
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_re['pen_qty']." where jobwork_process_id=".$j_re['jobwork_process_id']."");
													//var_dump($j_re['pen_qty']);
															$j_process_qty=$j_process_qty-$j_re['pen_qty'];
														}else{
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_process_qty." where jobwork_process_id=".$j_re['jobwork_process_id']."");
													//var_dump($j_process_qty);
															$j_process_qty=$j_process_qty-$j_process_qty;
														}
													}
												}
											}
										//die();
											$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$infogtrn['product_qty']." where jobwork_id=".$jid[$p]."");

											$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$infogtrn['product_qty']." where p_id=".$a_row['p_id']);
											
											$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
											where p_id=".$a_row['p_id'];
											$se=mysqli_fetch_assoc($dbcon->query($set11));
											$sss1=$se['start_qty']-$se['end_qty'];
											if($se['start_qty']<=$se['end_qty']){
												$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$a_row['p_id'];
												$dbcon->query($bb);
											}
											if($se['p_qty']==$se['end_qty']){
												$date=date("Y-m-d h:i:sa");
												$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$a_row['p_id']);
											}
											$job_qty=$job_qty-$job_pending_qty;
										}else{
											$infogtrn['product_id']		= $info2s['product_id'];
											$infogtrn['grn_trn_id']		= $tbl_grn_trn_id;
											
											$infogtrn['jobwork_id']			= $jid[$p];
											$infogtrn['product_qty']	= $job_qty;
											
											$infogtrn['cdate']				= date("Y-m-d H:i:s");
											$infogtrn['user_id']			= $_SESSION['user_id'];
											$infogtrn['company_id']			= $_SESSION['company_id'];
											
											$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon, $branch_id);
											
											
											//close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$infogtrn['product_qty']);
											
											$process_id=$re1['p_id'];
											/* $que_po="select * from tbl_allocate_process where p_id=".$a_row['p_id'];
											$resi_grn=$dbcon->query($que_po);
											$re=mysqli_fetch_assoc($resi_grn); */
											
											add_process_trn($dbcon,$a_row['p_id'],$a_row['p_ref_id'],$a_row['p_product_id'],$a_row['process_id'],$infogtrn['product_qty'],"1",$inserpoid);

											$j_que_po="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jid[$p]." and p_id=".$a_row['p_id']." having qty>used_qty";
											$j_process_qty=$infogtrn['product_qty'];
											$j_resi_grn=$dbcon->query($j_que_po);
											while($j_re=mysqli_fetch_assoc($j_resi_grn)){
												if($j_process_qty!=0){
													if($j_process_qty!=""){
														if($j_process_qty>=$j_re['pen_qty']){
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_re['pen_qty']." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_re['pen_qty'];
														}else{
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_process_qty." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_process_qty;
														}
													}
												}
											}
											$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$infogtrn['product_qty']." where jobwork_id=".$jid[$p]."");

											$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$infogtrn['product_qty']." where p_id=".$a_row['p_id']);
											
											$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
											where p_id=".$a_row['p_id'];
											$se=mysqli_fetch_assoc($dbcon->query($set11));
											$sss1=$se['start_qty']-$se['end_qty'];
											if($se['start_qty']<=$se['end_qty']){
												$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$a_row['p_id'];
												$dbcon->query($bb);
											}
											if($se['p_qty']==$se['end_qty']){
												$date=date("Y-m-d h:i:sa");
												$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$a_row['p_id']);
											}
											$job_qty=$job_qty-$job_qty;
										}
									}
								}
							}
							if($product_qc==1){	
								$process=get_next_process($dbcon,$a_row['process_id'],$info2s['product_id'],$a_row['p_ref_id'],$a_row['process_priority']);

								$process_pr=json_decode($process);

								$process_id_new=$process_pr->process_id;
								$process_type=$process_pr->process_type;
								$process_priority=$process_pr->process_priority;

								if($process_id_new==0){
									if($godown_id!=""){
										add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$sqty,"1",$branch_id);

										add_request_reserve_stock($dbcon,$a_row['p_ref_id'],$sqty,$info2s['unit_id'],$branch_id);

									}
								}else{
									process_allocate($dbcon,$a_row['p_id'],$process_id_new,$sqty,$a_row['p_ref_id'],"tbl_grn_trn",$info2s['product_id'],$process_type,$info2s['unit_id'],$process_priority,"",$branch_id);

								}
								add_process_stock($dbcon,$a_row['p_id'],$sqty,0,$process_id_new);
								
							}


								//pathik close

							if($a_row['previous_process_id']=="0"){
									/*$grn_qty=$POST['row_product_id'];
									for($k=0;$k<count($grn_qty);$k++)
									{*/

										$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
										WHERE rpro.p_status!=2 AND rpro.p_id in (".$j_alloc_process_id.")";
										$bom_resul=$dbcon->query($bom);
										$bom_rel1=mysqli_fetch_assoc($bom_resul);
										
										$bom1="SELECT rpro.*,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
										left join product_mst as pro on pro.product_id=rpro.rp_pid
										left join unit_mst as bunit on bunit.unitid=rpro.process_unit
										left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
										WHERE rpro.status!=2 AND rpro.perent_id in (".$bom_rel1['views'].") group by rpro.rp_pid" ;
										$bom1_result=$dbcon->query($bom1);
										

										while($bom_rel=mysqli_fetch_assoc($bom1_result)){

											$o_qty=convert_stock($dbcon,$bom_rel["req_qty_one"],$bom_rel['rp_pid'],"base_unit");
											$bom_rel["req_qty_one"]=round($bom_rel["req_qty_one"],6);
											$o_qty=round($o_qty,6);

											$uqty=$o_qty*$sqty;
											$uqty=round($uqty,4);

											$info2['allocate_process_id']	=$j_alloc_process_id;
											$info2['product_id']			=$bom_rel['rp_pid'];
											$info2['unit_id']				=$bom_rel['process_unit'];
											$info2['used_qty']				=$uqty;
											$info2['cdate']					= date("Y-m-d H:i:s");
											$info2['user_id']				= $_SESSION['user_id'];
											$info2['company_id']			= $_SESSION['company_id'];
											
											
											$tbl_grn_trn_id111=add_record('tbl_allocate_process_material',$info2, $dbcon, $branch_id);
											
											$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id111,$info2['used_qty'],$branch_id);
											
											
											//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
											$request_id=find_request_id($dbcon,$a_row['p_ref_id'],$info2['product_id']);
											
											//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
											deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
										}
										
									//}
									}
									$qty=$qty-$sqty;
								}else{
									$sqty=$pending_qty;
									$jpqty=$sqty;

									$info6['process_id']		= $a_row['process_id'];
									$info6['p_start_time']		='';
									$info6['p_end_time']		= date("Y-m-d H:i:s");
									$info6['p_qty']				= $sqty;
									$info6['pen_qty']			= '';
									$info6['p_status']			= '2';
									$info6['p_ref_id']			= $a_row['p_ref_id'];
									$info6['p_product_id']		= $a_row['p_product_id'];
									$info6['j_alloc_process_id']= $a_row['p_id'];

									$info6['cdate']				= date("Y-m-d H:i:s");
									$info6['user_id']			= $_SESSION['user_id'];
									$info6['company_id']		= $_SESSION['company_id'];


									$inserusrid1=add_record('tbl_jobwork_history', $info6, $dbcon, $branch_id);

									add_process_stock_new($dbcon,$a_row['p_id'],$sqty,$sqty);


								//pathik start
								/* $infogtrn['product_id']			= $info2s['product_id'];
								$infogtrn['grn_trn_id']			= $tbl_grn_trn_id;
								$infogtrn['jobwork_id']			= $POST['j_job_work_id'][$m];
								$infogtrn['product_qty']		= $sqty;
								$infogtrn['cdate']				= date("Y-m-d H:i:s");
								$infogtrn['user_id']			= $_SESSION['user_id'];
								$infogtrn['company_id']			= $_SESSION['company_id'];
								$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon);
								
								close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$sqty); */
								
								$job_qty=$sqty;
								$jid=explode(",",$POST['j_job_work_id'][$m]);
								for($p=0;$p<count($jid);$p++)
								{
									$jobquery11="select sum(j_qty) as start_qty from tbl_jobwork as trn where status=0 and jobwork_id=".$jid[$p];
									$jobrel1=mysqli_fetch_assoc($dbcon->query($jobquery11));

									$jobquery12="select sum(product_qty) as end_qty from tbl_grn_sub_trn as trn where status=0 and jobwork_id=".$jid[$p];
									$jobrel2=mysqli_fetch_assoc($dbcon->query($jobquery12));
									$job_pending_qty=$jobrel1['start_qty']-$jobrel2['end_qty'];

									if($job_qty!=0){
										if($job_qty!=""){
											if($job_qty>=$job_pending_qty){
												$infogtrn['product_id']		= $info2s['product_id'];
												$infogtrn['grn_trn_id']		= $tbl_grn_trn_id;

												$infogtrn['jobwork_id']			= $jid[$p];
												$infogtrn['product_qty']		= $job_pending_qty;

												$infogtrn['cdate']				= date("Y-m-d H:i:s");
												$infogtrn['user_id']			= $_SESSION['user_id'];
												$infogtrn['company_id']			= $_SESSION['company_id'];

												$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon, $branch_id);

										//close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$infogtrn['product_qty']);

												$process_id=$re1['p_id'];
											/* $que_po="select * from tbl_allocate_process where p_id=".$a_row['p_id'];
											$resi_grn=$dbcon->query($que_po);
											$re=mysqli_fetch_assoc($resi_grn); */
											
											add_process_trn($dbcon,$a_row['p_id'],$a_row['p_ref_id'],$a_row['p_product_id'],$a_row['process_id'],$infogtrn['product_qty'],"1",$inserpoid);
											

											$j_que_po="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jid[$p]." and p_id=".$a_row['p_id']." having qty>used_qty";
											$j_process_qty=$infogtrn['product_qty'];
											$j_resi_grn=$dbcon->query($j_que_po);
											while($j_re=mysqli_fetch_assoc($j_resi_grn)){
												if($j_process_qty!=0){
													if($j_process_qty!=""){
														if($j_process_qty>=$j_re['pen_qty']){
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_re['pen_qty']." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_re['pen_qty'];
														}else{
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_process_qty." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_process_qty;
														}
													}
												}
											}
											$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$infogtrn['product_qty']." where jobwork_id=".$jid[$p]."");

											$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$infogtrn['product_qty']." where p_id=".$a_row['p_id']);
											
											$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
											where p_id=".$a_row['p_id'];
											$se=mysqli_fetch_assoc($dbcon->query($set11));
											$sss1=$se['start_qty']-$se['end_qty'];
											if($se['start_qty']<=$se['end_qty']){
												$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$a_row['p_id'];
												$dbcon->query($bb);
											}
											if($se['p_qty']==$se['end_qty']){
												$date=date("Y-m-d h:i:sa");
												$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$a_row['p_id']);
											}
											$job_qty=$job_qty-$job_pending_qty;
										}else{
											$infogtrn['product_id']		= $info2s['product_id'];
											$infogtrn['grn_trn_id']		= $tbl_grn_trn_id;
											
											$infogtrn['jobwork_id']			= $jid[$p];
											$infogtrn['product_qty']	= $job_qty;
											
											$infogtrn['cdate']				= date("Y-m-d H:i:s");
											$infogtrn['user_id']			= $_SESSION['user_id'];
											$infogtrn['company_id']			= $_SESSION['company_id'];

											$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon, $branch_id);
											
											
											//close_grn_to_process($dbcon,$inserpoid,$infogtrn['jobwork_id'],$infogtrn['product_qty']);
											
											$process_id=$re1['p_id'];
											/* $que_po="select * from tbl_allocate_process where p_id=".$a_row['p_id'];
											$resi_grn=$dbcon->query($que_po);
											$re=mysqli_fetch_assoc($resi_grn); */
											
											add_process_trn($dbcon,$a_row['p_id'],$a_row['p_ref_id'],$a_row['p_product_id'],$a_row['process_id'],$infogtrn['product_qty'],"1",$inserpoid);

											$j_que_po="select p_id,qty,used_qty,(qty-used_qty) as pen_qty,jobwork_process_id from tbl_jobwork_process where jobwork_id=".$jid[$p]." and p_id=".$a_row['p_id']." having qty>used_qty";
											$j_process_qty=$infogtrn['product_qty'];
											$j_resi_grn=$dbcon->query($j_que_po);
											while($j_re=mysqli_fetch_assoc($j_resi_grn)){
												if($j_process_qty!=0){
													if($j_process_qty!=""){
														if($j_process_qty>=$j_re['pen_qty']){
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_re['pen_qty']." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_re['pen_qty'];
														}else{
															$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$j_process_qty." where jobwork_process_id=".$j_re['jobwork_process_id']."");
															$j_process_qty=$j_process_qty-$j_process_qty;
														}
													}
												}
											}
											$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$infogtrn['product_qty']." where jobwork_id=".$jid[$p]."");

											$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$infogtrn['product_qty']." where p_id=".$a_row['p_id']);
											
											$set11="select p_qty,(select sum(pt_qty) end_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=1) as end_qty,(select sum(pt_qty) start_qty1 from tbl_allocate_process_trn as alo_trn where alo_trn.pt_alloc_id=alo.p_id and p_status=0) as start_qty from tbl_allocate_process as alo
											where p_id=".$a_row['p_id'];
											$se=mysqli_fetch_assoc($dbcon->query($set11));
											$sss1=$se['start_qty']-$se['end_qty'];
											if($se['start_qty']<=$se['end_qty']){
												$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$a_row['p_id'];
												$dbcon->query($bb);
											}
											if($se['p_qty']==$se['end_qty']){
												$date=date("Y-m-d h:i:sa");
												$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$a_row['p_id']);
											}
											$job_qty=$job_qty-$job_qty;
										}
									}
								}
							}


							if($product_qc==1){	
								$process=get_next_process($dbcon,$a_row['process_id'],$info2s['product_id'],$a_row['p_ref_id'],$a_row['process_priority']);

								$process_pr=json_decode($process);

								$process_id_new=$process_pr->process_id;
								$process_type=$process_pr->process_type;
								$process_priority=$process_pr->process_priority;

								if($process_id_new==0){
									if($godown_id!=""){
										add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$sqty,"1",$branch_id);

										add_request_reserve_stock($dbcon,$a_row['p_ref_id'],$sqty,$info2s['unit_id'],$branch_id);

									}
								}else{
									process_allocate($dbcon,$a_row['p_id'],$process_id_new,$sqty,$a_row['p_ref_id'],"tbl_grn_trn",$info2s['product_id'],$process_type,$info2s['unit_id'],$process_priority,"",$branch_id);

								}
								add_process_stock($dbcon,$a_row['p_id'],$sqty,0,$process_id_new);
								
							}

								//pathik close

							if($a_row['previous_process_id']=="0"){
									/*$grn_qty=$POST['row_product_id'];
									for($k=0;$k<count($grn_qty);$k++)
									{*/

										$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
										WHERE rpro.p_status!=2 AND rpro.p_id in (".$j_alloc_process_id.")";
										$bom_resul=$dbcon->query($bom);
										$bom_rel1=mysqli_fetch_assoc($bom_resul);
										
										$bom1="SELECT rpro.*,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
										left join product_mst as pro on pro.product_id=rpro.rp_pid
										left join unit_mst as bunit on bunit.unitid=rpro.process_unit
										left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
										WHERE rpro.status!=2 AND rpro.perent_id in (".$bom_rel1['views'].") group by rpro.rp_pid" ;
										$bom1_result=$dbcon->query($bom1);
										$i=1;

										while($bom_rel=mysqli_fetch_assoc($bom1_result)){

											$o_qty=convert_stock($dbcon,$bom_rel["req_qty_one"],$bom_rel['rp_pid'],"base_unit");
											$bom_rel["req_qty_one"]=round($bom_rel["req_qty_one"],6);
											$o_qty=round($o_qty,6);


											$uqty=$o_qty*$sqty;
											$uqty=round($uqty,4);

											$info2['allocate_process_id']	=$eid;
											$info2['product_id']			=$bom_rel['rp_pid'];
											$info2['unit_id']				=$bom_rel['process_unit'];

											$info2['used_qty']				=$uqty;
											$info2['cdate']					= date("Y-m-d H:i:s");
											$info2['user_id']				= $_SESSION['user_id'];
											$info2['company_id']			= $_SESSION['company_id'];
											

											$tbl_grn_trn_idq=add_record('tbl_allocate_process_material',$info2, $dbcon, $branch_id);
											
											$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_idq,$info2['used_qty'],$branch_id);
											
											//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
											$request_id=find_request_id($dbcon,$a_row['p_ref_id'],$info2['product_id']);
											
											//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
											deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
										}
									//}
									}
									$qty=$qty-$sqty;
								}
							}
						}

					}
				}
			}
		}
		else if(strtolower($POST['mode']) == "edit") {

			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['gir_no']				= $POST['gir_no'];
			$info['invoice_no']			= $POST['invoice_no'];
			$info['challan_no']			= $POST['challan_no'];
			$info['remark']				= $_POST['remark']; 

			$info['cdate']			= date("Y-m-d H:i:s"); 
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id']; 
			$updateid=update_record('tbl_grn', $info,"grn_id=".$POST['eid'] , $dbcon);

			$grn_qty=$POST['grn_qty'];
			
			for($k=0;$k<count($grn_qty);$k++)
			{
				$loop_id=$grn_qty[$k];
				$qc_st=$POST['qc_status'][$k];
				
				if(strtolower($POST['qc_type'][$k])=="no"){
					$godown_id=$POST['grn_godown'][$k];
					$product_qc=1;
				}else{
					$godown_id="";
					$product_qc=0;
				}
				
				$info2['product_qty']		=$POST['grn_qty'][$k];
				$info2['unit_id']			=$POST['unit_id'][$k];
				$info2['grn_godown']		=$godown_id;
				//$info2['product_qc']		=$product_qc;
				
				$info2['cdate']				= date("Y-m-d H:i:s");
				$info2['user_id']			= $_SESSION['user_id'];
				$info2['company_id']		= $_SESSION['company_id'];
				//var_dump($info2);
				//$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2, $dbcon);
				if($qc_st!=1){
					$updateid1=update_record('tbl_grn_trn', $info2,"grn_trn_id=".$POST['grn_trn_id'][$k] , $dbcon);
				}
				if($POST['grn_against']==1){
					close_grn_to_process($dbcon,$POST['eid'],$POST['purchaseorder_id'],$info2['product_qty']);
				}

			}

			$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 1);

			if(!empty($_FILES['grn_file']['tmp_name'][0])) {
				$imgresp = upload_grn_receipt($_FILES,$dbcon,$POST['eid']);
			}

			if($updateid){	
				$arr['msg']="1";		
			//Insert LOG
				$log_entry=common_log_entry($dbcon,"grn_add",2,"tbl_grn",$POST['eid']);						
			}
			else{
				$arr['msg']="0";
			}
			$arr['back']=$POST['back'];
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "fieldadd") {

			$info1['purchaseorder_id']	= $POST['purchaseorder_id'];
			$info1['pro_entry_date']	= date('Y-m-d',strtotime($POST['pro_entry_date']));
			$info1['pro_mfg_date']		= date('Y-m-d',strtotime($POST['pro_mfg_date']));
			$info1['pro_exp_date']		= date('Y-m-d',strtotime($POST['pro_exp_date']));
			$info1['product_id']		= $POST['product_id'];
			$info1['description']		= $_POST['product_des'];
			$info1['product_qty']		= $POST['product_qty'];
			$info1['unit_id']			= $POST['unit_id']; 
			$info1['branch_id']			= $POST['branch_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['product_rate']		= $POST['product_rate'];
			$info1['formulaid']			= $POST['formulaid'];
			$info1['product_amount']	= $total=($POST['product_rate']*$POST['product_qty']);
			$info1['product_qc']		= $POST['product_qc'];
		//$info=get_product_tax_common($dbcon,$total,$POST['formulaid']);
		//$info1=array_merge($info1,$info);
			$table='tbl_grn_trn';$tableid='grn_trn_id';
			if(!empty($POST['grn_id'])) {
				$info1['grn_id']= $POST['grn_id'];
			}
			else{
				$info1['grn_trn_status']= 3;
			}

			if(empty($POST['edit_id'])) {
				$inserid=add_record($table, $info1, $dbcon);
			}
			else {
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}

		//var_dump($info1);
		}
		else if(strtolower($POST['mode'])== "load_grn_trn_data") {

			if($POST['grn_id']){
				$query="select mst.*,pro.product_name,cat.unit_name from tbl_grn_trn as mst
				left join product_mst as pro on pro.product_id=mst.product_id
				left join unit_mst as cat on cat.unitid=mst.unit_id  
				where grn_trn_status=0 and mst.grn_id=".$POST['grn_id'];
			}
			else{
				$query="select mst.*,pro.product_name,cat.unit_name from tbl_grn_trn as mst
				left join product_mst as pro on pro.product_id=mst.product_id
				left join unit_mst as cat on cat.unitid=mst.unit_id 
				where grn_trn_status=3 and mst.user_id=".$_SESSION['user_id'];
			}
			$result=$dbcon->query($query);
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					$pro_exp_date='';$pro_mfg_date='';
					if($rel['pro_mfg_date']!="1970-01-01" && $rel['pro_mfg_date']!="0000-00-00") {
						$pro_mfg_date=date('d-m-Y',strtotime($rel['pro_mfg_date']));
					}
					if($rel['pro_exp_date']!="1970-01-01" && $rel['pro_exp_date']!="0000-00-00") {
						$pro_exp_date=date('d-m-Y',strtotime($rel['pro_exp_date']));
					}
					echo '<tr> 
					<td style="vertical-align:top;">
					'.$rel['product_name'].'
					'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.nl2br($rel['description']):'').'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$pro_mfg_date.'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$pro_exp_date.'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$rel['product_qty'].'
					</td>
					<td style="vertical-align:top" class="text-center">
					'.$rel['unit_name'].'
					</td>
					<td style="vertical-align:top"> 
					<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_grn_data('.$rel['grn_trn_id'].')"><i class="fa fa-pencil"></i></button>
					<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_grn_data('.$rel['grn_trn_id'].','.$rel['purchaseorder_id'].')">X</button>
					</td>	
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
		}
		else if(strtolower($POST['mode'])== "preedit") {
			$q = $dbcon -> query("SELECT mst.* FROM tbl_grn_trn as mst WHERE grn_trn_id = '$POST[grn_trn_id]'");
			$r = $q->fetch_assoc();
			$r['pro_entry_date'] = date('d-m-Y',strtotime($r['pro_entry_date']));
			if($r['pro_mfg_date']!="1970-01-01" && $r['pro_mfg_date']!="0000-00-00"){
				$r['pro_mfg_date'] = date('d-m-Y',strtotime($r['pro_mfg_date']));
			}
			else{
				$r['pro_mfg_date']='';
			}
			if($r['pro_exp_date']!="1970-01-01" && $r['pro_exp_date']!="0000-00-00"){
				$r['pro_exp_date'] = date('d-m-Y',strtotime($r['pro_exp_date']));
			}
			else{
				$r['pro_exp_date'] = '';
			}

		//$r['po_html_resp']=get_po_for_grn($dbcon,$r['purchaseorder_id'],'Edit');
			$r['pro_html_resp']=get_po_for_grn_trn($dbcon,$r['purchaseorder_id'],$r['product_id'],'Edit');

			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data") {
			$row=array();
			$info['grn_trn_status']=2;	
			$updateid=update_record('tbl_grn_trn', $info, "grn_trn_id=".$POST['grn_trn_id'] , $dbcon);

		//$UPD_PO=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 2);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_grn") {
			$row=array();
			$info['grn_status']=2;
			$updateid=update_record('tbl_grn', $info, "grn_id=".$POST['grn_id'] , $dbcon);

			$upd_po_sts=upd_grn_used_status($dbcon, $POST['purchaseorder_id'], 2);

		//Insert LOG
			$log_entry=common_log_entry($dbcon,"grn_add",3,"tbl_grn",$POST['grn_id']);	

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		} 
		else if(strtolower($POST['mode'])== "load_purhcase_order_data") {

			$id=$POST['order_id'];
			$grn_type=$POST['grn_type'];

			if($grn_type==2)
			{
			//$resp['pro_html'] = get_po_details_for_grn_trn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
				$resp['pro_html'] = purchase_order_product_for_pending_grn($dbcon,$id,$grn_type,$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['branch_id']);
				$resp['request_id'] ='';
			}
			else
			{
				$resp['pro_html'] = job_work_product_for_pending_grn($dbcon,$POST['vender_id'],$POST['order_id']);

			//$resp['pro_html'] = get_jobwork_details_for_grn_trn($dbcon,$id,'',$POST['mode1'],$POST['eid'],$POST['vender_id'],$POST['order_id']);
			//$resp['request_id'] = get_request_id_jobwork($dbcon,$id);
				$resp['request_id'] = "";
			}

			$vendor_id=get_vender_id($dbcon,$id,$grn_type);
			$resp['vendor_id'] = $vendor_id;
			$resp['vendor_name'] = get_vender_name($dbcon,$vendor_id,$grn_type);

			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_po_ven_wise") {
			$resp['pro_html'] = get_po_for_grn($dbcon,'',$POST['vender_id'],'Add');
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_productdetail") {
			$purchaseorder_id=$POST['purchaseorder_id'];
			$product_id=$POST['product_id'];
			$query="select trn.*,main_grn_qty from tbl_purchaseordertrn as trn
			left join (SELECT purchaseorder_id,product_id,sum(product_qty) as main_grn_qty FROM tbl_grn_trn as chtrn where chtrn.grn_trn_status!=2 and chtrn.purchaseorder_id=".$purchaseorder_id." group by chtrn.product_id,chtrn.purchaseorder_id) as chtrn on chtrn.product_id=trn.product_id
			where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=".$purchaseorder_id." and trn.product_id=".$product_id;
			$rel=mysqli_fetch_assoc($dbcon->query($query));
			$rel['pending_qty']=floatval($rel['product_qty'])-floatval($rel['main_grn_qty']);

			$product_qc=explode(",",get_pro_field($dbcon,$product_id,'product_setting_check'));
			if(in_array("product_qc",$product_qc))
			{
				$rel['product_qc']=0;
			}
			else
			{
				$rel['product_qc']=1;
			}


			echo json_encode($rel);
		}
		else if(strtolower($POST['mode'])== "load_grn_no") {
			$row=array();
			$query1="select * from tbl_invoicetype where type_id='8' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			if($rows['invoice_format']=='2') {
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1') {
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_attch") {
			$row=array();
			$info['grn_attch_status']=2;	
			$updateid=update_record('tbl_grn_attch', $info, "grn_attch_id=".$POST['grn_attch_id'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_order_no") {

			$grn_type=$POST['grn_type'];
			$vender_id=$POST['vender_id'];

			if($grn_type==2)
			{
				$row=get_all_po_for_grn($dbcon,$rel['purchaseorder_id'],$vender_id);
			}
			else
			{
				$row=get_all_jobwork_for_grn($dbcon,$rel['purchaseorder_id']);
			}
			echo $row;
		}
		else if(strtolower($POST['mode'])== "convert_qty")
		{
			$row=array();
			if($POST["type"]=="1"){

				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="conv_unit";
				$ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
			}else{
				$ret_qty="0";
			}
				//var_dump($ret_qty);
			$ret_qty_new=number_format($ret_qty, 4, ".", "");
				//$ret_qty=$ret_qty;
			//	echo $ret_qty;
			$row['show_qty']=$ret_qty_new;
			$row['hide_qty']=$ret_qty;
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "batch_model_open")
		{
			$product_id = $POST['product_id'];	
			$main_pending_qty = $POST['main_pending_qty'];			
			$query="select * from product_mst where product_id=".$product_id;
			
			$rel=mysqli_fetch_assoc($dbcon->query($query));
			if(empty($POST['trn_id'])){
				
				echo '<input type="hidden" name="count" id="count" value="1" />
				<input type="hidden" name="count" id="count" value="1" />
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_batch_table">
				<tr id="field">			
				<th width="25%"  class="text-center" style="vertical-align:center;">Batch No</th>
				<th width="25%"  class="text-center;" style="vertical-align:center;">Qty</th>
				<th width="25%"  class="text-center;" style="vertical-align:center;">Mfg Date</th>
				<th width="25%"  class="text-center;" style="vertical-align:center;">Exp Date</th>			
				</tr>
				<tr id="field1">		
				<td   class="text-center" style="vertical-align:center;">
				<input type="text" class="form-control batch_no " id="batchno1" name="batch_no[]" placeholder="Batch No" >
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="text" class="form-control qty batch_qty" id="qty1" name="batch_qty[]" placeholder="'.$POST["qty"].'"  onchange="validate_batch_data();"/>
				</td>
				<td   class="text-center" style="vertical-align:center;">
				<input type="text" class="form-control default-date-picker valid mfg_date" id="mfgdate1" name="mfg_date[]" placeholder="Mfg date" onchange="get_exp_date(1);" >
				</td>
				<td   class="text-center" style="vertical-align:center;">
				<input type="text" class="form-control exp_date" id="expdate1" name="exp_date[]" placeholder="Exp date" readonly>
				</td>
				</tr>
				</table>';
			}else{
				$qry="SELECT * FROM `tbl_purchaseorder_delivery_date` WHERE po_delivery_date_status=0 and purchaseordertrn_id=".$POST['trn_id']." order by po_delivery_date_id";
				$row=$dbcon->query($qry);
				$cnt=brp_mysqli_num_rows($row);
				if($cnt>0){
					$i=1;
					echo '<input type="hidden" name="count" id="count" value="'.$cnt.'" />
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
					<tr id="field">
					<th width="30%"  class="text-center" style="vertical-align:center;">Date</th>
					<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
					<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
					</tr>';
					
					while($tax=brp_mysqli_fetch_assoc($row))
					{
						$date=date('d-m-Y',strtotime($tax['delivery_date']));
						echo '<tr id="field'.$i.'">
											
						<td   class="text-center" style="vertical-align:center;">
						<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date'.$i.'" name="delivery_date[]" placeholder="Delivery Date" value="'.$date.'" onkeyup="qty_wise_date_validation('.$i.');" >
						</td>
						<td	 class="text-center;" style="vertical-align:center;">
						<input type="text" class="form-control delivery_qty" id="delivery_qty'.$i.'" name="delivery_qty[]" placeholder="'.$tax["product_qty"].'" value="'.$tax["product_qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation('.$i.');" />
						</td>
						<td	 class="text-center;" style="vertical-align:center;">
						<input type="hidden" name="arry_sr[]" id="arry_sr'.$i.'" value="'.$i.'" />
						<input type="hidden" class="arry_edit" name="arry_edit[]" id="arry_edit'.$i.'" value="'.$tax["po_delivery_date_id"].'" />';
						if($i!=1){
							echo '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date('.$i.');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>';
						}
						echo '</td>
						</tr>';
						$i++;
					}
					echo '</table>';
				}else{
					echo '<input type="hidden" name="count" id="count" value="1" />
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
					<tr id="field">
					<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
					<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
					<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
					</tr>
					<tr id="field1">
					<td class="text-center" style="vertical-align:center;">
					<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="'.$POST["qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
					</td>
					</tr>
					</table>';
				}
			}
		}
		else if(strtolower($POST['mode'])== "get_exp_date_by_product")
		{
			$product_id = $_POST['product_id'];
			$mfgdate =  $_POST['mfgdate'];
			$get_dt_qry="select * from product_mst where product_id = '$product_id'";
			$getproduct_res=$dbcon->query($get_dt_qry);
			$getproduct_row=mysqli_fetch_assoc($getproduct_res);
			if($getproduct_row['self_life_days'] != '')
			{
				$exp_days = $getproduct_row['self_life_days'];
				$mfg_date = date("Y-m-d",strtotime($mfgdate));			
				echo $date = date("Y-m-d",strtotime("+".$exp_days." days", strtotime($mfg_date)));
							
			}
			else{
				echo   "0";
			}
		}
		else if(strtolower($POST['mode']) == "save_batch_data") 
		{
			$batch_no_arr = $POST['batch_no_arr'];
			$batch_qty_arr = $POST['batch_qty_arr'];
			$mfg_date_arr = $POST['mfg_date_arr'];
			$exp_date_arr = $POST['exp_date_arr'];
			for($i=0; $i<count($POST['batch_no_arr']);$i++)
			{
				
			$info['batch_no']			= $batch_no_arr[$i];
			$info['batch_qty']			= $batch_qty_arr[$i];
			$info['mfg_date']			= $mfg_date_arr[$i];
			$info['exp_date']			= $exp_date_arr[$i];
			$info['grn_no']				= $_POST['grn_no'];
			$info['grn_no']				= $POST['grn_no'];
			$info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$info['status']				= '3';			
			$info['cdate']				= date("Y-m-d H:i:s"); 
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			$grn_id=add_record('tbl_grn_batch', $info, $dbcon, $branch_id);
			
			}
		echo "true";
	}
		
		
		
			//pathik end	

		function upd_grn_used_status($dbcon,$purchaseorder_id,$flag){
			if($flag=='1'){
		//get Same Qty Data
				$get_dt_qry="SELECT SUM(potrn.product_qty) as po_qty,(SELECT SUM(grntrn.product_qty) FROM `tbl_grn_trn` as grntrn where grntrn.grn_trn_status=0 and grntrn.purchaseorder_id=".$purchaseorder_id." and grntrn.product_id=potrn.product_id) as grn_qty FROM `tbl_purchaseordertrn` as potrn where potrn.purchaseordertrn_status=0 and potrn.purchaseorder_id=".$purchaseorder_id." group by potrn.product_id";
				$get_dt_rs=$dbcon->query($get_dt_qry);
				$same_qty=true;
				while($get_dt_rel=mysqli_fetch_assoc($get_dt_rs)){
			//compare pending qty
					if($get_dt_rel['po_qty']!=$get_dt_rel['grn_qty']){
						$same_qty=false;
					}
				}
			}

	//update PO if all used in GRN
			if($same_qty){
				$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_grn_status=1 where purchaseorder_id=".$purchaseorder_id);
			}
			else{
				$upd_po_qry=$dbcon->query("update tbl_purchaseorder set used_grn_status=0 where purchaseorder_id=".$purchaseorder_id);
			}
		}

		function upload_grn_receipt($FILES,$dbcon,$grn_id){
			$cnt=count($_FILES['grn_file']['name']);
			for( $i=0 ; $i < $cnt ; $i++ ) {
				if(!empty($_FILES['grn_file']['tmp_name'][$i])) {
					$rand=rand(0,999999);
					$temp = explode(".", $_FILES["grn_file"]["name"][$i]);
					$extension = strtolower(end($temp));
					$file_name = $_FILES['grn_file']['name'][$i];
					$err = $_FILES["grn_file"]["tmp_name"][$i];
					$file_name = "grn_rec_".$rand.'.'.$extension;
					move_uploaded_file($err,RECEIPT_FILE_UPING.$file_name);

					$attch['grn_id']		= $grn_id;
					$attch['grn_file']		= $file_name;
					$attch['cdate']			= date("Y-m-d H:i:s"); 
					$attch['user_id']		= $_SESSION['user_id'];
					$attch['company_id']	= $_SESSION['company_id']; 
					$inserid=add_record('tbl_grn_attch', $attch, $dbcon);
			//return 	$file_name;
				}
			}
		}

		function get_product_tax($dbcon,$product_amount,$formulaid)
		{
			$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
			$row=$dbcon->query($qry);
			$rate_total=$total=$product_amount;
			$i=1;
			while($tax=mysqli_fetch_assoc($row))
			{
				$info['tax_name'.$i]=$tax['tax_name'];
				$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
				$rate_total+=$tax_amount;
				$i++;
			}
			for($j=$i;$j<=3;$j++)
			{
				$info['tax_name'.$i]='';
				$info['tax_amount'.$i]='';		
			}
			$info['total']=$rate_total;
			return $info;
		}
	?>