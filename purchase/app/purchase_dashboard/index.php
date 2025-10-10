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

if(strtolower($POST['mode']) == "load_purchase_po_com") {
	$finacial_date = getFinacialyear_data($dbcon);
	$date['start_date']=$finacial_date['financial_start_date'];
	$date['end_date']=$finacial_date['financial_end_date'];
	/*$date=get_calender_sdate($POST['t_pro_year']);	
	$t_pro_id=$POST['t_pro_id'];
    $log_user_id=$_SESSION['user_id'];//53
    $t_pro_year=$POST['t_pro_year'];*/
    if($POST['pur_amount_filter']==0){
    	$query="SELECT m.month,(SELECT sum(trn.product_conv_qty) FROM tbl_purchaseordertrn as trn LEFT JOIN `tbl_purchaseorder` as po ON trn.purchaseorder_id = po.purchaseorder_id
    	where po.status=0 and po.po_approval_status=1 and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(po.purchaseorder_date) and po.purchaseorder_date between '".$date['start_date']."' and '".$date['end_date']."' AND trn.purchaseordertrn_status = 0) as pototal, 
    	(SELECT sum(trns.product_conv_qty) FROM tbl_potrancation as trns LEFT JOIN `tbl_pono` as pur on pur.po_id = trns.po_id
    	where pur.status=0 and pur.approve_status=0  and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(pur.po_date) and pur.po_date between '".$date['start_date']."' and '".$date['end_date']."' AND trns.potrancation_status = 0) as purchase_total FROM 
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
    	//echo $query;exit;
    }else{
    	$query="SELECT m.month,(SELECT sum(po.g_total) FROM `tbl_purchaseorder` as po
    	where po.status=0 and po.po_approval_status=1 and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(po.purchaseorder_date) and po.purchaseorder_date between '".$date['start_date']."' and '".$date['end_date']."') as pototal, 
    	(SELECT sum(pur.g_total) FROM `tbl_pono` as pur
    	where pur.status=0 and pur.approve_status=0  and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(pur.po_date) and pur.po_date between '".$date['start_date']."' and '".$date['end_date']."') as purchase_total FROM 
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
    $row1 = array();
    $i=0;
   /* while($chart= mysqli_fetch_assoc($tar_counter))
    {	
    	$row2 = array();
    	$row3 = array();
    	$row2['label']=$chart['month'];
    	$row2['Y']=intval($chart['pototal']);
    	$povar=$chart['month']."po";
    	$pur=$chart['month']."pur";
    	$row[$povar][]=$row2;

    	$row3['label']=$chart['month'];
    	$row3['Y']=intval($chart['purchase_total']);
    	$row[$pur][]=$row3;
    	$row[]= $chart['month'];
    	$i++;
    }	*/

    while($chart= mysqli_fetch_assoc($tar_counter))
    {	
    	//$row[$i]["lable"]=$chart['min_rate'];
    	$row[$i]["label"]= $chart['month'];
    	$row[$i]["y"]=intval($chart['pototal']);

    	$row1[$i]["label"]=$chart['month'];
    	$row1[$i]["y"]=intval($chart['purchase_total']);
    	
    	$i++;
    }	
    $re[0]=$row;
    $re[1]=$row1;
    // $re[2]=$query;
    echo json_encode($re); 
}
else if(strtolower($POST['mode']) == "getcust") {
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
}
else if(strtolower($POST['mode']) == "top_20_vender") {
			//$date=get_sdate($POST['c_year']);
	$finacial_date = getFinacialyear_data($dbcon);
	$POST['end_date']=$finacial_date['financial_end_date'];
	$POST['start_date']=$finacial_date['financial_start_date'];
	
			//$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
	if($POST['vendor_filter']==0){
		$query="select sum(trn.product_conv_qty) as purchase_total,itrn.l_name as pg_name from tbl_potrancation as trn LEFT JOIN tbl_pono as pro on pro.po_id = trn.po_id
		left join tbl_ledger as itrn on itrn.l_id=pro.vender_id where pro.status=0 and trn.potrancation_status = 0 and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") group by pro.vender_id order by purchase_total desc limit 0,20";

		$query_total="select sum(trn.product_conv_qty) as purchase_total from tbl_potrancation as trn LEFT JOIN tbl_pono as pro ON pro.po_id = trn.po_id	where pro.status=0 and trn.potrancation_status = 0 and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") ";
	}else{
		$query="select sum(pro.g_total) as purchase_total,itrn.l_name as pg_name from tbl_pono as pro
		left join tbl_ledger as itrn on itrn.l_id=pro.vender_id	where pro.status=0 and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") group by pro.vender_id order by purchase_total desc limit 0,20";

		$query_total="select sum(pro.g_total) as purchase_total from tbl_pono as pro
		where pro.status=0 and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") ";
	}
	//var_dump($query);
	$result_total=$dbcon->query($query_total);
	$row_t=mysqli_fetch_assoc($result_total);
	$amo_total=$row_t['purchase_total'];
	$row1 = array();
	$i=0;$per=0;
	$result=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($result))
	{	
		$per=($row['purchase_total']*100)/$amo_total;
		$row1[$i]['label']=$row['pg_name'];
		//$row1[$i]['y']=number_format($row['purchase_total'],2,".","");	
		$row1[$i]['y']=number_format($per,2,".","");
		$i++;
	}
	//print_r($row1);
	//$row1[0]['sql']=$query;	
	echo json_encode($row1);
}
else if(strtolower($POST['mode']) == "top_20_product") {
			//$date=get_sdate($POST['c_year']);
	$finacial_date = getFinacialyear_data($dbcon);
	$POST['end_date']=$finacial_date['financial_end_date'];
	$POST['start_date']=$finacial_date['financial_start_date'];
			//$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
	if($POST['product_filter']==0){
		$query="select sum(itrn.product_conv_qty) as purchase_total,p.product_name as pg_name from tbl_pono as pro
		left join tbl_potrancation as itrn on itrn.po_id=pro.po_id
		left join product_mst as p on p.product_id=itrn.product_id
		where pro.status=0 and itrn.potrancation_status=0
		and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") group by itrn.product_id order by purchase_total desc limit 0,20";

		$query_total="select sum(itrn.product_conv_qty) as purchase_total from tbl_pono as pro
		left join tbl_potrancation as itrn on itrn.po_id=pro.po_id
		left join product_mst as p on p.product_id=itrn.product_id
		where pro.status=0 and itrn.potrancation_status=0
		and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") ";
	}else{
		$query="select sum(itrn.total) as purchase_total,p.product_name as pg_name from tbl_pono as pro
		left join tbl_potrancation as itrn on itrn.po_id=pro.po_id
		left join product_mst as p on p.product_id=itrn.product_id
		where pro.status=0 and itrn.potrancation_status=0
		and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") group by itrn.product_id order by purchase_total desc limit 0,20";

		$query_total="select sum(itrn.total) as purchase_total from tbl_pono as pro
		left join tbl_potrancation as itrn on itrn.po_id=pro.po_id
		left join product_mst as p on p.product_id=itrn.product_id
		where pro.status=0 and itrn.potrancation_status=0
		and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") ";
	}
	//var_dump($query);
	$result_total=$dbcon->query($query_total);
	$row_t=mysqli_fetch_assoc($result_total);
	$amo_total=$row_t['purchase_total'];

	$row1 = array();
	$i=0;$per=0;
	$result=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($result))
	{	
		$per=($row['purchase_total']*100)/$amo_total;
		$row1[$i]['label']=$row['pg_name'];
				//$row1[$i]['y']=intval($row['purchase_total']);	
		$row1[$i]['y']=number_format($per,2,".","");		
		$i++;
	}
			//$row1[0]['sql']=$query;	
	echo json_encode($row1);
}
else if(strtolower($POST['mode']) == "top_20_cat") {
			//$date=get_sdate($POST['c_year']);
	$finacial_date = getFinacialyear_data($dbcon);
	$POST['end_date']=$finacial_date['financial_end_date'];
	$POST['start_date']=$finacial_date['financial_start_date'];
	//var_dump($POST);
			//$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
	if($POST['pur_cat_filter']==0){
		$query="select sum(itrn.product_conv_qty) as purchase_total,cat.cat_name as pg_name,p.product_category from tbl_pono as pro
		left join tbl_potrancation as itrn on itrn.po_id=pro.po_id
		left join product_mst as p on p.product_id=itrn.product_id
		left join tbl_category as cat on cat.cat_id=p.product_category
		where pro.status=0 and itrn.potrancation_status=0
		and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") group by p.product_category order by purchase_total desc limit 0,5";

		$query_total="select sum(itrn.product_conv_qty) as purchase_total from tbl_pono as pro
		left join tbl_potrancation as itrn on itrn.po_id=pro.po_id
		left join product_mst as p on p.product_id=itrn.product_id
		left join tbl_category as cat on cat.cat_id=p.product_category
		where pro.status=0 and itrn.potrancation_status=0
		and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") ";
	}else{
		$query="select sum(itrn.total) as purchase_total,cat.cat_name as pg_name,p.product_category from tbl_pono as pro
		left join tbl_potrancation as itrn on itrn.po_id=pro.po_id
		left join product_mst as p on p.product_id=itrn.product_id
		left join tbl_category as cat on cat.cat_id=p.product_category
		where pro.status=0 and itrn.potrancation_status=0
		and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") group by p.product_category order by purchase_total desc limit 0,5";

		$query_total="select sum(itrn.total) as purchase_total from tbl_pono as pro
		left join tbl_potrancation as itrn on itrn.po_id=pro.po_id
		left join product_mst as p on p.product_id=itrn.product_id
		left join tbl_category as cat on cat.cat_id=p.product_category
		where pro.status=0 and itrn.potrancation_status=0
		and DATE_FORMAT(pro.po_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") ";
	}
	//echo $query;exit;

	$result_total=$dbcon->query($query_total);
	$row_t=mysqli_fetch_assoc($result_total);
	$amo_total=$row_t['purchase_total'];

	$row1 = array();
	$i=0;$per=0;
	$result=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($result))
	{	
		if($row['product_category'] =='0'){
			$row['pg_name'] = 'PRIMARY';
		}else{
			$row['pg_name']=$row['pg_name'];
		}
		$per=($row['purchase_total']*100)/$amo_total;
		$row1[$i]['label']=$row['pg_name'];
		$row1[$i]['y']=intval($row['purchase_total']);	
		//var_dump($row1[$i]['y']=intval($row['purchase_total']));		
		$i++;
	}
			//$row1[0]['sql']=$query;	
	echo json_encode($row1);
}
else if(strtolower($POST['mode']) == "paymentremainder") {
	$payment_remainder="SELECT invoice_no, invoice.invoice_date, cust.company_name,DATE_ADD(invoice_date,INTERVAL cust.terms DAY) as ex_date, invoice_id, cust_address, cust_mobile, cust_email FROM tbl_invoice as invoice inner join tbl_customer as cust on cust.cust_id=invoice.cust_id WHERE invoice_status=0 and invoice_id=".$POST['invoiceid'];
	$result_remainder=mysqli_fetch_assoc($dbcon->query($payment_remainder));
	echo json_encode($result_remainder);

}
else if(strtolower($POST['mode']) == "pass_session") {
			/*$_SESSION['company_id'] = $POST['company_id'];
			$_SESSION['company_name'] = $POST['company_name'];
			echo $POST['company_name'];*/
			
			if(LOGIN_SETTING=="1" && $_SESSION['LOGGED_IN'])
			{
				if($POST['company_id']>0)
				{
					$where=" and user_type=2 and company_id=".$POST['company_id'];
				}
				else if($POST['company_id']=="0")
				{
					$where=" and user_type=1 and company_id=".$POST['company_id'];
				}
				$sql = "SELECT `user_id`, `user_name`, `user_mail`,`user_type`, `user_phone`, `user_company`, `user_country`,`user_stat`,  `user_rid`, `user_tmst`, `user_date`, `setup`, `payment_status`,datediff (CURDATE(),user_tmst) as datedif,print_align,`company_id` FROM `users` WHERE active=0  ".$where;
				$result=$dbcon->query($sql);
				$row1 = $result->fetch_assoc();
				$_SESSION['LOGGED_IN'] = true;
				$_SESSION['title'] = TITLE;
				$_SESSION['domain'] = DOMAIN;
				$_SESSION['user_id'] = $row1['user_id'];
				$_SESSION['company_id'] = $row1['company_id'];
				$_SESSION['company_name'] = $row1['user_name'];
				$_SESSION['user_name'] = ucwords(strtolower($row1['user_name']));
				$_SESSION['user_type'] = $row1['user_type'];
				$_SESSION['user_company'] = $row1['user_company'];
				if($row1['print_align']=="0")//center
				{
					$_SESSION['print_page']='print_new';
				}
				else if($row1['print_align']=="2")//right
				{
					$_SESSION['print_page']='print_right';
				}
				else if($row1['print_align']=="1")//left
				{
					$_SESSION['print_page']='print_left';
				}
				$row['msg']=1;
			}
			else
			{
				$row['response']=getusertype($dbcon,0," and (usertype_id=2 or company_id=".$POST['company_id'].")");//usrtype_id=2 Company Admin
				$row['msg']=0;
			}
			echo json_encode($row);
		}else if(strtolower($POST['mode']) == "lead_circle") {
			//$date=get_sdate($POST['c_year']);
			$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
			$query="select count(inquiry_id) as led,rf.rb_name from tbl_inquiry as e 
			left join tbl_refer_by as rf on rf.rb_id=e.rb_id
			where e.inquiry_status=0 and e.user_id in (".$user_ids.") 
			and DATE_FORMAT(e.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) group by e.rb_id";
			
			$invoice_turnover=$dbcon->query($query);
			$row1 = array();
			$i=0;
			while($invoice_circle=mysqli_fetch_assoc($invoice_turnover))
			{	
				$row1[$i]['label']=intval($invoice_circle['rb_name']);
				$row1[$i]['symbol']=$invoice_circle['rb_name'];
				$row1[$i]['y']=$invoice_circle['led'];			
				$i++;
			}	
				//$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_employee_sales") {
			$user_ids=check_user_chein($dbcon,$POST['d_user_id'],1);
			/* $qry1="SELECT SUM(product_amount) AS total,cat.category_name as name from tbl_tranction as tan inner join category_mst as cat on cat.categoryid=tan.categoryid inner join tbl_invoice as invoice on invoice.invoice_id=tan.invoice_id where invoice_date>='".$date['start_date']."' AND invoice_date<='".$date['end_date']."' and trancation_status=0 GROUP BY cat.categoryid"; */
			
			$query="select led,e.user_name from users as e
			left join (select sum(i.g_total) as led,i.user_id from tbl_inquiry as i 
			where i.inquiry_status=0 and i.opp_id=12 and DATE_FORMAT(i.won_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['d_start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['d_end_date']))."'AS DATE) group by i.user_id) as dem on dem.user_id=e.user_id
			where e.active=0 and e.user_type=9 and e.user_id in (".$user_ids.") group by e.user_id";
			
			$invoice_turnover=$dbcon->query($query);
			$row1 = array();
			$i=0;
			while($invoice_circle=mysqli_fetch_assoc($invoice_turnover))
			{	
				$row1[$i]['label']=$invoice_circle['user_name'];
				$row1[$i]['y']=intval($invoice_circle['led']);			
				$i++;
			}	
				//$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_lead_by_product") {
			//$date=get_sdate($POST['c_year']);
			$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
			$query="select count(inq.inquiry_id) as led,pro.product_name as pg_name from product_mst as pro
			left join tbl_inquiry_trn as itrn on itrn.product_id=pro.product_id
			left join tbl_inquiry as inq on inq.inquiry_id=itrn.inquiry_id
			where inq.inquiry_status=0 and itrn.inquiry_trn_status=0 and pro.product_status	=0 
			and inq.user_id in (".$user_ids.") 
			and DATE_FORMAT(inq.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) group by pro.product_id";
			
			$invoice_turnover=$dbcon->query($query);
			$row1 = array();
			$i=0;
			while($invoice_circle=mysqli_fetch_assoc($invoice_turnover))
			{	
				$row1[$i]['label']=$invoice_circle['pg_name'];
				$row1[$i]['y']=intval($invoice_circle['led']);			
				$i++;
			}	
				//$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_funal") {
			//$date=get_sdate($POST['c_year']);
			$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
			$query="select count(inquiry_id) as led,rf.opp_stage from tbl_inquiry as e 
			left join tbl_opportunity_mst as rf on rf.opp_id=e.opp_id
			where e.inquiry_status=0 and e.user_id in (".$user_ids.") 
			and DATE_FORMAT(e.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) 
			group by rf.opp_id order by rf.opp_priority";
			
			$invoice_turnover=$dbcon->query($query);
			$row1 = array();
			$i=0;
			while($invoice_circle=mysqli_fetch_assoc($invoice_turnover))
			{	
				$row1[$i]['label']=$invoice_circle['opp_stage'];
				$row1[$i]['y']=intval($invoice_circle['led']);			
					//$row1[$i]['y']=20000;			
				$i++;
			}	
				//$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_month_wise_won"){
			//$date=get_sdate($POST['c_year']);
			$user_ids=check_user_chein($dbcon,$POST['user_id'],1);			
			$whr='';
			$whr.=' and u.user_id in ('.$user_ids.')';
			
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
			//	echo $query;
			$row	= array();
			$i=0;
			while($chart=mysqli_fetch_assoc($invoice_counter))
			{	
				$row1[$i]['label']=$chart['month'];
				$row1[$i]['y']=intval($chart['invoice']);	
				$i++;
			}		
				//var_dump($row);	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_lead_by_city") {
			//$date=get_sdate($POST['c_year']);
			$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
			$query="select count(inq.inquiry_id) as led,cit.city_name from tbl_inquiry as inq
			left join tbl_cust_address as cust_add on cust_add.cust_id=inq.cust_id
			left join city_mst as cit on cit.cityid=cust_add.c_add_city
			where inq.inquiry_status=0 and cit.city_status=0 and inq.user_id in (".$user_ids.") 
			and DATE_FORMAT(inq.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) 
			group by cit.cityid";
			
			$invoice_turnover=$dbcon->query($query);
			$row1 = array();
			$i=0;
			while($invoice_circle=mysqli_fetch_assoc($invoice_turnover))
			{	
				$row1[$i]['label']=$invoice_circle['city_name'];
				$row1[$i]['y']=intval($invoice_circle['led']);			
				$i++;
			}	
				//$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_lead_by_state") {
			//$date=get_sdate($POST['c_year']);
			$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
			$query="select count(inq.inquiry_id) as led,cit.state_name from tbl_inquiry as inq
			left join tbl_cust_address as cust_add on cust_add.cust_id=inq.cust_id
			left join state_mst as cit on cit.stateid=cust_add.c_add_state
			where inq.inquiry_status=0 and cit.state_status=0 and inq.user_id in (".$user_ids.") 
			and DATE_FORMAT(inq.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) 
			group by cit.stateid";
			
			$invoice_turnover=$dbcon->query($query);
			$row1 = array();
			$i=0;
			while($invoice_circle=mysqli_fetch_assoc($invoice_turnover))
			{	
				$row1[$i]['label']=$invoice_circle['state_name'];
				$row1[$i]['y']=intval($invoice_circle['led']);			
				$i++;
			}	
				//$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_counts") {
			//$date=get_sdate($POST['c_year']);
			$start_date = date('Y-m-01');
			$end_date = date('Y-m-t');
			$business_achieved = $opportunity_onhand = $pending_quotation = $lost_opportunity = $hot_leads = 0;

			$pending_quotation = $dbcon->query("SELECT count(DISTINCT task.task_id) as pending_quotation
				from tbl_task as task
				WHERE task.task_status=0 and task.entry_type=1 
				and DATE_FORMAT(task.task_due_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($start_date))."'AS DATE) and CAST('".date('Y-m-d',strtotime($end_date))."'AS DATE) 
				and task.task_type_id='15' 
				order by task_due_date DESC")->fetch_object()->pending_quotation;
                    //$pending_quotation = count_usr_pen_tsk($dbcon,15,$_SESSION['user_id']);

			$business_achieved = $dbcon->query("SELECT sum(inq.g_total) as business_achieved 
				FROM tbl_inquiry as inq
				WHERE inq.inquiry_status=0 and inq.opp_id=12 and inq.stage_prob=100 and
				DATE_FORMAT(inq.won_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($start_date))."'AS DATE) and CAST('".date('Y-m-d',strtotime($end_date))."'AS DATE) 
				and inq.company_id=".$_SESSION['company_id'])->fetch_object()->business_achieved;
			
			$opportunity_onhand = $dbcon->query("SELECT count(inquiry_id) as opportunity_onhand FROM `tbl_inquiry` 
				WHERE opp_id NOT IN(".WON.",".LOST.") AND inquiry_date >= '".$start_date."' AND inquiry_date <= '".$end_date."' 
				")->fetch_object()->opportunity_onhand;

			$lost_opportunity = $dbcon->query("SELECT count(inquiry_id) as lost_opportunity FROM `tbl_inquiry` 
				WHERE `opp_id` = ".LOST." AND inquiry_date >= '".$start_date."' AND inquiry_date <= '".$end_date."' 
				")->fetch_object()->lost_opportunity;

			$hot_leads = $dbcon->query("SELECT count(inquiry_id) as hot_leads FROM `tbl_inquiry` 
				WHERE opp_id NOT IN(".WON.",".LOST.") AND `sales_stage_id` = ".HOT." AND inquiry_date >= '".$start_date."' AND inquiry_date <= '".$end_date."' 
				")->fetch_object()->hot_leads;
			
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
			echo json_encode($count);
		}
		else if(strtolower($POST['mode']) == "dashbord_count") {
			 $today_date = date('Y-m-d');
			 
			 $over_due_inword ="SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty 

				FROM tbl_purchaseorder_delivery_date as pod 

				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 

				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 

				left join tbl_ledger as led on led.l_id=po.vender_id 

				left join branch_mst as bms on bms.branch_id=pod.branch_id 

				left join product_mst as pmst on pmst.product_id=trn.product_id 

				left join tbl_category as tc on pmst.product_category=tc.cat_id 

				left join unit_mst as unit on unit.unitid=trn.unit_id 

				where pod.po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and po.po_approval_status = 1 and trn.used_status=0 and po.po_type = 0 and delivery_date<'$today_date' and pod.company_id=".$_SESSION['company_id']."  Group by pod.po_delivery_date_id ";

			 $over_due_inworde=mysqli_num_rows($dbcon->query($over_due_inword));

			 $today_inward="SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty 

				FROM tbl_purchaseorder_delivery_date as pod 

				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 

				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 

				left join tbl_ledger as led on led.l_id=po.vender_id 

				left join branch_mst as bms on bms.branch_id=pod.branch_id 

				left join product_mst as pmst on pmst.product_id=trn.product_id 

				left join tbl_category as tc on pmst.product_category=tc.cat_id 

				left join unit_mst as unit on unit.unitid=trn.unit_id 

				where  pod.po_delivery_date_status=0 and po.po_approval_status = 1 and delivery_date='$today_date' and trn.used_status=0 and po.po_type = 0 and pod.company_id=".$_SESSION['company_id']." Group by pod.po_delivery_date_id ORDER BY pod.delivery_date desc ";
			 //echo $today_inward; exit;

			 $today_inwarde=mysqli_num_rows($dbcon->query($today_inward));

				$where = ""; // initialize in case no additional conditions

				// Example of adding extra conditions dynamically
				if (!empty($_POST['branch_id'])) {
					$where .= " AND po.branch_id = " . intval($_POST['branch_id']);
				}

				$pooverduepending = "SELECT COUNT(trn.purchaseordertrn_id) as po_overdue_pending
					FROM `tbl_purchaseordertrn` as trn
					LEFT JOIN product_mst as pro ON pro.product_id=trn.product_id
					LEFT JOIN tbl_purchaseorder as po ON po.purchaseorder_id=trn.purchaseorder_id
					WHERE trn.used_status=0 
					AND trn.purchaseordertrn_status=0 
					AND trn.purchaseorder_id!=0 
					AND po_approval_status=1 
					AND po.po_type = 0"
					. $where;

				$po_overdue_pending = mysqli_fetch_assoc($dbcon->query($pooverduepending));


			/*$today_date = date('Y-m-d');

			$over_due_inword="select count(po_delivery_date_id) as overdue_in from `tbl_purchaseorder_delivery_date` as del 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id = del.purchaseordertrn_id
			left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
			where po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status=1 and trn.used_status=0 and del.delivery_date<'".$today_date."' and del.grn_status=0 and del.company_id=".$_SESSION['company_id'];

			$over_due_inworde_res=mysqli_fetch_assoc($dbcon->query($over_due_inword));

			$today_over_due_inword="select count(po_delivery_date_id) as overdue_in from `tbl_purchaseorder_delivery_date` as del 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id = del.purchaseordertrn_id
			left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
			where po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status=1 and trn.used_status=0 and del.delivery_date='".$today_date."' and del.grn_status=0 and del.company_id=".$_SESSION['company_id'];

			$today_over_due_inword_res=mysqli_fetch_assoc($dbcon->query($today_over_due_inword));

			$date = new DateTime($today_date);
			$date->modify('+7 day');
			$tomorrowDATE = $date->format('Y-m-d');
			$where="  and del.delivery_date >= '".$today_date."' AND del.delivery_date <= '".$tomorrowDATE."'";
			$over_due_7days_inword="select count(po_delivery_date_id) as overdue_in from `tbl_purchaseorder_delivery_date` as del 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id = del.purchaseordertrn_id
			left join tbl_purchaseorder as po on po.purchaseorder_id = trn.purchaseorder_id
			where po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status=1 and trn.used_status=0 ".$where." and del.grn_status=0 and del.company_id=".$_SESSION['company_id'];
			
			$over_due_7days_inword_res=mysqli_fetch_assoc($dbcon->query($over_due_7days_inword));*/
			
			/*if(empty($over_due_7days_inword_res['overdue_in'])){
				$over_due_7days_inword_res['overdue_in']=0;
			}
			if(empty($today_over_due_inword_res['overdue_in'])){
				$today_over_due_inword_res['overdue_in']=0;
			}
			if(empty($over_due_inworde_res['overdue_in'])){
				$over_due_inworde_res['overdue_in']=0;
			}
			
			$r["over_due_7days"]=$over_due_7days_inword_res['overdue_in'];
			$r["today_over_due_inword"]=$today_over_due_inword_res['overdue_in'];
			$r["over_due_inworde"]=$over_due_inworde_res['overdue_in'];*/

			if(empty($over_due_inworde)){
				$over_due_inworde=0;
			}
			if(empty($today_inwarde)){
				$today_inwarde=0;
			}
			if(empty($po_overdue_pending['po_overdue_pending'])){
				$po_overdue_pending['po_overdue_pending']=0;
			}
			
			$r["over_due_7days"]=$today_inwarde;
			$r["today_over_due_inword"]=$over_due_inworde;
			$r["over_due_inworde"]=$po_overdue_pending['po_overdue_pending'];

			echo json_encode($r);

		}
		else if(strtolower($POST['mode']) == "top_20_dealy_product") {
			//$date=get_sdate($POST['c_year']);
			$finacial_date = getFinacialyear_data($dbcon);
			$POST['end_date']=$finacial_date['financial_end_date'];
			$POST['start_date']=$finacial_date['financial_start_date'];
			//$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
			$query="select count(pro.delay_days) as purchase_total,p.product_name as pg_name from tbl_purchaseorder_delivery_date as pro
			left join tbl_purchaseordertrn as itrn on itrn.purchaseordertrn_id=pro.purchaseordertrn_id
			left join product_mst as p on p.product_id=itrn.product_id
			where pro.grn_status=1 and pro.po_delivery_date_status=0
			and DATE_FORMAT(pro.delivery_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) AND pro.company_id IN (0,".$_SESSION['company_id'].") group by itrn.product_id order by purchase_total,delay_days desc limit 0,20";

			//echo $query;exit;
			$result=$dbcon->query($query);

			$row1 = array();
			$i=0;$per=0;
			while($row=mysqli_fetch_assoc($result))
			{	
				$row1[$i]['label']=$row['pg_name'];
				$row1[$i]['y']=intval($row['purchase_total']);		
				$i++;
			}
			//$row1[0]['sql']=$query;	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_target_chart") {
			//$date=get_calender_sdate($POST['t_pro_year']);
			$finacial_date = getFinacialyear_data($dbcon);	
			$date['start_date']=$finacial_date['financial_start_date'];
			$date['end_date']=$finacial_date['financial_end_date'];


			$query="SELECT MAX(ABS(product_rate)) as max_rate,MIN(ABS(product_rate)) as min_rate,(MAX(ABS(product_rate))-MIN(ABS(product_rate))) as diff_rate,pmst.product_id,pmst.product_name FROM `tbl_potrancation` as po left join product_mst as pmst on pmst.product_id=po.product_id WHERE `potrancation_status`=0 group by po.product_id order by diff_rate DESC limit 0,20";

			$tar_counter = $dbcon->query($query);
			$row = array();
			$row1 = array();
			$re = array();
			$row2= array();
			$i=0;
			while($chart= mysqli_fetch_assoc($tar_counter))
			{	
    	//$row[$i]["lable"]=$chart['min_rate'];
				$row[$i]["label"]= $chart['product_name'];
				$row[$i]["y"]=intval($chart['max_rate']);

				$row1[$i]["label"]=$chart['product_name'];
				$row1[$i]["y"]=intval($chart['min_rate']);
				$current_rate=get_last_purchase($dbcon,$chart['product_id']);

				$row2[$i]["label"]=$chart['product_name'];
				$row2[$i]["y"]=intval($current_rate);

				$i++;
			}	

			$re[0]=$row1;
			$re[1]=$row;
			$re[2]=$row2;
			echo json_encode($re); 
		}


		function get_sdate($date)
		{
			$sdate['start_date']=date('01-04-'.$date);
			$sdate['end_date']=date('31-03-'.($date+1));
			return $sdate;	
		}

	?>