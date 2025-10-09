<?php
	if(isset($AJAX) && $AJAX == true) {
		if(!isset($_SESSION['LOGGED_IN'])) {
			$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$url = urlencode($url);
			die("SESSION_END");
		}
	}
	else {
		if(!isset($_SESSION['LOGGED_IN'])) {
			$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$url = urlencode($url);
			header("Location: ".DOMAIN."login?redirect=".$url);
		}
	}
?>