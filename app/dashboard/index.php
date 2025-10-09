<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
error_reporting(E_ALL);
//include("../../config/session.php");
include("../../include/function_database_query.php");

include(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_functions.php");

$company_config = getCompanyConfiguration($dbcon);

$is_store_approval = @$company_config['store_approval'];
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
			 
			 $cdone="Select count(complaint_id) as total from tbl_complaint where complaint_status=0 and company_id=".$_SESSION['company_id']." and followup_status='4' ".$where." ";
			 $count_cdone=mysqli_fetch_assoc($dbcon->query($cdone));
			
			 $cndone="Select count(complaint_id) as n_total from tbl_complaint where complaint_status=0 and company_id=".$_SESSION['company_id']." and  followup_status='5' ".$where." ";
			 $count_cndone=mysqli_fetch_assoc($dbcon->query($cndone));
			
			 $cassign="Select count(complaint_id) as a_total from tbl_complaint where complaint_status=0 and company_id=".$_SESSION['company_id']." and  followup_status='2' ".$where." ";
			 $count_assign=mysqli_fetch_assoc($dbcon->query($cassign));
			 
			 $creassign="Select count(complaint_id) as re_total from tbl_complaint where complaint_status=0 and company_id=".$_SESSION['company_id']." and  followup_status='3' ".$where." ";
			 $count_reassign=mysqli_fetch_assoc($dbcon->query($creassign));
			 
			 $c_cnt_qry="Select count(complaint_id) as all_comp_cnt from tbl_complaint where complaint_status=0 ".$where." and company_id=".$_SESSION['company_id']." and followup_status in(1,2,3,4,5,6,7,8)";
			 $c_cnt_rel=mysqli_fetch_assoc($dbcon->query($c_cnt_qry));
			 
			 $cunassign="Select count(complaint_id) as una_total from tbl_complaint where complaint_status=0 and  followup_status='1' ".$where." ";
			 $count_unassign=mysqli_fetch_assoc($dbcon->query($cunassign));
			 
			 $c_emp_start="Select count(complaint_id) as emp_start from tbl_complaint where complaint_status=0 and company_id=".$_SESSION['company_id']." and  followup_status='7' ".$where." ";
			 $count_e_start=mysqli_fetch_assoc($dbcon->query($c_emp_start));
			 
			 $c_emp_ex="Select count(ex_id) as exp_count from tbl_expense_detail where expense_approve_status=0 and expense_status='0' ".$where." ";
			 $count_emp_ex=mysqli_fetch_assoc($dbcon->query($c_emp_ex));
			 
			 $c_new_spare="Select count(s_id) as spare_p_new from tbl_complain_spare_part where sp_sent_status='no' and company_id='$_SESSION[company_id]' ".$where1;
			 $count_new_spare=mysqli_fetch_assoc($dbcon->query($c_new_spare));
			 
			 $c_old_spare="Select count(tbl_complain_close_spare_part.s_id) as spare_p_old from tbl_complain_close_spare_part inner join tbl_complaint as comp on comp.complaint_id=tbl_complain_close_spare_part.sc_comp_id 
			 	 left join tbl_complain_spare_part as tct on tct.s_comp_id =comp.complaint_id
			 	where comp.complaint_status=0 and tct.s_inv_status = 1 and comp.company_id=".$_SESSION['company_id']." and s_return_status=0".$where1;
			 $count_old_spare=mysqli_fetch_assoc($dbcon->query($c_old_spare));
			 
			 $cregister="Select count(complaint_id) as r_total from tbl_complaint where complaint_status=0  and company_id=".$_SESSION['company_id']." and followup_status='1' ".$where." ";
			 $c_register=mysqli_fetch_assoc($dbcon->query($cregister));
			 
			// $cjobwork="Select count(jobwork_id) as pen_total from tbl_jobwork where job_close_status=0  and status='0' ";
			
			/* $cjobwork='select jo.*,(select COALESCE(sum(strn.product_qty),0) as tqty from tbl_grn as j 
				left join tbl_grn_trn as p on p.grn_id=j.grn_id 
				left join tbl_grn_sub_trn as strn on strn.grn_trn_id=p.grn_trn_id
				where strn.jobwork_id=jo.jobwork_id and j.grn_status=0 and strn.status=0 and j.ref_type=1 and p.grn_trn_status=0) as tqty from tbl_jobwork as jo 
				left join product_mst as pr on pr.product_id=jo.j_product_id 
				where jo.job_close_status="0" and jo.j_process_type!=1 and jo.status="0" and  jo.company_id='.$_SESSION['company_id'].' HAVING j_qty>tqty';
				$conew=$dbcon->query($cjobwork);
				$c_mrn_hh=mysqli_num_rows($conew); */
				
				$dask_job_query="select job.job_work_id from tbl_job_work_trn as job_trn 
					left join tbl_job_work as job on job.job_work_id=job_trn.job_work_id
					where job_trn.grn_complete_status=0 and job.job_work_status=0 and job.job_work_type=2 AND job_trn.is_reprocess = 0 and job.grn_complete_status=0 and job_trn.job_work_trn_status=0 and job_trn.company_id=".$_SESSION['company_id']." group by job_trn.product_id,job_trn.process_id,job_trn.branch_id,job_trn.product_version";
				$conew=$dbcon->query($dask_job_query);
				$c_mrn_hh=brp_mysqli_num_rows($conew);
				
			// $c_jobwork=mysqli_fetch_assoc();


				$reprocess_pending_jobwork_grn_qry="select job.job_work_id from tbl_job_work_trn as job_trn 
					left join tbl_job_work as job on job.job_work_id=job_trn.job_work_id
					where job_trn.grn_complete_status=0 and job.job_work_status=0 and job.job_work_type=2 and job.grn_complete_status=0 and job_trn.job_work_trn_status=0 AND job_trn.is_reprocess = 1 and job_trn.company_id=".$_SESSION['company_id'];
				$reprocess_pending_jobwork_result=$dbcon->query($reprocess_pending_jobwork_grn_qry);
				$reprocess_pending_jobwork_counter=brp_mysqli_num_rows($reprocess_pending_jobwork_result);
			
			
			$cjobwork111='select count(rp_id) as job_count from tbl_request_product as j 
					where job_card_status=1 and status not in (2,3) and company_id='.$_SESSION['company_id'];
			$cjobwork111=$dbcon->query($cjobwork111);
			$c_mrn_hh11=mysqli_num_rows($cjobwork111);
			$c_jobwork11=mysqli_fetch_assoc($cjobwork111);

			
			$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
			$where_db = check_branch('mrn', $branch_id);
			$where=" $where_db and mrn.company_id=".$_SESSION['company_id'];

			$query_deb="SELECT mrn.mrn_id,grn.grn_id,led.l_name,mtrn.rejected_qty,mrn.qc_no,pro.product_name,qc.qc_no,qc.qc_date,grn.grn_no,grn.grn_date,(select IFNULL(sum(product_qty),0) as qty  from tbl_debitnote_trn as chtrn where chtrn.debitnote_trn_status=0 and chtrn.grn_id=mrn.grn_no and mtrn.product_id=chtrn.product_id) as used_qty FROM tbl_mrn as mrn 
				left join tbl_mrn_trn as mtrn on mtrn.mrn_no=mrn.mrn_id
				left join product_mst as pro on pro.product_id=mtrn.product_id
				left join tbl_grn as grn on grn.grn_id=mrn.grn_no
				left join tbl_qc as qc on qc.qc_id=mrn.qc_no
				left join tbl_ledger as led on led.l_id=grn.vender_id
				where mrn.mrn_status=0 and mtrn.mrn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=1 $where having mtrn.rejected_qty > used_qty order by mrn.mrn_id";
		
			$conew_db=$dbcon->query($query_deb);
			$debit_note_pending=mysqli_num_rows($conew_db);
				
		
			$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
			$where_db = check_branch('grn', $branch_id);
			$where=" $where_db and grn.company_id=".$_SESSION['company_id'];		

			$query_pur="SELECT SQL_CALC_FOUND_ROWS grn.grn_id, gtrn.product_qty, grn.grn_no, grn.grn_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, grn.user_id, grn.gir_no, grn.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty, grn.branch_id, po.po_type FROM tbl_grn as grn left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id left join product_mst as pro on pro.product_id=gtrn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=grn.vender_id left join branch_mst as bms on bms.branch_id=grn.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.purchaseordertrn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and gtrn.purchase_status=0 and po.po_type=0 and grn.company_id=".$_SESSION['company_id']." ORDER BY grn.grn_id";
		
			$conew_pub=$dbcon->query($query_pur);
			$purchase_bill_pending=mysqli_num_rows($conew_pub);
			
			$pen_ser_bill = "SELECT SQL_CALC_FOUND_ROWS ser.service_id, strn.product_qty, ser.service_no, ser.service_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, ser.user_id, ser.service_no, ser.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and strn.product_id=chtrn.product_id) as used_qty, ser.branch_id, po.po_type FROM tbl_service_notes as ser left join tbl_service_notes_trn as strn on strn.service_id=ser.service_id left join product_mst as pro on pro.product_id=strn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=ser.vender_id left join branch_mst as bms on bms.branch_id=ser.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = strn.purchaseordertrn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where ( 1 AND ser.service_status=0 and strn.service_trn_status=0 and strn.purchase_status=0 and po.po_type=1 and ser.company_id =".$_SESSION['company_id'].")";
			$pe_ser = $dbcon->query($pen_ser_bill);
			$service_purchase_bill_pending = mysqli_num_rows($pe_ser);

			/*$pen_service_bill = "SELECT SQL_CALC_FOUND_ROWS grn.grn_id, gtrn.product_qty, grn.grn_no, grn.grn_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, grn.user_id, grn.gir_no, grn.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty, grn.branch_id, po.po_type FROM tbl_grn as grn left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id left join product_mst as pro on pro.product_id=gtrn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=grn.vender_id left join branch_mst as bms on bms.branch_id=grn.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.job_work_po_trn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where ( 1 AND grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type in (1,2) and gtrn.purchase_status=0 and po.po_type=2 and  grn.company_id=".$_SESSION['company_id'].") ORDER BY grn.grn_id desc";
			$pe_service = $dbcon->query($pen_service_bill);
			$service_purchase_bill_pending = mysqli_num_rows($pe_service);	*/
			
			
			$pen_job_bill = "SELECT SQL_CALC_FOUND_ROWS grn.grn_id, gtrn.product_qty, grn.grn_no, grn.grn_date, pro.product_name, tc.cat_name, led.l_name, bms.branch_name, grn.user_id, grn.gir_no, grn.invoice_no, (select IFNULL(sum(product_qty),0) as qty from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty, grn.branch_id, po.po_type FROM tbl_grn as grn left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id left join product_mst as pro on pro.product_id=gtrn.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_ledger as led on led.l_id=grn.vender_id left join branch_mst as bms on bms.branch_id=grn.branch_id left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = gtrn.job_work_po_trn_id left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id where ( 1 AND grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type in (1,2) and gtrn.purchase_status=0 and po.po_type=2 and  grn.company_id=".$_SESSION['company_id'].") ORDER BY grn.grn_id desc";
			$pe_job = $dbcon->query($pen_job_bill);
			$jobwork_purchase_bill_pending = mysqli_num_rows($pe_job);	
				
			 //purchase_bill_pending
			 
			 /* $popending="Select sum(product_qty) as po_pending from tbl_purchasetrntemp where purchaseordertrn_status=0";
			 $po_pending=mysqli_fetch_assoc($dbcon->query($popending));
			  */
			  
			/*$popending="select sum(product_qty) as pqty,GROUP_CONCAT(purchaseordertrn_id) as purchastrn_id from tbl_purchasetrntemp where purchaseordertrn_status=0 and po_trn_req_status=0";
				$rel_pen=mysqli_fetch_assoc($dbcon->query($popending));
		
			$query_ude="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id in (".$rel_pen['purchastrn_id'].")";
				
				$rel21s=mysqli_fetch_assoc($dbcon->query($query_ude));	
			$pending_qty=$rel_pen['pqty']-$rel21s['used_qty'];*/
			
		 	$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
			$where_db = check_branch('po', $branch_id);
			$where=" $where_db and po.company_id=".$_SESSION['company_id'];

			$popending2="select po.purchaseordertrn_id,po.mdate,pr.product_name,po.total,po.purchaseordertrn_status,po.cdate,po.user_id,po.po_ref_type,sum(po.product_qty) as pqty,po.po_ref_id,po.product_id,po.po_trn_req_status,GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id from tbl_purchasetrntemp as po 
			 left join product_mst as pr on pr.product_id=po.product_id
			 where po.purchaseordertrn_status = 0 and po_trn_req_status=0 $where group by po.product_id,po.po_trn_req_status";
			$pur_ds=$dbcon->query($popending2);
			$pending_qty=mysqli_num_rows($pur_ds);


			$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
			$where_db = check_branch('apo', $branch_id);
			$where=" $where_db and apo.company_id=".$_SESSION['company_id'];

			$quotationsql="SELECT SQL_CALC_FOUND_ROWS apo.approve_no, apo.approve_date, apo.approve_qty, po.indent_no, delivery_date, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, pmst.product_name, po.rp_id, apo.approve_indent_id FROM approve_indent as apo left join tbl_request_product as po on po.rp_id=apo.rp_id left join tbl_set_main_process as spro on spro.sp_id=po.sp_id left join product_mst as pmst on pmst.product_id=po.rp_pid left join unit_mst as unit on unit.unitid=apo.approve_unit where ( 1 AND apo.approve_indent_status=0 and quotation_requirement=1 and quotation_approve_status=0 $where) Group by apo.approve_indent_id ORDER BY apo.approve_indent_id desc";
			$quotation_res=$dbcon->query($quotationsql);
			$purchse_quotation_list=mysqli_num_rows($quotation_res);
			

			  /* $pendingjobworksql = "select SQL_CALC_FOUND_ROWS ap.*,sum(ap.p_qty) as ap_qty,sum(ap.pen_qty) as apen_qty,p.product_type,p.product_name,IFNULL(end_qty,0) as end_qty,IFNULL(strtt_qty,0) as strtt_qty, GROUP_CONCAT(ap.p_id ORDER BY `ap`.`p_id` ASC) as allocate_id from tbl_allocate_process as ap left join product_mst as p on p.product_id=ap.p_product_id left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0 group by pt_alloc_id) as apta1 on apta1.pt_alloc_id=ap.p_id where  ap.p_status IN (0,1)  and pr_process_type='2' group by ap.p_product_id,ap.process_id ORDER BY ap.p_id asc";   */
			
			 $pendingjobworksql = "SELECT SQL_CALC_FOUND_ROWS p.product_type, p.product_name, pro.process_name, sum(ap.p_qty) as ap_qty, sum(ap.pen_qty) as apen_qty, IFNULL(end_qty,0) as end_qty, IFNULL(strtt_qty,0) as strtt_qty, GROUP_CONCAT(ap.p_id ORDER BY `ap`.`p_id` ASC) as allocate_id, ap.* FROM tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id
				left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0) as apta1 on apta1.pt_alloc_id=ap.p_id 
				left join process_mst as pro on ap.process_id=pro.process_id 
				where ( 1 AND pr_process_type='2' and ap.p_status IN (0,1) ) Group by ap.p_product_id, ap.process_id ORDER BY ap.p_id asc LIMIT 0, 10"; 
			$pendingjobwork_res=$dbcon->query($pendingjobworksql);
			$pendingjobwork_list=mysqli_num_rows($pendingjobwork_res);

			$pendingjobwork_count = 0;
			while($rel=brp_mysqli_fetch_array($pendingjobwork_res))
			{
				$min_working_qty=0;
				$allocate_id = $rel['allocate_id'];

				$start_qty_data = "SELECT sum(pt_qty) as start_qty_valua FROM `tbl_allocate_process_trn` where p_status = 0 and pt_alloc_id IN (".$allocate_id.") ";
				$start_result=$dbcon->query($start_qty_data);
				$start_qty_result = brp_mysqli_fetch_assoc($start_result);
				$total_start_qty = $start_qty_result['start_qty_valua'];

				$finish_qty_data = "SELECT sum(pt_qty) as start_qty_valua FROM `tbl_allocate_process_trn` where p_status = 1 and pt_alloc_id IN (".$allocate_id.") ";
				$finish_result=$dbcon->query($finish_qty_data);
				$finish_qty_result = brp_mysqli_fetch_assoc($finish_result);
				$total_finsih_qty = $finish_qty_result['start_qty_valua'];

				$current_start_qty = $total_start_qty - $total_finsih_qty;

				$req_working_qty = $rel['apen_qty']-$current_start_qty;
				
				$process_id          = $rel['process_id'];
				$process_type        = $rel['pr_process_type'];
				$p_product_id 		 = $rel['p_product_id'];
				$p_status 			 = $rel['p_status'];
				$previous_process_id = $rel['previous_process_id'];
					
				//$min_working_qty = working_qty_avalable($dbcon, $process_id, $process_type, $p_product_id, $p_status, $previous_process_id);
				
				$min_working_qty = working_qty_avalable($dbcon, $process_id, $process_type, $p_product_id, $p_status, $previous_process_id);

					

				
					if($min_working_qty > 0){
						$pendingjobwork_count++;
						//$pendingjobwork_count=$pendingjobworksql;
					}
				
			} 
			
			$pendingjobwork_count=0;
			$job_penapproval_sql="SELECT GROUP_CONCAT(p_id) as pid FROM `tbl_allocate_process` as trn
				WHERE trn.pr_process_type = 2 and trn.p_status in (0,1) and trn.company_id=".$_SESSION['company_id']." group by p_product_id,process_id,branch_id,process_priority,product_version";
				$job_pen_resulr=$dbcon->query($job_penapproval_sql);
				
			 while($job_pen_approval=mysqli_fetch_assoc($job_pen_resulr)){
			 	$q = "SELECT IFNULL(sum(trn.product_base_qty),0) as used_qty FROM `tbl_job_work_sub_trn` as trn  
					left join tbl_job_work_trn as job_work_trn on job_work_trn.job_work_trn_id =  trn.job_work_trn_id
					where job_work_sub_trn_status = 0 and p_id IN (".$job_pen_approval['pid'].")  and job_work_trn.job_work_trn_status = 1";
				$job_trn=$dbcon->query($q);
				$job_trn_result = brp_mysqli_fetch_assoc($job_trn);
				$jobwork_working_qty = 0;
				$jobwork_working_qty = $job_trn_result['used_qty'];
				$qtp=production_start_count_using_p_id($dbcon,$job_pen_approval['pid']);
				if($qtp - $jobwork_working_qty > 0){
					if($qtp>0){
						$pendingjobwork_count++;
						//$pendingjobwork_count=$pendingjobwork_count." - ".$job_pen_approval['pid'];
					}
				}
			 }

			 $request_jobwork_count = 0;
			 $jobwrk_pending = "SELECT count(job_work_id) as jobwork_request_pending_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and request_status = 0";
			 $jobwrk_pending_result=mysqli_fetch_assoc($dbcon->query($jobwrk_pending));
			 $request_jobwork_count =  $jobwrk_pending_result['jobwork_request_pending_cnt'];
			//$pendingjobwork_count=$job_penapproval_sql;


			 $pending_jobowork_chalan_count  = 0;
			 $jobwrk_chalan = "SELECT count(job_work_id) as jobwork_release_chalan_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and release_status = 1 and chalan_status = 0 AND is_reprocess = 0";
			 $jobwrk_chalan_result=mysqli_fetch_assoc($dbcon->query($jobwrk_chalan));
			 $pending_jobowork_chalan_count =  $jobwrk_chalan_result['jobwork_release_chalan_cnt'];


			 $reprocess_pending_jobowork_chalan_count  = 0;
			 $reprocess_jobwrk_chalan = "SELECT count(job_work_id) as jobwork_release_chalan_cnt  FROM tbl_job_work where job_work_type = 2 and grn_complete_status = 0 and job_work_status = 0 and release_status = 1 and chalan_status = 0 AND is_reprocess = 1";
			 $reprocess_jobwrk_chalan_result=mysqli_fetch_assoc($dbcon->query($reprocess_jobwrk_chalan));
			 $reprocess_pending_jobowork_chalan_count =  $reprocess_jobwrk_chalan_result['jobwork_release_chalan_cnt'];
			
			 
			/* $pooverduepending="select count(purchaseorder_id) as po_overdue_pending from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub  where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty  from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 and so.purchaseorder_due_date < '$cur_date' ";*/
			
			/*trn.product_qty>(select IFNULL(sum(product_qty+tolerance),0) as qty  from tbl_grn_trn as chtrn
				where chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=trn.purchaseorder_id and trn.product_id=chtrn.product_id)*/

			$branch_id = ($_SESSION['user_type'] != '2') ? $_SESSION['branch_id'] : '';
			$where_db = check_branch('po', $branch_id);
			$where=" $where_db and po.company_id=".$_SESSION['company_id'];	

			$purchse_order_pending_approval_sql="SELECT COUNT(trn.purchaseorder_id) as purchse_order_pending_approval FROM `tbl_purchaseorder` as trn
				WHERE trn.po_approval_status = 0 and trn.status=0 and trn.company_id=".$_SESSION['company_id'];
			 $purchse_order_pending_approval=mysqli_fetch_assoc($dbcon->query($purchse_order_pending_approval_sql));
			 
			 $purchase_order_finance_aprroval_sql = "select count(po.purchaseorder_id) as po_finance_aprooval from tbl_purchaseorder as po 
			 WHERE po.po_approval_status = 3 and po.status=0 and po.company_id=".$_SESSION['company_id'];
			 
			 $purchase_order_finance_aprroval= mysqli_fetch_assoc($dbcon->query($purchase_order_finance_aprroval_sql));
			 
			 $pooverduepending="SELECT COUNT(trn.purchaseordertrn_id) as po_overdue_pending FROM `tbl_purchaseordertrn` as trn
				left join product_mst as pro on pro.product_id=trn.product_id
				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
				WHERE  trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1 and po.po_type = 0".$where;
			
             $po_overdue_pending=mysqli_fetch_assoc($dbcon->query($pooverduepending));
			 
              $service_notes_counter="SELECT COUNT(trn.purchaseordertrn_id) as service_notes_counter FROM `tbl_purchaseordertrn` as trn
				left join product_mst as pro on pro.product_id=trn.product_id
				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
				WHERE  trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1 and po.po_type = 1".$where;
			
             $service_notes=mysqli_fetch_assoc($dbcon->query($service_notes_counter));

             $po_short_pen = "SELECT SQL_CALC_FOUND_ROWS sht.log_id, sht.po_no, sht.product_id, tc.cat_name, sht.short_close_qty, sht.short_close_reason, sht.date, pro.product_name, bms.branch_name, user.user_name, sht.user_id, sht.aproove_status, sht.po_trn_id, sht.po_id, unit.unit_name FROM tbl_log_po_short_close as sht left join product_mst as pro on pro.product_id=sht.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_purchaseorder as po on po.purchaseorder_id=sht.po_id left join branch_mst as bms on bms.branch_id=sht.branch_id left join unit_mst as unit on unit.unitid=sht.unit_id left join users as user on user.user_id=sht.user_id where  sht.short_close_status=0 and aproove_status=0 and sht.company_id in (".$_SESSION['company_id'].") ORDER BY sht.log_id desc";
              $poshorpen=brp_mysqli_num_rows($dbcon->query($po_short_pen));
              //var_dump($poshorpen);exit;

              $po_short_dis = "SELECT SQL_CALC_FOUND_ROWS sht.log_id, sht.po_no, sht.product_id, tc.cat_name, sht.short_close_qty, sht.short_close_reason, sht.date, pro.product_name, bms.branch_name, user.user_name, sht.user_id, sht.aproove_status, sht.po_trn_id, sht.po_id, unit.unit_name FROM tbl_log_po_short_close as sht left join product_mst as pro on pro.product_id=sht.product_id left join tbl_category as tc on pro.product_category=tc.cat_id left join tbl_purchaseorder as po on po.purchaseorder_id=sht.po_id left join branch_mst as bms on bms.branch_id=sht.branch_id left join unit_mst as unit on unit.unitid=sht.unit_id left join users as user on user.user_id=sht.user_id where sht.short_close_status=0 and aproove_status=2 and sht.company_id in (".$_SESSION['company_id'].") ORDER BY sht.log_id desc ";
              $poshordiss=brp_mysqli_num_rows($dbcon->query($po_short_dis));

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

				where pod.po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and po.po_approval_status = 1 and trn.used_status=0 and po.po_type = 0 and delivery_date<'$today_date' and pod.grn_status=0 and pod.company_id=".$_SESSION['company_id']."  Group by pod.po_delivery_date_id ";
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

				where  pod.po_delivery_date_status=0 and po.po_approval_status = 1 and delivery_date='$today_date' and trn.used_status=0 and trn.purchaseordertrn_status=0 and po.po_type = 0 and pod.grn_status=0 and pod.company_id=".$_SESSION['company_id']." Group by pod.po_delivery_date_id ORDER BY pod.delivery_date desc ";
			 //echo $today_inward; exit;
			 $today_inwarde=mysqli_num_rows($dbcon->query($today_inward));
			 
			$inward_followup = "SELECT SQL_CALC_FOUND_ROWS pod.po_delivery_date_id, pod.delivery_date, pod.product_qty, po.purchaseorder_no, po.purchaseorder_date, bms.branch_name, pmst.product_name, tc.cat_name, unit.unit_name, po.purchaseorder_id, trn.purchaseordertrn_id, led.l_name, led.cust_mobile, (pod.product_qty-pod.used_qty) as pending_qty, follow.folloup_date, follow.remark 

				FROM tbl_purchaseorder_followup as follow 


				left join tbl_purchaseorder_delivery_date as pod on pod.po_delivery_date_id=follow.po_delivery_date_id 
				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=pod.purchaseordertrn_id 
				left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id 
				left join tbl_ledger as led on led.l_id=po.vender_id 
				left join branch_mst as bms on bms.branch_id=pod.branch_id 
				left join product_mst as pmst on pmst.product_id=trn.product_id 
				left join tbl_category as tc on pmst.product_category=tc.cat_id 
				left join unit_mst as unit on unit.unitid=trn.unit_id 

				where ( 1 AND pod.po_delivery_date_status=0 and trn.purchaseordertrn_status=0 and po.po_approval_status = 1 and trn.used_status=0 and po.po_type = 0 and pod.grn_status=0 and follow.followup_status=1 and follow.follow_date='$today_date' and pod.company_id=".$_SESSION['company_id'].") Group by pod.po_delivery_date_id ORDER BY pod.delivery_date desc";
			$inw_folloup=mysqli_num_rows($dbcon->query($inward_followup));
			
			 $totalinwardpending="select (po_count+job_count) as total_inward_pending from (select count(purchaseorder_id) as po_count from tbl_purchaseorder as so where status=0 and (select IFNULL(sum(product_qty),0) as qty from tbl_purchaseordertrn as sosub where purchaseordertrn_status=0 and sosub.purchaseorder_id=so.purchaseorder_id ) > (select IFNULL(sum(product_qty),0) as qty from tbl_grn as chall left join tbl_grn_trn as chtrn on chtrn.grn_id=chall.grn_id where grn_status=0 and chtrn.grn_trn_status=0 and chtrn.purchaseorder_id=so.purchaseorder_id) and so.po_approval_status=1 ) as t1,(select count(jobwork_id) as job_count from tbl_jobwork as jo where status=0 and job_close_status=0 ) as t2";
			$total_inward_pending=mysqli_fetch_assoc($dbcon->query($totalinwardpending));
			 
			$po_disapproved_qry="SELECT SQL_CALC_FOUND_ROWS purchaseorder_id, purchaseorder_no, l.l_name, city.city_name, bms.branch_name, purchaseorder_date, g_total, paid_amount, status, purchase_status, po.cdate, po.userid, po.po_type_status, po.po_req_status, po_approval_status FROM tbl_purchaseorder as po left join tbl_ledger as l on po.vender_id=l.l_id left join city_mst city on l.cityid=city.cityid left join branch_mst as bms on bms.branch_id=po.branch_id where ( 1 AND status = 0 and po_type_status=1 and po.company_id=".$_SESSION['company_id']." and po.po_approval_status in (2,4) ) ORDER BY po.purchaseorder_id desc";
			$po_disapproved=mysqli_num_rows($dbcon->query($po_disapproved_qry));
			//$poqcpending="Select count(grn_id) as po_qc_pending from tbl_grn where qc_status=0 and ref_type='2'";
			/*$poqcpending="SELECT COUNT(trn.grn_trn_id) as po_qc_pending FROM `tbl_grn_trn` as trn
				left join product_mst as pro on pro.product_id=trn.product_id
				left join tbl_grn as grn on grn.grn_id=trn.grn_id
				WHERE grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type='2' and trn.company_id=".$_SESSION['company_id'];*/

			$poqcpending = 	"SELECT COUNT(batch.batch_id) as po_qc_pending FROM tbl_batch_data as batch
				left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
				left join product_mst as pro on pro.product_id=trn.product_id
				left join tbl_grn as grn on grn.grn_id=trn.grn_id
				WHERE batch.status = 0 and batch.qc_status = 0 and grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type in(2,4) and trn.company_id=".$_SESSION['company_id'];

			$po_qc_pending=mysqli_fetch_assoc($dbcon->query($poqcpending));
			
			//$partsqcpending="Select count(grn_id) as parts_qc_pending from tbl_grn where qc_status=0 and ref_type='1'";
			
			/*$partsqcpending="SELECT COUNT(trn.grn_trn_id) as parts_qc_pending FROM `tbl_grn_trn` as trn
				left join product_mst as pro on pro.product_id=trn.product_id
				left join tbl_grn as grn on grn.grn_id=trn.grn_id
				WHERE grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type='1'";*/

			/*$partsqcpending="SELECT COUNT(batch.batch_id) as parts_qc_pending FROM tbl_batch_data as batch
				left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
				left join product_mst as pro on pro.product_id=trn.product_id
				left join tbl_grn as grn on grn.grn_id=trn.grn_id
				WHERE batch.status = 0 and batch.qc_status = 0 and grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type='1'";*/

				$partsqcpending="SELECT COUNT(batch.batch_id) as parts_qc_pending FROM tbl_batch_data as batch
				left join tbl_grn_trn as trn on trn.grn_trn_id = batch.grn_trn_id
				left join product_mst as pro on pro.product_id=trn.product_id
				left join tbl_grn as grn on grn.grn_id=trn.grn_id
				WHERE batch.status = 0 and batch.qc_status = 0 and grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0";


			$parts_qc_pending=mysqli_fetch_assoc($dbcon->query($partsqcpending));

			 $reprocessqcpending="SELECT COUNT(batch.batch_id) as reprocess_qc_pending FROM tbl_batch_data  as batch
				WHERE batch.status = 0 and reprocess_qc = 1 and batch.qc_status = 0 ".$pwhere.' '.$where_db." and batch.company_id=".$_SESSION['company_id'];


			$reprocess_qc_pending=mysqli_fetch_assoc($dbcon->query($reprocessqcpending));
			
			$fppending="Select count(qctrn_id) as fp_pending from tbl_qc_trn where qc_status=0";
			$fp_pending=mysqli_fetch_assoc($dbcon->query($fppending));
			
			$pending_debit_note="Select count(mrn_id) as c_pending_debit_note from tbl_mrn where mrn_status=0";
			$c_pending_debit_note=mysqli_fetch_assoc($dbcon->query($pending_debit_note));
			
			/* START JAYESH FOR GIR */
			$gir_counter="Select count(pro_gir_id) as gir_counter from pro_gir where gir_status=0";
			$gir_counter_result=mysqli_fetch_assoc($dbcon->query($gir_counter));
			/* END  JAYESH FOR GIR */
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
			$count['pending_job_work_count']=$pendingjobwork_count;
			$count['request_jobwork_count'] = $request_jobwork_count;
			$count['pending_jobowork_chalan_count'] = $pending_jobowork_chalan_count;
			$count['reprocess_pending_jobowork_chalan_count'] = $reprocess_pending_jobowork_chalan_count;
			$count['pending_job_card']=$c_mrn_hh;
			$count['reprocess_pending_jobwork_grn']=$reprocess_pending_jobwork_counter;
			$count['pending_job_card_new']=$c_jobwork11['job_count'];
			
			
			//$count['purchse_order_pending']=$po_pending['po_pending'];
			$count['purchse_order_pending']=$pending_qty;
			$count['purchse_quotation_list']=$purchse_quotation_list;
			$count['po_overdue_pending']=$po_overdue_pending['po_overdue_pending'];
			$count['overdue_inward']=$over_due_inworde;
			$count['today_inward']=$today_inwarde;
			$count['inward_followup']=$inw_folloup;
			$count['purchse_order_pending_approval']=$purchse_order_pending_approval['purchse_order_pending_approval'];
			$count['po_aprooval_finance']=$purchase_order_finance_aprroval['po_finance_aprooval'];
			$count['total_inward_pending']=$total_inward_pending['total_inward_pending'];

			$count['po_disapproved']	= $po_disapproved;
			/* START JAYESH  for gir counter*/
			$count['gir_counter']=$gir_counter_result['gir_counter'];
			/* END JAYESH  for gir counter*/	
			$count['service_notes_counter'] = $service_notes['service_notes_counter'];	
			
			$count['po_qc_pending']=$po_qc_pending['po_qc_pending'];
			$count['parts_qc_pending']=$parts_qc_pending['parts_qc_pending'];
			$count['reprocess_qc_pending']=$reprocess_qc_pending['reprocess_qc_pending'];
			$count['fp_pending']=$fp_pending['fp_pending'];
			$count['pending_debit_note']=$c_pending_debit_note['c_pending_debit_note'];
			$count['debit_note_pending']=$debit_note_pending;
			$count['purchase_bill_pending']=$purchase_bill_pending;
			$count['service_purchase_bill_pending']=$service_purchase_bill_pending;
			$count['jobwork_purchase_bill_pending']=$jobwork_purchase_bill_pending;
			$count['service_purchase_bill_pending'] = $service_purchase_bill_pending;
			$count['po_shortclose_approval']	= $poshorpen;
			$count['po_shortclose_disapproval']	= $poshordiss;
			//var_dump($count);
			echo json_encode($count);
		}
		
		else if(strtolower($POST['mode']) == "getemployee") {
			 
			 $date=date("Y-m-d");
			 $userid=$_SESSION['user_id'];
			 $usertype=$_SESSION['user_type'];
			 
			 if($usertype!='3'){
				 
			 $p=$dbcon->query("select l_id from tbl_ledger where l_status='0' and l_form='emp_form' and company_id=".$_SESSION['company_id']);
			 $emp_count=mysqli_num_rows($p);
			 
			 $q=$dbcon->query("select log_id from login_history where DATE(in_time)='$date' and attendance='yes' and company_id=".$_SESSION['company_id']." group by uid");
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
		$isWhere = array("followup_status = 0 and flp.company_id IN (0,$_SESSION[company_id]) and followup_id in(select max(followup_id) from tbl_followup where followup_status=0   group by start_lead_id) and flp.statusid=1 ".$where.check_user('flp'));
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
			//var_dump($POST);
			/*$_SESSION['company_id'] = $POST['company_id'];
			$_SESSION['company_name'] = $POST['company_name'];
			echo $POST['company_name'];*/

			
			if(LOGIN_SETTING=="1" && @$_SESSION['LOGGED_IN']!="" && @$_SESSION['user_type']=='1')
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
		//	'.get_tree_user($dbcon,$_SESSION["user_id"],$POST["user_id"]).'
			$str.='<table class="table">
						<tr> 
							<th colspan="2">
								<select class="form-control" name="crm_tree_user" id="crm_tree_user" onchange="crm_task_data_load();" >
									'.get_assign_users($dbcon, $POST["user_id"], " and user_type in(".$company_config['crm_user_type'].")").'
								</select>
							</th>
						</tr>
						<tr> 
							<th>
								<a href="'.CRM_ROOT.'inquiry_add'.'">Add Inquiry</a>
							</th>
							<th></th>
						</tr>';
						$query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
						$query_rs=$dbcon->query($query);
						while($row_p=mysqli_fetch_assoc($query_rs))
						{
							$quourl = CRM_ROOT.'quotation_list';
							if($row_p['mcd_id'] == '21'){
								$str .= '<tr>
											<th>
												<a  href="'.$quourl.'">PENDING QUOTATION APPOVAL</a>
											</th>
											<th>'.count_team_pending_quot_approval($dbcon,$POST["user_id"]).'</th>
										</tr>';
							}
							//Amish Soni Start 23-03-2021
                            $chkUrl = ($row_p['mcd_id'] == GENERAL_TASK_TYPE) ? 'general_task_list' : 'pending_task_list/'.$row_p['mcd_id'].'/'.$POST["user_id"];
							$utr = CRM_ROOT.$chkUrl;
                            //Amish Soni End 23-03-2021
							$str.='<tr>
								<th>
									<a  href="'.$utr.'">'.$row_p['mcd_name'].'</a>
								</th>
								<th>'.count_usr_pen_tsk($dbcon,$row_p['mcd_id'],$POST["user_id"]).'</th>
							</tr>';
							
							$cnt++;
						}
						$str.='<tr> 
							<th>
								<a  href="'.ROOT.'pending_sales_order_list">Pending P.O. Upload</a>
							</th>
							<th>'.count_pend_po_upload($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.CRM_ROOT.'disapprove_sales_order_list">DISAPPROVE SALES ORDER</a>
							</th>
							<th>'.count_dis_so_upload($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.'pending_so_approve_list">PENDING SALES ORDER APPROVE</a>
							</th>
							<th>'.count_pend_so_approve($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.CRM_ROOT.'order_acceptance_list">PENDING ORDER ACCEPT</a>
							</th>
							<th>'.count_pend_order_accept($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.CRM_ROOT.'sales_order_stock_allocation">SALES ORDER STOCK ALLOCATION</a>
							</th>
							<th>'.count_so_stock_allocation($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.'dispatch_list'.'">Pending Dispatch</a>
							</th>
							<th>'.count_pend_disp($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.'pending_appointment_list'.'">Upcoming Appointments</a>
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
								<a  href="'.CRM_ROOT.'inquiry_add'.'">Add Inquiry</a>
							</th>
							<th></th>
						</tr>';
						$query="select mcd_id,mcd_name from tbl_master_category_detail where mcd_status=0 and mcd_cat_id=10 order by priority ASC";
						$query_rs=$dbcon->query($query);
						while($row_p=mysqli_fetch_assoc($query_rs))
						{
							$quourl = CRM_ROOT.'quotation_list';
							if($row_p['mcd_id'] == '21'){
								$str .= '<tr>
											<th>
												<a  href="'.$quourl.'">PENDING QUOTATION APPOVAL</a>
											</th>
											<th>'.count_user_pending_quot_approval($dbcon,$POST["user_id"]).'</th>
										</tr>';
							}
                            //Amish Soni Start 23-03-2021
                            $chkUrl = ($row_p['mcd_id'] == GENERAL_TASK_TYPE) ? 'general_task_list' : 'pending_task_list_one/'.$row_p['mcd_id'].'/'.$POST["user_id"];
                            $utr = CRM_ROOT.$chkUrl;
                            //Amish Soni End 23-03-2021
							$str.='<tr>
								<th>
									<a  href="'.$utr.'">'.$row_p['mcd_name'].'</a>
								</th>
								<th>'.count_usr_pen_tsk1($dbcon,$row_p['mcd_id'],$POST["user_id"]).'</th>
							</tr>';
							
							$cnt++;
						}
						$str.='<tr> 
							<th>
								<a  href="'.ROOT.'pending_sales_order_list">Pending P.O. Upload</a>
							</th>
							<th>'.count_pend_po_upload($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.CRM_ROOT.'disapprove_sales_order_list">DISAPPROVE SALES ORDER</a>
							</th>
							<th>'.count_dis_so_upload($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.'pending_so_approve_list">PENDING SALES ORDER APPROVE</a>
							</th>
							<th>'.count_pend_so_approve($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.CRM_ROOT.'order_acceptance_list">PENDING ORDER ACCEPT</a>
							</th>
							<th>'.count_pend_order_accept($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.'dispatch_list'.'">Pending Dispatch</a>
							</th>
							<th>'.count_pend_disp($dbcon,$POST["user_id"]).'</th>
						</tr>
						<tr> 
							<th>
								<a  href="'.ROOT.'pending_appointment_list'.'">Upcoming Appointments</a>
							</th>
							<th>'.count_pend_appoint($dbcon,$POST["user_id"]).'</th>
						</tr>
					</table>';
			echo $str;
		}
                else if(strtolower($POST['mode']) == "get_usertype") {
			$get_usertype_details=$dbcon->query("select user_type,usertype_name,users.company_id from users
				left join tbl_usertype on users.user_type = tbl_usertype.usertype_id
				where user_mail = '".$_POST['username']."' and active=0 and users.company_id=".$_POST['login_company_id']." ");
			$user_count=mysqli_num_rows($get_usertype_details);
			if($user_count > 0)
			{
				$usertype_details = $get_usertype_details->fetch_assoc();
				
				$query2="select ip_add_login from tbl_company_configuration where company_id=".$usertype_details['company_id'];
				$rel2=mysqli_fetch_assoc($dbcon->query($query2));
				
				$row['user_count']=$user_count;
				$row['msg']=1;
				$row['usertype_id']=$usertype_details['user_type'];
				$row['response']=$usertype_details['usertype_name'];
				$row['ip_add_login']=$rel2['ip_add_login'];
			}
			else
			{
				$row['user_count']=$user_count;
				$row['response']='Invalid Detail';
				$row['msg']=0;
			}
			echo json_encode($row);
		}
    
    
     else if(strtolower($POST['mode']) == "get_resource_schedule_data") {
		 	header('Content-Type: application/json');
		 	$events = array();
		 	
		 	$resource_id = $POST['resource_id'];
		 	
		 	if($resource_id != '' )
		 	{
				$sql = "SELECT wra.*,`p`.`product_name`,`r`.`resource_name`, `rp`.`sp_id`, (SELECT po_req_no FROM tbl_set_main_process WHERE sp_id=`rp`.`sp_id`) as work_order_no, `proc`.`process_name`, `ressch`.`expected_start_date`, `ressch`.`expected_end_date`,p.image_name  FROM `tbl_work_order_resource_allocate` as wra 
			    LEFT JOIN product_mst as p ON `wra`.`product_id` = `p`.`product_id`
			    LEFT JOIN tbl_resource as r ON `wra`.`resource_id` = `r`.`resource_id`
			    LEFT JOIN tbl_request_product as rp ON `wra`.`request_id` = `rp`.`rp_id`
			    LEFT JOIN process_mst as proc ON `wra`.`process_id` = `proc`.`process_id`
			    LEFT JOIN tbl_resource_schedule as ressch ON `wra`.`request_id` = `ressch`.`rp_id` and `wra`.`process_id`=`ressch`.`process_id`
			    WHERE  `wra`.`resource_id`='".$resource_id."'  AND `wra`.`resourse_allocation_status`=0 AND `wra`.`company_id`='".$_SESSION['company_id']."' AND `wra`.`qty`!=0 AND `ressch`.`expected_start_date` != '' group BY `wra`.`resource_allocate_id` order by `ressch`.`expected_start_date` desc"; 
			}
			else
			{
				$sql = "SELECT wra.*,`p`.`product_name`,`r`.`resource_name`, `rp`.`sp_id`, (SELECT po_req_no FROM tbl_set_main_process WHERE sp_id=`rp`.`sp_id`) as work_order_no, `proc`.`process_name`, `ressch`.`expected_start_date`, `ressch`.`expected_end_date`,p.image_name  FROM `tbl_work_order_resource_allocate` as wra 
			    LEFT JOIN product_mst as p ON `wra`.`product_id` = `p`.`product_id`
			    LEFT JOIN tbl_resource as r ON `wra`.`resource_id` = `r`.`resource_id`
			    LEFT JOIN tbl_request_product as rp ON `wra`.`request_id` = `rp`.`rp_id`
			    LEFT JOIN process_mst as proc ON `wra`.`process_id` = `proc`.`process_id`
			    LEFT JOIN tbl_resource_schedule as ressch ON `wra`.`request_id` = `ressch`.`rp_id` and `wra`.`process_id`=`ressch`.`process_id`
			    WHERE  `wra`.`resourse_allocation_status`=0 AND `wra`.`company_id`='".$_SESSION['company_id']."' AND `wra`.`qty`!=0 AND `ressch`.`expected_start_date` != '' group BY `wra`.`resource_allocate_id` order by `ressch`.`expected_start_date` desc"; 
			}
		 	
		 //echo $sql;
			$result=$dbcon->query($sql);
		 	
		 //	$resource_id = $POST['resource_id'];
		
			$result=$dbcon->query($sql);

			$where = 'resource_id="'.$resource_id.'"'; 
			$resource_info = get_resource_info_by_id($dbcon, $where);
			
			
			//echo "<pre>"; print_r($resource_info);die;
			
			 $cnt=brp_mysqli_num_rows($result);
								  if($cnt>0){ 
								 
								  	  $i=1;	
								  	  $today = date('Y-m-d');
								  	  $total_time = 0;
                                      while($rel=brp_mysqli_fetch_assoc($result))
									  {
									  	// echo "tetst"; die;
									  //	$main_qty = $rel["p_qty"];
									  	//$pending_qty = $rel["qty"]-$rel["completed_qty"];

									  	//$remaing_time = $pending_qty*$rel["time_per_qty"];

									  //	$completed_hours = convertToHoursMins($remaing_time, '%02d Hours %02d Minutes');
									 	
										$exp_start_date = $rel["expected_start_date"];
										/*$exp_end_date = $rel["expected_end_date"];
										
										if($rel['image_name']!=null){
											
											$image_name1 = '<img src="'.ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;">';
										}else{
											$image_name1 = '';
										}*/
										
										$events[] = array('id' => $i, 'start' => $exp_start_date,  'title' => $rel["work_order_no"]);			 
                                 	
                                 	  	$i++;			   	
                                      }
			
			}
		 	
		 
  //  echo "<pre>" ; print_r($events); 
			echo json_encode($events);
			//exit;
		}
		
		
		else if(strtolower($POST['mode']) == "update_resource_schedule_data") {
		 
		$id = $_POST['id'];
		$start = $_POST['start'];
		$end = $_POST['end'];
		$start_date =  explode("T",$start);
		$start_time = explode("+",$start_date[1]);
		$time_update = $start_date[0].' '.$start_time[0];
		$res_sche_sql = "update tbl_resource_schedule set expected_start_date = '$time_update' where resource_schedule_id = '$id'";

		$res_sche_exec=$dbcon->query($res_sche_sql);
		if(mysqli_affected_rows($res_sche_exec) > 0 )
		{
			echo "1";
		}
		else
		{
			echo "0";
		}
    
	}
	else if(strtolower($POST['mode']) == "get_inhouse_process_list") {
		$str ='<table class="table" style="text-align:center">
								<tr>
									<th>#</th>
									<th style="white-space:nowrap;">Process Name</th>
									<th style="white-space:nowrap;">Total Pending</th>';

			if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0') { 
				$str .=	'<th style="white-space:nowrap;">Create Batch </th>';
									 }
									
									 if($is_store_approval){ 
										$str .='<th style="white-space:nowrap;">Store Request Pending</th>
										<!-- <th style="white-space:nowrap;">Store Release Pending</th> -->';
									 } 
									
			$str .='<th style="white-space:nowrap;">Pending Start</th>
									<th style="white-space:nowrap;">Pending Stop</th>
									<!-- <th style="white-space:nowrap;">Reprocess Qty</th> -->
									<th style="white-space:nowrap;">Reprocess Start</th>
									<th style="white-space:nowrap;">Reprocess End</th>
									<!--<th>Opening Qty</th>-->
								</tr>';
								
								
								$process_array = $bulkcheck =  [];
								$tr = 0; 
								$cnt=1;
								$sel_p1=$dbcon->query("select * from process_mst where process_status='0' 
									order by dashbord_priority ");
								while($row_p1=mysqli_fetch_assoc($sel_p1))
								{
									$process_array[] = 'dashboard-inhouse-'.str_replace(' ', '-', strtolower($row_p1['process_name'])); 
								}
								$bulkcheck = canCheckPermissionAccess($dbcon, $process_array);
								$sel_p=$dbcon->query("select * from process_mst where process_status='0' 
									order by dashbord_priority ");
								while($row_p=mysqli_fetch_assoc($sel_p))
								{

									
									 if(in_array($process_array[$tr],$bulkcheck)) { 
								$str .='<tr>
											<th>'. $cnt .'</th>
											<th>'. $row_p['process_name']. '</th>
											<th>
												<a href="'. ROOT.PRODUCTION_ROOT.'process_detail_list/'.$row_p['process_id'].'/1" class="link_dash">'. count_process_qty($dbcon,$row_p['process_id'],'1') .'</a>
											</th>';
											 if($company_config['batch_wise_stock'] == '1'  && $company_config['batch_process'] == '0') { 
												$str .='<th>
													<a href="'. ROOT.PRODUCTION_ROOT.'batch_create_list/'.$row_p['process_id'].'/1" class="link_dash">'.store_process_batch_wise_production_count($dbcon,$row_p['process_id'],1,1,1) .'</a>
												</th>';
											}
											
											 if($is_store_approval){ 
											$str .='<th>
													<a href="'.ROOT.PRODUCTION_ROOT.'store_request_detail_list/'.$row_p['process_id'].'/1" class="link_dash">'.store_process_wise_production_count($dbcon,$row_p['process_id'],1,1,1).'</a>
												</th>';
											

											$str .='<th>
													<a href="'.ROOT.PRODUCTION_ROOT.'working_process_detail_list/'.$row_p['process_id'].'/1" class="link_dash">'.process_wise_production_count($dbcon,$row_p['process_id'],1,1,1) .'</a>

												</th>';
											 }else{ 
											$str .='<th> 
													<a href="'. ROOT.PRODUCTION_ROOT.'working_process_detail_list/'.$row_p['process_id'].'/1" class="link_dash">'.process_wise_production_count($dbcon,$row_p['process_id'],1,1,0) . '</a>
													</th>';
												} 
										
										$str .='<th>
													<a href='.ROOT.PRODUCTION_ROOT.'working_process_detail_list/'.$row_p['process_id'].'/2" class="link_dash">'.process_wise_production_count($dbcon,$row_p['process_id'],1,2).'</a>

												</th>';

												
										$str .='<th><a href="'.ROOT.PRODUCTION_ROOT.'working_reprocess_detail_list/'.$row_p['process_id'].'/1"  class="link_dash">'. count_re_process_start_qty($dbcon,$row_p['process_id'],'1').'</a></th>

												<th><a href="'.ROOT.PRODUCTION_ROOT.'working_reprocess_detail_list/'.$row_p['process_id'].'/2"  class="link_dash">'.count_re_process_end_qty($dbcon,$row_p['process_id'],'1').'</a></th>

										</tr>';
									 } 
									
									$tr++;
									$cnt++;
								}
								
								
								
						$str .='</table>';

						echo $str;
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


function convertToHoursMins($time, $format = '%02d:%02d') {
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

// Get Expected Start and End Date Filter Option (Check the condition of start and end date)
function expected_start_end_date($expected_start_end_date){
	if($expected_start_end_date==null){
		$expected_date = '-';
	}else{
		$expected_date = date('d-M-Y H:i:s', strtotime($expected_start_end_date));
	}
	return $expected_date;
}





?>