<!DOCTYPE html>
<html>
<head>
<title>Demo</title>	
  <script src="ckeditor.js"></script>
</head>
<body>
  <center>
  <?php
   $con = @mysql_connect("localhost","root","root");
   $database = mysql_select_db("ajax");
    if(isset($_POST['submit']))
 {
  $file = $_POST['text'];
  if(isset($_POST['submit'])){ 
  $query ="INSERT INTO `ckeditor`(`file`) VALUES ('$file')";
  $result = mysql_query($query,$con);
  if(!$result)
  {
   echo 'Record Not Saved';
  }
  else
  {
	  
   echo 'Record Saved';
  }
  }  
 }
  ?>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	  <label id="l1">Enter name</label>	 
	  <input type="text" name=""/>
  <textarea name="text" id="text" rows="100">This is a demo text.</textarea>
  <script>
  CKEDITOR.replace( 'text' );
  </script><br/>
  	  
  <input type="submit" value="Save Changes" name="submit">
  </form>
  <?php
	  $query2 = "SELECT * from ckeditor";
	  $show = mysql_query($query2,$con);
	  while($row = mysql_fetch_array($show))
	  {
	  echo $row['file'];
	  }
  ?>
  </center>    
</body>
</html>