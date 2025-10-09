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
    DISPATCH_LIST_VIEW,DISPATCH_APPROVAL
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

            
            $where .=" and invoice.dispatch_status=2 ";
			$where.="  and invoice_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date <= '".date('Y-m-d',strtotime($s_date[1]))."' ";
			$appData = array();
			$i=1;
			$aColumns = array('invoice.invoice_id','invoice.invoice_no','cust.l_name','invoice_date','g_total','transportation_name','trns.transport_doc_no','paid_amount','invoicetype.invoice_type','invoice_status','invoice.cdate','invoice.user_id','invoice.usertype_id','invoice.invoicetype_id','invoice.gst_flag','invoice.approve_status','invoice.dispatch_status');
			$sIndexColumn = "invoice_id";
			$isWhere = array("invoice.invoice_status=0 and invoice.approve_status=1 and invoice.company_id = ".$_SESSION['company_id']." ".$where);
			$sTable = "tbl_invoice as invoice";
			$isJOIN = array('inner join tbl_ledger cust on invoice.cust_id=cust.l_id','left join tbl_transport_transaction as trns on trns.transport_transaction_table_id = invoice.invoice_id and trns.transport_transaction_table="tbl_invoice"','left join transportation_details as trd on trd.id=trns.transport_id','left join tbl_invoicetype invoicetype on invoice.invoicetype_id=invoicetype.invoicetype_id');
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
                $row_data[] = $row["transportation_name"];
                $row_data[] = $row["transport_doc_no"];
                /*if($row['approve_status']=='1'){
                    $row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
                }
                else if($row['approve_status']=='2'){
                    $row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Rejected</div>';
                }
                else{
                    $row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
                }*/
                $check = 'yes';$popuptype='transport';$dipatch_button='';
                if(in_array(DISPATCH_APPROVAL,$bulkAccessArray)) {
                    if($row['dispatch_status']==0){
                        $dipatch_button = '<button class="btn btn-xs btn-warning" data-original-title="Dispatch Yes / No" data-toggle="tooltip" data-placement="top" onClick="transport_detail('.$row['invoice_id'].',\''.$row['invoice_no'].'\')" )"><i class="fa fa-bus" aria-hidden="true"></i></button>';  
                    }else{
                        $dipatch_button = '<button class="btn btn-xs btn-success" data-original-title="Dispatch Yes / No" data-toggle="tooltip" data-placement="top" onClick="finally_dispatch_detail('.$row['invoice_id'].',\''.$row['invoice_no'].'\')" )"><i class="fa fa-bus" aria-hidden="true"></i></button>';
                    }
                }
                
                if(in_array(DISPATCH_APPROVAL,$bulkAccessArray)) { 
                    $row_data[] = $dipatch_button;
                }
                
                $appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
        
        
        else if(strtolower($POST['mode']) == "transport_add"){
            $info['transport_id']       = $POST['transport_id'];                            
            $info['transport_gr_no']    = $POST['transport_gr_no'];                         
            $info['transport_gr_date']  = date("Y-m-d",strtotime($POST['transport_gr_date']));          
            $info['distance_km']        = $POST['distance_km'];                         
            $info['transport_mode']     = $POST['transport_mode'];      
            $info['transport_vehicle_no']   = $POST['transport_vehicle_no'];                            
            $info['transport_station']  = $POST['transport_station'];
            $info['transport_pincode']  = $POST['transport_pincode'];
            $info['transport_doc_no']   = $POST['transport_doc_no'];
            $info['transport_doc_date'] = date("Y-m-d",strtotime($POST['transport_doc_date']));         
            $info['transport_voucher']  = $POST['transport_voucher'];                           
            $info['transport_transaction_table']    = 'tbl_invoice';         
            $info['transport_transaction_table_id'] = $POST['invoice_id'];           
            $info['cdate']      = date("Y-m-d H:i:s");
            $info['user_id']    = $_SESSION['user_id'];
            $info['company_id'] = $_SESSION['company_id'];
            //var_dump($POST);exit;

            $dispatch_log['invoice_id']     = $POST['invoice_id'];
            $dispatch_log['dispatch_status']= $POST['dispatch_status'];
            $dispatch_log['dispatch_ref']   = 'pending_dispatch';
            $dispatch_log['cdate']          = date("Y-m-d H:i:s");
            $dispatch_log['user_id']        = $_SESSION['user_id'];
            $dispatch_log['company_id']     = $_SESSION['company_id'];
            $dispatch_log['branch_id']      = $_SESSION['branch_id'];
            
            
            $inserlog=add_record('tbl_dispatch_log', $dispatch_log, $dbcon);

            if($POST['transport_transaction_id']!='')
            {
                $updateid=update_record('tbl_transport_transaction', $info,"transport_transaction_id=".$POST['transport_transaction_id'] , $dbcon);
                $info12['dispatch_status']  = $POST['dispatch_status'];
                $updateid12=update_record('tbl_invoice', $info12,"invoice_id=".$POST['invoice_id'] , $dbcon);
                if($updateid){
                    $arr['msg']="update";
                }
                else{
                    $arr['msg']=0;
                }
            }
            else
            {
                $inserid=add_record('tbl_transport_transaction', $info, $dbcon);
                $info12['dispatch_status']  = $POST['dispatch_status'];
                $updateid12=update_record('tbl_invoice', $info12,"invoice_id=".$POST['invoice_id'] , $dbcon);
                if($inserid){
                    $arr['msg']=1;
                }
                else{
                   $arr['msg']=0;
                }
            }
            echo json_encode($arr); 
        }

        else if(strtolower($POST['mode']) == "transport_data"){
            $query = "SELECT * FROM tbl_transport_transaction where transport_transaction_table='tbl_invoice' and transport_transaction_table_id=".$POST['invoice_id']." and isdelete=0";
            $result = $dbcon->query($query);
            $row = brp_mysqli_fetch_array($result);
            echo json_encode($row);
        }
        
        else if(strtolower($POST['mode']) == "final_dispatch"){
            $dispatch_log['invoice_id']     = $POST['invoi_id'];
            $dispatch_log['remark']         = $POST['final_dispatch_remark'];
            $dispatch_log['attach_document']= upload_attch_file($_FILES);
            $dispatch_log['dispatch_status']= $POST['final_dispatch_status'];
            $dispatch_log['dispatch_ref']   = 'final_dispatch';
            $dispatch_log['cdate']          = date("Y-m-d H:i:s");
            $dispatch_log['user_id']        = $_SESSION['user_id'];
            $dispatch_log['company_id']     = $_SESSION['company_id'];
            $dispatch_log['branch_id']      = $_SESSION['branch_id'];
            
            $info12['dispatch_status']  = $POST['final_dispatch_status'];
            $updateid12=update_record('tbl_invoice', $info12,"invoice_id=".$POST['invoi_id'] , $dbcon);
            $inserlog=add_record('tbl_dispatch_log', $dispatch_log, $dbcon);

            if($inserlog){
                $arr['msg']=1;
            }
            else{
               $arr['msg']=0;
            }
            echo json_encode($arr);
        }

        else if(strtolower($POST['mode']) == "fetch_dispatch_history"){
            $appData = array();
            $i=1;
            $aColumns = array('disp.dispatch_logid','disp.remark','disp.attach_document','disp.dispatch_status','disp.cdate','us.user_name');
            $sIndexColumn = "disp.dispatch_logid";
            $isWhere = array("disp.is_delete=0 and disp.invoice_id=".$POST['invoice_id']." and disp.dispatch_ref='final_dispatch' and disp.company_id = ".$_SESSION['company_id']);
            $sTable = "tbl_dispatch_log as disp";
            $isJOIN = array("left join users as us on us.user_id=disp.user_id");
            $hOrder = "disp.dispatch_logid desc";
            include($path.'include/pagging.php');
            $appData = array();
            $id=1;
            foreach($sqlReturn as $row) {
                $row_data = array();

                if($row['dispatch_status']==1){
                    $dis_status = '<strong style="color:green">Yes</strong>';
                }else{
                    $dis_status = '<strong style="color:orange">No</strong>';
                }

                $row_data[] = $i;
                $row_data[] = $row["user_name"];
                $row_data[] = date('d M, Y',strtotime($row["cdate"]));
                $row_data[] = $dis_status;
                $row_data[] = $row["remark"];
                $row_data[] = '<a href="'.ROOT.DISPATCH_ATTACH_VIEWING.$row['attach_document'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>';
                
                $appData[] = $row_data;
                $id++;
            }
            $output['aaData'] = $appData;
            echo json_encode( $output );
        }

function upload_attch_file($FILES)
{
    $rand=rand(0,99999999);
    if(!empty($FILES['final_dispatch_attachment']['tmp_name'])) {
        $temp = explode(".", $FILES["final_dispatch_attachment"]["name"]);
        $extension = strtolower(end($temp));
        $File = "dispatch_attach".$rand.".".$extension;
        $tmp_name = $FILES["final_dispatch_attachment"]["tmp_name"];
        move_uploaded_file($tmp_name,DISPATCH_ATTACH_UPING.$File);

        return  $File;              
    }
}
?>  