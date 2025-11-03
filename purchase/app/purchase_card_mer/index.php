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
				PURCHASE_CARD_VIEW,PURCHASE_CARD_UPDATE,PURCHASE_CARD_DELETE,PURCHASE_CARD_APPROVE,PURCHASE_CARD_ACTIVE
			]);

			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];

			$where='';
			if($POST['card_type'] == 0){
				$where.=" card.card_type=0";
			}else{
				$where .=" card.card_type=1";
			}

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('card', $branch_id);
			$where.=" $where_db ";
			
			$where_company=check_company('card');

			$where.=" $where_company";

			//$where_user=check_user('card');

			//$where.=" $where_user";

			$where.="  and card.pur_card_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND card.pur_card_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			
			$appData = array();
			$i=1;
			$aColumns = array('card.party_purchase_id','card.pur_card_no','pro.product_name','led.l_name','card.pur_card_date','card.valid_date','card.effective_date','bms.branch_name','card.is_aproove','card.is_active','card.card_type');
			$sIndexColumn = "card.party_purchase_id";
			$isWhere = array('card_status=0',$where);
			$sTable = "tbl_product_party_purchase as card";
			$isJOIN = array('left join product_mst as pro on pro.product_id=card.product_id','left join tbl_ledger as led on led.l_id=card.party_id','left join branch_mst as bms on bms.branch_id=card.branch_id');
			$hOrder = "card.party_purchase_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				
				$row_data[] = $row['pur_card_no'];
				$row_data[] = $row['pur_card_date'];
				if($POST['card_type'] ==0){
					$row_data[] = $row['l_name'];
				}else{
					$row_data[] = $row['product_name'];
				}
				
				/*$row_data[] = $row['valid_date'];
				$row_data[] = $row['effective_date'];*/
				//$row_data[] = $row['branch_name'];
				
				$edit = '';$delete = '';$is_approove='';$is_active='';$lab='';
				if($row['is_active'] == 1){
					$lab = "<div class='external-event label label-error ui-draggable' style='cursor:auto; background-color: #d9534f !important;'>in active</div>";
				}else{
					$lab = "<div class='external-event label label-success ui-draggable' style='cursor:auto;'>active</div>";	
				}				

				$row_data[] = $lab;
				if(in_array(PURCHASE_CARD_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'pocardedit/'.$row['party_purchase_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				
				

				if($row['is_aproove']==0){
					if(in_array(PURCHASE_CARD_APPROVE,$bulkAccessArray)){
						$is_approove = '<button class="btn btn-xs btn-warning" data-original-title="Approve PO Card" data-toggle="tooltip" data-placement="top" onclick="card_aprooval_status('."'$row[party_purchase_id]'".','."'$row[card_type]'".','."'$row[pur_card_no]'".')"><i class="fa fa-check"></i></button>';
					}
					if(in_array(PURCHASE_CARD_DELETE,$bulkAccessArray)){
						$delete = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_po_card('.$row['party_purchase_id'].');" ><i class="fa fa-trash-o"></i></button>';	
					}
				}else{
					if(in_array(PURCHASE_CARD_APPROVE,$bulkAccessArray)){
						$is_approove = '<button class="btn btn-xs btn-success" data-original-title="Approve PO Card" data-toggle="tooltip" data-placement="top" onclick="card_aprooval_status('."'$row[party_purchase_id]'".','."'$row[card_type]'".','."'$row[pur_card_no]'".')"><i class="fa fa-check"></i></button>';
					}
				}
				
				if(in_array(PURCHASE_CARD_ACTIVE,$bulkAccessArray)){
					$is_active = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="active_status_change('."'$row[party_purchase_id]'".','."'$row[is_active]'".');" ><i class="fa fa-times"></i></button>';
				}
				
				$row_data[] = $edit." ".$delete." ".$is_approove." ".$is_active;
				$row_data[] = "";
				//print_r($row_data);
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "fetch_item") {
		
			$where='';
			if($POST['card_id'] != ""){
				$where.=" trncard.purchasecardtrn_status=0 and trncard.party_purchase_id=".$POST['card_id'];
			}else{
				$where .=" trncard.purchasecardtrn_status=3";
			}
			
			$appData = array();
			$i=1;
			$aColumns = array('pro.product_name','led.l_name','trncard.rate_tolerance','trncard.discount_percentage','trncard.affected_date','trncard.valid_date','trncard.quotation_date','trncard.quotation_number','trncard.price','trncard.grate','trncard.lead_time','trncard.item_make','trncard.purchasecardtrn_id','cur.currency_name','unit.unit_name');
			$sIndexColumn = "trncard.purchasecardtrn_id";
			$isWhere = array($where);
			$sTable = "tbl_purchasecardtrn as trncard";
			$isJOIN = array('left join product_mst as pro on pro.product_id=trncard.product_id','left join tbl_ledger as led on led.l_id=trncard.vendor_id','left join currency_mst as cur on cur.currencyid=trncard.currency_id','left join unit_mst as unit on unit.unitid=trncard.unit_id');
			$hOrder = "trncard.purchasecardtrn_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				if($POST['card_type'] == 0){
					$row_data[] = $row['product_name']; 
				}else{
					$row_data[] = $row['l_name'];
				}
				
				if($row['quotation_date'] != '1970-01-01' && $row['quotation_date'] != '0000-00-00'){
					$row['quotation_date'] = date('d-m-Y',strtotime($row['quotation_date']));
				}else{
					$row['quotation_date']='';
				}
				
				if($row['affected_date'] != '1970-01-01' && $row['affected_date'] != '0000-00-00'){
					$row['affected_date'] = date('d-m-Y',strtotime($row['affected_date']));
				}else{
					$row['affected_date']='';
				}
				
				if($row['valid_date'] != '1970-01-01' && $row['affected_date'] != '0000-00-00' ){
					$row['valid_date'] =date('d-m-Y',strtotime($row['valid_date']));
				}else{
					$row['valid_date']='';
				}

				//$row_data[] = $row['currency_name'];
				$row_data[] = $row['rate_tolerance'];
				$row_data[] = $row['discount_percentage'];
				$row_data[] = $row['affected_date'];
				$row_data[] = $row['valid_date'];
				$row_data[] = $row['quotation_date'];
				//$row_data[] = "";
				$row_data[] = $row['price'];
				//$row_data[] = $row['grate'];
				//$row_data[] = $row['lead_time'];
				$row_data[] = $row['quotation_number'];
				$row_data[] = $row['unit_name'];
				
				$edit = '';$delete = '';
				
				$edit = '<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$row['purchasecardtrn_id'].');" ><i class="fa fa-pencil"></i></button>';
				
				$delete = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$row['purchasecardtrn_id'].');" ><i class="fa fa-times"></i></button>';
				
				$row_data[] = $edit." ".$delete;
				$row_data[] = "";
				//print_r($row_data);
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
			
			$info['card_type']				= $POST['card_type'];
			$info['pur_card_no']			= $POST['pur_card_no'];
			if($POST['card_type'] ==0){
				$info['party_id']				= $POST['vender_id'];
				$info['product_id']				= 0;
			}else{
				$info['product_id']				= $POST['product_id'];
				$info['party_id']				= 0;
			}
			$info['pur_card_date']			= date('Y-m-d',strtotime($POST['pur_card_date']));
			//$info['valid_date']				= date('Y-m-d',strtotime($POST['valid_date']));
			//$info['effective_date']			= date('Y-m-d',strtotime($POST['effective_date']));
			
            
			$info['cdate']					= date("Y-m-d H:i:s");
			$info['user_id']				= $_SESSION['user_id'];
			$info['company_id']				= $_SESSION['company_id'];

			
			$inserpoid=add_record('tbl_product_party_purchase', $info, $dbcon, $branch_id);
			
			if($inserpoid){
				$inftrn['party_purchase_id'] = $inserpoid;
				$inftrn['purchasecardtrn_status'] = 0;
				$updatetrnid=update_record('tbl_purchasecardtrn', $inftrn,"user_id=".$_SESSION['user_id']." and party_purchase_id=0 and purchasecardtrn_status=3" , $dbcon);
			}
			if($inserpoid)
			{	
				$arr['msg']="1";							
			}
			else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
			$today_date = date('Y-m-d');
			$valid_date = date('Y-m-d',strtotime($POST['valid_date']));
			$affected_date = date('Y-m-d',strtotime($POST['date']));
			if($POST['card_type'] == 0){
				$info1['product_id'] 		= $POST['vend_product_id'];
				$info1['vendor_id']	 		= $POST['vender_id'];
			}else{
				$info1['vendor_id']	 		= $POST['prod_id_vend'];
				$info1['product_id'] 		= $POST['product_id'];
			}

			$query = "select * from tbl_purchasecardtrn where valid_date>='$valid_date' and purchasecardtrn_status=0 and product_id=".$info1['product_id']." and vendor_id=".$info1['vendor_id']." and company_id=".$_SESSION['company_id']." and unit_id=".$POST['unit_id'];
			
			$q = $dbcon -> query($query);
			$cnt = brp_mysqli_num_rows($q);
			if($cnt>0 && $POST['edit_id'] == ""){
				$arr['msg']="-1";
			}else{
				$info1['currency_id']			= $POST['currency_id'];
				$info1['price']					= $POST['rate'];
				$info1['grate']					= $POST['g_rate'];
				$info1['rate_tolerance']		= $POST['rate_tolerance'];
				$info1['discount_percentage']	= $POST['disc_per'];
				$info1['affected_date']			= date('Y-m-d',strtotime($POST['date']));
				$info1['valid_date']			= date('Y-m-d',strtotime($POST['valid_date']));
				$info1['quotation_number']		= $POST['qtn_no'];
				$info1['quotation_date']		= date('Y-m-d',strtotime($POST['quot_date']));
				$info1['unit_id']				= $POST['unit_id'];
				$info1['lead_time']				= $POST['lead_time'];
				$info1['item_make']				= $POST['item_make'];
				$info1['user_id']				= $_SESSION['user_id'];
				$info1['company_id']			= $_SESSION['company_id'];
				$info1['cdate']					= date("Y-m-d H:i:s");
				
				$table='tbl_purchasecardtrn';$tableid='purchasecardtrn_id';
				if(!empty($POST['card_id']))
				{
					$info1['party_purchase_id']= $POST['card_id'];
				}else{
					$info1['purchasecardtrn_status']	= 3;
				}
				
				if(empty($POST['edit_id']))
				{
					$inserid=add_record($table, $info1, $dbcon);
				}
				else
				{
					$inserid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				}


				if($inserid){
					$arr['msg']="1";
				}else{
					$arr['msg']="0";
				}
			}
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("select * from tbl_purchasecardtrn where purchasecardtrn_id= '$POST[id]'");
			$r = $q->fetch_assoc();

			if($r['quotation_date'] != '1970-01-01' && $r['quotation_date'] != '0000-00-00'){
				$r['quotation_date'] = date('d-m-Y',strtotime($r['quotation_date']));
			}else{
				$r['quotation_date']='';
			}
			
			if($r['affected_date'] != '1970-01-01' && $r['affected_date'] != '0000-00-00'){
				$r['affected_date'] = date('d-m-Y',strtotime($r['affected_date']));
			}else{
				$r['affected_date']='';
			}
			
			if($r['valid_date'] != '1970-01-01' && $r['affected_date'] != '0000-00-00' ){
				$r['valid_date'] =date('d-m-Y',strtotime($r['valid_date']));
			}else{
				$r['valid_date']='';
			}
			
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$info['purchasecardtrn_status'] = 2;
			$updateid=update_record('tbl_purchasecardtrn', $info,'purchasecardtrn_id'."=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			//var_dump($query);
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
	
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$_POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "edit") {
			 	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
				$info['card_type']				= $POST['card_type'];
				$info['pur_card_no']			= $POST['pur_card_no'];
				if($POST['card_type'] ==0){
					$info['party_id']			= $POST['vender_id'];
					$info['product_id']			= 0;
				}else{
					$info['product_id']			= $POST['product_id'];
					$info['party_id']			= 0;
				}
				$info['pur_card_date']			= date('Y-m-d',strtotime($POST['pur_card_date']));
				$info['valid_date']				= date('Y-m-d',strtotime($POST['valid_date']));
				$info['effective_date']			= date('Y-m-d',strtotime($POST['effective_date']));
				
				
				$info['cdate']					= date("Y-m-d H:i:s");
				$info['user_id']				= $_SESSION['user_id'];
				$info['company_id']				= $_SESSION['company_id'];
				
				$updateid1=update_record('tbl_product_party_purchase', $info,"party_purchase_id=".$POST['eid'] , $dbcon,$branch_id);

				if($updateid1)
				{	
					$arr['msg']="update";
				}
				else{
					$arr['msg']=0;
				}
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['card_status']		= 2;
			$info1['purchasecardtrn_status']= 2;
			
			$updateinvoiceid=update_record('tbl_product_party_purchase', $info,"party_purchase_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_purchasecardtrn', $info1,"party_purchase_id=".$POST['eid'] , $dbcon);	
				
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "load_pocard_vender_detail") {
			$query = "select led.company_name,led.cust_mobile,led.m_address,led.gst_no,cit.city_name, st.state_name, cou.country_name, card.pur_card_no, card.pur_card_date, card.valid_date from tbl_product_party_purchase as card
			left join tbl_ledger as led on led.l_id=card.party_id
			left join city_mst as cit on cit.cityid=led.cityid
			left join state_mst as st on st.stateid=led.stateid
			left join country_mst as cou on cou.countryid=led.countryid
			where card.party_purchase_id=".$POST['card_id'];

			$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($query));

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
			<td><strong>Purchase Card No:</strong> '.$qt_rel['pur_card_no'].'</td>
			<td><strong>Purchase Card Date:</strong> '.date("d-M-Y",strtotime($qt_rel["pur_card_date"])).'</td>
			<td><strong>Purchase Card Valid Date:</strong> '.date("d-M-Y",strtotime($qt_rel['valid_date'])).'</td>
			</tr>
			';
			$str.='</table></div>
			<hr/>
			';

			$qt_rel['card_detail_show'] = $str;

			echo json_encode($qt_rel);		
		}
		else if(strtolower($POST['mode']) == "load_pocard_hist") {

			$where='';
			$where.="   log.party_purchase_id=".$POST['card_id'];

			$appData = array();
			$i=1;
			$aColumns = array('log.pocard_aprv_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
			$sIndexColumn = "log.pocard_aprv_id";
			$isWhere = array(" ".$where." ");
			$sTable = "tbl_pocard_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.pocard_aprv_id desc";
			include($include.'/pagging.php');
			//echo $sQuery;
			//exit;
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
		else if(strtolower($POST['mode']) == "add_pocard_apprv_hist") {
			$info1['approve_remark']	= $POST['approve_remark'];
			$info1['approve_status']	= $POST['approve_status'];
			$info1['party_purchase_id']	= $POST['party_purchase_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['cdate']				= date('Y-m-d H:i:s');

			$inserid=add_record("tbl_pocard_aprv_log", $info1, $dbcon);

			$info['is_aproove'] = $POST['approve_status'];	
			
			$updateid=update_record("tbl_product_party_purchase", $info, "party_purchase_id=".$POST['party_purchase_id'], $dbcon);
		}
		else if(strtolower($POST['mode']) == "load_pocard_pro_detail") {
			$query = "select card.pur_card_no, card.pur_card_date, card.valid_date, dr.drawing_number, pro.product_name,pro.product_icode, hsn.hsn_code, base.unit_name as base_unit, conv.unit_name as conv_unit, ty.product_type_name  from tbl_product_party_purchase as card

			left join product_mst as pro on pro.product_id = card.product_id
			left join pro_ms_product_type as ty on ty.product_type_id = pro.product_type  
			left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id 
			left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
			left join unit_mst as base on base.unitid = pro.product_base_unit
			left join unit_mst as conv on conv.unitid = pro.product_conv_unit

			where card.party_purchase_id=".$POST['card_id'];


			$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($query));

		//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Product Name:</strong> '.$qt_rel['product_name'].'</td>
			<td><strong>Product Code:</strong> '.$qt_rel['product_icode'].'</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Product type :</strong> '.$qt_rel['product_type_name'].'</td>
			<td><strong>HSN Code:</strong> '.$qt_rel['hsn_code'].'</td>
			</tr>
			<!--<tr>
			<td><strong>City:</strong> '.$qt_rel['city_name'].'</td>
			<td><strong>State:</strong> '.$qt_rel['state_name'].'</td>
			<td><strong>Country:</strong> '.$qt_rel['country_name'].'</td>
			</tr>-->
			<tr>
			<td><strong>Base Unit :</strong> '.$qt_rel['base_unit'].'</td>
			<td><strong>Conv. Unit:</strong> '.$qt_rel['conv_unit'].'</td>
			<td><strong>Drawing No:</strong> '.$qt_rel['drawing_number'].'</td>
			</tr>
			<tr>
			<td><strong>Purchase Card No:</strong> '.$qt_rel['pur_card_no'].'</td>
			<td><strong>Purchase Card Date:</strong> '.date("d-M-Y",strtotime($qt_rel["pur_card_date"])).'</td>
			<td><strong>Purchase Card Valid Date:</strong> '.date("d-M-Y",strtotime($qt_rel['valid_date'])).'</td>
			</tr>
			';
			$str.='</table></div>
			<hr/>
			';

			$qt_rel['card_detail_show'] = $str;

			echo json_encode($qt_rel);		
		}
		else if(strtolower($POST['mode'])== "active_status")
		{
			if($POST['active_status']==0){
				$info['is_active'] = 1;
			}else{
				$info['is_active'] = 0;
			}
			
			$updateid=update_record('tbl_product_party_purchase', $info,'party_purchase_id'."=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "load_product_unit")
{
    // --- Validation and Sanitization ---
    
    // 1. Sanitize edit_id
    $edit_id = isset($POST['edit_id']) ? (int)$POST['edit_id'] : 0;
    
    // 2. Sanitize product_id
    $product_id = isset($POST['product_id']) ? (int)$POST['product_id'] : 0;
    
    // Check if critical IDs are present
    if ($edit_id === 0 || $product_id === 0) {
        // Log an error or return a structured failure message
        echo json_encode(['error' => 'Missing or invalid edit_id or product_id.']);
        return; // Stop execution
    }

    // --- First Query ---
    $query = "select * from tbl_purchasecardtrn where purchasecardtrn_id=" . $edit_id;
    $rs_type = $dbcon->query($query);
    $row = brp_mysqli_fetch_assoc($rs_type);
    
    // --- Second Query ---
    $query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
        left join unit_mst as umst on umst.unitid=promst.product_base_unit
        left join join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
        WHERE product_id=" . $product_id;
    
    $rs_type1 = $dbcon->query($query1);
    $row1 = brp_mysqli_fetch_assoc($rs_type1);
    
    // ... rest of the logic
}
// 3. Best Practice: Using Prepared Statements
// For a robust application, both queries should use prepared statements. This requires two separate prepared statement blocks:

// PHP

else if(strtolower($POST['mode'])== "load_product_unit")
{
    // Sanitize and validate inputs
    $edit_id = isset($POST['edit_id']) ? (int)$POST['edit_id'] : 0;
    $product_id = isset($POST['product_id']) ? (int)$POST['product_id'] : 0;
    
    if ($edit_id === 0 || $product_id === 0) {
        echo json_encode(['error' => 'Missing required IDs.']);
        return;
    }

    // --- First Query (tbl_purchasecardtrn) ---
    $stmt1 = $dbcon->prepare("SELECT * FROM tbl_purchasecardtrn WHERE purchasecardtrn_id = ?");
    $stmt1->bind_param("i", $edit_id);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $row = $result1->fetch_assoc();
    $stmt1->close(); // Assuming you can replace brp_mysqli_fetch_assoc with fetch_assoc

    // --- Second Query (product_mst) ---
    $stmt2 = $dbcon->prepare("SELECT promst.product_base_unit, promst.product_conv_unit, conv_mst.unit_name AS convert_unit_name, umst.unit_name AS base_unit_name 
                              FROM product_mst AS promst
                              LEFT JOIN unit_mst AS umst ON umst.unitid = promst.product_base_unit
                              LEFT JOIN unit_mst AS conv_mst ON conv_mst.unitid = promst.product_conv_unit
                              WHERE product_id = ?");
    $stmt2->bind_param("i", $product_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row1 = $result2->fetch_assoc();
    $stmt2->close();

    // ... continue with your logic using $row and $row1
    
    if($row1['product_base_unit']!=$row1['product_conv_unit']){
    			$row1['unit_status']="1";
    			$base='';$conv='';
    			
    			if($row1['product_base_unit']==$row['unit_id']){
    				$base="selected='selected'";
    			}else{
    				$conv="selected='selected'";
    			}
    			$opt='<option '.$base.' value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
    			$opt .='<option '.$conv.' value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
    		}else{
    			$row1['unit_status']="0";

    			$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
    		}

    		$row1['unit_option']=$opt;
			echo json_encode($row1);
		}
?>
