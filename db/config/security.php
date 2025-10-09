<?php
/*SPECIAL FUNCTIONS*/
function rmv($str) {
	if($str == NULL || $str == '') {
		return "NO-DATA";
	}
	else {
		$str = $str;
		return strtoupper($str);
	}
}	
function filter_data($connection_link, $data) {
	$str = $connection_link -> real_escape_string($data);
	$str = ($str);
	return strtoupper($str);
}
function bulk_filter($connection_link,$array) {
	while ($ele = current($array)) {
		if(is_array($ele)) {
			$key = key($array);
			$value = bulk_filter($connection_link,$ele);
			$array[$key] = $value;
		}
		else {
			$key = key($array);
			$value = filter_data($connection_link,$ele);
			$array[$key] = $value;
		}
		next($array);
	}
	return $array;
}	
	
/**
 * Returns an encrypted & utf8-encoded
 */
 
 $key=md5('india');

//Encrypt Function
function encrypt($string, $key)
{
	$string=rtrim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $string, MCRYPT_MODE_ECB)));
	return $string;
}
//Deccrypt Function
function decrypt($string, $key)
{
	$string=rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($string), MCRYPT_MODE_ECB));
	return $string;

} 
function check_internet_connection($sCheckHost = 'www.google.com') 
{
	return (bool) @fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 5);
}
function encryptt($pure_string) {
	$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, ENCRYPTION_KEY, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
	return $encrypted_string;
}
/**
 * Returns decrypted original string
 */
function decryptt($encrypted_string) {
	$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, ENCRYPTION_KEY, $encrypted_string, MCRYPT_MODE_ECB, $iv);
	return $decrypted_string;
}	
//Convert to Indian Currency Format
// Multi user Inquiery Show Funcation
function check_user_inq($alias)
{
	$qry='';
	if($_SESSION['user_type']!='2')
	{
		$qry="  and FIND_IN_SET ($_SESSION[user_id],".$alias.".show_user_ids)";
	}
	return $qry;
}
/////////////////

function check_user($alias)
{
    $qry='';
    if($_SESSION['user_type']!='2' && $_SESSION['user_type']!='1')
    {
        $qry="  and ".$alias.".user_id in ($_SESSION[user_id])";
    }
    return $qry;
}
function check_userid($alias)
{
    $qry='';
    if($_SESSION['user_type']!='2' && $_SESSION['user_type']!='1')
    {
        $qry="  and ".$alias.".userid in ($_SESSION[user_id])";
    }
    return $qry;
}
function check_company($alias)
{
    $qry='';
    
    $qry="  and ".$alias.".company_id in ($_SESSION[company_id])";
   
    return $qry;
}

//Amish Soni Start 12-02-2021
function check_branch($alias, $branch_id = 0)
{
    $qry = "";
    $branch_condition = "";
    $branch_id = $branch_id ? $branch_id : $_SESSION['branch_id'];
    $branch_id = (int)$branch_id;

    if ($branch_id) {
        // for admin 
        if($_SESSION['user_type'] == '2'){
            if($branch_id != '0' && $branch_id != '1000') {
                $branch_condition = " branch_id = ".$branch_id;
            }
        } 
        else {
            $branch_condition = " branch_id IN (".$branch_id.",1000)";
        }
        
        if($branch_condition){
            if($alias){
                $qry = " and ".$alias.".".$branch_condition;
            } else {
                $qry = " and ".$branch_condition;
            }
        }
        
    }
	return $qry;
}
//Amish Soni End 12-02-2021

function text_rnremove($text)
{
	//return str_replace("\r\n","<br/>",($text));
	return str_replace(array("\n", "\r", "\N"), '', $text);
}
function text_rnrremove_disp($text)
{
	return str_replace("<br/>","\n",($text));
}
function indian_number($n, $d = 0) {
	$n = number_format($n, $d, '.', '');
	$n = strrev($n);
	if ($d) $d++;
	$d += 3;
	if (strlen($n) > $d)
		$n = substr($n, 0, $d) . ','
		   . implode(',', str_split(substr($n, $d), 2));

	return strrev($n);
}	
//@End
function convert_number_to_words($num = false,$currency='') {
    var_dump($currency);
 	$num = str_replace(array(',', ' '), '' , trim($num));
    $fraction='';
    if(! $num) {
        return false;
    }
	
    $words = array();
	
		$list1 = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven',
        'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
		);
		$list2 = array('', 'Ten', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety', 'Hundred');
		$list3 = array('', 'Thousand', 'Million', 'Billion', 'Trillion');


    $x = $num - floor($num);

    //$r = fmod($x, 10);
	
	if($x > 0 )
	{
	
	    $x1 = $x*100;
		$x = round($x1);
		if ( $x < 20 ) {
            $tens = ($x ? ' ' . $list1[$x] . ' ' : '' );
		
             } else {
                $tens = (int)($x / 10);
				$tens = ' ' . $list2[$tens] . ' ';
				$singles = (int) ($x % 10);
				$singles = ' ' . $list1[$singles] . ' ';
				
            }

		$fraction=  $tens.'-'.$singles.'Rupees';
	
	}
    $num = (int) $num;
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] .' '. $list2[10] . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ( $tens < 20 ) {
            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles =(int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
		
		
        $words[] = $hundreds. $tens .$singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
    } //end for loop
	
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
	if($fraction == ''){
		$and = '';
	}else{
		$and = 'and';
	}
    return $currency;
   //return  implode(' ', $words)."Rupees".' ' .$and .$fraction." Only " ;
}


function smart_resize_image($file,
                              $width              = 0, 
                              $height             = 0, 
                              $proportional       = false, 
                              $output             = 'file', 
                              $delete_original    = true, 
                              $use_linux_commands = false ) {
      
    if ( $height <= 0 && $width <= 0 ) return false;
    # Setting defaults and meta
    $info                         = getimagesize($file);
    $image                        = '';
    $final_width                  = 0;
    $final_height                 = 0;
    list($width_old, $height_old) = $info;
    # Calculating proportionality
    if ($proportional) {
      if      ($width  == 0)  $factor = $height/$height_old;
      elseif  ($height == 0)  $factor = $width/$width_old;
      else                    $factor = min( $width / $width_old, $height / $height_old );
      $final_width  = round( $width_old * $factor );
      $final_height = round( $height_old * $factor );
    }
    else {
      $final_width = ( $width <= 0 ) ? $width_old : $width;
      $final_height = ( $height <= 0 ) ? $height_old : $height;
    }
    # Loading image to memory according to type
    switch ( $info[2] ) {
      case IMAGETYPE_GIF:   $image = imagecreatefromgif($file);   break;
      case IMAGETYPE_JPEG:  $image = imagecreatefromjpeg($file);  break;
      case IMAGETYPE_PNG:   $image = imagecreatefrompng($file);   break;
      default: return false;
    }
    
    
    # This is the resizing/resampling/transparency-preserving magic
    $image_resized = imagecreatetruecolor( $final_width, $final_height );
    if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
      $transparency = imagecolortransparent($image);
      if ($transparency >= 0) {
        $transparent_color  = imagecolorsforindex($image, $trnprt_indx);
        $transparency       = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
        imagefill($image_resized, 0, 0, $transparency);
        imagecolortransparent($image_resized, $transparency);
      }
      elseif ($info[2] == IMAGETYPE_PNG) {
        imagealphablending($image_resized, false);
        $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
        imagefill($image_resized, 0, 0, $color);
        imagesavealpha($image_resized, true);
      }
    }
    imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
    
    # Taking care of original, if needed
    if ( $delete_original ) {
      if ( $use_linux_commands ) exec('rm '.$file);
      else @unlink($file);
    }
    # Preparing a method of providing result
    switch ( strtolower($output) ) {
      case 'browser':
        $mime = image_type_to_mime_type($info[2]);
        header("Content-type: $mime");
        $output = NULL;
      break;
      case 'file':
        $output = $file;
      break;
      case 'return':
        return $image_resized;
      break;
      default:
      break;
    }
    
    # Writing image according to type to the output destination
    switch ( $info[2] ) {
      case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
      case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output);   break;
      case IMAGETYPE_PNG:   imagepng($image_resized, $output);    break;
      default: return false;
    }
    return true;
}
function strip_word_html($text, $allowed_tags = '<a><ul><li><b><i><sup><sub><em><strong><u><br><br/><br /><p><h2><h3><h4><h5><h6><span><div><table><tr><td><th><thead><tbody><tfoot>')
{
	mb_regex_encoding('UTF-8');
    //replace MS special characters first
    $search = array('/&lsquo;/u', '/&rsquo;/u', '/&ldquo;/u', '/&rdquo;/u', '/&mdash;/u');
    $replace = array('\'', '\'', '"', '"', '-');
    $text = preg_replace($search, $replace, $text);
    //make sure _all_ html entities are converted to the plain ascii equivalents - it appears
    //in some MS headers, some html entities are encoded and some aren't
    //$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    //try to strip out any C style comments first, since these, embedded in html comments, seem to
    //prevent strip_tags from removing html comments (MS Word introduced combination)
    if(mb_stripos($text, '/*') !== FALSE){
        $text = mb_eregi_replace('#/\*.*?\*/#s', '', $text, 'm');
    }
    //introduce a space into any arithmetic expressions that could be caught by strip_tags so that they won't be
    //'<1' becomes '< 1'(note: somewhat application specific)
    $text = preg_replace(array('/<([0-9]+)/'), array('< $1'), $text);
    $text = strip_tags($text, $allowed_tags);
    //eliminate extraneous whitespace from start and end of line, or anywhere there are two or more spaces, convert it to one
    $text = preg_replace(array('/^\s\s+/', '/\s\s+$/', '/\s\s+/u'), array('', '', ' '), $text);
    //strip out inline css and simplify style tags
    $search = array('#<(strong|b)[^>]*>(.*?)</(strong|b)>#isu', '#<(em|i)[^>]*>(.*?)</(em|i)>#isu', '#<u[^>]*>(.*?)</u>#isu');
    $replace = array('<b>$2</b>', '<i>$2</i>', '<u>$1</u>');
    $text = preg_replace($search, $replace, $text);
    
    $num_matches = preg_match_all("/\<!--/u", $text, $matches);
    if($num_matches){
        $text = preg_replace('/\<!--(.)*--\>/isu', '', $text);
    }
    $text = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $text);
	 
return $text;
}

function convert_number_to_dollar_words($num = false)
{
    $num = str_replace(array(',', ' '), '' , trim($num));
    $fraction='';
    if(! $num) {
        return false;
    }
    $words = array();
	$list1 = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven',
	'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
	$list2 = array('', 'Ten', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety', 'Hundred');
	$list3 = array('', 'Thousand', 'Million', 'Billion', 'Trillion');
	
	
    $x = $num - floor($num);
    //$r = fmod($x, 10);
	if($x > 0 )
	{
	    $x = round($x*100);
		if ( $x < 20 ) {
            $tens = ($x ? ' ' . $list1[$x] . ' ' : '' );
             } else {
                $tens = (int)($x / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($x % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }

		$fraction= '-'. $tens . $singles.' Cent';
		
	}
    $num = (int) $num;
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] .' '. $list2[10] . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ( $tens < 20 ) {
            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles = (int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
        $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
   
    return implode(' ', $words)." Dollars ".$fraction." Only ";
	
}
function check_permission($pagename,$usetype,$permission,$dbcon)
{
    if(strtolower($permission)=='view'){
        $query="SELECT perm.* FROM `tbl_permission` as perm left join tbl_menu as menu on perm.menu_id=menu.menu_id where perm.permission=1 and menu.status=0 and perm.status=0 and usertype_id=".$usetype." and menu.page_name ='".$pagename."'";
    }
    else{
        $query="SELECT perm.* FROM `tbl_permission` as perm left join tbl_menu as menu on perm.menu_id=menu.menu_id where perm.".$permission."_permission=1 and menu.status=0 and perm.status=0 and usertype_id=".$usetype." and menu.page_name ='".$pagename."'";
    }
	$rs=$dbcon->query($query);
	if(mysqli_num_rows($rs)>0)
	{
		return true;
	}
	return false;
}

function canCheckPermissionAccess($dbcon, $slugs) {
    $company_id = $_SESSION['company_id'];
    $user_id = $_SESSION['user_id'];
    $accessibleSlugs = $getData = $accessibleIds = [];

    $qry="SELECT `user_id`,`user_access_permission` FROM `users` 
        WHERE `active` = 0 AND company_id = '".$company_id."' and user_id = '".$user_id."'";
    
    $user_data = $dbcon->query($qry);

    if($user_data->num_rows > 0) {
        $recorduserData = $user_data->fetch_assoc();
        $userpermission = explode(',', $recorduserData['user_access_permission']);
        $userpermissionData =  "'" . implode ( "', '", $userpermission ) . "'";
        $tempqry = "SELECT * FROM `menu_master_access_routes` 
        WHERE `status` = 0 AND `id` IN (".$userpermissionData.")" ;
        
        $template_data = $dbcon->query($tempqry);
        while($row = brp_mysqli_fetch_assoc($template_data)) {
            $accessibleIds[] = $row['slug_name'];
        }
        $accessibleSlugs = array_intersect($accessibleIds, $slugs);
    }
    return $accessibleSlugs;
}

function getLeftMenu($dbcon,$parent_id){
    $html = $where = '';
    $menu_show_permission = [];
    $user_id = $_SESSION['user_id'];
    $quserdata = $dbcon->query("SELECT * FROM `users` WHERE `user_id` = '".$user_id."'");
    
	$recorduserData = $quserdata->fetch_assoc();
	if(isset($recorduserData['menu_show_permission']) && $recorduserData['menu_show_permission'] != ''){
		$menu_show_permission = explode(",",$recorduserData['menu_show_permission']);
		$menupermissionData =  "'" . implode ( "', '", $menu_show_permission ) . "'";
	}

    $parent_qry = "SELECT `menumaster`.`id`,`menumaster`.`parent_id`,`menumaster`.`menu_name`,`menumaster`.`menu_fa_icon`,`menumaster`.`user_id`,`menumaster`.`menu_path` FROM `menu_master_access` as `menumaster` WHERE `menumaster`.`status` = '0' and `menumaster`.`parent_id` IN (".$parent_id.") and `menumaster`.`id` IN (".$menupermissionData.") ORDER BY `menumaster`.`menu_order`";
    $result = mysqli_query($dbcon,$parent_qry);
    $parent_data = mysqli_fetch_all($result,MYSQLI_ASSOC);
    if($parent_data){
        foreach($parent_data as $menu_item){
        	$has_child = $dbcon->query("SELECT `menumaster`.`id`,`menumaster`.`parent_id`,`menumaster`.`menu_name`,`menumaster`.`menu_fa_icon`,`menumaster`.`user_id`,`menumaster`.`menu_path` FROM `menu_master_access` as `menumaster` WHERE `menumaster`.`status` = '0' and `menumaster`.`parent_id`=".$menu_item['id']." ORDER BY `menumaster`.`menu_order`")->fetch_object()->id;
        	if($menu_item['menu_path'] != '0'){
        		$html .= '<li class="sub-menu">
        			  <a href="'.ROOT.strtolower($menu_item['menu_path']).'" title="'.ucwords(strtolower($menu_item['menu_name'])).'">
        			  <i class="fa '.strtolower($menu_item['menu_fa_icon']).'"></i><span>
                	 '.ucwords(strtolower($menu_item['menu_name'])).'</span></a>';
            }else{
            	$html .= '<li class="sub-menu">
        			  <a href="javascript:;" title="'.ucwords(strtolower($menu_item['menu_name'])).'">
        			  <i class="fa '.strtolower($menu_item['menu_fa_icon']).'"></i><span>
                	 '.ucwords(strtolower($menu_item['menu_name'])).'</span></a>';
            }
            if($has_child){
            		$html .= '<ul class="sub">'.getLeftMenu($dbcon, $menu_item['id']).'</ul>';
            		$html .= '</li>';
            }else{
            	$html .= '</li>';
            	$html .= getLeftMenu($dbcon, $menu_item['id']);
        	}
        }
    }
    return $html; 
}

//Amish Soni Start 30-3-2021
function convert_number_to_words_new($number = false)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'One', 2 => 'Two',
        3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
        10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
        19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
        40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty',
        70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
    $digits = array('', 'Hundred','Thousand','Lakh', 'Crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? "and " . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise Only' : '';
    $only = $paise ? '' : 'Only ';
    return ($Rupees ? $Rupees . 'Rupees '.$only : '') . $paise;
}
//Amish Soni End 30-3-2021

function getCheckRelation($dbcon, $sTable = [], $aColumns = [], $sWhere = []){
    $temp = [];
    $i = 0;
    foreach ($sTable as $key => $value) {
        # code...
        $where = $sWhere[$i][0];
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns[$i]))."
                   FROM $key
                   WHERE $where";
        
        $rResult = $dbcon->query($sQuery);
       // var_dump($sQuery);
        $cnt=mysqli_num_rows($rResult);
        if($cnt > 0){
            $temp[] = $value;
        }   
        $i++;        
    }
    if($temp){
        return $temp;
    }else{
        return [];
    }
    
}
//Devloped By Maulik Start
function getCheckusedMaster($dbcon,$column_name,$tbl_name,$column_status,$used_status,$master_id){
    $query = "select count(".$column_name.") as cnt from ".$tbl_name." where ".$column_status."=".$used_status." and ".$column_name."=".$master_id." and company_id=".$_SESSION['company_id'];
    $tr = $dbcon -> query($query);
    $row = brp_mysqli_fetch_array($tr);
    return $row['cnt'];
}
//End Maulik 
// set function with two digit 
function without_comma_two_digit_amount($num){
    $num = sprintf('%.2f', $num);
    return $num;
}