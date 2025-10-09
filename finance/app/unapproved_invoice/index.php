<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");
//Ankit Sompura 09-01-2021
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    FINANCE_PENDING_INVOICE_APPROVE
]);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$branch_id = $POST['branch_id'];
        $type = $POST['type_id'];
        $where = '';
		
            if($branch_id){
                $where .= check_branch('invoice',$branch_id);
            }

            if($POST['type_id'] >= 0)
			{
                $where .=" and invoice.approve_status='$type' ";
			}
			$where.="  and invoice_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date <= '".date('Y-m-d',strtotime($s_date[1]))."' ";
			$appData = array();
			$i=1;
			$aColumns = array('invoice_id','invoice_no','cust.l_name','invoice_date','invoicetype.invoice_type','g_total','paid_amount','invoice_status','invoice.cdate','invoice.user_id','invoice.usertype_id','invoice.invoicetype_id','invoice.gst_flag','invoice.approve_status');
			$sIndexColumn = "invoice_id";
			$isWhere = array("invoice.invoice_status=0 and invoice.company_id = ".$_SESSION['company_id']." ".$where);
			$sTable = "tbl_invoice as invoice";
			$isJOIN = array('inner join tbl_ledger cust on invoice.cust_id=cust.l_id','left join tbl_invoicetype invoicetype on invoice.invoicetype_id=invoicetype.invoicetype_id');
			$hOrder = "invoice.invoice_id desc";
			include($path.'include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
                //$row_data[] = $row["invoice_type"];
                $row_data[] = $row["invoice_no"];
                $row_data[] = date('d M, Y',strtotime($row["invoice_date"]));
                $row_data[] = $row["l_name"];
                $row_data[] = $row["g_total"];

                if($row['approve_status']=='1'){
                    $row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
                }
                else if($row['approve_status']=='2'){
                    $row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Rejected</div>';
                }
                else{
                    $row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
                }

                if(in_array(FINANCE_PENDING_INVOICE_APPROVE,$bulkAccessArray)) {
                    $row_data[] = '<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Quotation" data-toggle="tooltip" data-placement="top" onClick="open_approv_invoice(' . $row['invoice_id'] . ',\'' . $row['invoice_no'] . '\')"><i class="fa fa-exclamation-triangle"></i></button>';
                }
                $appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
        else if(strtolower($POST['mode']) == "load_invoice_hist_datatable") {

        $where='';
        if($POST['invoice_id']){
            $where.="  and log.invoice_id=".$POST['invoice_id'];
        }

        $appData = array();
        $i=1;
        $aColumns = array('log.invoice_aprv_log_id','log.invoice_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.created_at', 'log.user_id');
        $sIndexColumn = "log.invoice_aprv_log_id";
        $isWhere = array("log.invoice_aprv_log_status = 0 ".$where." ");
        $sTable = "tbl_invoice_aprv_log as log";
        $isJOIN = array('left join users as usr on usr.user_id=log.user_id');
        $hOrder = "log.invoice_aprv_log_id desc";
        include($path.'include/pagging.php');
        $appData = array();
        $id=1;
        foreach($sqlReturn as $row) {
            $row_data = array();
            $row_data[] = $row['sr'];
            $row_data[] = $row['user_name'];

            if($row['approve_status']=='1'){
                $row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
            }
            else{
                $row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Reject</div>';
            }

            $row_data[] = nl2br($row['approve_remark']);
            $row_data[] = date("d-M-Y h:i A",strtotime($row['created_at']));
            $row_data[] = '<button type="button" class="btn btn-xs btn-primary" data-original-title="View Image List" data-toggle="tooltip" data-placement="top" onclick="show_image_list('.$row['invoice_aprv_log_id'].','.$row['invoice_id'].')"><i class="fa fa-eye"></i></button>';

            $appData[] = $row_data;
            $id++;
        }
        $output['aaData'] = $appData;
        echo json_encode( $output );
    }
        else if(strtolower($POST['mode']) == "add_apprv_hist") {
            $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
            // check if user has already Approved or Rejected Quotation
            $check_hist_qry = "SELECT log.invoice_aprv_log_id, usr.user_name, log.approve_status, log.approve_remark, log.created_at, log.user_id 
                    FROM tbl_invoice_aprv_log as log left join users as usr on usr.user_id=log.user_id 
                    where log.invoice_aprv_log_status=0 and log.invoice_id=".$POST['invoice_id']." and log.user_id = ".$_SESSION['user_id']."
                    order by log.invoice_aprv_log_id desc limit 1";
            $result = brp_mysqli_query($dbcon,$check_hist_qry);
            $history_data = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);

            if($history_data[0]['approve_status'] !== $POST['approve_status']) {
                $info1['approve_remark']	= $POST['approve_remark'];
                $info1['approve_status']	= $POST['approve_status'];
                $info1['invoice_id']	= $POST['invoice_id'];
                $info1['user_id']		= $_SESSION['user_id'];
                $info1['company_id']	= $_SESSION['company_id'];

                $inserid=add_record("tbl_invoice_aprv_log", $info1, $dbcon, $branch_id);

                $error=array();
                $extension=array("jpeg","jpg","png","gif","pdf");
                foreach($_FILES["apprv_attachment"]["tmp_name"] as $key=>$tmp_name) {
                    $file_name=$_FILES["apprv_attachment"]["name"][$key];
                    $file_tmp=$_FILES["apprv_attachment"]["tmp_name"][$key];
                    $ext=pathinfo($file_name,PATHINFO_EXTENSION);

                    if(in_array($ext,$extension)) {
                        $path='../../view/upload/invoice_approve_image/';
                        if(!file_exists($path.$file_name)) {
                            move_uploaded_file($file_tmp=$_FILES["apprv_attachment"]["tmp_name"][$key],$path.$file_name);
                        }else {
                            $filename=basename($file_name,$ext);
                            $newFileName=$filename.time().".".$ext;
                            move_uploaded_file($file_tmp=$_FILES["apprv_attachment"]["tmp_name"][$key],$path.$newFileName);
                        }
                        $imageinfo['invoice_aprv_log_id']	= $inserid;
                        $imageinfo['invoice_id']	= $POST['invoice_id'];
                        $imageinfo['image_file']	= $file_name;
                        $inserimageid=add_record('tbl_invoice_aprv_image', $imageinfo, $dbcon);
                    }
                }

                //Hide approve btn if not allowed
                //$final_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'final_aprv',$dbcon);
                //if(in_array(QUOTATION_SLUG_APPROVE,$bulkAccessArray)){
                    $infoso['approve_status'] = $POST['approve_status'];
                    $updateid=update_record('tbl_invoice', $infoso,"invoice_id=".$POST['invoice_id'] , $dbcon, $branch_id);
                //}
                echo TRUE;
            } else {
                echo FALSE;
            }

        }
        else if(strtolower($POST['mode']) == "show_invoice_aprv_image"){
            $invoice_id = $POST['invoice_id'];
            $invoice_aprv_log_id = $POST['invoice_aprv_log_id'];

            $qt_qry = "SELECT image_file FROM `tbl_invoice_aprv_image` where invoice_aprv_log_id = ".$invoice_aprv_log_id." and invoice_id = ".$invoice_id;
            $result = brp_mysqli_query($dbcon,$qt_qry);
            $images = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);

            //p($images);

            $html = '';
            $html .= '<table class="display table table-bordered table-striped" id="invoice-apprv-history-datatable">
                                <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>File Name</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>';

            foreach ($images as $key => $image){
                $html .= '<tr>
                                <td>'.($key++).'</td>
                                <td>'.$image["image_file"].'</td>
                                <td><a href="'.ROOT.'view//upload//invoice_approve_image//'.$image['image_file'].'" id="qt_order_conf_attch_view" target="_blank" class="btn btn-primary"><i class="fa fa-eye"></i></a></td>
                        </tr>
                        ';
            }
            $html .= '</tbody>
                    </table>';
            $output['html'] = $html;
            echo json_encode( $output );
        }

?>