<?php

		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$com="select * from tbl_finance_setting where company_id=".$_SESSION['company_id'];
		$comty=mysqli_fetch_assoc($dbcon->query($com));	
		
        if($comty['series_same']=="1"){
                $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=1 and company_id= ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
        }else{
                $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
        }

        $sales_ledger_id = $dbcon->query("SELECT l_id FROM `tbl_ledger` WHERE `l_group` = ".SALES_ACCOUNTS)->fetch_object()->l_id;
        $info['complaint_id']	= $POST['complaint_id'];
        $info['quotation_id']	= $POST['quotation_id'];
        $info['invoicetype_id']	= $POST['invoicetype_id'];
        $info['invoice_no']	= $POST['invoice_no'];
        $info['invoice_date']	= date('Y-m-d',strtotime($POST['invoice_date']));
        $info['challan_no']	= $POST['challan_no'];
        $info['challan_date']	= date('Y-m-d',strtotime($POST['challan_date']));
        $info['num_of_parcel']	= $POST['num_of_parcel'];
        $info['dispatch_doc_no']= text_rnremove($POST['dispatch_doc_no']);
        $info['dispatch_date']  = date('Y-m-d H:i:s',strtotime($POST['dispatch_date']));
        $info['vehicle_no']	= $POST['vehicle_no'];
        $info['order_no']	= $POST['order_no'];
        $info['order_date']	= date('Y-m-d',strtotime($POST['order_date']));
        $info['dispatch_by']	= $POST['dispatch_by'];
        $info['destination']	= $POST['destination'];
        $info['payment_terms']	= $POST['payment_terms'];
        $info['docket_no']	= $POST['docket_no'];
        $info['packing_boxes']	= $POST['packing_boxes'];
        $info['total_weight']	= $POST['total_weight'];
        $info['cust_id']	= $POST['cust_id'];
        $info['sales_ledger_id']= $POST['sales_ledger_id'];
        //$info['machine_name']	= $POST['machine_name'];
        $info['consignee_id']	= $POST['consignee_id'];
        $info['packing']	= $POST['packing'];
        $info['cutting']	= $POST['cutting'];
        $info['freight']	= $POST['freight'];
        $info['g_total']	= $POST['g_total'];
        $info['formulaid']      = $POST['formula_id']; //added by : Dimple
        $info['tcs_total']      = $POST['tcs_total'];  //added by : Dimple
        $info['lrno']      = $POST['lrno']; //added by : pathik
        $info['transport_id']      = $POST['transport_id']; //added by : pathik
		$info['lr_date']		= date('Y-m-d',strtotime($POST['lr_date'])); //added by : pathik                        
        $info['remark']			= text_rnremove($POST['remark']);
        $info['reverse_charge']	= $POST['reverse_charge_check'];
        $info['install_type']	= $POST['install_type'];
        $info['gst_flag']		= '2';
        $info['cdate']			= date("Y-m-d H:i:s");
        $info['user_id']		= $_SESSION['user_id'];
        $info['company_id']		= $_SESSION['company_id'];
        $info['machine_name']		= 0;
        $info['discount']		= 0;
        $info['discount_per']		= 0;
        //$info['formulaid']		= 0;
        $info['tax1_name']		= 0;
        $info['tax2_name']		= 0;
        $info['tax3_name']		= 0;
        $info['taxvalue1']		= 0;
        $info['taxvalue2']		= 0;
        $info['taxvalue3']		= 0;
        $info['round_off']		= 0;
        $info['paid_amount']		= 0;
        $info['invoice_status']		= 0;
        $info['usertype_id']		= $_SESSION['usertype_id'];
        if(isset($POST['save_print']))
        {
                $info['print_status']	= $POST['print_status'];
        }
        $inserinvoiceid=add_record('tbl_invoice', $info, $dbcon, $branch_id);
		
		/*Update Trn Table Start*/
		if($inserinvoiceid){
			$inv_trn['invoice_id']			= $inserinvoiceid;
			$inv_trn['trancation_status']	= 0;
			$updatetrnid=update_record('tbl_invoicetrn', $inv_trn,"trancation_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);
		}
		$query="select trn.*,pro_mst.product_base_unit from tbl_invoicetrn as trn
		left join product_mst as pro_mst on pro_mst.product_id=trn.product_id
		where trancation_status=0 and invoice_id=".$inserinvoiceid;
		$result=$dbcon->query($query);
		while($row=brp_mysqli_fetch_assoc($result)){
			if($row['unit_id']!=0){
				minus_stock($dbcon,$row['product_id'],$row['unit_id'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
				deduct_so_reseve_stock($dbcon,$row['sales_ordertrn_id'],$row['product_qty'],$row['unit_id'],$row['trancation_id']);
			}else{
				minus_stock($dbcon,$row['product_id'],$row['product_base_unit'],$info['invoice_date'],"invoice_trn",$row['trancation_id'],$row['product_qty']);
				deduct_so_reseve_stock($dbcon,$row['sales_ordertrn_id'],$row['product_qty'],$row['product_base_unit'],$row['trancation_id']);
			}
			
			
			
			if(!empty($row['sales_ordertrn_id'])){
				$query_so_used="select IFNULL(sum(product_qty),0) as used_qty from tbl_invoicetrn as trn
								where trn.trancation_status=0 and trn.sales_ordertrn_id=".$row['sales_ordertrn_id'];
				$result_so_used=$dbcon->query($query_so_used);
				$row_so_used=mysqli_fetch_assoc($result_so_used);
				
				$query_so="select product_qty from tbl_sales_ordertrn as trn
							where trn.sales_ordertrn_status=0 and trn.sales_ordertrn_id=".$row['sales_ordertrn_id'];
				$result_so=$dbcon->query($query_so);
				$row_so=mysqli_fetch_assoc($result_so);
				if($row_so['product_qty']<=$row_so_used['used_qty']){
					$inv_so_update['invoice_status']	= 1;
					$updatetrnid33=update_record('tbl_sales_ordertrn', $inv_so_update,"sales_ordertrn_id=".$row['sales_ordertrn_id'] , $dbcon);
				}else{
					$inv_so_update['invoice_status']	= 0;
					$updatetrnid33=update_record('tbl_sales_ordertrn', $inv_so_update,"sales_ordertrn_id=".$row['sales_ordertrn_id'] , $dbcon);
				}
				sales_order_used_status_update($dbcon,$row['sales_ordertrn_id']);
			}
		}
		
        $taxable_amount = ($POST['g_total'] - $POST['tcs_total']);
        // if invoice entry done, make finance related entry.
        if($inserinvoiceid){
            add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,2,$POST['cust_id'],$taxable_amount,$general_book_id,$POST['invoice_date'], $branch_id);
			
            add_general_book_entry($dbcon,"tbl_invoice",$inserinvoiceid,1,$POST['sales_ledger_id'],$POST['sales_account_amount'],$general_book_id,$POST['invoice_date'], $branch_id);
			
			
            general_book_tax_entry($dbcon,$inserinvoiceid, $branch_id);
            general_book_sercices_entry($dbcon,$inserinvoiceid, $branch_id);
            // make entry for TCS tax
            if($POST['formula_id']){
                add_tax_record($dbcon,$inserinvoiceid,'tbl_invoice','invoice_id',$POST['formula_id'],$taxable_amount,$branch_id);
                general_book_tcs_entry($dbcon,$inserinvoiceid,$branch_id);
            }
        }
        
        if($POST['reverse_charge_check']){
            $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_status = 0 and l_id=44")
                ->fetch_object()->l_name;
            add_general_book_entry($dbcon,"invoice_reverse_charge",$inserinvoiceid,1,44,$POST['product_amount_tax'],$general_book_id,$POST['invoice_date']);
        }
		
		if($POST['install_type']=='yes')
		{
			$qry1="select * from tbl_invoicetrn where trancation_status=0 and invoice_id='$inserinvoiceid'";
			$row1=$dbcon->query($qry1);
			while($rel1=mysqli_fetch_assoc($row1))
			{
				$infoc['complaint_no']=load_complaint_no($dbcon);
				$infoc['complaint_date']=date('Y-m-d',strtotime($POST['invoice_date']));
				$infoc['cust_id']=$POST['cust_id'];
				$infoc['complaint_type_id']='1';
				$infoc['cdate']=date("Y-m-d H:i:s");
				$infoc['followup_status']='1';
				$infoc['sp_part_status']='4';
				$infoc['old_sp_part_status']='no';
				$infoc['user_id']=$_SESSION['user_id'];
				$infoc['company_id']=$_SESSION['company_id'];
				$infoc['invoice_id']=$inserinvoiceid;
				$insercomplainid=add_record('tbl_complaint', $infoc, $dbcon, $branch_id);
				
				/*$qry ='INSERT INTO tbl_complaint_trn (complaint_id,product_id,comp_pro_sts,user_id)
				SELECT '.$insercomplainid.',product_id,ser_status,user_id FROM  tbl_invoicetrn where invoice_id='.$inserinvoiceid; */
				
				$qryx="INSERT INTO tbl_complaint_trn (complaint_id,product_id,comp_pro_sts,comp_amount,user_id) values ('$insercomplainid','$rel1[product_id]','$rel1[ser_status]','$rel1[total]','$_SESSION[user_id]')";
							
				$dbcon->query($qryx);
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=1 and company_id= ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			}
		}
		
		if($POST['quotation_id']){
			$upd_qt_sts=upd_qt_done_sts($dbcon,$POST['quotation_id'],$inserinvoiceid);
		}
		if($POST['complaint_id']){
			$upd_spare_inv_sts=upd_spare_inv_sts($dbcon,$POST['complaint_id'],$inserinvoiceid);
		}
		
		//Update Serial No.
		$upd_inv_srl_no=upd_inv_srl_no($dbcon,$inserinvoiceid);
		
		//Copy Serial No.
		$cpy_srl_no=copy_srl_no($dbcon,$inserinvoiceid);
		//$deleteid=delete_record('tbl_invoicetrntemp',"user_id=".$_SESSION['user_id'], $dbcon);	
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"invoice_add",1,"tbl_invoice",$inserinvoiceid);
                
        // Payment Entery 
        if($POST['paymentmodeid'] && $POST['paid_amount'])
        {	
            $acc_id	= $sales_ledger_id;
        
            $row=array();
            $query1="select * from tbl_invoicetype where type_id=4 and company_id= ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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
            $receipt['receipt_date']          = $passbook['entry_date']	= date("Y-m-d",strtotime($POST['invoice_date']));					
            $receipt['cust_id']               = $POST['cust_id'];
            $receipt['bank_id']               = $POST['bankid'];
            $receipt['acc_id']                = $passbook['acc_id'] 	 	= $acc_id;
            $receipt['payment_mode_id']       = $passbook['paymentmodeid']     = $POST['paymentmodeid'];
            $receipt['cheque_dtl']            = $passbook['reference_no']      = $POST['cheque_dtl'];
            $receipt['ref_date']              = $passbook['reference_date']    = date("Y-m-d",strtotime($POST['ref_date']));
            $receipt['payment_type']          = 1;
            $receipt['total_paid_amount']     = $passbook['amount']	   	= $POST['paid_amount'];
            $receipt['payment_remark']        = text_rnremove($POST['remark']);
            $receipt['cdate']                 = date("Y-m-d H:i:s");
            $receipt['user_id']               = $_SESSION['user_id'];
            $receipt['company_id']            = $_SESSION['company_id'];
            $receipt_id = add_record('tbl_receipt', $receipt, $dbcon, $branch_id);
                		
            //Receipt transaction Entry
            $source="Invoice";
            $totalamo = $POST['paid_amount'];
            $receipt_trn['receipt_id']          = $receipt_id;
            $receipt_trn['invoice_id']          = $inserinvoiceid;
            $receipt_trn['purchase_id']         = 0;
            $receipt_trn['cradit_note_id']      = 0;
            $receipt_trn['debit_note_id']       = 0;
            $receipt_trn['excess_id']           = 0;
            $receipt_trn['payment_source']      = $source;
            $receipt_trn['paid_amount']         = $totalamo;
            $receipt_trn['total_amount']        = $totalamo;
            $receipt_trn['payment_type']        = 1;
            $receipt_trn['user_id']             = $_SESSION['user_id'];
            $receipt_trn['company_id']          = $_SESSION['company_id'];
            $receipt_trn['usertype_id']         = $_SESSION['user_type'];
            $receipt_trn['status']              = 0;
            $receipt_trn_id = add_record('tbl_receipt_trn', $receipt_trn, $dbcon, $branch_id);
				
            // Passbook entry
            $customer_name = $dbcon->query("select l_name as customer_name from tbl_ledger where l_status = 0 and l_id=".$POST['cust_id']." and company_id=".$_SESSION['company_id'])
                ->fetch_object()->customer_name;

            $passbook['customer_id']     = $POST['cust_id'];
            $passbook['typeid']          = 1;// 1. DR , 2 CR
            $passbook['trn_id']          = $receipt_id;
            $passbook['trn_table']       = 'tbl_receipt';
            $passbook['passbook_note']   = "Invoice Payment From : ".$customer_name;
            $passbook['user_id']         = $_SESSION['user_id'];
            $passbook['company_id']	 = $_SESSION['company_id'];
            $insert1 = add_record('tbl_passbookentry', $passbook, $dbcon, $branch_id);

            // General book Entry for Vendor
            $gen_vendor['ref_date']     = date("Y-m-d");
            $gen_vendor['table_name']	= "tbl_payment";
            $gen_vendor['table_id']	= $receipt_id;
            $gen_vendor['entry_type']	= 2;
            $gen_vendor['ledger_id']	= $POST['cust_id'];
            $gen_vendor['amount']	= $POST['paid_amount'];
            $gen_vendor['user_id']	= $_SESSION['user_id'];
            $gen_vendor['cdate']	= date("Y-m-d H:i:s");
            $gen_vendor['company_id']	= $_SESSION['company_id'];
            $gen_vendor_id = add_record("tbl_general_book", $gen_vendor, $dbcon, $branch_id);
        
            // General book Entry for Payment mode (cash, bank etc.)
            $gen_payment['ref_date']	= date("Y-m-d");
            $gen_payment['table_name']	= "tbl_payment";
            $gen_payment['table_id']	= $receipt_id;
            $gen_payment['entry_type']	= 1;
            $gen_payment['ledger_id']	= $POST['paymentmodeid'];
            $gen_payment['amount']	= $POST['paid_amount'];
            $gen_payment['user_id']	= $_SESSION['user_id'];
            $gen_payment['cdate']	= date("Y-m-d H:i:s");
            $gen_payment['company_id']	= $_SESSION['company_id'];
            $inserid11=add_record("tbl_general_book", $gen_payment, $dbcon, $branch_id);
        }
        // Payment Entry End

        if(isset($POST['save_print'])){
                $arr['printstatus']=$POST['print_status'];
                $arr['msg']="1";
                $arr['eid']=$inserinvoiceid;
        }
        else{
                if($inserinvoiceid){	
                        $arr['msg']="1";							
                }
                else{
                        $arr['msg']="0";
                }
        }
                
        echo json_encode($arr);