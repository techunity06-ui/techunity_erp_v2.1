<?php

function resizeImage($imagename,$imagesize,$imagetmp_name,$imagetype,$DestFolder,$MaxSize,$Quality)
{
		//$RandomNumber 			= rand(0, 9999999999); 
		$ImageName 		= str_replace(' ','-',strtolower($imagename)); //get image name
		$ImageSize 		= $imagesize; // get original image size
		$TempSrc	 	= $imagetmp_name; // Temp name of image file stored in PHP tmp folder
		$ImageType	 	= $imagetype; //get file type, returns "image/png", image/jpeg, text/plain etc.		
		switch(strtolower($ImageType))
		{
			case 'image/png'://Create a new image from file 			
				$SrcImage =  imagecreatefrompng($TempSrc);
				break;
			case 'image/gif':
				$SrcImage =  imagecreatefromgif($TempSrc);
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
				$SrcImage = imagecreatefromjpeg($TempSrc);
				break;
			default:
				die('Unsupported File!'); //output error and exit
		}
		list($CurWidth,$CurHeight)=getimagesize($TempSrc);	//list assign svalues to $CurWidth,$CurHeight //Get first two values from image, width and height. 	
		$ImageExt = substr($ImageName, strrpos($ImageName, '.'));//Get file extension from Image name, this will be added after random name
		$ImageExt = str_replace('.','',$ImageExt);		
		$ImageName = preg_replace("/\\.[^.\\s]{3,4}$/", "", $ImageName);	//remove extension from filename	
		$NewImageName = $ImageName.'.'.$ImageExt;	//Construct a new name with random number and extension.
		$DestFolder	= $DestFolder.$NewImageName; // Image with destination directory		
		
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
		//echo $NewCanves;
	//Destroy image, frees memory	
	if(is_resource($NewCanves)) {imagedestroy($NewCanves);} 
	return $NewImageName;
	}
}
function resizeImage_url($url,$DestFolder,$MaxSize,$Quality)
{
			$ImageName 		= basename($url); //get image name
			$imageinfo = getimagesize($url);
			$ImageType	 	= $imageinfo['mime']; //get file type, returns "image/png", image/jpeg, text/plain etc.
			$CurWidth=$imageinfo['0'];
			$CurHeight=$imageinfo['1'];
			$ImageExt = pathinfo($url, PATHINFO_EXTENSION);
			switch(strtolower($ImageType))
			{
			case 'image/png'://Create a new image from file 			
				$SrcImage =  imagecreatefrompng($url);
				break;
			case 'image/gif':
				$SrcImage =  imagecreatefromgif($url);
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
				$SrcImage = imagecreatefromjpeg($url);
				break;
			default:
				die('Unsupported File!'); //output error and exit
		}
		$ImageName = preg_replace("/\\.[^.\\s]{3,4}$/", "", $ImageName);	//remove extension from filename	
		$NewImageName = $ImageName.'.'.$ImageExt;	//Construct a new name with random number and extension.
		$DestFolder	= $DestFolder.$NewImageName; // Image with destination directory		
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
	return $NewImageName;
	}
}

//This function corps image to create exact square images, no matter what its original size!
function cropImage($CurWidth,$CurHeight,$iSize,$DestFolder,$SrcImage,$Quality,$ImageType)
{	 
	//Check Image size is not 0
	if($CurWidth <= 0 || $CurHeight <= 0) 
	{
		return false;
	}
	
	//abeautifulsite.net has excellent article about "Cropping an Image to Make Square bit.ly/1gTwXW9
	if($CurWidth>$CurHeight)
	{
		$y_offset = 0;
		$x_offset = ($CurWidth - $CurHeight) / 2;
		$square_size 	= $CurWidth - ($x_offset * 2);
	}else{
		$x_offset = 0;
		$y_offset = ($CurHeight - $CurWidth) / 2;
		$square_size = $CurHeight - ($y_offset * 2);
	}
	
	$NewCanves 	= imagecreatetruecolor($iSize, $iSize);	
	if(imagecopyresampled($NewCanves, $SrcImage,0, 0, $x_offset, $y_offset, $iSize, $iSize, $square_size, $square_size))
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
