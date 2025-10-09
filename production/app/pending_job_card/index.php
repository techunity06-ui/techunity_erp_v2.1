<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    PRODUCTION_PENDING_JOBCARD_SLUG_CREATE
	]);
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(brp_strtolower($POST['mode']) == "generate_report") {
				$s_date=date('Y-m-d',strtotime($POST['date']));
				$branch=$POST['branch_id'];
				$st_type=$POST['st_type'];
				
				$where='';
				
				//$where.=" and pro.branch_id='$branch'";
		
		/*HAVING j_qty>tqty
		,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
					where j.purchaseorder_id=jo.jobwork_id and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty*/
					
			if(!empty($POST['product_id'])){
				$ser_pro=" and jo.j_product_id=".$POST['product_id'];
			}
			if(!empty($POST['vender_id'])){
				$ser_ven=" and jo.j_vendor=".$POST['vender_id'];
			}
	/*  $query='select jo.*,pr.product_name,led.l_name,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
					where j.purchaseorder_id in (jo.jobwork_id) and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty from tbl_jobwork as jo 
				left join product_mst as pr on pr.product_id=jo.j_product_id
				left join tbl_ledger as led on led.l_id=jo.j_vendor
				where jo.job_close_status="0" '.$ser_pro.' '.$ser_ven.' and jo.j_process_type!=1 and jo.status="0" and jo.company_id='.$_SESSION['company_id'].'  HAVING j_qty>tqty'; */
				
				
				 $query='select jo.*,pmst.process_name,pr.product_name,tc.cat_name,led.l_name,(select COALESCE(sum(strn.product_qty),0) as tqty from tbl_grn as j 
				left join tbl_grn_trn as p on p.grn_id=j.grn_id 
				left join tbl_grn_sub_trn as strn on strn.grn_trn_id=p.grn_trn_id
				where strn.jobwork_id=jo.jobwork_id and j.grn_status=0 and strn.status=0 and j.ref_type=1 and p.grn_trn_status=0) as tqty from tbl_jobwork as jo 
				left join product_mst as pr on pr.product_id=jo.j_product_id
				left join tbl_category as tc on pr.product_category=tc.cat_id
				left join tbl_ledger as led on led.l_id=jo.j_vendor
				left join process_mst as pmst on pmst.process_id=jo.j_pr_process_id
				where jo.job_close_status="0" '.$ser_pro.' '.$ser_ven.' and jo.j_process_type!=1 and jo.status="0" and jo.company_id='.$_SESSION['company_id'].'  HAVING j_qty>tqty';
				
				/* $query_u="select COALESCE(sum(strn.product_qty),0) as tqty from tbl_grn as j 
				left join tbl_grn_trn as p on p.grn_id=j.grn_id 
				left join tbl_grn_sub_trn as strn on strn.grn_trn_id=p.grn_trn_id
				where strn.jobwork_id=jo.jobwork_id and j.grn_status=0 and strn.status=0 and j.ref_type=1 and p.grn_trn_status=0 "; */
				
				// group by jo.j_vendor,jo.j_product_id,jo.j_pr_process_id HAVING j_qty>tqty
		$rs=$dbcon->query($query);
			$str='';$i=1;
			$rel_num_rows=brp_mysqli_num_rows($rs);
			if($rel_num_rows>0){
				while($rel=brp_mysqli_fetch_assoc($rs))
				{
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
					/*$query_u="select sum(p.product_qty) as tqty from tbl_grn as j 
					left join tbl_grn_trn as p on p.grn_id=j.grn_id 
					where j.purchaseorder_id=".$rel['jobwork_id']." and grn_status=0 and ref_type=1 and grn_trn_status=0 ";
						$rs_product_u=$dbcon->query($query_u);
						$row_u=brp_mysqli_fetch_array($rs_product_u);*/
						$pending_qty=$rel['j_qty']-$rel['tqty'];
						$user_n=find_user_name($dbcon,$rel['userid']);
					$view = '';	
					if(in_array(PRODUCTION_PENDING_JOBCARD_SLUG_CREATE,$bulkAccessArray)){
						$view = '<a class="btn btn-xs btn-success" data-original-title="Add GRN" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'grn_add_job/'.$rel['jobwork_id'].'" ><i class="fa fa-plus"></i></a>';
					}	
					$str.='<tr>
							  <td style="text-align:center;">'.$i.'</td>
					
							  <td><!--<a href="'.ROOT.PRODUCTION_ROOT.'jobcard_detail/'.$rel['jobwork_id'].'">'.$rel['product_name'].'</a>-->'.$rel['product_name'].'</td>
							  <td>'.$rel['process_name'].'</td>
							  <td>'.$cat_name.'</td>
							   <td >'.$rel['l_name'].'</td>
							   <td style="text-align:right;">'.$rel['j_qty'].'</td>
							  <td style="text-align:right;">'.$pending_qty.'</td>
							   <td >'.$user_n.'</td>
							  <td style="text-align:center;">
								'.$view.'
							  </td>
							</tr>';
					$i++;					  
				}
			}
			else{
				$str.= '<tr><td colspan="9" style="text-align:center;">No Data Found !!!</td></tr>';
			}
			
			
			//echo $query;
		echo $str;
			
		}
		
		
		else if(brp_strtolower($POST['mode']) == "product_history") {
			
			$str="";$cnt=1;
			
			$str.="<thead> <tr>
					  <th width='10%' style='text-align:center;'>Sr. NO.</th>
					  <th width='10%' style='text-align:center;'>Month</th>
					  <th width='12%' style='text-align:center;'>Purchase</th>					  
					  <th width='12%' style='text-align:center;'>Sale</th>
					  <th width='12%' style='text-align:center;'>Outward</th>
					  <th width='12%' style='text-align:center;'>Inward</th>
					  <th width='12%' style='text-align:center;'>Branch Stock (IN)</th>
					  <th width='12%' style='text-align:center;'>Branch Stock (OUT)</th>
					  <th width='12%' style='text-align:center;'>Material Waste</th>

				  </tr></thead>";
				  
				  
			for($m=1;$m<=12;$m++)
			{
				$pid=$POST['pid'];
				$branch=$POST['branch'];
				$pr_name=get_pro_field($dbcon,$pid,'product_name');
				$m=str_pad($m,2,'0',STR_PAD_LEFT);
				
				//$qry2="select sum(pt.product_qty) as pqty from tbl_purchaseordertrn as pt left join tbl_purchaseorder as p on p.purchaseorder_id=pt.purchaseorder_id where MONTH(p.purchaseorder_date)='$m' and pt.product_id='$pid'" ;
				
				$str.="<tr>
				
					<th>".$cnt."</th>
					<th><a onclick='get_product_history(\"".$pid."\",\"".$pr_name."\",\"".$m."\",\"date_history\",\"".$branch."\")' href='#'>".date('F', mktime(0, 0, 0, $m, 1))."</a></th>
					<th>".get_purchase_total_stock($dbcon,$pid,$m,$branch)."</th>
					<th>".get_sale_total_stock($dbcon,$pid,$m,$branch)."</th>
					<th>".get_jobwork_Outwrd_total_stock($dbcon,$pid,$m,$branch)."</th>
					<th>".get_jobwork_inward_total_stock($dbcon,$pid,$m,$branch)."</th>
					<th>".get_jobwork_bstockin_total_stock($dbcon,$pid,$m,$branch)."</th>
					<th>".get_jobwork_bstockout_total_stock($dbcon,$pid,$m,$branch)."</th>
					<th>".get_jobwork_waste_total_stock($dbcon,$pid,$m,$branch)."</th>
				</tr>";
				
				
				$cnt++;
			}
			
			echo $str;
			
		}
		
		else if(brp_strtolower($POST['mode']) == "product_history_by_date") {
			
			$pid=$POST['pid'];
			$mid=$POST['mid'];
			$branch=$POST['branch'];
			$year=date("Y");
			$pr_name=get_pro_field($dbcon,$pid,'product_name');
			$str="";
			$cnt=1;
			
			$str.="
				
			<thead>
				<tr>
					<th colspan='5'><a onclick='get_product_history(\"".$pid."\",\"".$pr_name."\",\"0\",\"month_history\",\"".$branch."\")' href='#' ><< Back To Month List</a></th>
				</tr>
				<tr>
				  <th width='10%' style='text-align:center;'>Date.</th>
				  <th width='12%' style='text-align:center;'>Purchase</th>					  
				  <th width='12%' style='text-align:center;'>Sale</th>
				  <th width='12%' style='text-align:center;'>Outward</th>
				  <th width='12%' style='text-align:center;'>Inward</th>
				  <th width='12%' style='text-align:center;'>Branch Stock (IN)</th>
				  <th width='12%' style='text-align:center;'>Branch Stock (OUT)</th>
				  <th width='12%' style='text-align:center;'>Material Waste</th>
			  </tr></thead>";
			
			
			
			//$dateToTest = "2015-02-01";			
			$lastday = date('t',strtotime($start_date));
			
			for($i=1;$i<=$lastday;$i++)
			{
				
				$start_date = str_pad($year,2,'0',STR_PAD_LEFT)."-".str_pad($mid,2,'0',STR_PAD_LEFT)."-".str_pad($i,2,'0',STR_PAD_LEFT);
				
				$start_date_display = str_pad($i,2,'0',STR_PAD_LEFT)."-".str_pad($mid,2,'0',STR_PAD_LEFT)."-".str_pad($year,2,'0', STR_PAD_LEFT);
				//$end_date = $year."-".$mid."-".$lastday;
				//$start_date=str_pad($start_date, 2, '0', STR_PAD_LEFT); 
				//$start_date_display=str_pad($start_date, 2, '0', STR_PAD_LEFT); 
				
				$str.="<tr>
				
					<th>".$start_date_display."</th>
					<th>".get_purchase_total_stock_date($dbcon,$pid,$start_date,$branch)."</th>
					<th>".get_sale_total_stock_date($dbcon,$pid,$start_date,$branch)."</th>
					<th>".get_jobwork_Outwrd_total_stock_date($dbcon,$pid,$start_date,$branch)."</th>
					<th>".get_jobwork_inward_total_stock_date($dbcon,$pid,$start_date,$branch)."</th>
					<th>".get_jobwork_bstockin_total_stock_date($dbcon,$pid,$start_date,$branch)."</th>
					<th>".get_jobwork_bstockout_total_stock_date($dbcon,$pid,$start_date,$branch)."</th>
					<th>".get_jobwork_waste_total_stock_date($dbcon,$pid,$start_date,$branch)."</th>
				</tr>";
			}
			
			echo $str;
			
		}
	
	
	}
    
}
/*
else {
    die("Error - 1");
}*/

?>