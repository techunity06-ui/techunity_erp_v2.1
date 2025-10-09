<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
 
	if(strtolower($_POST['mode']) == "changepassword") {
		
		//$query="select * from users where user_id=".$_POST['eid']." and user_key='".md5($_POST['old_pass'])."'";
		//$rel=mysqli_fetch_assoc($dbcon->query($query));
		/*if(empty($rel)) {
			echo "2";
			exit;
		}*/
		$info['new_pass']		= ($_POST['new_pass']);
		$info['confirm_pass']	= ($_POST['confirm_pass']);
		if($info['new_pass']==$info['confirm_pass'])
		{
			$infodt['user_key']=md5($info['new_pass']);
			$updateid=update_record('users', $infodt,"user_id=".$_POST['eid'] , $dbcon);		
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
	}
	

/*
	function upload_image($FILES,$up_path,$fileprefix)
	{
	$rand=rand(0,9999);
	if(!empty($FILES['tmp_name'])) {
	list($width, $height, $type, $attr) = getimagesize($FILES['tmp_name']);
	if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
	$allowedExts = array("gif", "jpeg", "jpg", "png");
	$temp = explode(".", $FILES["name"]);
	$extension = end($temp);
	if (in_array($extension, $allowedExts)) {
	$File = $fileprefix.$rand.".jpg";
	$Thumb = $fileprefix.$rand.".jpg";
	$tmp_name = $FILES["tmp_name"];
	move_uploaded_file($tmp_name, $up_path.$File);							
	}
	}
	return $File;
	}
	
}*/
?>