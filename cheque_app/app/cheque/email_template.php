<?php
    include("../../../config/config.php");
    $date = date("d-m-Y");
    $payee = "CoRo Technologies";
    $amount = "4500.00";
    $account = "RAJESH CHOUDHARI (HDFC BANK - CG ROAD)";

    $html = '
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
                        <td align="right" valign="top" mc:edit="date" style="font:Bold 17px Arial, Helvetica, sans-serif; color:#060606;">'.date("F  d, Y").'</td>
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
                                        <th>Date</th>
                                        <td>'.$date.'</td>
                                    </tr>
                                    <tr>
                                        <th>Account</th>
                                        <td>'.$account.'</td>
                                    </tr>
                                    <tr>
                                        <th>Payee</th>
                                        <td>'.$payee.'</td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td>'.$amount.'</td>
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
    
    echo $html;

?>

                            