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

if(brp_strtolower($POST['mode']) == "fetch") {
	$jobwork_status = $POST['jobwork_status'];
	$vender_id = $POST['vender_id'];
	
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$where_db = check_branch('ap', $branch_id);
	
	$where='';
	
			/* if($jobwork_status!=''){
				$where .= ' and ap.p_status IN ('.$jobwork_status.')  ';
			} */
			/*if($vender_id!=''){
				
			}*/
			
			$where .= ' and ap.p_status IN (0,1)';
			$appData = array();
			$i=1;
			$aColumns = array('p.product_type', 'p.product_name', 'tc.cat_name' , 'pro.process_name','branc.branch_name', 'sum(ap.p_qty) as ap_qty', 'sum(ap.pen_qty) as apen_qty', 'IFNULL(end_qty,0) as end_qty', 'IFNULL(strtt_qty,0) as strtt_qty', 'GROUP_CONCAT(ap.p_id ORDER BY `ap`.`p_id` ASC) as allocate_id','ap.*');
			$sIndexColumn = "ap.p_id";
			$isWhere = array("ap.pr_process_type='2' and ap.p_status in(0,1) and ap.company_id=".$_SESSION['company_id']." ".$where." ".$where_db."");
			$sTable = "tbl_allocate_process as ap";			
			$isJOIN = array('left join product_mst as p on p.product_id=ap.p_product_id left join tbl_category as tc on p.product_category=tc.cat_id left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0) as apta1 on apta1.pt_alloc_id=ap.p_id','left join process_mst as pro on ap.process_id=pro.process_id','left join branch_mst as branc on branc.branch_id=ap.branch_id');
			$hOrder = " ap.p_id asc";
			$hGroupby = array(" ap.p_product_id, ap.process_id,ap.branch_id");
			include($include.'pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			
			foreach($sqlReturn as $rel) {
				$allocate_id = $rel['allocate_id'];
				$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				$start_qty_data = "SELECT IFNULL(sum(pt_qty),0) as start_qty_valua FROM `tbl_allocate_process_trn` where p_status = 0 and pt_alloc_id IN (".$allocate_id.") ";
				$start_result=$dbcon->query($start_qty_data);
				$start_qty_result = brp_mysqli_fetch_assoc($start_result);
				$total_start_qty = $start_qty_result['start_qty_valua'];

				$finish_qty_data = "SELECT sum(pt_qty) as start_qty_valua FROM `tbl_allocate_process_trn` where p_status = 1 and pt_alloc_id IN (".$allocate_id.") ";
				$finish_result=$dbcon->query($finish_qty_data);
				$finish_qty_result = brp_mysqli_fetch_assoc($finish_result);
				$total_finsih_qty = $finish_qty_result['start_qty_valua'];

				$current_start_qty = $total_start_qty - $total_finsih_qty;

				$row_data = array();

				$get_party_info = "select GROUP_CONCAT(`pjpp`.`job_party_rate`) job_party_rate, GROUP_CONCAT(`l`.`l_name`) l_name, pjpp.job_party_id from tbl_product_job_party_purchase as pjpp left join tbl_ledger as l on l.l_id=pjpp.job_party_id where pjpp.job_party_process_id='".$rel['process_id']."' and pjpp.job_party_product='".$rel['p_product_id']."' ";
				$result=$dbcon->query($get_party_info);
				$row_data=brp_mysqli_fetch_assoc($result);



				/* If user slect the total pending and done jobwork qty filter option */
				/*if($jobwork_status=='0,1'){
					$row_data[] = $id;
					$row_data[] = $rel['product_name'];
					$row_data[] = $rel['process_name'];
					if($rel['jobwork_vendor_name']!=''){
						$row_data[] = $rel['jobwork_vendor_name'];
					}else{
						$row_data[] = $row_data['l_name'];
					}
					$row_data[] = $rel['ap_qty'];
					$row_data[] = $rel['apen_qty'];
					$row_data[] = $rel['strtt_qty'];
					
					$btn_print = '';$btn_history='';
					
					//$btn_history='<a class="btn btn-xs btn-info" data-original-title="Process History" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'process_history/'.$rel['p_id'].'" target="_blank"><i class="fa fa-building"></i></a>';

					$row_data[] =  $btn_print.' '.$btn_history;

					$appData[] = $row_data;
					$id++;
				}*/

				/* If user slect the working qty filter option */
				//if($jobwork_status=='0,1'){

				$process_id          = $rel['process_id'];
				$process_type        = $rel['pr_process_type'];
				$p_product_id 		 = $rel['p_product_id'];
				$p_status 			 = $rel['p_status'];
				$previous_process_id = $rel['previous_process_id'];

				$req_working_qty = $rel['apen_qty']-$current_start_qty;

					//$min_working_qty = working_qty_avalable($dbcon, $process_id, $process_type, $p_product_id, $p_status, $previous_process_id);

					
				
				$min_working_qty=production_start_count_using_p_id($dbcon,$allocate_id);
					//var_dump($min_working_qty);
					//if($req_working_qty > 0)

				if($jobwork_status=="working_qty"){
					if($min_working_qty > 0)
					{
						$row_data[] = $id;
						$row_data[] = $rel['product_name'];
						$row_data[] =$cat_name;;
						$row_data[] = $rel['process_name'];
						if($rel['jobwork_vendor_name']!=''){
							$row_data[] = $rel['jobwork_vendor_name'];
						}else{
							$row_data[] = $row_data['l_name'];
						}
						$row_data[] = $rel['ap_qty'];
						$row_data[] = $min_working_qty;
						$row_data[] = $total_start_qty;
						if($_SESSION['branch_id']==0){ 
							$row_data[] = $rel['branch_name'];
						}
						if(!empty($row_data['job_party_id'])){
							$jjparty=$row_data['job_party_id'];
						}else{
							$jjparty=0;
						}
						if($min_working_qty > 0)
						{
							$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create Jobwork" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'create_job_work/'.$rel['process_id'].'/'.$rel['p_product_id'].'/'.$jjparty.'/'.$rel['branch_id'].'"><i class="fa fa-plus"></i></a>';
						}else{
							$add_po_btn='';
						}	
						$row_data[] = $add_po_btn;
						$appData[] = $row_data;
						$id++;
					}
				}else{
					
					$row_data[] = $id;
					$row_data[] = $rel['product_name'];
					$row_data[] = $rel['process_name'];
					if($rel['jobwork_vendor_name']!=''){
						$row_data[] = $rel['jobwork_vendor_name'];
					}else{
						$row_data[] = $row_data['l_name'];
					}
					$row_data[] = $rel['ap_qty'];
					$row_data[] = $min_working_qty;
					$row_data[] = $total_start_qty;
					$row_data[] = $rel['branch_name'];
					if(!empty($row_data['job_party_id'])){
						$jjparty=$row_data['job_party_id'];
					}else{
						$jjparty=0;
					}
					if($min_working_qty > 0)
					{
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create Jobwork" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'create_job_work/'.$rel['process_id'].'/'.$rel['p_product_id'].'/'.$jjparty.'/'.$rel['branch_id'].'"><i class="fa fa-plus"></i></a>';
					}else{
						$add_po_btn='';
					}	
					$row_data[] = $add_po_btn;
					$appData[] = $row_data;
					$id++;
				}
				

						//$btn_history='<a class="btn btn-xs btn-info" data-original-title="Process History" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'process_history/'.$rel['p_id'].'" target="_blank"><i class="fa fa-building"></i></a>';
				
				
				//}

			}
			$output['aaData'] = $appData;
			echo brp_json_encode( $output );
		}
		if(brp_strtolower($POST['mode']) == "add") {
			
			$branch_id=$POST['branch_id'];
			
			$vendor_rate = $POST['vendor_rate'];

			$info_jobwork['job_work_type']		= "2";
			$info_jobwork['job_work_no']		= load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
			$info_jobwork['job_work_date']		= date('Y-m-d',strtotime($_POST['jobwork_date']));
			$info_jobwork['vender_id']			= $POST['vender_id'];
			$info_jobwork['vehicle_no']			= $POST['vehicle_no'];
			$info_jobwork['remark']				= $POST['remark'];
			$info_jobwork['purchaseordertrn_id'] = $POST['purchase_id'];
			
			$info_jobwork['cdate']				= date("Y-m-d H:i:s");
			$info_jobwork['user_id']			= $_SESSION['user_id'];
			$info_jobwork['company_id']			= $_SESSION['company_id'];
			
			$job_work_id=add_record('tbl_job_work',$info_jobwork, $dbcon,$branch_id);
			if($job_work_id){
				update_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
			}
			foreach ($POST['p_product_id'] as $pkey => $pvalue) {
				$start_qty=$POST['available_qty'][$pkey];
				
				$info_jobwork_trn['job_work_id']			= $job_work_id;
				$info_jobwork_trn['process_id']				= $POST['p_process_id'][$pkey];
				$info_jobwork_trn['product_id']				= $POST['p_product_id'][$pkey];
				$info_jobwork_trn['product_base_qty']		= $start_qty;
				$info_jobwork_trn['product_base_unit']		= $POST['process_unit'][$pkey];
				$info_jobwork_trn['product_con_qty']		= $start_qty;
				$info_jobwork_trn['product_con_unit']		= $POST['process_unit'][$pkey];
				$info_jobwork_trn['remark']					= $POST['remark'];
				$info_jobwork_trn['purchaseordertrn_id'] = $POST['purchase_id'];
				$info_jobwork_trn['pr_rate']				= $vendor_rate[$pkey];
				
				$info_jobwork_trn['cdate']						= date("Y-m-d H:i:s");
				$info_jobwork_trn['user_id']					= $_SESSION['user_id'];
				$info_jobwork_trn['company_id']					= $_SESSION['company_id'];
				
				$job_work_trn_id=add_record('tbl_job_work_trn',$info_jobwork_trn, $dbcon,$branch_id);
				
				$query="select p_id,p_qty,start_qty,p_ref_id from tbl_allocate_process where p_id in (".$POST['p_id'][$pkey].")";
				$result=$dbcon->query($query);
				$cnt=brp_mysqli_num_rows($result);
				if($cnt){
					$allocate_process_qty=0;
					while($row=brp_mysqli_fetch_assoc($result)){
						$allocate_process_qty=($row['p_qty']-$row['start_qty']);
						$working_qty=production_start_count_using_p_id($dbcon,$row['p_id']);
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
								$info_allocate['task_status']	= 1;
								$updatetrnid=update_record('tbl_allocate_process',$info_allocate,"p_id=".$row['p_id'] , $dbcon);
								
								//location common_functions 
								add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$info_jobwork_trn['product_id'],$info_jobwork_trn['process_id'],$used_qty,"0");
								
								$info_jobwork_sub_trn['job_work_trn_id']	= $job_work_trn_id;
								$info_jobwork_sub_trn['product_id']			= $info_jobwork_trn['product_id'];
								$info_jobwork_sub_trn['product_base_qty']	= $used_qty;
								$info_jobwork_sub_trn['product_base_unit']	= $info_jobwork_trn['product_base_unit'];
								$info_jobwork_sub_trn['product_con_qty']	= $used_qty;
								$info_jobwork_sub_trn['product_con_unit']	= $info_jobwork_trn['product_con_unit'];
								$info_jobwork_sub_trn['p_id']				= $row['p_id'];
								$info_jobwork_sub_trn['rp_id']				= $row['p_ref_id'];
								$info_jobwork_sub_trn['purchaseordertrn_id'] = $POST['purchase_id'];
								$info_jobwork_sub_trn['pr_rate']				= $vendor_rate[$pkey];
								
								$info_jobwork_sub_trn['cdate']				= date("Y-m-d H:i:s");
								$info_jobwork_sub_trn['user_id']			= $_SESSION['user_id'];
								$info_jobwork_sub_trn['company_id']			= $_SESSION['company_id'];
								
								$job_work_sub_trn_id=add_record('tbl_job_work_sub_trn',$info_jobwork_sub_trn, $dbcon,$branch_id);
								
								$info_job_up['product_version']	= $row['product_version'];
								$updatetrn1id=update_record('tbl_job_work_trn',$info_job_up,"job_work_trn_id=".$job_work_trn_id , $dbcon);
								
								$start_qty=$start_qty-$used_qty;
							}
							
						}
					}
				}
			}
			if($job_work_id){
				$arr['msg'] = '1';
			}else{
				$arr['msg'] = '0';
			}

			echo brp_json_encode($arr);
			
		}else if(brp_strtolower($POST['mode']) == "fetch_done") {
			$jobwork_status = $POST['jobwork_status'];
			$vender_id = $POST['vender_id'];
			
			$where='';
			
			if($vender_id!=''){
				$where .= ' and jobmain.vender_id="'.$vender_id.'"  ';
			}
			$appData = array();
			$i=1;
			$aColumns = array('jobmain.job_work_no','jobmain.job_work_date' ,'l.l_name','jobmain.vehicle_no','jobmain.job_work_id');
			$sIndexColumn = "jobmain.job_work_id";
			$isWhere = array("job_work_status='0' ".$where." ");
			$sTable = "tbl_job_work as jobmain";			
			$isJOIN = array('left join tbl_ledger as l on l.l_id=jobmain.vender_id');
			$hOrder = " jobmain.job_work_id asc";
			$hGroupby = '';
			include($include.'pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			//print_r($sqlReturn);
			
			foreach($sqlReturn as $rel) {
				$row_data = array();

				$row_data[] = $id;
				$row_data[] = $rel['job_work_no'];
				$row_data[] = $rel['job_work_date'];
				$row_data[] = $rel['l_name'];
				$row_data[] = $rel['vehicle_no'];
				
				$add_po_btn = '';$btn_print='';

				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 10 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$btn_print='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$rel['job_work_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>&nbsp;';
					}
				}
				
				// $btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Jobwork Details" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRINT_ROOT.'jobworkreceipt/'.$rel['job_work_id'].'" target="_blank"><i class="fa fa-print"></i></a>';
				
				$row_data[] = $btn_print;

				$appData[] = $row_data;
				$id++;
				

			}
			$output['aaData'] = $appData;
			echo brp_json_encode( $output );
		}
		else if(brp_strtolower($POST['mode']) == "add_old") {
		//echo "<pre>"; print_r($POST); die();
			$date=date("Y-m-d h:i:sa");
			
			$jobwork_no = $POST['jobwork_no'];
			$jobwork_date = date('Y-m-d', strtotime($POST['jobwork_date']));
			$vender_id = $POST['vender_id'];
			$vehicle_no = $POST['vehicle_no'];
			$remark = $POST['remark'];
			$vendor_rate = $POST['vendor_rate'];
			$available_qty = $POST['available_qty'];
			$p_product_id = $POST['p_product_id'];
			$p_process_id = $POST['p_process_id'];
			$alloc_process_id = $POST['alloc_process_id'];
			$process_unit = $POST['process_unit'];
			$branch_id = $POST['branch_id'];

			
			$j_pr_process_no = load_series_no($dbcon,7);

			$infojobwork['jobwork_no']    	= $jobwork_no;  
			$infojobwork['jobwork_date']    = $jobwork_date;  
			$infojobwork['vendor_id']    	= $vender_id;  
			$infojobwork['vehicle_no']    	= $vehicle_no;  
			$infojobwork['remark']    		= $remark;  
			$infojobwork['jobwork_status']  = 0;  
			$infojobwork['user_id']    		= $_SESSION['user_id'];  
			$infojobwork['cdate']    		= $date;  
			$infojobwork['company_id']    	= $_SESSION['company_id'];  

			$jobwork_main_id=add_record('tbl_jobwork_main', $infojobwork, $dbcon,$branch_id);

			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '11' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '7' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);

			foreach ($p_product_id as $pkey => $pvalue) {

				$info1['jobwork_main_id']		= $jobwork_main_id;
				$info1['jobwork_no']			= $infojobwork['jobwork_no'];
				$info1['jobwork_date']			= $infojobwork['jobwork_date'];
				$info1['j_product_id']			= $p_product_id[$pkey];
				$info1['j_pr_process_id']		= $p_process_id[$pkey];
				$info1['j_process_type']		= 2;
				$info1['j_pr_process_no']		= $j_pr_process_no;
				$info1['j_vendor']				= $vender_id;
				$info1['j_qty']					= $available_qty[$pkey];
				
				$info1['j_alloc_process_id']	= $alloc_process_id[$pkey];
				$info1['process_unit']			= $process_unit[$pkey];
				$info1['pr_rate']				= $vendor_rate[$pkey];
				
				$info1['cdate']					= date("Y-m-d H:i:s");
				$info1['userid']				= $_SESSION['user_id'];
				$info1['company_id']			= $_SESSION['company_id'];
				
				$job_id=add_record('tbl_jobwork', $info1, $dbcon,$branch_id);


				$info3['process_id']			= $p_process_id[$pkey];
				$info3['p_start_time']			= date("Y-m-d H:i:s");
				$info3['p_end_time']			= '';
				$info3['p_qty']					= $available_qty[$pkey];
				$info3['pen_qty']				= '';
				$info3['p_status']				= '1';
				$info3['p_product_id']			= $p_product_id[$pkey];
				$info3['pr_process_type']		= 2;
				
				$info3['cdate']					= date("Y-m-d H:i:s");
				$info3['user_id']				= $_SESSION['user_id'];
				$info3['company_id']			= $_SESSION['company_id'];
				
				$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon,$branch_id);

				$alloc_process_id_implode = $alloc_process_id[$pkey];
				$query="select * from tbl_allocate_process where p_id in (".$alloc_process_id_implode.")";
				$result=$dbcon->query($query);

				$i=0;
			//$qty = (float)$available_qty[$pkey];
				$qty = $available_qty[$pkey];
				
				while($row=brp_mysqli_fetch_assoc($result)){
					

					$sub_qty=($row['p_qty']-$row['start_qty']);
					
					$aaac_qty=start_qty_avalable($dbcon,$row['process_id'],$row['pr_process_type'],$row['p_product_id'],$row['p_id'],$branch_id);
					
					if($aaac_qty<=$sub_qty){
						$sub_qty=$aaac_qty;
					}
					
					if($qty!=0){
						if($sub_qty>=$qty){
							
							
							$dbcon->query("update tbl_allocate_process set start_qty=start_qty+".$qty.",p_start_time='$date',p_status='1',task_status='1' where  p_id=".$row['p_id']."");
							
							add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$row['p_product_id'],$row['process_id'],$qty,"0");
							

							$infog['jobwork_id']		= $job_id;
							$infog['p_id']				= $row['p_id'];
							$infog['qty']				= $qty;
							$infog['cdate']				= date("Y-m-d H:i:s");
							$infog['userid']			= $_SESSION['user_id'];
							$infog['company_id']		= $_SESSION['company_id'];
							
							$job_p=add_record('tbl_jobwork_process', $infog, $dbcon,$branch_id);
							$qty=$qty-$qty;
							
							
						}else{
							$dbcon->query("update tbl_allocate_process set start_qty=start_qty+".$sub_qty.",p_start_time='$date',p_status='1',task_status='1' where p_id=".$row['p_id']."");
							
							add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$row['p_product_id'],$row['process_id'],$sub_qty,"0");

							$infog['jobwork_id']		= $job_id;
							$infog['p_id']				= $row['p_id'];
							$infog['qty']				= $sub_qty;
							$infog['cdate']				= date("Y-m-d H:i:s");
							$infog['userid']			= $_SESSION['user_id'];
							$infog['company_id']		= $_SESSION['company_id'];
							
							$job_p=add_record('tbl_jobwork_process', $infog, $dbcon,$branch_id);
							$qty=$qty-$sub_qty;
						}
					}
					
					
					$i++;		
				}
			}

			
			if($inserusrid1){
				$arr['msg'] = '1';
			}else{
				$arr['msg'] = '0';
			}

			echo brp_json_encode($arr);

		}
		else if(brp_strtolower($POST['mode'])== "get_series_no_jobwork")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=11 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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
		else if(brp_strtolower($POST['mode'])== "show_product_list"){

			$vendor_id=$POST['vendor_id'];
			$process_id=$_POST['process_id'];
			$product_id=$_POST['product_id'];
			$type=$_POST['type'];
			$branch_id=$_POST['branch_id'];
			
			
			$str='';
			$where = '';

			if($vendor_id!='' && $type=='1'){
				$get_process_and_product_sql = "select GROUP_CONCAT(job_party_process_id) as process_id, GROUP_CONCAT(job_party_product) as product_id  from tbl_product_job_party_purchase where job_party_id = '".$vendor_id."' and company_id='".$_SESSION['company_id']."' ";
				$get_party_result=$dbcon->query($get_process_and_product_sql);
				$party_data_array = brp_mysqli_fetch_assoc($get_party_result);

				$total_process_id = $party_data_array['process_id'];
				$total_product_id = $party_data_array['product_id'];
				$where .= " and ap.process_id IN (".$total_process_id.") and  p_product_id IN (".$total_product_id.") ";
			}else{
				$where .= " and ap.process_id = '".$process_id."' and  p_product_id = '".$product_id."' ";
			}
			if(!empty($branch_id)){
				$branch_where=" and ap.branch_id=".$branch_id;
			}
			$cnt=1;
			$query1="SELECT SQL_CALC_FOUND_ROWS ap.*, sum(ap.p_qty) as ap_qty, sum(ap.pen_qty) as apen_qty, p.product_type, p.product_name, IFNULL(end_qty,0) as end_qty, IFNULL(strtt_qty,0) as strtt_qty, pro.process_name, tc.cat_name, GROUP_CONCAT(`ap`.`p_id` ORDER BY `ap`.`p_id` ASC) allocate_id FROM tbl_allocate_process as ap
			left join product_mst as p on p.product_id=ap.p_product_id
			left join tbl_category as tc on p.product_category=tc.cat_id 
			left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
			left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
			left join process_mst as pro on ap.process_id=pro.process_id 
			
			WHERE ( 1 AND pr_process_type='2' and ap.p_status IN (0,1) and ap.company_id=".$_SESSION['company_id']." ".$where." ".$branch_where.") 
			Group by ap.p_product_id,ap.process_id,ap.product_version 
			ORDER BY ap.p_id asc  ";
			$query=$dbcon->query($query1);
			if($query->num_rows > 0){
				while($rel=brp_mysqli_fetch_array($query))
				{
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				//$allocate_id = $rel['allocate_id'];
					$query_c="select * from tbl_allocate_process where p_id in (".$rel['allocate_id'].")";
				//var_dump($query);
					$result_c=$dbcon->query($query_c);
					$nnq=array();
					while($row_c=brp_mysqli_fetch_assoc($result_c)){
					//$aaac_qty=start_qty_avalable($dbcon,$row_c['process_id'],$row_c['pr_process_type'],$row_c['p_product_id'],$row_c['p_id'],$branch_id);
						$aaac_qty=production_start_count_using_p_id($dbcon,$row_c['p_id']);
						
						if($aaac_qty>0){
							array_push($nnq,$row_c['p_id']);
						}
					//array_push($nnq,$row_c['p_id']);
					}
					$allocate_id=implode(",",$nnq);

					$start_qty_data = "SELECT sum(pt_qty) as start_qty_valua FROM `tbl_allocate_process_trn` where p_status = 0 and pt_alloc_id IN (".$allocate_id.") ";
					$start_result=$dbcon->query($start_qty_data);
					$start_qty_result = brp_mysqli_fetch_assoc($start_result);
					$total_start_qty = $start_qty_result['start_qty_valua'];

					$finish_qty_data = "SELECT sum(pt_qty) as start_qty_valua FROM `tbl_allocate_process_trn` where p_status = 1 and pt_alloc_id IN (".$allocate_id.") ";
					$finish_result=$dbcon->query($finish_qty_data);
					$finish_qty_result = brp_mysqli_fetch_assoc($finish_result);
					$total_finsih_qty = $finish_qty_result['start_qty_valua'];

					$current_start_qty = $total_start_qty - $total_finsih_qty;

					$req_working_qty = $rel['apen_qty']-$current_start_qty;

					$av_qty=start_qty_avalable($dbcon,$rel['process_id'],$rel['pr_process_type'],$rel['p_product_id'],$branch_id);
				//production_start_count_using_p_id($dbcon,$pid)
				//$min_working_qty = working_qty_avalable($dbcon,$rel['process_id'], $rel['pr_process_type'], $rel['p_product_id'], $rel['p_status'], $rel['previous_process_id'],$branch_id);
					$min_working_qty =production_start_count_using_p_id($dbcon,$allocate_id);
					

					$job_party_rate = '';
					if($vendor_id!=''){
						$party_rate_sql = "SELECT job_party_rate FROM `tbl_product_job_party_purchase` where job_party_process_id = '".$rel['process_id']."' and job_party_id = '".$vendor_id."' and job_party_product = '".$rel['p_product_id']."' and company_id='".$_SESSION['company_id']."' ";
						$party_rate_result=$dbcon->query($party_rate_sql);
						$party_rate_data = brp_mysqli_fetch_assoc($party_rate_result);
						$job_party_rate = $party_rate_data['job_party_rate'];
					}
					
					if($min_working_qty>0){
						$str.='<tr>
						<th>'.$cnt.'</th>
						<th>'.$rel['product_name'].'</th>
						<th>'.$cat_name.'</th>
						<th>'.$rel['process_name'].'</th>
						<th>'.$rel['ap_qty'].'</th>
						<th>'.$min_working_qty.'</th>
						<th><input type="number" class="form-control vendor_rate" required name="vendor_rate[]" value = "'.$job_party_rate.'"></th>
						<th>
						<!--<input type="text" name="alloc_process_id[]" value = "'.$rel['allocate_id'].'">-->
						<input type="hidden" name="alloc_process_id[]" value = "'.$allocate_id.'">
						<input type="hidden" name="p_id[]" id="p_id'.$cnt.'" value = "'.$allocate_id.'">
						<input type="hidden" name="process_unit[]" value = "'.$rel['process_unit'].'">
						<input type="hidden" name="p_process_id[]" value = "'.$rel['process_id'].'">
						<input type="hidden" name="p_product_id[]" value = "'.$rel['p_product_id'].'">
						<input type="number" class="form-control" required name="available_qty[]" max="'.$min_working_qty.'" value = "'.$min_working_qty.'">
						</th>
						</tr>';
						$cnt++;
					}
				}
			}else{
				$str.='<tr><td colspan="8" style="text-align: center;">DATA NOT EXISTS.</td></tr>';
			}
			echo $str;
		}
		else if(brp_strtolower($POST['mode'])== "load_po_no")
		{
			$vendor_id = $POST['vendor_id'];
			$product_id = $POST['product_id']; 
			$process_id = $POST['process_id']; 
			$id = "";
			$query="select po.purchaseorder_id, trn.purchaseordertrn_id,po.purchaseorder_no,trn.product_rate from tbl_purchaseordertrn as trn
			left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
			where trn.purchaseordertrn_status=0 and po.po_aproove_finance = 1 and trn.product_id=".$product_id." and trn.process_id = ".$process_id." and po.vender_id = ".$vendor_id." and  po.po_type = 2 and po.company_id=".$_SESSION['company_id'];
			$result=$dbcon->query($query);
		// $row=brp_mysqli_fetch_assoc($result);
			$str .='<option value="" >--Choose PO NO--</option>';
			while($row=mysqli_fetch_assoc($result)){
				$sel = '';
				if($row['purchaseordertrn_id']==$id){
					$sel = 'selected="selected"';
				}
				$str .= '<option '.$sel.' value="'.$row['purchaseordertrn_id'].'">'.$row['purchaseorder_no'].'</option>';
			}
			echo $str;
		}
		else if(brp_strtolower($POST['mode'])== "load_po_rate")
		{
			$purchaseordertrn_id = $POST['po_trn_id'];
			
			$query="select trn.product_rate from tbl_purchaseordertrn as trn
			where trn.purchaseordertrn_status=0 and trn.purchaseordertrn_id = ".$purchaseordertrn_id;
			
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);

			echo $row['product_rate'];
			
		}
		
		?>
