<?php
session_start();
$AJAX = true;
include("../../config/config.php");
// error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");

$purchase_ledger_id = $dbcon->query("select l_id as purchase_ledger_id from  tbl_ledger where l_group=".PURCHASE_ACCOUNTS." and company_id=".$_SESSION['company_id'])
->fetch_object()->purchase_ledger_id;

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PURCHASE_BILL_ADD,PURCHASE_BILL_UPDATE,PURCHASE_BILL_DELETE,PURCHASE_BILL_APPROVE,PURCHASE_BILL_FINAL_APPROVE
]);

if(strtolower($POST['mode']) == "fetch") {
	
		/*$bulkAccessArray = canCheckPermissionAccess($dbcon, [
				PURCHASE_BILL_ADD,PURCHASE_BILL_UPDATE,PURCHASE_BILL_DELETE,PURCHASE_BILL_APPROVE,PURCHASE_BILL_FINAL_APPROVE
			]);*/

		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
        //$approve_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'aprv',$dbcon);
        //$final_approve_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'final_aprv',$dbcon);

			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			$where='';

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$where_db = check_branch('po', $branch_id);
			$where.=" $where_db and po.company_id=".$_SESSION['company_id'];

			/*if($POST['report']=='all')
			{
				$where.="  and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}
			if($POST['report']=='paid')
			{
				$where.=" and  g_total=paid_amount and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}
			if($POST['report']=='due')
			{
				$where.="  and g_total>paid_amount and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}*/
			$where.="  and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			
			//$where.=" and po.branch_id=$branch";
			$where.=" and po.company_id=$_SESSION[company_id]";
			
			$appData = array();
			$i=1;
			$aColumns = array('po_id','po_no','l.l_name','city.city_name','bms.branch_name','po_date','order_no','g_total','approve_status','paid_amount','status','po.cdate','po.userid');
			$sIndexColumn = "po_id";
			$isWhere = array("status = 0".$where);
			$sTable = "tbl_pono as po";			
			$isJOIN = array('inner join  tbl_ledger as l on po.vender_id=l.l_id','left join  city_mst city on l.cityid=city.cityid','left join branch_mst as bms on bms.branch_id=po.branch_id');
			$hOrder = "po.po_id desc";
			include('../../include/pagging.php');
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				if(in_array(PURCHASE_BILL_UPDATE,$bulkAccessArray)){
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchaseedit/'.$row['po_id'].'">'.$row["po_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchaseedit/'.$row['po_id'].'">'.$row['order_no'].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchaseedit/'.$row['po_id'].'">'.date('d M, Y',strtotime($row['po_date'])).'</a>';
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchaseedit/'.$row['po_id'].'">'.$row['l_name'].'</a>';
				}else{
					$row_data[] = $row["po_no"];
					$row_data[] = $row['order_no'];
					$row_data[] = date('d M, Y',strtotime($row['po_date']));
					$row_data[] = $row['l_name'];
				}
				$row_data[] = $row['branch_name'];
				$row_data[] = $row['city_name'];
				$row_data[] = $row['g_total'];
				
				if($row['approve_status']=='1'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Authorized</div>';
				}
				else{
					$row_data[] = '<div class="external-event label label-warning ui-draggable" style="cursor:auto;">Pending</div>';
				}
				
				$addpayment='';$delete='';$edit='';$view='';
				
				if(in_array(PURCHASE_BILL_DELETE,$bulkAccessArray)){
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['po_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
				
				if(in_array(PURCHASE_BILL_VIEW,$bulkAccessArray)){
					$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchase_view/'.$row['po_id'].'"><i class="fa fa-eye"></i></a> ';
				}
				if(in_array(PURCHASE_BILL_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchaseedit/'.$row['po_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				
				//$mrn_btn=' <button class="btn btn-xs btn-primary" data-original-title="View MRN" data-toggle="tooltip" data-placement="top" onClick="get_mrn('.$row['po_id'].')"><i class="fa fa-bars"></i></button>'; 
				
				//$poprint='<a class="btn btn-xs btn-primary" data-original-title="Print Debit Note" data-toggle="tooltip" data-placement="top" href="'.ROOT.'debit_note_print/'.$row['po_id'].'"><i class="fa fa-print"></i></a>';
				
				$po_no=$dbcon->real_escape_string($row['po_no']);
				$apprv_btn = '';
				if(in_array(PURCHASE_BILL_APPROVE,$bulkAccessArray) && in_array(PURCHASE_BILL_FINAL_APPROVE,$bulkAccessArray)){
					$apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Purchase" data-toggle="tooltip" data-placement="top" onClick="open_approv_pbill('.$row['po_id'].',\''.$po_no.'\')"><i class="fa fa-exclamation-triangle"></i></button>';
				}
				
				$row_data[] = $edit.' '.$delete.' '.$view.' '.$apprv_btn;
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=12 and company_id=".$_SESSION['company_id']);
			
			//$trn_purchaseorder_id_up = $POST['trn_purchaseorder_id_up'];

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$info['purchase_type']	= $POST['purchase_type'];
			$info['po_no']		= $POST['po_no'];
			$info['vender_id']	= $POST['vender_id'];
			$info['purchase_ledger_id']	= $POST['purchase_ledger_id'];
			$info['po_date']	= date('Y-m-d',strtotime($POST['po_date']));
			$info['order_no']	= $POST['order_no'];
			$info['order_date']	= date('Y-m-d',strtotime($POST['po_date']));
			$info['round_off']	= $POST['round_off'];
			$info['packing']	= $POST['paking'];
			$info['remark']		= $POST['remark'];
			$info['g_total']	= $POST['g_total'];
			$info['exp_total']	= $POST['exp_total'];
			/*$info['formulaid']	= $POST['formulaid'];
			$info['discount']	= $POST['discount'];
			$info['tax1_name']	= $POST['taxname0'];
			$info['tax2_name']	= $POST['taxname1'];
			$info['tax3_name']	= $POST['taxname2'];
			$info['taxvalue1']	= $POST['taxvalue0'];
			$info['taxvalue2']	= $POST['taxvalue1'];
			$info['taxvalue3']	= $POST['taxvalue2'];*/

			$info['bill_booking_no']	= $POST['bill_booking_no'];
			$info['bill_status']	= $POST['bill_status'];
			$info['po_booking_date']	= date('Y-m-d',strtotime($POST['po_booking_date']));
			$info['po_received_date']	= date('Y-m-d',strtotime($POST['po_received_date']));
			$info['tax_type']	= $POST['tax_type'];
			$info['itc_type']	= $POST['itc_type'];
			$info['currency_id']	= $POST['currency_id'];
			$info['conversion_rate']	= $POST['conversion_rate'];
			$info['gst_type']	= $POST['gst_type'];
			$info['reverse_charge']	= $POST['reverse_charge'];
			$info['purchase_type_main']	= $POST['purchase_type_main'];
			$info['purchase_type_secondary']	= $POST['purchase_type_secondary'];
			$info['supply_type_main']	= $POST['supply_type_main'];
			$info['supply_type_secondary']	= $POST['supply_type_secondary'];
			$info['consignee_id']	= $POST['consignee_id'];
			$info['purchase_bill_type']	= $POST['purchase_bill_type'];
			$info['igst_amount']	= $POST['igst_amount'];
            $info['formulaid']      = $POST['formula_id']; //added by : Dimple
			//$info['formulaid']	= $POST['importformulaid'];

            
            if(isset($POST['save_print']))
            {
            	$info['print_status']	= $POST['print_status'];
            }
            $info['cdate']				= date("Y-m-d H:i:s");
            $info['mdate']				= date("Y-m-d H:i:s");
            $info['userid']			= $_SESSION['user_id'];
            $info['muserid']			= $_SESSION['user_id'];
            $info['company_id']		= $_SESSION['company_id'];
            $info['usertype_id']		= $_SESSION['user_type'];

            $inserpoid=add_record('tbl_pono', $info, $dbcon, $branch_id);

			// Code By Umair: 28-10-2020 : Insert tax
            if($info['purchase_bill_type']=='2'){
            	add_tax_record($dbcon,$inserpoid,"tbl_pono","po_id",$POST['importformulaid'],$POST['total'], $branch_id);
            }
			// End
            
            
                        //Auto approve if allowed
                        //$final_btn_per=check_permission("purchase_list",$_SESSION['user_id'],'final_aprv',$dbcon);
            if(in_array(PURCHASE_BILL_FINAL_APPROVE,$bulkAccessArray)){
            	$infoaprvqt['approve_status']	= 1;
            	$infoaprvqt['auserid']	= $_SESSION['user_id'];
            	$infoaprvqt['adate']				= date("Y-m-d H:i:s");
            	$updateperid=update_record('tbl_pono', $infoaprvqt,"po_id=".$inserpoid , $dbcon);
            }

            if($inserpoid){
				$user_id = (int)$_SESSION['user_id'];
				$qry1="select trn.*,product.product_name,product.product_type,cat.unit_name,tc.cat_name,grn.grn_no from tbl_potrancation as trn
				left join unit_mst as cat on cat.unitid=trn.unit_id 
				left join product_mst as product on product.product_id=trn.product_id  
				left join tbl_category as tc on product.product_category=tc.cat_id 
				left join tbl_grn as grn on grn.grn_id=trn.grn_id 
				where trn.potrancation_status=3 and trn.user_id=".$user_id;
				
				$result1=$dbcon->query($qry1);
				while($rel=brp_mysqli_fetch_assoc($result1)){
					$GRN = (int)$rel['grn_trn_id'];
					$q = "SELECT * FROM tbl_grn_sub_trn where grn_trn_id=".$GRN;
					$result2=$dbcon->query($q);
					while($rel1=brp_mysqli_fetch_assoc($result2)){
						update_grn_sub_trn_to_purchase_status($dbcon,$rel1['grn_trn_sub_id']);
					}
				}
				/*$gr_trn_id_q = "select group_concat(distinct grn_trn_id) as trn_id from tbl_grn_trn where grn_id in (".$gr_id['grn_id'].")";
				$trn_q_exe =  $dbcon->query($gr_trn_id_q);
				$trn_id = mysqli_fetch_array($trn_q_exe);
				
				$grn_sub_q = "select grn_trn_sub_id from tbl_grn_sub_trn where grn_trn_id in (".$trn_id['trn_id'].")";
				$sub_q_exe =  $dbcon->query($grn_sub_q);
				while($sub_id    = mysqli_fetch_array($sub_q_exe))
					update_grn_sub_trn_to_purchase_status($dbcon,$rel['grn_trn_sub_id']);
				}*/
            	$inftrn['po_id'] = $inserpoid;
            	$inftrn['potrancation_status'] = 0;
            	$updatetrnid=update_record('tbl_potrancation', $inftrn,"user_id=".$_SESSION['user_id']." and  potrancation_status=3" , $dbcon);
            }
                        /*$qry ='INSERT INTO tbl_potrancation (trn_purchaseorder_id,product_type,product_id, description,product_hsn_code,product_qty,product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,branch_id,company_id,po_id)
                        SELECT trn_purchaseorder_id,product_type,product_id,description,product_hsn_code,product_qty, product_rate,unit_id,product_disc,product_amount,product_discount,discount_per,formulaid,tax_name1,tax_amount1,tax_name2,tax_amount2,tax_name3,tax_amount3,total,user_id,branch_id,company_id,'.$inserpoid.' FROM   tbl_potrntemp where temp_status=0 and user_id='.$_SESSION['user_id'];

                        $dbcon->query($qry);
                        $deleteid=delete_record('tbl_potrntemp',"user_id=".$_SESSION['user_id'], $dbcon);*/		

                        /** Purchase Order Entry Start ***/
                                /*if($POST['purchaseorder_id']){
                                        $info_purchase_order['purchase_status']  = 1;
                                        $info_purchase_order['purchaseorder_id'] = $inserpoid;
                                        $updatepurchaseid=update_record('tbl_pono', $info_purchase_order,"purchaseorder_id=".$POST['purchaseorder_id'], $dbcon);
                                    }*/
                                    /** Purchase Order Entry End ***/
                                    
                                    
                                    $total_qty=get_total_qty_by_po($dbcon,$inserpoid);
                                    
                                    $total_lending_cost=$POST['exp_total']/$total_qty;
                                    
                                    $q_pt=$dbcon->query("select * from tbl_potrancation where po_id='$inserpoid'");
                                    while($r_pt=mysqli_fetch_assoc($q_pt))
                                    {
                                    	$pr_pt_qty=intval($r_pt['product_qty']);
                                    	
                                    	$per_landing=$pr_pt_qty*$total_lending_cost;
                                    	
                                    	$total_after_landing=$per_landing+$r_pt['product_rate'];
                                    	
                                    	$dbcon->query("update tbl_potrancation set po_landing_cost='$total_after_landing' where potrancation_id='$r_pt[potrancation_id]'");
                                    }
                                    
                                    
		//$dbcon->query("update tbl_grn set purchase_id='$inserpoid',purchase_status='1' where purchaseorder_id='$trn_purchaseorder_id_up' and purchase_status='0'");
                                    
		//$dbcon->query("update tbl_mrn set purchase_id='$inserpoid',purchase_status='1' where purchaseorder_id='$trn_purchaseorder_id_up' and purchase_status='0'");
                                    
                                    update_grn_status($dbcon,$inserpoid);
                                    
                                    
                                    
                                    foreach ($POST['ename_a'] as $i => $name) 
                                    {
                                    	$info_e['exp_e_name']=$POST['ename_a'][$i];
                                    	$info_e['exp_e_amount']=$POST['eamount_a'][$i];
                                    	$info_e['exp_in_id']=$inserpoid;
                                    	$info_e['cdate']=date("Y-m-d");
                                    	$info_e['company_id']=$_SESSION['company_id'];
                                    	$info_e['branch_id']=$_SESSION['branch_id'];
                                    	$info_e['user_id']=$_SESSION['user_id'];
                                    	$inserinvoiceidexp=add_record('tbl_purchase_exp', $info_e, $dbcon, $branch_id);
                                    	
                                    	add_general_book_entry($dbcon,"tbl_purchase_exp",$inserinvoiceidexp,2,$info_e['exp_e_name'],$info_e['exp_e_amount'],$general_book_id,$POST['po_date'], $branch_id);
                                    }
                // insert into general book pathik start
                                    add_general_book_entry($dbcon,"tbl_purchase",$inserpoid,1,$POST['vender_id'],$POST['g_total'],$general_book_id,$POST['po_date'], $branch_id);
                                    
                                    add_general_book_entry($dbcon,"tbl_purchase",$inserpoid,2,$POST['purchase_ledger_id'],$POST['purchse_account_amount'],$general_book_id,$POST['po_date'],$branch_id);

                                    if($POST['reverse_charge']){
                                    	$ledger_name = $dbcon->query("select l_name from tbl_ledger where l_status = 0 and l_id=44")
                                    	->fetch_object()->l_name;
                                    	add_general_book_entry($dbcon,"purchase_reverse_charge",$inserpoid,2,44,$POST['product_amount_tax'],$general_book_id,$POST['po_date'], $branch_id);
                                    }
                                    general_book_tax_entry($dbcon,$inserpoid, $branch_id);
                // pathik end
                                    $check_purchase_rate_status=check_purchase_rates_status($dbcon, $inserpoid);

                // Payment Entery 
                                    if($POST['paymentmodeid'] && $POST['paid_amount'])
                                    {	
                                    	$acc_id	= $purchase_ledger_id;
                                    	
                                    	$row=array();
                                    	$query1="select * from tbl_invoicetype where type_id=4 and company_id=".$_SESSION['company_id'];
                                    	$rows=mysqli_fetch_assoc($dbcon->query($query1));
                                    	$id=$rows['taxinvoice_start'];
                                    	$id++;
                                    	
                                    	if($rows['invoice_format']=='2'){
                                    		$receipt_no = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
                                    	}
                                    	else if($rows['invoice_format']=='1'){
                                    		$receipt_no = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
                                    	}
                                    	else if($rows['invoice_format']=='3'){
                                    		$receipt_no = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
                                    	}
                                    	else{
                                    		$receipt_no = str_pad($id,3,"0",STR_PAD_LEFT);
                                    	}
                                    	
                    //insert into receipt table
                                    	$receipt['receipt_no']            = $receipt_no;
                                    	$receipt['receipt_date']          = $passbook['entry_date']	= date("Y-m-d",strtotime($POST['po_date']));					
                                    	$receipt['cust_id']               = $POST['vender_id'];
                                    	$receipt['bank_id']               = $POST['bankid'];
                                    	$receipt['acc_id']                = $passbook['acc_id'] 	 	= $acc_id;
                                    	$receipt['payment_mode_id']       = $passbook['paymentmodeid']     = $POST['paymentmodeid'];
                                    	$receipt['cheque_dtl']            = $passbook['reference_no']      = $POST['cheque_dtl'];
                                    	$receipt['ref_date']              = $passbook['reference_date']    = date("Y-m-d",strtotime($POST['ref_date']));
                                    	$receipt['payment_type']          = 2;
                                    	$receipt['total_paid_amount']     = $passbook['amount']	   	= $POST['paid_amount'];
                                    	$receipt['payment_remark']        = text_rnremove($POST['remark']);
                                    	$receipt['cdate']		  = date("Y-m-d H:i:s");
                                    	$receipt['user_id']               = $_SESSION['user_id'];
                                    	$receipt['company_id']            = $_SESSION['company_id'];
                                    	$receipt_id = add_record('tbl_receipt', $receipt, $dbcon, $branch_id);
                                    	
                    //Receipt transaction Entry
                                    	$receipt_trn['receipt_id']          = $receipt_id;
                                    	$receipt_trn['invoice_id']          = 0;
                                    	$receipt_trn['purchase_id']         = $inserpoid;
                                    	$receipt_trn['cradit_note_id']      = 0;
                                    	$receipt_trn['debit_note_id']       = 0;
                                    	$receipt_trn['excess_id']           = 0;
                                    	$receipt_trn['payment_source']      = 'Purchase';
                                    	$receipt_trn['paid_amount']         = $POST['paid_amount'];
                                    	$receipt_trn['total_amount']        = $POST['paid_amount'];
                                    	$receipt_trn['payment_type']        = 2;
                                    	$receipt_trn['user_id']             = $_SESSION['user_id'];
                                    	$receipt_trn['company_id']          = $_SESSION['company_id'];
                                    	$receipt_trn['usertype_id']         = $_SESSION['user_type'];
                                    	$receipt_trn['status']              = 0;
                                    	$receipt_trn_id = add_record('tbl_receipt_trn', $receipt_trn, $dbcon, $branch_id);
                                    	
                    // Passbook entry
                                    	$customer_name = $dbcon->query("select l_name as customer_name from tbl_ledger where l_status = 0 and l_id=".$POST['vender_id']." and company_id=".$_SESSION['company_id'])
                                    	->fetch_object()->customer_name;

                                    	$passbook['customer_id']     = $POST['vender_id'];
                    $passbook['typeid']          = 2;// 1. DR , 2 CR
                    $passbook['trn_id']          = $receipt_id;
                    $passbook['trn_table']       = 'tbl_receipt';
                    $passbook['passbook_note']   = "Purchase Payment From : ".$customer_name;
                    $passbook['user_id']         = $_SESSION['user_id'];
                    $passbook['company_id']	    = $_SESSION['company_id'];
                    $insert1 = add_record('tbl_passbookentry', $passbook, $dbcon, $branch_id);
                    
                    // General book Entry for Vendor
                    $gen_vendor['ref_date']         = date("Y-m-d");
                    $gen_vendor['table_name']	= "tbl_payment";
                    $gen_vendor['table_id']		= $receipt_id;
                    $gen_vendor['entry_type']	= 1;
                    $gen_vendor['ledger_id']	= $POST['vender_id'];
                    $gen_vendor['amount']		= $POST['paid_amount'];
                    $gen_vendor['user_id']		= $_SESSION['user_id'];
                    $gen_vendor['cdate']		= date("Y-m-d H:i:s");
                    $gen_vendor['company_id']	= $_SESSION['company_id'];
                    $gen_vendor_id = add_record("tbl_general_book", $gen_vendor, $dbcon, $branch_id);
                    
                    // General book Entry for Payment mode (cash, bank etc.)
                    $gen_payment['ref_date']	= date("Y-m-d");
                    $gen_payment['table_name']	= "tbl_payment";
                    $gen_payment['table_id']	= $receipt_id;
                    $gen_payment['entry_type']	= 2;
                    $gen_payment['ledger_id']	= $POST['paymentmodeid'];
                    $gen_payment['amount']		= $POST['paid_amount'];
                    $gen_payment['user_id']		= $_SESSION['user_id'];
                    $gen_payment['cdate']		= date("Y-m-d H:i:s");
                    $gen_payment['company_id']	= $_SESSION['company_id'];
                    $inserid11=add_record("tbl_general_book", $gen_payment, $dbcon);
                }
                // Payment Entry End
                
                if(isset($POST['save_print']))
                {
                	$arr['printstatus']=$POST['print_status'];
                	$arr['msg']="1";
                	$arr['eid']=$inserpoeid;
                            //Insert LOG
                	$log_entry=common_log_entry($dbcon,"po_add",1,"tbl_pono",$inserpoid);
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

                $_SESSION['purchase_bill_rate'] = '';		
                echo json_encode($arr);					
                
            }		
            else if(strtolower($POST['mode']) == "edit") {
            	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
            	
            	$info['po_no']		= $POST['po_no'];
            	$info['vender_id']	= $POST['vender_id'];
            	$info['purchase_ledger_id']	= $POST['purchase_ledger_id'];
            	$info['po_date']	= date('Y-m-d',strtotime($POST['po_date']));
            	$info['order_no']	= $POST['order_no'];
            	$info['order_date']	= date('Y-m-d',strtotime($POST['po_date']));
            	$info['round_off']	= $POST['round_off'];
            	$info['packing']	= $POST['paking'];
            	$info['remark']		= $POST['remark'];
            	$info['exp_total']	= $POST['exp_total'];
            	$info['g_total']	= $POST['g_total'];
					/*$info['formulaid']	= $POST['formulaid'];
					$info['discount']	= $POST['discount'];
					$info['tax1_name']	= $POST['taxname0'];
					$info['tax2_name']	= $POST['taxname1'];
					$info['tax3_name']	= $POST['taxname2'];
					$info['taxvalue1']	= $POST['taxvalue0'];
					$info['taxvalue2']	= $POST['taxvalue1'];
					$info['taxvalue3']	= $POST['taxvalue2'];*/
					$info['bill_booking_no']	= $POST['bill_booking_no'];
					$info['bill_status']	= $POST['bill_status'];
					$info['po_booking_date']	= date('Y-m-d',strtotime($POST['po_booking_date']));
					$info['po_received_date']	= date('Y-m-d',strtotime($POST['po_received_date']));
					$info['tax_type']	= $POST['tax_type'];
					$info['itc_type']	= $POST['itc_type'];
					$info['currency_id']	= $POST['currency_id'];
					$info['conversion_rate']	= $POST['conversion_rate'];
					$info['gst_type']	= $POST['gst_type'];
					$info['reverse_charge']	= $POST['reverse_charge'];
					$info['purchase_type_main']	= $POST['purchase_type_main'];
					$info['purchase_type_secondary']	= $POST['purchase_type_secondary'];
					$info['supply_type_main']	= $POST['supply_type_main'];
					$info['supply_type_secondary']	= $POST['supply_type_secondary'];
					$info['consignee_id']	= $POST['consignee_id'];
					$info['purchase_bill_type']	= $POST['purchase_bill_type'];
					$info['igst_amount']	= $POST['igst_amount'];
                    $info['formulaid']      = $POST['formula_id']; //added by : Dimple
					//$info['formulaid']	= $POST['importformulaid'];


                    $info['mdate']		= date("Y-m-d H:i:s");
                    $info['muserid']		= $_SESSION['user_id'];
                    $info['company_id']		= $_SESSION['company_id'];
                    $info['approve_status']	= 0;
                    if(isset($POST['save_print']))
                    {
                    	$info['print_status']	= $POST['print_status'];
                    }
                    $info['cdate']				= 	date("Y-m-d H:i:s");
                    $info['userid']			= $_SESSION['user_id'];
                    $updateid=update_record('tbl_pono', $info,"po_id=".$POST['eid'] , $dbcon, $branch_id);

					// Code By Umair: 28-10-2020 : Insert tax
                    if($info['purchase_bill_type']=='2'){
                    	add_tax_record($dbcon,$POST['eid'],"tbl_pono","po_id",$POST['importformulaid'],$POST['total'], $branch_id);
                    }
					// End
                    
					$qry1="select trn.*,product.product_name,product.product_type,cat.unit_name,tc.cat_name,grn.grn_no from tbl_potrancation as trn
					left join unit_mst as cat on cat.unitid=trn.unit_id 
					left join product_mst as product on product.product_id=trn.product_id 
					left join tbl_category as tc on product.product_category=tc.cat_id 
					left join tbl_grn as grn on grn.grn_id=trn.grn_id 
					where trn.potrancation_status=0 and trn.po_id=".$POST['eid'];
					
					
					$result1=$dbcon->query($qry1);
					while($rel=brp_mysqli_fetch_assoc($result1)){
						$q = "select * from tbl_grn_sub_trn where grn_trn_id=".$rel['grn_trn_id'];
						$result2=$dbcon->query($q);
						while($rel1=brp_mysqli_fetch_assoc($result2)){
							update_grn_sub_trn_to_purchase_status($dbcon,$rel1['grn_trn_sub_id']);
						}
					}
		//Auto approve if allowed
		//$final_btn_per=check_permission("purchase_list",$_SESSION['user_id'],'final_aprv',$dbcon);
                    if(in_array(PURCHASE_BILL_FINAL_APPROVE,$bulkAccessArray)){
                    	$infoaprvqt['approve_status']	= 1;
                    	$infoaprvqt['auserid']	= $_SESSION['user_id'];
                    	$infoaprvqt['adate']				= date("Y-m-d H:i:s");
                    	$updateperid=update_record('tbl_pono', $infoaprvqt,"po_id=".$POST['eid'] , $dbcon, $branch_id);
                    }
                    
		//Update Charges
                    $deleteid=delete_record('tbl_purchase_exp',"exp_in_id=".$POST['eid'], $dbcon);
                    
                    $qry_d="select * from tbl_purchase_exp as cert where exp_in_id=".$POST['eid'] ;
                    $ro_d=$dbcon->query($qry_d);
                    while($re_d=mysqli_fetch_assoc($ro_d)){
                    	$info_gen['genral_book_status']	= 2;
                    	$updateperid=update_record('tbl_general_book', $info_gen,"table_name='tbl_purchase_exp' and table_id=".$ro_d['exp_id'] , $dbcon, $branch_id);
                    }
		//for($i=0;$i<$row_cnt;$i++)
                    foreach ($POST['ename_a'] as $i => $name) 
                    {
                    	$info_e['exp_e_name']=$POST['ename_a'][$i];
                    	$info_e['exp_e_amount']=$POST['eamount_a'][$i];
                    	$info_e['exp_in_id']=$POST['eid'];
                    	$info_e['cdate']=date("Y-m-d");
                    	$info_e['company_id']=$_SESSION['company_id'];
                    	$info_e['branch_id']=$_SESSION['branch_id'];
                    	$info_e['user_id']=$_SESSION['user_id'];
                    	$inserinvoiceidexp=add_record('tbl_purchase_exp', $info_e, $dbcon, $branch_id);
                    	
                    	add_general_book_entry($dbcon,"tbl_purchase_exp",$inserinvoiceidexp,2,$info_e['exp_e_name'],$info_e['exp_e_amount'],$general_book_id,$POST['po_date'], $branch_id);
                    }
		//pathik start
                    $general_book_id=get_general_book_id($dbcon,'tbl_purchase',$POST['eid'],$POST['vender_id']);	
                    add_general_book_entry($dbcon,"tbl_purchase",$POST['eid'],1,$POST['vender_id'],$POST['g_total'],$general_book_id,$POST['po_date'], $branch_id);
                    
                    $general_book_id_p=get_general_book_id($dbcon,'tbl_purchase',$POST['eid'],$POST['purchase_ledger_id']);	
                    add_general_book_entry($dbcon,"tbl_purchase",$POST['eid'],2,$POST['purchase_ledger_id'],$POST['purchse_account_amount'],$general_book_id_p,$POST['po_date'], $branch_id);
                    
                    general_book_tax_entry($dbcon,$POST['eid'], $branch_id);
                    
		//pathik end
                    $check_purchase_rate_status=check_purchase_rates_status($dbcon, $POST['eid']);
                    update_grn_status($dbcon,$POST['eid']);
                    if(isset($POST['save_print']))
                    {
                    	$arr['printstatus']=$POST['print_status'];
                    	$arr['msg']="update";
                    	$arr['eid']=$POST['eid'];
					//Insert LOG
                    	$log_entry=common_log_entry($dbcon,"po_add",2,"tbl_pono",$POST['eid']);
                    }
                    else
                    {
                    	if($updateid)
                    	{	
                    		$arr['msg']="update";
                    		
                    	}
                    	else
                    		$arr['msg']=0;
                    }

                    $_SESSION['purchase_bill_rate'] = '';	
                    echo json_encode($arr);	
                    
                }
                else if(strtolower($POST['mode']) == "delete") {
                	$info['status']		= 2;
                	$info1['potrancation_status']		= 2;
                	$q="select * from tbl_pono where po_id=".$POST['eid'];
                	$row=mysqli_fetch_assoc($dbcon->query($q));
                	$file=$row['po_pdf'];
                	unlink(POPDF_A.$file);
                	$updateinvoiceid=update_record(' tbl_pono', $info,"po_id=".$POST['eid'] , $dbcon);	
                	$updatetrancationid=update_record('tbl_potrancation', $info1,"po_id=".$POST['eid'] , $dbcon);

                	$info_gen['genral_book_status']		= 2;
                	$updateinvoiceid=update_record('tbl_general_book', $info_gen,"table_name='tbl_purchase' and table_id=".$POST['eid'] , $dbcon);	
                	
                	$qry="select * from `tbl_potrancation` as popro where po_id=".$POST['eid'];
                	$result=$dbcon->query($qry);
                	$info_ta['tax_used_status']		= 2;
                	while($row=mysqli_fetch_assoc($result)){
                		
                		$updateinvoiceid=update_record('tbl_used_tax', $info_ta,"table_name='tbl_potrancation' and used_transaction_id=".$row['potrancation_id'] , $dbcon);
                		
                		$sel_trn_po_qry="select * from tbl_po_grn_used where po_grn_used_status=0 and potrancation_id=".$POST['eid'];
                		$rsult=$dbcon->query($sel_trn_po_qry);
                		while($sel_trn_po_rel=brp_mysqli_fetch_assoc($rsult)){
                			
                			$info2['po_grn_used_status']=2;
                			$updateid1=update_record('tbl_po_grn_used', $info2, "po_grn_used_id=".$sel_trn_po_rel['po_grn_used_id'] , $dbcon);
                			
                			
                			update_grn_sub_trn_to_purchase_status($dbcon,$sel_trn_po_rel['grn_sub_trn_id']);
                		}
                	}
                	
                	$qry_d="select * from tbl_purchase_exp as cert where exp_in_id=".$POST['eid'] ;
                	$ro_d=$dbcon->query($qry_d);
                	$info_gen1['genral_book_status']	= 2;
                	while($re_d=mysqli_fetch_assoc($ro_d)){
                		
                		$updateperid=update_record('tbl_general_book', $info_gen1,"table_name='tbl_purchase_exp' and table_id=".$re_d['exp_id'] , $dbcon);
                	} 
			//update_grn_status($dbcon,$POST['eid']);
			// Update Purchase Order Status
			/*$info_purchase_order['purchase_status']  = 0;
			$updatepurchaseid=update_record('tbl_purchaseorder', $info_purchase_order,"used_purchase_id=".$POST['eid'], $dbcon);*/	
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"po_add",3,"tbl_pono",$POST['eid']);
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			//$qry="select popro.*, from tbl_purchaseproduct as porpo left join tbl_company as com on com.company_id=".$_SESSION['company_id']." where product_id=".$POST['eid'];
			$qry="select popro.*,com.stateid as com_stateid,ven.stateid as ven_stateid from `product_mst` as popro left join `tbl_company` as com on com.company_id=".$_SESSION['company_id']." left join tbl_ledger as ven on ven.l_id=".$POST['vender_id']." where product_id=".$POST['eid'];
			$result=$dbcon->query($qry);
			
			$row=mysqli_fetch_assoc($result);

			/** 
				Code By Umair: 02/1/2021
				Comment: Get Item Rate At Bill Time. First we are getting the rate from the tbl_purchaseordertrn, if not exist then we are checking the tbl_product_party_purchase table later we are getting the that particular party rate.
			*/
				$item_rate = get_product_rate_at_purchase_billing_time($dbcon, $POST['vender_id'], $POST['eid']);
				$row['item_rate'] = $item_rate;
				
				echo json_encode($row);
				
			}
			else if(strtolower($POST['mode']) == "formulavalue") {
				$rate_total=0;$c_total=$POST['c_total'];
				$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$POST['eid']." order by tax_value desc";
				$row=$dbcon->query($qry);
				$j=0;
				//$dis=$POST['total']*$POST['t_dis']/100;
				$rate_total=$total=$POST['total'];
				while($tax=mysqli_fetch_assoc($row))
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
				
				//$info1['grn_id']				= $POST['grn_id'];
				$info1['product_id']			= $POST['product_id'];
				$info1['description']			= $_POST['product_des'];
				$info1['product_qty']			= $POST['product_qty'];
				$info1['unit_id']				= $POST['unit_id'];
				$info1['product_rate']			= $POST['product_rate'];
				$info1['product_discount']		= $POST['product_discount'];
				$info1['discount_per']			= $POST['discount_per'];
				$info1['formulaid']				= $POST['formulaid'];
				$info1['sel_tax']				= $_POST['sel_tax'];
				$info1['product_amount']		= $POST['taxable_value'];
				$info1['total']					= $POST['product_amount'];
				$info1['company_id']			= $_SESSION['company_id'];
				$info1['user_id']				= $_SESSION['user_id'];
				$info1['purchase_bill_type']		= $POST['purchase_bill_type'];
				$info1['currency_id']				= $POST['currency_id'];
				$info1['conversion_rate']			= $POST['conversion_rate'];
				$info1['product_usd_rate']			= $POST['product_usd_rate'];
				$info1['product_usd_amount']		= $POST['product_usd_amount'];
				
				//$info=get_product_tax($dbcon,$total,$POST['formulaid']);
				//$info1=array_merge($info1,$info);
				//$info1['total']=$total;
				
				$table='tbl_potrancation';$tableid='potrancation_id';	
				if(!empty($POST['po_id'])) {
					$info1['po_id'] = $POST['po_id'];
				}
				else {
					$info1['potrancation_status'] = 3;
				}
				
				if(empty($POST['edit_id'])) {
					$inserid=add_record($table, $info1, $dbcon);
					$insert_tax=add_tax_record($dbcon,$inserid,"tbl_potrancation","po_id",$POST['formulaid'],$POST['taxable_value'],'');
				}
				else {
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
					$insert_tax=add_tax_record($dbcon,$POST['edit_id'],"tbl_potrancation","po_id",$POST['formulaid'],$POST['taxable_value'],'');
				}
			}

			else if(strtolower($POST['mode']) == "load_tempoutward") {
				if($POST['po_id']){
					$query="select trn.*,product.product_name,product.product_type,cat.unit_name,tc.cat_name,grn.grn_no from tbl_potrancation as trn
					left join unit_mst as cat on cat.unitid=trn.unit_id 
					left join product_mst as product on product.product_id=trn.product_id 
					left join tbl_category as tc on product.product_category=tc.cat_id 
					left join tbl_grn as grn on grn.grn_id=trn.grn_id 
					where trn.potrancation_status=0 and trn.po_id=".$POST['po_id'];
				}
				else{
					$query="select trn.*,product.product_name,product.product_type,cat.unit_name,tc.cat_name,grn.grn_no from tbl_potrancation as trn
					left join unit_mst as cat on cat.unitid=trn.unit_id 
					left join product_mst as product on product.product_id=trn.product_id  
					left join tbl_category as tc on product.product_category=tc.cat_id 
					left join tbl_grn as grn on grn.grn_id=trn.grn_id 
					where trn.potrancation_status=3 and trn.user_id=".$_SESSION['user_id'];
				}
				
				$result=$dbcon->query($query);
				
				echo ' <div class="form-group">
				<div class="col-md-12 col-xs-11">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
				<tr id="field">
				<th class="text-center grn" width="4%">GRN</th>
				<th class="text-center" width="20%">Product Name</th>
				<th class="text-center" width="15%">Product Category</th>
				<th class="text-center" width="6%">Qty</th>
				<th class="text-center" width="6%">USD Rate</th>
				<th class="text-center" width="6%">INR Rate</th>
				<th class="text-center importfield" width="6%">GRate</th>
				<th class="text-center" width="6%">Per</th>
				<th class="text-center" width="6%">Discount</th>
				<th class="text-center generalfield" width="9%">Taxable value</th>
				<th class="text-center generalfield" width="15%">Tax</th>
				<th class="text-center" width="9%">USD Amount</th>
				<th class="text-center" width="9%">INR Amount</th>
				<th class="text-center" width="5%">Action</th>
				</tr>';
				if(mysqli_num_rows($result)>0)
				{
					$i=1;
					$purchase_account_amount=0;
					while($rel=mysqli_fetch_assoc($result))
					{
						
						$query_t="select tax.tax_name,trn.tax_amount from tbl_used_tax as trn
						left join tbl_tax as tax on tax.tax_id=trn.tax_id 
						where trn.tax_used_status=0 and table_name='tbl_potrancation' and trn.used_transaction_id=".$rel['potrancation_id'];
						$result_it=$dbcon->query($query_t);
						if(mysqli_num_rows($result_it)>0){
							$tax_amount=0;
							$tax_show='<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">';
							while($rel_t=mysqli_fetch_assoc($result_it))
							{
								$tax_show.='<tr id="field">
								<td>'.$rel_t["tax_name"].'</td>
								<td>'.$rel_t["tax_amount"].'</td>
								</tr>';
								$tax_amount=$tax_amount+$rel_t["tax_amount"];
							}
							$tax_show.='<tr id="field">
							<td>Total Tax</td>
							<td>'.$tax_amount.'</td>
							</tr>';
							$tax_show.='</table>';
						}else{
							$tax_show="-";
						}
						$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';	
						echo '<tr id="'.$id.'" >
						<td class="grn" style="vertical-align:top;">
						'.$rel['grn_no'].'
						</td>
						<td style="vertical-align:top;">
						'.$rel['product_name'].'
						'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
						</td>
						<td style="vertical-align:top;">
						'.$cat_name.'
						</td>
						<td style="vertical-align:top;" class="text-center item_div item_qty_'.$i.'" data-usdrate="'.$rel['product_usd_rate'].'" data-qtnid="'.$rel['potrancation_id'].'">
						'.$rel['product_qty'].'
						</td>
						<td style="vertical-align:top;" class="text-center">
						'.$rel['product_usd_rate'].'
						</td>					
						<td style="vertical-align:top;" class="text-center">
						'.$rel['product_rate'].'
						</td>
						<td style="vertical-align:top;" class="text-center importfield item_grate_'.$rel['potrancation_id'].'"" >
						'.$rel['g_rate'].'
						</td>
						<td style="vertical-align:top" class="text-center">
						'.$rel['unit_name'].'
						</td>
						<td style="vertical-align:top" class="text-center">
						'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
						</td>
						<td style="vertical-align:top" class="text-center generalfield">
						'.($rel['product_amount']).'
						</td>
						<td style="vertical-align:top" class="text-center generalfield">
						'.$tax_show.'
						</td>
						<td style="vertical-align:top" class="text-center">
						'.$rel['product_usd_amount'].'
						<input type="hidden" value="'.$rel['product_usd_amount'].'" class="usd_amount">
						</td>
						<td style="vertical-align:top" class="text-center">
						'.$rel['total'].'
						</td>
						<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.$rel['total'].'"/>
						
						<td style="vertical-align:top">
						<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['potrancation_id'].');" ><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['potrancation_id'].');" ><i class="fa fa-times"></i></button>
						</td>	
						</tr>';
						if($rel['product_type']!="8"){
							$purchase_account_amount=$purchase_account_amount+$rel['product_amount'];
						}
						$i++;
					}
				}
				else{
					echo '<tr><td colspan="13" class="text-center">NO DATA FOUND</td></tr>';
				}
				echo '</table>			 
				<input type="hidden" name="purchse_account_amount" id="purchse_account_amount" value="'.$purchase_account_amount.'" />
				</div>
				</div>	';
			}
			else if(strtolower($POST['mode'])== "preedit")
			{
				$q = $dbcon -> query("SELECT mst.*, pro.product_name as pro_name FROM tbl_potrancation as mst LEFT JOIN product_mst as pro on pro.product_id = mst.product_id WHERE potrancation_id= '$POST[id]'");
				$r = $q->fetch_assoc();
				if($r['grn_id']){
					$r['producthtml'] = get_grn_trn_for_purchase($dbcon,$r['grn_id'],$r['product_id'],"Edit");
				}
				else{
					$r['producthtml'] = getrequiredproduct($dbcon,'','');
				}
				echo json_encode($r);
			}
			else if(strtolower($POST['mode'])== "getproduct_amount")
			{
				$arr=get_product_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
				echo json_encode($arr);
			}

			else if(strtolower($POST['mode'])== "update_grate")
			{
				$info['g_rate']=$POST['grate'];
				$updateid=update_record('tbl_potrancation', $info, "potrancation_id=".$POST['id'] , $dbcon);
			}
			else if(strtolower($POST['mode'])== "delete_data") {
				$row=array();
				$info['potrancation_status']=2;
				$updateid=update_record('tbl_potrancation', $info, "potrancation_id=".$POST['eid'] , $dbcon);
				
				$info_ta['tax_used_status']		= 2;
				$updateinvoiceid=update_record('tbl_used_tax', $info_ta,"table_name='tbl_potrancation' and used_transaction_id=".$POST['eid'] , $dbcon);
				
				$sel_trn_po_qry="select * from tbl_po_grn_used where po_grn_used_status=0 and potrancation_id=".$POST['eid'];
				$rsult=$dbcon->query($sel_trn_po_qry);
				if(brp_mysqli_num_rows($rsult)>0){
					while($sel_trn_po_rel=brp_mysqli_fetch_assoc($rsult)){
						
						$info2['po_grn_used_status']=2;
						$updateid1=update_record('tbl_po_grn_used', $info2, "po_grn_used_id=".$sel_trn_po_rel['po_grn_used_id'] , $dbcon);
						
						
						update_grn_sub_trn_to_purchase_status($dbcon,$sel_trn_po_rel['grn_sub_trn_id']);
					}
				}
				
		//$change_potrn_use_status=change_potrn_use_status($dbcon,$sel_trn_po_rel['trn_purchaseorder_id'],$sel_trn_po_rel['product_id'],0);

				if($updateid)
					$row['res']="1";
				else
					$row['res']="0";
				
				echo json_encode($row);
			}
			else if(strtolower($POST['mode'])== "delete_temp_data") {
				$sel_trn="select * from tbl_potrancation where potrancation_status=3 and user_id=".$_SESSION['user_id'];
				$rsu=$dbcon->query($sel_trn);
				while($sel=brp_mysqli_fetch_assoc($rsu)){
					
					$info['potrancation_status']=2;
					$updateid=update_record('tbl_potrancation', $info, "potrancation_id=".$sel['potrancation_id'] , $dbcon);
					
					$info_ta['tax_used_status']		= 2;
					$updateinvoiceid=update_record('tbl_used_tax', $info_ta,"table_name='tbl_potrancation' and used_transaction_id=".$sel['potrancation_id'] , $dbcon);
					
					$sel_trn_po_qry="select * from tbl_po_grn_used where po_grn_used_status=0 and potrancation_id=".$sel['potrancation_id'];
					$rsult=$dbcon->query($sel_trn_po_qry);
					while($sel_trn_po_rel=brp_mysqli_fetch_assoc($rsult)){
						
						$info2['po_grn_used_status']=2;
						$updateid1=update_record('tbl_po_grn_used', $info2, "po_grn_used_id=".$sel_trn_po_rel['po_grn_used_id'] , $dbcon);
						
						
						update_grn_sub_trn_to_purchase_status($dbcon,$sel_trn_po_rel['grn_sub_trn_id']);
					}
				}
			}
			else if(strtolower($POST['mode']) == "load_purchase_srs_no") {
				$resp['po_no'] = load_purchase_srs_no($dbcon);
				
				echo json_encode($resp);
			}
			else if(strtolower($POST['mode'])== "load_ven_grn") {
				$resp['pro_html'] = get_grn_for_purchase($dbcon,$POST['vender_id'],"","Add");
				echo json_encode($resp);
			}
			else if(strtolower($POST['mode'])== "load_grn_data") {
				$resp['pro_html']	= get_grn_trn_for_purchase($dbcon,$POST['grn_id'],"","Add");
				echo json_encode($resp);
			}
			else if(strtolower($POST['mode'])== "load_with_out_grn") {
				$resp['pro_html']	= getproduct($dbcon,0,'0,1,2,3,4,5');
				echo json_encode($resp);
			}
			else if(strtolower($POST['mode'])== "load_purchase_order") {
				echo get_po_for_purchase($dbcon,$POST['vender_id']);
			}
			else if(strtolower($POST['mode'])== "load_purhcase_order_data")
			{
				$q = $dbcon -> query("SELECT * from tbl_purchaseorder where purchaseorder_id=".$POST['purchaseorder_id']);
				$rel = $q->fetch_assoc();
				
				$resp['purchaseorder_no']	= $rel['purchaseorder_no'];
				$resp['purchaseorder_date'] = date("d-m-Y",strtotime($rel['purchaseorder_date']));
			//$resp['pro_html'] 		= get_purchase_order_data($dbcon,$POST['purchaseorder_id']);
				echo json_encode($resp);
			}
			else if(strtolower($POST['mode'])== "loadpurchase_producttypedata")
			{
				$resp['pro_html'] 			= get_purchase_order_typewise_data($dbcon,$POST['type_id'],$POST['purchaseorder_id']);
				echo json_encode($resp);
			}
			else if(strtolower($POST['mode'])== "load_purhcase_pro")
			{
				$resp['pro_html'] 			= getproduct($dbcon,0,'0,1,3');
				echo json_encode($resp);
			}
			else if(strtolower($POST['mode'])== "loadpurchase_productdata")
			{
				$q = $dbcon -> query("SELECT trn.*,potrn.product_rate,trn.unit_id,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status!=2 and chtrn.grn_id=trn.grn_id and trn.product_id=chtrn.product_id) as used_qty from tbl_grn_trn as trn
					left join tbl_purchaseordertrn as potrn on potrn.purchaseorder_id=trn.purchaseorder_id and potrn.product_id=trn.product_id
					where trn.grn_id=".$POST['grn_id']." and trn.grn_trn_status=0 and trn.product_id=".$POST['product_id']."");
				$resp = $q->fetch_assoc();
				$resp['uqty']=$resp['product_qty']-$resp['used_qty'];

			/** 
				Code By Umair: 02/1/2021
				Comment: Get Item Rate At Bill Time. First we are getting the rate from the tbl_purchaseordertrn, if not exist then we are checking the tbl_product_party_purchase table later we are getting the that particular party rate.
			*/

			/*$item_rate = getItemsPartyRate($dbcon, $POST['product_id'], $POST['vender_id'] );
			if($item_rate=='0'){
				$item_rate = getItemsPurchaseOrderTrnRate($dbcon, $POST['product_id']);
			}*/

			$item_rate = get_product_rate_at_purchase_billing_time($dbcon, $POST['vender_id'], $POST['product_id']);
			$resp['item_rate'] = $item_rate;

			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "last_rate")
		{
			$query="select product_rate,potrancation_id,potrancation_status,product_id from tbl_potrancation as trn left join tbl_pono as mst on mst.po_id=trn.po_id where product_id=".$POST["product_id"]." and potrancation_status=0 order by potrancation_id DESC";
			$prel=mysqli_fetch_assoc($dbcon->query($query));
			echo $prel['product_rate'];
		}
		else if(strtolower($POST['mode'])== "load_rate_hist")
		{
			$resp='';
			$query="select inv.*,ven.vender_name,pro.product_name,trn.product_rate from tbl_pono as inv
			inner join tbl_potrancation as trn on inv.po_id=trn.po_id 
			inner join tbl_vender as ven on ven.vender_id=inv.vender_id
			inner join product_mst as pro on pro.product_id=trn.product_id
			where inv.status=0 and trn.potrancation_status=0 and inv.vender_id=".$POST["vender_id"]." and trn.product_id=".$POST["product_id"]." order by trn.potrancation_id DESC LIMIT 10";
			$rs_prel=$dbcon->query($query);
			$rs_prel_num_rows=mysqli_num_rows($rs_prel);
			if($rs_prel_num_rows>0){
				while($prel=mysqli_fetch_assoc($rs_prel)){
					$resp.='<tr>
					<td class="text-center">'.$prel['po_no'].'</td>
					<td class="text-center">'.date('d-m-y',strtotime($prel['po_date'])).'</td>
					<td class="text-center">'.$prel['product_rate'].'</td>
					</tr>';
					$row['cust_name']=$prel['vender_name'];
					$row['product_name']=$prel['product_name'];		
				}
			}
			else{
				$resp.='<tr>
				<td colspan="3" class="text-center">NO DATA FOUND !!</td>
				</tr>';
				$row['cust_name']="";
				$row['product_name']="";
			}
			
			
			$row['resp']=$resp;
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];
			echo getrequiredproduct($dbcon,'',' and product_type='.$type_id.'');
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
		else if(strtolower($POST['mode'])=="expense_by_id")
		{
			$eid=$POST['eid'];
			echo get_ledger_expense_by_id($dbcon,$eid);
		}
		else if(strtolower($POST['mode']) == "add_apprv_hist") {
			
			$info1['assign_user_ids']	= $POST['assign_user_ids'];
			$info1['approve_remark']	= $_POST['approve_remark'];
			$info1['approve_status']	= $POST['approve_status'];
			$info1['po_id']				= $POST['po_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$inserid=add_record("tbl_purchasebill_aprv_log", $info1, $dbcon);
			
			//Hide approve btn if not allowed
			//$final_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'final_aprv',$dbcon);
			if(in_array(PURCHASE_BILL_FINAL_APPROVE,$bulkAccessArray)){
				$infoso['approve_status']	= $POST['approve_status'];
				$updateid=update_record('tbl_pono', $infoso,"po_id=".$POST['po_id'] , $dbcon);
			}
			
		}
		else if(strtolower($POST['mode']) == "load_purchase_hist_datatable") {

			$where='';
			$where.=" and log.po_id=".$POST['po_id'];

			$appData = array();
			$i=1;
			$aColumns = array('log.p_aprv_log_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.cdate', 'log.user_id');
			$sIndexColumn = "log.p_aprv_log_id";
			$isWhere = array("log.p_aprv_log_status=0 ".$where." ");
			$sTable = "tbl_purchasebill_aprv_log as log";			
			$isJOIN = array('left join users as usr on usr.user_id=log.user_id');
			$hOrder = "log.p_aprv_log_id desc";
			include('../../include/pagging.php');
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
				$row_data[] = date("d-M-Y h:i A",strtotime($row['cdate']));

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode'])== "set_vendor_sesion"){
			$vendor_id = $POST['vendor_id'];
			$_SESSION['selected_vendor'] = $vendor_id;

		}
		else if(strtolower($POST['mode'])== "get_po_vendor_details"){

			
			$vendor_id = $POST['vendor_id'];
			$eid = $POST['eid'];
			$sql = "SELECT `v`.*, `conm1`.`country_name`, `cm1`.`city_name`, `sm`.`state_name`  FROM `tbl_ledger` as v left join `country_mst` as conm1  ON `v`.`countryid`= `conm1`.`countryid` left join `city_mst` as cm1 ON `v`.`cityid`= `cm1`.`cityid` left join `state_mst` as sm ON `v`.`stateid`= `sm`.`stateid`  WHERE `v`.`l_id` = '".$vendor_id."' AND `v`.`company_id`='".$_SESSION['company_id']."'";
			$vrow=$dbcon->query($sql);
			$rel=mysqli_fetch_assoc($vrow);
			
			
			echo '<section class="panel">
			<div class="panel-body bio-graph-info">
			<h1>Vendor Details</h1>
			<div class="row">
			<div class="bio-row">
			<p><span>Party Name </span>: '.$rel["l_name"].'</p>
			</div>
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
			<div class="bio-row">
			<p><span>GSTIN</span>: '.$rel["gst_no"].'</p>
			</div>';

			if($eid!=''){

				$billsql = "SELECT po_no, po_date FROM tbl_pono WHERE po_id='".$eid."' AND status='0' AND userid='".$_SESSION['user_id']."' AND company_id='".$_SESSION['company_id']."'";	
				$vrowbill=$dbcon->query($billsql);
				$relbill=mysqli_fetch_assoc($vrowbill);

				echo '<div class="bio-row">
				<p><span>Bill No.</span>: '.$relbill["po_no"].'</p>
				</div>
				<div class="bio-row">
				<p><span>Bill Date</span>: '.date('d-M-Y',strtotime($relbill["po_date"])).'</p>
				</div>';
			}

			echo '</div>
			</div>
			</section>';
		}
		else if(strtolower($POST['mode'])== "get_po_manufacturer"){

			$consignee_id = $POST['consignee_id'];
			$vendor_id = $POST['vendor_id'];
			$eid = $POST['eid'];
			$sql = "SELECT `v`.*, `conm1`.`country_name`, `cm1`.`city_name`, `sm`.`state_name`  FROM `tbl_custmer_consignee` as v left join `country_mst` as conm1  ON `v`.`countryid`= `conm1`.`countryid` left join `city_mst` as cm1 ON `v`.`cityid`= `cm1`.`cityid` left join `state_mst` as sm ON `v`.`stateid`= `sm`.`stateid`  WHERE `v`.`cust_id` = '".$consignee_id."' AND `v`.`cust_ref_id` = '".$vendor_id."' AND `v`.`company_id`='".$_SESSION['company_id']."'";
			$vrow=$dbcon->query($sql);
			if(mysqli_num_rows($vrow)>0)
			{
				$rel=mysqli_fetch_assoc($vrow);
				
				echo '<section class="panel">
				<div class="panel-body bio-graph-info">
				<h1>Manufacturer Details</h1>
				<div class="row">
				<div class="bio-row">
				<p><span>Party Name </span>: '.$rel["company_name"].'</p>
				</div>
				<div class="bio-row">
				<p><span>Address </span>: '.$rel["cust_address"].'</p>
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
				<p><span>Pin Code </span>: '.$rel["cust_pincode"].'</p>
				</div>
				<div class="bio-row">
				<p><span>GSTIN</span>: '.$rel["gst_no"].'</p>
				</div>';

				if($eid!=''){

					$billsql = "SELECT po_no, po_date FROM tbl_pono WHERE po_id='".$eid."' AND status='0' AND userid='".$_SESSION['user_id']."' AND company_id='".$_SESSION['company_id']."'";	
					$vrowbill=$dbcon->query($billsql);
					$relbill=mysqli_fetch_assoc($vrowbill);

					echo '<div class="bio-row">
					<p><span>Bill No.</span>: '.$relbill["po_no"].'</p>
					</div>
					<div class="bio-row">
					<p><span>Bill Date</span>: '.date('d-M-Y',strtotime($relbill["po_date"])).'</p>
					</div>';
				}

				echo '</div>
				</div>
				</section>';
			}else{
				echo "DATA NOT EXISTS.";
			}
		}
		else if(strtolower($POST['mode'])== "get_po_accounting"){

			$vendor_id = $POST['vendor_id'];
			$eid = $POST['eid'];
			$sql = "SELECT b.*, `bm`.`bank_name` FROM `tbl_customer_bank` as b left join bank_mst as bm ON `b`.`b_name`=`bm`.`bankid` WHERE `b`.`b_cust`='".$vendor_id."' AND `b`.`userid`='".$_SESSION['user_id']."'";
			$vrow=$dbcon->query($sql);
			
			
			$rel=mysqli_fetch_assoc($vrow);
			
			echo '<section class="panel">
			<div class="panel-body bio-graph-info">
			<h1>Account Details</h1>
			<div class="row">
			<div class="bio-row">
			<p><span>A/C No. </span>: '.$rel["bank_ac"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Bank Name </span>: '.$rel["bank_name"].'</p>
			</div>
			<div class="bio-row">
			<p><span>A/C Name </span>: '.$rel["ac_name"].'</p>
			</div>
			<div class="bio-row">
			<p><span>IFSC </span>: '.$rel["bank_ifsc"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Opening Balance</span>: '.$rel["bank_open"].'</p>
			</div>
			</div>
			</div>
			</section>';
			
		}
		else if(strtolower($POST['mode'])== "get_po_order")
		{
			$vendor_id = $POST['vendor_id'];
			$qry="SELECT `po`.`po_id`,`po`.`vender_id`,`po`.`purchase_type`, `po`.`po_no`, `po`.`po_date`, `po`.`approve_status` as stage, SUM(`pdt`.`product_amount`) as product_amount, SUM(`pdt`.`total`) as product_total_amount, `po`.`exp_total`,  `po`.`g_total` FROM `tbl_pono` as po left join `tbl_potrancation` as pdt ON  `po`.`po_id` = `pdt`.`po_id` Where `po`.`vender_id`=".$vendor_id." and `po`.`status`= 0 and `po`.`company_id`='".$_SESSION['company_id']."' and `po`.`userid`='".$_SESSION['user_id']."' group BY `po`.`po_id` Order by `po`.`po_id` DESC ";
			

			$result=$dbcon->query($qry);
			echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th class="text-center" width="10%">PB No</th>
			<th class="text-center" width="25%">PB Date</th>
			<th class="text-center"width="8%">Net Amount</th>
			<th class="text-center"width="8%">Gross Amount</th>
			<th class="text-center"width="8%">Expenses</th>
			<th class="text-center"width="8%">Total(Inc. Expenses)</th>
			<th class="text-center"width="10%">Status</th>
			<th class="text-center"width="10%">Stage</th>
			</tr>';
			

			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
						//$r=get_product_tax($dbcon,$rel['purchaseordertrn_id']);
						//$total=$rel['pqty']*$rel['product_purchase_rate'];
					if($rel['stage']=='1'){
						$stage = 'Approved';
					}else{
						$stage = 'No';
					}
					echo '<tr id="fieldtr'.$i.'">
					
					<td style="vertical-align:top;" class="text-center"><a href="'.ROOT.'purchaseedit/'.$rel['po_id'].'">'.$rel['po_no'].'</a>
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.date('d-M-Y',strtotime($rel["po_date"])).'
					</td>					
					<td style="vertical-align:top;" class="text-center">
					'.sprintf('%0.2f', $rel['product_amount']).'
					</td>
					<td style="vertical-align:top" class="text-center">
					'.sprintf('%0.2f', $rel['product_total_amount']).'
					</td>
					<td style="vertical-align:top;" class="text-center">
					'.sprintf('%0.2f', $rel['exp_total']).'
					</td>	
					<td style="vertical-align:top;" class="text-center">
					'.sprintf('%0.2f', $rel['g_total']).'
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
			echo '</table> </div></div>';
		}
		else if(strtolower($POST['mode'])== "get_po_history")
		{
			$eid = $POST['eid']; // as purchase id
			$sql = "SELECT `u`.`user_name` as prepared_by,`po`.`cdate`, `mu`.`user_name` as last_modify_by, `po`.`mdate`, `au`.`user_name` as approved_by, `po`.`adate`, `po`.`approve_status` as stage, `po`.`po_date`  FROM `tbl_pono` as po left join `users` as u ON  `po`.`userid` = `u`.`user_id` left join `users` as mu ON  `po`.`muserid` = `mu`.`user_id` left join `users` as au ON  `po`.`auserid` = `au`.`user_id` Where `po`.`po_id`='".$eid."' and `po`.`status`= 0 and `po`.`company_id`='".$_SESSION['company_id']."'";
			
			$vrow=$dbcon->query($sql);
			
			$rel=mysqli_fetch_assoc($vrow);
			
			if($rel['stage']=='1'){
				$stage = 'Approved';
			}else{
				$stage = 'No';
			}
			if($rel['order_date']!="1970-01-01" && $rel['order_date']!="0000-00-00")
			{
				$order_date=date('d-m-Y',strtotime($rel['order_date']));
			}			
			echo '<section class="panel">
			<div class="panel-body bio-graph-info">
			<h1>Login History</h1>
			<div class="row">
			<div class="bio-row">
			<p><span>Prepared By </span>: '.$rel["prepared_by"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Prepared Date </span>: '.(($rel["cdate"]!='' && $rel['cdate']!="1970-01-01" && $rel['cdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["cdate"])):'').'</p>
			</div>
			<div class="bio-row">
			<p><span>Modified By </span>: '.$rel["last_modify_by"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Modified Date</span>: '.(($rel["mdate"]!='' && $rel['mdate']!="1970-01-01" && $rel['mdate']!="0000-00-00")?date('d-M-Y',strtotime($rel["mdate"])):'').'</p>
			</div>
			
			<div class="bio-row">
			<p><span>Approved By </span>: '.$rel["approved_by"].'</p>
			</div>
			<div class="bio-row">
			<p><span>Approved Date </span>: '.(($rel["adate"]!='' && $rel['adate']!="1970-01-01" && $rel['adate']!="0000-00-00")?date('d-M-Y',strtotime($rel["mdate"])):'').'</p>
			</div>
			<div class="bio-row">
			<p><span>Delivery Date </span>: '.(($rel["po_date"]!='' && $rel['po_date']!="1970-01-01" && $rel['po_date']!="0000-00-00")?date('d-M-Y',strtotime($rel["po_date"])):'').'</p>
			</div>
			<div class="bio-row">
			<p><span> Stage </span>: '.$stage.'</p>
			</div>
			
			</div>
			</div>
			</section>';
			
		}else if(strtolower($POST['mode'])== "set_currency_rate")
		{
			$_SESSION['purchase_bill_rate']=$POST['currency_rate'];
			$arr['msg'] = 'Conversion rate has been set in session.';

			echo json_encode($arr);
		}
                // Dimple Panchal : Start
		else if(strtolower($POST['mode'])== "get_tax_on_total")
		{
			$arr = get_tax_on_total($dbcon,$POST['total'],$POST['formulaid']);
			echo json_encode($arr);
		}
                // Dimple Panchal : end
		
		else if(strtolower($POST['mode'])== "insert_product")
		{
			
			$grn=implode(",",$POST['grn_id']);
			
			//$deleteid=delete_record('tbl_invoicetrntemp',"temp_status=0", $dbcon);	
			
			/*$upd['challan_no']=$challan;
			
			 if($POST['eid']!=''){
				$updid=update_record('tbl_invoice',$upd,"invoice_id=".$POST['eid'], $dbcon);
				$deleteid=delete_record('tbl_invoicetrn',"invoice_id=".$POST['eid'], $dbcon);
			} */
			
			$po_id=$POST['eid'];
			
			$qry1="select grn.grn_id,grn_trn.grn_trn_id,grn_sub_trn.grn_trn_sub_id,grn_sub_trn.product_id,grn_sub_trn.product_qty,grn_trn.unit_id,potrn.product_rate,potrn.formulaid,potrn.discount_per from tbl_grn as grn
			left join tbl_grn_trn as grn_trn on grn_trn.grn_id=grn.grn_id
			left join tbl_grn_sub_trn as grn_sub_trn on grn_sub_trn.grn_trn_id=grn_trn.grn_trn_id
			left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id=grn_sub_trn.purchaseordertrn_id
			where grn.grn_status=0 and grn_trn.grn_trn_status=0 and grn_sub_trn.status=0 and grn.purchase_status=0 and grn_trn.purchase_status=0 and grn_sub_trn.purchase_status=0 and grn.grn_id in (".$grn.")";
			$result1=$dbcon->query($qry1);
			while($rel=brp_mysqli_fetch_assoc($result1)){
				
				if(empty($po_id)){
					$qry_d="select * from tbl_po_grn_used as mst 
					left join tbl_potrancation as trn on trn.potrancation_id=mst.potrancation_id
					where mst.po_grn_used_status=0 and mst.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and trn.potrancation_status=3 and trn.user_id=".$_SESSION['user_id'];
					//$qry_d="select * from tbl_potrancation as grn where grn.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and grn.potrancation_status=3";
				}else{
					$qry_d="select * from tbl_po_grn_used as mst 
					left join tbl_potrancation as trn on trn.potrancation_id=mst.potrancation_id
					left join tbl_pono as po on po.po_id=trn.po_id
					where mst.po_grn_used_status=0 and mst.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and trn.potrancation_status=0 and trn.po_id=".$po_id." and po.company_id=".$_SESSION['company_id'];
					
					//$qry_d="select * from tbl_potrancation as grn where grn.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and grn.potrancation_status=0 and po_id=".$po_id;
				}
				
				$result_d=$dbcon->query($qry_d);
				$entry_count = brp_mysqli_num_rows($result_d);
				
				if($entry_count==0)
				{	
					if(empty($po_id)){
						$qry_ol="select trn.potrancation_id,trn.product_qty from tbl_po_grn_used as mst 
						left join tbl_potrancation as trn on trn.potrancation_id=mst.potrancation_id
						where trn.product_id=".$rel['product_id']." trn grn.discount_per=".$rel['discount_per']." and trn.product_rate=".$rel['product_rate']." and trn.formulaid=".$rel['formulaid']." and  mst.po_grn_used_status=0 and mst.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and trn.potrancation_status=3 and trn.user_id=".$_SESSION['user_id'];
						
						//$qry_ol="select * from tbl_potrancation as grn where grn.product_id=".$rel['product_id']." and grn.discount_per=".$rel['discount_per']." and grn.product_rate=".$rel['product_rate']." and grn.formulaid=".$rel['formulaid']." and grn.potrancation_status=3 and company_id=".$_SESSION['company_id'];
						
					}else{
						$qry_ol="select trn.potrancation_id,trn.product_qty from tbl_po_grn_used as mst 
						left join tbl_potrancation as trn on trn.potrancation_id=mst.potrancation_id
						left join tbl_pono as po on po.po_id=trn.po_id
						where trn.product_id=".$rel['product_id']." trn grn.discount_per=".$rel['discount_per']." and trn.product_rate=".$rel['product_rate']." and trn.formulaid=".$rel['formulaid']." and mst.po_grn_used_status=0 and mst.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and trn.potrancation_status=0 and trn.po_id=".$po_id." and po.company_id=".$_SESSION['company_id'];
						
						
						//$qry_ol="select * from tbl_potrancation as grn where grn.product_id=".$rel['product_id']." and grn.discount_per=".$rel['discount_per']." and grn.product_rate=".$rel['product_rate']." and grn.formulaid=".$rel['formulaid']." and grn.potrancation_status=3 and company_id=".$_SESSION['company_id']." and po_id=".$po_id;
					}
					
					
					$result_ol=$dbcon->query($qry_ol);
					$rel_ol=brp_mysqli_fetch_assoc($result_ol);
					
					$qry_used_qt="select IFNULL(sum(used_qty),0) as usedqty from tbl_po_grn_used as mst 
					where po_grn_used_status=0 and grn_sub_trn_id=".$rel['grn_trn_sub_id'];
					$result_used_qt=$dbcon->query($qry_used_qt);
					$rel_used_qt=brp_mysqli_fetch_assoc($result_used_qt);
					$pending_qty=$rel['product_qty']-$rel_used_qt['usedqty'];
					
					if(!empty($rel_ol['potrancation_id'])){
						$product_qty=$pending_qty+$rel_ol['product_qty'];
						$product_amount_with_out_discount=$rel['product_rate']*$product_qty;
						if(!empty($rel['discount_per'])){
							$product_discount_per=$rel['discount_per'];
							$product_discount_amount=(($product_amount_with_out_discount*$product_discount_per)/100);
							$product_amount=$product_amount_with_out_discount-$product_discount_amount;
							
						}else{
							$product_amount=$product_amount_with_out_discount;
						}
					}else{
						$product_qty=$pending_qty;
						$product_amount_with_out_discount=$rel['product_rate']*$product_qty;
						if(!empty($rel['discount_per'])){
							$product_discount_per=$rel['discount_per'];
							$product_discount_amount=(($product_amount_with_out_discount*$product_discount_per)/100);
							$product_amount=$product_amount_with_out_discount-$product_discount_amount;
							
						}else{
							$product_amount=$product_amount_with_out_discount;
						}
					}
					
					$info1['grn_id']				= $rel['grn_id'];
					$info1['grn_trn_id']			= $rel['grn_trn_id'];
					$info1['grn_sub_trn_id']		= $rel['grn_trn_sub_id'];
					
					
					$info1['product_id']			= $rel['product_id'];
					$info1['description']			= '';
					$info1['product_qty']			= $product_qty;
					$info1['unit_id']				= $rel['unit_id'];
					$info1['product_rate']			= $rel['product_rate'];
					$info1['product_discount']		= $product_discount_amount;
					$info1['discount_per']			= $product_discount_per;
					$info1['formulaid']				= $rel['formulaid'];
				//	$info1['sel_tax']				= $r_decode['name'];
					$info1['product_amount']		= $product_amount;
					//$info1['total']				= $total;
					$info1['company_id']			= $_SESSION['company_id'];
					$info1['user_id']				= $_SESSION['user_id'];
					$info1['purchase_bill_type']	= $purchase_bill_type;
					$info1['currency_id']			= $currency_id;
					$info1['conversion_rate']		= $conversion_rate;
					$info1['product_usd_rate']		= $rel['product_rate'];
					$info1['product_usd_amount']	= $total;
					
					$info=get_product_tax($dbcon,$product_amount,$info1['formulaid']);
					$info1=array_merge($info1,$info);
					
					$table='tbl_potrancation';$tableid='potrancation_id';	
					if(!empty($po_id)) {
						$info1['po_id'] = $po_id;
					}
					else {
						$info1['potrancation_status'] = 3;
					}
					
					if(!empty($rel_ol['potrancation_id'])){
						$updatesalesid=update_record($table, $info1,"potrancation_id=".$rel_ol['potrancation_id'], $dbcon);
						$inserid=$rel_ol['potrancation_id'];
					}else{
						$inserid=add_record($table, $info1, $dbcon);
					}
					$insert_tax=add_tax_record($dbcon,$inserid,"tbl_potrancation","po_id",$info1['formulaid'],$product_amount,'');
					
					$query_invoicetype = $dbcon->query("UPDATE tbl_grn_sub_trn SET purchase_qty = purchase_qty + ".$pending_qty." WHERE grn_trn_sub_id = ".$rel['grn_trn_sub_id']);
					
					
					$info_used['potrancation_id']	= $inserid;
					$info_used['product_id']		= $rel['product_id'];
					$info_used['used_qty']			= $pending_qty;
					$info_used['product_rate']		= $rel['product_rate'];
					$info_used['unit_id']			= $rel['unit_id'];
					$info_used['grn_id']			= $rel['grn_id'];
					$info_used['grn_trn_id']		= $rel['grn_trn_id'];
					$info_used['grn_sub_trn_id']	= $rel['grn_trn_sub_id'];
					$info_used['discount_per']		= $rel['discount_per'];
					$info_used['formulaid']			= $info1['formulaid'];
					$info_used['cdate']				= date("Y-m-d H:i:s");
					$info_used['user_id']			= $_SESSION['user_id'];
					$info_used['company_id']		= $_SESSION['company_id'];
					
					$inserid3=add_record("tbl_po_grn_used", $info_used, $dbcon);
				}
			}	
		}

		function get_product_tax($dbcon,$product_amount,$formulaid)
		{
			$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
			$row=$dbcon->query($qry);
			$rate_total=$total=$product_amount;
			$i=1;$tax_total_amount=0;
			while($tax=mysqli_fetch_assoc($row))
			{	
		//$info['tax_name'.$i]=$tax['tax_name'];
				$itax_amount=$tax_amount=($total)*$tax['tax_value']/100;
				$rate_total+=$tax_amount;
				$tax_total_amount+=$itax_amount;
				$i++;
			}
			for($j=$i;$j<=3;$j++)
			{
		//$info['tax_name'.$i]='';
		//$info['tax_amount'.$i]='';
			}
			$info['total']=$rate_total;
			$info['tax_total_amount']=$tax_total_amount;
			return $info;
		}

		function check_purchase_rates_status($dbcon,$po_id){
			$sel_pro_rate = "select * from tbl_potrancation where potrancation_status=0 and po_id=".$po_id;
			$rate_flag=false;
			$sel_pro_rate_rs = $dbcon->query($sel_pro_rate);
			while($sel_pro_rate_rel=mysqli_fetch_assoc($sel_pro_rate_rs)){
				if($sel_pro_rate_rel['trn_purchaseorder_id']){
					$get_protrn_rate_qry = "select product_rate from tbl_purchaseordertrn where purchaseordertrn_id=".$sel_pro_rate_rel['trn_purchaseorder_id'];
					$pro_rt_rel = mysqli_fetch_assoc($dbcon->query($get_protrn_rate_qry));
					$pro_mst_rate = $pro_rt_rel['product_rate'];
				}
				else{
					$pro_mst_rate = get_pro_field($dbcon, $sel_pro_rate_rel['product_id'], 'product_purchase_rate');
				}
				
				if($pro_mst_rate && $sel_pro_rate_rel['product_rate']> $pro_mst_rate){
					$rate_flag=true;
					break;
				}
			}
			
			if($rate_flag){
				$upd_stst=$dbcon->query("update tbl_pono set mismatch_rate_status=1 where po_id=".$po_id);
			}
			else{
				$upd_stst=$dbcon->query("update tbl_pono set mismatch_rate_status=0 where po_id=".$po_id);
			}
		}
		function change_potrn_use_status($dbcon,$trn_purchaseorder_id,$product_id,$use_purchase_status){
			$upd_sts = $dbcon->query("update tbl_purchaseordertrn set use_purchase_status=".$use_purchase_status." where purchaseorder_id=".$trn_purchaseorder_id." and purchaseordertrn_status=0 and product_id in(".$product_id.")");
			
			$upd_qry = "select * from tbl_purchaseordertrn where purchaseordertrn_status=0 and use_purchase_status=0 and purchaseorder_id=".$trn_purchaseorder_id." ";
			$upd_qry_rs = $dbcon->query($upd_qry);
			$upd_qry_nums = mysqli_num_rows($upd_qry_rs);
			if($upd_qry_nums){
				$updmain_sts = $dbcon->query("update tbl_purchaseorder set purchase_status=0 where purchaseorder_id=".$trn_purchaseorder_id." ");
			}
			else{
				$updmain_sts = $dbcon->query("update tbl_purchaseorder set purchase_status=1 where purchaseorder_id=".$trn_purchaseorder_id." ");
			}
		}
		
		function load_purchase_srs_no($dbcon){
			
	//Load no by Type ID
			$row=array();
			$query1="select * from tbl_invoicetype where status=0 and type_id=12 and company_id=".$_SESSION['company_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			if($rows['invoice_format']=='2'){
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			return $row['invoiceno'];
		}
		function general_book_tax_entry($dbcon,$invoice_id, $branch_id=''){
			$qry1="select group_concat(potrancation_id) as tid from tbl_potrancation as cert where potrancation_status=0 and po_id=".$invoice_id;
			$ro=$dbcon->query($qry1);
			$re=mysqli_fetch_assoc($ro);
			
			$qry122="select * from tbl_pono as cert where status=0 and po_id=".$invoice_id;
			$ro12=$dbcon->query($qry122);
			$rea=mysqli_fetch_assoc($ro12);
			
			
			$qry="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax WHERE tax_used_status=0 and used_transaction_id in (".$re["tid"].") and table_name='tbl_potrancation' group by ledger_id order by tax_used_id desc";
			$row=$dbcon->query($qry);
			while($tax=mysqli_fetch_assoc($row))
			{
				$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_purchase'";
				$ros=$dbcon->query($qry12);
				$re2=mysqli_fetch_assoc($ros);
				
				
				$info1['table_name']	= "tbl_purchase";
				$info1['table_id']		= $invoice_id;
				$info1['ref_date']		= date("Y-m-d",strtotime($rea['po_date']));
				$info1['entry_type']	= 2;
				$info1['ledger_id']		= $tax['ledger_id'];
				$info1['amount']		= $tax['tamount'];
				$info1['user_id']		= $_SESSION['user_id'];
				$info1['cdate']			= date("Y-m-d H:i:s");
				$info1['company_id']	= $_SESSION['company_id'];
				
				if(!empty($re2['general_book_id'])){
					$updateid=update_record("tbl_general_book", $info1,"general_book_id=".$re2['general_book_id'] , $dbcon, $branch_id);
				}else{
					$inserid=add_record("tbl_general_book", $info1, $dbcon, $branch_id);
				}
		//var_dump($re2['general_book_id']);
			}


	// Code By Umair: 28/10/2020 : For IGST Tax(Import Purchase)
			$qry1="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax WHERE tax_used_status=0 and used_transaction_id in (".$invoice_id.") and table_name='tbl_pono' group by ledger_id order by tax_used_id desc";
			$row=$dbcon->query($qry1);
			while($tax=mysqli_fetch_assoc($row))
			{
				$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_purchase_main'";
				$ros=$dbcon->query($qry12);
				$re2=mysqli_fetch_assoc($ros);


				$info1['table_name'] = "tbl_purchase_main";
				$info1['table_id'] = $invoice_id;
				$info1['ref_date'] = date("Y-m-d",strtotime($rea['po_date']));
				$info1['entry_type'] = 2;
				$info1['ledger_id'] = $tax['ledger_id'];
				$info1['amount'] = $tax['tamount'];
				$info1['user_id'] = $_SESSION['user_id'];
				$info1['cdate'] = date("Y-m-d H:i:s");
				$info1['company_id'] = $_SESSION['company_id'];

				if(!empty($re2['general_book_id'])){
					$updateid=update_record("tbl_general_book", $info1,"general_book_id=".$re2['general_book_id'] , $dbcon);
				}else{
					$inserid=add_record("tbl_general_book", $info1, $dbcon);
				}
	//var_dump($re2['general_book_id']);
			}
			
		}
	?>