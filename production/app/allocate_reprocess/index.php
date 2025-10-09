<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			
			$process_id=$POST['process_id'];
			$process_type=$_POST['process_type'];
			
			$str='';
			$str.='<tr>
				
				<th>#</th>
				<th>Product Name</th>
				<th>Product Category</th>
				<th>Qty</th>
				<th>Pending Qty</th>
				<th>Status</th>
				<!--<th>Start Time</th>
				<th>End Time</th>-->
				<th>Action</th>
				
			</tr>';
			
			$cnt=1;
			$query1="select p.*,pr.product_name,tc.cat_name,pr.product_type,dqty from tbl_allocate_re_process as p 
			left join product_mst as pr on pr.product_id=p.p_product_id left join tbl_category as tc on pr.product_category=tc.cat_id  
			left join (select sum(pt_qty) as dqty,pt_alloc_id from tbl_allocate_re_process_trn group by pt_alloc_id) as apta on apta.pt_alloc_id=p.p_id  
			where process_id='$process_id' and p.pr_process_type='$process_type'";
			$query=$dbcon->query($query1);
			while($rel=mysqli_fetch_array($query))
			{

				//$pending_qty=($rel['p_qty']-$rel['dqty']);
				$pending_qty=$rel['pen_qty'];
				$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				
				if($rel['p_status']=='0')
				{
					$status="<strong style='color:red'>Not Started</strong>";
					$start_date='-';
					$end_date='-';
					//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process1" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_reprocess/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';

					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_reprocess/'.$rel['p_product_id'].'/'.$process_type.'/'.$process_id.'/process'.'" ><i class="fa fa-plus"></i></a>';


				//	$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
					
					//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" onclick="start_process(\''.$rel['p_id'].'\',\''.get_pro_field($dbcon,$rel['p_product_id'],'product_name').'\',\''.$pending_qty.'\',\''.$rel['p_product_id'].'\',\''.$rel['product_type'].'\')"><i class="fa fa-plus"></i></a>';
				}
				else if($rel['p_status']=='1')
				{
					$status="<strong style='color:green'>Started</strong>";
					$start_date=date("d/m/Y h:i:sa",strtotime($rel['p_start_time']));
					$end_date='--';
					//$button='<a class="btn btn-xs btn-success" data-original-title="End Allocate Process Quantity" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'end_process_allocation_repocess/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';

					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'end_process_allocation_repocess/'.$rel['p_product_id'].'/'.$process_type.'/'.$process_id.'" ><i class="fa fa-power-off"></i></a>';
				//	$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" onclick="add_allocate_process(\''.$rel['p_id'].'\',\''.$rel['p_product_id'].'\',\''.get_pro_field($dbcon,$rel['p_product_id'],'product_name').'\',\''.$pending_qty.'\',\''.$rel['p_ref_id'].'\',\''.$rel['process_id'].'\',\''.$rel['p_ref_type'].'\',\''.$rel['pr_process_type'].'\',\''.$rel['product_type'].'\')"><i class="fa fa-plus"></i></a>';
				}
				else
				{
					$status="<strong style='color:red'>Completed</strong>";
					$start_date=date("d/m/Y h:i:sa",strtotime($rel['p_start_time']));
					$end_date=date("d/m/Y  h:i:sa",strtotime($rel['p_end_time']));
					$button='';
				}
				
				
				
				$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Challan" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'chalan_print/'.$rel['p_id'].'" target="_blank"><i class="fa fa-print"></i></a>';
			
				$str.='<tr>
				
				<th>'.$cnt.'</th>
				<th>'.$rel['product_name'].'</th>
				<th>'.$cat_name.'</th>
				<th>'.$rel['p_qty'].'</th>
				<th>'.$pending_qty.'</th>
				<th>'.$status.'</th>
				<!--<th>'.$start_date.'</th>
				<th>'.$end_date.'</th>-->
				<th>'.$button.' '.$btn_print.'</th>
				</tr>';
				
				$cnt++;
			}
			
			echo $str;
		}

		else if(strtolower($POST['mode']) == "start_process_allocation") {
			
			$pr_st_time=$POST['pr_st_time'];
			$pr_end_time=$POST['pr_end_time'];
			$pr_pr_qty=$POST['pr_pr_qty'];
			$product_id=$POST['product_id'];
			$qc_id=$POST['qc_id'];
			$process_id=$POST['process_id'];
			$ref_type=$POST['ref_type'];
			$pr_process_type=$_POST['pr_process_type'];
			$aid=$POST['aid'];
			$pr_remain_qty=$POST['pr_remain_qty'];
			
			$info['pt_product_id']=$product_id;
			$info['pt_process_id']=$process_id;
			$info['pt_ref_id']=$qc_id;
			$info['pt_qty']=$pr_pr_qty;
			$info['pt_alloc_id']=$aid;
			
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			
			$inserusrid=add_record('tbl_allocate_re_process_trn', $info, $dbcon); 
			
			$next_process=get_next_process($dbcon,$process_id,$product_id);
			
			if($next_process!=0)
			{
				$info1['process_id']=$next_process;
				$info1['p_qty']=$pr_pr_qty;
				$info1['pen_qty']=$pr_pr_qty;
				$info1['p_ref_id']=$qc_id;
				$info1['p_product_id']=$product_id;
				$info1['p_status']='0';
				$info1['p_ref_type']=$ref_type;
				$info1['p_ref_type']=$ref_type;
				$info1['pr_process_type']=$pr_process_type;
				
				$info1['cdate']			= date("Y-m-d H:i:s");
				$info1['user_id']		= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				
				$inserusrid1=add_record('tbl_allocate_re_process', $info1, $dbcon);
			}
			
			if($pr_remain_qty==0)
			{
				$info2['p_end_time']=date("Y-m-d h:i:sa",strtotime($pr_end_time));
				$info2['p_status']='3';
				
				$table='tbl_allocate_re_process';$tableid='p_id';
				update_record($table, $info2, $tableid."=".$aid, $dbcon);
			}
			
		}
		
		else if(strtolower($POST['mode']) == "add_start_process") {
			
			$process_id=$POST['process_id_hid'];
			$eid=$POST['eid'];
			$date=date("Y-m-d h:i:sa");
			
			$dbcon->query("update tbl_allocate_re_process set p_start_time='$date',p_status='1' where  p_id='$eid'");
			
			$info1['jobwork_no']		=$POST['pr_job_no'];
			$info1['jobwork_date']		=$date;
			$info1['j_product_id']		=$POST['product_id_hid'];
			$info1['j_pr_process_id']	=$POST['process_id_hid'];
			$info1['j_process_type']	=$POST['process_type_hid'];
			$info1['j_pr_process_no']	=$POST['pr_process_no'];
			$info1['j_vendor']			=$POST['pr_vender_id'];
			$info1['j_chalan_no']		=$POST['pr_chalan_no'];
			$info1['j_qty']				=$POST['machine_no'];
			$info1['j_ref_id']			=$POST['request_no'];
			$info1['j_reprocess']		='1';
			$info1['j_reprocess_id']	=$POST['eid'];
			$info1['process_unit']		=$POST['process_unit'];
			$info1['j_alloc_process_id']=$POST['pr_process_id'];
			
			$info1['cdate']				= date("Y-m-d H:i:s");
			$info1['userid']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			
			$inserusrid1=add_record('tbl_jobwork', $info1, $dbcon);
			
			$infog['jobwork_id']		= $inserusrid1;
			$infog['p_id']				= $POST['pr_process_id'];
			$infog['qty']				= $POST['machine_no'];
			$infog['cdate']				= date("Y-m-d H:i:s");
			$infog['userid']			= $_SESSION['user_id'];
			$infog['company_id']		= $_SESSION['company_id'];
			
			$job_p=add_record('tbl_jobwork_process', $infog, $dbcon);
			
			
			/*if($POST['process_type_hid']==2)
			{*/
				$j_pr_job_id=$POST['j_pr_job_id'];
				
				for($k=0;$k<count($j_pr_job_id);$k++)
				{
					$loop_id=$j_pr_job_id[$k];
					
					$info2['product_type']=$POST['j_ptype'][$k];
					$info2['raw_product_id']=$loop_id;
					$info2['jobwork_id']=$inserusrid1;
					$info2['outward_product_qty']=$POST['j_usable'][$k];
					$info2['outward_product_rate']=$POST['j_prate'][$k];
					$info2['outward_product_amt']=$POST['j_pamount'][$k];
				
					$info2['cdate']			= date("Y-m-d H:i:s");
					$info2['user_id']		= $_SESSION['user_id'];
					$info2['company_id']		= $_SESSION['company_id'];
					
					add_record('tbl_jobworktrn', $info2, $dbcon);
				}
			/* } */
			/*	if($POST['process_type_hid']==2)
				{
					$info1['jobwork_no']=$POST['pr_job_no'];
					$info1['jobwork_date']=$date;
					$info1['j_product_id']=$POST['product_id_hid'];
					$info1['j_pr_process_id']=$POST['process_id_hid'];
					$info1['j_process_type']=$POST['process_type_hid'];
					$info1['j_pr_process_no']=$POST['pr_process_no'];
					
					$info1['cdate']			= date("Y-m-d H:i:s");
					$info1['userid']		= $_SESSION['user_id'];
					$info1['company_id']		= $_SESSION['company_id'];
					
					$inserusrid1=add_record('tbl_jobwork', $info1, $dbcon);
				} */
			echo "1";
		}
		
		if(strtolower($POST['mode']) == "show_material_list") {
			
			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			
			$pid=$POST['pid'];
			$pro_type=$POST['pro_type'];
			$pqty=$POST['pqty'];
			
			$where="";
			if($pro_type==0)
			{
				$where.=" and parent_id = '0' and sale_product_id='$pid'";
			}
			else
			{
				$where.=" and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
			}
			
			$appData = array();
			$i=1;
			$aColumns = array('itm.product_id','itm.product_type','itm.product_purchase_rate', 'itm.product_name', 'itm.product_min_stock', 'itm.product_opening','bt.product_act_qty','u.unit_name','bt.product_base_unit','IFNULL(qcqty,0)');
			$sIndexColumn = "bt.bom_trn_id";
			$isWhere = array("bt.bom_trn_status=0 ".$where." ");
			$sTable = "tbl_bomtrn as bt";
			$isJOIN = array('left join product_mst as itm on itm.product_id=bt.product_id','left join unit_mst as u on u.unitid=bt.product_base_unit','left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id');
			$hOrder = "itm.product_name";
			$hGroupby = "bt.product_id";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {

				$row_data = array();
				
				$op_stock=$row['product_opening'];
				$total=$op_stock+$row['qcqty'];
				$cl_stock=$total;
				
				$required_qty=$pqty*$row['product_act_qty'];
				
				if($total>$required_qty)
				{
					$usable=$required_qty;
				}
				else
				{
					$usable=round(($total/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
				}
				
				$machine_make=round(($usable/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
				
				$pr_amount=$row['product_purchase_rate']*$usable;
				
				$row_data[] = $row['product_name']."<input type='hidden' class='form-control text-box j_pr_job_id' name='j_pr_job_id[]' id='j_pr_job_id$id' value='".$row['product_id']."' />"."<input type='hidden' class='form-control text-box' name='j_ptype[]' id='j_ptype$id' value='".$row['product_type']."' />"."<input type='hidden' class='form-control text-box' name='j_prate[]' id='j_prate$id' value='".$row['product_purchase_rate']."' />"."<input type='hidden' class='form-control text-box' name='j_pamount[]' id='j_pamount$id' value='".$pr_amount."' />";
				$row_data[] = $row['product_act_qty'];
				$row_data[] = $required_qty;
				$row_data[] = $total;
				$row_data[] = $usable."<input type='hidden' class='form-control text-box' name='j_usable[]' id='j_usable$id' value='".$usable."' />";
				$row_data[] = $row['unit_name']."<input type='hidden' class='form-control text-box' name='machine_make[]' id='machine_make$id' value='".$machine_make."' />
				<input type='hidden' name='j_unit_id[]' id='j_unit_id$id' value='".$row['product_base_unit']."' />";
	   
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
				
		}
		else if(strtolower($POST['mode']) == "show_material_list_new") {
			
			$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
				WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['eid'].")";
			$resul=$dbcon->query($bom);
			$rel1=mysqli_fetch_assoc($resul);
			
			$bom1="SELECT rpro.*,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			WHERE rpro.status!=2 AND rpro.perent_id in (".$rel1['views'].") group by rpro.rp_pid" ;
			$result=$dbcon->query($bom1);
			$i=1;
			while($rel=mysqli_fetch_assoc($result)){
				$o_qty=convert_stock($dbcon,$rel["req_qty_one"],$rel['rp_pid'],"base_unit");
				$rel["req_qty_one"]=round($rel["req_qty_one"],6);
				$o_qty=round($o_qty,6);
				//$o_qty=round($rel["req_qty_one"],6);
				
				$total_req_qty=$POST['pending_qty']*$o_qty;
				$total_req_qty=round($total_req_qty,4);
				$used_qty=$POST['max_start_qty']*$o_qty;
				$used_qty=round($used_qty,4);
				$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit']);
				$cur_stock=round($cur_stock,4);
				//$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['process_unit'],"",$rel_n1['rp_id']);
				echo '<tr>
						<td>'.$rel["product_name"].'
							<input type="hidden" class="" name="row_product_id[]" id="row_product_id'.$i.'" value="'.$rel['rp_pid'].'" />
						</td>
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
		if(strtolower($POST['mode']) == "show_material_list_allocate") {
			
			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			
			$pid=$POST['pid'];
			$pro_type=$POST['pro_type'];
			$pqty=$POST['pqty'];
			
			$where="";
			if($pro_type==0)
			{
				$where.=" and parent_id = '0' and sale_product_id='$pid'";
			}
			else
			{
				$where.=" and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
			}
			
			$appData = array();
			$i=1;
			$aColumns = array('itm.product_id', 'itm.product_name', 'itm.product_min_stock', 'itm.product_opening','bt.product_act_qty','u.unit_name','IFNULL(qcqty,0)');
			$sIndexColumn = "bt.bom_trn_id";
			$isWhere = array("bt.bom_trn_status=0 ".$where." ");
			$sTable = "tbl_bomtrn as bt";
			$isJOIN = array('left join product_mst as itm on itm.product_id=bt.product_id','left join unit_mst as u on u.unitid=bt.product_base_unit','left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id');
			$hOrder = "itm.product_name";
			$hGroupby = "bt.product_id";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {

				$row_data = array();
				
				$op_stock=$row['product_opening'];
				$total=$op_stock+$row['qcqty'];
				$cl_stock=$total;
				
				$required_qty=$pqty*$row['product_act_qty'];
				
				if($total>$required_qty)
				{
					$usable=$required_qty;
				}
				else
				{
					$usable=round(($required_qty/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
				}
				
				$machine_make=round(($usable/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
				
				$row_data[] = $row['product_name'];
				$row_data[] = $row['product_act_qty'];
				$row_data[] = $required_qty;
				$row_data[] = $total;
				$row_data[] = $usable;
				$row_data[] = $row['unit_name']."<input type='hidden' class='form-control text-box' name='machine_make_allocate[]' id='machine_make$id' value='".$machine_make."' />";
	   
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
				
		}
		
		
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>