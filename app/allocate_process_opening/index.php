<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
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
		
		
		
		if(strtolower($POST['mode']) == "fetch_opening") {
			
			$process_id=$POST['process_id'];
			$process_type=$_POST['process_type'];
			
			$str='';
			$str.='<tr>
				
				<th>#</th>
				<th>Product Name</th>
				<th>Qty</th>
				<th>Pending Qty</th>
				<th>Status</th>
				<th>Task Status</th>
				<th>Start Time</th>
				<th>End Time</th>
				<th>Action</th>
				
			</tr>';
			
			$cnt=1;
			$query1="select p.*,pr.product_name,pr.product_type,dqty from tbl_allocate_process as p left join product_mst as pr on pr.product_id=p.p_product_id  left join (select sum(pt_qty) as dqty,pt_alloc_id from tbl_allocate_process_trn group by pt_alloc_id) as apta on apta.pt_alloc_id=p.p_id  where process_id='$process_id' and p.pr_process_type='$process_type' and p.process_type_data='1'";
			$query=$dbcon->query($query1);
			while($rel=mysqli_fetch_array($query))
			{
				$pending_qty=($rel['p_qty']-$rel['dqty']);
				
				if($rel['p_status']=='0')
				{
					$status="<strong style='color:red'>Not Started</strong>";
					$start_date='-';
					$end_date='-';
					
					//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" onclick="start_process(\''.$rel['p_id'].'\',\''.get_pro_field($dbcon,$rel['p_product_id'],'product_name').'\',\''.$pending_qty.'\',\''.$rel['p_product_id'].'\',\''.$rel['product_type'].'\')"><i class="fa fa-plus"></i></a>';
				}
				else if($rel['p_status']=='1')
				{
					$status="<strong style='color:green'>Started</strong>";
					$start_date=date("d/m/Y h:i:sa",strtotime($rel['p_start_time']));
					$end_date='--';
					
					
				}
				else
				{
					$status="<strong style='color:red'>Completed</strong>";
					$start_date=date("d/m/Y h:i:sa",strtotime($rel['p_start_time']));
					$end_date=date("d/m/Y  h:i:sa",strtotime($rel['p_end_time']));
					
				}
				
				if($rel['task_status']==0)
				{
					$task_status="<strong style='color:red'>Not Started</strong>";
					
					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.'start_process_opening/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
				}
				else if($rel['task_status']=='1')
				{
					$task_status="<strong style='color:green'>Started</strong>";
					
					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" href="'.ROOT.'end_process_allocation_opening/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
				}
				else
				{
					$task_status="<strong style='color:red'>Process End</strong>";
					
					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.'start_process_opening/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
					
				}
				
				//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.'start_process/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
				
				//$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Challan" data-toggle="tooltip" data-placement="top" href="'.ROOT.'chalan_print/'.$rel['p_id'].'" target="_blank"><i class="fa fa-print"></i></a>';
				
				$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Warehouse Details" data-toggle="tooltip" data-placement="top" href="'.ROOT.'warehouse_print/'.$rel['p_id'].'" target="_blank"><i class="fa fa-print"></i></a>';
				
				$btn_history='<a class="btn btn-xs btn-info" data-original-title="Process History" data-toggle="tooltip" data-placement="top" href="'.ROOT.'process_history/'.$rel['p_id'].'" target="_blank"><i class="fa fa-building"></i></a>';
			
				$str.='<tr>
				
				<th>'.$cnt.'</th>
				<th>'.$rel['product_name'].'</th>
				<th>'.$rel['p_qty'].'</th>
				<th>'.$pending_qty.'</th>
				<th>'.$status.'</th>
				<th>'.$task_status.'</th>
				<th>'.$start_date.'</th>
				<th>'.$end_date.'</th>
				<th>'.$button.' '.$btn_print.' '.$btn_history.'</th>
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
			
			$inserusrid=add_record('tbl_allocate_process_trn', $info, $dbcon); 
			
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
				
				$inserusrid1=add_record('tbl_allocate_process', $info1, $dbcon);
			}
			
			if($pr_remain_qty==0)
			{
				$info2['p_end_time']=date("Y-m-d h:i:sa",strtotime($pr_end_time));
				$info2['p_status']='3';
				
				$table='tbl_allocate_process';$tableid='p_id';
				update_record($table, $info2, $tableid."=".$aid, $dbcon);
			}
			
		}
		
		else if(strtolower($POST['mode']) == "add_start_process") {
			
			$process_id=$POST['process_id_hid'];
			$eid=$POST['eid'];
			$date=date("Y-m-d h:i:sa");
			
			$dbcon->query("update tbl_allocate_process set p_start_time='$date',p_status='1',task_status='1' where  p_id='$eid'");
			
			$sel1=$dbcon->query("select jobwork_id from tbl_jobwork where j_alloc_process_id='$eid'");
			$count=mysqli_num_rows($sel1);
			
			if($count==0)
			{
				$info1['jobwork_no']=$POST['pr_job_no'];
				$info1['jobwork_date']=$date;
				$info1['j_product_id']=$POST['product_id_hid'];
				$info1['j_pr_process_id']=$POST['process_id_hid'];
				$info1['j_process_type']=$POST['process_type_hid'];
				$info1['j_pr_process_no']=$POST['pr_process_no'];
				$info1['j_vendor']=$POST['pr_vender_id'];
				$info1['j_chalan_no']=$POST['pr_chalan_no'];
				$info1['j_qty']=$POST['machine_no'];
				$info1['j_ref_id']=$POST['request_no'];
				$info1['j_alloc_process_id']=$eid;
				$info1['pr_jobwork_no']=$POST['pr_jobwork_no'];
				
				$info1['cdate']			= date("Y-m-d H:i:s");
				$info1['userid']		= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				
				$inserusrid1=add_record('tbl_jobwork', $info1, $dbcon);
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '11'");
				
			}
			
			$info3['process_id']=$POST['process_id_hid'];
			$info3['p_start_time']=$date;
			$info3['p_end_time']='';
			$info3['p_qty']=$POST['machine_no'];
			$info3['pen_qty']='';
			$info3['p_status']='1';
			$info3['p_ref_id']=$POST['request_no'];
			//$info3['p_ref_type']=$POST['pr_chalan_no'];
			$info3['p_product_id']=$POST['product_id_hid'];
			$info3['pr_process_type']=$POST['process_type_hid'];
			$info3['j_alloc_process_id']=$eid;
			
			$info3['cdate']			= date("Y-m-d H:i:s");
			$info3['user_id']		= $_SESSION['user_id'];
			$info3['company_id']		= $_SESSION['company_id'];
			
			$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon);
			
			/*if($POST['process_type_hid']==2)
			{*/
			
			/*	$j_pr_job_id=$POST['j_pr_job_id'];
				
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
				
				$deducted_stock=$POST['deducted_stock'];
				$deducted_gd_id=$POST['deducted_gd_id'];
				
				foreach($deducted_stock as $key=>$value)
				{
					if($POST['deducted_stock'][$key]>0)
					{
						$info3['gst_dd_id']=$POST['deducted_gd_id'][$key];
						$info3['gst_pid']=$POST['product_id'][$key];
						$info3['gst_qty']=$POST['deducted_stock'][$key];
						$info3['gst_type']='1';
						$info3['gst_ref_id']=$POST['gst_eid'][$key];
						$info3['gst_date']=date('Y-m-d');
						
						$info3['cdate']			= date("Y-m-d H:i:s");
						$info3['user_id']		= $_SESSION['user_id'];
						$info3['company_id']		= $_SESSION['company_id'];
						
						add_record('tbl_godown_stock_trn', $info3, $dbcon);
					}
				}  /*
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
		
		else if(strtolower($POST['mode']) == "show_material_list") {
			
			$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			
			$pid=$POST['pid'];
			$pro_type=$POST['pro_type'];
			$pqty=$POST['pqty'];
			$eid=$POST['eid'];
			
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
			$aColumns = array('itm.product_id','itm.product_type','itm.product_purchase_rate', 'itm.product_name', 'itm.product_min_stock', 'itm.product_opening','bt.product_act_qty','u.unit_name','IFNULL(qcqty,0)');
			$sIndexColumn = "bt.bom_trn_id";
			$isWhere = array("bt.bom_trn_status=0 ".$where." ");
			$sTable = "tbl_bomtrn as bt";
			$isJOIN = array('left join product_mst as itm on itm.product_id=bt.product_id','left join unit_mst as u on u.unitid=bt.product_base_unit','left join (select sum(qc.qc_accepted) as qcqty,qc.qc_product from tbl_qc_trn as qc where qc_status=0 group by qc.qc_product) as qcd on qcd.qc_product=bt.product_id');
			$hOrder = "itm.product_name";
			$hGroupby = "bt.product_id";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				
				$cur_stock=get_current_stock($dbcon,$row['product_id']);
				
				$row_data = array();
				
				$op_stock=$row['product_opening'];
				//$total=$op_stock+$row['qcqty'];
				$cl_stock=$total;
				
				$total=$cur_stock;
				
				if($total<=0){ $total=0; }
				
				$required_qty=$pqty*$row['product_act_qty'];
				
				$get_warehose_qty=get_warehouse_qty($dbcon,$row['product_id'],$required_qty,$eid);
				
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
				
				$row_data[] = $required_qty.'<a onClick="get_warehose_deduction(\''.$row['product_id'].'\',\''.$row['product_name'].'\',\''.$required_qty.'\');"><i class="fa fa-building" style="color:green;float:right;border:solid black thin" data-original-title="Warehouse Deduction Details" data-toggle="tooltip" data-placement="top"></i></a><br>'.$get_warehose_qty;
				
				$row_data[] = $total;
				
				$row_data[] = $usable."<input type='hidden' class='form-control text-box' name='j_usable[]' id='j_usable$id' value='".$usable."' />";
				
				$row_data[] = $row['unit_name']."<input type='hidden' class='form-control text-box' name='machine_make[]' id='machine_make$id' value='".$machine_make."' />";
	   
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
				
		}
		
		else if(strtolower($POST['mode']) == "show_material_list_allocate") {
			
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
			include('../../include/pagging.php');
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
		
		else if(strtolower($POST['mode']) == "show_godown_product_detail") {
			
			$pro_id=$POST['pro_id'];
			$req_qty=$POST['req_qty'];
			
			$cnt=1;$str='';
			$selb=$dbcon->query("select gd.*,gps.product_stock,gps.priority from mst_godown as gd left join tbl_branch_product_stock as gps  on gd.gd_id=gps.branch_id where gd.g_status=0 and gps.product_id='$pro_id' ");
			while($rb=mysqli_fetch_array($selb))
			{
				if($req_qty>=$rb['product_stock'])
				{
					$deducted=$rb['product_stock'];
					$req_qty=$req_qty-$rb['product_stock'];
				}
				else
				{
					$deducted=$req_qty;
					$req_qty=0;
				}
				
				
				
				$str.='
				<tr>
					<td>'.$cnt.'</td>
					<td>'.$rb['gd_name'].'</td>
					<td>'.$rb['product_stock'].'</td>
					<td>
						<input type="text" class="form-control" name="deducted_stock[]" value="'.$deducted.'" />
					</td>
				</tr>';
			}
			
			echo $str;
		}
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=11 and company_id=".$_SESSION['company_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
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
		
		
		else if(strtolower($POST['mode'])== "get_series_no_jobwork")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=12 and company_id=".$_SESSION['company_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(strtolower($POST['mode'])== "load_invoiceno_jobwork")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
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
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>