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
			echo '<ul class="jtree_parent_node">';
			
             get_tree($dbcon,$parentNode['product_id'],$parentNode['parent_id'],0,$cnt,$bom);
			 
			echo '</ul>';
        }
    }

}

function get_tree($dbcon,$product_id,$parent_id,$level,$cnt,$bom)
{
	
	
	if($level==0)
	{
		$pr_value=get_pro_field($dbcon,$product_id,'product_name');
		echo '
				<li>
					<span class="jtree_expand jtree_node_close"> </span>
					<label><input type="checkbox" name="sp_part[]" id="'. $product_id.'" parent-id="" class="jtree_parent_checkbox" value="'.$product_id.'"> '.$pr_value.'</label>';
	}
					
	$getChildNodes = "select * from tbl_bomtrn where parent_id = '".$product_id."' and bom_id='$bom'";
	$resChildNodes = $dbcon->query($getChildNodes);
	if(mysqli_num_rows($resChildNodes) > 0)
	{

		echo '<ul class="jtree_parent_node"  style="display: none;">';
		while($childNode = mysqli_fetch_assoc($resChildNodes))
		{
			$pro_name=get_pro_field($dbcon,$childNode['product_id'],'product_name');
			
			$getChildNodes1 = "select * from tbl_bomtrn where parent_id = '".$childNode['product_id']."' and bom_id='$bom'";
			$resChildNodes1 = $dbcon->query($getChildNodes1);
			if(mysqli_num_rows($resChildNodes1) > 0)
			{
				echo '
				<li>
					
					<label><input type="checkbox" id="'. $childNode['product_id'].'" parent-id="'. $parent_id.'" class="jtree_parent_checkbox" name="sp_part[]" value="'.$childNode['product_id'].'"> '. $pro_name.'</label>
				</li>';
			
				$level++;$cnt++;
				get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			else
			{
				
				echo '<li><label><input type="checkbox" id="'. $childNode['product_id'].'" parent-id="'. $parent_id.'" class="jtree_child_checkbox" name="sp_part[]" value="'.$childNode['product_id'].'"> '. $pro_name.'</label></li>';
				
				$level++;$cnt++;
				//get_tree($dbcon,$childNode['product_id'],$parent_id,$level,$cnt,$bom);
				
			}
			
			
		}
		
		echo '</ul>';
	}

	echo '</li>';
	 
		
}

?>