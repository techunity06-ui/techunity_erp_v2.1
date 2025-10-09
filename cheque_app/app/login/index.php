<?php
	session_start();
	include('../../../config/config.php');
	include('../../config/geoplugin.class.php');
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
	{
		if(isset($_POST['redirect']) && ($_POST['redirect'] != NULL || $_POST['redirect'] != "")) {
			$d = DOMAIN.'login?redirect='.urlencode($_POST['redirect']);
		}
		else {
			$d = DOMAIN.'login';
		}

		if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == $d)
		{
			$is_ajax = $_POST['is_ajax'];
			if(isset($is_ajax) && $is_ajax)
			{
				if($_POST['token'] == $_SESSION['token'])
				{
					$username = encrypt(strtolower($_POST['username']));
					$password = $_POST['password'];

					//Prevent SQL Injection
					$usr = stripslashes($username);
					$pwd = stripslashes($password);
					$usr = $dbcon->real_escape_string($usr);
					$pwd = $dbcon->real_escape_string($pwd);
					$pwd = md5($pwd);

					 $sql = "SELECT `user_id`, `user_name`, `user_mail`, `user_phone`, `user_company`, `user_country`, `user_pan`,`user_stat`, `user_tmst`,user_date,user_address,print_align FROM `coro_users` WHERE `user_mail` = '$usr' AND `user_key` = '$pwd'";

					$result=$dbcon->query($sql);

					if(!$result = $dbcon->query($sql)){
						echo 'There was an error running the query [' . $dbcon->error . ']';
					}
					// Mysql_num_row is counting table row
					$count= $result->num_rows;
					if($count==1)
					{
						$row = $result->fetch_assoc();
						 $sdate=decrypt($row['user_tmst']);
						$datedif=(strtotime(date('Y-m-d 00:00:00')) - strtotime($sdate)) / (60 * 60 * 24);
						exec('wmic DISKDRIVE GET SerialNumber 2>&1',$m);
						if(!$row)
						{
							echo 'fetch_error';
						}
						/*else if($m[1].'2015'!=$row['user_date'])
						{
							echo 'single_user';
						}*/
						else if ($datedif > 30)
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
								$test_con=check_internet_connection();
								if($test_con && $row['user_stat']=="1")
								{
									$geoplugin = new geoPlugin();
									$geoplugin->locate($ip);
									$info['ip']		= $ip = rmv("{$geoplugin->ip}");
									$info['city']	= $ct = rmv("{$geoplugin->city}");
									$info['state']	= $st = rmv("{$geoplugin->region}");
									$info['country']	= $cont = rmv("{$geoplugin->countryName}");
									$info['lng']	= $lng =  rmv("{$geoplugin->longitude}");
									$info['lat']	= $lat = rmv("{$geoplugin->latitude}");
									$_SESSION['current_location'] = "{$geoplugin->city}";
									$info['key']= $row['user_date'];
									define("COMPANY",decrypt($row['user_company']));
									$info['company']=COMPANY;
									$info['email']=decrypt($row['user_mail']);
									$info['address']=decrypt($row['user_address']);
									$info['contactno']=decrypt($row['user_phone']);
									$data=send_data($info);									
									if($data=="success")
									{
										$str = "UPDATE `coro_users` SET `user_stat`= '0' where user_id=0";
										$dbcon -> query($str);			
									}
									
								}
								else
								{
									$ct = "";
									$st = "";
									$cont = "";
									$lng =  "";
									$lat = "";
								}
								$in = date("Y-m-d H:i:s");

								$insert = "INSERT INTO 
											`coro_login_history`
												(`log_id`, `uid`, `in_time`, `out_time`, `ip`, `browser`, `version`, `os`, `city`, `state`, `country`, `lng`, `lat`)
											 VALUES 
												('','$row[user_id]','$in','','$ip','$b','$bv','$os','$ct','$st','$cont','$lng','$lat')";
								$iq = $dbcon->query($insert);
								if($iq)
								{									
									$_SESSION['user_id'] = $row['user_id'];
									
									$_SESSION['user_name'] = ucwords(strtolower($row['user_name']));
									$_SESSION['session_id'] = $dbcon->insert_id;
									$_SESSION['LOGGED_IN'] = true;
									if($row['print_align']=="0")//center
									{
										$_SESSION['print_page']='print';
									}
									else if($row['print_align']=="2")//right
									{
										$_SESSION['print_page']='print_right';
									}
									else if($row['print_align']=="1")//left
									{
										$_SESSION['print_page']='print_left';
									}
									
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
?>
