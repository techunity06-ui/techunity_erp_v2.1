<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$company_config = getCompanyConfiguration($dbcon);	

$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);


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
		
		if(brp_strtolower($POST['mode']) == "fetch") {


			
			$process_id=$POST['process_id'];
			$process_type=$_POST['process_type'];
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$where_db = check_branch('p', $branch_id);
			
			$str='<tbody>';
			$str.='<tr>
				
				<th>#</th>
				<th>Workorder No</th>
				<th>Jobcard No</th>
				<th>Product Name</th>
				<th>Product Category</th>
				<th>Qty</th>
				<th>Pending Qty</th>
				<th>Status</th>
				<th>Task Status</th>
				<!--<th>Start Time</th>
				<th>End Time</th>-->
				<th>Action</th>
				
			</tr>';
			if(!empty($POST['product_id'])){
				$ser=" and p.p_product_id=".$POST['product_id'];
			}
			$cnt=1;
			$query1="select p.*,pr.product_name,pr.product_type,tc.cat_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty,pr.product_icode, dr.drawing_number,sp.po_req_no,rp.job_card_no from tbl_allocate_process as p 
			left join product_mst as pr on pr.product_id=p.p_product_id left join tbl_category as tc on pr.product_category=tc.cat_id 
			left join tbl_drawing as dr on dr.drawing_id = pr.drawing_id
			left join tbl_request_product as rp on rp.rp_id = p.p_ref_id
			left join tbl_set_main_process as sp on sp.sp_id = rp.sp_id
			left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=p.p_id 
			left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=p.p_id 
			where process_id='$process_id' ".$ser." ".$where_db." and p.company_id=".$_SESSION['company_id']." and p.pr_process_type='$process_type' and p.p_status in(0,1)";
			$query=$dbcon->query($query1);
			while($rel=brp_mysqli_fetch_array($query))
			{
				//$pending_qty=($rel['p_qty']-$rel['dqty']);
				$pending_qty=($rel['pen_qty']);
				$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				$complete_status = 0;
				if($rel['strtt_qty']=='0')
				{
					$status="<strong style='color:red'>Not Started</strong>";
					$start_date='-';
					$end_date='-';
					
					//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" onclick="start_process(\''.$rel['p_id'].'\',\''.get_pro_field($dbcon,$rel['p_product_id'],'product_name').'\',\''.$pending_qty.'\',\''.$rel['p_product_id'].'\',\''.$rel['product_type'].'\')"><i class="fa fa-plus"></i></a>';
				}
				else if($rel['p_qty']!=$rel['end_qty'])
				{
					$complete_status = 0;
					$status="<strong style='color:green'>Started</strong>";
					$start_date=date("d/m/Y h:i:sa",strtotime($rel['p_start_time']));
					$end_date='--';
				}
				else
				{
					$complete_status = 1;
					$status="<strong style='color:red'>Completed</strong>";
					$start_date=date("d/m/Y h:i:sa",strtotime($rel['p_start_time']));
					$end_date=date("d/m/Y  h:i:sa",strtotime($rel['p_end_time']));
					
				}
				
				if($rel['task_status']==0)
				{
					$task_status="<strong style='color:red'>Not Started</strong>";
					
					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
				}
				else if($rel['task_status']=='1')
				{
					$task_status="<strong style='color:green'>Started</strong>";
					
					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'end_process_allocation/'.$rel['p_id'].'" ><i class="fa fa-power-off"></i></a>';
				}
				else
				{
					$task_status="<strong style='color:red'>Process End</strong>";
					$button='';
					//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
					
				}
				
				//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
				
				//$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Challan" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'chalan_print/'.$rel['p_id'].'" target="_blank"><i class="fa fa-print"></i></a>';
				

				//$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Warehouse Details" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'warehouse_print/'.$rel['p_id'].'" target="_blank"><i class="fa fa-print"></i></a>';

				// $btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Warehouse Details" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobcard_tracking_report/'.$rel['p_ref_id'].'" target="_blank"><i class="fa fa-print"></i></a>';

				
				$btn_history='<a class="btn btn-xs btn-info" data-original-title="Process History" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'process_history/'.$rel['p_id'].'" target="_blank"><i class="fa fa-building"></i></a>';

				$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Warehouse Details" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'jobcard_tracking_report/'.$rel['p_ref_id'].'" target="_blank"><i class="fa fa-print"></i></a>';

				$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$rel['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$rel['product_icode'].")";
				        }
			if($complete_status == 0){


				$str.='<tr>
				
				<th>'.$cnt.'</th>
				<th>'.$rel['po_req_no'].'</th>
				<th>'.$rel['job_card_no'].'</th>
				<th>'.$rel['product_name'].' '.$item_code.' '.$drawing_number.'</th>
				<th>'.$cat_name.'</th>
				<th>'.$rel['p_qty'].'</th>
				<th>'.$pending_qty.'</th>
				<th>'.$status.'</th>
				<th>'.$task_status.'</th>
				<!--<th>'.$start_date.'</th>
				<th>'.$end_date.'</th>-->
				<th>'.$btn_print.' '.$btn_history.'</th>
				</tr>';
				//'.$button.' 
				$cnt++;
			}
			}
			$str.='</tbody>';
			echo $str;
		}
		
		else if(brp_strtolower($POST['mode']) == "fetch_working") {
			
			$process_id=$POST['process_id'];
			$process_type=$_POST['process_type'];
			
			$str='';
			$str.='<tr>
				
				<th>#</th>
				<th>Product Name</th>
				<th>Product Category</th>
				<th>Qty</th>
				<th>Pending Qty</th>
				<th>Working Qty</th>
				<th>Status</th>
				<!--<th>Start Time</th>
				<th>End Time</th>-->';
				if($_SESSION['branch_id']==0){
					$str.='<th>Branch Name</th>';
				}
				$str.='<th>Action</th>
				
			</tr>';
			if(!empty($POST['product_id'])){
				$ser=" and ap.p_product_id=".$POST['product_id'];
			}

			/*$user_type = $_SESSION['user_type'];
			$where_user_wise = '';
			if($user_type!='2'){
				$where_user_wise = 'and ap.resource_id="'.$_SESSION['resource_id'].'"';
			}*/
			//pen_qty

			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('ap', $branch_id);
			
			
			 $s_ql = "select ap.*, tc.cat_name, sum(ap.p_qty) as ap_qty,sum(ap.pen_qty) as apen_qty,p.product_type,p.product_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty,branch.branch_name,group_concat(ap.p_ref_id) as refid from tbl_allocate_process as ap
				
			left join product_mst as p on p.product_id=ap.p_product_id left join tbl_category as tc on p.product_category=tc.cat_id

			left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
			left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
			left join branch_mst as branch on branch.branch_id=ap.branch_id
			where ap.process_id=".$process_id." ".$ser." ".$where_db." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type' group by ap.p_product_id,ap.p_status,ap.branch_id" ;

			$q=$dbcon->query($s_ql);
			
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{
				//echo $rel['end_qty'];
				//echo $rel['strtt_qty'];
				$pid=$rel['p_product_id'];
				
				$where='';
				if($rel['product_type']==0)
				{
					$where.=" and parent_id = '0' and sale_product_id='$pid'";
				}
				else
				{
					$where.=" and parent_id = (select bom_trn_id from tbl_bomtrn where product_id='$pid' order by bom_trn_id desc limit 0,1)";
				}
				//var_dump($rel['p_status']);
				if($rel['p_status']==1){
					$min_machine111=0;$pending_qty=0;

					/*$sq_l1 = "select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
						left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
						left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
					where ap.process_id=".$process_id." and ap.p_product_id=".$rel['p_product_id']." and ap.p_status=1 and pr_process_type='$process_type' ";*/

					 $sq_l1 = "select ap.*,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty from tbl_allocate_process as ap 
						left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id 
						left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id 
					where ap.process_id=".$process_id." ".$where_db." and ap.company_id=".$_SESSION['company_id']." and ap.p_product_id=".$rel['p_product_id']." and pr_process_type='$process_type' ";  // and ap.p_status=1 
					
					$q1=$dbcon->query($sq_l1);
					while($rel_n=brp_mysqli_fetch_array($q1)){
						
						$min_machine=$rel_n['start_qty'];
						$min_machine1111=$rel_n['strtt_qty']-$rel_n['end_qty'];
						$pending_qty1=$rel_n['pen_qty'];
						if($min_machine1111>$pending_qty1){
							$min_machine1111=$pending_qty1;
						}
						$pending_qty=$pending_qty+$pending_qty1;
						$min_machine111=$min_machine111+$min_machine1111;
					}
					
					//var_dump($min_machine111);
				}
				else if($rel['previous_process_id']==0){	

					$pending_qty=0;$min_machine111=0;
					$min_machine1112=0;
					$q1=$dbcon->query("select * from tbl_allocate_process as ap 
					where ap.process_id=".$process_id." ".$where_db." and ap.company_id=".$_SESSION['company_id']." and ap.p_product_id=".$rel['p_product_id']." and ap.p_status=0 and pr_process_type='$process_type'" );
				
					while($rel_n=brp_mysqli_fetch_array($q1)){
						$machine_make=array();
						$min_machine1112=0;
						$q12=$dbcon->query("select * from tbl_request_product as ap 
							where status=0 and perent_id=".$rel_n['p_ref_id']);
							while($rel_n1=brp_mysqli_fetch_array($q12)){
							$o_qty=$rel_n1['req_qty_one'];

							$required_qty=$rel_n['p_qty']*$o_qty;

							$required_qty=$required_qty;

							$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['purchase_unit'],"",$rel_n1['rp_id'],"","",$rel_n1['branch_id']);
							
							$total=$cur_stock;
							if($total<0){
								$total=0;
							}
							if($total>$required_qty)
							{
								$usable=$required_qty;
							}
							else
							{
								$usable=$total/$o_qty;
								$usable=$usable*$o_qty;
								
							}
							$chkp=$usable/$o_qty;
							
							$machine_make[]=$chkp;//number_format($chkp,5,".",""); code by umair
							
							$min_machine=min($machine_make);
							$min_machine1111=$min_machine;
							$pending_qty1=$rel_n['pen_qty'];
							if($min_machine1111>$pending_qty1){
								$min_machine1111=$pending_qty1;
							}
							if($min_machine1111!=$rel_n['pen_qty']){
								$min_machine1111=$min_machine1111;//floor($min_machine1111); 
							}
						}


						$pending_qty=$pending_qty+$rel_n['pen_qty'];
						$min_machine1112=$min_machine1112+$min_machine1111;
						if($min_machine1112>$pending_qty){
							$min_machine1112=$pending_qty;
						}
						$min_machine111=$min_machine111+$min_machine1112;
					}
				}else{
					$min_machine111=0;$pending_qty=0;
					$q1=$dbcon->query("select * from tbl_allocate_process as ap 
					where ap.process_id=".$process_id." ".$where_db." and ap.company_id=".$_SESSION['company_id']." and ap.p_product_id=".$rel['p_product_id']." and ap.p_status=0 and pr_process_type='$process_type' " );
					while($rel_n=brp_mysqli_fetch_array($q1)){
						
						$q22="select * from tbl_allocate_process as bt 
								where bt.p_id=".$rel_n['previous_process_id'];
						$q23=$dbcon->query($q22);
						$row12=brp_mysqli_fetch_array($q23);
						
						$min_machine=$row12['process_stock']-$row12['process_used_stock'];
						$min_machine1111=$min_machine;
						//$pending_qty11=$min_machine;
						$pending_qty1=$rel_n['pen_qty'];
						if($min_machine1111>$pending_qty1){
							$min_machine1111=$pending_qty1;
						}
						$pending_qty=$pending_qty+$pending_qty1;
						$min_machine111=$min_machine111+$min_machine1111;
					}
					//var_dump($min_machine111);
				}
			
				$machine_configuration_btn = '';
				if($min_machine111>0)
				{
						if($rel['p_status']=='0')
						{
							$status="<strong style='color:red'>Not Started</strong>";
							$start_date='-';
							$end_date='-';
							$machine_configuration_btn='<button class="btn btn-xs btn-info" data-original-title="Machine Configuration" data-toggle="tooltip" data-placement="top" onClick="machine_configuration('.$rel['p_product_id'].','.$process_id.')"><i class="fa fa-cog"></i></button>';
							
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
							
							$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_product_id'].'/'.$process_type.'/'.$process_id.'/process/'.$rel['branch_id'].'" ><i class="fa fa-plus"></i></a>';
						}
						else if($rel['task_status']=='1')
						{
							$task_status="<strong style='color:green'>Started</strong>";
							if($rel['pr_process_type']==1){
								//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'end_process_allocation/'.$rel['p_id'].'" ><i class="fa fa-power-off"></i></a>';
								
								$button='<a class="btn btn-xs btn-danger" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'end_process_allocation/'.$rel['p_product_id'].'/'.$process_type.'/'.$process_id.'/'.$rel['branch_id'].'" ><i class="fa fa-power-off"></i></a>';
								
							}else{
								$button='';
							}
						}
						else
						{
							$task_status="<strong style='color:red'>Process End</strong>";
							
							$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_product_id'].'/'.$process_type.'/'.$process_id.'/process/'.$rel['branch_id'].'" ><i class="fa fa-plus"></i></a>';
							
						}
						
						$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Challan" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'chalan_print/'.$rel['p_id'].'" target="_blank"><i class="fa fa-print"></i></a>';
						
						$btn_history='<a class="btn btn-xs btn-info" data-original-title="Process History" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'process_history/'.$rel['p_id'].'" target="_blank"><i class="fa fa-building"></i></a>';
						
						$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';	
						$str.='<tr>
					
						<th>'.$cnt.' '.$rel["refid"].'</th>
						<th>'.$rel['product_name'].'</th>
						<th>'.$cat_name.'</th>
						<th>'.$rel["ap_qty"].'</th>
						<th>'.round($pending_qty, 2).'</th>
						<th>'.round($min_machine111, 2).'</th>
						<th>'.$status.'</th>
						<!--<th>'.$start_date.'</th>
						<th>'.$end_date.'</th>-->';
						if($_SESSION['branch_id']==0){
								$str.='<th>'.$rel["branch_name"].'</th>';
						}
						$str.='<th>'.$button.' '.$btn_print.' '.$btn_history.' '.$machine_configuration_btn.'</th>
						</tr>';
					$cnt++;
					$datacheck=1;
				}
			}
			if($datacheck!=1){
				$str.= '<tr><td colspan="9"> <center>No Process Found!!!!!</center></td></tr>';
			}
			
			echo $str;
		}
		
		if(brp_strtolower($POST['mode']) == "fetch_opening") {
			
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
			$query1="select p.*,pr.product_name,pr.product_type,dqty from tbl_allocate_process as p left join product_mst as pr on pr.product_id=p.p_product_id left join (select sum(pt_qty) as dqty,pt_alloc_id from tbl_allocate_process_trn group by pt_alloc_id) as apta on apta.pt_alloc_id=p.p_id  where process_id='$process_id' and p.pr_process_type='$process_type' and p.process_type_data='1'";
			$query=$dbcon->query($query1);
			while($rel=brp_mysqli_fetch_array($query))
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
					
					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process_opening/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
				}
				else if($rel['task_status']=='1')
				{
					$task_status="<strong style='color:green'>Started</strong>";
					
					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'end_process_allocation_opening/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
				}
				else
				{
					$task_status="<strong style='color:red'>Process End</strong>";
					
					$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process_opening/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
					
				}
				
				//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_id'].'" ><i class="fa fa-plus"></i></a>';
				
				//$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Challan" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'chalan_print/'.$rel['p_id'].'" target="_blank"><i class="fa fa-print"></i></a>';
				
				$btn_print='<a class="btn btn-xs btn-primary" data-original-title="Print Warehouse Details" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'warehouse_print/'.$rel['p_id'].'" target="_blank"><i class="fa fa-print"></i></a>';
				
				$btn_history='<a class="btn btn-xs btn-info" data-original-title="Process History" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'process_history/'.$rel['p_id'].'" target="_blank"><i class="fa fa-building"></i></a>';
			
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
		
		else if(brp_strtolower($POST['mode']) == "start_process_allocation") {
			
			$pr_st_time			=$POST['pr_st_time'];
			$pr_end_time		=$POST['pr_end_time'];
			$pr_pr_qty			=$POST['pr_pr_qty'];
			$product_id			=$POST['product_id'];
			$qc_id				=$POST['qc_id'];
			$process_id			=$POST['process_id'];
			$ref_type			=$POST['ref_type'];
			$pr_process_type	=$_POST['pr_process_type'];
			$aid				=$POST['aid'];
			$pr_remain_qty		=$POST['pr_remain_qty'];
			
			$info['pt_product_id']	=$product_id;
			$info['pt_process_id']	=$process_id;
			$info['pt_ref_id']		=$qc_id;
			$info['pt_qty']			=$pr_pr_qty;
			$info['pt_alloc_id']	=$aid;
			
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
		
		else if(brp_strtolower($POST['mode']) == "add_start_process") {

			$process_id=$POST['process_id_hid'];
			
			$eid=$POST['eid'];
			$date=date("Y-m-d h:i:sa");
			$qty=$POST['machine_no'];
			$branch_id=$POST['branch_id'];
			
			$info1['jobwork_no']		=load_job_no($dbcon,$POST['invoicetype_id']);
			$info1['jobwork_date']		=$date;
			$info1['j_product_id']		=$POST['product_id_hid'];
			$info1['j_pr_process_id']	=$POST['process_id_hid'];
			$info1['j_process_type']	=$POST['process_type_hid'];
			$info1['j_pr_process_no']	=$POST['pr_process_no'];
			$info1['j_vendor']			=$POST['pr_vender_id'];
			$info1['j_chalan_no']		=$POST['pr_chalan_no'];
			$info1['j_qty']				=$qty;
			//$info1['j_ref_id']			=$row['p_ref_id'];
			$info1['j_alloc_process_id']	=$eid;
			//$info1['pr_jobwork_no']		=$POST['pr_jobwork_no'];
			$info1['process_unit']		=$POST['process_unit'];
			$info1['pr_rate']			=$POST['pr_rate'];
			
			$info1['cdate']				= date("Y-m-d H:i:s");
			$info1['userid']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			
			$job_id=add_record('tbl_jobwork', $info1, $dbcon,$branch_id);
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '11' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '7' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			
			$info3['process_id']			=$POST['process_id_hid'];
			$info3['p_start_time']			=$date;
			$info3['p_end_time']			='';
			$info3['p_qty']					=$qty;
			$info3['pen_qty']				='';
			$info3['p_status']				='1';
			//$info3['p_ref_id']				=$row['p_ref_id'];
			//$info3['p_ref_type']			=$POST['pr_chalan_no'];
			$info3['p_product_id']			=$POST['product_id_hid'];
			$info3['pr_process_type']		=$POST['process_type_hid'];
			//$info3['j_alloc_process_id']	=$row['p_id'];
			
			$info3['cdate']					= date("Y-m-d H:i:s");
			$info3['user_id']				= $_SESSION['user_id'];
			$info3['company_id']			= $_SESSION['company_id'];
			
			$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon,$branch_id);
			
			$query="select * from tbl_allocate_process where p_id in (".$eid.")";
			//var_dump($query);
			$result=$dbcon->query($query);
			while($row=brp_mysqli_fetch_assoc($result)){
				
				$sub_qty=($row['p_qty']-$row['start_qty']);
				//pathik start 
					//only  allocate qty use 
					$aaac_qty=start_qty_avalable($dbcon,$row['process_id'],$row['pr_process_type'],$row['p_product_id'],$row['p_id'],$branch_id);
					
					if($aaac_qty<=$sub_qty){
						$sub_qty=$aaac_qty;
					}
				//pathik end
				if($qty!=""){
					if($qty>0){
						if($sub_qty>=$qty){
							/* var_dump("pid=");
							var_dump($row['p_id']);
							echo "</br>";
							var_dump("qty");
							var_dump($qty);
							echo "</br>";
							 */
							$dbcon->query("update tbl_allocate_process set start_qty=start_qty+".$qty.",p_start_time='$date',p_status='1',task_status='1' where  p_id=".$row['p_id']."");
							
							add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$row['p_product_id'],$row['process_id'],$qty,"0");
							
							/*
							Code By Umair: 19/11/2020
							Commnet: Below code is commented coz of this is used in the end process function
							*/
							//add_process_stock_new($dbcon,$row['p_id'],$qty);
							
							$infog['jobwork_id']		= $job_id;
							$infog['p_id']				= $row['p_id'];
							$infog['qty']				= $qty;
							$infog['cdate']				= date("Y-m-d H:i:s");
							$infog['userid']			= $_SESSION['user_id'];
							$infog['company_id']		= $_SESSION['company_id'];
							
							$job_p=add_record('tbl_jobwork_process', $infog, $dbcon,$branch_id);
			
							/*
							Code By Umair: 19/11/2020
							Commnet: Below code is commented coz of this is used in the end process function
							*/
							/*if($row['previous_process_id']=="0"){
								$grn_qty=$POST['row_product_id'];
								for($k=0;$k<count($grn_qty);$k++)
								{
									$uqty=$POST['row_req_qty_one'][$k]*$qty;
									$uqty=round($uqty,4);
									$info2['allocate_process_id']	=$eid;
									$info2['product_id']			=$POST['row_product_id'][$k];
									$info2['unit_id']				=$POST['row_unit_id'][$k];
									$info2['used_qty']				=$uqty;
									$info2['cdate']					= date("Y-m-d H:i:s");
									$info2['user_id']				= $_SESSION['user_id'];
									$info2['company_id']			= $_SESSION['company_id'];
									
									$tbl_grn_trn_id=add_record('tbl_allocate_process_material',$info2, $dbcon);
									
									$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id,$info2['used_qty']);
									
									//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
									$request_id=find_request_id($dbcon,$row['p_ref_id'],$info2['product_id']);
									
									//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
									deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
								}
							}*/
							$qty=$qty-$qty;
							
							
						}else{
							/* var_dump("pid=");
							var_dump($row['p_id']);
							echo "</br>";
							var_dump("sub_qty");
							var_dump($sub_qty);
							echo "</br>";
							 */
							$dbcon->query("update tbl_allocate_process set start_qty=start_qty+".$sub_qty.",p_start_time='$date',p_status='1',task_status='1' where p_id=".$row['p_id']."");
							
							add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$row['p_product_id'],$row['process_id'],$sub_qty,"0");
							
							/*
							Code By Umair: 19/11/2020
							Commnet: Below code is commented coz of this is used in the end process function
							*/
							//add_process_stock_new($dbcon,$row['p_id'],$sub_qty);
							
							/* $info1['jobwork_no']		=load_job_no($dbcon,$POST['invoicetype_id']);
							$info1['jobwork_date']		=$date;
							$info1['j_product_id']		=$row['p_product_id'];
							$info1['j_pr_process_id']	=$row['process_id'];
							$info1['j_process_type']	=$row['pr_process_type'];
							$info1['j_pr_process_no']	=$POST['pr_process_no'];
							$info1['j_vendor']			=$POST['pr_vender_id'];
							$info1['j_chalan_no']		=$POST['pr_chalan_no'];
							$info1['j_qty']				=$sub_qty;
							$info1['j_ref_id']			=$row['p_ref_id'];
							$info1['j_alloc_process_id']=$row['p_id'];
							$info1['process_unit']		=$row['process_unit'];
							$info1['pr_rate']			=$POST['pr_rate'];
							
							$info1['cdate']				= date("Y-m-d H:i:s");
							$info1['userid']			= $_SESSION['user_id'];
							$info1['company_id']		= $_SESSION['company_id'];
							
							$inserusrid1=add_record('tbl_jobwork', $info1, $dbcon);
							
							$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '11'");
							
							$info3['process_id']			=$row['process_id'];
							$info3['p_start_time']			=$date;
							$info3['p_end_time']			='';
							$info3['p_qty']					=$sub_qty;
							$info3['pen_qty']				='';
							$info3['p_status']				='1';
							$info3['p_ref_id']				=$row['p_ref_id'];
							//$info3['p_ref_type']			=$POST['pr_chalan_no'];
							$info3['p_product_id']			=$row['p_product_id'];
							$info3['pr_process_type']		=$row['pr_process_type'];
							$info3['j_alloc_process_id']	=$row['p_id'];
							
							$info3['cdate']					= date("Y-m-d H:i:s");
							$info3['user_id']				= $_SESSION['user_id'];
							$info3['company_id']			= $_SESSION['company_id'];
							
							$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon); */
							
							$infog['jobwork_id']		= $job_id;
							$infog['p_id']				= $row['p_id'];
							$infog['qty']				= $sub_qty;
							$infog['cdate']				= date("Y-m-d H:i:s");
							$infog['userid']			= $_SESSION['user_id'];
							$infog['company_id']		= $_SESSION['company_id'];
							
							$job_p=add_record('tbl_jobwork_process', $infog, $dbcon,$branch_id);
							
							/*
							Code By Umair: 19/11/2020
							Commnet: Below code is commented coz of this is used in the end process function
							*/
							 /*if($row['previous_process_id']=="0"){
								$grn_qty=$POST['row_product_id'];
								for($k=0;$k<count($grn_qty);$k++)
								{
									$uqty=$POST['row_req_qty_one'][$k]*$sub_qty;
									
									$request_id=find_request_id($dbcon,$row['p_ref_id'],$info2['product_id']);
									
									$info2['allocate_process_id']	=$eid;
									$info2['product_id']			=$POST['row_product_id'][$k];
									$info2['unit_id']				=$POST['row_unit_id'][$k];
									$info2['used_qty']				=$uqty;
									$info2['cdate']					= date("Y-m-d H:i:s");
									$info2['user_id']				= $_SESSION['user_id'];
									$info2['company_id']			= $_SESSION['company_id'];
									
									$tbl_grn_trn_id=add_record('tbl_allocate_process_material',$info2, $dbcon);
									
									$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id,$info2['used_qty']);
									
									//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
									
									deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
								}
							}  */
							$qty=$qty-$sub_qty;
							
						}
					}
				}
			}
			
			
			
			
			
			
			/* 
			$sel1=$dbcon->query("select jobwork_id from tbl_jobwork where j_alloc_process_id='$eid'");
			$count=brp_mysqli_num_rows($sel1);
			
			if($count==0)
			{
				//pr_process_no
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
				$info1['j_alloc_process_id']=$eid;
				$info1['pr_jobwork_no']		=$POST['pr_jobwork_no'];
				$info1['process_unit']		=$POST['process_unit'];
				$info1['pr_rate']			=$POST['pr_rate'];
				
				$info1['cdate']				= date("Y-m-d H:i:s");
				$info1['userid']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				
				$inserusrid1=add_record('tbl_jobwork', $info1, $dbcon);
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '11'");
				
				$info3['process_id']			=$POST['process_id_hid'];
				$info3['p_start_time']			=$date;
				$info3['p_end_time']			='';
				$info3['p_qty']					=$POST['machine_no'];
				$info3['pen_qty']				='';
				$info3['p_status']				='1';
				$info3['p_ref_id']				=$POST['request_no'];
				//$info3['p_ref_type']			=$POST['pr_chalan_no'];
				$info3['p_product_id']			=$POST['product_id_hid'];
				$info3['pr_process_type']		=$POST['process_type_hid'];
				$info3['j_alloc_process_id']	=$eid;
				
				$info3['cdate']					= date("Y-m-d H:i:s");
				$info3['user_id']				= $_SESSION['user_id'];
				$info3['company_id']			= $_SESSION['company_id'];
				
				$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon);
				
			}
			
			$info3['process_id']			=$POST['process_id_hid'];
			$info3['p_start_time']			=$date;
			$info3['p_end_time']			='';
			$info3['p_qty']					=$POST['machine_no'];
			$info3['pen_qty']				='';
			$info3['p_status']				='1';
			$info3['p_ref_id']				=$POST['request_no'];
			//$info3['p_ref_type']			=$POST['pr_chalan_no'];
			$info3['p_product_id']			=$POST['product_id_hid'];
			$info3['pr_process_type']		=$POST['process_type_hid'];
			$info3['j_alloc_process_id']	=$eid;
			
			$info3['cdate']					= date("Y-m-d H:i:s");
			$info3['user_id']				= $_SESSION['user_id'];
			$info3['company_id']			= $_SESSION['company_id'];
			
			$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon);
			
			if($POST['previous_process_id']=="0"){
				$grn_qty=$POST['j_pr_job_id'];
			//var_dump(count($grn_qty));
					//var_dump("123");
				for($k=0;$k<count($grn_qty);$k++)
				{
					
					$info2['allocate_process_id']	=$eid;
					$info2['product_id']			=$POST['j_pr_job_id'][$k];
					//$info2['qty_need_for_single']	=$inserpoid;
					//$info2['total_req_qty']			=$POST['grn_qty'][$k];
					$info2['unit_id']				=$POST['j_unit_id'][$k];
					$info2['used_qty']				=$POST['j_usable'][$k];
					$info2['cdate']					= date("Y-m-d H:i:s");
					$info2['user_id']				= $_SESSION['user_id'];
					$info2['company_id']			= $_SESSION['company_id'];
					
					$tbl_grn_trn_id=add_record('tbl_allocate_process_material',$info2, $dbcon);
					//var_dump($info2['used_qty']);
					$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id,$info2['used_qty']);
					
					$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
					
					deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
					
					
				}
				
				
			} */
			//var_dump("fdsa");

			/*
				Code By Umair : 15/12/2020
				Comment: Update  actual_start_date in tbl_resource_schedule
			*/
			$resource_sch_where = '';	
			if(isset($_SESSION['resource_id']) && $_SESSION['resource_id']!=""){
				$resource_sch_where = ' and resource_id = "'.$_SESSION['resource_id'].'" ';
			}	
			$resource_schedule_sql = 'select * from tbl_resource_schedule where process_id="'.$POST['process_id_hid'].'" and p_product_id="'.$POST['product_id_hid'].'" and work_status="0" and company_id="'.$_SESSION['company_id'].'" '.$resource_sch_where.' ';

			$resource_schedule_exec=$dbcon->query($resource_schedule_sql);

			$entered_qty = (float)$POST['machine_no'];
			while($resource_schedule_data=brp_mysqli_fetch_assoc($resource_schedule_exec)){
				$resource_schedule_id = $resource_schedule_data['resource_schedule_id'];

				$p_qty = $resource_schedule_data['p_qty']; 
				$pen_qty = $resource_schedule_data['pen_qty']; 
				$start_qty = $resource_schedule_data['start_qty']; 

				$sub_qty_val=($resource_schedule_data['p_qty']-$resource_schedule_data['start_qty']);
				$sub_qty_val= (float)$sub_qty_val;
				if($entered_qty!="" && $entered_qty>0){

					if($start_qty==0 || $start_qty==''){
						$actual_start_date = " ,actual_start_date='".date('Y-m-d H:i:s')."'";
					}
					
					if($sub_qty_val >= $entered_qty){
						
						$update_sql = "UPDATE tbl_resource_schedule SET start_qty = start_qty+'".$entered_qty."' , process_qty = '".$entered_qty."', work_status='1' $actual_start_date WHERE resource_schedule_id = '".$resource_schedule_id."' ";
						$dbcon->query($update_sql);

						$entered_qty=$entered_qty-$entered_qty;
					}else{
						$update_sql = "UPDATE tbl_resource_schedule SET start_qty = start_qty+'".$sub_qty_val."' , process_qty = '".$sub_qty_val."', work_status='1' $actual_start_date WHERE resource_schedule_id = '".$resource_schedule_id."' ";
						$dbcon->query($update_sql);
						
						$entered_qty=$entered_qty-$sub_qty_val;
					}
				}		

				/*if($entered_qty==$pen_qty){
					$start_qty = $entered_qty;
				}elseif($entered_qty > $pen_qty){
					$entered_qty = $entered_qty - $pen_qty;
					$start_qty = $pen_qty;
				}*/

				/*$start_qty = $start_qty + $entered_qty;

				if($start_qty==0 || $start_qty==''){
					$update_sql = "UPDATE tbl_resource_schedule SET start_qty = '".$start_qty."' , process_qty = '".$entered_qty."', actual_start_date='".date('Y-m-d H:i:s')."' , work_status='1' WHERE resource_schedule_id = '".$resource_schedule_id."' ";
					$dbcon->query($update_sql);
				}else{
					$update_sql = "UPDATE tbl_resource_schedule SET start_qty = '".$start_qty."' , process_qty = '".$entered_qty."', work_status='1' WHERE resource_schedule_id = '".$resource_schedule_id."' ";
					$dbcon->query($update_sql);
				}
				$dbcon->query($update_sql);
				$entered_qty = $entered_qty - $pen_qty; */

			}
			
			
			echo "1";
		}
		else if(brp_strtolower($POST['mode']) == "show_material_list_new") {
			$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
				WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['eid'].")";
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
				$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id);
				
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
		else if(brp_strtolower($POST['mode']) == "show_material_list") {
			
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
			$aColumns = array('itm.product_id','itm.product_type','itm.product_purchase_rate', 'itm.product_name', 'itm.product_min_stock', 'itm.product_opening','bt.product_act_qty','(bt.product_act_qty/bt.product_base_qty) as bom_qty','u.unit_name','IFNULL(qcqty,0)','bt.product_base_unit');
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
				
				//$cur_stock=get_current_stock($dbcon,$row['product_id']);
				//$cur_stock=get_current_stock_new($dbcon,$row['product_id'],$row['product_base_unit']);
				
				$cur_stock=reserve_stock($dbcon,$row['product_id'],$row['product_base_unit']);
				
				$row_data = array();
				
				$op_stock=$row['product_opening'];
				//$total=$op_stock+$row['qcqty'];
				$cl_stock=$total;
				
				$total=$cur_stock;
				if($total<=0){ $total=0; }
				
				//$required_qty=$pqty*$row['product_act_qty'];
				$required_qty=$pqty*$row['bom_qty'];
				
				//$get_warehose_qty=get_warehouse_qty($dbcon,$row['product_id'],$required_qty,$eid);
				
				if($total>$required_qty)
				{
					$usable=$required_qty;
				}
				else
				{
					//$usable=round(($total/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
					$usable=round(($total/$row['bom_qty']),0,PHP_ROUND_HALF_DOWN);
					//$usable=$usable*$row['product_act_qty'];
					$usable=$usable*$row['bom_qty'];
				}
				
				
				//$machine_make=round(($usable/$row['product_act_qty']),0,PHP_ROUND_HALF_DOWN);
				$machine_make=round(($usable/$row['bom_qty']),0,PHP_ROUND_HALF_DOWN);
				
				$pr_amount=$row['product_purchase_rate']*$usable;
				
				$row_data[] = $row['product_name']."
					
					<input type='hidden' class='form-control text-box j_pr_job_id' name='j_pr_job_id[]' id='j_pr_job_id$id' value='".$row['product_id']."' />"."<input type='hidden' class='form-control text-box' name='j_ptype[]' id='j_ptype$id' value='".$row['product_type']."' />"."
					<input type='hidden' class='form-control text-box' name='j_prate[]' id='j_prate$id' value='".$row['product_purchase_rate']."' />"."
					<input type='hidden' class='form-control text-box' name='j_pamount[]' id='j_pamount$id' value='".$pr_amount."' />
					<!--<input type='hidden' class='form-control' name='product_act_qty[]' id='product_act_qty$id' value='".$row['product_act_qty']."' />-->
					<input type='hidden' class='form-control' name='product_act_qty[]' id='product_act_qty$id' value='".$row['bom_qty']."' />";
				
				//$row_data[] = $row['product_act_qty'];
				$row_data[] = $row['bom_qty'];
				
				$row_data[] = $required_qty.'<!--<a onClick="get_warehose_deduction(\''.$row['product_id'].'\',\''.$row['product_name'].'\',\''.$required_qty.'\');"><i class="fa fa-building" style="color:green;float:right;border:solid black thin" data-original-title="Warehouse Deduction Details" data-toggle="tooltip" data-placement="top"></i></a><br>-->'.$get_warehose_qty;
				
				$row_data[] = $total;
				$usable1=$row['bom_qty']*$POST['machine_no'];
				//$usable1=$row['product_act_qty']*$POST['machine_no'];
				$row_data[] = $usable1."<input type='hidden' class='form-control text-box' name='j_usable[]' id='j_usable$id' value='".$usable1."' />
				<input type='hidden' name='j_unit_id[]' id='j_unit_id$id' value='".$row['product_base_unit']."' />
				";
				
				$row_data[] = $row['unit_name']."<input type='hidden' name='machine_make[]' id='machine_make$id' value='".$machine_make."' />";
	   
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
				
		}
		
		else if(brp_strtolower($POST['mode']) == "show_material_list_allocate") {
			
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
		
		else if(brp_strtolower($POST['mode']) == "show_godown_product_detail") {
			
			$pro_id=$POST['pro_id'];
			$req_qty=$POST['req_qty'];
			
			$cnt=1;$str='';
			$selb=$dbcon->query("select gd.*,gps.product_stock,gps.priority from mst_godown as gd left join tbl_branch_product_stock as gps  on gd.gd_id=gps.branch_id where gd.g_status=0 and gps.product_id='$pro_id' ");
			while($rb=brp_mysqli_fetch_array($selb))
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
		else if(brp_strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=11 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(brp_strtolower($POST['mode'])== "load_invoiceno")
		{
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
		
		
		else if(brp_strtolower($POST['mode'])== "get_series_no_jobwork")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=12 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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
		else if(brp_strtolower($POST['mode'])== "product_load")
		{
			$control = 'process_id';
			$blank = '';
			$str = '';
			$str.= '<form id="machine_configuration_add" role="form" method="post" novalidate enctype="multipart/form-data">
					<div class="row">
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-2"></div>
						<div class="col-md-9">
							<div class="form-group">
								<label class="col-md-4 control-label">Product Name*</label>
								<div class="col-md-8">
	   								<select class="select2 form-control" id="product_id_model" name="product_id" onchange="get_related_process(this.value,\''.$control.'\',\''.$_REQUEST['product_id'].'\')" placeholder="Choose Products" required>
	 									'.getproduct($dbcon,$_REQUEST['product_id']).'
	   								</select>
   								</div>
							</div>
						</div>	
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-2"></div>
						<div class="col-md-9">
							<div class="form-group">
								<label class="col-md-4 control-label">Process Name*</label>
								<div class="col-md-8" id="process_data_select">
									<select class="select2 form-control" name="process_id" id="process_id_model" required>
										'.get_process_by_product_id($dbcon,$_REQUEST['product_id'],$_REQUEST['process_id']).'	
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-2"></div>
						<div class="col-md-9">
							<div class="form-group">
								<label class="col-md-4 control-label">Upload Images</label>
								<div class="col-md-8">
									<input type="file" id="upload_machine_file" name="upload_machine_file[]" multiple/>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12" style="padding-top: 12px;">
						<div class="col-md-2"></div>
						<div class="col-md-9">
							<div class="form-group">
								<label class="col-md-4 control-label">Short Count*</label>
								<div class="col-md-8">
									<input class="form-control" type="text" name="short_count" id="short_count" placeholder="Short Count" onkeypress="return isNumberKey(event)" value="0" />
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="mode" id="mode" value="Add" />
					<div class="col-md-12" style="margin-top:10px;">
						<div class="col-md-6 col-md-offset-5">  	
							<button type="submit" id="submit_btn" class="btn btn-success">Submit</button>
						</div>	
					</div>';
			$str.=	'</div></form>';
			$resp['html_resp']=$str;
        	echo json_encode($resp);		

		}
		else if(brp_strtolower($POST['mode'])== "load_process")
		{
			$product='';
			$product_id = $_REQUEST['id'];
			$product_qry="select p.process_id,p.process_priority,pr.process_name from tbl_product_process as p left join process_mst as pr on pr.process_id=p.process_id where p.status = 0 and p.product_id='".$product_id."' order by p.process_priority"; 
			$product_data = $dbcon->query($product_qry);	
			$product.= '<select class="select2 form-control" name="process_id" id="process_id"><option value="">Select Process</option>';	
			while($r=brp_mysqli_fetch_assoc($product_data))
			{	
				$sel='';	
				if($r['process_id']==$product_id)
				{$sel='selected="selected"';}
				$product .= '<option '.$sel.' value="'.$r['process_id'].'">'.$r['process_name'].'</option>';
			}
			$product .= '</select>';						
			echo $product;
			die;
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