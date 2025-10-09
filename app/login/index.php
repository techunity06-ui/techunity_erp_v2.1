<?php
	session_start();
	include('../../config/config.php');
	include('../../config/geoplugin.class.php');
	// error_reporting(E_ALL);
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
					
					$username = strtolower($_POST['username']);
					$password = $_POST['password'];
					//Prevent SQL Injection
					$usr = stripslashes($username);
					$pwd = stripslashes($password);
					$usr = $dbcon->real_escape_string($usr);
					$pwd = $dbcon->real_escape_string($pwd);
					$pwd = md5($pwd);

					$sql = "SELECT `user_id`, `user_name`, `user_mail`,`user_type`, `user_phone`, `user_company`, `user_country`,`user_stat`, `user_tmst`,`user_date`,`setup`,`payment_status`,datediff (CURDATE(),user_tmst) as datedif,print_align FROM `users` WHERE `user_mail` = '$usr' AND `user_key` = '$pwd'";
		
					$result=$dbcon->query($sql);
					if(!$result = $dbcon->query($sql)){
						echo 'There was an error running the query [' . $dbcon->error . ']';
					}
					// Mysql_num_row is counting table row
					$count= $result->num_rows;
					if($count==1)
					{
						$row = $result->fetch_assoc();
						$datedif=(strtotime(date('Y-m-d 00:00:00')) - strtotime($row['user_tmst'])) / (60 * 60 * 24);
						exec('wmic DISKDRIVE GET SerialNumber 2>&1',$m);//disk no code
						if(!$row)
						{
							$arr['msg']= 'fetch_error';
						}
						else if($row['user_stat'] == 0 )
						{
							$arr['msg']= 'activate';
						}
						else if ($datedif > 30 && $row['payment_status']=="0")
						{	
							$arr['msg']='licence';
						}
						else if($m[1].'2015'!=$row['user_date'])
						{
							$arr['msg']='single_user';
						}
						
						else if($row['setup']==0)
						{	$str = "UPDATE `users` SET 
									`user_date`= '".($m[1].'2015')."',
									`user_stat`= '1',
									`setup`= '1'
								WHERE
									`user_id`=".$row['user_id'];
								$query = $dbcon -> query($str);	
						}
						if(!($_SESSION['EN_USER'] = $usr) || !($_SESSION['EN_PASS'] = $pwd))
						{
							$myFile = "log.txt";
							$fh = fopen($myFile, 'w');
							$stringData = "\r\n"."File Name : doLogin.php Session Error : ".mysql_error()."\r\n";
							fwrite($fh, $stringData);
							fclose($fh);	
							$arr['msg']= 'error session';
						}
						else {
							$b = rmv($_POST['b']);
							$bv =rmv($_POST['bv']);
							$ip =rmv($_POST['ip']);
							$os =rmv($_POST['os']);
							//$ip = "";
							$ct = "";
							$st = "";
							$cont = "";
							$lng =  "";
							$lat = "";
							$in = date("Y-m-d H:i:s");
							$insert = "INSERT INTO `login_history`
											(`log_id`, `uid`, `in_time`, `out_time`, `ip`, `browser`, `version`, `os`, `city`, `state`, `country`, `lng`, `lat`) VALUES ('','$row[user_id]','$in','','$ip','$b','$bv','$os','$ct','$st','$cont','$lng','$lat')";
							$iq = $dbcon->query($insert);
						if($iq)
						{
							$_SESSION['current_location'] = "";//"{$geoplugin->city}";
							$_SESSION['user_id'] = $row['user_id'];
							$_SESSION['user_name'] = ucwords(strtolower($row['user_name']));
							$_SESSION['user_type'] = $row['user_type'];
							$_SESSION['session_id'] = $dbcon->insert_id;
							$_SESSION['LOGGED_IN'] = true;
							$_SESSION['title'] = TITLE;
							$_SESSION['domain'] = DOMAIN;
							/*CHEQUE PRINT PAGE SETTING START*/
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
							/*CHEQUE PRINT PAGE SETTING END*/
							if($row['user_type']=="user")
							{
								echo 'employee';
							}	
							else
							{
								$start=(date('m')=='04') ? date('Y',strtotime('-1 year')) : '';
								$query="SELECT * FROM `tbl_invoicetype` where year(cdate)='".$start."'";
								$rs_data=$dbcon->query($query);
								while($rel=mysqli_fetch_assoc($rs_data))
								{
									$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET exciseinvoice_start=0,taxinvoice_start=0,cdate='".date('Y')."-04-01' where invoicetype_id=".$rel['invoicetype_id']);
								}
								$arr['msg']='success'; //login done
							}
							/**Monthly backup*/
							$query="select datediff (CURDATE(),max(cdate)) as datedif from tbl_db_backup";
							$rs_dbbkp = mysqli_fetch_assoc($dbcon->query($query));
								if($rs_dbbkp['datedif']>BKP_DAYS ||   is_null($rs_dbbkp['datedif']) )
								{
									mkdir(BACKUP);
									$arr['bkp']=true;
									$arr['bkp_url']='backup/1';
								}
							}
							else {
								$arr['msg']='invalid'.$dbcon->error;
							}
						}
						
					}
					else
					{
						$arr['msg']="invalid";	
					}
				}
				else {
					$arr['msg']='reject';
				}
			}
			else {
				$arr['msg']='error22';
			}
		} //second security check if
		else {
			/*
			echo '<br> D is  : '.$d.' <br>'."\r\n";
			echo '<br> Encoding is : '.urlencode($_GET["redirect"]).' <br>'."\r\n";
			echo '<br> HTTP : '.$_SERVER['HTTP_REFERER'].'<br>'."\r\n";
			*/
			$arr['msg']='access denied 1';
		}
	}
	else {
		$arr['msg']='access denied 2';
	}
	echo json_encode($arr);
?>
