<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "generate_report") {
		$s_date=explode(' - ',$POST['date']);
		$str='';$whr='';$hav='';
		$str.='<table class="display table table-bordered table-striped">
				<thead>
					<tr>
						<th style="white-space:nowrap;">Sr. No.</th>
						<th>Quotation No</th>				  
						<th style="white-space:nowrap;">Quotation Date</th>				  
						<th>Customer Name</th>				  
						<th>Address</th>				  
						<th style="white-space:nowrap;">Category Name</th>				  
						<th style="white-space:nowrap;">Product Name</th>				  
						<th style="white-space:nowrap;">Product Qty</th>				  
						<th style="white-space:nowrap;">Product Amount</th>				  
						<th style="white-space:nowrap;">Quotation Type</th>				  
						<th >Reason</th>				  
						<th>Users</th>				  
						<th style="white-space:nowrap;">Quotation Status</th>				  
						<th style="white-space:nowrap;">Branch Name</th>	
						<th>Actions</th>					  
					</tr>
				</thead>
				<tbody>';
	if(!empty($POST['quotation_date'])){
		$whr.=" and qtn.quotation_date='".date("Y-m-d",strtotime($POST['inquiry_date']))."'";
	}else{
		$whr.=" and qtn.quotation_date between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	}
	

	if($POST['cust_id']){
		$whr.=' and qtn.cust_id='.$POST['cust_id'];
	}

	if($POST['product_category']){
		$whr.=' and pro.product_category='.$POST['product_category'];
	}

	if($POST['product_id']){
		$whr.=' and qtr.product_id='.$POST['product_id'];
	}

	if($POST['quot_type']){
		$whr.=' and qtn.quot_type='.$POST['quot_type'];
	}

	if($POST['approve_status']){
		$whr.=' and qtn.approve_status='.$POST['approve_status'];
	}

	if($POST['quotation_id']){
		$whr.=' and qtn.quotation_id='.$POST['quotation_id'];
	}

	if($POST['branch_id']){
		$whr.=' and qtn.branch_id='.$POST['branch_id'];
	}

	if($POST['country_id']){
		$whr.=' and addr.c_add_country='.$POST['country_id'];
	}

	if($POST['state_id']){
		$whr.=' and addr.c_add_state='.$POST['state_id'];
	}

	if($POST['city_id']){
		$whr.=' and addr.c_add_city='.$POST['city_id'];
	}
	
	if($POST['user_id']){
		$whr.=' and qtn.user_id='.$POST['user_id'];
	}

	$c=1;
	$qry="SELECT qtn.*,qtr.product_qty, qtr.product_amount, coun.country_name, state.state_name, addr.c_add_address, city.city_name, usr.user_name, cust.cust_name, cur.currency_code,pro.product_name, cat.cat_name, brn.branch_name from tbl_quotation as qtn

	left join tbl_quotation_trn as qtr on qtr.quotation_id = qtn.quotation_id
	left join product_mst as pro on pro.product_id = qtr.product_id
	left join tbl_category as cat on cat.cat_id = pro.product_category
	left join users as usr on usr.user_id=qtn.user_id
	left join tbl_customer as cust on cust.cust_id=qtn.cust_id
	left join tbl_cust_address as addr on addr.cust_id = qtn.cust_id and addr.c_addr_defult=1
	left join country_mst as coun on coun.countryid = addr.c_add_country
	left join state_mst as state on state.stateid = addr.c_add_state
	left join city_mst as city on city.cityid = addr.c_add_city
	left join tbl_currency as cur on cur.currency_id=qtn.currency_id
	left join branch_mst as brn on brn.branch_id = qtn.branch_id
	WHERE qtn.quotation_status=0 AND qtn.revise_status=0 AND qtn.company_id IN (0,".$_SESSION['company_id'].")".$whr." order by qtn.quotation_date";
	$qry_rs=$dbcon->query($qry);
	if(mysqli_num_rows($qry_rs)){
		while($rel=mysqli_fetch_assoc($qry_rs)){
			$quotation_date='';
			if($rel['quotation_date']!='1970-01-01' && $rel['quotation_date']!='0000-00-00'){
				$quotation_date=date("d-m-Y",strtotime($rel['quotation_date']));
			}

			if($rel['quot_type']==1){
				$quot_type = 'Export';
			}else{
				$quot_type =  'Domestic';
			}

			if($rel['approve_status']==1){			
				$quotation_status = 'Approved';
			}else if($rel['approve_status']==2){
				$quotation_status = 'Rejected';
			}else{
				$quotation_status = 'Pending';
			}

			
			if($rel['cat_name']!=''){
				$rel['cat_name'] = $rel['cat_name'];
			}else{
				$rel['cat_name'] = 'Primary';
			}

			if(!empty($rel['inquiry_id'])){
				$view_hist_btn = '<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_view/'.$rel['inquiry_id'].'"><i class="fa fa-eye"></i></a>';
			}

			$get_task_qry="select tsk.*,sub.mcd_name as subject,usr.user_name,prior.task_priority_name,user.user_name as assign_name from tbl_task as tsk 
				left join tbl_master_category_detail as sub on sub.mcd_id=tsk.task_type_id
				left join users as usr on usr.user_id=tsk.user_id
				left join users as user on user.user_id=tsk.assign_user_ids
				left join task_priority_mst as prior on prior.task_priority_id=tsk.task_priority_id
				where tsk.task_status!=2 and tsk.task_rel_id=5 and tsk.entry_type=1 and tsk.inquiry_id=".$rel['inquiry_id']." order by tsk.create_date DESC limit 1";
			$get_task_qry_rs=$dbcon->query($get_task_qry);
			$task_rel=mysqli_fetch_assoc($get_task_qry_rs);
			
			$str.='<tr>
				<td class="text-left">'.$c.'</td>
				<td class="text-left" style="white-space:nowrap;">'.$rel['quotation_no'].'</td>
				<td class="text-left" >'.$quotation_date.'</td>
				<td class="text-left" style="white-space:nowrap;">'.$rel['cust_name'].'</td>
				<td class="text-left">'.$rel['c_add_address'].','.$rel['country_name'].','.$rel['state_name'].','.$rel['city_name'].'</td>
				<td class="text-left">'.$rel['cat_name'].'</td>
				<td class="text-left">'.$rel['product_name'].'</td>
				<td class="text-left">'.$rel['product_qty'].'</td>
				<td class="text-left">'.$rel['product_amount'].'</td>
				<td class="text-left">'.$quot_type.'</td>
				<td class="text-left">'.nl2br($task_rel['task_remark']).'</td>
				<td class="text-left">'.$rel['user_name'].'</td>
				<td class="text-left"><strong>'.$quotation_status.'</strong></td>
				<td class="text-left">'.$rel['branch_name'].'</td>
				
				<td class="text-left">'.$view_hist_btn.'</td>
			</tr>';
			$c++;
		}
	}
	else{
		$str.='<tr><td colspan="24" class="text-center">NO DATA FOUND !!!</td></tr>';
	}
		
		$str.='</tbody>				 
			</table>';
		
		$resp['html_resp']=$str;
		echo json_encode($resp);
	}
?>