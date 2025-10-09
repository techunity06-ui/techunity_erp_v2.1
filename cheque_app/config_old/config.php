<?php
    error_reporting(0);
    date_default_timezone_set('Asia/Kolkata');	
	$authenticate = true;    
	/*
     * PROJECT DETAILS
     */   
    define("TITLE","metR Technology");
    define("DOMAIN","http://".$_SERVER["SERVER_ADDR"]."/hiten/metr_purchase_sale_soft/cheque_app/");
    define("ROOT","/hiten/cheque_app/");
    define("INC","/hiten/cheque_app/");
    define("CHEQUE_IMG","upload//check//");
	//define("CHEQUE_IMG","C:\\wamp\\www\\cheque_app_software\\view\\upload\\check\\");
	//define("BACKUP","C:\\wamp\\www\\cheque_app_software\\view\\upload\\backup\\");
    //define("COMPANY","metR Technology");
    define("C_URL","http://www.metrtechnology.com");
    define("DEVELOPER"," metR Technology");
    define("D_URL","http://www.metrtechnology.com");
    
    define("CITY","AHMEDABAD");
    define("COUNTRY","INDIA");
    define("CURRENCY","INR");
    define("C_SYMBOL","&#8377;");
    
    /*
     *	Database Credentials
     */
    define("SERVER","localhost");
    define("DB","kashyap_cheque_bill_soft");
    define("DB_USER","root");
    define("DB_PASS","");            
    /*
    * Admin Details
    */
    define("ADMIN","metR Technologies");
    define("ADMIN_EMAIL","mihirbhatt@metrtechnology.com");
    
    /*Database Connectivity*/
    $dbcon = new mysqli(SERVER,DB_USER,DB_PASS,DB);
    if($dbcon->connect_errno > 0){
            die('Unable to connect to database server. [' . $dbcon->connect_error . ']');
    }
    if(isset($_SESSION['permission'])) {
            $permission = unserialize($_SESSION['permission']);
    }	
    
    /*SPECIAL FUNCTIONS*/
    function rmv($str) {
		if($str == NULL || $str == '') {
			return "NO-DATA";
		}
		else {
			$str = strtoupper($str);
			return $str;
		}
    }
	
	function filter_data($connection_link, $data) {
		$str = $connection_link -> real_escape_string($data);
		$str = strtoupper($str);
		return $str;
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

	define("ENCRYPTION_KEY", "!@#$%^&*");
	/**
	 * Returns an encrypted & utf8-encoded
	 */
	 
	 $key=md5('india');

	//Encrypt Function
	function encrypt($string)
	{
		$string=rtrim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, ENCRYPTION_KEY, $string, MCRYPT_MODE_ECB)));
		return $string;
	}
	//Deccrypt Function
	function decrypt($string)
	{
		$string=rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, ENCRYPTION_KEY, base64_decode($string), MCRYPT_MODE_ECB));
		return $string;

	} 
	//Convert to Indian Currency Format
	//@Start
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
	function send_data($info)
	{
			//Prepare you post parameters
			$postData = array(
				'authkey' => $info['key'],
				'ip'=>$info['ip'],
				'city'=>$info['city'],
				'state'=>$info['state'],
				'country'=>$info['country'],
				'lng'=>$info['lng'],
				'lat'=>$info['lat'],
				'sender' => $info['company'],
				'address' => $info['address'],
				'contactno' => $info['contactno'],
				'email' => $info['email'],
				'type' => 'install'
			);
			
			//API URL
			$url="http://www.cheque360.com/chequesendhttp";
			// init the resource
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS =>($postData)
				//,CURLOPT_FOLLOWLOCATION => true
			));			
			//get response
			//$output = curl_exec($ch);			
			//Print error if any
			/*if(curl_errno($ch))
			{
				echo 'error:' . curl_error($ch);
				return 2;
			}
			else
				return 1;*/
			curl_close($ch);
			return $output;
			//API URL
			/*$url=("http://1acres.com/chequesendhttp.php?authkey=".$info['key']."&senderid=".$info['company']."&ip=".$info['ip']."&city=".$info['city']."&state=".$info['state']."&country=".$info['country']."&type=install");
			// init the resource
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0
			));
			//get response
			$output = curl_exec($ch);
			//Print error if any
			if(curl_errno($ch))
			{
				echo 'error:' . curl_error($ch);
			}
			curl_close($ch);
			return $output;*/ 
			//return $url;
	}
	function check_internet_connection($sCheckHost = 'www.1acres.com') 
	{
		return (bool) @fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 5);
	}

	/* COMPANY NAME*/
	/*$rs_usr=$dbcon->query('select user_company from coro_users where user_id=0');
	$rel=mysqli_fetch_assoc($rs_usr);
    
	define("COMPANY",decrypt($rel['user_company']));
	*/
	//@End

?>
