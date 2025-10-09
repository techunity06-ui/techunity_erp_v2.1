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
						<th>Sr. No.</th>
						<th>Created Date</th>				  
						<th>Modified Date</th>				  
						<th>Owner</th>				  
						<th>Assign Users</th>				  
						<th>Territory</th>				  
						<th>Inquiry No</th>				  
						<th>Opportunity Name</th>				  
						<th>Customer Name</th>				  
						<th>Product Name</th>				  
						<th>Address</th>				  
						<th>Mobile No</th>				  
						<th>Email</th>				  
						<th>Customer Type</th>				  
						<th>Type</th>				  
						<th>Source</th>				  
						<th>Industry</th>				  
						<th>Stage</th>				  
						<th>Amount</th>				  
						<th>Close Date</th>						  
						<th>Inquiry Category</th>				  
						<th>Sales Stage</th>	  
						<th>Desc</th>				  
						<th>Inquiry Status</th>
						<th>Remarks</th>				  
						<th>Competition Status</th>	
						<th>Actions</th>					  
					</tr>
				</thead>
				<tbody>';
	if(!empty($POST['inquiry_date'])){
		$whr.=" and inq.inquiry_date='".date("Y-m-d",strtotime($POST['inquiry_date']))."'";
	}else{
		$whr.=" and inq.inquiry_date between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	}
	if($POST['t_id']){
		$whr.=' and inq.t_id='.$POST['t_id'];
	}

	if($POST['cust_id']){
		$whr.=' and inq.cust_id='.$POST['cust_id'];
	}

	if($POST['inquiry_id']){
		$whr.=' and inq.inquiry_id='.$POST['inquiry_id'];
	}

	if($POST['branch_id']){
		$whr.=' and inq.branch_id='.$POST['branch_id'];
	}

	if($POST['industry_type']){
		$whr.=' and cust.cust_ind='.$POST['industry_type'];
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

	if($POST['inquiry_status']!=''){
		if($POST['inquiry_status']==0){
			$hav.=' HAVING quo_sta=0';
		}else{
			$hav.=' HAVING quo_sta>0';
		}
	}
	
	if($POST['assign_user_id']){
		$whr.=' and assign_usr.user_id='.$POST['assign_user_id'];
	}

	if($POST['opp_id']){
		$whr.=' and inq.opp_id='.$POST['opp_id'];
	}

	if($POST['product_id']){
		$whr.=' and itr.product_id='.$POST['product_id'];
	}

	$c=1;
	$qry="SELECT inq.*,coun.country_name,state.state_name,city.city_name,assign_usr.user_name as assign_users,ter.t_name,cust.cust_name,stage.opp_stage,src.rb_name,ind.ci_name,inq_cat.mcd_name as inq_cat_name,pro.product_name,inq_sale_stage.mcd_name as inq_sale_stage_name,pro.product_icode,inq_type.mcd_name as inq_type_name,cur.currency_code,IF((select count(cust_id) from tbl_inquiry where inquiry_status=0 and cust_id=inq.cust_id)>1, 'Existing Customer', 'New Customer') as ctype,owner_ausr.user_name as owner_user,addr.c_add_address,(select count(quotation_id) from tbl_quotation as quot where quot.inquiry_id=inq.inquiry_id) as quo_sta,cust.cust_mobile,cust.cust_email from tbl_inquiry as inq
	left join tbl_inquiry_trn as itr on itr.inquiry_id = inq.inquiry_id
	left join product_mst as pro on pro.product_id = itr.product_id
	left join users as assign_usr on assign_usr.user_id=inq.user_id
	left join users as owner_ausr on owner_ausr.user_id = inq.owner_user_id
	left join territory_mst as ter on ter.t_id=inq.t_id
	left join tbl_customer as cust on cust.cust_id=inq.cust_id
	left join tbl_cust_address as addr on addr.cust_id = inq.cust_id and addr.c_addr_defult=1
	left join country_mst as coun on coun.countryid = addr.c_add_country
	left join state_mst as state on state.stateid = addr.c_add_state
	left join city_mst as city on city.cityid = addr.c_add_city
	left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id
	left join tbl_refer_by as src on src.rb_id=cust.cust_source
	left join tbl_customer_industry as ind on ind.ci_id=cust.cust_ind
	left join tbl_master_category_detail as inq_type on inq_type.mcd_id=inq.inquiry_type_id
	left join tbl_master_category_detail as inq_cat on inq_cat.mcd_id=inq.inquiry_cat_id
	left join tbl_master_category_detail as inq_sale_stage on inq_sale_stage.mcd_id=inq.sales_stage_id

	left join tbl_currency as cur on cur.currency_id=inq.currency_id
	WHERE  inq.company_id IN (0,".$_SESSION['company_id'].")".$whr." ".$hav." order by inq.inquiry_date";

	//echo $qry;
	$qry_rs=$dbcon->query($qry);
	if(mysqli_num_rows($qry_rs)){
		while($rel=mysqli_fetch_assoc($qry_rs)){
			$closing_date='';
			if($rel['closing_date']!='1970-01-01' && $rel['closing_date']!='0000-00-00'){
				$closing_date=date("d-m-Y",strtotime($rel['closing_date']));
			}

			$inquiry_status = ($rel['quo_sta']==0)? ('Pending') : ('Done');

			$get_task_qry="select tsk.*,sub.mcd_name as subject, usr.user_name, prior.task_priority_name, user.user_name as assign_name from tbl_task as tsk 
			left join tbl_master_category_detail as sub on sub.mcd_id=tsk.task_type_id
			left join users as usr on usr.user_id=tsk.user_id
			left join users as user on user.user_id=tsk.assign_user_ids
			left join task_priority_mst as prior on prior.task_priority_id=tsk.task_priority_id
			
			where tsk.task_status!=2 and tsk.task_rel_id=5 and tsk.entry_type=1 and tsk.inquiry_id=".$rel['inquiry_id']." order by tsk.create_date DESC LIMIT 1";
			
			$get_task_qry_rs=$dbcon->query($get_task_qry);
			$task_rel = brp_mysqli_fetch_array($get_task_qry_rs);

			$view_hist_btn = '<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_view/'.$rel['inquiry_id'].'"><i class="fa fa-eye"></i></a>';
			$str.='<tr>
				<td class="text-left">'.$c.'</td>
				<td class="text-left" style="white-space:nowrap;">'.date("d-m-Y h:i A",strtotime($rel['create_date'])).'</td>
				<td class="text-left" style="white-space:nowrap;">'.date("d-m-Y h:i A",strtotime($rel['cdate'])).'</td>
				<td class="text-left">'.$rel['owner_user'].'</td>
				<td class="text-left">'.$rel['assign_users'].'</td>
				<td class="text-left">'.$rel['t_name'].'</td>
				<td class="text-left">'.$rel['inquiry_no'].'</td>
				<td class="text-left">'.$rel['inquiry_name'].'</td>
				<td class="text-left">'.$rel['cust_name'].'</td>
				<td class="text-left">'.$rel['product_name'].'--'.$rel['product_icode'].'</td>
				<td class="text-left">'.$rel['c_add_address'].','.$rel['country_name'].','.$rel['state_name'].','.$rel['city_name'].'</td>
				<td class="text-left">'.$rel['cust_mobile'].'</td>
				<td class="text-left">'.$rel['cust_email'].'</td>
				<td class="text-left">'.$rel['ctype'].'</td>

				<td class="text-left">'.$rel['inq_type_name'].'</td>
				<td class="text-left">'.$rel['rb_name'].'</td>
				<td class="text-left">'.$rel['ci_name'].'</td>
				<td class="text-left">'.$rel['opp_stage'].'</td>
				<td class="text-left">'.$rel['g_total'].' '.$rel['currency_code'].'</td>
				<td class="text-left" style="white-space:nowrap;">'.$closing_date.'</td>
				<td class="text-left">'.$rel['inq_cat_name'].'</td>
				<td class="text-left">'.$rel['inq_sale_stage_name'].'</td>
				<td class="text-left">'.nl2br($rel['inq_desc']).'</td>
				<td class="text-left">'.$inquiry_status.'</td>
				<td class="text-left">'.nl2br($task_rel['task_remark']).'</td>
				<td class="text-left">'.nl2br($rel['inq_comp_desc']).'</td>
				<td class="text-left">'.$view_hist_btn.'</td>
			</tr>';
			$c++;
		}
	}
	else{
		$str.='<tr><td colspan="25" class="text-center">NO DATA FOUND !!!</td></tr>';
	}
		
		$str.='</tbody>				 
			</table>';
		
		echo $str;
		//$resp['html_resp']=$str;
		//echo json_encode($resp);
	}
?>