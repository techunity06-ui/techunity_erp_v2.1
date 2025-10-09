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
        $branch_id = $POST['branch_id'];
        
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
		$aColumns = array('journal_id','journal_no','journal_date','remark','journal_status','jo.cdate','jo.user_id');
		$sIndexColumn = "journal_id";
		$isWhere = array("jo.entry_type=1 and jo.journal_status = 0 ".$where);
		$sTable = "tbl_journal as jo";			
		$isJOIN = array();
		$hOrder = "jo.journal_date desc";
		include($path.'include/pagging.php');
		//$appData = array();
		$id=1;
                    $edit = $delete = ''; 
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['journal_no'];
			$row_data[] = date('d M, Y',strtotime($row['journal_date']));
			$addpayment='';$delete='';$edit='';
				
            if(in_array(FINANCE_JOURNAL_DELETE,$bulkAccessArray)){
                $delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_invoice('.$row['journal_id'].')"><i class="fa fa-trash-o"></i></button>';
            }
            //$view='<a class="btn btn-xs btn-info" data-original-title="View" data-toggle="tooltip" data-placement="top" href="'.ROOT.'purchase_view/'.$row['po_id'].'"><i class="fa fa-eye"></i></a> ';

            if(in_array(FINANCE_JOURNAL_EDIT,$bulkAccessArray)){
                $edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'credit_note_without_itm_edit/'.$row['journal_id'].'"><i class="fa fa-pencil"></i></a>';
            }
            $row_data[] = $edit.' '.$delete.' '.$view;
			 
			$appData[] = $row_data;
			$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			
			//echo '<pre>';print_r($deb_cre_details_r);exit;

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
			$info['entry_type']	= 1;
			//var_dump($info);
			$inserpoid=add_record('tbl_journal',array_merge($info,$curncy_trn), $dbcon, $POST['branch_id']);

			//Sales Return Table Entry
			$deb_cre_details_r = brp_mysqli_fetch_assoc($dbcon -> query("SELECT * FROM `tbl_cr_dr_adjustment` where voucher_id=0 and entry_type=1 and isdelete=0"));

			$info1['sales_ledger_id'] = $POST['sales_ledger_id'];
			$info1['sale_return_branch'] = $branch_id;

			$info1['sal_return_voucher_no']		= $POST['journal_entry_no'];
			$info1['sale_return_date']	= date('Y-m-d',strtotime($POST['journal_entry_date']));
			$info1['cdate']			= date("Y-m-d H:i:s");
			$info1['user_id']		= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['sale_return_currency_enable']	= $_POST['currency_enable'];
			
			$info1['sale_return_customer'] = $_POST['ledger_hid_id'][0];
			$info1['sale_return_sale_invoice_no'] = $deb_cre_details_r['adjust_invoice'];
			$info1['sale_return_total'] = $deb_cre_details_r['adjsut_diff'];
			$info1['sale_return_gtotal'] = $deb_cre_details_r['adjsut_diff'];

			$info1['is_without_item']	= 1;

			//var_dump($info);
			$insersaleid=add_record('tbl_sale_return',array_merge($info1,$curncy_trn), $dbcon,'');
				
				$info_trn['journal_id']	= $inserpoid;
				$info_trn['journal_trn_status']= 0;
			$updateid=update_record('tbl_journal_trn',array_merge($info_trn,$curncy_trn),"journal_trn_status=3 and user_id=".$_SESSION['user_id'] ,$dbcon, $POST['branch_id']);
			
			insert_general_book($dbcon,$inserpoid, $POST['branch_id']);

			/*Update Credit debit note trn in tbl_cr_dr_adjustment Table Start by Dhruv*/
			if(($inserpoid) && ($_POST['gst_nature'] == 86 || $_POST['gst_nature'] == 100)){
				$cr_dr['voucher_id']	= $inserpoid;
				$updatecrdrid=update_record('tbl_cr_dr_adjustment',array_merge($cr_dr,$curncy_trn),"voucher_id=0 and voucher_table='tbl_journal' and entry_type=1  and user_id=".$_SESSION['user_id'] , $dbcon,'');
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
			
			//Currency converter
			if(isset($POST['currency_enable'])){
		    	$curncy_trn['currency_id'] = $POST['currency_id'];
		    	$curncy_trn['currency_rate'] = $POST['currency_rate'];
		    }else{
		    	$basecurrency = getbasecurrency($dbcon);
		    	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
		    	$curncy_trn['currency_rate'] = 1;
		    }

		    $info['journal_no']		= $POST['journal_entry_no'];
			$info['journal_date']	= date('Y-m-d',strtotime($POST['journal_entry_date']));
			$info['remark']			= $POST['remark'];
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$info['currency_enable']	= $_POST['currency_enable'];
			$info['gst_nature']	= $_POST['gst_nature'];
			$info['entry_type']	= 1;			 
           
            $updateid=update_record('tbl_journal', $info,"journal_id=".$POST['journal_id'] , $dbcon, $POST['branch_id']);
			
			if($updateid)
			{	
				$arr['msg']="update";
				
			}
			else{
				$arr['msg']=0;
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
			$info1['trn_entry_type'] = 1;
			
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
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);
			}
			//var_dump($info1);
		}
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			if(!empty($POST['journal_id'])){
				$query="select mst.*,led.l_name as ledger_name,led.l_id,bt.balance_type_name
				from  tbl_journal_trn as mst 
				left join tbl_ledger as led on led.l_id=mst.ledger_id
				left join mst_balance_type as bt on bt.balance_typeid=mst.entry_type
				where journal_trn_status=0 and trn_entry_type=1 and journal_id=".$POST['journal_id'];
			}else{
				$query="select mst.*,led.l_name as ledger_name,led.l_id,bt.balance_type_name
				from  tbl_journal_trn as mst 
				left join tbl_ledger as led on led.l_id=mst.ledger_id
				left join mst_balance_type as bt on bt.balance_typeid=mst.entry_type
				where journal_trn_status=3 and trn_entry_type=1 and mst.user_id=".$_SESSION['user_id'];
			}
		    $result=$dbcon->query($query);
			
			echo ' <div class="form-group">
					<div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="table12 display table  table-striped table-bordered">
						<tr id="field">
							<th class="text-center" width="15%">Entry Type</th>
							<th class="text-center"width="25%">Ledger Name</th>
							<th class="text-center"width="15%">Cr Amount</th>
							<th class="text-center"width="15%">Dr Amount</th>
							<th class="text-center"width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;$dr_amount=0;$cr_amount=0;
                        $edit_btn = $delete_btn = '';
			while($rel=mysqli_fetch_assoc($result))
			{
			 echo '<tr id="fieldtr'.$id.'" >
					<td data-label="Entry Type" style="vertical-align:top;text-align:left">
						'.$rel['balance_type_name'].'
					</td>
					<td data-label="Ledger Name" style="vertical-align:top;" class="text-center">
						'.$rel['ledger_name'].'
							
					</td>
					<input type="hidden" name="ledger_hid_id[]" id="ledger_hid_id" value="'.$rel['l_id'].'" />';
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

                    if(in_array(FINANCE_JOURNAL_EDIT,$bulkAccessArray)){
                        $edit_btn = '<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['journal_trn_id'].');" id="fieldr'.$i.'"><i class="fa fa-pencil"></i></button>';
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
					<td  data-label="Total Cr Amount" style="vertical-align:top;"class="text-center">'.$cr_amount.'</td>
					<td data-label="Total Dr Amount" style="vertical-align:top;"class="text-center">'.$dr_amount.'</td>
					<td style="vertical-align:top;"class="text-center"></td>
				</tr>
				<input type="hidden" name="cr_amount" id="cr_amount" value="'.$cr_amount.'" />
				<input type="hidden" name="dr_amount" id="dr_amount" value="'.$dr_amount.'" />
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
			$updateid=update_record("tbl_journal_trn", $info,"journal_trn_id=".$POST['eid'] , $dbcon);
			
			$info1['genral_book_status']=2;	
			$updateid1=update_record("tbl_general_book", $info1," table_name='tbl_journal_trn' and table_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "add_genral_book"){
			$uid=insert_general_book($dbcon,$POST['journal_id']);
			//var_dump($uid);
		}else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=25 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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