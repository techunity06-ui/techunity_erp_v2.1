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

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
	
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    FINANCE_JOURNAL_LIST,
    FINANCE_JOURNAL_CREATE,
    FINANCE_JOURNAL_EDIT,
    FINANCE_JOURNAL_DELETE
]);
//p($bulkAccessArray);

if(strtolower($POST['mode']) == "fetch") {
			
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
               
		$where.="  and journal_date between '".date('Y-m-d',strtotime($s_date[0]))."' AND '".date('Y-m-d',strtotime($s_date[1]))."'";

		 //branch , company, user check start - dhaval 
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('jo', $branch_id);
		
		$where.=" $where_db";

		$where_company=check_company('jo');

		$where.=" $where_company";

		$where_user=check_user('jo');

		//$where.=" $where_user";

		// branch , comapny , user check end - dhaval  


			$appData = array();
			$i=1;
			//All Ledger Show karva hoy to aa comment open karavi//$aColumns = array('jo.journal_id','jo.journal_no','GROUP_CONCAT(tl.l_name) as lname','jo.journal_date','jo.remark','jo.journal_status','jo.cdate','jo.user_id');
			$aColumns = array('jo.journal_id','jo.journal_no','tl.l_name as lname','jot.amount','jo.journal_date','jo.jv_remark','jo.journal_status','jo.cdate','jo.user_id');
			$sIndexColumn = "jo.journal_id";
			$isWhere = array("jo.entry_type=0 and jo.journal_status = 0  ".$where. "and jot.journal_trn_status=0 ");
			$sTable = "tbl_journal as jo";			
			$isJOIN = array('left join tbl_journal_trn jot on jo.journal_id=jot.journal_id',
					'left join tbl_ledger as tl on jot.ledger_id=tl.l_id');
			$hOrder = "jo.journal_date desc";
			$hGroupby = array("jot.journal_id");
			include('../../../include/pagging.php');
			//$appData = array();
			$id=1;
            $edit = $delete = ''; 
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['journal_no'];
				$row_data[] = date('d M, Y',strtotime($row['journal_date']));
				$row_data[] = $row['lname'];
				$row_data[] = $row['amount'];
				$row_data[] = $row['jv_remark'];
				
				$addpayment='';$delete='';$edit='';
					
                    if(in_array(FINANCE_JOURNAL_DELETE,$bulkAccessArray)){
                        $delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['journal_id'].')"><i class="fa fa-trash-o"></i></button>';
                    }
                    //$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchase_view/'.$row['po_id'].'"><i class="fa fa-eye"></i></a> ';

                    if(in_array(FINANCE_JOURNAL_EDIT,$bulkAccessArray)){
                        $edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'journal_entry_edit/'.$row['journal_id'].'"><i class="fa fa-pencil"></i></a>';
                    }
                    $row_data[] = $edit.' '.$delete.' '.$view;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
            $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

            //Currency converter
			if(isset($POST['currency_enable'])){
		    	$curncy_trn['currency_id'] = $POST['currency_id'];
		    	$curncy_trn['currency_rate'] = $POST['currency_rate'];
		    }else{
		    	$basecurrency = getbasecurrency($dbcon);
		    	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
		    	$curncy_trn['currency_rate'] = 1;
		    }

			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
			
				$info['journal_no']		= $POST['journal_entry_no'];
				$info['journal_date']	= date('Y-m-d',strtotime($POST['journal_entry_date']));
				$info['remark']			= $POST['remark'];
				$info['cdate']			= date("Y-m-d H:i:s");
				$info['user_id']		= $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];
				$info['currency_enable']	= $_POST['currency_enable'];
				$info['gst_nature']	= $_POST['gst_nature'];
				$info['jv_remark'] = $POST['jv_remark'];
				//var_dump($info);
			$inserpoid=add_record('tbl_journal',array_merge($info,$curncy_trn), $dbcon, $POST['branch_id']);
				
				$info_trn['journal_id']	= $inserpoid;
				$info_trn['journal_trn_status']= 0;
			$updateid=update_record('tbl_journal_trn',array_merge($info_trn,$curncy_trn),"journal_trn_status=3 and user_id=".$_SESSION['user_id'] ,$dbcon, $branch_id);
			
			insert_general_book($dbcon,$inserpoid, $branch_id);


			// Update payment id in TDS/TCS deduction table
			if($POST['ledger_Tax_type'] == '9891'){
				$tds_ded['payment_date']	= date("Y-m-d",strtotime($POST['journal_entry_date']));
				$tds_ded['payment_id'] = $inserpoid;
				$update_tds_ded_id=update_record('tbl_tds_tax_deduction_reference',$tds_ded,"isdelete=0 and payment_id=0 and payment_type=1 and user_id=".$_SESSION['user_id'] , $dbcon);
			}else if($POST['ledger_Tax_type'] == '9892'){
				$tcs_ded['payment_date']	= date("Y-m-d",strtotime($POST['journal_entry_date']));
				$tcs_ded['payment_id'] = $inserpoid;
				$update_tds_ded_id=update_record('tbl_tds_tax_deduction_reference',$tcs_ded,"isdelete=0 and payment_id=0 and payment_type=2 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
			
			/*Update register expence in tbl_registered_expense Table Start by Dhruv*/
			if($inserpoid && $_POST['gst_nature'] == 94){
				$reg_exp['receipt_id']	= $inserpoid;
				$reg_exp['voucher_id']	= $inserpoid;
				$updateregexpid=update_record('tbl_registered_expense',array_merge($reg_exp,$curncy_trn),"receipt_id=0 and isdelete=0 and voucher_id=0 and voucher_table='tbl_journal'  and user_id=".$_SESSION['user_id'] , $dbcon);
			}

			/*Update payment to gov in tbl_payment_to_govt Table Start by Dhruv*/
			if($inserpoid && $_POST['gst_nature'] == 92){
				$pay_gov['receipt_id']	= $inserpoid;
				$pay_gov['voucher_id']	= $inserpoid;
				$updatepaygovid=update_record('tbl_payment_to_govt',array_merge($pay_gov,$curncy_trn),"receipt_id=0 and isdelete=0 and voucher_id=0 and voucher_table='tbl_journal'  and user_id=".$_SESSION['user_id'] , $dbcon);
			}

			/*Update Credit debit note trn in tbl_cr_dr_adjustment Table Start by Dhruv*/
			if($inserpoid && ($_POST['gst_nature'] == 79 || $_POST['gst_nature'] == 80)){
				$cr_dr['voucher_id']	= $inserpoid;
				$updatecrdrid=update_record('tbl_cr_dr_adjustment',array_merge($cr_dr,$curncy_trn),"voucher_id=0 and voucher_table='tbl_journal' and entry_type=0  and user_id=".$_SESSION['user_id'] , $dbcon);
			}

			if($inserpoid){
				$billby_bill['bill_table_id']	= $inserpoid;

				//$billby_bill['bill_ledger_id'] =  $POST['vender_id'];
				$updatebillbytrnid=update_record('tbl_bill_by_bill_adjustment_transaction',array_merge($billby_bill,$curncy_trn),"bill_voucher_type=".$POST['bill_adjust_voucher_type']." and isdelete=0 and bill_table='tbl_journal' and bill_table_id=0 and user_id=".$_SESSION['user_id'] , $dbcon);
			}
				
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
			echo json_encode($arr);					
		 
		}		
		else if(strtolower($POST['mode']) == "edit") {

                $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
				
				$info['journal_no']		= $POST['journal_entry_no'];
				$info['journal_date']   = date('Y-m-d',strtotime($POST['journal_entry_date']));
				$info['gst_nature']		= $_POST['gst_nature'];
				//$info['remark']			= $POST['remark'];
				$info['cdate']			= date("Y-m-d H:i:s");
				$info['user_id']		= $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];
				$info['jv_remark'] = $POST['jv_remark'];
                $updateid=update_record('tbl_journal', $info,"journal_id=".$POST['journal_id'] , $dbcon, $POST['branch_id']);
			
				insert_general_book($dbcon,$POST['journal_id'],$branch_id);	

				if(isset($POST['save_print']))
				{
					$arr['printstatus']=$POST['print_status'];
					$arr['msg']="update";
					$arr['eid']=$POST['eid'];
				}
				else
				{
					if($updateid)
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
			
			$query="select * from  tbl_journal_trn as mst 
			where journal_trn_status=0 and mst.journal_id=".$POST['eid'];
                        $result=$dbcon->query($query);
			$table_name="tbl_journal_trn";
			while($rel=mysqli_fetch_assoc($result))
			{
				$general_book_id=get_general_book_id($dbcon,$table_name,$rel['journal_trn_id'],$rel['ledger_id']);
				
				$info12['genral_book_status']		= 2;
				$update=update_record('tbl_general_book', $info12,"general_book_id=".$general_book_id, $dbcon);
				
			}
			
			$info['journal_status']		= 2;
			$info1['journal_trn_status']		= 2;
			$update=update_record('tbl_journal', $info,"journal_id=".$POST['eid'], $dbcon);	
			$updatepurchaseid=update_record('tbl_journal_trn', $info1,"journal_id=".$POST['eid'], $dbcon);
			
			if($update)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
            $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$info1['entry_type']	= $POST['entry_type'];
			//$info1['description']		= text_rnremove($_POST['product_des']);
			$info1['ledger_id']		= $POST['ledger_id'];
			$info1['amount']		= $POST['amount'];
			$info1['user_id']		= $_SESSION['user_id'];
			$info1['cdate']			= date("Y-m-d H:i:s");
			$info1['company_id']	= $_SESSION['company_id'];
			$info1['tds_per'] = $POST['tds_per'];

		//echo '<pre>';print_r($info1);exit;
			$table='tbl_journal_trn';
			$tableid='journal_trn_id';
			if(!empty($POST['journal_id']))
			{
				$info1['journal_id']= $POST['journal_id'];
			}
			else
			{
				$info1['journal_trn_status']	= 3;
			}
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
				if($POST['istds'] == 'yes'){
					$info_tds['ref_id']=$inserid+1;
					update_record('tbl_journal_trn', $info_tds,"journal_trn_id=".$inserid , $dbcon);
				}
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);

				if(!empty($POST['receiptid'])){
					$info1['entry_type'] = $POST['entry_type'];
					$info1['ledger_id'] = $POST['ledger_id'];
					$info1['amount'] = $POST['amount'];
					$updategeneid=update_record('tbl_general_book', $info1,"table_id=".$POST['edit_id']." and table_name='tbl_journal_trn' " , $dbcon);
				}
			}
			//var_dump($info1);
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			if(!empty($POST['journal_id'])){
				$query="select mst.*,led.l_name as ledger_name,led.l_id,bt.balance_type_name
				from  tbl_journal_trn as mst 
				left join tbl_ledger as led on led.l_id=mst.ledger_id
				left join mst_balance_type as bt on bt.balance_typeid=mst.entry_type
				where journal_trn_status=0 and journal_id=".$POST['journal_id'];
			}else{
				$query="select mst.*,led.l_name as ledger_name,led.l_id,bt.balance_type_name
				from  tbl_journal_trn as mst 
				left join tbl_ledger as led on led.l_id=mst.ledger_id
				left join mst_balance_type as bt on bt.balance_typeid=mst.entry_type
				where journal_trn_status=3 and mst.user_id=".$_SESSION['user_id'];
			}

		    $result=$dbcon->query($query);
			
			echo ' <div class="form-group">
					<div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="table12 display table  table-striped table-bordered">
						<tr id="field">
							<th class="text-center" width="15%">Entry Type</th>
							<th class="text-center"width="25%">Ledger Name</th>
							<th class="text-center"width="15%">Cr Amount <span class="currency_icon"></label></th>
							<th class="text-center"width="15%">Dr Amount <span class="currency_icon"></label></th>
							<th class="text-center"width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;$dr_amount=0;$cr_amount=0;
                        $edit_btn = $delete_btn = '';
			while($rel=mysqli_fetch_assoc($result))
			{
				$refid=$rel['ref_id'];
				if($refid!=0){
					$refid1 = $refid;
				}

			 echo '<tr id="fieldtr'.$id.'" >
			 		<input type="hidden" class="fieldcount" />
					<td data-label="Entry Type" style="vertical-align:top;text-align:left">
						'.$rel['balance_type_name'].'
					</td>
					<td data-label="Ledger Name" style="vertical-align:top;" class="text-center">
						'.$rel['ledger_name'].'
					
					</td><input type="hidden" name="entry_hid_type[]" id="entry_hid_type" value="'.$rel['balance_type_name'].'" />		
					</td><input type="hidden" name="ledger_hid_id[]" id="ledger_hid_id" value="'.$rel['l_id'].'" />
					</td><input type="hidden" name="amount_hid_id[]" id="amount_hid_id" value="'.$rel['amount'].'" />';

					if($rel['entry_type']=="1"){
						echo '<td data-label="Cr Amount" style="vertical-align:top;"class="text-center">
						'.$rel['amount'].'
						</td>';
					}else{
						echo '<td data-label="Cr Amount" style="vertical-align:top;height: 35px;"class="text-center">
						</td>';
					}
					if($rel['entry_type']=="2"){
						echo '<td data-label="Dr Amount" style="vertical-align:top;" class="text-center">
						'.$rel['amount'].'
						</td>';
					}else{
						echo '<td data-label="Dr Amount" style="vertical-align:top;height: 35px;" class="text-center">
						</td>';
					}
					
					if((!empty($refid) || $refid1 == $rel['journal_trn_id']) || ($POST['gst_nature'] == 94)){
						$edit_btn='';
					}else{

	                    if(in_array(FINANCE_JOURNAL_EDIT,$bulkAccessArray)){
	                        $edit_btn = '<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['journal_trn_id'].');" id="fieldr'.$i.'"><i class="fa fa-pencil"></i></button>';
	                    }
	                }

                    if(in_array(FINANCE_JOURNAL_DELETE,$bulkAccessArray)){
                        $delete_btn = '<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['journal_trn_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>';
                    }
                                        
					echo '<td data-label="Action" style="vertical-align:top">
							'.$edit_btn.'&nbsp;'.$delete_btn.'
                                            </td>	
					</tr>';
					if($rel['entry_type']=="1"){
						$cr_amount+=$rel['amount'];
					}else{
						$cr_amount=$cr_amount;
					}
					if($rel['entry_type']=="2"){
						$dr_amount+=$rel['amount'];
					}else{
						$dr_amount=$dr_amount;
					}
					
			$i++;
			}
			echo '<tr>
					<td data-label="" colspan="2" style="vertical-align:top;"class="text-center"></td>
					<td  data-label="Total Cr Amount" style="vertical-align:top;"class="text-center">'.number_format($cr_amount,2,".","").'</td>
					<td data-label="Total Dr Amount" style="vertical-align:top;"class="text-center">'.number_format($dr_amount,2,".","").'</td>
					<td style="vertical-align:top;"class="text-center"></td>
				</tr>
				<input type="hidden" name="cr_amount" id="cr_amount" value="'.number_format($cr_amount,2,".","").'" />
				<input type="hidden" name="dr_amount" id="dr_amount" value="'.number_format($dr_amount,2,".","").'" />
			';
		}
		else{
		echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '
	 
		</table>			 
							</div>
                           
							</div>	';
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			
			$q = $dbcon -> query("SELECT * FROM tbl_journal_trn WHERE journal_trn_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
			//var_dump($r);
		}
		else if(strtolower($POST['mode'])=="delete_data")
		{
			$row=array();
			$info['journal_trn_status']=2;	
			//Added by dhruv 21-12-2021
			$q = $dbcon -> query("SELECT * FROM tbl_journal_trn WHERE ref_id = ".$POST['eid']." and (journal_trn_status=3 or journal_trn_status=0) ");
			$r = brp_mysqli_fetch_assoc($q);
			if(!empty($r['ref_id'])){
				$updaterefid=update_record('tbl_journal_trn', $info,"journal_trn_id=".$r['journal_trn_id'] , $dbcon);
			}
			//end code
			$updateid=update_record("tbl_journal_trn", $info,"journal_trn_id=".$POST['eid'] , $dbcon);
			
			$info1['genral_book_status']=2;	
			$updateid1=update_record("tbl_general_book", $info1," table_name='tbl_journal_trn' and table_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "delete_all_journal_entry") {

			$info['isdelete']='1';
			$info1['journal_trn_status']=2;
			
			if(empty($POST['journal_id'])){
				$updateid=update_record('tbl_journal_trn', $info1,"journal_id=0 and trn_entry_type=0", $dbcon);		
				$updategovpayid=update_record('tbl_payment_to_govt', $info,"voucher_id=0 and receipt_id=0 and voucher_table='tbl_journal' and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
				$updatecrdrid=update_record('tbl_cr_dr_adjustment', $info,"voucher_id=0 and voucher_table='tbl_journal' and voucher_type=".$POST['bill_voucher_type']." and entry_type=0 " , $dbcon);
				$updateregexpid=update_record('tbl_registered_expense', $info,"voucher_id=0 and receipt_id=0 and voucher_table='tbl_journal' and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);

			}
			
		}else if(strtolower($POST['mode']) == "delete_all_journal_expence_entry") {
			$info['isdelete']='1';
			$info1['journal_trn_status']=2;
			if(empty($POST['journal_id'])){
				$updateid=update_record('tbl_journal_trn', $info1,"journal_id=0 and trn_entry_type=0", $dbcon);	
				$updateregexpid=update_record('tbl_registered_expense', $info,"voucher_id=0 and receipt_id=0 and voucher_table='tbl_journal' and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
			}else{
				$updateid=update_record('tbl_journal_trn', $info1,"journal_id=".$POST['journal_id']." and trn_entry_type=0", $dbcon);	
				$updateregexpid=update_record('tbl_registered_expense', $info," receipt_id=".$POST['journal_id']." and voucher_table='tbl_journal' and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
			}
		}
		else if(strtolower($POST['mode'])== "add_genral_book"){
			$uid=insert_general_book($dbcon,$POST['journal_id']);
			//var_dump($uid);
		}else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=18 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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
		else if(strtolower($POST['mode'])== "get_check_trn_entry")
		{
			if($POST['journal_id'] ==""){
				$query="select count(journal_id) as trn_count from tbl_journal_trn where journal_id=0 and journal_trn_status=3 and trn_entry_type=0 and company_id=".$_SESSION['company_id'];
			}else{
				$query="select count(journal_id) as trn_count from tbl_journal_trn where journal_trn_status=0 and trn_entry_type=0 and journal_id=".$POST['journal_id'];
			}
			//var_dump($query);
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "check_bill_by_bill"){
			$ledger = $POST['ledger'];
			$q = $dbcon->query("select * from tbl_ledger where l_group IN (37,38) and l_id='$ledger'");
			echo brp_mysqli_num_rows($q);

		}
		else if(strtolower($POST['mode'])== "check_pl_ledger"){

			$ledger_id = $POST['ledger_id'];

			$pal_query = $dbcon->query("select opn_balance,balance_typeid  from tbl_ledger where l_id='$ledger_id' and l_group='".PROFIT_LOSS."'");
			if(brp_mysqli_num_rows($pal_query)>0)
			{
				$ral_query = brp_mysqli_fetch_assoc($pal_query);
				if($ral_query['balance_typeid']==1)
				{
					$opening = $ral_query['opn_balance'];	
				}
				else
				{
					$opening = -($ral_query['opn_balance']);
				}
				

				$gb_query = $dbcon->query("SELECT (select IFNULL(sum(amount),0) from tbl_general_book where entry_type=1 and ledger_id='$ledger_id' and genral_book_status=0) as cr_amount,(select IFNULL(sum(amount),0) from tbl_general_book where entry_type=2 and ledger_id='$ledger_id'  and genral_book_status=0 ) as db_amount FROM `tbl_general_book` where ledger_id='$ledger_id'");
				$rgb_query = brp_mysqli_fetch_assoc($gb_query);
				$credit = $rgb_query['cr_amount'];
				$debit = $rgb_query['db_amount'];

				$total=($opening)+($credit-$debit);
			}
			else
			{
				$total = '';
			}
			echo $total;


		}

function insert_general_book($dbcon,$journal_id, $branch_id)
{
	$qry122="select * from tbl_journal as cert where journal_status=0 and journal_id=".$journal_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$query="select * from  tbl_journal_trn as mst 
			where journal_trn_status=0 and mst.journal_id=".$journal_id;
		    $result=$dbcon->query($query);
			$table_name="tbl_journal_trn";
			while($rel=mysqli_fetch_assoc($result))
			{
				$general_book_id=get_general_book_id($dbcon,$table_name,$rel['journal_trn_id'],$rel['ledger_id']);
				
				$info1['ref_date']		= date("Y-m-d",strtotime($rea['journal_date']));
				$info1['table_name']	= $table_name;
				$info1['table_id']		= $rel['journal_trn_id'];
				$info1['entry_type']	= $rel['entry_type'];
				$info1['ledger_id']		= $rel['ledger_id'];
				$info1['amount']		= $rel['amount'];
				$info1['user_id']		= $_SESSION['user_id'];
				$info1['cdate']			= date("Y-m-d H:i:s");
				$info1['company_id']	= $_SESSION['company_id'];
				
				if(empty($general_book_id)){
					$inserid_gen=add_record("tbl_general_book", $info1, $dbcon, $branch_id);
				}else{
					$updateid=update_record('tbl_general_book', $info1,"general_book_id=".$general_book_id , $dbcon, $branch_id);
				}
				
				//$inserid=add_record("tbl_general_book", $info1, $dbcon);
			}
}		
?>