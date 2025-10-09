<?php 

	$fstat=$_POST['fstat'];
	
	if($fstat=='1')
	{
		$query="select * from tbl_followup_status where f_status=0 and f_id='2' and f_id='4'";
		$rs_cust=$dbcon->query($query);	
	}
	
	$html='';
	$html .="<select class='form-control'>";
	while($rel=mysqli_fetch_assoc($rs_cust))
	{	
		
		if($rel['f_id']==$id) {
			$sel="selected='selected'";
		}
		
		$html.='<option '.$sel.' value="'.$rel['f_id'].'">'.$rel['followup_id'].'</option>';
	}
	$html="<select>";
	echo $html;
?>