<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
			PO_SHORTCLOSE_LIST
		]);

		$companyConfiguration=getCompanyConfiguration($dbcon);
		$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
		$pro_search=explode(",", $purchase_pro_search);

		$where="";

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('sht', $branch_id);
		$where.=" $where_db ";

		$where_company=check_company('sht');

		$where.=" $where_company";

		//$where_user=check_user('sht');

		//$where.=" $where_user";
		
		/*trn.product_qty>(select IFNULL(sum(product_qty+tolerance),0) as qty  from tbl_grn_trn as chtrn 
		left join tbl_grn as gn on gn.grn_id=chtrn.grn_id
		where chtrn.grn_trn_status=0 and gn.ref_type=2 and chtrn.purchaseorder_id=trn.purchaseorder_id and trn.product_id=chtrn.product_id)*/
		
		$appData = array();
		$i=1;
		$aColumns = array('sht.log_id','sht.po_no','sht.product_id','tc.cat_name','sht.short_close_qty','pro.product_name','pro.product_icode', 'dr.drawing_number', 'pro.product_alias_name','sht.short_close_reason','sht.date', 'bms.branch_name','user.user_name','sht.user_id','sht.aproove_status','sht.po_trn_id','sht.po_id','unit.unit_name');
		$sIndexColumn = "sht.log_id";
		$isWhere = array("sht.short_close_status=0 and aproove_status=0".$where);
		$sTable = "tbl_log_po_short_close as sht";			
		$isJOIN = array('left join product_mst as pro on pro.product_id=sht.product_id','left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id','left join tbl_category as tc on pro.product_category=tc.cat_id','left join tbl_purchaseorder as po on po.purchaseorder_id=sht.po_id','left join branch_mst as bms on bms.branch_id=sht.branch_id','left join unit_mst as unit on unit.unitid=sht.unit_id','left join users as user on user.user_id=sht.user_id');
		$hOrder = "sht.log_id desc";
		//$hGroupby = array("trn.product_id");
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		
		$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
		$set_conf=brp_mysqli_fetch_assoc($dbcon->query($setconf));
		$purchase_pro_search = $set_conf['purchase_pro_search'];
		$pro_search=explode(",", $purchase_pro_search);
		
		foreach($sqlReturn as $row) {
			
			if(in_array('drawing',$pro_search)){
	            $drawing_number = " -- (".$row['drawing_number'].")";
	        }
	        if(in_array('item',$pro_search)){
	            $item_code = " -- (".$row['product_icode'].")";
	        }
	        if(in_array('alias',$pro_search)){
	            $alias = " -- (".$row['product_alias_name'].")";
	        }
			
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['po_no'];
			$row_data[] = $row['product_name']." ".$drawing_number." ".$item_code." ".$alias;
			$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$row_data[] = date('d-m-Y',strtotime($row['date']));
			$row_data[] = number_format($row['short_close_qty'],4,".","")." ".$row['unit_name'];
			$row_data[] = $row['short_close_reason'];
			$row_data[] = find_user_name($dbcon,$row['user_id']);
			
			if($row['aproove_status'] ==1){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
			}else{
				$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Approve Pending</div>';
			}
			
			$short_close_po_approove = '';
			if($row['aproove_status'] ==1){
				$short_close_po_approove='<button class="btn btn-xs btn-success" data-original-title="PO Short Close Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_shortclose_approval_status('.$row['po_id'].','.$row['po_trn_id'].',0, \''.$row['po_no'].'\')"><i class="fa fa-check"></i></button>';
			}else{
				$short_close_po_approove='<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_shortclose_approval_status('.$row['po_id'].','.$row['po_trn_id'].',1, \''.$row['po_no'].'\')"><i class="fa fa-check"></i></button>';
			}
			$row_data[] = $short_close_po_approove;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "load_purchase_hist_datatable") {
		
		$where='';
		$where.="   log.purchaseorder_trn_id=".$POST['po_trn_id'];
		
		$appData = array();
		$i=1;
		$aColumns = array('log.po_shortclose_aprv_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
		$sIndexColumn = "log.po_shortclose_aprv_id";
		$isWhere = array(" ".$where." ");
		$sTable = "tbl_po_shortclose_aprv_log as log";			
		$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
		$hOrder = "log.po_shortclose_aprv_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['user_name'];
			
			if($row['approve_status']=='1'){
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
	else if(strtolower($POST['mode']) == "add_po_apprv_hist") {
		$companyConfiguration 			= getCompanyConfiguration($dbcon);

		$info1['approve_remark']		= $POST['approve_remark'];
		$info1['approve_status']		= $POST['approve_status'];
		$info1['purchaseorder_id']		= $POST['purchase_order_id'];
		$info1['purchaseorder_trn_id']	= $POST['po_trn_id'];	
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];
		$info1['cdate']					= date('Y-m-d H:i:s');

		$inserid=add_record("tbl_po_shortclose_aprv_log", $info1, $dbcon);
		
		// Update For Po Trn table 
		if($POST['approve_status'] == 1){
			$inshort['used_status'] = 1;
			$inshort['shortclose_status'] = 0;
			
			$req_q = "select * from tbl_request_product where rp_req_type='short_close' and purchaseordertrn_id=".$POST['po_trn_id'];
			$req_e = $dbcon->query($req_q);
			$cnt = brp_mysqli_num_rows($req_e);
			if( $cnt > 0){
				
			}else{
				$query = "select po_ref_id from tbl_purchaseordertrn where purchaseordertrn_id=".$POST['po_trn_id'];

				$que_e = $dbcon->query($query);
				if($cnt = brp_mysqli_num_rows($que_e)>0){
					$que = "select reqs.*,ptrn.branch_id as bnch_id,(select sum(used_qty) from tbl_purchaseorder_req_trn as sr where sr.purchaseordertrn_req_status=0 and sr.rp_id=rtrn.rp_id) as re_conv_qty,(select sum(product_conv_qty) from tbl_grn_sub_trn as inv where inv.status=0 and inv.rp_id = rtrn.rp_id)  as inw_conv_qty from tbl_purchaseordertrn as ptrn 
						left join tbl_purchaseorder_req_trn as rtrn on rtrn.purchaseordertrn_id = ptrn.purchaseordertrn_id
						left join tbl_request_product as reqs on reqs.rp_id = rtrn.rp_id
						where rtrn.purchaseordertrn_req_status=0 and ptrn.purchaseordertrn_id =".$POST['po_trn_id'];

					$que_ex = $dbcon->query($que);

					while($row = brp_mysqli_fetch_array($que_ex)){
						$pending_qty = $row['re_conv_qty']-$row['inw_conv_qty'];
						$branch_id	 = $row['bnch_id'];
						if($pending_qty>0){
							$indenttrn['indent_no']			= load_common_no($dbcon,17);
						
							$query_invoicetype 	= $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=17 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
							
							$indenttrn['indent_date']		= date('Y-m-d');
							$indenttrn['rp_req_date']		= date('Y-m-d');
							$indenttrn['rp_req_qty']		= $pending_qty;
							$indenttrn['purchase_unit']		= $row['purchase_unit'];
							$indenttrn['rp_pid']			= $row['rp_pid'];
							$indenttrn['branch_id']			= $row['branch_id'];
							$indenttrn['indent_status']		= 1;
							$indenttrn['rp_req_type']		= "short_close";
							$indenttrn['rp_po_req_no']		= $row['rp_po_req_no'];
							$indenttrn['rp_process_req_no']	= $row['rp_process_req_no'];
							$indenttrn['sr_no']				= $row['sr_no'];
							$indenttrn['sp_id']				= $row['sp_id'];
							$indenttrn['rp_req_no']			= $row['rp_req_no'];
							$indenttrn['req_qty_one']		= $row['req_qty_one'];
							$indenttrn['rp_po_qty']			= $pending_qty;	
							$indenttrn['in_process_qty']	= $row['in_process_qty'];				
							$indenttrn['out_process_qty']	= $row['out_process_qty'];
							$indenttrn['row_cnt']		 	= $row['row_cnt'] ;
							$indenttrn['process_unit']		= $row['process_unit'];


							$indenttrn['perent_id']			= $row['parent_id'];
							$indenttrn['reserve_stock']		= $row['reserve_stock'];
							$indenttrn['main_request']		= $row['main_request'];
							$indenttrn['pre_trn_id']		= $row['pre_trn_id'];
							$indenttrn['purchaseordertrn_id'] = $POST['po_trn_id'];

							$indenttrn['job_card_no']		= $row['job_card_no'];
							$indenttrn['job_card_date']		= $row['job_card_date'];
							$indenttrn['job_card_status']   = $row['job_card_status']; 
							$indenttrn['sales_order_trn_id']= $row['sales_order_trn_id'];
							$indenttrn['product_version']	= $row['product_version'];
							$indenttrn['work_order_no']		= $row['work_order_no'];
							$indenttrn['work_order_date']	= $row['work_order_date'];
							$indenttrn['work_order_status'] = $row['work_order_status'];
							$indenttrn['bom_id']			= $row['bom_id'];
							$indenttrn['approval_status']	= $row['approval_status'];
							$indenttrn['jobwork_type']	 	= $row['jobwork_type'];
							$indenttrn['customer_id']	 	= $row['customer_id'];

							

							$indenttrn['cdate']				= date("Y-m-d H:i:s");;
							$indenttrn['user_id']			= $_SESSION['user_id'];
							$indenttrn['company_id']		= $_SESSION['company_id'];
							
							$indenttid = add_record('tbl_request_product', $indenttrn, $dbcon, $branch_id);


							if($companyConfiguration['automatic_approval_indent']==1){
								$approve_no=load_common_no($dbcon,18);
								update_common_no($dbcon,18);
									
								$info['approve_no']					= $approve_no;
								$info['approve_date']				= date("Y-m-d");
								$info['rp_id']						= $indenttid;
								$info['approve_qty']				= $pending_qty;
								$info['approve_unit']				= $row['purchase_unit'];
								$info['delivery_date']				= date("Y-m-d H:i:s");
								$info['quotation_requirement']		= 0;
								$info['cdate']						= date("Y-m-d H:i:s");
								$info['user_id']					= $_SESSION['user_id'];
								$info['company_id']					= $_SESSION['company_id'];
									
								$inserpoid=add_record('approve_indent', $info, $dbcon, $branch_id);
								
								if($pending_qty==$pending_qty){

									$inftrn['indent_status'] = 3;
									$updatetrnid=update_record('tbl_request_product', $inftrn,"rp_id=".$indenttid , $dbcon, $branch_id);
								}
								
								$query_used="select * from tbl_request_product as rpro
										where rp_id=".$indenttid." and company_id = '".$_SESSION['company_id']."' ";
								$rel_used=brp_mysqli_fetch_assoc($dbcon->query($query_used));	
								
									
								$rate=get_pro_field($dbcon,$rel_used['rp_pid'],'product_purchase_rate');
							
								$total=$pending_qty*$rate;
								
								$infpotrn['purchaseorder_id']	= '0';
								$infpotrn['product_type']		= '';
								$infpotrn['product_id']			= $rel_used['rp_pid'];
								$infpotrn['product_qty']		= $pending_qty;
								$infpotrn['product_rate']		= $rate;
								$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$rel_used['rp_pid'],'product_hsn');
								//$infpotrn['unit_id']			= get_pro_field($dbcon,$pr_id,'product_base_unit');
								$infpotrn['unit_id']			= $row['purchase_unit'];
								$infpotrn['product_amount']		= $total;
								$infpotrn['total']				= $total;
								$infpotrn['parent_pro']			= 0;
								$infpotrn['main_pro_status']	= 1;//Requested products
								$infpotrn['user_id']			= $_SESSION['user_id'];
								$infpotrn['po_ref_id']			= $indenttid;
								$infpotrn['po_ref_type']		= '0';
								$infpotrn['po_bom_id']			= '';
								$infpotrn['po_bom_trn_id']		= '';
								$infpotrn['mdate']				= date('Y-m-d');
								$infpotrn['company_id']			= $_SESSION['company_id'];
									
									
								if($info['quotation_requirement']==0){
									$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon, $branch_id);
								}
							}
						}
					}
				}
			}
		}else{
			$inshort['used_status'] = 0;
			$inshort['shortclose_status'] = 0;
		}
		
		$updateid=update_record("tbl_purchaseordertrn", $inshort, "purchaseordertrn_id=".$POST['po_trn_id'], $dbcon);
		
		// Aproove For Short Close Log Table
		$infoshort['aproove_status']		= $POST['approve_status'];
		
		$updateid=update_record("tbl_log_po_short_close", $infoshort, "po_trn_id=".$POST['po_trn_id'], $dbcon);
	}
	else if(strtolower($POST['mode']) == "load_party_purchase_dtl") {
		$qt_qry="select qt.*,country_name,state_name,city_name,led.company_name,led.cust_mobile,led.m_address,led.gst_no from tbl_purchaseorder as qt
		left join tbl_ledger as led on led.l_id=qt.vender_id
		left join country_mst as country on country.countryid=led.countryid
		left join state_mst as state on state.stateid=led.stateid
		left join city_mst as city on city.cityid=led.cityid
		where qt.purchaseorder_id=".$POST['purchase_order_id'];
		$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($qt_qry));
		
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
		$str.='</table></div>
		<hr/>
		';
		
		$qt_rel['mod_po_comp_div_sec'] = $str;
		
		echo json_encode($qt_rel);
	}
	
?>
