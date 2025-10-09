<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST['mode']);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "dynamic_chart") {
				//var_dump($_REQUEST);
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
			//	echo $query;
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
				//var_dump($row);	
				echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "getyear") {
			$date=get_sdate($POST['c_year']);
			$invoice_count="SELECT sum(quot.g_total) as itotal FROM `tbl_quotation` as quot 
inner join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
WHERE quot.quotation_status=0 and quot.revise_status=0 and inq.stage_prob=100 AND quot.quotation_date<='".date('Y-m-d',strtotime($date['end_date']))."' and quot.company_id=".$_SESSION['company_id'];
			$count_invoice=mysqli_fetch_assoc($dbcon->query($invoice_count));
			
			$invoice_paid="SELECT sum(quot.g_total) as ipaid_amount FROM `tbl_quotation` as quot 
inner join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
WHERE quot.quotation_status=0 and quot.revise_status=0 and inq.stage_prob=100 AND date_format(quot.quotation_date,'%Y-%m')='".date('Y-m')."' and quot.company_id=".$_SESSION['company_id'];
			$count_paid=mysqli_fetch_assoc($dbcon->query($invoice_paid));
			
			$lost_m_qry="SELECT sum(quot.g_total) as lost_m_rev FROM `tbl_quotation` as quot 
inner join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
WHERE quot.quotation_status=0 and quot.revise_status=0 and inq.stage_prob<=10 AND date_format(quot.quotation_date,'%Y-%m')='".date('Y-m')."' and quot.company_id=".$_SESSION['company_id'];
			$lost_m_rel=mysqli_fetch_assoc($dbcon->query($lost_m_qry));
			
			$count['total']=floatval($count_invoice['itotal']);
			$count['total_w']=ucwords(convert_number_to_words(floatval($count_invoice['itotal'])));
			$count['paid_amount']=floatval($count_paid['ipaid_amount']);
			$count['paid_amount_W']=ucwords(convert_number_to_words(floatval($count_paid['ipaid_amount'])));
			$count['lost_m_rev']=floatval($lost_m_rel['lost_m_rev']);
			$count['lost_m_rev_w']=ucwords(convert_number_to_words(floatval($lost_m_rel['lost_m_rev'])));
			echo json_encode($count);
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
			$date=get_sdate($POST['c_year']);
			$user_ids=check_user_chein($dbcon,$POST['d_user_id'],1);
			/* $qry1="SELECT SUM(product_amount) AS total,cat.category_name as name from tbl_tranction as tan inner join category_mst as cat on cat.categoryid=tan.categoryid inner join tbl_invoice as invoice on invoice.invoice_id=tan.invoice_id where invoice_date>='".$date['start_date']."' AND invoice_date<='".$date['end_date']."' and trancation_status=0 GROUP BY cat.categoryid"; */
			
			$query="select led,e.user_name from users as e
                                left join (select sum(i.g_total) as led,i.user_id from tbl_inquiry as i where i.inquiry_status=0 and i.opp_id=12 and DATE_FORMAT(i.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['d_start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['d_end_date']))."'AS DATE) group by i.user_id) as dem on dem.user_id=e.user_id
                                where e.active=0 and e.user_id in (".$user_ids.") group by e.user_id";
			
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
					where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.won_date) and inquiry_status=0 and u.opp_id=12 and company_id=".$_SESSION['company_id']." and u.won_date between '".date('Y-m-d',strtotime($POST['start_date']))."' and '".date('Y-m-d',strtotime($POST['end_date']))."' ".$whr.") as invoice
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
                    $end_date = date('Y-m-d');
                    $business_achieved = $opportunity_onhand = $pending_quotation = $lost_opportunity = $hot_leads = 0;
                    
                    $business_achieved = $dbcon->query("SELECT sum(quot.g_total) as business_achieved FROM `tbl_quotation` as quot 
                        inner join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id
                        WHERE quot.quotation_status=0 and quot.revise_status=0 and inq.stage_prob=100 
                        AND quot.quotation_date >= '".date('Y-m-d',strtotime($start_date))."'
                        AND quot.quotation_date <= '".date('Y-m-d',strtotime($end_date))."' 
                        and quot.company_id=".$_SESSION['company_id'])->fetch_object()->business_achieved;;
			
                        
                    $opportunity_onhand = $dbcon->query("SELECT count(inquiry_id) as opportunity_onhand FROM `tbl_inquiry` 
                        WHERE opp_id NOT IN(".WON.",".CLOSED_LOST.") AND inquiry_date >= '".$start_date."' AND inquiry_date <= '".$end_date."' 
                            ")->fetch_object()->opportunity_onhand;
                    
                    $lost_opportunity = $dbcon->query("SELECT count(inquiry_id) as lost_opportunity FROM `tbl_inquiry` 
                        WHERE `opp_id` = ".CLOSED_LOST." AND inquiry_date >= '".$start_date."' AND inquiry_date <= '".$end_date."' 
                            ")->fetch_object()->lost_opportunity;
                    
                    $hot_leads = $dbcon->query("SELECT count(inquiry_id) as hot_leads FROM `tbl_inquiry` 
                        WHERE `sales_stage_id` = ".HOT." AND inquiry_date >= '".$start_date."' AND inquiry_date <= '".$end_date."' 
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
    
    
	}
	/*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
function get_sdate($date)
{
	$sdate['start_date']=date('01-04-'.$date);
	$sdate['end_date']=date('31-03-'.($date+1));
	return $sdate;	
}

?>