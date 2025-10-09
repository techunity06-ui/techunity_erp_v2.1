<?php 
/* Added by : Dimple Panchal Start*/
// to fetch lost reasons from master table
function get_lost_reasons($dbcon,$id){
    $qry="select id,reason from tbl_reason_mst where status = 0";
    $rs_state = $dbcon->query($qry);	
    $str = '';
    $str .= "<option value=''>Choose Reason</option>";
    $e_id = explode(",",$id);
    while($row = brp_mysqli_fetch_assoc($rs_state))
    {	
        // $sel='';
        if(in_array($row['id'],$e_id))
            {$sel='selected="selected"';} else {$sel="";}
        $str.= '<option '.$sel.' value="'.$row['id'].'">'.$row['reason'].'</option>';
    }
    return $str;
}

// to retrive quotation number
function load_quotation_no($dbcon){
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=3 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
	$rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
	if($rows['invoice_format']=='2'){
		$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
	}
	else if($rows['invoice_format']=='1'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
	}
	else if($rows['invoice_format']=='3'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
	}
	else{
		$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
	}
	return $row['invoiceno'];
}

// if inquiry won without quotation, quotation will generate automatically. 
function auto_create_quotation($dbcon,$data, $inquiryid =0){
    $product_data = get_inquiry_products($dbcon, $inquiryid);
    
    $quot_validity = $dbcon->query("select quot_validity from tbl_company where company_id=".$_SESSION['company_id'])
    ->fetch_object()->quot_validity;

    if($data['cust_id']){
        $query = $dbcon -> query("SELECT tc.cust_name,tc.cust_mobile,tc.cust_gst,ca.c_add_location, ca.c_add_country, ca.c_add_state, ca.c_add_city
            FROM tbl_customer tc
            LEFT JOIN tbl_cust_address as ca ON ca.cust_id = tc.cust_id
            WHERE tc.cust_id = ".$data['cust_id']);
        $cust_data = $query->fetch_assoc();
        $quote_info['qt_company_name']  = $cust_data['cust_name'];
        $quote_info['qt_com_mno']       = $cust_data['cust_mobile'];
        $quote_info['qt_com_gstno'] = $cust_data['cust_gst'];
        $quote_info['qt_com_addr']  = $cust_data['c_add_location'];
        $quote_info['qt_add_country']   = $cust_data['c_add_country'];
        $quote_info['qt_add_state'] = $cust_data['c_add_state'];
        $quote_info['qt_add_city']  = $cust_data['c_add_city'];

        $cust_stateid = $cust_data['c_add_state'];
    }

        // Quotation Creation 
    $quote_info['quotation_date']   = date('Y-m-d');
    $quote_info['quotation_no'] = load_quotation_no($dbcon);
    $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=3 and company_id=".$_SESSION['company_id']);

    $quote_info['cust_id']      = $data['cust_id'];
    $quote_info['c_con_id']     = $data['c_con_id'];
    $quote_info['inquiry_id']           = $inquiryid;
    $quote_info['quot_subject']         = $data['inquiry_no'];
    $quote_info['quot_type']            = 0;
    $quote_info['quotation_valid_date'] = date('Y-m-d', strtotime('+'.$quot_validity.' days'));
    $quote_info['quotation_ref']    = 'Quotation';
    $quote_info['quot_remark']          = 'Auto Created Quotation for :'.$data['inquiry_no'];
    $quote_info['g_total']      = $data['g_total'];
    $quote_info['quot_address']         = $cust_data['c_add_location'];
    $quote_info['create_date']          = date('Y-m-d H:i:s');
    $quote_info['cdate']        = date("Y-m-d H:i:s");
    $quote_info['user_id']      = $_SESSION['user_id'];
    $quote_info['company_id']           = $_SESSION['company_id'];
    $quotation_id = add_record('tbl_quotation', $quote_info, $dbcon);

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        QUOTATION_SLUG_FINAL_APPROVE
    ]);
    $final_btn_per= in_array(QUOTATION_SLUG_FINAL_APPROVE, $bulkAccessArray);
    if($final_btn_per){
        $infoaprvqt['approve_status']   = 1;
        $updateid=update_record('tbl_quotation', $infoaprvqt,"quotation_id=".$quotation_id , $dbcon, $branch_id);

        // add approve log 
        $info1['approve_remark']    = 'Auto Approved by Admin';
        $info1['approve_status']    = 1;
        $info1['quotation_id']          = $quotation_id;
        $info1['user_id']       = $_SESSION['user_id'];
        $info1['company_id']            = $_SESSION['company_id'];

        $inserid=add_record("tbl_quot_aprv_log", $info1, $dbcon, $branch_id);
    }
    $dbcon->query("update tbl_quotation set start_quotation_id=".$quotation_id." where quotation_id=".$quotation_id);

    if($quotation_id && $product_data){
        foreach ($product_data as $product) {
            $company_state = get_company_data($dbcon,$_SESSION['company_id']);
            //$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
            $sale_gst = get_tax_cat_by_hsn($dbcon,$product['product_hsn_code']);

            $cgst_tax_rate=0;
            $sgst_tax_rate=0;
            $igst_tax_rate=0;
            if(($company_state['stateid'] == $cust_stateid)){
                $gst = $sale_gst['tax_gst']/2;
                $cgst_tax_per = $gst;
                $cgst_tax_rate = ($gst*$product['product_amount'])/100;
                $sgst_tax_per = $gst;
                $sgst_tax_rate = ($gst*$product['product_amount'])/100;
            }else{
                $igst_tax_per = $sale_gst['tax_gst'];
                $igst_tax_rate = ($sale_gst['tax_gst']*$product['product_amount'])/100;
            }
            $quote_trn['quotation_id']      = $quotation_id;
            $quote_trn['product_id']        = $product['product_id'];
            $quote_trn['product_desc']      = $product['product_desc'];
            $quote_trn['product_spec']      = $product['product_spec'];
            $quote_trn['level_id']          = $product['level_id'];
            $quote_trn['unitid']            = $product['unitid'];
            $quote_trn['product_qty']       = $product['product_qty'];
            $quote_trn['product_rate']      = $product['product_rate'];
            $quote_trn['product_discount']  = $product['product_discount'];
            $quote_trn['discount_per']      = $product['discount_per'];
            $quote_trn['product_amount']    = $product['product_amount'];
            $quote_trn['formulaid']         = $product['formulaid'];
            $info=get_product_common_tax($dbcon,$product['product_amount'],$product['formulaid']);
            $quote_trn=array_merge($quote_trn,$info);
            $quote_trn['cgst_tax_per']      = isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
            $quote_trn['cgst_tax_rate'] = isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
            $quote_trn['sgst_tax_per']      = isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
            $quote_trn['sgst_tax_rate']     = isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
            $quote_trn['igst_tax_per']      = isset($igst_tax_per) ? $igst_tax_per : 0 ;
            $quote_trn['igst_tax_rate']         = isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
            $quote_trn['product_tax_cat']   = $sale_gst['tax_cat_id'];
            $quote_trn['user_id']           = $_SESSION['user_id'];
            $quote_trn['company_id']        = $_SESSION['company_id'];
            $quote_trn_id = add_record('tbl_quotation_trn', $quote_trn, $dbcon);
        }
    }
}
// checks if inquiry has quotation or not
function check_has_quotation($dbcon,$inquiry_id){
    $quotation_id = $dbcon->query("SELECT quotation_id FROM tbl_quotation WHERE inquiry_id=".$inquiry_id)
    ->fetch_object()->quotation_id;
    return ($quotation_id) ? $quotation_id : 0;
}

// fetch all products for inquiry
function get_inquiry_products($dbcon,$inquiryId = 0){
    if($inquiryId){
        $query="select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,trn.product_desc as description,trn.product_spec as spec,cat.cat_name,unit.unit_name as rat_unit, hsn.hsn_code as product_hsn_code, buni.unit_name as base_unit, cuni.unit_name as conv_unit from tbl_inquiry_trn as trn 
        left join product_mst as pro on pro.product_id  = trn.product_id 
        left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
        left join tbl_category as cat on cat.cat_id=trn.cat_id
        left join unit_mst as unit on unit.unitid=trn.rate_unit
        left join unit_mst as buni on buni.unitid = trn.unitid
        left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
        where trn.inquiry_trn_status=0 and trn.inquiry_id=".$inquiryId;
        /* END */ 
    }
    else{
        $query="select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,trn.product_desc as description,trn.product_spec as spec,cat.cat_name,unit.unit_name as rat_unit, hsn.hsn_code as product_hsn_code,buni.unit_name as base_unit, cuni.unit_name as conv_unit from tbl_inquiry_trn as trn 
        left join product_mst as pro on pro.product_id  = trn.product_id 
        left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
        left join tbl_category as cat on cat.cat_id=trn.cat_id
        left join unit_mst as unit on unit.unitid=trn.rate_unit
        left join unit_mst as buni on buni.unitid = trn.unitid
        left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
        where trn.inquiry_trn_status=3 and trn.user_id=".$_SESSION['user_id'];
        /* END */ 
    }
    $result = brp_mysqli_query($dbcon,$query);
    $products = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);
    return $products;
        //return $query;
}
