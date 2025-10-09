<?php
error_reporting(E_ALL);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
send_mail1(['metr.umair@gmail.com'], 'Test', 'Umair');

function send_mail1($to, $subject, $message, $from_email = "",$ccmail=[], $resume=[],$bccmail=[]) {
               
        //Load Composer's autoloader
        require '../vendor/autoload.php';

        $mail = new PHPMailer(true); 
       
        // Passing `true` enables exceptions
        try {
        
            //if(IS_SMTP=='1'){         
                //Server settings
                //$mail->SMTPDebug = 2;                   
                $mail->isSMTP();                         
                $mail->Host =  'smtp.gmail.com'; 
                $mail->SMTPAuth = true;                  
                $mail->Username = 'metr.umair@gmail.com'; 
                $mail->Password = '@Metr#987$'; 
                $mail->SMTPSecure = 'ssl';     
                $mail->Port = 465;    

                $mail->SMTPOptions = array(
                   'ssl' => array(
                       'verify_peer' => false,
                       'verify_peer_name' => false,
                       'allow_self_signed' => true
                   )
                );
            //}
            //Recipients
            $mail->setFrom('metr.umair@gmail.com','BRP');

            //$mail->addAddress($to); 
            foreach ($to as $key => $value) {
                $mail->addAddress($value);     // Add a recipient    
            } 
          
            //$mail->addReplyTo('', ''); 

            //CC Mail
            if(!empty($ccmail)){
                foreach ($ccmail as $key => $value) {
                    $mail->addCC($value);         
                }
            }
            
            //Bcc Mail
            if(!empty($bccmail)){
                foreach ($bccmail as $key => $value) {
                    $mail->addBCC($value);         
                }
            } 

            //Attachments
            if(!empty($resume)){
                foreach ($resume as $key => $value) {
                    $attachment=FCPATH.'uploads/invoice/'.$value;
                  //echo $attachment;die();
                  $mail->addAttachment($attachment);
                    // $s = explode("/",$value);
                    // $filename=end($s);
                    // $mail->AddStringAttachment($value, $filename,  $encoding = 'base64', $type = 'application/pdf');          
                }
            }  
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message; 

            if($mail->send()){
                echo "send";
            }else{
                echo "fail";
            }
            
            //return true;
        } catch (Exception $e) {
             
            return false;
            //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;die();
        }         
}
