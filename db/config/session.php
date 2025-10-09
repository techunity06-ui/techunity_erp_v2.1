<?php
/**User Type wise permission code START
	$arr=explode("/",$_SERVER['PHP_SELF']);
	$page_name=end($arr);
	if($page_name!='index.php' && $page_name!='setting.php' && $page_name!='setting.php' && $page_name!='changepassword.php')
	{
		$page_name=basename($page_name, '.php');
		$menuqry="select pid from tbl_menu as menu left join tbl_permission as per on per.menu_id=menu.menu_id left join tbl_usertype as type on type.usertype_id=per.usertype_id where menu.status!=2 and page_name like '%".$page_name."%' and   per.usertype_id=".$_SESSION['user_type'];
		$menurel=mysqli_fetch_assoc($dbcon->query($menuqry));	
		if(empty($menurel))
		{
			echo '<META http-equiv="refresh" content="0;URL='.ROOT_F.'">';	
		}
	}	
User Type wise permission code END**/
	if(!isset($_SESSION['LOGGED_IN']) || $_SESSION['domain']!=DOMAIN) {	
			//echo 'hietn1';
			/*$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$url = urlencode($url);
			//die("SESSION_END");*/
			$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$url = urlencode($url);
			header("Location: ".DOMAIN."login?redirect=".$url);
			//header("Location: <a href='".DOMAIN."login?redirect=".$url."'></a>");
	}
	/*else {
		//if(!isset($_SESSION['LOGGED_IN']) && $_SESSION['domain']!=DOMAIN) 
		{
			
			
			
		}
	}	*/
?>