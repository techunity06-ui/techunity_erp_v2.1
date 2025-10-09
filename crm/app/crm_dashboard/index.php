<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';
$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "dynamic_chart") {
	$date=get_sdate($POST['c_year']);	
	$whr='';
	if($_SESSION['user_type']!='2'){
		$whr.=' and u.user_id='.$_SESSION['user_id'];
	}

	$query="SELECT m.month,(select count(inquiry_id) from tbl_inquiry u 
	where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.inquiry_date) and inquiry_status=0 and company_id=".$_SESSION['company_id']." and u.inquiry_date between '".date('Y-m-d',strtotime($date['start_date']))."' and '".date('Y-m-d',strtotime($date['end_date']))."' ".$whr.") as invoice
	FROM (
	SELECT 'Apr' AS MONTH
	UNION SELECT 'May' AS MONTH
	UNION SELECT 'Jun' AS MONTH
	UNION SELECT 'Jul' AS MONTH
	UNION SELECT 'Aug' AS MONTH
	UNION SELECT 'Sep' AS MONTH
	UNION SELECT 'Oct' AS MONTH
	UNION SELECT 'Nov' AS MONTH
	UNION SELECT 'Dec' AS MONTH
	UNION SELECT 'Jan' AS MONTH
	UNION SELECT 'Feb' AS MONTH
	UNION SELECT 'Mar' AS MONTH
	) AS m
	GROUP BY m.month
	ORDER BY 1+1";
	$invoice_counter=$dbcon->query($query);
	$row	= array();
	$i=0;
	while($chart=mysqli_fetch_assoc($invoice_counter))
	{	
		$row[$chart['month']][]=intval($chart['invoice']);
		$row[]= $chart['month'];
		$row1[$i]['device']=$chart['month'];
		$row1[$i]['geekbench']=$chart['invoice'];
		$i++;
	}		
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "getcust") {
	$date=get_sdate($POST['c_year']);
	$table1='';
	$qry="SELECT SUM(invoice.g_total) AS total,cust.company_name as name from tbl_invoice as invoice inner join  tbl_customer as cust on invoice.cust_id=cust.cust_id  where invoice_date>='".date('Y-m-d',strtotime($date['start_date']))."' AND invoice_date<='".date('Y-m-d',strtotime($date['end_date']))."' and invoice_status=0 GROUP BY cust.cust_id ORDER BY total  desc limit 0,5";
	$cat=$dbcon->query($qry);
	$i=1;
	$table1.='<div>
	<div class="">
	<h1 style="padding-top:0px !important">Top 5 Customer OF Year '.$POST['c_year'].'-'.($POST['c_year']+1).'</h1>
	</div>
	</div>
	<table class="table table-hover personal-task">
	<tbody>
	<tr>
	<td>Sr No</td>
	<td>Name</td>
	<td>Total Business</td>
	</tr>
	';
	while($rel=mysqli_fetch_assoc($cat))
	{
		$table1 .= '<tr>
		<td>'.$i.'</td>
		<td>
		'.$rel['name'].'
		</td>
		<td>
		<span class="badge bg-important">'.$rel['total'].'</span>
		</td>
		</tr>
		';
		$i++;
	}
	$table1 .='</tbody>
	</table>';
	echo $table1;
} else if(strtolower($POST['mode']) == "lead_circle") {

	$_SESSION['start_date'] = $post_data['start_date'];
	$_SESSION['end_date'] = $post_data['end_date'];

	$where = "";
	$post_user_id = 0;
	if(isset($$POST['user_id']) && !empty($$POST['user_id'])) {
		$post_user_id = $POST['user_id'];
		$where .= " AND inq.user_id = '".$POST['user_id']."'";
	} else if($_SESSION['user_type']!=2){ 
		$user_funnel_id = check_user_chein($dbcon,$_SESSION['user_id'],1);
		$where .= " and inq.user_id IN (".$user_funnel_id.")";
	}

	$start_date = $POST['start_date'];
	$end_date = $POST['end_date'];
	if(!empty($start_date) && !empty($end_date)){
		$where .="  AND DATE(inq.inquiry_date) >= '".date('Y-m-d',strtotime($start_date))."' AND  DATE(inq.inquiry_date) <= '".date('Y-m-d',strtotime($end_date))."'";
	}

	if($_SESSION['user_type']!=2){ 
		$where.=" and FIND_IN_SET($_SESSION[user_id],task.show_user_ids)";
	}

	$query = "SELECT 
				rb.rb_name, 
				rb.rb_id, 
				COUNT(DISTINCT inq.inquiry_id) AS led 
			FROM 
				tbl_inquiry AS inq 
				LEFT JOIN tbl_inquiry_trn AS tr ON tr.inquiry_id = inq.inquiry_id 
				LEFT JOIN tbl_task AS task ON task.inquiry_id = inq.inquiry_id 
				LEFT JOIN tbl_customer AS cust ON cust.cust_id = inq.cust_id 
				LEFT JOIN tbl_refer_by AS rb ON rb.rb_id = cust.cust_source 
			WHERE 
				inq.inquiry_status = 0 
				AND inq.company_id IN (0, 1) 
				AND task.task_status = 0 
				AND rb.rb_id IS NOT NULL 
				$where
			GROUP BY 
				rb.rb_name, 
				rb.rb_id 
			ORDER BY 
				MAX(inq.inquiry_id) DESC, 
				COUNT(rb.rb_id) DESC;
			";
			
	$result=$dbcon->query($query);
	$row1 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		if ($row['rb_name']) {
			$row1[$i]['label']=ucwords($row['rb_name']);
			$row1[$i]['symbol']=$row['rb_name'];
			$row1[$i]['y']=intval($row['led']);	
			$row1[$i]['link']="inquiry_list_flt_source/".$row['rb_id'].'/'.$POST['start_date'].'/'.$POST['end_date'];	
			$i++;
		}
	}
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_employee_sales") {
	
	$user_ids=check_user_chein($dbcon,$POST['d_user_id'],1);
	
	$where = "";
	if ($POST['d_user_id'])
		$where = " AND e.user_id = ".$POST['d_user_id'];
	
	$query="select led,e.user_name from users as e left join (select sum(i.g_total) as led,i.user_id from tbl_inquiry as i where i.inquiry_status=0 and i.opp_id=12 and DATE_FORMAT(i.won_date,'%Y-%m-%d %h:%i:%s') BETWEEN CAST('".date('Y-m-d 00:00:00',strtotime($POST['d_start_date']))."'AS DATE) and CAST('".date('Y-m-d 23:59:59',strtotime($POST['d_end_date']))."'AS DATE) group by i.user_id) as dem on dem.user_id=e.user_id where e.active=0 $where AND e.company_id = ".$_SESSION['company_id']." group by e.user_id ORDER BY led DESC LIMIT 8";

	$result=$dbcon->query($query);
	
	$row1 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['label'] = $row['user_name'];
		$row1[$i]['y'] = intval($row['led']);
		$i++;
	}	
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_closed_won_sales") {

	$_SESSION['start_date'] = $POST['d_start_date'];
	$_SESSION['end_date'] = $POST['d_end_date'];

	$where = "";
	$d_user_id = 0;
	if ($POST['d_user_id']) {
		$d_user_id = $POST['d_user_id'];
		$where .= " AND inq.user_id = ".$POST['d_user_id'];
	}		

	// $where .= "  AND inq.inquiry_date BETWEEN '".date('Y-m-d',strtotime($POST['d_start_date']))."' AND '".date('Y-m-d',strtotime($POST['d_end_date']))."'";

	$where.="  AND DATE(inq.inquiry_date) >= '".date('Y-m-d',strtotime($POST['d_start_date']))."' AND  DATE(inq.inquiry_date) <= '".date('Y-m-d',strtotime($POST['d_end_date']))."'";

	// $where.="  AND DATE(inq.inquiry_date) >= '".date('Y-m-d',strtotime($start_date))."' AND  DATE(inq.inquiry_date) <= '".date('Y-m-d',strtotime($end_date))."'";
	if($_SESSION['user_type']!=2){ 
		$where.=" and FIND_IN_SET($_SESSION[user_id],task.show_user_ids)";
	}
	
	$query="select count(DISTINCT inq.inquiry_id) as led,cat.cat_name as cat_name, pro.product_category from tbl_category as cat left join product_mst as pro on pro.product_category = cat.cat_id left join tbl_inquiry_trn as itrn on itrn.product_id=pro.product_id left join tbl_inquiry as inq on inq.inquiry_id=itrn.inquiry_id  left join tbl_task as task on task.inquiry_id=inq.inquiry_id where cat.cat_status=0 and inq.opp_id=12 and inq.inquiry_status=0 and itrn.inquiry_trn_status=0 and pro.product_status=0 $where AND cat.company_id IN (0,".$_SESSION['company_id'].") group by cat.cat_id ORDER BY led DESC";

	$result=$dbcon->query($query);
	$row1 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['label'] = $row['cat_name'];
		$row1[$i]['y'] = intval($row['led']);
		$row1[$i]['link']="inquiry_list_flt_inq/12/".$row['product_category'].'/'.$d_user_id.'/'.$POST['d_start_date'].'/'.$POST['d_end_date'];		
		$i++;
	}
	
	// For Lost Inquiry
	$query="select count(DISTINCT inq.inquiry_id) as led, cat.cat_name as cat_name, pro.product_category from tbl_category as cat left join product_mst as pro on pro.product_category = cat.cat_id left join tbl_inquiry_trn as itrn on itrn.product_id=pro.product_id left join tbl_inquiry as inq on inq.inquiry_id=itrn.inquiry_id  left join tbl_task as task on task.inquiry_id=inq.inquiry_id where cat.cat_status=0 and inq.opp_id=13 and inq.inquiry_status=0 and itrn.inquiry_trn_status=0 and pro.product_status=0 $where AND cat.company_id IN (0,".$_SESSION['company_id'].") group by cat.cat_id ORDER BY led DESC";

	$result=$dbcon->query($query);
	$row2 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row2[$i]['label'] = $row['cat_name'];
		$row2[$i]['y'] = intval($row['led']);
		$row2[$i]['link']="inquiry_list_flt_inq/13/".$row['product_category'].'/'.$d_user_id.'/'.$POST['d_start_date'].'/'.$POST['d_end_date'];
		$i++;
	}

	// For Inquiry 
	$query="select count(inq.inquiry_id) as led,cat.cat_name as cat_name, pro.product_category from tbl_category as cat left join product_mst as pro on pro.product_category = cat.cat_id left join tbl_inquiry_trn as itrn on itrn.product_id=pro.product_id left join tbl_inquiry as inq on inq.inquiry_id=itrn.inquiry_id  left join tbl_task as task on task.inquiry_id=inq.inquiry_id where cat.cat_status=0 and inq.opp_id NOT IN(12,13) and task.task_status = 0 and inq.inquiry_status=0 and itrn.inquiry_trn_status=0 and pro.product_status=0 $where AND cat.company_id IN (0,".$_SESSION['company_id'].") group by cat.cat_id ORDER BY led DESC";


	// die($query);

	$result=$dbcon->query($query);
	$row3 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row3[$i]['label'] = $row['cat_name'];
		$row3[$i]['y'] = intval($row['led']);
		$row3[$i]['link']="inquiry_list_flt_inq/all/".$row['product_category'].'/'.$d_user_id.'/'.$POST['d_start_date'].'/'.$POST['d_end_date'];
		$i++;
	}

	$res = array('won' => $row1, 'lost' => $row2, 'inquiry' => $row3);
	
	echo json_encode($res);
	// echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_objection") {

	$user_ids=check_user_chein($dbcon,$POST['d_user_id'],1);

	$where = "";
	if ($POST['d_user_id'])
		$where = " AND e.user_id = ".$POST['d_user_id'];
	
	$query="select led,e.user_name from users as e left join (select sum(i.g_total) as led,i.user_id from tbl_inquiry as i where i.inquiry_status=0 and i.opp_id=12 and DATE_FORMAT(i.won_date,'%Y-%m-%d %h:%i:%s') BETWEEN CAST('".date('Y-m-d 00:00:00',strtotime($POST['d_start_date']))."'AS DATE) and CAST('".date('Y-m-d 23:59:59',strtotime($POST['d_end_date']))."'AS DATE) group by i.user_id) as dem on dem.user_id=e.user_id where e.active=0 $where AND e.company_id = ".$_SESSION['company_id']." group by e.user_id ORDER BY led DESC LIMIT 8";

	$result=$dbcon->query($query);
	$row1 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['label'] = $row['user_name'];
		$row1[$i]['y'] = intval($row['led']);
		$i++;
	}	
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_lead_by_product") {

	$get_data = common_crm_chart_conditions($POST,$dbcon);
	$where = $get_data['where'];

	$post_category_id = 0;
	if (!empty($POST['category_id']) || $POST['category_id'] == '0') {
		$post_category_id = $POST['category_id'];
		$where .= ' AND pro.product_category='.$POST['category_id'];
	}

	$query="select count(inq.inquiry_id) as led,pro.product_id,pro.product_category,pro.product_name as pg_name from product_mst as pro left join tbl_inquiry_trn as itrn on itrn.product_id=pro.product_id 
	left join tbl_inquiry as inq on inq.inquiry_id=itrn.inquiry_id  left join tbl_task as task on task.inquiry_id=inq.inquiry_id where inq.inquiry_status=0 and itrn.inquiry_trn_status=0 and pro.product_status=0 $where 
	AND pro.company_id IN (0,".$_SESSION['company_id'].") and task.task_status = 0 group by pro.product_id";

	$result=$dbcon->query($query);
	$row1 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['label']=$row['pg_name'];
		$row1[$i]['y']=intval($row['led']);		
		$row1[$i]['link']="inquiry_list_flt_prod/".$row['product_id'].'/'.$row['product_category'].'/'.$get_data['user_id'].'/'.$get_data['start_date'].'/'.$get_data['end_date'];		
		$i++;
	}
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_inq_cat") {

	// print_r($_POST);exit;
	$where = "";
	$d_user_id = 0;
	if ($POST['user_id']) {
		$d_user_id = $POST['user_id'];
		$where .= " AND inq.user_id = ".$POST['user_id'];
	}		

	$where .= "  AND inq.inquiry_date BETWEEN '".date('Y-m-d',strtotime($POST['start_date']))."' AND '".date('Y-m-d',strtotime($POST['end_date']))."'";

	if($_SESSION['user_type']!=2){ 
		$where.=" and FIND_IN_SET($_SESSION[user_id],task.show_user_ids)";
	}

	if (isset($POST['category_id']) && !empty($POST['category_id'])) {
		$where .= " AND cat_detail.mcd_id = ".$POST['category_id'];
	}

	$query = "SELECT COUNT(inq.inquiry_id) AS led, cat_detail.mcd_id as cat_detail_id, cat_detail.mcd_name as cat_name, cat.mc_id FROM tbl_master_category_detail AS cat_detail
	LEFT JOIN tbl_master_category AS cat ON cat.mc_id = cat_detail.mcd_cat_id
	LEFT JOIN tbl_inquiry AS inq ON inq.inquiry_cat_id = cat_detail.mcd_id
	LEFT JOIN tbl_task as task on task.inquiry_id=inq.inquiry_id
	WHERE cat.mc_id = 9 and inq.inquiry_status=0 and task.task_status = 0 $where AND cat_detail.company_id IN (0,".$_SESSION['company_id'].") GROUP BY cat_detail.mcd_id, cat_detail.mcd_name, cat.mc_id";

	$result=$dbcon->query($query);
	$row1 = array();
	$i=0;

	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['label'] = $row['cat_name'];
		$row1[$i]['y'] = intval($row['led']);
		$row1[$i]['link']="inquiry_list_sales_stage_category/".$row['cat_detail_id'].'/'.$d_user_id.'/'.$POST['start_date'].'/'.$POST['end_date'];		
		$i++;
	}

	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_funal") {

	$get_data = common_crm_chart_conditions($POST,$dbcon);
	$where = $get_data['where'];

	if($_SESSION['user_type']!=2 && $get_data['user_id'] == 0){
		$user_funnel_id = check_user_chein($dbcon,$_SESSION['user_id'],1);
		$where .= " and inq.user_id IN (".$user_funnel_id.")";
	}

	$query="select COUNT(inq.inquiry_id) AS led, inq.opp_id, rf.opp_stage from tbl_inquiry as inq 
	left join tbl_opportunity_mst as rf on rf.opp_id=inq.opp_id left join tbl_task as task on task.inquiry_id=inq.inquiry_id where inq.inquiry_status=0 and inq.opp_id != 0 and rf.opp_status=0 $where AND inq.company_id IN (0,".$_SESSION['company_id'].") and task.task_status = 0 group by rf.opp_id order by rf.opp_priority";

	// Get All the WON and lost inquiry data
	$where_won = "";
	$post_user_id = 0;
	if(isset($POST['user_id']) && !empty($POST['user_id'])) {
		$post_user_id = $post_data['user_id'];
		$where_won .= " AND inq.user_id = '".$POST['user_id']."'";
	} else if($_SESSION['user_type'] != 2){
		$user_funnel_id = check_user_chein($dbcon,$_SESSION['user_id'],1);
		$where_won .= " and inq.user_id IN (".$user_funnel_id.")";
	}

	$start_date = $POST['start_date'];
	$end_date = $POST['end_date'];
	if(!empty($start_date) && !empty($end_date)){
		$where_won .="  AND DATE(inq.inquiry_date) >= '".date('Y-m-d',strtotime($start_date))."' AND  DATE(inq.inquiry_date) <= '".date('Y-m-d',strtotime($end_date))."'";
	}

	$query_won_lost = "SELECT COUNT(inq.inquiry_id) AS led, stage.opp_stage, inq.opp_id
					FROM 
						tbl_inquiry as inq 
						left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id 
					where 
						( 1 AND inq.inquiry_status = 0 and 
						inq.company_id IN (0,".$_SESSION['company_id'].") AND 
						inq.opp_id in (12,13) 
						$where_won )
					Group by inq.opp_id";


	$result_won_lost=$dbcon->query($query_won_lost);

	$result=$dbcon->query($query);
	$row1 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		if ($row['opp_id'] != 12 && $row['opp_id'] != 13) {
			$row1[$i]['label']=$row['opp_stage'];
			$row1[$i]['y']=intval($row['led']);			
			$row1[$i]['id']=$row['opp_id'];		
			$row1[$i]['link']="crm_stage_users/".$row['opp_id'].'/'.$get_data['start_date'].'/'.$get_data['end_date'];
			$i++;
		}
	}	

	// Get WON amd LOST inquiry data count
	while($row=mysqli_fetch_assoc($result_won_lost))
	{	
		$row1[$i]['label']=$row['opp_stage'];
		$row1[$i]['y']=intval($row['led']);			
		$row1[$i]['id']=$row['opp_id'];		
		$row1[$i]['link']="crm_stage_users/".$row['opp_id'].'/'.$get_data['start_date'].'/'.$get_data['end_date'];
		$i++;
	}

	echo json_encode($row1);
} else if(strtolower($POST['mode']) == "generate_report") {
	$s_date=explode(' - ',$POST['rep_date']);
	$t_idstatus=0;
	if(!empty($POST['t_id'])){
		$t_idstatus=1;
	}
	$rep_date=0;
	if(!empty($POST['t_id'])){
		$rep_date==1;
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
	 
	$grp = 'GROUP BY inq.inquiry_date';
	if($POST['inquiry_status']!=''){
		if($POST['inquiry_status']==0){
			$hav.=' HAVING quo_sta=0';
		}else{
			$hav.=' HAVING quo_sta>0';
		}
	}
	
	if($POST['assign_user_id']){
		$whr.=' and inq.user_id='.$POST['assign_user_id'];
	}

	if($t_idstatus==1){
		$query="SELECT COUNT(*) as led, inquiry_id,inquiry_date,(select count(quotation_id) from tbl_quotation as quot where quot.inquiry_id=inq.inquiry_id) as quo_sta FROM tbl_inquiry as inq 
		left join tbl_customer as cust on cust.cust_id=inq.cust_id
		left join tbl_cust_address as addr on addr.cust_id = inq.cust_id and addr.c_addr_defult=1
		where inq.t_id='".$POST['t_id']."' and inq.inquiry_date between  '".date("Y-m-d",strtotime($s_date[0]))."' and  '".date("Y-m-d",strtotime($s_date[1]))."' AND inq.company_id IN (0,".$_SESSION['company_id'].") ".$whr."  GROUP BY inquiry_date"." ".$hav;
	}else{
		$query="SELECT COUNT(*) as led, inquiry_id,inquiry_date,(select count(quotation_id) from tbl_quotation as quot where quot.inquiry_id=inq.inquiry_id) as quo_sta FROM tbl_inquiry as inq 
		left join tbl_customer as cust on cust.cust_id=inq.cust_id
		left join tbl_cust_address as addr on addr.cust_id = inq.cust_id and addr.c_addr_defult=1
		where inq.inquiry_date between  '".date("Y-m-d",strtotime($s_date[0]))."' and  '".date("Y-m-d",strtotime($s_date[1]))."'AND inq.company_id IN (0,".$_SESSION['company_id'].") ".$whr." ".$grp." ".$hav;
	}

	$result=$dbcon->query($query);
	$i=0;
	while($row=brp_mysqli_fetch_array($result))
	{	
		$row1[$i]['y']= (int)$row['led'];
		$row1[$i]['label']=$row['inquiry_date'];
				//$row1[$i]['label']=$POST['rep_date'];
		$row1[$i]['inquiry_date']=$row['inquiry_date'];	
		$i++;
	}	
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "generate_report_owner") {
	$s_date=explode(' - ',$POST['rep_date']);
	$whr.=" and DATE_FORMAT(task.task_due_date,'%Y-%m-%d') between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	if($POST['user_id']){
		$whr.=' and task.user_id='.$POST['user_id'];
	}
	if($POST['task_type_id']){
		$whr.=' and task.task_type_id='.$POST['task_type_id'];
	}
	if($POST['task_status']){
		$whr.=' and task.task_status='.$POST['task_status'];
	}
	if($POST['task_rel_id']){
		$whr.=' and task.task_rel_id in('.$POST['task_rel_id'].')';
	}
			//$query="SELECT COUNT(*)as tot_rec,usr.user_name,task_sub.mcd_name,task.task_type_id,usr.user_name,task.user_id from tbl_task as task left join users as usr on usr.user_id=task.user_id left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id WHERE task.task_status!=2 and task.entry_type=1 ".$whr." group by task.task_type_id";

	$query="SELECT DISTINCT task.*,COUNT(*)as tot_rec,usr.user_name,task_sub.mcd_name as task_sub_name,rel.task_rel_name,(select GROUP_CONCAT(user_name) from users where find_in_set(user_id,task.assign_user_ids)) as assign_users,
	(
	CASE
	WHEN task.task_rel_id=1 then task.task_name
	WHEN task.task_rel_id=2 then task.task_name
	WHEN task.task_rel_id=3 then (SELECT c_con_fname from tbl_cust_contact WHERE c_con_id=task.c_con_id)
	WHEN task.task_rel_id=4 then (SELECT cust_name from tbl_customer WHERE cust_id=task.cust_id)
	WHEN task.task_rel_id=5 then (SELECT inquiry_no from tbl_inquiry WHERE inquiry_id=task.inquiry_id)
	END
	) as rel_name
	from tbl_task as task
	left join users as usr on usr.user_id=task.user_id
	left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id
	left join task_rel_mst as rel on rel.task_rel_id=task.task_rel_id
	WHERE task.task_status=0 and task.entry_type=1 ".$whr." AND task.company_id IN (0,".$_SESSION['company_id'].") and task.task_type_id !=14 group by task.task_type_id";

	$result=$dbcon->query($query);
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['y']= (int)$row['tot_rec'];
		$row1[$i]['label']=$row['task_sub_name'];
				//$row1[$i]['label']=$POST['rep_date'];
		$row1[$i]['task_type_id']=$row['task_type_id'];	
		$i++;
	}	
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "generate_report_leads_executive") {
	$source_id=$POST['source_id'];
	$s_date=explode(' - ',$POST['date']);

	$whr = " and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	if(!empty($source_id)){
		$query="select COUNT(*)as led,e.cdate,e.user_id,us.user_name as lead_owner,e.inquiry_name as oppurtunity_name from tbl_inquiry as e 
		left join tbl_task  as et on et.inquiry_id=e.inquiry_id
		left join users as us on us.user_id=e.user_id
		where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.user_id=".$source_id." AND e.company_id IN (0,".$_SESSION['company_id'].") ".$whr." group by e.user_id";
		$result=$dbcon->query($query);
		$i=0;
		while($row=mysqli_fetch_assoc($result))
		{	
			$row1[$i]['y']= (int)$row['led'];
			$row1[$i]['label']=$row['lead_owner'];
					//$row1[$i]['label']=$POST['rep_date'];
			$row1[$i]['user_id']=$row['user_id'];	
			$i++;
		}	
		echo json_encode($row1);
	}
}
else if(strtolower($POST['mode']) == "generate_report_appointment_activity_list") {

	$user_id=$POST['user_id'];
	$where='';
	if($user_id){
		$where.=' and appoint.user_id='.$POST['user_id'];
	}

	$query="SELECT COUNT(usr.user_name)as led, appoint.task_id, usr.user_name as lead_owner, appoint.appointment_start_time, appoint.appointment_end_time, appoint.appointment_subject, appoint.task_remark, appoint.task_location, appoint.task_status, appoint.user_id FROM tbl_task as appoint left join users as usr on usr.user_id=appoint.user_id where ( 1 AND appoint.task_status = 0 and appoint.entry_type = 2 ".$where." AND appoint.company_id IN (0,".$_SESSION['company_id'].")) GROUP BY usr.user_name";
	$result=$dbcon->query($query);
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['y']= (int)$row['led'];
		$row1[$i]['label']=$row['lead_owner'];
		$row1[$i]['user_id']=$row['user_id'];	
		$i++;
	}	
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "generate_report_industry_wise_party_list") {

	$cust_ind_id=$POST['cust_ind'];
	$t_id=$POST['t_id'];
	$where='';
	if($cust_ind_id){
		$where.=' and cindu.ci_id='.$POST['cust_ind'];
	}
	if($t_id){
		$where.=' and cust.t_id='.$POST['t_id'];
	}

	$query="SELECT COUNT(cindu.ci_name)as led,cindu.ci_id,cust.cust_id, cindu.ci_name, comp.company_name, tere.t_name, custadd.c_add_location, country.country_name, state.state_name, city.city_name, cc.cc_name, cust.party_type, cust.cust_name, cust.cust_email, cust.cust_mobile, cust.cust_gst, cust.cust_status, cust.cdate, cust.user_id FROM tbl_customer as cust
	left join tbl_customer_industry as cindu on cindu.ci_id=cust.cust_ind 
	left join tbl_customer_category as cc on cc.cc_id=cust.cust_cat 
	left join tbl_company as comp on comp.company_id = cust.company_id 
	left join tbl_cust_address as custadd on custadd.cust_id=cust.cust_id 
	left join country_mst as country on country.countryid=custadd.c_add_country 
	left join state_mst as state on state.stateid=custadd.c_add_state 
	left join city_mst as city on city.cityid=custadd.c_add_city 
	left join territory_mst as tere on tere.t_id=cust.t_id
	where ( 1 AND cust.cust_status = 0 ".$where." AND cust.company_id IN (0,".$_SESSION['company_id'].")) GROUP BY cindu.ci_name";
	$result=$dbcon->query($query);
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['y']= (int)$row['led'];
		$row1[$i]['label']=$row['ci_name'];
		$row1[$i]['user_id']=$row['ci_id'];	
		$i++;
	}	
	echo json_encode($row1);

}
else if(strtolower($POST['mode']) == "generate_report_leads_stages") {
	$source_id=$POST['source_id'];
	$s_date=explode(' - ',$POST['date']);
	$user_id = $POST['user_id'];
	$va .=" and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";

	$va .=' and e.user_id='.$user_id;

	if(empty($source_id)){
		$query="select COUNT(*)as led,e.cdate,op.opp_stage as stage,e.inquiry_name as oppurtunity_name from tbl_inquiry as e left join tbl_task  as et on et.inquiry_id=e.inquiry_id 
		left join tbl_opportunity_mst as op on op.opp_id=e.opp_id
		where e.inquiry_status=0 and et.task_status!=2 and et.entry_type=1 AND e.company_id IN (0,".$_SESSION['company_id'].") ".$va." group by op.opp_stage";
	}else{
		$query="select COUNT(*)as led,e.cdate,op.opp_stage as stage,e.inquiry_name as oppurtunity_name from tbl_inquiry as e left join tbl_task  as et on et.inquiry_id=e.inquiry_id 
		left join tbl_opportunity_mst as op on op.opp_id=e.opp_id where e.inquiry_status=0 and et.task_status!=2 and et.entry_type=1 and e.opp_id=".$source_id." AND e.company_id IN (0,".$_SESSION['company_id'].") ".$va." group by op.opp_stage";
	}

	$result=$dbcon->query($query);
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['y']= (int)$row['led'];
		$row1[$i]['label']=$row['stage'];
					//$row1[$i]['label']=$POST['rep_date'];
		$row1[$i]['stage']=$row['stage'];	
		$i++;
	}	
	echo json_encode($row1);
} else if(strtolower($POST['mode']) == "generate_report_leads_probablity") {
	$s_date=explode(' - ',$POST['date']);
	$source_id=$POST['source_id'];

	$va .=" and DATE(e.cdate) between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";

	if(empty($source_id)){
		$query="select COUNT(*)as led,e.cdate,e.inquiry_name as oppurtunity_name,e.opp_id,e.stage_prob as probablity from tbl_inquiry as e left join tbl_task  as et on et.inquiry_id=e.inquiry_id where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 AND e.company_id IN (0,".$_SESSION['company_id'].") ".$va." group by e.opp_id";
	}else{
		$query="select COUNT(*)as led,e.cdate,e.inquiry_name as oppurtunity_namee.stage_prob as probablity,e.opp_id from tbl_inquiry as e left join tbl_task  as et on et.inquiry_id=e.inquiry_id where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.opp_id=".$source_id." AND e.company_id IN (0,".$_SESSION['company_id'].") ".$va." group by e.inquiry_name";
	}

	$result=$dbcon->query($query);
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['y']= (int)$row['led'];
		$row1[$i]['label']=$row['probablity'];
					//$row1[$i]['label']=$POST['rep_date'];
		$row1[$i]['opp_id']=$row['opp_id'];	
		$i++;
	}	
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "generate_report_vendor_analysis") {

	$_POST=$_GET;
	$s_date=explode(' - ',$POST['rep_po_date']);

	$whr.=" tp.purchaseorder_date between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
			//$query="select COUNT(*)as led,e.closing_date,e.inquiry_name as oppurtunity_name from tbl_inquiry as e left join tbl_task  as et on et.inquiry_id=e.inquiry_id where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 ".$whr." group by ".$group."";
	if(!empty($_POST['vendor_id'])){
		$vendor_id=$_POST['vendor_id'];
	}
	if(!empty($_POST['vendor_id'])){
		$query="SELECT COUNT(*)as led,tp.vender_id,tp.purchaseorder_no,tp.purchaseorder_date,tpt.unit_id,tpt.product_id, tl.l_name as vendorname,tpt.product_qty,p.product_name,p.product_desc,tp.purchaseorder_due_date,tpt.purchaseordertrn_id
		FROM tbl_purchaseorder as tp
		left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
		left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
		left JOIN product_mst as p ON p.product_id=tpt.product_id where ".$whr." and tp.vender_id=$vendor_id AND tp.company_id IN (0,".$_SESSION['company_id'].") group by tl.l_name";
	}else{
		$query="SELECT COUNT(*)as led,tp.vender_id,tp.purchaseorder_no,tp.purchaseorder_date,tpt.unit_id,tpt.product_id, tl.l_name as vendorname,tpt.product_qty,p.product_name,p.product_desc,tp.purchaseorder_due_date,tpt.purchaseordertrn_id
		FROM tbl_purchaseorder as tp
		left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
		left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
		left JOIN product_mst as p ON p.product_id=tpt.product_id where ".$whr." AND tp.company_id IN (0,".$_SESSION['company_id'].") group by tl.l_name";
	}

	$result=$dbcon->query($query);
	$i=0;
	$date=date_create($end_date);
	$start_datename= date_format($date,"Y-F");
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['y']= (int)$row['led'];
		$row1[$i]['label']=$row['vendorname'];
				//$row1[$i]['label1']=$end_date;
		$row1[$i]['vendor_id']=$row['vender_id'];	
		$i++;
	}
	echo json_encode($row1);
} else if(strtolower($POST['mode']) == "generate_report_fg_stock") {
	$pro_id=$POST['product_id'];
	if($pro_id != 'NULL'){
				//$pro_id=implode(",",$pro_id1);
		$whr.=' and pmst.product_id in('.$pro_id.')';
	}
	$query="SELECT SQL_CALC_FOUND_ROWS COUNT(*)as tot_rec,s1.bom_id, s1.product_name, s1.bom_product, min(s1.product_base_qty), s1.req_product_id, s1.req_unit_id, s1.req_product_base_qty
	FROM   ( select bom.bom_id,bom.bom_product,pmst.product_name,btrn.product_id,btrn.product_base_qty, GROUP_CONCAT(btrn.product_id) as req_product_id, GROUP_CONCAT(btrn.product_base_unit) as req_unit_id, GROUP_CONCAT(btrn.product_base_qty) as req_product_base_qty from tbl_bom as bom
	left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
	left join product_mst as pmst on pmst.product_id=bom.bom_product
	where btrn.bom_trn_status=0 and pmst.product_type=0 ".$whr." group by bom.bom_product ) s1
	Group by s1.bom_product
	ORDER BY s1.bom_product 			
	";

	$result=$dbcon->query($query);
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['y']= (int)$row['tot_rec'];
		$row1[$i]['label']=$row['product_name'];
				//$row1[$i]['label']=$POST['rep_date'];
				//$row1[$i]['product_name']=$row['product_name'];	
		$row1[$i]['product_id']=$row['bom_product'];	
		$i++;
	}	
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_month_wise_objection"){
	
	$whr='';
	$post_user_id = 0;
	if ($POST['user_id']) {
		$post_user_id = $POST['user_id'];
		$user_ids = $POST['user_id'];			
		$whr.=' and u.user_id in ('.$user_ids.')';
	}

	$_SESSION['start_date'] = $POST['start_date'];
    $_SESSION['end_date'] = $POST['end_date'];

	$query="SELECT m.month,(select count(u.inquiry_id) as led from tbl_inquiry u 
	where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.inquiry_date) and inquiry_status=0 and u.objection_flag=1 and company_id=".$_SESSION['company_id']." 
	and DATE_FORMAT(u.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) ".$whr.") as objection
	FROM (
	SELECT 'Apr' AS MONTH
	UNION SELECT 'May' AS MONTH
	UNION SELECT 'Jun' AS MONTH
	UNION SELECT 'Jul' AS MONTH
	UNION SELECT 'Aug' AS MONTH
	UNION SELECT 'Sep' AS MONTH
	UNION SELECT 'Oct' AS MONTH
	UNION SELECT 'Nov' AS MONTH
	UNION SELECT 'Dec' AS MONTH
	UNION SELECT 'Jan' AS MONTH
	UNION SELECT 'Feb' AS MONTH
	UNION SELECT 'Mar' AS MONTH
	) AS m
	GROUP BY m.month
	ORDER BY 1+1";

	$objection_counter=$dbcon->query($query);
	$row = array();
	$i=0;
	while($chart=mysqli_fetch_assoc($objection_counter))
	{	
		$row1[$i]['label']=$chart['month'];
		$row1[$i]['y']=intval($chart['objection']);	
		$row1[$i]['link']="inquiry_list_flt_objection/".$chart['month'].'/'.$post_user_id.'/'.$POST['start_date'].'/'.$POST['end_date'];
		$i++;
	}
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_month_wise_won"){
	
	$whr='';
	if ($POST['user_id']) {
		$user_ids=check_user_chein($dbcon,$POST['user_id'],1);			
		$whr.=' and u.user_id in ('.$user_ids.')';
	}

	$query="SELECT m.month,(select sum(u.g_total) as led from tbl_inquiry u 
	where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.won_date) and inquiry_status=0 and u.opp_id=12 and company_id=".$_SESSION['company_id']." 
	and DATE_FORMAT(u.won_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) ".$whr.") as invoice
	FROM (
	SELECT 'Apr' AS MONTH
	UNION SELECT 'May' AS MONTH
	UNION SELECT 'Jun' AS MONTH
	UNION SELECT 'Jul' AS MONTH
	UNION SELECT 'Aug' AS MONTH
	UNION SELECT 'Sep' AS MONTH
	UNION SELECT 'Oct' AS MONTH
	UNION SELECT 'Nov' AS MONTH
	UNION SELECT 'Dec' AS MONTH
	UNION SELECT 'Jan' AS MONTH
	UNION SELECT 'Feb' AS MONTH
	UNION SELECT 'Mar' AS MONTH
	) AS m
	GROUP BY m.month
	ORDER BY 1+1";
	$invoice_counter=$dbcon->query($query);
	$row	= array();
	$i=0;
	while($chart=mysqli_fetch_assoc($invoice_counter))
	{	
		$row1[$i]['label']=$chart['month'];
		$row1[$i]['y']=intval($chart['invoice']);	
		$i++;
	}		
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_lead_by_city") {

	$where = " AND inq.inquiry_date BETWEEN '".date('Y-m-d',strtotime($POST['start_date']))."' AND '".date('Y-m-d',strtotime($POST['end_date']))."'";

	$post_user_id = 0;
	if ($POST['user_id']) {
		$post_user_id = $POST['user_id'];
		$user_ids=$POST['user_id'];
		$where .= " AND inq.user_id in (".$user_ids.")";
	}
		
	if (isset($POST['state_id']) && !empty($POST['state_id'])) {
		$where .= ' AND cit.stateid='.$POST['state_id'];
	}
	
	$query="select count(inq.inquiry_id) as led,cit.city_name,cit.cityid,cit.stateid from tbl_inquiry as inq
	LEFT JOIN tbl_task AS task ON task.inquiry_id = inq.inquiry_id
	left join tbl_cust_address as cust_add on cust_add.cust_id=inq.cust_id
	left join city_mst as cit on cit.cityid=cust_add.c_add_city
	where inq.inquiry_status=0 and cit.city_status=0 and task.task_status = 0 $where  
	and inq.company_id=".$_SESSION['company_id']."
	group by cit.cityid";

	$result=$dbcon->query($query);
	$row1 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['label']=$row['city_name'];
		$row1[$i]['y']=intval($row['led']);
		$row1[$i]['link']="inquiry_list_flt_city/".$row['stateid']."/".$row['cityid']."/".$post_user_id.'/'.$POST['start_date'].'/'.$POST['end_date'];
		$i++;
	}	
	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_lead_by_state") {

	$where =" AND inq.inquiry_date BETWEEN '".date('Y-m-d',strtotime($POST['start_date']))."' AND '".date('Y-m-d',strtotime($POST['end_date']))."'";

	$post_user_id = 0;
	if ($POST['user_id']) {
		$post_user_id = $POST['user_id'];
		// $user_ids=check_user_chein($dbcon,$POST['user_id'],1);
		$user_ids=$POST['user_id'];
		$where .= " AND inq.user_id in (".$user_ids.")";
	}

	$query = "select count(inq.inquiry_id) as led,cit.state_name, cit.stateid from tbl_inquiry as inq LEFT JOIN tbl_task AS task ON task.inquiry_id = inq.inquiry_id left join tbl_cust_address as cust_add on cust_add.cust_id=inq.cust_id left join state_mst as cit on cit.stateid=cust_add.c_add_state where inq.inquiry_status=0 and task.task_status = 0 and cit.state_status=0 $where and inq.company_id=".$_SESSION['company_id']." group by cit.stateid";

	$result=$dbcon->query($query);
	$row1 = array();
	$i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
		$row1[$i]['label']=$row['state_name'];
		$row1[$i]['y']=intval($row['led']);
		$row1[$i]['link']="inquiry_list_flt_state/".$row['stateid']."/".$post_user_id.'/'.$POST['start_date'].'/'.$POST['end_date'];
		$i++;
	}	

	echo json_encode($row1);

} else if(strtolower($POST['mode']) == "load_counts") {	
	
	if (isset($POST['date']) && !empty($POST['date'])) {
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start'] = $s_date[0];
		$_SESSION['end'] = $s_date[1];
	} else {
		$_SESSION['start'] = date('Y-m-01');
		$_SESSION['end'] = date('Y-m-t');	
	}

	$start_date = $_SESSION['start'];
	$end_date = $_SESSION['end'];

	$_SESSION['start_date'] = $start_date;
	$_SESSION['end_date'] = $end_date;

	$business_achieved = $opportunity_onhand = $pending_quotation = $lost_opportunity = $hot_leads = 0;
	$companyConfiguration=getCompanyConfiguration($dbcon);

	$pending_quotation = $dbcon->query("SELECT count(DISTINCT task.task_id) as pending_quotation from tbl_task as task
		WHERE task.task_status=0 and task.entry_type=1 AND task.company_id = ".$_SESSION['company_id']." and DATE_FORMAT(task.task_due_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($start_date))."'AS DATE) and CAST('".date('Y-m-d',strtotime($end_date))."'AS DATE) and task.task_type_id='15' order by task_due_date DESC")->fetch_object()->pending_quotation;

	if($companyConfiguration['forecast_calculation']==1){
		$business_achieved = $dbcon->query("SELECT sum(inq.g_total) as business_achieved FROM tbl_inquiry as inq WHERE inq.inquiry_status=0 and inq.opp_id=".WON." and inq.stage_prob=100 and
			DATE_FORMAT(inq.won_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($start_date))."'AS DATE) and CAST('".date('Y-m-d',strtotime($end_date))."'AS DATE) 
			and inq.company_id=".$_SESSION['company_id'])->fetch_object()->business_achieved;
	}else if($companyConfiguration['forecast_calculation']==2){
		$business_achieved = $dbcon->query("SELECT sum(inq.g_total) as business_achieved FROM tbl_sales_order as inq WHERE inq.sales_order_status=0 and
			DATE_FORMAT(inq.sales_order_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($start_date))."'AS DATE) and CAST('".date('Y-m-d',strtotime($end_date))."'AS DATE) 
			and inq.company_id=".$_SESSION['company_id'])->fetch_object()->business_achieved;
	}else if($companyConfiguration['forecast_calculation']==3){
		$business_achieved = $dbcon->query("SELECT sum(inq.g_total) as business_achieved FROM tbl_invoice as inq WHERE inq.invoice_status=0 and
			DATE_FORMAT(inq.invoice_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($start_date))."'AS DATE) and CAST('".date('Y-m-d',strtotime($end_date))."'AS DATE) 
			and inq.company_id=".$_SESSION['company_id'])->fetch_object()->business_achieved;
	}

	$date_condition = "AND DATE_FORMAT(inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($start_date))."'AS DATE) and CAST('".date('Y-m-d',strtotime($end_date))."'AS DATE)";

	$opportunity_onhand = $dbcon->query("SELECT count(inquiry_id) as opportunity_onhand FROM `tbl_inquiry` WHERE inquiry_status=0 and `opp_id` = ".WON." $date_condition AND company_id=".$_SESSION['company_id']."")->fetch_object()->opportunity_onhand;

	$lost_opportunity = $dbcon->query("SELECT count(inquiry_id) as lost_opportunity FROM `tbl_inquiry` WHERE inquiry_status=0 and `opp_id` = ".LOST." $date_condition AND company_id=".$_SESSION['company_id']."")->fetch_object()->lost_opportunity;

	$hot_leads = $dbcon->query("SELECT count(inquiry_id) as hot_leads FROM `tbl_inquiry` WHERE opp_id NOT IN(".WON.",".LOST.") AND inquiry_status=0 and `sales_stage_id` = ".HOT." $date_condition AND company_id=".$_SESSION['company_id']."")->fetch_object()->hot_leads;

	$cold_leads = $dbcon->query("SELECT count(inquiry_id) as cold_leads FROM `tbl_inquiry` WHERE opp_id NOT IN(".WON.",".LOST.") AND inquiry_status=0 and `sales_stage_id` = ".COLD." $date_condition AND company_id=".$_SESSION['company_id']."")->fetch_object()->cold_leads;

	$warm_leads = $dbcon->query("SELECT count(inquiry_id) as warm_leads FROM `tbl_inquiry` WHERE opp_id NOT IN(".WON.",".LOST.") AND inquiry_status=0 and `sales_stage_id` = ".WARM." $date_condition AND company_id=".$_SESSION['company_id']."")->fetch_object()->warm_leads;

	$not_appli_leads = $dbcon->query("SELECT count(inquiry_id) as not_appli_leads FROM `tbl_inquiry` WHERE opp_id NOT IN(".WON.",".LOST.") AND inquiry_status=0 and `sales_stage_id` = ".NOT_APPLICABLE." $date_condition AND company_id=".$_SESSION['company_id']."")->fetch_object()->not_appli_leads;

	$count['business_achieved_counts']=floatval($business_achieved);
	$count['business_achieved_words']=ucwords(convert_number_to_words(floatval($business_achieved)));

	$count['opportunity_onhand_counts']=floatval($opportunity_onhand);
	$count['opportunity_onhand_words']=ucwords(convert_number_to_words(floatval($opportunity_onhand)));

	$count['pending_quotation_counts']=floatval($pending_quotation);
	$count['pending_quotation_words']=ucwords(convert_number_to_words(floatval($pending_quotation)));

	$count['lost_opportunity_counts']=floatval($lost_opportunity);
	$count['lost_opportunity_words']=ucwords(convert_number_to_words(floatval($lost_opportunity)));

	$count['hot_leads_counts']=floatval($hot_leads);
	$count['hot_leads_words']=ucwords(convert_number_to_words(floatval($hot_leads)));

	$count['cold_leads_counts']=floatval($cold_leads);
	$count['cold_leads_words']=ucwords(convert_number_to_words(floatval($cold_leads)));

	$count['warm_leads_counts']=floatval($warm_leads);
	$count['warm_leads_words']=ucwords(convert_number_to_words(floatval($warm_leads)));

	$count['not_appli_leads_counts']=floatval($not_appli_leads);
	$count['not_appli_leads_words']=ucwords(convert_number_to_words(floatval($not_appli_leads)));

	echo json_encode($count);
}
else if(strtolower($POST['mode']) == "load_target_chart") {
	$date=get_calender_sdate($POST['t_pro_year']);	
	$t_pro_id=$POST['t_pro_id'];
    $log_user_id=$_SESSION['user_id'];//53
    $t_pro_year=$POST['t_pro_year'];
    if($POST['t_pro_wise']=='1'){//Qty Wise Load Target data
    	$query="SELECT m.month,(SELECT ptrn.ter_target_qty FROM `tbl_f_byuser_pro_inrtrn` as ptrn
    	left join tbl_forecast_byuser_pro as mst on mst.forecast_id=ptrn.forecast_id
    	where ptrn.f_ter_trn_status=0 and mst.forecast_status=0 and ptrn.product_id='$t_pro_id' and ptrn.ref_user_id='$log_user_id' and mst.f_year='$t_pro_year' AND ptrn.company_id = ".$_SESSION['company_id']." and mst.f_period_id=MONTH(STR_TO_DATE(m.month,'%M'))) as total ,
    	(SELECT sum(qtrn.product_qty) FROM `tbl_quotation` as qt
    	left join tbl_quotation_trn as qtrn on qtrn.quotation_id=qt.quotation_id
    	where qtrn.quot_trn_status=0 and qt.quotation_status=0 and qtrn.inv_done_status=1 and qtrn.product_id='".$t_pro_id."' and qt.quot_won_user_id='$log_user_id' and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(qt.quotation_date) and qt.quotation_date between '".$date['start_date']."' and '".$date['end_date']."') as total_paid FROM 
    	( SELECT 'Jan' AS MONTH 
    	UNION SELECT 'Feb' AS MONTH 
    	UNION SELECT 'Mar' AS MONTH
    	UNION SELECT 'Apr' AS MONTH 
    	UNION SELECT 'May' AS MONTH
    	UNION SELECT 'Jun' AS MONTH 
    	UNION SELECT 'Jul' AS MONTH 
    	UNION SELECT 'Aug' AS MONTH 
    	UNION SELECT 'Sep' AS MONTH 
    	UNION SELECT 'Oct' AS MONTH 
    	UNION SELECT 'Nov' AS MONTH 
    	UNION SELECT 'Dec' AS MONTH  ) AS m GROUP BY m.month ORDER BY 1+1";

    } else {//Amount wise Load Target data
    	$query="SELECT m.month,(SELECT ptrn.ter_target_amt FROM `tbl_f_byuser_pro_inrtrn` as ptrn
    	left join tbl_forecast_byuser_pro as mst on mst.forecast_id=ptrn.forecast_id
    	where ptrn.f_ter_trn_status=0 and mst.forecast_status=0 and ptrn.product_id='$t_pro_id' and ptrn.ref_user_id='$log_user_id' and mst.f_year='$t_pro_year' AND ptrn.company_id = ".$_SESSION['company_id']." and mst.f_period_id=MONTH(STR_TO_DATE(m.month,'%M'))) as total, 
    	(SELECT sum(qtrn.product_total) FROM `tbl_quotation` as qt
    	left join tbl_quotation_trn as qtrn on qtrn.quotation_id=qt.quotation_id
    	where qtrn.quot_trn_status=0 and qt.quotation_status=0 and qtrn.inv_done_status=1 and qtrn.product_id='$t_pro_id' and qt.quot_won_user_id='$log_user_id' and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(qt.quotation_date) and qt.quotation_date between '".$date['start_date']."' and '".$date['end_date']."') as total_paid FROM 
    	( SELECT 'Jan' AS MONTH 
    	UNION SELECT 'Feb' AS MONTH 
    	UNION SELECT 'Mar' AS MONTH
    	UNION SELECT 'Apr' AS MONTH 
    	UNION SELECT 'May' AS MONTH
    	UNION SELECT 'Jun' AS MONTH 
    	UNION SELECT 'Jul' AS MONTH 
    	UNION SELECT 'Aug' AS MONTH 
    	UNION SELECT 'Sep' AS MONTH 
    	UNION SELECT 'Oct' AS MONTH 
    	UNION SELECT 'Nov' AS MONTH 
    	UNION SELECT 'Dec' AS MONTH  ) AS m GROUP BY m.month ORDER BY 1+1";
    }
    $tar_counter = $dbcon->query($query);
    $row = array();
    $i=0;
    while($chart= mysqli_fetch_assoc($tar_counter))
    {	
    	$row[$chart['month']][]=intval($chart['total']);
    	$row[$chart['month']][]=intval($chart['total_paid']);
    	$row[]= $chart['month'];
    }

    echo json_encode($row); 
}

function get_sdate($date)
{
	$sdate['start_date']=date('01-04-'.$date);
	$sdate['end_date']=date('31-03-'.($date+1));
	return $sdate;	
}

function get_calender_sdate($date)
{
	$sdate['start_date']=date($date.'-01-01');
	$sdate['end_date']=date(($date).'-12-31');
	return $sdate;	
}


function common_crm_chart_conditions($post_data,$dbcon) {

	$_SESSION['start_date'] = $post_data['start_date'];
	$_SESSION['end_date'] = $post_data['end_date'];

	$where = "";
	$post_user_id = 0;
	if(isset($post_data['user_id']) && !empty($post_data['user_id'])) {
		$post_user_id = $post_data['user_id'];
		$where .= " AND inq.user_id = '".$post_data['user_id']."'";
	} else if($_SESSION['user_type']!=2){ 
		$user_funnel_id = check_user_chein($dbcon,$_SESSION['user_id'],1);
		$where .= " and inq.user_id IN (".$user_funnel_id.")";
	}

	$start_date = $post_data['start_date'];
	$end_date = $post_data['end_date'];
	if(!empty($start_date) && !empty($end_date)){
		$where .="  AND DATE(inq.inquiry_date) >= '".date('Y-m-d',strtotime($start_date))."' AND  DATE(inq.inquiry_date) <= '".date('Y-m-d',strtotime($end_date))."'";
	}

	if($_SESSION['user_type']!=2){ 
		$where.=" and FIND_IN_SET($_SESSION[user_id],task.show_user_ids)";
	}

	$data = array('where' => $where, 'start_date' => $start_date, 'end_date' => $end_date, 'user_id' => $post_user_id);

	return $data;
}
?>