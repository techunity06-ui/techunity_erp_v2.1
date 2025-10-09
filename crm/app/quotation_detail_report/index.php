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

if(strtolower($POST['mode']) == "cust_so_detail_report")
{
    $s_date=explode(' - ',$POST['date']);
    $_SESSION['start']=$s_date[0];
    $_SESSION['end']=$s_date[1];
    $cust_id=$POST['cust_id'];
    $str = $where = $whr = '';
    $where.="  and so.quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND so.quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
    
    /*if(!empty($cust_id)){
        $where.=" AND so.cust_id = ".$POST['cust_id'];
    }*/

    $query =" SELECT cus.cust_name,so.quotation_no,so.quotation_date,so.task_id,so.cdate,so.quotation_ref,inq.inquiry_no, user.user_name as owner,mcd.mcd_name as customer_type,cadd.c_add_address, city.city_name, state.state_name, con.c_con_fname, con.c_con_mobile, con.c_con_email, pro.product_name, trn.product_amount,rf.rb_name, inq.stage_prob as probablity, so.inquiry_id, op.opp_stage as stage FROM `tbl_quotation` as so 
    left join tbl_quotation_trn as trn on trn.quotation_id=so.quotation_id
    left join product_mst as pro on pro.product_id = trn.product_id
    left join tbl_inquiry as inq on inq.inquiry_id = so.inquiry_id
    left join tbl_customer as cus on cus.cust_id = so.cust_id 
    left join tbl_refer_by as rf on rf.rb_id=cus.cust_source 
    left join tbl_cust_contact as con on con.c_con_id = so.c_con_id 
    left join tbl_cust_address as cadd on cadd.cust_id = so.cust_id and cadd.c_addr_defult=1
    left join tbl_opportunity_mst as op on op.opp_id=inq.opp_id
    left join city_mst as city on city.cityid  = cadd.c_add_city
    left join state_mst as state on state.stateid = cadd.c_add_state
    left join users as user on user.user_id = so.user_id 
    left join tbl_master_category_detail as mcd on mcd.mcd_id = cus.cust_type
    WHERE so.quotation_status = 0 and trn.quot_trn_status=0  and so.company_id = ".$_SESSION['company_id']." ".$where;
    $result=$dbcon->query($query);
    $user_id = $_SESSION['user_id'];
    $fis = check_crm_find_in_set_new($dbcon, $user_id, 1);
    $ftp = " and FIND_IN_SET (" . $user_id . ", show_user_ids)";
    $str .= '<table class="table table-bordered table-striped " id="data_list">
        <thead> 
            <tr>
                <th style="text-align:center;white-space:nowrap" width="3%">Quotation Date</th>
                <th style="text-align:center;white-space:nowrap" width="11%">Inquiry No</th>
                <th style="text-align:center;white-space:nowrap" width="9%">Quotation Ref No</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Company Name</th>
                <th style="text-align:center;white-space:nowrap" width="8%">Address</th>
                <th style="text-align:center;white-space:nowrap" width="8%">City</th>
                <th style="text-align:center;white-space:nowrap" width="8%">Contact Person</th>
                <th style="text-align:center;white-space:nowrap" width="8%">Contact No.</th>
                <th style="text-align:center;white-space:nowrap" width="15%">Email</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Source Of Inquiry</th>
                <!--<th style="text-align:center;white-space:nowrap" width="20%">Stage</th>-->
                <th style="text-align:center;white-space:nowrap" width="20%">Remark By Task Assign</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Quotation Made By Person Name</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Task Assign User Name</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Product Name</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Quotation Basic Price</th>
            </tr>
        </thead>
        <tbody>';
        $cnt = brp_mysqli_num_rows($result);
        if($cnt>0){
            $i = 1;
            while($row = mysqli_fetch_assoc($result)){
                
                $query1 = "select task_remark,assign_user_ids from tbl_task where task_id=".$row['task_id'];
                $result1 = $dbcon->query($query1);
                $task = brp_mysqli_fetch_array($result1);
                $str .= '<tr>
                    <td>'.date('d-m-Y',strtotime($row['quotation_date'])).'</td>
                    <td>'.$row['inquiry_no'].'</td>
                    <td>'.$row['quotation_no'].'</td>
                    <td>'.$row['cust_name'].'</td>
                    <td>'.$row['c_add_address'].'</td>
                    <td>'.$row['city_name'].'</td>
                    <td>'.$row['c_con_fname'].'</td>
                    <td>'.$row['c_con_mobile'].'</td>
                    <td>'.strtolower($row['c_con_email']).'</td>
                    <td>'.$row['rb_name'].'</td>
                    <!--<td>'.$row['stage'].'</td>-->
                    <td>'.nl2br($task['task_remark']).'</td>
                    <td>'.$row['owner'].'</td>
                    <td>'.getTaskAssignNameCommaSeparated($dbcon, $task['assign_user_ids']).'</td>
                    <td>'.$row['product_name'].'</td>
                    <td>'.$row['product_amount'].'</td>
                </tr>';
                $i++;
            }
        }else{
            $str .= '<tr>
                <td colspan="17" style="text-align:center">No Data Found...!!!!</td>
            </tr>';
        }

    $str .='</tbody>
    </table>';
    
    echo $str;
}

function getTaskAssignNameCommaSeparated($dbcon, $assign_user_ids)
{

    $strVal = '';
    $qry = 'SELECT tsk.task_id, GROUP_CONCAT(userdata.user_name) AS valuesdata FROM tbl_task tsk JOIN users AS userdata ON FIND_IN_SET(userdata.user_id, "' . $assign_user_ids . '") GROUP BY tsk.task_id';
    $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));

    if ($qry_rel) {
        $strVal = $qry_rel['valuesdata'];
    }
    return $strVal;
}
?>