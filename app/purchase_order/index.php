<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
include_once("../../crm/app/send_quotation.php");

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PO_LIST_VIEW,PO_LIST_ADD,PO_LIST_READ,PO_LIST_UPDATE,PO_LIST_DELETE,PO_LIST_APPROVE
			]);
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];

			$where='';
			$where.="  and po_type_status=1";

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('po', $branch_id);
			$where.=" $where_db and po.company_id=".$_SESSION['company_id'];
		/*switch($POST['po_type_status']){
			case "1":
			$where.="  and po_type_status=1";
			break;
			
			case "2":
			$where.="  and po_type_status=2";
			break;
			
			case "3":
			$where.="  and po_type_status=3";
			break;
			
			default:
			$where.="";
		}*/
		//echo $_SESSION['page'];
			/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);*/

			
			$where.="  and purchaseorder_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND purchaseorder_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('purchaseorder_id','purchaseorder_no','l.l_name','city.city_name','bms.branch_name','purchaseorder_date','g_total','paid_amount','status','purchase_status','po.cdate','po.userid','po.po_type_status','po.po_req_status','po_approval_status','po.branch_id');
			$sIndexColumn = "purchaseorder_id";
			$isWhere = array("status = 0".$where);
			$sTable = "tbl_purchaseorder as po";
			$isJOIN = array('left join tbl_ledger as l on po.vender_id=l.l_id','left join  city_mst city on l.cityid=city.cityid','left join branch_mst as bms on bms.branch_id=po.branch_id');
			$hOrder = "po.purchaseorder_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				if(in_array(PO_LIST_UPDATE,$bulkAccessArray)){
					$row_data[] = '<a class="" data-original-title="Edit '.$row['purchaseorder_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poedit/'.$row['purchaseorder_id'].'">'.$row['purchaseorder_no'].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row['purchaseorder_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poedit/'.$row['purchaseorder_id'].'">'.date('d M, Y',strtotime($row['purchaseorder_date'])).'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row['purchaseorder_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poedit/'.$row['purchaseorder_id'].'">'.$row["l_name"].'</a>';
				}else{
					$row_data[] = $row['purchaseorder_no'];
					$row_data[] = date('d M, Y',strtotime($row['purchaseorder_date']));
					$row_data[] = $row["l_name"];
				}
				if($row['branch_id']==10000){
					$row_data[] = 'All Branch';
				} else if($row['branch_id']==0){
					$row_data[] = '';
				} else {
					$row_data[] = $row['branch_name'];
				}
				$row_data[] = $row['city_name'];
				$row_data[] = round($row['g_total']);
				if($row['po_approval_status']=='3'){
					$row_data[] = '<button class="btn btn-xs btn-warning">Finance Pending</button>';
				}
				else if($row['po_approval_status']=='1'){
					$row_data[] = '<button class="btn btn-xs btn-success" >Approved</button>';
				}else{
					$row_data[] = '<button class="btn btn-xs btn-warning">Approved Pending</button>';
				}
				
				$poprint='';$delete='';$edit='';$cancel_po_btn='';$po_app_btn='';$po_short_close='';$grn_done='';$po_emend='';
				
				$query = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and used_status=0 and purchaseorder_id=".$row['purchaseorder_id'];
				$query_exe = $dbcon->query($query);

				//PO Approval Button To admin
				//if($_SESSION['user_type']=='2'){
				if(in_array(PO_LIST_APPROVE,$bulkAccessArray)){
					if(mysqli_num_rows($query_exe)>0){
						if($row['po_approval_status']=="3"){
							$po_app_btn='<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status('.$row['purchaseorder_id'].',0, \''.$row['purchaseorder_no'].'\')"><i class="fa fa-check"></i></button>';
						}else if($row['po_approval_status']=="0"){
							$po_app_btn='<button class="btn btn-xs btn-warning" data-original-title="Approve PO" data-toggle="tooltip" data-placement="top" onclick="change_po_approval_status('.$row['purchaseorder_id'].',1, \''.$row['purchaseorder_no'].'\')"><i class="fa fa-check"></i></button>';
						}else{
							$po_app_btn ="";
						}
					}
				}
				//}
				if($row['po_approval_status']=='3'){
					$po_emend = '<a class="btn btn-xs btn-info" data-original-title="PO Amend" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poemend/'.$row['purchaseorder_id'].'"><i class="fa fa-repeat"></i></a>';
				}
				if($row['po_approval_status']!='3' && $row['po_approval_status']!='1'){
					if($row['purchase_status']=="0"){
						if(in_array(PO_LIST_DELETE,$bulkAccessArray)){
							$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_po('.$row['purchaseorder_id'].')"><i class="fa fa-trash-o"></i></button>';
						}
						if(in_array(PO_LIST_UPDATE,$bulkAccessArray)){
							$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poedit/'.$row['purchaseorder_id'].'"><i class="fa fa-pencil"></i></a>';
						}
					}
				}else{
					if(mysqli_num_rows($query_exe)>0){
						$short_close = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and used_status=0 and shortclose_status = 1 and purchaseorder_id=".$row['purchaseorder_id'];
						$sclose_exe = $dbcon->query($short_close);
						if(mysqli_num_rows($sclose_exe)>0){
							$po_short_close = '<button class="btn btn-xs btn-warning" >Short Close Aprooval Pending</button>';
						}else{
							$po_short_close='<a onclick="shortclosepo('.$row['purchaseorder_id'].','."'$row[purchaseorder_no]'".')" class="btn btn-xs btn-danger" data-original-title="Sort Close PO" data-toggle="tooltip" data-placement="top"><i class="fa fa-close"></i></a>';
						}
					}else{
						$grn_done = '<button class="btn btn-xs btn-primary" >Grn Done</button>';
						$po_emend = '';
					}
				}
				$add_po_btn='';
				if($row['po_type_status']=='2'){
					if($row['po_req_status']=='1'){
						$add_po_btn='<button class="btn btn-xs btn-success" data-original-title="PO Created" data-toggle="tooltip" data-placement="top" >PO Created</button>';
					}
					else{
						$add_po_btn='<a class="btn btn-xs btn-primary" data-original-title="Create PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.'direct_po_add/'.$row['purchaseorder_id'].'"><i class="fa fa-plus"></i></a>';			
						$cancel_po_btn='<button class="btn btn-xs btn-danger" data-original-title="Cancel PO" data-toggle="tooltip" data-placement="top" onclick="cancel_po_status('.$row['purchaseorder_id'].',3)"><i class="fa fa-ban"></i></button>';
					}
				}
				if($row['po_type_status']=='3'){
					$cancel_po_btn='<button class="btn btn-xs btn-info" data-original-title="Request PO" data-toggle="tooltip" data-placement="top" onclick="cancel_po_status('.$row['purchaseorder_id'].',2)"><i class="fa fa-check"></i></button>';
				}
				$send_whatsapp='<button class="btn btn-xs btn-primary" data-original-title="Send Whatsapp" data-toggle="tooltip" data-placement="top" onClick="send_purchase_order('.$row['purchaseorder_id'].')"><i class="fa fa-whatsapp"></i></button>';
				
				$poprint='<a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'poprint/'.$row['purchaseorder_id'].'"><i class="fa fa-print"></i></a>';

				$row_data[] = $poprint.' '.$edit.' '.$delete.' '.$po_app_btn.' '.$send_whatsapp.' '.$po_short_close.' '.$grn_done.' '.$po_emend;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			if($POST['revise_status']){//Get Revise Count No
				$get_rev_cnt="select count(purchaseorder_id) as ttl_cnt,(select purchaseorder_no from tbl_purchaseorder where purchaseorder_id=".$POST['start_purchaseorder_id'].") as qt_no from tbl_purchaseorder where purchase_status=0 and start_purchaseorder_id=".$POST['start_purchaseorder_id'];
				$rev_cnt=mysqli_fetch_assoc($dbcon->query($get_rev_cnt));
				$info['purchaseorder_no'] 			= $rev_cnt['qt_no']."/R-".$rev_cnt['ttl_cnt'];
				$info['start_purchaseorder_id']		= $POST['start_purchaseorder_id'];			
				$info['prev_purchaseorder_id']		= $POST['prev_purchaseorder_id'];	
				$upd_prev_qt_sts=$dbcon->query("UPDATE tbl_purchaseorder set revise_status=1 where purchaseorder_id=".$POST['prev_purchaseorder_id']);
			}
			else{
				// $info['quotation_no']		= load_quotation_no($dbcon);
				// Update Start series of No
				$info['purchaseorder_no']	= $POST['purchaseorder_no'];
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
			}

			$info['po_type_status']		= 1;
			$info['vender_id']			= $POST['vender_id'];
			$info['consignee_id']		= $POST['consignee_id'];
			$info['purchaseorder_date']	= date('Y-m-d',strtotime($POST['purchaseorder_date']));
			$info['purchaseorder_due_date']	= date('Y-m-d',strtotime($POST['purchaseorder_due_date']));
			$info['mode_of_dispatch']	= $POST['dispatch_doc_no'];
			$info['payment_terms']		= $POST['payment_terms'];
			$info['round_off']			= $POST['round_off'];
			$info['packing']			= $POST['paking'];
			$info['remark']				= $POST['remark'];
			$info['g_total']			= $POST['g_total'];
			$info['po_ref_id']			= $POST['po_ref_id'];
			$info['po_condition']		= $_POST['po_condition'];
			$info['currency_id']		= $_POST['currency_id'];
			$info['godown_id']			= $POST['godown_id'];
			$info['conversion_rate']	= $_POST['conversion_rate'];
			$info['vendor_reference']	= $_POST['vendor_reference'];
			$info['quotation_no']		= $_POST['quotation_no'];
			$info['quotation_date']		= date('Y-m-d',strtotime($POST['quotation_date']));
			$info['supply_type']		= $_POST['supply_type'];
			$info['gst_type']			= $_POST['gst_type'];
			$info['formulaid']      	= $POST['formula_id']; //added by : Dimple
			$info['delivery_type'] 	 	= $POST['delivery_type']; //added by : pathik 
			$info['po_type']      		= $POST['po_type']; //added by : Maulik 
			/*$info['formulaid']		= $POST['formulaid'];
			$info['discount']			= $POST['discount'];
			$info['tax1_name']			= $POST['taxname0'];
			$info['tax2_name']			= $POST['taxname1'];
			$info['tax3_name']			= $POST['taxname2'];
			$info['taxvalue1']			= $POST['taxvalue0'];
			$info['taxvalue2']			= $POST['taxvalue1'];
			$info['taxvalue3']			= $POST['taxvalue2'];*/


			if(isset($POST['save_print']))
			{
				$info['print_status']	= $POST['print_status'];
			}
			$info['cdate']				= date("Y-m-d H:i:s");
			
			$info['userid']			= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];

			if(isset($POST['currency_total'])){
				$info['currency_total']	= $POST['currency_total'];
			}
			$inserpoid=add_record('tbl_purchaseorder', $info, $dbcon, $branch_id);
			
			if($inserpoid){
				$inftrn['purchaseorder_id'] = $inserpoid;
				$inftrn['purchaseordertrn_status'] = 0;
				$updatetrnid=update_record('tbl_purchaseordertrn', $inftrn,"user_id=".$_SESSION['user_id']." and purchaseorder_id=0 and purchaseordertrn_status=3" , $dbcon, $branch_id);
			}
			
			if(strtolower($POST['delivery_type'])=="po wise"){
				$sel_pro_rate = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id=".$inserpoid;$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);
				while($sel_pro_rate_rel=brp_mysqli_fetch_assoc($sel_pro_rate_rs)){
					$inftrn11d['delivery_date'] = date('Y-m-d',strtotime($POST['purchaseorder_due_date']));
					$updatetrnid=update_record('tbl_purchaseorder_delivery_date', $inftrn11d,"po_delivery_date_status=0 and purchaseordertrn_id=".$sel_pro_rate_rel['purchaseordertrn_id'], $dbcon, $branch_id);
				}
			}
			
			
			
/*$qry ='INSERT INTO tbl_purchaseordertrn (product_type,product_id, description,product_hsn_code,product_qty,product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,purchaseorder_id)
SELECT product_type,product_id,description,product_hsn_code,product_qty, product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,'.$inserpoid.' FROM tbl_purchasetrntemp where po_trn_req_status=1 and user_id='.$_SESSION['user_id'];
			
$dbcon->query($qry);*/
	//$deleteid=delete_record('tbl_purchasetrntemp',"user_id=".$_SESSION['user_id']." and po_trn_req_status=1", $dbcon);		
			//Change Status of Temp to Requested
			//$upd_sts_qry="UPDATE `tbl_purchasetrntemp` set po_trn_req_status=1 WHERE find_in_set(purchaseordertrn_id,(SELECT GROUP_CONCAT(temptrn_ref_id) from tbl_purchaseordertrn WHERE purchaseorder_id=".$inserpoid." and purchaseordertrn_status=0 ))";
			//$upd_sts_qry_rs=$dbcon->query($upd_sts_qry);

			//update_po_status($dbcon,$inserpoid);
			update_poreq_status($dbcon,$inserpoid);


			//Update Reqested PO Ref id in table
			//$appr_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);
			//if($appr_btn_per){
				if($POST['po_ref_id']){
					$infopo['po_req_status']	= 1;//Change Status to Done
					$updateid=update_record('tbl_purchaseorder', $infopo,"purchaseorder_id=".$POST['po_ref_id'] , $dbcon);
				}
			//}

			//$appr_btn_per=check_permission("po_list",$_SESSION['user_id'],'aprv',$dbcon);
			//var_dump($appr_btn_per);
			//auto approve stop
				/* if(in_array(PO_LIST_APPROVE,$bulkAccessArray)){
						$infopo1['auserid']			    = $_SESSION['user_id'];
						$infopo1['adate']				= date("Y-m-d H:i:s");
						$infopo1['po_approval_status']	= 1;//Change Status to Done
						$updateid12=update_record('tbl_purchaseorder', $infopo1,"purchaseorder_id=".$POST['eid'] , $dbcon);
					
				}else{ 
						$infopo1['po_approval_status']			= 0;//Change Status to Done
						$updateid12=update_record('tbl_purchaseorder', $infopo1,"purchaseorder_id=".$POST['eid'] , $dbcon);
						//var_dump("212");
				}
				*/
			//auto approve stop
			//update po transaction

			//$dbcon->query("update tbl_purchaseordertrn set po_trn_req_status");

			//$check_po_rate_status=check_po_rates_status($dbcon, $inserpoid);
				
			//Insert LOG
				$log_entry=common_log_entry($dbcon,"purchaseorder_add",1,"tbl_purchaseorder",$inserpoid);

			//auto approve log stop
			/* $logsave['approve_remark']	    = '';
			$logsave['approve_status']	    = $infopo1['po_approval_status'];
			$logsave['purchaseorder_id']	= $inserpoid;
			$logsave['user_id']			    = $_SESSION['user_id'];
			$logsave['company_id']		    = $_SESSION['company_id'];
			$logsave['cdate']				= date('Y-m-d H:i:s');

			add_record("tbl_purchaseorder_aprv_log", $logsave, $dbcon, $branch_id); */

			if(isset($POST['save_print']))
			{
				$arr['printstatus']=$POST['print_status'];
				$arr['msg']="1";
				$arr['eid']=$inserpoeid;
			}
			else
			{
				if($inserpoid)
				{	
					$arr['msg']="1";							
				}
				else
					$arr['msg']="0";
			}
			$arr['back']=$POST['back'];
			
			echo json_encode($arr);					

		}		
		else if(strtolower($POST['mode']) == "edit") {

			$info['po_type_status']		= 1;
			$info['purchaseorder_no']	= $POST['purchaseorder_no'];
			$info['vender_id']			= $POST['vender_id'];
			$info['consignee_id']		= $POST['consignee_id'];
			$info['purchaseorder_date']	= date('Y-m-d',strtotime($POST['purchaseorder_date']));
			$info['purchaseorder_due_date']	= date('Y-m-d',strtotime($POST['purchaseorder_due_date']));
			$info['mode_of_dispatch']	= $POST['dispatch_doc_no'];
			$info['payment_terms']		= $POST['payment_terms'];
			$info['round_off']			= $POST['round_off'];
			$info['packing']			= $POST['paking'];
			$info['remark']				= $POST['remark'];
			$info['g_total']			= $POST['g_total'];
			$info['po_condition']		= $_POST['po_condition'];
			$info['currency_id']	    = $_POST['currency_id'];
			$info['godown_id']			= $POST['godown_id'];
			$info['conversion_rate']	= $_POST['conversion_rate'];
			$info['vendor_reference']	= $_POST['vendor_reference'];
			$info['quotation_no']	    = $_POST['quotation_no'];
			$info['quotation_date']		= date('Y-m-d',strtotime($POST['quotation_date']));
			$info['supply_type']		= $_POST['supply_type'];
			$info['gst_type']			= $_POST['gst_type'];
			$info['formulaid']      	= $POST['formula_id']; //added by : Dimple
			$info['delivery_type']      = $POST['delivery_type']; //added by : pathik
			$info['po_type']     		= $POST['po_type']; //added by : Maulik
			/*$info['formulaid']	= $POST['formulaid'];
			$info['discount']	= $POST['discount'];
			$info['tax1_name']	= $POST['taxname0'];
			$info['tax2_name']	= $POST['taxname1'];
			$info['tax3_name']	= $POST['taxname2'];
			$info['taxvalue1']	= $POST['taxvalue0'];
			$info['taxvalue2']	= $POST['taxvalue1'];
			$info['taxvalue3']	= $POST['taxvalue2'];*/
			$info['mdate']		= date("Y-m-d H:i:s");
			$info['company_id']		= $_SESSION['company_id'];
			if(isset($POST['save_print']))
			{
				$info['print_status']	= $POST['print_status'];
			}
			//$info['cdate']				= 	date("Y-m-d H:i:s");
			$info['muserid']				= $_SESSION['user_id'];
			$updateid1=update_record('tbl_purchaseorder', $info,"purchaseorder_id=".$POST['eid'] , $dbcon);
			
			if(strtolower($POST['delivery_type'])=="po wise"){
				$sel_pro_rate = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id=".$POST['eid'];$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);
				while($sel_pro_rate_rel=brp_mysqli_fetch_assoc($sel_pro_rate_rs)){
					$inftrn11d['delivery_date'] = date('Y-m-d',strtotime($POST['purchaseorder_due_date']));
					$updatetrnid=update_record('tbl_purchaseorder_delivery_date', $inftrn11d,"po_delivery_date_status=0 and purchaseordertrn_id=".$sel_pro_rate_rel['purchaseordertrn_id'], $dbcon, $branch_id);
				}
			}

			//$check_po_rate_status=check_po_rates_status($dbcon, $POST['eid']);	

			//Update Reqested PO Ref id in table
			//$appr_btn_per=check_permission("po_list",$_SESSION['user_id'],'aprv',$dbcon);
			//var_dump($appr_btn_per);
			// auto approve stop
			/* if(in_array(PO_LIST_APPROVE,$bulkAccessArray)){
					$infopo['auserid']			   = $_SESSION['user_id'];
					$infopo['adate']			   = date("Y-m-d H:i:s");					
					$infopo['po_approval_status']  = 1;//Change Status to Done
					$updateid12=update_record('tbl_purchaseorder', $infopo,"purchaseorder_id=".$POST['eid'] , $dbcon);
				
				
			}else{ 
					$infopo['po_approval_status']			= 0;//Change Status to Done
					$updateid12=update_record('tbl_purchaseorder', $infopo,"purchaseorder_id=".$POST['eid'] , $dbcon);
					//var_dump("212");
				} */
			// auto approve stop
			//update_po_status($dbcon,$POST['eid']);

			//Insert LOG
				$log_entry=common_log_entry($dbcon,"purchaseorder_add",2,"tbl_purchaseorder",$POST['eid']);

				if(isset($POST['save_print']))
				{
					//var_dump($updateid1);
					$arr['printstatus']=$POST['print_status'];
					$arr['msg']="update";
					$arr['eid']=$POST['eid'];
				}
				else
				{ 
					
					if($updateid1)
					{	
						$arr['msg']="update";
						
					}
					else{
						$arr['msg']=0;
					}
				}
				echo json_encode($arr);	

			}
			else if(strtolower($POST['mode']) == "delete") {
				$info['status']		= 2;
				$info1['purchaseordertrn_status']		= 2;

				$que_po="select * from tbl_purchaseordertrn where temptrn_ref_id!='' and purchaseorder_id=".$POST['eid'];
				$resi=$dbcon->query($que_po);
				while($re_po=brp_mysqli_fetch_assoc($resi))
				{
					delete_po_req_status($dbcon,$re_po['purchaseordertrn_id']);
				}

				$updateinvoiceid=update_record(' tbl_purchaseorder', $info,"purchaseorder_id=".$POST['eid'] , $dbcon);	
				$updatetrancationid=update_record('tbl_purchaseordertrn', $info1,"purchaseorder_id=".$POST['eid'] , $dbcon);	
				
				
			//update_po_status($dbcon,$POST['eid']);
			//Insert LOG
				$log_entry=common_log_entry($dbcon,"purchaseorder_add",3,"tbl_purchaseorder",$POST['eid']);

				if($updatetrancationid)
					echo "1";	
				else
					echo "0";			
			}
			else if(strtolower($POST['mode'])== "load_productdata")
			{
			//$qry="select popro.*, from tbl_purchaseproduct as porpo left join tbl_company as com on com.company_id=".$_SESSION['company_id']." where product_id=".$POST['eid'];
				$qry="select popro.*,com.stateid as com_stateid,ven.stateid as ven_stateid from `product_mst` as popro 
				left join `tbl_company` as com on com.company_id=".$_SESSION['company_id']." 
				left join tbl_ledger as ven on ven.l_id=".$POST['vender_id']." where product_id=".$POST['eid'];
				$result=$dbcon->query($qry);

				$row=brp_mysqli_fetch_assoc($result);

				$qry_purchase_card_rate="select party_rate from `tbl_product_party_purchase` as popro 
				where product_party_purchase_status=0 and company_id=".$_SESSION['company_id']." and party_product=".$POST['eid']." and party_id=".$POST['vender_id'];
				$result_purchase_card_rate=$dbcon->query($qry_purchase_card_rate);
				$row_purchase_card_rate=brp_mysqli_fetch_assoc($result_purchase_card_rate);

				if(!empty($row_purchase_card_rate['party_rate'])){
					$row['prate']=$row_purchase_card_rate['party_rate'];
				}else{
					$row['prate']=$row['product_purchase_rate'];
				}

				echo json_encode( $row );
			}
			else if(strtolower($POST['mode'])== "get_series_no")
			{
				$query="select * from tbl_invoicetype where status=0 and type_id=".$POST['type_id']." and company_id=".$_SESSION['company_id'];
				$result=$dbcon->query($query);
				$row=brp_mysqli_fetch_assoc($result);
				echo $row['invoicetype_id'];
			}
			else if(strtolower($POST['mode']) == "formulavalue") {
				$rate_total=0;$c_total=$POST['c_total'];
				$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$POST['eid']." order by tax_value desc";
				$row=$dbcon->query($qry);
				$j=0;
				//$dis=$POST['total']*$POST['t_dis']/100;
				$rate_total=$total=$POST['total'];
				while($tax=brp_mysqli_fetch_assoc($row))
				{	
					if(strpos(strtolower(" ".$tax['tax_name']), "excise")==true)
					{
						$rate=$total*$tax['tax_value']/100;
						$total+=$rate;
						$rate=number_format($rate,2,".","");
					}
					else	
					{
						$rate=($total)*$tax['tax_value']/100;
						$rate=number_format($rate,2,".","");
					}
					echo '<div class="form-group">
					<label class="col-md-6 control-label">'.$tax['tax_name'].'</label>
					<div class="col-md-4 col-xs-11">
					<input id="taxvalue'.$j.'" name="taxvalue'.$j.'" value= "'.$rate.'"type="text" class="form-control" readonly="readonly">
					</div>
					</div>
					<input id="taxname'.$j.'" name="taxname'.$j.'" value= "'.$tax['tax_name'].'" type="hidden" class="form-control">';
					$rate_total=$rate_total+$rate;
					$j++;
				}
				$g_total=$rate_total+$c_total;
				$g_total=number_format($g_total,2,".","");

				echo '<input id="rate" name="rate" value= "'.$g_total.'" type="hidden" class="form-control" >';
			}
			else if(strtolower($POST['mode']) == "fieldadd") {

				$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

				$total=$POST['taxable_value'];
				$product_currency_rate = ($POST['conversion_rate']*$POST['product_rate']);
				$product_currency_amount = ($POST['conversion_rate']*$POST['taxable_value']);
				$product_currency_amount_tax = ($POST['conversion_rate']*$POST['product_amount_tax']);
				$currency_total = ($POST['conversion_rate']*$POST['product_amount']);

				$info1['product_type']			= $POST['product_type'];
				$info1['product_id']			= $POST['product_id'];
				$info1['description']			= $_POST['product_des'];
				$info1['product_hsn_code']		= $POST['product_hsn_code'];
				$info1['product_qty']			= $POST['product_qty'];
				$info1['product_conv_qty']		= $POST['product_conv_qty'];
				$info1['process_id']			= $POST['process_id'];
				//$info1['sqr_ft']				= $POST['sqr_ft'];
				$info1['unit_id']				= $POST['unit_id'];
				$info1['conv_unit_id']			= $POST['conv_unitid'];
				$info1['product_rate']			= $POST['product_rate'];
				$info1['product_discount']		= $POST['product_discount'];
				$info1['discount_per']			= $POST['discount_per'];
				$info1['formulaid']				= $POST['formulaid'];
				$info1['product_amount']		= $POST['taxable_value'];
				$info1['sel_tax']				= $POST['sel_tax'];
				$info1['formula_tax_id']		= $POST['formula_tax_id'];
				$info1['total']					= $POST['product_amount'];
				$info1['product_amount_tax']	= $POST['product_amount_tax'];
				$info1['user_id']				= $_SESSION['user_id'];

				/*Code By Umair:*/
				$info1['currency_id']			= $POST['currency_id'];
				$info1['conversion_rate']		= $POST['conversion_rate'];
				$info1['product_currency_rate']	= sprintf('%0.2f', $product_currency_rate);
				$info1['product_currency_amount']= sprintf('%0.2f', $product_currency_amount);
				$info1['product_currency_amount_tax']= sprintf('%0.2f', $product_currency_amount_tax);
				$info1['currency_total']		= sprintf('%0.2f', $currency_total);

				/*if($POST['vendor_id']!=''){
					
				}*/
				
				$info=get_product_tax($dbcon,$total,$POST['formulaid']);

				$info1['tax_name1']		= $info['tax_name1'];
				$info1['tax_amount1']	= $info['tax_amount1'];
				$info1['tax_name2']		= $info['tax_name2'];
				$info1['tax_amount2']	= $info['tax_amount2'];
				$info1['tax_name3']		= $info['tax_name3'];
				$info1['tax_amount3']	= $info['tax_amount3'];

				//$info1=array_merge($info1,$info);
				$table='tbl_purchaseordertrn';$tableid='purchaseordertrn_id';
				if(!empty($POST['purchaseorder_id']))
				{
					$info1['purchaseorder_id']= $POST['purchaseorder_id'];
					$table='tbl_purchaseordertrn';
					$tableid='purchaseordertrn_id';
				}else{
					$info1['purchaseordertrn_status']	= 3;
				}
				
				if(empty($POST['edit_id']))
				{
					
					$inserid=add_record($table, $info1, $dbcon, $branch_id);
					$tax_trn_id=$inserid;
					$tx_tran_type_id=$POST['purchaseorder_id'];
				}
				else
				{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
					$tax_trn_id=$POST['edit_id'];
					$tx_tran_type_id='0';

					// Update the tax data of those product from the tbl_tax_trn table
					$updStatus['tx_status']	= 2;
					$updwhere = " tx_product_id = '".$POST['product_id']."' AND tx_transaction_id = '".$POST['edit_id']."' AND tx_transaction_type = 'purchase_order' ";
					$updateid=update_record('tbl_tax_trn',$updStatus,$updwhere, $dbcon, $branch_id);	
				}
				
				if(!empty($POST['purchaseorder_id'])){
					if(!empty($POST['edit_id'])){
						update_poreq_status_edit($dbcon,$POST['edit_id'], $branch_id);
					}
				}
				
				$formula_tax_id=explode(",",$POST['formula_tax_id']);
				
				
				foreach($formula_tax_id as $f)
				{
					$tax_value=get_tax_field_tax_id($dbcon,$f,'tax_value');
					$taxable_value=($info1['product_amount']*$tax_value)/100;
					
					
					$infot['tx_tax_id']=$f;
					$infot['tx_tax_value']=$tax_value;
					$infot['tx_taxable_value']=$taxable_value;
					$infot['tx_transaction_id']=$tax_trn_id;
					$infot['tx_transaction_type']='purchase_order';
					$infot['tx_product_id']=$POST['product_id'];
					$infot['tx_tran_type_id']=$tx_tran_type_id;
					$infot['user_id']	= $_SESSION['user_id'];
					$infot['cdate']= date("Y-m-d H:i:s");
					$infot['company_id']=$_SESSION['company_id'];
					
					$table1='tbl_tax_trn';$tableid1='tx_id';
					
					$inserid1=add_record($table1, $infot, $dbcon, $branch_id);
					
					echo $taxable_value."<br>";
				}
				
				$d_id=array();
				if(strtolower($POST['delivery_type'])=="product_wise"){	
					$total_delivery_qty=$POST['total_delivery_qty'];
					$delivery_date=$POST['delivery_date'];
					$arry_edit=$POST['arry_edit'];
					

					for($i=0;$i<count($total_delivery_qty);$i++)
					{
						$info_dil['purchaseordertrn_id']	= $tax_trn_id;
						$info_dil['delivery_date']			= date('Y-m-d',strtotime($delivery_date[$i]));
						$info_dil['product_qty']			= $total_delivery_qty[$i];
						$info_dil['unit_id']				= $info1['unit_id'];
						
						$info_dil['user_id']		= $_SESSION['user_id'];
						$info_dil['cdate']			= date("Y-m-d h:i:s");
						$info_dil['company_id']		= $_SESSION['company_id'];
						//$info_dil['branch_id']		=$_SESSION['company_id'];
						//var_dump($info);
						$table_k='tbl_purchaseorder_delivery_date';$tableid_k='po_delivery_date_id';
						
						if(!empty($arry_edit[$i])){
							$updateid_k=update_record($table_k,$info_dil,"po_delivery_date_id=".$arry_edit[$i],$dbcon,$branch_id);
							array_push($d_id,$arry_edit[$i]);
						}else{
							$inserid_k=add_record($table_k,$info_dil,$dbcon,$branch_id);
							array_push($d_id,$inserid_k);
						}
					}
					
				}else{
					$query_dd="select * from tbl_purchaseorder_delivery_date as mst 
					where mst.purchaseordertrn_id=".$tax_trn_id." order by po_delivery_date_id desc";
					$row_dd=$dbcon->query($query_dd);
					$rel_dd=brp_mysqli_fetch_assoc($row_dd);

					$info_dil['purchaseordertrn_id']	= $tax_trn_id;
					$info_dil['delivery_date']			= date('Y-m-d',strtotime($POST['purchaseorder_due_date']));
					$info_dil['product_qty']			= $info1['product_qty'];
					$info_dil['unit_id']				= $info1['unit_id'];
					
					$info_dil['user_id']		= $_SESSION['user_id'];
					$info_dil['cdate']			= date("Y-m-d h:i:s");
					$info_dil['company_id']		= $_SESSION['company_id'];
					//$info_dil['branch_id']		=$_SESSION['company_id'];
					//var_dump($info);
					$table_k='tbl_purchaseorder_delivery_date';$tableid_k='po_delivery_date_id';
					
					if(!empty($rel_dd['po_delivery_date_id'])){
						$updateid_k=update_record($table_k,$info_dil,"po_delivery_date_id=".$rel_dd['po_delivery_date_id'],$dbcon,$branch_id);
						array_push($d_id,$rel_dd['po_delivery_date_id']);
					}else{
						$inserid_k=add_record($table_k,$info_dil,$dbcon,$branch_id);
						array_push($d_id,$inserid_k);
					}
				}

				$did=implode(",",$d_id);
				$info_dil_1['po_delivery_date_status']="2";
				$updateid_p=update_record($table_k,$info_dil_1,"purchaseordertrn_id=".$tax_trn_id." and po_delivery_date_id NOT IN (".$did.")",$dbcon,$branch_id);
			}
			else if(strtolower($POST['mode']) == "load_tempoutward") {
			/*$query="select sum(po.product_qty) as pqty,sum(po.product_amount) as pamt,sum(po.total) as ptotal,po.tax_name,product.* from tbl_purchasetrntemp  as po 
			left join product_mst as product on product.product_id=po.product_id  
			where purchaseordertrn_status=0 and po.product_id=".$po_id." group by po.product_id";*/
			
			if($POST['eid']){ 
				$query="select trn.*,product.product_name,product.product_type,cat.unit_name,cat_con.unit_name as conv_unit_name,tc.cat_name,proc.process_name from tbl_purchaseordertrn as trn
				left join unit_mst as cat on cat.unitid=trn.unit_id
				left join unit_mst as cat_con on cat_con.unitid=trn.conv_unit_id
				left join product_mst as product on product.product_id=trn.product_id 
				left join tbl_category as tc on product.product_category=tc.cat_id 
				left join process_mst as proc on proc.process_id=trn.process_id 
				where trn.purchaseordertrn_status=0 and trn.purchaseorder_id=".$POST['eid'];
			}
			else{
				$query="select trn.*,product.product_name,product.product_type,cat.unit_name,cat_con.unit_name as conv_unit_name,tc.cat_name,proc.process_name from tbl_purchaseordertrn as trn
				left join unit_mst as cat on cat.unitid=trn.unit_id
				left join unit_mst as cat_con on cat_con.unitid=trn.conv_unit_id
				left join product_mst as product on product.product_id=trn.product_id
				left join tbl_category as tc on product.product_category=tc.cat_id 
				left join process_mst as proc on proc.process_id=trn.process_id				
				where trn.purchaseordertrn_status=3 and trn.purchaseorder_id=0 and trn.user_id=".$_SESSION['user_id'];
			}
			
			$result=$dbcon->query($query);
			echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th class="text-center" width="10%">Product Type</th>
			<th class="text-center" width="22%">Product Name</th>
			<th class="text-center" width="10%">Product Category</th>';
			if($POST['po_type'] == 2){
				echo '<th class="text-center" width="10%">Process Name</th>';
			}
			echo '<th class="text-center"width="6%">HSN Code</th>
			<th class="text-center"width="8%">Qty</th>
			<th class="text-center"width="12%">Rate</th>
			<!--<th class="text-center"width="6%">Unit</th>-->
			<th class="text-center"width="8%">Discount</th>
			<th class="text-center"width="10%">Taxable value</th>
			<th class="text-center"width="15%">Tax</th>
			<th class="text-center"width="15%">Amount</th>
			<th class="text-center"width="10%">Action</th>
			</tr>';

			//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				//$r=get_product_tax($dbcon,$rel['purchaseordertrn_id']);
				//$total=$rel['pqty']*$rel['product_purchase_rate'];
					$currency_id = $rel['currency_id'];
					$rate_label = '';$product_amount_label = '';$product_total_label = '';
					if($currency_id!=0){
						$selectCu = "SELECT currency_name FROM currency_mst WHERE currencyid='".$currency_id."' ";
						$curenresult=$dbcon->query($selectCu);
						$vrel=brp_mysqli_fetch_assoc($curenresult);

						if($vrel['currency_name']!=$_SESSION['currency_name']){
							echo '<input type="hidden" id="currency_type_response" value="'.$vrel['currency_name'].'">';
							$rate_label .= $_SESSION['currency_name'].' :' .$rel['product_rate']."<br>";
							$rate_label .=  $vrel['currency_name'].' :' .$rel['product_currency_rate'];

							$product_amount_label .= $_SESSION['currency_name'].' :' .$rel['product_amount']."<br>";
							$product_amount_label .=  $vrel['currency_name'].' :' .$rel['product_currency_amount'];

							$product_total_label .= $_SESSION['currency_name'].' :' .$rel['total']."<br>";
							$product_total_label .=  $vrel['currency_name'].' :' .$rel['currency_total'];

						}else{
							$rate_label .= $_SESSION['currency_name'].' :' .$rel['product_rate'];
							$product_amount_label .=$_SESSION['currency_name'].' :' .$rel['product_amount'];
							$product_total_label .= $_SESSION['currency_name'].' :' .$rel['total'];
						}
					}else{
						$rate_label .= $_SESSION['currency_name'].' :' .$rel['product_rate'];
						$product_amount_label .= $_SESSION['currency_name'].' :' .$rel['product_amount'];
						$product_total_label .= $_SESSION['currency_name'].' :' .$rel['total'];
					}

					if($rel['unit_id']!=$rel['conv_unit_id']){
						$show_qty=$rel['product_qty']." ".$rel['unit_name']." </br> ".$rel['product_conv_qty']." ".$rel['conv_unit_name'];
					}else{
						$show_qty=$rel['product_qty']." ".$rel['unit_name'];
					}
					
					echo '<tr id="fieldtr'.$i.'">
					<td style="vertical-align:top;">
					'.get_pro_type_name($rel['product_type']).'
					</td>
					<td style="vertical-align:top;max-width:310px">
					'.$rel['product_name'].'
					'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$cat_name.'
					</td>';
					if($POST['po_type'] == 2){
						echo '<th class="text-center" width="10%">'.$rel['process_name'].'</th>';
					}
					echo '<td style="vertical-align:top;" class="text-center">
					'.$rel['product_hsn_code'].'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$show_qty.'
					</td>					
					<td style="vertical-align:top;" class="text-left">
					'.$rate_label.'
					</td>				
					<!--<td style="vertical-align:top" class="text-center">
					'.$rel['unit_name'].'
					</td>-->
					<td style="vertical-align:top" class="text-right">
					'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
					</td>
					<td style="vertical-align:top" class="text-left">
					'.($product_amount_label).'
					</td>
					<td style="vertical-align:top" class="text-left">
					'.$rel['sel_tax'].' - ('.$rel['product_amount_tax'].')
					</td>
					<td style="vertical-align:top" class="text-left">
					'.$product_total_label.'
					</td>
					<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.$rel['total'].'"/>
					<input type="hidden" name="currency_total[]" id="currency_total'.$i.'" value="'.$rel['currency_total'].'"/>

					<td style="vertical-align:top">

					<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['purchaseordertrn_id'].',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['purchaseordertrn_id'].',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
					</td>	
					</tr>';

					$i++;
				}
			}

			else{
				echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table> </div>
			</div>';
		}
		else if(strtolower($POST['mode'])== "get_po_tax")
		{
			$cust_id=$POST['cust_id'];
			
			$query="select  mst.*,product.product_name,product.product_purchase_rate,cat.unit_name,product.product_name from tbl_purchasetrntemp as mst 
			left join unit_mst as cat on cat.unitid=mst.unit_id 
			left join product_mst as product on product.product_id=mst.product_id  
			where mst.product_id='$POST[eid]' order by purchaseordertrn_id desc";
			$row=$dbcon->query($query);
			
			while($rel=brp_mysqli_fetch_assoc($row))
			{
				$pur_trn_id=$rel['purchaseordertrn_id'];
				$rate=$rel['product_rate'];
				$qty=$rel['product_qty'];
				$product_id=$rel['product_id'];
				$pr_amount=$rate*$qty;
				
				$cust_arr=get_cust_data_arr($dbcon,$cust_id);
				$cust_state=$cust_arr['stateid'];
				$r=get_product_tax_formula($dbcon,$product_id,'purchase',$cust_state);
				
				
				$r1=json_decode($r,true);
				//$info1['formulaid']			= $r['formulaid'];
				//$arr=get_product_tax($dbcon,$rate,$r['formulaid']);

				$fid=$r1['id'];
				$tax_name=$r1['name'];
				$arr=get_product_tax_common($dbcon,$pr_amount,$fid);
				
				//print_r($arr);
				//echo $fid.",";
				$total=$arr['total'];
				$tax=$arr['tax_total_amount'];

				$dbcon->query("update tbl_purchasetrntemp set product_rate='$rate',product_amount='$pr_amount',product_amount_tax='$tax',formulaid='$fid',total='$total',tax_name='$tax_name',po_trn_req_status='1' where product_id='$product_id'");
				
				//echo $tax_name;
			}

		}
		else if(strtolower($POST['mode'])== "get_vendor_contact_details"){
			/* Code By Umair : to return the vendors basic information */
			$cust_id=$POST['cust_id'];
			$venqry = "SELECT `v`.`m_name`, `v`.`company_name`, `v`.`cust_mobile`, `v`.`cust_mobile`, `v`.`cust_email` FROM tbl_ledger as v WHERE `v`.`l_id`='".$cust_id."' AND `v`.`company_id`='".$_SESSION['company_id']."'";
			$vrow=$dbcon->query($venqry);

			$vrel=brp_mysqli_fetch_assoc($vrow);
			
			echo json_encode($vrel);

		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("SELECT mst.*,pro.product_name,pro.product_type,proc.process_name FROM ".$_POST['table']." as mst left join product_mst as pro on mst.product_id=pro.product_id left join process_mst as proc on proc.process_id = mst.process_id  WHERE ".$_POST['whereid']." = '$POST[id]'");
			$r = $q->fetch_assoc();
			
			$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');
			
			$r['product_qty_show']=number_format($r['product_qty'], 3, ".", "");
			$r['product_conv_qty_show']=number_format($r['product_conv_qty'], 3, ".", "");
			
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "load_product_tax")
		{
			$cust_arr=get_cust_data_arr($dbcon,$POST['vendor']);
			$cust_state=$cust_arr['stateid'];
			$r=get_product_tax_formula($dbcon,$POST['pid'],$_POST['tran_type'],$cust_state);
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo $r;
			//echo $cust_state;
		}
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
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
		else if(strtolower($POST['mode'])== "getproduct_amount")
		{
			$arr=get_product_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			if(!empty($POST['purchaseorder_id']))
			{
				$info['purchaseordertrn_status']=2;	
				//$row['producthtml']=getpurchaseproduct($dbcon,0,'Edit',$POST['purchaseorder_id']);
				delete_po_req_status($dbcon,$POST['eid']);
			}
			else
			{
				$info['purchaseordertrn_status']=2;	
				//$row['producthtml']=getpurchaseproduct($dbcon,0,'Add');
			}
			$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];

			/*Code By Umair: To fetch those product  list which have purchase card.*/
			/*$vender_id = $POST['vender_id'];
			$sql = "SELECT product_id FROM tbl_purchasecardtrn WHERE vendor_id = '".$vender_id."'";
			$vrow=$dbcon->query($sql);
			$product_list = [];
			while($vrel=brp_mysqli_fetch_assoc($vrow)){
				$product_list[] = "'".$vrel['product_id']."'";
			}

			$product_list = implode(',', $product_list);
			if($product_list){
				$where = " and p.product_id in(".$product_list.") and product_type=".$type_id;
			}else{
				$where = " and product_type=".$type_id;
			}*/

			$where = " and product_type=".$type_id;
			/*End Umair Code*/
			echo getrequiredproduct($dbcon,'', $where);
		}
		/*else if(strtolower($POST['mode'])=="entry_po_req_data")
		{
			$purchaseorder_id=$POST['purchaseorder_id'];
			$deleteid=delete_record('tbl_purchasetrntemp',"user_id=".$_SESSION['user_id'], $dbcon);		
			
			$qry ='INSERT INTO tbl_purchasetrntemp (product_type,product_id, description,product_hsn_code,product_qty,product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id)
SELECT product_type,product_id,description,product_hsn_code,product_qty, product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id FROM tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id='.$purchaseorder_id;
			
			$dbcon->query($qry);
			
		}*/
		else if(strtolower($POST['mode'])== "cancel_po_status")
		{
			$row=array();
			$info['po_type_status'] = $POST['po_status'];	
			
			$updateid=update_record("tbl_purchaseorder", $info,"purchaseorder_id=".$POST['eid'] , $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "change_po_approval_status")
		{
			$row=array();
			$info['po_approval_status'] = $POST['po_approval_status'];	
			
			$updateid=update_record("tbl_purchaseorder", $info, "purchaseorder_id=".$POST['eid'], $dbcon);

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_po_order")
		{
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('po', $branch_id);
			$where.=" $where_db and po.company_id=".$_SESSION['company_id'];

			$vendor_id = $POST['vendor_id'];
			$qry="SELECT `po`.`vender_id`,`po`.`purchaseorder_id`, `po`.`purchaseorder_no`, `po`.`po_approval_status` as stage, `po`.`purchaseorder_date`, SUM(`pdt`.`product_amount`) as product_amount, SUM(`pdt`.`product_amount` + `pdt`.`product_amount_tax`) as product_total_amount  FROM `tbl_purchaseorder` as po left join `tbl_purchaseordertrn` as pdt ON  `po`.`purchaseorder_id` = `pdt`.`purchaseorder_id` Where `po`.`vender_id`=".$vendor_id." and `po`.`purchase_status`= 0 $where group BY `po`.`purchaseorder_id`";
			

			$result=$dbcon->query($qry);
			echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th class="text-center" width="10%">PO No</th>
			<th class="text-center" width="25%">PO Date</th>
			<th class="text-center"width="8%">Net Amount</th>
			<th class="text-center"width="8%">Gross Amount</th>
			<th class="text-center"width="10%">Status</th>
			<th class="text-center"width="10%">Stage</th>
			</tr>';

						//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
				//$r=get_product_tax($dbcon,$rel['purchaseordertrn_id']);
				//$total=$rel['pqty']*$rel['product_purchase_rate'];
					if($rel['stage']=='1'){
						$stage = 'Approved';
					}else{
						$stage = 'No';
					}
					echo '<tr id="fieldtr'.$i.'">
					
					<td style="vertical-align:top;" class="text-center"><a href="'.ROOT.'poedit/'.$rel['purchaseorder_id'].'">'.$rel['purchaseorder_no'].'</a>
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.$rel['purchaseorder_date'].'
					</td>					
					<td style="vertical-align:top;" class="text-right">
					'.$rel['product_amount'].'
					</td>				
					<td style="vertical-align:top" class="text-center">
					'.$rel['product_total_amount'].'
					</td>
					<td style="vertical-align:top" class="text-center">
					</td>
					<td style="vertical-align:top" class="text-center">
					'.$stage.'
					</td>

					</tr>';
					$i++;
				}
			}

			else{
				echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table> </div>
			</div>';
		}
		else if(strtolower($POST['mode'])== "get_po_billing_terms")
		{
			/*echo "<pre>";
			print_r($_POST);*/
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
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['user_name'];

				if($row['approve_status']=='3'){
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
		else if(strtolower($POST['mode']) == "add_po_apprv_hist") {

			$info1['approve_remark']	= $POST['approve_remark'];
			$info1['approve_status']	= $POST['approve_status'];
			$info1['purchaseorder_id']	= $POST['purchase_order_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['cdate']				= date('Y-m-d H:i:s');

			$inserid=add_record("tbl_purchaseorder_aprv_log", $info1, $dbcon);

			$info['po_approval_status'] = $POST['approve_status'];	
			
			$updateid=update_record("tbl_purchaseorder", $info, "purchaseorder_id=".$POST['purchase_order_id'], $dbcon);


		}else if(strtolower($POST['mode'])== "get_po_vendor_details")
		{
			$vendor_id = $POST['vendor_id'];
			$sql = "SELECT `v`.`l_id`,`v`.`l_name`,`v`.`l_form`, `v`.`cust_pincode`, `v`.`m_address`, `v`.`cust_mobile`, `v`.`cust_email`, `v`.`cust_website`, `v`.`gst_no`, `conm1`.`country_name`, `cm1`.`city_name`, `sm`.`state_name`  FROM `tbl_ledger` as v left join `country_mst` as conm1  ON `v`.`countryid`= `conm1`.`countryid` left join `city_mst` as cm1 ON `v`.`cityid`= `cm1`.`cityid` left join `state_mst` as sm ON `v`.`stateid`= `sm`.`stateid`  WHERE `v`.`l_id` = '".$vendor_id."' AND `v`.`company_id`='".$_SESSION['company_id']."'";
			$vrow=$dbcon->query($sql);
			$rel=brp_mysqli_fetch_assoc($vrow);
			
			
			echo '<section class="panel">
			<div class="panel-body bio-graph-info">
			<h1>Vendor Details</h1>
			<div class="row">
			<div class="bio-row">
			<p><span>Address </span>: '.$rel["m_address"].'</p>
			</div>
			<div class="bio-row">
			<p><span>City </span>: '.$rel["city_name"].'</p>
			</div>
			<div class="bio-row">
			<p><span>State </span>: '.$rel["state_name"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Country</span>: '.$rel["country_name"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Fax No. </span>: NA</p>
			</div>
			<div class="bio-row">
			<p><span>Email ID </span>: '.$rel["cust_email"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Mobile </span>: '.$rel["cust_mobile"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Website </span>: '.$rel["cust_website"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Pin Code </span>: '.$rel["cust_pincode"].'</p>
			</div>

			</div>
			</div>
			</section>';
		}

		else if(strtolower($POST['mode'])== "get_po_history")
		{
			$eid = $POST['eid']; // as purchase id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `au`.`user_name` as approved_by, `po`.`adate`, `po`.`po_approval_status` as stage, `po`.`purchaseorder_due_date` as delivery_date  FROM `tbl_purchaseorder` as po left join `users` as u ON  `po`.`userid` = `u`.`user_id` left join `users` as mu ON  `po`.`muserid` = `mu`.`user_id` left join `users` as au ON  `po`.`auserid` = `au`.`user_id` Where `po`.`purchaseorder_id`='".$eid."' and `po`.`purchase_status`= 0 and `po`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			$rel=brp_mysqli_fetch_assoc($vrow);

			if($rel['stage']=='1'){
				$stage = 'Approved';
			}else{
				$stage = 'No';
			}			
			echo '<section class="panel">
			<div class="panel-body bio-graph-info">
			<h1>PO History</h1>
			<div class="row">
			<div class="bio-row">
			<p><span>Prepared By </span>: '.$rel["prepared_by"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Prepared Date </span>: '.$rel["cdate"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Modified By </span>: '.$rel["last_modify_by"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Modified Date</span>: '.$rel["mdate"].'</p>
			</div>

			<div class="bio-row">
			<p><span>Approved By </span>: '.$rel["approved_by"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Approved Date </span>: '.$rel["adate"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Delivery Date </span>: '.$rel["delivery_date"].'</p>
			</div>
			<div class="bio-row">
			<p><span> Stage </span>: '.$stage.'</p>
			</div>

			</div>
			</div>
			</section>';
		}
		else if(strtolower($POST['mode'])== "set_vendor_sesion"){
			$vendor_id = $POST['vendor_id'];
			$_SESSION['selected_vendor'] = $vendor_id;
		}

		else if(strtolower($POST['mode'])== "get_purchase_card_price")
		{
			$vendor_id = $POST['vendor_id'];
			$product_id = $POST['product_id'];

			$respose = getItemPriceByVendorId($dbcon, $vendor_id, $product_id);
			if(!empty($respose)){
				$row['status'] = '1';
				$row['response'] = $respose; 
			}else{
				$row['status'] = '0';
			}

			echo json_encode($row);
		}
                // Dimple Panchal : Start
		else if(strtolower($POST['mode'])== "get_tax_on_total")
		{
			$arr = get_tax_on_total($dbcon,$POST['total'],$POST['formulaid']);
			echo json_encode($arr);
		}
                // Dimple Panchal : end
			//pathik start
		else if(strtolower($POST['mode'])== "load_product_unit")
		{
			$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE product_id=".$POST['product_id'];
			$rs_type1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($rs_type1);

			if($row1['product_base_unit']!=$row1['product_conv_unit']){
				$row1['unit_status']="1";
			}else{
				$row1['unit_status']="0";
			}
				//$row1['qye']=$query1;

			echo json_encode($row1);
		}
		else if(strtolower($POST['mode'])== "send_purchase_order"){
			get_purchaseorder($dbcon,$_POST['purchaseorder_id']);
			$arr=send_whatsapp_po($dbcon,$_POST['purchaseorder_id']);
			echo $arr;
		}
		else if(strtolower($POST['mode'])== "convert_qty")
		{
			$row=array();
			if($POST["type"]=="1"){
				$type="conv_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
			}else{
				$ret_qty="0";
			}
				//var_dump($ret_qty);
			$ret_qty_new=number_format($ret_qty, 3, ".", "");
					//$ret_qty=$ret_qty;
				//	echo $ret_qty;
			$row['show_qty']=$ret_qty_new;
			$row['hide_qty']=$ret_qty;
			echo json_encode($row);
		}

		//maulik Start
		else if(strtolower($POST['mode']) == "po_trn_tbl") {
			$qt_qry="select trn.*,product.product_name,product.product_type,cat.unit_name,cat_con.unit_name as conv_unit_name,tc.cat_name,product.product_icode,product.drawing_id,dr.drawing_number,(select IFNULL(sum(product_qty),0) as qty from tbl_grn_sub_trn as chtrn where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty from tbl_purchaseordertrn as trn
			left join unit_mst as cat on cat.unitid=trn.unit_id
			left join unit_mst as cat_con on cat_con.unitid=trn.conv_unit_id
			left join product_mst as product on product.product_id=trn.product_id 
			left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
			left join tbl_category as tc on product.product_category=tc.cat_id 
			where trn.purchaseordertrn_status=0 and trn.used_status=0 and trn.purchaseorder_id=".$POST['po_id'];
			
			$qt_exe = $dbcon->query($qt_qry);
			//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<td><strong>#</strong></td>
			<td><strong>Product Type</strong></td>
			<td><strong>Product Name</strong></td>
			<td><strong>Product Category</strong></td>
			<td><strong>HSN Code</strong></td>
			<td><strong>Qty</strong></td>
			<td><strong>Used Qty</strong></td>
			<td><strong>Due Qty</strong></td>
			</tr>
			</thead>
			<tbody>
			';
			
			$setconf="select * from tbl_company_configuration where company_id=".$_SESSION['company_id'];
			$set_conf=brp_mysqli_fetch_assoc($dbcon->query($setconf));
			$purchase_pro_search = $set_conf['purchase_pro_search'];
			$pro_search=explode(",", $purchase_pro_search);
			
			if(brp_mysqli_num_rows($qt_exe)>0){
				while($rel=brp_mysqli_fetch_assoc($qt_exe)){
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
					$currency_id = $rel['currency_id'];
					
					if($rel['unit_id']!=$rel['conv_unit_id']){
						$show_qty=$rel['product_qty']." ".$rel['unit_name']." </br> ".$rel['product_conv_qty']." ".$rel['conv_unit_name'];
					}else{
						$show_qty=$rel['product_qty']." ".$rel['unit_name'];
					}
					
					if(in_array('drawing',$pro_search)){
						$drawing_number = " (".$rel['drawing_number'].")";
					}else{
						$drawing_number = '';
					}
					if(in_array('item',$pro_search)){
						$item_code = " (".$rel['product_icode'].")";
					}else{
						$item_code = '';
					}
					$done_qty = $rel['done_qty'];
					$due_qty  = $rel['product_qty']-$rel['done_qty'];
					$str .= '
					<tr>
					<td><input type="checkbox" name="po_trn_id[]" value="'.$rel['purchaseordertrn_id'].'"></td>
					<td>'.get_pro_type_name($rel['product_type']).'</td>
					<td>'.$rel['product_name'].' '.$drawing_number.' '.$item_code.' '.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'</td>
					<td>'.$cat_name.'</td>
					<td>'.$rel['product_hsn_code'].'</td>
					<td>'.$rel['product_qty'].'</td>
					<td>'.$done_qty.'</td>
					<td>'.$due_qty.'</td>
					</tr>';
				}
			}else{
				$str .='<tr>
				<td colspan="10" style="text-align:center">No Data Yet...!!!</td>
				</tr>';
			}
			$str.='</tbody></table></div>
			<hr/>
			';
			
			$qt_rel['po_trn_tbl'] = $str;
			
			echo json_encode($qt_rel);
		}
		else if(strtolower($POST['mode'])== "full_poshort_close"){
			$log_s['short_close_status'] = 2;
			$updateid=update_record("tbl_log_po_short_close", $log_s, "aproove_status=0 and po_id=".$POST['po_id'], $dbcon);

			$query = "select trn.*,po.purchaseorder_no,(select IFNULL(sum(product_qty),0) as qty from tbl_grn_sub_trn as chtrn where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty from tbl_purchaseordertrn as trn 
			left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
			where trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status=1 and po.company_id=".$_SESSION['company_id']." and trn.purchaseorder_id=".$POST['po_id'];

			$que_e = $dbcon->query($query);

			while($row = mysqli_fetch_array($que_e)){
				$due_qty = $row['product_qty']-$row['done_qty'];
				$info['short_close_qty'] 	= $due_qty;
				$info['short_close_reason'] = $_POST['close_reson'];
				$info['shortclose_status']	= 1;
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];	
				$info['company_id']			= $_SESSION['company_id'];
				$updateid=update_record("tbl_purchaseordertrn", $info, "purchaseordertrn_id=".$row['purchaseordertrn_id'], $dbcon);

				$log_entry['po_no'] 			= $row['purchaseorder_no'];
				$log_entry['po_id'] 			= $row['purchaseorder_id'];
				$log_entry['po_trn_id'] 		= $row['purchaseordertrn_id'];
				$log_entry['product_id']		= $row['product_id'];
				$log_entry['short_close_qty']	= $due_qty;
				$log_entry['short_close_reason']= $_POST['close_reson'];
				$log_entry['date'] 				= date("Y-m-d");
				$log_entry['cdate'] 			= date("Y-m-d H:i:s");
				$log_entry['user_id']			= $_SESSION['user_id'];
				$log_entry['company_id']		= $_SESSION['company_id'];

				$inserid=add_record("tbl_log_po_short_close", $log_entry, $dbcon);
			}
		}
		else if(strtolower($POST['mode'])== "manual_poshort_close"){
			$po_trn_id = implode(",",$POST['po_trn_id']);

			$log_s['short_close_status'] = 2;
			$updateid=update_record("tbl_log_po_short_close", $log_s, "po_trn_id=".$po_trn_id, $dbcon);

			$query = "select trn.*,po.purchaseorder_no,(select IFNULL(sum(product_qty),0) as qty from tbl_grn_sub_trn as chtrn where chtrn.status=0 and chtrn.purchaseordertrn_id=trn.purchaseordertrn_id and trn.product_id=chtrn.product_id) as done_qty from tbl_purchaseordertrn as trn 
			left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
			where trn.used_status=0 and trn.purchaseordertrn_status=0 and trn.purchaseorder_id!=0 and po.po_approval_status=1 and po.company_id=".$_SESSION['company_id']." and trn.purchaseorder_id=".$POST['po_id']." and trn.purchaseordertrn_id in (".$po_trn_id.")";

			$que_e = $dbcon->query($query);

			while($row = mysqli_fetch_array($que_e)){
				$due_qty = $row['product_qty']-$row['done_qty'];
				$info['short_close_qty'] 	= $due_qty;
				$info['short_close_reason'] = $_POST['close_reson'];
				$info['shortclose_status']	= 1;
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];	
				$info['company_id']			= $_SESSION['company_id'];
				$updateid=update_record("tbl_purchaseordertrn", $info, "purchaseordertrn_id=".$row['purchaseordertrn_id'], $dbcon);

				$log_entry['po_no'] 	= $row['purchaseorder_no'];
				$log_entry['po_id'] 	= $row['purchaseorder_id'];
				$log_entry['po_trn_id'] = $row['purchaseordertrn_id'];
				$log_entry['product_id']= $row['product_id'];
				$log_entry['short_close_qty']	= $due_qty;
				$log_entry['short_close_reason']= $_POST['close_reson'];
				$log_entry['date'] 		= date("Y-m-d");
				$log_entry['cdate'] 	= date("Y-m-d H:i:s");
				$log_entry['user_id']	= $_SESSION['user_id'];
				$log_entry['company_id']= $_SESSION['company_id'];
				$log_entry['branch_id'] = $_SESSION['branch_id'];

				$inserid=add_record("tbl_log_po_short_close", $log_entry, $dbcon);
			}
		}
		else if(strtolower($POST['mode']) == "po_close_reason") {
			$query = "select DISTINCT short_close_reason  from tbl_purchaseordertrn where purchaseorder_id=".$POST['po_id'];

			$que_e = $dbcon->query($query);
			$str ='';
			$str .='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<td><strong>Sr.no</strong></td>
			<td><strong>Short Close Reason</strong></td>
			</tr>
			</thead>
			<tbody>';
			if(mysqli_num_rows($que_e)>0){
				$i = 1;
				while($row = mysqli_fetch_array($que_e)){
					$str .= '<tr>
					<td><strong>'.$i.'</strong></td>
					<td><strong>'.$row['short_close_reason'].'</strong></td>
					</tr>';
					$i++;
				}
			}else{
				$str .='<tr>
				<td style="text-align:center" colspan="2"></td>
				</tr>';
			}
			$str .= '</tbody></table></div>';
			$qt_rel['f_po_close_reason'] = $str;

			echo json_encode($qt_rel);
		}
		else if(strtolower($POST['mode']) == "m_po_close_reason") {
			$query = "select DISTINCT short_close_reason  from tbl_purchaseordertrn where purchaseorder_id=".$POST['po_id'];

			$que_e = $dbcon->query($query);
			$str ='';
			$str .='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<thead>
			<tr>
			<td><strong>Sr.no</strong></td>
			<td><strong>Short Close Reason</strong></td>
			</tr>
			</thead>
			<tbody>';
			if(mysqli_num_rows($que_e)>0){
				$i = 1;
				while($row = mysqli_fetch_array($que_e)){
					$str .= '<tr>
					<td><strong>'.$i.'</strong></td>
					<td><strong>'.$row['short_close_reason'].'</strong></td>
					</tr>';
					$i++;
				}
			}else{
				$str .='<tr>
				<td style="text-align:center" colspan="2"></td>
				</tr>';
			}
			$str .= '</tbody></table></div>';
			$qt_rel['m_po_close_reason'] = $str;

			echo json_encode($qt_rel);
		}

		//maulik end
			//pathik end
		//Maulik Start
		else if(strtolower($POST['mode']) == "vender_detail") {
			$qt_qry="select * from tbl_ledger where l_id=".$POST['vender_id'];
			$qt_rel=brp_mysqli_fetch_assoc($dbcon->query($qt_qry));
			
			if($POST['product_id']!=""){
				$pr_qry="select product_name from product_mst where product_id=".$POST['product_id'];
				$pr_rel=brp_mysqli_fetch_assoc($dbcon->query($pr_qry));
			}
			
			//var_dump($pr_qry);
			//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Company Name : </strong> '.$qt_rel['company_name'].'</td>
			</tr>
			<tr>
			<td><strong>Mobile : </strong> '.$qt_rel['cust_mobile'].'</td>
			<td><strong>Email : </strong> '.$qt_rel['cust_email'].'</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Address:</strong> '.$qt_rel['m_address'].'</td>
			</tr>';
			$str.='</table></div>
			<hr/>
			';
			
			$qt_rel['vender_detail'] = $str;
			$qt_rel['vender_name'] = $qt_rel['l_name'];
			$qt_rel['product_name'] = $pr_rel['product_name'];
			echo json_encode($qt_rel);
		}
		else if(strtolower($POST['mode']) == "price_detail") {
			$price_list = get_pricelist_po($dbcon,$POST['vender_id'],"");
			
			$qt_rel['product_detail'] = $price_list;
			echo json_encode($qt_rel);
		}
		else if(strtolower($POST['mode']) == "product_price_detail") {
			$price_list = get_pricelist_po($dbcon,$POST['vender_id'],$POST['product_id']);
			
			$qt_rel['product_detail'] = $price_list;
			echo json_encode($qt_rel);
		}
		//Maulik End
		else if(strtolower($POST['mode'])== "delivary_date_model_open")
		{
			if(empty($POST['trn_id'])){
				echo '<input type="hidden" name="count" id="count" value="1" />
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
				<tr id="field">
				<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
				<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
				<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
				</tr>
				<tr id="field1">
				<td   class="text-center" style="vertical-align:center;">
				<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="'.$POST["qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
				</td>
				<td	 class="text-center;" style="vertical-align:center;">
				<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
				</td>
				</tr>
				</table>';
			}else{
				$qry="SELECT * FROM `tbl_purchaseorder_delivery_date` WHERE po_delivery_date_status=0 and purchaseordertrn_id=".$POST['trn_id']." order by po_delivery_date_id";
				$row=$dbcon->query($qry);
				$cnt=brp_mysqli_num_rows($row);
				if($cnt>0){
					$i=1;
					echo '<input type="hidden" name="count" id="count" value="'.$cnt.'" />
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
					<tr id="field">
					<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
					<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
					<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
					</tr>';
					
					while($tax=brp_mysqli_fetch_assoc($row))
					{
						$date=date('d-m-Y',strtotime($tax['delivery_date']));
						echo '<tr id="field'.$i.'">
						<td   class="text-center" style="vertical-align:center;">
						<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date'.$i.'" name="delivery_date[]" placeholder="Delivery Date" value="'.$date.'" onkeyup="qty_wise_date_validation('.$i.');" >
						</td>
						<td	 class="text-center;" style="vertical-align:center;">
						<input type="text" class="form-control delivery_qty" id="delivery_qty'.$i.'" name="delivery_qty[]" placeholder="'.$tax["product_qty"].'" value="'.$tax["product_qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation('.$i.');" />
						</td>
						<td	 class="text-center;" style="vertical-align:center;">
						<input type="hidden" name="arry_sr[]" id="arry_sr'.$i.'" value="'.$i.'" />
						<input type="hidden" class="arry_edit" name="arry_edit[]" id="arry_edit'.$i.'" value="'.$tax["po_delivery_date_id"].'" />';
						if($i!=1){
							echo '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="remove_dilivary_date('.$i.');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>';
						}
						echo '</td>
						</tr>';
						$i++;
					}
					echo '</table>';
				}else{
					echo '<input type="hidden" name="count" id="count" value="1" />
					<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped " id="mix_loose_material_table">
					<tr id="field">
					<th width="60%"  class="text-center" style="vertical-align:center;">Date</th>
					<th width="30%"  class="text-center;" style="vertical-align:center;">Qty</th>
					<th width="5%"  class="text-center;" style="vertical-align:center;"></th>
					</tr>
					<tr id="field1">
					<td class="text-center" style="vertical-align:center;">
					<input type="text" class="form-control default-date-picker delivery_date" id="delivery_date1" name="delivery_date[]" placeholder="Delivery Date" onkeyup="qty_wise_date_validation(1);" >
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="text" class="form-control delivery_qty" id="delivery_qty1" name="delivery_qty[]" placeholder="'.$POST["qty"].'" onchange="validate_dilivary_date();" onkeyup="qty_wise_date_validation(1);" />
					</td>
					<td	 class="text-center;" style="vertical-align:center;">
					<input type="hidden" name="arry_sr[]" id="arry_sr" value="1" />
					</td>
					</tr>
					</table>';
				}
			}
		}
		else if(strtolower($POST['mode']) == "get_revise_po_no") {
			$get_rev_cnt="select count(purchaseorder_id) as ttl_cnt,(select purchaseorder_no from tbl_purchaseorder where purchaseorder_id=".$POST['start_purchaseorder_id'].") as qt_no from tbl_purchaseorder where purchase_status=0 and start_purchaseorder_id=".$POST['start_purchaseorder_id'];
				$rev_cnt=mysqli_fetch_assoc($dbcon->query($get_rev_cnt));
				$row['purchaseorder_no'] = $rev_cnt['qt_no']."/R-".$rev_cnt['ttl_cnt'];

				echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "copy_prev_purchase_trn") {
			$del_trn=delete_record('tbl_purchaseordertrn',"purchaseordertrn_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
			$prev_purchaseorder_id=$_POST['prev_purchaseorder_id'];

			$sql = $dbcon->query("SELECT * FROM `tbl_purchaseordertrn` WHERE purchaseordertrn_status=0 and purchaseorder_id=".$prev_purchaseorder_id);
			while($row=brp_mysqli_fetch_assoc($sql)){
				$info1['product_type']			= $row['product_type'];
				$info1['product_id']			= $row['product_id'];
				$info1['description']			= $row['product_des'];
				$info1['product_hsn_code']		= $row['product_hsn_code'];
				$info1['product_qty']			= $row['product_qty'];
				$info1['product_conv_qty']		= $row['product_conv_qty'];
				$info1['sqr_ft']				= $row['sqr_ft'];
				$info1['unit_id']				= $row['unit_id'];
				$info1['conv_unit_id']			= $row['conv_unitid'];
				$info1['product_rate']			= $row['product_rate'];
				$info1['product_discount']		= $row['product_discount'];
				$info1['discount_per']			= $row['discount_per'];
				$info1['formulaid']				= $row['formulaid'];
				$info1['product_amount']		= $row['taxable_value'];
				$info1['sel_tax']				= $row['sel_tax'];
				$info1['formula_tax_id']		= $row['formula_tax_id'];
				$info1['total']					= $row['product_amount'];
				$info1['product_amount_tax']	= $row['product_amount_tax'];
				$info1['user_id']				= $_SESSION['user_id'];
				$info1['company_id']			= $_SESSION['company_id'];
				$info1['currency_id']			= $row['currency_id'];
				$info1['conversion_rate']		= $row['conversion_rate'];
				$info1['product_currency_rate']	= $row['product_currency_rate'];
				$info1['product_currency_amount']= $row['product_currency_amount'];
				$info1['product_currency_amount_tax']= $row['product_currency_amount_tax'];
				$info1['currency_total']		= $row['currency_total'];
				$info1['prev_purchaseordertrn_id']= $row['purchaseordertrn_id'];
				$info1['purchaseordertrn_status']= 3;

				$table='tbl_purchaseordertrn';$tableid='purchaseordertrn_id';
				$inserid=add_record($table, $info1, $dbcon, $row['branch_id']);				

				$dbcon->query("UPDATE `tbl_purchaseordertrn` SET `used_status` = '1' WHERE `purchaseordertrn_id` = '".$row['purchaseordertrn_id']."'");

				$purchase_tax_id=$dbcon->query("INSERT INTO `tbl_tax_trn`(`tx_tax_id`, `tx_tax_value`, `tx_taxable_value`, `tx_transaction_id`, `tx_transaction_type`, `tx_tran_type_id`, `tx_product_id`, `tx_status`, `user_id`, `company_id`, `branch_id`) SELECT  `tx_tax_id`, `tx_tax_value`, `tx_taxable_value`, '".$inserid."', `tx_transaction_type`, `tx_tran_type_id`, `tx_product_id`, `tx_status`, `user_id`, `company_id`, `branch_id` FROM `tbl_tax_trn` WHERE `tx_status` = 0 AND `tx_transaction_id` = '".$row['purchaseordertrn_id']."'");

				// $dbcon->query("UPDATE `tbl_purchaseorder_delivery_date` SET `purchaseordertrn_id`		='".$inserid."' WHERE `po_delivery_date_status` = 0 AND `purchaseordertrn_id` = '".$row['purchaseordertrn_id']."'");

				$purchase_delivery_id=$dbcon->query("INSERT INTO `tbl_purchaseorder_delivery_date`(`purchaseordertrn_id`, `delivery_date`, `product_qty`, `used_qty`, `grn_status`, `unit_id`, `po_delivery_date_status`, `user_id`, `company_id`, `branch_id`) SELECT  '".$inserid."', `delivery_date`, `product_qty`, `used_qty`, `grn_status`, `unit_id`, `po_delivery_date_status`, `user_id`, `company_id`, `branch_id` FROM `tbl_purchaseorder_delivery_date` WHERE po_delivery_date_status=0 AND purchaseordertrn_id='".$row['purchaseordertrn_id']."'");
			}
			echo $prev_purchaseorder_id;
		}
		else if(strtolower($POST['mode']) == "load_process_out_side") {
			$eid = "";
			if($POST['proc'] !=""){
				$eid = $POST['proc'];
			}
			$prod_id = $POST['prod_id'];
			$str=load_process_out_side($dbcon,$prod_id,$eid);
			
			$qt_rel['process_list'] = $str;
			echo json_encode($qt_rel);
		}
		else if(strtolower($POST['mode'])== "load_payment_terms"){
			$vendor_id = $_POST['vendor_id'];
			$pay_terms="select pay_terms from tbl_ledger where l_id=$vender_id";
			var_dump($pay_terms);
			$rel1=mysqli_fetch_assoc($dbcon->query($pay_terms));
			$resp = getpaymentterms($dbcon,$rel1['pay_terms']);
			$row['resp_html'] = $resp;
			echo json_encode($row);
		}
	}
}

function get_purchaseorder($dbcon,$purchaseorder_id){
	$query="select po.*,state.state_name,modesup.mode_dispatch,payterms.payment_terms as payment_term,l.l_name as vender_name,country.country_name,l.m_address as vender_address,l.gst_no as tin_no,l.cust_mobile as vender_mobile,l.stateid,state.gst_state_code,city.city_name from tbl_purchaseorder as po inner join tbl_ledger as l on l.l_id=po.vender_id
	left join country_mst as country on country.countryid=l.countryid
	left join pay_terms as payterms on payterms.terms_id=po.payment_terms
	left join mode_of_dispatch as modesup on modesup.mode_dis_id=po.mode_of_dispatch
	left join state_mst as state on state.stateid=l.stateid
	left join city_mst as city on city.cityid=l.cityid
	where po.purchaseorder_id=$purchaseorder_id";
	$rel=mysqli_fetch_assoc($dbcon->query($query));

	$_SESSION['invoice_no']=$rel['invoice_no'];		

	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$rel['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));	
	$order_date='';
	if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
	{
		$order_date=date('d-m-Y',strtotime($rel['order_date']));
	}

	$cons_company_name	= $rel['company_name'];
	$cons_cust_address	= $rel['cust_address'];
	$cons_gst_no		= $rel['gst_no'];
	$cons_state_name	= $rel['state_name'];
	$cons_gst_state_code= $rel['gst_state_code'];
	$cons_city_name		= $rel['city_name'];
	$cons_country_name	= $rel['country_name'];

	if(!empty($rel['consignee_id']))
	{	
		$consignee="select * from tbl_custmer_consignee as cust 
		left join country_mst as country on country.countryid=cust.countryid
		left join state_mst as state on state.stateid=cust.stateid 
		left join city_mst as city on city.cityid=cust.cityid where cust_id=".$rel['consignee_id'];
		$cons_data=mysqli_fetch_assoc($dbcon->query($consignee));	
		$cons_company_name=$cons_data['company_name'];
		$cons_cust_address=$cons_data['cust_address'];
		$cons_gst_no=$cons_data['gst_no'];
		$cons_state_name=$cons_data['state_name'];
		$cons_gst_state_code=$cons_data['gst_state_code'];
		$cons_city_name=$cons_data['city_name'];
		$cons_country_name=$cons_data['country_name'];

	}
	$user_qry = "select user_name,user_mail,user_phone from users where user_id=".$_SESSION['user_id']." and company_id=".$rel['company_id'];
	$user_data = mysqli_fetch_assoc($dbcon->query($user_qry));
	/* Check Discount is On or off Start */
	if($set_head['show_disc']=='1'){
		$colspan=5;
		$dynamicwidth=40;
	}else{
		$colspan=6;
		$dynamicwidth=46;
	}  
	$header ='<img src="'.DOMAIN_F.LOGO.'hermatic-logo.jpg'.'" style="" />';

	$approve_status='';
	if($rel['approve_status']=='0'){
		$approve_status=' (DRAFT)';
	}

	$html ='<html>
	<head>					
	<title>'.$form.' - '.$rel['purchaseorder_no'].'</title>
	<style type="text/css">
	/*
	.page{
		width:8.27in;
		height:10.69in;
		}*/
		.nextpage
		{
			page-break-after: always;
		}
		table{
			border-collapse:collapse;
			width:100%;
		}

		table tr,td{
			border:1px solid #000 !important;
			/*page-break-inside:avoid;*/
		}
		.quot_annex_content_div table tr,td{
			padding:5px;
		}

		</style>
		</head>
		<body>

		<htmlpageheader name="otherpages" style="display:none">
		<div style="text-align:center">'.$header.'</div>
		</htmlpageheader>
		<!--<htmlpagefooter name="otherpages_footer" style="display:none">
		<div style="text-align:center">'.$footer.'</div>
		</htmlpagefooter>-->
		<sethtmlpageheader name="otherpages" value="on" show-this-page="0"/>
		<div>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="5" cellspacing="5">
		<tr>
		<td colspan="3" style="text-align:center; font-size:15px; font-weight:bold;">'.$form.'</td>
		</tr>
		<tr>
		<td rowspan="6" style="text-align:left; vertical-align:top; border:1px solid; width:50%;">
		<strong>To, <br>'.$rel['vender_name'].'</strong><br/>'.$rel['vender_address'].'<br/>'.$rel['city_name'].','.$rel['state_name'].','.$rel['country_name'].'<br>GST NO. : '.$rel['tin_no'].'<br>Kind Attn. : '.$rel['vender_name'].'
		</td>
		<td style="text-align:left;border:1px solid;width:20%;"><strong>Purchase Order No</strong></td>
		<td style="text-align:left;border:1px solid;width:30%;font-size:14px"><strong>'.$rel['purchaseorder_no'].'</strong></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">Purchase Order Date</td>
		<td style="text-align:left;border:1px solid;"> '.date("d-M-Y",strtotime($rel['purchaseorder_date'])).'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Quotation Ref No</td>
		<td style="text-align:left;border:1px solid;"> '.$rel['quotation_no'].'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Quotation Ref Date</td>
		<td style="text-align:left;border:1px solid;"> '.date('d-M-Y', strtotime($rel['quotation_date'])).'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Vendor Code</td>
		<td style="text-align:left;border:1px solid;"></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">Project Code</td>
		<td style="text-align:left;border:1px solid;"></td>
		</tr>
		<tr style="border-bottom: none;">
		<td rowspan="6" style="text-align:left; vertical-align:top; border:1px solid; width:50%;border-bottom: none;">
		<strong>Ship To, <br>'.$rel['vender_name'].'</strong><br/>'.$rel['vender_address'].'<br/>'.$rel['city_name'].','.$rel['state_name'].','.$rel['country_name'].'
		</td>
		<td style="text-align:left;border:1px solid;">PR No</td>
		<td style="text-align:left;border:1px solid;"></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">PO Valid Till</td>
		<td style="text-align:left;border:1px solid;"></td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;">Delivery Date</td>
		<td style="text-align:left;border:1px solid;"> '.date("d-M-Y",strtotime($rel['purchaseorder_due_date'])).'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Payment Terms</td>
		<td style="text-align:left;border:1px solid;"> '.$rel['payment_terms'].'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Buyers Name</td>
		<td style="text-align:left;border:1px solid;"> '.$user_data['user_name'].'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;"> Buyers Mobile No</td>
		<td style="text-align:left;border:1px solid;"> '.$user_data['user_phone'].'</td>
		</tr>
		<tr>
		<td style="text-align:left;border:1px solid;border-top: none;">'.$set_head['company_name'].' GST No. : '.$set_head['vatno'].'</td>
		<td style="text-align:left;border:1px solid;"> Buyers Email</td>
		<td style="text-align:left;border:1px solid;"> '.(strtolower($user_data['user_mail'])).'</td>
		</tr>
		<tr style="border-bottom: none;">
		<td colspan="3"><strong> We are pleased to place this Purchase/ Service Order for the supply of the following, subject to the terms and conditions given in annexure. </strong></td>
		</tr>
		</table>
		<table style="font-size:12px;border-collapse: collapse;width:100%;" cellpadding="3" cellspacing="3">
		<thead>
		<tr>
		<th style="width:5%;text-align:center;border:1px solid;">Sr.<br/>No.</th>
		<th style="width:25%;text-align:center;border:1px solid;">Item Description</th>
		<th style="width:5%;text-align:center;border:1px solid;">HSN Code</th>
		<th style="width:8%;text-align:center;border:1px solid;">Qty</th>
		<th style="width:10%;text-align:center;border:1px solid;">Rate</th>';
		if($set_head['show_disc']=='1'){ 
			$html.='<th style="width:7%;text-align:center;border:1px solid;">Less. Disc.</th>';
		}
		$html.='<th style="width:10%;text-align:center;border:1px solid;">Amount</th>
		<th style="width:5%;text-align:center;border:1px solid;">Rate(%)</th>
		<th style="width:10%;text-align:center;border:1px solid;">Tax Value</th>
		<th style="width:15%;text-align:right;border:1px solid;">Total Price</th>
		</tr>
		</thead>
		<tbody>';
		$qry="select trn.*,product.*,product.product_desc as scode,per.unit_name,per1.unit_name as base_unit_name,per2.unit_name as conv_unit_name,group_concat(tax.tax_value) as tax_val,group_concat(tax.tax_name) as tax_name FROM `tbl_purchaseordertrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=trn.unit_id 
		left join unit_mst as per1 on per1.unitid=product.product_base_unit 
		left join unit_mst as per2 on per2.unitid=product.product_conv_unit 
		left join `formula_mst` as ftax on ftax.formulaid=trn.formulaid left join tbl_tax as tax on find_in_set(tax.tax_id,ftax.tax_id)
		where purchaseordertrn_status=0 and purchaseorder_id=".$rel['purchaseorder_id']." group by purchaseordertrn_id order by purchaseordertrn_id";
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;$totalqty=0;$charges_qty=0;
		$cnt=mysqli_num_rows($result);
		while($row=mysqli_fetch_assoc($result))
		{
			if($row['product_base_unit']!=$row['product_conv_unit']){
			//base_unit_name,per2.unit_name as conv_unit_name
				if($row['unit_id']==$row['product_base_unit']){
					$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"conv_unit");
					$uname=$row['conv_unit_name'];
				}else{
					$cqty=convert_stock($dbcon,$row['product_qty'],$row['product_id'],"base_unit");
					$uname=$row['base_unit_name'];
				}
			}
			$tax_arr=explode(",",$row['tax_val']);
		//tax summary calculation start
			if(!empty($row['tax_val']))
			{
				$tax_num=explode(",",$row['tax_val']);
				$tax_name=explode(",",$row['tax_name']);
				$total_net_rate=($row['product_qty']*$row['product_rate'])-$row['discount'];
				for($j=0;$j<count($tax_num);$j++)
				{
					if(!in_array($tax_name[$j],$tax['per']))
					{
						$tax['per'][]=$tax_name[$j];
					}
					$tax['per_total'][$tax_name[$j]]+=$total_net_rate*$tax_num[$j]/100;
				}
			}
			$total_taxs=$tax_arr[0]+$tax_arr[1];
		//tax summary calculation end
			$taxable_amt=$row['total']-$row['product_amount'];

			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$i.'</td>
			<td style="text-align:left;border:1px solid;vertical-align:top;">
			<strong>'.$row['product_name'].'</strong><br/>
			'.nl2br($row['description']).'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$row['product_hsn'].'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">
			'.$row['product_qty'].''.$row['unit_name'].'
			</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($row['product_rate'],2,".","").'</td>';
			if($set_head['show_disc']=='1'){
				$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($row['discount_per'],2,".","").'</td>';
			}
			$html.='<td style="text-align:center;border:1px solid;vertical-align:top;">'.number_format($row['product_amount'],2,".","").'</td>
			<td style="text-align:center;border:1px solid;vertical-align:top;">'.$total_taxs.' %</td>
			<td style="text-align:right;border:1px solid;vertical-align:top;">'.number_format($taxable_amt,2,".","").'</td>
			<td style="text-align:right;border:1px solid;vertical-align:top;">'.number_format($row['total'],2,".","").'</td>
			</tr>';
			$i++; 
			$totalqty=$totalqty+$row['product_qty']-$charges_qty;$totalsqr=$totalsqr+$row['sqr_ft']-$charges_qty1;
			$total_product_amount+=$row['product_amount'];
			$totaltaxable+=$taxable_amt;
			$total+=$row['total'];
		}
		$pr=10-$cnt;
		for($j=0; $j<$pr; $j++){
			$html.='<tr style="border:none;border-left:1px solid;border-right:1px solid;">
			<td style="border:none;border-left:1px solid;border-right:1px solid;height:25px;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			<td style="border:none;border-left:1px solid;border-right:1px solid;"></td>
			</tr>';
		}

		$html.='<tr>
		<td colspan="5" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">Basic PO Amount: '. number_format($total_product_amount,2,".","").'</td>
		<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Basic</td>
		<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($total_product_amount,2,".","").'</td>
		</tr>';
		$chkrow=$dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
			left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
			where trn.purchaseorder_id='".$purchaseorder_id."' and tx_status=0 and tx_transaction_type='purchase_order' group by tx_tax_id");
		$getrows=mysqli_num_rows($rt);
		$rt=$dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
			left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
			left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
			where trn.purchaseorder_id='".$purchaseorder_id."' and tx_status=0 and tx_transaction_type='purchase_order' group by tx_tax_id");
		$k=0;
		while($rel1=mysqli_fetch_assoc($rt)){
			if($getrows>2){
				$rows=3;
			}else{
				$rows=2;
			}
			$rt1=$dbcon->query("select mst.*,t.tax_name,sum(tx_taxable_value) as tamo from tbl_tax_trn as mst 
				left join tbl_tax as t on t.tax_id=mst.tx_tax_id 
				left join tbl_purchaseordertrn as trn on trn.purchaseordertrn_id=mst.tx_transaction_id
				where trn.purchaseorder_id='$purchaseorder_id' and mst.tx_tax_id=".$rel1['tx_tax_id']." and tx_status=0 and tx_transaction_type='purchase_order' ");
			$rel122=mysqli_fetch_assoc($rt1);
			$html.='<tr>';
			if($k==0){
				$html.='<td colspan="5" rowspan="'.$rows.'" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">GST Amount: '. number_format($totaltaxable,2,".","").'</td>';
			}
			$html.='<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">'.$rel1['tax_name'].' Amount</td>
			<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($rel122['tamo'],2,".","").'</td>
			</tr>';
			$k++;
		}
		$html.='<tr>
		<td colspan="5" rowspan="2" style="text-align:left;border:1px solid;color:Black; font-weight: bold;">Total PO Amount: '. number_format($rel['g_total'],2,".","").'</td>
		<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Tax Amount</td>
		<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($totaltaxable,2,".","").'</td>
		</tr>
		<tr>
		<td colspan="4" style="text-align:right;border:1px solid; font-weight: bold;">Total Amount</td>
		<td style="text-align:right;border:1px solid;font-weight: bold;">'.number_format($rel['g_total'],2,".","").'</td>
		</tr>';
		$html.='
		<tr>
		<td colspan="5" rowspan="2" style="height:80px; text-align:left; vertical-align:top; border-left:1px solid; border-bottom:1px solid; font-weight: bold;"><strong>Terms and Conditions:</strong><br> '.$set_head['po_condition'].'</td>
		<td colspan="5" style="text-align:center;vertical-align:top;border-bottom:1px solid;border-right:1px solid;font-weight: bold;height:80px;">For, '.$set_head['company_name'].'</td>
		</tr>
		<tr>
		<td colspan="5"><center style="vertical-align:bottom;">Authorised Signatory</center></td>
		</tr>
		</tbody></table>
		<div style="clear:both;"></div>
		</div>
		<!--page1 end-->';

		/* Get Terms And Condition Start */
		$terms_qry="select qtrm.*,mst.tc_name from tbl_quotation_terms_trn as qtrm 
		left join tbl_terms_condition as mst on mst.tc_id=qtrm.tc_id
		where qtrm.quotation_terms_trn_status=0 and qtrm.quotation_id=".$rel['quotation_id']." order by qtrm.tc_priority";
		$terms_qry_rs=$dbcon->query($terms_qry);
		if(mysqli_num_rows($terms_qry_rs)){
			$html.='<center class="nextpage"></center>
			<h3 style="text-align:center;">Terms & Conditions for Sales Quotation No : <u>'.$rel['quotation_no'].'</u></h3>
			<div><table width="100%" style="font-size:12px;border-collapse: collapse;width:100%;overflow:wrap;" cellpadding="3" cellspacing="3"><tbody>';
			$t=1;
			while($term_rel=mysqli_fetch_assoc($terms_qry_rs)){
				$string=(nl2br($term_rel['tc_details']));

				$html.='<tr>
				<td width="5%" style="width:5%;text-align:center;border:1px solid;padding:5px;">'.$t.'</td>
				<td width="25%" style="width:25%;text-align:left;border:1px solid;padding:5px;">'.$term_rel['tc_name'].'</td>
				<td width="70%" style="width:70%;text-align:left;border:1px solid #000;padding:5px;">'.$string.'</td>
				</tr>';
				$t++;
			}
			$html.='</tbody></table></div>';	
		}
		/* Get Terms And Condition Start */

		$html.='<!--<sethtmlpagefooter name="otherpages_footer" value="on" />-->
		</body>
		</html>';
		// echo $html;exit;
		ob_end_clean();
		include("../view/export/mpdf/mpdf.php");
		$mpdf=new mPDF('','A4','0','calibri','10','10','25','10','1','1');
		$mpdf->defaultheaderfontsize = 10; /* in pts */
		$mpdf->defaultheaderfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultheaderline = 1; /* 1 to include line below header/above footer */
		$mpdf->defaultfooterfontsize = 10; /* in pts */
		$mpdf->defaultfooterfontstyle = B; /* blank, B, I, or BI */
		$mpdf->defaultfooterline = 1; /* 1 to include line below header/above footer */
		$mpdf->SetHTMLHeader($header);
		//$mpdf->SetHTMLFooter($footer);

		//Show page number
		$mpdf->pagenumPrefix = ' ';
		$mpdf->pagenumSuffix = ' / ';
		$mpdf->nbpgPrefix = ' ';
		$mpdf->nbpgSuffix = ' pages';
		$mpdf->SetFooter('{PAGENO}{nbpg}');

		$mpdf->SetWatermarkText();
		$mpdf->showWatermarkText = true;
		$mpdf->allow_charset_conversion=true;
		$mpdf->charset_in='UTF-8';
		$mpdf->WriteHTML($html);
		// $mpdf->Output();
		$mpdf->Output('../view/upload/quotation_pdf_file/Purchase_Order_'.$purchaseorder_id.'.pdf','f');
		ob_clean();
		return $purchaseorder_id;
	}
	function get_product_tax($dbcon,$product_amount,$formulaid)
	{
		$qry="SELECT formula.*,tax.*,formula.tax_id as ftax FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
		$row=$dbcon->query($qry);
		$rate_total=$total=$product_amount;
		$i=1;$tax_total_amount=0;
		while($tax=brp_mysqli_fetch_assoc($row))
		{	
			if($i==1){
				$info['tax_id']=$tax['ftax'];
			}
			$info['tax_name'.$i]=$tax['tax_name'];
			$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
			$rate_total+=$tax_amount;
			$tax_total_amount+=$info['tax_amount'.$i];
			$i++;
		}
		for($j=$i;$j<=3;$j++)
		{
			$info['tax_name'.$i]='';
			$info['tax_amount'.$i]='';


		}
		$info['total']=$rate_total;
		$info['tax_total_amount']=$tax_total_amount;
		return $info;
	}
	function check_po_rates_status($dbcon,$purchaseorder_id){
		$sel_pro_rate = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and purchaseorder_id=".$purchaseorder_id;$rate_flag=false;
		$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);
		while($sel_pro_rate_rel=brp_mysqli_fetch_assoc($sel_pro_rate_rs)){
			$pro_mst_rate = get_pro_field($dbcon, $sel_pro_rate_rel['product_id'], 'product_purchase_mst_rate');
			if($pro_mst_rate && $sel_pro_rate_rel['product_rate']> $pro_mst_rate){
				$rate_flag=true;
				break;
			}
		}
		if($rate_flag){
			$upd_stst=$dbcon->query("update tbl_purchaseorder set po_approval_status=0 where purchaseorder_id=".$purchaseorder_id);
		}
		else{
			$upd_stst=$dbcon->query("update tbl_purchaseorder set po_approval_status=1 where purchaseorder_id=".$purchaseorder_id);
		}
	}
/* function update_po_status($dbcon,$inserpoid){
				$que_po="select * from tbl_purchaseordertrn where temptrn_ref_id!=0 and purchaseorder_id=".$inserpoid;
				$resi=$dbcon->query($que_po);
				while($re_po=brp_mysqli_fetch_assoc($resi)){
					
					$query_p="select sum(product_qty) as used_qty from tbl_purchaseordertrn where purchaseordertrn_status=0 and temptrn_ref_id=".$re_po['temptrn_ref_id'];
					$rels=brp_mysqli_fetch_assoc($dbcon->query($query_p));
					
					$query_s="select product_qty from tbl_purchasetrntemp where purchaseordertrn_status=0 and purchaseordertrn_id=".$re_po['temptrn_ref_id'];
					$relp=brp_mysqli_fetch_assoc($dbcon->query($query_s));
					$pending_qty=$relp['product_qty']-$rels['used_qty'];
					if($pending_qty<=0){
						$inf['po_trn_req_status']=1;
						update_record('tbl_purchasetrntemp', $inf,"purchaseordertrn_id=".$re_po['temptrn_ref_id'], $dbcon);
					}else{
						$inf['po_trn_req_status']=0;
						update_record('tbl_purchasetrntemp', $inf,"purchaseordertrn_id=".$re_po['temptrn_ref_id'], $dbcon);
					}
				}
			}
 */
		?>