<?php 
  
session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	ini_set('max_execution_time', 3000000);
	
	if(isset($_POST['submit']) == 'submit')
	{
		
		$query_data = explode(";",$_POST['quert_text']);
		
//	echo "<pre>"; print_r($query_data);
		
		$q_erro = array();
		$q_not_erro = array();
	
	    foreach($query_data as $query)
	    {
	      
	       if($query != '')
	       {
	       $q = str_replace('"','',$query);
	       
	      
	       $results=$dbcon->query($q);
	        
	        
            if(!$results)
            {
                $errors =  mysqli_error($dbcon);
                $q_erro[] = array($q=>$errors);
            
            }
            else
            {
                $q_not_erro[] =  array($q);
               // echo "Query succesfully executed!";
            } 
        
        
	       }
	        

	    }
	      echo "<pre> Successfuly query "; print_r($q_not_erro);
        echo "<pre> Query Error  OR already Exists"; print_r($q_erro);
	    	die;
	}	
	

?>


	

	<form method="post">
		<table>
			<tr>
				<td>Query</td><td><textarea cols='150' rows='30' name="quert_text"></textarea></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" name="submit" value="Submit"/></td>
			</tr>
		</table>
