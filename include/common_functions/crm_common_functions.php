<?php 
/* Added by : Dimple Panchal Start*/
// to fetch lost reasons from master table
function get_lost_reasons($dbcon,$id){
    $qry="select id,reason from tbl_reason_mst where status = 0 and company_id =".$_SESSION['company_id'];
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
    $companyConfiguration=getCompanyConfiguration($dbcon);
    $companySettings = getCompanySettings($dbcon);
    
    $quot_validity = $dbcon->query("select quot_validity from tbl_company where company_id=".$_SESSION['company_id'])
    ->fetch_object()->quot_validity;

    if($data['cust_id']){
        $query = $dbcon -> query("SELECT tc.cust_name,tc.cust_mobile,tc.cust_gst,ca.c_add_address, ca.c_add_country, ca.c_add_state, ca.c_add_city
            FROM tbl_customer tc
            LEFT JOIN tbl_cust_address as ca ON ca.cust_id = tc.cust_id
            WHERE tc.cust_id = ".$data['cust_id']);
        $cust_data = $query->fetch_assoc();
        $quote_info['qt_company_name']  = $cust_data['cust_name'];
        $quote_info['qt_com_mno']       = $cust_data['cust_mobile'];
        $quote_info['qt_com_gstno']	= $cust_data['cust_gst'];
        $quote_info['qt_com_addr']	= $cust_data['c_add_address'];
        $quote_info['qt_add_country']	= $cust_data['c_add_country'];
        $quote_info['qt_add_state']	= $cust_data['c_add_state'];
        $quote_info['qt_add_city']	= $cust_data['c_add_city'];

        $cust_stateid = $cust_data['c_add_state'];
    }

        // Quotation Creation 
    $quote_info['quotation_date']	= date('Y-m-d');
    $quote_info['quotation_no']	= load_quotation_no($dbcon);
    $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=3 and company_id=".$_SESSION['company_id']);

    $show_user_ids                      = show_user_ids($dbcon,$_SESSION['user_id']);

    $quote_info['currency_id']          = $data['currency_id'];
    $quote_info['currency_rate']        = $data['currency_rate'];

    $quote_info['cust_id']		        = $data['cust_id'];
    $quote_info['c_con_id']		        = $data['c_con_id'];
    $quote_info['inquiry_id']           = $inquiryid;
    $quote_info['quot_subject']         = $data['inquiry_no'];
    $quote_info['quot_type']            = 0;
    $quote_info['quotation_valid_date'] = date('Y-m-d', strtotime('+'.$quot_validity.' days'));
    $quote_info['quotation_ref']	    = 'Quotation';
    $quote_info['quot_remark']          = 'Auto Created Quotation for :'.$data['inquiry_no'];
    
    if($data['currency_id'] == $_SESSION['currency_id']){
        $quote_info['quot_type']           = 0;
        $quote_info['g_total']             = $data['g_total'];
        $quote_info['g_total_conv']        = $data['g_total']*$data['currency_rate'];
    }else{
        $quote_info['quot_type']           = 1;
        $quote_info['g_total']             = $data['g_total'];
        $quote_info['g_total_conv']        = $data['g_total']*$data['currency_rate'];
    }
    $quote_info['gst_type']              = $data['gst_type'];;
    $quote_info['quot_address']         = $cust_data['c_add_address'];
    $quote_info['quot_header']          = $companySettings['quotation_print_content'];
    $quote_info['quot_footer']          = $companySettings['quotation_footer_content'];
    $quote_info['create_date']          = date('Y-m-d H:i:s');
    $quote_info['cdate']		        = date("Y-m-d H:i:s");
    $quote_info['show_user_ids']        = $show_user_ids;
    $quote_info['user_id']              = $_SESSION['user_id'];
    $quote_info['quot_won_user_id']		= $_SESSION['user_id'];
    $quote_info['company_id']           = $_SESSION['company_id'];
    $quotation_id = add_record('tbl_quotation', $quote_info, $dbcon,$data['branch_id']);

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        QUOTATION_SLUG_FINAL_APPROVE
    ]);
    $final_btn_per= in_array(QUOTATION_SLUG_FINAL_APPROVE, $bulkAccessArray);
    if($final_btn_per || $companyConfiguration['automatic_approval_quotation']==1){
        $infoaprvqt['approve_status']	= 1;
        $updateid=update_record('tbl_quotation', $infoaprvqt,"quotation_id=".$quotation_id , $dbcon, $branch_id);

        // add approve log 
        $info1['approve_remark']	= 'Auto Approved by Admin';
        $info1['approve_status']	= 1;
        $info1['quotation_id']          = $quotation_id;
        $info1['user_id']		= $_SESSION['user_id'];
        $info1['company_id']            = $_SESSION['company_id'];

        $inserid=add_record("tbl_quot_aprv_log", $info1, $dbcon, $branch_id);
    }
    $dbcon->query("update tbl_quotation set start_quotation_id=".$quotation_id." where quotation_id=".$quotation_id);
    // die;
// $quotation_id = '38';
    if($quotation_id || $product_data){
        foreach ($product_data as $product) {
            $company_state = get_company_data($dbcon,$_SESSION['company_id']);
            //$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
            

            $cgst_tax_rate=0;$cgst_tax_rate_conv=0;
            $sgst_tax_rate=0;$sgst_tax_rate_conv=0;
            $igst_tax_rate=0;$igst_tax_rate_conv=0;

            if($data['gst_type']==3){
            $sale_gst1['tax_gst']=0.1;
            $sale_gst1['tax_cat_id']=0;
        }else if($data['gst_type']==4){
            $sale_gst1['tax_gst']=0;
            $sale_gst1['tax_cat_id']=0;
        }else if($data['gst_type']==5){
            $sale_gst1['tax_gst']=5;
            $sale_gst1['tax_cat_id']=0;
        }else if($data['gst_type']==6){
            $sale_gst1['tax_gst']=12;
            $sale_gst1['tax_cat_id']=0;
        }else if($data['gst_type']==7){
            $sale_gst1['tax_gst']=18;
            $sale_gst1['tax_cat_id']=0;
        }else if($data['gst_type']==8){
            $sale_gst1['tax_gst']=24;
            $sale_gst1['tax_cat_id']=0;
        }else{
            $sale_gst1 = get_tax_cat_by_hsn($dbcon,$product['product_hsn_code']); 
        }

            

            if(($company_state['stateid'] == $cust_stateid)){
                $gst = $sale_gst1['tax_gst']/2;
                $cgst_tax_per = $gst;
                $cgst_tax_rate = ($gst*$product['product_amount'])/100;
                $cgst_tax_rate_conv = ($gst*$product['product_amount_conv'])/100;
                $sgst_tax_per = $gst;
                $sgst_tax_rate = ($gst*$product['product_amount'])/100;
                $sgst_tax_rate_conv = ($gst*$product['product_amount_conv'])/100;
            }else{
                $igst_tax_per = $sale_gst1['tax_gst'];
                $igst_tax_rate = ($sale_gst1['tax_gst']*$product['product_amount'])/100;
                $igst_tax_rate_conv = ($data['currency_rate'] *$sale_gst1['tax_gst']*$product['product_amount_conv'])/100;
            }
            
            
            $quote_trn['quotation_id']      = $quotation_id;
            $quote_trn['inquiry_type']      = $data['inquiry_type'];
            $quote_trn['project_wise']      = 0;
            $quote_trn['product_id']        = $product['product_id'];
            $quote_trn['product_desc']      = $product['description'];
            $quote_trn['product_spec']      = $product['spec'];
            $quote_trn['level_id']          = $product['level_id'];
            $quote_trn['unitid']            = $product['unitid'];
            $quote_trn['rate_unit']         = $product['rate_unit'];
            $quote_trn['conv_unit_id']      = $product['conv_unit_id'];
            $quote_trn['product_qty']       = $product['product_qty'];
            $quote_trn['product_conv_qty']  = $product['product_conv_qty'];
            $quote_trn['discount_per']      = $product['discount_per'];
            $quote_trn['formulaid']         = $product['formulaid'];
            $quote_trn['currency_id']       = $data['currency_id'];
            $quote_trn['currency_rate']     = $data['currency_rate'];
            // $info=get_product_common_tax($dbcon,$product['product_amount'],$product['formulaid']);
            // $quote_trn=array_merge($quote_trn,$info);
            $quote_trn['cgst_tax_per']      = isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
            $quote_trn['sgst_tax_per']      = isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
            $quote_trn['igst_tax_per']      = isset($igst_tax_per) ? $igst_tax_per : 0 ;

            
            
            
            $quote_trn['product_rate']      = $product['product_rate'];
            //$quote_trn['product_discount']  = $product['product_discount'];
            $quote_trn['product_amount']    = $product['product_amount'];
            $quote_trn['cgst_tax_rate']     = isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
            $quote_trn['sgst_tax_rate']     = isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
            $quote_trn['igst_tax_rate']     = isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
            $quote_trn['product_total']     = $product['product_amount'];

            $quote_trn['product_rate_conv']      = $product['product_rate_conv'];
            //$quote_trn['product_discount_conv']  = $product['product_discount'];
            $quote_trn['product_amount_conv']    = $product['product_amount_conv'];
            $quote_trn['cgst_tax_rate_conv']     = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
            $quote_trn['sgst_tax_rate_conv']     = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
            $quote_trn['igst_tax_rate_conv']     = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
            $quote_trn['product_total_conv']     = $product['product_amount_conv'];

           /* }else{
                $quote_trn['product_rate']      = $product['product_rate']*$data['currency_rate'];
                $quote_trn['product_discount']  = $product['product_discount']*$data['currency_rate'];
                $quote_trn['product_amount']    = $product['product_amount']*$data['currency_rate'];
                $quote_trn['cgst_tax_rate']     = isset($cgst_tax_rate_conv) ? $cgst_tax_rate_conv : 0 ;
                $quote_trn['sgst_tax_rate']     = isset($sgst_tax_rate_conv) ? $sgst_tax_rate_conv : 0 ;
                $quote_trn['igst_tax_rate']     = isset($igst_tax_rate_conv) ? $igst_tax_rate_conv : 0 ;
                $quote_trn['product_total']     = $product['product_amount']*$data['currency_rate'];

                $quote_trn['product_rate_conv']      = $product['product_rate'];
                $quote_trn['product_discount_conv']  = $product['product_discount'];
                $quote_trn['product_amount_conv']    = $product['product_amount'];
                $quote_trn['cgst_tax_rate_conv']     = isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
                $quote_trn['sgst_tax_rate_conv']     = isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
                $quote_trn['igst_tax_rate_conv']     = isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
                $quote_trn['product_total']          = $product['product_amount'];
            }*/
            
            $quote_trn['product_tax_cat']   = $sale_gst['tax_cat_id'];
            $quote_trn['user_id']           = $_SESSION['user_id'];
            $quote_trn['company_id']        = $_SESSION['company_id'];
            
            $quote_trn_id = add_record('tbl_quotation_trn', $quote_trn, $dbcon);

            if(($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
                $cl_id = get_ledger_by_name($dbcon,'CGST');
                $insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$quote_trn_id,"tbl_quotation_trn",$product['product_id'],3,$quote_trn_id,$data['branch_id'],$data['currency_id'],$data['currency_rate'],$cgst_tax_rate_conv);
            }
            if(($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
                $cl_id = get_ledger_by_name($dbcon,'SGST');
                $insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$quote_trn_id,"tbl_quotation_trn",$product['product_id'],3,$quote_trn_id,$data['branch_id'],$data['currency_id'],$data['currency_rate'],$sgst_tax_rate_conv);
            }
            if(($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
                $cl_id = get_ledger_by_name($dbcon,'IGST');
                $insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$quote_trn_id,"tbl_quotation_trn",$product['product_id'],3,$quote_trn_id,$data['branch_id'],$data['currency_id'],$data['currency_rate'],$igst_tax_rate_conv);
            }

            // check for the addiotional tax on product Start -- dhaval
            $pro_amt = $product['product_amount_conv'];
            $count_add_tax=get_check_addition_tax($dbcon,$sale_gst['tax_cat_id'],$product['product_amount'],$quote_trn_id,$product['product_id'],$quote_trn_id,$data['branch_id'],'tbl_quotation_trn',$data['currency_id'],$data['currency_rate'],$pro_amt);
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
    $getspecialConfiguration=getspecialConfiguration($dbcon);
    $where ='';
    if($getspecialConfiguration['durva_permission']==1){
        $where = " and pid=0";
    }
    if($inquiryId){
        $query="select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,trn.product_desc as description,trn.product_spec as spec,cat.cat_name,unit.unit_name as rat_unit , hsn.hsn_code as product_hsn_code, buni.unit_name as base_unit, cuni.unit_name as conv_unit, pcat.cat_name as pcat_name from tbl_inquiry_trn as trn 
        left join product_mst as pro on pro.product_id  = trn.product_id 
        left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
        left join tbl_category as cat on cat.cat_id=trn.cat_id
        left join tbl_category_reciclare as pcat on pcat.rcat_id = trn.rcat_id
        left join unit_mst as unit on unit.unitid=trn.rate_unit
        left join unit_mst as buni on buni.unitid = trn.unitid
        left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
        where trn.inquiry_trn_status=0 ".$where." and trn.inquiry_id=".$inquiryId;
        /* END */ 
    }
    else{
        $query="select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,trn.product_desc as description,trn.product_spec as spec,cat.cat_name,unit.unit_name as rat_unit , hsn.hsn_code as product_hsn_code, buni.unit_name as base_unit, cuni.unit_name as conv_unit, pcat.cat_name as pcat_name from tbl_inquiry_trn as trn 
        left join product_mst as pro on pro.product_id  = trn.product_id 
        left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
        left join tbl_category as cat on cat.cat_id=trn.cat_id
        left join tbl_category_reciclare as pcat on pcat.rcat_id = trn.rcat_id
        left join unit_mst as unit on unit.unitid=trn.rate_unit
        left join unit_mst as buni on buni.unitid = trn.unitid
        left join unit_mst as cuni on cuni.unitid = trn.conv_unit_id
        where trn.inquiry_trn_status=3  ".$where." and trn.user_id=".$_SESSION['user_id'];
        /* END */ 
    }
    $result = brp_mysqli_query($dbcon,$query);
    $products = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);
    return $products;
        // return $query;
}
function check_last_month_forecast($dbcon,$cust_id,$type)
{

    $financial_year=get_financial_year_new($dbcon); 

    $start_date = date("m",strtotime($financial_year['financial_start_date']));
    $end_date = date("m",strtotime($financial_year['financial_end_date']));
    $current_date = date("m");

    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $current_year = date("Y");

    $q = $dbcon->query("select IFNULL(sum(forecast_amount_pr),0)  as target_sum from tbl_cust_forecast_pr where forecast_cust_id='$cust_id' and forecast_type='$type' and forecast_month between '$start_date' and '$current_date' and forecast_year between '$start_year' and '$current_year' AND isdelete='0'");  
    // $q=$dbcon->query("select forecast_month from tbl_cust_forecast_pr where forecast_cust_id='$cust_id' and forecast_month='$month'");
    $row=brp_mysqli_fetch_assoc($q);
    return $row['target_sum'];
}
function get_target_of_user($dbcon,$user,$from,$to)
{
   $q = "select IFNULL(sum(forecast_amount_pr),0) as total from tbl_cust_forecast_pr where user_id='$user' and forecast_type='1' and isdelete='0' and forecast_month between $from and $to";
   $query = $dbcon->query($q);
   $row = brp_mysqli_fetch_assoc($query);
   return $row['total'];
   //return $from;
}
function get_achievement_of_user($dbcon,$user,$from,$to)
{
    $q = "select c.*,cust.ledger_id from tbl_cust_forecast_pr as c 
    left join tbl_customer as cust on c.forecast_cust_id=cust.cust_id
    where c.user_id='$user' and c.forecast_type='1' and c.isdelete='0' and c.forecast_month between $from and $to";
    $query = $dbcon->query($q);
    $sum=0;
    while($row = brp_mysqli_fetch_assoc($query))
    {
        $r = "select IFNULL(sum(g_total),0) as total from tbl_invoice where cust_id='$row[ledger_id]' and invoice_status='0' and MONTH(invoice_date) between $from and $to";
        $query1 = $dbcon->query($r);
        $row1 = brp_mysqli_fetch_assoc($query1);
        $sum+=$row1['total'];
    }
    return $sum;
}

function get_target_of_customer($dbcon,$cust,$from,$to)
{


   $q = "select IFNULL(sum(forecast_amount_pr),0) as total from tbl_cust_forecast_pr where forecast_cust_id='$cust' and forecast_type='1' and isdelete='0' and cdate between '$from' and '$to'";
   $query = $dbcon->query($q);
   $row = brp_mysqli_fetch_assoc($query);
   return $row['total'];
   //return $from;
}

function get_achievement_of_customer($dbcon,$cust,$from,$to)
{
    $ledger_id = get_id_detail($dbcon,'tbl_customer','cust_id',$cust,'ledger_id');

    $q = "select IFNULL(sum(g_total),0) as total from tbl_invoice where invoice_date between '$from' and '$to' and cust_id='$ledger_id'";

    $query = $dbcon->query($q);

    $row = brp_mysqli_fetch_assoc($query);

    return $row['total'];
}
function get_report_to_user($dbcon,$user_id)
{
    $user_arr=array();
    unset($user_arr['1']);
    $sel_report_to = $dbcon->query("select * from users where report_to_user_id='$user_id'");
    if(brp_mysqli_num_rows($sel_report_to)>0)
    {
        while($r_report_to = brp_mysqli_fetch_assoc($sel_report_to))
        {
            if($r_report_to['user_id']!=1)
            {
                $user_arr[]=array_push($user_arr,$r_report_to['user_id']);
                
                get_report_to_user($dbcon,$r_report_to['user_id']);
            }
        }
    }

    return $user_arr;
}
function check_current_month_forecast($dbcon,$cust_id,$type,$month)
{

    $financial_year=get_financial_year_new($dbcon); 

    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $current_year = date("Y");

    $q = $dbcon->query("select IFNULL(sum(forecast_amount_pr),0)  as target_sum from tbl_cust_forecast_pr where forecast_cust_id='$cust_id' and forecast_type='$type' and forecast_month = '$month' and forecast_year between '$start_year' and '$current_year' AND isdelete='0' AND company_id = ".$_SESSION['company_id']);  
    // $q=$dbcon->query("select forecast_month from tbl_cust_forecast_pr where forecast_cust_id='$cust_id' and forecast_month='$month'");
    $row=brp_mysqli_fetch_assoc($q);
    return $row['target_sum'];
    // return "select IFNULL(sum(forecast_amount_pr),0)  as target_sum from tbl_cust_forecast_pr where forecast_cust_id='$cust_id' and forecast_type='$type' and forecast_month = '$month' and forecast_year between '$start_year' and '$current_year' AND isdelete='0'";
}

function get_inquiry_no($dbcon, $inquiry_id){
    $query = "select * from tbl_inquiry WHERE inquiry_status=0";
    $result = $dbcon->query($query);

    $str = "";$sel="";
    while($row = brp_mysqli_fetch_array($result)){
        if($row['inquiry_id']==$inquiry_id){
            $sel = "selected='selected'";
        }
        $str.= '<option '.$sel.' value="'.$row['inquiry_id'].'">'.$row['inquiry_no'].'</option>';
    }
    return $str;
}

function get_quotation_no($dbcon, $quotation_id){
    $query = "select * from tbl_quotation WHERE quotation_status=0 and revise_status=0";
    $result = $dbcon->query($query);

    $str = "";$sel="";
    while($row = brp_mysqli_fetch_array($result)){
        if($row['quotation_id']==$quotation_id){
            $sel = "selected='selected'";
        }
        $str.= '<option '.$sel.' value="'.$row['quotation_id'].'">'.$row['quotation_no'].'</option>';
    }
    return $str;
}

function get_deliverydate_carry_forward($dbcon, $inser_id, $inser_field, $insert_tb, $ref_trn_id, $refer_tb, $refer_st_field, $refer_field, $branch_id){
    
    $query = 'select * from '.$refer_tb.' where '.$refer_st_field.' = 0 and '.$refer_field.'='.$ref_trn_id;
    $result = $dbcon->query($query);
    while($row = brp_mysqli_fetch_array($result)){
        $info[$inser_field]             = $inser_id;
        $info['delivery_date']          = date('Y-m-d',strtotime($row['delivery_date']));
        $info['product_qty']            = $row['product_qty'];
        $info['unit_id']                = $row['unit_id'];

        $info['user_id']                = $_SESSION['user_id'];
        $info['cdate']                  = date("Y-m-d h:i:s");
        $info['company_id']             = $_SESSION['company_id'];

        $inserid_k=add_record($insert_tb,$info,$dbcon,$branch_id);
    }
}

function get_stock_reserve_allocate_so($dbcon,$inserid){
    
    $info_wo_temp['status']  = 0;
    $updatetrnid=update_record('work_order_reserve_temp',$info_wo_temp,"sales_ordertrn_id=".$inserid , $dbcon);
    
    $info_reserve['stock_status'] = 2;

    $updatetrnid=update_record('tbl_reserve_stock',$info_reserve,"sales_order_trn_id=".$inserid , $dbcon);
    
    $info_product['sales_order_production_status'] = 2;
    
    $updatetrnid=update_record('tbl_sales_order_production_trn',$info_product,"sales_ordertrn_id=".$inserid , $dbcon);

    $query_rstock="select * from work_order_reserve_temp as i
    where i.status = 0 and i.sales_ordertrn_id =".$inserid;
    $result_rstock=$dbcon->query($query_rstock);
    while($row_rstock=brp_mysqli_fetch_assoc($result_rstock)){
        $reserve_qty=$row_rstock['reserve_qty'];
        $batch_where="";
        if(!empty($row_rstock['stock_id'])){
            $batch_where=" and i.stock_id=".$row_rstock['stock_id'];
        }
        $query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i  
        where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) ".$batch_where." and i.product_id=".$row_rstock['product_id']." and i.godown_id=".$row_rstock['godown_id'];
        $result_dstock=$dbcon->query($query_dstock);
        while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
            if($row_dstock['convert_unit']==$row_rstock['unit_id']){
                $pending_stock=$row_dstock['pending_conv_stock'];
            }else{
                $pending_stock=$row_dstock['pending_base_stock'];   
            }
            if($reserve_qty>0){
                if($pending_stock>=$reserve_qty){
                    $rqty=$reserve_qty;
                    $reserve_qty=$reserve_qty-$reserve_qty;
                }else{
                    $rqty=$pending_stock;
                    $reserve_qty=$reserve_qty-$pending_stock;
                }

                $que="select * from product_mst as ta where product_id=".$row_rstock['product_id'];
                $rs_di=$dbcon->query($que);
                $re=brp_mysqli_fetch_assoc($rs_di);


                if($re['product_conv_unit']==$row_rstock['unit_id']){
                    $type="base_unit";
                    $con_stock=$rqty;
                    $base_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
                }else{
                    $type="conv_unit";
                    $base_stock=$rqty;
                    $con_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
                }

                
                $info_rese['reserve_date']      = date('Y-m-d');
                $info_rese['product_id']        = $row_rstock['product_id'];
                $info_rese['godown_id']         = $row_dstock['godown_id'];
                $info_rese['base_unit']         = $re['product_base_unit'];
                $info_rese['base_stock']        = $base_stock;
                $info_rese['convert_unit']      = $re['product_conv_unit'];
                $info_rese['convert_stock']     = $con_stock;
                $info_rese['stock_flage']       = "1";
                $info_rese['request_id']        = $row_rstock['rp_id'];
                $info_rese['ref_name']          = "wo_allocate";
                $info_rese['ref_id']            = $row_rstock['work_order_reserve_temp_id'];
                $info_rese['sales_order_trn_id']= $row_rstock['sales_ordertrn_id'];
                $info_rese['stock_id']          = $row_dstock['stock_id'];

                $info_rese['cdate']                 = date("Y-m-d H:i:s");
                $info_rese['user_id']               = $_SESSION['user_id'];
                $info_rese['company_id']            = $_SESSION['company_id'];      
                                    
                $reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row_dstock['branch_id']);
                
                $wo_res_temp_info['status'] = 3;
                            
                $updatetrnid=update_record('work_order_reserve_temp',$wo_res_temp_info,"work_order_reserve_temp_id=".$row_rstock['work_order_reserve_temp_id'] , $dbcon);
                
                if($row_dstock['base_unit']==$re['product_base_unit']){
                    $used_base_stock=$row_dstock['used_base_stock']+$base_stock;
                    $used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
                }else{
                    $used_base_stock=$row_dstock['used_convert_stock']+$con_stock;
                    $used_convert_stock=$row_dstock['used_base_stock']+$base_stock;
                }

                $info_stock['used_base_stock']      = $used_base_stock;
                $info_stock['used_convert_stock']   = $used_convert_stock;
                
                $updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);

                $info_e['sales_ordertrn_id']    =$row_rstock['sales_ordertrn_id'];
                $info_e['product_id']           =$row_rstock['product_id'];
                $info_e['product_qty']          =$info_rese['base_stock'];
                $info_e['godown_id']            =$info_rese['godown_id'];
                $info_e['unit_id']              =$info_rese['base_unit'];
                $info_e['allocate_qty']         =$info_rese['base_stock'];
                $info_e['remaning_invoice_qty'] =$info_rese['base_stock'];
                
                $info_e['cdate']                =date("Y-m-d");
                $info_e['company_id']           =$_SESSION['company_id'];
                $info_e['user_id']              =$_SESSION['user_id'];
                $inserinvoiceidexp=add_record('tbl_sales_order_production_trn', $info_e, $dbcon,$row_dstock['branch_id']);
                update_salesorder_qty_and_status_so_alloc($dbcon,$row_rstock['sales_ordertrn_id']);
            }
        }
    }
}

function update_salesorder_qty_and_status_so_alloc($dbcon,$sales_ordertrn_id){
     $que="select product_qty from tbl_sales_ordertrn where sales_ordertrn_id=".$sales_ordertrn_id;
    $rs_di=$dbcon->query($que);
    $re=brp_mysqli_fetch_assoc($rs_di);

    $que1="select sum(product_qty) as product_qty from tbl_sales_order_production_trn where sales_order_production_status = 0 and sales_ordertrn_id=".$sales_ordertrn_id ." group by sales_ordertrn_id";
    $rs_di1=$dbcon->query($que1);
    $re1=brp_mysqli_fetch_assoc($rs_di1);
    $so_qty = (float)$re['product_qty'];
    $done_qty = (float)$re1['product_qty'];
    
    if($done_qty >= $so_qty){
        
        $info['bom_status'] =  1 ;
        $updatetrnid=update_record('tbl_sales_ordertrn',$info,"sales_ordertrn_id=".$sales_ordertrn_id, $dbcon); 
    }    
}

function direct_salesorder_reserve_stock($dbcon,$sales_ordertrn_id){
    $query = "select * from tbl_sales_ordertrn where sales_ordertrn_id=".$sales_ordertrn_id;
    $result = $dbcon->query($query);
    while($row = brp_mysqli_fetch_array($result)){
        $query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i  
        where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and i.product_id=".$row_rstock['product_id'];
        $result_dstock=$dbcon->query($query_dstock);
        while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){

            if($row_dstock['convert_unit']==$row_rstock['unit_id']){
                $pending_stock=$row_dstock['pending_conv_stock'];
            }else{
                $pending_stock=$row_dstock['pending_base_stock'];   
            }

            if($reserve_qty>0){
                if($pending_stock>=$reserve_qty){
                    $rqty=$reserve_qty;
                    $reserve_qty=$reserve_qty-$reserve_qty;
                }else{
                    $rqty=$pending_stock;
                    $reserve_qty=$reserve_qty-$pending_stock;
                }

                $que="select * from product_mst as ta where product_id=".$row_rstock['product_id'];
                $rs_di=$dbcon->query($que);
                $re=brp_mysqli_fetch_assoc($rs_di);


                if($re['product_conv_unit']==$row_rstock['unit_id']){
                    $type="base_unit";
                    $con_stock=$rqty;
                    $base_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
                }else{
                    $type="conv_unit";
                    $base_stock=$rqty;
                    $con_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
                }

            
                $info_rese['reserve_date']      = date('Y-m-d');
                $info_rese['product_id']        = $row['product_id'];
                $info_rese['godown_id']         = $row_dstock['godown_id'];
                $info_rese['base_unit']         = $row['unit_id'];
                $info_rese['base_stock']        = $base_stock;
                $info_rese['convert_unit']      = $row['conv_unit_id'];
                $info_rese['convert_stock']     = $con_stock;
                $info_rese['stock_flage']       = "1";
                $info_rese['request_id']        = 0;
                $info_rese['ref_name']          = "so_allocate";
                $info_rese['ref_id']            = $sales_ordertrn_id;
                $info_rese['sales_order_trn_id']= $sales_ordertrn_id;
                $info_rese['stock_id']          = $row_dstock['stock_id'];

                $info_rese['cdate']                 = date("Y-m-d H:i:s");
                $info_rese['user_id']               = $_SESSION['user_id'];
                $info_rese['company_id']            = $_SESSION['company_id'];      
                                    
                $reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row_dstock['branch_id']);
                
                if($row_dstock['base_unit']==$row['unit_id']){
                    $used_base_stock=$row_dstock['used_base_stock']+$base_stock;
                    $used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
                }else{
                    $used_base_stock=$row_dstock['used_convert_stock']+$con_stock;
                    $used_convert_stock=$row_dstock['used_base_stock']+$base_stock;
                }

                $info_stock['used_base_stock']      = $used_base_stock;
                $info_stock['used_convert_stock']   = $used_convert_stock;
                
                $updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);

                $info_e['sales_ordertrn_id']    = $row_rstock['sales_ordertrn_id'];
                $info_e['product_id']           = $row_rstock['product_id'];
                $info_e['product_qty']          = $info_rese['base_stock'];
                $info_e['godown_id']            = $info_rese['godown_id'];
                $info_e['unit_id']              = $info_rese['base_unit'];
                $info_e['allocate_qty']         = $info_rese['base_stock'];
                $info_e['remaning_invoice_qty'] = $info_rese['base_stock'];
                
                $info_e['cdate']                = date("Y-m-d");
                $info_e['company_id']           = $_SESSION['company_id'];
                $info_e['user_id']              = $_SESSION['user_id'];
                $inserinvoiceidexp=add_record('tbl_sales_order_production_trn', $info_e, $dbcon,$row_dstock['branch_id']);
                update_salesorder_qty_and_status_so_alloc($dbcon,$row_rstock['sales_ordertrn_id']);
            }
        }
    }
}

function special_durva_data_add($dbcon,$product_id,$gst_type,$currency_rate,$branch_id,$inquiry_type,$currency_id,$cust_stateid,$cust_id,$sales_order_id,$user_id,$with_out_stock_invoice,$product_attr,$edit_id,$inserid){
    $companyConfiguration=getCompanyConfiguration($dbcon);
    $company_state = get_company_data($dbcon,$_SESSION['company_id']);
    $custLedgerDetails = get_cust_data_arr($dbcon,$cust_id);
    $inq_qry="select tiat.*, pm.product_base_unit, pm.product_conv_unit, pm.product_spec, pm.product_spec_id, pm.product_hsn, hsn.hsn_code from tbl_so_access_trn as tiat 
            left join product_mst as pm on pm.product_id=tiat.product_id 
            left join mst_hsn_code as hsn on hsn.hsn_id = pm.product_hsn
            where tiat.inq_access_status=3 and tiat.pid=".$product_id." and tiat.company_id=".$_SESSION['company_id']." and tiat.user_id=".$_SESSION['user_id']."";
    $inq_qry_rs=$dbcon->query($inq_qry);

    while($inq_rel=brp_mysqli_fetch_array($inq_qry_rs))
    {
        $product_detail1 = get_product_detail($dbcon,$inq_rel['product_id']);
        if($gst_type==3){
            $sale_gst1['tax_gst']=0.1;
            $sale_gst1['tax_cat_id']=0;
        }else if($gst_type==4){
            $sale_gst1['tax_gst']=0;
            $sale_gst1['tax_cat_id']=0;
        }else if($gst_type==5){
            $sale_gst1['tax_gst']=5;
            $sale_gst1['tax_cat_id']=0;
        }else if($gst_type==6){
            $sale_gst1['tax_gst']=12;
            $sale_gst1['tax_cat_id']=0;
        }else if($gst_type==7){
            $sale_gst1['tax_gst']=18;
            $sale_gst1['tax_cat_id']=0;
        }else if($gst_type==8){
            $sale_gst1['tax_gst']=24;
            $sale_gst1['tax_cat_id']=0;
        }else{
            $sale_gst1 = get_tax_cat_by_hsn($dbcon,trim($inq_rel['hsn_code'])); 
        }


        $cgst_tax_rate1=0;$cgst_tax_rate_conv1=0;
        $sgst_tax_rate1=0;$sgst_tax_rate_conv1=0;
        $igst_tax_rate1=0;$igst_tax_rate_conv1=0;
        if($product_detail1['product_gst'] == 'including'){
            $prorate = $inq_rel['acce_rate'] * 100 /(100 + $sale_gst1['tax_gst']);
        }else{
            $prorate = $inq_rel['acce_rate']; 
        }
        if(($company_state['stateid'] == $cust_stateid) && ($custLedgerDetails['enable_sez'] == 0)){
            $gst = $sale_gst1['tax_gst']/2;
            $cgst_tax_per1 = $gst;
            $cgst_tax_rate1 = ($gst*$inq_rel['acc_amount'])/100;
            $cgst_tax_rate_conv1 = ($currency_rate *$gst*$inq_rel['acc_amount'])/100;
            $sgst_tax_per1 = $gst;
            $sgst_tax_rate1 = ($gst*$inq_rel['acc_amount'])/100;
            $sgst_tax_rate_conv1 = ($currency_rate *$gst*$inq_rel['acc_amount'])/100;
        }else{
            $igst_tax_per1 = $sale_gst1['tax_gst'];
            $igst_tax_rate1 = ($sale_gst1['tax_gst']*$inq_rel['acc_amount'])/100;
            $igst_tax_rate_conv1 = ($currency_rate *$sale_gst1['tax_gst']*$inq_rel['acc_amount'])/100;
        }

        
       
        //$info12['quotation_id']  = $POST['quotation_id'];
        //$info12['quot_trn_id']   = $POST['quot_trn_id'];
        $info12['inquiry_type'] = $inquiry_type;
        $info12['product_id']   = $inq_rel['product_id'];
        $info12['description']  = $inq_rel['product_desc'];
        $info12['product_disc'] = $inq_rel['product_desc'];
        $info12['pid']          = $inserid;
        //$info12['product_spec']   = $_POST['product_spec'];
        $info12['product_hsn_code'] = $inq_rel['hsn_code'];
        /*if($getspecialConfiguration['elcon_permission'] ==1){
            $info12['product_item_code']    = $POST['product_item_code'];
        }*/
        /*if($getspecialConfiguration['vipul_copper_permission'] ==1){
            $info12['product_category_id']  = $POST['product_category_id'];
            $info12['product_length']   = $POST['product_length'];
            $info12['product_pices']        = $POST['product_pices'];
        }*/

        if($companyConfiguration['sales_wise_branch_planning'] == 1){
            $info12['production_branch_id'] = 0;
        }else{
            $info12['production_branch_id'] = $branch_id;
        }

        $type="base_unit";
        $ret_qty=convert_stock($dbcon,$inq_rel['qty'],$inq_rel['product_id'],$type);

        $info12['product_qty']          = $inq_rel['qty'];
        $info12['remaning_invoice_qty'] = $inq_rel['qty'];
        $info12['product_conv_qty']     = $ret_qty;
        $info12['unit_id']              = $inq_rel['product_base_unit'];
        $info12['conv_unit_id']         = $inq_rel['product_conv_unit'];
        $info12['rate_unit']            = $inq_rel['product_base_unit'];
        $info12['delivery_type']        = 'so_wise';

        $info12['cgst_tax_per']     = isset($cgst_tax_per1) ? $cgst_tax_per1 : 0 ;
        $info12['sgst_tax_per']     = isset($sgst_tax_per1) ? $sgst_tax_per1 : 0 ;
        $info12['igst_tax_per']     = isset($igst_tax_per1) ? $igst_tax_per1 : 0 ;

        if($currency_id==$company_state['currency_id']){
            $info12['product_rate']         = $prorate;
            //$info12['product_discount']   = $POST['product_discount'];
            $info12['product_amount']       = $inq_rel['acc_amount'];
            $info12['cgst_tax_rate']        = isset($cgst_tax_rate1) ? $cgst_tax_rate1 : 0 ;
            $info12['sgst_tax_rate']        = isset($sgst_tax_rate1) ? $sgst_tax_rate : 0 ;
            $info12['igst_tax_rate']        = isset($igst_tax_rate1) ? $igst_tax_rate : 0 ;
            $info12['total']                = $inq_rel['acc_amount']+$cgst_tax_rate1+$sgst_tax_rate1+$igst_tax_rate1;
            
            $info12['product_rate_conv']        = $prorate*$currency_rate;
            //$info12['product_discount_conv']  = $POST['product_discount']*$currency_rate;
            $info12['product_amount_conv']  = $inq_rel['acc_amount']*$currency_rate;
            $info12['cgst_tax_rate_conv']   = isset($cgst_tax_rate_conv1) ? $cgst_tax_rate_conv1 : 0 ;
            $info12['sgst_tax_rate_conv']   = isset($sgst_tax_rate_conv1) ? $sgst_tax_rate_conv1 : 0 ;
            $info12['igst_tax_rate_conv']   = isset($igst_tax_rate_conv1) ? $igst_tax_rate_conv1 : 0 ;
            $info12['total_conv']           = $info12['product_amount_conv']+$cgst_tax_rate_conv1+$sgst_tax_rate_conv1+$igst_tax_rate_conv1;
        }else{
            $info12['product_rate']     = $prorate*$currency_rate;
            //$info12['product_discount']   = $POST['product_discount']*$currency_rate;
            $info12['product_amount']   = $inq_rel['acc_amount']*$currency_rate;
            $info12['cgst_tax_rate']        = isset($cgst_tax_rate_conv1) ? $cgst_tax_rate_conv1 : 0 ;
            $info12['sgst_tax_rate']        = isset($sgst_tax_rate_conv1) ? $sgst_tax_rate_conv1 : 0 ;
            $info12['igst_tax_rate']        = isset($igst_tax_rate_conv1) ? $igst_tax_rate_conv1 : 0 ;
            $info12['total']                = $info12['product_amount']+$cgst_tax_rate_conv1+$sgst_tax_rate_conv1+$igst_tax_rate_conv1; 

            $info12['product_rate_conv']        = $prorate;
            //$info12['product_discount_conv']  = $POST['product_discount'];
            $info12['product_amount_conv']  = $inq_rel['acc_amount'];
            $info12['cgst_tax_rate_conv']   = isset($cgst_tax_rate1) ? $cgst_tax_rate1 : 0 ;
            $info12['sgst_tax_rate_conv']   = isset($sgst_tax_rate1) ? $sgst_tax_rate1 : 0 ;
            $info12['igst_tax_rate_conv']   = isset($igst_tax_rate1) ? $igst_tax_rate1 : 0 ;
            $info12['total_conv']           = $inq_rel['acc_amount']+$cgst_tax_rate1+$sgst_tax_rate1+$igst_tax_rate1;
        }

        $info12['product_tax_cat']  = $sale_gst1['tax_cat_id'];
        if($companyConfiguration['trading_stock']!=0){
            $info12['bom_status']       = 1;
        }

        //$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
       //   $info12=array_merge($info12,$info);
        //var_dump($info12);
        $table='tbl_sales_ordertrn';$tableid='sales_ordertrn_id';
        if(!empty($sales_order_id))
        {
            $info12['user_id']  = $user_id;
            $info12['sales_order_id']= $sales_order_id;
            $table='tbl_sales_ordertrn';
            $tableid='sales_ordertrn_id';
            $info12['with_out_stock_invoice']= $with_out_stock_invoice;
        }
        else
        {
            $info12['user_id']  = $user_id;
            $info12['sales_ordertrn_status']= 3;
        }

        if(isset($product_attr) && strtolower($product_attr)=='projectwise'){
            $info12['project_wise']= 1;
        }
        $inserid_acc=add_record($table, $info12, $dbcon, $branch_id);   
        
        if(($cgst_tax_per1 != 0) && ($cgst_tax_rate1 != 0) ){
            $cl_id = get_ledger_by_name($dbcon,'CGST');
            $insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per1,$cgst_tax_rate1,$inserid_acc,"tbl_sales_ordertrn",$inq_rel['product_id'],3,$edit_id,$branch_id,$currency_id,$currency_rate,$cgst_tax_rate_conv1);
        }
        if(($sgst_tax_per1 != 0) && ($sgst_tax_rate1 != 0) ){
            $cl_id = get_ledger_by_name($dbcon,'SGST');
            $insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per1,$sgst_tax_rate1,$inserid_acc,"tbl_sales_ordertrn",$inq_rel['product_id'],3,$edit_id,$branch_id,$currency_id,$currency_rate,$sgst_tax_rate_conv1);
        }
        if(($igst_tax_per1 != 0) && ($igst_tax_rate1 != 0) ){
            $cl_id = get_ledger_by_name($dbcon,'IGST');
            $insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per1,$igst_tax_rate1,$inserid_acc,"tbl_sales_ordertrn",$inq_rel['product_id'],3,$edit_id,$branch_id,$currency_id,$currency_rate,$igst_tax_rate_conv1);
        }

        // check for the addiotional tax on product Start -- dhaval
        $pro_amt = $inq_rel['acc_amount']*$currency_rate;
        $count_add_tax=get_check_addition_tax($dbcon,$sale_gst1['tax_cat_id'],$inq_rel['acc_amount'],$inserid_acc,$inq_rel['product_id'],$edit_id,$branch_id,'tbl_sales_ordertrn',$currency_id,$currency_rate,$pro_amt);

        $deleteid=delete_record('tbl_so_access_trn', "pid=".$product_id. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
    }
}

