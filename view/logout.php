<?php 
	session_start();
	include("../config/config.php");
	$out = date("Y-m-d H:i:s");
	if(isset($_SESSION['session_id'])) {
		$query = "UPDATE `login_history` SET `out_time` = '$out' WHERE `log_id` = '$_SESSION[session_id]'";
		if($dbcon->query($query)) {
			session_destroy();
			//header("Location: ".DOMAIN);
                        echo "<meta http-equiv=refresh content=0;url=".DOMAIN.">";
			exit();
		}
		else {
			//header("Location: ".DOMAIN."dashboard");

			echo "Error ".$dbcon -> error;
		}
	}
	else {
		session_destroy();
                echo "<meta http-equiv=refresh content=0;url=".DOMAIN.">";
		header('Location: '.DOMAIN);
	}
	
?>