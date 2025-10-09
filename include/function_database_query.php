<?php
function add_record($table, $data, $db, $branch_id = '')
{
	if($branch_id) {
		$data['branch_id'] = $branch_id;
	}

	//var_dump($data);
	foreach(array_keys($data) as $field_name) 
	{
		//var_dump($data[$field_name]);
		if(!empty($data[$field_name]) || $data[$field_name]=='0'){
			$data[$field_name]=$data[$field_name];
		}else{
			$data[$field_name]="";
		}
		$data[$field_name] = brp_sc_mysql_escape($data[$field_name],$db);
		if (!isset($field_string)) {
			$field_string = "".$field_name.""; 
			$value_string = "'$data[$field_name]'";
		}else{
			$field_string .= ",".$field_name."";
			$value_string .= ",'$data[$field_name]'";
		}
	}
	 $dbQuery = "INSERT INTO $table ($field_string) VALUES ($value_string)";

 

 	 // echo $dbQuery;echo "</br></br>"; 

	 // exit; 

	 // exit;


 	 /*echo $dbQuery;echo "</br></br>"; 
	 exit;*/

	 $db->query($dbQuery);
	
	$insert_id=brp_mysqli_insert_id($db);
	//echo $insert_id;
	if(isset($insert_id))
	{
		$_SESSION['msg']='Record Added Successfully';
	}
	return $insert_id;//return record number of the record just added, in case we need it
}


function bulk_add_record($table, $values, $fields, $db, $branch_id = '')
{
	$dbQuery = "INSERT INTO $table ($fields) VALUES " . implode(', ', $values);

	$db->query($dbQuery);
	
	$insert_id=brp_mysqli_insert_id($db);

	if(isset($insert_id))
	{
		$_SESSION['msg']='Record Added Successfully';
	}
	return $insert_id;//return record number of the record just added, in case we need it
}


function update_record($table, $data, $where, $db, $branch_id = '')
{	
	if($branch_id) {
		$data['branch_id'] = $branch_id;
	}

	foreach(array_keys($data) as $field_name){
		$data[$field_name] = brp_sc_mysql_escape($data[$field_name],$db);
		if (!isset($field_string)) {
			$field_string = " ".$field_name.""; 
			$value_string = "'$data[$field_name]'";
			$querystring=" set ".$field_string."=".$value_string;
		} else {
			$field_string = ",".$field_name."";
			$value_string = "'$data[$field_name]'";
			$querystring.=$field_string."=".$value_string;
		}
	}
	$dbQuery = "update ".$table.$querystring." Where ".$where;	

	// echo $dbQuery;echo "</br></br>"; 

 	//exit; 
	$db->query($dbQuery);
	//echo $dbQuery;exit;	
	$update_id=brp_mysqli_affected_rows($db);
	if(isset($update_id))
	{
		$_SESSION['msg']='Record Updated Successfully';
	}
	return $update_id;//return record number of the record just added, in case we need it
}
function delete_record($table, $where, $db)
{
	$dbQuery = "delete from ".$table." Where ".$where;	
	//echo $dbQuery;exit;
	$db->query($dbQuery);
	//$update_id = brp_mysqli_affected_rows($db);
	$update_id = brp_mysqli_affected_rows($db);
	return $update_id;	
}
function get_update_maxno($table, $db)
{
	$query='select maxno from tbl_maxno where tbl_name="'.$table.'"';
	$rs=($db->query($query));
	$rs=brp_mysqli_fetch_array($rs);
	$max_id=$rs['maxno']+1;
	$query='update tbl_maxno set maxno='.$max_id.' where tbl_name="'.$table.'"';
	$db->query($query);
	return $max_id;
}
function get_fieldname_id($type, $db)
{
	$query='select id,field_name FROM  `field_master` WHERE TYPE ="'.$type.'"';
	$rs=($db->query($query));
	//$field_arr=array();
	$field_arr=array();
	while($rel=brp_mysqli_fetch_array($rs))
	{
		$field_arr[$rel['id']]=$rel['field_name'];		
	}
	return $field_arr;
}
function  getfieldid_fromname($field_name,$fieldname_arr)
{
	return $key= array_search($field_name,$fieldname_arr);
}

function sc_mysql_escape($value,$db) {
	if (is_string($value));
	// strip out slashes IF they exist AND magic_quotes is on
	if (get_magic_quotes_gpc() && (strstr($value,'\"') || strstr($value,"\\'"))) $value = stripslashes($value);	
	// escape string to make it safe for mysql
	return @mysqli_real_escape_string($db,$value);
}

//Purpose: to call addslashes(), stripping slashes before only if necessary
function sc_php_escape($value) {
	if (is_string($value));
	// strip out slashes IF they exist AND magic_quotes is on
	if (get_magic_quotes_gpc() && (strstr($value,'\"') || strstr($value,"\\'"))) $value = stripslashes($value);	
	// escape string to make it safe for mysql
	return addslashes($value);
}
function updateopamount($poid,$oldpoid,$oldamount,$newanount,$dbcon)
{
		if($poid==$oldpoid)
		{
		$query_from = $dbcon->query("UPDATE tbl_pono SET bill_amount =(bill_amount - ".$oldamount.")+ ".$newanount." WHERE po_id = ".$poid);
		}
		else if($poid>0)
		{
			$query_from = $dbcon->query("UPDATE tbl_pono SET bill_amount =bill_amount + ".$newanount." WHERE po_id = ".$poid);
		}
		else
		{
		$query_from = $dbcon->query("UPDATE tbl_pono SET bill_amount =(bill_amount - ".$oldamount.") WHERE po_id = ".$oldpoid);		}	
		
		return $query_from;		
}
function add_tax_record($dbcon,$used_transaction_id,$table_name,$table_id,$formula_id,$taxableamount,$branch_id)

{	
	$info_del['tax_used_status']	= 2;
	$updateid1=update_record("tbl_used_tax",$info_del,"table_name='".$table_name."' and table_id='".$table_id."' and used_transaction_id=".$used_transaction_id, $dbcon);
	$str='';
	 $query="select * from formula_mst as pro where formula_status=0 and formulaid=".$formula_id." order by formulaid";
	$rs_dispatch=$dbcon->query($query);
	$rel=brp_mysqli_fetch_assoc($rs_dispatch);
	
	 $que="select * from tbl_tax as ta where tax_status=0 and tax_id in (".$rel['tax_id'].") order by tax_id";
	$rs_di=$dbcon->query($que);
	while($re=brp_mysqli_fetch_assoc($rs_di))
	{	
		if(!empty($re['tax_value'])){
			$tax_amount=($taxableamount)*$re['tax_value']/100;
			$info1['used_transaction_id']		= $used_transaction_id;
			$info1['tax_id']					= $re['tax_id'];
			$info1['table_name']				= $table_name;
			$info1['table_id']					= $table_id;
			$info1['tax_per']					= $re['tax_value'];
			$info1['ledger_id']					= $re['ledger_id'];
			$info1['tax_amount']				= $tax_amount;
			$info1['cdate']						= date("Y-m-d H:i:s");
			$info1['user_id']					= $_SESSION['user_id'];
			$info1['usertype_id']				= $_SESSION['user_type'];
			$info1['company_id']				= $_SESSION['company_id'];
			//var_dump($info1);
			$inserid=add_record("tbl_used_tax",$info1, $dbcon,$branch_id);

			$totaltax_amount+=$tax_amount;
			$totaltax_per+=$re['tax_value'];
		}
		//var_dump($re['tax_value']);		
	}
		
		/* $info_main['tax_per']	= $totaltax_per;
		$info_main['tax_per_id']	= $rel['tax_per_id'];
		$info_main['tax_amount']	= $totaltax_amount;
		$info_main['total']			= ($taxableamount+$totaltax_amount);
	$updateid1=update_record($table_name,$info_main,$table_id."=".$used_transaction_id, $dbcon); */
	//return 12;
	//var_dump("1234");
}


function add_general_book_entry($dbcon,$table_name,$table_id,$entry_type,$ledger_id,$amount,$general_book_id,$ref_date, $branch_id='',$currency_array = array(),$module_name="",$module_id=0,$ledger_id_ref=0)
{	
	$info_gen['table_name']		= $table_name;
	$info_gen['table_id']		= $table_id;
	$info_gen['entry_type']		= $entry_type;
	$info_gen['ref_date']		= date('Y-m-d',strtotime($ref_date));
	$info_gen['ledger_id']		= $ledger_id;
	if($ledger_id_ref !=0)
	{
		$info_gen['ledger_id_ref']	= $ledger_id_ref;
	}
	$info_gen['amount']			= $amount;
	
	$info_gen['module_name']	= $module_name;
	$info_gen['module_id']		= $module_id;
	
	$info_gen['user_id']		= $_SESSION['user_id'];
	$info_gen['cdate']			= date("Y-m-d H:i:s");
	$info_gen['company_id']		= $_SESSION['company_id'];

	if(empty($general_book_id)){
		$inserid_gen=add_record("tbl_general_book", array_merge($info_gen,$currency_array) , $dbcon, $branch_id);
	}else{
		$updateid=update_record('tbl_general_book', array_merge($info_gen,$currency_array),"general_book_id=".$general_book_id , $dbcon, $branch_id);
	}
}
function add_stock($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id,$perent_id,$reserve_id,$customer_id="",$batch_id="",$batch_no="",$base_rate="",$conv_rate="",$workorder_id=0){

	$que_stock="select * from tbl_stock_trn where stock_id=".$perent_id;
	$re_stock=$dbcon->query($que_stock);
	$res_stock=brp_mysqli_fetch_assoc($re_stock);

	if(brp_mysqli_num_rows($re_stock) > 0){
		if($customer_id == ""){
			$customer_id = $res_stock['customer_id'];
		}

		if($batch_id == ""){
			$batch_id = $res_stock['batch_id'];

		}
		if($batch_no == ""){
			$batch_no = $res_stock['batch_no'];
		}
	}

	if($stock_flag == '1' && $batch_id != "" && $batch_id > 0){
			$bt_qry = " SELECT mfg_date,exp_date FROM tbl_batch_data WHERE batch_id = " . $batch_id;
			$bt_row = brp_mysqli_fetch_assoc($dbcon->query($bt_qry));

			$info_gen['mfg_date'] = $bt_row['mfg_date'];
			$info_gen['exp_date'] = $bt_row['exp_date'];
		}else if($stock_flag == '1'){
			$info_gen['mfg_date'] = date("Y-m-d");
			$dt = get_exp_date_by_product($dbcon,$_POST['product_id'],date("d-m-Y"));
			$info_gen['exp_date'] = date('Y-m-d',strtotime($dt));	
		}

	$que="select * from product_mst as ta where product_id=".$product_id;
	$rs_di=$dbcon->query($que);
	$re=brp_mysqli_fetch_assoc($rs_di);

	if($re['product_conv_unit'] != $re['product_base_unit']){
		if(!empty($batch_id)){
			$s_que="select * from tbl_batch_data where batch_id=".$batch_id;
			$s_rs_di=$dbcon->query($s_que);
			$s_re=brp_mysqli_fetch_assoc($s_rs_di);
			
			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$stock_qty;
				// $base_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
				$base_stock = ($con_stock/$s_re['conv_qty']) * $s_re['base_qty'];
			}else{
				$type="conv_unit";
				$base_stock=$stock_qty;
				// $con_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
				$con_stock = ($base_stock/$s_re['base_qty']) * $s_re['conv_qty'];
			}
		}else{
			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$stock_qty;
				$base_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
			}else{
				$type="conv_unit";
				$base_stock=$stock_qty;
				$con_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
			}
		}
	}else{
		$base_stock=$stock_qty;
		$con_stock=$stock_qty;
	}
	
	
	$info_gen['stock_date']			= date('Y-m-d',strtotime($stock_date));
	$info_gen['product_id']			= $product_id;
	$info_gen['base_unit']			= $re['product_base_unit'];
	$info_gen['base_stock']			= $base_stock;
	$info_gen['convert_unit']		= $re['product_conv_unit'];
	$info_gen['convert_stock']		= $con_stock;
	$info_gen['stock_flage']		= $stock_flag;
	$info_gen['godown_id']			= $godown_id;
	$info_gen['ref_name']			= $ref_name;
	$info_gen['ref_id']				= $ref_id;
	$info_gen['perent_id']			= $perent_id;
	$info_gen['reserve_id']			= $reserve_id;
	$info_gen['customer_id'] 		= $customer_id;
	$info_gen['batch_id'] 			= $batch_id; 
	$info_gen['batch_no']			= $batch_no;
	
	$info_gen['base_rate']			= $base_rate;
	$info_gen['conv_rate']			= $conv_rate;
	$info_gen['workorder_id']		= $workorder_id;

	$info_gen['user_id']			= $_SESSION['user_id'];
	$info_gen['cdate']				= date("Y-m-d H:i:s");
	$info_gen['company_id']			= $_SESSION['company_id'];

	if($stock_flag == '2'){
		$info_gen['base_rate']			= $res_stock['base_rate'];
		$info_gen['conv_rate']			= $res_stock['conv_rate'];
	}

	// $batch_no = get_batch_no($dbcon,$product_id);

	// $info_gen['batch_no'] = $batch_no;

	// /var_dump($info_gen);
	$inserid_gen=add_record("tbl_stock_trn", $info_gen, $dbcon,$branch_id);

	$remark = 'Stock Arrived<br>'.$re['product_name'].'<br>Stock: '.$base_stock;

	$infotask['task_type_id']=14;
	$infotask['task_rel_id']=1;
	$infotask['task_remark']=$remark;
	$infotask['task_priority_id']=1;
	$infotask['assign_user_ids']=$_SESSION['user_id'];
	$infotask['task_alert_id']=2;
	$infotask['entry_type']=1;
	$infotask['show_user_ids']=$_SESSION['user_id'];
	$infotask['task_due_date']=date("Y-m-d H:i:s");
	$infotask['create_date']=date("Y-m-d H:i:s");
	$infotask['alert_date_time']=date("Y-m-d H:i:s");
	$infotask['task_completion_date']=date("Y-m-d H:i:s");
	$infotask['user_id']		= $_SESSION['user_id'];
	$infotask['cdate']			= date("Y-m-d H:i:s");
	$infotask['company_id']		= $_SESSION['company_id'];

	if($inserid_gen){
		// update_batch_no($dbcon,$product_id);
		// $inserid_task=add_record("tbl_task", $infotask, $dbcon,$branch_id);
	}
	if($stock_flag==1){
		//add_request_reserve_stock($dbcon,$product_id);
		//rquest_qty_deduct($dbcon,$product_id,$stock_qty);
	}else if($stock_flag==2){
		//deduct_remove_stock($dbcon,$product_id,$info_gen['base_unit'],$info_gen['base_stock'],$info_gen['convert_unit'],$ref_name,$ref_id);
	}
	
	return $inserid_gen;
}

function convert_stock_new($dbcon,$stock,$product_id,$type){
	$que_po="select * from product_mst where product_id='".$product_id . "'";
	$resi_grn=$dbcon->query($que_po);
	$re=brp_mysqli_fetch_assoc($resi_grn);
	if($re['product_base_unit']!=$re['product_conv_unit']){
		if($type=="base_unit"){
			$ret_qty=($stock/$re['product_conv_qty'])*$re['product_base_qty'];
		}else{
			$ret_qty=($stock/$re['product_base_qty'])*$re['product_conv_qty'];
		}
	}else{
		$ret_qty=$stock;
	}
	return $ret_qty;
	//return $type;
}
function minus_stock($dbcon,$product_id,$unit_id,$date,$ref_name,$ref_id,$used_qty,$branch_id){
	if(!empty($branch_id)){
		$branch_where=" and branch_id=".$branch_id;
	}
	
	$que_po="select * from mst_godown where g_status=0 ".$branch_where." and company_id=".$_SESSION['company_id'];
	$resi_grn=$dbcon->query($que_po);
	//$used_qty=$used_qty;
	while($rel=brp_mysqli_fetch_assoc($resi_grn)){
		$stock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,$rel['gd_id'],$branch_id);
		if($used_qty>0){
			if($stock>0){
				if($used_qty<=$stock){
					add_stock($dbcon,$product_id,$unit_id,$date,$ref_name,$ref_id,$rel['gd_id'],$used_qty,2,$branch_id,'','');
					$used_qty=$used_qty-$used_qty;
				}else{
					add_stock($dbcon,$product_id,$unit_id,$date,$ref_name,$ref_id,$rel['gd_id'],$stock,2,$branch_id,'','');
					$used_qty=$used_qty-$stock;
				}
			}
		}
		$stock1=$stock1+$stock;
	}
	//return $unit_id;
}

function minus_batch_stock($dbcon,$product_id,$unit_id,$date,$ref_name,$ref_id,$used_qty,$branch_id,$stock_id,$customer_id){
	
	//add_stock($dbcon,$product_id,$unit_id,$date,$ref_name,$ref_id,$rel['gd_id'],$used_qty,2,$branch_id);
	$que="select * from product_mst as ta where product_id=".$product_id;
		$rs_di=$dbcon->query($que);
		$re=brp_mysqli_fetch_assoc($rs_di);
	if($re['product_conv_unit'] != $re['product_base_unit']){
		if(!empty($batch_id)){
			$s_que="select * from tbl_batch_data where batch_id=".$batch_id;
			$s_rs_di=$dbcon->query($s_que);
			$s_re=brp_mysqli_fetch_assoc($s_rs_di);

			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$used_qty;
				$base_stock = ($con_stock/$s_re['conv_qty']) * $s_re['base_qty'];
			}else{
				$type="conv_unit";
				$base_stock=$used_qty;
				$con_stock = ($base_stock/$s_re['base_qty']) * $s_re['convert_qty'];
			}
		}else{
			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$used_qty;
				$base_stock=convert_stock_new($dbcon,$used_qty,$product_id,$type);
			}else{
				$type="conv_unit";
				$base_stock=$used_qty;
				$con_stock=convert_stock_new($dbcon,$used_qty,$product_id,$type);
			}
		}
	}else{
		$base_stock=$used_qty;
		$con_stock=$used_qty;
	}
		
		
		$info_gen['stock_date']			= date('Y-m-d',strtotime($stock_date));
		$info_gen['product_id']			= $product_id;
		$info_gen['base_unit']			= $re['product_base_unit'];
		$info_gen['base_stock']			= $base_stock;
		$info_gen['convert_unit']		= $re['product_conv_unit'];
		$info_gen['convert_stock']		= $con_stock;
		$info_gen['stock_flage']		= 2;
		$info_gen['ref_name']			= $ref_name;
		$info_gen['ref_id']				= $ref_id;
		$info_gen['perent_id']			= $stock_id;
		$info_gen['customer_id'] 		= $customer_id;
		$info_gen['batch_id'] 			= $batch_id; 
		$info_gen['batch_no']			 = $batch_no;
		$info_gen['user_id']		= $_SESSION['user_id'];
		$info_gen['cdate']			= date("Y-m-d H:i:s");
		$info_gen['company_id']		= $_SESSION['company_id'];

		$inserid_gen=add_record("tbl_stock_trn", $info_gen, $dbcon,$branch_id);

		// $remark = 'Stock Arrived<br>'.$re['product_name'].'<br>Stock: '.$base_stock;

		// $infotask['task_type_id']=14;
		// $infotask['task_rel_id']=1;
		// $infotask['task_remark']=$remark;
		// $infotask['task_priority_id']=1;
		// $infotask['assign_user_ids']=$_SESSION['user_id'];
		// $infotask['task_alert_id']=2;
		// $infotask['entry_type']=1;
		// $infotask['show_user_ids']=$_SESSION['user_id'];
		// $infotask['task_due_date']=date("Y-m-d H:i:s");
		// $infotask['create_date']=date("Y-m-d H:i:s");
		// $infotask['alert_date_time']=date("Y-m-d H:i:s");
		// $infotask['task_completion_date']=date("Y-m-d H:i:s");
		// $infotask['user_id']		= $_SESSION['user_id'];
		// $infotask['cdate']			= date("Y-m-d H:i:s");
		// $infotask['company_id']		= $_SESSION['company_id'];

		// if($inserid_gen){
		// 	$inserid_task=add_record("tbl_task", $infotask, $dbcon,$branch_id);
		// }
		// if($stock_flag==1){
		// }else if($stock_flag==2){
		// }
		
		return $inserid_gen;					
	
}

function deduct_batch_reseve_stock($dbcon,$product_id,$used_qty){
	
	$que="select * from product_mst as ta where product_id=".$product_id;
	$rs_di=$dbcon->query($que);
	$re=brp_mysqli_fetch_assoc($rs_di);
	
	$info['reserve_date']		=date('Y-m-d');
	$info['product_id']			=$product_id;
	$info['base_unit']			=$re['product_base_unit'];
	$info['base_stock']			=$used_qty;
	$info['convert_unit']		=$re['product_conv_unit'];
	$info['convert_stock']		=$used_qty;
	$info['stock_flage']		=2;
	//$info['request_id']			=$row['request_id'];
	//$info['ref_name']			="request";
	//$info['ref_id']				=$row['reserve_id'];
	//$info['sales_order_trn_id']	=$row['sales_order_trn_id'];
	$info['cdate']				=date('Y-m-d H:i:s');
	$info['user_id']			=$_SESSION['user_id'];
	$info['company_id']			=$_SESSION['company_id'];
	
	$inserid=add_record('tbl_reserve_stock', $info, $dbcon,$branch_id);
					
}

function add_store_receive($dbcon,$product_id,$unit_id,$stock_date,$ref_name,$ref_id,$godown_id,$stock_qty,$stock_flag,$branch_id,$perent_id,$reserve_id,$customer_id="",$p_id=0,$rp_id=0){

	$que="select * from product_mst as ta where product_id=".$product_id;
		$rs_di=$dbcon->query($que);
		$re=brp_mysqli_fetch_assoc($rs_di);
		
		if($re['product_conv_unit']==$unit_id){
			$type="base_unit";
			$con_stock=$stock_qty;
			$base_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
		}else{
			$type="conv_unit";
			$base_stock=$stock_qty;
			$con_stock=convert_stock_new($dbcon,$stock_qty,$product_id,$type);
		}


	$info_gen['stock_date']			= date('Y-m-d',strtotime($stock_date));
	$info_gen['product_id']			= $product_id;
	$info_gen['base_unit']			= $re['product_base_unit'];
	$info_gen['base_stock']			= $base_stock;
	$info_gen['used_base_stock']	= $base_stock;
	$info_gen['convert_unit']		= $re['product_conv_unit'];
	$info_gen['convert_stock']		= $con_stock;
	$info_gen['stock_flage']		= $stock_flag;
	$info_gen['godown_id']			= $godown_id;
	$info_gen['ref_name']			= $ref_name;
	$info_gen['ref_id']				= $ref_id;
	$info_gen['perent_id']			= $perent_id;
	$info_gen['reserve_id']			= $reserve_id;
	$info_gen['stock_status']		= '3';
	$info_gen['p_id']		= 		$p_id;
	$info_gen['request_id']	= 		$rp_id;	
	$info_gen['customer_id'] = $customer_id;	
	
	$info_gen['user_id']		= $_SESSION['user_id'];
	$info_gen['cdate']			= date("Y-m-d H:i:s");
	$info_gen['company_id']		= $_SESSION['company_id'];
	$info_gen['branch_id']		= $branch_id;
	

//echo "<pre>"; print_r($info_gen);die;
	$inserid_gen=add_record("tbl_stock_receive", $info_gen, $dbcon,$branch_id);
}
function round_up($number, $precision = 5)
{
    $fig = pow(10, $precision);
    $val = (ceil($number * $fig) / $fig);
    return number_format($number,5,".","");
}
?>
