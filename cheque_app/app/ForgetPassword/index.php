<?php
	session_start();
	include('../../../config/config.php');
	include('../../config/geoplugin.class.php');
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
	{
		$d = DOMAIN.'login';
		

		if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == $d)
		{
			$is_ajax = $_POST['is_ajax'];
			if(isset($is_ajax) && $is_ajax)
			{
				if($_POST['token'] == $_SESSION['token'])
				{
					$username = strtolower($_POST['usernameF']);

					//Prevent SQL Injection
					$usr = stripslashes($username);
					$usr = $dbcon->real_escape_string($usr);
					$sql = "SELECT `user_id`, `user_name`, `user_mail`, `user_key`, datediff (CURDATE(),user_tmst) as datedif FROM `coro_users` WHERE `user_mail` = '$usr'";
					$result=$dbcon->query($sql);

					if(!$result = $dbcon->query($sql)){
						echo 'There was an error running the query [' . $dbcon->error . ']';
					}
					// Mysql_num_row is counting table row
					$count= $result->num_rows;
					if($count==1)
					{
						$row = $result->fetch_assoc();
						if(!$row)
						{
							echo 'fetch_error';
						}
						else if($row['user_stat'] == 0 )
						{
							echo 'activate';
							mail_password($row['user_mail'],$row['user_key']);
						}
						else if ($row['datedif'] > 60)
						{
							echo 'licence';
						}
						else
						{
							if(!($_SESSION['EN_USER'] = $usr) || !($_SESSION['EN_PASS'] = $pwd))
							{
								$myFile = "log.txt";
								$fh = fopen($myFile, 'w');
								$stringData = "\r\n"."File Name : doLogin.php Session Error : ".mysql_error()."\r\n";
								fwrite($fh, $stringData);
								fclose($fh);	
								echo 'error session';
							}
							else {
								$b = rmv($_POST['b']);
								$bv =rmv($_POST['bv']);
								$ip =rmv($_POST['ip']);
								$os =rmv($_POST['os']);

								$geoplugin = new geoPlugin();

								$geoplugin->locate($ip);
								$ip = rmv("{$geoplugin->ip}");
								$ct = rmv("{$geoplugin->city}");
								$st = rmv("{$geoplugin->region}");
								$cont = rmv("{$geoplugin->countryName}");
								$lng =  rmv("{$geoplugin->longitude}");
								$lat = rmv("{$geoplugin->latitude}");

								$in = date("Y-m-d H:i:s");

								$insert = "INSERT INTO 
											`coro_login_history`
												(`log_id`, `uid`, `in_time`, `out_time`, `ip`, `browser`, `version`, `os`, `city`, `state`, `country`, `lng`, `lat`)
											 VALUES 
												('','$row[user_id]','$in','','$ip','$b','$bv','$os','$ct','$st','$cont','$lng','$lat')";
								$iq = $dbcon->query($insert);
								if($iq)
								{
									$_SESSION['current_location'] = "{$geoplugin->city}";
									$_SESSION['user_id'] = $row['user_id'];
									$_SESSION['user_name'] = ucwords(strtolower($row['user_name']));
									$_SESSION['session_id'] = $dbcon->insert_id;
									$_SESSION['LOGGED_IN'] = true;
									echo 'success'; //login done
								}
								else {
									echo 'invalid'.$dbcon->error;
								}
							}
						}
					}
					else
					{
						echo "invalid";	
					}
				}
				else {
					echo 'reject';
				}
			}
			else {
				echo 'error22';
			}
		} //second security check if
		else {
			/*
			echo '<br> D is  : '.$d.' <br>'."\r\n";
			echo '<br> Encoding is : '.urlencode($_GET["redirect"]).' <br>'."\r\n";
			echo '<br> HTTP : '.$_SERVER['HTTP_REFERER'].'<br>'."\r\n";
			*/
			echo 'access denied 1';
		}
	}
	else {
		echo 'access denied 2';
	}
function mail_password($emailid,$code)
{
	//echo $emailid.$password.$profilename;
	$from="no-reply@cheque360.com";
	$headers  = "From:  $from\r\n";
	$headers .= "Content-type: text/html\r\n";
	$subject="Forgot Password";
	$to=$emailid;
	$body.='
	<span style="font-family:Verdana, Arial, Helvetica, sans-serif; font-size:14px;">				
		<center><img src="'.DOMAIN.'/view/images/logo.png" />
	<br>HI.... <br /> <br /> 
		Please check your email address <br /> <br />
		Emailid :'.$emailid.'
		<h3><a href="'.DOMAIN.'changepassword/'.$code.'" target="_blank">To Change Password click here</a></h3>
		<br /> <br />
		</center>
	</span>';
	mail($to,$subject,$body,$headers);	
	//echo $to.$subject.$body.$headers;
}
?>
