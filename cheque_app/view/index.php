<?php
    include("../../config/config.php");
    if(isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN'] == true) {
        header("Location: ".DOMAIN_CHEQUE."home");
    }
    else {
		$query = $dbcon -> query("SELECT setup FROM `coro_users` where user_id=0");
		$rs=mysqli_fetch_assoc($query);
		if($rs['setup']==0)
		{
			header("Location: ".DOMAIN_CHEQUE."login");
		}
		else
		{
			header("Location: ".DOMAIN_CHEQUE."setup");
		
		}
    }
?>
