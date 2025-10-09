<?php
function add_record($table, $data, $db)
{
	
	foreach(array_keys($data) as $field_name) 
	{
		$data[$field_name] = sc_mysql_escape($data[$field_name],$db);
		if (!isset($field_string)) {
			$field_string = "".$field_name.""; 
			$value_string = "'$data[$field_name]'";
		} else {
			$field_string .= ",".$field_name."";
			$value_string .= ",'$data[$field_name]'";
		}
	}
	$dbQuery = "INSERT INTO $table ($field_string) VALUES ($value_string)";
	/*echo $dbQuery;*/	
		
	 $db->query($dbQuery);
	//echo $dbQuery;
	$insert_id=mysqli_insert_id($db);
	if(isset($insert_id))
	{
		$_SESSION['msg']='Record Added Successfully';
	}
	return $insert_id;//return record number of the record just added, in case we need it
}
function update_record($table, $data, $where, $db)
{
	foreach(array_keys($data) as $field_name){
		$data[$field_name] = sc_mysql_escape($data[$field_name],$db);
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
// echo $dbQuery;	 
	$db->query($dbQuery);
	//echo $dbQuery;exit;	
	$update_id=mysqli_affected_rows($db);
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
	$update_id=mysqli_affected_rows($db);
	return $update_id;	
}
function get_update_maxno($table, $db)
{
	$query='select maxno from tbl_maxno where tbl_name="'.$table.'"';
	$rs=($db->query($query));
	$rs=mysqli_fetch_array($rs);
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
	while($rel=mysqli_fetch_array($rs))
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
?>
