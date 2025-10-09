<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
	
include("../../config/image.php");
$image = new SimpleImage();
							
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
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			$where ="  and payment_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND payment_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			
			$appData = array();
			$i=1;
			$aColumns = array('payment_mstid','payment_date','paymentno','referenceno', 'party.l_name','payment.payment_mode', 'receipt.amount','receipt.cdate','receipt.credits','receipt.partyid','pay.chequegenerateid','pay.generat_status','receipt.user_id');
			$sIndexColumn = "payment_mstid";
			$isWhere = array("receipt.mst_status = 0 and receipt.typeid=2 ".$where);
			$sTable = "payment_mst as receipt";			
			$isJOIN = array('inner join tbl_ledger party on party.l_id=receipt.partyid','left join tbl_payment_mode payment on payment.paymentmodeid=receipt.payment_mode','left join tbl_payment_cheque_generate as pay on pay.purchase_payid=payment_mstid and generat_status=0');
			$hOrder = "receipt.payment_mstid desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $id;
				$row_data[] = date('d-m-Y',strtotime($row['payment_date']));
				$row_data[] = $row['paymentno'];
				$row_data[] = $row['referenceno'];
				 $row_data[] = $row['l_name'];
				$row_data[] = $row['payment_mode'];
				$row_data[] = $row['amount'];
				$btn='';
				
				 
				$row_data[] = ' <a class="btn btn-xs btn-warning" data-original-title="Receipt Update" data-toggle="tooltip" data-placement="top" href="'.ROOT.'receipt-update/'.$row['payment_mstid'].'"><i class="fa fa-pencil"></i></a>
				       <a class="btn btn-xs btn-info" data-original-title="Receipt Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'receipt_invoicepayment/'.$row['payment_mstid'].'"><i class="fa fa-print"></i></a>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payment('.$row['payment_mstid'].')"><i class="fa fa-trash-o"></i></button>'.$btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {	
            $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoice_type =8");
			
            $info['partyid']			= $POST['partyid'];
			$info['accountid']			= $POST['accountid'];
			$info['payment_date']		= date('Y-m-d',strtotime($POST['payment_date']));
			$info['payment_mode']		= $POST['paymentmodeid'];
			$info['amount']				= $POST['paid_amount'];
			$info['referenceno']		= $POST['referenceno'];
			$info['used_amount']		= $POST['total_used_payment'];
			$info['credits']			= $POST['total_excess_payment'];
			$info['tax_deducted_flag']	= $POST['tax_deducted_flag'];
			$info['notes']				= $POST['notes'];
			$info['typeid']				= 2;//credit
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['company_id']			= $_SESSION['company_id'];
        
             $info['paymentno']= $POST['receiptid'];//paymentno($dbcon,$paymentno,$invoicetype=8);
			$infotrn['payment_mstid']=$paymentid=add_record("payment_mst",$info,$dbcon);
      
			for($i=0;$i<count($POST['trn_id']);$i++)
			{
				if($POST['bill_paid_amount'][$i]>0)
				{
					$infotrn['bill_id']		= $POST['trn_id'][$i];
					$infotrn['bill_type']	= $_POST['trn_type'][$i];
					$infotrn['paid_amount']	= $POST['bill_paid_amount'][$i];
					$infotrn['tax_amount']	= $POST['tax_amount'][$i];
					$infotrn['total_amount']= $POST['bill_paid_amount'][$i]+$POST['tax_amount'][$i];
					$insertid=add_record("payment_trn",$infotrn,$dbcon);
					if($insertid)
						payment_trn_entry($infotrn,$dbcon,strtolower($POST['mode']));//update in po,expense table
				}
			}
			/*if($info2['paymentmodeid']==2 )//if cheque select then
			{//insert cheque generate record
				//$query_from = $dbcon->query("UPDATE account_mst SET acc_chequeno = acc_chequeno + 1 WHERE acc_id = ".$infopbk['acc_id']);
				//$query_from = $dbcon->query("UPDATE account_mst SET acc_chequeleft = acc_chequeleft - 1 WHERE acc_id = ".$infopbk['acc_id']);
				$info_gen['acc_id']			= $infopbk['acc_id'];
				$info_gen['amount']			= $infopbk['amount'];
				$info_gen['cheque_date']	= $infopbk['reference_date'];
				$info_gen['cheque_num']		= $POST['cheque_dtl'];
				$info_gen['vender_id']		= $rel['vender_id'];
				$info_gen['purchase_payid'] = $insertreceiptid;
				$info_gen['generat_status'] = 0;// for cheque generate
				$info_gen['company_id']		= $_SESSION['company_id'];
				$insert_cheque=add_record('tbl_payment_cheque_generate', $info_gen, $dbcon);
			}*/
			if($POST['save_cheque']=="1")
			{
				$arr['msg']="1";
				$arr['eid']=$insertreceiptid;
				$arr['cheque_genid']=$insert_cheque;
			}
			else
			{
				if($paymentid)
				{	
					$arr['msg']="1";
                  
				}
				else
					$arr['msg']="0";
			}
			echo json_encode($arr);
				
			
		}
		else if(strtolower($POST['mode']) == "edit") {
			
			//$info['paymentno']			= '';
			$info['partyid']			= $POST['partyid'];
			$info['accountid']			= $POST['accountid'];
			$info['payment_date']		= date('Y-m-d',strtotime($POST['payment_date']));
			$info['payment_mode']		= $POST['paymentmodeid'];
			$info['amount']				= $POST['paid_amount'];
			$info['referenceno']		= $POST['referenceno'];
			$info['used_amount']		= $POST['total_used_payment'];
			$info['credits']			= $POST['total_excess_payment'];
			$info['notes']				= $POST['notes'];
			$info['tax_deducted_flag']	= $POST['tax_deducted_flag'];
			$info['typeid']				= 2;//credit
			$info['mdate']				= date('Y-m-d H:i:s');
			$paymentid=update_record("payment_mst",$info,"payment_mstid=".$POST['eid'],$dbcon);
			$infotrn['payment_mstid']=$POST['eid'];
			for($i=0;$i<count($POST['trn_id']);$i++)
			{
				if($POST['bill_paid_amount'][$i]>0 || !empty($POST['payment_trnid'][$i]))
				{
					unset($infotrn['old_amount']);
					$infotrn['bill_id']		= $POST['trn_id'][$i];
					$infotrn['bill_type']	= $_POST['trn_type'][$i];
					$infotrn['paid_amount']	= $POST['bill_paid_amount'][$i];
					$infotrn['tax_amount']	= $POST['tax_amount'][$i];
					$infotrn['total_amount']= $POST['bill_paid_amount'][$i]+$POST['tax_amount'][$i];
					if(!isset($POST['payment_trnid'][$i]))
						$insertid=add_record("payment_trn",$infotrn,$dbcon);
					else
						$insertid=update_record("payment_trn",$infotrn,"payment_trnid=".$POST['payment_trnid'][$i],$dbcon);
					
					$infotrn['old_amount']=$POST['old_amount'][$i]+$POST['old_tax_amount'][$i];
					if($insertid)
						payment_trn_entry($infotrn,$dbcon,strtolower($POST['mode']));//update in po,expense table
				}
			}			
			if($POST['save_cheque']=="1")
			{
				$arr['msg']="1";
				$arr['eid']=$insertreceiptid;
				$arr['cheque_genid']=$insert_cheque;
			}
			else
			{
				if($paymentid || $insertid)
				{	
					$arr['msg']="1";							
				}
				else
					$arr['msg']="0";
			}
			echo json_encode($arr);
				
			
		}
		else if(strtolower($POST['mode']) == "credit_update") {
			$info['credits']=$POST['total_excess_payment'];
			$query="update payment_mst set used_amount=used_amount+".$POST['total_used_payment']." , credits=".$POST['total_excess_payment']." where payment_mstid=".$POST['payment_mstid'];
			$rs=$dbcon->query($query);
			//$paymentid=update_record("payment_mst",$info,"payment_mstid=".$POST['payment_mstid'],$dbcon);
			$infotrn['payment_mstid']		= $POST['payment_mstid'];
			for($i=0;$i<count($POST['trn_id']);$i++)
			{
				if($POST['bill_paid_amount'][$i]>0 )
				{
					$infotrn['bill_id']		= $POST['trn_id'][$i];
					$infotrn['bill_type']	= $_POST['trn_type'][$i];
					$infotrn['paid_amount']	= $POST['bill_paid_amount'][$i]+$POST['old_amount'][$i];
					$infotrn['tax_amount']	= $POST['tax_amount'][$i];
					$infotrn['total_amount']= $POST['bill_paid_amount'][$i]+$POST['tax_amount'][$i]+$POST['old_amount'][$i];
					if(empty($POST['payment_trnid'][$i]))
						$insertid=add_record("payment_trn",$infotrn,$dbcon);
					else
						$insertid=update_record("payment_trn",$infotrn,"payment_trnid=".$POST['payment_trnid'][$i],$dbcon);
					
					$infotrn['old_amount']=intval($POST['old_amount'][$i]);
					if($insertid)
						payment_trn_entry($infotrn,$dbcon,strtolower($POST['mode']));//update in po,expense table
					
				}
			}
			
			exit;
		}
		else if(strtolower($POST['mode']) == "delete") {
			
			$qry="select * from payment_trn where payment_mstid=".$POST['eid'];
			$rs_payment=$dbcon->query($qry);
			if(mysqli_num_rows($rs_payment)>0)
			{
				while($rel=mysqli_fetch_assoc($rs_payment))
				{
					$info['bill_type']=$rel['bill_type'];
					$info['old_amount']=$rel['total_amount'];
					$info['bill_id']=$rel['bill_id'];
					payment_trn_entry($info,$dbcon,$mode);
				}
			}
			$infomst['mst_status']		= 2;
			$updateid=update_record('payment_mst', $infomst,"payment_mstid=".$POST['eid'] , $dbcon);			
            			
			if($updateid )
				echo "1";	
			else
				echo "0";			
		}

        else if(strtolower($POST['mode']) == "get_opn_bal") {
			$acc_id=$POST['acc_id'];
			if($acc_id==0)
			{	
				$qry="select acc_id,acc_type from account_mst where acc_type=1 and acc_status=0 and company_id=".$_SESSION['company_id'];
				$rel=mysqli_fetch_assoc($dbcon->query($qry));	
				$acc_id=$rel['acc_id'];
			}
			else
			{
					$qry="select acc_id,acc_type from account_mst where  acc_status=0 and company_id=".$_SESSION['company_id']." and acc_id=".$acc_id;
					$rel=mysqli_fetch_assoc($dbcon->query($qry));	
					$acc_id=$rel['acc_id'];
			
			}
			echo get_opening_balance($acc_id,$dbcon,$rel['acc_type']);
		}
		else if(strtolower($POST['mode']) == "load_data") {
			if(strtolower($POST['emode'])=="edit")
			{				
				$arr=paymetn_trn_edit_data($POST,$dbcon);//get payment data and invoice and income id
				$invoice_where=$income_where='';
				if(!empty($arr['invoice'][0]))
					$invoice_where="and invoice_id not in (".implode(",",$arr['invoice']).")";	
				if(!empty($arr['income'][0]))
					$income_where="and incomeid not in (".implode(",",$arr['income']).") ";
				$query="(SELECT invoice_id,'invoice' as type,invoice_no,invoice_date,g_total,paid_amount FROM `tbl_invoice` as invoice where invoice.invoice_status=0 and invoice.cust_id=".$POST['partyid']." and g_total>paid_amount ".$invoice_where." )
					union all 
					(SELECT incomeid,'income' as type,invoice_no,income_date,g_total,paid_amount FROM `income_mst` as income where income.mst_status=0 and income.customerid=".$POST['partyid']." and income.g_total>income.paid_amount ".$income_where." )";
//echo $query;				
$str=$arr['data'];
				$i=count($arr['invoice'])+count($arr['income']);
				$i++;
			}
			else if(strtolower($POST['emode'])=="add")
			{
				$query="select * from ((SELECT invoice_id,'invoice' as type,invoice_no,invoice_date,g_total,paid_amount FROM `tbl_invoice` as invoice where invoice.invoice_status=0 and invoice.cust_id=".$POST['partyid']." and g_total>paid_amount) 
				union all (SELECT incomeid,'income' as type,invoice_no,income_date,g_total,paid_amount FROM `income_mst` as income where income.mst_status=0 and income.customerid=".$POST['partyid']." and income.g_total>income.paid_amount)) as tbl order by tbl.invoice_date";
				$arr['status']=0;
				$str='';$i=1;
			}
			else if(strtolower($POST['emode'])=="credit")
			{
				$query="select tbl.*,trn.payment_trnid from ((SELECT invoice_id,'invoice' as type,invoice_no,invoice_date,g_total,paid_amount FROM `tbl_invoice` as invoice where invoice.invoice_status=0 and invoice.cust_id=".$POST['partyid']." and g_total>paid_amount) 
				union all (SELECT incomeid,'income' as type,invoice_no,income_date,g_total,paid_amount FROM `income_mst` as income where income.mst_status=0 and income.customerid=".$POST['partyid']." and income.g_total>income.paid_amount)) as tbl
					left join payment_trn as trn on trn.bill_id=tbl.invoice_id and trn.bill_type=cast(tbl.type as CHAR)
					group by tbl.invoice_id,tbl.type
					order by tbl.invoice_date";
			}			
				$rs_payment_data=$dbcon->query($query);
				
				if(mysqli_num_rows($rs_payment_data)>0)
				{
					
					while($rel=mysqli_fetch_assoc($rs_payment_data))
					{
						$due_amount=$rel['g_total']-$rel['paid_amount'];
						$str.='<tr>	
									<td class="col-md-2">'.date('d-m-Y',strtotime($rel['invoice_date'])).'</td>
									<td class="col-md-2">'.ucwords($rel['type']).'</td>
									<td class="col-md-2">'.$rel['invoice_no'].'</td>
									<td class="col-md-2 text-right">'.floatval($rel['g_total']).'</td>
									<td class="col-md-2 text-right">'.floatval($due_amount).'</td>
									<td class="col-md-1 text-right tax_deduct '.($_POST['tax_deduct']=="true"?'':'hidden').'"><input type="text" name="tax_amount[]" id="tax_amount'.$i.'" value="'.$rel['tax_amount'].'" class="form-control" onchange="check_due_and_use_amount('.$i.');" /></td>
									<td class="col-md-2 text-right">
										<input type="text" name="bill_paid_amount[]" id="bill_paid_amount'.$i.'" value="'.$rel['amount'].'" class="form-control" onchange="check_due_and_use_amount('.$i.');" /> 
										<input type="hidden" name="trn_id[]" value="'.$rel['invoice_id'].'" />
										<input type="hidden" name="trn_type[]" value="'.$rel['type'].'" /> 
										<input type="hidden" name="due_amount[]" id="due_amount'.$i.'" value="'.$due_amount.'" />
										<input type="hidden" name="payment_trnid[]" value="'.$rel['payment_trnid'].'" />
										<input type="hidden" name="old_amount[]" value="'.$rel['paid_amount'].'" />
									</td>	
								</tr>
							';
						$total+=$due_amount;$i++;	
					}
					$arr['data']=$str;
					$arr['status']=1;
					$arr['total']=$total;
				}
				echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "load_totaldata") {
			$qry="select* from tbl_pono where po_id=".$POST['purchasebill_id'];
			$total=mysqli_fetch_assoc($dbcon->query($qry));	
			echo json_encode($total);
		}
		else if(strtolower($POST['mode']) == "get_chequeno") {
			
			echo get_chequeno($POST['acc_id'],$dbcon);
		}		
		
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
function payment_trn_entry($info,$dbcon,$mode)
{
	$type=strtolower($info['bill_type']);
	$table_type_arr=array("invoice"=>array("table"=>"tbl_invoice","primaryid"=>"invoice_id","paid_amount"=>"paid_amount"),
	"income"=>array("table"=>"income_mst","primaryid"=>"incomeid","paid_amount"=>"paid_amount")); 
	if(!empty($info['old_amount']) && !empty($info['total_amount']))//amount=amount+paid-old editmode
	{
		$query="UPDATE ".$table_type_arr[$type]['table']." SET ".$table_type_arr[$type]['paid_amount']." = ".$table_type_arr[$type]['paid_amount']." + ".$info['total_amount']." - ".$info['old_amount']." WHERE ".$table_type_arr[$type]['primaryid']." = ".$info['bill_id'];	
		$query_from = $dbcon->query($query);
	}
	else if(empty($info['old_amount']) && !empty($info['total_amount']))//amount=amount+paid addmode
	{
			$query_from = $dbcon->query("UPDATE ".$table_type_arr[$type]['table']." SET ".$table_type_arr[$type]['paid_amount']." = ".$table_type_arr[$type]['paid_amount']." + ".$info['total_amount']." WHERE ".$table_type_arr[$type]['primaryid']." = ".$info['bill_id']);
	}
	else if(!empty($info['old_amount']) && !isset($info['total_amount']))//amount=amount-old deletemode
	{
		$query="UPDATE ".$table_type_arr[$type]['table']." SET ".$table_type_arr[$type]['paid_amount']." = ".$table_type_arr[$type]['paid_amount']." - ".$info['old_amount']." WHERE ".$table_type_arr[$type]['primaryid']." = ".$info['bill_id'];	
		$query_from = $dbcon->query($query);
	}		
	
}
function paymetn_trn_edit_data($POST,$dbcon)
{
	$query="SELECT ptrn.payment_trnid,ptrn.paid_amount as amount,ptrn.tax_amount,ptrn.total_amount,po_in.* FROM `payment_trn` as ptrn 
	left join ((SELECT invoice_id,'invoice' as type,invoice_no,invoice_date,g_total,paid_amount FROM `tbl_invoice` as invoice where invoice.invoice_status=0 and invoice.cust_id=".$POST['partyid'].") union all (SELECT incomeid,'income' as type,invoice_no,income_date,g_total,paid_amount FROM `income_mst` as income where income.mst_status=0 and income.customerid=".$POST['partyid']."))  as po_in on po_in.invoice_id=ptrn.bill_id and ptrn.bill_type=CAST(po_in.type as CHAR)
	where ptrn.payment_mstid=".$POST['eid']."  order by po_in.invoice_date";
	$rs_payment_data=$dbcon->query($query);
	$arr['status']=0;
	if(mysqli_num_rows($rs_payment_data)>0)
	{
		$str='';$i=1;
		while($rel=mysqli_fetch_assoc($rs_payment_data))
		{
			$due_amount=($rel['g_total']-$rel['paid_amount'])+($rel['amount']+$rel['tax_amount']);
			$str.='<tr>	
						<td class="col-md-2">'.date('d-m-Y',strtotime($rel['invoice_date'])).'</td>
						<td class="col-md-2">'.ucwords($rel['type']).'</td>
						<td class="col-md-2">'.$rel['invoice_no'].'</td>
						<td class="col-md-2 text-right">'.floatval($rel['g_total']).'</td>
						<td class="col-md-2 text-right">'.floatval($due_amount).'</td>
						<td class="col-md-1 text-right tax_deduct '.($_POST['tax_deduct']=="true"?'':'hidden').'"><input type="text" name="tax_amount[]" id="tax_amount'.$i.'" value="'.$rel['tax_amount'].'" class="form-control" onchange="check_due_and_use_amount('.$i.');" /> <input type="hidden" name="old_tax_amount[]" value="'.$rel['tax_amount'].'" /></td>
						<td class="col-md-2 text-right">
							<input type="text" name="bill_paid_amount[]" id="bill_paid_amount'.$i.'" value="'.$rel['amount'].'" class="form-control" onchange="check_due_and_use_amount('.$i.');" /> 
							<input type="hidden" name="trn_id[]" value="'.$rel['invoice_id'].'" />
							<input type="hidden" name="old_amount[]" value="'.$rel['amount'].'" />
							<input type="hidden" name="trn_type[]" value="'.$rel['type'].'" /> 
							<input type="hidden" name="due_amount[]" id="due_amount'.$i.'" value="'.$due_amount.'" />  
							<input type="hidden" name="payment_trnid[]" id="payment_trnid'.$i.'" value="'.$rel['payment_trnid'].'" />
						</td>	
					</tr>
				';
			$total+=$due_amount;$i++;
		if($rel['type']=="invoice")
				$arr['invoice'][]=$rel['invoice_id'];
		if($rel['type']=="income")
				$arr['income'][]=$rel['invoice_id'];
		}
		$arr['data']=$str;
		$arr['status']=1;
		$arr['total']=$total;
	}
	return $arr;
}

/*function paymentno($dbcon,$paymentno,$invoicetype)
{
  
    $row=array();
			$query1="select * from  tbl_invoicetype where invoice_type='".$invoicetype."' and company_id='".$_SESSION['company_id']."'";
            //echo $query1;
            $rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
           //$id=$rows['Max(taxinvoice_start)']+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2'){
				$info['paymentno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1'){
				$info['paymentno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$info['paymentno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
               
			}
			else{
				$info['paymentno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
  // var_dump($info);
      return $info['paymentno'];
}*/
?>
