<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
//include("../../config/session.php");
include("../../include/function_database_query.php");

include(COMMON_FUNCTION_PATH."common_functions.php");
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
			$date=get_sdate($POST['year_graph1']);	
			$status=$POST['status'];	
			//$year_graph1=get_sdate($POST['year_graph1']);
			
			$where='';
			if($status!='0')
			{
				$where.=' and followup_status='.$status;
			}
			$query="SELECT m.month,(select count(complaint_id) from tbl_complaint u 
			where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.complaint_date) ".$where."  and complaint_status=0 and u.company_id=".$_SESSION['company_id']." and u.complaint_date between '".date('Y-m-d',strtotime($date['start_date']))."' and '".date('Y-m-d',strtotime($date['end_date']))."' ) as complaint
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
				$row[$chart['month']][]=intval($chart['complaint']);
				$row[]= $chart['month'];
				$row1[$i]['device']=$chart['month'];
				$row1[$i]['geekbench']=$chart['complaint'];
				$i++;
			}		
				//var_dump($row);	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "dynamic_chart_emp") {
				//var_dump($_REQUEST);
			$date=get_sdate($POST['year_graph2']);	
			$status=$POST['status'];	
			//$year_graph1=get_sdate($POST['year_graph1']);
			
			$where='';
			if($status!='0')
			{
				$where.=' and followup_status='.$status;
			}
			$query1="select e.*,(select count(complaint_id) from tbl_complaint as u where 
			u.complaint_status=0 ".$where." and  u.company_id=".$_SESSION['company_id']." and u.complaint_date between '".date('Y-m-d',strtotime($date['start_date']))."' and '".date('Y-m-d',strtotime($date['end_date']))."' and u.emp_id=e.employee_id) as complaint from employee_mst as e ";
			$invoice_counter1=$dbcon->query($query1);
			//	echo $query;
			$row	= array();
			$i=0;
			$row1['count']=mysqli_num_rows($invoice_counter1);
			while($chart1=mysqli_fetch_assoc($invoice_counter1))
			{	

				$row1[$i]['device']=$chart1['employee_name'];
				$row1[$i]['geekbench']=$chart1['complaint'];
				$i++;
					//echo $chart1['employee_id'];
			}		
				//var_dump($row);	
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "getyear") {

			$date=get_sdate($POST['c_year']);
			$userid=$_SESSION['user_id'];
			$usertype=$_SESSION['user_type'];
			$cur_date=date("Y-m-d");

			$emp_id=getEmployeeIdUser($dbcon,$userid);

			$where="";
			$where1="";
			if($usertype=='3'){

				$where.="  and emp_id='$emp_id'";
				$where1.="  and s_emp_id='$emp_id'";
			}

			$cdone="Select count(complaint_id) as total from tbl_complaint where complaint_status=0  and followup_status='4' ".$where." ";
			$count_cdone=mysqli_fetch_assoc($dbcon->query($cdone));
			
			$cndone="Select count(complaint_id) as n_total from tbl_complaint where complaint_status=0 and  followup_status='5' ".$where." ";
			$count_cndone=mysqli_fetch_assoc($dbcon->query($cndone));
			
			$cassign="Select count(complaint_id) as a_total from tbl_complaint where complaint_status=0 and  followup_status='2' ".$where." ";
			$count_assign=mysqli_fetch_assoc($dbcon->query($cassign));

			$creassign="Select count(complaint_id) as re_total from tbl_complaint where complaint_status=0 and  followup_status='3' ".$where." ";
			$count_reassign=mysqli_fetch_assoc($dbcon->query($creassign));

			$c_cnt_qry="Select count(complaint_id) as all_comp_cnt from tbl_complaint where complaint_status=0 ".$where." and followup_status in(1,2,3,4,5,6,7,8)";
			$c_cnt_rel=mysqli_fetch_assoc($dbcon->query($c_cnt_qry));

			$cunassign="Select count(complaint_id) as una_total from tbl_complaint where complaint_status=0 and  followup_status='1' ".$where." ";
			$count_unassign=mysqli_fetch_assoc($dbcon->query($cunassign));

			$c_emp_start="Select count(complaint_id) as emp_start from tbl_complaint where complaint_status=0 and  followup_status='7' ".$where." ";
			$count_e_start=mysqli_fetch_assoc($dbcon->query($c_emp_start));

			$c_emp_ex="Select count(ex_id) as exp_count from tbl_expense_detail where expense_approve_status=0 and expense_status='0' ".$where." ";
			$count_emp_ex=mysqli_fetch_assoc($dbcon->query($c_emp_ex));

			$c_new_spare="Select count(s_id) as spare_p_new from tbl_complain_spare_part where sp_sent_status='no'".$where1;
			$count_new_spare=mysqli_fetch_assoc($dbcon->query($c_new_spare));

			$c_old_spare="Select count(s_id) as spare_p_old from tbl_complain_close_spare_part inner join tbl_complaint as comp on comp.complaint_id=tbl_complain_close_spare_part.sc_comp_id where complaint_status=0 and s_return_status=0".$where1;
			$count_old_spare=mysqli_fetch_assoc($dbcon->query($c_old_spare));

			$cregister="Select count(complaint_id) as r_total from tbl_complaint where complaint_status=0  and followup_status='1' ".$where." ";
			$c_register=mysqli_fetch_assoc($dbcon->query($cregister));

			// $cjobwork="Select count(jobwork_id) as pen_total from tbl_jobwork where job_close_status=0  and status='0' ";
			
			$cjobwork='select jo.*,(select COALESCE(sum(p.product_qty),0) as tqty from tbl_grn as j left join tbl_grn_trn as p on p.grn_id=j.grn_id 
			where j.purchaseorder_id=jo.jobwork_id and grn_status=0 and ref_type=1 and grn_trn_status=0) as tqty from tbl_jobwork as jo 
			left join product_mst as pr on pr.product_id=jo.j_product_id 
			where jo.job_close_status="0" and jo.j_process_type!=1 and jo.status="0" and  jo.company_id='.$_SESSION['company_id'].' HAVING j_qty>tqty';
			$conew=$dbcon->query($cjobwork);
			$c_mrn_hh=mysqli_num_rows($conew);
			// $c_jobwork=mysqli_fetch_assoc();
			
			
			$cjobwork111='select count(rp_id) as job_count from tbl_request_product as j 
			where job_card_status=1';
			$cjobwork111=$dbcon->query($cjobwork111);
			$c_mrn_hh11=mysqli_num_rows($cjobwork111);

			$c_jobwork11=mysqli_fetch_assoc($cjobwork111);
			
			$query_deb="SELECT mrn.mrn_id,grn.grn_id,led.l_name,mtrn.rejected_qty,mrn.qc_no,pro.product_name,qc.qc_no,qc.qc_date,grn.grn_no,grn.grn_date,(select IFNULL(sum(product_qty),0) as qty  from tbl_debitnote_trn as chtrn where chtrn.debitnote_trn_status=0 and chtrn.grn_id=mrn.grn_no and mtrn.product_id=chtrn.product_id) as used_qty FROM tbl_mrn as mrn 
			left join tbl_mrn_trn as mtrn on mtrn.mrn_no=mrn.mrn_id
			left join product_mst as pro on pro.product_id=mtrn.product_id
			left join tbl_grn as grn on grn.grn_id=mrn.grn_no
			left join tbl_qc as qc on qc.qc_id=mrn.qc_no
			left join tbl_ledger as led on led.l_id=grn.vender_id
			where mrn.mrn_status=0 and mtrn.mrn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=1 having mtrn.rejected_qty > used_qty order by mrn.mrn_id";

			$conew_db=$dbcon->query($query_deb);
			$debit_note_pending=mysqli_num_rows($conew_db);


			$query_pur="SELECT grn.*,gtrn.product_qty,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty FROM tbl_grn as grn 
			left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id
			where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=0 having gtrn.product_qty > used_qty order by grn.grn_id";

			$conew_pub=$dbcon->query($query_pur);
			$purchase_bill_pending=mysqli_num_rows($conew_pub);


				//purchase_bill_pending


			 /* $popending="Select sum(product_qty) as po_pending from tbl_purchasetrntemp where purchaseordertrn_status=0";
			 $po_pending=mysqli_fetch_assoc($dbcon->query($popending));
			  */

			/*$popending="select sum(product_qty) as pqty,GROUP_CONCAT(purchaseordertrn_id) as purchastrn_id from tbl_purchasetrntemp where purchaseordertrn_status=0 and po_trn_req_status=0";
				$rel_pen=mysqli_fetch_assoc($dbcon->query($popending));
		
			$query_ude="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id in (".$rel_pen['purchastrn_id'].")";
				
				$rel21s=mysqli_fetch_assoc($dbcon->query($query_ude));	
				$pending_qty=$rel_pen['pqty']-$rel21s['used_qty'];*/


				$popending2="select po.purchaseordertrn_id,po.mdate,pr.product_name,po.total,po.purchaseordertrn_status,po.cdate,po.user_id,po.po_ref_type,sum(po.product_qty) as pqty,po.po_ref_id,po.product_id,po.po_trn_req_status,GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id from tbl_purchasetrntemp as po 
				left join product_mst as pr on pr.product_id=po.product_id
				where po.purchaseordertrn_status = 0 and po_trn_req_status=0 group by po.product_id,po.po_trn_req_status";
				$pur_ds=$dbcon->query($popending2);
				$pending_qty=mysqli_num_rows($pur_ds);




				/* $pooverduepending="select count(purchaseorder_id) as po_overdue_pending from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub  where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty  from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 and so.purchaseorder_due_date < '$cur_date' ";*/

			/*trn.product_qty>(select IFNULL(sum(product_qty+tolerance),0) as qty  from tbl_grn_trn as chtrn
			where chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=trn.purchaseorder_id and trn.product_id=chtrn.product_id)*/
			$pooverduepending="SELECT COUNT(trn.purchaseordertrn_id) as po_overdue_pending FROM `tbl_purchaseordertrn` as trn
			left join product_mst as pro on pro.product_id=trn.product_id
			left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
			WHERE  trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1";
			$po_overdue_pending=mysqli_fetch_assoc($dbcon->query($pooverduepending));


			$totalinwardpending="select (po_count+job_count) as total_inward_pending from (select count(purchaseorder_id) as po_count from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 ) as t1,(select count(jobwork_id) as job_count from tbl_jobwork as jo where status=0 and job_close_status=0 ) as t2";
			$total_inward_pending=mysqli_fetch_assoc($dbcon->query($totalinwardpending));


			//$poqcpending="Select count(grn_id) as po_qc_pending from tbl_grn where qc_status=0 and ref_type='2'";
			$poqcpending="SELECT COUNT(trn.grn_trn_id) as po_qc_pending FROM `tbl_grn_trn` as trn
			left join product_mst as pro on pro.product_id=trn.product_id
			left join tbl_grn as grn on grn.grn_id=trn.grn_id
			WHERE grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type='2'";
			$po_qc_pending=mysqli_fetch_assoc($dbcon->query($poqcpending));
			
			//$partsqcpending="Select count(grn_id) as parts_qc_pending from tbl_grn where qc_status=0 and ref_type='1'";
			$partsqcpending="SELECT COUNT(trn.grn_trn_id) as parts_qc_pending FROM `tbl_grn_trn` as trn
			left join product_mst as pro on pro.product_id=trn.product_id
			left join tbl_grn as grn on grn.grn_id=trn.grn_id
			WHERE grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type='1'";
			$parts_qc_pending=mysqli_fetch_assoc($dbcon->query($partsqcpending));
			
			$fppending="Select count(qctrn_id) as fp_pending from tbl_qc_trn where qc_status=0";
			$fp_pending=mysqli_fetch_assoc($dbcon->query($fppending));
			
			$pending_debit_note="Select count(mrn_id) as c_pending_debit_note from tbl_mrn where mrn_status=0";
			$c_pending_debit_note=mysqli_fetch_assoc($dbcon->query($pending_debit_note));
			
			$count['c_register']= $c_register['r_total'];
			$count['cdone']= $count_cdone['total'];
			$count['cndone']=$count_cndone['n_total'];
			$count['cassign']=$count_assign['a_total']+$count_reassign['re_total'];
			$count['unassign']=$count_unassign['una_total'];
			$count['all_comp_cnt']=$c_cnt_rel['all_comp_cnt'];
			$count['expense']=$count_emp_ex['exp_count'];
			$count['emp_start']=$count_e_start['emp_start'];
			$count['new_spare']=$count_new_spare['spare_p_new'];
			$count['old_spare']=$count_old_spare['spare_p_old'];
			//$count['pending_job_card']=$c_jobwork['pen_total'];
			$count['pending_job_card']=$c_mrn_hh;
			$count['pending_job_card_new']=$c_jobwork11['job_count'];
			
			
			//$count['purchse_order_pending']=$po_pending['po_pending'];
			$count['purchse_order_pending']=$pending_qty;
			$count['po_overdue_pending']=$po_overdue_pending['po_overdue_pending'];
			$count['total_inward_pending']=$total_inward_pending['total_inward_pending'];
			
			
			$count['po_qc_pending']=$po_qc_pending['po_qc_pending'];
			$count['parts_qc_pending']=$parts_qc_pending['parts_qc_pending'];
			$count['fp_pending']=$fp_pending['fp_pending'];
			$count['pending_debit_note']=$c_pending_debit_note['c_pending_debit_note'];
			$count['debit_note_pending']=$debit_note_pending;
			$count['purchase_bill_pending']=$purchase_bill_pending;
			
			
			echo json_encode($count);
		}
		
		else if(strtolower($POST['mode']) == "getemployee") {

			$date=date("Y-m-d");
			$userid=$_SESSION['user_id'];
			$usertype=$_SESSION['user_type'];

			if($usertype!='3'){

				$p=$dbcon->query("select l_id from tbl_ledger where l_status='0' and l_form='emp_form'");
				$emp_count=mysqli_num_rows($p);

				$q=$dbcon->query("select log_id from login_history where DATE(in_time)='$date' and attendance='yes' group by uid");
				$present_count=mysqli_num_rows($q);


				$count['present']= $present_count;
				$count['absent']= $emp_count-$present_count;
				
				echo json_encode($count);


			}
		}
		
		else if(strtolower($POST['mode']) == "load_saleval") {
			$date=get_sdate($POST['c_year']);

			$invoice_count="Select SUM(g_total) as itotal,SUM(product_amount) as taxable_amt from tbl_invoice as invoice
			left join tbl_invoicetrn as invtrn on invtrn.invoice_id=invoice.invoice_id
			where  invoice_date>='".date('Y-m-d',strtotime($date['start_date']))."' AND invoice_date<='".date('Y-m-d',strtotime($date['end_date']))."' AND invoice_status=0 and invtrn.trancation_status=0 and company_id=".$_SESSION['company_id'];
			$count_invoice=mysqli_fetch_assoc($dbcon->query($invoice_count));
			
			$invoice_paid="Select SUM(paid_amount) as ipaid_amount from tbl_receipt where  payment_date>='".date('Y-m-d',strtotime($date['start_date']))."' AND payment_date<='".date('Y-m-d',strtotime($date['end_date']))."' AND status=0 and company_id=".$_SESSION['company_id'];
			$count_paid=mysqli_fetch_assoc($dbcon->query($invoice_paid));
			$count['total']= intval($count_invoice['itotal']);
			$count['taxable_amt']= intval($count_invoice['taxable_amt']);
			$count['paid_amount']=intval($count_paid['ipaid_amount']);
			echo json_encode($count);
		}
		
		else if(strtolower($POST['mode']) == "load_purchaseval") {
			$date=get_sdate($POST['c_year']);

			$invoice_count="Select SUM(g_total) as itotal,SUM(product_amount) as taxable_amt,SUM(product_amount) as taxable_amt from tbl_pono as po 
			left join tbl_potrancation as potrn on potrn.po_id=po.po_id 
			where  po_date>='".date('Y-m-d',strtotime($date['start_date']))."' AND po_date<='".date('Y-m-d',strtotime($date['end_date']))."' AND po.status=0 and potrn.potrancation_status=0 and company_id=".$_SESSION['company_id'];
			$count_invoice=mysqli_fetch_assoc($dbcon->query($invoice_count));
			
			$invoice_paid="Select SUM(paid_amount) as ipaid_amount from tbl_purchasereceipt where  payment_date>='".date('Y-m-d',strtotime($date['start_date']))."' AND payment_date<='".date('Y-m-d',strtotime($date['end_date']))."' AND status=0 and company_id=".$_SESSION['company_id'];
			$count_paid=mysqli_fetch_assoc($dbcon->query($invoice_paid));
			$count['total']= intval($count_invoice['itotal']);
			$count['taxable_amt']= intval($count_invoice['taxable_amt']);
			$count['paid_amount']=intval($count_paid['ipaid_amount']);
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
		else if(strtolower($POST['mode']) == "fetch_followup_status") {
			$s_date=explode(' - ',$POST['date']);
			
			$where.=" and followup_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND followup_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			
			$appData = array();
			$i=1;
			$aColumns = array('flp.followup_id', 'lead.lead_no','flp.cdate', 'followup_status', 'sts.status_name','inq.inquiry_no','quo.quotation_no','type.type_name','cust.company_name','flp.followup_date','flp.inquiry_id','flp.start_lead_id','flp.quotation_id');
			$sIndexColumn = "followup_id";
			$isWhere = array("followup_status = 0 and followup_id in(select max(followup_id) from tbl_followup where followup_status=0   group by start_lead_id) and flp.statusid=1 ".$where.check_user('flp'));
			$sTable = "tbl_followup as flp";			
			$isJOIN = array('left join status_mst as sts on sts.statusid=flp.statusid','left join tbl_lead as lead on lead.lead_id=flp.start_lead_id','left join tbl_customer cust on lead.cust_id=cust.cust_id','left join tbl_inquiry as inq on inq.inquiry_id=flp.inquiry_id','left join tbl_quotation as quo on quo.quotation_id=flp.quotation_id','left join type_mst as type on type.typeid=flp.typeid');
			$hOrder = "flp.start_lead_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['lead_no'];
				$row_data[] = $row['inquiry_no'];
				$row_data[] = $row['quotation_no'];
				$row_data[] = $row['company_name'];
				$row_data[] = date('d, M y',strtotime($row['followup_date']));
				$row_data[] = $row['type_name'];
				$row_data[] = $row['status_name'];
				if(empty($row['quotation_id']) AND (empty($row['inquiry_id']))){
					$row_data[] = '<a class="btn btn-xs btn-primary" title="Add Lead Follow-Up" data-toggle="tooltip" data-placement="top" href="'.ROOT.'add_lead_followup/'.$row['start_lead_id'].'"><i class="fa fa-plus"></i></a> ';
				}else if(empty($row['quotation_id'])){
					$row_data[] = ' <a class="btn btn-xs btn-primary" title="Add Inquiry Follow-Up" data-toggle="tooltip" data-placement="top" href="'. ROOT.'add_inq_followup/'.$row['inquiry_id'].'"><i class="fa fa-plus"></i></a>';
				}
				else{
					$row_data[] = '  <a class="btn btn-xs btn-primary" title="Add Quotation Follow-Up" data-toggle="tooltip" data-placement="top" href="'.ROOT.'add_quotation_followup/'.$row['quotation_id'].'"><i class="fa fa-plus"></i></a>';

				}

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "pass_session") {
			/*$_SESSION['company_id'] = $POST['company_id'];
			$_SESSION['company_name'] = $POST['company_name'];
			echo $POST['company_name'];*/
			
			if(LOGIN_SETTING=="1" && $_SESSION['LOGGED_IN'] && $_SESSION['user_type']=='1')
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
		}
		else if(strtolower($POST['mode']) == "crm_dashbord_data_load") {
			$str="";
			$str.='<table class="table">
			<tr> 
			<th colspan="2">
			<select class="form-control" name="crm_tree_user" id="crm_tree_user" onchange="crm_task_data_load();" >
			'.get_tree_user($dbcon,$_SESSION["user_id"],$POST["user_id"]).'
			</select>
			</th>
			</tr>
			<tr> 
			<th>
			<a href="'.ROOT.'inquiry_add'.'">Add Inquiry</a>
			</th>
			<th></th>
			</tr>';
			$query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
			$query_rs=$dbcon->query($query);
			while($row_p=mysqli_fetch_assoc($query_rs))
			{
				$utr=ROOT.'pending_task_list/'.$row_p['mcd_id'].'/'.$POST["user_id"];
				$str.='<tr>
				<th>
				<a target="_blank" href="'.$utr.'">'.$row_p['mcd_name'].'</a>
				</th>
				<th>'.count_usr_pen_tsk($dbcon,$row_p['mcd_id'],$POST["user_id"]).'</th>
				</tr>';

				$cnt++;
			}
			$str.='<tr> 
			<th>
			<a target="_blank" href="'.ROOT.'order_confirm_list/pen_po'.'">Pending P.O. Upload</a>
			</th>
			<th>'.count_pend_po_upload($dbcon,$POST["user_id"]).'</th>
			</tr>
			<tr> 
			<th>
			<a target="_blank" href="'.ROOT.'dispatch_list'.'">Pending Dispatch</a>
			</th>
			<th>'.count_pend_disp($dbcon,$POST["user_id"]).'</th>
			</tr>
			<tr> 
			<th>
			<a target="_blank" href="'.ROOT.'pending_appointment_list'.'">Upcoming Appointments</a>
			</th>
			<th>'.count_pend_appoint($dbcon,$POST["user_id"]).'</th>
			</tr>
			</table>';
			echo $str;
		}
		else if(strtolower($POST['mode']) == "crm_dashbord_data_load1") {
			$str="";
			$str.='<table class="table">
			<tr> 
			<th colspan="2">
			<select class="form-control" name="crm_tree_user1" id="crm_tree_user1" onchange="crm_task_data_load1();" >
			'.get_tree_user($dbcon,$_SESSION["user_id"],$POST["user_id"]).'
			</select>
			</th>
			</tr>
			<tr> 
			<th>
			<a target="_blank" href="'.ROOT.'inquiry_add'.'">Add Inquiry</a>
			</th>
			<th></th>
			</tr>';
			$query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
			$query_rs=$dbcon->query($query);
			while($row_p=mysqli_fetch_assoc($query_rs))
			{
				$utr=ROOT.'pending_task_list_one/'.$row_p['mcd_id'].'/'.$POST["user_id"];
				$str.='<tr>
				<th>
				<a target="_blank" href="'.$utr.'">'.$row_p['mcd_name'].'</a>
				</th>
				<th>'.count_usr_pen_tsk1($dbcon,$row_p['mcd_id'],$POST["user_id"]).'</th>
				</tr>';

				$cnt++;
			}
			$str.='<tr> 
			<th>
			<a target="_blank" href="'.ROOT.'order_confirm_list/pen_po'.'">Pending P.O. Upload</a>
			</th>
			<th>'.count_pend_po_upload($dbcon,$POST["user_id"]).'</th>
			</tr>
			<tr> 
			<th>
			<a target="_blank" href="'.ROOT.'dispatch_list'.'">Pending Dispatch</a>
			</th>
			<th>'.count_pend_disp($dbcon,$POST["user_id"]).'</th>
			</tr>
			<tr> 
			<th>
			<a target="_blank" href="'.ROOT.'pending_appointment_list'.'">Upcoming Appointments</a>
			</th>
			<th>'.count_pend_appoint($dbcon,$POST["user_id"]).'</th>
			</tr>
			</table>';
			echo $str;
		}
		else if(strtolower($POST['mode']) == "task_add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($_POST['branch_id']) && $_POST['branch_id']) ? $_POST['branch_id'] : $_SESSION['branch_id'];

        	$show_user_ids = $POST['assign_user_ids'].','.$_SESSION['user_id'];

			$info['show_user_ids'] = $show_user_ids;
			$info['task_type_id'] = 14;
			$info['gt_id'] = $_POST['gt_id'];
			$info['task_rel_id'] = 1;
			$info['task_name'] = $POST['task_name'];
			$info['task_remark'] = $_POST['task_remark'];
			$info['assign_user_ids'] = $_POST['assign_user_ids'];
			$info['task_priority_id'] = 1;
			$info['create_date'] = date('Y-m-d H:i:s');
			$info['cdate'] = date("Y-m-d H:i:s");
			$info['task_due_date'] = date('Y-m-d H:i:s');
			$info['task_alert_id'] = 2;
			$info['email_template_id'] = 0;

			$alert_date = date("Y-m-d H:i:s");
			$gap_mints = get_alert_mintes($dbcon, 2);
			$filt_alert_date = date("Y-m-d H:i:s", strtotime($alert_date . "-" . $gap_mints . " minutes"));
			$info['alert_date_time'] = date('Y-m-d H:i:s', strtotime($filt_alert_date));


			$info['entry_type'] = 1;
			$info['user_id'] = $_SESSION['user_id'];
			$info['company_id'] = $_SESSION['company_id'];

			$ins_task_id = add_record('tbl_task', $info, $dbcon, $branch_id);
			if($ins_task_id){
				$arr['msg'] = "1";
			}else{
				$arr['msg'] = "0";
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "notes_add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($_POST['branch_id']) && $_POST['branch_id']) ? $_POST['branch_id'] : $_SESSION['branch_id'];

			$info['description'] = $_POST['description'];
			if($_POST['notes_id']=='note_add'){
				$info['notes_date'] = date("Y-m-d h:i:s");
				$info['user_id'] = $_SESSION['user_id'];
				$info['company_id'] = $_SESSION['company_id'];
				$info['branch_id'] = $branch_id;

				$ins_task_id = add_record('director_working_notes', $info, $dbcon, $branch_id);
				if($ins_task_id){
					$arr['msg'] = $ins_task_id;
				}else{
					$arr['msg'] = "0";
				}
			} else{
				$ins_task_id = update_record('director_working_notes', $info, "notes_id = ".$_POST['notes_id'] ,$dbcon, $branch_id);
				if($ins_task_id){
					$arr['msg'] = $_POST['notes_id'];
				}else{
					$arr['msg'] = "0";
				}
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "load_notes") {
			$html = '';
			if(!empty($_POST['start_date'])){
				$today = date("Y-m-d 00:00:00",strtotime($_POST['start_date']));
				$end = date("Y-m-d 23:59:59",strtotime($_POST['start_date']));
			}else{
				$today = date("Y-m-d 00:00:00");
				$end = date("Y-m-d 23:59:59");
			}
			$sql = $dbcon->query("SELECT * FROM director_working_notes WHERE notes_status!=2 AND notes_date >= '".$today."' AND notes_date <= '".$end."'");
			$cnt = mysqli_num_rows($sql);
			while($row = mysqli_fetch_assoc($sql)){ 
				$notes_id = $row['notes_id'];
				$notes_status = $row['notes_status'];
				if($notes_status==1){
					$cls = 'style = "color: #30ad11;"';
					$clss = 'readonly';
				}else{
					$cls = "";
					$clss = '';
				}
				$html .= '<div class="webflow-style-input" >';
				if($notes_status!=3){
				$html .= '<input class="" '.$cls.' type="text" name="description" id="description'.$notes_id.'" value="'.$row['description'].'" onkeyup="add_notes(\'description'.$notes_id.'\',\'notes_id'.$notes_id.'\')" '.$clss.'>';
				}else{
					$html .= '<strike>'.$row['description'].'</strike>';
				}
				$html .= '<input type="hidden" name="notes_id" id="notes_id'.$notes_id.'" value="'.$notes_id.'">
				<input type="hidden" name="mode" id="mode" value="notes_add">
				<button type="submit" onClick="opendatemodel(\'notes_id'.$notes_id.'\',\''.$notes_id.'\',\''.$notes_status.'\')"><i class="fa fa-circle"></i></button>
				</div>';
			}
			$pr=5-$cnt;
			for($j=0; $j<$pr; $j++) {
				$html .= '<div class="webflow-style-input" >
				<input class="" type="text" name="description" id="description'.$j.'" placeholder="Write Your Note" onkeyup="add_notes(\'description'.$j.'\',\'notes_id'.$j.'\')">
				<input type="hidden" name="notes_id" id="notes_id'.$j.'" value="note_add">
				<input type="hidden" name="mode" id="mode" value="notes_add">
				<button type="submit" onClick="opendatemodel(\'notes_id'.$j.'\',\'\',\'\')"><i class="fa fa-circle"></i></button>
				</div>';
			}
			$html .= '<input type="hidden" id="count" value="'.$j.'">';

			echo $html;
		}else if(strtolower($POST['mode']) == "assign_date") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($_POST['branch_id']) && $_POST['branch_id']) ? $_POST['branch_id'] : $_SESSION['branch_id'];

			$notesql = $dbcon->query("SELECT description FROM director_working_notes WHERE notes_id = ".$_POST['notes_id']);
			$rowsql = mysqli_fetch_assoc($notesql);

			$notes_date = date("Y-m-d h:i:s",strtotime($_POST['notes_date']));
			$info['notes_date'] = $notes_date;
			$info['notes_status'] = $_POST['status'];
			if($_POST['status']==3){
				$info['description'] = "<del>".$rowsql['description']."</del>";
			}
			$ins_task_id = update_record('director_working_notes', $info, "notes_id = ".$_POST['notes_id'] ,$dbcon, $branch_id);
			if($ins_task_id){
				$arr['msg'] = $_POST['notes_id'];
			}else{
				$arr['msg'] = "0";
			}
			echo json_encode($arr);
		}else if(strtolower($POST['mode']) == "getdata") {
			$poamtsql = "SELECT COUNT(trn.purchaseorder_id) as purchse_order_pending_approval, SUM(trn.g_total) as po_pending_amount FROM `tbl_purchaseorder` as trn
				WHERE trn.po_approval_status = 0 and trn.status=0 and trn.revise_status = 0 and trn.company_id=".$_SESSION['company_id'];
			$pomtrow = mysqli_fetch_assoc($dbcon->query($poamtsql));

			$purchase_order_finance_aprroval_sql = "SELECT COUNT(po.purchaseorder_id) as po_finance_aprooval, SUM(po.g_total) as po_finance_amount FROM tbl_purchaseorder as po WHERE po.po_approval_status = 3 and po.status=0 and trn.revise_status = 0 and po.company_id=".$_SESSION['company_id'];
			$purchase_order_finance_aprroval= mysqli_fetch_assoc($dbcon->query($purchase_order_finance_aprroval_sql));

			$sales_order_aprroval_sql = "SELECT COUNT(po.sales_order_id) as so_aprooval, SUM(po.g_total) as so_pending_amount FROM tbl_sales_order as po WHERE po.sales_order_status = 0 and po.approve_status=0 and po.order_accept_status=0 and po.company_id=".$_SESSION['company_id'];
			$sales_order_aprroval= mysqli_fetch_assoc($dbcon->query($sales_order_aprroval_sql));

			$order_acceptance_aprroval_sql = "SELECT COUNT(po.sales_order_id) as order_accept_aprooval, SUM(po.g_total) as order_accept_pending_amount FROM tbl_sales_order as po WHERE po.sales_order_status = 0 and po.approve_status=3 and po.order_accept_status=0 and po.company_id=".$_SESSION['company_id'];
			$order_acceptance_aprroval= mysqli_fetch_assoc($dbcon->query($order_acceptance_aprroval_sql));

			$quotation_aprroval_sql = "SELECT count(quot.quotation_id) as total_pending_appro, SUM(quot.g_total) as quotation_pending_amount FROM `tbl_quotation` as quot WHERE quot.approve_status != 1 and quot.quotation_status=0 and quot.revise_status = 0 and quot.company_id=".$_SESSION['company_id'];
			$quotation_aprroval= mysqli_fetch_assoc($dbcon->query($quotation_aprroval_sql));

			$indent_aprroval_sql = "SELECT count(rp.rp_id) as pending_indent, SUM(used_qty) as used_qty, SUM(rp.shortclose_qty) as shortclose_qty, SUM(rp.rp_po_qty) as rp_po_qty from tbl_request_product as rp left join (select round(IFNULL(sum(req.approve_qty),0),4) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0  group by req.rp_id) as rereq on rereq.rp_id=rp.rp_id where rp.indent_status=1 and rp.jobwork_type = 0 and rp.company_id=".$_SESSION['company_id'];
			$indent_aprroval= mysqli_fetch_assoc($dbcon->query($indent_aprroval_sql));
			$pending_indent_amt = round($indent_aprroval['rp_po_qty'],4)-$indent_aprroval['used_qty']-$indent_aprroval['shortclose_qty'];

			$invoice_aprroval_sql = "SELECT count(invoice_id) as invoice_count, SUM(g_total) as invoice_amount FROM `tbl_invoice` where invoice_status= 0 AND approve_status = 0 AND company_id = ".$_SESSION['company_id'];
			$invoice_aprroval= mysqli_fetch_assoc($dbcon->query($invoice_aprroval_sql));
			
			$count['purchse_order_pending_approval']=($pomtrow['purchse_order_pending_approval']) ? $pomtrow['purchse_order_pending_approval'] : 0;
			$count['po_pending_amount']=($pomtrow['po_pending_amount']) ? $pomtrow['po_pending_amount'] : 0;
			$count['po_finance_aprooval']=($purchase_order_finance_aprroval['po_finance_aprooval']) ? $purchase_order_finance_aprroval['po_finance_aprooval'] : 0;
			$count['po_finance_amount']=($purchase_order_finance_aprroval['po_finance_amount']) ? $purchase_order_finance_aprroval['po_finance_amount'] : 0;
			$count['so_aprooval']=($sales_order_aprroval['so_aprooval']) ? $sales_order_aprroval['so_aprooval'] : 0;
			$count['so_pending_amount']=($sales_order_aprroval['so_pending_amount']) ? $sales_order_aprroval['so_pending_amount'] : 0;
			$count['order_accept_aprooval']=($order_acceptance_aprroval['order_accept_aprooval']) ? $order_acceptance_aprroval['order_accept_aprooval'] : 0;
			$count['order_accept_pending_amount']=($order_acceptance_aprroval['order_accept_pending_amount']) ? $order_acceptance_aprroval['order_accept_pending_amount'] : 0;
			$count['quotation_pending_count']=($quotation_aprroval['total_pending_appro']) ? $quotation_aprroval['total_pending_appro'] : 0;
			$count['quotation_pending_amount']=($quotation_aprroval['quotation_pending_amount']) ? $quotation_aprroval['quotation_pending_amount'] : 0;
			$count['pending_indent']=($indent_aprroval['pending_indent']) ? $indent_aprroval['pending_indent'] : 0;
			$count['pending_indent_amt']=($pending_indent_amt) ? $pending_indent_amt : 0;
			$count['invoice_count']=($invoice_aprroval['invoice_count']) ? $invoice_aprroval['invoice_count'] : 0;
			$count['invoice_amount']=($invoice_aprroval['invoice_amount']) ? $invoice_aprroval['invoice_amount'] : 0;
			
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