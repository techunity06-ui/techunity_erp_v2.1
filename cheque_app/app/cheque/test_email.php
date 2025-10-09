<?php
	session_start();
	include("../../../config/config.php");
	include("send_mail2.php");
	send_email("DEMO","1234567890","2014-10-09","1","10,000","1","1",$dbcon);
?>                                
                            
                            
                            