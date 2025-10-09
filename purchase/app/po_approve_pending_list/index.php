<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
//{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
//	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PURCHASE_ORDER_APPROVAL
		]);
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';
		$where.="  and po_type_status=1";

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('po', $branch_id);
		$where.=" $where_db and po.company_id=".$_SESSION['company_id'];
		switch($POST['po_approval_status']){
			case "0":
			$where.="  and po.po_approval_status in (0)";
			break;
			
			case "1":
			$where.="  and po.po_approval_status in (3)";
			break;

			default:
			$where.="";
		}
		//echo $_SESSION['page'];
			/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);*/

			
			$where.="  and purchaseorder_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND purchaseorder_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('purchaseorder_id','purchaseorder_no','l.l_name','city.city_name','bms.branch_name','purchaseorder_date','g_total','paid_amount','status','purchase_status','po.cdate','po.userid','po.po_type_status','po.po_req_status','po_approval_status','us.user_name');
			$sIndexColumn = "purchaseorder_id";
			$isWhere = array("status = 0".$where);
			$sTable = "tbl_purchaseorder as po";
			$isJOIN = array('left join tbl_ledger as l on po.vender_id=l.l_id','left join  city_mst city on l.cityid=city.cityid','left join branch_mst as bms on bms.branch_id=po.branch_id','left join users as us on us.user_id=po.userid');
			$hOrder = "po.purchaseorder_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['purchaseorder_no'];
				$row_data[] = date('d M, Y',strtotime($row['purchaseorder_date']));
				$row_data[] = $row["l_name"];
				$row_data[] = $row['branch_name'];
				$row_data[] = $row['city_name'];
				$row_data[] = round($row['g_total']);
				$row_data[] = $row['user_name'];
				
				/* if($row['po_approval_status']=='1'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}
				else{
					$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
				} */
				
				$poprint='';$delete='';$edit='';$cancel_po_btn='';$po_app_btn='';
				//PO Approval Button To admin
				
				if(in_array(PURCHASE_ORDER_APPROVAL,$bulkAccessArray)){
					if($row['po_approval_status']=="3"){
						$po_app_btn='<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status('.$row['purchaseorder_id'].',0, \''.$row['purchaseorder_no'].'\')"><i class="fa fa-check"></i></button>';
					}
					else{
						$po_app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status('.$row['purchaseorder_id'].',1, \''.$row['purchaseorder_no'].'\')"><i class="fa fa-check"></i></button>';
					}
				}
				
				
				
				
				
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 4 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$poprint='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['purchaseorder_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>&nbsp;';
					}
				}
				
				if($row['po_approval_status']=='3'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}
				else if($row['po_approval_status']=='2'){
					$disapproved_reason = get_po_disapproved_reason($dbcon,'tbl_purchaseorder_aprv_log','approve_remark',$row['purchaseorder_id'],'approve_status','2','purchaseorder_aprv_id');
					$row_data[] = '<button class="btn btn-xs btn-danger" title="'.$disapproved_reason.'" >Disapproved</button>';
				} 
				else{
					$row_data[] = '<button class="btn btn-xs btn-warning">Approval Pending</button>';
				}	
				$row_data[] = $poprint.' '.$po_app_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		
?>