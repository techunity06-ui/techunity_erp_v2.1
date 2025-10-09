<?php
function brp_mysqli_affected_rows($db){
	return mysqli_affected_rows($db);
}

function brp_mysqli_insert_id($db){
	return mysqli_insert_id($db);
}

function brp_sc_mysql_escape($value, $db){
	if (is_string($value));
	// strip out slashes IF they exist AND magic_quotes is on
	if (get_magic_quotes_gpc() && (strstr($value,'\"') || strstr($value,"\\'"))) $value = stripslashes($value);	
	// escape string to make it safe for mysql
	return @mysqli_real_escape_string($db,$value);
}

function brp_mysqli_fetch_array($array){
	return mysqli_fetch_array($array);
}

function brp_strtolower($value) {
	return strtolower($value);
}

function brp_mysqli_fetch_assoc($value) {
	return mysqli_fetch_assoc($value);
}

function brp_mysqli_num_rows($value) {
	return mysqli_num_rows($value);
}

function brp_json_encode($value){
	return json_encode($value);
}

function brp_mysqli_fetch_all($result){
       return mysqli_fetch_all($result,MYSQLI_ASSOC);
}

function brp_mysqli_query($dbcon,$query){
       return mysqli_query($dbcon,$query);
}

function brp_ucwords($value){
       return ucwords($value);
}
?>