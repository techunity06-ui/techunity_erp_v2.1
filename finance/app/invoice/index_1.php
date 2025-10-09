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
	FINANCE_INVOICE_RECEIPT,
	FINANCE_INVOICE_CHALAN,
	FINANCE_INVOICE_EDIT,
	FINANCE_INVOICE_DELETE
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
		
   		//branch , company, user check start - dhaval 
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('invoice', $branch_id);
		
		$where.=" $where_db";

		$where_company=check_company('invoice');

		$where.=" $where_company";

		$where_user=check_user('invoice');

		$where.=" $where_user";

		// branch , comapny , user check end - dhaval
		
		//check_user('invoice')
			if(!empty($POST['type_id']))
			{
				$where .=" and invoice.invoicetype_id=".$POST['type_id'];
			}
			$where.="  and invoice_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND invoice_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('invoice_id','invoice_no','cust.l_name','invoice_date','invoicetype.invoice_type','g_total','paid_amount','invoice_status','invoice.cdate','invoice.user_id','invoice.usertype_id','invoice.invoicetype_id','invoice.gst_flag','invoice.approve_status');
			$sIndexColumn = "invoice_id";
			$isWhere = array("invoice_status = 0 ".$where);
			$sTable = "tbl_invoice as invoice";			
			$isJOIN = array('inner join tbl_ledger cust on invoice.cust_id=cust.l_id','left join tbl_invoicetype invoicetype on invoice.invoicetype_id=invoicetype.invoicetype_id');
			$hOrder = "invoice.invoice_id desc";
			include($path.'include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				if(in_array(FINANCE_INVOICE_EDIT,$bulkAccessArray)){
					$row_data[] = $id;
					$row_data[] = '<a class="" data-original-title="Edit '.$row["invoice_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'].'">'.$row["invoice_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["invoice_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'].'">'.date('d M, Y',strtotime($row["invoice_date"])).'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["invoice_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'].'">'.$row["l_name"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit '.$row["invoice_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'].'">'.$row["g_total"].'</a>';
				}
				
				 
				$addpayment='';$delete='';$edit='';$invoice_chalan='';$print='';
				if($row["g_total"]>$row["paid_amount"]){
					//$addpayment='<a class="btn btn-xs btn-primary" data-original-title="Payable '.($row['g_total']-$row['paid_amount']).' Rs." data-toggle="tooltip" data-placement="top" href="invoicepaymentmode/'.$row['invoice_id'].'"><i class="fa fa-plus"></i></a>';
					
				}
					
				if($_SESSION['user_type']!=2){
					if($_SESSION['user_id']==$row['user_id']){
						if(in_array(FINANCE_INVOICE_DELETE,$bulkAccessArray)  && $row['approve_status']!='1'){
							$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['invoice_id'].')"><i class="fa fa-trash-o"></i></button>';
						}
						if(in_array(FINANCE_INVOICE_EDIT,$bulkAccessArray)  && $row['approve_status']!='1'){
							$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'].'"><i class="fa fa-pencil"></i></a>';
						}
					
					}else{
						$delete='';
						$edit='';

					}
				}else{
					if(in_array(FINANCE_INVOICE_DELETE,$bulkAccessArray)  && $row['approve_status']!='1'){
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['invoice_id'].')"><i class="fa fa-trash-o"></i></button>';
					}
					if(in_array(FINANCE_INVOICE_EDIT,$bulkAccessArray)   && $row['approve_status']!='1'){
						$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$row['invoice_id'].'"><i class="fa fa-pencil"></i></a>';
					}
				}
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				if(in_array(FINANCE_INVOICE_RECEIPT,$bulkAccessArray)){
					$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type =7 AND approve_status = 1 AND status = 0 ORDER BY priority");
					while($res = mysqli_fetch_assoc($sql)){
						if(in_array($res['id'],$menu_show_permissions)) {
							$print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['invoice_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>&nbsp;';
						}
					}
				}

				if(in_array(FINANCE_INVOICE_CHALAN,$bulkAccessArray)){
					$sqls=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type =6 AND approve_status = 1 AND status = 0 ORDER BY priority");
					while($ress = mysqli_fetch_assoc($sqls)){
						if(in_array($ress['id'],$menu_show_permissions)) {
							$invoice_chalan.='<a class="btn btn-xs btn-primary" data-original-title="'.$ress['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$ress['page_path'].'/'.$row['invoice_id'].'" style="background: '.$ress['icon_color'].'; border-color: '.$ress['icon_color'].';"><i class="'.$ress['fa_icon'].'"></i></a>&nbsp;';
						}
					}
				}
				$row_data[] = $print.'&nbsp;'.$invoice_chalan.'&nbsp;'.$edit.'&nbsp;'.$delete.'&nbsp;'.$addpayment.'&nbsp;';
				 
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
		  
		  //echo '<pre>';print_r($POST);exit;     
            if(isset($POST['currency_enable'])){
            	$curncy_trn['currency_id'] 		= $POST['currency_id'];
            	$curncy_trn['currency_rate'] 	= $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id']		= $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] 	= 1;
            }

	        $info['invoice_no']			= $POST['invoice_no'];
	        $info['invoice_date']		= date('Y-m-d',strtotime($POST['invoice_date']));
	        $info['invoice_due_date']	= date('Y-m-d',strtotime($POST['invoice_due_date']));
	        $info['dc_enable'] 			= $POST['dc_enable']; //Added new by dhruv
	        $info['challan_no']			= (isset($POST['dc_enable'])) ? $POST['challan_no'] : 0 ; //Added new by dhruv
	        $info['challan_date']		= (isset($POST['dc_enable'])) ? date('Y-m-d',strtotime($POST['challan_date'])) : 0 ; //Added new by dhruv  	
			
	        $info['po_enable']			= $POST['po_enable']; //Added new by dhruv    
	        $info['order_no']			= (isset($POST['po_enable'])) ? $POST['order_no'] : 0 ; //Added new by dhruv	        
	        $info['order_date']			= (isset($POST['po_enable'])) ? date('Y-m-d',strtotime($POST['order_date'])) : 0 ; //Added new by dhruv	   

	        $info['currency_enable'] 	= $POST['currency_enable']; //Added new by dhruv    
	        $info['currency_id']		= (isset($POST['currency_enable'])) ? $POST['currency_id'] : 0 ; //Added new by dhruv
	        $info['currency_rate']		= (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1 ; //Added new by dhruv 

	        $info['sale_material_center']	= $POST['sale_material_center']; //Added new by dhruv
	        $info['is_sales_order']		= (isset($POST['is_sales_order']) && ($POST['is_sales_order']=='yes')) ? 1 : 0; //Added new by dhruv
	        $info['enable_cost_center'] = (isset($POST['enable_cost_center']) && ($POST['enable_cost_center']=='yes')) ? 1 : 0; //Added new by dhruv
	        $info['enable_tcs_details'] = (isset($POST['enable_tcs_details']) && ($POST['enable_tcs_details']=='yes')) ? 1 : 0; //Added new by dhruv
	        $info['enable_ewaybill'] 	= (isset($POST['enable_ewaybill']) && ($POST['enable_ewaybill']=='yes')) ? 1 : 0; //Added new by dhruv
	        $info['enable_transport'] = (isset($POST['enable_transport']) && ($POST['enable_transport']=='yes')) ? 1 : 0;

	        $info['eway_bill_no'] 		= $POST['eway_bill_no']; //Added new by dhruv
	        $info['eway_bill_date'] 	= $POST['eway_bill_date']; //Added new by dhruv
		
	        $info['cust_id']			= $POST['cust_id'];
	        $info['sales_ledger_id']	= implode(",",$POST['sales_ledger_id']);
	        $info['basic_total']		= $POST['total'];
	        $info['g_total']			= $POST['g_total'];

	        $info['print_status']		= $POST['print_status'];
	        $info['financial_year_id']	= $POST['financial_year'];
	        $info['sales_ledger_id'] = $POST['sales_ledger_id'];

	        $info['remark']				= text_rnremove($POST['remark']);
	        $info['install_type']		= (isset($POST['install_type']) && ($POST['install_type']=='yes')) ? 1 : 0; //Added new by dhruv
	        $info['cdate']				= date("Y-m-d H:i:s");
	        $info['user_id']			= $_SESSION['user_id'];
	        $info['company_id']			= $_SESSION['company_id'];
	        $info['branch_id']   		= $POST['branch_id'];
	        $info['invoice_status']		= 0;
	        $info['usertype_id']		= $_SESSION['usertype_id'];
	        $info['tcs_amount']   = $POST['tcs_amount'];
			$info['tcs_per']   = $POST['tcs_per'];
			
			//print_r($POST);exit;
			if(!empty($POST['salesorderid'][0])){
				$info['sales_order_id']	= implode(",",$POST['salesorderid']);
			}else if($POST['sales_order'] != 'undefined' || $POST['sales_order'] != ''){
				$info['sales_order_id']	= implode(",",json_decode($POST['sales_order']));
			}
			
			
	        $inserinvoiceid=add_record('tbl_invoice', array_merge($info,$curncy_trn), $dbcon);

	        $cust_name = get_ledger_expense_by_id($dbcon, $POST['cust_id']);
			tbl_transcation_entry($dbcon,"Invoice",$POST['invoice_no'],$inserinvoiceid,$cust_name,$POST['g_total']);



	        /*Update Invoice Trn Table Start by Dhruv*/
	        if($inserinvoiceid){
				$inv_trn['invoice_id']	= $inserinvoiceid;
				$inv_trn['trancation_status'] = 0;
				$updatetrnid=update_record('tbl_invoicetrn',array_merge($inv_trn,$curncy_trn),"user_id=".$_SESSION['user_id']." and trancation_status=1 and invoice_id=0 " , $dbcon);
			}

			//Stock maintain
	        $query="select trn.*,pro_mst.product_base_unit from tbl_invoicetrn as trn
			left join product_mst as pro_mst on pro_mst.product_id=trn.product_id
			where trn.trancation_status=0 and trn.invoice_id=".$inserinvoiceid;
			//echo $query;exit;
			$result=$dbcon->query($query);

			while($row=brp_mysqli_fetch_assoc($result)){
				if($row['unit_id']!=0){
					minus_stock($dbcon,$row['product_id'],$row['unit_id'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
					deduct_so_reseve_stock($dbcon,$row['sales_ordertrn_id'],$row['product_qty'],$row['unit_id']);
				}else{
					minus_stock($dbcon,$row['product_id'],$row['product_base_unit'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
					deduct_so_reseve_stock($dbcon,$row['sales_ordertrn_id'],$row['product_qty'],$row['product_base_unit']);
				}
			}

			/*Update Cost center Trn Table Start by Dhruv*/
	        if($inserinvoiceid && $POST['enable_cost_center']=='yes'){
				$cost_trn['cost_center_ledger_id']	= $POST['cust_id'];
				$cost_trn['cost_center_table_id'] = $inserinvoiceid;
				$updatecosttrnid=update_record('tbl_cost_center_transaction', array_merge($cost_trn,$curncy_trn),"isdelete=0 and cost_center_table_id=0 and cost_center_ledger_id=0 and cost_center_table='tbl_invoice' and user_id=".$_SESSION['user_id'] , $dbcon);
			}

			/*Update TCS Trn Table Start by Dhruv*/
	        if($inserinvoiceid && $POST['enable_tcs_details']=='yes'){
				$tcs_trn['tcs_sale_id']	= $inserinvoiceid;
				$tcs_trn['tcs_sale_ledger'] = $POST['sales_ledger_id'];
				$tcs_trn['tcs_cust_ledger'] = $POST['cust_id'];
				$updatetcstrnid=update_record('tbl_tcs_deduction_transaction',array_merge($tcs_trn,$curncy_trn),"isdelete=0 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
			
			//print_r($POST['bill_sundry_tax']);exit;
			
			/** Insert in general book table By Dhruv **/
			if($inserinvoiceid){
            	add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,1,$POST['sales_ledger_id'],$POST['total'],'',$POST['invoice_date'],'',$curncy_trn); 
            	//basic total & credit - done

            	add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,2,$POST['cust_id'],$POST['g_total'],'',$POST['invoice_date'],'',$curncy_trn); // grand total & debit - done

            	foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount) {
           			
           			$info_sundry_tax['sundry_ledger_id']=$bill_sundry_tax_id;
					$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
					$info_sundry_tax['sundry_voucher_id']=$inserinvoiceid;
					$info_sundry_tax['sundry_voucher_type']=SALES_VOUCHER;
					$info_sundry_tax['sundry_voucher_table']='tbl_invoice';
					$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			        $info_sundry_tax['user_id']	= $_SESSION['user_id'];
			        $info_sundry_tax['company_id']	= $_SESSION['company_id'];

					$sundry_tax_insert=add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_tax,$curncy_trn), $dbcon);

            		add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_tax_insert,1,$bill_sundry_tax_id,$bill_sundry_tax_amount,'',$POST['invoice_date'],'',$curncy_trn); // credit entry & tax amount - done
            	}

            	foreach ($POST['bill_sundry_addon'] as $bill_sundry_addon_id => $bill_sundry_addon_amount) {

            		$info_sundry_addon['sundry_ledger_id']=$bill_sundry_addon_id;
					$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount;
					$info_sundry_addon['sundry_voucher_id']=$inserinvoiceid;
					$info_sundry_addon['sundry_voucher_type']=SALES_VOUCHER;
					$info_sundry_addon['sundry_voucher_table']='tbl_invoice';
					$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
			        $info_sundry_addon['user_id']	= $_SESSION['user_id'];
			        $info_sundry_addon['company_id']	= $_SESSION['company_id'];
					

					//print_r(array_merge($info_sundry_addon,$curncy_trn));
					
					$sundry_addon_insert=add_record('tbl_bill_sundry_transaction',array_merge($info_sundry_addon,$curncy_trn), $dbcon);

					if($bill_sundry_addon_amount < 0){
						add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_addon_insert,2,$bill_sundry_addon_id,abs($bill_sundry_addon_amount),'',$POST['invoice_date'],'',$curncy_trn);

						$info_gen1['table_name']	= 'tbl_invoice';
						$info_gen1['table_id']		= $inserinvoiceid;
						$info_gen1['entry_type']	= 1;
						$info_gen1['ref_date']		= date('Y-m-d',strtotime($POST['invoice_date']));
						$info_gen1['ledger_id']		= $POST['cust_id'];
						$info_gen1['amount']		= abs($bill_sundry_addon_amount);
						$info_gen1['user_id']		= $_SESSION['user_id'];
						$info_gen1['cdate']			= date("Y-m-d H:i:s");
						$info_gen1['company_id']	= $_SESSION['company_id'];
						$info_gen1['ref_by'] = 'tbl_addon_bill_sundry';
						
						//$inserid_gen1=add_record("tbl_general_book", array_merge($info_gen1,$curncy_trn) , $dbcon);
						//add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,1,$POST['cust_id'],abs($bill_sundry_addon_amount),'',$POST['invoice_date'],'',$curncy_trn,$info_sundry);

					}else{
						add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_addon_insert,1,$bill_sundry_addon_id,$bill_sundry_addon_amount,'',$POST['invoice_date'],'',$curncy_trn);

						$info_gen2['table_name']	= 'tbl_invoice';
						$info_gen2['table_id']		= $inserinvoiceid;
						$info_gen2['entry_type']	= 2;
						$info_gen2['ref_date']		= date('Y-m-d',strtotime($POST['invoice_date']));
						$info_gen2['ledger_id']		= $POST['cust_id'];
						$info_gen2['amount']		= $bill_sundry_addon_amount;
						$info_gen2['user_id']		= $_SESSION['user_id'];
						$info_gen2['cdate']			= date("Y-m-d H:i:s");
						$info_gen2['company_id']	= $_SESSION['company_id'];
						$info_gen2['ref_by'] = 'tbl_addon_bill_sundry';
						
						//$inserid_gen2=add_record("tbl_general_book", array_merge($info_gen2,$curncy_trn) , $dbcon);

						//add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,2,$POST['cust_id'],$bill_sundry_addon_amount,'',$POST['invoice_date'],'',$curncy_trn,$info_sundry);
					}
            		 
            		// plus entry credit & cust new entry with debit & sundry amt
            		// minus entry debit & cust new entry with credit & sundry amt
            	}
        	}

        	/**Update sales order table By Dhruv **/
        	if($inserinvoiceid){
        		if($POST['is_sales_order'] == 'yes'){
        			$sales_order=json_decode($POST['sales_order']);
        			foreach ($sales_order as $sales_order_id) {
        				if($POST['transaction_type'] == 1){
        					$sales_remaning_qty = brp_mysqli_fetch_array($dbcon->query("select sum(sot.remaning_invoice_qty) as remning from tbl_sales_order as so left join tbl_sales_ordertrn as sot on so.sales_order_id=sot.sales_order_id where so.sales_order_id= '".$sales_order_id."' group by sot.sales_order_id "));
	                        if($sales_remaning_qty['remning'] == 0){
	                        	$so_tbl['invoice_status'] = 1;
	                        	$so_tbl['used_invoice_id'] = $inserinvoiceid;
								$updatesoid=update_record('tbl_sales_order', $so_tbl,"sales_order_id='".$sales_order_id."'" , $dbcon);
	                        }
        				}else if($POST['transaction_type'] == 2){
        					$sales_alloc_remaning_qty = brp_mysqli_fetch_array($dbcon->query("select sum(sopt.remaning_invoice_qty) as remning from tbl_sales_order as so left join tbl_sales_ordertrn as sot on so.sales_order_id=sot.sales_order_id left join tbl_sales_order_production_trn as sopt on sot.sales_ordertrn_id=sopt.sales_ordertrn_id where so.sales_order_id= 5 group by sot.sales_order_id"));
	                        if($sales_alloc_remaning_qty['remning'] == 0){
	                        	$so_alloc_tbl['invoice_status'] = 1;
	                        	$so_alloc_tbl['used_invoice_id'] = $inserinvoiceid;
								$updatesoallocid=update_record('tbl_sales_order', $so_alloc_tbl,"sales_order_id='".$sales_order_id."'" , $dbcon);
	                        }
        				}
        				

        			}
        		}
        	}
        	//echo $POST['salesorderid'];exit;
        	if($inserinvoiceid){
        		$so_arry = array();
        		$so_arry= $POST['salesorderid'];
        		//print_r($so_arry);exit;
        		if(!empty($so_arry)){
					
        			//get so transaction id 

        			$sel_so = $dbcon->query("select * from tbl_invoicetrn where invoice_id='$inserinvoiceid'");
        			while($r_so=brp_mysqli_fetch_assoc($sel_so))
        			{
        				$so_trn_id = $r_so['so_allocation_id'];

        				$so_qty = $dbcon->query("select product_qty from tbl_sales_ordertrn where sales_ordertrn_id='$so_trn_id'");
						$so_count = brp_mysqli_fetch_assoc($so_qty);

						$inv_qty = $dbcon->query("select sum(product_qty) as total from tbl_invoicetrn where invoice_id='$inserinvoiceid'");
						$inv_count = brp_mysqli_fetch_assoc($inv_qty);

						$remain = $so_count['product_qty'] - $inv_count['total'];
						//echo $remain;exit;
	                    if($remain == 0){
	                    	$so_tbl['invoice_status'] = 1;
							$updatesoid=update_record('tbl_sales_ordertrn', $so_tbl,"sales_ordertrn_id='".$so_trn_id."'" , $dbcon);
	                    }
        			}

					
        		}
        	}

        	/*Update Tax Trn Table Start by Dhruv*/
	        if($inserinvoiceid){
				$tax_trn['tx_status'] = 0;
				$tax_trn['tx_trn_ref_id'] = $inserinvoiceid;
				$updatetcstrnid=update_record('tbl_tax_trn', array_merge($tax_trn,$curncy_trn),"tx_transaction_type='tbl_invoicetrn' and tx_status = 3" , $dbcon);
			}

			/*Update Salesman Table Start by Dhruv*/
			if($inserinvoiceid && $POST['enable_salesman']=='yes'){
				$salesman_trn['transaction_table_id'] = $inserinvoiceid;
				$updatesalesmantrnid=update_record('tbl_salesman_transaction', array_merge($salesman_trn,$curncy_trn),"transaction_voucher_type=".SALES_VOUCHER." and transaction_table_id = 0" , $dbcon);
			}

			/* Update voucher No */
			if($inserinvoiceid){
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=37 and company_id=".$_SESSION['company_id']);
			}

			/* Eway Bill API */
			if($inserinvoiceid && $POST['enable_ewaybill']=='yes')
			{
				$eway_row=getTransportEwayDetails($dbcon,SALES_VOUCHER);
				$company_data = get_company_data($dbcon,$_SESSION['company_id']);
				$customer_ledger_data = get_ledger_details($dbcon,$POST['cust_id']);
				$product_details = get_trans_by_inv_id($dbcon,$inserinvoiceid);
				$company_state_data = get_state_details($dbcon,'and stateid = '.$company_data['stateid'].'');
				$cust_state_data = get_state_details($dbcon,'and stateid = '.$customer_ledger_data['stateid'].'');
				$cust_city_data = get_city_details($dbcon,'and cityid = '.$customer_ledger_data['cityid'].'');
				$trasport_gst = get_trasport_data($dbcon,'and id = '.$eway_row['transport_id'].'');
				$sub_type = get_common_mst_data($dbcon,'and common_mst_id = '.$eway_row['eway_sub_type'].'');
				$trans_mode = get_common_mst_data($dbcon,'and common_mst_id = '.$eway_row['transport_mode'].'');
				
				$jsonobj .='
				{
					"Push_Data_List": [ ';

				foreach ($product_details as $product_data) {

					$jsonobj .='{
							 "GSTIN": "'.$company_data['vatno'].'",  
							 "Year": "'.date('Y',strtotime($POST['invoice_date'])).'",       
							 "Month": "'.date('m',strtotime($POST['invoice_date'])).'",      
							 "SupplyType": "O",
							 "SubType": "'.$sub_type['common_mst_desc'].'",       
							 "DocType": "INV",        
							 "DocNo": "'.$POST['invoice_no'].'", 
							 "DocDate": "'.date('Ymd',strtotime($POST['invoice_date'])).'",    
							 "SupGSTIN": "'.$company_data['vatno'].'",
							 "SupName": "'.$company_data['company_name'].'",       
							 "SupAdd1": "'.$company_data['address'].'",
							 "SupAdd2": "",				
							 "SupCity": "'.$company_data['city_name'].'",			
							 "SupState": "'.$company_state_data['gst_state_code'].'",				
							 "SupPincode": "'.$company_data['pincode'].'",		

							 "RecGSTIN": "'.$customer_ledger_data['gst_no'].'",     
							 "RecName": "'.$customer_ledger_data['l_name'].'",					
							 "RecAdd1": "'.$customer_ledger_data['m_address'].'",	 
							 "RecAdd2": "",						// blank
						     "Reccity": "'.$cust_city_data['city_name'].'",				
							 "RecState": "'.$cust_state_data['gst_state_code'].'",				
							 "Recpincode": "'.$customer_ledger_data['cust_pincode'].'",		

							 "TransMode": "'.$trans_mode['common_mst_desc'].'",
							 "TransporterId": "'.$trasport_gst['transportation_gst_number'].'", 
							 "TransporterName": "'.$trasport_gst['transportation_name'].'",
							 "TransDistance": "'.$eway_row['distance_km'].'",
							 "TransDocNo": "'.$eway_row['transport_doc_no'].'", 
							 "TransDocDate": "'.$eway_row['transport_doc_date'].'",
							 "VehicleType": "R",
							 "VehicleNo": "'.$eway_row['transport_vehicle_no'].'",


							 "ProductName": "'.$product_data['productName'].'",
							 "ProductDesc": "'.$product_data['product_desc'].'",
							 "HSNCode": "'.$product_data['hsnCode'].'",
							 "Quantity": "'.$product_data['product_qty'].'",
							 "QtyUnit": "'.$product_data['unit_code'].'",
							 "TaxableValue": "'.$product_data['taxable_value'].'",
							 "TotalValue": "'.$product_data['total'].'",
							 "SGSTRate": "'.$product_data['sgstPer'].'",
							 "SGSTValue": "'.$product_data['sgstValue'].'",
							 "CGSTRate": "'.$product_data['cgstPer'].'",
							 "CGSTValue": "'.$product_data['cgstValue'].'",
							 "IGSTRate": "'.$product_data['igstper'].'",
							 "IGSTValue": "'.$product_data['igstValue'].'",
							 "CessRate": 0,
							 "CessValue": 0,

							 "EWBUserName": "05AAACW3775F012",
							 "EWBPassword": "Admin!23",
							 "CessNonAdvol": 0,
							 "SubSupplyDesc": "",
							 "ShipFromStateCode": "05",
							 "ShipToStateCode": "05",

							 "TotalInvoiceValue": "'.$POST['g_total'].'",
							 "CessNonAdvolValue": 0,
							 "OtherValue": 0,

							 "dispatchFromGSTIN ": "'.$company_data['vatno'].'",
							 "dispatchFromTradeName": "'.$company_data['company_name'].'",	
							 "ShipToGSTIN": "'.$customer_ledger_data['gst_no'].'",		
							 "ShipToTradeName": "'.$customer_ledger_data['l_name'].'",	
							 "IsBillFromShipFromSame": "1",			
							 "IsBillToShipToSame": "1",

							 "IsGSTINSEZ": "'.$customer_ledger_data['enable_sez'].'"  
							 }, ';
					}		 
				$jsonobj .= ' ],
					 "Year": 2018,
					 "Month": 10,
					 "EFUserName": "29AAACW3775F000",
					 "EFPassword": "Admin!23..",
					 "CDKey": "1000687"
				}';
				
				//print_r($jsonobj);exit;
				$callEway = submitEwayApi($jsonobj);
			
				$obj = json_decode($callEway);
			
				$arr=json_decode($obj);

				//echo '<pre>';print_r($arr[0]);exit;
				
				$eway_bill_status=$arr[0]->IsSuccess;
				
			}

			if($eway_bill_status=='true')
			{
				$eway_status_trn['eway_bill_status']=1;
			}
			else
			{
				$eway_status_trn['eway_bill_status']=2;
			}


			//Update Invoice table with eway_no & date
			if($POST['enable_ewaybill'] == 'yes' && $eway_bill_status=='true'){
				$info_invtbl['eway_bill_no'] = $arr[0]->EWayBill;
				$info_invtbl['eway_bill_date'] = date('Y-m-d H:i:s',strtotime($arr[0]->Date));	
			}else{
				$info_invtbl['eway_bill_no'] = $POST['eway_bill_no'];
				$info_invtbl['eway_bill_date'] = date('Y-m-d',strtotime($POST['eway_bill_date']));	
			}
			
			$updateinvtbl=update_record('tbl_invoice', $info_invtbl,"invoice_id='".$inserinvoiceid."'" , $dbcon);
			
			$updatetcstrnid=update_record('tbl_ewaybill_transaction',array_merge($eway_status_trn,$curncy_trn),"eway_bill_voucher_type='1' and eway_bill_voucher_id ='0'" , $dbcon);

			/*Update Trasport and Eway trans Table Start by Dhruv*/
	        if($inserinvoiceid){
				$transp_trn['transport_transaction_table_id'] = $inserinvoiceid;
				$updatetcstrnid=update_record('tbl_transport_transaction', array_merge($transp_trn,$curncy_trn),"transport_transaction_table='tbl_invoice' and transport_transaction_table_id = 0" , $dbcon);
			}

			if($inserinvoiceid)
			{
				$arr['eid']=$inserinvoiceid;	
				$arr['msg']=1;
			}
			else
			{
				$arr['msg']=0;
			}
			
			echo json_encode($arr);	


		}		
		else if(strtolower($POST['mode']) == "edit") {
		
			 if(isset($POST['currency_enable'])){
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }

	        $info['invoice_no']	= $POST['invoice_no'];
	        $info['invoice_date']	= date('Y-m-d',strtotime($POST['invoice_date']));
	        $info['invoice_due_date']	= date('Y-m-d',strtotime($POST['invoice_due_date']));
	        $info['dc_enable'] = $POST['dc_enable']; //Added new by dhruv
	        $info['challan_no']	= (isset($POST['dc_enable'])) ? $POST['challan_no'] : 0 ; //Added new by dhruv
	        $info['challan_date']	= (isset($POST['dc_enable'])) ? date('Y-m-d',strtotime($POST['challan_date'])) : 0 ; //Added new by dhruv  	
			
	        $info['po_enable'] = $POST['po_enable']; //Added new by dhruv    
	        $info['order_no']	= (isset($POST['po_enable'])) ? $POST['order_no'] : 0 ; //Added new by dhruv	        
	        $info['order_date']	= (isset($POST['po_enable'])) ? date('Y-m-d',strtotime($POST['order_date'])) : 0 ; //Added new by dhruv	   

	        $info['currency_enable'] = $POST['currency_enable']; //Added new by dhruv    
	        $info['currency_id']	= (isset($POST['currency_enable'])) ? $POST['currency_id'] : 0 ; //Added new by dhruv
	        $info['currency_rate']	= (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1 ; //Added new by dhruv 

	        $info['sale_material_center']	= $POST['sale_material_center']; //Added new by dhruv
	        $info['is_sales_order']	= $POST['is_sales_order']; //Added new by dhruv
	        $info['enable_cost_center'] = (isset($POST['enable_cost_center']) && ($POST['enable_cost_center']=='yes')) ? 1 : 0; //Added new by dhruv
	        $info['enable_tcs_details'] = (isset($POST['enable_tcs_details']) && ($POST['enable_tcs_details']=='yes')) ? 1 : 0; //Added new by dhruv
	        $info['enable_ewaybill'] = (isset($POST['enable_ewaybill']) && ($POST['enable_ewaybill']=='yes')) ? 1 : 0; //Added new by dhruv

	        $info['eway_bill_no'] = $POST['eway_bill_no']; //Added new by dhruv
	        $info['eway_bill_date'] = date('Y-m-d',strtotime($POST['eway_bill_date'])); //Added new by dhruv
			$info['sales_ledger_id']	= implode(",",$POST['sales_ledger_id']);	
				
	        $info['cust_id']	= $POST['cust_id'];
	        $info['sales_ledger_id']= $POST['sales_ledger_id'];
			$info['basic_total']	= $POST['total'];
	        $info['g_total']	= $POST['g_total'];

	        $info['print_status']	= $POST['print_status'];
	        $info['financial_year_id']	= $POST['financial_year'];

	        $info['remark']			= text_rnremove($POST['remark']);
	        $info['install_type']	= (isset($POST['install_type']) && ($POST['install_type']=='yes')) ? 1 : 0; //Added new by dhruv
	        $info['cdate']			= date("Y-m-d H:i:s");
	        $info['user_id']		= $_SESSION['user_id'];
	        $info['company_id']		= $_SESSION['company_id'];
	        $info['branch_id']   = $POST['branch_id'];
	        $info['invoice_status']		= 0;
	        $info['usertype_id']	= $_SESSION['usertype_id'];
			
			$info['tcs_amount']   = $POST['tcs_amount'];
			$info['tcs_per']   = $POST['tcs_per'];
			
			//print_r($POST);exit;
			
			$inserinvoiceid=update_record('tbl_invoice', $info,"invoice_id=".$POST['eid'] , $dbcon);
	        //$inserinvoiceid=add_record('tbl_invoice', array_merge($info,$curncy_trn), $dbcon);

			$query1="select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[eid]'  and isdelete=0 and sundry_voucher_table='tbl_invoice'  ";
			$rel1=brp_mysqli_fetch_all($dbcon->query($query1));

			foreach ($rel1 as $bill_sundry_addon){
				$info_general_sundry['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
				update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_addon['sundry_ledger_id']." and table_name='tbl_bill_sundry_transaction' 
						and table_id= ".$bill_sundry_addon['sundry_id']." " ,$dbcon);
			}

			$info_invoice_sundry['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
			update_record("tbl_general_book",$info_invoice_sundry,"table_name='tbl_invoice' 
						and table_id= ".$POST['eid']." " ,$dbcon);
			

			if($inserinvoiceid)
			{	
				$arr['msg']=1;
				$arr['eid']=$POST['eid'];	
			}
			else
			{
				$arr['msg']=0;
			}
			
			echo json_encode($arr);	
		}
		else if(strtolower($POST['mode']) == "delete") {
			$query="select * from tbl_invoicetrn where invoice_id=".$POST['eid'];
			$result=$dbcon->query($query);
			while($row=mysqli_fetch_assoc($result)){		 
				$info_de['stock_status']=2;
				$updateid1=update_record("tbl_stock_trn", $info_de,"ref_name='invoice_trn' and ref_id=".$row['trancation_id'] ,$dbcon);
			}		 
					 
			$info['invoice_status']	= 2;
			$info1['trancation_status']	= 2;
			$informdr['status'] = 2;
			$info_sales_order['invoice_status']  = 0;
			$info_srl['inv_srl_trn_status']  = 0;
			$updatesalesid=update_record('tbl_sales_order', $info_sales_order,"used_invoice_id=".$POST['eid'], $dbcon);
			$updateinvoiceid=update_record('tbl_invoice', $info,"invoice_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_invoicetrn', $info1,"invoice_id=".$POST['eid'] , $dbcon);	
			$updatesrlid=update_record('tbl_inv_srl_trn', $info_srl,"invoice_id=".$POST['eid'] , $dbcon);	
			//Update Payment Reminder
			$updatermdrid=update_record('todo_mst', $informdr,"ref_id=".$POST['eid']." and ref_table='tbl_invoice'" , $dbcon);
			//Update Serial Number
			//$deleteid=delete_record('tbl_serialtrn',"invoice_id=".$POST['eid'], $dbcon);
			
			$info_gen['genral_book_status']		= 2;
			$updateinvoiceid=update_record('tbl_general_book', $info_gen,"table_name='tbl_invoice' and table_id=".$POST['eid'] , $dbcon);	
			
			//tax transaction
			
			$sel_itrn = $dbcon->query("select * from  tbl_invoicetrn where invoice_id='POST[eid]' and trancation_status='2'");
			while($r_itrn=brp_mysqli_fetch_array($sel_itrn))
			{
				$info_tax_trn['tx_status']=2;
				update_record("tbl_tax_trn", $info_tax_trn,"tx_transaction_id='r_itrn[trancation_id]' and tx_transaction_type='tbl_invoicetrn'" ,$dbcon);
			}
			
			//Eway Bill Transaction
			
			$eway_trans['isdelete']=1;
			$updateinvoiceid=update_record('tbl_ewaybill_transaction', $eway_trans,"eway_bill_voucher_table='tbl_invoice' and eway_bill_voucher_id=".$POST['eid'] , $dbcon);	
			
			//Transport Transaction
			
			$transport_transaction['transportation_status']=1;
			$updateinvoiceid=update_record('tbl_transport_transaction', $transport_transaction,"transport_transaction_table='tbl_invoice' and transport_transaction_table_id=".$POST['eid'] , $dbcon);	
			
			
			//Salesman Transaction
			
			$salesman_transaction['isdelete']=1;
			$updateinvoiceid=update_record('tbl_salesman_transaction', $salesman_transaction,"transaction_table='tbl_invoice' and transaction_table_id=".$POST['eid'] , $dbcon);	
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"invoice_add",3,"tbl_invoice",$POST['eid']);
			
			//Cost Center Transaction
			
			$info_cost['costcenter_status'] = 2;
			$updateid1=update_record("tbl_cost_center_transaction", $info_cost, "table_name='tbl_invoice' and table_id=".$POST['eid'], $dbcon);
			
			//TCS Deduction Transaction
			
			$info_tcs['isdelete'] = 1;
			$updateid1=update_record("tbl_tcs_deduction_transaction", $info_tcs, "tcs_sale_id=".$POST['eid'], $dbcon);
			
			//Bill Sundry Transaction
			
			$info_bsun['isdelete'] = 1;
			$updateid1=update_record("tbl_bill_sundry_transaction", $info_bsun,
				"sundry_voucher_table='tbl_invoice' and sundry_voucher_id=".$POST['eid']."", $dbcon);
			
			$sel_bsun = $dbcon->query("select * from tbl_bill_sundry_transaction where sundry_voucher_table='tbl_invoice' and sundry_voucher_id=".$POST['eid']." and isdelete='1'");
			while($r_bsun=brp_mysqli_fetch_array($sel_bsun))
			{
				$info_bsun_general['genral_book_status'] = 2;
				$updateid1=update_record("tbl_general_book", $info_bsun_general, "table_name='tbl_bill_sundry_transaction' and table_id=".$r_bsun['sundry_id']." ", $dbcon);
			}
			
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") {

			//echo '<pre>'; print_r($POST);exit;
			
			if(isset($POST['currency_enable']) && $POST['currency_enable']==1){
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }
			//$POST['currency_enable'];
			//print_r($curncy_trn);
			$product_detail = get_product_detail($dbcon,$POST['product_id']);

			$company_state = get_company_data($dbcon,$_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
			$sale_gst = get_tax_cat_by_hsn($dbcon,$POST['product_hsn_code']);
			
			$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);

			$cgst_tax_rate=0;
			$sgst_tax_rate=0;
			$igst_tax_rate=0;
			if($product_detail['product_gst'] == 'including'){
				$prorate = $POST['product_rate'] * 100 /(100 + $sale_gst['tax_gst']);
			}else{
				$prorate = $POST['product_rate']; 
			}
			if(($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)){

				$product_amt = $POST['product_amount'];
				$gst = $sale_gst['tax_gst']/2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst*$product_amt)/100;
				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst*$product_amt)/100;	
				
			}else{
				$product_amt = $POST['product_amount'];
				$igst_tax_per = $sale_gst['tax_gst'];
				$igst_tax_rate = ($sale_gst['tax_gst']*$product_amt)/100;				
			}
			
			//print_r($sale_gst);
			//print_r($POST);exit;
		
			$info1['product_id']		= $POST['product_id'];
			$info1['description']		= $POST['product_des'];
			$info1['ser_status']		= $POST['ser_status'];
			$info1['product_hsn_code']	= $POST['product_hsn_code'];
			$info1['product_qty']		= $POST['product_qty'];
			$info1['product_rate']		= $prorate;
			$info1['product_disc']		= $POST['product_disc'];
			$info1['unit_id']			= $POST['unit_id'];
			$info1['product_spec']		= $POST['product_spec'];
			//$info1['product_amount']	= $POST['product_amount'];
			$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
			$info1['cgst_tax_rate']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
			$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
			$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
			$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;
			$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;

			$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
			$info1['product_discount']	= $POST['product_discount'];
			$info1['discount_per']		= $POST['discount_per'];
			//$info1['formulaid']			= $POST['formulaid'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['product_amount']	= $product_amt;
			//$info1['bill_value']		= $POST['bill_value'];
			//$info1['bill_black_value']	= $POST['bill_black_value'];
			$info1['taxable_value']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
			$info1['total'] = $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $product_amt;
			$info1['product_type']		= $product_detail['product_type'];
			//$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
            // $info1=array_merge($info1,$info);
			$info1['user_id']	= $_SESSION['user_id'];

			$table='tbl_invoicetrn';
			$tableid='trancation_id';

			if(!empty($POST['invoice_id'])){
				$info1['invoice_id']= $POST['invoice_id'];
				$info1['trancation_status']	= 0;
			}
			else{
				$info1['trancation_status']	= 1;
			}
			
			if(empty($POST['edit_id'])){

				$inserid=add_record($table, array_merge($info1,$curncy_trn), $dbcon, $POST['branch_id']);
			
			}
			else{
				
				$updateid=update_record($table, array_merge($info1,$curncy_trn),$tableid."=".$POST['edit_id'] , $dbcon, $POST['branch_id']);	
				
				$inserid=$POST['edit_id'];
			}
			
				
			/* insert to tax transaction table by Dhruv */
			if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'CGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_invoicetrn",$POST['product_id'],3,$POST['edit_id'],$POST['branch_id']);
			}
			if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'SGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_invoicetrn",$POST['product_id'],3,$POST['edit_id'],$POST['branch_id']);
			}
			if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'IGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_invoicetrn",$POST['product_id'],3,$POST['edit_id'],$POST['branch_id']);
			}
			
			// check for the addiotional tax on product Start -- dhaval
			
			$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$POST['product_amount'],$inserid,$POST['product_id'],$POST['edit_id'],$POST['branch_id'],'tbl_invoicetrn');
			
			// check for the addiotional tax on product End  -- dhaval
			
				/***Update stock trn and allocate table By Dhruv**/
				$remaning_invoice_qty = $POST['trans_stock'] - $POST['product_qty'];
				if($POST['trans_type'] == 1){
					
					if($remaning_invoice_qty < 0){
						$info_so_trans['remaning_invoice_qty'] =  0 ;
						$info_so_trans['invoice_status'] =  1 ;
					}else{
						$info_so_trans['remaning_invoice_qty'] = $remaning_invoice_qty;
						$info_so_trans['invoice_status'] =  0;
					}
					$update_sotransid=update_record('tbl_sales_ordertrn', $info_so_trans,"sales_ordertrn_id=".$POST['trans_id'] , $dbcon);
				}
				if($POST['trans_type'] == 2){
					if($remaning_invoice_qty < 0){
						$info_alloc_trans['remaning_invoice_qty'] =  0 ;
					}else{
						$info_alloc_trans['remaning_invoice_qty'] = $remaning_invoice_qty;
					}
					$update_alloctransid=update_record('tbl_sales_order_production_trn', $info_alloc_trans,"sales_order_production_trn_id=".$POST['trans_id'] , $dbcon);
				}


			if(!empty($POST['invoice_id'])){
				$info_de['stock_status']=2;
				$updateid1=update_record("tbl_stock_trn", $info_de,"ref_name='invoice_trn' and ref_id=".$inserid ,$dbcon);
				
				$query="select i.*,sum(trn.product_amount) as gamo from tbl_invoice as i
						left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id
						where trn.trancation_status=0 and i.invoice_id=".$POST['invoice_id'];
					$result=$dbcon->query($query);
					$row=mysqli_fetch_assoc($result);
					
				minus_stock($dbcon,$info1['product_id'],$info1['unit_id'],$row['invoice_date'],"invoice_trn",$inserid,$info1['product_qty']);
				
				$general_book_id=get_general_book_id($dbcon,'tbl_invoice',$POST['invoice_id'],$row['cust_id']);
				
				// add_general_book_entry($dbcon,"tbl_invoice",$POST['invoice_id'],2,$row['cust_id'],$row['gamo'],$general_book_id,$row['invoice_date']);
				// general_book_tax_entry($dbcon,$POST['invoice_id']);
				// general_book_sercices_entry($dbcon,$POST['invoice_id']);
			}
		
		}
		else if(strtolower($POST['mode']) == "formulavalue") 
		{
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
				}
				else	
				{
					 $rate=($total)*$tax['tax_value']/100;
				}
				echo '<div class="form-group">
                                            <label class="col-md-5 control-label">'.$tax['tax_name'].'</label>
                                            <div class="col-md-5 col-xs-11">
                                            <input id="taxvalue'.$j.'" name="taxvalue'.$j.'" value= "'.$rate.'" type="text" class="form-control" readonly="readonly">
                                        </div>
					</div>
					<input id="taxname'.$j.'" name="taxname'.$j.'" value= "'.$tax['tax_name'].'" type="hidden" class="form-control">';
					$rate_total=$rate_total+$rate;
					$j++;
			}
			$g_total=$rate_total+$c_total;
			echo '<input id="rate" name="rate" value= "'.$g_total.'" type="hidden" class="form-control" >';
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			$pid=$POST['eid'];
			//$qry="select * from tbl_product where product_id=".$POST['eid'];
			$qry="select mst.*,unit.unit_name from product_mst as mst
			left join unit_mst as unit on unit.unitid = mst.product_base_unit
			where product_id=$pid";
			$result=$dbcon->query($qry);
			$row=brp_mysqli_fetch_assoc($result);

			$qry3="SELECT h.hsn_id,h.hsn_code,h.sale_gst,t.tax_gst,t.tax_cat_id FROM `mst_hsn_code` as h left join tbl_tax_category as t on t.tax_cat_id=h.sale_gst where h.hsn_status=0 and h.hsn_id=".$row['product_hsn']." ";
			$sale_gst=brp_mysqli_fetch_assoc($dbcon->query($qry3));
			
			$qry1="select led.stateid as lst,com.stateid as cst from tbl_ledger as led 
				left join tbl_company as com on com.company_id=led.company_id
				where l_id=".$POST['cust_id'];
			$result1=$dbcon->query($qry1);
			$row1=brp_mysqli_fetch_assoc($result1);
					
			echo json_encode(array_merge($row, $sale_gst));
		
		}	
		else if(strtolower($POST['mode'])== "load_product_typeiwse")
		{
			echo get_product($dbcon,"",$POST['type_id']);
		}
		else if(strtolower($POST['mode'])== "get_product_amount")
		{
			$arr=get_product_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "load_podata")
		{
				getpono($dbcon,$POST['cust_id']);
		}
		else if(strtolower($POST['mode'])== "load_podate")
		{
			$qry2="select * from tbl_pono where po_id=".$POST['po_id'];
			$result2=mysqli_fetch_assoc($dbcon->query($qry2));
			echo json_encode($result2);	
		}
		else if(strtolower($POST['mode'])== "reminder")
		{
			$qry2="select * from pay_terms where terms_id=".$POST['paymentterms'];
			$result2=mysqli_fetch_assoc($dbcon->query($qry2));
			echo json_encode($result2);	
		}
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=37 and company_id=".$_SESSION['company_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		
		}
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
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
			$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "update_total") {

			//update total , net total , general books entry at edit time start - dhaval 
			$bill_sundry_tax = array_combine($POST['bill_sundry_tax'],$POST['bill_sundry_tax1']);

			if($POST['invoice_id']>0)
			{
				$query="select sales_ledger_id,cust_id from tbl_invoice where invoice_id=".$POST['invoice_id']." ";
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query)); 

				$update_invoice['g_total'] = $POST['g_total'];
				$update_invoice['basic_total'] = $POST['basic_total'];
				//$update_invoice['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
				update_record("tbl_invoice",$update_invoice," invoice_id=".$POST['invoice_id'] ,$dbcon);

				//Update Basic total in General book for invoice table - sales ledger entry
				$info_gen['amount'] = $POST['basic_total'];
				$info_gen['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
				update_record("tbl_general_book",$info_gen," table_id=".$POST['invoice_id']." and ledger_id=".$rel['sales_ledger_id']." and table_name='tbl_invoice'" ,$dbcon);

				//Update Basic total in General book for invoice table - customer ledger entry
				$info_gen1['amount'] = $POST['g_total'];
				$info_gen1['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
				update_record("tbl_general_book",$info_gen1," table_id=".$POST['invoice_id']." and ledger_id=".$rel['cust_id']." and ref_by='' and genral_book_status=0  and table_name='tbl_invoice'" ,$dbcon);
				
				//update bill sundry in bill sundry table and general table 
				
				foreach ($bill_sundry_tax as $bill_sundry_tax_id => $bill_sundry_tax_amount) {
           			
					$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
					$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			        $info_sundry_tax['user_id']	= $_SESSION['user_id'];
			        $info_sundry_tax['company_id']	= $_SESSION['company_id'];
					$info_sundry_tax['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
					$update_sundryid = update_record("tbl_bill_sundry_transaction",$info_sundry_tax," sundry_ledger_id=".$bill_sundry_tax_id." and sundry_voucher_table='tbl_invoice' and sundry_voucher_id='$POST[invoice_id]'" ,$dbcon);
					
					$query1="select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[invoice_id]' and sundry_voucher_table='tbl_invoice' and sundry_ledger_id=".$bill_sundry_tax_id." and isdelete=0  ";
					$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1)); 
					
					$info_general_sundry['amount'] = $bill_sundry_tax_amount;
					$info_general_sundry['cdate']	= date("Y-m-d H:i:s");
			        $info_general_sundry['user_id']	= $_SESSION['user_id'];
			        $info_general_sundry['company_id']	= $_SESSION['company_id'];
					$info_general_sundry['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
					update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_tax_id." and table_name='tbl_bill_sundry_transaction' 
						and table_id= ".$rel1['sundry_id']." " ,$dbcon);
					//add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_tax_insert,1,$bill_sundry_tax_id,$bill_sundry_tax_amount,'',$POST['invoice_date'],'',$curncy_trn);
					
					//echo $bill_sundry_tax_id.'-'.$bill_sundry_tax_amount."<br>";
            	}
				
			 /* $dsun = $dbcon->query("select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[invoice_id]' and isdelete='0'");
			    while($r=brp_mysqli_fetch_array($dsun))
				{
					
					$sundry_id = $r['sundry_id'];
					
					$sundry['sundry_amount'] = $r['sundry_amount'];
					$sundry['cdate']			= date("Y-m-d H:i:s A");
					$sundry['user_id']			= $_SESSION['user_id'];
					$sundry['company_id']		= $_SESSION['company_id'];					
					
					update_record("tbl_bill_sundry_transaction",$sundry," sundry_id=".$sundry_id." and sundry_voucher_table='tbl_invoice'" ,$dbcon);
									
					$sundry_general['amount'] = $r['sundry_amount'];
					$sundry_general['entry_type'] = 1;
					
					$sundry_general['branch_id'] = $POST['branch_id'];
					$sundry_general['cdate']			= date("Y-m-d H:i:s A");
					$sundry_general['user_id']			= $_SESSION['user_id'];
					$sundry_general['company_id']		= $_SESSION['company_id'];
					
					
					update_record("tbl_general_book", $sundry_general," table_id=".$sundry_id." and table_name='tbl_bill_sundry_transaction'" ,$dbcon);
					
				
				} */
				
			}
			//update total , net total , general books entry at edit time end - dhaval 
			
			//print_r($bill_sundry_tax);
			//print_r($bill_sundry_addon);
			
		}
     	else if(strtolower($POST['mode']) == "load_tempoutward") {
			if($POST['eid']){
				$query="select mst.*,product.product_name,product.product_type,product.product_base_unit,cat.unit_name from  tbl_invoicetrn as mst
					left join unit_mst as cat on cat.unitid=mst.unit_id 
					left join product_mst as product on product.product_id=mst.product_id  
					where trancation_status=0 and invoice_id=".$POST['eid'];
					
			}
			else{
				$query="select mst.*,product.product_name,product.product_type,cat.unit_name,product.product_base_unit from  tbl_invoicetrn as mst
					left join unit_mst as cat on cat.unitid=mst.unit_id 
					left join product_mst as product on product.product_id=mst.product_id  
					where trancation_status=1 and mst.user_id=".$_SESSION['user_id'];
			}
			
			$str ="";
			/*$query="select mst.*,product.product_name,cat.unit_name,m.model_name from  tbl_invoicetrntemp as mst 
			left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id left join model_mst as m on m.model_id=mst.model_id  where temp_status=0 and mst.user_id=".$_SESSION['user_id']." order by tempinvoicetrn_id Desc";*/
			$result=$dbcon->query($query);
			$str .= ' <div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="25%">Product Name</th>
							<th class="text-center" width="8%">Qty</th>
							<th class="text-center" width="8%">Rate</th>
							<th class="text-center" width="6%">Unit</th>
							<th class="text-center" width="8%">Discount</th>
							<th class="text-center" width="8%">Tax Details</th>
							<th class="text-center" width="12%">Amount</th>
						 	<th class="text-center" width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;$j=0;
			while($rel=mysqli_fetch_assoc($result))
			{
				$cnt_pro_stk='';
				$product_type_arr = array("0", "1", "2", "3", "4", "5");
				if (in_array($rel['product_type'], $product_type_arr)){
					if(!empty($rel['unit_id'])){
						$unit_id=$rel['unit_id'];
					}else{
						$unit_id=$rel['product_base_unit'];
					}
					$current_stock=get_current_stock_new($dbcon,$rel['product_id'],$unit_id);
					$where=" and trancation_status!='2' and invoice_id='0'";
					$unclear_qty = get_unclear_stock($dbcon,$rel['product_id'],$unit_id,'tbl_invoicetrn','product_qty','product_id',$where);
					$cnt_pro_stk = $current_stock - $unclear_qty;
				}
				else{
					$cnt_pro_stk=9999;
				}
				//var_dump($cnt_pro_stk);
				$product_name=$dbcon->real_escape_string($rel['product_name']);
				
				$so = "select * from tbl_sales_ordertrn where sales_ordertrn_id=".$rel['so_allocation_id'];
				$so_exe = $dbcon->query($so);
				$so_row = mysqli_fetch_array($so_exe); 
				
				$with_out_stock_invoice ="";
				if($POST['isstockngative'] == ''){
					
					if($cnt_pro_stk <= $rel['product_qty']){
						
						$with_out_stock_invoice = "<strong style='color:red;' >Product stock is not enough.</strong>";
						$j++;
					}
				}
				//$str .= $j;
				
				$cgst_tax="";				
				$sgst_tax="";				
				$igst_tax="";				
				
				if($rel['cgst_tax_per']!=0)
				{
					$cgst_tax="<Strong>CGST (".$rel['cgst_tax_per'].") : </strong>".$rel['cgst_tax_rate'];
				}

				if($rel['sgst_tax_per']!=0)
				{
					$sgst_tax="<Strong>SGST (".$rel['sgst_tax_per'].") : </strong>".$rel['sgst_tax_rate'];
				}
				
				if($rel['igst_tax_per']!=0)
				{
					$igst_tax="<Strong>IGST (".$rel['igst_tax_per'].") : </strong>".$rel['igst_tax_rate'];
				}
				
				//sales ored number 
				$so_details='';
				if($rel['transaction_type']==1)
				{
					$so_details="<strong style='color:blue'> Sales Order No:  </strong><strong style='color:red'>".get_id_detail($dbcon,'tbl_sales_order','sales_order_id',$so_row['sales_order_id'],'sales_order_no').'</strong>';
				}
				else if($rel['transaction_type']==2)
				{
					$so_details="<strong style='color:blue'> Sales Order No:  </strong><strong style='color:red'>".get_sales_order_by_allocation($dbcon,$rel['so_allocation_id'])."</strong>"."<br>"."<strong style='color:green'>(Allocated)</strong>";
				}
				else
				{
					$so_details='';
				}
					
			 	$str .= '<tr id="fieldtr'.$id.'" >
					
					<td style="vertical-align:top;" class="text-center">
						<b>'.$rel['product_name'].'<br>'.$so_details.'<br><strong style="color:green">Current Stock : '.$cnt_pro_stk.'</strong><br>'.$with_out_stock_invoice.'</b><br><b>Description:</b>'.$rel['description'].'
					</td>
					
					<td style="vertical-align:top;" class="text-center">
						'.$rel['product_qty'].'
						<input type="hidden" id="trn_pro_stk'.$i.'" name="trn_pro_stk[]" value="'.$rel['product_qty'].'">
						<input type="hidden" id="cnt_pro_stk'.$i.'" name="cnt_pro_stk[]" value="'.$cnt_pro_stk.'">						
						
					';
					
					$str .= '</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['product_rate'].'
					</td>				
					<td style="vertical-align:top" class="text-center">
						'.$rel['unit_name'].'
					</td>
					<td style="vertical-align:top" class="text-center">
						'.$rel['product_discount'].' ('.$rel['discount_per'].'%)
					</td>
					<td>
						'.$cgst_tax.'<br>'.$sgst_tax.'<br>'.$igst_tax.'
					</td>
					<td style="vertical-align:top" class="text-center">
						'.($rel['product_amount']).'<br>
						
					</td>
					
					<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.$rel['product_amount'].'"/>
					<td style="vertical-align:top">
						<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['trancation_id'].',\' tbl_invoicetrn\',\'trancation_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['trancation_id'].',\' tbl_invoicetrn\',\'trancation_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
					</td>	
				</tr>';
				$i++;
				if($rel['product_type']!="8"){
					$sales_account_amount=$sales_account_amount+$rel["taxable_value"];
				}
			}
		}
		else{
			$str .= '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
			
			$str .= '<input type="hidden" name="sales_account_amount" id="sales_account_amount" value="'.$sales_account_amount.'" />
			</table>			 
					</div></div>';
			$row['html_data']=$str;
			if($j>0){
				$row['stock'] = "1";
			}
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("SELECT mst.*,pro.product_name,cat.unit_name,pro.product_gst 
				FROM tbl_invoicetrn as mst 
				left join unit_mst as cat on cat.unitid = mst.unit_id
				left join product_mst as pro on mst.product_id=pro.product_id 
				left join unit_mst as unit on unit.unitid WHERE trancation_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			/*if(strtolower($POST['table'])=='tbl_invoicetrntemp')
			{
				$row['producthtml']=getproduct($dbcon,0,'0,2');
			}
			else
			{
					$row['producthtml']=getproduct($dbcon,0,'0,2');
			}*/
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');
			
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			//echo '<pre>';print_r($POST);exit;
			$row=array();
			$info['trancation_status']=2;	
			
			$updateid=update_record("tbl_invoicetrn", $info, "trancation_id=".$POST['eid'] , $dbcon);
			
				$info_de['stock_status']=2;
				$updateid1=update_record("tbl_stock_trn", $info_de,"ref_name='invoice_trn' and ref_id=".$POST['eid'] ,$dbcon);
			
			// $info_gen['genral_book_status']=2;	
			// $updateid1=update_record("tbl_general_book", $info_gen, "table_name='tbl_invoicetrn' and table_id=".$POST['eid'] , $dbcon);
			
			//update tax transaction table By Dhruv
			$info_tax['tx_status']=2;	
			$updatetax=update_record("tbl_tax_trn", $info_tax, "tx_transaction_type='tbl_invoicetrn' and tx_transaction_id=".$POST['eid'] , $dbcon);
			
			$query="select * from tbl_invoicetrn where trancation_id=".$POST['eid']." ";
			$prel=mysqli_fetch_assoc($dbcon->query($query));


			// if($prel['invoice_id']!=0){
			// 	$general_book_id=get_general_book_id($dbcon,'tbl_invoice',$prel['invoice_id'],$prel['cust_id']);
				
			// 	$query1="select sum(product_amount) as gamo from tbl_invoicetrn as trn left join tbl_invoice as mst on mst.invoice_id=trn.invoice_id where trancation_status=0 and invoice_id=".$prel['invoice_id']." order by trancation_id DESC";
			// 	$prel1=mysqli_fetch_assoc($dbcon->query($query1));
				
			// 	add_general_book_entry($dbcon,"tbl_invoice",$prel['invoice_id'],2,$prel['cust_id'],$prel1['gamo'],$general_book_id,$prel['invoice_date']);
			// 	general_book_tax_entry($dbcon,$prel['invoice_id']);
			// }

			/***Update stock trn and allocate table By Dhruv**/
			if($prel['transaction_type'] == 1){
				$info_so_trans['remaning_invoice_qty'] = $prel['product_qty'];
				$info_so_trans['invoice_status'] = 0;
				$update_sotransid=update_record('tbl_sales_ordertrn', $info_so_trans,"sales_ordertrn_id=".$prel['so_allocation_id'] , $dbcon);
			}
			if($prel['transaction_type'] == 2){
				$info_alloc_trans['remaning_invoice_qty'] = $prel['product_qty'];
				$update_alloctransid=update_record('tbl_sales_order_production_trn', $info_alloc_trans,"sales_order_production_trn_id=".$prel['so_allocation_id'] , $dbcon);
			}			
			

			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "last_rate")
		{
			$query="select product_rate,trancation_id,trancation_status,product_id from tbl_invoicetrn as trn left join tbl_invoice as mst on mst.invoice_id=trn.invoice_id where cust_id=".$POST["cust_id"]." and product_id=".$POST["product_id"]." and trancation_status=0 order by trancation_id DESC";
			$prel=mysqli_fetch_assoc($dbcon->query($query));
			echo $prel['product_rate'];
		}
		else if(strtolower($POST['mode'])== "load_consignee")
		{
				echo get_custmer_consignee($dbcon,$POST['cust_id']);
		}
		else if(strtolower($POST['mode'])== "load_sales_order")
		{
			echo get_sales_order($dbcon,$POST['cust_id'],$POST['branch_id']);
		}
		else if(strtolower($POST['mode'])== "load_sales_order_data")
		{
			
			//$deleteid=delete_record('tbl_invoicetrn',"trancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
			
			$query_inv="select * from tbl_invoicetrn as trn 
					where trn.trancation_status=3 and trn.user_id=".$_SESSION['user_id']." and trn.company_id=".$_SESSION['company_id'];
				$rs_dispatch_inv=$dbcon->query($query_inv);	
			while($rel_inv=mysqli_fetch_assoc($rs_dispatch_inv))
			{	
				$info_inv['trancation_status'] = 2;
				$updateid_in=update_record('tbl_invoicetrn', $info_inv,"trancation_id=".$rel_inv['trancation_id'] ,$dbcon);
				
				$info_utax['tax_used_status'] = 2;
				$updateidutax=update_record('tbl_used_tax', $info_utax,"table_name='tbl_invoicetrn' and table_id='trancation_id' and used_transaction_id=".$rel_inv['trancation_id'] ,$dbcon);
			
			}
			
			
			
			/* $q = $dbcon -> query("SELECT * from tbl_sales_order where sales_order_id=".$POST['sales_order_id']);
			$rel = $q->fetch_assoc();
			
			$resp['transport_id'] = $rel['transport_id'];
			$resp['sales_order_no'] = $rel['sales_order_no'];
			$resp['sales_order_date'] = date("d-m-Y",strtotime($rel['sales_order_date']));
			$resp['pro_html'] = get_sales_order_data($dbcon,$POST['sales_order_id']);
			echo json_encode($resp); */
			
			$query="select * from tbl_sales_ordertrn as trn 
					where trn.sales_ordertrn_status=0 and trn.invoice_status=0 and trn.sales_order_id=".$POST['sales_order_id'];
				$rs_dispatch=$dbcon->query($query);	
				while($rel=mysqli_fetch_assoc($rs_dispatch))
				{	
					$resve_stoc=reserve_stock($dbcon,$rel['product_id'],$rel['unit_id'],"","","",$rel['sales_ordertrn_id'],$rel['branch_id']);
					
					if($resve_stoc>0){
						
						$query_used="select IFNULL(sum(product_qty),0) as used_qty from tbl_invoicetrn as trn 
								where trn.trancation_status=0 and trn.sales_ordertrn_id=".$rel['sales_ordertrn_id'];
							$rs_dispatch_used=$dbcon->query($query_used);	
						$rel_used=mysqli_fetch_assoc($rs_dispatch_used);
						
						$pending_qty=$rel['product_qty']-$rel_used['used_qty'];
						if($pending_qty>0){
							if($pending_qty>=$resve_stoc){
								$product_qty=$resve_stoc;
							}else{
								$product_qty=$pending_qty;
							}
							
							$total_value=$product_qty*$rel['product_rate'];
							if($rel['discount_per']>0){
								$discount_amount=($total_value*$rel['discount_per'])/100;
							}else{
								$discount_amount=0;
							}
							$taxablevalue=$total_value-$discount_amount;
							$product_amount=find_with_tax_amount($dbcon,$rel['formulaid'],$taxablevalue);
							
							$info1['product_id']		= $rel['product_id'];
							$info1['product_hsn_code']	= $rel['product_hsn_code'];
							$info1['product_qty']		= $product_qty;
							$info1['product_rate']		= $rel['product_rate'];
							$info1['unit_id']			= $rel['unit_id'];
							$info1['product_discount']	= $discount_amount;
							$info1['discount_per']		= $rel['discount_per'];
							$info1['formulaid']			= $rel['formulaid'];
							$info1['company_id']		= $_SESSION['company_id'];
							$info1['product_amount']	= $product_amount;
							$info1['taxable_value']		= $taxablevalue;
							$info1['sales_ordertrn_id']	= $rel['sales_ordertrn_id'];
							$info1['user_id']			= $_SESSION['user_id'];
							$info1['trancation_status']	= 3;
							$info1['branch_id']			= $rel['branch_id'];
							$info1['cdate']				= date("Y-m-d H:i:s");
							$table='tbl_invoicetrn';$tableid='trancation_id';
							$inserid=add_record($table, $info1, $dbcon);
							$insert_tax=add_tax_record($dbcon,$inserid,"tbl_invoicetrn","trancation_id",$rel['formulaid'],$info1['taxable_value']);
							
							
							//$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
							//$info1=array_merge($info1,$info);
							
							//$info1['bill_value']		= $POST['bill_value'];
							//$info1['bill_black_value']	= $POST['bill_black_value'];
							
							//$info1['model_id']			= $POST['model_id'];
							//$info1['ser_status']		= $POST['ser_status'];
						}else{
							$info_so['invoice_status'] = 1;
							$updateid=update_record('tbl_sales_ordertrn', $info_so,"sales_ordertrn_id=".$rel['sales_ordertrn_id'] , $dbcon);
						}
					}
				}
		}
		else if(strtolower($POST['mode'])== "load_sales_pro")
		{
			$resp['pro_html']=getproduct($dbcon,0,'0,2,3');
			echo json_encode($resp);
		}
		
		else if(strtolower($POST['mode'])== "loadsales_producttypedata")
		{
			$resp['pro_html'] 			= get_sales_order_typewise_data($dbcon,$POST['type_id'],$POST['sales_order_id']);
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "loadsales_productdata")
		{
			$q = $dbcon -> query("SELECT * from tbl_sales_ordertrn where sales_order_id=".$POST['sales_order_id']." and sales_ordertrn_status=0 and product_id=".$POST['product_id']." ");
			$resp = $q->fetch_assoc();
			$resp['rsock']=reserve_stock($dbcon,$resp['product_id'],$resp['unit_id'],$reserve_id,$request_id,$complaint_id,$POST['so_trn_id']);
			
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_qty")
		{
		    echo getsale_productqty($dbcon,$POST['product_id']);
		}
		else if(strtolower($POST['mode'])== "load_rate_hist")
		{
			$resp='';
			$query="select inv.*,cust.company_name,pro.product_name,trn.product_rate from tbl_invoice as inv
					inner join tbl_invoicetrn as trn on inv.invoice_id=trn.invoice_id 
					inner join tbl_customer as cust on cust.cust_id=inv.cust_id
					inner join tbl_product as pro on pro.product_id=trn.product_id
					where inv.invoice_status=0 and trn.trancation_status=0 and inv.cust_id=".$POST["cust_id"]." and trn.product_id=".$POST["product_id"]." order by trn.trancation_id DESC LIMIT 10";
				
			$rs_prel=$dbcon->query($query);
			$rs_prel_num_rows=mysqli_num_rows($rs_prel);
				
			if($rs_prel_num_rows>0){
				while($prel=mysqli_fetch_assoc($rs_prel)){
			
					$resp.='<tr>
								<td class="text-center">'.$prel['invoice_no'].'</td>
								<td class="text-center">'.date('d-m-y',strtotime($prel['invoice_date'])).'</td>
								<td class="text-center">'.$prel['product_rate'].'</td>
							</tr>';
					$row['cust_name']=$prel['company_name'];
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
		else if(strtolower($POST['mode'])=="load_stock_qty")
		{
			$product_id=$POST['product_id'];
			$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
			$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
			
			$product_type_arr = array("0", "1", "2", "3", "4", "5");
			if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
				if(!empty($POST['unit_id'])){
					$unit_id=$POST['unit_id'];
				}else{
					$unit_id=$get_pro_type_rel['product_base_unit'];
				}
				$current_stock = get_current_stock_new($dbcon,$product_id,$unit_id);
				
				$where=" and trancation_status!='2' and invoice_id='0'";
				$unclear_qty = get_unclear_stock($dbcon,$product_id,$unit_id,'tbl_invoicetrn','product_qty','product_id',$where);
				echo $current_stock-$unclear_qty;
			}
			else{
				echo 9999;
			}
			
		}
		else if(strtolower($POST['mode'])=="copy_quot_trn_data"){
			$deleteid=delete_record('tbl_invoicetrn',"trancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
			
			$qt_qry="select * from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$POST['quotation_id'];
			$qt_qry_rs=$dbcon->query($qt_qry);
			while($qt_trn=mysqli_fetch_assoc($qt_qry_rs)){
				$info1=array();
				
				$info1['ref_quot_trn_id']	= $qt_trn['quot_trn_id'];
				$info1['product_id']		= $qt_trn['product_id'];
				$info1['description']		= $qt_trn['product_desc'];
				$info1['product_qty']		= $qt_trn['product_qty'];
				$info1['product_rate']		= $qt_trn['product_rate'];
				$info1['unit_id']			= $qt_trn['unit_id'];
				$info1['product_discount']	= $qt_trn['product_discount'];
				$info1['discount_per']		= $qt_trn['discount_per'];
				$info1['formulaid']			= $qt_trn['formulaid'];
				$info1['product_amount']	= $qt_trn['product_amount'];
				$info1['taxable_value']		= $qt_trn['taxable_value'];
				$info=get_product_tax($dbcon,$qt_trn['product_amount'],$qt_trn['formulaid']);
				$info1=array_merge($info1,$info);
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				$info1['trancation_status']	= 3;
				$inserid=add_record('tbl_invoicetrn', $info1, $dbcon);
			}
		}
		else if(strtolower($POST['mode'])=="copy_comp_spare_trn_data"){
			$deleteid=delete_record('tbl_invoicetrn',"trancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
			//Amish Soni Start - 17-12-2020
			$qt_qry="select sp.*, ic.received_qty from tbl_complain_spare_part sp 
			left join tbl_internal_chalan ic ON ic.sp_id = sp.s_id
			where sp.s_inv_status=0 and sp.s_paid_status='paid' and sp.s_comp_id=".$POST['complaint_id'];
			
			$qt_qry_rs=$dbcon->query($qt_qry);
			while($qt_trn=mysqli_fetch_assoc($qt_qry_rs)){
				$info1=array();
				$total_amount = $qt_trn['received_qty'] * $qt_trn['s_rate'];
				$info1['ref_s_id']			= $qt_trn['s_id'];
				$info1['product_id']		= $qt_trn['s_product'];
				//$info1['description']		= $qt_trn['product_desc'];
				// $info1['product_qty']		= $qt_trn['s_qty'];
				$info1['product_qty']		= $qt_trn['received_qty'];
				$info1['product_rate']		= $qt_trn['s_rate'];
				//$info1['unit_id']			= $qt_trn['unit_id'];
				//$info1['product_discount']= $qt_trn['product_discount'];
				//$info1['discount_per']	= $qt_trn['discount_per'];
				$info1['formulaid']			= $qt_trn['formulaid'];
				// $info1['product_amount']	= $qt_trn['s_amount'];
				// $info1['taxable_value']		= $qt_trn['s_amount'];
				$info1['product_amount']	= $total_amount;
				$info1['taxable_value']		=  $total_amount;
				//Amish Soni End - 17-12-2020
				$info=get_product_tax($dbcon,$info1['product_amount'],$info1['formulaid']);
				$info1=array_merge($info1,$info);
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				$info1['trancation_status']	= 3;
				$inserid=add_record('tbl_invoicetrn', $info1, $dbcon);
			}
			
			//Entry Service Charge
			$comp_trn_qry="select trn.* from tbl_complaint_trn as trn
			where trn.comp_pro_sts=2 and trn.complaint_trn_status=0 and trn.complaint_id=".$POST['complaint_id'];
			$comp_trn_rel=mysqli_fetch_assoc($dbcon->query($comp_trn_qry));
			if($comp_trn_rel['product_id']){
				$info1=array();
				
				$info1['product_id']		= 2862;//Fixed Product ID
				$info1['product_qty']		= 1;
				$info1['product_rate']		= $comp_trn_rel['comp_amount'];
				$info1['product_amount']	= $comp_trn_rel['comp_amount'];
				$info1['taxable_value']		= $comp_trn_rel['comp_amount'];
				$info=get_product_tax($dbcon,$info1['product_amount'],$info1['formulaid']);
				$info1=array_merge($info1,$info);
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				$info1['trancation_status']	= 3;
				$inserid=add_record('tbl_invoicetrn', $info1, $dbcon);
			}
		}
		else if(strtolower($POST['mode'])=="add_pro_srl_no"){
			$info1['pro_srl_no']	= $POST['pro_srl_no'];
			$info1['trancation_id']	= $POST['trancation_id'];
			$info1['user_id']		= $_SESSION['user_id'];
			$table='tbl_inv_srl_trn';$tableid='inv_srl_trn_id';
			if(!empty($POST['invoice_id'])) {
				$info1['invoice_id']= $POST['invoice_id'];
			}
			$inserid=add_record($table, $info1, $dbcon);
		}
	else if(strtolower($POST['mode'])=="show_pro_srl_no") {
		$str='';
		if($POST['trancation_id']){
			$query="select trn.* from tbl_inv_srl_trn as trn 
			where trn.inv_srl_trn_status=0 and trn.trancation_id=".$POST['trancation_id'];
		}
		
		$result=$dbcon->query($query);
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$str.='<tr> 
					<td style="vertical-align:top;">
						<strong>'.$i.'</strong>
					</td>
					<td style="vertical-align:top;">
						<strong>'.$rel['pro_srl_no'].'</strong>
					</td>
					<td style="vertical-align:middle"> 
						<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inv_srl_data('.$rel['inv_srl_trn_id'].')">X</button>
					</td>
				</tr>';
				$i++;
			}
		}
		else{
			$str.= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		
		echo $str;
	}
	else if(strtolower($POST['mode'])== "delete_inv_srl_data") {
		$row=array();
		$info['inv_srl_trn_status']=2;	
		$updateid=update_record('tbl_inv_srl_trn', $info, "inv_srl_trn_id=".$POST['inv_srl_trn_id'] , $dbcon);
		
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "load_ven_grn") {
		$resp['pro_html'] = get_so_for_finance($dbcon,$POST['vender_id'],$POST['id'],"Add");
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "get_so_by_vendor"){
		$vender_id=$POST['vender_id'];
		$so_id = $POST['so_id'];
		$modee = $POST['modee'];
		echo get_so_for_finance($dbcon,$vender_id,$so_id,$modee);
	}
	else if(strtolower($POST['mode'])== "count_pro_srl_no") {
		$cnt_srl_qry="select count(inv_srl_trn_id) srl_qty,(select product_qty from tbl_invoicetrn where trancation_id=".$POST['trancation_id'].") as act_qty from tbl_inv_srl_trn where inv_srl_trn_status=0 and trancation_id=".$POST['trancation_id'];
		$cnt_srl_rel=mysqli_fetch_assoc($dbcon->query($cnt_srl_qry));
		if(floatval($cnt_srl_rel['act_qty'])>floatval($cnt_srl_rel['srl_qty'])){
			echo "1";
		}
		else{
			echo "0";
		}
	}
        // Dimple Panchal : Start
        else if(strtolower($POST['mode'])== "get_tax_on_total")
                        {
            $arr = get_tax_on_total($dbcon,$POST['total'],$POST['formulaid']);
            echo json_encode($arr);
        }
        else if(strtolower($POST['mode'])== "show_tcs_row")
        {
            $is_tcs_applicable = $dbcon->query("SELECT tcs_applicable FROM tbl_finance_setting WHERE company_id=".$_SESSION['company_id'])
                        ->fetch_object()->tcs_applicable;
            
            if($is_tcs_applicable) {
                $invoice_total = $dbcon->query("SELECT sum(g_total) as invoice_total FROM `tbl_invoice` where cust_id = ".$POST['cust_id']." and company_id = ".$_SESSION['company_id']." and invoice_status = 0")
                        ->fetch_object()->invoice_total;
                
                if($invoice_total >= 5000000){
                    echo "1";
                } else {
                    echo "0";
                }
            } else {
                echo "0";
            }
        }
	// Dimple Panchal : end

    /*Dhruv start code*/
    else if(strtolower($POST['mode'])== "get_gst_statecode")
    {
        $arr = get_gst_statecode($dbcon,$POST['cust_id']);
        echo $arr;
    }
    else if(strtolower($POST['mode'])== "get_grossbalance")
    {
        $arr = get_grossbalance($dbcon,$POST['cust_id']);
        echo $arr;
    }

    else if(strtolower($POST['mode'])== "get_tax_details_table")
	{
		$invoice_id=$POST['invoice_id'];
		$resp='';
		$query="SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2 group by cgst_tax_per,sgst_tax_per,igst_tax_per";
		
		$rs_prel=$dbcon->query($query);
		$rs_prel_fetch=brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2"));
		$rs_prel_num_rows=mysqli_num_rows($rs_prel);
		//print_r($rs_prel_fetch);exit;
		$resp='';
		$resp .= '<table class="table table-bordered">
													
						<tr>
							<th class="text-center">#</th>
							<th  class="text-center">Total Tax</th>
							<th  class="text-center">Tax Amount</th>';
		if(($rs_prel_fetch['cgst_rate']!=0) || ($rs_prel_fetch['sgst_rate']!=0)){
			$resp .='<th  class="text-center">CGST</th>
					<th  class="text-center">SGST</th>';
		}if(($rs_prel_fetch['igst_rate']!=0)){
			$resp .= '<th  class="text-center">IGST</th>';
		}
							
							
		$resp .='</tr>';
			
		if($rs_prel_num_rows > 0){
			$taxRate = brp_mysqli_fetch_all($rs_prel);
			//print_r($taxRate);exit;
			$cnt=1;
			foreach($taxRate as $taxdetail) {
				$gst_tax_per = ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) ? ($taxdetail['cgst_tax_per']+$taxdetail['sgst_tax_per']) : $taxdetail['igst_tax_per'];
				$gst_tax_rate = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate']+$taxdetail['sgst_rate']) : $taxdetail['igst_rate'];

				if($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0){
					$resp.='<tr>
							<th class="text-center">'.$cnt.'</th>
							<th class="text-center">'.$gst_tax_per.'%'.'</th>
							<th class="text-center">'.$gst_tax_rate.'</th>
							<th class="text-center">'.($taxdetail['cgst_tax_per']).'%'.'</th>
							<th class="text-center">'.($taxdetail['sgst_tax_per']).'%'.'</th>
						</tr>';
				}

				if($taxdetail['igst_tax_per'] != 0){
					$resp.='<tr>
							<th class="text-center">'.$cnt.'</th>
							<th class="text-center">'.$gst_tax_per.'%'.'</th>
							<th class="text-center">'.$gst_tax_rate.'</th>
							<th class="text-center">'.($taxdetail['igst_tax_per']).'%'.'</th>
						</tr>';
				}	

				$cnt++;		
						
			}

		}
		
		$resp.='</table>';
		
		$row['resp']=$resp;
		
		echo json_encode($row);
	}
	
	else if(strtolower($POST['mode'])== "get_invoice_total_tax")
	{
		$invoice_id=$POST['invoice_id'];
		
		$resp='';
		$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2 and user_id=".$_SESSION['user_id']." ";
		
		
		$rs_prel= brp_mysqli_fetch_assoc($dbcon->query($query));

		$row['isTcs']="0";
		$getCompanyConfig = getCompanyConfiguration($dbcon);
		$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);		
		$get_bill_sundry = get_bill_sundry_ledger($dbcon,1); 

		foreach ($get_bill_sundry as $billsundry) {
		
			if((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate']!= 0) && $billsundry['l_name'] == 'SGST')){

				$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round($gstValue,2).'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
				
				
			}
			if(($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST'){
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round($rs_prel['igst_rate'],2).'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
			}
			
			if(($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($POST['gross'] > $getCompanyConfig['gross_balance_limit'])){
				$row['isTcs']="1";
				$total_tcs_calculate = $rs_prel['product_amount']+$gstValue+$rs_prel['igst_rate'];
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round((($total_tcs_calculate*$billsundry['tax_value'])/100),2).'" placeholder="'.$billsundry['l_name'].'" readonly >
						<input type="hidden" name="tcs_amount" id="tcs_amount" value="'.round((($total_tcs_calculate*$billsundry['tax_value'])/100),2).'" >
						<input type="hidden" name="tcs_per" id="tcs_per" value="'.$billsundry['tax_value'].'" >
					</div>
				</div>';
			}
		
			
		}
		
		//additional tax transaction start - dhaval
		
	/*	$qry_add=$dbcon->query("SELECT trn.*,p.product_hsn,h.sale_gst,tc.tax_cat_id,t.tax_id,t.tax_per,l.l_name from tbl_invoicetrn as trn 
		left join product_mst as p on p.product_id=trn.product_id
		left join mst_hsn_code as h on h.hsn_id=p.product_hsn
		left join tbl_tax_category as tc on tc.tax_cat_id=h.sale_gst
		left join tbl_tax_category_details as t on t.tax_cat=tc.tax_cat_id
		left join tbl_ledger as l on l.l_id=t.tax_id
		where t.tax_additional='1' and trn.invoice_id='$invoice_id' and trn.trancation_status!=2 and t.isdelete='0'");
		while($row1=brp_mysqli_fetch_array($qry_add))
		{
			
			$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;
			
			
			$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$row1['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$row1['l_name'].'" name="bill_sundry_tax['.$row1['l_id'].']" type="number" class="form-control gst" title="'.$row1['l_name'].'"  value="'.$tax_rate.'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
		}
		*/
		
		$qry_add = $dbcon->query("select sum((tc.tax_per*trn.product_amount)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_invoicetrn as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
		left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id
		where tc.tax_additional='1' and trn.invoice_id='$invoice_id' and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id 
		");
		while($row1=brp_mysqli_fetch_array($qry_add))
		{
			
			//$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;
			
			
			$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$row1['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$row1['l_name'].'" name="bill_sundry_tax['.$row1['l_id'].']" type="number" class="form-control gst" title="'.$row1['l_name'].'"  value="'.round($row1['add_sum'],2).'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
		}
		
		$row['resp']=$resp;
		
		echo json_encode($row);
	}

	else if(strtolower($POST['mode'])== "get_invoice_total_tax_old")
	{
		$invoice_id=$POST['invoice_id'];
		
		$resp='';
		$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount FROM `tbl_invoicetrn` where invoice_id='$invoice_id' and trancation_status!=2";
			
		$rs_prel= brp_mysqli_fetch_assoc($dbcon->query($query));

		$row['isTcs']="0";
		$getCompanyConfig = getCompanyConfiguration($dbcon);
		$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);		
		$get_bill_sundry = get_bill_sundry_ledger($dbcon,1); 

		foreach ($get_bill_sundry as $billsundry) {
		
			if((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate']!= 0) && $billsundry['l_name'] == 'SGST')){

				$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.$gstValue.'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
				
				
			}
			if(($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST'){
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.$rs_prel['igst_rate'].'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
			}
			
			if(($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($POST['gross'] > $getCompanyConfig['gross_balance_limit'])){
				$row['isTcs']="1";
				$total_tcs_calculate = $rs_prel['product_amount']+$gstValue+$rs_prel['igst_rate'];
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round((($total_tcs_calculate*$billsundry['tax_value'])/100),2).'" placeholder="'.$billsundry['l_name'].'" readonly >
						<input type="hidden" name="tcs_per" id="tcs_per" value="'.$billsundry['tax_value'].'" >
					</div>
				</div>';
			}
		
			
		}
		
		$row['resp']=$resp;
		
		echo json_encode($row);
	}

	else if(strtolower($POST['mode'])== "eway_api")
    {
        //$getToken = get_eway_token();
        //print_r($getToken);exit;
        $postData = array();
        $postData['supplyType'] = "0";
        $postData['subSupplyType'] = "1";
        $postData['subSupplyDesc'] = '';
        $postData['docType'] = 'INV';
        $postData['docNo'] = '111-19909';
        $postData['docDate'] = "09/07/2021";
        $postData['fromGstin'] = "34AACCC1596Q002";
        $postData['fromTrdName'] = "welton";
        $postData['fromAddr1'] = "2ND CROSS NO 59  19  A";
        $postData['fromAddr2'] = "GROUND FLOOR OSBORNE ROAD";
        $postData['fromPlace'] = "FRAZER TOWN";
        $postData['fromPincode'] = "605005";
        $postData['actFromStateCode'] = "34";
        $postData['fromStateCode'] = '34';
        $postData['toGstin'] = "02EHFPS5910D2Z0";
        $postData['toTrdName'] = "sthuthya";

        $postData['toAddr1'] = "Shree Nilaya";
        $postData['toAddr2'] = "Dasarahosahalli";
        $postData['toPlace'] = "Beml Nagar";
        $postData['toPincode'] = '176036';
        $postData['actToStateCode'] = "02";
        $postData['toStateCode'] = "02";
        $postData['transactionType'] = "4";
        $postData['dispatchFromGSTIN'] = "29AAAAA1303P1ZV";
        $postData['dispatchFromTradeName'] = "ABC Traders";
        $postData['shipToGSTIN'] = "29ALSPR1722R1Z3";
        $postData['shipToTradeName'] = "XYZ Traders";
        $postData['otherValue'] = -"100";

        $postData['totalValue'] = "56099";
        $postData['cgstValue'] = "0";
        $postData['sgstValue'] = "0";
        $postData['igstValue'] = "300.67";
        $postData['cessValue'] = "400.56";
        $postData['cessNonAdvolValue'] = "400";
        $postData['totInvValue'] = "68358";
        $postData['transporterId'] = "";
        $postData['transporterName'] = "";
        $postData['transDocNo'] = "";
        $postData['transMode'] = "1";
        $postData['transDistance'] = "2786";

        $postData['transDocDate'] = "";
        $postData['vehicleNo'] = "PVC1234";
        $postData['vehicleType'] = "R";
        

        $postData['itemList'] = get_item_details($dbcon);

        $callEway = submitEwayApi(json_encode($postData));
        print_r($callEway);exit;
        //echo $arr;
    }
	else if(strtolower($POST['mode'])== "remove_sundry"){
		
		$ledger_id = $POST['ledger_id'];
		$invoice_id = $POST['edit_id'];
		$cust_ledger_id = $POST['cust_ledger_id'];

		$info['isdelete']=1;

		$updateid=update_record('tbl_bill_sundry_transaction', $info,"sundry_id=".$POST['ledger_id'] , $dbcon);

		$info_general['genral_book_status'] = 2;

		$q = $dbcon -> query("SELECT amount from tbl_general_book where table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction' ");
		$resp = $q->fetch_assoc();

		$update_gen_cusid=update_record('tbl_general_book', $info_general,"table_id=".$invoice_id." and ledger_id=".$cust_ledger_id." and amount=".$resp['amount']." and ref_by='tbl_addon_bill_sundry' and  table_name='tbl_invoice'" , $dbcon);

		$updateid=update_record('tbl_general_book', $info_general,"table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction'" , $dbcon);

	}
    else if(strtolower($POST['mode'])== "get_bill_sundry_details")
	{
		$invoice_id=$POST['invoice_id'];
		//echo '<pre>'; print_r($POST);exit;
		$q = $dbcon -> query("SELECT * from tbl_ledger_bill_sundry where sundry_ledger_id=".$POST['sundry_ledger_id']." and company_id = ".$_SESSION['company_id']." ");
		$resp = $q->fetch_assoc();

		$basic_total = $POST['basic_amount'];
		$netamount = $POST['netamount'];
		$taxableamount = $POST['taxableamount'];
		
		$default_amount = $POST['default_amount'];
		
		
		//print_r($POST['totalsundryexist']);exit;
		$totalsundryexist = $POST['totalsundryexist'];

		if($resp['sundry_type'] == 1){
			if($resp['sundry_amount_of'] == 1){
				if($resp['sundry_calculate_on'] == 1){
					$finalNetAmount = $netamount + $default_amount;
					$pervalue =  $default_amount;
				}else if($resp['sundry_calculate_on'] == 2){
					$finalNetAmount = $basic_total + $default_amount;
					$pervalue =  $default_amount;
				}else if($resp['sundry_calculate_on'] == 3){
					$finalNetAmount = $basic_total + $default_amount;
					$pervalue =  $default_amount;
				}
				//$finalNetAmount = $netamount + $default_amount;

			}else if($resp['sundry_amount_of'] == 2){
				if($resp['sundry_calculate_on'] == 1){
					$finalNetAmount = (($netamount * $default_amount)/100) + $netamount;
					$pervalue = ($netamount * $default_amount)/100;
				}else if($resp['sundry_calculate_on'] == 2){
					$finalNetAmount = (($basic_total * $default_amount)/100) + $basic_total;
					$pervalue = ($basic_total * $default_amount)/100;
				}else if($resp['sundry_calculate_on'] == 3){
					$finalNetAmount = (($basic_total * $default_amount)/100) + $basic_total;
					$pervalue = ($basic_total * $default_amount)/100;
				}
				//$finalNetAmount = (($netamount * $default_amount)/100) + $netamount;
			}
			//$per_amount_show='';
		}
		else if($resp['sundry_type'] == 2){
			if($resp['sundry_amount_of'] == 1){
				if($resp['sundry_calculate_on'] == 1){
					$finalNetAmount = $netamount - $default_amount;
					$pervalue =  -$default_amount;
				}else if($resp['sundry_calculate_on'] == 2){
					$finalNetAmount = $basic_total - $default_amount;
					$pervalue =  -$default_amount;
				}else if($resp['sundry_calculate_on'] == 3){
					//$finalNetAmount = (($basic_total + $taxableamount) - $default_amount) + $totalsundryexist;
					$finalNetAmount = $basic_total - $default_amount;
					$pervalue =  -$default_amount;
				}
				//$finalNetAmount = $netamount - $default_amount;
			}else if($resp['sundry_amount_of'] == 2){
				if($resp['sundry_calculate_on'] == 1){
					$finalNetAmount = $netamount - (($netamount * $default_amount)/100);
					$pervalue = -($netamount * $default_amount)/100;
				}else if($resp['sundry_calculate_on'] == 2){
					//$finalNetAmount = (($basic_total + $taxableamount) - (($basic_total * $default_amount)/100)) + $totalsundryexist;
					$finalNetAmount = $basic_total - (($basic_total * $default_amount)/100);
					$pervalue = -($basic_total * $default_amount)/100;
				}else if($resp['sundry_calculate_on'] == 3){
					//$finalNetAmount = (($basic_total + $taxableamount) + ((($basic_total + $taxableamount) * $default_amount)/100)) + $totalsundryexist;
					$finalNetAmount = $basic_total - (($basic_total * $default_amount)/100);
					$pervalue = -($basic_total * $default_amount)/100;
				}
				//$finalNetAmount = $netamount - (($netamount * $default_amount)/100);
			}
			
			//$per_amount_show = '('.$default_amount.'% )';
			
		}
		
		//if invoice is edit time insert data in database start - dhaval
		if($invoice_id>0)
		{
			$info_sundry_addon['sundry_ledger_id']=$POST['sundry_ledger_id'];
			$info_sundry_addon['sundry_amount']=$pervalue;
			$info_sundry_addon['sundry_voucher_id']=$invoice_id;
			$info_sundry_addon['sundry_voucher_type']=SALES_VOUCHER;
			$info_sundry_addon['sundry_voucher_table']='tbl_invoice';
			$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
			$info_sundry_addon['user_id']	= $_SESSION['user_id'];
			$info_sundry_addon['company_id']	= $_SESSION['company_id'];
			
			//print_r(array_merge($info_sundry_addon,$curncy_trn));
			
			if(isset($POST['currency_enable'])){
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }
			
			$sundry_addon_insert=add_record('tbl_bill_sundry_transaction',array_merge($info_sundry_addon,$curncy_trn), $dbcon);
			
			//general book entry 
			$invoice_date = date("Y-m-d",strtotime($POST['invoice_date']));

			if($pervalue < 0){
				$ledger_entry_type = 2;
				$cust_entry_type = 1; 
			}else{
				$ledger_entry_type = 1;
				$cust_entry_type = 2;
			}
			
			$info_general_addon['ledger_id']=$POST['sundry_ledger_id'];
			$info_general_addon['amount']=abs($pervalue);
			$info_general_addon['table_id']=$sundry_addon_insert;
			$info_general_addon['entry_type']=$ledger_entry_type;
			$info_general_addon['table_name']='tbl_bill_sundry_transaction';
			$info_general_addon['ref_date']=$invoice_date;
			$info_general_addon['cdate']	= date("Y-m-d H:i:s");
			$info_general_addon['user_id']	= $_SESSION['user_id'];
			$info_general_addon['company_id']	= $_SESSION['company_id'];
			
			add_record('tbl_general_book',array_merge($info_general_addon,$curncy_trn), $dbcon);

			$info_gen2['table_name']	= 'tbl_invoice';
			$info_gen2['table_id']		= $invoice_id;
			$info_gen2['entry_type']	= $cust_entry_type;
			$info_gen2['ref_date']		= date('Y-m-d',strtotime($invoice_date));
			$info_gen2['ledger_id']		= $POST['cust_id'];
			$info_gen2['amount']		= abs($pervalue);
			$info_gen2['user_id']		= $_SESSION['user_id'];
			$info_gen2['cdate']			= date("Y-m-d H:i:s");
			$info_gen2['company_id']	= $_SESSION['company_id'];
			$info_gen2['ref_by'] = 'tbl_addon_bill_sundry';
						
			//$inserid_gen2=add_record("tbl_general_book", array_merge($info_gen2,$curncy_trn) , $dbcon);
			
			//add_general_book_entry($dbcon,"tbl_invoice",$invoice_id,$cust_entry_type,$POST['cust_id'],abs($pervalue),'',$invoice_date,'',$curncy_trn);
		}
		//if invoice is edit time insert data in database end - dhaval
		
		if($resp['sundry_amount_of'] == 1){
			
			$per_amount_show="";
			
		}
		else{
			
			$per_amount_show= '<strong> ('.round($default_amount,2).'%)</strong>';
		}

		// $finalNetAmount = round($finalNetAmount,2);
		 $pervalue = round($pervalue,2);
		// $per_amount_show = round($per_amount_show,2);
		
		echo json_encode($finalNetAmount.','.$pervalue.','.$per_amount_show.','.$invoice_id);
	}
	
	else if(strtolower($POST['mode'])== "get_all_bill_sundry")
	{
		$invoice_id=$POST['invoice_id'];
		
		$q=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' and le.default_sundry='0' ");
		
		$resp=brp_mysqli_fetch_all($q);
		
		$str="";$cnt=1;
		foreach($resp as $r)
		{
			
			if($r['sundry_type'] == 1){
				
				$per_amount_show='';
			}
			else if($r['sundry_type'] == 2){
				
				$per_amount_show = '('.$r['sundry_default_value'].'%'.')';
				
			}
			
			$str.='<div class="form-group">
					<label class="col-md-5 control-label">'.$r['l_name'].'</label>
					<div class="col-md-4">
						<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
						<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
					</div>
					<div class="col-md-3">
						<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
							type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
					</div>
				</div>';
			
			$cnt++;
			//$str.=$r['sundry_amount'];
		}
		
		echo $str;
		//echo json_encode($resp);
	}
	

	else if(strtolower($POST['mode'])== "get_sales_order_details")
	{
		$resp='';

		if($POST['isallocate'] == 1){
			$query="select * from tbl_sales_order where sales_order_status=0 and approve_status!=0 and cust_id=".$POST['cust_id']." and invoice_status=0 and company_id=".$_SESSION['company_id']." and branch_id=".$POST['branch_id']." ";
			
			$rs_prel= brp_mysqli_fetch_all($dbcon->query($query));

			foreach ($rs_prel as $result) {	

				$resp.='<div class="row">
						<div class="col-md-6"><label>'.$result['sales_order_no'].' </label></div>
						<div class="col-md-4" ><input type="checkbox" class="sales_order" value="'.$result['sales_order_id'].'"  ></div>
					</div><br>';
			}
		}else if($POST['isallocate'] == 2){
			$query="SELECT so.sales_order_no,so.sales_order_id FROM `tbl_sales_order_production_trn` as sop left join tbl_sales_ordertrn as sot on sot.sales_ordertrn_id = sop.sales_ordertrn_id left join tbl_sales_order as so on so.sales_order_id=sot.sales_order_id where so.cust_id=".$POST['cust_id']." and so.invoice_status=0 and so.company_id=".$_SESSION['company_id']." and so.branch_id=".$POST['branch_id']." and sop.remaning_invoice_qty != 0 group by so.sales_order_no
";
			
			$rs_prel= brp_mysqli_fetch_all($dbcon->query($query));

			foreach ($rs_prel as $result) {	

				$resp.='<div class="row">
						<div class="col-md-6"><label>'.$result['sales_order_no'].' </label></div>
						<div class="col-md-4" ><input type="checkbox" class="sales_order" value="'.$result['sales_order_id'].'"  ></div>
					</div><br>';
			}
		}else{
			$resp.='';
		}
		

		
		echo json_encode($resp);
	}

	else if(strtolower($POST['mode'])== "get_hsn_code")
    {
    	$qry="SELECT hc.hsn_code FROM `product_mst` as pm
			join mst_hsn_code as hc on pm.product_hsn=hc.hsn_id and hc.hsn_status=0
			where pm.product_id=".$POST['product_id']." and pm.company_id=".$_SESSION['company_id']."";
		$row=brp_mysqli_fetch_assoc($dbcon->query($qry));
        print_r($row['hsn_code']);
    }

    else if(strtolower($POST['mode'])== "add_sales_order")
    {

    	//echo '<pre>';print_r($POST);exit;
		$company_state = get_company_data($dbcon,$_SESSION['company_id']);		
		$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);

		foreach ($POST['sales_order'] as $sale_id) {

			$qry = "SELECT * FROM `tbl_sales_ordertrn` where sales_order_id=".$sale_id." and sales_ordertrn_status=0";
			$get_sales_order = brp_mysqli_fetch_all($dbcon->query($qry));

			//$row=array();
			$info_sale['billing_type']=$POST['transaction_type'];	
			//$updateid=update_record('tbl_sales_order', $info_sale, "sales_order_id=".$sale_id , $dbcon);
			foreach ($get_sales_order as $get_sales_order_details) {
				
				if($POST['transaction_type'] == 2){
					$qry1 = "SELECT remaning_invoice_qty,sales_order_production_trn_id FROM `tbl_sales_order_production_trn` where sales_ordertrn_id=".$get_sales_order_details['sales_ordertrn_id']." and invoice_status=0 and remaning_invoice_qty != 0";
					$get_sales_order_production_trn = brp_mysqli_fetch_assoc($dbcon->query($qry1));

					if(!empty($get_sales_order_production_trn)){


						$hsn_details = brp_mysqli_fetch_assoc($dbcon->query("SELECT hc.sale_gst,hc.hsn_code,t.tax_gst FROM `product_mst` as pm join mst_hsn_code as hc on hc.hsn_id=pm.product_hsn and hsn_status=0 left join tbl_tax_category as t on t.tax_cat_id=hc.sale_gst where pm.product_id=".$get_sales_order_details['product_id']." "));

						$cgst_tax_rate=0;
						$sgst_tax_rate=0;
						$igst_tax_rate=0;

						$product_amt = ($get_sales_order_details['product_rate']*$get_sales_order_production_trn['remaning_invoice_qty'])-$get_sales_order_details['product_discount'];

						if(($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
							$gst = $hsn_details['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$product_amt)/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$product_amt)/100;
						}else{
							$igst_tax_per = $hsn_details['tax_gst'];
							$igst_tax_rate = ($hsn_details['tax_gst']*$product_amt)/100;
						}

						$info1['product_id']		= $get_sales_order_details['product_id'];
						$info1['description']		= $get_sales_order_details['description'];
						$info1['product_spec']		=$get_sales_order_details['product_spec'];
						$info1['product_hsn_code']	= $hsn_details['hsn_code'];
						$info1['product_qty']		= $get_sales_order_production_trn['remaning_invoice_qty'];
						$info1['product_rate']		= $get_sales_order_details['product_rate'];
						$info1['unit_id']			= $get_sales_order_details['unit_id'];
						$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
						$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
						$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
						$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
						$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;
						$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
						$info1['product_discount']	= $get_sales_order_details['product_discount'];
						$info1['discount_per']		= $get_sales_order_details['discount_per'];
						$info1['company_id']		= $_SESSION['company_id'];
						$info1['product_amount']	= $product_amt;
						$info1['taxable_value']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
						$info1['total'] 			= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $product_amt;
						$info1['transaction_type'] 	= 2; 
						$info1['so_allocation_id'] 	= $get_sales_order_production_trn['sales_order_production_trn_id'];
						$info1['user_id']			= $_SESSION['user_id'];
						$info1['trancation_status']	= 1;

						$table='tbl_invoicetrn';

						//echo '<pre>';print_r($info1);exit;

						$inserid=add_record($table, $info1, $dbcon, $branch_id);

						$info_allo['remaning_invoice_qty']=0;	
						$update_alloid=update_record('tbl_sales_order_production_trn', $info_allo, "sales_order_production_trn_id=".$get_sales_order_production_trn['sales_order_production_trn_id'] , $dbcon);

						/* insert to tax transaction table by Dhruv */
						if( ($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'CGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_invoicetrn",$get_sales_order_details['product_id'],3,'','');
						}
						if( ($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'SGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_invoicetrn",$get_sales_order_details['product_id'],3,'','');
						}
						if( ($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'IGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_invoicetrn",$get_sales_order_details['product_id'],3,'','');
						}
					}

					
				}else if($POST['transaction_type'] == 1){

					if($get_sales_order_details['remaning_invoice_qty'] != 0){

			
						$hsn_details = brp_mysqli_fetch_assoc($dbcon->query("SELECT hc.sale_gst,hc.hsn_code,t.tax_gst FROM `product_mst` as pm join mst_hsn_code as hc on hc.hsn_id=pm.product_hsn and hsn_status=0 left join tbl_tax_category as t on t.tax_cat_id=hc.sale_gst where pm.product_id=".$get_sales_order_details['product_id']." "));

						$cgst_tax_rate=0;
						$sgst_tax_rate=0;
						$igst_tax_rate=0;

						if(($company_state['stateid'] == $POST['cust_stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
							$gst = $hsn_details['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$get_sales_order_details['product_amount'])/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$get_sales_order_details['product_amount'])/100;
						}else{
							$igst_tax_per = $hsn_details['tax_gst'];
							$igst_tax_rate = ($hsn_details['tax_gst']*$get_sales_order_details['product_amount'])/100;
						}

						$info1['product_id']		= $get_sales_order_details['product_id'];
						$info1['description']		= $get_sales_order_details['description'];
						//$info1['ser_status']		= $POST['ser_status'];
						$info1['product_hsn_code']	= $hsn_details['hsn_code'];
						$info1['product_qty']		= $get_sales_order_details['remaning_invoice_qty'];
						$info1['product_rate']		= $get_sales_order_details['product_rate'];
						$info1['product_spec']		=$get_sales_order_details['product_spec'];
						//$info1['product_disc']		= $POST['product_disc'];
						$info1['unit_id']			= $get_sales_order_details['unit_id'];
						$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
						$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
						$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
						$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
						$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;
						$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;

						$info1['product_discount']	= $get_sales_order_details['product_discount'];
						$info1['discount_per']		= $get_sales_order_details['discount_per'];
						$info1['company_id']		= $_SESSION['company_id'];
						$info1['product_amount']	= $get_sales_order_details['product_amount'];
						$info1['taxable_value']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
						$info1['total'] 			= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $get_sales_order_details['product_amount'];
						$info1['transaction_type'] = 1;
						$info1['so_allocation_id'] = $get_sales_order_details['sales_ordertrn_id'];
						$info1['user_id']	= $_SESSION['user_id'];

						$info1['trancation_status']	= 1;

						$table='tbl_invoicetrn';

						$inserid=add_record($table, $info1, $dbcon, $branch_id);

						$info_so['remaning_invoice_qty']=0;	
						$info_so['invoice_status']=1;
						$update_soid=update_record('tbl_sales_ordertrn', $info_so, "sales_ordertrn_id=".$get_sales_order_details['sales_ordertrn_id'] , $dbcon);

						/* insert to tax transaction table by Dhruv */
						if( ($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'CGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_invoicetrn",
								$get_sales_order_details['product_id'],3,'','');
						}
						if( ($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'SGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_invoicetrn",
								$get_sales_order_details['product_id'],3,'','');
						}
						if( ($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
							$cl_id = get_ledger_by_name($dbcon,'IGST');
							$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_invoicetrn",
								$get_sales_order_details['product_id'],3,'','');
						}

					}
				}
			}			
			
		}
		if($inserid){
			echo "1";
		}else{
			echo "0";
		}
	
    }

    else if(strtolower($POST['mode'])== "get_ledger_details")
	{
		
		$ledger_id=$POST['ledger_id'];
		
		$row=get_ledger_details($dbcon,$ledger_id);
		
		echo json_encode($row);
	}

	else if(strtolower($POST['mode']) == "add_tcs_details") {

		//echo '<pre>';print_r($POST);exit;

		$info['tcs_lower_rate']	= $POST['tcs_lower_rate'];							
		$info['tcs_lower_rate_reason']	= $POST['tcs_lower_rate_reason'];							
		
		$info['tcs_section']	= $POST['tcs_section'];							
		$info['tcs_collection_code']	= $POST['tcs_collection_code'];							
		$info['tcs_ref_no']	= $POST['tcs_ref_no'];							
		$info['tcs_amt']	= $POST['tcs_amt'];	

		$info['tcs_collected_on']	= date("Y-m-d",strtotime($POST['tcs_collected_on']));	
		$info['tcs_invoice_date']	= date("Y-m-d",strtotime($POST['tcs_invoice_date']));							
		$info['tcs_percentage']	= $POST['tcs_percentage'];	

		$info['tcs_amount']	= $POST['tcs_amount'];							
		$info['tcs_sur_percentage']	= $POST['tcs_sur_percentage'];							
		$info['tcs_sur_percentage_amount']	= $POST['tcs_sur_percentage_amount'];							
		$info['tcs_total_tax']	= $POST['tcs_total_tax'];							
					
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		
		
		if($POST['edit_id']!='')
		{
			$updateid=update_record('tbl_tcs_deduction_transaction', $info,"tcs_deduct_id=".$POST['edit_id'] , $dbcon);
			
			if($updateid){
			echo "2";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_tcs_deduction_transaction', $info, $dbcon);
			
			if($inserid){
			echo "1";
			}
			else{
				echo "0";
			}
		}

	}
	else if(strtolower($POST['mode']) == "load_tcs_detail") {
	
		
		$invoice_id = $POST['invoice_id'];
		
		$query = "select * from tbl_tcs_deduction_transaction where tcs_sale_id='$invoice_id' and isdelete='0'";
		$select = $dbcon->query($query);
		$row = brp_mysqli_fetch_assoc($select);
		
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "insert_product")
	{
		
		$sales_order_id=implode(",",$POST['sales_order_id']);
		$invoice_id=$POST['eid'];
		
		$company_state = get_company_data($dbcon,$_SESSION['company_id']);		
		$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);
					
		$qry = "SELECT * FROM `tbl_sales_ordertrn` where sales_order_id in (".$sales_order_id.") and sales_ordertrn_status=0";
		$ex_q = $dbcon->query($qry);
		while($row=brp_mysqli_fetch_assoc($ex_q)){
			
			$isdelete['trancation_status'] = 2;
			$updatesalesid=update_record('tbl_invoicetrn', $isdelete ,"invoice_id=0", $dbcon);
			
			$istaxdelete['tx_status'] = 2;
			$updatesalesid=update_record('tbl_tax_trn', $istaxdelete ,"tx_transaction_type='tbl_invoicetrn' and tx_status=3", $dbcon);
			
			$company_state = get_company_data($dbcon,$_SESSION['company_id']);
			$sale_gst = get_tax_cat_by_hsn($dbcon,$row['product_hsn_code']);
			$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);
			
			$ven_s = "select stateid from tbl_ledger where l_id=".$POST['cust_id'];
			$ves=$dbcon->query($ven_s);
			$vers = mysqli_fetch_array($ves);
			$cgst_tax_rate=0;
			$sgst_tax_rate=0;
			$igst_tax_rate=0;
			if(($company_state['stateid'] == $vers['stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
				$gst = $sale_gst['tax_gst']/2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst*$row['product_amount'])/100;
				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst*$row['product_amount'])/100;
			}else{
				$igst_tax_per 	= $sale_gst['tax_gst'];
				$igst_tax_rate 	= ($sale_gst['tax_gst']*$row['product_amount'])/100;
			}
			
			$info1['product_id']		= $row['product_id'];
			$info1['description']		= $row['description'];
			$info1['product_spec']		= $row['product_spec'];
			$info1['product_hsn_code'] 	= $row['product_hsn_code'];
			$info1['product_qty'] 	   	= $row['product_qty'];
			$info1['product_rate'] 		= $row['product_rate'];
			$info1['unit_id'] 			= $row['unit_id'];
			$info1['product_amount'] 	= $row['product_amount'];
			$info1['total'] 	= $row['product_amount'] + $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
			$info1['trancation_status'] = 1;
			$info1['transaction_type'] = 1;
			
			$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
			$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
			$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
			$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
			$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;
			$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
			$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
			$info1['sales_ordertrn_id']	= $row['sales_ordertrn_id'];
			$info1['so_allocation_id'] = 	$row['sales_ordertrn_id'];
			$info1['user_id']			= $_SESSION['user_id'];	
			
			
			if(!empty($invoice_id)) {
				$info1['invoice_id'] = $invoice_id;
			}
			
			$table = "tbl_invoicetrn";
			
			$inserid=add_record($table, $info1, $dbcon);
			
			if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'CGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_invoicetrn",$row['product_id'],3,'',$POST['branch_id']);
			}
			if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'SGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_invoicetrn",$row['product_id'],3,'',$POST['branch_id']);
			}
			if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'IGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_invoicetrn",$row['product_id'],3,'',$POST['branch_id']);
			}
			
			$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$row['product_amount'],$inserid,$row['product_id'],0,$POST['branch_id'],'tbl_invoicetrn');
		}			
	}
	else if(strtolower($POST['mode'])== "get_so_detail")
	{	

		$cust_id = $POST['cust_id'];
		$q = $dbcon->query("select * from tbl_salesorder where cust_id='$cust_id'");
	}

    /*Dhruv end code*/

function get_product_tax($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
                $tax_total_amount+=$info['tax_amount'.$i];
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$j]='';
		$info['tax_amount'.$j]='';		
	}
	$info['total']=$rate_total;
        //$info['tax_total_amount']=$tax_total_amount;
	return $info;
}
function upd_qt_done_sts($dbcon,$quotation_id,$invoice_id){
	$qt_trn_qry="select sum(product_qty) as qt_qty from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$quotation_id;
	$qt_trn_rel=mysqli_fetch_assoc($dbcon->query($qt_trn_qry));
	//Invoice Qty
	$inv_trn_qry="select sum(product_qty) as inv_qty from tbl_invoicetrn as trn
	inner join tbl_invoice as inv on inv.invoice_id=trn.invoice_id
	where trn.trancation_status=0 and inv.invoice_status=0 and inv.quotation_id=".$quotation_id;
	$inv_trn_rel=mysqli_fetch_assoc($dbcon->query($inv_trn_qry));
	
	if(floatval($inv_trn_rel['inv_qty'])>=$qt_trn_rel['qt_qty']){
		$upd_qt="update tbl_quotation set inv_done_status=1 where quotation_id=".$quotation_id;
		$upd_qt_rs=$dbcon->query($upd_qt);
	}
	
	//Update Quotation trn rows
	$upd_qt_trn_qry="update tbl_quotation_trn set inv_done_status=1 where quot_trn_status=0 and find_in_set(quot_trn_id,(select group_concat(ref_quot_trn_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=".$invoice_id."))";
	$upd_qt_trn_qry_rs=$dbcon->query($upd_qt_trn_qry);
}
function upd_spare_inv_sts($dbcon,$complaint_id,$invoice_id){
	//Update Quotation trn rows
	$upd_qt_trn_qry="update tbl_complain_spare_part set s_inv_status=1 where s_inv_status=0 and find_in_set(s_id,(select group_concat(ref_s_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=".$invoice_id."))";
	$upd_qt_trn_qry_rs=$dbcon->query($upd_qt_trn_qry);
	
	$upd_comp_trn_qry="update tbl_complaint_trn set inv_done_status=1 where complaint_id=".$complaint_id;
	$upd_comp_trn_qry_rs=$dbcon->query($upd_comp_trn_qry);
}
function upd_inv_srl_no($dbcon,$invoice_id){
	$upd_qry="update `tbl_inv_srl_trn` set invoice_id=$invoice_id where find_in_set(trancation_id,(select group_concat(trancation_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=$invoice_id));";
	$upd_qry_rs=$dbcon->query($upd_qry);
}
function copy_srl_no($dbcon,$invoice_id){
	//Invoice DATA
	$inv_qry="select cust_id,invoice_no,invoice_date from tbl_invoice where invoice_id=".$invoice_id;
	$inv_rel=mysqli_fetch_assoc($dbcon->query($inv_qry));
	
	$srl_qry="select srl.pro_srl_no,(select product_id from tbl_invoicetrn where trancation_id=srl.trancation_id) as pro_id from tbl_inv_srl_trn as srl where srl.inv_srl_trn_status=0 and srl.invoice_id=".$invoice_id;
	$srl_qry_rs=$dbcon->query($srl_qry);
	while($srl_rel=mysqli_fetch_assoc($srl_qry_rs)){
		$info1['cust_id']				= $inv_rel['cust_id'];
		$info1['sold_inv_foc_date']		= date("Y-m-d",strtotime($inv_rel['invoice_date']));
		$info1['product_id']			= $srl_rel['pro_id'];
		$info1['sold_pro_srl_no']		= $srl_rel['pro_srl_no'];
		$info1['cdate']					= date("Y-m-d H:i:s");
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];
		
		$table='tbl_cust_sold_pro';$tableid='cust_sold_pro_id';
		$inserid=add_record($table, $info1, $dbcon);
		
	}
}
function general_book_tcs_entry($dbcon,$invoice_id,$branch_id){
    
        $qry = "select * from tbl_invoice as cert where invoice_status=0 and company_id = ".$_SESSION['company_id']." and invoice_id=".$invoice_id;
	$result = $dbcon->query($qry);
	$invoice = mysqli_fetch_assoc($result);
        
        $tax_qry="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax 
                WHERE tax_used_status=0 and used_transaction_id in (".$invoice_id.") and table_name='tbl_invoice' 
                GROUP BY ledger_id ORDER BY tax_used_id desc";
	$row=$dbcon->query($tax_qry);
        $tax = mysqli_fetch_assoc($row);
        
        
        $ledger_id = $dbcon->query("SELECT l_id FROM `tbl_ledger` where l_name like 'TCS' and l_group = '".DUTIES_AND_TAXES."' and company_id=".$_SESSION['company_id'])
            ->fetch_object()->l_id;
        
        $general_book_id = $dbcon->query("select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_invoice'")
            ->fetch_object()->general_book_id;
                
        $info['table_name']     = "tbl_invoice";
        $info['table_id']	= $invoice_id;
        $info['ref_date']	= date("Y-m-d",strtotime($invoice['invoice_date']));
        $info['entry_type']     = 1;
        $info['ledger_id']	= $ledger_id;
        $info['amount']         = $invoice['tcs_total'];
        $info['user_id']        = $_SESSION['user_id'];
        $info['cdate']		= date("Y-m-d H:i:s");
        $info['company_id']     = $_SESSION['company_id'];

        if($general_book_id){
                $updateid=update_record("tbl_general_book", $info,"general_book_id=".$general_book_id , $dbcon, $branch_id);
        }else{
                $inserid=add_record("tbl_general_book", $info, $dbcon, $branch_id);
        }
}

function general_book_tax_entry($dbcon,$invoice_id,$branch_id){
	$qry1="select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_invoice as cert where invoice_status=0 and company_id = ".$_SESSION['company_id']." and invoice_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$qry="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax WHERE tax_used_status=0 and used_transaction_id in (".$re["tid"].") and table_name='tbl_invoicetrn' group by ledger_id order by tax_used_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_invoice'";
                $ros=$dbcon->query($qry12);
                $re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']            = "tbl_invoice";
		$info1['table_id']		= $invoice_id;
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['invoice_date']));
		$info1['entry_type']            = 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['tamount'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']            = $_SESSION['company_id'];
		
		if(!empty($re2['general_book_id'])){
			$updateid=update_record("tbl_general_book", $info1,"general_book_id=".$re2['general_book_id'] , $dbcon, $branch_id);
		}else{
			$inserid=add_record("tbl_general_book", $info1, $dbcon, $branch_id);
		}
		//var_dump($re2['general_book_id']);
	}
	
}
function general_book_sercices_entry($dbcon,$invoice_id, $branch_id){
	$qry1="select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_invoice as cert where invoice_status=0 and invoice_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$qry="SELECT itrn.*,promst.ledger_id FROM `tbl_invoicetrn` as itrn 
			left join product_mst as promst on promst.product_id=itrn.product_id
			WHERE itrn.trancation_status=0 and promst.product_type=8 and itrn.invoice_id=".$invoice_id." order by itrn.trancation_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$tax['trancation_id']." and table_name='tbl_invoicetrn'";
			$ros=$dbcon->query($qry12);
			$re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']	= "tbl_invoicetrn";
		$info1['table_id']		= $tax['trancation_id'];
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['invoice_date']));
		$info1['entry_type']	= 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['product_amount'];
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
	
}
?>
