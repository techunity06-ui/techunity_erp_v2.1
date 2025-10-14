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
			$where.="  and po.po_approval_status in (2,4)";
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
					
					$po_app_btn='<button class="btn btn-xs btn-success" data-original-title="PO Approved Log" data-toggle="tooltip" data-placement="top" onclick="approve_log('.$row['purchaseorder_id'].',0, \''.$row['purchaseorder_no'].'\','.$row['po_approval_status'].')"><i class="fa fa-exclamation-triangle"></i></button>';
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
				
				if($row['po_approval_status']=='2'){
					$disapproved_reason = get_po_disapproved_reason($dbcon,'tbl_purchaseorder_aprv_log','approve_remark',$row['purchaseorder_id'],'approve_status','2','purchaseorder_aprv_id');
					$row_data[] = '<button class="btn btn-xs btn-danger" title="'.$disapproved_reason.'" >Disapproved</button>';
				} 
				else{
					$row_data[] = '<button class="btn btn-xs btn-danger">Finance-Disapproved</button>';
				}	
				$row_data[] = $poprint.' '.$po_app_btn;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "load_purchase_finhist_datatable") {

			$where='';
			$where.="   log.purchaseorder_id=".$POST['purchase_order_id'];

			$appData = array();
			$i=1;
			$aColumns = array('log.po_finance_approve_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
			$sIndexColumn = "log.po_finance_approve_id";
			$isWhere = array(" ".$where." ");
			$sTable = "tbl_purchaseorder_finance_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.po_finance_approve_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['user_name'];

				if($row['approve_status']=='1'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
				}
				else if($row['approve_status']=='4'){
					$row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Finance Disapproved</div>';
				}
				else{
					$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Approve Pending</div>';
				}

				$row_data[] = nl2br($row['approve_remark']);
				$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "load_purchase_hist_datatable") {

			$where='';
			$where.="   log.purchaseorder_id=".$POST['purchase_order_id'];

			$appData = array();
			$i=1;
			$aColumns = array('log.purchaseorder_aprv_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
			$sIndexColumn = "log.purchaseorder_aprv_id";
			$isWhere = array(" ".$where." ");
			$sTable = "tbl_purchaseorder_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.purchaseorder_aprv_id desc";
			include($include.'/pagging.php');
			//echo $sQuery;
			//exit;
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['user_name'];

				if($row['approve_status']=='3'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
				}else if($row['approve_status']=='2'){
					$row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Disapproved</div>';
				}else{
					$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Approve Pending</div>';
				}

				$row_data[] = nl2br($row['approve_remark']);
				$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "load_party_purchase_dtl") {
			$qt_qry="select qt.*,country_name,state_name,city_name,led.company_name,led.cust_mobile,led.m_address,led.gst_no from tbl_purchaseorder as qt
			left join tbl_ledger as led on led.l_id=qt.vender_id
			left join country_mst as country on country.countryid=led.countryid
			left join state_mst as state on state.stateid=led.stateid
			left join city_mst as city on city.cityid=led.cityid
			where qt.purchaseorder_id=".$POST['purchase_order_id'];
			$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($qt_qry));

			$getspecialConfiguration=getspecialConfiguration($dbcon);

			if($getspecialConfiguration['rb_auto_permission']==1){
				$sales_order_no = "select trn.purchaseordertrn_id, trn.po_ref_id, req.sp_id from tbl_purchaseordertrn as trn
				left join tbl_request_product as req on req.rp_id = trn.po_ref_id
				where purchaseorder_id=".$POST['purchase_order_id']." group by req.sp_id";
				//var_dump($sales_order_no);
				$sales_order_no_e=$dbcon->query($sales_order_no);
				$sales_no = "";$client_name="";
				while($rel = brp_mysqli_fetch_array($sales_order_no_e)){

					//$sales_order_trn_id=get_so_no_po_ref($dbcon,$rel['perent_id']);

					$q = "SELECT sales_order_trn_id FROM tbl_request_product WHERE sp_id='" . $rel['sp_id'] . "' AND main_request=1 GROUP BY sp_id";
					$e = $dbcon->query($q);
					$r = brp_mysqli_fetch_array($e);

					$so_no = "select so.sales_order_no,led.l_name from tbl_sales_ordertrn as strn
					left join tbl_sales_order as so on so.sales_order_id = strn.sales_order_id
					left join tbl_ledger as led on led.l_id = so.cust_id
					where strn.sales_ordertrn_id=".$r['sales_order_trn_id'];
					
					//var_dump($so_no);
					$so_no_e = $dbcon->query($so_no);
					$so_no_r = brp_mysqli_fetch_array($so_no_e);
					$sales_no .= $so_no_r['sales_order_no']."<br>";
					$client_name .= $so_no_r['l_name']."<br>";
				}
			}
		//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Company Name:</strong> '.$qt_rel['company_name'].'</td>
			<td><strong>Contact No.:</strong> '.$qt_rel['cust_mobile'].'</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Address:</strong> '.$qt_rel['m_address'].'</td>
			<td><strong>GST No.:</strong> '.$qt_rel['gst_no'].'</td>
			</tr>
			<!--<tr>
			<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
			<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
			<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
			</tr>-->
			<tr>
			<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
			<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
			<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
			</tr>
			<tr>
			<td><strong>Purchase order No:</strong> '.$qt_rel['purchaseorder_no'].'</td>
			<td><strong>Purchase Order Date:</strong> '.date("d-M-Y",strtotime($qt_rel["purchaseorder_date"])).'</td>
			<td><strong>Purchase Order Amount:</strong> '.$qt_rel['g_total'].'</td>
			</tr>
			';
			
			if($getspecialConfiguration['rb_auto_permission']==1){
				$str.='<tr>
					<td><strong>Sales Order No :</strong> '.$sales_no.'</td>
					<td colspan="2"><strong>Client Name :</strong> '.$client_name.'</td>
				</tr>
				';
			}
			$str.='</table></div>
			<hr/>
			';
			
			$qt_rel['mod_po_comp_div_sec'] = $str;
			
			echo json_encode($qt_rel);
		}
		else if(strtolower($POST['mode']) == "load_pro_purchase_dtl") {
			$qt_qry="select trn.*,pmst.product_name,unit.unit_name,ctrn.price,ctrn.rate_tolerance,ctrn.discount_percentage,tc.cat_name from tbl_purchaseordertrn as trn 
			left join product_mst as pmst on pmst.product_id = trn.product_id
			left join tbl_category as tc on pmst.product_category=tc.cat_id
			left join unit_mst as unit on unit.unitid = trn.rate_unit
			left join tbl_purchasecardtrn as ctrn on ctrn.purchasecardtrn_id=trn.purchasecardtrn_id
			where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=".$POST['purchase_order_id'];
			$qt_rel=$dbcon->query($qt_qry);
		//Party PO Details Table View
			$str='';
			$str.='<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
				<th class="text-center" width="6%">Product Type</th>
				<th class="text-center" width="12%">Product Name</th>
				<th class="text-center" width="10%">Product Category</th>
				<th class="text-center" width="6%">HSN Code</th>
				<th class="text-center" width="6%">Qty</th>
				<th class="text-center" width="10%">Rate</th>
				<th class="text-center" width="10%">Discount</th>
				<th class="text-center" width="8%">Amount</th>
				<th class="text-center" width="8%">Action</th>
			</tr>';
			while($row=brp_mysqli_fetch_array($qt_rel)){

				$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				
				if($row['unit_id']===$row['rate_unit']){
					$sqty=$row['product_qty'];
				}else{
					$sqty=$row['product_conv_qty'];
				}

				$over_tol = '';
				if($row['price'] != ''){
					if($row['product_rate']>$row['price']){
						$tole_rate = ($row['price']*$row['rate_tolerance'])/100;
						$tol_rate  = $row['price']+$tole_rate;
						if($row['product_rate']>$tol_rate){
							$over_tol .= "<strong><span style='color:red'>Over Tolerance Rate</span></strong>";
						} 
					}	
				}

				$ove_disc = '';
				if($row['discount_percentage'] != ''){
					if($row['discount_percentage'] > $row['discount_per']){
						$ove_disc = "<strong><span style='color:red'>Less Discount As Per Minimum Discount</span></strong>";
					}
				}

				$str .='<tr>
					<td>'.get_pro_type_name($row['product_type']).'</td>
					<td>'.$row['product_name'].'</td>
					<td>'.$cat_name.'</td>
					<td>'.$row['product_hsn_code'].'</td>
					<td>'.number_format($sqty,4,'.','').' '.$row['unit_name'].'</td>
					<td>'.number_format($row['product_rate'],2,'.','').' <br> '.$over_tol.'</td>
					<td>'.$row['product_discount'].' ('.$row['discount_per'].'%)<br>'.$ove_disc.'</td>
					<td>'.$row['product_amount'].'</td>
					<td>
						<button type="button" class="btn btn-round btn-primary btn-xs" onclick="delivery_detail('.$row['purchaseordertrn_id'].');" ><i class="fa fa-eye" aria-hidden="true"></i> </button>
					</td>
				</tr>';
			}
			$str.='</table>
			<hr/>
			';
			
			$res['mod_po_pro_div_sec'] = $str;
			
			echo json_encode($res);
		}
?>