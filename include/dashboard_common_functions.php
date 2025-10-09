<?php
/*
 * Added By : Dimple Panchal
 * to get count of advance payment of Invoice
 */

$dates = get_financial_year();
extract($dates);
$start_date = date('Y-m-d', strtotime($start_date));
$end_date = date('Y-m-d', strtotime($end_date));
function count_invoice_unadjusted($dbcon){
    $ledger_qry = 'SELECT count(distinct(ledger.l_id)) as count 
                FROM tbl_excess as ex  
                LEFT JOIN tbl_ledger ledger ON ex.cust_id = ledger.l_id
                WHERE ledger.l_status = 0 and ledger.l_group IN ('.SUNDRY_DEBTORS.')';
    $count = $dbcon->query($ledger_qry)->fetch_object()->count;
    
    $excess_qry = "select rep.receipt_id,rep.receipt_date as ref_date,rep.receipt_no as ref_no,excess_id as ref_id,excess_amount as ref_amount,rep.payment_mode_id,
            (select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and payment_type=2 and inv.excess_id=trn.excess_id) as pay_amount,inv.cdate 
            FROM tbl_excess as inv 
            left join tbl_receipt as rep on rep.receipt_id=inv.receipt_id 
            where inv.status=0 and excess_type=1 AND inv.excess_amount>(select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and payment_type=2 and inv.excess_id=trn.excess_id)";
    return $count;
}

function count_so_invoice_pending($dbcon){
    $qry = "SELECT SQL_CALC_FOUND_ROWS so_trn.sales_ordertrn_id, so.sales_order_no, so.sales_order_date, led.company_name, pro.product_name, so_trn.product_qty, so_trn.product_id, so_trn.unit_id, so_trn.with_out_stock_invoice, led.l_name FROM tbl_sales_ordertrn as so_trn left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id left join tbl_ledger as led on led.l_id=so.cust_id left join product_mst as pro on pro.product_id=so_trn.product_id where ( 1 AND so_trn.sales_ordertrn_status = 0 and so_trn.invoice_status=0 and so.approve_status=3 and so.company_id IN (0,".$_SESSION['company_id'].") and so.invoice_status=0) ORDER BY so_trn.sales_ordertrn_id desc ";

    $count = $dbcon->query($qry);

    $cnt = brp_mysqli_num_rows($count);

    return $cnt;
}

// to get count of advance payment of Purchase
function count_purchase_unadjusted($dbcon){
    $ledger_qry = 'SELECT count(distinct(ledger.l_id)) as count 
                FROM tbl_excess as ex  
                LEFT JOIN tbl_ledger ledger ON ex.cust_id = ledger.l_id
                WHERE ledger.l_status = 0 and ledger.l_group IN ('.SUNDRY_CREDITORS.') AND ex.company_id = '.$_SESSION['company_id'];
    $count = $dbcon->query($ledger_qry)->fetch_object()->count;
    return $count;
}

function count_pending_order_invoice($dbcon){
    $inv_qry = 'SELECT count(quot.quotation_id) as count
        FROM tbl_quotation as quot 
        left join tbl_customer as cust on cust.cust_id=quot.cust_id 
        left join tbl_inquiry as inq on inq.inquiry_id=quot.inquiry_id 
        left join users as usr on usr.user_id=inq.user_id where ( 1 AND quot.quotation_status = 0 and quot.revise_status=0 and quot.approve_status=1 and quot.payment_approve_status=1 and quot.inv_done_status=0 and quot.quotation_date >= "'.$start_date.'" AND quot.quotation_date <= "'.$end_date.'") 
        ';
    $count = $dbcon->query($inv_qry)->fetch_object()->count;
    return ($count) ? $count : '0';
}
function count_pending_spare_invoice($dbcon){
    $inv_qry = 'SELECT count(comp.complaint_id) as count  
        FROM tbl_complaint as comp 
        left join tbl_complain_spare_part as spare on spare.s_comp_id=comp.complaint_id 
        left join tbl_internal_chalan as cln on cln.complaint_id=comp.complaint_id 
        left join tbl_ledger as l on comp.cust_id=l.l_id 
        left join users as usr on usr.user_id=comp.user_id where ( 1 AND comp.complaint_status = 0 and spare.s_paid_status="paid" and spare.s_inv_status=0 and cln.status = "receive" ) 
        Group by spare.s_comp_id
        ';
    $count = $dbcon->query($inv_qry)->fetch_object()->count;
    return ($count) ? $count : '0';
}
function count_pending_service_charge_invoice($dbcon){
    $inv_qry = 'SELECT count(comp.complaint_id) as count
        FROM tbl_complaint as comp 
        left join tbl_complaint_trn as trn on trn.complaint_id=comp.complaint_id 
        left join tbl_ledger as l on comp.cust_id=l.l_id 
        left join users as usr on usr.user_id=comp.user_id where ( 1 AND comp.complaint_status=0 and comp.followup_status=4 and trn.inv_done_status=0 and trn.comp_pro_sts=2 and trn.complaint_trn_status=0 and comp.company_id in (0,1) ) 
        Group by trn.complaint_id
        ';
    $count = $dbcon->query($inv_qry)->fetch_object()->count;
    return ($count) ? $count : '0';
}
function count_pending_foc_spare_invoice($dbcon){
    $inv_qry = 'SELECT count(comp.complaint_id) as count 
        FROM tbl_complaint as comp 
        left join tbl_complain_spare_part as spare on spare.s_comp_id=comp.complaint_id 
        left join tbl_ledger as l on comp.cust_id=l.l_id 
        left join users as usr on usr.user_id=comp.user_id where ( 1 AND comp.complaint_status = 0 and spare.s_paid_status="free" and spare.s_inv_status=0 and comp.company_id in (0,1) ) 
        Group by spare.s_comp_id
        ';
    $count = $dbcon->query($inv_qry)->fetch_object()->count;
    return ($count) ? $count : '0';
}

function count_pending_invoice_approval($dbcon){
    $inv_qry = 'SELECT count(invoice_id) as count FROM `tbl_invoice` where invoice_status= 0 and approve_status= 0 and company_id='.$_SESSION['company_id'].' ORDER BY `invoice_id` DESC';
    $count = $dbcon->query($inv_qry)->fetch_object()->count;
    return ($count) ? $count : '0';
}
function dispatch_pending($dbcon,$dispatch_status){
    $dispatch_pending = 'select count(invoice_id) as cnt from tbl_invoice where invoice_status=0 and approve_status=1 and company_id='.$_SESSION['company_id'].' and  dispatch_status='.$dispatch_status;
    $count = $dbcon->query($dispatch_pending)->fetch_object()->cnt;
    return ($count) ? $count : '0';
}

/* Start Cronjob Code By Umair Start
*  Date: 24-05-2021
*/
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

function purchase_quotation_list_count($dbcon){

      $where="  and apo.company_id=".$_SESSION['company_id'];
      $quotationsql="SELECT SQL_CALC_FOUND_ROWS apo.approve_no, apo.approve_date, apo.approve_qty, po.indent_no, delivery_date, po.indent_date, po.rp_po_qty, unit.unit_name, spro.po_req_no, pmst.product_name, po.rp_id, apo.approve_indent_id FROM approve_indent as apo left join tbl_request_product as po on po.rp_id=apo.rp_id left join tbl_set_main_process as spro on spro.sp_id=po.sp_id left join product_mst as pmst on pmst.product_id=po.rp_pid left join unit_mst as unit on unit.unitid=apo.approve_unit where ( 1 AND apo.approve_indent_status=0 and quotation_requirement=1 and quotation_approve_status=0 $where) Group by apo.approve_indent_id ORDER BY apo.approve_indent_id desc";
      $quotation_res=$dbcon->query($quotationsql);
      $purchse_quotation_list=brp_mysqli_num_rows($quotation_res);

      return $purchse_quotation_list;
}
function purchse_order_pending_count($dbcon){
      $where=" and po.company_id=".$_SESSION['company_id'];
      $popending2="select po.purchaseordertrn_id,po.mdate,pr.product_name,po.total,po.purchaseordertrn_status,po.cdate,po.user_id,po.po_ref_type,sum(po.product_qty) as pqty,po.po_ref_id,po.product_id,po.po_trn_req_status,GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id from tbl_purchasetrntemp as po 
       left join product_mst as pr on pr.product_id=po.product_id
       where po.purchaseordertrn_status = 0 and po_trn_req_status=0 $where group by po.product_id,po.po_trn_req_status";
      $pur_ds=$dbcon->query($popending2);
      $pending_qty=brp_mysqli_num_rows($pur_ds);
     
      return $pending_qty;
}
function purchse_order_pending_approval_count($dbcon){

      $where=" and po.company_id=".$_SESSION['company_id']; 
      $purchse_order_pending_approval_sql="SELECT COUNT(trn.purchaseorder_id) as purchse_order_pending_approval FROM `tbl_purchaseorder` as trn
        WHERE trn.po_approval_status = 0 and trn.status=0 and trn.company_id=".$_SESSION['company_id'];
       $purchse_order_pending_approval=brp_mysqli_fetch_assoc($dbcon->query($purchse_order_pending_approval_sql));
     
      return $purchse_order_pending_approval['purchse_order_pending_approval'];
}
function purchse_overdue_pending_count($dbcon){
     $where=" and trn.company_id=".$_SESSION['company_id']; 
     $pooverduepending="SELECT COUNT(trn.purchaseordertrn_id) as po_overdue_pending FROM `tbl_purchaseordertrn` as trn
        left join product_mst as pro on pro.product_id=trn.product_id
        left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
        WHERE  trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1".$where;
       $po_overdue_pending=brp_mysqli_fetch_assoc($dbcon->query($pooverduepending));

      return $po_overdue_pending['po_overdue_pending'];
}
function purchase_bill_pending_count($dbcon){
     $where=" and grn.company_id=".$_SESSION['company_id'];   

      $query_pur="SELECT grn.*,gtrn.product_qty,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty FROM tbl_grn as grn 
        left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id
        where grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=0 $where having gtrn.product_qty > used_qty order by grn.grn_id";
    
      $conew_pub=$dbcon->query($query_pur);
      $purchase_bill_pending=brp_mysqli_num_rows($conew_pub);

      return $purchase_bill_pending;
}
function debit_note_pending_count($dbcon){
      $where=" and mrn.company_id=".$_SESSION['company_id'];

      $query_deb="SELECT mrn.mrn_id,grn.grn_id,led.l_name,mtrn.rejected_qty,mrn.qc_no,pro.product_name,qc.qc_no,qc.qc_date,grn.grn_no,grn.grn_date,(select IFNULL(sum(product_qty),0) as qty  from tbl_debitnote_trn as chtrn where chtrn.debitnote_trn_status=0 and chtrn.grn_id=mrn.grn_no and mtrn.product_id=chtrn.product_id) as used_qty FROM tbl_mrn as mrn 
        left join tbl_mrn_trn as mtrn on mtrn.mrn_no=mrn.mrn_id
        left join product_mst as pro on pro.product_id=mtrn.product_id
        left join tbl_grn as grn on grn.grn_id=mrn.grn_no
        left join tbl_qc as qc on qc.qc_id=mrn.qc_no
        left join tbl_ledger as led on led.l_id=grn.vender_id
        where mrn.mrn_status=0 and mtrn.mrn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=1 $where having mtrn.rejected_qty > used_qty order by mrn.mrn_id";
    
      $conew_db=$dbcon->query($query_deb);
      $debit_note_pending=brp_mysqli_num_rows($conew_db);
     

      return $debit_note_pending;
}
function pending_job_card_new_count($dbcon){
      $cjobwork111='select count(rp_id) as job_count from tbl_request_product as j 
          where job_card_status=1';
      $cjobwork111=$dbcon->query($cjobwork111);
      $c_mrn_hh11=brp_mysqli_num_rows($cjobwork111);
      $c_jobwork11=brp_mysqli_fetch_assoc($cjobwork111);

      return $c_jobwork11['job_count'];
}
function pending_job_work_count($dbcon){
      $pendingjobworksql = "SELECT SQL_CALC_FOUND_ROWS p.product_type, p.product_name, pro.process_name, sum(ap.p_qty) as ap_qty, sum(ap.pen_qty) as apen_qty, IFNULL(end_qty,0) as end_qty, IFNULL(strtt_qty,0) as strtt_qty, GROUP_CONCAT(ap.p_id ORDER BY `ap`.`p_id` ASC) as allocate_id, ap.* FROM tbl_allocate_process as ap 
        left join product_mst as p on p.product_id=ap.p_product_id 
        left join (select sum(pt_qty) as end_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=1 group by pt_alloc_id) as apta on apta.pt_alloc_id=ap.p_id
        left join (select sum(pt_qty) as strtt_qty,pt_alloc_id from tbl_allocate_process_trn where p_status=0) as apta1 on apta1.pt_alloc_id=ap.p_id 
        left join process_mst as pro on ap.process_id=pro.process_id 
        where ( 1 AND pr_process_type='2' and ap.p_status IN (0,1) ) Group by ap.p_product_id, ap.process_id ORDER BY ap.p_id asc LIMIT 0, 10"; 
      $pendingjobwork_res=$dbcon->query($pendingjobworksql);
      $pendingjobwork_list=brp_mysqli_num_rows($pendingjobwork_res);

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
        $p_product_id      = $rel['p_product_id'];
        $p_status        = $rel['p_status'];
        $previous_process_id = $rel['previous_process_id'];
          
        //$min_working_qty = working_qty_avalable($dbcon, $process_id, $process_type, $p_product_id, $p_status, $previous_process_id);
        
        $min_working_qty = working_qty_avalable($dbcon, $process_id, $process_type, $p_product_id, $p_status, $previous_process_id);
        

        if($min_working_qty > 0){
          $pendingjobwork_count++;
          //$pendingjobwork_count=$pendingjobworksql;
        }
      }

      return $pendingjobwork_count;
}
function pending_job_card_count($dbcon){

      $cjobwork='select jo.*,(select COALESCE(sum(strn.product_qty),0) as tqty from tbl_grn as j 
        left join tbl_grn_trn as p on p.grn_id=j.grn_id 
        left join tbl_grn_sub_trn as strn on strn.grn_trn_id=p.grn_trn_id
        where strn.jobwork_id=jo.jobwork_id and j.grn_status=0 and strn.status=0 and j.ref_type=1 and p.grn_trn_status=0) as tqty from tbl_jobwork as jo 
        left join product_mst as pr on pr.product_id=jo.j_product_id 
        where jo.job_close_status="0" and jo.j_process_type!=1 and jo.status="0" and  jo.company_id='.$_SESSION['company_id'].' HAVING j_qty>tqty';
        $conew=$dbcon->query($cjobwork);
        $c_mrn_hh=brp_mysqli_num_rows($conew);

      return $c_mrn_hh;
}
function purchase_qc_pending_count($dbcon){
  $poqcpending="SELECT COUNT(trn.grn_trn_id) as po_qc_pending FROM `tbl_grn_trn` as trn
        left join product_mst as pro on pro.product_id=trn.product_id
        left join tbl_grn as grn on grn.grn_id=trn.grn_id
        WHERE grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type='2'";
  $po_qc_pending=brp_mysqli_fetch_assoc($dbcon->query($poqcpending));

  return $po_qc_pending['po_qc_pending'];
}
function parts_qc_pending_count($dbcon){
    $partsqcpending="SELECT COUNT(trn.grn_trn_id) as parts_qc_pending FROM `tbl_grn_trn` as trn
        left join product_mst as pro on pro.product_id=trn.product_id
        left join tbl_grn as grn on grn.grn_id=trn.grn_id
        WHERE grn.grn_status=0 and grn.qc_status=0 and trn.grn_trn_status=0 and trn.product_qc=0 and grn.ref_type='1'";
    $parts_qc_pending=brp_mysqli_fetch_assoc($dbcon->query($partsqcpending));

    return $parts_qc_pending['parts_qc_pending'];
}
function bussiness_registered_count($dbcon){
  $cregister="Select count(complaint_id) as r_total from tbl_complaint where complaint_status=0  and followup_status='1' ".$where." ";
  $c_register=brp_mysqli_fetch_assoc($dbcon->query($cregister));

  return $c_register['r_total'];
}
function bussiness_assign_count($dbcon){
  $cassign="Select count(complaint_id) as a_total from tbl_complaint where complaint_status=0 and  followup_status='2' ".$where." ";
  $count_assign=brp_mysqli_fetch_assoc($dbcon->query($cassign));
   
  $creassign="Select count(complaint_id) as re_total from tbl_complaint where complaint_status=0 and  followup_status='3' ".$where." ";
  $count_reassign=brp_mysqli_fetch_assoc($dbcon->query($creassign));

  return $count_assign['a_total']+$count_reassign['re_total'];
}
function bussiness_e_start_count($dbcon){
  $c_emp_start="Select count(complaint_id) as emp_start from tbl_complaint where complaint_status=0 and  followup_status='7' ".$where." ";
  $count_e_start=brp_mysqli_fetch_assoc($dbcon->query($c_emp_start));
  return $count_e_start['emp_start'];
}
function bussiness_e_notstart_count($dbcon){
  $cassign="Select count(complaint_id) as a_total from tbl_complaint where complaint_status=0 and  followup_status='2' ".$where." ";
  $count_assign=brp_mysqli_fetch_assoc($dbcon->query($cassign));
   
  $creassign="Select count(complaint_id) as re_total from tbl_complaint where complaint_status=0 and  followup_status='3' ".$where." ";
  $count_reassign=brp_mysqli_fetch_assoc($dbcon->query($creassign));

  return $count_assign['a_total']+$count_reassign['re_total'];
}
function bussiness_count($dbcon){
  $cdone="Select count(complaint_id) as total from tbl_complaint where complaint_status=0  and followup_status='4' ".$where." ";
  $count_cdone=brp_mysqli_fetch_assoc($dbcon->query($cdone));

  return $count_cdone['total'];
}
function turnover_count($dbcon){
  $cndone="Select count(complaint_id) as n_total from tbl_complaint where complaint_status=0 and  followup_status='5' ".$where." ";
  $count_cndone=brp_mysqli_fetch_assoc($dbcon->query($cndone));

  return $count_cndone['n_total'];
}
function all_comp_cnt_count($dbcon){
  $c_cnt_qry="Select count(complaint_id) as all_comp_cnt from tbl_complaint where complaint_status=0 ".$where." and followup_status in(1,2,3,4,5,6,7,8)";
  $c_cnt_rel=brp_mysqli_fetch_assoc($dbcon->query($c_cnt_qry));

  return $c_cnt_rel['all_comp_cnt'];
}
function e_present_count($dbcon){
   $q=$dbcon->query("select log_id from login_history where DATE(in_time)='$date' and attendance='yes' group by uid");
   $present_count=brp_mysqli_num_rows($q);

   return $present_count;
}
function e_absent_count($dbcon){
  $p=$dbcon->query("select l_id from tbl_ledger where l_status='0' and l_form='emp_form'");
  $emp_count=brp_mysqli_num_rows($p);

  $q=$dbcon->query("select log_id from login_history where DATE(in_time)='$date' and attendance='yes' group by uid");
  $present_count=brp_mysqli_num_rows($q);

  return $emp_count-$present_count;
}
function exp_approval_count($dbcon){
  $c_emp_ex="Select count(ex_id) as exp_count from tbl_expense_detail where expense_approve_status=0 and expense_status='0' ".$where." ";
  $count_emp_ex=brp_mysqli_fetch_assoc($dbcon->query($c_emp_ex));
  return $count_emp_ex['exp_count'];
}
function new_spare_count($dbcon){
  $c_new_spare="Select count(s_id) as spare_p_new from tbl_complain_spare_part where sp_sent_status='no'".$where1;
  $count_new_spare=brp_mysqli_fetch_assoc($dbcon->query($c_new_spare));

  return $count_new_spare['spare_p_new'];
}
function old_spare_count($dbcon){
  $c_old_spare="Select count(s_id) as spare_p_old from tbl_complain_close_spare_part inner join tbl_complaint as comp on comp.complaint_id=tbl_complain_close_spare_part.sc_comp_id where complaint_status=0 and s_return_status=0".$where1;

  $count_old_spare=brp_mysqli_fetch_assoc($dbcon->query($c_old_spare));

  return $count_old_spare['spare_p_old'];
}
function count_pend_so_approve_count($dbcon, $user_id){
  $where='';
  if($user_id) {
    $where.=' and user_id='.$user_id;
  }
        $inv_qry = 'SELECT count(sales_order_id) as count FROM `tbl_sales_order` 
                where sales_order_status= 0 and approve_status != 3 '.$where.' and company_id = '.$_SESSION['company_id'].' 
                ORDER BY `sales_order_id` DESC';
        $count = $dbcon->query($inv_qry)->fetch_object()->count;
        return ($count) ? $count : '0';
}


function today_inquiry_add_count($dbcon){
    $inquiry=$dbcon->query("select count(inquiry_id) as inquiry_count from `tbl_inquiry` where cdate >=  CURDATE() and inquiry_status = '0' ");
    $inquiry_count=brp_mysqli_fetch_assoc($inquiry);
   
    return $inquiry_count['inquiry_count'];
}
function today_quotation_created_count($dbcon){
  $q_sql = "select count(quotation_id) as quotation_count from `tbl_quotation` where cdate >=  CURDATE() and quotation_status = '0' ";
  $quotation=$dbcon->query($q_sql);
  $count_new_spare=brp_mysqli_fetch_assoc($quotation);

  return $count_new_spare['quotation_count'];
}
function today_pending_folloup_count($dbcon){
    $task_sql = "select count(task_id) as task_count from `tbl_task` where cdate >=  CURDATE() and task_status = '1' ";
    $task_exec=$dbcon->query($task_sql);
    $task_data=brp_mysqli_fetch_assoc($task_exec);

    return $task_data['task_count'];
}

function today_work_order_pending_count($dbcon){
    $workorder_sql = "select count(rp_id) as rp_count from `tbl_request_product` where cdate >=  CURDATE() and sp_id != '0' and main_request = '1' and finish_status = '0' and status = '0' ";
    $workorder_exec=$dbcon->query($workorder_sql);
    $workorder_data=brp_mysqli_fetch_assoc($workorder_exec);

    return $workorder_data['rp_count'];
}
function today_job_card_pending_count($dbcon){
    $wjobcard_sql = "select count(rp_id) as rp_count from `tbl_request_product` where cdate >=  CURDATE() and sp_id='0' and job_card_status in (1,3) and finish_status = '0' and status= '0' ";
    $wjobcard_exec=$dbcon->query($wjobcard_sql);
    $wjobcard_data=brp_mysqli_fetch_assoc($wjobcard_exec);

    return $wjobcard_data['rp_count'];
}
function today_indent_approve_pending_count($dbcon){
   $where.="  and rp.company_id=".$_SESSION['company_id'];
   $query="select count(rp.rp_id) as pending_cou from tbl_request_product as rp where rp.indent_status=1 and cdate >=  CURDATE() ".$where;

   $result=$dbcon->query($query);
   $row=brp_mysqli_fetch_assoc($result);
   return $row['pending_cou'];
}
function today_purchse_order_created_count($dbcon){
      $popending2="SELECT count(purchaseorder_id) as purchaseorder_count FROM tbl_purchaseorder as po left join tbl_ledger as l on po.vender_id=l.l_id left join city_mst city on l.cityid=city.cityid left join branch_mst as bms on bms.branch_id=po.branch_id where (  1 AND status = 0 and po_type_status=1 and po.company_id='".$_SESSION['company_id']."' and po.cdate >=  CURDATE()) ORDER BY po.purchaseorder_id desc";

      $pur_ds=$dbcon->query($popending2);
      $row=brp_mysqli_fetch_assoc($pur_ds);
     
      return $row['purchaseorder_count'];
}
function today_purchse_order_pending_count($dbcon){
      $where=" and po.company_id=".$_SESSION['company_id'];
      $popending2="select po.purchaseordertrn_id,po.mdate,pr.product_name,po.total,po.purchaseordertrn_status,po.cdate,po.user_id,po.po_ref_type,sum(po.product_qty) as pqty,po.po_ref_id,po.product_id,po.po_trn_req_status,GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id from tbl_purchasetrntemp as po 
       left join product_mst as pr on pr.product_id=po.product_id
       where po.cdate >=  CURDATE() and po.purchaseordertrn_status = 0 and po_trn_req_status=0 $where group by po.product_id,po.po_trn_req_status";
      $pur_ds=$dbcon->query($popending2);
      $pending_qty=brp_mysqli_num_rows($pur_ds);
     
      return $pending_qty;
}
function today_pending_grn_count($dbcon){

    $where=" and trn.company_id=".$_SESSION['company_id']; 
    $pooverduepending="SELECT COUNT(trn.purchaseordertrn_id) as po_overdue_pending FROM `tbl_purchaseordertrn` as trn
        left join product_mst as pro on pro.product_id=trn.product_id
        left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
        WHERE trn.cdate >= CURDATE() and trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po_approval_status=1".$where;
       $po_overdue_pending=brp_mysqli_fetch_assoc($dbcon->query($pooverduepending));

    return $po_overdue_pending['po_overdue_pending'];
}
function today_purchase_bill_pending_count($dbcon){
     $where=" and grn.company_id=".$_SESSION['company_id'];   

      $query_pur="SELECT grn.*,gtrn.product_qty,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status=0 and chtrn.grn_id=grn.grn_id and gtrn.product_id=chtrn.product_id) as used_qty FROM tbl_grn as grn 
        left join tbl_grn_trn as gtrn on gtrn.grn_id=grn.grn_id
        where grn.cdate >= CURDATE() and grn.grn_status=0 and gtrn.grn_trn_status=0 and grn.ref_type=2 and grn.purchase_status=0 $where having gtrn.product_qty > used_qty order by grn.grn_id";
    
      $conew_pub=$dbcon->query($query_pur);
      $purchase_bill_pending=brp_mysqli_num_rows($conew_pub);

      return $purchase_bill_pending;
}
function today_purchase_total_amount($dbcon){
    
    $purchase_sql="select if(sum(g_total), sum(g_total),0)  as purchase_amount from `tbl_pono` where cdate >=  CURDATE() and status = '0' ";

    $purchase_exec=$dbcon->query($purchase_sql);
    $purchase_data=brp_mysqli_fetch_assoc($purchase_exec);
   
    return $purchase_data['purchase_amount'];
} 

function today_sales_total_amount($dbcon){
    $sales_sql="select if(sum(g_total), sum(g_total),0)  as sales_amount from `tbl_invoice` where cdate >=  CURDATE() and invoice_status = '0' ";

    $sales_exec=$dbcon->query($sales_sql);
    $sales_data=brp_mysqli_fetch_assoc($sales_exec);
   
    return $sales_data['sales_amount'];
}  
function today_won_inquiry_count($dbcon){
    $inquiry="select count(inquiry_id) as inquiry_count from `tbl_inquiry` where cdate >=  CURDATE() and opp_id='12' and stage_prob like '100%' and inquiry_status = '0' ";

    $inquiry_exec=$dbcon->query($inquiry);
    $inquiry_data=brp_mysqli_fetch_assoc($inquiry_exec);
   
    return $inquiry_data['inquiry_count'];
}
function today_total_amount_of_won_inquiry($dbcon){
    $inquiry="select inquiry_id, g_total from `tbl_inquiry` where cdate >=  CURDATE() and opp_id='12' and stage_prob like '100%' and inquiry_status = '0' ";

    $inquiry_exec=$dbcon->query($inquiry);
    $amount_total = 0;
    while($inquiry_data=brp_mysqli_fetch_assoc($inquiry_exec)){
        $inquiry_id = $inquiry_data['inquiry_id'];

        $quotation = "select g_total from tbl_quotation where inquiry_id = '".$inquiry_id."' ";
        $quotation_exec=$dbcon->query($quotation);
        if(brp_mysqli_num_rows($quotation_exec) > 0 ){
            $quotation_data=brp_mysqli_fetch_assoc($quotation_exec);
            $amount_total = $amount_total+$quotation_data['g_total'];
        }else{
            $amount_total = $amount_total+$inquiry_data['g_total'];
        }
    }
   
    return $amount_total;
}
/* End
*  Date: 24-05-2021
*/

/* Start :: Code by Sanat ::  10-08-2021 
    comment :: Total sales till date of finantial year  
*/
function today_purchase_total_till_date($dbcon){


$purchase="select if(sum(g_total), sum(g_total),0) as total_purchase_amt from `tbl_pono` where  status = '0' and  DATE(cdate) >= '2021-04-01' and DATE(cdate) <= CURDATE()";

    $purchase_exec=$dbcon->query($purchase);
    $purchase_data=brp_mysqli_fetch_assoc($purchase_exec);
   
    return $purchase_data['total_purchase_amt'];
   
} 

function today_sales_total_till_date($dbcon){


$sales="select if(sum(g_total), sum(g_total),0) as total_sales_amt from `tbl_invoice` where  invoice_status = '0' and  DATE(cdate) >= '2021-04-01' and DATE(cdate) <= CURDATE()";

    $sales_exec=$dbcon->query($sales);
    $sales_data=brp_mysqli_fetch_assoc($sales_exec);
   
    return $sales_data['total_sales_amt'];
   
} 


function today_pending_inquiry_folloup_count($dbcon){
    $task_sql = "select count(task_id) as task_count from `tbl_task` where cdate >=  CURDATE() and task_status = '1' and task_type_id = 16";
    $task_exec=$dbcon->query($task_sql);
    $task_data=brp_mysqli_fetch_assoc($task_exec);

    return $task_data['task_count'];
}

function today_pending_quotation_folloup_count($dbcon){
    $task_sql = "select count(task_id) as task_count from `tbl_task` where cdate >=  CURDATE() and task_status = '1' and task_type_id = 21 ";
    $task_exec=$dbcon->query($task_sql);
    $task_data=brp_mysqli_fetch_assoc($task_exec);

    return $task_data['task_count'];
}

/* End :: Cody by Sanat ::  10-08-2021 */

/* post crm : dhaval */

function count_value_wise_target($dbcon,$user_id)
{
    $month = date("m");

    $where="";
    if($_SESSION['user_type']==2)
    {
        //$where.=" and user_id IN ($_SESSION[user_id],1)";
        $q = "select * from tbl_cust_forecast_pr as f left join tbl_customer as c on c.cust_id = f.forecast_cust_id where f.forecast_month='$month' and f.forecast_type='1' and f.isdelete='0' AND c.ledger_id != 0 and c.post_crm_yes_no=0";
        $query = $dbcon->query($q);
        $count= brp_mysqli_num_rows($query);
       
    }
    else
    {
        $q = "select * from tbl_cust_forecast_pr as f left join tbl_customer as c on c.cust_id = f.forecast_cust_id where f.forecast_month='$month' and f.forecast_type='1' and f.isdelete='0' AND c.ledger_id != 0 AND FIND_IN_SET(".$_SESSION['user_id'].",c.cust_assign_user and c.post_crm_yes_no=0)";
        $query = $dbcon->query($q);
        $count= brp_mysqli_num_rows($query);
    }
    return $count;
}

function count_product_wise_target($dbcon,$user_id)
{
    $month = date("m");

    $q = "select f.*,c.cust_owner
            from tbl_cust_forecast_pr as f
            inner join tbl_customer as c on c.cust_id=f.forecast_cust_id
            where f.forecast_type='0' and f.isdelete='0' and c.post_crm_yes_no=0 and c.cust_owner='$_SESSION[user_id]'";
    $query = $dbcon->query($q);
    $count = brp_mysqli_num_rows($query);
    return $count;
}
function get_total_current_month_target($dbcon,$user_id){
    $financial_year=getFinacialyear_data($dbcon);
    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $current_year = date("Y");
    $month = date("m");
    $where="";
    if($_SESSION['user_type']!=2)
    {
        $where.=" AND FIND_IN_SET(".$_SESSION['user_id'].",c.cust_assign_user)";
    }

    $q = "select sum(f.forecast_amount_pr) as current_month from tbl_cust_forecast_pr as f left join tbl_customer as c on c.cust_id = f.forecast_cust_id where f.forecast_month='$month' and f.forecast_type='1' and f.isdelete='0' AND c.ledger_id != 0 and c.post_crm_yes_no=0 and f.forecast_year between '$start_year' and '$current_year'";
        $query = $dbcon->query($q);
        $res = brp_mysqli_fetch_assoc($query);
    return $res['current_month'];
}
function get_total_achieved_target($dbcon,$user_id){
    $financial_year=getFinacialyear_data($dbcon);
    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $current_year = date("Y");
    $month = date("m");
    $where="";
    if($_SESSION['user_type']!=2)
    {
        $where.=" AND FIND_IN_SET(".$user_id.",c.cust_assign_user)";
    }

    $q = "select GROUP_CONCAT(c.ledger_id) as current_month from tbl_cust_forecast_pr as f left join tbl_customer as c on c.cust_id = f.forecast_cust_id where f.forecast_month='$month' and f.forecast_type='1' and f.isdelete='0' AND c.ledger_id != 0 and c.post_crm_yes_no=0 and f.forecast_year between '$start_year' and '$current_year'";
        $query = $dbcon->query($q);
        $res = brp_mysqli_fetch_assoc($query);

        $q_sql = $dbcon->query("select IFNULL(sum(g_total),0) as total from tbl_invoice where cust_id IN (".$res['current_month'].") and invoice_status='0' and MONTH(invoice_date)=$month AND YEAR(invoice_date) between '$start_year' and '$current_year'"); 
    $row=brp_mysqli_fetch_assoc($q_sql);
    return $row['total'];
    // return $res['current_month'];
}
function get_total_outstanding_target($dbcon,$user_id){
    $financial_year=getFinacialyear_data($dbcon);
    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $current_year = date("Y");
    $month = date("m");
    $where="";
    if($_SESSION['user_type']!=2)
    {
        $where.=" AND FIND_IN_SET(".$user_id.",c.cust_assign_user)";
    }

    $q = "select GROUP_CONCAT(c.cust_id) as current_month from tbl_cust_forecast_pr as f left join tbl_customer as c on c.cust_id = f.forecast_cust_id where f.forecast_month='$month' and f.forecast_type='1' and f.isdelete='0' AND c.ledger_id != 0 and c.post_crm_yes_no=0 and f.forecast_year between '$start_year' and '$current_year'";
        $query = $dbcon->query($q);
        $res = brp_mysqli_fetch_assoc($query);

        $q_sql = $dbcon->query("select IFNULL(sum(forecast_amount_pr),0)  as target_sum from tbl_cust_forecast_pr where forecast_cust_id IN (".$res['current_month'].") and forecast_type='1' and forecast_month = '$month' and forecast_year between '$start_year' and '$current_year' AND isdelete='0'"); 
    $row=brp_mysqli_fetch_assoc($q_sql);
    $total = get_total_achieved_target($dbcon,$user_id);
    return $row['target_sum']-$total;
}
function get_target_total_summery($dbcon,$user_id){
    $financial_year=getFinacialyear_data($dbcon);
    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $qry = $dbcon->query("SELECT f.forecast_month, sum(f.forecast_amount_pr) as total FROM tbl_cust_forecast_pr AS f LEFT JOIN tbl_customer as c on c.cust_id = f.forecast_cust_id WHERE f.forecast_type='1' and f.isdelete='0' AND c.ledger_id != 0 AND c.post_crm_yes_no=0 AND c.cust_status=0 AND c.company_id = '".$_SESSION['company_id']."' and FIND_IN_SET(".$user_id.",c.cust_assign_user) AND f.forecast_year between ".$start_year." AND ".$end_year." GROUP BY f.forecast_month");
    $str='<table class="table table-bordered table-hover table-striped">
    <thead>
    <tr>
    <th></th>
    <th>APR</th>
    <th>MAY</th>
    <th>JUN</th>
    <th>JUL</th>
    <th>AUG</th>
    <th>SEP</th>
    <th>OCT</th>
    <th>NOV</th>
    <th>DEC</th>
    <th>JAN</th>
    <th>FEB</th>
    <th>MAR</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="font-weight: bold;">Total Target</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        </tr>
        <tr>
    <td style="font-weight: bold;">Total Outstanding</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    </tr>
    <tr>
    <td style="font-weight: bold;">Total Achieved Target</td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    </tr>
    </tbody>
    </table>';
    return $str;
}
?>