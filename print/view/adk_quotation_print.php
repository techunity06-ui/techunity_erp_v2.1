<?php
session_start();
// var_dump(123);
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

$userQuery = "SELECT u.*, type.usertype_name FROM users u LEFT JOIN tbl_usertype as type on type.usertype_id=u.user_type
WHERE u.active = 0 AND u.user_id = ".$_SESSION['user_id'];
$userData = brp_mysqli_fetch_assoc($dbcon->query($userQuery));
$userPhone = $userData['user_phone'] ? 'Mo.: '.$userData['user_phone'] : '';
$userEmail = $userData['user_mail'] ? ' - Email: '.$userData['user_mail'] : '';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	ORDER_ACCEPTANCE_SLUG_PRINT,
]);

$invoiceid=$dbcon->real_escape_string($_REQUEST['id']);	
$type='pdf';
if(strtolower($type) == 'pdf') {
    //Quotation Data
    $query = "select invoice.*,quot.quotation_no,quot.quotation_date,country.country_name,state.state_name,city.city_name,cust.l_name as company_name,cust.m_address as cust_address,cust.cust_pincode,cust.cust_mobile,cust.common_email_id,cust.gst_no,cust.m_pan,per.c_con_fname,per.c_con_lname,state.gst_state_code,td.transportation_name,cust.cust_cont_name, cust.stateid,terms.payment_terms from tbl_sales_order as invoice
    left join tbl_quotation as quot on quot.quotation_id=invoice.quotation_id
    left join pay_terms as terms on terms.terms_id=invoice.payment_terms
    left join tbl_ledger as cust on cust.l_id=invoice.cust_id
    left join tbl_cust_contact as per on per.cust_id = invoice.cust_id
    left join country_mst as country on country.countryid=cust.countryid
    left join state_mst as state on state.stateid=cust.stateid
    left join city_mst as city on city.cityid=cust.cityid
    left join transportation_details as td on td.id=invoice.transport_id
    where invoice.sales_order_id=$invoiceid";

    $rel = mysqli_fetch_assoc($dbcon->query($query));

    // safe defaults for dates (same logic preserved)
    $po_date = '';
    if (!empty($rel['po_date']) && $rel['po_date']!="1970-01-01 00:00:00" && $rel['po_date']!="0000-00-00 00:00:00") {
        $po_date = date('d-m-Y', strtotime($rel['po_date']));
    }
    $so_date = '';
    if (!empty($rel['sales_order_date']) && $rel['sales_order_date']!="1970-01-01 00:00:00" && $rel['sales_order_date']!="0000-00-00 00:00:00") {
        $so_date = date('d-m-Y', strtotime($rel['sales_order_date']));
    }
    $delivery_date = '';
    if (!empty($rel['delivery_date']) && $rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00") {
        $delivery_date = date('d-m-Y', strtotime($rel['delivery_date']));
    }

    if(!$rel){
        header("Location: ".ROOT.CRM_ROOT."order_acceptance_list");
        exit;
    }

    $HowManyWeeks = (strtotime( $rel['cdate'] ) - strtotime( $rel['sales_order_date'])) / 604800;
    $HowManyWeeks = round($HowManyWeeks);
    $HowManyWeeks = ($HowManyWeeks=='0')?'0':$HowManyWeeks;
    $delivery_week = $HowManyWeeks .' - '. ($HowManyWeeks+1) . ' WEEKS';
    $order_by = ($rel['order_by']!='0')?$rel['order_by']:"";

    $party_address_billing = "<strong>".(isset($rel['company_name']) ? $rel['company_name'] : '')."</strong>
    <span style='font-weight:normal;'> <br/>"
    .(isset($rel['cust_address']) ? $rel['cust_address'] : '').",<br/>"
    .(isset($rel['cust_pincode']) ? $rel['cust_pincode'] : '')." "
    .(isset($rel['city_name']) ? $rel['city_name'] : '').", "
    .(isset($rel['state_name']) ? $rel['state_name'] : '').", "
    .(isset($rel['country_name']) ? $rel['country_name'] : '')."</span>
    <br>  State Code :".(isset($rel['state_name']) ? $rel['state_name'] : '')."(".(isset($rel['gst_state_code']) ? $rel['gst_state_code'] : '').")
    <br>  GSTIN : ".(isset($rel['gst_no']) ? $rel['gst_no'] : '');

    if(isset($rel['consignee_id']) && $rel['consignee_id']==0){
        $contact_person = isset($rel['cust_cont_name']) ? $rel['cust_cont_name'] : '';
        $party_address_con = $party_address_billing;
    } else if (!empty($rel['consignee_id'])) {
        $query_con="select cust.cust_name,cust.company_name,cust.cust_address,cust.cust_mobile,cust.cust_email,cust.cust_pincode,cust.gst_no,country.country_name,state.state_name,city.city_name,state.gst_state_code from tbl_custmer_consignee as cust 
        left join country_mst as country on country.countryid=cust.countryid
        left join state_mst as state on state.stateid=cust.stateid
        left join city_mst as city on city.cityid=cust.cityid
        where cust_id=".$rel['consignee_id'];
        $rel_con = brp_mysqli_fetch_assoc($dbcon->query($query_con));	
        $cpincode = "";
        if(!empty($rel_con['cust_pincode'])){
            $cpincode = "- ".$rel_con['cust_pincode'];
        }
        $contact_person = isset($rel_con['cust_name']) ? $rel_con['cust_name'] : '';
        $party_address_con = "
        <strong>".(isset($rel_con['cust_name']) ? $rel_con['cust_name'] : '')."</strong>
        <span style='font-weight:normal;'> <br/>
        ".(isset($rel_con['cust_address']) ? $rel_con['cust_address'] : '').",<br/>
        ".(isset($rel_con['cust_pincode']) ? $rel_con['cust_pincode'] : '')." "
        .(isset($rel_con['city_name']) ? $rel_con['city_name'] : '').", "
        .(isset($rel_con['state_name']) ? $rel_con['state_name'] : '').", "
        .(isset($rel_con['country_name']) ? $rel_con['country_name'] : '')."</span>
        <br>  State Code : ".(isset($rel_con['state_name']) ? $rel_con['state_name'] : '')."(".(isset($rel_con['gst_state_code']) ? $rel_con['gst_state_code'] : '').")
        <br>  GSTIN : ".(isset($rel_con['gst_no']) ? $rel_con['gst_no'] : '');
    } else {
        $contact_person = '';
        $party_address_con = '';
    }

    $quot_address = isset($rel['quot_address']) ? nl2br($rel['quot_address']) : '';

    // currency logic preserved
    if(isset($rel['currency_id']) && $rel['currency_id']=='1'){
        $currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`=68 ';
        $currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
        $currency_name = '(INR)';
        $currency_word_start = 'Rupees';
        $currency_word_end = 'Paise';
        $currency_symbol = $currency_rel['currency_symbol'];
    } else if (!empty($rel['currency_id'])) {
        $currency_sql = 'SELECT * FROM `tbl_currency` WHERE `currency_id`="'.$rel['currency_id'].'" ';
        $currency_rel=mysqli_fetch_assoc($dbcon->query($currency_sql));
        $currency_name = '('.ucfirst(strtolower($currency_rel['currency_code'])).')';
        $currency_word_start = ucfirst(strtolower($currency_rel['currency_in_word']));
        $currency_word_end = ucfirst(strtolower($currency_rel['currency_in_word_end']));
        $currency_symbol = $currency_rel['currency_symbol'];
    } else {
        $currency_name = '';
        $currency_word_start = '';
        $currency_word_end = '';
        $currency_symbol = '';
    }

    $set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".(isset($rel['company_id'])?$rel['company_id']:0);
    $set_head=mysqli_fetch_assoc($dbcon->query($set));
    $companyConfiguration = getCompanyConfiguration($dbcon);
    $sales_pro_print = explode(",", $companyConfiguration['sales_pro_print']);
    $html='';

    // header image or letterhead logic preserved
    $header = '';
    if($companyConfiguration['sales_print_letterhead_per']==0){
        $header .='<img src="'.DOMAIN_F.LOGO.(isset($set_head['logo']) ? $set_head['logo'] : '').'" style="width: 60%" />';
        $footer = '';
    } else {
        $header = get_header($dbcon,'text-align: center','','70px');
        $footer = '';
    }

    // start building the new UI html (using the new CSS & layout)
    $html .= '<html><head><title>ORDER ACCEPTANCE - '.(isset($rel['sales_order_no'])?$rel['sales_order_no']:'').'</title>
   <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:Arial,sans-serif;padding:8px;font-size:11px;}
    .container{max-width:100%;margin:0 auto;border:2px solid #000;padding:8px;}
    /* Top section: left = company, right = order info */
   /* ---------- top/header layout: force single row ---------- */
.top-section{
  display: flex;
  flex-wrap: nowrap;              /* important: keep everything on one row */
  justify-content: space-between;
  align-items: flex-start;        /* align top edges */
  gap: 12px;
  padding: 8px 10px;
  border-bottom: 2px solid #000;
  width: 100%;
  box-sizing: border-box;
}

/* left side: allow this column to shrink (min-width:0 is required) */
.company-info{
  flex: 1 1 auto;                 /* flexible, can grow and shrink */
  min-width: 0;                   /* CRITICAL: allows the flex item to shrink below its content width */
  font-size: 10px;
  line-height: 1.25;
  overflow: hidden;               /* keep layout safe if content is very long */
  word-wrap: break-word;
}

.work-order-header{
  flex: 0 0 300px;                /* fixed base width (adjust smaller if needed) */
  max-width: 300px;
  min-width: 200px;               /* prevents collapse on tiny widths */
  text-align: right;
  font-size: 11px;
  line-height: 1.25;
}

/* smaller heading so it fits better */
.work-order-header h1{
  font-size: 20px;
  margin: 0 0 4px 0;
  line-height: 1;
}

/* ---------- address section: 3 columns in one row ---------- */
/* Use minmax(0,1fr) so grid children can shrink when needed */
.address-section{
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0;
  border-bottom: 2px solid #000;
  margin-top: 8px;
  box-sizing: border-box;
}

/* each box must allow shrinking */
.address-box{
  padding: 10px;
  border-right: 2px solid #000;
  font-size: 10px;
  line-height: 1.25;
  min-height: 120px;
  min-width: 0;                    /* allow the box to shrink inside grid */
  overflow-wrap: break-word;
}

/* remove right border on last child */
.address-box:last-child{
  border-right: none;
}

/* small responsive fallback */
@media print, screen and (max-width:1000px){
  .work-order-header{ flex: 0 0 260px; max-width:260px; }
  .work-order-header h1{ font-size:18px; }
  .company-info{ font-size:9px; }
}

    /* Address section */
    .address-section{
        display:grid;
        grid-template-columns:repeat(3,1fr);
        gap:0;
        border-bottom:2px solid #000;
        margin-top:8px;
    }
    .address-box{
        padding:10px;
        border-right:2px solid #000;
        font-size:10px;
        line-height:1.25;
        min-height:120px;
    }
    .address-box:last-child{border-right:none;}
    .address-box h3{font-size:12px;font-weight:700;margin-bottom:6px;}
    .other-info{font-size:10px;line-height:1.35;}
    .other-info-row{display:flex;margin-bottom:4px;}
    .other-info-row strong{min-width:130px;display:inline-block;font-weight:600;}

    /* Items table - compact */
    .items-table{width:100%;border-collapse:collapse;margin-top:8px;}
    .items-table th{
        background:#fff;border:1px solid #000;padding:5px 4px;font-size:9px;text-align:center;font-weight:700;line-height:1.1;
    }
    .items-table td{
        border:1px solid #000;padding:6px 4px;font-size:9px;text-align:center;vertical-align:top;line-height:1.2;
    }
    .items-table th:first-child, .items-table td:first-child{width:38px;}
    .items-table thead th{padding:6px 4px;}
    .items-table tbody tr{height:56px;} /* reduced row height to fit more rows */
    .text-left{text-align:left !important;}

    /* Tax / totals area */
    .tax-table{width:100%;border-collapse:collapse;margin-top:8px;font-size:10px;}
    .tax-table td,.tax-table th{border:1px solid #000;padding:6px;}

    /* Packing instructions */
    .packing-table{width:100%;border-collapse:collapse;margin-top:8px;}
    .packing-table th,.packing-table td{border:1px solid #000;padding:6px;font-size:10px;text-align:center;}

    /* Signature area tweaks */
    .signature {text-align:center;}
    .signature img{max-width:120px;max-height:100px;display:block;margin:0 auto;}

    @media print{ body{padding:0;} .container{padding:4px;} }
</style>

    </head><body>
    <div class="container">
    ';

  $html .= '<div class="top-section">
    <div class="company-info">
        <strong>'.(isset($set_head['company_name'])?$set_head['company_name']:'').'</strong><br/>
        '.(isset($set_head['address'])?$set_head['address']:'').'<br/>
        PAN No: '.(isset($set_head['pan_no'])?$set_head['pan_no']:'').'
    </div>
    <div class="work-order-header">
        <h1>ORDER ACCEPTANCE</h1>
        <div class="work-order-details">
            <div><strong>Order No :</strong> '.(isset($rel['sales_order_no'])?$rel['sales_order_no']:'').'</div>
            <div><strong>Order Date :</strong> '.($so_date).'</div>
            <div><strong>GSTIN No :</strong> '.(isset($set_head['vatno'])?$set_head['vatno']:'').'</div>
        </div>
    </div>
</div>';


    // address section
    $html .= '<div class="address-section">
        <div class="address-box">
            <h3>Bill To</h3>
            <div>'.$party_address_billing.'</div>
        </div>
        <div class="address-box">
            <h3>Ship To</h3>
            <div>'.$party_address_con.'</div>
        </div>
        <div class="address-box">
            <h3>Other Information</h3>
            <div class="other-info">
                <div class="other-info-row"><strong>PO No</strong> : '.(isset($rel['po_no'])?$rel['po_no']:'').'</div>
                <div class="other-info-row"><strong>PO Date</strong> : '.($po_date).'</div>
                <div class="other-info-row"><strong>Quotation No</strong> : '.(isset($rel['quotation_no'])?$rel['quotation_no']:'').'</div>
                <div class="other-info-row"><strong>Quotation Date</strong> : '.(isset($rel['quotation_date'])?$rel['quotation_date']:'').'</div>
                <div class="other-info-row"><strong>Delivery Term</strong> : '.(isset($rel['delivery_type'])?$rel['delivery_type']:'').'</div>
                <div class="other-info-row"><strong>Mode of Transport</strong> : '.(isset($rel['transport_mode'])?$rel['transport_mode']:'').'</div>
                <div class="other-info-row"><strong>Transporter Name</strong> : '.(isset($rel['transportation_name'])?$rel['transportation_name']:'').'</div>
                <div class="other-info-row"><strong>Transporter Contact</strong> : '.(isset($rel['transport_contact'])?$rel['transport_contact']:'').'</div>
                <div class="other-info-row"><strong>Buyer Name</strong> : '.(isset($contact_person)?$contact_person:'').'</div>
                <div class="other-info-row"><strong>Buyer Contact Detail</strong> : '.(isset($rel['cust_mobile'])?$rel['cust_mobile']:'').'</div>
                <div class="other-info-row"><strong>Buyer Email ID</strong> : '.(isset($rel['common_email_id'])?$rel['common_email_id']:'').'</div>
            </div>
        </div>
    </div>';

    // items table header (new UI columns)
    $html .= '<table class="items-table"><thead><tr>
        <th>Sr<br>No</th>
        <th>OA<br>No</th>
        <th>SBA<br>No</th>
        <th>PO No</th>
        <th>PO Date</th>
        <th>Party Code</th>
        <th>Party Desc</th>
        <th>Party<br>Drawing</th>
        <th>Our Item<br>Code</th>
        <th>Our Item<br>Desc</th>
        <th>Balance<br>Qty</th>
        <th>Issued<br>Qty</th>
        <th>Req Del<br>Date</th>
        <th>No of<br>Packets</th>
        <th>Box No</th>
        <th>Net<br>Weight</th>
        <th>Gross<br>Weight</th>
    </tr></thead><tbody>';

    // get items (same query as before)
    $qry = "select trn.*,product.product_name,product.product_icode,unit_name FROM `tbl_sales_ordertrn` as trn left join product_mst as product on product.product_id=trn.product_id left join unit_mst as per on per.unitid=trn.unit_id WHERE sales_ordertrn_status=0 and sales_order_id='$invoiceid' ";
    $result = $dbcon->query($qry);

    $i = 1;
    $total = 0;
    $discount = 0;
    $totalqty = 0;
    $charges_qty = 0;
    $total_gst = 0;
    $total_i_gst = 0;
    $cnt = brp_mysqli_num_rows($result);

    while ($row = brp_mysqli_fetch_assoc($result)) {
        // build each row, using fallback to empty strings when fields are missing
        $html .= '<tr>';
        $html .= '<td>'.($i).'</td>';
        $html .= '<td>'.(isset($row['oa_no']) ? $row['oa_no'] : '').'</td>';
        $html .= '<td>'.(isset($row['sba_no']) ? $row['sba_no'] : '').'</td>';
        // PO No may be on header or per row; prefer per-row if exists else header rel['po_no']
        $po_no_cell = isset($row['po_no']) ? $row['po_no'] : (isset($rel['po_no']) ? $rel['po_no'] : '');
        $html .= '<td>'.$po_no_cell. (isset($row['po_suffix']) && $row['po_suffix'] ? '<br>'. $row['po_suffix'] : '').'</td>';
        $html .= '<td>'.($po_date).'</td>';
        $html .= '<td>'.(isset($row['party_code']) ? $row['party_code'] : '').'</td>';
        $html .= '<td class="text-left">'.(isset($row['party_desc']) ? $row['party_desc'] : '').'</td>';
        $html .= '<td>'.(isset($row['party_drawing']) ? $row['party_drawing'] : '').'</td>';
        $html .= '<td>'.(isset($row['our_item_code']) ? $row['our_item_code'] : (isset($row['product_icode']) ? $row['product_icode'] : '')).'</td>';
        // product name and description
        $desc_html = '';
        if (isset($row['product_name'])) {
            $desc_html .= $row['product_name'];
        }
        if (isset($company_config['enable_item_description']) && $company_config['enable_item_description'] == 1 && !empty($row['description'])) {
            $desc_html .= '<br>'. $row['description'];
        } else if (empty($desc_html) && isset($row['description']) && !empty($row['description'])) {
            $desc_html .= $row['description'];
        }
        $html .= '<td class="text-left">'.$desc_html.'</td>';

        // quantities (format when present)
        $balance_qty = (isset($row['balance_qty']) ? number_format($row['balance_qty'], 2) : (isset($row['product_qty']) ? number_format($row['product_qty'],2) : ''));
        $issued_qty = (isset($row['issued_qty']) ? number_format($row['issued_qty'], 2) : '');
        $html .= '<td>'. $balance_qty .'</td>';
        $html .= '<td>'. $issued_qty .'</td>';
        $html .= '<td>'. ($delivery_date) .'</td>';
        $html .= '<td>'. (isset($row['no_of_packets']) ? $row['no_of_packets'] : '') .'</td>';
        $html .= '<td>'. (isset($row['box_no']) ? $row['box_no'] : '') .'</td>';
        $html .= '<td>'. (isset($row['net_weight']) ? $row['net_weight'] : '') .'</td>';
        $html .= '<td>'. (isset($row['gross_weight']) ? $row['gross_weight'] : '') .'</td>';

        $html .= '</tr>';

        // keep original aggregations intact
        $gst_per = (isset($row['cgst_tax_per']) ? $row['cgst_tax_per'] : 0) + (isset($row['sgst_tax_per']) ? $row['sgst_tax_per'] : 0) + (isset($row['igst_tax_per']) ? $row['igst_tax_per'] : 0);
        $gst_rate = (isset($row['cgst_tax_rate_conv']) ? $row['cgst_tax_rate_conv'] : 0) + (isset($row['sgst_tax_rate_conv']) ? $row['sgst_tax_rate_conv'] : 0) + (isset($row['igst_tax_rate_conv']) ? $row['igst_tax_rate_conv'] : 0);
        if (isset($row['cgst_tax_rate_conv']) && $row['cgst_tax_rate_conv'] != 0 || (isset($row['sgst_tax_rate_conv']) && $row['sgst_tax_rate_conv'] != 0)) {
            $total_cs_gst += $gst_rate;
        } else {
            $total_i_gst += $gst_rate;
        }

        $i++;
        $totalqty = $totalqty + (isset($row['product_qty']) ? $row['product_qty'] : 0);
        $total_product_amount += (isset($row['product_qty']) && isset($row['product_rate']) ? ($row['product_qty'] * $row['product_rate']) : 0);
        $totaltaxable += (isset($row['product_amount_conv']) ? $row['product_amount_conv'] : 0);
        $totaltax1 += (isset($row['tax_amount1']) ? $row['tax_amount1'] : 0);
        $totaltax2 += (isset($row['tax_amount2']) ? $row['tax_amount2'] : 0);
        $total += (isset($row['total']) ? $row['total'] : 0);
        $total_gst_rate += $gst_rate;
    }

    // add empty lines to fill table (same as original)
    $lines = 5;
    for ($j = 1; $j <= $lines; $j++) {
        $html .= '<tr>';
        for ($c = 1; $c <= 17; $c++) {
            $html .= '<td></td>';
        }
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    // Tax / totals section preserved — simplified to match new UI but same computation & values
    $html .= '<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin-top:10px;"><tr><td style="vertical-align:top;width:60%;">';

    // Build tax breakdown table (we preserve all your original queries & calculations)
    $html .= '<table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;table-layout:fixed;">';

    // header rows depend on intra-state or inter-state (preserve original logic)
    if (isset($rel['stateid']) && isset($set_head['stateid']) && $rel['stateid'] == $set_head['stateid']) {
        $html .= '<tr>
            <td rowspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>HSN/SAC</strong></td>
            <td rowspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>Taxable Value</strong></td>
            <td colspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>CGST Tax</strong></td>
            <td colspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>SGST Tax</strong></td>
            <td rowspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>Total Tax Amount</strong></td>
        </tr>
        <tr>
            <td style="text-align:center;border:1px solid #000;padding:6px;"><strong>Rate</strong></td>
            <td style="text-align:center;border:1px solid #000;padding:6px;"><strong>Amount</strong></td>
            <td style="text-align:center;border:1px solid #000;padding:6px;"><strong>Rate</strong></td>
            <td style="text-align:center;border:1px solid #000;padding:6px;"><strong>Amount</strong></td>
        </tr>';
    } else {
        $html .= '<tr>
            <td rowspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>HSN/SAC</strong></td>
            <td rowspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>Taxable Value</strong></td>
            <td colspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>IGST Tax</strong></td>
            <td rowspan="2" style="text-align:center;border:1px solid #000;padding:6px;"><strong>Total Tax Amount</strong></td>
        </tr>
        <tr>
            <td style="text-align:center;border:1px solid #000;padding:6px;"><strong>Rate</strong></td>
            <td style="text-align:center;border:1px solid #000;padding:6px;"><strong>Amount</strong></td>
        </tr>';
    }

    // tax grouping query (preserved)
    $query = "select product_hsn_code,sum(product_amount) as product_amount,cgst_tax_per,sum(cgst_tax_rate) as cgst_tax_rate,sgst_tax_per,sum(sgst_tax_rate) as sgst_tax_rate,igst_tax_per,sum(igst_tax_rate) as igst_tax_rate FROM `tbl_sales_ordertrn` as trn  where trn.sales_ordertrn_status=0 and trn.sales_order_id=" . $invoiceid . " group by trn.product_hsn_code";
    $rs_tax = $dbcon->query($query);
    while ($rel_tax = brp_mysqli_fetch_assoc($rs_tax)) {
        $row_total = (isset($rel_tax['cgst_tax_rate']) ? $rel_tax['cgst_tax_rate'] : 0) + (isset($rel_tax['sgst_tax_rate']) ? $rel_tax['sgst_tax_rate'] : 0) + (isset($rel_tax['igst_tax_rate']) ? $rel_tax['igst_tax_rate'] : 0);
        $html .= '<tr>
            <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($rel_tax['product_hsn_code']) ? $rel_tax['product_hsn_code'] : '').'</td>
            <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($rel_tax['product_amount']) ? $rel_tax['product_amount'] : '').'</td>';

        if (isset($rel['stateid']) && isset($set_head['stateid']) && $rel['stateid'] == $set_head['stateid']) {
            $html .= '<td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($rel_tax['cgst_tax_per']) ? str_replace("CGST","",$rel_tax['cgst_tax_per']) : '').'</td>
                      <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($rel_tax['cgst_tax_rate']) ? $rel_tax['cgst_tax_rate'] : '').'</td>
                      <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($rel_tax['sgst_tax_per']) ? str_replace("SGST","",$rel_tax['sgst_tax_per']) : '').'</td>
                      <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($rel_tax['sgst_tax_rate']) ? $rel_tax['sgst_tax_rate'] : '').'</td>';
        } else {
            $html .= '<td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($rel_tax['igst_tax_per']) ? str_replace("IGST","",$rel_tax['igst_tax_per']) : '').'</td>
                      <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($rel_tax['igst_tax_rate']) ? $rel_tax['igst_tax_rate'] : '').'</td>';
        }

        $html .= '<td style="text-align:center;border:1px solid #000;padding:6px;">'.number_format($row_total, 2).'</td></tr>';

        $totalamt += (isset($rel_tax['product_amount']) ? $rel_tax['product_amount'] : 0);
        $totaltaxamt1 += (isset($rel_tax['cgst_tax_rate']) ? $rel_tax['cgst_tax_rate'] : 0);
        $totaltaxamt2 += (isset($rel_tax['sgst_tax_rate']) ? $rel_tax['sgst_tax_rate'] : 0);
        $totaltaxamt3 += (isset($rel_tax['igst_tax_rate']) ? $rel_tax['igst_tax_rate'] : 0);
        $total1 += $row_total;
    }

    // sundry taxes preserved
    $sundrytax1 = $dbcon->query("select b.*,tl.ledger_hsn from tbl_bill_sundry_transaction as b
                    left join tbl_ledger as tl on b.sundry_ledger_id=tl.l_id
                    where b.sundry_voucher_id=" . $invoiceid . " and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' ");
    while ($sundry_tax = brp_mysqli_fetch_assoc($sundrytax1)) {
        if ($sundry_tax['sundry_gst_amount'] != 0) {
            $total_sun1 += $sundry_tax['sundry_gst_amount'];
            $html .= '<tr>
                <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($sundry_tax['ledger_hsn'])?$sundry_tax['ledger_hsn']:'').'</td>
                <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($sundry_tax['sundry_amount'])?$sundry_tax['sundry_amount']:'').'</td>';
            if (isset($rel['stateid']) && isset($set_head['stateid']) && $rel['stateid'] == $set_head['stateid']) {
                $sun_gst_per = $sundry_tax['sundry_gst_per'] / 2;
                $sun_gst_amt = $sundry_tax['sundry_gst_amount'] / 2;
                $html .= '<td style="text-align:center;border:1px solid #000;padding:6px;">'.$sun_gst_per.'</td>
                          <td style="text-align:center;border:1px solid #000;padding:6px;">'.$sun_gst_amt.'</td>
                          <td style="text-align:center;border:1px solid #000;padding:6px;">'.$sun_gst_per.'</td>
                          <td style="text-align:center;border:1px solid #000;padding:6px;">'.$sun_gst_amt.'</td>';
            } else {
                $html .= '<td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($sundry_tax['sundry_gst_per'])?$sundry_tax['sundry_gst_per']:'').'</td>
                          <td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($sundry_tax['sundry_gst_amount'])?$sundry_tax['sundry_gst_amount']:'').'</td>';
            }
            $html .= '<td style="text-align:center;border:1px solid #000;padding:6px;">'.(isset($sundry_tax['sundry_gst_amount'])?$sundry_tax['sundry_gst_amount']:'').'</td></tr>';
            $total_sunamt += (isset($sundry_tax['sundry_amount']) ? $sundry_tax['sundry_amount'] : 0);
            $total_suntaxamt1 += (isset($sundry_tax['sundry_gst_amount']) ? $sundry_tax['sundry_gst_amount']/2 : 0);
            $total_suntaxamt2 += (isset($sundry_tax['sundry_gst_amount']) ? $sundry_tax['sundry_gst_amount'] : 0);
        }
    }

    // totals row
    $html .= '<tr>
        <td colspan="2" style="text-align:right;border:1px solid #000;padding:6px;">'.number_format($totalamt + $total_sunamt, 2).'</td>';
    if (isset($rel['stateid']) && isset($set_head['stateid']) && $rel['stateid'] == $set_head['stateid']) {
        $html .= '<td colspan="2" style="text-align:right;border:1px solid #000;padding:6px;">'.number_format($totaltaxamt1 + $total_suntaxamt1, 2).'</td>
                  <td colspan="2" style="text-align:right;border:1px solid #000;padding:6px;">'.number_format($totaltaxamt2 + $total_suntaxamt1, 2).'</td>';
    } else {
        $html .= '<td colspan="2" style="text-align:right;border:1px solid #000;padding:6px;">'.number_format($totaltaxamt3 + $total_suntaxamt2, 2).'</td>';
    }
    $html .= '<td style="text-align:right;border:1px solid #000;padding:6px;">'.number_format($total1 + $total_sun1, 2).'</td></tr>';

    $html .= '</table>'; // end tax table
    $html .= '</td>'; // left column

    // right column: totals summary (preserved behaviour)
    $html .= '<td style="padding:0;text-align:right;width:40%;vertical-align:top;"><table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;table-layout:fixed;">';
    $html .= '<tr><td colspan="2" style="width:45%;font-size:12px;border-left:none;">Taxable Value</td><td style="width:20%;text-align:right;">'.number_format($totalamt, 2).'</td></tr>';

    // TCS, additional taxes, bill sundry (preserved from original code)
    $qry121 = "select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
        from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
        left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
        where b.sundry_voucher_id=" . $rel['sales_order_id'] . " and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and b.sundry_ledger_id=" . TCS . " ";
    $result121 = $dbcon->query($qry121);
    if (brp_mysqli_num_rows($result121) > 0) {
        $row121 = brp_mysqli_fetch_assoc($result121);
        if ($company_config['tax_editable'] == 0) {
            $tcs_gst = $row121['sundry_amount'];
        } else {
            $tcs_gst = $rel['tcs'];
        }
        $html .= '<tr><td style="border-right:1px solid;border-top:1px solid;font-size:12px;"></td><td style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left">TCS</td><td style="text-align:right;border-top:1px solid;font-size:12px;border-left:1px solid;">'.number_format($tcs_gst,2,'.','').'</td></tr>';
    }

    $qry11 = "select sum((tc.tax_per*trn.product_amount)/100) as add_sum ,l.l_name,l.l_id,t.tax_cat_id from tbl_sales_ordertrn as trn 
        left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
        left join tbl_ledger as l on l.l_id=tc.tax_id 
        where tc.tax_additional='1' and trn.sales_order_id=" . $rel['sales_order_id'] . " and trn.sales_ordertrn_status!=2 and tc.isdelete='0' group by tc.tax_id";
    $result11 = $dbcon->query($qry11);
    while ($row11 = brp_mysqli_fetch_assoc($result11)) {
        $html .= '<tr><td colspan="2" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left;">'.$row11['l_name'].'</td><td style="text-align:right;border-top:1px solid;font-size:12px;border-left:1px solid;">'.number_format($row11['add_sum'],2,'.','').'</td></tr>';
    }

    $qry12 = "select b.sundry_amount,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id 
        from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id 
        left join tbl_ledger as le on le.l_id=b.sundry_ledger_id 
        where b.sundry_voucher_id=" . $rel['sales_order_id'] . " and b.sundry_voucher_table='tbl_sales_order' and b.isdelete='0' and le.default_sundry='0' ";
    $result12 = $dbcon->query($qry12);
    while ($row12 = brp_mysqli_fetch_assoc($result12)) {
        $html .= '<tr><td colspan="2" style="border-top:1px solid;border-right:1px solid;font-size:12px;text-align:left;">'.$row12['l_name'].'</td><td style="text-align:right;border-top:1px solid;font-size:12px;border-left:1px solid;">'.number_format($row12['sundry_amount'],2,'.','').'</td></tr>';
    }

    // total tax
    $html .= '<tr><td colspan="2" style="font-size:12px;border-left:none;">Total Tax</td>';
    if (isset($rel['stateid']) && isset($set_head['stateid']) && $rel['stateid'] == $set_head['stateid']) {
        $html .= '<td style="text-align:right;border-top:1px solid;border-right:1px solid;">'.number_format($totaltaxamt1 + $total_suntaxamt1 + $totaltaxamt2 + $total_suntaxamt1, 2).'</td>';
    } else {
        $html .= '<td style="text-align:right;border-top:1px solid;border-left:1px solid;border-right:1px solid;">'.number_format($totaltaxamt3 + $total_suntaxamt2, 2).'</td>';
    }
    $html .= '</tr>';

    $html .= '<tr><td colspan="2" style="border-top:1px solid;border-right:1px solid;border-left:none;font-size:12px;text-align:left;">Round Off</td><td style="text-align:right;border-top:1px solid;border-left:1px solid;">'.(isset($rel['round_off'])?$rel['round_off']:'').'</td></tr>';
    $html .= '<tr><td colspan="2" style="border-left:none;"><b>Total Amount</b></td><td style="text-align:right;">'.(isset($rel['g_total'])? number_format($rel['g_total'],0,'.','') . '.00' : '').'</td></tr>';

    $html .= '</table></td></tr></table>';

    // Amount in words and bank details (preserved)
    $html .= '<table width="100%" style="margin-top:8px;">';
    $html .= '<tr><td colspan="4"><b>Amount In Words</b> : '.(isset($rel['g_total']) ? ucwords(convert_number_to_words_new(round($rel['g_total']))) : '').'</td></tr>';
    $html .= '<tr>
        <td style="width:25%;">Bank Name<br>'.(isset($set_head['bank_name'])?$set_head['bank_name']:'').'</td>
        <td style="width:25%;">Branch<br>'.(isset($set_head['branch_name'])?$set_head['branch_name']:'').'</td>
        <td style="width:25%;">IFSC No<br>'.(isset($set_head['ifcs'])?$set_head['ifcs']:'').'</td>
        <td style="width:25%;">Account No.<br>'.(isset($set_head['ac_no'])?$set_head['ac_no']:'').'</td>
    </tr></table>';

    // Terms & Condition / Declaration / Signature (preserved layout)
    $html .= '<table style="font-size:12px;border-collapse:collapse;width:100%;margin-top:10px;"><tr>
        <td width="30%" style="text-align:left;border:1px solid;padding:8px;vertical-align:top;">';
    if (isset($rel['terms_type']) && $rel['terms_type'] == 2) {
        $html .= (isset($rel['quotation_condition']) ? $rel['quotation_condition'] : '');
    }
    $html .= '</td>
        <td style="vertical-align:top;padding:8px;">Certified that the particulars given above are true and correct and the amount indicated represents the price actually charged and there is no flow of additional consideration or indirectly from the buyer.</td>
        <td style="vertical-align:top;"></td>
        <td style="vertical-align:top;text-align:center;border-left:1px solid;padding:8px;">';
    if (isset($set_head['authorized_signature']) && $set_head['authorized_signature'] != "") {
        $html .= '<img src="'.DOMAIN_F.'view/upload/signature/'.$set_head['authorized_signature'].'" style="height:100px;width:100px;"><br>';
    } else {
        $html .= '<br><br><br><br><br><br><br>';
    }
    $html .= '<span>Authorised Signatory</span></td></tr></table>';

    // Packing Instructions table (new UI)
    $html .= '<table class="packing-table" style="margin-top:12px;"><thead><tr>
        <th>Packing Instructions:</th>
        <th>Type of Packing</th>
        <th>Length (CM)</th>
        <th>Width (CM)</th>
        <th>Height (CM)</th>
        <th>Total Boxes</th>
        <th>Total Net Weight</th>
        <th>Total Gross Weight</th>
    </tr></thead><tbody><tr>
        <td>'.(isset($rel['packing_instructions'])?$rel['packing_instructions']:'').'</td>
        <td>'.(isset($rel['packing_type'])?$rel['packing_type']:'').'</td>
        <td>'.(isset($rel['packing_length'])?$rel['packing_length']:'').'</td>
        <td>'.(isset($rel['packing_width'])?$rel['packing_width']:'').'</td>
        <td>'.(isset($rel['packing_height'])?$rel['packing_height']:'').'</td>
        <td>'.(isset($rel['total_boxes'])?$rel['total_boxes']:'').'</td>
        <td>'.(isset($rel['total_net_weight'])?$rel['total_net_weight']:'').'</td>
        <td>'.(isset($rel['total_gross_weight'])?$rel['total_gross_weight']:'').'</td>
    </tr></tbody></table>';

    $html .= '</div></body></html>';

    // Output with mPDF (preserved)
    ob_end_clean();
    include("../../view/export/mpdf/mpdf.php");
  $mpdf=new mPDF('','A4-L','0','calibri','5','5','5','0','1','1');

    // $mpdf->defaultheaderfontsize = 10;
    // $mpdf->defaultheaderfontstyle = B;
    // $mpdf->defaultheaderline = 1;
    // $mpdf->defaultfooterfontsize = 10;
    // $mpdf->defaultfooterfontstyle = B;
    // $mpdf->defaultfooterline = 1;
    // $mpdf->SetHTMLHeader($header);
    $mpdf->SetWatermarkText();
    $mpdf->showWatermarkText = true;
    $mpdf->allow_charset_conversion=true;
    $mpdf->charset_in='UTF-8';
    $mpdf->WriteHTML($html);
    $mpdf->Output();
    ob_clean();
    return 'Order Acceptance'.(isset($rel['sales_order_no'])?$rel['sales_order_no']:'').'.pdf';
}
?>
