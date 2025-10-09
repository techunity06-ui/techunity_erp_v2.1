<?php
    include("../config/config.php");
    if(isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN'] == true && $_SESSION['title'] = TITLE) {
       // header("Location: ".DOMAIN."dashboard");
        echo "<meta http-equiv=refresh content=0;url=".DOMAIN."dashboard>";
    }
    else {
        echo "<meta http-equiv=refresh content=0;url=".DOMAIN."login>";
		
    }
?>