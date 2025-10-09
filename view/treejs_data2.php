<?php

include("../config/config.php");
include("../include/common_functions.php");
include("../include/function_database_query.php");


// Source type : json / html
$sourceType = $_REQUEST['sourceType'];

if($sourceType == 'html')
{
	$bom=$_REQUEST['bom_id'];
	$product=$_REQUEST['product'];
	
    $getParentNodes = "select * from tbl_bomtrn where parent_id='$product' and bom_id='$bom'";
    $resParentNodes = $dbcon->query($getParentNodes);
    $response = '';
    if(mysqli_num_rows($resParentNodes) > 0)
    {
		$cnt=1;
		while($parentNode = mysqli_fetch_assoc($resParentNodes))
        {
			$number="1.".$cnt;
			echo '<tr  data-id="'.$cnt.'" data-parent="">';
			
             get_tree($dbcon,$parentNode['product_id'],$parentNode['parent_id'],0,$cnt,$bom,$number);
			 
			echo '</tr>';
			$cnt++;
        }
    }

}

function get_tree($dbcon,$product_id,$parent_id,$level,$cnt,$bom,$number)
{
	
	
	if($level==0)
	{
		$pr_value=get_pro_field($dbcon,$product_id,'product_name');
		echo '
				<td class="td1">'.$number.'</td>
				<td class="td2">'.$pr_value.'</td>
				<td class="td3">'.$qty.'</td>
				';
	}
					
	$getChildNodes = "select * from tbl_bomtrn where parent_id = '".$product_id."' and bom_id='$bom'";
	$resChildNodes = $dbcon->query($getChildNodes);
	if(mysqli_num_rows($resChildNodes) > 0)
	{

		//echo '<ul class="jtree_parent_node">';
		
		$cntt=1;
		while($childNode = mysqli_fetch_assoc($resChildNodes))
		{
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			
			$getChildNodes1 = "select * from tbl_bomtrn where parent_id = '".$childNode['product_id']."' and bom_id='$bom'";
			$resChildNodes1 = $dbcon->query($getChildNodes1);
			if(mysqli_num_rows($resChildNodes1) > 0)
			{
				$new_number=$number.'.'.$cntt;
				
				echo '<tr data-id="'.$new_number.'" data-parent="'.$number.'">
				<td  class="td1">'.$new_number.'</td>
				<td class="td2">'.$pro_name.'</td>
				<td class="td3">'.$childNode['product_qty'].'</td>
				</tr>';
			
				$level++;$cnt++;$cntt++;
				
				get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom,$new_number);
				
			}
			else
			{
				$new_number=$number.'.'.$cntt;
				
				echo '<tr data-id="'.$new_number.'" data-parent="'.$number.'">
				<td  class="td1">'.$new_number.'</td>
				<td   class="td2">'.$pro_name.'</td>
				<td class="td3">'.$childNode['product_qty'].'</td>
				</tr>';
				
				$level++;$cnt++;$cntt++;
				//get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			
			//$cntt++;
		}
		
		
	}
	
}

?>