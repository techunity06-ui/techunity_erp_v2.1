<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
    
	if(strtolower($POST['mode']) == "load_value_vise_target") {

        $companyConfiguration=getCompanyConfiguration($dbcon);
        $outstanding = $companyConfiguration['enable_count_outstanding_target'];
        
        $bulkAccessArray = canCheckPermissionAccess($dbcon, [
            //APPOINTMNET_SLUG_EDIT,
            //APPOINTMNET_SLUG_DELETE
        ]);
		
        $month = $POST['month'];

      	$str="";

  		$cnt=1;
        if($_SESSION['user_type']==2)
        {
            $where="";
        }
        else{
            // $sel_report_to = $dbcon->query("select * from users where report_to_user_id='$_SESSION[user_id]'");
            // if(brp_mysqli_num_rows($sel_report_to)>0)
            // {
            //     $user_array = get_report_to_user($dbcon,$_SESSION['user_id']);
            //     unset($user_array['1']);
            //     $user_array_in = implode(',',$user_array);
            //     $where.=" and f.user_id IN ($user_array_in)"; 
            // }
            // else
            // {
            //     $where.=" and f.user_id IN ($_SESSION[user_id])";    
            // }
            $where.=" and FIND_IN_SET($_SESSION[user_id],c.cust_assign_user)";
        }
        if(!empty($POST['user_id'])){
            $where.=' and c.cust_owner = '.$POST['user_id'];
        }
        if(!empty($POST['state_id'])){
                $where.=" and c.state_id = ".$POST['state_id'];
            }

        $appData = array();
        $i=1;
        $aColumns = array('f.forecast_pr_id','c.cust_name','f.forecast_amount_pr','user.user_name','f.forecast_month','f.forecast_year','f.forecast_cust_id','c.ledger_id','c.cust_id');
        $sIndexColumn = "f.forecast_pr_id";
        $isWhere = array("f.forecast_month='$month' and f.forecast_type='1' and f.isdelete='0' AND c.ledger_id != 0 and c.post_crm_yes_no= 0 ".$where);
        $sTable = "tbl_cust_forecast_pr as f";          
        $isJOIN = array('left join tbl_customer as c on c.cust_id = f.forecast_cust_id
            left join users as user on c.cust_owner = user.user_id');
        $hOrder = "c.cust_name ASC";
        include('../../../include/pagging.php');
        $appData = array();
        $id=1;
        foreach($sqlReturn as $row) {
                    $invoice_total = get_invoice_current_forecast($dbcon,$row['ledger_id'],$row['forecast_month']);
            if($outstanding==1)
            {
                $last_month = $month-1;
                $last_month_name = date('F', mktime(0, 0, 0, $last_month, 10));
                $check_total_forecast=check_current_month_forecast($dbcon,$row['forecast_cust_id'],1,$row['forecast_month']);

                if($check_total_forecast!=0)
                {
                    $total_outstanding = $check_total_forecast-$invoice_total;
                }
                else
                {
                    $invoice_total="0.00";
                    $total_outstanding="0.00";
                }
            }
            else
            {
                $last_month="";
                $last_month_name="";
                $invoice_total = "";
                $check_total_forecast="";
                $total_outstanding="NA";
            }
            $row_data = array();
            $row_data[] = $row['sr'];
            $row_data[] = $row['cust_name'];
            $row_data[] = $row['user_name'];
            $row_data[] = $row['forecast_amount_pr'];
            $row_data[] = $total_outstanding;
            $row_data[] = $invoice_total;
            $row_data[] = "<a class='btn btn-info btn-xs' data-original-title='Add Followup' data-toggle='tooltip' data-placement='top' onclick='add_followup(".$row['forecast_cust_id'].",".$month.")'>
                        <i class='fa fa-plus'></i>
                    </a>&nbsp;
                    <a class='btn btn-primary btn-xs' href='".ROOT.CRM_ROOT."value_target_details/".$month."/".$row['forecast_cust_id']."' data-original-title='View Details' data-toggle='tooltip' data-placement='top'>
                        <i class='fa fa-eye'></i>
                    </a>&nbsp;
                    <a class='btn btn-warning btn-xs' data-original-title='Followup Histpry' data-toggle='tooltip' data-placement='top' onclick='followup_history_value(".$row['forecast_cust_id'].")'>
                        <i class='fa fa-tasks'></i>
                    </a>&nbsp;
                    <a class='btn btn-xs btn-success' data-original-title='Edit' data-toggle='tooltip' data-placement='top' href='".ROOT.CRM_ROOT."customeraddedit_user/".$row['forecast_cust_id']."'><i class='fa fa-pencil'></i></a>";
            $appData[] = $row_data;
            $id++;
        }
        $output['aaData'] = $appData;
        echo json_encode( $output );

	}
    else if(strtolower($POST['mode']) == "load_product_vise_target") {

        $companyConfiguration=getCompanyConfiguration($dbcon);
        $outstanding = $companyConfiguration['enable_count_outstanding_target'];

        $bulkAccessArray = canCheckPermissionAccess($dbcon, [
            //APPOINTMNET_SLUG_EDIT,
            //APPOINTMNET_SLUG_DELETE
        ]);
        
        $str="";

        $cnt=1;
        $query = $dbcon->query("select f.*,sum(f.forecast_amount_pr) as total,pr.product_name
            from tbl_cust_forecast_pr as f
            left join product_mst as pr on pr.product_id = f.forecast_pr_product_id
            where f.forecast_type='0' and f.isdelete='0' and f.user_id='$_SESSION[user_id]'");
        while($row=brp_mysqli_fetch_assoc($query))
        {

            // if($outstanding==1)
            // {
            //     $last_month = $month-1;
            //     $last_month_name = date('F', mktime(0, 0, 0, $last_month, 10));
            //     $check_last=check_last_month_forecast($dbcon,$last_month,$row['forecast_cust_id']);

            //     if($check_last!=0)
            //     {
            //         $last_month_total = get_invoice_total($dbcon,$last_month,$row['ledger_id']);
            //     }
            //     else
            //     {
            //         $last_month_total="0";
            //     }
            // }
            // else
            // {
            //     $last_month="";
            //     $last_month_name="";
            //     $last_month_total = "";
            //     $check_last="";
            // }
            
            $str.="<tr>

                <th>".$cnt."</th>
                <td>".$row['product_name']."</td>
                <td>".$row['total']."</td>
                <td>".get_invoice_total_by_product($dbcon,$row['forecast_pr_product_id'])."</td>
                <td>
                    <a class='btn btn-info btn-xs' data-original-title='Add Followup' data-toggle='tooltip' data-placement='top' onclick='add_followup_product(".$row['forecast_cust_id'].",".$row['forecast_pr_product_id'].")'>
                        <i class='fa fa-plus'></i>
                    </a>&nbsp;
                    <a class='btn btn-primary btn-xs' href='".ROOT.CRM_ROOT."product_target_details/".$row['forecast_pr_product_id']."'>
                        <i class='fa fa-eye'></i>
                    </a>&nbsp;
                    <a class='btn btn-warning btn-xs' data-original-title='Followup Histpry' data-toggle='tooltip' data-placement='top' onclick='followup_history_product(".$row['forecast_cust_id'].")'>
                        <i class='fa fa-tasks'></i>
                    </a>&nbsp;
                    <a class='btn btn-xs btn-success' data-original-title='Edit' data-toggle='tooltip' data-placement='top' href='".ROOT.CRM_ROOT."customeraddedit_user/".$row['forecast_cust_id']."'><i class='fa fa-pencil'></i></a>
                </td>
            </tr>";

            $cnt++;
        }


        $str.="</table>";

        echo $str;

    }

    else if(strtolower($POST['mode']) == "add_followup_value_wise") {

        echo $POST['cust_id'];

    }
    else if(strtolower($POST['mode']) == "add_value_followup") {
        /*echo "112";*/
        $show_user_ids = show_user_ids($dbcon, $POST['assign_user_ids']);
        
        $task_type_id       = $POST['task_type_id'];
        $task_due_date      = $POST['task_due_date'];
        $followup_remark    = $POST['followup_remark'];
        $opp_id             = $POST['opp_id'];
        $sales_stage_id     = $POST['sales_stage_id'];
        $assign_user_ids    = $POST['assign_user_ids'];
        $task_priority_id   = $POST['task_priority_id'];
        $task_alert_id      = $POST['task_alert_id'];
        $email_template_id  = $POST['email_template_id'];


        $cust_id = $POST['cust_id'];
        $month_id = $POST['month_id'];
        
        $prev_task_id = get_previous_taskid_postcrm($dbcon,$cust_id);

        $task_info['show_user_ids']     = $show_user_ids;
        $task_info['task_type_id']      = $task_type_id;
        $task_info['task_rel_id']       = 5;
        $task_info['opp_id']            = $opp_id;
        $task_info['sales_stage_id']    = $sales_stage_id;
        $task_info['task_name']         = "Value Wise forecast followup for month ".date('F', mktime(0, 0, 0, $month_id, 10));
        $task_info['cust_id']           = $cust_id;
        $task_info['task_remark']       = $followup_remark;
        $task_info['assign_user_ids']   = $assign_user_ids;
        $task_info['task_priority_id']  = $task_priority_id;
        $task_info['task_alert_id']     = $task_alert_id;
        $task_info['email_template_id'] = $email_template_id;
        $task_info['cdate']             = date("Y-m-d H:i:s");
        $task_info['create_date']       = date('Y-m-d H:i:s');
        $task_info['task_due_date']     = date("Y-m-d H:i:s",strtotime($POST['task_due_date']));
        $task_info['entry_type']        = 3;//Fixed Task Type
        $task_info['user_id']           = $_SESSION['user_id'];
        $task_info['company_id']        = $_SESSION['company_id'];
        $task_info['is_delete']         = 1;
        $task_info['perent_id']         = $prev_task_id['prev_taskid'];
        
        $upd_qry = "update tbl_task set task_status=1,is_delete=1,task_completion_date='" . date("Y-m-d H:i:s") . "' where task_status=0 and entry_type=3 and cust_id=" . $cust_id;
        $upd_qry_rs = $dbcon->query($upd_qry);

        /*echo "123";exit;*/
        $ins_task_id=add_record('tbl_task', $task_info, $dbcon, $branch_id);

        //echo $ins_task_id;
        $upd_qry_tk = "UPDATE tbl_inq_attach SET inq_attach_status = 0, task_id = '".$ins_task_id."' WHERE inq_attach_status=0 AND task_id = 0 AND inquiry_id=0 AND user_id = '" . $_SESSION['user_id']."'";
        $upd_qry_tks = $dbcon->query($upd_qry_tk);

        if($ins_task_id)
        {
            echo "1";
        }
        else
        {
            echo "0";
        }
    }
    else if(strtolower($POST['mode']) == "followup_history_value") {

        $customer = $POST['customer'];

        $str="";
        $str.="<table class='table table-bordered table-hover table-striped'>";
        $str.="<tr>

            <th>#</th>
            <th>Create Date</th>
            <th>Next Followup Date</th>
            <th>Remark</th>
        </tr>";

        $cnt=1;
        $sel = $dbcon->query("select * from tbl_task where cust_id='$customer' and entry_type='3'");
        if(brp_mysqli_num_rows($sel)>0)
        {
            while($row=brp_mysqli_fetch_assoc($sel))
            {
                $str.="<tr>

                    <th>".$cnt."</th>
                    <th>".date("d/m/Y H:i:s A",strtotime($row['create_date']))."</th>
                    <th>".date("d/m/Y H:i:s A",strtotime($row['task_due_date']))."</th>
                    <th>".$row['task_remark']."</th>
                </tr>";

                $cnt++;
            }
        }
        else
        {
            $str.="<tr>
                <th colspan='4' style='text-align:center'>No Data Found</th>
            </tr>";
        }

        echo $str;
    }

    else if(strtolower($POST['mode']) == "add_product_followup") {

        $task_type_id = $POST['task_type_id'];
        $task_due_date = $POST['task_due_date'];
        $followup_remark = $POST['followup_remark'];
        $cust_id = $POST['cust_id'];
        $f_product_id = $POST['f_product_id'];

        $task_info['show_user_ids'] = $_SESSION['user_id'];
        $task_info['task_type_id']  = $task_type_id;
        $task_info['task_rel_id']   = 5;
        $task_info['task_name']     = "Product Wise forecast followup for - ". get_id_detail($dbcon,'product_mst','product_id',$f_product_id,'product_name');
        $task_info['cust_id']       = $cust_id;
        $task_info['task_remark']   = $followup_remark;
        $task_info['assign_user_ids']   = $_SESSION['user_id'];
        $task_info['task_priority_id']  = 1;
        $task_info['cdate']             = date("Y-m-d H:i:s");
        $task_info['create_date']       = date('Y-m-d H:i:s');
        $task_info['task_due_date']     = date("Y-m-d H:i:s",strtotime($POST['task_due_date']));
        $task_info['entry_type']    = 4;//Fixed Task Type
        $task_info['user_id']       = $_SESSION['user_id'];
        $task_info['company_id']    = $_SESSION['company_id'];
        $task_info['is_delete']    = 1;
        
        $ins_task_id=add_record('tbl_task', $task_info, $dbcon, $branch_id);

        if($ins_task_id)
        {
            echo "1";
        }
        else
        {
            echo "0";
        }


    }

    else if(strtolower($POST['mode']) == "followup_history_product") {

        $customer = $POST['customer'];

        $str="";
        $str.="<table class='table table-bordered table-hover table-striped'>";
        $str.="<tr>

            <th>#</th>
            <th>Create Date</th>
            <th>Next Followup Date</th>
            <th>Remark</th>
        </tr>";

        $cnt=1;
        $sel = $dbcon->query("select * from tbl_task where cust_id='$customer' and entry_type='4'");
        if(brp_mysqli_num_rows($sel)>0)
        {
            while($row=brp_mysqli_fetch_assoc($sel))
            {
                $str.="<tr>

                    <th>".$cnt."</th>
                    <th>".date("d/m/Y H:i:s A",strtotime($row['create_date']))."</th>
                    <th>".date("d/m/Y H:i:s A",strtotime($row['task_due_date']))."</th>
                    <th>".$row['task_remark']."</th>
                </tr>";

                $cnt++;
            }
        }
        else
        {
            $str.="<tr>
                <th colspan='4' style='text-align:center'>No Data Found</th>
            </tr>";
        }

        echo $str;
    }
    
    else if(strtolower($POST['mode'])== "show_task_attach_data") {
        $chkmode=$POST['modee'];
        // $delete_btn_per = in_array(INQUIRY_SLUG_DELETE,$bulkAccessArray);
        $query="SELECT * FROM tbl_inq_attach WHERE inq_attach_status = 0 AND task_id = '".$POST['task_id']."' and inquiry_id=0";
        $result=$dbcon->query($query);
        echo '<table class="display table table-bordered table-striped">
        <tr>
        <th width="60%" class="text-center">Document Name</th>
        <th width="30%" class="text-center">Attached Document</th>';
        echo'<th width="10%" class="text-center">Action</th>';
        echo'</tr>
        <tbody>';
        if(brp_mysqli_num_rows($result)>0){
            while($rows=brp_mysqli_fetch_assoc($result)){
                echo '<tr> 
                <td style="vertical-align:top;">
                <strong>'.$rows['inq_attch_doc_name'].'</strong>
                </td>
                <td style="vertical-align:top;" class="text-center">
                <a href="'.ROOT.INQ_ATTACH_VWING.$rows['inq_attch_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
                </td>
                <td style="vertical-align:top"><button type="button" class="btn btn-sm btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_task_attach_data('.$rows['inq_attach_id'].')">X</button></td>
                </tr>';
                $i++;
            }
        }
        else{
            echo '<tr><td colspan="3" class="text-center">NO DATA FOUND</td></tr>';
        }

        echo '</tbody>
        </table>';
    }
?>