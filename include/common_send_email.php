<?php
include("Mailin.php");
function final_send_email($from_email, $to_email_id,$ccemail_id,$bccemail_id,$email_subject,$email_content,$files){
	
	$mailin = new Mailin("https://api.sendinblue.com/v2.0",SENDMAILKEY);
	
	$from = $from_email;
	// var_dump($files);
	/*$attch=array();
	if(!empty($file)){
		$files = fopen(MAIL_ATTACH_ACTUAL.$file,"rb");
		$data = fread($files,filesize(MAIL_ATTACH_ACTUAL.$file));
		fclose($files);
		$data = chunk_split(base64_encode($data));
		$attch[$file]= $data;
	}*/
	$attch=array(); 
	for($x=0;$x<count($files);$x++){
		if(!empty($files[$x])){
			$file = fopen("../../../view/upload/mail_attach/".$files[$x],"rb");
			//var_dump (MAIL_ATTACH_ACTUAL.$files[$x]);
			$data = fread($file,filesize("../../../view/upload/mail_attach/".$files[$x]));
			fclose($file);
			$data = chunk_split(base64_encode($data));
			$attch[$files[$x]]= $data;
			// var_dump(DOMAIN_F."view/upload/mail_attach/".$files[$x]);
		}
	}
	
	$ccemail=explode(";",$ccemail_id);
	$cc=array();
	for($i=0;$i<count($ccemail);$i++){
		$cc[$ccemail[$i]]= $ccemail[$i];
	}
	
	$bccemail=explode(",",$bccemail_id);
	$bcc=array();
	for($b=0;$b<count($bccemail);$b++){
		$bcc[$bccemail[$b]]=$bccemail[$b];
	}
	
	$cc1 = array_filter($cc); 
	if (empty($cc1)){
		$c='echo //';
	}
	$bcc1 = array_filter($bcc); 

	if (empty($bcc1)){
		$bc='echo //';
	}
	

# Define the campaign settings
        if($files[0]){	
		$data = array( "to" => array($to_email_id=>$to_email_id),
			$c."cc" => $cc1,
			$bc."bcc" => $bcc1,
			"from" => array($from,$from),
			"subject" => $email_subject,
			"html" => $email_content,
			"attachment" => $attch
		);
	}
	else{
		$data = array( "to" => array($to_email_id=>$to_email_id),
			$c."cc" => $cc1,
			$bc."bcc" => $bcc1,
			"from" => array($from,$from),
			"subject" => $email_subject,
			"html" => $email_content
		);
	}
	// var_dump($data);
	
	//var_dump($data);
	// $send = $mailin->send_email($data);
	// p($send);
	return $mailin->send_email($data);
}
function quotation_mail_send($to_email_id,$ccemail_id,$bccemail_id,$email_subject,$email_content,$files){
	
	$mailin = new Mailin("https://api.sendinblue.com/v2.0",SENDMAILKEY);
	
	$from = ADMIN_EMAIL;
	//var_dump($files);
	/*$attch=array();
	if(!empty($file)){
		$files = fopen(MAIL_ATTACH_ACTUAL.$file,"rb");
		$data = fread($files,filesize(MAIL_ATTACH_ACTUAL.$file));
		fclose($files);
		$data = chunk_split(base64_encode($data));
		$attch[$file]= $data;
	}*/
	$attch=array(); 
	for($x=0;$x<count($files);$x++){
		if(!empty($files[$x])){
			$file = fopen(quotation_A.$files[$x],"rb");
			//var_dump (quotation_A.$files[$x]);
			$data = fread($file,filesize(quotation_A.$files[$x]));
			fclose($file);
			$data = chunk_split(base64_encode($data));
			//$attch[$files[$x]]= $data;
			$attch[$files[$x]]= $data;
			//var_dump($files);
		}
	}
	
	$ccemail=explode(";",$ccemail_id);
	$cc=array();
	for($i=0;$i<count($ccemail);$i++){
		$cc[$ccemail[$i]]= $ccemail[$i];
	}
	
	$bccemail=explode(",",$bccemail_id);
	$bcc=array();
	for($b=0;$b<count($bccemail);$b++){
		$bcc[$bccemail[$b]]=$bccemail[$b];
	}
	
	$cc1 = array_filter($cc); 
	if (empty($cc1)){
		$c='echo //';
	}
	$bcc1 = array_filter($bcc); 

	if (empty($bcc1)){
		$bc='echo //';
	}
	

# Define the campaign settings
	if($files[0]){	
		$data = array( "to" => array($to_email_id=>$to_email_id),
			$c."cc" => $cc1,
			$bc."bcc" => $bcc1,
			"from" => array($from,$from),
			"subject" => $email_subject,
			"html" => $email_content,
			"attachment" => $attch
		);
	}
	else{
		$data = array( "to" => array($to_email_id=>$to_email_id),
			$c."cc" => $cc1,
			$bc."bcc" => $bcc1,
			"from" => array($from,$from),
			"subject" => $email_subject,
			"html" => $email_content
		);
	}
	//var_dump($data);
	
	//var_dump($data);
	
	return $mailin->send_email($data);
}
?>