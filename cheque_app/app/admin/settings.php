<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");

if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { 
	if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) {
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['type']) == "update") {
			if($_POST['token'] == $_SESSION['token']) {
				$admin 			= encrypt(strtolower($POST['admin']));
				$printer 		= strtolower($POST['printer']);
				$print_align 	= ($POST['print_align']);
				$query = $dbcon -> query("UPDATE `users` SET `print_align` = '$print_align' WHERE `user_id` = '$_SESSION[user_id]'");
				if($query) {
					if($print_align=="0")//center
					{
						$_SESSION['print_page']='print';
					}
					else if($print_align=="2")//right
					{
						$_SESSION['print_page']='print_right';
					}
					else if($print_align=="1")//left
					{
						$_SESSION['print_page']='print_left';
					}
					echo "1";
				}
				else {
					echo "0";
				}
			}
			else {
				echo "0";
			}
		}
		else if(strtolower($POST['type']) == "password") {
			if($_POST['token'] == $_SESSION['token']) {
				if(($POST['newpass'] != NULL && $POST['newpass'] != "") && ($POST['repeatpass'] != NULL && $POST['repeatpass'] != "")) {
					if($POST['newpass'] == $POST['repeatpass']) {
						$pass = md5(strtolower($POST['newpass']));
						$query = $dbcon -> query("UPDATE `coro_users` SET `user_key` = '$pass' WHERE `user_id` = '$_SESSION[user_id]'");
						if($query) {
							echo "1";
						}
						else {
							echo "0";
						}
					}
					else {
						echo "-1";
					}
				}
				else {
					echo "0";
				}
			}
			else {
				echo "0";
			}
		}
		else {
			die("Error - 2");
		}
		
	}
	else {
	    die("Error - 2");
	}
}
else {
    die("Error - 1");
}
?>
