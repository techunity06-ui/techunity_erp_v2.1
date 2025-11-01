<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(brp_strtolower($POST['mode']) == "graphsalesorderdata") {
	$arr=array();
	$sales=array();
	$work=array();
	$query_date = date('d-m-Y');
	$firstday=date('Y-m-d', strtotime($_POST['start_date']));
	$lastday=date('Y-m-d', strtotime($_POST['end_date']));
	$where = '';
	if(!empty($_POST['product_id'])){
		$where.=' AND tso.product_id IN ('.$_POST['product_id'].')';
	}
	$query="SELECT  sum(tso.product_qty) as y,p.product_name as label,tso.product_id,ts.sales_order_id
	FROM tbl_sales_ordertrn as tso
	left JOIN tbl_sales_order as ts ON tso.sales_order_id=ts.sales_order_id
	left JOIN product_mst as p ON p.product_id=tso.product_id
	where tso.sales_ordertrn_status=0
	";
	$query.=" and (ts.cdate>='".$firstday."' and ts.cdate<='".$lastday."') or ( ts.mdate>='".$firstday."' and  ts.mdate<='".$lastday."' )".$where;
	$query.=" group by tso.product_id";
	$result1=$dbcon->query($query);
	if(brp_mysqli_num_rows($result1)>0)
	{
		while($re=brp_mysqli_fetch_assoc($result1))
		{
			$wodata=getwoqtybyprdctid($dbcon,$re['product_id']);
			if(count($wodata)>0){
				$re['lable']=(int)$wodata['wo'];
			}else{
				$re['lable']=0;
			}
			$re['pending']=(int)$re['y']-(int)$re['wo'];
			$re['y']=(int)$re['y'];
			array_push($arr,$re);
		}
	}
	echo json_encode($arr);
	exit;
}
else if(brp_strtolower($POST['mode']) == "graphworkorderdata") {
	$arr=array();
	$query_date = date('d-m-Y');
		// First day of the month.
	$firstday=date('Y-m-01', strtotime($query_date));
		// Last day of the month.
	$lastday=date('Y-m-t', strtotime($query_date));
	$query="SELECT  sum(tso.product_qty) as y,p.product_name as label
	FROM tbl_sales_ordertrn as tso
	left JOIN tbl_sales_order as ts ON tso.sales_order_id=ts.sales_order_id
	left JOIN product_mst as p ON p.product_id=tso.product_id
	where tso.sales_ordertrn_status=0
	";
	$query.=" and ts.cdate>='".$firstday."' and ts.cdate<='".$lastday."'";
	$query.="group by tso.product_id order by ";
	$result1=$dbcon->query($query);
	if(brp_mysqli_num_rows($result1)>0)
	{
		while($re=brp_mysqli_fetch_assoc($result1))
		{
			$re['y']=(int)$re['y'];
			array_push($arr,$re);
		}
	}
	echo json_encode($arr);
	exit;
}
else if(brp_strtolower($POST['mode']) == "getproducts") {
	if($POST['categoryid']!=''){
		echo getproductsbycategory($dbcon,$POST['categoryid']);
	}else{
		echo getfinishedproducts($dbcon,$POST['categoryid']);
	}	
	exit;
}
//group chart
else if(brp_strtolower($POST['mode']) == "groupchart") {
	$mrp_count[]=getmrpcount($dbcon,$POST['product_category'],$POST['product_id']);
	$mrp_count[]=getpocount($dbcon,$POST['product_category'],$POST['product_id']);
	$mrp_count[]=getgrncount($dbcon,$POST['product_category'],$POST['product_id']);
	//$mrp_count[]=15;
	$mrp_count[]=getqccount($dbcon,$POST['product_category'],$POST['product_id']);
	$mrp_count[]=processinside($dbcon,$POST['product_category'],$POST['product_id']);
	$mrp_count[]=processoutside($dbcon,$POST['product_category'],$POST['product_id']);
	$a=array();
	//static x-axis val
	$arr=['MRP','PO','GRN','QC','Process Inside','Outside'];
	$i=0;
	foreach($mrp_count as  $value) {
		$a[$i]['label']=  $arr[$i];
		$a[$i]['y']= $value;
		$i++;
	}
    echo json_encode($a);
	exit;
}
else if(brp_strtolower($POST['mode']) == "get_work_order") {

	$sp_id = $POST['sp_id'];

	 $cjobwork111='select sp.po_req_no,sp.sp_id from tbl_request_product as j 
					left join tbl_set_main_process as sp on sp.sp_id=j.sp_id
					where  main_request=1 and rp_pid='.$POST['product_id'];
			$cjobwork111=$dbcon->query($cjobwork111);
			$c_mrn_hh11=mysqli_num_rows($cjobwork111);
			$str='<option value="" >--Choose Work Order--</option>';
			while($c_jobwork11=mysqli_fetch_assoc($cjobwork111)){
				$sel = '';
					if($c_jobwork11['sp_id']==$sp_id){
						$sel = 'selected="selected"';
					}
				$str .= '<option '.$sel.' value="'.$c_jobwork11['sp_id'].'">'
				.$c_jobwork11['po_req_no'].'</option>';
			}
		echo $str;
}
else if(strtolower($POST['mode']) == "load_work_order_status") {
			//$date=get_sdate($POST['c_year']);
	$query1="select count(req.rp_id) as total_work_order from tbl_request_product as req
	where req.sp_id=".$POST['work_order_id1'];

		$result1=$dbcon->query($query1);
	$row1=mysqli_fetch_assoc($result1);
	

	$query2="select count(req.rp_id) as total_done_jobwork from tbl_request_product as req
	where job_card_status=3 and req.sp_id=".$POST['work_order_id1'];
	$result2=$dbcon->query($query2);
	$row2=mysqli_fetch_assoc($result2);
	

	$query3="select count(req.rp_id) as total_pending_jobwork from tbl_request_product as req
	where job_card_status=1 and req.sp_id=".$POST['work_order_id1'];
	$result3=$dbcon->query($query3);
	$row3=mysqli_fetch_assoc($result3);
	

	$query4="select count(req.rp_id) as total_done_indent from tbl_request_product as req
	where indent_status=3 and req.sp_id=".$POST['work_order_id1'];
	$result4=$dbcon->query($query4);
	$row4=mysqli_fetch_assoc($result4);
	

	$query5="select count(req.rp_id) as total_pending_indent from tbl_request_product as req
	where indent_status=1 and req.sp_id=".$POST['work_order_id1'];
	$result5=$dbcon->query($query5);
	$row5=mysqli_fetch_assoc($result5);


	$total_pending=$row5['total_pending_indent']+$row3['total_pending_jobwork'];
	$total_done=$row2['total_done_jobwork']+$row4['total_done_indent'];
	$total=$row1['total_work_order'];

	$pening_per=($total_pending*100)/$total;
	$done_per=($total_done*100)/$total;

	
	//$row1[0]['sql']=$query;	
	$rowre[0]['label']="Done";
	$rowre[0]['y']=intval($done_per);

	$rowre[1]['label']="Pending";
	$rowre[1]['y']=intval($pening_per);			
		
	echo json_encode($rowre);
}
else if(brp_strtolower($POST['mode']) == "get_job_work") {
	$rp_id = $POST['rp_id'];
	$cjobwork111='select job_card_no,rp_id from tbl_request_product as j 
					where job_card_status=1 and rp_pid='.$POST['product_id'];
			$cjobwork111=$dbcon->query($cjobwork111);
			$c_mrn_hh11=mysqli_num_rows($cjobwork111);
			$str='<option value="" >--Choose Job Card--</option>';
			while($c_jobwork11=mysqli_fetch_assoc($cjobwork111)){

				$sel = '';
					if($c_jobwork11['rp_id']==$rp_id){
						$sel = 'selected="selected"';
					}
				$str .= '<option '.$sel.'  value="'.$c_jobwork11['rp_id'].'">'.$c_jobwork11['job_card_no'].'</option>';
			}
		echo $str;
}

else if(strtolower($POST['mode']) == "load_job_work_status") {
$pending=0;$done=0;$pending=0;
	 $query1="select * from tbl_request_product as req
	where req.rp_id=".$POST['job_work_id'];
	$result1=$dbcon->query($query1);
	while($row1=mysqli_fetch_assoc($result1)){
		if($row1['job_card_status']=="3"){
			$done++;
			
		}else if($row1['job_card_status']=="1"){
			$pending++;
			
		}

		if($row1['indent_status']=="3"){
			$done++;
			
		}else if($row1['indent_status']=="1"){
			$pending++;
			
		}	

		
		 $done=done_job_work_co($dbcon,$row1['rp_id'],$done);
		 $pending=pending_job_work_co($dbcon,$row1['rp_id'],$pending);
		
	}
	//echo $done;
	//echo $pending;
	$total=$done+$pending;
	
	$pening_per=($pending*100)/$total;
	$done_per=($done*100)/$total;

	
	//$row1[0]['sql']=$query;	
	$rowre[0]['label']="Done";
	$rowre[0]['y']=intval($done_per);

	$rowre[1]['label']="Pending";
	$rowre[1]['y']=intval($pening_per);			
		
	echo json_encode($rowre);
}
else if(strtolower($POST['mode']) == "get_production_qty_data") {
	$arr = array();
	$qry = "select IFNULL(sum(finish_used_qty),0) as complete_qty from tbl_request_product where status = 0 and in_process_qty != 0";
	$row = brp_mysqli_fetch_assoc($dbcon->query($qry));
	$qry1 = "select IFNULL(sum(in_process_qty),0) as total_pending_qty from tbl_request_product where status = 0";
	$row1 = brp_mysqli_fetch_assoc($dbcon->query($qry1));
	$qry2 = "select (IFNULL(sum(bt.reject_qty),0)) as total_reject_qty from tbl_batch_data as bt
			left join tbl_grn_trn as grn on grn.grn_trn_id = bt.grn_trn_id
	 where bt.status = 0 and grn.ref_type != 2";
	$row2 = brp_mysqli_fetch_assoc($dbcon->query($qry2));

	$arr['completed'] = $row['complete_qty'];
	$arr['pending'] = $row1['total_pending_qty'] - $row['complete_qty'];
	$arr['reject'] = $row2['total_reject_qty'];

	echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "get_production_yearly_data") {
	$arr = array();
	$arr['planning'] = array();
	$arr['completed'] = array();
	$arr['rejected'] = array();
	$arr['pending'] = array();

	$q = "SELECT YEAR(cdate) as year from tbl_request_product where YEAR(cdate) != 0 GROUP BY YEAR(cdate) ORDER BY YEAR(cdate) desc LIMIT 3";
	$res = $dbcon->query($q);
	$i = 0;
	while ($row_y = brp_mysqli_fetch_assoc($res)) {
		$qry = "select IFNULL(sum(finish_used_qty),0) as complete_qty from tbl_request_product where status = 0 and in_process_qty != 0";
		$row = brp_mysqli_fetch_assoc($dbcon->query($qry));
		$qry1 = "select IFNULL(sum(in_process_qty),0) as total_qty from tbl_request_product where status = 0";
		$row1 = brp_mysqli_fetch_assoc($dbcon->query($qry1));
		$qry2 = "select (IFNULL(sum(bt.reject_qty),0)) as total_reject_qty from tbl_batch_data as bt
				left join tbl_grn_trn as grn on grn.grn_trn_id = bt.grn_trn_id
		 where bt.status = 0 and grn.ref_type != 2";
		$row2 = brp_mysqli_fetch_assoc($dbcon->query($qry2));

		$total_qty = (float) $row1['total_qty'];
		$total_complete_qty = (float) $row['complete_qty'];
		$total_reject_qty = (float) $row2['total_reject_qty'];
		$total_pending_qty = (float) $total_qty - $total_complete_qty;

		$arr['planning'][$i] = array("label" => $row_y['year'] , "y" => $total_qty);
		$arr['completed'][$i] = array("label" => $row_y['year'] , "y" => $total_complete_qty);
		$arr['pending'][$i] = array("label" => $row_y['year'] , "y" => $total_pending_qty);
		$arr['rejected'][$i] = array("label" => $row_y['year'] , "y" => $total_reject_qty);
   		$i++;
	}
	// print_r($arr);

	 // echo json_encode($data_points, JSON_NUMERIC_CHECK);	
	echo json_encode($arr);
}


else if(strtolower($POST['mode']) == "get_complete_vs_reject") {
	// $row1 = array();
	$product_id = $POST['product_id'];
	$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=''; 
		$where.=" and rp.cdate>='".date('Y-m-d',strtotime($s_date[0]))."' AND rp.cdate<='".date('Y-m-d',strtotime($s_date[1]))."'";

		if($product_id != ""){
			$where.=" and rp.rp_pid in (".$product_id.")";	
		}
	
	$qry = "select IFNULL(sum(rp.finish_used_qty),0) as complete_qty,pro.product_name from tbl_request_product as rp left join product_mst as pro on pro.product_id = rp.rp_pid where rp.status = 0 and rp.in_process_qty != 0 ". $where ." group by rp.rp_pid LIMIT 5";
		$result = $dbcon->query($qry);



		/*while($row = brp_mysqli_fetch_assoc($result)){
			$qry2 = "select (IFNULL(sum(bt.reject_qty),0)) as total_reject_qty from tbl_batch_data as bt
				left join tbl_grn_trn as grn on grn.grn_trn_id = bt.grn_trn_id
		 		where bt.status = 0 and grn.ref_type != 2 where bt.product_id = " . $row['rp_pid'];
			$row2 = brp_mysqli_fetch_assoc($dbcon->query($qry2));


			$total_complete_qty = (float) $row['complete_qty'];
			$total_reject_qty = (float) $row2['total_reject_qty'];
			

			$arr[$row['product_name']] = array("label" => 'Completed'  , "y" => $total_complete_qty);
			$arr['rejected'] = array("label" => 'Reject' , "y" => $total_reject_qty);
		}*/
		$row = array();
		$i=0;
		while($chart=brp_mysqli_fetch_assoc($result))
		{	$qry2 = "select (IFNULL(sum(bt.reject_qty),0)) as total_reject_qty from tbl_batch_data as bt
				left join tbl_grn_trn as grn on grn.grn_trn_id = bt.grn_trn_id
		 		where bt.status = 0 and grn.ref_type != 2 where bt.product_id = " . $chart['rp_pid'];
			$row2 = brp_mysqli_fetch_assoc($dbcon->query($qry2));


			$total_complete_qty = (float) $chart['complete_qty'];
			$total_reject_qty = (float) $row2['total_reject_qty'];

			
			$row[]= $chart['product_name'];
			$row[$chart['product_name']][]=intval($chart['complete_qty']);
			$row[$chart['product_name']][]=intval($chart['total_reject_qty']);
			
			$i++;
		}		
					
		echo json_encode($row);
		// echo json_encode($arr);
}else if(strtolower($POST['mode']) == "load_workorder_piechart") {

	$product_id = $POST['product_id'];
	$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where=''; 
		$where.=" and rp.cdate>='".date('Y-m-d',strtotime($s_date[0]))."' AND rp.cdate<='".date('Y-m-d',strtotime($s_date[1]))."'";

		if($product_id != ""){
			$where.=" and rp.rp_pid in (".$product_id.")";	
		}
			//$date=get_sdate($POST['c_year']);
		$query1="select SUM(req.in_process_qty) as total_work_order from tbl_request_product as req
		where req.main_request = 1 and status =0";
		$result1=$dbcon->query($query1);
		$row1=mysqli_fetch_assoc($result1);


		$qry = "select IFNULL(sum(finish_used_qty),0) as complete_qty from tbl_request_product where status = 0 and in_process_qty != 0";
		$row3 = brp_mysqli_fetch_assoc($dbcon->query($qry));


		$qry4 = "select IFNULL(sum(in_process_qty),0) as total_qty from tbl_request_product where status = 0";
		$row4 = brp_mysqli_fetch_assoc($dbcon->query($qry4));

	/*	$qry5 = "select IFNULL(sum(start_qty),0) as start_qty from tbl_allocate_process where p_status = 1";
		$row5 = brp_mysqli_fetch_assoc($dbcon->query($qry5));

		$qry6 = "select IFNULL(sum(start_qty),0) as stop_qty from tbl_allocate_process where p_status = 3";
		$row6 = brp_mysqli_fetch_assoc($dbcon->query($qry6));*/

		$qry2 = "select (IFNULL(sum(bt.reject_qty),0)) as total_reject_qty from tbl_batch_data as bt
			left join tbl_grn_trn as grn on grn.grn_trn_id = bt.grn_trn_id
	 where bt.status = 0 and grn.ref_type != 2";
	$row2 = brp_mysqli_fetch_assoc($dbcon->query($qry2));
	
		$total=$row4['total_qty'];
		$total_complete_qty=$row3['complete_qty'];
		$total_planning_qty=$row4['total_qty'] - $row3['complete_qty'];
		$total_reject_qty=$row2['total_reject_qty'];
		/*$total_start=$row5['start_qty'];
		$total_stop=$row6['stop_qty'];*/
	
				/*var_dump($total);
				var_dump($total_complete_qty);
				var_dump($total_planning_qty);*/
			/*var_dump($total_start);
			var_dump($total_stop);
*/
		$arr_complete_qty=($total_complete_qty*100)/$total;
		$arr_pending_qty=($total_planning_qty*100)/$total;
		$arr_reject_qty=($total_reject_qty*100)/$total;
		/*$arr_start_qty=($total_start*100)/$total;
		$arr_stop_qty=($total_stop*100)/$total;*/

		
		//$row1[0]['sql']=$query;	
		$rowre[0]['label']="Completed";
		$rowre[0]['y']=floatval(number_format($arr_complete_qty,2));

		$rowre[1]['label']="Pending";
		$rowre[1]['y']=floatval(number_format($arr_pending_qty,2));

		$rowre[2]['label']="Reject";
		$rowre[2]['y']=floatval(number_format($arr_reject_qty,2));
	
		echo json_encode($rowre);
}
else if(brp_strtolower($POST['mode']) == "get_all_data_graph") {
	
	 $qry1 = "select IFNULL(SUM(in_process_qty),0) as wororder_qty FROM tbl_request_product where main_request = 1  and status = 0";
	$row1 = brp_mysqli_fetch_assoc($dbcon->query($qry1));

	  $qry2 = "select IFNULL(SUM(in_process_qty),0) as jobcard_qty FROM tbl_request_product where  status != 2 and job_card_status in(0,3)";
	$row2 = brp_mysqli_fetch_assoc($dbcon->query($qry2));

	 $qry3 = "select IFNULL((SUM(p_qty) - SUM(start_qty)),0) as start_pending_qty FROM tbl_allocate_process where p_status != 2";
	$row3 = brp_mysqli_fetch_assoc($dbcon->query($qry3));

	 $qry4 = "select IFNULL(SUM(trn.product_base_qty),0) as end_pending_qty from tbl_job_work_trn as trn left join tbl_job_work as job on job.job_work_id = trn.job_work_id where trn.grn_complete_status = 0 and trn.job_work_trn_status != 2 and job.job_work_type = 1";
	$row4 = brp_mysqli_fetch_assoc($dbcon->query($qry4));

	 $qry5 = "select IFNULL(SUM(trn.product_base_qty),0) as jobwork_qty from tbl_job_work_trn as trn left join tbl_job_work as job on job.job_work_id = trn.job_work_id where trn.grn_complete_status = 0 and trn.job_work_trn_status != 2 and job.job_work_type = 2";
	$row5 = brp_mysqli_fetch_assoc($dbcon->query($qry5));

	 $qry6 = "select IFNULL(SUM(strn.product_base_qty),0) as grn_qty from tbl_job_work_sub_trn as strn 
	left join tbl_job_work_trn as trn on trn.job_work_trn_id = strn.job_work_trn_id 
	left join tbl_job_work as job on job.job_work_id = trn.job_work_id 
	where strn.grn_complete_status = 1 and trn.job_work_trn_status != 2 and strn.job_work_sub_trn_status != 2 and job.job_work_type = 2";
	$row6 = brp_mysqli_fetch_assoc($dbcon->query($qry6));

	 $qry7 = "select IFNULL(SUM(batch_qty),0) as qc_qty from tbl_batch_data where status = 0 and qc_status = 0";
	$row7 = brp_mysqli_fetch_assoc($dbcon->query($qry7));

	$mrp_count[]=$row1['wororder_qty'];
	$mrp_count[]=$row2['jobcard_qty'];
	$mrp_count[]=$row3['start_pending_qty'];
	$mrp_count[]=$row4['end_pending_qty'];
	$mrp_count[]=$row5['jobwork_qty'];
	$mrp_count[]=$row6['grn_qty'];
	$mrp_count[]=$row7['qc_qty'];
	
	// var_dump($mrp_count);
	
	$a=array();
	//static x-axis val
	$arr=['Workorder','Jobcard','Inhouse Start Pending','Inhouse End Pending','Jobwork','GRN','QC'];
	$i=0;
	foreach($mrp_count as  $value) {
		$a[$i]['label']=  $arr[$i];
		$a[$i]['y']= $value;
		$i++;
	}
    echo json_encode($a);
	exit;
}

function  done_job_work_co($dbcon,$rp_id,$done){
	 $query1="select * from tbl_request_product as req
	where req.perent_id=".$rp_id;
	$result1=$dbcon->query($query1);
	while($row1=mysqli_fetch_assoc($result1)){
		if($row1['job_card_status']=="3"){
			$done++;
			
		}else if($row1['job_card_status']=="1"){
			//$pending++;
			
		}

		if($row1['indent_status']=="3"){
			$done++;
			
		}else if($row1['indent_status']=="1"){
			//$pending++;
		}	
		$done=done_job_work_co($dbcon,$row1['rp_id'],$done);
		//echo $done;
	}
	return $done; 
}

function  pending_job_work_co($dbcon,$rp_id,$pending){
	$query1="select * from tbl_request_product as req
	where req.perent_id=".$rp_id;
	$result1=$dbcon->query($query1);
	while($row1=mysqli_fetch_assoc($result1)){
		if($row1['job_card_status']=="3"){
			//$done++;
			
		}else if($row1['job_card_status']=="1"){
			$pending++;
			
		}

		if($row1['indent_status']=="3"){
			//$done++;
			
		}else if($row1['indent_status']=="1"){
			$pending++;
			
		}	

		$pending=done_job_work_co($dbcon,$row1['rp_id'],$pending);
		
	}
	return $pending;
}

?>