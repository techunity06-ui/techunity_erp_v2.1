<?php
	//send_email(check,account,date,payee,amount,mode);
	function send_email($cnum,$acc,$date,$payee,$amount,$mode,$note,$link) {
		$admin_query = $link -> query("SELECT `user_name`,`user_mail`,`user_printer` FROM `coro_users` WHERE `user_id` = '$_SESSION[user_id]'");
		$email = $admin_query -> fetch_assoc();
		
		$account_query = $link -> query("SELECT `acc_name`, `acc_number`, `bank_name` FROM `coro_accounts` INNER JOIN `coro_banks` ON `acc_bank` = `bank_id`  WHERE `acc_id` = '$acc' AND `acc_of` = '$_SESSION[user_id]'");
		$account = $account_query ->fetch_assoc();
		
		$payee_query = $link -> query("SELECT `cust_name`,`cust_city` FROM `coro_customers` WHERE `cust_id` = '$payee' AND `cust_of` = '$_SESSION[user_id]'");
		$_payee = $payee_query ->fetch_assoc();
		
		//send to
		$to  = $email['user_mail'];
		
		// subject
		$subject = $_payee['cust_name'] ; //.'A New Cheque Generated.';
		
		// message
		
		$account_detail = $account['acc_name'].' ('.$account['bank_name'].' - '.$account['acc_number'].')';
	    
		$message = '
		    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		    <html xmlns="http://www.w3.org/1999/xhtml">
		    <head>
		    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		    <title>Sai Krishna Group - Ahmedabad. Powered By CoRo Technologies</title>
		    
		    <style type="text/css">
		    
		    body{
			    margin:0px;
			    padding:0px;
			    background:#dfdddd;
			    text-align:center;
			    width:100%;
		    }
		    
		    
		    
		    img {
			    border:0px;
			text-align: right;
			    outline:none;
			    text-decoration:none;
			    display:block;
		    
		    }
		    
		    a,a:hover
		    {
			    color:#d95d97;
			    text-decoration:none;
		    }
		    
		    .header-borter
		    {
			    border-top:#201f1f solid 8px;
		    }
		    
		    .top-arrow
		    {
			    border-bottom:#2f2f2f solid 6px;
		    }
		    
		    .border
		    {
			    border:#e5e3e3 solid 1px;
		    }
		    
		    .bottom-border-single
		    {
			    border-top:#2f2f2f solid 17px;
		    }
		    
		    .cont-bg
		    {
			     background-color: #dfdddd;
		    }
		    </style>
		    
		    </head>
		    
		    
		    <body>
		    
		    <!--Table Start-->
		    
		    <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="cont-bg" bgcolor="#dfdddd" style="background:#dfdddd;padding-top:20px; padding-bottom:20px;">
		      <tr>
			<td align="center" valign="top">
		    
		    <table width="630" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="background:#ffffff;">
		      <tr>
			<td align="left" valign="top">
			
			<!--Header Start-->
			<table width="630" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <td align="left" valign="top"><img src="'.DOMAIN.'images/mail/border-top.png" width="630" height="3" alt="" style="display:block;" /></td>
			  </tr>
			  <tr>
			    <td align="right" valign="top" >
			    
			    <!--Browse Start-->
			    <table width="630" border="0" cellspacing="0" cellpadding="0">
			      <tr>
				<td align="right" valign="top" mc:edit="view-browse" style="padding:21px 21px 8px 0px; font:Normal 12px Arial, Helvetica, sans-serif; color:#404140;">Cheque Manager - <a href="http://coro.cc/">CoRo Technologies.</a></td>
			      </tr>
			      <tr>
				<td align="left" valign="top"><img src="'.DOMAIN.'images/mail/divater.png" width="630" height="11" alt="" style="display:block;" /></td>
			      </tr>
			    </table>
			    <!--Browse End-->
			    
			    </td>
			  </tr>
			  <tr>
			    <td align="left" valign="top"><table width="630" border="0" cellspacing="0" cellpadding="0">
			      <tr>
			      
				<!--Logo Start-->
				<td width="420" align="left" valign="top" style="padding:28px 0px 27px 20px;"><a href="#"><img mc:edit="logo" src="'.DOMAIN.'images/mail/logo.png" width="240" height="57" alt="" style="display:inline-block;" /></a></td>
				<!--Logo End-->
				
				<td width="200" align="left" valign="top" style="padding:37px 0px 0px 0px;">
				
				<!--Logo Start-->
				<table width="174" border="0" cellspacing="0" cellpadding="0">
				  <tr>
				    <td width="178" align="right" valign="top" mc:edit="issue" style="font:Bold 12px Arial, Helvetica, sans-serif; color:#d95d97; padding-bottom:3px;">New Cheque</td>
				    </tr>
				  <tr>
				    <td align="right" valign="top" mc:edit="date" style="font:Bold 17px Arial, Helvetica, sans-serif; color:#060606;">'.date("d-m-Y").'</td>
				    </tr>
				  </table>
				  <!--Logo End-->
				  
				  </td>
			      </tr>
			      </table></td>
			  </tr>
			</table>
			<!--Header End-->
			
			</td>
		      </tr>
		      <tr>
			<td align="left" valign="top"><table width="630" border="0" cellspacing="0" cellpadding="0">
		     
			  <tr>
			    <td align="left" valign="top">
			    
			    <!--Content Start-->
			    <table width="630" border="0" cellspacing="0" cellpadding="0">
			      <tr>
				<td align="left" valign="top"><img src="'.DOMAIN.'images/mail/divater.png" width="630" height="11" alt="" style="display:block;" /></td>
				</tr>
			      <tr>
				<td align="left" valign="top" style="padding:50px 25px 30px 25px;">
				
						
				<!--Content Text Part Start-->
				<table width="580" border="0" cellspacing="0" cellpadding="0">
				  <tr>
				    <td align="left" valign="top">
		    
				    <table width="580" border="0" cellspacing="0" cellpadding="0">
				      <tr>
				      
				      <!--Content Text Title Start-->
					<td align="left" valign="top" mc:edit="title" style="font:Bold 18px Arial, Helvetica, sans-serif; color:#060606; line-height:24px;">
					    Cheque details are as below,
					</td>
					<!--Content Text Title End-->
					
				      </tr>
				      <tr>
				      
				      <!--Content Text Inner Start-->
					<td align="left" valign="top" mc:edit="inner" style="font:Normal 12px Arial, Helvetica, sans-serif; color:#666363; line-height:18px; padding:10px 0px 15px 0px;">
					    <table width="100%">
						<tr>
						    <th>DATE</th>
						    <td><span type="date">'.date("M d, Y",strtotime($date)).'</span> ( <span type="date">'.date("d/m/Y",strtotime($date)).'</span> )</td>
						</tr>
						<tr>
						    <th>PAYEE</th>
						    <td style="font-size:16px;color:red;">'.$_payee['cust_name'].'( '.$_payee['cust_city'].' )</td>
						</tr>
						<tr>
						    <th>ACCOUNT</th>
						    <td>'.$account_detail.'</td>
						</tr>
						<tr>
						    <th>CHEQUE NUMBER</th>
						    <td>'.$cnum.'</td>
						</tr>
						<tr>
						    <th>AMOUNT</th>
						    <td style="font-size:16px;color:red;">'.$amount.'</td>
						</tr>
						<tr>
						    <th>NOTE</th>
						    <td>'.$note.'</td>
						</tr>
						<tr>
							<td>
								<center>
								<a href="https://www.google.com/calendar/render?action=TEMPLATE&text='.str_replace(' ','+',$_payee['cust_name']).'&dates='.date('Ymd',strtotime($date)).'T120000/'.date('Ymd',strtotime($date)).'T115959&details='.str_replace(' ','+',$note).'&location=Ahmedabad,+Gujarat&sf=true&output=xml" target="_blank" class="btn-primary"><button type="button">Add to Google Calendar</button></a>
								</center>
							</td>
						</tr>
					    </table>
					</td>
					<!--Content Text Inner End-->
					
				      </tr>
				      </table>
				      <!--Content Text Part End-->
				      
				      </td>
				    </tr>
				  </table>
				  <!--Content Text Part End-->
				  
				  </td>
				</tr>
			      <tr>
				<td align="left" valign="top"><img src="'.DOMAIN.'images/mail/divater.png" width="630" height="11" alt="" style="display:block;" /></td>
			      </tr>
			      </table>
			      <!--Content End-->
			      
			      </td>
			  </tr>
			</table></td>
		      </tr>
		      <tr>
			<td align="left" valign="top">
			
			<!--Footer  Start-->
			<table width="630" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <td align="left" valign="top" style="padding:32px 25px 32px 25px;">
			    
			    <!--Copyright Start-->
			    <table width="580" border="0" cellspacing="0" cellpadding="0">
			      <tr>
				<td align="left" valign="top" style="padding-bottom:8px;">
				
				<!--Socialmedia Start-->
				<table width="70" border="0" align="center" cellpadding="0" cellspacing="0">
				  <tr>
				    <td width="32" align="left" valign="top"><a href="http://fb.me/CoRoTechnologies"><img mc:edit="facebook" src="'.DOMAIN.'images/mail/fb.png" width="28" height="28" alt="" style="display:block;" /></a></td>
				    <td width="32" align="left" valign="top"><a href="http://twitter.com/CoRoTechnology"><img mc:edit="twitter" src="'.DOMAIN.'images/mail/tw.png" width="28" height="28" alt="" style="display:block;" /></a></td>
				  </tr>
				</table>
				<!--Socialmedia End-->
				
				</td>
			      </tr>
			      <tr>
			      
			      <!--Copyright Start-->
				<td align="left" valign="top" mc:edit="footer-text" style="font:Normal 12px Arial, Helvetica, sans-serif; color:#666363; line-height:18px; padding:5px 60px 0px 60px; text-align:center;">
				    You have received this email because you have subscribed to cheque manager software. Copyright &copy; <?php echo date("Y"); ?> CoRo Technologies.
				</td>
				<!--Copyright End-->
				
			      </tr>
			    </table>
			    <!--Copyright End-->
			    
			    </td>
			  </tr>
			  <tr>
			    <td align="left" valign="top"><img src="'.DOMAIN.'images/mail/border-top.png" width="630" height="3" alt="" style="display:block;" /></td>
			  </tr>
			</table>
			<!--Footer  End-->
			</td>
		      </tr>
		    </table>
		    
			</td>
		      </tr>
		    </table>
		    
		    
		    <!--Table End-->
		    
		    </body>
		    
		    </html>
		';
		
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		
		// Additional headers
		$headers .= 'To: '.$email['user_name'].' <'.$email['user_mail'].'>' . "\r\n";
		//$headers .= 'To: Hardik Thaker <hardik@coro.cc>' . "\r\n";
		$headers .= 'From: Cheque Book <chequebook@coro.cc>' . "\r\n";
		
		// Mail it
		//mail($to, $subject, $message, $headers);
	}
?>
                            
                            
                            