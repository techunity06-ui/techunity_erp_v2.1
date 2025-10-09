<?php
session_start();
include_once("../../config/config.php");
if(isset($_POST)) {
	//print_r($_POST);

	$targ_w = '500px'; //$_POST['w'];
	$targ_h = '230px'; //$_POST['h'];
	
	$jpeg_quality = 90;
	
	$FileDirectory	=CHEQUE_IMG.'temp/';
	$DestinationDirectory = CHEQUE_IMG;
	
	$src = $FileDirectory.$_POST['imgsrc'];
	//Get file extension from Image name, this will be added after random name
	$ImageExt = substr($_POST['imgsrc'], strrpos($_POST['imgsrc'], '.'));
  	$ImageExt = str_replace('.','',$ImageExt);
	
	//Let's check allowed $ImageType, we use PHP SWITCH statement here
	switch(strtolower($ImageExt))
	{
		case 'png':
			//Create a new image from file 
			$img_r = imagecreatefrompng($src);
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
			$CreatedImage =  imagecreatefrompng($src);
			break;
		case 'gif':
			$img_r = imagecreatefromgif($src);
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
			$CreatedImage =  imagecreatefromgif($src);
			break;			
		case 'jpg':
		case 'jpeg':
		case 'pjpeg':
			$img_r = imagecreatefromjpeg($src);
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
			$CreatedImage = imagecreatefromjpeg($src);
			break;
		default:
			$reply = array("response" => 0, "Message" => "Unsupported Error");
			die(json_encode($reply));
	}
	
	imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
		$targ_w,$targ_h,$_POST['w'],$_POST['h']);

	//header('Content-type: image/jpeg');
	imagejpeg($dst_r, $DestinationDirectory.$_POST['imgsrc'], $jpeg_quality);
	
	$files = glob($FileDirectory.'*'); // get all file names
	foreach($files as $file){ // iterate files
	  if(is_file($file))
		unlink($file); // delete file
	}
	
	$reply = array("response" => 1,"image" => $_POST['imgsrc']);
	echo json_encode($reply);
}
?>