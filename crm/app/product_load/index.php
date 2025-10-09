<?php
session_start(); //start session
ini_set('memory_limit', '-1');
set_time_limit(0);
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';
// Amish Soni Start 30-12-2020
include_once($incPath."common_send_email.php");
// Amish Soni End 30-12-2020
$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
$getspecialConfiguration=getspecialConfiguration($dbcon);
// error_reporting(E_ALL);
if(strtolower($POST['mode']) == "product_load") {
    $drawing_number = '';
    $item_code = '';
    $alias = '';
    $whr = '';

    $companyConfiguration=getCompanyConfiguration($dbcon);
    $crm_pro_type=$companyConfiguration['crm_pro_type'];
    $so_pro_type=$companyConfiguration['so_pro_type'];
    $indent_po_pro_type=$companyConfiguration['indent_po_pro_type'];
    $production_pro_type=$companyConfiguration['production_pro_type'];
    $crm_pro_search=$companyConfiguration['crm_pro_search'];
    $purchase_pro_search=$companyConfiguration['purchase_pro_search'];
    $sales_pro_search=$companyConfiguration['sales_pro_search'];
    $bom_pro_search=$companyConfiguration['bom_pro_search'];
    $production_pro_search = $companyConfiguration['production_pro_search'];
    $rejection_pro_type = $companyConfiguration['rejection_pro_type'];
    $inventory_pro_type = $companyConfiguration['inventory_pro_type'];
    
    $inquiry_type = $POST['inquiry_type'];
	$product_category = $POST['product_category'];
    $quotation_id = $POST['quotaion_id'];

    $type = strtolower($POST['type']);
    $search = strtolower($POST['search']);
    if(isset($POST['product_type']) && $POST['product_type'] != ""){
        $whr .= " and pro.product_type = " . $POST['product_type'];
    }else if($type=='crm_pro_type'){
        $whr=' and pro.product_type in('.$crm_pro_type.')';
    } else if($type=='so_pro_type'){
        $whr=' and pro.product_type in('.$so_pro_type.')';
    } else if($type=='production_pro_type'){
        $whr=' and pro.product_type in('.$production_pro_type.')';
    } else if($type=='indent_po_pro_type'){
        $whr=' and pro.product_type in('.$indent_po_pro_type.')';
    } else if($type=='rejection_pro_search'){
        $whr=' and pro.product_type in('.$rejection_pro_type.')';
    } else if($type=='inventory_pro_type'){
        $whr=' and pro.product_type in('.$inventory_pro_type.')';
    }

    if(isset($POST['is_process_required_check']) && $POST['is_process_required_check']== '1'){
        $pro_typ_q = "select group_concat(product_type_id) as product_type FROM pro_ms_product_type where product_type_status = 0 and process_required = 1 and company_id  in (0,".$_SESSION['company_id'].")";
        $pro_typ_rs = $dbcon->query($pro_typ_q);
        $pro_typ_rw = brp_mysqli_fetch_assoc($pro_typ_rs);

        if(!empty($pro_typ_rw['product_type'])){
            $whr=' and pro.product_type in('.$pro_typ_rw['product_type'].')';    
        }
    }

    if($search=='crm_pro_search'){
        $pro_search=explode(",", $crm_pro_search);
    } else if($search=='purchase_pro_search'){
        $pro_search=explode(",", $purchase_pro_search);
    } else if($search=='sales_pro_search'){
        $pro_search=explode(",", $sales_pro_search);
    } else if($search=='bom_pro_search'){
        $pro_search=explode(",", $bom_pro_search);
    } else if($search == 'production_pro_search'){
        $pro_search=explode(",", $production_pro_search);
    } else if($search == 'rejection_pro_search'){
        $pro_search=explode(",", $rejection_pro_type);
    }

    
    /*var_dump($POST['product_category']);*/
    if(isset($POST['product_category']) && $POST['product_category'] != ""){
        $whr .= " and pro.product_category = ". $POST['product_category'];
    }

	if($getspecialConfiguration['filter_concept_permission'] ==1)
	{
		if($POST['po_type'] == 1){ 
			$query ="SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name from product_mst as pro left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where pro.product_status=0  and pro.product_type=8 AND pro.company_id IN (0,".$_SESSION['company_id'].") order by pro.product_name";
		}else{
			$query="SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name from product_mst as pro left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where pro.product_status=0 AND pro.company_id IN (0,".$_SESSION['company_id'].")".$whr." order by pro.product_name";
		}
	}
	else
	{
        if($inquiry_type=='2'){
            $query = "SELECT product_id, product_name, product_alias_name FROM product_mst WHERE product_status = 0 AND product_type = '-1' AND company_id IN (0,".$_SESSION['company_id'].")";
        }else{
    		if($POST['po_type'] == 1)
    		{ 
    			$query ="SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name from product_mst as pro left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where pro.product_status=0  and pro.product_type=8 AND pro.company_id IN (0,".$_SESSION['company_id'].") order by pro.product_name";
    		}else{
    			if($getspecialConfiguration['aeon_permission'] ==1)
    			{
    				if($product_category=='')
    				{
    				    $query="SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name from product_mst as pro left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where pro.product_status=0 AND pro.company_id IN (0,".$_SESSION['company_id'].")".$whr." order by pro.product_name";
    				}
    				else
    				{
    				    $query="SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name from product_mst as pro left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where pro.product_status=0 and pro.product_category=".$product_category."  AND pro.company_id IN (0,".$_SESSION['company_id'].")".$whr." order by pro.product_name";	
    				}
    			}
    			else
    			{
    				$query="SELECT pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name from product_mst as pro left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where pro.product_status=0 AND pro.company_id IN (0,".$_SESSION['company_id'].")".$whr." order by pro.product_name";
    			}
    		}
        }
	}

    if(!empty($quotation_id)){
        $query = "select qtrn.product_id,pro.product_name, pro.product_icode, dr.drawing_number, pro.product_alias_name,qtrn.quot_trn_id from tbl_quotation_trn as qtrn 
        left join product_mst as pro on pro.product_id = qtrn.product_id
        left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id 
        where qtrn.quot_trn_status=0 and qtrn.quotation_id=".$quotation_id;
    }
	
	// echo $query;
	
    $result=$dbcon->query($query);
	// echo "<pre>"; var_dump($crm_pro_type);var_dump($query); die();
    $i=0;
     $row1[0][]="";
        $row1[1][]="Select Product";
        $row1[2][]="";
    while($row=mysqli_fetch_array($result)){
		
        if(in_array('drawing',$pro_search)){
            $drawing_number = " -- (".$row['drawing_number'].")";
        }
        if(in_array('item',$pro_search)){
            $item_code = " -- (".$row['product_icode'].")";
        }
        if(in_array('alias',$pro_search)){
            $alias = " -- (".$row['product_alias_name'].")";
        }
        $row1[0][]=$row['product_id'];
        $row1[1][]=$row['product_name'].' '.$item_code.' '.$drawing_number.' '.$alias;
        $row1[2][]=$row['quot_trn_id'];
    }
	//$row=mysqli_fetch_array($result);		
    // print_r($POST);
	//echo "<pre>"; print_r($row1); die();
    echo json_encode($row1); //die();
}
?>