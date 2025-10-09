<?php
	session_start(); 
	$AJAX = true;
	include("../../config/config.php");
	//error_reporting(E_ALL);
	include("../../config/session.php");
	include(COMMON_FUNCTION_PATH."common_functions.php");
	include("../../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);		
	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	}
	else {
		$POST = bulk_filter($dbcon,$_GET);
	}
	if(strtolower($POST['mode']) == "generate_report_min") 
	{
		$production_pro_search = $company_config['production_pro_search'];
		$pro_search=explode(",", $production_pro_search);
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/

		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			MRP_REJECT_QC_REQUEST_LIST_SLUG_VIEW,MRP_REJECT_QC_REQUEST_LIST_SLUG_CREATE
		]);

		$appData = array();
		$i=1;
		$aColumns = array('pmst.product_icode','dr.drawing_number','qtrn.qctrn_id','pmst.product_id','qc_rejected','qc_rejected_used','pmst.product_name','tc.cat_name','sum(qc_rejected-qc_rejected_used) as pending_qty');
		$sIndexColumn = "qtrn.qctrn_id";
		$isWhere = array("qtrn.qc_status=0 and qtrn.qc_rejected!=0 and qc_rejected>qc_rejected_used");
		$sTable = "tbl_qc_trn as qtrn";
		$isJOIN = array('left join product_mst as pmst on pmst.product_id=qtrn.qc_product left join tbl_category as tc on pmst.product_category=tc.cat_id','left join tbl_drawing as dr on dr.drawing_id = pmst.drawing_id');
		$hOrder = "pmst.product_name desc";
		$hGroupby = array("qtrn.qc_product");
		$having=" pending_qty > 0 ";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {

			// Check the tbl_qc_process_trn table for quantity
			$set11="select rp.*,sum(reject_qty-reject_request_qty) as pending_qty from tbl_qc_process_trn as rp
			where rp.qc_process_status=0 and rp.reject_qty>0 and CAST(reject_qty as DECIMAL(50,2)) > CAST(reject_request_qty as DECIMAL(50,2)) and rp.product_id=".$row['product_id']." group by rp.product_id";
			$ser=$dbcon->query($set11);
			$set_row=brp_mysqli_fetch_assoc($ser);
			
			if($set_row['pending_qty']>0){
				$drawing_number = "";
				$item_code = "";
				 if(in_array('drawing',$pro_search)){
			            $drawing_number = " -- (".$row['drawing_number'].")";
			        }
			        if(in_array('item',$pro_search)){
			            $item_code = " -- (".$row['product_icode'].")";
			        }

				$row_data = array();
				$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				$row_data[] = $set_row['pending_qty'];

				$view='';
				if(in_array(MRP_REJECT_QC_REQUEST_LIST_SLUG_CREATE,$bulkAccessArray)){
					$view='<a class="btn btn-xs btn-primary" data-original-title="" data-toggle="tooltip" data-placement="top" href="'.ROOT.'production/rejectrequestproduct/'.$row['product_id'].'"><i class="fa fa-paper-plane"></i> Request</a>';
				}
			
				$row_data[] = $view;
			
				$appData[] = $row_data;
				$id++;
			}
		}
			$output['aaData'] = $appData;
			echo json_encode( $output );
	}	
?>