<?php
session_start();
include_once("../../config/config.php");
if(isset($_POST))
{
	############ Edit settings ##############
	$BigImageMaxSize 		= 500; //Image Maximum height or width
	$DestinationDirectory	= CHEQUE_IMG.'temp/'; //specify upload directory ends with / (slash)
	$Quality 				= 90; //jpeg quality
	##########################################
	
	//check if this is an ajax request
	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		//die();
		$reply = array("response" => 0, "Message" => "AJAX Error");
		die(json_encode($reply));
	}
	
	// check $_FILES['ImageFile'] not empty
	if(!isset($_FILES['ImageFile']) || !is_uploaded_file($_FILES['ImageFile']['tmp_name']))
	{
		//die('Something wrong with uploaded file, something missing!'); // output error when above checks fail.
		$reply = array("response" => 0, "Message" => "Missing File");
		die(json_encode($reply));
	}
	
	// Random number will be added after image name
	$RandomNumber 	= rand(0, 9999999999); 

	$ImageName 		= str_replace(' ','-',strtolower($_FILES['ImageFile']['name'])); //get image name
	$ImageSize 		= $_FILES['ImageFile']['size']; // get original image size
	$TempSrc	 	= $_FILES['ImageFile']['tmp_name']; // Temp name of image file stored in PHP tmp folder
	$ImageType	 	= $_FILES['ImageFile']['type']; //get file type, returns "image/png", image/jpeg, text/plain etc.

	//Let's check allowed $ImageType, we use PHP SWITCH statement here
	switch(strtolower($ImageType))
	{
		case 'image/png':
			//Create a new image from file 
			$CreatedImage =  imagecreatefrompng($_FILES['ImageFile']['tmp_name']);
			break;
		case 'image/gif':
			$CreatedImage =  imagecreatefromgif($_FILES['ImageFile']['tmp_name']);
			break;			
		case 'image/jpeg':
		case 'image/pjpeg':
			$CreatedImage = imagecreatefromjpeg($_FILES['ImageFile']['tmp_name']);
			break;
		default:
			$reply = array("response" => 0, "Message" => "Unsupported Error");
			die(json_encode($reply));
	}
	
	//PHP getimagesize() function returns height/width from image file stored in PHP tmp folder.
	//Get first two values from image, width and height. 
	//list assign svalues to $CurWidth,$CurHeight
	list($CurWidth,$CurHeight)=getimagesize($TempSrc);
	
	//Get file extension from Image name, this will be added after random name
	$ImageExt = substr($ImageName, strrpos($ImageName, '.'));
  	$ImageExt = str_replace('.','',$ImageExt);
	
	//remove extension from filename
	//$ImageName 		= preg_replace("/\\.[^.\\s]{3,4}$/", "", $ImageName)
	
	$ImageName	=preg_replace('/[^A-Za-z0-9.]/','', $ImageName); 
	$ImageName 	= preg_replace("/\\.[^.\\s]{3,4}$/", "", $ImageName);
	//Construct a new name with random number and extension.
	$NewImageName = $ImageName.'-'.$RandomNumber.'.'.$ImageExt;
	
	//set the Destination Image
	$DestRandImageName 			= $DestinationDirectory.$NewImageName; // Image with destination directory
	
	switch(strtolower($ImageType))
	{
		case 'image/png':
			imagepng($CreatedImage,$DestRandImageName);
			break;
		case 'image/gif':
			imagegif($CreatedImage,$DestRandImageName);
			break;			
		case 'image/jpeg':
		case 'image/pjpeg':
			imagejpeg($CreatedImage,$DestRandImageName,$Quality);
			break;
		default:
			$reply = array("response" => 0, "Message" => "Format Error");
			die(json_encode($reply));
	}
	
	//Resize image to Specified Size by calling resizeImage function.
	if(resizeImage($CurWidth,$CurHeight,$BigImageMaxSize,$DestRandImageName,$CreatedImage,$Quality,$ImageType))
	{
		$reply = array("response" => 1,"image" => $NewImageName);
		echo json_encode($reply);
	}
	else{
		//die('Resize Error'); //output error
		$reply = array("response" => 0, "Message" => "Resize Error");
		die(json_encode($reply));
	}
}

// This function will proportionally resize image 
function resizeImage($CurWidth,$CurHeight,$MaxSize,$DestFolder,$SrcImage,$Quality,$ImageType)
{

	//Check Image size is not 0
	if($CurWidth <= 0 || $CurHeight <= 0) 
	{
		return false;
	}
	
	//Construct a proportional size of new image
	$ImageScale      	= min($MaxSize/$CurWidth, $MaxSize/$CurHeight); 
	$NewWidth  			= ceil($ImageScale*$CurWidth);
	$NewHeight 			= ceil($ImageScale*$CurHeight);
	$NewCanves 			= imagecreatetruecolor($NewWidth, $NewHeight);
	
	// Resize Image
	if(imagecopyresampled($NewCanves, $SrcImage,0, 0, 0, 0, $NewWidth, $NewHeight, $CurWidth, $CurHeight))
	{
		switch(strtolower($ImageType))
		{
			case 'image/png':
				imagepng($NewCanves,$DestFolder);
				break;
			case 'image/gif':
				imagegif($NewCanves,$DestFolder);
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
				imagejpeg($NewCanves,$DestFolder,$Quality);
				break;
			default:
				return false;
		}
	//Destroy image, frees memory	
	if(is_resource($NewCanves)) {imagedestroy($NewCanves);} 
		return true;
	}

}
?>