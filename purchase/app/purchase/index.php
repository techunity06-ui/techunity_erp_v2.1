<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
// /error_reporting(E_ALL);
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
		
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		$where='';

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		// $where_db = check_branch('po', $branch_id);
		 if(!empty($POST['branch_id']) && $POST['branch_id'] > 0){
                $where_db = " and po.branch_id = " . $POST['branch_id'];
            }

		$where.=" $where_db ";

		 
		$where_company=check_company('po');

		$where.=" $where_company";

		$where_user=check_userid('po');

		//$where.=" $where_user";
		$getspecialConfiguration=getspecialConfiguration($dbcon);
		
		if($POST['date_id']==2)
		{
			$where.="  and order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND order_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		}
		else
		{
			$where.="  and po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		}	
			//$where.=" and po.branch_id=$branch";
			$where.=" and po.company_id=$_SESSION[company_id]";
			
			$appData = array();
			$i=1;
			$aColumns = array('po.po_id','po.po_no','po.order_no','po.order_date','l.l_name','city.city_name','bms.branch_name','po.po_date','po.g_total','po.approve_status','po.paid_amount','po.status','po.cdate','po.userid','l.company_name','l.cust_mobile');
			$sIndexColumn = "po_id";
			$isWhere = array("status = 0".$where);
			$sTable = "tbl_pono as po";			
			$isJOIN = array('inner join  tbl_ledger as l on po.vender_id=l.l_id','left join  city_mst city on l.cityid=city.cityid','left join branch_mst as bms on bms.branch_id=po.branch_id');
			$hOrder = "po.po_id desc";
			include($include.'pagging.php');
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $id;

				$query = "select sum(po.g_total) as purchase_amt,sum((select sum(prt.product_amount) as amt from tbl_potrancation as prt where prt.potrancation_status=0 and prt.po_id=po.po_id)) as basic_amount from tbl_pono as po where po.status=0 and po.po_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND po.po_date<='".date('Y-m-d',strtotime($s_date[1]))."' ";
				
				$result = $dbcon->query($query);
				$res = brp_mysqli_fetch_array($result);

				if(in_array(PURCHASE_BILL_UPDATE,$bulkAccessArray)){
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$row['po_id'].'">'.$row["po_no"].'</a>';
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$row['po_id'].'">'.date('d M, Y',strtotime($row['po_date'])).'</a>';
					$row_data[] = $row["order_no"];
					$row_data[] = date('d M, Y',strtotime($row['order_date']));
					$row_data[] = '<a class="" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$row['po_id'].'">'.$row['l_name'].'</a>';
				}else{
					
					$row_data[] = $row["po_no"];
					$row_data[] = date('d M, Y',strtotime($row['po_date']));
					$row_data[] = $row["order_no"];
					$row_data[] = date('d M, Y',strtotime($row['order_date']));
					$row_data[] = $row['l_name'];
				}
				$row_data[] = $row['g_total'];
				$row_data[] = $res['purchase_amt'];
				$row_data[] = $res['basic_amount'];
					
				if(in_array(PURCHASE_BILL_DELETE,$bulkAccessArray)){
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['po_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
				
				if(in_array(PURCHASE_BILL_VIEW,$bulkAccessArray)){
					$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchase_view/'.$row['po_id'].'"><i class="fa fa-eye"></i></a> ';
				}
				if(in_array(PURCHASE_BILL_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$row['po_id'].'"><i class="fa fa-pencil"></i></a>';
					
					$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_purchase('.$row['po_id'].')"><i class="fa fa-trash-o"></i></button>';
				}

				$poprint = '';$whatsapp='';

				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 15 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$poprint.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['po_id'].'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
					}

				}
		
				

				$key = "encryptionkey";
				$text=$row['po_id'];
				$encrypted = bin2hex(openssl_encrypt($text,'AES-128-CBC', $key));
				$whatsapp.='<a title="Send to Whatsapp" type="button" class="btn btn-xs btn-success" href="https://web.whatsapp.com/send?phone=+91'.$row['cust_mobile'].'&text='.$rel['company_name'].'Thank you for your purchase.%0aPurchase No:-'.$row['order_no'].'%0aDate:- '.date('d-m-Y',strtotime($row['po_date'])).'%0aAmount:- '.$row['g_total'].'%0aBest Regards%0a '.DOMAIN.PRINT_ROOT.$res['page_path'].'linkpurchase_bill_print/'.$encrypted.'" target="_blank"> <i class="fa fa-whatsapp"></i></a>&nbsp;';              
				
				if(empty($row['cust_mobile'])){
					    $whatsapp = "";
					}
				$row_data[] = $edit.' '.$delete.' '.$view.' '.$poprint.' '.$whatsapp;

			 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$module_name=MODULE_PURCHASE;
			//echo '<pre>';print_r($POST);exit;
			/*if(isset($POST['currency_enable'])){*/
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            /*}else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }*/
			$info['invoicetype_id'] = $POST['invoicetype_id'];
			$info['po_no']		= $POST['po_no'];
			$info['vender_id']	= $POST['vender_id'];
            $info['purchase_ledger_id']	= $POST['purchase_ledger_id'];
			$info['po_date']	= date('Y-m-d',strtotime($POST['po_date']));
			$info['remark']		= $POST['remark'];
			$info['currency_enable'] = $POST['currency_enable']; //Added new by dhaval    
	       // $info['currency_id']	= (isset($POST['currency_enable'])) ? $POST['currency_id'] : 0 ; //Added new by dhaval
	       // $info['currency_rate']	= (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1 ; //Added new by dhaval 
			$info['enable_salesman']	= (isset($POST['enable_salesman'])) ? $POST['enable_salesman'] : 1 ; //Added new by dhaval 
			
			$info['enable_cost_center'] = (isset($POST['enable_cost_center']) && ($POST['enable_cost_center']=='yes')) ? 1 : 0; //Added new by dhaval
	        $info['enable_ewaybill'] = (isset($POST['enable_ewaybill']) && ($POST['enable_ewaybill']=='yes')) ? 1 : 0; //Added new by dhaval
	        $info['enable_transport'] = (isset($POST['enable_transport']) && ($POST['enable_transport']=='yes')) ? 1 : 0;

	        $info['eway_bill_no'] = $POST['eway_bill_no']; //Added new by dhaval
	        $info['eway_bill_date'] = $POST['eway_bill_date']; //Added new by dhaval

	        $info['order_no']	= $POST['invoice_no']; //Added by Maulik Kapatel
			$info['order_date']	= date('Y-m-d',strtotime($POST['invoice_date'])); // Added by Maulik Kapatel


			if(isset($POST['save_print']))
			{
				$info['print_status']	= $POST['print_status'];
			}
			$info['grn_id']				= implode(",",$POST['grn_id']);
			$info['service_id']			= implode(",",$POST['service_id']);
			$info['financial_year_id']	= $POST['financial_year'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['mdate']				= date("Y-m-d H:i:s");
			$info['userid']				= $_SESSION['user_id'];
			$info['muserid']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['usertype_id']		= $_SESSION['user_type'];
			$info['branch_id']   		= $POST['branch_id'];
			$info['purchase_material_center']   = $POST['purchase_material_center'];
			//Added by dhruv - 20_12_2021
			if($POST['currency_id']==$_SESSION['currency_id']){
				$info['tds_amount'] = $POST['tds_amount'];
				$info['total']		= $POST['total'];
				$info['g_total']	= $POST['g_total'];
				$info['round_of']	= $POST['round_of'];
				$info['tds_amount_conv'] = $POST['tds_amount']*$POST['currency_rate'];
				$info['total_conv']		= $POST['total']*$POST['currency_rate'];
				$info['g_total_conv']	= $POST['g_total']*$POST['currency_rate'];
				$info['round_of_conv']	= $POST['round_of']*$POST['currency_rate'];
			}else{
				$info['tds_amount'] = $POST['tds_amount']*$POST['currency_rate'];
				$info['total']		= $POST['total']*$POST['currency_rate'];
				$info['g_total']	= $POST['g_total']*$POST['currency_rate'];
				$info['round_of']	= $POST['round_of']*$POST['currency_rate'];
				$info['tds_amount_conv'] = $POST['tds_amount'];
				$info['total_conv']		= $POST['total'];
				$info['g_total_conv']	= $POST['g_total'];
				$info['round_of_conv']	= $POST['round_of'];
			}
			$info['tds_per']   			= $POST['tds_per'];
			$info['enable_bill_adjustment'] = $POST['bill_adjustment'];
			$info['sales_type']   = $POST['sales_type'];
			
			//print_r($POST['bill_sundry_tax']);exit;
			$module_id=$inserpoid=add_record('tbl_pono', array_merge($info,$curncy_trn), $dbcon);
			
			/*Update Product Trn Table Start by Dhruv*/
			if($inserpoid){
					$inftrn['po_id'] = $inserpoid;
					$inftrn['potrancation_status'] = 0;
					$updatetrnid=update_record('tbl_potrancation', $inftrn,"user_id=".$_SESSION['user_id']." and  potrancation_status=3" , $dbcon);

					$cust_name = get_ledger_expense_by_id($dbcon, $POST['vender_id']); 
					tbl_transcation_entry($dbcon,"Purchase",$POST['po_no'],$inserpoid,$cust_name,$POST['g_total']);
			}
			update_service_purchase_status($dbcon,$inserpoid);
			/*Update Cost center Trn Table Start by Dhruv*/
	        if($inserpoid && $POST['enable_cost_center']=='yes'){
				$cost_trn['cost_center_ledger_id']	= $POST['vender_id'];
				$cost_trn['cost_center_table_id'] = $inserpoid;
				$updatecosttrnid=update_record('tbl_cost_center_transaction', array_merge($cost_trn,$curncy_trn),"isdelete=0 and cost_center_table='tbl_pono' and user_id=".$_SESSION['user_id'] , $dbcon);
			}
			
			
			/** Insert in general book table By Dhruv **/
			if($inserpoid){
				

            	add_general_book_entry($dbcon,"tbl_pono",$inserpoid,2,$POST['purchase_ledger_id'],$info['total'],'',$POST['po_date'],'',$curncy_trn,$module_name,$module_id);

            	add_general_book_entry($dbcon,"tbl_pono",$inserpoid,1,$POST['vender_id'],$info['g_total'],'',$POST['po_date'],'',$curncy_trn,$module_name,$module_id);
				
            	if(!empty($POST['tds_amount'])){
					add_general_book_entry($dbcon,"tbl_pono",$inserpoid,2,$POST['vender_id'],abs($info['tds_amount']),'',$POST['po_date'],'',$curncy_trn,$module_name,$module_id,24453);
            	}
				
				
				if( $POST['round_of'] < 0){
					
					add_general_book_entry($dbcon,"tbl_pono",$inserpoid,2,98777,abs($info['round_of']),'',$POST['invoice_date'],'',$curncy_trn,$module_name,$module_id); 
				}
				else
				{
					add_general_book_entry($dbcon,"tbl_pono",$inserpoid,1,98777,abs($info['round_of']),'',$POST['invoice_date'],'',$curncy_trn,$module_name,$module_id); 
				}
				
				
          	
            	foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount) 
				{
					//var_dump($bill_sundry_tax_amount);
           			
           			if($bill_sundry_tax_amount < 0){
           				$cr_dr = 1;
						
           			}else{
           				$cr_dr = 2;
           			}
					
           			$info_sundry_tax['sundry_ledger_id']=$bill_sundry_tax_id;
					//$info_sundry_tax['sundry_amount']=abs($bill_sundry_tax_amount);
					$info_sundry_tax['sundry_voucher_id']=$inserpoid;
					$info_sundry_tax['sundry_voucher_type']=PURCHASE_VOUCHER;
					$info_sundry_tax['sundry_voucher_table']='tbl_pono';
					$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			        $info_sundry_tax['user_id']	= $_SESSION['user_id'];
			        $info_sundry_tax['company_id']	= $_SESSION['company_id'];

			        if($POST['currency_id']==$_SESSION['currency_id']){
						$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
						$info_sundry_tax['sundry_amount_conv']=$bill_sundry_tax_amount*$POST['currency_rate'];
					}else{
						$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount*$POST['currency_rate'];
						$info_sundry_tax['sundry_amount_conv']=$bill_sundry_tax_amount;
					}

					$sundry_tax_insert=add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_tax,$curncy_trn), $dbcon);

            		add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_tax_insert,$cr_dr,$bill_sundry_tax_id,abs($info_sundry_tax['sundry_amount']),'',$POST['po_date'],'',$curncy_trn,$module_name,$module_id);

            		if($bill_sundry_tax_id == 9870){
            			if($POST['currency_id']==$_SESSION['currency_id']){
							$infogsttax['cgst'] = $bill_sundry_tax_amount;
							$infogsttax['cgst_conv'] = $bill_sundry_tax_amount*$POST['currency_rate'];
						}else{
							$infogsttax['cgst'] = $bill_sundry_tax_amount*$POST['currency_rate'];
							$infogsttax['cgst_conv'] = $bill_sundry_tax_amount;
						}
            			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$inserpoid." " , $dbcon);
            		}else if($bill_sundry_tax_id == 9880){
        				if($POST['currency_id']==$_SESSION['currency_id']){
							$infogsttax['sgst'] = $bill_sundry_tax_amount;
							$infogsttax['sgst_conv'] = $bill_sundry_tax_amount*$POST['currency_rate'];
						}else{
							$infogsttax['sgst'] = $bill_sundry_tax_amount*$POST['currency_rate'];
							$infogsttax['sgst_conv'] = $bill_sundry_tax_amount;
						}
            			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$inserpoid." ", $dbcon);
            		}else if($bill_sundry_tax_id == 9890){
            			if($POST['currency_id']==$_SESSION['currency_id']){
							$infogsttax['igst'] = $bill_sundry_tax_amount;
							$infogsttax['igst_conv'] = $bill_sundry_tax_amount*$POST['currency_rate'];
						}else{
							$infogsttax['igst'] = $bill_sundry_tax_amount*$POST['currency_rate'];
							$infogsttax['igst_conv'] = $bill_sundry_tax_amount;
						}
            			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$inserpoid." " , $dbcon);
            		}else if($bill_sundry_tax_id == 9895){
            			$infogsttax['tds'] = $bill_sundry_tax_amount*$POST['currency_rate'];
            			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$inserpoid." ", $dbcon);
            		}

            	}

            	foreach ($POST['bill_sundry_addon'] as $bill_sundry_addon_id => $bill_sundry_addon_amount) {

            		$info_sundry_addon['sundry_ledger_id']=$bill_sundry_addon_id;
					//$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount;
					$info_sundry_addon['sundry_voucher_id']=$inserpoid;
					$info_sundry_addon['sundry_voucher_type']=PURCHASE_VOUCHER;
					$info_sundry_addon['sundry_voucher_table']='tbl_pono';
					$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
			        $info_sundry_addon['user_id']	= $_SESSION['user_id'];
			        $info_sundry_addon['company_id']	= $_SESSION['company_id'];

			        if($POST['currency_id']==$_SESSION['currency_id']){
						$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount;
						$info_sundry_addon['sundry_amount_conv']=$bill_sundry_addon_amount*$POST['currency_rate'];
					}else{
						$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount*$POST['currency_rate'];
						$info_sundry_addon['sundry_amount_conv']=$bill_sundry_addon_amount;
					}

					$sundry_addon_insert=add_record('tbl_bill_sundry_transaction',array_merge($info_sundry_addon,$curncy_trn), $dbcon);

					if($bill_sundry_addon_amount < 0){
						add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_addon_insert,1,$bill_sundry_addon_id,abs($info_sundry_addon['sundry_amount']),'',$POST['po_date'],'',$curncy_trn,$module_name,$module_id);
						
						$info_gen1['table_name']	= 'tbl_pono';
						$info_gen1['table_id']		= $inserpoid;
						$info_gen1['entry_type']	= 2;
						$info_gen1['ref_date']		= date('Y-m-d',strtotime($POST['po_date']));
						$info_gen1['ledger_id']		= $POST['vender_id'];
						$info_gen1['amount']		= abs($bill_sundry_addon_amount);
						$info_gen1['user_id']		= $_SESSION['user_id'];
						$info_gen1['cdate']			= date("Y-m-d H:i:s");
						$info_gen1['company_id']	= $_SESSION['company_id'];
						$info_gen1['ref_by'] = 'tbl_addon_bill_sundry';
						
						//$inserid_gen1=add_record("tbl_general_book", array_merge($info_gen1,$curncy_trn) , $dbcon);

						//add_general_book_entry($dbcon,"tbl_pono",$inserpoid,2,$POST['vender_id'],abs($bill_sundry_addon_amount),'',$POST['po_date'],'',$curncy_trn);
					}else{
						add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_addon_insert,2,$bill_sundry_addon_id,$info_sundry_addon['sundry_amount'],'',$POST['po_date'],'',$curncy_trn,$module_name,$module_id);
						
						$info_gen2['table_name']	= 'tbl_pono';
						$info_gen2['table_id']		= $inserpoid;
						$info_gen2['entry_type']	= 1;
						$info_gen2['ref_date']		= date('Y-m-d',strtotime($POST['po_date']));
						$info_gen2['ledger_id']		= $POST['vender_id'];
						$info_gen2['amount']		= $bill_sundry_addon_amount;
						$info_gen2['user_id']		= $_SESSION['user_id'];
						$info_gen2['cdate']			= date("Y-m-d H:i:s");
						$info_gen2['company_id']	= $_SESSION['company_id'];
						$info_gen2['ref_by'] = 'tbl_addon_bill_sundry';
						
						//$inserid_gen2=add_record("tbl_general_book", array_merge($info_gen2,$curncy_trn) , $dbcon);

						//add_general_book_entry($dbcon,"tbl_pono",$inserpoid,1,$POST['vender_id'],$bill_sundry_addon_amount,'',$POST['po_date'],'',$curncy_trn);
					}

            		// add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_addon_insert,1,$bill_sundry_addon_id,$bill_sundry_addon_amount,'',$POST['po_date'],'',$curncy_trn);
            	}

            	foreach($POST['bill_sundry_addon_tax'] as $addon_id=>$addon_value){

            		$addon_explode = explode("-",$addon_value);

            		$info_addon['sundry_gst_per'] = $addon_explode[1];
            		$info_addon['sundry_gst_amount'] = $addon_explode[0];
            		$updateaddontaxid=update_record('tbl_bill_sundry_transaction', $info_addon,"sundry_voucher_table='tbl_pono' and isdelete=0 and sundry_voucher_id=".$inserpoid." and sundry_ledger_id=".$addon_id." " , $dbcon);
            	}

            	
        	}

        	//tds percentage entry 

        	foreach($POST['tds_per'] as $tds_id=>$tds_value){

        		$info_tds['tds_per'] = $tds_value;

        		$updatetdstaxid=update_record('tbl_bill_sundry_transaction', $info_tds,"sundry_voucher_table='tbl_pono' and isdelete=0 and sundry_voucher_id=".$inserpoid." and sundry_ledger_id=".$tds_id." " , $dbcon);

        		$sel_sundry = $dbcon->query("select sundry_id from tbl_bill_sundry_transaction where sundry_voucher_id=".$inserpoid." and sundry_ledger_id=".$tds_id." ");
        		$r_sundry = brp_mysqli_fetch_assoc($sel_sundry);
        		$sundry_id = $r_sundry['sundry_id'];

        		$infog['general_percentage'] =  $tds_value;

        		update_record("tbl_general_book",$infog,"table_name='tbl_bill_sundry_transaction' and table_id= ".$sundry_id." " ,$dbcon);
        	}

        	//add general book entry for service and capital goods products 

        	if($inserpoid){

        		$sel_gen = $dbcon->query("select trn.*,p.product_type,p.ledger_id from tbl_potrancation as trn 
        			left join product_mst as p on trn.product_id=p.product_id
        			where trn.po_id='$inserpoid' and trn.potrancation_status='0'");

        		while($r_gen = brp_mysqli_fetch_assoc($sel_gen))
        		{
        			update_grn_sub_trn_to_purchase_status($dbcon,$r_gen['grn_sub_trn_id']);
        			add_general_book_entry($dbcon,"tbl_potrancation",$r_gen['potrancation_id'],2,$r_gen['ledger_id'],$r_gen['product_amount'],'',$POST['po_date'],'',$curncy_trn,$module_name,$module_id); 
        		}
        	}

			/*Update Tax Trn Table Start by Dhruv*/
	        if($inserpoid){
				$tax_trn['tx_status'] = 0;
				$tax_trn['tx_trn_ref_id'] = $inserpoid;
				$updatetcstrnid=update_record('tbl_tax_trn', array_merge($tax_trn,$curncy_trn),"tx_transaction_type='tbl_potrancation' and tx_status = 3" , $dbcon);
			}

			/*Update Salesman Table Start by Dhruv*/
			if($inserpoid && $POST['enable_salesman']=='yes'){
				$salesman_trn['transaction_table_id'] = $inserpoid;
				$updatesalesmantrnid=update_record('tbl_salesman_transaction', array_merge($salesman_trn,$curncy_trn),"transaction_voucher_type=".PURCHASE_VOUCHER." and transaction_table_id = 0" , $dbcon);
			}

			/* Update voucher No */
			if($inserpoid){
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=12 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			}
			
			/* Eway Bill API */
			if($inserpoid && $POST['enable_ewaybill']=='yes')
			{
				$eway_row=getTransportEwayDetails($dbcon,PURCHASE_VOUCHER);
				$company_data = get_company_data($dbcon,$_SESSION['company_id']);
				$customer_ledger_data = get_ledger_details($dbcon,$POST['vender_id']);
				$product_details = get_trans_by_inv_id($dbcon,$inserpoid);
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
							 "Year": "'.date('Y',strtotime($POST['po_date'])).'",       
							 "Month": "'.date('m',strtotime($POST['po_date'])).'",      
							 "SupplyType": "O",
							 "SubType": "'.$sub_type['common_mst_desc'].'",       
							 "DocType": "INV",        
							 "DocNo": "'.$POST['po_no'].'", 
							 "DocDate": "'.date('Ymd',strtotime($POST['po_date'])).'",    
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

							 "IsGSTINSEZ": "0"  
							 }, ';
					}		 
				$jsonobj .= ' ],
					 "Year": "'.date('Y').'",
					 "Month": "'.date('m').'",
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
				
				
				
				//echo $eway_bill_status;exit;
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
			
			$updateinvtbl=update_record('tbl_pono', $info_invtbl,"po_id='".$inserpoid."'" , $dbcon);
			
			$updatetcstrnid=update_record('tbl_ewaybill_transaction',array_merge($eway_status_trn,$curncy_trn),"eway_bill_voucher_type='2' and eway_bill_voucher_id ='0'" , $dbcon);

			/*Update Trasport and Eway trans Table Start by Dhruv*/
	        if($inserpoid && $POST['enable_ewaybill']=='yes'){
				$transp_trn['transport_transaction_table_id'] = $inserpoid;
				$updatetcstrnid=update_record('tbl_transport_transaction', array_merge($transp_trn,$curncy_trn),"transport_transaction_table='tbl_pono' and transport_transaction_table_id = 0" , $dbcon);
			}

			//bill by bill adjustment

			if($inserpoid){
				
				$bill_adjustment = $POST['bill_adjustment'];

				if($bill_adjustment==1)
				{
					$adj_trn['bill_ref'] = $inserpoid;
					$adj_trn['bill_due_date'] = date('Y-m-d',strtotime($POST['po_date']));
					$adj_trn['bill_table_id'] = $inserpoid;
					
					update_record('tbl_bill_by_bill_adjustment_transaction', array_merge($adj_trn,$curncy_trn),"bill_table='TBL_PONO' and bill_table_id = 0" , $dbcon);

					$sel1=$dbcon->query("select bill_transaction_id,bill_amount from tbl_bill_by_bill_adjustment_transaction where bill_adjustment_status='0' ");
					while($row1=brp_mysqli_fetch_assoc($sel1))
					{
						$amount = $row1['bill_amount'];
						$sel2=$dbcon->query("select sum(bill_amount) as paid_total from tbl_bill_by_bill_adjustment_transaction where bill_adjustment_id='$row1[bill_transaction_id]'");
						$row2 = brp_mysqli_fetch_assoc($sel2);

						$remaining = $amount - $row2['paid_total'];

						if($remaining==0)
						{
							$advance_trn['bill_adjustment_status'] = 1;

							update_record('tbl_bill_by_bill_adjustment_transaction',array_merge($advance_trn,$curncy_trn),"bill_transaction_id='$row1[bill_transaction_id]'" , $dbcon);
						}
					}

				}
			}

			
			
			if($inserpoid)
			{	
				$arr['msg']="1";							
			}
			else
			{
				$arr['msg']="0";
			}
			
			echo json_encode($arr);
			
		}		
		else if(strtolower($POST['mode']) == "edit") {
		//echo '<pre>';print_r($POST);exit;
			/*if(isset($POST['currency_enable'])){*/
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            /*}else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }*/
			
			$info['po_no']		= $POST['po_no'];
			$info['vender_id']	= $POST['vender_id'];
            $info['purchase_ledger_id']	= $POST['purchase_ledger_id'];
			$info['po_date']	= date('Y-m-d',strtotime($POST['po_date']));
			$info['remark']		= $POST['remark'];
			$info['grn_id']				= implode(",",$POST['grn_id']);
			$info['service_id']			= implode(",",$POST['service_id']);
			$info['currency_enable'] 	= $POST['currency_enable']; //Added new by dhaval    
	       // $info['currency_id']		= (isset($POST['currency_enable'])) ? $POST['currency_id'] : 0 ; //Added new by dhaval
	        //$info['currency_rate']		= (isset($POST['currency_enable'])) ? $POST['currency_rate'] : 1 ; //Added new by dhaval 
			$info['enable_salesman']	= (isset($POST['enable_salesman'])) ? $POST['enable_salesman'] : 1 ; //Added new by dhaval 
			
			$info['enable_cost_center'] = (isset($POST['enable_cost_center']) && ($POST['enable_cost_center']=='yes')) ? 1 : 0; //Added new by dhaval
	        $info['enable_ewaybill'] 	= (isset($POST['enable_ewaybill']) && ($POST['enable_ewaybill']=='yes')) ? 1 : 0; //Added new by dhaval

	        $info['eway_bill_no'] = $POST['eway_bill_no']; //Added new by dhaval
	        $info['eway_bill_date'] = $POST['eway_bill_date']; //Added new by dhaval


    		$info['order_no']	= $POST['invoice_no']; //Added by Maulik Kapatel
			$info['order_date']	= date('Y-m-d',strtotime($POST['invoice_date'])); // Added by Maulik Kapatel

			$info['sales_type'] = $POST['sales_type'];

			if(isset($POST['save_print']))
			{
				$info['print_status']	= $POST['print_status'];
			}
			$info['financial_year_id']	= $POST['financial_year'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['mdate']				= date("Y-m-d H:i:s");
			$info['userid']			= $_SESSION['user_id'];
			$info['muserid']			= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$info['usertype_id']		= $_SESSION['user_type'];
			$info['branch_id']   = $POST['branch_id'];
			$info['purchase_material_center']   = $POST['purchase_material_center'];
			//Added by dhruv - 20_12_2021
			if($POST['currency_id']==$_SESSION['currency_id']){
				$info['tds_amount'] = $POST['tds_amount'];
				$info['total']		= $POST['total'];
				$info['g_total']	= $POST['g_total'];
				$info['round_of']	= $POST['round_of'];
				$info['tds_amount_conv'] = $POST['tds_amount']*$POST['currency_rate'];
				$info['total_conv']		= $POST['total']*$POST['currency_rate'];
				$info['g_total_conv']	= $POST['g_total']*$POST['currency_rate'];
				$info['round_of_conv']	= $POST['round_of']*$POST['currency_rate'];
			}else{
				$info['tds_amount'] = $POST['tds_amount']*$POST['currency_rate'];
				$info['total']		= $POST['total']*$POST['currency_rate'];
				$info['g_total']	= $POST['g_total']*$POST['currency_rate'];
				$info['round_of']	= $POST['round_of']*$POST['currency_rate'];
				$info['tds_amount_conv'] = $POST['tds_amount'];
				$info['total_conv']		= $POST['total'];
				$info['g_total_conv']	= $POST['g_total'];
				$info['round_of_conv']	= $POST['round_of'];
			}
			$info['tds_per']   = $POST['tds_per'];
			$inforoundof['amount'] = abs($info['round_of']);
			
			
			
			$inserpoid=update_record('tbl_pono', array_merge($info,$curncy_trn),"po_id=".$POST['eid'] , $dbcon);
			
			
			$info_total['amount']	= abs($POST['total']);
			update_record("tbl_general_book",$info_total," ledger_id=".$POST['purchase_ledger_id']." and table_name='tbl_pono' and table_id= ".$POST['eid']." " ,$dbcon);
			$info_g_total['amount']	= abs($POST['g_total']);
			update_record("tbl_general_book",$info_g_total," ledger_id=".$POST['vender_id']." and table_name='tbl_pono' and table_id= ".$POST['eid']." " ,$dbcon);
			
			if( $POST['round_of'] < 0)
				{
						
					$inforoundof['entry_type'] = 2 ; 
					update_record("tbl_general_book",$inforoundof," ledger_id= 98777 and table_name='tbl_pono' and table_id=".$POST['eid'] ,$dbcon);

				}
				else
				{
					$inforoundof['entry_type'] = 1 ; 
					update_record("tbl_general_book",$inforoundof," ledger_id= 98777 and table_name='tbl_pono' and table_id=".$POST['eid'] ,$dbcon);
				}
			
			

			

			//Update Date in general book table
			
			/////////////////////////////////////////////////////harshil///////////////////////////////////////////////////////////////
			
			//print_r($POST['bill_sundry_tax']);exit;
			foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount) 
				{
           			
           			if($bill_sundry_tax_amount < 0){
           				$cr_dr = 1;
           			}else{
           				$cr_dr = 2;
           			}
					
					if($bill_sundry_tax_id == 24453)
					{
						
					
					
           			$info_sundry_tax_1['sundry_ledger_id']=$bill_sundry_tax_id;
					//$info_sundry_tax_1['sundry_amount']=abs($bill_sundry_tax_amount);
					//$info_sundry_tax_1['sundry_voucher_id']=$inserpoid;
					$info_sundry_tax_1['sundry_voucher_type']=PURCHASE_VOUCHER;
					$info_sundry_tax_1['sundry_voucher_table']='tbl_pono';
					$info_sundry_tax_1['cdate']	= date("Y-m-d H:i:s");
			        $info_sundry_tax_1['user_id']	= $_SESSION['user_id'];
			        $info_sundry_tax_1['company_id']	= $_SESSION['company_id'];
					
					if($POST['currency_id']==$_SESSION['currency_id']){
						$info_sundry_tax_1['sundry_amount']=$bill_sundry_tax_amount;
						$info_sundry_tax_1['sundry_amount_conv']=$bill_sundry_tax_amount*$POST['currency_rate'];
					}else{
						$info_sundry_tax_1['sundry_amount']=$bill_sundry_tax_amount*$POST['currency_rate'];
						$info_sundry_tax_1['sundry_amount_conv']=$bill_sundry_tax_amount;
					}

					$updatetds=update_record('tbl_bill_sundry_transaction', array_merge($info_sundry_tax_1,$curncy_trn),"sundry_voucher_id=".$POST['eid']." and sundry_ledger_id=".$bill_sundry_tax_id , $dbcon);
					}
				/*	$sundry_tax_insert=add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_tax,$curncy_trn), $dbcon);

            		add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_tax_insert,$cr_dr,$bill_sundry_tax_id,abs($bill_sundry_tax_amount),'',$POST['po_date'],'',$curncy_trn);
*/
            		if($bill_sundry_tax_id == 9870)
					{
            			if($POST['currency_id']==$_SESSION['currency_id']){
							$infogsttax['cgst'] = $bill_sundry_tax_amount;
							$infogsttax['cgst_conv'] = $bill_sundry_tax_amount*$POST['currency_rate'];
						}else{
							$infogsttax['cgst'] = $bill_sundry_tax_amount*$POST['currency_rate'];
							$infogsttax['cgst_conv'] = $bill_sundry_tax_amount;
						}
            			$updateinvoice=update_record('tbl_bill_sundry_transaction',$infogsttax,"sundry_voucher_id=".$POST['eid']." and sundry_ledger_id=".$bill_sundry_tax_id , $dbcon);
            		}
					
					else if($bill_sundry_tax_id == 9880){
            			if($POST['currency_id']==$_SESSION['currency_id']){
							$infogsttax['sgst'] = $bill_sundry_tax_amount;
							$infogsttax['sgst_conv'] = $bill_sundry_tax_amount*$POST['currency_rate'];
						}else{
							$infogsttax['sgst'] = $bill_sundry_tax_amount*$POST['currency_rate'];
							$infogsttax['sgst_conv'] = $bill_sundry_tax_amount;
						}
            			$updateinvoice=update_record('tbl_bill_sundry_transaction',$infogsttax,"sundry_voucher_id=".$POST['eid']." and sundry_ledger_id=".$bill_sundry_tax_id , $dbcon);
            		}
					else if($bill_sundry_tax_id == 9890){
        				if($POST['currency_id']==$_SESSION['currency_id']){
							$infogsttax['igst'] = $bill_sundry_tax_amount;
							$infogsttax['igst_conv'] = $bill_sundry_tax_amount*$POST['currency_rate'];
						}else{
							$infogsttax['igst'] = $bill_sundry_tax_amount*$POST['currency_rate'];
							$infogsttax['igst_conv'] = $bill_sundry_tax_amount;
						}
            			$updateinvoice=update_record('tbl_bill_sundry_transaction',$infogsttax,"sundry_voucher_id=".$POST['eid']." and sundry_ledger_id=".$bill_sundry_tax_id , $dbcon);

            		}
					/*else if($bill_sundry_tax_id == 9895){
            			$infogsttax['tcs'] = $bill_sundry_tax_amount*$POST['currency_rate'];
            			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$inserpoid." ", $dbcon);
            		}*/

            	}
				///////////////////////////////////////////////////////////////////////////////////////harshil end/////////////////////////////////////////////////////////
			
			
			
			
			
			  $query1="select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[eid]'  and isdelete=0 and sundry_voucher_table='tbl_pono'  ";
			//exit();
			$rel1=brp_mysqli_fetch_all($dbcon->query($query1));

			foreach ($rel1 as $bill_sundry_addon){
				$info_general_sundry['ref_date'] = date('Y-m-d',strtotime($POST['po_date']));
				$info_general_sundry['amount'] = $bill_sundry_addon['sundry_amount'];
							
				update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_addon['sundry_ledger_id']." and table_name='tbl_bill_sundry_transaction' and table_id= ".$bill_sundry_addon['sundry_id']." " ,$dbcon);
			}

			foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount){

				if($bill_sundry_tax_id == 9870){
        			$infogsttax['cgst'] = $bill_sundry_tax_amount;
        			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$POST['eid']." " , $dbcon);
        		}else if($bill_sundry_tax_id == 9880){
        			$infogsttax['sgst'] = $bill_sundry_tax_amount;
        			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$POST['eid']." ", $dbcon);
        		}else if($bill_sundry_tax_id == 9890){
        			$infogsttax['igst'] = $bill_sundry_tax_amount;
        			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$POST['eid']." " , $dbcon);
        		}else if($bill_sundry_tax_id == 9895){
        			$infogsttax['tds'] = $bill_sundry_tax_amount;
        			$updateinvoice=update_record('tbl_pono',$infogsttax,"po_id=".$POST['eid']." ", $dbcon);
        		}

			}

			 
				
			
			if($inserpoid)
			{	
				$arr['msg']="update";							
			}
			else
			{
				$arr['msg']="0";
			}
			
			echo json_encode($arr);
			 
		}
		else if(strtolower($POST['mode']) == "delete") {
			
			
			
			$info['status']		= 2;
			$info1['potrancation_status']		= 2;
			$informdr['status'] = 2;
			$info_sales_order['invoice_status']  = 0;
			$updateinvoiceid=update_record('tbl_pono', $info,"po_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_potrancation', $info1,"po_id=".$POST['eid'] , $dbcon);	
			//Update Payment Reminder
			$updatermdrid=update_record('todo_mst', $informdr,"ref_id=".$POST['eid']." and ref_table='tbl_pono'" , $dbcon);
			//Update Serial Number
			//$deleteid=delete_record('tbl_serialtrn',"invoice_id=".$POST['eid'], $dbcon);
			
			$info_gen['genral_book_status']		= 2;
			$updateinvoiceid1=update_record('tbl_general_book', $info_gen,"table_name='tbl_pono' and table_id=".$POST['eid'] , $dbcon);	
			
			//tax transaction
			
			$sel_itrn = $dbcon->query("select * from  tbl_potrancation where po_id='$POST[eid]' and potrancation_status='2'");
			while($r_itrn=brp_mysqli_fetch_array($sel_itrn))
			{
				$info_tax_trn['tx_status']=2;
				update_record("tbl_tax_trn", $info_tax_trn,"tx_transaction_id='$r_itrn[potrancation_id]' and tx_transaction_type='tbl_potrancation'" ,$dbcon);

				$info_general['genral_book_status'] = 2;
				update_record('tbl_general_book', $info_general,"table_name='tbl_potrancation' and table_id=".$r_itrn['potrancation_id'] , $dbcon);

				
				$info_grn['purchase_status'] = 0;
				$updateid=update_record('tbl_grn', $info_grn, "grn_id=".$r_itrn['grn_id'] , $dbcon);
				$updateid=update_record('tbl_grn_trn', $info_grn, "grn_trn_id=".$r_itrn['grn_trn_id'] , $dbcon);
				$updateid=update_record('tbl_grn_sub_trn', $info_grn, "grn_trn_sub_id=".$r_itrn['grn_sub_trn_id'] , $dbcon);

				$info_used['po_grn_used_status'] =2;
	    		
	    		$updateid=update_record('tbl_po_grn_used', $info_used, "potrancation_id=".$r_itrn['potrancation_id'] , $dbcon);
			}
			
			//Eway Bill Transaction
			
			$eway_trans['isdelete']=1;
			$updateinvoiceid2=update_record('tbl_ewaybill_transaction', $eway_trans,"eway_bill_voucher_table='tbl_pono' and eway_bill_voucher_id=".$POST['eid'] , $dbcon);	
			
			//Transport Transaction
			
			$transport_transaction['transportation_status']=1;
			$updateinvoiceid3=update_record('tbl_transport_transaction', $transport_transaction,"transport_transaction_table='tbl_pono' and transport_transaction_table_id=".$POST['eid'] , $dbcon);	
			
			
			//Salesman Transaction
			
			$salesman_transaction['isdelete']=1;
			$updateinvoiceid4=update_record('tbl_salesman_transaction', $salesman_transaction,"transaction_table='tbl_pono' and transaction_table_id=".$POST['eid'] , $dbcon);	
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"invoice_add",3,"tbl_pono",$POST['eid']);
			
			//Cost Center Transaction
			
			$info_cost['costcenter_status'] = 2;
			$updateid1=update_record("tbl_cost_center_transaction", $info_cost, "table_name='tbl_pono' and table_id=".$POST['eid'], $dbcon);
			
			//TCS Deduction Transaction
			
			$info_tcs['isdelete'] = 1;
			$updateid1=update_record("tbl_tcs_deduction_transaction", $info_tcs, "tcs_sale_id=".$POST['eid'], $dbcon);
			
			//Bill Sundry Transaction
			
			$info_bsun['isdelete'] = 1;
			$updateid1=update_record("tbl_bill_sundry_transaction", $info_bsun, "sundry_voucher_table='tbl_pono' and sundry_voucher_id='$POST[eid]'", $dbcon);
			
			$sel_bsun = $dbcon->query("select * from tbl_bill_sundry_transaction where sundry_voucher_table='tbl_pono' and sundry_voucher_id='$POST[eid]' and isdelete='1'");
			while($r_bsun=brp_mysqli_fetch_array($sel_bsun))
			{
				$info_bsun_general['genral_book_status'] = 2;
				$updateid1=update_record("tbl_general_book", $info_bsun_general, "table_name='tbl_bill_sundry_transaction' and table_id='$r_bsun[sundry_id]'", $dbcon);
			}
			if($updateinvoiceid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			//$qry="select popro.*, from tbl_purchaseproduct as porpo left join tbl_company as com on com.company_id=".$_SESSION['company_id']." where product_id=".$POST['eid'];
			$qry="select popro.*,com.stateid as com_stateid,ven.stateid as ven_stateid,u.unit_name from `product_mst` as popro left join `tbl_company` as com on com.company_id=".$_SESSION['company_id']." left join tbl_ledger as ven on ven.l_id=".$POST['vender_id']." left join unit_mst as u on u.unitid=popro.product_base_unit where product_id=".$POST['eid'];
			$result=$dbcon->query($qry);
			$row=mysqli_fetch_assoc($result);

			// /** 
			// 	Code By Umair: 02/1/2021
			// 	Comment: Get Item Rate At Bill Time. First we are getting the rate from the tbl_purchaseordertrn, if not exist then we are checking the tbl_product_party_purchase table later we are getting the that particular party rate.
			// */
			$item_rate = get_po_card_rate($dbcon, $POST['eid'], $POST['vender_id'],"");
			$row['item_rate'] = $item_rate;
			
				//check item for tds start

			$getCompanyConfig = getCompanyConfiguration($dbcon);

			if($getCompanyConfig['enable_tds_reporting'] == 1)
			{
				$po_id = $POST['po_id'];
				$product_type = get_id_detail($dbcon,'product_mst','product_id',$POST['eid'],'product_type');
				if($product_type==6 || $product_type==8)
				{
					$q = "select trn.product_id,p.product_type from tbl_potrancation as trn left join product_mst as p on p.product_id=trn.product_id where trn.potrancation_status!='2' and po_id='$POST[po_id]' and p.product_type not in (6,8)";
					$query_pr = $dbcon->query($q);

					if(brp_mysqli_num_rows($query_pr)>0)
					{
						$row['status']=0;		
					}
					else
					{
						$row['status']=1;
					}
				}
				else
				{
					$q = "select trn.product_id,p.product_type from tbl_potrancation as trn left join product_mst as p on p.product_id=trn.product_id where trn.potrancation_status!='2' and po_id='$POST[po_id]' and p.product_type in (6,8)";
					$query_pr = $dbcon->query($q);

						if(brp_mysqli_num_rows($query_pr)>0)
						{
							$row['status']=0;
						}
						else
						{
							$row['status']=1;
						}
				}

			}
			else
			{
				$row['status']=1;
			}

			//check item for tds end
			//echo $q;exit;
			echo json_encode($row);
		
		}
		else if(strtolower($POST['mode'])== "get_ledger_details")
		{
			
			$ledger_id=$POST['ledger_id'];
			
			$row=get_ledger_details($dbcon,$ledger_id);
			
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
		
	else if(strtolower($POST['mode'])== "get_bill_sundry_details")
	{
		$invoice_id=$POST['invoice_id'];
		//echo '<pre>'; print_r($POST);exit;
		$q = $dbcon -> query("SELECT * from tbl_ledger_bill_sundry where sundry_ledger_id=".$POST['sundry_ledger_id']." and company_id = ".$_SESSION['company_id']." and isdelete=0 ");
		$resp = $q->fetch_assoc();
		
		$q_tax = $dbcon -> query("select tax_gst from tbl_tax_category where tax_cat_id=".$resp['sundry_gst']." ");
		$resp_tax = $q_tax->fetch_assoc();

		$basic_total = $POST['basic_amount'];
		$netamount = $POST['netamount'];
		$taxableamount = $POST['taxableamount'];
		
		$default_amount = $POST['default_amount'];

		if($POST['sales_type']=="3"){
			$resp_tax['tax_gst']=0.1;
		}else if($POST['sales_type']=="4"){
			$resp_tax['tax_gst']=0;
		}else if($POST['sales_type']=="5"){
			$resp_tax['tax_gst']=5;
		}else if($POST['sales_type']=="6"){
			$resp_tax['tax_gst']=12;
		}else if($POST['sales_type']=="7"){
			$resp_tax['tax_gst']=18;
		}else if($POST['sales_type']=="8"){
			$resp_tax['tax_gst']=24;
		}
		
		if(($resp['apply_gst'] == 2) && (!empty($resp['sundry_gst']))){
			if($resp['sundry_amount_of'] == 2){
                $taxvl = ($resp_tax['tax_gst']*(($basic_total * $default_amount)/100))/100;
            }else{
                $taxvl = ($resp_tax['tax_gst']*$POST['default_amount'])/100;
            }
			//$taxvl = ($resp_tax['tax_gst']*$POST['default_amount'])/100;
			$taxgst=$resp_tax['tax_gst'];
		}else{
			$taxvl=0;
			$taxgst=0;
		}
		
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
			$info_sundry_addon['sundry_voucher_id']=$invoice_id;
			$info_sundry_addon['sundry_voucher_type']=PURCHASE_VOUCHER;
			$info_sundry_addon['sundry_voucher_table']='tbl_pono';
			$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
			$info_sundry_addon['user_id']	= $_SESSION['user_id'];
			$info_sundry_addon['company_id']	= $_SESSION['company_id'];
			
			$info_sundry_addon['sundry_gst_per']	= $taxgst;
			//$info_sundry_addon['sundry_amount']=$pervalue;
			//$info_sundry_addon['sundry_gst_amount']	= $taxvl;
			//print_r(array_merge($info_sundry_addon,$curncy_trn));
			
			if(isset($POST['currency_enable'])){
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }

            if($POST['currency_id']==$_SESSION['currency_id']){
				$info_sundry_addon['sundry_amount']=$pervalue;
				$info_sundry_addon['sundry_gst_amount']	= $taxvl;
				$info_sundry_addon['sundry_amount_conv']=$pervalue*$POST['currency_rate'];
				$info_sundry_addon['sundry_gst_amount_conv']= $taxvl*$POST['currency_rate'];
			}else{
				$info_sundry_addon['sundry_amount']=$pervalue*$POST['currency_rate'];
				$info_sundry_addon['sundry_gst_amount']	= $taxvl*$POST['currency_rate'];
				$info_sundry_addon['sundry_amount_conv']=$pervalue;
				$info_sundry_addon['sundry_gst_amount_conv']= $taxvl;
			}
			
			$sundry_addon_insert=add_record('tbl_bill_sundry_transaction',array_merge($info_sundry_addon,$curncy_trn), $dbcon);
			
			//general book entry 
			$invoice_date = date("Y-m-d",strtotime($POST['invoice_date']));

			if($pervalue < 0){
				$ledger_entry_type = 1;
				$cust_entry_type = 2; 
			}else{
				$ledger_entry_type = 2;
				$cust_entry_type = 1;
			}
			
			$info_general_addon['ledger_id']=$POST['sundry_ledger_id'];
			
			if($POST['currency_id']==$_SESSION['currency_id']){
				$info_general_addon['amount']=abs($pervalue);
			}else{
				$info_general_addon['amount']=abs($pervalue*$POST['currency_rate']);
			}
			
			$info_general_addon['table_id']=$sundry_addon_insert;
			$info_general_addon['entry_type']=$ledger_entry_type;
			$info_general_addon['table_name']='tbl_bill_sundry_transaction';
			$info_general_addon['ref_date']=$invoice_date;
			$info_general_addon['cdate']	= date("Y-m-d H:i:s");
			$info_general_addon['user_id']	= $_SESSION['user_id'];
			$info_general_addon['company_id']	= $_SESSION['company_id'];
			
			add_record('tbl_general_book',array_merge($info_general_addon,$curncy_trn), $dbcon);

			$info_gen2['table_name']	= 'tbl_pono';
			$info_gen2['table_id']		= $invoice_id;
			$info_gen2['entry_type']	= $cust_entry_type;
			$info_gen2['ref_date']		= date('Y-m-d',strtotime($invoice_date));
			$info_gen2['ledger_id']		= $POST['vender_id'];
			$info_gen2['amount']		= abs($pervalue);
			$info_gen2['user_id']		= $_SESSION['user_id'];
			$info_gen2['cdate']			= date("Y-m-d H:i:s");
			$info_gen2['company_id']	= $_SESSION['company_id'];
			$info_gen2['ref_by'] = 'tbl_addon_bill_sundry';
						
			//$inserid_gen2=add_record("tbl_general_book", array_merge($info_gen2,$curncy_trn) , $dbcon);

			//add_general_book_entry($dbcon,"tbl_pono",$invoice_id,$cust_entry_type,$POST['vender_id'],abs($pervalue),'',$invoice_date,'',$curncy_trn);
		}
		//if invoice is edit time insert data in database end - dhaval
		if($resp['sundry_amount_of'] == 1){
			
			$per_amount_show="";
			
		}
		else{
			
			$per_amount_show= '<strong> ('.$default_amount.'%)</strong>';
		}

		$pervalue = round($pervalue,2);
		echo json_encode($finalNetAmount.','.$pervalue.','.$per_amount_show.','.$invoice_id.','.$taxvl.','.$resp_tax['tax_gst']);

		// echo $finalNetAmount."<br>".$pervalue."<br>".$per_amount_show."<br>".$invoice_id."<br>".$taxvl."<br>".$resp_tax['tax_gst']."<br>";
	}
	
		
		else if(strtolower($POST['mode'])== "get_invoice_total_tax")
		{
			$invoice_id=0;$where="";
			if($POST['invoice_id']){
				$invoice_id=$POST['invoice_id'];
			}else{
				$where .="and user_id=".$_SESSION['user_id'];
			}
			
			$resp='';
			$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate,sum(product_amount) as product_amount,sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_potrancation` where po_id='$invoice_id' and potrancation_status!=2 ".$where;
				
			$rs_prel= brp_mysqli_fetch_assoc($dbcon->query($query));

			$query_inv="SELECT cgst,sgst,igst,tds,cgst_conv,sgst_conv,igst_conv from tbl_pono where po_id='$invoice_id' ";
			$rs_prel_inv= brp_mysqli_fetch_assoc($dbcon->query($query_inv));

			$row['isTds']="0";
			$getCompanyConfig = getCompanyConfiguration($dbcon);
			$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);	
			$company_state = get_company_data($dbcon,$_SESSION['company_id']);	
			$get_bill_sundry = get_bill_sundry_ledger($dbcon,1); 

			if($getCompanyConfig['tax_editable'] == 1){
				$readonly=" onChange='update_netbalance()'";
			}else{
				$readonly="readonly";
			}

			if($POST['salestype'] == 2){
				if($custLedgerDetails['stateid'] == $company_state['stateid']){
					$rs_prel['cgst_rate'] = ($rs_prel['product_amount']*(0.05)/100);
					$rs_prel['sgst_rate'] = ($rs_prel['product_amount']*(0.05)/100);
				}else{
					$rs_prel['igst_rate'] = ($rs_prel['product_amount']*(0.1)/100);
				}
				
			}

		if($getCompanyConfig['tax_editable'] == 1 && $invoice_id != '' ){
			if(($rs_prel_inv['cgst'] != 0) || ($custLedgerDetails['enable_sez']==0 && $custLedgerDetails['stateid'] == $company_state['stateid']) ){
					$resp.='<div class="form-group">
							<label class="col-md-5 control-label">CGST <span class="currency_icon"></span></label>
							<div class="col-md-5 col-xs-11">
								<input id="CGST" name="bill_sundry_tax[9870]" type="number" class="form-control gst" title="CGST"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? $rs_prel_inv['cgst'] : $rs_prel_inv['cgst_conv']).'" placeholder="CGST" '.$readonly.' >
							</div>
						</div>';
				}
			
			if(($rs_prel_inv['sgst'] != 0) || ($custLedgerDetails['enable_sez']==0 && $custLedgerDetails['stateid'] == $company_state['stateid']) ){
					$resp.='<div class="form-group">
							<label class="col-md-5 control-label">SGST <span class="currency_icon"></span></label>
							<div class="col-md-5 col-xs-11">
								<input id="SGST" name="bill_sundry_tax[9880]" type="number" class="form-control gst" title="SGST"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? $rs_prel_inv['sgst'] : $rs_prel_inv['sgst_conv']).'" placeholder="SGST" '.$readonly.' >
							</div>
						</div>';
				}
			
			if(($rs_prel_inv['igst'] != 0) || ($custLedgerDetails['enable_sez']==1 && $custLedgerDetails['stateid'] == $company_state['stateid']) ){
					$resp.='<div class="form-group">
							<label class="col-md-5 control-label">IGST <span class="currency_icon"></span></label>
							<div class="col-md-5 col-xs-11">
								<input id="IGST" name="bill_sundry_tax[9890]" type="number" class="form-control gst" title="IGST"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? $rs_prel_inv['igst'] : $rs_prel_inv['igst_conv']).'" placeholder="IGST" '.$readonly.' >
							</div>
						</div>';
				}
			
			if(($rs_prel_inv['tcs'] != 0) || ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($POST['gross'] >= $getCompanyConfig['gross_balance_limit'])){
					$resp.='<div class="form-group">
							<label class="col-md-5 control-label">TDS(ON PURCHASE OF GOODS) <span class="currency_icon"></span></label>
							<div class="col-md-5 col-xs-11">
								<input id="TDS" name="bill_sundry_tax[9895]" type="number" class="form-control gst" title="TDS"  value="'.$rs_prel_inv['tds'].'" placeholder="TDS" '.$readonly.' >
							</div>
						</div>';
				}
			

		}else{

			foreach ($get_bill_sundry as $billsundry) {
			
				if((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate']!= 0) && $billsundry['l_name'] == 'SGST')){

					if(!empty($POST['addontax1'])){
						$addontax = $POST['addontax1']/2;
					}
					$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');

					$gstValue_conv = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate_conv'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate_conv'] : '');

					$resp.='
					<div class="row  row_margin">
					<div class="form-group">
						<label class="col-md-5 control-label text-right">'.$billsundry['l_name'].' <span class="currency_icon"></span></label>
						<div class="col-md-5 col-xs-11">
							<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? round($gstValue+$addontax,2) : round($gstValue_conv+$addontax,2)).'" placeholder="'.$billsundry['l_name'].'" '.$readonly.' >
						</div>
					</div>
					</div>
					';
					
					
				}

				if(($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST'){
					if(!empty($POST['addontax1'])){
						$addontax = $POST['addontax1'];
					}
					$resp.='
					<div class="row row_margin">
					<div class="form-group">
						<label class="col-md-5 control-label text-right">'.$billsundry['l_name'].' <span class="currency_icon"></span></label>
						<div class="col-md-5 col-xs-11">
							<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? round($rs_prel['igst_rate']+$addontax,2) : round($rs_prel['igst_rate_conv']+$addontax,2)).'" placeholder="'.$billsundry['l_name'].'" '.$readonly.' >
						</div>
					</div>
					</div>
					';
				}
			
			}	

			//check whether Groos Payment aplly or not start
			// 6 - CAPITAL GOODS , 8 - SERVICE 
			$query_gross = $dbcon->query("select trn.product_id,p.product_type from tbl_potrancation as trn left join product_mst as p on p.product_id=trn.product_id where trn.po_id='$invoice_id' and trn.potrancation_status!='2' and p.product_type not in (6,8)  group by trn.product_id");
			if(brp_mysqli_num_rows($query_gross)>0)
			{
				
				/////Harshil Comment this line is any issue then please remove this comment and second if comment 
				//if(($billsundry['ledger_alias'] == 'TDS_PURCHASE_GROSS') && ($getCompanyConfig['enable_tds_reporting'] == 1) && $custLedgerDetails['enable_tds']==1)
				/////////////end////////////////////////////	
				if($getCompanyConfig['enable_tds_reporting'] == 1 && $custLedgerDetails['enable_tds']==1)
				{
					
					$row['isTds']="1";
					$tds_perecentage = get_tds_percentage($dbcon,$POST['cust_id'],'');
				
					if(($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST')
					{
						$total_tds_calculate = -($rs_prel['product_amount']);
						$total_tds_calculate_conv = -($rs_prel['product_amount_conv']+$rs_prel['igst_rate_conv']);
					}
					else
					{
						$total_tds_calculate = -($rs_prel['product_amount']);
						$total_tds_calculate_conv = -($rs_prel['product_amount_conv']+$rs_prel['cgst_rate_conv']+$rs_prel['sgst_rate_conv']);	
					}

					$resp.='<div class="form-group">
					<label class="col-md-5 control-label">TDS ('.$tds_perecentage.'%) <span class="currency_icon"></span></label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control" title="'.$billsundry['l_name'].'"  value="'.(($POST['currency_id']==$_SESSION['currency_id']) ? round((($total_tds_calculate*$tds_perecentage)/100),2) : round((($total_tds_calculate_conv*$tds_perecentage)/100),2)).'" placeholder="'.$billsundry['l_name'].'" '.$readonly.' >
						
						<input type="hidden" name="tds_amount" id="tds_amount" value="'.(($POST['currency_id']==$_SESSION['currency_id']) ? round((($total_tds_calculate*$tds_perecentage)/100),2) : round((($total_tds_calculate_conv*$tds_perecentage)/100),2)).'" >
						
						<input type="hidden" name="tds_per['.$billsundry['l_id'].']" id="tds_per" value="'.$tds_perecentage.'" >
					</div>
					</div>';
				}
			}
			//gross payment apply end

			//purchase product tds start
			
			if($getCompanyConfig['enable_tds_reporting'] == 1 && $custLedgerDetails['enable_tds']==1)
			{

				$query1=$dbcon->query("SELECT trn.product_id,sum(trn.product_amount) as product_total,p.product_type FROM `tbl_potrancation` as trn left join product_mst as p on p.product_id=trn.product_id where trn.po_id='$invoice_id' and trn.potrancation_status!='2' and p.product_type in (6,8) group by trn.product_id");
				//$resp.=$query1;
				while($rs_pre2=brp_mysqli_fetch_assoc($query1))
				{
					$tds_details=get_product_tds($dbcon,$rs_pre2['product_id'],$POST['cust_id']);
					
					if($tds_details!=0)
					{
						
						$customer_pan = get_id_detail($dbcon,'tbl_ledger','l_id',$POST['cust_id'],'m_pan');
						if($customer_pan=='' || $customer_pan=='0')
						{
							$tds_charge = $tds_details['tds_without_pan'];
						}
						else
						{
							$tds_charge = $tds_details['tds_with_pan'];
						}

						if(($rs_prel['igst_rate'] != 0)){
							$tds_total = -(($rs_pre2['product_total'])*$tds_charge)/100;
							$tds_total_conv = -(($rs_pre2['product_total_conv']+$rs_prel['igst_rate_conv'])*$tds_charge)/100;
						}
						else
						{
							$tds_total = -(($rs_pre2['product_total'])*$tds_charge)/100;
							$tds_total_conv = -(($rs_pre2['product_total_conv']+$rs_prel['cgst_rate_conv']+$rs_prel['sgst_rate_conv'])*$tds_charge)/100;
						}

						$resp.='<div class="form-group">
							<label class="col-md-5 control-label">TDS - '.$tds_details['tds_cat_name'].'('.$tds_charge.'%)'.' <span class="currency_icon"></span></label>
							<div class="col-md-5 col-xs-11">
								
								<input id="'.$tds_details['effected_ledger_id'].'" name="bill_sundry_tax['.$tds_details['effected_ledger_id'].']" type="number" class="form-control gst" title="'.$tds_details['tds_cat_name'].'"  value="'.(($POST['currency_id']==$_SESSION['currency_id']) ? $tds_total : $tds_total_conv).'" placeholder="'.$tds_details['tds_cat_name'].'" '.$readonly.' >

								<input type="hidden" name="tds_amount1" id="tds_amount1" value="'.(($POST['currency_id']==$_SESSION['currency_id']) ? $tds_total : $tds_total_conv).'" >
								
								<input type="hidden" class="tds_per" name="tds_per['.$tds_details['effected_ledger_id'].']" id="tds_per1" value="'.$tds_charge.'" >
							</div>
						</div>';
					}
				}
			}
			//purchase product tds end
		}
			
			
			$qry_add = $dbcon->query("select sum((tc.tax_per*trn.product_amount)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_potrancation as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
			left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
			left join tbl_ledger as l on l.l_id=tc.tax_id
			where tc.tax_additional='1' and trn.po_id='$invoice_id' and trn.potrancation_status!=2 and tc.isdelete='0' ".$where." group by tc.tax_id 
			");
			while($row1=brp_mysqli_fetch_array($qry_add))
			{
				
				//$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;
				
				
				$resp.='<div class="form-group">
						<label class="col-md-5 control-label">'.$row1['l_name'].'<span class="currency_icon"></span> </label>
						<div class="col-md-5 col-xs-11">
							<input id="'.$row1['l_name'].'" name="bill_sundry_tax['.$row1['l_id'].']" type="number" class="form-control gst" title="'.$row1['l_name'].'"  value="'.(($POST['currency_id']==$company_state['currency_id']) ? $row1['add_sum'] : $row1['add_sum_conv']).'" placeholder="'.$billsundry['l_name'].'" readonly >
						</div>
					</div>';
			}

			$row['resp']=$resp;
			
			echo json_encode($row);
		}
	

	else if(strtolower($POST['mode'])== "remove_sundry"){
		
		$ledger_id = $POST['ledger_id'];
		$invoice_id = $POST['edit_id'];
		$vender_ledger_id = $POST['vender_ledger_id'];

		$info['isdelete']=1;
		
		$updateid=update_record('tbl_bill_sundry_transaction', $info,"sundry_id=".$POST['ledger_id'] , $dbcon);
		
		$info_general['genral_book_status'] = 2;

		$q = $dbcon -> query("SELECT amount from tbl_general_book where table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction' ");
		$resp = $q->fetch_assoc();
		
		$update_gen_cusid=update_record('tbl_general_book', $info_general,"table_id=".$invoice_id." and ledger_id=".$vender_ledger_id." and amount=".$resp['amount']." and ref_by='tbl_addon_bill_sundry' and  table_name='tbl_pono'" , $dbcon);

		$updateid1=update_record('tbl_general_book', $info_general,"table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction'" , $dbcon);
		
	}
	
	else if(strtolower($POST['mode'])== "get_all_bill_sundry")
	{
		$invoice_id=$POST['invoice_id'];
		
		$q=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id,le.ledger_Tax_type from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_pono' and b.isdelete='0' and le.default_sundry='0' ");
		
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

			if(empty($r['sundry_gst_per'])){
				$sundry_amount = ($r['currency_id']==$_SESSION['currency_id']) ? $r['sundry_amount'] : $r['sundry_amount_conv'];
				$str.='<div class="form-group">
						<label class="col-md-5 control-label">'.$r['l_name'].' <span class="currency_icon"></span></label>
						<div class="col-md-4">
							<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$sundry_amount.'">
							<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$sundry_amount.'" readonly placeholder="Amount">
						</div>
						<div class="col-md-3">
							<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
								type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$sundry_amount.'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
						</div>
					</div>';
			}else{
				$sundry_amount = ($r['currency_id']==$_SESSION['currency_id']) ? $r['sundry_amount'] : $r['sundry_amount_conv'];
				$sundry_gst_amount = ($r['currency_id']==$_SESSION['currency_id']) ? $r['sundry_gst_amount'] : $r['sundry_gst_amount_conv'];

				$str.='<div class="form-group">
						<label class="col-md-5 control-label">'.$r['l_name'].' <span class="currency_icon"></span></label>
						<div class="col-md-4">
							<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$sundry_amount.'">
							<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$sundry_amount.'" readonly placeholder="Amount">
							<input class="addontax" name="bill_sundry_addon_tax['.$r['l_id'].']" type="hidden" value="'.$sundry_gst_amount.'-'.$r['sundry_gst_per'].'-'.$sundry_amount.'" >
						</div>
						<div class="col-md-3">
							<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
								type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$sundry_amount.'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
						</div>
					</div>';
			}
			
			$cnt++;
			//$str.=$r['sundry_amount'];
		}
		
		echo $str;
		//echo json_encode($resp);
	}
		
		else if(strtolower($POST['mode'])== "get_tax_details_table")
		{
			$invoice_id = 0;
			if($POST['invoice_id']){
				$invoice_id=$POST['invoice_id'];
			}else{
				$where .="and user_id=".$_SESSION['user_id'];
			}
			
			$resp='';
			$query="SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,product_amount, sum(product_amount_conv) as product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_potrancation` where po_id='$invoice_id' and potrancation_status!=2 ".$where." group by cgst_tax_per,sgst_tax_per,igst_tax_per";
			
			$rs_prel=$dbcon->query($query);
			$rs_prel_fetch=brp_mysqli_fetch_assoc($dbcon->query("SELECT cgst_tax_per,sum(cgst_tax_rate) as cgst_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_rate,igst_tax_per,sum(igst_tax_rate) as igst_rate,product_amount, product_amount_conv, sum(cgst_tax_rate_conv) as cgst_rate_conv,sum(sgst_tax_rate_conv) as sgst_rate_conv,sum(igst_tax_rate_conv) as igst_rate_conv FROM `tbl_potrancation` where po_id='$invoice_id' and potrancation_status!=2 ".$where));
			
			$rs_prel_num_rows=mysqli_num_rows($rs_prel);
			//print_r($rs_prel_fetch);exit;
			$resp='';
			if($POST['salestype'] == 1){
				$resp .= '<table class="table table-bordered">
															
								<tr>
									<th class="text-center">#</th>
									<th  class="text-center">Total Tax </th>
									<th  class="text-center">Taxable Amount <span class="currency_icon"></span></th>
									<th  class="text-center">Tax Amount <span class="currency_icon"></span></th>';
				if(($rs_prel_fetch['cgst_rate']!=0) || ($rs_prel_fetch['sgst_rate']!=0)){
					$resp .='<th  class="text-center">CGST</th>
							<th  class="text-center">SGST</th>';
				}if(($rs_prel_fetch['igst_rate']!=0)){
					$resp .= '<th  class="text-center">IGST</th>';
				}
									
									
				$resp .='</tr>';
			}

			$cnt=1;
			$cntloop=0;	
			if($rs_prel_num_rows > 0 && $POST['salestype'] == 1){
				$taxRate = brp_mysqli_fetch_all($rs_prel);
				//print_r($taxRate);exit;
				foreach($taxRate as $taxdetail) {
					$gst_tax_per = ($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0) ? ($taxdetail['cgst_tax_per']+$taxdetail['sgst_tax_per']) : $taxdetail['igst_tax_per'];
					$gst_tax_rate = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate']+$taxdetail['sgst_rate']) : $taxdetail['igst_rate'];
					$gst_tax_rate_conv = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate_conv']+$taxdetail['sgst_rate_conv']) : $taxdetail['igst_rate_conv'];

					if($taxdetail['cgst_tax_per'] != 0 || $taxdetail['sgst_tax_per'] != 0){
						$resp.='<tr>
								<th class="text-center">'.$cnt.'</th>
								<th class="text-center">'.$gst_tax_per.'%'.'</th>
								<th class="text-center">';
							if($POST['currency_id']==$_SESSION['currency_id']){
								$resp.=$taxdetail['product_amount'].'</th>
								<th class="text-center">'.$gst_tax_rate;
							}else{
								$resp.=$taxdetail['product_amount_conv'].'</th>
								<th class="text-center">'.$gst_tax_rate_conv;
							}
						$resp .='</th>
								<th class="text-center">'.($taxdetail['cgst_tax_per']).'%'.'</th>
								<th class="text-center">'.($taxdetail['sgst_tax_per']).'%'.'</th>
							</tr>';
						if(!empty($POST['addontax1']) && $cntloop==0){
							foreach($POST['addontax1'] as $addtax){
								$cnt++;
								$exp_addtax = explode("-",$addtax);
								if($exp_addtax[1] != 0){
									$resp.='<tr>
										<th class="text-center">'.$cnt.'</th>
										<th class="text-center">'.$exp_addtax[1].'%'.'</th>
										<th class="text-center">'.($exp_addtax[2]).'</th>
										<th class="text-center">'.$exp_addtax[0].'</th>
										<th class="text-center">'.($exp_addtax[1]/2).'%'.'</th>
										<th class="text-center">'.($exp_addtax[1]/2).'%'.'</th>
									</tr>';
								}
							}
							$cntloop=1;
						}
					}

					if($taxdetail['igst_tax_per'] != 0){
						$resp.='<tr>
								<th class="text-center">'.$cnt.'</th>
								<th class="text-center">'.$gst_tax_per.'%'.'</th>
								<th class="text-center">';
							if($POST['currency_id']==$_SESSION['currency_id']){
								$resp.=$taxdetail['product_amount'].'</th>
								<th class="text-center">'.$gst_tax_rate;
							}else{
								$resp.=$taxdetail['product_amount_conv'].'</th>
								<th class="text-center">'.$gst_tax_rate_conv;
							}
						$resp.='</th>
								<th class="text-center">'.($taxdetail['igst_tax_per']).'%'.'</th>
							</tr>';
						if(!empty($POST['addontax1']) && $cntloop==0){
							foreach($POST['addontax1'] as $addtax){
								$cnt++;
								$exp_addtax = explode("-",$addtax);
								//echo '<pre>';print_r($exp_addtax);
								if($exp_addtax[1] != 0){
									$resp.='<tr>
										<th class="text-center">'.$cnt.'</th>
										<th class="text-center">'.$exp_addtax[1].'%'.'</th>
										<th class="text-center">'.($exp_addtax[2]).'</th>
										<th class="text-center">'.$exp_addtax[0].'</th>
										<th class="text-center">'.($exp_addtax[1]).'%'.'</th>
									</tr>';
								}
							}
							$cntloop=1;
						}
					}			
					$cnt++;			
				}

			}
			
			$resp.='</table>';
			
			$row['resp']=$resp;
			
			echo json_encode($row);
		}

		
		else if(strtolower($POST['mode']) == "fieldadd") {


			if(isset($POST['currency_enable']) && $POST['currency_enable']==1){
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }
			
			$company_state = get_company_data($dbcon,$_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
			/*$sale_gst = get_tax_cat_by_hsn($dbcon,trim($_POST['product_hsn_code']));*/
			$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);

			if($POST['sales_type']==1){
				$sale_gst = get_tax_cat_by_hsn($dbcon,trim($_POST['product_hsn_code']));
			}else if($POST['sales_type']==2){
				$sale_gst['tax_gst']=0.1;
				$sale_gst['tax_cat_id']=0;
			}else if($POST['sales_type']==3){
				$sale_gst['tax_gst']=0;
				$sale_gst['tax_cat_id']=0;
			}else if($POST['sales_type']==4){
				$sale_gst['tax_gst']=5;
				$sale_gst['tax_cat_id']=0;
			}else if($POST['sales_type']==5){
				$sale_gst['tax_gst']=0;
				$sale_gst['tax_cat_id']=0;
			}else if($POST['sales_type']==6){
				$sale_gst['tax_gst']=12;
				$sale_gst['tax_cat_id']=0;
			}else if($POST['sales_type']==7){
				$sale_gst['tax_gst']=18;
				$sale_gst['tax_cat_id']=0;
			}else if($POST['sales_type']==8){
				$sale_gst['tax_gst']=24;
				$sale_gst['tax_cat_id']=0;
			}

			$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
			$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
			$igst_tax_rate=0;$igst_tax_rate_conv=0;
			//echo $company_state['stateid']."--".$POST['cust_stateid'];exit;
			if(($company_state['stateid'] == $POST['cust_stateid'])){
				$gst = $sale_gst['tax_gst']/2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
				$cgst_tax_rate_conv = ($POST['currency_rate'] * $gst*$POST['product_amount'])/100;
				$sgst_tax_rate_conv = ($POST['currency_rate'] * $gst*$POST['product_amount'])/100;
			}else{
				$igst_tax_per = $sale_gst['tax_gst'];
				$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
				$igst_tax_rate_conv = ($POST['currency_rate'] * $sale_gst['tax_gst']*$POST['product_amount'])/100;
			}
			
				
			//$info1['grn_id']				= $POST['grn_id'];
			$info1['product_id']			= $POST['product_id'];
			$info1['description']			= $_POST['product_des'];
			
			$info1['product_des']			= $_POST['pro_des'];
			$info1['pro_spe']				= $_POST['pro_spe'];
				
			$info1['product_qty']			= $POST['product_qty'];
			$info1['product_conv_qty']		= $POST['product_conv_qty'];

			$info1['unit_id']				= $POST['unit_id'];
			$info1['conv_unit_id']			= $POST['conv_unitid'];
			$info1['rate_unit']				= $POST['rate_unitid'];

			$info1['product_hsn_code']		= $POST['product_hsn_code'];
			$info1['unit_id']				= $POST['unit_id'];
			//$info1['product_rate']			= $POST['product_rate'];
			$info1['purchasecardtrn_id']	= $POST['purchasecardtrn_id'];
			//$info1['product_discount']		= $POST['product_discount'];
			$info1['discount_per']			= $POST['discount_per'];
			//$info1['product_amount']		= $POST['product_amount'];
			//$info1['total']					= $POST['product_amount'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['user_id']				= $_SESSION['user_id'];
			
			$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
			$info1['cgst_tax_per']		= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
			$info1['sgst_tax_per']		= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
			$info1['igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;

			if($POST['currency_id']==$company_state['currency_id']){
				$info1['product_rate']			= $POST['product_rate'];
				$info1['product_discount']		= $POST['product_discount'];
				$info1['product_amount']		= $POST['product_amount'];
				
				$info1['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				$info1['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				$info1['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
				$info1['taxable_value']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
				$info1['total'] = $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $POST['product_amount'];
				$info1['product_rate_conv']			= $POST['product_rate']*$POST['currency_rate'];
				$info1['product_discount_conv']		= $POST['product_discount']*$POST['currency_rate'];
				$info1['product_amount_conv']		= $POST['product_amount']*$POST['currency_rate'];
				$info1['cgst_tax_rate_conv']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
				$info1['sgst_tax_rate_conv']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
				$info1['igst_tax_rate_conv']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
				$info1['taxable_value_conv']		= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
				$info1['total_conv'] = $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv + $info1['product_amount_conv'];
			}else{
				$info1['product_rate']			= $POST['product_rate']*$POST['currency_rate'];
				$info1['product_discount']		= $POST['product_discount']*$POST['currency_rate'];
				$info1['product_amount']		= $POST['product_amount']*$POST['currency_rate'];
	
				$info1['cgst_tax_rate']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
				$info1['sgst_tax_rate']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
				$info1['igst_tax_rate']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
				$info1['taxable_value']		= $cgst_tax_rate_conv + $sgst_tax_rate_conv + $igst_tax_rate_conv;
				$info1['total'] = $cgst_tax_rate_conv + $cgst_tax_rate_conv + $igst_tax_rate_conv + $info1['product_amount'];
				$info1['product_rate_conv']			= $POST['product_rate'];
				$info1['product_discount_conv']		= $POST['product_discount'];
				$info1['product_amount_conv']		= $POST['product_amount'];
				$info1['cgst_tax_rate_conv']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				$info1['sgst_tax_rate_conv']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				$info1['igst_tax_rate_conv']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
				$info1['taxable_value_conv']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
				$info1['total_conv'] = $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $POST['product_amount'];
			}
			

			$table='tbl_potrancation';$tableid='potrancation_id';	
			if(!empty($POST['po_id'])) {
				$info1['po_id'] = $POST['po_id'];
			}
			else {
				$info1['potrancation_status'] = 3;
			}
			
			if(empty($POST['edit_id'])) {
				//$inserid=add_record($table, $info1, $dbcon);
				$inserid=add_record($table, array_merge($info1,$curncy_trn), $dbcon, $POST['branch_id']);

				//add general book entry 
				if(!empty($POST['po_id'])) {

					$sel_gen = $dbcon->query("select trn.*,p.product_type,p.ledger_id,po.po_date from tbl_potrancation as trn 
        			left join product_mst as p on trn.product_id=p.product_id
        			left join tbl_pono as po on po.po_id = trn.po_id
        			where trn.potrancation_id='$inserid'");

        			$r_gen=brp_mysqli_fetch_assoc($sel_gen);
        			$module_name=MODULE_PURCHASE;
        			$module_id=$POST['po_id'];
        			add_general_book_entry($dbcon,"tbl_potrancation",$r_gen['potrancation_id'],2,$r_gen['ledger_id'],$POST['product_amount'],'',$r_gen['po_date'],'',$curncy_trn,$module_name,$module_id); 
				
				}
			}
			else {
				//$inserid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				$inserid=update_record($table, array_merge($info1,$curncy_trn),$tableid."=".$POST['edit_id'] , $dbcon, $POST['branch_id']);	

				$stock_detail = get_find_stock_detail($dbcon,$POST['edit_id'],$POST['rate_unitid'],$POST['unit_id'],$POST['product_rate']);
				 
				
				$sel_edit = $dbcon->query("select general_book_id from tbl_general_book where table_name='tbl_potrancation' and table_id='$POST[edit_id]'");
				$r_edit = brp_mysqli_fetch_assoc($sel_edit);

				$info_gen1['amount'] = $POST['product_amount'];

				update_record("tbl_general_book",$info_gen1," table_id=".$POST['edit_id']."  and table_name='tbl_potrancation'" ,$dbcon);		
				
				//$inserid=$POST['edit_id'];
			}
			
			/* insert to tax transaction table by dhaval */
			if( ($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'CGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_potrancation",$POST['product_id'],3,$POST['edit_id'],$POST['branch_id'],$POST['currency_id'],$POST['currency_rate'],$cgst_tax_rate_conv);
			}
			if( ($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'SGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_potrancation",$POST['product_id'],3,$POST['edit_id'],$POST['branch_id'],$POST['currency_id'],$POST['currency_rate'],$sgst_tax_rate_conv);
			}
			if( ($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
				$cl_id = get_ledger_by_name($dbcon,'IGST');
				$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_potrancation",$POST['product_id'],3,$POST['edit_id'],$POST['branch_id'],$POST['currency_id'],$POST['currency_rate'],$igst_tax_rate_conv);
			}

		}
		else if(strtolower($POST['mode']) == "load_rate") {
			//var_dump($_POST);
			$rate = get_po_card_rate($dbcon,$_POST['product_id'],$_POST['vender_id'],$_POST['unit_id']);

			$row['rate'] = $rate['price'];
			$row['purchasecardtrn_id']   = $rate['purchasecardtrn_id'];
			$row['discount_percentage']  = $rate['discount_percentage']; 
			//var_dump($row);
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			if($POST['po_id']){
				$query="select trn.*,product.product_name,grn.purchaseorder_id,po.purchaseorder_no,product.product_type,cat.unit_name as rat_unit,tc.cat_name,grn.grn_no,ctrn.price,ctrn.rate_tolerance,ctrn.discount_percentage,buni.unit_name as base_unit,cuni.unit_name as conv_unit from tbl_potrancation as trn
				   	left join unit_mst as cat on cat.unitid=trn.rate_unit 
				   	left join unit_mst as buni on buni.unitid = trn.unit_id
					left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
					left join product_mst as product on product.product_id=trn.product_id 
					left join tbl_category as tc on product.product_category=tc.cat_id 
					left join tbl_grn as grn on grn.grn_id=trn.grn_id 
					left join tbl_purchaseorder as po on po.purchaseorder_id = grn.purchaseorder_id
					left join tbl_purchasecardtrn as ctrn on ctrn.purchasecardtrn_id=trn.purchasecardtrn_id
					where trn.potrancation_status=0 and trn.po_id=".$POST['po_id'];
			}
			else{
			  	$query="select trn.*,product.product_name,grn.purchaseorder_id,po.purchaseorder_no,product.product_type,cat.unit_name as rat_unit,tc.cat_name,grn.grn_no,ctrn.price,ctrn.rate_tolerance,ctrn.discount_percentage,buni.unit_name as base_unit,cuni.unit_name as conv_unit from tbl_potrancation as trn
			  	left join unit_mst as cat on cat.unitid=trn.rate_unit 
		  		left join unit_mst as buni on buni.unitid = trn.unit_id
				left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
				left join product_mst as product on product.product_id=trn.product_id  
				left join tbl_category as tc on product.product_category=tc.cat_id 
				left join tbl_grn as grn on grn.grn_id=trn.grn_id 
				left join tbl_purchaseorder as po on po.purchaseorder_id = grn.purchaseorder_id
				left join tbl_purchasecardtrn as ctrn on ctrn.purchasecardtrn_id=trn.purchasecardtrn_id
				where trn.potrancation_status=3 and trn.user_id=".$_SESSION['user_id'];
			}
			//echo $query;
			$result=$dbcon->query($query);
			$curr=getcurrencydetail($dbcon,$POST['currency_id']);
			echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="20%">Product Name</th>
							<th class="text-center" width="6%">Qty</th>
							<th class="text-center" width="6%">Rate ('.$curr['currency_symbol'].')</th>
							<th class="text-center" width="6%">Discount  ('.$curr['currency_symbol'].')</th>
							<th class="text-center" width="8%">Tax Details ('.$curr['currency_symbol'].')</th>
							<th class="text-center" width="9%">Amount ('.$curr['currency_symbol'].')</th>
						 	<th class="text-center" width="5%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			$purchase_account_amount=0;
			while($rel=mysqli_fetch_assoc($result))
			{
				if(!empty($rel['currency_id'])){
					$currency=getcurrencydetail($dbcon,$rel['currency_id']);
				}else{
					$currency=getcurrencydetail($dbcon,$_SESSION['currency_id']);
				}
				$cgst_tax="";				
				$sgst_tax="";				
				$igst_tax="";	
				$product_qty = '';
						
				if($rel['unit_id']===$rel['rate_unit']){
					$sqty=$rel['product_qty'];
				}else{
					$sqty=$rel['product_conv_qty'];
				}

				if($rel['unit_id'] != $rel['conv_unit_id']){
					$qty_lb = '<strong style="color:green;">Base Qty</strong> :'.number_format($rel['product_qty'],4,'.','').' '.$rel['base_unit'].'<br><strong style="color:green;">Conv. Qty</strong> :'.number_format($rel['product_conv_qty'],4,'.','').' '.$rel['conv_unit']; 
				}else{
					$qty_lb = '<strong style="color:green;">Base Qty</strong> :'.number_format($rel['product_qty'],4,'.','').' '.$rel['base_unit'];
				}
				
				if($rel['cgst_tax_per']!=0)
				{
					$cgst_tax="<Strong>CGST (".$rel['cgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['cgst_tax_rate'] : $rel['cgst_tax_rate_conv']).'<br>';
				}

				if($rel['sgst_tax_per']!=0)
				{
					$sgst_tax="<Strong>SGST (".$rel['sgst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['sgst_tax_rate'] : $rel['sgst_tax_rate_conv']).'<br>';
				}

				if($rel['igst_tax_per']!=0)
				{
					$igst_tax="<Strong>IGST (".$rel['igst_tax_per'].") : </strong>".$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['igst_tax_rate'] : $rel['igst_tax_rate_conv']).'<br>';
				}
				
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
			if($rel['grn_id']!=0){ $grn='<br><strong style="color:red">GRN : </strong><strong style="color:green">'.$rel['grn_no'].'</strong>';	} else { $grn=""; }
			if($rel['purchaseorder_no']!=""){ $po_no='<br><strong style="color:red">PO No : </strong><strong style="color:green">'.$rel['purchaseorder_no'].'</strong>';	} else { $po_no=""; }

				$over_tol = '';
				if($rel['price'] != ''){
					if($rel['product_rate']>$rel['price']){
						$tole_rate = ($rel['price']*$rel['rate_tolerance'])/100;
						$tol_rate  = $rel['price']+$tole_rate;
						if($rel['product_rate']>$tol_rate){
							$over_tol .= "<strong><span style='color:red'>Over Tolerance Rate</span></strong>";
						} 
					}	
				}

				$ove_disc = '';
				if($rel['discount_percentage'] != ''){
					if($rel['discount_percentage'] > $rel['discount_per']){
						$ove_disc = "<strong><span style='color:red'>Less Discount As Per Minimum Discount</span></strong>";
					}
				}
			 echo '<tr id="'.$id.'" >
					<td style="vertical-align:top;">
						'.$rel['product_name'].' (<strong style="color:green">'.$rel['product_hsn_code'].'</strong>)
						'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
						'.$grn.'
						'.$po_no.'
					</td>
					<td style="vertical-align:top;" class="text-left item_div item_qty_'.$i.'" data-usdrate="'.$rel['product_usd_rate'].'" data-qtnid="'.$rel['potrancation_id'].'">
						<strong style="color:green">Rate Qty</strong> :'.number_format($sqty,4,'.','').' '.$rel['rat_unit'].'<br> '.$qty_lb.'
					</td>
					<td style="vertical-align:top;" class="text-center">
						'.$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_rate'] : $rel['product_rate_conv']).'<br>'.$over_tol.'
					</td>
					
					<td style="vertical-align:top" class="text-center">
						'.$currency['currency_symbol']." ".(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_discount'] : $rel['product_discount_conv']).' ('.$rel['discount_per'].'%) '.$ove_disc.'
					</td>
					<td>
						'.$cgst_tax.'<br>'.$sgst_tax.'<br>'.$igst_tax.'
					</td>
					<td style="vertical-align:top" class="text-center">
						'.$currency['currency_symbol']." ".
						(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_amount'] : $rel['product_amount_conv']).'
					</td>
				<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_amount'] : $rel['product_amount_conv']).'"/>
											
					<td style="vertical-align:top">
						<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['potrancation_id'].');" ><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['potrancation_id'].');" ><i class="fa fa-times"></i></button>
					</td>	
			</tr>';
			if($rel['product_type']!="8"){
				$purchase_account_amount=$purchase_account_amount+(($rel['currency_id']==$_SESSION['currency_id']) ? $rel['product_amount'] : $rel['product_amount_conv']);
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
			$q = $dbcon -> query("SELECT mst.*,pro.product_name,unit.unit_name FROM tbl_potrancation as mst
			left join product_mst as pro on pro.product_id=mst.product_id
			left join unit_mst as unit on unit.unitid = mst.unit_id
			WHERE potrancation_id= '$POST[id]'");
			$r = $q->fetch_assoc();
			if($r['grn_id']){
				$r['producthtml'] = get_grn_trn_for_purchase($dbcon,$r['grn_id'],$r['product_id'],"Edit");
			}
			else{
				$r['producthtml'] = getrequiredproduct($dbcon,'','');
			}
			$r['product_qty_show']=number_format($r['product_qty'], 3, ".", "");
			$r['product_conv_qty_show']=number_format($r['product_conv_qty'], 3, ".", "");
			//var_dump($r);
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
			
			//update tax transaction table By Dhaval
			$info_tax['tx_status']=2;	
			$updatetax=update_record("tbl_tax_trn", $info_tax, "tx_transaction_type='tbl_potrancation' and tx_transaction_id=".$POST['eid'] , $dbcon);
			
			$query_ptr = "select grn_id,grn_trn_id,grn_sub_trn_id from tbl_potrancation where potrancation_id=".$POST['eid']; 
			$result_ptr = $dbcon->query($query_ptr);
			$row_ptr = brp_mysqli_fetch_array($result_ptr);


			if ($row_ptr['grn_id'] != 0 ) {
				$info_grn['purchase_status'] = 0;
				update_record('tbl_grn', $info_grn, "grn_id=".$row_ptr['grn_id'] , $dbcon);
				update_record('tbl_grn_trn', $info_grn, "grn_trn_id=".$row_ptr['grn_trn_id'] , $dbcon);
				update_record('tbl_grn_sub_trn', $info_grn, "grn_trn_sub_id=".$row_ptr['grn_sub_trn_id'] , $dbcon);
			}

			// Update po grn used status
			$info_used['po_grn_used_status'] =2;  		
    		update_record('tbl_po_grn_used', $info_used, "potrancation_id=".$POST['eid'] , $dbcon);
		
			// Update genral_book_status 
			$info_general_book['genral_book_status'] = 2;
			update_record('tbl_general_book', $info_general_book,"table_name='tbl_potrancation' and table_id=".$POST['eid'] , $dbcon);

			
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_data_temp_products") {
			$row=array();
			$sel_trn="select * from tbl_potrancation where potrancation_status=3 and user_id=".$_SESSION['user_id'];
			$rsu=$dbcon->query($sel_trn);
			while($sel=brp_mysqli_fetch_assoc($rsu)){

				$info['potrancation_status']=2;
				$updateid=update_record('tbl_potrancation', $info, "potrancation_id=".$sel['potrancation_id'] , $dbcon);
				
				//update tax transaction table By Dhaval
				$info_tax['tx_status']=2;	
				$updatetax=update_record("tbl_tax_trn", $info_tax, "tx_transaction_type='tbl_potrancation' and tx_transaction_id=".$sel['potrancation_id'] , $dbcon);
				
				$query_ptr = "select grn_id,grn_trn_id,grn_sub_trn_id from tbl_potrancation where potrancation_id=".$sel['potrancation_id']; 
				$result_ptr = $dbcon->query($query_ptr);
				$row_ptr = brp_mysqli_fetch_array($result_ptr);


				if ($row_ptr['grn_id'] != 0 ) {
					$info_grn['purchase_status'] = 0;
					update_record('tbl_grn', $info_grn, "grn_id=".$row_ptr['grn_id'] , $dbcon);
					update_record('tbl_grn_trn', $info_grn, "grn_trn_id=".$row_ptr['grn_trn_id'] , $dbcon);
					update_record('tbl_grn_sub_trn', $info_grn, "grn_trn_sub_id=".$row_ptr['grn_sub_trn_id'] , $dbcon);
				}

				// Update po grn used status
				$info_used['po_grn_used_status'] =2;  		
				update_record('tbl_po_grn_used', $info_used, "potrancation_id=".$sel['potrancation_id'] , $dbcon);
			
				// Update genral_book_status 
				$info_general_book['genral_book_status'] = 2;
				update_record('tbl_general_book', $info_general_book,"table_name='tbl_potrancation' and table_id=".$sel['potrancation_id'] , $dbcon);

			}
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
				$opt='<option  value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
        			$opt .='<option  value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
			}else{
				$row1['unit_status']="0";
				$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
			}
				//$row1['qye']=$query1;
				$row1['unit_option']=$opt;		
			echo json_encode($row1);
		}
		else if(strtolower($POST['mode']) == "load_purchase_srs_no") {
			
			$resp['po_no'] = load_purchase_srs_no($dbcon,$POST['invoicetype_id']);
			
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_ven_grn") {
			$resp['pro_html'] = get_grn_for_purchase($dbcon,$POST['vender_id'],$POST['id'],"Add");
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_service_bill") {
			$resp['pro_html'] = get_service_for_purchase($dbcon,$POST['vender_id'],$POST['id'],"Add");
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
			$grn_id = implode(",",$POST['grn_id']);
			$q = $dbcon -> query("SELECT trn.*,potrn.product_rate,potrn.product_des,potrn.product_des,trn.unit_id,unit.unit_name,(select IFNULL(sum(product_qty),0) as qty  from tbl_potrancation as chtrn where chtrn.potrancation_status!=2 and chtrn.grn_id=trn.grn_id and trn.product_id=chtrn.product_id) as used_qty from tbl_grn_trn as trn
			left join tbl_purchaseordertrn as potrn on potrn.purchaseorder_id=trn.purchaseorder_id and potrn.product_id=trn.product_id
			left join unit_mst as unit on unit.unitid=trn.unit_id
			where trn.grn_id in (".$grn_id.") and trn.grn_trn_status=0 and trn.product_id=".$POST['product_id']."");
			
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
			$resp='<table class="table table-bordered table-striped">
			<thead>
			<tr>
			<th class="text-center">PO No</th>
			<th class="text-center">Po Date</th>
			<th class="text-center">Product Qty</th>
			<th class="text-center">Product Rate</th>
			</tr>
			</thead>
			<tbody>';
			$query="select inv.*,ven.l_name,pro.product_name,trn.product_rate, trn.product_qty from tbl_pono as inv
					left join tbl_potrancation as trn on inv.po_id=trn.po_id 
					left join tbl_ledger as ven on ven.l_id=inv.vender_id
					left join product_mst as pro on pro.product_id=trn.product_id
					where inv.status=0 and trn.potrancation_status=0 and inv.vender_id=".$POST["vender_id"]." and trn.product_id=".$POST["product_id"]." order by trn.potrancation_id DESC LIMIT 50";
			$rs_prel=$dbcon->query($query);
			$rs_prel_num_rows=mysqli_num_rows($rs_prel);
			if($rs_prel_num_rows>0){
				while($prel=mysqli_fetch_assoc($rs_prel)){
					$resp.='<tr>
								<td class="text-center">'.$prel['po_no'].'</td>
								<td class="text-center">'.date('d-M-Y',strtotime($prel['po_date'])).'</td>
								<td class="text-center">'.$prel['product_qty'].'</td>
								<td class="text-center">'.$prel['product_rate'].'</td>
							</tr>';
					$row['cust_name']='<table class="table table-bordered table-striped"><tr><td><strong>Vendor Name : '.$prel['l_name'].'</strong></td></tr></table>';
					$row['product_name']=$prel['product_name'];		
				}
			}
			else{
				$resp.='<tr>
							<td colspan="4" class="text-center">NO DATA FOUND !!</td>
						</tr>';
				$row['cust_name']="";
				$row['product_name']="";
			}
			$resp.='</tbody></table>';
			
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
		
		else if(strtolower($POST['mode'])== "get_grossbalance")
		{
			$arr = get_grossbalance_purchase($dbcon,$POST['cust_id']);
			echo $arr;
		}
		
		else if(strtolower($POST['mode']) == "update_total") {
			
			//update total , net total , general books entry at edit time start - dhaval 
			$bill_sundry_tax = array_combine($POST['bill_sundry_tax'],$POST['bill_sundry_tax1']);
			print_r('bill_sundry_tax');
			exit();
			if($POST['invoice_id']>0)
			{
				$query="SELECT purchase_ledger_id,vender_id FROM `tbl_pono` where po_id=".$POST['invoice_id']." ";
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

				if($POST['currency_id']==$_SESSION['currency_id']){
					$update_invoice['g_total'] 		= $POST['g_total'];
					$update_invoice['g_total_conv']	= $POST['g_total']*$POST['currency_rate'];
					$update_invoice['total'] = $POST['basic_total'];
					$update_invoice['total_conv'] = $POST['basic_total']*$POST['currency_rate'];
				}else{
					$update_invoice['g_total'] = $POST['g_total']*$POST['currency_rate'];
					$update_invoice['g_total_conv'] = $POST['g_total']; 
					$update_invoice['total'] = $POST['basic_total']*$POST['currency_rate'];
					$update_invoice['total_conv'] = $POST['basic_total'];

				}

				update_record("tbl_pono",$update_invoice," po_id=".$POST['invoice_id'] ,$dbcon);

				//Update Basic total in General book for invoice table - sales ledger entry
				$info_gen['amount'] = $update_invoice['total'];
				$info_gen['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
				update_record("tbl_general_book",$info_gen," table_id=".$POST['invoice_id']." and ledger_id=".$rel['purchase_ledger_id']." and table_name='tbl_pono'" ,$dbcon);

				//Update Basic total in General book for invoice table - customer ledger entry
				$info_gen1['amount'] = $update_invoice['g_total'];
				$info_gen1['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
				update_record("tbl_general_book",$info_gen1," table_id=".$POST['invoice_id']." and ledger_id=".$rel['vender_id']." and ref_by='' and genral_book_status=0 and table_name='tbl_pono'" ,$dbcon);
				
				//update bill sundry in bill sundry table and general table 
				
				foreach ($bill_sundry_tax as $bill_sundry_tax_id => $bill_sundry_tax_amount) 	{
           			
					if($POST['currency_id']==$_SESSION['currency_id']){
						$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
						$info_sundry_tax['sundry_amount_conv']=$bill_sundry_tax_amount*$POST['currency_rate'];
					}else{
						$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount*$POST['currency_rate'];
						$info_sundry_tax['sundry_amount_conv']=$bill_sundry_tax_amount;
					}
					
					$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			        $info_sundry_tax['user_id']	= $_SESSION['user_id'];
			        $info_sundry_tax['company_id']	= $_SESSION['company_id'];
					$info_sundry_tax['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
					update_record("tbl_bill_sundry_transaction",$info_sundry_tax," sundry_ledger_id=".$bill_sundry_tax_id." and sundry_voucher_table='tbl_pono' and sundry_voucher_id='$POST[invoice_id]'" ,$dbcon);

					$query1="select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[invoice_id]' and sundry_ledger_id=".$bill_sundry_tax_id." and sundry_voucher_table='tbl_pono' and isdelete=0  ";
					$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1)); 
					
					$info_general_sundry['amount'] = abs($info_sundry_tax['sundry_amount']);
					$info_general_sundry['cdate']	= date("Y-m-d H:i:s");
			        $info_general_sundry['user_id']	= $_SESSION['user_id'];
			        $info_general_sundry['company_id']	= $_SESSION['company_id'];
					$info_general_sundry['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
					update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_tax_id." and table_name='tbl_bill_sundry_transaction' and table_id= ".$rel1['sundry_id']." " ,$dbcon);
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
		
		else if(strtolower($POST['mode'])== "get_grn_by_vendor"){
			$vender_id=$POST['vender_id'];
			$grn_id = $POST['grn_id'];
			$modee = $POST['modee'];
			echo get_grn_for_purchase($dbcon,$vender_id,$grn_id,$modee);
		}

		else if(strtolower($POST['mode'])== "get_service_by_vendor"){
			$vender_id=$POST['vender_id'];
			$grn_id = $POST['grn_id'];

		} 
		else if(strtolower($POST['mode'])== "convert_qty")
		{
			//var_dump($POST);
			$row=array();
			if($POST["type"]=="1"){
				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="conv_unit";
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
				$qry1="select grn.grn_id,grn_trn.grn_trn_id,grn_sub_trn.grn_trn_sub_id,grn_trn.product_id,grn_sub_trn.product_qty,grn_sub_trn.product_conv_qty,grn_sub_trn.product_base_unit,grn_sub_trn.product_conv_unit,potrn.discount_per,potrn.currency_id,potrn.currency_rate,potrn.conversion_rate,potrn.product_tax_cat,potrn.product_discount_conv,potrn.product_rate,potrn.formulaid,potrn.discount_per,potrn.product_hsn_code,potrn.product_des,potrn.pro_spe,potrn.rate_unit,potrn.unit_id,po.sales_type,potrn.product_currency_rate,job_trn.pr_rate as jobwork_rate,job_trn.rate_unit as jobwork_rate_unit from tbl_grn as grn
						left join tbl_grn_trn as grn_trn on grn_trn.grn_id=grn.grn_id
						left join tbl_grn_sub_trn as grn_sub_trn on grn_sub_trn.grn_trn_id=grn_trn.grn_trn_id
						left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=grn_sub_trn.job_work_trn_id
						left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id=grn_sub_trn.purchaseordertrn_id
						left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id
						left join product_mst as product on product.product_id=grn_trn.product_id
						where grn.grn_status=0 and grn_trn.grn_trn_status=0 and grn_sub_trn.status=0 and grn.purchase_status=0 and grn_trn.purchase_status=0 and grn_sub_trn.purchase_status=0 and grn.grn_id in (".$grn.")";
				//echo $qry1;
						
				$result1=$dbcon->query($qry1);
				while($rel=brp_mysqli_fetch_assoc($result1)){
				
				if(empty($po_id)){
					$qry_d="select * from tbl_po_grn_used as mst 
								left join tbl_potrancation as trn on trn.potrancation_id=mst.potrancation_id
							where mst.po_grn_used_status=0 and mst.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and trn.potrancation_status=3 and trn.user_id=".$_SESSION['user_id'];
					
						//	$qry_d="select * from tbl_potrancation as grn where grn.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and grn.potrancation_status=3";
				}else{
					$qry_d="select * from tbl_po_grn_used as mst 
								left join tbl_potrancation as trn on trn.potrancation_id=mst.potrancation_id
								left join tbl_pono as po on po.po_id=trn.po_id
							where mst.po_grn_used_status=0 and mst.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and trn.potrancation_status=0 and trn.po_id=".$po_id." and po.company_id=".$_SESSION['company_id'];
					
					//$qry_d="select * from tbl_potrancation as grn where grn.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and grn.potrancation_status=0 and po_id=".$po_id;
				}
				//echo $qry_d;
				//exit;
				$result_d=$dbcon->query($qry_d);
				$rd = brp_mysqli_fetch_array($result_d);
				$entry_count = brp_mysqli_num_rows($result_d);
				if($entry_count==0)
				{	
					
					if(empty($po_id)){
						$del_temp_used['po_grn_used_status'] = 2;
						$delete_temp_used = update_record('tbl_po_grn_used', $del_temp_used,"potrancation_id=".$rd['potrancation_id'], $dbcon);
						$qry_ol="select trn.potrancation_id,trn.product_qty,trn.product_conv_qty from tbl_po_grn_used as mst 
								left join tbl_potrancation as trn on trn.potrancation_id=mst.potrancation_id
							where trn.product_id='".$rel['product_id']."' and trn.discount_per='".$rel['discount_per']."' and trn.product_rate='".$rel['product_rate']."' and trn.formulaid='".$rel['formulaid']."' and  mst.po_grn_used_status=0 and mst.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and trn.potrancation_status=3 and trn.user_id=".$_SESSION['user_id'];
					
						//$qry_ol="select * from tbl_potrancation as grn where grn.product_id=".$rel['product_id']." and grn.discount_per=".$rel['discount_per']." and grn.product_rate=".$rel['product_rate']." and grn.formulaid=".$rel['formulaid']." and grn.potrancation_status=3 and company_id=".$_SESSION['company_id'];
					
					}else{
						$qry_ol="select trn.potrancation_id,trn.product_qty,trn.product_conv_qty from tbl_po_grn_used as mst 
							left join tbl_potrancation as trn on trn.potrancation_id=mst.potrancation_id
								left join tbl_pono as po on po.po_id=trn.po_id
								where trn.product_id='".$rel['product_id']."' and  trn.discount_per='".$rel['discount_per']."' and trn.product_rate='".$rel['product_rate']."' and trn.formulaid='".$rel['formulaid']."' and mst.po_grn_used_status=0 and mst.grn_sub_trn_id=".$rel['grn_trn_sub_id']." and trn.potrancation_status=0 and trn.po_id=".$po_id." and po.company_id=".$_SESSION['company_id'];
					
						
						//$qry_ol="select * from tbl_potrancation as grn where grn.product_id=".$rel['product_id']." and grn.discount_per=".$rel['discount_per']." and grn.product_rate=".$rel['product_rate']." and grn.formulaid=".$rel['formulaid']." and grn.potrancation_status=3 and company_id=".$_SESSION['company_id']." and po_id=".$po_id;
					}
					//echo $qry_ol;
					//exit;
					$result_ol=$dbcon->query($qry_ol);
					$rel_ol=brp_mysqli_fetch_assoc($result_ol);
					
					$qry_used_qt="select IFNULL(sum(used_qty),0) as usedqty,IFNULL(sum(used_qty),0) as convusedqty from tbl_po_grn_used as mst 
						where po_grn_used_status=0 and grn_sub_trn_id=".$rel['grn_trn_sub_id'];

					//echo $qry_used_qt;
					//exit;
					$result_used_qt=$dbcon->query($qry_used_qt);
					$rel_used_qt=brp_mysqli_fetch_assoc($result_used_qt);
					$pending_qty=$rel['product_qty']-$rel_used_qt['usedqty'];
					$conv_pending_qty=$rel['product_conv_qty']-$rel_used_qt['convusedqty'];
					if(!empty($rel_ol['potrancation_id'])){
						$product_qty=$pending_qty+$rel_ol['product_qty'];
						$product_conv_qty=$conv_pending_qty+$rel_ol['product_conv_qty'];
						/*$product_conv_qty = convert_stock($dbcon,$product_qty,$rel['product_id'],"conv_unit");*/
						if($rel['unit_id']===$rel['rate_unit']){
	    					$sqty=$product_qty;
	    				}else{
							$sqty=$product_conv_qty;
	    				}


						$product_amount_with_out_discount=$rel['product_rate']*$sqty;
						if(!empty($rel['discount_per'])){
							$product_discount_per=$rel['discount_per'];
							$product_discount_amount=(($product_amount_with_out_discount*$product_discount_per)/100);
							$product_amount=$product_amount_with_out_discount-$product_discount_amount;
						}else{
							$product_amount=$product_amount_with_out_discount;
						}
					}else{
						$product_qty=$pending_qty;
						$product_conv_qty=$conv_pending_qty;
						/*$product_conv_qty = convert_stock($dbcon,$product_qty,$rel['product_id'],"conv_unit");*/
						if($rel['unit_id']===$rel['rate_unit']){
	    					$sqty=$product_qty;
	    				}else{
							$sqty=$product_conv_qty;
	    				}

	    				if($rel['currency_id']==$company_state['currency_id']){
	    					$product_amount_with_out_discount=$rel['product_rate']*$sqty;
						}else{
							$product_amount_with_out_discount=$rel['product_currency_rate']*$sqty;
						}
						
						if(!empty($rel['discount_per'])){
							$product_discount_per=$rel['discount_per'];
							$product_discount_amount=(($product_amount_with_out_discount*$product_discount_per)/100);
							$product_amount=$product_amount_with_out_discount-$product_discount_amount;
							
						}else{
							$product_amount=$product_amount_with_out_discount;
						}
					}
					

					$company_state = get_company_data($dbcon,$_SESSION['company_id']);
					$sale_gst = get_tax_cat_by_hsn($dbcon,$rel['product_hsn_code']);
					$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);
					
					if($rel['sales_type']==1){
						$sale_gst = get_tax_cat_by_hsn($dbcon,$rel['product_hsn_code']);
					}else if($rel['sales_type']==2){
						$sale_gst['tax_gst']=0.1;
						$sale_gst['tax_cat_id']=0;
					}else if($rel['sales_type']==3){
						$sale_gst['tax_gst']=0;
						$sale_gst['tax_cat_id']=0;
					}else if($rel['sales_type']==4){
						$sale_gst['tax_gst']=5;
						$sale_gst['tax_cat_id']=0;
					}else if($rel['sales_type']==5){
						$sale_gst['tax_gst']=0;
						$sale_gst['tax_cat_id']=0;
					}else if($rel['sales_type']==6){
						$sale_gst['tax_gst']=12;
						$sale_gst['tax_cat_id']=0;
					}else if($rel['sales_type']==7){
						$sale_gst['tax_gst']=18;
						$sale_gst['tax_cat_id']=0;
					}else if($rel['sales_type']==8){
						$sale_gst['tax_gst']=24;
						$sale_gst['tax_cat_id']=0;
					}
					
					$ven_s = "select stateid from tbl_ledger where l_id=".$POST['cust_id'];
					$ves=$dbcon->query($ven_s);
					$vers = mysqli_fetch_array($ves);

					
					$cgst_tax_rate=0;$cgst_tax_rate_conv=0;
					$sgst_tax_rate=0;$sgst_tax_rate_conv=0;
					$igst_tax_rate=0;$igst_tax_rate_conv=0;
					if(($company_state['stateid'] == $vers['stateid']) && ($custLedgerDetails['enable_sez'] == 0)){
						$gst = $sale_gst['tax_gst']/2;
						$cgst_tax_per = $gst;
						$cgst_tax_rate = ($gst*$product_amount)/100;
						$sgst_tax_per = $gst;
						$sgst_tax_rate = ($gst*$product_amount)/100;
						$cgst_tax_rate_conv = ($rel['currency_rate']*$gst*$product_amount)/100;
						$sgst_tax_rate_conv = ($rel['currency_rate']*$gst*$product_amount)/100;
					}else{
						$igst_tax_per 	= $sale_gst['tax_gst'];
						$igst_tax_rate 	= ($sale_gst['tax_gst']*$product_amount)/100;
						$igst_tax_rate_conv = ($rel['currency_rate']*$sale_gst['tax_gst']*$product_amount)/100;
					}
					//echo $company_state['stateid']."-".$POST['cust_stateid']."-".$custLedgerDetails['enable_sez'];
					//echo "product-".$rel['product_id']."-";
					//echo "HSN-".$rel['product_hsn']."-";
					//print_r($sale_gst);
					
					$info1['grn_id']				= $rel['grn_id'];
					$info1['grn_trn_id']			= $rel['grn_trn_id'];
					$info1['grn_sub_trn_id']		= $rel['grn_trn_sub_id'];
					
					
					$info1['product_id']			= $rel['product_id'];
					$info1['description']			= '';
					$info1['product_des']			= $rel['product_des'];
					$info1['pro_spe']				= $rel['pro_spe']; 
					$info1['product_qty']			= $product_qty;
					$info1['product_conv_qty']		= $product_conv_qty;
					$info1['product_hsn_code']		= $rel['product_hsn_code'];
					$info1['unit_id']				= $rel['product_base_unit'];
					$info1['conv_unit_id']			= $rel['product_conv_unit'];
					$info1['rate_unit']				= $rel['rate_unit'];
					//$info1['product_discount']		= $product_discount_amount;
					$info1['discount_per']			= $product_discount_per;
				//	$info1['formulaid']				= $rel['formulaid'];
				//	$info1['sel_tax']				= $r_decode['name'];
					//$info1['product_amount']		= $product_amount;
					//$info1['total']					= $product_amount;
					$info1['company_id']			= $_SESSION['company_id'];
					$info1['user_id']				= $_SESSION['user_id'];
					$info1['purchase_bill_type']	= 0;
					$info1['currency_id']			= $rel['currency_id'];
					$info1['currency_rate']			= $rel['currency_rate'];
					$info1['conversion_rate']		= $conversion_rate;
					$info1['product_usd_rate']		= $rel['product_rate'];
					$info1['product_usd_amount']	= $total;
					
					$info1['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
					$info1['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
					$info1['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0 ;
					//$info1['product_rate']			= $rel['product_rate'];
					//$info1['cgst_tax_rate']			= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
					//$info1['sgst_tax_rate']			= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
					//$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
					$info1['product_tax_cat']		= $sale_gst['tax_cat_id'];
					
					if($rel['currency_id']==$company_state['currency_id']){
    					$info1['product_rate']			= $rel['product_rate'];
    					$info1['product_discount']		= $product_discount_amount;
    					$info1['total']					= $product_amount;
    					$info1['product_amount']		= $product_amount;
    					$info1['taxable_value']		    = $cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
    					$info1['cgst_tax_rate']			= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
    					$info1['sgst_tax_rate']			= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
    					$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
    					
    					$info1['product_rate_conv']		= $rel['currency_rate']*$rel['product_rate'];
    					$info1['product_discount_conv']		= $rel['currency_rate']*$product_discount_amount;
    					$info1['total_conv']			= $rel['currency_rate']*$product_amount;
    					$info1['product_amount_conv']	= $rel['currency_rate']*$product_amount;
    					$info1['taxable_value_conv']	= $cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;
    					$info1['cgst_tax_rate_conv']		= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
    					$info1['sgst_tax_rate_conv']		= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
    					$info1['igst_tax_rate_conv']		= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
					}else{
					    $info1['product_rate']		    = $rel['currency_rate']*$rel['product_rate'];
    					$info1['product_discount']		= $rel['currency_rate']*$product_discount_amount;
    					$info1['total']					= $rel['currency_rate']*$product_amount;
    					$info1['product_amount']		= $rel['currency_rate']*$product_amount;
    					$info1['taxable_value']		    = $cgst_tax_rate_conv+$sgst_tax_rate_conv+$igst_tax_rate_conv;
    					$info1['cgst_tax_rate']			= isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
    					$info1['sgst_tax_rate']			= isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
    					$info1['igst_tax_rate']			= isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
    					
    					$info1['product_rate_conv']		= $rel['product_currency_rate'];
    					$info1['product_discount_conv']		= $product_discount_amount;
    					$info1['total_conv']			= $product_amount;
    					$info1['product_amount_conv']	= $product_amount;
    					$info1['taxable_value_conv']		    = $cgst_tax_rate+$sgst_tax_rate+$igst_tax_rate;
    					$info1['cgst_tax_rate_conv']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
    					$info1['sgst_tax_rate_conv']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
    					$info1['igst_tax_rate_conv']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
					}
					
					//$info=get_product_tax($dbcon,$product_amount,$info1['formulaid']);
					//$info1=array_merge($info1,$info);
					
					$table='tbl_potrancation';$tableid='potrancation_id';	
					if(!empty($po_id)) {
						$info1['po_id'] = $po_id;
					}
					else {
						$info1['potrancation_status'] = 3;
					}
					/*var_dump($info1);*/
					if(!empty($rel_ol['potrancation_id'])){
						$updatesalesid=update_record($table, $info1,"potrancation_id=".$rel_ol['potrancation_id'], $dbcon);
						$inserid=$rel_ol['potrancation_id'];
					}else{
						$inserid=add_record($table, $info1, $dbcon);
					}
					//$insert_tax=add_tax_record($dbcon,$inserid,"tbl_potrancation","po_id",$info1['formulaid'],$product_amount);
					
					//insert tax record
					
					/* insert to tax transaction table by Dhruv */
					if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'CGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_potrancation",$rel['product_id'],3,'',$POST['branch_id'],$rel['currency_id'],$rel['currency_rate'],$cgst_tax_rate_conv);
					}
					if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'SGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_potrancation",$rel['product_id'],3,'',$POST['branch_id'],$rel['currency_id'],$rel['currency_rate'],$sgst_tax_rate_conv);
					}
					if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
						$cl_id = get_ledger_by_name($dbcon,'IGST');
						$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_potrancation",$rel['product_id'],3,'',$POST['branch_id'],$rel['currency_id'],$rel['currency_rate'],$igst_tax_rate_conv);
					}
					
					$count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$product_amount,$inserid,$rel['product_id'],0,$POST['branch_id'],'tbl_potrancation',$rel['currency_id'],$rel['currency_rate'],$product_amount*$rel['currency_rate']);
					
					$query_invoicetype = $dbcon->query("UPDATE tbl_grn_sub_trn SET purchase_qty = purchase_qty + ".$pending_qty." WHERE grn_trn_sub_id = ".$rel['grn_trn_sub_id']);
					
					
					$info_used['potrancation_id']	= $inserid;
					$info_used['product_id']		= $rel['product_id'];
					$info_used['used_qty']			= $pending_qty;
					$info_used['conv_used_qty']		= $conv_pending_qty;	
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
					
					//update_grn_sub_trn_to_purchase_status($dbcon,$rel['grn_trn_sub_id']);
				}
			}	
		}
		else if(strtolower($POST['mode'])== "insert_service"){
			$in_po['potrancation_status'] = 2;
			$updatesalesid=update_record('tbl_potrancation', $in_po,"potrancation_status=3 and po_id=0 and company_id=".$_SESSION['company_id'], $dbcon);
			$query = "select ser.service_id,hsn.hsn_code,ser.vender_id,led.stateid,potrn.product_rate,trn.product_qty,(select sum(product_conv_qty) from tbl_potrancation as ptr where ptr.service_id = ser.service_id and potrancation_status!=2 ) as done_qty,trn.product_id,trn.description,potrn.product_des,potrn.pro_spe,trn.unit_id,potrn.discount_per,trn.service_trn_id from tbl_service_notes as ser 
			left join tbl_service_notes_trn as trn on trn.service_id = ser.service_id
			left join product_mst as pro on pro.product_id = trn.product_id
			left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
			left join tbl_ledger as led on led.l_id = ser.vender_id
			left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = trn.purchaseordertrn_id
			where trn.service_trn_status=0 and ser.service_id=".$POST['service_id'];
			//echo $query;exit;
			$result=$dbcon->query($query);
			while($row = mysqli_fetch_array($result)){
				if(isset($POST['currency_enable']) && $POST['currency_enable']==1){
	            	$curncy_trn['currency_id'] = $POST['currency_id'];
	            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
	            }else{
	            	$basecurrency = getbasecurrency($dbcon);
	            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
	            	$curncy_trn['currency_rate'] = 1;
	            }
				
				$company_state = get_company_data($dbcon,$_SESSION['company_id']);
				//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
				$sale_gst = get_tax_cat_by_hsn($dbcon,$row['hsn_code']);
				$custLedgerDetails = get_cust_data_arr($dbcon,$row['vender_id']);

				if($row['discount_per']){
					$pr_amt = $row['product_rate']*$row['product_qty'];
					$disc_amt = $pr_amt * $row['discount_per']/100;
					$product_amount = $pr_amt - $disc_amt;
				}else{
					$pr_amt = $row['product_rate']*$row['product_qty'];
					$disc_amt = 0;
					$product_amount = $pr_amt - $disc_amt;
				}
				
				

				$cgst_tax_rate=0;
				$sgst_tax_rate=0;
				$igst_tax_rate=0;

				//echo $company_state['stateid']."--".$POST['cust_stateid'];exit;
				if(($company_state['stateid'] == $row['stateid'])){
					$gst = $sale_gst['tax_gst']/2;
					$cgst_tax_per = $gst;
					$cgst_tax_rate = ($gst*$product_amount)/100;
					$sgst_tax_per = $gst;
					$sgst_tax_rate = ($gst*$product_amount)/100;
				}else{
					$igst_tax_per = $sale_gst['tax_gst'];
					$igst_tax_rate = ($sale_gst['tax_gst']*$product_amount)/100;
				}
				
					
				$info1['service_id']			= $row['service_id'];
				$info1['service_trn_id']		= $row['service_trn_id'];
				$info1['product_id']			= $row['product_id'];
				$info1['description']			= $row['description'];
				
				$info1['product_des']			= $row['product_des'];
				$info1['pro_spe']				= $row['pro_spe'];
					
				$info1['product_qty']			= $row['product_qty'];
				$info1['product_conv_qty']		= $row['product_qty'];

				$info1['unit_id']				= $row['unit_id'];
				$info1['conv_unit_id']			= $row['unit_id'];
				$info1['rate_unit']				= $row['unit_id'];

				$info1['product_hsn_code']		= $row['hsn_code'];
				
				$info1['product_rate']			= $row['product_rate'];
				$info1['product_discount']		= $disc_amt;
				$info1['discount_per']			= $row['discount_per'];
				$info1['product_amount']		= $product_amount;
				//$info1['total']					= $POST['product_amount'];
				$info1['company_id']			= $_SESSION['company_id'];
				$info1['user_id']				= $_SESSION['user_id'];
				
				$info1['product_tax_cat']		= $sale_gst['tax_cat_id'];
				$info1['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
				$info1['cgst_tax_rate']			= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				$info1['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
				$info1['sgst_tax_rate']			= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				$info1['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0 ;
				$info1['igst_tax_rate']			= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
				
				$info1['taxable_value']		= $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate;
				$info1['total'] = $cgst_tax_rate + $sgst_tax_rate + $igst_tax_rate + $product_amount;
				
				$table='tbl_potrancation';$tableid='potrancation_id';	
				
				$info1['potrancation_status'] = 3;
				
				
				$inserid=add_record($table, array_merge($info1,$curncy_trn), $dbcon, $POST['branch_id']);
				
				
				/* insert to tax transaction table by dhaval */
				if( ($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'CGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_potrancation",$row['product_id'],3,"",$POST['branch_id'],"","","");
				}
				if( ($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'SGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_potrancation",$POST['product_id'],3,"",$POST['branch_id'],"","","");
				}
				if( ($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'IGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_potrancation",$POST['product_id'],3,"",$POST['branch_id'],"","","");
				}
			}
		}
		else if(strtolower($POST['mode'])== "check_product_tds")
		{
			$prodcut_id = $POST['prodcut_id'];
			$vender_id = $POST['vender_id'];

			$tds_details=get_product_tds($dbcon,$prodcut_id,$vender_id);

			echo json_encode($tds_details);

			//echo $prodcut_id;
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
			$pro_mst_rate = get_pro_field($dbcon, $sel_pro_rate_rel['product_id'], 'product_purchase_mst_rate');
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
	
function load_purchase_srs_no($dbcon,$invoicetype_id){
	
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=12 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']." and invoicetype_id=".$invoicetype_id;
	
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

		$info1['module_name']	= MODULE_PURCHASE;
		$info1['module_id']		= $invoice_id;

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
		$info1['module_name'] = MODULE_PURCHASE;
		$info1['module_id'] = $invoice_id;
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