<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$where="";
		
		if($POST['qc_type']){
			$where.= " and grn.ref_type=".$POST['qc_type'];
		}

		$appData = array();
		$i=1;
		$aColumns = array('trn.qctrn_id','sp.po_req_no','so.sales_order_no','qc.qc_id','qc.qc_no','qc.qc_date','pro.product_name','trn.qc_product_qty','grn.grn_no','grn.grn_date','qc_accepted','grn.ref_type','qty_reprocess','qc_rejected','trn.qc_status','pro.product_type');
		$sIndexColumn = "trn.qctrn_id";
		$isWhere = array("qc.qc_status=0 and trn.qc_status=0".$where);
		$sTable = "tbl_qc_trn as trn";			
		$isJOIN = array('left join tbl_qc as qc on qc.qc_id=trn.qc_id',
			'left join product_mst as pro on pro.product_id=trn.qc_product',
			'left join tbl_grn_trn as gt on gt.grn_trn_id = qc.grn_trn_id',
			'left join tbl_grn_sub_trn as gts on gts.grn_trn_id = qc.grn_trn_id',
			'left join tbl_request_product as rp on rp.rp_id=gts.rp_id'
			,'left join tbl_set_main_process as sp on sp.sp_id=rp.sp_id'
			,'left join tbl_sales_ordertrn as so_trn on sp.sales_order_trn_id=so_trn.sales_ordertrn_id'
			,'left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id',
			'left join tbl_grn as grn on grn.grn_id=gt.grn_id');
		$hOrder = "trn.qctrn_id desc";
		//$hGroupby = array("trn.product_id");
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['qc_no'];
			$row_data[] = date("d-M-Y",strtotime($row['qc_date']));
			$row_data[] = $row['grn_no'];
			$row_data[] = date("d-M-Y",strtotime($row['grn_date']));
			$row_data[] = $row['product_name'];
			$row_data[] = $row['po_req_no'];
			$row_data[] = $row['sales_order_no'];
			$row_data[] = $row['qc_accepted'];
			$row_data[] = $row['qc_rejected'];
			$row_data[] = $row['qty_reprocess'];
			
			$view_attach_doc = '<button class="btn btn-xs btn-info" data-original-title="View Attached Document" data-toggle="tooltip" data-placement="top" onClick="view_attach_document('.$row['qc_id'].',\''.$row['qc_no'].'\')"><i class="fa fa-eye"></i></button>';

			$print_btn='';
			if($row['ref_type']=='2' && $row['product_type']=='3'){
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 24 AND approve_status = 1 AND status = 0 ORDER BY priority");

				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['qctrn_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
						$quotation_link .= "'".$_SERVER['SERVER_NAME'].ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['quotation_id'].'?'.time()."'";
					}
				}
			}else{
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 25 AND approve_status = 1 AND status = 0 ORDER BY priority");

				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['qctrn_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
						$quotation_link .= "'".$_SERVER['SERVER_NAME'].ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['quotation_id'].'?'.time()."'";
					}
				}
			}
			

			$row_data[] = $view_attach_doc.' '.$print_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "load_attach_document") {
	    $appData = array();
	    $i=1;
	    $where='';
	    if($POST['qc_id']){
	        $where = ' and attach.qc_id='.$POST['qc_id'];
	    }
	    // if($branch_id){
	    //     $where .= check_branch('opportun',$branch_id);
	    // }
	    $aColumns = array('attach.qc_attach_id', 'attach.qc_id','attach.qc_file');
	    $sIndexColumn = "attach.qc_attach_id";
	    $isWhere = array("attach.qc_attch_status=0 and attach.company_id in (0,$_SESSION[company_id])".$where);
	    $sTable = "tbl_qc_attch as attach";            
	    $isJOIN = array('');
	    $hOrder = "attach.qc_attach_id desc";
	    include('../../include/pagging.php');
	    $appData = array();
	    $id=1;
	    foreach($sqlReturn as $row) {
	        $row_data = array();
	        $row_data[] = $row['sr']; 
	        $row_data[] = '<a href="'.ROOT.QC_FILE_VWING.$row['qc_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>'; 
	    
	        $appData[] = $row_data;
	        $id++;
	    }
	    $output['aaData'] = $appData;
	    echo json_encode( $output );
	}
?>